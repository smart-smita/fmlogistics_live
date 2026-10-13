<?php
$details = $details['0'];

// Status Messages
$status_badge = '';
$status_color = '';
switch($details['status']) {
    case 0: $status_badge = 'PENDING'; $status_color = '#dc3545'; break;
    case 1: $status_badge = 'VERIFIED'; $status_color = '#28a745'; break;
    case 2: $status_badge = 'OPEN'; $status_color = '#ffc107'; break;
    case 3: $status_badge = 'CANCEL'; $status_color = '#6c757d'; break;
    case 4: $status_badge = 'REQUEST REQUIRED'; $status_color = '#ffc107'; break;
    case 5: $status_badge = 'CLOSE'; $status_color = '#28a745'; break;
}

// Priority
$priority_badge = (isset($details['near_miss_priority']) && $details['near_miss_priority'] == 1) ? 'HIGH' : 'LOW';
$priority_color = ($priority_badge == 'HIGH') ? '#dc3545' : '#ffc107';

// Future Occurrence JSON Decode
$near_miss_json_data = json_decode($details['future_occurrence'], true);

// Logo Path
$logo_path = FCPATH . 'assets/media/app-logo-3.jpeg';
$logo_base64 = '';
if(file_exists($logo_path)) {
    $type = pathinfo($logo_path, PATHINFO_EXTENSION);
    $data = file_get_contents($logo_path);
    $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { 
        margin: 0px;
        footer: page-footer;
    }
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        color: #333;
        margin: 0;
        padding: 0;
        font-size: 11px;
    }
    .page-content {
        padding: 20px 40px;
    }
    
    /* Header */
    .header-bar {
        background-color: #3e1e68;
        color: #ffffff;
        padding: 20px 40px;
    }
    .header-table { width: 100%; border: none; }
    .header-table td { border: none; padding: 0; vertical-align: middle; }
    .title {
        font-size: 24px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }
    
    /* Global Layout Tables */
    table { width: 100%; border-collapse: collapse; }
    
    /* Cards */
    .card {
        border: 1px solid #d1c4e9;
        border-radius: 8px;
        background-color: #fff;
        margin-top: 15px;
        overflow: hidden;
    }
    .card-title-bar {
        background-color: #f4f1fa;
        color: #3e1e68;
        padding: 10px 15px;
        font-weight: bold;
        font-size: 12px;
        border-bottom: 1px solid #d1c4e9;
    }
    .card-content {
        padding: 15px;
    }
    
    /* Two Column Info Grids */
    .info-table td { padding: 4px 0; vertical-align: top; }
    .info-label { font-weight: bold; color: #3e1e68; width: 38%; font-size: 11px; word-break: keep-all;}
    .info-colon { width: 4%; text-align: center; color: #3e1e68; font-weight:bold;}
    .info-value { width: 58%; color: #333; font-size: 11px; word-wrap: break-word; word-break: break-word;}
    
    /* Icons */
    .icon-circle {
        display: inline-block;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #4a148c;
        color: #fff;
        text-align: center;
        line-height: 20px;
        margin-right: 8px;
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10px;
    }
    
    /* Data Tables */
    .data-table { width: 100%; border-collapse: collapse; margin-top: 0; }
    .data-table th {
        background-color: #1f0954;
        color: #ffffff;
        padding: 10px;
        text-align: left;
        font-weight: bold;
        font-size: 11px;
    }
    .data-table td {
        padding: 10px;
        border: 1px solid #e5e7eb;
        border-top: none;
        word-wrap: break-word;
        word-break: break-word;
        max-width: 200px;
    }
    
    /* Custom Red Border Card for Priority/Status */
    .priority-card {
        border: 1px solid #ffcccc;
        border-radius: 8px;
        margin-top: 15px;
    }
    
    /* Footer */
    #page-footer {
        width: 100%;
        border-top: 1px solid #d1c4e9;
        padding-top: 10px;
        padding-left: 40px;
        padding-right: 40px;
        font-size: 10px;
        color: #666;
    }
</style>
</head>
<body>

<htmlpagefooter name="page-footer">
    <table width="100%" style="border-collapse: collapse; padding: 0 40px 10px 40px; border-top: 1px solid #d1c4e9; font-size: 10px; color: #666;">
        <tr>
            <td width="33%" style="text-align: left;">Near Miss Report | Report #<?=$details['near_miss_id']?></td>
            <td width="34%" style="text-align: center;">Confidential - FM Logistic</td>
            <td width="33%" style="text-align: right;">Page {PAGENO} of {nb}</td>
        </tr>
    </table>
</htmlpagefooter>

<div class="header-bar">
    <table class="header-table">
        <tr>
            <td style="width: 50%; vertical-align: middle;">
                <span class="title">NEAR MISS REPORT</span>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: middle;">
                <table style="width:100%;">
                    <tr>
                        <td style="text-align:right; width:90%; padding-right:15px;">
                            <div style="font-weight:bold; font-size:12px; color:#fff;">Report No. #<?=$details['near_miss_id']?></div>
                            <div style="font-size:9px; color:#d1c4e9; margin-top:3px;">Generated on : <?=date('d-M-Y h:i A')?></div>
                        </td>
                        <td style="width:10%; text-align:right;">
                            <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 6px; text-align:center; line-height:32px; color:#fff; font-size:14px; font-weight:bold; border: 1px solid rgba(255,255,255,0.4);">
                                NM
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="page-content">
    
    <table style="width: 100%;">
        <tr>
            <!-- Left Column: Reporting Location -->
            <td style="width: 49%; vertical-align: top;">
                <div class="card" style="min-height: 200px;">
                    <div class="card-title-bar">
                        <span style="display:inline-block; width:18px; height:18px; background:#3e1e68; border-radius:50%; color:#fff; text-align:center; line-height:18px; font-size:9px; font-weight:bold; margin-right:6px; vertical-align:middle;">L</span>
                        REPORTING LOCATION
                    </div>
                    <div class="card-content">
                        <table class="info-table">
                            <tr><td class="info-label">Project Name</td><td class="info-colon">:</td><td class="info-value"><?=$details['project_name']?></td></tr>
                            <tr><td class="info-label">Job No.</td><td class="info-colon">:</td><td class="info-value"><?=$details['job_no']?></td></tr>
                            <tr><td class="info-label">Location</td><td class="info-colon">:</td><td class="info-value"><?=$details['location']?></td></tr>
                            <tr><td class="info-label">GPS Location</td><td class="info-colon">:</td><td class="info-value"><?=$details['gps_location']?></td></tr>
                            <tr><td class="info-label">Exact Location</td><td class="info-colon">:</td><td class="info-value"><?=$details['manual_location']?></td></tr>
                            <tr><td class="info-label">Date</td><td class="info-colon">:</td><td class="info-value"><?=date('Y-m-d H:i:s', strtotime($details['near_miss_date']." ". $details['near_miss_time']))?></td></tr>
                        </table>
                    </div>
                </div>
            </td>
            
            <td style="width: 2%;"></td>
            
            <!-- Right Column: Priority & Reporter Details -->
            <td style="width: 49%; vertical-align: top;">
                <!-- Priority / Status Box -->
                <div class="priority-card" style="background-color: #fff9f9;">
                    <table style="width: 100%; text-align: center; padding: 15px 0;">
                        <tr>
                            <td style="width: 50%; border-right: 1px dashed #ffcccc; vertical-align: middle; padding: 12px;">
                                <div style="width: 40px; height: 40px; background: #ffc107; border-radius: 50%; margin: 0 auto; text-align:center; line-height:40px; color:#fff; font-weight:bold; font-size:16px;">!</div>
                                <div style="color:#1f0954; font-weight:bold; font-size:11px; margin-top:8px;">PRIORITY</div>
                                <div style="display:inline-block; background:<?=$priority_color?>; color:#fff; font-weight:bold; padding:4px 12px; border-radius:12px; font-size:10px; margin-top:5px;"><?=$priority_badge?></div>
                                <?php if(!empty($details['priority_comment'])): ?>
                                    <div style="font-size:9px; margin-top:5px; color:#666;"><?=$details['priority_comment']?></div>
                                <?php endif; ?>
                            </td>
                            <td style="width: 50%; vertical-align: middle; padding: 12px;">
                                <div style="width: 40px; height: 40px; background: #28a745; border-radius: 50%; margin: 0 auto; text-align:center; line-height:40px; color:#fff; font-weight:bold; font-size:18px;">&#10003;</div>
                                <div style="color:#1f0954; font-weight:bold; font-size:11px; margin-top:8px;">STATUS</div>
                                <div style="display:inline-block; background:<?=$status_color?>; color:#fff; font-weight:bold; padding:4px 12px; border-radius:12px; font-size:10px; margin-top:5px;"><?=$status_badge?></div>
                                <?php if(!empty($details['rejection_comment'])): ?>
                                    <div style="font-size:9px; margin-top:5px; color:#dc3545;"><?=$details['rejection_comment']?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Reporter Details -->
                <div class="card" style="margin-top: 10px; min-height: 100px;">
                    <div class="card-title-bar">
                        <span style="display:inline-block; width:18px; height:18px; background:#3e1e68; border-radius:50%; color:#fff; text-align:center; line-height:18px; font-size:9px; font-weight:bold; margin-right:6px; vertical-align:middle;">R</span>
                        REPORTER DETAILS
                    </div>
                    <div class="card-content" style="padding-top:10px; padding-bottom:10px;">
                        <table class="info-table">
                            <tr><td class="info-label" style="width:30%;">Name</td><td class="info-colon" style="width:5%;">:</td><td class="info-value"><?=$details['user_name']?></td></tr>
                            <tr><td class="info-label" style="width:30%;">Email Id</td><td class="info-colon" style="width:5%;">:</td><td class="info-value"><?=$details['user_email']?></td></tr>
                            <tr><td class="info-label" style="width:30%;">Contact No.</td><td class="info-colon" style="width:5%;">:</td><td class="info-value"><?=$details['user_contact']?></td></tr>
                            <tr><td class="info-label" style="width:30%;">Designation</td><td class="info-colon" style="width:5%;">:</td><td class="info-value"><?=$details['user_designation']?></td></tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="card">
        <div class="card-title-bar">
            <span style="display:inline-block; width:18px; height:18px; background:#3e1e68; border-radius:50%; color:#fff; text-align:center; line-height:18px; font-size:9px; font-weight:bold; margin-right:6px; vertical-align:middle;">I</span>
            INCIDENT DETAILS
        </div>
        <div class="card-content">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 48%; vertical-align: top;">
                        <table class="info-table">
                            <tr><td class="info-label">Category</td><td class="info-colon">:</td><td class="info-value" style="font-weight:bold; color:#1e1c77;"><?=!empty($details['near_miss_category_text'])?$details['near_miss_category_text']:'NEAR MISS'?></td></tr>
                            <tr><td class="info-label">Name of equipment involved</td><td class="info-colon">:</td><td class="info-value"><?=!empty($details['involved_equipment_name'])?$details['involved_equipment_name']:'-'?></td></tr>
                            <tr><td class="info-label">Description briefly</td><td class="info-colon">:</td><td class="info-value"><?=!empty($details['description_of_occurred'])?$details['description_of_occurred']:'-'?></td></tr>
                            <tr><td class="info-label">What could have happened</td><td class="info-colon">:</td><td class="info-value"><?=!empty($details['what_could_have_happened'])?$details['what_could_have_happened']:'-'?></td></tr>
                            <tr><td class="info-label">Immediate Action</td><td class="info-colon">:</td><td class="info-value"><?=!empty($details['immediate_action'])?$details['immediate_action']:'-'?></td></tr>
                            <tr><td class="info-label">Task Activity</td><td class="info-colon">:</td><td class="info-value"><?=!empty($details['mode_of_work'])?$details['mode_of_work']:'-'?></td></tr>
                            <tr><td class="info-label">Nature Of Injury Or Damage</td><td class="info-colon">:</td><td class="info-value"><?=!empty($details['nature_of_damage'])?$details['nature_of_damage']:'-'?></td></tr>
                        </table>
                    </td>
                    
                    <td style="width: 4%;"></td>
                    
                    <td style="width: 48%; vertical-align: top;">
                        <table class="info-table">
                            <tr><td class="info-label">Causes of Incident</td><td class="info-colon">:</td><td class="info-value"><?=!empty($details['causes_of_incident'])?$details['causes_of_incident']:'-'?></td></tr>
                            <tr><td class="info-label">Body Part Affect</td><td class="info-colon">:</td><td class="info-value"><?=!empty($details['body_part_affect_details'])?$details['body_part_affect_details']:'-'?></td></tr>
                            <tr><td class="info-label">Loss Hours</td><td class="info-colon">:</td><td class="info-value"><?=!empty($details['total_hours'])?$details['total_hours']:'-'?></td></tr>
                            <tr><td class="info-label">Loss Cost</td><td class="info-colon">:</td><td class="info-value">Rs <?=!empty($details['total_cost'])?$details['total_cost']:'0'?></td></tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-title-bar">
            <span style="display:inline-block; width:18px; height:18px; background:#3e1e68; border-radius:50%; color:#fff; text-align:center; line-height:18px; font-size:9px; font-weight:bold; margin-right:6px; vertical-align:middle;">D</span>
            DETAILS OF INCIDENT
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Age</th>
                    <th>Company Name</th>
                    <th>Shift</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?=!empty($details['incident_person_name'])?$details['incident_person_name']:'-'?></td>
                    <td><?=!empty($details['incident_person_designation'])?$details['incident_person_designation']:'-'?></td>
                    <td><?=!empty($details['incident_person_age'])?$details['incident_person_age']:'-'?></td>
                    <td><?=!empty($details['incident_person_company'])?$details['incident_person_company']:'-'?></td>
                    <td><?=!empty($details['incident_person_sift'])?$details['incident_person_sift']:'-'?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php if(isset($near_miss_json_data['future_occurrence']) && count($near_miss_json_data['future_occurrence']) > 0): ?>
    <div class="card">
        <div class="card-title-bar">
            <span style="display:inline-block; width:18px; height:18px; background:#3e1e68; border-radius:50%; color:#fff; text-align:center; line-height:18px; font-size:9px; font-weight:bold; margin-right:6px; vertical-align:middle;">F</span>
            WHAT SHOULD BE DONE IN FUTURE
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Action To Be Taken</th>
                    <th>Action By</th>
                    <th>Remarks</th>
                    <th>Target Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($near_miss_json_data['future_occurrence'] as $future): ?>
                <tr>
                    <td><?=isset($future['corrective_action'])?$future['corrective_action']:"-"?></td>
                    <td><?=isset($future['person_responsible'])?$future['person_responsible']:"-"?></td>
                    <td><?=isset($future['causes'])?$future['causes']:"-"?></td>
                    <td><?=isset($future['target_date'])?$future['target_date']:"-"?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-title-bar">
            BEFORE PROOF / EVIDENCE (IF ANY)
        </div>
        <div style="padding: 15px;">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
            <?php 
            if(!empty($details['images'])) {
                $images = explode(",", $details['images']);
                $count = 0;
                foreach($images as $img) {
                    if(empty(trim($img))) continue;
                    $ext = strtolower(pathinfo(parse_url(trim($img), PHP_URL_PATH), PATHINFO_EXTENSION));
                    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    if ($isImg) {
                        $options = ['http' => ['ignore_errors' => true], 'https' => ['ignore_errors' => true]];
                        $context = stream_context_create($options);
                        $imageData = @file_get_contents(trim($img), false, $context);
                        if($imageData) {
                            $base64 = base64_encode($imageData);
                            echo '<td style="width:25%; text-align:center; padding:8px;">';
                            echo '<div style="background:#f8f6fb; padding:8px; border-radius:8px; border:1px solid #d1c4e9;">';
                            echo '<img src="data:image/'.$ext.';base64,'.$base64.'" style="max-width:100%; max-height:140px; border-radius:4px;">';
                            echo '</div></td>';
                            $count++;
                            if($count % 3 == 0) echo '</tr><tr>';
                        }
                    } else {
                        // Non-image attachment
                        echo '<td style="padding:8px;">';
                        echo '<div style="background:#fff; border:1px solid #d1c4e9; border-radius:8px; padding:14px;">';
                        echo '<table style="border-collapse:collapse; width:100%;"><tr>';
                        echo '<td style="width:36px;"><div style="width:32px;height:32px;background:#fef2f2;border-radius:6px;text-align:center;line-height:32px;font-size:14px;">&#128196;</div></td>';
                        echo '<td style="padding-left:10px;"><div style="font-weight:bold;font-size:12px;color:#111827;">'.esc(basename(parse_url(trim($img), PHP_URL_PATH))).'</div>';
                        echo '<div style="font-size:10px;color:#6b7280;">'.strtoupper($ext).' Attachment</div></td>';
                        echo '<td style="text-align:right;"><a href="'.esc(trim($img)).'" style="color:#2563eb;font-weight:bold;font-size:11px;text-decoration:none;">View</a></td>';
                        echo '</tr></table></div></td>';
                        $count++;
                    }
                }
                if($count == 0) {
                    echo '<td><div style="background:#f8f6fb; padding:30px; text-align:center; color:#999; border-radius:8px; border:1px dashed #d1c4e9;">No Before Proof Available</div></td>';
                }
            } else {
                echo '<td><div style="background:#f8f6fb; padding:30px; text-align:center; color:#999; border-radius:8px; border:1px dashed #d1c4e9;">No Before Proof Available</div></td>';
            }
            ?>
            </tr>
        </table>
        </div>
    </div>

    <div class="card">
        <div class="card-title-bar">
            AFTER PROOF / EVIDENCE (IF ANY)
        </div>
        <div style="padding: 15px;">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
            <?php 
            if(!empty($details['after_images'])) {
                $after_images = explode(",", $details['after_images']);
                $count = 0;
                foreach($after_images as $img) {
                    if(empty(trim($img))) continue;
                    $ext = strtolower(pathinfo(parse_url(trim($img), PHP_URL_PATH), PATHINFO_EXTENSION));
                    $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    if ($isImg) {
                        $options = ['http' => ['ignore_errors' => true], 'https' => ['ignore_errors' => true]];
                        $context = stream_context_create($options);
                        $imageData = @file_get_contents(trim($img), false, $context);
                        if($imageData) {
                            $base64 = base64_encode($imageData);
                            echo '<td style="width:25%; text-align:center; padding:8px;">';
                            echo '<div style="background:#f8f6fb; padding:8px; border-radius:8px; border:1px solid #d1c4e9;">';
                            echo '<img src="data:image/'.$ext.';base64,'.$base64.'" style="max-width:100%; max-height:140px; border-radius:4px;">';
                            echo '</div></td>';
                            $count++;
                            if($count % 3 == 0) echo '</tr><tr>';
                        }
                    } else {
                        echo '<td style="padding:8px;">';
                        echo '<div style="background:#fff; border:1px solid #d1c4e9; border-radius:8px; padding:14px;">';
                        echo '<table style="border-collapse:collapse; width:100%;"><tr>';
                        echo '<td style="width:36px;"><div style="width:32px;height:32px;background:#fef2f2;border-radius:6px;text-align:center;line-height:32px;font-size:14px;">&#128196;</div></td>';
                        echo '<td style="padding-left:10px;"><div style="font-weight:bold;font-size:12px;color:#111827;">'.esc(basename(parse_url(trim($img), PHP_URL_PATH))).'</div>';
                        echo '<div style="font-size:10px;color:#6b7280;">'.strtoupper($ext).' Attachment</div></td>';
                        echo '<td style="text-align:right;"><a href="'.esc(trim($img)).'" style="color:#2563eb;font-weight:bold;font-size:11px;text-decoration:none;">View</a></td>';
                        echo '</tr></table></div></td>';
                        $count++;
                    }
                }
                if($count == 0) {
                    echo '<td><div style="background:#f8f6fb; padding:30px; text-align:center; color:#999; border-radius:8px; border:1px dashed #d1c4e9;">No After Proof Available</div></td>';
                }
            } else {
                echo '<td><div style="background:#f8f6fb; padding:30px; text-align:center; color:#999; border-radius:8px; border:1px dashed #d1c4e9;">No After Proof Available</div></td>';
            }
            ?>
            </tr>
        </table>
        </div>
    </div>

    <?php if(isset($include_chat) && $include_chat): ?>
    <div style="margin-top:20px;"></div>
    
    <!-- CHAT MEMBERS -->
    <div style="background-color: #f8f6fb; padding: 20px; border-radius: 8px;">
        <div style="color: #3e1e68; font-weight: bold; font-size: 14px; margin-bottom: 15px;">CHAT MEMBERS</div>
        <table width="100%" style="border-collapse: collapse;">
            <tr>
            <?php if(!empty($chat_members)): ?>
                <?php $count = 0; foreach($chat_members as $member): 
                    $initial = strtoupper(substr(trim(esc($member['user_name'])), 0, 1));
                    if (empty($initial)) $initial = '?';
                ?>
                    <td style="padding-right: 15px; <?php if($count > 0) echo 'border-left: 1px solid #d1c4e9; padding-left: 15px;'; ?>">
                        <table style="border-collapse: collapse;">
                            <tr>
                                <td>
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background-color: #4a148c; color: white; text-align: center; line-height: 36px; font-weight: bold; font-size: 14px;">
                                        <?=$initial?>
                                    </div>
                                </td>
                                <td style="padding-left: 10px;">
                                    <div style="color: #4a148c; font-weight: bold; font-size: 12px;"><?=esc($member['user_name'])?></div>
                                    <div style="color: #666; font-size: 10px;"><?=esc($member['user_designation'])?></div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <?php $count++; if($count % 4 == 0) echo '</tr><tr><td colspan="4" style="height:10px;"></td></tr><tr>'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <td><div style="color:#999; font-style:italic;">No additional members assigned.</div></td>
            <?php endif; ?>
            </tr>
        </table>
    </div>

    <div style="margin-top:20px;"></div>

    <!-- CHAT DISCUSSION -->
    <div style="background-color: #4a148c; padding: 12px 20px; border-radius: 8px 8px 0 0; color: white;">
        <table width="100%" style="border-collapse: collapse;">
            <tr>
                <td style="font-weight: bold; font-size: 14px;">
                    <span style="font-family: DejaVu Sans, sans-serif;">💬</span> NEAR-MISS CHAT / DISCUSSION
                </td>
                <td style="text-align: right; font-size: 11px; opacity: 0.8;">
                    Collaborate and resolve this near miss report
                </td>
            </tr>
        </table>
    </div>
    <div style="border: 1px solid #d1c4e9; border-top: none; padding: 20px; border-radius: 0 0 8px 8px;">
        <table width="100%" style="border-collapse: collapse;">
            <?php if(!empty($near_miss_chat)): ?>
                <?php foreach($near_miss_chat as $msg): 
                    $initial = strtoupper(substr(trim(esc($msg['user_name'])), 0, 1));
                    if (empty($initial)) $initial = '?';
                ?>
                <tr>
                    <td style="padding: 15px 0; border-bottom: 1px dashed #eee; vertical-align: top;">
                        <table width="100%" style="border-collapse: collapse;">
                            <tr>
                                <!-- Avatar -->
                                <td width="50" style="vertical-align: top;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #7b1fa2; color: white; text-align: center; line-height: 40px; font-weight: bold; font-size: 16px; margin: 0 auto;">
                                        <?=$initial?>
                                    </div>
                                </td>
                                <!-- Content -->
                                <td style="vertical-align: top; padding-left: 10px;">
                                    <table width="100%" style="border-collapse: collapse;">
                                        <tr>
                                            <td style="color: #4a148c; font-weight: bold; font-size: 13px;">
                                                <?=esc($msg['user_name'])?>
                                            </td>
                                            <td style="text-align: right; color: #888; font-size: 11px;">
                                                <?=date('d-M-Y h:i A', strtotime($msg['default_date']))?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="color: #888; font-size: 11px; padding-bottom: 8px;">
                                                <?=esc($msg['user_designation'])?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <?php if (!empty($msg['chat_text'])): ?>
                                                    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 12px; font-size: 12px; color: #333; display: inline-block; min-width: 50%; white-space: pre-wrap; word-wrap: break-word; margin-bottom: 5px;"><?=esc($msg['chat_text'])?></div>
                                                <?php endif; ?>
                                                
                                                <?php if(isset($msg['chat_type']) && (int)$msg['chat_type'] === 1): ?>
                                                    <?php 
                                                        $img_url = trim($msg['chat_image']);
                                                        $options = ['http' => ['ignore_errors' => true], 'https' => ['ignore_errors' => true]];
                                                        $context = stream_context_create($options);
                                                        $imageData = @file_get_contents($img_url, false, $context);
                                                        if($imageData) {
                                                            $base64 = base64_encode($imageData);
                                                            echo '<div style="margin-top:8px; display:inline-block;"><img src="data:image/png;base64,'.$base64.'" style="max-height:150px; max-width:100%; border-radius:6px; border:1px solid #ddd;"></div>';
                                                        }
                                                    ?>
                                                <?php elseif(isset($msg['chat_type']) && (int)$msg['chat_type'] === 2): 
                                                    $meta = json_decode($msg['chat_image'], true);
                                                    if ($meta) {
                                                        $ext = strtolower(pathinfo($meta['original_name'], PATHINFO_EXTENSION));
                                                        $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                                        if ($isImg) {
                                                            $img_url = base_url($meta['path']);
                                                            $options = ['http' => ['ignore_errors' => true], 'https' => ['ignore_errors' => true]];
                                                            $context = stream_context_create($options);
                                                            $imageData = @file_get_contents($img_url, false, $context);
                                                            if($imageData) {
                                                                $base64 = base64_encode($imageData);
                                                                echo '<div style="margin-top:8px; display:inline-block;"><img src="data:image/png;base64,'.$base64.'" style="max-height:150px; max-width:100%; border-radius:6px; border:1px solid #ddd;"></div>';
                                                            }
                                                        } else {
                                                            $sizeKB = isset($meta['file_size']) ? number_format($meta['file_size']/1024, 1).' KB' : strtoupper($ext).' File';
                                                            echo '<div style="margin-top:8px; padding:12px; border:1px solid #e5e7eb; background-color:#fff; border-radius:8px; width: 60%;">';
                                                            echo '<table width="100%" style="border-collapse:collapse;"><tr>';
                                                            echo '<td width="40"><div style="width:32px;height:32px;background:#fef2f2;border-radius:6px;text-align:center;line-height:32px;font-size:16px;">📄</div></td>';
                                                            echo '<td style="padding-left:10px;"><div style="font-weight:bold;font-size:12px;color:#111827;">'.esc($meta['original_name']).'</div><div style="font-size:10px;color:#6b7280;">Document &bull; '.$sizeKB.'</div></td>';
                                                            echo '<td style="text-align:right;"><a href="'.base_url($meta['path']).'" style="color:#2563eb;font-weight:bold;font-size:12px;text-decoration:none;">Download</a></td>';
                                                            echo '</tr></table></div>';
                                                        }
                                                    }
                                                ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td style="padding: 20px; text-align: center; color: #999;">
                        No discussion history available.
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

<?php endif; ?>

</div>

<div style="background: #f4f1fa; border-top: 2px solid #3e1e68; padding: 12px 40px; margin-top: 20px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width:40%; vertical-align:middle; font-size:10px; color:#3e1e68; font-style:italic;">Safety is not just a priority, It's a way of life.</td>
            <td style="width:30%; text-align:center; vertical-align:middle; font-size:10px; color:#3e1e68; font-weight:bold;">www.fmlogistic.com</td>
            <td style="width:30%; text-align:right; vertical-align:middle; font-size:10px; color:#666;">Generated On : <?=date('d-M-Y h:i A')?></td>
        </tr>
    </table>
</div>

</body>
</html>
