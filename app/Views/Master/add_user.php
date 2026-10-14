<?php
$form_id = $button_id;
//data-ajax-url during add/edit 
//data-ajax-add-url during add Important
?>
<?php $this->extend("Layout/base_admin"); ?>
<?php
$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
<li class="breadcrumb-item text-muted">
    <a href="<?= current_url() ?>" class="text-muted text-hover-primary"><?= isset($title) ? $title : "Client" ?></a>
</li>
<!--end::Item-->
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<?= $table ?>

<style>
    /* Scoped password requirements message box */
    #message {
        display: none;
        background: #f8f9fa;
        color: #212529;
        position: relative;
        padding: 15px;
        margin-top: 10px;
    }

    #message p {
        padding-left: 24px;
        font-size: 13px;
        position: relative;
    }

    /* Add a green text color and a checkmark when the requirements are right */
    #message .valid {
        color: #28a745;
    }

    #message .valid:before {
        position: absolute;
        left: 0;
        content: "✔";
    }

    /* Add a red text color and an "x" when the requirements are wrong */
    #message .invalid {
        color: #dc3545;
    }

    #message .invalid:before {
        position: absolute;
        left: 0;
        content: "✖";
    }
</style>
<?php $this->endSection(); ?>

<?php $this->section("modals_section"); ?>

