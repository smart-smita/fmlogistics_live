<?php $this->extend("Layout/base_admin"); ?>
<?php 
    $this->section("breadcrumb_title_li");
?>

<style>
      #result {
            /*border: 1px solid #ccc;*/
            /*max-width: 300px;*/
            /*margin-top: 5px;*/
            /*position: absolute;*/
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
        /* changes on 14/10/25 by darsh: Select2 dropdown styling */
        span.select2-selection.select2-selection--single.form-select.js-example-basic-single {
            height: 38px !important;
        }
        .select2-container .select2-selection--single {
            height: 38px !important;
        }

        /* === NEW: sticky thead for Audit CheckList table === */
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
        <strong>Access Denied:</strong> You do not have permission to perform audits. Only Auditors can perform audits.
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
?>

<?php

?>
<form action="<?=$action?>" method="post" enctype='multipart/form-data' onsubmit="return validate()">
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title"><?=(isset($page_title))?$page_title:""?></h4>
        
        <div class="col-xl-4" style="float: right;">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importExcelModel">
                Upload Migration Data for OE
            </button>
        </div>
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
                                        <?php
                                        if (isset($details['audit_no']) && !empty($details['audit_no'])) {
                                            $final_audit_no = $details['audit_no'];
                                        } else {
                                            $prefix = isset($details['audit_name']) ? $details['audit_name'] : 'AUD';
                                            $suffix = isset($next_audit_id) ? $next_audit_id : rand(100,999);
                                            $final_audit_no = $prefix . "-" . date("Y-m-d") . "-" . $suffix;
                                        }
                                        ?>
                                        <input type="text" class="form-control" name="audit_no" id="audit_no" value="<?= htmlspecialchars($final_audit_no) ?>" readonly class="form-control-disabled" />
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
                                       <!-- changes on 14/10/25 by darsh: Converted to Select2 dropdown with search for better UX -->
                                       <select class="form-select js-example-basic-single" id="auditor_name" name="auditor_name">
                                           <option value="">Select Auditor</option>
                                           <?php if(isset($auditors) && is_array($auditors)) {
                                               foreach($auditors as $auditor) { ?>
                                                   <option value="<?= $auditor['user_name'] ?>" <?php if(isset($details['auditor_name']) && $details['auditor_name'] == $auditor['user_name']) echo 'selected'; ?>><?= $auditor['user_name'] ?></option>
                                           <?php } } ?>
                                       </select>
                                    </td>
                                </tr>
                                 <tr>
                                    <th class="table-secondary">Audit Date</th>
                                    <td>
                                   <input type="date" name="audit_date" value="<?= isset($details['audit_date']) ? $details['audit_date'] : date("Y-m-d") ?>" />
                                    </td>
                                </tr>

                                <tr>
                                    <th class="table-secondary">Next Audit Date</th>
                                    <td>
                                       <input type="date" name="next_date" id="next_date" value="<?= isset($details['next_date']) ? $details['next_date'] : date("Y-m-d") ?>" required />
                                    </td>
                                </tr>
                               
                                <tr>
                                    <th class="table-secondary">Score</th>
                                    <td>
                            <!-- changes on 03/11/25: Show live calculated score as x/100 (only Yes adds weightage) -->
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
        <h4 class="card-title">Site Details</h4>
        <blockquote class="blockquote">
            <table class="table table-hover">
                            <tbody>
                                 <tr>
                                    <th class="table-secondary">Client Name</th>
                                    <td>
                                     
                                        
                                        <select class="form-select js-example-basic-single" name="location" id="location">
                                            <option value="">Select Client Name</option>
                                            <?php if (isset($location)) {
                                                foreach ($location as $loc) { 
                                                    $dispName = $loc['client_name'] ?? ($loc['location_name'] ?? '');
                                                    $manager  = $loc['account_manager'] ?? '';
                                                    $cluster  = $loc['cluster'] ?? '';
                                                    $locRegion = $loc['region'] ?? ($loc['region_name'] ?? '');
                                                    ?>
                                                   <option value="<?= htmlspecialchars($dispName, ENT_QUOTES); ?>"
    data-manager="<?= htmlspecialchars($manager, ENT_QUOTES); ?>"
    data-cluster="<?= htmlspecialchars($cluster, ENT_QUOTES); ?>"
    data-region="<?= htmlspecialchars($locRegion, ENT_QUOTES); ?>"
>
    <?= htmlspecialchars($dispName, ENT_QUOTES); ?>
</option>
                                            <?php } } ?>
                                        </select>

                                    </td>
                                </tr>
                                
                                 <tr>
                                    <th class="table-secondary">Auditee Name</th>
                                    <td>
                                        <div class="auditee-wrapper">
                                            <select id="auditee_name_select" class="form-select js-example-basic-single" style="width: 100%;">
                                                <option value="">Select Auditee Name</option>
                                                <?php 
                                                $initialAuditee = !empty($details['auditee_name']) ? trim($details['auditee_name']) : (!empty($client_account_manager) ? trim($client_account_manager) : '');
                                                $amList = isset($account_managers) ? $account_managers : [];
                                                if (!empty($initialAuditee) && !in_array($initialAuditee, $amList)) {
                                                    $amList[] = $initialAuditee;
                                                    sort($amList, SORT_NATURAL | SORT_FLAG_CASE);
                                                }
                                                foreach ($amList as $am) {
                                                    $sel = (strcasecmp($initialAuditee, $am) === 0) ? 'selected' : '';
                                                    echo '<option value="' . htmlspecialchars($am, ENT_QUOTES) . '" ' . $sel . '>' . htmlspecialchars($am) . '</option>';
                                                }
                                                ?>
                                                <option value="__other__">Other (Enter Manually)</option>
                                            </select>
                                            <div id="auditee_other_container" class="mt-2" style="display: none;">
                                                <input type="text" id="auditee_name_other" class="form-control" placeholder="Enter Auditee Name manually..." value="" />
                                            </div>
                                            <input type="hidden" name="auditee_name" id="auditee_name_input" value="<?= htmlspecialchars($initialAuditee, ENT_QUOTES); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr>
                                <th class="table-secondary">Region</th>
                                <td>
                                    <!-- changes on 16/11/25: Region starts as dropdown, locks after location selection -->
                                    <select class="form-select js-example-basic-single" name="region" id="region_dropdown" style="display: block;">
                                        <option value="">Select Region</option>
                                        <?php if(isset($region)) {
                                            foreach($region as $reg) { ?>
                                                <option value="<?= $reg['region_name']; ?>" <?php 
                                                    if(isset($details['region']) && strtolower(trim($reg['region_name'])) == strtolower(trim($details['region']))){ echo "selected"; } ?> ><?= $reg['region_name']; ?></option>
                                        <?php } } ?>
                                    </select>
                                    <input type="text" id="region_locked" class="form-control" readonly style="background-color: #f5f5f5; cursor: not-allowed; display: none;" value="">
                                    <input type="hidden" name="region" id="region_value" value="<?= isset($details['region']) ? $details['region'] : '' ?>">
                                </td>
                                </tr>
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
                  <!-- changes on 15/10/25 by darsh: move the new filter above table header -->
                  <div class="d-flex align-items-center" style="gap:10px;">
                      <label class="mb-0">Filter:</label>
                      <select id="answerFilterOE" class="form-select form-select-sm" style="width:auto;">
                          <option value="all">All</option>
                          <option value="attempted">Attempted</option>
                          <option value="yes">Yes</option>
                          <option value="no">No</option>
                          <option value="na">Not attempted</option>
                      </select>
                      <!-- changes on 4/11/025 by darsh: Category filter for Perform -->
                      <!--<label class="mb-0" style="margin-left:10px;">Category:</label>-->
                      <!--<?php $__cats = []; foreach($excel_details as $__r){ $__cats[strval($__r['category'])]=true; } ?>-->
                      <!--<select id="categoryFilterOE" class="form-select form-select-sm" style="width:auto;">-->
                      <!--    <option value="__all__">All</option>-->
                      <!--    <?php foreach(array_keys($__cats) as $__cat){ ?>-->
                      <!--        <option value="<?= htmlspecialchars($__cat) ?>"><?= htmlspecialchars($__cat) ?></option>-->
                      <!--    <?php } ?>-->
                      <!--</select>-->
                  </div>
              </div>
         </div>
        <div class="card-body">
            <div class="row">
                <!-- NEW: wrapper class added for sticky header -->
                <div class="col-md-12 audit-table-wrapper table-responsive">
                    <table class="table table-row-bordered align-middle text-gray-700 font-medium text-sm" >
                        <thead>
                        <tr>
                             <!-- changes on 4/11/025 by darsh: Sr No column -->
                             <th>Sr No</th>
                             <th>Category</th>
                             <th style="min-width: 250px;">
                                Audit Questions 

                            </th> 
                            <!--<th style="min-width: 350px;">-->
                            <!--    Audit Parameters-->

                            <!--</th>-->
                            <th style="min-width: 500px;">
                                Target
                            </th> <!-- changes on 13/10/25 by darsh: added max-width for better column sizing -->
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
                 $temp_location = "";
                 foreach($excel_details as $key=>$row){
                     
                 if($temp_location !=$row['location']){
                     $temp_location =$row['location'];
                     echo "<tr><th colspan='8'><br><center>$temp_location</center><th></tr>";
                 } 
                 ?>
                     <input type="hidden" name="location_list[]" value="<?=$temp_location?>">

                     <input type="hidden" name="category[]" value="<?=$row['category']?>">
                     <input type="hidden" name="audit_question[]" value="<?=htmlspecialchars($row['audit_question'] ?? '')?>">
                     <input type="hidden" name="audit_parameter[]" value="<?=htmlspecialchars($row['audit_parameter'] ?? '')?>">
                     <input type="hidden" name="risk_priority[]" value="<?=$row['risk_priority']?>">
                     <input type="hidden" name="weightage[]" value="<?=$row['weightage']?>">
                    <tr class="audit-row" data-category="<?= htmlspecialchars($row['category']) ?>">
                       
                        <!-- changes on 4/11/025 by darsh: Sr No displayed and posted -->
                        <td>
                            <?= ($key+1) ?>
                            <input type="hidden" name="sr_no[]" value="<?= ($key+1) ?>">
                        </td>
                        <td class="category-cell">
                            <lable class="form-label"><?=$row['category']?></lable>
                        </td>
                       <?php
                           $isNormal = isset($details['audit_template_type']) && strtolower(trim($details['audit_template_type'])) === 'normal';
                           $displayQuestion = $isNormal ? ($row['audit_question'] ?? '') : ($row['audit_parameter'] ?? '');
                           $displayTarget   = $row['risk_priority'] ?? '';
                       ?>
                        <td>
                            <lable class="form-label"><?=$displayQuestion?></lable>
                        </td>
                       <td>
                            <!-- changes on 4/11/025 by darsh: preserve paragraph formatting from Excel; -->
                            <pre class="form-label" style="white-space:pre-wrap; margin:0;"><?=$displayTarget?></pre>
                        </td>
                        <td>
                            <lable class="form-label"><?=$row['weightage']?></lable>
                        </td>
                         
                        <td class="finding-cell" data-type="oe">
                            <!-- changes on 03/11/25: Yes adds weightage; No does not -->
                             <div class="flex gap-12">
                              <label class="form-label flex items-center gap-2.5 text-nowrap">
                               Yes <input class="checkbox checkbox_data" name="check_<?=$key?>" type="checkbox" value="1" data-score="<?=$row['weightage']?>" <?php $k=strtolower(trim($row['audit_question'])); echo (isset($audit_details[$k]['audit_finding']) && $audit_details[$k]['audit_finding']=="YES")?"checked":""; ?>/>
                              </label>
                                  &nbsp;&nbsp;
                              <label class="form-label flex items-center gap-2.5 text-nowrap">
                               No <input  class="checkbox" name="check_<?=$key?>" type="checkbox" value="0" data-score="0" <?php $k=strtolower(trim($row['audit_question'])); echo (isset($audit_details[$k]['audit_finding']) && $audit_details[$k]['audit_finding']=="NO")?"checked":""; ?> />
                              </label>
                                  &nbsp;&nbsp;
                              <label class="form-label flex items-center gap-2.5 text-nowrap">
                               NA <input class="checkbox checkbox_na_score" name="check_<?=$key?>" type="checkbox" value="NA" data-score="<?=$row['weightage']?>" <?php $k=strtolower(trim($row['audit_question'])); echo (isset($audit_details[$k]['audit_finding']) && $audit_details[$k]['audit_finding']=="NA")?"checked":""; ?> />
                              </label>
                             </div>
                        </td>
                        <td>
                            <textarea name="remark[]" class="textarea border-danger" placeholder="Please add your remark" rows="6" value=""><?php $k=strtolower(trim($row['audit_question'])); echo (isset($audit_details[$k]['audit_remark']) && $audit_details[$k]['audit_remark']!="")?$audit_details[$k]['audit_remark']:""; ?></textarea>
                        </td>
                        <td class="attachment-cell">
                            <?php 
                                $k = strtolower(trim($row['audit_question'] ?? ''));
                                $existing = isset($audit_details[$k]['audit_attachment']) ? trim($audit_details[$k]['audit_attachment']) : '';
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
                        <span class="text-primary fw-bolder fs-3"><span id="scoreDisplayFooter">0</span>%</span>
                    </div>
                    <div class="vr h-30px opacity-25"></div>
                    <div class="d-flex flex-column text-start">
                        <span class="text-muted fw-semibold" style="font-size: 11px;">Audit Mode</span>
                        <span class="badge badge-light-success fs-7 fw-bold">Live Audit</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
 </form>

 <!--begin::Modal - Create App-->
        <div class="modal fade" id="importExcelModel" tabindex="-1" aria-hidden="true">
            <form id="form" action="<?= base_url(); ?>/Masters/Audit_template/import_perform_audit" method="POST" enctype="multipart/form-data">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered mw-900px">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header">
                        <!--begin::Modal title-->
                        <h2>Create Audit Template</h2>
                        <!--end::Modal title-->
                        <!--begin::Close-->
                        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                            <span class="svg-icon svg-icon-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                                    <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body py-lg-10 px-lg-10">
                        <div class="scroll-y me-n7 pe-7" id="user" data-kt-scroll="false" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_new_address_header" data-kt-scroll-wrappers="#fees_head" data-kt-scroll-offset="300px" style="max-height: 273px;">
                                <!--begin::Input group-->
                                <div class="row mb-12">
                                    <!--begin::Col-->
                                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="required fs-5 fw-bold mb-2">Upload File 1</label>
                                        <input type="file" class="form-control form-control-solid" id="file1" name="file1" accept=".jpg,.jpeg,.png,.gif,.pdf,.xls,.xlsx" onchange="validateFileType(this)" required>
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="required fs-5 fw-bold mb-2">Upload File 2</label>
                                        <input type="file" class="form-control form-control-solid" id="file2" name="file2" accept=".jpg,.jpeg,.png,.gif,.pdf,.xls,.xlsx" onchange="validateFileType(this)" required>
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Input group-->
                            </div>
                    </div>
                    <!--end::Modal body-->
                    
                    <!--begin::Modal footer-->
                    <div class="modal-footer flex-center">
                            <!--begin::Button-->
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                            <!--end::Button-->
                            
                            <!--begin::Button-->
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">Submit</span>
                                <span class="indicator-progress">Please wait... 
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                            <!--end::Button-->
                            
                        </div>
                        <!--end::Modal footer-->
                        
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </form>
        </div>
        <!--end::Modal - Create App-->
                         
                         
<?php                 $this->endSection();?>
    

<?php                     $this->section("javascript_section");?>
<!-- changes on 14/10/25 by darsh: Added Select2 CSS and JS libraries -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>

    function clientOnselect() {
        $("#client_name").val($("#client_id option:selected").text());
    }

var final_score = 0;

// Changes on 05/11/25 by darsh: Show precise decimal percentage (no integer rounding)
function calculateScore() {
    var totalScore = 0;
    var totalWeightage = 0;
    
    // Use native DOM for better performance
    var noCheckboxes = document.querySelectorAll('.checkbox_data:checked, .checkbox_na_score:checked');
    var allCheckboxes = document.querySelectorAll('.checkbox_data');
    
    // Calculate total possible weightage
    for (var i = 0; i < allCheckboxes.length; i++) {
        var weight = parseFloat(allCheckboxes[i].getAttribute('data-score')) || 0;
        totalWeightage += weight;
    }
    
    // Calculate actual score from "No" responses (which are actually Yes/NA in HTML)
    for (var i = 0; i < noCheckboxes.length; i++) {
        var weight = parseFloat(noCheckboxes[i].getAttribute('data-score')) || 0;
        totalScore += weight;
    }
    
    var isNormal = <?= (isset($details['audit_template_type']) && strtolower(trim($details['audit_template_type'])) === 'normal') ? 'true' : 'false' ?>;
    
    // Validate and update display
    var percentage = 0;
    
    if (isNormal) {
        // Normal Audit just uses the addition of the weightages directly
        percentage = totalScore;
        $('#finalScoreDenominator').text(totalWeightage);
    } else {
        if (totalWeightage > 0) {
            percentage = (totalScore / totalWeightage) * 100;
        }
    }

    // changes on 05/11/25 by darsh: format score as integer when whole, otherwise up to 2 decimals without trailing zeros
    var display = percentage === 0 ? '0' : String(parseFloat(percentage.toFixed(2)));
    $('#scoreDisplay').text(display);
    $('#scoreDisplayFooter').text(display);
    // store numeric up to 2 decimals (no integer rounding to whole number)
    $('#scoreValue').val(parseFloat(percentage.toFixed(2)));
    
    console.log("Score: " + totalScore + "/" + totalWeightage + " (" + (totalWeightage > 0 ? (parseFloat(percentage.toFixed(2))) : 0) + (isNormal ? "" : "%") + ")");
}

// Changes on 28/10/25 by darsh: Performance optimized checkbox handling
var scoreUpdateScheduled = false;

function scheduleScoreUpdate() {
    if (scoreUpdateScheduled) return;
    scoreUpdateScheduled = true;
    requestAnimationFrame(function() {
        scoreUpdateScheduled = false;
        calculateScore();
    });
}

// Event delegation for better performance
$(document).on('click', '.checkbox', function() {
    var name = $(this).attr('name');
    if (!name) return;
    
    // Fast mutual exclusivity using native DOM
    var checkboxes = document.querySelectorAll('input[name="' + CSS.escape(name) + '"]');
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i] !== this) {
            checkboxes[i].checked = false;
        }
    }
    
    // Batch score updates to avoid UI blocking
    scheduleScoreUpdate();
});

        // Enhanced file upload validation & live preview function
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
        
        $(document).ready(function() {
            // Changes on 4/10/25 by Darsh: Initialized score calculation on page load
            // This ensures pre-checked items are properly calculated when the page loads
            calculateScore();
            
            // changes on 16/11/25: Sync region dropdown changes to hidden field (before location selection)
            $('#region_dropdown').on('change', function() {
                $('#region_value').val($(this).val());
            });
            
            // changes on 14/10/25 by darsh: Initialize Select2 on all dropdowns with search functionality
            setTimeout(function() {
                console.log('jQuery loaded:', typeof $ !== 'undefined');
                console.log('Select2 loaded:', typeof $.fn.select2 !== 'undefined');
                console.log('Found elements:', $('.js-example-basic-single').length);
                
                if (typeof $.fn.select2 !== 'undefined') {
                    $('.js-example-basic-single').each(function() {
                        if (this.id !== 'auditee_name_select') {
                            $(this).select2({
                                placeholder: "Select an option",
                                allowClear: true,
                                width: '100%'
                            });
                        }
                    });

                    // Searchable Auditee Name dropdown with tags (custom addition) support
                    $('#auditee_name_select').select2({
                        placeholder: "Select Auditee Name",
                        allowClear: true,
                        tags: true,
                        width: '100%'
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

                    // If location is already selected on page load and auditee is empty, auto-sync
                    if ($('#location').val() && !$('#auditee_name_input').val()) {
                        $('#location').trigger('change');
                    }
                    console.log('Select2 initialization complete');
                } else {
                    console.error('Select2 library not loaded!');
                }
            }, 500);
            
            // changes on 16/11/25: Auto-fill and LOCK region when location is selected
           $('#location').on('change', function() {

    let selectedOption = $(this).find('option:selected');

    let managerName = selectedOption.data('manager');
    let regionValue = selectedOption.data('region');
    let clusterValue = selectedOption.data('cluster');

    // Auto-fill and select Account Manager under selected Client Name
    if (managerName && String(managerName).trim() !== '') {
        let cleanManager = String(managerName).trim();
        let found = false;
        $('#auditee_name_select option').each(function() {
            if ($(this).val().toLowerCase() === cleanManager.toLowerCase()) {
                cleanManager = $(this).val();
                found = true;
                return false;
            }
        });
        if (!found) {
            let newOption = new Option(cleanManager, cleanManager, true, true);
            $('#auditee_name_select').prepend(newOption);
        }
        $('#auditee_name_select').val(cleanManager).trigger('change');
        $('#auditee_other_container').hide();
        $('#auditee_name_input').val(cleanManager);
    } else {
        if ($('#auditee_name_select').val() !== '__other__' && !$('#auditee_name_input').val()) {
            $('#auditee_name_select').val('').trigger('change');
            $('#auditee_name_input').val('');
        }
    }

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

    // Set Region
    if (regionValue) {
        $('#region_dropdown').hide();
        if ($('#region_dropdown').next('.select2-container').length) {
            $('#region_dropdown').next('.select2-container').hide();
        }
        $('#region_locked').val(regionValue).show();
        $('#region_value').val(regionValue);
    } else {
        $('#region_locked').hide();
        $('#region_dropdown').show();
        if ($('#region_dropdown').next('.select2-container').length) {
            $('#region_dropdown').next('.select2-container').show();
        }
        $('#region_value').val($('#region_dropdown').val());
    }

});

    // Handle region dropdown changes
    $('#region_dropdown').on('change', function() {
        $('#region_value').val($(this).val());
    });

            
            // changes on 14/10/25 by darsh: Removed old autocomplete code as Select2 now handles auditor search
        });

        // changes on 15/10/25 by darsh: client-side filter for OE/Normal perform to show rows by Yes/No/Attempted
        $('#answerFilterOE').on('change', function() {
            var val = $(this).val();
            $('.audit-row').each(function() {
                var cell = $(this).find('.finding-cell');
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
                    show = true; // all
                }
                $(this).toggle(show);
            });
        });
        
        // changes on 4/11/025 by darsh: Category filter for Perform
        $('#categoryFilterOE').on('change', function(){
            var cat = $(this).val();
            $('.audit-row').each(function(){
                var rowCat = $(this).data('category');
                var show = (cat === '__all__') ? true : (String(rowCat) === String(cat));
                $(this).toggle(show);
            });
        });
        
         function validate(){
              // Auditor Details validation
              if($("#auditor_name").val()=="" || $("#auditor_name").val()==null){
                  alert("Please select Auditor Name");
                  $("#auditor_name").focus();
                  return false;
              }else if($("input[name=audit_date]").val()==""){
                  alert("Please select Audit Date");
                  $("input[name=audit_date]").focus();
                  return false;
              }else if($("input[name=next_date]").val()==""){
                  alert("Please select Next Audit Date");
                  $("input[name=next_date]").focus();
                  return false;
              }
              // Client Details validation
              else if($("#location").val()=="" || $("#location").val()==null){
                  alert("Please select Client / Site Location");
                  $("#location").focus();
                  return false;
              }else if($("input[name=auditee_name]").val().trim()==""){
                  alert("Please enter Auditee Name");
                  $("input[name=auditee_name]").focus();
                  return false;
              }else if($("#region_value").val()=="" || $("#region_value").val()==null){
                  alert("Please select Region (select location first)");
                  $("#location").focus();
                  return false;
              }
              return true;
          }

// File type validation function (modal uses this too)
function validateFileType(input) {
    const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xls', 'xlsx'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const extension = file.name.split('.').pop().toLowerCase();
        
        // Check file type
        if (!allowedTypes.includes(extension)) {
            alert('Invalid file type. Only images (JPG, PNG, GIF), PDFs, and Excel files (XLS, XLSX) are allowed.');
            input.value = '';
            return false;
        }
        
        // Check file size
        if (file.size > maxSize) {
            alert('File size too large. Maximum allowed size is 10MB.');
            input.value = '';
            return false;
        }
    }
    return true;
}

