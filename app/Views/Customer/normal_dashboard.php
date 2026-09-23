<?php $this->extend('Layout/base_admin'); ?>
<?php $this->section("breadcrumb_title_li"); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="<?= base_url('js/select2.min.css') ?>">
<style>
    /* Select2 inside OE filter — match Bootstrap sm control height and responsive */
    .oe-filter-card .select2-container {
        width: 100% !important;
    }
    .oe-filter-card .select2-container .select2-selection--multiple {
        min-height: 38px;
        max-height: 75px; /* Prevent uncontrolled vertical growth */
        overflow-y: auto;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        font-size: 0.875rem;
        padding: 2px 4px;
        scrollbar-width: thin; /* Firefox */
    }
    /* Webkit scrollbar for select2 */
    .oe-filter-card .select2-container .select2-selection--multiple::-webkit-scrollbar {
        width: 4px;
    }
    .oe-filter-card .select2-container .select2-selection--multiple::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 4px;
    }


    .select2-all-clear { font-size:.75rem; }
    
    /* Dropdown list items wrapping */
    .select2-container--default .select2-results__option {
        word-wrap: break-word;
        white-space: normal;
        font-size: 0.875rem;
    }
</style>

<style>
    /* ============================================================
       OE AUDIT DASHBOARD  –  Responsive Redesign (HSE-style)
    ============================================================ */

    /* --- Inherit theme font (Poppins) — exclude icon elements --- */
    .oe-wrap {
        font-family: 'Poppins', sans-serif;
    }

    /* Apply Poppins to text nodes only, never to icon elements */
    .oe-wrap *:not(i):not([class*="fa"]):not([class*="fas"]):not([class*="far"]):not([class*="fab"]):not([class*="fal"]) {
        font-family: 'Poppins', sans-serif;
    }

    /* DataTables controls inherit font too — same exclusion */
    div[id$="_wrapper"] *:not(i):not([class*="fa"]):not([class*="fas"]):not([class*="far"]):not([class*="fab"]) {
        font-family: 'Poppins', sans-serif;
    }

    /* Ensure Font Awesome icons always keep their icon font */
    .oe-wrap i[class*="fa"],
    div[id$="_wrapper"] i[class*="fa"] {
        font-family: "Font Awesome 5 Free", "Font Awesome 5 Brands", "FontAwesome" !important;
    }

    /* --- Base reset / spacing --- */
    .oe-wrap {
        padding: 0 4px;
    }

    /* ---- Page Header ---- */
    .oe-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 1.25rem;
    }

    .oe-page-header h2 {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .oe-page-header p {
        font-size: 1rem;
        color: #9a9aa0;
        margin: 2px 0 0;
    }

    /* ---- Filter card ---- */
    .oe-filter-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 1px 8px rgba(0, 0, 0, .07);
    }

    .oe-filter-card .form-select,
    .oe-filter-card .form-control {
        border-radius: 8px;
        font-size: 1rem;
    }

    .oe-filter-card .form-label {
        font-size: 1rem;
    }

    /* ---- KPI cards — reference style ---- */
    .kpi-card {
        border-radius: 14px;
        border: 1.5px solid #ececec;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .055);
        transition: transform .18s, box-shadow .18s;
        height: 100%;
        overflow: hidden;
        position: relative;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, .11);
    }

    /* decorative bubble — bottom-right */
    .kpi-card::after {
        content: '';
        position: absolute;
        bottom: -22px;
        right: -22px;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        opacity: .13;
        pointer-events: none;
    }

    .kpi-card.kpi-blue::after {
        background: #2563eb;
    }

    .kpi-card.kpi-indigo::after {
        background: #4f46e5;
    }

    .kpi-card.kpi-amber::after {
        background: #d97706;
    }

    .kpi-card.kpi-green::after {
        background: #16a34a;
    }

    .kpi-card.kpi-orange::after {
        background: #ea580c;
    }

    .kpi-card.kpi-purple::after {
        background: #7c3aed;
    }

    .kpi-card .card-body {
        padding: 1.05rem 1.15rem;
        display: flex;
        align-items: center;
        gap: 14px;
        position: relative;
        z-index: 1;
    }

    /* square icon */
    .kpi-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        color: #fff;
    }

    .kpi-icon-box.bg-kpi-blue {
        background: #2563eb;
    }

    .kpi-icon-box.bg-kpi-indigo {
        background: #4f46e5;
    }

    .kpi-icon-box.bg-kpi-amber {
        background: #d97706;
    }

    .kpi-icon-box.bg-kpi-green {
        background: #16a34a;
    }

    .kpi-icon-box.bg-kpi-orange {
        background: #ea580c;
    }

    .kpi-icon-box.bg-kpi-purple {
        background: #7c3aed;
    }

    /* text block */
    .kpi-text {
        flex: 1;
        min-width: 0;
    }

    .kpi-label {
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #9a9aa0;
        margin-bottom: 2px;
        word-wrap: break-word;
        white-space: normal;
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.05;
        color: #1a1a2e;
        margin: 0;
    }

    .kpi-sub {
        font-size: .80rem;
        color: #9a9aa0;
        margin-top: 2px;
        word-wrap: break-word;
        white-space: normal;
    }

    .kpi-sub .sub-hi {
        font-weight: 700;
        color: #4b5563;
    }


    /* ---- Responsive Table Wrapper ---- */
    .tbl-scroll-x {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .tbl-scroll-x .table {
        margin-bottom: 0;
    }

    /* ---- Tabbed Reports (.sec-tabs) ---- */
    .sec-tabs {
        border-bottom: 2px solid #e2e8f0;
        gap: 0.5rem;
        padding-bottom: 0px;
        margin-bottom: 0;
    }

    .sec-tabs .nav-link {
        color: #64748b;
        font-weight: 600;
        font-size: 0.95rem;
        border-radius: 8px 8px 0 0;
        padding: 0.6rem 1.25rem;
        border: 1px solid transparent;
        border-bottom: none;
        transition: all 0.2s ease-in-out;
        background: #f8fafc;
        margin-bottom: -2px;
        white-space: nowrap;
    }

    .sec-tabs .nav-link.active {
        color: #fff;
        background: #3b82f6;
        border-color: #3b82f6;
        box-shadow: 0 -2px 10px rgba(59, 130, 246, 0.15);
    }

    .sec-tabs .nav-link:hover:not(.active) {
        background: #f1f5f9;
        color: #334155;
    }

    /* ---- DataTables Overrides ---- */
    .oe-table th {
        background: #f8fafc !important;
        font-size: .88rem;
        font-weight: 700;
        color: #475569;
        white-space: nowrap;
        text-transform: uppercase;
        border-bottom: 2px solid #e2e8f0;
    }

    /* Account Wise Aging — category column headers must stay horizontal */
    #OE_aging_table thead th {
        white-space: normal !important;
        word-break: break-word !important;
        vertical-align: middle !important;
        writing-mode: horizontal-tb !important;
        text-orientation: mixed !important;
        max-width: 140px;
        text-align: center;
    }

    /* responsive */
    @media (max-width: 1199px) {
        .kpi-value {
            font-size: 1.7rem;
        }
    }

    @media (max-width: 767px) {
        .kpi-value {
            font-size: 1.45rem;
        }

        .kpi-icon-box {
            width: 44px;
            height: 44px;
            font-size: 1.2rem;
        }
    }

    @media (max-width: 575px) {
        .kpi-value {
            font-size: 1.25rem;
        }

        .kpi-card .card-body {
            gap: 10px;
            padding: .85rem 1rem;
        }
    }

    /* ---- Generic card base ---- */
    .oe-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .07);
        height: 100%;
    }

    .oe-card .card-header {
        background: #fff;
        border-bottom: 1px solid #f1f1f4;
        padding: 1.15rem 1.35rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }

    .oe-card .card-body {
        padding: 1.35rem;
    }

    .oe-card .ch-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1a1a2e;
    }

    .oe-card .ch-sub {
        font-size: 1rem;
        color: #9a9aa0;
        margin-top: 1px;
    }

    /* ---- Score chart ---- */
    .score-donut-wrap {
        position: relative;
        flex-shrink: 0;
        width: 180px;
        height: 180px;
    }

    .score-donut-wrap canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .donut-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
    }

    .donut-center .dc-val {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1a1a2e;
        line-height: 1;
    }

    .donut-center .dc-lbl {
        font-size: 1rem;
        color: #9a9aa0;
        font-weight: 600;
        margin-top: 2px;
    }

    /* score legend */
    .score-legend {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .score-legend li {
        display: flex;
        align-items: center;
        padding: 3px 0;
        font-size: 1rem;
    }

    .score-legend .dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-right: 7px;
    }

    .score-legend .lname {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        min-width: 0;
    }

    .score-legend .lscore {
        font-weight: 700;
        margin-left: 8px;
        flex-shrink: 0;
    }

    /* ---- Bar chart ---- */
    .bar-chart-wrap {
        width: 100%;
    }

    /* ---- Status pills ---- */
    .stat-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 1rem;
    }

    .stat-pill {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 8px 14px;
        border-radius: 10px;
        flex: 1;
        min-width: 70px;
    }

    .stat-pill .sp-val {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1;
    }

    .stat-pill .sp-lbl {
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-top: 3px;
    }

    /* ---- DataTables custom styling ---- */
    .oe-dt-wrapper .dataTables_length select,
    .oe-dt-wrapper .dataTables_filter input,
    div[id$="_wrapper"] .dataTables_length select,
    div[id$="_wrapper"] .dataTables_filter input {
        border: 1.5px solid #e0e4ef;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 1rem;
        outline: none;
        min-height: 32px;
    }

    div[id$="_wrapper"] .dataTables_filter input:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 2px rgba(67, 97, 238, .12);
    }

    div[id$="_wrapper"] .dataTables_filter input::placeholder {
        color: #b0b7c9;
    }

    div[id$="_wrapper"] .dataTables_info {
        font-size: 1rem;
        color: #6c757d;
        padding-top: 8px;
    }

    div[id$="_wrapper"] .dataTables_paginate {
        padding-top: 10px;
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
    }

    div[id$="_wrapper"] .dataTables_paginate span {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
    }

    div[id$="_wrapper"] .dataTables_paginate .paginate_button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px !important;
        font-size: 1rem !important;
        font-weight: 600 !important;
        padding: 4px 11px !important;
        margin: 2px 2px !important;
        border: 1.5px solid transparent !important;
        color: #4361ee !important;
        transition: all .14s;
    }

    div[id$="_wrapper"] .dataTables_paginate .paginate_button.current {
        background: #4361ee !important;
        color: #fff !important;
        border-color: #4361ee !important;
    }

    div[id$="_wrapper"] .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
        background: #eef2ff !important;
        color: #4361ee !important;
        border-color: #c7d0f8 !important;
    }

    div[id$="_wrapper"] .dataTables_paginate .paginate_button.disabled {
        color: #c5cbe3 !important;
        cursor: default;
    }

    div[id$="_wrapper"] .dataTables_length label,
    div[id$="_wrapper"] .dataTables_filter label {
        font-size: 1rem;
        color: #6c757d;
        font-weight: 500;
    }

    div[id$="_wrapper"] .dataTables_length select,
    div[id$="_wrapper"] .dataTables_filter input {
        font-size: 1rem;
    }

    div[id$="_wrapper"] .dataTables_length {
        margin-bottom: 4px;
    }

    /* scrollBody custom scrollbar */
    div[id$="_wrapper"] .dataTables_scrollBody {
        border-bottom: 1px solid #e5e7eb !important;
    }

    div[id$="_wrapper"] .dataTables_scrollBody::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    div[id$="_wrapper"] .dataTables_scrollBody::-webkit-scrollbar-track {
        background: #f8f9fc;
        border-radius: 4px;
    }

    div[id$="_wrapper"] .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #c5cbe3;
        border-radius: 4px;
    }

    div[id$="_wrapper"] .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
        background: #4361ee;
    }

    div[id$="_wrapper"] .dataTables_scrollHead {
        overflow: hidden !important;
    }

    div[id$="_wrapper"] .dataTables_scrollHeadInner {
        box-sizing: border-box !important;
    }

    /* tab badge count */
    #oeMainTabs .badge {
        vertical-align: middle;
    }

    .oe-table thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-size: 1rem;
        white-space: nowrap;
        font-weight: 700;
    }

    .oe-table tbody td {
        font-size: 1rem;
        vertical-align: middle;
    }

    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover {
        background: #f1f5ff !important;
    }

    /* aging badge */
    .badge-aging-ok {
        background: #dcfce7;
        color: #15803d;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 1rem;
        font-weight: 700;
    }

    .badge-aging-bad {
        background: #fee2e2;
        color: #b91c1c;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 1rem;
        font-weight: 700;
    }

    /* ---- Section tabs ---- */
    .sec-tabs .nav-link {
        font-size: 1rem;
        font-weight: 600;
        color: #6c757d;
        border: none;
        padding: .45rem 1rem;
        border-radius: 8px;
        white-space: nowrap;
    }

    .sec-tabs .nav-link.active {
        background: #0d6efd;
        color: #fff;
    }

    /* ---- Upcoming list ---- */
    .upcoming-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 9px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .upcoming-item:last-child {
        border-bottom: none;
    }

    .udate-badge {
        flex-shrink: 0;
        min-width: 40px;
        text-align: center;
        border-radius: 8px;
        padding: 4px 6px;
    }

    .udate-badge .ud-day {
        font-size: 1rem;
        font-weight: 800;
        line-height: 1;
    }

    .udate-badge .ud-mon {
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .u-title {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.3;
    }

    .u-meta {
        font-size: 1rem;
        color: #9a9aa0;
    }

    .u-badge {
        font-size: 1rem;
        padding: 2px 8px;
        border-radius: 20px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* scroll container */
    #oeUpcomingScroller {
        scrollbar-width: thin;
        scrollbar-color: #c5cbe3 #f8f9fc;
    }

    #oeUpcomingScroller::-webkit-scrollbar {
        width: 5px;
    }

    #oeUpcomingScroller::-webkit-scrollbar-track {
        background: #f8f9fc;
        border-radius: 4px;
    }

    #oeUpcomingScroller::-webkit-scrollbar-thumb {
        background: #c5cbe3;
        border-radius: 4px;
    }

    #oeUpcomingScroller::-webkit-scrollbar-thumb:hover {
        background: #4361ee;
    }

    /* ---- Quick Actions ---- */
    .qa-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 10px;
        background: #f8f9fc;
        border: 1px solid #e8eaf0;
        text-decoration: none;
        color: inherit;
        transition: background .14s;
    }

    .qa-item:hover {
        background: #e8f0fe;
        color: inherit;
    }

    .qa-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    .qa-title {
        font-size: 1rem;
        font-weight: 700;
    }

    .qa-sub {
        font-size: 1rem;
        color: #6c757d;
    }

    /* ---- No-data ---- */
    .no-data {
        text-align: center;
        padding: 36px 20px;
        color: #bbb;
        font-size: 1rem;
        font-weight: 600;
        border: 1px dashed #ddd;
        border-radius: 8px;
    }

    /* ---- Responsive tweaks ---- */
    @media (max-width: 1199px) {
        .score-donut-wrap {
            width: 150px;
            height: 150px;
        }

        .donut-center .dc-val {
            font-size: 1.05rem;
        }

        .kpi-value {
            font-size: 1.6rem;
        }
    }

    @media (max-width: 767px) {
        .oe-page-header h2 {
            font-size: 1.1rem;
        }

        .kpi-value {
            font-size: 1.45rem;
        }

        .score-donut-wrap {
            width: 130px;
            height: 130px;
        }

        .stat-pill {
            min-width: 60px;
        }

        .sec-tabs .nav-link {
            font-size: 1rem;
            padding: .35rem .7rem;
        }
    }

    @media (max-width: 575px) {
        .kpi-card .card-body {
            padding: .75rem .9rem;
        }

        .kpi-value {
            font-size: 1.35rem;
        }
    }
