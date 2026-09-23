<?php

namespace App\Services;

use Config\Database;

class HseGembaSyncService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->ensureSchemaUpdated();
    }

    /**
     * Requirement 7: Add Required Reference Columns
     * Ensures that the required tracking columns exist in the alert_gemba_audits table.
     */
    private function ensureSchemaUpdated()
    {
        if (!$this->db->fieldExists('hse_audit_id', 'alert_gemba_audits')) {
            $this->db->query("
                ALTER TABLE alert_gemba_audits
                ADD COLUMN hse_audit_id INT NULL,
                ADD COLUMN hse_detail_id INT NULL,
                ADD COLUMN source_module VARCHAR(50) DEFAULT 'manual',
                ADD COLUMN action_category VARCHAR(255) NULL
            ");
        }
    }

    /**
     * Targeted Sync Method for a single HSE Audit
     * Call this on HSE audit save/re-audit save.
     */
    public function syncAudit($hse_audit_id)
    {
        $sql = "
            SELECT 
                d.id as detail_id,
                d.question_id,
                d.capa_json,
                d.site_category,
                d.sub_category,
                d.audit_category,
                d.nc_closed_date,
                d.nc_status,
                d.remark as hse_remarks,
                d.nc_type,
                m.hse_audit_id as master_id,
                m.audit_no,
                m.region as hse_region,
                m.auditor_name,
                m.client_name as site_name,
                m.cluster_name,
                m.account_manager,
                m.report_date,
                m.audit_date
            FROM alert_hse_audit_details d
            JOIN alert_hse_audit_master m ON d.hse_audit_id = m.hse_audit_id
            WHERE m.hse_audit_id = ?
            AND d.finding = 'NO' 
            AND d.nc_type IN ('NC', 'RD')
        ";

        $hseRecords = $this->db->query($sql, [$hse_audit_id])->getResultArray();

        $stats = [
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0
        ];

        foreach ($hseRecords as $record) {
            try {
                $this->processRecord($record, $stats);
            } catch (\Exception $e) {
                $stats['errors']++;
                log_message('error', "HseGembaSyncService Error mapping HSE Detail ID {$record['detail_id']}: " . $e->getMessage());
            }
        }

        // Handle closing of NCs if the finding changed to YES or NA in a re-audit
        $sqlYes = "
            SELECT d.question_id, d.audit_question
            FROM alert_hse_audit_details d
            WHERE d.hse_audit_id = ?
            AND d.finding IN ('YES', 'NA')
        ";
        $yesRecords = $this->db->query($sqlYes, [$hse_audit_id])->getResultArray();
        
        if (!empty($yesRecords)) {
            $master = $this->db->table('alert_hse_audit_master')->select('audit_no')->where('hse_audit_id', $hse_audit_id)->get()->getRowArray();
            if ($master) {
                foreach ($yesRecords as $yr) {
                    $builder = $this->db->table('alert_gemba_audits ga')
                        ->select('ga.gemba_sr_no, ga.nc_status')
                        ->join('alert_hse_audit_details d', 'd.id = ga.hse_detail_id')
                        ->join('alert_hse_audit_master m', 'm.hse_audit_id = d.hse_audit_id')
                        ->where('m.audit_no', $master['audit_no']);
                        
                    if (!empty($yr['question_id'])) {
                        $builder->where('d.question_id', $yr['question_id']);
                    } else {
                        $builder->where('ga.observation_point', $yr['audit_question']);
                    }
                    $existing = $builder->get()->getRowArray();
                    
                    if ($existing && $existing['nc_status'] != 1) { // 1 = Closed
                        $this->db->table('alert_gemba_audits')
                            ->where('gemba_sr_no', $existing['gemba_sr_no'])
                            ->update([
                                'nc_status' => 1,
                                'point_status' => 'Closed',
                                'point_category' => 'Closed Category',
                                'closed_date' => date('Y-m-d H:i:s'),
                                'update_date' => date('Y-m-d H:i:s')
                            ]);
                        $stats['updated']++;
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Core Sync Method (Bulk)
     * Run this to synchronize all applicable HSE NC findings into Gemba.
     */
    public function syncHseToGemba()
    {
        $sql = "
            SELECT 
                d.id as detail_id,
                d.question_id,
                d.capa_json,
                d.site_category,
                d.sub_category,
                d.audit_category,
                d.nc_closed_date,
                d.nc_status,
                d.remark as hse_remarks,
                d.nc_type,
                m.hse_audit_id as master_id,
                m.audit_no,
                m.region as hse_region,
                m.auditor_name,
                m.client_name as site_name,
                m.cluster_name,
                m.account_manager,
                m.report_date
            FROM alert_hse_audit_details d
            JOIN alert_hse_audit_master m ON d.hse_audit_id = m.hse_audit_id
            WHERE d.finding = 'NO' 
            AND d.nc_type IN ('NC', 'RD')
        ";

        $hseRecords = $this->db->query($sql)->getResultArray();

        $stats = [
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0
        ];

        foreach ($hseRecords as $record) {
            try {
                $this->processRecord($record, $stats);
            } catch (\Exception $e) {
                $stats['errors']++;
                log_message('error', "HseGembaSyncService Error mapping HSE Detail ID {$record['detail_id']}: " . $e->getMessage());
            }
        }

        return $stats;
    }

    private function processRecord($record, &$stats)
    {
        $detailId = $record['detail_id'];
        $uniqueNo = 'HSED-' . $detailId;

        // Requirement 4: Parse CAPA JSON
        $capa = json_decode($record['capa_json'] ?? '{}', true);
        if (!$capa || !is_array($capa)) {
            $capa = [];
        }

        // Safe extraction
        $observationPoint = $capa['findings']['no'] ?? 'NA';
        $risksDetails = $capa['risk']['no'] ?? 'NA';
        $actionRec = $capa['actions']['no'] ?? 'NA';
        $actionCat = $capa['action_category']['no'] ?? 'NA';
        $uaUc = $capa['ua_uc']['no'] ?? 'NA';
        $severity = $this->normalizeRisk($capa['risk_severity']['no'] ?? 'NA');
        $probability = $capa['risk_probability']['no'] ?? 'NA';
        $colorCode = $capa['color_code']['no'] ?? 'NA';
        $costType = $capa['cost_type']['no'] ?? 'NA';
        $combinedRisk = $capa['combined_risk_rating']['no'] ?? 'NA';

        // Dates
        $reportDate = !empty($record['report_date']) ? $record['report_date'] : date('Y-m-d');
        $closedDate = (!empty($record['nc_closed_date']) && strpos($record['nc_closed_date'], '0000') === false) ? $record['nc_closed_date'] : null;

        // Requirement 9: Status Synchronization
        $ncStatus = (int) ($record['nc_status'] ?? 0);
        $statusMap = [
            0 => 'Open',
            1 => 'Closed',
            2 => 'Rejected',
            3 => 'In-Progress'
        ];
        $mappedStatus = $statusMap[$ncStatus] ?? 'Open';

        if ($mappedStatus !== 'Closed') {
            $closedDate = null;
        }

        // =========================================
        // AUDIT DATE
        // =========================================

        $auditDate = !empty($record['audit_date'])
            ? date('Y-m-d', strtotime($record['audit_date']))
            : date('Y-m-d');

        // =========================================
        // TARGET DATE = REPORT DATE (NEXT AUDIT DATE) OR AUDIT DATE + 1 MONTH
        // =========================================

        $targetDate = !empty($record['report_date']) 
            ? date('Y-m-d', strtotime($record['report_date'])) 
            : date('Y-m-d', strtotime($auditDate . ' +1 month'));

        // =========================================
        // NEXT 3 DAYS DATE
        // =========================================

        $next3DaysDate = date(
            'Y-m-d',
            strtotime($auditDate . ' +3 days')
        );

        // =========================================
        // POINT STATUS
        // =========================================

        $mappedStatus = (
            strtolower(trim($record['nc_status'] ?? 'open')) == 'closed'
        )
            ? 'Closed'
            : 'Open';

        // =========================================
        // CLOSURE STATUS
        // =========================================

        $currentDate = date('Y-m-d');

        $closureStatus = 'On-Time';

        //if ($mappedStatus == 'Closed') {

        if ($currentDate > $targetDate) {

            $closureStatus = 'Delayed';

        } else {

            $closureStatus = 'On-Time';
        }
        //}

        // =========================================
        // POINT CATEGORY
        // =========================================

        $pointCategory = (
            strtolower($mappedStatus) == 'closed'
        )
            ? 'Closed Category'
            : 'Open Category';

        // Calculations
        $auditDateTs = strtotime($auditDate);
        $weeknumRaised = date('W', $auditDateTs);
        $monthRaised = date('Y-m-01', $auditDateTs);
        $yearMonthRaised = date('Y-m', $auditDateTs);
        $yearRaised = date('Y', $auditDateTs);
        $weeknumYearmonthRaised = $yearMonthRaised . '-W' . $weeknumRaised;

        $ageingDays = 0;
        if ($auditDate) {
            $endDate = $closedDate ? strtotime($closedDate) : time();
            $diff = $endDate - $auditDateTs;
            $ageingDays = floor($diff / (60 * 60 * 24));
            if ($ageingDays < 0)
                $ageingDays = 0;
        }

        $ageBracket = 'NA';
        if ($ageingDays <= 30)
            $ageBracket = '<=30';
        elseif ($ageingDays <= 60)
            $ageBracket = '.31-60';
        elseif ($ageingDays <= 90)
            $ageBracket = '.61-90';
        else
            $ageBracket = '.>90';

        // Requirement 5: Gemba Site Mapping
        $siteMapping = $this->getGembaSiteMapping($record['site_name']);

        $mappedRegion = $siteMapping ? $siteMapping['region'] : $record['hse_region'];
        $mappedSiteType1 = $siteMapping ? $siteMapping['site_type_1'] : 'NA';
        $mappedSiteType2 = $siteMapping ? $siteMapping['site_type_2'] : 'NA';
        $mappedSiteCat = $siteMapping ? $siteMapping['site_category'] : ($record['sub_category'] ?? 'NA');

        // Check for Existing Record
        $existing = null;
        
        $builder = $this->db->table('alert_gemba_audits ga')
            ->select('ga.*')
            ->join('alert_hse_audit_details d', 'd.id = ga.hse_detail_id')
            ->join('alert_hse_audit_master m', 'm.hse_audit_id = d.hse_audit_id')
            ->where('m.audit_no', $record['audit_no']);
            
        if (!empty($record['question_id'])) {
            $builder->where('d.question_id', $record['question_id']);
        } else {
            $builder->where('ga.observation_point', $observationPoint);
        }
        
        $existing = $builder->get()->getRowArray();

        $userName = session()->get('user_name') ?? 'System';

        $data = [
            'unique_no' => $existing ? $existing['unique_no'] : $uniqueNo,
            'hse_audit_id' => $record['master_id'],
            'hse_detail_id' => $detailId,
            'source_module' => 'hse_audit',
            'region' => $mappedRegion ?: 'NA',
            'auditor_name' => $record['auditor_name'] ?? 'NA',
            'audit_category' => 'HSE Audit',
            'audit_type' => 'HSE GRID AUDIT',
            'audit_doc_type' => 'HSE Audit',
            'site_name' => $record['site_name'] ?? 'NA',
            'site_type_1' => $mappedSiteType1,
            'site_type_2' => $mappedSiteType2,
            'site_category' => $mappedSiteCat,
            'audit_mail_received_date' => $auditDate,
            'audit_report_date' => $auditDate,
            'weeknum_raised' => $weeknumRaised,
            'month_raised' => $monthRaised,
            'year_month_raised' => $yearMonthRaised,
            'year_raised' => $yearRaised,
            'weeknum_yearmonth_raised' => $weeknumYearmonthRaised,
            'checklist_category' => 'HSE Audit',

            // CAPA fields mapping
            'nc_recommendation' => (strtoupper(trim($record['nc_type'] ?? 'NC')) == 'RD') ? 'RECOMMENDATION' : 'NC',
            'observation_point' => $observationPoint,
            'risks_details' => $risksDetails,
            'action_recommendation' => $actionRec,
            'action_category' => $actionCat,
            'ua_uc_type' => $uaUc,
            'risk_severity' => $severity,
            'risk_probability' => $probability,
            'color_code' => $colorCode,
            'cost_type' => $costType,
            'combined_risk_rating' => $combinedRisk,
            'target_date' => $targetDate,
            'cluster_manager_spoc' => $record['cluster_name'] ?? 'NA',
            'account_manager' => $record['account_manager'] ?? 'NA',
            'closed_date' => $closedDate,
            'point_category' => $pointCategory,
            // Status map
            'closure_status' => $closureStatus,
            'point_status' => $mappedStatus,
            'ageing_days' => $ageingDays,
            'age_bracket' => $ageBracket,
            'updated_by' => $userName,
            'status' => 1,
            'update_date' => date('Y-m-d H:i:s')
        ];

        // Requirement 14: Save User Details
        if ($existing) {
            unset($data['remarks_wm']);
            unset($data['whose_scope']);
            unset($data['remarks_corporate']);
            unset($data['followup_by']);
            unset($data['specifications']);

            $this->db->table('alert_gemba_audits')->where('gemba_sr_no', $existing['gemba_sr_no'])->update($data);
            $stats['updated']++;
        } else {
            $data['times_repeated'] = '1';
            $data['remarks_wm'] = $record['hse_remarks'] ?? 'NA';
            $data['whose_scope'] = 'HSE';
            $data['remarks_corporate'] = 'NA';
            $data['followup_by'] = $record['auditor_name'] ?? 'NA';
            $data['specifications'] = 'NA';
            $data['created_by'] = $userName;
            $data['default_date'] = date('Y-m-d H:i:s');

            $this->db->table('alert_gemba_audits')->insert($data);
            $stats['inserted']++;
        }
    }

    /**
     * Requirement 5: Gemba Site Mapping using site_name
     */
    private function getGembaSiteMapping($siteName)
    {
        if (empty($siteName))
            return null;

        return $this->db->table('alert_gemba_sites')
            ->where('site_name', $siteName)
            ->get()->getRowArray();
    }

    /**
     * Requirement 13: Normalize Dropdown Values
     */
    private function normalizeRisk($risk)
    {
        $r = trim(strtolower($risk));
        if ($r === 'very high' || $r === 'critical') {
            return 'High';
        }
        return $risk;
    }
}

