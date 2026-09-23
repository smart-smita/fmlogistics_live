

<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/> -->

<style>
.select2-container--default .select2-selection--multiple {
    min-height: 42px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 4px;
}

.select2-selection__choice {
    background-color: #0d6efd !important;
    color: #fff !important;
    border-radius: 5px !important;
    padding: 2px 6px !important;
}



.select2-dropdown {
    border-radius: 8px;
}

.select2-results__option--highlighted {
    background-color: #0d6efd !important;
    color: #fff !important;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #fff;
    margin-right: 5px;
}

.dropdown-menu {
    border-radius: 10px;
    padding: 8px;
}

.dropdown-item {
    border-radius: 6px;
    padding: 10px;
    transition: 0.2s;
}

.dropdown-item:hover {
    background: #f1f5f9;
}
</style>

<form method="get" class="card p-3 mb-4">
<div class="row g-3">

<!-- REGION -->
<div class="col-md-2">
    <label class="form-label fw-bold">Region</label>
    <select id="region" name="region[]" class="form-select select2" multiple>
        <option value="ALL">Select All</option>
        <?php foreach($regions as $r): ?>
            <option value="<?= $r['region_name'] ?>"
                <?= in_array($r['region_name'], (array)$req->getGet('region')) ? 'selected' : '' ?>>
                <?= $r['region_name'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- CLUSTER -->
<div class="col-md-2">
    <label class="form-label fw-bold">Cluster</label>
    <select id="cluster" name="cluster[]" class="form-select select2" multiple></select>
</div>

<!-- LOCATION -->
<div class="col-md-2">
    <label class="form-label fw-bold">Location</label>
    <select id="location" name="location[]" class="form-select select2" multiple></select>
</div>

<!-- SITE CATEGORY -->
<div class="col-md-2">
    <label class="form-label fw-bold">Site Category</label>
    <select id="site_category" name="site_category[]" class="form-select select2" multiple>
        <option value="ALL">Select All</option>
        <?php foreach($siteCategories as $s): ?>
            <option value="<?= $s['site_category'] ?>"
                <?= in_array($s['site_category'], (array)$req->getGet('site_category')) ? 'selected' : '' ?>>
                <?= $s['site_category'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- SUB CATEGORY -->
<!-- <div class="col-md-3">
    <label class="form-label fw-bold">Sub Category</label>
    <select id="sub_category" name="sub_category[]" class="form-select select2" multiple></select>
</div> -->



<!-- NC TYPE -->
<div class="col-md-2">
    <label class="form-label fw-bold">NC Type</label>
    <select id="nc_type" name="nc_type" class="form-select">
        <option value="">All</option>
        <option value="NC" <?= $req->getGet('nc_type') === 'NC' ? 'selected' : '' ?>>NC</option>
        <option value="RD" <?= $req->getGet('nc_type') === 'RD' ? 'selected' : '' ?>>RD</option>
    </select>
</div>

<div class="col-md-2 mt-10">
    <button class="btn btn-primary w-100">Show</button>
</div>
<div class="col-md-2 mt-10">
    <div class="dropdown w-100">
        <button class="btn btn-sm btn-success dropdown-toggle "
                type="button"
                data-bs-toggle="dropdown">

            <i class="fa fa-download me-2"></i>Export
        </button>

        <ul class="dropdown-menu w-100 shadow-lg border-0">
            
            <li>
                <a class="dropdown-item d-flex align-items-center"
                   href="javascript:void(0)"
                   onclick="downloadHseReport('csv')">

                    <i class="fa fa-file-csv text-success me-2"></i>
                    Export CSV
                </a>
            </li>

            <li>
                <a class="dropdown-item d-flex align-items-center"
                   href="javascript:void(0)"
                   onclick="downloadHseReport('pdf')">

                    <i class="fa fa-file-pdf text-danger me-2"></i>
                    Export PDF
                </a>
            </li>

        </ul>
    </div>
</div>
</div>
</form>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function(){

    // CSRF Token
    let csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    // Update CSRF hash on each AJAX request
    $(document).ajaxComplete(function(event, xhr, settings) {
        let newCsrfHash = xhr.getResponseHeader('X-CSRF-TOKEN');
        if (newCsrfHash) {
            csrfHash = newCsrfHash;
        }
    });

    // Initialization handled by custom-multiselect.js globally

    let selectedRegion   = <?= json_encode($req->getGet('region') ?? ($assigned_regions ?? [])) ?>;
    let selectedCluster  = <?= json_encode($req->getGet('cluster') ?? ($assigned_clusters ?? [])) ?>;
    let selectedLocation = <?= json_encode($req->getGet('location') ?? ($assigned_locations ?? [])) ?>;
    let selectedSite     = <?= json_encode($req->getGet('site_category') ?? []) ?>;
    let selectedSub      = <?= json_encode($req->getGet('sub_category') ?? []) ?>;

    function setDropdown(selector, data, selected = [], valueKey = null, textKey = null)
    {
        let html = '<option value="selectAll">Select All</option>';

        if (!Array.isArray(data)) {
            data = [];
        }

        if (!Array.isArray(selected)) {
            selected = [selected];
        }

        data.forEach(item => {
            let value = valueKey ? item[valueKey] : Object.values(item)[0];
            let text  = textKey ? item[textKey] : Object.values(item)[0];

            let isSelected = selected.includes(String(value)) || selected.includes(value) ? 'selected' : '';
            html += `<option value="${value}" ${isSelected}>${text}</option>`;
        });

        if (selected.includes('selectAll') || selected.includes('ALL')) {
            html = html.replace('<option value="selectAll">', '<option value="selectAll" selected>');
        }

        $(selector).html(html);
        if ($(selector).data('select2')) {
            $(selector).trigger('change.select2');
        }
        if (typeof updateCustomBadges === 'function') {
            updateCustomBadges($(selector));
        }
    }

    function loadClusters(region = [], selected = [], callback = null)
    {
        let data = { region: region };
        data[csrfName] = csrfHash;

        $.ajax({
            url: "<?= base_url('Masters/Hse_nc_tracker/get_cluster') ?>",
            type: "POST",
            dataType: "json",
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: data,
            success: function(res){
                setDropdown('#cluster', res, selected, 'cluster_name', 'cluster_name');
                if(callback) callback();
            },
            error: function(xhr){
                console.error("Cluster AJAX Error:", xhr.responseText);
            }
        });
    }

    function loadLocations(cluster = [], selected = [], callback = null)
    {
        let data = { cluster: cluster };
        data[csrfName] = csrfHash;

        $.ajax({
            url: "<?= base_url('Masters/Hse_nc_tracker/get_location') ?>",
            type: "POST",
            dataType: "json",
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: data,
            success: function(res){
                setDropdown('#location', res, selected, 'location_name', 'location_name');
                if(callback) callback();
            },
            error: function(xhr){
                console.error("Location AJAX Error:", xhr.responseText);
            }
        });
    }

    loadClusters(selectedRegion, selectedCluster, function(){
        loadLocations(selectedCluster, selectedLocation);
    });

    $('#region').on('change', function(){
        let region = $(this).val() || [];
        loadClusters(region, [], function(){
            $('#location').html('').trigger('change.select2');
        });
    });

    $('#cluster').on('change', function(){
        let cluster = $(this).val() || [];
        loadLocations(cluster, []);
    });

});
</script>
<script>
function downloadHseReport(type)
{
    let url = "";

    if(type === 'csv'){
        url = "<?= base_url('Masters/Hse_nc_tracker/export_report_excel') ?>";
    } else if(type === 'pdf'){
        url = "<?= base_url('Masters/Hse_nc_tracker/export_report_pdf') ?>";
    }

    let params = new URLSearchParams();

    // Region
    ($('#region').val() || []).forEach(val => {
        if(val !== 'ALL') params.append('region[]', val);
    });

    // Cluster
    ($('#cluster').val() || []).forEach(val => {
        if(val !== 'ALL') params.append('cluster[]', val);
    });

    // Location
    ($('#location').val() || []).forEach(val => {
        if(val !== 'ALL') params.append('location[]', val);
    });

    // Site Category
    ($('#site_category').val() || []).forEach(val => {
        if(val !== 'ALL') params.append('site_category[]', val);
    });

    // Sub Category
    ($('#sub_category').val() || []).forEach(val => {
        if(val !== 'ALL') params.append('sub_category[]', val);
    });

    // NC TYPE
    let ncType = $('#nc_type').val();
    if(ncType){
        params.append('nc_type', ncType);
    }

    // If page is Open / Working / Closed etc.
    let pathParts = window.location.pathname.split('/');
    let ncStatusIndex = pathParts.indexOf('template_type_filter');

    if (ncStatusIndex !== -1 && pathParts[ncStatusIndex + 1]) {
        params.append('nc_status', pathParts[ncStatusIndex + 1]);
    }

    // Final open
    window.open(url + '?' + params.toString(), '_blank');
}
</script>
