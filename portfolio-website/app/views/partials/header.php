<?php 
require_once dirname(__DIR__, 2) . '/config/config.php'; 

// Function to check if current page matches the given path
function isActivePage($pageName) {
    // Get the current URL path
    $url = $_GET['url'] ?? 'home/index';
    $urlParts = explode('/', $url);
    $currentPage = $urlParts[0];
    
    // Return true if current page matches the given page name
    return ($currentPage === $pageName) ? true : false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst(isset($_GET['url']) ? explode('/', $_GET['url'])[0] : 'Home'); ?> | Vuyani Magibisela</title>
    
    <!-- Favicon and App Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $baseUrl; ?>/images/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $baseUrl; ?>/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $baseUrl; ?>/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    
    <!-- External Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Stylesheets -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/responsive.css">
    
    <!-- Analytics and Tracking Scripts -->
    <!-- Consent Manager for GDPR Compliance -->
    <script type="text/javascript" data-cmp-ab="1" src="https://cdn.consentmanager.net/delivery/autoblocking/6e62a22472408.js" data-cmp-host="b.delivery.consentmanager.net" data-cmp-cdn="cdn.consentmanager.net" data-cmp-codesrc="16"></script>
    
    <!-- Google Analytics (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VNN90D4GDE"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-VNN90D4GDE');
    </script>
    
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6423925713865339" crossorigin="anonymous"></script>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-mode' : ''; ?>">
<div class="container">
    <header>
        <div class="logo">Vuyani Magibisela</div>
        
        <!-- Mobile Burger Menu -->
        <div class="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        
        <!-- Desktop Navigation -->
        <nav class="desktop-navigation">
            <a href="<?php echo $baseUrl; ?>/home" class="<?php echo isActivePage('home') ? 'active' : ''; ?>">Home</a>
            <a href="<?php echo $baseUrl; ?>/clients" class="<?php echo isActivePage('clients') ? 'active' : ''; ?>">Clients</a>
            <a href="<?php echo $baseUrl; ?>/projects" class="<?php echo isActivePage('projects') ? 'active' : ''; ?>">Projects</a>
            <a href="<?php echo $baseUrl; ?>/blog" class="<?php echo isActivePage('blog') ? 'active' : ''; ?>">Blog</a>
            <a href="<?php echo $baseUrl; ?>/contact" class="<?php echo isActivePage('contact') ? 'active' : ''; ?>">Contact</a>
            <a href="#" class="light-mode" id="theme-toggle"><?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? '🌙' : '🌞'; ?></a>
        </nav>
    </header>
</div>

<!-- Mobile Navigation Menu Overlay -->
<div class="mobile-nav-overlay">
    <div class="mobile-nav-container">
        <div class="mobile-nav-header">
            <span>Vuyani Magibisela</span>
            <div class="close-mobile-nav">
                <i class="fas fa-times"></i>
            </div>
        </div>
        
        <div class="theme-toggles">
            <a href="#" class="theme-toggle-btn dark-mode-btn">
                <i class="fas fa-moon"></i>
            </a>
            <a href="#" class="theme-toggle-btn light-mode-btn">
                <i class="fas fa-sun"></i>
            </a>
        </div>
        
        <nav class="mobile-navigation">
            <a href="<?php echo $baseUrl; ?>/home" class="<?php echo isActivePage('home') ? 'active' : ''; ?>">Home</a>
            <a href="<?php echo $baseUrl; ?>/clients" class="<?php echo isActivePage('clients') ? 'active' : ''; ?>">Clients</a>
            <a href="<?php echo $baseUrl; ?>/projects" class="<?php echo isActivePage('projects') ? 'active' : ''; ?>">Projects</a>
            <a href="<?php echo $baseUrl; ?>/blog" class="<?php echo isActivePage('blog') ? 'active' : ''; ?>">Blogs</a>
            <a href="<?php echo $baseUrl; ?>/contact" class="<?php echo isActivePage('contact') ? 'active' : ''; ?>">Contacts</a>
        </nav>
    </div>
</div>