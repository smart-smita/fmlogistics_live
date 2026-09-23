<?php $this->extend("Layout/base_admin"); ?>
<?php 
    $this->section("breadcrumb_title_li");
?>
<?php
$session = session();
$isClusterManager = ($session->get('user_designation') && strtolower($session->get('user_designation')) === 'cluster manager');
// Higher Authority users get full access to all filters (read-only role with full visibility)
helper('designation_acl');
$isHigherAuthorityUser = isHigherAuthority();
$isAccountManagerUser = isAccountManager();
$userRegion  = $session->get('user_region')  ?? '';
$userCluster = $session->get('user_name')    ?? '';
if ($isClusterManager) {
    $userRegion = getClusterManagerAssignedRegion();
    $userCluster = getClusterManagerAssignedCluster();
}
// Filters are locked for CM and AM (but not Higher Authority)
$filtersLocked = (($isClusterManager || $isAccountManagerUser) && !$isHigherAuthorityUser);
?>


<?php 
$form_id= $button_id;
//data-ajax-url during add/edit 
//data-ajax-add-url during add Important
?>

<!--begin::Item-->
    <li class="breadcrumb-item text-muted">
        <!-- NEW CHANGES: Breadcrumb resets filter by going to index -->
        <a href="<?= base_url('Masters/Normal_nc_tracker') ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"Client"?></a>
    </li>
<!--end::Item-->
<?php $this->endSection();?>    

<?php $this->section("main_body"); ?>
<div id="oe_filter_container"
     data-get-clusters-url="<?= base_url('Customer/Audit_dashboard/get_clusters_by_region') ?>"
     data-get-locations-url="<?= base_url('Customer/Audit_dashboard/get_locations_by_cluster') ?>"
     data-pre-region='<?= json_encode($selRegion) ?>'
     data-pre-cluster='<?= json_encode($selCluster) ?>'
     data-pre-location='<?= json_encode($selLocation) ?>'
     data-user-region='<?= json_encode($userRegion) ?>'
     data-user-cluster='<?= json_encode($userCluster) ?>'
     data-is-cluster="<?= $isClusterManager ? '1' : '0' ?>"
     data-is-account-manager="<?= $isAccountManagerUser ? '1' : '0' ?>"
     data-is-higher-authority="<?= $isHigherAuthorityUser ? '1' : '0' ?>">



