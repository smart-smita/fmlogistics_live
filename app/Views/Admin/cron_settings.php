<?= $this->extend('Layout/base_admin') ?>

<?= $this->section('breadcrumb_title_li') ?>
<li class="breadcrumb-item text-muted">
    <a href="<?=current_url()?>" class="text-muted text-hover-primary">Cron Settings</a>
</li>
<?= $this->endSection() ?>

<?= $this->section('main_body') ?>



<div class="row gy-5 g-xl-8">
    <div class="col-xl-12">
        <div class="container settings-container">
            <h2 class="mb-4">Cron Settings</h2>
            <p class="mb-8">Manage scheduled tasks (cron jobs). You can temporarily enable or disable individual crons without modifying the server cron configuration.</p>
            
            <div class="card mb-8">
                <div class="card-header bg-primary">
                    <h3 class="card-title" style="color: #fff !important;">Cron Jobs Control</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Cron Job</th>
                                    <th>Schedule</th>
                                    <th>Last Run</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($crons)): ?>
                                    <?php foreach($crons as $cron): ?>
                                        <tr>
                                            <td><strong><?= esc($cron['cron_name']) ?></strong></td>
                                            <td><?= esc($cron['schedule']) ?></td>
                                            <td>
                                                <?= $cron['last_run'] ? date('d M, Y h:i A', strtotime($cron['last_run'])) : '<span class="text-muted">Never</span>' ?>
                                            </td>
                                            <td>
                                                <?php if($cron['is_enabled'] == 1): ?>
                                                    <span id="badge-<?= $cron['id'] ?>" class="badge bg-success">🟢 Enabled</span>
                                                <?php else: ?>
                                                    <span id="badge-<?= $cron['id'] ?>" class="badge bg-danger">🔴 Disabled</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <label class="toggle-switch mb-0">
                                                    <input type="checkbox" class="cron-toggle" data-id="<?= $cron['id'] ?>" <?= $cron['is_enabled'] == 1 ? 'checked' : '' ?>>
                                                    <span class="slider"></span>
                                                </label>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">No cron jobs found in database.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('javascript_section') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('.cron-toggle').on('change', function(e) {
        let isChecked = $(this).is(':checked');
        let cronId = $(this).data('id');
        let checkbox = $(this);
        let badge = $('#badge-' + cronId);
        
        // Revert immediately, wait for confirmation
        checkbox.prop('checked', !isChecked);
        
        let actionText = isChecked ? 'Enable' : 'Disable';
        
        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to ${actionText} this cron job?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Yes, ${actionText} it!`
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed with AJAX
                $.ajax({
                    url: '<?= base_url('admin/settings/cron-settings/toggle') ?>',
                    type: 'POST',
                    data: {
                        id: cronId,
                        status: isChecked ? 1 : 0
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            checkbox.prop('checked', isChecked); // set to actual choice
                            
                            // Update badge
                            if (isChecked) {
                                badge.removeClass('bg-danger').addClass('bg-success').html('🟢 Enabled');
                            } else {
                                badge.removeClass('bg-success').addClass('bg-danger').html('🔴 Disabled');
                            }
                            
                            Swal.fire(
                                'Updated!',
                                response.message,
                                'success'
                            );
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'An error occurred while updating the status.', 'error');
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
