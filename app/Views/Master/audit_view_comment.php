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

?>

<div class="row">
	<div class="col-sm-12">
		<h4 class="page-title"><?=(isset($page_title))?$page_title:""?></h4>
	</div>
</div>

<div class="col-md-12 mb-5">
							<div class="card">
								 <div class="card-header" style="padding: 10px; word-wrap: break-word;">
                                               <h2>Basic Details</h2> 
                                            </div>
                            	<div class="card-body">
                                <div class="row">
                                <div class="col-md-6">
                                    <h4 class="card-title">Reporting Location</h4>
                                    <blockquote class="blockquote">
                                    <table class="table table-hover">
                                                    <tbody>
                                                        <tr>
                                                            <th class="table-secondary ">Location</th>
                                                            <td >
                                                               <?=$details['location']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary">Nc Description</th>
                                                            <td>
                                                            <?=$details['nc_description']?>
                                                            </td>
                                                        </tr>
                                                         <tr>
                                                            <th class="table-secondary">Nc Photo</th>
                                                            <td>
                                                           <?=$details['nc_photo']?>
                                                            </td>
                                                        </tr>
                                                          <tr>
                                                            <th class="table-secondary">Action Required Dep</th>
                                                            <td>
                                                             <?=$details['action_required_dep']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary">Div Dep</th>
                                                            <td>
                                                             <?=$details['devision_dept']?>
                                                            </td>
                                                        </tr>
                                                          <tr>
                                                            <th class="table-secondary">Sixs Category</th>
                                                            <td>
                                                            <?=$details['sixs_category']?>
                                                            </td>
                                                        </tr>
                                                        
                                                        <tr>
                                                            <th class="table-secondary">Responsibilty</th>
                                                            <td>
                                                            <?=$details['responsibilty']?>
                                                            </td>
                                                        </tr>
                                                          <tr>
                                                            <th class="table-secondary">Target Date</th>
                                                            <td>
                                                            <?=$details['target_date']?>
                                                            </td>
                                                        </tr>
                                                         
                                                        <tr>
                                                            <th class="table-secondary">Status</th>
                                                            <td>
                                                            <?=$details['status']?>
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
                                                            <th class="table-secondary">Eamil</th>
                                                            <td>
                                                            <?=$details['user_email']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th class="table-secondary">Contact</th>
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
                                                            <th class="table-secondary">After Photo</th>
                                                            <td>
                                                            <?=$details['after_photo']?>
                                                            </td>
                                                        </tr>
                                                         <tr>
                                                            <th class="table-secondary">Details Of Ca Pa</th>
                                                            <td>
                                                            <?=$details['details_of_ca_pa']?>
                                                            </td>
                                                        </tr>
                                                         <tr>
                                                            <th class="table-secondary">Department Status</th>
                                                            <td>
                                                            <?=$details['department_status']?>
                                                            </td>
                                                        </tr>
                                                         <tr>
                                                            <th class="table-secondary">Final Status Soi</th>
                                                            <td>
                                                            <?=$details['final_status_soi']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary">Status</th>
                                                            <td>
                                                            <?=$status?>
                                                            </td>
                                                        </tr>
                                                    
                                                    </tbody>
                                    </table>			
                                <!-- <footer>Someone famous in <cite title="Source Title">Source Title</cite></footer> -->
									</blockquote>
                                    </div>
                                   
								</div>
                                <!-- start row -->
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
                                <div class="row">
                                <h4>Task To Be Perform</h4>
                                <div class="col-lg-12 pb-5">       
                                <p><?=isset($details['task_to_be_prform'])?$details['task_to_be_prform']:''?></p>
                                </div>
                                </div>
                                <!-- end row  -->
                                </div>
							</div>
						</div>
                       
<div class="row">
<div class="col-md-3 col-lg-3 col-xl-3 mb-5">
    <div class="card">
        <div class="card-user-profile">
            <div class="profile-page-left">
                <div class="row">
                    <div class="col-lg-12 mb-4 text-center" >
                        <div class="profile-picture profile-picture-lg bg-gradient bg-primary mb-4">
                            <img src="<?=base_url('assets/images/profile.png')?>" width="144" height="144">
                        </div>
                        <a class="btn btn-primary btn-block btn-gradient waves-effect waves-light" href="#">
                            <span class="gradient">
                                <i class="batch-icon batch-icon-user-alt-2"></i>
                                <?=$details['user_name']?>
                            </span>
                        </a>
                    </div>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-6 text-center mb-3">
                                <h5 class="my-0">Users</h5>
                                <div class="h3 my-0">
                                    <a href="#"><?=$details['user_count']?></a>
                                </div>
                            </div>
                            <div class="col-sm-6 text-center mb-3">
                                <h5 class="my-0">Comments</h5>
                                <div class="h3 my-0">
                                    <a href="#"><?=$details['comment_count']?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
            </div>
        </div>
    </div>
</div>

<div class="col-md-9 col-lg-9 col-xl-9 mb-5" style="background-color: #fff; padding: 20px; border-radius: 5px;">
    <h1 class="card-user-profile-name">Write Comment</h1>
    <form action="<?=$form_action?>" method="post">
        <div class="comment-block">
            <div class="form-group">
                <textarea name="comment_text" class="form-control" id="comment-textarea" rows="3" placeholder="Enter your comment here..." required style="width: 100%;"></textarea>
                <div class="media-feed-control clearfix mt-3">
                    <button type="submit" class="btn btn-primary btn-sm float-right waves-effect waves-light">Post</button>
                </div>
            </div>
        </div>
        <input type="text" style="visibility:hidden" value="<?=$details['audit_id']?>" name="audit_id">
        <input type="text" style="visibility:hidden" value="<?=$login_id?>" name="user_id">
    </form>

    <hr>
    <ul class="list-unstyled mt-5">
        <?php foreach ($sixs_comments as $rows) { ?>
            <li class="media mb-3">
                <div class="row">
                <div class="col-md-2 col-lg-2 col-xl-2 ">
                <div class="profile-picture ">
                    <img src="<?=base_url('assets/images/profile.png')?>" width="50" height="50">
                </div>
                </div>
            <div class="col-md-10 col-lg-10 col-xl-10 mb-5">
                <div class="media-body">
                    <div class="media-title mt-0 mb-1">
                        <a href="#"><?=$rows['user_name']?></a><br><?=$rows['user_designation']?> <small><?=$rows['default_date']?></small>
                    </div>
                    <?php if ($rows['comment_type'] == 1) { ?>
                        <a href="<?=$rows['comment_text']?>">
                            <img src="<?=$rows['comment_text']?>" class="img-fluid img-thumbnail">
                        </a>
                    <?php } else { ?>
                        <?=$rows['comment_text']?>
                    <?php } ?>
                </div>
                </div>
                </div>
            </li>
            <hr>
        <?php } ?>
    </ul>
</div>
</div>


                        
<?php $this->endSection();?>	

