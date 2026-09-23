<?= $this->extend('Layout/base_admin') ?>

<?= $this->section('main_body') ?>
<div class="container-fluid mt-4">
    <!-- Header Card -->
    <div class="card shadow-sm mb-5">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Gemba Sites Master
                    <?php helper('gemba_acl'); if (!gemba_can_write()): ?>
                        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.55em; vertical-align: middle; padding: 6px 10px; border-radius: 6px;">Read Only Access</span>
                    <?php endif; ?>
                </span>
                <span class="text-muted mt-1 fw-bold fs-7">Manage your warehouse sites</span>
            </h3>
            <div class="card-toolbar">
                <?php if (gemba_can_write()): ?>
                <a href="<?= base_url('gemba-sites/create') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-2"></i>Create New Site
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body py-3">
            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                    <i class="fas fa-check-circle fs-2hx text-success me-4"></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Success</h4>
                        <span><?= session()->getFlashdata('success') ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                    <i class="fas fa-exclamation-triangle fs-2hx text-danger me-4"></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-danger">Error</h4>
                        <span><?= session()->getFlashdata('error') ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Table Container -->
            <div class="row">
                <div class="col-12">
                    <?= $table ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript_section') ?>
<script>
    $(document).ready(function () {
        // Gemba Sites table ID from controller: gemba_sites_table
        const tableId = '#gemba_sites_table';
        
        // The table is already initialized by Layout/table-view.php
        // We can add custom behavior here if needed.
        
        // Example: Refresh button in toolbar could call reloadDataTable()
    });

    function reloadDataTable() {
        const tableId = '#gemba_sites_table';
        if ($.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable().ajax.reload();
        }
    }
</script>
<?= $this->endSection() ?>
