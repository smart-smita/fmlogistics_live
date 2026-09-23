<?php $this->extend("Layout/base_admin"); ?>
<?php 
$this->section("breadcrumb_title_li");
?>
<style>
    label {
        display: inline-block;
        padding-left: 10px !important;
        padding-right: 10px !important;
    }
    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
    
    #result {
        background: darkgrey;
    }
    #resultAuditor {
        background: darkgrey;
        position:absolute;
    }
    .result-item {
        padding: 8px;
        cursor: pointer;
        border-bottom: 1px solid #ddd;
    }
    .result-item:hover {
        background-color: #f0f0f0;
    }
    .result-item1 {
        padding: 8px;
        cursor: pointer;
        border-bottom: 1px solid #ddd;
    }
    .result-item1:hover {
        background-color: #f0f0f0;
    }
    /* Select2 dropdown styling */
    span.select2-selection.select2-selection--single.form-select.js-example-basic-single {
        height: 38px !important;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
    }
    
    /* === NEW: sticky thead for HSE Audit table === */
    .audit-table-wrapper {
        max-height: 980px;       /* adjust height as you like */
        overflow-y: auto;
        overflow-x: auto;        /* keep horizontal scroll if needed */
    }

    .audit-table-wrapper table {
        margin-bottom: 0;        /* prevent extra space at bottom inside scroll */
    }

    .audit-table-wrapper thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background-color: #f5f8fa;   /* match theme */
    }
</style>

<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>

