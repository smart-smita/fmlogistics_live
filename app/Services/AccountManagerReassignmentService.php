<?php

namespace App\Services;

use Config\Database;

/**
 * AccountManagerReassignmentService
 * 
 * Centralized service to handle Cluster Manager and Account Manager reassignment across:
 * 1. OE Audits (alert_final_structured_audit, alert_normal_audit, alert_final_structured_audit_details)
 * 2. HSE Audits (alert_hse_audit_master, alert_hse_audit_details)
 * 3. Gemba Audits (alert_gemba_audits)
 * 
 * CORE RULES:
 * A. Closed & Force-Closed Points/Audits - PRESERVE old Cluster Manager and Account Manager.
 *    Do not replace in historical closed or force-closed records.
 * B. Active/In-Progress Points/Audits - REASSIGN to new Cluster Manager and/or Account Manager
 *    so they can manage open/review NCs.
 * C. Mandatory Reliable Backup - Snapshot affected records into backup tables BEFORE update.
 * D. Complete History Logging - Record history with old/new CM, old/new AM, affected IDs, timestamps, admin info.
 * E. Prevent Duplicate History - Repeated saves with no manager changes generate no history/backup records.
 * F. Data Isolation & Atomic Transactions - Protect unrelated clients/sites; rollback on error.
 */
