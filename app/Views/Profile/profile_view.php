<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<li class="breadcrumb-item text-muted">
    <a href="<?= base_url('Profile') ?>" class="text-muted text-hover-primary">AUDITOR PROFILE & DASHBOARD</a>
</li>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>

<style>
/* ============================================================
   AUDITOR PROFILE DASHBOARD – MODERN CLEAN STYLING
============================================================ */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

:root {
    --aud-primary: #3b82f6;
    --aud-primary-dark: #2563eb;
    --aud-success: #10b981;
    --aud-warning: #f59e0b;
    --aud-danger: #ef4444;
    --aud-info: #06b6d4;
    --aud-purple: #8b5cf6;
    --aud-card-bg: #ffffff;
    --aud-border-color: #e2e8f0;
    --aud-text-main: #0f172a;
    --aud-text-muted: #64748b;
    --aud-icon-unified: #3b82f6;
}

[data-bs-theme="dark"], body.dark-mode, .aside-dark ~ * [data-theme="dark"] {
    --aud-card-bg: #1e1e2d;
    --aud-border-color: #2b2b40;
    --aud-text-main: #f8fafc;
    --aud-text-muted: #94a3b8;
    --aud-icon-unified: #60a5fa;
}

.aud-dashboard,
.aud-dashboard * {
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
}

.aud-dashboard {
    color: var(--aud-text-main);
}

/* ── Unified Icon System ── */
.aud-filter-icon {
    color: var(--aud-icon-unified) !important;
    font-size: 0.85rem;
    margin-right: 0.4rem;
    vertical-align: middle;
}

.aud-header-meta-icon {
    color: rgba(255, 255, 255, 0.72) !important;
    font-size: 0.82rem;
    margin-right: 0.35rem;
    vertical-align: middle;
}

.aud-chart-title i,
.aud-chart-title-icon {
    color: var(--aud-icon-unified) !important;
    font-size: 1.05rem;
    margin-right: 0.35rem;
    vertical-align: middle;
}