<form method="get" class="card mb-5 p-3" style="overflow: visible;">
    <div class="row g-3 align-items-start">

        <div class="col-12 col-md-4 col-lg-2">
            <label class="form-label fw-bold">Audit Type</label>
            <div class="position-relative">
                <select id="flt_audit_type" name="audit_type[]" multiple="multiple"
                        class="form-select form-select-sm fw-semibold js-example-basic-single">
                    <?php if (isset($audit_types)): ?>
                        <?php foreach($audit_types as $at): ?>
                            <option value="<?= esc($at['audit_name']) ?>" 
                                <?= in_array($at['audit_name'], $selAuditType ?? []) ? 'selected' : '' ?>>
                                <?= esc($at['audit_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="col-12 col-md-4 col-lg-2">
            <label class="form-label fw-bold">Region</label>
            <div class="position-relative">
                <select id="flt_region" name="region[]" multiple="multiple"
                        class="form-select form-select-sm fw-semibold js-example-basic-single"
                        <?= $filtersLocked ? 'disabled' : '' ?>>
                    <?php if($filtersLocked): ?>
                        <!-- For display only (CM or AM restricted view) -->
                        <?php foreach($selRegion as $r): ?>
                            <option value="<?= esc($r) ?>" selected><?= esc($r) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach($regions as $r): ?>
                            <option value="<?= esc($r['region_name']) ?>" 
                                <?= in_array(strtolower($r['region_name']), array_map('strtolower', $selRegion)) ? 'selected' : '' ?>>
                                <?= esc($r['region_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="col-12 col-md-4 col-lg-2">
            <label class="form-label fw-bold">Cluster</label>
            <div class="position-relative">
                <select id="flt_cluster" name="cluster[]" multiple="multiple"
                        class="form-select form-select-sm fw-semibold js-example-basic-single"
                        <?= $filtersLocked ? 'disabled' : '' ?>>
                    <?php if($filtersLocked): ?>
                        <?php foreach($selCluster as $c): ?>
                            <option value="<?= esc($c) ?>" selected><?= esc($c) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach($clusters as $c): ?>
                            <option value="<?= esc($c['cluster_name']) ?>" 
                                <?= in_array($c['cluster_name'], $selCluster) ? 'selected' : '' ?>>
                                <?= esc($c['cluster_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="col-12 col-md-4 col-lg-3">
            <label class="form-label fw-bold">Location</label>
            <div class="position-relative">
                <select id="flt_location" name="location[]" multiple="multiple"
                        class="form-select form-select-sm fw-semibold js-example-basic-single">
                      <?php if($isAccountManagerUser && !$isHigherAuthorityUser): ?>
                          <!-- AM locked to their assigned client locations -->
                          <?php $amLocations = !empty($userClientACL) ? (is_array($userClientACL) ? $userClientACL : [$userClientACL]) : []; ?>
                          <?php foreach($amLocations as $l): ?>
                              <option value="<?= esc($l) ?>" <?= in_array($l, $selLocation) ? 'selected' : '' ?>><?= esc($l) ?></option>
                          <?php endforeach; ?>
                      <?php else: ?>
                        <?php foreach($locations as $l): ?>
                            <option value="<?= esc($l['location_name']) ?>" 
                                <?= in_array($l['location_name'], $selLocation) ? 'selected' : '' ?>>
                                <?= esc($l['location_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="col-12 col-md-4 col-lg-2">
            <label class="form-label fw-bold">Month</label>
            <div class="position-relative">
                <select id="flt_month" name="month" class="form-select form-select-sm fw-semibold js-example-basic-single">
                    <option value="">All Months</option>
                    <?php if (!empty($months)): ?>
                        <?php foreach ($months as $mKey => $mLabel): ?>
                            <option value="<?= esc($mKey); ?>" <?= (isset($selMonth) && $selMonth === $mKey) ? 'selected' : '' ?>>
                                <?= esc($mLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="col-12 col-md-4 col-lg-1">
            <button class="btn btn-sm btn-primary w-100 mb-2 mt-2">Show</button>
            <div class="dropdown">
                <button class="btn btn-sm btn-success dropdown-toggle w-100" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-download"></i> Export
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="downloadMonthlyReport('pdf')"><i class="fa fa-file-pdf text-danger"></i> PDF</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="downloadMonthlyReport('excel')"><i class="fa fa-file-excel text-success"></i> Excel</a></li>
                </ul>
            </div>
        </div>

    </div>
</form>

<script>
function downloadMonthlyReport(format) {
    // Get current filter values (arrays or strings)
    const audit_type = $('#flt_audit_type').val();
    const region   = $('#flt_region').val();
    const cluster  = $('#flt_cluster').val();
    const location = $('#flt_location').val();
    const month    = $('#flt_month').val();
    
    // ⭐ CAPTURE CURRENT STATUS FROM URL
    // URL pattern: Masters/Normal_nc_tracker/index/{status}?filters...
    const currentPath = window.location.pathname;
    const pathParts = currentPath.split('/');
    let statusFilter = '';
    
    // Check if URL contains status filter (e.g., /index/open, /index/working, etc.)
    const indexPos = pathParts.indexOf('index');
    if (indexPos !== -1 && pathParts.length > indexPos + 1) {
        statusFilter = pathParts[indexPos + 1];
        // Decode URI component in case of spaces (e.g., "Under Review")
        statusFilter = decodeURIComponent(statusFilter);
    }
    
    // Build query string
    const params = new URLSearchParams();
    
    if (audit_type) {
        if(Array.isArray(audit_type)) audit_type.forEach(v => params.append('audit_type[]', v));
        else params.append('audit_type', audit_type);
    }
    if (region) {
        if(Array.isArray(region)) region.forEach(v => params.append('region[]', v));
        else params.append('region', region);
    }
    if (cluster) {
        if(Array.isArray(cluster)) cluster.forEach(v => params.append('cluster[]', v));
        else params.append('cluster', cluster);
    }
    if (location) {
        if(Array.isArray(location)) location.forEach(v => params.append('location[]', v));
        else params.append('location', location);
    }
    if (month) params.append('month', month);
    
    // ⭐ ADD STATUS FILTER TO EXPORT
    if (statusFilter && statusFilter.trim() !== '') {
        params.append('status', statusFilter);
    }
    
    // Navigate to download URL
    let url = '';
    if (format === 'pdf') {
        url = '<?= base_url('Masters/Normal_nc_tracker/monthly_report_pdf') ?>' + (params.toString() ? '?' + params.toString() : '');
    } else if (format === 'excel') {
        url = '<?= base_url('Masters/Normal_nc_tracker/export_report_excel') ?>' + (params.toString() ? '?' + params.toString() : '');
    }
    if (url) window.open(url, '_blank');
}
</script>

</div>

<!-- Bulk Action Toolbar -->
<div id="bulkActionToolbar" class="d-none mb-3">
    <div class="d-flex align-items-center bg-light-primary border border-primary p-3 rounded">
        <span class="fs-5 fw-bold me-4 text-primary"><span id="selectedCount">0</span> NCs Selected</span>
        <button type="button" class="btn btn-sm btn-success" onclick="openBulkCloseModal()">
            <i class="fa fa-check-double"></i> Close Selected NCs
        </button>
        <button type="button" class="btn btn-sm btn-light-danger ms-2" onclick="clearBulkSelection()">
            <i class="fa fa-times"></i> Cancel
        </button>
    </div>
</div>

<?=$table?>
<?php $this->endSection(); ?>

<?php  $this->section("modals_section"); ?>

<!--begin::Modal - Bulk Close-->
<div class="modal fade" id="bulkCloseModal" tabindex="-1" aria-hidden="true">
    <form id="bulkCloseForm" onsubmit="return false;">
        <div class="modal-dialog modal-dialog-centered mw-600px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Bulk Close NCs</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="modal-body py-lg-10 px-lg-10">
                    <div class="alert alert-warning d-flex align-items-center p-5 mb-5">
                        <i class="fa fa-exclamation-triangle fs-2x me-4 text-warning"></i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-warning">Confirm Bulk Closure</h4>
                            <span>Are you sure you want to approve and close <span id="modalSelectedCount" class="fw-bolder"></span> selected NC(s)? This action cannot be undone.</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="btn_submit_bulk_close" onclick="submitBulkClose()">
                        <span class="indicator-label">Yes, Close NCs</span>
                        <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<!--end::Modal - Bulk Close-->

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
                    <h2>Normal NC Tracker</h2>
                    <!--end::Modal title-->
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                            </svg>
                        </span>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->

                <!--begin::Modal body-->
                <div class="modal-body py-lg-10 px-lg-10">

                    <div class="scroll-y me-n7 pe-7" id="user" data-kt-scroll="false"
                         data-kt-scroll-activate="{default: false, lg: true}"
                         data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#kt_modal_new_address_header"
                         data-kt-scroll-wrappers="#fees_head"
                         data-kt-scroll-offset="300px"
                         style="max-height: 70vh;">
                        <!--begin::Input group-->
                        <div class="row mb-12">
                            <!--begin::Col-->

                            <div class="col-md-12 fv-row fv-plugins-icon-container">
                                <label class="fs-5 fw-bold mb-2">Audit Details Id</label>
                                <input type="text" class="form-control form-control-solid"
                                       id="audit_details_id" name="audit_details_id" disabled>
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>

                            <div class="twocol">
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class="fs-5 fw-bold mb-2">Audit no.</label>
                                    <input type="text" class="form-control form-control-solid" id="audit_no" name="audit_no" disabled>
                                </div>
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class="fs-5 fw-bold mb-2">Audit Type Name</label>
                                    <input type="text" class="form-control form-control-solid" id="audit_name" name="audit_name" disabled>
                                </div>
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class="fs-5 fw-bold mb-2">Auditor Name</label>
                                    <input type="text" class="form-control form-control-solid" id="auditor_name" name="auditor_name" disabled>
                                </div>
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class="fs-5 fw-bold mb-2">Auditee Name</label>
                                    <input type="text" class="form-control form-control-solid" id="auditee_name" name="auditee_name" disabled>
                                </div>
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class="fs-5 fw-bold mb-2">Region</label>
                                    <input type="text" class="form-control form-control-solid" id="region" name="region" disabled>
                                </div>
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class="fs-5 fw-bold mb-2">Client Name</label>
                                    <input type="text" class="form-control form-control-solid" id="client_name" name="client_name" disabled>
                                </div>
                                <!-- changes on 10/11/12 by darsh: hide Site Location in NC form -->
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class="fs-5 fw-bold mb-2">Audit Score</label>
                                    <input type="text" class="form-control form-control-solid" id="audit_score" name="audit_score" disabled>
                                </div>
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class="fs-5 fw-bold mb-2">Category</label>
                                    <input type="text" class="form-control form-control-solid" id="category" name="category" disabled>
                                </div>
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class="fs-5 fw-bold mb-2">Audit Question</label>
                                    <input type="text" class="form-control form-control-solid" id="audit_question" name="audit_question" disabled>
                                </div>


                                <!-- Risk Priority as PRE (HTML allowed, no <b> text) -->
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class="fs-5 fw-bold mb-2">Target</label>
                                    <pre class="form-control form-control-solid"
                                         id="risk_priority"
                                         style="white-space:pre-wrap; min-height:38px; overflow:auto;"></pre>
                                </div>
                            </div>

                            <div class="col-md-12 fv-row fv-plugins-icon-container">
                                <label class="fs-5 fw-bold mb-2">Weightage</label>
                                <input type="text" class="form-control form-control-solid" id="weightage" name="weightage" disabled>
                            </div>
                            <div class="col-md-12 fv-row fv-plugins-icon-container">
                                <label class="fs-5 fw-bold mb-2">Audit Finding</label>
                                <input type="text" class="form-control form-control-solid" id="audit_finding" name="audit_finding" disabled>
                            </div>

                            <!-- NEW CHANGES: NC Before Photo - Display only from audit, no file input -->
                            <div class="col-md-12 fv-row fv-plugins-icon-container">
                                <label class="fs-5 fw-bold mb-2">Nc Before Photo (From Audit)</label>
                                <div id="audit_before_photo_display" class="form-control form-control-solid"
                                     style="min-height: 50px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                    <span class="text-muted">Before photo will be loaded from audit data</span>
                                </div>
                                <input type="hidden" name="audit_before_attachment" id="audit_before_attachment">
                            </div>

                            <!-- clickable thumbnail preview for before photo -->
                            <script>
                                (function(){
                                    function renderBeforePreview(url){
                                        var wrap = document.getElementById('audit_before_photo_display');
                                        if(!wrap) return;
                                        if(url && url.trim() !== ''){
                                            var base = '<?= base_url('/') ?>';
                                            var full = url.match(/^https?:/i) ? url : (base + url);
                                            wrap.innerHTML =
                                                '<a href="'+full+'" target="_blank">' +
                                                '<img src="'+full+'" style="max-width:120px;max-height:120px;border:1px solid #eee;padding:2px;background:#fff" />' +
                                                '</a>';
                                        }
                                    }
                                    document.addEventListener('shown.bs.modal', function(ev){
                                        if(ev.target && ev.target.id === '<?= $form_id ?>'){
                                            var val = document.getElementById('audit_before_attachment')
                                                      ? document.getElementById('audit_before_attachment').value
                                                      : '';
                                            renderBeforePreview(val);
                                        }
                                    });
                                })();
                            </script>

                            <div class="col-md-12 fv-row fv-plugins-icon-container">
                                <label class="required fs-5 fw-bold mb-2">Nc After Photo</label>
                                <input type="file" class="form-control" id="nc_after_photo" name="nc_after_photo"
                                       accept=".jpg,.jpeg,.png,.gif,.pdf" onchange="validateFileType(this)"/>
                                <input type="hidden" name="nc_after_photo_url" id="nc_after_photo_url">
                            </div>

                            <!-- NEW CHANGES: NC Remark textarea field -->
                            <div class="col-md-12 fv-row fv-plugins-icon-container">
                                <label class="required fs-5 fw-bold mb-2">NC Remark</label>
                                <textarea class="form-control form-control-solid" id="nc_remark" name="nc_remark"
                                          rows="4" placeholder="Please enter NC remark"></textarea>
                                <div class="fv-plugins-message-container invalid-feedback"></div>
                            </div>

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

                    <!-- Save as Draft -->
                    <button type="button" id="ajax_click_draft" data-ajax-add-url="<?=$ajax_url?>" class="btn btn-light-primary me-3">
                        <span class="indicator-label">Save as Draft</span>
                        <span class="indicator-progress">Please wait... 
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>

                    <!-- Submit for Review -->
                    <button type="submit" id="ajax_click" data-ajax-add-url="<?=$ajax_url?>" class="btn btn-primary">
                        <span class="indicator-label">Submit for Review</span>
                        <span class="indicator-progress">Please wait... 
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
                <!--end::Modal footer-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </form>
</div>
<!--end::Modal - Create App-->

<?php $this->endSection();?>
    

<?php $this->section("javascript_section");?>

<script>
function update_div_dep(obj){
    $(".locations").hide();
    $(".location_id_"+$(obj).find("option:selected").data("location-id")).show()
}

// form validation
const form = document.getElementById('<?=$form_id?>_form');
var validation_object  = {
    fields: {        
        'nc_remark':{
            validators: {
                notEmpty: {
                    message: 'NC remark is required'
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
                var formData = new FormData($("#<?=$form_id?>_form")[0]);
                formData.append('action', 'submit_for_review'); // Add action parameter

                ajax_call_img(url,formData,button,function(responce){
                    try{responce = JSON.parse(responce);}catch(e){}
                    form.reset();
                    $("#<?=$form_id?>").modal("hide");
                    if(typeof reload_data_table === 'function') { reload_data_table(); } else { location.reload(); }
                });
            }
        });
    }
});

// Save as Draft
$("#ajax_click_draft").on("click",function(){
    var button = $(this);
    var url = $(this).attr("data-ajax-url") || $(this).attr("data-ajax-add-url");
    var formData = new FormData($("#<?=$form_id?>_form")[0]);
    formData.append('action', 'save_as_draft');
    ajax_call_img(url,formData,button,function(responce){
        try{responce = JSON.parse(responce);}catch(e){}
        $("#<?=$form_id?>").modal("hide");
        if(typeof reload_data_table === 'function') { reload_data_table(); } else { location.reload(); }
    });
});

// prevent Enter submitting textarea
$(document).ready(function() {
    $('textarea').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            var cursorPos = this.selectionStart;
            var textBefore = this.value.substring(0, cursorPos);
            var textAfter = this.value.substring(cursorPos);
            this.value = textBefore + '\n' + textAfter;
            this.setSelectionRange(cursorPos + 1, cursorPos + 1);
        }
    });

    $('form').on('submit', function(e) {
        var activeElement = document.activeElement;
        if (activeElement && activeElement.tagName === 'TEXTAREA') {
            e.preventDefault();
            return false;
        }
    });
});

function edit_id(obj,id){
    var url = $(obj).attr("data-ajax-url");
    var formData={"id":id};
    ajax_call(url,formData,$(obj),function(responce){
        try{
            responce = JSON.parse(responce);
            if(responce.status==1){
                var d = responce.data || {};

                $("#<?=$form_id?>").find("#audit_details_id").val(d.audit_details_id).prop("disabled", true);

                $("#<?=$form_id?>").find("#audit_no").val(d.master_audit_no || d.audit_no || '').prop("disabled", true);
                $("#<?=$form_id?>").find("#audit_name").val(d.master_audit_name || d.audit_name || '').prop("disabled", true);
                $("#<?=$form_id?>").find("#auditor_name").val(d.master_auditor_name || d.auditor_name || '').prop("disabled", true);
                $("#<?=$form_id?>").find("#auditee_name").val(d.master_auditee_name || d.auditee_name || '').prop("disabled", true);
                $("#<?=$form_id?>").find("#region").val(d.master_region || d.master_zone || d.region || d.zone || '').prop("disabled", true);
                $("#<?=$form_id?>").find("#client_name").val(d.master_client_name || d.client_name || '').prop("disabled", true);
                $("#<?=$form_id?>").find("#audit_score").val(d.master_audit_score || d.audit_score || '').prop("disabled", true);
                $("#<?=$form_id?>").find("#category").val(d.category).prop("disabled", true);
                $("#<?=$form_id?>").find("#audit_question").val(d.audit_question).prop("disabled", true);

                // IMPORTANT: put HTML into <pre>, so <b> is rendered, not printed
                $("#<?=$form_id?>").find("#risk_priority").html(d.risk_priority || "");

                $("#<?=$form_id?>").find("#weightage").val(d.weightage).prop("disabled", true);
                $("#<?=$form_id?>").find("#audit_finding").val(d.audit_finding).prop("disabled", true);

                // before photo
                var beforeUrl = d.before_photo_url || d.audit_attachment || '';
                if(beforeUrl && beforeUrl !== '') {
                    var full = beforeUrl.match(/^https?:/i) ? beforeUrl : ('<?= base_url('/') ?>' + beforeUrl);
                    $("#<?=$form_id?>").find("#audit_before_photo_display").html(
                        '<a href="'+full+'" target="_blank">' +
                        '<img src="' + full + '" style="max-width: 100%; max-height: 200px;" ' +
                        'onerror="this.parentElement.innerHTML=\'<span class=&quot;text-muted&quot;>No before photo available</span>\'" />' +
                        '</a>'
                    );
                } else {
                    $("#<?=$form_id?>").find("#audit_before_photo_display").html('<span class="text-muted">No before photo available</span>');
                }
                $("#<?=$form_id?>").find("#audit_before_attachment").val(beforeUrl);

                // NC remark
                $("#<?=$form_id?>").find("#nc_remark").val(d.audit_remark || d.nc_remark || "");

                // existing after photo
                $("#<?=$form_id?>").find("#nc_after_photo_url").val(d.after_photo_url || "");

                // set URLs for submit/draft
                $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-url",
                    $("#<?=$form_id?>").find("#ajax_click").attr("data-ajax-add-url")+"/"+id);
                $("#<?=$form_id?>").find("#ajax_click_draft").attr("data-ajax-url",
                    $("#<?=$form_id?>").find("#ajax_click_draft").attr("data-ajax-add-url")+"/"+id);

                $("#<?=$form_id?>").modal("show");

                $("#<?=$form_id?>").on('shown.bs.modal', function(){
                    var d2 = responce.data || {};
                    $("#<?=$form_id?>").find("#audit_no").val(d2.master_audit_no || d2.audit_no || '');
                    $("#<?=$form_id?>").find("#audit_name").val(d2.master_audit_name || d2.audit_name || '');
                    $("#<?=$form_id?>").find("#auditor_name").val(d2.master_auditor_name || d2.auditor_name || '');
                    $("#<?=$form_id?>").find("#auditee_name").val(d2.master_auditee_name || d2.auditee_name || '');
                    $("#<?=$form_id?>").find("#region").val(d2.master_region || d2.master_zone || d2.region || d2.zone || '');
                    $("#<?=$form_id?>").find("#client_name").val(d2.master_client_name || d2.client_name || '');
                    $("#<?=$form_id?>").find("#location").val(d2.master_location || d2.location || '');
                    $("#<?=$form_id?>").find("#audit_score").val(d2.master_audit_score || d2.audit_score || '');
                });
            }
        }catch(error){
            // silent ignore
        }
    });
}

// Silent status update for OE tracker
function updateOeStatus(btn, id, status) {
    var button = btn ? $(btn) : null;
    $.ajax({
        url: '<?= base_url('Masters/Normal_nc_tracker/update_status') ?>',
        method: 'POST',
        dataType: 'json',
        data: { id: id, status: status },
        beforeSend: function(){
            if (button && button.length) {
                button.attr('data-kt-indicator', 'on');
                button.attr('disabled', true);
            }
        },
        success: function(res){
            if (typeof toastr !== 'undefined' && res && typeof res === 'object') {
                if (String(res.status) === '1') toastr.success(res.message || 'Updated');
                else toastr.warning(res.message || 'Update failed');
            }
        },
        complete: function(){
            if (button && button.length) {
                button.attr('data-kt-indicator', 'off');
                button.attr('disabled', false);
            }
            if(typeof reload_data_table === 'function') { reload_data_table(); } else { location.reload(); }
        }
    });
}

// File type validation function
function validateFileType(input) {
    const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const extension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.includes(extension)) {
            alert('Invalid file type. Only images (JPG, PNG, GIF) and PDFs are allowed.');
            input.value = '';
            return false;
        }
        
        if (file.size > maxSize) {
            alert('File size too large. Maximum allowed size is 10MB.');
            input.value = '';
            return false;
        }
    }
    return true;
}
</script>

<!-- hide Add button on Normal NC Tracker page only -->
<script>
$(function(){
    $('button[data-bs-target="#user_modal"]').closest('.card-toolbar').hide();
});
</script>

<script>
if (typeof jQuery === 'undefined') {
    document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
}
</script>

<script>
(function () {
  "use strict";

  function onReady(cb) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
      return cb();
    }
    document.addEventListener("DOMContentLoaded", cb);
  }

  onReady(function () {

    var $ = window.jQuery;
    var container = $("#oe_filter_container");
    if (!container.length) return;

    var urlClusters = container.data("get-clusters-url");
    var urlLocations = container.data("get-locations-url");

    var preRegion   = container.data("pre-region");   // Array or string
    var preCluster  = container.data("pre-cluster");  // Array or string
    var preLocation = container.data("pre-location"); // Array or string

    var userRegion  = container.data("user-region")   || "";
    var userCluster = container.data("user-cluster")  || "";
    var isClusterManager = container.data("is-cluster") === 1 || container.data("is-cluster") === "1";
    var isAccountManager = container.data("is-account-manager") === 1 || container.data("is-account-manager") === "1";
    // Higher Authority users get full access to all filters (read-only role with full visibility)
    var isHigherAuthority = container.data("is-higher-authority") === 1 || container.data("is-higher-authority") === "1";
    
    // If Higher Authority, override restrictions
    if (isHigherAuthority) {
        isClusterManager = false;
        isAccountManager = false;
    }

    var $region   = $("#flt_region");
    var $cluster  = $("#flt_cluster");
    var $location = $("#flt_location");

    function safeOptions(rows, key, placeholder) {
      // For multi-select, we typically don't need a placeholder option value=""
      // But keeping it consistent with Select2 empty option if needed. 
      // Actually Select2 Multiple doesn't like empty value option that much for placeholder.
      // Let's return just options.
      var html = ''; 
      rows.forEach(function (r) {
        var v = (r[key] || "").trim();
        if (v) html += `<option value="${v}">${v}</option>`;
      });
      return html;
    }

    function loadClusters(region, preselect, done) {
      // Higher Authority users never have disabled filters
      if (!isHigherAuthority && (isClusterManager || isAccountManager)) {
        $cluster.prop("disabled", true); 
      } else {
        $location.html(''); // Just clear locations, don't disable
      }

      if (!region || region.length === 0) {
        // If not restricted, enable
        if (!isClusterManager && !isAccountManager) {
             $cluster.html('').prop("disabled", false);
        }
        if (done) done();
        return;
      }

      // Convert array to JSON string to ensure robust transmission
      var regionJson = Array.isArray(region) ? JSON.stringify(region) : region;

      $.ajax({
        url: urlClusters,
        type: 'POST',
        dataType: 'json',
        data: { region_name: regionJson }, 
        success: function(rows) {
            $cluster.html(safeOptions(rows, "cluster_name", "Select Cluster"));

            if (isClusterManager) {
                $cluster.val(userCluster ? [userCluster] : []);
            } else if (isAccountManager) {
                 // AM preselect handled by server side rendering of options or preselect variable
                 if (preselect) $cluster.val(preselect);
            } else {
                if (preselect) $cluster.val(preselect);
                $cluster.prop("disabled", false);
            }

            if ($.fn.select2) $cluster.trigger("change.select2");
            if (done) done();
        },
        error: function(xhr, status, error) {
            console.error("Cluster Load Error:", error);
            if (!isClusterManager && !isAccountManager) {
                 $cluster.prop("disabled", false); 
            }
            if (done) done();
        }
      });
    }

    function loadLocations(region, cluster, preselect, done) {
      // Higher Authority users never have disabled filters
      
      if (!region || region.length === 0 || !cluster || cluster.length === 0) {
           if (preselect && preselect.length > 0) {
               var html = '<option value="">Select Location</option>';
               var preArr = Array.isArray(preselect) ? preselect : [preselect];
               preArr.forEach(function(val) {
                   html += '<option value="'+val+'" selected>'+val+'</option>';
               });
               $location.html(html);
           } else {
               $location.html('').prop("disabled", false);
           }
          if (done) done();
          return;
        }
      
      var regionJson  = Array.isArray(region)  ? JSON.stringify(region)  : region;
      var clusterJson = Array.isArray(cluster) ? JSON.stringify(cluster) : cluster;

      $.ajax({
        url: urlLocations,
        type: 'POST',
        dataType: 'json',
        data: { region_name: regionJson, cluster_name: clusterJson },
        success: function(rows) {
            $location.html(safeOptions(rows, "location_name", "Select Location"));
            
            $location.prop("disabled", false);

            if (preselect) {
                $location.val(preselect);
            }

            if ($.fn.select2) $location.trigger("change.select2");
            if (done) done();
        },
        error: function() {
            $location.prop("disabled", false);
            if (done) done();
        }
      });
    }

    // Trigger cascade on CLOSE, so user can select multiple items first
    $region.on("select2:close", function () {
      loadClusters($(this).val(), null);
    });

    $cluster.on("select2:close", function () {
      loadLocations($region.val(), $(this).val(), null);
    });

    // Initial Load Logic
    if (isClusterManager || isAccountManager) {
       var rVal = $region.val(); 
       var cVal = $cluster.val();
       
       loadLocations(rVal, cVal, preLocation || null);

    } else if (preRegion && preRegion.length > 0) {
       // Pre-selected values exist (reloading page with filters)
       // HTML 'selected' takes care of $region visual.
       
       // Load Clusters
       loadClusters(preRegion, preCluster, function () {
         setTimeout(function () {
           // Load Locations
           loadLocations(preRegion, preCluster, preLocation);
         }, 120);
       });
    }

    // Checkbox styling for Select2
    function formatOption(state) {
        if (!state.id) {
            return state.text;
        }
        // We rely on CSS for the checkbox/check mark, but we could add icons here.
        // Returning standard text allows CSS pseudo-elements to work best for 'aria-selected'.
        return state.text;
    }
  });
})();
</script>

