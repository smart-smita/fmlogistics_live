<?php 
$this->extend("Layout/base_admin");
?>

<?php 
					$this->section("breadcrumb_title_li");
?>
<style>
 
    
  table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px 12px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .average-row {
            font-weight: bold;
            text-align: right;
            background-color: #e0e0e0;
        }
          
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .card {
            max-height: 500px;
            overflow-y: auto;
        }
         .table-custom th, .table-custom td {
            padding: 10px;
            text-align: center;
           
        }
        .table-custom .header-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        
       
       
    </style>
    
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



    <div class="row">
        <div class="col-md-8 col-lg-8 col-xl-8 mb-5">
            <div class="card card-custom">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-label">OE Score</h3>
                    </div>
                    
                </div>
                <div class="card-body" style="height:500px">
              <table class="table table-bordered table-custom">
    <thead>
        <tr class="header-row">
            <th>Region</th> <!-- changes on 30/09/25 by darsh: rename header from Zone to Region -->
            <?php
            // Display unique months as column headers
            $months = array_unique(array_column($zone, 'audit_month'));
            foreach ($months as $month): ?>
                <th style="text-align: center;"><?= esc($month); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        // changes on 30/09/25 by darsh: fix undefined $region, use $zone data and build $zones map
        $zones = [];
        foreach ($zone as $row) {
            if ($row['audit_month']) { // Only add if month is not NULL
                $zones[$row['zone']][$row['audit_month']] = $row['yes'];
            }
        }

        // Define a list of all zones to show by default (regardless of available data)
        $all_zones = ['East', 'West', 'North', 'South'];

        // changes on 30/09/25 by darsh: zone_totals now provided by controller as normalized values
        foreach ($all_zones as $zone_name):
            $months_data = isset($zones[$zone_name]) ? $zones[$zone_name] : [];
        ?>
            <tr>
                <td ><?= esc($zone_name); ?></td>
                <?php foreach ($months as $month): 
                    // changes on 30/09/25 by darsh: always show numeric value (default 0%)
                    $cell = isset($months_data[$month]) ? (esc($months_data[$month]) . '%') : '0%'; ?>
                        <td style="text-align: center;"><?= $cell; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


                </div>
            </div>
        </div>
        
      <div class="col-md-4 col-lg-4 col-xl-4 mb-5">
    <div class="card card-custom">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-label">OE Score</h3>
            </div>
        </div>
        <div class="card-body" style="height:415px">
            <canvas id="oeScoreChart"></canvas>
        </div>
    </div>
</div>
</div>
        
                
                    
                    


<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Month Summary</h3>
                </div>
                <div class="card-body">
                  <table class="table table-bordered table-custom">
    <thead>
        <tr class="header-row">
            <th>Client Name</th>
            <?php
            // Define all 12 months explicitly
            $months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            foreach ($months as $month): ?>
                <th><?= esc($month); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        // Group data by client_name for row-wise display
        $clients = [];
        foreach ($audit as $row) {
            // Group the data by client_name and audit_month
            $clients[$row['client_name']][$row['audit_month']] = $row['yes'];
        }

        // Display each client with data organized by month
        foreach ($clients as $client_name => $months_data): ?>
            <tr>
                <td><?= esc($client_name); ?></td>
                <?php foreach ($months as $month):
                    // Check if the client has a value for the current month
                    $yes_value = isset($months_data[$month]) ? esc($months_data[$month]) . '%' : '0%';
                ?>
                    <td><?= $yes_value; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

                </div>
            </div>
        </div>
    </div>
</div>





<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">West Summary</h3>
                </div>
                <div class="card-body">
                    <!-- Table with proper borders -->
<table class="table table-bordered table-custom">
    <thead>
        <tr class="header-row">
            <th>Region</th> <!-- changes on 30/09/25 by darsh: rename header from Zone to Region -->
            <th>Client Name</th>
            <?php foreach ($unique_locations as $location): ?>
                <th><?= esc($location); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
         <tr class="header-row">
            <td colspan="2"></td>
            <?php foreach ($unique_locations as $location): ?>
                <td><?= isset($location_west[$location]) ? esc($location_west[$location]) . '%' : '0%' ?></td>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($west_score as $row): ?>
            <tr>
                <td><?= esc($row['zone']); ?></td>
                <td><?= esc($row['client_name']); ?></td>
                <?php foreach ($unique_locations as $location): 
                    $yes_value = ($row['location'] == $location) ? esc($row['yes']) . '%' : '0%';
                ?>
                    <td><?= $yes_value; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        <!-- Totals Row -->
       
    </tbody>
