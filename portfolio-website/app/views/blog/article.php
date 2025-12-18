<?php require_once '../app/views/partials/header.php'; ?>

<div class="container">
    <article class="blog-article">
        <div class="article-header">
            <span class="article-category">
                <a href="<?php echo $baseUrl; ?>/blog/category/<?php echo $post['category_slug']; ?>">
                    <?php echo $post['category_name']; ?>
                </a>
            </span>
            <h1 class="article-title"><?php echo $post['title']; ?></h1>
            
            <div class="article-meta">
                <span class="article-date"><?php echo date('F d, Y', strtotime($post['published_at'])); ?></span>
                <span class="article-author">
                    By <?php echo !empty($post['first_name']) ? $post['first_name'] . ' ' . $post['last_name'] : $post['author_name']; ?>
                </span>
                <span class="article-views"><?php echo $post['views']; ?> views</span>
            </div>
            
            <?php if(!empty($post['featured_image'])): ?>
                <div class="article-featured-image">
                    <img src="<?php echo $post['featured_image']; ?>" alt="<?php echo $post['title']; ?>">
                </div>
            <?php endif; ?>
        </div>
        
        <div class="article-content">
            <?php echo $post['content']; ?>
        </div>
        
        <?php if(!empty($post['tags'])): ?>
            <div class="article-tags">
                <?php foreach($post['tags'] as $tag): ?>
                    <a href="<?php echo $baseUrl; ?>/blog/tag/<?php echo $tag['slug']; ?>" class="tag"><?php echo $tag['name']; ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="article-share">
            <span class="share-label">Share this article:</span>
            <div class="share-buttons">
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($baseUrl . '/blog/article/' . $post['slug']); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" class="share-button twitter-share">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($baseUrl . '/blog/article/' . $post['slug']); ?>" target="_blank" class="share-button facebook-share">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($baseUrl . '/blog/article/' . $post['slug']); ?>&title=<?php echo urlencode($post['title']); ?>" target="_blank" class="share-button linkedin-share">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
        </div>
        
        <div class="article-author-bio">
            <div class="author-avatar">
                <img src="<?php echo $baseUrl; ?>/images/author-avatar.jpg" alt="Author Avatar">
            </div>
            <div class="author-info">
                <h3 class="author-name">
                    <?php echo !empty($post['first_name']) ? $post['first_name'] . ' ' . $post['last_name'] : $post['author_name']; ?>
                </h3>
                <p class="author-description">Passionate about technology and design, constantly exploring new ways to create and innovate. Follow along on this journey of discovery and learning.</p>
                <div class="author-social">
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                </div>
            </div>
        </div>
    </article>
    
    <?php if(!empty($relatedPosts)): ?>
        <section class="related-posts">
            <h2 class="section-title">Related Articles</h2>
            <div class="related-posts-grid">
                <?php foreach($relatedPosts as $relatedPost): ?>
                    <div class="related-post-card">
                        <div class="post-image">
                            <?php if(!empty($relatedPost['featured_image'])): ?>
                                <img src="<?php echo $baseUrl; ?>/images/blog/<?php echo $relatedPost['featured_image']; ?>" alt="<?php echo $relatedPost['title']; ?>">
                            <?php else: ?>
                                <img src="<?php echo $baseUrl; ?>/images/blog/default-post.jpg" alt="Default post image">
                            <?php endif; ?>
                        </div>
                        <div class="post-content">
                            <h3 class="post-title">
                                <a href="<?php echo $baseUrl; ?>/blog/article/<?php echo $relatedPost['slug']; ?>"><?php echo $relatedPost['title']; ?></a>
                            </h3>
                            <span class="post-date"><?php echo date('M d, Y', strtotime($relatedPost['published_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php require_once '../app/views/partials/footer.php'; ?>