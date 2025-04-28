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
        
        <div class="freelance-clients">
            <div class="clients-grid">
                <div class="client-card">
                    <h3>Design</h3>
                    <div class="client-image">
                        <img src="<?php echo $baseUrl; ?>/images/clients/design.jpg" alt="Design services" onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg03.jpeg'">
                    </div>
                </div>
                
                <div class="client-card">
                    <h3>Web Dev</h3>
                    <div class="client-image">
                        <img src="<?php echo $baseUrl; ?>/images/clients/webdev.jpg" alt="Web Development services" onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg02.jpeg'">
                    </div>
                </div>
                
                <div class="client-card">
                    <h3>Game Dev</h3>
                    <div class="client-image">
                        <img src="<?php echo $baseUrl; ?>/images/clients/gamedev.jpg" alt="Game Development services" onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg01.png'">
                    </div>
                </div>
                
                <div class="client-card">
                    <h3>Maker</h3>
                    <div class="client-image">
                        <img src="<?php echo $baseUrl; ?>/images/clients/maker.jpg" alt="Maker services" onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg04.jpeg'">
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php require_once '../app/views/partials/footer.php'; ?>