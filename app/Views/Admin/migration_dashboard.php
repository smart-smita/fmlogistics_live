<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<li class="breadcrumb-item text-muted">
    <a href="<?= current_url() ?>" class="text-muted text-hover-primary">Admin</a>
</li>
<li class="breadcrumb-item text-muted">
    <a href="<?= current_url() ?>" class="text-muted text-hover-primary">Database Migration</a>
</li>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Database Migration Status</h3>
            </div>
            <div class="card-body">
                <div class="row mb-5">
                    <div class="col-md-4">
                        <div class="card <?= $migration_status['hierarchy_columns'] ? 'bg-success' : 'bg-warning' ?>">
                            <div class="card-body text-center">
                                <i class="fas fa-database fa-2x mb-2"></i>
                                <h5>Hierarchy Columns</h5>
                                <p><?= $migration_status['hierarchy_columns'] ? 'Added' : 'Missing' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card <?= $migration_status['data_populated'] ? 'bg-success' : 'bg-warning' ?>">
                            <div class="card-body text-center">
                                <i class="fas fa-sitemap fa-2x mb-2"></i>
                                <h5>Data Population</h5>
                                <p><?= $migration_status['data_populated'] ? 'Completed' : 'Pending' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card <?= $migration_status['foreign_keys'] ? 'bg-success' : 'bg-warning' ?>">
                            <div class="card-body text-center">
                                <i class="fas fa-link fa-2x mb-2"></i>
                                <h5>Foreign Keys</h5>
                                <p><?= $migration_status['foreign_keys'] ? 'Created' : 'Missing' ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <?php if (!$migration_status['hierarchy_columns'] || !$migration_status['data_populated'] || !$migration_status['foreign_keys']): ?>
                        <button class="btn btn-primary btn-lg" onclick="runMigration()">
                            <i class="fas fa-play"></i> Run Migration
                        </button>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Migration completed successfully!
                        </div>
                        <button class="btn btn-info" onclick="runMigration()">
                            <i class="fas fa-refresh"></i> Re-run Migration
                        </button>
                    <?php endif; ?>
                </div>

                <div class="mt-5">
                    <h4>Migration Steps:</h4>
                    <ol>
                        <li>Add hierarchy columns (region_id, cluster_id, location_id)</li>
                        <li>Populate cluster-region relationships</li>
                        <li>Populate location-cluster relationships</li>
                        <li>Update user table with proper IDs</li>
                        <li>Add foreign key constraints</li>
                        <li>Validate data integrity</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="migration-results" style="display: none;">
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Migration Results</h3>
        </div>
        <div class="card-body" id="results-content">
            <!-- Results will be loaded here -->
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->section("custom_js"); ?>
<script>
function runMigration() {
    // Show loading
    Swal.fire({
        title: 'Running Migration...',
        text: 'Please wait while the database migration is in progress.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Run migration
    $.ajax({
        url: '<?= base_url("Admin/DataMigration/runMigration") ?>',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                Swal.fire({
                    title: 'Migration Completed!',
                    text: 'Database migration completed successfully.',
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Migration Error',
                    text: response.error || 'Migration failed',
                    icon: 'error'
                });
            }
            
            // Show detailed results
            displayResults(response.results);
        },
        error: function() {
            Swal.close();
            Swal.fire({
                title: 'Error',
                text: 'Failed to run migration. Please try again.',
                icon: 'error'
            });
        }
    });
}

function displayResults(results) {
    let html = '';
    
    results.forEach(function(result) {
        let statusClass = result.status === 'success' ? 'success' : 
                         result.status === 'warning' ? 'warning' : 'danger';
        
        html += `
            <div class="alert alert-${statusClass}">
                <h5><i class="fas fa-${result.status === 'success' ? 'check' : 'exclamation-triangle'}"></i> ${result.step}</h5>
                <ul>
        `;
        
        result.details.forEach(function(detail) {
            html += `<li>${detail}</li>`;
        });
        
        html += `
                </ul>
            </div>
        `;
    });
    
    $('#results-content').html(html);
    $('#migration-results').show();
}
</script>
<?php $this->endSection(); ?>
