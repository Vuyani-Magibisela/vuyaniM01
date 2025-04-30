<?php require_once '../app/views/partials/header.php'; ?>
<div class="container">
    <section class="projects-section">
        <h1>My Projects</h1>
        <p class="projects-intro">Explore my work across various disciplines, from web development to 3D design and maker projects.</p>
        
        <!-- Project Filter System -->
        <div class="project-filters">
            <button class="filter-btn active" data-filter="all">All Projects</button>
            <button class="filter-btn" data-filter="web-dev">Web Dev</button>
            <button class="filter-btn" data-filter="app-dev">App Dev</button>
            <button class="filter-btn" data-filter="game-dev">Game Dev</button>
            <button class="filter-btn" data-filter="digital-design">Digital Design</button>
            <button class="filter-btn" data-filter="maker">Maker</button>
        </div>
        
        <!-- Projects Grid -->
        <div class="projects-grid">
            <!-- Web Development Projects -->
            <div class="project-card" data-category="web-dev">
                <div class="project-image">
                    <img src="<?php echo $baseUrl; ?>/images/projects/project1.jpg" alt="Portfolio Website" onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg02.jpeg'; this.style.width='100%'; this.style.height='100%'; this.style.objectFit='cover';">
                </div>
                <div class="project-content">
                    <h3>Portfolio Website</h3>
                    <p>A responsive portfolio website built with PHP, CSS, and JavaScript.</p>
                    <div class="project-tags">
                        <span class="project-tag">PHP</span>
                        <span class="project-tag">CSS</span>
                        <span class="project-tag">JavaScript</span>
                    </div>
                    <a href="<?php echo $baseUrl; ?>/projects/web-dev/1" class="project-link">View Project</a>
                </div>
            </div>
            
            <div class="project-card" data-category="web-dev">
                <div class="project-image">
                    <img src="<?php echo $baseUrl; ?>/images/projects/project2.jpg" alt="E-commerce Platform" onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg02.jpeg'; this.style.width='100%'; this.style.height='100%'; this.style.objectFit='cover';">
                </div>
                <div class="project-content">
                    <h3>E-commerce Platform</h3>
                    <p>A full-featured online store with product management and secure checkout.</p>
                    <div class="project-tags">
                        <span class="project-tag">PHP</span>
                        <span class="project-tag">MySQL</span>
                        <span class="project-tag">JavaScript</span>
                    </div>
                    <a href="<?php echo $baseUrl; ?>/projects/web-dev/2" class="project-link">View Project</a>
                </div>
            </div>
            
            <!-- App Development Projects -->
            <div class="project-card" data-category="app-dev">
                <div class="project-image">
                    <img src="<?php echo $baseUrl; ?>/images/projects/project3.jpg" alt="Health Tracker App" onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg01.png'; this.style.width='100%'; this.style.height='100%'; this.style.objectFit='cover';">
                </div>
                <div class="project-content">
                    <h3>Health Tracker App</h3>
                    <p>A mobile application for tracking fitness goals and nutrition intake.</p>
                    <div class="project-tags">
                        <span class="project-tag">React Native</span>
                        <span class="project-tag">Firebase</span>
                        <span class="project-tag">Redux</span>
                    </div>
                    <a href="<?php echo $baseUrl; ?>/projects/app-dev/1" class="project-link">View Project</a>
                </div>
            </div>
            
            <!-- Game Development Projects -->
            <div class="project-card" data-category="game-dev">
                <div class="project-image">
                    <img src="<?php echo $baseUrl; ?>/images/projects/project4.jpg" alt="Educational Puzzle Game" onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg01.png'; this.style.width='100%'; this.style.height='100%'; this.style.objectFit='cover';">
                </div>
                <div class="project-content">
                    <h3>Educational Puzzle Game</h3>
                    <p>A game that teaches coding concepts through interactive puzzles.</p>
                    <div class="project-tags">
                        <span class="project-tag">Unity</span>
                        <span class="project-tag">C#</span>
                        <span class="project-tag">Game Design</span>
                    </div>
                    <a href="<?php echo $baseUrl; ?>/projects/game-dev/1" class="project-link">View Project</a>
                </div>
            </div>
            
            <!-- Digital Design Projects -->
            <div class="project-card" data-category="digital-design">
                <div class="project-image">
                    <img src="<?php echo $baseUrl; ?>/images/projects/project5.jpg" alt="3D Character Design" onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg03.jpeg'; this.style.width='100%'; this.style.height='100%'; this.style.objectFit='cover';">
                </div>
                <div class="project-content">
                    <h3>3D Character Design</h3>
                    <p>Character modeling and animation for an animated short film.</p>
                    <div class="project-tags">
                        <span class="project-tag">Blender</span>
                        <span class="project-tag">Animation</span>
                        <span class="project-tag">Texturing</span>
                    </div>
                    <a href="<?php echo $baseUrl; ?>/projects/digital-design/1" class="project-link">View Project</a>
                </div>
            </div>
            
            <div class="project-card" data-category="digital-design">
                <div class="project-image">
                    <img src="<?php echo $baseUrl; ?>/images/projects/project6.jpg" alt="Brand Identity Design" onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg03.jpeg'; this.style.width='100%'; this.style.height='100%'; this.style.objectFit='cover';">
                </div>
                <div class="project-content">
                    <h3>Brand Identity Design</h3>
                    <p>Complete visual identity system for a tech startup including logo, colors, and marketing materials.</p>
                    <div class="project-tags">
                        <span class="project-tag">Illustrator</span>
                        <span class="project-tag">Photoshop</span>
                        <span class="project-tag">Branding</span>
                    </div>
                    <a href="<?php echo $baseUrl; ?>/projects/digital-design/2" class="project-link">View Project</a>
                </div>
            </div>
            
            <!-- Maker Projects -->
            <div class="project-card" data-category="maker">
                <div class="project-image">
                    <img src="<?php echo $baseUrl; ?>/images/projects/project7.jpg" alt="Smart Home System" onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg04.jpeg'; this.style.width='100%'; this.style.height='100%'; this.style.objectFit='cover';">
                </div>
                <div class="project-content">
                    <h3>Smart Home System</h3>
                    <p>DIY home automation system using Arduino and Raspberry Pi to control lighting and temperature.</p>
                    <div class="project-tags">
                        <span class="project-tag">Arduino</span>
                        <span class="project-tag">Raspberry Pi</span>
                        <span class="project-tag">IoT</span>
                    </div>
                    <a href="<?php echo $baseUrl; ?>/projects/maker/1" class="project-link">View Project</a>
                </div>
            </div>
            
            <div class="project-card" data-category="maker">
                <div class="project-image">
                    <img src="<?php echo $baseUrl; ?>/images/projects/project8.jpg" alt="3D Printed Drone" onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg04.jpeg'; this.style.width='100%'; this.style.height='100%'; this.style.objectFit='cover';">
                </div>
                <div class="project-content">
                    <h3>3D Printed Drone</h3>
                    <p>Custom-designed and 3D printed quadcopter drone with camera mount.</p>
                    <div class="project-tags">
                        <span class="project-tag">3D Printing</span>
                        <span class="project-tag">Electronics</span>
                        <span class="project-tag">Design</span>
                    </div>
                    <a href="<?php echo $baseUrl; ?>/projects/maker/2" class="project-link">View Project</a>
                </div>
            </div>
        </div>
        
        <!-- No Results Message -->
        <div class="no-results" style="display: none;">
            <p>No projects found matching your filter criteria.</p>
            <button class="reset-filter-btn">Show All Projects</button>
        </div>
    </section>
</div>
<?php require_once '../app/views/partials/footer.php'; ?>