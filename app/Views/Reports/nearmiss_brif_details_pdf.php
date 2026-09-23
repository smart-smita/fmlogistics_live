<?php
//print_r($details['0']);
$details=$details['0'];
$pending_message = '<span class="badge badge-danger">Pending</span>';
$verified_message = 'Verified';
$active_message = '<span class="badge badge-success">Active</span>';
$pending_message_nm = '<span class="badge badge-danger">Pending</span>';

    $Requested_message = '<span class="badge badge-warning">Request required</span>';
    $open_message = '<span class="badge badge-warning">Open</span>';
    $close_message = '<span class="badge badge-success">Close</span>';
    $cancle_message = '<span class="badge badge-secondary">Cancle</span>';


$status= ($details ['status'] == 0) ? $pending_message : ($details ['status'] == 1 ? $verified_message: ($details ['status'] == 2 ? $active_message : ($details ['status'] == 3 ? $deactivated_message : "")));
$near_miss_status= ($details ['status'] == 0) ? $pending_message_nm : ($details ['status'] == 1 ? $open_message: ($details ['status'] == 2 ? $Requested_message : ($details ['status'] == 3 ? $close_message :$cancle_message)));
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

#table3, #table5, #table4, #table7, #table8 {
  border-collapse: collapse;
  width: 100%;
}


#table3 th {
  height: 40px;
  border: 1px solid black;
 
}
#table3 td {
  height: 40px;
  border: 1px solid black;


}
#table5{
    tr:nth-child(odd) {background-color: white;}
    tr:nth-child(even) {background-color: white;}
}

/*tr:nth-child(odd) {background-color: #f2f2f2;}*/
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


<div class="margin">
								   
								<div align="center">
									<h2 style="display: inline-block; border-bottom: 3px double black;"><u>Brief Details Of <span <span style="color: orange; border-bottom: 3px double; padding-bottom: 2px;"><?=$details['user_name']?></span></u></h2>
								</div>
								
								<div style="float:right"><b>Status:</b><?=$near_miss_status?></div>
								<br>
							
                               
								
                                <div   style="width:45%;float:left">
                                    <h3><u>Reporting Location</u></h3>
                                   
                                     <table id="table1"> 
                                                    <tbody>
                                                        <tr>
                                                            <th style="text-align:left; padding-left:5px; width:40%;">Project Name</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px;">
                                                                <?=$details['project_name']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th style="text-align:left; padding-left:5px; width:40%;">Job No.</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px;">
                                                            <?=$details['job_no']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th style="text-align:left; padding-left:5px; width:40%;">Location</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px;">
                                                            <?=$details['gps_location']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th style="text-align:left; padding-left:5px; width:40%;">Date</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px;">
                                                            <?=$details['near_miss_date']." ". $details['near_miss_time']?>
                                                            </td>
                                                        </tr>
                                                        
                                                    
                                                    </tbody>
                                    </table>			
                                <!-- <footer>Someone famous in <cite title="Source Title">Source Title</cite></footer> -->
									
                                   </div>
								   
                                <div   style="width:33%;float:right">
                                <h3><u>Reporter Details</u></h3>
                               
                                    <table id="table2">
                                                    <tbody>
                                                        <tr>
                                                            <th style="text-align:left; padding-left:5px; width:40%;">Name</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px;"> 
                                                                <?=$details['user_name']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th style="text-align:left; padding-left:5px; width:40%;">Email Id</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px;">
                                                            <?=$details['user_email']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th style="text-align:left; padding-left:5px; width:40%;">Contact No.</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px;">
                                                            <?=$details['user_contact']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th style="text-align:left; padding-left:5px; width:40%;">Designation</th>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px;">
                                                            <?=$details['user_designation']?>
                                                            </td>
                                                        </tr>
                                                        <!-- 
                                                        <tr>
                                                            <th>Status</th>
                                                            <td>
                                                            <?php //$status?>
                                                            </td>
                                                        </tr>
                                                    -->
                                                    </tbody>
                                    </table>			
                            
                                    </div>
									<div style="width:2%" >
                                         .
								</div>
							<?php if(isset($details['near_miss_priority'])){
                                    $low_priority = '<span class="badge badge-warning">Low</span>';
                                    $high_priority = '<span class="badge badge-danger">High</span>';
                                    $details['near_miss_priority']=($details['near_miss_priority']==0)?$low_priority:$high_priority;

                                }?>
                                <?php if(isset($details['rejection_comment']) && !empty($details['rejection_comment'])){ ?>
                                	    <table id="table8">
									     <thead>
                                            <tr>
                                               <th># Rejection Comment</th>
                                                      
											</tr>
										</thead>
										<tbody>

											    <tr><td><?php echo  isset($details['rejection_comment'])?$details['rejection_comment']:'' ;?></td></tr>
										</tbody>
									<?php } ?>	
									</table>
                                
									<table id="table7">
									     <thead>
                                            <tr>
                                               <th># Priority</th>
                                                      
											</tr>
										</thead>
										<tbody>
											    <tr><td><?php echo  isset($details['near_miss_priority'])?$details['near_miss_priority']:'' ;?></td></tr>
											    <tr><td><?php echo  isset($details['priority_comment'])?$details['priority_comment']:'' ;?></td></tr>
										</tbody>
									</table>
									<div style="text-align: center; margin-bottom: 20px;">
									<h3><u>Incident Details</u></h3>
									</div>
                                   <table id="table3">
									                <thead  >
                                                      <tr>
                                                            <th># Name of the equipment involved</th>
                                                            </tr><tr>
															<td style="text-align:left; padding-left:5px; padding-right:5px; "><?php echo  isset($details['involved_equipment_name'])?($details['involved_equipment_name']!="")?$details['involved_equipment_name']:'</br>':'' ;?></td>
													        
														</tr>
                                                        <tr>
                                                            <th># Description briefly how the incident occurs</th></tr><tr>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; "><?php echo  isset($details['description_of_occurred'])?($details['description_of_occurred']!="")?$details['description_of_occurred']:'</br>':'' ;?></td>
                                                        </tr>
														<tr>
                                                            <th># what could have happned</th></tr><tr>
															<td style="text-align:left; padding-left:5px; padding-right:5px; "><?php echo  isset($details['what_could_have_happened'])?($details['what_could_have_happened']!="")?$details['what_could_have_happened']:'</br>':'' ;?></td>
                                                            
                                                        </tr>
														<tr>
                                                            <th> # immediate_action</th></tr><tr>
                                                            <td style="text-align:left; padding-left:5px; padding-right:5px; "><?php echo  isset($details['immediate_action'])?($details['immediate_action']!="")?$details['immediate_action']:'</br>':'' ;?></td>
                                                        </tr>
                                                        </thead>
													
                                    </table>			
                            
								
					
