<?= $this->extend('Layout/base_admin') ?>

<?= $this->section('main_body') ?>
<div class="container-fluid mt-4">
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success d-flex align-items-center p-5 mb-5 shadow-sm">
            <i class="fas fa-check-circle fs-2hx text-success me-4"></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-success">Success</h4>
                <span><?= session()->getFlashdata('success') ?></span>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger d-flex align-items-center p-5 mb-5 shadow-sm">
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
