<?php $this->extend("Layout/base_admin"); ?>
<?php 
    $this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
    <li class="breadcrumb-item text-muted">
        <a href="<?= current_url() ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"Client"?></a>
    </li>
<!--end::Item-->
<?php $this->endSection();?>    

<?php $this->section("main_body"); ?>

<?php 
// ACL: Load helper and check permissions - 12/11/25
helper('designation_acl'); 

// Check if user can perform audits
if (!canPerformAudit()) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>Access Denied:</strong> You do not have permission to perform re-audits. Only Auditors can perform audits.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>';
    $dashboardUrl = base_url('Customer/Audit_dashboard/OE_Audit');
    if (isset($audit_template_type)) {
        if (strcasecmp($audit_template_type, 'Normal') === 0) {
            $dashboardUrl = base_url('Customer/Audit_dashboard/Normal_Audit');
        } elseif (strcasecmp($audit_template_type, 'HSE') === 0) {
            $dashboardUrl = base_url('Customer/Audit_Dashboard_HSE/HSE_Audit');
        }
    }
    echo '<a href="' . $dashboardUrl . '" class="btn btn-secondary">Back to Dashboard</a>';
    $this->endSection();
    return;
}

echo showACLMessage('info'); 

// changes on 18/11/25: Debug output to verify data is loaded correctly
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo '<div class="alert alert-info">';
    echo '<h5>Debug Information:</h5>';
    echo '<p><strong>Audit ID:</strong> ' . ($details['audit_no'] ?? 'N/A') . '</p>';
    echo '<p><strong>Template Questions:</strong> ' . count($excel_details ?? []) . '</p>';
    echo '<p><strong>Saved Audit Details:</strong> ' . count($audit_details_by_sr_loc ?? []) . ' (by Sr+Loc)</p>';
    echo '<p><strong>Saved Audit Details:</strong> ' . count($audit_details_by_param_loc ?? []) . ' (by Param+Loc)</p>';
    echo '<p><strong>Saved Audit Details:</strong> ' . count($audit_details ?? []) . ' (by Question)</p>';
    echo '<p><strong>Saved Audit Details:</strong> ' . count($audit_details_by_index ?? []) . ' (by Index)</p>';
    if (!empty($audit_details_by_sr_loc)) {
        echo '<p><strong>Sample Sr+Loc Keys:</strong> ' . implode(', ', array_slice(array_keys($audit_details_by_sr_loc), 0, 5)) . '</p>';
    }
    echo '</div>';
}
?>

<form action="<?=$action?>" method="post" enctype='multipart/form-data' onsubmit="return validateReaudit()">
<style>
/* changes on 16/10/25 by darsh: Match OE/Normal Select2 dropdown sizing to HSE */
.select2-container { width: 100% !important; }
.select2-container .select2-selection--single { height: 38px !important; display: flex; align-items: center; }
/* changes on 16/10/25 by darsh: Center text vertically like HSE */
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px !important; display: flex; align-items: center; padding-left: 12px; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px !important; top: 50% !important; transform: translateY(-50%) !important; }
.form-select.js-example-basic-single { width: 100%; }

/* NEW CHANGES: highlight rows whose NC was closed in OE NC Tracker */
.resolved-nc-row {
    background-color: #e6ffe6 !important;   /* light green */
}

/* === NEW: Sticky header for Reaudit Audit CheckList table === */
.audit-table-wrapper {
    max-height: 850px;
    overflow-y: auto;
    overflow-x: auto;
}

.audit-table-wrapper table {
    margin-bottom: 0;
}

.audit-table-wrapper thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background-color: #f5f8fa;
}

/* === Responsive Sticky Footer Action Bar === */
.sticky-bottom-footer {
    position: sticky;
    bottom: 0;
    z-index: 100;
    background: #ffffff !important;
    border-top: 2px solid #e2e8f0;
    box-shadow: 0 -6px 20px rgba(0, 0, 0, 0.08);
}
@media (max-width: 991.98px) {
    .sticky-bottom-footer {
        flex-direction: column !important;
        align-items: stretch !important;
        padding: 14px !important;
    }
    .sticky-bottom-footer .d-flex {
        width: 100% !important;
        justify-content: center !important;
    }
    .sticky-bottom-footer .btn {
        width: 100% !important;
    }
}

.attachment-cell {
    min-width: 180px;
    max-width: 240px;
}
.existing-attachment-card {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}
.attachment-thumb-link:hover img {
    transform: scale(1.05);
    transition: transform 0.15s ease-in-out;
    box-shadow: 0 2px 8px rgba(0, 158, 247, 0.3);
}
</style>

