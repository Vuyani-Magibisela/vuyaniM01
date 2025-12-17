<?php require_once '../app/views/partials/header.php'; ?>
<div class="container">
    <section class="projects-section">
        <h1>My Projects</h1>
        <p class="projects-intro">Explore my work across various disciplines, from web development to 3D design and maker projects.</p>

        <!-- Project Filter System -->
        <div class="project-filters">
            <button class="filter-btn active" data-filter="all">All Projects</button>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <button class="filter-btn" data-filter="<?php echo htmlspecialchars($category['slug']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </button>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback if no categories in database -->
                <button class="filter-btn" data-filter="web-dev">Web Dev</button>
                <button class="filter-btn" data-filter="app-dev">App Dev</button>
                <button class="filter-btn" data-filter="game-dev">Game Dev</button>
                <button class="filter-btn" data-filter="digital-design">Digital Design</button>
                <button class="filter-btn" data-filter="maker">Maker</button>
            <?php endif; ?>
        </div>

        <!-- Projects Grid -->
        <div class="projects-grid">
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $project):
                    // Get category slug for data attribute
                    $categorySlug = '';
                    foreach ($categories as $cat) {
                        if ($cat['id'] == $project['category_id']) {
                            $categorySlug = $cat['slug'];
                            break;
                        }
                    }

                    // Parse technologies into array
                    $technologies = !empty($project['technologies']) ? explode(',', $project['technologies']) : [];

                    // Fallback image
                    $fallbackImage = $baseUrl . '/images/skillsImg02.jpeg';
                ?>
                    <div class="project-card" data-category="<?php echo htmlspecialchars($categorySlug); ?>">
                        <div class="project-image">
                            <?php if (!empty($project['featured_image'])): ?>
                                <img src="<?php echo $baseUrl . htmlspecialchars($project['featured_image']); ?>"
                                     alt="<?php echo htmlspecialchars($project['title']); ?>"
                                     onerror="this.src='<?php echo $fallbackImage; ?>'; this.style.width='100%'; this.style.height='100%'; this.style.objectFit='cover';">
                            <?php else: ?>
                                <img src="<?php echo $fallbackImage; ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="project-content">
                            <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                            <p><?php echo htmlspecialchars($project['description'] ?? substr(strip_tags($project['content']), 0, 120) . '...'); ?></p>

                            <?php if (!empty($technologies)): ?>
                                <div class="project-tags">
                                    <?php foreach ($technologies as $tech):
                                        $tech = trim($tech);
                                        if (!empty($tech)):
                                    ?>
                                        <span class="project-tag"><?php echo htmlspecialchars($tech); ?></span>
                                    <?php
                                        endif;
                                    endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <a href="<?php echo $baseUrl; ?>/projects/detail/<?php echo htmlspecialchars($project['slug']); ?>" class="project-link">View Project</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- No Projects Message -->
                <div class="no-projects-message" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <h2 style="color: #666; margin-bottom: 10px;">No Projects Yet</h2>
                    <p style="color: #999;">Projects will appear here once they are published.</p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo $baseUrl; ?>/admin/createProject" class="btn" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #f5b642; color: #fff; border-radius: 5px; text-decoration: none;">
                            Create Your First Project
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- No Results Message (shown by JS filter) -->
        <div class="no-results" style="display: none;">
            <p>No projects found matching your filter criteria.</p>
            <button class="reset-filter-btn">Show All Projects</button>
        </div>
    </section>
</div>

<script src="<?php echo $baseUrl; ?>/js/projects.js"></script>

<?php require_once '../app/views/partials/footer.php'; ?>
