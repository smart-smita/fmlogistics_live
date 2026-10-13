<?php $this->extend("Layout/base_admin"); ?>
<?php $this->section("breadcrumb_title_li"); ?>
<!--begin::Item-->
<li class="breadcrumb-item text-muted">
    <a href="<?= current_url() ?>" class="text-muted text-hover-primary"><?=isset($title)?$title:"Client"?></a>
</li>
<!--end::Item-->
<?php $this->endSection();?>	

<?php $this->section("main_body"); ?>

<?php
if(isset($details) && !empty($details))
	$details=$details[0];

$status_map = [
    0 => '<span class="badge badge-danger px-3 py-2 fs-7 fw-bold">Pending</span>',
    1 => '<span class="badge badge-success px-3 py-2 fs-7 fw-bold">Verified</span>',
    2 => '<span class="badge badge-warning px-3 py-2 fs-7 fw-bold">Open</span>',
    3 => '<span class="badge badge-secondary px-3 py-2 fs-7 fw-bold">Cancel</span>',
    4 => '<span class="badge badge-warning px-3 py-2 fs-7 fw-bold">Request required</span>',
    5 => '<span class="badge badge-success px-3 py-2 fs-7 fw-bold">Close</span>'
];
$near_miss_status = isset($status_map[$details['status']]) ? $status_map[$details['status']] : '';

$priority_map = [
    0 => '<span class="badge badge-light-warning text-warning px-3 py-2 fw-bolder">Low Priority</span>',
    1 => '<span class="badge badge-light-danger text-danger px-3 py-2 fw-bolder">High Priority</span>'
];
$priority_badge = isset($priority_map[$details['near_miss_priority']]) ? $priority_map[$details['near_miss_priority']] : '';
?>

