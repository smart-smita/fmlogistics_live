<?php
$this->extend("Layout/base_admin");
?>

<?php $this->section("breadcrumb_title_li"); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="<?= base_url('js/select2.min.css') ?>">
<style>
    /* Select2 inside HSE filter — match Bootstrap sm control height and responsive */
    .hse-filter-card .select2-container {
        width: 100% !important;
    }
    .hse-filter-card .select2-container .select2-selection--multiple {
        min-height: 38px;
        max-height: 75px; /* Prevent uncontrolled vertical growth */
        overflow-y: auto;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        font-size: 0.95rem;
        padding: 2px 4px;
        scrollbar-width: thin; /* Firefox */
    }
    /* Webkit scrollbar for select2 */
    .hse-filter-card .select2-container .select2-selection--multiple::-webkit-scrollbar {
        width: 4px;
    }
    .hse-filter-card .select2-container .select2-selection--multiple::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 4px;
    }


    .select2-all-clear { font-size: 0.75rem; }
    
    /* Dropdown list items wrapping */
    .select2-container--default .select2-results__option {
        word-wrap: break-word;
        white-space: normal;
        font-size: 0.875rem;
    }
</style>
<style>
    /* ============================================================
       HSE AUDIT DASHBOARD  –  Responsive Redesign
    ============================================================ */

    /* --- Inherit theme font (Poppins) — exclude icon elements --- */
    .hse-wrap {
        font-family: 'Poppins', sans-serif;
    }

    /* Apply Poppins to text nodes only, never to icon elements */
    .hse-wrap *:not(i):not([class*="fa"]):not([class*="fas"]):not([class*="far"]):not([class*="fab"]):not([class*="fal"]) {
        font-family: 'Poppins', sans-serif;
    }

    /* DataTables controls inherit font too — same exclusion */
    div[id$="_wrapper"] *:not(i):not([class*="fa"]):not([class*="fas"]):not([class*="far"]):not([class*="fab"]) {
        font-family: 'Poppins', sans-serif;
    }

    /* Ensure Font Awesome icons always keep their icon font */
    .hse-wrap i[class*="fa"],
    div[id$="_wrapper"] i[class*="fa"] {
        font-family: "Font Awesome 5 Free", "Font Awesome 5 Brands", "FontAwesome" !important;
    }

    /* --- Base reset / spacing --- */
    .hse-wrap {
        padding: 0 4px;
    }

    /* ---- Page Header ---- */
    .hse-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 1.25rem;
    }

    .hse-page-header h2 {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hse-page-header p {
        font-size: 0.95rem;
        color: #495057;
        margin: 2px 0 0;
    }

    /* ---- Filter card ---- */
    .hse-filter-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 1px 8px rgba(0, 0, 0, .07);
    }

    .hse-filter-card .form-select,
    .hse-filter-card .form-control {
        border-radius: 8px;
        font-size: 1rem;
    }

    .hse-filter-card .form-label {
        font-size: 1rem;
    }

    /* ---- Export Excel Button ---- */
    .export-excel-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 5px 10px;
        font-size: 0.95rem;
        font-weight: 600;
        color: #15803d;
        background-color: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 6px;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }

    .export-excel-btn i {
        font-size: 0.95rem;
    }

    .export-excel-btn:hover {
        background-color: #dcfce7;
        color: #166534;
        border-color: #86efac;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(22, 163, 74, 0.1);
    }

    /* ---- KPI cards — reference style ---- */
    .kpi-card {
        border-radius: 14px;
        border: 1px solid #ced4da;
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

    /* text block */
    .kpi-text {
        flex: 1;
        min-width: 0;
    }

    .kpi-label {
        font-size: .90rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #495057;
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
        font-size: 0.95rem;
        color: #495057;
        margin-top: 2px;
        word-wrap: break-word;
        white-space: normal;
    }

    .kpi-sub .sub-hi {
        font-weight: 700;
        color: #4b5563;
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
    .hse-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .07);
        height: 100%;
    }

    .hse-card .card-header {
        background: #fff;
        border-bottom: 1px solid #f1f1f4;
        padding: 1.15rem 1.35rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }

    .hse-card .card-body {
        padding: 1.35rem;
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
        color: #343a40;
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
        background: #16a34a; /* HSE green */
        border-color: #16a34a;
        box-shadow: 0 -2px 10px rgba(22, 163, 74, 0.15);
    }

    .sec-tabs .nav-link:hover:not(.active) {
        background: #f1f5f9;
        color: #334155;
    }

    /* ---- DataTables Overrides ---- */
    .hse-table th {
        background: #f8fafc !important;
        font-size: 0.95rem;
        font-weight: 800; text-transform: uppercase;
        color: #475569;
        white-space: nowrap;
        text-transform: uppercase;
        border-bottom: 2px solid #e2e8f0;
    }

    .hse-card .ch-title {
        font-size: 1rem;
        font-weight: 800; font-size: 1.1rem;
        color: #1a1a2e;
    }

    .hse-card .ch-sub {
        font-size: 0.95rem;
        color: #495057;
        margin-top: 1px;
    }

    .view-all-link {
        font-size: 1rem;
        font-weight: 600;
        color: #0d6efd;
        text-decoration: none;
        white-space: nowrap;
    }

    .view-all-link:hover {
        text-decoration: underline;
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
        color: #495057;
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

    /* ---- CAPA donut ---- */
    .capa-donut-wrap {
        position: relative;
        width: 170px;
        height: 170px;
        flex-shrink: 0;
    }

    .capa-donut-wrap canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .capa-legend-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        margin-top: 10px;
    }

    .capa-legend-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 54px;
    }

    .capa-legend-item .cl-dot {
        width: 14px;
        height: 14px;
        border-radius: 4px;
        margin: 0 auto 3px;
    }

    .capa-legend-item .cl-lbl {
        font-size: 1rem;
        font-weight: 700;
    }

    .capa-legend-item .cl-cnt {
        font-size: 1rem;
        color: #6c757d;
    }

    /* ---- DataTables custom styling — all 3 tab tables + open points table ---- */
    .hse-dt-wrapper .dataTables_length select,
    .hse-dt-wrapper .dataTables_filter input,
    div[id$="_wrapper"] .dataTables_length select,
    div[id$="_wrapper"] .dataTables_filter input {
        border: 1.5px solid #e0e4ef;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 0.95rem;
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
        padding-top: 6px;
    }

    div[id$="_wrapper"] .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        font-size: 1rem !important;
        font-weight: 600 !important;
        padding: 4px 11px !important;
        margin: 0 2px !important;
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

    /* Freeze first column look */
    div[id$="_wrapper"] .dataTables_scrollHead {
        overflow: hidden !important;
    }

    div[id$="_wrapper"] .dataTables_scrollHeadInner {
        box-sizing: border-box !important;
    }

    /* tab badge count */
    #mainTabs .badge {
        vertical-align: middle;
    }

    .hse-table thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-size: 1rem;
        white-space: nowrap;
        font-weight: 700;
    }

    /* Category Wise Aging — category column headers must stay horizontal */
    #agingTable thead th {
        white-space: normal !important;
        word-break: break-word !important;
        writing-mode: horizontal-tb !important;
        text-orientation: mixed !important;
        vertical-align: middle !important;
        max-width: 140px;
    }

    .hse-table tbody td {
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
        font-size: 0.95rem;
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
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .u-title {
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.3;
    }

    .u-meta {
        font-size: 0.95rem;
        color: #495057;
    }

    .u-badge {
        font-size: 0.95rem;
        padding: 2px 8px;
        border-radius: 20px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* scroll container */
    #upcomingScroller {
        scrollbar-width: thin;
        scrollbar-color: #c5cbe3 #f8f9fc;
    }

    #upcomingScroller::-webkit-scrollbar {
        width: 5px;
    }

    #upcomingScroller::-webkit-scrollbar-track {
        background: #f8f9fc;
        border-radius: 4px;
    }

    #upcomingScroller::-webkit-scrollbar-thumb {
        background: #c5cbe3;
        border-radius: 4px;
    }

    #upcomingScroller::-webkit-scrollbar-thumb:hover {
        background: #4361ee;
    }

    /* ---- Quick Actions ---- */
    .qa-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 21px 12px;
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
        font-size: 0.95rem;
        color: #6c757d;
    }

    /* ---- No-data ---- */
    .no-data {
        text-align: center;
        padding: 36px 20px;
        color: #bbb;
        font-size: .92rem;
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
        .hse-page-header h2 {
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
            font-size: 0.95rem;
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

        .kpi-spark {
            height: 28px;
        }
    }

    /* ---- Responsive DataTables Pagination Fix ---- */
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

    /* ---- Open Points Scroller ---- */
    #openPointsScroller {
        max-height: 360px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 8px;
    }

    #openPointsScroller::-webkit-scrollbar {
        width: 6px;
    }

    #openPointsScroller::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    #openPointsScroller::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    #openPointsScroller::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<?php
