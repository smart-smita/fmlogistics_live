<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<li class="breadcrumb-item text-muted">
    <a href="<?= base_url('Masters/GembaNcTracker') ?>" class="text-muted text-hover-primary">Gemba HSE NC Tracker</a>
</li>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<style>
    .managed-hse-badge{
    background:#6f42c1 !important;
    color:#fff !important;
    border-radius:20px;
    padding:6px 14px;
    font-size:12px;
    font-weight:600;
    transition:all .25s ease;
}

.managed-hse-badge:hover,
.managed-hse-badge:focus,
.managed-hse-badge:active{
    background:#5b34a3 !important;
    color:#fff !important;
    text-decoration:none !important;
    box-shadow:0 2px 8px rgba(111,66,193,.35);
}

.managed-hse-badge:visited{
    color:#fff !important;
}
</style>

<!-- Filter Section -->
<div class="card mb-5 mb-xl-10 shadow-sm border-0 rounded-4">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1">Gemba HSE NC Tracker Filters</span>
        </h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light-primary me-3" id="reset_filters">
                <i class="fas fa-sync-alt me-2"></i>Reset
            </button>
            <!-- <a href="javascript:void(0)" onclick="exportToExcel()" class="btn btn-sm btn-success me-2">
                <i class="fas fa-file-excel me-2"></i>Export Excel
            </a> -->
            <a href="javascript:void(0)" onclick="exportToCSV()" class="btn btn-sm btn-info" style="background-color: #6f42c1; border-color: #6f42c1;">
                <i class="fas fa-file-csv me-2"></i>Export CSV
            </a>
        </div>
    </div>
    <div class="card-body py-3">
        <form id="filter_form">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">Region</label>
                    <select name="region[]" id="region" class="form-select select2-filter" multiple="multiple"
                        data-control="select2" data-placeholder="All Regions">
                        <?php foreach ($regions as $r): ?>
                            <option value="<?= $r['region'] ?>"><?= $r['region'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">Cluster</label>
                    <select name="cluster[]" id="cluster" class="form-select select2-filter" multiple="multiple"
                        data-control="select2" data-placeholder="All Clusters">
                        <?php foreach ($clusters as $c): ?>
                            <option value="<?= htmlspecialchars($c['cluster']) ?>"><?= htmlspecialchars($c['cluster']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">Audit Category</label>
                    <select name="audit_category[]" class="form-select select2-filter" multiple="multiple"
                        data-control="select2" data-placeholder="All Categories">
                        <?php foreach ($audit_categories as $a): ?>
                            <option value="<?= htmlspecialchars($a['audit_category']) ?>">
                                <?= htmlspecialchars($a['audit_category']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">Site Category</label>
                    <select name="site_category[]" id="site_category" class="form-select select2-filter"
                        multiple="multiple" data-control="select2" data-placeholder="All Categories">
                        <?php foreach ($site_categories as $sc): ?>
                            <option value="<?= htmlspecialchars($sc['site_category']) ?>">
                                <?= htmlspecialchars($sc['site_category']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">Site Name</label>
                    <select name="site_name[]" id="site_name" class="form-select select2-filter" multiple="multiple"
                        data-control="select2" data-placeholder="All Sites">
                        <?php foreach ($site_names as $sn): ?>
                            <option value="<?= htmlspecialchars($sn['site_name']) ?>">
                                <?= htmlspecialchars($sn['site_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">Point Status</label>
                    <select name="point_status[]" class="form-select select2-filter" multiple="multiple"
                        data-control="select2" data-placeholder="All Status">
                        <option value="Open">Open</option>
                        <option value="WIP">WIP</option>
                        <option value="Closed">Closed</option>
                        <option value="Excluded">Excluded</option>
                        <option value="Hold-review with Client">Hold-review with Client</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">NC Type</label>
                    <select name="nc_recommendation[]" class="form-select select2-filter" multiple="multiple"
                        data-control="select2" data-placeholder="All Types">
                        <?php foreach ($nc_types as $n): ?>
                            <option value="<?= htmlspecialchars($n['nc_recommendation']) ?>">
                                <?= htmlspecialchars($n['nc_recommendation']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" id="apply_filter">
                        <i class="fas fa-filter me-2"></i> Show
                    </button>
                </div>
            </div>
            <input type="hidden" name="nc_status" id="hidden_nc_status" value="<?= htmlspecialchars($nc_status ?? '') ?>">
        </form>
    </div>
</div>

<style>
    /* Fix table text wrapping for long content without breaking layout */
    .dataTables_wrapper .table.dataTable td {
        white-space: normal !important;
        word-wrap: break-word !important;
        word-break: break-word !important;
        min-width: 120px;
        vertical-align: middle;
    }

    /* Long text columns: Observation, Recommendation Action, Working Remarks, Closing Remarks, etc. */
    .dataTables_wrapper .table.dataTable th.long-text-column,
    .dataTables_wrapper .table.dataTable td.long-text-column {
        min-width: 300px !important;
        width: 380px !important;
        max-width: 550px !important;
        white-space: normal !important;
        word-wrap: break-word !important;
        word-break: break-word !important;
    }
    
    /* Keep action column and bulk select checkbox tight & make them sticky */
    #gemba_nc_tracker_table_wrapper .table.dataTable td:first-child,
    #gemba_nc_tracker_table_wrapper .table.dataTable th:first-child {
        white-space: nowrap !important;
        width: 40px !important;
        min-width: 40px !important;
        max-width: 40px !important;
        position: sticky !important;
        left: 0 !important;
        z-index: 2 !important;
        background-color: #fff !important;
    }

    #gemba_nc_tracker_table_wrapper .table.dataTable td:nth-child(2),
    #gemba_nc_tracker_table_wrapper .table.dataTable th:nth-child(2) {
        white-space: nowrap !important;
        width: 100px !important;
        min-width: 100px !important;
        max-width: 100px !important;
        position: sticky !important;
        left: 40px !important; /* Offset by the width of the first column */
        z-index: 2 !important;
        background-color: #fff !important;
        box-shadow: inset -1px 0 0 #e2e8f0 !important; /* Use inset shadow for seamless border */
    }

    /* Keep headers above body rows */
    #gemba_nc_tracker_table_wrapper .table.dataTable thead th:first-child,
    #gemba_nc_tracker_table_wrapper .table.dataTable thead th:nth-child(2) {
        z-index: 3 !important;
        background-color: #fff !important;
    }

    /* Support for hover and striped rows if needed */
    #gemba_nc_tracker_table_wrapper .table.dataTable tbody tr:hover td:first-child,
    #gemba_nc_tracker_table_wrapper .table.dataTable tbody tr:hover td:nth-child(2) {
        background-color: #f5f5f5;
    }
    #gemba_nc_tracker_table_wrapper .table.dataTable.table-striped tbody tr:nth-of-type(odd) td:first-child,
    #gemba_nc_tracker_table_wrapper .table.dataTable.table-striped tbody tr:nth-of-type(odd) td:nth-child(2) {
        background-color: #f9f9f9;
    }

    /* Ensure headers don't wrap awkwardly */
    #gemba_nc_tracker_table_wrapper .table.dataTable th {
        white-space: nowrap !important;
        vertical-align: middle;
    }

    /* Redesigned Tracker Cards styling */
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
</style>

<?php
$activeStatus = $nc_status ?? null;
$qs = http_build_query(array_filter([
    'region' => $filters['region'] ?? null,
    'audit_category' => $filters['audit_category'] ?? null,
    'site_category' => $filters['site_category'] ?? null,
    'site_name' => $filters['site_name'] ?? null,
    'point_status' => $filters['point_status'] ?? null,
    'nc_recommendation' => $filters['nc_recommendation'] ?? null,
]));
$qsStr = $qs ? '?' . $qs : '';

$totalActive = ($activeStatus === null || $activeStatus === '') ? 'active-filter' : '';
$openActive = ($activeStatus === '0' || $activeStatus === 0) ? 'active-filter' : '';
$workingActive = ($activeStatus === '1' || $activeStatus === 1) ? 'active-filter' : '';
$clusterActive = ($activeStatus === '5' || $activeStatus === 5) ? 'active-filter' : '';
$auditorActive = ($activeStatus === '2' || $activeStatus === 2) ? 'active-filter' : '';
$closedActive = ($activeStatus === '3' || $activeStatus === 3) ? 'active-filter' : '';
?>

<!-- Summary Cards for Workflow -->
<div class="tracker-cards-container" id="tracker-cards">
    <div class="row g-4">
        <!-- Total Observation Points -->
        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-6">
            <a href="javascript:void(0);" onclick="applyCardFilter('', this)" class="tracker-card <?= $totalActive ?>" style="--status-color: #4f46e5; --light-bg: #f5f3ff; --shadow-color: rgba(79, 70, 229, 0.25);" title="">
                <div class="card-top">
                    <div class="icon-wrapper">
                        <i class="fas fa-clipboard-list" style="font-size: 24px;"></i>
                    </div>
                    <h3 class="card-value" id="stat_total"><?= number_format($stats['total'] ?? 0) ?></h3>
                </div>
                <div class="card-body-content">
                    <div class="card-title-text" title="Total Observation Points">Total Observation Points</div>
                    <div class="card-subtitle-text" title="Overall NC observations">Overall NC observations</div>
                </div>
            </a>
        </div>
        <!-- Closed -->
        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-6">
            <a href="javascript:void(0);" onclick="applyCardFilter('3', this)" class="tracker-card <?= $closedActive ?>" style="--status-color: #22c55e; --light-bg: #f0fdf4; --shadow-color: rgba(34, 197, 94, 0.25);" title="">
                <div class="card-top">
                    <div class="icon-wrapper">
                        <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                    </div>
                    <h3 class="card-value" id="stat_wf_closed"><?= number_format($stats['workflow_closed'] ?? 0) ?></h3>
                </div>
                <div class="card-body-content">
                    <div class="card-title-text" title="Closed">Closed</div>
                    <div class="card-subtitle-text" title="Successfully Closed">Successfully Closed</div>
                </div>
            </a>
        </div>
        <!-- Open -->
        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-6">
            <a href="javascript:void(0);" onclick="applyCardFilter('0', this)" class="tracker-card <?= $openActive ?>" style="--status-color: #ef4444; --light-bg: #fef2f2; --shadow-color: rgba(239, 68, 68, 0.25);" title="">
                <div class="card-top">
                    <div class="icon-wrapper">
                        <i class="fas fa-exclamation-circle" style="font-size: 24px;"></i>
                    </div>
                    <h3 class="card-value" id="stat_wf_open"><?= number_format($stats['workflow_open'] ?? 0) ?></h3>
                </div>
                <div class="card-body-content">
                    <div class="card-title-text" title="Open">Open</div>
                    <div class="card-subtitle-text" title="Pending action">Pending action</div>
                </div>
            </a>
        </div>
        <!-- Working -->
        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-6">
            <a href="javascript:void(0);" onclick="applyCardFilter('1', this)" class="tracker-card <?= $workingActive ?>" style="--status-color: #f97316; --light-bg: #fff7ed; --shadow-color: rgba(249, 115, 22, 0.25);" title="">
                <div class="card-top">
                    <div class="icon-wrapper">
                        <i class="fas fa-circle-notch fa-spin" style="font-size: 24px;"></i>
                    </div>
                    <h3 class="card-value" id="stat_wf_working"><?= number_format($stats['workflow_working'] ?? 0) ?></h3>
                </div>
                <div class="card-body-content">
                    <div class="card-title-text" title="Working">Working</div>
                    <div class="card-subtitle-text" title="Under progress">Under progress</div>
                </div>
            </a>
        </div>
        <!-- Cluster Review -->
        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-6">
            <a href="javascript:void(0);" onclick="applyCardFilter('5', this)" class="tracker-card <?= $clusterActive ?>" style="--status-color: #3b82f6; --light-bg: #eff6ff; --shadow-color: rgba(59, 130, 246, 0.25);" title="">
                <div class="card-top">
                    <div class="icon-wrapper">
                        <i class="fas fa-users" style="font-size: 24px;"></i>
                    </div>
                    <h3 class="card-value" id="stat_wf_cluster"><?= number_format($stats['workflow_cluster_review'] ?? 0) ?></h3>
                </div>
                <div class="card-body-content">
                    <div class="card-title-text" title="Cluster Review">Cluster Review</div>
                    <div class="card-subtitle-text" title="Awaiting Cluster Review">Awaiting Cluster Review</div>
                </div>
            </a>
        </div>
        <!-- Auditor Review -->
        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-6">
            <a href="javascript:void(0);" onclick="applyCardFilter('2', this)" class="tracker-card <?= $auditorActive ?>" style="--status-color: #06b6d4; --light-bg: #ecfeff; --shadow-color: rgba(6, 182, 212, 0.25);" title="">
                <div class="card-top">
                    <div class="icon-wrapper">
                        <i class="fas fa-shield-alt" style="font-size: 24px;"></i>
                    </div>
                    <h3 class="card-value" id="stat_wf_auditor"><?= number_format($stats['workflow_auditor_review'] ?? 0) ?></h3>
                </div>
                <div class="card-body-content">
                    <div class="card-title-text" title="Auditor Review">Auditor Review</div>
                    <div class="card-subtitle-text" title="Awaiting Auditor Approval">Awaiting Auditor Approval</div>
                </div>
            </a>
        </div>
        <!-- Excluded -->
        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-6">
            <a href="javascript:void(0);" onclick="applyCardFilter('Excluded', this)" class="tracker-card <?= ($activeStatus === 'Excluded' ? 'active-filter' : '') ?>" style="--status-color: #64748b; --light-bg: #f8fafc; --shadow-color: rgba(100, 116, 139, 0.25);" title="">
                <div class="card-top">
                    <div class="icon-wrapper">
                        <i class="fas fa-ban" style="font-size: 24px;"></i>
                    </div>
                    <h3 class="card-value" id="stat_wf_excluded"><?= number_format($stats['workflow_excluded'] ?? 0) ?></h3>
                </div>
                <div class="card-body-content">
                    <div class="card-title-text" title="Excluded">Excluded</div>
                    <div class="card-subtitle-text" title="Excluded Points">Excluded Points</div>
                </div>
            </a>
        </div>
        <!-- Hold -->
        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-6">
            <a href="javascript:void(0);" onclick="applyCardFilter('Hold', this)" class="tracker-card <?= ($activeStatus === 'Hold' ? 'active-filter' : '') ?>" style="--status-color: #8b5cf6; --light-bg: #f5f3ff; --shadow-color: rgba(139, 92, 246, 0.25);" title="">
                <div class="card-top">
                    <div class="icon-wrapper">
                        <i class="fas fa-pause-circle" style="font-size: 24px;"></i>
                    </div>
                    <h3 class="card-value" id="stat_wf_hold"><?= number_format($stats['workflow_hold'] ?? 0) ?></h3>
                </div>
                <div class="card-body-content">
                    <div class="card-title-text" title="Hold">Hold</div>
                    <div class="card-subtitle-text" title="On Hold">On Hold</div>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">
        <?= $table ?>
    </div>
</div>

<!-- Upload Details Modal -->
<div class="modal fade" id="add_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                </div>
            </div>
            <form id="details_form" class="form" enctype="multipart/form-data">
                <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-10">
                    <input type="hidden" name="gemba_sr_no" id="details_id">
                    <div class="mb-13 text-center">
                        <h1 class="mb-3">Update Finding Details</h1>
                        <div class="text-muted fw-bold fs-5">Unique No: <span id="details_unique_no"
                                class="text-primary"></span></div>
                    </div>
                    <div class="fv-row mb-4">
                        <label class="fs-6 fw-bold mb-2">Observation Point</label>
                        <textarea class="form-control form-control-solid" id="details_observation"
                            name="observation_point" rows="3" disabled></textarea>
                    </div>
                    <div class="fv-row mb-4">
                        <label class="required fs-6 fw-bold mb-2">NC Remark</label>
                        <textarea class="form-control form-control-solid" id="details_nc_remark" name="nc_remark"
                            rows="4" placeholder="Please enter NC remark"></textarea>
                    </div>
                    <div class="fv-row mb-0">
                        <label class="required fs-6 fw-bold mb-2">NC After Proof</label>
                        <input type="file" class="form-control" id="details_nc_after_photo" name="nc_after_photo"
                            accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xls,.xlsx,.doc,.docx" />
                        <div id="details_after_photo_preview" class="mt-3 text-center"></div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-center pb-10 border-0">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="save_details_wip" class="btn btn-primary me-3">
                        <span class="indicator-label">Save (WIP)</span>
                    </button>
                    <button type="button" id="submit_details_review" class="btn btn-success">
                        <span class="indicator-label">Submit for Review</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close NC Modal -->
<div class="modal fade" id="closeNcModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="closeNcForm" onsubmit="return false;">
                <div class="modal-header">
                    <h5 class="modal-title">Close NC Finding</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="close_nc_id" name="id">
                    <input type="hidden" id="close_nc_status" name="status">

                    <div class="fv-row mb-4">
                        <label class="required fs-6 fw-bold mb-2">Closure Date</label>
                        <input type="date" class="form-control form-control-solid" id="close_date" name="close_date"
                            required>
                    </div>

                    <div class="fv-row mb-4">
                        <label class="fs-6 fw-bold mb-2">Closure Remarks</label>
                        <textarea class="form-control form-control-solid" id="close_remarks" name="closure_remarks"
                            rows="3" placeholder="Enter closure details..."></textarea>
                    </div>

                    <div class="fv-row mb-4">
                        <label class="fs-6 fw-bold mb-2">Closure Proof / Attachment</label>
                        <input type="file" class="form-control" id="closed_nc_photo" name="closed_nc_photo"
                            accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xls,.xlsx,.doc,.docx" />
                    </div>

                    <div class="fv-row mb-4">
                        <label class="required fs-6 fw-bold mb-2">Closure Status</label>
                        <select class="form-select form-select-solid" id="close_status_val" name="closure_status"
                            required>
                            <option value="Closed">Closed</option>
                            <option value="Hold-review with Client">Hold-review with Client</option>
                            <option value="Excluded">Excluded</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" onclick="submitCloseNc()">Confirm Closure</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Close NC Modal -->
<div class="modal fade" id="bulkCloseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="bulkCloseForm" onsubmit="return false;">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Close NCs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-4">
                        You are about to close <strong id="bulkCloseCount">0</strong> NCs.<br>
                        This action cannot be undone. Continue?
                    </div>
                    <div class="fv-row mb-4">
                        <label class="required fs-6 fw-bold mb-2">Closure Date</label>
                        <input type="date" class="form-control form-control-solid" id="bulk_close_date" name="closed_date" required>
                    </div>
                    <div class="fv-row mb-4">
                        <label class="fs-6 fw-bold mb-2">Closure Remarks</label>
                        <textarea class="form-control form-control-solid" id="bulk_close_remarks" name="closure_remarks" rows="3" placeholder="Enter closure details..."></textarea>
                    </div>
                    <div class="fv-row mb-4">
                        <label class="required fs-6 fw-bold mb-2">Closure Status</label>
                        <select class="form-select form-select-solid" id="bulk_close_status_val" name="closure_status" required>
                            <option value="Closed">Closed</option>
                            <option value="Hold-review with Client">Hold-review with Client</option>
                            <option value="Excluded">Excluded</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" onclick="submitBulkCloseNc()">Confirm Closure</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= view('Master/nc_action_history_modal'); ?>

<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<script>
    function applyCardFilter(status, element) {
        // Update active class
        $('.tracker-card').removeClass('active-filter');
        $(element).addClass('active-filter');
        
        // Update hidden status field
        $('#hidden_nc_status').val(status);
        
        // Trigger filter
        $('#apply_filter').click();
    }

    $(document).ready(function () {
        // Initialize Select2
        $('.select2-filter').select2({
            placeholder: "Select option",
            allowClear: true,
            closeOnSelect: false
        });

        // Select All Logic
        $('.select2-filter').on("select2:select", function (e) {
            var data = e.params.data.id;
            if (data === 'selectAll') {
                var $el = $(this);
                var allValues = [];
                $el.find('option').each(function() {
                    if ($(this).val() !== 'selectAll' && $(this).val() !== '') {
                        allValues.push($(this).val());
                    }
                });
                $el.val(allValues).trigger('change');
            }
        });
        
        $('.select2-filter').on("select2:unselect", function (e) {
            var data = e.params.data.id;
            if (data === 'selectAll') {
                $(this).val(null).trigger('change');
            }
        });


        // Dependent dropdown logic
        var $region = $('#region');
        var $siteCategory = $('#site_category');
        var $siteName = $('#site_name');

        function safeOptions(rows, key, placeholder) {
            var html = '<option value="selectAll">Select All</option>';
            if (!Array.isArray(rows)) return html;
            rows.forEach(function (r) {
                var v = (r[key] || "").trim();
                if (v) html += '<option value="' + escapeHtml(v) + '">' + escapeHtml(v) + '</option>';
            });
            return html;
        }

        function loadDependentOptions(type, done) {
            var regions = $region.val() || [];
            var site_categories = $siteCategory.val() || [];

            // Remove 'selectAll' from the arrays before sending to server
            regions = regions.filter(function(v) { return v !== 'selectAll'; });
            site_categories = site_categories.filter(function(v) { return v !== 'selectAll'; });

            $.ajax({
                url: '<?= base_url("Masters/GembaNcTracker/get_dependent_dropdowns") ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    type: type,
                    regions: regions,
                    site_categories: site_categories
                },
                success: function(rows) {
                    if (type === 'site_category') {
                        var preVal = $siteCategory.val();
                        $siteCategory.html(safeOptions(rows, 'site_category', 'All Categories'));
                        if (preVal && preVal.length > 0) $siteCategory.val(preVal);
                        if ($.fn.select2) $siteCategory.trigger("change.select2");
                    } else if (type === 'site_name') {
                        var preVal = $siteName.val();
                        $siteName.html(safeOptions(rows, 'site_name', 'All Sites'));
                        if (preVal && preVal.length > 0) $siteName.val(preVal);
                        if ($.fn.select2) $siteName.trigger("change.select2");
                    }
                    if (done) done();
                }
            });
        }

        $region.on("select2:close", function () {
            loadDependentOptions('site_category', function() {
                loadDependentOptions('site_name');
            });
        });

        $siteCategory.on("select2:close", function () {
            loadDependentOptions('site_name');
        });

        // DataTable re-initialization with filters
        window.reload_data_table = function (callback) {
            var params = $('#filter_form').serialize();
            var url = '<?= base_url("Masters/GembaNcTracker/table_ajax") ?>?' + params;

            if ($.fn.DataTable.isDataTable('#gemba_nc_tracker_table')) {
                var table = $('#gemba_nc_tracker_table').DataTable();
                if (callback) {
                    table.ajax.url(url).load(callback);
                } else {
                    table.ajax.url(url).load();
                }
            } else {
                if (callback) callback();
            }
        };

        window.refresh_summary_stats = function (callback) {
            var params = $('#filter_form').serialize();
            $.get('<?= base_url("Masters/GembaNcTracker/stats_ajax") ?>?' + params, function (res) {
                $('#stat_total').text(parseInt(res.total || 0).toLocaleString());
                $('#stat_wf_open').text(parseInt(res.workflow_open || 0).toLocaleString());
                $('#stat_wf_working').text(parseInt(res.workflow_working || 0).toLocaleString());
                $('#stat_wf_cluster').text(parseInt(res.workflow_cluster_review || 0).toLocaleString());
                $('#stat_wf_auditor').text(parseInt(res.workflow_auditor_review || 0).toLocaleString());
                $('#stat_wf_closed').text(parseInt(res.workflow_closed || 0).toLocaleString());
                $('#stat_wf_rejected').text(parseInt(res.workflow_rejected || 0).toLocaleString());
                $('#stat_wf_excluded').text(parseInt(res.workflow_excluded || 0).toLocaleString());
                $('#stat_wf_hold').text(parseInt(res.workflow_hold || 0).toLocaleString());
                
                if (callback) callback();
            });
        };

        // Trigger reload on filter button click
        $('#apply_filter').on('click', function () {
            var btn = $(this);
            if (btn.prop('disabled')) return;
            var originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Loading...');

            var tableDone = false;
            var statsDone = false;

            function checkDone() {
                if (tableDone && statsDone) {
                    btn.prop('disabled', false).html(originalHtml);
                }
            }

            reload_data_table(function () {
                tableDone = true;
                checkDone();
            });

            refresh_summary_stats(function () {
                statsDone = true;
                checkDone();
            });
        });

        function escapeHtml(unsafe) {
            if(!unsafe) return '';
            return unsafe
                 .toString()
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }

        // Universal Dependent Dropdowns Logic
        var isUpdatingFilters = false;

        $('.select2-filter').on('change', function(e) {
            // Prevent recursive calls while updating dynamically
            if (isUpdatingFilters) return;

            var $changed = $(this);
            var changedId = $changed.attr('id') || $changed.attr('name').replace('[]', '');
            
            var params = $('#filter_form').serialize();
            
            // Show tiny loading indicator on select2
            $(this).next('.select2-container').css('opacity', '0.6');

            $.post('<?= base_url("Masters/GembaNcTracker/dependent_filters_ajax") ?>?' + params, function(res) {
                isUpdatingFilters = true;

                function updateDropdown(id, dataList) {
                    var $el = $('#' + id).length ? $('#' + id) : $('select[name="' + id + '[]"]');
                    if (!$el.length) return;
                    
                    if (id !== changedId && dataList) {
                        var currentVal = $el.val() || [];
                        if (!Array.isArray(currentVal)) currentVal = [currentVal];
                        
                        var options = '<option value="selectAll">Select All</option>';
                        dataList.forEach(function(item) {
                            options += '<option value="' + escapeHtml(item) + '">' + escapeHtml(item) + '</option>';
                        });
                        
                        $el.html(options).trigger('change.select2');
                        
                        if (currentVal.length > 0) {
                            var validVals = [];
                            currentVal.forEach(function(val) {
                                if ($el.find('option[value="' + escapeHtml(val) + '"]').length > 0) {
                                    validVals.push(val);
                                }
                            });
                            $el.val(validVals).trigger('change.select2');
                        }
                    }
                }

                if (res) {
                    updateDropdown('cluster', res.cluster);
                    updateDropdown('gd_audit_category', res.audit_category);
                    updateDropdown('site_category', res.site_category);
                    updateDropdown('site_name', res.site_name);
                    updateDropdown('auditor_name', res.auditor_name);
                    updateDropdown('account_manager', res.account_manager);
                    updateDropdown('cluster_manager', res.cluster_manager_spoc);
                    updateDropdown('nc_type', res.nc_recommendation);
                    updateDropdown('point_status', res.point_status);
                }

                isUpdatingFilters = false;
                $('.select2-container').css('opacity', '1');
            }).fail(function() {
                isUpdatingFilters = false;
                $('.select2-container').css('opacity', '1');
            });
        });

        // Reset Filters
        $('#reset_filters').on('click', function () {
            $('#filter_form')[0].reset();
            $('.select2-filter').val(null).trigger('change.select2');
            $('#region').trigger('change');
            reload_data_table();
            refresh_summary_stats();
        });

        // Submit Details Form Action Handler
        function handleDetailsSubmit(buttonId, actionType, buttonText) {
            $('#' + buttonId).on('click', function () {
                var form = $('#details_form')[0];
                var formData = new FormData(form);
                formData.append('action', actionType);

                var btn = $(this);
                btn.attr('disabled', true).text('Processing...');

                $.ajax({
                    url: '<?= base_url('Masters/GembaNcTracker/save_details') ?>',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        btn.attr('disabled', false).html('<span class="indicator-label">' + buttonText + '</span>');
                        if (res.status == 'success') {
                            Swal.fire('Success', res.message, 'success');
                            $('#add_modal').modal('hide');
                            reload_data_table();
                            refresh_summary_stats();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function () {
                        btn.attr('disabled', false).html('<span class="indicator-label">' + buttonText + '</span>');
                        Swal.fire('Error', 'An error occurred', 'error');
                    }
                });
            });
        }

        handleDetailsSubmit('save_details_wip', 'save_wip', 'Save (WIP)');
        handleDetailsSubmit('submit_details_review', 'submit_for_review', 'Submit for Review');
    });

    function updateNcStatus(id, action) {
        if (action === '0') {
            // Reject with reason
            Swal.fire({
                title: 'Reject Finding',
                text: 'Please enter a rejection reason:',
                input: 'textarea',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Reject',
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to write something!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= base_url('Masters/GembaNcTracker/update_nc_status') ?>', {
                        id: id,
                        status: action,
                        rejection_reason: result.value
                    }, function (res) {
                        if (res.status == 1) {
                            Swal.fire('Success', res.message, 'success');
                            reload_data_table();
                            refresh_summary_stats();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
                }
            });
        } else {
            var confirmTitle = 'Are you sure?';
            var confirmText = 'You are about to change the status of this finding.';

            Swal.fire({
                title: confirmTitle,
                text: confirmText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= base_url('Masters/GembaNcTracker/update_nc_status') ?>', {
                        id: id,
                        status: action
                    }, function (res) {
                        if (res.status == 1) {
                            Swal.fire('Success', res.message, 'success');
                            reload_data_table();
                            refresh_summary_stats();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
                }
            });
        }
    }

    function edit_id(element, id) {
        $('#details_id').val(id);
        var url = $(element).data('ajax-url');
        $.get(url, function (res) {
            if (res.status == 1) {
                $('#details_unique_no').text(res.data.unique_no);
                $('#details_observation').val(res.data.observation_point);
                $('#details_nc_remark').val(res.data.nc_remark);

                if (res.data.after_photo_url) {
                    $('#details_after_photo_preview').html('<img src="' + res.data.after_photo_url + '" style="max-width:100%;max-height:200px" />');
                } else {
                    $('#details_after_photo_preview').html('');
                }

                $('#add_modal').modal('show');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        });
    }

    function openCloseModal(id, auditDate, status) {
        $('#close_nc_id').val(id);
        $('#close_nc_status').val(status);

        let dateInput = document.getElementById('close_date');
        dateInput.value = '';
        $('#close_remarks').val('');
        $('#close_status_val').val('Closed');

        $('#closeNcModal').modal('show');
    }

    function submitCloseNc() {
        let dateInput = document.getElementById('close_date');
        if (!dateInput.value) {
            Swal.fire('Warning', 'Please select a closure date', 'warning');
            return;
        }

        let form = document.getElementById('closeNcForm');
        let formData = new FormData(form);
        formData.append('gemba_sr_no', $('#close_nc_id').val());
        formData.append('closed_date', dateInput.value);
        formData.append('closure_remarks', $('#close_remarks').val());
        formData.append('closure_status', $('#close_status_val').val());

        $.ajax({
            url: '<?= base_url('Masters/GembaNcTracker/close_nc') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.status == 'success') {
                    Swal.fire('Success', res.message, 'success');
                    $('#closeNcModal').modal('hide');
                    reload_data_table();
                    refresh_summary_stats();
                } else {
                    Swal.fire('Error', res.message || 'Action failed', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Server connection failed', 'error');
            }
        });
    }

    function exportToExcel() {
        var params = $('#filter_form').serializeArray();

        if ($.fn.DataTable.isDataTable('#gemba_nc_tracker_table')) {
            var searchVal = $('#gemba_nc_tracker_table').DataTable().search();
            if (searchVal) {
                params.push({name: 'search[value]', value: searchVal});
            }
        }

        var form = $('<form>', {
            action: '<?= base_url("Masters/GembaNcTracker/export_excel") ?>',
            method: 'POST'
        });
        $.each(params, function (i, field) {
            form.append($('<input>', {
                type: 'hidden',
                name: field.name,
                value: field.value
            }));
        });
        $('body').append(form);
        form.submit();
        form.remove();
    }

    function exportToCSV() {
        var params = $('#filter_form').serializeArray();

        if ($.fn.DataTable.isDataTable('#gemba_nc_tracker_table')) {
            var searchVal = $('#gemba_nc_tracker_table').DataTable().search();
            if (searchVal) {
                params.push({name: 'search[value]', value: searchVal});
            }
        }

        var form = $('<form>', {
            action: '<?= base_url("Masters/GembaNcTracker/export_csv") ?>',
            method: 'POST'
        });
        $.each(params, function (i, field) {
            form.append($('<input>', {
                type: 'hidden',
                name: field.name,
                value: field.value
            }));
        });
        $('body').append(form);
        form.submit();
        form.remove();
    }

    // Bulk Logic
    function getSelectedRows() {
        var selected = [];
        $('.row-checkbox:checked').each(function() {
            selected.push($(this).val());
        });
        return selected;
    }

    function updateBulkBtn() {
        var selectedAuditor = $('.row-checkbox[data-status="2"]:checked').length;
        var selectedOpen = $('.row-checkbox[data-status="0"]:checked').length;
        var selectedWorking = $('.row-checkbox[data-status="1"]:checked').length;

        if (selectedAuditor > 0) {
            $('.row-checkbox').not('[data-status="2"]').prop('disabled', true);
            $('#bulkCloseBtn').removeClass('d-none').prop('disabled', false).text('Close Selected NCs (' + selectedAuditor + ')');
            $('#bulkWorkingBtn').addClass('d-none').prop('disabled', true);
            $('#bulkRejectBtn').addClass('d-none').prop('disabled', true);
        } else if (selectedOpen > 0) {
            $('.row-checkbox').not('[data-status="0"]').prop('disabled', true);
            $('#bulkWorkingBtn').removeClass('d-none').prop('disabled', false).text('Convert to Working (' + selectedOpen + ')');
            $('#bulkCloseBtn').addClass('d-none').prop('disabled', true);
            $('#bulkRejectBtn').addClass('d-none').prop('disabled', true);
        } else if (selectedWorking > 0) {
            $('.row-checkbox').not('[data-status="1"]').prop('disabled', true);
            $('#bulkRejectBtn').removeClass('d-none').prop('disabled', false).text('Reject Selected (' + selectedWorking + ')');
            $('#bulkCloseBtn').addClass('d-none').prop('disabled', true);
            $('#bulkWorkingBtn').addClass('d-none').prop('disabled', true);
        } else {
            // Nothing selected
            $('.row-checkbox').prop('disabled', false); // enable all
            $('#bulkCloseBtn').addClass('d-none').prop('disabled', true).text('Close Selected NCs (0)');
            $('#bulkWorkingBtn').addClass('d-none').prop('disabled', true).text('Convert to Working (0)');
            $('#bulkRejectBtn').addClass('d-none').prop('disabled', true).text('Reject Selected (0)');
        }
    }

    $(document).on('change', '.selectAllCheckbox', function() {
        var isChecked = this.checked;
        $('.selectAllCheckbox').prop('checked', isChecked);
        if (isChecked) {
            var firstStatus = $('.row-checkbox:not(:disabled)').first().data('status');
            if (firstStatus !== undefined) {
                $('.row-checkbox[data-status="' + firstStatus + '"]').prop('checked', true);
            }
        } else {
            $('.row-checkbox').prop('checked', false);
        }
        updateBulkBtn();
    });

    $(document).on('change', '.row-checkbox', function() {
        if (!this.checked) {
            $('.selectAllCheckbox').prop('checked', false);
        } else {
            var myStatus = $(this).data('status');
            var allOfMyStatus = $('.row-checkbox[data-status="' + myStatus + '"]').length;
            var checkedOfMyStatus = $('.row-checkbox[data-status="' + myStatus + '"]:checked').length;
            if (allOfMyStatus > 0 && allOfMyStatus === checkedOfMyStatus) {
                $('.selectAllCheckbox').prop('checked', true);
            }
        }
        updateBulkBtn();
    });

    // Reset checkboxes on table draw
    $(document).on('draw.dt', '#gemba_nc_tracker_table', function () {
        $('.selectAllCheckbox').prop('checked', false);
        updateBulkBtn();
    });

    function openBulkCloseModal() {
        var selected = getSelectedRows();
        if (selected.length === 0) return;
        
        $('#bulkCloseCount').text(selected.length);
        $('#bulk_close_date').val('');
        $('#bulk_close_remarks').val('');
        $('#bulk_close_status_val').val('Closed');
        
        $('#bulkCloseModal').modal('show');
    }

    function submitBulkCloseNc() {
        var selected = getSelectedRows();
        if (selected.length === 0) return;

        let dateInput = document.getElementById('bulk_close_date');
        if (!dateInput.value) {
            Swal.fire('Warning', 'Please select a closure date', 'warning');
            return;
        }

        let btn = $('#bulkCloseModal').find('button[type="submit"]');
        btn.prop('disabled', true).text('Processing...');

        let formData = {
            ids: selected,
            closed_date: dateInput.value,
            closure_remarks: $('#bulk_close_remarks').val(),
            closure_status: $('#bulk_close_status_val').val()
        };

        $.post('<?= base_url('Masters/GembaNcTracker/bulk_close_nc') ?>', formData, function (res) {
            btn.prop('disabled', false).text('Confirm Closure');
            if (res.status == 'success') {
                Swal.fire('Success', res.message, 'success');
                $('#bulkCloseModal').modal('hide');
                reload_data_table();
                refresh_summary_stats();
                $('.selectAllCheckbox').prop('checked', false);
                updateBulkBtn();
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }).fail(function() {
            btn.prop('disabled', false).text('Confirm Closure');
            Swal.fire('Error', 'Server error occurred', 'error');
        });
    }

    function submitBulkWorking() {
        var selected = getSelectedRows();
        if (selected.length === 0) return;

        Swal.fire({
            title: 'Convert to Working?',
            text: 'You are about to convert ' + selected.length + ' NCs to Working status. Continue?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                let btn = $('#bulkWorkingBtn');
                var originalText = btn.text();
                btn.prop('disabled', true).text('Processing...');

                let formData = {
                    ids: selected
                };

                $.post('<?= base_url('Masters/GembaNcTracker/bulk_working_nc') ?>', formData, function (res) {
                    btn.prop('disabled', false).text(originalText);
                    if (res.status == 'success') {
                        Swal.fire('Success', res.message, 'success');
                        reload_data_table();
                        refresh_summary_stats();
                        $('.selectAllCheckbox').prop('checked', false);
                        updateBulkBtn();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }).fail(function() {
                    btn.prop('disabled', false).text(originalText);
                    Swal.fire('Error', 'Server error occurred', 'error');
                });
            }
        });
    }

    function submitBulkReject() {
        var selected = getSelectedRows();
        if (selected.length === 0) return;

        Swal.fire({
            title: 'Reject Selected Working NCs?',
            text: 'Are you sure you want to reject the selected Working NC records? The selected records will be converted to Open status.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reject them!'
        }).then((result) => {
            if (result.isConfirmed) {
                let btn = $('#bulkRejectBtn');
                var originalText = btn.text();
                btn.prop('disabled', true).text('Processing...');

                let formData = {
                    ids: selected
                };

                $.post('<?= base_url('Masters/GembaNcTracker/bulk_reject_nc') ?>', formData, function (res) {
                    btn.prop('disabled', false).text(originalText);
                    if (res.status == 'success') {
                        Swal.fire('Success', res.message, 'success');
                        reload_data_table();
                        refresh_summary_stats();
                        $('.selectAllCheckbox').prop('checked', false);
                        updateBulkBtn();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }).fail(function() {
                    btn.prop('disabled', false).text(originalText);
                    Swal.fire('Error', 'Server error occurred', 'error');
                });
            }
        });
    }
</script>
<?php $this->endSection(); ?>