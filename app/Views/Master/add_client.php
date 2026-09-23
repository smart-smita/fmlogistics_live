
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
						<h2>Client Name</h2>
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
										<label class="required fs-5 fw-bold-2">Client Name</label>
										<input type="text" class="form-control form-control-solid" placeholder="Client Name"  id="client_name" name="client_name">
										<div id="client_name_edit_note" class="text-warning fs-7 mt-1 font-weight-bold" style="display: none;">
											<strong>Note:</strong> Changing the Client Name, Location, Account Manager, or Cluster Manager may update the corresponding details in dependent audit records.
										</div>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
								    <!-- changes on 1/11/25 by darsh: Added location dropdown with manual entry and Add Location button -->
								    <div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="fs-5 fw-bold-2">Location </label>
										<input type="text" class="form-control form-control-solid" placeholder="Location" id="location" name="location">
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									 <div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold-2">Region </label>
										<select class="form-select form-select-solid"  id="region" name="region">
										    <option value="" data-location-id="0">Select Region</option>
										    <?php foreach($region_list as $row){ ?>
										    <option value="<?=$row['region_name']?>" data-location-id="<?=$row['region_id']?>"><?=$row['region_name']?></option>
										        <?php } ?>
										        </select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									 <div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="required fs-5 fw-bold-2">Cluster </label>
										<select class="form-select form-select-solid"  id="cluster" name="cluster">
										    <option value="" data-location-id="0">Select Cluster</option>
										    <?php foreach($cluster_list as $row){ ?>
										    <option value="<?=$row['cluster_name']?>" data-location-id="<?=$row['cluster_id']?>"><?=$row['cluster_name']?></option>
										        <?php } ?>
										        </select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>

                                    <div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class=" fs-5 fw-bold-2">Account Manager</label>
										<select class="form-select form-select-solid"  id="account_manager" name="account_manager">
										    <option value="" data-email="">Select Account Manager</option>
										    <?php if(isset($account_managers)): ?>
                                                <?php foreach($account_managers as $am): ?>
                                                <option value="<?=esc($am['user_name'])?>" data-email="<?=esc($am['user_email'] ?? '')?>"><?=esc($am['user_name'])?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
										</select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
                                    
									<div class="col-md-12 fv-row fv-plugins-icon-container">
										<label class="fs-5 fw-bold mb-2"> Manager E-mail </label>
										<input type="text" class="form-control form-control-solid"placeholder="Enter E-mail " id="email" name="email">
										<div class="fv-plugins-message-container invalid-feedback"></div>
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
		</div>
		<!--end::Modal - Create App-->

<!-- Deactivate Confirm Modal (Modal 1) -->
<div class="modal fade" id="deactivateConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-700px">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h2 class="text-white">Deactivate Client / Location</h2>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1"><i class="fa fa-times text-white"></i></span>
                </div>
            </div>
            <div class="modal-body">
                <div class="mb-5">
                    <h5>Client Name: <span id="deact_client_name" class="text-primary"></span></h5>
                    <h5>Client Code: <span id="deact_client_code" class="text-primary"></span></h5>
                    <h5>Client Status: <span id="deact_client_status" class="text-primary"></span></h5>
                    <h5>Location: <span id="deact_location_name" class="text-primary"></span></h5>
                </div>
                
                <hr>
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <h4 class="text-info text-center">OE NCs Breakdown</h4>
                        <table class="table table-bordered table-sm text-center">
                            <tr><td class="text-start">Open</td><td id="oe_open">0</td></tr>
                            <tr><td class="text-start">Working</td><td id="oe_work">0</td></tr>
                            <tr><td class="text-start">Under Review - CM</td><td id="oe_cr">0</td></tr>
                            <tr><td class="text-start">Under Review - Auditor</td><td id="oe_ar">0</td></tr>
                            <tr class="fw-bold bg-light"><td class="text-start">Total Open NCs</td><td id="oe_total">0</td></tr>
                        </table>
                    </div>
                </div>
                <hr>
                <h3 class="text-center text-danger">Total Open NC Count: <span id="overall_total">0</span></h3>
            </div>
            <div class="modal-footer justify-content-between">
                <div>
                    <button type="button" class="btn btn-sm btn-info" onclick="viewNCDetails()">OE NC Tracker</button>
                </div>
                <div>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDeactivateAndClose()">Deactivate & Close NC</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 				$this->endSection();?>
	

