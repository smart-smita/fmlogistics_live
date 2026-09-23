<?php $this->extend("Layout/base_admin"); ?>
<?php
$this->section("breadcrumb_title_li");
?>
<?php
$isAuditorLogin = (isset($_SESSION['role']) && $_SESSION['role'] === 'Auditor');
$sessionAuditorName = $_SESSION['user_name'] ?? '';
$isReaudit = isset($details['hse_audit_id']) && !empty($details['hse_audit_id']);

if ($isAuditorLogin && !empty($sessionAuditorName)) {
    $details['auditor_name'] = $sessionAuditorName;
}
?>
<style>
    label {
        display: inline-block;
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .question-table,
    .question-table th,
    .question-table td {
        border: 1px solid #000 !important;
        border-collapse: collapse !important;
    }

    #result {
        background: darkgrey;
    }

    #resultAuditor {
        background: darkgrey;
        position: absolute;
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
        height: auto !important;
        min-height: 38px !important;
    }

    .select2-container {
        width: 100% !important;
        max-width: 100%;
    }

    .select2-selection__rendered {
        white-space: normal !important;
        word-break: break-word;
        line-height: 1.5 !important;
    }

    /* === NEW: sticky thead for HSE Audit table === */
    .audit-table-wrapper {
        max-height: 980px;
        /* adjust height as you like */
        overflow-y: auto;
        overflow-x: auto;
        /* keep horizontal scroll if needed */
    }

    .audit-table-wrapper table {
        margin-bottom: 0;
        /* prevent extra space at bottom inside scroll */
    }

    .audit-table-wrapper thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background-color: #f5f8fa;
        /* match theme */
    }

    .nc-closed-row {
        background-color: #d4edda !important;
    }

    .nc-closed-row td {
        background-color: #d4edda !important;
    }

    /* ✅ CAPA Saved Row Indicator */
    .capa-saved {
        background-color: #e8f1f5 !important;
        box-shadow: inset 3px 0 0 #0078d7;
    }

    .capa-saved td {
        background-color: #f1f8f6 !important;
    }

    .capa-indicator {
        display: inline-block;
        background: #4caf50 !important;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: bold;
        margin-left: 5px;
    }

    [data-hse-col] {
        display: none;
    }

    /* DO NOT hide remark */
    .remark-cell {
        display: table-cell !important;
    }

    /* Note Modal Styles */
    .note-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .note-modal-overlay.active {
        display: flex;
    }

    .note-modal-content {
        background: white;
        border-radius: 8px;
        padding: 30px;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .note-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e0e0e0;
    }

    .note-modal-header h5 {
        margin: 0;
        font-weight: 600;
    }

    .note-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #666;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .note-modal-close:hover {
        color: #000;
    }

    .note-modal-body {
        white-space: pre-wrap;
        word-wrap: break-word;
        line-height: 1.6;
        color: #333;
    }
</style>

<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-4">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success mb-4">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (isset($template_version_changed) && $template_version_changed): ?>
    <div class="alert alert-info mb-4">
        <strong>Notice:</strong> This Re-Audit is using the latest Audit Template Version. Previous findings have been mapped to the updated questionnaire where applicable.
    </div>
<?php endif; ?>

