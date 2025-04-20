<?php require_once 'app/views/layouts/header.php'; ?>

<main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container hero-container">
                <div class="hero-image">
                    <img src="/api/placeholder/400/450" alt="Vuyani Magibisela portrait">
                </div>
                <div class="hero-content">
                    <h1>Empowering Technology & Creativity</h1>
                    <h2>Vuyani Magibisela – ICT Trainer, Web/App Developer, Maker and 3D Artist</h2>
                    <a href="#services" class="btn btn-primary">Learn More</a>
                </div>
            </div>
        </section>

        <!-- Service Highlights -->
        <section id="services" class="services">
            <div class="container">
                <h2 class="section-title">What I Do</h2>
                <div class="services-grid">
                    <div class="service-card">
                        <div class="service-icon">
                            <img src="/api/placeholder/100/100" alt="ICT Training Icon">
                        </div>
                        <h3>ICT Training</h3>
                        <p>Empowering youth with tech education and digital literacy skills</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">
                            <img src="/api/placeholder/100/100" alt="Web/App Development Icon">
                        </div>
                        <h3>Web/App Development</h3>
                        <p>Creating responsive, user-friendly digital experiences</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">
                            <img src="/api/placeholder/100/100" alt="3D Design Icon">
                        </div>
                        <h3>3D Design</h3>
                        <p>Bringing ideas to life with creative 3D modeling and visualization</p>
                    </div>
                    <div class="service-card">
                        <div class="service-icon">
                            <img src="/api/placeholder/100/100" alt="Maker Projects Icon">
                        </div>
                        <h3>Maker Projects</h3>
                        <p>Innovative hardware and robotics solutions</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Me Section -->
        <section class="about">
            <div class="container">
                <h2 class="section-title">About Me</h2>
                <div class="about-content">
                    <p>I'm passionate about technology and design, with a mission to help others discover the power of digital innovation. With experience in various tech disciplines, I blend creativity with technical expertise to deliver solutions that empower and inspire. My background in ICT training has given me insights into how technology can transform lives and communities.</p>
                    <p>Whether I'm developing web applications, creating 3D designs, or building maker projects, my goal is to create meaningful technological experiences that solve real problems.</p>
                </div>
            </div>
        </section>

        <!-- Connect with Me Section -->
        <section class="connect">
            <div class="container">
                <h2 class="section-title">Connect with me</h2>
                <p class="connect-subtitle">Let's collaborate or start a conversation about your next project!</p>
                <div class="connect-grid">
                    <a href="#" class="connect-card whatsapp">
                        <i class="fab fa-whatsapp"></i>
                        <h3>Let's chat</h3>
                    </a>
                    <a href="#" class="connect-card tiktok">
                        <i class="fab fa-tiktok"></i>
                        <h3>Catch latest posts</h3>
                    </a>
                    <a href="#" class="connect-card instagram">
                        <i class="fab fa-instagram"></i>
                        <h3>Visual portfolio</h3>
                    </a>
                    <a href="#" class="connect-card email">
                        <i class="far fa-envelope"></i>
                        <h3>Direct contact</h3>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?php require_once 'app/views/layouts/footer.php'; ?>