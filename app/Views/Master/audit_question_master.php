<?php 
$form_id = $button_id;
?>
<?php $this->extend("Layout/base_admin"); ?>
<?php 
    $this->section("breadcrumb_title_li");
?>
<li class="breadcrumb-item text-muted">
    <a href="<?= current_url() ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"HSE Audit Questions"?></a>
</li>
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>
<div class="card mb-5 mb-xl-8">
    <div class="card-header border-0 pt-5 flex-wrap gap-3">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1">HSE Audit Questions & Configuration</span>
            <span class="text-muted mt-1 fw-bold fs-7">Manage centralized template questions and site-specific question configurations</span>
        </h3>
        <div class="card-toolbar d-flex flex-wrap gap-2">
            <a href="javascript:void(0);" onclick="exportQuestionsCsv();" class="btn btn-sm btn-light-success">
                <i class="fa fa-file-csv me-1"></i> Export CSV
            </a>
            <a href="javascript:void(0);" onclick="downloadQuestionsPdf();" class="btn btn-sm btn-light-primary">
                <i class="fa fa-file-pdf me-1"></i> Download PDF
            </a>
        </div>
    </div>
    <div class="card-body pt-0">
        <!-- Filter Card (Responsive) -->
        <div class="p-4 p-md-5 border rounded bg-light mb-6 shadow-xs">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-xl-3">
                    <label class="fs-6 fw-bold mb-2">Audit Template</label>
                    <select class="form-select form-select-solid" id="filter_template_id">
                        <?php foreach($template_list as $tpl): ?>
                            <option value="<?=$tpl['audit_template_id']?>" <?= ($tpl['audit_template_id'] == ($selected_template_id ?? 0)) ? 'selected' : '' ?>>
                                #<?=$tpl['audit_template_id']?> - <?=$tpl['audit_name']?> (<?=$tpl['status'] == 1 ? 'Active' : 'Inactive'?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <label class="fs-6 fw-bold mb-2">Site / Client (Optional)</label>
                    <select class="form-select form-select-solid" id="filter_site_name">
                        <option value="">All Sites (Template Default)</option>
                        <?php foreach($site_list as $st): ?>
                            <option value="<?=htmlspecialchars($st['client_name'])?>"><?=htmlspecialchars($st['client_name'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <label class="fs-6 fw-bold mb-2">Site Category</label>
                    <select class="form-select form-select-solid" id="filter_site_category">
                        <option value="">All Site Categories</option>
                        <?php foreach($site_categories as $sc): ?>
                            <option value="<?=$sc['site_category_name']?>" data-id="<?=$sc['site_category_id']?>"><?=$sc['site_category_name']?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <label class="fs-6 fw-bold mb-2">Sub Category</label>
                    <select class="form-select form-select-solid" id="filter_sub_category">
                        <option value="">All Sub Categories</option>
                        <?php foreach($sub_categories as $sub): ?>
                            <option value="<?=$sub['sub_category_name']?>" data-site-id="<?=$sub['site_category_id']?>"><?=$sub['sub_category_name']?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 gap-2 pt-2 border-top">
                <span class="text-muted fs-7">
                    <i class="fas fa-info-circle text-primary me-1"></i> Select a site to view or customize site-specific question overrides.
                </span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-secondary" id="btn_reset_filters">
                        <i class="fas fa-undo me-1"></i> Reset Filters
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" id="btn_apply_filters">
                        <i class="fas fa-filter me-1"></i> Apply Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Lock Warning Banner -->
        <div id="template_lock_alert" class="alert alert-warning d-flex align-items-center p-4 mb-5 d-none">
            <i class="fas fa-lock fs-2hx text-warning me-4"></i>
            <div class="d-flex flex-column">
                <h5 class="mb-1 text-warning">Template Protected (Audits Performed)</h5>
                <span>Audits have already been conducted against this template. Canonical template questions cannot be edited or deleted. However, you can freely create and edit site-specific question configurations below.</span>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <?=$table?>
</div>
<?php $this->endSection(); ?>

<?php  $this->section("modals_section"); ?>
<div class="modal fade" id="<?=$button_id?>" tabindex="-1" aria-hidden="true">
    <form id="<?=$form_id?>_form" onsubmit="return false;">
        <input type="hidden" name="template_question_id" id="modal_template_question_id" />
        <input type="hidden" name="audit_template_id" id="modal_audit_template_id" />
        <input type="hidden" name="question_id" id="modal_override_question_id" />

        <div class="modal-dialog modal-dialog-centered modal-xl mw-1000px w-100 mx-auto p-2 p-md-3">
            <div class="modal-content">
                <div class="modal-header py-4">
                    <h3 class="modal-title fw-bold" id="modal_title_text">Configure HSE Audit Question</h3>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="modal-body py-4 px-4 px-md-8">
                    
                    <!-- Locked Notice inside Modal -->
                    <div id="modal_locked_alert" class="alert alert-danger d-flex align-items-center p-3 mb-4 d-none">
                        <i class="fas fa-lock fs-2 text-danger me-3"></i>
                        <div class="fs-7">
                            <strong>Template Question Locked:</strong> Audits have already been conducted using this template. Canonical question text, categories, CAPA, and risk parameters cannot be edited or deleted. To customize question values for a specific site, select a Site in the filters.
                        </div>
                    </div>

                    <div class="scroll-y pe-2 pe-md-4" id="user" data-kt-scroll="true" data-kt-scroll-max-height="auto" data-kt-scroll-offset="200px">
                        
                        <!-- Configuration Scope Context -->
                        <div class="p-3 p-md-4 bg-light-primary rounded mb-5 border border-primary border-opacity-25">
                            <h5 class="text-primary mb-3 fs-6"><i class="fas fa-sliders-h text-primary me-2"></i>Configuration Scope</h5>
                            <div class="row g-3">
                                <div class="col-12 col-md-4 fv-row">
                                    <label class="fs-7 fw-bold mb-1">Target Site / Client</label>
                                    <select class="form-select form-select-solid form-select-sm" name="site_name" id="modal_site_name">
                                        <option value="">All Sites (Template Default)</option>
                                        <?php foreach($site_list as $st): ?>
                                            <option value="<?=htmlspecialchars($st['client_name'])?>"><?=htmlspecialchars($st['client_name'])?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="text-muted fs-8">Select specific site for site-only override</span>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4 fv-row">
                                    <label class="fs-7 fw-bold mb-1">Site Category</label>
                                    <select class="form-select form-select-solid form-select-sm" name="audit_site_category_name" id="modal_site_category">
                                        <option value="">General / All</option>
                                        <?php foreach($site_categories as $sc): ?>
                                            <option value="<?=$sc['site_category_name']?>"><?=$sc['site_category_name']?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4 fv-row">
                                    <label class="fs-7 fw-bold mb-1">Sub Category</label>
                                    <select class="form-select form-select-solid form-select-sm" name="audit_sub_category_name" id="modal_sub_category">
                                        <option value="">General / All</option>
                                        <?php foreach($sub_categories as $sub): ?>
                                            <option value="<?=$sub['sub_category_name']?>"><?=$sub['sub_category_name']?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 15 Fields -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-sm-4 col-md-3 fv-row">
                                <label class="required fs-7 fw-bold mb-1">Category ID (Field 1)</label>
                                <input type="text" class="form-control form-control-solid form-control-sm" name="category_id" id="modal_category_id" required />
                            </div>
                            <div class="col-12 col-sm-8 col-md-6 fv-row">
                                <label class="required fs-7 fw-bold mb-1">Category Name (Field 2)</label>
                                <input type="text" class="form-control form-control-solid form-control-sm" id="modal_category_name" name="category_name" required />
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 fv-row">
                                <label class="fs-7 fw-bold mb-1">Default Value (Field 4)</label>
                                <select class="form-select form-select-solid form-select-sm" name="default_value" id="modal_default_value">
                                    <option value="">(Blank)</option>
                                    <option value="NA">NA</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                        </div>

                        <div class="fv-row mb-4">
                            <label class="required fs-7 fw-bold mb-1">Question Text (Field 3)</label>
                            <textarea class="form-control form-control-solid" rows="2" name="question_text" id="modal_question_text" required></textarea>
                        </div>

                        <div class="separator separator-dashed my-4"></div>

                        <h5 class="mb-3 text-dark fs-6"><i class="fas fa-clipboard-list text-info me-2"></i>CAPA & Risk Fields (Fields 5 to 15)</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6 fv-row">
                                <label class="fs-7 fw-bold mb-1">Findings (Assertive Statement) (Field 5)</label>
                                <textarea class="form-control form-control-solid form-control-sm" rows="2" name="findings" id="modal_findings" placeholder="Assertive statement when finding is NO"></textarea>
                            </div>
                            <div class="col-12 col-md-6 fv-row">
                                <label class="fs-7 fw-bold mb-1">Risk (Field 6)</label>
                                <textarea class="form-control form-control-solid form-control-sm" rows="2" name="risk" id="modal_risk" placeholder="Identified risk"></textarea>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6 fv-row">
                                <label class="fs-7 fw-bold mb-1">Actions / Recommendation (Field 7)</label>
                                <textarea class="form-control form-control-solid form-control-sm" rows="2" name="actions_recommendation" id="modal_actions" placeholder="Recommended CAPA actions"></textarea>
                            </div>
                            <div class="col-12 col-md-6 fv-row">
                                <label class="fs-7 fw-bold mb-1">Note (Field 15)</label>
                                <textarea class="form-control form-control-solid form-control-sm" rows="2" name="note" id="modal_note" placeholder="Additional instructions or notes"></textarea>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-4 fv-row">
                                <label class="fs-7 fw-bold mb-1">Action Category (Field 8)</label>
                                <input type="text" class="form-control form-control-solid form-control-sm" name="action_category" id="modal_action_category" placeholder="e.g. Certificates, Engineering" />
                            </div>
                            <div class="col-12 col-sm-4 fv-row">
                                <label class="fs-7 fw-bold mb-1">UA-UC (Field 9)</label>
                                <input type="text" class="form-control form-control-solid form-control-sm" name="ua_uc" id="modal_ua_uc" placeholder="e.g. Others, Unsafe Act, Unsafe Condition" />
                            </div>
                            <div class="col-12 col-sm-4 fv-row">
                                <label class="fs-7 fw-bold mb-1">Risk Severity (Field 10)</label>
                                <input type="text" class="form-control form-control-solid form-control-sm" name="risk_severity" id="modal_risk_severity" placeholder="e.g. Very High, High, Medium, Low" />
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-sm-6 col-md-3 fv-row">
                                <label class="fs-7 fw-bold mb-1">Risk Probability (Field 11)</label>
                                <input type="text" class="form-control form-control-solid form-control-sm" name="risk_probability" id="modal_risk_probability" placeholder="e.g. Very High, High" />
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 fv-row">
                                <label class="fs-7 fw-bold mb-1">Color Code (Field 12)</label>
                                <input type="text" class="form-control form-control-solid form-control-sm" name="color_code" id="modal_color_code" placeholder="e.g. Black, Red, Orange, Yellow" />
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 fv-row">
                                <label class="fs-7 fw-bold mb-1">Cost Type (Field 13)</label>
                                <input type="text" class="form-control form-control-solid form-control-sm" name="cost_type" id="modal_cost_type" placeholder="e.g. Capex, Opex" />
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 fv-row">
                                <label class="fs-7 fw-bold mb-1">Combined Risk Rating (Field 14)</label>
                                <input type="text" class="form-control form-control-solid form-control-sm" name="combined_risk_rating" id="modal_combined_risk_rating" placeholder="e.g. 225, 150, 96" />
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                    <div id="modal_lock_status_badge" class="d-none"></div>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="reset" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="ajax_click_submit" class="btn btn-sm btn-primary">
                            <span class="indicator-label"><i class="fas fa-save me-1"></i> Save Configuration</span>
                            <span class="indicator-progress">Saving... 
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php $this->endSection(); ?>

<?php $this->section("javascript_section");?>
<script>
var currentTemplateLocked = false;

// Filter triggers
$("#filter_site_category").on("change", function() {
    var siteId = $(this).find(':selected').data('id');
    $("#filter_sub_category option").each(function() {
        var subSiteId = $(this).data('site-id');
        if (siteId && subSiteId && siteId != subSiteId) {
            $(this).hide();
        } else {
            $(this).show();
        }
    });
    if ($("#filter_sub_category option:selected").css('display') === 'none') {
        $("#filter_sub_category").val("");
    }
});

$("#btn_apply_filters").on("click", function() {
    reloadQuestionsTable();
});

$("#btn_reset_filters").on("click", function() {
    $("#filter_site_name").val("");
    $("#filter_site_category").val("");
    $("#filter_sub_category").val("");
    reloadQuestionsTable();
});

$("#filter_template_id").on("change", function() {
    var tplId = $(this).val();
    if (window.history && window.history.replaceState) {
        var newUrl = window.location.pathname + '?audit_template_id=' + tplId;
        window.history.replaceState({path: newUrl}, '', newUrl);
    }
    reloadQuestionsTable();
});

function reloadQuestionsTable() {
    const tableId = '#hse_questions_table';
    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().ajax.reload(null, false);
    } else if ($.fn.DataTable.isDataTable('table.dataTable')) {
        $('table.dataTable').DataTable().ajax.reload(null, false);
    } else if ($.fn.DataTable.isDataTable('#kt_table_1')) {
        $('#kt_table_1').DataTable().ajax.reload(null, false);
    }
}

// Hook into DataTables pre-XHR to inject filters
$(document).on('preXhr.dt', function(e, settings, data) {
    data.audit_template_id = $("#filter_template_id").val();
    data.site_name = $("#filter_site_name").val();
    data.site_category = $("#filter_site_category").val();
    data.sub_category = $("#filter_sub_category").val();
});

// Hook into DataTables post-XHR to detect lock status
$(document).on('xhr.dt', function(e, settings, json, xhr) {
    if (json) {
        currentTemplateLocked = json.is_locked || false;
        if (currentTemplateLocked) {
            $("#template_lock_alert").removeClass('d-none');
            $("[data-bs-target='#user_modal']").hide();
        } else {
            $("#template_lock_alert").addClass('d-none');
            $("[data-bs-target='#user_modal']").show();
        }
    }
});

function open_config_modal(templateQuestionId, isViewOnly) {
    var url = "<?=base_url('Masters/Audit_question_master/get_form_data')?>/" + templateQuestionId;
    var params = {
        template_question_id: templateQuestionId,
        audit_template_id: $("#filter_template_id").val(),
        site_name: $("#filter_site_name").val(),
        site_category: $("#filter_site_category").val(),
        sub_category: $("#filter_sub_category").val()
    };

    $.getJSON(url, params, function(res) {
        if (res.status == 1) {
            var d = res.data;
            $("#modal_template_question_id").val(d.template_question_id);
            $("#modal_audit_template_id").val(d.audit_template_id);
            $("#modal_override_question_id").val(d.question_id || "");

            // Sync scope selectors with active filter or override
            $("#modal_site_name").val(d.site_name || $("#filter_site_name").val());
            $("#modal_site_category").val(d.audit_site_category_name || $("#filter_site_category").val());
            $("#modal_sub_category").val(d.audit_sub_category_name || $("#filter_sub_category").val());

            // 15 fields
            $("#modal_category_id").val(d.category_id || "");
            $("#modal_category_name").val(d.category_name || "");
            $("#modal_question_text").val(d.question_text || "");
            
            // Default value: support blank or NA
            var defVal = (d.default_value !== undefined && d.default_value !== null) ? d.default_value : "";
            $("#modal_default_value").val(defVal);

            $("#modal_findings").val(d.findings || "");
            $("#modal_risk").val(d.risk || "");
            $("#modal_actions").val(d.actions_recommendation || "");
            $("#modal_action_category").val(d.action_category || "");
            $("#modal_ua_uc").val(d.ua_uc || "");
            $("#modal_risk_severity").val(d.risk_severity || "");
            $("#modal_risk_probability").val(d.risk_probability || "");
            $("#modal_color_code").val(d.color_code || "");
            $("#modal_cost_type").val(d.cost_type || "");
            $("#modal_combined_risk_rating").val(d.combined_risk_rating || "");
            $("#modal_note").val(d.note || "");

            // Lock / View Mode Handling
            var activeSite = $("#filter_site_name").val();
            var isLocked = isViewOnly || (d.is_locked && !d.is_override && !activeSite);

            if (isLocked) {
                $("#modal_locked_alert").removeClass('d-none');
                $("#modal_title_text").html('<span class="text-danger"><i class="fas fa-lock me-2"></i> View HSE Template Question (Locked - Audits Performed)</span>');
                $("#<?=$form_id?>_form input, #<?=$form_id?>_form textarea, #<?=$form_id?>_form select").prop('disabled', true);
                $("#ajax_click_submit").hide();
                $("#modal_lock_status_badge").html('<span class="badge badge-light-danger fs-7"><i class="fas fa-lock me-1"></i> Locked (Audits Exist)</span>').removeClass('d-none');
            } else {
                $("#modal_locked_alert").addClass('d-none');
                $("#<?=$form_id?>_form input, #<?=$form_id?>_form textarea, #<?=$form_id?>_form select").prop('disabled', false);
                $("#ajax_click_submit").show();
                $("#modal_lock_status_badge").addClass('d-none');

                if (d.is_override) {
                    $("#modal_title_text").html('<span class="text-success"><i class="fas fa-edit me-1"></i> Edit Site Question Configuration</span>');
                } else if (activeSite) {
                    $("#modal_title_text").html('<span class="text-primary"><i class="fas fa-sliders-h me-1"></i> Customize Question for ' + activeSite + '</span>');
                } else {
                    $("#modal_title_text").html('<span><i class="fas fa-edit me-1"></i> Edit Base HSE Question</span>');
                }
            }

            $("#<?=$button_id?>").modal("show");
        } else {
            toastr.error(res.message || "Could not fetch question details.");
        }
    }).fail(function() {
        toastr.error("Error communicating with server.");
    });
}

// Modal save submission
$("#ajax_click_submit").on("click", function() {
    var $btn = $(this);
    var formEl = document.getElementById("<?=$form_id?>_form");
    var formData = new FormData(formEl);

    $btn.prop("disabled", true).find(".indicator-label").hide();
    $btn.find(".indicator-progress").show();

    $.ajax({
        url: "<?=base_url('Masters/Audit_question_master/save_details')?>",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(res) {
            $btn.prop("disabled", false).find(".indicator-label").show();
            $btn.find(".indicator-progress").hide();

            if (res.status == 1) {
                toastr.success(res.message);
                $("#<?=$button_id?>").modal("hide");
                reloadQuestionsTable();
            } else {
                toastr.warning(res.message);
            }
        },
        error: function(xhr) {
            $btn.prop("disabled", false).find(".indicator-label").show();
            $btn.find(".indicator-progress").hide();
            toastr.error("Failed to save question configuration.");
        }
    });
});

function reset_override(overrideQuestionId) {
    Swal.fire({
        title: 'Reset to Template Default?',
        text: 'This will remove the site-specific customization and restore original template question defaults.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, reset!',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post("<?=base_url('Masters/Audit_question_master/save_details')?>", {
                question_id: overrideQuestionId,
                action: 'reset_override'
            }, function(res) {
                if (typeof res === 'string') res = JSON.parse(res);
                if (res.status == 1) {
                    toastr.success(res.message);
                    reloadQuestionsTable();
                } else {
                    toastr.warning(res.message);
                }
            });
        }
    });
}

