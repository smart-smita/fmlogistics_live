<?php
if (!function_exists('renderAttachment')) {
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
      padding: 5px 3px;
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
      margin-bottom: 20px;
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
      padding: 6px 8px;
      font-size: 10px;
   }

   .info-table td {
      padding: 6px 8px;
      font-size: 10px;
   }

   .badge {
      display: inline-block;
      padding: 4px 8px;
      font-size: 11px;
      font-weight: bold;
      color: #fff;
      border-radius: 4px;
   }

   .badge-success { background-color: #468847; }
   .badge-danger { background-color: #b94a48; }
   .badge-warning { background-color: #ffc107; }
   .badge-secondary { background-color: #868e96; }

   .page-break-before {
      page-break-before: always;
      break-before: page;
   }
</style>

<div>
   <div align="center">
      <h2 style="display: inline-block; border-bottom: 3px double black; margin-bottom: 15px;"><u>HSE Audit CAPA Report</u></h2>
   </div>

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
                     <td><?= htmlspecialchars($details['perform_audit_by'] ?? '') ?></td>
                  </tr>
               </tbody>
            </table>
         </td>
      </tr>
   </table>

   <div style="text-align: center; margin-bottom: 15px; page-break-before: always; break-before: page;" class="page-break-before">
      <h3><u>Audit Questions & CAPA Details</u></h3>
   </div>

   <table style="width:100%; table-layout: fixed;">
      <thead>
         <tr>
            <th style="width: 4%;">Sr No.</th>
            <th style="width: 9%;">Category</th>
            <th style="width: 17%;">Audit Question</th>
            <th style="width: 8%;">Remark</th>
            <th style="width: 6%;">Attachment</th>

            <?php $cat = $activeCategory ?? ''; ?>
            <th style="width: 4%;"><?= ucfirst(str_replace('_',' ', $cat)) ?></th>
            <th style="width: 8%;">Findings</th>
            <th style="width: 7%;">Risk</th>
            <th style="width: 8%;">Actions</th>
            <th style="width: 7%;">Action Category</th>
            <th style="width: 4%;">UA-UC</th>
            <th style="width: 4%;">Severity</th>
            <th style="width: 5%;">Probability</th>
            <th style="width: 4%;">Color Code</th>
            <th style="width: 4%;">Cost Type</th>
            <th style="width: 5%;">Rating</th>
         </tr>
      </thead>
      <tbody>
         <?php foreach ($results as $key => $result): 
            $categoryId = trim($result['audit_category_id'] ?? ($key + 1));
            $isCategoryHeader = (strlen($categoryId) === 1 && ctype_alpha($categoryId));

            if ($isCategoryHeader): ?>
               <tr style="background-color: #e0e0e0; font-weight: bold;">
                  <td colspan="16" style="text-align:left; font-size: 10px; padding: 6px;">
                     <b><?= htmlspecialchars($categoryId) ?>. <?= htmlspecialchars($result['full_data']['audit_category'] ?? '') ?></b>
                  </td>
               </tr>
            <?php else: ?>
               <tr>
                  <td style="text-align:center; font-weight:bold;"><?= htmlspecialchars($categoryId) ?></td>
                  <td><?= htmlspecialchars($result['full_data']['audit_category'] ?? '') ?></td>
                  <td><?= htmlspecialchars($result['full_data']['audit_question'] ?? '') ?></td>

                  <td>
                     <?php if(!empty($result['full_data']['remark'])): ?>
                        <strong>Remark:</strong> <?= htmlspecialchars($result['full_data']['remark']) ?><br>
                     <?php endif; ?>
                     <?php if(!empty($result['full_data']['nc_remark'])): ?>
                        <strong>NC Remark:</strong> <?= htmlspecialchars($result['full_data']['nc_remark']) ?>
                     <?php endif; ?>
                  </td>

                  <td style="text-align:center;">
                     <?= renderAttachment($result['full_data']['nc_after_photo'] ?? '', 'NC After') ?>
                     <?= renderAttachment($result['full_data']['attachment'] ?? '', 'Attachment') ?>
                  </td>

                  <td style="text-align:center; font-weight:bold; color:<?= (($result[$cat.'_value'] ?? '') == 'NO') ? 'red' : ((($result[$cat.'_value'] ?? '') == 'YES') ? 'green' : 'gray') ?>;">
                     <?= htmlspecialchars($result[$cat.'_value'] ?? '') ?>
                  </td>

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
                        <td style="font-size: 8.5px;">
                           <?= htmlspecialchars(($finding == 'NO') ? ($values[$k] ?? '') : '') ?>
                        </td>
                  <?php endforeach; ?>
               </tr>
            <?php endif; ?>
         <?php endforeach; ?>
      </tbody>
   </table>
</div>
