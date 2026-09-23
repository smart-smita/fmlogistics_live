<?php $this->extend("Layout/base_admin"); ?>

<?php $this->section("breadcrumb_title_li"); ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    /* ---- KPI cards — reference style ---- */
    .kpi-card {
        border-radius: 14px;
        border: 1px solid #ced4da;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .055);
        transition: transform .18s, box-shadow .18s;
        height: 100%;
        overflow: hidden;
        position: relative;
        cursor: pointer;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, .11);
    }

    /* decorative bubble — bottom-right */
    .kpi-card::after {
        content: '';
        position: absolute;
        bottom: -22px;
        right: -22px;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        opacity: .13;
        pointer-events: none;
    }

    .kpi-card.kpi-blue::after { background: #2563eb; }
    .kpi-card.kpi-indigo::after { background: #4f46e5; }
    .kpi-card.kpi-amber::after { background: #d97706; }
    .kpi-card.kpi-green::after { background: #16a34a; }
    .kpi-card.kpi-orange::after { background: #ea580c; }
    .kpi-card.kpi-purple::after { background: #9333ea; }
    .kpi-card.kpi-red::after { background: #dc2626; }
    .kpi-card.kpi-teal::after { background: #0d9488; }

    .kpi-card .card-body {
        padding: 1.05rem 1.15rem;
        display: flex;
        align-items: center;
        gap: 14px;
        position: relative;
        z-index: 1;
    }

    /* square icon */
    .kpi-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        color: #fff;
    }

    .kpi-icon-box.bg-kpi-blue { background: #2563eb; }
    .kpi-icon-box.bg-kpi-indigo { background: #4f46e5; }
    .kpi-icon-box.bg-kpi-amber { background: #d97706; }
    .kpi-icon-box.bg-kpi-green { background: #16a34a; }
    .kpi-icon-box.bg-kpi-orange { background: #ea580c; }
    .kpi-icon-box.bg-kpi-purple { background: #9333ea; }
    .kpi-icon-box.bg-kpi-red { background: #dc2626; }
    .kpi-icon-box.bg-kpi-teal { background: #0d9488; }

    /* text block */
    .kpi-text {
        flex: 1;
        min-width: 0;
    }

    .kpi-label {
        font-size: .85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #495057;
        margin-bottom: 2px;
        word-wrap: break-word;
        white-space: normal;
    }

    .kpi-value {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1.05;
        color: #1a1a2e;
        margin: 0;
    }

    .kpi-sub {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 2px;
        word-wrap: break-word;
        white-space: normal;
    }

    @media (max-width: 1199px) { .kpi-value { font-size: 1.6rem; } }
    @media (max-width: 767px) {
        .kpi-value { font-size: 1.45rem; }
        .kpi-icon-box { width: 44px; height: 44px; font-size: 1.2rem; }
    }
    @media (max-width: 575px) {
        .kpi-value { font-size: 1.25rem; }
        .kpi-card .card-body { gap: 10px; padding: .85rem .95rem; }
    }
</style>
<?php $this->endSection(); ?>

<?php $this->section("main_body"); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 text-dark fw-bold">Software Documentation</h3>
        <?php if (in_array($role, [1, 2])): ?>
            <a href="<?= base_url('software-documentation/create') ?>" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-upload me-2 text-white"></i> Upload Document
            </a>
        <?php endif; ?>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-5">
        <!-- 1. Total Documents -->
        <div class="col-xl-3 col-md-4 col-sm-6 col-12">
            <div class="card kpi-card kpi-blue" onclick="window.location.href='<?= base_url('software-documentation') ?>'">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-blue">
                        <i class="fas fa-copy text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Total Documents</div>
                        <div class="kpi-value"><?= $total_docs ?></div>
                        <div class="kpi-sub">All Files</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Definitions -->
        <div class="col-xl-3 col-md-4 col-sm-6 col-12">
            <div class="card kpi-card kpi-green" onclick="window.location.href='<?= base_url('software-definitions') ?>'">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-green">
                        <i class="fas fa-book text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Total Definitions</div>
                        <div class="kpi-value"><?= $total_defs ?></div>
                        <div class="kpi-sub">Knowledge Base</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. SOPs -->
        <div class="col-xl-3 col-md-4 col-sm-6 col-12">
            <div class="card kpi-card kpi-indigo" onclick="window.location.href='<?= base_url('software-documentation') ?>?category=SOPs'">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-indigo">
                        <i class="fas fa-file-pdf text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">SOPs</div>
                        <div class="kpi-value"><?= $total_sops ?></div>
                        <div class="kpi-sub">Standard Ops</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Protocols -->
        <div class="col-xl-3 col-md-4 col-sm-6 col-12">
            <div class="card kpi-card kpi-amber" onclick="window.location.href='<?= base_url('software-documentation') ?>?category=Protocols'">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-amber">
                        <i class="fas fa-clipboard-check text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Protocols</div>
                        <div class="kpi-value"><?= $total_protocols ?></div>
                        <div class="kpi-sub">Rules & Protocols</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. OE Documents -->
        <div class="col-xl-3 col-md-4 col-sm-6 col-12">
            <div class="card kpi-card kpi-orange" onclick="window.location.href='<?= base_url('software-documentation') ?>?category=OE Documents'">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-orange">
                        <i class="fas fa-folder-open text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">OE Documents</div>
                        <div class="kpi-value"><?= $total_oe ?></div>
                        <div class="kpi-sub">Operations</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Policies -->
        <div class="col-xl-3 col-md-4 col-sm-6 col-12">
            <div class="card kpi-card kpi-red" onclick="window.location.href='<?= base_url('software-documentation') ?>?category=Policies'">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-red">
                        <i class="fas fa-shield-alt text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Policies</div>
                        <div class="kpi-value"><?= $total_policies ?></div>
                        <div class="kpi-sub">Company Policies</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Manuals -->
        <div class="col-xl-3 col-md-4 col-sm-6 col-12">
            <div class="card kpi-card kpi-purple" onclick="window.location.href='<?= base_url('software-documentation') ?>?category=Manuals'">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-purple">
                        <i class="fas fa-book-open text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">Manuals</div>
                        <div class="kpi-value"><?= $total_manuals ?></div>
                        <div class="kpi-sub">User Manuals</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. HSE Checklists -->
        <div class="col-xl-3 col-md-4 col-sm-6 col-12">
            <div class="card kpi-card kpi-teal" onclick="window.location.href='<?= base_url('software-documentation') ?>?category=HSE Grid Checklist'">
                <div class="card-body">
                    <div class="kpi-icon-box bg-kpi-teal">
                        <i class="fas fa-tasks text-white"></i>
                    </div>
                    <div class="kpi-text">
                        <div class="kpi-label">HSE Checklists</div>
                        <div class="kpi-value"><?= $total_hse ?></div>
                        <div class="kpi-sub">Grid Checklists</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Layout/table-view handles the datatable rendering -->
    <?= isset($table) ? $table : '' ?>
</div>
<?php $this->endSection(); ?>

<?php $this->section("javascript_section"); ?>
<?php $this->endSection(); ?>
