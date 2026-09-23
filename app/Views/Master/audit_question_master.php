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
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1">Audit Question Master</span>
        </h3>
        <div class="card-toolbar">
            <a href="<?= base_url('Masters/Audit_question_master/download_pdf') ?>" class="btn btn-sm btn-light-primary me-3">
                <i class="fa fa-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>
</div>
<?=$table?>
<?php $this->endSection(); ?>

<?php  $this->section("modals_section"); ?>
<div class="modal fade" id="<?=$button_id?>" tabindex="-1" aria-hidden="true">
    <form id="<?=$form_id?>_form" onsubmit="return false;">
        <div class="modal-dialog modal-dialog-centered mw-900px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>HSE Audit Question</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="modal-body py-lg-10 px-lg-10">
                    <div class="scroll-y me-n7 pe-7" id="user" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-offset="300px">
                        <div class="row g-9 mb-8">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-5 fw-bold mb-2">Site Category</label>
                                <select class="form-select form-select-solid" name="audit_site_category_name" id="audit_site_category_name">
                                    <option value="">Select Site Category</option>
                                    <?php foreach($site_categories as $sc): ?>
                                        <option value="<?=$sc['site_category_name']?>" data-id="<?=$sc['site_category_id']?>"><?=$sc['site_category_name']?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="fs-5 fw-bold mb-2">Sub Category</label>
                                <select class="form-select form-select-solid" name="audit_sub_category_name" id="audit_sub_category_name">
                                    <option value="">Select Sub Category</option>
                                    <?php foreach($sub_categories as $sub): ?>
                                        <option value="<?=$sub['sub_category_name']?>" data-site-id="<?=$sub['site_category_id']?>"><?=$sub['sub_category_name']?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-9 mb-8">
                            <div class="col-md-4 fv-row">
                                <label class="fs-5 fw-bold mb-2">Category ID (e.g. A.1)</label>
                                <input type="text" class="form-control form-control-solid" name="audit_category_id" id="audit_category_id" />
                            </div>
                            <div class="col-md-8 fv-row">
                                <label class="required fs-5 fw-bold mb-2">Category Name</label>
                                <input type="text" class="form-control form-control-solid" id="audit_category" name="audit_category" placeholder="Enter Category Name" />
                            </div>
                        </div>

                        <div class="fv-row mb-8">
                            <label class="required fs-5 fw-bold mb-2">Audit Question</label>
                            <textarea class="form-control form-control-solid" rows="3" name="audit_question" id="audit_question" placeholder="Enter question text"></textarea>
                        </div>

                        <div class="row g-9 mb-8">
                            <div class="col-md-6 fv-row">
                                <label class="fs-5 fw-bold mb-2">Default Value</label>
                                <select class="form-select form-select-solid" name="audit_question_default_value" id="audit_question_default_value">
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                    <option value="NA" selected>NA</option>
                                </select>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="fs-5 fw-bold mb-2">Audit Template ID (Optional)</label>
                                <input type="number" class="form-control form-control-solid" name="audit_template_id" id="audit_template_id" />
                            </div>
                        </div>

                        <div class="separator separator-dashed my-10"></div>
                        
                        <h4 class="mb-5">CAPA Details (for NO findings)</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle gs-0 gy-3" id="capa_table">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="ps-4 min-w-150px">Field</th>
                                        <th class="min-w-300px">Value for "NO"</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-4">Findings</td>
                                        <td><input type="text" class="form-control form-control-sm" name="capa[findings][no]" /></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Risk</td>
                                        <td><input type="text" class="form-control form-control-sm" name="capa[risk][no]" /></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Actions</td>
                                        <td><input type="text" class="form-control form-control-sm" name="capa[actions][no]" /></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Action Category</td>
                                        <td><input type="text" class="form-control form-control-sm" name="capa[action_category][no]" /></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">UA-UC</td>
                                        <td><input type="text" class="form-control form-control-sm" name="capa[ua_uc][no]" /></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Risk Severity</td>
                                        <td><input type="text" class="form-control form-control-sm" name="capa[risk_severity][no]" /></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Risk Probability</td>
                                        <td><input type="text" class="form-control form-control-sm" name="capa[risk_probability][no]" /></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Color Code</td>
                                        <td><input type="text" class="form-control form-control-sm" name="capa[color_code][no]" /></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Cost Type</td>
                                        <td><input type="text" class="form-control form-control-sm" name="capa[cost_type][no]" /></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Combined Risk Rating</td>
                                        <td><input type="text" class="form-control form-control-sm" name="capa[combined_risk_rating][no]" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" id="ajax_click" data-ajax-add-url="<?=$ajax_url?>" class="btn btn-primary">
                        <span class="indicator-label">Submit</span>
                        <span class="indicator-progress">Please wait... 
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<?php $this->endSection(); ?>

