<?php
   $details=$details[0];
   $pending_message = '<span class="badge badge-warning">Pending</span>';
   $verified_message = '<span class="badge badge-info">Verified</span>';
   $active_message = '<span class="badge badge-success">Active</span>';
   
   $Requested_message = '<span class="badge badge-warning">Request required</span>';
    $open_message = '<span class="badge badge-warning">Open</span>';
    $close_message = '<span class="badge badge-success">Close</span>';
    $cancle_message = '<span class="badge badge-secondary">Cancle</span>';
    
   $status= ($details ['status'] == 0) ? $pending_message : ($details ['status'] == 1 ? $verified_message: ($details ['status'] == 2 ? $active_message : ($details ['status'] == 3 ? $deactivated_message : "")));
   $lmra_status= ($details ['status'] == 0) ? $pending_message : ($details ['status'] == 1 ? $open_message: ($details ['status'] == 2 ? $active_message : ($details ['status'] == 3 ? $close_message :$cancle_message)));
   
   ?>
<style>
   table, th, td {
   border: 1px solid black;
   }
   th {
   height: 30px;
   }
   td {
   height: 50px;
   
   }
   table{
   width:100%;
   border-collapse: collapse;
   }
   th {
   background-color: #1e1c77;
   color: white;
   border: 1px solid yellow !important;
   
   }
   
  </style> 
  
  <style>
   body {
  border-style: groove;
  padding: 10px;
font-family:sans-serif;  
}
html{
    margin-left:5%;
} 
.badge{
	display:inline-block;
	min-width:10px;
	padding:10px;
	font-size:16px;
	font-weight:700;
	color:#fff;
	line-height:1;
	vertical-align:baseline;
	white-space:nowrap;
	text-align:center;
	background-color:#999;
	border-radius:10px
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
.badge-secondary{
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
<h2 style="display: inline-block; border-bottom: 3px double black;">
   Basic Details Of <span style="color: orange; border-bottom: 3px double; padding-bottom: 2px;"><?=$details['user_name']?></span>
</h2>
</div>
   <div style="width:49%; float:left">
      <h3><u>Reporting Location</u></h3>
      <table border="0">
         <tbody>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Project Name</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['lmra_project_name']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;" >Job No.</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['lmra_job_no']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Location</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['lmra_location']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Visit Date</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['lmra_visit_date']." ". $details['lmra_visit_time']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Status</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$lmra_status?>
               </td>
            </tr>
         </tbody>
      </table>
      <!-- <footer>Someone famous in <cite title="Source Title">Source Title</cite></footer> -->
   </div>
   <div style="width:49%;float:right">
      <h3><u>Repoter Details</u></h3>
      <table >
         <tbody>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;"> User Name</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['user_name']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Email Id</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['user_name']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Contact No.</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['user_contact']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Designation</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$details['user_designation']?>
               </td>
            </tr>
            <tr>
               <th style="text-align:left; padding-left:5px; width:40%;">Status</th>
               <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                  <?=$status?>
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
   
    <?php if(isset($details['rejection_comment']) && !empty($details['rejection_comment'])){?>
      <table>
      <tr>
         <th># Rejection comment</th>
      </tr>
      <tbody>

         <tr>
            <td>
               <?php echo  isset($details['rejection_comment'])?$details['rejection_comment']:'' ;?>
            </td>
         </tr>
      </tbody>
        </table><br>
     <?php } ?>
   
   
   
   <?php 
      $lmra_json_data =json_decode($details['lmra_json_data'],true);
      ?>
      <div style="text-align: center; margin-bottom: 20px;">
   <h3><u>LMRA Check List</u></h3>
   </div>
   <table>
      <?php foreach($lmra_json_data as $header_row){ ?>
      <tr>
         <th style="width:5%">#</th>
         <th style="width:56%"><?=$header_row['title_1_header']?></th>
         <th style="width:8%"><?=$header_row['title_2_header']?></th>
         <th style="width:8%"><?=$header_row['title_3_header']?></th>
         <th style="width:8%"><?=$header_row['title_4_header']?></th>
         <th style="width:15%"><?=$header_row['title_5_header']?></th>
      </tr>
      <tbody>
         <?php 
            $index = 1;
            foreach($header_row['lmrsFooterForms'] as $footer_row){
            ?>
         <tr>
            <td style="text-align:center"><?=$index?></td>
            <td style="text-align:left; padding-left:5px; padding-right:5px; "><?=$footer_row['title_1_footer']?></td>
            <td style="text-align:center"><input type="checkbox" <?=($footer_row['cb_1_footer'])?"checked":""?> disabled></td>
            <td style="text-align:center"><input type="checkbox" <?=($footer_row['cb_2_footer'])?"checked":""?> disabled></td>
            <td style="text-align:center"><input type="checkbox" <?=($footer_row['cb_3_footer'])?"checked":""?> disabled></td>
            <td style="text-align:center">
               <?=($footer_row['selected_string_3']=='1')?"medium":(($footer_row['selected_string_3']=='2')?"high":"low");?>
            </td>
         </tr>
         <?php $index++;} ?>
      </tbody>
      <?php } ?>
   </table>
   <!-- end row  -->
   
   <br>
   <div style="text-align: center; margin-bottom: 20px;" >
   <h3><u>LMRA Comments</u></h3>
   </div>
   <?php if(!empty($lmra_comments)) { ?>
  <table>
     <tr>
        <th>User</th>
        <th>Comment</th>
        <th>Date</th>
     </tr>
      <tbody>
         <?php foreach($lmra_comments as $comment_row){ ?> 
         <tr>
            <td style="text-align:left; padding-left:5px; padding-right:5px; "><?=$comment_row['user_name']?><?=(isset($comment_row['user_designation']) && $comment_row['user_designation']!=='')? ' ('.$comment_row['user_designation'].')':''?></td>
            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
               <?php if(isset($comment_row['comment_type']) && $comment_row['comment_type']==1) { ?>
                <?php
                    $p=parse_url($comment_row['comment_text']);
                    $tem= $p['path'];
                    echo '<img src='.'..'.$tem.' height="100"  hspace="20" width="100">';
                ?>
               <?php } else { ?>
               <?=$comment_row['comment_text']?>
               <?php } ?>
               
            </td>
            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
               <?=isset($comment_row['default_date'])?$comment_row['default_date']:''?>
            </td>
         </tr>
         <?php } ?>
      </tbody>
   </table>
   <?php } ?>        
</div>
<script></script>