<!-- Select2 CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<style>
/* Custom styled checkboxes for Select2 */
.select2-results__option {
    padding-left: 30px !important;
    position: relative;
    color: #3f4254;
}

.select2-results__option:before {
    content: "";
    display: inline-block;
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    border: 1px solid #d8d8d8;
    border-radius: 4px;
    background-color: #fff;
    transition: all 0.2s ease;
}

/* Checked state: Blue Checkbox with Tick (no row highlight) */
.select2-container--default .select2-results__option[aria-selected=true]:before {
    background-color: #009ef7; /* Primary color for checkbox only */
    border-color: #009ef7;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
    background-size: 12px 12px;
    background-position: center;
    background-repeat: no-repeat;
}

/* IMPORTANT: Remove default tick from the row background (Select2 theme often adds it here) */
.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: transparent !important;
    background-image: none !important; /* Kills the right-side tick */
    color: #3f4254 !important;
}

/* Hover state: No highlight on row */
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: transparent !important;
    background-image: none !important;
    color: #3f4254 !important; 
}

/* Ensure Checkbox does NOT change on hover (no tick, no blue) */
.select2-container--default .select2-results__option--highlighted[aria-selected=false]:before {
    background-color: #fff !important;
    border-color: #d8d8d8 !important;
    background-image: none !important;
}

