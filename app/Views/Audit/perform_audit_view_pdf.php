<?php
    // Safety check for details array
    if (isset($details) && is_array($details) && count($details) > 0) {
        if (isset($details[0]) && is_array($details[0])) {
            $details = $details[0];
        }
    } else {
        $details = [];
    }

    // Fallback fetch for missing Auditee / Region
    if (empty($details['auditee_name']) || empty($details['region'])) {
        try {
            $db = \Config\Database::connect();
            $auditNo = $details['audit_no'] ?? '';
            if (!empty($auditNo)) {
                $isNormalFallback = (strpos(strtolower($details['audit_name'] ?? ''), 'normal') !== false);
                $headerTable = $isNormalFallback ? 'alert_normal_audit' : 'alert_final_structured_audit';
                $query = $db->table($headerTable)->select('auditee_name, region')->where('audit_no', $auditNo)->get(1);
                if ($rowHdr = $query->getRowArray()) {
                    $details['auditee_name'] = !empty($details['auditee_name']) ? $details['auditee_name'] : ($rowHdr['auditee_name'] ?? '');
                    $details['region'] = !empty($details['region']) ? $details['region'] : ($rowHdr['region'] ?? '');
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    $audit_details = isset($audit_details) && is_array($audit_details) ? $audit_details : [];
    $reportTitle = isset($report_title) ? $report_title : 'OE Audit Report';
    $auditScore = isset($details['audit_score']) ? $details['audit_score'] : '0';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($reportTitle) ?> - <?= htmlspecialchars($details['audit_no'] ?? '') ?></title>
<style>
    @page {
        margin: 22px 20px 32px 20px;
    }
    body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-size: 8pt;
        color: #1e293b;
        margin: 0;
        padding: 0;
        line-height: 1.3;
    }

    /* Executive Header */
    .title-banner {
        background: #1e3a8a;
        color: #ffffff;
        padding: 8px 14px;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    .title-banner table {
        width: 100%;
        border-collapse: collapse;
        border: none;
    }
    .title-banner td {
        border: none;
        padding: 0;
        vertical-align: middle;
    }
    .title-banner h1 {
        margin: 0;
        font-size: 13pt;
        font-weight: bold;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #ffffff;
    }
    .title-banner .sub-title {
        font-size: 8pt;
        color: #93c5fd;
        margin-top: 2px;
    }
    .score-badge {
        display: inline-block;
        background: #10b981;
        color: #ffffff;
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 10pt;
        font-weight: bold;
        text-align: right;
    }

    /* Meta Summary Box */
    .meta-box {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        background: #ffffff;
    }
    .meta-box th {
        background: #f1f5f9;
        color: #334155;
        font-weight: bold;
        font-size: 7.5pt;
        text-align: left;
        padding: 5px 8px;
        border: 1px solid #e2e8f0;
        width: 15%;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .meta-box td {
        padding: 5px 8px;
        border: 1px solid #e2e8f0;
        font-size: 8pt;
        color: #0f172a;
        width: 35%;
    }

    /* Attendance Table */
    .section-title {
        font-size: 9pt;
        font-weight: bold;
        color: #1e3a8a;
        margin: 10px 0 4px 0;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .att-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
        border: 1px solid #cbd5e1;
    }
    .att-table th {
        background: #3b82f6;
        color: #ffffff;
        font-size: 7.5pt;
        font-weight: bold;
        padding: 5px 6px;
        border: 1px solid #2563eb;
        text-align: center;
    }
    .att-table td {
        padding: 5px 6px;
        font-size: 7.5pt;
        border: 1px solid #cbd5e1;
        text-align: center;
    }

    /* Checklist Table */
    .audit-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .audit-table thead {
        display: table-header-group;
    }
    .audit-table tr {
        page-break-inside: avoid;
    }
    .audit-table th {
        background-color: #1e3a8a;
        color: #ffffff;
        font-size: 7.5pt;
        font-weight: bold;
        text-align: center;
        padding: 6px 4px;
        border: 1px solid #0f2b6b;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .audit-table td {
        padding: 5px 4px;
        font-size: 7.5pt;
        line-height: 1.35;
        vertical-align: top;
        border: 1px solid #cbd5e1;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .audit-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    /* Column Widths (Total 100%) */
    .col-sr { width: 3.5%; text-align: center; font-weight: bold; }
    .col-category { width: 10.5%; font-weight: 600; color: #0f172a; }
    .col-question { width: 16%; color: #1e293b; }
    .col-target { width: 22%; color: #334155; }
    .col-weightage { width: 4.5%; text-align: center; font-weight: 600; }
    .col-finding { width: 6%; text-align: center; }
    .col-score { width: 4.5%; text-align: center; font-weight: bold; }
    .col-remark { width: 17%; color: #1e293b; }
    .col-attachment { width: 16%; text-align: center; }

    /* Badges */
    .badge-finding {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-weight: bold;
        font-size: 7pt;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .badge-yes { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-no { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-na { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

    /* Attachment Buttons / Previews */
    .att-btn {
        display: inline-block;
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 7pt;
        font-weight: bold;
        text-decoration: none;
        margin: 2px auto;
        text-align: center;
        max-width: 90%;
        word-break: break-all;
    }
    .att-pdf { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .att-excel { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .att-file { background: #faf5ff; color: #7e22ce; border: 1px solid #e9d5ff; }

    .att-img-container {
        display: inline-block;
        text-align: center;
        text-decoration: none;
        margin: 1px auto;
    }
    .att-img {
        max-width: 75px;
        max-height: 55px;
        border-radius: 3px;
        border: 1px solid #94a3b8;
        display: block;
        margin: 0 auto;
    }
    .att-view-label {
        font-size: 6.5pt;
        color: #1d4ed8;
        display: block;
        margin-top: 2px;
        text-decoration: underline;
    }
</style>
</head>
<body>

<!-- Meta Summary Table (Only requested 10 options) -->
<table class="meta-box" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 15%;">Audit No.</th>
        <td style="width: 35%;"><?= htmlspecialchars($details['audit_no'] ?? 'N/A') ?></td>
        <th style="width: 15%;">Audit Name</th>
        <td style="width: 35%;"><?= htmlspecialchars($details['audit_name'] ?? $details['template_audit_name'] ?? 'OE Audit') ?></td>
    </tr>
    <tr>
        <th>Auditor Name</th>
        <td><?= htmlspecialchars($details['auditor_name'] ?? 'N/A') ?></td>
        <th>Audit Date</th>
        <td><?= htmlspecialchars($details['audit_date'] ?? 'N/A') ?></td>
    </tr>
    <tr>
        <th>Client Name</th>
        <td><?= htmlspecialchars($details['client_name'] ?? 'N/A') ?></td>
        <th>Site Details</th>
        <td><?= htmlspecialchars($details['location'] ?? $details['client_name'] ?? 'N/A') ?></td>
    </tr>
    <tr>
        <th>Auditee Name</th>
        <td><?= htmlspecialchars($details['auditee_name'] ?? 'N/A') ?></td>
        <th>Region</th>
        <td><?= htmlspecialchars($details['region'] ?? 'N/A') ?></td>
    </tr>
    <tr>
        <th>Next Audit Date</th>
        <td><?= htmlspecialchars($details['next_date'] ?? 'N/A') ?></td>
        <th>Score</th>
        <td style="font-weight: bold; color: #15803d; font-size: 8.5pt;"><?= htmlspecialchars($auditScore) ?>%</td>
    </tr>
</table>

<!-- Auditee Attendance (if available) -->
<?php if (!empty($attendance) && is_array($attendance)): ?>
<div class="section-title">Auditee Attendance</div>
<table class="att-table">
    <thead>
        <tr>
            <th style="width: 5%;">Sr.</th>
            <th style="width: 25%;">Auditee Name</th>
            <th style="width: 15%;">Opening Date</th>
            <th style="width: 20%;">Opening Sign</th>
            <th style="width: 15%;">Closing Date</th>
            <th style="width: 20%;">Closing Sign</th>
        </tr>
    </thead>
    <tbody>
        <?php $aidx = 1; foreach ($attendance as $att): ?>
        <tr>
            <td><?= $aidx++ ?></td>
            <td style="text-align: left;"><?= htmlspecialchars($att['auditee_attendance'] ?? 'N/A') ?></td>
            <td><?= !empty($att['opening_date']) ? date('d-m-Y', strtotime($att['opening_date'])) : 'N/A' ?></td>
            <td>
                <?php if (!empty($att['opening_sign'])): ?>
                    <a href="<?= base_url($att['opening_sign']) ?>" target="_blank">
                        <img src="<?= FCPATH . $att['opening_sign'] ?>" style="max-height:35px; max-width:80px;" alt="Opening Sign">
                    </a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </td>
            <td><?= !empty($att['closing_date']) ? date('d-m-Y', strtotime($att['closing_date'])) : 'N/A' ?></td>
            <td>
                <?php if (!empty($att['closing_sign'])): ?>
                    <a href="<?= base_url($att['closing_sign']) ?>" target="_blank">
                        <img src="<?= FCPATH . $att['closing_sign'] ?>" style="max-height:35px; max-width:80px;" alt="Closing Sign">
                    </a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<!-- Audit Checklist Table -->
<table class="audit-table">
    <thead>
        <tr>
            <th class="col-sr">Sr.</th>
            <th class="col-category">Category</th>
            <th class="col-question">Audit Question</th>
            <th class="col-target">Target / Criteria</th>
            <th class="col-weightage">Weight (%)</th>
            <th class="col-finding">Findings</th>
            <th class="col-score">Score</th>
            <th class="col-remark">Compulsory Remarks</th>
            <th class="col-attachment">Attachments / Proof</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $tplType = strtolower(trim($details['audit_template_type'] ?? (strpos(strtolower($details['audit_name'] ?? ''), 'normal') !== false ? 'normal' : 'oe')));
        $isNormalTpl = ($tplType === 'normal');

        foreach ($audit_details as $key => $row): 
            $displayQuestion = $isNormalTpl ? ($row['audit_question'] ?? '') : ($row['audit_parameter'] ?? $row['audit_question'] ?? '');
            if (empty($displayQuestion)) {
                $displayQuestion = $row['audit_question'] ?? 'N/A';
            }

            $displayTarget = $row['risk_priority'] ?? '';
            // Safely clean target without letting raw <pre> break Dompdf line heights
            $cleanTarget = strip_tags($displayTarget, '<b><strong><i><em><br><p><ul><li>');
            if (strpos($cleanTarget, '<br') === false && strpos($cleanTarget, '<p') === false) {
                $cleanTarget = nl2br($cleanTarget);
            }
            if (empty(trim(strip_tags($cleanTarget)))) {
                $cleanTarget = 'N/A';
            }

            $finding = strtoupper(trim($row['audit_finding'] ?? ''));
            $findingClass = ($finding === 'YES') ? 'badge-yes' : (($finding === 'NO') ? 'badge-no' : 'badge-na');

            // Resolve Attachment
            $rawAtt = trim($row['audit_attachment'] ?? '');
            $hasAtt = false;
            $attHtml = '';

            if (!empty($rawAtt)) {
                $normPath = ltrim(preg_replace('#/+#', '/', $rawAtt), '/');

                // Dynamically resolve public URL for active server host
                $requestHost = $_SERVER['HTTP_HOST'] ?? '';
                $scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) ? 'https://' : 'http://';

                if (strpos($rawAtt, 'http://') === 0 || strpos($rawAtt, 'https://') === 0) {
                    $publicUrl = $rawAtt;
                } elseif (!empty($requestHost)) {
                    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
                    $basePrefix = ($scriptDir !== '' && $scriptDir !== '.' && $scriptDir !== '/') ? $scriptDir : '';
                    $publicUrl = $scheme . $requestHost . $basePrefix . '/' . $normPath;
                } else {
                    $publicUrl = base_url($normPath);
                }

                $escapedUrl = htmlspecialchars($publicUrl, ENT_QUOTES, 'UTF-8');
                $ext = strtolower(pathinfo(parse_url($normPath, PHP_URL_PATH), PATHINFO_EXTENSION));
                $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);

                if ($isImg) {
                    $candidates = [
                        (defined('ROOTPATH') ? ROOTPATH : '') . $normPath,
                        (defined('FCPATH') ? FCPATH : '') . $normPath,
                        (defined('FCPATH') ? FCPATH : '') . '../' . $normPath,
                        ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/' . $normPath,
                    ];
                    $localFile = null;
                    foreach ($candidates as $c) {
                        if (!empty($c) && file_exists($c)) { 
                            $localFile = $c; 
                            break; 
                        }
                    }

                    if ($localFile && filesize($localFile) > 0 && filesize($localFile) <= 3145728) {
                        $imgW = 65;
                        $imgH = 45;
                        $sz = @getimagesize($localFile);
                        if ($sz && !empty($sz[0]) && !empty($sz[1])) {
                            $origW = $sz[0];
                            $origH = $sz[1];
                            $scale = min(65 / $origW, 45 / $origH);
                            $imgW = max(20, round($origW * $scale));
                            $imgH = max(15, round($origH * $scale));
                        }

                        $attHtml = '<table cellpadding="0" cellspacing="0" style="width:100%; border:none; border-collapse:collapse; margin:0 auto;">'
                                 . '<tr><td style="border:none; text-align:center; padding:0;">'
                                 . '<a href="' . $escapedUrl . '" target="_blank">'
                                 . '<img src="' . $localFile . '" width="' . $imgW . '" height="' . $imgH . '" style="border:1px solid #94a3b8; border-radius:3px;" alt="Proof Image" />'
                                 . '</a>'
                                 . '</td></tr>'
                                 . '<tr><td style="border:none; text-align:center; padding-top:2px;">'
                                 . '<a href="' . $escapedUrl . '" target="_blank" style="color:#1d4ed8; text-decoration:underline; font-size:7pt; font-weight:bold; display:inline-block;">'
                                 . 'View Image &raquo;'
                                 . '</a>'
                                 . '</td></tr>'
                                 . '</table>';
                        $hasAtt = true;
                    } else {
                        // Fallback to URL link
                        $attHtml = '<div style="text-align:center; padding:4px 0;"><a href="' . $escapedUrl . '" target="_blank" class="att-btn att-pdf" style="text-decoration:underline;">View Image &raquo;</a></div>';
                        $hasAtt = true;
                    }
                } else {
                    // PDF, Excel, or other Document
                    $isExcel = in_array($ext, ['xls', 'xlsx', 'csv']);
                    $isPdf = ($ext === 'pdf');
                    $badgeCls = $isExcel ? 'att-excel' : ($isPdf ? 'att-pdf' : 'att-file');
                    $label = $isPdf ? 'View PDF &raquo;' : ($isExcel ? 'View Excel &raquo;' : 'View File &raquo;');
                    
                    $attHtml = '<div style="text-align:center; padding:4px 0;">'
                             . '<a href="' . $escapedUrl . '" target="_blank" class="att-btn ' . $badgeCls . '" style="text-decoration:underline;">' 
                             . $label 
                             . '</a>'
                             . '</div>';
                    $hasAtt = true;
                }
            }

            if (!$hasAtt) {
                $attHtml = '<span style="color:#94a3b8; font-size:7pt;">-</span>';
            }
        ?>
        <tr>
            <td class="col-sr"><?= $key + 1 ?></td>
            <td class="col-category"><?= htmlspecialchars($row['category'] ?? 'N/A') ?></td>
            <td class="col-question"><?= htmlspecialchars($displayQuestion) ?></td>
            <td class="col-target"><?= $cleanTarget ?></td>
            <td class="col-weightage"><?= htmlspecialchars($row['weightage'] ?? '0') ?>%</td>
            <td class="col-finding">
                <span class="badge-finding <?= $findingClass ?>"><?= htmlspecialchars($finding ?: 'N/A') ?></span>
            </td>
            <td class="col-score">
                <?= ($finding === 'YES' ? htmlspecialchars($row['weightage'] ?? '0') . '%' : '0') ?>
            </td>
            <td class="col-remark">
                <?= !empty($row['audit_remark']) ? nl2br(htmlspecialchars($row['audit_remark'])) : '<span style="color:#94a3b8;">-</span>' ?>
            </td>
            <td class="col-attachment"><?= $attHtml ?></td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($audit_details)): ?>
        <tr>
            <td colspan="9" style="text-align: center; padding: 25px; color: #94a3b8; font-style: italic;">
                No audit questions recorded for this audit.
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Running Footer for All Pages -->
<script type="text/php">
if (isset($pdf)) {
    $font = $fontMetrics->get_font("Helvetica", "normal");
    $size = 7.5;
    $y = $pdf->get_height() - 20;
    $x_left = 20;
    $x_right = $pdf->get_width() - 85;
    $pdf->page_text($x_left, $y, "FM Logistic OE Audit Report | Audit No: " . <?= json_encode($details['audit_no'] ?? '') ?>, $font, $size, array(0.35, 0.4, 0.45));
    $pdf->page_text($x_right, $y, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, $size, array(0.35, 0.4, 0.45));
}
</script>

</body>
</html>
