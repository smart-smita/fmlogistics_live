<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<li class="breadcrumb-item text-muted">
    <a href="<?= base_url('Masters/GembaNcTracker') ?>" class="text-muted text-hover-primary">Gemba HSE NC Tracker</a>
</li>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>

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
            <a href="javascript:void(0)" onclick="exportToExcel()" class="btn btn-sm btn-success me-2">
                <i class="fas fa-file-excel me-2"></i>Export Excel
            </a>
            <a href="javascript:void(0)" onclick="exportToCSV()" class="btn btn-sm btn-info">
                <i class="fas fa-file-csv me-2"></i>Export CSV
            </a>
        </div>
    </div>
    <div class="card-body py-3">
        <form id="filter_form">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">Region</label>
                    <select name="region[]" id="region" class="form-select select2-filter" multiple="multiple" data-control="select2" data-placeholder="All Regions">
                        <?php foreach ($regions as $r): ?>
                            <option value="<?= $r['region'] ?>"><?= $r['region'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">Audit Category</label>
                    <select name="audit_category[]" class="form-select select2-filter" multiple="multiple" data-control="select2" data-placeholder="All Categories">
                        <?php foreach ($audit_categories as $a): ?>
                            <option value="<?= htmlspecialchars($a['audit_category']) ?>"><?= htmlspecialchars($a['audit_category']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">Site Category</label>
                    <select name="site_category[]" id="site_category" class="form-select select2-filter" multiple="multiple" data-control="select2" data-placeholder="All Categories">
                        <?php foreach ($site_categories as $sc): ?>
                            <option value="<?= htmlspecialchars($sc['site_category']) ?>"><?= htmlspecialchars($sc['site_category']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">Site Name</label>
                    <select name="site_name[]" id="site_name" class="form-select select2-filter" multiple="multiple" data-control="select2" data-placeholder="All Sites">
                        <?php foreach ($site_names as $sn): ?>
                            <option value="<?= htmlspecialchars($sn['site_name']) ?>"><?= htmlspecialchars($sn['site_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">Point Status</label>
                    <select name="point_status[]" class="form-select select2-filter" multiple="multiple" data-control="select2" data-placeholder="All Status">
                        <option value="Open">Open</option>
                        <option value="Closed">Closed</option>
                        <option value="Excluded">Excluded</option>
                        <option value="Hold-review with Client">Hold-review with Client</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-dark">NC Type</label>
                    <select name="nc_recommendation[]" class="form-select select2-filter" multiple="multiple" data-control="select2" data-placeholder="All Types">
                        <?php foreach ($nc_types as $n): ?>
                            <option value="<?= htmlspecialchars($n['nc_recommendation']) ?>"><?= htmlspecialchars($n['nc_recommendation']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" id="apply_filter">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </div>
            <?php if(isset($nc_status)): ?>
                <input type="hidden" name="nc_status" value="<?= htmlspecialchars($nc_status) ?>">
            <?php endif; ?>
        </form>
    </div>
</div>

<style>
    /* New Tracker Cards */
    .t-card {
        background: #fff;
        border-radius: 16px;
        padding: 15px 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        border-bottom: 4px solid transparent;
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .t-card::before {
        content: '';
        position: absolute;
        right: 0;
        bottom: 0;
        width: 140px;
        height: 120px;
        background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><path d="M0,100 C30,70 70,50 100,0 L100,100 Z" fill="currentColor" opacity="0.05"/></svg>');
        background-size: cover;
        background-position: right bottom;
        z-index: 1;
    }

    .t-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 24px;
        position: relative;
        z-index: 2;
    }

    .t-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #4b5563;
        margin-bottom: 12px;
        position: relative;
        z-index: 2;
    }

    .t-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 16px;
        position: relative;
        z-index: 2;
    }

    /* Colors */
    .t-blue { border-bottom-color: #3b82f6; }
    .t-blue .t-icon-box { background: #eff6ff; color: #3b82f6; }
    .t-blue .t-value { color: #2563eb; }

    .t-red { border-bottom-color: #ef4444; }
    .t-red .t-icon-box { background: #fef2f2; color: #ef4444; }
    .t-red .t-value { color: #dc2626; }

    .t-green { border-bottom-color: #22c55e; }
    .t-green .t-icon-box { background: #f0fdf4; color: #22c55e; }
    .t-green .t-value { color: #16a34a; }

    .t-purple { border-bottom-color: #a855f7; }
    .t-purple .t-icon-box { background: #faf5ff; color: #a855f7; }
    .t-purple .t-value { color: #9333ea; }

    .t-orange { border-bottom-color: #f97316; }
    .t-orange .t-icon-box { background: #fff7ed; color: #f97316; }
    .t-orange .t-value { color: #ea580c; }
    
    .t-info { border-bottom-color: #0dcaf0; }
    .t-info .t-icon-box { background: #cff4fc; color: #0dcaf0; }
    .t-info .t-value { color: #087990; }

    .t-warning { border-bottom-color: #ffc107; }
    .t-warning .t-icon-box { background: #fff3cd; color: #ffc107; }
    .t-warning .t-value { color: #997404; }

    .t-dark { background: linear-gradient(145deg, #1e1e2d, #151521); border-bottom-color: #1e1e2d; }
    .t-dark::before { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><path d="M0,100 C20,80 40,80 60,100 L80,60 L100,100 Z" fill="%23ffffff" opacity="0.05"/></svg>'); }
    .t-dark .t-icon-box { background: rgba(255, 255, 255, 0.1); color: #fff; }
    .t-dark .t-title { color: #fff; }
    .t-dark .t-value { color: #fff; }

    .card-filter {
        cursor: pointer;
        transition: transform 0.2s ease-in-out;
    }
    .card-filter:hover {
        transform: translateY(-5px);
    }
</style>

<!-- Summary Cards for Workflow -->
<div class="row g-4 mb-5" id="tracker-cards">
    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
        <a href="<?= base_url('Masters/GembaNcTracker') ?>" class="text-decoration-none">
            <div class="t-card t-blue card-filter">
                <div class="t-icon-box"><i class="fas fa-clipboard-list"></i></div>
                <div class="t-title">Total Points</div>
                <div class="t-value" id="stat_total"><?= number_format($stats['total'] ?? 0) ?></div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
        <a href="<?= base_url('Masters/GembaNcTracker/template_type_filter/0') ?>" class="text-decoration-none">
            <div class="t-card t-red card-filter">
                <div class="t-icon-box"><i class="fas fa-exclamation-circle"></i></div>
                <div class="t-title">Open</div>
                <div class="t-value" id="stat_wf_open"><?= $stats['workflow_open'] ?? 0 ?></div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
        <a href="<?= base_url('Masters/GembaNcTracker/template_type_filter/1') ?>" class="text-decoration-none">
            <div class="t-card t-info card-filter">
                <div class="t-icon-box"><i class="fas fa-spinner"></i></div>
                <div class="t-title">Working</div>
                <div class="t-value" id="stat_wf_working"><?= $stats['workflow_working'] ?? 0 ?></div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
        <a href="<?= base_url('Masters/GembaNcTracker/template_type_filter/5') ?>" class="text-decoration-none">
            <div class="t-card t-purple card-filter">
                <div class="t-icon-box"><i class="fas fa-users"></i></div>
                <div class="t-title">Cluster Review</div>
                <div class="t-value" id="stat_wf_cluster"><?= $stats['workflow_cluster_review'] ?? 0 ?></div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
        <a href="<?= base_url('Masters/GembaNcTracker/template_type_filter/2') ?>" class="text-decoration-none">
            <div class="t-card t-warning card-filter">
                <div class="t-icon-box"><i class="fas fa-user-check"></i></div>
                <div class="t-title">Auditor Review</div>
                <div class="t-value" id="stat_wf_auditor"><?= $stats['workflow_auditor_review'] ?? 0 ?></div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
        <a href="<?= base_url('Masters/GembaNcTracker/template_type_filter/3') ?>" class="text-decoration-none">
            <div class="t-card t-green card-filter">
                <div class="t-icon-box"><i class="fas fa-check-circle"></i></div>
                <div class="t-title">Closed</div>
                <div class="t-value" id="stat_wf_closed"><?= $stats['workflow_closed'] ?? 0 ?></div>
            </div>
        </a>
    </div>
    <div class="col-xl-2 col-lg-3 col-md-4 col-6">
        <a href="<?= base_url('Masters/GembaNcTracker/template_type_filter/6') ?>" class="text-decoration-none">
            <div class="t-card t-dark card-filter">
                <div class="t-icon-box"><i class="fas fa-times-circle"></i></div>
                <div class="t-title">Rejected</div>
                <div class="t-value" id="stat_wf_rejected"><?= $stats['workflow_rejected'] ?? 0 ?></div>
            </div>
        </a>
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
                        <textarea class="form-control form-control-solid" id="details_nc_remark"
                            name="nc_remark" rows="4" placeholder="Please enter NC remark"></textarea>
                    </div>
                    <div class="fv-row mb-0">
                        <label class="required fs-6 fw-bold mb-2">NC After Proof</label>
                        <input type="file" class="form-control" id="details_nc_after_photo"
                            name="nc_after_photo"
                            accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xls,.xlsx,.doc,.docx" />
                        <div id="details_after_photo_preview" class="mt-3 text-center"></div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-center pb-10 border-0">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="submit_details" class="btn btn-primary">
                        <span class="indicator-label">Save Details</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<script>
    $(document).ready(function () {
        // Initialize Select2
        $('.select2-filter').select2({
            placeholder: "Select option",
            allowClear: true
        });

        // DataTable re-initialization with filters
        window.reload_data_table = function () {
            var params = $('#filter_form').serialize();
            var url = '<?= base_url("Masters/GembaNcTracker/table_ajax") ?>?' + params;

            if ($.fn.DataTable.isDataTable('#gemba_nc_tracker_table')) {
                $('#gemba_nc_tracker_table').DataTable().ajax.url(url).load();
            }
        };

        window.refresh_summary_stats = function () {
            var params = $('#filter_form').serialize();
            $.get('<?= base_url("Masters/GembaNcTracker/stats_ajax") ?>?' + params, function (res) {
                $('#stat_total').text(parseInt(res.total).toLocaleString());
                $('#stat_wf_open').text(res.workflow_open);
                $('#stat_wf_working').text(res.workflow_working);
                $('#stat_wf_cluster').text(res.workflow_cluster_review);
                $('#stat_wf_auditor').text(res.workflow_auditor_review);
                $('#stat_wf_closed').text(res.workflow_closed);
                $('#stat_wf_rejected').text(res.workflow_rejected);
            });
        };

        // Trigger reload on filter button click
        $('#apply_filter').on('click', function () {
            reload_data_table();
            refresh_summary_stats();
        });

        // Reset Filters
        $('#reset_filters').on('click', function () {
            $('#filter_form')[0].reset();
            $('.select2-filter').val(null).trigger('change');
            reload_data_table();
            refresh_summary_stats();
        });

        // Action Buttons Handler (Change Status)
        $(document).on('click', '.w-action-btn', function () {
            var id = $(this).data('id');
            var action = $(this).data('action');
            
            var confirmTitle = 'Are you sure?';
            var confirmText = 'You are about to change the status of this finding.';
            
            if (action == 'closed') {
                confirmTitle = 'Close Finding';
                confirmText = 'This will mark the finding as closed.';
            }

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
        });

        // Details Form Open
        $(document).on('click', '.w-form-btn', function () {
            var id = $(this).data('id');
            $('#details_id').val(id);
            
            // Fetch Details
            $.get('<?= base_url('Masters/GembaNcTracker/get_form_data') ?>/' + id, function (res) {
                if (res.status == 1) {
                    $('#details_unique_no').text(res.data.unique_no);
                    $('#details_observation').val(res.data.observation_point);
                    $('#details_nc_remark').val(res.data.nc_remark);
                    
                    if(res.data.after_photo_url) {
                        $('#details_after_photo_preview').html('<img src="'+res.data.after_photo_url+'" style="max-width:100%;max-height:200px" />');
                    } else {
                        $('#details_after_photo_preview').html('');
                    }
                    
                    $('#add_modal').modal('show');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            });
        });

        // Submit Details Form
        $('#submit_details').on('click', function () {
            var form = $('#details_form')[0];
            var formData = new FormData(form);
            
            var btn = $(this);
            btn.attr('disabled', true).text('Processing...');

            $.ajax({
                url: '<?= base_url('Masters/GembaNcTracker/save_details') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    btn.attr('disabled', false).text('Save Details');
                    if (res.status == 'success') {
                        Swal.fire('Success', res.message, 'success');
                        $('#add_modal').modal('hide');
                        reload_data_table();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function() {
                    btn.attr('disabled', false).text('Save Details');
                    Swal.fire('Error', 'An error occurred', 'error');
                }
            });
        });
    });

    function exportToExcel() {
        var params = $('#filter_form').serializeArray();
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
</script>
<?php $this->endSection(); ?>
