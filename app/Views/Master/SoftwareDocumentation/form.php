<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<style>
    .sd-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .form-label {
        font-weight: 600;
        color: #3f4254;
    }
    .custom-file-upload {
        border: 2px dashed #009ef7;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        background: #f1faff;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .custom-file-upload:hover {
        background: #e1f5ff;
    }
</style>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-dark fw-bold"><?= isset($document) ? 'Edit Document' : 'Upload Document' ?></h3>
        <a href="<?= base_url('software-documentation') ?>" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
    </div>

    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card sd-card">
        <div class="card-body p-4">
            <?php 
                $action = isset($document) ? base_url('software-documentation/update/'.$document['id']) : base_url('software-documentation/store'); 
            ?>
            <form action="<?= $action ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                
                <div class="row g-4">
                    <!-- Left Column: Details -->
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" name="category" id="category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach($categories as $cat => $subs): ?>
                                        <option value="<?= esc($cat) ?>" <?= (isset($document) && $document['category'] == $cat) ? 'selected' : '' ?>><?= esc($cat) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" id="lbl_doc_name">Document Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="doc_name" value="<?= isset($document) ? esc($document['doc_name']) : old('doc_name') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" id="lbl_doc_number">Document Number</label>
                                <input type="text" class="form-control" name="doc_number" value="<?= isset($document) ? esc($document['doc_number']) : old('doc_number') ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Version Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="version_number" value="<?= isset($document) ? esc($document['version_number']) : '1.0' ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Revision Number</label>
                                <input type="text" class="form-control" name="revision_number" value="<?= isset($document) ? esc($document['revision_number']) : old('revision_number') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" name="department" value="<?= isset($document) ? esc($document['department']) : old('department') ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Effective Date</label>
                                <input type="date" class="form-control" name="effective_date" value="<?= isset($document) ? esc($document['effective_date']) : old('effective_date') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Review Date</label>
                                <input type="date" class="form-control" name="review_date" value="<?= isset($document) ? esc($document['review_date']) : old('review_date') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" name="expiry_date" value="<?= isset($document) ? esc($document['expiry_date']) : old('expiry_date') ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Keywords (Comma separated)</label>
                                <input type="text" class="form-control" name="keywords" value="<?= isset($document) ? esc($document['keywords']) : old('keywords') ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"><?= isset($document) ? esc($document['description']) : old('description') ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="Active" <?= (isset($document) && $document['status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= (isset($document) && $document['status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                                    <option value="Archived" <?= (isset($document) && $document['status'] == 'Archived') ? 'selected' : '' ?>>Archived</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: File Upload -->
                    <div class="col-md-4">
                        <label class="form-label">Upload File <?= isset($document) ? '<small class="text-muted">(Leave empty to keep existing file)</small>' : '<span class="text-danger">*</span>' ?></label>
                        <div class="custom-file-upload mt-2" onclick="document.getElementById('document_file').click()">
                            <i class="fas fa-cloud-upload-alt text-primary mb-3" style="font-size: 3rem;"></i>
                            <h5>Drag & Drop or Click to Browse</h5>
                            <p class="text-muted small mb-0">Supported files: PDF, DOC, DOCX, XLS, XLSX, CSV, PPT, PPTX, JPG, JPEG, PNG</p>
                            <input type="file" id="document_file" name="document_file" style="display:none;" onchange="updateFileName(this)">
                        </div>
                        <div id="file-name-display" class="mt-3 text-center fw-bold text-primary">
                            <?php if(isset($document)): ?>
                                Current File: <?= esc($document['file_name']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                
                <hr class="my-4">
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-5">
                        <i class="fas fa-save me-2"></i> <?= isset($document) ? 'Save Changes' : 'Upload Document' ?>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<script>
    const categoriesMap = <?= json_encode($categories) ?>;

    function updateFileName(input) {
        if(input.files && input.files[0]) {
            document.getElementById('file-name-display').innerHTML = 'Selected: ' + input.files[0].name;
        }
    }

    $(document).ready(function() {
        function updateLabels(cat) {
            let nameLbl = 'Document Name <span class="text-danger">*</span>';
            let numLbl = 'Document Number';
            
            if (cat === 'SOPs') {
                nameLbl = 'SOP Name <span class="text-danger">*</span>';
                numLbl = 'SOP Number';
            } else if (cat === 'Protocols') {
                nameLbl = 'Protocol Name <span class="text-danger">*</span>';
                numLbl = 'Protocol Number';
            } else if (cat === 'Policies') {
                nameLbl = 'Policy Name <span class="text-danger">*</span>';
                numLbl = 'Policy Number';
            } else if (cat === 'Manuals') {
                nameLbl = 'Manual Name <span class="text-danger">*</span>';
                numLbl = 'Manual Number';
            } else if (cat === 'HSE Grid Checklist') {
                nameLbl = 'Checklist Name <span class="text-danger">*</span>';
                numLbl = 'Checklist Number';
            }

            $('#lbl_doc_name').html(nameLbl);
            $('#lbl_doc_number').html(numLbl);
        }

        function updateLabelsOnCategoryChange() {
            var cat = $('#category').val();
            updateLabels(cat);
        }

        $('#category').on('change', updateLabelsOnCategoryChange);
        // Initial load for edit mode
        updateLabelsOnCategoryChange();
    });
</script>
<?php $this->endSection(); ?>
