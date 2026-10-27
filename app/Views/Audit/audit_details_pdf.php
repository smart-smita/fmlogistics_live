<?php
$details = $details ?? [];
$audit_details = is_array($audit_details ?? null) ? $audit_details : [];
$categoryKeys = is_array($categoryKeys ?? null) ? $categoryKeys : [];
$numCategories = max(1, count($categoryKeys));
$dynamicColWidth = round(40 / $numCategories, 2);
$totalCols = 3 + count($categoryKeys) + 2;

if (!function_exists('renderPdfImage')) {
function renderPdfImage($file, $label = '') {
    if (empty($file)) return '';

    if (filter_var($file, FILTER_VALIDATE_URL)) {
        return "<div><strong>$label</strong><br><a href='$file' target='_blank'>View File</a></div>";
    }

    $filePath = FCPATH . $file;
    $url = base_url($file);

    if (!file_exists($filePath)) {
        return "<div><strong>$label</strong><br><a href='$url' target='_blank'>View File</a></div>";
    }

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        try {
            $imageData = base64_encode(file_get_contents($filePath));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp'
            ];
            $mime = $mimeTypes[$ext] ?? 'image/jpeg';
            return "<div style='margin-bottom:3px;'>
                " . ($label ? "<strong>$label</strong><br>" : "") . "
                <img src='data:$mime;base64,$imageData' style='max-width:70px; max-height:70px; object-fit:contain;'>
            </div>";
        } catch (\Exception $e) {
            return "<div><strong>$label</strong><br><a href='$url' target='_blank'>View Image</a></div>";
        }
    }

    return "<div><strong>$label</strong><br><a href='$url' target='_blank'>View File</a></div>";
}
}
?>

<style>
   body {
      border: 1px solid #ccc;
      padding: 10px;
      font-family: sans-serif;
      font-size: 10px;
   }

   table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      margin-bottom: 15px;
   }

   table, th, td {
      border: 1px solid black;
   }

   th {
      background-color: #1e1c77;
      color: white;
      border: 1px solid #000 !important;
      font-weight: bold;
      text-align: center;
      padding: 5px 3px;
      font-size: 9px;
      word-wrap: break-word;
      overflow-wrap: break-word;
      vertical-align: middle;
   }

   td {
      padding: 5px 4px;
      font-size: 9px;
      word-wrap: break-word;
      overflow-wrap: break-word;
      vertical-align: top;
   }

   .layout-table {
      width: 100%;
      border: none !important;
      border-collapse: collapse;
      table-layout: fixed;
      margin-bottom: 15px;
   }

   .layout-table td {
      border: none !important;
      padding: 0;
      vertical-align: top;
   }

   .info-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
   }

   .info-table th {
      width: 40%;
      text-align: left;
      padding: 5px 8px;
      font-size: 10px;
   }

   .info-table td {
      padding: 5px 8px;
      font-size: 10px;
   }

   .page-break {
      page-break-before: always;
      break-before: page;
   }

   .header-small {
      font-size: 8.5px;
      line-height: 1.1;
   }
</style>

