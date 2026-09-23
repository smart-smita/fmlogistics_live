
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
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary">User</a>
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
						<h2>User Details</h2>
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
                    	<div class="scroll-y me-n7 pe-7" id="customer" data-kt-scroll="false" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_new_address_header" data-kt-scroll-wrappers="#customer" data-kt-scroll-offset="300px" style="max-height: 273px;">
								<!--begin::Input group-->
								<div class="row mb-12">
									<!--begin::Col-->
									<div class="col-md-12 fv-row fv-plugins-icon-container" data-select2-id="select2-data-424-yxl3">
									<label class="required fs-6 fw-bold mb-2">Client</label>
									<select class="form-select form-select-solid "  data-hide-search="true" data-placeholder="Select a Client" id="user_id" name="user_id" tabindex="-1" aria-hidden="true">
										<option value="" data-select2-id="select2-data-63-4pgj">Select Client...</option>
										<?php
										foreach ($user_list as $row){
										  //  echo $row["user_id"],$row["f_name"].$row["l_name"];
										  ?>
										<option value="<?=$row["user_id"]?>" data-select2-id="select2-data-63-4pgj"><?=$row["first_name"]." ".$row["last_name"]." ( ".$row["company_name"]." ) "?></option>
										  
										  
										  <?php 
										} 
										?>
										
									</select>
								<!--<span class="select2 select2-container select2-container--bootstrap5 select2-container--above" dir="ltr" data-select2-id="select2-data-62-h7xs" style="width: 100%;"><span class="selection"><span class="select2-selection select2-selection--single form-select form-select-solid" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-disabled="false" aria-labelledby="select2-target_assign-2x-container" aria-controls="select2-target_assign-2x-container"><span class="select2-selection__rendered" id="select2-target_assign-2x-container" role="textbox" aria-readonly="true" title="Select a Team Member"><span class="select2-selection__placeholder">Select a Customer</span></span><span class="select2-selection__arrow" role="presentation"><b role="presentation"></b></span></span></span><span class="dropdown-wrapper" aria-hidden="true"></span></span>-->
								<div class="fv-plugins-message-container invalid-feedback"></div></div>
								
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2"> Name </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter Name" id="customer_name" name="customer_name">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Email Id  </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter Email Id" id="customer_email" name="customer_email">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<!--<div class="col-md-12 fv-row fv-plugins-icon-container">-->
									<!--	<label class="required fs-5 fw-bold mb-2">Company Name  </label>-->
									<!--	<input type="text" class="form-control form-control-solid" placeholder="Please enter Company Name" id="company_name" name="company_name">-->
									<!--	<div class="fv-plugins-message-container invalid-feedback"></div>-->
									<!--</div>-->
									
								
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Expiry Date  </label>
										<input type="date" class="form-control form-control-solid" placeholder="Please enter Expiry Date" id="customer_expiry_date" name="customer_expiry_date">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Password  </label>
										<input type="password" class="form-control form-control-solid" placeholder="Please enter Password" id="customer_password" name="customer_password">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									
									
									<!--end::Col-->
								</div>
								
								
								
								<!--end::Input group-->
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
            'customer_name': {
                validators: {
                    notEmpty: {
                        message: 'Name is required'
                    }
                }
            },
            'customer_email': {
                validators: {
                    notEmpty: {
                        message: 'Email is required'
                    }
                }
            },
            'customer_expiry_date': {
                validators: {
                    notEmpty: {
                        message: 'Expiry Date is required'
                    }
                }
            },
            'customer_password': {
                validators: {
                    notEmpty: {
                        message: 'Password is required'
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
                var data_to_send =$("#<?=$form_id?>_form").serializeArray();
                    var formData = {};
                    $.each(data_to_send, function(i, field){
                        if(field.value.trim() != ""){
                          formData[field.name] = field.value;
                        }
                    });

                //ajax_call(url,data_to_send,button,success_function)
                    ajax_call(url,formData,button,function(responce){
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
                                    $("#<?=$form_id?>").find("#customer_name").val(responce.data.customer_name);
                                    $("#<?=$form_id?>").find("#customer_email").val(responce.data.customer_email);
                                    $("#<?=$form_id?>").find("#customer_expiry_date").val(responce.data.customer_expiry_date);
                                    $("#<?=$form_id?>").find("#customer_password").val(responce.data.customer_password);
                                    $("#<?=$form_id?>").find("#user_id").val(responce.data.user_id);

                                  /*  $("#<?=$form_id?>").find("#fees_category").val(responce.data.fees_category);*/
                                    
                                    
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
<?php 				$this->endSection();?>

