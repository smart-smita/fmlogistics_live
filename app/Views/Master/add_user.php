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
    /* Prevent select2 dropdown from being hidden behind modal */
    .select2-container--open {
        z-index: 9999999 !important;
    }

    /* Style all input fields */
    input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        margin-top: 6px;
        margin-bottom: 16px;
    }

    /* Style the submit button */
    input[type=submit] {
        background-color: #4CAF50;
        color: white;
    }

    /* Style the container for inputs */
    .container {
        background-color: #f1f1f1;
        padding: 20px;
    }

    /* The message box is shown when the user clicks on the password field */
    #message {
        display: none;
        background: #f1f1f1;
        color: #000;
        position: relative;
        padding: 20px;
        margin-top: 10px;
    }

    #message p {
        padding: 10px 35px;
        font-size: 18px;
    }

    /* Add a green text color and a checkmark when the requirements are right */
    .valid {
        color: green;
    }

    .valid:before {
        position: relative;
        left: -35px;
        content: "✔";
    }

    /* Add a red text color and an "x" when the requirements are wrong */
    .invalid {
        color: red;
    }

    .invalid:before {
        position: relative;
        left: -35px;
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


                    <div class="row">

                        <div class="col-md-6">
                            <br>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') { ?>

                                <div class="form-group">
                                    <label for="empcountry">Country</label>
                                    <select name="user_emp_country" class="form-control" id="user_emp_country">
                                        <option value=''>Select Country</option>
                                        <?php foreach ($country_list as $key => $value) { ?>
                                            <option value='<?= $value['country_short_name'] ?>'><?= $value['country_name'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } else { ?>
                                <input type="hidden" name='user_emp_country'
                                    value='<?= isset($_SESSION['country']) ? $_SESSION['country'] : '' ?>'>
                            <?php } ?>

                            <div class="form-group">
                                <label>Region *</label>
                                <select class="form-control" id="user_region" name="user_region">
                                    <option value="" data-location-id="0">Select Region</option>
                                    <?php foreach ($region_list as $row) { ?>
                                        <option value="<?= $row['region_name'] ?>" data-location-id="<?= $row['region_id'] ?>"
                                            <?= (isset($user_region) && $user_region == $row['region_name']) ? 'selected="selected"' : '' ?>><?= $row['region_name'] ?></option>

                                    <?php } ?>
                                </select>
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>


                            <div class="form-group">
                                <label for="user_name">User Name *</label> <input type="text" class="form-control"
                                    id="user_name" placeholder="User Name" name="user_name"
                                    value="<?php echo isset($user_name) ? $user_name : "" ?>">
                            </div>

                            <div class="form-group">
                                <label for="user_contact">Mobile Number *</label> <input type="text"
                                    class="form-control" id="user_contact" placeholder="Mobile Number"
                                    name="user_contact" minlength="10" maxlength="10"
                                    value="<?php echo isset($user_contact) ? $user_contact : "" ?>">
                            </div>

                            <div class="form-group">
                                <label for="empdesignation">Designation *</label>
                                <select class="form-control select2" name="user_designation" id="user_designation">
                                    <option selected="selected" disabled="disabled" value="">Select Designation</option>
                                    <?php if (isset($designation))
                                        foreach ($designation as $row) { ?>
                                            <option value="<?= $row['name'] ?>" <?= (isset($user_designation) && $user_designation == $row['name']) ? 'selected="selected"' : "" ?>><?= $row['name'] ?>
                                            </option>
                                        <?php } ?>
                                </select>
                            </div>
                            
                            <?php if (($_SESSION['admin_flag'] ?? 0) == 1 || strtolower($_SESSION['role'] ?? '') == 'super admin' || strtolower($_SESSION['role'] ?? '') == 'admin') { ?>
                            <div class="form-group mt-3">
                                <label for="admin_flag">Admin Access</label>
                                <select class="form-control" name="admin_flag" id="admin_flag">
                                    <option value="0" <?= (isset($admin_flag) && $admin_flag == 0) ? 'selected="selected"' : "" ?>>No</option>
                                    <option value="1" <?= (isset($admin_flag) && $admin_flag == 1) ? 'selected="selected"' : "" ?>>Yes (Super Admin Privileges)</option>
                                </select>
                            </div>
                            <?php } ?>

                        </div>
                        <!-- end col -->

                        <div class="col-md-6">
                            <br>
                            <!--	<div class="form-group">
                            <label for="empzone">Region</label> 
                            <select name ="user_emp_zone" class="form-control" id="user_emp_zone">
                                <option value='' selected>Select Region</option>
                                <option value='east' <?php echo (isset($user_emp_zone) && $user_emp_zone == 'east') ? 'selected' : '' ?>>East</option>
                                <option value='west' <?php echo (isset($user_emp_zone) && $user_emp_zone == 'west') ? 'selected' : '' ?>>West</option>
                                <option value='north' <?php echo (isset($user_emp_zone) && $user_emp_zone == 'north') ? 'selected' : '' ?>>North</option>
                                <option value='south' <?php echo (isset($user_emp_zone) && $user_emp_zone == 'south') ? 'selected' : '' ?>>South</option>

                               </select>
                        </div>-->


                            <div class="form-group">
                                <label>Cluster </label>
                                <select class="form-control" id="user_cluster" name="user_cluster">
                                    <option value="" data-location-id="0">Select Cluster</option>
                                    <?php foreach ($cluster_list as $row) { ?>
                                        <option value="<?= $row['cluster_name'] ?>" data-location-id="<?= $row['cluster_id'] ?>"
                                            <?= (isset($user_cluster) && $user_cluster == $row['cluster_name']) ? 'selected="selected"' : "" ?>>
                                            <?= $row['cluster_name'] ?></option>
                                    <?php } ?>
                                </select>
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>

                            <div class="form-group">
                                <label for="user_emp_code">Employee Id</label> <input type="text" class="form-control"
                                    id="user_emp_code" placeholder="Employee Id" name="user_emp_code"
                                    value="<?php echo isset($user_emp_code) ? $user_emp_code : "" ?>">
                            </div>

                            <div class="form-group">
                                <label for="user_email">Email ID *</label> <input type="email" class="form-control"
                                    id="user_email" placeholder="Email address" name="user_email"
                                    value="<?php echo isset($user_email) ? $user_email : "" ?>">
                            </div>
                            <?php if (!isset($user_password)) { ?>
                                <div class="form-group">
                                    <label for="user_password">Password *</label> <input type="text" class="form-control"
                                        id="user_password" name="user_password" placeholder="Password"
                                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                        title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters"
                                        required value="<?php echo isset($user_password) ? $user_password : "" ?>" required>
                                    <div id="message">
                                        <h3>Password must contain the following:</h3>
                                        <p id="letter" class="invalid">A <b>lowercase</b> letter</p>
                                        <p id="capital" class="invalid">A <b>capital (uppercase)</b> letter</p>
                                        <p id="number" class="invalid">A <b>number</b></p>
                                        <p id="spacial" class="invalid">A <b>special characters</b></p>
                                        <p id="length" class="invalid">Minimum <b>8 characters</b></p>
                                    </div>
                                </div>
                            <?php } ?>

                        </div>
                        <!-- end col -->
                    </div>

                    <!-- Multi-Site Allocation Container -->
                    <div class="row mt-4 p-3 bg-light rounded border" id="site_allocation_container">
                        <div class="col-12 mb-2">
                            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-building text-primary me-2"></i>Multi-Site Access Control Allocation</h5>
                            <span class="fs-7 text-muted">Assign one or multiple sites for Cluster Managers and Account Managers. Logged-in users will only see data for these assigned sites.</span>
                        </div>
                        <div class="col-md-6 mt-2">
                            <div class="form-group mb-0">
                                <label for="oe_site_ids" class="fw-bold text-dark mb-1">Allocated OE Sites (Multi-Select)</label>
                                <select class="form-control select2" name="oe_site_ids[]" id="oe_site_ids" multiple="multiple" data-placeholder="Select OE Sites">
                                    <?php if (isset($oe_clients) && is_array($oe_clients)) { foreach ($oe_clients as $client) { ?>
                                        <option value="<?= $client['client_id'] ?>"><?= esc($client['client_name']) ?><?= !empty($client['cluster']) ? ' ('.esc($client['cluster']).')' : '' ?></option>
                                    <?php } } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <div class="form-group mb-0">
                                <label for="hse_site_ids" class="fw-bold text-dark mb-1">Allocated HSE Sites (Multi-Select)</label>
                                <select class="form-control select2" name="hse_site_ids[]" id="hse_site_ids" multiple="multiple" data-placeholder="Select HSE Sites">
                                    <?php if (isset($hse_clients) && is_array($hse_clients)) { foreach ($hse_clients as $client) { ?>
                                        <option value="<?= $client['client_id'] ?>"><?= esc($client['client_name']) ?><?= !empty($client['cluster']) ? ' ('.esc($client['cluster']).')' : '' ?></option>
                                    <?php } } ?>
                                </select>
                            </div>
                        </div>
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
    // Enhanced user form with cascading dropdowns and validation
    $(document).ready(function () {
        if ($.fn.select2) {
            $('#oe_site_ids, #hse_site_ids').select2({
                placeholder: 'Select Sites',
                allowClear: true
            });
        }

        // Initialize form state
        var isEditing = false;
        var originalData = {};

        // Store original data when editing
        function storeOriginalData() {
            originalData = {
                region: $('#user_region').val(),
                cluster: $('#user_cluster').val(),
                location: $('#user_location').val()
            };
        }

        // When country changes, update regions
        $('#user_emp_country').change(function () {
            var country = $(this).val();

            // Clear all dependent dropdowns
            $('#user_region').html('<option value="">Select Region</option>');
            $('#user_cluster').html('<option value="">Select Cluster</option>');
            $('#user_location').html('<option value="">Select Client</option>');

            if (country) {
                $.ajax({
                    url: '<?= base_url("Masters/LocationHierarchy/getRegionsByCountry") ?>',
                    type: 'POST',
                    data: { country: country },
                    dataType: 'json',
                    success: function (regions) {
                        $.each(regions, function (index, region) {
                            $('#user_region').append(
                                '<option value="' + region.region_name + '" data-region-id="' + region.region_id + '">' +
                                region.region_name + '</option>'
                            );
                        });

                        // Restore selection if editing
                        if (isEditing && originalData.region) {
                            $('#user_region').val(originalData.region).trigger('change');
                        }
                    },
                    error: function () {
                        showAlert('Error loading regions', 'error');
                    }
                });
            }
        });

        // When region changes, update clusters
        $('#user_region').change(function () {
            var region = $(this).val();

            // Clear dependent dropdowns
            $('#user_cluster').html('<option value="">Select Cluster</option>');
            $('#user_location').html('<option value="">Select Client</option>');

            if (region) {
                // Show loading indicator
                $('#user_cluster').append('<option value="">Loading clusters...</option>');

                $.ajax({
                    url: '<?= base_url("Masters/LocationHierarchy/getClustersByRegion") ?>',
                    type: 'POST',
                    data: { region: region },
                    dataType: 'json',
                    success: function (clusters) {
                        $('#user_cluster').html('<option value="">Select Cluster</option>');

                        if (clusters.length === 0) {
                            $('#user_cluster').append('<option value="">No clusters available</option>');
                            return;
                        }

                        $.each(clusters, function (index, cluster) {
                            $('#user_cluster').append(
                                '<option value="' + cluster.cluster_name + '" data-cluster-id="' + cluster.cluster_id + '">' +
                                cluster.cluster_name + '</option>'
                            );
                        });

                        // Restore selection if editing
                        if (isEditing && originalData.cluster) {
                            $('#user_cluster').val(originalData.cluster).trigger('change');
                        }
                    },
                    error: function () {
                        $('#user_cluster').html('<option value="">Error loading clusters</option>');
                        showAlert('Error loading clusters', 'error');
                    }
                });
            }
        });

        // When cluster changes, update locations
        $('#user_cluster').change(function () {
            var cluster = $(this).val();

            // Clear dependent dropdown
            $('#user_location').html('<option value="">Select Client</option>');

            if (cluster) {
                // Show loading indicator
                $('#user_location').append('<option value="">Loading clients...</option>');

                $.ajax({
                    url: '<?= base_url("Masters/LocationHierarchy/getLocationsByCluster") ?>',
                    type: 'POST',
                    data: { cluster: cluster },
                    dataType: 'json',
                    success: function (locations) {
                        $('#user_location').html('<option value="">Select Client</option>');

                        if (locations.length === 0) {
                            $('#user_location').append('<option value="">No clients available</option>');
                            return;
                        }

                        $.each(locations, function (index, location) {
                            $('#user_location').append(
                                '<option value="' + location.location_name + '" data-location-id="' + location.location_id + '">' +
                                location.location_name + '</option>'
                            );
                        });

                        // Restore selection if editing
                        if (isEditing && originalData.location) {
                            $('#user_location').val(originalData.location);
                            isEditing = false; // Reset flag
                        }
                    },
                    error: function () {
                        $('#user_location').html('<option value="">Error loading clients</option>');
                        showAlert('Error loading clients', 'error');
                    }
                });
            }
        });

        // Real-time hierarchy validation
        function validateHierarchy() {
            var region = $('#user_region').val();
            var cluster = $('#user_cluster').val();
            var location = $('#user_location').val();

            if (region && cluster && location) {
                $.ajax({
                    url: '<?= base_url("Masters/LocationHierarchy/validateHierarchy") ?>',
                    type: 'POST',
                    data: {
                        region: region,
                        cluster: cluster,
                        location: location
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (!response.valid) {
                            showAlert('Invalid hierarchy: ' + response.errors.join(', '), 'warning');
                            // Optionally clear invalid selections
                            $('#user_location').val('');
                        }
                    }
                });
            }
        }

        // Trigger validation when location changes
        $('#user_location').change(function() {
            validateHierarchy();
        });

        // Note: Designation-based hierarchy validation is handled directly on form submission in the #ajax_click handler.

        // Override the global edit_id function
        window.edit_id = function (button, id) {
            isEditing = true;

            // Get user data via AJAX
            var ajaxUrl = $(button).data('ajax-url');

            $.ajax({
                url: ajaxUrl,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status == "1" && response.data) {
                        var userData = response.data;

                        // Store original data
                        originalData = {
                            region: userData.user_region,
                            cluster: userData.user_cluster,
                            location: userData.user_location
                        };

                        // Populate form fields
                        $('#user_name').val(userData.user_name);
                        $('#user_email').val(userData.user_email);
                        $('#user_contact').val(userData.user_contact);
                        $('#user_emp_code').val(userData.user_emp_code);
                        $('#user_designation').val(userData.user_designation);
                        $('#user_emp_country').val(userData.user_emp_country);

                        // Trigger cascading dropdowns
                        if (userData.user_emp_country) {
                            $('#user_emp_country').trigger('change');
                        }

                        // Show modal
                        $('#<?= $button_id ?>').modal('show');
                    }
                },
                error: function () {
                    showAlert('Error loading user data', 'error');
                }
            });
        };

        // Reset form when modal closes
        $('#<?= $button_id ?>').on('hidden.bs.modal', function () {
            isEditing = false;
            originalData = {};
            $(this).find('form')[0].reset();

            // Reset dropdowns
            $('#user_region').html('<option value="">Select Region</option>');
            $('#user_cluster').html('<option value="">Select Cluster</option>');
            $('#user_location').html('<option value="">Select Location</option>');
        });

        // Utility function to show alerts (exposed globally)
        window.showAlert = function (message, type) {
            // Use SweetAlert if available, otherwise use regular alert
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
        }
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
        $('#user_designation, #user_location, #user_cluster, #user_region, #user_emp_country').on('change', function () {
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
                    $("#<?= $form_id ?>").find("#user_designation").val(responce.data.user_designation).trigger("change");
                    $("#<?= $form_id ?>").find("#user_location").val(responce.data.user_location);
                    $("#<?= $form_id ?>").find("#user_region").val(responce.data.user_region);
                    $("#<?= $form_id ?>").find("#user_cluster").val(responce.data.user_cluster);
                    $("#<?= $form_id ?>").find("#user_emp_country").val(responce.data.user_emp_country);

                    if (responce.data.oe_site_ids) {
                        $("#oe_site_ids").val(responce.data.oe_site_ids).trigger("change");
                    } else {
                        $("#oe_site_ids").val([]).trigger("change");
                    }
                    if (responce.data.hse_site_ids) {
                        $("#hse_site_ids").val(responce.data.hse_site_ids).trigger("change");
                    } else {
                        $("#hse_site_ids").val([]).trigger("change");
                    }

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
            showCancelButton: true,
            confirmButtonText: 'Save',
        }).then((result) => {
            if (result.isConfirmed) {
                url_call_ajax($(obj).attr("data-ajax-url"), $(obj));
            }
        });

    }

    function update_location_id(obj){
        if ($("#location_id").length > 0) {
            $("#location_id").val(""+$(obj).find("option:selected").data("location-id"));
        }
    }

    var validator = FormValidation.formValidation(form, validation_object);

    $("#ajax_click").on("click", function () {
        var button = $(this);
        var url = $(this).attr("data-ajax-url");

        if (validator) {
            validator.validate().then(function (status) {
                if (status == 'Valid') {
                    var designation = $('#user_designation').val();
                    var region = $('#user_region').val();
                    var cluster = $('#user_cluster').val();
                    
                    if (designation === 'Cluster manager') {
                        if (!region) {
                            if (typeof window.showAlert === 'function') {
                                window.showAlert('Region is required for Cluster Managers', 'error');
                            } else {
                                toastr.warning('Region is required for Cluster Managers');
                            }
                            return false;
                        }
                    } else if (designation === 'Account Manager') {
                        if (!cluster) {
                            if (typeof window.showAlert === 'function') {
                                window.showAlert('Cluster is required for Account Managers', 'error');
                            } else {
                                toastr.warning('Cluster is required for Account Managers');
                            }
                            return false;
                        }
                    }

                    var data_to_send = $("#<?= $form_id ?>_form").serializeArray();
                    var formData = {};
                    $.each(data_to_send, function (i, field) {
                        if (field.name.indexOf('[]') !== -1) {
                            var cleanName = field.name.replace('[]', '');
                            if (!formData[cleanName]) {
                                formData[cleanName] = [];
                            }
                            formData[cleanName].push(field.value);
                        } else {
                            if (field.value.trim() !== "") {
                                formData[field.name] = field.value;
                            }
                        }
                    });

                    ajax_call(url, formData, button, function (responce) {
                        try {
                            responce = JSON.parse(responce);
                            if (responce.status == 1) {
                                toastr.success(responce.message);
                                form.reset();
                                $("#oe_site_ids, #hse_site_ids").val([]).trigger("change");
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
