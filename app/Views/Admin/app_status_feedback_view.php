<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<li class="breadcrumb-item text-muted">
    <a href="<?= base_url('Admin/AppStatusFeedback') ?>" class="text-muted text-hover-primary">Application Status Feedback</a>
</li>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>

<style>
/* Override custom-typography.css or theme dark color inheritance on KPI card icons */
.card-flush .bg-white.bg-opacity-20 i,
.card-flush i.text-white {
    color: #ffffff !important;
    fill: #ffffff !important;
}
.stat-pill {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 20px;
}
.stat-pill-yes {
    background-color: #d1fae5;
    color: #065f46;
}
.stat-pill-no {
    background-color: #fee2e2;
    color: #991b1b;
}
.stat-pill-na {
    background-color: #f1f5f9;
    color: #475569;
}
</style>

<!-- Top KPI Card -->
<div class="row g-5 g-xl-8 mb-6">
    <div class="col-xl-4 col-md-6">
        <div class="card card-flush h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);">
            <div class="card-body d-flex flex-column justify-content-between text-white p-6">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <span class="fs-4 fw-bold text-white text-opacity-90">Total Status Submissions</span>
                    <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded-circle" style="width: 48px; height: 48px;">
                        <i class="fas fa-clipboard-check fs-2 text-white" style="color: #ffffff !important;"></i>
                    </div>
                </div>
                <div>
                    <h2 class="display-6 fw-bolder text-white mb-1"><?= number_format($total_submissions) ?></h2>
                    <span class="fs-7 text-white text-opacity-75">Users who submitted application status feedback</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-md-6">
        <div class="card card-flush h-100 shadow-sm border-0 bg-white">
            <div class="card-body p-6 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fs-5 fw-bolder text-gray-800">12-Point Application Health Overview</span>
                        <span class="badge badge-light-primary fw-bold">Live Aggregates</span>
                    </div>
                    <span class="text-muted fs-7">Aggregate counts of responses received from all active system users across operational and audit modules.</span>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-4">
                        <div class="p-3 bg-light-success rounded-3 text-center">
                            <?php 
                                $totalYes = array_sum(array_column($question_stats, 'yes'));
                            ?>
                            <div class="fs-4 fw-bolder text-success"><?= number_format($totalYes) ?></div>
                            <span class="fs-8 text-gray-600 fw-semibold"><i class="fas fa-check text-success me-1"></i> Total "Yes"</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-light-danger rounded-3 text-center">
                            <?php 
                                $totalNo = array_sum(array_column($question_stats, 'no'));
                            ?>
                            <div class="fs-4 fw-bolder text-danger"><?= number_format($totalNo) ?></div>
                            <span class="fs-8 text-gray-600 fw-semibold"><i class="fas fa-times text-danger me-1"></i> Total "No"</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-light-secondary rounded-3 text-center">
                            <?php 
                                $totalNa = array_sum(array_column($question_stats, 'na'));
                            ?>
                            <div class="fs-4 fw-bolder text-secondary"><?= number_format($totalNa) ?></div>
                            <span class="fs-8 text-gray-600 fw-semibold"><i class="fas fa-minus text-secondary me-1"></i> Total "NA"</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Per-Question Response Counts Grid -->