helper('designation_acl');
echo showACLMessage('info');

$totalOpenPts = $total_open_points ?? (($overall_open_nc ?? 0) + ($overall_working_nc ?? 0) + ($overall_review_nc ?? 0));
$criticalFindings = $critical_findings_count ?? 0;
$upcomingCount = count($upcoming_audits ?? []);
$agingOver30 = $aging_over_30_count ?? 0;
?>

<div class="hse-wrap">

    <!-- ===================== PAGE HEADER ===================== -->
    <div class="hse-page-header">
        <div>
            <h2><i class="fas fa-shield-alt text-primary fs-1"></i> HSE Audit Dashboard</h2>
            <p>Overview of Health, Safety &amp; Environment audits across locations and accounts</p>
        </div>

    </div>

    <!-- ===================== QUICK ACTIONS ===================== -->
    <div class="card hse-filter-card mb-4" id="hseQuickActionsCard">
        <div class="card-header bg-white border-bottom-0 pb-0 pt-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-bolt text-primary me-2"></i>Quick Actions</h6>

        </div>
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/Audit_template/template_type_filter/HSE') ?>"
                        class="qa-item border-0 h-100" style="background: #f4f7fa;">
                        <div class="qa-icon" style="background:#e0e7ff;">
                            <i class="fas fa-shield-alt text-primary"></i>
                        </div>
                        <div class="flex-fill">
                            <div class="qa-title text-dark">New HSE Audit</div>
                            <div class="qa-sub">Create a new HSE audit</div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/Reaudit/index/HSE') ?>" class="qa-item border-0 h-100"
                        style="background: #fffbeb;">
                        <div class="qa-icon" style="background:#fef9c3;">
                            <i class="fas fa-sync-alt text-warning"></i>
                        </div>
                        <div class="flex-fill">
                            <div class="qa-title text-dark">HSE Old Audit</div>
                            <div class="qa-sub">View old audits and initiate re-audit</div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/Hse_audit') ?>" class="qa-item border-0 h-100"
                        style="background: #f0fdf4;">
                        <div class="qa-icon" style="background:#dcfce7;">
                            <i class="fas fa-file-invoice text-success"></i>
                        </div>
                        <div class="flex-fill">
                            <div class="qa-title text-dark">HSE Performed Audit</div>
                            <div class="qa-sub">Performed HSE audit</div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="<?= base_url('Masters/Hse_nc_tracker') ?>" class="qa-item border-0 h-100"
                        style="background: #fef2f2;">
                        <div class="qa-icon" style="background:#fee2e2;">
                            <i class="fas fa-list-ul text-danger"></i>
                        </div>
                        <div class="flex-fill">
                            <div class="qa-title text-dark">HSE NC Tracker</div>
                            <div class="qa-sub">Track NC performance and status</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== FILTER BAR ===================== -->
    <div class="card hse-filter-card mb-4" id="hseFilterCard">
        <div class="card-body py-3">
            <form method="GET" action="<?= base_url('Customer/Audit_Dashboard_HSE/HSE_Audit'); ?>"
                class="row g-2 align-items-end" id="hseFilterForm">

                <!-- Year -->
                <div class="col-6 col-sm-4 col-md-2">
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size:1rem;">Year</label>
                    <select class="form-select form-select-sm hse-select2" id="filter_year" name="filter_year[]" multiple="multiple"
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
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size: 1rem;">Region</label>
                    <?php if (isset($is_cluster_manager) && $is_cluster_manager): ?>
                        <?php
                        $val = is_array($locked_region) ? implode(',', $locked_region) : (isset($locked_region) ? $locked_region : '');
                        $lbl = is_array($locked_region) ? implode(', ', $locked_region) : (isset($locked_region) ? $locked_region : '');
                        ?>
                        <input type="text" class="form-control form-control-sm" value="<?= esc($lbl) ?>" readonly>
                        <input type="hidden" name="region[]" value="<?= esc($val) ?>">
                    <?php elseif (isset($is_account_manager) && $is_account_manager): ?>
                        <select class="form-select form-select-sm hse-select2" id="region" name="region[]" multiple="multiple" data-placeholder="All Regions">
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
                        <select class="form-select form-select-sm hse-select2" id="region" name="region[]" multiple="multiple" data-placeholder="All Regions">
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
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size: 1rem;">Cluster</label>
                    <?php if (isset($is_cluster_manager) && $is_cluster_manager): ?>
                        <?php
                        $valC = is_array($locked_cluster) ? implode(',', $locked_cluster) : (isset($locked_cluster) ? $locked_cluster : '');
                        $lblC = is_array($locked_cluster) ? implode(', ', $locked_cluster) : (isset($locked_cluster) ? $locked_cluster : '');
                        ?>
                        <input type="text" class="form-control form-control-sm" value="<?= esc($lblC) ?>" readonly>
                        <input type="hidden" name="cluster_name[]" value="<?= esc($valC) ?>">
                    <?php elseif (isset($is_account_manager) && $is_account_manager): ?>
                        <select class="form-select form-select-sm hse-select2" id="cluster_name" name="cluster_name[]" multiple="multiple" data-placeholder="All Clusters">
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
                        <select class="form-select form-select-sm hse-select2" id="cluster_name" name="cluster_name[]" multiple="multiple" data-placeholder="All Clusters">
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
                    <select class="form-select form-select-sm hse-select2" id="location_name" name="location_name[]" multiple="multiple" data-placeholder="All Accounts">
                        <?php
                        $locationList = isset($location) ? $location : [];
                        $selLocations = is_array($selected_location ?? null) ? $selected_location : (($selected_location ?? '') !== '' ? [$selected_location] : []);
                        foreach ($locationList as $row):
                            $sel = in_array($row['location_name'], $selLocations) ? 'selected' : '';
                            ?>
                            <option value="<?= esc($row['location_name']) ?>" <?= $sel ?>><?= esc($row['location_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Month -->
                <div class="col-6 col-sm-4 col-md-2">
                    <label class="form-label fw-semibold text-muted mb-1" style="font-size: 1rem;">Month</label>
                    <select class="form-select form-select-sm hse-select2" id="month" name="month[]" multiple="multiple" data-placeholder="All Months">
                        <?php
                        $selMonths = is_array($selected_month ?? null) ? $selected_month : (($selected_month ?? '') !== '' ? [$selected_month] : []);
                        foreach ($months as $mKey => $mLabel):
                            $isSel = in_array($mKey, $selMonths) ? 'selected' : '';
                            ?>
                            <option value="<?= $mKey ?>" <?= $isSel ?>><?= $mLabel ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-6 col-sm-4 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill fw-semibold">
                        <i class="fa fa-filter me-1"></i> Show
                    </button>
                    <a href="<?= base_url('Customer/Audit_Dashboard_HSE/HSE_Audit') ?>"
                        class="btn btn-outline-secondary btn-sm flex-fill fw-semibold">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- ===================== KPI CARDS (4 cards) ===================== -->
    <style>
        @media (min-width: 768px) {
            .col-md-kpi {
                flex: 0 0 25%;
                max-width: 25%;
            }
        }
    </style>
    <div class="row g-3 mb-4">

        <!-- 1. HSE Score -->
        <div class="col-6 col-md-kpi">
            <div class="card kpi-card kpi-blue">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-blue">
                        <i class="fas fa-shield-alt text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">HSE Score</div>
                        <div class="kpi-value"><?= number_format((float) ($overall_hse_score ?? 0), 2) ?>%</div>
                        <div class="kpi-sub">Overall Score</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Open Points -->
        <div class="col-6 col-md-kpi">
            <div class="card kpi-card kpi-amber">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-amber">
                        <i class="fas fa-exclamation-circle  text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Open Points</div>
                        <div class="kpi-value"><?= $totalOpenPts ?></div>
                        <div class="kpi-sub">
                            Active: <span class="sub-hi"><?= $overall_open_nc ?? 0 ?></span>
                            &nbsp;|&nbsp; Working: <?= $overall_working_nc ?? 0 ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Closed NC Tracker -->
        <div class="col-6 col-md-kpi">
            <div class="card kpi-card kpi-green">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-green">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Closed NC Tracker</div>
                        <div class="kpi-value"><?= $overall_closed_nc ?? 0 ?></div>
                        <div class="kpi-sub">
                            Resolved: <span class="sub-hi"><?= $overall_closed_nc ?? 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Upcoming Audits -->
        <div class="col-6 col-md-kpi">
            <div class="card kpi-card kpi-indigo">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-indigo">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Upcoming Audits</div>
                        <div class="kpi-value"><?= $upcomingCount ?></div>
                        <div class="kpi-sub">
                            Scheduled: <span class="sub-hi"><?= $upcomingCount ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ===================== CHARTS ROW ===================== -->
    <div class="row g-3 mb-4">

        <!-- HSE Score by Region – Donut + Legend -->
        <div class="col-12 col-xl-6">
            <div class="card hse-card">
                <div class="card-header">
                    <div>
                        <div class="ch-title">HSE Score by Region
                            <span class="text-muted fw-normal" style="font-size: 0.95rem;">(Top 10)</span>
                        </div>
                        <div class="ch-sub">Average audit score per region</div>
                    </div>
                    <a href="javascript:void(0);" onclick="exportHseChartExcel('region_wise')" class="export-excel-btn"
                        title="Export Excel Data">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($pie_chart)): ?>
                        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-center">
                            <div class="score-donut-wrap">
                                <canvas id="scoreDonutChart"></canvas>
                                <div class="donut-center">
                                    <div class="dc-val"><?= number_format((float) ($overall_hse_score ?? 0), 2) ?>%</div>
                                    <div class="dc-lbl">Total</div>
                                </div>
                            </div>
                            <div style="flex:1; min-width:150px; max-width:340px;">
                                <ul class="score-legend">
                                    <?php
                                    $dotPalette = [
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
                                    $topRegions = array_slice($pie_chart, 0, 10);
                                    foreach ($topRegions as $si => $sl):
                                        $dc = $dotPalette[$si % count($dotPalette)];
                                        ?>
                                        <li>
                                            <span class="dot" style="background:<?= $dc ?>;"></span>
                                            <span class="lname" title="<?= esc($sl['region']) ?>">
                                                <?= esc($sl['region']) ?>
                                            </span>
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

        <!-- CAPA Color Distribution -->
        <div class="col-12 col-md-6 col-xl-6">
            <div class="card hse-card">
                <div class="card-header">
                    <div>
                        <div class="ch-title">CAPA Color Distribution</div>
                        <div class="ch-sub">Open points by risk color code</div>
                    </div>
                    <a href="javascript:void(0);" onclick="exportHseChartExcel('open_points')" class="export-excel-btn"
                        title="Export Excel Data">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-3">
                    <?php if (!empty($color_code_open_points)): ?>
                        <div class="capa-donut-wrap">
                            <canvas id="colorCodePieChart"></canvas>
                        </div>
                        <?php
                        $capaClrMap = [
                            'Red' => '#dc3545',
                            'Black' => '#212529',
                            'Yellow' => '#ffc107',
                            'Green' => '#198754',
                            'Orange' => '#fd7e14',
                            'Blue' => '#0d6efd'
                        ];
                        $totalCapa = array_sum(array_column($color_code_open_points, 'total_count'));
                        ?>
                        <div class="capa-legend-row">
                            <?php foreach ($color_code_open_points as $cp):
                                $cc = $cp['color_code'];
                                $cnt = (int) $cp['total_count'];
                                $pct = $totalCapa > 0 ? round($cnt / $totalCapa * 100, 1) : 0;
                                $clr = $capaClrMap[$cc] ?? '#6c757d';
                                ?>
                                <div class="capa-legend-item">
                                    <div class="cl-dot" style="background:<?= $clr ?>;"></div>
                                    <div class="cl-lbl">
                                        <?= esc($cc) ?>
                                    </div>
                                    <div class="cl-cnt">
                                        <?= $cnt ?> (
                                        <?= $pct ?>%)
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data w-100">No CAPA Color Data Found</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- ===================== CAPA Color + Open Points Summary ===================== -->
    <div class="row g-3 mb-4" id="sectionOpenPts">
        <!-- HSE Open Points by Location – Horizontal Bar (All Entries) -->
        <div class="col-12 col-xl-8">
            <div class="card hse-card">
                <div class="card-header">
                    <div>
                        <div class="ch-title">HSE Open Points by Location</div>
                        <div class="ch-sub">All locations with active open NC points</div>
                    </div>
                    <a href="javascript:void(0);" onclick="exportHseChartExcel('open_points')" class="export-excel-btn"
                        title="Export Excel Data">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                </div>
                <div class="card-body p-2 p-sm-3">
                    <?php if (!empty($total_HSE_score_openpoints)): ?>
                        <div class="bar-chart-scroller" id="openPointsScroller">
                            <div class="bar-chart-wrap" id="barChartWrap" style="position: relative; width: 100%;">
                                <canvas id="openPointsBarChart"></canvas>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-data">No Open Points Data Found</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- NC vs Recommendation -->
        <div class="col-12 col-xl-4">
            <div class="card hse-card">
                <div class="card-header">
                    <div>
                        <div class="ch-title">NC vs Recommendation</div>
                        <div class="ch-sub">Ratio analysis — hover for details</div>
                    </div>
                    <a href="javascript:void(0);" onclick="exportHseChartExcel('nc_recommendation')"
                        class="export-excel-btn" title="Export Excel Data">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-3">
                    <div class="capa-donut-wrap">
                        <canvas id="ncRecChart"></canvas>
                        <?php
                        $ncRecTotal = (int) ($nc_rec_chart['nc_count'] ?? 0) + (int) ($nc_rec_chart['rd_count'] ?? 0);
                        ?>
                        <div class="donut-center" style="top: auto; bottom: 10px; transform: translateX(-50%);">
                            <div class="dc-val"><?= number_format($ncRecTotal) ?></div>
                            <div class="dc-lbl">Total</div>
                        </div>
                    </div>
                    <div class="capa-legend-row mt-3">
                        <div class="capa-legend-item">
                            <div class="cl-dot" style="background:#4361ee;"></div>
                            <div class="cl-lbl">NC</div>
                            <div class="cl-cnt"><?= number_format($nc_rec_chart['nc_count'] ?? 0) ?></div>
                        </div>
                        <div class="capa-legend-item">
                            <div class="cl-dot" style="background:#f72585;"></div>
                            <div class="cl-lbl">Recommendation</div>
                            <div class="cl-cnt"><?= number_format($nc_rec_chart['rd_count'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Open Points Summary Table – DataTables with search/pagination -->
        <div class="col-12 col-md-7 col-xl-12">
            <div class="card hse-card">
                <div class="card-header">
                    <div>
                        <div class="ch-title">
                            <?= (!empty($is_higher_authority) && $is_higher_authority)
                                ? 'Open Points Summary Report'
                                : 'Open Points Summary' ?>
                        </div>
                        <div class="ch-sub">Location-wise NC status breakdown</div>
                    </div>
                    <!-- <button class="btn btn-success btn-sm rounded-pill px-3 me-2"
                        onclick="ExportToExcel('xlsx','OpenPoints_Summary.xlsx',false,'openPointsTable')">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button> -->
                    <button class="btn btn-info btn-sm rounded-pill px-3"
                        onclick="ExportToCSV('OpenPoints_Summary.csv','openPointsTable')">
                        <i class="fas fa-file-csv me-1"></i> CSV
                    </button>
                </div>
                <div class="card-body">

                    <!-- Status pills -->
                    <div class="stat-pills">
                        <div class="stat-pill" style="background:#fff1f2;">
                            <span class="sp-val text-danger"><?= $overall_open_nc ?? 0 ?></span>
                            <span class="sp-lbl text-danger">Total Open</span>
                        </div>
                        <div class="stat-pill" style="background:#fffbeb;">
                            <span class="sp-val text-warning"><?= $overall_working_nc ?? 0 ?></span>
                            <span class="sp-lbl text-warning">Working</span>
                        </div>
                        <div class="stat-pill" style="background:#eff6ff;">
                            <span class="sp-val text-info"><?= $overall_review_nc ?? 0 ?></span>
                            <span class="sp-lbl text-info">Under Review</span>
                        </div>
                        <div class="stat-pill" style="background:#f0fdf4;">
                            <span class="sp-val text-success"><?= $overall_closed_nc ?? 0 ?></span>
                            <span class="sp-lbl text-success">Closed</span>
                        </div>
                    </div>

                    <div class="tbl-scroll-x">
                        <table class="table table-bordered table-hover table-sm hse-table" id="openPointsTable"
                            style="width:100%;">
                            <thead>
                                <tr class="table-light">
                                    <th>Location</th>
                                    <th>Region</th>
                                    <th>Cluster</th>
                                    <th class="text-center">Open</th>
                                    <th class="text-center">Working</th>
                                    <th class="text-center">CM Review</th>
                                    <th class="text-center">Auditor Review</th>
                                    <th class="text-center">Closed</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($total_HSE_score_openpoints)):
                                    foreach ($total_HSE_score_openpoints as $row):
                                        $isHA = !empty($is_higher_authority) && $is_higher_authority;
                                        $trAttr = $isHA ? '' :
                                            'class="clickable-row" onclick="drillDown(\'' .
                                            esc($row['location'], 'js') . '\',\'' .
                                            esc($row['region'], 'js') . '\',\'' .
                                            esc($row['cluster_name'], 'js') . '\')"';
                                        ?>
                                        <tr <?= $trAttr ?>>
                                            <td class="<?= $isHA ? 'fw-semibold' : 'text-primary fw-semibold' ?>">
                                                <?= esc($row['location']) ?>
                                            </td>
                                            <td><?= esc($row['region']) ?></td>
                                            <td><?= esc($row['cluster_name']) ?></td>
                                            <td class="text-center text-danger fw-bold"><?= (int) $row['open_pts'] ?></td>
                                            <td class="text-center text-warning fw-bold"><?= (int) $row['working_pts'] ?></td>
                                            <td class="text-center text-info fw-bold"><?= (int) $row['review_cm'] ?></td>
                                            <td class="text-center text-primary fw-bold"><?= (int) $row['review_auditor'] ?>
                                            </td>
                                            <td class="text-center text-success fw-bold"><?= (int) $row['closed_pts'] ?></td>
                                            <td class="text-center">
                                                <?php if (!$isHA): ?>
                                                    <button class="btn btn-sm btn-outline-primary py-0 px-2" title="View" onclick="event.stopPropagation();drillDown(
                                        '<?= esc($row['location'], 'js') ?>',
                                        '<?= esc($row['region'], 'js') ?>',
                                        '<?= esc($row['cluster_name'], 'js') ?>')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                <?php else: ?>—<?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">No open points data found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($total_HSE_score_openpoints)):
                                $tO = array_sum(array_column($total_HSE_score_openpoints, 'open_pts'));
                                $tW = array_sum(array_column($total_HSE_score_openpoints, 'working_pts'));
                                $tCM = array_sum(array_column($total_HSE_score_openpoints, 'review_cm'));
                                $tAud = array_sum(array_column($total_HSE_score_openpoints, 'review_auditor'));
                                $tCl = array_sum(array_column($total_HSE_score_openpoints, 'closed_pts'));
                                ?>
                                <!-- <tfoot>
                            <tr class="table-dark fw-bold">
                                <td colspan="3">Total</td>
                                <td class="text-center"><?= $tO ?></td>
                                <td class="text-center"><?= $tW ?></td>
                                <td class="text-center"><?= $tCM ?></td>
                                <td class="text-center"><?= $tAud ?></td>
                                <td class="text-center"><?= $tCl ?></td>
                                <td></td>
                            </tr>
                        </tfoot> -->
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ===================== TABBED REPORTS ===================== -->
    <div class="card hse-card mb-4" style="height:auto;">
        <div class="card-header align-items-start flex-column flex-sm-row gap-2">
            <ul class="nav sec-tabs flex-nowrap overflow-auto" id="mainTabs" role="tablist" style="max-width:100%;">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-month" type="button"
                        id="tab-month-btn">
                        Month Summary
                        <?php if (!empty($locData) || !empty($month_summary)): ?>
                            <span class="badge bg-primary ms-1" style="font-size: 0.95rem;">
                                <?= count(array_unique(array_column($month_summary ?? [], 'location'))) ?>
                            </span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" style="padding-left: 10px;">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-report" type="button"
                        id="tab-report-btn">
                        Gemba Report
                        <?php if (!empty($open_point_report)): ?>
                            <span class="badge bg-warning text-dark ms-1" style="font-size: 0.95rem;">
                                <?= count($open_point_report) ?>
                            </span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" style="padding-left: 10px;">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-aging" type="button"
                        id="tab-aging-btn">
                        Aging Report
                        <?php if (!empty($aging_report)): ?>
                            <span class="badge bg-danger ms-1" style="font-size: 0.95rem;">
                                <?= count($aging_report) ?>
                            </span>
                        <?php endif; ?>
                    </button>
                </li>
            </ul>
            <div id="tabExportBtn" class="ms-sm-auto flex-shrink-0">
                <!-- <button class="btn btn-success btn-sm rounded-pill px-3 me-2"
                    onclick="ExportToExcel('xlsx','Month_Summary.xlsx',false,'monthSummaryTable')">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </button> -->
                <button class="btn btn-info btn-sm rounded-pill px-3"
                    onclick="ExportToCSV('Month_Summary.csv','monthSummaryTable')">
                    <i class="fas fa-file-csv me-1"></i> CSV
                </button>
            </div>
        </div>

        <div class="card-body p-2 p-md-3">
            <div class="tab-content">

                <!-- ============ TAB 1: Month Summary ============ -->
                <div class="tab-pane fade show active" id="tab-month" role="tabpanel">
                    <div class="tbl-scroll-x">
                        <table class="table table-bordered table-hover table-sm hse-table w-100" id="monthSummaryTable"
                            style="width:100%;">
                        <thead>
                            <tr class="table-light">
                                <th style="min-width:180px;">Location</th>
                                <th style="min-width:90px;">Region</th>
                                <th style="min-width:110px;">Cluster</th>
                                <?php
                                // Filter months to only selected ones (if any)
                                $filteredMonths = [];
                                foreach ($months as $mNum => $mLabel) {
                                    if (empty($selectedMonthNums) || in_array($mNum, $selectedMonthNums)) {
                                        $filteredMonths[$mNum] = $mLabel;
                                    }
                                }
                                foreach ($filteredMonths as $mNum => $mLabel): ?>
                                    <th class="text-center" style="min-width:60px;"><?= substr($mLabel, 0, 3) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $locData = [];
                            foreach ($month_summary as $ms) {
                                $locData[$ms['location']]['region'] = $ms['region'];
                                $locData[$ms['location']]['cluster'] = $ms['cluster_name'];
                                $locData[$ms['location']]['scores'][$ms['month_num']] = $ms['score'];
                            }
                            if (!empty($locData)):
                                foreach ($locData as $loc => $d):
                                    ?>
                                    <tr>
                                        <td class="fw-semibold"><?= esc($loc) ?></td>
                                        <td><?= esc($d['region']) ?></td>
                                        <td><?= esc($d['cluster']) ?></td>
                                        <?php foreach ($filteredMonths as $mNum => $mLabel):
                                            $sc = $d['scores'][$mNum] ?? null;
                                            $cls = '';
                                            if ($sc !== null) {
                                                $cls = $sc >= 85 ? 'text-success fw-semibold'
                                                    : ($sc < 70 ? 'text-danger fw-semibold'
                                                        : 'text-warning fw-semibold');
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
                                    <td colspan="<?= 3 + count($filteredMonths) ?>" class="text-center text-muted py-4">No month
                                        summary data found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- ============ TAB 2: HSE Report ============ -->
                <div class="tab-pane fade" id="tab-report" role="tabpanel">
                    <div class="tbl-scroll-x">
                        <table class="table table-bordered table-hover table-sm hse-table w-100" id="openPointsReportTable"
                            style="width:100%;">
                        <thead>
                            <tr class="table-light">
                                <th style="min-width:160px;">Location</th>
                                <th style="min-width:80px;">Region</th>
                                <th style="min-width:100px;">Cluster</th>
                                <th style="min-width:80px;">Month</th>
                                <th style="min-width:100px;">Audit Date</th>
                                <th style="min-width:110px;">Category</th>
                                <th style="min-width:200px;">Question</th>
                                <th style="min-width:180px;">Description</th>
                                <th style="min-width:180px;">Findings</th>
                                <th style="min-width:180px;">Risk</th>
                                <th style="min-width:200px;">Actions</th>
                                <th style="min-width:130px;">Action Category</th>
                                <th style="min-width:80px;">UA-UC</th>
                                <th style="min-width:110px;">Risk Severity</th>
                                <th style="min-width:120px;">Risk Probability</th>
                                <th style="min-width:100px;">Color Code</th>
                                <th style="min-width:90px;">Cost Type</th>
                                <th style="min-width:150px;">Combined Risk Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($open_point_report)):
                                foreach ($open_point_report as $row):
                                    $cc2 = strtolower(trim($row['color_code_text'] ?? ''));
                                    $ccLbl = esc($row['color_code_text']);
                                    if ($cc2 === 'red') {
                                        $ccBadge = 'badge bg-danger';
                                    } elseif ($cc2 === 'yellow') {
                                        $ccBadge = 'badge bg-warning text-dark';
                                    } elseif ($cc2 === 'black') {
                                        $ccBadge = 'badge bg-dark';
                                    } elseif ($cc2 === 'green') {
                                        $ccBadge = 'badge bg-success';
                                    } else {
                                        $ccBadge = 'badge bg-secondary';
                                    }
                                    ?>
                                    <tr>
                                        <td class="fw-semibold text-primary"><?= esc($row['location']) ?></td>
                                        <td><?= esc($row['region']) ?></td>
                                        <td><?= esc($row['cluster_name']) ?></td>
                                        <td><?= esc($row['audit_month']) ?></td>
                                        <td><?= !empty($row['audit_date']) ? date('d-M-Y', strtotime($row['audit_date'])) : '' ?>
                                        </td>
                                        <td><?= esc($row['audit_category']) ?></td>
                                        <td><?= esc($row['audit_question']) ?></td>
                                        <td><?= esc($row['nc_remark']) ?></td>
                                        <td><?= esc($row['findings_text']) ?></td>
                                        <td><?= esc($row['risk_text']) ?></td>
                                        <td><?= esc($row['actions_text']) ?></td>
                                        <td><?= esc($row['action_category_text']) ?></td>
                                        <td><?= esc($row['ua_uc_text']) ?></td>
                                        <td><?= esc($row['risk_severity_text']) ?></td>
                                        <td><?= esc($row['risk_probability_text']) ?></td>
                                        <td class="text-center">
                                            <?= $ccLbl ? '<span class="' . $ccBadge . '">' . $ccLbl . '</span>' : '' ?>
                                        </td>
                                        <td><?= esc($row['cost_type_text']) ?></td>
                                        <td class="text-center fw-semibold"><?= esc($row['combined_risk_rating_text']) ?></td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="18" class="text-center text-muted py-4">No HSE report data found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- ============ TAB 3: Aging Report ============ -->
                <div class="tab-pane fade" id="tab-aging" role="tabpanel">
                    <div class="tbl-scroll-x">
                        <table class="table table-bordered table-hover table-sm hse-table w-100" id="agingTable"
                            style="width:100%;">
                        <thead>
                            <tr class="table-light">
                                <th style="min-width:180px;">Location</th>
                                <th style="min-width:110px;">Audit No</th>
                                <th style="min-width:140px;">Name</th>
                                <th style="min-width:90px;">Region</th>
                                <th style="min-width:110px;">Cluster</th>
                                <th style="min-width:110px;">Latest Audit</th>
                                <th style="min-width:120px;">Original Audit</th>
                                <th style="min-width:110px;">Aging</th>
                                <?php foreach ($auditCategories as $cat): ?>
                                    <th style="min-width:120px;"><?= esc($cat['audit_category']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($aging_report)):
                                foreach ($aging_report as $row):
                                    $agCls = (int) $row['aging_days'] > 30 ? 'badge-aging-bad' : 'badge-aging-ok';
                                    ?>
                                    <tr>
                                        <td class="fw-semibold"><?= esc($row['location']) ?></td>
                                        <td><?= esc($row['audit_no']) ?></td>
                                        <td><?= esc($row['audit_name']) ?></td>
                                        <td><?= esc($row['region']) ?></td>
                                        <td><?= esc($row['cluster_name']) ?></td>
                                        <td><?= date('d-M-Y', strtotime($row['latest_audit_date'])) ?></td>
                                        <td><?= date('d-M-Y', strtotime($row['original_audit_date'])) ?></td>
                                        <td><span class="<?= $agCls ?>"><?= $row['aging_days'] ?> days</span></td>
                                        <?php foreach ($auditCategories as $cat):
                                            $k = strtolower(trim($cat['audit_category']));
                                            $k = str_replace(['&'], ['and'], $k);
                                            $k = preg_replace('/[^a-z0-9]+/', '_', $k);
                                            $k = trim($k, '_');
                                            ?>
                                            <td class="text-center"><?= $row[$k . '_audit_open'] ?? 0 ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="<?= 8 + count($auditCategories) ?>" class="text-center text-muted py-4">No
                                        aging data found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ===================== UPCOMING + QUICK ACTIONS ===================== -->
    <div class="row g-3 mb-4">

        <!-- Upcoming Audits – full list with scroller -->
        <div class="col-12 col-lg-12">
            <div class="card hse-card">
                <div class="card-header">
                    <div>
                        <div class="ch-title">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Upcoming Audits
                        </div>
                        <div class="ch-sub">
                            <?= $upcomingCount ?> scheduled
                            <?= $upcomingCount === 1 ? 'audit' : 'audits' ?>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill"
                            style="background:#eef2ff;color:#4361ee;font-size: 0.95rem;padding:5px 10px;">
                            <?= $upcomingCount ?> Total
                        </span>
                        <a href="javascript:void(0);" onclick="exportHseChartExcel('upcoming_audits')"
                            class="export-excel-btn" title="Export Excel Data">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                    </div>
                </div>

                <!-- scrollable list — shows ALL entries -->
                <div class="card-body p-0">
                    <div id="upcomingScroller" style="max-height:420px; overflow-y:auto; padding:.5rem .75rem;">

                        <?php if (!empty($upcoming_audits)):
                            foreach ($upcoming_audits as $ua):
                                $nd = !empty($ua['next_audit_date']) ? $ua['next_audit_date'] : null;
                                $dayNum = $nd ? date('d', strtotime($nd)) : '--';
                                $monStr = $nd ? strtoupper(date('M', strtotime($nd))) : '';
                                $dLeft = $nd ? max(0, (int) floor((strtotime($nd) - time()) / 86400)) : null;
                                $bgBadge = $dLeft === null ? '#6c757d'
                                    : ($dLeft <= 7 ? '#dc3545'
                                        : ($dLeft <= 14 ? '#ffc107' : '#198754'));
                                $txtBadge = ($dLeft !== null && $dLeft > 7 && $dLeft <= 14) ? '#212529' : '#fff';
                                $daysCls = $dLeft === null ? 'bg-secondary text-white'
                                    : ($dLeft <= 7 ? 'bg-danger text-white'
                                        : ($dLeft <= 14 ? 'bg-warning text-dark' : 'bg-success text-white'));
                                ?>
                                <div class="upcoming-item">
                                    <div class="udate-badge" style="background:<?= $bgBadge ?>;color:<?= $txtBadge ?>;">
                                        <div class="ud-day"><?= $dayNum ?></div>
                                        <div class="ud-mon"><?= $monStr ?></div>
                                    </div>

                                    <div class="flex-fill" style="min-width:0;">
                                        <div class="u-title"><?= strtoupper(esc($ua['location'])) ?></div>
                                        <div class="u-meta">
                                            <?= esc($ua['audit_no']) ?>
                                            &nbsp;|&nbsp;<?= esc($ua['region']) ?>
                                            <?= !empty($ua['cluster_name']) ? ' &nbsp;|&nbsp; ' . esc($ua['cluster_name']) : '' ?>
                                            <?php if (!empty($ua['auditor_name'])): ?>
                                                &nbsp;|&nbsp; <i class="fas fa-user-tie" style="font-size: 0.95rem;"></i>
                                                <?= esc($ua['auditor_name']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if ($dLeft !== null): ?>
                                        <div class="text-end flex-shrink-0">
                                            <span class="u-badge <?= $daysCls ?>"><?= $dLeft ?> Days Left</span>
                                            <?php if ($nd): ?>
                                                <div style="font-size: 0.95rem;color:#9a9aa0;margin-top:3px;">
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

                    </div><!-- /#upcomingScroller -->
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <!-- <div class="col-12 col-lg-5">
            <div class="card hse-card">
                <div class="card-header">
                    <div class="ch-title">
                        <i class="fas fa-bolt text-warning me-1"></i> Quick Actions
                    </div>
                </div>
                <div class="card-body p-2 p-sm-3">
                    <div class="d-grid gap-4">

                        <a href="<?= base_url('Masters/Audit_template/template_type_filter/HSE') ?>" class="qa-item">
                            <div class="qa-icon" style="background:#dbeafe;">
                                <i class="fas fa-plus-circle text-primary"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="qa-title">New HSE Audit</div>
                                <div class="qa-sub">Create a new HSE audit</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted" style="font-size: 0.95rem;"></i>
                        </a>

                        <a href="<?= base_url('Masters/Reaudit/index/HSE') ?>" class="qa-item">
                            <div class="qa-icon" style="background:#fef9c3;">
                                <i class="fas fa-sync-alt text-warning"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="qa-title">HSE Re-audit</div>
                                <div class="qa-sub">Initiate re-audit for closed audits</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted" style="font-size: 0.95rem;"></i>
                        </a>

                        <a href="<?= base_url('Masters/Hse_audit') ?>" class="qa-item">
                            <div class="qa-icon" style="background:#dcfce7;">
                                <i class="fas fa-clipboard-check text-success"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="qa-title">HSE Audit</div>
                                <div class="qa-sub">Schedule and conduct HSE audit</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted" style="font-size: 0.95rem;"></i>
                        </a>

                        <a href="<?= base_url('Masters/Hse_nc_tracker') ?>" class="qa-item">
                            <div class="qa-icon" style="background:#fce7f3;">
                                <i class="fas fa-tasks text-danger"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="qa-title">HSE NC Tracker</div>
                                <div class="qa-sub">Track non-conformance and active status</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted" style="font-size: 0.95rem;"></i>
                        </a>

                    </div>
                </div>
            </div>
        </div> -->

    </div>

</div><!-- /.hse-wrap -->
<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script
    src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    Chart.register(ChartDataLabels);

    /* ---------- Helpers ---------- */
    function drillDown(location, region, cluster) {
        const base = '<?= base_url('Masters/Hse_nc_tracker') ?>';
        window.open(`${base}?location[]=${encodeURIComponent(location)}&region[]=${encodeURIComponent(region)}&cluster[]=${encodeURIComponent(cluster)}`, '_blank');
    }

    function activateTab(btnId) {
        const el = document.getElementById(btnId);
        if (el) bootstrap.Tab.getOrCreateInstance(el).show();
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
        const orig = document.getElementById(tableId);
        if (!orig) return;

        /* If this table is a DataTable, rebuild a full <table> from all rows */
        let exportTable;
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
            const dt = $('#' + tableId).DataTable();
            const hdrs = [];

            /* Collect visible header cells */
            $(orig).find('thead tr th').each(function () {
                hdrs.push($(this).text().trim());
            });

            /* Build a plain <table> element for SheetJS */
            exportTable = document.createElement('table');
            const thead = document.createElement('thead');
            const htr = document.createElement('tr');
            hdrs.forEach(h => {
                const th = document.createElement('th');
                th.textContent = h;
                htr.appendChild(th);
            });
            thead.appendChild(htr);
            exportTable.appendChild(thead);

            const tbody = document.createElement('tbody');

            /* Use the original DOM rows in order (all pages) so HTML like badges render as text */
            dt.rows({ search: 'applied' }).nodes().each(function (tr) {
                const newTr = document.createElement('tr');
                $(tr).find('td').each(function () {
                    const td = document.createElement('td');
                    /* Strip HTML tags, keep plain text */
                    td.textContent = $(this).text().trim();
                    newTr.appendChild(td);
                });
                tbody.appendChild(newTr);
            });

            /* Append tfoot totals row if present */
            const tfootOrig = $(orig).find('tfoot');
            if (tfootOrig.length) {
                const tfoot = document.createElement('tfoot');
                tfootOrig.find('tr').each(function () {
                    const ftr = document.createElement('tr');
                    $(this).find('td, th').each(function () {
                        const td = document.createElement('td');
                        td.textContent = $(this).text().trim();
                        ftr.appendChild(td);
                    });
                    tfoot.appendChild(ftr);
                });
                exportTable.appendChild(tfoot);
            }
            exportTable.appendChild(tbody);

        } else {
            /* Plain table — clone as before */
            exportTable = orig.cloneNode(true);
        }

        /* For Category Wise Aging table, remove the first header row ("Location Info" / "NC Points") */
        if (tableId === 'agingTable') {
            const thead = exportTable.querySelector('thead');
            if (thead) {
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

        const wb = XLSX.utils.book_new();
        /* raw:true prevents SheetJS auto-converting "50.5%" -> 0.505 */
        const ws = XLSX.utils.table_to_sheet(exportTable, { raw: true });

        /* Post-process: any cell whose formatted value contains "%" → force to string
           so Excel stores "50.5%" not 0.505 */
        Object.keys(ws).forEach(addr => {
            if (addr[0] === '!') return;
            const cell = ws[addr];
            if (cell && typeof cell.w === 'string' && cell.w.indexOf('%') !== -1) {
                cell.t = 's';
                cell.v = cell.w;
                delete cell.z;
            }
        });

        XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
        return dl
            ? XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' })
            : XLSX.writeFile(wb, fn || ('Export.' + (type || 'xlsx')));
    }

    /* ---------- Export to CSV ---------- */
    function ExportToCSV(fn, tableId) {
        const orig = document.getElementById(tableId);
        if (!orig) return;

        let exportTable;
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#' + tableId)) {
            const dt = $('#' + tableId).DataTable();
            const hdrs = [];

            $(orig).find('thead tr th').each(function () {
                hdrs.push($(this).text().trim());
            });

            exportTable = document.createElement('table');
            const thead = document.createElement('thead');
            const htr = document.createElement('tr');
            hdrs.forEach(h => {
                const th = document.createElement('th');
                th.textContent = h;
                htr.appendChild(th);
            });
            thead.appendChild(htr);
            exportTable.appendChild(thead);

            const tbody = document.createElement('tbody');
            dt.rows({ search: 'applied' }).nodes().each(function (tr) {
                const newTr = document.createElement('tr');
                $(tr).find('td').each(function () {
                    const td = document.createElement('td');
                    td.textContent = $(this).text().trim();
                    newTr.appendChild(td);
                });
                tbody.appendChild(newTr);
            });

            const tfootOrig = $(orig).find('tfoot');
            if (tfootOrig.length) {
                const tfoot = document.createElement('tfoot');
                tfootOrig.find('tr').each(function () {
                    const ftr = document.createElement('tr');
                    $(this).find('td, th').each(function () {
                        const ftd = document.createElement('td');
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

        if (tableId === 'agingTable') {
            const thead = exportTable.querySelector('thead');
            if (thead) {
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

        const ws = XLSX.utils.table_to_sheet(exportTable, { raw: true });

        Object.keys(ws).forEach(addr => {
            if (addr[0] === '!') return;
            const cell = ws[addr];
            if (cell && typeof cell.w === 'string' && cell.w.indexOf('%') !== -1) {
                cell.t = 's';
                cell.v = cell.w;
                delete cell.z;
            }
        });

        const csv = XLSX.utils.sheet_to_csv(ws, { blankrows: false });
        const blob = new Blob(["\uFEFF" + csv], { type: "text/csv;charset=utf-8;" });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", fn || 'export.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /* ---------- Tab export button swap is now handled inside $(document).ready ---------- */

    function exportHseChartExcel(chartType) {
        let form = document.createElement("form");
        form.method = "POST";
        form.style.display = "none";

        let url = "";
        if (chartType === 'region_wise') url = "<?= base_url('Customer/Audit_Dashboard_HSE/exportRegionWiseHseChart') ?>";
        else if (chartType === 'open_points') url = "<?= base_url('Customer/Audit_Dashboard_HSE/exportOpenPointsHseChart') ?>";
        else if (chartType === 'capa_color') url = "<?= base_url('Customer/Audit_Dashboard_HSE/exportCapaColorHseChart') ?>";
        else if (chartType === 'nc_recommendation') url = "<?= base_url('Customer/Audit_Dashboard_HSE/exportNcRecommendationHseChart') ?>";
        else if (chartType === 'upcoming_audits') url = "<?= base_url('Customer/Audit_Dashboard_HSE/exportUpcomingAuditsHseChart') ?>";
        form.action = url;

        const filters = ['region', 'cluster_name', 'location_name', 'month'];
        filters.forEach(f => {
            const el = document.getElementById('filter_' + f) || document.querySelector(`[name="${f}"]`);
            if (el && el.value) {
                let input = document.createElement("input");
                input.type = "hidden";
                input.name = f;
                input.value = el.value;
                form.appendChild(input);
            }
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    /* ---------- Charts ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        const regionColors = <?= json_encode($region_color_map) ?>;
        const safeDestroy = id => { const c = Chart.getChart(id); if (c) c.destroy(); };

        /* sparklines removed — cards now use icon+value layout */

        /* ---- Score Donut — Region wise ---- */
        const dotPalette = ['#4361ee', '#f72585', '#7209b7', '#3a0ca3', '#4cc9f0',
            '#4895ef', '#560bad', '#b5179e', '#f3722c', '#43aa8b'];
        const sLabels = <?= json_encode(array_column(array_slice($pie_chart ?? [], 0, 10), 'region')) ?>;
        const sData = <?= json_encode(array_map(fn($v) => is_numeric($v) ? (float) $v : 0, array_column(array_slice($pie_chart ?? [], 0, 10), 'score'))) ?>;
        const donutEl = document.getElementById('scoreDonutChart');
        if (donutEl && sLabels.length) {
            safeDestroy('scoreDonutChart');
            new Chart(donutEl, {
                type: 'doughnut',
                data: {
                    labels: sLabels,
                    datasets: [{
                        data: sData,
                        backgroundColor: dotPalette.slice(0, sLabels.length),
                        borderWidth: 2, borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}%` } },
                        datalabels: { display: false }
                    }
                }
            });
        }

        /* ---- Horizontal Bar – ALL entries, rainbow colors ---- */
        const rainbowPalette = [
            '#ef4444', '#f97316', '#eab308', '#22c55e',
            '#14b8a6', '#3b82f6', '#6366f1', '#8b5cf6',
            '#a855f7', '#ec4899', '#6b7280', '#0ea5e9'
        ];

        const bLabels = <?= json_encode(array_column($total_HSE_score_openpoints, 'location')) ?>;
        const bData = <?= json_encode(array_map(fn($v) => is_numeric($v) ? (int) $v : 0, array_column($total_HSE_score_openpoints, 'total_active_open'))) ?>;
        const bRegions = <?= json_encode(array_column($total_HSE_score_openpoints, 'region')) ?>;

        /* Build rows for ALL locations with open pts > 0, sorted desc */
        let bRows = [];
        bLabels.forEach((l, i) => { if (l && bData[i] > 0) bRows.push({ l, v: bData[i], r: bRegions[i] || '' }); });
        bRows.sort((a, b) => b.v - a.v);
        /* No slice — show ALL */

        const barEl = document.getElementById('openPointsBarChart');
        if (barEl && bRows.length) {
            safeDestroy('openPointsBarChart');
            const wrap = document.getElementById('barChartWrap');
            const h = Math.max(300, bRows.length * 40);
            if (wrap) wrap.style.height = h + 'px';
            barEl.style.height = h + 'px';

            new Chart(barEl, {
                type: 'bar',
                data: {
                    labels: bRows.map(r => r.l),
                    datasets: [{
                        label: 'Open Points',
                        data: bRows.map(r => r.v),
                        backgroundColor: bRows.map((_, i) => rainbowPalette[i % rainbowPalette.length]),
                        borderRadius: 6,
                        barThickness: 26
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    layout: { padding: { right: 48, top: 4, bottom: 4 } },
                    onClick: (e, el) => { if (el.length) drillDown(bRows[el[0].index].l, bRows[el[0].index].r, ''); },
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ` Open Points: ${ctx.raw}` } },
                        datalabels: {
                            anchor: 'end', align: 'right', offset: 6,
                            color: '#374151', font: { weight: 'bold', size: 12 },
                            formatter: v => v,
                            display: ctx => ctx.dataset.data[ctx.dataIndex] > 0
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true, ticks: { precision: 0, color: '#6b7280' },
                            title: { display: true, text: 'Open Points Count', font: { weight: '600', size: 12 }, color: '#374151' },
                            grid: { color: '#f3f4f6' },
                            border: { color: '#e5e7eb' }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { autoSkip: false, font: { size: 11 }, color: '#374151', padding: 6 }
                        }
                    }
                }
            });
        }

        /* ---- CAPA Donut ---- */
        const cPalette = {
            Red: '#dc3545', Black: '#212529', Yellow: '#ffc107',
            Green: '#198754', Orange: '#fd7e14', Blue: '#0d6efd',
            Grey: '#6c757d', Gray: '#6c757d', Unknown: '#adb5bd'
        };
        const cLabels = <?= json_encode(array_column($color_code_open_points, 'color_code')) ?>;
        const cData = <?= json_encode(array_map(fn($v) => is_numeric($v) ? (int) $v : 0, array_column($color_code_open_points, 'total_count'))) ?>;
        let cRows = [];
        cLabels.forEach((l, i) => { if (l && cData[i] > 0) cRows.push({ l, v: cData[i] }); });
        const capaEl = document.getElementById('colorCodePieChart');
        if (capaEl && cRows.length) {
            safeDestroy('colorCodePieChart');
            new Chart(capaEl, {
                type: 'pie',
                data: {
                    labels: cRows.map(r => r.l),
                    datasets: [{
                        data: cRows.map(r => r.v),
                        backgroundColor: cRows.map(r => cPalette[r.l] || '#6c757d'),
                        borderWidth: 2, borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw} pts` } },
                        datalabels: {
                            display: ctx => ctx.dataset.data[ctx.dataIndex] > 0,
                            formatter: (v, ctx) => {
                                const tot = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                return tot ? ((v / tot) * 100).toFixed(1) + '%' : '';
                            },
                            color: '#fff', font: { weight: 'bold', size: 10 }
                        }
                    }
                }
            });
        }

        /* ---- NC vs Recommendation Donut ---- */
        const ncRecData = [
            <?= (int) ($nc_rec_chart['nc_count'] ?? 0) ?>,
            <?= (int) ($nc_rec_chart['rd_count'] ?? 0) ?>
        ];
        const ncRecEl = document.getElementById('ncRecChart');
        if (ncRecEl && (ncRecData[0] > 0 || ncRecData[1] > 0)) {
            safeDestroy('ncRecChart');
            new Chart(ncRecEl, {
                type: 'doughnut',
                data: {
                    labels: ['NC', 'Recommendation'],
                    datasets: [{
                        data: ncRecData,
                        backgroundColor: ['#4361ee', '#f72585'],
                        borderWidth: 2, borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    circumference: 180,
                    rotation: -90,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` } },
                        datalabels: { display: false }
                    }
                }
            });
        }
    });

    /* ---------- DataTables shared config factory ---------- */
    function makeHseDT(selector, scrollYpx, extraOpts) {
        if ($.fn.DataTable.isDataTable(selector)) return;
        const base = {
            paging: true,
            pageLength: 10,
            lengthMenu: [
                [5, 10, 25, 50, 100, -1],
                ['5   ', '10   ', '25   ', '50   ', '100   ', 'All']
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
        return $(selector).DataTable(Object.assign({}, base, extraOpts || {}));
    }

    /* ---------- Ajax filter dropdowns + DataTables init ---------- */
    $(document).ready(function () {

        /* ---- Open Points Summary table (always visible) ---- */
        if ($('#openPointsTable tbody tr td').length > 1) {
            makeHseDT('#openPointsTable', '380px', {
                columnDefs: [{ orderable: false, targets: -1 }],
                order: [[3, 'desc']]   /* sort by Open desc */
            });
        }

        /* ---- Tab 1: Month Summary ---- */
        /* Initialise when the tab is shown so width calculates correctly */
        $('#tab-month-btn').one('shown.bs.tab', function () {
            if ($('#monthSummaryTable tbody tr td').length > 1) {
                if (!$.fn.DataTable.isDataTable('#monthSummaryTable')) {
                    makeHseDT('#monthSummaryTable', '420px', {
                        order: [[0, 'asc']],
                        columnDefs: [
                            { type: 'string', targets: [0, 1, 2] },
                            { orderable: true, targets: '_all' }
                        ]
                    });
                }
            }
        });
        /* Also init on first load since tab-month is active by default */
        if ($('#monthSummaryTable tbody tr td').length > 1) {
            makeHseDT('#monthSummaryTable', '420px', {
                order: [[0, 'asc']],
                columnDefs: [{ orderable: true, targets: '_all' }]
            });
        }

        /* ---- Tab 2: HSE Report ---- */
        $('#tab-report-btn').one('shown.bs.tab', function () {
            if ($('#openPointsReportTable tbody tr td').length > 1) {
                if (!$.fn.DataTable.isDataTable('#openPointsReportTable')) {
                    makeHseDT('#openPointsReportTable', '420px', {
                        order: [[4, 'desc']],   /* sort by Audit Date desc */
                        columnDefs: [
                            { className: 'text-wrap', targets: [6, 7, 8, 9, 10] }
                        ]
                    });
                }
            }
        });

        /* ---- Tab 3: Aging Report ---- */
        $('#tab-aging-btn').one('shown.bs.tab', function () {
            if ($('#agingTable tbody tr td').length > 1) {
                if (!$.fn.DataTable.isDataTable('#agingTable')) {
                    makeHseDT('#agingTable', '420px', {
                        order: [[7, 'desc']]    /* sort by Aging desc */
                    });
                }
            }
        });

        /* ---- Recalculate column widths when tab shown (fixes header misalign) ---- */
        $('[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const target = $(e.target).data('bs-target');
            const tableMap = {
                '#tab-month': '#monthSummaryTable',
                '#tab-report': '#openPointsReportTable',
                '#tab-aging': '#agingTable'
            };
            const tblSel = tableMap[target];
            if (tblSel && $.fn.DataTable.isDataTable(tblSel)) {
                $(tblSel).DataTable().columns.adjust().draw(false);
            }
        });

        /* ---- Tab export button swap ---- */
        const tabExportMap2 = {
            'tab-month-btn': { file: 'Month_Summary.xlsx', table: 'monthSummaryTable' },
            'tab-report-btn': { file: 'HSE_Report.xlsx', table: 'openPointsReportTable' },
            'tab-aging-btn': { file: getTimestampedFilename('HSE_Category_Wise_Aging'), table: 'agingTable' },
        };
        $('#mainTabs [data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const info = tabExportMap2[e.target.id];
            if (info) {
                $('#tabExportBtn').html(
                    // `<button class="btn btn-success btn-sm rounded-pill px-3 me-2"
                    //     onclick="ExportToExcel('xlsx','${info.file}',false,'${info.table}')">
                    //     <i class="fas fa-file-excel me-1"></i> Excel
                    // </button>
                    `<button class="btn btn-info btn-sm rounded-pill px-3"
                        onclick="ExportToCSV('${info.file.replace('.xlsx', '.csv')}','${info.table}')">
                        <i class="fas fa-file-csv me-1"></i> CSV
                    </button>`
                );
            }
        });

        // Initialization handled by custom-multiselect.js globally

        /* ---- HSE Dependent Dropdown Helpers ---- */
        function loadHseClusters(restoreSelected) {
            const rawRegions = $('#region').val() || [];
            const regions = rawRegions.filter(v => v !== 'selectAll');
            const $c = $('#cluster_name');
            const prevSelected = restoreSelected ? $c.val() : [];
            if (!regions || regions.length === 0) {
                $c.empty().trigger('change');
                $('#location_name').empty().trigger('change');
                return;
            }
            $.post('<?= base_url('Customer/Audit_Dashboard_HSE/get_clusters_by_region') ?>',
                { region_name: regions, <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                function (res) {
                    $c.empty();
                    $c.append(new Option("Select All", "selectAll", false, prevSelected.includes("selectAll")));
                    if (res && res.clusters) {
                        res.clusters.forEach(c => {
                            const isSel = prevSelected.includes(c.cluster_name);
                            $c.append(new Option(c.cluster_name, c.cluster_name, false, isSel));
                        });
                    }
                    if ($c.data('select2')) {
                        $c.trigger('change.select2');
                    }
                    if (typeof updateCustomBadges === 'function') {
                        updateCustomBadges($c);
                    }
                    
                    // Continue cascade to locations
                    loadHseLocations(restoreSelected);
                });
        }

        function loadHseLocations(restoreSelected) {
            const rawClusters = $('#cluster_name').val() || [];
            const clusters = rawClusters.filter(v => v !== 'selectAll');
            const rawRegions = $('#region').val() || [];
            const regions = rawRegions.filter(v => v !== 'selectAll');
            const $l = $('#location_name');
            const prevSelected = restoreSelected ? $l.val() : [];
            if (!clusters || clusters.length === 0) {
                $l.empty().trigger('change');
                return;
            }
            $.post('<?= base_url('Customer/Audit_Dashboard_HSE/get_locations_by_cluster') ?>',
                { cluster_name: clusters, region_name: regions, <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                function (res) {
                    $l.empty();
                    $l.append(new Option("Select All", "selectAll", false, prevSelected.includes("selectAll")));
                    if (res && res.locations) {
                        res.locations.forEach(l => {
                            const isSel = prevSelected.includes(l.location_name);
                            $l.append(new Option(l.location_name, l.location_name, false, isSel));
                        });
                    }
                    if ($l.data('select2')) {
                        $l.trigger('change.select2');
                    }
                    if (typeof updateCustomBadges === 'function') {
                        updateCustomBadges($l);
                    }
                });
        }

        /* ---- Ajax Region dropdown ---- */
        $('#region').on('change select2:select select2:unselect', debounce(function (e) {
            loadHseClusters(false);
        }, 100));
        $('#cluster_name').on('change select2:select select2:unselect', debounce(function (e) {
            loadHseLocations(false);
        }, 100));

        // Simple debounce utility to prevent multiple rapid triggers during Select All
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
    });
</script>
<?php $this->endSection(); ?>