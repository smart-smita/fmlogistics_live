<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HSE Audit Submitted</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
            color: #333333;
        }

        .email-wrapper {
            max-width: 800px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #003399;
            background-image: linear-gradient(135deg, #003399 0%, #0052cc 100%);
            color: #ffffff;
            padding: 30px;
            display: table;
            width: 100%;
            box-sizing: border-box;
        }

        .header-content {
            display: table-cell;
            vertical-align: middle;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #d1e0ff;
        }

        .body-content {
            padding: 30px;
        }

        .greeting {
            font-size: 15px;
            margin-bottom: 20px;
        }

        .section-title {
            background-color: #f4f7fc;
            color: #003399;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 6px 6px 0 0;
            border: 1px solid #e1e8f5;
            border-bottom: none;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .details-box {
            border: 1px solid #e1e8f5;
            border-radius: 0 0 6px 6px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table td {
            padding: 10px;
            vertical-align: top;
            width: 33.33%;
        }

        .label {
            font-size: 12px;
            color: #0052cc;
            font-weight: 600;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .value {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        .cards-container {
            display: table;
            width: 100%;
            border-spacing: 10px;
            margin-bottom: 25px;
        }

        .card {
            display: table-cell;
            width: 25%;
            border-radius: 8px;
            padding: 20px 10px;
            text-align: center;
            border: 1px solid #e1e8f5;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        }

        .card.blue {
            border-top: 4px solid #0052cc;
            background-color: #f5f9ff;
        }

        .card.red {
            border-top: 4px solid #e74c3c;
            background-color: #fff5f5;
        }

        .card.orange {
            border-top: 4px solid #e67e22;
            background-color: #fff9f4;
        }

        .card.green {
            border-top: 4px solid #2ecc71;
            background-color: #f5fcf5;
        }

        .card-label {
            font-size: 11px;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .split-section {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }

        .split-left,
        .split-right {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        .split-spacer {
            display: table-cell;
            width: 4%;
        }

        .summary-box {
            border: 1px solid #e1e8f5;
            border-radius: 6px;
            overflow: hidden;
        }

        .summary-title {
            background-color: #f4f7fc;
            padding: 12px 15px;
            font-size: 13px;
            font-weight: bold;
            color: #003399;
            border-bottom: 1px solid #e1e8f5;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e1e8f5;
            font-size: 13px;
        }

        .data-table th {
            color: #0052cc;
            font-weight: 600;
            background: #fafbfc;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table .total-row {
            background-color: #f4f7fc;
            font-weight: bold;
            color: #0052cc;
        }

        .risk-cards {
            display: table;
            width: 100%;
            border-spacing: 10px;
            padding: 10px;
        }

        .risk-card {
            display: table-cell;
            text-align: center;
            padding: 15px 5px;
            border-radius: 6px;
            border: 1px solid #eee;
            width: 33.33%;
        }

        .risk-card.black {
            border-color: #999999;
            background-color: #f5f5f5;
        }

        .risk-card.red {
            border-color: #ffcccc;
            background-color: #fff5f5;
        }

        .risk-card.yellow {
            border-color: #fff59d;
            background-color: #fffff5;
        }

        .risk-label {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .risk-card.black .risk-label {
            color: #333333;
        }

        .risk-card.red .risk-label {
            color: #e74c3c;
        }

        .risk-card.yellow .risk-label {
            color: #f1c40f;
        }

        .risk-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .action-banner {
            background-color: #f5fcf5;
            border: 1px solid #c8e6c9;
            border-radius: 6px;
            padding: 20px;
            display: table;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 30px;
        }

        .action-cell {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
        }

        .action-label {
            font-size: 12px;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 5px;
        }

        .action-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .action-text {
            font-size: 13px;
            color: #555;
        }

        .button-container {
            text-align: center;
            margin: 30px 0;
            border-top: 1px solid #eee;
            padding-top: 30px;
            position: relative;
        }

        .btn {
            background-color: #0052cc;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            display: inline-block;
            text-transform: uppercase;
        }

        .footer {
            background-color: #001f5c;
            color: #a0b2d8;
            padding: 25px 30px;
            font-size: 12px;
            text-align: center;
        }

        .footer p {
            margin: 5px 0;
        }

        .footer strong {
            color: #ffffff;
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <h1>HSE AUDIT SUBMITTED</h1>
                <p>Your audit has been successfully submitted</p>
            </div>
        </div>

        <!-- Body -->
        <div class="body-content">
            <div class="greeting">
                <p style="margin-top:0;">Dear Team,</p>
                <p>The HSE Audit has been successfully submitted in the system.</p>
            </div>

            <!-- Audit Details -->
            <div class="section-title">
                &#8505;&#65039; &nbsp; AUDIT DETAILS
            </div>
            <div class="details-box">
                <table class="details-table">
                    <tr>
                        <td>
                            <div class="label">Audit No</div>
                            <div class="value"><?= esc($audit_no) ?></div>
                        </td>
                        <td>
                            <div class="label">Audit Date</div>
                            <div class="value"><?= esc($audit_date) ?></div>
                        </td>
                        <td>
                            <div class="label">Submitted On</div>
                            <div class="value"><?= date('d M Y h:i A') ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 20px;">
                            <div class="label">Site Name</div>
                            <div class="value"><?= esc($site_name) ?></div>
                        </td>
                        <td style="padding-top: 20px;">
                            <div class="label">Region</div>
                            <div class="value"><?= esc($region) ?></div>
                        </td>
                        <td style="padding-top: 20px;">
                            <div class="label">Submitted By</div>
                            <div class="value"><?= esc($submitted_by) ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 20px;">
                            <div class="label">Auditor</div>
                            <div class="value"><?= esc($auditor_name) ?></div>
                        </td>
                        <td style="padding-top: 20px;" colspan="2">
                            <div class="label">Audit Type</div>
                            <div class="value">Perform Audit</div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Summary Cards -->
            <div class="cards-container">
                <div class="card blue">
                    <div class="card-label">Total Attempted Questions</div>
                    <div class="card-value"><?= esc($total_questions) ?></div>
                </div>
                <div class="card red">
                    <div class="card-label">No Findings (NC)</div>
                    <div class="card-value"><?= esc($nc_count) ?></div>
                </div>
                <div class="card orange">
                    <div class="card-label">Recommendation (RD)</div>
                    <div class="card-value"><?= esc($rd_count) ?></div>
                </div>
                <div class="card green">
                    <div class="card-label">Score</div>
                    <div class="card-value"><?= esc($score) ?>%</div>
                </div>
            </div>

            <!-- Findings & Risk Split -->
            <div class="split-section">
                <!-- Left: Findings Summary -->
                <div class="split-left">
                    <div class="summary-box" style="height: 100%;">
                        <div class="summary-title">&#128202; FINDINGS SUMMARY</div>
                        <table class="data-table">
                            <tr>
                                <th style="width:70%;">Finding</th>
                                <th>Count</th>
                            </tr>
                            <tr>
                                <td><span style="color:#2ecc71;">&#10004;&#65039;</span> &nbsp; YES</td>
                                <td><?= esc($yes_count) ?></td>
                            </tr>
                            <tr>
                                <td><span style="color:#e74c3c;">&#10060;</span> &nbsp; NO</td>
                                <td><?= esc($no_count) ?></td>
                            </tr>
                            <tr>
                                <td><span style="color:#95a5a6;">&#9866;</span> &nbsp; NA</td>
                                <td><?= esc($na_count) ?></td>
                            </tr>
                            <tr class="total-row">
                                <td style="text-align:right;">TOTAL</td>
                                <td><?= esc($total_questions) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="split-spacer"></div>

                <!-- Right: Risk Summary -->
                <div class="split-right">
                    <div class="summary-box" style="height: 100%;">
                        <div class="summary-title">&#128737;&#65039; RISK SUMMARY</div>
                        <div class="risk-cards">
                            <div class="risk-card black">
                                <div class="risk-label">Black</div>
                                <div class="risk-value"><?= esc($color_black) ?></div>
                            </div>
                            <div class="risk-card red">
                                <div class="risk-label">Red</div>
                                <div class="risk-value"><?= esc($color_red) ?></div>
                            </div>
                            <div class="risk-card yellow">
                                <div class="risk-label">Yellow</div>
                                <div class="risk-value"><?= esc($color_yellow) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Banner -->
            <div class="action-banner">
                <div class="action-cell" style="border-right: 1px solid #c8e6c9; padding-right: 20px;">
                    <div class="action-label">&#128197; NEXT AUDIT DATE</div>
                    <div class="action-value"><?= esc($next_audit_date) ?></div>
                </div>
                <div class="action-cell" style="padding-left: 20px;">
                    <div class="action-label">&#128196; REMARKS</div>
                    <div class="action-text">Please review all findings and ensure CAPA actions are initiated on time.
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="button-container">
                <p style="color: #555; font-size: 14px; margin-bottom: 20px;">Please review the complete audit details
                    and CAPA in the HSE Portal.</p>
                <!-- This link assumes base_url to HSE portal -->
                <a href="<?= base_url('/Masters/Hse_audit') ?>" class="btn">VIEW AUDIT DETAILS</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Stay Safe. Stay Compliant.</strong></p>
            <p>Together We Build a Safer Workplace.</p>
            <p style="margin-top: 15px; font-size: 10px; color: #6a7c9f;">This is an automated email. Please do not
                reply to this email.</p>
        </div>
    </div>
</body>

</html>