<form action="<?=$action?>" method="post" enctype="multipart/form-data" onsubmit="return validateHSEAudit()">
    <?= csrf_field() ?>
    <?php
    // Build prefill map for reaudit (existing HSE details)
    $prefillMap = [];
    if (isset($audit_details) && is_array($audit_details)) {
        foreach ($audit_details as $d) {
            // key by category+question
            $keyIndex = md5((string)($d['question_name'] ?? '').'|'.(string)($d['audit_question'] ?? ''));
            $prefillMap[$keyIndex] = [
                'client_leased' => $d['client_leased'] ?? '',
                'inplant'       => $d['inplant'] ?? '',
                'fm_leased'     => $d['fm_leased'] ?? '',
                'remark'        => $d['remark'] ?? '',
                'attachment'    => $d['attachment'] ?? '',
            ];
        }
    }
    ?>
    
    <div class="col-md-12 mb-5">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 ">
                        <h4 class="card-title">Auditor Details</h4>
                        <blockquote class="blockquote">
                            <table class="table table-hover">
                                <tbody>
                                    <?php 
                                    $isReaudit = isset($details['hse_audit_id']) && !empty($details['hse_audit_id']);
                                    ?>
                                    <tr>
                                        <th class="table-secondary">Audit No.</th>
                                        <td>
                                            <input type="text" id="audit_no" name="audit_no" value="<?= isset($details['audit_no']) ? $details['audit_no'] : '' ?>" readonly />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Audit Type Name</th>
                                        <td>
                                            <input type="text" name="audit_name" value="<?= isset($details['audit_name']) ? htmlspecialchars($details['audit_name']) : '' ?>" readonly />    
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Auditor Name</th>
                                        <td>
                                            <?php if($isReaudit) { ?>
                                                <input type="text" class="form-control" value="<?= isset($details['auditor_name']) ? $details['auditor_name'] : '' ?>" readonly class="form-control-disabled">
                                                <input type="hidden" name="auditor_name" value="<?= isset($details['auditor_name']) ? $details['auditor_name'] : '' ?>">
                                            <?php } else { ?>
                                                <select class="form-select js-example-basic-single" id="auditor_name" name="auditor_name">
                                                    <option value="">Select Auditor</option>
                                                    <?php if(isset($auditors) && is_array($auditors)) {
                                                        foreach($auditors as $auditor) { ?>
                                                            <option value="<?= $auditor['user_name'] ?>" <?php if(isset($details['auditor_name']) && $details['auditor_name'] == $auditor['user_name']) echo 'selected'; ?>><?= $auditor['user_name'] ?></option>
                                                    <?php } } ?>
                                                </select>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Auditee Name</th>
                                        <td>
                                            <?php if($isReaudit) { ?>
                                                <input type="text" class="form-control" value="<?= isset($details['auditee_name']) ? $details['auditee_name'] : '' ?>" readonly class="form-control-disabled">
                                                <input type="hidden" name="auditee_name" value="<?= isset($details['auditee_name']) ? $details['auditee_name'] : '' ?>">
                                            <?php } else { ?>
                                                <input type="text" name="auditee_name" value="<?= isset($details['auditee_name']) ? $details['auditee_name'] : '' ?>" />
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Audit Date</th>
                                        <td>
                                            <?php if($isReaudit) { ?>
                                                <input type="text" class="form-control" value="<?= isset($details['audit_date']) ? $details['audit_date'] : '' ?>" readonly class="form-control-disabled">
                                                <input type="hidden" name="audit_date" value="<?= isset($details['audit_date']) ? $details['audit_date'] : '' ?>">
                                            <?php } else { ?>
                                                <input type="date" name="audit_date" value="<?= isset($details['audit_date']) ? $details['audit_date'] : '' ?>" />
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>			
                        </blockquote>
                    </div>
                    <div class="col-md-6">
                        <h4 class="card-title">Client Details</h4>
                        <blockquote class="blockquote">
                            <table class="table table-hover">
                                <tbody>
                                    <tr>
                                        <th class="table-secondary">Client Name</th>
                                        <td>
                                            <input type="hidden" name="audit_template_id" value="<?= isset($details['hse_audit_id']) ? $details['hse_audit_id'] : '' ?>" />
                                            <?php if($isReaudit) { ?>
                                                <input type="text" class="form-control" value="<?= isset($details['client_name']) ? $details['client_name'] : '' ?>" readonly class="form-control-disabled">
                                                <input type="hidden" id="client_name" name="client_name" value="<?= isset($details['client_name']) ? $details['client_name'] : '' ?>" />
                                                <input type="hidden" id="client_id" name="client_id" value="<?= isset($details['client_id']) ? $details['client_id'] : '' ?>" />
                                            <?php } else { ?>
                                                <select class="form-select js-example-basic-single" id="client_id" name="client_id" onchange="clientOnselect()">
                                                    <option value="">Select Client</option>
                                                    <?php if(isset($client)) {
                                                        foreach($client as $clnt) { 
                                                            $selected = (isset($details['client_name']) && $details['client_name'] == $clnt['client_name']) ? 'selected' : '';
                                                        ?>
                                                            <option value="<?= $clnt['client_id']; ?>" <?= $selected ?> data-client-name="<?= htmlspecialchars($clnt['client_name']) ?>"><?= $clnt['client_name']; ?></option>
                                                    <?php } } ?>
                                                </select>
                                                <input type="hidden" id="client_name" name="client_name" value="<?= isset($details['client_name']) ? $details['client_name'] : '' ?>" />
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Region</th>
                                        <td>
                                            <?php if($isReaudit) { ?>
                                                <input type="text" class="form-control form-control-disabled" readonly  value="<?= isset($details['region']) ? $details['region'] : '' ?>">
                                                <input type="hidden" name="region" id="region_hidden" value="<?= isset($details['region']) ? $details['region'] : '' ?>">
                                            <?php } else { ?>

                                                <select class="form-select form-select-solid js-example-basic-single" name="region" id="region_dropdown" style="display: block;">
                                                    <option value="">Select Region</option>
                                                    <?php if(isset($region)) {
                                                        foreach($region as $reg) { 
                                                            $selected = (isset($details['region']) && $details['region'] == $reg['region_name']) ? 'selected' : '';
                                                        ?>
                                                            <option value="<?= $reg['region_name']; ?>" <?= $selected ?>><?= $reg['region_name']; ?></option>
                                                    <?php } } ?>
                                                </select>
                                                <input type="text" id="region_locked" class="form-control" readonly style="background-color: #f5f5f5; cursor: not-allowed; display: none;" value="">
                                                <input type="hidden" name="region" id="region_hidden" value="<?= isset($details['region']) ? $details['region'] : '' ?>">
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Report Date</th>
                                        <td>
                                            <?php if($isReaudit) { ?>
                                                <input type="text" class="form-control" value="<?= isset($details['report_date']) ? $details['report_date'] : (isset($details['audit_date']) ? $details['audit_date'] : '') ?>" readonly class="form-control-disabled">
                                                <input type="hidden" name="report_date" value="<?= isset($details['report_date']) ? $details['report_date'] : (isset($details['audit_date']) ? $details['audit_date'] : '') ?>">
                                            <?php } else { ?>
                                                <input type="date" name="report_date" value="<?= isset($details['audit_date']) ? $details['audit_date'] : '' ?>" />
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Score</th>
                                        <td>
                                            <span id="scoreDisplay">0</span> / <span id="finalScoreDisplay">100</span>
                                            <input type="hidden" id="finalScoreDisplayText" name="score" value="">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Site Category</th>
                                        <td>
                                            <select class="form-select form-select-solid js-example-basic-single" name="perform_audit_by" id="perform_audit_by" onchange="toggleAuditTable()">
                                                <option value="">Please Select</option>
                                                <option value="client_leased" <?= (isset($details['perform_audit_by']) && $details['perform_audit_by'] == 'client_leased') ? 'selected' : '' ?>>Client Leased</option>
                                                <option value="inplant" <?= (isset($details['perform_audit_by']) && $details['perform_audit_by'] == 'inplant') ? 'selected' : '' ?>>Inplant</option>
                                                <option value="fm_leased" <?= (isset($details['perform_audit_by']) && $details['perform_audit_by'] == 'fm_leased') ? 'selected' : '' ?>>FM Leased</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>			
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    // Calculate categories early for Summary Card
    // Calculate categories early for Summary Card
    $categories = [];
    $categorySrMap = [];
    if (isset($audit_data) && is_array($audit_data)) {
        foreach($audit_data as $row) {
            $qName = $row['question_name'] ?? '';
            $catId = $row['audit_category_id'] ?? '';
            
            // Map category name to its ID (A, B, C) if it's a header row
            if (strlen(trim($catId)) === 1 && ctype_alpha($catId)) {
                $categorySrMap[$qName] = $catId;
            }
            
            if (!empty($qName) && !in_array($qName, $categories)) {
                $categories[] = $qName;
            }
        }
        
        // Sort categories alphabetically by their mapped ID (Sr No)
        usort($categories, function($a, $b) use ($categorySrMap) {
            $idA = $categorySrMap[$a] ?? '';
            $idB = $categorySrMap[$b] ?? '';
            return strcmp($idA, $idB);
        });
    }
    ?>

    <!-- NEW: Realtime Summary Card -->
    <div class="card mb-5" id="realtimeSummaryCard" style="display:none; border: 1px solid #ddd;">
        <div class="card-header" style="background-color: #f8f9fa;">
            <h3 class="card-title m-0">Realtime Audit Summary</h3>
        </div>
        <div class="card-body p-3">
            <div class="row">
                <!-- Overall Summary -->
                <div class="col-md-5">
                    <h5 class="text-center mb-2">Overall Summary of Compliance</h5>
                    <table class="table table-bordered table-sm text-center" id="summaryOverallTable" style="font-size: 13px;">
                        <thead class="table-primary">
                            <tr>
                                <th style="background-color: #1e1c77; color: white;">Category</th>
                                <th style="background-color: #1e1c77; color: white;">Total Selected</th>
                                <th style="background-color: #1e1c77; color: white;">YES</th>
                                <th style="background-color: #1e1c77; color: white;">NO</th>
                                <th style="background-color: #1e1c77; color: white;">NA</th>
                                <th style="background-color: #1e1c77; color: white;">Score %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr id="row_summary_client_leased">
                                <td class="fw-bold">CLIENT LEASED</td>
                                <td class="s-total-selected">0</td>
                                <td class="s-yes">0</td>
                                <td class="s-no">0</td>
                                <td class="s-na">0</td>
                                <td class="s-score fw-bold">0%</td>
                            </tr>
                            <tr id="row_summary_inplant">
                                <td class="fw-bold">INPLANT</td>
                                <td class="s-total-selected">0</td>
                                <td class="s-yes">0</td>
                                <td class="s-no">0</td>
                                <td class="s-na">0</td>
                                <td class="s-score fw-bold">0%</td>
                            </tr>
                            <tr id="row_summary_fm_leased">
                                <td class="fw-bold">FM LEASED</td>
                                <td class="s-total-selected">0</td>
                                <td class="s-yes">0</td>
                                <td class="s-no">0</td>
                                <td class="s-na">0</td>
                                <td class="s-score fw-bold">0%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Category Wise -->
                <div class="col-md-7">
                    <h5 class="text-center mb-2">Category-wise Header Score Summary</h5>
                    <div style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-bordered table-sm text-center" id="summaryCategoryTable" style="font-size: 13px;">
                            <thead class="table-primary" style="position: sticky; top: 0; z-index: 2;">
                                <tr>
                                    <th style="background-color: #1e1c77; color: white;">Sr No</th>
                                    <th style="background-color: #1e1c77; color: white;">Category</th>
                                    <th style="background-color: #1e1c77; color: white;">Client %</th>
                                    <th style="background-color: #1e1c77; color: white;">Inplant %</th>
                                    <th style="background-color: #1e1c77; color: white;">FM %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $srChar = 'A';
                                if(!empty($categories)) {
                                    foreach($categories as $cat) { 
                                        $displaySr = $categorySrMap[$cat] ?? $srChar++;
                                    ?>
                                        <tr data-cat-name="<?= htmlspecialchars($cat) ?>">
                                            <td><?= $displaySr ?></td>
                                            <td class="text-start"><?= htmlspecialchars($cat) ?></td>
                                            <td class="c-client">0%</td>
                                            <td class="c-inplant">0%</td>
                                            <td class="c-fm">0%</td>
                                        </tr>
                                    <?php } 
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="position: sticky; top: 0; z-index: 999; background: #fff; border-bottom: 1px solid #eff2f5;">
            <div class="d-flex align-items-center" style="gap:10px; flex-wrap: wrap;">
                <label class="mb-0">Filter:</label>
                <select id="answerFilterHSE" class="form-select form-select-sm js-example-basic-single" style="width:auto;">
                    <option value="all">All</option>
                    <option value="attempted">Attempted</option>
                    <option value="not_attempted">Not Attempted</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                    <option value="na">NA</option>
                </select>
                <label class="mb-0">Category:</label>
                <select id="categoryFilterHSE" class="form-select form-select-sm js-example-basic-single" style="width:auto;">
                    <option value="all">All Categories</option>
                    <?php 
                    // Reuse calculated categories
                    foreach($categories as $cat) { ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="col-md-12 audit-table-wrapper table-responsive" id="auditTableContainer" style="display: none;">
                <table>
                    <thead>
                    <tr>
                        <th>Sr. No</th>
                        <th>Audit Category</th>
                        <th>Audit Question</th>
                        <th class="client_leased">Client Leased</th>
                        <th class="inplant">Inplant</th>
                        <th class="fm_leased">FM Leased</th>
                        <th>Remark</th>
                        <th>Attachment</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    
                    
                   
                    if(isset($audit_data) && is_array($audit_data)) { 
                        foreach($audit_data as $key => $audit_head) { 
                            // prefill lookup
                            $rowKey = md5((string)($audit_head['question_name'] ?? '').'|'.(string)($audit_head['audit_question'] ?? ''));
                            $prefill = $prefillMap[$rowKey] ?? [
                                'client_leased' => '',
                                'inplant'       => '',
                                'fm_leased'     => '',
                                'remark'        => '',
                                'attachment'    => '',
                            ];

                            // detect Miscellaneous row (either in question_name or audit_parameter)
                            $isMisc = false;
                            if (isset($audit_head['audit_parameter']) && strcasecmp(trim($audit_head['audit_parameter']), 'miscellaneous') === 0) {
                                $isMisc = true;
                            }
                            if (!$isMisc && isset($audit_head['question_name']) && strcasecmp(trim($audit_head['question_name']), 'miscellaneous') === 0) {
                                $isMisc = true;
                            }
                            
                            // Clear Question Text for Miscellaneous if New Audit (Not Reaudit)
                            if ($isMisc && empty($details['hse_audit_id'])) {
                                $audit_head['audit_question'] = '';
                            }
                    ?>
                    <?php
                    // Check if this is a category header (single letter like A, B, C)
                    $categoryId = $audit_head['audit_category_id'] ?? '';
                    $isCategoryHeader = (strlen(trim($categoryId)) === 1 && ctype_alpha($categoryId));
                    ?>
                    
                    <?php if ($isCategoryHeader): ?>
                        <!-- Category Header Row (A, B, C, etc.) -->
                        <tr class="audit-row category-header-row" data-category="<?= htmlspecialchars($audit_head['question_name'] ?? '') ?>" style="background-color: #f0f0f0; font-weight: bold;">
                            <!-- hidden fields for controller -->
                            <input type="hidden" name="question_name[<?=$key?>]" value="<?= htmlspecialchars($audit_head['question_name'] ?? '') ?>">
                            <input type="hidden" name="question_audit_id[<?=$key?>]" value="<?= isset($audit_head['question_audit_id']) ? (int)$audit_head['question_audit_id'] : '' ?>">
                            <input type="hidden" name="is_misc[<?=$key?>]" value="0">
                            <input type="hidden" name="audit_category_id[<?=$key?>]" value="<?= htmlspecialchars($categoryId) ?>">
                            <input type="hidden" name="audit_question[<?=$key?>]" value="<?= htmlspecialchars($audit_head['audit_question'] ?? '') ?>">
                            <input type="hidden" name="client_leased[<?=$key?>]" value="">
                            <input type="hidden" name="inplant[<?=$key?>]" value="">
                            <input type="hidden" name="fm_leased[<?=$key?>]" value="">
                            <input type="hidden" name="remark[<?=$key?>]" value="">
                            
                            <th colspan="8" style="text-align: center; padding: 15px; font-size: 16px; background-color: #e0e0e0;">
                                <?= htmlspecialchars($categoryId) ?>. <?= htmlspecialchars($audit_head['question_name'] ?? '') ?>
                            </th>
                        </tr>
                    <?php else: ?>
                        <!-- Regular Question Row -->
                        <tr class="audit-row" data-category="<?= htmlspecialchars($audit_head['question_name'] ?? '') ?>">
                            <!-- hidden fields for controller -->
                            <input type="hidden" name="question_name[<?=$key?>]" value="<?= htmlspecialchars($audit_head['question_name'] ?? '') ?>">
                            <input type="hidden" name="question_audit_id[<?=$key?>]" value="<?= isset($audit_head['question_audit_id']) ? (int)$audit_head['question_audit_id'] : '' ?>">
                            <input type="hidden" name="is_misc[<?=$key?>]" value="<?= $isMisc ? '1' : '0' ?>">
                            <input type="hidden" name="audit_category_id[<?=$key?>]" value="<?= htmlspecialchars($audit_head['audit_category_id'] ?? '') ?>">

                            <!-- Sr No -->
                            <th>
                                <?= htmlspecialchars($audit_head['audit_category_id'] ?? ($key + 1)) ?>
                            </th>

                            <!-- Audit Category (never editable) -->
                            <th class="category-cell">
                                <?= htmlspecialchars($audit_head['question_name'] ?? '') ?>
                            </th>

                            <!-- Audit Question: ONLY editable for Miscellaneous -->
                            <th>
                                <?php if($isMisc): ?>
                                    <input type="text"
                                           name="audit_question[<?=$key?>]"
                                           class="form-control"
                                           value="<?= htmlspecialchars($audit_head['audit_question'] ?? '') ?>">
                                <?php else: ?>
                                    <input type="hidden"
                                           name="audit_question[<?=$key?>]"
                                           value="<?= htmlspecialchars($audit_head['audit_question'] ?? '') ?>">
                                    <?= htmlspecialchars($audit_head['audit_question'] ?? '') ?>
                                <?php endif; ?>
                            </th>
                            
                            <!-- Client Leased -->
                            <th class="client_leased finding-cell" data-hse-col="client_leased">
                                <label><input type="checkbox" class="checkbox" name="client_leased[<?=$key?>]" value="YES" data-type="client_leased" <?= (strtoupper($prefill['client_leased'])==='YES')?'checked':'' ?> onclick="singleSelectHSE(this, 'client_leased_<?=$key?>')"> YES</label>
                                <label><input type="checkbox" class="checkbox" name="client_leased[<?=$key?>]" value="NO" data-type="client_leased" <?= (strtoupper($prefill['client_leased'])==='NO')?'checked':'' ?> onclick="singleSelectHSE(this, 'client_leased_<?=$key?>')"> NO</label>
                                <label><input type="checkbox" class="checkbox" name="client_leased[<?=$key?>]" value="NA" data-type="client_leased" <?= (strtoupper($prefill['client_leased'])==='NA')?'checked':'' ?> onclick="singleSelectHSE(this, 'client_leased_<?=$key?>')"> NA</label>
                            </th>
                            
                            <!-- Inplant -->
                            <th class="inplant finding-cell" data-hse-col="inplant">
                                <label><input type="checkbox" class="checkbox" name="inplant[<?=$key?>]" value="YES" data-type="inplant" <?= (strtoupper($prefill['inplant'])==='YES')?'checked':'' ?> onclick="singleSelectHSE(this, 'inplant_<?=$key?>')"> YES</label>
                                <label><input type="checkbox" class="checkbox" name="inplant[<?=$key?>]" value="NO" data-type="inplant" <?= (strtoupper($prefill['inplant'])==='NO')?'checked':'' ?> onclick="singleSelectHSE(this, 'inplant_<?=$key?>')"> NO</label>
                                <label><input type="checkbox" class="checkbox" name="inplant[<?=$key?>]" value="NA" data-type="inplant" <?= (strtoupper($prefill['inplant'])==='NA')?'checked':'' ?> onclick="singleSelectHSE(this, 'inplant_<?=$key?>')"> NA</label>
                            </th>
                            
                            <!-- FM Leased -->
                            <th class="fm_leased finding-cell" data-hse-col="fm_leased">
                                <label><input type="checkbox" class="checkbox" name="fm_leased[<?=$key?>]" value="YES" data-type="fm_leased" <?= (strtoupper($prefill['fm_leased'])==='YES')?'checked':'' ?> onclick="singleSelectHSE(this, 'fm_leased_<?=$key?>')"> YES</label>
                                <label><input type="checkbox" class="checkbox" name="fm_leased[<?=$key?>]" value="NO" data-type="fm_leased" <?= (strtoupper($prefill['fm_leased'])==='NO')?'checked':'' ?> onclick="singleSelectHSE(this, 'fm_leased_<?=$key?>')"> NO</label>
                                <label><input type="checkbox" class="checkbox" name="fm_leased[<?=$key?>]" value="NA" data-type="fm_leased" <?= (strtoupper($prefill['fm_leased'])==='NA')?'checked':'' ?> onclick="singleSelectHSE(this, 'fm_leased_<?=$key?>')"> NA</label>
                            </th>

                            <!-- Remark -->
                            <th>
                                <textarea name="remark[<?=$key?>]"><?= htmlspecialchars($prefill['remark']) ?></textarea>
                            </th>

                            <!-- Attachment -->
                            <th>
                                <?php if (!empty($prefill['attachment'])) { ?>
                                    <div style="margin-bottom:6px;">
                                        <a href="<?= base_url($prefill['attachment']) ?>" target="_blank">Existing Attachment</a>
                                    </div>
                                    <input type="hidden" name="existing_attachment[<?=$key?>]" value="<?= htmlspecialchars($prefill['attachment']) ?>">
                                <?php } ?>
                                <input type="file" name="attachment[<?=$key?>]" accept=".jpg,.jpeg,.png,.gif,.pdf,.xls,.xlsx" onchange="validateFileType(this)">
                            </th>
                        </tr>
                    <?php endif; ?>
                    <?php } } ?>
                    </tbody>
                </table>
            </div>

            <input type="submit" class="btn btn-success float-end" value="Save Details">
        </div>
    </div>
</form>

<?php $this->endSection();?>

<?php $this->section("javascript_section");?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// // Auto-fetch and LOCK region based on client name selection
// function clientOnselect() {
//     var selectedOption = $('#client_id option:selected');
//     var clientName = selectedOption.text().trim();
//     var clientId = $('#client_id').val();
    
//     $("#client_name").val(clientName);
    
//     if (clientName && clientName !== 'Select Client' && clientName !== '' && clientId) {
//         $.ajax({
//             url: "<?= base_url('Masters/Hse_audit/get_region_by_client') ?>",
//             method: "POST",
//             data: { client_name: clientName },
//             dataType: "json",
//             success: function(response) {
//                 if (response.status === 1 && response.region) {
//                     $('#region_dropdown').hide();
//                     $('#region_locked').val(response.region).show();
//                     $('#region_hidden').val(response.region);
//                 } else {
//                     $('#region_dropdown').show();
//                     $('#region_locked').hide();
//                     $('#region_dropdown').val('');
//                     $('#region_hidden').val('');
//                 }
//             },
//             error: function(xhr, status, error) {
//                 console.log('Error fetching region for client:', error);
//                 $('#region_dropdown').show();
//                 $('#region_locked').hide();
//                 $('#region_dropdown').val('');
//                 $('#region_hidden').val('');
//             }
//         });
//     } else {
//         $('#region').val('').trigger('change');
//     }
// }

// function toggleAuditTable() {
//     const selectedOption = document.getElementById('perform_audit_by').value;
//     const auditTableContainer = document.getElementById('auditTableContainer');
    
//     if (selectedOption) {
//         auditTableContainer.style.display = 'block';
//     } else {
//         auditTableContainer.style.display = 'none';
//     }
    
//     const clientLeasedCols = document.querySelectorAll('.client_leased');
//     const inplantCols = document.querySelectorAll('.inplant');
//     const fmLeasedCols = document.querySelectorAll('.fm_leased');
    
//     clientLeasedCols.forEach(col => col.style.display = 'none');
//     inplantCols.forEach(col => col.style.display = 'none');
//     fmLeasedCols.forEach(col => col.style.display = 'none');
    
//     if (selectedOption === 'client_leased') {
//         clientLeasedCols.forEach(col => col.style.display = 'table-cell');
//     } else if (selectedOption === 'inplant') {
//         inplantCols.forEach(col => col.style.display = 'table-cell');
//     } else if (selectedOption === 'fm_leased') {
//         fmLeasedCols.forEach(col => col.style.display = 'table-cell');
//     }
    
//     calculateScoreHSE();
    
//     setTimeout(function() {
//         if (typeof applyFiltersHSE === 'function') {
//             applyFiltersHSE();
//         }
//     }, 100);
// }

// var scoreCalculationTimeout = null;
// var scoreCalculationScheduled = false;

// function singleSelectHSE(checkbox, groupName) {
//     var name = checkbox.getAttribute('name');
//     if (!name) { 
//         scheduleScoreCalculation(); 
//         return; 
//     }
    
//     var nodes = document.querySelectorAll('input[name="' + CSS.escape(name) + '"]');
//     for (var i = 0; i < nodes.length; i++) {
//         if (nodes[i] !== checkbox && nodes[i].checked) {
//             nodes[i].checked = false;
//         }
//     }
    
//     scheduleScoreCalculation();
// }

// function scheduleScoreCalculation() {
//     if (scoreCalculationScheduled) {
//         return;
//     }
    
//     scoreCalculationScheduled = true;
    
//     if (scoreCalculationTimeout) {
//         clearTimeout(scoreCalculationTimeout);
//     }
    
//     if (window.requestAnimationFrame) {
//         requestAnimationFrame(function() {
//             scoreCalculationTimeout = setTimeout(function() {
//                 calculateScoreHSE();
//                 scoreCalculationScheduled = false;
//                 scoreCalculationTimeout = null;
//             }, 50);
//         });
//     } else {
//         scoreCalculationTimeout = setTimeout(function() {
//             calculateScoreHSE();
//             scoreCalculationScheduled = false;
//             scoreCalculationTimeout = null;
//         }, 50);
//     }
// }

// function calculateScoreHSE() {
//     let yesCount = 0;
//     let attemptedCount = 0;
    
//     var selectedCategory = $('#perform_audit_by').val();
    
//     if (!selectedCategory) {
//         $('#scoreDisplay').text('0');
//         $('#finalScoreDisplay').text('100');
//         $('#finalScoreDisplayText').val('0');
//         return;
//     }
    
//     var questionGroups = new Set();
//     var list = document.querySelectorAll('.checkbox[data-type="' + selectedCategory + '"]');
//     for (var i = 0; i < list.length; i++) { 
//         questionGroups.add(list[i].getAttribute('name')); 
//     }
    
//     questionGroups.forEach(function(name) {
//         var checkedBox = document.querySelector('input[name="' + CSS.escape(name) + '"]:checked');
//         if (checkedBox) {
//             var checkedValue = (checkedBox.value || '').toUpperCase();
            
//             if (checkedValue === 'YES') {
//                 attemptedCount++;
//                 yesCount++;
//             } else if (checkedValue === 'NO') {
//                 attemptedCount++;
//             }
//         }
//     });
    
//     var score = 0;
//     if (attemptedCount > 0) {
//         score = (yesCount / attemptedCount) * 100;
//         score = parseFloat(score.toFixed(2));
//     }
    
//     var displayScore;
//     if (score === 0) {
//         displayScore = '0';
//     } else if (score % 1 === 0) {
//         displayScore = score.toString();
//     } else {
//         displayScore = parseFloat(score.toFixed(2)).toString();
//     }
//     $('#scoreDisplay').text(displayScore);
//     $('#finalScoreDisplay').text('100');
//     $('#finalScoreDisplayText').val(score);
// }

// function validateHSEAudit() {
//     var isReaudit = $("input[type=hidden][name=auditor_name]").length > 0;
    
//     if(isReaudit) {
//         if($("input[name=auditor_name]").val()=="" || $("input[name=auditor_name]").val()==null){
//             alert("Error: Auditor Name is missing. Please contact administrator.");
//             return false;
//         }
//         if($("input[name=client_name]").val()=="" || $("input[name=client_name]").val()==null){
//             alert("Error: Client Name is missing. Please contact administrator.");
//             return false;
//         }
//     } else {
//         if($("#auditor_name").val()=="" || $("#auditor_name").val()==null){
//             alert("Please select Auditor Name");
//             $("#auditor_name").focus();
//             return false;
//         }else if($("input[name=auditee_name]").val().trim()==""){
//             alert("Please enter Auditee Name");
//             $("input[name=auditee_name]").focus();
//             return false;
//         }else if($("input[name=audit_date]").val()==""){
//             alert("Please select Audit Date");
//             $("input[name=audit_date]").focus();
//             return false;
//         }else if($("#client_id").val()=="" || $("#client_id").val()==null){
//             alert("Please select Client Name");
//             $("#client_id").focus();
//             return false;
//         }else if($("input[name=report_date]").val()==""){
//             alert("Please select Report Date");
//             $("input[name=report_date]").focus();
//             return false;
//         }
//     }
    
//     if($("#region_hidden").val()=="" || $("#region_hidden").val()==null){
//         alert("Please select Region (select client first)");
//         if(!isReaudit) $("#client_id").focus();
//         return false;
//     }else if($("#perform_audit_by").val()=="" || $("#perform_audit_by").val()==null){
//         alert("Please select Site Category");
//         $("#perform_audit_by").focus();
//         return false;
//     }
//     return true;
// }

// function validateFileType(input) {
//     const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xls', 'xlsx'];
//     const maxSize = 10 * 1024 * 1024;
    
//     if (input.files && input.files[0]) {
//         const file = input.files[0];
//         const extension = file.name.split('.').pop().toLowerCase();
        
//         if (!allowedTypes.includes(extension)) {
//             alert('Invalid file type. Only images (JPG, PNG, GIF), PDFs, and Excel files (XLS, XLSX) are allowed.');
//             input.value = '';
//             return false;
//         }
        
//         if (file.size > maxSize) {
//             alert('File size too large. Maximum allowed size is 10MB.');
//             input.value = '';
//             return false;
//         }
//     }
//     return true;
// }

// (function() {
//     var auditNoField = document.getElementById('audit_no');
//     if (auditNoField && auditNoField.value === '') {
//         const randomNum = Math.floor(Math.random() * 999) + 1;
//         const auditNo = 'AUD' + randomNum;
//         auditNoField.value = auditNo;
//     }
// })();

// function applyFiltersHSE() {
//     var answerVal = $('#answerFilterHSE').val();
//     var categoryVal = $('#categoryFilterHSE').val();
    
//     $('.audit-row').each(function(){
//         var $row = $(this);
//         var groupCells = $row.find('.finding-cell');
//         var yesChecked = false, noChecked = false, naChecked = false;
        
//         groupCells.each(function(){
//             if ($(this).css('display') !== 'none') {
//                 yesChecked = yesChecked || $(this).find("input[value='YES']").is(':checked');
//                 noChecked  = noChecked  || $(this).find("input[value='NO']").is(':checked');
//                 naChecked  = naChecked  || $(this).find("input[value='NA']").is(':checked');
//             }
//         });
        
//         var rowCategory = $row.data('category') || $row.find('th').first().text().trim();
        
//         var showAnswer = true;
//         if (answerVal === 'attempted') {
//             showAnswer = yesChecked || noChecked || naChecked;
//         } else if (answerVal === 'not_attempted') {
//             showAnswer = !yesChecked && !noChecked && !naChecked;
//         } else if (answerVal === 'yes') {
//             showAnswer = yesChecked;
//         } else if (answerVal === 'no') {
//             showAnswer = noChecked;
//         } else if (answerVal === 'na') {
//             showAnswer = naChecked;
//         } else {
//             showAnswer = true;
//         }
        
//         var showCategory = true;
//         if (categoryVal !== 'all') {
//             showCategory = (rowCategory === categoryVal);
//         }
        
//         $row.toggle(showAnswer && showCategory);
//     });
// }

// $(document).ready(function() {
//     setTimeout(function() {
//         if (typeof $.fn.select2 !== 'undefined') {
//             $('.js-example-basic-single:visible').each(function() {
//                 $(this).select2({
//                     placeholder: "Select an option",
//                     allowClear: true,
//                     width: '100%'
//                 });
//             });
            
//             if($('#client_id').length && $('#client_id').is(':visible')) {
//                 $('#client_id').on('change', function() {
//                     clientOnselect();
//                 });
                
//                 var selectedClient = $('#client_id').val();
//                 if (selectedClient && selectedClient !== '') {
//                     setTimeout(function() {
//                         clientOnselect();
//                     }, 100);
//                 }
//             }
//         } else {
//             if($('#client_id').length && $('#client_id').is(':visible')) {
//                 $('#client_id').on('change', function() {
//                     clientOnselect();
//                 });
//             }
//         }
//     }, 500);
    
//     toggleAuditTable();
    
//     setTimeout(function() {
//         calculateScoreHSE();
//     }, 200);
    
//     $('#perform_audit_by').on('change', function() {
//         toggleAuditTable();
//     });
    
//     $('#region_dropdown').on('change', function() {
//         $('#region_hidden').val($(this).val());
//     });
    
//     $(document).off('change', '.checkbox').on('change', '.checkbox', function() {
//         setTimeout(function() {
//             applyFiltersHSE();
//         }, 100);
//     });
    
//     $('#answerFilterHSE').on('change', applyFiltersHSE);
//     $('#categoryFilterHSE').on('change', applyFiltersHSE);
    
//     $('textarea').on('keydown', function(e) {
//         if (e.key === 'Enter') {
//             e.stopPropagation();
//         }
//     });
//});
</script>  

<script>
/********************************************************************
 * SHORTCUT FUNCTIONS
 ********************************************************************/
const $  = (s) => document.querySelector(s);
const $$ = (s) => document.querySelectorAll(s);

let initialized = false;
let debounceTimer = null;


/********************************************************************
 * CLIENT → REGION AUTO SELECT
 ********************************************************************/
function clientOnselect() {
    let option = $('#client_id option:checked');
    let clientName = option.textContent.trim();
    let clientId   = $('#client_id').value;

    $("#client_name").value = clientName;

    if (!clientId) return resetRegion();

    fetch("<?= base_url('Masters/Hse_audit/get_region_by_client') ?>", {
    method: "POST",
    headers: {
        "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "client_name=" + encodeURIComponent(clientName)
})
.then(res => res.json())
.then(res => {
    if (res.status === 1 && res.region) {
        $('#region_dropdown').style.display = "none";
        $('#region_locked').style.display = "block";
        $('#region_locked').value = res.region;
        $('#region_hidden').value = res.region;
    } else {
        resetRegion();
    }
})
.catch(() => resetRegion());

}

function resetRegion() {
    $('#region_dropdown').style.display = 'block';
    $('#region_locked').style.display   = 'none';
    $('#region_dropdown').value         = "";
    $('#region_hidden').value           = "";
}


/********************************************************************
 * SHOW / HIDE AUDIT COLUMNS BASED ON SITE CATEGORY
 ********************************************************************/
function toggleAuditTable() {
    let selected = $('#perform_audit_by').value;
    let table    = $('#auditTableContainer');

    table.style.display = selected ? "block" : "none";

    ['client_leased','inplant','fm_leased'].forEach(col => {
        $$('.' + col).forEach(c => c.style.display = 'none');
    });

    if (selected) {
        $$('.' + selected).forEach(c => c.style.display = 'table-cell');
    }

    calculateScore();
    applyFilters();
    updateRealTimeSummary();
}


/********************************************************************
 * YES / NO / NA CHECKBOX LOGIC — PER ROW ONLY
 ********************************************************************/
function singleSelectHSE(checkbox) {
    let row  = checkbox.closest('tr');
    let type = checkbox.dataset.type;

    let group = row.querySelectorAll(`input.checkbox[data-type="${type}"]`);

    group.forEach(cb => {
        if (cb !== checkbox) cb.checked = false;
    });

    debounce(calculateScore);
    debounce(updateRealTimeSummary); // Update summary on change
}


/********************************************************************
 * DEBOUNCE FUNCTION
 ********************************************************************/
function debounce(fn, delay = 80) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fn, delay);
}


/********************************************************************
 * SCORE CALCULATION
 ********************************************************************/
function calculateScore() {
    let type = $('#perform_audit_by').value;
    if (!type) {
        $("#scoreDisplay").textContent = "0";
        $("#finalScoreDisplayText").value = "0";
        return;
    }

    let list = $$(`input.checkbox[data-type="${type}"]`);
    let groups = new Map();

    list.forEach(cb => {
        if (!groups.has(cb.name)) groups.set(cb.name, []);
        groups.get(cb.name).push(cb);
    });

    let yes = 0, attempted = 0;

    groups.forEach(group => {
        let checked = group.find(cb => cb.checked);
        if (checked) {
            attempted++;
            if (checked.value === "YES") yes++;
        }
    });

    let score = attempted ? ((yes / attempted) * 100).toFixed(2) : "0";
    $("#scoreDisplay").textContent = score;
    $("#finalScoreDisplayText").value = score;
}


/********************************************************************
 * FILTER ROWS
 ********************************************************************/
function applyFilters() {
    let ans = $('#answerFilterHSE').value;
    let cat = $('#categoryFilterHSE').value;

    $$('.audit-row').forEach(row => {

        // Detect active column based on category selection
        let visibleCell = row.querySelector('.finding-cell:not([style*="display:none"])');
        let yes = false, no  = false, na  = false;

        if (visibleCell) {
            yes = visibleCell.querySelector("input[value='YES']")?.checked;
            no  = visibleCell.querySelector("input[value='NO']")?.checked;
            na  = visibleCell.querySelector("input[value='NA']")?.checked;
        }

        // Answer filter logic
        let okAnswer = true;
        if (ans === "attempted")     okAnswer = yes || no || na;
        if (ans === "not_attempted") okAnswer = !yes && !no && !na;
        if (ans === "yes")           okAnswer = yes;
        if (ans === "no")            okAnswer = no;
        if (ans === "na")            okAnswer = na;

        // Category filter logic
        let rowCat = row.dataset.category;
        let okCat  = (cat === "all" || rowCat === cat);

        row.style.display = (okAnswer && okCat) ? "" : "none";
    });
}


/********************************************************************
 * FILE TYPE VALIDATION
 ********************************************************************/
function validateFileType(input) {
    let allowed = ['jpg','jpeg','png','gif','pdf','xls','xlsx'];
    let file = input.files[0];
    if (!file) return;

    let ext = file.name.split('.').pop().toLowerCase();
    if (!allowed.includes(ext)) {
        alert("Invalid file format");
        input.value = "";
        return;
    }

    if (file.size > 10*1024*1024) {
        alert("Max file size is 10MB");
        input.value = "";
    }
}


/********************************************************************
 * INITIALIZATION — RUNS ONLY ONCE
 ********************************************************************/
function initAudit() {
    if (initialized) return;
    initialized = true;

    toggleAuditTable();
    calculateScore();
    applyFilters();
    updateRealTimeSummary();
}


/********************************************************************
 * EVENT BINDINGS
 ********************************************************************/
document.addEventListener("DOMContentLoaded", () => {

    initAudit();

    // Delegated checkbox handler
    document.body.addEventListener("change", e => {
        if (e.target.classList.contains("checkbox")) {
            singleSelectHSE(e.target);
            applyFilters();
        }
    });

    $('#perform_audit_by')?.addEventListener("change", toggleAuditTable);
    $('#answerFilterHSE')?.addEventListener("change", applyFilters);
    $('#categoryFilterHSE')?.addEventListener("change", applyFilters);
    $('#client_id')?.addEventListener("change", clientOnselect);

    // Region dropdown updates hidden field
    $('#region_dropdown')?.addEventListener("change", e => {
        $('#region_hidden').value = e.target.value;
    });

    // Select2 load safely
    // Select2 load safely
    setTimeout(() => {
        if (typeof jQuery !== "undefined" && typeof jQuery.fn.select2 !== "undefined") {
            jQuery('.js-example-basic-single').select2({
                width: "100%",
                placeholder: "Select option",
                allowClear: true
            });
            // Fix: ensure select2 changes trigger native change
            jQuery('.js-example-basic-single').on('change', function() {
                this.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }
    }, 200);

});

/********************************************************************
 * AUTO GENERATE AUDIT NUMBER
 ********************************************************************/
(function () {
    let field = $('#audit_no');
    if (field && !field.value) {
        field.value = "AUD" + (Math.floor(Math.random()*999)+1);
    }
})();

// function validateHSEAudit() {

//     // -------------------------------
//     // HEADER LEVEL VALIDATION
//     // -------------------------------
//     var isReaudit = document.querySelector('input[type="hidden"][name="auditor_name"]') !== null;

//     if (!isReaudit) {

//         if (!document.getElementById('auditor_name')?.value) {
//             alert('Please select Auditor Name');
//             document.getElementById('auditor_name').focus();
//             return false;
//         }

//         if (!document.querySelector('input[name="auditee_name"]')?.value.trim()) {
//             alert('Please enter Auditee Name');
//             document.querySelector('input[name="auditee_name"]').focus();
//             return false;
//         }

//         if (!document.querySelector('input[name="audit_date"]')?.value) {
//             alert('Please select Audit Date');
//             document.querySelector('input[name="audit_date"]').focus();
//             return false;
//         }

//         if (!document.getElementById('client_id')?.value) {
//             alert('Please select Client Name');
//             document.getElementById('client_id').focus();
//             return false;
//         }

//         if (!document.querySelector('input[name="report_date"]')?.value) {
//             alert('Please select Report Date');
//             document.querySelector('input[name="report_date"]').focus();
//             return false;
//         }
//     }

//     if (!document.getElementById('region_hidden')?.value) {
//         alert('Please select Region (select client first)');
//         return false;
//     }

//     if (!document.getElementById('perform_audit_by')?.value) {
//         alert('Please select Site Category');
//         document.getElementById('perform_audit_by').focus();
//         return false;
//     }

//     // -------------------------------
//     // TABLE LEVEL VALIDATION
//     // -------------------------------
//     var siteType = document.getElementById('perform_audit_by').value;
//     var rows = document.querySelectorAll('.audit-row');

//     for (var i = 0; i < rows.length; i++) {

//         var row = rows[i];

//         // find visible checkbox group only
//         var checkboxes = row.querySelectorAll(
//             'input.checkbox[data-type="' + siteType + '"]'
//         );

//         if (checkboxes.length === 0) {
//             continue; // hidden row
//         }

//         var checked = false;
//         var checkedValue = '';

//         checkboxes.forEach(cb => {
//             if (cb.checked) {
//                 checked = true;
//                 checkedValue = cb.value;
//             }
//         });

//         if (!checked) {
//             alert('Please select YES / NO / NA for all audit questions');
//             row.scrollIntoView({ behavior: 'smooth', block: 'center' });
//             return false;
//         }

//         // If NO → remark mandatory
//         if (checkedValue === 'NO') {
//             var remark = row.querySelector('textarea');
//             if (!remark || !remark.value.trim()) {
//                 alert('Remark is mandatory when answer is NO');
//                 remark.focus();
//                 return false;
//             }
//         }
//     }

//     // -------------------------------
//     // SCORE CHECK (OPTIONAL SAFETY)
//     // -------------------------------
//     var score = document.getElementById('finalScoreDisplayText')?.value;
//     if (score === '' || score === null) {
//         alert('Score calculation failed. Please review answers.');
//         return false;
//     }

//     // ✅ ALL VALID
//     return true;
// }
function validateHSEAudit() {

    const qs  = (s) => document.querySelector(s);

    const isReaudit = qs('input[type="hidden"][name="auditor_name"]') !== null;

    /* ---------------- HEADER VALIDATION ---------------- */

    if (isReaudit) {

        let auditorName = qs('input[name="auditor_name"]')?.value || qs('select[name="auditor_name"]')?.value;
        if (!auditorName) {
            alert("Error: Auditor Name is missing. Please contact administrator.");
            return false;
        }

        let clientName = qs('input[name="client_name"]')?.value || qs('select[name="client_name"]')?.value;
        if (!clientName) {
            alert("Error: Client Name is missing. Please contact administrator.");
            return false;
        }

    } else {

        if (!qs('#auditor_name')?.value) {
            alert("Please select Auditor Name");
            qs('#auditor_name').focus();
            return false;
        }

        if (!qs('input[name="auditee_name"]')?.value.trim()) {
            alert("Please enter Auditee Name");
            qs('input[name="auditee_name"]').focus();
            return false;
        }

        if (!qs('input[name="audit_date"]')?.value) {
            alert("Please select Audit Date");
            qs('input[name="audit_date"]').focus();
            return false;
        }

        if (!qs('#client_id')?.value) {
            alert("Please select Client Name");
            qs('#client_id').focus();
            return false;
        }

        if (!qs('input[name="report_date"]')?.value) {
            alert("Please select Report Date");
            qs('input[name="report_date"]').focus();
            return false;
        }
    }

    if (!qs('#region_hidden')?.value) {
        alert("Please select Region (select client first)");
        if (!isReaudit) qs('#client_id')?.focus();
        return false;
    }

    if (!qs('#perform_audit_by')?.value) {
        alert("Please select Site Category");
        qs('#perform_audit_by').focus();
        return false;
    }

    /* ---------------- CHECKBOXES NOT COMPULSORY ---------------- */
    // ✔ NO checkbox validation (as requested)

    return true; // ✅ SAFE SUBMIT
}

/********************************************************************
 * REALTIME SUMMARY UPDATE (Overall & Category)
 ********************************************************************/
function updateRealTimeSummary() {
    let type = $('#perform_audit_by').value;
    $('#realtimeSummaryCard').style.display = type ? "block" : "none";
    if (!type) return;

    // Stats Accumulators
    let stats = {
        client_leased: { yes:0, no:0, na:0 },
        inplant:       { yes:0, no:0, na:0 },
        fm_leased:     { yes:0, no:0, na:0 }
    };

    let catStats = {}; 
    
    // Initialize Category Stats
    $$('#summaryCategoryTable tbody tr').forEach(tr => {
        let cat = tr.dataset.catName;
        if(cat) {
            catStats[cat] = { 
                client_leased: {yes:0, no:0, na:0}, 
                inplant: {yes:0, no:0, na:0}, 
                fm_leased: {yes:0, no:0, na:0} 
            };
        }
    });

    // Iterate all checkbox rows
    $$('.audit-row').forEach(row => {
        let catName = row.dataset.category;
        
        ['client_leased', 'inplant', 'fm_leased'].forEach(colType => {
            let checked = row.querySelector(`input.checkbox[data-type="${colType}"]:checked`);
            if (checked) {
                let v = checked.value.toUpperCase();
                if (v==='YES') { 
                    stats[colType].yes++; 
                    if(catStats[catName]) catStats[catName][colType].yes++; 
                }
                if (v==='NO')  { 
                    stats[colType].no++;  
                    if(catStats[catName]) catStats[catName][colType].no++; 
                }
                if (v==='NA')  { 
                    stats[colType].na++;  
                    if(catStats[catName]) catStats[catName][colType].na++; 
                }
            }
        });
    });

    // UPDATE UI: Overall
    ['client_leased', 'inplant', 'fm_leased'].forEach(t => {
        let row = $(`#row_summary_${t}`);
        if(row) {
            let totalSelected = stats[t].yes + stats[t].no + stats[t].na;
            row.querySelector('.s-total-selected').textContent = totalSelected;
            row.querySelector('.s-yes').textContent = stats[t].yes;
            row.querySelector('.s-no').textContent = stats[t].no;
            row.querySelector('.s-na').textContent = stats[t].na;
            
            let denom = stats[t].yes + stats[t].no;
            let pct = denom > 0 ? ((stats[t].yes / denom) * 100).toFixed(2) : '0';
            row.querySelector('.s-score').textContent = pct + '%';
            
            // Sync Main Score if this is the selected type
            if (t === type) {
               $("#scoreDisplay").textContent = pct;
               $("#finalScoreDisplayText").value = pct;
            }
        }
    });

    // UPDATE UI: Category
    for (let [catName, data] of Object.entries(catStats)) {
        // Find by data attribute to handle special chars safely
        let tr = $(`#summaryCategoryTable tbody tr[data-cat-name="${catName.replace(/"/g, '\\"')}"]`);
        if (tr) {
            // Helper to calc pct
            const getPct = (d) => {
                let total = d.yes + d.no;
                return total > 0 ? ((d.yes / total) * 100).toFixed(2) + '%' : '0%';
            };

            // Show/hide columns based on selected site category
            let clientCell = tr.querySelector('.c-client');
            let inplantCell = tr.querySelector('.c-inplant');
            let fmCell = tr.querySelector('.c-fm');
            
            if (clientCell) {
                clientCell.textContent = getPct(data.client_leased);
                clientCell.style.display = (type === 'client_leased') ? '' : 'none';
            }
            if (inplantCell) {
                inplantCell.textContent = getPct(data.inplant);
                inplantCell.style.display = (type === 'inplant') ? '' : 'none';
            }
            if (fmCell) {
                fmCell.textContent = getPct(data.fm_leased);
                fmCell.style.display = (type === 'fm_leased') ? '' : 'none';
            }
        }
    }
    
    // Show/hide header columns based on selected site category
    let headerRow = $('#summaryCategoryTable thead tr');
    if (headerRow) {
        let headers = headerRow.querySelectorAll('th');
        // Assuming order: Sr No, Category, Client %, Inplant %, FM %
        if (headers.length >= 5) {
            headers[2].style.display = (type === 'client_leased') ? '' : 'none';  // Client %
            headers[3].style.display = (type === 'inplant') ? '' : 'none';        // Inplant %
            headers[4].style.display = (type === 'fm_leased') ? '' : 'none';      // FM %
        }
    }
}

</script>

<?php $this->endSection();?>