<div class="card shadow-sm border-0 rounded-4 mb-6">
    <div class="card-header border-0 pt-6">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bolder fs-4 text-gray-800">Per-Question Response Counts</span>
            <span class="text-muted mt-1 fw-bold fs-7">Detailed breakdown of Yes / No / NA responses for all 12 modules</span>
        </h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="collapse" data-bs-target="#questionBreakdownCollapse" aria-expanded="true">
                <i class="fas fa-chevron-down me-1"></i> Toggle Overview
            </button>
        </div>
    </div>
    <div class="collapse show" id="questionBreakdownCollapse">
        <div class="card-body pt-0 pb-6">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-4 gy-3 mb-0">
                    <thead>
                        <tr class="fw-bolder text-muted bg-light text-uppercase fs-8">
                            <th style="width: 50px;">#</th>
                            <th>Question / Module</th>
                            <th class="text-center" style="width: 110px;">Yes</th>
                            <th class="text-center" style="width: 110px;">No</th>
                            <th class="text-center" style="width: 110px;">NA</th>
                            <th class="text-center" style="width: 160px;">Health Ratio</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700 fs-7">
                        <?php foreach ($question_stats as $qNum => $stat): 
                            $qTotal = $stat['yes'] + $stat['no'] + $stat['na'];
                            $yesPct = ($qTotal > 0) ? round(($stat['yes'] / $qTotal) * 100) : 0;
                        ?>
                            <tr>
                                <td><span class="badge badge-light-primary fw-bolder">Q<?= $qNum ?></span></td>
                                <td class="fw-bold text-gray-800"><?= htmlspecialchars($stat['title']) ?></td>
                                <td class="text-center">
                                    <span class="stat-pill stat-pill-yes"><?= number_format($stat['yes']) ?> Yes</span>
                                </td>
                                <td class="text-center">
                                    <span class="stat-pill stat-pill-no"><?= number_format($stat['no']) ?> No</span>
                                </td>
                                <td class="text-center">
                                    <span class="stat-pill stat-pill-na"><?= number_format($stat['na']) ?> NA</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="progress flex-grow-1 h-6px bg-light rounded-pill" style="max-width: 100px;">
                                            <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: <?= $yesPct ?>%;"></div>
                                        </div>
                                        <span class="fs-8 fw-bold text-muted"><?= $yesPct ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow-sm border-0 rounded-4 mb-6">
    <div class="card-header border-0 pt-6">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bolder fs-4 text-gray-800">Filter Submissions</span>
            <span class="text-muted mt-1 fw-bold fs-7">Filter by role, date range, and individual question responses (Yes / No / NA)</span>
        </h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light-primary me-2" onclick="resetFilters()">
                <i class="fas fa-undo me-1"></i> Reset
            </button>
            <button type="button" class="btn btn-sm btn-primary" onclick="exportAppStatusCsv()">
                <i class="fas fa-file-csv me-1"></i> Export to CSV
            </button>
        </div>
    </div>
    <div class="card-body pt-0 pb-6">
        <div class="row g-4">
            <!-- Filter by Role -->
            <div class="col-md-3">
                <label class="form-label fs-7 fw-bold text-gray-700">User Role / Designation</label>
                <select class="form-select form-select-solid form-select-sm" id="filterRole">
                    <option value="">All Roles</option>
                    <?php foreach ($roles_list as $role): ?>
                        <option value="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars($role) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter by Question Number -->
            <div class="col-md-4">
                <label class="form-label fs-7 fw-bold text-gray-700">Question</label>
                <select class="form-select form-select-solid form-select-sm" id="filterQuestionNum">
                    <option value="">All Questions</option>
                    <?php foreach ($questions as $qNum => $qTitle): ?>
                        <option value="<?= $qNum ?>">Q<?= $qNum ?>: <?= htmlspecialchars($qTitle) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter by Answer -->
            <div class="col-md-2">
                <label class="form-label fs-7 fw-bold text-gray-700">Response</label>
                <select class="form-select form-select-solid form-select-sm" id="filterQuestionAns">
                    <option value="">Any (Yes / No / NA)</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                    <option value="NA">NA</option>
                </select>
            </div>

            <!-- Date From -->
            <div class="col-md-3">
                <label class="form-label fs-7 fw-bold text-gray-700">Date Range</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control form-control-solid form-control-sm" id="filterFromDate" placeholder="From">
                    <input type="date" class="form-control form-control-solid form-control-sm" id="filterToDate" placeholder="To">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Responses Table Card -->
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header border-0 pt-6">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bolder fs-4 text-gray-800">User Submissions</span>
            <span class="text-muted mt-1 fw-bold fs-7">Complete log of all application status questionnaires</span>
        </h3>
    </div>
    <div class="card-body pt-0 pb-6">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="appStatusTable">
                <thead>
                    <tr class="fw-bolder text-muted bg-light text-uppercase fs-8">
                        <th style="min-width: 40px;">#</th>
                        <th style="min-width: 170px;">User Details</th>
                        <th style="min-width: 120px;">Role</th>
                        <th style="min-width: 380px;">Question Responses (Q1 - Q12)</th>
                        <th style="min-width: 180px;">Comments / Issues</th>
                        <th style="min-width: 120px;">Submitted At</th>
                        <th style="min-width: 60px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700 fs-7">
                    <!-- Populated via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail Modal for inspecting single submission -->
<div class="modal fade" id="appStatusDetailModal" tabindex="-1" aria-labelledby="appStatusDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-5 px-6" style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-20 text-white rounded-circle" style="width: 40px; height: 40px;">
                        <i class="fas fa-clipboard-check fs-4 text-white" style="color: #ffffff !important;"></i>
                    </div>
                    <div>
                        <h4 class="modal-title fw-bolder text-white mb-0" id="appStatusDetailModalLabel" style="color: #ffffff !important;">Feedback Submission Details</h4>
                        <div class="text-white text-opacity-75 fs-8" id="detailModalSubtitle" style="color: rgba(255,255,255,0.85) !important;">User response breakdown</div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-icon btn-color-white bg-white bg-opacity-10 rounded-circle" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-6 text-white" style="color: #ffffff !important;"></i>
                </button>
            </div>
            <div class="modal-body py-5 px-6">
                <div class="d-flex flex-wrap justify-content-between p-3 bg-light rounded-3 mb-4 fs-7">
                    <div><strong>User:</strong> <span id="detailUserName"></span></div>
                    <div><strong>Role:</strong> <span id="detailUserRole"></span></div>
                    <div><strong>Submitted At:</strong> <span id="detailSubmittedAt"></span></div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light fs-8 text-uppercase">
                            <tr>
                                <th style="width: 45px;">#</th>
                                <th>Question</th>
                                <th style="width: 90px;" class="text-center">Response</th>
                            </tr>
                        </thead>
                        <tbody id="detailQuestionsTableBody" class="fs-7">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-light rounded-3">
                    <div class="fw-bold text-gray-800 mb-1 fs-7"><i class="fas fa-comment-alt text-primary me-1"></i> Additional Comments:</div>
                    <div id="detailComments" class="text-gray-700 fs-7 fst-italic"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-6">
                <button type="button" class="btn btn-light px-5 rounded-pill" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<link href="<?= base_url(); ?>/assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
