<?php 
$this->extend("Layout/base_admin");
?>

<?php 
					$this->section("breadcrumb_title_li");
?>

<!--begin::Item-->
	<!--<li class="breadcrumb-item text-muted">-->
	<!--	<a href="<?= current_url() ?>" class="text-muted text-hover-primary">DASHBOARD</a>-->
	<!--</li>-->
<!--end::Item-->
<?php 
					$this->endSection();
?>										
<?php 
					$this->section("main_body");
?>

<style>
    span.select2-selection.select2-selection--single.form-select.js-example-basic-single
 {
    height: 38px !important;
}
.select2-container .select2-selection--single{
    height: 38px !important;
}
</style>

<div class="row">

<div class="col-md-2 col-lg-2 col-xl-2 mb-4">
<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Region</label>
  <!-- changes on 14/11/25 by darsh: Lock region for cluster managers -->
  <select class="form-select select2-filter js-example-basic-single" id="region" name="region[]" multiple="multiple" data-placeholder="Select Region" <?= isset($is_cluster_manager) && $is_cluster_manager ? 'disabled' : '' ?>>
		<?php if(isset($is_cluster_manager) && $is_cluster_manager): ?>
			<?php 
                $val = is_array($locked_regions) ? implode(',', $locked_regions) : $locked_regions;
                $lbl = is_array($locked_regions) ? implode(', ', $locked_regions) : $locked_regions;
            ?>
			<option value="<?= esc($val) ?>" selected><?= esc($lbl) ?></option>
		<?php else: ?>

			<?php foreach($region as $row){?>
			<option value="<?=$row['region_name']?>"><?=$row['region_name']?></option>
			<?php }?>
		<?php endif; ?>
		</select>
    </div>
    </div>
    

<div class="col-md-2 col-lg-2 col-xl-2 mb-4">
<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Cluster</label>
  <!-- changes on 14/11/25 by darsh: Lock cluster for cluster managers -->
  <select class="form-select select2-filter js-example-basic-single" id="cluster_name" name="cluster_name[]" multiple="multiple" data-placeholder="Select Cluster" <?= isset($is_cluster_manager) && $is_cluster_manager ? 'disabled' : '' ?>>
		<?php if(isset($is_cluster_manager) && $is_cluster_manager): ?>
			<?php 
                $valC = is_array($locked_clusters) ? implode(',', $locked_clusters) : $locked_clusters;
                $lblC = is_array($locked_clusters) ? implode(', ', $locked_clusters) : $locked_clusters;
            ?>
			<option value="<?= esc($valC) ?>" selected><?= esc($lblC) ?></option>
		<?php else: ?>

			<?php foreach($cluster as $row){?>
			<option value="<?=$row['cluster_name']?>"><?=$row['cluster_name']?></option>
			<?php }?>
		<?php endif; ?>
        </select>
    </div>
</div>

<div class="col-md-2 col-lg-2 col-xl-2 mb-4">
<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Location</label>
  <select class="form-select select2-filter js-example-basic-single" id="location_name" name="location_name[]" multiple="multiple" data-placeholder="Select Location" style="height:38px !important;">

        <?php foreach($location as $row){?>
        <option value="<?=$row['location_name']?>"><?=$row['location_name']?></option>
        <?php }?>
        </select>
    </div>
</div>

<div class="col-md-2 col-lg-2 col-xl-2 mb-4">
<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Audit Name</label>
  <select class="form-select select2-filter js-example-basic-single"  id="audit_name" name="audit_name[]" multiple="multiple" data-placeholder="Select Audit Name"> <!-- changes on 8/10/25 by Darsh - replace audit type with audit name -->

		<?php foreach($audit_template as $row){?>
		<option value="<?=$row['audit_name']?>"><?=$row['audit_name']?></option>
		<?php }?>
		</select>
    </div>
    </div>
    
<div class="col-md-2 col-lg-2 col-xl-2 mb-4">
<div class="col-md-12 fv-row fv-plugins-icon-container">
  <label class="required fs-5 fw-bold mb-2">Year</label>
  <select class="form-select select2-filter js-example-basic-single"  id="audit_year" name="audit_year[]" multiple="multiple" data-placeholder="Select Year"> <!-- NEW CHANGE: corrected id/name for Year select so JS can read it -->
		<option value="<?php echo $audit_year; ?>"><?php echo $audit_year; ?></option>

		</select>
    </div>
    </div>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="col-md-2 col-lg-2  col-xl-2 mb-5">
    <br>
 <div class="col-md-6 d-flex align-items-end">
    <button type="button" id="showBtn" class="btn btn-primary">Show</button>
    </div>
	</div>	
