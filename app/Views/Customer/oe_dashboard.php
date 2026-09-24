<?php
$this->extend("Layout/base_admin");
?>

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

    /* Category Wise Aging — category column headers must stay horizontal */
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
                'account_manager' => $ar['account_manager'] ?? '-',
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
            <h2><i class="fas fa-award text-primary fs-1"></i> OE Audit Dashboard</h2>
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
                    <a href="<?= base_url('Masters/Audit_template/template_type_filter/OE') ?>"
                        class="qa-item border h-100 bg-light-primary"
                        style="border-radius: 12px; border-color: #e8eaf0 !important;">
                        <div class="qa-icon text-white" style="background:#0d6efd; border-radius: 50%;">
                            <i class="fas fa-file-alt text-white"></i>
                        </div>
                        <div class="flex-fill ms-2">
                            <div class="qa-title text-dark">New OE Audit</div>
                            <div class="qa-sub">Create a new OE audit</div>
                        </div>
                        <i class="fas fa-chevron-right text-dark" style="font-size:.85rem; font-weight: bold;"></i>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/Reaudit/index/OE') ?>" class="qa-item border h-100  bg-light-warning"
                        style="border-radius: 12px; border-color: #e8eaf0 !important;">
                        <div class="qa-icon text-white" style="background:#f59e0b; border-radius: 50%;">
                            <i class="fas fa-sync-alt text-white"></i>
                        </div>
                        <div class="flex-fill ms-2">
                            <div class="qa-title text-dark">OE Old Audit</div>
                            <div class="qa-sub">Initiate re-audit for OE audits</div>
                        </div>
                        <i class="fas fa-chevron-right text-dark" style="font-size:.85rem; font-weight: bold;"></i>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/Audit_final_structure') ?>"
                        class="qa-item border h-100  bg-light-success"
                        style="border-radius: 12px; border-color: #e8eaf0 !important;">
                        <div class="qa-icon text-white" style="background:#10b981; border-radius: 50%;">
                            <i class="far fa-calendar-check text-white"></i>
                        </div>
                        <div class="flex-fill ms-2">
                            <div class="qa-title text-dark">OE Performed Audit</div>
                            <div class="qa-sub">Schedule and conduct OE audit</div>
                        </div>
                        <i class="fas fa-chevron-right text-dark" style="font-size:.85rem; font-weight: bold;"></i>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/Oe_nc_tracker') ?>" class="qa-item border h-100  bg-light-danger"
                        style="border-radius: 12px; border-color: #e8eaf0 !important;">
                        <div class="qa-icon text-white" style="background:#f43f5e; border-radius: 50%;">
                            <i class="fas fa-list-ul text-white"></i>
                        </div>
                        <div class="flex-fill ms-2">
                            <div class="qa-title text-dark">OE NC Tracker</div>
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
            <form method="GET" action="<?= base_url('Customer/Audit_dashboard/OE_Audit'); ?>"
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
                        $categoryList = isset($audit_categories) ? $audit_categories : [];
                        foreach ($categoryList as $row):
                            if (empty(trim($row['category']))) continue;
                            $sel = in_array($row['category'], $selCategories) ? 'selected' : '';
                            ?>
                            <option value="<?= esc($row['category']) ?>" <?= $sel ?>><?= esc($row['category']) ?></option>
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
                    <a href="<?= base_url('Customer/Audit_dashboard/OE_Audit') ?>"
                        class="btn btn-outline-secondary btn-sm flex-fill fw-semibold">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>

            </form>
        </div>
    </div>

    <?php if (!empty($selected_site_managers)): ?>
        <!-- Selected Site Managers Info Card -->
        <div class="alert alert-primary bg-light-primary border-primary d-flex align-items-center mb-4 p-3 rounded-3 shadow-sm">
            <i class="fas fa-building text-primary fs-3 me-3"></i>
            <div class="flex-grow-1">
                <h6 class="mb-1 fw-bold text-dark"><i class="fas fa-map-marker-alt text-danger me-1"></i> Selected Site Info</h6>
                <div class="d-flex flex-wrap gap-3 text-dark" style="font-size: 0.95rem;">
                    <?php foreach ($selected_site_managers as $ssm): ?>
                        <div class="p-2 border rounded bg-white shadow-sm">
                            <span class="fw-bold text-primary"><?= esc($ssm['client_name']) ?></span>:
                            <span class="badge bg-primary ms-1" style="font-size: 0.8rem;"><i class="fas fa-user-tie me-1"></i> Account Manager: <?= esc($ssm['account_manager'] ?: '-') ?></span>
                            <span class="badge bg-info text-dark ms-1" style="font-size: 0.8rem;"><i class="fas fa-user-shield me-1"></i> Cluster Manager: <?= esc($ssm['cluster_manager'] ?: '-') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

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

        <!-- 1. OE Score -->
        <div class="col-6 col-md-kpi">
            <div class="card kpi-card kpi-purple">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-purple">
                        <i class="fas fa-award text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">OE Score</div>
                        <div class="kpi-value"><?= ($overall_oe_score !== null) ? $overall_oe_score . '%' : 'N/A' ?>
                        </div>
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
                        <div class="kpi-value"><?= $total_open ?></div>
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
                        <div class="kpi-value"><?= $total_closed ?></div>
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
                        <div class="kpi-value"><?= $total_under_review ?></div>
                        <div class="kpi-sub">CM: <?= $total_review_cm ?> | Auditor: <?= $total_review_auditor ?></div>
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
                        <div class="kpi-value"><?= isset($total_oe_audits_count) ? $total_oe_audits_count : 0 ?></div>
                        <div class="kpi-sub">Unique OE audits (excl. re-audits)</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ===================== CHARTS ROW ===================== -->
    <div class="row g-3 mb-4">

        <!-- OE Score by Region – Donut + Legend -->
        <div class="col-12 col-xl-5">
            <div class="card oe-card">
                <div class="card-header">
                    <div>
                        <div class="ch-title" id="oeDonutChartTitle"><?= esc(isset($pie_chart_title) ? $pie_chart_title : 'OE Score by Region (Top 10)') ?></div>
                        <div class="ch-sub">Average audit score — top 10 groups</div>
                    </div>
                    <!-- <button class="btn btn-success btn-sm rounded-pill px-3 me-2"
                        onclick="ExportToExcel('xlsx','OE_Score_Chart.xlsx',false,'hiddenOEScoreRegionTable')">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button> -->
                    <button class="btn btn-info btn-sm rounded-pill px-3"
                        onclick="ExportToCSV('OE_Score_Chart.csv','hiddenOEScoreRegionTable')">
                        <i class="fas fa-file-csv me-1"></i> CSV
                    </button>
                </div>
                <div class="card-body">
                    <?php if (!empty($pie_chart)): ?>
                        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-center">
                            <div class="score-donut-wrap">
                                <canvas id="oeScoreDonutChart"></canvas>
                                <div class="donut-center">
                                    <div class="dc-val">
                                        <?= ($overall_oe_score !== null) ? $overall_oe_score . '%' : 'N/A' ?>
                                    </div>
                                    <div class="dc-lbl">Overall</div>
                                </div>
                            </div>
                            <div style="flex:1; min-width:150px; max-width:340px;">
                                <ul class="score-legend">
                                    <?php
                                    $oeDotPalette = [
                                        '#4361ee',
                                        '#f72585',
                                        '#7209b7',
                                        '#3a0ca3',
                                        '#4cc9f0',
                                        '#4895ef',
                                        '#560bad',
                                        '#b5179e',
                                        '#f3722c',
                                        '#43aa8b'
                                    ];
                                    $oeTopRegions = array_slice($pie_chart, 0, 10);
                                    foreach ($oeTopRegions as $si => $sl):
                                        $dc = $oeDotPalette[$si % count($oeDotPalette)];
                                        $slLabel = $sl['label'] ?? ($sl['region'] ?? '');
                                        ?>
                                        <li>
                                            <span class="dot" style="background:<?= $dc ?>;"></span>
                                            <span class="lname"
                                                title="<?= esc($slLabel) ?>"><?= esc($slLabel) ?></span>
                                            <span class="lscore" style="color:<?= $dc ?>">
                                                <?= number_format((float) $sl['score'], 2) ?>%
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-data">No Region Score Data Found</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- OE Open Points by Location – Vertical Bar (All Entries) -->
        <div class="col-12 col-xl-7">
            <div class="card oe-card">
                <div class="card-header">
                    <div>
                        <div class="ch-title">OE Open Points by Account Name</div>
                        <div class="ch-sub">All locations with active open points</div>
                    </div>
                    <!-- <button class="btn btn-success btn-sm rounded-pill px-3 me-2"
                        onclick="ExportToExcel('xlsx','OE_Open_Points_by_Account.xlsx',false,'hiddenOEBarChartTable')">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button> -->
                    <button class="btn btn-info btn-sm rounded-pill px-3"
                        onclick="ExportToCSV('OE_Open_Points_by_Account.csv','hiddenOEBarChartTable')">
                        <i class="fas fa-file-csv me-1"></i> CSV
                    </button>
                </div>
                <div class="card-body p-2 p-sm-3">
                    <?php if (!empty($total_OE_score_openpoints)): ?>
                        <div style="overflow-x:auto; overflow-y:hidden;">
                            <div class="bar-chart-wrap" id="oeBarChartWrap" style="height:380px; min-width:480px;">
                                <canvas id="oeOpenPointsBarChart"></canvas>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-data">No Open Points Data Found</div>
                    <?php endif; ?>
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
                onclick="ExportToExcel('xlsx','OE_Open_Points.xlsx',false,'OE_openpoints_table')">
                <i class="fas fa-file-excel me-1"></i> Excel
            </button> -->
            <button class="btn btn-info btn-sm rounded-pill px-3"
                onclick="ExportToCSV('OE_Open_Points.csv','OE_openpoints_table')">
                <i class="fas fa-file-csv me-1"></i> CSV
            </button>
        </div>
        <div class="card-body">

            <!-- Status pills -->
            <div class="stat-pills">
                <div class="stat-pill" style="background:#eff6ff;">
                    <span class="sp-val text-primary"><?= $total_open ?></span>
                    <span class="sp-lbl text-primary">Total Open</span>
                </div>
                <div class="stat-pill" style="background:#fffbeb;">
                    <span class="sp-val text-warning"><?= $total_working ?></span>
                    <span class="sp-lbl text-warning">Working</span>
                </div>
                <div class="stat-pill" style="background:#e0f2fe;">
                    <span class="sp-val text-info"><?= $total_under_review ?></span>
                    <span class="sp-lbl text-info">Under Review</span>
                </div>
                <div class="stat-pill" style="background:#f0fdf4;">
                    <span class="sp-val text-success"><?= $total_closed ?></span>
                    <span class="sp-lbl text-success">Closed</span>
                </div>
            </div>

            <div class="tbl-scroll-x">
                <table class="table table-bordered table-hover table-sm oe-table" id="OE_openpoints_table"
                    style="width:100%;">
                    <thead>
                        <tr class="table-light">
                            <th style="min-width:160px;">Account Name</th>
                            <th style="min-width:110px;">Cluster Manager</th>
                            <th style="min-width:130px;">Account Manager</th>
                            <th class="text-center" style="min-width:90px;">Open Points</th>
                            <th class="text-center" style="min-width:100px;">Working Points</th>
                            <th class="text-center" style="min-width:120px;">Under Review-CM</th>
                            <th class="text-center" style="min-width:140px;">Under Review-Auditor</th>
                            <th class="text-center" style="min-width:100px;">Closed Points</th>
                            <th class="text-center" style="min-width:70px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($total_OE_score_openpoints)):
                            foreach ($total_OE_score_openpoints as $row):
                                $loc = isset($row['location']) ? $row['location'] : '';
                                $reg = isset($row['region']) ? $row['region'] : '';
                                $clu = isset($row['cluster_name']) ? $row['cluster_name'] : '';
                                $am = isset($row['account_manager']) ? $row['account_manager'] : '-';
                                ?>
                                <tr class="clickable-row oe-drilldown-row"
                                    data-loc="<?= esc($loc) ?>" 
                                    data-reg="<?= esc($reg) ?>" 
                                    data-clu="<?= esc($clu) ?>">
                                    <td class="text-primary fw-semibold">
                                        <?= esc(isset($row['client_name']) ? $row['client_name'] : $loc) ?>
                                    </td>
                                    <td><?= esc($clu) ?></td>
                                    <td><?= esc($am) ?></td>
                                    <td class="text-center text-danger fw-bold">
                                        <?= (int) (isset($row['count_open_points']) ? $row['count_open_points'] : 0) ?>
                                    </td>
                                    <td class="text-center text-warning fw-bold">
                                        <?= (int) (isset($row['count_working_points']) ? $row['count_working_points'] : 0) ?>
                                    </td>
                                    <td class="text-center text-info fw-bold">
                                        <?= (int) (isset($row['count_under_review_cm']) ? $row['count_under_review_cm'] : 0) ?>
                                    </td>
                                    <td class="text-center text-primary fw-bold">
                                        <?= (int) (isset($row['count_under_review_auditor']) ? $row['count_under_review_auditor'] : 0) ?>
                                    </td>
                                    <td class="text-center text-success fw-bold">
                                        <?= (int) (isset($row['count_closed_points']) ? $row['count_closed_points'] : 0) ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary py-0 px-2 oe-drilldown-btn" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No open points data found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($total_OE_score_openpoints)):
                        $tfO = 0;
                        $tfW = 0;
                        $tfCM = 0;
                        $tfAud = 0;
                        $tfCl = 0;
                        foreach ($total_OE_score_openpoints as $row) {
                            $tfO += (int) (isset($row['count_open_points']) ? $row['count_open_points'] : 0);
                            $tfW += (int) (isset($row['count_working_points']) ? $row['count_working_points'] : 0);
                            $tfCM += (int) (isset($row['count_under_review_cm']) ? $row['count_under_review_cm'] : 0);
                            $tfAud += (int) (isset($row['count_under_review_auditor']) ? $row['count_under_review_auditor'] : 0);
                            $tfCl += (int) (isset($row['count_closed_points']) ? $row['count_closed_points'] : 0);
                        }
                        ?>
                        <!-- <tfoot>
                    <tr class="table-dark fw-bold">
                        <td colspan="3">Total</td>
                        <td class="text-center"><?= $tfO ?></td>
                        <td class="text-center"><?= $tfW ?></td>
                        <td class="text-center"><?= $tfCM ?></td>
                        <td class="text-center"><?= $tfAud ?></td>
                        <td class="text-center"><?= $tfCl ?></td>
                        <td></td>
                    </tr>
                </tfoot> -->
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================== TABBED REPORTS ===================== -->
    <div class="card oe-card mb-4" style="height:auto;">
        <div class="card-header align-items-start flex-column flex-sm-row gap-2">
            <ul class="nav sec-tabs flex-nowrap overflow-auto" id="oeMainTabs" role="tablist" style="max-width:100%;">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#oe-tab-month" type="button"
                        id="oe-tab-month-btn">
                        Month Summary
                        <span class="badge bg-primary ms-1" style="font-size:.65rem;"><?= $oeMonthCount ?></span>
                    </button>
                </li>
                <li class="nav-item" style="padding-left: 10px;">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#oe-tab-report" type="button"
                        id="oe-tab-report-btn">
                        Open Points Report
                        <span class="badge bg-warning text-dark ms-1"
                            style="font-size:.65rem;"><?= $oeReportCount ?></span>
                    </button>
                </li>
                <li class="nav-item" style="padding-left: 10px;">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#oe-tab-aging" type="button"
                        id="oe-tab-aging-btn">
                        Category Wise Aging
                        <span class="badge bg-danger ms-1" style="font-size:.65rem;"><?= $oeAgingCount ?></span>
                    </button>
                </li>
            </ul>
            <div id="oeTabExportBtn" class="ms-sm-auto flex-shrink-0">
                <!-- <button class="btn btn-success btn-sm rounded-pill px-3 me-2"
                    onclick="ExportToExcel('xlsx','OE_Month_Summary.xlsx',false,'OE_Month_Summary')">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </button> -->
                <button class="btn btn-info btn-sm rounded-pill px-3"
                    onclick="ExportToCSV('OE_Month_Summary.csv','OE_Month_Summary')">
                    <i class="fas fa-file-csv me-1"></i> CSV
                </button>
            </div>
        </div>

        <div class="card-body p-2 p-md-3">
            <div class="tab-content">

                <!-- ============ TAB 1: Month Summary ============ -->
                <div class="tab-pane fade show active" id="oe-tab-month" role="tabpanel">
                    <div class="tbl-scroll-x">
                        <table class="table table-bordered table-hover table-sm oe-table w-100" id="OE_Month_Summary"
                            style="width:100%;">
                        <thead>
                            <tr class="table-light">
                                <th style="min-width:130px;">Account Name</th>
                                <th style="min-width:90px;">Region</th>
                                <th style="min-width:110px;">Cluster</th>
                                <?php
                                // Map month field prefixes to month numbers (Apr = 4, ..., Mar = 3)
                                $monthFieldToNum = [
                                    'Apr_Score' => 4,
                                    'May_Score' => 5,
                                    'Jun_Score' => 6,
                                    'Jul_Score' => 7,
                                    'Aug_Score' => 8,
                                    'Sep_Score' => 9,
                                    'Oct_Score' => 10,
                                    'Nov_Score' => 11,
                                    'Dec_Score' => 12,
                                    'Jan_Score' => 1,
                                    'Feb_Score' => 2,
                                    'Mar_Score' => 3
                                ];
                                // Filter month fields to only selected ones (if any)
                                $filteredMonthFields = [];
                                foreach (array_keys($monthFieldToNum) as $mf) {
                                    $monthNum = $monthFieldToNum[$mf];
                                    if (empty($selectedMonthNums) || in_array($monthNum, $selectedMonthNums)) {
                                        $filteredMonthFields[] = $mf;
                                    }
                                }
                                // Get 3-letter month name from field
                                $getMonthName = function($mf) {
                                    return substr($mf, 0, 3);
                                };
                                foreach ($filteredMonthFields as $mf): ?>
                                    <th class="text-center" style="min-width:55px;"><?= $getMonthName($mf) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($OE_year_score_query)):
                                foreach ($OE_year_score_query as $ms):
                                    ?>
                                    <tr>
                                        <td class="fw-semibold"><?= esc(isset($ms['location']) ? $ms['location'] : '') ?></td>
                                        <td><?= esc(isset($ms['region']) ? $ms['region'] : '') ?></td>
                                        <td><?= esc(isset($ms['cluster_name']) ? $ms['cluster_name'] : '') ?></td>
                                        <?php foreach ($filteredMonthFields as $mf):
                                            $sc = isset($ms[$mf]) && $ms[$mf] !== '' ? (float) $ms[$mf] : null;
                                            $cls = '';
                                            if ($sc !== null) {
                                                if ($sc >= 85) {
                                                    $cls = 'text-success fw-semibold';
                                                } elseif ($sc < 70) {
                                                    $cls = 'text-danger fw-semibold';
                                                } else {
                                                    $cls = 'text-warning fw-semibold';
                                                }
                                            }
                                            ?>
                                            <td class="text-center <?= $cls ?>">
                                                <?php if ($sc !== null): ?>
                                                    <?php if ($sc === 0.00): ?>
                                                        <span class="text-muted">—</span>
                                                    <?php else: ?>
                                                        <?= number_format($sc, 2) ?>%
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="<?= 2 + count($filteredMonthFields) ?>" class="text-center text-muted py-4">No month summary data found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- ============ TAB 2: Open Points Report ============ -->
                <div class="tab-pane fade" id="oe-tab-report" role="tabpanel">
                    <div class="tbl-scroll-x">
                        <table class="table table-bordered table-hover table-sm oe-table w-100" id="OE_open_point_report"
                            style="width:100%;">
                        <thead>
                            <tr class="table-light">
                                <th style="min-width:160px;">Account Name</th>
                                <th style="min-width:90px;">Region</th>
                                <th style="min-width:110px;">Cluster Manager</th>
                                <th style="min-width:130px;">Account Manager</th>
                                <th style="min-width:90px;">Month</th>
                                <th style="min-width:100px;">Audit Date</th>
                                <th style="min-width:130px;">Category</th>
                                <th style="min-width:130px;">Sub Category</th>
                                <th style="min-width:200px;">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($open_point_report)):
                                foreach ($open_point_report as $row):
                                    ?>
                                    <tr>
                                        <td class="fw-semibold text-primary">
                                            <?= esc(isset($row['client_name']) ? $row['client_name'] : '') ?>
                                        </td>
                                        <td><?= esc(isset($row['region']) ? $row['region'] : '') ?></td>
                                        <td><?= esc(isset($row['cluster_name']) ? $row['cluster_name'] : '-') ?></td>
                                        <td><?= esc(isset($row['account_manager']) ? $row['account_manager'] : '-') ?></td>
                                        <td><?= esc(isset($row['audit_month']) ? $row['audit_month'] : '') ?></td>
                                        <td><?= !empty($row['audit_date']) ? date('d-M-Y', strtotime($row['audit_date'])) : '' ?>
                                        </td>
                                        <td><?= esc(isset($row['category']) ? $row['category'] : '') ?></td>
                                        <td><?= esc(isset($row['audit_parameter']) ? $row['audit_parameter'] : '') ?></td>
                                        <td><?= esc(isset($row['audit_remark']) ? $row['audit_remark'] : '') ?></td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No open points report data found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- ============ TAB 3: Category Wise Aging ============ -->
                <div class="tab-pane fade" id="oe-tab-aging" role="tabpanel">
                    <?php
                    /* ---- Build pivot: unique categories across all aging rows ---- */
                    // Moved to top of file to calculate $oeAgingCount
                    ?>
                    <div class="tbl-scroll-x" style="overflow-x:auto;">
                        <table class="table table-bordered table-hover table-sm oe-table" id="OE_aging_table"
                            style="width:100%; min-width:600px;">
                            <thead>
                                <!-- Row 1: group header -->
                                <tr style="background:#f0f4ff;">
                                    <th colspan="7" class="text-center fw-bold"
                                        style="background:#e8edff;border-right:2px solid #c5cfee;">Location Info</th>
                                    <th colspan="<?= max(1, count($agingCategories)) ?>" class="text-center fw-bold"
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
                                    <th style="min-width:110px;">Cluster Manager</th>
                                    <th style="min-width:130px;">Account Manager</th>
                                    <th style="min-width:100px;">Audit Date</th>
                                    <th style="min-width:90px;border-right:2px solid #c5cfee;">Aging (Days)</th>
                                    <?php foreach ($agingCategories as $cat): ?>
                                        <th class="text-center" style="min-width:110px; white-space:normal; word-break:break-word; vertical-align:middle; font-size:.82rem;">
                                            <?= esc(strtoupper($cat)) ?>
                                        </th>
                                    <?php endforeach; ?>
                                    <?php if (empty($agingCategories)): ?>
                                        <th class="text-center">—</th>
                                    <?php endif; ?>
                                    <th class="text-center fw-bold" style="min-width:80px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($agingPivot)):
                                    foreach ($agingPivot as $locName => $pd):
                                        ?>
                                        <tr>
                                            <td class="fw-semibold text-primary"
                                                style="position: sticky; left: 0; background: #fff; z-index: 1;">
                                                <?= esc($locName) ?>
                                            </td>
                                            <td><?= esc($pd['audit_no']) ?></td>
                                            <td><?= esc($pd['region']) ?></td>
                                            <td><?= esc($pd['cluster']) ?></td>
                                            <td><?= esc(isset($pd['account_manager']) ? $pd['account_manager'] : '-') ?></td>
                                            <td><?= esc($pd['audit_date']) ?></td>
                                            <td style="border-right:2px solid #c5cfee; font-weight: 600; color: #dc3545;">
                                                <?= esc($pd['aging_days']) ?>
                                            </td>
                                            <?php foreach ($agingCategories as $cat):
                                                $pts = (int) ($pd['cats'][$cat] ?? 0);
                                                ?>
                                                <td class="text-center">
                                                    <?php if ($pts > 0): ?>
                                                        <span class="badge"
                                                            style="background:#fee2e2;color:#b91c1c;padding:3px 8px;border-radius:6px;font-weight:700;font-size:.85rem;">
                                                            <?= $pts ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="text-center fw-bold">
                                                <?php if ($pd['total'] > 0): ?>
                                                    <span class="badge"
                                                        style="background:#dbeafe;color:#1d4ed8;padding:3px 10px;border-radius:6px;font-weight:700;">
                                                        <?= $pd['total'] ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">0</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="<?= 7 + max(1, count($agingCategories)) ?>"
                                            class="text-center text-muted py-4">No aging data found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>

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
                            <?= $oeUpcomingCount ?> scheduled <?= $oeUpcomingCount === 1 ? 'audit' : 'audits' ?>
                        </div>
                    </div>
                    <span class="badge rounded-pill"
                        style="background:#eef2ff;color:#4361ee;font-size:.75rem;padding:5px 10px;">
                        <?= $oeUpcomingCount ?> Total
                    </span>
                </div>

                <div class="card-body p-0">
                    <div id="oeUpcomingScroller" style="max-height:420px; overflow-y:auto; padding:.5rem .75rem;">

                        <?php if (!empty($OE_audit)):
                            foreach ($OE_audit as $ua):
                                $nd = !empty($ua['next_date']) ? $ua['next_date'] : null;
                                $dayNum = $nd ? date('d', strtotime($nd)) : '--';
                                $monStr = $nd ? strtoupper(date('M', strtotime($nd))) : '';
                                $dLeft = $nd ? max(0, (int) floor((strtotime($nd) - time()) / 86400)) : null;
                                $bgBadge = ($dLeft === null) ? '#6c757d'
                                    : ($dLeft <= 7 ? '#dc3545'
                                        : ($dLeft <= 14 ? '#ffc107' : '#198754'));
                                $txtBadge = ($dLeft !== null && $dLeft > 7 && $dLeft <= 14) ? '#212529' : '#fff';
                                $daysCls = ($dLeft === null) ? 'bg-secondary text-white'
                                    : ($dLeft <= 7 ? 'bg-danger text-white'
                                        : ($dLeft <= 14 ? 'bg-warning text-dark' : 'bg-success text-white'));
                                ?>
                                <div class="upcoming-item">
                                    <div class="udate-badge" style="background:<?= $bgBadge ?>;color:<?= $txtBadge ?>;">
                                        <div class="ud-day"><?= $dayNum ?></div>
                                        <div class="ud-mon"><?= $monStr ?></div>
                                    </div>

                                    <div class="flex-fill" style="min-width:0;">
                                        <div class="u-title">
                                            <?= strtoupper(esc(isset($ua['client_name']) ? $ua['client_name'] : '')) ?>
                                        </div>
                                        <div class="u-meta">
                                            <?= esc(isset($ua['audit_no']) ? $ua['audit_no'] : '') ?>
                                            <?php if (!empty($ua['region'])): ?>&nbsp;|&nbsp;<?= esc($ua['region']) ?><?php endif; ?>
                                            <?php if (!empty($ua['cluster'])): ?>&nbsp;|&nbsp;<?= esc($ua['cluster']) ?><?php endif; ?>
                                            <?php if (!empty($ua['auditor_name'])): ?>
                                                &nbsp;|&nbsp; <i class="fas fa-user-tie" style="font-size:.65rem;"></i>
                                                <?= esc($ua['auditor_name']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if ($dLeft !== null): ?>
                                        <div class="text-end flex-shrink-0">
                                            <span class="u-badge <?= $daysCls ?>"><?= $dLeft ?> Days Left</span>
                                            <?php if ($nd): ?>
                                                <div style="font-size:.65rem;color:#9a9aa0;margin-top:3px;">
                                                    <?= date('d M Y', strtotime($nd)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach;
                        else: ?>
                            <div class="no-data my-3">No upcoming audits scheduled</div>
                        <?php endif; ?>

                    </div><!-- /#oeUpcomingScroller -->
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

                        <a href="<?= base_url('Masters/Audit_template/template_type_filter/OE') ?>" class="qa-item">
                            <div class="qa-icon" style="background:#dbeafe;">
                                <i class="fas fa-plus-circle text-primary"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="qa-title">New Audit</div>
                                <div class="qa-sub">Create a new OE audit</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted" style="font-size:.75rem;"></i>
                        </a>

                        <a href="<?= base_url('Masters/Reaudit/index/OE') ?>" class="qa-item">
                            <div class="qa-icon" style="background:#fef9c3;">
                                <i class="fas fa-sync-alt text-warning"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="qa-title">OE Reaudit</div>
                                <div class="qa-sub">Initiate re-audit for OE audits</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted" style="font-size:.75rem;"></i>
                        </a>

                        <a href="<?= base_url('Masters/Audit_final_structure') ?>" class="qa-item">
                            <div class="qa-icon" style="background:#dcfce7;">
                                <i class="fas fa-clipboard-check text-success"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="qa-title">OE Audit</div>
                                <div class="qa-sub">Schedule and conduct OE audit</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted" style="font-size:.75rem;"></i>
                        </a>

                        <a href="<?= base_url('Masters/Oe_nc_tracker') ?>" class="qa-item">
                            <div class="qa-icon" style="background:#fce7f3;">
                                <i class="fas fa-tasks text-danger"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="qa-title">OE NC Tracker</div>
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
                <th>OE Score (%)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($pie_chart)):
                foreach ($pie_chart as $sl):
                    $exportLabel = $sl['label'] ?? ($sl['region'] ?? ''); ?>
                    <tr>
                        <td><?= esc($exportLabel) ?></td>
                        <td><?= number_format((float) $sl['score'], 2) ?>%</td>
                    </tr>
                <?php endforeach; endif; ?>
        </tbody>
    </table>

    <table id="hiddenOEBarChartTable" style="display:none;">
        <thead>
            <tr>
                <th>Account Name</th>
                <th>Open Points</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($total_OE_score_openpoints)):
                foreach ($total_OE_score_openpoints as $row):
                    $count = (int) (isset($row['count_open_points']) ? $row['count_open_points'] : 0);
                    if ($count > 0):
                        ?>
                        <tr>
                            <td><?= esc(isset($row['client_name']) ? $row['client_name'] : (isset($row['location']) ? $row['location'] : '')) ?>
                            </td>
                            <td><?= $count ?></td>
                        </tr>
                    <?php endif; endforeach; endif; ?>
        </tbody>
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
    /* -------- Select2 initialisation for OE filter dropdowns -------- */
    /* Helper function to toggle cluster dropdown visibility */
    function toggleClusterDropdown() {
        var yearSelected = $('#filter_year').val() && $('#filter_year').val().length > 0;
        var regionSelected = $('#region').val() && $('#region').val().length > 0;
        var $clusterDiv = $('#cluster_name').closest('.col-6, .col-sm-4, .col-md-2');
        
        if (yearSelected && !regionSelected) {
            $clusterDiv.hide();
            $('#cluster_name').val(null).trigger('change'); // clear cluster selection
        } else {
            $clusterDiv.show();
        }
    }

    $(function () {

        /* Obsolete loadClusters and loadLocations removed to prevent duplicate definitions */

        /* Cascade event listeners moved to the correct closure below */

        /* Cascade: when Year changes, toggle cluster dropdown but DO NOT reload regions/clusters/locations */
        $('#filter_year').on('change select2:select select2:unselect', function () {
            toggleClusterDropdown();
        });

        /* Initial load: toggle cluster dropdown and DO NOT load dependent dropdowns (PHP already did) */
        toggleClusterDropdown();
    });

    Chart.register(ChartDataLabels);

    /* ---------- drillDown: open OE NC Tracker with filters ---------- */
    function drillDown(location, region, cluster) {
        var base = '<?= base_url('Masters/Oe_nc_tracker') ?>';
        window.open(
            base + '?location[]=' + encodeURIComponent(location) +
            '&region[]=' + encodeURIComponent(region) +
            '&cluster[]=' + encodeURIComponent(cluster),
            '_blank'
        );
    }

    /* ---------- Timestamped filename helper ---------- */
    function getTimestampedFilename(base) {
        var now = new Date();
        var pad = function(n) { return String(n).padStart(2, '0'); };
        var ts = String(now.getFullYear()) +
                 pad(now.getMonth() + 1) +
                 pad(now.getDate()) + '_' +
                 pad(now.getHours()) +
                 pad(now.getMinutes()) +
                 pad(now.getSeconds());
        return base + '_' + ts + '.xlsx';
    }

    /* ---------- Export to Excel — full data (bypasses DataTables pagination) ---------- */
    function ExportToExcel(type, fn, dl, tableId) {
        var orig = document.getElementById(tableId);
        if (!orig) return;

        var exportTable;
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
            var dt = $('#' + tableId).DataTable();
            var hdrs = [];

            $(orig).find('thead tr:last-child th').each(function () {
                hdrs.push($(this).text().trim());
            });

            exportTable = document.createElement('table');
            var thead = document.createElement('thead');
            var htr = document.createElement('tr');
            hdrs.forEach(function (h) {
                var th = document.createElement('th');
                th.textContent = h;
                htr.appendChild(th);
            });
            thead.appendChild(htr);
            exportTable.appendChild(thead);

            var tbody = document.createElement('tbody');
            dt.rows({ search: 'applied' }).nodes().each(function (tr) {
                var newTr = document.createElement('tr');
                $(tr).find('td').each(function () {
                    var td = document.createElement('td');
                    td.textContent = $(this).text().trim();
                    newTr.appendChild(td);
                });
                tbody.appendChild(newTr);
            });

            /* Append tfoot totals row if present */
            var tfootOrig = $(orig).find('tfoot');
            if (tfootOrig.length) {
                var tfoot = document.createElement('tfoot');
                tfootOrig.find('tr').each(function () {
                    var ftr = document.createElement('tr');
                    $(this).find('td, th').each(function () {
                        var ftd = document.createElement('td');
                        ftd.textContent = $(this).text().trim();
                        ftr.appendChild(ftd);
                    });
                    tfoot.appendChild(ftr);
                });
                exportTable.appendChild(tfoot);
            }

            exportTable.appendChild(tbody);
        } else {
            exportTable = orig.cloneNode(true);
        }

        /* For Category Wise Aging table, remove the first header row ("Location Info" / "NC Points") */
        if (tableId === 'OE_aging_table') {
            const thead = exportTable.querySelector('thead');
            if (thead && thead.querySelectorAll('tr').length > 1) {
                const firstRow = thead.querySelector('tr');
                if (firstRow) {
                    firstRow.remove();
                }
            }
        }

        /* Remove 'Action' column if present */
        let actionColIdx = -1;
        exportTable.querySelectorAll('thead tr').forEach(tr => {
            tr.querySelectorAll('th, td').forEach((cell, i) => {
                if (cell.textContent.trim().toLowerCase() === 'action') {
                    actionColIdx = i;
                    cell.remove();
                }
            });
        });

        if (actionColIdx !== -1) {
            exportTable.querySelectorAll('tbody tr, tfoot tr').forEach(tr => {
                const cells = tr.querySelectorAll('td, th');
                if (cells.length > actionColIdx) {
                    cells[actionColIdx].remove();
                }
            });
        }

        var wb = XLSX.utils.book_new();
        /* Pass raw:true so SheetJS does NOT auto-convert "50.5%" -> 0.505 */
        var ws = XLSX.utils.table_to_sheet(exportTable, { raw: true });
        /* Post-process: any cell whose formatted value contains "%" → force to string */
        Object.keys(ws).forEach(function (addr) {
            if (addr[0] === '!') return;
            var cell = ws[addr];
            if (cell && typeof cell.w === 'string' && cell.w.indexOf('%') !== -1) {
                cell.t = 's';
                cell.v = cell.w;
                delete cell.z;
            }
        });
        XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
        XLSX.writeFile(wb, fn || 'export.xlsx');
    }

    /* ---------- Export to CSV ---------- */
    function ExportToCSV(fn, tableId) {
        var orig = document.getElementById(tableId);
        if (!orig) return;

        var exportTable;
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
            var dt = $('#' + tableId).DataTable();
            var hdrs = [];

            $(orig).find('thead tr:last-child th').each(function () {
                hdrs.push($(this).text().trim());
            });

            exportTable = document.createElement('table');
            var thead = document.createElement('thead');
            var htr = document.createElement('tr');
            hdrs.forEach(function (h) {
                var th = document.createElement('th');
                th.textContent = h;
                htr.appendChild(th);
            });
            thead.appendChild(htr);
            exportTable.appendChild(thead);

            var tbody = document.createElement('tbody');
            dt.rows({ search: 'applied' }).nodes().each(function (tr) {
                var newTr = document.createElement('tr');
                $(tr).find('td').each(function () {
                    var td = document.createElement('td');
                    td.textContent = $(this).text().trim();
                    newTr.appendChild(td);
                });
                tbody.appendChild(newTr);
            });

            var tfootOrig = $(orig).find('tfoot');
            if (tfootOrig.length) {
                var tfoot = document.createElement('tfoot');
                tfootOrig.find('tr').each(function () {
                    var ftr = document.createElement('tr');
                    $(this).find('td, th').each(function () {
                        var ftd = document.createElement('td');
                        ftd.textContent = $(this).text().trim();
                        ftr.appendChild(ftd);
                    });
                    tfoot.appendChild(ftr);
                });
                exportTable.appendChild(tfoot);
            }
            exportTable.appendChild(tbody);
        } else {
            exportTable = orig.cloneNode(true);
        }

        if (tableId === 'OE_aging_table' || tableId === 'agingTable') {
            const thead = exportTable.querySelector('thead');
            if (thead && thead.querySelectorAll('tr').length > 1) {
                const firstRow = thead.querySelector('tr');
                if (firstRow) {
                    firstRow.remove();
                }
            }
        }

        let actionColIdx = -1;
        exportTable.querySelectorAll('thead tr').forEach(tr => {
            tr.querySelectorAll('th, td').forEach((cell, i) => {
                if (cell.textContent.trim().toLowerCase() === 'action') {
                    actionColIdx = i;
                    cell.remove();
                }
            });
        });

        if (actionColIdx !== -1) {
            exportTable.querySelectorAll('tbody tr, tfoot tr').forEach(tr => {
                const cells = tr.querySelectorAll('td, th');
                if (cells.length > actionColIdx) {
                    cells[actionColIdx].remove();
                }
            });
        }

        var ws = XLSX.utils.table_to_sheet(exportTable, { raw: true });
        Object.keys(ws).forEach(function (addr) {
            if (addr[0] === '!') return;
            var cell = ws[addr];
            if (cell && typeof cell.w === 'string' && cell.w.indexOf('%') !== -1) {
                cell.t = 's';
                cell.v = cell.w;
                delete cell.z;
            }
        });
        
        var csv = XLSX.utils.sheet_to_csv(ws, { blankrows: false });
        var blob = new Blob(["\uFEFF" + csv], { type: "text/csv;charset=utf-8;" });
        var url = URL.createObjectURL(blob);
        var link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", fn || 'export.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /* ---------- DataTables factory — same pattern as HSE ---------- */
    function makeOeDT(selector, scrollYpx, extraOpts) {
        if ($.fn.DataTable.isDataTable(selector)) return;
        var base = {
            paging: true,
            pageLength: 10,
            lengthMenu: [
                [5, 10, 25, 50, 100, -1],
                ['5', '10', '25', '50', '100', 'All']
            ],
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            scrollX: true,
            scrollY: scrollYpx || '400px',
            scrollCollapse: true,
            deferRender: true,
            language: {
                search: '',
                searchPlaceholder: 'Search...',
                lengthMenu: '_MENU_',
                info: 'Showing _START_ – _END_ of <strong>_TOTAL_</strong> entries',
                infoEmpty: 'No entries found',
                infoFiltered: '(filtered from _MAX_ total)',
                paginate: { first: '«', last: '»', next: '›', previous: '‹' },
                emptyTable: 'No data available'
            }
        };
        var opts = {};
        for (var k in base) { opts[k] = base[k]; }
        if (extraOpts) { for (var k in extraOpts) { opts[k] = extraOpts[k]; } }
        return $(selector).DataTable(opts);
    }

    /* ---------- Charts + DataTables init on DOM ready ---------- */
    document.addEventListener('DOMContentLoaded', function () {

        var safeDestroy = function (id) {
            var c = Chart.getChart(id);
            if (c) c.destroy();
        };

        /* ---- OE Score Donut — Region wise (top 10) ---- */
        var oeDotPalette = ['#4361ee', '#f72585', '#7209b7', '#3a0ca3', '#4cc9f0',
            '#4895ef', '#560bad', '#b5179e', '#f3722c', '#43aa8b'];

        var oeRegionRaw = <?= json_encode(array_slice($pie_chart ?? [], 0, 10)) ?>;
        var oeDonutLbls = oeRegionRaw.map(function (r) { return r.label || r.region || ''; });
        var oeDonutData = oeRegionRaw.map(function (r) { return parseFloat(r.score) || 0; });

        var oeDonutEl = document.getElementById('oeScoreDonutChart');
        if (oeDonutEl && oeDonutLbls.length) {
            safeDestroy('oeScoreDonutChart');
            new Chart(oeDonutEl, {
                type: 'doughnut',
                data: {
                    labels: oeDonutLbls,
                    datasets: [{
                        data: oeDonutData,
                        backgroundColor: oeDotPalette.slice(0, oeDonutLbls.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function (ctx) { return ' ' + ctx.label + ': ' + ctx.raw + '%'; } } },
                        datalabels: { display: false }
                    }
                }
            });
        }

        /* ---- Vertical Bar – ALL entries, rainbow colors ---- */
        var rainbowPalette = [
            '#ef4444', '#f97316', '#eab308', '#22c55e',
            '#14b8a6', '#3b82f6', '#6366f1', '#8b5cf6',
            '#a855f7', '#ec4899', '#6b7280', '#0ea5e9'
        ];

        var oeOpenRaw = <?= json_encode(isset($total_OE_score_openpoints) ? $total_OE_score_openpoints : []) ?>;

        var oeBarRows = [];
        oeOpenRaw.forEach(function (r) {
            var lbl = r.client_name || r.location || '';
            var val = parseInt(r.count_open_points, 10) || 0;
            var reg = r.region || '';
            var clu = r.cluster_name || '';
            if (lbl && val > 0) {
                oeBarRows.push({ l: lbl, v: val, r: reg, c: clu });
            }
        });
        oeBarRows.sort(function (a, b) { return b.v - a.v; });

        var oeBarEl = document.getElementById('oeOpenPointsBarChart');
        if (oeBarEl && oeBarRows.length) {
            safeDestroy('oeOpenPointsBarChart');
            var wrap = document.getElementById('oeBarChartWrap');
            /* Vertical bar: width scales with count; height fixed */
            var bw = Math.max(480, oeBarRows.length * 72);
            if (wrap) {
                wrap.style.height = '380px';
                wrap.style.minWidth = bw + 'px';
                wrap.style.overflowX = 'auto';
            }
            oeBarEl.style.height = '380px';
            oeBarEl.style.minWidth = bw + 'px';

            new Chart(oeBarEl, {
                type: 'bar',
                data: {
                    labels: oeBarRows.map(function (r) { return r.l; }),
                    datasets: [{
                        label: 'Open Points',
                        data: oeBarRows.map(function (r) { return r.v; }),
                        backgroundColor: oeBarRows.map(function (_, i) { return rainbowPalette[i % rainbowPalette.length]; }),
                        borderRadius: 6,
                        barThickness: 48,
                        maxBarThickness: 60
                    }]
                },
                options: {
                    /* NO indexAxis override → vertical by default */
                    responsive: false,
                    maintainAspectRatio: false,
                    layout: { padding: { top: 24, bottom: 8 } },
                    onClick: function (e, el) {
                        if (el.length) {
                            var row = oeBarRows[el[0].index];
                            drillDown(row.l, row.r, row.c);
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: function (ctx) { return ' Open Points: ' + ctx.raw; } } },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            offset: 2,
                            color: '#374151',
                            font: { weight: 'bold', size: 11 },
                            formatter: function (v) { return v; },
                            display: function (ctx) { return ctx.dataset.data[ctx.dataIndex] > 0; }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                autoSkip: false,
                                font: { size: 10 },
                                color: '#374151',
                                maxRotation: 45,
                                minRotation: 30
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#6b7280' },
                            title: { display: true, text: 'Open Points Count', font: { weight: '600', size: 12 }, color: '#374151' },
                            grid: { color: '#f3f4f6' },
                            border: { color: '#e5e7eb' }
                        }
                    }
                }
            });
        }

        /* ---- DataTables init ---- */

        /* Open Points Summary (always visible) */
        if ($('#OE_openpoints_table tbody tr td').length > 1) {
            makeOeDT('#OE_openpoints_table', '380px', {
                columnDefs: [{ orderable: false, targets: -1 }],
                order: [[3, 'desc']]
            });
        }

        /* Tab 1: Month Summary (active by default — init immediately) */
        if ($('#OE_Month_Summary tbody tr td').length > 1) {
            makeOeDT('#OE_Month_Summary', '420px', {
                order: [[0, 'asc']]
            });
        }

        /* Tab 2: Open Points Report */
        $('#oe-tab-report-btn').one('shown.bs.tab', function () {
            if ($('#OE_open_point_report tbody tr td').length > 1) {
                if (!$.fn.DataTable.isDataTable('#OE_open_point_report')) {
                    makeOeDT('#OE_open_point_report', '420px', {
                        order: [[3, 'desc']]
                    });
                }
            }
        });

        /* Tab 3: Aging */
        $('#oe-tab-aging-btn').one('shown.bs.tab', function () {
            if ($('#OE_aging_table tbody tr td').length > 1) {
                if (!$.fn.DataTable.isDataTable('#OE_aging_table')) {
                    makeOeDT('#OE_aging_table', '420px', {
                        order: [[4, 'desc']]
                    });
                }
            }
        });

        /* Recalculate column widths when tab shown (fixes header misalign) */
        $('[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).data('bs-target');
            var tableMap = {
                '#oe-tab-month': '#OE_Month_Summary',
                '#oe-tab-report': '#OE_open_point_report',
                '#oe-tab-aging': '#OE_aging_table'
            };
            var tblSel = tableMap[target];
            if (tblSel && $.fn.DataTable.isDataTable(tblSel)) {
                $(tblSel).DataTable().columns.adjust().draw(false);
            }
        });

        /* Tab export button swap */
        var tabExportMap = {
            'oe-tab-month-btn': { file: 'OE_Month_Summary.xlsx', table: 'OE_Month_Summary' },
            'oe-tab-report-btn': { file: 'OE_Open_Point_Report.xlsx', table: 'OE_open_point_report' },
            'oe-tab-aging-btn': { file: getTimestampedFilename('OE_Category_Wise_Aging'), table: 'OE_aging_table' }
        };

        /* Drilldown click listeners */
        $(document).on('click', '.oe-drilldown-row', function(e) {
            var loc = $(this).data('loc');
            var reg = $(this).data('reg');
            var clu = $(this).data('clu');
            drillDown(loc, reg, clu);
        });
        
        $(document).on('click', '.oe-drilldown-btn', function(e) {
            e.stopPropagation();
            var row = $(this).closest('.oe-drilldown-row');
            var loc = row.data('loc');
            var reg = row.data('reg');
            var clu = row.data('clu');
            drillDown(loc, reg, clu);
        });
        $('#oeMainTabs [data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var info = tabExportMap[e.target.id];
            if (info) {
                $('#oeTabExportBtn').html(
                    // '<button class="btn btn-success btn-sm rounded-pill px-3 me-2" ' +
                    // 'onclick="ExportToExcel(\'xlsx\',\'' + info.file + '\',false,\'' + info.table + '\')">' +
                    // '<i class="fas fa-file-excel me-1"></i> Excel' +
                    // '</button>' +
                    '<button class="btn btn-info btn-sm rounded-pill px-3" ' +
                    'onclick="ExportToCSV(\'' + info.file.replace('.xlsx', '.csv') + '\',\'' + info.table + '\')">' +
                    '<i class="fas fa-file-csv me-1"></i> CSV' +
                    '</button>'
                );
            }
        });
    });

    /* ---- Helper to preserve selections ---- */
    function preserveSelections($el, optionsHtml) {
            var selected = $el.val();
            if (!Array.isArray(selected)) {
                selected = selected ? [selected] : [];
            }
            // Always ensure Select All exists in the newly loaded options
            optionsHtml = '<option value="selectAll">Select All</option>' + optionsHtml;
            $el.empty().html(optionsHtml);
            
            // Re-select only valid options that still exist
            var validSelected = [];
            $el.find('option').each(function() {
                if (selected.includes($(this).val())) {
                    validSelected.push($(this).val());
                }
            });
            // Preserve selectAll if it was selected
            if (selected.includes('selectAll')) {
                validSelected.push('selectAll');
            }
            $el.val(validSelected);
            if ($el.data('select2')) {
               $el.trigger('change.select2');
            }
            // Update the UI badges
            if (typeof updateCustomBadges === 'function') {
                updateCustomBadges($el);
            }
        }

        /* ---- Ajax Region, Cluster & Account Name dropdowns ---- */
        function loadRegions() {
            $.post('<?= base_url('Customer/Audit_dashboard/get_regions_by_year') ?>',
                { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
                function (res) {
                    var h = '';
                    if (res && res.length > 0) {
                        res.forEach(r => {
                            h += `<option value="${r.region_name}">${r.region_name}</option>`;
                        });
                    }
                    preserveSelections($('#region'), h);
                }, 'json');
        }

        function loadClusters() {
            var regions = $('#region').val();
            if (!regions) regions = [];
            
            $.post('<?= base_url('Customer/Audit_dashboard/get_clusters_by_region') ?>',
                { region_name: regions, '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
                function (res) {
                    var h = '';
                    if (res && Array.isArray(res)) {
                        res.forEach(c => {
                            h += `<option value="${c.cluster_name}">${c.cluster_name}</option>`;
                        });
                    }
                    preserveSelections($('#cluster_name'), h);
                    
                    // Automatically trigger loadLocations to continue the cascade
                    loadLocations();
                }, 'json');
        }

        function loadLocations() {
            var clusters = $('#cluster_name').val();
            var regions = $('#region').val();
            if (!clusters) clusters = [];
            if (!regions) regions = [];
            
            $.post('<?= base_url('Customer/Audit_dashboard/get_locations_by_cluster') ?>',
                { cluster_name: clusters, region_name: regions, '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
                function (res) {
                    var h = '';
                    if (res && Array.isArray(res)) {
                        res.forEach(l => {
                            h += `<option value="${l.location_name}">${l.location_name}</option>`;
                        });
                    }
                    preserveSelections($('#location_name'), h);
                }, 'json');
        }

    $(function() {
        /* Cascade: when Region changes, AJAX load clusters and reset location */
        $('#region').on('change select2:select select2:unselect', debounce(function (e) {
            toggleClusterDropdown();
            loadClusters();
        }, 100));

        /* Cascade: when Cluster changes, AJAX load locations */
        $('#cluster_name').on('change select2:select select2:unselect', debounce(function (e) {
            loadLocations();
        }, 100));
    });

        // Simple debounce utility to prevent multiple rapid triggers during Select All
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
</script>
<?php $this->endSection(); ?>