<div>
   <div align="center">
      <h2 style="display: inline-block; border-bottom: 3px double black; margin-bottom: 15px;"><u>HSE Structure Audit</u></h2>
   </div>

   <!-- ================= 2-COLUMN AUDITOR & CLIENT DETAILS ================= -->
   <table class="layout-table">
      <tr>
         <td style="width: 48%;">
            <h3 style="margin-top:0; margin-bottom: 8px;"><u>Auditor Details</u></h3>
            <table class="info-table">
               <tbody>
                  <tr>
                     <th>Audit No.</th>
                     <td><?= htmlspecialchars($details['audit_no'] ?? '') ?></td>
                  </tr>
                  <tr>
                     <th>Audit Name</th>
                     <td><?= htmlspecialchars($details['audit_name'] ?? '') ?></td>
                  </tr>
                  <tr>
                     <th>Auditor Name</th>
                     <td><?= htmlspecialchars($details['auditor_name'] ?? '') ?></td>
                  </tr>
                  <tr>
                     <th>Audit Date</th>
                     <td><?= htmlspecialchars($details['audit_date'] ?? '') ?></td>
                  </tr>
                  <tr>
                     <th>Location ID</th>
                     <td><?= htmlspecialchars($details['location'] ?? '') ?></td>
                  </tr>
               </tbody>
            </table>
         </td>
         <td style="width: 4%;">&nbsp;</td>
         <td style="width: 48%;">
            <h3 style="margin-top:0; margin-bottom: 8px;"><u>Client Details</u></h3>
            <table class="info-table">
               <tbody>
                  <tr>
                     <th>Client Name</th>
                     <td><?= htmlspecialchars($details['client_name'] ?? '') ?></td>
                  </tr>
                  <tr>
                     <th>Auditee Name</th>
                     <td><?= htmlspecialchars($details['auditee_name'] ?? '') ?></td>
                  </tr>
                  <tr>
                     <th>Region</th>
                     <td><?= htmlspecialchars($details['region'] ?? '') ?></td>
                  </tr>
                  <tr>
                     <th>Score</th>
                     <td>
                        <?= is_numeric($details['score'] ?? null) ? number_format((float)$details['score'], 2, '.', '') : ($details['score'] ?? '') ?>
                     </td>
                  </tr>
                  <tr>
                     <th>Perform Audit By</th>
                     <td><?= htmlspecialchars($details['perform_audit_by'] ?? $_SESSION['user_name'] ?? '') ?></td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </table>

   <!-- ================= AUDITEE ATTENDANCE ================= -->
   <div style="margin-top: 10px; margin-bottom: 15px;">
       <h3 style="margin-bottom: 6px;"><u>Auditee Attendance</u></h3>
       <table style="width:100%; table-layout:fixed;">
           <thead>
               <tr>
                   <th style="width: 6%;">Sr No</th>
                   <th style="width: 26%;">Auditee Name</th>
                   <th style="width: 16%;">Opening Date</th>
                   <th style="width: 18%;">Opening Sign</th>
                   <th style="width: 16%;">Closing Date</th>
                   <th style="width: 18%;">Closing Sign</th>
               </tr>
           </thead>
           <tbody>
               <?php if (!empty($attendance) && is_array($attendance)): ?>
                   <?php $idx = 1; foreach ($attendance as $att): ?>
                       <tr>
                           <td style="text-align:center;"><?= $idx++ ?></td>
                           <td><?= htmlspecialchars($att['auditee_attendance'] ?? '') ?></td>
                           <td style="text-align:center;"><?= !empty($att['opening_date']) ? date('d-m-Y', strtotime($att['opening_date'])) : 'N/A' ?></td>
                           <td style="text-align:center;">
                               <?= renderPdfImage($att['opening_sign'] ?? '', '') ?: 'N/A' ?>
                           </td>
                           <td style="text-align:center;"><?= !empty($att['closing_date']) ? date('d-m-Y', strtotime($att['closing_date'])) : 'N/A' ?></td>
                           <td style="text-align:center;">
                               <?= renderPdfImage($att['closing_sign'] ?? '', '') ?: 'N/A' ?>
                           </td>
                       </tr>
                   <?php endforeach; ?>
               <?php else: ?>
                   <tr>
                       <td colspan="6" style="text-align:center; font-weight:bold;">No Auditee Attendance Records Available</td>
                   </tr>
               <?php endif; ?>
           </tbody>
       </table>
   </div>

   <!-- ================= AUDIT QUESTION DETAILS ================= -->
   <div class="page-break"></div>

   <div style="text-align: center; margin-bottom: 15px;">
      <h3><u>Audit Questions & Responses</u></h3>
   </div>

   <table style="width:100%; table-layout:fixed;">
      <thead>
         <tr>
             <th style="width: 4%;">Sr.No.</th>
             <th style="width: 12%;">Audit Category</th>
             <th style="width: 24%;">Audit Question</th>

             <?php foreach($categoryKeys as $key => $label): ?>
                  <th style="width: <?= $dynamicColWidth ?>%;" class="header-small">
                     <?= nl2br(htmlspecialchars(wordwrap($label, 12, "\n", true))) ?>
                  </th>
             <?php endforeach; ?>

             <th style="width: 12%;">Remark</th>
             <th style="width: 8%;">Attachment</th>
         </tr>
      </thead>
      <tbody>
         <?php 
         foreach ($audit_details as $detail): 
            $categoryId = trim($detail['audit_category_id'] ?? '');
            $isHeader = (strlen($categoryId) === 1 && ctype_alpha($categoryId));

            if ($isHeader): ?>
               <tr style="background:#e0e0e0; font-weight:bold;">
                   <td colspan="<?= $totalCols ?>" style="text-align:left; font-size:10px; padding:6px;">
                       <b><?= htmlspecialchars($categoryId) ?>. <?= htmlspecialchars($detail['question_name'] ?? '') ?></b>
                   </td>
               </tr>
            <?php else: ?>
               <tr>
                   <td style="text-align:center; font-weight:bold;"><?= htmlspecialchars($categoryId) ?></td>
                   <td><?= htmlspecialchars($detail['question_name'] ?? '') ?></td>
                   <td><?= htmlspecialchars($detail['audit_question'] ?? '') ?></td>

                   <!-- CATEGORY VALUES -->
                   <?php foreach($categoryKeys as $key => $label): 
                       $value = $detail['categories'][$key] ?? '';
                       $color = $value == 'YES' ? 'green' : ($value == 'NO' ? 'red' : 'gray');
                   ?>
                       <td style="text-align:center; font-weight:bold; color:<?= $color ?>;">
                           <?= htmlspecialchars($value) ?>
                       </td>
                   <?php endforeach; ?>

                   <td>
                       <?php 
                           $remarks = [];
                           if(!empty($detail['remark'])) $remarks[] = "Remark: ".htmlspecialchars($detail['remark']);
                           if(!empty($detail['nc_remark'])) $remarks[] = "NC Remark: ".htmlspecialchars($detail['nc_remark']);
                           echo implode("<br>", $remarks);
                       ?>
                   </td>

                   <td style="text-align:center;">
                       <?php
                       $hasAttachment = false;
                       foreach (['nc_after_photo', 'attachment'] as $imgField) {
                           if (!empty($detail[$imgField])) {
                               $label = ($imgField === 'nc_after_photo') ? 'NC After' : 'Attachment';
                               echo renderPdfImage($detail[$imgField], $label);
                               $hasAttachment = true;
                           }
                       }
                       if (!$hasAttachment) {
                           echo '<span style="color:#999; font-size:9px;">No attachment</span>';
                       }
                       ?>
                   </td>
               </tr>
            <?php endif; 
         endforeach; ?>

         <?php if(empty($audit_details)): ?>
            <tr>
                <td colspan="<?= $totalCols ?>" style="text-align:center;">No data found</td>
            </tr>
         <?php endif; ?>
      </tbody>
   </table>

   <!-- ================= AUTOGRID REPORT ================= -->
   <?php if (isset($overallStats) && isset($categoryStats)): 
      $summaryColWidth = round(75 / $numCategories, 2);
      $catScoreColWidth = round(62 / $numCategories, 2);
   ?>
       <!-- Table 1: Overall Summary -->
       <div class="page-break" style="clear: both;"></div>
       
       <div align="center" style="margin-bottom: 20px;">
           <h2 style="display: inline-block; border-bottom: 3px double black;"><u>HSE Audit AutoGrid Report</u></h2>
       </div>
       
       <div style="clear: both; margin-bottom: 30px;">
            <h3 style="margin-bottom: 10px;"><u>Overall Summary for Compliance</u></h3>
            <table style="width:100%; table-layout:fixed;">
               <thead>
                  <tr>
                      <th style="width: 25%; text-align: left; padding-left: 8px;">Category</th>
                      <?php foreach($categoryKeys as $key => $label): ?>
                          <th style="width: <?= $summaryColWidth ?>%; text-align: center;"><?= htmlspecialchars($label) ?></th>
                      <?php endforeach; ?>
                  </tr>
               </thead>
               <tbody>
                  <tr>
                      <th style="text-align: left; padding-left: 8px; background-color: #f2f2f2; color: #000; border: 1px solid #000 !important;">Total Selected</th>
                      <?php foreach($overallStats as $stat): ?>
                          <td style="text-align:center; font-weight:bold;"><?= $stat['yes'] + $stat['no'] + $stat['na'] ?></td>
                      <?php endforeach; ?>
                  </tr>
                  <tr>
                      <th style="text-align: left; padding-left: 8px; background-color: #f2f2f2; color: #000; border: 1px solid #000 !important;">YES</th>
                      <?php foreach($overallStats as $stat): ?>
                          <td style="text-align:center; font-weight:bold; color:green;"><?= $stat['yes'] ?></td>
                      <?php endforeach; ?>
                  </tr>
                  <tr>
                      <th style="text-align: left; padding-left: 8px; background-color: #f2f2f2; color: #000; border: 1px solid #000 !important;">NO</th>
                      <?php foreach($overallStats as $stat): ?>
                          <td style="text-align:center; font-weight:bold; color:red;"><?= $stat['no'] ?></td>
                      <?php endforeach; ?>
                  </tr>
                  <tr>
                      <th style="text-align: left; padding-left: 8px; background-color: #f2f2f2; color: #000; border: 1px solid #000 !important;">NA</th>
                      <?php foreach($overallStats as $stat): ?>
                          <td style="text-align:center; font-weight:bold; color:gray;"><?= $stat['na'] ?></td>
                      <?php endforeach; ?>
                  </tr>
                  <tr>
                      <th style="text-align: left; padding-left: 8px; background-color: #1e1c77; color: #fff; border: 1px solid #000 !important;">Score</th>
                      <?php foreach($overallStats as $stat): ?>
                          <td style="text-align:center; font-weight:bold; background-color:#f9f9f9;"><?= $stat['percentage'] ?>%</td>
                      <?php endforeach; ?>
                  </tr>
               </tbody>
            </table>
       </div>
          
       <!-- Table 2: Categorywise Score (starts on new page to prevent overlap) -->
       <div class="page-break" style="clear: both;"></div>

       <div style="clear: both; margin-top: 15px;">
           <h3 style="margin-bottom: 10px;"><u>Categorywise/Headerwise Score of Audit Grid</u></h3>
           <table style="width:100%; table-layout:fixed;">
               <thead>
                   <tr>
                       <th style="width: 8%; text-align: center;">Sr. No.</th>
                       <th style="width: 30%; text-align: left; padding-left: 8px;">Category</th>
                       <?php foreach($categoryKeys as $key => $label): ?>
                         <th style="width: <?= $catScoreColWidth ?>%; text-align: center;" class="header-small">
                            <?= nl2br(htmlspecialchars(wordwrap($label, 12, "\n", true))) ?>
                         </th>
                      <?php endforeach; ?>
                   </tr>
               </thead>
               <tbody>
                   <?php 
                   $srNo = 'A';
                   foreach ($categoryStats as $category => $stats): ?>
                   <tr>
                       <td style="text-align:center; font-weight:bold;"><?= $srNo++ ?></td>
                       <td style="text-align:left; padding-left:8px;">
                           <?= htmlspecialchars($category) ?>
                       </td>
                       <?php foreach($categoryKeys as $key => $label): ?>
                           <td style="text-align:center; font-weight:bold;">
                               <?= $stats[$key]['percentage'] ?? 0 ?>%
                           </td>
                       <?php endforeach; ?>
                   </tr>
                   <?php endforeach; ?>
               </tbody>
           </table>
       </div>
   <?php endif; ?>
</div>