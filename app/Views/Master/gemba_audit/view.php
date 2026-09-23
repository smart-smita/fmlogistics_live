<?= $this->extend('Layout/base_admin') ?>

<?= $this->section('main_body') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">View Gemba Audit: <?= htmlspecialchars($audit['unique_no'] ?? '') ?></h3>
            <a href="<?= base_url('gemba-audit') ?>" class="btn btn-secondary">Back to List</a>
        </div>
        <div class="card-body">
            <!-- 1. Audit Header -->
            <h5 class="mb-3 text-primary border-bottom pb-2">Audit Header</h5>
            <div class="row mb-4">
                <div class="col-md-3"><strong>Unique No:</strong> <br><?= htmlspecialchars($audit['unique_no'] ?? '') ?></div>
                <div class="col-md-3"><strong>Region:</strong> <br><?= htmlspecialchars($audit['region'] ?? '') ?></div>
                <div class="col-md-3"><strong>Auditor Name:</strong> <br><?= htmlspecialchars($audit['auditor_name'] ?? '') ?></div>
                <div class="col-md-3"><strong>Site Name:</strong> <br><?= htmlspecialchars($audit['site_name'] ?? '') ?></div>
            </div>
            <div class="row mb-4">
                <div class="col-md-3"><strong>Audit Category:</strong> <br><?= htmlspecialchars($audit['audit_category'] ?? '') ?></div>
                <div class="col-md-3"><strong>Audit Type:</strong> <br><?= htmlspecialchars($audit['audit_type'] ?? '') ?></div>
                <div class="col-md-3"><strong>Audit Doc Type:</strong> <br><?= htmlspecialchars($audit['audit_doc_type'] ?? '') ?></div>
                <div class="col-md-3"><strong>Source Module:</strong> <br><?= htmlspecialchars($audit['source_module'] ?? '') ?></div>
            </div>

            <!-- 2. Site Details -->
            <h5 class="mb-3 text-primary border-bottom pb-2">Site & Category Details</h5>
            <div class="row mb-4">
                <div class="col-md-3"><strong>Site Type 1:</strong> <br><?= htmlspecialchars($audit['site_type_1'] ?? '') ?></div>
                <div class="col-md-3"><strong>Site Type 2:</strong> <br><?= htmlspecialchars($audit['site_type_2'] ?? '') ?></div>
                <div class="col-md-3"><strong>Site Category:</strong> <br><?= htmlspecialchars($audit['site_category'] ?? '') ?></div>
                <div class="col-md-3"><strong>Checklist Category:</strong> <br><?= htmlspecialchars($audit['checklist_category'] ?? '') ?></div>
            </div>

            <!-- 3. Risk Assessment -->
            <h5 class="mb-3 text-primary border-bottom pb-2">Risk Assessment</h5>
            <div class="row mb-4">
                <div class="col-md-3"><strong>Risk Severity:</strong> <br><?= htmlspecialchars($audit['risk_severity'] ?? '') ?> (<?= htmlspecialchars($audit['risk_severity_rating'] ?? '') ?>)</div>
                <div class="col-md-3"><strong>Risk Probability:</strong> <br><?= htmlspecialchars($audit['risk_probability'] ?? '') ?> (<?= htmlspecialchars($audit['risk_probability_rating'] ?? '') ?>)</div>
                <div class="col-md-3"><strong>Combined Risk:</strong> <br><?= htmlspecialchars($audit['combined_risk_rating'] ?? '') ?></div>
                <div class="col-md-3"><strong>Times Repeated:</strong> <br><?= htmlspecialchars($audit['times_repeated'] ?? '') ?></div>
            </div>
            <div class="row mb-4">
                <div class="col-md-3"><strong>Final Rating:</strong> <br><?= htmlspecialchars($audit['final_rating'] ?? '') ?></div>
                <div class="col-md-3"><strong>Color Code:</strong> <br><?= htmlspecialchars($audit['color_code'] ?? '') ?></div>
                <div class="col-md-3"><strong>Cost Type:</strong> <br><?= htmlspecialchars($audit['cost_type'] ?? '') ?></div>
                <div class="col-md-3"><strong>UA/UC Type:</strong> <br><?= htmlspecialchars($audit['ua_uc_type'] ?? '') ?></div>
            </div>

            <!-- 4. Dates & Ageing -->
            <h5 class="mb-3 text-primary border-bottom pb-2">Dates & Ageing</h5>
            <div class="row mb-4">
                <div class="col-md-3"><strong>Audit Report Date:</strong> <br><?= htmlspecialchars($audit['audit_report_date'] ?? '') ?></div>
                <div class="col-md-3"><strong>Target Date:</strong> <br><?= htmlspecialchars($audit['target_date'] ?? '') ?></div>
                <div class="col-md-3"><strong>Closed Date:</strong> <br><?= htmlspecialchars($audit['closed_date'] ?? '') ?></div>
                <div class="col-md-3"><strong>Mail Received:</strong> <br><?= htmlspecialchars($audit['audit_mail_received_date'] ?? '') ?></div>
            </div>
            <div class="row mb-4">
                <div class="col-md-3"><strong>Ageing Days:</strong> <br><?= htmlspecialchars($audit['ageing_days'] ?? '') ?></div>
                <div class="col-md-3"><strong>Age Bracket:</strong> <br><?= htmlspecialchars($audit['age_bracket'] ?? '') ?></div>
                <div class="col-md-6"></div>
            </div>

            <!-- 5. Computed Fields -->
            <h5 class="mb-3 text-primary border-bottom pb-2">Computed Date Fields</h5>
            <div class="row mb-4">
                <div class="col-md-3"><strong>Week Raised:</strong> <br><?= htmlspecialchars($audit['weeknum_yearmonth_raised'] ?? '') ?></div>
                <div class="col-md-3"><strong>Month Raised:</strong> <br><?= htmlspecialchars($audit['month_raised'] ?? '') ?></div>
                <div class="col-md-3"><strong>Week Closed:</strong> <br><?= htmlspecialchars($audit['weeknum_yearmonth_closed'] ?? '') ?></div>
                <div class="col-md-3"><strong>Month Closed:</strong> <br><?= htmlspecialchars($audit['month_closed'] ?? '') ?></div>
            </div>

            <!-- 6. Observation & Details -->
            <h5 class="mb-3 text-primary border-bottom pb-2">Observation & Recommendation</h5>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <strong>Observation Point:</strong>
                    <div class="border p-2 bg-light mt-1"><?= nl2br(htmlspecialchars($audit['observation_point'] ?? '')) ?></div>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Risks Details:</strong>
                    <div class="border p-2 bg-light mt-1"><?= nl2br(htmlspecialchars($audit['risks_details'] ?? '')) ?></div>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Action Recommendation:</strong>
                    <div class="border p-2 bg-light mt-1"><?= nl2br(htmlspecialchars($audit['action_recommendation'] ?? '')) ?></div>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Specifications (Capex/Opex Infra):</strong>
                    <div class="border p-2 bg-light mt-1"><?= nl2br(htmlspecialchars($audit['specifications'] ?? '')) ?></div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-4"><strong>NC/Recommendation:</strong> <br><?= htmlspecialchars($audit['nc_recommendation'] ?? '') ?></div>
                <div class="col-md-4"><strong>Action Category:</strong> <br><?= htmlspecialchars($audit['action_category'] ?? '') ?></div>
                <div class="col-md-4"><strong>QHSE Remarks:</strong> <br><?= htmlspecialchars($audit['qhse_remarks'] ?? '') ?></div>
            </div>

            <!-- 7. Scope & Remarks -->
            <h5 class="mb-3 text-primary border-bottom pb-2">Scope & Corporate Remarks</h5>
            <div class="row mb-4">
                <div class="col-md-4"><strong>Whose Scope:</strong> <br><?= htmlspecialchars($audit['whose_scope'] ?? '') ?></div>
                <div class="col-md-4"><strong>Remarks WM:</strong> <br><?= htmlspecialchars($audit['remarks_wm'] ?? '') ?></div>
                <div class="col-md-4"><strong>Remarks Corporate:</strong> <br><?= htmlspecialchars($audit['remarks_corporate'] ?? '') ?></div>
            </div>

            <!-- 8. Status & Follow-up -->
            <h5 class="mb-3 text-primary border-bottom pb-2">Status & Ownership</h5>
            <div class="row mb-4">
                <div class="col-md-3"><strong>Point Status:</strong> <br><?= htmlspecialchars($audit['point_status'] ?? '') ?></div>
                <div class="col-md-3"><strong>Closure Status:</strong> <br><?= htmlspecialchars($audit['closure_status'] ?? '') ?></div>
                <div class="col-md-3"><strong>Point Category:</strong> <br><?= htmlspecialchars($audit['point_category'] ?? '') ?></div>
                <div class="col-md-3"><strong>Follow-up By:</strong> <br><?= htmlspecialchars($audit['followup_by'] ?? '') ?></div>
            </div>
            <div class="row mb-4">
                <div class="col-md-4"><strong>Cluster Manager/SPOC:</strong> <br><?= htmlspecialchars($audit['cluster_manager_spoc'] ?? '') ?></div>
                <div class="col-md-4"><strong>Account Manager:</strong> <br><?= htmlspecialchars($audit['account_manager'] ?? '') ?></div>
                <div class="col-md-4"></div>
            </div>

            <!-- 9. Workflow & Audit Trail -->
            <h5 class="mb-3 text-primary border-bottom pb-2">NC Workflow & System Info</h5>
            <div class="row mb-4">
                <div class="col-md-3">
                    <strong>NC Status:</strong> <br>
                    <?php
                        $ncMap = [0 => 'Open', 1 => 'Working', 5 => 'Cluster Review', 2 => 'Auditor Review', 3 => 'Closed', 4 => 'Draft', 6 => 'Rejected'];
                        echo htmlspecialchars($ncMap[$audit['nc_status'] ?? 0] ?? $audit['nc_status']);
                    ?>
                </div>
                <div class="col-md-3"><strong>Created By:</strong> <br><?= htmlspecialchars($audit['created_by'] ?? '') ?></div>
                <div class="col-md-3"><strong>Updated By:</strong> <br><?= htmlspecialchars($audit['updated_by'] ?? '') ?></div>
                <div class="col-md-3"><strong>Last Update:</strong> <br><?= htmlspecialchars($audit['update_date'] ?? '') ?></div>
            </div>
            <div class="row mb-4">
                <div class="col-md-3"><strong>Worked By:</strong> <br><?= htmlspecialchars($audit['nc_worked_by'] ?? '') ?></div>
                <div class="col-md-3"><strong>Cluster Reviewed:</strong> <br><?= htmlspecialchars($audit['nc_cluster_reviewed_by'] ?? '') ?><br><small><?= htmlspecialchars($audit['nc_cluster_reviewed_date'] ?? '') ?></small></div>
                <div class="col-md-3"><strong>Auditor Reviewed:</strong> <br><?= htmlspecialchars($audit['nc_auditor_reviewed_by'] ?? '') ?><br><small><?= htmlspecialchars($audit['nc_auditor_reviewed_date'] ?? '') ?></small></div>
                <div class="col-md-3"><strong>Closed By:</strong> <br><?= htmlspecialchars($audit['nc_closed_by'] ?? '') ?><br><small><?= htmlspecialchars($audit['nc_closed_date'] ?? '') ?></small></div>
            </div>
            <?php if (!empty($audit['nc_rejected_by']) || !empty($audit['rejection_reason'])): ?>
            <div class="row mb-4 text-danger">
                <div class="col-md-4"><strong>Rejected By:</strong> <br><?= htmlspecialchars($audit['nc_rejected_by'] ?? '') ?><br><small><?= htmlspecialchars($audit['nc_rejected_date'] ?? '') ?></small></div>
                <div class="col-md-8"><strong>Rejection Reason:</strong> <br><?= nl2br(htmlspecialchars($audit['rejection_reason'] ?? '')) ?></div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?= $this->endSection() ?>