</table>




                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">North Summary</h3>
                </div>
                <div class="card-body">
                    <!-- Table with proper borders -->
                   <table class="table table-bordered table-custom">
    <thead>
        <tr class="header-row">
            <th>Region</th> <!-- changes on 30/09/25 by darsh: rename header from Zone to Region -->
            <th>Client Name</th>
            <?php foreach ($unique_locations as $location): ?>
                <th><?= esc($location); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
         <tr class="header-row">
            <td colspan="2"></td>
            <?php foreach ($unique_locations as $location): ?>
                <td><?= isset($location_north[$location]) ? esc($location_north[$location]) . '%' : '0%' ?></td>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($north_score as $row): ?>
            <tr>
                <td><?= esc($row['zone']); ?></td>
                <td><?= esc($row['client_name']); ?></td>
                <?php foreach ($unique_locations as $location): 
                    $yes_value = ($row['location'] == $location) ? esc($row['yes']) . '%' : '0%';
                ?>
                    <td><?= $yes_value; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        <!-- Totals Row -->
       
    </tbody>
</table>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">South Summary</h3>
                </div>
                <div class="card-body">
                    
                        <table class="table table-bordered table-custom">
    <thead>
        <tr class="header-row">
            <th>Region</th> <!-- changes on 30/09/25 by darsh: rename header from Zone to Region -->
            <th>Client Name</th>
            <?php foreach ($unique_locations as $location): ?>
                <th><?= esc($location); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
         <tr class="header-row">
            <td colspan="2"></td>
            <?php foreach ($unique_locations as $location): ?>
                <td><?= isset($location_score[$location]) ? esc($location_score[$location]) . '%' : '0%' ?></td>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($scores as $row): ?>
            <tr>
                <td><?= esc($row['zone']); ?></td>
                <td><?= esc($row['client_name']); ?></td>
                <?php foreach ($unique_locations as $location): 
                    $yes_value = ($row['location'] == $location) ? esc($row['yes']) . '%' : '0%';
                ?>
                    <td><?= $yes_value; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        <!-- Totals Row -->
       
    </tbody>
</table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">East Summary</h3>
                </div>
                <div class="card-body">
                
                    <table class="table table-bordered table-custom">
    <thead>
        <tr class="header-row">
            <th>Region</th> <!-- changes on 30/09/25 by darsh: rename header from Zone to Region -->
            <th>Client Name</th>
            <?php foreach ($unique_locations as $location): ?>
                <th><?= esc($location); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
         <tr class="header-row">
            <td colspan="2"></td>
            <?php foreach ($unique_locations as $location): ?>
                <td><?= isset($location_totals[$location]) ? esc($location_totals[$location]) . '%' : '0%' ?></td>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($east_score as $row): ?>
            <tr>
                <td><?= esc($row['zone']); ?></td>
                <td><?= esc($row['client_name']); ?></td>
                <?php foreach ($unique_locations as $location): 
                    $yes_value = ($row['location'] == $location) ? esc($row['yes']) . '%' : '0%';
                ?>
                    <td><?= $yes_value; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        <!-- Totals Row -->
       
    </tbody>
</table>
                    
                    
                    
                </div>
            </div>
        </div>
    </div>
</div>


	
	







<?php 
					$this->endSection();
?>
	<?php $this->section("javascript_section"); ?>


 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.52.0/apexcharts.min.css" integrity="sha512-w3pXofOHrtYzBYpJwC6TzPH6SxD6HLAbT/rffdkA759nCQvYi5AHy5trNWFboZnj4xtdyK0AFMBtck9eTmwybg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.52.0/apexcharts.min.js" integrity="sha512-piY4QAXPoG2xLdUZZbcc5klXzMxckrQKY9A2o6nKDRt9inolvvLbvGPC+z9IZ29b28UJlD05B7CjxxPaxh4bjQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

 // changes on 30/09/25 by darsh: use controller-provided normalized zone_totals
    const pieData = {
        labels: <?= json_encode(array_keys($zone_totals)) ?>, // Zone names
        datasets: [{
            data: <?= json_encode(array_values($zone_totals)) ?>, // Zone totals
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'], // Colors for zones
        }]
    };

    // Render pie chart
    const ctx = document.getElementById('oeScoreChart').getContext('2d');
    const pieChart = new Chart(ctx, {
        type: 'pie',
        data: pieData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw + '%';
                        }
                    }
                }
            }
        }
    });

</script>
<?php $this->endSection(); ?>
