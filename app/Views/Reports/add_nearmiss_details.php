
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
		<div class="modal fade" id="<?=$button_id?>" tabindex="-1" aria-hidden="true">
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
   <!--<div class="col-md-12">-->
   <!--   <h3 class="card-tite">NearMiss</h3>-->
   <!--</div>-->
   <div class=" ">
      <div class="card">
         <div class="card-body">
            <form class="POST_AJAX" action="<?php echo isset($action)?$action:""?>" method="post" >
               <div class="row">
                  <div class="col-md-12">
                     <div class ="row">
                         
                        <?php if($_SESSION['role'] != 'Engineer'){?>
                         
                        <div class="col-md-6">
                           <label class="control-label"><b>User</b> </label>
                           <!-- change on 29/09/25 by darsh: add name attribute and default selection -->
                           <select  class="form-control" name="engineer" id="engineer" required onchange='$("#user_id").val(this.value);$("#fcm_id").val($("#engineer option:selected").attr("fcm"));'>
                              <option value=" ">Select</option>
                               <?php if($_SESSION['role'] == 'Higher authority'){?>
                                 <option value="<?=$_SESSION['login_id'] ?>"  <?= isset($user_id) && $user_id==$_SESSION['login_id']?" selected='selected'":''?> fcm="<?=$_SESSION['fcm_id']?>">Self</option>
                             <?php }?>
                              <?php foreach($engineer_list as $engineer){?>
                                 <option value="<?=$engineer['user_id'] ?>"  <?= isset($user_id) && $user_id==$engineer['user_id']?" selected='selected'":''?> fcm="<?=$engineer['fcm_id'] ?>" ><?=$engineer['user_name'] ?></option>
                             <?php }?>
                           </select>
                        </div>
                        
                        <?php } ?>
                        <div class="col-md-6">
                           <label class="control-label"><b>Category of Incidence</b> </label>
                           <select name="near_miss_category_text" class="form-control" id="near_miss_category_text">
                              <option value="Near Miss">Near Miss</option>
                              <option value="Minor Injury">Minor Injury</option>
                              <option value="Major Injury">Major Injury</option>
                              <!--"<?php echo isset($near_miss_category_text)?$near_miss_category_text:""?>" required>-->
                           </select>
                        </div>
                     </div>
                     <div class ="row">
                        <div class="col-md-6">
                           <label class="control-label"><b>Customer/Project Name</b></label>
                           <input type="text" name="project_name" id="project_name" class="form-control" 
                              value="<?php echo isset($project_name)?$project_name:""?>" required>
                           <!-- changes on 8/10/25 by darsh: rename name/id to match DB column project_name -->
                        </div>
                        <div class="col-md-6">
                           <label class="control-label"><b>Job no</b></label>
                           <input type="text" name="job_no" id="job_no" class="form-control" 
                              value="<?php echo isset($job_no)?$job_no:""?>" required>
                        </div>
                        <div class="col-md-12">
                           <label class="control-label"><b>Location</b></label>
                           <input type="text" name="location" id="location" class="form-control" 
                              value="<?php echo isset($location)?$location:""?>" >
                              <div id="result" class="col-md-6"></div>
                        </div>
                        <div class="col-md-6">
                           <label class="control-label"><b>Gps location</b></label>
                           <input type="text" name="gps_location" id="gps_location" class="form-control" 
                              value="<?php echo isset($gps_location)?$gps_location:""?>" >
                           <!-- changes on 8/10/25 by darsh: rename to gps_location to match DB column -->
                        </div>
                        <div class="col-md-6">
                           <label class="control-label"><b>Exact Location</b></label>
                           <input type="text" name="manual_location" id="manual_location" class="form-control" 
                              value="<?php echo isset($manual_location)?$manual_location:""?>" required>
                           <!-- changes on 8/10/25 by darsh: fix duplicate name/id; map to manual_location DB column -->
                        </div>
                        <div class="col-md-6">
                           <label class="control-label"><b>Date</b></label>
                           <input type="date" name="near_miss_date" id="near_miss_date" class="form-control" 
                              value="<?php echo isset($near_miss_date)?$near_miss_date:date("Y-m-d")?>" required>
                        </div>
                        <div class="col-md-6">
                           <label class="control-label"><b>Time</b></label>
                           <input type="time" name="near_miss_time" id="near_miss_time" class="form-control" 
                              value="<?php echo isset($near_miss_time)?$near_miss_time:""?>" required>
                        </div>
                     </div>
                     <div class ="row" id="conditional_data">
                         <div id="near_miss" style="display:none">
