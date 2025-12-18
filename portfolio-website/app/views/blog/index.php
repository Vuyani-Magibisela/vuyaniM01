<?php require_once '../app/views/partials/header.php'; ?>

<div class="container">
    <section class="blog-header">
        <h1 class="page-title">Exploring Tech & Creativity</h1>
        <p class="page-subtitle">Insights, tutorials, and resources to inspire your journey</p>
    </section>

    <section class="blog-content">
        <div class="posts-container">
            <?php if(!empty($featuredPosts)): ?>
            <div class="featured-posts">
                <h2 class="section-title">Featured Articles</h2>
                <div class="featured-posts-grid">
                    <?php foreach($featuredPosts as $post): ?>
                        <div class="featured-post-card">
                            <div class="post-image">
                                <?php if(!empty($post['featured_image'])): ?>
                                    <img src="<?php echo $post['featured_image']; ?>" alt="<?php echo $post['title']; ?>">
                                <?php else: ?>
                                    <img src="<?php echo $baseUrl; ?>/images/blog/default-post.jpg" alt="Default post image">
                                <?php endif; ?>
                            </div>
                            <div class="post-content">
                                <span class="post-category"><?php echo $post['category_name']; ?></span>
                                <h3 class="post-title"><?php echo $post['title']; ?></h3>
                                <p class="post-excerpt"><?php echo $post['excerpt']; ?></p>
                                <div class="post-meta">
                                    <span class="post-date"><?php echo date('M d, Y', strtotime($post['published_at'])); ?></span>
                                    <a href="<?php echo $baseUrl; ?>/blog/article/<?php echo $post['slug']; ?>" class="read-more">Read More</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="latest-articles-section">
                <h2 class="section-title">Latest Articles</h2>

                <?php if(empty($posts)): ?>
                    <div class="no-posts-message">
                        <p>No articles available yet. Check back soon!</p>
                    </div>
                <?php else: ?>
                    <div class="posts-grid">
                        <?php foreach($posts as $post): ?>
                            <div class="post-card">
                                <div class="post-image">
                                    <?php if(!empty($post['featured_image'])): ?>
                                        <img src="<?php echo $post['featured_image']; ?>" alt="<?php echo $post['title']; ?>">
                                    <?php else: ?>
                                        <img src="<?php echo $baseUrl; ?>/images/blog/default-post.jpg" alt="Default post image">
                                    <?php endif; ?>
                                    <span class="post-category"><?php echo $post['category_name']; ?></span>
                                </div>
                                <div class="post-content">
                                    <h3 class="post-title">
                                        <a href="<?php echo $baseUrl; ?>/blog/article/<?php echo $post['slug']; ?>"><?php echo $post['title']; ?></a>
                                    </h3>
                                    <p class="post-excerpt"><?php echo $post['excerpt']; ?></p>
                                    <div class="post-meta">
                                        <span class="post-date"><?php echo date('M d, Y', strtotime($post['published_at'])); ?></span>
                                        <span class="post-author">by <?php echo $post['author_name']; ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="blog-sidebar">
            <div class="sidebar-widget categories-widget">
                <h3 class="widget-title">Categories</h3>
                <ul class="category-list">
                    <li><a href="<?php echo $baseUrl; ?>/blog">All Articles</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/blog/category/articles">Articles</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/blog/category/tutorials">Tutorials</a></li>
                    <li><a href="<?php echo $baseUrl; ?>/blog/resources">Resources</a></li>
                </ul>
            </div>

            <div class="sidebar-widget newsletter-widget">
                <h3 class="widget-title">Subscribe</h3>
                <p>Stay updated with my latest articles and resources</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit" class="subscribe-btn">Subscribe</button>
                </form>
            </div>

            <div class="sidebar-widget resources-widget">
                <h3 class="widget-title">Popular Resources</h3>
                <div class="resource-links">
                    <a href="<?php echo $baseUrl; ?>/blog/resources" class="resource-link">
                        <span class="resource-icon">📚</span>
                        <span class="resource-title">View All Resources</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once '../app/views/partials/footer.php'; ?>
