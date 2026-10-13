<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<li class="breadcrumb-item text-muted">
    <a href="<?= base_url('Profile') ?>" class="text-muted text-hover-primary">AUDITOR PROFILE & DASHBOARD</a>
</li>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>

<style>
/* ============================================================
   AUDITOR PROFILE DASHBOARD – PREMIUM MODERN STYLING
============================================================ */
:root {
    --aud-primary: #3b82f6;
    --aud-success: #10b981;
    --aud-warning: #f59e0b;
    --aud-danger: #ef4444;
    --aud-info: #06b6d4;
    --aud-purple: #8b5cf6;
    --aud-card-bg: #ffffff;
    --aud-border-color: #e2e8f0;
    --aud-text-main: #0f172a;
    --aud-text-muted: #64748b;
}

[data-bs-theme="dark"], body.dark-mode, .aside-dark ~ * [data-theme="dark"] {
    --aud-card-bg: #1e1e2d;
    --aud-border-color: #2b2b40;
    --aud-text-main: #f8fafc;
    --aud-text-muted: #94a3b8;
}

.aud-dashboard {
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--aud-text-main);
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
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
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
    margin-bottom: 1.75rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}

/* ── Module Nav Pills ── */
.aud-module-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.aud-module-tab-btn {
    padding: 0.6rem 1.25rem;
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
}
.aud-module-tab-btn:hover {
    background: #f8fafc;
    color: #2563eb;
    border-color: #cbd5e1;
}
.aud-module-tab-btn.active {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

/* ── Module Section Header Banner ── */
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
.banner-hse {
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    border-left: 5px solid #ea580c;
    color: #9a3412;
}
.banner-gemba {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-left: 5px solid #16a34a;
    color: #166534;
}

/* ── KPI Summary Cards ── */
.aud-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 1.15rem;
    margin-bottom: 1.75rem;
}
@media (min-width: 1400px) {
    .aud-kpi-grid-5 {
        grid-template-columns: repeat(5, 1fr);
    }
}
@media (max-width: 768px) {
    .aud-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
}
@media (max-width: 480px) {
    .aud-kpi-grid {
        grid-template-columns: 1fr;
    }
}

