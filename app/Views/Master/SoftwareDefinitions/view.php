<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<style>
    .sd-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .question-header {
        font-size: 1.5rem;
        color: #181C32;
        font-weight: 700;
        margin-bottom: 20px;
    }
    .answer-body {
        font-size: 1.05rem;
        color: #3F4254;
        line-height: 1.8;
    }
    .answer-body img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 15px 0;
    }
    .answer-body table {
        width: 100% !important;
        margin-bottom: 1rem;
        color: #212529;
        border-collapse: collapse;
    }
    .answer-body table th,
    .answer-body table td {
        padding: 0.75rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
    }
    .meta-label {
        font-weight: 600;
        color: #7E8299;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-dark fw-bold">Knowledge Base</h3>
        <a href="<?= base_url('software-definitions') ?>" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i> Back to Definitions
        </a>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card sd-card h-100">
                <div class="card-body p-5">
                    <h1 class="question-header">Q: <?= esc($definition['question']) ?></h1>
                    <hr class="my-4">
                    <div class="answer-body">
                        <!-- We deliberately do not escape here because Summernote returns raw HTML -->
                        <?= $definition['answer'] ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Metadata -->
        <div class="col-lg-4">
            <div class="card sd-card">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Details</h5>
                    
                    <div class="mb-3">
                        <div class="meta-label">Category</div>
                        <div class="fs-6 text-dark fw-semibold"><?= esc($definition['category']) ?></div>
                    </div>

                    <?php if($definition['sub_category']): ?>
                    <div class="mb-3">
                        <div class="meta-label">Sub Category</div>
                        <div class="fs-6 text-dark"><?= esc($definition['sub_category']) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if($definition['keywords']): ?>
                    <div class="mb-3">
                        <div class="meta-label">Keywords</div>
                        <div class="fs-6 text-dark">
                            <?php 
                                $tags = explode(',', $definition['keywords']);
                                foreach($tags as $tag):
                            ?>
                                <span class="badge bg-light text-primary border border-primary me-1 mb-1"><?= esc(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <div class="meta-label">Status</div>
                        <?php 
                            $badge = 'bg-success';
                            if($definition['status'] == 'Inactive') $badge = 'bg-secondary';
                            if($definition['status'] == 'Archived') $badge = 'bg-dark';
                        ?>
                        <span class="badge <?= $badge ?> mt-1"><?= $definition['status'] ?></span>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="meta-label">Created At</div>
                        <div class="fs-6 text-muted"><?= date('d M, Y H:i', strtotime($definition['created_at'])) ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="meta-label">Last Updated</div>
                        <div class="fs-6 text-muted"><?= $definition['updated_at'] ? date('d M, Y H:i', strtotime($definition['updated_at'])) : '-' ?></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>
