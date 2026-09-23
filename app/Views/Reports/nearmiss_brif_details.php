<?php $this->extend("Layout/base_admin"); ?>
<?php 
					$this->section("breadcrumb_title_li");
?>


<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"Client"?></a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>

<?php

$details=$details['0'];
$pending_message = '<span class="badge badge-warning">Pending</span>';
$verified_message = '<span class="badge badge-info">Verified</span>';
$active_message = '<span class="badge badge-success">Active</span>';
$pending_message_nm = '<span class="badge badge-danger">Pending</span>';
    $Requested_message = '<span class="badge badge-warning">Request required</span>';
    $open_message = '<span class="badge badge-warning">Open</span>';
    $close_message = '<span class="badge badge-success">Close</span>';
    $cancle_message = '<span class="badge badge-secondary">Cancle</span>';
        
        
$status= ($details ['status'] == 0) ? $pending_message : ($details ['status'] == 1 ? $verified_message: ($details ['status'] == 2 ? $active_message : ($details ['status'] == 3 ? $deactivated_message : "")));
$near_miss_status= ($details ['status'] == 0) ? $pending_message_nm : ($details ['status'] == 1 ? $open_message: ($details ['status'] == 2 ? $Requested_message : ($details ['status'] == 3 ? $close_message :$cancle_message)));
?>
<div class="row">
	<div class="col-sm-12">
		<h4 class="page-title"><?=(isset($page_title))?$page_title:""?></h4>
	</div>
</div>


<div class="col-md-12 mb-5">
							<div class="card">
								<div class="card-header">
									Brif Details Of <?=$details['near_miss_category_text']?>
									<span style="float:right"><?php print_r($print); ?></span>
								</div>
								<div class="card-body">
                                <div class="row">
                                <div class="col-md-6">
								
                                    <h4 class="card-title">Reporting Location</h4>
                                    <blockquote class="blockquote">
                                    <table class="table table-hover" >
                                                    <tbody>
                                                        <tr>
                                                            <th class="table-secondary ">Project Name</th>
                                                            <td >
                                                                <?=$details['project_name']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary">Job No.</th>
                                                            <td>
                                                            <?=$details['job_no']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th class="table-secondary" >Location</th>
                                                            <td style="max-width:100px;">
                                                            <?=$details['location']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th class="table-secondary">Date</th>
                                                            <td>
                                                            <?=$details['near_miss_date']." ". $details['near_miss_time']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary">Status</th>
                                                            <td>
                                                            <?=$near_miss_status?>
                                                            </td>
                                                        </tr>
                                                    
                                                    </tbody>
                                    </table>			
                                <!-- <footer>Someone famous in <cite title="Source Title">Source Title</cite></footer> -->
									</blockquote>
                                    </div>
                                <div class="col-md-6">
                                <h4 class="card-title">Repoter Details</h4>
                                <blockquote class="blockquote">
                                    <table class="table table-hover">
                                                    <tbody>
                                                        <tr>
                                                            <th class="table-secondary ">Name</th>
                                                            <td >
                                                                <?=$details['user_name']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary">Email Id</th>
                                                            <td>
                                                            <?=$details['user_email']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th class="table-secondary">Contact No.</th>
                                                            <td>
                                                            <?=$details['user_contact']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th class="table-secondary">Designation</th>
                                                            <td>
                                                            <?=$details['user_designation']?>
                                                            </td>
                                                        </tr>
                                                        <!-- 
                                                        <tr>
                                                            <th class="table-secondary">Status</th>
                                                            <td>
                                                            <?php //$status?>
                                                            </td>
                                                        </tr>
                                                        -->
                                                    
                                                    </tbody>
                                    </table>			
                            </blockquote>
                                    </div>
                               
                                <!-- end row  -->
                                </div>
                                <?php if(isset($details['near_miss_priority'])){
                                    $low_priority = '<span class="badge badge-warning">Low</span>';
                                    $high_priority = '<span class="badge badge-danger">High</span>';
                                    $details['near_miss_priority']=($details['near_miss_priority']==0)?$low_priority:$high_priority;

                                } 
                                ?>
                                 <?php if(isset($details['rejection_comment']) && !empty($details['rejection_comment'])){?>
                                    <div class="col-lg-12 pb-5">
                                        <table class="table table-hover">
                                            <thead class="thead-default">
                                                <tr>
                                                    <th ># Rejection comment</th>
												</tr>
											</thead> 
											<tbody>
											    <tr><td><?php echo  isset($details['rejection_comment'])?$details['rejection_comment']:'' ;?></td></tr>
											    
											</tbody>
                                        </table>
                                    </div>
                                 <?php } ?>
                                
									<div class="col-lg-12 pb-5">
                                        <table class="table table-hover">
                                            <thead class="thead-default">
                                                <tr>
                                                            <th ># Priority</th>

												</tr>
											</thead> 
											<tbody>
											    <tr><td><?php echo  isset($details['near_miss_priority'])?$details['near_miss_priority']:'' ;?></td></tr>
											    <tr><td><?php echo  isset($details['priority_comment'])?$details['priority_comment']:'' ;?></td></tr>
											</tbody>
                                        </table>
                                    </div>
                                    
									
									<h2>Incident Details</h2>
									<div class="col-lg-12 pb-5">
                                    <table class="table">
										                <thead class="thead-default">
                                                      <tr>
                                                            <th ># Name of the equipment involved</th>
                                                            </tr><tr>
															<td><?php echo  isset($details['involved_equipment_name'])?($details['involved_equipment_name']!="")?$details['involved_equipment_name']:'</br>':'no' ;?></td>
													        
														</tr>
                                                        <tr>
                                                            <th ># Description briefly how the incident occurs</th></tr><tr>
                                                            <td><?php echo  isset($details['description_of_occurred'])?($details['description_of_occurred']!="")?$details['description_of_occurred']:'</br>':'no' ;?></td>
                                                        </tr>
														<tr>
                                                            <th ># what could have happned</th></tr><tr>
															<td><?php echo  isset($details['what_could_have_happened'])?($details['what_could_have_happened']!="")?$details['what_could_have_happened']:'</br>':'no' ;?></td>
                                                            
                                                        </tr>
														<tr>
                                                            <th > # immediate_action</th></tr><tr>
                                                            <td><?php echo  isset($details['immediate_action'])?($details['immediate_action']!="")?$details['immediate_action']:'</br>':'no' ;?></td>
                                                        </tr>
                                                        </thead>
													
                                    </table>
												
                            
								</div>
							<div id="test">	
								<div class="col-lg-12 pb-5">
                                    <table class="table">
                                        <thead class="thead-default">
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
                                            <td><?=$details['incident_person_name']?></td>
                                            <td><?=$details['incident_person_designation']?></td>
                                            <td><?=$details['incident_person_age']?></td>
                                            <td><?=$details['incident_person_company']?></td>
                                            <td><?=$details['incident_person_sift']?></td>
                                        </tr>
                                        </tbody>
                                    </table>

                                                <table class="table">

													<tr>
                                                      <thead class="thead-default">
                                                            <th> # Task/Activity</th>
                                                         
														</thead>
														<tbody>
														<td>
                                                            <?=(isset($near_miss_json_data['description_of_occurred']))?$near_miss_json_data['description_of_occurredt']:""?> 
                                                            </td>
														</tbody>
												  </tr>
												  </table>
												  
												  <table class="table">
														<tr>
                                                         <thead class="thead-default">
                                                            <th> # Nature Of Injury Or Damage</th>
														 </thead>
														<tbody>
														<td>
                                                           <?=(isset($near_miss_json_data['causes_of_incident']))?$near_miss_json_data['causes_of_incident']:""?> 
                                                            </td>
														</tbody>
														</tr>
												 </table>
												 
												<table class="table">	
														<tr>
                                                      <thead class="thead-default">
                                                            <th scope="row"> # Body Part Affect</th>
                                                         
														</thead>
														<tbody>
							      						<tr>
														<td><?php echo  isset($details['body_part_affect_details'])?($details['body_part_affect_details']!="")?$details['body_part_affect_details']:'</br>':'' ;?></td>
														</tr>
														
														</tbody>
														</tr>
														
														
                                    
														
                                                </table>			
                            
								</div>
								
								
								
								<div class="col-lg-12 pb-5">
                                <?php 