.aud-kpi-card {
    background: var(--aud-card-bg);
    border: 1px solid var(--aud-border-color);
    border-radius: 14px;
    padding: 1.15rem 1.25rem;
    position: relative;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
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
.aud-kpi-card.card-rose::before { background: #f43f5e; }
.aud-kpi-card.card-orange::before { background: #ea580c; }
.aud-kpi-card.card-teal::before { background: #14b8a6; }

.aud-kpi-icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.card-blue .aud-kpi-icon-wrap { background: #eff6ff; color: #2563eb; }
.card-amber .aud-kpi-icon-wrap { background: #fffbeb; color: #d97706; }
.card-emerald .aud-kpi-icon-wrap { background: #ecfdf5; color: #059669; }
.card-cyan .aud-kpi-icon-wrap { background: #ecfeff; color: #0891b2; }
.card-purple .aud-kpi-icon-wrap { background: #f5f3ff; color: #7c3aed; }
.card-rose .aud-kpi-icon-wrap { background: #fff1f2; color: #e11d48; }
.card-orange .aud-kpi-icon-wrap { background: #fff7ed; color: #c2410c; }
.card-teal .aud-kpi-icon-wrap { background: #f0fdfa; color: #0d9488; }

.aud-kpi-value {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1.1;
    color: var(--aud-text-main);
    letter-spacing: -0.02em;
}
.aud-kpi-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--aud-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-top: 0.3rem;
}
.aud-kpi-badge {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
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
    gap: 0.5rem;
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
    font-size: 0.75rem;
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
                        <h2 class="text-white fw-bolder m-0 fs-2"><?= esc($target_user['user_name']) ?></h2>
                        <span class="badge bg-primary px-3 py-1 fs-7 fw-bold"><?= esc($target_user['user_designation'] ?: 'Auditor') ?></span>
                        <?php if ((int)$target_user['status'] === 1): ?>
                            <span class="badge bg-success px-2 py-1 fs-8"><i class="fa fa-check-circle me-1"></i> Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary px-2 py-1 fs-8">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-2 flex-wrap text-slate-300 fs-7">
                        <span><i class="fa fa-envelope text-info me-1"></i> <?= esc($target_user['user_email']) ?></span>
                        <span><i class="fa fa-id-badge text-warning me-1"></i> <?= esc($target_user['user_emp_code'] ?: 'ID: #' . $target_user['user_id']) ?></span>
                        <?php if (!empty($target_user['user_contact'])): ?>
                            <span><i class="fa fa-phone text-success me-1"></i> <?= esc($target_user['user_contact']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                        <?php if (!empty($target_user['user_region'])): ?>
                            <span class="aud-meta-badge"><i class="fa fa-map-marker-alt text-danger"></i> Region: <?= esc($target_user['user_region']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($target_user['user_cluster'])): ?>
                            <span class="aud-meta-badge"><i class="fa fa-cubes text-warning"></i> Cluster: <?= esc($target_user['user_cluster']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($target_user['user_location'])): ?>
                            <span class="aud-meta-badge"><i class="fa fa-building text-info"></i> <?= esc($target_user['user_location']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if ($is_admin && !empty($auditor_list)): ?>
                    <!-- Admin Auditor Switcher -->
                    <div class="d-flex align-items-center bg-white bg-opacity-10 rounded px-3 py-1">
                        <small class="text-white-50 me-2 text-nowrap"><i class="fa fa-user-shield me-1"></i> Auditor Preview:</small>
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
                <!-- 1. Yearly Filter Scope (Default Current Year) -->
                <div class="col-xl-2 col-md-3 col-sm-6">
                    <label class="form-label fs-7 fw-bold text-muted mb-1"><i class="fa fa-calendar-check text-primary me-1"></i> Report Year</label>
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
                <div class="col-xl-2 col-md-3 col-sm-6">
                    <label class="form-label fs-7 fw-bold text-muted mb-1"><i class="fa fa-calendar-alt text-info me-1"></i> Date Filter</label>
                    <select class="form-select form-select-sm form-select-solid" id="audFilterDateRange">
                        <option value="yearly" selected>Full Year</option>
                        <option value="30d">Last 30 Days</option>
                        <option value="90d">Last 3 Months</option>
                        <option value="180d">Last 6 Months</option>
                        <option value="custom">Custom Date Range...</option>
                    </select>
                </div>

                <!-- Custom Start & End Date (Initially hidden) -->
                <div class="col-xl-2 col-md-3 col-sm-6 d-none" id="customDateRangeBox">
                    <label class="form-label fs-7 fw-bold text-muted mb-1">From Date</label>
                    <input type="date" class="form-control form-control-sm form-control-solid" id="audFilterStartDate" value="<?= date('Y-01-01') ?>">
                </div>
                <div class="col-xl-2 col-md-3 col-sm-6 d-none" id="customEndDateBox">
                    <label class="form-label fs-7 fw-bold text-muted mb-1">To Date</label>
                    <input type="date" class="form-control form-control-sm form-control-solid" id="audFilterEndDate" value="<?= date('Y-m-d') ?>">
                </div>

                <!-- 3. Module Filter -->
                <div class="col-xl-2 col-md-3 col-sm-6">
                    <label class="form-label fs-7 fw-bold text-muted mb-1"><i class="fa fa-th-large text-warning me-1"></i> Module</label>
                    <select class="form-select form-select-sm form-select-solid" id="audFilterModule">
                        <option value="all" selected>All Modules</option>
                        <option value="OE">OE Audits (Monthly)</option>
                        <option value="HSE">HSE Audits (Quarterly)</option>
                        <option value="Gemba">Gemba Audits (Walks)</option>
                    </select>
                </div>

                <!-- 4. Status Filter -->
                <div class="col-xl-2 col-md-3 col-sm-6">
                    <label class="form-label fs-7 fw-bold text-muted mb-1"><i class="fa fa-tasks text-success me-1"></i> Schedule Status</label>
                    <select class="form-select form-select-sm form-select-solid" id="audFilterStatus">
                        <option value="all" selected>All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="due">Due Soon (15 Days)</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>

                <!-- 5. Action Buttons -->
                <div class="col-xl-2 col-md-3 col-sm-6 d-flex gap-2">
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
             MODULE NAVIGATION PILLS
        ======================================================== -->
        <div class="aud-module-tabs">
            <button type="button" class="aud-module-tab-btn active" data-target="all">
                <i class="fa fa-border-all"></i> All Modules Overview
            </button>
            <button type="button" class="aud-module-tab-btn" data-target="oe">
                <i class="fa fa-layer-group text-primary"></i> OE Audit Section
            </button>
            <button type="button" class="aud-module-tab-btn" data-target="hse">
                <i class="fa fa-shield-alt text-warning"></i> HSE Audit Section
            </button>
            <button type="button" class="aud-module-tab-btn" data-target="gemba">
                <i class="fa fa-walking text-success"></i> Gemba Audit Section
            </button>
            <button type="button" class="aud-module-tab-btn" data-target="nc">
                <i class="fa fa-clipboard-list text-purple"></i> NC Analytics
            </button>
            <button type="button" class="aud-module-tab-btn" data-target="schedule">
                <i class="fa fa-calendar-alt text-info"></i> Audit Schedule & Table
            </button>
        </div>

        <!-- ========================================================
             SECTION 3.A: OE AUDIT DASHBOARD (Monthly: 1 Month)
        ======================================================== -->
        <div class="module-section-box" id="sectionOE">
            <div class="aud-section-banner banner-oe">
                <div>
                    <h5 class="fw-bolder m-0 d-flex align-items-center gap-2">
                        <i class="fa fa-layer-group fs-4"></i> Section A: OE Audit Dashboard
                    </h5>
                    <small class="text-muted">Standard Frequency: <strong>1 Audit per Month (Monthly)</strong></small>
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

            <!-- OE 5 KPI Cards -->
            <div class="aud-kpi-grid aud-kpi-grid-5 mb-4">
                <!-- 1. Total OE Audits -->
                <div class="aud-kpi-card card-blue">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-layer-group"></i></div>
                        <span class="aud-kpi-badge bg-light-primary text-primary">All OE Records</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOeTotal"><?= number_format($initial_kpis['oe']['total_audits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Total OE Audits</div>
                </div>

                <!-- 2. Latest Performed OE Audit -->
                <div class="aud-kpi-card card-emerald">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-check-circle"></i></div>
                        <span class="aud-kpi-badge bg-light-success text-success">Latest Performed</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOeLatest"><?= number_format($initial_kpis['oe']['latest_performed'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Performed OE Audits</div>
                </div>

                <!-- 3. Previous OE Audits & Re-audits -->
                <div class="aud-kpi-card card-cyan">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-history"></i></div>
                        <span class="aud-kpi-badge bg-light-info text-info">History Chain</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOeReaudits"><?= number_format($initial_kpis['oe']['reaudits_performed'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Previous Audits / Re-Audits</div>
                </div>

                <!-- 4. Due & Overdue OE Audits -->
                <div class="aud-kpi-card card-rose">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-exclamation-triangle"></i></div>
                        <span class="aud-kpi-badge bg-light-danger text-danger" id="kpiOeDueBadge"><?= number_format($initial_kpis['oe']['due_audits'] ?? 0) ?> Due</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOeOverdue"><?= number_format($initial_kpis['oe']['overdue_audits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Overdue OE Audits</div>
                </div>

                <!-- 5. OE NC Actions -->
                <div class="aud-kpi-card card-purple">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-tasks"></i></div>
                        <span class="aud-kpi-badge bg-light-purple text-purple" id="kpiOeNcTotalBadge"><?= number_format($initial_kpis['oe']['total_ncs'] ?? 0) ?> NCs</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiOeNcPending"><?= number_format($initial_kpis['oe']['pending_actions'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Pending OE NC Actions</div>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 3.B: HSE AUDIT DASHBOARD (Quarterly: 3 Months)
        ======================================================== -->
        <div class="module-section-box" id="sectionHSE">
            <div class="aud-section-banner banner-hse">
                <div>
                    <h5 class="fw-bolder m-0 d-flex align-items-center gap-2">
                        <i class="fa fa-shield-alt fs-4"></i> Section B: HSE Audit Dashboard
                    </h5>
                    <small class="text-muted">Standard Frequency: <strong>1 Audit every Three Months (Quarterly)</strong></small>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-white text-orange border shadow-sm px-3 py-2 fw-bold" id="hseLastDateBadge">
                        <i class="fa fa-calendar-check me-1"></i> Last Audit: <?= esc($initial_kpis['hse']['last_audit_date'] ?? '-') ?>
                    </span>
                    <span class="badge bg-warning text-white shadow-sm px-3 py-2 fw-bold" id="hseNextDateBadge">
                        <i class="fa fa-clock me-1"></i> Next Due: <?= esc($initial_kpis['hse']['next_audit_date'] ?? '-') ?>
                    </span>
                </div>
            </div>

            <!-- HSE 5 KPI Cards -->
            <div class="aud-kpi-grid aud-kpi-grid-5 mb-4">
                <!-- 1. Total HSE Audits -->
                <div class="aud-kpi-card card-orange">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-shield-alt"></i></div>
                        <span class="aud-kpi-badge bg-light-warning text-orange">All HSE Records</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiHseTotal"><?= number_format($initial_kpis['hse']['total_audits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Total HSE Audits</div>
                </div>

                <!-- 2. Latest Performed HSE Audits -->
                <div class="aud-kpi-card card-emerald">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-check-double"></i></div>
                        <span class="aud-kpi-badge bg-light-success text-success">Latest Performed</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiHseLatest"><?= number_format($initial_kpis['hse']['latest_performed'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Performed HSE Audits</div>
                </div>

                <!-- 3. Previous HSE Audits & Re-audits -->
                <div class="aud-kpi-card card-cyan">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-sync-alt"></i></div>
                        <span class="aud-kpi-badge bg-light-info text-info">Cycle Revisions</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiHseReaudits"><?= number_format($initial_kpis['hse']['reaudits_performed'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Previous Audits / Re-Audits</div>
                </div>

                <!-- 4. Due & Overdue HSE Audits -->
                <div class="aud-kpi-card card-rose">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-exclamation-circle"></i></div>
                        <span class="aud-kpi-badge bg-light-danger text-danger" id="kpiHseDueBadge"><?= number_format($initial_kpis['hse']['due_audits'] ?? 0) ?> Due</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiHseOverdue"><?= number_format($initial_kpis['hse']['overdue_audits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Overdue HSE Audits</div>
                </div>

                <!-- 5. HSE NC Actions -->
                <div class="aud-kpi-card card-purple">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-notes-medical"></i></div>
                        <span class="aud-kpi-badge bg-light-purple text-purple" id="kpiHseNcTotalBadge"><?= number_format($initial_kpis['hse']['total_ncs'] ?? 0) ?> NCs</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiHseNcPending"><?= number_format($initial_kpis['hse']['pending_actions'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Pending HSE NC Actions</div>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 3.C: GEMBA AUDIT DASHBOARD (Walk / Event Workflow)
        ======================================================== -->
        <div class="module-section-box" id="sectionGemba">
            <div class="aud-section-banner banner-gemba">
                <div>
                    <h5 class="fw-bolder m-0 d-flex align-items-center gap-2">
                        <i class="fa fa-walking fs-4"></i> Section C: Gemba Audit Dashboard
                    </h5>
                    <small class="text-muted">Standard Frequency: <strong>Walk-Based / Event-Driven Workflow</strong></small>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-white text-success border shadow-sm px-3 py-2 fw-bold" id="gembaLastDateBadge">
                        <i class="fa fa-calendar-check me-1"></i> Last Walk: <?= esc($initial_kpis['gemba']['last_audit_date'] ?? '-') ?>
                    </span>
                    <span class="badge bg-success text-white shadow-sm px-3 py-2 fw-bold">
                        <i class="fa fa-route me-1"></i> Cycle: Workflow Driven
                    </span>
                </div>
            </div>

            <!-- Gemba 5 KPI Cards -->
            <div class="aud-kpi-grid aud-kpi-grid-5 mb-4">
                <!-- 1. Total Gemba Walks -->
                <div class="aud-kpi-card card-teal">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-walking"></i></div>
                        <span class="aud-kpi-badge bg-light-success text-teal">Site Walks</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaTotal"><?= number_format($initial_kpis['gemba']['total_audits'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Total Gemba Walks</div>
                </div>

                <!-- 2. Total Observations / NC Points -->
                <div class="aud-kpi-card card-blue">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-list-ul"></i></div>
                        <span class="aud-kpi-badge bg-light-primary text-primary">All Points</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaTotalNc"><?= number_format($initial_kpis['gemba']['total_ncs'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Total Observation Points</div>
                </div>

                <!-- 3. Open & Working Points -->
                <div class="aud-kpi-card card-amber">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-hourglass-half"></i></div>
                        <span class="aud-kpi-badge bg-light-warning text-warning">Active Floor</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaOpen"><?= number_format(($initial_kpis['gemba']['open_ncs'] ?? 0) + ($initial_kpis['gemba']['working_ncs'] ?? 0)) ?></div>
                    <div class="aud-kpi-label">Open / Working Actions</div>
                </div>

                <!-- 4. Overdue Gemba Actions -->
                <div class="aud-kpi-card card-rose">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-calendar-times"></i></div>
                        <span class="aud-kpi-badge bg-light-danger text-danger">Target Past</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaOverdue"><?= number_format($initial_kpis['gemba']['overdue_actions'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Overdue Action Points</div>
                </div>

                <!-- 5. Completed Gemba Points -->
                <div class="aud-kpi-card card-emerald">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="aud-kpi-icon-wrap"><i class="fa fa-check-double"></i></div>
                        <span class="aud-kpi-badge bg-light-success text-success">Verified Closed</span>
                    </div>
                    <div class="aud-kpi-value" id="kpiGembaClosed"><?= number_format($initial_kpis['gemba']['closed_ncs'] ?? 0) ?></div>
                    <div class="aud-kpi-label">Completed Action Points</div>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 4: 4 AUDIT PERFORMANCE GRAPHS
        ======================================================== -->

        <!-- ROW 1: Graph 1 (Performed vs Pending) & Graph 2 (Due & Overdue Status) -->
        <div class="row g-4 mb-4">
            <!-- Graph 1: Performed vs Pending Audits (Grouped by Module) -->
            <div class="col-xl-7 col-lg-7">
                <div class="aud-chart-card position-relative" id="cardChartPerformedPending">
                    <div class="card-header">
                        <h4 class="aud-chart-title">
                            <i class="fa fa-chart-bar text-primary"></i> Graph 1: Performed vs Pending Audits
                        </h4>
                        <span class="badge bg-light text-primary fs-8 fw-bold">OE vs HSE vs Gemba</span>
                    </div>
                    <div class="aud-chart-body">
                        <div id="chartPerformedVsPending" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>

            <!-- Graph 2: Audit Due and Overdue Status -->
            <div class="col-xl-5 col-lg-5">
                <div class="aud-chart-card position-relative" id="cardChartDueStatus">
                    <div class="card-header">
                        <h4 class="aud-chart-title">
                            <i class="fa fa-clock text-warning"></i> Graph 2: Audit Due & Overdue Status
                        </h4>
                        <span class="badge bg-light text-muted fs-8">Schedule Distribution</span>
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
                                <i class="fa fa-chart-line text-info"></i> Graph 3: Monthly Audit Trend (Per Yearly)
                            </h4>
                            <small class="text-muted fs-8">Audits performed across Jan – Dec of selected year</small>
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
                            <i class="fa fa-sync-alt text-success"></i> Graph 4: Re-Audit Analysis
                        </h4>
                        <span class="badge bg-light-success text-success fs-8 fw-bold">Latest vs Re-Audits</span>
                    </div>
                    <div class="aud-chart-body">
                        <div id="chartReauditAnalysis" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 5: DEDICATED NC ANALYTICS SECTION
        ======================================================== -->
        <div class="module-section-box mb-4" id="sectionNC">
            <div class="aud-section-banner" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border-left: 5px solid #7c3aed; color: #5b21b6;">
                <div>
                    <h5 class="fw-bolder m-0 d-flex align-items-center gap-2">
                        <i class="fa fa-clipboard-check fs-4"></i> Section 5: NC Analytics & Action Progress
                    </h5>
                    <small class="text-muted">Module-level Non-Conformance status breakdown and action item tracking</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-white text-purple border shadow-sm px-3 py-2 fw-bold" id="totalNcActionsBadge">
                        Total Closed: <?= number_format($initial_kpis['summary']['nc_actions_completed'] ?? 0) ?>
                    </span>
                </div>
            </div>

            <div class="row g-4">
                <!-- NC Status Distribution Chart -->
                <div class="col-lg-7">
                    <div class="aud-chart-card">
                        <div class="card-header">
                            <h4 class="aud-chart-title">
                                <i class="fa fa-chart-bar text-purple"></i> NC Status Distribution by Module
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
                                <i class="fa fa-pie-chart text-emerald"></i> NC Action Progress Rate
                            </h4>
                            <span class="badge bg-light text-muted fs-8">Action Lifecycle</span>
                        </div>
                        <div class="aud-chart-body d-flex align-items-center justify-content-center">
                            <div id="chartNcActionProgress" style="width: 100%; min-height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 6: AUDIT SCHEDULE AND REPORT TABLE
        ======================================================== -->
        <div class="aud-chart-card mb-4" id="sectionScheduleTable">
            <div class="card-header">
                <div>
                    <h4 class="aud-chart-title">
                        <i class="fa fa-table text-primary"></i> Section 6: Site Audit Schedule & Status Report
                    </h4>
                    <small class="text-muted fs-8">Site-level audit schedule, latest dates, module frequency, and action items</small>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="text" class="form-control form-control-sm form-control-solid" id="searchScheduleTable" placeholder="Search client, site..." style="max-width: 220px;">
                    <div class="btn-group-pill" id="scheduleTableModulePills">
                        <button type="button" class="btn active" data-mod="all">All</button>
                        <button type="button" class="btn" data-mod="OE">OE</button>
                        <button type="button" class="btn" data-mod="HSE">HSE</button>
                        <button type="button" class="btn" data-mod="Gemba">Gemba</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="aud-table-wrap">
                    <table class="aud-table" id="auditScheduleDataTable">
                        <thead>
                            <tr>
                                <th>Module</th>
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

                <!-- Table Footer & Pagination -->
                <div class="d-flex align-items-center justify-content-between p-3 border-top flex-wrap gap-2">
                    <small class="text-muted" id="scheduleTableCountInfo">Showing 0 of 0 site schedules</small>
                    <div class="btn-group" id="scheduleTablePagination">
                        <!-- Dynamic pagination buttons -->
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

    let scheduleTableRecords = [];
    let tableCurrentPage = 1;
    const tablePageSize = 10;
    let activeTableModFilter = 'all';

    // Toggle custom date pickers
    $('#audFilterDateRange').on('change', function () {
        if ($(this).val() === 'custom') {
            $('#customDateRangeBox, #customEndDateBox').removeClass('d-none');
        } else {
            $('#customDateRangeBox, #customEndDateBox').addClass('d-none');
        }
    });

    // Module Tab Switcher
    $('.aud-module-tab-btn').on('click', function () {
        $('.aud-module-tab-btn').removeClass('active');
        $(this).addClass('active');
        const target = $(this).data('target');

        if (target === 'all') {
            $('.module-section-box, #cardChartPerformedPending, #cardChartDueStatus, #cardChartMonthlyTrend, #cardChartReaudit, #sectionNC, #sectionScheduleTable').fadeIn(200);
            $('#audFilterModule').val('all');
        } else if (target === 'oe') {
            $('#sectionOE').fadeIn(200);
            $('#sectionHSE, #sectionGemba').hide();
            $('#audFilterModule').val('OE');
            $('html, body').animate({ scrollTop: $("#sectionOE").offset().top - 80 }, 400);
        } else if (target === 'hse') {
            $('#sectionHSE').fadeIn(200);
            $('#sectionOE, #sectionGemba').hide();
            $('#audFilterModule').val('HSE');
            $('html, body').animate({ scrollTop: $("#sectionHSE").offset().top - 80 }, 400);
        } else if (target === 'gemba') {
            $('#sectionGemba').fadeIn(200);
            $('#sectionOE, #sectionHSE').hide();
            $('#audFilterModule').val('Gemba');
            $('html, body').animate({ scrollTop: $("#sectionGemba").offset().top - 80 }, 400);
        } else if (target === 'nc') {
            $('html, body').animate({ scrollTop: $("#sectionNC").offset().top - 80 }, 400);
        } else if (target === 'schedule') {
            $('html, body').animate({ scrollTop: $("#sectionScheduleTable").offset().top - 80 }, 400);
        }
    });

    // Apply & Reset Filters
    $('#btnApplyAudFilters').on('click', function () {
        loadAuditorAnalytics();
    });

    $('#btnResetAudFilters').on('click', function () {
        $('#audFilterYear').val('<?= date('Y') ?>');
        $('#audFilterDateRange').val('yearly').trigger('change');
        $('#audFilterModule').val('all');
        $('#audFilterStatus').val('all');
        $('.aud-module-tab-btn').removeClass('active');
        $('.aud-module-tab-btn[data-target="all"]').addClass('active');
        $('.module-section-box').show();
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
            module: $('#audFilterModule').val(),
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

                    // Section 5: NC Analytics
                    renderNcAnalytics(res.charts.nc_analytics);

                    // Section 6: Audit Schedule Table
                    scheduleTableRecords = res.schedule_table || [];
                    tableCurrentPage = 1;
                    renderScheduleTable();
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

        // OE Metrics
        if (k.oe) {
            $('#kpiOeTotal').text(nf(k.oe.total_audits));
            $('#kpiOeLatest').text(nf(k.oe.latest_performed));
            $('#kpiOeReaudits').text(nf(k.oe.reaudits_performed));
            $('#kpiOeOverdue').text(nf(k.oe.overdue_audits));
            $('#kpiOeDueBadge').text(nf(k.oe.due_audits) + ' Due');
            $('#kpiOeNcPending').text(nf(k.oe.pending_actions));
            $('#kpiOeNcTotalBadge').text(nf(k.oe.total_ncs) + ' NCs');
            if (k.oe.last_audit_date && k.oe.last_audit_date !== '-') {
                $('#oeLastDateBadge').html('<i class="fa fa-calendar-check me-1"></i> Last Audit: ' + k.oe.last_audit_date);
            }
            if (k.oe.next_audit_date && k.oe.next_audit_date !== '-') {
                $('#oeNextDateBadge').html('<i class="fa fa-clock me-1"></i> Next Due: ' + k.oe.next_audit_date);
            }
        }

        // HSE Metrics
        if (k.hse) {
            $('#kpiHseTotal').text(nf(k.hse.total_audits));
            $('#kpiHseLatest').text(nf(k.hse.latest_performed));
            $('#kpiHseReaudits').text(nf(k.hse.reaudits_performed));
            $('#kpiHseOverdue').text(nf(k.hse.overdue_audits));
            $('#kpiHseDueBadge').text(nf(k.hse.due_audits) + ' Due');
            $('#kpiHseNcPending').text(nf(k.hse.pending_actions));
            $('#kpiHseNcTotalBadge').text(nf(k.hse.total_ncs) + ' NCs');
            if (k.hse.last_audit_date && k.hse.last_audit_date !== '-') {
                $('#hseLastDateBadge').html('<i class="fa fa-calendar-check me-1"></i> Last Audit: ' + k.hse.last_audit_date);
            }
            if (k.hse.next_audit_date && k.hse.next_audit_date !== '-') {
                $('#hseNextDateBadge').html('<i class="fa fa-clock me-1"></i> Next Due: ' + k.hse.next_audit_date);
            }
        }

        // Gemba Metrics
        if (k.gemba) {
            $('#kpiGembaTotal').text(nf(k.gemba.total_audits));
            $('#kpiGembaTotalNc').text(nf(k.gemba.total_ncs));
            $('#kpiGembaOpen').text(nf((k.gemba.open_ncs || 0) + (k.gemba.working_ncs || 0)));
            $('#kpiGembaOverdue').text(nf(k.gemba.overdue_actions));
            $('#kpiGembaClosed').text(nf(k.gemba.closed_ncs));
            if (k.gemba.last_audit_date && k.gemba.last_audit_date !== '-') {
                $('#gembaLastDateBadge').html('<i class="fa fa-calendar-check me-1"></i> Last Walk: ' + k.gemba.last_audit_date);
            }
        }

        if (k.summary) {
            $('#totalNcActionsBadge').text('Total Closed: ' + nf(k.summary.nc_actions_completed));
        }
    }

    function nf(val) {
        return (val || 0).toLocaleString();
    }

    /* ── Graph 1: Performed vs Pending Audits ── */
    function renderGraph1PerformedPending(data) {
        if (!data) return;
        const options = {
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'Poppins, sans-serif'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 6
                }
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            series: data.series || [],
            xaxis: {
                categories: data.categories || ['OE Audits', 'HSE Audits', 'Gemba Audits'],
                labels: { style: { colors: '#64748b', fontSize: '12px', fontWeight: 600 } }
            },
            yaxis: {
                labels: { style: { colors: '#64748b', fontSize: '11px' } },
                forceNiceScale: true
            },
            colors: ['#2563eb', '#f59e0b', '#ef4444'],
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

    /* ── Graph 2: Audit Due & Overdue Status ── */
    function renderGraph2DueStatus(data) {
        if (!data) return;
        const labels = data.labels || ['Completed', 'Upcoming', 'Due Soon', 'Overdue'];
        const series = data.series || [0, 0, 0, 0];
        const total = series.reduce((a, b) => a + b, 0);

        const options = {
            chart: {
                type: 'donut',
                height: 320,
                fontFamily: 'Poppins, sans-serif'
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
                                label: 'Total Tracked',
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
                y: { formatter: function (val) { return val + " Audits"; } }
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
                fontFamily: 'Poppins, sans-serif'
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
            colors: ['#2563eb', '#ea580c', '#10b981'],
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
        const options = {
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'Poppins, sans-serif'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '45%',
                    borderRadius: 6
                }
            },
            series: data.series || [],
            xaxis: {
                categories: data.categories || ['OE Audits', 'HSE Audits'],
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

    /* ── Section 5: NC Analytics Charts ── */
    function renderNcAnalytics(data) {
        if (!data) return;

        // NC Status Distribution (Stacked Bar)
        const distData = data.distribution || {};
        const distOptions = {
            chart: {
                type: 'bar',
                height: 300,
                stacked: true,
                toolbar: { show: false },
                fontFamily: 'Poppins, sans-serif'
            },
            plotOptions: { bar: { horizontal: false, columnWidth: '50%', borderRadius: 4 } },
            series: distData.series || [],
            xaxis: {
                categories: distData.categories || ['OE Module', 'HSE Module', 'Gemba Module'],
                labels: { style: { colors: '#64748b', fontSize: '12px', fontWeight: 600 } }
            },
            yaxis: { labels: { style: { colors: '#64748b', fontSize: '11px' } }, forceNiceScale: true },
            colors: ['#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6', '#10b981'],
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px' },
            tooltip: { y: { formatter: function (val) { return val + " NC Points"; } } }
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
            labels: actionData.labels || ['Completed Actions', 'Pending Review', 'Overdue Actions'],
            series: totalActions > 0 ? actionData.series : [1],
            colors: totalActions > 0 ? ['#10b981', '#8b5cf6', '#f43f5e'] : ['#e2e8f0'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Actions',
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
                y: { formatter: function (val) { return val + " Actions"; } }
            }
        };

        if (chartNcActionInst) {
            chartNcActionInst.updateOptions(actionOptions);
        } else {
            chartNcActionInst = new ApexCharts(document.querySelector("#chartNcActionProgress"), actionOptions);
            chartNcActionInst.render();
        }
    }

    /* ── Section 6: Audit Schedule Table ── */
    $('#scheduleTableModulePills button').on('click', function () {
        $('#scheduleTableModulePills button').removeClass('active');
        $(this).addClass('active');
        activeTableModFilter = $(this).data('mod');
        tableCurrentPage = 1;
        renderScheduleTable();
    });

    $('#searchScheduleTable').on('input', function () {
        tableCurrentPage = 1;
        renderScheduleTable();
    });

    function renderScheduleTable() {
        const query = ($('#searchScheduleTable').val() || '').toLowerCase().trim();
        let filtered = scheduleTableRecords.filter(function (row) {
            if (activeTableModFilter !== 'all' && row.module !== activeTableModFilter) {
                return false;
            }
            if (query !== '') {
                const searchCorpus = (row.module + ' ' + row.client_name + ' ' + row.site_name + ' ' + row.audit_status + ' ' + row.audit_type).toLowerCase();
                if (!searchCorpus.includes(query)) return false;
            }
            return true;
        });

        const totalItems = filtered.length;
        const totalPages = Math.ceil(totalItems / tablePageSize) || 1;
        if (tableCurrentPage > totalPages) tableCurrentPage = totalPages;

        const startIdx = (tableCurrentPage - 1) * tablePageSize;
        const pageItems = filtered.slice(startIdx, startIdx + tablePageSize);

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
            let modBadgeClass = 'bg-light-primary text-primary';
            if (r.module === 'HSE') modBadgeClass = 'bg-light-warning text-orange';
            if (r.module === 'Gemba') modBadgeClass = 'bg-light-success text-success';

            let statusBadgeClass = 'badge-light-warning text-warning';
            if (r.audit_status === 'Overdue') statusBadgeClass = 'badge-light-danger text-danger';
            if (r.audit_status === 'Upcoming') statusBadgeClass = 'badge-light-primary text-primary';
            if (r.audit_status === 'Due') statusBadgeClass = 'badge-light-warning text-warning';
            if (r.audit_status === 'Completed') statusBadgeClass = 'badge-light-success text-success';

            tbody.append(`
                <tr>
                    <td><span class="badge ${modBadgeClass} fw-bold px-2 py-1">${escapeHtml(r.module)}</span></td>
                    <td class="fw-semibold text-dark">${escapeHtml(r.client_name)}</td>
                    <td class="text-muted">${escapeHtml(r.site_name)}</td>
                    <td class="text-nowrap text-muted">${escapeHtml(r.latest_audit_date)}</td>
                    <td class="text-nowrap text-muted">${escapeHtml(r.report_date)}</td>
                    <td class="text-nowrap fw-bold ${r.audit_status === 'Overdue' ? 'text-danger' : 'text-dark'}">${escapeHtml(r.next_audit_date)}</td>
                    <td class="text-nowrap fs-8"><span class="badge bg-light text-dark">${escapeHtml(r.audit_frequency)}</span></td>
                    <td class="text-nowrap fs-8 text-muted">${escapeHtml(r.audit_type)}</td>
                    <td><span class="badge ${statusBadgeClass} fw-bold px-2 py-1">${escapeHtml(r.audit_status)}</span></td>
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
        const displayEnd = Math.min(startIdx + tablePageSize, totalItems);
        $('#scheduleTableCountInfo').text(`Showing ${displayStart} to ${displayEnd} of ${totalItems} site schedules`);

        // Pagination buttons
        const pagin = $('#scheduleTablePagination');
        pagin.empty();
        if (totalPages > 1) {
            pagin.append(`
                <button type="button" class="btn btn-sm btn-light ${tableCurrentPage === 1 ? 'disabled' : ''}" id="btnSchedPrev">
                    <i class="fa fa-chevron-left"></i>
                </button>
            `);
            for (let p = 1; p <= totalPages; p++) {
                if (totalPages > 6 && Math.abs(p - tableCurrentPage) > 2 && p !== 1 && p !== totalPages) {
                    if (p === 2 || p === totalPages - 1) pagin.append(`<span class="btn btn-sm btn-light disabled">...</span>`);
                    continue;
                }
                pagin.append(`
                    <button type="button" class="btn btn-sm ${p === tableCurrentPage ? 'btn-primary' : 'btn-light'} btn-sched-page" data-page="${p}">${p}</button>
                `);
            }
            pagin.append(`
                <button type="button" class="btn btn-sm btn-light ${tableCurrentPage === totalPages ? 'disabled' : ''}" id="btnSchedNext">
                    <i class="fa fa-chevron-right"></i>
                </button>
            `);

            $('#btnSchedPrev').on('click', function () { if (tableCurrentPage > 1) { tableCurrentPage--; renderScheduleTable(); } });
            $('#btnSchedNext').on('click', function () { if (tableCurrentPage < totalPages) { tableCurrentPage++; renderScheduleTable(); } });
            $('.btn-sched-page').on('click', function () { tableCurrentPage = parseInt($(this).data('page')); renderScheduleTable(); });
        }
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