<?php if($details['near_miss_category_text']!='Near Miss' && $details['near_miss_category_text']!='near_miss'){?>
								<div >
										<br>
                                        <table>
											<thead>
												<tr >
													<th colspan="5"># Details of Incident</th>
												</tr>
											</thead>
											<thead>
												<tr>

													<th>Name</th>
													<th>Description</th>
													<th>Age</th>
													<th>Company Name</th>
													<th>Shift</th>
												</tr>
											</thead>
											 <tbody>
											<tr>
												<td style="text-align:left; padding-left:5px; padding-right:5px; "><?=$details['incident_person_name']?></td>
												<td style="text-align:left; padding-left:5px; padding-right:5px; "><?=$details['incident_person_designation']?></td>
												<td style="text-align:left; padding-left:5px; padding-right:5px; "><?=$details['incident_person_age']?></td>
												<td style="text-align:left; padding-left:5px; padding-right:5px; "><?=$details['incident_person_company']?></td>
												<td style="text-align:left; padding-left:5px; padding-right:5px; "><?=$details['incident_person_sift']?></td>
											</tr>
                                        </tbody>
                                    </table>
									    <br>
												<table>
														
													
                                                      <thead>
													  <tr>
                                                            <th> # Task/Activity</th>
                                                         </tr>
														</thead>
														<tbody>
														<tr>
														<td>
                                                            <?=$details['description_of_occurred']?>
                                                            </td>
															</tr>
														</tbody>
												    
												 </table>
												  <br>
												  <table style = "width:100%">
														
                                                         <thead>
														 <tr>
                                                            <th> # Nature Of Injury Or Damage</th>
															</tr>
														 </thead>
														<tbody>
														<tr>
														<td>
														
                                                            <?=$details['causes_of_incident']?>
                                                            </td>
														</tr>
														</tbody>
														
												 </table>
												 <br>
												 
												<table>	
														
                                                      <thead  bgcolor="#e9ecef">
													  <tr>
                                                            <th > # Body Part Affect</th>
                                                       </tr>  
													  </thead>
													
												     <tbody>
							      						<tr>
														
														<td><?=$details['body_part_affect_details']?></td>
                                                        </tr>
														
													</tbody>
	
														
												</table>			
                            
								</div>
								
								
								
								<div >
<?php 
$near_miss_json_data =json_decode($details['future_occurrence'],true);
//echo "call";
//print_r($near_miss_json_data);

//echo " 1 call 1";
/*if(isset($near_miss_json_data['future_occurrence']) && count($near_miss_json_data['future_occurrence'])==1)
$near_miss_json_data=$near_miss_json_data['future_occurrence']['0'] ;
else 
$near_miss_json_data=$near_miss_json_data['future_occurrence'];*/
?>
<br>
<h4># What should be done future</h4>
<?php if(isset($near_miss_json_data)>0){ ?>
                                   <table id="table4">
									               
                                                      <thead>
                                                         <tr>
                                                            <th colspan=4># What should be done future </th>
													     </tr>
													</thead>
                                                    
                                                    <tbody>

                                                    <?php 
													
                                                        //$near_miss_json_data=$near_miss_json_data['future_occurrence']['0']
                                                        foreach($near_miss_json_data['future_occurrence'] as $near_miss_json_data){
                                                          ?>

													   <tr>
														<td>
                                                            <?=(isset($near_miss_json_data['causes']))?$near_miss_json_data['causes']:""?>
                                                        </td>
														<td>
                                                        <?=(isset($near_miss_json_data['corrective_action']))?$near_miss_json_data['corrective_action']:""?>
                                                        </td>
														<td>  <?=(isset($near_miss_json_data['person_responsible']))?$near_miss_json_data['person_responsible']:""?>
                                                        </td>
														<td>  <?=(isset($near_miss_json_data['target_date']))?$near_miss_json_data['target_date']:""?>
                                                        </td>
														</tr>
													<?php } ?>	
													</tbody>

									 </table>					
														
	<?php } ?>							
									
                                   
									<br>
                                    <table style = "width:100%">
                                                      <thead  >
                                                      <tr>
                                                            <th># Loss Hours </th>
                                                            </tr><tr>
													        <td><?=$details['total_hours']?></td>
														</tr>
                                                        <tr>
                                                            <th># Loss Cost </th></tr><tr>
                                                            <td><?=$details['total_cost']?></td>
                                                        </tr>
                                                        </thead>
                                    </table>			

								</div>
 <?php }?>				  
						  <br>
					   <div>
					   
