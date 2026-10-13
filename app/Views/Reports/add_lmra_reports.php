
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
						<h2>LMRA Details</h2>
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
					<!--<div class="modal-body py-lg-10 px-lg-10">-->
     <!--               	<div class="scroll-y me-n7 pe-7" id="user" data-kt-scroll="false" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_new_address_header" data-kt-scroll-wrappers="#fees_head" data-kt-scroll-offset="300px" style="max-height: 273px;">-->
								<!--begin::Input group-->
					<!--			<div class="row mb-12">-->
									<!--begin::Col-->
					<!--				 <div class="col-md-6">-->
     <!--                                  <label class="control-label"><b>Project Name</b></label>-->
     <!--                                  <input type="text" name="customer_proj_name" class="form-control" -->
     <!--                                     value="<?php echo isset($lmra_project_name)?$lmra_project_name:" "?>" required>-->
     <!--                            
					<div class="model-body py-lg-10 px-lg-10"> -->
 <style>
      #result {
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #ccc;
            max-width: 100%;
            max-height: 200px;
            overflow-y: auto;
            margin-top: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
<!--   <h3 class="card-tite">LMRA</h3>-->
<!--</div>-->
<div class="">
<div class="card">
   <div class="card-body ">
      <form class="POST_AJAX" action="<?php echo isset($action)?$action:" "?>" method="post" >
         <div class ="row">
            <!-- change on 29/09/25 by darsh: user prefill select with session defaults -->
            <div class="col-md-6">
                <label class="control-label"><b>Assessment For</b> <span class="text-danger">*</span></label><br>
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="radio" name="self_other" id="self_radio" value="0" checked onchange="toggleOtherEngineer()">
                    <label class="form-check-label" for="self_radio">Self</label>
                </div>
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="radio" name="self_other" id="other_radio" value="1" onchange="toggleOtherEngineer()">
                    <label class="form-check-label" for="other_radio">Other Engineer</label>
                </div>
            </div>

            <div class="col-md-6" id="other_engineer_div" style="display: none;">
               <label class="control-label"><b>Other Engineer</b> <span class="text-danger">*</span></label>
               <select class="form-control" name="other_engineer_id" id="other_engineer_id">
                  <option value="">Select Engineer</option>
                  <?php if(isset($all_engineers)) { 
                      $loggedInUser = $_SESSION['login_id'] ?? 0;
                      foreach($all_engineers as $engineer){
                          if ((int)$engineer['user_id'] === (int)$loggedInUser) continue;
                  ?>
                  <option value="<?=$engineer['user_id'] ?>"><?=$engineer['user_name'] ?></option>
                  <?php } } ?>
               </select>
            </div>
            <div class="col-md-6">
               <label class="control-label"><b>Project Name</b> <span class="text-danger">*</span></label>
               <input type="text" name="lmra_project_name" id="lmra_project_name" class="form-control" 
                  value="<?php echo isset($lmra_project_name)?$lmra_project_name:" "?>" required>
            </div>
            <div class="col-md-6">
               <label class="control-label"><b>Job No</b> <span class="text-danger">*</span></label>
               <input type="text" name="lmra_job_no" id="lmra_job_no" class="form-control" 
                  value="<?php echo isset($lmra_job_no)?$lmra_job_no:" "?>" required>
            </div>
            <div class="col-md-6">
                <label class="control-label"><b>Location</b> <span class="text-danger">*</span></label>
                <input type="text" name="lmra_location" id="lmra_location" class="form-control" placeholder="Search Location..." autocomplete="off"
                  value="<?php echo isset($lmra_location)?$lmra_location:""?>" required>
                   <!--<input type="text" id="lmra_location" placeholder="Search Location..." autocomplete="off">-->
                    <div id="result"></div>
            </div>
            <div class="col-md-6">
               <label class="control-label"><b>Visit Date</b> <span class="text-danger">*</span></label>
               <input type="date" name="lmra_visit_date" id="lmra_visit_date"class="form-control" 
                  value="<?php echo isset($lmra_visit_date)?$lmra_visit_date:" "?>" required>
            </div>
            <div class="col-md-6">
               <label class="control-label"><b>Time</b> <span class="text-danger">*</span></label>
               <input type="time" name="lmra_visit_time" id="lmra_visit_time" class="form-control" 
                  value="<?php echo isset($lmra_visit_time)?$lmra_visit_time:" "?>" required>
            </div>
            <div class="col-md-6">
               <label class="control-label"><b>Task to be perform</b> <span class="text-danger">*</span></label>
               <input type="text" name="task_to_be_perform" id="task_to_be_perform" class="form-control" autocomplete="off" 
                  value="<?php echo isset($task_to_be_perform)?$task_to_be_perform:" "?>" required>
               <input type="hidden" name="lmra_json_data" class="form-control"  id="lmra_json_data"
                  value="<?php echo isset($lmra_json_data)?$lmra_json_data:" "?>" >
                <input type="hidden" name="sta_data" class="form-control"  id="sta_data"
                  value="<?php echo isset($sta_data)?$sta_data:" "?>" >  
               <input type="hidden" name="user_id" class="form-control" id="user_id"
                  value="<?php echo isset($user_id)?$user_id:$_SESSION['login_id']?>" >
               <input type="hidden" name="fcm_id" class="form-control" id="fcm_id"
                  value="<?php echo isset($_SESSION['fcm_id'])?$_SESSION['fcm_id']:''?>" >
            </div>
            <div class="col-md-12 table-responsive">
               <!--<label class="control-label"><b>Quotations</b></label>-->
               <!--<input type="text" name="sta_data" class="form-control" -->
               <!--   value="<?php echo isset($sta_data)?$sta_data:" "?>" required>-->
               <div class="table-responsive">
               <table class="table">
                  <!--<thead>-->
                     <th>Quotations</th>
                     <th>Yes</th>
                     <th>No</th>
                  <!--</thead>-->
                  <tbody class="dont_hide">
                     <tr value="do_i_have_the_right_ppes">
                        <td>Do i have right PPE'S?</td>
                        <td><input type="radio" name="sta_row1" value="yes" onchange="make_sda_json()"></td>
                        <td><input type="radio" name="sta_row1" value="no" onchange="make_sda_json()"></td>
                     </tr>
                     <tr value="do_i_have_the_right_tools_and_parts">
                        <td>Do i have right tool and parts?</td>
                        <td><input type="radio" name="sta_row2" value="yes" onchange="make_sda_json()"></td>
                        <td><input type="radio" name="sta_row2" value="no" onchange="make_sda_json()"></td>
                     </tr>
                     <tr value="look_around_you_is_it_safe">
                        <td>Look Around you, is it safe?</td>
                        <td><input type="radio" name="sta_row3" value="yes" onchange="make_sda_json()"></td>
                        <td><input type="radio" name="sta_row3" value="no" onchange="make_sda_json()"></td>
                     </tr>
                     <tr value="do_i_know_what_to_do_if_things_go_wrong">
                        <td>Do you know what to do if things go wrong?</td>
                        <td><input type="radio" name="sta_row4" value="yes" onchange="make_sda_json()"></td>
                        <td><input type="radio" name="sta_row4" value="no" onchange="make_sda_json()"></td>
                     </tr>
                     <tr value="what_are_the_biggest_risk_that_can_result_in_an_injury">
                        <td>what are the biggest risk that can result in an injury?</td>
                        <td><input type="radio" name="sta_row5" value="yes" onchange="make_sda_json()"></td>
                        <td><input type="radio" name="sta_row5" value="no" onchange="make_sda_json()"></td>
                     </tr>
                  </tbody>
               </table>
               </div>
            </div>
            <div class="col-md-12 table-responsive" id="lmra_dynamic_container">
               <?php  foreach($lmra_data as $key=>$lmra_head){ ?>
               <table class="table lmra_table">
                  <thead>
                     <th><input type="checkbox"></th>
                     <th style="width: 500px;"><?=$lmra_head['lmra_category_text']?></th>
                     <th><?=$lmra_head['title1']?></th>
                     <th><?=$lmra_head['title2']?></th>
                     <th><?=$lmra_head['title3']?></th>
                     <th><?=$lmra_head['title4']?></th>
                  </thead>
                  <tbody class="hide_data">
                     <?php $lmra_option=json_decode($lmra_head['lmra_data'],true) ?>
                     <?php foreach($lmra_option as $data_key=>$data_text){ ?>
                     <tr>
                        <td><?=$data_key+1 ?></td>
                        <td style="width: 500px;" ><?=$data_text['lmra_text'] ?></td>
                        <td><input type="radio" name="lmra_cat_id_<?=$data_text['lmra_id']?>" value="<?=$data_text['title1'] ?>" onchange="make_lmra_json()"></td>
                        <td><input type="radio" name="lmra_cat_id_<?=$data_text['lmra_id']?>" value="<?=$data_text['titlew'] ?>" onchange="make_lmra_json()"></td>
                        <td><input type="radio" name="lmra_cat_id_<?=$data_text['lmra_id']?>" value="<?=$data_text['title3'] ?>" onchange="make_lmra_json()"></td>
                        <?php $lmra_option_vals=explode(',', $data_text['lmra_options'])?>
                        <td>
                           <select name="lmra_sev_<?=$data_text['lmra_id']?>" onchange="make_lmra_json()" class="form-select">
                              <?php foreach($lmra_option_vals as $key_cont=> $option) { ?>
                              <option value="<?=trim($option)?>"><?=trim($option)?></option>
                              <?php } ?>
                           </select>
                        </td>
                     </tr>
                     <?php } ?>
                  </tbody>
               </table>
               <?php  } ?>
            </div>
            <!--<div class="col-md-12">-->
            <!--<button class="btn btn-outline-primary" fail_message=""-->
            <!--done_message="<?php echo isset($done_message)?$done_message:""?>"-->
            <!--ajax_events="true">Save</button>-->
            <!--<button ajax_events="true" value="save_html_data" data-url="<?php echo isset($action)?$action:""?>" type="submit" class="btn btn-purple waves-effect waves-light" style="margin-top: 15px; float: right;" onclick="event.preventDefault(); ajaxLoader(this,$(this).closest('form'));">Submit</button>-->
            <!--</div>-->
            <!--</div>-->
            <!--<div class="col-md-12">-->
            <!--   <button ajax_events="true" value="save_html_data" data-url="<?php echo isset($action)?$action:""?>" type="submit" class="btn btn-purple waves-effect waves-light" style="margin-top: 15px; float: right;" onclick="event.preventDefault(); ajaxLoader(this,$(this).closest('form'));">Submit</button>-->
            <!--</div>-->
      </form>
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

        $("#ajax_click").on("click",function(){
                var button = $(this);
                var url = $(this).attr("data-ajax-url");
                
                // Custom Validation
                $('.error-msg').remove();
                $("#<?=$form_id?>_form").find('.is-invalid').removeClass('is-invalid');
                var isValid = true;
                
                $("#<?=$form_id?>_form").find('[required]').each(function(){
                    if($(this).val() === null || $(this).val().trim() === ""){
                        isValid = false;
                        $(this).addClass('is-invalid');
                        $(this).after('<div class="text-danger error-msg mt-1" style="font-size: 0.85rem;">This field is required</div>');
                    }
                });

                if (isValid) {
                    var data_to_send =$("#<?=$form_id?>_form").serializeArray();
                    var formData = {};
                    $.each(data_to_send, function(i, field){
                        if(field.value.trim() != ""){
                          formData[field.name] = field.value;
                        }
                    });

                    ajax_call(url,formData,button,function(responce){
                        try{
                            if (typeof responce === 'string') {
                                responce = JSON.parse(responce);
                            }
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
                } else {
                    toastr.warning("Please fill all mandatory fields");
                }
});
function edit_id(obj,id){
    var url = $(obj).attr("data-ajax-url");
    var formData={"id":id};
    ajax_call(url,formData,$(obj),function(responce){
                        try{
                            if (typeof responce === 'string') {
                                responce = JSON.parse(responce);
                            }
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
                                    $("#<?=$form_id?>").find("#lmra_project_name").val(responce.data.lmra_project_name);
                                    $("#<?=$form_id?>").find("#lmra_job_no").val(responce.data.lmra_job_no);
                                    $("#<?=$form_id?>").find("#lmra_location").val(responce.data.lmra_location);
                                    $("#<?=$form_id?>").find("#lmra_visit_date").val(responce.data.lmra_visit_date);
                                    $("#<?=$form_id?>").find("#lmra_visit_time").val(responce.data.lmra_visit_time);
                                    $("#<?=$form_id?>").find("#task_to_be_perform").val(responce.data.task_to_be_perform);
                                    // change on 29/09/25 by darsh: prefill simple Yes/No questions from sta_data JSON
                                    try{
                                        var sda = responce.data.sta_data ? JSON.parse(responce.data.sta_data) : {};
                                        $("#<?=$form_id?>").find('.dont_hide tr').each(function(){
                                            var key = $(this).attr('value');
                                            var val = sda[key];
                                            var name = $(this).find('input').first().attr('name');
                                            if(val === true){
                                                $("#<?=$form_id?>").find('input[name="'+name+'"][value="yes"]').prop('checked', true);
                                            }else if(val === false){
                                                $("#<?=$form_id?>").find('input[name="'+name+'"][value="no"]').prop('checked', true);
                                            } else {
                                                // leave unchecked if undefined
                                            }
                                        });
                                    }catch(e){}
                                    // change on 29/09/25 by darsh: prefill LMRA table selections from lmra_json_data
                                    try{
                                        
                                        // Handle self/other engineer logic
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
                                            // Legacy data without self_other, default to self
                                            $("#self_radio").prop("checked", true);
                                            $("#other_engineer_div").hide();
                                            $("#other_engineer_id").val("");
                                        }
                                        
                                        var lmra = responce.data.lmra_json_data ? JSON.parse(responce.data.lmra_json_data) : [];
                                        if (lmra.length > 0) {
                                            var newHtml = "";
                                            lmra.forEach(function(header, tIndex) {
                                                newHtml += '<div class="table-responsive">';
                                                newHtml += '<table class="table lmra_table">';
                                                newHtml += '<thead><tr>';
                                                var headerChecked = header.header ? "checked" : "";
                                                newHtml += '<th><input type="checkbox" ' + headerChecked + '></th>';
                                                newHtml += '<th style="width: 500px;">' + (header.title_1_header || "") + '</th>';
                                                newHtml += '<th>' + (header.title_2_header || "") + '</th>';
                                                newHtml += '<th>' + (header.title_3_header || "") + '</th>';
                                                newHtml += '<th>' + (header.title_4_header || "") + '</th>';
                                                newHtml += '<th>' + (header.title_5_header || "") + '</th>';
                                                newHtml += '</tr></thead>';
                                                
                                                var tbodyClass = header.header ? "show_data" : "hide_data";
                                                newHtml += '<tbody class="' + tbodyClass + '">';
                                                var items = header.lmrsFooterForms || [];
                                                items.forEach(function(it, rIndex) {
                                                    newHtml += '<tr>';
                                                    newHtml += '<td>' + (it.id_footer || "") + '</td>';
                                                    newHtml += '<td style="width: 500px;">' + (it.title_1_footer || "") + '</td>';
                                                    
                                                    var rName = "lmra_cat_id_hist_" + tIndex + "_" + rIndex;
                                                    var c1 = it.cb_1_footer ? "checked" : "";
                                                    var c2 = it.cb_2_footer ? "checked" : "";
                                                    var c3 = it.cb_3_footer ? "checked" : "";
                                                    
                                                    newHtml += '<td><input type="radio" name="'+rName+'" value="'+(header.title_2_header || "")+'" '+c1+' onchange="make_lmra_json()"></td>';
                                                    newHtml += '<td><input type="radio" name="'+rName+'" value="'+(header.title_3_header || "")+'" '+c2+' onchange="make_lmra_json()"></td>';
                                                    newHtml += '<td><input type="radio" name="'+rName+'" value="'+(header.title_4_header || "")+'" '+c3+' onchange="make_lmra_json()"></td>';
                                                    
                                                    newHtml += '<td><select class="form-select" onchange="make_lmra_json()">';
                                                    var opts = it.strings_3 || [];
                                                    opts.forEach(function(opt) {
                                                        var sel = (opt.string === it.selected_string_3) ? "selected" : "";
                                                        newHtml += '<option value="'+opt.string+'" '+sel+'>'+opt.string+'</option>';
                                                    });
                                                    newHtml += '</select></td>';
                                                    newHtml += '</tr>';
                                                });
                                                newHtml += '</tbody></table></div>';
                                            });
                                            $("#<?=$form_id?>").find("#lmra_dynamic_container").html(newHtml);
                                        } else {
                                            // Fallback to default structure
                                            $("#<?=$form_id?>").find("#lmra_dynamic_container").html(default_lmra_html);
                                        }

                                        // also restore hidden json fields so subsequent saves preserve state
                                        $("#<?=$form_id?>").find('#lmra_json_data').val(responce.data.lmra_json_data || '');
                                        $("#<?=$form_id?>").find('#sta_data').val(responce.data.sta_data || '');
                                    }catch(e){ console.error(e); }

                                
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

function change_lmra_status(url, title, button) {
    Swal.fire({
        title: 'Confirm ' + title,
        text: 'Are you sure you want to ' + title.toLowerCase() + ' this LMRA?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, ' + title,
    }).then((result) => {
        if (result.isConfirmed) {
            url_call_ajax(url, button);
        }
    });
}

function change_lmra_status_with_comment(url, title, placeholder, button) {
    Swal.fire({
        title: title,
        text: 'Please provide ' + placeholder,
        input: 'textarea',
        inputPlaceholder: placeholder,
        showCancelButton: true,
        confirmButtonText: 'Submit',
        preConfirm: (comment) => {
            if (!comment || comment.trim() === '') {
                Swal.showValidationMessage('Comment is required');
                return false;
            }
            return comment;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            var formData = { rejection_comment: result.value };
            ajax_call(url, formData, button, function(responce) {
                try {
                    if (typeof responce === 'string') {
                        responce = JSON.parse(responce);
                    }
                    if (responce.status == 1) {
                        toastr.success(responce.message);
                        reload_data_table();
                    } else {
                        toastr.warning(responce.message);
                    }
                } catch(error) {
                    toastr.error(error);
                }
            });
        }
    });
}

function toggleOtherEngineer() {
    if ($("#other_radio").is(":checked")) {
        $("#other_engineer_div").show();
    } else {
        $("#other_engineer_div").hide();
        $("#other_engineer_id").val("");
    }
}

        $(document).ready(function() {
            $('#lmra_location').on('keyup', function() {
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
                                    output += `<div class="result-item" data-value="${user.location_name}">${user.location_name}</div>`;
                                });
                            } else {
                                output = '<div class="result-item no-results">No results found</div>';
                            }
                            $('#result').html(output).show();
                        }
                    });
                } else {
                    $('#result').hide();
                }
            });
            
            $(document).on('click', '.result-item', function() {
                if (!$(this).hasClass('no-results')) {
                    $('#lmra_location').val($(this).data('value'));
                }
                $('#result').hide();
            });
            
            // Hide dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#lmra_location').length && !$(e.target).closest('#result').length) {
                    $('#result').hide();
                }
            });
            
            // Show dropdown when input is focused (optional for better UX)
            $('#lmra_location').on('focus', function() {
                if ($(this).val().length > 1) {
                    $(this).trigger('keyup');
                }
            });
        });
