<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<li class="breadcrumb-item text-muted">
    <a href="javascript:void(0)" class="text-muted text-hover-primary">Reports</a>
</li>
<li class="breadcrumb-item text-muted">
    <span>Weekly Reports</span>
</li>
<li class="breadcrumb-item text-dark fw-bold">
    <span>RT-WeeklyML</span>
</li>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<style>
    .report-header-banner {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #ffffff;
        padding: 20px 24px;
        border-radius: 12px 12px 0 0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .report-sub-banner {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        border-left: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        padding: 12px 24px;
        border-radius: 0 0 12px 12px;
        font-size: 0.95rem;
        color: #475569;
    }
    .section-title-bar {
        background-color: #f1f5f9;
        border-left: 5px solid #3b82f6;
        padding: 12px 18px;
        font-weight: 700;
        font-size: 1.15rem;
        color: #1e293b;
        border-radius: 6px;
        margin-top: 25px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .table-sticky-wrapper {
        max-height: 460px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .table-sticky-wrapper table thead th {
        position: sticky;
        top: 0;
        background-color: #f8fafc;
        color: #334155;
        font-weight: 700;
        z-index: 5;
        border-bottom: 2px solid #cbd5e1;
        white-space: nowrap;
    }
    .grand-total-row {
        background-color: #fff7ed !important;
        font-weight: 700 !important;
        color: #9a3412 !important;
        border-top: 2px solid #fdba74 !important;
        border-bottom: 2px solid #fdba74 !important;
    }
    .num-cell {
        text-align: right;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
    }
    .cat-cell {
        text-align: left;
        font-weight: 600;
        color: #334155;
    }
    .action-btn-group .btn {
        margin-bottom: 6px;
    }
    @media print {
        .no-print, .card-toolbar, .breadcrumb, .header, .aside, .footer {
            display: none !important;
        }
        .table-sticky-wrapper {
            max-height: none !important;
            overflow: visible !important;
            border: none !important;
        }
        .main_body, .content, .container, .container-fluid, .post {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .report-header-banner {
            background: #1e293b !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .grand-total-row {
            background-color: #fff7ed !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<!-- Filter Card (No Print) -->
<div class="card mb-6 shadow-sm border-0 rounded-4 no-print">
    <div class="card-header border-0 pt-4 pb-2">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1 text-dark">
                <i class="fas fa-filter me-2 text-primary"></i>Report Filters
            </span>
            <span class="text-muted mt-1 fw-semibold fs-7">Customize reporting period, categories, and region criteria</span>
        </h3>
    </div>
    <div class="card-body py-4">
        <form id="rt_weekly_filter_form" onsubmit="return false;">
            <div class="row g-4 mb-5">
                <!-- Start Date -->
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-bold text-dark fs-7">Start Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-calendar-alt text-muted"></i></span>
                        <input type="date" class="form-control form-control-sm form-control-solid" id="filter_start_date" name="start_date" value="<?= esc($start_date) ?>">
                    </div>
                </div>

                <!-- End Date -->
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-bold text-dark fs-7">End Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-calendar-check text-muted"></i></span>
                        <input type="date" class="form-control form-control-sm form-control-solid" id="filter_end_date" name="end_date" value="<?= esc($end_date) ?>">
                    </div>
                </div>

                <!-- Audit Category (Multi-select) -->
                <div class="col-md-6 col-sm-12">
                    <label class="form-label fw-bold text-dark fs-7 d-flex justify-content-between">
                        <span>Audit Category</span>
                        <a href="javascript:void(0)" onclick="selectAllCategories()" class="fs-8 text-primary fw-semibold">Select All / Clear</a>
                    </label>
                    <select class="form-select form-select-sm form-select-solid" id="filter_audit_category" name="audit_category[]" data-control="select2" data-placeholder="All Audit Categories (Default)" multiple="multiple">
                        <?php if (!empty($options['audit_categories'])): ?>
                            <?php foreach ($options['audit_categories'] as $cat): ?>
                                <option value="<?= esc($cat['name']) ?>" <?= (!empty($filters['audit_category']) && ((is_array($filters['audit_category']) && in_array($cat['name'], $filters['audit_category'])) || $filters['audit_category'] == $cat['name'])) ? 'selected' : '' ?>>
                                    <?= esc($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>


            </div>

            <hr class="text-muted">

            <div class="d-flex flex-wrap justify-content-between align-items-center action-btn-group pt-2">
                <div>
                    <button type="button" onclick="loadReportData()" class="btn btn-sm btn-primary px-5 me-2 shadow-sm" id="btn_search">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                    <button type="button" onclick="resetFilters()" class="btn btn-sm btn-light-primary px-4 me-2">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                </div>
                <div>
                    <button type="button" onclick="triggerExport('excel')" class="btn btn-sm btn-success me-2 shadow-sm">
                        <i class="fas fa-file-excel me-2"></i>Export to Excel
                    </button>
                    <button type="button" onclick="triggerExport('csv')" class="btn btn-sm" style="background-color: #6f42c1; color: #fff; border: none;">
                        <i class="fas fa-file-csv me-2"></i>Export to CSV
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-sm btn-dark me-2">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                    <button type="button" onclick="openEmailModal()" class="btn btn-sm btn-danger shadow-sm">
                        <i class="fas fa-envelope me-2"></i>Email Report
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Report Container -->
<div class="card shadow-sm border-0 rounded-4 mb-10" id="report_display_area">
    <!-- Header Banner -->
    <div class="report-header-banner d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h2 class="text-white fw-bold m-0 ">
                <i class="fas fa-chart-line me-3 text-warning"></i>RT-WeeklyML – Weekly Gemba Update for Management Team Monthly Call
            </h2>
            <div class="text-white-50 mt-1 fs-7">
                Regional Non-Conformance (NC) Point Tracking & Closure Balance
            </div>
        </div>
        <div class="text-end mt-2 mt-sm-0">
            <span class="badge bg-warning text-dark fs-7 fw-bolder px-4 py-2 shadow-sm">
                <i class="fas fa-clock text-dark me-1"></i> Generated: <?= date('d M Y') ?>
            </span>
        </div>
    </div>

    <!-- Sub Banner for active filter readout -->
    <div class="report-sub-banner d-flex flex-wrap justify-content-between align-items-center" id="sub_banner_text">
        <div>
            <strong>Reporting Period:</strong> <span class="text-primary fw-bold" id="lbl_period"><?= date('d M Y', strtotime($start_date)) ?> to <?= date('d M Y', strtotime($end_date)) ?></span>
        </div>
        <div>
            <strong>Active Category:</strong> <span class="text-dark fw-semibold" id="lbl_category">All Categories</span>
        </div>
    </div>

    <div class="card-body p-4 p-md-6" id="report_table_container">
        <!-- Sections will be rendered here via PHP initially and updated via AJAX -->
        <?php if (!$report_data['has_records']): ?>
            <div class="alert alert-warning d-flex align-items-center p-5 mb-5 shadow-sm rounded-3">
                <i class="fas fa-exclamation-triangle fs-2hx text-warning me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-dark">No Non-Conformance (NC) Records Found</h4>
                    <span>There were no NC points found matching the specified date criteria or regional filters. Showing zero baseline tables below.</span>
                </div>
            </div>
        <?php endif; ?>

        <?php 
            $regionCols = ['HO', 'North', 'South', 'TPT', 'West-1', 'West-2', 'Total'];
            foreach ($report_data['sections'] as $secKey => $sec): 
        ?>
            <div class="section-container mb-6">
                <div class="section-title-bar">
                    <span><?= esc($sec['title']) ?> <small class="text-muted fw-normal ms-2">(<?= esc($sec['subtitle']) ?>)</small></span>
                    <span class="badge bg-primary fs-8"><?= esc($sec['title']) ?> Matrix</span>
                </div>
                <div class="table-sticky-wrapper">
                    <table class="table table-bordered table-striped align-middle table-hover fs-7 mb-0">
                        <thead>
                            <tr class="text-center">
                                <th style="min-width: 200px; text-align: left; padding-left: 16px;">Audit Category</th>
                                <th>HO</th>
                                <th>North</th>
                                <th>South</th>
                                <th>TPT</th>
                                <th>West-1</th>
                                <th>West-2</th>
                                <th class="bg-light-primary text-primary fw-bolder">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sec['rows'])): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted fw-bold">No Records Found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sec['rows'] as $row): ?>
                                    <tr>
                                        <td class="cat-cell" style="padding-left: 16px;"><?= esc($row['audit_category']) ?></td>
                                        <?php foreach (['HO', 'North', 'South', 'TPT', 'West-1', 'West-2'] as $c): ?>
                                            <td class="num-cell"><?= (int)$row[$c] ?></td>
                                        <?php endforeach; ?>
                                        <td class="num-cell fw-bolder text-primary bg-light-primary"><?= (int)$row['Total'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <!-- Grand Total Row -->
                                <tr class="grand-total-row">
                                    <td class="cat-cell text-uppercase" style="padding-left: 16px;">Grand Total</td>
                                    <?php foreach (['HO', 'North', 'South', 'TPT', 'West-1', 'West-2'] as $c): ?>
                                        <td class="num-cell"><?= (int)$sec['grand_total'][$c] ?></td>
                                    <?php endforeach; ?>
                                    <td class="num-cell fs-6"><?= (int)$sec['grand_total']['Total'] ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Email Report Modal -->
<div class="modal fade no-print" id="emailReportModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg border-0">
            <div class="modal-header bg-danger text-white px-6 py-4 rounded-top-4">
                <h3 class="modal-title text-white fw-bold m-0" id="emailModalLabel">
                    <i class="fas fa-paper-plane me-2 text-white"></i>Email RT-WeeklyML Report
                </h3>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6">
                <div class="alert alert-info d-flex align-items-center p-4 mb-5 rounded-3">
                    <i class="fas fa-file-excel fs-2x text-info me-4"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-dark fs-6">Automatic Excel Spreadsheet Attachment</span>
                        <span class="text-muted fs-8">An `.xlsx` sheet containing the current region matrices and calculations will be dynamically built and attached to this email.</span>
                    </div>
                </div>

                <form id="email_report_form" onsubmit="return false;">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark fs-7">Recipient Email Address (To) <span class="text-danger">*</span></label>
                        <input type="email" class="form-control form-control-solid" id="receiver_email" name="receiver_email" placeholder="e.g., management.team@fmlogistic.com" required>
                        <div class="form-text fs-8 text-muted">For multiple recipients, separate addresses with a comma.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark fs-7">CC Email Address(es)</label>
                        <input type="text" class="form-control form-control-solid" id="cc_emails" name="cc_emails" placeholder="e.g., hse.head@fmlogistic.com, regional.manager@fmlogistic.com">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark fs-7">Subject Line</label>
                        <input type="text" class="form-control form-control-solid fw-semibold" id="email_subject" name="subject" value="RT-WeeklyML – Weekly Gemba Update for Management Team Monthly Call">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark fs-7">Additional Notes / Remarks (Optional)</label>
                        <textarea class="form-control form-control-solid" id="additional_notes" name="additional_notes" rows="4" placeholder="Enter any specific managerial remarks or commentary on weekly Gemba non-conformance progress..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer px-6 py-4 bg-light border-0 rounded-bottom-4 d-flex justify-content-end">
                <button type="button" class="btn btn-light-secondary px-5 me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" onclick="submitEmailReport()" class="btn btn-danger px-6 shadow-sm" id="btn_send_email">
                    <span class="indicator-label"><i class="fas fa-paper-plane me-2"></i>Send Email</span>
                    <span class="indicator-progress d-none">
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span> Transmitting...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined') {
            if ($('[data-control="select2"]').length) {
                $('[data-control="select2"]').select2();
            }
        } else {
            // Fallback: poll for jQuery if it's deferred
            let checkJquery = setInterval(function() {
                if (typeof $ !== 'undefined') {
                    clearInterval(checkJquery);
                    if ($('[data-control="select2"]').length) {
                        $('[data-control="select2"]').select2();
                    }
                }
            }, 100);
        }
    });

    function selectAllCategories() {
        let sel = $('#filter_audit_category');
        if (sel.val() && sel.val().length > 0) {
            sel.val(null).trigger('change');
        } else {
            let allVals = [];
            sel.find('option').each(function() {
                if ($(this).val()) allVals.push($(this).val());
            });
            sel.val(allVals).trigger('change');
        }
    }

    function getFilterParams() {
        let cats = $('#filter_audit_category').val() || [];
        return {
            start_date: $('#filter_start_date').val(),
            end_date: $('#filter_end_date').val(),
            audit_category: Array.isArray(cats) ? cats.join(',') : cats
        };
    }

    function resetFilters() {
        $('#filter_start_date').val('<?= date("Y-m-d", strtotime("-7 days")) ?>');
        $('#filter_end_date').val('<?= date("Y-m-d") ?>');
        $('#filter_audit_category').val(null).trigger('change');
        loadReportData();
    }

    function triggerExport(type) {
        let params = getFilterParams();
        let queryStr = $.param(params);
        if (type === 'excel') {
            window.location.href = '<?= $export_excel_url ?>?' + queryStr;
        } else if (type === 'csv') {
            window.location.href = '<?= $export_csv_url ?>?' + queryStr;
        }
    }

    function loadReportData() {
        let btn = $('#btn_search');
        let params = getFilterParams();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');
        
        // Show loading state in container
        $('#report_table_container').css('opacity', '0.5');

        $.ajax({
            url: '<?= $ajax_url ?>',
            type: 'GET',
            data: params,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fas fa-search me-2"></i>Search');
                $('#report_table_container').css('opacity', '1');

                if (res && res.status == 1 && res.data) {
                    renderReportTables(res.data, params);
                    if (!res.data.has_records && typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: 'No non-conformance records found for selected filters',
                            showConfirmButton: false,
                            timer: 3500
                        });
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Unable to retrieve report data.', 'error');
                    } else {
                        alert('Unable to retrieve report data.');
                    }
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-search me-2"></i>Search');
                $('#report_table_container').css('opacity', '1');
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Server error occurred while loading report data.', 'error');
                } else {
                    alert('Server error occurred while loading report data.');
                }
            }
        });
    }

    function renderReportTables(data, params) {
        // Update sub-banner readouts
        let sDate = new Date(data.start_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        let eDate = new Date(data.end_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        $('#lbl_period').text(sDate + ' to ' + eDate);

        let catReadout = params.audit_category ? params.audit_category.replace(/,/g, ', ') : 'All Categories';
        $('#lbl_category').text(catReadout);

        let html = '';
        if (!data.has_records) {
            html += `<div class="alert alert-warning d-flex align-items-center p-5 mb-5 shadow-sm rounded-3">
                <i class="fas fa-exclamation-triangle fs-2hx text-warning me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-dark">No Non-Conformance (NC) Records Found</h4>
                    <span>There were no NC points found matching the specified date criteria or regional filters. Showing zero baseline tables below.</span>
                </div>
            </div>`;
        }

        let cols = ['HO', 'North', 'South', 'TPT', 'West-1', 'West-2'];

        $.each(data.sections, function(secKey, sec) {
            html += `<div class="section-container mb-6">
                <div class="section-title-bar">
                    <span>${sec.title} <small class="text-muted fw-normal ms-2">(${sec.subtitle})</small></span>
                    <span class="badge bg-primary fs-8">${sec.title} Matrix</span>
                </div>
                <div class="table-sticky-wrapper">
                    <table class="table table-bordered table-striped align-middle table-hover fs-7 mb-0">
                        <thead>
                            <tr class="text-center">
                                <th style="min-width: 200px; text-align: left; padding-left: 16px;">Audit Category</th>
                                <th>HO</th><th>North</th><th>South</th><th>TPT</th><th>West-1</th><th>West-2</th>
                                <th class="bg-light-primary text-primary fw-bolder">Total</th>
                            </tr>
                        </thead>
                        <tbody>`;
            
            if (!sec.rows || sec.rows.length === 0) {
                html += `<tr><td colspan="8" class="text-center py-5 text-muted fw-bold">No Records Found</td></tr>`;
            } else {
                $.each(sec.rows, function(idx, row) {
                    html += `<tr>
                        <td class="cat-cell" style="padding-left: 16px;">${row.audit_category}</td>`;
                    $.each(cols, function(cIdx, colName) {
                        html += `<td class="num-cell">${parseInt(row[colName] || 0)}</td>`;
                    });
                    html += `<td class="num-cell fw-bolder text-primary bg-light-primary">${parseInt(row['Total'] || 0)}</td>
                    </tr>`;
                });
                
                // Grand Total
                let gt = sec.grand_total || {};
                html += `<tr class="grand-total-row">
                    <td class="cat-cell text-uppercase" style="padding-left: 16px;">Grand Total</td>`;
                $.each(cols, function(cIdx, colName) {
                    html += `<td class="num-cell">${parseInt(gt[colName] || 0)}</td>`;
                });
                html += `<td class="num-cell fs-6">${parseInt(gt['Total'] || 0)}</td>
                </tr>`;
            }

            html += `</tbody></table></div></div>`;
        });

        $('#report_table_container').html(html);
    }

    function openEmailModal() {
        let params = getFilterParams();
        let sDate = params.start_date || '<?= $start_date ?>';
        let eDate = params.end_date || '<?= $end_date ?>';
        $('#email_subject').val('RT-WeeklyML – Weekly Gemba Update for Management Team Monthly Call (' + sDate + ' to ' + eDate + ')');
        $('#emailReportModal').modal('show');
    }

    function submitEmailReport() {
        let recipient = $('#receiver_email').val();
        if (!recipient || !recipient.trim()) {
            if (typeof Swal !== 'undefined') {
                Swal.fire("Validation Error", "Please enter at least one Recipient Email Address.", "warning");
            } else {
                alert("Please enter at least one Recipient Email Address.");
            }
            $('#receiver_email').focus();
            return;
        }

        let btn = $('#btn_send_email');
        let params = getFilterParams();
        
        let formData = Object.assign({}, params, {
            receiver_email: $('#receiver_email').val(),
            cc_emails: $('#cc_emails').val(),
            subject: $('#email_subject').val(),
            additional_notes: $('#additional_notes').val()
        });

        btn.prop('disabled', true);
        btn.find('.indicator-label').addClass('d-none');
        btn.find('.indicator-progress').removeClass('d-none');

        $.ajax({
            url: '<?= $send_email_url ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false);
                btn.find('.indicator-label').removeClass('d-none');
                btn.find('.indicator-progress').addClass('d-none');

                if (res && res.status == 1) {
                    $('#emailReportModal').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Email Sent Successfully!',
                            text: res.message || 'The RT-WeeklyML summary and attached Excel report have been dispatched.',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        alert('Email Sent Successfully!');
                    }
                } else {
                    let errMsg = (res && res.message) ? res.message : 'Failed to transmit email.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire("Transmission Error", errMsg, "error");
                    } else {
                        alert("Transmission Error: " + errMsg);
                    }
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false);
                btn.find('.indicator-label').removeClass('d-none');
                btn.find('.indicator-progress').addClass('d-none');
                if (typeof Swal !== 'undefined') {
                    Swal.fire("Server Error", "An HTTP error occurred while sending the report email.", "error");
                } else {
                    alert("Server Error while sending report email.");
                }
            }
        });
    }
</script>
<?php $this->endSection(); ?>
