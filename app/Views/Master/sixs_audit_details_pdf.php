<?php
$details=$details[0];
$status_num = (int)$details['status'];
if ($status_num == 0) { $status = '<span class="status-badge" style="background-color: #ffc107; color: black;">Pending</span>'; }
elseif ($status_num == 1) { $status = '<span class="status-badge" style="background-color: #17a2b8; color: white;">Verify</span>'; }
elseif ($status_num == 2) { $status = '<span class="status-badge" style="background-color: #007bff; color: white;">Inprogress</span>'; }
elseif ($status_num == 3) { $status = '<span class="status-badge" style="background-color: #28a745; color: white;">Close</span>'; }
elseif ($status_num == 4) { $status = '<span class="status-badge" style="background-color: #dc3545; color: white;">Reject</span>'; }
else { $status = '<span class="status-badge" style="background-color: #6c757d; color: white;">Unknown</span>'; }

// Convert NC Photo to Base64 if needed
$ncPhotoBase64 = '';
if (!empty($details['nc_photo'])) {
    $imageUrl = base_url($details['nc_photo']);
    $context = stream_context_create(['http' => ['ignore_errors' => true], 'https' => ['ignore_errors' => true]]);
    $imageInfo = @getimagesize($imageUrl);
    if ($imageInfo) {
        $imageData = file_get_contents($imageUrl, false, $context);
        if ($imageData) {
            $ncPhotoBase64 = 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode($imageData);
        }
    }
}

// Convert After Photo to Base64 if needed
$afterPhotoBase64 = '';
if (!empty($details['after_photo'])) {
    $imageUrl = base_url($details['after_photo']);
    $context = stream_context_create(['http' => ['ignore_errors' => true], 'https' => ['ignore_errors' => true]]);
    $imageInfo = @getimagesize($imageUrl);
    if ($imageInfo) {
        $imageData = file_get_contents($imageUrl, false, $context);
        if ($imageData) {
            $afterPhotoBase64 = 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode($imageData);
        }
    }
}

