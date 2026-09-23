<?php

namespace App\Models;

use CodeIgniter\Model;

class RtWeeklyMlModel extends Model
{
    protected $table = 'alert_gemba_audits';
    protected $primaryKey = 'gemba_sr_no';
    protected $returnType = 'array';
    protected $protectFields = false;

    /**
     * Standard region column names required for the report
     */
    protected $reportRegions = ['HO', 'North', 'South', 'TPT', 'West-1', 'West-2'];

    public function __construct()
    {
        parent::__construct();
        helper(['gemba_acl', 'designation_acl']);
    }

    /**
     * Apply common role-based visibility and dataset consistency filters
     */
    protected function applySecurityAndBaseFilters(&$builder, $filters = [])
    {
        $builder->where('status !=', 2);

        // Apply Role-Based Visibility if helper function is available
        if (function_exists('apply_gemba_role_filters')) {
            apply_gemba_role_filters($builder, 'alert_gemba_audits');
        }

        // Only include the latest HSE Audits + Manual Gemba records
        $builder->groupStart()
                ->where("hse_audit_id IS NULL")
                ->orWhere("hse_audit_id IN (SELECT MAX(hse_audit_id) FROM alert_hse_audit_master GROUP BY audit_no)", null, false)
                ->groupEnd();

        // Apply dynamic UI filters
        if (!empty($filters['audit_category'])) {
            $cats = is_array($filters['audit_category']) ? $filters['audit_category'] : explode(',', $filters['audit_category']);
            $cats = array_filter($cats, function($v) { return trim((string)$v) !== '' && $v !== 'All' && $v !== 'selectAll'; });
            if (!empty($cats)) {
                $builder->whereIn('audit_category', $cats);
            }
        }


    }

    /**
     * Fetch filter dropdown options dynamically from the database
     */
    public function getFilterOptions()
    {
        $db = \Config\Database::connect();
        
        // Audit Categories
        $catBuilder = $db->table('alert_gemba_audits')
            ->select('DISTINCT(audit_category) as name')
            ->where('status', 1)
            ->where('audit_category !=', 'NA')
            ->where('audit_category IS NOT NULL')
            ->where('audit_category !=', '')
            ->orderBy('audit_category', 'ASC');
        if (function_exists('apply_gemba_role_filters')) {
            apply_gemba_role_filters($catBuilder, 'alert_gemba_audits');
        }
        $auditCategories = $catBuilder->get()->getResultArray();



        return [
            'audit_categories' => $auditCategories
        ];
    }

    /**
     * Normalize region string to match standardized column headings
     */
    protected function normalizeRegion($region)
    {
        $reg = trim((string)$region);
        if (strcasecmp($reg, 'HO') === 0) return 'HO';
        if (strcasecmp($reg, 'North') === 0) return 'North';
        if (strcasecmp($reg, 'South') === 0) return 'South';
        if (strcasecmp($reg, 'TPT') === 0) return 'TPT';
        if (in_array(strtolower($reg), ['west-1', 'west 1', 'west1'])) return 'West-1';
        if (in_array(strtolower($reg), ['west-2', 'west 2', 'west2'])) return 'West-2';
        return 'HO'; // Default/fallback for unexpected region strings to ensure counts aren't lost
    }