</div>
	
	<div class="row">
	<div class="col-md-8 col-lg-8 col-xl-8 mb-5">
    <div class="card card-custom">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-label">Audit Completion Rate</h3>
            </div>
            <div class="card-toolbar">
                <!-- <div class="dropdown dropdown-inline">-->
                <!--    <a href="#" class="btn btn-sm btn-light-primary font-weight-bolder dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">-->
                <!--        <i class="ki ki-calendar icon-sm"></i> Filter by Date-->
                <!--    </a>-->
                <!--    <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">-->
                <!--        <ul class="navi navi-hover py-3">-->
                <!--            <li class="navi-item">-->
                <!--                <a href="#" class="navi-link">-->
                <!--                    <span class="navi-text">This Year</span>-->
                <!--                </a>-->
                <!--            </li>-->
                <!--            <li class="navi-item">-->
                <!--                <a href="#" class="navi-link">-->
                <!--                    <span class="navi-text">This Quarter</span>-->
                <!--                </a>-->
                <!--            </li>-->
                <!--            <li class="navi-item">-->
                <!--                <a href="#" class="navi-link">-->
                <!--                    <span class="navi-text">This Month</span>-->
                <!--                </a>-->
                <!--            </li>-->
                <!--        </ul>-->
                <!--    </div>-->
                <!--</div> -->
            </div>
        </div>
        <div class="card-body">
           
            <canvas id="auditCompletionChart"></canvas>
        </div>
    </div>
</div>


<div class="col-md-4 col-lg-4 col-xl-4 mb-5">
    
    <div class="card card-custom">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-label">Audit Risk Distribution</h3>
            </div>
        </div>
        <div class="card-body" style="height:415px">
            
            <canvas id="auditRiskDistributionChart"></canvas>
        </div>
    </div>
</div>
</div>

<div class="row">
<div class="col-md-6 col-lg-6 col-xl-6 mb-5">
    <div class="card card-custom">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-label">Audit Findings (Yes/No)</h3>
            </div>
        </div>
        <div class="card-body">
           
            <canvas id="auditFindingsChart"></canvas>
        </div>
    </div>
</div>

<div class="col-md-6 col-lg-6 col-xl-6 mb-5">
        <div class="card card-custom">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-label">Audit Score Trends Over Time</h3>
                </div>
            </div>
            <div class="card-body">
                <canvas id="auditScoreTrends"></canvas>
            </div>
        </div>
    </div>

</div>

<div class="row">
 <div class="col-md-6 col-lg-6 col-xl-6 mb-5">
        <div class="card card-custom">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-label">Top Locations by Risk Level</h3>
                </div>
            </div>
            <div class="card-body">
                <canvas id="topLocationsRisk"></canvas>
            </div>
        </div>
    </div>
  
    <div class="col-md-6 col-lg-6 col-xl-6 mb-5">
        <div class="card card-custom">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-label">Audit Frequency by Location</h3>
                </div>
            </div>
            <div class="card-body">
                <canvas id="auditFrequencyHeatmap"></canvas>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-6 col-lg-6 col-xl-6 mb-5">
						    <div class="grid">
						            <?=$user_table?>
						            
                            </div>
                            </div>
    <!--<div class="col-md-6 col-lg-6 col-xl-6 mb-5">-->
    <!--    <div class="card card-custom">-->
    <!--        <div class="card-header">-->
    <!--            <div class="card-title">-->
    <!--                <h3 class="card-label">Upcoming Audits</h3>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--        <div class="card-body" style="height:320px">-->
    <!--             <table class="table">-->
    <!--        <thead>-->
    <!--            <tr>-->
    <!--                <th>Audit Name</th>-->
    <!--                <th>Next Audit Date</th>-->
    <!--            </tr>-->
    <!--        </thead>-->
    <!--        <tbody>-->
    <!--            <tr>-->
    <!--                <td>Financial external audit</td>-->
    <!--                <td>2024-10-15</td>-->
    <!--            </tr>-->
    <!--            <tr>-->
    <!--                <td>System internal audit</td>-->
    <!--                <td>2024-11-01</td>-->
    <!--            </tr>-->
    <!--            <tr>-->
    <!--                <td>Compliance internal audit</td>-->
    <!--                <td>2024-12-05</td>-->
    <!--            </tr>-->
    <!--            <tr>-->
    <!--                <td>Performance internal audit</td>-->
    <!--                <td>2024-9-09</td>-->
    <!--            </tr>-->
    <!--        </tbody>-->
    <!--    </table>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</div>-->
    
 
