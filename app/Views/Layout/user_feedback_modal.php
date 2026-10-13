<!-- User Feedback Modal Popup -->
<div class="modal fade" id="userFeedbackModal" tabindex="-1" aria-labelledby="userFeedbackModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mw-600px">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 pb-0 pt-6 px-7 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
                <div class="d-flex align-items-center gap-3 py-2">
                    <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-20 text-white rounded-circle" style="width: 44px; height: 44px;">
                        <i class="fas fa-comment-dots fs-3 text-white"></i>
                    </div>
                    <div>
                        <h4 class="modal-title fw-bolder text-white mb-0" id="userFeedbackModalLabel">Share Your Feedback</h4>
                        <div class="text-white text-opacity-75 fs-7">Help us improve your FM Logistics portal experience</div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-icon btn-color-white btn-active-color-primary bg-white bg-opacity-10 rounded-circle" onclick="dismissUserFeedbackModal()" title="Close">
                    <i class="fas fa-times fs-6 text-white"></i>
                </button>
            </div>
            
            <form id="userFeedbackForm" onsubmit="return false;">
                <div class="modal-body py-6 px-7">
                    <!-- Rating Section -->
                    <div class="mb-6 text-center">
                        <label class="form-label fw-bold text-dark fs-6 d-block mb-2">Overall Experience <span class="text-danger">*</span></label>
                        <div class="d-inline-flex gap-2 align-items-center justify-content-center star-rating-container py-1 px-3 bg-light rounded-pill">
                            <span class="star-rating-item" data-value="1" title="1 - Poor"><i class="fas fa-star fs-2 text-muted"></i></span>
                            <span class="star-rating-item" data-value="2" title="2 - Fair"><i class="fas fa-star fs-2 text-muted"></i></span>
                            <span class="star-rating-item" data-value="3" title="3 - Good"><i class="fas fa-star fs-2 text-muted"></i></span>
                            <span class="star-rating-item" data-value="4" title="4 - Very Good"><i class="fas fa-star fs-2 text-muted"></i></span>
                            <span class="star-rating-item" data-value="5" title="5 - Excellent"><i class="fas fa-star fs-2 text-muted"></i></span>
                        </div>
                        <input type="hidden" name="rating" id="feedbackRatingInput" value="" />
                        <div id="ratingTextDesc" class="fs-7 text-muted mt-2 fw-semibold" style="min-height: 20px;">Tap a star to rate</div>
                        <div class="text-danger fs-8 mt-1 d-none" id="ratingErrorMsg">Please select a star rating.</div>
                    </div>

                    <!-- Category Section -->
                    <div class="mb-5">
                        <label class="form-label fw-bold text-dark fs-6 mb-2">Feedback Category <span class="text-danger">*</span></label>
                        <select class="form-select form-select-solid rounded-3" name="category" id="feedbackCategoryInput">
                            <option value="">Select a category...</option>
                            <option value="UI/Design">UI / Design & Usability</option>
                            <option value="Functionality">Feature Functionality</option>
                            <option value="Performance">System Speed & Performance</option>
                            <option value="Reports">Reports & Analytics</option>
                            <option value="Login/Access">Login & Access Permissions</option>
                            <option value="Other">Other / General Feedback</option>
                        </select>
                        <div class="text-danger fs-8 mt-1 d-none" id="categoryErrorMsg">Please select a category.</div>
                    </div>

                    <!-- Comments Section -->
                    <div class="mb-5">
                        <label class="form-label fw-bold text-dark fs-6 mb-2">Your Comments & Feedback <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-solid rounded-3" name="comments" id="feedbackCommentsInput" rows="3" placeholder="Tell us what you like or any issues you encountered..."></textarea>
                        <div class="text-danger fs-8 mt-1 d-none" id="commentsErrorMsg">Please enter your feedback comments.</div>
                    </div>

                    <!-- Improvement Suggestion Section (Optional) -->
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark fs-6 mb-2">Suggestions for Improvement <span class="text-muted fw-normal fs-7">(Optional)</span></label>
                        <textarea class="form-control form-control-solid rounded-3" name="suggestion" id="feedbackSuggestionInput" rows="2" placeholder="Any new features or improvements you'd like to see?"></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-6 px-7 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-light btn-active-light-primary px-5 py-3 rounded-pill fw-semibold" onclick="dismissUserFeedbackModal()">
                        <i class="fas fa-clock me-1 fs-7"></i> Cancel / Not Now
                    </button>
                    <button type="button" class="btn btn-primary px-7 py-3 rounded-pill fw-bold" id="submitUserFeedbackBtn" onclick="submitUserFeedback()">
                        <span class="indicator-label"><i class="fas fa-paper-plane me-2"></i> Submit Feedback</span>
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
.star-rating-container {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s ease;
}
.star-rating-item {
    transition: transform 0.15s ease;
    padding: 2px 4px;
}
.star-rating-item:hover {
    transform: scale(1.2);
}
.star-rating-item i {
    transition: color 0.15s ease;
}
.star-rating-item.active i {
    color: #f59e0b !important; /* Golden Amber */
}
.star-rating-item.hover i {
    color: #fbbf24 !important;
}
</style>

