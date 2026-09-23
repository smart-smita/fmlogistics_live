<?php 
$this->extend("Layout/base_admin");
?>

<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?=base_url("Customer/Audit_dashboard/OE_Audit")?>" class="text-muted text-hover-primary">Steps</a>
	</li>
<!--end::Item-->
<?php 
					$this->endSection();
?>										
<?php 
					$this->section("main_body");
?>
<!--begin::Input wrapper-->
<a href="#" class="btn btn-link btn-color-info me-5 mb-2">
  <i class="bi bi-clipboard"></i> Template Setup
</a>
<a href="#" class="btn btn-link btn-color-gray-500  me-5 mb-2">
  <i class="bi bi-pencil"></i> Edit Template
</a>
<a href="#" class="btn btn-link btn-color-gray-500  me-5 mb-2">
  <i class="bi bi-pencil"></i> Review
</a>
<div class="mb-5 hover-scroll-x">
    <div class="d-grid">
        <ul class="nav nav-tabs flex-nowrap text-nowrap">
            <li class="nav-item">
                <a class="nav-link btn btn-active-light btn-color-gray-500 btn-active-color-primary rounded-bottom-0" data-bs-toggle="tab" href="#kt_tab_pane_1">Marketing</a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-active-light btn-color-gray-500 btn-active-color-primary rounded-bottom-0" data-bs-toggle="tab" href="#kt_tab_pane_1">Utility</a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-active-light btn-color-gray-500 btn-active-color-primary rounded-bottom-0" data-bs-toggle="tab" href="#kt_tab_pane_1">Authintication</a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-active-light btn-color-gray-500 btn-active-color-primary rounded-bottom-0" data-bs-toggle="tab" href="#kt_tab_pane_1">Custem</a>
            </li>
            
        </ul>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Media card carosel</h3>
        <div class="card-toolbar">
            <a href="#" class="btn btn-icon btn-sm btn-active-color-primary" data-kt-card-action="remove" data-kt-card-confirm="true" data-kt-card-confirm-message="Are you sure to remove this card ?" data-bs-toggle="tooltip" title="Remove card" data-bs-dismiss="click">
                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
            </a>
        </div>
    </div>
    <div class="card-body">
        Media card carousel templates allow you to send a single text massage 
    </div>
</div>
<div class="row">
   <div class="col-md-8"></div>
   <div class="col-md-4">
  <!-- Empty div container for buttons -->
  <div class="button-container flex-right">
    <a href="#" class="btn btn-light">Cancel</a>
    <a href="#" class="btn btn-primary">Continue</a>
  </div>

  </div>
  </div>

<!--end::Input wrapper-->

<?php 
					$this->endSection();
?>
<?php 					$this->section("javascript_section");?>

<script>
</script>



<?php 
					$this->endSection();
?>