/* Adjust the main container height/width */
.select2-container .select2-selection--multiple {
    min-height: 38px;
    border: 1px solid #e4e6ef;
    background-color: #fff;
    width: 100% !important; /* Ensure full width */
}
/* Match single select height to multiple */
.select2-container .select2-selection--single {
    height: 38px !important;
    border: 1px solid #e4e6ef;
    display: flex;
    align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
    top: 1px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: normal !important;
}
.select2-container {
    width: 100% !important; /* Force container to fill column */
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #b5b5c3;
}
</style>

<script>
$(document).ready(function() {
    // Initialize Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $('.js-example-basic-single').each(function() {
            var isMultiple = $(this).attr('multiple') === 'multiple';
            $(this).select2({ 
                placeholder: "Select option(s)", 
                allowClear: true, 
                width: '100%', // ensure full width
                closeOnSelect: !isMultiple, // Keep open for multi-select
            });
        });
    }
});
</script>
</script>

<script>
// ---------- BULK CLOSE LOGIC ----------

// Checkbox selection array
let selectedNcIds = [];

// Handle "Select All" click
$(document).on('change', '#selectAllNc', function() {
    let isChecked = $(this).prop('checked');
    selectedNcIds = [];
    
    $('.nc-checkbox').each(function() {
        $(this).prop('checked', isChecked);
        if (isChecked) {
            selectedNcIds.push($(this).val());
        }
    });
    
    updateBulkToolbar();
});

