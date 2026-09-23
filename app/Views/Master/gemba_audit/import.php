<?= $this->extend('Layout/base_admin') ?>

<?= $this->section('main_body') ?>
<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Gemba Audit Excel/CSV Import</h3>
            <div class="text-muted fs-7">Upload audit records to insert new or update existing audits.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('gemba-audit/download_sample') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-download me-2"></i>Gemba Audit Sample File
            </a>
            <a href="<?= base_url('gemba-audit') ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Audits List
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success d-flex align-items-center p-4 mb-4">
            <i class="fas fa-check-circle fa-2x me-3 text-success"></i>
            <div>
                <h5 class="mb-1 text-success fw-bold">Success</h5>
                <span><?= session()->getFlashdata('success') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger d-flex align-items-center p-4 mb-4">
            <i class="fas fa-exclamation-circle fa-2x me-3 text-danger"></i>
            <div>
                <h5 class="mb-1 text-danger fw-bold">Error</h5>
                <span><?= session()->getFlashdata('error') ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Import Summary Dashboard (Displayed after redirecting with summary) -->
    <?php $summary = session()->getFlashdata('import_summary'); ?>
    <?php if ($summary): ?>
        <div class="card shadow-sm mb-4 border border-info">
            <div class="card-header bg-light-info py-3">
                <h4 class="card-title fw-bold text-info mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Import Summary - <?= htmlspecialchars($summary['file_name']) ?>
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center mb-4">
                    <div class="col-6 col-md-2">
                        <div class="border rounded p-3 bg-light-primary">
                            <div class="text-muted fs-7 fw-bold uppercase">Total Processed</div>
                            <div class="fs-2 fw-bold text-primary"><?= $summary['total_rows'] ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="border rounded p-3 bg-light-success">
                            <div class="text-muted fs-7 fw-bold uppercase">Inserted</div>
                            <div class="fs-2 fw-bold text-success"><?= $summary['inserted'] ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="border rounded p-3 bg-light-info">
                            <div class="text-muted fs-7 fw-bold uppercase">Updated</div>
                            <div class="fs-2 fw-bold text-info"><?= $summary['updated'] ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="border rounded p-3 bg-light-secondary">
                            <div class="text-muted fs-7 fw-bold uppercase">Skipped</div>
                            <div class="fs-2 fw-bold text-secondary"><?= $summary['skipped'] ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="border rounded p-3 bg-light-danger">
                            <div class="text-muted fs-7 fw-bold uppercase">Failed</div>
                            <div class="fs-2 fw-bold text-danger"><?= $summary['failed'] ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="border rounded p-3 bg-light-warning text-dark">
                            <div class="text-muted fs-7 fw-bold uppercase">Duplicates</div>
                            <div class="fs-2 fw-bold text-warning-dark"><?= $summary['duplicates'] ?></div>
                        </div>
                    </div>
                </div>

                <?php if (session()->getFlashdata('import_errors_exist')): ?>
                    <div
                        class="alert alert-custom alert-light-danger border border-danger d-flex align-items-center justify-content-between p-4 mb-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-excel fa-2x me-3 text-danger"></i>
                            <div>
                                <h6 class="mb-1 fw-bold text-danger">Validation Issues Encountered</h6>
                                <span class="fs-7 text-muted">Some rows had incorrect/missing data and were skipped. View the
                                    report for row numbers and reasons.</span>
                            </div>
                        </div>
                        <a href="<?= base_url('gemba-audit/download_error_report') ?>"
                            class="btn btn-danger btn-sm fw-bold shadow-sm">
                            <i class="fas fa-download me-2"></i>Download Error Report
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Left: Upload Form -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-4">
                    <h3 class="card-title fw-bold">Upload Source File</h3>
                </div>
                <div class="card-body">
                    <form id="import_form" method="POST" action="<?= base_url('gemba-audit/import') ?>"
                        enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <!-- Drag and Drop Zone -->
                        <div id="drop_zone"
                            class="border border-dashed border-primary rounded p-5 text-center bg-light-primary cursor-pointer mb-4 position-relative"
                            style="transition: all 0.3s ease; min-height: 200px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                            <input type="file" name="import_file" id="import_file" accept=".xlsx,.xls,.csv"
                                class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" required>
                            <div id="upload_prompt">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3 anim-bounce"></i>
                                <h5 class="fw-bold">Drag & Drop file here or click to browse</h5>
                                <span class="text-muted fs-7 d-block mb-2">Supported formats: .xlsx, .xls, .csv</span>
                                <span class="badge bg-secondary px-3 py-2">Max file size: 10MB</span>
                            </div>
                            <div id="file_selected_info" class="d-none w-100">
                                <i class="fas fa-file-alt fa-3x text-success mb-3"></i>
                                <h5 class="fw-bold text-success" id="selected_file_name">Filename.xlsx</h5>
                                <span class="text-muted fs-7 d-block" id="selected_file_size">0 KB</span>
                                <span class="text-primary fs-7 d-block mt-3 cursor-pointer fw-bold hover-underline"
                                    id="change_file_btn">Change File</span>
                            </div>
                        </div>

                        <!-- Info Card -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-2 text-dark"><i
                                        class="fas fa-info-circle me-2 text-info"></i>Import Guidelines:</h6>
                                <ul class="fs-7 text-muted mb-0 ps-4">
                                    <li class="mb-1"><strong>Insert Rule</strong>: Every row in the uploaded Excel file will be inserted as a new record.</li>
                                    <li class="mb-1"><strong>Mandatory columns</strong>: `region`, `auditor_name`,
                                        `audit_category`, `audit_type`, `site_name`, `audit_report_date`,
                                        `observation_point`, `risks_details`, `action_recommendation`, `point_status`,
                                        `point_category`.</li>
                                    <li><strong>Data types</strong>: Numeric columns must be numbers, and dates must be
                                        in valid date formats.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid">
                            <button type="submit" id="submit_btn" class="btn btn-primary btn-lg fw-bold py-3" disabled>
                                <span id="btn_text"><i class="fas fa-file-import me-2"></i>Process Import</span>
                                <span id="btn_spinner" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"
                                        aria-hidden="true"></span>
                                    Uploading & Parsing...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Import Log (History) -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-4">
                    <h3 class="card-title fw-bold">Import History Log</h3>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="import_history_table" class="table table-hover table-striped align-middle mb-0"
                            style="font-size: 0.9rem; width: 100%;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">File Name</th>
                                    <th>Imported By</th>
                                    <th>Import Date</th>
                                    <th class="text-center">Total Rows</th>
                                    <th class="text-center text-success">Inserted</th>
                                    <th class="text-center text-info">Updated</th>
                                    <th class="text-center text-danger">Failed</th>
                                    <th class="pe-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($history)): ?>
                                    <?php foreach ($history as $h): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"
                                                style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                                title="<?= htmlspecialchars($h['file_name']) ?>">
                                                <?= htmlspecialchars($h['file_name']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($h['imported_by']) ?></td>
                                            <td><?= date('d M Y H:i', strtotime($h['import_date'])) ?></td>
                                            <td class="text-center fw-bold"><?= $h['total_rows'] ?></td>
                                            <td class="text-center text-success fw-bold"><?= $h['inserted'] ?></td>
                                            <td class="text-center text-info fw-bold"><?= $h['updated'] ?></td>
                                            <td class="text-center text-danger fw-bold"><?= $h['failed'] ?></td>
                                            <td class="pe-4 text-center">
                                                <?php if ($h['failed'] == 0): ?>
                                                    <span class="badge bg-light-success text-success px-3 py-1">Success</span>
                                                <?php elseif ($h['inserted'] > 0 || $h['updated'] > 0): ?>
                                                    <span class="badge bg-light-warning text-warning px-3 py-1">Partial</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light-danger text-danger px-3 py-1">Failed</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                                            No import history logs found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-primary {
        background-color: #E8F1FC !important;
    }

    .bg-light-success {
        background-color: #E6F6ED !important;
    }

    .bg-light-info {
        background-color: #E5F6F8 !important;
    }

    .bg-light-secondary {
        background-color: #F5F6F8 !important;
    }

    .bg-light-danger {
        background-color: #FDF2F2 !important;
    }

    .bg-light-warning {
        background-color: #FEF9EB !important;
    }

    .text-warning-dark {
        color: #8F6B00 !important;
    }

    .bg-light-success.text-success {
        color: #2B7A4B !important;
    }

    .bg-light-warning.text-warning {
        color: #A07800 !important;
    }

    .bg-light-danger.text-danger {
        color: #C81E1E !important;
    }

    #drop_zone:hover {
        border-color: #0d6efd !important;
        background-color: #f1f7fe !important;
        transform: scale(1.01);
    }

    .anim-bounce {
        animation: bounce 2s infinite;
    }

    @keyframes bounce {

        0%,
        20%,
        50%,
        80%,
        100% {
            transform: translateY(0);
        }

        40% {
            transform: translateY(-8px);
        }

        60% {
            transform: translateY(-4px);
        }
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('javascript_section') ?>
<link href="<?= base_url(); ?>/assets/plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
<script src="<?= base_url(); ?>/assets/plugins/custom/datatables/datatables.bundle.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize DataTable for Import History Log
        $('#import_history_table').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "order": [[2, "desc"]], // Default sort by Import Date (index 2) descending
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "pageLength": 10,
            "autoWidth": false,
            "responsive": true,
            "dom": "<'row mb-3'<'col-sm-6'l><'col-sm-6 text-end'f>>" +
                "<'table-responsive'tr>" +
                "<'row mt-3'<'col-sm-5'i><'col-sm-7 text-end'p>>",
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries"
            }
        });

        const fileInput = document.getElementById('import_file');
        const dropZone = document.getElementById('drop_zone');
        const uploadPrompt = document.getElementById('upload_prompt');
        const fileInfo = document.getElementById('file_selected_info');
        const selectedFileName = document.getElementById('selected_file_name');
        const selectedFileSize = document.getElementById('selected_file_size');
        const changeFileBtn = document.getElementById('change_file_btn');
        const submitBtn = document.getElementById('submit_btn');
        const importForm = document.getElementById('import_form');
        const btnText = document.getElementById('btn_text');
        const btnSpinner = document.getElementById('btn_spinner');

        // Trigger input click when clicking drop zone (except if clicking input directly, which bubble does)
        dropZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('border-solid');
            this.style.backgroundColor = '#dcebfd';
        });

        dropZone.addEventListener('dragleave', function (e) {
            e.preventDefault();
            this.classList.remove('border-solid');
            this.style.backgroundColor = '#E8F1FC';
        });

        dropZone.addEventListener('drop', function (e) {
            this.classList.remove('border-solid');
            this.style.backgroundColor = '#E8F1FC';
        });

        fileInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                const sizeKb = (file.size / 1024).toFixed(1);

                selectedFileName.textContent = file.name;
                selectedFileSize.textContent = `${sizeKb} KB`;

                uploadPrompt.classList.add('d-none');
                fileInfo.classList.remove('d-none');
                submitBtn.removeAttribute('disabled');
            } else {
                resetFileInput();
            }
        });

        changeFileBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            resetFileInput();
            fileInput.click();
        });

        function resetFileInput() {
            fileInput.value = '';
            uploadPrompt.classList.remove('d-none');
            fileInfo.classList.add('d-none');
            submitBtn.setAttribute('disabled', 'disabled');
        }

        importForm.addEventListener('submit', function () {
            submitBtn.setAttribute('disabled', 'disabled');
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
        });
    });
</script>
<?= $this->endSection() ?>