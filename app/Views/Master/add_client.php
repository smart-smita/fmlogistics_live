
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
<style>
/* ─────────────────────────────────────────────────────────────
   CLIENT MASTER - HIGH CONTRAST BUTTONS & ACTION STYLING
───────────────────────────────────────────────────────────── */

/* 1. Status Filter Buttons (Active / All) */
.client-status-filter-group {
    background: #ffffff;
    border-radius: 8px;
    padding: 3px;
    border: 1px solid #cbd5e1;
    display: inline-flex;
}

#filter_active_btn,
#filter_all_btn {
    border-radius: 6px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    padding: 7px 16px !important;
    transition: all 0.2s ease-in-out !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: 1px solid transparent !important;
    text-decoration: none !important;
}

/* Active State */
#filter_active_btn.active,
#filter_all_btn.active {
    background-color: #009ef7 !important;
    border-color: #009ef7 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(0, 158, 247, 0.35) !important;
}

#filter_active_btn.active i,
#filter_all_btn.active i,
#filter_active_btn.active span,
#filter_all_btn.active span {
    color: #ffffff !important;
}

/* Inactive State - High Contrast & Clearly Visible */
#filter_active_btn:not(.active),
#filter_all_btn:not(.active) {
    background-color: #f8fafc !important;
    border: 1px solid #cbd5e1 !important;
    color: #334155 !important;
}

#filter_active_btn:not(.active) i,
#filter_all_btn:not(.active) i {
    color: #475569 !important;
}

#filter_active_btn:not(.active):hover,
#filter_all_btn:not(.active):hover {
    background-color: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
}

/* 2. Top Header Add Client Button */
.card-header .btn[data-bs-target="#Client"] {
    background-color: #009ef7 !important;
    color: #ffffff !important;
    border: 1px solid #009ef7 !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    padding: 7px 16px !important;
    border-radius: 6px !important;
    box-shadow: 0 2px 6px rgba(0, 158, 247, 0.25) !important;
    transition: all 0.2s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
}

.card-header .btn[data-bs-target="#Client"]:hover {
    background-color: #0086d4 !important;
    border-color: #0086d4 !important;
    box-shadow: 0 4px 10px rgba(0, 158, 247, 0.35) !important;
}

.card-header .btn[data-bs-target="#Client"] svg rect {
    fill: #ffffff !important;
    opacity: 1 !important;
}

/* 3. Excel / CSV Export Button */
#custom_export_csv_btn,
.buttons-csv,
button[onclick*="buttons-csv"] {
    font-weight: 600 !important;
    font-size: 13px !important;
    padding: 7px 16px !important;
    border-radius: 6px !important;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25) !important;
}

/* 4. Action Column Buttons in Table */
table.dataTable th:last-child,
table.dataTable td:last-child,
.col-field-action {
    min-width: 175px !important;
    width: 175px !important;
    text-align: center !important;
    vertical-align: middle !important;
}

table.dataTable td .btn-icon.btn-sm {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    min-height: 32px !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 6px !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
}

table.dataTable td .btn-icon.btn-sm i {
    font-size: 13px !important;
}

