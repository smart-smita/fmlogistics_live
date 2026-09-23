<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<li class="breadcrumb-item text-muted">
    <a class="text-muted text-hover-primary">PROFILE</a>
</li>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>

<div class="row">
    <!-- View Details Card -->
    <div class="col-md-7 mb-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title text-white m-0 py-3"><i class="fa fa-user me-2 text-white"></i> Profile Details
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless table-striped">
                        <tbody>

                            <tr>
                                <th class="text-muted">Name</th>
                                <td><strong><?= esc($user['user_name']) ?></strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Email</th>
                                <td><strong><?= esc($user['user_email']) ?></strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Contact</th>
                                <td><strong><?= esc($user['user_contact']) ?: '-' ?></strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Location
                                    Details<br /><small>(Country/Zone/Region/Cluster/Location)</small></th>
                                <td>
                                    <?php
                                    $locArray = [
                                        $user['user_emp_country'],
                                        $user['user_emp_zone'],
                                        $user['user_region'],
                                        $user['user_cluster'],
                                        $user['user_location']
                                    ];
                                    // Remove empty values
                                    $locFiltered = array_filter($locArray, function ($v) {
                                        return !empty(trim($v));
                                    });
                                    echo esc(implode(' / ', $locFiltered)) ?: '-';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Designation</th>
                                <td><span class="badge badge-info"><?= esc($user['user_designation']) ?: '-' ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Reporting To</th>
                                <td><strong><?= esc($user['employee_reporting_to']) ?: '-' ?></strong></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status</th>
                                <td>
                                    <?php if ($user['status'] == 1 || $user['status'] == '1'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Card -->
    <div class="col-md-5 mb-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white">
                <h3 class="card-title text-white m-0 py-3"><i class="fa fa-lock me-2 text-white"></i> Change Password
                </h3>
            </div>
            <div class="card-body">
                <form id="changePasswordForm">
                    <div id="passwordAlert" class="alert d-none"></div>

                    <div class="form-group mb-4">
                        <label for="current_password" class="font-weight-bold">Current Password <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required>
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" style="cursor: pointer;">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="new_password" class="font-weight-bold">New Password <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password"
                                minlength="6" required>
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" style="cursor: pointer;">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <small class="form-text text-muted">Min 6 characters, uppercase, lowercase, number, and special
                            character (e.g. Abc@123).</small>
                    </div>

                    <div class="form-group mb-4">
                        <label for="confirm_password" class="font-weight-bold">Confirm New Password <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                minlength="6" required>
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" style="cursor: pointer;">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnChangePassword">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<script>
    $(document).ready(function () {
        // Toggle Password Visibility
        $('.toggle-password').on('click', function () {
            let input = $(this).closest('.input-group').find('input');
            let icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Handle AJAX Form Submission
        $('#changePasswordForm').on('submit', function (e) {
            e.preventDefault();

            let current_password = $('#current_password').val();
            let new_password = $('#new_password').val();
            let confirm_password = $('#confirm_password').val();

            let alertBox = $('#passwordAlert');
            let btn = $('#btnChangePassword');

            // Basic Frontend Validation
            if (new_password.length < 6) {
                showAlert('danger', 'Password must be at least 6 characters.');
                return;
            }

            let passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{6,}$/;
            if (!passwordRegex.test(new_password)) {
                showAlert('danger', 'Password format should be e.g. Abc@123 (uppercase, lowercase, number, special char).');
                return;
            }
            if (new_password !== confirm_password) {
                showAlert('danger', 'New Password and Confirm Password do not match.');
                return;
            }
            if (new_password === current_password) {
                showAlert('danger', 'New Password cannot be the same as Current Password.');
                return;
            }

            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

            $.ajax({
                url: '<?= base_url("profile/update-password") ?>',
                type: 'POST',
                data: {
                    current_password: current_password,
                    new_password: new_password,
                    confirm_password: confirm_password
                },
                dataType: 'json',
                success: function (response) {
                    btn.prop('disabled', false).text('Update Password');

                    if (response.status == 1) {
                        showAlert('success', response.message + ' Redirecting to login...');
                        $('#changePasswordForm')[0].reset();
                        // Logout user after password change (security purpose)
                        setTimeout(function () {
                            window.location.href = response.redirect;
                        }, 2000);
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function () {
                    btn.prop('disabled', false).text('Update Password');
                    showAlert('danger', 'An error occurred while updating the password.');
                }
            });
        });

        function showAlert(type, message) {
            $('#passwordAlert')
                .removeClass('alert-success alert-danger d-none')
                .addClass('alert-' + type)
                .text(message)
                .show();

            setTimeout(function () {
                $('#passwordAlert').fadeOut();
            }, 5000);
        }
    });
</script>
<?php $this->endSection(); ?>