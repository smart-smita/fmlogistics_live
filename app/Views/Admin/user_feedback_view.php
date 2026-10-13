<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<li class="breadcrumb-item text-muted">
    <a href="<?= base_url('Admin/UserFeedback') ?>" class="text-muted text-hover-primary">User Feedback Management</a>
</li>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>

<style>
/* Override custom-typography.css or theme dark color inheritance on KPI card icons */
.card-flush .bg-white.bg-opacity-20 i,
.card-flush i.text-white,
.card-flush .fa-comments,
.card-flush .fa-star.fs-2,
.card-flush .fa-star.fs-6 {
    color: #ffffff !important;
    fill: #ffffff !important;
}
</style>

<!-- KPI / Summary Cards -->
<div class="row g-5 g-xl-8 mb-6">
    <!-- Total Feedback Card -->
    <div class="col-xl-4 col-md-6">
        <div class="card card-flush h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
            <div class="card-body d-flex flex-column justify-content-between text-white p-6">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <span class="fs-4 fw-bold text-white text-opacity-90">Total Submissions</span>
                    <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded-circle" style="width: 48px; height: 48px;">
                        <i class="fas fa-comments fs-2 text-white" style="color: #ffffff !important;"></i>
                    </div>
                </div>
                <div>
                    <h2 class="display-6 fw-bolder text-white mb-1"><?= number_format($total_submissions) ?></h2>
                    <span class="fs-7 text-white text-opacity-75">Users who submitted one-time feedback</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Rating Card -->
    <div class="col-xl-4 col-md-6">
        <div class="card card-flush h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="card-body d-flex flex-column justify-content-between text-white p-6">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <span class="fs-4 fw-bold text-white text-opacity-90">Average Rating</span>
                    <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded-circle" style="width: 48px; height: 48px;">
                        <i class="fas fa-star fs-2 text-white" style="color: #ffffff !important;"></i>
                    </div>
                </div>
                <div>
                    <div class="d-flex align-items-baseline gap-2 mb-1">
                        <h2 class="display-6 fw-bolder text-white mb-0"><?= number_format($avg_rating, 1) ?></h2>
                        <span class="fs-4 text-white text-opacity-80">/ 5.0</span>
                    </div>
                    <div class="d-flex gap-1 text-white">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="<?= $i <= round($avg_rating) ? 'fas' : 'far' ?> fa-star fs-6" style="color: #ffffff !important;"></i>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Breakdown Card -->
    <div class="col-xl-4 col-md-12">
        <div class="card card-flush h-100 shadow-sm border-0 bg-white">
            <div class="card-body p-6">
                <div class="fs-6 fw-bold text-gray-800 mb-3">Rating Breakdown</div>
                <div class="d-flex flex-column gap-2">
                    <?php for($star = 5; $star >= 1; $star--): 
                        $count = $rating_counts[$star] ?? 0;
                        $pct = $total_submissions > 0 ? round(($count / $total_submissions) * 100) : 0;
                    ?>
                    <div class="d-flex align-items-center gap-2 fs-8">
                        <span class="text-nowrap fw-semibold text-muted" style="width: 45px;"><?= $star ?> <i class="fas fa-star text-warning fs-8"></i></span>
                        <div class="progress flex-grow-1 h-6px bg-light rounded-pill">
                            <div class="progress-bar bg-warning rounded-pill" role="progressbar" style="width: <?= $pct ?>%;"></div>
                        </div>
                        <span class="text-muted fw-bold" style="width: 35px; text-align: right;"><?= $count ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow-sm border-0 rounded-4 mb-6">
    <div class="card-header border-0 pt-6">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bolder fs-4 text-gray-800">Filter Feedback</span>
            <span class="text-muted mt-1 fw-bold fs-7">Filter by rating, category, and submission date range</span>
        </h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light-primary me-2" onclick="resetFilters()">
                <i class="fas fa-undo me-1"></i> Reset
            </button>
            <button type="button" class="btn btn-sm btn-success text-white" onclick="exportFeedbackCsv()">
                <i class="fas fa-file-csv me-1"></i> Export to CSV
            </button>
        </div>
    </div>
    <div class="card-body py-4">
        <div class="row g-4">
            <!-- Filter by Rating -->
            <div class="col-md-3 col-sm-6">
                <label class="form-label fs-7 fw-bold text-gray-700">Rating</label>
                <select id="filterRating" class="form-select form-select-solid form-select-sm rounded-3">
                    <option value="">All Ratings</option>
                    <option value="5">5 Stars (Excellent)</option>
                    <option value="4">4 Stars (Very Good)</option>
                    <option value="3">3 Stars (Good)</option>
                    <option value="2">2 Stars (Fair)</option>
                    <option value="1">1 Star (Poor)</option>
                </select>
            </div>

            <!-- Filter by Category -->
            <div class="col-md-3 col-sm-6">
                <label class="form-label fs-7 fw-bold text-gray-700">Category</label>
                <select id="filterCategory" class="form-select form-select-solid form-select-sm rounded-3">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date From -->
            <div class="col-md-3 col-sm-6">
                <label class="form-label fs-7 fw-bold text-gray-700">From Date</label>
                <input type="date" id="filterFromDate" class="form-control form-control-solid form-control-sm rounded-3" />
            </div>

            <!-- Date To -->
            <div class="col-md-3 col-sm-6">
                <label class="form-label fs-7 fw-bold text-gray-700">To Date</label>
                <input type="date" id="filterToDate" class="form-control form-control-solid form-control-sm rounded-3" />
            </div>
        </div>
    </div>
