<?php 
$form_id= $button_id;
//data-ajax-url during add/edit 
//data-ajax-add-url during add Important
?>
<?php $this->extend("Layout/base_admin"); ?>
<?php $this->section("breadcrumb_title_li"); ?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"Client"?></a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>
<?php 
// changes on 16/10/25 by darsh: Using simple ACL helper
helper('simple_acl');
// Hide audit type filter buttons for Cluster Managers (read-only access)
if(canCreateAudit()): ?>
<div class="row g-5 g-xl-8">
    <div class="col-xl-3">
        <a href="<?= base_url('Masters/Audit_template/template_type_filter/OE'); ?>" class="card bg-primary hoverable mb-xl-8">
            <!--begin::Body-->
            <div class="card-body" style="padding: 1rem 2.25rem;">
               
                <div class="fw-semibold text-gray-100">OE</div>
            </div>
        </a>
    </div>
    
    <div class="col-xl-3">
        <a href="<?= base_url('Masters/Audit_template/template_type_filter/HSE'); ?>" class="card bg-dark hoverable mb-xl-8">
            <div class="card-body" style="padding: 1rem 2.25rem;">
                <div class="fw-semibold text-gray-100">HSE </div>
            </div>
        </a>
    </div>
    
    <!-- <div class="col-xl-3">
        <a href="<?= base_url('Masters/Audit_template/template_type_filter/Normal'); ?>" class="card bg-warning hoverable mb-xl-8">
            <div class="card-body" style="padding: 1rem 2.25rem;">
                <div class="fw-semibold text-white">Normal </div>
            </div>
        </a>
    </div> -->

    <div class="col-xl-3">
        
        <a href="<?= base_url('Masters/Audit_template'); ?>" class="card bg-info hoverable mb-5 mb-xl-8">
            <div class="card-body" style="padding: 1rem 2.25rem;">
                <div class="fw-semibold text-white">All </div>
            </div>
        </a>    
    </div>
</div>
<?php else: ?>
<!-- changes on 16/10/25 by darsh: Show simple ACL message for Cluster Managers -->
<?= showACLMessage('info'); ?>
<?php endif; ?>
<?=$table?>
<?php $this->endSection(); ?>

<?php  $this->section("modals_section"); ?>
 <style>
      #result {
            /*border: 1px solid #ccc;*/
            /*max-width: 300px;*/
            /*margin-top: 5px;*/
            /*position: absolute;*/
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
		<!--begin::Modal - Create App-->
		<div class="modal fade" id="<?=$button_id?>" tabindex="-1" aria-hidden="true">
		    <form id="<?=$form_id?>_form" enctype="multipart/form-data" method="post">
			<!--begin::Modal dialog-->
			<div class="modal-dialog modal-dialog-centered mw-900px">
				<!--begin::Modal content-->
				<div class="modal-content">
					<!--begin::Modal header-->
					<div class="modal-header">
						<!--begin::Modal title-->
						<h2>Create Audit Template</h2>
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
										<label class="required fs-5 fw-bold mb-2">Name Of Audit</label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter audit name" id="audit_name" name="audit_name">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Template Type</label>
										<!--<input type="text" class="form-control form-control-solid" placeholder="Please enter auditor name" id="auditor_name" name="auditor_name">-->
										<select class="form-control form-control-solid" id="audit_template_type" name="audit_template_type">
										    <option value='OE'>OE</option>
										    <option value='HSE'>HSE</option>
										    <!-- <option value='Normal'>Normal</option> -->
										</select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Template Owner</label>
										<!--<input type="text" class="form-control form-control-solid" placeholder="Please enter auditor name" id="auditor_name" name="auditor_name">-->
										<select class="form-control form-control-solid" id="auditor_name" name="auditor_name">
										     <option value='' selected>Select Auditor</option>
            							    <?php foreach($auditor_list as $row){ ?>
            							    <option value='<?=$row['user_name'] ?>' ><?= $row['user_name'] ?></option>
            							    <?php } ?>
										</select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									<!--<div class="col-md-12 fv-row fv-plugins-icon-container">-->
									<!--	<label class="required fs-5 fw-bold mb-2">Name Of Auditee</label>-->
									<!--	<input type="text" class="form-control form-control-solid" placeholder="Please enter auditee name" id="auditee_name" name="auditee_name">-->
									<!--	<div class="fv-plugins-message-container invalid-feedback"></div>-->
									<!--</div>-->
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Date</label>
										<input type="date" class="form-control form-control-solid"  id="date" name="date">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									<!--<div class="col-md-12 fv-row fv-plugins-icon-container">-->
									<!--	<label class="required fs-5 fw-bold mb-2">Client Name</label>-->
									<!--	<input type="text" class="form-control form-control-solid" placeholder="Please enter client name" id="client_name" name="client_name">-->
									<!--	<div class="fv-plugins-message-container invalid-feedback"></div>-->
									<!--</div>-->
									<!--<div class="col-md-12 fv-row fv-plugins-icon-container">-->
									<!--	<label class="required fs-5 fw-bold mb-2">Region</label>-->
									<!--	<input type="text" class="form-control form-control-solid" placeholder="Please enter Region" id="region" name="region">-->
									<!--	<div class="fv-plugins-message-container invalid-feedback"></div>-->
									<!--</div>-->
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Frequency</label>
										<input type="text" class="form-control form-control-solid" placeholder="Please enter fequency" id="frequency" name="frequency">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									<!--<div class="col-md-12 fv-row fv-plugins-icon-container">-->
									<!--	<label class="required fs-5 fw-bold mb-2">Next Date Of Audit</label>-->
									<!--	<input type="date" class="form-control form-control-solid"  id="next_date" name="next_date">-->
									<!--	<div class="fv-plugins-message-container invalid-feedback"></div>-->
									<!--</div>-->
									<!--<div class="col-md-12 fv-row fv-plugins-icon-container">-->
									<!--	<label class="required fs-5 fw-bold mb-2">Location</label>-->
									<!--	<input type="text" class="form-control form-control-solid" placeholder="Please Search Location" id="location" name="location">-->
									<!--	<div class="fv-plugins-message-container invalid-feedback"></div>-->
									<!--	<div id="result" class="col-md-6"></div>-->
									<!--</div>-->
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Import Excel</label>
										<input type="file" class="form-control form-control-solid"  id="emport_excel" name="emport_excel" accept=".csv,.xls,.xlsx,.pdf,.jpg,.jpeg,.png,.gif" onchange="validateFileType(this)">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
										<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold mb-2">Score</label>
										<input type="text" class="form-control form-control-solid"  id="score" name="score" value="100">
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
							<button type="button" id="ajax_click" data-ajax-add-url="<?=$ajax_url?>" class="btn btn-primary">
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
        const form = document.getElementById('<?=$form_id?>_form');

