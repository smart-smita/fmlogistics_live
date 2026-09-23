<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($status_text) ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f7fc; color: #333333; }
        .email-wrapper { max-width: 700px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background-color: #001f5c; color: #ffffff; padding: 25px 30px; display: table; width: 100%; box-sizing: border-box; }
        .header h2 { margin: 0; font-size: 22px; color: #f39c12; text-transform: uppercase; font-weight: 800; }
        .body-content { padding: 30px; }
        .greeting { font-size: 15px; margin-bottom: 20px; }
        .details-box { border: 1px solid #e1e8f5; border-radius: 6px; padding: 10px; margin-bottom: 25px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e1e8f5; font-size: 13px; }
        .data-table th { background-color: #f8fafc; font-weight: 600; color: #444; width: 35%; }
        .data-table tr:last-child th, .data-table tr:last-child td { border-bottom: none; }
        .footer { background-color: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #666666; border-top: 1px solid #e1e8f5; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <h2><?= esc($status_text) ?></h2>
        </div>
        <div class="body-content">
            <div class="greeting">
                Dear <strong><?= esc($receiver_name) ?></strong>,<br><br>
                <?= $description ?>
            </div>

            <div class="details-box">
                <table class="data-table">
                    <tr>
                        <th>Audit ID</th>
                        <td><?= esc($nc['audit_details_id'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Audit Number</th>
                        <td><?= esc($nc['audit_no'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Audit Name</th>
                        <td><?= esc($nc['audit_name'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Audit Date</th>
                        <td><?= isset($nc['audit_date']) ? date('d-M-Y', strtotime($nc['audit_date'])) : '' ?></td>
                    </tr>
                    <tr>
                        <th>Client Name</th>
                        <td><?= esc($nc['client_name'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td><?= esc($nc['location'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Region</th>
                        <td><?= esc($nc['region_name'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td><?= esc($nc['category_name'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Risk Priority</th>
                        <td><?= esc($nc['risk_priority'] ?? '') ?></td>
                    </tr>
                </table>
            </div>

            <p style="font-size: 14px; margin-top: 20px;">
                Please log into the ALert system for more details.
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0;">This is an auto-generated email from ALert. Please do not reply.</p>
            <p style="margin: 5px 0 0 0;">&copy; <?= date('Y') ?> Unitglo Solutions. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
