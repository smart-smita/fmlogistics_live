<?php
   // changes on 16/11/25: Add safety check to prevent undefined offset error
   if (isset($details) && is_array($details) && count($details) > 0) {
       $details = $details[0];
   } else {
       // Fallback: initialize empty details array
       $details = [];
   }
?>
<style>
   /* changes on 7/10/25 by darsh: Improved table styling for better PDF rendering */
   table, th, td {
      border: 1px solid black;
   }
   th {
      height: 50px;
      background-color: #1e1c77;
      color: white;
      border: 1px solid yellow !important;
      text-align: center;
      font-weight: bold;
      padding: 8px;
   }
   td {
      height: auto;
      min-height: 50px;
      padding: 8px;
      vertical-align: top;
      word-wrap: break-word;
      overflow-wrap: break-word;
   }
   table {
      width: 100%;
      border-collapse: collapse; 
      table-layout: fixed;
      /* changes on 05/11/25 by darsh: reduce unexpected page breaks within rows */
      page-break-inside: auto;
   }

   /* IMPORTANT: repeat header row on each PDF page */
   thead {
      display: table-header-group;
   }
   tfoot {
      display: table-footer-group;
   }
   
  /* changes on 05/11/25 by darsh: Rebalanced widths to give more space to Target */
  .col-sr { width: 4%; }
  .col-category { width: 11%; }
  .col-question { width: 15%; }
  .col-parameter { width: 23%; } /* Target */
  .col-weightage { width: 5%; }
  .col-finding { width: 7%; }
  .col-score { width: 5%; }
  .col-remark { width: 13%; }
  .col-attachment { width: 12%; }
   
   body {
       border-style: groove;
      padding: 10px;
      font-family:sans-serif; 
   }
   html {
      margin-left: 5%;
   }
  /* changes on 15/10/25 by darsh: prevent page break between section heading and table */
  .keep-together { page-break-inside: avoid; }
  /* changes on 05/11/25 by darsh: keep rows intact to avoid orphaned header/empty gaps */
  tr { page-break-inside: avoid; page-break-after: auto; }
   
   /* changes on 7/10/25 by darsh: Add page break handling for long tables */
   .page-break {
      page-break-before: always;
   }
   
   /* FIXED: Attachment image styling to prevent overflow */
   .attachment-image {
      max-width: 100%;
      width: 100px;
      height: auto;
      max-height: 100px;
      object-fit: contain;
      display: block;
      margin: 2px auto;
   }
   
   /* Attachment container to control overflow */
   .attachment-container {
      max-width: 100%;
      overflow: hidden;
      text-align: center;
      padding: 2px;
   }
   
   /* changes on 7/10/25 by darsh: Ensure proper text wrapping */
   .text-wrap {
      word-wrap: break-word;
      overflow-wrap: break-word;
      white-space: normal;
   }

   .badge {
      display: inline-block;
      min-width: 10px;
      padding: 10px;
      font-size: 16px;
      font-weight: 700;
      color: #fff;
      line-height: 1;
      vertical-align: baseline;
      white-space: nowrap;
      text-align: center;
      background-color: #999;
      border-radius: 10px;
   }

   .badge-success {
      background-color: #468847;
   }

   .badge-danger {
      background-color: #b94a48;
   }

   .badge-warning {
      background-color: #ffc107;
   }

   .badge-secondary {
      background-color: #868e96;
   }
   .margin {
     margin: 35px;
   }
   
  .page_break { 
      page-break-before: always; 
  }
  
</style>
<h4><?=(isset($page_title))?$page_title:""?></h4>
<div>
   	<div align="center">
		<h2 style="display: inline-block; border-bottom: 3px double black;"><u><?= isset($report_title) ? htmlspecialchars($report_title) : 'OE Audit Report' ?> </u></h2>
	</div>
   <div style="width:49%; float:left">
      <h3><u>Auditor Details</u></h3>
      <table border="0">
         <tbody>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Audit No.</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['audit_no'] ?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;" >Audit Name</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['audit_name']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Auditor Name</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['auditor_name']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Audit Date</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['audit_date']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Client Name</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['client_name']?>
               </td>
            </tr>
         </tbody>
      </table>
   </div>
   <div style="width:49%;float:right">
      <h4><u>Site Details</u></h4>
      <table>
         <tbody>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Auditee Name</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                 <?php
                 // changes on 05/11/25 by darsh: Fallback fetch for missing Auditee/Region using audit tables
                 if (empty($details['auditee_name']) || empty($details['region'])) {
                     try {
                         $db = \Config\Database::connect();
                         $auditNo = $details['audit_no'] ?? '';
                         if (!empty($auditNo)) {
                             $isNormalFallback = (strpos(strtolower($details['audit_name'] ?? ''), 'normal') !== false);
                             $headerTable = $isNormalFallback ? 'alert_normal_audit' : 'alert_final_structured_audit';
                             $query = $db->table($headerTable)->select('auditee_name, region')->where('audit_no', $auditNo)->get(1);
                             if ($rowHdr = $query->getRowArray()) {
                                 $details['auditee_name'] = $details['auditee_name'] ?: ($rowHdr['auditee_name'] ?? '');
                                 $details['region'] = $details['region'] ?: ($rowHdr['region'] ?? '');
                             }
                         }
                     } catch (\Throwable $e) {
                         // ignore in view fallback
                     }
                 }
                 echo $details['auditee_name'];
                 ?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Region</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                 <?= $details['region'] ?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Next Audit Date</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['next_date']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Score</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['audit_score']?>%
               </td>
            </tr>
         </tbody>
      </table>
   </div>
   
   <?php 
  // changes on 7/10/25 by darsh: Add proper data validation and error handling
  $audit_details = isset($audit_details) && is_array($audit_details) ? $audit_details : [];
  ?>
   <div style="clear:both; height:20px;"></div>

