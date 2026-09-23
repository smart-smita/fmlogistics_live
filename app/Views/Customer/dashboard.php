<?php
$this->extend("Layout/base_admin");
?>

<?php $this->section("breadcrumb_title_li"); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
/* ============================================================
   CUSTOMER DASHBOARD  –  Pixel-faithful redesign per mockup
============================================================ */
.db-wrap { font-family: 'Poppins', sans-serif; }
.db-wrap *:not(i):not([class*="fa"]):not([class*="fas"]):not([class*="far"]):not([class*="fab"]) {
    font-family: 'Poppins', sans-serif;
}
.db-wrap i[class*="fa"] {
    font-family: "Font Awesome 5 Free","Font Awesome 5 Brands","FontAwesome" !important;
}

/* ── Page title ── */
.db-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 1.1rem;
}

/* ── Generic white card ── */
.db-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e8eaf0;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    height: 100%;
}
.db-card-header {
    padding: .75rem 1rem .4rem;
    font-size: .85rem;
    font-weight: 700;
    color: #1a1a2e;
    border-bottom: none;
}
.db-card-sub {
    font-size: .72rem;
    color: #aaa;
    font-weight: 400;
    margin-top: 1px;
}
.db-card-footer-row {
    display: flex;
    justify-content: space-between;
    padding: .3rem 1rem .75rem;
    font-size: .75rem;
    color: #666;
    font-weight: 600;
}

/* ── Right-side KPI tiles (LTI / LMRA / Random / Structural) ── */
.kpi-tile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: .6rem .85rem;
    border-radius: 8px;
    border: 1px solid #f0f0f0;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    height: 100%;
    min-height: 54px;
}
.kpi-tile-icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    color: #fff;
}
.kpi-tile-body { flex: 1; min-width: 0; }
.kpi-tile-val {
    font-size: 1.25rem;
    font-weight: 800;
    line-height: 1;
    color: #1a1a2e;
}
.kpi-tile-lbl {
    font-size: .7rem;
    font-weight: 600;
    color: #9a9aa0;
    margin-top: 1px;
    white-space: nowrap;
}