var validation_object  = {
    fields: {
        'audit_name': { validators: { notEmpty: { message: 'Audit Name is required' } } },
        'auditor_name': { validators: { notEmpty: { message: 'Auditor Name is required' } } },
        'date': { validators: { notEmpty: { message: 'Date is required' } } },
        'frequency': { validators: { notEmpty: { message: 'Frequency is required' } } },
        'emport_excel': { validators: { notEmpty: { message: 'Excel file is required' } } },
        'score': { validators: { notEmpty: { message: 'Score is required' } } }
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

var validator = FormValidation.formValidation(form, validation_object);

// document.getElementById("ajax_click").addEventListener("click", function() {
//     validator.validate().then(function(status) {
//         if (status === 'Valid') {
//             debugger;
//             let formData = new FormData(form);
//             let url = document.getElementById("ajax_click").getAttribute("data-ajax-add-url");

//             fetch(url, {
//                 method: "POST",
//                 body: formData
//             })
//             .then(res => res.text())
//             .then(data => {
//                 console.log(data);
//                alert("Audit Template saved successfully!");
//                 location.reload();
//             })
//             .catch(err => {
//                 console.error(err);
//                 alert("Error while saving!");
//             });
//         }
//     });
// });

document.getElementById("ajax_click").addEventListener("click", function () {
    let button = this;

    validator.validate().then(function (status) {
        if (status === 'Valid') {

            let formData = new FormData(form);
            let url = button.getAttribute("data-ajax-url") || button.getAttribute("data-ajax-add-url");

            button.setAttribute("data-kt-indicator", "on");
            button.disabled = true;

            fetch(url, {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(res => res.json())
            .then(res => {
                button.removeAttribute("data-kt-indicator");
                button.disabled = false;

                if (res.status == "1") {

                    let title = "Success";
                    let htmlMessage = res.message;

                    // Special clean display only for HSE
                    if (res.audit_type === "HSE") {
                        title = "HSE Import Completed";

                        htmlMessage = res.message
                            .replace("HSE Import Completed Successfully.", "<b>HSE Import Completed Successfully</b><br><br>")
                            .replace("CSV Rows:", "<b>CSV Rows:</b>")
                            .replace("Inserted:", "<br><b>Inserted:</b>")
                            .replace("Updated:", "<br><b>Updated:</b>")
                            .replace("Skipped Blank Rows:", "<br><b>Skipped Blank Rows:</b>")
                            .replace("Invalid:", "<br><b>Invalid Rows:</b>")
                            .replace("Total Processed:", "<br><b>Total Processed:</b>");
                    }

                    Swal.fire({
                        icon: "success",
                        title: title,
                        html: `<div style="text-align:left;">${htmlMessage}</div>`,
                        confirmButtonText: "OK"
                    }).then(() => {
                        location.reload();
                    });

                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Import Failed",
                        html: `<div style="text-align:left;">${res.message || "Something went wrong!"}</div>`,
                        confirmButtonText: "OK"
                    });
                }
            })
            .catch(err => {
                button.removeAttribute("data-kt-indicator");
                button.disabled = false;

                console.error("Fetch Error:", err);

                Swal.fire({
                    icon: "error",
                    title: "Something went wrong!",
                    text: "Server error or invalid response.",
                    confirmButtonText: "OK"
                });
            });

        }
    });
});
   
        function edit_id(obj,id,action='edit'){
            var url = $(obj).attr("data-ajax-url");
            var formData={"id":id};
            ajax_call(url,formData,$(obj),function(responce){
                                try{
                                    responce = JSON.parse(responce);
                                    if(responce.status==1){
                                            toastr.success(responce.message);
                                            //form.reset();
                                            // Debug: log the response data
                                            console.log('Form data received:', responce.data);
                                            // Clear any existing file notes first
                                            $("#<?=$form_id?>").find("#emport_excel").next('small').remove();
                                            
                                            // Set form values with a small delay to ensure form is ready
                                            setTimeout(function() {
                                                // to update value 
                                                $("#<?=$form_id?>").find("#audit_name").val(responce.data.audit_name);
                                                // Set auditor name and debug
                                                console.log('Setting auditor_name to:', responce.data.auditor_name);
                                                $("#<?=$form_id?>").find("#auditor_name").val(responce.data.auditor_name);
                                                console.log('Auditor name field value after setting:', $("#<?=$form_id?>").find("#auditor_name").val());
                                                $("#<?=$form_id?>").find("#audit_template_type").val(responce.data.audit_template_type);
                                                // $("#<?=$form_id?>").find("#auditee_name").val(responce.data.auditee_name);
                                                $("#<?=$form_id?>").find("#date").val(responce.data.date);
                                                // $("#<?=$form_id?>").find("#client_name").val(responce.data.client_name);
                                                // $("#<?=$form_id?>").find("#region").val(responce.data.region);
                                                $("#<?=$form_id?>").find("#frequency").val(responce.data.frequency);
                                                // $("#<?=$form_id?>").find("#next_date").val(responce.data.next_date);
                                                // $("#<?=$form_id?>").find("#location").val(responce.data.location);
                                                
                                                // Handle Excel file - show current file name if exists
                                                if(responce.data.import_excel && responce.data.import_excel !== '') {
                                                    // Add a note about the current file since we can't pre-fill file inputs
                                                    var fileNote = '<small class="text-muted d-block mt-1">Current file: ' + responce.data.import_excel + '</small>';
                                                    $("#<?=$form_id?>").find("#import_excel").after(fileNote);
                                                }
                                                
                                                $("#<?=$form_id?>").find("#score").val(responce.data.score);
                                            }, 100);
                                            // $("#<?=$form_id?>").find("#auditee_name").val(responce.data.auditee_name);
        
        
                                        
                                            // Set URL based on action type
                                            if(action === 'reaudit') {
                                                // For reaudit, we want to create a new record, not update existing one
                                                $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-url",$("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-add-url"));
                                            } else if(action === 'report') {
                                                // For report, we want to update the existing record
                                                $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-url",$("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-add-url")+"/"+id);
                                                // Make form read-only for report
                                                $("#<?=$form_id?>").find("input, select").prop("readonly", true);
                                                $("#<?=$form_id?>").find("#ajax_click").hide();
                                            } else {
                                                // For edit, we want to update the existing record
                                                $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-url",$("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-add-url")+"/"+id);
                                            }
                                            $("#<?=$form_id?>").modal("show");
                                            
                                            // Ensure form is ready after modal is shown
                                            $("#<?=$form_id?>").on('shown.bs.modal', function() {
                                                // Additional check to ensure values are set after modal is fully shown
                                                setTimeout(function() {
                                                    console.log('Modal shown, re-checking auditor_name value:', $("#<?=$form_id?>").find("#auditor_name").val());
                                                }, 200);
                                            });
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
            const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xls', 'xlsx', 'csv'];
            const maxSize = 10 * 1024 * 1024; // 10MB
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const extension = file.name.split('.').pop().toLowerCase();
                
                // Check file type
                if (!allowedTypes.includes(extension)) {
                    alert('Invalid file type. Only images (JPG, PNG, GIF), PDFs, and Excel files (XLS, XLSX,) are allowed.');
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
</script>
<?php 				$this->endSection();?>

