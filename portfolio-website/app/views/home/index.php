<?php require_once '../app/views/partials/header.php'; ?>
<div class="container">

    <section class="hero">
      <div class="hero-image">
        <img src="<?php echo $baseUrl; ?>/images/headerImg.jpg" alt="Vuyani Magibisela at a science event wearing a white hat and shirt with a camera">
      </div>
      <div class="hero-content">
        <h1 class="hero-title">Empowering Technology & Creativity</h1>
        <p class="hero-subtitle">Vuyani Magibisela - ICT Trainer, Web/App Developer, Maker and 3D Artist</p>
        <?php if (!empty($latestPosts) && !empty($latestPosts[0])): ?>
          <a href="<?php echo $baseUrl; ?>/blog/article/<?php echo $latestPosts[0]['slug']; ?>" class="hero-latest-badge">
            <i class="fas fa-newspaper"></i>
            Latest: <?php echo htmlspecialchars(mb_strimwidth($latestPosts[0]['title'], 0, 50, '...')); ?>
          </a>
        <?php endif; ?>
      </div>
    </section>

    <section class="services">
      <div class="service-card">
        <div class="service-image">
          <img src="<?php echo $baseUrl; ?>/images/skillsImg01.png" alt="ICT Training">
        </div>
        <div class="service-content">
          <h3 class="service-title">ICT Training</h3>
          <p class="service-description">Empowering youth with tech education.</p>
        </div>
      </div>

      <div class="service-card">
        <div class="service-image">
          <img src="<?php echo $baseUrl; ?>/images/skillsImg02.jpeg" alt="Web/App Development">
        </div>
        <div class="service-content">
          <h3 class="service-title">Web/App Development</h3>
          <p class="service-description">Innovative solutions online</p>
        </div>
      </div>

      <div class="service-card">
        <div class="service-image">
          <img src="<?php echo $baseUrl; ?>/images/skillsImg03.jpeg" alt="3D Artistry">
        </div>
        <div class="service-content">
          <h3 class="service-title">3D Artistry</h3>
          <p class="service-description">Bringing ideas to life</p>
        </div>
      </div>

      <div class="service-card">
        <div class="service-image">
          <img src="<?php echo $baseUrl; ?>/images/skillsImg04.jpeg" alt="Maker">
        </div>
        <div class="service-content">
          <h3 class="service-title">Maker</h3>
          <p class="service-description">Product creation, experiments, explorations and repairs.</p>
        </div>
      </div>
    </section>

    <section class="about">
      <h2 class="section-title">About Me</h2>
      <p class="about-text">With a passion for technology and an eye for design, I've dedicated my career to helping others discover the power of digital innovation. Let's explore the possibilities together.</p>
    </section>

    <?php if (!empty($latestPosts)): ?>
    <section class="latest-posts-home">
      <h2 class="section-title">Latest from the Blog</h2>
      <div class="home-posts-grid">
        <?php foreach ($latestPosts as $post): ?>
          <div class="post-card">
            <div class="post-image">
              <?php if (!empty($post['featured_image'])): ?>
                <img src="<?php echo $post['featured_image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
              <?php else: ?>
                <img src="<?php echo $baseUrl; ?>/images/blog/default-post.jpg" alt="Blog post">
              <?php endif; ?>
              <?php if (!empty($post['category_name'])): ?>
                <span class="post-category"><?php echo htmlspecialchars($post['category_name']); ?></span>
              <?php endif; ?>
            </div>
            <div class="post-content">
              <h3 class="post-title">
                <a href="<?php echo $baseUrl; ?>/blog/article/<?php echo $post['slug']; ?>">
                  <?php echo htmlspecialchars($post['title']); ?>
                </a>
              </h3>
              <p class="post-excerpt"><?php echo htmlspecialchars(mb_strimwidth($post['excerpt'] ?? '', 0, 120, '...')); ?></p>
              <div class="post-meta">
                <span><?php echo date('M d, Y', strtotime($post['published_at'])); ?></span>
                <a href="<?php echo $baseUrl; ?>/blog/article/<?php echo $post['slug']; ?>" class="read-more">Read More &rarr;</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div style="text-align: center; margin-top: 25px;">
        <a href="<?php echo $baseUrl; ?>/blog" class="cta-button">View All Posts</a>
      </div>
    </section>
    <?php endif; ?>

</div>
<?php require_once '../app/views/partials/footer.php'; ?>
