<!-- Application Status Feedback Modal Popup -->
<div class="modal fade" id="appStatusFeedbackModal" tabindex="-1" aria-labelledby="appStatusFeedbackModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-850px">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden; max-height: 90vh;">
            <div class="modal-header border-0 pb-3 pt-5 px-6 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);">
                <div class="d-flex align-items-center gap-3 py-1">
                    <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-20 text-white rounded-circle" style="width: 44px; height: 44px; min-width: 44px;">
                        <i class="fas fa-clipboard-check fs-3 text-white" style="color: #ffffff !important;"></i>
                    </div>
                    <div>
                        <h4 class="modal-title fw-bolder text-white mb-0" id="appStatusFeedbackModalLabel" style="color: #ffffff !important;">Application Status Feedback</h4>
                        <div class="text-white text-opacity-75 fs-7" style="color: rgba(255,255,255,0.85) !important;">Please verify module performance & data accuracy (One-Time Verification)</div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-icon btn-color-white btn-active-color-primary bg-white bg-opacity-10 rounded-circle" onclick="dismissAppStatusFeedbackModal()" title="Close">
                    <i class="fas fa-times fs-6 text-white" style="color: #ffffff !important;"></i>
                </button>
            </div>
            
            <form id="appStatusFeedbackForm" onsubmit="return false;" style="display: contents;">
                <div class="modal-body py-5 px-6 scroll-y" style="max-height: calc(90vh - 140px);">
                    
                    <div class="alert alert-dismissible bg-light-primary border border-primary border-dashed d-flex flex-column flex-sm-row p-4 mb-5 rounded-3">
                        <i class="fas fa-info-circle fs-2 text-primary me-3 mb-2 mb-sm-0 align-self-center"></i>
                        <div class="d-flex flex-column pe-0 pe-sm-10">
                            <span class="fw-bold text-gray-800 fs-7">Help us confirm the system status across all modules</span>
                            <span class="text-muted fs-8">Please answer all 12 questions below with <strong>Yes</strong>, <strong>No</strong>, or <strong>NA</strong>. Your answers are submitted once and securely recorded.</span>
                        </div>
                    </div>

                    <div id="appStatusValidationAlert" class="alert alert-danger d-none py-3 px-4 mb-4 rounded-3 fs-7 fw-semibold">
                        <i class="fas fa-exclamation-triangle me-2"></i> <span id="appStatusValidationMsg">Please answer all questions before submitting.</span>
                    </div>

                    <?php
                    $questions = [
                        1 => 'Is the OE Audit module working properly?',
                        2 => 'Is the OE Re-Audit functionality working properly?',
                        3 => 'Is the HSE Audit module working properly?',
                        4 => 'Is the HSE Re-Audit functionality working properly?',
                        5 => 'Is the Gemba Audit module working properly?',
                        6 => 'Are the dashboards and summary counts working properly?',
                        7 => 'Are the dashboard graphs and charts displaying correctly?',
                        8 => 'Are the NC trackers and NC status updates working properly?',
                        9 => 'Are reports, filters, and data exports working properly?',
                        10 => 'Are login, role permissions, and site-based data access working properly?',
                        11 => 'Are all assigned client/site details displaying correctly?',
                        12 => 'Is the overall application working properly?'
                    ];
                    ?>

                    <div class="d-flex flex-column gap-3 mb-5">
                        <?php foreach ($questions as $qNum => $qText): ?>
                            <div class="card card-bordered border-gray-300 rounded-3 p-4 bg-hover-light-secondary transition-all q-card" id="q_card_<?= $qNum ?>" style="transition: all 0.2s ease;">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                    <div class="d-flex align-items-start gap-2 flex-grow-1">
                                        <span class="badge badge-light-primary fw-bolder fs-7 px-2 py-1" style="min-width: 28px; text-align: center;"><?= $qNum ?></span>
                                        <label class="form-label mb-0 fw-semibold text-gray-800 fs-7 cursor-pointer" for="q<?= $qNum ?>_yes">
                                            <?= htmlspecialchars($qText) ?> <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0 ps-7 ps-md-0">
                                        <div class="btn-group w-100 w-sm-auto q-btn-group shadow-xs" role="group" aria-label="Question <?= $qNum ?> response">
                                            <!-- Yes Option -->
                                            <input type="radio" class="btn-check q-radio" name="q<?= $qNum ?>" id="q<?= $qNum ?>_yes" value="Yes" autocomplete="off" onchange="clearQCardError(<?= $qNum ?>)">
                                            <label class="btn btn-sm btn-outline-success px-3 py-2 fw-bold opt-btn opt-yes" for="q<?= $qNum ?>_yes">
                                                <i class="fas fa-check me-1 fs-8"></i> Yes
                                            </label>

                                            <!-- No Option -->
                                            <input type="radio" class="btn-check q-radio" name="q<?= $qNum ?>" id="q<?= $qNum ?>_no" value="No" autocomplete="off" onchange="clearQCardError(<?= $qNum ?>)">
                                            <label class="btn btn-sm btn-outline-danger px-3 py-2 fw-bold opt-btn opt-no" for="q<?= $qNum ?>_no">
                                                <i class="fas fa-times me-1 fs-8"></i> No
                                            </label>

                                            <!-- NA Option (Clear dark text with solid high-contrast border) -->
                                            <input type="radio" class="btn-check q-radio" name="q<?= $qNum ?>" id="q<?= $qNum ?>_na" value="NA" autocomplete="off" onchange="clearQCardError(<?= $qNum ?>)">
                                            <label class="btn btn-sm btn-outline-secondary px-3 py-2 fw-bold opt-btn opt-na" for="q<?= $qNum ?>_na">
                                                <i class="fas fa-minus me-1 fs-8"></i> NA
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Additional Comments Section (Optional) -->
                    <div class="mb-2">
                        <label class="form-label fw-bold text-gray-800 fs-6 mb-2">
                            <i class="fas fa-comment-alt text-primary me-1"></i> Additional Comments / Issues <span class="text-muted fw-normal fs-7">(Optional)</span>
                        </label>
                        <textarea class="form-control form-control-solid rounded-3" name="comments" id="appStatusCommentsInput" rows="3" placeholder="Describe any problems or observations you encounter in detail..."></textarea>
                        <div class="form-text text-muted fs-8">If you marked 'No' for any item, please let us know what went wrong so we can resolve it quickly.</div>
                    </div>

                </div>

                <div class="modal-footer border-0 pt-2 pb-5 px-6 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light btn-active-light-primary px-5 py-3 rounded-pill fw-semibold order-2 order-sm-1" onclick="dismissAppStatusFeedbackModal()">
                        <i class="fas fa-clock me-1 fs-7"></i> Cancel / Not Now
                    </button>
                    <button type="button" class="btn btn-primary px-7 py-3 rounded-pill fw-bold order-1 order-sm-2" id="submitAppStatusFeedbackBtn" onclick="submitAppStatusFeedback()">
                        <span class="indicator-label"><i class="fas fa-paper-plane me-2"></i> Submit Status Feedback</span>
                        <span class="indicator-progress d-none">
                            Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* App Status Feedback Questionnaire Styling & High-Contrast Options */