<style>
    /* Modern Aesthetic Overrides */
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
        border-radius: 1rem;
    }
    .detail-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #a1a5b7;
        font-weight: 600;
        margin-bottom: 0.25rem;
        display: block;
    }
    .detail-value {
        font-size: 1rem;
        color: #3f4254;
        font-weight: 500;
        word-wrap: break-word;
    }
    /* New Figma UI Overrides */
    .chat-sidebar {
        background: #f8f9fc;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e4e6ef;
    }
    .chat-sidebar-avatar-wrap {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #e0f2fe;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px auto;
        border: 3px solid #3b82f6;
    }
    .chat-sidebar-avatar-wrap img {
        width: 86px;
        height: 86px;
        border-radius: 50%;
        border: 3px solid #fff;
    }
    .stat-box {
        border-radius: 12px;
        padding: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }
    .stat-box-blue {
        background: #e0f2fe;
        color: #1e3a8a;
    }
    .stat-box-green {
        background: #dcfce7;
        color: #166534;
    }
    .member-pill {
        border: 1px solid #a855f7;
        color: #a855f7;
        border-radius: 20px;
        padding: 5px 12px;
        display: inline-flex;
        align-items: center;
        font-weight: 600;
        font-size: 13px;
        background: transparent;
    }
    .chat-main-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e4e6ef;
        display: flex;
        flex-direction: column;
    }
    .chat-bubble-other {
        background: #f3f4f6;
        color: #1f2937;
        border-radius: 0 15px 15px 15px;
        padding: 12px 16px;
        display: inline-block;
    }
    .chat-bubble-me {
        background: #e0f2fe;
        color: #1e3a8a;
        border-radius: 15px 15px 0 15px;
        padding: 12px 16px;
        display: inline-block;
    }
    .role-badge-engineer { background: #e0f2fe; color: #3b82f6; }
    .role-badge-auditor { background: #ffedd5; color: #ea580c; }
    .role-badge-admin { background: #dcfce7; color: #16a34a; }
    
    .chat-attachment-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        gap: 12px;
        min-width: 200px;
        max-width: 100%;
        margin-top: 10px;
    }
    .chat-input-wrapper {
        background: #f3f4f6;
        border-radius: 8px;
        padding: 5px;
        display: flex;
        align-items: flex-end;
        border: 1px solid #e5e7eb;
    }
    .chat-input {
        background: transparent;
        border: none !important;
        resize: none;
        box-shadow: none !important;
        flex-grow: 1;
        padding: 10px 15px;
    }
    .chat-input:focus {
        outline: none !important;
    }
    .drag-drop-zone {
        border: 1px dashed #3b82f6;
        border-radius: 8px;
        background: #eff6ff;
        padding: 15px;
        text-align: center;
        display: none;
        margin-top: 10px;
    }
    .selected-files-zone {
        display: none;
        margin-top: 10px;
    }
    .selected-file-pill {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px 12px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #fff;
    }
</style>

<div class="row mb-5">
	<div class="col-sm-12 d-flex justify-content-between align-items-center">
		<h2 class="page-title fw-bolder m-0"><?=(isset($page_title))?$page_title:"Near Miss Report Overview"?></h2>
        <div>
            <a href="<?=base_url(index_page().'/Reports/Near_miss_reports/nearMissChatPdf/'.$details['near_miss_id'])?>" class="btn btn-danger fw-bold me-2" target="_blank" title="Download Chat PDF">
                <i class="fa fa-file-pdf"></i> Download PDF
            </a>
            <a href="<?=base_url('Reports/Near_miss_reports')?>" class="btn btn-secondary fw-bold"><i class="fa fa-arrow-left me-2"></i> Back</a>
        </div>
	</div>
</div>

<div class="row g-5 mb-5">
    <!-- Left Column: Details -->
    <div class="col-12">
        <div class="card glass-card">
            <div class="card-header d-flex justify-content-between align-items-center py-4 border-bottom-0">
                <h3 class="card-title fw-bolder m-0">
                    <i class="fa fa-clipboard-list text-primary me-2"></i> Report #<?=$details['near_miss_id']?> Details
                </h3>
                <div>
                    <?=$priority_badge?>
                    <button class="btn btn-sm btn-light-primary ms-3 fw-bold" onclick="$('#full_details_container').slideToggle(); $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');">
                        Show/Hide All Data <i class="fa fa-chevron-down ms-2"></i>
                    </button>
                </div>
            </div>

            <div class="card-body pt-0 pb-5" id="full_details_container" style="display:none;">
                
                <!-- 1. General & Location -->
                <h4 class="section-title mt-4">General & Location</h4>
                <div class="row g-5 mb-5">
                    <div class="col-md-3">
                        <span class="detail-label">Status</span>
                        <div class="detail-value"><?=$near_miss_status?></div>
                    </div>
                    <div class="col-md-3">
                        <span class="detail-label">Category</span>
                        <div class="detail-value fw-bold text-primary"><?=esc($details['near_miss_category_text'] ?? '-')?></div>
                    </div>
                    <div class="col-md-3">
                        <span class="detail-label">Date & Time</span>
                        <div class="detail-value"><i class="fa fa-calendar-alt text-muted me-1"></i> <?=esc($details['near_miss_date'])?> <?=esc($details['near_miss_time'])?></div>
                    </div>
                    <div class="col-md-3">
                        <span class="detail-label">Project Name</span>
                        <div class="detail-value"><?=esc($details['project_name'] ?? '-')?></div>
                    </div>
                    <div class="col-md-3">
                        <span class="detail-label">Job No</span>
                        <div class="detail-value"><?=esc($details['job_no'] ?? '-')?></div>
                    </div>
                    <div class="col-md-3">
                        <span class="detail-label">Location (Dropdown)</span>
                        <div class="detail-value"><?=esc($details['location'] ?? '-')?></div>
                    </div>
                    <div class="col-md-3">
                        <span class="detail-label">GPS Location</span>
                        <div class="detail-value"><?=esc($details['gps_location'] ?? '-')?></div>
                    </div>
                    <div class="col-md-3">
                        <span class="detail-label">Manual Location</span>
                        <div class="detail-value"><?=esc($details['manual_location'] ?? '-')?></div>
                    </div>
                </div>

                <!-- 2. Reporter Details -->
                <h4 class="section-title">Reporter Information</h4>
                <div class="row g-5 mb-5 p-4 bg-light rounded">
                    <div class="col-md-3">
                        <span class="detail-label">Reporter Name</span>
                        <div class="detail-value"><?=esc($details['user_name'] ?? '-')?></div>
                    </div>
                    <div class="col-md-3">
                        <span class="detail-label">Designation</span>
                        <div class="detail-value"><?=esc($details['user_designation'] ?? '-')?></div>
                    </div>
                    <div class="col-md-3">
                        <span class="detail-label">Contact</span>
                        <div class="detail-value"><?=esc($details['user_contact'] ?? '-')?></div>
                    </div>
                    <div class="col-md-3">
                        <span class="detail-label">Email</span>
                        <div class="detail-value"><?=esc($details['user_email'] ?? '-')?></div>
                    </div>
                    <?php if(isset($details['self_other']) && $details['self_other'] == '1'): ?>
                    <div class="col-md-12 mt-4">
                        <div class="alert alert-primary d-flex align-items-center p-3 mb-0">
                            <i class="fa fa-info-circle fs-2 text-primary me-3"></i>
                            <div><strong>Reported on behalf of:</strong> <?=esc($details['other_engineer_name'] ?? '-')?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 3. Incident Person Details -->
                <h4 class="section-title">Involved Person Details</h4>
                <div class="row g-5 mb-5">
                    <div class="col-md-3">
                        <span class="detail-label">Name</span>
                        <div class="detail-value"><?=esc($details['incident_person_name'] ?? '-')?></div>
                    </div>
                    <div class="col-md-3">
                        <span class="detail-label">Designation</span>
                        <div class="detail-value"><?=esc($details['incident_person_designation'] ?? '-')?></div>
                    </div>
                    <div class="col-md-2">
                        <span class="detail-label">Age</span>
                        <div class="detail-value"><?=esc($details['incident_person_age'] ?? '-')?></div>
                    </div>
                    <div class="col-md-2">
                        <span class="detail-label">Company</span>
                        <div class="detail-value"><?=esc($details['incident_person_company'] ?? '-')?></div>
                    </div>
                    <div class="col-md-2">
                        <span class="detail-label">Shift</span>
                        <div class="detail-value"><?=esc($details['incident_person_sift'] ?? '-')?></div>
                    </div>
                </div>

                <!-- 4. Incident Analysis -->
                <h4 class="section-title">Incident Analysis</h4>
                <div class="row g-5 mb-5">
                    <div class="col-md-4">
                        <span class="detail-label">Mode of Work</span>
                        <div class="detail-value"><?=esc($details['mode_of_work'] ?? '-')?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="detail-label">Involved Equipment</span>
                        <div class="detail-value"><?=esc($details['involved_equipment_name'] ?? '-')?></div>
                    </div>
                    <div class="col-md-4">
                        <span class="detail-label">Damage Nature</span>
                        <div class="detail-value"><?=esc($details['nature_of_damage'] ?? '-')?></div>
                    </div>
                    
                    <div class="col-md-12">
                        <span class="detail-label">Brief Description of Occurrence</span>
                        <div class="detail-value bg-light p-3 rounded" style="white-space: pre-wrap;"><?=esc($details['description_of_occurred'] ?? '-')?></div>
                    </div>
                    <div class="col-md-12">
                        <span class="detail-label">What could have happened?</span>
                        <div class="detail-value bg-light p-3 rounded" style="white-space: pre-wrap;"><?=esc($details['what_could_have_happened'] ?? '-')?></div>
                    </div>
                    <div class="col-md-12">
                        <span class="detail-label">Causes of Incident</span>
                        <div class="detail-value bg-light p-3 rounded" style="white-space: pre-wrap;"><?=esc($details['causes_of_incident'] ?? '-')?></div>
                    </div>
                    <div class="col-md-12">
                        <span class="detail-label">Immediate Action</span>
                        <div class="detail-value bg-light p-3 rounded" style="white-space: pre-wrap;"><?=esc($details['immediate_action'] ?? '-')?></div>
                    </div>
                    
                    <div class="col-md-6">
                        <span class="detail-label">Body Part Affect</span>
                        <div class="detail-value"><?=esc($details['body_part_affect'] ?? '-')?></div>
                    </div>
                    <div class="col-md-6">
                        <span class="detail-label">Body Part Affect Details</span>
                        <div class="detail-value"><?=esc($details['body_part_affect_details'] ?? '-')?></div>
                    </div>
                </div>

                <!-- 5. Future Occurrence -->
                <h4 class="section-title">Corrective Action & Future Occurrence</h4>
                <div class="row g-5 mb-5">
                    <div class="col-md-12">
                        <?php 
                        $future_occ = isset($details['future_occurrence']) ? json_decode($details['future_occurrence'], true) : [];
                        if (!empty($future_occ) && isset($future_occ['future_occurrence'])) {
                            echo '<div class="table-responsive"><table class="table table-bordered table-striped border-gray-300 align-middle">
                                    <thead class="bg-light">
                                        <tr class="fw-bolder text-gray-800">
                                            <th>Sr.No</th>
                                            <th>Causes</th>
                                            <th>Corrective Action</th>
                                            <th>Responsibility</th>
                                            <th>Target Date</th>
                                        </tr>
                                    </thead><tbody>';
                            foreach ($future_occ['future_occurrence'] as $idx => $row) {
                                echo '<tr>
                                        <td>'.($idx + 1).'</td>
                                        <td>'.esc($row['causes'] ?? '').'</td>
                                        <td>'.esc($row['corrective_action'] ?? '').'</td>
                                        <td>'.esc($row['person_responsible'] ?? '').'</td>
                                        <td>'.esc($row['target_date'] ?? '').'</td>
                                      </tr>';
                            }
                            echo '</tbody></table></div>';
                        } else {
                            echo '<div class="text-muted">No Future Occurrence data provided.</div>';
                        }
                        ?>
                    </div>
                    <div class="col-md-6">
                        <span class="detail-label">Total Hours Required</span>
                        <div class="detail-value fw-bold"><?=esc($details['total_hours'] ?? '0')?> Hrs</div>
                    </div>
                    <div class="col-md-6">
                        <span class="detail-label">Total Cost Involved</span>
                        <div class="detail-value fw-bold"><?=esc($details['total_cost'] ?? '0')?> </div>
                    </div>
                </div>

                <!-- 6. Admin Comments & Images -->
                <?php if(!empty($details['priority_comment']) || !empty($details['rejection_comment'])): ?>
                <h4 class="section-title">Reviewer Comments</h4>
                <div class="row g-5 mb-5">
                    <?php if(!empty($details['priority_comment'])): ?>
                    <div class="col-md-6">
                        <span class="detail-label">Priority Comment</span>
                        <div class="detail-value p-3 rounded bg-light-warning border border-warning border-dashed"><?=esc($details['priority_comment'])?></div>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($details['rejection_comment'])): ?>
                    <div class="col-md-6">
                        <span class="detail-label">Rejection Comment</span>
                        <div class="detail-value p-3 rounded bg-light-danger border border-danger border-dashed"><?=esc($details['rejection_comment'])?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if(!empty($details['images'])): ?>
                <h4 class="section-title">Attachments</h4>
                <div class="d-flex flex-wrap gap-4">
                    <?php 
                    $imgs = explode(",", $details['images']);
                    foreach($imgs as $img):
                        if(trim($img) != ""):
                    ?>
                        <img src="<?=esc(trim($img))?>" class="gallery-img border border-gray-300" height="150" width="150" onclick="window.open(this.src)">
                    <?php 
                        endif;
                    endforeach;
                    ?>
                </div>
                <?php endif; ?>

                <?php if(!empty($details['after_images'])): ?>
                <h4 class="section-title">After Proof / Evidence</h4>
                <div class="d-flex flex-wrap gap-4">
                    <?php 
                    $afterImgs = explode(",", $details['after_images']);
                    foreach($afterImgs as $img):
                        if(trim($img) != ""):
                            // Check if it's an image or document
                            $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    ?>
                        <img src="<?=esc(trim($img))?>" class="gallery-img border border-gray-300" height="150" width="150" onclick="window.open(this.src)">
                    <?php 
                            } else {
                    ?>
                        <div class="border border-gray-300 rounded p-3 text-center d-flex flex-column align-items-center justify-content-center" style="width: 150px; height: 150px;">
                            <i class="fa fa-file-alt fa-3x text-primary mb-2"></i>
                            <a href="<?=esc(trim($img))?>" target="_blank" class="btn btn-sm btn-light-primary w-100">View/Download</a>
                        </div>
                    <?php
                            }
                        endif;
                    endforeach;
                    ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php 
