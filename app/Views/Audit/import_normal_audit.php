<?php $this->extend("Layout/base_admin"); ?>
<?php 
					$this->section("breadcrumb_title_li");
?>

<style>
      #result {
            /*border: 1px solid #ccc;*/
            /*max-width: 300px;*/
            /*margin-top: 5px;*/
            /*position: absolute;*/
            background: darkgrey;
        }
        #resultAuditor {
            background: darkgrey;
            position:absolute;
        }
        .result-item {
            padding: 8px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
        }
        .result-item:hover {
            background-color: #f0f0f0;
        }
        .result-item1 {
            padding: 8px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
        }
        .result-item1:hover {
            background-color: #f0f0f0;
        }
</style>
<!--begin::Item-->
	<li class="breadcrumb-item text-muted">
		<a href="<?= current_url() ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"Client"?></a>
	</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>

<?php

?>
    <form  id="auditForm" method="POST" enctype="multipart/form-data" onsubmit="return validate()">
        <div class="row">
        	<div class="col-sm-12">
        		<h4 class="page-title"><?=(isset($page_title))?$page_title:""?></h4>
        	</div>
        </div>
        <div class="col-md-12 mb-5">
			<div class="card">
				<div class="card-body">
				    <div class="row mb-12">
				        <div class="col-md-12 fv-row fv-plugins-icon-container">
                            <label class="required fs-5 fw-bold-2">Select Audit Option</label>
                            <select class="form-control form-control-solid audit-type-select" id="audit_type" name="audit_type">
                                <option value="">-- Select --</option>
                                <option value="Normal">Normal</option>
                                <option value="OE">OE</option>
                                <option value="HSE">HSE</option>
                            </select>
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
						<!--begin::Col-->
						<div class="col-md-12 fv-row fv-plugins-icon-container">
							<label class="required fs-5 fw-bold-2">Upload File 1</label>
							<input type="file" class="form-control form-control-solid" id="file1" name="file1" accept=".jpg,.jpeg,.png,.gif,.pdf,.xls,.xlsx,.csv" onchange="validateFileType(this)">
							<div class="fv-plugins-message-container invalid-feedback"></div>
						    <div class="fv-plugins-message-container invalid-feedback"></div>
						</div>
						
						<div class="col-md-12 fv-row fv-plugins-icon-container">
							<label class="required fs-5 fw-bold mb-2">Upload File 1</label>
							<input type="file" class="form-control form-control-solid" id="file2" name="file2" accept=".jpg,.jpeg,.png,.gif,.pdf,.xls,.xlsx,.csv" onchange="validateFileType(this)">
							<div class="fv-plugins-message-container invalid-feedback"></div>
						<div class="fv-plugins-message-container invalid-feedback"></div></div>
						<hr>
						<div class="col-md-3 fv-row fv-plugins-icon-container">
        					<button type="submit" class="btn btn-primary">
        				        Submit
        				    </button>
						</div>
					</div>
                </div>
            </div>
        </div>
    </form>
              <script>
document.addEventListener("DOMContentLoaded", function () {
    const select = document.querySelector(".audit-type-select");
    const form = document.querySelector("form");

    if (select) {
        select.addEventListener("change", function () {
            const selected = this.value;

            if (selected === "Normal") {
                form.action = "<?= base_url('Masters/Audit_template/import_normal_save_perform_audit') ?>";
            } else if (selected === "OE") {
                form.action = "<?= base_url('Masters/Audit_template/import_perform_audit') ?>";
            } else if (selected === "HSE") {
                form.action = "<?= base_url('Masters/Audit_template/import_HSE_save_perform_audit') ?>";
            } else {
                form.action = "#";
            }
        });
    }
});
// File type validation function
function validateFileType(input) {
    const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'xls', 'xlsx', 'csv'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const extension = file.name.split('.').pop().toLowerCase();
        
        // Check file type
        if (!allowedTypes.includes(extension)) {
            alert('Invalid file type. Only images (JPG, PNG, GIF), PDFs, and Excel files (XLS, XLSX, csv) are allowed.');
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



<?php 					$this->section("javascript_section");?>
  
<?php $this->endSection();?>	

