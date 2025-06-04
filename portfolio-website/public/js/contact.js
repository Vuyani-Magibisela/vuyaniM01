document.addEventListener("DOMContentLoaded", () => {
    // Contact form elements
    const contactForm = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    
    // Character counter for message textarea
    if (messageTextarea && charCount) {
        // Update character count on page load
        updateCharacterCount();
        
        messageTextarea.addEventListener('input', updateCharacterCount);
        
        function updateCharacterCount() {
            const currentLength = messageTextarea.value.length;
            charCount.textContent = currentLength;
            
            // Change color if approaching limit
            if (currentLength > 900) {
                charCount.style.color = '#e74c3c';
            } else if (currentLength > 800) {
                charCount.style.color = '#f39c12';
            } else {
                charCount.style.color = '#888';
            }
        }
    }
    
    // Form submission handling
    if (contactForm) {
        contactForm.addEventListener('submit', handleFormSubmission);
        
        async function handleFormSubmission(e) {
            e.preventDefault();
            
            // Disable submit button and show loading state
            setLoadingState(true);
            
            // Get form data
            const formData = new FormData(contactForm);
            formData.append('ajax', '1'); // Flag for AJAX request
            
            try {
                const response = await fetch(contactForm.action, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccessMessage(result.message);
                    contactForm.reset();
                    updateCharacterCount(); // Reset character count
                } else {
                    showErrorMessage(result.errors);
                }
            } catch (error) {
                console.error('Form submission error:', error);
                showErrorMessage(['An error occurred while sending your message. Please try again.']);
            } finally {
                setLoadingState(false);
            }
        }
        
        function setLoadingState(loading) {
            submitBtn.disabled = loading;
            
            if (loading) {
                btnText.style.display = 'none';
                btnLoading.style.display = 'flex';
            } else {
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
            }
        }
        
        function showSuccessMessage(message) {
            // Remove any existing messages
            removeExistingMessages();
            
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `
                <div class="message-icon">✓</div>
                <div class="message-content">
                    <h3>Message Sent Successfully!</h3>
                    <p>${message}</p>
                </div>
            `;
            
            // Insert before the form container
            const formContainer = document.querySelector('.contact-form-container');
            formContainer.parentNode.insertBefore(successDiv, formContainer);
            
            // Scroll to message
            successDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Animate in
            if (typeof gsap !== 'undefined') {
                gsap.from(successDiv, {
                    duration: 0.5,
                    opacity: 0,
                    y: -20,
                    ease: "power2.out"
                });
            }
        }
        
        function showErrorMessage(errors) {
            // Remove any existing messages
            removeExistingMessages();
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-messages';
            
            const errorList = errors.map(error => `<li>${error}</li>`).join('');
            
            errorDiv.innerHTML = `
                <div class="error-icon">⚠</div>
                <div class="error-content">
                    <h4>Please correct the following errors:</h4>
                    <ul>${errorList}</ul>
                </div>
            `;
            
            // Insert before the form container
            const formContainer = document.querySelector('.contact-form-container');
            formContainer.parentNode.insertBefore(errorDiv, formContainer);
            
            // Scroll to message
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Animate in
            if (typeof gsap !== 'undefined') {
                gsap.from(errorDiv, {
                    duration: 0.5,
                    opacity: 0,
                    y: -20,
                    ease: "power2.out"
                });
            }
        }
        
        function removeExistingMessages() {
            const existingMessages = document.querySelectorAll('.success-message, .error-messages');
            existingMessages.forEach(msg => msg.remove());
        }
    }
    
    // Form validation enhancements
    const formInputs = document.querySelectorAll('.form-input, .form-textarea');
    
    formInputs.forEach(input => {
        // Real-time validation feedback
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearFieldError);
        
        function validateField() {
            const value = input.value.trim();
            let isValid = true;
            let errorMessage = '';
            
            // Remove existing error styling
            input.classList.remove('error');
            removeFieldError(input);
            
            // Validation logic
            switch (input.type) {
                case 'email':
                    if (value && !isValidEmail(value)) {
                        isValid = false;
                        errorMessage = 'Please enter a valid email address';
                    }
                    break;
                case 'text':
                    if (input.hasAttribute('required') && !value) {
                        isValid = false;
                        errorMessage = 'This field is required';
                    }
                    break;
                default:
                    if (input.hasAttribute('required') && !value) {
                        isValid = false;
                        errorMessage = 'This field is required';
                    }
                    break;
            }
            
            // Special validation for message length
            if (input.id === 'message' && value.length > 1000) {
                isValid = false;
                errorMessage = 'Message must be less than 1000 characters';
            }
            
            if (!isValid) {
                showFieldError(input, errorMessage);
            }
            
            return isValid;
        }
        
        function clearFieldError() {
            input.classList.remove('error');
            removeFieldError(input);
        }
        
        function showFieldError(field, message) {
            field.classList.add('error');
            
            // Create error element if it doesn't exist
            let errorEl = field.parentNode.querySelector('.field-error');
            if (!errorEl) {
                errorEl = document.createElement('div');
                errorEl.className = 'field-error';
                field.parentNode.appendChild(errorEl);
            }
            
            errorEl.textContent = message;
        }
        
        function removeFieldError(field) {
            const errorEl = field.parentNode.querySelector('.field-error');
            if (errorEl) {
                errorEl.remove();
            }
        }
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    });
    
    // Add error styling for form validation
    const style = document.createElement('style');
    style.textContent = `
        .form-input.error,
        .form-textarea.error {
            border-color: #e74c3c !important;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1) !important;
        }
        
        .field-error {
            color: #e74c3c;
            font-size: 0.8rem;
            margin-top: 5px;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
    
    // GSAP animations for page elements
    if (typeof gsap !== 'undefined') {
        // Animate contact header
        gsap.from(".contact-header", {
            duration: 1,
            opacity: 0,
            y: 30,
            ease: "power3.out"
        });
        
        // Animate contact content sections
        gsap.from(".contact-form-container", {
            duration: 0.8,
            opacity: 0,
            x: -30,
            ease: "power3.out",
            delay: 0.2
        });
        
        gsap.from(".info-card", {
            duration: 0.6,
            opacity: 0,
            y: 20,
            stagger: 0.1,
            ease: "power3.out",
            delay: 0.4
        });
        
        gsap.from(".social-contact-link", {
            duration: 0.6,
            opacity: 0,
            y: 20,
            stagger: 0.1,
            ease: "power3.out",
            delay: 0.6
        });
        
        // Add hover animations for info cards
        const infoCards = document.querySelectorAll('.info-card, .social-contact-link');
        infoCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                gsap.to(card.querySelector('.info-icon, .social-icon'), {
                    duration: 0.3,
                    scale: 1.1,
                    ease: "power2.out"
                });
            });
            
            card.addEventListener('mouseleave', () => {
                gsap.to(card.querySelector('.info-icon, .social-icon'), {
                    duration: 0.3,
                    scale: 1,
                    ease: "power2.out"
                });
            });
        });
    }
    
    // Auto-hide success/error messages after 5 seconds
    setTimeout(() => {
        const messages = document.querySelectorAll('.success-message, .error-messages');
        messages.forEach(msg => {
            if (typeof gsap !== 'undefined') {
                gsap.to(msg, {
                    duration: 0.5,
                    opacity: 0,
                    y: -20,
                    ease: "power2.in",
                    onComplete: () => msg.remove()
                });
            } else {
                msg.style.transition = 'opacity 0.5s ease-out';
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 500);
            }
        });
    }, 5000);
});