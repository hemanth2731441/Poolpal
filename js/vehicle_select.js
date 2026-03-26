/**
 * PoolPal Vehicle Selection Page JavaScript
 * Adds animations, transitions and interactive effects
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('vehicleTypeForm');
    const mainOptions = document.querySelectorAll('.vehicle-main-option');
    const allOptions = document.querySelectorAll('input[type="radio"][name="vehicle_type"]');
    
    // Function to hide all suboptions
    function hideAllSuboptions() {
        document.querySelectorAll('.vtype-suboptions').forEach(sub => {
            sub.style.display = 'none';
        });
    }

    // Function to remove selected state from all options
    function removeAllSelected() {
        document.querySelectorAll('.vtype-option, .vtype-suboption').forEach(option => {
            option.classList.remove('selected');
        });
    }

    // Handle main option selection
    document.querySelectorAll('.vtype-option').forEach(option => {
        const mainRadio = option.querySelector('.vehicle-main-option');
        
        option.addEventListener('click', function(e) {
            const radio = this.querySelector('input[type="radio"]');
            if (!radio.checked) {
                radio.checked = true;
                
                // If this is a main option with suboptions
                if (mainRadio && mainRadio.dataset.hasSuboptions === 'true') {
                    hideAllSuboptions();
                    removeAllSelected();
                    this.classList.add('selected');

                    const suboptionsClass = radio.id + '-suboptions';
                    const suboptions = document.querySelector('.' + suboptionsClass);
                    if (suboptions) {
                        suboptions.style.display = 'block';
                        // Uncheck any previously selected suboptions
                        suboptions.querySelectorAll('input[type="radio"]').forEach(subRadio => {
                            subRadio.checked = false;
                        });
                    }
                } else if (!mainRadio) {
                    // For options without suboptions
                    hideAllSuboptions();
                    removeAllSelected();
                    this.classList.add('selected');
                }
            }
            e.stopPropagation();
        });
    });

    // Handle sub-option selection
    document.querySelectorAll('.vtype-suboption').forEach(suboption => {
        suboption.addEventListener('click', function(e) {
            const radio = this.querySelector('input[type="radio"]');
            if (!radio.checked) {
                radio.checked = true;
                
                // Find and update parent option
                const parentSuboptions = this.closest('.vtype-suboptions');
                if (parentSuboptions) {
                    const parentOption = parentSuboptions.previousElementSibling;
                    removeAllSelected();
                    if (parentOption) {
                        parentOption.classList.add('selected');
                    }
                    this.classList.add('selected');
                }
            }
            e.stopPropagation();
        });
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        const selectedOption = document.querySelector('input[type="radio"][name="vehicle_type"]:checked');
        
        if (!selectedOption) {
            e.preventDefault();
            alert('Please select a vehicle type');
            return;
        }

        // Additional validation for main options with suboptions
        if (selectedOption.classList.contains('vehicle-main-option') && 
            selectedOption.dataset.hasSuboptions === 'true') {
            const suboptionsClass = selectedOption.id + '-suboptions';
            const suboptions = document.querySelector('.' + suboptionsClass);
            const hasSelectedSuboption = suboptions.querySelector('input[type="radio"]:checked');
            
            if (!hasSelectedSuboption) {
                e.preventDefault();
                alert('Please select a specific option under ' + selectedOption.value);
            }
        }
    });

    // Add animation classes on load
    document.querySelectorAll('.vtype-option').forEach((option, index) => {
        option.style.animationDelay = `${index * 0.1}s`;
        option.classList.add('animate__animated', 'animate__fadeInUp');
    });

    // Initialize animations with staggered timing
    function initAnimations() {
        // Animate title and subtitle
        const title = document.querySelector('.vs-title');
        const subtitle = document.querySelector('.vs-subtitle');
        
        if (title) title.classList.add('vs-fade-in');
        if (subtitle) {
            subtitle.classList.add('vs-fade-in');
            subtitle.style.animationDelay = '0.1s';
        }
        
        // Animate vehicle options with staggered delay
        const options = document.querySelectorAll('.vs-option');
        options.forEach((option, index) => {
            option.classList.add('vs-scale-in');
            option.style.animationDelay = `${0.2 + (index * 0.1)}s`;
        });
        
        // Animate button and back link
        const button = document.querySelector('.vs-btn');
        const backLink = document.querySelector('.vs-back');
        
        if (button) {
            button.classList.add('vs-slide-up');
            button.style.animationDelay = `${0.2 + (options.length * 0.1)}s`;
        }
        
        if (backLink) {
            backLink.classList.add('vs-fade-in');
            backLink.style.animationDelay = `${0.3 + (options.length * 0.1)}s`;
        }
    }
    
    // Make the whole vehicle option clickable
    function setupVehicleOptions() {
        const vehicleOptions = document.querySelectorAll('.vs-option');
        
        vehicleOptions.forEach(option => {
            // Click handler for the option
            option.addEventListener('click', function(e) {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    
                    // Add ripple effect on click
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const ripple = document.createElement('span');
                    ripple.classList.add('vs-ripple');
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                }
            });
            
            // Hover effect for icons
            const icon = option.querySelector('.vs-icon');
            if (icon) {
                option.addEventListener('mouseenter', () => {
                    icon.classList.add('vs-icon-hover');
                });
                
                option.addEventListener('mouseleave', () => {
                    icon.classList.remove('vs-icon-hover');
                });
            }
        });
    }
    
    // Form validation with visual feedback
    function setupFormValidation() {
        const form = document.getElementById('vehicleTypeForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const selectedOption = form.querySelector('input[name="vehicle_type"]:checked');
                const submitButton = form.querySelector('.vs-btn');
                
                if (!selectedOption) {
                    e.preventDefault();
                    
                    // Visual feedback for error
                    submitButton.classList.add('vs-btn-error');
                    
                    // Show error message
                    let errorMsg = document.querySelector('.vs-error-msg');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.classList.add('vs-error-msg');
                        errorMsg.textContent = 'Please select a vehicle type';
                        form.insertBefore(errorMsg, submitButton);
                        
                        // Animate error message
                        errorMsg.classList.add('vs-fade-in');
                    }
                    
                    // Remove error state after delay
                    setTimeout(() => {
                        submitButton.classList.remove('vs-btn-error');
                    }, 1000);
                } else {
                    // Add loading state to button
                    submitButton.classList.add('vs-btn-loading');
                    submitButton.innerHTML = 'Processing <span class="vs-spinner"></span>';
                }
            });
        }
    }
    
    // Add parallax effect to the card background
    function setupParallaxEffect() {
        const card = document.querySelector('.vs-card');
        
        if (card) {
            document.addEventListener('mousemove', function(e) {
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;
                
                card.style.transform = `perspective(1000px) rotateX(${y * 2 - 1}deg) rotateY(${-(x * 2 - 1)}deg) scale(1.01)`;
            });
            
            // Reset transform when mouse leaves
            document.addEventListener('mouseleave', function() {
                card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
            });
        }
    }
    
    // Initialize all features
    initAnimations();
    setupVehicleOptions();
    setupFormValidation();
    
    // Only add parallax on desktop devices
    if (window.innerWidth > 992) {
        setupParallaxEffect();
    }
    
    // Add CSS for dynamic elements
    const style = document.createElement('style');
    style.textContent = `
        .vs-ripple {
            position: absolute;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.7);
            transform: scale(0);
            animation: vs-ripple 0.6s linear;
            pointer-events: none;
            width: 100px;
            height: 100px;
            margin-left: -50px;
            margin-top: -50px;
        }
        
        @keyframes vs-ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        .vs-icon-hover {
            animation: vs-icon-bounce 0.5s ease;
        }
        
        @keyframes vs-icon-bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        .vs-btn-error {
            animation: vs-shake 0.5s ease-in-out;
            background: linear-gradient(135deg, #ff5252, #ff1744) !important;
        }
        
        @keyframes vs-shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        
        .vs-error-msg {
            color: #ff1744;
            text-align: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .vs-btn-loading {
            pointer-events: none;
            opacity: 0.8;
        }
        
        .vs-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: vs-spin 0.8s linear infinite;
            margin-left: 8px;
            vertical-align: middle;
        }
        
        @keyframes vs-spin {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
});