</style>
<?php $this->endSection(); ?>
<?php $this->section("main_body"); ?>
<?php
helper('designation_acl');
echo showACLMessage('info');

if (!function_exists('fmt_oe_score')) {
    function fmt_oe_score($val)
    {
        if ($val === null || $val === '')
            return '';
        if (!is_numeric($val))
            return esc($val);
        $f = (float) $val;
        if (floor($f) == $f)
            return number_format($f, 0);
        $s = number_format($f, 2, '.', '');
        $s = rtrim($s, '0');
        $s = rtrim($s, '.');
        return $s;
    }
}

$selected_month = isset($selected_month) ? $selected_month : '';
$selected_year = isset($selected_year) ? $selected_year : '';

// (overall_oe_score is now computed in the backend)
// Compute totals from open-points summary
$total_open = 0;
$total_working = 0;
$total_review_cm = 0;
$total_review_auditor = 0;
$total_closed = 0;
if (isset($total_OE_score_openpoints) && is_array($total_OE_score_openpoints)) {
    foreach ($total_OE_score_openpoints as $row) {
        $total_open += (int) (isset($row['count_open_points']) ? $row['count_open_points'] : 0);
        $total_working += (int) (isset($row['count_working_points']) ? $row['count_working_points'] : 0);
        $total_review_cm += (int) (isset($row['count_under_review_cm']) ? $row['count_under_review_cm'] : 0);
        $total_review_auditor += (int) (isset($row['count_under_review_auditor']) ? $row['count_under_review_auditor'] : 0);
        $total_closed += (int) (isset($row['count_closed_points']) ? $row['count_closed_points'] : 0);
    }
}
$total_under_review = $total_review_cm + $total_review_auditor;