// Handle individual row checkbox click
$(document).on('change', '.nc-checkbox', function() {
    let id = $(this).val();
    if ($(this).prop('checked')) {
        if (!selectedNcIds.includes(id)) selectedNcIds.push(id);
    } else {
        selectedNcIds = selectedNcIds.filter(val => val !== id);
        $('#selectAllNc').prop('checked', false);
    }
    
    // Check if all are selected
    if ($('.nc-checkbox').length > 0 && selectedNcIds.length === $('.nc-checkbox').length) {
        $('#selectAllNc').prop('checked', true);
    }
    
    updateBulkToolbar();
});

// Update toolbar visibility
function updateBulkToolbar() {
    $('#selectedCount').text(selectedNcIds.length);
    if (selectedNcIds.length > 0) {
        $('#bulkActionToolbar').removeClass('d-none');
    } else {
        $('#bulkActionToolbar').addClass('d-none');
    }
}

function clearBulkSelection() {
    selectedNcIds = [];
    $('.nc-checkbox').prop('checked', false);
    $('#selectAllNc').prop('checked', false);
    updateBulkToolbar();
}

function openBulkCloseModal() {
    if (selectedNcIds.length === 0) return;
    $('#modalSelectedCount').text(selectedNcIds.length);
    $('#bulkCloseModal').modal('show');
}

