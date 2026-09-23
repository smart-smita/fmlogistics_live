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
$lmra_status= ($details ['status'] == 0) ? $pending_message : ($details ['status'] == 1 ? $verified_message: ($details ['status'] == 2 ? $active_message : ($details ['status'] == 3 ? $deactivated_message : "")));

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
                                                            <th class="table-secondary ">Project Name</th>
                                                            <td >
                                                                <?=$details['lmra_project_name']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary">Job No.</th>
                                                            <td>
                                                            <?=$details['lmra_job_no']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th class="table-secondary">Location</th>
                                                            <td>
                                                            <?=$details['lmra_location']?>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <th class="table-secondary">Visit Date</th>
                                                            <td>
                                                            <?=$details['lmra_visit_date']." ". $details['lmra_visit_time']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary">Status</th>
                                                            <td>
                                                            <?=$lmra_status?>
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
                                                            <th class="table-secondary ">User Name</th>
                                                            <td >
                                                                <?=$details['user_name']?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="table-secondary">Email Id</th>
                                                            <td>
                                                            <?=$details['user_name']?>
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
<?php 
$lmra_json_data =json_decode($details['lmra_json_data'],true);
$sta_json_data =json_decode($details['sta_data'],true);
?>
                                <div class="row">
                                <h4>STA Check List</h4>
                                <div class="col-lg-12 pb-5">                                

                                        <table  class="table table-hover"> 
                                        <tbody>
                                            <tr>
                                                <td>Do I have the right PPEs?</td>
                                                <td><?=(isset($sta_json_data['do_i_have_the_right_ppes']) && $sta_json_data['do_i_have_the_right_ppes']==1)?"YES":"NO"?></td>
                                            </tr>
                                            <tr>
                                                <td>Do I have the right tools and parts?</td>
                                                <td><?=(isset($sta_json_data['do_i_have_the_right_tools_and_parts']) && $sta_json_data['do_i_have_the_right_tools_and_parts']==1)?"YES":"NO"?></td>
                                            </tr>
                                            <tr>
                                                <td>Look around you, Is it safe?</td>
                                                <td><?=(isset($sta_json_data['look_around_you_is_it_safe']) && $sta_json_data['look_around_you_is_it_safe']==1)?"YES":"NO"?></td>
                                            </tr>

                                            <tr>
                                                <td>Do I know what to do if things go wrong?</td>
                                                <td><?=(isset($sta_json_data['do_i_know_what_to_do_if_things_go_wrong']) && $sta_json_data['do_i_know_what_to_do_if_things_go_wrong']==1)?"YES":"NO"?></td>
                                            </tr>

                                            <tr>
                                                <td>What is the biggest risk that can result in an injury / accident?</td>
                                                <td><?=(isset($sta_json_data['what_are_the_biggest_risk_that_can_result_in_an_injury']) && $sta_json_data['what_are_the_biggest_risk_that_can_result_in_an_injury']==1)?"YES":"NO"?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        </div>
                                </div>
                                <div class="row">
                                
                                <h4>LMRA Check List</h4>
                                <div class="col-lg-12 pb-5">                                
                                <table class="table">
                                    <?php foreach($lmra_json_data as $header_row){ ?>
                                            <thead class="thead-default">
                                                        <tr>
                                                            <th>#</th>
                                                            <th><?=$header_row['title_1_header']?></th>
                                                            <th><?=$header_row['title_2_header']?></th>
                                                            <th><?=$header_row['title_3_header']?></th>
                                                            <th><?=$header_row['title_4_header']?></th>
                                                            <th><?=$header_row['title_5_header']?></th>
                                                        </tr>
                                            </thead>
                                            <tbody>

                                            <?php 
                                            $index = 1;
                                            foreach($header_row['lmrsFooterForms'] as $footer_row){
                                            ?>
                                                <tr>
                                                    <th><?=$index?></th>
                                                    <th scope="row"><?=$footer_row['title_1_footer']?></th>
                                                    <td><input type="checkbox" <?=($footer_row['cb_1_footer'])?"checked":""?> disabled></td>
                                                    <td><input type="checkbox" <?=($footer_row['cb_2_footer'])?"checked":""?> disabled></td>
                                                    <td><input type="checkbox" <?=($footer_row['cb_3_footer'])?"checked":""?> disabled></td>
                                                    <td>
                                                    <select id="sevirity-<?=$footer_row['id_footer']?>" disabled>
                                                    <?php foreach($footer_row['strings_3'] as $idx => $sevirity){?>
                                                    <option <?= ($idx == ($footer_row['selected_string_3'] ?? null)) ? 'selected' : '' ?>><?=$sevirity['string']?></option>
                                                    <?php }  ?>
                                                    </select>

                                                    </td>
                                                    
                                                </tr>

                                            <?php $index++;} ?>
                                            </tbody>
                                        <?php } ?>
                                </table>
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
        <?php // changes on 9/10/25 by darsh: ensure hidden fields post only lmra_id; user_id is taken server-side from session ?>
        <input type="hidden" value="<?=$details['lmra_details_id']?>" name="lmra_id">
    </form>

    <hr>
    <?php // changes on 9/10/25 by darsh: render as vertical list matching theme ?>
    <div class="mt-5">
        <?php if (empty($lmra_comments)) { ?>
            <div class="text-muted">No comments yet.</div>
        <?php } ?>
        <?php foreach ($lmra_comments as $rows) { ?>
            <div class="d-flex align-items-start mb-4">
                <div class="me-3">
                    <img src="<?=base_url('assets/images/profile.png')?>" width="44" height="44" class="rounded-circle" alt="avatar">
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center mb-1">
                        <span class="fw-bold me-2"><?=$rows['user_name']?></span>
                        <?php if (isset($rows['user_designation']) && $rows['user_designation']!=='') { ?>
                            <span class="badge badge-light me-2" style="text-transform:none;"><?=$rows['user_designation']?></span>
                        <?php } ?>
                        <span class="text-muted small"><?=$rows['default_date']?></span>
                    </div>
                    <div>
                        <?php if (isset($rows['comment_type']) && $rows['comment_type'] == 1) { ?>
                            <a href="<?=$rows['comment_text']?>">
                                <img src="<?=$rows['comment_text']?>" class="img-fluid img-thumbnail" style="max-width:240px;">
                            </a>
                        <?php } else { ?>
                            <p class="mb-0" style="white-space:pre-wrap; word-break:break-word;"><?=$rows['comment_text']?></p>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <hr class="my-3">
        <?php } ?>
    </div>
</div>
</div>
					
<?php 				$this->endSection();?>
	

<?php 					$this->section("javascript_section");?>
             <script>
// No additional JS needed; selected option set server-side above
</script>          
<?php $this->endSection();?> 	

