<?php

namespace App\Models;

use CodeIgniter\Model;

class GembaAuditModel extends Model
{
    protected $table = 'alert_gemba_audits';
    protected $primaryKey = 'gemba_sr_no';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'unique_no',
        'region',
        'auditor_name',
        'audit_category',
        'audit_type',
        'audit_doc_type',
        'site_name',
        'site_type_1',
        'site_type_2',
        'site_category',
        'audit_mail_received_date',
        'audit_report_date',
        'weeknum_raised',
        'month_raised',
        'year_month_raised',
        'year_raised',
        'weeknum_yearmonth_raised',
        'checklist_category',
        'nc_recommendation',
        'qhse_remarks',
        'observation_point',
        'risks_details',
        'action_recommendation',
        'specifications',
        'ua_uc_type',
        'risk_severity',
        'risk_probability',
        'color_code',
        'cost_type',
        'remarks_wm',
        'whose_scope',
        'remarks_corporate',
        'target_date',
        'closed_date',
        'point_status',
        'point_category',
        'closure_status',
        'weeknum_closed',
        'month_closed',
        'year_month_closed',
        'risk_severity_rating',
        'risk_probability_rating',
        'combined_risk_rating',
        'times_repeated',
        'combined_risk_freq',
        'final_rating',
        'ageing_days',
        'age_bracket',
        'followup_by',
        'created_by',
        'updated_by',
        'status',
        'weeknum_yearmonth_closed',
        'hse_audit_id',
        'hse_detail_id',
        'source_module',
        'action_category',
        'cluster_manager_spoc',
        'nc_status',
        'nc_remark',
        'nc_worked_by',
        'nc_after_photo',
        'nc_cluster_reviewed_by',
        'nc_cluster_reviewed_date',
        'nc_auditor_reviewed_by',
        'nc_auditor_reviewed_date',
        'nc_closed_by',
        'nc_closed_date',
        'nc_rejected_by',
        'nc_rejected_date',
        'rejection_reason',
        'account_manager',
        'working_remarks',
        'working_uploaded_file',
        'working_date',
        'closed_remarks',
        'closed_uploaded_file'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'default_date';
    protected $updatedField = 'update_date';

    protected $beforeInsert = ['beforeInsertHook'];
    protected $beforeUpdate = ['beforeUpdateHook'];

    protected function beforeInsertHook(array $data)
    {
        if (isset($data['data'])) {
            $data['data']['created_by'] = session()->get('user_name') ?? 'System';
            $data['data']['updated_by'] = session()->get('user_name') ?? 'System';
        }
        return $this->calculateRatingsAndDates($data);
    }

    protected function beforeUpdateHook(array $data)
    {
        if (isset($data['data'])) {
            $data['data']['updated_by'] = session()->get('user_name') ?? 'System';
        }
        return $this->calculateRatingsAndDates($data);
    }