#appStatusFeedbackModal .q-card {
    background-color: #ffffff;
    border: 1px solid #e2e8f0 !important;
}
#appStatusFeedbackModal .q-card:hover {
    border-color: #cbd5e1 !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
#appStatusFeedbackModal .opt-btn {
    min-width: 65px;
    font-size: 0.825rem;
    letter-spacing: 0.3px;
    transition: all 0.15s ease-in-out;
}

/* Unchecked NA Option: Prominent dark text and clear border */
#appStatusFeedbackModal .opt-na {
    color: #1e293b !important; /* Slate 800 - dark, clear, highly readable */
    border: 1.5px solid #94a3b8 !important; /* Crisp visible slate border */
    background-color: #f8fafc !important; /* Crisp light slate background */
}
#appStatusFeedbackModal .opt-na:hover {
    background-color: #e2e8f0 !important;
    color: #0f172a !important;
    border-color: #64748b !important;
}

/* Unchecked Yes & No */
#appStatusFeedbackModal .opt-yes {
    color: #047857 !important;
    border: 1.5px solid #10b981 !important;
    background-color: #f0fdf4 !important;
}
#appStatusFeedbackModal .opt-yes:hover {
    background-color: #dcfce7 !important;
    color: #065f46 !important;
}
#appStatusFeedbackModal .opt-no {
    color: #b91c1c !important;
    border: 1.5px solid #ef4444 !important;
    background-color: #fef2f2 !important;
}
#appStatusFeedbackModal .opt-no:hover {
    background-color: #fee2e2 !important;
    color: #991b1b !important;
}