<form id="hseAuditForm" action="<?= $action ?>" method="post" enctype="multipart/form-data"
    onsubmit="return handleAuditSubmit(event)">
    <?= csrf_field() ?>
    <?php
    // Build prefill map for reaudit (existing HSE details)
    //     $prefillMap = [];
    // if (isset($audit_details) && is_array($audit_details)) {
    // foreach($audit_details as $row){
    
    //     $qid = $row['question_id'];
    
    //     $prefillMap[$qid] = [
    //         'finding'    => $row['finding'] ?? '',
    //         'remark'     => $row['remark'] ?? '',
    //         'attachment' => $row['attachment'] ?? ''
    //     ];
    // }
    
    // $data['prefillMap'] = $prefillMap;
    // }
    ?>

    <?php
    // Build prefill map for reaudit (existing HSE details)
    $prefillMap = [];

    if (isset($audit_details) && is_array($audit_details)) {

        foreach ($audit_details as $d) {

            $qId = (isset($d['question_id']) && !empty($d['question_id'])) ? (int) $d['question_id'] : null;
            $textKey = ($d['question_name'] ?? '') . '|' . ($d['audit_question'] ?? '');
            $keyIndex = $qId ?? $textKey;

            $prefillMap[$keyIndex] = [
                'remark' => $d['remark'] ?? '',
                'attachment' => $d['attachment'] ?? '',
                'finding' => strtoupper(trim($d['finding'] ?? '')),
                'capa_json' => $d['capa_json'] ?? '',
                'note' => $d['note'] ?? '',
                'audit_question' => $d['audit_question'] ?? '',
                'nc_status' => isset($d['nc_status']) ? (int) $d['nc_status'] : 0
            ];
        }
    }
    ?>


    <!-- Draft Available Alert -->
    <div id="draftAvailableAlert" class="col-md-12 mb-3" style="display:none;">
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert" style="border-left: 5px solid #17a2b8;">
            <div class="flex-grow-1">
                <strong><i class="mdi mdi-cloud-download me-1"></i> Draft Available!</strong> 
                An unsaved draft exists for this audit.
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary me-2" id="loadDraftBtn">Load Draft</button>
                <button type="button" class="btn btn-sm btn-secondary" id="discardDraftBtn">Discard</button>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>

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
                                    <?php if ($isReaudit && isset($details['hse_audit_id'])) { ?>
                                        <input type="hidden" name="hse_audit_id"
                                            value="<?= (int) $details['hse_audit_id'] ?>">
                                    <?php } ?>
                                    <input type="hidden" id="draft_type" value="<?= $isReaudit ? 'reaudit' : 'perform' ?>">
                                    <tr>
                                        <th class="table-secondary">Audit No.</th>
                                        <td>
                                            <?php
                                            if ($isReaudit && !empty($details['audit_no'])) {
                                                $final_audit_no = $details['audit_no'];
                                            } else {
                                                $final_audit_no = '[ Auto Generated on Final Save ]';
                                            }
                                            ?>
                                            <input type="text" class="form-control" id="audit_no" name="audit_no"
                                                value="<?= htmlspecialchars($final_audit_no) ?>" readonly
                                                style="background-color: #f5f5f5; cursor: not-allowed; font-weight: bold; color: #666;" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Audit Type Name</th>
                                        <td>
                                            <input type="text" class="form-control" name="audit_name"
                                                value="<?= isset($details['audit_name']) ? htmlspecialchars($details['audit_name']) : '' ?>"
                                                readonly />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Auditor Name</th>
                                        <td>

                                            <?php if ($isAuditorLogin): ?>

                                                <!-- If logged-in user is Auditor, always use session name -->
                                                <input type="text" class="form-control"
                                                    value="<?= htmlspecialchars($sessionAuditorName) ?>" readonly
                                                    style="background-color:#f5f5f5; cursor:not-allowed;" />

                                                <input type="hidden" id="auditor_name" name="auditor_name"
                                                    value="<?= htmlspecialchars($sessionAuditorName) ?>" />

                                            <?php elseif ($isReaudit): ?>

                                                <!-- Reaudit for non-auditor login -->
                                                <input type="text" class="form-control"
                                                    value="<?= htmlspecialchars($details['auditor_name'] ?? '') ?>" readonly
                                                    style="background-color:#f5f5f5; cursor:not-allowed;">

                                                <input type="hidden" id="auditor_name" name="auditor_name"
                                                    value="<?= htmlspecialchars($details['auditor_name'] ?? '') ?>">

                                            <?php else: ?>

                                                <!-- Perform Audit for Admin / Other roles -->
                                                <select class="form-select js-example-basic-single" id="auditor_name"
                                                    name="auditor_name">
                                                    <option value="">Select Auditor</option>

                                                    <?php if (isset($auditors) && is_array($auditors)) {
                                                        foreach ($auditors as $auditor) { ?>
                                                            <option value="<?= htmlspecialchars($auditor['user_name']) ?>"
                                                                <?= (($details['auditor_name'] ?? '') == $auditor['user_name']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($auditor['user_name']) ?>
                                                            </option>
                                                        <?php }
                                                    } ?>
                                                </select>

                                            <?php endif; ?>

                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Auditee Name</th>
                                        <td>
                                            <input type="text" class="form-control" name="auditee_name"
                                                value="<?= isset($details['auditee_name']) ? htmlspecialchars($details['auditee_name']) : '' ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Audit Date</th>
                                        <td>
                                            <input type="date" class="form-control" id="audit_date" name="audit_date"
                                                value="<?= isset($details['audit_date']) ? htmlspecialchars($details['audit_date']) : '' ?>"
                                                onchange="setNextAuditDate()" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            <button type="button" class="btn btn-info w-100 mt-2" onclick="openAttendanceModal()">📄 Auditee Attendance</button>
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
                                    <input type="hidden" name="audit_template_id"
                                        value="<?= isset($audit_template_id) ? $audit_template_id : '' ?>" />
                                    <tr>
                                        <th class="table-secondary">Region</th>
                                        <td>
                                            <?php if ($isReaudit) { ?>
                                                <input type="text" class="form-control" readonly
                                                    class="form-control-disabled"
                                                    value="<?= isset($details['region']) ? $details['region'] : '' ?>">
                                                <input type="hidden" name="region" id="region_hidden"
                                                    value="<?= isset($details['region']) ? $details['region'] : '' ?>">
                                            <?php } else { ?>
                                                <select class="form-select js-example-basic-single" name="region"
                                                    id="region_dropdown" onchange="loadCategoriesByRegion()">
                                                    <option value="">Select Region</option>
                                                    <?php if (isset($region)) {
                                                        foreach ($region as $reg) {
                                                            $selected = (isset($details['region']) && $details['region'] == $reg['region_name']) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?= $reg['region_name']; ?>" <?= $selected ?>>
                                                                <?= $reg['region_name']; ?>
                                                            </option>
                                                        <?php }
                                                    } ?>
                                                </select>
                                                <input type="hidden" name="region" id="region_hidden"
                                                    value="<?= isset($details['region']) ? $details['region'] : '' ?>">
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Site Category</th>
                                        <td>
                                            <?php if ($isReaudit) { ?>
                                                <input type="text" class="form-control" readonly
                                                    class="form-control-disabled"
                                                    value="<?= isset($details['main_category']) ? $details['main_category'] : (isset($details['perform_audit_by']) ? ucwords(str_replace('_', ' ', $details['perform_audit_by'])) : '') ?>">
                                                <input type="hidden" name="main_category" id="main_category"
                                                    value="<?= isset($details['main_category']) ? $details['main_category'] : (isset($details['perform_audit_by']) ? $details['perform_audit_by'] : '') ?>">
                                            <?php } else { ?>
                                                <select class="form-select js-example-basic-single" name="main_category"
                                                    id="main_category"
                                                    onchange="loadSubCategoriesByCategoryAndRegion(); updateRealTimeSummary();">
                                                    <option value="">Please Select</option>
                                                    <?php if (isset($name_list) && is_array($name_list)) {
                                                        foreach ($name_list as $nRow) {
                                                            $val = $nRow['display'] ?? $nRow['hse_type'];
                                                            $sel = (isset($details['main_category']) && $details['main_category'] == $val) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?= $val ?>" <?= $sel ?>><?= $val ?></option>
                                                        <?php }
                                                    } ?>
                                                </select>

                                            <?php } ?>
                                            <input type="hidden" name="perform_audit_by" id="perform_audit_by"
                                                value="<?= $details['perform_audit_by'] ?? '' ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Sub Category</th>
                                        <td>
                                            <?php if ($isReaudit) { ?>
                                                <input type="text" class="form-control" readonly
                                                    class="form-control-disabled"
                                                    value="<?= isset($details['sub_category']) ? $details['sub_category'] : '' ?>">
                                                <input type="hidden" name="sub_category" id="sub_category"
                                                    value="<?= isset($details['sub_category']) ? $details['sub_category'] : '' ?>">
                                            <?php } else { ?>
                                                <select class="form-select js-example-basic-single" name="sub_category"
                                                    id="sub_category"
                                                    onchange="updatePerformAuditBy(); toggleAuditTable(); loadClientsByRegionCategorySubcategory(); loadAuditQuestions(); updateRealTimeSummary();">
                                                    <option value="">Please Select Site Category First</option>
                                                </select>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Client Name</th>
                                        <td>
                                            <?php if ($isReaudit) { ?>
                                                <input type="text" id="client_name_display" class="form-control"
                                                    value="<?= isset($details['client_name']) ? htmlspecialchars($details['client_name']) : '' ?>"
                                                    readonly class="form-control-disabled">

                                                <input type="hidden" id="client_name" name="client_name"
                                                    value="<?= isset($details['client_name']) ? htmlspecialchars($details['client_name']) : '' ?>" />
                                                <input type="hidden" name="location"
                                                    value="<?= isset($details['location']) ? htmlspecialchars($details['location']) : '' ?>" />
                                                <input type="hidden" id="client_id" name="client_id"
                                                    value="<?= isset($details['client_id']) ? htmlspecialchars($details['client_id']) : '' ?>" />
                                                <input type="hidden" id="cluster_name" name="cluster_name"
                                                    value="<?= isset($details['cluster_name']) ? htmlspecialchars($details['cluster_name']) : '' ?>">
                                                <input type="hidden" id="account_manager" name="account_manager"
                                                    value="<?= isset($details['account_manager']) ? htmlspecialchars($details['account_manager']) : '' ?>">
                                            <?php } else { ?>
                                                <select class="form-select js-example-basic-single" id="client_id"
                                                    name="client_id" onchange="clientOnselect();">
                                                    <option value="">Select Client</option>
                                                </select>
                                                <input type="hidden" id="client_name" name="client_name"
                                                    value="<?= isset($details['client_name']) ? $details['client_name'] : '' ?>" />
                                                <input type="hidden" name="location"
                                                    value="<?= isset($details['location']) ? $details['location'] : '' ?>" />
                                                <input type="hidden" id="cluster_name" name="cluster_name"
                                                    value="<?= isset($details['cluster_name']) ? $details['cluster_name'] : '' ?>">
                                                <input type="hidden" id="account_manager" name="account_manager"
                                                    value="<?= isset($details['account_manager']) ? $details['account_manager'] : '' ?>">
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Next Audit Date</th>
                                        <td>
                                            <input type="date" class="form-control" id="report_date" name="report_date"
                                                value="<?= isset($details['report_date']) ? htmlspecialchars($details['report_date']) : '' ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="table-secondary">Score</th>
                                        <td>
                                            <span id="scoreDisplay">0</span> / <span id="finalScoreDisplay">100</span>
                                            <input type="hidden" id="finalScoreDisplayText" name="score" value="">
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
        foreach ($audit_data as $row) {
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
        usort($categories, function ($a, $b) use ($categorySrMap) {
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
            <div class="row mb-3">
                <div class="col-12">
                    <div class="p-2 border rounded bg-light d-flex justify-content-between flex-wrap"
                        style="gap: 10px;">
                        <span><strong>Selected Site Category:</strong> <span
                                id="summary_display_category">N/A</span></span>
                        <span><strong>Sub Category:</strong> <span id="summary_display_subcategory">N/A</span></span>
                        <span><strong>Client Name:</strong> <span id="summary_display_client">N/A</span></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Overall Summary -->
                <div class="col-md-5">
                    <h5 class="text-center mb-2">Overall Summary of Compliance</h5>

                    <table class="table table-bordered table-sm text-center" id="summaryOverallTable"
                        style="font-size:13px;">

                        <thead class="table-primary">
                            <tr>
                                <th style="background:#1e1c77;color:white;">Category</th>
                                <th style="background:#1e1c77;color:white;">Total Selected</th>
                                <th style="background:#1e1c77;color:white;">YES</th>
                                <th style="background:#1e1c77;color:white;">NO</th>
                                <th style="background:#1e1c77;color:white;">NA</th>
                                <th style="background:#1e1c77;color:white;">Score %</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!empty($name_list)): ?>
                                <?php foreach ($name_list as $row): ?>

                                    <tr id="row_summary_<?= htmlspecialchars($row['hse_type']) ?>"
                                        data-category-type="<?= htmlspecialchars($row['hse_type']) ?>">

                                        <td class="fw-bold">
                                            <?= htmlspecialchars($row['display']) ?>
                                        </td>

                                        <td class="s-total-selected">0</td>
                                        <td class="s-yes">0</td>
                                        <td class="s-no">0</td>
                                        <td class="s-na">0</td>
                                        <td class="s-score fw-bold">0%</td>

                                    </tr>

                                <?php endforeach; ?>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
                <!-- Category Wise -->
                <div class="col-md-7">
                    <h5 class="text-center mb-2">Category-wise Header Score Summary</h5>
                    <div style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-bordered table-sm text-center" id="summaryCategoryTable"
                            style="font-size: 13px;">
                            <thead class="table-primary" style="position: sticky; top: 0; z-index: 2;">
                                <tr>
                                    <th style="background-color: #1e1c77; color: white;">Sr No</th>
                                    <th style="background-color: #1e1c77; color: white;">Category</th>
                                    <?php
                                    // Dynamically generate header columns for all site categories
                                    if (isset($name_list) && is_array($name_list)) {
                                        foreach ($name_list as $nRow) {
                                            $hseType = $nRow['hse_type'];
                                            $displayName = $nRow['display'] ?? ucwords(str_replace('_', ' ', $hseType));
                                            $displayName = str_ireplace('Fm ', 'FM ', $displayName);
                                            ?>
                                            <th style="background-color: #1e1c77; color: white;"
                                                class="header-<?= htmlspecialchars($hseType) ?>"
                                                data-category-type="<?= htmlspecialchars($hseType) ?>">
                                                <?= htmlspecialchars($displayName) ?> %
                                            </th>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $srChar = 'A';

                                if (!empty($categories)) {

                                    foreach ($categories as $cat) {

                                        $displaySr = $categorySrMap[$cat] ?? $srChar++;

                                        ?>

                                        <tr data-cat-name="<?= htmlspecialchars($cat) ?>">

                                            <td><?= $displaySr ?></td>

                                            <td class="text-start"><?= htmlspecialchars($cat) ?></td>

                                            <?php
                                            foreach ($name_list as $nRow) {

                                                $hseType = $nRow['hse_type'];

                                                ?>

                                                <td class="c-<?= htmlspecialchars($hseType) ?>"
                                                    data-category-type="<?= htmlspecialchars($hseType) ?>">
                                                    0%
                                                </td>

                                            <?php } ?>

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
        <div class="card-header"
            style="position: sticky; top: 0; z-index: 999; background: #fff; border-bottom: 1px solid #eff2f5;">
            <div class="d-flex align-items-center" style="gap:10px; flex-wrap: wrap;">
                <label class="mb-0">Filter:</label>
                <select id="answerFilterHSE" class="form-select form-select-sm" style="width:auto;">
                    <option value="all">All</option>
                    <option value="attempted">Attempted</option>
                    <option value="not_attempted">Not Attempted</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                    <option value="na">NA</option>
                </select>
                <label class="mb-0">Category:</label>
                <select id="categoryFilterHSE" class="form-select form-select-sm" style="width:auto;">
                    <option value="all">All Categories</option>
                    <?php
                    // Reuse calculated categories
                    foreach ($categories as $cat) { ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="col-md-12 audit-table-wrapper table-responsive" id="auditTableContainer"
                style="display: <?= $isReaudit ? 'block' : 'none' ?>;">
                <table
                    class="table table-row-bordered question-table align-middle text-gray-700 font-medium text-sm w-100">
                    <thead>
                        <tr>
                            <th>Sr. No</th>
                            <th>Audit Category</th>
                            <th style="min-width: 250px;">Audit Question</th>
                            <?php if (!empty($name_list)): ?>
                                <?php foreach ($name_list as $row): ?>
                                    <th class="<?= htmlspecialchars($row['hse_type']) ?>"
                                        data-hse-col="<?= htmlspecialchars($row['hse_type']) ?>">
                                        <?= htmlspecialchars($row['display']) ?>
                                    </th>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <th style="display:none;">Note / Relevance</th>
                            <th style="min-width: 250px;">Remark</th>
                            <th style="min-width: 200px;">Attachment</th>
                        </tr>
                    </thead>
                    <tbody id="auditTableBody">
                        <?php



                        if (isset($audit_data) && is_array($audit_data)) {
                            foreach ($audit_data as $key => $audit_head) {

                                // prefill lookup
                                // Priority: question_id (int) or plain text key
                                $qId = $audit_head['question_id'] ?? null;
                                $rowKey = $qId;
                                $textKey = (string) ($audit_head['question_name'] ?? '') . '|' . (string) ($audit_head['audit_question'] ?? '');

                                $prefill = $prefillMap[$rowKey] ?? [
                                    'finding' => '',
                                    'remark' => '',
                                    'attachment' => '',
                                    'nc_status' => 0,
                                    'capa_json' => '',
                                    'audit_question' => '',
                                ];


                                $ncTracker = (int) ($prefill['nc_status'] ?? 0);
                                $findingValue = strtoupper(trim($prefill['finding'] ?? ''));
                                $isClosedNcNo = ($ncTracker === 3 && $findingValue === 'NO');

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
                                // ----------------------------------------------------
                                // PRIORITY CHECK: SAVED VALUE > DEFAULT VALUE
                                // ----------------------------------------------------
                        
                                // Trim & normalize defaults
                                $default_client = strtoupper(trim($audit_head['client_leased'] ?? ''));
                                $default_fm = strtoupper(trim($audit_head['fm_leased'] ?? ''));
                                $default_office = strtoupper(trim($audit_head['inplant'] ?? ''));

                                // Detect saved values
                                $hasSavedClient = !empty($prefill['client_leased']);
                                $hasSavedFm = !empty($prefill['fm_leased']);
                                $hasSavedOffice = !empty($prefill['office']);

                                // Final values (saved value gets priority)
                                $clientValue = $hasSavedClient ? strtoupper($prefill['client_leased']) : ($default_client === 'NA' ? 'NA' : '');
                                $fmValue = $hasSavedFm ? strtoupper($prefill['fm_leased']) : ($default_fm === 'NA' ? 'NA' : '');
                                $officeValue = $hasSavedOffice ? strtoupper($prefill['office']) : ($default_office === 'NA' ? 'NA' : '');

                                ?>
                                <?php
                                // Check if this is a category header (single letter like A, B, C)
                                $categoryId = $audit_head['audit_category_id'] ?? '';
                                $isCategoryHeader = (strlen(trim($categoryId)) === 1 && ctype_alpha($categoryId));
                                ?>

                                <?php if ($isCategoryHeader): ?>
                                    <!-- Category Header Row (A, B, C, etc.) -->
                                    <tr class="audit-row category-header-row"
                                        data-category="<?= htmlspecialchars($audit_head['question_name'] ?? '') ?>"
                                        style="background-color: #f0f0f0; font-weight: bold;">
                                        
                                        <th colspan="<?= 3 + count($name_list) + 2 ?>"
                                            style="text-align: center; padding: 15px; font-size: 16px; background-color: #e0e0e0;">
                                            <!-- hidden fields for controller -->
                                            <input type="hidden" name="question_name[<?= $key ?>]"
                                                value="<?= htmlspecialchars($audit_head['question_name'] ?? '') ?>">
                                            <input type="hidden" name="question_audit_id[<?= $key ?>]"
                                                value="<?= (int) $audit_head['question_id'] ?? '' ?>">
                                            <input type="hidden" name="is_misc[<?= $key ?>]" value="0">
                                            <input type="hidden" name="audit_category_id[<?= $key ?>]"
                                                value="<?= htmlspecialchars($categoryId) ?>">
                                            <input type="hidden" name="audit_question[<?= $key ?>]"
                                                value="<?= htmlspecialchars($audit_head['audit_question'] ?? '') ?>">
                                            <input type="hidden" name="client_leased[<?= $key ?>]" value="">
                                            <input type="hidden" name="fm_leased[<?= $key ?>]" value="">
                                            <input type="hidden" name="remark[<?= $key ?>]" value="">
                                            <input type="hidden" name="capa_json[<?= $key ?>]" class="capa_json"
                                                value='<?= htmlspecialchars($prefill['capa_json'] ?? '', ENT_QUOTES, 'UTF-8') ?>'>

                                            <?= htmlspecialchars($categoryId) ?>.
                                            <?= htmlspecialchars($audit_head['question_name'] ?? '') ?>
                                        </th>
                                    </tr>
                                <?php else: ?>
                                    <!-- Regular Question Row -->
                                    <tr class="audit-row <?= $isClosedNcNo ? 'nc-closed-row' : '' ?>"
                                        data-category="<?= htmlspecialchars($audit_head['question_name'] ?? '') ?>">
                                        
                                        <!-- Sr No -->
                                        <th>
                                            <!-- hidden fields for controller -->
                                            <input type="hidden" name="question_name[<?= $key ?>]"
                                                value="<?= htmlspecialchars($audit_head['question_name'] ?? '') ?>">
                                            <input type="hidden" name="question_audit_id[<?= $key ?>]"
                                                value="<?= isset($audit_head['question_id']) ? (int) $audit_head['question_id'] : '' ?>">
                                            <input type="hidden" name="is_misc[<?= $key ?>]" value="<?= $isMisc ? '1' : '0' ?>">
                                            <input type="hidden" name="audit_category_id[<?= $key ?>]"
                                                value="<?= htmlspecialchars($audit_head['audit_category_id'] ?? '') ?>">
                                            <input type="hidden" name="capa_json[<?= $key ?>]" class="capa_json"
                                                value='<?= htmlspecialchars($prefill['capa_json'] ?? '', ENT_QUOTES, 'UTF-8') ?>'>
                                            <input type="hidden" name="nc_type[<?= $key ?>]" class="nc_type_input"
                                                value='<?= $audit_head['nc_type'] ?? 'NC' ?>'>
                                            
                                            <?php
                                            $activeSiteCategoryRaw = $details['main_category'] ?? $details['perform_audit_by'] ?? '';
                                            $activeSiteCategory = strtolower(str_replace(' ', '_', $activeSiteCategoryRaw));
                                            $rowFinding = strtoupper(trim($prefill['finding'] ?? ''));
                                            if ($isClosedNcNo)
                                                $rowFinding = 'YES';
                                            if (empty($rowFinding)) {
                                                $rowFinding = strtoupper(trim($audit_head['default_value'] ?? ''));
                                            }
                                            ?>
                                            <input type="hidden" name="finding[<?= $key ?>]" class="finding_hidden"
                                                value="<?= $rowFinding ?>">

                                            <?= htmlspecialchars($audit_head['audit_category_id'] ?? ($key + 1)) ?>
                                        </th>

                                        <!-- Audit Category (never editable) -->
                                        <th class="category-cell">
                                            <?= htmlspecialchars($audit_head['question_name'] ?? '') ?>
                                        </th>

                                        <!-- Audit Question: ONLY editable for Miscellaneous -->
                                        <th>
                                            <?php if ($isMisc): ?>

                                                <?php
                                                $savedQuestion = $prefill['audit_question'] ?? '';
                                                $finalQuestion = !empty($savedQuestion)
                                                    ? $savedQuestion
                                                    : ($audit_head['audit_question'] ?? '');
                                                ?>

                                                <input type="text" name="audit_question[<?= $key ?>]" class="form-control"
                                                    value="<?= htmlspecialchars($finalQuestion) ?>" placeholder="Enter Audit Question"
                                                    <?= (!empty($savedQuestion) && $isReaudit) ? 'readonly' : '' ?>
                                                    style="background: <?= (!empty($savedQuestion) && $isReaudit) ? '#f8f9fa' : '#fff' ?>;">
                                                <?php if ($isReaudit && !empty($prefill['capa_json'])): ?>
                                                    <div class="mt-2 capa-edit-btn-container">
                                                        <button type="button" class="btn btn-sm btn-info py-1 px-2" style="font-size: 10px;"
                                                            onclick="openCapaModalForRow(this.closest('tr'))">
                                                            <i class="fa fa-edit"></i> Edit CAPA
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <input type="hidden" name="audit_question[<?= $key ?>]"
                                                    value="<?= htmlspecialchars($audit_head['audit_question'] ?? '') ?>">
                                                <?= htmlspecialchars($audit_head['audit_question'] ?? '') ?>
                                                <?php
                                                if ($isReaudit && !empty($prefill['capa_json']) && $prefill['finding'] === 'NO'): ?>
                                                    <div class="mt-2 capa-edit-btn-container">
                                                        <button type="button" class="btn btn-sm btn-info py-1 px-2" style="font-size: 10px;"
                                                            onclick="openCapaModalForRow(this.closest('tr'))">
                                                            <i class="fa fa-edit"></i> Edit CAPA
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </th>

                                        <!-- Dynamic Site Category Columns -->
                                        <?php if (!empty($name_list)): ?>
                                            <?php foreach ($name_list as $row): ?>
                                                <?php
                                                $hseType = $row['hse_type'];


                                                $finalValue = ($hseType === $activeSiteCategory) ? $rowFinding : '';
                                                if ($hseType !== $activeSiteCategory && strtoupper(trim($audit_head['default_value'] ?? '')) === 'NA') {
                                                    $finalValue = 'NA';
                                                }


                                                ?>

                                                <th class="<?= htmlspecialchars($hseType) ?> finding-cell"
                                                    data-hse-col="<?= htmlspecialchars($hseType) ?>">



                                                    <label>
                                                        <input type="checkbox" class="checkbox" data-type="<?= $hseType ?>" value="YES"
                                                            <?= ($finalValue === 'YES') ? 'checked' : '' ?> onclick="singleSelectHSE(this)">
                                                        YES
                                                    </label>

                                                    <label>
                                                        <input type="checkbox" class="checkbox no_checkbox" data-type="<?= $hseType ?>"
                                                            value="NO" <?= ($finalValue === 'NO') ? 'checked' : '' ?>
                                                            onclick="singleSelectHSE(this)"> NO
                                                    </label>

                                                    <label>
                                                        <input type="checkbox" class="checkbox" value="NA" data-type="<?= $hseType ?>"
                                                            <?= ($finalValue === 'NA') ? 'checked' : '' ?> onclick="singleSelectHSE(this)"> NA
                                                    </label>
                                                </th>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <!-- Note / Relevance -->
                                        <th style="display:none;">
                                            <?php
                                            $noteText = !empty($prefill['note']) ? $prefill['note'] : ($audit_head['note'] ?? '');
                                            ?>
                                            <?php if (!empty($noteText)) { ?>
                                                <button type="button" class="btn btn-sm btn-primary note-modal-trigger"
                                                    onclick="openNoteModal(this)"
                                                    data-note="<?= htmlspecialchars($noteText, ENT_QUOTES) ?>"
                                                    data-question="<?= htmlspecialchars($audit_head['audit_question'] ?? 'Note', ENT_QUOTES) ?>">View</button>
                                            <?php } else { ?>
                                                <span class="text-muted">-</span>
                                            <?php } ?>
                                            <input type="hidden" name="note[<?= $key ?>]"
                                                value="<?= htmlspecialchars($noteText, ENT_QUOTES) ?>">
                                        </th>

                                        <!-- Remark -->
                                        <th>
                                            <textarea name="remark[<?= $key ?>]" class="form-control"
                                                placeholder="Add remarks"><?= htmlspecialchars($prefill['remark'] ?? '') ?></textarea>
                                        </th>

                                        <!-- Attachment -->
                                        <th>
                                            <?php if (!empty($prefill['attachment'])) { ?>
                                                <div class="existing-attachment" style="margin-bottom:6px;">
                                                    <a href="<?= base_url($prefill['attachment']) ?>" target="_blank">Existing
                                                        Attachment</a>
                                                </div>
                                                <input type="hidden" name="existing_attachment[<?= $key ?>]"
                                                    value="<?= htmlspecialchars($prefill['attachment']) ?>">
                                            <?php } else { ?>
                                                <input type="hidden" name="existing_attachment[<?= $key ?>]" value="">
                                            <?php } ?>
                                            <input type="file" name="attachment[<?= $key ?>]"
                                                accept=".jpg,.jpeg,.png,.gif,.pdf,.xls,.xlsx"
                                                onchange="uploadAttachmentAsync(this)">
                                        </th>

                                    </tr>
                                <?php endif; ?>
                            <?php }
                        } ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" id="saveAuditBtn" class="btn btn-success float-end">
                <span id="saveBtnText">Save Details</span>
                <span id="saveBtnLoader" style="display:none; margin-left:8px;">
                    <i class="fa fa-spinner fa-spin"></i>
                </span>
            </button>
        </div>
    </div>
    <!-- Audit Attendance Sheet Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="attendanceModalLabel"><span class="text-white">📄 Audit Attendance Sheet</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Header Card -->
                    <div class="card mb-3">
                        <div class="card-body bg-light">
                            <div class="row">
                                <div class="col-md-4 mb-2"><strong>Audit No:</strong> <span id="att_audit_no"></span></div>
                                <div class="col-md-4 mb-2"><strong>Audit Type Name:</strong> <span id="att_audit_type"></span></div>
                                <div class="col-md-4 mb-2"><strong>Auditor Name:</strong> <span id="att_auditor_name"></span></div>
                                <div class="col-md-4 mb-2"><strong>Auditee Name:</strong> <span id="att_auditee_name"></span></div>
                                <div class="col-md-4 mb-2"><strong>Audit Date:</strong> <span id="att_audit_date"></span></div>
                                <div class="col-md-4 mb-2"><strong>Region:</strong> <span id="att_region"></span></div>
                                <div class="col-md-4 mb-2"><strong>Site Category:</strong> <span id="att_site_category"></span></div>
                                <div class="col-md-4 mb-2"><strong>Sub Category:</strong> <span id="att_sub_category"></span></div>
                                <div class="col-md-4 mb-2"><strong>Client Name:</strong> <span id="att_client_name"></span></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table Header with Add Row Button -->
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-sm btn-primary" id="btnAddAttendanceRow" onclick="addAttendanceRow()">+ Add Row</button>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center align-middle" id="attendanceTable">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th style="width: 5%;">Sr. No.</th>
                                    <th style="width: 25%;">Auditee Attendance</th>
                                    <th style="width: 15%;">Opening Date</th>
                                    <th style="width: 20%;">Opening Sign</th>
                                    <th style="width: 15%;">Closing Date</th>
                                    <th style="width: 20%;">Closing Sign</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceTableBody">
                                <?php 
                                $attendanceRows = 1; 
                                if (isset($attendance) && is_array($attendance) && count($attendance) > 0) {
                                    $attendanceRows = count($attendance);
                                }
                                
                                for($i=1; $i<=$attendanceRows; $i++): 
                                    $attData = null;
                                    if (isset($attendance) && is_array($attendance)) {
                                        foreach ($attendance as $row) {
                                            if ($row['row_no'] == $i) {
                                                $attData = $row;
                                                break;
                                            }
                                        }
                                    }
                                    $auditeeName = $attData ? htmlspecialchars($attData['auditee_attendance']) : '';
                                    $openDate = ($attData && !empty($attData['opening_date'])) ? htmlspecialchars($attData['opening_date']) : '';
                                    $openSign = $attData ? htmlspecialchars($attData['opening_sign']) : '';
                                    $closeDate = ($attData && !empty($attData['closing_date'])) ? htmlspecialchars($attData['closing_date']) : '';
                                    $closeSign = $attData ? htmlspecialchars($attData['closing_sign']) : '';
                                ?>
                                <tr class="attendance-row" data-index="<?= $i ?>">
                                    <td class="sr-no"><?= $i ?></td>
                                    <td>
                                        <input type="text" class="form-control" name="att_auditee_name[<?= $i ?>]" placeholder="Enter Name" value="<?= $auditeeName ?>">
                                    </td>
                                    <td>
                                        <input type="date" class="form-control" name="att_opening_date[<?= $i ?>]" value="<?= $openDate ?>">
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openSignaturePad(this, 'att_opening_sign', <?= $i ?>)" title="Draw Signature">✍️ Draw</button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="triggerSignatureUpload(this)" title="Upload Image">📁 Upload</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="triggerSignatureCamera(this)" title="Capture Camera">📷 Camera</button>
                                        </div>
                                        <input type="file" class="d-none" name="att_opening_sign[<?= $i ?>]" accept="image/*" onchange="uploadAttendanceAttachmentAsync(this)">
                                        <input type="hidden" name="existing_att_opening_sign[<?= $i ?>]" value="<?= $openSign ?>">
                                        <div class="att-opening-preview mt-2">
                                            <?php if($openSign): ?>
                                                <div class="position-relative d-inline-block">
                                                    <img src="<?= base_url($openSign) ?>" style="max-height: 50px; border: 1px solid #ccc; padding: 2px; cursor: pointer;" onclick="previewSignature('<?= base_url($openSign) ?>')">
                                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 start-100 translate-middle rounded-circle p-1" style="width: 24px; height: 24px; line-height: 1;" onclick="clearSignature(this, 'existing_att_opening_sign[<?= $i ?>]')"><i class="fa fa-times text-white ps-1"></i></button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="date" class="form-control" name="att_closing_date[<?= $i ?>]" value="<?= $closeDate ?>">
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openSignaturePad(this, 'att_closing_sign', <?= $i ?>)" title="Draw Signature">✍️ Draw</button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="triggerSignatureUpload(this)" title="Upload Image">📁 Upload</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="triggerSignatureCamera(this)" title="Capture Camera">📷 Camera</button>
                                        </div>
                                        <input type="file" class="d-none" name="att_closing_sign[<?= $i ?>]" accept="image/*" onchange="uploadAttendanceAttachmentAsync(this)">
                                        <input type="hidden" name="existing_att_closing_sign[<?= $i ?>]" value="<?= $closeSign ?>">
                                        <div class="att-closing-preview mt-2">
                                            <?php if($closeSign): ?>
                                                <div class="position-relative d-inline-block">
                                                    <img src="<?= base_url($closeSign) ?>" style="max-height: 50px; border: 1px solid #ccc; padding: 2px; cursor: pointer;" onclick="previewSignature('<?= base_url($closeSign) ?>')">
                                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 start-100 translate-middle rounded-circle p-1" style="width: 24px; height: 24px; line-height: 1;" onclick="clearSignature(this, 'existing_att_closing_sign[<?= $i ?>]')"><i class="fa fa-times text-white ps-1"></i></button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="btnSaveAttendanceModal" onclick="saveAttendanceModal()">Save</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<!-- Signature Pad Modal -->
<div class="modal fade" id="signaturePadModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">✍️ Draw Signature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div style="border: 2px dashed #ccc; padding: 5px; background: #f8f9fa; display: inline-block;">
                    <canvas id="signatureCanvas" style="border: 1px solid #000; cursor: crosshair; touch-action: none; background: #fff;"></canvas>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="signaturePad.clear()">Clear</button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="undoSignature()">Undo</button>
                </div>
                <div>
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="saveSignaturePad()">Save Signature</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Note Modal -->
<div id="noteModalOverlay" class="note-modal-overlay">
    <div class="note-modal-content">
        <div class="note-modal-header">
            <h5 id="noteModalQuestion">Note</h5>
            <button type="button" id="noteModalClose" class="note-modal-close">&times;</button>
        </div>
        <div class="note-modal-body" id="noteModalText"></div>
    </div>
</div>

<!-- CAPA JSON Modal for Miscellaneous NO findings -->
<div class="modal fade" id="capaModal" tabindex="-1" aria-labelledby="capaModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="capaModalLabel">CAPA Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="capaForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Findings</strong></label>
                            <textarea id="capa_findings" name="capa_findings" class="form-control" rows="2"
                                placeholder="Enter findings..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Risk</strong></label>
                            <textarea id="capa_risk" name="capa_risk" class="form-control" rows="2"
                                placeholder="Enter risk..."></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><strong>Actions/Recommendation</strong></label>
                            <textarea id="capa_actions" name="capa_actions" class="form-control" rows="2"
                                placeholder="Enter actions..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><strong>Action Category</strong></label>
                            <input id="capa_action_category" type="text" name="capa_action_category"
                                class="form-control" placeholder="Enter action category">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label"><strong>UA / UC</strong></label>
                            <input id="capa_ua_uc" type="text" name="capa_ua_uc" class="form-control"
                                placeholder="Enter UA / UC">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><strong>Risk Severity</strong></label>
                            <select id="capa_risk_severity" name="capa_risk_severity" class="form-control">
                                <option value="">Select...</option>
                                <option value="Very Low">Very Low</option>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Very High">Very High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><strong>Risk Probability</strong></label>
                            <select id="capa_risk_probability" name="capa_risk_probability" class="form-control">
                                <option value="">Select...</option>
                                <option value="Very Low">Very Low</option>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Very High">Very High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label"><strong>Color Code</strong></label>
                            <select id="capa_color_code" name="capa_color_code" class="form-control">
                                <option value="">Select...</option>
                                <option value="Yellow">Yellow</option>
                                <option value="Red">Red</option>
                                <option value="Black">Black</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><strong>Cost Type</strong></label>
                            <select id="capa_cost_type" name="capa_cost_type" class="form-control">
                                <option value="">Select...</option>
                                <option value="Operational Action">Operational Action</option>
                                <option value="Opex">Opex</option>
                                <option value="Capex">Capex</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><strong>Combined Risk Rating</strong></label>
                            <input id="capa_combined_risk_rating" type="number" name="capa_combined_risk_rating"
                                class="form-control" placeholder="Auto calculated" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="recommendation_checkbox"
                                    name="recommendation_checkbox" checked>
                                <label class="form-check-label" for="recommendation_checkbox">
                                    Recommendation
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                    id="capaCancelBtn">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCapaBtn" onclick="saveCapaData()">Save CAPA
                    Details</button>
            </div>
        </div>
    </div>
</div>



<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
    /********************************************************************
     * SHORTCUT FUNCTIONS
     ********************************************************************/
    const qs = (s) => document.querySelector(s);
    const qsa = (s) => document.querySelectorAll(s);
    const NAME_LIST = <?= json_encode($name_list ?? []) ?>;
    let ACTIVE_SITE_CATEGORY = "<?= $active_site_category ?? '' ?>";
    const CATEGORY_MAP = <?= json_encode($category_map ?? []) ?>;

    let initialized = false;
    let debounceTimer = null;

    $(document).ready(function () {
        if (typeof jQuery !== "undefined" && typeof jQuery.fn.select2 !== "undefined") {
            $('.js-example-basic-single').select2({ width: '100%' });
        }

        ['#capa_risk_severity', '#capa_risk_probability', '#capa_color_code', '#capa_cost_type'].forEach(selector => {
            const el = document.querySelector(selector);
            if (el) {
                el.addEventListener('change', calculateRiskRating);
            }
        });
    });

    const RISK_SCORE_MAP = {
        'Very Low': 1,
        'Low': 2,
        'Medium': 3,
        'High': 4,
        'Very High': 5,
        'Critical': 5
    };

    const COLOR_SCORE_MAP = {
        'Yellow': 1,
        'Black': 2,
        'Red': 3
    };

    const COST_SCORE_MAP = {
        'Operational Action': 1,
        'Opex': 2,
        'Capex': 3
    };

    function calculateRiskRating() {
        const severity = RISK_SCORE_MAP[document.getElementById('capa_risk_severity')?.value] || 0;
        const probability = RISK_SCORE_MAP[document.getElementById('capa_risk_probability')?.value] || 0;
        const color = COLOR_SCORE_MAP[document.getElementById('capa_color_code')?.value] || 0;
        const cost = COST_SCORE_MAP[document.getElementById('capa_cost_type')?.value] || 0;

        const result = severity * probability * color * cost;
        const ratingEl = document.getElementById('capa_combined_risk_rating');
        if (ratingEl) {
            ratingEl.value = result || '';
            if (result > 100) {
                ratingEl.style.backgroundColor = '#f8d7da';
                ratingEl.style.color = '#842029';
            } else if (result >= 50) {
                ratingEl.style.backgroundColor = '#fff3cd';
                ratingEl.style.color = '#664d03';
            } else if (result > 0) {
                ratingEl.style.backgroundColor = '#d1e7dd';
                ratingEl.style.color = '#0f5132';
            } else {
                ratingEl.style.backgroundColor = '';
                ratingEl.style.color = '';
            }
        }
    }


    /********************************************************************
     * REGION → SITE CATEGORY → SUB CATEGORY → CLIENT CASCADE
     * changes on 21/02/26: Proper cascade flow from database
     ********************************************************************/

    // Load Site Categories when Region is selected
    function loadCategoriesByRegion() {
        let regionDropdown = document.querySelector('#region_dropdown');
        let categoryDropdown = document.querySelector('#main_category');
        let subCategoryDropdown = document.querySelector('#sub_category');
        let clientDropdown = document.querySelector('#client_id');

        if (!regionDropdown || !categoryDropdown) {
            console.error('Required dropdowns not found');
            return;
        }

        let region = regionDropdown.value;
        console.log('loadCategoriesByRegion called with region:', region);

        // Ensure hidden region input is correctly populated
        let regionHidden = document.querySelector('#region_hidden');
        if (regionHidden) {
            regionHidden.value = region;
        }

        // Clear dependent dropdowns
        categoryDropdown.innerHTML = '<option value="">Please Select</option>';
        subCategoryDropdown.innerHTML = '<option value="">Please Select Site Category First</option>';
        clientDropdown.innerHTML = '<option value="">Select Client</option>';

        updateRealTimeSummary();

        if (!region) return;

        let fetchUrl = window.location.href.split('/Masters/')[0] + "/Masters/Hse_audit/get_categories_by_region";
        console.log('Fetching categories from:', fetchUrl);

        fetch(fetchUrl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "region=" + encodeURIComponent(region)
        })
            .then(res => res.json())
            .then(res => {
                console.log('Categories response:', res);
                if (res.status === 1 && res.categories && res.categories.length > 0) {
                    res.categories.forEach(cat => {
                        let option = document.createElement('option');
                        option.value = cat.category;
                        option.textContent = cat.category;
                        categoryDropdown.appendChild(option);
                    });
                    console.log('Loaded', res.categories.length, 'categories');
                    if (typeof jQuery !== "undefined" && typeof jQuery.fn.select2 !== "undefined") {
                        jQuery(categoryDropdown).trigger('change.select2');
                    }
                } else {
                    console.log('No categories found');
                }
            })
            .catch(err => console.error('Error loading categories:', err));
    }

    // Load Sub Categories when Site Category is selected
    function loadSubCategoriesByCategoryAndRegion() {

        let regionDropdown = document.querySelector('#region_dropdown');
        let regionHidden = document.querySelector('#region_hidden');
        let categoryDropdown = document.querySelector('#main_category');
        let subCategoryDropdown = document.querySelector('#sub_category');
        let clientDropdown = document.querySelector('#client_id');

        if (!categoryDropdown || !subCategoryDropdown) return;

        let region = regionDropdown?.value || regionHidden?.value;
        let category = categoryDropdown.value;

        subCategoryDropdown.innerHTML = '<option value="">Please Select</option>';
        clientDropdown.innerHTML = '<option value="">Select Client</option>';

        updatePerformAuditBy();

        if (!region || !category) return;

        fetch("<?= base_url('Masters/Hse_audit/get_subcategories_by_region_and_category') ?>", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "region=" + encodeURIComponent(region) + "&category=" + encodeURIComponent(category)
        })
            .then(res => res.json())
            .then(res => {

                if (res.status === 1 && res.subcategories) {

                    res.subcategories.forEach(subcat => {

                        let option = document.createElement('option');
                        option.value = subcat.sub_category;
                        option.textContent = subcat.sub_category;

                        subCategoryDropdown.appendChild(option);

                    });

                    if (typeof jQuery !== "undefined" && typeof jQuery.fn.select2 !== "undefined") {
                        jQuery(subCategoryDropdown).trigger('change.select2');
                    }
                }
            })
            .catch(err => console.error(err));
    }

    // Load Clients when Sub Category is selected
    function loadClientsByRegionCategorySubcategory() {
        let regionDropdown = document.querySelector('#region_dropdown');
        let categoryDropdown = document.querySelector('#main_category');
        let subCategoryDropdown = document.querySelector('#sub_category');
        let clientDropdown = document.querySelector('#client_id');
        let clientNameHidden = document.querySelector('#client_name');

        if (!regionDropdown || !categoryDropdown || !subCategoryDropdown || !clientDropdown) {
            console.error('Required dropdowns not found for loading clients');
            return;
        }

        let region = regionDropdown.value;
        let category = categoryDropdown.value;
        let subCategory = subCategoryDropdown.value;

        console.log('Loading clients for region:', region, 'category:', category, 'sub_category:', subCategory);

        // Clear client dropdown
        clientDropdown.innerHTML = '<option value="">Select Client</option>';
        if (clientNameHidden) clientNameHidden.value = "";

        if (!region || !category || !subCategory) {
            console.log('Missing required values - region:', region, 'category:', category, 'sub_category:', subCategory);
            return;
        }

        let fetchUrl = window.location.href.split('/Masters/')[0] + "/Masters/Hse_audit/get_clients_by_region_category_subcategory";
        console.log('Fetching clients from:', fetchUrl);

        fetch(fetchUrl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "region=" + encodeURIComponent(region) + "&category=" + encodeURIComponent(category) + "&sub_category=" + encodeURIComponent(subCategory)
        })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok: ' + res.status);
                }
                return res.json();
            })
            .then(res => {
                console.log('Clients response:', res);
                if (res.status === 1 && res.clients && res.clients.length > 0) {
                    console.log('Found', res.clients.length, 'clients');

                    // Check if Select2 is initialized
                    let isSelect2Initialized = false;
                    if (typeof jQuery !== "undefined" && typeof jQuery.fn.select2 !== "undefined") {
                        try {
                            isSelect2Initialized = jQuery('#client_id').data('select2') !== undefined;
                            console.log('Select2 initialized:', isSelect2Initialized);
                        } catch (e) {
                            console.log('Error checking Select2:', e);
                        }
                    }

                    // Clear existing options first
                    clientDropdown.innerHTML = '<option value="">Select Client</option>';

                    // Debug: log first client to see structure
                    if (res.clients.length > 0) {
                        console.log('Sample client object from response:', JSON.stringify(res.clients[0], null, 2));
                    }

                    // Add all client options
                    res.clients.forEach((client, index) => {

                        let option = document.createElement('option');

                        let clientId = client.client_id || client.location_name || client.location || '';
                        let clientName = client.client_name || '';
                        let locationName = client.location_name || client.location || client.client_id || '';
                        let cluster = client.cluster || '';
                        let accountManager = client.account_manager || '';

                        if (!clientId || clientId.trim() === '' || clientId === null) {

                            if (locationName && locationName.trim() !== '') {
                                clientId = locationName;
                            }
                            else if (clientName && clientName.trim() !== '') {
                                clientId = clientName;
                            }
                            else {
                                clientId = 'client_' + Date.now() + '_' + index;
                            }
                        }

                        option.value = clientId;

                        let displayText = '';

                        if (clientName && locationName && locationName !== clientName) {
                            displayText = clientName + ' - ' + locationName;
                        }
                        else if (clientName) {
                            displayText = clientName;
                        }
                        else if (locationName) {
                            displayText = locationName;
                        }
                        else {
                            displayText = clientId;
                        }

                        option.textContent = displayText;

                        option.setAttribute('data-client-name', clientName || locationName || clientId);
                        option.setAttribute('data-location', locationName || clientName || clientId);

                        // ✅ ADD THESE TWO
                        option.setAttribute('data-cluster', cluster);
                        option.setAttribute('data-account-manager', accountManager);

                        clientDropdown.appendChild(option);

                    });

                    console.log('Total options in dropdown:', clientDropdown.options.length);

                    // Update Select2 if it's initialized
                    if (typeof jQuery !== "undefined" && typeof jQuery.fn.select2 !== "undefined") {
                        jQuery('#client_id').trigger('change.select2');
                    } else {
                        // If Select2 is not available, use native change event
                        clientDropdown.removeEventListener('change', clientOnselect);
                        clientDropdown.addEventListener('change', clientOnselect);
                        console.log('Using native select (Select2 not available)');
                    }
                } else {
                    console.log('No clients found - status:', res.status, 'clients:', res.clients);
                    if (res.message) {
                        console.log('Server message:', res.message);
                    }

                    // Clear dropdown and show message
                    clientDropdown.innerHTML = '<option value="">No clients found</option>';
                    if (typeof jQuery !== "undefined" && typeof jQuery.fn.select2 !== "undefined") {
                        jQuery('#client_id').trigger('change.select2');
                    }
                }
            })
            .catch(err => {
                console.error('Error loading clients:', err);
                alert('Error loading clients. Please check console for details.');
            });

        // Also trigger table toggle
        toggleAuditTable();
    }

    function updateSubCategories() {
        // This function is now replaced by loadSubCategoriesByCategoryAndRegion
        loadSubCategoriesByCategoryAndRegion();
    }

    function updatePerformAuditBy() {

        let siteCat = document.querySelector("#main_category").value;
        let hiddenInput = document.querySelector("#perform_audit_by");

        if (!hiddenInput) return;

        if (!siteCat) {
            hiddenInput.value = "";
            return;
        }

        hiddenInput.value = siteCat
            .toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/-/g, '_')
            .replace(/&/g, '')
            .replace(/__+/g, '_')
            .trim();
    }

    function loadClientsByRegionAndCategory() {
        // Deprecated - replaced by loadClientsByRegionCategorySubcategory
        loadClientsByRegionCategorySubcategory();
    }

    function clientOnselect() {

        let clientDropdown = document.querySelector('#client_id');
        let clientNameHidden = document.querySelector('#client_name');
        let locationHidden = document.querySelector('input[name="location"]');
        let clusterHidden = document.querySelector('#cluster_name');
        let accountManagerHidden = document.querySelector('#account_manager');

        if (!clientDropdown) {
            console.error('Client dropdown not found');
            return;
        }

        let selectedValue = '';
        let option = null;

        // Handle Select2 or normal dropdown
        if (typeof jQuery !== "undefined" &&
            typeof jQuery.fn.select2 !== "undefined" &&
            jQuery('#client_id').data('select2')) {

            selectedValue = jQuery('#client_id').val();
            option = clientDropdown.querySelector('option[value="' + selectedValue + '"]');

        } else {

            option = clientDropdown.options[clientDropdown.selectedIndex];
            selectedValue = option ? option.value : '';

        }

        if (!option || !selectedValue) {

            if (clientNameHidden) {
                clientNameHidden.value = "";
                clientNameHidden.dispatchEvent(new Event('change'));
            }
            if (locationHidden) locationHidden.value = "";
            if (clusterHidden) clusterHidden.value = "";
            if (accountManagerHidden) accountManagerHidden.value = "";

            return;
        }

        let clientName = option.getAttribute('data-client-name') || "";
        let location = option.getAttribute('data-location') || "";
        let cluster = option.getAttribute('data-cluster') || "";
        let accountManager = option.getAttribute('data-account-manager') || "";

        // Fallback logic if attributes missing
        if (!clientName && option.textContent) {

            let displayText = option.textContent.trim();

            if (displayText.includes(' - ')) {

                let parts = displayText.split(' - ');
                clientName = parts[0];
                location = parts[1] || location;

            } else {

                clientName = displayText;
                location = location || displayText;

            }
        }

        // Set values
        if (clientNameHidden) {
            clientNameHidden.value = clientName;
            clientNameHidden.dispatchEvent(new Event('change'));
        }

        if (locationHidden) {
            locationHidden.value = location ? location : clientName;
            locationHidden.dispatchEvent(new Event('change'));
        }

        if (clusterHidden) clusterHidden.value = cluster;

        if (accountManagerHidden) accountManagerHidden.value = accountManager;

        console.log("Client:", clientName);
        console.log("Location:", location);
        console.log("Cluster:", cluster);
        console.log("Account Manager:", accountManager);

        updateRealTimeSummary();
        loadAuditQuestions();
    }

    function loadAuditQuestions() {

        let performAuditBy = document.querySelector('#main_category')?.value || document.querySelector('#perform_audit_by')?.value;

        /* --------------------------------------------------
           Detect Site Category dynamically from NAME_LIST
        ---------------------------------------------------*/
        let selectedText = "";
        let mainCategory = document.querySelector('#main_category');
        if (mainCategory) {
            if (mainCategory.tagName === "SELECT") {
                selectedText = mainCategory.options[mainCategory.selectedIndex]?.text || "";
            } else {
                selectedText = mainCategory.value || mainCategory.textContent || "";
            }
        } else {
            selectedText = performAuditBy;
        }

        if (selectedText) {
            let matched = NAME_LIST.find(item => 
                item.display.toLowerCase() === selectedText.toLowerCase() || 
                item.hse_type.toLowerCase() === selectedText.toLowerCase() ||
                item.display.toLowerCase().replace(/ /g, '_') === selectedText.toLowerCase()
            );

            if (matched) {
                performAuditBy = matched.hse_type;
            } else {
                performAuditBy = performAuditBy ? performAuditBy.toLowerCase().replace(/ /g, '_') : "";
            }
        }

        ACTIVE_SITE_CATEGORY = performAuditBy || ACTIVE_SITE_CATEGORY || "";

        /* --------------------------------------------------
           Get selected values
        ---------------------------------------------------*/

        let siteCategory = document.querySelector('#main_category')?.value || "";
        let subCategory = document.querySelector('#sub_category')?.value || "";

        let hseAuditIdEle =
            document.querySelector('input[name="hse_id"]') ||
            document.querySelector('input[name="hse_audit_id"]');

        let hseAuditId = hseAuditIdEle ? hseAuditIdEle.value : "";

        let clientId = document.querySelector('#client_id')?.value || "";

        if (!siteCategory || !subCategory) return;

        /* --------------------------------------------------
           Build request
        ---------------------------------------------------*/

        let formData = new FormData();

        formData.append('site_category', siteCategory);
        formData.append('sub_category', subCategory);

        if (hseAuditId) {
            formData.append('hse_audit_id', hseAuditId);
        }

        let auditTemplateIdEl = document.querySelector('input[name="audit_template_id"]');

        if (auditTemplateIdEl && auditTemplateIdEl.value) {
            formData.append('audit_template_id', auditTemplateIdEl.value);
        }

        /* --------------------------------------------------
           Fetch audit questions
        ---------------------------------------------------*/

        fetch("<?= base_url('Masters/Hse_audit/get_audit_questions_ajax') ?>", {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {

                if (data.status === 1) {
                    renderAuditQuestions(
                        data.questions,
                        data.prefill,
                        performAuditBy
                    );

                    /* Reattach checkbox events */
                    document.querySelectorAll('input.checkbox').forEach(cb => {

                        cb.addEventListener("change", function () {
                            singleSelectHSE(this);
                        });

                    });

                    toggleAuditTable();
                    document.dispatchEvent(new Event('auditQuestionsLoaded'));

                } else {

                    let tbody = document.querySelector('#auditTableBody');

                    if (tbody) {
                        tbody.innerHTML =
                            "<tr><td colspan='8'>No questions found</td></tr>";
                    }

                }

            })
            .catch(err => console.error(err));

    }

    function renderAuditQuestions(questions, prefillMap, activeSiteCategory) {

        let tbody = document.querySelector('#auditTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        let isReauditOrEdit =
            document.querySelector('input[name="hse_audit_id"]') ||
            document.querySelector('input[name="hse_id"]');

        let shownHeaders = new Set();

        questions.forEach((q, index) => {

            let qId = q.question_id || null;
            let questionNameStr = (q.audit_category || q.category || '').trim();
            let auditQuestionStr = q.audit_question || q.question_text || '';

            let rowKey = qId ? parseInt(qId) : (questionNameStr + "|" + auditQuestionStr);
            let prefill = prefillMap[rowKey] || {};

            let isMisc =
                (q.audit_category && q.audit_category.toLowerCase() === "miscellaneous") ||
                (q.category && q.category.toLowerCase() === "miscellaneous") ||
                (q.question_name && q.question_name.toLowerCase() === "miscellaneous") ||
                (q.audit_parameter && q.audit_parameter.toLowerCase() === "miscellaneous");

            // ✅ Always show DB question if available, or prefill for reaudit Miscellaneous
            let questionText = q.audit_question || q.question_text || "";
            if (isMisc && isReauditOrEdit && prefill.audit_question) {
                questionText = prefill.audit_question;
            }

            let noteText = prefill.note || q.note || q.note_text || '';

            // Reaudit NC closed override: If NC status is closed (3) and original finding was NO
            let forceYesNCclosed = false;
            let rowStyle = '';
            if ((prefill.nc_status === 3 || String(prefill.nc_status) === '3') && (String(prefill.finding || '').toUpperCase() === 'NO')) {
                forceYesNCclosed = true;
                rowStyle = 'nc-closed-row';
            }

            /* ---------------- CATEGORY HEADER ---------------- */

            let catId = String(q.audit_category_id || "").trim().toUpperCase();
            catId = catId.replace(/\.$/, '');

            let isHeader = /^[A-Z]$/.test(catId);

            // ✅ SHOW HEADER ONLY ONCE
            if (isHeader && !shownHeaders.has(catId)) {

                shownHeaders.add(catId);

                let headerHtml = `
                <tr class="audit-row category-header-row"
                    data-category="${questionNameStr.replace(/"/g, '&quot;')}"
                    style="background:#f0f0f0;font-weight:bold;">

                    <td colspan="${3 + NAME_LIST.length + 2}"
                        style="text-align:center;padding:15px;font-size:16px;background:#e0e0e0;">

                        ${catId}. ${questionNameStr}

                        <input type="hidden" name="question_name[${index}]" value="${questionNameStr}">
                        <input type="hidden" name="question_audit_id[${index}]" value="${q.question_id || ''}">
                        <input type="hidden" name="is_misc[${index}]" value="0">
                        <input type="hidden" name="audit_category_id[${index}]" value="${catId}">
                        <input type="hidden" name="audit_question[${index}]" value="${q.audit_question || questionNameStr}">
                        <input type="hidden" name="remark[${index}]" value="">
                        <input type="hidden" name="existing_attachment[${index}]" value="">
                    </td>
                </tr>`;

                tbody.insertAdjacentHTML("beforeend", headerHtml);
                return; // 🚀 header only, do not render as question row
            }

            /* ---------------- SITE CATEGORY COLUMNS ---------------- */

            let siteColsHtml = "";
            let rowFinding = "";

            NAME_LIST.forEach(cat => {

                let hseType = cat.hse_type;
                let activeType = activeSiteCategory;

                let savedValue = (prefill[hseType] || "").toUpperCase();
                let genericFinding = (prefill.finding || "").toUpperCase();
                let defaultValue = (q.audit_question_default_value || q.default_value || "").toUpperCase();

                let finalValue = "";
                if (savedValue !== "") {
                    finalValue = savedValue;
                } else if (hseType === activeType && genericFinding !== "") {
                    finalValue = genericFinding;
                } else if (activeType === hseType) {
                    finalValue = defaultValue;
                } else if (defaultValue === "NA") {
                    finalValue = "NA";
                }

                // For closed NC with NO finding, auto-steer to YES for active site column
                if (forceYesNCclosed && hseType === activeType) {
                    finalValue = 'YES';
                }

                if (hseType === activeType) {
                    rowFinding = finalValue;
                }

                siteColsHtml += `
<td class="finding-cell" data-hse-col="${hseType}">



    <label>
        <input type="checkbox" class="checkbox"
            value="YES" data-type="${hseType}"
            ${finalValue === "YES" ? "checked" : ""}
            onclick="singleSelectHSE(this)"> YES
    </label>

    <label>
        <input type="checkbox" class="checkbox no_checkbox"
            value="NO" data-type="${hseType}"
            ${finalValue === "NO" ? "checked" : ""}
            onclick="singleSelectHSE(this)"> NO
    </label>

    <label>
        <input type="checkbox" class="checkbox"
            value="NA" data-type="${hseType}"
            ${finalValue === "NA" ? "checked" : ""}
            onclick="singleSelectHSE(this)"> NA
    </label>

</td>`;
            });

            /* ---------------- QUESTION ROW ---------------- */

            let html = `
        <tr class="audit-row ${rowStyle}"
            data-category="${questionNameStr.replace(/"/g, '&quot;')}"
            style="font-weight:bold;">

            <td>
                <input type="hidden" name="question_name[${index}]" value="${questionNameStr}">
                <input type="hidden" name="question_audit_id[${index}]" value="${q.question_id || ''}">
                <input type="hidden" name="is_misc[${index}]" value="${isMisc ? "1" : "0"}">
                <input type="hidden" name="audit_category_id[${index}]" value="${q.audit_category_id || ''}">
                <input type="hidden" name="capa_json[${index}]" class="capa_json" value='${(prefill.capa_json || "").replace(/'/g, "&#39;")}'>
                <input type="hidden" name="nc_type[${index}]" class="nc_type_input" value="${q.nc_type || 'NC'}">
                <input type="hidden" name="finding[${index}]" class="finding_hidden" value="${rowFinding}">
                
                ${q.audit_category_id}
            </td>
            <td class="category-cell">${questionNameStr}</td>

            <td>
                ${isMisc
                    ? `<input type="text" name="audit_question[${index}]"
                        class="form-control"
                        value="${questionText}"
                        placeholder="Enter Audit Question"
                        ${questionText !== '' ? 'readonly' : ''}
                        style="background:${questionText !== '' ? '#f8f9fa' : '#fff'};">`
                    : `<input type="hidden" name="audit_question[${index}]"
                        value="${questionText}">${questionText}`
                }
            </td>

            ${siteColsHtml}

            <td style="display:none;">
                ${noteText ? `<button type="button" class="btn btn-sm btn-primary note-modal-trigger" onclick="openNoteModal(this)" data-note="${String(noteText).replace(/`/g, '&#96;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;')}" data-question="${String(questionText || 'Note').replace(/`/g, '&#96;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;')}">View</button>
                    <input type="hidden" name="note[${index}]" value="${String(noteText).replace(/`/g, '&#96;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;')}">` : `<span class="text-muted">-</span>
                    <input type="hidden" name="note[${index}]" value="">`}
            </td>

            <td class="remark-cell">
    <textarea name="remark[${index}]" class="form-control"></textarea>
</td>

            <td>
                ${prefill.attachment
                    ? `<div class="existing-attachment" style="margin-bottom:6px">
                        <a href="<?= base_url() ?>/${prefill.attachment}" target="_blank">
                           Existing Attachment
                        </a>
                       </div>
                       <input type="hidden" name="existing_attachment[${index}]"
                              value="${prefill.attachment}">`
                    : `<input type="hidden" name="existing_attachment[${index}]" value="">`
                }

                <input type="file"
                       name="attachment[${index}]"
                       accept=".jpg,.jpeg,.png,.gif,.pdf,.xls,.xlsx"
                       onchange="uploadAttachmentAsync(this)">
            </td>
        </tr>`;

            tbody.insertAdjacentHTML("beforeend", html);
        });

        /* ---------------- CATEGORY FILTER ---------------- */

        let categories = [];
        let seenCats = new Set();

        questions.forEach(q => {
            let catId = String(q.audit_category_id || "").trim().toUpperCase().replace(/\.$/, '');
            let catName = (q.audit_category || '').trim();

            if (/^[A-Z]$/.test(catId) && catName && !seenCats.has(catName)) {
                seenCats.add(catName);
                categories.push({
                    letter: catId,
                    name: catName
                });
            }
        });

        let catSelect = document.querySelector("#categoryFilterHSE");
        if (catSelect) {
            let opts = `<option value="all">All Categories</option>`;
            categories.forEach(catObj => {
                opts += `<option value="${catObj.name}">${catObj.letter}. ${catObj.name}</option>`;
            });
            catSelect.innerHTML = opts;
        }
        // Always show remark column
        document.querySelectorAll('.remark-cell').forEach(el => {
            el.style.display = 'table-cell';
        });
        document.querySelectorAll('.note-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const noteContent = button.closest('td')?.querySelector('.note-content');
                if (!noteContent) return;

                if (!noteContent.textContent.trim()) {
                    noteContent.textContent = button.getAttribute('data-note') || '';
                }

                noteContent.style.display = noteContent.style.display === 'none' ? 'block' : 'none';
            });
        });

        // Note Modal Handler
        const noteModalOverlay = document.getElementById('noteModalOverlay');
        const noteModalClose = document.getElementById('noteModalClose');

        if (noteModalOverlay) {
            document.querySelectorAll('.note-modal-trigger').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const noteText = this.getAttribute('data-note') || '';
                    const questionText = this.getAttribute('data-question') || 'Note';

                    document.getElementById('noteModalQuestion').textContent = questionText;
                    document.getElementById('noteModalText').textContent = noteText;
                    noteModalOverlay.classList.add('active');
                });
            });

            noteModalClose?.addEventListener('click', () => {
                noteModalOverlay.classList.remove('active');
            });

            noteModalOverlay.addEventListener('click', (e) => {
                if (e.target === noteModalOverlay) {
                    noteModalOverlay.classList.remove('active');
                }
            });
        }

        /* ---------------- CATEGORY SUMMARY ---------------- */

        let summaryTbody = document.querySelector("#summaryCategoryTable tbody");

        if (summaryTbody) {
            summaryTbody.innerHTML = "";

            let categories = [];
            let seenLetters = new Set();

            questions.forEach(q => {

                let rawCatId = String(q.audit_category_id || "").trim().toUpperCase();
                let catName = (q.audit_category || '').trim();

                if (!rawCatId || !catName) return;

                // take first letter from A / A.1 / A.38 / B / B.1
                let mainLetter = rawCatId.split('.')[0];

                if (/^[A-Z]$/.test(mainLetter) && !seenLetters.has(mainLetter)) {
                    seenLetters.add(mainLetter);

                    categories.push({
                        letter: mainLetter,
                        name: catName
                    });
                }
            });

            // sort A, B, C, D...
            categories.sort((a, b) => a.letter.localeCompare(b.letter));

            categories.forEach(catObj => {

                let siteStatsCells = "";

                NAME_LIST.forEach(s => {
                    siteStatsCells += `
                <td class="c-${s.hse_type}" data-category-type="${s.hse_type}">
                    0%
                </td>`;
                });

                let row = `
            <tr data-cat-name="${catObj.name.replace(/"/g, '&quot;')}" data-cat-letter="${catObj.letter}">
                <td>${catObj.letter}</td>
                <td class="text-start">${catObj.name}</td>
                ${siteStatsCells}
            </tr>`;

                summaryTbody.insertAdjacentHTML("beforeend", row);
            });
        }

        setTimeout(updateRealTimeSummary, 0);

        // Re-initialize note modal handlers for dynamically added rows
        //initializeNoteModalHandlers();
    }

    function hideExistingAttachment(input) {
        const parent = input.closest('td') || input.closest('th');
        const existing = parent?.querySelectorAll('.existing-attachment');
        if (existing) {
            existing.forEach(e => e.style.display = 'none');
        }
    }

    function uploadAttachmentAsync(input) {
        if(typeof validateFileType === 'function') validateFileType(input);
        hideExistingAttachment(input);
        
        if (!input.files || input.files.length === 0) return;
        
        let file = input.files[0];
        let auditNo = document.querySelector('input[name="audit_no"]')?.value || 'DRAFT';
        
        let formData = new FormData();
        formData.append('file', file);
        formData.append('audit_no', auditNo);
        
        let indexMatch = input.name.match(/attachment\[(\d+)\]/);
        if (!indexMatch) return;
        let index = indexMatch[1];
        
        let parent = input.closest('td') || input.closest('th');
        if (!parent) return;
        
        let loading = document.createElement('div');
        loading.className = 'upload-loading';
        loading.innerHTML = '<small style="color:orange">Uploading draft...</small>';
        parent.appendChild(loading);
        
        fetch("<?= base_url('Masters/Hse_audit/upload_draft_attachment') ?>", {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            loading.remove();
            if (data.status === 1) {
                let existingInput = document.querySelector('input[name="existing_attachment[' + index + ']"]');
                if (existingInput) {
                    existingInput.value = data.path;
                    existingInput.dispatchEvent(new Event('change', { bubbles: true })); // Trigger autosave
                }
                
                let success = document.createElement('div');
                success.className = 'upload-success existing-attachment';
                success.innerHTML = `<small style="color:green">Draft Saved: <a href="<?= base_url() ?>/${data.path}" target="_blank">View File</a></small>`;
                parent.appendChild(success);
            }
        })
        .catch(err => {
            if (loading) loading.remove();
            console.error(err);
        });
    }

    function openNoteModal(button) {
        if (!button) return;

        let noteText = button.getAttribute('data-note') || '';
        let questionText = button.getAttribute('data-question') || 'Note';

        // ✅ Decode HTML safely
        let txt = document.createElement("textarea");
        txt.innerHTML = noteText;
        noteText = txt.value;

        document.getElementById("noteModalQuestion").innerText = questionText;
        document.getElementById("noteModalText").innerText = noteText;

        document.getElementById("noteModalOverlay").classList.add("active");
    }

    document.addEventListener("DOMContentLoaded", function () {

        const modal = document.getElementById("noteModalOverlay");
        const closeBtn = document.getElementById("noteModalClose");

        if (closeBtn) {
            closeBtn.addEventListener("click", function () {
                modal.classList.remove("active");
            });
        }

        // Click outside modal → close
        if (modal) {
            modal.addEventListener("click", function (e) {
                if (e.target === modal) {
                    modal.classList.remove("active");
                }
            });
        }

    });

    //     let table = $('#auditTableContainer');
    //     table.style.display = ACTIVE_SITE_CATEGORY ? "block" : "none";

    //     // Hide all scorable site categories
    //     const allCategories = ['client_leased', 'fm_leased', 'office'];

    //     allCategories.forEach(col => {
    //         // Use attribute selector with ~= to match class in a list of classes
    //         let elements = document.querySelectorAll(`th[class~="${col}"], td[class~="${col}"]`);
    //         console.log(`Hiding ${col}: found ${elements.length} elements`); // DEBUG
    //         elements.forEach(c => c.style.display = 'none');
    //     });

    //     // Show only the selected category
    //     if (ACTIVE_SITE_CATEGORY) {
    //         let elementsToShow = document.querySelectorAll(`th[class~="${ACTIVE_SITE_CATEGORY}"], td[class~="${ACTIVE_SITE_CATEGORY}"]`);
    //         console.log(`Showing ${ACTIVE_SITE_CATEGORY}: found ${elementsToShow.length} elements`); // DEBUG
    //         elementsToShow.forEach(c => {
    //             c.style.display = 'table-cell';
    //         });
    //     }

    //     // ⏱ Run after DOM update
    //     setTimeout(() => {
    //         calculateScore();
    //         applyFilters();
    //         updateRealTimeSummary();
    //     }, 50);
    // }



    /********************************************************************
     * SHOW / HIDE TABLE BASED ON SELECTED SITE CATEGORY
     ********************************************************************/
    function toggleAuditTable() {
        let performAudit = document.querySelector("#perform_audit_by");

        // Normal audit
        if (performAudit && performAudit.value) {
            ACTIVE_SITE_CATEGORY = performAudit.value;
        }

        let tableContainer = document.querySelector("#auditTableContainer");

        if (!tableContainer) return;

        tableContainer.style.display = ACTIVE_SITE_CATEGORY ? "block" : "none";

        // Hide all site columns
        document.querySelectorAll("[data-hse-col]").forEach(col => {
            col.style.display = "none";
        });

        // Show only active column
        if (ACTIVE_SITE_CATEGORY) {
            document.querySelectorAll(`[data-hse-col="${ACTIVE_SITE_CATEGORY}"]`)
                .forEach(col => {
                    col.style.display = "table-cell";
                });
        }
    }


    /********************************************************************
     * SINGLE SELECT CHECKBOX (YES / NO / NA)
     ********************************************************************/
    function singleSelectHSE(checkbox) {
        let row = checkbox.closest("tr");
        if (!row) return;

        let type = checkbox.dataset.type;

        // ✅ Uncheck other checkboxes in same row column but allow toggle off
        row.querySelectorAll(`input.checkbox[data-type="${type}"]`)
            .forEach(cb => {
                if (cb !== checkbox) {
                    cb.checked = false;
                }
            });

        // ✅ Update hidden input field
        let hiddenInput = row.querySelector('.finding_hidden');
        if (hiddenInput) {
            hiddenInput.value = checkbox.checked ? checkbox.value : '';
            hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        const isMiscInput = row.querySelector('input[name^="is_misc"]');
        const isMisc = isMiscInput && isMiscInput.value === '1';
        const isCategoryV = isCategoryVRow(row);
        const isNoCheckbox = checkbox.classList.contains('no_checkbox') || checkbox.value === 'NO';

        if (isNoCheckbox && checkbox.checked) {

            // Only open modal if Question text is entered for Miscellaneous
            if (isMisc) {
                const questionInput = row.querySelector('input[name^="audit_question"]');
                if (questionInput && questionInput.value.trim() === '') {
                    alert("Please enter Audit Question before selecting NO.");
                    checkbox.checked = false;
                    if (hiddenInput) hiddenInput.value = '';
                    return;
                }
            }

            // AJAX fetch CAPA JSON if it doesn't exist locally yet
            const hseAuditId = document.querySelector('input[name="hse_audit_id"]')?.value;
            const categoryId = row.querySelector('input[name^="audit_category_id"]')?.value;
            const questionId = row.querySelector('input[name^="question_audit_id"]')?.value;
            const capaInput = row.querySelector('.capa_json');

            if ((categoryId || questionId) && (!capaInput || !capaInput.value)) {
                $.ajax({
                    url: "<?= base_url('Masters/Hse_audit/get_capa_by_question') ?>",
                    method: "POST",
                    data: {
                        audit_id: hseAuditId || '',
                        category_id: categoryId,
                        question_id: questionId
                    },
                    success: function (res) {
                        if (res.status && res.data) {
                            if (!capaInput) {
                                // Create input if missing
                                const rowKeyMatch = row.querySelector('input[name^="question_name"]')?.name.match(/\[(\d+)\]/);
                                const index = rowKeyMatch ? rowKeyMatch[1] : 0;
                                const newInput = document.createElement('input');
                                newInput.type = 'hidden';
                                newInput.name = `capa_json[${index}]`;
                                newInput.className = 'capa_json';
                                newInput.value = JSON.stringify(res.data);
                                row.appendChild(newInput);
                            } else {
                                capaInput.value = JSON.stringify(res.data);
                            }
                            let activeCapaInput = row.querySelector('.capa_json');
                            if (activeCapaInput) activeCapaInput.dispatchEvent(new Event('change', { bubbles: true }));
                            openCapaModalForRow(row);
                        } else {
                            if (isMisc || isCategoryV) {
                                openCapaModalForRow(row);
                            } else {
                                alert("CAPA JSON is not available in the Question Master. You cannot select NO for this question.");
                                checkbox.checked = false;
                                if (hiddenInput) hiddenInput.value = '';
                                calculateScore();
                                updateRealTimeSummary();
                            }
                        }
                    },
                    error: function () {
                        if (isMisc || isCategoryV) {
                            openCapaModalForRow(row);
                        } else {
                            alert("Failed to fetch CAPA details. Please try again.");
                            checkbox.checked = false;
                            if (hiddenInput) hiddenInput.value = '';
                            calculateScore();
                            updateRealTimeSummary();
                        }
                    }
                });
            } else {
                openCapaModalForRow(row);
            }

        } else {
            // ✅ If YES or NA selected → Strict Rule: Remove existing CAPA JSON
            const capaInput = row.querySelector('.capa_json');
            if (capaInput) {
                capaInput.value = '';
                capaInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // ✅ Remove UI indicators
            row.classList.remove('capa-saved');

            const indicator = row.querySelector('.capa-indicator');
            if (indicator) {
                indicator.remove();
            }

            const editBtn = row.querySelector('.capa-edit-btn-container');
            if (editBtn) {
                editBtn.remove();
            }
        }

        calculateScore();
        updateRealTimeSummary();
    }


    function openCapaModalForRow(row) {

        const modalEl = document.getElementById('capaModal');
        if (!modalEl) return;

        const form = document.getElementById('capaForm');
        form.reset();
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const existingJson = row.querySelector('.capa_json')?.value || '';
        const recommendationInput = document.getElementById('recommendation_checkbox');

        if (existingJson) {
            try {
                const data = JSON.parse(existingJson);

                // Prefill with robust check for both new (data.findings.no) and old (data.findings.value) structure
                const prefillField = (id, fieldData) => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.value = fieldData?.no || fieldData?.value || '';
                    }
                };

                prefillField('capa_findings', data.findings);
                prefillField('capa_risk', data.risk);
                prefillField('capa_actions', data.actions);
                prefillField('capa_action_category', data.action_category);
                prefillField('capa_ua_uc', data.ua_uc);
                prefillField('capa_risk_severity', data.risk_severity);
                prefillField('capa_risk_probability', data.risk_probability);
                prefillField('capa_color_code', data.color_code);
                prefillField('capa_cost_type', data.cost_type);
                prefillField('capa_combined_risk_rating', data.combined_risk_rating);

                if (recommendationInput) {
                    const existingNcType = row.querySelector('.nc_type_input')?.value || '';
                    const jsonNcType = data.nc_type?.no || data.nc_type?.value || '';
                    const findingText = data.findings?.no || data.findings?.value || '';

                    const isReauditOrEdit = document.querySelector('input[name="hse_audit_id"]') !== null;
                    const isSavedRecord = isReauditOrEdit || row.classList.contains('capa-saved');

                    let finalNcType = 'RD'; // Default to RD (checked)

                    if (jsonNcType === 'NC' || jsonNcType === 'RD') {
                        // 1. Trust JSON if it explicitly has the NC type saved
                        finalNcType = jsonNcType;
                    } else if (isSavedRecord && findingText && findingText.trim() !== '') {
                        // 2. If it is a legitimately saved CAPA record (has findings) but JSON didn't have nc_type, fallback to hidden input
                        finalNcType = existingNcType || 'RD';
                    } else {
                        // 3. New record or default JSON from master -> Checkbox should be checked
                        finalNcType = 'RD';
                    }

                    recommendationInput.checked = (finalNcType === 'RD');
                }

                calculateRiskRating();

            } catch (error) {
                console.error("CAPA JSON Prefill Error:", error);
                form.reset();
                if (recommendationInput) recommendationInput.checked = true;
            }
        } else {
            // New question, no CAPA JSON yet
            form.reset();
            if (recommendationInput) recommendationInput.checked = true;
        }

        $(modalEl).data('row', row);
        $(modalEl).data('saved', false);

        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();
    }

    function saveCapaData() {

        const modalEl = document.getElementById('capaModal');
        const form = document.getElementById('capaForm');
        let isValid = true;

        // ✅ VALIDATION (skip hidden + honeypot)
        form.querySelectorAll('input, textarea, select').forEach(field => {

            if (
                field.type === 'hidden' ||
                field.name === 'honeypot' ||
                field.offsetParent === null
            ) {
                return;
            }

            let value = (field.value || '').toString().trim();

            if (value === '') {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            alert('All fields are required.');
            return;
        }

        // ✅ BUILD JSON in proper format with text, yes, no, na keys
        const capaJson = {
            findings: { text: "FINDINGS", yes: "", no: document.getElementById('capa_findings').value.trim(), na: "" },
            risk: { text: "RISK", yes: "", no: document.getElementById('capa_risk').value.trim(), na: "" },
            actions: { text: "ACTIONS", yes: "", no: document.getElementById('capa_actions').value.trim(), na: "" },
            action_category: { text: "ACTION CATEGORY", yes: "", no: document.getElementById('capa_action_category').value.trim(), na: "" },
            ua_uc: { text: "UA UC", yes: "", no: document.getElementById('capa_ua_uc').value.trim(), na: "" },
            risk_severity: { text: "RISK SEVERITY", yes: "", no: document.getElementById('capa_risk_severity').value, na: "" },
            risk_probability: { text: "RISK PROBABILITY", yes: "", no: document.getElementById('capa_risk_probability').value, na: "" },
            color_code: { text: "COLOR CODE", yes: "", no: document.getElementById('capa_color_code').value.trim(), na: "" },
            cost_type: { text: "COST TYPE", yes: "", no: document.getElementById('capa_cost_type').value, na: "" },
            combined_risk_rating: { text: "COMBINED RISK_RATING", yes: "", no: document.getElementById('capa_combined_risk_rating').value, na: "" },
            nc_type: {
                text: "NC TYPE",
                yes: "",
                no: document.getElementById('recommendation_checkbox').checked ? 'RD' : 'NC',
                na: ""
            }
        };

        // ✅ GET ROW (FIX jQuery issue)
        let row = $(modalEl).data('row');
        if (!row) {
            alert('No question row selected.');
            return;
        }

        const rowEl = row instanceof jQuery ? row[0] : row;

        // ✅ FIND / CREATE INPUT
        let capaJsonInput = rowEl.querySelector('.capa_json');
        let ncTypeInput = rowEl.querySelector('.nc_type_input');

        if (!capaJsonInput) {

            const rowKey = rowEl.querySelector('input[name^="question_name"]');
            const match = rowKey ? rowKey.name.match(/\[(\d+)\]/) : null;
            const index = match ? match[1] : 0;

            capaJsonInput = document.createElement('input');
            capaJsonInput.type = 'hidden';
            capaJsonInput.name = `capa_json[${index}]`;
            capaJsonInput.className = 'capa_json';

            ncTypeInput = document.createElement('input');
            ncTypeInput.type = 'hidden';
            ncTypeInput.name = `nc_type[${index}]`;
            ncTypeInput.className = 'nc_type_input';

            rowEl.appendChild(capaJsonInput);
            rowEl.appendChild(ncTypeInput);
        }

        // ✅ SAVE JSON
        capaJsonInput.value = JSON.stringify(capaJson);
        capaJsonInput.dispatchEvent(new Event('change', { bubbles: true }));

        if (ncTypeInput) {
            ncTypeInput.value = document.getElementById('recommendation_checkbox').checked ? 'RD' : 'NC';
            ncTypeInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // ✅ MARK SAVED
        $(modalEl).data('saved', true);

        // ✅ UI INDICATOR
        rowEl.classList.add('capa-saved');

        let indicator = rowEl.querySelector('.capa-indicator');

        if (!indicator) {
            indicator = document.createElement('span');
            indicator.className = 'capa-indicator';
            indicator.innerHTML = '✓ CAPA';

            indicator.style.cssText = `
            display:inline-block;
            background:#28a745;
            color:white;
            padding:2px 6px;
            border-radius:3px;
            font-size:11px;
            margin-left:5px;
        `;

            let questionCell = rowEl.querySelector('td:nth-child(3)');
            if (questionCell) {
                questionCell.appendChild(indicator);
            }
        }

        // ✅ CLOSE MODAL
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();

        console.log('CAPA saved successfully');
    }

    function isCategoryVRow(row) {

        const category = row.querySelector('.category-cell')?.textContent.trim().toLowerCase() || '';

        if (!category.includes('ims documents review')) {
            return false;
        }

        // ✅ BEST SOURCE (always correct)
        const categoryIdInput = row.querySelector('input[name^="audit_category_id"]');
        const categoryId = categoryIdInput ? categoryIdInput.value.trim() : '';

        console.log("Category:", category);
        console.log("CategoryId:", categoryId);

        return /^V\.\d+$/i.test(categoryId);
    }

    $('#capaModal').on('hidden.bs.modal', function () {
        const row = $(this).data('row');
        const saved = $(this).data('saved');

        if (row && !saved) {
            const noCheckbox = row.querySelector('input.checkbox.no_checkbox[value="NO"]');
            if (noCheckbox) {
                noCheckbox.checked = false;
            }
            const hiddenInput = row.querySelector('.finding_hidden');
            if (hiddenInput) {
                hiddenInput.value = '';
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            const capaJsonInput = row.querySelector('.capa_json');
            if (capaJsonInput) {
                capaJsonInput.value = '';
                capaJsonInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            const ncTypeInput = row.querySelector('.nc_type_input');
            if (ncTypeInput) {
                ncTypeInput.value = '';
                ncTypeInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            // Remove CAPA indicator if cancelled
            const indicator = row.querySelector('.capa-indicator');
            if (indicator) {
                indicator.remove();
            }
            row.classList.remove('capa-saved');

            // Update scores since finding changed
            calculateScore();
            updateRealTimeSummary();
        }

        $(this).removeData('row');
        $(this).removeData('saved');
        const form = document.getElementById('capaForm');
        if (form) {
            form.reset();
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        }
    });

    let currentRow = null;
    let isSaved = false;

    // NO CLICK - REMOVED: Handled by singleSelectHSE function

    // CANCEL - REMOVED: Handled by Bootstrap modal events

    // SAVE - REMOVED: Handled by saveCapaData function


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
        if (!ACTIVE_SITE_CATEGORY) {
            document.getElementById("scoreDisplay").innerText = "0";
            document.getElementById("finalScoreDisplayText").value = "0";
            return;
        }

        let yes = 0;
        let no = 0;

        document.querySelectorAll(
            `.finding-cell[data-hse-col="${ACTIVE_SITE_CATEGORY}"]`
        ).forEach(cell => {

            let checked = cell.querySelector("input.checkbox:checked");

            if (!checked) return;

            let val = checked.value.toUpperCase();

            if (val === "YES") yes++;
            if (val === "NO") no++;

        });

        let denom = yes + no;

        let score = denom ? ((yes / denom) * 100).toFixed(2) : "0";

        document.getElementById("scoreDisplay").innerText = score;
        document.getElementById("finalScoreDisplayText").value = score;
    }


    /********************************************************************
     * FILTER FUNCTION
     ********************************************************************/
    function applyFilters() {
        let answerFilter = document.getElementById("answerFilterHSE").value;
        let categoryFilter = document.getElementById("categoryFilterHSE").value;

        document.querySelectorAll(".audit-row").forEach(row => {

            if (row.classList.contains("category-header-row")) {
                row.style.display = "";
                return;
            }

            let category = row.dataset.category || "";

            let cell = row.querySelector(
                `.finding-cell[data-hse-col="${ACTIVE_SITE_CATEGORY}"]`
            );

            let yes = false, no = false, na = false;

            if (cell) {
                yes = cell.querySelector("input[value='YES']")?.checked || false;
                no = cell.querySelector("input[value='NO']")?.checked || false;
                na = cell.querySelector("input[value='NA']")?.checked || false;
            }

            let showAnswer = true;

            if (answerFilter === "attempted") showAnswer = yes || no || na;
            if (answerFilter === "not_attempted") showAnswer = !yes && !no && !na;
            if (answerFilter === "yes") showAnswer = yes;
            if (answerFilter === "no") showAnswer = no;
            if (answerFilter === "na") showAnswer = na;

            let showCategory = true;

            if (categoryFilter !== "all") {
                showCategory = (category === categoryFilter);
            }

            row.style.display = (showAnswer && showCategory) ? "" : "none";

        });
    }


    /********************************************************************
     * AUTO SCORE + SUMMARY WHEN CHECKBOX CHANGES
     ********************************************************************/
    document.addEventListener("change", function (e) {

        if (e.target.classList.contains("checkbox")) {
            singleSelectHSE(e.target);
        }

    });


    /********************************************************************
     * FILTER EVENTS
     ********************************************************************/
    document.getElementById("answerFilterHSE").addEventListener(
        "change", applyFilters
    );

    document.getElementById("categoryFilterHSE").addEventListener(
        "change", applyFilters
    );
    /********************************************************************
     * FILE TYPE VALIDATION
     ********************************************************************/
    function validateFileType(input) {
        let allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xls', 'xlsx'];
        let file = input.files[0];
        if (!file) return;

        let ext = file.name.split('.').pop().toLowerCase();
        if (!allowed.includes(ext)) {
            alert("Invalid file format");
            input.value = "";
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert("Max file size is 10MB");
            input.value = "";
        }
    }


    /*************************a*******************************************
     * INITIALIZATION — RUNS ONLY ONCE
     ********************************************************************/
    function initAudit() {
        if (initialized) return;
        initialized = true;

        // Load dynamic questions if reaudit or edit
        let formAction = document.querySelector('form').getAttribute('action');
        let isReauditOrEdit = document.querySelector('input[name="hse_id"]') || document.querySelector('input[name="hse_audit_id"]');

        if (isReauditOrEdit) {
            toggleAuditTable();
            calculateScore();
            applyFilters();
            updateRealTimeSummary();
        } else {
            toggleAuditTable();
            calculateScore();
            applyFilters();
            updateRealTimeSummary();
        }
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

        let performAuditBy = document.querySelector('#perform_audit_by');
        if (performAuditBy) {
            performAuditBy.addEventListener("change", () => {
                toggleAuditTable();
                loadClientsByRegionAndCategory();
            });
        }

        let answerFilter = document.querySelector('#answerFilterHSE');
        if (answerFilter) {
            answerFilter.addEventListener("change", applyFilters);
        }

        let categoryFilter = document.querySelector('#categoryFilterHSE');
        if (categoryFilter) {
            categoryFilter.addEventListener("change", () => {
                applyFilters();
                updateRealTimeSummary();
            });
        }

        // Setup client dropdown change event - use jQuery for Select2 compatibility
        if (typeof jQuery !== "undefined") {
            jQuery('#client_id').off('change').on('change', function () {
                console.log('Client dropdown changed (initial setup)');
                clientOnselect();
            });
        } else {
            // Fallback to native event if jQuery is not available
            let clientIdDropdown = document.querySelector('#client_id');
            if (clientIdDropdown) {
                clientIdDropdown.addEventListener("change", clientOnselect);
            }
        }

        // Region dropdown updates hidden field - removed duplicate event listener
        let regionDropdown = document.querySelector('#region_dropdown');
        let regionHidden = document.querySelector('#region_hidden');
        if (regionDropdown && regionHidden) {
            regionDropdown.addEventListener("change", e => {
                regionHidden.value = e.target.value;
            });
        }

        // Select2 load safely
        setTimeout(() => {
            if (typeof jQuery !== "undefined" && typeof jQuery.fn.select2 !== "undefined") {
                jQuery('.js-example-basic-single').select2({
                    width: "100%",
                    placeholder: "Select option",
                    allowClear: true
                });
            }
            toggleAuditTable();
            calculateScore();
            updateRealTimeSummary();
        }, 200);
        toggleAuditTable();
        calculateScore();
        updateRealTimeSummary();
    });

    /********************************************************************
     * AUTO GENERATE AUDIT NUMBER
     ********************************************************************/
    (function () {
        let field = $('#audit_no');
        if (field && !field.value) {
            field.value = "AUD" + (Math.floor(Math.random() * 999) + 1);
        }
    })();
    let isAuditSubmitting = false;

    function handleAuditSubmit(e) {
        if (isAuditSubmitting) {
            e.preventDefault();
            return false;
        }

        let valid = validateHSEAudit();
        if (!valid) {
            e.preventDefault();
            return false;
        }

        $('.audit-row').each(function () {

            let row = $(this);
            let isMisc = row.find('input[name^="is_misc"]').val();
            let noChecked = row.find('.no_checkbox').is(':checked');
            let capa = row.find('.capa_json').val();

            // if (isMisc == '1' && noChecked && capa == '') {
            //     alert("CAPA required for Miscellaneous NO");
            //     valid = false;
            //     return false;
            // }
        });
        
        if (valid) {
            isAuditSubmitting = true;
            
            // --- OPTIMIZATION TO PREVENT MODSECURITY 403 ERROR ---
            // Disable empty/unnecessary fields to drastically reduce POST size and bypass WAF limits
            $('.audit-row').each(function () {
                let row = $(this);
                
                let findingVal = row.find('.finding_hidden').val();
                
                let capaInput = row.find('.capa_json');
                if (capaInput.length && (findingVal !== 'NO' || capaInput.val() === '')) {
                    capaInput.prop('disabled', true);
                }
                
                let remarkInput = row.find('textarea[name^="remark"]');
                if (remarkInput.length && remarkInput.val().trim() === '') {
                    remarkInput.prop('disabled', true);
                }

                let noteInput = row.find('input[name^="note"]');
                if (noteInput.length && noteInput.val().trim() === '') {
                    noteInput.prop('disabled', true);
                }
                
                let attachmentInput = row.find('input[name^="attachment"]');
                if (attachmentInput.length && attachmentInput.val().trim() === '') {
                    attachmentInput.prop('disabled', true);
                }
                
                if (!findingVal) {
                    row.find('.finding_hidden').prop('disabled', true);
                }
                
                let isMiscInput = row.find('input[name^="is_misc"]');
                if (isMiscInput.length && isMiscInput.val() !== '1') {
                    isMiscInput.prop('disabled', true);
                }
            });
            // -----------------------------------------------------

            let btn = document.getElementById("saveAuditBtn");
            if (btn) {
                setTimeout(() => {
                    btn.disabled = true;
                    let btnText = document.getElementById("saveBtnText");
                    if (btnText) btnText.innerText = "Saving...";
                    let btnLoader = document.getElementById("saveBtnLoader");
                    if (btnLoader) btnLoader.style.display = "inline-block";
                }, 10);
            }
        }

        return valid;
    }


    function validateHSEAudit() {

        const qs = (s) => document.querySelector(s);

        // ✅ Reaudit if hidden hse_audit_id exists
        const isReaudit = qs('input[name="hse_audit_id"]') !== null;

        /* ================= HEADER VALIDATION ================= */

        if (isReaudit) {

            // Reaudit can have readonly / hidden / normal input
            let auditorName =
                qs('input[name="auditor_name"]')?.value ||
                qs('select[name="auditor_name"]')?.value ||
                qs('#auditor_name')?.value ||
                '';

            if (!auditorName.trim()) {
                alert("Error: Auditor Name is missing.");
                return false;
            }

            let clientName =
                qs('input[name="client_name"]')?.value ||
                qs('select[name="client_name"]')?.value ||
                qs('#client_id')?.value ||
                '';

            if (!clientName.trim()) {
                alert("Error: Client Name is missing.");
                return false;
            }

        } else {

            // Perform Audit validation
            if (!qs('#auditor_name')?.value?.trim()) {
                alert("Please select Auditor Name");
                qs('#auditor_name')?.focus();
                return false;
            }

            if (!qs('input[name="auditee_name"]')?.value?.trim()) {
                alert("Please enter Auditee Name");
                qs('input[name="auditee_name"]')?.focus();
                return false;
            }

            if (!qs('input[name="audit_date"]')?.value?.trim()) {
                alert("Please select Audit Date");
                qs('input[name="audit_date"]')?.focus();
                return false;
            }

            if (!qs('#region_hidden')?.value?.trim() && !qs('#region_dropdown')?.value?.trim()) {
                alert("Please select Region");
                qs('#region_dropdown')?.focus();
                return false;
            }

            if (!qs('#perform_audit_by')?.value?.trim() && !qs('#main_category')?.value?.trim()) {
                alert("Please select Site Category");
                qs('#perform_audit_by')?.focus();
                return false;
            }

            if (!qs('#client_id')?.value?.trim() && !qs('input[name="client_name"]')?.value?.trim()) {
                alert("Please select Client Name");
                qs('#client_id')?.focus();
                return false;
            }

            if (!qs('input[name="report_date"]')?.value?.trim()) {
                alert("Please select Report Date");
                qs('input[name="report_date"]')?.focus();
                return false;
            }
        }

        /* ================= ATTENDANCE VALIDATION (OPTIONAL) ================= */
        // Attendance is optional. Partial rows will be ignored by backend if incomplete.

        /* ================= TABLE VALIDATION ================= */

        let rows = document.querySelectorAll('#auditTableBody tr.audit-row');
        let atleastOneSelected = false;

        for (let row of rows) {

            if (row.classList.contains('category-header-row')) continue;

            let checked = row.querySelector('input.checkbox:checked');

            if (checked && (checked.value === 'YES' || checked.value === 'NO' || checked.value === 'NA')) {
                atleastOneSelected = true;
                break;
            }
        }

        if (!atleastOneSelected) {
            alert("Please select at least one YES / NO / NA in the audit table");
            return false;
        }

        // ✅ MISCELLANEOUS CAPA VALIDATION
        let miscValid = true;
        document.querySelectorAll('.audit-row').forEach(row => {
            const isMiscInput = row.querySelector('input[name^="is_misc"]');
            const isMisc = isMiscInput && isMiscInput.value === '1';

            if (isMisc || isCategoryVRow(row)) {
                // Get the checked checkbox in the active site category column
                const type = ACTIVE_SITE_CATEGORY;
                const cell = row.querySelector(`.finding-cell[data-hse-col="${type}"]`);

                if (cell) {
                    const checked = cell.querySelector("input.checkbox:checked");
                    const isNo = checked && (checked.classList.contains('no_checkbox') || checked.value === 'NO');

                    // Get the hidden capa_json input for this row
                    const capaInput = row.querySelector('.capa_json');
                    const capa = capaInput ? capaInput.value.trim() : '';

                    if (isNo) {
                        if (!capa || capa === '' || capa === 'null' || capa === '[]' || capa === '{}') {
                            const catIdInput = row.querySelector('input[name^="audit_category_id"]');
                            const catId = catIdInput ? catIdInput.value : 'Unknown';
                            alert(`CAPA details are required for Miscellaneous row ${catId} when NO is selected.`);
                            miscValid = false;
                        }
                    }
                }
            }
        });

        if (!miscValid) return false;

        return true;
    }

    /********************************************************************
     * REALTIME SUMMARY UPDATE (Overall & Category)
     ********************************************************************/
    function updateRealTimeSummary() {
        let mainCatSelect = qs('#main_category');
        let subCatSelect = qs('#sub_category');
        // Get selected type (slug format)
        let type = ACTIVE_SITE_CATEGORY;

        // Get text/value for labels from both select and input (reaudit)
        let mainCatElem = qs('#main_category');
        let subCatElem = qs('#sub_category');
        let mainCatText = "N/A", subCatText = "N/A";

        if (mainCatElem) {
            if (mainCatElem.tagName === 'SELECT') {
                mainCatText = mainCatElem.options[mainCatElem.selectedIndex]?.text || "N/A";
            } else {
                mainCatText = mainCatElem.value || "N/A";
            }
        }

        if (subCatElem) {
            if (subCatElem.tagName === 'SELECT') {
                subCatText = subCatElem.options[subCatElem.selectedIndex]?.text || "N/A";
            } else {
                subCatText = subCatElem.value || "N/A";
            }
        }

        // Fallback if type is still empty (e.g. manual entry or pre-load)
        if (!type && mainCatText !== "N/A") {
            let matched = NAME_LIST.find(n => n.display === mainCatText);
            if (matched) type = matched.hse_type;
        }

        // ================= CLIENT NAME =================
        let clientText = "N/A";

        // Perform Audit (dropdown)
        let clientSelect = document.querySelector('select#client_id');

        // Reaudit hidden
        let clientHidden = document.querySelector('input#client_name');

        // Reaudit readonly display
        let clientDisplay = document.querySelector('#client_name_display');

        if (clientSelect && clientSelect.tagName === "SELECT" && clientSelect.value) {
            let selectedOption = clientSelect.options[clientSelect.selectedIndex];
            clientText = selectedOption?.getAttribute('data-client-name') || selectedOption?.text || "N/A";

            if (clientText === "Select Client" || clientText.trim() === "") {
                clientText = "N/A";
            }
        }
        else if (clientHidden && clientHidden.value.trim() !== "") {
            clientText = clientHidden.value.trim();
        }
        else if (clientDisplay && clientDisplay.value.trim() !== "") {
            clientText = clientDisplay.value.trim();
        }

        if (qs('#summary_display_client')) {
            qs('#summary_display_client').textContent = clientText;
        }

        if (qs('#summary_display_category')) qs('#summary_display_category').textContent = mainCatText;
        if (qs('#summary_display_subcategory')) qs('#summary_display_subcategory').textContent = subCatText;
        if (qs('#summary_display_client')) qs('#summary_display_client').textContent = clientText;

        // Update Overall table rows text and visibility
        qsa('#summaryOverallTable tbody tr').forEach(tr => {
            let rowType = tr.dataset.categoryType;
            if (rowType === type) {
                tr.style.display = "table-row";
                let cell = tr.querySelector('td:first-child');
                if (cell) cell.textContent = mainCatText.toUpperCase() + (subCatText !== "N/A" ? " - " + subCatText.toUpperCase() : "");
            } else {
                tr.style.display = "none";
            }
        });

        // Update Category-wise table headers visibility and text
        qsa('#summaryCategoryTable thead th[data-category-type]').forEach(th => {
            let headerType = th.getAttribute('data-category-type');
            if (headerType === type) {
                th.style.display = "table-cell";
                th.textContent = mainCatText + " %";
            } else {
                th.style.display = "none";
            }
        });

        qs('#realtimeSummaryCard').style.display = type ? "block" : "none";
        if (!type) return;

        // Initialize stats
        let stats = { yes: 0, no: 0, na: 0 };
        let catStats = {};

        document.querySelectorAll('#summaryCategoryTable tbody tr').forEach(tr => {
            let cat = tr.dataset.catName;
            if (cat) {
                catStats[cat] = { yes: 0, no: 0, na: 0 };
            }
        });

        // Loop through audit rows and accumulate stats for selected type
        document.querySelectorAll('.audit-row').forEach(row => {

            if (row.classList.contains('category-header-row')) return;

            let catName = (row.dataset.category || '').trim().toLowerCase();

            let cell = row.querySelector(`.finding-cell[data-hse-col="${type}"]`);
            if (!cell) return;

            let checked = cell.querySelector("input.checkbox:checked");
            if (!checked) return;

            // ✅ FIX: detect value safely
            let v = '';

            if (checked.classList.contains('no_checkbox')) {
                v = 'NO';
            } else {
                v = (checked.value || '').toUpperCase();
            }

            // ✅ FIX: normalize category key
            let key = Object.keys(catStats).find(k => k.toLowerCase() === catName);

            if (v === "YES") {
                stats.yes++;
                if (key) catStats[key].yes++;
            }
            else if (v === "NO") {
                stats.no++;
                if (key) catStats[key].no++;
            }
            else if (v === "NA") {
                stats.na++;
                if (key) catStats[key].na++;
            }
        });
        // UPDATE UI: Overall
        let denom = stats.yes + stats.no;
        let pct = denom > 0 ? ((stats.yes / denom) * 100).toFixed(2) : "0";

        let activeRow = document.querySelector(`#summaryOverallTable tbody tr[data-category-type="${type}"]`);
        if (activeRow) {
            let totalSelected = stats.yes + stats.no + stats.na;
            activeRow.querySelector('.s-total-selected').textContent = totalSelected;
            activeRow.querySelector('.s-yes').textContent = stats.yes;
            activeRow.querySelector('.s-no').textContent = stats.no;
            activeRow.querySelector('.s-na').textContent = stats.na;
            activeRow.querySelector('.s-score').textContent = pct + "%";

            qs("#scoreDisplay").textContent = pct;
            qs("#finalScoreDisplayText").value = pct;
        }

        // UPDATE UI: Category Score Table
        for (let [catName, data] of Object.entries(catStats)) {
            let tr = [...document.querySelectorAll('#summaryCategoryTable tbody tr')]
                .find(r => (r.dataset.catName || '').trim() === catName);
            if (!tr) continue;

            let d = data.yes + data.no;
            let p = d > 0 ? ((data.yes / d) * 100).toFixed(2) : "0";

            tr.querySelectorAll('td[data-category-type]').forEach(cell => {
                let cellType = cell.dataset.categoryType;
                if (cellType === type) {
                    cell.textContent = p + "%";
                    cell.style.display = "table-cell";
                } else {
                    cell.style.display = "none";
                }
            });
        }
    }

    function setNextAuditDate() {
        let auditDate = document.getElementById("audit_date").value;

        if (!auditDate) return;

        let d = new Date(auditDate);
        d.setMonth(d.getMonth() + 3);

        // Edge-case handling for month-end
        let day = d.getDate();
        if (day !== new Date(auditDate).getDate()) {
            d.setDate(0);
        }

        let next = d.toISOString().split('T')[0];

        let nextDate = document.getElementById("report_date");

        nextDate.value = next;
        nextDate.min = auditDate;
    }

</script>

<script>

</script>

<!-- Auto Save Draft UI Indicator -->
<div id="autoSaveIndicator" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; padding: 10px 20px; border-radius: 5px; background-color: #333; color: #fff; font-weight: bold; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <i class="mdi mdi-cloud-sync me-2"></i> <span id="autoSaveText">Saving...</span>
</div>

<script>
    let dirtyFields = {};
    let isSaving = false;
    let autoSaveInterval = null;
    let lastSavedTime = null;
    let draftLoaded = false;
    
    function showAutoSaveIndicator(text, color = '#333') {
        let ind = document.getElementById('autoSaveIndicator');
        ind.style.backgroundColor = color;
        ind.querySelector('#autoSaveText').innerText = text;
        ind.style.display = 'block';
        setTimeout(() => { ind.style.display = 'none'; }, 3000);
    }
    
    function getDraftContext() {
        let hseAuditId = $('input[name="hse_audit_id"]').val() || null;
        let auditTemplateId = '<?= $audit_template_id ?? "" ?>';
        let clientName = $('[name="client_name"]').val() || '';
        let location = $('[name="location"]').val() || '';
        let mainCategory = $('[name="main_category"]').val() || '';
        let subCategory = $('[name="sub_category"]').val() || '';
        let draftType = $('#draft_type').val() || 'perform';
        
        return {
            draft_type: draftType,
            audit_template_id: auditTemplateId,
            original_hse_audit_id: hseAuditId,
            client_name: clientName,
            location: location,
            main_category: mainCategory,
            sub_category: subCategory
        };
    }
    
    function initAutoSave() {
        // Track changes
        $('#hseAuditForm').on('change input', 'input, select, textarea', function() {
            let name = $(this).attr('name');
            if(!name) return;
            
            // Handle radio/checkbox differently
            let val = $(this).val();
            if(($(this).attr('type') == 'radio' || $(this).attr('type') == 'checkbox') && !$(this).is(':checked')){
                return;
            }
            
            // Skip file uploads
            if($(this).attr('type') == 'file') return;
            
            dirtyFields[name] = val;
            
            // Save to LocalStorage instantly as backup
            let lsKey = 'hse_draft_backup';
            let existingLS = JSON.parse(localStorage.getItem(lsKey) || '{}');
            existingLS[name] = val;
            localStorage.setItem(lsKey, JSON.stringify(existingLS));
        });
        
        // Auto Save Interval
        autoSaveInterval = setInterval(() => {
            saveDraftToDB();
        }, 30000); // 30 seconds
        
        // When key identifying fields change, try to load a draft
        $('select[name="client_name"], select[name="location"], select[name="main_category"], select[name="sub_category"]').on('change', function() {
            loadDraft();
        });
        
        // Initial load check
        setTimeout(loadDraft, 500); // slight delay to allow form population
    }
    
    function saveDraftToDB() {
        if(Object.keys(dirtyFields).length === 0) return;
        if(isSaving) return;
        
        let context = getDraftContext();

        isSaving = true;
        let dataToSave = JSON.stringify(dirtyFields);
        
        let payload = Object.assign({}, context, { draft_data: dataToSave });
        
        $.ajax({
            url: '<?= base_url("Masters/Hse_audit/save_draft") ?>',
            type: 'POST',
            data: payload,
            success: function(res) {
                if(res.status === 'success') {
                    dirtyFields = {}; // Clear dirty list on success
                    showAutoSaveIndicator('Draft Saved: ' + res.timestamp, '#28a745');
                    lastSavedTime = res.timestamp;
                }
            },
            error: function() {
                // If offline, it's already in LocalStorage. Just show a warning.
                showAutoSaveIndicator('Saved Locally (Offline)', '#ffc107');
            },
            complete: function() {
                isSaving = false;
            }
        });
    }
    
    function loadDraft() {
        if(draftLoaded) return;
        
        let context = getDraftContext();
        
        $.ajax({
            url: '<?= base_url("Masters/Hse_audit/get_draft") ?>',
            type: 'POST',
            data: context,
            success: function(res) {
                let dbData = {};
                let dbTime = null;
                
                if(res.status === 'success' && res.data) {
                    dbData = res.data;
                    dbTime = new Date(res.updated_at).getTime();
                }
                
                let lsData = JSON.parse(localStorage.getItem('hse_draft_backup') || '{}');
                // Merge DB Data and LS Data, with LS taking priority (in case browser had offline changes)
                let finalData = Object.assign({}, dbData, lsData);
                
                if(Object.keys(finalData).length > 0 && dbTime) {
                    $('#draftAvailableAlert').fadeIn();
                    $('#draftAvailableAlert strong').html('<i class="mdi mdi-cloud-download me-1"></i> Draft Found (Last Saved: ' + dbTime + ')');
                    
                    $('#loadDraftBtn').off('click').on('click', function() {
                        restoreFormFields(finalData);
                        showAutoSaveIndicator('Draft Restored', '#17a2b8');
                        $('#draftAvailableAlert').fadeOut();
                        draftLoaded = true;
                    });
                    
                    $('#discardDraftBtn').off('click').on('click', function() {
                        localStorage.removeItem('hse_draft_backup');
                        dirtyFields = {}; // Clear dirty fields
                        // we should also delete from DB visually or via API
                        $('#draftAvailableAlert').fadeOut();
                        draftLoaded = true;
                    });
                } else if(Object.keys(finalData).length > 0) {
                    // LocalStorage only
                    $('#draftAvailableAlert').fadeIn();
                    $('#loadDraftBtn').off('click').on('click', function() {
                        restoreFormFields(finalData);
                        showAutoSaveIndicator('Local Draft Restored', '#17a2b8');
                        $('#draftAvailableAlert').fadeOut();
                        draftLoaded = true;
                    });
                    $('#discardDraftBtn').off('click').on('click', function() {
                        localStorage.removeItem('hse_draft_backup');
                        $('#draftAvailableAlert').fadeOut();
                        draftLoaded = true;
                    });
                }
            }
        });
    }
    
    async function restoreFormFields(data) {
        // 1. Sequentially restore dependent dropdowns first
        if (data.region) {
            $('[name="region"]').val(data.region).trigger('change');
            await new Promise(r => setTimeout(r, 1500));
        }
        
        if (data.main_category) {
            $('[name="main_category"]').val(data.main_category).trigger('change');
            await new Promise(r => setTimeout(r, 1500));
        }
        
        if (data.sub_category) {
            $('[name="sub_category"]').val(data.sub_category).trigger('change');
            await new Promise(r => setTimeout(r, 1500));
        }
        
        if (data.client_id) {
            let questionsLoaded = false;
            let onLoaded = function() { questionsLoaded = true; };
            document.addEventListener('auditQuestionsLoaded', onLoaded, {once: true});
            
            $('[name="client_id"]').val(data.client_id).trigger('change');
            
            // Wait until the questions table finishes loading via AJAX, or timeout after 5 seconds
            for(let i=0; i<50; i++) {
                if (questionsLoaded) break;
                await new Promise(r => setTimeout(r, 100));
            }
        }
        
        // Wait an extra 500ms for RealTimeSummary timeouts to settle
        await new Promise(r => setTimeout(r, 500));
        
        // Ensure hidden context fields are perfectly restored
        if (data.client_name) $('[name="client_name"]').val(data.client_name).trigger('change');
        if (data.location) $('[name="location"]').val(data.location).trigger('change');
        
        // 2. NOW restore regular fields (so they don't get wiped by loadAuditQuestions)
        for(let name in data) {
            if(['region', 'main_category', 'sub_category', 'client_id', 'client_name', 'location'].includes(name)) continue;
            let val = data[name];
            
            // DEPENDENCY FIX: Ensure Attendance Rows exist before restoring attendance fields
            if (name.startsWith('att_auditee_name[') || name.startsWith('existing_att_opening_sign[') || name.startsWith('existing_att_closing_sign[')) {
                let match = name.match(/\[(\d+)\]/);
                if (match) {
                    let requiredIndex = parseInt(match[1]);
                    let tableBody = document.getElementById('attendanceTableBody');
                    if (tableBody) {
                        while (tableBody.querySelectorAll('tr.attendance-row').length < requiredIndex) {
                            if (typeof addAttendanceRow === 'function') {
                                addAttendanceRow();
                            } else {
                                break;
                            }
                        }
                    }
                }
            }
            
            let el = $('[name="' + name + '"]');
            
            // Handle finding checkboxes manually because they don't have a name attribute
            if (name.startsWith('finding[')) {
                let match = name.match(/finding\[(\d+)\]/);
                if (match) {
                    let index = match[1];
                    let row = document.querySelector('input[name="audit_question[' + index + ']"]')?.closest('tr');
                    if (row) {
                        // Uncheck all in this row first
                        row.querySelectorAll('.checkbox').forEach(cb => cb.checked = false);
                        // Check ALL matching checkboxes (to ensure the visible column's checkbox is checked)
                        row.querySelectorAll('.checkbox[value="' + val + '"]').forEach(cb => cb.checked = true);
                    }
                }
            }
            
            // Handle CAPA JSON to restore the green UI indicator
            if (name.startsWith('capa_json[')) {
                let match = name.match(/capa_json\[(\d+)\]/);
                if (match && val && val.trim() !== '' && val !== '""' && val !== '{}') {
                    let index = match[1];
                    let row = document.querySelector('input[name="audit_question[' + index + ']"]')?.closest('tr');
                    if (row) {
                        row.classList.add('capa-saved');
                        let indicator = row.querySelector('.capa-indicator');
                        if (!indicator) {
                            indicator = document.createElement('span');
                            indicator.className = 'capa-indicator';
                            indicator.innerHTML = '✓ CAPA';
                            indicator.style.cssText = 'display:inline-block; background:#28a745; color:white; padding:2px 6px; border-radius:3px; font-size:11px; margin-left:5px;';
                            let questionCell = row.querySelector('td:nth-child(3)');
                            if (questionCell) questionCell.appendChild(indicator);
                        }
                    }
                }
            }
            
            // Handle Draft Attachment UI restoration
            if (name.startsWith('existing_attachment[')) {
                let match = name.match(/existing_attachment\[(\d+)\]/);
                if (match && val && val.trim() !== '') {
                    let index = match[1];
                    let row = document.querySelector('input[name="audit_question[' + index + ']"]')?.closest('tr') || document.querySelector('input[name="attachment[' + index + ']"]')?.closest('tr');
                    if (row) {
                        let fileInput = row.querySelector('input[type="file"]');
                        let parent = fileInput ? (fileInput.closest('td') || fileInput.closest('th')) : null;
                        if (parent) {
                            let existing = parent.querySelector('.upload-success');
                            if (!existing) {
                                let success = document.createElement('div');
                                success.className = 'upload-success existing-attachment';
                                success.innerHTML = `<small style="color:green">Draft Saved: <a href="<?= base_url() ?>/${val}" target="_blank">View File</a></small>`;
                                parent.appendChild(success);
                            }
                        }
                    }
                }
            }

            // Handle Attendance Draft Images
            if (name.startsWith('existing_att_opening_sign[') || name.startsWith('existing_att_closing_sign[')) {
                let match = name.match(/existing_att_(opening|closing)_sign\[(\d+)\]/);
                if (match && val && val.trim() !== '') {
                    let type = match[1];
                    let index = match[2];
                    let previewDiv = null;
                    let targetInput = document.querySelector('input[name="existing_att_' + type + '_sign[' + index + ']"]');
                    if (targetInput) {
                        previewDiv = targetInput.parentElement.querySelector('div[class*="-preview"]');
                    }
                    if (previewDiv) {
                        let fileUrl = "<?= rtrim(base_url(), '/') ?>/" + val;
                        previewDiv.innerHTML = `
                            <div class="position-relative d-inline-block">
                                <img src="${fileUrl}" style="max-height: 50px; border: 1px solid #ccc; padding: 2px; cursor: pointer;" onclick="previewSignature('${fileUrl}')">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 start-100 translate-middle rounded-circle p-1" style="width: 24px; height: 24px; line-height: 1;" onclick="clearSignature(this, 'existing_att_${type}_sign[${index}]')"><i class="fa fa-times text-white ps-1"></i></button>
                            </div>
                        `;
                    }
                }
            }
            
            if(el.length > 0) {
                let type = el.attr('type');
                if(type === 'radio' || type === 'checkbox') {
                    el.filter('[value="' + val + '"]').prop('checked', true).trigger('change');
                } else if(type !== 'file' && name !== 'audit_no') {
                    el.val(val).trigger('change');
                }
            }
        }
        
        // 3. Update the realtime summary table with the newly checked values
        if (typeof updateRealTimeSummary === 'function') {
            updateRealTimeSummary();
        }
    }
    
    // Clear draft on successful submit
    document.getElementById("hseAuditForm").addEventListener("submit", function (e) {
        localStorage.removeItem('hse_draft_backup');
    });

    // Attendance Modal functions
    function openAttendanceModal() {
        $('#att_audit_no').text(document.querySelector('input[name="audit_no"]')?.value || 'DRAFT');
        
        let auditType = qs('#perform_audit_by')?.value || qs('input[name="main_category"]')?.value || '';
        $('#att_audit_type').text(auditType ? auditType.replace(/_/g, ' ').toUpperCase() : '');
        
        let auditor = qs('select[name="auditor_name"] option:checked')?.text || qs('input[name="auditor_name"]')?.value || '';
        $('#att_auditor_name').text(auditor);
        
        $('#att_auditee_name').text(qs('input[name="auditee_name"]')?.value || '');
        $('#att_audit_date').text(qs('input[name="audit_date"]')?.value || '');
        
        let region = qs('select[name="region"] option:checked')?.text || qs('input[name="region"]')?.value || '';
        $('#att_region').text(region);
        
        $('#att_site_category').text(auditType ? auditType.replace(/_/g, ' ').toUpperCase() : '');
        
        let subCat = qs('select[name="sub_category"] option:checked')?.text || qs('input[name="sub_category"]')?.value || '';
        $('#att_sub_category').text(subCat);
        
        let client = qs('select[name="client_name"] option:checked')?.text || qs('input[name="client_name"]')?.value || '';
        $('#att_client_name').text(client);
        
        $('#attendanceModal').modal('show');
    }

    function uploadAttendanceAttachmentAsync(input) {
        if(typeof validateFileType === 'function') validateFileType(input);
        
        let file = input.files[0];
        let previewDiv = input.parentElement.querySelector('div[class*="-preview"]');
        if (file && file.type.startsWith('image/')) {
            let reader = new FileReader();
            reader.onload = function(e) {
                previewDiv.innerHTML = `<img src="${e.target.result}" style="max-height: 50px; max-width: 100%; border: 1px solid #ccc; padding: 2px;">`;
            }
            reader.readAsDataURL(file);
        } else {
            previewDiv.innerHTML = '';
            if (!file) return;
        }
        
        let auditNo = document.querySelector('input[name="audit_no"]')?.value || 'DRAFT';
        let formData = new FormData();
        formData.append('file', file);
        formData.append('audit_no', auditNo);
        
        let nameMatch = input.name.match(/([a-zA-Z_]+)\[(\d+)\]/);
        if (!nameMatch) return;
        let fieldName = nameMatch[1];
        let index = nameMatch[2];
        
        fetch("<?= base_url('Masters/Hse_audit/upload_draft_attachment') ?>", {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 1) {
                let existingInputName = 'existing_' + fieldName + '[' + index + ']';
                let existingInput = document.querySelector('input[name="' + existingInputName + '"]');
                if (existingInput) {
                    existingInput.value = data.path;
                    existingInput.dispatchEvent(new Event('change', { bubbles: true })); // Trigger autosave
                }
                
                let fileUrl = "<?= rtrim(base_url(), '/') ?>/" + data.path;
                previewDiv.innerHTML = `
                    <div class="position-relative d-inline-block">
                        <img src="${fileUrl}" style="max-height: 50px; border: 1px solid #ccc; padding: 2px; cursor: pointer;" onclick="previewSignature('${fileUrl}')">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 start-100 translate-middle rounded-circle p-1" style="width: 24px; height: 24px; line-height: 1;" onclick="clearSignature(this, '${existingInputName}')"><i class="fa fa-times text-white ps-1"></i></button>
                    </div>
                `;
                
                // Clear the file input so it can trigger change again if same file selected later
                input.value = '';
            } else {
                console.error("Upload failed: " + data.message);
            }
        })
        .catch(err => console.error("Error uploading file"));
    }

    // --- Signature Pad & Attachment Actions ---

    function triggerSignatureUpload(btn) {
        let fileInput = btn.closest('td').querySelector('input[type="file"]');
        fileInput.removeAttribute('capture');
        fileInput.click();
    }
    
    function triggerSignatureCamera(btn) {
        let fileInput = btn.closest('td').querySelector('input[type="file"]');
        fileInput.setAttribute('capture', 'environment');
        fileInput.click();
    }
    
    function clearSignature(btn, hiddenInputName) {
        let td = btn.closest('td');
        let previewDiv = td.querySelector('div[class*="-preview"]');
        if (previewDiv) previewDiv.innerHTML = '';
        let hiddenInput = td.querySelector('input[name="' + hiddenInputName + '"]');
        if (hiddenInput) hiddenInput.value = '';
    }
    
    function previewSignature(src) {
        let previewModal = document.createElement('div');
        previewModal.className = 'modal fade signature-preview-modal';
        previewModal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Signature Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${src}" style="max-width: 100%; border: 1px solid #ccc;">
                    </div>
                </div>
            </div>`;
        document.body.appendChild(previewModal);
        let m = new bootstrap.Modal(previewModal);
        m.show();
        previewModal.addEventListener('hidden.bs.modal', function() {
            previewModal.remove();
        });
    }

    let signaturePad;
    let currentSignatureTarget = null;
    let currentSignatureIndex = null;
    let currentSignatureField = null;

    function openSignaturePad(btn, fieldName, index) {
        currentSignatureTarget = btn.closest('td');
        currentSignatureField = fieldName;
        currentSignatureIndex = index;
        
        let canvas = document.getElementById('signatureCanvas');
        
        // Wait for modal to be somewhat visible to get correct width
        let m = new bootstrap.Modal(document.getElementById('signaturePadModal'));
        m.show();
        
        setTimeout(() => {
            let modalBody = document.querySelector('#signaturePadModal .modal-body');
            let width = Math.min(modalBody.clientWidth - 20, 400); 
            canvas.width = width;
            canvas.height = width * 0.6; // aspect ratio
            
            if (!signaturePad) {
                if (typeof SignaturePad !== 'undefined') {
                    signaturePad = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: 'rgb(0, 0, 0)'
                    });
                } else {
                    alert("Signature Pad library not loaded!");
                }
            } else {
                signaturePad.clear();
            }
        }, 150);
    }
    
    function undoSignature() {
        if(signaturePad) {
            let data = signaturePad.toData();
            if (data) {
                data.pop(); // remove the last dot or line
                signaturePad.fromData(data);
            }
        }
    }

    function saveSignaturePad() {
        if (signaturePad.isEmpty()) {
            alert("Please provide a signature first.");
            return;
        }
        
        let dataURL = signaturePad.toDataURL('image/png');
        let auditNo = document.querySelector('input[name="audit_no"]')?.value || 'DRAFT';
        
        let formData = new FormData();
        formData.append('image', dataURL);
        formData.append('audit_no', auditNo);
        
        fetch("<?= base_url('Masters/Hse_audit/upload_signature_base64') ?>", {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 1) {
                let existingInputName = 'existing_' + currentSignatureField + '[' + currentSignatureIndex + ']';
                let hiddenInput = currentSignatureTarget.querySelector('input[name="' + existingInputName + '"]');
                let previewDiv = currentSignatureTarget.querySelector('div[class*="-preview"]');
                
                hiddenInput.value = data.path;
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                
                let fileUrl = "<?= rtrim(base_url(), '/') ?>/" + data.path;
                previewDiv.innerHTML = `
                    <div class="position-relative d-inline-block">
                        <img src="${fileUrl}" style="max-height: 50px; border: 1px solid #ccc; padding: 2px; cursor: pointer;" onclick="previewSignature('${fileUrl}')">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 start-100 translate-middle rounded-circle p-1" style="width: 24px; height: 24px; line-height: 1;" onclick="clearSignature(this, '${existingInputName}')"><i class="fa fa-times text-white ps-1"></i></button>
                    </div>
                `;
                
                let modalEl = document.getElementById('signaturePadModal');
                let modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                } else {
                    $(modalEl).modal('hide');
                }
            } else {
                alert("Error saving signature: " + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert("Error saving signature.");
        });
    }

    function addAttendanceRow() {
        let tableBody = document.getElementById('attendanceTableBody');
        let rows = tableBody.querySelectorAll('tr.attendance-row');
        if (rows.length >= 10) {
            alert('Maximum 10 attendance records allowed.');
            return;
        }
        
        let newIndex = 1;
        if (rows.length > 0) {
            let lastRow = rows[rows.length - 1];
            newIndex = parseInt(lastRow.getAttribute('data-index')) + 1;
        }

        let newRow = document.createElement('tr');
        newRow.className = 'attendance-row';
        newRow.setAttribute('data-index', newIndex);
        
        newRow.innerHTML = `
            <td class="sr-no"></td>
            <td>
                <input type="text" class="form-control" name="att_auditee_name[${newIndex}]" placeholder="Enter Name" value="">
            </td>
            <td>
                <input type="date" class="form-control" name="att_opening_date[${newIndex}]" value="">
            </td>
            <td>
                <div class="d-flex flex-wrap gap-1 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openSignaturePad(this, 'att_opening_sign', ${newIndex})" title="Draw Signature">✍️ Draw</button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="triggerSignatureUpload(this)" title="Upload Image">📁 Upload</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="triggerSignatureCamera(this)" title="Capture Camera">📷 Camera</button>
                </div>
                <input type="file" class="d-none" name="att_opening_sign[${newIndex}]" accept="image/*" onchange="uploadAttendanceAttachmentAsync(this)">
                <input type="hidden" name="existing_att_opening_sign[${newIndex}]" value="">
                <div class="att-opening-preview mt-2"></div>
            </td>
            <td>
                <input type="date" class="form-control" name="att_closing_date[${newIndex}]" value="">
            </td>
            <td>
                <div class="d-flex flex-wrap gap-1 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openSignaturePad(this, 'att_closing_sign', ${newIndex})" title="Draw Signature">✍️ Draw</button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="triggerSignatureUpload(this)" title="Upload Image">📁 Upload</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="triggerSignatureCamera(this)" title="Capture Camera">📷 Camera</button>
                </div>
                <input type="file" class="d-none" name="att_closing_sign[${newIndex}]" accept="image/*" onchange="uploadAttendanceAttachmentAsync(this)">
                <input type="hidden" name="existing_att_closing_sign[${newIndex}]" value="">
                <div class="att-closing-preview mt-2"></div>
            </td>
        `;
        
        tableBody.appendChild(newRow);
        resequenceAttendanceRows();
    }

    function deleteAttendanceRow(btn) {
        // Disabled per request, but kept empty for safety if still bound anywhere
    }

    function saveAttendanceModal() {
        let rows = document.querySelectorAll('#attendanceTableBody tr.attendance-row');
        let isValid = true;
        let formData = new FormData();
        
        let auditNo = '<?= $details['audit_no'] ?? '' ?>';
        let hseAuditId = '<?= $details['hse_audit_id'] ?? '' ?>';
        formData.append('audit_no', auditNo);
        formData.append('hse_audit_id', hseAuditId);

        let hasData = false;
        
        rows.forEach((row) => {
            let index = row.getAttribute('data-index');
            let nameInput = row.querySelector(`input[name="att_auditee_name[${index}]"]`);
            let openingDateInput = row.querySelector(`input[name="att_opening_date[${index}]"]`);
            let closingDateInput = row.querySelector(`input[name="att_closing_date[${index}]"]`);
            let existingOpenSign = row.querySelector(`input[name="existing_att_opening_sign[${index}]"]`);
            let newOpenSign = row.querySelector(`input[name="att_opening_sign[${index}]"]`);
            let existingCloseSign = row.querySelector(`input[name="existing_att_closing_sign[${index}]"]`);
            let newCloseSign = row.querySelector(`input[name="att_closing_sign[${index}]"]`);

            let name = nameInput.value.trim();
            let openingDate = openingDateInput.value.trim();
            let closingDate = closingDateInput.value.trim();
            
            let hasOpenSign = (existingOpenSign && existingOpenSign.value) || (newOpenSign && newOpenSign.files.length > 0);
            
            // if any field in the row has data, validate compulsory fields
            let hasAnyData = name || openingDate || closingDate || hasOpenSign || 
                            ((existingCloseSign && existingCloseSign.value) || (newCloseSign && newCloseSign.files.length > 0));

            if (hasAnyData) {
                hasData = true;
                if (!name) {
                    alert(`Please enter Auditee Name in row ${index}`);
                    isValid = false;
                    return;
                }
                if (!openingDate) {
                    alert(`Please select Opening Date in row ${index}`);
                    isValid = false;
                    return;
                }
                if (!hasOpenSign) {
                    alert(`Please provide Opening Sign in row ${index}`);
                    isValid = false;
                    return;
                }
                
                formData.append(`att_auditee_name[${index}]`, name);
                formData.append(`att_opening_date[${index}]`, openingDate);
                formData.append(`att_closing_date[${index}]`, closingDate);
                
                if (existingOpenSign) formData.append(`existing_att_opening_sign[${index}]`, existingOpenSign.value);
                if (newOpenSign && newOpenSign.files.length > 0) formData.append(`att_opening_sign[${index}]`, newOpenSign.files[0]);
                
                if (existingCloseSign) formData.append(`existing_att_closing_sign[${index}]`, existingCloseSign.value);
                if (newCloseSign && newCloseSign.files.length > 0) formData.append(`att_closing_sign[${index}]`, newCloseSign.files[0]);
            }
        });

        if (!isValid) return;

        // If this is a new audit (no ID yet), the attendance will be saved along with the main form.
        if (!hseAuditId) {
            $('#attendanceModal').modal('hide');
            return;
        }

        let btn = document.getElementById('btnSaveAttendanceModal');
        let oldText = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;

        fetch('<?= base_url("Masters/Hse_audit/save_attendance_ajax") ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 1) {
                // Update existing signs with returned new paths so multiple saves work
                if (data.new_paths) {
                    for(let i in data.new_paths) {
                        let op_rel = data.new_paths[i].opening_sign_rel;
                        let cp_rel = data.new_paths[i].closing_sign_rel;
                        let row = document.querySelector(`tr.attendance-row[data-index="${i}"]`);
                        if (row) {
                            if (op_rel) {
                                row.querySelector(`input[name="existing_att_opening_sign[${i}]"]`).value = op_rel;
                                row.querySelector(`input[name="att_opening_sign[${i}]"]`).value = ''; // clear file input
                            }
                            if (cp_rel) {
                                row.querySelector(`input[name="existing_att_closing_sign[${i}]"]`).value = cp_rel;
                                row.querySelector(`input[name="att_closing_sign[${i}]"]`).value = ''; // clear file input
                            }
                        }
                    }
                }
                $('#attendanceModal').modal('hide');
            } else {
                alert('Error saving attendance: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred while saving attendance.');
        })
        .finally(() => {
            btn.innerHTML = oldText;
            btn.disabled = false;
        });
    }

    function resequenceAttendanceRows() {
        let tableBody = document.getElementById('attendanceTableBody');
        let rows = tableBody.querySelectorAll('tr.attendance-row');
        
        rows.forEach((row, index) => {
            let srNoTd = row.querySelector('.sr-no');
            if (srNoTd) {
                srNoTd.textContent = index + 1;
            }
        });
        
        let addBtn = document.getElementById('btnAddAttendanceRow');
        if (rows.length >= 10) {
            addBtn.disabled = true;
        } else {
            addBtn.disabled = false;
        }
    }

    // Call once to ensure correct numbering initially
    $(document).ready(function() {
        resequenceAttendanceRows();
        initAutoSave();
    });
</script>
<?php $this->endSection(); ?>
