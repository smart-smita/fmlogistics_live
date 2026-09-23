  <?php
   $details = $details ?? [];
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
   }
   table {
      width: 100%;
      border-collapse: collapse; 
      table-layout: fixed;
   }
   
   /* changes on 7/10/25 by darsh: Fixed column widths to prevent table breaking */
   .col-sr { width: 5%; }
   .col-category { width: 12%; }
   .col-question { width: 25%; }
   .col-response { width: 7%; }
   .col-remark { width: 18%; }
   .col-attachment { width: 12%; }
   
   body {
       border-style: groove;
      padding: 10px;
    font-family:sans-serif; 
   }
   html {
      margin-left: 5%;
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
   
   /* changes on 7/10/25 by darsh: Add page break handling for long tables */
   .page-break {
      page-break-before: always;
   }
   
   .attachment-image {
      max-width: 80px;
      max-height: 80px;
      object-fit: contain;
   }
   
   /* changes on 7/10/25 by darsh: Ensure proper text wrapping */
   .text-wrap {
      word-wrap: break-word;
      overflow-wrap: break-word;
   }
</style>
<style>
   body {
      border-style: groove;
      padding: 10px;
      font-family: sans-serif;
      font-size: 11px;
   }

   html {
      margin-left: 2%;
      margin-right: 2%;
   }

   table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
   }

   table, th, td {
      border: 1px solid black;
   }

   th {
      background-color: #1e1c77;
      color: white;
      border: 1px solid yellow !important;
      text-align: center;
      font-weight: bold;
      padding: 6px 4px;
      font-size: 10px;
      line-height: 1.2;
      word-wrap: break-word;
      overflow-wrap: break-word;
      vertical-align: middle;
   }

   td {
      padding: 6px 5px;
      vertical-align: top;
      font-size: 11px;
      word-wrap: break-word;
      overflow-wrap: break-word;
   }

   .col-sr { width: 4%; }
   .col-category { width: 10%; }
   .col-question { width: 22%; }
   .col-response { width: 5%; }
   .col-remark { width: 12%; }
   .col-attachment { width: 9%; }

   .attachment-image {
      max-width: 70px;
      max-height: 70px;
      object-fit: contain;
   }

   .page-break {
      page-break-before: always;
   }

   .text-wrap {
      word-wrap: break-word;
      overflow-wrap: break-word;
   }

   .header-small {
      font-size: 9px;
      line-height: 1.1;
   }

   .header-rotate {
      writing-mode: vertical-rl;
      transform: rotate(180deg);
      white-space: nowrap;
      text-align: center;
      vertical-align: middle;
      height: 120px;
   }
</style>
<h4><?=(isset($page_title))?$page_title:""?></h4>
<div>
   	
   	<div align="center">
		<h2 style="display: inline-block; border-bottom: 3px double black;"><u>HSE Structure Audit</u></h2>
	</div>
   <div style="width:49%; float:left">
      <h3><u>Auditor Details</u></h3>
      <table border="0">
         <tbody>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Audit No.</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?= htmlspecialchars($details['audit_no'] ?? '') ?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;" >Audit Name</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?= htmlspecialchars($details['audit_name']?? '') ?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Auditor Name</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?= htmlspecialchars($details['auditor_name']?? '') ?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Audit Date</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?= htmlspecialchars($details['audit_date']?? '') ?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Location ID</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?= htmlspecialchars($details['location']?? '') ?>
               </td>
            </tr>
           
         </tbody>
      </table>
      <!-- <footer>Someone famous in <cite title="Source Title">Source Title</cite></footer> -->
   </div>
   <div style="width:49%;float:right">
      <h3><u>Client Details</u></h3>
      <table >
         <tbody>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Client Name</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; " >
                  <?= htmlspecialchars($details['client_name']?? '') ?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Auditee Name</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?= htmlspecialchars($details['auditee_name']?? '') ?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Region</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?= htmlspecialchars($details['region']?? '') ?>
               </td>
            </tr>
            
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Score</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
              <?= is_numeric($details['score']) ? number_format((float)$details['score'], 2, '.', '') : $details['score'] ?>             
              </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Perform Audit By</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
              <?=$_SESSION['user_name']?>             
              </td>
            </tr>
         </tbody>
      </table>
      <!-- <footer>Someone famous in <cite title="Source Title">Source Title</cite></footer> -->
   </div>
   <div style="width:2%" >
      .
   </div>
   
   
   <div style="clear:both; height:20px;"></div>

<!-- ================= AUDITEE ATTENDANCE ================= -->
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
            <?php if (!empty($attendance) && is_array($attendance)): ?>
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
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; font-weight:bold;">No Auditee Attendance Records Available</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="page-break"></div>

   <!-- start row -->
  
   
   
   
   <?php 
$audit_details = is_array($audit_details ?? null) ? $audit_details : [];
$totalCols = 3 + count($categoryKeys) + 2; // dynamic colspan
?>

<table style="width:100%; table-layout:fixed;">
<thead>
<tr>
    <th class="col-sr">Sr.No.</th>
    <th class="col-category">Audit Category</th>
    <th class="col-question">Audit Question</th>

    <?php foreach($categoryKeys as $key => $label): ?>
         <th class="col-response header-small">
            <?= nl2br(htmlspecialchars(wordwrap($label, 12, "\n", true))) ?>
         </th>
      <?php endforeach; ?>

    <th class="col-remark">Remark</th>
    <th class="col-attachment">Attachment</th>
</tr>
</thead>

<tbody>

<?php 
$currentLetter = '';
$subIndex = 1;

foreach ($audit_details as $detail): 