$role = strtolower($_SESSION['role'] ?? '');
$isHigherAuth = in_array($role, ['higher authority', 'higher_authority']);
$canChangeStatus = in_array($role, ['super admin', 'super_admin', 'admin', 'auditor', 'account manager', 'account_manager', 'cluster manager', 'cluster_manager']);

if ($details['status'] == 2 && !$isHigherAuth && $canChangeStatus): 
?>
<!-- Close Workflow Section -->
<div class="row g-5 mb-5">
    <div class="col-12">
        <div class="card glass-card border-success border border-2 border-dashed">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title text-success fw-bolder">
                    <i class="fa fa-check-double text-success me-2"></i> Close Near Miss Report
                </h3>
            </div>
            <div class="card-body pt-0">
                <p class="text-muted">To officially close this Near Miss report, please upload the <strong>After Proof / Evidence</strong> demonstrating that the corrective action has been implemented.</p>
                <form action="<?=base_url('Reports/Near_miss_reports/close_near_miss')?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="near_miss_id" value="<?=$details['near_miss_id']?>">
                    <div class="row align-items-center mt-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Upload After Proof (Images or Documents) <span class="text-danger">*</span></label>
                            <input type="file" name="after_images[]" class="form-control form-control-solid" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" multiple required>
                            <div class="form-text">Supported formats: JPG, PNG, PDF, DOCX, XLSX.</div>
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success fw-bold w-100 mt-4">
                                <i class="fa fa-upload me-2"></i> Upload & Close Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Chat Section -->
