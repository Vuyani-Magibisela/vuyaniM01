<?php
require_once '../app/core/Session.php';
use App\Core\Session;
$csrfToken = Session::generateCsrfToken();
?>
<?php require_once '../app/views/partials/header.php'; ?>

<div class="container">
    <?php if (!empty($isPreview)): ?>
    <div class="preview-banner">
        <i class="fas fa-eye"></i>
        <span>You are previewing a draft — this post is not yet published.</span>
        <a href="<?php echo $baseUrl; ?>/admin/editBlogPost/<?php echo $post['id']; ?>">Back to Editor</a>
    </div>
    <?php endif; ?>

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

            <?php if (!empty($post['featured_image'])): ?>
                <div class="article-featured-image">
                    <img src="<?php echo $post['featured_image']; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                </div>
            <?php endif; ?>
        </div>

        <div class="article-content">
            <?php echo $post['content']; ?>
        </div>

        <?php if (!empty($post['tags'])): ?>
            <div class="article-tags">
                <?php foreach ($post['tags'] as $tag): ?>
                    <a href="<?php echo $baseUrl; ?>/blog/tag/<?php echo $tag['slug']; ?>" class="tag"><?php echo $tag['name']; ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $absoluteUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $baseUrl . '/blog/article/' . $post['slug'];
        ?>
        <div class="article-share">
            <span class="share-label">Share this article:</span>
            <div class="share-buttons">
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($absoluteUrl); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" rel="noopener" class="share-button x-share" aria-label="Share on X">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($absoluteUrl); ?>" target="_blank" rel="noopener" class="share-button facebook-share" aria-label="Share on Facebook">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($absoluteUrl); ?>" target="_blank" rel="noopener" class="share-button linkedin-share" aria-label="Share on LinkedIn">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.063 2.063 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                </a>
                <a href="https://wa.me/?text=<?php echo urlencode($post['title'] . ' — ' . $absoluteUrl); ?>" target="_blank" rel="noopener" class="share-button whatsapp-share" aria-label="Share on WhatsApp">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M11.999 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.946-1.424A9.953 9.953 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18a7.946 7.946 0 01-4.337-1.277l-.31-.185-3.233.93.948-3.152-.203-.322A7.944 7.944 0 014 12c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z"/></svg>
                </a>
            </div>
        </div>

        <!-- Emoji Reactions -->
        <?php if (empty($isPreview)): ?>
        <div class="article-reactions">
            <span class="reactions-label">How did this land?</span>
            <div class="reaction-pills" data-post-id="<?php echo (int)$post['id']; ?>">
                <?php
                $emojiMap = ['like' => '👍', 'love' => '❤️', 'fire' => '🔥', 'clap' => '👏', 'wow' => '😮'];
                foreach ($emojiMap as $key => $emoji):
                    $count = (int)($reactionCounts[$key] ?? 0);
                ?>
                <button class="reaction-pill" data-emoji="<?php echo $key; ?>" type="button">
                    <span class="reaction-emoji"><?php echo $emoji; ?></span>
                    <span class="reaction-count"><?php echo $count > 0 ? $count : ''; ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php
            $authorAvatar = !empty($post['profile_image'])
                ? $post['profile_image']
                : $baseUrl . '/images/author-avatar.jpg';
            $authorBio = !empty($post['bio'])
                ? $post['bio']
                : 'Passionate about technology and design, constantly exploring new ways to create and innovate. Follow along on this journey of discovery and learning.';
        ?>
        <div class="article-author-bio">
            <div class="author-avatar">
                <img src="<?php echo $authorAvatar; ?>" alt="Author Avatar" onerror="this.src='<?php echo $baseUrl; ?>/images/author-avatar.jpg'">
            </div>
            <div class="author-info">
                <h3 class="author-name">
                    <?php echo !empty($post['first_name']) ? $post['first_name'] . ' ' . $post['last_name'] : $post['author_name']; ?>
                </h3>
                <p class="author-description"><?php echo nl2br(htmlspecialchars($authorBio)); ?></p>
            </div>
        </div>
    </article>

    <?php if (!empty($relatedPosts)): ?>
        <section class="related-posts">
            <h2 class="section-title">Related Articles</h2>
            <div class="related-posts-grid">
                <?php foreach ($relatedPosts as $relatedPost): ?>
                    <a href="<?php echo $baseUrl; ?>/blog/article/<?php echo $relatedPost['slug']; ?>" class="related-post-card">
                        <div class="post-image">
                            <?php if (!empty($relatedPost['featured_image'])): ?>
                                <img src="<?php echo $relatedPost['featured_image']; ?>" alt="<?php echo htmlspecialchars($relatedPost['title']); ?>">
                            <?php else: ?>
                                <img src="<?php echo $baseUrl; ?>/images/blog/default-post.jpg" alt="Default post image">
                            <?php endif; ?>
                        </div>
                        <div class="post-content">
                            <h3 class="post-title"><?php echo $relatedPost['title']; ?></h3>
                            <span class="post-date"><?php echo date('M d, Y', strtotime($relatedPost['published_at'])); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Comments Section -->
    <?php if (empty($isPreview)): ?>
    <section class="comments-section">
        <?php if (!empty($comments)): ?>
        <div class="comments-list">
            <h2 class="section-title">
                <?php echo count($comments); ?> Comment<?php echo count($comments) !== 1 ? 's' : ''; ?>
            </h2>
            <?php foreach ($comments as $c): ?>
            <div class="comment-item">
                <div class="comment-avatar">
                    <?php echo strtoupper(mb_substr($c['author_name'], 0, 1)); ?>
                </div>
                <div class="comment-body">
                    <div class="comment-meta">
                        <span class="comment-author"><?php echo htmlspecialchars($c['author_name']); ?></span>
                        <span class="comment-date"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></span>
                    </div>
                    <p class="comment-content"><?php echo nl2br(htmlspecialchars($c['content'])); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="comment-form-wrap">
            <h2 class="section-title">Leave a Comment</h2>
            <p class="comment-notice">Your email address will not be published. Comments are reviewed before appearing.</p>

            <div id="comment-feedback" class="comment-feedback" style="display:none;"></div>

            <form id="commentForm" class="comment-form" novalidate>
                <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <!-- Honeypot — must stay empty -->
                <input type="text" name="website" class="comment-honeypot" tabindex="-1" autocomplete="off">

                <div class="comment-form-row">
                    <div class="comment-form-group">
                        <label for="author_name">Name <span class="required">*</span></label>
                        <input type="text" id="author_name" name="author_name" required minlength="2" maxlength="100" placeholder="Your name">
                    </div>
                    <div class="comment-form-group">
                        <label for="author_email">Email <span class="required">*</span></label>
                        <input type="email" id="author_email" name="author_email" required placeholder="your@email.com">
                    </div>
                </div>
                <div class="comment-form-group">
                    <label for="comment_content">Comment <span class="required">*</span></label>
                    <textarea id="comment_content" name="content" required minlength="10" maxlength="2000" rows="5" placeholder="Share your thoughts..."></textarea>
                    <div class="char-counter"><span id="charCount">0</span> / 2000</div>
                </div>
                <button type="submit" class="btn-comment-submit" id="commentSubmitBtn">
                    Post Comment
                </button>
            </form>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
