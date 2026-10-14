<?php

namespace App\Services;

use Config\Database;

class ClientSiteDependencySyncService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Filters input data to ensure only columns existing in target table are updated.
     */
    private function filterExistingFields(string $table, array $data): array
    {
        if (empty($data) || !$this->db->tableExists($table)) {
            return [];
        }
        $fields = $this->db->getFieldNames($table);
        return array_intersect_key($data, array_flip($fields));
    }

    /**
     * Synchronizes OE Client Master changes to all dependent OE tables.
     *
     * @param array $oldData Existing OE Client record
     * @param array $newData Updated OE Client record
     * @param int|null $userId User ID performing update
     * @param string|null $userName User name performing update
     * @return array Status and affected records details
     */
    public function syncOeClientChanges(array $oldData, array $newData, int $userId = null, string $userName = null): array
    {
        $oldClientName = trim($oldData['client_name'] ?? '');
        $newClientName = trim($newData['client_name'] ?? $oldClientName);

        $oldLocation = trim($oldData['location'] ?? '');
        $newLocation = trim($newData['location'] ?? $oldLocation);

        $oldCluster = trim($oldData['cluster'] ?? '');
        $newCluster = trim($newData['cluster'] ?? $oldCluster);

        $oldAccountManager = trim($oldData['account_manager'] ?? '');
        $newAccountManager = trim($newData['account_manager'] ?? $oldAccountManager);

        $oldRegion = trim($oldData['region'] ?? '');
        $newRegion = trim($newData['region'] ?? $oldRegion);

        $oldStatus = $oldData['status'] ?? null;
        $newStatus = $newData['status'] ?? $oldStatus;

        // First handle rename via ClientSiteRenameService if client_name changed
        if (!empty($newClientName) && !empty($oldClientName) && $newClientName !== $oldClientName) {
            $clientId = !empty($oldData['client_id']) ? (int)$oldData['client_id'] : (!empty($oldData['id']) ? (int)$oldData['id'] : (!empty($newData['client_id']) ? (int)$newData['client_id'] : (!empty($newData['id']) ? (int)$newData['id'] : null)));
            $renameRemarks = trim($newData['rename_remarks'] ?? $newData['reassignment_reason'] ?? $newData['remarks'] ?? '');
            $renameService = new ClientSiteRenameService();
            $renameRes = $renameService->renameOeClient($oldClientName, $newClientName, $userId, $userName, $clientId, $renameRemarks);
            if ($renameRes['status'] === 0) {
                return $renameRes;
            }
        }

        $effectiveClientName = !empty($newClientName) ? $newClientName : $oldClientName;

        // Determine metadata changes
        $changes = [];
        if ($oldLocation !== $newLocation && $newLocation !== '') $changes['location'] = ['old' => $oldLocation, 'new' => $newLocation];
        if ($oldCluster !== $newCluster && $newCluster !== '') $changes['cluster'] = ['old' => $oldCluster, 'new' => $newCluster];
        if ($oldAccountManager !== $newAccountManager && $newAccountManager !== '') $changes['account_manager'] = ['old' => $oldAccountManager, 'new' => $newAccountManager];
        if ($oldRegion !== $newRegion && $newRegion !== '') $changes['region'] = ['old' => $oldRegion, 'new' => $newRegion];
        if ($oldStatus != $newStatus && $newStatus !== null) $changes['status'] = ['old' => $oldStatus, 'new' => $newStatus];

        if (empty($changes)) {
            return ['status' => 1, 'message' => 'No OE dependent metadata changes required.', 'affected_records' => 0];
        }

        $this->db->transStart();

        $affectedCounts = [];
        $totalAffected = 0;

        // 1. Sync Location Master (alert_location_master - OE scope)
        if ($this->db->tableExists('alert_location_master')) {
            $locUpdateData = [];
            if (isset($changes['cluster'])) $locUpdateData['cluster_name'] = $newCluster;
            if (isset($changes['account_manager'])) $locUpdateData['account_manager'] = $newAccountManager;
            if (isset($changes['region'])) $locUpdateData['region_name'] = $newRegion;
            if (isset($changes['status'])) $locUpdateData['status'] = $newStatus;

            $locUpdateData = $this->filterExistingFields('alert_location_master', $locUpdateData);

            if (!empty($locUpdateData)) {
                $builder = $this->db->table('alert_location_master')
                    ->groupStart()
                        ->where('location_name', $effectiveClientName)
                        ->orWhere('location_name', $oldLocation)
                    ->groupEnd()
                    ->groupStart()
                        ->where('audit_type', 'OE')
                        ->orWhere('audit_type', 'OE and HSE')
                        ->orWhere('audit_type IS NULL')
                        ->orWhere('audit_type', '')
                    ->groupEnd();
                $builder->update($locUpdateData);
                $cnt = $this->db->affectedRows();
                $affectedCounts['alert_location_master'] = $cnt;
                $totalAffected += $cnt;
            }
        }

        // 2. Sync OE Audit Master (alert_final_structured_audit)
        if ($this->db->tableExists('alert_final_structured_audit')) {
            $oeAuditData = [];
            if (isset($changes['location'])) $oeAuditData['location'] = $newLocation;
            if (isset($changes['region'])) $oeAuditData['region'] = $newRegion;
            if (isset($changes['cluster'])) $oeAuditData['cluster_name'] = $newCluster;
            if (isset($changes['account_manager'])) $oeAuditData['client_manager_name'] = $newAccountManager;

            $oeAuditData = $this->filterExistingFields('alert_final_structured_audit', $oeAuditData);

            if (!empty($oeAuditData)) {
                $matchNames = array_unique(array_filter([
                    trim($effectiveClientName),
                    trim($oldClientName),
                    trim($newLocation),
                    trim($oldLocation)
                ]));
                if (!empty($matchNames)) {
                    $escapedNames = array_map([$this->db, 'escape'], $matchNames);
                    $inClause = implode(',', $escapedNames);
                    $this->db->table('alert_final_structured_audit')
                        ->where("(TRIM(client_name) IN ({$inClause}) OR TRIM(location) IN ({$inClause}))")
                        ->update($oeAuditData);
                    $cnt = $this->db->affectedRows();
                    $affectedCounts['alert_final_structured_audit'] = $cnt;
                    $totalAffected += $cnt;
                }
            }
        }

        // 3. Sync OE Normal Audit Master (alert_normal_audit)
        if ($this->db->tableExists('alert_normal_audit')) {
            $normalAuditData = [];
            if (isset($changes['location'])) $normalAuditData['location'] = $newLocation;
            if (isset($changes['region'])) $normalAuditData['region'] = $newRegion;
            if (isset($changes['cluster'])) $normalAuditData['cluster_name'] = $newCluster;
            if (isset($changes['account_manager'])) $normalAuditData['client_manager_name'] = $newAccountManager;

            $normalAuditData = $this->filterExistingFields('alert_normal_audit', $normalAuditData);

            if (!empty($normalAuditData)) {
                $matchNames = array_unique(array_filter([
                    trim($effectiveClientName),
                    trim($oldClientName),
                    trim($newLocation),
                    trim($oldLocation)
                ]));
                if (!empty($matchNames)) {
                    $escapedNames = array_map([$this->db, 'escape'], $matchNames);
                    $inClause = implode(',', $escapedNames);
                    $this->db->table('alert_normal_audit')
                        ->where("(TRIM(client_name) IN ({$inClause}) OR TRIM(location) IN ({$inClause}))")
                        ->update($normalAuditData);
                    $cnt = $this->db->affectedRows();
                    $affectedCounts['alert_normal_audit'] = $cnt;
                    $totalAffected += $cnt;
                }
            }
        }


        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            log_message('error', "ClientSiteDependencySyncService: OE Sync for '{$effectiveClientName}' failed.");
            return ['status' => 0, 'message' => 'Database transaction failed during OE dependency synchronization. Changes rolled back.'];
        }

        log_message('info', "ClientSiteDependencySyncService: Synced OE Client '{$effectiveClientName}'. Total affected records: {$totalAffected}");

        return [
            'status' => 1,
            'message' => "OE Client dependency synchronized successfully. Updated {$totalAffected} dependent record(s).",
            'affected_records' => $totalAffected,
            'details' => $affectedCounts
        ];
    }

    /**
     * Synchronizes HSE Client Master changes to all dependent HSE & Gemba tables.
     *
     * @param array $oldData Existing HSE Client record
     * @param array $newData Updated HSE Client record
     * @param int|null $userId User ID performing update
     * @param string|null $userName User name performing update
     * @return array Status and affected records details
     */
    public function syncHseClientChanges(array $oldData, array $newData, int $userId = null, string $userName = null): array
    {
        $oldClientName = trim($oldData['client_name'] ?? '');
        $newClientName = trim($newData['client_name'] ?? $oldClientName);

        $oldLocation = trim($oldData['location'] ?? '');
        $newLocation = trim($newData['location'] ?? $oldLocation);

        $oldCluster = trim($oldData['cluster'] ?? '');
        $newCluster = trim($newData['cluster'] ?? $oldCluster);

        $oldAccountManager = trim($oldData['account_manager'] ?? '');
        $newAccountManager = trim($newData['account_manager'] ?? $oldAccountManager);

        $oldRegion = trim($oldData['region'] ?? '');
        $newRegion = trim($newData['region'] ?? $oldRegion);

        $oldStatus = $oldData['status'] ?? null;
        $newStatus = $newData['status'] ?? $oldStatus;

        // First handle rename via ClientSiteRenameService if client_name changed
        if (!empty($newClientName) && !empty($oldClientName) && $newClientName !== $oldClientName) {
            $clientId = !empty($oldData['client_id']) ? (int)$oldData['client_id'] : (!empty($oldData['id']) ? (int)$oldData['id'] : (!empty($newData['client_id']) ? (int)$newData['client_id'] : (!empty($newData['id']) ? (int)$newData['id'] : null)));
            $renameRemarks = trim($newData['rename_remarks'] ?? $newData['reassignment_reason'] ?? $newData['remarks'] ?? '');
            $renameService = new ClientSiteRenameService();
            $renameRes = $renameService->renameHseClient($oldClientName, $newClientName, $userId, $userName, $clientId, $renameRemarks);
            if ($renameRes['status'] === 0) {
                return $renameRes;
            }
        }

        $effectiveClientName = !empty($newClientName) ? $newClientName : $oldClientName;

        // Determine metadata changes
        $changes = [];
        if ($oldLocation !== $newLocation && $newLocation !== '') $changes['location'] = ['old' => $oldLocation, 'new' => $newLocation];
        if ($oldCluster !== $newCluster && $newCluster !== '') $changes['cluster'] = ['old' => $oldCluster, 'new' => $newCluster];
        if ($oldAccountManager !== $newAccountManager && $newAccountManager !== '') $changes['account_manager'] = ['old' => $oldAccountManager, 'new' => $newAccountManager];
        if ($oldRegion !== $newRegion && $newRegion !== '') $changes['region'] = ['old' => $oldRegion, 'new' => $newRegion];
        if ($oldStatus != $newStatus && $newStatus !== null) $changes['status'] = ['old' => $oldStatus, 'new' => $newStatus];

        if (empty($changes)) {
            return ['status' => 1, 'message' => 'No HSE dependent metadata changes required.', 'affected_records' => 0];
        }

        $this->db->transStart();

        $affectedCounts = [];
        $totalAffected = 0;

        // 1. Sync Location Master (alert_location_master - HSE scope)
        if ($this->db->tableExists('alert_location_master')) {
            $locUpdateData = [];
            if (isset($changes['cluster'])) $locUpdateData['cluster_name'] = $newCluster;
            if (isset($changes['account_manager'])) $locUpdateData['account_manager'] = $newAccountManager;
            if (isset($changes['region'])) $locUpdateData['region_name'] = $newRegion;
            if (isset($changes['status'])) $locUpdateData['status'] = $newStatus;

            $locUpdateData = $this->filterExistingFields('alert_location_master', $locUpdateData);

            if (!empty($locUpdateData)) {
                $builder = $this->db->table('alert_location_master')
                    ->groupStart()
                        ->where('location_name', $effectiveClientName)
                        ->orWhere('location_name', $oldLocation)
                    ->groupEnd()
                    ->groupStart()
                        ->where('audit_type', 'HSE')
                        ->orWhere('audit_type', 'OE and HSE')
                        ->orWhere('audit_type IS NULL')
                        ->orWhere('audit_type', '')
                    ->groupEnd();
                $builder->update($locUpdateData);
                $cnt = $this->db->affectedRows();
                $affectedCounts['alert_location_master'] = $cnt;
                $totalAffected += $cnt;
            }
        }

        // 2. Sync Gemba Sites (alert_gemba_sites)
        if ($this->db->tableExists('alert_gemba_sites')) {
            $gembaSiteData = [];
            if (isset($changes['region'])) $gembaSiteData['region'] = $newRegion;
            if (isset($changes['status'])) $gembaSiteData['status'] = $newStatus;

            $gembaSiteData = $this->filterExistingFields('alert_gemba_sites', $gembaSiteData);

            if (!empty($gembaSiteData)) {
                $builder = $this->db->table('alert_gemba_sites')
                    ->groupStart()
                        ->where('site_name', $effectiveClientName)
                        ->orWhere('site_name', $oldLocation)
                    ->groupEnd();
                $builder->update($gembaSiteData);
                $cnt = $this->db->affectedRows();
                $affectedCounts['alert_gemba_sites'] = $cnt;
                $totalAffected += $cnt;
            }
        }

        // 3. Sync HSE Audit Master (alert_hse_audit_master)
        if ($this->db->tableExists('alert_hse_audit_master')) {
            $hseAuditData = [];
            if (isset($changes['location'])) $hseAuditData['location'] = $newLocation;
            if (isset($changes['region'])) $hseAuditData['region'] = $newRegion;
            if (isset($changes['cluster'])) $hseAuditData['cluster_name'] = $newCluster;
            if (isset($changes['account_manager'])) $hseAuditData['account_manager'] = $newAccountManager;

            $hseAuditData = $this->filterExistingFields('alert_hse_audit_master', $hseAuditData);

            if (!empty($hseAuditData)) {
                $matchHseNames = array_unique(array_filter([
                    trim($effectiveClientName),
                    trim($oldClientName),
                    trim($newLocation),
                    trim($oldLocation)
                ]));
                if (!empty($matchHseNames)) {
                    $escapedNames = array_map([$this->db, 'escape'], $matchHseNames);
                    $inClause = implode(',', $escapedNames);
                    $this->db->table('alert_hse_audit_master')
                        ->where("(TRIM(client_name) IN ({$inClause}) OR TRIM(location) IN ({$inClause}))")
                        ->update($hseAuditData);
                    $cnt = $this->db->affectedRows();
                    $affectedCounts['alert_hse_audit_master'] = $cnt;
                    $totalAffected += $cnt;
                }
            }
        }

        // 4. Sync Gemba Audits (alert_gemba_audits)
        if ($this->db->tableExists('alert_gemba_audits')) {
            $gembaAuditData = [];
            if (isset($changes['location'])) $gembaAuditData['site_name'] = $newLocation;
            if (isset($changes['region'])) $gembaAuditData['region'] = $newRegion;
            if (isset($changes['cluster'])) $gembaAuditData['cluster_manager_spoc'] = $newCluster;
            if (isset($changes['account_manager'])) $gembaAuditData['account_manager'] = $newAccountManager;

            $gembaAuditData = $this->filterExistingFields('alert_gemba_audits', $gembaAuditData);

            if (!empty($gembaAuditData)) {
                $matchGembaNames = array_unique(array_filter([
                    trim($effectiveClientName),
                    trim($oldClientName),
                    trim($newLocation),
                    trim($oldLocation)
                ]));
                if (!empty($matchGembaNames)) {
                    $escapedNames = array_map([$this->db, 'escape'], $matchGembaNames);
                    $inClause = implode(',', $escapedNames);
                    $this->db->table('alert_gemba_audits')
                        ->where("TRIM(site_name) IN ({$inClause})")
                        ->update($gembaAuditData);
                    $cnt = $this->db->affectedRows();
                    $affectedCounts['alert_gemba_audits'] = $cnt;
                    $totalAffected += $cnt;
                }
            }
        }


        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            log_message('error', "ClientSiteDependencySyncService: HSE Sync for '{$effectiveClientName}' failed.");
            return ['status' => 0, 'message' => 'Database transaction failed during HSE dependency synchronization. Changes rolled back.'];
        }

        log_message('info', "ClientSiteDependencySyncService: Synced HSE Client '{$effectiveClientName}'. Total affected records: {$totalAffected}");

        return [
            'status' => 1,
            'message' => "HSE Client dependency synchronized successfully. Updated {$totalAffected} dependent record(s).",
            'affected_records' => $totalAffected,
            'details' => $affectedCounts
        ];
    }

    /**
     * Synchronizes Location Master changes to OE and/or HSE/Gemba dependent tables based on audit_type.
     *
     * @param array $oldData Existing Location Master record
     * @param array $newData Updated Location Master record
     * @param int|null $userId User ID performing update
     * @param string|null $userName User name performing update
     * @return array Status and affected records details
     */
    public function syncLocationMasterChanges(array $oldData, array $newData, int $userId = null, string $userName = null): array
    {
        $oldLocName = trim($oldData['location_name'] ?? '');
        $newLocName = trim($newData['location_name'] ?? $oldLocName);

        $oldCluster = trim($oldData['cluster_name'] ?? '');
        $newCluster = trim($newData['cluster_name'] ?? $oldCluster);

        $oldAccountManager = trim($oldData['account_manager'] ?? '');
        $newAccountManager = trim($newData['account_manager'] ?? $oldAccountManager);

        $oldRegion = trim($oldData['region_name'] ?? '');
        $newRegion = trim($newData['region_name'] ?? $oldRegion);

        $oldAuditType = trim($oldData['audit_type'] ?? '');
        $newAuditType = trim($newData['audit_type'] ?? $oldAuditType);

        $oldStatus = $oldData['status'] ?? null;
        $newStatus = $newData['status'] ?? $oldStatus;

        $effectiveLocName = !empty($newLocName) ? $newLocName : $oldLocName;
        $effectiveAuditType = !empty($newAuditType) ? $newAuditType : $oldAuditType;

        $changes = [];
        if ($oldLocName !== $newLocName && $newLocName !== '') $changes['location_name'] = ['old' => $oldLocName, 'new' => $newLocName];
        if ($oldCluster !== $newCluster && $newCluster !== '') $changes['cluster'] = ['old' => $oldCluster, 'new' => $newCluster];
        if ($oldAccountManager !== $newAccountManager && $newAccountManager !== '') $changes['account_manager'] = ['old' => $oldAccountManager, 'new' => $newAccountManager];
        if ($oldRegion !== $newRegion && $newRegion !== '') $changes['region'] = ['old' => $oldRegion, 'new' => $newRegion];
        if ($oldStatus != $newStatus && $newStatus !== null) $changes['status'] = ['old' => $oldStatus, 'new' => $newStatus];

        if (empty($changes)) {
            return ['status' => 1, 'message' => 'No Location Master dependent changes required.', 'affected_records' => 0];
        }

        $this->db->transStart();

        $affectedCounts = [];
        $totalAffected = 0;

        $isOe = ($effectiveAuditType === 'OE' || $effectiveAuditType === 'OE and HSE' || empty($effectiveAuditType));
        $isHse = ($effectiveAuditType === 'HSE' || $effectiveAuditType === 'OE and HSE' || empty($effectiveAuditType));

        // 1. Sync OE Master (`alert_client`)
        if ($isOe && $this->db->tableExists('alert_client')) {
            $oeClientData = [];
            if (isset($changes['location_name'])) $oeClientData['client_name'] = $newLocName;
            if (isset($changes['cluster'])) $oeClientData['cluster'] = $newCluster;
            if (isset($changes['account_manager'])) $oeClientData['account_manager'] = $newAccountManager;
            if (isset($changes['region'])) $oeClientData['region'] = $newRegion;
            if (isset($changes['status'])) $oeClientData['status'] = $newStatus;

            $oeClientData = $this->filterExistingFields('alert_client', $oeClientData);

            if (!empty($oeClientData)) {
                $this->db->table('alert_client')
                    ->groupStart()
                        ->where('client_name', $oldLocName)
                        ->orWhere('location', $oldLocName)
                    ->groupEnd()
                    ->update($oeClientData);
                $cnt = $this->db->affectedRows();
                $affectedCounts['alert_client'] = $cnt;
                $totalAffected += $cnt;
            }
        }

        // 2. Sync OE Final Structured Audit (`alert_final_structured_audit`)
        if ($isOe && $this->db->tableExists('alert_final_structured_audit')) {
            $oeAuditData = [];
            if (isset($changes['location_name'])) {
                $oeAuditData['client_name'] = $newLocName;
                $oeAuditData['location'] = $newLocName;
            }
            if (isset($changes['region'])) $oeAuditData['region'] = $newRegion;
            if (isset($changes['cluster'])) $oeAuditData['cluster_name'] = $newCluster;
            if (isset($changes['account_manager'])) $oeAuditData['client_manager_name'] = $newAccountManager;

            $oeAuditData = $this->filterExistingFields('alert_final_structured_audit', $oeAuditData);

            if (!empty($oeAuditData)) {
                $matchNames = array_unique(array_filter([
                    trim($oldLocName),
                    trim($newLocName)
                ]));
                if (!empty($matchNames)) {
                    $escapedNames = array_map([$this->db, 'escape'], $matchNames);
                    $inClause = implode(',', $escapedNames);
                    $this->db->table('alert_final_structured_audit')
                        ->where("(TRIM(client_name) IN ({$inClause}) OR TRIM(location) IN ({$inClause}))")
                        ->update($oeAuditData);
                    $cnt = $this->db->affectedRows();
                    $affectedCounts['alert_final_structured_audit'] = $cnt;
                    $totalAffected += $cnt;
                }
            }
        }

        // 3. Sync OE Normal Audit (`alert_normal_audit`)
        if ($isOe && $this->db->tableExists('alert_normal_audit')) {
            $normalAuditData = [];
            if (isset($changes['location_name'])) {
                $normalAuditData['client_name'] = $newLocName;
                $normalAuditData['location'] = $newLocName;
            }
            if (isset($changes['region'])) $normalAuditData['region'] = $newRegion;
            if (isset($changes['cluster'])) $normalAuditData['cluster_name'] = $newCluster;
            if (isset($changes['account_manager'])) $normalAuditData['client_manager_name'] = $newAccountManager;

            $normalAuditData = $this->filterExistingFields('alert_normal_audit', $normalAuditData);

            if (!empty($normalAuditData)) {
                $matchNames = array_unique(array_filter([
                    trim($oldLocName),
                    trim($newLocName)
                ]));
                if (!empty($matchNames)) {
                    $escapedNames = array_map([$this->db, 'escape'], $matchNames);
                    $inClause = implode(',', $escapedNames);
                    $this->db->table('alert_normal_audit')
                        ->where("(TRIM(client_name) IN ({$inClause}) OR TRIM(location) IN ({$inClause}))")
                        ->update($normalAuditData);
                    $cnt = $this->db->affectedRows();
                    $affectedCounts['alert_normal_audit'] = $cnt;
                    $totalAffected += $cnt;
                }
            }
        }

        // 4. Sync HSE Master (`alert_hse_client_master`)
        if ($isHse && $this->db->tableExists('alert_hse_client_master')) {
            $hseClientData = [];
            if (isset($changes['location_name'])) $hseClientData['client_name'] = $newLocName;
            if (isset($changes['cluster'])) $hseClientData['cluster'] = $newCluster;
            if (isset($changes['account_manager'])) $hseClientData['account_manager'] = $newAccountManager;
            if (isset($changes['region'])) $hseClientData['region'] = $newRegion;
            if (isset($changes['status'])) $hseClientData['status'] = $newStatus;

            $hseClientData = $this->filterExistingFields('alert_hse_client_master', $hseClientData);

            if (!empty($hseClientData)) {
                $matchNames = array_unique(array_filter([
                    trim($oldLocName),
                    trim($newLocName)
                ]));
                if (!empty($matchNames)) {
                    $escapedNames = array_map([$this->db, 'escape'], $matchNames);
                    $inClause = implode(',', $escapedNames);
                    $this->db->table('alert_hse_client_master')
                        ->where("(TRIM(client_name) IN ({$inClause}) OR TRIM(location) IN ({$inClause}))")
                        ->update($hseClientData);
                    $cnt = $this->db->affectedRows();
                    $affectedCounts['alert_hse_client_master'] = $cnt;
                    $totalAffected += $cnt;
                }
            }
        }

        // 5. Sync Gemba Sites (`alert_gemba_sites`)
        if ($isHse && $this->db->tableExists('alert_gemba_sites')) {
            $gembaSiteData = [];
            if (isset($changes['location_name'])) $gembaSiteData['site_name'] = $newLocName;
            if (isset($changes['region'])) $gembaSiteData['region'] = $newRegion;
            if (isset($changes['status'])) $gembaSiteData['status'] = $newStatus;

            $gembaSiteData = $this->filterExistingFields('alert_gemba_sites', $gembaSiteData);

            if (!empty($gembaSiteData)) {
                $matchNames = array_unique(array_filter([
                    trim($oldLocName),
                    trim($newLocName)
                ]));
                if (!empty($matchNames)) {
                    $escapedNames = array_map([$this->db, 'escape'], $matchNames);
                    $inClause = implode(',', $escapedNames);
                    $this->db->table('alert_gemba_sites')
                        ->where("TRIM(site_name) IN ({$inClause})")
                        ->update($gembaSiteData);
                    $cnt = $this->db->affectedRows();
                    $affectedCounts['alert_gemba_sites'] = $cnt;
                    $totalAffected += $cnt;
                }
            }
        }

        // 6. Sync HSE Audit Master (`alert_hse_audit_master`)
        if ($isHse && $this->db->tableExists('alert_hse_audit_master')) {
            $hseAuditData = [];
            if (isset($changes['location_name'])) {
                $hseAuditData['client_name'] = $newLocName;
                $hseAuditData['location'] = $newLocName;
            }
            if (isset($changes['region'])) $hseAuditData['region'] = $newRegion;
            if (isset($changes['cluster'])) $hseAuditData['cluster_name'] = $newCluster;
            if (isset($changes['account_manager'])) $hseAuditData['account_manager'] = $newAccountManager;

            $hseAuditData = $this->filterExistingFields('alert_hse_audit_master', $hseAuditData);

            if (!empty($hseAuditData)) {
                $matchNames = array_unique(array_filter([
                    trim($oldLocName),
                    trim($newLocName)
                ]));
                if (!empty($matchNames)) {
                    $escapedNames = array_map([$this->db, 'escape'], $matchNames);
                    $inClause = implode(',', $escapedNames);
                    $this->db->table('alert_hse_audit_master')
                        ->where("(TRIM(client_name) IN ({$inClause}) OR TRIM(location) IN ({$inClause}))")
                        ->update($hseAuditData);
                    $cnt = $this->db->affectedRows();
                    $affectedCounts['alert_hse_audit_master'] = $cnt;
                    $totalAffected += $cnt;
                }
            }
        }

        // 7. Sync Gemba Audits (`alert_gemba_audits`)
        if ($isHse && $this->db->tableExists('alert_gemba_audits')) {
            $gembaAuditData = [];
            if (isset($changes['location_name'])) $gembaAuditData['site_name'] = $newLocName;
            if (isset($changes['region'])) $gembaAuditData['region'] = $newRegion;
            if (isset($changes['cluster'])) $gembaAuditData['cluster_manager_spoc'] = $newCluster;
            if (isset($changes['account_manager'])) $gembaAuditData['account_manager'] = $newAccountManager;

            $gembaAuditData = $this->filterExistingFields('alert_gemba_audits', $gembaAuditData);

            if (!empty($gembaAuditData)) {
                $matchNames = array_unique(array_filter([
                    trim($oldLocName),
                    trim($newLocName)
                ]));
                if (!empty($matchNames)) {
                    $escapedNames = array_map([$this->db, 'escape'], $matchNames);
                    $inClause = implode(',', $escapedNames);
                    $this->db->table('alert_gemba_audits')
                        ->where("TRIM(site_name) IN ({$inClause})")
                        ->update($gembaAuditData);
                    $cnt = $this->db->affectedRows();
                    $affectedCounts['alert_gemba_audits'] = $cnt;
                    $totalAffected += $cnt;
                }
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            log_message('error', "ClientSiteDependencySyncService: Location Master Sync for '{$effectiveLocName}' failed.");
            return ['status' => 0, 'message' => 'Database transaction failed during Location Master dependency synchronization. Changes rolled back.'];
        }

        log_message('info', "ClientSiteDependencySyncService: Synced Location Master '{$effectiveLocName}'. Total affected records: {$totalAffected}");

        return [
            'status' => 1,
            'message' => "Location Master dependency synchronized successfully. Updated {$totalAffected} dependent record(s).",
            'affected_records' => $totalAffected,
            'details' => $affectedCounts
        ];
    }
}
