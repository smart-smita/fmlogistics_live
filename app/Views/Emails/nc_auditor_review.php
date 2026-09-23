<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HSE NC Submitted for Auditor Review</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
            color: #333333;
        }

        .email-wrapper {
            max-width: 700px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #001f5c;
            color: #ffffff;
            padding: 25px 30px;
            display: table;
            width: 100%;
            box-sizing: border-box;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 30%;
            font-weight: bold;
            font-size: 20px;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 70%;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #ffffff;
            font-weight: 500;
        }

        .header h2 {
            margin: 0;
            font-size: 22px;
            color: #3399ff;
            text-transform: uppercase;
            font-weight: 800;
        }

        .body-content {
            padding: 30px;
        }

        .greeting {
            font-size: 15px;
            margin-bottom: 20px;
        }

        .section-header {
            display: flex;
            align-items: center;
            color: #0052cc;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .details-box {
            border: 1px solid #e1e8f5;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 25px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e8f5;
            font-size: 13px;
        }

        .data-table tr:last-child th,
        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table th {
            color: #555;
            font-weight: 600;
            width: 30%;
            background: #fafbfc;
        }

        .badge {
            background-color: #e74c3c;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-box {
            border: 1px solid #0052cc;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .status-label {
            font-size: 14px;
            font-weight: bold;
            color: #0052cc;
            margin-right: 15px;
            text-transform: uppercase;
        }

        .status-badge {
            background-color: #0052cc;
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            display: inline-block;
        }

        .alert-box {
            background-color: #f5f9ff;
            border-left: 4px solid #0052cc;
            padding: 15px;
            font-size: 13px;
            color: #666;
            margin-bottom: 30px;
        }

        .footer-action {
            display: table;
            width: 100%;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .footer-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
            font-size: 13px;
            color: #555;
        }

        .footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
        }

        .btn {
            background-color: #001f5c;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 13px;
            display: inline-block;
        }

        .footer {
            background-color: #001f5c;
            color: #a0b2d8;
            padding: 20px 30px;
            font-size: 11px;
            text-align: center;
        }

        .footer table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer td {
            text-align: center;
            color: #a0b2d8;
            font-size: 11px;
        }

        .footer a {
            color: #a0b2d8;
            text-decoration: none;
        }

        .automated-text {
            margin-top: 15px;
            border-top: 1px solid #1a3a7a;
            padding-top: 15px;
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="header">
            <div class="header-left">&#128227; ALert<br><span
                    style="font-size:8px;font-weight:normal;letter-spacing:1px;">FM LOGISTICS</span></div>
            <div class="header-right">
                <h1>HSE NC SUBMITTED FOR</h1>
                <h2>AUDITOR REVIEW</h2>
            </div>
        </div>

        <div class="body-content">
            <div class="greeting">
                <p style="margin-top:0;"><strong>Dear Auditor,</strong></p>
                <p>A Non-Conformance (NC) has been reviewed by the Cluster Manager and is now submitted for your review.
                </p>
            </div>

            <div class="section-header">
                &#128196; &nbsp; NC DETAILS
            </div>

            <div class="details-box">
                <table class="data-table">
                    <tr>
                        <th>Audit No</th>
                        <td><?= esc($audit_no) ?></td>
                    </tr>
                    <tr>
                        <th>Audit Name</th>
                        <td><?= esc($audit_name) ?></td>
                    </tr>
                    <tr>
                        <th>Client</th>
                        <td><?= esc($client) ?></td>
                    </tr>
                    <tr>
                        <th>Region</th>
                        <td><?= esc($region) ?></td>
                    </tr>
                    <tr>
                        <th>Cluster</th>
                        <td><?= esc($cluster) ?></td>
                    </tr>
                    <tr>
                        <th>Auditor</th>
                        <td><?= esc($auditor) ?></td>
                    </tr>
                    <tr>
                        <th>Auditee</th>
                        <td><?= esc($auditee) ?></td>
                    </tr>
                    <tr>
                        <th>Audit Date</th>
                        <td><?= esc($audit_date) ?></td>
                    </tr>
                    <tr>
                        <th>NC Type</th>
                        <td><span class="badge"><?= esc($nc_type) ?></span></td>
                    </tr>
                    <tr>
                        <th>Question</th>
                        <td><?= esc($question) ?></td>
                    </tr>
                    <tr>
                        <th>NC Remark</th>
                        <td><?= esc($nc_remark) ?></td>
                    </tr>
                </table>
            </div>

            <div class="status-box">
                <span class="status-label">&#128203; CURRENT STATUS</span>
                <span class="status-badge">AUDITOR REVIEW</span>
            </div>

            <div class="alert-box">
                &#8505;&#65039; &nbsp; Please review the NC and take necessary action.
            </div>

            <div class="footer-action">
                <div class="footer-left">
                    Regards,<br>
                    <strong>FM Logistics HSE System</strong>
                </div>
                <div class="footer-right">
                    <a href="<?= esc($action_link) ?>" class="btn">&#128065;&#65039; REVIEW NC</a>
                </div>
            </div>
        </div>

        <div class="footer">
            <table>
                <tr>
                    <td>&#9993;&#65039; info@fmlogistics.com</td>
                    <td>&#127760; www.fmlogistic.unitglo.com</td>
                    <td>&#128222; +91 22 1234 5678</td>
                </tr>
            </table>
            <div class="automated-text">This is an automated email. Please do not reply.</div>
        </div>
    </div>
</body>

</html>