$categoryId = trim($detail['audit_category_id'] ?? '');

// detect header (A, B, C)
$isHeader = (strlen($categoryId) === 1 && ctype_alpha($categoryId));

if ($isHeader) {

    $currentLetter = $categoryId;
    $subIndex = 1;
?>
<tr style="background:#e0e0e0; font-weight:bold;">
    <td colspan="<?= $totalCols ?>" style="text-align:left;">
        <b><?= htmlspecialchars($categoryId) ?>. <?= htmlspecialchars($detail['question_name']) ?></b>
    </td>
</tr>

<?php } else { ?>

<tr>
    <!-- ✅ FIXED NUMBERING -->
    <td><?= htmlspecialchars($categoryId) ?></td>

    <!-- ✅ CATEGORY NAME FIX -->
    <td><?= htmlspecialchars($detail['question_name'] ?? '') ?></td>

    <td><?= htmlspecialchars($detail['audit_question'] ?? '') ?></td>

    <!-- CATEGORY VALUES -->
    <?php foreach($categoryKeys as $key => $label): ?>
        <td style="text-align:center">
            <?php
            $value = $detail['categories'][$key] ?? '';
            $color = $value == 'YES' ? 'green' : ($value == 'NO' ? 'red' : 'gray');
            ?>
            <span style="color:<?= $color ?>; font-weight:bold;">
                <?= $value ?>
            </span>
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

    <!-- ATTACHMENT -->
    <td style="text-align:center">
        <?php
        $hasAttachment = false;

        foreach (['nc_after_photo','attachment'] as $imgField) {
            if (!empty($detail[$imgField])) {

                $file = $detail[$imgField];
                $filePath = FCPATH . $file;
                $url = base_url($file);

                if (file_exists($filePath)) {
                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $imageData = base64_encode(file_get_contents($filePath));

                        // Safe MIME type detection by extension (PHP 8.4 compatible)
                        $mimeTypes = [
                            'jpg' => 'image/jpeg',
                            'jpeg' => 'image/jpeg',
                            'png' => 'image/png',
                            'gif' => 'image/gif',
                            'webp' => 'image/webp'
                        ];
                        $mime = $mimeTypes[$ext] ?? 'image/jpeg';

                        echo '<img src="data:'.$mime.';base64,'.$imageData.'"
                              style="max-width:80px; max-height:80px;"><br>';
                    } else {
                        echo '<a href="'.$url.'" target="_blank" style="font-size:10px;">View File</a><br>';
                    }
                    $hasAttachment = true;
                } else {
                    echo '<a href="'.$url.'" target="_blank" style="font-size:10px;">View File</a><br>';
                    $hasAttachment = true;
                }
            }
        }

        if (!$hasAttachment) {
            echo '<span style="color:#999;">No attachment</span>';
        }
        ?>
    </td>
</tr>

<?php } endforeach; ?>

<?php if(empty($audit_details)): ?>
<tr>
    <td colspan="<?= $totalCols ?>" style="text-align:center;">No data found</td>
</tr>
<?php endif; ?>

</tbody>
</table>

<?php if (isset($overallStats) && isset($categoryStats)): ?>
    <div style="page-break-before: always;"></div>
    
    <div align="center">
		<h2 style="display: inline-block; border-bottom: 3px double black;"><u>HSE Audit AutoGrid Report</u></h2>
	</div>
    
    <?php $mode = strtolower($details['perform_audit_by'] ?? ''); ?>
    
    <div style="margin-top: 30px;">
         <h3><u>Overall Summary for Compliance</u></h3>
         <table>
<thead>
<tr>
    <th>Category</th>
    <?php foreach($categoryKeys as $key => $label): ?>
        <th><?= $label ?></th>
    <?php endforeach; ?>
</tr>
</thead>

<tbody>

<tr>
    <th>Total Selected</th>
    <?php foreach($overallStats as $stat): ?>
        <td><?= $stat['yes'] + $stat['no'] + $stat['na'] ?></td>
    <?php endforeach; ?>
</tr>

<tr>
    <th>YES</th>
    <?php foreach($overallStats as $stat): ?>
        <td><?= $stat['yes'] ?></td>
    <?php endforeach; ?>
</tr>

<tr>
    <th>NO</th>
    <?php foreach($overallStats as $stat): ?>
        <td><?= $stat['no'] ?></td>
    <?php endforeach; ?>
</tr>

<tr>
    <th>NA</th>
    <?php foreach($overallStats as $stat): ?>
        <td><?= $stat['na'] ?></td>
    <?php endforeach; ?>
</tr>

<tr>
    <th>Score</th>
    <?php foreach($overallStats as $stat): ?>
        <td><?= $stat['percentage'] ?>%</td>
    <?php endforeach; ?>
</tr>

</tbody>
</table>
       </div>
       
         <div style="margin-top: 30px;">
    <h3><u>Categorywise/Headerwise Score of Audit Grid</u></h3>

    <table>
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Category</th>

                <?php foreach($categoryKeys as $key => $label): ?>
                  <th class="col-response header-small">
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
                <td style="text-align:center"><?= $srNo++ ?></td>

                <td style="text-align:left; padding-left:10px;">
                    <?= htmlspecialchars($category) ?>
                </td>

                <!-- ✅ DYNAMIC CATEGORY LOOP -->
                <?php foreach($categoryKeys as $key => $label): ?>
                    <td style="text-align:center">
                        <?= $stats[$key]['percentage'] ?? 0 ?>%
                    </td>
                <?php endforeach; ?>

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>


   <!-- end row  -->
   
</div>
<script></script>