// Upcoming count
$oeUpcomingCount = !empty($OE_audit) ? count($OE_audit) : 0;

// Month summary count
$oeMonthCount = !empty($OE_year_score_query) ? count($OE_year_score_query) : 0;

// Open point report count
$oeReportCount = !empty($open_point_report) ? count($open_point_report) : 0;

// Aging count
$agingCategories = [];
$agingPivot = [];
if (!empty($aging_OE_score)) {
    foreach ($aging_OE_score as $ar) {
        $cat = trim($ar['category'] ?? '');
        if ($cat !== '' && !in_array($cat, $agingCategories)) {
            $agingCategories[] = $cat;
        }
    }
    sort($agingCategories);

    foreach ($aging_OE_score as $ar) {
        $loc = trim($ar['client_name'] ?? ($ar['location'] ?? ''));
        $auditNo = trim($ar['audit_no'] ?? '');
        $arRegion = trim($ar['region'] ?? '');
        $arCluster = trim($ar['cluster_name'] ?? ($ar['cluster'] ?? ''));
        $date = !empty($ar['audit_date']) ? date('d-M-Y', strtotime($ar['audit_date'])) : '';
        $cat = trim($ar['category'] ?? '');
        $pts = (int) ($ar['openpoints'] ?? 0);

        $agingDays = '';
        if (!empty($ar['audit_date'])) {
            $diff = time() - strtotime($ar['audit_date']);
            $agingDays = floor($diff / (60 * 60 * 24));
        }

        if (!isset($agingPivot[$loc])) {
            $agingPivot[$loc] = [
                'audit_no' => $auditNo,
                'region' => $arRegion,
                'cluster' => $arCluster,
                'audit_date' => $date,
                'aging_days' => $agingDays,
                'cats' => [],
                'total' => 0,
            ];
        }
        if ($cat !== '') {
            $agingPivot[$loc]['cats'][$cat] = ($agingPivot[$loc]['cats'][$cat] ?? 0) + $pts;
            $agingPivot[$loc]['total'] += $pts;
        }
    }
}
$oeAgingCount = count($agingPivot);
?>