<!-- ================= AUDITEE ATTENDANCE ================= -->
<?php if (!empty($attendance) && is_array($attendance)): ?>
<div>
    <h3 style="margin-bottom: 5px;"><u>Auditee Attendance</u></h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">Sr No</th>
                <th style="width: 25%;">Auditee Name</th>
                <th style="width: 15%;">Opening Date</th>
                <th style="width: 20%;">Opening Sign</th>
                <th style="width: 15%;">Closing Date</th>
                <th style="width: 20%;">Closing Sign</th>
            </tr>
        </thead>
        <tbody>
            <?php $idx = 1; foreach ($attendance as $att): ?>
                <tr>
                    <td style="text-align:center"><?= $idx++ ?></td>
                    <td><?= htmlspecialchars($att['auditee_attendance'] ?? '') ?></td>
                    <td style="text-align:center"><?= !empty($att['opening_date']) ? date('d-m-Y', strtotime($att['opening_date'])) : 'N/A' ?></td>
                    <td style="text-align:center">
                        <?php if (!empty($att['opening_sign'])): ?>
                            <a href="<?= base_url($att['opening_sign']) ?>" target="_blank">
                                <img src="<?= FCPATH . $att['opening_sign'] ?>" class="attachment-image" style="max-height:60px;" alt="Sign">
                            </a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center"><?= !empty($att['closing_date']) ? date('d-m-Y', strtotime($att['closing_date'])) : 'N/A' ?></td>
                    <td style="text-align:center">
                        <?php if (!empty($att['closing_sign'])): ?>
                            <a href="<?= base_url($att['closing_sign']) ?>" target="_blank">
                                <img src="<?= FCPATH . $att['closing_sign'] ?>" class="attachment-image" style="max-height:60px;" alt="Sign">
                            </a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

  <!-- Start checklist table on new page -->
  <table class="keep-together" style="width: 100%; table-layout: fixed; margin-top: 0; page-break-before: always;">
    <thead>
        <tr>
            <td class="col-sr" style="text-align:center; font-weight:bold; color:#fff; background-color:#1e1c77; border:1px solid yellow;">Sr No.</td>
            <td class="col-category" style="text-align:center; font-weight:bold; color:#fff; background-color:#1e1c77; border:1px solid yellow;">Category</td>
            <td class="col-question" style="text-align:center; font-weight:bold; color:#fff; background-color:#1e1c77; border:1px solid yellow;">Audit Questions</td>
            <td class="col-parameter" style="text-align:center; font-weight:bold; color:#fff; background-color:#1e1c77; border:1px solid yellow;">Target</td>
            <td class="col-weightage" style="text-align:center; font-weight:bold; color:#fff; background-color:#1e1c77; border:1px solid yellow;">Weightage (%)</td>
            <td class="col-finding" style="text-align:center; font-weight:bold; color:#fff; background-color:#1e1c77; border:1px solid yellow;">Audit Findings (Yes / No)</td>
            <td class="col-score" style="text-align:center; font-weight:bold; color:#fff; background-color:#1e1c77; border:1px solid yellow;">Audit Score</td>
            <td class="col-remark" style="text-align:center; font-weight:bold; color:#fff; background-color:#1e1c77; border:1px solid yellow;">Remarks</td>
            <td class="col-attachment" style="text-align:center; font-weight:bold; color:#fff; background-color:#1e1c77; border:1px solid yellow;">Attachments / Images</td>
        </tr>
    </thead>
    <tbody>
         <?php 
         foreach($audit_details as $key=>$row){
            ?>
         <tr>
            <td class="col-sr" style="text-align:center; padding: 5px;"><?=$key+1?></td>
            <td class="col-category text-wrap" style="text-align:left; padding: 5px;">
                <?= htmlspecialchars($row['category'] ?? 'N/A') ?>
            </td>
            <?php
                // changes on 05/11/25 by darsh: Align Normal vs OE mapping like perform views
                $tplType = strtolower(trim($details['audit_template_type'] ?? (strpos(strtolower($details['audit_name'] ?? ''), 'normal') !== false ? 'normal' : 'oe')));
                $isNormalTpl = ($tplType === 'normal');
                $displayQuestion = $isNormalTpl ? ($row['audit_question'] ?? '') : ($row['audit_parameter'] ?? '');
                $displayTarget   = $row['risk_priority'] ?? '';
            ?>
            <td class="col-question text-wrap" style="text-align:left; padding: 5px;">
                <?= htmlspecialchars($displayQuestion ?: 'N/A') ?>
            </td>
            <td class="col-parameter text-wrap" style="text-align:left; padding: 5px;">
                <pre style="white-space:pre-wrap; margin:0; font-family:sans-serif; font-size:inherit;"><?= htmlspecialchars($displayTarget ?: 'N/A') ?></pre>
            </td>
            <td class="col-weightage" style="text-align:center; padding: 5px;">
                <?= htmlspecialchars($row['weightage'] ?? '0') ?>%
            </td>
            <td class="col-finding" style="text-align:center; padding: 5px;">
                <?= htmlspecialchars($row['audit_finding'] ?? '') ?>
            </td>
            <td class="col-score" style="text-align:center; padding: 5px;">
                <?php if (strtoupper($row['audit_finding'] ?? '') == "YES"): ?>
                    <?= htmlspecialchars($row['weightage'] ?? '0') ?>%
                <?php else: ?>
                    0
                <?php endif; ?>
            </td>
            <td class="col-remark text-wrap" style="text-align:left; padding: 5px;">
                <?= nl2br(htmlspecialchars($row['audit_remark'] ?? '')) ?>
            </td>
            <td class="col-attachment" style="text-align:center; padding: 5px; overflow: hidden;">
                <div class="attachment-container">
                <?php 
                $hasAttachment = false;
                
                if (!empty($row['audit_attachment'])) {
                    $path = $row['audit_attachment'];
                    $mimeType = '';
                    $imageData = false;
                    $publicUrl = base_url($path);   // URL used in link
                    
                    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
                        $imageInfo = @getimagesize($path);
                        if ($imageInfo && isset($imageInfo['mime'])) { $mimeType = $imageInfo['mime']; }
                        $imageData = @file_get_contents($path);
                        $publicUrl = $path; // already full URL
                    } else {
                        $full = (defined('FCPATH') ? FCPATH : $_SERVER['DOCUMENT_ROOT'].'/').ltrim($path,'/\\');
                        if (file_exists($full)) {
                            if (function_exists('finfo_open')) {
                                $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                                if ($finfo) {
                                    $mimeType = @finfo_file($finfo, $full) ?: '';
                                    @finfo_close($finfo);
                                }
                            }
                            
                            // Fallback for images if finfo failed or is missing
                            if (empty($mimeType)) {
                                $imageInfo = @getimagesize($full);
                                if ($imageInfo && isset($imageInfo['mime'])) {
                                    $mimeType = $imageInfo['mime'];
                                }
                            }
                            
                            $imageData = @file_get_contents($full);
                        }
                    }
                    
                    if ($imageData && $mimeType && strpos($mimeType, 'image/') === 0) {
                        $base64 = base64_encode($imageData);
                        $imageBase64 = 'data:' . $mimeType . ';base64,' . $base64;
                        ?>
                        <!-- Image wrapped in link so it is clickable in PDF and HTML -->
                        <a href="<?= $publicUrl ?>" target="_blank">
                            <img src="<?= $imageBase64 ?>" class="attachment-image" alt="Audit Attachment" />
                        </a>
                        <?php
                        $hasAttachment = true;
                    } else {
                        // Non-image file: show normal "View File" link
                        echo '<a href="' . $publicUrl . '" target="_blank" style="font-size:11px; word-break:break-all;">View File</a>';
                        $hasAttachment = true;
                    }
                } else {
                    try {
                        $db = \Config\Database::connect();
                        $auditNo = $details['audit_no'] ?? '';
                        if (!empty($auditNo)) {
                            $isNormalDtl = (strpos(strtolower($details['audit_name'] ?? ''), 'normal') !== false);
                            $detailTable = $isNormalDtl ? 'alert_normal_audit_details' : 'alert_final_structured_audit_details';
                            $builder = $db->table($detailTable)->select('audit_attachment')->where('audit_no', $auditNo);
                            if (!empty($row['category'])) { $builder->where('category', $row['category']); }
                            if (!empty($row['audit_parameter'])) { $builder->where('audit_parameter', $row['audit_parameter']); }
                            $q = $builder->get(1);
                            if ($r = $q->getRowArray()) {
                                $fallbackPath = $r['audit_attachment'] ?? '';
                                if (!empty($fallbackPath)) {
                                    $fallbackUrl = base_url($fallbackPath);
                                    echo '<a href="' . $fallbackUrl . '" target="_blank" style="font-size:11px; word-break:break-all;">View File</a>';
                                    $hasAttachment = true;
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore in view
                    }
                }
                
                if (!$hasAttachment) {
                    echo '<span style="color: #999; font-size:11px; ">-</span>';
                }
                ?>
                </div>
            </td>
            
         </tr>
         <?php } ?>
         
         <?php if (empty($audit_details)): ?>
         <tr>
             <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                 No audit questions found for this audit.
             </td>
         </tr>
         <?php endif; ?>
      </tbody>
   </table>
</div>


<script></script>