    /**
     * Main method to generate report sections based on Start Date, End Date, and Filters
     */
    public function getReportData($startDate, $endDate, $filters = [])
    {
        $db = \Config\Database::connect();

        $cleanStart = date('Y-m-d', strtotime($startDate));
        $cleanEnd   = date('Y-m-d', strtotime($endDate));

        // Get list of categories to display
        $targetCategories = [];
        if (!empty($filters['audit_category'])) {
            $cats = is_array($filters['audit_category']) ? $filters['audit_category'] : explode(',', $filters['audit_category']);
            $cats = array_filter($cats, function($v) { return trim((string)$v) !== '' && $v !== 'All' && $v !== 'selectAll'; });
            foreach ($cats as $c) {
                $targetCategories[$c] = trim((string)$c);
            }
        }

        // If All selected or empty, query distinct categories
        if (empty($targetCategories)) {
            $catBuilder = $db->table('alert_gemba_audits')
                ->select('DISTINCT(audit_category) as name')
                ->where('status', 1)
                ->where('audit_category !=', 'NA')
                ->where('audit_category IS NOT NULL')
                ->where('audit_category !=', '')
                ->orderBy('audit_category', 'ASC');
            if (function_exists('apply_gemba_role_filters')) {
                apply_gemba_role_filters($catBuilder, 'alert_gemba_audits');
            }
            $res = $catBuilder->get()->getResultArray();
            foreach ($res as $r) {
                $targetCategories[$r['name']] = $r['name'];
            }
        }

        // If still empty, supply at least standard category fallback
        if (empty($targetCategories)) {
            $targetCategories = ['HSE Audit' => 'HSE Audit', 'Gemba Audit' => 'Gemba Audit', 'Normal Audit' => 'Normal Audit'];
        }

        // Build main aggregation query in SQL for optimal performance
        $builder = $db->table('alert_gemba_audits');
        $this->applySecurityAndBaseFilters($builder, $filters);

        // Select counts aggregated by category and region
        $builder->select("
            COALESCE(audit_category, 'Uncategorized') as audit_category,
            COALESCE(region, 'HO') as region,
            SUM(
                CASE 
                    WHEN LOWER(point_status) != 'closed' 
                     AND (target_date >= '{$cleanStart}' AND target_date <= '{$cleanEnd}' AND target_date IS NOT NULL AND target_date != '0000-00-00')
                    THEN 1 ELSE 0 
                END
            ) as opening_count,
            SUM(
                CASE 
                    WHEN LOWER(point_status) = 'closed' 
                     AND (closed_date >= '{$cleanStart}' AND closed_date <= '{$cleanEnd}' AND closed_date IS NOT NULL AND closed_date != '0000-00-00') 
                    THEN 1 ELSE 0 
                END
            ) as closed_count,
            SUM(
                CASE 
                    WHEN (audit_report_date >= '{$cleanStart}' AND audit_report_date <= '{$cleanEnd}' AND audit_report_date IS NOT NULL AND audit_report_date != '0000-00-00') 
                    THEN 1 ELSE 0 
                END
            ) as new_count,
            SUM(
                CASE 
                    WHEN LOWER(point_status) != 'closed' 
                     AND (target_date <= '{$cleanEnd}' AND target_date IS NOT NULL AND target_date != '0000-00-00')
                    THEN 1 ELSE 0 
                END
            ) as closing_count
        ", false);

        $builder->groupBy('audit_category, region');
        $rawResults = $builder->get()->getResultArray();

        // Initialize structures for the 4 sections
        $initRow = [
            'audit_category' => '',
            'HO'             => 0,
            'North'          => 0,
            'South'          => 0,
            'TPT'            => 0,
            'West-1'         => 0,
            'West-2'         => 0,
            'Total'          => 0
        ];

        $openingRows  = [];
        $closedRows   = [];
        $newRows      = [];
        $closingRows  = [];

        foreach ($targetCategories as $catName) {
            $r1 = $initRow; $r1['audit_category'] = $catName;
            $r2 = $initRow; $r2['audit_category'] = $catName;
            $r3 = $initRow; $r3['audit_category'] = $catName;
            $r4 = $initRow; $r4['audit_category'] = $catName;

            $openingRows[$catName] = $r1;
            $closedRows[$catName]  = $r2;
            $newRows[$catName]     = $r3;
            $closingRows[$catName] = $r4;
        }

        $hasRecords = false;

        // Process SQL aggregation results into matrices
        foreach ($rawResults as $row) {
            $cat = trim((string)$row['audit_category']);
            if (!isset($openingRows[$cat])) {
                // Incorporate uncategorized or unexpected categories if returned by query
                $openingRows[$cat]  = array_merge($initRow, ['audit_category' => $cat]);
                $closedRows[$cat]   = array_merge($initRow, ['audit_category' => $cat]);
                $newRows[$cat]      = array_merge($initRow, ['audit_category' => $cat]);
                $closingRows[$cat]  = array_merge($initRow, ['audit_category' => $cat]);
            }

            $regCol = $this->normalizeRegion($row['region']);

            $op  = (int)$row['opening_count'];
            $cl  = (int)$row['closed_count'];
            $nw  = (int)$row['new_count'];
            $cls = (int)($row['closing_count'] ?? 0);

            if ($op > 0 || $cl > 0 || $nw > 0 || $cls > 0) {
                $hasRecords = true;
            }

            $openingRows[$cat][$regCol] += $op;
            $closedRows[$cat][$regCol]  += $cl;
            $newRows[$cat][$regCol]     += $nw;
            $closingRows[$cat][$regCol] += $cls;
        }

        // Calculate Totals per row
        foreach ($openingRows as $cat => &$oRow) {
            $cRow = &$closedRows[$cat];
            $nRow = &$newRows[$cat];
            $endRow = &$closingRows[$cat];

            $oTotal = 0; $cTotal = 0; $nTotal = 0; $endTotal = 0;

            foreach ($this->reportRegions as $reg) {
                $oVal = $oRow[$reg];
                $cVal = $cRow[$reg];
                $nVal = $nRow[$reg];
                $endVal = $endRow[$reg];

                $oTotal   += $oVal;
                $cTotal   += $cVal;
                $nTotal   += $nVal;
                $endTotal += $endVal;
            }

            $oRow['Total']   = $oTotal;
            $cRow['Total']   = $cTotal;
            $nRow['Total']   = $nTotal;
            $endRow['Total'] = $endTotal;

            if ($endTotal > 0) {
                $hasRecords = true;
            }
        }
        unset($oRow, $cRow, $nRow, $endRow);

        // Build Grand Total Rows
        $openingGrand = array_merge($initRow, ['audit_category' => 'Grand Total']);
        $closedGrand  = array_merge($initRow, ['audit_category' => 'Grand Total']);
        $newGrand     = array_merge($initRow, ['audit_category' => 'Grand Total']);
        $closingGrand = array_merge($initRow, ['audit_category' => 'Grand Total']);

        foreach ($this->reportRegions as $reg) {
            foreach ($openingRows as $row) {
                $openingGrand[$reg]  += $row[$reg];
                $openingGrand['Total'] += $row[$reg];
            }
            foreach ($closedRows as $row) {
                $closedGrand[$reg]  += $row[$reg];
                $closedGrand['Total'] += $row[$reg];
            }
            foreach ($newRows as $row) {
                $newGrand[$reg]  += $row[$reg];
                $newGrand['Total'] += $row[$reg];
            }
            foreach ($closingRows as $row) {
                $closingGrand[$reg]  += $row[$reg];
                $closingGrand['Total'] += $row[$reg];
            }
        }

        return [
            'start_date' => $cleanStart,
            'end_date'   => $cleanEnd,
            'has_records'=> $hasRecords,
            'sections'   => [
                'opening' => [
                    'title'       => 'Opening',
                    'subtitle'    => "As on " . date('d M Y', strtotime($cleanStart)),
                    'rows'        => array_values($openingRows),
                    'grand_total' => $openingGrand
                ],
                'closed' => [
                    'title'       => 'Closed',
                    'subtitle'    => "From " . date('d M Y', strtotime($cleanStart)) . " To " . date('d M Y', strtotime($cleanEnd)),
                    'rows'        => array_values($closedRows),
                    'grand_total' => $closedGrand
                ],
                'new_generated' => [
                    'title'       => 'New Points Added',
                    'subtitle'    => "From " . date('d M Y', strtotime($cleanStart)) . " To " . date('d M Y', strtotime($cleanEnd)),
                    'rows'        => array_values($newRows),
                    'grand_total' => $newGrand
                ],
                'closing' => [
                    'title'       => 'Closing',
                    'subtitle'    => "As on " . date('d M Y', strtotime($cleanEnd)),
                    'rows'        => array_values($closingRows),
                    'grand_total' => $closingGrand
                ]
            ]
        ];
    }
}