<div class="oe-wrap">

    <!-- ===================== PAGE HEADER ===================== -->
    <div class="oe-page-header">
        <div>
            <h2><i class="fas fa-award text-primary fs-1"></i> Normal Audit Dashboard</h2>
            <p>Overview of Operational Excellence audits across locations and accounts</p>
        </div>

    </div>

    <!-- ===================== QUICK ACTIONS ===================== -->
    <div class="card oe-filter-card mb-4" id="oeQuickActionsCard">
        <!-- <div class="card-header bg-white border-bottom-0 pb-0 pt-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-bolt text-primary me-2"></i>Quick Actions</h6>
        </div> -->
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/Audit_template/template_type_filter/Normal') ?>"
                        class="qa-item border h-100 bg-light-primary"
                        style="border-radius: 12px; border-color: #e8eaf0 !important;">
                        <div class="qa-icon text-white" style="background:#0d6efd; border-radius: 50%;">
                            <i class="fas fa-file-alt text-white"></i>
                        </div>
                        <div class="flex-fill ms-2">
                            <div class="qa-title text-dark">New Normal Audit</div>
                            <div class="qa-sub">Create a new Normal audit</div>
                        </div>
                        <i class="fas fa-chevron-right text-dark" style="font-size:.85rem; font-weight: bold;"></i>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/Reaudit/index/NORMAL') ?>" class="qa-item border h-100  bg-light-warning"
                        style="border-radius: 12px; border-color: #e8eaf0 !important;">
                        <div class="qa-icon text-white" style="background:#f59e0b; border-radius: 50%;">
                            <i class="fas fa-sync-alt text-white"></i>
                        </div>
                        <div class="flex-fill ms-2">
                            <div class="qa-title text-dark">Normal Old Audit</div>
                            <div class="qa-sub">Initiate re-audit for Normal audits</div>
                        </div>
                        <i class="fas fa-chevron-right text-dark" style="font-size:.85rem; font-weight: bold;"></i>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/Audit_final_structure/normal_audit_structure') ?>"
                        class="qa-item border h-100  bg-light-success"
                        style="border-radius: 12px; border-color: #e8eaf0 !important;">
                        <div class="qa-icon text-white" style="background:#10b981; border-radius: 50%;">
                            <i class="far fa-calendar-check text-white"></i>
                        </div>
                        <div class="flex-fill ms-2">
                            <div class="qa-title text-dark">Normal Performed Audit</div>
                            <div class="qa-sub">Schedule and conduct Normal audit</div>
                        </div>
                        <i class="fas fa-chevron-right text-dark" style="font-size:.85rem; font-weight: bold;"></i>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/Normal_nc_tracker') ?>" class="qa-item border h-100  bg-light-danger"
                        style="border-radius: 12px; border-color: #e8eaf0 !important;">
                        <div class="qa-icon text-white" style="background:#f43f5e; border-radius: 50%;">
                            <i class="fas fa-list-ul text-white"></i>
                        </div>
                        <div class="flex-fill ms-2">
                            <div class="qa-title text-dark">Normal NC Tracker</div>
                            <div class="qa-sub">Track non-conformance and status</div>
                        </div>
                        <i class="fas fa-chevron-right text-dark" style="font-size:.85rem; font-weight: bold;"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== FILTER BAR ===================== -->
    <div class="card oe-filter-card mb-4" id="oeFilterCard">
        <div class="card-body py-3">
            <form method="GET" action="<?= base_url('Customer/Audit_dashboard/Normal_Audit'); ?>"
                class="row g-2 align-items-end" id="oeFilterForm">

                <!-- Year -->
                <div class="col-6 col-sm-4 col-md-2">
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size:1rem;">Year</label>
                    <select class="form-select form-select-sm oe-select2" id="filter_year" name="filter_year[]" multiple="multiple"
                        data-placeholder="All Years">
                        <?php
                        $currentYearVal = (int) date('Y');
                        $selYears = is_array($selected_year ?? null) ? $selected_year : (($selected_year ?? '') !== '' ? [$selected_year] : []);
                        for ($y = $currentYearVal + 1; $y >= 2020; $y--):
                            $isSel = in_array((string)$y, array_map('strval', $selYears));
                            ?>
                            <option value="<?= $y ?>" <?= $isSel ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Region -->
                <div class="col-6 col-sm-4 col-md-2">
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size:1rem;">Region</label>
                    <?php if (isset($is_cluster_manager) && $is_cluster_manager): ?>
                        <?php
                        $val = is_array($locked_region) ? implode(',', $locked_region) : (isset($locked_region) ? $locked_region : '');
                        $lbl = is_array($locked_region) ? implode(', ', $locked_region) : (isset($locked_region) ? $locked_region : '');
                        ?>
                        <input type="text" class="form-control form-control-sm" value="<?= esc($lbl) ?>" readonly>
                        <input type="hidden" name="region[]" value="<?= esc($val) ?>">
                    <?php elseif (isset($is_account_manager) && $is_account_manager): ?>
                        <select class="form-select form-select-sm oe-select2" id="region" name="region[]" multiple="multiple" data-placeholder="All Regions">
                            <?php
                            $amRegions = isset($locked_region) ? (is_array($locked_region) ? $locked_region : [$locked_region]) : [];
                            $selRegions = is_array($selected_region ?? null) ? $selected_region : (($selected_region ?? '') !== '' ? [$selected_region] : []);
                            foreach ($amRegions as $amRegion):
                                if (empty($amRegion)) continue;
                                $sel = in_array($amRegion, $selRegions) ? 'selected' : '';
                                ?>
                                <option value="<?= esc($amRegion) ?>" <?= $sel ?>><?= esc($amRegion) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select class="form-select form-select-sm oe-select2" id="region" name="region[]" multiple="multiple" data-placeholder="All Regions">
                            <?php
                            $regionList = isset($region) ? $region : [];
                            $selRegions = is_array($selected_region ?? null) ? $selected_region : (($selected_region ?? '') !== '' ? [$selected_region] : []);
                            foreach ($regionList as $row):
                                $sel = in_array($row['region_name'], $selRegions) ? 'selected' : '';
                                ?>
                                <option value="<?= esc($row['region_name']) ?>" <?= $sel ?>><?= esc($row['region_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Cluster -->
                <div class="col-6 col-sm-4 col-md-2">
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size:1rem;">Cluster</label>
                    <?php if (isset($is_cluster_manager) && $is_cluster_manager): ?>
                        <?php
                        $valC = is_array($locked_cluster) ? implode(',', $locked_cluster) : (isset($locked_cluster) ? $locked_cluster : '');
                        $lblC = is_array($locked_cluster) ? implode(', ', $locked_cluster) : (isset($locked_cluster) ? $locked_cluster : '');
                        ?>
                        <input type="text" class="form-control form-control-sm" value="<?= esc($lblC) ?>" readonly>
                        <input type="hidden" name="cluster_name[]" value="<?= esc($valC) ?>">
                    <?php elseif (isset($is_account_manager) && $is_account_manager): ?>
                        <select class="form-select form-select-sm oe-select2" id="cluster_name" name="cluster_name[]" multiple="multiple" data-placeholder="All Clusters">
                            <?php
                            $amClusters = isset($locked_cluster) ? (is_array($locked_cluster) ? $locked_cluster : [$locked_cluster]) : [];
                            $selClusters = is_array($selected_cluster ?? null) ? $selected_cluster : (($selected_cluster ?? '') !== '' ? [$selected_cluster] : []);
                            foreach ($amClusters as $amCluster):
                                if (empty($amCluster)) continue;
                                $sel = in_array($amCluster, $selClusters) ? 'selected' : '';
                                ?>
                                <option value="<?= esc($amCluster) ?>" <?= $sel ?>><?= esc($amCluster) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select class="form-select form-select-sm oe-select2" id="cluster_name" name="cluster_name[]" multiple="multiple" data-placeholder="All Clusters">
                            <?php
                            $clusterList = isset($cluster) ? $cluster : [];
                            $selClusters = is_array($selected_cluster ?? null) ? $selected_cluster : (($selected_cluster ?? '') !== '' ? [$selected_cluster] : []);
                            foreach ($clusterList as $row):
                                $sel = in_array($row['cluster_name'], $selClusters) ? 'selected' : '';
                                ?>
                                <option value="<?= esc($row['cluster_name']) ?>" <?= $sel ?>><?= esc($row['cluster_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- Account Name (Location) -->
                <div class="col-6 col-sm-4 col-md-2">
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size:1rem;">Account Name</label>
                    <?php
                    $selLocations = is_array($selected_location ?? null) ? $selected_location : (($selected_location ?? '') !== '' ? [$selected_location] : []);
                    ?>
                    <select class="form-select form-select-sm oe-select2" id="location_name" name="location_name[]" multiple="multiple" data-placeholder="All Accounts">
                        <?php
                        $locationList = isset($location) ? $location : [];
                        foreach ($locationList as $row):
                            $sel = in_array($row['location_name'], $selLocations) ? 'selected' : '';
                            ?>
                            <option value="<?= esc($row['location_name']) ?>" <?= $sel ?>><?= esc($row['location_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Audit Type (Category) -->
                <div class="col-6 col-sm-4 col-md-2">
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size:1rem;">Audit Type (Category)</label>
                    <?php
                    $selCategories = is_array($selected_category ?? null) ? $selected_category : (($selected_category ?? '') !== '' ? [$selected_category] : []);
                    ?>
                    <select class="form-select form-select-sm oe-select2" id="category" name="category[]" multiple="multiple" data-placeholder="Audit Type (Category)">
                        <?php
                        $categoryList = isset($audit_types) ? $audit_types : [];
                        foreach ($categoryList as $row):
                            if (empty(trim($row['audit_name']))) continue;
                            $sel = in_array($row['audit_name'], $selCategories) ? 'selected' : '';
                            ?>
                            <option value="<?= esc($row['audit_name']) ?>" <?= $sel ?>><?= esc($row['audit_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Month -->
                <div class="col-6 col-sm-4 col-md-2">
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size:1rem;">Month</label>
                    <select class="form-select form-select-sm oe-select2" id="month" name="month[]" multiple="multiple" data-placeholder="All Months">
                        <?php
                        $monthsList = isset($months) ? $months : [];
                        $selMonths = is_array($selected_month ?? null) ? $selected_month : (($selected_month ?? '') !== '' ? [$selected_month] : []);
                        foreach ($monthsList as $mKey => $mLabel):
                            $isSel = in_array($mKey, $selMonths) || in_array($mLabel, $selMonths) || in_array(strtolower($mLabel), array_map('strtolower', $selMonths));
                            ?>
                            <option value="<?= esc($mKey) ?>" <?= $isSel ? 'selected' : '' ?>><?= esc($mLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-6 col-sm-4 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill fw-semibold">
                        <i class="fa fa-filter me-1"></i> Show
                    </button>
                    <a href="<?= base_url('Customer/Audit_dashboard/Normal_Audit') ?>"
                        class="btn btn-outline-secondary btn-sm flex-fill fw-semibold">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- ===================== KPI CARDS (5 cards) ===================== -->
    <style>
        @media (min-width: 768px) {
            .col-md-kpi {
                flex: 0 0 20%;
                max-width: 20%;
            }
        }
        @media (min-width: 576px) and (max-width: 767px) {
            .col-md-kpi {
                flex: 0 0 33.333%;
                max-width: 33.333%;
            }
        }
    </style>
    <div class="row g-3 mb-4">

        <!-- 1. Normal Score -->
        <div class="col-6 col-md-kpi">
            <div class="card kpi-card kpi-purple">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-purple">
                        <i class="fas fa-award text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Normal Score</div>
                        <div class="kpi-value"><span id="kpi_score_val">--</span></div>
                        <div class="kpi-sub">Overall (All Locations)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Open Points -->
        <div class="col-6 col-md-kpi">
            <div class="card kpi-card kpi-blue">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-blue">
                        <i class="fas fa-file-invoice text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Open Points</div>
                        <div class="kpi-value"><span id="kpi_open_val">--</span></div>
                        <div class="kpi-sub">Across all audits</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Closed Points -->
        <div class="col-6 col-md-kpi">
            <div class="card kpi-card kpi-green">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-green">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Closed Points</div>
                        <div class="kpi-value"><span id="kpi_closed_val">--</span></div>
                        <div class="kpi-sub">During selected period</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Under Review -->
        <div class="col-6 col-md-kpi">
            <div class="card kpi-card kpi-orange">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-orange">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Under Review</div>
                        <div class="kpi-value"><span id="kpi_review_val">--</span></div>
                        <div class="kpi-sub">CM: <span id="kpi_review_cm_val">--</span> | Auditor: <span id="kpi_review_auditor_val">--</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Total Audits -->
        <div class="col-6 col-md-kpi">
            <div class="card kpi-card kpi-indigo">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-indigo">
                        <i class="fas fa-clipboard-list text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Total Audits</div>
                        <div class="kpi-value"><span id="kpi_total_val">--</span></div>
                        <div class="kpi-sub">Unique Normal audits (excl. re-audits)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== CHARTS ROW ===================== -->
    <div class="row g-3 mb-4">

        <!-- Normal Score by Region – Donut + Legend -->
        <div class="col-12 col-xl-5">
            <div class="card oe-card">
                <div class="card-header">
                    <div>
                        <div class="ch-title" id="oeDonutChartTitle">Audit Score Overview</div>
                        <div class="ch-sub">Average audit score</div>
                    </div>
                    <button class="btn btn-info btn-sm rounded-pill px-3"
                        onclick="ExportToCSV('Normal_Score_Chart.csv','hiddenOEScoreRegionTable')">
                        <i class="fas fa-file-csv me-1"></i> CSV
                    </button>
                </div>
                <div class="card-body d-flex flex-column align-items-center">
                    <div class="donut-wrap position-relative" style="width:220px; height:220px;">
                        <canvas id="oeScoreDonutChart"></canvas>
                        <div class="donut-center position-absolute top-50 start-50 translate-middle text-center">
                            <div class="dc-val fw-bold text-dark" style="font-size:24px;">0%</div>
                            <div class="dc-lbl text-muted" style="font-size:12px;">Avg Score</div>
                        </div>
                    </div>
                    <ul class="score-legend mt-4 mb-0">
                        <!-- JS injected legend -->
                    </ul>
                    <table id="hiddenOEScoreRegionTable" style="display:none;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Score (%)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Normal Open Points by Location – Vertical Bar (All Entries) -->
        <div class="col-12 col-xl-7">
            <div class="card oe-card">
                <div class="card-header">
                    <div>
                        <div class="ch-title">Normal Open Points by Account Name</div>
                        <div class="ch-sub">All locations with active open points</div>
                    </div>
                    <button class="btn btn-info btn-sm rounded-pill px-3"
                        onclick="ExportToCSV('Normal_Open_Points_by_Account.csv','hiddenOEBarChartTable')">
                        <i class="fas fa-file-csv me-1"></i> CSV
                    </button>
                </div>
                <div class="card-body p-2 p-sm-3">
                    <div style="overflow-x:auto; overflow-y:hidden;">
                        <div class="bar-chart-wrap" id="oeBarChartWrap" style="height:380px; min-width:480px;">
                            <canvas id="oeOpenPointsBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== OPEN POINTS SUMMARY ===================== -->
    <div class="card oe-card mb-4" style="height:auto;">
        <div class="card-header">
            <div>
                <div class="ch-title">Open Points Summary</div>
            </div>
            <!-- <button class="btn btn-success btn-sm rounded-pill px-3 me-2"
                onclick="ExportToExcel('xlsx','Normal_Open_Points.xlsx',false,'OE_openpoints_table')">
                <i class="fas fa-file-excel me-1"></i> Excel
            </button> -->
            <button class="btn btn-info btn-sm rounded-pill px-3"
                onclick="ExportToCSV('Normal_Open_Points.csv','OE_openpoints_table')">
                <i class="fas fa-file-csv me-1"></i> CSV
            </button>
        </div>
        <div class="card-body">

            <!-- Status pills -->
            <div class="stat-pills">
                <div class="stat-pill" style="background:#eff6ff;">
                    <span class="sp-val text-primary" id="pill_open">--</span>
                    <span class="sp-lbl text-primary">Total Open</span>
                </div>
                <div class="stat-pill" style="background:#fffbeb;">
                    <span class="sp-val text-warning" id="pill_working">--</span>
                    <span class="sp-lbl text-warning">Working</span>
                </div>
                <div class="stat-pill" style="background:#e0f2fe;">
                    <span class="sp-val text-info" id="pill_review">--</span>
                    <span class="sp-lbl text-info">Under Review</span>
                </div>
                <div class="stat-pill" style="background:#f0fdf4;">
                    <span class="sp-val text-success" id="pill_closed">--</span>
                    <span class="sp-lbl text-success">Closed</span>
                </div>
            </div>

            <div class="tbl-scroll-x">
                <table class="table table-bordered table-hover table-sm oe-table" id="OE_openpoints_table"
                    style="width:100%;">
                    <thead>
                        <tr class="table-light">
                            <th style="min-width:160px;">Account Name</th>
                            <th style="min-width:110px;">Cluster</th>
                            <th class="text-center" style="min-width:90px;">Open Points</th>
                            <th class="text-center" style="min-width:100px;">Working Points</th>
                            <th class="text-center" style="min-width:120px;">Under Review-CM</th>
                            <th class="text-center" style="min-width:140px;">Under Review-Auditor</th>
                            <th class="text-center" style="min-width:100px;">Closed Points</th>
                            <th class="text-center" style="min-width:70px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    
                </table>
            </div>
        </div>
    </div>

    <!-- ===================== TABBED REPORTS ===================== -->
    <div class="card oe-card mb-4" style="height:auto;">
        <div class="card-header align-items-start flex-column flex-sm-row gap-2">
            <ul class="nav sec-tabs flex-nowrap overflow-auto" id="oeMainTabs" role="tablist" style="max-width:100%;">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#normal-tab-month" type="button"
                        id="normal-tab-month-btn">
                        Month Summary
                        <span class="badge bg-primary ms-1" style="font-size:.65rem;">--</span>
                    </button>
                </li>
                <li class="nav-item" style="padding-left: 10px;">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#normal-tab-report" type="button"
                        id="normal-tab-report-btn">
                        Open Points Report
                        <span class="badge bg-warning text-dark ms-1"
                            style="font-size:.65rem;">--</span>
                    </button>
                </li>
                <li class="nav-item" style="padding-left: 10px;">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#normal-tab-aging" type="button"
                        id="normal-tab-aging-btn">
                        Account Wise Aging
                        <span class="badge bg-danger ms-1" style="font-size:.65rem;">--</span>
                    </button>
                </li>
            </ul>
            <div id="oeTabExportBtn" class="ms-sm-auto flex-shrink-0">
                <!-- <button class="btn btn-success btn-sm rounded-pill px-3 me-2"
                    onclick="ExportToExcel('xlsx','Normal_Month_Summary.xlsx',false,'Normal_Month_Summary')">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </button> -->
                <button class="btn btn-info btn-sm rounded-pill px-3"
                    onclick="ExportTabCSV()">
                    <i class="fas fa-file-csv me-1"></i> CSV
                </button>
            </div>
        </div>

        <div class="card-body p-2 p-md-3">
            <div class="tab-content">

                <!-- ============ TAB 1: Month Summary ============ -->
                <div class="tab-pane fade show active" id="normal-tab-month" role="tabpanel">
                    <div class="tbl-scroll-x">
                        <table class="table table-bordered table-hover table-sm oe-table w-100" id="Normal_Month_Summary"
                            style="width:100%;">
                        <thead>
                            <tr class="table-light">
                                <th style="min-width:130px;">Account Name</th>
                                <th style="min-width:90px;">Region</th>
                                <th style="min-width:110px;">Cluster</th>
                                
                                <th class="text-center" style="min-width:55px;">Jan</th>
                                <th class="text-center" style="min-width:55px;">Feb</th>
                                <th class="text-center" style="min-width:55px;">Mar</th>
                                <th class="text-center" style="min-width:55px;">Apr</th>
                                <th class="text-center" style="min-width:55px;">May</th>
                                <th class="text-center" style="min-width:55px;">Jun</th>
                                <th class="text-center" style="min-width:55px;">Jul</th>
                                <th class="text-center" style="min-width:55px;">Aug</th>
                                <th class="text-center" style="min-width:55px;">Sep</th>
                                <th class="text-center" style="min-width:55px;">Oct</th>
                                <th class="text-center" style="min-width:55px;">Nov</th>
                                <th class="text-center" style="min-width:55px;">Dec</th>

                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    </div>
                </div>

                <!-- ============ TAB 2: Open Points Report ============ -->
                <div class="tab-pane fade" id="normal-tab-report" role="tabpanel">
                    <div class="tbl-scroll-x">
                        <table class="table table-bordered table-hover table-sm oe-table w-100" id="OE_open_point_report" style="width:100%;">
                            <thead>
                                <tr class="table-light">
                                    <th>Account Name</th>
                                    <th>Region</th>
                                    <th>Status</th>
                                    <th>Audit Date</th>
                                    <th>Cluster</th>
                                    <th>Category</th>
                                    <th>Sub Category</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- ============ TAB 3: Account Wise Aging ============ -->
                <div class="tab-pane fade" id="normal-tab-aging" role="tabpanel">
                    
                    <div class="tbl-scroll-x" style="overflow-x:auto;">
                        <table class="table table-bordered table-hover table-sm oe-table" id="OE_aging_table"
                            style="width:100%; min-width:600px;">
                            <thead>
                                <!-- Row 1: group header -->
                                <tr style="background:#f0f4ff;">
                                    <th colspan="6" class="text-center fw-bold"
                                        style="background:#e8edff;border-right:2px solid #c5cfee;">Location Info</th>
                                    <th colspan="7" class="text-center fw-bold"
                                        style="background:#fff3cd;">NC Points</th>
                                    <th class="text-center fw-bold" style="background:#dcfce7;">Total</th>
                                </tr>
                                <!-- Row 2: column names -->
                                <tr class="table-light">
                                    <th
                                        style="min-width:170px; position: sticky; left: 0; background: #f8f9fa; z-index: 2;">
                                        Account Name</th>
                                    <th style="min-width:110px;">Audit No</th>
                                    <th style="min-width:80px;">Region</th>
                                    <th style="min-width:110px;">Cluster</th>
                                    <th style="min-width:100px;">Audit Date</th>
                                    <th style="min-width:90px;border-right:2px solid #c5cfee;">Aging (Days)</th>
                                    
                                    <th class="text-center" style="min-width:110px;">0-30</th>
                                    <th class="text-center" style="min-width:110px;">31-60</th>
                                    <th class="text-center" style="min-width:110px;">61-90</th>
                                    <th class="text-center" style="min-width:110px;">91-120</th>
                                    <th class="text-center" style="min-width:110px;">121-150</th>
                                    <th class="text-center" style="min-width:110px;">151-180</th>
                                    <th class="text-center" style="min-width:110px;">Above 180</th>

                                    <th class="text-center fw-bold" style="min-width:80px;">Total</th>
                                </tr>
                            </thead>
                            <tbody></tbody>

                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ===================== UPCOMING AUDITS + QUICK ACTIONS ===================== -->
    <div class="row g-3 mb-4">

        <!-- Upcoming Audits – full list with scroller -->
        <div class="col-12 col-lg-12">
            <div class="card oe-card">
                <div class="card-header">
                    <div>
                        <div class="ch-title">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Upcoming Audit
                        </div>
                        <div class="ch-sub">
                            <span id="upcoming_scheduled_text">-- scheduled audits</span>
                        </div>
                    </div>
                    <span class="badge rounded-pill"
                        style="background:#eef2ff;color:#4361ee;font-size:.75rem;padding:5px 10px;">
                        <span id="upcoming_total_badge">--</span> Total
                    </span>
                </div>

                <div class="card-body p-0">
                    <div id="oeUpcomingScroller" style="max-height:420px; overflow-y:auto; padding:.5rem .75rem;"></div><!-- /#oeUpcomingScroller -->
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <!-- <div class="col-12 col-lg-5">
            <div class="card oe-card">
                <div class="card-header">
                    <div class="ch-title">
                        <i class="fas fa-bolt text-warning me-1"></i> Quick Actions
                    </div>
                </div>
                <div class="card-body p-2 p-sm-3">
                    <div class="d-grid gap-4">

                        <a href="<?= base_url('Masters/Audit_template/template_type_filter/Normal') ?>" class="qa-item">
                            <div class="qa-icon" style="background:#dbeafe;">
                                <i class="fas fa-plus-circle text-primary"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="qa-title">New Audit</div>
                                <div class="qa-sub">Create a new Normal audit</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted" style="font-size:.75rem;"></i>
                        </a>

                        <a href="<?= base_url('Masters/Reaudit/index/NORMAL') ?>" class="qa-item">
                            <div class="qa-icon" style="background:#fef9c3;">
                                <i class="fas fa-sync-alt text-warning"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="qa-title">Normal Reaudit</div>
                                <div class="qa-sub">Initiate re-audit for Normal audits</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted" style="font-size:.75rem;"></i>
                        </a>

                        <a href="<?= base_url('Masters/Audit_final_structure') ?>" class="qa-item">
                            <div class="qa-icon" style="background:#dcfce7;">
                                <i class="fas fa-clipboard-check text-success"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="qa-title">Normal Audit</div>
                                <div class="qa-sub">Schedule and conduct Normal audit</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted" style="font-size:.75rem;"></i>
                        </a>

                        <a href="<?= base_url('Masters/Normal_nc_tracker') ?>" class="qa-item">
                            <div class="qa-icon" style="background:#fce7f3;">
                                <i class="fas fa-tasks text-danger"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="qa-title">Normal NC Tracker</div>
                                <div class="qa-sub">Track non-conformance and active status</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted" style="font-size:.75rem;"></i>
                        </a>

                    </div>
                </div>
            </div>
        </div> -->

    </div>

    <!-- Hidden tables for ExportToExcel -->
    <table id="hiddenOEScoreRegionTable" style="display:none;">
        <thead>
            <tr>
                <th><?= esc(isset($pie_chart_title) ? $pie_chart_title : 'Group') ?></th>
                <th>Normal Score (%)</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <table id="hiddenOEBarChartTable" style="display:none;">
        <thead>
            <tr>
                <th>Account Name</th>
                <th>Open Points</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

</div><!-- /.oe-wrap -->
<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script
    src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= base_url('js/select2.full.min.js') ?>"></script>

<script>
let charts = {};
let tables = {};

// Colors
const colGreen = '#00E676';
const colPurple = '#7C4DFF';
const colYellow = '#FFEA00';
const colRed = '#FF5252';
const colOrange = '#FF9100';

$(document).ready(function() {
    // Cascading Dropdowns
    $('#filter_year').change(function() { /* Year cascade logic here if needed */ });
    $('#region').change(function() {
        const reg = $(this).val();
        if(reg && reg.length > 0) {
            $.post('<?= base_url("Customer/Audit_dashboard/get_clusters_by_region") ?>', {region_name: reg}, function(res) {
                let html = '';
                if(res) {
                    let clusters = typeof res === 'string' ? JSON.parse(res) : res;
                    clusters.forEach(c => { html += `<option value="${c.cluster_name}">${c.cluster_name}</option>`; });
                }
                $('#cluster_name').html(html).trigger('change');
            });
        }
    });

    $('#cluster_name').change(function() {
        const clu = $(this).val();
        const reg = $('#region').val();
        if(clu && clu.length > 0) {
            $.post('<?= base_url("Customer/Audit_dashboard/get_locations_by_cluster") ?>', {cluster_name: clu, region_name: reg}, function(res) {
                let html = '';
                if(res) {
                    let locs = typeof res === 'string' ? JSON.parse(res) : res;
                    locs.forEach(l => { html += `<option value="${l.location_name}">${l.location_name}</option>`; });
                }
                $('#location_name').html(html).trigger('change');
            });
        }
    });

    // Init fetch
    fetchDashboardData();

    $('#oeFilterForm').submit(function(e) {
        e.preventDefault();
        fetchDashboardData();
    });
});

function getFilters() {
    return {
        year: $('#filter_year').val() || 'All',
        region: $('#region').val() || 'All',
        cluster: $('#cluster_name').val() || 'All',
        location: $('#location_name').val() || 'All',
        audit_type: $('#category').val() || 'All',
        month: $('#month').val() || 'All'
    };
}

function fetchDashboardData() {
    $('#loader-overlay').css('display', 'flex');
    const filters = getFilters();

    $.post('<?= base_url("Customer/Audit_dashboard/Normal_filter_ajax") ?>', filters, function(res) {
        $('#loader-overlay').hide();
        if(res && res.status === 1) {
            updateKPIs(res.kpis);
            updateCharts(res.charts, res.kpis, res.tables);
            updateTablesAndLists(res.tables);
        }
    }).fail(function() {
        $('#loader-overlay').hide();
        alert('Failed to load dashboard data.');
    });
}

function updateKPIs(data) {
    $('#kpi_total_val').text(data.total_audits || 0);
    $('#kpi_open_val').text(data.open_points || 0);
    $('#kpi_closed_val').text(data.closed_points || 0);
    $('#kpi_review_val').text((data.review_cm_points || 0) + (data.review_auditor_points || 0));
    $('#kpi_review_cm_val').text(data.review_cm_points || 0);
    $('#kpi_review_auditor_val').text(data.review_auditor_points || 0);
    $('#kpi_score_val').text((data.avg_score || 0) + '%');

    $('#pill_open').text(data.open_points || 0);
    $('#pill_working').text(data.working_points || 0);
    $('#pill_review').text((data.review_cm_points || 0) + (data.review_auditor_points || 0));
    $('#pill_closed').text(data.closed_points || 0);
}

function updateCharts(chartsData, kpis, tablesData) {
    Chart.defaults.font.family = "'Poppins', sans-serif";
    
    // 1. Score Overview (Donut)
    let scoreData = chartsData.score_region || [];
    let scoreColors = [
        '#00a8ff', '#9c88ff', '#fbc531', '#4cd137', '#487eb0',
        '#e84118', '#7f8fa6', '#273c75', '#c23616', '#0097e6',
        '#8c7ae6', '#e1b12c', '#44bd32', '#40739e', '#e15f41'
    ];
    
    $('#oeDonutChartTitle').text(chartsData.pie_chart_title || 'Audit Score Overview');
    
    let donutEl = document.getElementById('oeScoreDonutChart');
    if (donutEl) {
        if(charts.score_overview) charts.score_overview.destroy();
        charts.score_overview = new Chart(donutEl, {
            type: 'doughnut',
            data: {
                labels: scoreData.map(d=>d.label),
                datasets: [{ 
                    data: scoreData.map(d=>d.score), 
                    backgroundColor: scoreData.map((d,i) => scoreColors[i % scoreColors.length]), 
                    borderWidth: 0 
                }]
            },
            options: { plugins: { legend: { display: false } }, cutout: '75%', responsive: true, maintainAspectRatio: false }
        });
        $('.donut-center .dc-val').text((kpis.avg_score || 0) + '%');
        
        let legendHtml = '';
        let tableHtml = '';
        scoreData.forEach((item, i) => {
            let color = scoreColors[i % scoreColors.length];
            legendHtml += `<li><span class="dot" style="background:${color};"></span><span class="lname">${item.label}</span><span class="lscore" style="color:${color}">${item.score}%</span></li>`;
            tableHtml += `<tr><td>${item.label}</td><td>${item.score}%</td></tr>`;
        });
        $('.score-legend').html(legendHtml);
        $('#hiddenOEScoreRegionTable tbody').html(tableHtml);
    }

    // 2. Open Points by Account (Bar)
    let accPoints = {};
    (tablesData.open_points || []).forEach(p => {
        if (String(p.status) === '0') {
            let acc = p.account_name;
            if(acc) { accPoints[acc] = (accPoints[acc] || 0) + 1; }
        }
    });
    
    let barRows = [];
    for(let acc in accPoints) { barRows.push({l: acc, v: accPoints[acc]}); }
    barRows.sort((a,b) => b.v - a.v);

    var rainbowPalette = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#14b8a6', '#3b82f6', '#6366f1'];
    var oeBarEl = document.getElementById('oeOpenPointsBarChart');
    if (oeBarEl && barRows.length > 0) {
        if(charts.bar) charts.bar.destroy();
        
        var wrap = document.getElementById('oeBarChartWrap');
        var bw = Math.max(480, barRows.length * 72);
        if (wrap) {
            wrap.style.height = '380px';
            wrap.style.minWidth = bw + 'px';
            wrap.style.overflowX = 'auto';
        }
        oeBarEl.style.height = '380px';
        oeBarEl.style.minWidth = bw + 'px';
        
        let hiddenBarHtml = '';
        barRows.forEach(r => {
            hiddenBarHtml += `<tr><td>${r.l}</td><td>${r.v}</td></tr>`;
        });
        $('#hiddenOEBarChartTable tbody').html(hiddenBarHtml);

        charts.bar = new Chart(oeBarEl, {
            type: 'bar',
            data: {
                labels: barRows.map(r => r.l),
                datasets: [{
                    label: 'Open Points',
                    data: barRows.map(r => r.v),
                    backgroundColor: barRows.map((_, i) => rainbowPalette[i % rainbowPalette.length]),
                    borderRadius: 6,
                    barThickness: 25,
                    maxBarThickness: 30,
                    categoryPercentage: 0.6,
                    barPercentage: 0.8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { beginAtZero: true } }
            }
        });
    }

}

function updateTablesAndLists(data) {
    // Update Tab Badges
    $('#normal-tab-month-btn .badge').text((data.monthly || []).length);
    $('#normal-tab-aging-btn .badge').text((data.aging || []).length);

    // 1. Open Points Summary
    if ($.fn.DataTable.isDataTable('#OE_openpoints_table')) { $('#OE_openpoints_table').DataTable().destroy(); }
    let opHtml = '';
    
    // Group open points by account_name
    let opGrouped = {};
    (data.open_points || []).forEach(p => {
        let acc = p.account_name || 'Unknown';
        if (!opGrouped[acc]) {
            opGrouped[acc] = { 
                account_name: acc, 
                cluster: p.cluster || '', 
                open: 0, 
                working: 0, 
                review_cm: 0, 
                review_auditor: 0, 
                closed: 0 
            };
        }
        
        let st = String(p.status);
        if (st === '0') opGrouped[acc].open++;
        else if (st === '1' || st === '4') opGrouped[acc].working++;
        else if (st === '6') opGrouped[acc].review_cm++;
        else if (st === '2') opGrouped[acc].review_auditor++;
        else if (st === '3' || st === '5') opGrouped[acc].closed++;
    });

    Object.values(opGrouped).forEach(p => {
        // Hide if all open/working/review points are 0
        if (p.open === 0 && p.working === 0 && p.review_cm === 0 && p.review_auditor === 0) return;

        opHtml += `<tr>
            <td class="text-primary fw-semibold">${p.account_name}</td>
            <td>${p.cluster}</td>
            <td class="text-center text-danger fw-bold">${p.open}</td>
            <td class="text-center text-warning fw-bold">${p.working}</td>
            <td class="text-center text-info fw-bold">${p.review_cm}</td>
            <td class="text-center text-primary fw-bold">${p.review_auditor}</td>
            <td class="text-center text-success fw-bold">${p.closed}</td>
            <td class="text-center"><a target="_blank" href="<?= base_url('Masters/Normal_nc_tracker') ?>?location=${encodeURIComponent(p.account_name)}" class="btn btn-sm btn-light text-primary">View</a></td>
        </tr>`;
    });
    $('#OE_openpoints_table tbody').html(opHtml);
    $('#OE_openpoints_table').DataTable({ responsive: true, pageLength: 10, dom: 'ftip' });

    // 2. Month Summary
    if ($.fn.DataTable.isDataTable('#Normal_Month_Summary')) { $('#Normal_Month_Summary').DataTable().destroy(); }
    let moHtml = '';
    (data.monthly || []).forEach(m => {
        moHtml += `<tr>
            <td class="fw-semibold">${m.account_name || ''}</td>
            <td>${m.region || ''}</td>
            <td>${m.cluster || ''}</td>
            <td class="text-center">${m.m1 || '—'}</td>
            <td class="text-center">${m.m2 || '—'}</td>
            <td class="text-center">${m.m3 || '—'}</td>
            <td class="text-center">${m.m4 || '—'}</td>
            <td class="text-center">${m.m5 || '—'}</td>
            <td class="text-center">${m.m6 || '—'}</td>
            <td class="text-center">${m.m7 || '—'}</td>
            <td class="text-center">${m.m8 || '—'}</td>
            <td class="text-center">${m.m9 || '—'}</td>
            <td class="text-center">${m.m10 || '—'}</td>
            <td class="text-center">${m.m11 || '—'}</td>
            <td class="text-center">${m.m12 || '—'}</td>
        </tr>`;
    });
    $('#Normal_Month_Summary tbody').html(moHtml);
    $('#Normal_Month_Summary').DataTable({ responsive: true, pageLength: 10, dom: 'ftip' });

    // 3. Open Points Report
    if ($.fn.DataTable.isDataTable('#OE_open_point_report')) { $('#OE_open_point_report').DataTable().destroy(); }
    let repHtml = '';
    let repCount = 0;
    (data.open_points || []).forEach(p => {
        let st = String(p.status);
        let statusBadge = '';
        if (st === '0') statusBadge = '<span class="badge bg-danger">Open</span>';
        else if (st === '1' || st === '4') statusBadge = '<span class="badge bg-warning text-dark">Working</span>';
        else if (st === '2') statusBadge = '<span class="badge" style="background:#6f42c1;">Review (Auditor)</span>';
        else if (st === '6') statusBadge = '<span class="badge bg-info">Review (CM)</span>';
        
        // Only show open points
        if (st === '0') {
            repCount++;
            repHtml += `<tr>
                <td class="fw-semibold text-primary">${p.account_name || ''}</td>
                <td>${p.region || ''}</td>
                <td>${statusBadge}</td>
                <td>${p.audit_date || ''}</td>
                <td>${p.cluster || ''}</td>
                <td>${p.category || ''}</td>
                <td>${p.sub_category || ''}</td>
                <td>${p.description || ''}</td>
            </tr>`;
        }
    });
    $('#OE_open_point_report tbody').html(repHtml);
    $('#OE_open_point_report').DataTable({ responsive: true, pageLength: 10, dom: 'ftip' });
    
    // Update Open Points Report badge based on filtered points
    $('#normal-tab-report-btn .badge').text(repCount);

    // 4. Aging
    if ($.fn.DataTable.isDataTable('#OE_aging_table')) { $('#OE_aging_table').DataTable().destroy(); }
    let agHtml = '';
    (data.aging || []).forEach(a => {
        agHtml += `<tr>
            <td class="fw-semibold text-primary">${a.account_name || ''}</td>
            <td>${a.audit_no || ''}</td>
            <td>${a.region || ''}</td>
            <td>${a.cluster || ''}</td>
            <td>${a.audit_date || ''}</td>
            <td>${a.aging_days !== undefined && a.aging_days !== null ? a.aging_days : ''}</td>
            <td class="text-center text-danger fw-bold">${a['0-30'] || '0'}</td>
            <td class="text-center text-warning fw-bold">${a['31-60'] || '0'}</td>
            <td class="text-center text-info fw-bold">${a['61-90'] || '0'}</td>
            <td class="text-center text-primary fw-bold">${a['91-120'] || '0'}</td>
            <td class="text-center text-secondary fw-bold">${a['121-150'] || '0'}</td>
            <td class="text-center text-dark fw-bold">${a['151-180'] || '0'}</td>
            <td class="text-center fw-bold text-danger">${a['Above 180'] || '0'}</td>
            <td class="text-center fw-bold text-success">${a.total || '0'}</td>
        </tr>`;
    });
    $('#OE_aging_table tbody').html(agHtml);
    $('#OE_aging_table').DataTable({ responsive: true, pageLength: 10, dom: 'ftip' });

    // 5. Upcoming
    let upcomingData = data.upcoming || [];
    $('#upcoming_total_badge').text(upcomingData.length);
    $('#upcoming_scheduled_text').text(upcomingData.length + ' scheduled audits');
    let upHtml = '';
    if(upcomingData.length === 0) {
        upHtml = '<div class="no-data my-3">No upcoming audits scheduled</div>';
    } else {
        upcomingData.forEach(u => {
            let mDate = new Date(u.audit_date);
            let d2 = new Date();
            let dLeft = Math.ceil((mDate - d2) / (1000 * 60 * 60 * 24));
            let dayNum = mDate.getDate();
            let monStr = mDate.toLocaleString('default', { month: 'short' }).toUpperCase();
            
            upHtml += `
            <div class="upcoming-item">
                <div class="udate-badge" style="background:#0d6efd;color:#fff;">
                    <div class="ud-day">${dayNum}</div>
                    <div class="ud-mon">${monStr}</div>
                </div>
                <div class="flex-fill" style="min-width:0;">
                    <div class="u-title">${(u.account_name || '').toUpperCase()}</div>
                    <div class="u-meta">${u.audit_no || ''} | ${u.region || ''} | ${u.cluster || ''} | <i class="fas fa-user-tie"></i> ${u.auditor || 'N/A'}</div>
                </div>
                <div class="text-end flex-shrink-0">
                    <span class="u-badge bg-primary text-white">${dLeft > 0 ? dLeft + ' Days Left' : 'Today'}</span>
                </div>
            </div>`;
        });
    }
    $('#oeUpcomingScroller').html(upHtml);
}

function ExportToCSV(filename, tableId) {
    let originalTable = document.getElementById(tableId);
    if (!originalTable) return;
    
    let table = originalTable.cloneNode(true);
    let ths = table.querySelectorAll('thead th');
    if (ths.length > 0) {
        let lastTh = ths[ths.length - 1];
        if (lastTh.innerText.trim().toLowerCase() === 'action') {
            lastTh.parentNode.removeChild(lastTh);
            let rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                let tds = row.querySelectorAll('td');
                if (tds.length > 0) row.removeChild(tds[tds.length - 1]);
            });
        }
    }

    var ws = XLSX.utils.table_to_sheet(table, { raw: true });
    Object.keys(ws).forEach(function (addr) {
        if (addr[0] === '!') return;
        var cell = ws[addr];
        if (cell && typeof cell.w === 'string' && cell.w.indexOf('%') !== -1) {
            cell.t = 's';
            cell.v = cell.w; // force the string representation back as value
        }
    });
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Sheet JS");
    let ext = filename.toLowerCase().endsWith('.csv') ? '' : '.csv';
    XLSX.writeFile(wb, filename + ext);
}

function ExportTabCSV() {
    let activeTabId = $('#oeMainTabs .nav-link.active').attr('id');
    if (activeTabId === 'normal-tab-month-btn') {
        ExportToCSV('Normal_Month_Summary.csv', 'Normal_Month_Summary');
    } else if (activeTabId === 'normal-tab-report-btn') {
        ExportToCSV('Normal_Open_Point_Report.csv', 'OE_open_point_report');
    } else if (activeTabId === 'normal-tab-aging-btn') {
        ExportToCSV('Normal_Category_Wise_Aging.csv', 'OE_aging_table');
    }
}
</script>
<?php $this->endSection(); ?>