<?php 					$this->section("javascript_section");?>
<script>

//$("#kt_datepicker_1").flatpickr();
//$.ajax()
const form = document.getElementById('<?=$form_id?>_form');
var validation_object  = {
        fields: {
            'client_name': {
                validators: {
                    notEmpty: {
                        message: 'Please enter Client Name.'
                    }
                }
            },
            'region': {
                validators: {
                    notEmpty: {
                        message: 'Please select Region.'
                    }
                }
            },
            'cluster': {
                validators: {
                    notEmpty: {
                        message: 'Please select Cluster.'
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
var originalOeData = null;

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

                function performOeSubmit() {
                    ajax_call(url,formData,button,function(responce){
                        try{
                            responce = JSON.parse(responce);
                            if(responce.status==1){
                                    toastr.success(responce.message);
                                    form.reset();
                                    originalOeData = null;
                                    $("#client_name_edit_note").hide();
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
                if (originalOeData) {
                    var curName = $("#<?=$form_id?>").find("#client_name").val().trim();
                    var curLoc = $("#<?=$form_id?>").find("#location").val().trim();
                    var curCluster = $("#<?=$form_id?>").find("#cluster").val();
                    var curAm = $("#<?=$form_id?>").find("#account_manager").val();

                    if ((originalOeData.client_name || '') !== curName ||
                        (originalOeData.location || '') !== curLoc ||
                        (originalOeData.cluster || '') !== curCluster ||
                        (originalOeData.account_manager || '') !== curAm) {
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
                                performOeSubmit();
                            }
                        });
                    } else {
                        if (confirm(confirmMsg)) {
                            performOeSubmit();
                        }
                    }
                } else {
                    performOeSubmit();
                }

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
                                    
                                    originalOeData = {
                                        client_name: responce.data.client_name || '',
                                        location: responce.data.location || '',
                                        cluster: responce.data.cluster || '',
                                        account_manager: responce.data.account_manager || ''
                                    };
                                    $("#<?=$form_id?>").find("#client_name").val(responce.data.client_name);
                                    $("#client_name_edit_note").show();

                                    if(responce.data.location) {
                                        $("#<?=$form_id?>").find("#location_select").val(responce.data.location);
                                        $("#<?=$form_id?>").find("#location").val(responce.data.location);
                                    }
                                    $("#<?=$form_id?>").find("#email").val(responce.data.email);
                                    $("#<?=$form_id?>").find("#region").val(responce.data.region);
                                    $("#<?=$form_id?>").find("#cluster").val(responce.data.cluster);
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

// Reset edit note when clicking add button
$('[data-bs-target="#<?=$button_id?>"]').on('click', function() {
    originalOeData = null;
    $("#client_name_edit_note").hide();
    $("#<?=$form_id?>_form")[0].reset();
    $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-url", $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-add-url"));
});

// Auto-fetch Manager E-mail when selecting Account Manager
$("#account_manager").on("change", function() {
    var selectedOpt = $(this).find("option:selected");
    var email = selectedOpt.attr("data-email") || selectedOpt.data("email") || "";
    var userName = $(this).val();
    
    if (email && email.trim() !== "") {
        $("#email").val(email.trim());
    } else if (userName && userName.trim() !== "") {
        $.ajax({
            url: "<?=base_url('Masters/Client/get_manager_email')?>",
            type: "POST",
            data: { user_name: userName.trim() },
            dataType: "json",
            success: function(res) {
                if (res && res.status == 1 && res.email) {
                    $("#email").val(res.email);
                }
            }
        });
    } else {
        $("#email").val("");
    }
});

var currentStatusFilter = 'active';

function setStatusFilter(status) {
    currentStatusFilter = status;
    
    if (status === 'active') {
        $('#filter_active_btn').removeClass('btn-outline-primary btn-outline-secondary').addClass('btn-primary active text-white');
        $('#filter_all_btn').removeClass('btn-secondary active text-white').addClass('btn-outline-secondary');
    } else {
        $('#filter_all_btn').removeClass('btn-outline-primary btn-outline-secondary').addClass('btn-secondary active text-white');
        $('#filter_active_btn').removeClass('btn-primary active text-white').addClass('btn-outline-primary');
    }

    var datatable = $('.table-bordered').DataTable();
    var newUrl = '<?=base_url("Masters/Client/table_ajax")?>?status_filter=' + status;
    datatable.ajax.url(newUrl).load();
}

// Update counts when DataTables receives XHR response
$(document).ready(function() {
    var tableEl = $('.table-bordered');
    tableEl.on('xhr.dt', function(e, settings, json, xhr) {
        if (json && json.counts) {
            $('#active_count_badge').text(json.counts.active !== undefined ? json.counts.active : 0);
            $('#all_count_badge').text(json.counts.all !== undefined ? json.counts.all : 0);
        }
    });
});
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

// changes on 1/11/25 by darsh: Add functionality for location dropdown with Add Location button
// (Removed location dropdown JS per request)


var currentDeactClientId = null;
var currentDeactClientName = null;
var currentDeactLocation = null;
var currentDeactRegion = null;

function openDeactivateConfirmModal(clientId) {
    console.log("Deactivate clicked for ID:", clientId);
    currentDeactClientId = clientId;
    
    // Fetch pending NCs
    $.ajax({
        url: "<?=base_url('Masters/Client/get_pending_nc_counts')?>/" + clientId,
        type: "GET",
        dataType: "json",
        success: function(res) {
            console.log("AJAX success response:", res);
            if(res.status == 1) {
                currentDeactClientName = res.client_name;
                currentDeactLocation = res.location;
                currentDeactRegion = res.region;
                
                $("#deact_client_name").text(res.client_name);
                $("#deact_client_code").text(res.client_code);
                $("#deact_client_status").text(res.client_status);
                $("#deact_location_name").text(res.location);
                
                $("#oe_open").text(res.oe.open);
                $("#oe_work").text(res.oe.working);
                $("#oe_cr").text(res.oe.cluster_review);
                $("#oe_ar").text(res.oe.auditor_review);
                $("#oe_total").text(res.oe.total);
                
                let overall = res.oe.total;
                $("#overall_total").text(overall);
                
                const modal = new bootstrap.Modal(document.getElementById('deactivateConfirmModal'));
                modal.show();
            } else {
                toastr.error(res.message);
            }
        },
        error: function(err) {
            console.error("AJAX Error:", err);
            toastr.error("Failed to fetch pending NC counts.");
        }
    });
}

function viewNCDetails() {
    if(!currentDeactClientName) return;
    let baseUrl = "<?=base_url('Masters/Oe_nc_tracker')?>";
    let url = baseUrl + "?client=" + encodeURIComponent(currentDeactClientName) + 
              "&region=" + encodeURIComponent(currentDeactRegion) + 
              "&location=" + encodeURIComponent(currentDeactClientName);
    window.open(url, '_blank');
}

function confirmDeactivateAndClose() {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This action will close all pending NCs for this client and deactivate the client. This action cannot be undone. Do you want to continue?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Deactivate & Close NC',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "<?=base_url('Masters/Client/deactivate_and_close_ncs')?>",
                type: "POST",
                data: { client_id: currentDeactClientId },
                dataType: "json",
                success: function(res) {
                    if(res.status == 1) {
                        toastr.success(res.message);
                        $("#deactivateConfirmModal").modal("hide");
                        reload_data_table();
                    } else {
                        toastr.error(res.message);
                    }
                }
            });
        }
    });
}

</script>
<?php 				$this->endSection();?>

