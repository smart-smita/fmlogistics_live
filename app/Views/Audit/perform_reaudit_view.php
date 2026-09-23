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
    max-height: 980px;        /* adjust as needed */
    overflow-y: auto;
    overflow-x: auto;
}

.audit-table-wrapper table {
    margin-bottom: 0;         /* no extra gap inside scroll area */
}

.audit-table-wrapper thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background-color: #f5f8fa;   /* match other tables */
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
                                   <!-- changes on 16/11/25: Auditee name read-only in reaudit -->
                                   <?php if(isset($is_edit_mode) && $is_edit_mode) { ?>
                                        <input type="text" name="auditee_name" class="form-control" value="<?=$details['auditee_name']?>">
                                   <?php } else { ?>
                                        <input type="text" class="form-control" value="<?=$details['auditee_name']?>" readonly class="form-control-disabled">
                                        <input type="hidden" name="auditee_name" value="<?=$details['auditee_name']?>" />
                                   <?php } ?>
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
                        <td>
                            <?php 
                                $existing = isset($existingBySrLoc['audit_attachment']) ? $existingBySrLoc['audit_attachment'] : '';
                                if (!empty($existing)) { ?>
                                <div style="margin-bottom:6px;">
                                    <a href="<?= base_url($existing) ?>" target="_blank">Existing Attachment</a>
                                </div>
                                <input type="hidden" name="existing_attachment[]" value="<?= esc($existing) ?>">
                            <?php } else { ?>
                                <input type="hidden" name="existing_attachment[]" value="">
                            <?php } ?>
                            <input type="file" name="audit_attachment[]" accept=".jpg,.jpeg,.png,.gif,.pdf,.xls,.xlsx" onchange="validateFileType(this)">
                        </td>
                        </tr>
                    
                 <?php } ?>
                 </table>
                 </div>
                 </div> 
            </div>
        </div>
    </div>
                         <input type="submit" class="btn btn-success" value="Save Details">

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

function handleFileSelect(event) {
    const file = event.files[0];
    if((file.size / 1024).toFixed(2)>100){
        $(event).val(null)
        alert("File not allowd more that 100KB");
        return false;
    }else{
        return true;
    }
}

// File type validation
function validateFileType(input) {
    const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xls', 'xlsx'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const extension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.includes(extension)) {
            alert('Invalid file type. Only images (JPG, PNG, GIF), PDFs, and Excel files (XLS, XLSX) are allowed.');
            input.value = '';
            return false;
        }
        
        if (file.size > maxSize) {
            alert('File size too large. Maximum allowed size is 10MB.');
            input.value = '';
            return false;
        }
    }
    return true;
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
        $('.js-example-basic-single').select2({ width: '100%', placeholder: 'Select an option', allowClear: true });
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


