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

if(isset($details) && !empty($details))
	$details=$details[0];

$pending_message = '<span class="badge badge-warning">Pending</span>';
$verified_message = '<span class="badge badge-info">Verified</span>';
$active_message = '<span class="badge badge-success">Active</span>';
$deactivated_message='<span class="badge badge-danger">Deactive</span>';
$status= ($details ['status'] == 0) ? $pending_message : ($details ['status'] == 1 ? $verified_message: ($details ['status'] == 2 ? $active_message : ($details ['status'] == 3 ? $deactivated_message : "")));
$near_miss_status= ($details ['status'] == 0) ? $pending_message : ($details ['status'] == 1 ? $verified_message: ($details ['status'] == 2 ? $active_message : ($details ['status'] == 3 ? $deactivated_message : "")));

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
									<span style="float:right"><button class="btn btn-info" onclick="event.preventDefault; $('#details_div').toggle('show'); ">show/hide </button></span>
								</div>
								<div class="card-body" id="details_div" style="display:none">
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
                                                            <?=$details['gps_location']?>
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
                                 <footer>Someone famous in <cite title="Source Title">Source Title</cite></footer> 
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
                                                         
                                                        <tr>
                                                            <th class="table-secondary">Status</th>
                                                            <td>
                                                            <?php $status?>
                                                            </td>
                                                        </tr>
                                                       
                                                    
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

                           }  ?>
                                
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

<!--chat-->
                        <div class="row mb-4">
						<div class="col-md-12">
							<div class="card">
								<div class="card-user-profile">
									<div class="profile-page-left">
										<div class="row">
											<div class="col-lg-12 mb-4">
												<div class="profile-picture profile-picture-lg bg-gradient bg-primary mb-4">
													<img src="<?=base_url().'assets/images/profile.png'?>" width="144" height="144" >
												</div>
 												<a class="btn btn-primary btn-block btn-gradient waves-effect waves-light" href="#"><span class="gradient">
													<i class="batch-icon batch-icon-user-alt-2"></i>
													<?=$details['user_name']?>
												</span></a>
											</div>
											<div class="col-sm-6">
												<h5 class="my-0">Users</h5>
												<div class="h3 my-0">
													<a href="#"><?=$details['group_member_count']?></a>
												</div>
											</div>
											<div class="col-sm-6">
												<h5 class="my-0">Comments</h5>
												<div class="h3 my-0">
													<a href="#"><?=count($near_miss_chat)?></a>
												</div>
											</div>
										</div>
									
										<hr>
											<div class="row">
											    <div class="col-sm-12">
											        
											        <h5 style="float:left">Chat Members</h5>
											        <span style="float:right">
					<!--						        <button   data-target='#myModal' data-toggle='modal' class="btn btn-outline-info btn-sm"  style="margin-top: -5%;">-->
					<!--<i class='fa fa-plus'  data-toggle='tooltip' data-placement='top' title='email details' id="involved_user"></i></button>-->
					</span>
											    </div>
										    <div class="col-sm-8" >
										        <h3> 
										    <?php $temp=explode(",",$details['involved_user']);
										    if(count($temp)>0){
										        foreach($temp as $value){
										            echo "<span class='badge badge-info'>".$value."</span><br> ";
										            
										        }
										    }
										    ?>
										    </h3>
										    </div>
										</div>
										<hr>
										<div>
										 <form action="<?=base_url ( index_page().'/reports/add_engineer/' )?>" method="post"  id="user_list_involved" class=login-form >
										<div class="row">
										    <div class="col-md-12">
										      <select  onfocusout='$("#user_list_involved").submit();' class="form-control select2" name="assigned_engineer_id[]" id="assigned_engineer_id" multiple="multiple">
                                                    <option value=''>select Member</option>
                                                <?php  foreach($users_list as $key=>$engineer_row){
                                                    echo '<option value='.$engineer_row['user_id'].' >'.$engineer_row['user_name'].'</option>';
                                                
                                                }
                                                ?>
                                                </select>
                                                <input type="hidden" name="near_miss_id" value="<?=$details['near_miss_id']?>">
										    </div>
										    <div class="col-md-3">
										        <button   class="btn btn-outline-info btn-sm" ><i class='fa fa-plus'  data-toggle='tooltip' data-placement='top' title='add user to chat ' id="add_user"></i></button>
										    </div>
										</div>
										</form>
										</div>
										<hr>
