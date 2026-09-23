
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
		<a><?=isset($title)?$title:"Client"?></a>
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
						<h2>Location Details</h2>
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
										<label class="required fs-5 fw-bold-2">Location Name</label>
										<input type="text" class="form-control form-control-solid" placeholder="Location name"  id="location_name" name="location_name">
										<div id="location_name_edit_note" class="text-warning fs-7 mt-1 font-weight-bold" style="display: none;">
											<strong>Note:</strong> Changing the Client Name, Location, Account Manager, or Cluster Manager may update the corresponding details in dependent audit records.
										</div>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
								
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Cluster Name</label>
										<select class="form-select form-select-solid" id="cluster_name" name="cluster_name">
										    <option value="" data-cluster-id="0">Select Cluster</option>
										    <?php foreach($cluster_list as $row){ ?>
										    <option value="<?=$row['cluster_name']?>" data-cluster-id="<?=$row['cluster_id']?>"><?=$row['cluster_name']?></option>
										        <?php } ?>
										<input type="hidden" class="form-control form-control-solid" placeholder="cluster id" id="cluster_id" name="cluster_id">
										<div class="fv-plugins-message-container invalid-feedback">
										    
										</div>
									</div>
									
										<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Region</label>
										<select class="form-select form-select-solid" id="region_name" name="region_name">
										    <option value="">Select Region</option>
										    <?php if(isset($region_list)) { foreach($region_list as $r) { ?>
										    <option value="<?=$r['region_name']?>"><?=$r['region_name']?></option>
										    <?php } } ?>
										 </select> 
										 <div class="fv-plugins-message-container invalid-feedback">
										    
										</div>
									</div>
									
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Country Name</label>
										<select class="form-select form-select-solid" id="country_name" name="country_name">
										    <option value="" data-country-id="0">Select country</option>
										    <?php foreach($country_list as $row){ 
										        $selected = '';
										        if (isset($country_name) && $country_name != '') {
										            if (strtolower($country_name) == strtolower($row['country_name'])) {
										                $selected = 'selected="selected"';
										            }
										        } else {
										            if (strtolower($row['country_name']) == 'india') {
										                $selected = 'selected="selected"';
										            }
										        }
										    ?>
										    <option value="<?=$row['country_name']?>" data-country-id="<?=$row['country_id']?>" <?=$selected?>><?=$row['country_name']?></option>
										        <?php } ?>
										</select>
										<input type="hidden" class="form-control form-control-solid" placeholder="country id" id="country_id" name="country_id">
										<div class="fv-plugins-message-container invalid-feedback">
										    
										</div>
									</div>
									
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Audit Type</label>
										<select class="form-select form-select-solid" id="audit_type" name="audit_type">
										    <option value="">Select Audit Type</option>
										    <option value="OE">OE</option>
										    <option value="HSE">HSE</option>
										    <option value="OE and HSE">OE and HSE</option>
										 </select> 
										 <div class="fv-plugins-message-container invalid-feedback">
										    
										</div>
									</div>
									
                                    <div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class=" fs-5 fw-bold-2">Account Manager</label>
										<select class="form-select form-select-solid"  id="account_manager" name="account_manager">
										    <option value="">Select Account Manager</option>
										    <?php if(isset($account_managers)): ?>
                                                <?php foreach($account_managers as $am): ?>
                                                <option value="<?=esc($am['user_name'])?>"><?=esc($am['user_name'])?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
										</select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
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

//$("#kt_datepicker_1").flatpickr();
//$.ajax()
const form = document.getElementById('<?=$form_id?>_form');
var validation_object  = {
        fields: {
            'location_name': {
                validators: {
                    notEmpty: {
                        message: 'Location Name is required'
                    }
                }
            },
            'cluster_name': {
                validators: {
                    notEmpty: {
                        message: 'Cluster Name is required'
                    }
                }
            },
            'country_name': {
                validators: {
                    notEmpty: {
                        message: 'country Name is required'
                    }
                }
            },
            'audit_type': {
                validators: {
                    notEmpty: {
                        message: 'Audit Type is required'
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
var originalLocationData = null;
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
                    if (field.name === 'options[]') {
                        if (!formData['options[]']) {
                            formData['options[]'] = [];
                        }
                        formData['options[]'].push(field.value);
                    } else {
                        if (field.value.trim() !== "") {
                            formData[field.name] = field.value;
                        }
                    }
                });

                function performLocationSubmit() {
                    ajax_call(url,formData,button,function(responce){
                        try{
                            responce = JSON.parse(responce);
                            if(responce.status==1){
                                    toastr.success(responce.message);
                                    form.reset();
                                    originalLocationData = null;
                                    $("#location_name_edit_note").hide();
                                    $("#<?=$form_id?>").modal("hide");
                                    reload_data_table();
                            }else{
                                    toastr.warning(responce.message);
                                }
                            }catch(error){
                                toastr.error(error);
                            }
                    });
                }

                var isChanged = false;
                if (originalLocationData) {
                    var curName = $("#<?=$form_id?>").find("#location_name").val().trim();
                    var curCluster = $("#<?=$form_id?>").find("#cluster_name").val();
                    var curAm = $("#<?=$form_id?>").find("#account_manager").val();

                    if ((originalLocationData.location_name || '') !== curName ||
                        (originalLocationData.cluster_name || '') !== curCluster ||
                        (originalLocationData.account_manager || '') !== curAm) {
                        isChanged = true;
                    }
                }

                if (isChanged) {
                    var confirmMsg = "This change will update the corresponding details in dependent audit records. Do you want to continue?";
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: confirmMsg,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Update',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                performLocationSubmit();
                            }
                        });
                    } else {
                        if (confirm(confirmMsg)) {
                            performLocationSubmit();
                        }
                    }
                } else {
                    performLocationSubmit();
                }

                }else{
                    // not validate 
                }
        });
        }
    
});

$('#<?=$button_id?>').on('hidden.bs.modal', function () {
    originalLocationData = null;
    $("#location_name_edit_note").hide();
    $("#<?=$form_id?>_form")[0].reset();
    $("#<?=$form_id?>").find("#country_name").val("India");
    $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-url", $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-add-url"));
});
$('#<?=$button_id?>').on('show.bs.modal', function () {
    if (!$("#<?=$form_id?>").find("#country_name").val()) {
        $("#<?=$form_id?>").find("#country_name").val("India");
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
                                    
                                    originalLocationData = {
                                        location_name: responce.data.location_name || '',
                                        cluster_name: responce.data.cluster_name || '',
                                        account_manager: responce.data.account_manager || ''
                                    };
                                    $("#<?=$form_id?>").find("#location_name").val(responce.data.location_name);
                                    $("#location_name_edit_note").show();
                                    $("#<?=$form_id?>").find("#cluster_name").val(responce.data.cluster_name);
                                    $("#<?=$form_id?>").find("#country_name").val(responce.data.country_name);
                                    $("#<?=$form_id?>").find("#region_name").val(responce.data.region_name);
                                    $("#<?=$form_id?>").find("#audit_type").val(responce.data.audit_type);
                                    $("#<?=$form_id?>").find("#account_manager").val(responce.data.account_manager);
                                                                
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