<div class="col-md-6 col-lg-6 col-xl-6 mb-5">
        <div class="card card-custom">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-label">Audit Frequency by Audit</h3>
                </div>
            </div>
            <div class="card-body">
                <canvas id="calculatedScoreChart"></canvas>
            </div>
        </div>
    </div
    </div>
</div>




<?php 
					$this->endSection();
?>
	<?php $this->section("javascript_section"); ?>


 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.52.0/apexcharts.min.css" integrity="sha512-w3pXofOHrtYzBYpJwC6TzPH6SxD6HLAbT/rffdkA759nCQvYi5AHy5trNWFboZnj4xtdyK0AFMBtck9eTmwybg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.52.0/apexcharts.min.js" integrity="sha512-piY4QAXPoG2xLdUZZbcc5klXzMxckrQKY9A2o6nKDRt9inolvvLbvGPC+z9IZ29b28UJlD05B7CjxxPaxh4bjQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


<script>
$('#showBtn').click(function () {
    
    // Collect selected values
    const cluster   = $('#cluster_name').val();
    const location  = $('#location_name').val();
    const year      = $('#audit_year').val(); // NEW CHANGE: use corrected #audit_year
    const audit_name      = $('#audit_name').val(); // changes on 8/10/25 by Darsh - audit name value
    // Debugging: check values in console
    console.log("Selected:", cluster, location, year);

    // ðŸ”¹ Make an AJAX call to backend (you must create this route in CI controller)
    $.ajax({
        url: "<?= base_url('Structure_audit_dashboard/filter') ?>",  // NEW CHANGE: fixed route casing to match controller
        type: "POST",
        data: { cluster_name: cluster, location_name: location, year: year, audit_name: audit_name }, // changes on 8/10/25 by Darsh - send audit_name
        dataType: "json",
        success: function (response) {
            // Update charts with new data
            console.log(response);

            // NEW CHANGE: update all charts from response
            if (window.auditCompletionChart) {
                window.auditCompletionChart.data.labels = response.auditNames || [];
                window.auditCompletionChart.data.datasets[0].data = response.auditCounts || [];
                window.auditCompletionChart.update();
            }

            if (window.auditRiskDistributionChart) {
                const high = Number(response.highCount || 0);
                const med  = Number(response.mediumCount || 0);
                const low  = Number(response.lowCount || 0);
                window.auditRiskDistributionChart.data.datasets[0].data = [high, med, low];
                window.auditRiskDistributionChart.update();
            }

            if (window.auditFindingsChart) {
                window.auditFindingsChart.data.labels = response.auditFindingNames || [];
                window.auditFindingsChart.data.datasets[0].data = response.countsYes || [];
                window.auditFindingsChart.data.datasets[1].data = response.countsNo || [];
                window.auditFindingsChart.update();
            }

            if (window.auditScoreTrendsChart) {
                window.auditScoreTrendsChart.data.labels = response.audit_date || [];
                window.auditScoreTrendsChart.data.datasets[0].data = response.audit_scores || [];
                window.auditScoreTrendsChart.update();
            }

            if (window.topLocationsRiskChart) {
                window.topLocationsRiskChart.data.labels = response.location_risk || [];
                window.topLocationsRiskChart.data.datasets[0].data = response.risk_priority || [];
                window.topLocationsRiskChart.update();
            }

            if (window.auditFrequencyChart) {
                const rebuilt = buildFrequencyData(response.location_audit || [], response.audit_name || [], response.frequency_count || []);
                window.auditFrequencyChart.data.labels = rebuilt.labels;
                window.auditFrequencyChart.data.datasets = rebuilt.datasets;
                window.auditFrequencyChart.update();
            }

            if (window.calculatedScoreChart) {
                window.calculatedScoreChart.data.labels = response.audit_score_name || [];
                window.calculatedScoreChart.data.datasets[0].data = response.frequency_score || [];
                window.calculatedScoreChart.update();
            }

            // NEW CHANGE: finished updates for all charts
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
        }
    });
});

        // changes on 8/10/25 by Darsh - removed dependent chaining of Region→Cluster and Cluster→Location; dropdowns are independent now

    // changes on 30/09/25 by darsh: audit template independent; fetch full list on page load and when filters change
    function loadAllAuditTemplates(){
        $.ajax({
            url:"<?php echo base_url('Structure_audit_dashboard/GetAuditName'); ?>",
            method:"POST",
            dataType : 'json',
            success:function(response){
                var html = '<option value="0" selected disabled>Select Audit Template</option>';
                for(var i = 0; i < response.length; i++){
                    if(response[i].audit_name){ html += '<option value="'+response[i].audit_name+'">'+response[i].audit_name+'</option>'; }
                }
                $('#audit_name').html(html);
            }
        });
    }
    loadAllAuditTemplates();
    $('#region, #cluster_name, #location_name').on('change', function(){
        loadAllAuditTemplates();
    });

    const auditLabels = <?= $auditNames ?>; 
    const auditCounts = <?= $auditCounts ?>; 
   const auditData = {
        labels: auditLabels,
        datasets: [{
            label: 'Completed Audits',
            data: auditCounts,  
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    };

    $('.js-example-basic-single').select2();

   
    const config = {
        type: 'bar',
        data: auditData,
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return Math.round(value);
                        }
                    }
                }
            },
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                },
                title: {
                    display: true,
                    text: 'Audit Completion Rate'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toFixed(0);
                        }
                    }
                }
            }
        }
    };

   
    window.auditCompletionChart = new Chart( // NEW CHANGE: store chart globally for later updates
        document.getElementById('auditCompletionChart'),
        config
    );
     
     const auditHigh = <?=$highCount?>; 
     const auditMedium = <?=$mediumCount?>;
     const audiLow = <?=$lowCount?>; 

    const riskData = {
        labels: ["High", "Medium", "Low"],
        datasets: [{
            label: 'Audit Risk Distribution',
            data: [auditHigh,auditMedium,audiLow],
            backgroundColor: [
                'rgba(255, 99, 132, 0.6)',  
                'rgba(255, 206, 86, 0.6)',  
                'rgba(75, 192, 192, 0.6)'   
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    };

   
    const auditRiskDistributionConfig = {
        type: 'doughnut',
        data: riskData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Audit Risk Distribution'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.raw;
                            const percentage = Math.round((value / total) * 100);
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    };

    
    window.auditRiskDistributionChart = new Chart( // NEW CHANGE: store chart globally
        document.getElementById('auditRiskDistributionChart'),
        auditRiskDistributionConfig
    );
    
     const auditName = <?= $auditFindingNames ?>; 
     const auditYes = <?= $countsYes ?>; 
      const auditNo = <?= $countsNo ?>; 

     const auditFindingsData = {
        labels: auditName,  
        datasets: [{
            label:'Yes Findings',  
            data: auditYes,  
            backgroundColor: 'rgba(75, 192, 192, 0.6)',  
            borderColor: 'rgba(75, 192, 192, 1)',        
            borderWidth: 1
        },
        {
            label: 'No Findings', 
            data: auditNo,  
            backgroundColor: 'rgba(255, 99, 132, 0.6) ',  
            borderColor: 'rgba(255, 99, 132, 1)',        
            borderWidth: 1
        }]
    };

    
    const auditFindingsConfig = {
        type: 'bar',
        data: auditFindingsData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                },
                title: {
                    display: true,
                    text: 'Audit Findings (Yes/No)'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${Math.round(context.parsed.y)}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true  
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return Math.round(value);
                        }
                    }
                }
            }
        }
    };

    
    window.auditFindingsChart = new Chart( // NEW CHANGE: store chart globally
        document.getElementById('auditFindingsChart'),
        auditFindingsConfig
    );
    
    
    const auditDate = <?= $audit_date?>;
    const auditScores = <?= $audit_scores?>;
     const auditScoreData = {
        labels: auditDate,
        // [ "2024-01", "2024-02", "2024-03", "2024-04", "2024-05"], 
        datasets: [{
            label: 'Overall Score',
            data:auditScores,
            // [75, 80, 85, 90, 88],  
            borderColor: 'rgba(75, 192, 192, 1)',
            fill: false,
        }]
    };

    const auditScoreConfig = {
        type: 'line',
        data: auditScoreData,
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Audit Score Trends Over Time'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Score: ${Math.round(context.parsed.y)}`;
                        }
                    }
                }
            },
            scales: {
                x: { 
                    title: { display: true, text: 'Audit Date' }
                },
                y: { 
                    title: { display: true, text: 'Overall Score' }, 
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return Math.round(value);
                        }
                    }
                }
            }
        }
    };

    window.auditScoreTrendsChart = new Chart(document.getElementById('auditScoreTrends'), auditScoreConfig); // NEW CHANGE: store chart globally
    
    const auditLocation = <?= $location_risk ?>; 
    const auditRisk = <?= $risk_priority ?>; 
    const locationRiskData = {
        labels: auditLocation,  
        datasets: [{
            label: 'Risk Level',
            data: auditRisk,  
            backgroundColor: 'rgba(255, 99, 132, 0.6)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    };

    const locationRiskConfig = {
        type: 'bar',
        data: locationRiskData,
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Top Locations by Risk Level'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Count: ${Math.round(context.parsed.x)}`;
                        }
                    }
                }
            },
            scales: {
                x: { title: { display: true, text: 'Risk/Priority' }},
                y: { title: { display: true, text: 'Location' }}
            }
        }
    };

    window.topLocationsRiskChart = new Chart(document.getElementById('topLocationsRisk'), locationRiskConfig); // NEW CHANGE: store chart globally
    