</div>
<div id="Minor_Injury" style="display:none">
   <div class="col-md-6">
      <label class="control-label"><b>Name of person</b></label>
      <input type="text" name="incident_person_name" id="incident_person_name" class="form-control" 
         value="<?php echo isset($incident_person_name)?$incident_person_name:""?>" required>
   </div>
    <div class="col-md-6">
      <label class="control-label"><b>Designation</b></label>
      <input type="text" name="incident_person_designation" id="incident_person_designation" class="form-control" 
         value="<?php echo isset($incident_person_designation)?$incident_person_designation:""?>" required>
   </div>
   <div class="col-md-6">
      <label class="control-label"><b>Age</b></label>
      <input type="text" name="incident_person_age" id="incident_person_age" class="form-control numeric" 
         value="<?php echo isset($incident_person_age)?$incident_person_age:""?>" required>
   </div>
   <div class="col-md-6">
      <label class="control-label"><b>Company</b></label>
      <input type="text" name="incident_person_company" id="incident_person_company" class="form-control" 
         value="<?php echo isset($incident_person_company)?$incident_person_company:""?>" required>
   </div>
      <div class="col-md-6">
      <label class="control-label"><b>Shift</b></label>
      <input type="text" name="incident_person_sift" id="incident_person_sift" class="form-control" 
         value="<?php echo isset($incident_person_sift)?$incident_person_sift:""?>" required>
   </div>
   <div class="col-md-6">
      <label class="control-label"><b>Damage Nature</b></label>
      <!--<input type="text" name="" class="form-control" -->
      <!--   value="<?php echo isset($nature_of_damage)?$nature_of_damage:""?>" required>-->
      <select name="nature_of_damage" id="nature_of_damage" class="form-control" id="nature_of_damage">
         <option value=" ">Nature Of Incident</option>
         <?php foreach($nature_incident as $nature){?>
         <option value="<?=$nature['incident_name'] ?>"  <?= isset($nature_of_damage) && $nature_of_damage==$nature['incident_name']?" selected='selected'":''?>><?=$nature['incident_name'] ?></option>
         <?php }?>
      </select>
   </div>
   <div class="col-md-6">
      <label class="control-label"><b>Body Part Affects</b></label>
      <!--<input type="text" name="" class="form-control" -->
      <!--   value="<?php echo isset($body_part_affect)?$body_part_affect:""?>" required>-->
      <select name="body_part_affect" class="form-control" id="body_part_affect">
         <option value="">Body Part Affects</option>
         <?php foreach($body_part as $part){?>
         <option value="<?=$part['body_part_name'] ?>"  <?= isset($body_part_affect) && $body_part_affect==$part['body_part_name']?" selected='selected'":''?>><?=$part['body_part_name'] ?></option>
         <?php }?>
      </select>
   </div>
