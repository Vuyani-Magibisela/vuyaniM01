<?php require_once dirname(__DIR__, 2) . '/config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(ucfirst(isset($_GET['url']) ? explode('/', $_GET['url'])[0] : 'Home'), ENT_QUOTES, 'UTF-8'); ?> | Vuyani Magibisela</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">
    <!-- Add GSAP for animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/ScrollTrigger.min.js"></script>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-mode' : ''; ?>">
    <?php require_once dirname(__DIR__) . '/partials/header.php'; ?>
    
    <?php 
    // Get the current URL path
    $url = $_GET['url'] ?? 'home/index';
    $urlParts = explode('/', $url);
    $currentPage = $urlParts[0];
    
    // Include the appropriate view based on the current page
    if ($currentPage === 'home' || $currentPage === '') {
        require_once dirname(__DIR__) . '/home/index.php';
    } else if ($currentPage === 'clients') {
        require_once dirname(__DIR__) . '/clients/index.php';
    } else if (file_exists(dirname(__DIR__) . "/{$currentPage}/index.php")) {
        require_once dirname(__DIR__) . "/{$currentPage}/index.php";
    } else {
        // Default to home if page doesn't exist
        require_once dirname(__DIR__) . '/home/index.php';
    }
    ?>
    
    <?php require_once dirname(__DIR__) . '/partials/footer.php'; ?>
    
    <!-- First load theme.js to set up theme functionality -->
    <script src="<?php echo $baseUrl; ?>/js/theme.js"></script>
    <!-- Then load app.js for page-specific animations -->
    <script src="<?php echo $baseUrl; ?>/js/app.js"></script>
</body>
</html>