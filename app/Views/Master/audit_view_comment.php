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
$status_num = (int)$details['status'];
if ($status_num == 0) { $status = '<span class="badge badge-warning">Pending</span>'; }
elseif ($status_num == 1) { $status = '<span class="badge badge-info">Verify</span>'; }
elseif ($status_num == 2) { $status = '<span class="badge badge-primary">Inprogress</span>'; }
elseif ($status_num == 3) { $status = '<span class="badge badge-success">Close</span>'; }
elseif ($status_num == 4) { $status = '<span class="badge badge-danger">Reject</span>'; }
else { $status = '<span class="badge badge-secondary">Unknown</span>'; }
?>

<div class="row mb-4">
	<div class="col-sm-12 d-flex justify-content-between align-items-center">
		<h4 class="page-title m-0"><?=(isset($page_title))?$page_title:""?></h4>
		<a href="<?=base_url('Masters/Sixs_audit')?>" class="btn btn-secondary"><i class="fa fa-arrow-left me-2"></i> Back</a>
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
                                    <div class="table-responsive">
                                    <table class="table table-hover table-bordered mb-0">
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
                                                                <?php if(!empty($details['nc_photo'])) { ?>
                                                                    <a href="<?=base_url($details['nc_photo'])?>" target="_blank">
                                                                        <img src="<?=base_url($details['nc_photo'])?>" class="img-thumbnail" style="max-height:100px; max-width:100%;">
                                                                    </a>
                                                                <?php } else { echo "N/A"; } ?>
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
                                                            <?=$status?>
                                                            </td>
                                                        </tr>

                                                      
                                                    </tbody>
                                    </table>
                                    </div>
									</blockquote>
                                    </div>
                                <div class="col-md-6">
                                <h4 class="card-title">Repoter Details</h4>
                                <blockquote class="blockquote">
                                    <div class="table-responsive">
                                    <table class="table table-hover table-bordered mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <th class="table-secondary ">Name</th>
                                                            <td >
                                                                <?=($details['user_id'] == 0) ? "Super Admin" : $details['user_name']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary">Email</th>
                                                            <td>
                                                            <?=($details['user_id'] == 0) ? "admin@fmlogistic.com" : $details['user_email']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th class="table-secondary">Contact</th>
                                                            <td>
                                                            <?=($details['user_id'] == 0) ? "-" : $details['user_contact']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th class="table-secondary">Designation</th>
                                                            <td>
                                                            <?=($details['user_id'] == 0) ? "Administrator" : $details['user_designation']?>
                                                            </td>
                                                        </tr>
                                                        
                                                          <tr>
                                                            <th class="table-secondary">After Photo</th>
                                                            <td>
                                                                <?php if(!empty($details['after_photo'])) { ?>
                                                                    <a href="<?=base_url($details['after_photo'])?>" target="_blank">
                                                                        <img src="<?=base_url($details['after_photo'])?>" class="img-thumbnail" style="max-height:100px; max-width:100%;">
                                                                    </a>
                                                                <?php } else { echo "N/A"; } ?>
                                                            </td>
                                                        </tr>
                                                         <tr>
                                                            <th class="table-secondary">Details Of Ca Pa</th>
                                                            <td>
                                                            <?=$details['details_of_ca_pa']?>
                                                            </td>
                                                        </tr>

                                                    
                                                    </tbody>
                                    </table>
                                    </div>
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
                                <?=($details['user_id'] == 0) ? "Super Admin" : $details['user_name']?>
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
    <?php if($details['status'] != 3 && $details['status'] != 5): ?>
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
    <?php else: ?>
    <div class="text-center p-4 bg-light rounded mb-4">
        <span class="text-muted fw-bold"><i class="fa fa-lock me-2"></i> Chat is disabled for this closed record.</span>
    </div>
    <?php endif; ?>

    <hr>
    <ul class="list-unstyled mt-5">
        <?php foreach ($sixs_comments as $rows) { ?>
            <?php 
                $c_name = ($rows['user_id'] == 0) ? "Super Admin" : $rows['user_name'];
                $c_desig = ($rows['user_id'] == 0) ? "Administrator" : $rows['user_designation'];
            ?>
            <li class="media mb-4 p-3" style="border: 1px solid #e1e1e1; border-radius: 8px; background: #f9f9f9;">
                <div class="row w-100">
                    <div class="col-md-1 text-center">
                        <div class="profile-picture">
                            <img src="<?=base_url('assets/images/profile.png')?>" width="45" height="45" class="rounded-circle">
                        </div>
                    </div>
                    <div class="col-md-11">
                        <div class="media-body">
                            <div class="media-title mt-0 mb-2">
                                <strong class="text-primary"><?=$c_name?></strong> <span class="badge badge-light text-muted"><?=$c_desig?></span>
                                <small class="float-right text-muted"><?=$rows['default_date']?></small>
                            </div>
                            <div class="comment-content mt-2" style="font-size: 14px; color: #333;">
                                <?php if ($rows['comment_type'] == 1) { ?>
                                    <a href="<?=base_url($rows['comment_text'])?>" target="_blank">
                                        <img src="<?=base_url($rows['comment_text'])?>" class="img-fluid img-thumbnail" style="max-height: 150px;">
                                    </a>
                                <?php } else { ?>
                                    <?=nl2br(htmlspecialchars($rows['comment_text']))?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        <?php } ?>
    </ul>
</div>
</div>


                        
<?php $this->endSection();?>	

