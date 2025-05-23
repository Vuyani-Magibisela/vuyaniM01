<?php require_once '../app/views/partials/header.php'; ?>

<div class="container">
    <section class="resources-header">
        <h1 class="page-title">Resources & Downloads</h1>
        <p class="page-subtitle">Free tools, templates, and guides to help you in your creative journey</p>
    </section>
    
    <section class="resources-content">
        <?php if(empty($resources)): ?>
            <div class="no-resources-message">
                <p>No resources available yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="resources-filter">
                <h3 class="filter-title">Filter by:</h3>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="template">Templates</button>
                    <button class="filter-btn" data-filter="tool">Tools</button>
                    <button class="filter-btn" data-filter="guide">Guides</button>
                </div>
            </div>
            
            <div class="resources-grid">
                <?php foreach($resources as $resource): ?>
                    <?php 
                        // Extract file type from the file_type field or guess from the path
                        $fileType = !empty($resource['file_type']) ? strtolower($resource['file_type']) : '';
                        $resourceClass = '';
                        $resourceIcon = '📄';
                        
                        if (strpos($fileType, 'pdf') !== false) {
                            $resourceClass = 'pdf-resource';
                            $resourceIcon = '📕';
                        } elseif (strpos($fileType, 'zip') !== false) {
                            $resourceClass = 'zip-resource';
                            $resourceIcon = '📦';
                        } elseif (strpos($fileType, 'image') !== false) {
                            $resourceClass = 'image-resource';
                            $resourceIcon = '🖼️';
                        } elseif (strpos($fileType, 'video') !== false) {
                            $resourceClass = 'video-resource';
                            $resourceIcon = '🎬';
                        } elseif (strpos($fileType, 'audio') !== false) {
                            $resourceClass = 'audio-resource';
                            $resourceIcon = '🎵';
                        }
                    ?>
                    <div class="resource-card <?php echo $resourceClass; ?>" data-category="template">
                        <div class="resource-icon">
                            <span><?php echo $resourceIcon; ?></span>
                        </div>
                        <div class="resource-content">
                            <h3 class="resource-title"><?php echo $resource['title']; ?></h3>
                            <p class="resource-description"><?php echo $resource['description']; ?></p>
                            <div class="resource-meta">
                                <?php if(!empty($resource['file_size'])): ?>
                                    <span class="resource-size"><?php echo formatFileSize($resource['file_size']); ?></span>
                                <?php endif; ?>
                                <span class="resource-downloads"><?php echo $resource['download_count']; ?> downloads</span>
                            </div>
                        </div>
                        <div class="resource-action">
                            <?php if($resource['requires_login'] && !isset($_SESSION['user_id'])): ?>
                                <a href="<?php echo $baseUrl; ?>/auth/login?redirect=blog/resources" class="resource-btn login-required">
                                    <i class="fas fa-lock"></i> Login to Download
                                </a>
                            <?php else: ?>
                                <a href="<?php echo $baseUrl; ?>/blog/download-resource/<?php echo $resource['id']; ?>" class="resource-btn">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    
    <section class="resources-cta">
        <div class="cta-content">
            <h2>Looking for something specific?</h2>
            <p>Can't find what you're looking for? Let me know what resources would help you.</p>
            <a href="<?php echo $baseUrl; ?>/contact" class="cta-button">Request a Resource</a>
        </div>
    </section>
</div>

<?php 
// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>

<?php require_once '../app/views/partials/footer.php'; ?>