<!-- 										
										<h5>
											<i class="batch-icon batch-icon-image"></i>
											Album
										</h5> -->
										
									</div>
									<div class="profile-page-center">
										<h1 class="card-user-profile-name">Write Comment</h1>
                                        <form action="<?=$form_action?>" method="post">
										<div class="comment-block">
											<div class="form-group">
												<textarea name="comment_text" class="form-control" id="comment-textarea" rows="2" placeholder="Enter your comment here..." require></textarea>
												<div class="media-feed-control clearfix">
													<button type="submit" class="btn btn-secondary btn-sm comment-reply float-right waves-effect waves-light">Post</button>
													<!-- <a href="#" data-toggle="tooltip" title="" data-original-title="Add Picture">
														<i class="batch-icon batch-icon-image"></i>
													</a> -->
												</div>
											</div>
										</div>
                                        <input type="text" style="visibility:hidden" value="<?=$details['near_miss_id']?>" name="near_miss_id">
                                        <input type="text" style="visibility:hidden" value="<?=$login_id?>" name="user_id">
                                        </form>

										<hr>
										<ul class="list-unstyled mt-5">
                                        <?php foreach($near_miss_chat as $rows){ ?>
											<li class="media">
												<div class="profile-picture bg-gradient bg-primary mb-4">
													<img src="<?=base_url().'assets/images/profile.png'?>" width="44" height="44">
												</div>
												<div class="media-body">
													<div class="media-title mt-0 mb-1">
														<a href="#"><?=$rows['user_name']?></a><br><?=$rows['user_designation']?> <small> <?=$rows['default_date']?></small>
													</div>
                                                    <?php if($rows['chat_type']==1){?>
                                                    <a href="<?=$rows['chat_image']?>">
														<img src="<?=$rows['chat_image']?>" class="img-fluid img-thumbnail">
													</a>
													<?php }else{?>
													<?=$rows['chat_text']?>
                                                    <?php } ?>
													
												</div>
											</li>
                                        <?php } ?>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
                        
<script>

// var data_url='<?=base_url(index_page().'/Reports/add_engineer/'.$details['near_miss_id']); ?>';
// var values='';
//  $("#involved_user").on('click', ()=>{   
//     $("#myModalActionBtn").attr("value",$(this).attr(data_url)).html("Add Engineer"); 
//     $("#myModalActionBtn").attr("form","emform").attr("type","submit").attr("onclick",""); 
//     $("#myModalBody").html("<form action='<?=base_url ( index_page().'/reports/add_engineer/' )?>' method=post  id=emform class=login-form >"+ 
//     											"<div class=row>"+
//                                                 "<div class=col-md-12>"+
//     											"<label>Engineer</label>"+
//     											$("#select_engineer").html()+
//     											"<input type=hidden value=<?=$details['near_miss_id']?> >"+
//     											"</div>"+
//     											"</div>"+
//     											"</form>");

//     $("#myModalLabel").html("Engineers"); 
//     $("#myModalLabel").parent().parent();
//     values='<?=$details['group_member_ids']?>';
//     $('#assigned_engineer_id').val(values.split(',')).trigger('change');
// })

</script>

<!--<div id="select_engineer">-->
    
<!--            <select class="form-control " name="assigned_engineer_id[]" id="assigned_engineer_id" multiple="multiple">-->
<!--                <option value=''>select Member</option>-->
              <?php  //foreach($users_list as $key=>$engineer_row){-->
// <!--                echo '<option value='.$engineer_row['user_id'].' >'.$engineer_row['user_name'].'</option>';-->
            
// <!--            }-->
//       ?>
<!--            </select>-->
<!--</div>-->
<script>
$(()=>{
    values='<?=$details['group_member_ids']?>';
    $('#assigned_engineer_id').val(values.split(',')).trigger('change');
})

$("#add_user").on('click',()=>{
    $("#user_list_involved").submit();
});
</script>

<?php $this->endSection();?>	






