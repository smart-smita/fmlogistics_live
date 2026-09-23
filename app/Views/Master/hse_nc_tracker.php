<?php $this->extend("Layout/base_admin"); ?>
<?php
$this->section("breadcrumb_title_li");
?>
<?php
$form_id = $button_id;
//data-ajax-url during add/edit 
//data-ajax-add-url during add Important
?>

<!--begin::Item-->
<li class="breadcrumb-item text-muted">
    <!-- NEW CHANGES: Breadcrumb resets status filter by linking to index -->
    <a href="<?= base_url('Masters/Hse_nc_tracker') ?>"
        class="text-muted text-hover-primary"><?= isset($title) ? $title : "Client" ?>HSE NC Tracker</a>
</li>
<!--end::Item-->
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<?= $table ?>
<?php $this->endSection(); ?>

<?php $this->section("modals_section"); ?>
<?= view('Master/nc_action_history_modal'); ?>

<!--begin::Modal - Create App-->
<div class="modal fade" id="<?= $button_id ?>" tabindex="-1" aria-hidden="true">
    <form id="<?= $form_id ?>_form" onsubmit="return false;" enctype="multipart/form-data">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0 rounded-4">

                <!-- Modal Header -->
                <div class="modal-header border-0 pb-2">
                    <h2 class="fw-bold mb-0">HSE NC Tracker</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                    transform="rotate(-45 6 17.3137)" fill="black" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)"
                                    fill="black" />
                            </svg>
                        </span>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body pt-2 pb-4 px-4 px-lg-6">

                    <div class="row g-4">

                        <!-- LEFT COLUMN -->
                        <div class="col-lg-6">
                            <div class="card border-0 bg-light-subtle shadow-sm rounded-4 h-100">
                                <div class="card-body">

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold mb-2">Audit No.</label>
                                        <input type="text" class="form-control form-control-solid" id="audit_no"
                                            name="audit_no">
                                    </div>

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold mb-2">Audit Name</label>
                                        <input type="text" class="form-control form-control-solid" id="audit_name"
                                            name="audit_name">
                                    </div>

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold mb-2">Auditor Name</label>
                                        <input type="text" class="form-control form-control-solid" id="auditor_name"
                                            name="auditor_name">
                                    </div>

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold mb-2">Auditee Name</label>
                                        <input type="text" class="form-control form-control-solid" id="auditee_name"
                                            name="auditee_name">
                                    </div>

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold mb-2">Region</label>
                                        <input type="text" class="form-control form-control-solid" id="region"
                                            name="region">
                                    </div>

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold mb-2">Score</label>
                                        <input type="text" class="form-control form-control-solid" id="score"
                                            name="score">
                                    </div>

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold mb-2">Site Category</label>
                                        <input type="text" class="form-control form-control-solid" id="main_category"
                                            name="main_category">
                                    </div>

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold mb-2">Sub Category</label>
                                        <input type="text" class="form-control form-control-solid" id="sub_category"
                                            name="sub_category">
                                    </div>

                                    <div class="fv-row mb-4 d-none">
                                        <label class="fs-6 fw-bold mb-2">Perform Audit By</label>
                                        <input type="text" class="form-control form-control-solid" id="perform_audit_by"
                                            name="perform_audit_by">
                                    </div>

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold mb-2">Question Name</label>
                                        <input type="text" class="form-control form-control-solid" id="audit_category"
                                            name="audit_category">
                                    </div>

                                    <div class="fv-row mb-0">
                                        <label class="fs-6 fw-bold mb-2">Audit Question</label>
                                        <textarea class="form-control form-control-solid" id="audit_question"
                                            name="audit_question" rows="4"></textarea>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-lg-6">
                            <div class="card border-0 bg-light-subtle shadow-sm rounded-4 h-100">
                                <div class="card-body">

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold mb-2">Finding</label>
                                        <input type="text" class="form-control form-control-solid" id="finding"
                                            name="finding" value="NO" disabled>
                                    </div>

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold mb-2">Remark</label>
                                        <input type="text" class="form-control form-control-solid" id="remark"
                                            name="remark">
                                    </div>

                                    <div class="fv-row mb-4" style="display:none">
                                        <label class="fs-6 fw-bold mb-2">NC Status</label>
                                        <input type="text" class="form-control form-control-solid" id="nc_status"
                                            name="nc_status">
                                    </div>

                                    <div class="fv-row mb-4">
                                        <label class="required fs-6 fw-bold mb-2">NC Remark</label>
                                        <textarea class="form-control form-control-solid" id="nc_remark"
                                            name="nc_remark" rows="4" placeholder="Please enter NC remark"></textarea>
                                    </div>

                                    <div class="mb-4 p-3 rounded-3 border bg-white">
                                        <strong>Site Category:</strong> <span id="site_category_text">Not
                                            selected</span>
                                    </div>

                                    <div class="fv-row mb-4">
                                        <label class="fs-6 fw-bold mb-2">NC Before Proof (From Audit)</label>
                                        <div id="audit_before_photo_display"
                                            class="form-control form-control-solid d-flex align-items-center justify-content-center rounded-3"
                                            style="min-height: 180px; background-color: #f8f9fa;">
                                            <span class="text-muted">Before Proof will be loaded from audit data</span>
                                        </div>
                                        <input type="hidden" name="before_photo_url" id="before_photo_url">
                                    </div>

                                    <div class="fv-row mb-0">
                                        <label class="required fs-6 fw-bold mb-2">NC After Proof</label>
                                        <input type="file" class="form-control" id="nc_after_photo"
                                            name="nc_after_photo"
                                            accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xls,.xlsx,.doc,.docx"
                                            onchange="validateFileType(this); previewAfterPhoto(this);" />
                                        <input type="hidden" name="after_photo_url" id="after_photo_url">
                                        <div id="nc_after_photo_preview" class="mt-3 text-center"></div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer border-0 pt-2 justify-content-end">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>

                    <button type="button" id="ajax_click_draft" data-ajax-add-url="<?= $ajax_url ?>"
                        class="btn btn-light-primary me-3">
                        <span class="indicator-label">Save as Draft</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>

                    <button type="submit" id="ajax_click" data-ajax-add-url="<?= $ajax_url ?>" class="btn btn-primary">
                        <span class="indicator-label">Submit for Review</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