const locationAudit = <?= $location_audit?>;  
const auditNames = <?= $audit_name ?>;  
const frequencyCounts = <?= $frequency_count ?>;  

const uniqueLocations = [...new Set(locationAudit)]; // NEW CHANGE: keep unique locations for stacked chart

// NEW CHANGE: rebuild frequency datasets from flat arrays
function buildFrequencyData(locationsArr, auditsArr, countsArr){
    const uniqueLocs = [...new Set(locationsArr)];
    const uniqueAudits = [...new Set(auditsArr)];
    const matrix = {};
    uniqueAudits.forEach(a=>{ matrix[a] = {}; uniqueLocs.forEach(l=>{ matrix[a][l] = 0; }); });
    for (let i = 0; i < locationsArr.length; i++) {
        const l = locationsArr[i];
        const a = auditsArr[i];
        const c = Number(countsArr[i] || 0);
        if (matrix[a] && l in matrix[a]) { matrix[a][l] = c; }
    }
    const datasetsLocal = uniqueAudits.map(audit => ({
        label: audit,  
        data: uniqueLocs.map(l => matrix[audit][l]),
        backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.6)`
    }));
    return { labels: uniqueLocs, datasets: datasetsLocal };
}

const rebuiltInitial = buildFrequencyData(locationAudit, auditNames, frequencyCounts); // NEW CHANGE: use helper to build datasets

const auditFrequencyData = {
    labels: rebuiltInitial.labels,   // NEW CHANGE
    datasets: rebuiltInitial.datasets  // NEW CHANGE
};

const auditFrequencyConfig = {
    type: 'bar',
    data: auditFrequencyData,
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Audit Frequency by Location'
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        scales: { // NEW CHANGE: place scales correctly
            x: { stacked: true, title: { display: true, text: 'Location' } },
            y: { stacked: true, title: { display: true, text: 'Audit Frequency' }, beginAtZero: true }
        }
    }
};

window.auditFrequencyChart = new Chart(document.getElementById('auditFrequencyHeatmap'), auditFrequencyConfig); // NEW CHANGE: store chart globally

   const frequencyScore = <?= $frequency_score ?>; 
   const auditScoreName = <?= $audit_score_name ?>; 

const scoreData = {
    labels: auditScoreName,  
    datasets: [{
        label: 'Calculated Score',
        data: frequencyScore,  
        backgroundColor: 'rgba(153, 102, 255, 0.6)',
        borderColor: 'rgba(153, 102, 255, 1)',
        borderWidth: 1
    }]
};

const scoreConfig = {
    type: 'scatter',  
    data: scoreData,
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Calculated Score by Audits'
            }
        },
        scales: {
            x: { title: { display: true, text: 'Audits' }},
            y: { title: { display: true, text: 'Calculated Score' }, beginAtZero: true }
        }
    }
};

window.calculatedScoreChart = new Chart(document.getElementById('calculatedScoreChart'), scoreConfig); // NEW CHANGE: store chart globally

    
    
    
</script>
<?php $this->endSection(); ?>
