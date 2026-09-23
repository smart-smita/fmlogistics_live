<?php
   $details = isset($details) ? $details : [];
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
   /* changes on 15/10/25 by darsh: prevent page break between section title and its table */
   .keep-together { page-break-inside: avoid; }

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
</style>

<h4><?=(isset($page_title))?$page_title:""?></h4>
<div>
   	<div align="center">
		<h2 style="display: inline-block; border-bottom: 3px double black;"><u>HSE Audit AutoGrid Report</u></h2>
	</div>
   
   <!-- changes on 6/10/25 by darsh: Add side-by-side Auditor Details and Client Details with required fields -->
   <div style="margin-top: 10px; width:49%; float:left;">
      <h3><u>Auditor Details</u></h3>
      <table>
         <tbody>
            <tr>
               <th style="text-align:left; padding-left:5px; width:45%;">Audit No.</th>
               <td style="text-align:left; padding-left:5px;"><?= isset($details['audit_no']) ? $details['audit_no'] : '' ?></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px;">Audit Type Name</th>
               <td style="text-align:left; padding-left:5px;"><?= isset($details['audit_name']) ? $details['audit_name'] : '' ?></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px;">Auditor Name</th>
               <td style="text-align:left; padding-left:5px;"><?= isset($details['auditor_name']) ? $details['auditor_name'] : '' ?></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px;">Auditee Name</th>
               <td style="text-align:left; padding-left:5px;"><?= isset($details['auditee_name']) ? $details['auditee_name'] : '' ?></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px;">Audit Date</th>
               <td style="text-align:left; padding-left:5px;"><?= isset($details['audit_date']) ? $details['audit_date'] : '' ?></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px;">Next Date</th>
               <td style="text-align:left; padding-left:5px;"><?= isset($details['next_date']) ? $details['next_date'] : ($details['template_date'] ?? '') ?></td>
            </tr>
         </tbody>
      </table>
   </div>
   <div style="margin-top: 10px; width:49%; float:right;">
      <h3><u>Client Details</u></h3>
      <table>
         <tbody>
            <tr>
               <th style="text-align:left; padding-left:5px; width:45%;">Site Location</th>
               <td style="text-align:left; padding-left:5px;"><?= isset($details['location']) ? $details['location'] : '' ?></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px;">Client Name</th>
               <td style="text-align:left; padding-left:5px;"><?= isset($details['client_name']) ? $details['client_name'] : '' ?></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px;">Region</th>
               <td style="text-align:left; padding-left:5px;"><?= isset($details['region']) ? $details['region'] : '' ?></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px;">Report Date</th>
               <td style="text-align:left; padding-left:5px;"><?= isset($details['report_date']) ? $details['report_date'] : ($details['audit_date'] ?? '') ?></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px;">Score</th>
               <td style="text-align:left; padding-left:5px;"><?= isset($details['score']) && is_numeric($details['score']) ? number_format((float)$details['score'], 2, '.', '') : (isset($details['score']) ? $details['score'] : '') ?></td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px;">Site Category</th>
               <td style="text-align:left; padding-left:5px;"><?= isset($details['perform_audit_by']) ? $details['perform_audit_by'] : '' ?></td>
            </tr>
         </tbody>
      </table>
   </div>

   <div style="clear:both; height: 10px;"></div>

  <!-- Overall Summary Section (kept on one page with title) -->
  <?php $mode = strtolower($details['perform_audit_by'] ?? ''); ?>
  <div class="keep-together" style="margin-top: 30px;">
     <h3><u>Overall Summary for Compliance</u></h3>
     <table>
        <thead>
           <tr>
              <th>Category</th>
              <?php if ($mode === 'client_leased' || $mode === ''): ?><th>Client Leased</th><?php endif; ?>
              <?php if ($mode === 'fm_leased'): ?><th>FM Leased</th><?php endif; ?>
              <?php if ($mode === 'office'): ?><th>Office</th><?php endif; ?>
           </tr>
        </thead>
        <tbody>
           <tr>
              <th style="text-align:left; padding-left:5px; background-color: #1e1c77; color: white;">Total Selected</th>
              <?php if ($mode === 'client_leased' || $mode === ''): ?><td style="text-align:center"><?= $overallStats['client_leased']['yes'] + $overallStats['client_leased']['no'] + $overallStats['client_leased']['na'] ?></td><?php endif; ?>
              <?php if ($mode === 'fm_leased'): ?><td style="text-align:center"><?= $overallStats['fm_leased']['yes'] + $overallStats['fm_leased']['no'] + $overallStats['fm_leased']['na'] ?></td><?php endif; ?>
              <?php if ($mode === 'office'): ?><td style="text-align:center"><?= $overallStats['office']['yes'] + $overallStats['office']['no'] + $overallStats['office']['na'] ?></td><?php endif; ?>
           </tr>
           <tr>
              <th style="text-align:left; padding-left:5px; background-color: #1e1c77; color: white;">Complied (Yes)</th>
              <?php if ($mode === 'client_leased' || $mode === ''): ?><td style="text-align:center"><?= $overallStats['client_leased']['yes'] ?></td><?php endif; ?>
              <?php if ($mode === 'fm_leased'): ?><td style="text-align:center"><?= $overallStats['fm_leased']['yes'] ?></td><?php endif; ?>
              <?php if ($mode === 'office'): ?><td style="text-align:center"><?= $overallStats['office']['yes'] ?></td><?php endif; ?>
           </tr>
           <tr>
              <th style="text-align:left; padding-left:5px; background-color: #1e1c77; color: white;">Not Complied (No)</th>
              <?php if ($mode === 'client_leased' || $mode === ''): ?><td style="text-align:center"><?= $overallStats['client_leased']['no'] ?></td><?php endif; ?>
              <?php if ($mode === 'fm_leased'): ?><td style="text-align:center"><?= $overallStats['fm_leased']['no'] ?></td><?php endif; ?>
              <?php if ($mode === 'office'): ?><td style="text-align:center"><?= $overallStats['office']['no'] ?></td><?php endif; ?>
           </tr>
           <tr>
              <th style="text-align:left; padding-left:5px; background-color: #1e1c77; color: white;">Not Applicable (NA)</th>
              <?php if ($mode === 'client_leased' || $mode === ''): ?><td style="text-align:center"><?= $overallStats['client_leased']['na'] ?></td><?php endif; ?>
              <?php if ($mode === 'fm_leased'): ?><td style="text-align:center"><?= $overallStats['fm_leased']['na'] ?></td><?php endif; ?>
              <?php if ($mode === 'office'): ?><td style="text-align:center"><?= $overallStats['office']['na'] ?></td><?php endif; ?>
           </tr>
           <tr>
              <th style="text-align:left; padding-left:5px; background-color: #1e1c77; color: white;">Total Points</th>
              <?php if ($mode === 'client_leased' || $mode === ''): ?><td style="text-align:center"><?= $overallStats['client_leased']['total_points'] ?? 0 ?></td><?php endif; ?>
              <?php if ($mode === 'fm_leased'): ?><td style="text-align:center"><?= $overallStats['fm_leased']['total_points'] ?? 0 ?></td><?php endif; ?>
              <?php if ($mode === 'office'): ?><td style="text-align:center"><?= $overallStats['office']['total_points'] ?? 0 ?></td><?php endif; ?>
           </tr>
           <tr>
              <th style="text-align:left; padding-left:5px; background-color: #1e1c77; color: white;">Overall Score</th>
              <?php if ($mode === 'client_leased' || $mode === ''): ?><td style="text-align:center"><?= $overallStats['client_leased']['percentage'] ?>%</td><?php endif; ?>
              <?php if ($mode === 'fm_leased'): ?><td style="text-align:center"><?= $overallStats['fm_leased']['percentage'] ?>%</td><?php endif; ?>
              <?php if ($mode === 'office'): ?><td style="text-align:center"><?= $overallStats['office']['percentage'] ?>%</td><?php endif; ?>
           </tr>
        </tbody>
     </table>
  </div>

   <!-- Category-wise Section -->
  <div class="keep-together" style="margin-top: 30px;">
      <h3><u>Categorywise/Headerwise Score of Audit Grid</u></h3>
      <table>
         <thead>
            <tr>
               <th>Sr. No.</th>
               <th>Category</th>
               <?php if ($mode === 'client_leased' || $mode === ''): ?><th>Client Leased</th><?php endif; ?>
               <?php if ($mode === 'fm_leased'): ?><th>FM Leased</th><?php endif; ?>
               <?php if ($mode === 'office'): ?><th>Office</th><?php endif; ?>
            </tr>
         </thead>
         <tbody>
            <?php 
            $srNo = 1;
            foreach ($categoryStats as $category => $stats): ?>
            <tr>
               <td style="text-align:center"><?= $srNo++ ?></td>
               <td style="text-align:left; padding-left:10px;"><?= $category ?></td>
               <?php if ($mode === 'client_leased' || $mode === ''): ?><td style="text-align:center"><?= $stats['client_leased']['percentage'] ?>%</td><?php endif; ?>

               <?php if ($mode === 'fm_leased'): ?><td style="text-align:center"><?= $stats['fm_leased']['percentage'] ?>%</td><?php endif; ?>
               <?php if ($mode === 'office'): ?><td style="text-align:center"><?= $stats['office']['percentage'] ?>%</td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   </div>

  <!-- changes on 15/10/25 by darsh: removed duplicate Audit Information block (already on first page) -->
</div>

<script></script>
