
<?php 
$form_id= $button_id;
//data-ajax-url during add/edit 
//data-ajax-add-url during add Important
?>
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
<?=$table?>
<?php $this->endSection(); ?>

<?php  $this->section("modals_section"); ?>

		<!--begin::Modal - Create App-->
		<div class="modal fade" id="<?=$button_id?>" aria-hidden="true">
		    <form id="<?=$form_id?>_form" onsubmit="return false;">
			<!--begin::Modal dialog-->
			<div class="modal-dialog modal-dialog-centered mw-900px">
				<!--begin::Modal content-->
				<div class="modal-content">
					<!--begin::Modal header-->
					<div class="modal-header">
						<!--begin::Modal title-->
						<h2>Near Miss Details</h2>
						<!--end::Modal title-->
						<!--begin::Close-->
						<div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
							<!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
							<span class="svg-icon svg-icon-1">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
									<rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
									<rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
								</svg>
							</span>
							<!--end::Svg Icon-->
						</div>
						<!--end::Close-->
					</div>
					<!--end::Modal header-->
					<!--begin::Modal body-->
					<div class="modal-body py-lg-10 px-lg-10">
       <!--<div class="scroll-y me-n7 pe-7" id="user" data-kt-scroll="false" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_new_address_header" data-kt-scroll-wrappers="#fees_head" data-kt-scroll-offset="300px" style="max-height: 273px;">-->
								<!--begin::Input group-->
							<!--	<div class="row mb-12">-->
									<!--begin::Col-->
							<!--		<div class="col-md-12 fv-row fv-plugins-icon-container">-->
							<!--			<label class="required fs-5 fw-bold mb-2">category name </label>-->
							<!--			<input type="text" class="form-control form-control-solid" placeholder="Please enter Incident Name" id="category_name" name="category_name">-->
							<!--			<div class="fv-plugins-message-container invalid-feedback"></div>-->
							<!--		</div>-->
									
									
									
									<!--end::Col-->
							<!--	</div>-->
								
								
								
								<!--end::Input group-->
							<!--</div>-->
<style>
  #result {
            position: absolute;
            background: darkgrey;
        }
        .result-item {
            padding: 8px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
        }
        .result-item:hover {
            background-color: #f0f0f0;
        }
