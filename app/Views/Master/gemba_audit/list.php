<?= $this->extend('Layout/base_admin') ?>

<?= $this->section('main_body') ?>
<div class="container-fluid mt-4">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
            <i class="fas fa-check-circle fs-4 me-2 text-success"></i>
            <div><strong>Success:</strong> <?= session()->getFlashdata('success') ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle fs-4 me-2 text-danger"></i>
            <div><strong>Error:</strong> <?= session()->getFlashdata('error') ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-5 gemba-filter-card">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">Gemba Audit Filters
                    <?php helper('gemba_acl'); if (!gemba_can_write()): ?>
                        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.55em; vertical-align: middle; padding: 6px 10px; border-radius: 6px;">Read Only Access</span>
                    <?php endif; ?>
                </span>
            </h3>
            <div class="card-toolbar">
                <?php if (gemba_can_write()): ?>
                <a href="<?= site_url('gemba-audit/import') ?>" class="btn btn-sm btn-info me-2">
                    <i class="fas fa-file-import me-2"></i>Import Excel/CSV
                </a>
                <a href="<?= site_url('gemba-audit/create') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-2"></i>Create New
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body pt-2">
            <form id="gemba_filter_form" class="row g-3">
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label">
                        <span>Region</span>
                    </label>
                    <select name="region[]" id="filter_region" class="form-select select2-filter" multiple="multiple" data-placeholder="All Regions">
                        <?php foreach ($regions as $r): ?>
                            <option value="<?= esc($r['region']) ?>"><?= esc($r['region']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label">
                        <span>Site Name</span>
                        <span class="badge badge-light-primary text-primary fw-normal fs-9 d-none" id="site_region_indicator"></span>
                    </label>
                    <select name="site_name[]" id="filter_site_name" class="form-select select2-filter" multiple="multiple" data-placeholder="All Sites">
                        <?php if (!empty($site_names)): ?>
                            <?php foreach ($site_names as $s): ?>
                                <option value="<?= esc($s) ?>"><?= esc($s) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label">
                        <span>Audit Category</span>
                    </label>
                    <select name="audit_category[]" id="filter_category" class="form-select select2-filter" multiple="multiple" data-placeholder="All Categories">
                        <?php
                        $cats = ['ISO Audit', 'HSE Audit', 'GIA Audit', 'Electric Audit', 'Fire Audit', 'Near Miss Scanner'];
                        foreach ($cats as $cat): ?>
                            <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label">
                        <span>NC Recommendation</span>
                    </label>
                    <select name="nc_recommendation[]" id="filter_nc_recommendation" class="form-select select2-filter" multiple="multiple" data-placeholder="All NC Recommendations">
                        <?php if (!empty($nc_recommendations)): ?>
                            <?php foreach ($nc_recommendations as $nc): ?>
                                <option value="<?= esc($nc) ?>"><?= esc($nc) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label">
                        <span>Point Status</span>
                    </label>
                    <select name="point_status[]" id="filter_status" class="form-select select2-filter" multiple="multiple" data-placeholder="All Statuses">
                        <option value="Open">Open</option>
                        <option value="WIP">WIP</option>
                        <option value="Closed">Closed</option>
                        <option value="Excluded">Excluded</option>
                        <option value="Hold-review with Client">Hold-review with Client</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label">
                        <span>Color Code</span>
                    </label>
                    <select name="color_code[]" id="filter_color" class="form-select select2-filter" multiple="multiple" data-placeholder="All Colors">
                        <option value="Red">Red</option>
                        <option value="Yellow">Yellow</option>
                        <option value="Black">Black</option>
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end align-items-center flex-wrap gap-2 gemba-filter-actions">
                    <button type="button" id="btn_apply_filter" class="btn btn-primary px-4">
                        <i class="fas fa-filter me-2"></i>Show
                    </button>
                    <button type="button" id="btn_reset_filter" class="btn btn-light px-4 border">
                        <i class="fas fa-sync-alt me-2"></i>Reset
                    </button>
                    <button type="button" id="btn_export_csv" class="btn btn-info px-4">
                        <i class="fas fa-file-csv me-2"></i>CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Container -->
    <div class="row">
        <div class="col-12">
            <?= $table ?>
        </div>
    </div>
</div>

<style>
    .gemba-filter-card {
        border-radius: 10px;
    }
    .gemba-filter-card .form-label {
        font-size: 0.84rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.35rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .gemba-filter-card .select2-container {
        width: 100% !important;
    }
    .gemba-filter-card .select2-container .select2-selection--multiple {
        min-height: 40px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }
    .gemba-filter-actions {
        border-top: 1px solid #f1f5f9;
        margin-top: 0.35rem;
        padding-top: 0.85rem;
    }
    @media (max-width: 576px) {
        .gemba-filter-actions button {
            flex: 1 1 auto;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('javascript_section') ?>
<script>
    $(document).ready(function () {
        // Site-to-region mapping data passed from backend
        var siteRegionMap = <?= json_encode($site_region_map ?? []) ?>;
        var allSites = <?= json_encode($site_names ?? []) ?>;

        // Initialize Select2 if available
        if ($.fn.select2) {
            $('.select2-filter').select2({
                placeholder: function () {
                    return $(this).data('placeholder') || "Select option";
                },
                allowClear: true,
                width: '100%'
            });
        }

        const tableId = '#gemba_audit_table';
        var isCascadingSites = false;

        // Dependent dropdown: update Site Name options when Region changes
        $('#filter_region').on('change', function () {
            if (isCascadingSites) return;
            isCascadingSites = true;

            var selectedRegions = ($('#filter_region').val() || []).filter(function (v) {
                var vt = (v || '').toLowerCase();
                return vt !== '' && vt !== 'all' && vt !== 'selectall';
            });

            var allowedSites = [];
            if (selectedRegions.length === 0) {
                allowedSites = allSites.slice();
                $('#site_region_indicator').addClass('d-none');
            } else {
                var siteSet = {};
                selectedRegions.forEach(function (r) {
                    if (siteRegionMap[r]) {
                        siteRegionMap[r].forEach(function (s) {
                            siteSet[s] = true;
                        });
                    }
                });
                allowedSites = Object.keys(siteSet).sort(function (a, b) {
                    return a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' });
                });
                $('#site_region_indicator').removeClass('d-none').text(allowedSites.length + ' in region');
            }

            // Fallback to AJAX if no sites matched locally and regions were selected
            if (selectedRegions.length > 0 && allowedSites.length === 0) {
                $.ajax({
                    url: '<?= site_url("gemba-audit/get_sites_by_region") ?>',
                    type: 'GET',
                    data: { regions: selectedRegions },
                    dataType: 'json',
                    success: function (res) {
                        applySitesToDropdown(res || []);
                    },
                    error: function () {
                        applySitesToDropdown(allowedSites);
                    }
                });
            } else {
                applySitesToDropdown(allowedSites);
            }

            function applySitesToDropdown(sitesList) {
                var currentSelectedSites = ($('#filter_site_name').val() || []).filter(function (v) {
                    return sitesList.indexOf(v) !== -1;
                });

                var $siteSelect = $('#filter_site_name');
                $siteSelect.empty();
                sitesList.forEach(function (site) {
                    var isSelected = currentSelectedSites.indexOf(site) !== -1 ? 'selected' : '';
                    $siteSelect.append('<option value="' + site.replace(/"/g, '&quot;') + '" ' + isSelected + '>' + site + '</option>');
                });

                // Re-initialize custom-multiselect for updated options
                $siteSelect.data('custom-multiselect-initialized', false);
                if (typeof window.reinitMultiSelect === 'function') {
                    window.reinitMultiSelect();
                }

                isCascadingSites = false;
                reloadDataTable();
            }
        });

        // Apply Filter
        $('#btn_apply_filter').on('click', function () {
            reloadDataTable();
        });

        // Reset Filter
        $('#btn_reset_filter').on('click', function () {
            $('#gemba_filter_form')[0].reset();

            // Restore all sites in site dropdown
            var $siteSelect = $('#filter_site_name');
            $siteSelect.empty();
            allSites.forEach(function (site) {
                $siteSelect.append('<option value="' + site.replace(/"/g, '&quot;') + '">' + site + '</option>');
            });
            $('#site_region_indicator').addClass('d-none');

            $('.select2-filter').val(null).trigger('change');
            $('.select2-filter').data('custom-multiselect-initialized', false);
            if (typeof window.reinitMultiSelect === 'function') {
                window.reinitMultiSelect();
            }
            reloadDataTable();
        });

        function reloadDataTable() {
            if ($.fn.DataTable.isDataTable(tableId)) {
                const params = $('#gemba_filter_form').serialize();
                const newUrl = '<?= site_url("gemba-audit/table_ajax") ?>?' + params;
                $(tableId).DataTable().ajax.url(newUrl).load();
            }
        }

        // Export CSV
        $('#btn_export_csv').on('click', function () {
            const params = $('#gemba_filter_form').serialize();
            window.location.href = '<?= site_url("gemba-audit/gemba_export") ?>?' + params;
        });

        // Dynamic auto-reload on filter change (for non-region filters)
        $('#filter_site_name, #filter_category, #filter_nc_recommendation, #filter_status, #filter_color').on('change', function () {
            reloadDataTable();
        });
    });
</script>
<?= $this->endSection() ?>