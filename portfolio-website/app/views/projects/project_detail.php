<?php require_once '../app/views/partials/header.php'; ?>
<div class="container">
    <section class="project-detail">
        <!-- Project Header -->
        <div class="project-header">
            <div class="project-breadcrumb">
                <a href="<?php echo $baseUrl; ?>/projects">Projects</a> /
                <span><?php echo htmlspecialchars($project['title']); ?></span>
            </div>
            <h1 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h1>
            <div class="project-metadata">
                <?php if (!empty($project['completion_date'])): ?>
                    <div class="project-date">Completed: <?php echo date('F Y', strtotime($project['completion_date'])); ?></div>
                <?php endif; ?>

                <?php if (!empty($project['category_name'])): ?>
                    <div class="project-category">
                        <span class="category-badge"><?php echo htmlspecialchars($project['category_name']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($project['technologies'])):
                    $technologies = array_map('trim', explode(',', $project['technologies']));
                ?>
                    <div class="project-tags">
                        <?php foreach($technologies as $tech): ?>
                            <span class="project-tag"><?php echo htmlspecialchars($tech); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Project Gallery -->
        <div class="project-gallery">
            <?php if (!empty($project['featured_image'])): ?>
                <div class="project-main-image">
                    <img id="main-image" src="<?php echo htmlspecialchars($project['featured_image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                </div>
            <?php endif; ?>

            <?php if (!empty($project['images']) && count($project['images']) > 0): ?>
                <div class="project-thumbnails">
                    <?php foreach($project['images'] as $image): ?>
                        <div class="thumbnail" data-image="<?php echo htmlspecialchars($image['image_path']); ?>">
                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>"
                                 alt="<?php echo htmlspecialchars($image['caption'] ?? $project['title']); ?>"
                                 onclick="document.getElementById('main-image').src = this.src;">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Project Description -->
        <div class="project-description">
            <?php if (!empty($project['description'])): ?>
                <h2>Project Overview</h2>
                <div class="description-content">
                    <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($project['content'])): ?>
                <h2>Details</h2>
                <div class="description-content">
                    <?php echo $project['content']; // Already HTML from Quill editor ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($project['client'])): ?>
                <h2>Client</h2>
                <div class="client-info">
                    <p><?php echo htmlspecialchars($project['client']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($project['technologies'])):
                $technologies = array_map('trim', explode(',', $project['technologies']));
            ?>
                <h2>Technologies Used</h2>
                <div class="tech-list">
                    <ul>
                        <?php foreach($technologies as $tech): ?>
                            <li><?php echo htmlspecialchars($tech); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($project['project_url']) || !empty($project['github_url'])): ?>
                <div class="project-links">
                    <?php if (!empty($project['project_url'])): ?>
                        <a href="<?php echo htmlspecialchars($project['project_url']); ?>" class="cta-button" target="_blank" rel="noopener noreferrer">
                            View Live Project
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($project['github_url'])): ?>
                        <a href="<?php echo htmlspecialchars($project['github_url']); ?>" class="github-link" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-github"></i> View on GitHub
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Related Projects -->
        <?php if (!empty($relatedProjects) && count($relatedProjects) > 0): ?>
            <div class="related-projects">
                <h2>Related Projects</h2>
                <div class="related-grid">
                    <?php foreach($relatedProjects as $related): ?>
                        <a href="<?php echo $baseUrl; ?>/projects/detail/<?php echo htmlspecialchars($related['slug']); ?>" class="related-project">
                            <div class="related-image">
                                <?php if (!empty($related['featured_image'])): ?>
                                    <img src="<?php echo $baseUrl . htmlspecialchars($related['featured_image']); ?>"
                                         alt="<?php echo htmlspecialchars($related['title']); ?>"
                                         onerror="this.src='<?php echo $baseUrl; ?>/images/skillsImg02.jpeg';">
                                <?php else: ?>
                                    <img src="<?php echo $baseUrl; ?>/images/skillsImg02.jpeg" alt="<?php echo htmlspecialchars($related['title']); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="related-title"><?php echo htmlspecialchars($related['title']); ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Back to Projects -->
        <div class="back-to-projects">
            <a href="<?php echo $baseUrl; ?>/projects" class="btn-back">← Back to All Projects</a>
        </div>
    </section>
</div>
<?php require_once '../app/views/partials/footer.php'; ?>
