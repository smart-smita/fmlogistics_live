<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<!--begin::Item-->
<li class="breadcrumb-item text-muted">
    <a href="<?=current_url()?>" class="text-muted text-hover-primary">Email Settings</a>
</li>
<!--end::Item-->

<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>

<style>
    .settings-container {
        max-width: 800px;
        margin: 40px auto;
    }
</style>

<div class="row gy-5 g-xl-8">
<div class="col-xl-12">

<div class="container settings-container">
    <h2 class="mb-4">Email Settings</h2>
    <p class="mb-8">Manage outgoing system emails and SMTP configurations.</p>
    
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('admin/settings/email-settings/save') ?>" method="post">
        
        <div class="card mb-8">
            <div class="card-header bg-primary">
                <h3 class="card-title" style="color: #fff !important;">Global Email Control</h3>
            </div>
            <div class="card-body d-flex align-items-center">
                <label class="toggle-switch mb-0 me-4 mr-3">
                    <input type="checkbox" name="email_enabled" value="1" <?= (!empty($settings) && $settings['email_enabled'] == 1) ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <div>
                    <strong>Email Notifications</strong><br>
                    <span class="text-muted">When disabled, no emails will be sent from the system.</span>
                </div>
            </div>
        </div>

        <div class="card mb-8">
            <div class="card-header bg-secondary">
                <h3 class="card-title">SMTP Configuration</h3>
            </div>
            <div class="card-body">
                <div class="row mb-5">
                    <div class="col-md-6 form-group">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?= $settings['smtp_host'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label">SMTP Port</label>
                        <input type="text" name="smtp_port" class="form-control" value="<?= $settings['smtp_port'] ?? '' ?>" required>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6 form-group">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_username" class="form-control" value="<?= $settings['smtp_username'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_password" class="form-control" placeholder="Leave blank to keep existing password">
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6 form-group">
                        <label class="form-label">SMTP Encryption</label>
                        <select name="smtp_encryption" class="form-select form-control">
                            <option value="tls" <?= (!empty($settings) && $settings['smtp_encryption'] == 'tls') ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= (!empty($settings) && $settings['smtp_encryption'] == 'ssl') ? 'selected' : '' ?>>SSL</option>
                            <option value="" <?= (!empty($settings) && empty($settings['smtp_encryption'])) ? 'selected' : '' ?>>None</option>
                        </select>
                    </div>
                </div>

                <div class="separator my-5"></div>

                <div class="row mb-5">
                    <div class="col-md-6 form-group">
                        <label class="form-label">From Email</label>
                        <input type="email" name="from_email" class="form-control" value="<?= $settings['from_email'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label">From Name</label>
                        <input type="text" name="from_name" class="form-control" value="<?= $settings['from_name'] ?? '' ?>" required>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6 form-group">
                        <label class="form-label">Reply To Email</label>
                        <input type="email" name="reply_to_email" class="form-control" value="<?= $settings['reply_to_email'] ?? '' ?>">
                    </div>
                </div>

            </div>
            <div class="card-footer text-end text-right">
                <button type="submit" class="btn btn-success">Save Settings</button>
            </div>
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-header bg-info">
            <h3 class="card-title" style="color: #fff !important;">Test SMTP Connection</h3>
        </div>
        <div class="card-body">
            <form action="<?= base_url('admin/settings/email-settings/test') ?>" method="post" class="d-flex align-items-center">
                <label class="me-3 mr-2 fw-bold">Send Test Email To:</label>
                <input type="email" name="test_email" class="form-control w-250px me-3 mr-2" placeholder="Enter email address" required>
                <button type="submit" class="btn btn-primary">Send Test Email</button>
            </form>
        </div>
    </div>

</div>

</div>
</div>

<?php $this->endSection(); ?>