(function () {
    var BASE = '<?php echo $baseUrl; ?>';
    var CSRF = '<?php echo htmlspecialchars($csrfToken); ?>';

    /* ── Emoji reactions ── */
    var pills = document.querySelectorAll('.reaction-pill');
    pills.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var emoji    = btn.dataset.emoji;
            var postId   = btn.closest('.reaction-pills').dataset.postId;
            var countEl  = btn.querySelector('.reaction-count');

            fetch(BASE + '/blog/react', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'post_id=' + postId + '&emoji=' + encodeURIComponent(emoji) + '&csrf_token=' + encodeURIComponent(CSRF)
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.success) return;
                countEl.textContent = d.count > 0 ? d.count : '';
                btn.classList.toggle('reacted', d.action === 'added');
            })
            .catch(function () {});
        });
    });

    /* ── Comment textarea char counter ── */
    var textarea = document.getElementById('comment_content');
    var charCount = document.getElementById('charCount');
    if (textarea && charCount) {
        textarea.addEventListener('input', function () {
            charCount.textContent = textarea.value.length;
        });
    }

    /* ── Comment form submission ── */
    var form     = document.getElementById('commentForm');
    var feedback = document.getElementById('comment-feedback');
    var submitBtn = document.getElementById('commentSubmitBtn');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.textContent = 'Posting…';

            var data = new URLSearchParams(new FormData(form)).toString();

            fetch(BASE + '/blog/comment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: data
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                feedback.style.display = 'block';
                if (d.success) {
                    feedback.className = 'comment-feedback comment-feedback--success';
                    feedback.textContent = d.message;
                    form.reset();
                    if (charCount) charCount.textContent = '0';
                } else {
                    feedback.className = 'comment-feedback comment-feedback--error';
                    feedback.textContent = d.error || 'Something went wrong. Please try again.';
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Post Comment';
                feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            })
            .catch(function () {
                feedback.style.display = 'block';
                feedback.className = 'comment-feedback comment-feedback--error';
                feedback.textContent = 'Network error. Please try again.';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Post Comment';
            });
        });
    }
})();
</script>

<?php require_once '../app/views/partials/footer.php'; ?>
