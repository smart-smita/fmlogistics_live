<?php $this->extend("Layout/base_admin"); ?>
<?php 	$this->section("breadcrumb_title_li"); ?>
<?php 
$form_id= $button_id;
//data-ajax-url during add/edit 
//data-ajax-add-url during add Important
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<!-- NEW CHANGES: Breadcrumb resets filter by going to index -->
		<a href="<?= base_url('Masters/Inplant_nc_tracker') ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"Client"?></a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>
<?=$table?>
<?php $this->endSection(); ?>

<?php  $this->section("modals_section"); ?>

		<!--begin::Modal - Create App-->
		<div class="modal fade" id="<?=$button_id?>" tabindex="-1" aria-hidden="true">
		    <!--<form id="" onsubmit="return false;">-->
		  <form id="<?=$form_id?>_form" onsubmit="return false;"  enctype="multipart/form-data">
			<!--begin::Modal dialog-->
			<div class="modal-dialog modal-dialog-centered mw-900px">
				<!--begin::Modal content-->
				<div class="modal-content">
					<!--begin::Modal header-->
					<div class="modal-header">
						<!--begin::Modal title-->
						<h2>Inplant Nc Tracker</h2>
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

                    	<div class="scroll-y me-n7 pe-7" id="user" data-kt-scroll="false" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_new_address_header" data-kt-scroll-wrappers="#fees_head" data-kt-scroll-offset="300px" style="max-height: 70vh;">
								<!--begin::Input group-->
								<div class="row mb-12">
									<!--begin::Col-->


                                  <div class="twocol">
                                  <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="fs-5 fw-bold mb-2">Audit No.</label>
										<input type="text" class="form-control form-control-solid"  id="audit_no" name="audit_no">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									 <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="fs-5 fw-bold mb-2">Audit Name </label>
										<input type="text" class="form-control form-control-solid" id="audit_name" name="audit_name">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									 <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="fs-5 fw-bold mb-2">Auditor Name</label>
										<input type="text" class="form-control form-control-solid"  id="auditor_name" name="auditor_name">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									 <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="fs-5 fw-bold mb-2">Auditee Name</label>
										<input type="text" class="form-control form-control-solid"  id="auditee_name" name="auditee_name">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									 <!-- changes on 10/11/12 by darsh: remove Audit/Completion dates in NC form -->
									 <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="fs-5 fw-bold mb-2">Region</label>
										<input type="text" class="form-control form-control-solid"  id="region" name="region">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									 <!-- changes on 10/11/12 by darsh: remove Site Location in NC form -->
									 <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="fs-5 fw-bold mb-2">Score</label>
										<input type="text" class="form-control form-control-solid"  id="score" name="score">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									 <div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Site Category</label>
										<input type="text" class="form-control form-control-solid"  id="perform_audit_by" name="perform_audit_by">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									 
									 <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="fs-5 fw-bold mb-2">Question Name </label>
										<input type="text" class="form-control form-control-solid"  id="question_name" name="question_name">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
                                     <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="fs-5 fw-bold mb-2">Audit Question </label>
										<input type="text" class="form-control form-control-solid"  id="audit_question" name="audit_question">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
                                    </div> 									<!-- NEW CHANGES: Make Inplant read-only and show NO -->
                                    <div class="twocol">
                                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="fs-5 fw-bold mb-2">Inplant</label>
                                        <input type="text" class="form-control form-control-solid" id="inplant" name="inplant" value="NO" disabled>
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                     <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="fs-5 fw-bold mb-2">Remark </label>
                                        <input type="text" class="form-control form-control-solid"  id="remark" name="remark">
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                    </div>
									<!-- NEW CHANGES: NC Before Photo - Display only from audit, no file input -->
									<div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class="fs-5 fw-bold mb-2">NC Before Photo (From Audit)</label>
										<div id="audit_before_photo_display" class="form-control form-control-solid" style="min-height: 50px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
											<span class="text-muted">Before photo will be loaded from audit data</span>
										</div>
										<input type="hidden" name="before_photo_url" id="before_photo_url">
									</div>
                                        <!-- changes on 6/10/25 by darsh: clickable thumbnail preview for before photo -->
                                        <script>
                                        (function(){
                                            function renderBeforePreview(url){
                                                var wrap = document.getElementById('audit_before_photo_display');
                                                if(!wrap) return;
                                                if(url && url.trim() !== ''){
                                                    var base = '<?= base_url('/') ?>';
                                                    var full = url.match(/^https?:/i) ? url : (base + url);
                                                    wrap.innerHTML = '<a href="'+full+'" target="_blank"><img src="'+full+'" style="max-width:120px;max-height:120px;border:1px solid #eee;padding:2px;background:#fff" /></a>';
                                                }
                                            }
                                            document.addEventListener('shown.bs.modal', function(ev){
                                                if(ev.target && ev.target.id === '<?= $form_id ?>'){
                                                    var val = document.getElementById('before_photo_url') ? document.getElementById('before_photo_url').value : '';
                                                    renderBeforePreview(val);
                                                }
                                            });
                                        })();
                                        </script>
                                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                                       <label class="required fs-5 fw-bold mb-2">Nc Remark</label>
                                       <textarea class="form-control form-control-solid" id="nc_remark" name="nc_remark" rows="4" placeholder="Please enter NC remark"></textarea>
                                       <div class="fv-plugins-message-container invalid-feedback"></div>
                                   </div>
									
                                   <div class="col-md-12 fv-row fv-plugins-icon-container">
                                     <label class="required fs-5 fw-bold mb-2">Nc After Photo</label>
                                     <input type="file" class="form-control" id="nc_after_photo" name="nc_after_photo" accept=".jpg,.jpeg,.png,.gif,.pdf" onchange="validateFileType(this)"/>
                                     <input type="hidden" name="after_photo_url" id="after_photo_url">
                                   </div>
                                    
                                    <!-- NEW CHANGES: Move date and by fields under NC After Photo -->
                                   <div class="twocol">
                                   <!-- <div class="col-md-12 fv-row fv-plugins-icon-container">
                                       <label class="fs-5 fw-bold mb-2">Nc Closed Date</label>
                                       <input type="date" class="form-control form-control-solid"  id="nc_closed_date" name="nc_closed_date">
                                       <div class="fv-plugins-message-container invalid-feedback"></div>
                                   </div>
                                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                                       <label class="fs-5 fw-bold mb-2">Nc Reviewed Date</label>
                                       <input type="date" class="form-control form-control-solid"  id="nc_reviewed_date" name="nc_reviewed_date">
                                       <div class="fv-plugins-message-container invalid-feedback"></div>
                                   </div>
                                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                                       <label class="fs-5 fw-bold mb-2">Nc Closed By</label>
                                       <input type="text" class="form-control form-control-solid"  id="nc_closed_by" name="nc_closed_by">
                                       <div class="fv-plugins-message-container invalid-feedback"></div>
                                   </div>
                                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                                       <label class="fs-5 fw-bold mb-2">Nc Reviewed By</label>
                                       <input type="text" class="form-control form-control-solid"  id="nc_reviewed_by" name="nc_reviewed_by">
                                       <div class="fv-plugins-message-container invalid-feedback"></div>
                                   </div> -->
                                   </div>
                        
                       

									<!--<div class="col-md-12 fv-row fv-plugins-icon-container">-->
									<!--	<label class="required fs-5 fw-bold mb-2">lmra_options  </label>-->
									<!--	<input type="text" class="form-control form-control-solid" placeholder="Please enter lmra options" id="lmra_options" name="lmra_options">-->
									<!--	<div class="fv-plugins-message-container invalid-feedback"></div>-->
									<!--</div>-->
							

							
							<!--<div class="form-check" id="checkbox-container">-->
                                    <!-- Checkboxes will be generated here by JavaScript -->
       <!--                         </div>		-->
                            									<!--end::Col-->
							<!--	</div>-->
								
								
								
								<!--end::Input group-->
							</div>
					</div>
					<!--end::Modal body-->
					
					<!--begin::Modal footer-->
					<div class="modal-footer flex-center">
								<!--begin::Button-->
								<button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
								<!--end::Button-->
								
                                
								
                                <!--begin::Button - Save as Draft-->
                                <button type="button" id="ajax_click_draft" data-ajax-add-url="<?=$ajax_url?>" class="btn btn-light-primary me-3">
                                    <span class="indicator-label">Save as Draft</span>
                                    <span class="indicator-progress">Please wait... 
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                                <!--end::Button-->
                                <!--begin::Button - Submit for Review-->
                                <button type="submit" id="ajax_click" data-ajax-add-url="<?=$ajax_url?>" class="btn btn-primary">
                                    <span class="indicator-label">Submit for Review</span>
                                    <span class="indicator-progress">Please wait... 
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                                <!--end::Button-->
							
						</div>
						<!--end::Modal footer-->
							
						
				<!--end::Modal content-->
			</div>
			<!--end::Modal dialog-->
        </form>
        </div>
		<!--end::Modal - Create App-->