/* Checked States */
#appStatusFeedbackModal .btn-check:checked + .opt-yes {
    background-color: #10b981 !important;
    border-color: #10b981 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.35);
}
#appStatusFeedbackModal .btn-check:checked + .opt-yes i {
    color: #ffffff !important;
}
#appStatusFeedbackModal .btn-check:checked + .opt-no {
    background-color: #ef4444 !important;
    border-color: #ef4444 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.35);
}
#appStatusFeedbackModal .btn-check:checked + .opt-no i {
    color: #ffffff !important;
}
#appStatusFeedbackModal .btn-check:checked + .opt-na {
    background-color: #334155 !important; /* Deep Slate 700 - clear distinct active state */
    border-color: #334155 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 4px rgba(51, 65, 85, 0.35);
}
#appStatusFeedbackModal .btn-check:checked + .opt-na i {
    color: #ffffff !important;
}

/* Error validation highlight */
#appStatusFeedbackModal .q-card.border-danger-dashed {
    border: 2px dashed #ef4444 !important;
    background-color: #fff1f2 !important;
}
#appStatusFeedbackModal .modal-header i,
#appStatusFeedbackModal .modal-header h4 {
    color: #ffffff !important;
}

@media (max-width: 576px) {
    #appStatusFeedbackModal .opt-btn {
        flex: 1 1 auto;
        min-width: 0;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
}
</style>

<script>
(function() {
    window.clearQCardError = function(qNum) {
        $('#q_card_' + qNum).removeClass('border-danger-dashed');
        if ($('.q-card.border-danger-dashed').length === 0) {
            $('#appStatusValidationAlert').addClass('d-none');
        }
    };

    window.dismissAppStatusFeedbackModal = function() {
        $('#appStatusFeedbackModal').modal('hide');
        // Notify backend to mark skipped in current session
        $.ajax({
            url: '<?= base_url("AppStatusFeedback/skip") ?>',
            type: 'POST',
            dataType: 'json'
        });
    };

    window.submitAppStatusFeedback = function() {
        var unanswered = [];
        for (var i = 1; i <= 12; i++) {
            if (!$('input[name="q' + i + '"]:checked').val()) {
                unanswered.push(i);
                $('#q_card_' + i).addClass('border-danger-dashed');
            } else {
                $('#q_card_' + i).removeClass('border-danger-dashed');
            }
        }

        if (unanswered.length > 0) {
            $('#appStatusValidationMsg').text('Please answer all 12 questions (Question ' + unanswered.join(', ') + ' not answered).');
            $('#appStatusValidationAlert').removeClass('d-none');
            
            // Scroll modal body to first unanswered question
            var firstCard = document.getElementById('q_card_' + unanswered[0]);
            if (firstCard) {
                firstCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        $('#appStatusValidationAlert').addClass('d-none');

        const btn = $('#submitAppStatusFeedbackBtn');
        btn.prop('disabled', true);
        btn.find('.indicator-label').addClass('d-none');
        btn.find('.indicator-progress').removeClass('d-none');

        const formData = $('#appStatusFeedbackForm').serialize();

        $.ajax({
            url: '<?= base_url("AppStatusFeedback/submit") ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false);
                btn.find('.indicator-label').removeClass('d-none');
                btn.find('.indicator-progress').addClass('d-none');

                if (res && res.status === 1) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Thank You!',
                            text: res.message || 'Your application status feedback has been submitted successfully.',
                            icon: 'success',
                            confirmButtonText: 'Great!',
                            customClass: { confirmButton: 'btn btn-primary px-6 rounded-pill' }
                        });
                    } else if (typeof toastr !== 'undefined') {
                        toastr.success(res.message);
                    } else {
                        alert(res.message);
                    }
                    $('#appStatusFeedbackModal').modal('hide');
                    $('#appStatusFeedbackMenuItem, #appStatusFeedbackMenuSeparator').remove();
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(res.message || "Failed to submit feedback");
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function() {
                btn.prop('disabled', false);
                btn.find('.indicator-label').removeClass('d-none');
                btn.find('.indicator-progress').addClass('d-none');
                if (typeof toastr !== 'undefined') {
                    toastr.error("An error occurred while submitting feedback. Please try again.");
                } else {
                    alert("An error occurred while submitting feedback.");
                }
            }
        });
    };

    // Auto-check application status feedback eligibility on page load
    $(document).ready(function() {
        setTimeout(function() {
            $.ajax({
                url: '<?= base_url("AppStatusFeedback/check_status") ?>',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res && res.status === 1) {
                        if (!res.has_submitted && !res.skipped_this_session) {
                            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                var modalEl = document.getElementById('appStatusFeedbackModal');
                                if (modalEl) {
                                    var modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
                                    modalObj.show();
                                }
                            } else {
                                $('#appStatusFeedbackModal').modal('show');
                            }
                        }
                    }
                }
            });
        }, 1200); // 1.2s delay for clean page initialization
    });
})();
</script>
