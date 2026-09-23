<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<li class="breadcrumb-item text-muted">
    <a href="<?= current_url() ?>" class="text-muted text-hover-primary">Admin</a>
</li>
<li class="breadcrumb-item text-muted">
    <a href="<?= current_url() ?>" class="text-muted text-hover-primary">ACL Test</a>
</li>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<div class="row">
    <!-- Current User Info -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Current User Session</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach($current_user as $key => $value): ?>
                        <div class="col-md-2">
                            <strong><?= ucfirst(str_replace('_', ' ', $key)) ?>:</strong><br>
                            <span class="badge badge-info"><?= $value ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- User Counts by Role -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User Distribution by Role</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Total</th>
                                <th>With Region</th>
                                <th>With Cluster</th>
                                <th>With Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($user_counts as $count): ?>
                                <tr>
                                    <td><?= $count['user_designation'] ?></td>
                                    <td><span class="badge badge-primary"><?= $count['total_count'] ?></span></td>
                                    <td><span class="badge badge-success"><?= $count['with_region'] ?></span></td>
                                    <td><span class="badge badge-info"><?= $count['with_cluster'] ?></span></td>
                                    <td><span class="badge badge-warning"><?= $count['with_location'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Hierarchy Test Results -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hierarchy Relationships</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h5>Clusters</h5>
                    <p>Total: <span class="badge badge-primary"><?= $hierarchy_test['clusters']['total_clusters'] ?></span></p>
                    <p>With Region: <span class="badge badge-success"><?= $hierarchy_test['clusters']['clusters_with_region'] ?></span></p>
                </div>

                <div class="mb-3">
                    <h5>Locations</h5>
                    <p>Total: <span class="badge badge-primary"><?= $hierarchy_test['locations']['total_locations'] ?></span></p>
                    <p>With Cluster: <span class="badge badge-success"><?= $hierarchy_test['locations']['locations_with_cluster'] ?></span></p>
                </div>

                <div class="mb-3">
                    <h5>Users</h5>
                    <p>Total: <span class="badge badge-primary"><?= $hierarchy_test['users']['total_users'] ?></span></p>
                    <p>With Hierarchy: <span class="badge badge-success"><?= $hierarchy_test['users']['users_with_hierarchy'] ?></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- ACL Filtering Test -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ACL Filtering Test</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach($acl_test as $role => $result): ?>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5><?= $role ?></h5>
                                    <p class="h3 text-primary"><?= $result['count'] ?></p>
                                    <small>Accessible Users</small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Hierarchy Sample Data -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hierarchy Sample Data</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User Name</th>
                                <th>Region</th>
                                <th>Cluster</th>
                                <th>Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($hierarchy_test['hierarchy_sample'] as $sample): ?>
                                <tr>
                                    <td><?= $sample['user_name'] ?></td>
                                    <td><?= $sample['region_name'] ?: 'N/A' ?></td>
                                    <td><?= $sample['cluster_name'] ?: 'N/A' ?></td>
                                    <td><?= $sample['location_name'] ?: 'N/A' ?></td>
                                    <td>
                                        <span class="badge badge-<?= $sample['hierarchy_status'] == 'Valid' ? 'success' : 'danger' ?>">
                                            <?= $sample['hierarchy_status'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12 text-center">
        <a href="<?= base_url('Admin/DataMigration') ?>" class="btn btn-primary">
            <i class="fas fa-database"></i> Migration Dashboard
        </a>
        <a href="<?= base_url('Masters/User') ?>" class="btn btn-success">
            <i class="fas fa-users"></i> Test User Management
        </a>
        <a href="<?= base_url('Customer/Audit_dashboard/OE_Audit') ?>" class="btn btn-info">
            <i class="fas fa-tachometer-alt"></i> Test Dashboard
        </a>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->section("custom_js"); ?>
<script>
// Add any custom JavaScript for ACL testing
$(document).ready(function() {
    // Highlight invalid hierarchy entries
    $('tbody tr').each(function() {
        var status = $(this).find('.badge').text().trim();
        if (status === 'Invalid') {
            $(this).addClass('table-danger');
        }
    });
});
</script>
<?php $this->endSection(); ?>