$near_miss_json_data =json_decode($details['future_occurrence'],true);
// echo "call";
// print_r($near_miss_json_data);
// echo " 1 call 1";
if(isset($near_miss_json_data['future_occurrence']) && count($near_miss_json_data['future_occurrence'])==1)
// $near_miss_json_data=$near_miss_json_data['future_occurrence']['0'] ;
// else 
// $near_miss_json_data=$near_miss_json_data['future_occurrence'];
?>

                                    <table class="table">
									                <tr>
                                                      <thead class="thead-default">
                                                        
                                                            <th colspan=4># What should be done future </th>
													
													</thead>
                                                    </tr>
                                                    <tbody>

                                                    <?php 
                                                        //$near_miss_json_data=$near_miss_json_data['future_occurrence']['0']
                                                        if(is_array($near_miss_json_data['future_occurrence']))
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
                                    <table class="table">
                                                      <thead class="thead-default">
                                                      <tr>

                                                            <th ># Loss Hours </th>
                                                            </tr><tr>
													        <td><?=(isset($near_miss_json_data['total_hours']))?$near_miss_json_data['total_hours']:""?></td>
														</tr>
                                                        <tr>
                                                            <th ># Loss Cost </th></tr><tr>
                                                            <td><?=(isset($near_miss_json_data['total_cost']))?$near_miss_json_data['total_cost']:""?></td>
                                                        </tr>
                                                        </thead>
                                    </table>			
                            
								</div>
                      </div> 
                    					  
					   <div class="col-lg-12 pb-5">
					   
					 <?php  

				if(isset($details['images'])?$details['images']:"") {
					 $temp=explode(",",$details['images']);
					 echo '<div class="row">';
						foreach($temp as $tem):
						if($tem!=""){
						//print_r($tem);
							echo'<div class="column">';
							echo '<img src='.$tem.' height="150" onclick="window.open(this.src)" hspace="20" width="150">';
							echo '</div>';
						}
					  endforeach;
					echo '</div>';
					}
					 ?>
					  
					  </div>
					 
							</div>
						</div>
</div>


<script>
$(document).ready(function(){
	
$("#test").hide();
var type="<?php echo $details['near_miss_category_text']?>";

if (type!='Near Miss'&& type!='near_miss'){
	$("#test").show();
	
}
})
</script>
<?php $this->endSection();?>
