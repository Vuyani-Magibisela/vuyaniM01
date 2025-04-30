<?php require_once '../app/views/partials/header.php'; ?>
<div class="container">
    <section class="project-detail">
        <!-- Project Header -->
        <div class="project-header">
            <div class="project-breadcrumb">
                <a href="<?php echo $baseUrl; ?>/projects">Projects</a> / 
                <a href="<?php echo $baseUrl; ?>/projects/<?php echo $project['category']; ?>"><?php echo ucfirst($project['category']); ?></a> / 
                <span><?php echo $project['title']; ?></span>
            </div>
            <h1 class="project-title"><?php echo $project['title']; ?></h1>
            <div class="project-metadata">
                <div class="project-date">Completed: <?php echo $project['date']; ?></div>
                <div class="project-tags">
                    <?php foreach($project['tags'] as $tag): ?>
                        <span class="project-tag"><?php echo $tag; ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Project Gallery -->
        <div class="project-gallery">
            <div class="project-main-image">
                <img src="<?php echo $baseUrl; ?>/images/projects/<?php echo $project['main_image']; ?>" alt="<?php echo $project['title']; ?>">
            </div>
            
            <?php if (!empty($project['gallery'])): ?>
            <div class="project-thumbnails">
                <?php foreach($project['gallery'] as $image): ?>
                <div class="thumbnail" data-image="<?php echo $image; ?>">
                    <img src="<?php echo $baseUrl; ?>/images/projects/thumbnails/<?php echo $image; ?>" alt="Project image thumbnail">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Project Description -->
        <div class="project-description">
            <h2>Project Overview</h2>
            <div class="description-content">
                <?php echo $project['description']; ?>
            </div>
            
            <?php if (!empty($project['challenges'])): ?>
            <h2>Challenges & Solutions</h2>
            <div class="challenges-content">
                <?php echo $project['challenges']; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($project['technologies'])): ?>
            <h2>Technologies Used</h2>
            <div class="tech-list">
                <ul>
                    <?php foreach($project['technologies'] as $tech): ?>
                    <li><?php echo $tech; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($project['link'])): ?>
            <div class="project-links">
                <a href="<?php echo $project['link']; ?>" class="cta-button" target="_blank">View Live Project</a>
                
                <?php if (!empty($project['github'])): ?>
                <a href="<?php echo $project['github']; ?>" class="github-link" target="_blank">
                    <i class="fab fa-github"></i> View on GitHub
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Next/Previous Projects -->
        <div class="project-navigation">
            <?php if (!empty($prevProject)): ?>
            <a href="<?php echo $baseUrl; ?>/projects/<?php echo $prevProject['category']; ?>/<?php echo $prevProject['id']; ?>" class="prev-project">
                <span class="nav-label">Previous Project</span>
                <span class="nav-title"><?php echo $prevProject['title']; ?></span>
            </a>
            <?php else: ?>
            <div class="prev-project empty"></div>
            <?php endif; ?>
            
            <?php if (!empty($nextProject)): ?>
            <a href="<?php echo $baseUrl; ?>/projects/<?php echo $nextProject['category']; ?>/<?php echo $nextProject['id']; ?>" class="next-project">
                <span class="nav-label">Next Project</span>
                <span class="nav-title"><?php echo $nextProject['title']; ?></span>
            </a>
            <?php else: ?>
            <div class="next-project empty"></div>
            <?php endif; ?>
        </div>
        
        <!-- Related Projects -->
        <?php if (!empty($relatedProjects)): ?>
        <div class="related-projects">
            <h2>Related Projects</h2>
            <div class="related-grid">
                <?php foreach($relatedProjects as $related): ?>
                <a href="<?php echo $baseUrl; ?>/projects/<?php echo $related['category']; ?>/<?php echo $related['id']; ?>" class="related-project">
                    <div class="related-image">
                        <img src="<?php echo $baseUrl; ?>/images/projects/thumbnails/<?php echo $related['thumbnail']; ?>" alt="<?php echo $related['title']; ?>">
                    </div>
                    <div class="related-title"><?php echo $related['title']; ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>
</div>
<?php require_once '../app/views/partials/footer.php'; ?>