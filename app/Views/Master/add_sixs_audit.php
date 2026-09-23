<?php $this->extend("Layout/base_admin"); ?>
<?php 
					$this->section("breadcrumb_title_li");
?>
<?php 
$form_id= $button_id;
//data-ajax-url during add/edit 
//data-ajax-add-url during add Important
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
		    <!--<form id="" onsubmit="return false;">-->
		  <form id="<?=$form_id?>_form" onsubmit="return false;"  enctype="multipart/form-data">
			<!--begin::Modal dialog-->
			<div class="modal-dialog modal-dialog-centered mw-900px">
				<!--begin::Modal content-->
				<div class="modal-content">
					<!--begin::Modal header-->
					<div class="modal-header">
						<!--begin::Modal title-->
						<h2>Audit Details</h2>
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
  <label class="required fs-5 fw-bold mb-2">Location</label>
  	<select class="form-select form-select-solid"  id="location" name="location" onchange="update_div_dep(this)">
		<option value="">Select Location</option>
		<?php foreach($location_list as $row){ ?>
		<option value="<?=$row['location_name']?>" data-location-id="<?=$row['location_id']?>"><?=$row['location_name']?></option>
		<?php } ?>
		</select>
  
</div>

<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Div Dep</label>
  	<select class="form-select form-select-solid"  id="devision_dept" name="devision_dept">
		<option value="">Select Action</option>
		<?php foreach($location_list_dep as $row){ ?>
		<option class="locations location_id_<?=$row['location_id']?>" value="<?=$row['department_name']?>"><?=$row['department_name']?></option>
		<?php } ?>
		</select>
</div>


	<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Sixs Category</label>
  <select class="form-select form-select-solid"  id="sixs_category" name="sixs_category">
		<option value="">Select Category</option>
		<?php foreach($location_list_cat as $row){ ?>
		<option value="<?=$row['name']?>"><?=$row['name']?></option>
		<?php } ?>
		</select>
</div>
        
<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Nc Description</label>
  <input
    type="text"
    class="form-control"
    id="nc_description"
    placeholder="Enter Nc Description"
    name="nc_description"
  />
</div>

<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Nc Photo</label>
  <input type="file" class="form-control" id="nc_photo" placeholder="Add Nc Photo" name="nc_photo" accept=".jpg,.jpeg,.png,.gif,.pdf" onchange="validateFileType(this)" />
    <input type="hidden" name="nc_photo_url" id="nc_photo_url">
</div>

<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Action Required Dep</label>
  	<select class="form-select form-select-solid"  id="action_required_dep" name="action_required_dep">
		<option value="">Select Action</option>
		<?php foreach($location_list_dep as $row){ ?>
		<option value="<?=$row['department_name']?>"><?=$row['department_name']?></option>
		<?php } ?>
		</select>
</div>


<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Responsibilty</label>
  <input
    type="text"
    class="form-control"
    id="responsibilty"
    placeholder="Enter Responsibilty"
    name="responsibilty"
  />
</div>

	<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Target Date</label>
  <input
    type="date"
    class="form-control"
    id="target_date"
    placeholder="Enter Target Date"
    name="target_date"
  />
</div>



	<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Details Of Ca Pa</label>
  <input
    type="text"
    class="form-control"
    id="details_of_ca_pa"
    placeholder="Enter Details Of Ca Pa"
    name="details_of_ca_pa"
  />
</div>

	<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">After Photo</label>
  <input type="file" class="form-control" id="after_photo" placeholder="Enter After Photo" name="after_photo" accept=".jpg,.jpeg,.png,.gif,.pdf" onchange="validateFileType(this)"/>
      <input type="hidden" name="after_photo_url" id="after_photo_url">

</div>


	<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Final Status Soi</label>
  <input
    type="text"
    class="form-control"
    id="final_status_soi"
    placeholder="Enter Final Status Soi"
    name="final_status_soi"
  />
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


function update_div_dep(obj){
    $(".locations").hide();
    $(".location_id_"+$(obj).find("option:selected").data("location-id")).show()
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

                                    $("#<?=$form_id?>").find("#location").val(responce.data.location);
                                    $("#<?=$form_id?>").find("#nc_description").val(responce.data.nc_description);
                                    $("#<?=$form_id?>").find("#devision_dept").val(responce.data.devision_dept);
                                    $("#<?=$form_id?>").find("#nc_photo").attr("src",responce.data.nc_photo);
                                    // $("#<?=$form_id?>").find("#nc_photo").val(responce.data.nc_photo);
                                    $("#<?=$form_id?>").find("#action_required_dep").val(responce.data.action_required_dep);
                                    $("#<?=$form_id?>").find("#responsibilty").val(responce.data.responsibilty);
                                    $("#<?=$form_id?>").find("#target_date").val(responce.data.target_date);
                                    $("#<?=$form_id?>").find("#sixs_category").val(responce.data.sixs_category);
                                    $("#<?=$form_id?>").find("#details_of_ca_pa").val(responce.data.details_of_ca_pa);
                                    $("#<?=$form_id?>").find("#after_photo").attr("src",responce.data.after_photo);
                                    $("#<?=$form_id?>").find("#department_status").val(responce.data.department_status);
                                    $("#<?=$form_id?>").find("#final_status_soi").val(responce.data.final_status_soi);
                                    $("#<?=$form_id?>").find("#nc_photo_url").val(responce.data.nc_photo);
                                    $("#<?=$form_id?>").find("#after_photo_url").val(responce.data.after_photo_url);



                                   
                                    
                                                                
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
<?php 				$this->endSection();?>

