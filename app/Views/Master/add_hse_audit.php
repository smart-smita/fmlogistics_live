
<?php 
//   $form_id= $form_id;
//data-ajax-url during add/edit 
//data-ajax-add-url during add Important
?>
<?php $this->extend("Layout/base_admin"); ?>
<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a><?=isset($title)?$title:"Client"?></a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>
<div class="row g-5 g-xl-8">
    <div class="col-md-4">
        <a href="<?= base_url('Masters/Hse_audit/template_type_filter/No NC'); ?>" class="card bg-primary hoverable mb-xl-8">
            <!--begin::Body-->
            <div class="card-body" style="padding: 1rem 2.25rem;">
               
                <div class="fw-semibold text-gray-100">No NC</div>
            </div>
        </a>
    </div>
    
    <div class="col-md-4">
        <a href="<?= base_url('Masters/Hse_audit/template_type_filter/Found NC'); ?>" class="card bg-dark hoverable mb-xl-8">
            <div class="card-body" style="padding: 1rem 2.25rem;">
                <div class="fw-semibold text-gray-100">Found NC</div>
            </div>
        </a>
    </div>
    
    

    <div class="col-md-4">
        
        <a href="<?= base_url('Masters/Hse_audit/template_type_filter/All'); ?>" class="card bg-info hoverable mb-5 mb-xl-8">
            <div class="card-body" style="padding: 1rem 2.25rem;">
                <div class="fw-semibold text-white">All</div>
            </div>
        </a>    
    </div>
</div>
<?=$table?>
<?php $this->endSection(); ?>

<?php  $this->section("modals_section"); ?>

		<!--begin::Modal - Create App-->
		<div class="modal fade" id="<?=$form_id?>" tabindex="-1" aria-hidden="true">
		    <form id="<?=$form_id?>_form" onsubmit="return false;">
			<!--begin::Modal dialog-->
			<div class="modal-dialog modal-dialog-centered mw-900px">
				<!--begin::Modal content-->
				<div class="modal-content">
					<!--begin::Modal header-->
					<div class="modal-header">
						<!--begin::Modal title-->
						<h2>HSE Audit Details</h2>
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
										<label class="required fs-5 fw-bold mb-2">Audit Date</label>
										<input type="date" class="form-control form-control-solid"  id="audit_date" name="audit_date">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Auditor Name </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter Auditor Name" id="auditor_name" name="auditor_name">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Auditee Name</label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter Auditee" id="auditee_name" name="auditee_name">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Customer Name </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter customer Name" id="customer_name" name="customer_name">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Import Excel</label>
										<input type="file" class="form-control form-control-solid"  id="import_excel" name="import_excel" accept=".xls,.xlsx,.pdf,.jpg,.jpeg,.png,.gif" onchange="validateFileType(this)">
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
            'incident_name': {
                validators: {
                    notEmpty: {
                        message: 'Inncident Name is required'
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
        if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                var formData =new FormData(<?=$form_id."_form"?>);
                    
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
                                    $("#<?=$form_id?>").find("#audit_date").val(responce.data.audit_date);
                                    $("#<?=$form_id?>").find("#auditor_name").val(responce.data.auditor_name);
                                    $("#<?=$form_id?>").find("#auditee_name").val(responce.data.auditee_name);
                                    $("#<?=$form_id?>").find("#customer_name").val(responce.data.customer_name);
                                    $("#<?=$form_id?>").find("#import_excel").val(responce.data.import_excel);
                                   


                                
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
    const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xls', 'xlsx'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const extension = file.name.split('.').pop().toLowerCase();
        
        // Check file type
        if (!allowedTypes.includes(extension)) {
            alert('Invalid file type. Only images (JPG, PNG, GIF), PDFs, and Excel files (XLS, XLSX) are allowed.');
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
