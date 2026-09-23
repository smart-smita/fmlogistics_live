<?= $this->extend('Layout/base_admin') ?>

<?= $this->section('main_body') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><?= isset($site) ? 'Edit Gemba Site' : 'Create Gemba Site' ?></h3>
        </div>
        <div class="card-body">
            
            <?php if(session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <ul>
                    <?php foreach(session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= isset($site) ? base_url('gemba-sites/edit/'.$site['id']) : base_url('gemba-sites/create') ?>">
                <?= csrf_field() ?>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Site Name <span class="text-danger">*</span></label>
                        <!-- Merged dropdown from alert_client and alert_hse_client_master -->
                        <select name="site_name" class="form-select" required>
                            <option value="">Select Site Name</option>
                            <?php if (isset($merged_sites)): ?>
                                <?php foreach($merged_sites as $s): ?>
                                    <option value="<?= htmlspecialchars($s['site_name']) ?>" <?= old('site_name', $site['site_name'] ?? '') == $s['site_name'] ? 'selected' : '' ?>><?= htmlspecialchars($s['site_name']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="<?= htmlspecialchars($site['site_name'] ?? '') ?>" selected><?= htmlspecialchars($site['site_name'] ?? '') ?></option>
                            <?php endif; ?>
                        </select>
                        <?php if (isset($site)): ?>
                            <div class="form-text text-warning font-weight-bold mt-1">
                                <strong>Note:</strong> If you change the Client Name, the updated name will replace the old Client Name in all related/dependent forms and records.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Region <span class="text-danger">*</span></label>
                        <select name="region" class="form-select" required>
                            <option value="">Select Region</option>
                            <?php if (isset($db_regions)): ?>
                                <?php foreach($db_regions as $r): ?>
                                    <option value="<?= htmlspecialchars($r['region_name']) ?>" <?= old('region', $site['region'] ?? '') == $r['region_name'] ? 'selected' : '' ?>><?= htmlspecialchars($r['region_name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Site Type 1 <span class="text-danger">*</span></label>
                        <input type="text" name="site_type_1" class="form-control" value="<?= old('site_type_1', $site['site_type_1'] ?? 'ISO') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Site Type 2 <span class="text-danger">*</span></label>
                        <input type="text" name="site_type_2" class="form-control" value="<?= old('site_type_2', $site['site_type_2'] ?? 'FM WH') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Site Category <span class="text-danger">*</span></label>
                        <input type="text" name="site_category" class="form-control" value="<?= old('site_category', $site['site_category'] ?? 'Category-1') ?>" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="<?= base_url('gemba-sites') ?>" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Gemba Site</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($site)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const initialSiteName = <?= json_encode($site['site_name'] ?? '') ?>;
    let isConfirmed = false;

    form.addEventListener('submit', function(e) {
        if (isConfirmed) return;
        const currentSiteName = form.querySelector('[name="site_name"]').value.trim();
        if (initialSiteName && currentSiteName && initialSiteName !== currentSiteName) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Changing the Client Name will update the Client Name in all dependent forms. Do you want to continue?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Update',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        isConfirmed = true;
                        form.submit();
                    }
                });
            } else {
                if (confirm('Changing the Client Name will update the Client Name in all dependent forms. Do you want to continue?')) {
                    isConfirmed = true;
                    form.submit();
                }
            }
        }
    });
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