<script src="<?= base_url(); ?>/assets/plugins/custom/datatables/datatables.bundle.js"></script>

<script>
var appStatusDataTable;

$(document).ready(function() {
    appStatusDataTable = $('#appStatusTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: '<?= base_url("Admin/AppStatusFeedback/datatable_ajax") ?>',
            type: 'POST',
            data: function(d) {
                d.role = $('#filterRole').val();
                d.question_num = $('#filterQuestionNum').val();
                d.question_ans = $('#filterQuestionAns').val();
                d.from_date = $('#filterFromDate').val();
                d.to_date = $('#filterToDate').val();
            }
        },
        columns: [
            { data: 'sr_no', orderable: false },
            { data: 'user_info' },
            { data: 'role' },
            { data: 'answers', orderable: false },
            { data: 'comments', orderable: false },
            { data: 'submitted_at' },
            { data: 'actions', orderable: false, className: 'text-end' }
        ],
        order: [[5, 'desc']], // Default order by submitted_at desc
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" + "<'table-wrapper'tr>" + "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
        language: {
            emptyTable: "No application status feedback submissions found",
            processing: '<span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading submissions...'
        }
    });

    $('#filterRole, #filterQuestionNum, #filterQuestionAns, #filterFromDate, #filterToDate').on('change', function() {
        appStatusDataTable.ajax.reload();
    });
});

function resetFilters() {
    $('#filterRole').val('');
    $('#filterQuestionNum').val('');
    $('#filterQuestionAns').val('');
    $('#filterFromDate').val('');
    $('#filterToDate').val('');
    appStatusDataTable.search('').ajax.reload();
}

function exportAppStatusCsv() {
    var role = encodeURIComponent($('#filterRole').val() || '');
    var qNum = encodeURIComponent($('#filterQuestionNum').val() || '');
    var qAns = encodeURIComponent($('#filterQuestionAns').val() || '');
    var fromDate = encodeURIComponent($('#filterFromDate').val() || '');
    var toDate = encodeURIComponent($('#filterToDate').val() || '');

    var exportUrl = '<?= base_url("Admin/AppStatusFeedback/export_csv") ?>' +
        '?role=' + role +
        '&question_num=' + qNum +
        '&question_ans=' + qAns +
        '&from_date=' + fromDate +
        '&to_date=' + toDate;

    window.location.href = exportUrl;
}

function viewAppStatusDetails(id) {
    $.ajax({
        url: '<?= base_url("Admin/AppStatusFeedback/get_details/") ?>/' + id,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res && res.status === 1) {
                var d = res.data;
                var q = res.questions;
                $('#detailUserName').text((d.user_name || 'User #' + d.user_id) + (d.user_emp_code ? ' (' + d.user_emp_code + ')' : ''));
                $('#detailUserRole').text(d.user_designation || 'User');
                $('#detailSubmittedAt').text(d.submitted_at);
                $('#detailComments').text(d.comments || 'No additional comments provided.');

                var tbody = '';
                for (var i = 1; i <= 12; i++) {
                    var ans = d['q' + i] || 'NA';
                    var badgeClass = 'badge-light-secondary text-secondary';
                    if (ans === 'Yes') badgeClass = 'badge-light-success text-success';
                    else if (ans === 'No') badgeClass = 'badge-light-danger text-danger';

                    tbody += '<tr>' +
                        '<td><span class="badge badge-light-primary fw-bolder">Q' + i + '</span></td>' +
                        '<td>' + (q[i] || 'Question ' + i) + '</td>' +
                        '<td class="text-center"><span class="badge ' + badgeClass + ' fs-8 px-2 py-1 fw-bold">' + ans + '</span></td>' +
                        '</tr>';
                }
                $('#detailQuestionsTableBody').html(tbody);
                $('#appStatusDetailModal').modal('show');
            } else {
                alert(res.message || 'Error fetching details');
            }
        }
    });
}
</script>
<?php $this->endSection(); ?>