<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title"><?=(isset($page_title))?$page_title:""?></h4>
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
                                <tr>
                                    <th class="table-secondary" >Audit No.</th>
                                    <td>
                                        <input type="text" class="form-control" name="audit_no" value="<?=$details['audit_no']?>" readonly class="form-control-disabled" />
                                    </td>
                                </tr>
                                <tr>
                                    <th class="table-secondary ">Audit Type Name</th>
                                    <td >
                                        <?=$details['audit_name']?>
                                        <input type=hidden name="audit_name" value="<?=$details['audit_name']?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <th class="table-secondary">Auditor Name</th>
                                    <td>
                                   <!-- changes on 05/01/26: Auditor name dropdown for OE/Structure audits, read-only for Normal audits -->
                                   <?php 
                                   $isNormalAudit = isset($audit_template_type) && strtolower($audit_template_type) === 'normal';
                                   if($isNormalAudit) { 
                                   ?>
                                       <input type="text" class="form-control" value="<?= isset($details['auditor_name']) ? $details['auditor_name'] : '' ?>" readonly class="form-control-disabled">
                                       <input type="hidden" name="auditor_name" value="<?= isset($details['auditor_name']) ? $details['auditor_name'] : '' ?>">
                                   <?php } else { ?>
                                       <select class="form-select js-example-basic-single" id="auditor_name" name="auditor_name" required>
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
                                    <th class="table-secondary">ReAudit Date</th>
                                    <td>
                                   <!-- changes on 03/12/25: Reaudit date editable - score will appear in this month -->
                                   <input type="date" name="reaudit_date" class="form-control" value="<?= isset($details['audit_date']) ? $details['audit_date'] : date('Y-m-d') ?>" min="<?= isset($min_reaudit_date) ? $min_reaudit_date : (isset($details['audit_date']) ? $details['audit_date'] : '') ?>" required>
                                   <input type="hidden" name="original_audit_date" value="<?= isset($details['audit_date']) ? $details['audit_date'] : '' ?>" />
                                    </td>
                                </tr>

                               
                                    <th class="table-secondary">Score</th>
                                    <td>
                            <!-- changes on 16/10/25 by darsh: Standardize to x/100 visual and hidden input -->
                            <span id="scoreDisplay">0</span> / <span id="finalScoreDenominator">100</span>
                            <input type="hidden" id="scoreValue" name="score" value="0">

                                    </td>
                                </tr>
                                 
                        
                        
                        </tbody>
            </table>          
        <!-- <footer>Someone famous in <cite title="Source Title">Source Title</cite></footer> -->
            </blockquote>
            </div>
        <div class="col-md-6">
        <h4 class="card-title">Client Details</h4>
        <blockquote class="blockquote">
            <table class="table table-hover">
                            <tbody>
                                
                               <?php $isNormal = isset($audit_template_type) && strtolower($audit_template_type) === 'normal'; ?>
                               <?php if(!$isNormal) { ?>
                               <tr>
                                    <th class="table-secondary">Client Name</th>
                                     <td>
                                         <input type="hidden" name="audit_template_id" value="<?=$details['audit_template_id']?>" />
                                         <!-- changes on 16/11/25: Client name read-only in reaudit -->
                                         <input type="text" class="form-control" value="<?= isset($details['client_name']) ? $details['client_name'] : '' ?>" readonly class="form-control-disabled">
                                         <input type="hidden" name="client_name" value="<?= isset($details['client_name']) ? $details['client_name'] : '' ?>">
                                        </td>
                                </tr>
                                <?php } else { ?>
                                    <input type="hidden" name="audit_template_id" value="<?=$details['audit_template_id']?>" />
                                    <input type="hidden" name="client_name" value="<?= isset($details['client_name']) ? esc($details['client_name']) : '' ?>" />
                                <?php } ?>
                                 <tr>
                                    <th class="table-secondary">Auditee Name</th>
                                    <td>
                                    <?php 
                                        $targetAuditee = !empty($details['auditee_name']) ? trim($details['auditee_name']) : (!empty($client_account_manager) ? trim($client_account_manager) : '');
                                        $amList = isset($account_managers) ? $account_managers : [];
                                        if (!empty($targetAuditee) && !in_array($targetAuditee, $amList)) {
                                            $amList[] = $targetAuditee;
                                            sort($amList, SORT_NATURAL | SORT_FLAG_CASE);
                                        }
                                    ?>
                                        <div class="auditee-wrapper">
                                            <select id="auditee_name_select" class="form-select js-example-basic-single" style="width: 100%;">
                                                <option value="">Select Auditee Name</option>
                                                <?php foreach ($amList as $am) { 
                                                    $sel = (strcasecmp($targetAuditee, $am) === 0) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= htmlspecialchars($am, ENT_QUOTES); ?>" <?= $sel ?>><?= htmlspecialchars($am) ?></option>
                                                <?php } ?>
                                                <option value="__other__">Other (Enter Manually)</option>
                                            </select>
                                            <div id="auditee_other_container" class="mt-2" style="display: none;">
                                                <input type="text" id="auditee_name_other" class="form-control" placeholder="Enter Auditee Name manually..." value="" />
                                            </div>
                                            <input type="hidden" name="auditee_name" id="auditee_name_input" value="<?= htmlspecialchars($targetAuditee, ENT_QUOTES); ?>" />
                                        </div>
                                   <input type="hidden" name="cluster_name" value="<?= $details['cluster_name'] ?? '' ?>" />
                                   <input type="hidden" name="client_manager_name" value="<?= $details['client_manager_name'] ?? '' ?>" />
                                    </td>
                                </tr>
                                <tr>
                                <th class="table-secondary">Region</th>
                                <td>
                                    <!-- changes on 16/11/25: Region read-only in reaudit -->
                                    <input type="text" class="form-control" value="<?= isset($details['region']) ? $details['region'] : '' ?>" readonly class="form-control-disabled">
                                    <input type="hidden" name="region" value="<?= isset($details['region']) ? $details['region'] : '' ?>">
                                </td>
                            </tr>
                            
                             <tr>
                                    <th class="table-secondary">Next Audit Date</th>
                                    <td>
                                       <!-- changes on 16/11/25: Next audit date editable in reaudit -->
                                       <input type="date" name="next_date" class="form-control" value="<?=$details['next_date'] ?? ''?>">
                                    </td>
                                </tr>
                                <tr>
                            <tr>
                            <th class="table-secondary">Audit Note</th>
                            <td>
                                <textarea class="form-control" name="audit_note" id="audit_note" rows="3"><?= $details['audit_note'] ?? '' ?></textarea>
                            </td>
                        </tr>
                            </tbody>
            </table>          
        <!-- <footer>Someone famous in <cite title="Source Title">Source Title</cite></footer> -->
            </blockquote>
            </div>
           
        </div>
           </div>
   </div>
</div>
                         
                         
<div class="col-md-12 mb-5">
    <div class="card">
         <div class="card-header">
              <div class="d-flex justify-content-between align-items-center w-100">
                  <h3 class="card-title mb-0">Audit CheckList</h3>
                  <!-- changes on 15/10/25 by darsh: move filter above table for Reaudit -->
                  <div class="d-flex align-items-center" style="gap:10px;">
                      <label class="mb-0">Filter:</label>
                      <select id="answerFilterReaudit" class="form-select form-select-sm" style="width:auto;">
                          <option value="all">All</option>
                          <option value="attempted">Attempted</option>
                          <option value="yes">Yes</option>
                          <option value="no">No</option>
                          <option value="na">Not attempted</option>
                      </select>
                      <!-- changes on 4/11/025 by darsh: Category filter -->
                      <?php 
                          if (!isset($__categories)) {
                              $__categories = [];
                              if (isset($excel_details) && is_array($excel_details)) {
                                  foreach ($excel_details as $__r) {
                                      $__categories[strval($__r['category'] ?? '')] = true;
                                  }
                              }
                          }
                      ?>
                      <!--<select id="categoryFilterReaudit" class="form-select form-select-sm" style="width:auto;">-->
                      <!--    <option value="__all__">All</option>-->
                      <!--    <?php foreach(array_keys($__categories) as $__cat){ ?>-->
                      <!--        <option value="<?= htmlspecialchars($__cat) ?>"><?= htmlspecialchars($__cat) ?></option>-->
                      <!--    <?php } ?>-->
                      <!--</select>-->
                  </div>
              </div>
         </div>
        <div class="card-body">
            <div class="row">
                <!-- NEW: added audit-table-wrapper class for sticky header -->
                <div class="col-md-12 audit-table-wrapper table-responsive">
                    <table class="table table-row-bordered align-middle text-gray-700 font-medium text-sm" id="auditTable">
                        <thead>
                        <tr>
                            <!-- changes on 4/11/025 by darsh: Added Sr No column to stabilize question identity -->
                            <th>Sr No</th>
                            <th>Category</th>
                            <th style="min-width: 250px;">
                                Audit Questions 

                            </th>
                            <th style="min-width: 500px;">
                                Target
                            </th>
                            <th>
                               Weightage
                            </th>
                            <th>
                                Audit Findings (Yes / No)
                            </th>
                            <th>
                                Remarks
                            </th>
                            <th>
                                Attachments / Images
                            </th>
                        </tr>
                        </thead>
                        
                 <?php
                 $__categories = [];
                 foreach($excel_details as $__r){ $__categories[strval($__r['category'])] = true; }
                 $temp_location = "";
                 foreach($excel_details as $key=>$row){
                     
                 if($temp_location !=$row['location']){
                     $temp_location =$row['location'];
                     echo "<tr><th colspan='8'><br><center>$temp_location</center><th></tr>";
                 } 
                 ?>
                     <input type="hidden" name="location_list[]" value="<?=$temp_location?>">

                     <input type="hidden" name="category[]" value="<?=$row['category']?>"><input type="hidden" name="audit_question[]" value="<?=htmlspecialchars($row['audit_question'] ?? '')?>">
                     <input type="hidden" name="audit_parameter[]" value="<?=htmlspecialchars($row['audit_parameter'] ?? '')?>">
                     <input type="hidden" name="risk_priority[]" value="<?=$row['risk_priority']?>">
                     <input type="hidden" name="weightage[]" value="<?=$row['weightage']?>">

                    <?php
                           // ====== EXISTING DATA RESOLUTION (same as before) ======
                           $isNormalRe = isset($audit_template_type) && strtolower(trim($audit_template_type)) === 'normal';

                           $existingBySrLoc = null;

                           // Strategy 1: Match by Sr No + Location (most reliable for new audits)
                           $srKey = strtolower(trim((string)($key+1).'|'.($temp_location ?? '')));
                           if (isset($audit_details_by_sr_loc[$srKey])) {
                               $existingBySrLoc = $audit_details_by_sr_loc[$srKey];
                           }

                           // Strategy 2: Match by audit_parameter + Location (for OE audits)
                           if (!$existingBySrLoc && !$isNormalRe && isset($audit_details_by_param_loc)) {
                               $paramKey = strtolower(trim(($row['audit_parameter'] ?? '').'|'.($temp_location ?? '')));
                               if (isset($audit_details_by_param_loc[$paramKey])) {
                                   $existingBySrLoc = $audit_details_by_param_loc[$paramKey];
                               }
                           }

                           // Strategy 3: Match by audit_question (for Normal audits or fallback)
                           if (!$existingBySrLoc && isset($audit_details)) {
                               $questionKey = $isNormalRe ? strtolower(trim($row['audit_question'] ?? '')) : strtolower(trim($row['audit_parameter'] ?? ''));
                               if (isset($audit_details[$questionKey])) {
                                   $existingBySrLoc = $audit_details[$questionKey];
                               }
                           }

                           // Strategy 4: Match by risk_priority + category + location (last resort)
                           if (!$existingBySrLoc && isset($audit_details_by_target)) {
                               $targetKey = strtolower(trim(($row['risk_priority'] ?? '').'|'.($row['category'] ?? '').'|'.($temp_location ?? '')));
                               if (isset($audit_details_by_target[$targetKey])) {
                                   $existingBySrLoc = $audit_details_by_target[$targetKey];
                               }
                           }

                           // Strategy 5: Match by index position (absolute fallback)
                           if (!$existingBySrLoc && isset($audit_details_by_index[$key])) {
                               $existingBySrLoc = $audit_details_by_index[$key];
                           }

                           $existingId = $existingBySrLoc['audit_details_id'] ?? '';

                           // NEW CHANGES: compute flags for closed NC / YES / NO
                           $isClosedNC = !empty($existingBySrLoc) && isset($existingBySrLoc['status']) && (string)$existingBySrLoc['status'] === '3';
                           // When NC is closed (status=3) for this same audit, treat it as YES automatically
                           $isYes = (!empty($existingBySrLoc) && isset($existingBySrLoc['audit_finding']) && strtoupper($existingBySrLoc['audit_finding']) == "YES") || $isClosedNC;
                           // NO should never be selected for closed NC
                           $isNo  = (!empty($existingBySrLoc) && isset($existingBySrLoc['audit_finding']) && strtoupper($existingBySrLoc['audit_finding']) == "NO" && !$isClosedNC);
                    ?>

                    <!-- NEW CHANGES: add resolved-nc-row class for closed NC -->
                    <tr class="audit-row <?= $isClosedNC ? 'resolved-nc-row' : '' ?>" data-category="<?= htmlspecialchars($row['category']) ?>">
                       
                       <!-- changes on 4/11/025 by darsh: Sr No displayed and posted -->
                       <td>
                           <?= ($key+1) ?>
                           <input type="hidden" name="sr_no[]" value="<?= ($key+1) ?>">
                           <input type="hidden" name="existing_detail_id[]" value="<?= esc($existingId) ?>">
                       </td>
                       <td>
                            <lable class="form-label"><?=$row['category']?></lable>
                        </td>

                        <?php
                        // Normal: Question = audit_question, Target = risk_priority
                        // OE/Structure: Question = audit_parameter, Target = risk_priority
                        $displayQuestion = $isNormalRe ? ($row['audit_question'] ?? '') : ($row['audit_parameter'] ?? '');
                        $displayTarget   = $row['risk_priority'] ?? '';
                        ?>
                        <td>
                            <lable class="form-label"><?=$displayQuestion?></lable>
                        </td>
                       <td>
                            <pre class="form-label" style="white-space:pre-wrap; margin:0;"><?=$displayTarget?></pre>
                        </td>
                        <td>
                            <lable class="form-label"><?=$row['weightage']?></lable>
                        </td>
                         
                        <td class="finding-cell" data-type="oe">
                            <!-- YES / NO with closed-NC logic -->
                             <div class="flex gap-12">
                              <label class="form-label flex items-center gap-2.5 text-nowrap">
                              Yes 
                              <input class="checkbox checkbox_data"
                                     name="check_<?=$key?>"
                                     type="checkbox"
                                     value="1"
                                     data-score="<?=$row['weightage']?>"
                                     data-sr="<?=($key+1)?>"
                                     data-location="<?=htmlspecialchars($temp_location)?>"
                                     <?= $isYes ? "checked" : "" ?> />
                              </label>
                                  &nbsp;&nbsp;
                              <label class="form-label flex items-center gap-2.5 text-nowrap">
                               No 
                               <input class="checkbox"
                                      name="check_<?=$key?>"
                                      type="checkbox"
                                      value="0"
                                      data-score="0"
                                      data-sr="<?=($key+1)?>"
                                      data-location="<?=htmlspecialchars($temp_location)?>"
                                      <?= $isNo ? "checked" : "" ?> />
                              </label>
                                  &nbsp;&nbsp;
                              <label class="form-label flex items-center gap-2.5 text-nowrap">
                               NA 
                               <input class="checkbox checkbox_na_score"
                                      name="check_<?=$key?>"
                                      type="checkbox"
                                      value="NA"
                                      data-score="<?=$row['weightage']?>"
                                      data-sr="<?=($key+1)?>"
                                      data-location="<?=htmlspecialchars($temp_location)?>"
                                      <?php 
                                        $isNA = (!empty($existingBySrLoc) && isset($existingBySrLoc['audit_finding']) && strtoupper($existingBySrLoc['audit_finding']) == "NA");
                                        echo $isNA ? "checked" : ""; 
                                      ?> />
                              </label>
                             </div>
                        </td>
                        <td>
                            <textarea name="remark[]" class="textarea border-danger" placeholder="Please add your remark" rows="6" value=""><?php echo (isset($existingBySrLoc['audit_remark']) && $existingBySrLoc['audit_remark']!="")?$existingBySrLoc['audit_remark']:""; ?></textarea>
                        </td>
                        <td class="attachment-cell">
                            <?php 
                                $existing = isset($existingBySrLoc['audit_attachment']) ? trim($existingBySrLoc['audit_attachment']) : '';
                                if (!empty($existing)) { 
                                    $normExisting = ltrim(preg_replace('#/+#', '/', $existing), '/');
                                    $reqHost = $_SERVER['HTTP_HOST'] ?? '';
                                    $scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) ? 'https://' : 'http://';
                                    if (strpos($existing, 'http://') === 0 || strpos($existing, 'https://') === 0) {
                                        $fileUrl = $existing;
                                    } elseif (!empty($reqHost)) {
                                        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
                                        $basePrefix = ($scriptDir !== '' && $scriptDir !== '.' && $scriptDir !== '/') ? $scriptDir : '';
                                        $fileUrl = $scheme . $reqHost . $basePrefix . '/' . $normExisting;
                                    } else {
                                        $fileUrl = base_url($normExisting);
                                    }
                                    $ext = strtolower(pathinfo(parse_url($normExisting, PHP_URL_PATH), PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                            ?>
                                <div class="existing-attachment-card mb-2 p-2 border rounded bg-light">
                                    <?php if ($isImage): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="<?= $fileUrl ?>" target="_blank" class="d-inline-block position-relative attachment-thumb-link" title="Click to view full image in new tab">
                                                <img src="<?= $fileUrl ?>" alt="Attachment" style="width: 55px; height: 55px; object-fit: cover; border-radius: 4px; border: 1px solid #3b82f6; display: block;" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'badge badge-light-primary py-1 px-2\'><i class=\'bi bi-image\'></i> View Image</span>';" />
                                            </a>
                                            <div class="d-flex flex-column">
                                                <span class="text-muted" style="font-size: 10px; font-weight: 600;">Image Proof</span>
                                                <a href="<?= $fileUrl ?>" target="_blank" class="text-primary fw-bold" style="font-size: 11px; text-decoration: underline;">
                                                    <i class="bi bi-box-arrow-up-right"></i> Open Full View
                                                </a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <span style="font-size: 20px;">
                                                <?= ($ext === 'pdf') ? '📄' : (in_array($ext, ['xls', 'xlsx', 'csv']) ? '📊' : '📎') ?>
                                            </span>
                                            <div class="d-flex flex-column">
                                                <span class="text-muted" style="font-size: 10px; font-weight: 600;"><?= strtoupper($ext ?: 'FILE') ?> Document</span>
                                                <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-xs btn-outline btn-outline-primary py-1 px-2 fw-bold" style="font-size: 11px;">
                                                    <i class="bi bi-box-arrow-up-right"></i> View in New Tab
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="existing_attachment[]" value="<?= esc($existing) ?>">
                            <?php } else { ?>
                                <input type="hidden" name="existing_attachment[]" value="">
                            <?php } ?>

                            <div class="upload-wrapper">
                                <label class="form-label text-muted mb-1" style="font-size: 10px;"><?= !empty($existing) ? 'Replace / Upload New Proof:' : 'Upload Proof:' ?></label>
                                <input type="file" name="audit_attachment[]" class="form-control form-control-sm audit-file-input" accept=".jpg,.jpeg,.png,.gif,.pdf,.xls,.xlsx,.csv" onchange="handleAuditFileUpload(this)">
                                <div class="file-live-preview mt-2" style="display:none;"></div>
                            </div>
                        </td>
                        </tr>
                    
                 <?php } ?>
                 </table>
                 </div>
                 </div> 
            </div>

            <!-- Responsive Sticky Footer Action Bar -->
            <div class="card-footer bg-white py-4 px-6 border-top shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-3 sticky-bottom-footer">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <button type="submit" class="btn btn-success btn-lg fw-bold d-inline-flex align-items-center gap-2 px-6 shadow-sm">
                        <i class="bi bi-check-circle-fill fs-4"></i> Save & Submit Details
                    </button>
                    <?php if (!empty($details['structured_audit_id'])): ?>
                        <a href="<?= base_url('Masters/Audit_final_structure/auditViewDetailsPdf/' . $details['structured_audit_id']) ?>" target="_blank" class="btn btn-primary btn-lg fw-bold d-inline-flex align-items-center gap-2 px-5 shadow-sm" title="Download or View PDF Report">
                            <i class="bi bi-file-earmark-pdf fs-4"></i> Download PDF
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('Masters/Audit_final_structure') ?>" class="btn btn-light-secondary btn-lg fw-semibold px-4">
                        <i class="bi bi-arrow-left"></i> Back to Audits
                    </a>
                </div>
                <div class="d-flex align-items-center gap-4 bg-light rounded-3 px-4 py-2 border">
                    <div class="d-flex flex-column text-end">
                        <span class="text-muted fw-semibold" style="font-size: 11px;">Audit Live Score</span>
                        <span class="text-primary fw-bolder fs-3"><span id="scoreDisplayFooter"><?= $details['audit_score'] ?? 0 ?></span>%</span>
                    </div>
                    <div class="vr h-30px opacity-25"></div>
                    <div class="d-flex flex-column text-start">
                        <span class="text-muted fw-semibold" style="font-size: 11px;">Audit Type</span>
                        <span class="badge badge-light-primary fs-7 fw-bold"><?= htmlspecialchars($details['audit_name'] ?? 'OE Audit') ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
 </form>
                         
                         
<?php                 $this->endSection();?>    
    

<?php                     $this->section("javascript_section");?>
<!-- changes on 15/10/25 by darsh: add Select2 assets for reaudit dropdowns -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// changes on 28/10/25 by darsh: performance improvements for fast ticking

var final_score = 0;
var recomputeScheduled = false;

// score calculation
function calculateScore() {
    let final_score = 0.0;

    // count YES only (checkbox_data)
    const nodes = document.querySelectorAll('#auditTable input.checkbox_data:checked, #auditTable input.checkbox_na_score:checked');
    for (let i = 0; i < nodes.length; i++) {
        const v = nodes[i].getAttribute('data-score');
        if (v) {
            const score = parseFloat(v) || 0;
            final_score += score;
        }
    }

    final_score = parseFloat(final_score.toFixed(2));

    $('#scoreDisplay').text(final_score);
    $('#scoreDisplayFooter').text(final_score);
    $('#scoreValue').val(final_score);
}

function scheduleRecompute() {
    if (recomputeScheduled) return;
    recomputeScheduled = true;
    requestAnimationFrame(function(){
        recomputeScheduled = false;
        calculateScore();
    });
}

// Delegated handler: mutual exclusivity + score update
document.addEventListener('click', function(evt){
    var el = evt.target;
    if (!el || el.tagName !== 'INPUT' || el.type !== 'checkbox') return;
    if (!el.closest('#auditTable')) return;
    var name = el.getAttribute('name');
    if (!name) return;
    var group = document.querySelectorAll('#auditTable input[name="'+CSS.escape(name)+'"]');
    for (var i = 0; i < group.length; i++) {
        if (group[i] !== el) group[i].checked = false;
    }
    scheduleRecompute();
});

// File upload validation & live preview
function handleAuditFileUpload(input) {
    const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'csv'];
    const maxSize = 25 * 1024 * 1024; // 25MB
    const cell = $(input).closest('td');
    const previewContainer = cell.find('.file-live-preview');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const extension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.includes(extension)) {
            alert('Invalid file type. Allowed files: Images (JPG, PNG, GIF, WEBP), PDFs, and Excel files (XLS, XLSX, CSV).');
            input.value = '';
            previewContainer.hide().empty();
            return false;
        }
        
        if (file.size > maxSize) {
            alert('File size too large. Maximum allowed size is 25MB.');
            input.value = '';
            previewContainer.hide().empty();
            return false;
        }

        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);
        const fileUrl = URL.createObjectURL(file);
        const fileSizeKb = (file.size / 1024).toFixed(1);

        let previewHtml = '';
        if (isImage) {
            previewHtml = `
                <div class="new-attachment-preview p-2 border border-primary border-dashed rounded bg-light-primary d-flex align-items-center gap-2">
                    <a href="${fileUrl}" target="_blank" class="d-inline-block" title="Click to view full image in new tab">
                        <img src="${fileUrl}" alt="Selected" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #009ef7;" />
                    </a>
                    <div class="d-flex flex-column overflow-hidden">
                        <span class="badge badge-success px-1 py-0 fs-9 mb-1" style="width: fit-content;">New Image Selected</span>
                        <a href="${fileUrl}" target="_blank" class="text-primary fw-bold fs-8 text-truncate" title="Open in new tab">
                            <i class="bi bi-box-arrow-up-right"></i> View Full Image (${fileSizeKb} KB)
                        </a>
                    </div>
                </div>
            `;
        } else {
            const icon = extension === 'pdf' ? '📄' : (['xls', 'xlsx', 'csv'].includes(extension) ? '📊' : '📎');
            previewHtml = `
                <div class="new-attachment-preview p-2 border border-info border-dashed rounded bg-light-info d-flex align-items-center gap-2">
                    <span style="font-size: 22px;">${icon}</span>
                    <div class="d-flex flex-column overflow-hidden">
                        <span class="badge badge-info px-1 py-0 fs-9 mb-1" style="width: fit-content;">New ${extension.toUpperCase()} Selected</span>
                        <a href="${fileUrl}" target="_blank" class="text-info fw-bold fs-8 text-truncate" title="Open in new tab">
                            <i class="bi bi-box-arrow-up-right"></i> View (${file.name})
                        </a>
                    </div>
                </div>
            `;
        }

        previewContainer.html(previewHtml).show();
    } else {
        previewContainer.hide().empty();
    }
    return true;
}