<!--end::Modal - Create App-->

<!--begin::Modal - Close NC-->
<div class="modal fade" id="closeNcModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="closeNcForm" onsubmit="return false;" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Close NC</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="close_nc_id" name="id">
                    <input type="hidden" id="close_nc_status" name="status">

                    <div class="fv-row mb-4">
                        <label class="required fs-6 fw-bold mb-2">Closure Date</label>
                        <input type="date" class="form-control form-control-solid" id="close_date" name="close_date" required>
                    </div>

                    <div class="fv-row mb-4">
                        <label class="required fs-6 fw-bold mb-2">Closure Status</label>
                        <select class="form-select form-select-solid" id="close_nc_closure_status" name="closure_status" required>
                            <option value="Closed">Closed</option>
                            <option value="Hold-review with Client">Hold-review with Client</option>
                            <option value="Excluded">Excluded</option>
                        </select>
                    </div>

                    <div class="fv-row mb-4">
                        <label class="fs-6 fw-bold mb-2">Closing Remarks</label>
                        <textarea class="form-control form-control-solid" id="close_nc_remarks" name="closed_remarks"
                            rows="3" placeholder="Enter closing remarks..."></textarea>
                    </div>

                    <div class="fv-row mb-0">
                        <label class="fs-6 fw-bold mb-2">Closing Proof (Optional)</label>
                        <input type="file" class="form-control" id="closed_uploaded_file" name="closed_uploaded_file"
                            accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xls,.xlsx,.doc,.docx"
                            onchange="validateCloseFileType(this);">
                        <div class="form-text text-muted">Accepted: images, PDF, Word, Excel (max 10MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitCloseNcBtn" onclick="submitCloseNc()">Close NC</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end::Modal - Close NC-->

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

<?php $this->endSection(); ?>


<?php $this->section("javascript_section"); ?>

<style>
    /* Custom styled checkboxes for Select2 (copied from OE NC Tracker) */
    .select2-results__option {
        padding-left: 30px !important;
        position: relative;
        color: #3f4254;
    }

    .select2-results__option:before {
        content: "";
        display: inline-block;
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        border: 1px solid #d8d8d8;
        border-radius: 4px;
        background-color: #fff;
        transition: all 0.2s ease;
    }

    /* Checked state: Blue Checkbox with Tick */
    .select2-container--default .select2-results__option[aria-selected=true]:before {
        background-color: #009ef7;
        border-color: #009ef7;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
        background-size: 12px 12px;
        background-position: center;
        background-repeat: no-repeat;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: transparent !important;
        background-image: none !important;
        color: #3f4254 !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: transparent !important;
        background-image: none !important;
        color: #3f4254 !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected=false]:before {
        background-color: #fff !important;
        border-color: #d8d8d8 !important;
        background-image: none !important;
    }

    .select2-container .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #e4e6ef;
        background-color: #fff;
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #e4e6ef;
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        top: 1px !important;
    }

    .select2-container {
        width: 100% !important;
    }
