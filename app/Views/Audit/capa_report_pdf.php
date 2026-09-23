
<?php
function renderAttachment($file, $label = '') {
    if (empty($file)) return '';

    // If it's a full URL, just show the link
    if (filter_var($file, FILTER_VALIDATE_URL)) {
        return "<div><strong>$label</strong><br><a href='$file' target='_blank'>View File</a></div>";
    }

    $filePath = FCPATH . $file;
    $url = base_url($file);

    // If file doesn't exist on server, show link to base_url
    if (!file_exists($filePath)) {
        return "<div><strong>$label</strong><br><a href='$url' target='_blank'>View File</a></div>";
    }

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // If it's an image, embed it as base64 for Dompdf to render without remote issues
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        try {
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
            return "<div>
                <strong>$label</strong><br>
                <img src='data:$mime;base64,$imageData' width='80' style='max-height:100px; object-fit:contain;'>
            </div>";
        } catch (\Exception $e) {
            return "<div><strong>$label</strong><br><a href='$url' target='_blank'>View Image</a></div>";
        }
    }

    return "<div>
        <strong>$label</strong><br>
        <a href='$url' target='_blank'>View File</a>
    </div>";
}
?>

<style>
   table, th, td {
      border: 1px solid black;
   }
   th {
      height: 50px;
   }
   td {
      height: 50px;
   }
   table {
      width: 100%;
      border-collapse: collapse; 
   }
   th {
     background-color: #1e1c77;
   color: white;
   border: 1px solid yellow !important;
   }
   
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
   .margin
{
     margin: 35px;
}
   
  
   /* changes on 8/11/25 by darsh: Add page break before Audit Question section */
   .page-break-before {
      page-break-before: always;
      break-before: page;
   }
</style>
<div>
   <!-- changes on 8/11/25 by darsh: Removed header "HSE Structure Audit CAPA" -->
   <div style="width:49%; float:left">
      <h3><u>Auditor Details</u></h3>
      <table border="0">
         <tbody>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Audit No.</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['audit_no']?>
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
            <!-- changes on 8/11/25 by darsh: Removed Location field from reaudit PDF -->
           
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
                  <?=$details['client_name']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Auditee Name</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['auditee_name']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Region</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['region']?>
               </td>
            </tr>
            
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Score</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
              <!-- changes on 8/11/25 by darsh: Display score with proper decimal formatting -->
              <?= is_numeric($details['score']) ? number_format((float)$details['score'], 2, '.', '') : $details['score'] ?>             
              </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Perform Audit By</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
              <?=$details['perform_audit_by']?>             
              </td>
            </tr>
         </tbody>
      </table>
      <!-- <footer>Someone famous in <cite title="Source Title">Source Title</cite></footer> -->
   </div>
   <div style="width:2%" >
      .
   </div>
   <!-- start row -->
  
  <!-- changes on 8/11/25 by darsh: Audit Question title starts from new page -->
  <div style="text-align: center; margin-bottom: 20px; page-break-before: always; break-before: page;" class="page-break-before">
   <h3><u>Audit Question</u></h3>
   </div>
   <table style="width:100%">
   <thead>
                                                <tr>
                                                    
                                                    <th>Sr No.</th>
                                                    <th>Category name</th>
                                                    <th>Audit Question</th>
                                                    <th>Remark</th>
                                                    <th>Attachment</th>

                                                    <?php $cat = $activeCategory; ?>

                                                      <th><?= ucfirst(str_replace('_',' ', $cat)) ?></th>
                                                      <th>Findings</th>
                                                      <th>Risk</th>
                                                      <th>Actions</th>
                                                      <th>Action Category</th>
                                                      <th>UA-UC</th>
                                                      <th>Risk Severity</th>
                                                      <th>Risk Probability</th>
                                                      <th>Color Code</th>
                                                      <th>Cost Type</th>
                                                      <th>Combined Risk Rating</th>

                                                </tr>
                                                
                                               
                                                </thead>
                  <tbody>

                        <?php foreach ($results as $key=>$result): 

                           $categoryId = $result['audit_category_id'] ?? ($key+1);
                           $isCategoryHeader = (strlen(trim($categoryId)) === 1 && ctype_alpha($categoryId));

                           if ($isCategoryHeader): ?>

                              <tr style="background-color: #e0e0e0; font-weight: bold;">
                                    <?php $totalCols = 5 + 11; ?>
                                    <td colspan="<?= $totalCols ?>" style="text-align:center;">
                                       <?= htmlspecialchars($categoryId) ?>. <?= htmlspecialchars($result['full_data']['audit_category'] ?? '') ?>
                                    </td>
                              </tr>

                           <?php else: ?>

                              <tr>
                                    <td><?= htmlspecialchars($categoryId) ?></td>
                                    <td><?= $result['full_data']['audit_category'] ?? '' ?></td>
                                    <td><?= $result['full_data']['audit_question'] ?></td>

                                    <td>
                                       <?php if(!empty($result['full_data']['remark'])): ?>
                                          <strong>Remark:</strong> <?= htmlspecialchars($result['full_data']['remark']) ?><br>
                                       <?php endif; ?>

                                       <?php if(!empty($result['full_data']['nc_remark'])): ?>
                                          <strong>NC Remark:</strong> <?= htmlspecialchars($result['full_data']['nc_remark']) ?>
                                       <?php endif; ?>
                                    </td>

                                    <!-- ✅ FIXED ATTACHMENT -->
                                    <td>
                                       <?= renderAttachment($result['full_data']['nc_after_photo'],'NC After Photo') ?>
                                       <?= renderAttachment($result['full_data']['attachment'],'Attachment') ?>
                                    </td>

                                    <!-- ✅ CATEGORY VALUE -->
                                    <td style="color:<?= ($result[$cat.'_value']=='NO') ? 'red' : 'green' ?>">
                                       <?= $result[$cat.'_value'] ?? '' ?>
                                    </td>
                                            
                                    <!-- ✅ ALWAYS FIX COLUMN COUNT -->
                                   <?php 
                                       $values = $result['capa_values'] ?? [];
                                       $finding = $result[$cat.'_value'] ?? '';

                                       $keys = [
                                          'Findings',
                                          'Risk',
                                          'Actions',
                                          'Action Category',
                                          'UA-UC',
                                          'Risk Severity',
                                          'Risk Probability',
                                          'Color Code',
                                          'Cost Type',
                                          'Combined Risk Rating'
                                       ];

                                       foreach ($keys as $k): ?>

                                          <td>
                                             <?= ($finding == 'NO') ? ($values[$k] ?? '') : '' ?>
                                          </td>

                                    <?php endforeach; ?>
                              </tr>

                           <?php endif; ?>

                        <?php endforeach; ?>

                  </tbody>
   </table>
   <!-- end row  -->
   
</div>
<script>

</script>
