<?php $this->extend("Layout/base_admin"); ?>
<?php $this->section("main_body"); ?>
<?=$table?>
<?php $this->endSection(); ?>

<?php $this->section("modals_section"); ?>
<div class="modal fade" id="<?=$button_id?>">
<form id="<?=$button_id?>_form">

<div class="modal-dialog modal-dialog-centered mw-900px">
<div class="modal-content">

<div class="modal-header">
<h2>HSE Client</h2>
</div>

<div class="modal-body">
<div class="row">

<div class="col-md-6 fv-row mb-5">
<label class="required">Category</label>
<select class="form-select form-select-solid" name="category" id="category" onchange="loadSubcategories()">
<option value="" data-id="">Select Category</option>
<?php foreach($category_list as $cat){ ?>
<option value="<?=$cat['site_category_name']?>" data-id="<?=$cat['site_category_id']?>"><?=$cat['site_category_name']?></option>
<?php } ?>
</select>
<div class="invalid-feedback"></div>
</div>

<div class="col-md-6 fv-row mb-5">
<label>Sub Category</label>
<select class="form-select form-select-solid" name="sub_category" id="sub_category">
<option value="">Select Sub Category</option>
</select>
<div class="invalid-feedback"></div>
</div>

<div class="col-md-6 fv-row mb-5">
<label class="required">Client Name</label>
<input type="text" class="form-control form-control-solid" name="client_name" id="client_name">
<div id="hse_client_name_edit_note" class="text-warning fs-7 mt-1 font-weight-bold" style="display: none;">
    <strong>Note:</strong> Changing the Client Name will update dependent HSE audit records and backup pre-rename audit history.
</div>
<div class="invalid-feedback"></div>
</div>

<div class="col-md-6 fv-row mb-5" id="hse_rename_remarks_group" style="display: none;">
<label class="text-primary"><i class="fa fa-info-circle text-primary me-1"></i>Reason / Remarks for Renaming Site (Optional)</label>
<input type="text" class="form-control form-control-solid border border-primary" name="rename_remarks" id="hse_rename_remarks" placeholder="Enter reason for renaming (e.g. Facility name change)">
<div class="form-text text-muted fs-7">Recorded in HSE rename audit history.</div>
<div class="invalid-feedback"></div>
</div>

<div class="col-md-6 fv-row mb-5">
<label>Email</label>
<input type="text" class="form-control form-control-solid" name="email" id="email">
</div>

<div class="col-md-6 fv-row mb-5">
<label>Region</label>
<select class="form-select form-select-solid" name="region" id="region">
<option value="">Select Region</option>
<?php foreach($region_list as $r){ ?>
<option value="<?=$r['region_name']?>"><?=$r['region_name']?></option>
<?php } ?>
</select>
<div class="invalid-feedback"></div>
</div>

<div class="col-md-6 fv-row mb-5">
<label>Cluster</label>
<select class="form-select form-select-solid" name="cluster" id="cluster">
<option value="">Select Cluster</option>
<?php foreach($cluster_list as $c){ 
    $cVal = $c['cluster'] ?? $c['cluster_name'] ?? '';
    if(trim((string)$cVal) === '') continue;
?>
<option value="<?=htmlspecialchars($cVal)?>"><?=htmlspecialchars($cVal)?></option>
<?php } ?>
</select>
<div class="invalid-feedback"></div>
</div>

<div class="col-md-6 fv-row mb-5">
<label>Account Manager</label>
<select class="form-select form-select-solid" name="account_manager" id="account_manager">
<option value="">Select Account Manager</option>
<?php foreach($am_list as $a){ 
    $aVal = $a['account_manager'] ?? $a['user_name'] ?? '';
    if(trim((string)$aVal) === '') continue;
?>
<option value="<?=htmlspecialchars($aVal)?>"><?=htmlspecialchars($aVal)?></option>
<?php } ?>
</select>
<div class="invalid-feedback"></div>
</div>

</div>
</div>

<div class="modal-footer">
<button class="btn btn-light" data-bs-dismiss="modal">Discard</button>
<button type="submit" id="ajax_click" data-ajax-add-url="<?=$ajax_url?>" class="btn btn-primary">Submit</button>
</div>

</div>
</div>
</form>
</div>
</div>


<?php 				$this->endSection();?>


<?php 					$this->section("javascript_section");?>
<script>
var originalHseData = null;

