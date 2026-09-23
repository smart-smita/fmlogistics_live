<?= $this->extend('Layout/base_admin') ?>

<?= $this->section('main_body') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><?= isset($audit['gemba_sr_no']) ? 'Edit Gemba Audit' : 'Create Gemba Audit' ?></h3>
        </div>
        <div class="card-body">

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST"
action="<?= isset($audit['gemba_sr_no']) 
    ? base_url('gemba-audit/edit/' . $audit['gemba_sr_no']) 
    : base_url('gemba-audit/create') ?>">
                <?= csrf_field() ?>

                <h5 class="mb-3 text-primary border-bottom pb-2">Audit Header</h5>
                <div class="row mb-3">
                    <?php if (isset($audit['gemba_sr_no'])): ?>
                    <div class="col-md-3">
                        <label class="form-label">Unique No</label>
                        <input type="text" name="unique_no" class="form-control bg-light"
                            value="<?= old('unique_no', $audit['unique_no'] ?? '') ?>" readonly>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-4">
                        <label class="form-label">Region <span class="text-danger">*</span></label>
                        <select name="region" class="form-select" required>
                            <option value="">Select Region</option>
                            <?php if (isset($db_regions)): ?>
                                <?php foreach ($db_regions as $r): ?>
                                    <option value="<?= htmlspecialchars($r['region_name']) ?>" <?= old('region', $audit['region'] ?? '') == $r['region_name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['region_name']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php $regions = ['South', 'North', 'East', 'West']; ?>
                                <?php foreach ($regions as $r): ?>
                                    <option value="<?= $r ?>" <?= old('region', $audit['region'] ?? '') == $r ? 'selected' : '' ?>>
                                        <?= $r ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Auditor Name <span class="text-danger">*</span></label>
                        <select name="auditor_name" class="form-select" required>
                            <option value="">Select Auditor</option>
                            <?php if (isset($db_auditors)): ?>
                                <?php foreach ($db_auditors as $a): ?>
                                    <option value="<?= htmlspecialchars($a['user_name']) ?>" <?= old('auditor_name', $audit['auditor_name'] ?? '') == $a['user_name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($a['user_name']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="Auditor 1" <?= old('auditor_name', $audit['auditor_name'] ?? '') == 'Auditor 1' ? 'selected' : '' ?>>Auditor 1</option>
                                <option value="Auditor 2" <?= old('auditor_name', $audit['auditor_name'] ?? '') == 'Auditor 2' ? 'selected' : '' ?>>Auditor 2</option>
                                <option value="Auditor 3" <?= old('auditor_name', $audit['auditor_name'] ?? '') == 'Auditor 3' ? 'selected' : '' ?>>Auditor 3</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Audit Category <span class="text-danger">*</span></label>
                        <select name="audit_category" id="audit_category" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php 
                            $cats = isset($db_categories) ? $db_categories : ['ISO Audit', 'HSE Audit', 'GIA Audit', 'Electric Audit', 'Fire Audit', 'Leadership Visit'];
                            foreach ($cats as $c): 
                            ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= old('audit_category', $audit['audit_category'] ?? '') == $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Audit Type <span class="text-danger">*</span></label>
                        <select name="audit_type" id="audit_type" class="form-select" required>
                            <option value="">Select Audit Type</option>
                            <?php if (isset($audit['audit_type'])): ?>
                                <option value="<?= htmlspecialchars($audit['audit_type']) ?>" selected>
                                    <?= htmlspecialchars($audit['audit_type']) ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Audit Doc Type <span class="text-danger">*</span></label>
                        <select name="audit_doc_type" id="audit_doc_type" class="form-select" required>
                            <option value="">Select Doc Type</option>
                            <?php if (isset($audit['audit_doc_type'])): ?>
                                <option value="<?= htmlspecialchars($audit['audit_doc_type']) ?>" selected>
                                    <?= htmlspecialchars($audit['audit_doc_type']) ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <h5 class="mb-3 text-primary border-bottom pb-2">Site & Dates</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Site Name <span class="text-danger">*</span></label>
                        <select name="site_name" id="site_name" class="form-select" required>
                            <option value="">Select Site</option>
                            <?php if (!empty($db_sites)): ?>
                                <?php foreach ($db_sites as $s): ?>
                                    <option value="<?= htmlspecialchars($s['site_name']) ?>" <?= old('site_name', $audit['site_name'] ?? '') == $s['site_name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['site_name']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="Site A" <?= old('site_name', $audit['site_name'] ?? '') == 'Site A' ? 'selected' : '' ?>>Site A</option>
                                <option value="Site B" <?= old('site_name', $audit['site_name'] ?? '') == 'Site B' ? 'selected' : '' ?>>Site B</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Site Type 1</label>
                        <select name="site_type_1" id="site_type_1" class="form-select">
                            <option value="">Select Site Type 1</option>
                            <?php if (!empty($db_site_type_1)): ?>
                                <?php foreach ($db_site_type_1 as $st1): ?>
                                    <option value="<?= htmlspecialchars($st1['site_type_1']) ?>" <?= old('site_type_1', $audit['site_type_1'] ?? '') == $st1['site_type_1'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($st1['site_type_1']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="ISO" <?= old('site_type_1', $audit['site_type_1'] ?? '') == 'ISO' ? 'selected' : '' ?>>ISO</option>
                                <option value="Others" <?= old('site_type_1', $audit['site_type_1'] ?? '') == 'Others' ? 'selected' : '' ?>>Others</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Site Type 2</label>
                        <select name="site_type_2" id="site_type_2" class="form-select">
                            <option value="">Select Site Type 2</option>
                            <?php if (!empty($db_site_type_2)): ?>
                                <?php foreach ($db_site_type_2 as $st2): ?>
                                    <option value="<?= htmlspecialchars($st2['site_type_2']) ?>" <?= old('site_type_2', $audit['site_type_2'] ?? '') == $st2['site_type_2'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($st2['site_type_2']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="FM WH" <?= old('site_type_2', $audit['site_type_2'] ?? '') == 'FM WH' ? 'selected' : '' ?>>FM WH</option>
                                <option value="FM WH-MCF" <?= old('site_type_2', $audit['site_type_2'] ?? '') == 'FM WH-MCF' ? 'selected' : '' ?>>FM WH-MCF</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Site Category</label>
                        <select name="site_category" id="site_category" class="form-select">
                            <option value="">Select Site Category</option>
                            <?php if (!empty($db_site_category)): ?>
                                <?php foreach ($db_site_category as $sc): ?>
                                    <option value="<?= htmlspecialchars($sc['site_category']) ?>" <?= old('site_category', $audit['site_category'] ?? '') == $sc['site_category'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sc['site_category']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="Category-1" <?= old('site_category', $audit['site_category'] ?? '') == 'Category-1' ? 'selected' : '' ?>>Category-1</option>
                                <option value="Category-2" <?= old('site_category', $audit['site_category'] ?? '') == 'Category-2' ? 'selected' : '' ?>>Category-2</option>
                                <option value="Category-3" <?= old('site_category', $audit['site_category'] ?? '') == 'Category-3' ? 'selected' : '' ?>>Category-3</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Mail Received Date</label>
                        <input type="date" name="audit_mail_received_date" class="form-control"
                            value="<?= old('audit_mail_received_date', $audit['audit_mail_received_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Audit Report Date <span class="text-danger">*</span></label>
                        <input type="date" name="audit_report_date" class="form-control" required
                            value="<?= old('audit_report_date', $audit['audit_report_date'] ?? '') ?>">
                    </div>
                </div>

                <h5 class="mb-3 text-primary border-bottom pb-2">Checklist & Observations</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Checklist Category <span class="text-danger">*</span></label>
                        <select name="checklist_category" class="form-select" required>
                            <option value="">Select Checklist Category</option>
                            <?php $chks = ['HSE Audit', 'Electric Audit', 'Fire Audit', 'GIA audit', 'GEMBA', 'Review of Infrastructural points']; ?>
                            <?php foreach ($chks as $c): ?>
                                <option value="<?= $c ?>" <?= old('checklist_category', $audit['checklist_category'] ?? '') == $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NC/Recommendation <span class="text-danger">*</span></label>
                        <select name="nc_recommendation" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="NC" <?= old('nc_recommendation', $audit['nc_recommendation'] ?? '') == 'NC' ? 'selected' : '' ?>>NC</option>
                            <option value="RECOMMENDATION" <?= old('nc_recommendation', $audit['nc_recommendation'] ?? '') == 'RECOMMENDATION' ? 'selected' : '' ?>>RECOMMENDATION</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Observation Point <span class="text-danger">*</span></label>
                        <textarea name="observation_point" class="form-control" rows="3"
                            required><?= old('observation_point', $audit['observation_point'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Risks Details</label>
                        <textarea name="risks_details" class="form-control"
                            rows="3"><?= old('risks_details', $audit['risks_details'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Action Recommendation</label>
                        <textarea name="action_recommendation" class="form-control"
                            rows="2"><?= old('action_recommendation', $audit['action_recommendation'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Specifications</label>
                        <textarea name="specifications" class="form-control"
                            rows="2"><?= old('specifications', $audit['specifications'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">QHSE Remarks</label>
                        <textarea name="qhse_remarks" class="form-control"
                            rows="2"><?= old('qhse_remarks', $audit['qhse_remarks'] ?? '') ?></textarea>
                    </div>
                </div>

                <h5 class="mb-3 text-primary border-bottom pb-2">Risk Assessment</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">UA/UC Type</label>
                        <select name="ua_uc_type" class="form-select">
                            <option value="UA-UC" <?= old('ua_uc_type', $audit['ua_uc_type'] ?? '') == 'UA-UC' ? 'selected' : '' ?>>UA-UC</option>
                            <option value="Quality" <?= old('ua_uc_type', $audit['ua_uc_type'] ?? '') == 'Quality' ? 'selected' : '' ?>>Quality</option>
                            <option value="Others" <?= old('ua_uc_type', $audit['ua_uc_type'] ?? '') == 'Others' ? 'selected' : '' ?>>Others</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Risk Severity <span class="text-danger">*</span></label>
                        <select name="risk_severity" class="form-select" required>
                            <option value="Low" <?= old('risk_severity', $audit['risk_severity'] ?? '') == 'Low' ? 'selected' : '' ?>>Low</option>
                            <option value="Medium" <?= old('risk_severity', $audit['risk_severity'] ?? '') == 'Medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="High" <?= old('risk_severity', $audit['risk_severity'] ?? '') == 'High' ? 'selected' : '' ?>>High</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Risk Probability <span class="text-danger">*</span></label>
                        <select name="risk_probability" class="form-select" required>
                            <option value="Low" <?= old('risk_probability', $audit['risk_probability'] ?? '') == 'Low' ? 'selected' : '' ?>>Low</option>
                            <option value="Medium" <?= old('risk_probability', $audit['risk_probability'] ?? '') == 'Medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="High" <?= old('risk_probability', $audit['risk_probability'] ?? '') == 'High' ? 'selected' : '' ?>>High</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Times Repeated</label>
                        <input type="number" name="times_repeated" class="form-control" min="1"
                            value="<?= old('times_repeated', $audit['times_repeated'] ?? '1') ?>">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Color Code <span class="text-danger">*</span></label>
                        <select name="color_code" class="form-select" required>
                            <option value="Red" <?= old('color_code', $audit['color_code'] ?? '') == 'Red' ? 'selected' : '' ?>>Red</option>
                            <option value="Yellow" <?= old('color_code', $audit['color_code'] ?? '') == 'Yellow' ? 'selected' : '' ?>>Yellow</option>
                            <option value="Black" <?= old('color_code', $audit['color_code'] ?? '') == 'Black' ? 'selected' : '' ?>>Black</option>
                            <!-- <option value="Green" <?= old('color_code', $audit['color_code'] ?? '') == 'Green' ? 'selected' : '' ?>>Green</option> -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cost Type <span class="text-danger">*</span></label>
                        <select name="cost_type" class="form-select" required>
                            <option value="Capex" <?= old('cost_type', $audit['cost_type'] ?? '') == 'Capex' ? 'selected' : '' ?>>Capex</option>
                            <option value="Opex" <?= old('cost_type', $audit['cost_type'] ?? '') == 'Opex' ? 'selected' : '' ?>>Opex</option>
                        </select>
                    </div>
                </div>

                <h5 class="mb-3 text-primary border-bottom pb-2">Action & Scope</h5>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Whose Scope</label>
                        <select name="whose_scope" class="form-select">
                            <option value="Operation" <?= old('whose_scope', $audit['whose_scope'] ?? '') == 'Operation' ? 'selected' : '' ?>>Operation</option>
                            <option value="Landlord Scope" <?= old('whose_scope', $audit['whose_scope'] ?? '') == 'Landlord Scope' ? 'selected' : '' ?>>Landlord Scope</option>
                            <option value="Project Team" <?= old('whose_scope', $audit['whose_scope'] ?? '') == 'Project Team' ? 'selected' : '' ?>>Project Team</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Remarks WM</label>
                        <textarea name="remarks_wm" class="form-control"
                            rows="2"><?= old('remarks_wm', $audit['remarks_wm'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Remarks Corporate</label>
                        <textarea name="remarks_corporate" class="form-control"
                            rows="2"><?= old('remarks_corporate', $audit['remarks_corporate'] ?? '') ?></textarea>
                    </div>
                </div>

                <h5 class="mb-3 text-primary border-bottom pb-2">Closure Details</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Target Date</label>
                        <input type="date" name="target_date" class="form-control"
                            value="<?= old('target_date', $audit['target_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Closed Date</label>
                        <input type="date" name="closed_date" id="closed_date" class="form-control"
                            value="<?= old('closed_date', $audit['closed_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            Point Status <span class="text-danger">*</span>
                        </label>

                        <select name="point_status" id="point_status" class="form-select" required>

                            <option value="">Select Status</option>

                            <option value="Open"
                                <?= old('point_status', $audit['point_status'] ?? '') == 'Open' ? 'selected' : '' ?>>
                                Open
                            </option>
                            
                            <option value="WIP"
                                <?= old('point_status', $audit['point_status'] ?? '') == 'WIP' ? 'selected' : '' ?>>
                                WIP
                            </option>

                            <option value="Closed"
                                <?= old('point_status', $audit['point_status'] ?? '') == 'Closed' ? 'selected' : '' ?>>
                                Closed
                            </option>

                            <option value="Excluded"
                                <?= old('point_status', $audit['point_status'] ?? '') == 'Excluded' ? 'selected' : '' ?>>
                                Excluded
                            </option>

                            <option value="Hold-review with Client"
                                <?= old('point_status', $audit['point_status'] ?? '') == 'Hold-review with Client' ? 'selected' : '' ?>>
                                Hold-review with Client
                            </option>

                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Point Category</label>
                        <select name="point_category" class="form-select">
                            <option value="Open Category" <?= old('point_category', $audit['point_category'] ?? '') == 'Open Category' ? 'selected' : '' ?>>Open Category</option>
                            <option value="Closed Category" <?= old('point_category', $audit['point_category'] ?? '') == 'Closed Category' ? 'selected' : '' ?>>Closed Category</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Closure Status</label>
                        <select name="closure_status" class="form-select">
                            <option value="Delayed" <?= old('closure_status', $audit['closure_status'] ?? '') == 'Delayed' ? 'selected' : '' ?>>Delayed</option>
                            <option value="On-Time" <?= old('closure_status', $audit['closure_status'] ?? '') == 'On-Time' ? 'selected' : '' ?>>On-Time</option>
                            <option value="Closed" <?= old('closure_status', $audit['closure_status'] ?? '') == 'Closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Follow-up By</label>
                        <select name="followup_by" class="form-select">
                            <?php if (!empty($db_followups)): ?>
                                <option value="NA" <?= old('followup_by', $audit['followup_by'] ?? '') == 'NA' ? 'selected' : '' ?>>NA</option>
                                <?php foreach ($db_followups as $f): ?>
                                    <option value="<?= htmlspecialchars($f['designation']) ?>" <?= old('followup_by', $audit['followup_by'] ?? '') == $f['designation'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['designation']) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="NA" <?= old('followup_by', $audit['followup_by'] ?? '') == 'NA' ? 'selected' : '' ?>>NA</option>
                                <option value="Manager" <?= old('followup_by', $audit['followup_by'] ?? '') == 'Manager' ? 'selected' : '' ?>>Manager</option>
                                <option value="Admin" <?= old('followup_by', $audit['followup_by'] ?? '') == 'Admin' ? 'selected' : '' ?>>Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cluster Manager/SPOC <span class="text-danger">*</span></label>
                        <select name="cluster_manager_spoc" class="form-select" required>
                            <option value="">Select Manager</option>
                            <?php if (!empty($db_cluster_managers)): ?>
                                <?php foreach ($db_cluster_managers as $m): ?>
                                    <option value="<?= htmlspecialchars($m['user_name']) ?>" <?= old('cluster_manager_spoc', $audit['cluster_manager_spoc'] ?? '') == $m['user_name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['user_name']) ?> (<?= htmlspecialchars($m['user_cluster']) ?> - <?= htmlspecialchars($m['user_region']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="Manager A" <?= old('cluster_manager_spoc', $audit['cluster_manager_spoc'] ?? '') == 'Manager A' ? 'selected' : '' ?>>Manager A</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Account Manager</label>
                        <select name="account_manager" class="form-select">
                            <option value="">Select Account Manager</option>
                            <?php if (!empty($db_account_managers)): ?>
                                <?php foreach ($db_account_managers as $am): ?>
                                    <option value="<?= htmlspecialchars($am['user_name']) ?>" <?= old('account_manager', $audit['account_manager'] ?? '') == $am['user_name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($am['user_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="Manager B" <?= old('account_manager', $audit['account_manager'] ?? '') == 'Manager B' ? 'selected' : '' ?>>Manager B</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="<?= base_url('gemba-audit') ?>" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Gemba Audit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript_section') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // Initialize Select2
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            $('.select2').select2({
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });
        }

        // Auto-fill logic for Site Name
        const siteSelect = document.getElementById('site_name');
        if (siteSelect) {
            siteSelect.addEventListener('change', function () {
                if (this.value) {
                    fetch('<?= base_url('gemba-audit/getSiteDetails') ?>?site_name=' + encodeURIComponent(this.value))
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.site_name) {
                                let t1 = document.getElementById('site_type_1');
                                let t2 = document.getElementById('site_type_2');
                                let sc = document.getElementById('site_category');

                                function updateDropdown(dropdown, value) {
                                    if (value) {
                                        let optionExists = Array.from(dropdown.options).some(opt => opt.value === value);
                                        if (!optionExists) {
                                            dropdown.add(new Option(value, value));
                                        }
                                        dropdown.value = value;
                                    }
                                }

                                updateDropdown(t1, data.site_type_1);
                                updateDropdown(t2, data.site_type_2);
                                updateDropdown(sc, data.site_category);
                            }
                        });

                    fetch('<?= base_url('Masters/Client/get_site_manager_info') ?>?site_identifier=' + encodeURIComponent(this.value) + '&module_type=GEMBA')
                        .then(res => res.json())
                        .then(mgrData => {
                            if (mgrData && mgrData.status == 1) {
                                let cmSelect = document.getElementsByName('cluster_manager_spoc')[0];
                                let amSelect = document.getElementsByName('account_manager')[0];

                                function selectOrAdd(dropdown, val) {
                                    if (!dropdown || !val) return;
                                    let exists = Array.from(dropdown.options).some(opt => opt.value.toLowerCase().trim() === val.toLowerCase().trim());
                                    if (!exists) {
                                        dropdown.add(new Option(val, val));
                                    }
                                    Array.from(dropdown.options).forEach(opt => {
                                        if (opt.value.toLowerCase().trim() === val.toLowerCase().trim()) {
                                            dropdown.value = opt.value;
                                        }
                                    });
                                }

                                selectOrAdd(cmSelect, mgrData.cluster_manager_name);
                                selectOrAdd(amSelect, mgrData.account_manager_name);
                            }
                        });
                }
            });
        }

        // Cascading dropdown logic
        const categorySelect = document.getElementById('audit_category');
        const typeSelect = document.getElementById('audit_type');
        const docTypeSelect = document.getElementById('audit_doc_type');

        function fetchTypes(category, selectedType = '', selectedDocType = '') {
            if (!category) {
                typeSelect.innerHTML = '<option value="">Select Audit Type</option>';
                docTypeSelect.innerHTML = '<option value="">Select Doc Type</option>';
                return;
            }

            fetch('<?= base_url('gemba-audit/cascading-dropdown') ?>?audit_category=' + encodeURIComponent(category))
                .then(res => res.json())
                .then(data => {
                    let tHTML = '<option value="">Select Audit Type</option>';
                    data.audit_types.forEach(function (t) {
                        let sel = (t == selectedType) ? 'selected' : '';
                        tHTML += `<option value="${t}" ${sel}>${t}</option>`;
                    });
                    typeSelect.innerHTML = tHTML;

                    let dHTML = '<option value="">Select Doc Type</option>';
                    data.doc_types.forEach(function (d) {
                        let sel = (d == selectedDocType) ? 'selected' : '';
                        dHTML += `<option value="${d}" ${sel}>${d}</option>`;
                    });
                    docTypeSelect.innerHTML = dHTML;
                });
        }

        categorySelect.addEventListener('change', function () {
            fetchTypes(this.value);
        });

        // Run on load if category is selected (for Edit)
        if (categorySelect.value) {
            const oldType = '<?= old('audit_type', $audit['audit_type'] ?? '') ?>';
            const oldDocType = '<?= old('audit_doc_type', $audit['audit_doc_type'] ?? '') ?>';
            fetchTypes(categorySelect.value, oldType, oldDocType);
        }

        // Closed Date required validation
        const statusSelect = document.getElementById('point_status');
        const closedDateInput = document.getElementById('closed_date');

        function toggleClosedDate() {
            if (statusSelect.value === 'Closed') {
                closedDateInput.required = true;
            } else {
                closedDateInput.required = false;
            }
        }

        statusSelect.addEventListener('change', toggleClosedDate);
        toggleClosedDate(); // run on load

        // Auto-set Status when Closed Date is selected
        closedDateInput.addEventListener('change', function() {
            if (this.value) {
                statusSelect.value = 'Closed';
                
                // Point Category dropdown
                const pointCategory = document.getElementsByName('point_category')[0];
                if (pointCategory) pointCategory.value = 'Closed Category';
                
                // Closure Status dropdown
                const closureStatus = document.getElementsByName('closure_status')[0];
                if (closureStatus) closureStatus.value = 'Closed';
            }
        });
    });
</script>
<?= $this->endSection() ?>