<?php 				$this->endSection();?>
	

<?php 					$this->section("javascript_section");?>
<script>

// NEW CHANGES: Fix textarea Enter key issue
$(document).ready(function() {
    $('textarea').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            var cursorPos = this.selectionStart;
            var textBefore = this.value.substring(0, cursorPos);
            var textAfter = this.value.substring(cursorPos);
            this.value = textBefore + '\n' + textAfter;
            this.setSelectionRange(cursorPos + 1, cursorPos + 1);
        }
    });
});


function update_div_dep(obj){
    $(".locations").hide();
    $(".location_id_"+$(obj).find("option:selected").data("location-id")).show()
}

// NEW CHANGES: Add status update function for Inplant NC Tracker
function updateInplantStatus(id, status) {
    $.post('<?= base_url('Masters/Inplant_nc_tracker/update_nc_status') ?>', { id: id, status: status })
        .always(function() {
            if (typeof reload_data_table === 'function') {
                reload_data_table();
            } else {
                location.reload();
            }
        });
}

//$("#kt_datepicker_1").flatpickr();
//$.ajax()
const form = document.getElementById('<?=$form_id?>_form');
var validation_object  = {
        fields: {
                'location': {
                validators: {
                    notEmpty: {
                        message: 'Location is required'
                    }
                }
            },'nc_description':{
                 validators: {
                    notEmpty: {
                        message: 'Nc Description is required'
                    }
                }
            },'devision_dept':{
                 validators: {
                    notEmpty: {
                        message: 'Div/Dep is required'
                    }
                }
            }
            ,'nc_photo':{
                 validators: {
                    notEmpty: {
                        message: 'Nc Photo is required'
                    }
                }
            }
            ,'action_required_dep':{
                 validators: {
                    notEmpty: {
                        message: 'Action Required Dep is required'
                    }
                }
            },'responsibilty':{
                 validators: {
                    notEmpty: {
                        message: 'Responsibilty is required'
                    }
                }
            },'target_date':{
                 validators: {
                    notEmpty: {
                        message: 'Target Date is required'
                    }
                }
            },'sixs_category':{
                 validators: {
                    notEmpty: {
                        message: 'Sixs Category is required'
                    }
                }
            },'details_of_ca_pa':{
                 validators: {
                    notEmpty: {
                        message: 'Details Of Ca Pa is required'
                    }
                }
            },'after_photo':{
                 validators: {
                    notEmpty: {
                        message: 'After Photo is required'
                    }
                }
            },'department_status':{
                 validators: {
                    notEmpty: {
                        message: 'Department Status is required'
                    }
                }
            },'final_status_soi':{
                 validators: {
                    notEmpty: {
                        message: 'Final Status Soi is required'
                    }
                }
            },
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
        if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                // var data_to_send = $("#<?php //$form_id?>_form").serializeArray();
                // var formData = {};
                // $.each(data_to_send, function(i, field) {
                //     // Check if the field name is 'options[]'
                //     if (field.name === 'options[]') {
                //         // Initialize the array if it doesn't exist
                //         if (!formData['options[]']) {
                //             formData['options[]'] = [];
                //         }
                //         // Push the value into the 'options[]' array
                //         formData['options[]'].push(field.value);
                //     } else {
                //         // Assign the value directly to the corresponding key
                //         if (field.value.trim() !== "") {
                //             formData[field.name] = field.value;
                //         }
                //     }
                // });
                var formData = new FormData($("#<?=$form_id?>_form")[0]);
                formData.append('action', 'submit_for_review'); // Add action parameter

                //ajax_call(url,data_to_send,button,success_function)
                    ajax_call_img(url,formData,button,function(responce){
                        try{
                            responce = JSON.parse(responce);
                            if(responce.status==1){
                                    // NEW CHANGES: Silent operation - no toasts
                                    form.reset();
                                    $("#<?=$form_id?>").modal("hide");
                                    reload_data_table();
                            }else{
                                    // Silent error handling
                                }
                            }catch(error){
                                // Silent error handling
                            }
                    });
                }else{
                    // not validate 
                }
        });
        }
    
});

