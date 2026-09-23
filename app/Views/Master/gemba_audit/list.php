<?= $this->extend('Layout/base_admin') ?>

<?= $this->section('main_body') ?>
<div class="container-fluid mt-4">
    <!-- Filters Card -->
    <div class="card shadow-sm mb-5">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Gemba Audit Filters
                    <?php helper('gemba_acl'); if (!gemba_can_write()): ?>
                        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.55em; vertical-align: middle; padding: 6px 10px; border-radius: 6px;">Read Only Access</span>
                    <?php endif; ?>
                </span>
            </h3>
            <div class="card-toolbar">
                <?php if (gemba_can_write()): ?>
                <a href="<?= base_url('gemba-audit/import') ?>" class="btn btn-sm btn-info me-2">
                    <i class="fas fa-file-import me-2"></i>Import Excel/CSV
                </a>
                <a href="<?= base_url('gemba-audit/create') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-2"></i>Create New
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <form id="gemba_filter_form" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-bold">Region</label>
                    <select name="region[]" id="filter_region" class="form-select select2-filter" multiple="multiple" data-placeholder="All Regions">
                        <?php foreach ($regions as $r): ?>
                            <option value="<?= $r['region'] ?>"><?= $r['region'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Audit Category</label>
                    <select name="audit_category[]" id="filter_category" class="form-select select2-filter" multiple="multiple" data-placeholder="All Categories">
                        <?php
                        $cats = ['ISO Audit', 'HSE Audit', 'GIA Audit', 'Electric Audit', 'Fire Audit', 'Near Miss Scanner'];
                        foreach ($cats as $cat): ?>
                            <option value="<?= $cat ?>"><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Point Status</label>
                    <select name="point_status[]" id="filter_status" class="form-select select2-filter" multiple="multiple" data-placeholder="All Statuses">
                        <option value="Open">Open</option>
                        <option value="WIP">WIP</option>
                        <option value="Closed">Closed</option>
                        <option value="Excluded">Excluded</option>
                        <option value="Hold-review with Client">Hold-review with Client</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Color Code</label>
                    <select name="color_code[]" id="filter_color" class="form-select select2-filter" multiple="multiple" data-placeholder="All Colors">
                        <option value="Red">Red</option>
                        <option value="Yellow">Yellow</option>
                        <option value="Black">Black</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="button" id="btn_apply_filter" class="btn btn-primary flex-fill">
                        <i class="fas fa-filter me-2"></i>Show
                    </button>
                    <button type="button" id="btn_reset_filter" class="btn btn-light flex-fill">
                        <i class="fas fa-sync-alt me-2"></i>Reset
                    </button>
                    <button type="button" id="btn_export_csv" class="btn btn-info">
                        <i class="fas fa-file-csv me-2"></i>CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

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
        // Initialize Select2 if available
        if ($.fn.select2) {
            $('.select2-filter').select2({
                placeholder: "Select option",
                allowClear: true,
                width: '100%'
            });
        }

        const tableId = '#gemba_audit_table';

        // Apply Filter
        $('#btn_apply_filter').on('click', function () {
            reloadDataTable();
        });

        // Reset Filter
        $('#btn_reset_filter').on('click', function () {
            $('#gemba_filter_form')[0].reset();
            $('.select2-filter').val(null).trigger('change');
            reloadDataTable();
        });

        function reloadDataTable() {
            if ($.fn.DataTable.isDataTable(tableId)) {
                const params = $('#gemba_filter_form').serialize();
                const newUrl = '<?= base_url("gemba-audit/table_ajax") ?>?' + params;
                $(tableId).DataTable().ajax.url(newUrl).load();
            }
        }

        // Export CSV
        $('#btn_export_csv').on('click', function () {
            const params = $('#gemba_filter_form').serialize();
            window.location.href = '<?= base_url("gemba-audit/gemba_export") ?>?' + params;
        });



        // Sync table adjustment on filter change (optional but good for UI)
        $('.select2-filter').on('change', function () {
            // reloadDataTable(); // Uncomment if auto-reload is desired
        });
    });
</script>
<?= $this->endSection() ?>