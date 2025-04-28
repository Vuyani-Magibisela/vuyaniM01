<?php require_once '../app/views/partials/header.php'; ?>
<div class="container">
    <section class="clients-section">
        <h1>Trusted by Diverse Organizations</h1>
        
        <div class="current-role">
            <span class="role-tag">Current Role</span>
        </div>
        
        <div class="main-employment">
            <h2>ICT Trainer | Clubhouse Coordinator</h2>
            <p class="role-description">Designing curriculum frameworks, creating engaging content, and teaching cutting-edge technology skills.</p>
            <ul class="role-achievements">
                <li>Developed comprehensive training programs</li>
                <li>Mentored 100+ students in technology skills</li>
                <li>Implemented modern teaching methodologies</li>
            </ul>
        </div>
        
        <!-- Mobile-Friendly Expertise Section -->
        <div class="freelance-expertise">
            <h2 class="expertise-heading">Areas of Expertise</h2>
            
            <div class="expertise-container">
                <!-- First Row -->
                <div class="expertise-card mobile-friendly">
                    <div class="expertise-icon">
                        <div class="icon-circle">
                            <img src="<?php echo $baseUrl; ?>/images/clients/design-icon.svg" alt="Design Icon" onerror="this.src='<?php echo $baseUrl; ?>/images/digitaldesignIcon.jpeg'; this.style.width='100%'; this.style.height='100%'; this.style.borderRadius='50%';">
                        </div>
                    </div>
                    <div class="expertise-content">
                        <h3>Design</h3>
                        <p>Creating visually stunning experiences that communicate effectively and inspire action.</p>
                        <div class="expertise-details">
                            <div class="expertise-tag">UI/UX</div>
                            <div class="expertise-tag">Graphic Design</div>
                            <div class="expertise-tag">3D Design</div>
                        </div>
                    </div>
                </div>
                
                <!-- Second Row -->
                <div class="expertise-card mobile-friendly">
                    <div class="expertise-icon">
                        <div class="icon-circle">
                            <img src="<?php echo $baseUrl; ?>/images/clients/webdev-icon.svg" alt="Web Dev Icon" onerror="this.src='<?php echo $baseUrl; ?>/images/webDev.jpeg'; this.style.width='100%'; this.style.height='100%'; this.style.borderRadius='50%';">
                        </div>
                    </div>
                    <div class="expertise-content">
                        <h3>Web Development</h3>
                        <p>Building responsive, accessible, and performant websites that deliver exceptional user experiences.</p>
                        <div class="expertise-details">
                            <div class="expertise-tag">PHP</div>
                            <div class="expertise-tag">JavaScript</div>
                            <div class="expertise-tag">Responsive</div>
                        </div>
                    </div>
                </div>
                
                <!-- Third Row -->
                <div class="expertise-card mobile-friendly">
                    <div class="expertise-icon">
                        <div class="icon-circle">
                            <img src="<?php echo $baseUrl; ?>/images/clients/gamedev-icon.svg" alt="Game Dev Icon" onerror="this.src='<?php echo $baseUrl; ?>/images/gameDev.jpeg'; this.style.width='100%'; this.style.height='100%'; this.style.borderRadius='50%';">
                        </div>
                    </div>
                    <div class="expertise-content">
                        <h3>Game Development</h3>
                        <p>Crafting engaging gaming experiences that entertain, educate, and inspire players of all ages.</p>
                        <div class="expertise-details">
                            <div class="expertise-tag">Unity</div>
                            <div class="expertise-tag">C#</div>
                            <div class="expertise-tag">Game Design</div>
                        </div>
                    </div>
                </div>
                
                <!-- Fourth Row -->
                <div class="expertise-card mobile-friendly">
                    <div class="expertise-icon">
                        <div class="icon-circle">
                            <img src="<?php echo $baseUrl; ?>/images/clients/maker-icon.svg" alt="Maker Icon" onerror="this.src='<?php echo $baseUrl; ?>/images/maker.jpeg'; this.style.width='100%'; this.style.height='100%'; this.style.borderRadius='50%';">
                        </div>
                    </div>
                    <div class="expertise-content">
                        <h3>Maker</h3>
                        <p>Turning creative ideas into physical reality through hands-on fabrication, electronics, and innovation.</p>
                        <div class="expertise-details">
                            <div class="expertise-tag">Electronics</div>
                            <div class="expertise-tag">3D Printing</div>
                            <div class="expertise-tag">Prototyping</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="expertise-cta">
                <a href="<?php echo $baseUrl; ?>/contact" class="cta-button">Work With Me</a>
            </div>
        </div>
    </section>
</div>
<?php require_once '../app/views/partials/footer.php'; ?>