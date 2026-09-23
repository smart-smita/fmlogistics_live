<?php
// Normal NC Tracker Monthly Report PDF View
?>
<style>
    table, th, td {
        border: 1px solid black;
    }
    th {
        height: 50px;
        background-color: #1e1c77;
        color: white;
        border: 1px solid yellow !important;
        text-align: center;
        font-weight: bold;
        padding: 8px;
    }
    td {
        height: auto;
        min-height: 40px;
        padding: 8px;
        vertical-align: middle;
        text-align: center;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        page-break-inside: auto;
    }
    
    thead {
        display: table-header-group;
    }
    
    body {
        border-style: groove;
        padding: 10px;
        font-family: sans-serif;
    }
    
    html {
        margin-left: 5%;
    }
    
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    
    .page-break {
        page-break-before: always;
    }
    
    .badge {
        display: inline-block;
        min-width: 10px;
        padding: 5px 10px;
        font-size: 14px;
        font-weight: 700;
        color: #fff;
        line-height: 1;
        vertical-align: baseline;
        white-space: nowrap;
        text-align: center;
        background-color: #999;
        border-radius: 5px;
    }
    
    .badge-success {
        background-color: #468847;
    }
    
    .badge-danger {
        background-color: #b94a48;
    }
    
    .badge-warning {
        background-color: #ffc107;
        color: #000;
    }
    
    .badge-info {
        background-color: #5bc0de;
    }
    
    .badge-dark {
        background-color: #343a40;
    }
    
    .summary-box {
        background-color: #f8f9fa;
        border: 2px solid #1e1c77;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
    
    .summary-item {
        display: inline-block;
        margin-right: 20px;
        padding: 10px;
    }
    
    .text-center {
        text-align: center;
    }
    
    .text-right {
        text-align: right;
    }
    
    .text-left {
        text-align: left;
    }
</style>

<div>
    <div align="center">
        <h2 style="display: inline-block; border-bottom: 3px double black;"><u>Normal NC Tracker - Monthly Report</u></h2>
    </div>
    
    <!-- Report Summary -->
    <div class="summary-box">
        <h3 style="margin-top: 0;"><u>Report Summary</u></h3>
        <div class="summary-item">
            <strong>Report Period:</strong> <?= isset($month) && $month ? date('F Y', strtotime($month . '-01')) : 'All Time' ?>
        </div>
        <div class="summary-item">
            <strong>Generated On:</strong> <?= date('d-M-Y H:i:s') ?>
        </div>
        <?php if (isset($status_filter) && $status_filter): ?>
        <div class="summary-item">
            <strong>Status Filter:</strong> <?= ucwords(htmlspecialchars($status_filter)) ?>
        </div>
        <?php endif; ?>
        <?php if (isset($region) && $region): ?>
        <div class="summary-item">
            <strong>Region:</strong> <?= htmlspecialchars($region) ?>
        </div>
        <?php endif; ?>
        <?php if (isset($cluster) && $cluster): ?>
        <div class="summary-item">
            <strong>Cluster:</strong> <?= htmlspecialchars($cluster) ?>
        </div>
        <?php endif; ?>
        <?php if (isset($location) && $location): ?>
        <div class="summary-item">
            <strong>Location:</strong> <?= htmlspecialchars($location) ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Overall Statistics -->
    <div style="margin-bottom: 20px;">
        <h3><u>Overall Statistics</u></h3>
        <table style="width: 100%; margin-bottom: 20px;">
            <thead>
                <tr>
                    <th>Total Clients</th>
                    <th>Total NCs</th>
                    <th>Open NCs</th>
                    <th>Working NCs</th>
                    <th>Under Review</th>
                    <th>Closed NCs</th>
                    <th>Force Closed</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong><?= isset($total_clients) ? $total_clients : 0 ?></strong></td>
                    <td><strong><?= isset($total_ncs) ? $total_ncs : 0 ?></strong></td>
                    <td><span class="badge badge-danger"><?= isset($open_count) ? $open_count : 0 ?></span></td>
                    <td><span class="badge badge-warning"><?= isset($working_count) ? $working_count : 0 ?></span></td>
                    <td><span class="badge badge-info"><?= isset($review_count) ? $review_count : 0 ?></span></td>
                    <td><span class="badge badge-success"><?= isset($closed_count) ? $closed_count : 0 ?></span></td>
                    <td><span class="badge badge-danger"><?= isset($forced_count) ? $forced_count : 0 ?></span></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Client-wise NC Count -->
    <div style="page-break-before: always;">
        <h3><u>Client-wise NC Count</u></h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">Sr No.</th>
                    <th style="width: 25%;">Client Name</th>
                    <th style="width: 15%;">Region</th>
                    <th style="width: 10%;">Total NCs</th>
                    <th style="width: 9%;">Open</th>
                    <th style="width: 9%;">Working</th>
                    <th style="width: 9%;">Review</th>
                    <th style="width: 9%;">Closed</th>
                    <th style="width: 9%;">Forced</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (isset($client_data) && is_array($client_data) && count($client_data) > 0):
                    $sr = 1;
                    foreach ($client_data as $client): 
                ?>
                <tr>
                    <td><?= $sr++ ?></td>
                    <td class="text-left"><?= htmlspecialchars($client['client_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($client['region'] ?? 'N/A') ?></td>
                    <td><strong><?= $client['total_ncs'] ?? 0 ?></strong></td>
                    <td><?= $client['open'] ?? 0 ?></td>
                    <td><?= $client['working'] ?? 0 ?></td>
                    <td><?= $client['review'] ?? 0 ?></td>
                    <td><?= $client['closed'] ?? 0 ?></td>
                    <td><?= $client['forced'] ?? 0 ?></td>
                </tr>
                <?php 
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                        No NC data found for the selected period.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Month-wise Breakdown (if monthly data available) -->
    <?php if (isset($monthly_data) && is_array($monthly_data) && count($monthly_data) > 0): ?>
    <div style="page-break-before: always;">
        <h3><u>Month-wise NC Trend</u></h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">Sr No.</th>
                    <th style="width: 20%;">Month</th>
                    <th style="width: 15%;">Total NCs</th>
                    <th style="width: 12%;">Open</th>
                    <th style="width: 12%;">Working</th>
                    <th style="width: 12%;">Review</th>
                    <th style="width: 12%;">Closed</th>
                    <th style="width: 12%;">Forced</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sr = 1;
                foreach ($monthly_data as $month_key => $data): 
                ?>
                <tr>
                    <td><?= $sr++ ?></td>
                    <td class="text-left"><?= date('F Y', strtotime($month_key . '-01')) ?></td>
                    <td><strong><?= $data['total'] ?? 0 ?></strong></td>
                    <td><?= $data['open'] ?? 0 ?></td>
                    <td><?= $data['working'] ?? 0 ?></td>
                    <td><?= $data['review'] ?? 0 ?></td>
                    <td><?= $data['closed'] ?? 0 ?></td>
                    <td><?= $data['forced'] ?? 0 ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <!-- Footer -->
    <div style="margin-top: 30px; padding-top: 10px; border-top: 2px solid #1e1c77;">
        <p style="text-align: center; font-size: 12px; color: #666;">
            <strong>Note:</strong> This report shows NC (Non-Conformance) counts based on the latest audit data. 
            Status definitions: Open (0), Working (1,4), Under Review (2), Closed (3), Force Closed (5).
        </p>
    </div>
</div>
