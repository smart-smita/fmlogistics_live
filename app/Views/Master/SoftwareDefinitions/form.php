<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
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
    .note-editor .note-editing-area .note-editable {
        min-height: 250px;
        font-family: inherit;
    }
</style>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-dark fw-bold"><?= isset($definition) ? 'Edit Question' : 'Add Question' ?></h3>
        <a href="<?= base_url('software-definitions') ?>" class="btn btn-outline-secondary rounded-pill px-4">
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
                $action = isset($definition) ? base_url('software-definitions/update/'.$definition['id']) : base_url('software-definitions/store'); 
            ?>
            <form action="<?= $action ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label">Question <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" name="question" placeholder="e.g. What is a Near Miss?" value="<?= isset($definition) ? esc($definition['question']) : old('question') ?>" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Answer <span class="text-danger">*</span></label>
                        <textarea id="summernote" name="answer" required><?= isset($definition) ? $definition['answer'] : old('answer') ?></textarea>
                    </div>



                    <div class="col-md-6">
                        <label class="form-label">Display Order (Lower numbers show first)</label>
                        <input type="number" class="form-control" name="display_order" value="<?= isset($definition) ? esc($definition['display_order']) : '0' ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="Active" <?= (isset($definition) && $definition['status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                            <option value="Inactive" <?= (isset($definition) && $definition['status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                            <option value="Archived" <?= (isset($definition) && $definition['status'] == 'Archived') ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-5">
                        <i class="fas fa-save me-2"></i> <?= isset($definition) ? 'Save Changes' : 'Publish Question' ?>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    const categoriesMap = <?= json_encode($categories) ?>;

    $(document).ready(function() {
        $('#summernote').summernote({
            placeholder: 'Write your comprehensive answer here...',
            tabsize: 2,
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });

        function loadSubcategories() {
            var cat = $('#category').val();
            var $sub = $('#sub_category');
            var selectedSub = $('#selected_sub').val();
            
            $sub.empty().append('<option value="">Select Sub Category</option>');
            
            if(cat && categoriesMap[cat] && categoriesMap[cat].length > 0) {
                categoriesMap[cat].forEach(function(sc) {
                    var isSelected = (sc === selectedSub) ? 'selected' : '';
                    $sub.append('<option value="'+sc+'" '+isSelected+'>'+sc+'</option>');
                });
            }
        }

        $('#category').on('change', loadSubcategories);
        loadSubcategories();
    });
</script>
<?php $this->endSection(); ?>