<?php $this->section("javascript_section");?>
<script>
const form = document.getElementById('<?=$form_id?>_form');
var validation_object = {
    fields: {
        'audit_site_category_name': { validators: { notEmpty: { message: 'Site Category is required' } } },
        'audit_category': { validators: { notEmpty: { message: 'Category Name is required' } } },
        'audit_question': { validators: { notEmpty: { message: 'Audit Question is required' } } }
    },
    plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap: new FormValidation.plugins.Bootstrap5({
            rowSelector: '.fv-row',
            eleInvalidClass: '',
            eleValidClass: ''
        })
    }
};
var validator = FormValidation.formValidation(form, validation_object);

$("#ajax_click").on("click", function() {
    var button = $(this);
    var url = $(this).attr("data-ajax-url");
    if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                var formData = new FormData(form);
                
                // Construct capa_json from the capa inputs
                var capa = {};
                $("#capa_table input").each(function() {
                    var name = $(this).attr('name');
                    var matches = name.match(/capa\[(.*?)\]\[no\]/);
                    if (matches) {
                        var field = matches[1];
                        if (!capa[field]) capa[field] = { text: field.replace('_', ' ').toUpperCase(), yes: "", no: "", na: "" };
                        capa[field].no = $(this).val();
                    }
                });
                formData.append('capa_json', JSON.stringify(capa));

                ajax_call_img(url, formData, button, function(response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        if (response.status == 1) {
                            toastr.success(response.message);
                            form.reset();
                            $("#<?=$form_id?>").modal("hide");
                            reload_data_table();
                        } else {
                            toastr.warning(response.message);
                        }
                    } catch (error) {
                        console.error('Response Error:', error, response);
                        toastr.error('Error processing response');
                    }
                });
            }
        });
    }
});

// Filter sub-categories based on selected site category
$("#audit_site_category_name").on("change", function() {
    var siteId = $(this).find(':selected').data('id');
    $("#audit_sub_category_name option").each(function() {
        var subSiteId = $(this).data('site-id');
        if (siteId && subSiteId && siteId != subSiteId) {
            $(this).hide();
        } else {
            $(this).show();
        }
    });
    // Reset sub-category if current selection is hidden
    if ($("#audit_sub_category_name option:selected").css('display') === 'none') {
        $("#audit_sub_category_name").val("");
    }
});

function edit_id(obj, id) {
    var url = $(obj).attr("data-ajax-url");
    var formData = { "id": id };

    ajax_call(url, formData, $(obj), function(response) {
        try {
            if (typeof response === 'string') {
                response = JSON.parse(response);
            }
            if (response.status == 1) {
                form.reset();
                var data = response.data;
                
                $("#<?=$form_id?>").find("#audit_site_category_name").val(data.audit_site_category_name).trigger('change');
                $("#<?=$form_id?>").find("#audit_sub_category_name").val(data.audit_sub_category_name);
                $("#<?=$form_id?>").find("#audit_category_id").val(data.audit_category_id);
                $("#<?=$form_id?>").find("#audit_category").val(data.audit_category);
                $("#<?=$form_id?>").find("#audit_question").val(data.audit_question);
                $("#<?=$form_id?>").find("#audit_question_default_value").val(data.audit_question_default_value);
                $("#<?=$form_id?>").find("#audit_template_id").val(data.audit_template_id);

                // Fill CAPA fields
                if (data.capa_json_decoded) {
                    var capa = data.capa_json_decoded;
                    Object.keys(capa).forEach(function(key) {
                        var val = (capa[key] && capa[key].no) ? capa[key].no : "";
                        $("#<?=$form_id?>").find(`input[name='capa[${key}][no]']`).val(val);
                    });
                }

                $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-url", $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-add-url") + "/" + id);
                $("#<?=$form_id?>").modal("show");
            } else {
                toastr.warning(response.message);
            }
        } catch (error) {
            console.error('Edit Error:', error, response);
            toastr.error('Error processing edit data');
        }
    });
}

function delete_row(obj, id) {
    Swal.fire({
        title: 'Are you sure you want to delete this question?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            var url = $(obj).attr("data-ajax-url");
            ajax_call(url, {}, $(obj), function(response) {
                try {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    if (response.status == 1) {
                        toastr.success(response.message);
                        reload_data_table();
                    } else {
                        toastr.warning(response.message);
                    }
                } catch (error) {
                    console.error('Delete Error:', error, response);
                    toastr.error('Error processing delete request');
                }
            });
        }
    });
}
</script>
<?php $this->endSection();?>