<script>
(function() {
    let currentRating = 0;
    const ratingTexts = {
        1: "Poor - Needs significant improvement",
        2: "Fair - Has several issues",
        3: "Good - Meets expectations",
        4: "Very Good - Smooth experience",
        5: "Excellent - Outstanding performance!"
    };

    // Star rating interactions
    $(document).on('mouseenter', '.star-rating-item', function() {
        const val = parseInt($(this).data('value'));
        $('.star-rating-item').each(function() {
            const itemVal = parseInt($(this).data('value'));
            if (itemVal <= val) {
                $(this).addClass('hover');
            } else {
                $(this).removeClass('hover');
            }
        });
        $('#ratingTextDesc').text(ratingTexts[val] || "");
    });

    $(document).on('mouseleave', '.star-rating-container', function() {
        $('.star-rating-item').removeClass('hover');
        if (currentRating > 0) {
            $('.star-rating-item').each(function() {
                const itemVal = parseInt($(this).data('value'));
                if (itemVal <= currentRating) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
            $('#ratingTextDesc').text(ratingTexts[currentRating]);
        } else {
            $('.star-rating-item').removeClass('active');
            $('#ratingTextDesc').text("Tap a star to rate");
        }
    });

    $(document).on('click', '.star-rating-item', function() {
        currentRating = parseInt($(this).data('value'));
        $('#feedbackRatingInput').val(currentRating);
        $('#ratingErrorMsg').addClass('d-none');
        
        $('.star-rating-item').each(function() {
            const itemVal = parseInt($(this).data('value'));
            if (itemVal <= currentRating) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });
        $('#ratingTextDesc').text(ratingTexts[currentRating]);
    });

    window.dismissUserFeedbackModal = function() {
        $('#userFeedbackModal').modal('hide');
        // Notify backend that user dismissed for this login session
        $.ajax({
            url: '<?= base_url("Feedback/skip") ?>',
            type: 'POST',
            dataType: 'json'
        });
    };

    window.submitUserFeedback = function() {
        let hasError = false;
        const rating = parseInt($('#feedbackRatingInput').val() || 0);
        const category = $('#feedbackCategoryInput').val();
        const comments = $.trim($('#feedbackCommentsInput').val());
        const suggestion = $.trim($('#feedbackSuggestionInput').val());

        // Reset errors
        $('#ratingErrorMsg').addClass('d-none');
        $('#categoryErrorMsg').addClass('d-none');
        $('#commentsErrorMsg').addClass('d-none');

        if (rating < 1 || rating > 5) {
            $('#ratingErrorMsg').removeClass('d-none');
            hasError = true;
        }

        if (!category) {
            $('#categoryErrorMsg').removeClass('d-none');
            hasError = true;
        }

        if (!comments) {
            $('#commentsErrorMsg').removeClass('d-none');
            hasError = true;
        }

        if (hasError) return;

        const btn = $('#submitUserFeedbackBtn');
        btn.prop('disabled', true);
        btn.find('.indicator-label').addClass('d-none');
        btn.find('.indicator-progress').removeClass('d-none');

        $.ajax({
            url: '<?= base_url("Feedback/submit") ?>',
            type: 'POST',
            data: {
                rating: rating,
                category: category,
                comments: comments,
                suggestion: suggestion
            },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false);
                btn.find('.indicator-label').removeClass('d-none');
                btn.find('.indicator-progress').addClass('d-none');

                if (res.status === 1) {
                    $('#userFeedbackModal').modal('hide');
                    $('#userFeedbackForm')[0].reset();
                    $('#feedbackRatingInput').val('');
                    $('.star-rating-item i').removeClass('fas text-warning').addClass('far text-muted');
                    $('#ratingTextDesc').text('Tap a star to rate');
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || "Thank you for your feedback!");
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Thank You!',
                            text: res.message || "Your feedback has been submitted successfully.",
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
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

    // Auto-check feedback popup removed per instruction:
    // Only Application Status Feedback modal opens automatically on page load.
    // User Feedback modal is opened manually via profile menu 'Give Feedback'.
})();
</script>