</script>
<script>
   function validatation(){
    var valid=0
    $('form').find('[required]').each(function(i){
        console.log(this);
        if($(this).val()==" " ||  $(this).val()==""){
            valid++;
            $(this).focus();
            return false;
        }
    })
    if(valid==0)
        return true;
   }
   window.redirect_method = function() {
     window.location="<?php echo isset($redirect_url)?$redirect_url:''?>";
   };

   function make_lmra_json(){
    var lmra_object=[];
    var lmrsFooterForms_temp=[];
    var lmrsFooterForms='';
    $(".lmra_table").each(function(i){
        // make header object
        var head=$(this).find('thead > tr');
        var header_id=i+1;
        var header=head.find("th:eq(0)").find('input').prop("checked"); 
        var title_1_header=head.find("th:eq(1)").text().trim();
        var title_2_header=head.find("th:eq(2)").text().trim();
        var title_3_header=head.find("th:eq(3)").text().trim();
        var title_4_header=head.find("th:eq(4)").text().trim();
        var title_5_header=head.find("th:eq(5)").text().trim();
        lmrsheaderForms= new Object();
        lmrsheaderForms={
            "id": header_id,
            "header": header,
            "sr_no_header": "a)",
            "title_1_header": title_1_header,
            "title_2_header": title_2_header,
            "title_3_header": title_3_header,
            "title_4_header": title_4_header,
            "title_5_header": title_5_header,
            "lmrsFooterForms":[]
        };
       $(this).find('tbody > tr').each(function(){
            // footer object create 
            var id_footer=$(this).find("td:eq(0)").text();
            var sr_no_footer=$(this).find("td:eq(0)").text();
            var title_1_footer=$(this).find("td:eq(1)").text();
            var cb_1_footer=$(this).find("td:eq(2)").find('input').prop("checked");
            var cb_2_footer=$(this).find("td:eq(3)").find('input').prop("checked");
            var cb_3_footer=$(this).find("td:eq(4)").find('input').prop("checked");
            var select=$(this).find("td:eq(5)").find('select');
            var selected_string_3=select.val();
            lmrsFooterForms= new Object();
            lmrsFooterForms={
                "id_footer": id_footer,
                "sr_no_footer": sr_no_footer,
                "title_1_footer": title_1_footer,
                "cb_1_footer": cb_1_footer,
                "cb_2_footer": cb_2_footer,
                "cb_3_footer": cb_3_footer,
                "strings_3": [],
                "selected_string_3": selected_string_3
            };
            $(select).find('option').each(function(){
               var string=new Object();
               string={
                    "string": $(this).text()
                  }
                lmrsFooterForms['strings_3'].push(string);  
            });
            // lmrsFooterForms['strings_3'].reverse() ;
            lmrsheaderForms['lmrsFooterForms'].push(lmrsFooterForms);
        });
        lmra_object.push(lmrsheaderForms);
    });
    $("#lmra_json_data").val(JSON.stringify(lmra_object));
   }
   
    // $(".dont_hide >tr >td >input[type='checkbox']").on('click', function(){
        // make_sda_json();
    // }) ;
   function make_sda_json(){
       var sda_body=$(".dont_hide");
       var sda_json={};
       sda_body.find('tr').each(function(){
           var input_name=$(this).find('td:eq(1) > input').attr('name');
           var selected_val = $("input[name='"+input_name+"']:checked").val();
           var final_value = null;
           if (selected_val === 'yes') {
               final_value = true;
           } else if (selected_val === 'no') {
               final_value = false;
           }
           sda_json[$(this).attr('value')]=final_value;
       });
       $("#sta_data").val(JSON.stringify(sda_json));
   }
   
   

    // Removed old check_if_selected as radio buttons are now used for mutually exclusive selection
   
   $(document).on("click", "table.lmra_table thead", function(e) {
      var chcck_box = $(this).find("th:eq(0) input[type='checkbox']");
      var tbody = $(this).parents("table").find("tbody");
      
      // If the user clicked the thead but NOT the checkbox itself, toggle the checkbox manually
      if(e.target !== chcck_box[0]) {
          chcck_box.prop("checked", !chcck_box.prop("checked"));
      }
      
      if(chcck_box.prop("checked") == true){
          tbody.removeClass('hide_data').addClass('show_data');
      } else {
          tbody.removeClass('show_data').addClass("hide_data");
      }
   });
   
   var default_lmra_html = "";
   $(document).ready(function(){
       default_lmra_html = $("#lmra_dynamic_container").html();
       make_lmra_json();
   });
   
   $("#<?=$form_id?>").on('hidden.bs.modal', function () {
       try {
           if (form && typeof form.reset === 'function') {
               form.reset();
           }
       } catch(e) {}
       try {
           if (typeof validator !== 'undefined' && validator && typeof validator.resetForm === 'function') {
               validator.resetForm(true);
           }
       } catch(e) {}
       $('.error-msg').remove();
       $("#<?=$form_id?>_form").find('.is-invalid').removeClass('is-invalid');
       // Reset HTML structure to active masters for new entries
       $("#<?=$form_id?>").find("#lmra_dynamic_container").html(default_lmra_html);
       $("#<?=$form_id?>").find('#lmra_json_data').val('');
       $("#<?=$form_id?>").find('#sta_data').val('');
       $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-url", $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-add-url"));
   });

   
</script>
<style>
.hide_data{
    display:none;
}
.show_data{
    display:contents;
}

</style>
<?php 				$this->endSection();?>