<?php  

				if(isset($details['images'])?$details['images']:"") {
					 $temp=explode(",",$details['images']);
					 echo '<div class="row">';
					 echo '<h4><u>Attachment</u></h4> <br />';
						foreach($temp as $tem):
							if($tem!=""){
							    
                                                            $imageUrl = $tem;
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
                                                                               $options = [
                                'http' => [
                                    'ignore_errors' => true,
                                ],
                                'https' => [
                                    'ignore_errors' => true,
                                ],
                            ];
                                        $context = stream_context_create($options);


                                                            // Convert image to Base64
                                                            $imageData = file_get_contents($imageUrl,false,$context);
                                                            $base64 = base64_encode($imageData);
                                                            $imageBase64 = 'data:image/png;base64,' . $base64; // Change the MIME type as needed
                                                                if ($imageBase64) {
                                                                        echo '<a href="' . $imageUrl . '" target="_blank"><img src="' . $imageBase64 . '" width="100" height="100" alt="Audit Image" onclick=""></a>';
                                                                    } else {
                                                                        echo $linkHtml; // Show the link if image is not available
                                                                    }
//                                                        echo '5.<img src="'.$tem.'" height="100"  hspace="20" width="100">';
                                                  
							}
					  endforeach;
					echo '</div>';
					}
					 ?>
					  
					  </div>
					 
						
		 <br>
	
		<h3 style="text-align:center"><u>Near-Miss Chat</u></h3>
		
<?php if(!empty($near_miss_chat)) { ?>
   <table id="table5">
      <tr>
         <th>User</th>
         <th>Comment</th>
      </tr>
      <tbody>
         <?php foreach($near_miss_chat as $chat_row){ ?> 
         <tr>
            <td style="text-align:left; padding-left:5px; padding-right:5px; "><?=$chat_row['user_name']?></td>
            <td style="text-align:left; padding-left:5px; padding-right:5px; ">
               <?php if($chat_row['chat_type']==0) { ?>
               <?=$chat_row['chat_text']?>
               <?php } ?>
               <?php if($chat_row['chat_type']==1) { ?>
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
                                                            $imageUrl = $chat_row['chat_image'];
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