</div> <!-- Ends Minor_Injury -->

   <div class="col-md-12" id="future_occuance_div">
      <label class="control-label"><b>Future Occurence</b></label>
      <table class="table table-hover align-middle" style="display:none" id="fuc_occ_table"><thead class="thead-light"><tr><td>Causes</td><td>Corrective Action</td><td>Person Responsible</td><td>Target Date</td></tr></thead><tbody id="future_occurrence_data"></tbody></table>
      
      <input
    type="hidden"
    id="future_occurrence"
    name="future_occurrence"
    value="<?=
        !empty($future_occurrence)
            ? $future_occurrence
            : '{"future_occurrence":[]}'
    ?>">

      <div class="row mt-3">
         <div class="col-md-6">
            <label class="control-label"><b>Root Cause</b></label>
            <input type="text" id="fut_cause" class="form-control" value="" >
         </div>
         <div class="col-md-6">
            <label class="control-label"><b>Corrective Action</b></label>
            <input type="text" class="form-control" id="fut_corrective_action" value="" >
         </div>
         <div class="col-md-6">
            <label class="control-label"><b>Person Responsible</b></label>
            <input type="text" class="form-control" id="fut_person_responsible" value="" >
         </div>
         <div class="col-md-6">
            <label class="control-label"><b>Date</b></label>
            <input type="date" class="form-control" id="fut_target_date" value="" >
         </div>
         <div class="col-md-12 mt-2">
            <button class="btn btn-success" onclick="event.preventDefault(); add_future_occurance();" style="float: right;"> Add</button>
         </div>
      </div> 
   </div>
                     </div>
                     <div class="row">
                        <div class="col-md-6">
                           <label class="control-label"><b>Immidiate Action</b></label>
                           <input type="text" name="immediate_action" id="immediate_action" class="form-control" 
                              value="<?php echo isset($immediate_action)?$immediate_action:""?>" required>
                        </div>
                        <div class="col-md-6">
                           <label class="control-label"><b>What couldHappened</b></label>
                           <input type="text" name="what_could_have_happened" id="what_could_have_happened" class="form-control" 
                              value="<?php echo isset($what_could_have_happened)?$what_could_have_happened:""?>" required>
                        </div>
                        <div class="col-md-6">
                           <label class="control-label"><b>Task Activity</b> </label>
                           <select name="mode_of_work" class="form-control" id="mode_of_work">
                              <option value=" ">Task Activity</option>
                              <?php foreach($task_activity as $task) { ?>
                              <option value="<?=$task['category_name']?>" <?= isset($mode_of_work) && $mode_of_work==$task['category_name']?" selected='selected'":''?>><?=$task['category_name']?></option>
                              <?php } ?>
                           </select>
                        </div>
                        <div class="col-md-6">
                           <label class="control-label"><b>Involved Equipment Name</b></label>
                           <input type="text" name="involved_equipment_name" id="involved_equipment_name" class="form-control" 
                              value="<?php echo isset($involved_equipment_name)?$involved_equipment_name:""?>" required>
                        </div>
                        <div class="col-md-6">
                           <label class="control-label"><b>Occured Description</b></label>
                           <input type="text" name="description_of_occurred" id="description_of_occurred" class="form-control" 
                              value="<?php echo isset($description_of_occurred)?$description_of_occurred:""?>" required>
                        </div>
                     </div>
                     <div class ="row">
                        <div class="col-md-6">
                           <label class="control-label"><b>Priority</b></label><br>
                           <label class="radio-inline">
                           <input type="radio" name="near_miss_priority" id="near_miss_priority" value="0" <?= (isset($near_miss_priority) && $near_miss_priority == '0') ? 'checked' : (!isset($near_miss_priority) ? 'checked' : '') ?> required>Low
                           </label>
                           <label class="radio-inline">
                           <input type="radio" name="near_miss_priority" value="1" <?= (isset($near_miss_priority) && $near_miss_priority == '0') ? 'checked' : (!isset($near_miss_priority) ? 'checked' : '') ?> required>High
                           </label>
                        </div>
                        <div class="col-md-6">
                           <label class="control-label"><b>Priority Comment</b></label>
                           <input type="text" name="priority_comment" id="priority_comment" class="form-control" 
                              value="<?php echo isset($priority_comment)?$priority_comment:""?>" >
                        </div>
                     </div>
                     <div class ="row">
                        <div class="col-md-6">
                           <label class="control-label"><b>Lost Hours</b></label>
                           <input type="text" name="total_hours" id="total_hours" class="form-control numeric" 
                              value="<?php echo isset($total_hours)?$total_hours:""?>" >
                        </div>
                        <div class="col-md-6">
                           <label class="control-label"><b>Lost Cost</b></label>
                           <input type="text" name="total_cost" id="total_cost" class="form-control numeric" 
                              value="<?php echo isset($total_cost)?$total_cost:""?>" >
                            <input type="hidden" name="user_id" class="form-control" 
                              value="<?php echo isset($user_id)?$user_id:$_SESSION['login_id']?>" id="user_id">
                             <input type="hidden" name="fcm_id" class="form-control" 
                              value="<?php echo isset($_SESSION['fcm_id'])?$_SESSION['fcm_id']:''?>" id="fcm_id">
                        </div>
                     </div>
                     <div class ="row">
                        <div class="col-md-6">
                           <input name="images" id="images"  value="" hidden=""> <img
                              alt="Logo" 
                              src="<?php echo isset($images)?base_url().$images: base_url('uploads/default_img.png');?>"
                              style="height: 200px; width: 200px;" id="near_miss_att">
                           <br><br>
                           <label for="images">Near Miss Banner :</label> 
                           <input  type="file" id="test" img-id="near_miss_att" name="file_1"
                              onchange="readURL1hhh(this); validateFileType(this);" accept=".jpg,.jpeg,.png,.gif,.pdf">
                        </div>
                         <div class="col-md-6">
                           <input name="images" id="images"  value="" hidden=""> <img
                              alt="Logo" 
                              src="<?php echo isset($images)?base_url().$images: base_url('uploads/default_img.png');?>"
                              style="height: 200px; width: 200px;" id="near_miss_att2">
                           <br><br>
                           <label for="images">Near Miss Banner :</label> 
                           <input  type="file" id="test2" img-id="near_miss_att2" name="file_2"
                              onchange="readURL1hhh(this); validateFileType(this);" accept=".jpg,.jpeg,.png,.gif,.pdf">
                        </div>
                     </div>
                  </div>
               </div>

    <!--           	<div class="col-md-12">-->
				<!--	<button ajax_events="true" value="save_html_data" data-url="<?php echo isset($action)?$action:""?>" type="submit" class="btn btn-purple waves-effect waves-light" style="margin-top: 15px; float: right;" onclick="event.preventDefault(); ajaxLoader(this,$(this).closest('form'));">Submit</button>-->
				<!--</div>-->
            </form>
         </div>
      </div>