function submitBulkClose() {
    if (selectedNcIds.length === 0) {
        toastr.error("No NCs selected");
        return;
    }
    
    let btn = $('#btn_submit_bulk_close');
    btn.attr('data-kt-indicator', 'on').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('Masters/Normal_nc_tracker/bulk_close_nc') ?>',
        type: 'POST',
        dataType: 'json',
        data: {
            nc_ids: selectedNcIds
        },
        success: function(res) {
            btn.attr('data-kt-indicator', 'off').prop('disabled', false);
            if (res.status == 1) {
                toastr.success(res.message);
                $('#bulkCloseModal').modal('hide');
                $('#bulkCloseForm')[0].reset();
                
                // Clear selection
                clearBulkSelection();
                
                // Reload DataTable
                if (typeof dTable !== 'undefined' && dTable) {
                    dTable.ajax.reload(null, false);
                } else if ($.fn.DataTable.isDataTable('#kt_table_users')) {
                    $('#kt_table_users').DataTable().ajax.reload(null, false);
                } else {
                    location.reload();
                }
            } else {
                toastr.error(res.message || "Failed to close NCs");
            }
        },
        error: function(err) {
            btn.attr('data-kt-indicator', 'off').prop('disabled', false);
            toastr.error("An error occurred while processing your request");
        }
    });
}
</script>

<?php $this->endSection();?>