function delete_row(obj, tqId) {
    if (currentTemplateLocked) {
        toastr.warning("Cannot delete question: Template is locked because audits have already been performed.");
        return;
    }
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to delete this template question?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post("<?=base_url('Masters/Audit_question_master/save_details')?>/" + tqId + "/delete", {}, function(res) {
                if (typeof res === 'string') res = JSON.parse(res);
                if (res.status == 1) {
                    toastr.success(res.message);
                    reloadQuestionsTable();
                } else {
                    toastr.warning(res.message);
                }
            });
        }
    });
}

function exportQuestionsCsv() {
    var tplId = $("#filter_template_id").val() || <?=json_encode($selected_template_id ?? 0)?>;
    var siteName = $("#filter_site_name").val() || "";
    var siteCategory = $("#filter_site_category").val() || "";
    var subCategory = $("#filter_sub_category").val() || "";
    var searchVal = $('input[type="search"]').val() || "";

    var url = "<?=base_url('Masters/Audit_question_master/export_csv')?>?audit_template_id=" + encodeURIComponent(tplId)
        + "&site_name=" + encodeURIComponent(siteName)
        + "&site_category=" + encodeURIComponent(siteCategory)
        + "&sub_category=" + encodeURIComponent(subCategory);

    if (searchVal) {
        url += "&search=" + encodeURIComponent(searchVal);
    }

    window.location.href = url;
}

function downloadQuestionsPdf() {
    var tplId = $("#filter_template_id").val();
    window.location.href = "<?=base_url('Masters/Audit_question_master/download_pdf')?>?audit_template_id=" + tplId;
}

$(document).ready(function() {
    // Intercept DataTables default export button inside the table header so it downloads complete details with CAPA
    $(document).on('click', '#custom_export_csv_btn, .buttons-csv', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        exportQuestionsCsv();
        return false;
    });
});
</script>
<?php $this->endSection();?>