<!--begin::Modal - Create App-->
<div class="modal fade" id="<?= $button_id ?>" tabindex="-1" aria-hidden="true">
    <form id="<?= $form_id ?>_form" onsubmit="return false;">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-900px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <!--begin::Modal title-->
                    <h2><?php echo isset($title) ? $title : "FORM" ?></h2>
                    <!--end::Modal title-->
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                    transform="rotate(-45 6 17.3137)" fill="black" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)"
                                    fill="black" />
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body py-lg-10 px-lg-10">


                    <div class="row g-4">
                        <div class="col-md-6">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>
                                <div class="form-group mb-4">
                                    <label class="form-label fw-bold" for="user_emp_country">Country</label>
                                    <select name="user_emp_country" class="form-control" id="user_emp_country">
                                        <option value=''>Select Country</option>
                                        <?php foreach ($country_list as $key => $value) { ?>
                                            <option value='<?= $value['country_short_name'] ?>'><?= $value['country_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } else { ?>
                                <input type="hidden" name='user_emp_country'
                                    value='<?= isset($_SESSION['country']) ? $_SESSION['country'] : '' ?>'>
                            <?php } ?>

                            <div class="form-group mb-4">
                                <label class="form-label fw-bold" for="user_name">User Name *</label>
                                <input type="text" class="form-control" id="user_name" placeholder="User Name" name="user_name"
                                    value="<?php echo isset($user_name) ? $user_name : "" ?>">
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label fw-bold" for="user_contact">Mobile Number *</label>
                                <input type="text" class="form-control" id="user_contact" placeholder="Mobile Number"
                                    name="user_contact" minlength="10" maxlength="10"
                                    value="<?php echo isset($user_contact) ? $user_contact : "" ?>">
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label fw-bold" for="user_designation">Designation *</label>
                                <select class="form-control select2" name="user_designation" id="user_designation">
                                    <option selected="selected" disabled="disabled" value="">Select Designation</option>
                                    <?php if (isset($designation))
                                        foreach ($designation as $row) { ?>
                                            <option value="<?= $row['name'] ?>" <?= (isset($user_designation) && $user_designation == $row['name']) ? 'selected="selected"' : "" ?>><?= $row['name'] ?></option>
                                        <?php } ?>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label fw-bold" for="audit_type">Audit Type *</label>
                                <select class="form-control" name="audit_type" id="audit_type" required>
                                    <option value="Both" <?= (!isset($audit_type) || $audit_type == 'Both' || $audit_type == '') ? 'selected="selected"' : '' ?>>Both</option>
                                    <option value="OE" <?= (isset($audit_type) && $audit_type == 'OE') ? 'selected="selected"' : '' ?>>OE</option>
                                    <option value="HSE" <?= (isset($audit_type) && $audit_type == 'HSE') ? 'selected="selected"' : '' ?>>HSE</option>
                                </select>
                            </div>
                            
                            <?php if (($_SESSION['admin_flag'] ?? 0) == 1 || strtolower($_SESSION['role'] ?? '') == 'super admin' || strtolower($_SESSION['role'] ?? '') == 'admin') { ?>
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold" for="admin_flag">Admin Access</label>
                                <select class="form-control" name="admin_flag" id="admin_flag">
                                    <option value="0" <?= (isset($admin_flag) && $admin_flag == 0) ? 'selected="selected"' : "" ?>>No</option>
                                    <option value="1" <?= (isset($admin_flag) && $admin_flag == 1) ? 'selected="selected"' : "" ?>>Yes (Super Admin Privileges)</option>
                                </select>
                            </div>
                            <?php } ?>
                        </div>
                        <!-- end col-md-6 -->

                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold" for="user_emp_code">Employee Id</label>
                                <input type="text" class="form-control" id="user_emp_code" placeholder="Employee Id" name="user_emp_code"
                                    value="<?php echo isset($user_emp_code) ? $user_emp_code : "" ?>">
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label fw-bold" for="user_email">Email ID *</label>
                                <input type="email" class="form-control" id="user_email" placeholder="Email address" name="user_email"
                                    value="<?php echo isset($user_email) ? $user_email : "" ?>">
                            </div>

                            <?php if (!isset($user_password)) { ?>
                                <div class="form-group mb-4">
                                    <label class="form-label fw-bold" for="user_password">Password *</label>
                                    <input type="text" class="form-control" id="user_password" name="user_password" placeholder="Password"
                                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                        title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters"
                                        required value="<?php echo isset($user_password) ? $user_password : "" ?>">
                                    <div id="message" class="rounded p-3 mt-2 border">
                                        <h6 class="fs-7 fw-bold text-gray-800">Password must contain:</h6>
                                        <p id="letter" class="invalid mb-1 fs-8">A <b>lowercase</b> letter</p>
                                        <p id="capital" class="invalid mb-1 fs-8">A <b>capital (uppercase)</b> letter</p>
                                        <p id="number" class="invalid mb-1 fs-8">A <b>number</b></p>
                                        <p id="spacial" class="invalid mb-1 fs-8">A <b>special character</b></p>
                                        <p id="length" class="invalid mb-0 fs-8">Minimum <b>8 characters</b></p>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <!-- end col-md-6 -->
                    </div>

                </div>
                <!--end::Modal body-->

                <!--begin::Modal footer-->
                <div class="modal-footer flex-center">
                    <!--begin::Button-->
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                    <!--end::Button-->

                    <!--begin::Button-->
                    <button type="submit" id="ajax_click" data-ajax-add-url="<?= $ajax_url ?>" class="btn btn-primary">
                        <span class="indicator-label">Submit</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                    <!--end::Button-->

                </div>
                <!--end::Modal footer-->

            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </form>
</div>
<!--end::Modal - Create App-->


<?php $this->endSection(); ?>

<?php $this->section("custom_js"); ?>
<script>
    $(document).ready(function () {
        // Reset form when modal closes
        $('#<?= $button_id ?>').on('hidden.bs.modal', function () {
            $(this).find('form')[0].reset();
            $('#audit_type').val('Both');
        });

        // Utility function to show alerts (exposed globally)
        window.showAlert = function (message, type) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'error' ? 'Error' : type === 'warning' ? 'Warning' : 'Info',
                    text: message,
                    icon: type,
                    timer: 3000
                });
            } else {
                alert(message);
            }
        };
    });
</script>
<?php $this->endSection(); ?>


