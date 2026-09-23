<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<!--begin::Item-->
<li class="breadcrumb-item text-muted">
    <span class="text-muted">Access Denied</span>
</li>
<!--end::Item-->
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-column align-items-center justify-content-center py-10">
            <!--begin::Icon-->
            <div class="mb-5">
                <i class="fas fa-exclamation-triangle text-danger" style="font-size: 80px;"></i>
            </div>
            <!--end::Icon-->
            
            <!--begin::Title-->
            <h1 class="fw-bold text-gray-800 mb-5"><?= esc($title ?? 'Access Denied') ?></h1>
            <!--end::Title-->
            
            <!--begin::Message-->
            <div class="fs-4 text-gray-600 mb-8 text-center" style="max-width: 500px;">
                <?= esc($message ?? 'You do not have permission to access this resource.') ?>
            </div>
            <!--end::Message-->
            
            <!--begin::User Info-->
            <div class="mb-8 text-center">
                <span class="badge badge-light-primary fs-6 me-2">
                    <i class="fas fa-user me-1"></i> <?= esc($user_name ?? 'Unknown User') ?>
                </span>
                <span class="badge badge-light-warning fs-6">
                    <i class="fas fa-id-badge me-1"></i> <?= esc($user_role ?? 'Unknown Role') ?>
                </span>
            </div>
            <!--end::User Info-->
            
            <!--begin::Actions-->
            <div class="d-flex">
                <a href="<?= base_url('/') ?>" class="btn btn-primary me-3">
                    <i class="fas fa-home me-2"></i>Go to Dashboard
                </a>
                <a href="javascript:history.back()" class="btn btn-light-primary">
                    <i class="fas fa-arrow-left me-2"></i>Go Back
                </a>
            </div>
            <!--end::Actions-->
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->section("modals_section"); ?>
<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<?php $this->endSection(); ?>