    protected function calculateRatingsAndDates(array $data)
    {
        if (!isset($data['data'])) {
            return $data;
        }

        $row = $data['data'];

        // Ratings Calculation
        $sev = 1;
        if (isset($row['risk_severity'])) {
            $sevStr = strtolower($row['risk_severity']);
            if ($sevStr === 'low')
                $sev = 1;
            elseif ($sevStr === 'medium')
                $sev = 3;
            elseif ($sevStr === 'high')
                $sev = 4;
            $row['risk_severity_rating'] = $sev;
        } else {
            $sev = isset($row['risk_severity_rating']) ? $row['risk_severity_rating'] : 1;
        }

        $prob = 1;
        if (isset($row['risk_probability'])) {
            $probStr = strtolower($row['risk_probability']);
            if ($probStr === 'low')
                $prob = 1;
            elseif ($probStr === 'medium')
                $prob = 3;
            elseif ($probStr === 'high')
                $prob = 4;
            $row['risk_probability_rating'] = $prob;
        } else {
            $prob = isset($row['risk_probability_rating']) ? $row['risk_probability_rating'] : 1;
        }

        $row['combined_risk_rating'] = $sev * $prob;

        $timesRepeated = isset($row['times_repeated']) ? (int) $row['times_repeated'] : 1;
        $row['combined_risk_freq'] = $sev * $prob * $timesRepeated;

        $colorMult = 1;
        if (isset($row['color_code'])) {
            $row['color_code'] = ucfirst(strtolower(trim($row['color_code'])));
            $colorStr = strtolower($row['color_code']);
            if ($colorStr === 'red')
                $colorMult = 4;
            elseif ($colorStr === 'black')
                $colorMult = 6;
            elseif ($colorStr === 'yellow')
                $colorMult = 2;
            elseif ($colorStr === 'green')
                $colorMult = 1;
        }

        $costMult = 1;
        if (isset($row['cost_type'])) {
            $costStr = strtolower($row['cost_type']);
            if ($costStr === 'capex')
                $costMult = 3;
            elseif ($costStr === 'opex')
                $costMult = 1;
        }

        $row['final_rating'] = $sev * $prob * $timesRepeated * $colorMult * $costMult;

        // Dates & Computed Week/Month/Year logic
        if (isset($row['audit_report_date']) && !empty($row['audit_report_date'])) {
            $reportDate = strtotime($row['audit_report_date']);
            $row['weeknum_raised'] = date('W', $reportDate);
            $row['month_raised'] = date('Y-m-01', $reportDate);
            $row['year_month_raised'] = date('Y-m', $reportDate);
            $row['year_raised'] = date('Y', $reportDate);
            $row['weeknum_yearmonth_raised'] = date('Y-m', $reportDate) . '-W' . date('W', $reportDate);
        }

        if (isset($row['point_status']) && $row['point_status'] === 'Closed' && isset($row['closed_date']) && !empty($row['closed_date'])) {
            $closedDate = strtotime($row['closed_date']);
            $row['weeknum_closed'] = date('W', $closedDate);
            $row['month_closed'] = date('Y-m-01', $closedDate);
            $row['year_month_closed'] = date('Y-m', $closedDate);
            $row['weeknum_yearmonth_closed'] = date('Y-m', $closedDate) . '-W' . date('W', $closedDate);
            
            // Ageing days for closed points
            if (isset($row['audit_report_date']) && !empty($row['audit_report_date'])) {
                $reportDate = strtotime($row['audit_report_date']);
                $diff = $closedDate - $reportDate;
                $days = floor($diff / (60 * 60 * 24));
                $row['ageing_days'] = $days >= 0 ? $days : 0;
                
                if ($row['ageing_days'] <= 30) {
                    $row['age_bracket'] = '<=30';
                } elseif ($row['ageing_days'] <= 60) {
                    $row['age_bracket'] = '.31-60';
                } elseif ($row['ageing_days'] <= 90) {
                    $row['age_bracket'] = '.61-90';
                } else {
                    $row['age_bracket'] = '.>90';
                }
            }
        } else {
            $row['weeknum_closed'] = null;
            $row['month_closed'] = null;
            $row['year_month_closed'] = null;
            $row['weeknum_yearmonth_closed'] = null;
            if (isset($row['point_status']) && $row['point_status'] === 'Open') {
                $row['closed_date'] = null; // Clear closed date if open
                
                // Ageing days for open points
                if (isset($row['audit_report_date']) && !empty($row['audit_report_date'])) {
                    $reportDate = strtotime($row['audit_report_date']);
                    $diff = time() - $reportDate;
                    $days = floor($diff / (60 * 60 * 24));
                    $row['ageing_days'] = $days >= 0 ? $days : 0;
                    
                    if ($row['ageing_days'] <= 30) {
                        $row['age_bracket'] = '<=30';
                    } elseif ($row['ageing_days'] <= 60) {
                        $row['age_bracket'] = '.31-60';
                    } elseif ($row['ageing_days'] <= 90) {
                        $row['age_bracket'] = '.61-90';
                    } else {
                        $row['age_bracket'] = '.>90';
                    }
                }
            }
        }

        // Generate unique_no if not present
        if (!isset($row['unique_no']) && empty($row['unique_no'])) {
            // we will set it after insert if needed, or generate a random one
            $row['unique_no'] = 'GMB-' . time() . '-' . rand(1000, 9999);
        }

        // NA Fallback for string columns
        $stringCols = [
            'region', 'auditor_name', 'audit_category', 'audit_type', 'audit_doc_type',
            'site_name', 'site_type_1', 'site_type_2', 'site_category',
            'checklist_category', 'nc_recommendation', 'qhse_remarks', 'observation_point',
            'risks_details', 'action_recommendation', 'specifications', 'ua_uc_type',
            'risk_severity', 'risk_probability', 'color_code', 'cost_type',
            'remarks_wm', 'whose_scope', 'remarks_corporate', 'point_status',
            'point_category', 'closure_status', 'followup_by', 'cluster_manager_spoc',
            'account_manager'
        ];

        foreach ($stringCols as $col) {
            if (array_key_exists($col, $row) && trim((string)$row[$col]) === '') {
                $row[$col] = 'NA';
            }
        }

        $data['data'] = $row;
        return $data;
    }

    public function refreshAgeing()
    {
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        $builder->where('point_status', 'Open');
        $builder->set('ageing_days', 'DATEDIFF(CURDATE(), audit_report_date)', false);
        $builder->set('age_bracket', "CASE 
                WHEN DATEDIFF(CURDATE(), audit_report_date) <= 30 THEN '<=30'
                WHEN DATEDIFF(CURDATE(), audit_report_date) <= 60 THEN '.31-60'
                WHEN DATEDIFF(CURDATE(), audit_report_date) <= 90 THEN '.61-90'
                ELSE '.>90'
            END", false);
        $builder->update();
    }
}
