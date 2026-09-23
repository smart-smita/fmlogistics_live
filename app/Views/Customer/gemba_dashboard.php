<?= $this->extend('Layout/base_admin') ?>

<?= $this->section('breadcrumb_title_li') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="<?= base_url('js/select2.min.css') ?>">
<style>
    /* Select2 inside Gemba filter — match Bootstrap sm control height and responsive */
    .gd-filter-card .select2-container {
        width: 100% !important;
    }
    .gd-filter-card .select2-container .select2-selection--multiple {
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
    .gd-filter-card .select2-container .select2-selection--multiple::-webkit-scrollbar {
        width: 4px;
    }
    .gd-filter-card .select2-container .select2-selection--multiple::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 4px;
    }

    .gd-filter-card .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #8b5cf6; /* Match gemba purple */
        border: none;
        color: #fff;
        border-radius: 4px;
        font-size: 0.78rem;
        padding: 2px 8px;
        margin: 4px 4px 0 0; /* consistent spacing */
        /* Responsive truncation for long names */
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        box-sizing: border-box;
        display: inline-flex;
        align-items: center;
        vertical-align: top;
    }
    .gd-filter-card .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(255,255,255,.9);
        margin-right: 6px;
        order: -1; /* Put 'x' on the left */
        border-right: 1px solid rgba(255,255,255,0.2);
        padding-right: 6px;
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
       GEMBA DASHBOARD  –  Redesign (HSE/OE-style)
    ============================================================ */

    .gd-wrap {
        font-family: 'Poppins', sans-serif;
        padding: 0 4px;
    }

    .gd-wrap *:not(i):not([class*="fa"]):not([class*="fas"]):not([class*="far"]):not([class*="fab"]):not([class*="fal"]) {
        font-family: 'Poppins', sans-serif;
    }

    div[id$="_wrapper"] *:not(i):not([class*="fa"]):not([class*="fas"]):not([class*="far"]):not([class*="fab"]) {
        font-family: 'Poppins', sans-serif;
    }

    .gd-wrap i[class*="fa"],
    div[id$="_wrapper"] i[class*="fa"] {
        font-family: "Font Awesome 5 Free", "Font Awesome 5 Brands", "FontAwesome" !important;
    }

    /* ---- Page Header ---- */
    .gd-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 1.25rem;
    }

    .gd-page-header h2 {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .gd-page-header p {
        font-size: .80rem;
        color: #9a9aa0;
        margin: 2px 0 0;
    }

    /* ---- Filter card ---- */
    .gd-filter-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 1px 8px rgba(0, 0, 0, .07);
    }

    .gd-filter-card .form-select,
    .gd-filter-card .form-control {
        border-radius: 8px;
        font-size: .85rem;
    }

    .gd-filter-card .form-label {
        font-size: .78rem;
    }

    /* ---- Export CSV Button ---- */
    .export-csv-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 5px 10px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #0369a1;
        background-color: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 6px;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }

    .export-csv-btn i {
        font-size: 0.85rem;
    }

    .export-csv-btn:hover {
        background-color: #e0f2fe;
        color: #075985;
        border-color: #7dd3fc;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(3, 105, 161, 0.1);
    }

    /* ---- KPI cards (Tracker Cards) ---- */
    .tracker-cards-container {
        margin-bottom: 24px;
    }
    .tracker-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 20px 16px;
        height: 100%;
        min-height: 145px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        text-decoration: none !important;
        position: relative;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.025);
        cursor: pointer;
    }
    .tracker-card::before {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background-color: var(--status-color);
        transition: height 0.25s ease;
    }
    .tracker-card:hover::before {
        height: 8px;
    }
    .tracker-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px var(--shadow-color), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
    }
    .tracker-card.active-filter {
        background: var(--light-bg);
        border: 2px solid var(--status-color) !important;
        box-shadow: 0 12px 25px -8px var(--shadow-color) !important;
    }
    .tracker-card .card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-bottom: 12px;
    }
    .tracker-card .icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 12px;
        background-color: var(--light-bg);
        color: var(--status-color);
        transition: all 0.25s ease;
    }
    .tracker-card:hover .icon-wrapper {
        transform: scale(1.1);
    }
    .tracker-card .card-value {
        font-size: 34px;
        font-weight: 800;
        color: var(--status-color);
        line-height: 1;
        margin: 0;
    }
    .tracker-card .card-body-content {
        display: flex;
        flex-direction: column;
        width: 100%;
        margin-bottom: 4px;
    }
    .tracker-card .card-title-text {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .tracker-card .card-subtitle-text {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
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
        font-size: .72rem;
        color: #9a9aa0;
        margin-top: 2px;
        word-wrap: break-word;
        white-space: normal;
    }

    .kpi-sub .sub-hi {
        font-weight: 700;
        color: #4b5563;
    }

    @media (min-width:768px) {
        .col-md-kpi {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
    }

    @media (min-width:1200px) {
        .col-md-kpi {
            flex: 0 0 16.666667%;
            max-width: 16.666667%;
        }
    }

    @media (max-width:1199px) {
        .kpi-value {
            font-size: 1.7rem;
        }
    }

    @media (max-width:767px) {
        .kpi-value {
            font-size: 1.45rem;
        }

        .kpi-icon-box {
            width: 44px;
            height: 44px;
            font-size: 1.2rem;
        }
    }

    @media (max-width:575px) {
        .kpi-value {
            font-size: 1.35rem;
        }

        .kpi-card .card-body {
            gap: 10px;
            padding: .85rem 1rem;
        }
    }

    /* ---- Generic chart card ---- */
    .gd-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .07);
        height: 100%;
    }

    .gd-card .card-header {
        background: transparent;
        border-bottom: 1px solid #f0f0f0;
        padding: .85rem 1.1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }

    .gd-card .ch-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1a1a2e;
    }

    .gd-card .ch-sub {
        font-size: .80rem;
        color: #9a9aa0;
        margin-top: 1px;
    }

    /* ---- Donut wrap ---- */
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
        font-size: 1.4rem;
        font-weight: 800;
        color: #1a1a2e;
        line-height: 1;
    }

    .donut-center .dc-lbl {
        font-size: .72rem;
        color: #9a9aa0;
        font-weight: 600;
        margin-top: 2px;
    }

    @media (max-width:1199px) {
        .score-donut-wrap {
            width: 150px;
            height: 150px;
        }

        .donut-center .dc-val {
            font-size: 1.1rem;
        }
    }

    @media (max-width:767px) {
        .score-donut-wrap {
            width: 130px;
            height: 130px;
        }
    }

    /* ---- Color Priority Blocks ---- */
    .pc-priority {
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #fff;
    }

    .pc-priority:last-child {
        margin-bottom: 0;
    }

    .pc-red {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .pc-yellow {
        background: linear-gradient(135deg, #fcd34d, #d97706);
    }

    .pc-black {
        background: linear-gradient(135deg, #374151, #111827);
    }

    .pc-label {
        font-size: .75rem;
        font-weight: 600;
        opacity: .9;
    }

    .pc-value {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1;
        margin-top: 2px;
    }

    .pc-pct {
        font-size: .85rem;
        font-weight: 700;
        opacity: .9;
    }

    /* ---- NC vs Rec Donut legend ---- */
    .nc-legend {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .nc-legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: .8rem;
        font-weight: 600;
        color: #4b5563;
    }

    .nc-legend-dot {
        width: 11px;
        height: 11px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    /* ---- Tab styles (pill style) ---- */
    .gd-tab-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .07);
    }

    .gd-tab-card .card-header {
        background: transparent;
        border-bottom: 1px solid #f0f0f0;
        padding: .75rem 1.1rem;
    }

    .sec-tabs {
        gap: 4px;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 2px;
    }

    .sec-tabs::-webkit-scrollbar {
        height: 0;
    }

    .sec-tabs .nav-link {
        font-size: .85rem;
        font-weight: 600;
        color: #6c757d;
        border: none;
        padding: .42rem .95rem;
        border-radius: 8px;
        white-space: nowrap;
        background: transparent;
    }

    .sec-tabs .nav-link.active {
        background: #0d6efd;
        color: #fff;
    }

    .sec-tabs .nav-link:hover:not(.active) {
        background: #f1f5ff;
        color: #0d6efd;
    }

    /* ---- Table ---- */
    .gd-table thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-size: .8rem;
        white-space: nowrap;
        font-weight: 700;
    }

    .gd-table tbody td {
        font-size: .8rem;
        vertical-align: middle;
    }

    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover {
        background: #f1f5ff !important;
    }

    .badge-aging-ok {
        background: #dcfce7;
        color: #15803d;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: .72rem;
        font-weight: 700;
    }

    .badge-aging-bad {
        background: #fee2e2;
        color: #b91c1c;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: .72rem;
        font-weight: 700;
    }

    /* ---- DataTables overrides ---- */
    div[id$="_wrapper"] .dataTables_length select,
    div[id$="_wrapper"] .dataTables_filter input {
        border: 1.5px solid #e0e4ef;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: .8rem;
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
        font-size: .8rem;
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
        font-size: .8rem;
        color: #6c757d;
        font-weight: 500;
    }

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

    /* ---- Quick Actions Styling from OE ---- */
    .qa-item {
        display: flex;
        align-items: center;
        padding: 12px 14px;
        text-decoration: none;
        color: inherit;
        background: #fff;
        transition: all 0.2s ease;
    }

    .qa-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .qa-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .qa-title {
        font-size: 0.9rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 2px;
    }

    .qa-sub {
        font-size: 0.75rem;
        color: #64748b;
        line-height: 1.3;
    }

    .bg-light-primary {
        background: #eff6ff !important;
    }

    .bg-light-success {
        background: #f0fdf4 !important;
    }

    .bg-light-purple {
        background: #faf5ff !important;
    }

    .bg-light-warning {
        background: #fffbeb !important;
    }

    .bg-light-teal {
        background: #f0fdfa !important;
    }

    .bg-light-info {
        background: #ecfeff !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('main_body') ?>
<?php
$totalPoints = (int) ($summary['total'] ?? 0);
$openPoints = (int) ($summary['open'] ?? 0);
$closedPoints = (int) ($summary['closed'] ?? 0);
$excludedPoints = (int) ($summary['excluded'] ?? 0);
$holdPoints = (int) ($summary['hold'] ?? 0);
$overduePoints = (int) ($summary['overdue'] ?? 0);

$totalProgress = $closedPoints + $openPoints;
$closedPct = $totalProgress > 0 ? round(($closedPoints / $totalProgress) * 100, 1) : 0;

$colorRed = (int) ($chart_color['Red'] ?? 0);
$colorYellow = (int) ($chart_color['Yellow'] ?? 0);
$colorBlack = (int) ($chart_color['Black'] ?? 0);
$totalColorOpen = $colorRed + $colorYellow + $colorBlack;
$redPct = $totalColorOpen > 0 ? round(($colorRed / $totalColorOpen) * 100, 1) : 0;
$yellowPct = $totalColorOpen > 0 ? round(($colorYellow / $totalColorOpen) * 100, 1) : 0;
$blackPct = $totalColorOpen > 0 ? round(($colorBlack / $totalColorOpen) * 100, 1) : 0;

$ncCount = (int) ($chart_nc_rec['NC'] ?? 0);
$recCount = (int) ($chart_nc_rec['RECOMMENDATION'] ?? 0);
$ncRecTotal = $ncCount + $recCount;
?>

<div class="container-fluid gd-wrap mt-3">

    <!-- ===================== PAGE HEADER ===================== -->
    <div class="gd-page-header">
        <div>
            <?php helper('gemba_acl'); ?>
            <h2><i class="fas fa-chart-line text-primary fs-1"></i> Gemba HSE Audit Dashboard
                <?php if (!gemba_can_write()): ?>
                    <span class="badge bg-warning text-dark ms-2"
                        style="font-size: 0.55em; vertical-align: middle; padding: 6px 10px; border-radius: 6px;">Read Only
                        Access</span>
                <?php endif; ?>
            </h2>
            <p>Gemba audits, open points &amp; analytics overview</p>
        </div>
        <!-- <div class="d-flex gap-2">
            <a href="javascript:history.back()" class="btn btn-light shadow-sm btn-sm fw-bold align-self-start">
                <i class="fas fa-chevron-left me-1"></i> Back
            </a>
        </div> -->
    </div>

    <!-- ===================== QUICK ACTIONS ===================== -->
    <div class="card mb-4"
        style="border:1px solid #f43f5e; border-radius:12px; box-shadow:0 4px 12px rgba(244,63,94,0.08);">
        <div class="card-body py-3 px-4">
            <div class="d-flex align-items-center mb-3">
                <h6 class="mb-0 fw-bold" style="color:#1a1a2e; font-size:1.05rem;">Quick Actions</h6>
            </div>
            <div class="row g-3">

                <?php if (gemba_can_write()): ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <a href="<?= base_url('gemba-audit') ?>" target="_blank" class="qa-item border h-100 bg-light-primary"
                            style="border-radius: 12px; border-color: #e8eaf0 !important;">
                            <div class="qa-icon text-white" style="background:#3b82f6; border-radius: 8px;">
                                <i class="fas fa-clipboard-check text-white"></i>
                            </div>
                            <div class="flex-fill ms-2">
                                <div class="qa-title text-dark">Gemba Audit</div>
                                <div class="qa-sub">Create Audit</div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('gemba-sites') ?>" target="_blank" class="qa-item border h-100 bg-light-success"
                        style="border-radius: 12px; border-color: #e8eaf0 !important;">
                        <div class="qa-icon text-white" style="background:#10b981; border-radius: 8px;">
                            <i class="far fa-building text-white"></i>
                        </div>
                        <div class="flex-fill ms-2">
                            <div class="qa-title text-dark">Gemba Sites</div>
                            <div class="qa-sub">Manage Sites</div>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/GembaNcTracker') ?>" target="_blank" class="qa-item border h-100 bg-light-purple"
                        style="border-radius: 12px; border-color: #e8eaf0 !important;">
                        <div class="qa-icon text-white" style="background:#8b5cf6; border-radius: 8px;">
                            <i class="fas fa-bullseye text-white"></i>
                        </div>
                        <div class="flex-fill ms-2">
                            <div class="qa-title text-dark">Gemba NC Tracker</div>
                            <div class="qa-sub">Track NCs</div>
                        </div>
                    </a>
                </div>


                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/Hse_audit') ?>" target="_blank" class="qa-item border h-100 bg-light-info"
                        style="border-radius: 12px; border-color: #e8eaf0 !important;">
                        <div class="qa-icon text-white" style="background:#06b6d4; border-radius: 8px;">
                            <i class="fas fa-download text-white"></i>
                        </div>
                        <div class="flex-fill ms-2">
                            <div class="qa-title text-dark">HSE Performed Audit</div>
                            <div class="qa-sub">Download</div>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- ===================== FILTER BAR ===================== -->
    <div class="card gd-filter-card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= base_url('Customer/Gemba_Dashboard') ?>" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label fw-semibold text-muted mb-1">Region</label>
                        <select name="region[]" id="gd_region" class="form-select form-select-sm select2-filter"
                            multiple="multiple" data-control="select2" data-placeholder="All Regions">
                            <?php foreach ($regions as $r): ?>
                                <option value="<?= esc($r['region']) ?>" <?= in_array($r['region'], $filters['region']) ? 'selected' : '' ?>>
                                    <?= esc($r['region']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label fw-semibold text-muted mb-1">Audit Category</label>
                        <select name="audit_category[]" id="gd_audit_category"
                            class="form-select form-select-sm select2-filter" multiple="multiple" data-control="select2"
                            data-placeholder="All Categories">
                            <?php foreach ($audit_categories as $ac): ?>
                                <option value="<?= esc($ac['audit_category']) ?>" <?= in_array($ac['audit_category'], $filters['audit_category']) ? 'selected' : '' ?>>
                                    <?= esc($ac['audit_category']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label fw-semibold text-muted mb-1">Site Category</label>
                        <select name="site_category[]" id="gd_site_category"
                            class="form-select form-select-sm select2-filter" multiple="multiple" data-control="select2"
                            data-placeholder="All Categories">
                            <?php foreach ($site_categories as $c): ?>
                                <option value="<?= esc($c['site_category']) ?>" <?= in_array($c['site_category'], $filters['site_category']) ? 'selected' : '' ?>>
                                    <?= esc($c['site_category']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label fw-semibold text-muted mb-1">Site Name</label>
                        <select name="site_name[]" id="gd_site_name"
                            class="form-select form-select-sm select2-filter" multiple="multiple" data-control="select2"
                            data-placeholder="All Sites">
                            <?php foreach ($site_names as $s): ?>
                                <option value="<?= esc($s['site_name']) ?>" <?= in_array($s['site_name'], $filters['site_name']) ? 'selected' : '' ?>>
                                    <?= esc($s['site_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label fw-semibold text-muted mb-1">Point Status</label>
                        <select name="point_status[]" class="form-select form-select-sm select2-filter"
                            multiple="multiple" data-control="select2" data-placeholder="All Status">
                            <option value="Open" <?= in_array('Open', $filters['point_status']) ? 'selected' : '' ?>>Open
                            </option>
                            <option value="WIP" <?= in_array('WIP', $filters['point_status']) ? 'selected' : '' ?>>WIP
                            </option>
                            <option value="Closed" <?= in_array('Closed', $filters['point_status']) ? 'selected' : '' ?>>
                                Closed</option>
                            <option value="Excluded" <?= in_array('Excluded', $filters['point_status']) ? 'selected' : '' ?>>Excluded</option>
                            <option value="Hold-review with Client" <?= in_array('Hold-review with Client', $filters['point_status']) ? 'selected' : '' ?>>Hold-review with Client</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label fw-semibold text-muted mb-1">NC Type</label>
                        <select name="nc_recommendation[]" class="form-select form-select-sm select2-filter"
                            multiple="multiple" data-control="select2" data-placeholder="All Types">
                            <?php foreach ($nc_types as $n): ?>
                                <option value="<?= esc($n['nc_recommendation']) ?>" <?= in_array($n['nc_recommendation'], $filters['nc_recommendation']) ? 'selected' : '' ?>>
                                    <?= esc($n['nc_recommendation']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <label class="form-label fw-semibold text-muted mb-1">Ageing</label>
                        <select name="age_bracket[]" class="form-select form-select-sm select2-filter"
                            multiple="multiple" data-control="select2" data-placeholder="All Ages">
                            <?php foreach ($age_brackets as $ab): ?>
                                <option value="<?= esc($ab['age_bracket']) ?>" <?= in_array($ab['age_bracket'], $filters['age_bracket'] ?? []) ? 'selected' : '' ?>>
                                    <?= esc($ab['age_bracket']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill fw-semibold">
                            <i class="fa fa-filter me-1"></i> Apply
                        </button>
                        <a href="<?= base_url('Customer/Gemba_Dashboard') ?>"
                            class="btn btn-outline-secondary btn-sm flex-fill fw-semibold">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================== KPI CARDS ===================== -->
    <div class="tracker-cards-container" id="tracker-cards">
        <div class="row g-3 mb-4">
            <!-- Total Observation Points -->
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-6">
                <div class="tracker-card" style="--status-color: #4f46e5; --light-bg: #f5f3ff; --shadow-color: rgba(79, 70, 229, 0.25);">
                    <div class="card-top">
                        <div class="icon-wrapper">
                            <i class="fas fa-clipboard-list" style="font-size: 24px;"></i>
                        </div>
                        <h3 class="card-value"><?= number_format($totalPoints) ?></h3>
                    </div>
                    <div class="card-body-content">
                        <div class="card-title-text" title="Total Observation Points">Total Observation Points</div>
                        <div class="card-subtitle-text" title="Overall points raised">Overall points raised</div>
                    </div>
                </div>
            </div>
            <!-- Closed Points -->
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-6">
                <div class="tracker-card" style="--status-color: #10b981; --light-bg: #ecfdf5; --shadow-color: rgba(16, 185, 129, 0.25);">
                    <div class="card-top">
                        <div class="icon-wrapper">
                            <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                        </div>
                        <h3 class="card-value"><?= number_format($closedPoints) ?></h3>
                    </div>
                    <div class="card-body-content">
                        <div class="card-title-text" title="Closed Points">Closed Points</div>
                        <div class="card-subtitle-text" title="Resolved points">Resolved points</div>
                    </div>
                </div>
            </div>
            <!-- Open Points -->
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-6">
                <div class="tracker-card" style="--status-color: #f59e0b; --light-bg: #fffbeb; --shadow-color: rgba(245, 158, 11, 0.25);">
                    <div class="card-top">
                        <div class="icon-wrapper">
                            <i class="fas fa-exclamation-circle" style="font-size: 24px;"></i>
                        </div>
                        <h3 class="card-value"><?= number_format($openPoints) ?></h3>
                    </div>
                    <div class="card-body-content">
                        <div class="card-title-text" title="Open Points">Open Points</div>
                        <div class="card-subtitle-text" title="Pending resolution">Pending resolution</div>
                    </div>
                </div>
            </div>
            <!-- Overdue Points -->
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-6">
                <div class="tracker-card" style="--status-color: #ef4444; --light-bg: #fef2f2; --shadow-color: rgba(239, 68, 68, 0.25);">
                    <div class="card-top">
                        <div class="icon-wrapper">
                            <i class="fas fa-clock" style="font-size: 24px;"></i>
                        </div>
                        <h3 class="card-value"><?= number_format($overduePoints) ?></h3>
                    </div>
                    <div class="card-body-content">
                        <div class="card-title-text" title="Overdue Points">Overdue Points</div>
                        <div class="card-subtitle-text" title="Past target date">Past target date</div>
                    </div>
                </div>
            </div>
            <!-- Excluded Points -->
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-6">
                <div class="tracker-card" style="--status-color: #64748b; --light-bg: #f8fafc; --shadow-color: rgba(100, 116, 139, 0.25);">
                    <div class="card-top">
                        <div class="icon-wrapper">
                            <i class="fas fa-ban" style="font-size: 24px;"></i>
                        </div>
                        <h3 class="card-value"><?= number_format($excludedPoints) ?></h3>
                    </div>
                    <div class="card-body-content">
                        <div class="card-title-text" title="Excluded Points">Excluded Points</div>
                        <div class="card-subtitle-text" title="Excluded from counts">Excluded from counts</div>
                    </div>
                </div>
            </div>
            <!-- Hold Points -->
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-6">
                <div class="tracker-card" style="--status-color: #8b5cf6; --light-bg: #f5f3ff; --shadow-color: rgba(139, 92, 246, 0.25);">
                    <div class="card-top">
                        <div class="icon-wrapper">
                            <i class="fas fa-pause-circle" style="font-size: 24px;"></i>
                        </div>
                        <h3 class="card-value"><?= number_format($holdPoints) ?></h3>
                    </div>
                    <div class="card-body-content">
                        <div class="card-title-text" title="Hold Points">Hold Points</div>
                        <div class="card-subtitle-text" title="On Hold / Review">On Hold / Review</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <!-- ===================== WEEKLY PENDING APPROVAL NOTIFICATION CARD ===================== -->
    <?php if (!empty($gemba_notification)): ?>
        <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-4 rounded shadow-sm position-relative">
            <span class="svg-icon svg-icon-2hx svg-icon-danger me-4 mb-5 mb-sm-0">
                <i class="fas fa-exclamation-triangle fs-2hx text-danger"></i>
            </span>
            <div class="d-flex flex-column text-dark pe-0 pe-sm-10">
                <h5 class="mb-2 fw-bold text-danger"><i class="fas fa-bell me-2"></i><?= esc($gemba_notification['title']) ?></h5>
                <div class="fs-6"><?= $gemba_notification['message'] ?></div>
                <div class="mt-3">
                    <a href="<?= base_url('Masters/GembaNcTracker/read_and_redirect/' . $gemba_notification['id']) ?>" class="btn btn-danger btn-sm fw-bold">
                        <i class="fas fa-check-circle me-1"></i> View Pending Points
                    </a>
                </div>
            </div>
            <button type="button" class="position-absolute top-0 end-0 m-3 btn btn-icon btn-sm btn-active-light-danger" data-bs-dismiss="alert">
                <i class="fas fa-times text-danger fs-5"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- ===================== WEEKLY PENDING APPROVAL SUMMARY WIDGET ===================== -->
    <?php if (isset($widget_counts) && $widget_counts['total'] > 0): ?>
        <div class="card mb-4 border border-warning shadow-sm" style="background: linear-gradient(135deg, #fffcf6, #fff9eb);">
            <div class="card-body py-4 px-5">
                <!-- Header Section -->
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-size: 1.35rem; flex-shrink: 0;">
                        <i class="fas fa-exclamation-triangle text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold text-warning-dark" style="color: #854d0e;"><i class="fas fa-exclamation-circle me-1"></i> Weekly Pending Approval Summary</h5>
                        <p class="mb-0 text-muted" style="font-size: 0.82rem;">You have pending observations due in the current week requiring review.</p>
                    </div>
                </div>
                
                <!-- Counts Section -->
                <div class="d-flex align-items-center justify-content-around flex-wrap gap-2 gap-md-3">
                    <a href="<?= base_url('Masters/GembaNcTracker/template_type_filter/pending_due?color_code=Black') ?>" class="text-center px-3 py-2 border-end border-warning border-opacity-25 text-decoration-none" style="min-width: 100px;">
                        <span class="d-block fw-bold text-dark fs-3"><?= $widget_counts['black'] ?></span>
                        <span class="badge bg-dark text-white" style="font-size: 0.75rem; font-weight: 700;">Black Pending</span>
                    </a>
                    <a href="<?= base_url('Masters/GembaNcTracker/template_type_filter/pending_due?color_code=Red') ?>" class="text-center px-3 py-2 border-end border-warning border-opacity-25 text-decoration-none" style="min-width: 100px;">
                        <span class="d-block fw-bold text-danger fs-3"><?= $widget_counts['red'] ?></span>
                        <span class="badge bg-danger text-white" style="font-size: 0.75rem; font-weight: 700;">Red Pending</span>
                    </a>
                    <a href="<?= base_url('Masters/GembaNcTracker/template_type_filter/pending_due?color_code=Yellow') ?>" class="text-center px-3 py-2 border-end border-warning border-opacity-25 text-decoration-none" style="min-width: 100px;">
                        <span class="d-block fw-bold text-warning fs-3"><?= $widget_counts['yellow'] ?></span>
                        <span class="badge bg-warning text-dark" style="font-size: 0.75rem; font-weight: 700; color: #1e293b;">Yellow Pending</span>
                    </a>
                    <a href="<?= base_url('Masters/GembaNcTracker/template_type_filter/pending_due') ?>" class="text-center px-3 py-2 text-decoration-none" style="min-width: 100px;">
                        <span class="d-block fw-bolder text-warning-dark fs-2" style="color: #854d0e;"><?= $widget_counts['total'] ?></span>
                        <span class="badge bg-warning text-dark" style="font-size: 0.75rem; font-weight: 800; background-color: #fef08a !important; color: #854d0e !important;">Total Pending</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ===================== CHARTS ROW 1 ===================== -->
    <div class="row g-3 mb-4">

        <!-- Open vs Closed Gauge -->
        <div class="col-12 col-xl-4">
            <div class="card gd-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="ch-title">Open vs Closed Status</div>
                        <div class="ch-sub">Audit Progress</div>
                    </div>
                    <a href="javascript:void(0);" onclick="exportChartExcel('open_closed')" class="export-csv-btn"
                        title="Export CSV Data">
                        <i class="fas fa-file-csv"></i> Export
                    </a>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-3">
                    <div class="score-donut-wrap" style="width:200px;height:200px;margin-bottom:-38px;">
                        <canvas id="gaugeChart"></canvas>
                        <div class="donut-center" style="top:56%;">
                            <div class="dc-val"><?= $closedPct ?>%</div>
                            <div class="dc-lbl">Closed</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-4 w-100 mt-4 pt-2">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:11px;height:11px;border-radius:50%;background:#16a34a;flex-shrink:0;">
                            </div>
                            <span style="font-size:.8rem;font-weight:600;color:#4b5563;">Closed:
                                <?= number_format($closedPoints) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:11px;height:11px;border-radius:50%;background:#dc3545;flex-shrink:0;">
                            </div>
                            <span style="font-size:.8rem;font-weight:600;color:#4b5563;">Open:
                                <?= number_format($openPoints) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Color Code Priority Blocks -->
        <div class="col-12 col-xl-4">
            <div class="card gd-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="ch-title">Gemba Open Points by Color Code</div>
                        <div class="ch-sub">Priority distribution</div>
                    </div>
                    <a href="javascript:void(0);" onclick="exportChartExcel('color_code')" class="export-csv-btn"
                        title="Export CSV Data">
                        <i class="fas fa-file-csv"></i> Export
                    </a>
                </div>
                <div class="card-body py-3">
                    <div class="pc-priority pc-red">
                        <div>
                            <div class="pc-label">Red Count</div>
                            <div class="pc-value"><?= number_format($colorRed) ?></div>
                        </div>
                        <div class="pc-pct"><?= $redPct ?>%</div>
                    </div>
                    <div class="pc-priority pc-yellow">
                        <div>
                            <div class="pc-label">Yellow Count</div>
                            <div class="pc-value"><?= number_format($colorYellow) ?></div>
                        </div>
                        <div class="pc-pct"><?= $yellowPct ?>%</div>
                    </div>
                    <div class="pc-priority pc-black">
                        <div>
                            <div class="pc-label">Black Count</div>
                            <div class="pc-value"><?= number_format($colorBlack) ?></div>
                        </div>
                        <div class="pc-pct"><?= $blackPct ?>%</div>
                    </div>
                    <div class="text-center mt-3" style="font-size:.78rem;font-weight:700;color:#9a9aa0;">
                        Total Open Points: <span style="color:#1a1a2e;"><?= number_format($totalColorOpen) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- NC vs Recommendation Donut -->
        <div class="col-12 col-xl-4">
            <div class="card gd-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="ch-title">NC vs Recommendation</div>
                        <div class="ch-sub">Open Points Ratio analysis — hover for details</div>
                    </div>
                    <a href="javascript:void(0);" onclick="exportChartExcel('nc_recommendation')"
                        class="export-csv-btn" title="Export CSV Data">
                        <i class="fas fa-file-csv"></i> Export
                    </a>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-3">
                    <div class="score-donut-wrap" style="width:185px;height:185px;">
                        <canvas id="ncRecChart"></canvas>
                        <div class="donut-center">
                            <div class="dc-val"><?= number_format($ncRecTotal) ?></div>
                            <div class="dc-lbl">Total</div>
                        </div>
                    </div>
                    <div class="nc-legend mt-2">
                        <div class="nc-legend-item">
                            <div class="nc-legend-dot" style="background:#4361ee;"></div>
                            NC <span class="ms-1 text-muted">(<?= number_format($ncCount) ?>)</span>
                        </div>
                        <div class="nc-legend-item">
                            <div class="nc-legend-dot" style="background:#f72585;"></div>
                            Recommendation <span class="ms-1 text-muted">(<?= number_format($recCount) ?>)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ===================== CHARTS ROW 2 ===================== -->
    <div class="row g-3 mb-4">

        <!-- Region-wise Stacked Bar -->
        <div class="col-12 col-xl-6">
            <div class="card gd-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="ch-title">Region-wise Gemba Count <span
                                style="font-size:.72rem;font-weight:400;color:#9a9aa0;">(Total Audits)</span></div>
                        <div class="ch-sub">
                            Total Audits:
                            <strong style="color:#1a1a2e;">
                                <?php
                                $regionTotal = 0;
                                foreach ($chart_region as $cr) {
                                    $regionTotal += (int) ($cr['red_count'] ?? 0) + (int) ($cr['yellow_count'] ?? 0) + (int) ($cr['black_count'] ?? 0);
                                }
                                echo number_format($regionTotal);
                                ?>
                            </strong>
                        </div>
                    </div>
                    <a href="javascript:void(0);" onclick="exportChartExcel('region_wise')" class="export-csv-btn"
                        title="Export CSV Data">
                        <i class="fas fa-file-csv"></i> Export
                    </a>
                </div>
                <div class="card-body py-2">
                    <?php if (!empty($chart_region)): ?>
                        <div style="position:relative;width:100%;height:290px;">
                            <canvas id="regionChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="no-data"><i class="fas fa-chart-bar fa-2x mb-2 d-block  text-white"></i>No region data
                            available</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Monthly Audit Trend -->
        <div class="col-12 col-xl-6">
            <div class="card gd-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="ch-title">Monthly Audit Trend</div>
                        <div class="ch-sub">Last 6 months — Total, Open, Closed, Overdue</div>
                    </div>
                    <a href="javascript:void(0);" onclick="exportChartExcel('monthly_trend')" class="export-csv-btn"
                        title="Export CSV Data">
                        <i class="fas fa-file-csv"></i> Export
                    </a>
                </div>
                <div class="card-body py-2">
                    <?php if (!empty($chart_monthly_trend)): ?>
                        <div style="position:relative;width:100%;height:290px;">
                            <canvas id="monthlyTrendChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="no-data"><i class="fas fa-chart-line fa-2x mb-2 d-block  text-white"></i>No monthly
                            trend data available</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- ===================== AGEING KPI CARDS ===================== -->
    <!-- <div class="row g-3 mb-4"> -->
        <!-- Points <= 30 Days -->
        <!-- <div class="col-6 col-md-2">
            <div class="card kpi-card kpi-green">
                <div class="card-body p-3" style="gap: 10px;">
                    <div class="kpi-icon-box bg-kpi-green" style="width:40px; height:40px; font-size:1.1rem;">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label" style="font-size:0.65rem;">&le; 30 Days</div>
                        <div class="kpi-value fs-4" style="color:#16a34a;"><?= number_format($summary['age_lte_30']) ?></div>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- Points > 30 Days -->
        <!-- <div class="col-6 col-md-2">
            <div class="card kpi-card kpi-amber">
                <div class="card-body p-3" style="gap: 10px;">
                    <div class="kpi-icon-box bg-kpi-amber" style="width:40px; height:40px; font-size:1.1rem;">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label" style="font-size:0.65rem;">&gt; 30 Days</div>
                        <div class="kpi-value fs-4" style="color:#d97706;"><?= number_format($summary['age_gt_30']) ?></div>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- Points <= 60 Days -->
        <!-- <div class="col-6 col-md-2">
            <div class="card kpi-card kpi-green">
                <div class="card-body p-3" style="gap: 10px;">
                    <div class="kpi-icon-box bg-kpi-green" style="width:40px; height:40px; font-size:1.1rem;">
                        <i class="fas fa-calendar-alt text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label" style="font-size:0.65rem;">&le; 60 Days</div>
                        <div class="kpi-value fs-4" style="color:#16a34a;"><?= number_format($summary['age_lte_60']) ?></div>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- Points > 60 Days -->
        <!-- <div class="col-6 col-md-2">
            <div class="card kpi-card kpi-orange">
                <div class="card-body p-3" style="gap: 10px;">
                    <div class="kpi-icon-box bg-kpi-orange" style="width:40px; height:40px; font-size:1.1rem;">
                        <i class="fas fa-history text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label" style="font-size:0.65rem;">&gt; 60 Days</div>
                        <div class="kpi-value fs-4" style="color:#ea580c;"><?= number_format($summary['age_gt_60']) ?></div>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- Points <= 90 Days -->
        <!-- <div class="col-6 col-md-2">
            <div class="card kpi-card kpi-green">
                <div class="card-body p-3" style="gap: 10px;">
                    <div class="kpi-icon-box bg-kpi-green" style="width:40px; height:40px; font-size:1.1rem;">
                        <i class="fas fa-calendar-plus text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label" style="font-size:0.65rem;">&le; 90 Days</div>
                        <div class="kpi-value fs-4" style="color:#16a34a;"><?= number_format($summary['age_lte_90']) ?></div>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- Points > 90 Days -->
        <!-- <div class="col-6 col-md-2">
            <div class="card kpi-card kpi-purple">
                <div class="card-body p-3" style="gap: 10px;">
                    <div class="kpi-icon-box bg-kpi-purple" style="width:40px; height:40px; font-size:1.1rem;">
                        <i class="fas fa-hourglass-end text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label" style="font-size:0.65rem;">&gt; 90 Days</div>
                        <div class="kpi-value fs-4" style="color:#7c3aed;"><?= number_format($summary['age_gt_90']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <!-- ===================== REPORTS TABLE ===================== -->
    <div class="card gd-tab-card mb-4">
        <div class="card-body p-3">
            <?= $gemba_table ?>
        </div>
    </div>

</div><!-- .gd-wrap -->

<!-- ===================== CHART SCRIPTS ===================== -->
<script>
    document.addEventListener('DOMContentLoaded', function () {

        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.color = '#9a9aa0';

        /* -------------------------------------------------------
           Bar Total Plugin — draws sum label above each stacked bar
        ------------------------------------------------------- */
        const stackedTotalPlugin = {
            id: 'stackedTotal',
            afterDatasetsDraw(chart) {
                if (chart.config.type !== 'bar') return;
                const xScale = chart.scales.x;
                const yScale = chart.scales.y;
                if (!xScale || !yScale) return;

                const { ctx, data } = chart;
                const totals = {};

                data.datasets.forEach((dataset, di) => {
                    const meta = chart.getDatasetMeta(di);
                    if (meta.hidden) return;
                    meta.data.forEach((bar, idx) => {

                        if (!bar || typeof bar.x === 'undefined' || typeof bar.y === 'undefined') {
                            return;
                        }

                        if (!totals[idx]) {
                            totals[idx] = {
                                value: 0,
                                topY: bar.y,
                                centerX: bar.x
                            };
                        }

                        totals[idx].value += Number(dataset.data[idx]) || 0;
                        totals[idx].topY = Math.min(totals[idx].topY, bar.y);
                        totals[idx].centerX = bar.x;
                    });
                });

                ctx.save();
                ctx.font = 'bold 11px Poppins, sans-serif';
                ctx.fillStyle = '#1a1a2e';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';

                for (const idx in totals) {
                    if (totals[idx].value > 0) {
                        ctx.fillText(
                            totals[idx].value,
                            totals[idx].centerX,
                            totals[idx].topY - 4
                        );
                    }
                }
                ctx.restore();
            }
        };

        /* -------------------------------------------------------
           1. Gauge Chart — Open vs Closed (semi-circle donut)
        ------------------------------------------------------- */
        const ctxGauge = document.getElementById('gaugeChart');
        if (ctxGauge) {
            new Chart(ctxGauge, {
                type: 'doughnut',
                data: {
                    labels: ['Closed', 'Open'],
                    datasets: [{
                        data: [<?= (int) $closedPoints ?>, <?= (int) $openPoints ?>],
                        backgroundColor: ['#16a34a', '#dc3545'],
                        borderWidth: 0,
                        borderRadius: 4,
                        cutout: '78%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    rotation: -90,
                    circumference: 180,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                    return ' ' + ctx.label + ': ' + ctx.raw.toLocaleString() + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        /* -------------------------------------------------------
           2. NC vs Recommendation — DONUT with center total
        ------------------------------------------------------- */
        const ctxNcRec = document.getElementById('ncRecChart');
        if (ctxNcRec) {
            new Chart(ctxNcRec, {
                type: 'doughnut',
                data: {
                    labels: ['NC', 'Recommendation'],
                    datasets: [{
                        data: [<?= $ncCount ?>, <?= $recCount ?>],
                        backgroundColor: ['#4361ee', '#f72585'],
                        borderWidth: 2,
                        borderColor: '#fff',
                        cutout: '65%',
                        borderRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                    return ' ' + ctx.label + ': ' + ctx.raw.toLocaleString() + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        /* -------------------------------------------------------
           3. Region-wise Stacked Bar + Total Labels
        ------------------------------------------------------- */
        const ctxRegion = document.getElementById('regionChart');
        if (ctxRegion) {
            <?php
            $regLabels = $regRed = $regYellow = $regBlack = [];
            foreach ($chart_region as $cr) {
                $regLabels[] = $cr['region'];
                $regRed[] = (int) ($cr['red_count'] ?? 0);
                $regYellow[] = (int) ($cr['yellow_count'] ?? 0);
                $regBlack[] = (int) ($cr['black_count'] ?? 0);
            }
            ?>
            new Chart(ctxRegion, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($regLabels) ?>,
                    datasets: [
                        { label: 'Red', data: <?= json_encode($regRed) ?>, backgroundColor: '#ef4444', borderRadius: 2 },
                        { label: 'Yellow', data: <?= json_encode($regYellow) ?>, backgroundColor: '#fbbf24', borderRadius: 2 },
                        { label: 'Black', data: <?= json_encode($regBlack) ?>, backgroundColor: '#374151', borderRadius: 3 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { top: 22 } },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, border: { dash: [4, 4] }, grid: { color: '#f1f5f9' }, beginAtZero: true }
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, font: { size: 11 } } },
                        tooltip: {
                            callbacks: {
                                footer: function (items) {
                                    const total = items.reduce((s, i) => s + i.raw, 0);
                                    return 'Total: ' + total.toLocaleString();
                                }
                            }
                        }
                    }
                },
                plugins: [stackedTotalPlugin]
            });
        }

        /* -------------------------------------------------------
           4. Monthly Audit Trend — Line Chart
        ------------------------------------------------------- */
        const ctxTrend = document.getElementById('monthlyTrendChart');
        if (ctxTrend) {
            <?php
            $mtLabels = $mtTotal = $mtOpen = $mtClosed = $mtOverdue = [];
            foreach ($chart_monthly_trend as $mt) {
                $mtLabels[] = $mt['month_year'];
                $mtTotal[] = (int) $mt['total'];
                $mtOpen[] = (int) $mt['open'];
                $mtClosed[] = (int) $mt['closed'];
                $mtOverdue[] = (int) $mt['overdue'];
            }
            $latestTotal = array_sum($mtTotal);
            $latestOpen = array_sum($mtOpen);
            $latestClosed = array_sum($mtClosed);
            $latestOverdue = array_sum($mtOverdue);
            ?>
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: <?= json_encode($mtLabels) ?>,
                    datasets: [
                        { label: 'Total Points (<?= $latestTotal ?>)', data: <?= json_encode($mtTotal) ?>, borderColor: '#4361ee', backgroundColor: 'rgba(67,97,238,.08)', tension: 0.35, pointRadius: 4, fill: true },
                        { label: 'Open Points (<?= $latestOpen ?>)', data: <?= json_encode($mtOpen) ?>, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,.05)', tension: 0.35, pointRadius: 4 },
                        { label: 'Closed Points (<?= $latestClosed ?>)', data: <?= json_encode($mtClosed) ?>, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.05)', tension: 0.35, pointRadius: 4 },
                        { label: 'Overdue Points (<?= $latestOverdue ?>)', data: <?= json_encode($mtOverdue) ?>, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.05)', tension: 0.35, pointRadius: 4 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false } },
                        y: { border: { dash: [4, 4] }, grid: { color: '#f1f5f9' }, beginAtZero: true }
                    },
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, padding: 14, font: { size: 11 } } }
                    }
                }
            });
        }

        /* -------------------------------------------------------
           Dependent Filter Dropdowns
        ------------------------------------------------------- */
        var isUpdatingFilters = false;
        var filterDebounceTimer = null;

        $('#gd_region, #gd_site_category').on('change', function(e) {
            if (isUpdatingFilters) return;
            
            var triggerId = $(this).attr('id');
            var selectedRegions = $('#gd_region').val() || [];
            selectedRegions = selectedRegions.filter(v => v !== 'selectAll' && v !== '');
            
            var selectedCategories = $('#gd_site_category').val() || [];
            selectedCategories = selectedCategories.filter(v => v !== 'selectAll' && v !== '');
            
            clearTimeout(filterDebounceTimer);
            filterDebounceTimer = setTimeout(function() {
                isUpdatingFilters = true;
                
                var requests = [];
                
                if (triggerId === 'gd_region') {
                    requests.push(
                        $.ajax({
                            url: '<?= base_url('Customer/Gemba_Dashboard/get_dependent_dropdowns') ?>',
                            type: 'POST',
                            data: { type: 'site_category', regions: selectedRegions },
                            success: function(res) {
                                var currentSelected = $('#gd_site_category').val() || [];
                                $('#gd_site_category').empty();
                                $.each(res, function(i, item) {
                                    if(item.site_category) {
                                        var selected = currentSelected.includes(item.site_category) ? 'selected' : '';
                                        $('#gd_site_category').append('<option value="' + item.site_category + '" ' + selected + '>' + item.site_category + '</option>');
                                    }
                                });
                            }
                        })
                    );
                }
                
                requests.push(
                    $.ajax({
                        url: '<?= base_url('Customer/Gemba_Dashboard/get_dependent_dropdowns') ?>',
                        type: 'POST',
                        data: { type: 'site_name', regions: selectedRegions, site_categories: selectedCategories },
                        success: function(res) {
                            var currentSelected = $('#gd_site_name').val() || [];
                            $('#gd_site_name').empty();
                            $.each(res, function(i, item) {
                                if(item.site_name) {
                                    var selected = currentSelected.includes(item.site_name) ? 'selected' : '';
                                    $('#gd_site_name').append('<option value="' + item.site_name + '" ' + selected + '>' + item.site_name + '</option>');
                                }
                            });
                        }
                    })
                );
                
                $.when.apply($, requests).done(function() {
                    // Mark old as destroyed so it reinitializes
                    if (triggerId === 'gd_region') {
                        $('#gd_site_category').data('custom-multiselect-initialized', false);
                    }
                    $('#gd_site_name').data('custom-multiselect-initialized', false);
                    
                    if (typeof window.reinitMultiSelect === 'function') {
                        window.reinitMultiSelect();
                    }
                    
                    setTimeout(function() { isUpdatingFilters = false; }, 100);
                });
            }, 300);
        });

        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            // Initialization handled by custom-multiselect.js globally
        }
    });

    // Function to download chart as image
    function downloadChart(canvasId, filename) {
        const canvas = document.getElementById(canvasId);
        if (canvas) {
            const link = document.createElement('a');
            link.download = filename + '.png';
            link.href = canvas.toDataURL('image/png', 1.0);
            link.click();
        }
    }

    function exportChartExcel(chartType) {
        let url = "<?= base_url('Customer/Gemba_Dashboard/exportChartExcel') ?>";

        let form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

        // Clone current filters from main filter form (if exists)
        let filterForm = document.getElementById('filterForm');
        if (filterForm) {
            let formData = new FormData(filterForm);
            for (let [name, value] of formData.entries()) {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            }
        }

        let chart = document.createElement('input');
        chart.type = 'hidden';
        chart.name = 'chart_type';
        chart.value = chartType;

        form.appendChild(chart);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
</script>
<?= $this->endSection() ?>