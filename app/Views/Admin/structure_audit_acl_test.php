<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<li class="breadcrumb-item text-muted">
    <a href="<?= current_url() ?>" class="text-muted text-hover-primary">Admin</a>
</li>
<li class="breadcrumb-item text-muted">
    <a href="<?= current_url() ?>" class="text-muted text-hover-primary">Structure Audit ACL Test</a>
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

    <!-- ACL Function Tests -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ACL Function Tests</h3>
            </div>
            <div class="card-body">
                <h5>Region Access Tests</h5>
                <div class="row mb-3">
                    <?php foreach($acl_tests['region_access'] as $region => $canAccess): ?>
                        <div class="col-md-3">
                            <span class="badge badge-<?= $canAccess ? 'success' : 'danger' ?>">
                                <?= $region ?>: <?= $canAccess ? 'Yes' : 'No' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h5>Audit Permission Tests</h5>
                <div class="row mb-3">
                    <?php foreach($acl_tests['audit_permissions'] as $type => $canPerform): ?>
                        <div class="col-md-4">
                            <span class="badge badge-<?= $canPerform ? 'success' : 'warning' ?>">
                                <?= ucfirst($type) ?>: <?= $canPerform ? 'Can Perform' : 'Read Only' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h5>Accessible Regions</h5>
                <p>
                    <?php if(!empty($acl_tests['accessible_regions'])): ?>
                        <?php foreach($acl_tests['accessible_regions'] as $region): ?>
                            <span class="badge badge-primary"><?= $region ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="badge badge-secondary">None</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Permission Tests by Role -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permission Tests by Role</h3>
            </div>
            <div class="card-body">
                <?php foreach($permission_tests as $role => $permissions): ?>
                    <div class="mb-3">
                        <h6><?= $role ?></h6>
                        <div class="row">
                            <div class="col-md-4">
                                <small>Structure Audit:</small><br>
                                <span class="badge badge-<?= $permissions['can_perform_structure_audit'] ? 'success' : 'warning' ?>">
                                    <?= $permissions['can_perform_structure_audit'] ? 'Can Perform' : 'Read Only' ?>
                                </span>
                            </div>
                            <div class="col-md-4">
                                <small>Normal Audit:</small><br>
                                <span class="badge badge-<?= $permissions['can_perform_normal_audit'] ? 'success' : 'warning' ?>">
                                    <?= $permissions['can_perform_normal_audit'] ? 'Can Perform' : 'Read Only' ?>
                                </span>
                            </div>
                            <div class="col-md-4">
                                <small>HSE Audit:</small><br>
                                <span class="badge badge-<?= $permissions['can_perform_hse_audit'] ? 'success' : 'warning' ?>">
                                    <?= $permissions['can_perform_hse_audit'] ? 'Can Perform' : 'Read Only' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <hr>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Audit Access Tests by Region -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Audit Access Tests by Region</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Region</th>
                                <th>ACL Condition</th>
                                <th>Accessible Audits</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($audit_access_test as $region => $test): ?>
                                <tr>
                                    <td><span class="badge badge-primary"><?= $region ?></span></td>
                                    <td><code><?= $test['acl_condition'] ?: 'No restriction' ?></code></td>
                                    <td><span class="badge badge-info"><?= $test['audit_count'] ?></span></td>
                                    <td>
                                        <?php if($test['audit_count'] > 0): ?>
                                            <span class="badge badge-success">Has Access</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">No Access</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Actions -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Test Actions</h3>
            </div>
            <div class="card-body text-center">
                <button class="btn btn-primary" onclick="simulateClusterManagerAccess()">
                    <i class="fas fa-user-shield"></i> Simulate Cluster Manager Access
                </button>
                
                <button class="btn btn-info" onclick="testSpecificUser()">
                    <i class="fas fa-user-check"></i> Test Specific User
                </button>
                
                <a href="<?= base_url('Masters/Audit_template') ?>" class="btn btn-success" target="_blank">
                    <i class="fas fa-clipboard-list"></i> Test Audit Templates
                </a>
                
                <a href="<?= base_url('Masters/Audit_final_structure') ?>" class="btn btn-warning" target="_blank">
                    <i class="fas fa-tasks"></i> Test Structure Audits
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Test Results Modal -->
<div class="modal fade" id="testResultsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="testResultsContent"></pre>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->section("custom_js"); ?>
<script>
function simulateClusterManagerAccess() {
    $.ajax({
        url: '<?= base_url("Admin/StructureAuditACLTest/simulateClusterManagerAccess") ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#testResultsContent').text(JSON.stringify(response, null, 2));
            $('#testResultsModal').modal('show');
        },
        error: function() {
            alert('Error running test');
        }
    });
}

function testSpecificUser() {
    var userId = prompt('Enter User ID to test:');
    if (userId) {
        $.ajax({
            url: '<?= base_url("Admin/StructureAuditACLTest/testUserStructureAccess/") ?>' + userId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#testResultsContent').text(JSON.stringify(response, null, 2));
                $('#testResultsModal').modal('show');
            },
            error: function() {
                alert('Error running test');
            }
        });
    }
}

// Highlight important test results
$(document).ready(function() {
    // Highlight read-only permissions
    $('.badge:contains("Read Only")').addClass('badge-warning');
    $('.badge:contains("Can Perform")').addClass('badge-success');
    
    // Highlight no access regions
    $('.badge:contains("No Access")').addClass('badge-danger');
    $('.badge:contains("Has Access")').addClass('badge-success');
});
</script>
<?php $this->endSection(); ?>
