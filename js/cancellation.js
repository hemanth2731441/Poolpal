function showCancellationDialog(bookingId) {
    Swal.fire({
        title: 'Cancel Booking',
        html: `
            <div class="cancellation-modal">
                <p style="color: #666; margin-bottom: 1.5rem;">Please select a reason for cancelling this booking:</p>
                
                <div class="reason-category">
                    <div class="reason-category-title">
                        <i class="fas fa-exclamation-circle"></i>
                        Common Reasons
                    </div>
                    <div class="cancellation-reasons">
                        <div class="reason-option">
                            <input type="radio" id="reason1" name="cancellation_reason" value="Change in travel plans">
                            <label for="reason1">
                                <i class="fas fa-calendar-alt"></i>
                                Change in travel plans
                            </label>
                        </div>
                        <div class="reason-option">
                            <input type="radio" id="reason2" name="cancellation_reason" value="Found alternative transport">
                            <label for="reason2">
                                <i class="fas fa-exchange-alt"></i>
                                Found alternative transport
                            </label>
                        </div>
                        <div class="reason-option">
                            <input type="radio" id="reason3" name="cancellation_reason" value="Emergency situation">
                            <label for="reason3">
                                <i class="fas fa-exclamation-triangle"></i>
                                Emergency situation
                            </label>
                        </div>
                        <div class="reason-option">
                            <input type="radio" id="reason4" name="cancellation_reason" value="Schedule conflict">
                            <label for="reason4">
                                <i class="fas fa-clock"></i>
                                Schedule conflict
                            </label>
                        </div>
                        <div class="reason-option">
                            <input type="radio" id="reason5" name="cancellation_reason" value="custom">
                            <label for="reason5">
                                <i class="fas fa-pen"></i>
                                Other reason
                            </label>
                        </div>
                    </div>
                </div>
                
                <div id="customReasonContainer">
                    <textarea id="customReason" 
                              placeholder="Please provide details about your cancellation reason (minimum 10 characters)"
                              maxlength="200"></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Confirm Cancellation',
        cancelButtonText: 'Keep Booking',
        reverseButtons: true,
        customClass: {
            container: 'cancellation-modal-container',
            popup: 'cancellation-modal-popup',
            title: 'cancellation-modal-title',
            htmlContainer: 'cancellation-modal-content',
            confirmButton: 'cancellation-modal-confirm',
            cancelButton: 'cancellation-modal-cancel'
        },
        didOpen: () => {
            // Add click handlers for reason options
            document.querySelectorAll('.reason-option').forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                option.addEventListener('click', () => {
                    // Remove selected class from all options
                    document.querySelectorAll('.reason-option').forEach(opt => 
                        opt.classList.remove('selected'));
                    // Add selected class to clicked option
                    option.classList.add('selected');
                    radio.checked = true;
                    
                    // Show/hide custom reason textarea
                    const customContainer = document.getElementById('customReasonContainer');
                    if (radio.value === 'custom') {
                        customContainer.classList.add('show');
                        document.getElementById('customReason').focus();
                    } else {
                        customContainer.classList.remove('show');
                    }
                });
            });
        },
        preConfirm: () => {
            const selectedReason = document.querySelector('input[name="cancellation_reason"]:checked');
            if (!selectedReason) {
                Swal.showValidationMessage('Please select a reason for cancellation');
                return false;
            }
            
            if (selectedReason.value === 'custom') {
                const customReason = document.getElementById('customReason').value.trim();
                if (customReason.length < 10) {
                    Swal.showValidationMessage('Please provide a detailed reason (minimum 10 characters)');
                    return false;
                }
                return customReason;
            }
            
            return selectedReason.value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Cancelling Booking...',
                html: 'Please wait while we process your cancellation.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="cancel_booking" value="1">
                <input type="hidden" name="booking_id" value="${bookingId}">
                <input type="hidden" name="cancellation_reason" value="${result.value}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
    return false;
} 