</div>

<!-- Feedback Table Card -->
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-6">
        <div class="table-responsive">
            <table id="feedbackTable" class="table table-row-bordered table-row-gray-200 align-middle gs-4 gy-4 w-100">
                <thead>
                    <tr class="fw-bolder fs-7 text-uppercase gs-0 bg-light text-muted">
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 180px;">User Info</th>
                        <th style="min-width: 130px;">Role</th>
                        <th style="min-width: 140px;">Rating</th>
                        <th style="min-width: 130px;">Category</th>
                        <th style="min-width: 250px;">Comments</th>
                        <th style="min-width: 220px;">Suggestions</th>
                        <th style="min-width: 140px;">Submitted At</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-700 fs-7">
                    <!-- Populated via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<link href="<?= base_url(); ?>/assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
<script src="<?= base_url(); ?>/assets/plugins/custom/datatables/datatables.bundle.js"></script>

<script>
var feedbackDataTable;

$(document).ready(function() {
    feedbackDataTable = $('#feedbackTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: '<?= base_url("Admin/UserFeedback/datatable_ajax") ?>',
            type: 'POST',
            data: function(d) {
                d.rating = $('#filterRating').val();
                d.category = $('#filterCategory').val();
                d.from_date = $('#filterFromDate').val();
                d.to_date = $('#filterToDate').val();
            }
        },
        columns: [
            { data: 'sr_no', orderable: false },
            { data: 'user_info' },
            { data: 'role' },
            { data: 'rating' },
            { data: 'category' },
            { data: 'comments' },
            { data: 'suggestion' },
            { data: 'submitted_at' }
        ],
        order: [[7, 'desc']], // Default order by submitted_at desc
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" + "<'table-wrapper'tr>" + "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
        language: {
            emptyTable: "No feedback submissions found",
            processing: '<span class="spinner-border spinner-border-sm align-middle me-2"></span> Loading feedback...'
        }
    });

    $('#filterRating, #filterCategory, #filterFromDate, #filterToDate').on('change', function() {
        feedbackDataTable.ajax.reload();
    });
});

function resetFilters() {
    $('#filterRating').val('');
    $('#filterCategory').val('');
    $('#filterFromDate').val('');
    $('#filterToDate').val('');
    feedbackDataTable.search('').ajax.reload();
}

function exportFeedbackCsv() {
    var rating = encodeURIComponent($('#filterRating').val() || '');
    var category = encodeURIComponent($('#filterCategory').val() || '');
    var fromDate = encodeURIComponent($('#filterFromDate').val() || '');
    var toDate = encodeURIComponent($('#filterToDate').val() || '');

    var exportUrl = '<?= base_url("Admin/UserFeedback/export_csv") ?>' +
        '?rating=' + rating +
        '&category=' + category +
        '&from_date=' + fromDate +
        '&to_date=' + toDate;

    window.location.href = exportUrl;
}
</script>
<?php $this->endSection(); ?>
