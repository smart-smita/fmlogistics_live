<?php

if (!function_exists('recordNcStatusTransition')) {
    /**
     * Records an NC status transition and manages review cycles across OE, HSE, and Gemba.
     * Guarantees unified cross-module tracking using canonical logical_nc_id.
     *
     * @param string $module 'OE', 'HSE', or 'GEMBA'
     * @param int $ncId The primary ID of the NC (audit_details_id for OE, id for HSE, gemba_sr_no for Gemba)
     * @param int|string $newStatusCode New status code
     * @param string|null $ncReference Optional NC reference number
     * @param string|null $logicalNcId Optional cross-module canonical identifier
     * @return bool
     */
    function recordNcStatusTransition(string $module, int $ncId, $newStatusCode, ?string $ncReference = null, ?string $logicalNcId = null): bool
    {
        try {
            $db = \Config\Database::connect();
            $now = date('Y-m-d H:i:s');
            $newStatusCode = (int)$newStatusCode;
            $module = strtoupper(trim($module));

            $reviewStatus = null;
            if ($module === 'OE') {
                if ($newStatusCode === 6) {
                    $reviewStatus = 'Cluster Review';
                } elseif ($newStatusCode === 2) {
                    $reviewStatus = 'Auditor Review';
                }
            } elseif ($module === 'HSE') {
                if ($newStatusCode === 5) {
                    $reviewStatus = 'Cluster Review';
                } elseif ($newStatusCode === 2) {
                    $reviewStatus = 'Auditor Review';
                }
            } elseif ($module === 'GEMBA') {
                if ($newStatusCode === 5) {
                    $reviewStatus = 'Cluster Review';
                } elseif ($newStatusCode === 2) {
                    $reviewStatus = 'Auditor Review';
                }
            }

            // Determine canonical logical_nc_id
            if (empty($logicalNcId)) {
                if ($module === 'OE') {
                    $logicalNcId = 'OE_NC_' . $ncId;
                } elseif ($module === 'HSE') {
                    $logicalNcId = 'HSE_NC_' . $ncId;
                } elseif ($module === 'GEMBA') {
                    $g = $db->table('alert_gemba_audits')
                        ->select('unique_no, source_module, hse_detail_id')
                        ->where('gemba_sr_no', $ncId)
                        ->get()->getRowArray();
                    if ($g && $g['source_module'] === 'hse_audit' && !empty($g['hse_detail_id'])) {
                        $logicalNcId = 'HSE_NC_' . (int)$g['hse_detail_id'];
                    } else {
                        $logicalNcId = 'GEMBA_NC_' . $ncId;
                    }
                } else {
                    $logicalNcId = $module . '_NC_' . $ncId;
                }
            }

            // 1. Close any currently active transition for this logical NC
            $db->table('alert_nc_status_transition')
                ->where('logical_nc_id', $logicalNcId)
                ->where('is_current', 1)
                ->update([
                    'is_current' => 0,
                    'exited_at' => $now
                ]);

            // 2. If transitioning into a review status, create a fresh active cycle
            if ($reviewStatus !== null) {
                // Fetch reference if not provided
                if (empty($ncReference)) {
                    if ($module === 'OE') {
                        $parent = $db->table('alert_final_structured_audit_details d')
                            ->select('a.audit_no')
                            ->join('alert_final_structured_audit a', 'a.structured_audit_id = d.structured_audit_id')
                            ->where('d.audit_details_id', $ncId)
                            ->get()->getRowArray();
                        $ncReference = $parent['audit_no'] ?? ('OE-NC-' . $ncId);
                    } elseif ($module === 'HSE') {
                        $hse = $db->table('alert_hse_audit_details d')
                            ->select('m.audit_no')
                            ->join('alert_hse_audit_master m', 'm.hse_audit_id = d.hse_audit_id', 'left')
                            ->where('d.id', $ncId)
                            ->get()->getRowArray();
                        $ncReference = 'HSED-' . $ncId;
                    } elseif ($module === 'GEMBA') {
                        $gemba = $db->table('alert_gemba_audits')
                            ->select('unique_no')
                            ->where('gemba_sr_no', $ncId)
                            ->get()->getRowArray();
                        $ncReference = $gemba['unique_no'] ?? ('GMB-' . $ncId);
                    }
                }

                $db->table('alert_nc_status_transition')->insert([
                    'module' => $module,
                    'nc_id' => $ncId,
                    'logical_nc_id' => $logicalNcId,
                    'nc_reference' => $ncReference,
                    'review_status' => $reviewStatus,
                    'status_code' => $newStatusCode,
                    'entered_at' => $now,
                    'exited_at' => null,
                    'is_current' => 1,
                    'created_at' => $now
                ]);
            }

            return true;
        } catch (\Throwable $e) {
            log_message('error', "recordNcStatusTransition failed for {$module} #{$ncId}: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('syncExistingPendingNcTransitions')) {
    /**
     * Backfills/synchronizes transition records for pre-existing pending NCs across OE, HSE, and Gemba.
     * Enforces latest-audit scoping for OE and HSE, and cross-module deduplication for HSE and Gemba.
     *
     * @return array Summary of sync operations
     */
    function syncExistingPendingNcTransitions(): array
    {
        $db = \Config\Database::connect();
        $oeCount = 0;
        $hseCount = 0;
        $gembaCount = 0;
        $staleClosed = 0;
        $now = date('Y-m-d H:i:s');

        try {
            // ── 1. OE: Latest Audits Only (reaudit = 0) ──
            $oePending = $db->table('alert_final_structured_audit_details d')
                ->select('d.audit_details_id, d.status, d.update_date, d.default_date, sa.audit_no, sa.reaudit')
                ->join('alert_final_structured_audit sa', 'sa.structured_audit_id = d.structured_audit_id')
                ->whereIn('d.status', [6, 2])
                ->get()->getResultArray();

            foreach ($oePending as $oe) {
                $ncId = (int)$oe['audit_details_id'];
                $logicalNcId = 'OE_NC_' . $ncId;
                $isLatestAudit = ((int)($oe['reaudit'] ?? 0) === 0);

                // If part of an older, superseded re-audit, close any active transition
                if (!$isLatestAudit) {
                    $closed = $db->table('alert_nc_status_transition')
                        ->where('logical_nc_id', $logicalNcId)
                        ->where('is_current', 1)
                        ->update(['is_current' => 0, 'exited_at' => $now]);
                    if ($closed) $staleClosed++;
                    continue;
                }

                $exists = $db->table('alert_nc_status_transition')
                    ->where('logical_nc_id', $logicalNcId)
                    ->where('is_current', 1)
                    ->countAllResults();

                if ($exists === 0) {
                    $statusCode = (int)$oe['status'];
                    $reviewStatus = ($statusCode === 6) ? 'Cluster Review' : 'Auditor Review';

                    $candidateDate = (!empty($oe['update_date']) && $oe['update_date'] !== '0000-00-00 00:00:00') ? $oe['update_date'] : null;
                    if (empty($candidateDate)) {
                        $candidateDate = (!empty($oe['default_date']) && $oe['default_date'] !== '0000-00-00 00:00:00') ? $oe['default_date'] : null;
                    }
                    $enteredAt = !empty($candidateDate) ? $candidateDate : $now;

                    $db->table('alert_nc_status_transition')->insert([
                        'module' => 'OE',
                        'nc_id' => $ncId,
                        'logical_nc_id' => $logicalNcId,
                        'nc_reference' => $oe['audit_no'] ?? ('OE-NC-' . $ncId),
                        'review_status' => $reviewStatus,
                        'status_code' => $statusCode,
                        'entered_at' => $enteredAt,
                        'exited_at' => null,
                        'is_current' => 1,
                        'created_at' => $now
                    ]);
                    $oeCount++;
                }
            }

            // ── 2. HSE: Latest Audits Only (MAX hse_audit_id per audit_no) ──
            $latestHseAudits = $db->query("SELECT MAX(hse_audit_id) as id FROM alert_hse_audit_master GROUP BY audit_no")->getResultArray();
            $latestHseIdList = array_values(array_filter(array_column($latestHseAudits, 'id')));

            $hsePending = [];
            if (!empty($latestHseIdList)) {
                $hsePending = $db->table('alert_hse_audit_details d')
                    ->select('d.id as hse_detail_id, d.hse_audit_id, d.nc_status, d.nc_cluster_reviewed_date, d.nc_auditor_reviewed_date, d.update_date, d.default_date, m.audit_no')
                    ->join('alert_hse_audit_master m', 'm.hse_audit_id = d.hse_audit_id')
                    ->where('d.finding', 'NO')
                    ->whereIn('d.nc_status', [5, 2])
                    ->whereIn('m.hse_audit_id', $latestHseIdList)
                    ->get()->getResultArray();
            }

            foreach ($hsePending as $h) {
                $ncId = (int)$h['hse_detail_id'];
                $logicalNcId = 'HSE_NC_' . $ncId;

                $exists = $db->table('alert_nc_status_transition')
                    ->where('logical_nc_id', $logicalNcId)
                    ->where('is_current', 1)
                    ->countAllResults();

                if ($exists === 0) {
                    $statusCode = (int)$h['nc_status'];
                    $reviewStatus = ($statusCode === 5) ? 'Cluster Review' : 'Auditor Review';

                    $enteredAt = null;
                    if ($statusCode === 5 && !empty($h['nc_cluster_reviewed_date'])) {
                        $enteredAt = $h['nc_cluster_reviewed_date'];
                    } elseif ($statusCode === 2 && !empty($h['nc_auditor_reviewed_date'])) {
                        $enteredAt = $h['nc_auditor_reviewed_date'];
                    }
                    if (empty($enteredAt) || $enteredAt === '0000-00-00 00:00:00') {
                        $enteredAt = !empty($h['update_date']) ? $h['update_date'] : (!empty($h['default_date']) ? $h['default_date'] : $now);
                    }
                    if (empty($enteredAt) || $enteredAt === '0000-00-00 00:00:00') {
                        $enteredAt = $now;
                    }

                    $db->table('alert_nc_status_transition')->insert([
                        'module' => 'HSE',
                        'nc_id' => $ncId,
                        'logical_nc_id' => $logicalNcId,
                        'nc_reference' => 'HSED-' . $ncId,
                        'review_status' => $reviewStatus,
                        'status_code' => $statusCode,
                        'entered_at' => $enteredAt,
                        'exited_at' => null,
                        'is_current' => 1,
                        'created_at' => $now
                    ]);
                    $hseCount++;
                }
            }

            // Close active transitions for older HSE audits if any
            if (!empty($latestHseIdList)) {
                $olderHseTransitions = $db->table('alert_nc_status_transition t')
                    ->select('t.id')
                    ->join('alert_hse_audit_details d', 'd.id = t.nc_id')
                    ->where('t.module', 'HSE')
                    ->where('t.is_current', 1)
                    ->whereNotIn('d.hse_audit_id', $latestHseIdList)
                    ->get()->getResultArray();

                foreach ($olderHseTransitions as $ot) {
                    $db->table('alert_nc_status_transition')
                        ->where('id', $ot['id'])
                        ->update(['is_current' => 0, 'exited_at' => $now]);
                    $staleClosed++;
                }
            }

            // ── 3. Gemba: All Applicable Gemba Audits with HSE Deduplication ──
            $gembaPending = $db->table('alert_gemba_audits')
                ->select('gemba_sr_no, unique_no, source_module, hse_audit_id, hse_detail_id, nc_status, nc_cluster_reviewed_date, nc_auditor_reviewed_date, update_date, default_date')
                ->whereIn('nc_status', [5, 2])
                ->get()->getResultArray();

            foreach ($gembaPending as $g) {
                $ncId = (int)$g['gemba_sr_no'];
                if ($g['source_module'] === 'hse_audit' && !empty($g['hse_detail_id'])) {
                    $logicalNcId = 'HSE_NC_' . (int)$g['hse_detail_id'];
                } else {
                    $logicalNcId = 'GEMBA_NC_' . $ncId;
                }

                // Deduplication check: if active transition already exists for this logical NC, skip
                $exists = $db->table('alert_nc_status_transition')
                    ->where('logical_nc_id', $logicalNcId)
                    ->where('is_current', 1)
                    ->countAllResults();

                if ($exists === 0) {
                    $statusCode = (int)$g['nc_status'];
                    $reviewStatus = ($statusCode === 5) ? 'Cluster Review' : 'Auditor Review';

                    $enteredAt = null;
                    if ($statusCode === 5 && !empty($g['nc_cluster_reviewed_date'])) {
                        $enteredAt = $g['nc_cluster_reviewed_date'];
                    } elseif ($statusCode === 2 && !empty($g['nc_auditor_reviewed_date'])) {
                        $enteredAt = $g['nc_auditor_reviewed_date'];
                    }
                    if (empty($enteredAt) || $enteredAt === '0000-00-00 00:00:00') {
                        $enteredAt = !empty($g['update_date']) ? $g['update_date'] : (!empty($g['default_date']) ? $g['default_date'] : $now);
                    }
                    if (empty($enteredAt) || $enteredAt === '0000-00-00 00:00:00') {
                        $enteredAt = $now;
                    }

                    $db->table('alert_nc_status_transition')->insert([
                        'module' => 'GEMBA',
                        'nc_id' => $ncId,
                        'logical_nc_id' => $logicalNcId,
                        'nc_reference' => $g['unique_no'] ?? ('GMB-' . $ncId),
                        'review_status' => $reviewStatus,
                        'status_code' => $statusCode,
                        'entered_at' => $enteredAt,
                        'exited_at' => null,
                        'is_current' => 1,
                        'created_at' => $now
                    ]);
                    $gembaCount++;
                }
            }

            // ── 4. Stale Transition Cleanup for Exited NCs ──
            $activeTransitions = $db->table('alert_nc_status_transition')
                ->where('is_current', 1)
                ->get()->getResultArray();

            foreach ($activeTransitions as $t) {
                $mod = strtoupper(trim($t['module']));
                $tNcId = (int)$t['nc_id'];
                $isStillPending = false;

                if ($mod === 'OE') {
                    $oeRow = $db->table('alert_final_structured_audit_details d')
                        ->select('d.status, sa.reaudit')
                        ->join('alert_final_structured_audit sa', 'sa.structured_audit_id = d.structured_audit_id')
                        ->where('d.audit_details_id', $tNcId)
                        ->get()->getRowArray();
                    if ($oeRow && in_array((int)$oeRow['status'], [6, 2]) && (int)$oeRow['reaudit'] === 0) {
                        $isStillPending = true;
                    }
                } elseif ($mod === 'HSE') {
                    $hseRow = $db->table('alert_hse_audit_details')
                        ->select('nc_status, finding')
                        ->where('id', $tNcId)
                        ->get()->getRowArray();
                    if ($hseRow && in_array((int)$hseRow['nc_status'], [5, 2]) && $hseRow['finding'] === 'NO') {
                        $isStillPending = true;
                    }
                } elseif ($mod === 'GEMBA') {
                    $gRow = $db->table('alert_gemba_audits')
                        ->select('nc_status')
                        ->where('gemba_sr_no', $tNcId)
                        ->get()->getRowArray();
                    if ($gRow && in_array((int)$gRow['nc_status'], [5, 2])) {
                        $isStillPending = true;
                    }
                }

                if (!$isStillPending) {
                    $db->table('alert_nc_status_transition')
                        ->where('id', $t['id'])
                        ->update(['is_current' => 0, 'exited_at' => $now]);
                    $staleClosed++;
                }
            }

        } catch (\Throwable $e) {
            log_message('error', 'syncExistingPendingNcTransitions error: ' . $e->getMessage());
        }

        return [
            'oe_synced' => $oeCount,
            'hse_synced' => $hseCount,
            'gemba_synced' => $gembaCount,
            'stale_closed' => $staleClosed
        ];
    }
}
