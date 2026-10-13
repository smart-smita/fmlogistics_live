<?php
/**
 * Corporate Enterprise LMRA PDF Template
 * Redesigned for high visual impact, clean typography, and 100% data integrity.
 */

// Safely extract main details record
$details = isset($details[0]) ? $details[0] : (isset($details) ? $details : []);

// Status Mapping with badges
$status_id = isset($details['status']) ? (string)$details['status'] : '0';
$status_config = [
    '0' => ['label' => 'Pending', 'bg' => '#EAB308', 'color' => '#FFFFFF'],
    '1' => ['label' => 'Verified', 'bg' => '#3B82F6', 'color' => '#FFFFFF'],
    '2' => ['label' => 'Open', 'bg' => '#F97316', 'color' => '#FFFFFF'],
    '3' => ['label' => 'Cancelled', 'bg' => '#64748B', 'color' => '#FFFFFF'],
    '4' => ['label' => 'Request Required', 'bg' => '#8B5CF6', 'color' => '#FFFFFF'],
    '5' => ['label' => 'Closed', 'bg' => '#10B981', 'color' => '#FFFFFF'],
];

$status_info = isset($status_config[$status_id]) ? $status_config[$status_id] : ['label' => 'Unknown', 'bg' => '#94A3B8', 'color' => '#FFFFFF'];

// Decode JSON Data structures
$sta_data = !empty($details['sta_data']) ? json_decode($details['sta_data'], true) : [];
$lmra_json_data = !empty($details['lmra_json_data']) ? json_decode($details['lmra_json_data'], true) : [];

// Basic Safety Questions mapping
$quotations = [
    'do_i_have_the_right_ppes' => 'Do I have right PPE\'s?',
    'do_i_have_the_right_tools_and_parts' => 'Do I have right tool and parts?',
    'look_around_you_is_it_safe' => 'Look Around you, is it safe?',
    'do_i_know_what_to_do_if_things_go_wrong' => 'Do you know what to do if things go wrong?',
    'what_are_the_biggest_risk_that_can_result_in_an_injury' => 'What are the biggest risk that can result in an injury?'
];

// Helper SVGs encoded as Data URIs for crisp Dompdf rendering
$svg_shield = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24"><path fill="#0B2265" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/><path fill="#F59E0B" d="M11 7h2v4h4v2h-4v4h-2v-4H7v-2h4V7z"/></svg>');
$svg_building = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#0B2265"><path d="M19 21H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1zm-9-14H8v2h2V7zm0 4H8v2h2v-2zm0 4H8v2h2v-2zm6-8h-2v2h2V7zm0 4h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>');
$svg_clipboard = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#0B2265"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm7 16H5V5h2v2h10V5h2v14z"/></svg>');
$svg_pin = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#0B2265"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>');
$svg_calendar = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#0B2265"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>');
$svg_clock = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#0B2265"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>');
$svg_user = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#0B2265"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>');
$svg_user_check = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#0B2265"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>');
$svg_mail = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#0B2265"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>');
$svg_phone = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#0B2265"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>');
$svg_briefcase = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#0B2265"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/></svg>');
$svg_comment = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#FFFFFF"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/></svg>');
$svg_comment_dark = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#0B2265"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/></svg>');
$svg_questions = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#FFFFFF"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 16h-2v-2h2v2zm1.07-7.75l-.9.92C12.45 11.9 12 12.5 12 14h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/></svg>');

// Category Icon mapping function
function getCategoryIconSvg($catName) {
    $catUpper = strtoupper($catName);
    if (strpos($catUpper, 'HEIGHT') !== false) {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#FFFFFF"><path d="M19 3v18h-2v-3H7v3H5V3h2v3h10V3h2zm-2 5H7v3h10V8zm0 5H7v3h10v-3z"/></svg>';
    } else if (strpos($catUpper, 'LIFT') !== false || strpos($catUpper, 'MATERIAL') !== false) {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#FFFFFF"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM11 7h2v4h4v2h-4v4h-2v-4H7v-2h4V7z"/></svg>';
    } else if (strpos($catUpper, 'CONFINED') !== false || strpos($catUpper, 'SPACE') !== false) {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#FFFFFF"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>';
    } else {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#FFFFFF"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>';
    }
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

// Helper to generate User Initials
function getUserInitials($name) {
    $name = trim(preg_replace('/[^a-zA-Z0-9\s_]/', ' ', $name));
    $parts = preg_split('/[\s_]+/', $name);
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    } else if (!empty($parts[0])) {
        return strtoupper(substr($parts[0], 0, 2));
    }
    return 'U';
}