<div class="row mb-4 g-5">
    
    <!-- Chat Sidebar -->
    <div class="col-12 col-lg-4 col-xl-3 mb-4 mb-lg-0">
        <div class="chat-sidebar h-100">
            <div class="text-center mb-6">
                <div class="chat-sidebar-avatar-wrap shadow-sm">
                    <img src="<?=base_url().'/assets/images/profile.png'?>" alt="Profile">
                </div>
                <h4 class="mb-1 fw-bolder text-gray-900" style="font-size: 1.15rem;"><?=esc($details['user_name'])?></h4>
                <span class="badge bg-primary text-white rounded-pill px-3 py-1 fs-8 fw-bold mt-1">Reporter</span>
            </div>

            <div class="row text-center mb-6 g-3">
                <div class="col-6">
                    <div class="stat-box stat-box-blue shadow-sm">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa fa-users"></i> <span class="fw-bold fs-7">Users</span>
                        </div>
                        <h3 class="mb-0 fw-bolder fs-4"><?=$details['group_member_count']?></h3>
                    </div>
                </div>
                <div class="col-6">
                    <div class="stat-box stat-box-green shadow-sm">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa fa-comments"></i> <span class="fw-bold fs-7">Comments</span>
                        </div>
                        <h3 class="mb-0 fw-bolder fs-4"><?=count($near_miss_chat)?></h3>
                    </div>
                </div>
            </div>

            <h5 class="fw-bolder text-gray-800 mb-3 d-flex align-items-center fs-6">
                <i class="fa fa-users text-primary me-2"></i> Chat Members
            </h5>
            
            <div class="mb-6 d-flex flex-wrap gap-2">
                <?php 
                $involvedRaw = isset($details['involved_user']) ? (string)$details['involved_user'] : '';
                $names = array_filter(array_map('trim', $involvedRaw !== '' ? explode(',', $involvedRaw) : []));
                $idsRaw = isset($details['group_member_ids']) ? (string)$details['group_member_ids'] : '';
                $ids = array_filter(array_map('trim', $idsRaw !== '' ? explode(',', $idsRaw) : []));
                $alreadyMemberIds = array_map('intval', $ids);
                if (!empty($details['user_id'])) {
                    $alreadyMemberIds[] = (int)$details['user_id'];
                }
                if (!empty($details['other_engineer_id'])) {
                    $alreadyMemberIds[] = (int)$details['other_engineer_id'];
                }
                foreach ($names as $idx => $name){
                    $uid = isset($ids[$idx]) ? (int)$ids[$idx] : null;
                    echo "<div class='member-pill shadow-sm'>".htmlspecialchars($name, ENT_QUOTES);
                    if ($uid){
                        $removeUrl = base_url(index_page().'/Reports/Near_miss_reports/remove_engineer?near_miss_id='.$details['near_miss_id'].'&user_id='.$uid);
                        echo "<a href='".$removeUrl."' class='text-primary ms-2 text-decoration-none' title='Remove' onclick=\"return confirm('Remove this member?');\"><i class='fa fa-times'></i></a>";
                    }
                    echo "</div>";
                }
                ?>
            </div>

            <div class="separator separator-dashed my-5 border-gray-300"></div>
            
        <form action="<?=base_url(index_page().'/Reports/Near_miss_reports/add_engineer')?>" method="post" id="user_list_involved">
                <label class="form-label fw-bold text-gray-800 fs-6">Add New Member</label>
                <div class="mt-2">
                    <!-- Styled checkbox member list -->
                    <div class="member-checkbox-list border rounded-3 bg-white" style="max-height: 220px; overflow-y: auto; padding: 6px 0;">
                        <?php 
                        $renderedUserIds = [];
                        foreach($users_list as $key => $engineer_row) { 
                            $uId = (int)$engineer_row['user_id'];
                            if (in_array($uId, $alreadyMemberIds) || in_array($uId, $renderedUserIds)) {
                                continue;
                            }
                            $renderedUserIds[] = $uId;
                            
                            $displayName = htmlspecialchars($engineer_row['user_name'], ENT_QUOTES);
                            if (!empty($engineer_row['user_designation'])) {
                                $displayName .= ' <span class="text-muted fs-8">(' . htmlspecialchars($engineer_row['user_designation'], ENT_QUOTES) . ')</span>';
                            }
                        ?>
                        <label class="member-checkbox-item d-flex align-items-center gap-2 px-3 py-2" style="cursor:pointer; transition: background 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                            <input type="checkbox" name="assigned_engineer_id[]" value="<?=$uId?>" style="width:16px; height:16px; flex-shrink:0; cursor:pointer; accent-color:#6d28d9;">
                            <span style="font-size:14px; color:#374151;"><?=$displayName?></span>
                        </label>
                        <?php } ?>
                    </div>
                    <button class="btn btn-primary mt-2 w-100 d-flex align-items-center justify-content-center gap-2" type="submit" id="add_user">
                        <i class='fa fa-plus'></i> Add Selected Members
                    </button>
                </div>
                <input type="hidden" name="near_miss_id" value="<?=$details['near_miss_id']?>">
            </form>
        </div>
    </div>

    <!-- Chat Messages -->
    <div class="col-12 col-lg-8 col-xl-9">
        <div class="chat-main-card h-100 flex-grow-1">
            
            <div class="card-header border-0 pt-5 px-5">
                <div class="d-flex align-items-start flex-column">
                    <h3 class="card-label fw-bolder text-dark m-0 fs-4 d-flex align-items-center">
                        <i class="fa fa-comments text-primary me-2"></i> Discussion
                    </h3>
                    <span class="text-muted mt-1 fw-bold fs-7">Collaborate and resolve this near miss report</span>
                </div>
            </div>
            
            <div class="card-body py-4 px-5 flex-grow-1" style="max-height: 500px; overflow-y: auto; display: flex; flex-direction: column;">
                <div class="chat-messages">
                    <?php 
                    foreach($near_miss_chat as $rows) { 
                        $isMe = (isset($login_id) && $rows['user_id'] == $login_id);
                        $bubbleClass = $isMe ? 'chat-bubble-me ms-auto' : 'chat-bubble-other me-auto';
                        $alignClass = $isMe ? 'justify-content-end' : 'justify-content-start';
                        
                        $designation = trim($rows['user_designation'] ?? '');
                        $roleBadgeClass = 'role-badge-engineer'; // default
                        $desigLower = strtolower($designation);
                        if(strpos($desigLower, 'admin') !== false) $roleBadgeClass = 'role-badge-admin';
                        elseif(strpos($desigLower, 'auditor') !== false) $roleBadgeClass = 'role-badge-auditor';

                        $initial = strtoupper(substr(trim(esc($rows['user_name'] ?? 'U')), 0, 1));
                        if(empty($initial)) $initial = '?';
                    ?>
                    
                    <div class="d-flex <?=$alignClass?> mb-5 w-100">
                        <?php if(!$isMe): ?>
                        <div class="me-3">
                            <div class="d-flex justify-content-center align-items-center rounded-circle" style="width:40px; height:40px; background:#6b21a8; color:#fff; font-weight:bold; font-size:16px;">
                                <?=$initial?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-flex flex-column align-items-<?=$isMe ? 'end' : 'start'?>" style="max-width: 75%;">
                            <div class="d-flex align-items-center mb-1 gap-2">
                                <?php if($isMe): ?>
                                    <span class="text-gray-800 fw-bold fs-6">You</span>
                                    <?php if($designation !== ''): ?>
                                        <span class="badge <?=$roleBadgeClass?> rounded px-2 py-1 fs-8"><?=$designation?></span>
                                    <?php endif; ?>
                                    <span class="text-muted fs-8 ms-2"><?=date('M d, H:i', strtotime($rows['default_date']))?></span>
                                <?php else: ?>
                                    <span class="text-gray-800 fw-bold fs-6"><?=$rows['user_name']?></span>
                                    <?php if($designation !== ''): ?>
                                        <span class="badge <?=$roleBadgeClass?> rounded px-2 py-1 fs-8"><?=$designation?></span>
                                    <?php endif; ?>
                                    <span class="text-muted fs-8 ms-2"><?=date('M d, H:i', strtotime($rows['default_date']))?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="<?=$bubbleClass?> shadow-sm">
                                <?php if (!empty($rows['chat_text'])): ?>
                                    <div class="fs-6" style="white-space:pre-wrap; word-break:break-word;"><?=$rows['chat_text']?></div>
                                <?php endif; ?>
                                
                                <?php if (isset($rows['chat_type']) && (int)$rows['chat_type'] === 1) { ?>
                                    <div class="mt-2">
                                        <a href="<?=$rows['chat_image']?>" target="_blank">
                                            <img src="<?=$rows['chat_image']?>" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                                        </a>
                                    </div>
                                <?php } elseif (isset($rows['chat_type']) && (int)$rows['chat_type'] === 2) { 
                                    $meta = json_decode($rows['chat_image'], true);
                                    if ($meta) {
                                        $ext = strtolower(pathinfo($meta['original_name'], PATHINFO_EXTENSION));
                                        $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                ?>
                                    <?php if ($isImg): ?>
                                        <div class="mt-2 text-center">
                                            <a href="<?=base_url($meta['path'])?>" target="_blank">
                                                <img src="<?=base_url($meta['path'])?>" class="img-fluid rounded" style="max-height: 150px; object-fit: contain;">
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="chat-attachment-card shadow-sm">
                                        <div class="d-flex align-items-center justify-content-center rounded" style="width:40px; height:40px; background:#f3f4f6;">
                                            <?php
                                                $icon = 'fa-file';
                                                $iconColor = 'text-muted';
                                                if (in_array($ext, ['pdf'])) { $icon = 'fa-file-pdf'; $iconColor = 'text-danger'; }
                                                elseif (in_array($ext, ['doc','docx'])) { $icon = 'fa-file-word'; $iconColor = 'text-primary'; }
                                                elseif (in_array($ext, ['xls','xlsx'])) { $icon = 'fa-file-excel'; $iconColor = 'text-success'; }
                                                elseif (in_array($ext, ['ppt','pptx'])) { $icon = 'fa-file-powerpoint'; $iconColor = 'text-warning'; }
                                                elseif ($isImg) { $icon = 'fa-image'; $iconColor = 'text-info'; }
                                                elseif (in_array($ext, ['zip'])) { $icon = 'fa-file-archive'; $iconColor = 'text-secondary'; }
                                            ?>
                                            <i class="fa <?=$icon?> <?=$iconColor?> fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden" style="min-width: 100px;">
                                            <div class="fw-bold text-truncate text-dark fs-7" title="<?=esc($meta['original_name'])?>"><?=esc($meta['original_name'])?></div>
                                            <div class="text-muted" style="font-size:11px;"><?=isset($meta['size']) ? number_format($meta['size']/1024, 1).' KB' : strtoupper($ext).' File'?></div>
                                        </div>
                                        <div class="d-flex gap-2 ms-2">
                                            <?php if($isImg || $ext == 'pdf'): ?>
                                            <a href="<?=base_url($meta['path'])?>" target="_blank" class="btn btn-icon btn-sm btn-light text-primary" style="background:#eff6ff;" title="View">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="<?=base_url(index_page().'/Reports/Near_miss_reports/downloadChatAttachment/'.$rows['chat_id'])?>" class="btn btn-icon btn-sm btn-light text-primary" style="background:#eff6ff;" title="Download">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php } } ?>
                            </div>
                        </div>
                        
                        <?php if($isMe): ?>
                        <div class="ms-3">
                            <div class="d-flex justify-content-center align-items-center rounded-circle" style="width:40px; height:40px; background:#1e3a8a; color:#fff; font-weight:bold; font-size:16px;">
                                <?=$initial?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php } ?>
                    
                    <?php if(empty($near_miss_chat)): ?>
                        <div class="text-center text-muted p-5 my-5">
                            <i class="fa fa-comments-o fa-3x mb-3 text-light"></i>
                            <p class="mb-0 fs-5">No messages yet. Start the conversation!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if($details['status'] != 5): ?>
            <div class="card-footer border-top p-4 bg-white rounded-bottom-3" style="border-top: 1px solid #e5e7eb !important;">
                <form action="<?=$form_action?>" method="post" enctype="multipart/form-data" id="chatForm">
                    <div class="chat-input-wrapper">
                        <textarea name="comment_text" class="chat-input fs-6" rows="1" placeholder="Type your message here..." style="min-height: 44px; max-height: 120px;" oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
                        
                        <div class="d-flex align-items-center gap-3 px-3 pb-2">
                            <a href="javascript:void(0);" class="text-muted text-hover-primary" title="Insert Emoji (Optional)">
                                <i class="fa fa-smile-o fs-4"></i>
                            </a>
                            <a href="javascript:void(0);" class="text-muted text-hover-primary" title="Attach File" onclick="$('#chat_attachment_input').click();">
                                <i class="fa fa-paperclip fs-4"></i>
                            </a>
                            <button type="submit" class="btn btn-primary d-flex justify-content-center align-items-center rounded" style="width:40px; height:40px; padding:0;" title="Send Message">
                                <i class="fa fa-paper-plane m-0 fs-5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden file input -->
                    <input type="file" name="chat_attachment[]" id="chat_attachment_input" class="d-none" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip" onchange="handleFileSelect(this)" />

                    <!-- Drag & Drop Zone (Expandable) -->
                    <div class="drag-drop-zone" id="dragDropZone">
                        <div class="d-flex align-items-center justify-content-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px; height:40px; background:#3b82f6; color:#fff;">
                                <i class="fa fa-upload"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-bold text-dark">Drag & drop files here or <span class="text-primary cursor-pointer" onclick="$('#chat_attachment_input').click();">Choose Files</span></div>
                                <div class="text-muted fs-8">You can upload images, PDF, DOC, PPT, XLS, etc. (Max 10 MB per file)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Selected File UI -->
                    <div class="selected-files-zone" id="selectedFilesZone">
                        <div class="fs-7 fw-bold text-gray-700 mb-2" id="selectedFilesCount">Selected Files (0)</div>
                        <div id="selectedFilesList" class="d-flex flex-wrap gap-2">
                            <!-- Files will be dynamically injected here via JS -->
                        </div>
                    </div>
                    
                    <input type="hidden" value="<?=$details['near_miss_id']?>" name="near_miss_id">
                </form>
            </div>
            <?php else: ?>
            <div class="card-footer border-top p-4 bg-light rounded-bottom-3 text-center">
                <span class="text-muted fw-bold"><i class="fa fa-lock me-2"></i> Chat is disabled for this record.</span>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>
 
