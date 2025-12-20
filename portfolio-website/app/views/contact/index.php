<?php require_once '../app/views/partials/header.php'; ?>
<div class="container">
    <section class="contact-section">
        <div class="contact-header">
            <h1 class="page-title">Get In Touch</h1>
            <p class="page-subtitle">Have a project in mind or just want to say hello? I'd love to hear from you. Let's start a conversation and explore how we can work together.</p>
        </div>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            <div class="success-message">
                <div class="message-icon">✓</div>
                <div class="message-content">
                    <h3>Message Sent Successfully!</h3>
                    <p>Thank you for reaching out. I'll get back to you as soon as possible.</p>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="contact-content">
            <!-- Contact Form -->
            <div class="contact-form-container">
                <div class="form-header">
                    <h2>Send Me a Message</h2>
                    <p>Fill out the form below and I'll respond within 24 hours.</p>
                </div>
                
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="error-messages">
                        <div class="error-icon">⚠</div>
                        <div class="error-content">
                            <h4>Please correct the following errors:</h4>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form id="contactForm" class="contact-form" action="<?php echo $baseUrl; ?>/contact/submit" method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label">Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-input" 
                            value="<?php echo isset($formData['name']) ? htmlspecialchars($formData['name']) : ''; ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            value="<?php echo isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="form-label">Subject</label>
                        <input 
                            type="text" 
                            id="subject" 
                            name="subject" 
                            class="form-input" 
                            value="<?php echo isset($formData['subject']) ? htmlspecialchars($formData['subject']) : ''; ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label">Message *</label>
                        <textarea 
                            id="message" 
                            name="message" 
                            class="form-textarea" 
                            rows="6" 
                            maxlength="1000"
                            required
                        ><?php echo isset($formData['message']) ? htmlspecialchars($formData['message']) : ''; ?></textarea>
                        <div class="character-count">
                            <span id="charCount">0</span>/1000 characters
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="btn-text">Send Message</span>
                        <span class="btn-loading" style="display: none;">
                            <div class="loading-spinner"></div>
                            Sending...
                        </span>
                    </button>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div class="contact-info">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h3>Email Me</h3>
                        <p>For general inquiries and project discussions</p>
                        <a href="mailto:admin@vuyanimagibisela.co.za" class="info-link">admin@vuyanimagibisela.co.za</a>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <h3>Call Me</h3>
                        <p>Available for urgent matters and consultations</p>
                        <a href="tel:+27123456789" class="info-link">+27 63 839 3157</a>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Location</h3>
                        <p>Based in Soweto Protea Glen, Gauteng</p>
                        <span class="info-text">South Africa</span>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h3>Response Time</h3>
                        <p>Typically respond within</p>
                        <span class="info-text">24 hours</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Contact Methods -->
        <div class="contact-alternatives">
            <h2 class="alternatives-title">Other Ways to Connect</h2>
            <div class="social-contact-links">
                <a href="#" class="social-contact-link whatsapp">
                    <div class="social-icon">
                        <img src="<?php echo $baseUrl; ?>/images/icons8-whatsapp-50.svg" alt="WhatsApp">
                    </div>
                    <div class="social-info">
                        <h4>WhatsApp</h4>
                        <p>Quick chat and media sharing</p>
                    </div>
                </a>
                
                <a href="#" class="social-contact-link linkedin">
                    <div class="social-icon">
                        <i class="fab fa-linkedin"></i>
                    </div>
                    <div class="social-info">
                        <h4>LinkedIn</h4>
                        <p>Professional networking</p>
                    </div>
                </a>
                
                <a href="#" class="social-contact-link instagram">
                    <div class="social-icon">
                        <img src="<?php echo $baseUrl; ?>/images/icons8-instagram-50.svg" alt="Instagram">
                    </div>
                    <div class="social-info">
                        <h4>Instagram</h4>
                        <p>Visual portfolio and updates</p>
                    </div>
                </a>
            </div>
        </div>
    </section>
</div>
<?php require_once '../app/views/partials/footer.php'; ?>