</style>

<script>
    // Global flag for filter logic in hse_nc_filters.js
    window.isHigherAuthorityUser = <?php helper('designation_acl');
    echo isHigherAuthority() ? 'true' : 'false'; ?>;

    function normalizeUrl(url) {
        if (!url) return '';
        url = url.toString().trim();
        if (url.match(/^https?:\/\//i)) return url;

        var base = '<?= rtrim(base_url('/'), '/') ?>';
        if (url.indexOf(base) === 0) {
            url = url.substring(base.length);
        }

        url = url.replace(/^\/+/g, '');
        return base + '/' + url;
    }
</script>
<script src="<?= base_url('assets/js/hse_nc_filters.js?v=' . time()) ?>"></script>
<script>


    function update_div_dep(obj) {
        $(".locations").hide();
        $(".location_id_" + $(obj).find("option:selected").data("location-id")).show()
    }

    //$("#kt_datepicker_1").flatpickr();
    //$.ajax()
    const form = document.getElementById('<?= $form_id ?>_form');
    var validation_object = {
        fields: {
            'location': {
                validators: {
                    notEmpty: {
                        message: 'Location is required'
                    }
                }
            }, 'nc_description': {
                validators: {
                    notEmpty: {
                        message: 'Nc Description is required'
                    }
                }
            }, 'devision_dept': {
                validators: {
                    notEmpty: {
                        message: 'Div/Dep is required'
                    }
                }
            }
            , 'nc_photo': {
                validators: {
                    notEmpty: {
                        message: 'Nc Proof is required'
                    }
                }
            }
            , 'action_required_dep': {
                validators: {
                    notEmpty: {
                        message: 'Action Required Dep is required'
                    }
                }
            }, 'responsibilty': {
                validators: {
                    notEmpty: {
                        message: 'Responsibilty is required'
                    }
                }
            }, 'target_date': {
                validators: {
                    notEmpty: {
                        message: 'Target Date is required'
                    }
                }
            }, 'sixs_category': {
                validators: {
                    notEmpty: {
                        message: 'Sixs Category is required'
                    }
                }
            }, 'details_of_ca_pa': {
                validators: {
                    notEmpty: {
                        message: 'Details Of Ca Pa is required'
                    }
                }
            }, 'after_photo': {
                validators: {
                    notEmpty: {
                        message: 'After Proof is required'
                    }
                }
            }, 'department_status': {
                validators: {
                    notEmpty: {
                        message: 'Department Status is required'
                    }
                }
            }, 'final_status_soi': {
                validators: {
                    notEmpty: {
                        message: 'Final Status Soi is required'
                    }
                }
            },
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

    $("#ajax_click").on("click", function () {
        var button = $(this);
        var url = $(this).attr("data-ajax-url");
        if (validator) {
            validator.validate().then(function (status) {
                if (status == 'Valid') {
                    // additional required checks
                    var ncRemark = $('#nc_remark').val() || '';
                    var ncAfterPhoto = $('#nc_after_photo').val() || '';
                    var afterPhotoUrl = $('#after_photo_url').val() || '';

                    if (ncRemark.trim() === '') {
                        alert('NC Remark is required to submit for review.');
                        return;
                    }

                    // if (ncAfterPhoto.trim() === '' && afterPhotoUrl.trim() === '') {
                    //     alert('NC After Proof is required to submit for review.');
                    //     return;
                    // }

                    var formData = new FormData($("#<?= $form_id ?>_form")[0]);
                    formData.append('action', 'submit_for_review'); // Add action parameter

                    //ajax_call(url,data_to_send,button,success_function)
                    ajax_call_img(url, formData, button, function (responce) {
                        try { responce = JSON.parse(responce); } catch (e) { }

                        if (responce.status == 1) {
                            form.reset();
                            $("#nc_after_photo_preview").html('');
                            $("#audit_before_photo_display").html('<span class="text-muted">Before Proof will be loaded from audit data</span>');
                            $("#<?= $form_id ?>").modal("hide");

                            if (typeof reload_data_table === 'function') {
                                reload_data_table();
                            } else {
                                location.reload();
                            }
                        } else {
                            alert(responce.message || 'Something went wrong while saving.');
                        }
                    });
                } else {
                    // not validate 
                }
            });
        }

    });

    // removed draft
    // Save as Draft handler
    $("#ajax_click_draft").on("click", function () {
        var button = $(this);
        var url = $(this).attr("data-ajax-url") || $(this).attr("data-ajax-add-url");
        var formData = new FormData($("#<?= $form_id ?>_form")[0]);
        formData.append('action', 'save_as_draft');
        ajax_call_img(url, formData, button, function (responce) {
            try { responce = JSON.parse(responce); } catch (e) { }
            $("#<?= $form_id ?>").modal("hide");
            if (typeof reload_data_table === 'function') { reload_data_table(); } else { location.reload(); }
        });
    });

    // NEW CHANGES: Fix textarea Enter key issue
    $(document).ready(function () {
        // Prevent form submission when Enter is pressed in textarea fields
        $('textarea').on('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                // Allow Shift+Enter for new lines, but prevent Enter alone from submitting
                e.preventDefault();
                // Insert a new line at cursor position
                var cursorPos = this.selectionStart;
                var textBefore = this.value.substring(0, cursorPos);
                var textAfter = this.value.substring(cursorPos);
                this.value = textBefore + '\n' + textAfter;
                // Set cursor position after the new line
                this.setSelectionRange(cursorPos + 1, cursorPos + 1);
            }
        });

        // Also prevent form submission on Enter key in textarea
        $('form').on('submit', function (e) {
            var activeElement = document.activeElement;
            if (activeElement && activeElement.tagName === 'TEXTAREA') {
                e.preventDefault();
                return false;
            }
        });
    });

    function previewAfterPhoto(input) {
        var preview = $('#nc_after_photo_preview');
        preview.html('');

        if (!input.files || !input.files[0]) {
            return;
        }

        var file = input.files[0];
        if (file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.html('<img src="' + e.target.result + '" style="max-width: 100%; max-height: 200px;"/>');
            };
            reader.readAsDataURL(file);
        } else {
            var fileUrl = URL.createObjectURL(file);
            preview.html('<a href="' + fileUrl + '" target="_blank" rel="noopener">Selected file: ' + file.name + '</a>');
        }
    }

    function openWorkingModal(id, siteCategory) {
        $('#site_category_text').text(siteCategory || 'N/A');

        var fakeBtn = $('<button>', {
            type: 'button',
            'data-ajax-url': '<?= base_url("Masters/Hse_nc_tracker/get_form_data") ?>/' + id
        });
        edit_id(fakeBtn[0], id);
    }

    function openCloseModal(id, auditDate, status) {
        $('#close_nc_id').val(id);
        $('#close_nc_status').val(status);

        let dateInput = document.getElementById('close_date');
        dateInput.value = '';
        if (auditDate) {
            dateInput.min = auditDate;
        }
        let today = new Date().toISOString().split('T')[0];
        dateInput.max = today;

        // Reset new closure fields each time the modal opens
        $('#close_nc_closure_status').val('Closed');
        $('#close_nc_remarks').val('');
        $('#closed_uploaded_file').val('');

        $('#closeNcModal').modal('show');
    }

    function submitCloseNc() {
        let dateInput = document.getElementById('close_date');
        if (!dateInput.value) {
            alert('Closure Date is required.');
            return;
        }

        let btn = document.getElementById('submitCloseNcBtn');
        btn.disabled = true;
        btn.textContent = 'Processing...';

        // Use FormData to support file upload
        let formData = new FormData(document.getElementById('closeNcForm'));
        // The hidden fields (id, status) are already in the form;
        // close_date, closure_status, closed_remarks, closed_uploaded_file are form inputs.

        $.ajax({
            url: '<?= base_url('Masters/Hse_nc_tracker/update_nc_status') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                btn.disabled = false;
                btn.textContent = 'Close NC';
                try { res = (typeof res === 'string') ? JSON.parse(res) : res; } catch(e) {}
                if (res && res.status == 1) {
                    $('#closeNcModal').modal('hide');
                    if (typeof reload_data_table === 'function') {
                        reload_data_table();
                    } else {
                        location.reload();
                    }
                } else {
                    alert(res.message || 'Error occurred while closing NC.');
                }
            },
            error: function () {
                btn.disabled = false;
                btn.textContent = 'Close NC';
                alert('Server error occurred. Please try again.');
            }
        });
    }

    function updateNcStatus(id, status) {
        $.post('<?= base_url('Masters/Hse_nc_tracker/update_nc_status') ?>', {
            id: id,
            status: status
        }, function () {
            location.reload();
        });
    }
    function edit_id(obj, id) {
        var url = $(obj).attr("data-ajax-url");
        var formData = { "id": id };
        ajax_call(url, formData, $(obj), function (responce) {
            try {
                responce = JSON.parse(responce);
                if (responce.status == 1) {

                    $("#<?= $form_id ?>").find("#audit_no").val(responce.data.audit_no).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#audit_name").val(responce.data.audit_name).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#auditee_name").val(responce.data.auditee_name).prop("disabled", true);
                    // dates removed
                    $("#<?= $form_id ?>").find("#region").val(responce.data.region).prop("disabled", true);
                    // location removed
                    // persist after-photo if no new upload
                    $("#<?= $form_id ?>").find("#after_photo_url").val(responce.data.nc_after_photo || "");
                    $("#<?= $form_id ?>").find("#score").val(responce.data.score).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#main_category").val(responce.data.main_category).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#sub_category").val(responce.data.sub_category).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#perform_audit_by").val(responce.data.perform_audit_by).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#auditor_name").val(responce.data.auditor_name).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#audit_category").val(responce.data.audit_category).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#audit_question").val(responce.data.audit_question).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#inplant").val("YES").prop("disabled", true);
                    $("#<?= $form_id ?>").find("#remark").val(responce.data.remark).prop("disabled", true);

                    var siteCat = responce.data.site_category || responce.data.main_category || 'N/A';
                    $("#site_category_text").text(siteCat);

                    var afterPhotoUrl = responce.data.nc_after_photo || '';
                    if (afterPhotoUrl !== '') {
                        var fullAfter = normalizeUrl(afterPhotoUrl);
                        var ext = (afterPhotoUrl.split('.').pop() || '').toLowerCase();
                        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                            $("#nc_after_photo_preview").html('<a href="' + fullAfter + '" target="_blank"><img src="' + fullAfter + '" style="max-width: 100%; max-height: 200px;" /></a>');
                        } else {
                            $("#nc_after_photo_preview").html('<a href="' + fullAfter + '" target="_blank">Download file: ' + encodeURI(afterPhotoUrl.split('/').pop()) + '</a>');
                        }
                        $("#after_photo_url").val(afterPhotoUrl);
                    } else {
                        $("#nc_after_photo_preview").html('');
                        $("#after_photo_url").val('');
                    }

                    // changes on 7/10/25 by darsh: Display before photo using resolved before_photo_url
                    var beforeUrl = responce.data.before_photo_url || responce.data.attachment || '';
                    if (beforeUrl && beforeUrl !== '') {
                        var full = normalizeUrl(beforeUrl);
                        var ext = (beforeUrl.split('.').pop() || '').toLowerCase();
                        var html = '';
                        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                            html = `
                                                <a href="${full}" target="_blank">
                                                    <img src="${full}" 
                                                        style="max-width: 100%; max-height: 200px;" />
                                                </a>`;
                        } else {
                            html = `<a href="${full}" target="_blank">Download file: ${encodeURI(beforeUrl.split('/').pop())}</a>`;
                        }
                        $("#<?= $form_id ?>").find("#audit_before_photo_display").html(html);
                    } else {
                        $("#<?= $form_id ?>").find("#audit_before_photo_display").html('<span class="text-muted">No before Proof available</span>');
                    }
                    $("#<?= $form_id ?>").find("#before_photo_url").val(beforeUrl);
                    $("#<?= $form_id ?>").find("#nc_closed_date").val(responce.data.nc_closed_date).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#nc_reviewed_date").val(responce.data.nc_reviewed_date).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#nc_closed_by").val(responce.data.nc_closed_by).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#nc_reviewed_by").val(responce.data.nc_reviewed_by).prop("disabled", true);
                    $("#<?= $form_id ?>").find("#nc_status").val(responce.data.nc_status).prop("disabled", true);


                    $("#<?= $form_id ?>").find("#ajax_click").attr("data-ajax-url", $("#<?= $form_id ?>").find("#ajax_click").attr("data-ajax-add-url") + "/" + id);
                    $("#<?= $form_id ?>").find("#ajax_click_draft").attr("data-ajax-url", $("#<?= $form_id ?>").find("#ajax_click_draft").attr("data-ajax-add-url") + "/" + id);
                    $("#<?= $form_id ?>").modal("show");
                } else {
                    // silent ignore
                }
            } catch (error) {
                // silent ignore
            }
        });
    }
    function delete_row(obj, id) {
        Swal.fire({
            title: 'Do you want delete?',
            //showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Save',
            //denyButtonText: `Don't delete`,
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                //Swal.fire('Saved!', '', 'success')
                url_call_ajax($(obj).attr("data-ajax-url"), $(obj));
            }
            //   else if (result.isDenied) {
            //     Swal.fire('Changes are not saved', '', 'info')
            //   }
        });

    }
    // File type validation function
    function validateFileType(input) {
        const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'doc', 'docx'];
        const maxSize = 10 * 1024 * 1024; // 10MB

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const extension = file.name.split('.').pop().toLowerCase();

            // Check file type
            if (!allowedTypes.includes(extension)) {
                alert('Invalid file type. Only images, PDFs, Word documents, and Excel files are allowed.');
                input.value = '';
                return false;
            }

            // Check file size
            if (file.size > maxSize) {
                alert('File size too large. Maximum allowed size is 10MB.');
                input.value = '';
                return false;
            }
        }
        return true;
    }

    // File type validation for Close NC proof upload
    function validateCloseFileType(input) {
        const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'doc', 'docx'];
        const maxSize = 10 * 1024 * 1024; // 10MB

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const extension = file.name.split('.').pop().toLowerCase();

            if (!allowedTypes.includes(extension)) {
                alert('Invalid file type. Only images, PDFs, Word documents, and Excel files are allowed.');
                input.value = '';
                return false;
            }
            if (file.size > maxSize) {
                alert('File size too large. Maximum allowed size is 10MB.');
                input.value = '';
                return false;
            }
        }
        return true;
    }