// removed draft
// Save as Draft handler
$("#ajax_click_draft").on("click",function(){
    var button = $(this);
    var url = $(this).attr("data-ajax-url") || $(this).attr("data-ajax-add-url");
    var formData = new FormData($("#<?=$form_id?>_form")[0]);
    formData.append('action', 'save_as_draft');
    ajax_call_img(url,formData,button,function(responce){
        try{responce = JSON.parse(responce);}catch(e){}
        $("#<?=$form_id?>").modal("hide");
        if(typeof reload_data_table === 'function') { reload_data_table(); } else { location.reload(); }
    });
});
function edit_id(obj,id){
    var url = $(obj).attr("data-ajax-url");
    var formData={"id":id};
    ajax_call(url,formData,$(obj),function(responce){
                        try{
                            responce = JSON.parse(responce);
                            if(responce.status==1){
                                    // NEW CHANGES: Silent operation - no toasts
                                    //form.reset();
                                    // to update value 
//                                     t//audit_name
                                    $("#<?=$form_id?>").find("#audit_no").val(responce.data.audit_no).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#audit_name").val(responce.data.audit_name).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#auditee_name").val(responce.data.auditee_name).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#audit_date").val(responce.data.audit_date).prop("disabled", true);
                                     $("#<?=$form_id?>").find("#template_date").val(responce.data.template_date).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#region").val(responce.data.region).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#location").val(responce.data.location).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#score").val(responce.data.score).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#perform_audit_by").val(responce.data.perform_audit_by).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#auditor_name").val(responce.data.auditor_name).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#question_name").val(responce.data.question_name).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#audit_question").val(responce.data.audit_question).prop("disabled", true);
                                    // NEW CHANGES: Keep inplant as NO, not YES
                                    $("#<?=$form_id?>").find("#inplant").val("NO").prop("disabled", true); 
                                    $("#<?=$form_id?>").find("#remark").val(responce.data.remark).prop("disabled", true);
                                    
                                    // changes on 7/10/25 by darsh: Display before photo using resolved before_photo_url
                                    var beforeUrl = responce.data.before_photo_url || responce.data.attachment || '';
                                    if(beforeUrl && beforeUrl !== '') {
                                        var full = beforeUrl.match(/^https?:/i) ? beforeUrl : ('<?= base_url('/') ?>' + beforeUrl);
                                        $("#<?=$form_id?>").find("#audit_before_photo_display").html('<a href="'+full+'" target="_blank"><img src="' + full + '" style="max-width: 100%; max-height: 200px;" onerror="this.parentElement.innerHTML=\'<span class=\\"text-muted\\">No before photo available</span>\'" /></a>');
                                    } else {
                                        $("#<?=$form_id?>").find("#audit_before_photo_display").html('<span class="text-muted">No before photo available</span>');
                                    }
                                    $("#<?=$form_id?>").find("#before_photo_url").val(beforeUrl);
                                    $("#<?=$form_id?>").find("#nc_closed_date").val(responce.data.nc_closed_date).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#nc_reviewed_date").val(responce.data.nc_reviewed_date).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#nc_closed_by").val(responce.data.nc_closed_by).prop("disabled", true);
                                    $("#<?=$form_id?>").find("#nc_reviewed_by").val(responce.data.nc_reviewed_by).prop("disabled", true);
                                    // NEW CHANGES: Handle after photo field
                                    $("#<?=$form_id?>").find("#after_photo_url").val(responce.data.nc_after_photo);

                                                                
                                    $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-url",$("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-add-url")+"/"+id);
                                    $("#<?=$form_id?>").find("#ajax_click_draft").attr("data-ajax-url",$("#<?=$form_id?>").find("#ajax_click_draft").attr("data-ajax-add-url")+"/"+id);
                                    $("#<?=$form_id?>").modal("show");
                            }else{
                                    // Silent error handling
                                }
                            }catch(error){
                                // Silent error handling
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

</script>
<!-- changes on 30/09/25 by darsh: hide Add button on Inplant NC Tracker page only -->
<script>
// changes on 30/09/25 by darsh: hide shared Add button rendered by table-view
$(function(){
	$('button[data-bs-target="#user_modal"]').closest('.card-toolbar').hide();
});
</script>
<?php 				$this->endSection();?>
