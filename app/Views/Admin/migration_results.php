<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<li class="breadcrumb-item text-muted">
    <a href="<?= current_url() ?>" class="text-muted text-hover-primary">Admin</a>
</li>
<li class="breadcrumb-item text-muted">
    <a href="<?= current_url() ?>" class="text-muted text-hover-primary">Migration Results</a>
</li>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <?php if ($success): ?>
                        <i class="fas fa-check-circle text-success"></i> Migration Completed Successfully
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle text-danger"></i> Migration Failed
                    <?php endif; ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if (!$success && isset($error)): ?>
                    <div class="alert alert-danger">
                        <strong>Error:</strong> <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php foreach ($results as $result): ?>
                    <div class="alert alert-<?= $result['status'] === 'success' ? 'success' : ($result['status'] === 'warning' ? 'warning' : 'danger') ?>">
                        <h5>
                            <i class="fas fa-<?= $result['status'] === 'success' ? 'check' : 'exclamation-triangle' ?>"></i>
                            <?= $result['step'] ?>
                        </h5>
                        
                        <?php if (!empty($result['details'])): ?>
                            <ul class="mb-0">
                                <?php foreach ($result['details'] as $detail): ?>
                                    <li><?= $detail ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="text-center mt-4">
                    <a href="<?= base_url('Admin/DataMigration') ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Migration Dashboard
                    </a>
                    
                    <?php if ($success): ?>
                        <a href="<?= base_url('Masters/User') ?>" class="btn btn-success">
                            <i class="fas fa-users"></i> Test User Management
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>
