<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Blog Post'; ?></title>

    <?php
    require_once dirname(__DIR__, 2) . '/config/config.php';
    $isEdit = isset($post);
    ?>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo $baseUrl; ?>/images/favicon/favicon-32x32.png">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">

    <style>
        .admin-container {
            min-height: 100vh;
            background: var(--bg-color);
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .admin-header {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .form-container {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid var(--border-color);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group label .required {
            color: #ef4444;
        }

        .form-group label .hint {
            font-weight: 400;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            background: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .char-count {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .editor-container {
            background: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }

        .editor-container .ql-toolbar {
            background: var(--card-bg);
            border: none;
            border-bottom: 1px solid var(--border-color);
        }

        .editor-container .ql-container {
            border: none;
            min-height: 400px;
            font-size: 1rem;
        }

        .editor-container .ql-editor {
            min-height: 400px;
            color: var(--text-color);
        }

        .image-upload {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: var(--bg-color);
        }

        .image-upload:hover {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.05);
        }

        .image-upload.has-image {
            padding: 0;
            border-style: solid;
        }

        .image-preview {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 6px;
        }

        .image-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            justify-content: center;
        }

        .tag-input-container {
            position: relative;
        }

        .tag-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .tag-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--primary-color);
            color: white;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .tag-chip .remove {
            cursor: pointer;
            font-weight: bold;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--bg-color);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            user-select: none;
        }

        .btn {
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
            margin-top: 2rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .slug-preview {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
            font-family: monospace;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .admin-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>

        <div class="admin-header">
            <div>
                <h1 style="color: var(--text-color); margin-bottom: 0.5rem;">
                    <?php echo $isEdit ? 'Edit' : 'Create New'; ?> Blog Post
                </h1>
                <p style="color: var(--text-muted);">
                    <?php echo $isEdit ? 'Update your blog post' : 'Write and publish a new blog post'; ?>
                </p>
            </div>
            <div>
                <a href="<?php echo $baseUrl; ?>/admin/blog" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Posts
                </a>
            </div>
        </div>

        <form method="POST" action="<?php echo $baseUrl; ?>/admin/<?php echo $isEdit ? 'updateBlogPost/' . $post->id : 'storeBlogPost'; ?>" class="form-container" id="blogPostForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="featured_image" id="featured_image_input" value="<?php echo $post->featured_image ?? ''; ?>">
            <input type="hidden" name="content" id="content_input">

            <div class="form-grid">
                <!-- Title -->
                <div class="form-group full-width">
                    <label for="title">
                        Title <span class="required">*</span>
                        <span class="hint">(Max 200 characters)</span>
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           class="form-control"
                           value="<?php echo htmlspecialchars($post->title ?? ''); ?>"
                           required
                           maxlength="200">
                    <div class="char-count">
                        <span id="title-count">0</span> / 200 characters
                    </div>
                </div>

                <!-- Slug -->
                <div class="form-group full-width">
                    <label for="slug">
                        URL Slug
                        <span class="hint">(Auto-generated from title, or enter custom)</span>
                    </label>
                    <input type="text"
                           id="slug"
                           name="slug"
                           class="form-control"
                           value="<?php echo htmlspecialchars($post->slug ?? ''); ?>"
                           pattern="[a-z0-9-]+"
                           title="Only lowercase letters, numbers, and hyphens allowed">
                    <div class="slug-preview">
                        Preview: <?php echo $baseUrl; ?>/blog/article/<span id="slug-preview"><?php echo htmlspecialchars($post->slug ?? 'your-post-slug'); ?></span>
                    </div>
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label for="category_id">
                        Category <span class="required">*</span>
                    </label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"
                                    <?php echo (isset($post) && $post->category_id == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="draft" <?php echo (isset($post) && $post->status === 'draft') ? 'selected' : ''; ?>>
                            Draft
                        </option>
                        <option value="published" <?php echo (isset($post) && $post->status === 'published') ? 'selected' : ''; ?>>
                            Published
                        </option>
                    </select>
                </div>
            </div>

            <!-- Excerpt -->
            <div class="form-group">
                <label for="excerpt">
                    Excerpt
                    <span class="hint">(Brief summary, max 300 characters)</span>
                </label>
                <textarea id="excerpt"
                          name="excerpt"
                          class="form-control"
                          maxlength="300"
                          rows="3"><?php echo htmlspecialchars($post->excerpt ?? ''); ?></textarea>
                <div class="char-count">
                    <span id="excerpt-count">0</span> / 300 characters
                </div>
            </div>

            <!-- Content (Quill Editor) -->
            <div class="form-group">
                <label>
                    Content <span class="required">*</span>
                </label>
                <div class="editor-container">
                    <div id="editor"><?php echo $post->content ?? ''; ?></div>
                </div>
            </div>

            <!-- Featured Image -->
            <div class="form-group">
                <label>Featured Image</label>
                <div class="image-upload" id="imageUploadArea">
                    <?php if (isset($post->featured_image) && $post->featured_image): ?>
                        <img src="<?php echo $baseUrl . $post->featured_image; ?>" alt="Featured image" class="image-preview" id="imagePreview">
                        <div class="image-actions">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="changeImage()">
                                <i class="fas fa-sync"></i> Change Image
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeImage()">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    <?php else: ?>
                        <div id="uploadPlaceholder">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <p style="color: var(--text-color); margin-bottom: 0.5rem;">Click to upload or drag and drop</p>
                            <p style="color: var(--text-muted); font-size: 0.875rem;">PNG, JPG, WEBP, GIF (Max 5MB)</p>
                        </div>
                    <?php endif; ?>
                </div>
                <input type="file" id="imageInput" accept="image/*" style="display: none;">
            </div>

            <!-- Tags -->
            <div class="form-group">
                <label for="tags_input">
                    Tags
                    <span class="hint">(Comma-separated, e.g., "php, tutorial, web dev")</span>
                </label>
                <div class="tag-input-container">
                    <input type="text"
                           id="tags_input"
                           name="tags"
                           class="form-control"
                           placeholder="Enter tags separated by commas"
                           value="<?php echo htmlspecialchars($tagsString ?? ''); ?>">
                    <div class="tag-chips" id="tagChips"></div>
                </div>
            </div>

            <!-- Featured Checkbox -->
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox"
                           id="is_featured"
                           name="is_featured"
                           value="1"
                           <?php echo (isset($post) && $post->is_featured) ? 'checked' : ''; ?>>
                    <label for="is_featured">
                        <i class="fas fa-star" style="color: #f59e0b;"></i>
                        Mark as featured post (will appear on homepage)
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="<?php echo $baseUrl; ?>/admin/blog" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" name="status" value="draft" class="btn btn-secondary">
                    <i class="fas fa-save"></i> Save as Draft
                </button>
                <button type="submit" name="status" value="published" class="btn btn-success">
                    <i class="fas fa-check"></i> <?php echo $isEdit ? 'Update' : 'Publish'; ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Quill Editor -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <script>
        // Theme management
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);

        // Initialize Quill Editor
        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    ['blockquote', 'code-block'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            },
            placeholder: 'Write your blog post content here...'
        });

        // Character counters
        const titleInput = document.getElementById('title');
        const titleCount = document.getElementById('title-count');
        const excerptInput = document.getElementById('excerpt');
        const excerptCount = document.getElementById('excerpt-count');

        titleInput.addEventListener('input', () => {
            titleCount.textContent = titleInput.value.length;
        });

        excerptInput.addEventListener('input', () => {
            excerptCount.textContent = excerptInput.value.length;
        });

        // Initialize counts
        titleCount.textContent = titleInput.value.length;
        excerptCount.textContent = excerptInput.value.length;

        // Slug generation
        const slugInput = document.getElementById('slug');
        const slugPreview = document.getElementById('slug-preview');

        titleInput.addEventListener('input', () => {
            if (!slugInput.value || slugInput.dataset.autoGenerated) {
                const slug = generateSlug(titleInput.value);
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';
                slugPreview.textContent = slug || 'your-post-slug';
            }
        });

        slugInput.addEventListener('input', () => {
            delete slugInput.dataset.autoGenerated;
            slugPreview.textContent = slugInput.value || 'your-post-slug';
        });

        function generateSlug(text) {
            return text
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }

        // Image upload
        const imageUploadArea = document.getElementById('imageUploadArea');
        const imageInput = document.getElementById('imageInput');
        const featuredImageInput = document.getElementById('featured_image_input');

        imageUploadArea.addEventListener('click', () => {
            if (!imageUploadArea.classList.contains('has-image')) {
                imageInput.click();
            }
        });

        imageInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (file) {
                await uploadImage(file);
            }
        });

        // Drag and drop
        imageUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageUploadArea.style.borderColor = 'var(--primary-color)';
        });

        imageUploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            imageUploadArea.style.borderColor = 'var(--border-color)';
        });

        imageUploadArea.addEventListener('drop', async (e) => {
            e.preventDefault();
            imageUploadArea.style.borderColor = 'var(--border-color)';
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                await uploadImage(file);
            }
        });

        async function uploadImage(file) {
            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('<?php echo $baseUrl; ?>/admin/uploadImage', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    displayImage('<?php echo $baseUrl; ?>' + data.url);
                    featuredImageInput.value = data.url;
                } else {
                    alert('Upload failed: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred during upload');
            }
        }

        function displayImage(url) {
            imageUploadArea.classList.add('has-image');
            imageUploadArea.innerHTML = `
                <img src="${url}" alt="Featured image" class="image-preview" id="imagePreview">
                <div class="image-actions">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="changeImage()">
                        <i class="fas fa-sync"></i> Change Image
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeImage()">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
            `;
        }

        function changeImage() {
            imageInput.click();
        }

        function removeImage() {
            imageUploadArea.classList.remove('has-image');
            imageUploadArea.innerHTML = `
                <div id="uploadPlaceholder">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-color); margin-bottom: 0.5rem;">Click to upload or drag and drop</p>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">PNG, JPG, WEBP, GIF (Max 5MB)</p>
                </div>
            `;
            featuredImageInput.value = '';
        }

        // Form submission
        document.getElementById('blogPostForm').addEventListener('submit', (e) => {
            // Get content from Quill and set to hidden input
            const content = quill.root.innerHTML;
            document.getElementById('content_input').value = content;

            // Basic validation
            if (!titleInput.value.trim()) {
                e.preventDefault();
                alert('Please enter a title');
                return;
            }

            if (content === '<p><br></p>' || !content.trim()) {
                e.preventDefault();
                alert('Please enter some content');
                return;
            }
        });

        // Initialize slug preview if editing
        <?php if (isset($post->slug)): ?>
        slugPreview.textContent = '<?php echo htmlspecialchars($post->slug); ?>';
        <?php endif; ?>
    </script>
</body>
</html>