<?php $this->endSection();?>
	
<?php $this->section("javascript_section");?>
<script>
$(()=>{
    values='<?=$details['group_member_ids']?>';
    $('#assigned_engineer_id').val(values.split(',')).trigger('change');
    
    // Auto-scroll chat to bottom
    var chatContainer = $('.card-body.flex-grow-1')[0];
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
})


$("#add_user").on('click',()=>{
    $("#user_list_involved").submit();
});

// Drag and drop / file selection UI logic
const dragDropZone = document.getElementById('dragDropZone');
const chatForm = document.getElementById('chatForm');

if (dragDropZone && chatForm) {
    // Show drag drop zone on drag over
    chatForm.addEventListener('dragover', (e) => {
        e.preventDefault();
        dragDropZone.style.display = 'block';
        dragDropZone.style.background = '#dbeafe';
    });

    chatForm.addEventListener('dragleave', (e) => {
        e.preventDefault();
        if (!e.relatedTarget || !chatForm.contains(e.relatedTarget)) {
            dragDropZone.style.display = 'none';
        }
    });

    chatForm.addEventListener('drop', (e) => {
        e.preventDefault();
        dragDropZone.style.display = 'none';
        
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            const input = document.getElementById('chat_attachment_input');
            input.files = e.dataTransfer.files;
            handleFileSelect(input);
        }
    });
}

