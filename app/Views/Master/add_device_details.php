
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
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary">Device Details</a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>
<?=$table?>
<?php $this->endSection(); ?>

<?php  $this->section("modals_section"); ?>

		<!--begin::Modal - Create App-->
		<div class="modal fade" id="<?=$button_id?>" tabindex="-1" aria-hidden="true">
		    <form id="<?=$form_id?>_form" onsubmit="return false;"  enctype="multipart/form-data">
			<!--begin::Modal dialog-->
			<div class="modal-dialog modal-dialog-centered mw-900px">
				<!--begin::Modal content-->
				<div class="modal-content">
					<!--begin::Modal header-->
					<div class="modal-header">
						<!--begin::Modal title-->
						<h2>Device Details</h2>
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
								
									
									<div class="col-md-3 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Device Name </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter Name" id="device_name" name="device_name">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
								<div class="col-md-3 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Publisher </label>
										<!--<input type="text" class="form-control form-control-solid" placeholder="Please enter orientation" id="orientation" name="orientation">-->
										<select class="form-select form-select-solid" aria-label="Select Publisher Type" id="user_id" name="user_id">
                                           <?php 
                                           foreach($user_list as $row){
                                           ?>
                                                <option value="<?=$row['user_id']?>"><?=$row['first_name']." ".$row['last_name']." (".$row['company_name'].")"?>
                                           <?php } ?>
                                            </select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-3 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2"> Device Id </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter Id" id="device_id" name="device_id">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									<div class="col-md-3 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Screen Details</label>
										<!--<input type="text" class="form-control form-control-solid" placeholder="Please enter orientation" id="orientation" name="orientation">-->
										<select class="form-select form-select-solid" aria-label="Select Screen" id="screen_id" name="screen_id">
                                            <option value="0">Select Screen</option>
                                            <?php foreach($screen_list as $row){ ?>
                                             <option value="<?=$row['screen_id']?>"><?=$row['screen_name']?></option>
                                            <?php } ?>
                                        </select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Screen Location </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter Screen Location" id="screen_location" name="screen_location">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-3 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Location Type </label>
										<!--<input type="text" class="form-control form-control-solid" placeholder="Please enter orientation" id="orientation" name="orientation">-->
										<select class="form-select form-select-solid" aria-label="Select Location Type" id="location_type" name="location_type">
                                            <option>Select Location Type</option>
                                            <option value="In Door">In Door</option>
                                            <option value="Out Door">Out Door</option>
                                            </select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-3 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Latitude </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter latitude" id="latitude" name="latitude">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-3 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Longitude </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter longitude" id="longitude" name="longitude">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-3 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Screen Size </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter Screen Size" id="screen_size" name="screen_size">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-4 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Orientation </label>
										<!--<input type="text" class="form-control form-control-solid" placeholder="Please enter orientation" id="orientation" name="orientation">-->
										<select class="form-select form-select-solid" aria-label="Select orientation" id="orientation" name="orientation">
                                            <option>Select Orientation</option>
                                            <option value="Horizontal">Horizontal</option>
                                            <option value="Vertical">Vertical</option>
                                            </select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-4 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Resolution </label>
										<!--<input type="text" class="form-control form-control-solid" placeholder="Please enter resolution" id="resolution" name="resolution">-->
											<select class="form-select form-select-solid" aria-label="Select resolution" id="resolution" name="resolution">
                                            <option>Select Resolution</option>
                                            <option value="800x600"> 800 x 600 </option>
                                            <option value="1280x720">1280 x 720</option>
                                            <option value="1440x900">1440 x 900</option>
                                            <option value="1600x900">1600 x 900</option>
                                            <option value="1920x1080">1920 x 1080</option>
                                            <option value="1900x1200">1900 x 1200</option>
                                            <option value="2880x1800">2880 x 1800</option>
                                            <option value="3840x2160">3840 x 2160</option>
                                            </select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-4 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Aspect Ratio </label>
										<!--<input type="text" class="form-control form-control-solid" placeholder="Please enter Aspect Ratio" id="aspect_ratio" name="aspect_ratio">-->
											<select class="form-select form-select-solid" aria-label="Select orientation" id="aspect_ratio" name="aspect_ratio">
                                            <option>Select Aspect Ratio</option>
                                            <option value="720p">720 P</option>
                                            <option value="1080p">1080 P</option>
                                            <option value="2K">2 K</option>
                                            <option value="3K">3 K</option>
                                            <option value="3KUHD">3 K UHD</option>
                                            <option value="4K">4 K</option>
                                            <option value="4KUHD"> 4 K UHD</option>
                                            </select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-4 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Monthly Footfall </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter Monthly Footfall" id="monthly_footfall" name="monthly_footfall">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-4 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Daily Impression </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter Monthly Footfall" id="daily_impression" name="daily_impression">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-4 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Cost for Impression </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter Monthly Footfall" id="cost_for_impression" name="cost_for_impression">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-4 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Languages </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter Languages" id="languages" name="languages">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-4 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">State </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter State" id="state" name="state">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
								    
								    <div class="col-md-4 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">City </label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter City" id="city" name="city">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									 <div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2"> Device Image </label>
										<input type="file" class="form-control form-control-solid" placeholder="Please enter City" id="device_image" name="device_image" accept=".jpg,.jpeg,.png,.gif,.pdf" onchange="validateFileType(this)">
										<input type="hidden" name="device_image_url" id="device_image_url">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<br>
									<hr>
									<h3>Opening Hours</h3>
									
									<hr>
									
									<div class="col-md-2 fv-row fv-plugins-icon-container">
										
										<h4>Monday</h4>
										
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										    <label class="required fs-5 fw-bold mb-2">Start Time </label>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="monday_start_time" name="monday_start_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										<span>End Time</span>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="monday_end_time" name="monday_end_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
									
									<hr>
									
									<div class="col-md-2 fv-row fv-plugins-icon-container">
										
										<h4>Tuesday</h4>
										
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										    <label class="required fs-5 fw-bold mb-2">Start Time </label>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="tuesday_start_time" name="tuesday_start_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										<span>End Time</span>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="tuesday_end_time" name="tuesday_end_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
									
									<hr>
									
									<div class="col-md-2 fv-row fv-plugins-icon-container">
										
										<h4>Wednesday</h4>
										
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										    <label class="required fs-5 fw-bold mb-2">Start Time </label>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="wednesday_start_time" name="wednesday_start_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										<span>End Time</span>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="wednesday_end_time" name="wednesday_end_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
									
									<hr>
									
									<div class="col-md-2 fv-row fv-plugins-icon-container">
										
										<h4>Thursday</h4>
										
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										    <label class="required fs-5 fw-bold mb-2">Start Time </label>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="thursday_start_time" name="thursday_start_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										<span>End Time</span>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="thursday_end_time" name="thursday_end_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
									
									<hr>
									
									<div class="col-md-2 fv-row fv-plugins-icon-container">
										
										<h4>Friday</h4>
										
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										    <label class="required fs-5 fw-bold mb-2">Start Time </label>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="friday_start_time" name="friday_start_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										<span>End Time</span>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="friday_end_time" name="friday_end_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
								    
								    <hr>
									
									<div class="col-md-2 fv-row fv-plugins-icon-container">
										
										<h4>Saturday</h4>
										
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										    <label class="required fs-5 fw-bold mb-2">Start Time </label>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="saturday_start_time" name="saturday_start_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										<span>End Time</span>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="saturday_end_time" name="saturday_end_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>

								    <hr>
									
									<div class="col-md-2 fv-row fv-plugins-icon-container">
										
										<h4>Sunday</h4>
										
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										    <label class="required fs-5 fw-bold mb-2">Start Time </label>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="sunday_start_time" name="sunday_start_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
									
									<div class="col-md-5 fv-row fv-plugins-icon-container">
										<div class="row">
										<div class="col-md-5 fv-row fv-plugins-icon-container">
										<span>End Time</span>
										</div>
										<div class="col-md-7 fv-row fv-plugins-icon-container">
										<input type="time" class="form-control form-control-solid" placeholder="" id="sunday_end_time" name="sunday_end_time">
										<div class="fv-plugins-message-container invalid-feedback"></div>
										</div>
										</div>
									</div>
									
									<hr>
									
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
            'device_id': {
                validators: {
                    notEmpty: {
                        message: 'Id is required'
                    }
                }
            },
            'device_name': {
                validators: {
                    notEmpty: {
                        message: 'Name is required'
                    }
                }
            },
            'location_type': {
                validators: {
                    notEmpty: {
                        message: 'Location Type is required'
                    }
                }
            },
            'screen_location': {
                validators: {
                    notEmpty: {
                        message: 'Location is required'
                    }
                }
            },
             'latitude': {
                validators: {
                    notEmpty: {
                        message: 'Latitude is required'
                    }
                }
            },
             'longitude': {
                validators: {
                    notEmpty: {
                        message: 'Longitude is required'
                    }
                }
            },
             'screen_size': {
                validators: {
                    notEmpty: {
                        message: 'Screen Size is required'
                    }
                }
            },
             'orientation': {
                validators: {
                    notEmpty: {
                        message: 'Orientation is required'
                    }
                }
            },
             'resolution': {
                validators: {
                    notEmpty: {
                        message: 'Resolution is required'
                    }
                }
            },
             'aspect_ratio': {
                validators: {
                    notEmpty: {
                        message: 'Aspect Ratio is required'
                    }
                }
            },
             'monthly_footfall': {
                validators: {
                    notEmpty: {
                        message: 'Footfall is required'
                    }
                }
            },
             'daily_impression': {
                validators: {
                    notEmpty: {
                        message: 'Daily Impression is required'
                    }
                }
            },
             'cost_for_impression': {
                validators: {
                    notEmpty: {
                        message: 'Cost for Impression is required'
                    }
                }
            },
             'languages': {
                validators: {
                    notEmpty: {
                        message: 'Languages is required'
                    }
                }
            },
            'city': {
                validators: {
                    notEmpty: {
                        message: 'City is required'
                    }
                }
            },
            'state': {
                validators: {
                    notEmpty: {
                        message: 'State is required'
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
                // var formData = new FormData();
                var formData = new FormData($("#<?=$form_id?>_form")[0]);
                // console.log(formData.values());
                
                // var data_to_send =$("#<?=$form_id?>_form").serializeArray();
                // //     // var formData = {};
                //     $.each(data_to_send, function(i, field){
                //         if(field.value.trim() != ""){
                //         //   formData[field.name] = field.value;
                //             formData.append(field.name, field.value);

                //         }
                //     });
                //      var fileInput = document.getElementById('device_image');
                //         var file = fileInput.files[0];
                //         formData.append('file', file);


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
                                    $("#<?=$form_id?>").find("#device_id").val(responce.data.device_id);
                                    $("#<?=$form_id?>").find("#user_id").val(responce.data.user_id);
                                    
                                    $("#<?=$form_id?>").find("#device_name").val(responce.data.device_name);
                                    $("#<?=$form_id?>").find("#location_type").val(responce.data.location_type);
                                    $("#<?=$form_id?>").find("#screen_location").val(responce.data.screen_location);
                                    $("#<?=$form_id?>").find("#latitude").val(responce.data.latitude);
                                    $("#<?=$form_id?>").find("#longitude").val(responce.data.longitude);
                                    $("#<?=$form_id?>").find("#screen_size").val(responce.data.screen_size);
                                    $("#<?=$form_id?>").find("#orientation").val(responce.data.orientation);
                                    $("#<?=$form_id?>").find("#resolution").val(responce.data.resolution);
                                    $("#<?=$form_id?>").find("#aspect_ratio").val(responce.data.aspect_ratio);
                                    $("#<?=$form_id?>").find("#monthly_footfall").val(responce.data.monthly_footfall);
                                    $("#<?=$form_id?>").find("#daily_impression").val(responce.data.daily_impression);
                                    $("#<?=$form_id?>").find("#cost_for_impression").val(responce.data.cost_for_impression);
                                    $("#<?=$form_id?>").find("#languages").val(responce.data.languages);
                                    $("#<?=$form_id?>").find("#city").val(responce.data.city);
                                    $("#<?=$form_id?>").find("#state").val(responce.data.state);
                                    $("#<?=$form_id?>").find("#device_image").attr("src",responce.data.device_image);
                                    $("#<?=$form_id?>").find("#monday_start_time").val(responce.data.monday_start_time);
                                    $("#<?=$form_id?>").find("#monday_end_time").val(responce.data.monday_end_time);
                                    $("#<?=$form_id?>").find("#tuesday_start_time").val(responce.data.tuesday_start_time);
                                    $("#<?=$form_id?>").find("#tuesday_end_time").val(responce.data.tuesday_end_time);
                                    $("#<?=$form_id?>").find("#wednesday_start_time").val(responce.data.wednesday_start_time);
                                    $("#<?=$form_id?>").find("#wednesday_end_time").val(responce.data.wednesday_end_time);
                                    $("#<?=$form_id?>").find("#thursday_start_time").val(responce.data.thursday_start_time);
                                    $("#<?=$form_id?>").find("#thursday_end_time").val(responce.data.thursday_end_time);
                                    $("#<?=$form_id?>").find("#friday_start_time").val(responce.data.friday_start_time);
                                    $("#<?=$form_id?>").find("#friday_end_time").val(responce.data.friday_end_time);
                                    $("#<?=$form_id?>").find("#saturday_start_time").val(responce.data.saturday_start_time);
                                    $("#<?=$form_id?>").find("#saturday_end_time").val(responce.data.saturday_end_time);
                                    $("#<?=$form_id?>").find("#sunday_start_time").val(responce.data.sunday_start_time);
                                    $("#<?=$form_id?>").find("#sunday_end_time").val(responce.data.sunday_end_time);
                                    $("#<?=$form_id?>").find("#screen_id").val(responce.data.screen_id);
                                    $("#<?=$form_id?>").find("#device_image_url").val(responce.data.device_image);
                                    

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