$("#ajax_click").on("click", function (e) {
    e.preventDefault();

    let form   = $("#<?=$button_id?>_form");
    let button = $(this);
    let url    = button.attr("data-ajax-url") || button.attr("data-ajax-add-url");

    // Clear old errors
    form.find(".is-invalid").removeClass("is-invalid");
    form.find(".invalid-feedback").html("");

    // UI VALIDATION
    let isValid = true;

    if ($("#category").val() === "") {
        setError("#category", "Category is required");
        isValid = false;
    }

    let clientNameVal = $("#client_name").val();
    let clientName = (clientNameVal !== null && clientNameVal !== undefined) ? String(clientNameVal).trim() : "";
    if (clientName.length < 3) {
        setError("#client_name", "Client name must be at least 3 characters");
        isValid = false;
    }

    let emailVal = $("#email").val();
    let email = (emailVal !== null && emailVal !== undefined) ? String(emailVal).trim() : "";
    if (email !== "" && !validateEmail(email)) {
        setError("#email", "Invalid email address");
        isValid = false;
    }

    if (!isValid) return;

    function performSubmit(extraRemarks = '') {
        // Disable button
        button.prop("disabled", true).text("Please wait...");

        let postDataStr = form.serialize();
        if (extraRemarks) {
            postDataStr += '&rename_remarks=' + encodeURIComponent(extraRemarks);
        }

        ajax_call(url, postDataStr, button, function (res) {
            if (res.status == 1) {
                toastr.success(res.message);
                form[0].reset();
                originalHseData = null;
                $("#hse_client_name_edit_note").hide();
                $("#sub_category").html('<option value="">Select Sub Category</option>');
                $("#<?=$button_id?>").modal("hide");
                reload_data_table();
            } else {
                toastr.warning(res.message);
                if (res.errors) {
                    $.each(res.errors, function (field, msg) {
                        setError("[name='"+field+"']", msg);
                    });
                }
            }
            button.prop("disabled", false).text("Submit");
        });
    }

    let isChanged = false;
    let isNameChanged = false;
    if (originalHseData) {
        let curNameVal = $("#client_name").val();
        let curName = (curNameVal !== null && curNameVal !== undefined) ? String(curNameVal).trim() : "";

        if ((originalHseData.client_name || '').trim() !== curName) {
            isChanged = true;
            isNameChanged = true;
        }
    }

    if (isChanged) {
        let confirmTitle = isNameChanged ? 'Rename HSE Site Confirmation' : 'Are you sure?';
        let confirmMsg = isNameChanged
            ? "Are you sure you want to rename HSE site '" + (originalHseData.client_name || '') + "' to '" + $("#client_name").val().trim() + "'? Dependent HSE audit records will be updated and an audit backup will be created."
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
                    performSubmit();
                }
            });
        } else {
            if (confirm(confirmMsg)) {
                performSubmit();
            }
        }
    } else {
        performSubmit();
    }
});

// Load dependent dropdown
function loadSubcategories(selectedSubcat = '') {
    let categoryId = $("#category option:selected").data("id");
    let subCatDropdown = $("#sub_category");
    subCatDropdown.html('<option value="">Loading...</option>');
    
    if(!categoryId) {
        subCatDropdown.html('<option value="">Select Sub Category</option>');
        return;
    }

    $.ajax({
        url: "<?=base_url('Masters/Hse_sub_category/get_by_category')?>",
        type: "POST",
        data: {site_category_id: categoryId},
        success: function(response) {
            let options = '<option value="">Select Sub Category</option>';
            if(response.status == 1 && response.data.length > 0) {
                $.each(response.data, function(index, item) {
                    let selected = (item.sub_category_name == selectedSubcat) ? 'selected' : '';
                    options += `<option value="${item.sub_category_name}" data-id="${item.sub_category_id}" ${selected}>${item.sub_category_name}</option>`;
                });
            }
            subCatDropdown.html(options);
        },
        error: function() {
            subCatDropdown.html('<option value="">Error loading data</option>');
        }
    });
}

// Helper functions
function setError(selector, message) {
    $(selector).addClass("is-invalid");
    $(selector).closest(".fv-row").find(".invalid-feedback").html(message);
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function edit_id(obj,id){
    ajax_call($(obj).data('ajax-url'),{},$(obj),function(res){
        res = JSON.parse(res);
        $("#category").val(res.data.category);
        
        // Trigger subcategory load
        if(res.data.category) {
            loadSubcategories(res.data.sub_category);
        } else {
            $("#sub_category").val(res.data.sub_category);
        }
        
        originalHseData = {
            client_name: res.data.client_name || '',
            location: res.data.location || '',
            cluster: res.data.cluster || '',
            account_manager: res.data.account_manager || ''
        };
        $("#client_name").val(res.data.client_name);
        $("#hse_client_name_edit_note").show();
        $("#hse_rename_remarks_group").hide();
        $("#hse_rename_remarks").val('');
        $("#email").val(res.data.email);
        $("#region").val(res.data.region);
        $("#cluster").val(res.data.cluster);
        $("#account_manager").val(res.data.account_manager);

        $("#ajax_click").attr("data-ajax-url",
            "<?=base_url('Masters/Hse_client/save_details')?>/"+id);

        $("#<?=$button_id?>").modal("show");
    });
}

// Dynamically display remarks field when site name is modified in edit modal
$(document).on("input change", "#client_name", function() {
    if (originalHseData && originalHseData.client_name) {
        let cur = $(this).val().trim();
        if (cur !== "" && cur !== originalHseData.client_name.trim()) {
            $("#hse_rename_remarks_group").slideDown();
        } else {
            $("#hse_rename_remarks_group").slideUp();
        }
    } else {
        $("#hse_rename_remarks_group").hide();
    }
});

// Reset form correctly on click of add button handled outside or standard reset
$('[data-bs-target="#<?=$button_id?>"]').on('click', function() {
    originalHseData = null;
    $("#hse_client_name_edit_note").hide();
    $("#hse_rename_remarks_group").hide();
    $("#hse_rename_remarks").val('');
    $("#<?=$button_id?>_form")[0].reset();
    $("#sub_category").html('<option value="">Select Sub Category</option>');
    $("#ajax_click").attr("data-ajax-url", $("#ajax_click").attr("data-ajax-add-url"));
});

function delete_row(obj){
    Swal.fire({
        title: 'Do you want to delete?',
        showCancelButton: true,
        confirmButtonText: 'Delete',
    }).then((result) => {
        if (result.isConfirmed) {
            url_call_ajax($(obj).attr("data-ajax-url"), $(obj));
        }
    });
}

</script>
<?php $this->endSection(); ?>
