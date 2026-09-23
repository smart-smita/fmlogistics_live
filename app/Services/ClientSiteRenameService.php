<?php

namespace App\Services;

use Config\Database;

class ClientSiteRenameService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->ensureSchema();
    }

    /**
     * Ensure the audit trail table exists for logging client & site renames.
     */
    private function ensureSchema()
    {
        if (!$this->db->tableExists('alert_client_rename_logs')) {
            $sql = "CREATE TABLE IF NOT EXISTS alert_client_rename_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                entity_type VARCHAR(50) NOT NULL,
                old_name VARCHAR(255) NOT NULL,
                new_name VARCHAR(255) NOT NULL,
                renamed_by_user_id INT NULL,
                renamed_by_user_name VARCHAR(255) NULL,
                affected_records INT DEFAULT 0,
                details_json TEXT NULL,
                created_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->db->query($sql);
        }
    }

    /**
     * 1. Rename an OE Client (alert_client) and update ONLY OE-dependent tables.
     *
     * Affected tables:
     * - alert_client
     * - alert_final_structured_audit (OE Audit Master)
     * - alert_normal_audit (Normal Audit Master)
     * - alert_location_master (where audit_type = 'OE' or matching location_name)
     *
     * EXCLUDED: HSE Client, HSE Audits, Gemba Sites, Gemba Audits.
     *
     * @param string $oldName
     * @param string $newName
     * @param int|null $userId
     * @param string|null $userName
     * @return array ['status' => int, 'message' => string, 'affected_records' => int]
     */
    public function renameOeClient(string $oldName, string $newName, int $userId = null, string $userName = null): array
    {
        $oldName = trim($oldName);
        $newName = trim($newName);

        if (empty($oldName) || empty($newName)) {
            return ['status' => 0, 'message' => 'Old OE client name and new name cannot be empty.'];
        }

        if ($oldName === $newName) {
            return ['status' => 1, 'message' => 'No name change requested.', 'affected_records' => 0];
        }

        if (strlen($newName) < 3) {
            return ['status' => 0, 'message' => 'New OE client name must be at least 3 characters long.'];
        }

        // Check if new name already exists in active OE Client Master under a different record
        $existing = $this->db->table('alert_client')
            ->where('client_name', $newName)
            ->where('status !=', 2)
            ->where('client_name !=', $oldName)
            ->countAllResults();

        if ($existing > 0) {
            return ['status' => 0, 'message' => "An active OE Client with the name '{$newName}' already exists."];
        }

        $this->db->transStart();

        $affectedCounts = [];
        $totalAffected = 0;

        // 1. Update OE Client Master (alert_client)
        if ($this->db->tableExists('alert_client')) {
            $this->db->table('alert_client')
                ->where('client_name', $oldName)
                ->update(['client_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_client'] = $cnt;
            $totalAffected += $cnt;
        }

        // 2. Update OE Audit Master (alert_final_structured_audit)
        if ($this->db->tableExists('alert_final_structured_audit')) {
            $this->db->table('alert_final_structured_audit')
                ->where('client_name', $oldName)
                ->update(['client_name' => $newName]);
            $cnt = $this->db->affectedRows();

            $this->db->table('alert_final_structured_audit')
                ->where('location', $oldName)
                ->update(['location' => $newName]);
            $cnt += $this->db->affectedRows();

            $affectedCounts['alert_final_structured_audit'] = $cnt;
            $totalAffected += $cnt;
        }

        // 3. Update Normal Audit Master (alert_normal_audit)
        if ($this->db->tableExists('alert_normal_audit')) {
            $this->db->table('alert_normal_audit')
                ->where('client_name', $oldName)
                ->update(['client_name' => $newName]);
            $cnt = $this->db->affectedRows();

            $this->db->table('alert_normal_audit')
                ->where('location', $oldName)
                ->update(['location' => $newName]);
            $cnt += $this->db->affectedRows();

            $affectedCounts['alert_normal_audit'] = $cnt;
            $totalAffected += $cnt;
        }

        // 4. Update Location Master (alert_location_master - OE scope)
        if ($this->db->tableExists('alert_location_master')) {
            $this->db->table('alert_location_master')
                ->where('location_name', $oldName)
                ->groupStart()
                    ->where('audit_type', 'OE')
                    ->orWhere('audit_type IS NULL')
                    ->orWhere('audit_type', '')
                ->groupEnd()
                ->update(['location_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_location_master'] = $cnt;
            $totalAffected += $cnt;
        }

        // Log operation
        $this->db->table('alert_client_rename_logs')->insert([
            'entity_type' => 'oe_client',
            'old_name' => $oldName,
            'new_name' => $newName,
            'renamed_by_user_id' => $userId,
            'renamed_by_user_name' => $userName,
            'affected_records' => $totalAffected,
            'details_json' => json_encode($affectedCounts),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            log_message('error', "ClientSiteRenameService: Rename OE Client from '{$oldName}' to '{$newName}' failed.");
            return ['status' => 0, 'message' => 'Database transaction failed while renaming OE client. Changes rolled back.'];
        }

        log_message('info', "ClientSiteRenameService: Renamed OE Client '{$oldName}' to '{$newName}'. Affected records: {$totalAffected}");

        return [
            'status' => 1,
            'message' => "OE Client renamed successfully from '{$oldName}' to '{$newName}'. Updated {$totalAffected} dependent record(s).",
            'affected_records' => $totalAffected,
            'details' => $affectedCounts
        ];
    }

    /**
     * 2. Rename an HSE Client (alert_hse_client_master) and update ONLY HSE-dependent tables.
     *
     * Affected tables:
     * - alert_hse_client_master
     * - alert_hse_audit_master (HSE Audit Master)
     * - alert_gemba_audits (where source_module = 'hse_audit')
     * - alert_gemba_sites
     * - alert_client_deactivation_logs
     * - alert_location_master (where audit_type = 'HSE' or matching location_name)
     *
     * EXCLUDED: OE Client (alert_client), OE Audits (alert_final_structured_audit, alert_normal_audit).
     *
     * @param string $oldName
     * @param string $newName
     * @param int|null $userId
     * @param string|null $userName
     * @return array ['status' => int, 'message' => string, 'affected_records' => int]
     */
    public function renameHseClient(string $oldName, string $newName, int $userId = null, string $userName = null): array
    {
        $oldName = trim($oldName);
        $newName = trim($newName);

        if (empty($oldName) || empty($newName)) {
            return ['status' => 0, 'message' => 'Old HSE client name and new name cannot be empty.'];
        }

        if ($oldName === $newName) {
            return ['status' => 1, 'message' => 'No name change requested.', 'affected_records' => 0];
        }

        if (strlen($newName) < 3) {
            return ['status' => 0, 'message' => 'New HSE client name must be at least 3 characters long.'];
        }

        // Check if new name already exists in active HSE Client Master under a different record
        $existing = $this->db->table('alert_hse_client_master')
            ->where('client_name', $newName)
            ->where('status !=', 2)
            ->where('client_name !=', $oldName)
            ->countAllResults();

        if ($existing > 0) {
            return ['status' => 0, 'message' => "An active HSE Client with the name '{$newName}' already exists."];
        }

        $this->db->transStart();

        $affectedCounts = [];
        $totalAffected = 0;

        // 1. Update HSE Client Master (alert_hse_client_master)
        $this->db->table('alert_hse_client_master')
            ->where('client_name', $oldName)
            ->update(['client_name' => $newName]);
        $cnt = $this->db->affectedRows();
        $affectedCounts['alert_hse_client_master'] = $cnt;
        $totalAffected += $cnt;

        // 2. Update HSE Audit Master (alert_hse_audit_master)
        if ($this->db->tableExists('alert_hse_audit_master')) {
            $this->db->table('alert_hse_audit_master')
                ->where('client_name', $oldName)
                ->update(['client_name' => $newName]);
            $cnt = $this->db->affectedRows();

            $this->db->table('alert_hse_audit_master')
                ->where('location', $oldName)
                ->update(['location' => $newName]);
            $cnt += $this->db->affectedRows();

            $affectedCounts['alert_hse_audit_master'] = $cnt;
            $totalAffected += $cnt;
        }

        // 3. Update Gemba Sites Master (alert_gemba_sites)
        if ($this->db->tableExists('alert_gemba_sites')) {
            $this->db->table('alert_gemba_sites')
                ->where('site_name', $oldName)
                ->update(['site_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_gemba_sites'] = $cnt;
            $totalAffected += $cnt;
        }

        // 4. Update Gemba Audits (synced HSE audit NCs)
        if ($this->db->tableExists('alert_gemba_audits')) {
            $this->db->table('alert_gemba_audits')
                ->where('site_name', $oldName)
                ->update(['site_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_gemba_audits'] = $cnt;
            $totalAffected += $cnt;
        }

        // 5. Update Client Deactivation Logs (alert_client_deactivation_logs)
        if ($this->db->tableExists('alert_client_deactivation_logs')) {
            $this->db->table('alert_client_deactivation_logs')
                ->where('client_name', $oldName)
                ->update(['client_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_client_deactivation_logs'] = $cnt;
            $totalAffected += $cnt;
        }

        // 6. Update Location Master (alert_location_master - HSE scope)
        if ($this->db->tableExists('alert_location_master')) {
            $this->db->table('alert_location_master')
                ->where('location_name', $oldName)
                ->groupStart()
                    ->where('audit_type', 'HSE')
                    ->orWhere('audit_type IS NULL')
                    ->orWhere('audit_type', '')
                ->groupEnd()
                ->update(['location_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_location_master'] = $cnt;
            $totalAffected += $cnt;
        }

        // Log operation
        $this->db->table('alert_client_rename_logs')->insert([
            'entity_type' => 'hse_client',
            'old_name' => $oldName,
            'new_name' => $newName,
            'renamed_by_user_id' => $userId,
            'renamed_by_user_name' => $userName,
            'affected_records' => $totalAffected,
            'details_json' => json_encode($affectedCounts),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            log_message('error', "ClientSiteRenameService: Rename HSE Client from '{$oldName}' to '{$newName}' failed.");
            return ['status' => 0, 'message' => 'Database transaction failed while renaming HSE client. Changes rolled back.'];
        }

        log_message('info', "ClientSiteRenameService: Renamed HSE Client '{$oldName}' to '{$newName}'. Affected records: {$totalAffected}");

        return [
            'status' => 1,
            'message' => "HSE Client renamed successfully from '{$oldName}' to '{$newName}'. Updated {$totalAffected} dependent record(s).",
            'affected_records' => $totalAffected,
            'details' => $affectedCounts
        ];
    }

    /**
     * 3. Rename a Gemba Site (alert_gemba_sites) and update ONLY Gemba-dependent tables.
     *
     * Affected tables:
     * - alert_gemba_sites
     * - alert_gemba_audits
     *
     * EXCLUDED: OE Client (alert_client), OE Audits (alert_final_structured_audit, alert_normal_audit).
     *
     * @param string $oldName
     * @param string $newName
     * @param int|null $userId
     * @param string|null $userName
     * @return array ['status' => int, 'message' => string, 'affected_records' => int]
     */
    public function renameGembaSite(string $oldName, string $newName, int $userId = null, string $userName = null): array
    {
        $oldName = trim($oldName);
        $newName = trim($newName);

        if (empty($oldName) || empty($newName)) {
            return ['status' => 0, 'message' => 'Old site name and new site name cannot be empty.'];
        }

        if ($oldName === $newName) {
            return ['status' => 1, 'message' => 'No name change requested.', 'affected_records' => 0];
        }

        $this->db->transStart();

        $affectedCounts = [];
        $totalAffected = 0;

        // 1. Update Gemba Sites Master
        $this->db->table('alert_gemba_sites')
            ->where('site_name', $oldName)
            ->update(['site_name' => $newName]);
        $cnt = $this->db->affectedRows();
        $affectedCounts['alert_gemba_sites'] = $cnt;
        $totalAffected += $cnt;

        // 2. Update Gemba Audits
        if ($this->db->tableExists('alert_gemba_audits')) {
            $this->db->table('alert_gemba_audits')
                ->where('site_name', $oldName)
                ->update(['site_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_gemba_audits'] = $cnt;
            $totalAffected += $cnt;
        }

        // Log operation
        $this->db->table('alert_client_rename_logs')->insert([
            'entity_type' => 'gemba_site',
            'old_name' => $oldName,
            'new_name' => $newName,
            'renamed_by_user_id' => $userId,
            'renamed_by_user_name' => $userName,
            'affected_records' => $totalAffected,
            'details_json' => json_encode($affectedCounts),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            log_message('error', "ClientSiteRenameService: Rename Gemba Site from '{$oldName}' to '{$newName}' failed.");
            return ['status' => 0, 'message' => 'Database transaction failed while renaming Gemba site. Changes rolled back.'];
        }

        log_message('info', "ClientSiteRenameService: Renamed Gemba Site '{$oldName}' to '{$newName}'. Affected records: {$totalAffected}");

        return [
            'status' => 1,
            'message' => "Gemba site renamed successfully from '{$oldName}' to '{$newName}'. Updated {$totalAffected} dependent record(s).",
            'affected_records' => $totalAffected,
            'details' => $affectedCounts
        ];
    }
}
