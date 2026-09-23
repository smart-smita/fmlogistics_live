<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<style>
    .sd-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .detail-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .detail-value {
        font-size: 1.05rem;
        color: #212529;
        font-weight: 500;
        margin-bottom: 20px;
    }
    .preview-container {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        height: 600px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .preview-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
</style>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-dark fw-bold"><?= esc($document['doc_name']) ?></h3>
            <span class="badge bg-primary mt-2">v<?= esc($document['version_number']) ?></span>
            <?php 
                $badge = 'bg-success';
                if($document['status'] == 'Inactive') $badge = 'bg-secondary';
                if($document['status'] == 'Archived') $badge = 'bg-dark';
            ?>
            <span class="badge <?= $badge ?> mt-2 ms-2"><?= $document['status'] ?></span>
            
            <?php if ($document['is_deleted']): ?>
                <span class="badge bg-danger mt-2 ms-2">Deleted</span>
            <?php endif; ?>
        </div>
        <div>
            <a href="<?= base_url('software-documentation/download/'.$document['id']) ?>" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-download me-2"></i> Download Latest
            </a>
            <a href="<?= base_url('software-documentation') ?>" class="btn btn-outline-secondary rounded-pill px-4 ms-2">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Metadata -->
        <div class="col-lg-4">
            <div class="card sd-card h-100">
                <div class="card-header bg-white border-bottom pt-4 pb-3">
                    <h5 class="fw-bold mb-0">Document Details</h5>
                </div>
                <div class="card-body">
                    <div class="detail-label">Document Number</div>
                    <div class="detail-value"><?= esc($document['doc_number']) ?: '-' ?></div>

                    <div class="detail-label">Category</div>
                    <div class="detail-value"><?= esc($document['category']) ?></div>

                    <div class="detail-label">Sub Category</div>
                    <div class="detail-value"><?= esc($document['sub_category']) ?: '-' ?></div>

                    <div class="detail-label">Department</div>
                    <div class="detail-value"><?= esc($document['department']) ?: '-' ?></div>

                    <div class="detail-label">Keywords</div>
                    <div class="detail-value"><?= esc($document['keywords']) ?: '-' ?></div>
                    
                    <div class="detail-label">Description</div>
                    <div class="detail-value"><?= esc($document['description']) ?: '-' ?></div>

                    <hr>

                    <div class="row">
                        <div class="col-6">
                            <div class="detail-label">Effective Date</div>
                            <div class="detail-value"><?= $document['effective_date'] ? date('d M, Y', strtotime($document['effective_date'])) : '-' ?></div>
                        </div>
                        <div class="col-6">
                            <div class="detail-label">Review Date</div>
                            <div class="detail-value"><?= $document['review_date'] ? date('d M, Y', strtotime($document['review_date'])) : '-' ?></div>
                        </div>
                        <div class="col-6">
                            <div class="detail-label">Expiry Date</div>
                            <div class="detail-value text-danger"><?= $document['expiry_date'] ? date('d M, Y', strtotime($document['expiry_date'])) : '-' ?></div>
                        </div>
                        <div class="col-6">
                            <div class="detail-label">Total Downloads</div>
                            <div class="detail-value"><?= $downloads ?></div>
                        </div>
                    </div>
                    
                    <hr>

                    <div class="detail-label">File Name</div>
                    <div class="detail-value" style="word-break: break-all;"><?= esc($document['file_name']) ?></div>
                    
                    <div class="detail-label">File Type</div>
                    <div class="detail-value"><?= esc(strtoupper($document['file_type'])) ?></div>
                </div>
            </div>
        </div>

        <!-- Preview & Versions -->
        <div class="col-lg-8">
            <div class="card sd-card mb-4">
                <div class="card-header bg-white border-bottom pt-4 pb-3">
                    <h5 class="fw-bold mb-0">Preview</h5>
                </div>
                <div class="card-body">
                    <?php 
                        $ext = strtolower($document['file_type']);
                        $previewUrl = base_url('software-documentation/download/'.$document['id']);
                        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])):
                    ?>
                        <div class="preview-container">
                            <?php if ($ext == 'pdf'): ?>
                                <iframe src="<?= $previewUrl ?>"></iframe>
                            <?php else: ?>
                                <img src="<?= $previewUrl ?>" style="max-width:100%; max-height:100%; object-fit:contain;">
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="preview-container flex-column">
                            <i class="fas fa-file-alt text-muted mb-3" style="font-size: 4rem;"></i>
                            <h5 class="text-muted">Preview not available for this file type.</h5>
                            <a href="<?= $previewUrl ?>" class="btn btn-primary mt-3">Download File to View</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Version History -->
            <?php if(!empty($versions)): ?>
            <div class="card sd-card">
                <div class="card-header bg-white border-bottom pt-4 pb-3">
                    <h5 class="fw-bold mb-0">Version History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Version</th>
                                    <th>Revision</th>
                                    <th>File Name</th>
                                    <th>Upload Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($versions as $v): ?>
                                <tr>
                                    <td><span class="badge bg-secondary">v<?= esc($v['version_number']) ?></span></td>
                                    <td><?= esc($v['revision_number']) ?: '-' ?></td>
                                    <td><small><?= esc($v['file_name']) ?></small></td>
                                    <td><?= date('d M, Y H:i', strtotime($v['upload_date'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('software-documentation/download/'.$document['id'].'/'.$v['id']) ?>" class="btn btn-sm btn-outline-primary" title="Download Version">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>