</style>
<div class="row mb-5">
   <div class="container-fluid px-0">
    <!-- General Information Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h3 class="card-title m-0 fw-bolder">General Information</h3>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-6 col-md-12 col-12 mb-3" id="report_for_div">
                    <label class="control-label fw-bold mb-2">Report For <span class="text-danger">*</span></label><br>
                    <div class="form-check form-check-inline mt-2">
                        <input class="form-check-input" type="radio" name="self_other" id="self_radio" value="0" checked onchange="toggleOtherEngineer()">
                        <label class="form-check-label" for="self_radio">Self</label>
                    </div>
                    <div class="form-check form-check-inline mt-2">
                        <input class="form-check-input" type="radio" name="self_other" id="other_radio" value="1" onchange="toggleOtherEngineer()">
                        <label class="form-check-label" for="other_radio">Other Engineer</label>
                    </div>
                </div>

                <div class="col-lg-6 col-md-12 col-12 mb-3" id="other_engineer_div" style="display: none;">
                    <label class="control-label fw-bold mb-2">Other Engineer <span class="text-danger">*</span></label>
                    <select class="form-select form-select-solid" name="other_engineer_id" id="other_engineer_id">
                        <option value="">Select Engineer</option>
                        <?php foreach($engineer_list as $engineer){?>
                            <option value="<?=$engineer['user_id'] ?>"><?=$engineer['user_name'] ?></option>
                        <?php }?>
                    </select>
                </div>

                <div class="col-12 mb-3" id="public_reporter_div" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <h5 class="fw-bolder mb-3 text-primary">Public Reporter Information</h5>
                    <div class="row">
                        <div class="col-md-3"><strong>Name:</strong> <span id="pub_name"></span></div>
                        <div class="col-md-3"><strong>Emp Code:</strong> <span id="pub_emp_code"></span></div>
                        <div class="col-md-3"><strong>Email:</strong> <span id="pub_email"></span></div>
                        <div class="col-md-3"><strong>Mobile:</strong> <span id="pub_mobile"></span></div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Category of Incidence <span class="text-danger">*</span></label>
                    <select name="near_miss_category_text" class="form-select form-select-solid" id="near_miss_category_text">
                        <option value="Near Miss">Near Miss</option>
                        <option value="Minor Injury">Minor Injury</option>
                        <option value="Major Injury">Major Injury</option>
                    </select>
                </div>

                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Customer/Project Name <span class="text-danger">*</span></label>
                    <input type="text" name="project_name" id="project_name" class="form-control form-control-solid" value="<?php echo isset($project_name)?$project_name:""?>" required>
                </div>

                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Job no <span class="text-danger">*</span></label>
                    <input type="text" name="job_no" id="job_no" class="form-control form-control-solid" value="<?php echo isset($job_no)?$job_no:""?>" required>
                </div>

                <div class="col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Location <span class="text-danger">*</span></label>
                    <select class="form-select form-select-solid" name="location" id="location" required>
                        <option value="">Select Location</option>
                        <?php
                        $selectedLoc = isset($location) ? $location : "";
                        if (isset($locations) && is_array($locations)) {
                            foreach ($locations as $loc) {
                                $selected = ($selectedLoc == $loc['location_name']) ? "selected" : "";
                                echo '<option value="' . htmlspecialchars($loc['location_name']) . '" ' . $selected . '>' . htmlspecialchars($loc['location_name']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Gps location <span class="text-danger">*</span></label>
                    <input type="text" name="gps_location" id="gps_location" class="form-control form-control-solid" value="<?php echo isset($gps_location)?$gps_location:""?>" required>
                </div>

                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Exact Location <span class="text-danger">*</span></label>
                    <input type="text" name="manual_location" id="manual_location" class="form-control form-control-solid" value="<?php echo isset($manual_location)?$manual_location:""?>" required>
                </div>

                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Date <span class="text-danger">*</span></label>
                    <input type="date" name="near_miss_date" id="near_miss_date" class="form-control form-control-solid" value="<?php echo isset($near_miss_date)?$near_miss_date:date("Y-m-d")?>" required>
                </div>

                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Time <span class="text-danger">*</span></label>
                    <input type="time" name="near_miss_time" id="near_miss_time" class="form-control form-control-solid" value="<?php echo isset($near_miss_time)?$near_miss_time:""?>" required>
                </div>
            </div>
        </div>
    </div>

    <div id="conditional_data">
        <div id="near_miss" style="display:none"></div>
        
        <div id="Injury_Details" style="display:none">
            <!-- Incident Person Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h3 class="card-title m-0 fw-bolder">Incident Person Details</h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-6 col-md-12 col-12 mb-3">
                            <label class="control-label fw-bold mb-2">Name of person <span class="text-danger">*</span></label>
                            <input type="text" name="incident_person_name" id="incident_person_name" class="form-control form-control-solid" value="<?php echo isset($incident_person_name)?$incident_person_name:""?>" required>
                        </div>
                        <div class="col-lg-6 col-md-12 col-12 mb-3">
                            <label class="control-label fw-bold mb-2">Designation <span class="text-danger">*</span></label>
                            <input type="text" name="incident_person_designation" id="incident_person_designation" class="form-control form-control-solid" value="<?php echo isset($incident_person_designation)?$incident_person_designation:""?>" required>
                        </div>
                        <div class="col-lg-6 col-md-12 col-12 mb-3">
                            <label class="control-label fw-bold mb-2">Age <span class="text-danger">*</span></label>
                            <input type="text" name="incident_person_age" id="incident_person_age" class="form-control form-control-solid numeric" value="<?php echo isset($incident_person_age)?$incident_person_age:""?>" required>
                        </div>
                        <div class="col-lg-6 col-md-12 col-12 mb-3">
                            <label class="control-label fw-bold mb-2">Company <span class="text-danger">*</span></label>
                            <input type="text" name="incident_person_company" id="incident_person_company" class="form-control form-control-solid" value="<?php echo isset($incident_person_company)?$incident_person_company:""?>" required>
                        </div>
                        <div class="col-lg-6 col-md-12 col-12 mb-3">
                            <label class="control-label fw-bold mb-2">Shift <span class="text-danger">*</span></label>
                            <input type="text" name="incident_person_sift" id="incident_person_sift" class="form-control form-control-solid" value="<?php echo isset($incident_person_sift)?$incident_person_sift:""?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Incident Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h3 class="card-title m-0 fw-bolder">Injury Details</h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-6 col-md-12 col-12 mb-3">
                            <label class="control-label fw-bold mb-2">Damage Nature <span class="text-danger">*</span></label>
                            <select name="nature_of_damage" class="form-select form-select-solid" id="nature_of_damage" required>
                                <option value="">Select Damage Nature</option>
                                <?php foreach($nature_incident as $nature){?>
                                <option value="<?=$nature['incident_name'] ?>" <?= isset($nature_of_damage) && $nature_of_damage==$nature['incident_name']?" selected='selected'":''?>><?=$nature['incident_name'] ?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-12 col-12 mb-3">
                            <label class="control-label fw-bold mb-2">Is Body Part Affected? <span class="text-danger">*</span></label>
                            <select name="body_part_affect" class="form-select form-select-solid" id="body_part_affect" required>
                                <option value="0" <?= isset($body_part_affect) && $body_part_affect=='0'?" selected='selected'":''?>>No</option>
                                <option value="1" <?= isset($body_part_affect) && $body_part_affect=='1'?" selected='selected'":''?>>Yes</option>
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-12 col-12 mb-3">
                            <label class="control-label fw-bold mb-2">Body Part Details</label>
                            <select name="body_part_affect_details" class="form-select form-select-solid" id="body_part_affect_details">
                                <option value="">Select Body Part</option>
                                <?php foreach($body_part as $part){?>
                                <option value="<?=$part['body_part_name'] ?>" <?= isset($body_part_affect_details) && $body_part_affect_details==$part['body_part_name']?" selected='selected'":''?>><?=$part['body_part_name'] ?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="control-label fw-bold mb-2">Causes of Incident</label>
                            <input type="text" name="causes_of_incident" id="causes_of_incident" class="form-control form-control-solid" value="<?php echo isset($causes_of_incident)?$causes_of_incident:""?>">
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- Ends Injury_Details -->
    </div> <!-- Ends conditional_data -->

    <!-- Event Specifics Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h3 class="card-title m-0 fw-bolder">Event Specifics</h3>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Immediate Action <span class="text-danger">*</span></label>
                    <input type="text" name="immediate_action" id="immediate_action" class="form-control form-control-solid" value="<?php echo isset($immediate_action)?$immediate_action:""?>" required>
                </div>
                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">What could have Happened <span class="text-danger">*</span></label>
                    <input type="text" name="what_could_have_happened" id="what_could_have_happened" class="form-control form-control-solid" value="<?php echo isset($what_could_have_happened)?$what_could_have_happened:""?>" required>
                </div>
                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Task Activity <span class="text-danger">*</span></label>
                    <select name="mode_of_work" class="form-control form-control-solid" id="mode_of_work" required>
                        <option value="">Select Task Activity</option>
                        <?php foreach($task_activity as $task) { ?>
                        <option value="<?=$task['category_name']?>" <?= isset($mode_of_work) && $mode_of_work==$task['category_name']?" selected='selected'":''?>><?=$task['category_name']?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Involved Equipment Name <span class="text-danger">*</span></label>
                    <input type="text" name="involved_equipment_name" id="involved_equipment_name" class="form-control form-control-solid" value="<?php echo isset($involved_equipment_name)?$involved_equipment_name:""?>" required>
                </div>
                <div class="col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Occurred Description <span class="text-danger">*</span></label>
                    <textarea name="description_of_occurred" id="description_of_occurred" class="form-control form-control-solid" rows="3" required><?php echo isset($description_of_occurred)?$description_of_occurred:""?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Future Occurrence / Corrective Action Card -->
    <div class="card shadow-sm mb-4" id="future_occuance_div">
        <div class="card-header py-3">
            <h3 class="card-title m-0 fw-bolder">Corrective Action & Future Occurrence</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive mb-3">
                <table class="table table-hover align-middle" style="display:none; min-width: 600px;" id="fuc_occ_table">
                    <thead class="thead-light">
                        <tr><th>Causes</th><th>Corrective Action</th><th>Person Responsible</th><th>Target Date</th><th>Action</th></tr>
                    </thead>
                    <tbody id="future_occurrence_data"></tbody>
                </table>
            </div>
            
            <input type="hidden" id="future_occurrence" name="future_occurrence" value="<?= !empty($future_occurrence) ? htmlspecialchars($future_occurrence, ENT_QUOTES) : '{"future_occurrence":[]}' ?>">

            <div class="row g-3">
                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Root Cause</label>
                    <input type="text" id="fut_cause" class="form-control form-control-solid" value="" >
                </div>
                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Corrective Action</label>
                    <input type="text" class="form-control form-control-solid" id="fut_corrective_action" value="" >
                </div>
                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Person Responsible</label>
                    <input type="text" class="form-control form-control-solid" id="fut_person_responsible" value="" >
                </div>
                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Date</label>
                    <input type="date" class="form-control form-control-solid" id="fut_target_date" value="" >
                </div>
                <div class="col-12 mt-3">
                    <button class="btn btn-success w-100 w-md-auto float-md-end" onclick="event.preventDefault(); add_future_occurance();">Add Action</button>
                </div>
            </div> 
        </div>
    </div>

    <!-- Additional Details Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h3 class="card-title m-0 fw-bolder">Additional Details</h3>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Priority</label><br>
                    <div class="form-check form-check-inline mt-2">
                        <input class="form-check-input" type="radio" name="near_miss_priority" id="priority_low" value="0" <?= (isset($near_miss_priority) && $near_miss_priority == '0') ? 'checked' : (!isset($near_miss_priority) ? 'checked' : '') ?> required>
                        <label class="form-check-label" for="priority_low">Low</label>
                    </div>
                    <div class="form-check form-check-inline mt-2">
                        <input class="form-check-input" type="radio" name="near_miss_priority" id="priority_high" value="1" <?= (isset($near_miss_priority) && $near_miss_priority == '1') ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="priority_high">High</label>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Priority Comment</label>
                    <input type="text" name="priority_comment" id="priority_comment" class="form-control form-control-solid" value="<?php echo isset($priority_comment)?$priority_comment:""?>" >
                </div>
                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Lost Hours</label>
                    <input type="text" name="total_hours" id="total_hours" class="form-control form-control-solid numeric" value="<?php echo isset($total_hours)?$total_hours:""?>" >
                </div>
                <div class="col-lg-6 col-md-12 col-12 mb-3">
                    <label class="control-label fw-bold mb-2">Lost Cost</label>
                    <input type="text" name="total_cost" id="total_cost" class="form-control form-control-solid numeric" value="<?php echo isset($total_cost)?$total_cost:""?>" >
                    
                    <!-- Hidden Fields -->
                    <input type="hidden" name="user_id" class="form-control" value="<?php echo isset($user_id)?$user_id:$_SESSION['login_id']?>" id="user_id">
                    <input type="hidden" name="fcm_id" class="form-control" value="<?php echo isset($_SESSION['fcm_id'])?$_SESSION['fcm_id']:''?>" id="fcm_id">
                </div>
            </div>
        </div>
    </div>

    <!-- Attachments Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h3 class="card-title m-0 fw-bolder">Before Proof / Evidence Attachments</h3>
        </div>
        <div class="card-body">
            <input name="images" id="images" value="" type="hidden"> 
            <div class="row g-4">
                <div class="col-lg-6 col-md-12 col-12 mb-3 text-center">
                    <div class="border p-3 rounded h-100 d-flex flex-column align-items-center justify-content-center">
                        <img alt="Banner" src="<?php echo isset($images)?base_url().$images: base_url('uploads/default_img.png');?>" class="img-fluid rounded mb-3 shadow-sm" style="max-height: 200px; object-fit: contain;" id="near_miss_att">
                        <div class="mt-2 w-100">
                            <label class="control-label fw-bold mb-2 d-block">Upload Before Proof 1:</label> 
                            <input class="form-control" type="file" id="test" img-id="near_miss_att" name="file_1" onchange="readURL1hhh(this); validateFileType(this);" accept=".jpg,.jpeg,.png,.gif,.pdf">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-12 mb-3 text-center">
                    <div class="border p-3 rounded h-100 d-flex flex-column align-items-center justify-content-center">
                        <img alt="Banner" src="<?php echo isset($images)?base_url().$images: base_url('uploads/default_img.png');?>" class="img-fluid rounded mb-3 shadow-sm" style="max-height: 200px; object-fit: contain;" id="near_miss_att2">
                        <div class="mt-2 w-100">
                            <label class="control-label fw-bold mb-2 d-block">Upload Before Proof 2:</label> 
                            <input class="form-control" type="file" id="test2" img-id="near_miss_att2" name="file_2" onchange="readURL1hhh(this); validateFileType(this);" accept=".jpg,.jpeg,.png,.gif,.pdf">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   </div>
</div>


					</div>
					<!--end::Modal body-->
					
					<!--begin::Modal footer-->
					<div class="modal-footer flex-center">
							<!--begin::Button-->
							<button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
							<!--end::Button-->
							
							<!--begin::Button-->
							<button type="submit" id="ajax_click" data-ajax-add-url="<?=$ajax_url?>" class="btn btn-primary">
								<span class="indicator-label">Submit</span>
								<span class="indicator-progress">Please wait... 
								<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
							</button>
							<!--end::Button-->
							
						</div>
						<!--end::Modal footer-->
						
				</div>
				<!--end::Modal content-->
			</div>
			<!--end::Modal dialog-->
        </form>
        </div>
		<!--end::Modal - Create App-->


<?php 				$this->endSection();?>
	

<?php 					$this->section("javascript_section");?>
<script>
//$("#kt_datepicker_1").flatpickr();
//$.ajax()
$(document).ready(function () {
    // Clear custom fields when modal is closed
    $("#<?=$button_id?>").on('hidden.bs.modal', function () {
        // Reset all native inputs including file selections
        $("#<?=$form_id?>_form")[0].reset();
        
        $("#<?=$form_id?>_form").find('#near_miss_att').attr('src', '').show();
        $("#<?=$form_id?>_form").find('#near_miss_att2').attr('src', '').show();
        $("#<?=$form_id?>_form").find('.pdf-preview').remove();
        $("#<?=$form_id?>_form").find('#images').val('');
        
        $("#<?=$form_id?>_form").find('#fuc_occ_table').hide();
        $("#<?=$form_id?>_form").find('#future_occurrence_data').empty();
        $("#<?=$form_id?>_form").find('#future_occurrence').val('');
        
        // Reset divs
        $("#report_for_div").show();
        $("#public_reporter_div").hide();
        $("#self_radio").prop("checked", true);
        $("#other_engineer_div").hide();
        $("#other_engineer_id").val("");
        
        // Reset the dynamic dropdown displays
        setTimeout(function() {
            set_data_of_neamiss();
        }, 100);
        
        if (validator) {
            validator.resetForm(true);
        }
    });
});

const form = document.getElementById('<?=$form_id?>_form');
    var validation_object  = {
        fields: {
            'near_miss_category_text': {
                validators: {
                    notEmpty: {
                        message: 'Category Name is required'
                    }
                }
            }
        }
    };
    var validator = FormValidation.formValidation(form,validation_object);

        $("#ajax_click").on("click", function (e) {
            e.preventDefault();
            var button = $(this);
            var url = $(this).attr("data-ajax-url");

        if (!url) {
            url = $(this).attr("data-ajax-add-url");
        }
        
        // Native HTML5 Validation Check
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                if ($("#other_radio").is(":checked") && (!$("#other_engineer_id").val() || $("#other_engineer_id").val() === "")) {
                    toastr.warning("Please select an Other Engineer.");
                    return;
                }
                // removed auto-append logic that caused duplication when edit inputs were filled
                // var data_to_send =$("#<?=$form_id?>_form").serializeArray();
                //     var formData = {};
                //     $.each(data_to_send, function(i, field){
                //         if(field.value.trim() != ""){
                //           formData[field.name] = field.value;
                //         }
                //     });
                    
                                    var formData = new FormData($("#<?=$form_id?>_form")[0]);


                //ajax_call(url,data_to_send,button,success_function)
                    ajax_call_img(url,formData,button,function(responce){
                        try{
                            responce = JSON.parse(responce);
                            if(responce.status==1){
                                    toastr.success(responce.message);
                                    form.reset();
                                    $("#<?=$form_id?>").modal("hide");
                                    reload_data_table();
                            }else{
                                    toastr.warning(responce.message);
                                }
                            }catch(error){
                                toastr.error(error);
                            }
                    });
                }else{
                    // not validate 
                }
        });
        }
    
});
function edit_id(obj,id){
    var url = $(obj).attr("data-ajax-url");
    var formData={"id":id};
    ajax_call(url,formData,$(obj),function(responce){
                        try{
                            responce = JSON.parse(responce);
                            if(responce.status==1){
                                    toastr.success(responce.message);
                                    //form.reset();
                                    // to update value 
                                        try{
                                        if (responce.data.source === 'public_qr') {
                                            $("#report_for_div").hide();
                                            $("#other_engineer_div").hide();
                                            $("#public_reporter_div").show();
                                            
                                            $("#pub_name").text(responce.data.public_reporter_name || 'N/A');
                                            $("#pub_emp_code").text(responce.data.public_reporter_emp_code || 'N/A');
                                            $("#pub_email").text(responce.data.public_reporter_email || 'N/A');
                                            $("#pub_mobile").text(responce.data.public_reporter_mobile || 'N/A');
                                            
                                            // uncheck radio and empty engineer select
                                            $("#self_radio").prop("checked", false);
                                            $("#other_radio").prop("checked", false);
                                            $("#other_engineer_id").val("");
                                        } else {
                                            $("#report_for_div").show();
                                            $("#public_reporter_div").hide();
                                            
                                            if (responce.data.self_other !== null && responce.data.self_other !== undefined) {
                                                if (responce.data.self_other == "1") {
                                                    $("#other_radio").prop("checked", true);
                                                    $("#other_engineer_div").show();
                                                    $("#other_engineer_id").val(responce.data.other_engineer_id);
                                                } else {
                                                    $("#self_radio").prop("checked", true);
                                                    $("#other_engineer_div").hide();
                                                    $("#other_engineer_id").val("");
                                                }
                                            } else {
                                                $("#self_radio").prop("checked", true);
                                                $("#other_engineer_div").hide();
                                                $("#other_engineer_id").val("");
                                            }
                                        }
                                    }catch(e){ console.error(e); }
                                    
                                    var fcm = "";    $("#<?=$form_id?>").find("#near_miss_category_text").val(responce.data.near_miss_category_text);
                                        // changes on 8/10/25 by darsh: map project_name to corrected field id
                                        $("#<?=$form_id?>").find("#project_name").val(responce.data.project_name || responce.data.customer_proj_name);
                                        $("#<?=$form_id?>").find("#job_no").val(responce.data.job_no);
                                        // changes on 8/10/25 by darsh: update GPS/manual selectors to match corrected ids
                                        $("#<?=$form_id?>").find("#location").val(responce.data.location || '').trigger('change');
                                        $("#<?=$form_id?>").find("#gps_location").val(responce.data.gps_location || responce.data.exact_location || '');
                                        $("#<?=$form_id?>").find("#manual_location").val(responce.data.manual_location || '');
                                        $("#<?=$form_id?>").find("#near_miss_date").val(responce.data.near_miss_date);
                                        $("#<?=$form_id?>").find("#near_miss_time").val(responce.data.near_miss_time);
                                        $("#<?=$form_id?>").find("#immediate_action").val(responce.data.immediate_action);
                                        $("#<?=$form_id?>").find("#what_could_have_happened").val(responce.data.what_could_have_happened);
                                        $("#<?=$form_id?>").find("#mode_of_work").val(responce.data.mode_of_work);
                                        $("#<?=$form_id?>").find("#involved_equipment_name").val(responce.data.involved_equipment_name);
                                        $("#<?=$form_id?>").find("#description_of_occurred").val(responce.data.description_of_occurred);
                                        $("#<?=$form_id?>").find("#near_miss_priority").val(responce.data.near_miss_priority);
                                        $("#<?=$form_id?>").find("#priority_comment").val(responce.data.priority_comment);
                                         $("#<?=$form_id?>").find("#total_hours").val(responce.data.total_hours);
                                         $("#<?=$form_id?>").find("#total_cost").val(responce.data.total_cost);
                                         
                                         // Clear old images before populating new ones
                                         $("#<?=$form_id?>").find('#near_miss_att').attr('src', '');
                                         $("#<?=$form_id?>").find('#near_miss_att2').attr('src', '');
                                         $("#<?=$form_id?>").find('#images').val('');
                                         
                                         // change on 29/09/25 by darsh: set image previews from existing CSV list
                                         if(responce.data.images){
                                             $("#<?=$form_id?>").find('#images').val(responce.data.images);
                                             var imgs = (responce.data.images+'').split(',');
                                             if(imgs[0]){ 
                                                if(imgs[0].toLowerCase().endsWith('.pdf')){
                                                    $("#<?=$form_id?>").find('#near_miss_att').hide();
                                                    if($("#pdf_prev_near_miss_att").length === 0){
                                                        $("#<?=$form_id?>").find('#near_miss_att').after('<div class="pdf-preview text-danger fw-bold my-2" id="pdf_prev_near_miss_att"><i class="fa fa-file-pdf fa-2x"></i> PDF Attached</div>');
                                                    }
                                                } else {
                                                    $("#<?=$form_id?>").find('#near_miss_att').show();
                                                    $("#<?=$form_id?>").find('#pdf_prev_near_miss_att').remove();
                                                    $("#<?=$form_id?>").find('#near_miss_att').attr('src', imgs[0]); 
                                                }
                                             }
                                             if(imgs[1]){ 
                                                if(imgs[1].toLowerCase().endsWith('.pdf')){
                                                    $("#<?=$form_id?>").find('#near_miss_att2').hide();
                                                    if($("#pdf_prev_near_miss_att2").length === 0){
                                                        $("#<?=$form_id?>").find('#near_miss_att2').after('<div class="pdf-preview text-danger fw-bold my-2" id="pdf_prev_near_miss_att2"><i class="fa fa-file-pdf fa-2x"></i> PDF Attached</div>');
                                                    }
                                                } else {
                                                    $("#<?=$form_id?>").find('#near_miss_att2').show();
                                                    $("#<?=$form_id?>").find('#pdf_prev_near_miss_att2').remove();
                                                    $("#<?=$form_id?>").find('#near_miss_att2').attr('src', imgs[1]); 
                                                }
                                             }
                                         }
                                         $("#<?=$form_id?>").find("#incident_person_name").val(responce.data.incident_person_name);
                                         $("#<?=$form_id?>").find("#incident_person_designation").val(responce.data.incident_person_designation);
                                         $("#<?=$form_id?>").find("#incident_person_age").val(responce.data.incident_person_age);
                                         $("#<?=$form_id?>").find("#incident_person_company").val(responce.data.incident_person_company);
                                         $("#<?=$form_id?>").find("#incident_person_sift").val(responce.data.incident_person_sift);
                                         $("#<?=$form_id?>").find("#nature_of_damage").val(responce.data.nature_of_damage);
                                         $("#<?=$form_id?>").find("#body_part_affect").val(responce.data.body_part_affect);
                                         $("#<?=$form_id?>").find("#body_part_affect_details").val(responce.data.body_part_affect_details);
                                         $("#<?=$form_id?>").find("#causes_of_incident").val(responce.data.causes_of_incident);
                                         
                                         // Trigger change to display the correct div
                                         $("#<?=$form_id?>").find("#near_miss_category_text").trigger('change');
                                         // change on 29/09/25 by darsh: prefill Future Occurrence JSON table into visible table and hidden field
                                         if(responce.data.future_occurrence){
                                             $("#<?=$form_id?>").find('#future_occurrence').val(responce.data.future_occurrence);
                                             render_future_occurrence();
                                             
                                             var json = {};
                                             try{ json = JSON.parse(responce.data.future_occurrence) || {}; }catch(e){}
                                             var arr = json.future_occurrence || [];
                                             // Intentionally not prefilling the inputs to avoid confusion and duplication
                                         }
                                                           
                                    $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-url",$("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-add-url")+"/"+id);
                                    $("#<?=$form_id?>").modal("show");
                            }else{
                                    toastr.warning(responce.message);
                                }
                            }catch(error){
                                toastr.error(error);
                            }
                    });
}
function delete_row(obj,id){
    Swal.fire({
              title: 'Do you want delete?',
              //showDenyButton: true,
              showCancelButton: true,
              confirmButtonText: 'Save',
              //denyButtonText: `Don't delete`,
            }).then((result) => {
              /* Read more about isConfirmed, isDenied below */
              if (result.isConfirmed) {
                //Swal.fire('Saved!', '', 'success')
                url_call_ajax($(obj).attr("data-ajax-url"),$(obj));
              } 
            //   else if (result.isDenied) {
            //     Swal.fire('Changes are not saved', '', 'info')
            //   }
            });

}

</script>
<script>
    function validatation(){
    var valid=0;
    $('form').find('[required]').each(function(i){
        if($(this).val()==" " ||  $(this).val()==""){
            valid++;
            $(this).focus();
            return false;
        }
    })
    if(valid==0)
        return true;
   }
   
   
   function redirect_method(){
     window.location="<?php echo isset($redirect_url)?$redirect_url:''?>";
   }
var table_body_templet='<tr><td>{{td1}}</td><td>{{td2}}</td><td>{{td3}}</td><td>{{td4}}</td></tr>';
    function add_future_occurance(){
        var form=$("#<?=$form_id?>_form");
        var cause=form.find("#fut_cause");
        var action=form.find("#fut_corrective_action");
        var person=form.find("#fut_person_responsible");
        var date=form.find("#fut_target_date");

        if($.trim(cause.val())==""){
            toastr.error("Enter Root Cause");
            cause.focus();
            return;
        }
        if($.trim(action.val())==""){
            toastr.error("Enter Corrective Action");
            action.focus();
            return;
        }
        if($.trim(person.val())==""){
            toastr.error("Enter Person Responsible");
            person.focus();
            return;
        }
        if($.trim(date.val())==""){
            toastr.error("Enter Target Date");
            date.focus();
            return;
        }

        var hidden=form.find("#future_occurrence");
        var future={ future_occurrence:[] };
        try{
            if(hidden.length && hidden.val() && hidden.val()!="undefined"){
                future=JSON.parse(hidden.val());
            }
        }catch(e){
            future={ future_occurrence:[] };
        }

        future.future_occurrence.push({
            causes:cause.val(),
            corrective_action:action.val(),
            person_responsible:person.val(),
            target_date:date.val()
        });

        hidden.val(JSON.stringify(future));
        
        cause.val("");
        action.val("");
        person.val("");
        date.val("");

        toastr.success("Future Occurrence Added Successfully");
        render_future_occurrence();
    }

    function remove_future_occurance(index) {
        var form = $("#<?=$form_id?>_form");
        var hidden = form.find("#future_occurrence");
        var future = { future_occurrence: [] };
        if (hidden.length && hidden.val() && hidden.val() != "undefined" && hidden.val() != "") {
            try { future = JSON.parse(hidden.val()); } catch(e) {}
        }
        if(future.future_occurrence && future.future_occurrence.length > index) {
            future.future_occurrence.splice(index, 1);
            hidden.val(JSON.stringify(future));
            render_future_occurrence();
            toastr.success("Row removed successfully");
        }
    }

    function render_future_occurrence() {
        var form = $("#<?=$form_id?>_form");
        var hidden = form.find("#future_occurrence");
        var tbody = form.find("#future_occurrence_data");
        var table = form.find("#fuc_occ_table");
        
        tbody.empty();
        
        var future = { future_occurrence: [] };
        if (hidden.length && hidden.val() && hidden.val() != "undefined" && hidden.val() != "") {
            try { future = JSON.parse(hidden.val()); } catch(e) {}
        }
        
        if (future.future_occurrence && future.future_occurrence.length > 0) {
            table.show();
            $.each(future.future_occurrence, function(index, item) {
                tbody.append(
                    "<tr>"
                    + "<td>" + item.causes + "</td>"
                    + "<td>" + item.corrective_action + "</td>"
                    + "<td>" + item.person_responsible + "</td>"
                    + "<td>" + item.target_date + "</td>"
                    + "<td><button class='btn btn-sm btn-danger' onclick='event.preventDefault(); remove_future_occurance(" + index + ");'><i class='fa fa-trash'></i></button></td>"
                    + "</tr>"
                );
            });
        } else {
            table.hide();
        }
    }


   
   var select=$("#near_miss_category_text");
   var display_div=$("#conditional_data");
   var temp_holder='';

    function set_data_of_neamiss() {
    var category = $("#near_miss_category_text").val();

    $("#near_miss").hide();
    $("#near_miss").find("input, select, textarea").prop("disabled", true);
    
    $("#Injury_Details").hide();
    $("#Injury_Details").find("input, select, textarea").prop("disabled", true);

    if (category === "Near Miss") {
        $("#near_miss").show();
        $("#near_miss").find("input, select, textarea").prop("disabled", false);
    } else if (category === "Minor Injury" || category === "Major Injury") {
        $("#Injury_Details").show();
        $("#Injury_Details").find("input, select, textarea").prop("disabled", false);
    }
}

$(document).ready(function () {
    set_data_of_neamiss();

    $("#near_miss_category_text").on("change", function () {
        set_data_of_neamiss();
    });
});
       
    function toggleOtherEngineer() {
        if ($("#other_radio").is(":checked")) {
            $("#other_engineer_div").show();
        } else {
            $("#other_engineer_div").hide();
            $("#other_engineer_id").val("");
        }
    }
   

   
   $(document).on("input", ".numeric", function() {
    this.value = this.value.replace(/\D/g,'');
});

$(document).ready(function() {
    render_future_occurrence();
});
// File type validation function
function validateFileType(input) {
    const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const extension = file.name.split('.').pop().toLowerCase();
        
        // Check file type
        if (!allowedTypes.includes(extension)) {
            alert('Invalid file type. Only images (JPG, PNG, GIF) and PDFs are allowed.');
            input.value = '';
            return false;
        }
        
        // Check file size
        if (file.size > maxSize) {
            alert('File size too large. Maximum allowed size is 10MB.');
            input.value = '';
            return false;
        }
    }
    return true;
}

function readURL1hhh(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var extension = file.name.split('.').pop().toLowerCase();
        var targetImgId = $(input).attr("img-id");
        
        if (extension === 'pdf') {
            $('#' + targetImgId).hide();
            $('#pdf_prev_' + targetImgId).remove();
            $('#' + targetImgId).after('<div class="pdf-preview text-danger fw-bold my-2" id="pdf_prev_'+targetImgId+'"><i class="fa fa-file-pdf fa-2x"></i> '+file.name+'</div>');
        } else {
            $('#pdf_prev_' + targetImgId).remove();
            $('#' + targetImgId).show();
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#' + targetImgId).attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    }
}

</script>
<?php 				$this->endSection();?>