// Helper to determine Role Color Theme
function getRoleTheme($designation) {
    $d = strtolower(trim($designation));
    if (strpos($d, 'audit') !== false) {
        return [
            'avatar_bg' => '#DCFCE7', 'avatar_color' => '#15803D',
            'badge_bg' => '#DCFCE7', 'badge_color' => '#15803D',
            'box_bg' => '#F0FDF4', 'box_border' => '#DCFCE7'
        ];
    } else if (strpos($d, 'admin') !== false) {
        return [
            'avatar_bg' => '#F3E8FF', 'avatar_color' => '#7E22CE',
            'badge_bg' => '#F3E8FF', 'badge_color' => '#7E22CE',
            'box_bg' => '#FAF5FF', 'box_border' => '#F3E8FF'
        ];
    } else if (strpos($d, 'hse') !== false || strpos($d, 'officer') !== false) {
        return [
            'avatar_bg' => '#FFEDD5', 'avatar_color' => '#C2410C',
            'badge_bg' => '#FFEDD5', 'badge_color' => '#C2410C',
            'box_bg' => '#FFF7ED', 'box_border' => '#FFEDD5'
        ];
    } else if (strpos($d, 'manag') !== false) {
        return [
            'avatar_bg' => '#CCFBF1', 'avatar_color' => '#0F766E',
            'badge_bg' => '#CCFBF1', 'badge_color' => '#0F766E',
            'box_bg' => '#F0FDFA', 'box_border' => '#CCFBF1'
        ];
    } else { // Engineer / Reporter / Default
        return [
            'avatar_bg' => '#DBEAFE', 'avatar_color' => '#1D4ED8',
            'badge_bg' => '#DBEAFE', 'badge_color' => '#1D4ED8',
            'box_bg' => '#EFF6FF', 'box_border' => '#DBEAFE'
        ];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>LMRA Report - <?= htmlspecialchars(isset($details['lmra_details_id']) ? $details['lmra_details_id'] : '') ?></title>
<style>
    @page {
        margin: 25px 20px 45px 20px;
    }
    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 10px;
        color: #1E293B;
        background-color: #FFFFFF;
        margin: 0;
        padding: 0;
    }

    /* Dompdf Fixed Page Footer (Strictly Table Based) */
    .page-footer {
        position: fixed;
        bottom: -40px;
        left: -20px;
        right: -20px;
        height: 25px;
        background-color: #0B2265;
    }
    .footer-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
        margin: 0;
        padding: 0;
    }
    .footer-table td {
        padding: 5px 10px;
        font-size: 9.5px;
        font-weight: bold;
        border: none;
        vertical-align: middle;
    }

    /* Top Main Header */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4px;
        border: none;
    }
    .header-table td {
        border: none;
        padding: 0;
    }
    .header-logo-cell {
        width: 30%;
        vertical-align: middle;
    }
    .header-title-cell {
        width: 36%;
        text-align: center;
        vertical-align: middle;
    }
    .header-meta-cell {
        width: 34%;
        vertical-align: middle;
    }
    .meta-box {
        border: 1.5px solid #CBD5E1;
        border-radius: 6px;
        padding: 6px 8px;
        background-color: #F8FAFC;
        font-size: 9.5px;
        word-wrap: break-word;
        overflow: hidden;
    }
    .meta-box td {
        word-break: break-word;
    }

    /* Yellow Accent Divider */
    .yellow-divider {
        height: 3px;
        background-color: #F59E0B;
        margin-bottom: 12px;
        border-radius: 2px;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: bold;
        font-size: 9.5px;
        text-align: center;
    }

    /* Layout Grid Table for Side-by-Side Cards */
    .grid-layout-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
        margin-bottom: 14px;
    }
    .grid-layout-table > tbody > tr > td {
        border: none;
        padding: 0;
        vertical-align: top;
    }

    /* Card Box */
    .card-box {
        border: 1px solid #CBD5E1;
        border-radius: 5px;
        overflow: hidden;
        background-color: #FFFFFF;
    }
    .card-header {
        background-color: #0B2265;
        color: #FFFFFF;
        padding: 6px 10px;
        font-size: 10.5px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }

    .details-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
    }
    .details-table tr {
        border-bottom: 1px solid #E2E8F0;
    }
    .details-table tr:last-child {
        border-bottom: none;
    }
    .details-table th {
        width: 42%;
        text-align: left;
        padding: 5px 7px;
        font-weight: bold;
        color: #1E293B;
        background-color: #F8FAFC;
        font-size: 9.5px;
        border-right: 1px solid #E2E8F0;
        border-top: none;
        border-bottom: none;
        border-left: none;
    }
    .details-table td {
        padding: 5px 7px;
        font-size: 9.5px;
        color: #334155;
        border: none;
    }

    /* Rejection Comment Alert Card */
    .rejection-card {
        border: 1px solid #CBD5E1;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 14px;
        background-color: #FFFFFF;
        page-break-inside: avoid;
    }
    .rejection-header {
        background-color: #0B2265;
        color: #FFFFFF;
        padding: 6px 10px;
        font-weight: bold;
        font-size: 10.5px;
    }
    .rejection-body {
        padding: 8px 10px;
        font-size: 9.5px;
        color: #1E293B;
        line-height: 1.4;
        background-color: #FFFFFF;
    }

    /* Tables Styling */
    .section-card {
        border: 1px solid #CBD5E1;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 14px;
        background-color: #FFFFFF;
        page-break-inside: avoid;
    }
    .section-header {
        background-color: #0B2265;
        color: #FFFFFF;
        padding: 6px 10px;
        font-weight: bold;
        font-size: 10.5px;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
    }
    .report-table th {
        padding: 6px 5px;
        font-size: 9.5px;
        font-weight: bold;
        text-align: center;
        border: 1px solid #CBD5E1;
    }
    .report-table td {
        padding: 5px;
        font-size: 9.5px;
        border: 1px solid #E2E8F0;
        vertical-align: middle;
    }

    .th-yellow { background-color: #F59E0B; color: #FFFFFF; }
    .th-navy { background-color: #0B2265; color: #FFFFFF; text-align: left !important; }
    .th-green { background-color: #16A34A; color: #FFFFFF; }
    .th-red { background-color: #DC2626; color: #FFFFFF; }
    .th-gray { background-color: #64748B; color: #FFFFFF; }

    /* Checklist Section */
    .checklist-section {
        margin-top: 10px;
    }
    .checklist-title-divider {
        text-align: center;
        margin: 10px 0 12px 0;
        font-size: 13px;
        font-weight: bold;
        color: #0B2265;
        page-break-inside: avoid;
    }
    .checklist-title-divider span {
        color: #F59E0B;
    }

    .category-container {
        margin-bottom: 14px;
        page-break-inside: avoid;
    }
    .category-header-table {
        width: 100%;
        border-collapse: collapse;
        border: none;
        margin-bottom: 4px;
    }
    .category-header-table td {
        border: none;
        padding: 0;
        vertical-align: middle;
    }
    .category-icon-badge {
        display: inline-block;
        width: 26px;
        height: 26px;
        background-color: #0B2265;
        border-radius: 50%;
        text-align: center;
        vertical-align: middle;
    }
    .category-icon-badge img {
        margin-top: 2px;
    }
    .category-title-text {
        font-size: 11.5px;
        font-weight: bold;
        color: #0B2265;
        padding-left: 6px;
    }

    /* Severity Text Styles */
    .sev-low { color: #16A34A; font-weight: bold; }
    .sev-medium { color: #EA580C; font-weight: bold; }
    .sev-high { color: #DC2626; font-weight: bold; }
    .sev-critical { color: #991B1B; font-weight: bold; }
    .sev-default { color: #0B2265; font-weight: bold; }

    .check-yes { color: #16A34A; font-weight: bold; font-size: 13px; }
    .check-no { color: #DC2626; font-weight: bold; font-size: 13px; }
    .check-na { color: #64748B; font-weight: bold; font-size: 13px; }
</style>
</head>
<body>

<!-- Dynamic Page Counter for Dompdf -->
<script type="text/php">
if (isset($pdf)) {
    $font = $fontMetrics->getFont("DejaVu Sans", "bold");
    $pdf->page_text(505, 816, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 8.5, array(0.04, 0.13, 0.40));
}
</script>

<!-- Fixed Footer Bar (Table based) -->
<div class="page-footer">
    <table class="footer-table">
        <tr>
            <td style="width: 33%; text-align: left; color: #FFFFFF;">
                Safety First, Work Safely
            </td>
            <td style="width: 37%; text-align: center; color: #FFFFFF;">
                Prepared By: <?= htmlspecialchars(isset($details['user_name']) ? $details['user_name'] : '') ?>
            </td>
            <td style="width: 30%; text-align: right; background-color: #F59E0B; color: #0B2265;">
                <!-- Dompdf injects Page X of Y here -->
            </td>
        </tr>
    </table>
</div>

<!-- Main Top Header -->
<table class="header-table">
    <tr>
        <td class="header-logo-cell">
            <table style="border:none; border-collapse:collapse;">
                <tr>
                    <td style="border:none; padding:0; vertical-align:middle; width:38px;">
                        <img src="<?= $svg_shield ?>" width="34" height="34" alt="LMRA Logo">
                    </td>
                    <td style="border:none; padding:0 0 0 4px; vertical-align:middle;">
                        <div style="font-size: 17px; font-weight: bold; color: #0B2265; line-height: 1;">LMRA</div>
                        <div style="font-size: 7px; color: #64748B; font-weight: bold; letter-spacing: 0.2px;">LAST MINUTE RISK ASSESSMENT</div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="header-title-cell">
            <div style="font-size: 13.5px; font-weight: bold; color: #0B2265;">
                BASIC DETAILS OF <span style="color: #F59E0B; border-bottom: 2px solid #F59E0B; padding-bottom: 1px;"><?= htmlspecialchars(isset($details['user_name']) ? $details['user_name'] : '') ?></span>
            </div>
        </td>
        <td class="header-meta-cell">
            <div class="meta-box">
                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                    <tr>
                        <td style="width: 16px; border: none; padding: 2px 0; vertical-align: middle;">
                            <img src="<?= $svg_clipboard ?>" width="11" height="11" style="display: block;">
                        </td>
                        <td style="width: 58px; font-weight: bold; color: #475569; border: none; padding: 2px 0; vertical-align: middle; font-size: 9.5px; white-space: nowrap;">
                            LMRA ID
                        </td>
                        <td style="width: 8px; font-weight: bold; color: #475569; border: none; padding: 2px 0; vertical-align: middle; font-size: 9.5px; text-align: center;">
                            :
                        </td>
                        <td style="border: none; padding: 2px 0 2px 4px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                            <?= htmlspecialchars(isset($details['lmra_details_id']) ? $details['lmra_details_id'] : '') ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 16px; border: none; padding: 2px 0; vertical-align: middle;">
                            <img src="<?= $svg_calendar ?>" width="11" height="11" style="display: block;">
                        </td>
                        <td style="width: 58px; font-weight: bold; color: #475569; border: none; padding: 2px 0; vertical-align: middle; font-size: 9.5px; white-space: nowrap;">
                            Visit Date
                        </td>
                        <td style="width: 8px; font-weight: bold; color: #475569; border: none; padding: 2px 0; vertical-align: middle; font-size: 9.5px; text-align: center;">
                            :
                        </td>
                        <td style="border: none; padding: 2px 0 2px 4px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                            <?= htmlspecialchars(isset($details['lmra_visit_date']) ? $details['lmra_visit_date'] . ' ' . (isset($details['lmra_visit_time']) ? $details['lmra_visit_time'] : '') : '') ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 16px; border: none; padding: 2px 0; vertical-align: middle;">
                            <img src="<?= $svg_clock ?>" width="11" height="11" style="display: block;">
                        </td>
                        <td style="width: 58px; font-weight: bold; color: #475569; border: none; padding: 2px 0; vertical-align: middle; font-size: 9.5px; white-space: nowrap;">
                            Status
                        </td>
                        <td style="width: 8px; font-weight: bold; color: #475569; border: none; padding: 2px 0; vertical-align: middle; font-size: 9.5px; text-align: center;">
                            :
                        </td>
                        <td style="border: none; padding: 2px 0 2px 4px; vertical-align: middle;">
                            <span class="status-badge" style="background-color: <?= $status_info['bg'] ?>; color: <?= $status_info['color'] ?>;">
                                <?= htmlspecialchars($status_info['label']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

<div class="yellow-divider"></div>

<!-- Basic Details Side-by-Side Cards (Grid Layout Table) -->
<table class="grid-layout-table">
    <tbody>
        <tr>
            <td style="width: 49%;">
                <!-- Card 1: Reporting Location -->
                <div class="card-box">
                    <div class="card-header">
                        <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                            <tr>
                                <td style="width: 18px; border: none; padding: 0; vertical-align: middle;">
                                    <img src="<?= $svg_building ?>" width="12" height="12" style="display: block;">
                                </td>
                                <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-size: 10.5px; font-weight: bold; color: #FFFFFF; letter-spacing: 0.5px;">
                                    REPORTING LOCATION
                                </td>
                            </tr>
                        </table>
                    </div>
                    <table class="details-table">
                        <tr>
                            <th>
                                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                                    <tr>
                                        <td style="width: 16px; border: none; padding: 0; vertical-align: middle;">
                                            <img src="<?= $svg_building ?>" width="11" height="11" style="display: block;">
                                        </td>
                                        <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                                            Project Name
                                        </td>
                                    </tr>
                                </table>
                            </th>
                            <td><?= htmlspecialchars(isset($details['lmra_project_name']) ? $details['lmra_project_name'] : '') ?></td>
                        </tr>
                        <tr>
                            <th>
                                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                                    <tr>
                                        <td style="width: 16px; border: none; padding: 0; vertical-align: middle;">
                                            <img src="<?= $svg_clipboard ?>" width="11" height="11" style="display: block;">
                                        </td>
                                        <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                                            Job No.
                                        </td>
                                    </tr>
                                </table>
                            </th>
                            <td><?= htmlspecialchars(isset($details['lmra_job_no']) ? $details['lmra_job_no'] : '') ?></td>
                        </tr>
                        <tr>
                            <th>
                                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                                    <tr>
                                        <td style="width: 16px; border: none; padding: 0; vertical-align: middle;">
                                            <img src="<?= $svg_pin ?>" width="11" height="11" style="display: block;">
                                        </td>
                                        <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                                            Location
                                        </td>
                                    </tr>
                                </table>
                            </th>
                            <td><?= htmlspecialchars(isset($details['lmra_location']) ? $details['lmra_location'] : '') ?></td>
                        </tr>
                        <tr>
                            <th>
                                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                                    <tr>
                                        <td style="width: 16px; border: none; padding: 0; vertical-align: middle;">
                                            <img src="<?= $svg_calendar ?>" width="11" height="11" style="display: block;">
                                        </td>
                                        <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                                            Visit Date
                                        </td>
                                    </tr>
                                </table>
                            </th>
                            <td><?= htmlspecialchars(isset($details['lmra_visit_date']) ? $details['lmra_visit_date'] . ' ' . (isset($details['lmra_visit_time']) ? $details['lmra_visit_time'] : '') : '') ?></td>
                        </tr>
                        <tr>
                            <th>
                                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                                    <tr>
                                        <td style="width: 16px; border: none; padding: 0; vertical-align: middle;">
                                            <img src="<?= $svg_clock ?>" width="11" height="11" style="display: block;">
                                        </td>
                                        <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                                            Status
                                        </td>
                                    </tr>
                                </table>
                            </th>
                            <td>
                                <span class="status-badge" style="background-color: <?= $status_info['bg'] ?>; color: <?= $status_info['color'] ?>;">
                                    <?= htmlspecialchars($status_info['label']) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="width: 2%;"></td>
            <td style="width: 49%;">
                <!-- Card 2: Reporter Details -->
                <div class="card-box">
                    <div class="card-header">
                        <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                            <tr>
                                <td style="width: 18px; border: none; padding: 0; vertical-align: middle;">
                                    <img src="<?= $svg_user ?>" width="12" height="12" style="display: block;">
                                </td>
                                <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-size: 10.5px; font-weight: bold; color: #FFFFFF; letter-spacing: 0.5px;">
                                    REPORTER DETAILS
                                </td>
                            </tr>
                        </table>
                    </div>
                    <table class="details-table">
                        <tr>
                            <th>
                                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                                    <tr>
                                        <td style="width: 16px; border: none; padding: 0; vertical-align: middle;">
                                            <img src="<?= $svg_user ?>" width="11" height="11" style="display: block;">
                                        </td>
                                        <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                                            User Name
                                        </td>
                                    </tr>
                                </table>
                            </th>
                            <td><?= htmlspecialchars(isset($details['user_name']) ? $details['user_name'] : '') ?></td>
                        </tr>
                        <tr>
                            <th>
                                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                                    <tr>
                                        <td style="width: 16px; border: none; padding: 0; vertical-align: middle;">
                                            <img src="<?= $svg_user_check ?>" width="11" height="11" style="display: block;">
                                        </td>
                                        <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                                            Assessment For
                                        </td>
                                    </tr>
                                </table>
                            </th>
                            <td><?= (isset($details['self_other']) && $details['self_other'] == '1') ? 'Other Engineer' : 'Self' ?></td>
                        </tr>
                        <?php if (isset($details['self_other']) && $details['self_other'] == '1'): ?>
                        <tr>
                            <th>
                                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                                    <tr>
                                        <td style="width: 16px; border: none; padding: 0; vertical-align: middle;">
                                            <img src="<?= $svg_user ?>" width="11" height="11" style="display: block;">
                                        </td>
                                        <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                                            Engineer Name
                                        </td>
                                    </tr>
                                </table>
                            </th>
                            <td><?= htmlspecialchars(isset($details['other_engineer_name']) ? $details['other_engineer_name'] : '') ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>
                                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                                    <tr>
                                        <td style="width: 16px; border: none; padding: 0; vertical-align: middle;">
                                            <img src="<?= $svg_mail ?>" width="11" height="11" style="display: block;">
                                        </td>
                                        <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                                            Email Id
                                        </td>
                                    </tr>
                                </table>
                            </th>
                            <td><?= htmlspecialchars(isset($details['user_email']) ? $details['user_email'] : '') ?></td>
                        </tr>
                        <tr>
                            <th>
                                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                                    <tr>
                                        <td style="width: 16px; border: none; padding: 0; vertical-align: middle;">
                                            <img src="<?= $svg_phone ?>" width="11" height="11" style="display: block;">
                                        </td>
                                        <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                                            Contact No.
                                        </td>
                                    </tr>
                                </table>
                            </th>
                            <td><?= htmlspecialchars(isset($details['user_contact']) ? $details['user_contact'] : '') ?></td>
                        </tr>
                        <tr>
                            <th>
                                <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
                                    <tr>
                                        <td style="width: 16px; border: none; padding: 0; vertical-align: middle;">
                                            <img src="<?= $svg_briefcase ?>" width="11" height="11" style="display: block;">
                                        </td>
                                        <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-weight: bold; color: #1E293B; font-size: 9.5px;">
                                            Designation
                                        </td>
                                    </tr>
                                </table>
                            </th>
                            <td><?= htmlspecialchars(isset($details['user_designation']) ? $details['user_designation'] : '') ?></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </tbody>
</table>

<!-- Rejection Comment Card (Only if non-empty) -->
<?php if (!empty($details['rejection_comment'])): ?>
<div class="rejection-card">
    <div class="rejection-header">
        <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
            <tr>
                <td style="width: 18px; border: none; padding: 0; vertical-align: middle;">
                    <img src="<?= $svg_comment ?>" width="12" height="12" style="display: block;">
                </td>
                <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-size: 10.5px; font-weight: bold; color: #FFFFFF;">
                    REJECTION COMMENT
                </td>
            </tr>
        </table>
    </div>
    <div class="rejection-body">
        <?= nl2br(htmlspecialchars($details['rejection_comment'])) ?>
    </div>
</div>
<?php endif; ?>

<!-- Quotations / Basic Safety Questions -->
<div class="section-card">
    <div class="section-header">
        <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed;">
            <tr>
                <td style="width: 18px; border: none; padding: 0; vertical-align: middle;">
                    <img src="<?= $svg_questions ?>" width="12" height="12" style="display: block;">
                </td>
                <td style="border: none; padding: 0 0 0 2px; vertical-align: middle; font-size: 10.5px; font-weight: bold; color: #FFFFFF;">
                    QUOTATIONS / BASIC SAFETY QUESTIONS
                </td>
            </tr>
        </table>
    </div>
    <table class="report-table">
        <thead>
            <tr>
                <th class="th-yellow" style="width: 6%;">#</th>
                <th class="th-navy" style="width: 68%;">Quotations / Safety Question</th>
                <th class="th-green" style="width: 13%;">Yes</th>
                <th class="th-red" style="width: 13%;">No</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $qIndex = 1;
            foreach ($quotations as $key => $qText):
                $isYes = isset($sta_data[$key]) && ($sta_data[$key] === true || $sta_data[$key] === 'true' || $sta_data[$key] === 1 || $sta_data[$key] === '1');
                $isNo  = isset($sta_data[$key]) && ($sta_data[$key] === false || $sta_data[$key] === 'false' || $sta_data[$key] === 0 || $sta_data[$key] === '0');
            ?>
            <tr>
                <td style="text-align: center; font-weight: bold; background-color: #FEF3C7; color: #D97706; border-color: #FCD34D;"><?= $qIndex++ ?></td>
                <td style="text-align: left; padding-left: 8px; color: #1E293B;"><?= htmlspecialchars($qText) ?></td>
                <td style="text-align: center;">
                    <?php if ($isYes): ?>
                        <span class="check-yes">☑</span>
                    <?php else: ?>
                        <span style="color:#CBD5E1;">☐</span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <?php if ($isNo): ?>
                        <span class="check-no">☑</span>
                    <?php else: ?>
                        <span style="color:#CBD5E1;">☐</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Checklist Section -->
<div class="checklist-section">
    <!-- LMRA Checklist Section Header -->
    <div class="checklist-title-divider">
        <span>●─────</span> LMRA CHECK LIST <span>─────●</span>
    </div>

    <!-- Dynamic LMRA Checklist Categories -->
    <?php if (!empty($lmra_json_data) && is_array($lmra_json_data)): ?>
        <?php foreach ($lmra_json_data as $header_row): ?>
            <?php 
                $category_name = '';
                if (!empty($header_row['header']) && !is_numeric($header_row['header'])) {
                    $category_name = $header_row['header'];
                } else if (!empty($header_row['title_0_header']) && !is_numeric($header_row['title_0_header'])) {
                    $category_name = $header_row['title_0_header'];
                } else if (!empty($header_row['title_1_header'])) {
                    $category_name = $header_row['title_1_header'];
                } else {
                    $category_name = 'SAFETY CHECKLIST';
                }

                $column_1_title = isset($header_row['title_1_header']) ? $header_row['title_1_header'] : 'Activity / Question';
                if (strcasecmp(trim($column_1_title), trim($category_name)) === 0) {
                    $column_1_title = 'Activity / Question';
                }

                $category_icon = getCategoryIconSvg($category_name);
            ?>
            <div class="category-container">
                <table class="category-header-table" style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed; margin-bottom: 4px;">
                    <tr>
                        <td style="width: 28px; vertical-align: middle; border: none; padding: 0;">
                            <table style="width: 24px; height: 24px; border-collapse: collapse; border: none; background-color: #0B2265; border-radius: 50%; margin: 0;">
                                <tr>
                                    <td style="text-align: center; vertical-align: middle; border: none; padding: 0;">
                                        <img src="<?= $category_icon ?>" width="13" height="13" style="display: block; margin: 0 auto;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="vertical-align: middle; border: none; padding: 0 0 0 6px;">
                            <span class="category-title-text" style="font-size: 11.5px; font-weight: bold; color: #0B2265;"><?= htmlspecialchars(strtoupper($category_name)) ?></span>
                        </td>
                    </tr>
                </table>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th class="th-yellow" style="width: 5%;">#</th>
                            <th class="th-navy" style="width: 53%;"><?= htmlspecialchars($column_1_title) ?></th>
                            <th class="th-green" style="width: 9%;"><?= htmlspecialchars(isset($header_row['title_2_header']) ? $header_row['title_2_header'] : 'Yes') ?></th>
                            <th class="th-red" style="width: 9%;"><?= htmlspecialchars(isset($header_row['title_3_header']) ? $header_row['title_3_header'] : 'No') ?></th>
                            <th class="th-gray" style="width: 9%;"><?= htmlspecialchars(isset($header_row['title_4_header']) ? $header_row['title_4_header'] : 'NA') ?></th>
                            <th class="th-navy" style="width: 15%; text-align: center !important;"><?= htmlspecialchars(isset($header_row['title_5_header']) ? $header_row['title_5_header'] : 'Severity') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $index = 1;
                        if (!empty($header_row['lmrsFooterForms']) && is_array($header_row['lmrsFooterForms'])):
                            foreach ($header_row['lmrsFooterForms'] as $footer_row):
                                $cb1 = !empty($footer_row['cb_1_footer']);
                                $cb2 = !empty($footer_row['cb_2_footer']);
                                $cb3 = !empty($footer_row['cb_3_footer']);
                                $severity_text = isset($footer_row['selected_string_3']) ? trim($footer_row['selected_string_3']) : '';
                                
                                $sev_class = 'sev-default';
                                $sev_lower = strtolower($severity_text);
                                if (strpos($sev_lower, 'low') !== false) $sev_class = 'sev-low';
                                elseif (strpos($sev_lower, 'med') !== false) $sev_class = 'sev-medium';
                                elseif (strpos($sev_lower, 'high') !== false) $sev_class = 'sev-high';
                                elseif (strpos($sev_lower, 'crit') !== false) $sev_class = 'sev-critical';
                        ?>
                        <tr>
                            <td style="text-align: center; font-weight: bold; background-color: #FEF3C7; color: #D97706; border-color: #FCD34D;"><?= $index++ ?></td>
                            <td style="text-align: left; padding-left: 7px; color: #1E293B;"><?= htmlspecialchars(isset($footer_row['title_1_footer']) ? $footer_row['title_1_footer'] : '') ?></td>
                            <td style="text-align: center;">
                                <?php if ($cb1): ?>
                                    <span class="check-yes">☑</span>
                                <?php else: ?>
                                    <span style="color:#CBD5E1;">☐</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($cb2): ?>
                                    <span class="check-no">☑</span>
                                <?php else: ?>
                                    <span style="color:#CBD5E1;">☐</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($cb3): ?>
                                    <span class="check-na">☑</span>
                                <?php else: ?>
                                    <span style="color:#CBD5E1;">☐</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;" class="<?= $sev_class ?>">
                                <?= htmlspecialchars($severity_text) ?>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modern Timeline Chat Section (Strict Dompdf Table Layout for Avatars & Date/Time Icons) -->
<?php if (!empty($lmra_comments) && is_array($lmra_comments)): ?>
<div style="margin-top: 14px; page-break-inside: avoid;">
    <!-- Section Header -->
    <table style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 4px;">
        <tr>
            <td style="width: 28px; vertical-align: middle; border: none; padding: 0;">
                <span class="category-icon-badge">
                    <img src="<?= $svg_comment ?>" width="16" height="16">
                </span>
            </td>
            <td style="border: none; padding: 0 0 0 6px; vertical-align: middle;">
                <div style="font-size: 13px; font-weight: bold; color: #0B2265; line-height: 1.1;">LMRA COMMENTS</div>
                <div style="font-size: 8.5px; color: #64748B; margin-top: 1px;">All comments and observations related to this LMRA are listed below in chronological order.</div>
            </td>
        </tr>
    </table>

    <!-- Comments Table with Numbered Timeline & Details -->
    <div style="border: 1px solid #CBD5E1; border-radius: 5px; overflow: hidden; background-color: #FFFFFF;">
        <table class="report-table" style="width: 100%; border-collapse: collapse; border: none;">
            <thead>
                <tr>
                    <th style="width: 6%; background-color: #0B2265; color: #FFFFFF; border: none; text-align: center;">#</th>
                    <th style="width: 74%; background-color: #0B2265; color: #FFFFFF; border: none; text-align: left !important; padding-left: 10px;">Comment Details</th>
                    <th style="width: 20%; background-color: #0B2265; color: #FFFFFF; border: none; text-align: center;">Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $cIdx = 1;
                $total_comments = count($lmra_comments);
                foreach ($lmra_comments as $comment_row): 
                    $userName = isset($comment_row['user_name']) ? $comment_row['user_name'] : 'User';
                    $userDesig = isset($comment_row['user_designation']) ? $comment_row['user_designation'] : '';
                    $initials = getUserInitials($userName);
                    $theme = getRoleTheme($userDesig);
                    
                    // Split date and time if available
                    $datetime = isset($comment_row['default_date']) ? $comment_row['default_date'] : '';
                    $dtParts = explode(' ', trim($datetime));
                    $cDate = isset($dtParts[0]) ? $dtParts[0] : $datetime;
                    $cTime = isset($dtParts[1]) ? $dtParts[1] : '';
                ?>
                <tr style="border-bottom: 1px solid #E2E8F0;">
                    <!-- Column 1: Numbered Timeline Badge (Strict HTML Table for Perfect Centering) -->
                    <td style="text-align: center; vertical-align: middle; background-color: #FFFFFF; border-right: 1px solid #E2E8F0; padding: 6px;">
                        <table style="width: 20px; height: 20px; border-collapse: collapse; border: none; margin: 0 auto; background-color: #0B2265; border-radius: 50%;">
                            <tr>
                                <td style="text-align: center; vertical-align: middle; font-weight: bold; font-size: 9.5px; color: #FFFFFF; border: none; padding: 0;">
                                    <?= $cIdx++ ?>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <!-- Column 2: Avatar Circle (Strict HTML Table for Centered Text), User Details & Tinted Box -->
                    <td style="text-align: left; padding: 7px 10px; background-color: #FFFFFF; border-right: 1px solid #E2E8F0;">
                        <table style="width: 100%; border-collapse: collapse; border: none;">
                            <tr>
                                <td style="width: 30px; vertical-align: top; border: none; padding: 0;">
                                    <table style="width: 26px; height: 26px; border-collapse: collapse; border: none; margin: 0; padding: 0; background-color: <?= $theme['avatar_bg'] ?>; border-radius: 50%;">
                                        <tr>
                                            <td style="text-align: center; vertical-align: middle; font-weight: bold; font-size: 10px; color: <?= $theme['avatar_color'] ?>; border: none; padding: 0;">
                                                <?= htmlspecialchars($initials) ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="border: none; padding: 0 0 0 8px; vertical-align: top;">
                                    <div style="margin-bottom: 3px;">
                                        <span style="font-weight: bold; color: #0B2265; font-size: 10px;"><?= htmlspecialchars($userName) ?></span>
                                        <?php if (!empty($userDesig)): ?>
                                            <span style="font-weight: normal; color: #64748B; font-size: 9px;">(<?= htmlspecialchars($userDesig) ?>)</span>
                                            <span style="display: inline-block; padding: 1px 6px; border-radius: 6px; font-size: 8px; font-weight: bold; margin-left: 3px; background-color: <?= $theme['badge_bg'] ?>; color: <?= $theme['badge_color'] ?>;">
                                                <?= htmlspecialchars($userDesig) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="border-radius: 5px; padding: 5px 9px; font-size: 9.5px; color: #1E293B; line-height: 1.4; background-color: <?= $theme['box_bg'] ?>; border: 1px solid <?= $theme['box_border'] ?>;">
                                        <?php if (isset($comment_row['comment_type']) && $comment_row['comment_type'] == 1): ?>
                                            <?php
                                                $p = parse_url($comment_row['comment_text']);
                                                $tem = isset($p['path']) ? $p['path'] : $comment_row['comment_text'];
                                                echo '<img src="..' . htmlspecialchars($tem) . '" height="60" style="border:1px solid #CBD5E1; border-radius:3px; padding:2px; margin-top:2px;">';
                                            ?>
                                        <?php else: ?>
                                            <?= nl2br(htmlspecialchars(isset($comment_row['comment_text']) ? $comment_row['comment_text'] : '')) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <!-- Column 3: Date & Time with Icons (Strict HTML Table Layout to prevent icon text overlap) -->
                    <td style="text-align: center; vertical-align: middle; background-color: #FFFFFF; padding: 6px;">
                        <table style="width: 100%; border-collapse: collapse; border: none; margin: 0 auto;">
                            <tr>
                                <td style="width: 14px; text-align: right; vertical-align: middle; border: none; padding: 1px 3px 1px 0;">
                                    <img src="<?= $svg_calendar ?>" width="11" height="11">
                                </td>
                                <td style="text-align: left; vertical-align: middle; border: none; padding: 1px 0; font-size: 9px; color: #475569; white-space: nowrap;">
                                    <?= htmlspecialchars($cDate) ?>
                                </td>
                            </tr>
                            <?php if (!empty($cTime)): ?>
                            <tr>
                                <td style="width: 14px; text-align: right; vertical-align: middle; border: none; padding: 1px 3px 1px 0;">
                                    <img src="<?= $svg_clock ?>" width="11" height="11">
                                </td>
                                <td style="text-align: left; vertical-align: middle; border: none; padding: 1px 0; font-size: 9px; color: #475569; white-space: nowrap;">
                                    <?= htmlspecialchars($cTime) ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Total Comments Footer Card (Dashed Border) -->
    <div style="border: 1px dashed #94A3B8; border-radius: 5px; background-color: #F8FAFC; text-align: center; padding: 5px; margin-top: 6px;">
        <table style="margin: 0 auto; border-collapse: collapse; border: none;">
            <tr>
                <td style="border: none; padding: 0 4px 0 0; vertical-align: middle;">
                    <img src="<?= $svg_comment_dark ?>" width="11" height="11">
                </td>
                <td style="border: none; padding: 0; vertical-align: middle; font-weight: bold; color: #0B2265; font-size: 9.5px;">
                    Total Comments: <span style="background-color: #0B2265; color: #FFFFFF; border-radius: 50%; padding: 2px 6px; font-size: 9px;"><?= $total_comments ?></span>
                </td>
            </tr>
        </table>
    </div>
</div>
<?php endif; ?>

</body>
</html>