// NEW CHANGES: Fix textarea Enter key issue
$(document).ready(function() {
    // Prevent form submission when Enter is pressed in textarea fields
    $('textarea').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            // Allow Shift+Enter for new lines, but prevent Enter alone from submitting
            e.preventDefault();
            // Insert a new line at cursor position
            var cursorPos = this.selectionStart;
            var textBefore = this.value.substring(0, cursorPos);
            var textAfter = this.value.substring(cursorPos);
            this.value = textBefore + '\n' + textAfter;
            // Set cursor position after the new line
            this.setSelectionRange(cursorPos + 1, cursorPos + 1);
        }
    });
    
    // Also prevent form submission on Enter key in textarea
    $('form').on('submit', function(e) {
        var activeElement = document.activeElement;
        if (activeElement && activeElement.tagName === 'TEXTAREA') {
            e.preventDefault();
            return false;
        }
    });
});

// NEW VALIDATION: Prevent performing audit more than once for same client
function validate(){

    // Auditor Details validation
    if($("#auditor_name").val()=="" || $("#auditor_name").val()==null){
        alert("Please select Auditor Name");
        $("#auditor_name").focus();
        return false;
    }else if($("input[name=audit_date]").val()==""){
        alert("Please select Audit Date");
        $("input[name=audit_date]").focus();
        return false;
    }else if($("input[name=next_date]").val()==""){
        alert("Please select Next Audit Date");
        $("input[name=next_date]").focus();
        return false;
    }
    // Client Details validation
    else if($("#location").val()=="" || $("#location").val()==null){
        alert("Please select Client / Site Location");
        $("#location").focus();
        return false;
    }else if($("input[name=auditee_name]").val().trim()==""){
        alert("Please enter Auditee Name");
        $("input[name=auditee_name]").focus();
        return false;
    }else if($("#region_value").val()=="" || $("#region_value").val()==null){
        alert("Please select Region (select location first)");
        $("#location").focus();
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
                alert("Please answer Yes / No for row no: " + (i+1));
                $("input[name='check_" + i + "'][value='1']").focus();
                return false;
            }
        }
    }

    // --------------- CHECK DUPLICATE ENTRY ----------------
    let location = $("#location").val().trim();
    let auditName = $("input[name='audit_name']").val().trim();

    let isDuplicate = false;

    $.ajax({
        url: "<?= base_url('/Masters/Audit_template/checkPerformedAudit') ?>",
        type: "POST",
        data: { location: location, audit_name: auditName },
        async: false,
        success: function(response){
            if(response == "EXISTS"){
                isDuplicate = true;
            }
        }
    });

    if(isDuplicate){
        alert("Audit already performed for this client. You cannot perform it again.");
        return false;
    }

    return true;
}


</script>          
<?php $this->endSection();?>