function validateFileType(input) {
    return handleAuditFileUpload(input);
}

// init
$(document).ready(function() {
    setTimeout(function() {
        calculateScore();
    }, 200);
    
    // prevent Enter submit in textarea
    $('textarea').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            var cursorPos = this.selectionStart;
            var textBefore = this.value.substring(0, cursorPos);
            var textAfter = this.value.substring(cursorPos);
            this.value = textBefore + '\n' + textAfter;
            this.setSelectionRange(cursorPos + 1, cursorPos + 1);
        }
    });
    
    $('form').on('submit', function(e) {
        var activeElement = document.activeElement;
        if (activeElement && activeElement.tagName === 'TEXTAREA') {
            e.preventDefault();
            return false;
        }
    });

    if (typeof $.fn.select2 !== 'undefined') {
        $('.js-example-basic-single').each(function() {
            if (this.id !== 'auditee_name_select') {
                $(this).select2({ width: '100%', placeholder: 'Select an option', allowClear: true });
            }
        });

        if ($('#auditee_name_select').length) {
            $('#auditee_name_select').select2({
                width: '100%',
                placeholder: 'Select Auditee Name',
                allowClear: true,
                tags: true
            });

            $('#auditee_name_select').on('change', function() {
                var val = $(this).val();
                if (val === '__other__') {
                    $('#auditee_other_container').slideDown(150);
                    var otherVal = $('#auditee_name_other').val().trim();
                    $('#auditee_name_input').val(otherVal);
                    $('#auditee_name_other').focus();
                } else {
                    $('#auditee_other_container').slideUp(150);
                    $('#auditee_name_input').val(val || '');
                }
            });

            $('#auditee_name_other').on('input', function() {
                if ($('#auditee_name_select').val() === '__other__') {
                    $('#auditee_name_input').val($(this).val().trim());
                }
            });

            $('form').on('submit', function() {
                if ($('#auditee_name_select').val() === '__other__') {
                    $('#auditee_name_input').val($('#auditee_name_other').val().trim());
                } else {
                    var sVal = $('#auditee_name_select').val();
                    if (sVal) {
                        $('#auditee_name_input').val(sVal);
                    }
                }
            });
        }
    }
    
    var initialLoc = $('#location_reaudit').val();
    if (initialLoc && $('#region').length) {
        $('#location_reaudit').trigger('change');
    }
    
    $('#location_reaudit').on('change', function() {
        let locationName = $(this).val().trim().toLowerCase();
    
        if (locationName) {
            <?php if(isset($locations)) {
                foreach($locations as $loc) { 
                    $locName = strtolower(trim($loc['location_name']));
                    $regionName = $loc['region_name'] ?? ''; ?>
                    if (locationName === <?= json_encode($locName); ?>) {
                        let regionValue = <?= json_encode($regionName); ?>;
                        $('#region option').filter(function() {
                            return $(this).val().toLowerCase().trim() === regionValue.toLowerCase().trim();
                        }).prop('selected', true);
                        return false;
                    }
                <?php } } ?>
        }
    });
});

