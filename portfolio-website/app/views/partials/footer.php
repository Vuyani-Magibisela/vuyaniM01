<?php 
// Make sure $baseUrl is available
if (!isset($baseUrl)) {
    require_once dirname(__DIR__, 2) . '/config/config.php';
}
?>
<div class="container">
    <section class="connect">
      <h2 class="section-title">Connect with me</h2>
      <p class="connect-intro">Interested in collaborating or learning from me? Reach out and let's start a conversation</p>
      
      <div class="social-links">
        <a href="#" class="social-link">
          <div class="social-icon"><img src="<?php echo $baseUrl; ?>/images/icons8-whatsapp-50.svg" alt="WhatsApp logo "></div>
          <div class="social-name">WhatsApp</div>
          <div class="social-description">Lets chat</div>
        </a>
        
        <a href="#" class="social-link">
          <div class="social-icon"><img src="<?php echo $baseUrl; ?>/images/icons8-tiktok-50.svg" alt="TikTok logo"></div>
          <div class="social-name">TikTok</div>
          <div class="social-description">Catch latest posts</div>
        </a>
        
        <a href="#" class="social-link">
          <div class="social-icon"><img src="<?php echo $baseUrl; ?>/images/icons8-instagram-50.svg" alt="Instagram Logo"></div>
          <div class="social-name">Instagram</div>
          <div class="social-description">Visual portfolio</div>
        </a>
        
        <a href="#" class="social-link">
          <div class="social-icon"><img src="<?php echo $baseUrl; ?>/images/icons8-email-50.png" alt="Email Logo"></div>
          <div class="social-name">Email</div>
          <div class="social-description">Direct contact</div>
        </a>
      </div>
    </section>
    
    <footer class="main-footer">
      <p class="copyright">© <?php echo date('Y'); ?> Vuyani Magibisela</p>
    </footer>
</div>
  <script src="<?php echo $baseUrl; ?>/js/theme.js"></script>
  <script src="<?php echo $baseUrl; ?>/js/mobile-nav.js"></script>

  <?php 
  // Load page-specific JavaScript files
  $url = $_GET['url'] ?? 'home/index';
  $urlParts = explode('/', $url);
  $currentPage = $urlParts[0];

  // Load blog.js script only if we're on a blog page
  if ($currentPage === 'blog') {
      echo '<script src="' . $baseUrl . '/js/blog.js"></script>';
  }
  
  // Load contact.js script only if we're on a contact page
  if ($currentPage === 'contact') {
      echo '<script src="' . $baseUrl . '/js/contact.js"></script>';
  }
  ?>

</body>
</html>