let currentFiles = new DataTransfer();

function handleFileSelect(input) {
    if (input.files.length > 0) {
        // Add new files to the DataTransfer object
        for (let i = 0; i < input.files.length; i++) {
            currentFiles.items.add(input.files[i]);
        }
        
        // Update the input element with all files
        input.files = currentFiles.files;
        
        renderSelectedFiles();
    }
}

function renderSelectedFiles() {
    const zone = document.getElementById('selectedFilesZone');
    const list = document.getElementById('selectedFilesList');
    const countText = document.getElementById('selectedFilesCount');
    
    list.innerHTML = '';
    
    if (currentFiles.files.length > 0) {
        document.getElementById('dragDropZone').style.display = 'none';
        zone.style.display = 'block';
        countText.textContent = `Selected Files (${currentFiles.files.length})`;
        
        for (let i = 0; i < currentFiles.files.length; i++) {
            const file = currentFiles.files[i];
            const sizeKB = (file.size / 1024).toFixed(1);
            const ext = file.name.split('.').pop().toLowerCase();
            
            let iconClass = 'fa-file-alt text-danger';
            let bgClass = 'bg-light-danger';
            if (['jpg','jpeg','png','gif','webp'].includes(ext)) { iconClass = 'fa-image text-info'; bgClass = 'bg-light-info'; }
            else if (['doc','docx'].includes(ext)) { iconClass = 'fa-file-word text-primary'; bgClass = 'bg-light-primary'; }
            else if (['xls','xlsx'].includes(ext)) { iconClass = 'fa-file-excel text-success'; bgClass = 'bg-light-success'; }
            else if (['ppt','pptx'].includes(ext)) { iconClass = 'fa-file-powerpoint text-warning'; bgClass = 'bg-light-warning'; }
            else if (['zip'].includes(ext)) { iconClass = 'fa-file-archive text-secondary'; bgClass = 'bg-light-dark'; }
            
            const fileHTML = `
                <div class="selected-file-pill border border-gray-300 rounded-3 p-2 d-flex align-items-center gap-2" style="background:#fff; min-width: 200px;">
                    <div class="d-flex align-items-center justify-content-center rounded ${bgClass}" style="width:36px; height:36px; background:#f3f4f6;">
                        <i class="fa ${iconClass} fs-5"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-bold text-dark fs-7 text-truncate" title="${file.name}" style="max-width: 150px;">${file.name}</div>
                        <div class="text-muted" style="font-size:11px;">${sizeKB} KB</div>
                    </div>
                    <a href="javascript:void(0);" class="text-muted text-hover-danger ms-2 p-1" onclick="removeSelectedFile(${i});" title="Remove File">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', fileHTML);
        }
    } else {
        zone.style.display = 'none';
    }
}

function removeSelectedFile(index) {
    const dt = new DataTransfer();
    
    // Copy all files except the one we are removing
    for (let i = 0; i < currentFiles.files.length; i++) {
        if (i !== index) {
            dt.items.add(currentFiles.files[i]);
        }
    }
    
    currentFiles = dt;
    document.getElementById('chat_attachment_input').files = currentFiles.files;
    
    renderSelectedFiles();
}
</script>
<?php $this->endSection();?>
