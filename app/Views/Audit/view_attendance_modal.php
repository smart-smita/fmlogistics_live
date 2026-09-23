<div class="modal-header bg-info text-white">
    <h5 class="modal-title"><span class="text-white">📄 Audit Attendance Details</span></h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <!-- Header Card -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body bg-light rounded">
            <div class="row">
                <div class="col-md-4 mb-2"><strong>Audit No:</strong> <span class="text-dark"><?= esc($master['audit_no']) ?></span></div>
                <div class="col-md-4 mb-2"><strong>Audit Type Name:</strong> <span class="text-dark"><?= esc($master['audit_name']) ?></span></div>
                <div class="col-md-4 mb-2"><strong>Auditor Name:</strong> <span class="text-dark"><?= esc($master['auditor_name']) ?></span></div>
                <div class="col-md-4 mb-2"><strong>Auditee Name:</strong> <span class="text-dark"><?= esc($master['auditee_name']) ?></span></div>
                <div class="col-md-4 mb-2"><strong>Audit Date:</strong> <span class="text-dark"><?= esc($master['audit_date']) ?></span></div>
                <div class="col-md-4 mb-2"><strong>Region:</strong> <span class="text-dark"><?= esc($master['region']) ?></span></div>
                <div class="col-md-4 mb-2"><strong>Site Category:</strong> <span class="text-dark"><?= esc($master['main_category']) ?></span></div>
                <div class="col-md-4 mb-2"><strong>Sub Category:</strong> <span class="text-dark"><?= esc($master['sub_category']) ?></span></div>
                <div class="col-md-4 mb-2"><strong>Client Name:</strong> <span class="text-dark"><?= esc($master['client_name']) ?></span></div>
            </div>
        </div>
    </div>
    
    <!-- Table -->
    <h5 class="text-dark fw-bold mb-3">Attendance Records</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark sticky-top">
                <tr>
                    <th style="width: 5%;">Sr. No.</th>
                    <th style="width: 25%;">Auditee Attendance</th>
                    <th style="width: 15%;">Opening Date</th>
                    <th style="width: 20%;">Opening Sign</th>
                    <th style="width: 15%;">Closing Date</th>
                    <th style="width: 20%;">Closing Sign</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($attendance)): ?>
                    <?php foreach($attendance as $row): ?>
                        <tr>
                            <td class="fw-bold"><?= esc($row['row_no']) ?></td>
                            <td class="text-start"><?= esc($row['auditee_attendance']) ?></td>
                            <td><?= !empty($row['opening_date']) ? esc(date('d-m-Y', strtotime($row['opening_date']))) : '<span class="text-muted">N/A</span>' ?></td>
                            <td>
                                <?php if(!empty($row['opening_sign'])): ?>
                                    <div class="text-center">
                                        <a href="<?= base_url($row['opening_sign']) ?>" target="_blank">
                                            <img src="<?= base_url($row['opening_sign']) ?>" alt="Opening Sign" class="img-thumbnail" style="max-height: 80px; max-width: 100%;">
                                        </a>
                                        <div class="mt-1">
                                            <a href="<?= base_url($row['opening_sign']) ?>" target="_blank" class="btn btn-sm btn-primary px-2 py-1" style="font-size: 12px;"><i class="fa fa-eye"></i> View File</a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?= !empty($row['closing_date']) ? esc(date('d-m-Y', strtotime($row['closing_date']))) : '<span class="text-muted">N/A</span>' ?></td>
                            <td>
                                <?php if(!empty($row['closing_sign'])): ?>
                                    <div class="text-center">
                                        <a href="<?= base_url($row['closing_sign']) ?>" target="_blank">
                                            <img src="<?= base_url($row['closing_sign']) ?>" alt="Closing Sign" class="img-thumbnail" style="max-height: 80px; max-width: 100%;">
                                        </a>
                                        <div class="mt-1">
                                            <a href="<?= base_url($row['closing_sign']) ?>" target="_blank" class="btn btn-sm btn-primary px-2 py-1" style="font-size: 12px;"><i class="fa fa-eye"></i> View File</a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted p-4">No attendance records found for this audit.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