/* ── Top Header Profile Card ── */
.aud-profile-header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 1.75rem 2rem;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
    margin-bottom: 1.75rem;
}
.aud-profile-header::after {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.aud-avatar-circle {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.65rem;
    font-weight: 700;
    border: 3px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    flex-shrink: 0;
}
.aud-meta-badge {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #f1f5f9;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

/* ── Filter Card ── */
.aud-filter-card {
    background: var(--aud-card-bg);
    border: 1px solid var(--aud-border-color);
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}

/* ── Module Nav Pills ── */
.aud-module-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    padding-bottom: 6px;
    flex-wrap: nowrap;
}
.aud-module-tab-btn {
    padding: 0.6rem 1.15rem;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 600;
    border: 1px solid var(--aud-border-color);
    background: var(--aud-card-bg);
    color: var(--aud-text-muted);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    white-space: nowrap;
    flex-shrink: 0;
}
.aud-module-tab-btn i {
    color: #64748b !important;
    font-size: 0.88rem;
    transition: color 0.2s ease;
}
.aud-module-tab-btn:hover {
    background: #f8fafc;
    color: #2563eb;
    border-color: #cbd5e1;
}
.aud-module-tab-btn:hover i {
    color: #2563eb !important;
}
.aud-module-tab-btn.active {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}
.aud-module-tab-btn.active i {
    color: #ffffff !important;
}

/* ── Category Banner ── */
.aud-section-banner {
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.banner-oe {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-left: 5px solid #2563eb;
    color: #1e40af;
}
.banner-gemba-hse {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-left: 5px solid #16a34a;
    color: #166534;
}

/* ── KPI Summary Cards ── */
.aud-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 0.85rem;
    margin-bottom: 1.5rem;
}
@media (min-width: 1400px) {
    .aud-kpi-grid-9 {
        grid-template-columns: repeat(9, 1fr);
        gap: 0.75rem;
    }
}
@media (min-width: 992px) and (max-width: 1399px) {
    .aud-kpi-grid-9 {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 0.75rem;
    }
}
@media (min-width: 576px) and (max-width: 991px) {
    .aud-kpi-grid-9 {
        grid-template-columns: repeat(3, 1fr);
        gap: 0.65rem;
    }
}
@media (max-width: 575px) {
    .aud-kpi-grid-9 {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }
}

.aud-kpi-card {
    background: var(--aud-card-bg);
    border: 1px solid var(--aud-border-color);
    border-radius: 12px;
    padding: 0.9rem 1rem;
    position: relative;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.aud-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.08);
}
.aud-kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
}
.aud-kpi-card.card-blue::before { background: #3b82f6; }
.aud-kpi-card.card-amber::before { background: #f59e0b; }
.aud-kpi-card.card-emerald::before { background: #10b981; }
.aud-kpi-card.card-cyan::before { background: #06b6d4; }
.aud-kpi-card.card-purple::before { background: #8b5cf6; }
.aud-kpi-card.card-rose::before { background: #ef4444; }
.aud-kpi-card.card-orange::before { background: #ea580c; }
.aud-kpi-card.card-indigo::before { background: #6366f1; }
.aud-kpi-card.card-teal::before { background: #14b8a6; }

.aud-kpi-icon-wrap {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
}
.card-blue .aud-kpi-icon-wrap { background: #eff6ff; color: #2563eb; }
.card-amber .aud-kpi-icon-wrap { background: #fffbeb; color: #d97706; }
.card-emerald .aud-kpi-icon-wrap { background: #ecfdf5; color: #059669; }
.card-cyan .aud-kpi-icon-wrap { background: #ecfeff; color: #0891b2; }
.card-purple .aud-kpi-icon-wrap { background: #f5f3ff; color: #7c3aed; }
.card-rose .aud-kpi-icon-wrap { background: #fef2f2; color: #dc2626; }
.card-orange .aud-kpi-icon-wrap { background: #fff7ed; color: #c2410c; }
.card-indigo .aud-kpi-icon-wrap { background: #eef2ff; color: #4f46e5; }
.card-teal .aud-kpi-icon-wrap { background: #f0fdfa; color: #0d9488; }

.aud-kpi-value {
    font-size: 1.4rem;
    font-weight: 700;
    line-height: 1.15;
    color: var(--aud-text-main);
    letter-spacing: -0.02em;
    margin-top: 0.45rem;
}
.aud-kpi-label {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--aud-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-top: 0.25rem;
    line-height: 1.25;
}

/* ── Chart Container Cards ── */
.aud-chart-card {
    background: var(--aud-card-bg);
    border: 1px solid var(--aud-border-color);
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    margin-bottom: 1.5rem;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.aud-chart-card .card-header {
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid var(--aud-border-color);
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.aud-chart-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--aud-text-main);
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin: 0;
}
.aud-chart-body {
    padding: 1.25rem 1.25rem 0.75rem;
    flex: 1;
    min-height: 320px;
    position: relative;
}

/* ── Pill Buttons ── */
.btn-group-pill {
    background: #f1f5f9;
    padding: 3px;
    border-radius: 8px;
    display: inline-flex;
}
.btn-group-pill .btn {
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 6px;
    border: none;
    color: #64748b;
    background: transparent;
}
.btn-group-pill .btn.active {
    background: #ffffff;
    color: #2563eb;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}

/* ── Table Styling ── */
.aud-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 10px;
}
.aud-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.aud-table th {
    background: #f8fafc;
    color: #475569;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--aud-border-color);
    white-space: nowrap;
}
.aud-table td {
    padding: 0.85rem 1rem;
    font-size: 0.82rem;
    color: var(--aud-text-main);
    border-bottom: 1px solid var(--aud-border-color);
    vertical-align: middle;
}
.aud-table tr:hover td {
    background-color: #f8fafc;
}

/* ── Mobile & Tablet Responsiveness ── */
@media (max-width: 767px) {
    .aud-profile-header {
        padding: 1.25rem 1rem;
    }
    .aud-avatar-circle {
        width: 54px;
        height: 54px;
        font-size: 1.3rem;
    }
    .aud-filter-card {
        padding: 1rem;
    }
    .aud-chart-card .card-header {
        padding: 0.9rem 1rem;
    }
    .aud-chart-body {
        padding: 0.85rem 0.5rem 0.5rem;
    }
    .aud-kpi-card {
        padding: 0.75rem 0.7rem;
    }
    .aud-kpi-value {
        font-size: 1.25rem;
    }
}
</style>

<div class="aud-dashboard">

    <!-- ========================================================
         TOP PROFILE BANNER
    ======================================================== -->
    <div class="aud-profile-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div class="aud-avatar-circle">
                    <?php
                    $audName = $target_user['user_name'] ?? 'Auditor';
                    $parts = explode(' ', trim($audName));
                    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                    echo esc($initials ?: 'AU');
                    ?>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h2 class="text-white fw-bolder m-0"><?= esc($target_user['user_name']) ?></h2>
                        <span class="badge bg-primary px-3 py-1 fs-7 fw-bold"><?= esc($target_user['user_designation'] ?: 'Auditor') ?></span>
                        <span class="badge bg-info px-2 py-1 fs-8">Scope: <?= esc($target_user['audit_type'] ?? 'Both') ?></span>
                        <?php if ((int)$target_user['status'] === 1): ?>
                            <span class="badge bg-success px-2 py-1 fs-8"><i class="fa fa-check-circle me-1"></i> Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary px-2 py-1 fs-8">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2 flex-wrap text-slate-300 fs-7">
                        <span><i class="fa fa-envelope aud-header-meta-icon"></i> <?= esc($target_user['user_email']) ?></span>
                        <span><i class="fa fa-id-badge aud-header-meta-icon"></i> <?= esc($target_user['user_emp_code'] ?: 'ID: #' . $target_user['user_id']) ?></span>
                        <?php if (!empty($target_user['user_contact'])): ?>
                            <span><i class="fa fa-phone aud-header-meta-icon"></i> <?= esc($target_user['user_contact']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                        <?php if (!empty($target_user['user_region'])): ?>
                            <span class="aud-meta-badge"><i class="fa fa-map-marker-alt aud-header-meta-icon"></i> Region: <?= esc($target_user['user_region']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($target_user['user_cluster'])): ?>
                            <span class="aud-meta-badge"><i class="fa fa-cubes aud-header-meta-icon"></i> Cluster: <?= esc($target_user['user_cluster']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($target_user['user_location'])): ?>
                            <span class="aud-meta-badge"><i class="fa fa-building aud-header-meta-icon"></i> <?= esc($target_user['user_location']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if ($is_admin && !empty($auditor_list)): ?>
                    <!-- Admin Auditor Switcher -->
                    <div class="d-flex align-items-center bg-white bg-opacity-10 rounded px-3 py-1">
                        <small class="text-white-50 me-2 text-nowrap"><i class="fa fa-user-shield aud-header-meta-icon"></i> Auditor Preview:</small>
                        <select class="form-select form-select-sm form-select-solid text-dark" style="min-width: 170px;" onchange="window.location.href='<?= base_url('Profile?auditor_id=') ?>' + this.value">
                            <?php foreach ($auditor_list as $aud): ?>
                                <option value="<?= $aud['user_id'] ?>" <?= ((int)$target_user['user_id'] === (int)$aud['user_id']) ? 'selected' : '' ?>>
                                    <?= esc($aud['user_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <button type="button" class="btn btn-sm btn-light-primary fw-bold" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="fa fa-key me-1"></i> Change Password
                </button>
            </div>
        </div>
    </div>

    <?php if ($is_auditor): ?>
        <!-- ========================================================
             YEARLY & DATE FILTER BAR
        ======================================================== -->
        <div class="aud-filter-card">
            <div class="row g-3 align-items-end">
                <!-- 1. Report Year -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label fs-7 fw-bold text-muted mb-1"><i class="fa fa-calendar-check aud-filter-icon"></i> Report Year</label>
                    <select class="form-select form-select-sm form-select-solid fw-bold" id="audFilterYear">
                        <?php
                        $activeYear = $current_year ?? date('Y');
                        $years = $years_list ?? [2026, 2025, 2024, 2023];
                        foreach ($years as $yr):
                        ?>
                            <option value="<?= $yr ?>" <?= ($yr == $activeYear) ? 'selected' : '' ?>>Year <?= $yr ?></option>
                        <?php endforeach; ?>
                        <option value="all">All Years</option>
                    </select>
                </div>

                <!-- 2. Date Range Mode -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label fs-7 fw-bold text-muted mb-1"><i class="fa fa-calendar-alt aud-filter-icon"></i> Date Filter</label>
                    <select class="form-select form-select-sm form-select-solid" id="audFilterDateRange">
                        <option value="yearly" selected>Full Year</option>
                        <option value="30d">Last 30 Days</option>
                        <option value="90d">Last 3 Months</option>
                        <option value="180d">Last 6 Months</option>
                        <option value="custom">Custom Date Range...</option>
                    </select>
                </div>

                <!-- Custom Start & End Date (Initially hidden) -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 d-none" id="customDateRangeBox">
                    <label class="form-label fs-7 fw-bold text-muted mb-1"><i class="fa fa-calendar-day aud-filter-icon"></i> From Date</label>
                    <input type="date" class="form-control form-control-sm form-control-solid" id="audFilterStartDate" value="<?= date('Y-01-01') ?>">
                </div>
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 d-none" id="customEndDateBox">
                    <label class="form-label fs-7 fw-bold text-muted mb-1"><i class="fa fa-calendar-day aud-filter-icon"></i> To Date</label>
                    <input type="date" class="form-control form-control-sm form-control-solid" id="audFilterEndDate" value="<?= date('Y-m-d') ?>">
                </div>

                <!-- 3. Audit Category Filter (Only 2 Main Categories) -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label fs-7 fw-bold text-muted mb-1"><i class="fa fa-th-large aud-filter-icon"></i> Audit Category</label>
                    <select class="form-select form-select-sm form-select-solid fw-bold" id="audFilterModule">
                        <?php if ($audit_scope === 'Both'): ?>
                        <option value="all" selected>All Categories</option>
                        <option value="OE">OE Audit</option>
                        <option value="Gemba_HSE">Gemba/HSE Audit</option>
                        <?php elseif ($audit_scope === 'OE'): ?>
                        <option value="OE" selected>OE Audit</option>
                        <?php elseif ($audit_scope === 'HSE'): ?>
                        <option value="Gemba_HSE" selected>Gemba/HSE Audit</option>
                        <?php else: ?>
                        <option value="all" selected>All Categories</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- 4. Status Filter -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label fs-7 fw-bold text-muted mb-1"><i class="fa fa-tasks aud-filter-icon"></i> Schedule Status</label>
                    <select class="form-select form-select-sm form-select-solid" id="audFilterStatus">
                        <option value="all" selected>All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="due">Due Soon (15 Days)</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>

                <!-- 5. Action Buttons -->
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-primary w-100 fw-bold" id="btnApplyAudFilters" title="Apply Filters">
                        <i class="fa fa-filter me-1"></i> Apply
                    </button>
                    <button type="button" class="btn btn-sm btn-light fw-bold" id="btnResetAudFilters" title="Reset Filters">
                        <i class="fa fa-undo"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================
             AUDIT CATEGORY NAVIGATION PILLS (2 Main Categories)
        ======================================================== -->
        <div class="aud-module-tabs">
            <?php if ($audit_scope === 'Both'): ?>
            <button type="button" class="aud-module-tab-btn active" data-target="all">
                <i class="fa fa-border-all"></i> All Categories Overview
            </button>
            <button type="button" class="aud-module-tab-btn" data-target="oe">
                <i class="fa fa-layer-group"></i> OE Audit Category
            </button>
            <button type="button" class="aud-module-tab-btn" data-target="gemba_hse">
                <i class="fa fa-shield-alt"></i> Gemba/HSE Audit Category
            </button>
            <?php elseif ($audit_scope === 'OE'): ?>
            <button type="button" class="aud-module-tab-btn active" data-target="oe">
                <i class="fa fa-layer-group"></i> OE Audit Category
            </button>
            <?php elseif ($audit_scope === 'HSE'): ?>
            <button type="button" class="aud-module-tab-btn active" data-target="gemba_hse">
                <i class="fa fa-shield-alt"></i> Gemba/HSE Audit Category
            </button>
            <?php endif; ?>
            <button type="button" class="aud-module-tab-btn" data-target="charts">
                <i class="fa fa-chart-line"></i> Analytics & Graphs
            </button>
            <button type="button" class="aud-module-tab-btn" data-target="nc">
                <i class="fa fa-clipboard-check"></i> NC Action Tracking
            </button>
            <button type="button" class="aud-module-tab-btn" data-target="reports">
                <i class="fa fa-table"></i> Audit & NC Reports
            </button>
        </div>

        <?php if ($audit_scope === 'Both' || $audit_scope === 'OE'): ?>
        <!-- ========================================================
             CATEGORY 1: OE AUDIT DASHBOARD (Summary Cards)
        ======================================================== -->
        <div class="module-section-box" id="sectionOE">
            <div class="aud-section-banner banner-oe">
                <div>
                    <h5 class="fw-bolder m-0 d-flex align-items-center gap-2">
                        <i class="fa fa-layer-group fs-4"></i> Category 1: OE Audit Dashboard
                    </h5>
                    <small class="text-muted">Operational Excellence Audits &bull; Frequency: <strong>Monthly (1 Month)</strong></small>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-white text-primary border shadow-sm px-3 py-2 fw-bold" id="oeLastDateBadge">
                        <i class="fa fa-calendar-check me-1"></i> Last Audit: <?= esc($initial_kpis['oe']['last_audit_date'] ?? '-') ?>
                    </span>
                    <span class="badge bg-primary text-white shadow-sm px-3 py-2 fw-bold" id="oeNextDateBadge">
                        <i class="fa fa-clock me-1"></i> Next Due: <?= esc($initial_kpis['oe']['next_audit_date'] ?? '-') ?>
                    </span>
                </div>
            </div>

            <!-- OE 9 Summary Cards -->
            <div class="aud-kpi-grid aud-kpi-grid-9 mb-4">
                <!-- 1. Total Audits -->
                <div class="aud-kpi-card card-blue">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-layer-group"></i></div>
                        <span class="badge bg-light-primary text-primary fs-8">Total</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOeTotal"><?= number_format($initial_kpis['oe']['total_audits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Total OE Audits</div>
                </div>

                <!-- 2. Performed Audits -->
                <div class="aud-kpi-card card-emerald">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-check-circle"></i></div>
                        <span class="badge bg-light-success text-success fs-8">Completed</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOePerformed"><?= number_format($initial_kpis['oe']['performed_audits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Performed Audits</div>
                </div>

                <!-- 3. Re-audits -->
                <div class="aud-kpi-card card-cyan">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-history"></i></div>
                        <span class="badge bg-light-info text-info fs-8">Follow-up</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOeReaudits"><?= number_format($initial_kpis['oe']['reaudits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Re-Audits</div>
                </div>

                <!-- 4. Pending Audits -->
                <div class="aud-kpi-card card-rose">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-clock"></i></div>
                        <span class="badge bg-light-danger text-danger fs-8" id="kpiOeDueBadge"><?= number_format($initial_kpis['oe']['due_audits'] ?? 0) ?> Due</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOePending"><?= number_format($initial_kpis['oe']['pending_audits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Pending Audits</div>
                </div>

                <!-- 5. Total NCs -->
                <div class="aud-kpi-card card-purple">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-exclamation-triangle"></i></div>
                        <span class="badge bg-light-purple text-purple fs-8">All NCs</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOeTotalNc"><?= number_format($initial_kpis['oe']['total_ncs'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Total NCs</div>
                </div>

                <!-- 6. Open NCs -->
                <div class="aud-kpi-card card-rose">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-door-open"></i></div>
                        <span class="badge bg-light-danger text-danger fs-8">Open</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOeOpenNc"><?= number_format($initial_kpis['oe']['open_ncs'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Open NCs</div>
                </div>

                <!-- 7. NCs under action / in progress -->
                <div class="aud-kpi-card card-amber">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-tools"></i></div>
                        <span class="badge bg-light-warning text-warning fs-8">Working</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOeWorkingNc"><?= number_format($initial_kpis['oe']['working_ncs'] ?? 0) ?></div>
                    <div class="aud-kpi-label">NCs Under Action</div>
                </div>

                <!-- 8. NCs pending review -->
                <div class="aud-kpi-card card-indigo">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-user-check"></i></div>
                        <span class="badge bg-light-primary text-primary fs-8">In Review</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOeReviewNc"><?= number_format($initial_kpis['oe']['review_ncs'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Pending Review</div>
                </div>

                <!-- 9. Closed/resolved NCs -->
                <div class="aud-kpi-card card-emerald">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-check-double"></i></div>
                        <span class="badge bg-light-success text-success fs-8">Resolved</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOeClosedNc"><?= number_format($initial_kpis['oe']['closed_ncs'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Closed / Resolved</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($audit_scope === 'Both' || $audit_scope === 'HSE'): ?>
        <!-- ========================================================
             CATEGORY 2: GEMBA/HSE AUDIT DASHBOARD (Combined)
        ======================================================== -->
        <div class="module-section-box" id="sectionGembaHSE">
            <div class="aud-section-banner banner-gemba-hse">
                <div>
                    <h5 class="fw-bolder m-0 d-flex align-items-center gap-2">
                        <i class="fa fa-shield-alt fs-4"></i> Category 2: Gemba/HSE Audit Dashboard
                    </h5>
                    <small class="text-muted">Combined Gemba Walks & HSE Audits &bull; Zero Double-Counting &bull; Latest HSE NCs Included</small>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-white text-success border shadow-sm px-3 py-2 fw-bold" id="gembaHseLastDateBadge">
                        <i class="fa fa-calendar-check me-1"></i> Last Activity: <?= esc($initial_kpis['gemba_hse']['last_audit_date'] ?? '-') ?>
                    </span>
                    <span class="badge bg-success text-white shadow-sm px-3 py-2 fw-bold">
                        <i class="fa fa-route me-1"></i> Quarterly & Walk Cycle
                    </span>
                </div>
            </div>

            <!-- Gemba/HSE 9 Summary Cards -->
            <div class="aud-kpi-grid aud-kpi-grid-9 mb-4">
                <!-- 1. Total Audits -->
                <div class="aud-kpi-card card-teal">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-walking"></i></div>
                        <span class="badge bg-light-success text-teal fs-8">Combined</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaHseTotal"><?= number_format($initial_kpis['gemba_hse']['total_audits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Total Audits</div>
                </div>

                <!-- 2. Performed Audits -->
                <div class="aud-kpi-card card-emerald">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-check-circle"></i></div>
                        <span class="badge bg-light-success text-success fs-8">Completed</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaHsePerformed"><?= number_format($initial_kpis['gemba_hse']['performed_audits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Performed Audits</div>
                </div>

                <!-- 3. Re-audits -->
                <div class="aud-kpi-card card-cyan">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-sync-alt"></i></div>
                        <span class="badge bg-light-info text-info fs-8">Follow-up</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaHseReaudits"><?= number_format($initial_kpis['gemba_hse']['reaudits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Re-Audits</div>
                </div>

                <!-- 4. Pending Audits -->
                <div class="aud-kpi-card card-rose">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-clock"></i></div>
                        <span class="badge bg-light-danger text-danger fs-8" id="kpiGembaHseDueBadge"><?= number_format($initial_kpis['gemba_hse']['due_audits'] ?? 0) ?> Due</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaHsePending"><?= number_format($initial_kpis['gemba_hse']['pending_audits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Pending Audits</div>
                </div>

                <!-- 5. Total NCs -->
                <div class="aud-kpi-card card-purple">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-notes-medical"></i></div>
                        <span class="badge bg-light-purple text-purple fs-8">All NCs</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaHseTotalNc"><?= number_format($initial_kpis['gemba_hse']['total_ncs'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Total NCs</div>
                </div>

                <!-- 6. Open NCs -->
                <div class="aud-kpi-card card-rose">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-exclamation-circle"></i></div>
                        <span class="badge bg-light-danger text-danger fs-8">Open</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaHseOpenNc"><?= number_format($initial_kpis['gemba_hse']['open_ncs'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Open NCs</div>
                </div>

                <!-- 7. NCs under action / in progress -->
                <div class="aud-kpi-card card-amber">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-hourglass-half"></i></div>
                        <span class="badge bg-light-warning text-warning fs-8">Working</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaHseWorkingNc"><?= number_format($initial_kpis['gemba_hse']['working_ncs'] ?? 0) ?></div>
                    <div class="aud-kpi-label">NCs Under Action</div>
                </div>

                <!-- 8. NCs pending review -->
                <div class="aud-kpi-card card-indigo">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-user-clock"></i></div>
                        <span class="badge bg-light-primary text-primary fs-8">In Review</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaHseReviewNc"><?= number_format($initial_kpis['gemba_hse']['review_ncs'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Pending Review</div>
                </div>

                <!-- 9. Closed/resolved NCs -->
                <div class="aud-kpi-card card-emerald">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-check-double"></i></div>
                        <span class="badge bg-light-success text-success fs-8">Resolved</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaHseClosedNc"><?= number_format($initial_kpis['gemba_hse']['closed_ncs'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Closed / Resolved</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ========================================================
             AUDIT PERFORMANCE CHARTS (OE vs Gemba/HSE)
        ======================================================== -->
        <div id="sectionCharts">
            <!-- ROW 1: Graph 1 (Performed vs Pending) & Graph 2 (Due & Overdue Status) -->
            <div class="row g-4 mb-4">
                <!-- Graph 1: Performed vs Pending Audits -->
                <div class="col-xl-7 col-lg-7">
                    <div class="aud-chart-card position-relative" id="cardChartPerformedPending">
                        <div class="card-header">
                            <h4 class="aud-chart-title">
                                <i class="fa fa-chart-bar aud-chart-title-icon"></i> Graph 1: Audit Activity (Performed vs Pending)
                            </h4>
                            <span class="badge bg-light text-primary fs-8 fw-bold">OE vs Gemba/HSE</span>
                        </div>
                        <div class="aud-chart-body">
                            <div id="chartPerformedVsPending" style="min-height: 320px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Graph 2: Audit Due and Overdue Status (Gemba/HSE Only) -->
                <div class="col-xl-5 col-lg-5">
                    <div class="aud-chart-card position-relative" id="cardChartDueStatus">
                        <div class="card-header">
                            <div>
                                <h4 class="aud-chart-title">
                                    <i class="fa fa-chart-pie aud-chart-title-icon"></i> Graph 2: Gemba/HSE Schedule Health
                                </h4>
                                <small class="text-muted fs-8">Gemba & HSE Schedule Distribution (Upcoming, Due & Overdue)</small>
                            </div>
                            <span class="badge bg-light-success text-success fs-8 fw-bold">Gemba/HSE Only</span>
                        </div>
                        <div class="aud-chart-body d-flex align-items-center justify-content-center">
                            <div id="chartDueOverdueStatus" style="width: 100%; min-height: 320px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 2: Graph 3 (Monthly Trend) & Graph 4 (Re-Audit Analysis) -->
            <div class="row g-4 mb-4">
                <!-- Graph 3: Monthly Audit Trend (Per Yearly Scope) -->
                <div class="col-xl-7 col-lg-7">
                    <div class="aud-chart-card position-relative" id="cardChartMonthlyTrend">
                        <div class="card-header">
                            <div>
                                <h4 class="aud-chart-title">
                                    <i class="fa fa-chart-line aud-chart-title-icon"></i> Graph 3: Monthly Audit Trend
                                </h4>
                                <small class="text-muted fs-8">Audits completed across Jan – Dec of selected year</small>
                            </div>
                            <span class="badge bg-light-info text-info fs-8 fw-bold" id="monthlyTrendYearBadge">Year <?= $current_year ?? date('Y') ?></span>
                        </div>
                        <div class="aud-chart-body">
                            <div id="chartMonthlyTrend" style="min-height: 320px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Graph 4: Re-Audit Analysis -->
                <div class="col-xl-5 col-lg-5">
                    <div class="aud-chart-card position-relative" id="cardChartReaudit">
                        <div class="card-header">
                            <h4 class="aud-chart-title">
                                <i class="fa fa-sync-alt aud-chart-title-icon"></i> Graph 4: Re-Audit Analysis
                            </h4>
                            <span class="badge bg-light-success text-success fs-8 fw-bold">Original vs Re-Audits</span>
                        </div>
                        <div class="aud-chart-body">
                            <div id="chartReauditAnalysis" style="min-height: 320px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================
             DEDICATED NC ACTION TRACKING & ANALYTICS
        ======================================================== -->
        <div class="module-section-box mb-4" id="sectionNC">
            <div class="aud-section-banner" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border-left: 5px solid #7c3aed; color: #5b21b6;">
                <div>
                    <h5 class="fw-bolder m-0 d-flex align-items-center gap-2">
                        <i class="fa fa-clipboard-check fs-4"></i> Section 3: NC Action Tracking & Status Analytics
                    </h5>
                    <small class="text-muted">Non-Conformance status distribution (Open, Under Action, In Review, Closed) across OE & Gemba/HSE</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-white text-purple border shadow-sm px-3 py-2 fw-bold" id="totalNcActionsBadge">
                        Total Closed: <?= number_format($initial_kpis['summary']['closed_ncs'] ?? 0) ?>
                    </span>
                </div>
            </div>

            <div class="row g-4">
                <!-- NC Status Distribution Chart -->
                <div class="col-lg-7">
                    <div class="aud-chart-card">
                        <div class="card-header">
                            <h4 class="aud-chart-title">
                                <i class="fa fa-chart-bar aud-chart-title-icon"></i> NC Status Distribution (OE vs Gemba/HSE)
                            </h4>
                            <span class="badge bg-light text-muted fs-8">Open, Working, Review, Closed</span>
                        </div>
                        <div class="aud-chart-body">
                            <div id="chartNcDistribution" style="min-height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <!-- NC Action Resolution Chart -->
                <div class="col-lg-5">
                    <div class="aud-chart-card">
                        <div class="card-header">
                            <h4 class="aud-chart-title">
                                <i class="fa fa-chart-pie aud-chart-title-icon"></i> NC Action Lifecycle Progress
                            </h4>
                            <span class="badge bg-light text-muted fs-8">Resolution Rate</span>
                        </div>
                        <div class="aud-chart-body d-flex align-items-center justify-content-center">
                            <div id="chartNcActionProgress" style="width: 100%; min-height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================
             REPORTS & DATA TABLES CONTAINER
        ======================================================== -->
        <div class="aud-chart-card mb-4" id="sectionReports">
            <div class="card-header">
                <div>
                    <ul class="nav nav-pills card-header-pills" id="reportTableTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-bold fs-7" id="tabSchedule-btn" data-bs-toggle="pill" data-bs-target="#tabScheduleContent" type="button" role="tab">
                                <i class="fa fa-calendar-alt me-1"></i> Site Audit Schedule & Status Report
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold fs-7" id="tabLatestNc-btn" data-bs-toggle="pill" data-bs-target="#tabLatestNcContent" type="button" role="tab">
                                <i class="fa fa-clipboard-list me-1"></i> Latest Non-Conformities (NCs) & Action Tracking
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-light-success fw-bold" id="btnExportTableCsv">
                        <i class="fa fa-file-excel me-1"></i> Export CSV
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="reportTableTabContent">

                    <!-- ─────────────────────────────────────────────
                         TAB 1: SITE AUDIT SCHEDULE & STATUS REPORT
                    ───────────────────────────────────────────── -->
                    <div class="tab-pane fade show active" id="tabScheduleContent" role="tabpanel">
                        <!-- Filters Header -->
                        <div class="d-flex align-items-center justify-content-between p-3 border-bottom flex-wrap gap-2 bg-light bg-opacity-50">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <input type="text" class="form-control form-control-sm form-control-solid" id="searchScheduleTable" placeholder="Search client, site, status..." style="max-width: 240px;">
                                <div class="btn-group-pill" id="scheduleTableCategoryPills">
                                    <?php if ($audit_scope === 'Both'): ?>
                                    <button type="button" class="btn active" data-cat="all">All</button>
                                    <button type="button" class="btn" data-cat="OE">OE Audit</button>
                                    <button type="button" class="btn" data-cat="Gemba/HSE">Gemba/HSE Audit</button>
                                    <?php elseif ($audit_scope === 'OE'): ?>
                                    <button type="button" class="btn active" data-cat="OE">OE Audit</button>
                                    <?php elseif ($audit_scope === 'HSE'): ?>
                                    <button type="button" class="btn active" data-cat="Gemba/HSE">Gemba/HSE Audit</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <select class="form-select form-select-sm form-select-solid" id="filterScheduleType" style="width: auto;">
                                    <option value="all">All Audit Types</option>
                                    <option value="Performed Audit">Performed Audits</option>
                                    <option value="Re-Audit">Re-Audits</option>
                                    <option value="Gemba Walk">Gemba Walks</option>
                                </select>
                            </div>
                        </div>

                        <div class="aud-table-wrap">
                            <table class="aud-table" id="auditScheduleDataTable">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Client Name</th>
                                        <th>Site / Location</th>
                                        <th>Latest Audit</th>
                                        <th>Report Date</th>
                                        <th>Next Scheduled</th>
                                        <th>Frequency</th>
                                        <th>Audit Type</th>
                                        <th>Status</th>
                                        <th class="text-center">NCs</th>
                                        <th class="text-center">Pending Actions</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="auditScheduleTableBody">
                                    <tr>
                                        <td colspan="12" class="text-center py-4">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                            Loading audit schedule & report data...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Schedule Table Footer & Pagination -->
                        <div class="d-flex align-items-center justify-content-between p-3 border-top flex-wrap gap-2">
                            <small class="text-muted" id="scheduleTableCountInfo">Showing 0 of 0 site schedules</small>
                            <div class="btn-group" id="scheduleTablePagination"></div>
                        </div>
                    </div>

                    <!-- ─────────────────────────────────────────────
                         TAB 2: LATEST NON-CONFORMITIES (NCs) REPORT
                    ───────────────────────────────────────────── -->
                    <div class="tab-pane fade" id="tabLatestNcContent" role="tabpanel">
                        <!-- Filters Header -->
                        <div class="d-flex align-items-center justify-content-between p-3 border-bottom flex-wrap gap-2 bg-light bg-opacity-50">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <input type="text" class="form-control form-control-sm form-control-solid" id="searchLatestNcTable" placeholder="Search site, NC ID, observation..." style="max-width: 250px;">
                                <div class="btn-group-pill" id="latestNcCategoryPills">
                                    <?php if ($audit_scope === 'Both'): ?>
                                    <button type="button" class="btn active" data-cat="all">All Categories</button>
                                    <button type="button" class="btn" data-cat="OE">OE NCs</button>
                                    <button type="button" class="btn" data-cat="Gemba/HSE">Gemba/HSE NCs</button>
                                    <?php elseif ($audit_scope === 'OE'): ?>
                                    <button type="button" class="btn active" data-cat="OE">OE NCs</button>
                                    <?php elseif ($audit_scope === 'HSE'): ?>
                                    <button type="button" class="btn active" data-cat="Gemba/HSE">Gemba/HSE NCs</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <select class="form-select form-select-sm form-select-solid" id="filterLatestNcStatus" style="width: auto;">
                                    <option value="all">All NC Statuses</option>
                                    <option value="0">Open</option>
                                    <option value="1">Under Action / Working</option>
                                    <option value="review">Pending Review</option>
                                    <option value="3">Closed / Resolved</option>
                                </select>
                            </div>
                        </div>

                        <div class="aud-table-wrap">
                            <table class="aud-table" id="latestNcDataTable">
                                <thead>
                                    <tr>
                                        <th>NC Ref / ID</th>
                                        <th>Category</th>
                                        <th>Audit Type</th>
                                        <th>Site / Location</th>
                                        <th>Audit Date</th>
                                        <th>Observation / Finding</th>
                                        <th>Responsible</th>
                                        <th>Status</th>
                                        <th>Action Due Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="latestNcTableBody">
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                            Loading latest NCs data...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Latest NC Table Footer & Pagination -->
                        <div class="d-flex align-items-center justify-content-between p-3 border-top flex-wrap gap-2">
                            <small class="text-muted" id="latestNcCountInfo">Showing 0 of 0 NC records</small>
                            <div class="btn-group" id="latestNcPagination"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ========================================================
             STANDARD NON-AUDITOR PROFILE VIEW
        ======================================================== -->
        <div class="row">
            <div class="col-md-7 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title text-white m-0 py-3"><i class="fa fa-user me-2 text-white"></i> Profile Details</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-striped">
                            <tbody>
                                <tr><th class="text-muted">Name</th><td><strong><?= esc($user['user_name']) ?></strong></td></tr>
                                <tr><th class="text-muted">Email</th><td><strong><?= esc($user['user_email']) ?></strong></td></tr>
                                <tr><th class="text-muted">Contact</th><td><strong><?= esc($user['user_contact']) ?: '-' ?></strong></td></tr>
                                <tr>
                                    <th class="text-muted">Location Details</th>
                                    <td><?= esc(implode(' / ', array_filter([$user['user_emp_country'], $user['user_emp_zone'], $user['user_region'], $user['user_cluster'], $user['user_location']]))) ?: '-' ?></td>
                                </tr>
                                <tr><th class="text-muted">Designation</th><td><span class="badge badge-info"><?= esc($user['user_designation']) ?: '-' ?></span></td></tr>
                                <tr><th class="text-muted">Status</th><td><span class="badge badge-success">Active</span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white">
                        <h3 class="card-title text-white m-0 py-3"><i class="fa fa-lock me-2 text-white"></i> Change Password</h3>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            Open Password Change Form
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- ========================================================
     MODAL: CHANGE PASSWORD
======================================================== -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title text-white"><i class="fa fa-lock me-2"></i> Update Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changePasswordForm">
                <div class="modal-body p-4">
                    <div id="passwordAlert" class="alert d-none"></div>

                    <div class="form-group mb-3">
                        <label for="current_password" class="form-label fw-bold">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-solid" id="current_password" name="current_password" required>
                            <span class="input-group-text toggle-password" style="cursor: pointer;"><i class="fa fa-eye"></i></span>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="new_password" class="form-label fw-bold">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-solid" id="new_password" name="new_password" minlength="6" required>
                            <span class="input-group-text toggle-password" style="cursor: pointer;"><i class="fa fa-eye"></i></span>
                        </div>
                        <small class="form-text text-muted">Min 6 chars, uppercase, lowercase, number, special char (e.g. Abc@123).</small>
                    </div>

                    <div class="form-group mb-3">
                        <label for="confirm_password" class="form-label fw-bold">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control form-control-solid" id="confirm_password" name="confirm_password" minlength="6" required>
                            <span class="input-group-text toggle-password" style="cursor: pointer;"><i class="fa fa-eye"></i></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btnChangePassword">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.52.0/dist/apexcharts.min.js"></script>

<script>
$(document).ready(function () {

    // ── Toggle Password Visibility ──
    $('.toggle-password').on('click', function () {
        let input = $(this).closest('.input-group').find('input');
        let icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // ── Password Form Submit ──
    $('#changePasswordForm').on('submit', function (e) {
        e.preventDefault();
        let cur = $('#current_password').val();
        let np = $('#new_password').val();
        let cp = $('#confirm_password').val();
        let btn = $('#btnChangePassword');

        if (np.length < 6) {
            showAlert('danger', 'Password must be at least 6 characters.');
            return;
        }
        let passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{6,}$/;
        if (!passwordRegex.test(np)) {
            showAlert('danger', 'Password format should be e.g. Abc@123 (uppercase, lowercase, number, special char).');
            return;
        }
        if (np !== cp) {
            showAlert('danger', 'New Password and Confirm Password do not match.');
            return;
        }
        if (np === cur) {
            showAlert('danger', 'New Password cannot be the same as Current Password.');
            return;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '<?= base_url("profile/update-password") ?>',
            type: 'POST',
            data: { current_password: cur, new_password: np, confirm_password: cp },
            dataType: 'json',
            success: function (res) {
                btn.prop('disabled', false).text('Update Password');
                if (res.status == 1) {
                    showAlert('success', res.message + ' Redirecting...');
                    $('#changePasswordForm')[0].reset();
                    setTimeout(function () { window.location.href = res.redirect; }, 2000);
                } else {
                    showAlert('danger', res.message);
                }
            },
            error: function () {
                btn.prop('disabled', false).text('Update Password');
                showAlert('danger', 'An error occurred while updating the password.');
            }
        });
    });

    function showAlert(type, message) {
        $('#passwordAlert')
            .removeClass('alert-success alert-danger d-none')
            .addClass('alert-' + type)
            .text(message)
            .show();
    }

    <?php if ($is_auditor): ?>
    /* ============================================================
       AUDITOR ANALYTICS DASHBOARD CLIENT ENGINE
    ============================================================ */
    const targetAuditorId = <?= (int)($target_user['user_id'] ?? 0) ?>;

    let chartPerformedPendingInst = null;
    let chartDueStatusInst = null;
    let chartMonthlyTrendInst = null;
    let chartReauditInst = null;
    let chartNcDistInst = null;
    let chartNcActionInst = null;

    // Table Records
    let scheduleTableRecords = [];
    let latestNcRecords = [];

    let schedCurrentPage = 1;
    const schedPageSize = 10;
    let activeSchedCat = 'all';

    let ncCurrentPage = 1;
    const ncPageSize = 10;
    let activeNcCat = 'all';

    // Toggle custom date pickers
    $('#audFilterDateRange').on('change', function () {
        if ($(this).val() === 'custom') {
            $('#customDateRangeBox, #customEndDateBox').removeClass('d-none');
        } else {
            $('#customDateRangeBox, #customEndDateBox').addClass('d-none');
        }
    });

    // Top Module Tab Switcher
    $('.aud-module-tab-btn').on('click', function () {
        $('.aud-module-tab-btn').removeClass('active');
        $(this).addClass('active');
        const target = $(this).data('target');

        if (target === 'all') {
            $('#sectionOE, #sectionGembaHSE, #sectionCharts, #sectionNC, #sectionReports').fadeIn(200);
            $('#audFilterModule').val('all');
        } else if (target === 'oe') {
            $('#sectionOE').fadeIn(200);
            $('#sectionGembaHSE').hide();
            $('#audFilterModule').val('OE');
            $('html, body').animate({ scrollTop: $("#sectionOE").offset().top - 80 }, 400);
        } else if (target === 'gemba_hse') {
            $('#sectionGembaHSE').fadeIn(200);
            $('#sectionOE').hide();
            $('#audFilterModule').val('Gemba_HSE');
            $('html, body').animate({ scrollTop: $("#sectionGembaHSE").offset().top - 80 }, 400);
        } else if (target === 'charts') {
            $('html, body').animate({ scrollTop: $("#sectionCharts").offset().top - 80 }, 400);
        } else if (target === 'nc') {
            $('html, body').animate({ scrollTop: $("#sectionNC").offset().top - 80 }, 400);
        } else if (target === 'reports') {
            $('html, body').animate({ scrollTop: $("#sectionReports").offset().top - 80 }, 400);
        }
    });

    // Apply & Reset Filters
    $('#btnApplyAudFilters').on('click', function () {
        loadAuditorAnalytics();
    });

    $('#btnResetAudFilters').on('click', function () {
        $('#audFilterYear').val('<?= date('Y') ?>');
        $('#audFilterDateRange').val('yearly').trigger('change');
        const defaultModule = '<?= ($audit_scope === "OE") ? "OE" : (($audit_scope === "HSE") ? "Gemba_HSE" : "all") ?>';
        $('#audFilterModule').val(defaultModule);
        $('#audFilterStatus').val('all');
        $('.aud-module-tab-btn').removeClass('active');
        const defaultTab = '<?= ($audit_scope === "OE") ? "oe" : (($audit_scope === "HSE") ? "gemba_hse" : "all") ?>';
        $('.aud-module-tab-btn[data-target="' + defaultTab + '"]').addClass('active');
        <?php if ($audit_scope === 'OE'): ?>
        $('#sectionOE, #sectionCharts, #sectionNC, #sectionReports').show();
        $('#sectionGembaHSE').hide();
        <?php elseif ($audit_scope === 'HSE'): ?>
        $('#sectionGembaHSE, #sectionCharts, #sectionNC, #sectionReports').show();
        $('#sectionOE').hide();
        <?php else: ?>
        $('#sectionOE, #sectionGembaHSE, #sectionCharts, #sectionNC, #sectionReports').show();
        <?php endif; ?>
        loadAuditorAnalytics();
    });

    // Initial Load
    loadAuditorAnalytics();

    function loadAuditorAnalytics() {
        const payload = {
            auditor_id: targetAuditorId,
            year: $('#audFilterYear').val(),
            date_range: $('#audFilterDateRange').val(),
            start_date: $('#audFilterStartDate').val(),
            end_date: $('#audFilterEndDate').val(),
            module: $('#audFilterModule').val() || '<?= ($audit_scope === "OE") ? "OE" : (($audit_scope === "HSE") ? "Gemba_HSE" : "all") ?>',
            status: $('#audFilterStatus').val()
        };

        showLoadingState(true);

        $.ajax({
            url: '<?= base_url("profile/get-auditor-analytics") ?>',
            type: 'POST',
            data: payload,
            dataType: 'json',
            success: function (res) {
                showLoadingState(false);
                if (res && res.status === 1) {
                    updateKpis(res.kpis);

                    // 4 Core Graphs
                    renderGraph1PerformedPending(res.charts.performed_vs_pending);
                    renderGraph2DueStatus(res.charts.due_overdue_status);
                    renderGraph3MonthlyTrend(res.charts.monthly_trend);
                    renderGraph4Reaudit(res.charts.reaudit_analysis);

                    // Section 3: NC Analytics
                    renderNcAnalytics(res.charts.nc_analytics);

                    // Tables Data
                    scheduleTableRecords = res.schedule_table || [];
                    schedCurrentPage = 1;
                    renderScheduleTable();

                    latestNcRecords = res.latest_ncs || [];
                    ncCurrentPage = 1;
                    renderLatestNcTable();
                } else {
                    console.error('Analytics load error:', res);
                }
            },
            error: function (xhr, status, error) {
                showLoadingState(false);
                console.error('AJAX Error:', error);
            }
        });
    }

    function showLoadingState(isLoading) {
        if (isLoading) {
            $('.aud-kpi-value').css('opacity', '0.4');
        } else {
            $('.aud-kpi-value').css('opacity', '1');
        }
    }

    function updateKpis(k) {
        if (!k) return;

        // OE Metrics (Category 1)
        if (k.oe) {
            $('#kpiOeTotal').text(nf(k.oe.total_audits));
            $('#kpiOePerformed').text(nf(k.oe.performed_audits));
            $('#kpiOeReaudits').text(nf(k.oe.reaudits));
            $('#kpiOePending').text(nf(k.oe.pending_audits));
            $('#kpiOeDueBadge').text(nf(k.oe.due_audits) + ' Due');

            $('#kpiOeTotalNc').text(nf(k.oe.total_ncs));
            $('#kpiOeOpenNc').text(nf(k.oe.open_ncs));
            $('#kpiOeWorkingNc').text(nf(k.oe.working_ncs));
            $('#kpiOeReviewNc').text(nf(k.oe.review_ncs));
            $('#kpiOeClosedNc').text(nf(k.oe.closed_ncs));

            if (k.oe.last_audit_date && k.oe.last_audit_date !== '-') {
                $('#oeLastDateBadge').html('<i class="fa fa-calendar-check me-1"></i> Last Audit: ' + k.oe.last_audit_date);
            }
            if (k.oe.next_audit_date && k.oe.next_audit_date !== '-') {
                $('#oeNextDateBadge').html('<i class="fa fa-clock me-1"></i> Next Due: ' + k.oe.next_audit_date);
            }
        }

        // Gemba/HSE Metrics (Category 2)
        if (k.gemba_hse) {
            $('#kpiGembaHseTotal').text(nf(k.gemba_hse.total_audits));
            $('#kpiGembaHsePerformed').text(nf(k.gemba_hse.performed_audits));
            $('#kpiGembaHseReaudits').text(nf(k.gemba_hse.reaudits));
            $('#kpiGembaHsePending').text(nf(k.gemba_hse.pending_audits));
            $('#kpiGembaHseDueBadge').text(nf(k.gemba_hse.due_audits) + ' Due');

            $('#kpiGembaHseTotalNc').text(nf(k.gemba_hse.total_ncs));
            $('#kpiGembaHseOpenNc').text(nf(k.gemba_hse.open_ncs));
            $('#kpiGembaHseWorkingNc').text(nf(k.gemba_hse.working_ncs));
            $('#kpiGembaHseReviewNc').text(nf(k.gemba_hse.review_ncs));
            $('#kpiGembaHseClosedNc').text(nf(k.gemba_hse.closed_ncs));

            if (k.gemba_hse.last_audit_date && k.gemba_hse.last_audit_date !== '-') {
                $('#gembaHseLastDateBadge').html('<i class="fa fa-calendar-check me-1"></i> Last Activity: ' + k.gemba_hse.last_audit_date);
            }
        }

        if (k.summary) {
            $('#totalNcActionsBadge').text('Total Closed: ' + nf(k.summary.closed_ncs));
        }
    }

    function nf(val) {
        return (val || 0).toLocaleString();
    }

    /* ── Graph 1: Performed vs Pending Audits ── */
    function renderGraph1PerformedPending(data) {
        if (!data) return;
        if (data.categories && data.categories.length === 1) {
            $('#cardChartPerformedPending .card-header .badge').text(data.categories[0] + ' Only');
        } else {
            $('#cardChartPerformedPending .card-header .badge').text('OE vs Gemba/HSE');
        }

        const options = {
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, sans-serif'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: (data.categories && data.categories.length === 1) ? '35%' : '50%',
                    borderRadius: 6
                }
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            series: data.series || [],
            xaxis: {
                categories: data.categories || ['OE Audits', 'Gemba/HSE Audits'],
                labels: { style: { colors: '#64748b', fontSize: '12px', fontWeight: 600 } }
            },
            yaxis: {
                labels: { style: { colors: '#64748b', fontSize: '11px' } },
                forceNiceScale: true
            },
            colors: ['#10b981', '#06b6d4', '#f59e0b', '#ef4444'],
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' },
            tooltip: { y: { formatter: function (val) { return val + " Audits"; } } }
        };

        if (chartPerformedPendingInst) {
            chartPerformedPendingInst.updateOptions(options);
        } else {
            chartPerformedPendingInst = new ApexCharts(document.querySelector("#chartPerformedVsPending"), options);
            chartPerformedPendingInst.render();
        }
    }

    /* ── Graph 2: Audit Schedule Health ── */
    function renderGraph2DueStatus(data) {
        if (!data) return;
        if (data.title) {
            $('#cardChartDueStatus .aud-chart-title').html('<i class="fa fa-chart-pie aud-chart-title-icon"></i> ' + data.title);
        }
        if (data.scope_badge) {
            $('#cardChartDueStatus .badge').text(data.scope_badge);
        }

        const labels = data.labels || ['Completed', 'Upcoming', 'Due Soon (15d)', 'Overdue'];
        const series = data.series || [0, 0, 0, 0];
        const total = series.reduce((a, b) => a + b, 0);
        const centerLabel = (data.module === 'OE') ? 'OE' : 'Gemba/HSE';
        const tooltipSuffix = (data.module === 'OE') ? ' OE Audits' : ' Gemba/HSE Audits';

        const options = {
            chart: {
                type: 'donut',
                height: 320,
                fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, sans-serif'
            },
            labels: labels,
            series: total > 0 ? series : [1],
            colors: total > 0 ? ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'] : ['#e2e8f0'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: centerLabel,
                                fontSize: '12px',
                                fontWeight: 600,
                                color: '#64748b',
                                formatter: function () { return total; }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { position: 'bottom', fontSize: '12px', itemMargin: { horizontal: 6, vertical: 3 } },
            stroke: { width: 2, colors: ['#ffffff'] },
            tooltip: {
                enabled: total > 0,
                y: { formatter: function (val) { return val + tooltipSuffix; } }
            }
        };

        if (chartDueStatusInst) {
            chartDueStatusInst.updateOptions(options);
        } else {
            chartDueStatusInst = new ApexCharts(document.querySelector("#chartDueOverdueStatus"), options);
            chartDueStatusInst.render();
        }
    }

    /* ── Graph 3: Monthly Audit Trend ── */
    function renderGraph3MonthlyTrend(data) {
        if (!data) return;
        $('#monthlyTrendYearBadge').text('Year ' + (data.year || '<?= date('Y') ?>'));

        const options = {
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, sans-serif'
            },
            series: data.series || [],
            xaxis: {
                categories: data.categories || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 500 } }
            },
            yaxis: {
                labels: { style: { colors: '#64748b', fontSize: '11px' } },
                forceNiceScale: true
            },
            colors: ['#2563eb', '#10b981'],
            stroke: { curve: 'smooth', width: 2.5 },
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 95, 100] }
            },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' },
            tooltip: { y: { formatter: function (val) { return val + " Audits"; } } }
        };

        if (chartMonthlyTrendInst) {
            chartMonthlyTrendInst.updateOptions(options);
        } else {
            chartMonthlyTrendInst = new ApexCharts(document.querySelector("#chartMonthlyTrend"), options);
            chartMonthlyTrendInst.render();
        }
    }

    /* ── Graph 4: Re-Audit Analysis ── */
    function renderGraph4Reaudit(data) {
        if (!data) return;
        if (data.categories && data.categories.length === 1) {
            $('#cardChartReaudit .card-header .badge').text(data.categories[0] + ' Only');
        } else {
            $('#cardChartReaudit .card-header .badge').text('Original vs Re-Audits');
        }

        const options = {
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, sans-serif'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: (data.categories && data.categories.length === 1) ? '35%' : '45%',
                    borderRadius: 6
                }
            },
            series: data.series || [],
            xaxis: {
                categories: data.categories || ['OE Audits', 'Gemba/HSE Audits'],
                labels: { style: { colors: '#64748b', fontSize: '12px', fontWeight: 600 } }
            },
            yaxis: {
                labels: { style: { colors: '#64748b', fontSize: '11px' } },
                forceNiceScale: true
            },
            colors: ['#10b981', '#06b6d4'],
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' },
            tooltip: { y: { formatter: function (val) { return val + " Audits"; } } }
        };

        if (chartReauditInst) {
            chartReauditInst.updateOptions(options);
        } else {
            chartReauditInst = new ApexCharts(document.querySelector("#chartReauditAnalysis"), options);
            chartReauditInst.render();
        }
    }

    /* ── Section 3: NC Analytics Charts ── */
    function renderNcAnalytics(data) {
        if (!data) return;

        // NC Status Distribution (Stacked Bar for OE vs Gemba/HSE)
        const distData = data.distribution || {};
        if (distData.categories && distData.categories.length === 1) {
            $('#chartNcDistribution').closest('.aud-chart-card').find('.aud-chart-title').html('<i class="fa fa-chart-bar aud-chart-title-icon"></i> NC Status Distribution (' + distData.categories[0] + ')');
        } else {
            $('#chartNcDistribution').closest('.aud-chart-card').find('.aud-chart-title').html('<i class="fa fa-chart-bar aud-chart-title-icon"></i> NC Status Distribution (OE vs Gemba/HSE)');
        }

        const distOptions = {
            chart: {
                type: 'bar',
                height: 300,
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'Poppins, -apple-system, BlinkMacSystemFont, sans-serif'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: (distData.categories && distData.categories.length === 1) ? '35%' : '45%',
                    borderRadius: 4
                }
            },
            series: distData.series || [],
            xaxis: {
                categories: distData.categories || ['OE Audit NCs', 'Gemba/HSE Audit NCs'],
                labels: { style: { colors: '#64748b', fontSize: '12px', fontWeight: 600 } }
            },
            yaxis: { labels: { style: { colors: '#64748b', fontSize: '11px' } }, forceNiceScale: true },
            colors: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981'],
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px' },
            tooltip: { y: { formatter: function (val) { return val + " NCs"; } } }
        };

        if (chartNcDistInst) {
            chartNcDistInst.updateOptions(distOptions);
        } else {
            chartNcDistInst = new ApexCharts(document.querySelector("#chartNcDistribution"), distOptions);
            chartNcDistInst.render();
        }

        // Action Progress Donut
        const actionData = data.action_progress || {};
        const totalActions = (actionData.series || []).reduce((a, b) => a + b, 0);
        const actionOptions = {
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'Poppins, sans-serif'
            },
            labels: actionData.labels || ['Closed / Resolved', 'Pending Review', 'Under Action', 'Open'],
            series: totalActions > 0 ? actionData.series : [1],
            colors: totalActions > 0 ? ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'] : ['#e2e8f0'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total NCs',
                                fontSize: '12px',
                                fontWeight: 600,
                                color: '#64748b',
                                formatter: function () { return totalActions; }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { position: 'bottom', fontSize: '11px' },
            stroke: { width: 2, colors: ['#ffffff'] },
            tooltip: {
                enabled: totalActions > 0,
                y: { formatter: function (val) { return val + " NCs"; } }
            }
        };

        if (chartNcActionInst) {
            chartNcActionInst.updateOptions(actionOptions);
        } else {
            chartNcActionInst = new ApexCharts(document.querySelector("#chartNcActionProgress"), actionOptions);
            chartNcActionInst.render();
        }
    }

    /* ── Schedule Table Logic ── */
    $('#scheduleTableCategoryPills button').on('click', function () {
        $('#scheduleTableCategoryPills button').removeClass('active');
        $(this).addClass('active');
        activeSchedCat = $(this).data('cat');
        schedCurrentPage = 1;
        renderScheduleTable();
    });

    $('#searchScheduleTable, #filterScheduleType').on('input change', function () {
        schedCurrentPage = 1;
        renderScheduleTable();
    });

    function renderScheduleTable() {
        const query = ($('#searchScheduleTable').val() || '').toLowerCase().trim();
        const typeFilter = $('#filterScheduleType').val();

        let filtered = scheduleTableRecords.filter(function (row) {
            if (activeSchedCat !== 'all' && row.category !== activeSchedCat) {
                return false;
            }
            if (typeFilter !== 'all' && row.audit_type !== typeFilter) {
                return false;
            }
            if (query !== '') {
                const searchCorpus = (row.category + ' ' + row.client_name + ' ' + row.site_name + ' ' + row.audit_status + ' ' + row.audit_type).toLowerCase();
                if (!searchCorpus.includes(query)) return false;
            }
            return true;
        });

        const totalItems = filtered.length;
        const totalPages = Math.ceil(totalItems / schedPageSize) || 1;
        if (schedCurrentPage > totalPages) schedCurrentPage = totalPages;

        const startIdx = (schedCurrentPage - 1) * schedPageSize;
        const pageItems = filtered.slice(startIdx, startIdx + schedPageSize);

        const tbody = $('#auditScheduleTableBody');
        tbody.empty();

        if (pageItems.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="12" class="text-center py-4 text-muted">
                        <i class="fa fa-folder-open fs-2 d-block mb-2 text-muted"></i>
                        No audit schedule records found matching selected filters.
                    </td>
                </tr>
            `);
            $('#scheduleTableCountInfo').text('Showing 0 of 0 site schedules');
            $('#scheduleTablePagination').empty();
            return;
        }

        pageItems.forEach(function (r) {
            let catBadge = (r.category === 'OE') ? 'bg-light-primary text-primary' : 'bg-light-success text-success';
            let statusBadge = 'badge-light-warning text-warning';
            if (r.audit_status === 'Overdue') statusBadge = 'badge-light-danger text-danger';
            if (r.audit_status === 'Upcoming') statusBadge = 'badge-light-primary text-primary';
            if (r.audit_status === 'Due') statusBadge = 'badge-light-warning text-warning';
            if (r.audit_status === 'Completed') statusBadge = 'badge-light-success text-success';

            tbody.append(`
                <tr>
                    <td><span class="badge ${catBadge} fw-bold px-2 py-1">${escapeHtml(r.category)}</span></td>
                    <td class="fw-semibold text-dark">${escapeHtml(r.client_name)}</td>
                    <td class="text-muted">${escapeHtml(r.site_name)}</td>
                    <td class="text-nowrap text-muted">${escapeHtml(r.latest_audit_date)}</td>
                    <td class="text-nowrap text-muted">${escapeHtml(r.report_date)}</td>
                    <td class="text-nowrap fw-bold ${r.audit_status === 'Overdue' ? 'text-danger' : 'text-dark'}">${escapeHtml(r.next_audit_date)}</td>
                    <td class="text-nowrap fs-8"><span class="badge bg-light text-dark">${escapeHtml(r.audit_frequency)}</span></td>
                    <td class="text-nowrap fs-8 text-muted">${escapeHtml(r.audit_type)}</td>
                    <td><span class="badge ${statusBadge} fw-bold px-2 py-1">${escapeHtml(r.audit_status)}</span></td>
                    <td class="text-center fw-bold">${escapeHtml(r.total_ncs)}</td>
                    <td class="text-center fw-bold ${r.pending_nc_actions > 0 ? 'text-warning' : 'text-success'}">${escapeHtml(r.pending_nc_actions)}</td>
                    <td class="text-center text-nowrap">
                        <a href="${r.action_url}" class="btn btn-sm btn-light-primary px-3 py-1 fw-bold" target="_blank">
                            <i class="fa fa-external-link-alt me-1 fs-8"></i> ${escapeHtml(r.action_text || 'View')}
                        </a>
                    </td>
                </tr>
            `);
        });

        // Count info
        const displayStart = startIdx + 1;
        const displayEnd = Math.min(startIdx + schedPageSize, totalItems);
        $('#scheduleTableCountInfo').text(`Showing ${displayStart} to ${displayEnd} of ${totalItems} site schedules`);

        // Pagination buttons
        renderPaginationButtons('#scheduleTablePagination', totalPages, schedCurrentPage, function (p) {
            schedCurrentPage = p;
            renderScheduleTable();
        });
    }

    /* ── Latest NC Table Logic ── */
    $('#latestNcCategoryPills button').on('click', function () {
        $('#latestNcCategoryPills button').removeClass('active');
        $(this).addClass('active');
        activeNcCat = $(this).data('cat');
        ncCurrentPage = 1;
        renderLatestNcTable();
    });

    $('#searchLatestNcTable, #filterLatestNcStatus').on('input change', function () {
        ncCurrentPage = 1;
        renderLatestNcTable();
    });

    function renderLatestNcTable() {
        const query = ($('#searchLatestNcTable').val() || '').toLowerCase().trim();
        const statusFilter = $('#filterLatestNcStatus').val();

        let filtered = latestNcRecords.filter(function (row) {
            if (activeNcCat !== 'all' && row.category !== activeNcCat) {
                return false;
            }
            if (statusFilter !== 'all') {
                if (statusFilter === 'review') {
                    if (row.nc_status !== 5 && row.nc_status !== 6 && row.nc_status !== 2) return false;
                } else if (String(row.nc_status) !== String(statusFilter)) {
                    return false;
                }
            }
            if (query !== '') {
                const searchCorpus = (row.nc_id + ' ' + row.audit_no + ' ' + row.site_name + ' ' + row.category + ' ' + row.description + ' ' + row.responsible + ' ' + row.status_label).toLowerCase();
                if (!searchCorpus.includes(query)) return false;
            }
            return true;
        });

        const totalItems = filtered.length;
        const totalPages = Math.ceil(totalItems / ncPageSize) || 1;
        if (ncCurrentPage > totalPages) ncCurrentPage = totalPages;

        const startIdx = (ncCurrentPage - 1) * ncPageSize;
        const pageItems = filtered.slice(startIdx, startIdx + ncPageSize);

        const tbody = $('#latestNcTableBody');
        tbody.empty();

        if (pageItems.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="10" class="text-center py-4 text-muted">
                        <i class="fa fa-clipboard-check fs-2 d-block mb-2 text-muted"></i>
                        No non-conformities found matching selected filters.
                    </td>
                </tr>
            `);
            $('#latestNcCountInfo').text('Showing 0 of 0 NC records');
            $('#latestNcPagination').empty();
            return;
        }

        pageItems.forEach(function (r) {
            let catBadge = (r.category === 'OE') ? 'bg-light-primary text-primary' : 'bg-light-success text-success';
            let statusBadge = 'badge-light-danger text-danger';
            if (r.nc_status === 1) statusBadge = 'badge-light-warning text-warning';
            if (r.nc_status === 5 || r.nc_status === 6) statusBadge = 'badge-light-primary text-primary';
            if (r.nc_status === 2) statusBadge = 'badge-light-info text-info';
            if (r.nc_status === 3) statusBadge = 'badge-light-success text-success';

            tbody.append(`
                <tr>
                    <td class="fw-bold text-dark text-nowrap"><i class="fa fa-tag text-muted me-1 fs-8"></i> ${escapeHtml(r.nc_id)}</td>
                    <td><span class="badge ${catBadge} fw-bold px-2 py-1">${escapeHtml(r.category)}</span></td>
                    <td class="text-nowrap fs-8 text-muted">${escapeHtml(r.audit_type)}</td>
                    <td class="fw-semibold text-dark">${escapeHtml(r.site_name)}</td>
                    <td class="text-nowrap text-muted">${escapeHtml(r.audit_date)}</td>
                    <td style="max-width: 280px;" class="text-truncate" title="${escapeHtml(r.description)}">${escapeHtml(r.description)}</td>
                    <td class="text-nowrap text-muted fs-8">${escapeHtml(r.responsible)}</td>
                    <td><span class="badge ${statusBadge} fw-bold px-2 py-1">${escapeHtml(r.status_label)}</span></td>
                    <td class="text-nowrap text-muted fs-8">${escapeHtml(r.due_date)}</td>
                    <td class="text-center text-nowrap">
                        <a href="${r.action_url}" class="btn btn-sm btn-light-primary px-3 py-1 fw-bold" target="_blank">
                            <i class="fa fa-external-link-alt me-1 fs-8"></i> ${escapeHtml(r.action_text || 'View')}
                        </a>
                    </td>
                </tr>
            `);
        });

        // Count info
        const displayStart = startIdx + 1;
        const displayEnd = Math.min(startIdx + ncPageSize, totalItems);
        $('#latestNcCountInfo').text(`Showing ${displayStart} to ${displayEnd} of ${totalItems} NC records`);

        // Pagination buttons
        renderPaginationButtons('#latestNcPagination', totalPages, ncCurrentPage, function (p) {
            ncCurrentPage = p;
            renderLatestNcTable();
        });
    }

    // Helper Pagination Generator
    function renderPaginationButtons(containerSelector, totalPages, currentPage, onPageClick) {
        const pagin = $(containerSelector);
        pagin.empty();
        if (totalPages <= 1) return;

        pagin.append(`
            <button type="button" class="btn btn-sm btn-light ${currentPage === 1 ? 'disabled' : ''}" data-act="prev">
                <i class="fa fa-chevron-left"></i>
            </button>
        `);
        for (let p = 1; p <= totalPages; p++) {
            if (totalPages > 6 && Math.abs(p - currentPage) > 2 && p !== 1 && p !== totalPages) {
                if (p === 2 || p === totalPages - 1) pagin.append(`<span class="btn btn-sm btn-light disabled">...</span>`);
                continue;
            }
            pagin.append(`
                <button type="button" class="btn btn-sm ${p === currentPage ? 'btn-primary' : 'btn-light'} btn-pg-action" data-page="${p}">${p}</button>
            `);
        }
        pagin.append(`
            <button type="button" class="btn btn-sm btn-light ${currentPage === totalPages ? 'disabled' : ''}" data-act="next">
                <i class="fa fa-chevron-right"></i>
            </button>
        `);

        pagin.find('button[data-act="prev"]').on('click', function () { if (currentPage > 1) onPageClick(currentPage - 1); });
        pagin.find('button[data-act="next"]').on('click', function () { if (currentPage < totalPages) onPageClick(currentPage + 1); });
        pagin.find('.btn-pg-action').on('click', function () { onPageClick(parseInt($(this).data('page'))); });
    }

    // ── CSV Export Functionality ──
    $('#btnExportTableCsv').on('click', function () {
        const activeTabId = $('#reportTableTabs .nav-link.active').attr('id');
        let csvContent = "data:text/csv;charset=utf-8,";

        if (activeTabId === 'tabSchedule-btn') {
            // Export Schedule Table
            csvContent += "Category,Client Name,Site Location,Latest Audit Date,Report Date,Next Scheduled Date,Audit Frequency,Audit Type,Status,Total NCs,Pending Actions\n";
            scheduleTableRecords.forEach(function (r) {
                const row = [
                    `"${r.category}"`,
                    `"${(r.client_name || '').replace(/"/g, '""')}"`,
                    `"${(r.site_name || '').replace(/"/g, '""')}"`,
                    `"${r.latest_audit_date}"`,
                    `"${r.report_date}"`,
                    `"${r.next_audit_date}"`,
                    `"${r.audit_frequency}"`,
                    `"${r.audit_type}"`,
                    `"${r.audit_status}"`,
                    r.total_ncs,
                    r.pending_nc_actions
                ];
                csvContent += row.join(",") + "\n";
            });
            downloadCsv(csvContent, "Site_Audit_Schedule_Report.csv");
        } else {
            // Export Latest NC Table
            csvContent += "NC ID,Category,Audit Type,Site Location,Audit Date,Observation Finding,Responsible Person,Status,Action Due Date\n";
            latestNcRecords.forEach(function (r) {
                const row = [
                    `"${r.nc_id}"`,
                    `"${r.category}"`,
                    `"${r.audit_type}"`,
                    `"${(r.site_name || '').replace(/"/g, '""')}"`,
                    `"${r.audit_date}"`,
                    `"${(r.description || '').replace(/"/g, '""')}"`,
                    `"${(r.responsible || '').replace(/"/g, '""')}"`,
                    `"${r.status_label}"`,
                    `"${r.due_date}"`
                ];
                csvContent += row.join(",") + "\n";
            });
            downloadCsv(csvContent, "Latest_NC_Action_Report.csv");
        }
    });

    function downloadCsv(content, filename) {
        const encodedUri = encodeURI(content);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    <?php endif; ?>

});
</script>
<?php $this->endSection(); ?>