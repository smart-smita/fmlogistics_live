<?php 
$this->extend("Layout/base_admin");
?>

<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?=base_url("Customer/Audit_dashboard/OE_Audit")?>" class="text-muted text-hover-primary">Create Form</a>
	</li>
<!--end::Item-->
<?php 
					$this->endSection();
?>										
<?php 
					$this->section("main_body");
?>
<!--begin::Input group-->
<a href="#" class="btn btn-link btn-color-info  me-5 mb-2">Template Setup</a>
<a href="#" class="btn btn-link btn-color-gray-500  me-5 mb-2">Edit Setup</a>
<a href="#" class="btn btn-link btn-color-gray-500  me-5 mb-2">Review</a>



<ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_pane_4">Link 1</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_5">Link 2</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_6">Link 3</a>
    </li>
</ul>

<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="kt_tab_pane_4" role="tabpanel">
       <div class="row">
           <div class="col-md-8"> 
<!--begin::Input group-->
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Title</h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light">
                Action
            </button>
        </div>
    </div>
    <div class="card-body">
        
        <form>
            <h2 class="fw-bold text-gray-900">1. Basic Settings.</h2>
            <div class="text-muted fw-semibold fs-6">
Provide the template name and select the language to tailor your content to the right audience.
        </div>
        <br><hr/><br>
        <div class="row">
        
        <div class="col-md-8 mb-10 fv-row fv-plugins-icon-container">      
        <!--begin::Label-->                  
        <label class="form-label mb-3">Template Name</label>
        <!--end::Label-->

        <!--begin::Input-->
        <input type="text" class="form-control form-control-lg form-control-solid" name="account_name" placeholder="" value="" autocomplete="off">
        <!--end::Input-->
            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback"></div>
        </div>
    <div class="col-md-4 mb-10 fv-row fv-plugins-icon-container">      
        <!--begin::Label-->                  
        <label class="form-label mb-3">Language</label>
        <!--end::Label-->

        <!--begin::Input-->
       <select class="form-select" data-control="select2" data-placeholder="Select an option">
    <option>English</option>
    <option value="1">French</option>
    <option value="2">German 2</option>
</select>

        <br><hr/><br>
        
        </div>
                <h2 class="fw-bold text-gray-900">2. Content</h2>
            <div class="text-muted fw-semibold fs-6">
This will be the primary text audience sees.so make it clear and engaging.        </div>

<hr>

        <label class="form-label mb-3">Message Body Text</label>
  <div class="py-5" data-bs-theme="light">
    <textarea name="kt_docs_ckeditor_classic" id="editor1">
    </textarea>
</div>
        </div>
</form>
        
    </div>
</div>
</div>
<div class="col-md-4"> 
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Title</h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light">
                Action
            </button>
        </div>
    </div>
    <div class="card-body">
        Lorem Ipsum is simply dummy text...
    </div>
    <div class="card-footer">
        Footer
    </div>
</div>

</div>
</div>

    </div>
    <div class="tab-pane fade" id="kt_tab_pane_5" role="tabpanel">
        ...
    </div>
    <div class="tab-pane fade" id="kt_tab_pane_6" role="tabpanel">
        ...
    </div>
</div>








<?php
	$this->endSection();
?>
<?php 					$this->section("javascript_section");?>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

<script>
                        CKEDITOR.replace( 'editor1' );

</script>



<?php 
					$this->endSection();
?>