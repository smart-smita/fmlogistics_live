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
   $sixs_status= ($details ['status'] == 0) ? $pending_message : ($details ['status'] == 1 ? $open_message: ($details ['status'] == 2 ? $active_message : ($details ['status'] == 3 ? $close_message :$cancle_message)));

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
   table{
   width:100%;
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
       <u>Basic Details of <span style="color: orange; border-bottom: 3px double; padding-bottom: 2px;"><?=$details['user_name']?></span></u>
    </h2>
</div>

<div style="width:49%; float:left; box-sizing:border-box;">
   <h3><u>Reporting Location</u></h3>
   <table border="0" style="width:100%; table-layout: fixed; word-break: break-word;">
      <tbody>
                                                         <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Location</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; "><?=$details['location']?></td>
                                                         </tr>
                                                          <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Nc Description</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                            <?=$details['nc_description']?>
                                                            </td>
                                                        </tr>
                                                         <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Nc Photo</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                           <?=$details['nc_photo']?>
                                                            </td>
                                                        </tr>
                                                          <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Action Required Dep</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                             <?=$details['action_required_dep']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Div Dep</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                             <?=$details['devision_dept']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Responsibilty</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                            <?=$details['responsibilty']?>
                                                            </td>
                                                        </tr>
                                                          <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Target Date</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                            <?=$details['target_date']?>
                                                            </td>
                                                        </tr>
                                                         
                                                         <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Department Status</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                            <?=$details['department_status']?>
                                                            </td>
                                                        </tr>
                                                        
                                                        <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Status</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                            <?=$sixs_status?>
                                                            </td>
                                                        </tr>

         </tbody>
      </table>
      <!-- <footer>Someone famous in <cite title="Source Title">Source Title</cite></footer> -->
   </div>
   <div style="width:49%; float:right; box-sizing:border-box;">
   <h3><u>Reporter Details</u></h3>
   <table style="width:100%; table-layout: fixed; word-break: break-word;">
      <tbody>
                                                 <tr>
                                                    <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Name</th>
                                                    <td style="text-align:left; padding-left:5px; padding-right:5px; "><?=$details['user_name']?></td>
                                                 </tr>
                                                        <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Eamil</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                            <?=$details['user_email']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Contact</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                            <?=$details['user_contact']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Sixs Category</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                            <?=$details['sixs_category']?>
                                                            </td>
                                                        </tr>
                                                         <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Details Of Ca Pa</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                            <?=$details['details_of_ca_pa']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Final Status Soi</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                            <?=$details['final_status_soi']?>
                                                            </td>
                                                        </tr>
                                                          <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">After Photo</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                            <?=$details['after_photo']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Designation</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
                                                            <?=$details['user_designation']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary" style="text-align:left; padding-left:5px; width:40%;">Status</th>
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
   
   
   
  
   <!-- end row  -->
   
   <br>
   <div style="text-align: center; margin-bottom: 20px;">
   <h3><u>6s Audit Comments</u></h3>
   </div>
   <?php if(!empty($sixs_comments)) { ?>
   <table>
      <tr>
         <th>User</th>
         <th>Comment</th>
      </tr>
      <tbody>
         <?php foreach($sixs_comments as $comment_row){ ?> 
         <tr>
            <td style="text-align:left; padding-left:5px; padding-right:5px; "><?=$comment_row['user_name']?></td>
            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
               <?php if($comment_row['comment_type']==0) { ?>
               <?=$comment_row['comment_text']?>
               <?php } ?>
               <?php if($comment_row['comment_type']==1) { ?>
                <?php
                    $options = [
                                'http' => [
                                    'ignore_errors' => true,
                                ],
                                'https' => [
                                    'ignore_errors' => true,
                                ],
                            ];
                                        $context = stream_context_create($options);
                                                            $imageUrl = $comment_row['chat_image'];
                                                            // Initialize variables for Base64 and link
                                                            $imageBase64 = '';
                                                            $linkHtml = '';
                                                    
                                                            // Use getimagesize to check the image MIME type
                                                            $imageInfo = @getimagesize($imageUrl);
                                                            if ($imageInfo) {
                                                                // Get MIME type
                                                                $mimeType = $imageInfo['mime'];
                                                    
                                                                // Fetch image data
                                                                $imageData = file_get_contents($imageUrl);
                                                                if ($imageData) {
                                                                    // Convert image data to Base64
                                                                    $base64 = base64_encode($imageData);
                                                                    $imageBase64 = 'data:' . $mimeType . ';base64,' . $base64; // Dynamic MIME type
                                                                }
                                                            } else {
                                                                // Create a link if the image cannot be fetched
                                                                $linkHtml = '<p>You can download it <a href="' . $imageUrl . '" target="_blank">here</a>.</p>';
                                                            }

                                                            // Convert image to Base64
                                                            $imageData = file_get_contents($imageUrl,false,$context);
                                                            $base64 = base64_encode($imageData);
                                                            $imageBase64 = 'data:image/png;base64,' . $base64; // Change the MIME type as needed
                                                                if ($imageBase64) {
                                                                        echo '<a href="' . $imageUrl . '" target="_blank"><img src="' . $imageBase64 . '" width="100" height="100" alt="Audit Image" onclick=""></a>';
                                                                    } else {
                                                                        echo $linkHtml; // Show the link if image is not available
                                                                    }
//                                                        echo '5.<img src="'.$chat_row['chat_image'].'" height="100"  hspace="20" width="100">';
                                                  
            
                ?>
               <?php } ?>
               
            </td>
         </tr>
         <?php } ?>
      </tbody>
   </table>
   <?php } ?>        
</div>
<script></script>