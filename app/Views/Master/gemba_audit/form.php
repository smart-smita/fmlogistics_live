<?= $this->extend('Layout/base_admin') ?>

<?= $this->section('main_body') ?>
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><?= isset($audit['gemba_sr_no']) ? 'Edit Gemba Audit' : 'Create Gemba Audit' ?></h3>
        </div>
        <div class="card-body">

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                    <i class="fas fa-exclamation-circle fs-4 me-2"></i>
                    <div><?= session()->getFlashdata('error') ?></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data"
action="<?= isset($audit['gemba_sr_no']) 
    ? site_url('gemba-audit/edit/' . $audit['gemba_sr_no']) 
    : site_url('gemba-audit/create') ?>">
                <?= csrf_field() ?>

                <h5 class="mb-3 text-primary border-bottom pb-2">Audit Header</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Unique No</label>
                        <input type="text" name="unique_no" class="form-control bg-light"
                            value="<?= old('unique_no', $audit['unique_no'] ?? '') ?>" readonly>
                    </div>
                    <div class="col-md-3">
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
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <label class="form-label">Audit Category <span class="text-danger">*</span></label>
                        <select name="audit_category" id="audit_category" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php 
                            $currentCat = old('audit_category', $audit['audit_category'] ?? '');
                            $cats = isset($db_categories) ? $db_categories : ['ISO Audit', 'HSE Audit', 'GIA Audit', 'Electric Audit', 'Fire Audit', 'Leadership Visit'];
                            if ($currentCat !== '' && !in_array($currentCat, $cats)): ?>
                                <option value="<?= htmlspecialchars($currentCat) ?>" selected><?= htmlspecialchars($currentCat) ?></option>
                            <?php endif; ?>
                            <?php foreach ($cats as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= $currentCat == $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                            <option value="__other__">Other (Specify)</option>
                        </select>
                        <div id="audit_category_other_wrap" class="mt-2 d-none">
                            <input type="text" name="audit_category_other" id="audit_category_other" class="form-control" placeholder="Specify New Audit Category">
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Audit Type <span class="text-danger">*</span></label>
                        <select name="audit_type" id="audit_type" class="form-select" required>
                            <option value="">Select Audit Type</option>
                            <?php if (!empty($audit['audit_type'])): ?>
                                <option value="<?= htmlspecialchars($audit['audit_type']) ?>" selected>
                                    <?= htmlspecialchars($audit['audit_type']) ?></option>
                            <?php endif; ?>
                            <option value="__other__">Other (Specify)</option>
                        </select>
                        <div id="audit_type_other_wrap" class="mt-2 d-none">
                            <input type="text" name="audit_type_other" id="audit_type_other" class="form-control" placeholder="Specify New Audit Type">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Audit Doc Type <span class="text-danger">*</span></label>
                        <select name="audit_doc_type" id="audit_doc_type" class="form-select" required>
                            <option value="">Select Doc Type</option>
                            <?php if (!empty($audit['audit_doc_type'])): ?>
                                <option value="<?= htmlspecialchars($audit['audit_doc_type']) ?>" selected>
                                    <?= htmlspecialchars($audit['audit_doc_type']) ?></option>
                            <?php endif; ?>
                            <option value="__other__">Other (Specify)</option>
                        </select>
                        <div id="audit_doc_type_other_wrap" class="mt-2 d-none">
                            <input type="text" name="audit_doc_type_other" id="audit_doc_type_other" class="form-control" placeholder="Specify New Doc Type">
                        </div>
                    </div>
                </div>

                <h5 class="mb-3 text-primary border-bottom pb-2">Site & Dates</h5>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Site Name <span class="text-danger">*</span></label>
                        <select name="site_name" id="site_name" class="form-select" required>
                            <option value="">Select Site</option>
                            <?php 
                            $currentSite = old('site_name', $audit['site_name'] ?? '');
                            $siteNamesInList = !empty($db_sites) ? array_column($db_sites, 'site_name') : [];
                            if ($currentSite !== '' && !in_array($currentSite, $siteNamesInList)): ?>
                                <option value="<?= htmlspecialchars($currentSite) ?>" selected><?= htmlspecialchars($currentSite) ?></option>
                            <?php endif; ?>
                            <?php if (!empty($db_sites)): ?>
                                <?php foreach ($db_sites as $s): ?>
                                    <option value="<?= htmlspecialchars($s['site_name']) ?>" <?= $currentSite == $s['site_name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['site_name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Site Type 1</label>
                        <select name="site_type_1" id="site_type_1" class="form-select">
                            <option value="">Select Site Type 1</option>
                            <?php 
                            $currentSt1 = old('site_type_1', $audit['site_type_1'] ?? '');
                            $st1List = !empty($db_site_type_1) ? array_column($db_site_type_1, 'site_type_1') : ['ISO', 'Others'];
                            if ($currentSt1 !== '' && !in_array($currentSt1, $st1List)): ?>
                                <option value="<?= htmlspecialchars($currentSt1) ?>" selected><?= htmlspecialchars($currentSt1) ?></option>
                            <?php endif; ?>
                            <?php foreach ($st1List as $st1): ?>
                                <option value="<?= htmlspecialchars($st1) ?>" <?= $currentSt1 == $st1 ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($st1) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Site Type 2</label>
                        <select name="site_type_2" id="site_type_2" class="form-select">
                            <option value="">Select Site Type 2</option>
                            <?php 
                            $currentSt2 = old('site_type_2', $audit['site_type_2'] ?? '');
                            $st2List = !empty($db_site_type_2) ? array_column($db_site_type_2, 'site_type_2') : ['FM WH', 'FM WH-MCF', 'Client WH', 'Inplant'];
                            if ($currentSt2 !== '' && !in_array($currentSt2, $st2List)): ?>
                                <option value="<?= htmlspecialchars($currentSt2) ?>" selected><?= htmlspecialchars($currentSt2) ?></option>
                            <?php endif; ?>
                            <?php foreach ($st2List as $st2): ?>
                                <option value="<?= htmlspecialchars($st2) ?>" <?= $currentSt2 == $st2 ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($st2) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Site Category</label>
                        <select name="site_category" id="site_category" class="form-select">
                            <option value="">Select Site Category</option>
                            <?php 
                            $currentSc = old('site_category', $audit['site_category'] ?? '');
                            $scList = !empty($db_site_category) ? array_column($db_site_category, 'site_category') : ['Category-1', 'Category-2', 'Category-3'];
                            if ($currentSc !== '' && !in_array($currentSc, $scList)): ?>
                                <option value="<?= htmlspecialchars($currentSc) ?>" selected><?= htmlspecialchars($currentSc) ?></option>
                            <?php endif; ?>
                            <?php foreach ($scList as $sc): ?>
                                <option value="<?= htmlspecialchars($sc) ?>" <?= $currentSc == $sc ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sc) ?></option>
                            <?php endforeach; ?>
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
                        <select name="checklist_category" id="checklist_category" class="form-select" required>
                            <option value="">Select Checklist Category</option>
                            <?php 
                            $currentChk = old('checklist_category', $audit['checklist_category'] ?? '');
                            $chks = isset($db_checklist_categories) ? $db_checklist_categories : ['HSE Audit', 'Electric Audit', 'Fire Audit', 'GIA audit', 'GEMBA', 'Review of Infrastructural points'];
                            if ($currentChk !== '' && !in_array($currentChk, $chks)): ?>
                                <option value="<?= htmlspecialchars($currentChk) ?>" selected><?= htmlspecialchars($currentChk) ?></option>
                            <?php endif; ?>
                            <?php foreach ($chks as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= $currentChk == $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                            <option value="__other__">Other (Specify)</option>
                        </select>
                        <div id="checklist_category_other_wrap" class="mt-2 d-none">
                            <input type="text" name="checklist_category_other" id="checklist_category_other" class="form-control" placeholder="Specify Checklist Category">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NC/Recommendation <span class="text-danger">*</span></label>
                        <select name="nc_recommendation" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="NC" <?= old('nc_recommendation', $audit['nc_recommendation'] ?? '') == 'NC' ? 'selected' : '' ?>>NC</option>
                            <option value="RECOMMENDATION" <?= old('nc_recommendation', $audit['nc_recommendation'] ?? '') == 'RECOMMENDATION' ? 'selected' : '' ?>>RECOMMENDATION</option>
                            <option value="Point Closed" <?= old('nc_recommendation', $audit['nc_recommendation'] ?? '') == 'Point Closed' ? 'selected' : '' ?>>Point Closed</option>
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

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label class="form-label">Attachment / Photo (Optional)</label>
                        <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx">
                        <small class="text-muted">Allowed formats: Images (JPG, PNG, WebP), PDF, Word, Excel. Max size: 10MB.</small>
                        <?php if (!empty($audit['attachment'])): 
                            $rawAtt = $audit['attachment'];
                            if (strpos($rawAtt, 'uploads/') === 0) {
                                $attUrl = base_url($rawAtt);
                                $displayFileName = basename($rawAtt);
                            } else {
                                $attDir = 'uploads/gemba_audit/';
                                if (!file_exists(FCPATH . 'uploads/gemba_audit/' . $rawAtt) && file_exists(FCPATH . 'uploads/nc_photo/' . $rawAtt)) {
                                    $attDir = 'uploads/nc_photo/';
                                }
                                $attUrl = base_url($attDir . $rawAtt);
                                $displayFileName = $rawAtt;
                            }
                            $ext = strtolower(pathinfo($displayFileName, PATHINFO_EXTENSION));
                        ?>
                            <div class="mt-2 p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fas fa-paperclip text-primary me-2"></i>
                                    <strong>Current Attachment:</strong> 
                                    <a href="<?= $attUrl ?>" target="_blank" class="text-decoration-underline ms-1">
                                        <?= htmlspecialchars($rawAtt) ?>
                                    </a>
                                </div>
                                <div class="d-flex align-items-center">
                                    <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                        <a href="<?= $attUrl ?>" target="_blank" class="me-2">
                                            <img src="<?= $attUrl ?>" alt="Thumbnail" class="img-thumbnail" style="max-height: 40px;">
                                        </a>
                                    <?php endif; ?>
                                    <span class="badge bg-secondary">Leave empty to keep existing</span>
                                </div>
                            </div>
                        <?php endif; ?>
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
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cost Type <span class="text-danger">*</span></label>
                        <select name="cost_type" class="form-select" required>
                            <?php 
                            $currentCost = old('cost_type', $audit['cost_type'] ?? 'Capex');
                            $costList = isset($db_cost_types) ? $db_cost_types : ['Capex', 'Opex', 'Operational Action'];
                            foreach ($costList as $ct): ?>
                                <option value="<?= htmlspecialchars($ct) ?>" <?= strtolower($currentCost) == strtolower($ct) ? 'selected' : '' ?>><?= htmlspecialchars($ct) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <h5 class="mb-3 text-primary border-bottom pb-2">Action & Scope</h5>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Whose Scope</label>
                        <select name="whose_scope" id="whose_scope" class="form-select">
                            <option value="">Select Scope</option>
                            <?php 
                            $currentScope = old('whose_scope', $audit['whose_scope'] ?? '');
                            $scopes = isset($db_whose_scope) ? $db_whose_scope : ['Operation', 'Landlord Scope', 'Project Team', 'FM Scope', 'Client Scope'];
                            if ($currentScope !== '' && !in_array($currentScope, $scopes)): ?>
                                <option value="<?= htmlspecialchars($currentScope) ?>" selected><?= htmlspecialchars($currentScope) ?></option>
                            <?php endif; ?>
                            <?php foreach ($scopes as $sc): ?>
                                <option value="<?= htmlspecialchars($sc) ?>" <?= $currentScope == $sc ? 'selected' : '' ?>><?= htmlspecialchars($sc) ?></option>
                            <?php endforeach; ?>
                            <option value="__other__">Other (Specify)</option>
                        </select>
                        <div id="whose_scope_other_wrap" class="mt-2 d-none">
                            <input type="text" name="whose_scope_other" id="whose_scope_other" class="form-control" placeholder="Specify Whose Scope">
                        </div>
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

                <?php 
                $isPointClosed = (!empty($audit['gemba_sr_no']) && ($audit['point_status'] ?? '') === 'Closed'); 
                ?>
                <h5 class="mb-3 text-primary border-bottom pb-2 d-flex align-items-center justify-content-between">
                    <span>Closure Details</span>
                    <?php if ($isPointClosed): ?>
                        <span class="badge bg-success"><i class="fas fa-lock me-1"></i> Closed (Locked)</span>
                    <?php endif; ?>
                </h5>
                <?php if ($isPointClosed): ?>
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="fas fa-lock me-1"></i> <strong>Audit point is Closed:</strong> Status cannot be changed back to Open, and Closure details, Cluster Manager, and Account Manager are locked.
                    </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Target Date</label>
                        <input type="date" name="target_date" class="form-control"
                            value="<?= old('target_date', $audit['target_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Closed Date</label>
                        <input type="date" name="closed_date" id="closed_date" class="form-control"
                            value="<?= old('closed_date', $audit['closed_date'] ?? '') ?>" <?= $isPointClosed ? 'disabled' : '' ?>>
                        <?php if ($isPointClosed): ?>
                            <input type="hidden" name="closed_date" value="<?= htmlspecialchars($audit['closed_date'] ?? '') ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            Point Status <span class="text-danger">*</span>
                        </label>

                        <select name="point_status" id="point_status" class="form-select" required <?= $isPointClosed ? 'disabled' : '' ?>>
                            <?php if (!$isPointClosed): ?>
                                <option value="">Select Status</option>
                                <option value="Open"
                                    <?= old('point_status', $audit['point_status'] ?? '') == 'Open' ? 'selected' : '' ?>>
                                    Open
                                </option>
                                <option value="WIP"
                                    <?= old('point_status', $audit['point_status'] ?? '') == 'WIP' ? 'selected' : '' ?>>
                                    WIP
                                </option>
                            <?php endif; ?>

                            <option value="Closed"
                                <?= old('point_status', $audit['point_status'] ?? '') == 'Closed' ? 'selected' : '' ?>>
                                Closed
                            </option>

                            <?php if (!$isPointClosed): ?>
                                <option value="Excluded"
                                    <?= old('point_status', $audit['point_status'] ?? '') == 'Excluded' ? 'selected' : '' ?>>
                                    Excluded
                                </option>

                                <option value="Hold-review with Client"
                                    <?= old('point_status', $audit['point_status'] ?? '') == 'Hold-review with Client' ? 'selected' : '' ?>>
                                    Hold-review with Client
                                </option>
                            <?php endif; ?>
                        </select>
                        <?php if ($isPointClosed): ?>
                            <input type="hidden" name="point_status" value="Closed">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Point Category</label>
                        <select name="point_category" class="form-select" <?= $isPointClosed ? 'disabled' : '' ?>>
                            <option value="Open Category" <?= old('point_category', $audit['point_category'] ?? '') == 'Open Category' ? 'selected' : '' ?>>Open Category</option>
                            <option value="Closed Category" <?= old('point_category', $audit['point_category'] ?? '') == 'Closed Category' ? 'selected' : '' ?>>Closed Category</option>
                        </select>
                        <?php if ($isPointClosed): ?>
                            <input type="hidden" name="point_category" value="<?= htmlspecialchars($audit['point_category'] ?? 'Closed Category') ?>">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Closure Status</label>
                        <select name="closure_status" class="form-select" <?= $isPointClosed ? 'disabled' : '' ?>>
                            <option value="Delayed" <?= old('closure_status', $audit['closure_status'] ?? '') == 'Delayed' ? 'selected' : '' ?>>Delayed</option>
                            <option value="On-Time" <?= old('closure_status', $audit['closure_status'] ?? '') == 'On-Time' ? 'selected' : '' ?>>On-Time</option>
                            <option value="Closed" <?= old('closure_status', $audit['closure_status'] ?? '') == 'Closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                        <?php if ($isPointClosed): ?>
                            <input type="hidden" name="closure_status" value="<?= htmlspecialchars($audit['closure_status'] ?? 'Closed') ?>">
                        <?php endif; ?>
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
                        <select name="cluster_manager_spoc" id="cluster_manager_spoc" class="form-select" required <?= $isPointClosed ? 'disabled' : '' ?>>
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
                        <?php if ($isPointClosed): ?>
                            <input type="hidden" name="cluster_manager_spoc" value="<?= htmlspecialchars($audit['cluster_manager_spoc'] ?? '') ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Account Manager</label>
                        <select name="account_manager" id="account_manager" class="form-select" <?= $isPointClosed ? 'disabled' : '' ?>>
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
                        <?php if ($isPointClosed): ?>
                            <input type="hidden" name="account_manager" value="<?= htmlspecialchars($audit['account_manager'] ?? '') ?>">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="<?= site_url('gemba-audit') ?>" class="btn btn-secondary me-2">Cancel</a>
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
        
        // Initialize Select2 if present
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            $('.select2').select2({
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });
        }

        // Generic "Other" toggle function
        function setupOtherToggle(selectId, wrapId, inputId) {
            const sel = document.getElementById(selectId);
            const wrap = document.getElementById(wrapId);
            const inp = document.getElementById(inputId);
            if (!sel || !wrap || !inp) return;

            function checkToggle() {
                if (sel.value === '__other__') {
                    wrap.classList.remove('d-none');
                    inp.required = true;
                } else {
                    wrap.classList.add('d-none');
                    inp.required = false;
                }
            }

            sel.addEventListener('change', checkToggle);
            checkToggle();
        }

        setupOtherToggle('audit_category', 'audit_category_other_wrap', 'audit_category_other');
        setupOtherToggle('audit_type', 'audit_type_other_wrap', 'audit_type_other');
        setupOtherToggle('audit_doc_type', 'audit_doc_type_other_wrap', 'audit_doc_type_other');
        setupOtherToggle('checklist_category', 'checklist_category_other_wrap', 'checklist_category_other');
        setupOtherToggle('whose_scope', 'whose_scope_other_wrap', 'whose_scope_other');

        // Helper to update dropdown value safely, adding option if not present
        function updateDropdown(dropdown, value) {
            if (!dropdown || !value) return;
            let optionExists = Array.from(dropdown.options).some(opt => opt.value === value);
            if (!optionExists) {
                dropdown.add(new Option(value, value));
            }
            dropdown.value = value;
        }

        // Auto-fill logic for Site Name
        const siteSelect = document.getElementById('site_name');
        if (siteSelect) {
            siteSelect.addEventListener('change', function () {
                if (this.value && this.value !== '__other__') {
                    fetch('<?= site_url('gemba-audit/getSiteDetails') ?>?site_name=' + encodeURIComponent(this.value), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                        .then(res => {
                            if (!res.ok) throw new Error('HTTP Status: ' + res.status);
                            return res.json();
                        })
                        .then(data => {
                            if (data && data.site_name) {
                                let t1 = document.getElementById('site_type_1');
                                let t2 = document.getElementById('site_type_2');
                                let sc = document.getElementById('site_category');
                                let reg = document.querySelector('select[name="region"]');
                                let cm = document.querySelector('select[name="cluster_manager_spoc"]');
                                let am = document.querySelector('select[name="account_manager"]');

                                if (data.site_type_1) updateDropdown(t1, data.site_type_1);
                                if (data.site_type_2) updateDropdown(t2, data.site_type_2);
                                if (data.site_category) updateDropdown(sc, data.site_category);
                                if (data.region && reg) updateDropdown(reg, data.region);
                                if (data.cluster_manager_spoc && cm && !cm.disabled) updateDropdown(cm, data.cluster_manager_spoc);
                                if (data.account_manager && am && !am.disabled) updateDropdown(am, data.account_manager);
                            }
                        })
                        .catch(err => console.error('Site details fetch error:', err));
                }
            });
        }

        // Cascading dropdown logic
        const categorySelect = document.getElementById('audit_category');
        const typeSelect = document.getElementById('audit_type');
        const docTypeSelect = document.getElementById('audit_doc_type');

        function fetchTypes(category, selectedType = '', selectedDocType = '') {
            if (!category || category === '__other__') {
                typeSelect.innerHTML = '<option value="">Select Audit Type</option><option value="__other__">Other (Specify)</option>';
                docTypeSelect.innerHTML = '<option value="">Select Doc Type</option><option value="__other__">Other (Specify)</option>';
                if (selectedType) {
                    typeSelect.add(new Option(selectedType, selectedType, true, true), typeSelect.options[1]);
                }
                if (selectedDocType) {
                    docTypeSelect.add(new Option(selectedDocType, selectedDocType, true, true), docTypeSelect.options[1]);
                }
                const typeWrap = document.getElementById('audit_type_other_wrap');
                const docWrap = document.getElementById('audit_doc_type_other_wrap');
                if (typeWrap) typeWrap.classList.add('d-none');
                if (docWrap) docWrap.classList.add('d-none');
                return;
            }

            fetch('<?= site_url('gemba-audit/cascading-dropdown') ?>?audit_category=' + encodeURIComponent(category), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(res => {
                    if (!res.ok) throw new Error('HTTP Status: ' + res.status);
                    return res.json();
                })
                .then(data => {
                    let tHTML = '<option value="">Select Audit Type</option>';
                    let hasSelectedType = false;
                    if (data.audit_types && data.audit_types.length > 0) {
                        data.audit_types.forEach(function (t) {
                            let sel = (t == selectedType) ? 'selected' : '';
                            if (sel) hasSelectedType = true;
                            tHTML += `<option value="${t}" ${sel}>${t}</option>`;
                        });
                    }
                    if (selectedType && !hasSelectedType && selectedType !== '__other__') {
                        tHTML += `<option value="${selectedType}" selected>${selectedType}</option>`;
                    }
                    tHTML += '<option value="__other__">Other (Specify)</option>';
                    typeSelect.innerHTML = tHTML;

                    let dHTML = '<option value="">Select Doc Type</option>';
                    let hasSelectedDoc = false;
                    if (data.doc_types && data.doc_types.length > 0) {
                        data.doc_types.forEach(function (d) {
                            let sel = (d == selectedDocType) ? 'selected' : '';
                            if (sel) hasSelectedDoc = true;
                            dHTML += `<option value="${d}" ${sel}>${d}</option>`;
                        });
                    }
                    if (selectedDocType && !hasSelectedDoc && selectedDocType !== '__other__') {
                        dHTML += `<option value="${selectedDocType}" selected>${selectedDocType}</option>`;
                    }
                    dHTML += '<option value="__other__">Other (Specify)</option>';
                    docTypeSelect.innerHTML = dHTML;

                    const typeWrap = document.getElementById('audit_type_other_wrap');
                    const docWrap = document.getElementById('audit_doc_type_other_wrap');
                    if (typeSelect.value === '__other__' && typeWrap) {
                        typeWrap.classList.remove('d-none');
                    } else if (typeWrap) {
                        typeWrap.classList.add('d-none');
                    }
                    if (docTypeSelect.value === '__other__' && docWrap) {
                        docWrap.classList.remove('d-none');
                    } else if (docWrap) {
                        docWrap.classList.add('d-none');
                    }
                })
                .catch(err => console.error('Cascading dropdown fetch error:', err));
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