table.dataTable td .badge {
    font-size: 11px !important;
    padding: 4px 8px !important;
    font-weight: 600 !important;
    border-radius: 4px !important;
    display: inline-block !important;
}
</style>
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
                    	<div class="scroll-y me-n7 pe-7" id="user" data-kt-scroll="false" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_new_address_header" data-kt-scroll-wrappers="#fees_head" data-kt-scroll-offset="300px" style="max-height: calc(75vh - 120px);">
								<!--begin::Input group-->
								<div class="row mb-12">
									<!--begin::Col-->
									<div class="col-md-12 fv-row fv-plugins-icon-container mb-5">
										<label class="required fs-5 fw-bold-2">Client Name</label>
										<input type="text" class="form-control form-control-solid" placeholder="Client Name"  id="client_name" name="client_name">
										<div id="client_name_edit_note" class="text-warning fs-7 mt-1 font-weight-bold" style="display: none;">
											<strong>Note:</strong> Changing the Client Name will update dependent audit records and backup pre-rename audit history.
										</div>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>

									<div class="col-md-12 fv-row fv-plugins-icon-container mb-5" id="rename_remarks_group" style="display: none;">
										<label class="fs-5 fw-bold-2 text-primary"><i class="fa fa-info-circle text-primary me-1"></i>Reason / Remarks for Renaming Site (Optional)</label>
										<input type="text" class="form-control form-control-solid border border-primary" placeholder="Enter reason or remarks for site renaming (e.g. Client facility name changed)" id="rename_remarks" name="rename_remarks">
										<div class="form-text text-muted fs-7">This remark will be permanently recorded in the OE rename audit history log.</div>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-12 fv-row fv-plugins-icon-container mb-5">
										<label class="required fs-5 fw-bold-2">Region </label>
										<select class="form-select form-select-solid"  id="region" name="region">
										    <option value="" data-location-id="0">Select Region</option>
										    <?php foreach($region_list as $row){ ?>
										    <option value="<?=$row['region_name']?>" data-location-id="<?=$row['region_id']?>"><?=$row['region_name']?></option>
										        <?php } ?>
										        </select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>
									
									<div class="col-md-12 fv-row fv-plugins-icon-container mb-5">
										<label class="required fs-5 fw-bold-2">Cluster </label>
										<select class="form-select form-select-solid"  id="cluster" name="cluster">
										    <option value="" data-location-id="0">Select Cluster</option>
										    <?php foreach($cluster_list as $row){ ?>
										    <option value="<?=$row['cluster_name']?>" data-location-id="<?=$row['cluster_id']?>"><?=$row['cluster_name']?></option>
										        <?php } ?>
										        </select>
										<div class="fv-plugins-message-container invalid-feedback"></div>
									</div>

                                    <div class="col-md-12 fv-row fv-plugins-icon-container mb-5">
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
                                    
									<div class="col-md-12 fv-row fv-plugins-icon-container mb-5">
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
                <h2 class="text-white">Deactivate Client</h2>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1"><i class="fa fa-times text-white"></i></span>
                </div>
            </div>
            <div class="modal-body">
                <div class="mb-5">
                    <h5>Client Name: <span id="deact_client_name" class="text-primary"></span></h5>
                    <h5>Client Code: <span id="deact_client_code" class="text-primary"></span></h5>
                    <h5>Client Status: <span id="deact_client_status" class="text-primary"></span></h5>
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
                        var fVal = (field.value !== null && field.value !== undefined) ? String(field.value).trim() : "";
                        if (fVal !== "") {
                            formData[field.name] = fVal;
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
                    var curNameVal = $("#<?=$form_id?>").find("#client_name").val();
                    var curName = (curNameVal !== null && curNameVal !== undefined) ? String(curNameVal).trim() : "";
                    var isNameChanged = (originalOeData.client_name || '').trim() !== curName;
                    if (isNameChanged) {
                        isChanged = true;
                    }
                }

                if (isChanged) {
                    var isNameChanged = originalOeData && (originalOeData.client_name || '').trim() !== curName;
                    var confirmTitle = isNameChanged ? 'Rename Site Confirmation' : 'Are you sure?';
                    var confirmMsg = isNameChanged
                        ? "Are you sure you want to rename site '" + (originalOeData.client_name || '') + "' to '" + curName + "'? Dependent audit records will be updated and an audit backup will be created."
                        : "This change will update the corresponding details in dependent audit records. Do you want to continue?";

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: confirmTitle,
                            text: confirmMsg,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: isNameChanged ? 'Yes, Rename' : 'Yes, Update',
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
                                        cluster: responce.data.cluster || '',
                                        account_manager: responce.data.account_manager || ''
                                    };
                                    $("#<?=$form_id?>").find("#client_name").val(responce.data.client_name);
                                    $("#client_name_edit_note").show();
                                    $("#rename_remarks_group").hide();
                                    $("#rename_remarks").val('');

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

// Dynamically display remarks field when site name is modified in edit modal
$(document).on("input change", "#client_name", function() {
    if (originalOeData && originalOeData.client_name) {
        var cur = $(this).val().trim();
        if (cur !== "" && cur !== originalOeData.client_name.trim()) {
            $("#rename_remarks_group").slideDown();
        } else {
            $("#rename_remarks_group").slideUp();
        }
    } else {
        $("#rename_remarks_group").hide();
    }
});

// Reset edit note when clicking add button
$('[data-bs-target="#<?=$button_id?>"]').on('click', function() {
    originalOeData = null;
    $("#client_name_edit_note").hide();
    $("#rename_remarks_group").hide();
    $("#rename_remarks").val('');
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
        $('#filter_active_btn').addClass('active');
        $('#filter_all_btn').removeClass('active');
    } else {
        $('#filter_all_btn').addClass('active');
        $('#filter_active_btn').removeClass('active');
    }

    var newUrl = '<?=base_url("Masters/Client/table_ajax")?>?status_filter=' + status;
    if ($.fn.DataTable.isDataTable('#client_master_table')) {
        $('#client_master_table').DataTable().ajax.url(newUrl).load();
    } else if (typeof table !== 'undefined' && table && table.ajax) {
        table.ajax.url(newUrl).load();
    }
}

// Update counts when DataTables receives XHR response
$(document).ready(function() {
    $('#client_master_table').on('xhr.dt', function(e, settings, json, xhr) {
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
                currentDeactRegion = res.region;
                
                $("#deact_client_name").text(res.client_name);
                $("#deact_client_code").text(res.client_code);
                $("#deact_client_status").text(res.client_status);
                
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