class AccountManagerReassignmentService
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? Database::connect('default');
    }

    /**
     * Reassign Cluster Manager and/or Account Manager for an OE Client.
     * 
     * @param int $clientId
     * @param string $oldAccountManager
     * @param string $newAccountManager
     * @param string $clientName
     * @param string|null $location
     * @param int|null $userId
     * @param string|null $userName
     * @param string $reason
     * @param array $extraOldData Optional old client record details for history logging
     * @param string|null $oldCluster
     * @param string|null $newCluster
     * @return array Result summary
     */
    public function reassignOeClient(
        int $clientId,
        string $oldAccountManager,
        string $newAccountManager,
        string $clientName,
        ?string $location = null,
        ?int $userId = null,
        ?string $userName = null,
        string $reason = 'Reassigned via Client Master update',
        array $extraOldData = [],
        ?string $oldCluster = null,
        ?string $newCluster = null
    ): array {
        $oldAccountManager = trim($oldAccountManager);
        $newAccountManager = trim($newAccountManager);
        $oldCluster = trim($oldCluster ?? ($extraOldData['cluster'] ?? ''));
        $newCluster = trim($newCluster ?? ($extraOldData['new_cluster'] ?? ($extraOldData['cluster'] ?? '')));
        $clientName = trim($clientName);
        $location = trim($location ?? '');
        $userId = $userId ?? 0;
        $userName = $userName ?? 'System';

        $isAmChanged = ($oldAccountManager !== $newAccountManager && $newAccountManager !== '');
        $isCmChanged = ($oldCluster !== $newCluster && $newCluster !== '');

        if (!$isAmChanged && !$isCmChanged) {
            return [
                'status'         => 1,
                'message'        => 'No Cluster Manager or Account Manager change detected.',
                'eligible_count' => 0,
                'closed_count'   => 0,
                'total_audits'   => 0
            ];
        }

        // 1. Identify all matching OE Audits for this client/location
        $escapedName = $this->db->escape($clientName);
        $whereClauses = ["TRIM(client_name) = {$escapedName}"];
        if ($location !== '') {
            $escapedLoc = $this->db->escape($location);
            $whereClauses[] = "TRIM(location) = {$escapedLoc}";
            $whereClauses[] = "TRIM(client_name) = {$escapedLoc}";
            $whereClauses[] = "TRIM(location) = {$escapedName}";
        }
        $matchWhere = '(' . implode(' OR ', array_unique($whereClauses)) . ')';

        $oeAudits = $this->db->query("
            SELECT * FROM alert_final_structured_audit 
            WHERE {$matchWhere}
        ")->getResultArray();

        $allAuditIds = array_column($oeAudits, 'structured_audit_id');
        $eligibleAuditIds = [];
        $closedAuditIds = [];

        // 2. Classify each OE audit as Eligible (Active) vs Closed based on NC status & header status
        foreach ($oeAudits as $audit) {
            $auditId = (int)$audit['structured_audit_id'];

            // Header level check: Status 3 = Closed, Status 5 = Forced Closed
            if ((int)($audit['status'] ?? 0) === 3 || (int)($audit['status'] ?? 0) === 5) {
                $closedAuditIds[] = $auditId;
                continue;
            }

            // Query active NCs for this audit:
            // Finding 'NO' and status NOT IN (3, 5) -> 3 is Closed, 5 is Forced Close
            $activeNcCount = $this->db->table('alert_final_structured_audit_details')
                ->where('structured_audit_id', $auditId)
                ->where('audit_finding', 'NO')
                ->whereNotIn('status', [3, 5])
                ->countAllResults();

            if ($activeNcCount > 0) {
                // Has open, working, or review NCs -> Eligible for manager reassignment
                $eligibleAuditIds[] = $auditId;
            } else {
                // All NCs are closed/forced-close, or 0 NCs (completed audit) -> Preserve historical managers
                $closedAuditIds[] = $auditId;
            }
        }

        // Count total active NCs across eligible audits
        $affectedNcCount = 0;
        if (!empty($eligibleAuditIds)) {
            $affectedNcCount = $this->db->table('alert_final_structured_audit_details')
                ->whereIn('structured_audit_id', $eligibleAuditIds)
                ->where('audit_finding', 'NO')
                ->whereNotIn('status', [3, 5])
                ->countAllResults();
        }

        $now = date('Y-m-d H:i:s');
        $oeHistId = null;

        $affectedFieldsArr = [];
        if ($isCmChanged) $affectedFieldsArr[] = 'cluster';
        if ($isAmChanged) $affectedFieldsArr[] = 'account_manager';
        $affectedFields = implode(', ', $affectedFieldsArr);

        if ($isCmChanged && $isAmChanged) {
            $remark = 'Cluster Manager & Account Manager Changed (Preserving Closed Audits)';
        } elseif ($isCmChanged) {
            $remark = 'Cluster Manager Changed (Preserving Closed Audits)';
        } else {
            $remark = 'Account Manager Changed (Preserving Closed Audits)';
        }

        // 3. Mandatory Centralized OE_history logging
        $oeHistTable = $this->getExistingTableName(['alert_oe_history', 'OE_history']);
        if ($oeHistTable) {
            $historyData = [
                'client_id'             => $clientId,
                'site_id'               => $extraOldData['site_id'] ?? null,
                'change_type'           => 'ALLOCATION',
                'remark'                => $remark,
                'reason'                => $reason,
                'prev_client_name'      => $clientName,
                'prev_location'         => $extraOldData['location'] ?? $location,
                'prev_region'           => $extraOldData['region'] ?? null,
                'prev_cluster'          => $oldCluster ?: ($extraOldData['cluster'] ?? null),
                'prev_account_manager'  => $oldAccountManager ?: ($extraOldData['account_manager'] ?? null),
                'prev_email'            => $extraOldData['email'] ?? null,
                'prev_status'           => isset($extraOldData['status']) ? (int)$extraOldData['status'] : null,
                'new_client_name'       => $clientName,
                'new_location'          => $extraOldData['location'] ?? $location,
                'new_region'            => $extraOldData['region'] ?? null,
                'new_cluster'           => $isCmChanged ? $newCluster : ($oldCluster ?: ($extraOldData['cluster'] ?? null)),
                'new_account_manager'   => $isAmChanged ? $newAccountManager : ($oldAccountManager ?: ($extraOldData['account_manager'] ?? null)),
                'new_email'             => $extraOldData['email'] ?? null,
                'new_status'            => isset($extraOldData['status']) ? (int)$extraOldData['status'] : null,
                'affected_fields'       => $affectedFields,
                'affected_audit_count'  => count($eligibleAuditIds),
                'affected_nc_count'     => $affectedNcCount,
                'affected_audit_ids'    => !empty($eligibleAuditIds) ? json_encode($eligibleAuditIds) : null,
                'changed_by_user_id'    => $userId,
                'changed_by_user_name'  => $userName,
                'created_at'            => $now
            ];
            $this->db->table($oeHistTable)->insert($historyData);
            $oeHistId = $this->db->insertID();
        }

        // 4. Mandatory Backup into OE_audit_backup BEFORE any update
        $oeBackupTable = $this->getExistingTableName(['alert_oe_audit_backup', 'OE_audit_backup']);
        if (!empty($oeAudits) && $oeBackupTable) {
            $backupCols = $this->getTableColumns($oeBackupTable);

            foreach ($oeAudits as $oeRow) {
                $auditId = (int)$oeRow['structured_audit_id'];
                $isEligible = in_array($auditId, $eligibleAuditIds, true);

                $backupRow = $oeRow;
                unset($backupRow['backup_id']);
                $backupRow['history_id'] = $oeHistId;
                $backupRow['backed_up_at'] = $now;

                if (in_array('old_cluster', $backupCols, true)) {
                    $backupRow['old_cluster'] = $oeRow['cluster_name'] ?: $oldCluster;
                }
                if (in_array('new_cluster', $backupCols, true)) {
                    $backupRow['new_cluster'] = $isCmChanged ? $newCluster : $oeRow['cluster_name'];
                }
                if (in_array('old_account_manager', $backupCols, true)) {
                    $backupRow['old_account_manager'] = $oeRow['client_manager_name'] ?: $oldAccountManager;
                }
                if (in_array('new_account_manager', $backupCols, true)) {
                    $backupRow['new_account_manager'] = $isAmChanged ? $newAccountManager : $oeRow['client_manager_name'];
                }
                if (in_array('reassignment_status', $backupCols, true)) {
                    $backupRow['reassignment_status'] = $isEligible ? 'REASSIGNED_ACTIVE' : 'PRESERVED_CLOSED';
                }

                $filteredRow = array_intersect_key($backupRow, array_flip($backupCols));
                $this->db->table($oeBackupTable)->insert($filteredRow);
            }
        }

        // 5. Update ONLY eligible active/open audits; KEEP closed & force-closed audits untouched!
        if (!empty($eligibleAuditIds)) {
            $updateData = [];
            if ($isCmChanged) {
                $updateData['cluster_name'] = $newCluster;
            }
            if ($isAmChanged) {
                $updateData['client_manager_name'] = $newAccountManager;
            }

            if (!empty($updateData)) {
                $this->db->table('alert_final_structured_audit')
                    ->whereIn('structured_audit_id', $eligibleAuditIds)
                    ->update($updateData);

                // Update alert_normal_audit with the SAME active/open status filtering
                if ($this->db->tableExists('alert_normal_audit')) {
                    $normalAudits = $this->db->query("
                        SELECT * FROM alert_normal_audit 
                        WHERE {$matchWhere}
                    ")->getResultArray();

                    $eligibleNormalIds = [];
                    foreach ($normalAudits as $normAudit) {
                        $normId = (int)$normAudit['normal_audit_id'];
                        if ((int)($normAudit['status'] ?? 0) === 3 || (int)($normAudit['status'] ?? 0) === 5) {
                            continue;
                        }
                        $activeNormNcCount = 0;
                        if ($this->db->tableExists('alert_normal_audit_details')) {
                            $activeNormNcCount = $this->db->table('alert_normal_audit_details')
                                ->where('normal_audit_id', $normId)
                                ->where('audit_finding', 'NO')
                                ->whereNotIn('status', [3, 5])
                                ->countAllResults();
                        }
                        if ($activeNormNcCount > 0) {
                            $eligibleNormalIds[] = $normId;
                        }
                    }

                    if (!empty($eligibleNormalIds)) {
                        $this->db->table('alert_normal_audit')
                            ->whereIn('normal_audit_id', $eligibleNormalIds)
                            ->update($updateData);
                    }
                }
            }
        }

        return [
            'status'             => 1,
            'message'            => 'OE manager reassigned successfully with status filtering.',
            'history_id'         => $oeHistId,
            'total_audits'       => count($oeAudits),
            'eligible_count'     => count($eligibleAuditIds),
            'closed_count'       => count($closedAuditIds),
            'eligible_audit_ids' => $eligibleAuditIds,
            'closed_audit_ids'   => $closedAuditIds
        ];
    }

    /**
     * Reassign Cluster Manager and/or Account Manager for an HSE Client (and associated Gemba records).
     * 
     * @param int $clientId
     * @param string $oldAccountManager
     * @param string $newAccountManager
     * @param string $clientName
     * @param string|null $location
     * @param int|null $userId
     * @param string|null $userName
     * @param string $reason
     * @param array $extraOldData Optional old HSE client details for history logging
     * @param string|null $oldCluster
     * @param string|null $newCluster
     * @return array Result summary
     */
    public function reassignHseClient(
        int $clientId,
        string $oldAccountManager,
        string $newAccountManager,
        string $clientName,
        ?string $location = null,
        ?int $userId = null,
        ?string $userName = null,
        string $reason = 'Reassigned via HSE Client Master update',
        array $extraOldData = [],
        ?string $oldCluster = null,
        ?string $newCluster = null
    ): array {
        $oldAccountManager = trim($oldAccountManager);
        $newAccountManager = trim($newAccountManager);
        $oldCluster = trim($oldCluster ?? ($extraOldData['cluster'] ?? ''));
        $newCluster = trim($newCluster ?? ($extraOldData['new_cluster'] ?? ($extraOldData['cluster'] ?? '')));
        $clientName = trim($clientName);
        $location = trim($location ?? '');
        $userId = $userId ?? 0;
        $userName = $userName ?? 'System';

        $isAmChanged = ($oldAccountManager !== $newAccountManager && $newAccountManager !== '');
        $isCmChanged = ($oldCluster !== $newCluster && $newCluster !== '');

        if (!$isAmChanged && !$isCmChanged) {
            return [
                'status'               => 1,
                'message'              => 'No Cluster Manager or Account Manager change detected.',
                'hse_eligible_count'   => 0,
                'hse_closed_count'     => 0,
                'gemba_eligible_count' => 0,
                'gemba_closed_count'   => 0
            ];
        }

        $now = date('Y-m-d H:i:s');
        $escapedName = $this->db->escape($clientName);
        $whereClauses = ["TRIM(client_name) = {$escapedName}"];
        if ($location !== '') {
            $escapedLoc = $this->db->escape($location);
            $whereClauses[] = "TRIM(location) = {$escapedLoc}";
            $whereClauses[] = "TRIM(client_name) = {$escapedLoc}";
            $whereClauses[] = "TRIM(location) = {$escapedName}";
        }
        $matchWhere = '(' . implode(' OR ', array_unique($whereClauses)) . ')';

        $affectedFieldsArr = [];
        if ($isCmChanged) $affectedFieldsArr[] = 'cluster';
        if ($isAmChanged) $affectedFieldsArr[] = 'account_manager';
        $affectedFields = implode(', ', $affectedFieldsArr);

        if ($isCmChanged && $isAmChanged) {
            $hseRemark = 'Cluster Manager & Account Manager Changed (Preserving Closed Audits)';
            $gembaRemark = 'Cluster Manager & Account Manager Changed (Preserving Closed Points)';
        } elseif ($isCmChanged) {
            $hseRemark = 'Cluster Manager Changed (Preserving Closed Audits)';
            $gembaRemark = 'Cluster Manager Changed (Preserving Closed Points)';
        } else {
            $hseRemark = 'Account Manager Changed (Preserving Closed Audits)';
            $gembaRemark = 'Account Manager Changed (Preserving Closed Points)';
        }

        // =================================================================
        // A. HSE AUDITS (alert_hse_audit_master & alert_hse_audit_details)
        // =================================================================
        $hseAudits = $this->db->query("
            SELECT * FROM alert_hse_audit_master 
            WHERE {$matchWhere}
        ")->getResultArray();

        $allHseAuditIds = array_column($hseAudits, 'hse_audit_id');
        $eligibleHseAuditIds = [];
        $closedHseAuditIds = [];

        foreach ($hseAudits as $hAudit) {
            $hId = (int)$hAudit['hse_audit_id'];

            // Header level check: Status 3 = Closed
            if ((int)($hAudit['status'] ?? 0) === 3) {
                $closedHseAuditIds[] = $hId;
                continue;
            }

            // Query active NCs for this HSE audit:
            // Finding 'NO' and nc_status != 3 (0=Open, 1=Working, 5=Cluster Review, 2=Auditor Review)
            $activeHseNcCount = $this->db->table('alert_hse_audit_details')
                ->where('hse_audit_id', $hId)
                ->where('finding', 'NO')
                ->where('nc_status !=', 3)
                ->countAllResults();

            if ($activeHseNcCount > 0) {
                $eligibleHseAuditIds[] = $hId;
            } else {
                $closedHseAuditIds[] = $hId;
            }
        }

        $affectedHseNcCount = 0;
        if (!empty($eligibleHseAuditIds)) {
            $affectedHseNcCount = $this->db->table('alert_hse_audit_details')
                ->whereIn('hse_audit_id', $eligibleHseAuditIds)
                ->where('finding', 'NO')
                ->where('nc_status !=', 3)
                ->countAllResults();
        }

        $hseHistId = null;
        $hseHistTable = $this->getExistingTableName(['alert_hse_history', 'HSE_history']);
        if ($hseHistTable) {
            $historyData = [
                'client_id'             => $clientId,
                'site_id'               => $extraOldData['site_id'] ?? null,
                'change_type'           => 'ALLOCATION',
                'remark'                => $hseRemark,
                'reason'                => $reason,
                'prev_client_name'      => $clientName,
                'prev_category'         => $extraOldData['category'] ?? null,
                'prev_sub_category'     => $extraOldData['sub_category'] ?? null,
                'prev_location'         => $extraOldData['location'] ?? $location,
                'prev_region'           => $extraOldData['region'] ?? null,
                'prev_cluster'          => $oldCluster ?: ($extraOldData['cluster'] ?? null),
                'prev_account_manager'  => $oldAccountManager ?: ($extraOldData['account_manager'] ?? null),
                'prev_email'            => $extraOldData['email'] ?? null,
                'prev_status'           => isset($extraOldData['status']) ? (int)$extraOldData['status'] : null,
                'new_client_name'       => $clientName,
                'new_category'          => $extraOldData['category'] ?? null,
                'new_sub_category'      => $extraOldData['sub_category'] ?? null,
                'new_location'          => $extraOldData['location'] ?? $location,
                'new_region'            => $extraOldData['region'] ?? null,
                'new_cluster'           => $isCmChanged ? $newCluster : ($oldCluster ?: ($extraOldData['cluster'] ?? null)),
                'new_account_manager'   => $isAmChanged ? $newAccountManager : ($oldAccountManager ?: ($extraOldData['account_manager'] ?? null)),
                'new_email'             => $extraOldData['email'] ?? null,
                'new_status'            => isset($extraOldData['status']) ? (int)$extraOldData['status'] : null,
                'affected_fields'       => $affectedFields,
                'affected_audit_count'  => count($eligibleHseAuditIds),
                'affected_nc_count'     => $affectedHseNcCount,
                'affected_audit_ids'    => !empty($eligibleHseAuditIds) ? json_encode($eligibleHseAuditIds) : null,
                'changed_by_user_id'    => $userId,
                'changed_by_user_name'  => $userName,
                'created_at'            => $now
            ];
            $this->db->table($hseHistTable)->insert($historyData);
            $hseHistId = $this->db->insertID();
        }

        // Mandatory Backup of HSE Audits BEFORE update
        $hseBackupTable = $this->getExistingTableName(['alert_hse_audit_backup', 'HSE_audit_backup']);
        if (!empty($hseAudits) && $hseBackupTable) {
            $hseBackupCols = $this->getTableColumns($hseBackupTable);

            foreach ($hseAudits as $hseRow) {
                $hId = (int)$hseRow['hse_audit_id'];
                $isEligible = in_array($hId, $eligibleHseAuditIds, true);

                $backupRow = $hseRow;
                unset($backupRow['backup_id']);
                $backupRow['history_id'] = $hseHistId;
                $backupRow['backed_up_at'] = $now;

                if (in_array('old_cluster', $hseBackupCols, true)) {
                    $backupRow['old_cluster'] = $hseRow['cluster_name'] ?: $oldCluster;
                }
                if (in_array('new_cluster', $hseBackupCols, true)) {
                    $backupRow['new_cluster'] = $isCmChanged ? $newCluster : $hseRow['cluster_name'];
                }
                if (in_array('old_account_manager', $hseBackupCols, true)) {
                    $backupRow['old_account_manager'] = $hseRow['account_manager'] ?: $oldAccountManager;
                }
                if (in_array('new_account_manager', $hseBackupCols, true)) {
                    $backupRow['new_account_manager'] = $isAmChanged ? $newAccountManager : $hseRow['account_manager'];
                }
                if (in_array('reassignment_status', $hseBackupCols, true)) {
                    $backupRow['reassignment_status'] = $isEligible ? 'REASSIGNED_ACTIVE' : 'PRESERVED_CLOSED';
                }

                $filteredRow = array_intersect_key($backupRow, array_flip($hseBackupCols));
                $this->db->table($hseBackupTable)->insert($filteredRow);
            }
        }

        // Update ONLY eligible HSE audits; PRESERVE closed HSE audits
        if (!empty($eligibleHseAuditIds)) {
            $hseUpdateData = [];
            if ($isCmChanged) {
                $hseUpdateData['cluster_name'] = $newCluster;
            }
            if ($isAmChanged) {
                $hseUpdateData['account_manager'] = $newAccountManager;
            }

            if (!empty($hseUpdateData)) {
                $this->db->table('alert_hse_audit_master')
                    ->whereIn('hse_audit_id', $eligibleHseAuditIds)
                    ->update($hseUpdateData);
            }
        }

        // =================================================================
        // B. GEMBA AUDITS (alert_gemba_audits)
        // =================================================================
        $gembaWhereClauses = ["TRIM(site_name) = {$escapedName}"];
        if ($location !== '') {
            $escapedLoc = $this->db->escape($location);
            $gembaWhereClauses[] = "TRIM(site_name) = {$escapedLoc}";
        }
        $gembaMatchWhere = '(' . implode(' OR ', array_unique($gembaWhereClauses)) . ')';

        $gembaRows = $this->db->query("
            SELECT * FROM alert_gemba_audits 
            WHERE {$gembaMatchWhere}
        ")->getResultArray();

        $eligibleGembaSrNos = [];
        $closedGembaSrNos = [];

        foreach ($gembaRows as $gRow) {
            $srNo = (int)$gRow['gemba_sr_no'];
            $ncStatus = (int)($gRow['nc_status'] ?? 0);
            $pointStatus = strtolower(trim($gRow['point_status'] ?? ''));
            $closureStatus = strtolower(trim($gRow['closure_status'] ?? ''));

            // Gemba Closed criteria:
            // nc_status = 3 OR point_status in ('closed', 'excluded') OR closure_status in ('closed', 'excluded')
            $isClosed = ($ncStatus === 3) 
                || in_array($pointStatus, ['closed', 'excluded'], true)
                || in_array($closureStatus, ['closed', 'excluded'], true);

            if ($isClosed) {
                $closedGembaSrNos[] = $srNo;
            } else {
                $eligibleGembaSrNos[] = $srNo;
            }
        }

        $gembaHistId = null;
        $gembaSite = null;
        if ($this->db->tableExists('alert_gemba_sites')) {
            $gembaSite = $this->db->table('alert_gemba_sites')
                ->where('TRIM(site_name)', $clientName)
                ->get()
                ->getRowArray();
        }

        $gembaHistTable = $this->getExistingTableName(['alert_gemba_history', 'Gemba_history']);
        if ($gembaHistTable) {
            $gembaHistoryData = [
                'client_id'            => $clientId,
                'site_id'              => $gembaSite['id'] ?? 0,
                'change_type'          => 'ALLOCATION',
                'remark'               => $gembaRemark,
                'reason'               => $reason,
                'prev_site_name'       => $clientName,
                'prev_site_category'   => $gembaSite['site_category'] ?? null,
                'prev_site_type_1'     => $gembaSite['site_type_1'] ?? null,
                'prev_site_type_2'     => $gembaSite['site_type_2'] ?? null,
                'prev_region'          => $gembaSite['region'] ?? ($extraOldData['region'] ?? null),
                'prev_cluster'         => $oldCluster ?: ($extraOldData['cluster'] ?? null),
                'prev_account_manager' => $oldAccountManager ?: ($extraOldData['account_manager'] ?? null),
                'prev_status'          => isset($gembaSite['status']) ? (int)$gembaSite['status'] : null,
                'new_site_name'        => $clientName,
                'new_site_category'    => $gembaSite['site_category'] ?? null,
                'new_site_type_1'      => $gembaSite['site_type_1'] ?? null,
                'new_site_type_2'      => $gembaSite['site_type_2'] ?? null,
                'new_region'           => $gembaSite['region'] ?? ($extraOldData['region'] ?? null),
                'new_cluster'          => $isCmChanged ? $newCluster : ($oldCluster ?: ($extraOldData['cluster'] ?? null)),
                'new_account_manager'  => $isAmChanged ? $newAccountManager : ($oldAccountManager ?: ($extraOldData['account_manager'] ?? null)),
                'new_status'           => isset($gembaSite['status']) ? (int)$gembaSite['status'] : null,
                'affected_fields'      => $affectedFields,
                'affected_gemba_ids'   => !empty($eligibleGembaSrNos) ? json_encode($eligibleGembaSrNos) : null,
                'changed_by_user_id'   => $userId,
                'changed_by_user_name' => $userName,
                'created_at'           => $now
            ];
            $this->db->table($gembaHistTable)->insert($gembaHistoryData);
            $gembaHistId = $this->db->insertID();
        }

        // Mandatory Backup of Gemba Audits BEFORE update
        $gembaBackupTable = $this->getExistingTableName(['alert_gemba_audit_backup', 'Gemba_audit_backup']);
        if (!empty($gembaRows) && $gembaBackupTable) {
            $gembaBackupCols = $this->getTableColumns($gembaBackupTable);

            foreach ($gembaRows as $gRow) {
                $srNo = (int)$gRow['gemba_sr_no'];
                $isEligible = in_array($srNo, $eligibleGembaSrNos, true);

                $backupRow = $gRow;
                unset($backupRow['backup_id']);
                $backupRow['history_id'] = $gembaHistId;
                $backupRow['backed_up_at'] = $now;

                if (in_array('old_cluster', $gembaBackupCols, true)) {
                    $backupRow['old_cluster'] = $gRow['cluster_manager_spoc'] ?: $oldCluster;
                }
                if (in_array('new_cluster', $gembaBackupCols, true)) {
                    $backupRow['new_cluster'] = $isCmChanged ? $newCluster : $gRow['cluster_manager_spoc'];
                }
                if (in_array('old_account_manager', $gembaBackupCols, true)) {
                    $backupRow['old_account_manager'] = $gRow['account_manager'] ?: $oldAccountManager;
                }
                if (in_array('new_account_manager', $gembaBackupCols, true)) {
                    $backupRow['new_account_manager'] = $isAmChanged ? $newAccountManager : $gRow['account_manager'];
                }
                if (in_array('reassignment_status', $gembaBackupCols, true)) {
                    $backupRow['reassignment_status'] = $isEligible ? 'REASSIGNED_ACTIVE' : 'PRESERVED_CLOSED';
                }

                $filteredRow = array_intersect_key($backupRow, array_flip($gembaBackupCols));
                $this->db->table($gembaBackupTable)->insert($filteredRow);
            }
        }

        // Update ONLY eligible active Gemba records; PRESERVE closed & excluded Gemba records
        if (!empty($eligibleGembaSrNos)) {
            $gembaUpdateData = [];
            if ($isCmChanged) {
                $gembaUpdateData['cluster_manager_spoc'] = $newCluster;
            }
            if ($isAmChanged) {
                $gembaUpdateData['account_manager'] = $newAccountManager;
            }

            if (!empty($gembaUpdateData)) {
                $this->db->table('alert_gemba_audits')
                    ->whereIn('gemba_sr_no', $eligibleGembaSrNos)
                    ->update($gembaUpdateData);
            }
        }

        return [
            'status'               => 1,
            'message'              => 'HSE and Gemba manager reassigned successfully with status filtering.',
            'hse_history_id'       => $hseHistId,
            'gemba_history_id'     => $gembaHistId,
            'hse_total_audits'     => count($hseAudits),
            'hse_eligible_count'   => count($eligibleHseAuditIds),
            'hse_closed_count'     => count($closedHseAuditIds),
            'gemba_total_rows'     => count($gembaRows),
            'gemba_eligible_count' => count($eligibleGembaSrNos),
            'gemba_closed_count'   => count($closedGembaSrNos),
            'eligible_hse_ids'     => $eligibleHseAuditIds,
            'closed_hse_ids'       => $closedHseAuditIds,
            'eligible_gemba_ids'   => $eligibleGembaSrNos,
            'closed_gemba_ids'     => $closedGembaSrNos
        ];
    }

    /**
     * Reassign Cluster Manager and/or Account Manager from Location Master update.
     */
    public function reassignLocationMaster(
        int $locId,
        string $oldAccountManager,
        string $newAccountManager,
        string $locName,
        string $auditType,
        ?int $userId = null,
        ?string $userName = null,
        string $reason = 'Reassigned via Location Master update',
        array $extraOldData = [],
        ?string $oldCluster = null,
        ?string $newCluster = null
    ): array {
        $auditType = trim($auditType);
        $isOe = ($auditType === 'OE' || $auditType === 'OE and HSE' || $auditType === 'Both' || empty($auditType));
        $isHse = ($auditType === 'HSE' || $auditType === 'OE and HSE' || $auditType === 'Both' || empty($auditType));

        $resOe = null;
        $resHse = null;

        if ($isOe) {
            $resOe = $this->reassignOeClient(
                $locId,
                $oldAccountManager,
                $newAccountManager,
                $locName,
                $locName,
                $userId,
                $userName,
                $reason,
                $extraOldData,
                $oldCluster,
                $newCluster
            );
        }

        if ($isHse) {
            $resHse = $this->reassignHseClient(
                $locId,
                $oldAccountManager,
                $newAccountManager,
                $locName,
                $locName,
                $userId,
                $userName,
                $reason,
                $extraOldData,
                $oldCluster,
                $newCluster
            );
        }

        return [
            'status'     => 1,
            'message'    => 'Location Master manager reassignment completed.',
            'oe_result'  => $resOe,
            'hse_result' => $resHse
        ];
    }

    /**
     * Helper to get list of column names for a table.
     */
    protected function getTableColumns(string $table): array
    {
        $fields = $this->db->getFieldData($table);
        $cols = [];
        foreach ($fields as $f) {
            $cols[] = $f->name;
        }
        return $cols;
    }

    /**
     * Helper to get existing table name among candidates.
     */
    protected function getExistingTableName(array $candidates): ?string
    {
        foreach ($candidates as $tbl) {
            if ($this->db->tableExists($tbl)) {
                return $tbl;
            }
        }
        return null;
    }
}