// Load SVG Icons for DomPDF inline
function get_svg_inline($path, $color) {
    if(file_exists($path)) {
        $svg = file_get_contents($path);
        // Replace colors
        $svg = str_replace(['currentColor', '#000000'], $color, $svg);
        // Force dimensions
        $svg = preg_replace('/width="[^"]+"/', 'width="14"', $svg, 1);
        $svg = preg_replace('/height="[^"]+"/', 'height="14"', $svg, 1);
        return $svg;
    }
    return '';
}
$icon_chat = get_svg_inline(FCPATH . 'assets/media/svg/icons/Communication/Chat6.svg', '#0f204b');
$icon_cal = get_svg_inline(FCPATH . 'assets/media/svg/icons/General/calendar.svg', '#666666');
$icon_clock = get_svg_inline(FCPATH . 'assets/media/svg/icons/Home/Clock.svg', '#666666');
?>
<!DOCTYPE html>
<html>
<head>
<style>
    @page { margin: 30px; }
    body {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        color: #333;
        margin: 0;
        padding: 0;
    }
    .header-table {
        width: 100%;
        border-bottom: 3px solid #ffb822;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    .header-table td { vertical-align: top; }
    .header-title {
        color: #0f204b;
        font-size: 28px;
        font-weight: bold;
        margin: 0;
        letter-spacing: 1px;
    }
    .header-subtitle {
        color: #666;
        font-size: 11px;
        margin-top: 3px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .header-info-table {
        width: 100%;
        background-color: #f9f9fc;
        border-radius: 8px;
        padding: 10px;
        border: 2px solid #e1e3ea;
    }
    .header-info-table td {
        padding: 5px 8px;
        font-size: 13px;
        vertical-align: middle;
    }
    .header-info-label {
        font-weight: bold;
        color: #0f204b;
        width: 40%;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        font-size: 12px;
        font-weight: bold;
        border-radius: 6px;
        text-align: center;
    }
    .section-wrapper {
        width: 100%;
        margin-bottom: 20px;
    }
    .panel {
        border: 1px solid #dce0e5;
        border-radius: 8px;
        overflow: hidden;
    }
    .panel-left { width: 48%; float: left; }
    .panel-right { width: 48%; float: right; }
    .panel-header {
        background-color: #0f204b;
        color: white;
        padding: 10px 15px;
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .panel-table {
        width: 100%;
        border-collapse: collapse;
    }
    .panel-table td {
        padding: 10px 15px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 13px;
        vertical-align: middle;
    }
    .panel-table tr:last-child td { border-bottom: none; }
    .panel-table .label-cell {
        width: 35%;
        font-weight: bold;
        color: #444;
    }
    .timeline-table {
        width: 100%;
        border-collapse: collapse;
    }
    .timeline-header {
        background-color: #0f204b;
        color: white;
        font-size: 12px;
        font-weight: bold;
    }
    .timeline-header th {
        padding: 10px 15px;
        text-align: left;
        text-transform: uppercase;
    }
    .timeline-row td {
        border-bottom: 1px solid #f0f0f0;
        vertical-align: top;
    }
    .timeline-col-num {
        width: 60px;
        text-align: center;
        padding: 0;
        vertical-align: top;
        position: relative;
    }
    .timeline-line {
        position: absolute;
        left: 29px;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e1e3ea;
        z-index: 1;
    }
    .circle-icon {
        width: 28px;
        height: 28px;
        background-color: #0f204b;
        color: white;
        border-radius: 14px;
        text-align: center;
        line-height: 28px;
        font-size: 12px;
        font-weight: bold;
        display: block;
        margin: 0 auto;
        margin-top: 15px;
        position: relative;
        z-index: 2;
    }
    .user-initials {
        width: 32px;
        height: 32px;
        background-color: #e6f2ff;
        color: #0066cc;
        border-radius: 16px;
        text-align: center;
        line-height: 32px;
        font-size: 13px;
        font-weight: bold;
        display: inline-block;
        margin-right: 12px;
    }
    .role-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 4px;
        margin-left: 8px;
        font-weight: bold;
    }
    .role-administrator { background-color: #e6f2ff; color: #0066cc; }
    .role-auditor { background-color: #e6f9ec; color: #28a745; }
    .role-hse { background-color: #fff3cd; color: #ffc107; }
    .role-manager { background-color: #f3e6ff; color: #6f42c1; }
    .role-default { background-color: #f0f0f0; color: #555; }
    
    .comment-meta { color: #666; font-size: 12px; }
    .comment-meta div { margin-bottom: 4px; }
    .footer {
        position: fixed;
        bottom: -20px;
        left: -30px;
        right: -30px;
        height: 50px;
        background-color: #0f204b;
        color: white;
    }
    .footer-table {
        width: 100%;
        border-collapse: collapse;
        height: 100%;
    }
    .footer-table td {
        padding: 0 30px;
        vertical-align: middle;
        font-size: 12px;
    }
    .photo-thumb { max-height: 70px; max-width: 120px; border: 1px solid #ddd; border-radius: 4px; }
</style>
</head>
<body>

<!-- HEADER -->
<table class="header-table">
    <tr>
        <td style="width: 60%;">
            <h1 class="header-title">6S AUDIT <span style="color:#ffb822;">REPORT</span></h1>
            <div class="header-subtitle">Workplace Organization & Safety Excellence</div>
        </td>
        <td style="width: 40%;">
            <table class="header-info-table">
                <tr>
                    <td class="header-info-label">Audit ID</td>
                    <td>: <?=$details['audit_id']?></td>
                </tr>
                <tr>
                    <td class="header-info-label">Audit Date</td>
                    <td>: <?=$details['default_date']?></td>
                </tr>
                <tr>
                    <td class="header-info-label">Status</td>
                    <td>: <?=$status?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table style="width: 100%; border: none; padding: 0; margin-bottom: 20px;">
    <tr>
        <td style="width: 48%; vertical-align: top; padding: 0;">
            <!-- LEFT PANEL: REPORTING LOCATION -->
            <div class="panel">
                <div class="panel-header">Reporting Location</div>
                <table class="panel-table">
            <tr>
                <td class="label-cell">Location</td>
                <td>: <?=$details['location']?></td>
            </tr>
            <tr>
                <td class="label-cell">NC Description</td>
                <td>: <?=$details['nc_description']?></td>
            </tr>
            <tr>
                <td class="label-cell">NC Photo</td>
                <td>: 
                    <?php if($ncPhotoBase64): ?>
                        <img src="<?=$ncPhotoBase64?>" class="photo-thumb">
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="label-cell">Action Req. Dep</td>
                <td>: <?=$details['action_required_dep']?></td>
            </tr>
            <tr>
                <td class="label-cell">Div Dep</td>
                <td>: <?=$details['devision_dept']?></td>
            </tr>
            <tr>
                <td class="label-cell">Responsibility</td>
                <td>: <?=$details['responsibilty']?></td>
            </tr>
            <tr>
                <td class="label-cell">Target Date</td>
                <td>: <?=$details['target_date']?></td>
            </tr>
            <tr>
                <td class="label-cell">Status</td>
                <td>: <?=$status?></td>
            </tr>
                </table>
            </div>
        </td>
        <td style="width: 4%;"></td> <!-- Spacer -->
        <td style="width: 48%; vertical-align: top; padding: 0;">
            <!-- RIGHT PANEL: REPORTER DETAILS -->
            <div class="panel">
                <div class="panel-header">Reporter Details</div>
        <?php
            $r_name = ($details['user_id'] == 0) ? "Super Admin" : $details['user_name'];
            $r_email = ($details['user_id'] == 0) ? "admin@fmlogistic.com" : $details['user_email'];
            $r_contact = ($details['user_id'] == 0) ? "-" : $details['user_contact'];
            $r_desig = ($details['user_id'] == 0) ? "Administrator" : $details['user_designation'];
        ?>
        <table class="panel-table">
            <tr>
                <td class="label-cell">Name</td>
                <td>: <?=$r_name?></td>
            </tr>
            <tr>
                <td class="label-cell">Email</td>
                <td>: <?=$r_email?></td>
            </tr>
            <tr>
                <td class="label-cell">Contact</td>
                <td>: <?=$r_contact?></td>
            </tr>
            <tr>
                <td class="label-cell">Sixs Category</td>
                <td>: <?=$details['sixs_category']?></td>
            </tr>
            <tr>
                <td class="label-cell">Details Of Ca Pa</td>
                <td>: <?=$details['details_of_ca_pa']?></td>
            </tr>
            <tr>
                <td class="label-cell">After Photo</td>
                <td>: 
                    <?php if($afterPhotoBase64): ?>
                        <img src="<?=$afterPhotoBase64?>" class="photo-thumb">
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="label-cell">Designation</td>
                <td>: <?=$r_desig?></td>
            </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

<!-- REJECTION COMMENT -->
<?php if(isset($details['rejection_comment']) && !empty($details['rejection_comment'])){ ?>
<div class="section-wrapper">
    <div class="panel">
        <div class="panel-header" style="background-color: #dc3545;">Rejection Comment</div>
        <table class="panel-table">
            <tr>
                <td><?=$details['rejection_comment']?></td>
            </tr>
        </table>
    </div>
</div>
<?php } ?>

<!-- 6S AUDIT COMMENTS -->
<?php if(!empty($sixs_comments)) { ?>
<div class="section-wrapper" style="margin-top: 15px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 200px; background-color: #0f204b; color: white; border-radius: 8px 8px 0 0; padding: 8px 20px; font-weight: bold; text-transform: uppercase; font-size: 13px;">
                <span style="display:inline-block; margin-right:8px; background:white; border-radius:10px; padding:2px 5px; vertical-align: middle;">
                    <?php if($icon_chat): ?><?=$icon_chat?><?php endif; ?>
                </span> 6S AUDIT COMMENTS
            </td>
            <td></td>
        </tr>
    </table>
    <div style="border: 2px solid #e1e3ea; border-radius: 0 8px 8px 8px; border-top: 2px solid #0f204b; overflow: hidden;">
    <table class="timeline-table">
        <tr class="timeline-header">
            <th style="width: 8%; text-align: center;">#</th>
            <th style="width: 70%;">COMMENT & USER</th>
            <th style="width: 22%;">DATE & TIME</th>
        </tr>
        <?php $count = 1; foreach($sixs_comments as $c){ 
            $c_name = ($c['user_id'] == 0) ? "Super Admin" : $c['user_name'];
            $c_desig = ($c['user_id'] == 0) ? "Administrator" : $c['user_designation'];
            
            // Extract initials
            $words = explode(" ", $c_name);
            $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
            
            // Format date
            $dateObj = new DateTime($c['default_date']);
            $formattedDate = $dateObj->format('Y-m-d');
            $formattedTime = $dateObj->format('h:i A');
            
            // Role Badge Class
            $role_class = 'role-default';
            $desig_lower = strtolower($c_desig);
            if(strpos($desig_lower, 'admin') !== false) $role_class = 'role-administrator';
            elseif(strpos($desig_lower, 'auditor') !== false) $role_class = 'role-auditor';
            elseif(strpos($desig_lower, 'hse') !== false) $role_class = 'role-hse';
            elseif(strpos($desig_lower, 'manager') !== false) $role_class = 'role-manager';
        ?>
        <tr class="timeline-row">
            <td class="timeline-col-num">
                <div class="timeline-line"></div>
                <div class="circle-icon"></div>
            </td>
            <td style="padding: 15px 15px 15px 15px;">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="width: 45px; border: none; padding: 0; vertical-align: top;">
                            <div class="user-initials"><?=$initials?></div>
                        </td>
                        <td style="border: none; padding: 0; padding-top: 5px;">
                            <strong style="color: #0f204b; font-size: 14px;"><?=$c_name?></strong> 
                            <span style="color:#666; font-size:12px;">(<?=$c_desig?>)</span>
                            <span class="role-badge <?=$role_class?>"><?=$c_desig?></span>
                            <div style="margin-top: 12px; color: #333; line-height: 1.5;">
                                <?php if($c['comment_type'] == 0) { ?>
                                    <?=$c['comment_text']?>
                                <?php } else { 
                                    $options = ['http' => ['ignore_errors' => true], 'https' => ['ignore_errors' => true]];
                                    $context = stream_context_create($options);
                                    $imageUrl = base_url($c['comment_text']);
                                    $imageBase64 = '';
                                    $imageInfo = @getimagesize($imageUrl);
                                    if ($imageInfo) {
                                        $imageData = file_get_contents($imageUrl, false, $context);
                                        if ($imageData) {
                                            $imageBase64 = 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode($imageData);
                                        }
                                    }
                                    if ($imageBase64) {
                                        echo '<img src="' . $imageBase64 . '" class="photo-thumb">';
                                    } else {
                                        echo 'Image attached (Cannot render in PDF)';
                                    }
                                } ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="comment-meta" style="padding: 15px;">
                <div>
                    <span style="display: inline-block; vertical-align: middle; margin-right: 4px;"><?php if($icon_cal): ?><?=$icon_cal?><?php endif; ?></span>
                    <?=$formattedDate?>
                </div>
                <div style="margin-top: 8px;">
                    <span style="display: inline-block; vertical-align: middle; margin-right: 4px;"><?php if($icon_clock): ?><?=$icon_clock?><?php endif; ?></span>
                    <?=$formattedTime?>
                </div>
            </td>
        </tr>
        <?php } ?>
    </table>
    </div>
</div>
<?php } ?>

<!-- FOOTER -->
<div class="footer">
    <table class="footer-table">
        <tr>
            <td style="width: 35%;">
                <strong>Safety First, Work Smart</strong><br>
                A Clean Workplace is a Safe Workplace
            </td>
            <td style="width: 30%; text-align: center;">
                Prepared By : <?=$r_name?>
            </td>
            <td style="width: 35%; background-color: #ffb822; color: #0f204b; font-weight: bold; text-align: right; border-radius: 40px 0 0 0;">
                <!-- Page numbers handled by script -->
            </td>
        </tr>
    </table>
</div>

<!-- PAGE NUMBER SCRIPT (Dompdf specific) -->
<script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $size = 11;
        $font = $fontMetrics->getFont("Helvetica", "bold");
        $width = $fontMetrics->get_text_width($text, $font, $size);
        $x = $pdf->get_width() - $width - 50;
        $y = $pdf->get_height() - 32;
        $pdf->page_text($x, $y, $text, $font, $size, array(0.06, 0.12, 0.29)); // color #0f204b
    }
</script>

</body>
</html>