<?php $this->section("javascript_section"); ?>
<script>
    var myInput = document.getElementById("user_password");
    var letter = document.getElementById("letter");
    var capital = document.getElementById("capital");
    var number = document.getElementById("number");
    var length = document.getElementById("length");
    var spacial = document.getElementById("spacial");
    var user_contact = document.getElementById("user_contact");

    // Attach password validators only if the field exists (it may be hidden on edit)
    if (myInput) {
        // When the user clicks on the password field, show the message box
        myInput.onfocus = function () {
            document.getElementById("message").style.display = "block";
        }

        // When the user clicks outside of the password field, hide the message box
        myInput.onblur = function () {
            document.getElementById("message").style.display = "none";
        }

        // When the user starts to type something inside the password field
        myInput.onkeyup = function () {
            // Validate lowercase letters
            var lowerCaseLetters = /[a-z]/g;
            if (myInput.value.match(lowerCaseLetters)) {
                letter.classList.remove("invalid");
                letter.classList.add("valid");
            } else {
                letter.classList.remove("valid");
                letter.classList.add("invalid");
            }

            // Validate capital letters
            var upperCaseLetters = /[A-Z]/g;
            if (myInput.value.match(upperCaseLetters)) {
                capital.classList.remove("invalid");
                capital.classList.add("valid");
            } else {
                capital.classList.remove("valid");
                capital.classList.add("invalid");
            }

            // Validate numbers
            var numbers = /[0-9]/g;
            if (myInput.value.match(numbers)) {
                number.classList.remove("invalid");
                number.classList.add("valid");
            } else {
                number.classList.remove("valid");
                number.classList.add("invalid");
            }

            // Validate length
            if (myInput.value.length >= 8) {
                length.classList.remove("invalid");
                length.classList.add("valid");
            } else {
                length.classList.remove("valid");
                length.classList.add("invalid");
            }
            var specialChars = "<>@!#$%^&*()_+[]{}?:;|'\"\\,./~`-=";
            var sp_val = false;
            for (i = 0; i < specialChars.length; i++) {
                if (myInput.value.indexOf(specialChars[i]) > -1) {
                    sp_val = true;
                }
            }

            if (sp_val) {
                spacial.classList.remove("invalid");
                spacial.classList.add("valid");
            } else {
                spacial.classList.remove("valid");
                spacial.classList.add("invalid");
            }
        }

    }


    $(() => {
        var old = '';
        <?php if (isset($user_name)) { ?>        $("#user_designation").trigger('change');

        setTimeout(function () { $("#user_designation").trigger('change'); }, 3000);
        <?php } ?>

        // Revalidate select2 fields on change so FormValidation clears the error
        $('#user_designation, #user_emp_country, #audit_type').on('change', function () {
            if (typeof validator !== 'undefined' && validator) {
                var fieldName = $(this).attr('name');
                if (validation_object.fields[fieldName]) {
                    validator.revalidateField(fieldName).catch(function() {});
                }
            }
        });
    });
    function validatation() {
        myInput.onkeyup();
        var valid = 1
        $(document).find('p').each(function () {
            if ($(this).hasClass('invalid')) {
                valid = 0;
                myInput.focus();
                return false
            }
        });
        if (valid)
            return true
    }
    var form = document.getElementById('<?= $form_id ?>_form');
    var validation_object = {
        fields: {
            'user_name': {
                validators: {
                    notEmpty: {
                        message: 'Name is required'
                    }
                }
            }, 'user_contact': {
                validators: {
                    notEmpty: {
                        message: 'User contact is required'
                    }
                }
            }, 'user_emp_zone': {
                validators: {
                    notEmpty: {
                        message: 'Zone is required'
                    }
                }
            }, 'user_email': {
                validators: {
                    notEmpty: {
                        message: 'Email is required'
                    }
                }
            }, 'user_password': {
                validators: {
                    notEmpty: {
                        message: 'Password is required'
                    }
                }
            }, 'user_designation': {
                validators: {
                    notEmpty: {
                        message: 'Designation is required'
                    }
                }
            }
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap: new FormValidation.plugins.Bootstrap5({
                rowSelector: '.form-group',
                eleInvalidClass: '',
                eleValidClass: ''
            })
        }
    };

    window.edit_id = function (obj, id) {
        var url = $(obj).attr("data-ajax-url");
        var formData = { "id": id };
        ajax_call(url, formData, $(obj), function (responce) {
            try {
                responce = JSON.parse(responce);
                if (responce.status == 1) {
                    toastr.success(responce.message);
                    //form.reset();
                    // to update value 

                    $("#<?= $form_id ?>").find("#user_emp_code").val(responce.data.user_emp_code);
                    $("#<?= $form_id ?>").find("#user_name").val(responce.data.user_name);
                    $("#<?= $form_id ?>").find("#user_contact").val(responce.data.user_contact);
                    $("#<?= $form_id ?>").find("#user_emp_zone").val(responce.data.user_emp_zone);
                    $("#<?= $form_id ?>").find("#user_email").val(responce.data.user_email);
                    $("#<?= $form_id ?>").find("#user_password").val(responce.data.user_password);
                    $("#<?= $form_id ?>").find("#user_designation").val(responce.data.user_designation);
                    $("#<?= $form_id ?>").find("#audit_type").val(responce.data.audit_type || 'Both');
                    $("#<?= $form_id ?>").find("#user_emp_country").val(responce.data.user_emp_country);
                    // Iterate over the array and check the corresponding checkboxes

                    $("#<?= $form_id ?>").find("#ajax_click").attr("data-ajax-url", $("#<?= $form_id ?>").find("#ajax_click").attr("data-ajax-add-url") + "/" + id);
                    $("#<?= $form_id ?>").modal("show");
                } else {
                    toastr.warning(responce.message);
                }
            } catch (error) {
                toastr.error(error);
            }
        });
    }
    window.delete_row = function (obj, id) {
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

    function update_location_id(obj){
        // Ensure there is a location_id input if this is used
        if ($("#location_id").length > 0) {
            $("#location_id").val(""+$(obj).find("option:selected").data("location-id"));
        }
    }

    // alert("DEMO");
    var validator = FormValidation.formValidation(form, validation_object);

    $("#ajax_click").on("click", function () {
        var button = $(this);
        var url = $(this).attr("data-ajax-url");

        if (validator) {
            validator.validate().then(function (status) {
                if (status == 'Valid') {


                    var data_to_send = $("#<?= $form_id ?>_form").serializeArray();
                    var formData = {};
                    $.each(data_to_send, function (i, field) {
                        // Check if the field name is 'options[]'
                        if (field.name === 'options[]') {
                            // Initialize the array if it doesn't exist
                            if (!formData['options[]']) {
                                formData['options[]'] = [];
                            }
                            // Push the value into the 'options[]' array
                            formData['options[]'].push(field.value);
                        } else {
                            // Assign the value directly to the corresponding key
                            if (field.value.trim() !== "") {
                                formData[field.name] = field.value;
                            }
                        }
                    });

                    //ajax_call(url,data_to_send,button,success_function)
                    ajax_call(url, formData, button, function (responce) {
                        try {
                            responce = JSON.parse(responce);
                            if (responce.status == 1) {
                                toastr.success(responce.message);
                                form.reset();
                                $("#<?= $form_id ?>").modal("hide");
                                reload_data_table();
                            } else {
                                toastr.warning(responce.message);
                            }
                        } catch (error) {
                            toastr.error(error);
                        }
                    });
                } else {
                    // not validate 
                }
            });
        }
    });

    // changes on 1/11/25 by darsh: Initialize Bootstrap tooltips for action buttons
    $(document).ready(function () {
        // Function to initialize tooltips
        function initializeTooltips() {
            // Destroy existing tooltips first to avoid duplicates
            $('[data-bs-toggle="tooltip"]').each(function () {
                var tooltipInstance = bootstrap.Tooltip.getInstance(this);
                if (tooltipInstance) {
                    tooltipInstance.dispose();
                }
            });

            // Initialize all tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Initialize tooltips on page load
        initializeTooltips();

        // Re-initialize tooltips after DataTable reloads
        if (typeof $.fn.DataTable !== 'undefined') {
            // Hook into DataTable draw event to re-initialize tooltips after table reload
            $(document).on('draw.dt', function () {
                setTimeout(function () {
                    initializeTooltips();
                }, 100);
            });
        }
    });
</script>
<?php
$this->endSection();
?>