// filter: yes/no/attempted
$(document).on('change', '#answerFilterReaudit', function(){
    var val = $(this).val();
    $('.audit-row').each(function() {
        var cell = $(this).find('td.finding-cell');
        var yesChecked = cell.find('input[type="checkbox"][value="1"]').is(':checked');
        var noChecked = cell.find('input[type="checkbox"][value="0"]').is(':checked');

        var show = true;
        if (val === 'attempted') {
            show = yesChecked || noChecked;
        } else if (val === 'yes') {
            show = yesChecked;
        } else if (val === 'no') {
            show = noChecked;
        } else if (val === 'na') {
            show = !yesChecked && !noChecked;
        } else {
            show = true;
        }
        $(this).toggle(show);
    });
});

// simple validation
// function validateReaudit(){
//     if($("input[name=auditor_name]").val()=="" || $("input[name=auditor_name]").val()==null){
//         alert("Error: Auditor Name is missing. Please contact administrator.");
//         return false;
//     }
//     if($("input[name=client_name]").val()=="" || $("input[name=client_name]").val()==null){
//         alert("Error: Location is missing. Please contact administrator.");
//         return false;
//     }
//     if($("input[name=region]").val()=="" || $("input[name=region]").val()==null){
//         alert("Error: Region is missing. Please contact administrator.");
//         return false;
//     }
//     return true;
// }
function validateReaudit(){

    // Mandatory audit header fields
    var auditorName = $("input[name=auditor_name]").val() || $("select[name=auditor_name]").val();
    if(!auditorName || auditorName === ""){
        alert("Error: Auditor Name is missing. Please contact administrator.");
        return false;
    }
    var clientName = $("input[name=client_name]").val() || $("select[name=client_name]").val();
    if(!clientName || clientName === ""){
        alert("Error: Client Name is missing. Please contact administrator.");
        return false;
    }
    var region = $("input[name=region]").val() || $("#region_hidden").val();
    if(!region || region === ""){
        alert("Error: Region is missing. Please contact administrator.");
        return false;
    }

    // ================= CHECK ALL CHECKBOXES ARE SELECTED ===================
    // changes on 13/03/26: Skip validation if it's an HSE audit (only OE requires mandatory selection)
    let auditNameVal = ($("#audit_name").val() || $("input[name='audit_name']").val() || "").toUpperCase();
    if (!auditNameVal.includes("HSE")) {
        var totalRows = $("input[type='checkbox'][name^='check_']").length / 3;

        for (let i = 0; i < totalRows; i++) {

            var yesChecked = $("input[name='check_" + i + "'][value='1']").is(":checked");
            var noChecked  = $("input[name='check_" + i + "'][value='0']").is(":checked");
            var naChecked  = $("input[name='check_" + i + "'][value='NA']").is(":checked");

            if (!yesChecked && !noChecked && !naChecked) {
                alert("Please answer Yes / No for row no: " + (i + 1));
                $("input[name='check_" + i + "'][value='1']").focus();
                return false;
            }
        }
    }

    // ========================================================================

    return true;
}

// category filter
$(document).on('change', '#categoryFilterReaudit', function(){
    var cat = $(this).val();
    $('.audit-row').each(function(){
        var rowCat = $(this).data('category');
        var show = (cat === '__all__') ? true : (String(rowCat) === String(cat));
        $(this).toggle(show);
    });
});

</script>          
<?php $this->endSection();?>