</div>
</div>
<!--</div>-->


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
const form = document.getElementById('<?=$form_id?>_form');
var validation_object  = {
        fields: {
            'category_name': {
                validators: {
                    notEmpty: {
                        message: 'Category Name is required'
                    }
                }
            }
        },

        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap: new FormValidation.plugins.Bootstrap5({
                rowSelector: '.fv-row',
                eleInvalidClass: '',
                eleValidClass: ''
            })
        }
    };
var validator = FormValidation.formValidation(form,validation_object);

        $("#ajax_click").on("click", function () {
            var button = $(this);
            var url = $(this).attr("data-ajax-url");

        if (!url) {
            url = $(this).attr("data-ajax-add-url");
        }
        if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                // changes on 8/10/25 by darsh: ensure last Future Occurrence row is captured even if Add not clicked
                try{
                    var cause = $("#<?=$form_id?>_form").find('#fut_cause').val();
                    var action = $("#<?=$form_id?>_form").find('#fut_corrective_action').val();
                    var responsible = $("#<?=$form_id?>_form").find('#fut_person_responsible').val();
                    var date = $("#<?=$form_id?>_form").find('#fut_target_date').val();
                    var futureHidden = $("#<?=$form_id?>_form").find('#future_occurrence');
                    if((cause||action||responsible||date)){
                        var payload = { future_occurrence: [] };
                        if(futureHidden.val()){
                            try{ var parsed = JSON.parse(futureHidden.val()); if(parsed && parsed.future_occurrence){ payload.future_occurrence = parsed.future_occurrence; } }catch(e){}
                        }
                        payload.future_occurrence.push({
                            causes: cause || '',
                            corrective_action: action || '',
                            person_responsible: responsible || '',
                            target_date: date || ''
                        });
                        futureHidden.val(JSON.stringify(payload));
                    }
                }catch(e){}
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
                                        // change on 29/09/25 by darsh: set user dropdown from user_id and update hidden fields
                                        if($("#<?=$form_id?>").find("#engineer").length){
                                            $("#<?=$form_id?>").find("#engineer").val(responce.data.user_id);
                                            $("#<?=$form_id?>").find("#user_id").val(responce.data.user_id);
                                            var fcm = $("#<?=$form_id?>").find("#engineer option:selected").attr('fcm') || '';
                                            $("#<?=$form_id?>").find("#fcm_id").val(fcm);
                                        }
                                        $("#<?=$form_id?>").find("#near_miss_category_text").val(responce.data.near_miss_category_text);
                                        // changes on 8/10/25 by darsh: map project_name to corrected field id
                                        $("#<?=$form_id?>").find("#project_name").val(responce.data.project_name || responce.data.customer_proj_name);
                                        $("#<?=$form_id?>").find("#job_no").val(responce.data.job_no);
                                        // changes on 8/10/25 by darsh: update GPS/manual selectors to match corrected ids
                                        $("#<?=$form_id?>").find("#location").val(responce.data.location || '');
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
                                         // change on 29/09/25 by darsh: set image previews from existing CSV list
                                         if(responce.data.images){
                                             var imgs = (responce.data.images+'').split(',');
                                             if(imgs[0]){ $("#<?=$form_id?>").find('#near_miss_att').attr('src', imgs[0]); }
                                             if(imgs[1]){ $("#<?=$form_id?>").find('#near_miss_att2').attr('src', imgs[1]); }
                                         }
                                         $("#<?=$form_id?>").find("#incident_person_name").val(responce.data.incident_person_name);
                                         $("#<?=$form_id?>").find("#incident_person_designation").val(responce.data.incident_person_designation);
                                         $("#<?=$form_id?>").find("#incident_person_age").val(responce.data.incident_person_age);
                                         $("#<?=$form_id?>").find("#incident_person_company").val(responce.data.incident_person_company);
                                         $("#<?=$form_id?>").find("#incident_person_sift").val(responce.data.incident_person_sift);
                                         $("#<?=$form_id?>").find("#nature_of_damage").val(responce.data.nature_of_damage);
                                         $("#<?=$form_id?>").find("#body_part_affect").val(responce.data.body_part_affect);
                                         // change on 29/09/25 by darsh: prefill Future Occurrence JSON table into visible table and hidden field
                                         if(responce.data.future_occurrence){
                                             var json = {};
                                             try{ json = JSON.parse(responce.data.future_occurrence) || {}; }catch(e){}
                                             var arr = json.future_occurrence || [];
                                             var table = $("#<?=$form_id?>").find('#fuc_occ_table');
                                             var tbody = $("#<?=$form_id?>").find('#future_occurrence_data');
                                             if(arr.length){ table.show(); tbody.empty(); }
                                             arr.forEach(function(it){
                                                 var row = '<tr><td>'+ (it.causes||'') +'</td><td>'+ (it.corrective_action||'') +'</td><td>'+ (it.person_responsible||'') +'</td><td>'+ (it.target_date||'') +'</td></tr>';
                                                 tbody.append(row);
                                             });
                                             $("#<?=$form_id?>").find('#future_occurrence').val(responce.data.future_occurrence);
                                             // change on 29/09/25 by darsh: prefill the input fields with the first row for quick edit
                                             if(arr.length){
                                                 $("#<?=$form_id?>").find('#fut_cause').val(arr[0].causes || '');
                                                 $("#<?=$form_id?>").find('#fut_corrective_action').val(arr[0].corrective_action || '');
                                                 $("#<?=$form_id?>").find('#fut_person_responsible').val(arr[0].person_responsible || '');
                                                 $("#<?=$form_id?>").find('#fut_target_date').val(arr[0].target_date || '');
                                             }
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

    var future={
        future_occurrence:[]
    };

    try{

        if(hidden.length && hidden.val() && hidden.val()!="undefined"){

            future=JSON.parse(hidden.val());

        }

    }catch(e){

        future={
            future_occurrence:[]
        };

    }

    future.future_occurrence.push({

        causes:cause.val(),

        corrective_action:action.val(),

        person_responsible:person.val(),

        target_date:date.val()

    });

    hidden.val(JSON.stringify(future));

    form.find("#fuc_occ_table").show();

    form.find("#future_occurrence_data").append(

        "<tr>"

        +"<td>"+cause.val()+"</td>"

        +"<td>"+action.val()+"</td>"

        +"<td>"+person.val()+"</td>"

        +"<td>"+date.val()+"</td>"

        +"</tr>"

    );

    cause.val("");

    action.val("");

    person.val("");

    date.val("");

    toastr.success("Future Occurrence Added Successfully");

}

   
   var select=$("#near_miss_category_text");
   var display_div=$("#conditional_data");
   var temp_holder='';

var minnor=$("#Minor_Injury").html();
$("#Minor_Injury").html('');

$(document).ready(function () {

    function set_data_of_neamiss() {

        var category = $("#near_miss_category_text").val();

        $("#near_miss").hide();
        $("#Minor_Injury").hide();

        if(category=="Near Miss"){
            $("#near_miss").show();
        }
        else{
            $("#Minor_Injury").show();
        }

    }

    set_data_of_neamiss();

    $("#near_miss_category_text").change(function(){

        set_data_of_neamiss();

    });

});
       
   
    select.on('change' ,function(){
        set_data_of_neamiss()
    });
   
   $(document).on("input", ".numeric", function() {
    this.value = this.value.replace(/\D/g,'');
});

$(document).ready(function() {
            $('#location').on('keyup', function() {
                let query = $(this).val();
               
                if (query.length > 1) {
                    $.ajax({
                        url: "<?= base_url('Reports/Lmra_reports/search') ?>",
                        method: "GET",
                        data: { query: query },
                        dataType: "json",
                        success: function(response) {
                            let output = '';
                            if (response.length > 0) {
                                response.forEach(user => {
                                    output += `<div class="result-item">${user.location_name}</div>`;
                                });
                            } else {
                                output = '<div class="result-item">No results found</div>';
                            }
                            $('#result').html(output);
                        }
                    });
                } else {
                    $('#result').html('');
                }
            });
            $(document).on('click', '.result-item', function() {
                $('#location').val($(this).text());
                $('#result').html('');
            });
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
        var reader = new FileReader();
        var targetImgId = $(input).attr("img-id");
        
        reader.onload = function (e) {
            $('#' + targetImgId).attr('src', e.target.result);
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

</script>
<?php 				$this->endSection();?>

