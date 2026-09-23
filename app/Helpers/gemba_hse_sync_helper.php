<?php

if (!function_exists('execute_hse_gemba_nc_action')) {
    /**
     * Executes the identical approval workflow for both HSE NC Tracker and Gemba HSE NC Tracker.
     * Updates both tables in a single transaction.
     *
     * @param int $id The alert_hse_audit_details ID
     * @param string $action The action requested (working, review, auditor, auditor_direct_close, closed, 0, submit_for_review, save)
     * @param array $postData Additional data like close_date, nc_remark, nc_after_photo, rejection_reason,
     *                        closed_remarks, closed_uploaded_file, closure_status
     * @param int|null $userId User ID initiating action
     * @param string $userName User Name initiating action
     * @return array ['status' => int, 'message' => string, 'new_status' => int|null, 'old_status' => int|null, 'gemba_sr_no' => int|null]
     */
    function execute_hse_gemba_nc_action($id, $action, $postData = [], $userId = null, $userName = null)
    {
        helper(['designation_acl', 'hse_acl']);
        $db = db_connect();
        
        $closeDate = $postData['close_date'] ?? null;
        $now = date('Y-m-d H:i:s');
        
        if (!empty($userName)) {
            $actionBy = $userName;
        } elseif (isSuperAdmin()) {
            $actionBy = '0'; // Super Admin save as 0 if name is empty
        } else {
            $actionBy = null; // other empty save null
        }

        /* ================= FETCH RECORD ================= */
        $current = $db->table('alert_hse_audit_details d')
            ->select('d.*, cm.account_manager, cm.cluster, m.audit_no')
            ->join('alert_hse_audit_master m', 'm.hse_audit_id = d.hse_audit_id', 'left')
            ->join('alert_hse_client_master cm', 'cm.client_name = m.client_name AND cm.status = 1', 'left')
            ->where('d.id', $id)
            ->get()
            ->getRowArray();

        if (!$current) {
            return ['status' => 0, 'message' => 'Record not found'];
        }

        $currentStatus = (int) $current['nc_status'];

        /* ❌ READ ONLY */
        if (isHigherAuthority()) {
            return ['status' => 0, 'message' => 'Read only access'];
        }

        /* 🔒 ACCOUNT MANAGER LOCK */
        $isAssignedAM = false;
        if (isAccountManager()) {
            if (!empty($current['account_manager']) && strtolower(trim($current['account_manager'])) === strtolower(trim($userName))) {
                $isAssignedAM = true;
            }
        }

        /* 🔒 CLUSTER MANAGER LOCK */
        $isAssignedCM = false;
        if (isClusterManager()) {
            $assignedClusters = getClusterManagerAssignedClusterHSE();
            if (in_array($current['cluster'], $assignedClusters)) {
                $isAssignedCM = true;
            }
        }

        $isSuperAdmin = isSuperAdmin();
        $isAuditor = isAuditor();

        $update = [];

        /* ====================================================
        🔥 ACTION BASED FLOW (from HSE NC Tracker)
        ==================================================== */
        if ($action === '0') {
            // Reset to Open / Rejected
            if ($isSuperAdmin || $isAuditor || $isAssignedCM || $isAssignedAM) {
                $update['nc_status'] = 0;
                $update['nc_rejected_by'] = $actionBy;
                $update['update_date'] = $now;
                if (!empty($postData['rejection_reason'])) {
                    $update['nc_remark'] = "Rejected Reason: " . $postData['rejection_reason'] . "\n" . ($current['nc_remark'] ?? '');
                }
            }
        } else if ($action === 'submit_for_review' || $action === 'save_wip') {
            // For details update and submission
            if (isset($postData['nc_remark'])) {
                $update['nc_remark'] = $postData['nc_remark'];
                $update['working_remarks'] = $postData['nc_remark'];
            }
            if (isset($postData['nc_after_photo'])) {
                $update['nc_after_photo'] = $postData['nc_after_photo'];
                $update['working_uploaded_file'] = $postData['nc_after_photo'];
            }
            $update['working_date'] = $now;
            
            if ($action === 'submit_for_review') {
                if ($isAuditor || $isSuperAdmin) {
                    $update['nc_status'] = 2; // Auditor Review
                    $update['nc_auditor_reviewed_by'] = $actionBy;
                    $update['nc_auditor_reviewed_date'] = $now;
                    $update['update_date'] = $now;
                } else {
                    $update['nc_status'] = 5; // Cluster Review
                    $update['nc_cluster_reviewed_by'] = $actionBy;
                    $update['nc_cluster_reviewed_date'] = $now;
                    $update['update_date'] = $now;
                }
            } else {
                // save_wip
                $update['nc_status'] = 1;
                $update['nc_worked_by'] = $actionBy;
                $update['update_date'] = $now;
            }
        } else {
            switch ($currentStatus) {
                case 0:
                case 6: // In case of rejected
                    if ($action === 'working' && ($isSuperAdmin || $isAuditor || $isAssignedCM || $isAssignedAM)) {
                        $update['nc_status'] = 1;
                        $update['nc_worked_by'] = $actionBy;
                        $update['update_date'] = $now;
                    }
                    break;
                case 1:
                    if ($action === 'review') {
                        if ($isSuperAdmin || $isAssignedCM || $isAssignedAM) {
                            $update['nc_status'] = 5;
                            $update['nc_cluster_reviewed_by'] = $actionBy;
                            $update['nc_cluster_reviewed_date'] = $now;
                            $update['update_date'] = $now;
                        } elseif ($isAuditor) {
                            $update['nc_status'] = 2;
                            $update['nc_auditor_reviewed_by'] = $actionBy;
                            $update['nc_auditor_reviewed_date'] = $now;
                            $update['update_date'] = $now;
                        }
                    }
                    break;
                case 5:
                    if ($action === 'auditor' && ($isSuperAdmin || $isAssignedCM || $isAssignedAM)) {
                        $update['nc_status'] = 2;
                        $update['nc_auditor_reviewed_by'] = $actionBy;
                        $update['nc_auditor_reviewed_date'] = $now;
                        $update['update_date'] = $now;
                    }
                    if ($action === 'auditor_direct_close' && $isAuditor) {
                        $update['nc_status'] = 3;
                        $update['nc_cluster_reviewed_by'] = $actionBy;
                        $update['nc_cluster_reviewed_date'] = $now;
                        $update['nc_auditor_reviewed_by'] = $actionBy;
                        $update['nc_auditor_reviewed_date'] = $now;
                        // nc_closed_by stores the user NAME; nc_closed_by_user stores the numeric user ID
                        $update['nc_closed_by'] = $actionBy;
                        $update['nc_closed_by_user'] = $userId; // fix: store numeric ID, not name string
                        $update['nc_closed_date'] = !empty($closeDate) ? $closeDate : $now;
                        if (isset($postData['closed_remarks'])) {
                            $update['closed_remarks'] = $postData['closed_remarks'];
                        }
                        if (isset($postData['closed_uploaded_file'])) {
                            $update['closed_uploaded_file'] = $postData['closed_uploaded_file'];
                        }
                        $update['update_date'] = $now;
                    }
                    break;
                case 2:
                    if ($action === 'closed' && ($isSuperAdmin || $isAuditor || $isAssignedCM || $isAssignedAM)) {
                        $update['nc_status'] = 3;
                        // nc_closed_by stores the user NAME; nc_closed_by_user stores the numeric user ID
                        $update['nc_closed_by'] = $actionBy;
                        $update['nc_closed_by_user'] = $userId; // fix: store numeric ID, not name string
                        $update['nc_closed_date'] = !empty($closeDate) ? $closeDate : $now;
                        if (isset($postData['closed_remarks'])) {
                            $update['closed_remarks'] = $postData['closed_remarks'];
                        }
                        if (isset($postData['closed_uploaded_file'])) {
                            $update['closed_uploaded_file'] = $postData['closed_uploaded_file'];
                        }
                        $update['update_date'] = $now;
                    }
                    break;
                case 3:
                    return ['status' => 0, 'message' => 'Already Closed'];
                case 4:
                    if ($action === 'working') {
                        $update['nc_status'] = 1;
                        $update['update_date'] = $now;
                    }
                    break;
            }
        }
        
        // If it's a save_details update without a status change (e.g. action="save_draft" or action is null)
        if (empty($update) && ($action === 'save' || empty($action))) {
            if (isset($postData['nc_remark'])) {
                $update['nc_remark'] = $postData['nc_remark'];
            }
            if (isset($postData['nc_after_photo'])) {
                $update['nc_after_photo'] = $postData['nc_after_photo'];
            }
            if (!empty($update)) {
                 $update['update_date'] = $now;
            }
        }

        if (empty($update)) {
            return ['status' => 0, 'message' => 'Unauthorized action or no changes'];
        }

        /* ================= SYNC GEMBA DATA PREP ================= */
        $gembaRow = $db->table('alert_gemba_audits')
            ->where('hse_audit_id', $current['hse_audit_id'])
            ->where('hse_detail_id', $id)
            ->where('source_module', 'hse_audit')
            ->get()
            ->getRowArray();

        $gembaUpdate = [];
        if ($gembaRow) {
            $gembaUpdateStr = $userName;
            
            if (array_key_exists('nc_status', $update)) $gembaUpdate['nc_status'] = $update['nc_status'];
            if (array_key_exists('nc_remark', $update)) $gembaUpdate['nc_remark'] = $update['nc_remark'];
            if (array_key_exists('nc_after_photo', $update)) $gembaUpdate['nc_after_photo'] = $update['nc_after_photo'];
            if (array_key_exists('working_remarks', $update)) $gembaUpdate['working_remarks'] = $update['working_remarks'];
            if (array_key_exists('working_uploaded_file', $update)) $gembaUpdate['working_uploaded_file'] = $update['working_uploaded_file'];
            if (array_key_exists('working_date', $update)) $gembaUpdate['working_date'] = $update['working_date'];
            if (array_key_exists('closed_remarks', $update)) $gembaUpdate['closed_remarks'] = $update['closed_remarks'];
            if (array_key_exists('closed_uploaded_file', $update)) $gembaUpdate['closed_uploaded_file'] = $update['closed_uploaded_file'];
            if (!empty($postData['rejection_reason'])) $gembaUpdate['rejection_reason'] = $postData['rejection_reason'];
            
            if ($action === 'submit_for_review' || $action === 'save_wip') {
                $gembaUpdate['point_status'] = 'WIP';
            }

            if (array_key_exists('nc_worked_by', $update)) $gembaUpdate['nc_worked_by'] = $actionBy;
            if (array_key_exists('nc_cluster_reviewed_by', $update)) {
                $gembaUpdate['nc_cluster_reviewed_by'] = $actionBy;
                $gembaUpdate['nc_cluster_reviewed_date'] = $update['nc_cluster_reviewed_date'] ?? $now;
            }
            if (array_key_exists('nc_auditor_reviewed_by', $update)) {
                $gembaUpdate['nc_auditor_reviewed_by'] = $actionBy;
                $gembaUpdate['nc_auditor_reviewed_date'] = $update['nc_auditor_reviewed_date'] ?? $now;
            }
            if (array_key_exists('nc_rejected_by', $update)) {
                $gembaUpdate['nc_rejected_by'] = $actionBy;
                $gembaUpdate['nc_rejected_date'] = $update['update_date'] ?? $now;
            }
            if (array_key_exists('nc_closed_by', $update)) {
                $gembaUpdate['nc_closed_by'] = $actionBy;
                $gembaUpdate['nc_closed_date'] = $update['nc_closed_date'] ?? $now;
            }

            if (isset($update['nc_status']) && (int)$update['nc_status'] === 3) {
                // Use the actual nc_closed_date from the form (not $now) as the closed timestamp
                $closedTs = isset($update['nc_closed_date']) ? strtotime($update['nc_closed_date']) : strtotime($now);

                // closure_status carries the selected value (Closed / Hold-review with Client / Excluded)
                // point_status must ALWAYS be 'Closed' when nc_status = 3
                // point_category must ALWAYS be 'Closed Category' when nc_status = 3
                $closureStatus = $postData['closure_status'] ?? 'Closed';
                $gembaUpdate['point_status']    = 'Closed';           // always 'Closed'
                $gembaUpdate['point_category']  = 'Closed Category';  // always 'Closed Category'
                $gembaUpdate['closure_status']  = $closureStatus;     // selected value

                // Closure date fields – derived from actual form-submitted close date
                $gembaUpdate['closed_date']               = date('Y-m-d', $closedTs);
                $gembaUpdate['nc_closed_date']            = date('Y-m-d', $closedTs);
                $gembaUpdate['nc_closed_by']              = $gembaUpdateStr; // user name
                $gembaUpdate['qhse_remarks']              = $update['nc_remark'] ?? ($current['nc_remark'] ?? 'Closed via HSE NC Tracker');
                $gembaUpdate['updated_by']                = $gembaUpdateStr;
                $gembaUpdate['update_date']               = $now;

                // Week / month / year computed from actual closed date
                $gembaUpdate['weeknum_closed']            = date('W', $closedTs);
                $gembaUpdate['month_closed']              = date('Y-m-01', $closedTs);
                $gembaUpdate['year_month_closed']         = date('Y-m', $closedTs);
                $gembaUpdate['weeknum_yearmonth_closed']  = date('Y-m', $closedTs) . '-W' . date('W', $closedTs);

                // Closing remarks and proof – forwarded from form
                if (isset($update['closed_remarks'])) {
                    $gembaUpdate['closed_remarks'] = $update['closed_remarks'];
                }
                if (isset($update['closed_uploaded_file'])) {
                    $gembaUpdate['closed_uploaded_file'] = $update['closed_uploaded_file'];
                }

                // Ageing days – computed from audit_report_date to actual closed date
                if (!empty($gembaRow['audit_report_date'])) {
                    $reportTs = strtotime($gembaRow['audit_report_date']);
                    $days = floor(($closedTs - $reportTs) / (60 * 60 * 24));
                    $gembaUpdate['ageing_days'] = $days >= 0 ? $days : 0;
                    if ($gembaUpdate['ageing_days'] <= 30) $gembaUpdate['age_bracket'] = '<=30';
                    elseif ($gembaUpdate['ageing_days'] <= 60) $gembaUpdate['age_bracket'] = '31-60';
                    elseif ($gembaUpdate['ageing_days'] <= 90) $gembaUpdate['age_bracket'] = '61-90';
                    else $gembaUpdate['age_bracket'] = '>90';
                }
            } else if (isset($update['nc_status']) && (int)$update['nc_status'] === 0) {
                 $gembaUpdate['point_status'] = 'Open';
                 $gembaUpdate['closure_status'] = 'Open';
                 $gembaUpdate['closed_date'] = null;
                 if (!empty($postData['rejection_reason'])) {
                     $gembaUpdate['qhse_remarks'] = 'Rejected Reason: ' . $postData['rejection_reason'];
                 } else {
                     $gembaUpdate['qhse_remarks'] = null;
                 }
                 $gembaUpdate['updated_by'] = $userName;
                 $gembaUpdate['update_date'] = $now;
                 $gembaUpdate['ageing_days'] = null;
                 $gembaUpdate['age_bracket'] = null;
                 $gembaUpdate['weeknum_closed'] = null;
                 $gembaUpdate['month_closed'] = null;
                 $gembaUpdate['year_month_closed'] = null;
                 $gembaUpdate['weeknum_yearmonth_closed'] = null;
                 if (!empty($update['nc_rejected_date'])) $gembaUpdate['nc_rejected_date'] = $update['nc_rejected_date'];
            }
        }

        /* ================= DATABASE TRANSACTION ================= */
        $db->transStart();
        
        $db->table('alert_hse_audit_details')->where('id', $id)->update($update);
        
        if ($gembaRow && !empty($gembaUpdate)) {
            $db->table('alert_gemba_audits')->where('gemba_sr_no', $gembaRow['gemba_sr_no'])->update($gembaUpdate);
            
            // Log audit trail for Gemba
            if (isset($update['nc_status']) && $currentStatus != $update['nc_status']) {
                $auditRemarks = 'Status changed to ' . $update['nc_status'];
                if ($action === '0' && !empty($postData['rejection_reason'])) {
                    $auditRemarks = 'Rejected. Reason: ' . $postData['rejection_reason'];
                }
                $db->table('alert_gemba_nc_audit_trail')->insert([
                    'gemba_audit_id' => $gembaRow['gemba_sr_no'],
                    'action_by' => $userName,
                    'action_date' => $now,
                    'status_from' => $currentStatus,
                    'status_to' => $update['nc_status'],
                    'remarks' => $auditRemarks
                ]);
            }
        }
        
        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['status' => 0, 'message' => 'Transaction failed'];
        }

        // Log NC action history
        $newStat = isset($update['nc_status']) ? (string)$update['nc_status'] : (string)$currentStatus;
        $actType = 'ACTION_UPDATED';
        if ($action === '0') {
            $actType = (isClusterManager()) ? 'REJECTED_BY_CM' : ((isAuditor()) ? 'REJECTED_BY_AUDITOR' : 'REJECTED');
        } elseif ($action === 'working' || $action === 'save_wip') {
            $actType = 'WORK_STARTED';
        } elseif ($action === 'submit_for_review' || $action === 'review') {
            $actType = (isAuditor() || isSuperAdmin()) ? 'FORWARDED_TO_AUDITOR' : 'SUBMITTED_TO_CM';
        } elseif ($action === 'auditor') {
            $actType = 'FORWARDED_TO_AUDITOR';
        } elseif ($action === 'closed' || $action === 'auditor_direct_close') {
            $actType = 'CLOSED';
        }
        if (!empty($postData['nc_after_photo']) || !empty($postData['closed_uploaded_file'])) {
            $actType = ($actType === 'CLOSED' || $actType === 'SUBMITTED_TO_CM' || $actType === 'FORWARDED_TO_AUDITOR') ? $actType : 'EVIDENCE_UPLOADED';
        }

        $rem = $postData['closed_remarks'] ?? ($postData['nc_remark'] ?? ($postData['rejection_reason'] ?? null));
        $att = $postData['closed_uploaded_file'] ?? ($postData['nc_after_photo'] ?? null);

        logNcActionHistory(
            'HSE',
            (int)$id,
            $actType,
            (string)$currentStatus,
            $newStat,
            $rem,
            $att,
            (int)($current['hse_audit_id'] ?? 0),
            $current['client_name'] ?? null
        );

        if ($gembaRow) {
            logNcActionHistory(
                'GEMBA',
                (int)$gembaRow['gemba_sr_no'],
                $actType,
                (string)$currentStatus,
                $newStat,
                $rem,
                $att,
                (int)($current['hse_audit_id'] ?? 0),
                $current['client_name'] ?? null
            );
        }

        return [
            'status' => 1, 
            'message' => 'Update successful',
            'old_status' => $currentStatus,
            'new_status' => $update['nc_status'] ?? null,
            'gemba_sr_no' => $gembaRow ? $gembaRow['gemba_sr_no'] : null
        ];
    }
}