/* colour helpers */
.bg-lti     { background: #fce4e4; }
.bg-lmra    { background: #fff8e1; }
.bg-random  { background: #e3f0ff; }
.bg-struct  { background: #e5f5eb; }
.ic-lti     { background: #e53935; }
.ic-lmra    { background: #f9a825; }
.ic-random  { background: #1976d2; }
.ic-struct  { background: #43a047; }
.txt-lti    { color: #e53935; }
.txt-lmra   { color: #f9a825; }
.txt-random { color: #1976d2; }
.txt-struct { color: #43a047; }

/* ── Audit Report section ── */
.section-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 1rem;
}

/* Outer wrapper — light blue-grey background matching mockup */
.audit-report-wrap {
    background: #f0f3fa;
    border-radius: 12px;
    padding: 1.2rem 1.1rem 1.4rem;
}

.audit-item-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #fff;
    border: none;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    height: 100%;
    min-height: 80px;
    transition: box-shadow .15s, transform .15s;
}
.audit-item-card:hover {
    box-shadow: 0 5px 18px rgba(0,0,0,.11);
    transform: translateY(-2px);
}

/* BIG circle icon — matches mockup */
.audit-icon {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    color: #fff;
}

.audit-body .a-val {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1;
}
.audit-body .a-lbl {
    font-size: .85rem;
    color: #9a9aa0;
    font-weight: 500;
    margin-top: 3px;
    line-height: 1.35;
}

/* ── Safe Man Days card ── */
.safe-card {
    background: linear-gradient(160deg, #2979ff 0%, #1565c0 100%);
    border-radius: 12px;
    color: #fff;
    padding: 1.5rem 1rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    height: 100%;
    min-height: 260px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .5rem;
}
.safe-card::before {
    content: '';
    position: absolute;
    bottom: -30px; right: -30px;
    width: 140px; height: 140px;
    border-radius: 50%;
    background: rgba(255,255,255,.07);
}
.safe-card-title {
    font-size: .9rem;
    font-weight: 700;
    opacity: .9;
    position: relative; z-index: 1;
}
.safe-avatar {
    width: 68px; height: 68px;
    border-radius: 50%;
    background: rgba(255,255,255,.18);
    border: 2px dashed rgba(255,255,255,.45);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem;
    position: relative; z-index: 1;
}
.safe-num {
    font-size: 3rem;
    font-weight: 900;
    line-height: 1;
    position: relative; z-index: 1;
}
.safe-sub {
    font-size: .8rem;
    font-weight: 600;
    opacity: .85;
    position: relative; z-index: 1;
}

/* ── User Management table wrapper ── */
.um-card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e8eaf0;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    height: 100%;
}
.um-card-header {
    padding: .85rem 1rem .6rem;
    font-size: .9rem;
    font-weight: 700;
    color: #1a1a2e;
    border-bottom: 1px solid #f0f0f0;
}
.um-card-body { padding: .75rem 1rem 1rem; }

/* ── DataTables overrides ── */
div[id$="_wrapper"] .dataTables_length select,
div[id$="_wrapper"] .dataTables_filter input {
    border: 1px solid #dde1eb;
    border-radius: 6px;
    padding: 3px 8px;
    font-size: .78rem;
    outline: none;
    min-height: 30px;
}
div[id$="_wrapper"] .dataTables_filter input:focus { border-color: #4361ee; box-shadow: 0 0 0 2px rgba(67,97,238,.1); }
div[id$="_wrapper"] .dataTables_info   { font-size: .72rem; color: #888; padding-top: 8px; }
div[id$="_wrapper"] .dataTables_length label,
div[id$="_wrapper"] .dataTables_filter label { font-size: .75rem; color: #666; font-weight: 500; }
div[id$="_wrapper"] .dataTables_paginate { padding-top: 6px; display:flex; flex-wrap:wrap; justify-content:flex-end; align-items:center; }
div[id$="_wrapper"] .dataTables_paginate span { display:flex; flex-wrap:wrap; }
div[id$="_wrapper"] .dataTables_paginate .paginate_button {
    display:inline-flex; align-items:center; justify-content:center;
    border-radius: 6px !important; font-size: .72rem !important; font-weight: 600 !important;
    padding: 3px 10px !important; margin: 2px !important;
    border: 1px solid transparent !important; color: #4361ee !important; transition: all .13s;
}
div[id$="_wrapper"] .dataTables_paginate .paginate_button.current {
    background: #4361ee !important; color: #fff !important; border-color: #4361ee !important;
}
div[id$="_wrapper"] .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
    background: #eef2ff !important; color: #4361ee !important; border-color: #c7d0f8 !important;
}
div[id$="_wrapper"] .dataTables_paginate .paginate_button.disabled { color: #ccc !important; cursor: default; }

/* ── Chart legend dots ── */
.chart-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    padding: .25rem 0 .5rem;
    font-size: .72rem;
    font-weight: 600;
    color: #555;
}
.chart-legend span { display:flex; align-items:center; gap:5px; }
.chart-legend .dot { width:10px; height:10px; border-radius:50%; display:inline-block; }

/* ── Responsive ── */
@media (max-width:767px) {
    .safe-num { font-size: 2.2rem; }
    .audit-body .a-val { font-size: 1.25rem; }
    .kpi-tile-val { font-size: 1.05rem; }
}
</style>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<?php
/* ── Compute status buckets ── */
$nearMissPending = 0; $nearMissClosed = 0; $nearMissUnderVefication = 0;
$ltiPending = 0;      $ltiClosed = 0;      $ltiUnderVefication = 0;
$minorPending = 0;    $minorClosed = 0;    $minorUnderVefication = 0;

foreach ($near_miss as $rows) {
    $cat = $rows['near_miss_category_text'];
    $cnt = (int)$rows['counts'];
    $st  = (int)$rows['status'];
    if ($cat === 'Near Miss') {
        if ($st === 1)     $nearMissClosed += $cnt;
        elseif ($st === 3) $nearMissPending += $cnt;
        else               $nearMissUnderVefication += $cnt;
    } elseif ($cat === 'Minor Injury') {
        if ($st === 1 || $st === 3) $minorPending += $cnt;
        else                        $minorUnderVefication += $cnt;
    } elseif ($cat === 'Lost Time Injury') {
        if ($st === 1)     $ltiClosed += $cnt;
        elseif ($st === 3) $ltiPending += $cnt;
        else               $ltiUnderVefication += $cnt;
    }
}

$totalNearMiss = $nearMissPending + $nearMissClosed + $nearMissUnderVefication;
$totalMinor    = $minorPending + $minorClosed + $minorUnderVefication;
$totalLti      = $ltiPending + $ltiClosed + $ltiUnderVefication;
$safeManDays   = (isset($safe_man_days) && count($safe_man_days) > 0) ? (int)$safe_man_days[0]['days'] : 0;

/* ── Audit report icon/colour map — fixed items + dynamic categories ── */
$auditRows = [
    ['val' => (int)($oe_total_count ?? 0), 'lbl' => 'OE Audit',  'bg' => '#3f51b5', 'icon' => 'fas fa-clipboard-check'],
    ['val' => (int)($hse_audit ?? 0),       'lbl' => 'HSE Nc',    'bg' => '#9c27b0', 'icon' => 'fas fa-clipboard'],
    ['val' => (int)($open_count ?? 0),      'lbl' => 'Open Nc',   'bg' => '#ff9800', 'icon' => 'fas fa-file-alt'],
];

/* Dynamic category counts — icon/colour assigned by position to match mockup visuals */
$catMeta = [
    ['bg' => '#4caf50', 'icon' => 'fas fa-check-circle'],   // green  – Client Closed
    ['bg' => '#00bcd4', 'icon' => 'fas fa-flag'],           // cyan   – FM Leased
    ['bg' => '#e91e63', 'icon' => 'fas fa-building'],       // pink   – Office
    ['bg' => '#ff5722', 'icon' => 'fas fa-user-circle'],    // orange – 4PL
    ['bg' => '#00acc1', 'icon' => 'fas fa-truck'],          // teal   – Distribution AC-W&D
    ['bg' => '#7b1fa2', 'icon' => 'fas fa-sitemap'],        // purple – Distribution AC-FTL
    ['bg' => '#43a047', 'icon' => 'fas fa-users'],          // green  – 4PL Headcount
    ['bg' => '#1976d2', 'icon' => 'fas fa-layer-group'],    // blue
    ['bg' => '#f57c00', 'icon' => 'fas fa-tag'],            // amber
];
$ci = 0;
if (isset($category_counts) && !empty($category_counts)) {
    foreach ($category_counts as $cat => $cnt) {
        $meta = $catMeta[$ci % count($catMeta)];
        $auditRows[] = [
            'val'  => (int)$cnt,
            'lbl'  => esc(ucwords(str_replace('_', ' ', $cat))) . ' Nc',
            'bg'   => $meta['bg'],
            'icon' => $meta['icon'],
        ];
        $ci++;
    }
}
?>

<div class="db-wrap">

    <div class="db-title">Dashboard</div>

    <!-- ══════════════════════════════════════════
         ROW 1:  Two half-donuts  +  4 KPI tiles
    ══════════════════════════════════════════ -->
    <div class="row g-3 mb-3">

        <!-- Audit Review Status -->
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="db-card">
                <div class="db-card-header">
                    Audit Review Status
                </div>
                <div id="nearMissChart" style="min-height:190px;"></div>
                <div class="chart-legend">
                    <span><span class="dot" style="background:#1976d2;"></span>Pending Review</span>
                    <span><span class="dot" style="background:#43a047;"></span>Resolved</span>
                    <span><span class="dot" style="background:#f9a825;"></span>Under Review</span>
                </div>
                <div class="db-card-footer-row">
                    <span>Total : <?= $totalNearMiss ?></span>
                    <span>Closed : <?= $nearMissClosed ?></span>
                </div>
            </div>
        </div>

        <!-- HSE NC Status -->
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="db-card">
                <div class="db-card-header">
                    HSE NC Status
                </div>
                <div id="minorInjuryChart" style="min-height:190px;"></div>
                <div class="chart-legend">
                    <span><span class="dot" style="background:#1976d2;"></span>Pending Review</span>
                    <span><span class="dot" style="background:#43a047;"></span>Resolved</span>
                    <span><span class="dot" style="background:#f9a825;"></span>Under Review</span>
                </div>
                <div class="db-card-footer-row">
                    <span>Total : <?= $totalMinor ?></span>
                    <span>Closed : <?= $minorClosed ?></span>
                </div>
            </div>
        </div>

        <!-- 4 KPI tiles stacked -->
        <div class="col-12 col-sm-12 col-xl-4">
            <div class="d-flex flex-column gap-2 h-100">

                <!-- LTI -->
                <div class="kpi-tile bg-lti" style="border: 2px solid #ffc4d4;">
                    <div class="kpi-tile-icon">
                        <i class="fas fa-user-injured text-danger" style="font-size:30px;"></i>
                    </div>
                    <div class="kpi-tile-body">
                        <div class="kpi-tile-val txt-lti"><?= $totalLti ?></div>
                        <div class="kpi-tile-lbl">LTI</div>
                    </div>
                </div>

                <!-- LMRA -->
                <div class="kpi-tile bg-lmra" style="border: 2px solid #f9d46d;">
                    <div class="kpi-tile-icon ">
                        <i class="fas fa-clipboard-list text-warning" style="font-size:30px;"></i>
                    </div>
                    <div class="kpi-tile-body">
                        <div class="kpi-tile-val txt-lmra"><?= (int)$lmra ?></div>
                        <div class="kpi-tile-lbl">LMRA</div>
                    </div>
                </div>

                <!-- Random Audit -->
                <div class="kpi-tile bg-random" style="border: 2px solid #a6cfed;">
                    <div class="kpi-tile-icon">
                        <i class="fas fa-random text-primary" style="font-size:30px;"></i>
                    </div>
                    <div class="kpi-tile-body">
                        <div class="kpi-tile-val txt-random"><?= (int)$sixes_audit ?></div>
                        <div class="kpi-tile-lbl">Random Audit</div>
                    </div>
                </div>

                <!-- Structural Audit -->
                <div class="kpi-tile bg-struct" style="border: 2px solid #8dd8a0ab;">
                    <div class="kpi-tile-icon">
                        <i class="fas fa-building text-success" style="font-size:30px;"></i>
                    </div>
                    <div class="kpi-tile-body">
                        <div class="kpi-tile-val txt-struct"><?= (int)$st_audit ?></div>
                        <div class="kpi-tile-lbl">Structural Audit</div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- ══════════════════════════════════════════
         ROW 2:  Line chart (left)  +  Abstract donut (right)
    ══════════════════════════════════════════ -->
    <div class="row g-3 mb-3">

        <!-- Report Overview line chart -->
        <div class="col-12 col-xl-7">
            <div class="db-card">
                <div class="db-card-header">
                    Report Overview
                    <div class="db-card-sub">Product Trends by Month</div>
                </div>
                <div class="px-2">
                    <div id="trendChart" style="min-height:270px;"></div>
                </div>
            </div>
        </div>

        <!-- Abstract donut -->
        <div class="col-12 col-xl-5">
            <div class="db-card">
                <div class="db-card-header">
                    Abstract
                    <div class="db-card-sub">Abstract: Near miss / LTI / Minor Injury</div>
                </div>
                <div id="abstractChart" style="min-height:280px;"></div>
                <div class="chart-legend pb-2">
                    <span><span class="dot" style="background:#eab308;"></span>Near Miss - <?= $totalNearMiss ?></span>
                    <span><span class="dot" style="background:#f97316;"></span>Minor Injury - <?= $totalMinor ?></span>
                    <span><span class="dot" style="background:#ef4444;"></span>LTI - <?= $totalLti ?></span>
                    <span><span class="dot" style="background:#14b8a6;"></span>LMRA - <?= (int)$lmra ?></span>
                </div>
            </div>
        </div>

    </div>

    <!-- ══════════════════════════════════════════
         ROW 3:  Audit Report items grid
    ══════════════════════════════════════════ -->
    <div class="mb-3">
        <div class="audit-report-wrap">
            <div class="section-title">Audit Report</div>
            <div class="row g-3">
                <?php foreach ($auditRows as $item): ?>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="audit-item-card">
                        <div class="audit-icon" style="background:<?= $item['bg'] ?>;">
                            <i class="<?= $item['icon'] ?> text-white fs-1" ></i>
                        </div>
                        <div class="audit-body">
                            <div class="a-val"><?= $item['val'] ?></div>
                            <div class="a-lbl"><?= $item['lbl'] ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         ROW 4:  Safe Man Days  +  User Management
    ══════════════════════════════════════════ -->
    <div class="row g-3 mb-4">

        <!-- Safe Man Days -->
        <div class="col-12 col-sm-5 col-md-4 col-xl-3 h-100">
            <div class="safe-card">
                <div class="safe-card-title">Safe Man Day's</div>
                <div class="safe-avatar">
                    <i class="fas fa-user fs-1 text-white"></i>
                </div>
                <div class="safe-num"><?= $safeManDays ?></div>
                <div class="safe-sub">Total Safe Man Days</div>
            </div>
        </div>

        <!-- User Management -->
        <div class="col-12 col-sm-7 col-md-8 col-xl-9">
            <div class="um-card">
                <!-- <div class="um-card-header">User Management</div> -->
                <div class="um-card-body">
                    <?= $user_table ?>
                </div>
            </div>
        </div>

    </div>

</div><!-- /.db-wrap -->

<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.52.0/dist/apexcharts.min.js"></script>
<script>
(function () {
    'use strict';

    /* ── Half-donut factory ── */
    function halfDonut(selector, series, colors) {
        new ApexCharts(document.querySelector(selector), {
            series  : series,
            chart   : { type: 'donut', height: 200, sparkline: { enabled: false }, toolbar: { show: false } },
            labels  : ['Pending Review', 'Resolved', 'Under Review'],
            colors  : colors,
            plotOptions: {
                pie: {
                    startAngle : -90,
                    endAngle   : 90,
                    offsetY    : 10,
                    donut      : { size: '62%' }
                }
            },
            grid        : { padding: { bottom: -80 } },
            legend      : { show: false },
            dataLabels  : {
                enabled : true,
                formatter: (val) => Math.round(val) + '%',
                style   : { fontSize: '11px', fontWeight: 700, colors: ['#fff'] },
                dropShadow: { enabled: false }
            },
            tooltip: { y: { formatter: v => v } }
        }).render();
    }

    halfDonut('#nearMissChart',
        [<?= $nearMissPending ?>, <?= $nearMissClosed ?>, <?= $nearMissUnderVefication ?>],
        ['#1976d2', '#43a047', '#f9a825']
    );

    halfDonut('#minorInjuryChart',
        [<?= $minorPending ?>, <?= $minorClosed ?>, <?= $minorUnderVefication ?>],
        ['#1976d2', '#43a047', '#f9a825']
    );

    /* ── Abstract full donut ── */
    new ApexCharts(document.querySelector('#abstractChart'), {
        series : [<?= $totalNearMiss ?>, <?= $totalMinor ?>, <?= $totalLti ?>, <?= (int)$lmra ?>],
        chart  : { type: 'donut', height: 280, toolbar: { show: false } },
        labels : ['Near Miss', 'Minor Injury', 'LTI', 'LMRA'],
        colors : ['#eab308', '#f97316', '#ef4444', '#14b8a6'],
        plotOptions: { pie: { donut: { size: '65%', labels: { show: false } } } },
        legend      : { show: false },
        dataLabels  : { enabled: false },
        stroke      : { width: 3 },
        tooltip: { y: { formatter: v => v + ' records' } }
    }).render();

    /* ── Trend line chart ── */
    new ApexCharts(document.querySelector('#trendChart'), {
        series: [
            { name: 'Minor Injury', data: [<?php foreach ($graph_count as $g) { echo (int)$g['minor']   . ','; } ?>] },
            { name: 'Near Miss',    data: [<?php foreach ($graph_count as $g) { echo (int)$g['nearmiss'] . ','; } ?>] }
        ],
        chart  : { type: 'line', height: 270, toolbar: { show: false }, zoom: { enabled: false } },
        colors : ['#1976d2', '#43a047'],
        stroke : { curve: 'smooth', width: 2 },
        markers: { size: 4, strokeWidth: 2, hover: { size: 6 } },
        dataLabels: { enabled: false },
        grid: {
            borderColor: '#f0f0f0',
            row: { colors: ['#fafafa', 'transparent'] }
        },
        xaxis: {
            categories: ['January','February','March','April','May','June','July','August','September','October','November','December'],
            labels: { style: { fontSize: '10px', fontWeight: 500 }, rotate: -30, rotateAlways: false }
        },
        yaxis: {
            min: 0,
            tickAmount: 6,
            labels: { style: { fontSize: '10px' }, formatter: v => parseFloat(v).toFixed(1) }
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            fontSize: '11px',
            fontWeight: 600,
            markers: { width: 10, height: 10, radius: 5 }
        },
        tooltip: { shared: true, intersect: false }
    }).render();

})();
</script>
<?php $this->endSection(); ?>