</script>
<!-- changes on 30/09/25 by darsh: hide Add button on Client NC Tracker page only -->
<script>
    // changes on 30/09/25 by darsh: hide shared Add button rendered by table-view
    $(function () {
        $('button[data-bs-target="#user_modal"]').closest('.card-toolbar').hide();
    });

    // Bulk Close Logic
    function getSelectedRows() {
        var selected = [];
        $('.row-checkbox:checked').each(function() {
            selected.push($(this).val());
        });
        return selected;
    }

    function updateBulkBtn() {
        var selected = getSelectedRows();
        $('#bulkCloseBtn').prop('disabled', selected.length === 0);
    }

    $(document).on('change', '#selectAllCheckbox', function() {
        $('.row-checkbox:not(:disabled)').prop('checked', this.checked);
        updateBulkBtn();
    });

    $(document).on('change', '.row-checkbox', function() {
        if (!this.checked) {
            $('#selectAllCheckbox').prop('checked', false);
        } else {
            if ($('.row-checkbox:not(:disabled):checked').length > 0 && $('.row-checkbox:not(:disabled):checked').length === $('.row-checkbox:not(:disabled)').length) {
                $('#selectAllCheckbox').prop('checked', true);
            }
        }
        updateBulkBtn();
    });

    // Reset checkboxes on table draw
    $(document).on('draw.dt', '#gemba_nc_tracker_table', function () {
        $('#selectAllCheckbox').prop('checked', false);
        updateBulkBtn();
    });
    
    // Fallback if table uses a different ID for HSE
    $(document).on('draw.dt', '.dataTable', function () {
        $('#selectAllCheckbox').prop('checked', false);
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
            alert('Please select a closure date');
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

        $.post('<?= base_url('Masters/Hse_nc_tracker/bulk_close_nc') ?>', formData, function (res) {
            btn.prop('disabled', false).text('Confirm Closure');
            if (res && res.status == 'success') {
                $('#bulkCloseModal').modal('hide');
                if (typeof reload_data_table === 'function') {
                    reload_data_table();
                } else {
                    location.reload();
                }
                $('#selectAllCheckbox').prop('checked', false);
                updateBulkBtn();
            } else {
                alert(res.message || 'Error occurred');
            }
        }).fail(function() {
            btn.prop('disabled', false).text('Confirm Closure');
            alert('Server error occurred');
        });
    }
</script>
<?php $this->endSection(); ?>