<!-- NC Action History Modal -->
<div class="modal fade" id="ncActionHistoryModal" tabindex="-1" aria-labelledby="ncActionHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-light-primary py-4">
                <h5 class="modal-title font-weight-bold text-primary" id="ncActionHistoryModalLabel">
                    <i class="fas fa-history me-2"></i> NC Action History & Audit Trail
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                <!-- Target Site & Metadata Header -->
                <div id="ncHistoryMetadata" class="alert alert-light border border-secondary mb-4 py-2 px-3 d-none">
                    <div class="row text-muted fs-7">
                        <div class="col-md-6"><strong>Site / Client:</strong> <span id="historySiteName">-</span></div>
                        <div class="col-md-6"><strong>Module:</strong> <span id="historyModuleName">-</span></div>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="ncHistorySpinner" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading history...</span>
                    </div>
                    <div class="text-muted mt-2 fs-7">Fetching action timeline...</div>
                </div>

                <!-- Empty State -->
                <div id="ncHistoryEmpty" class="text-center py-5 d-none">
                    <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                    <p class="text-muted fs-6">No action history recorded yet for this NC.</p>
                </div>

                <!-- Timeline Container -->
                <div id="ncHistoryTimeline" class="timeline timeline-border-dashed p-2 d-none">
                    <!-- Dynamic timeline items appended via JS -->
                </div>
            </div>
            <div class="modal-footer bg-light py-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showNcActionHistory(ncDetailId, moduleType, siteName) {
    var modal = new bootstrap.Modal(document.getElementById('ncActionHistoryModal'));
    modal.show();

    $('#ncHistorySpinner').removeClass('d-none');
    $('#ncHistoryEmpty').addClass('d-none');
    $('#ncHistoryTimeline').addClass('d-none').empty();
    $('#ncHistoryMetadata').addClass('d-none');

    $.ajax({
        url: '<?= base_url("Masters/Client/get_nc_action_history") ?>',
        type: 'GET',
        data: {
            nc_detail_id: ncDetailId,
            module_type: moduleType,
            site_name: siteName
        },
        dataType: 'json',
        success: function(response) {
            $('#ncHistorySpinner').addClass('d-none');
            
            if (response.status == 1 && response.history && response.history.length > 0) {
                $('#ncHistoryMetadata').removeClass('d-none');
                $('#historySiteName').text(response.site_name || siteName || 'N/A');
                $('#historyModuleName').text(moduleType || 'N/A');

                var html = '';
                $.each(response.history, function(index, item) {
                    var badgeClass = 'bg-secondary';
                    var icon = 'fa-info-circle';
                    
                    var act = (item.action_type || '').toUpperCase();
                    if (act.includes('CREATE')) { badgeClass = 'bg-primary'; icon = 'fa-plus-circle'; }
                    else if (act.includes('START') || act.includes('WORK')) { badgeClass = 'bg-warning text-dark'; icon = 'fa-tools'; }
                    else if (act.includes('EVIDENCE') || act.includes('PHOTO')) { badgeClass = 'bg-info text-white'; icon = 'fa-camera'; }
                    else if (act.includes('SUBMIT')) { badgeClass = 'bg-primary'; icon = 'fa-paper-plane'; }
                    else if (act.includes('REJECT')) { badgeClass = 'bg-danger'; icon = 'fa-times-circle'; }
                    else if (act.includes('FORWARD')) { badgeClass = 'bg-info'; icon = 'fa-arrow-right'; }
                    else if (act.includes('CLOSE')) { badgeClass = 'bg-success'; icon = 'fa-check-circle'; }

                    html += '<div class="timeline-item mb-4 border-start border-2 border-primary ps-3 ms-2">';
                    html += '  <div class="d-flex align-items-center mb-1">';
                    html += '    <span class="badge ' + badgeClass + ' me-2"><i class="fas ' + icon + ' me-1"></i>' + (item.action_type || 'ACTION') + '</span>';
                    html += '    <span class="text-dark fw-bold fs-6 me-2">' + (item.performed_by_user_name || 'User') + '</span>';
                    html += '    <span class="badge badge-light-secondary fs-8 me-auto">(' + (item.performed_by_designation || 'Staff') + ')</span>';
                    html += '    <span class="text-muted fs-7"><i class="far fa-clock me-1"></i>' + (item.created_at || '') + '</span>';
                    html += '  </div>';

                    if (item.previous_status || item.new_status) {
                        html += '  <div class="fs-7 text-secondary mb-1">Status: <code>' + (item.previous_status || 'Start') + '</code> ➔ <code class="text-primary">' + (item.new_status || 'Next') + '</code></div>';
                    }

                    if (item.action_remarks) {
                        html += '  <div class="bg-light rounded p-2 mt-1 fs-7 text-dark"><strong>Remarks:</strong> ' + item.action_remarks + '</div>';
                    }

                    if (item.evidence_attachment) {
                        var attUrl = '<?= base_url() ?>' + item.evidence_attachment;
                        html += '  <div class="mt-2"><a href="' + attUrl + '" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2 fs-7"><i class="fas fa-paperclip me-1"></i> View Attachment / Evidence</a></div>';
                    }
                    html += '</div>';
                });

                $('#ncHistoryTimeline').html(html).removeClass('d-none');
            } else {
                $('#ncHistoryEmpty').removeClass('d-none');
            }
        },
        error: function() {
            $('#ncHistorySpinner').addClass('d-none');
            $('#ncHistoryEmpty').removeClass('d-none').find('p').text('Error loading action history.');
        }
    });
}
</script>
