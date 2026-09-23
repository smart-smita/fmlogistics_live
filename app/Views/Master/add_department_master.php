
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
						<h2>Department Details</h2>
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
                    	<div class="scroll-y me-n7 pe-7" id="user" data-kt-scroll="false" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_new_address_header" data-kt-scroll-wrappers="#fees_head" data-kt-scroll-offset="300px" style="max-height: 273px;">
								<!--begin::Input group-->
								<div class="row mb-12">
									<!--begin::Col-->
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Department Name</label>
										<input type="text" class="form-control form-control-solid" placeholder="department name"  id="department_name" name="department_name">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Location Name</label>
										<select class="form-select form-select-solid" onchange="update_location_id(this)" id="location_name" name="location_name">
										    <option value="" data-location-id="0">Select Location</option>
										    <?php foreach($location_list as $row){ ?>
										    <option value="<?=$row['location_name']?>" data-location-id="<?=$row['location_id']?>"><?=$row['location_name']?></option>
										        <?php } ?>
										<input type="hidden" class="form-control form-control-solid" placeholder="location id" id="location_id" name="location_id">
										</select>
										<div class="fv-plugins-message-container invalid-feedback">
										    
										</div>
									</div>


							
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
							
							<!--begin::Button-->
							<button type="submit" id="ajax_click" data-ajax-add-url="<?=$ajax_url?>" class="btn btn-primary">
								<span class="indicator-label">Submit</span>
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
function update_location_id(obj){
    $("#location_id").val(""+$(obj).find("option:selected").data("location-id"));
}
//$("#kt_datepicker_1").flatpickr();
//$.ajax()
const form = document.getElementById('<?=$form_id?>_form');
var validation_object  = {
        fields: {
            'department_name': {
                validators: {
                    notEmpty: {
                        message: 'Department Name is required'
                    }
                }
            },
            'location_name': {
                validators: {
                    notEmpty: {
                        message: 'Location name is required'
                    }
                }
            },
            'location_id': {
                validators: {
                    notEmpty: {
                        message: 'Location id is required'
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
                var data_to_send = $("#<?=$form_id?>_form").serializeArray();
                var formData = {};
                $.each(data_to_send, function(i, field) {
                    // Check if the field name is 'options[]'
                    if (field.name === 'options[]') {
                        // Initialize the array if it doesn't exist
                        if (!formData['options[]']) {
                            formData['options[]'] = [];
                        }
                        // Push the value into the 'options[]' array
                        formData['options[]'].push(field.value);
                    } else {
                        // Assign the value directly to the corresponding key
                        if (field.value.trim() !== "") {
                            formData[field.name] = field.value;
                        }
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

                                    $("#<?=$form_id?>").find("#department_name").val(responce.data.department_name);
                                    $("#<?=$form_id?>").find("#location_name").val(responce.data.location_name);
                                    $("#<?=$form_id?>").find("#location_id").val(responce.data.location_id);

                                    
                                                                
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

