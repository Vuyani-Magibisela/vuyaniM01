<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/core/Session.php';

use App\Core\Session;

// Check if user is authenticated
if (!Session::isAuthenticated()) {
    header('Location: ' . $baseUrl . '/auth/login');
    exit;
}

$isEdit = isset($project);
$pageTitle = $isEdit ? 'Edit Project' : 'Create New Project';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/admin.css">

    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <style>
        .form-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .editor-container {
            background: var(--bg-color);
            border-radius: 8px;
            overflow: hidden;
        }

        #editor {
            min-height: 400px;
            background: white;
        }

        .ql-editor {
            min-height: 400px;
            font-size: 1rem;
            line-height: 1.6;
        }

        .ql-toolbar {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
        }

        .image-upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--bg-color);
        }

        .image-upload-area:hover {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.05);
        }

        .image-upload-area.drag-over {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.1);
        }

        .image-preview {
            margin-top: 1rem;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        .image-preview img {
            max-width: 100%;
            border-radius: 8px;
        }

        .image-preview-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .gallery-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .gallery-item:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .gallery-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .gallery-item-actions {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            display: flex;
            gap: 0.25rem;
        }

        .slug-preview {
            margin-top: 0.5rem;
            padding: 0.75rem 1rem;
            background: var(--bg-color);
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.875rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .char-counter {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .form-actions {
            position: sticky;
            bottom: 0;
            background: var(--card-bg);
            border-top: 1px solid var(--border-color);
            padding: 1.5rem 2rem;
            margin: 0 -2rem -2rem;
            border-radius: 0 0 12px 12px;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .tech-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .tech-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            background: var(--primary-color);
            color: white;
            border-radius: 16px;
            font-size: 0.875rem;
        }

        .tech-tag button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 0;
            margin-left: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include dirname(__DIR__) . '/partials/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?php echo $pageTitle; ?></h1>
                </div>
                <div class="admin-header-right">
                    <a href="<?php echo $baseUrl; ?>/admin/projects" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Projects
                    </a>
                </div>
            </header>

            <!-- Content -->
            <div class="admin-content">
                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo $baseUrl; ?>/admin/<?php echo $isEdit ? 'updateProject/' . $project['id'] : 'storeProject'; ?>" id="projectForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="featured_image" id="featured_image_input" value="<?php echo $project['featured_image'] ?? ''; ?>">
                    <input type="hidden" name="content" id="content_input">
                    <input type="hidden" name="gallery_images" id="gallery_images_input" value="[]">

                    <!-- Basic Information -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <i class="fas fa-info-circle"></i>
                            Basic Information
                        </h2>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="title" class="required">Project Title</label>
                                <input
                                    type="text"
                                    id="title"
                                    name="title"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($project['title'] ?? ''); ?>"
                                    required
                                    maxlength="200"
                                >
                                <div class="char-counter">
                                    <span id="title-counter">0</span>/200 characters
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="category_id" class="required">Category</label>
                                <select id="category_id" name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($project) && $project['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="slug">URL Slug</label>
                            <input
                                type="text"
                                id="slug"
                                name="slug"
                                class="form-control"
                                value="<?php echo htmlspecialchars($project['slug'] ?? ''); ?>"
                                pattern="[a-z0-9-]+"
                            >
                            <span class="form-help">Leave empty to auto-generate from title. Use only lowercase letters, numbers, and hyphens.</span>
                            <div class="slug-preview">
                                <i class="fas fa-link"></i>
                                <span><?php echo $baseUrl; ?>/projects/<span id="slug-preview">your-project-slug</span></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Short Description</label>
                            <textarea
                                id="description"
                                name="description"
                                class="form-control"
                                rows="3"
                                maxlength="500"
                            ><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
                            <span class="form-help">Brief summary for listings and previews</span>
                            <div class="char-counter">
                                <span id="description-counter">0</span>/500 characters
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <i class="fas fa-file-alt"></i>
                            Project Details
                        </h2>

                        <div class="form-group">
                            <label class="required">Content</label>
                            <div class="editor-container">
                                <div id="editor"><?php echo $project['content'] ?? ''; ?></div>
                            </div>
                            <span class="form-help">Full project description, features, and details</span>
                        </div>
                    </div>

                    <!-- Project Info -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <i class="fas fa-cogs"></i>
                            Project Information
                        </h2>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="client">Client Name</label>
                                <input
                                    type="text"
                                    id="client"
                                    name="client"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($project['client'] ?? ''); ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label for="completion_date">Completion Date</label>
                                <input
                                    type="date"
                                    id="completion_date"
                                    name="completion_date"
                                    class="form-control"
                                    value="<?php echo $project['completion_date'] ?? ''; ?>"
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="technologies">Technologies Used</label>
                            <input
                                type="text"
                                id="technologies"
                                name="technologies"
                                class="form-control"
                                value="<?php echo htmlspecialchars($project['technologies'] ?? ''); ?>"
                                placeholder="PHP, JavaScript, MySQL, React"
                            >
                            <span class="form-help">Comma-separated list of technologies</span>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="project_url">Project URL</label>
                                <input
                                    type="url"
                                    id="project_url"
                                    name="project_url"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($project['project_url'] ?? ''); ?>"
                                    placeholder="https://example.com"
                                >
                            </div>

                            <div class="form-group">
                                <label for="github_url">GitHub URL</label>
                                <input
                                    type="url"
                                    id="github_url"
                                    name="github_url"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($project['github_url'] ?? ''); ?>"
                                    placeholder="https://github.com/username/repo"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <i class="fas fa-image"></i>
                            Featured Image
                        </h2>

                        <div id="featured-image-upload" class="image-upload-area">
                            <i class="fas fa-cloud-upload-alt fa-3x" style="color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <p style="color: var(--text-color); margin-bottom: 0.5rem;">Click or drag image to upload</p>
                            <p style="color: var(--text-muted); font-size: 0.875rem;">Maximum file size: 5MB</p>
                            <input type="file" id="featured-image-input" accept="image/*" style="display: none;">
                        </div>

                        <div id="featured-image-preview" class="image-preview" style="display: none;">
                            <img id="featured-image-img" src="" alt="Featured image">
                            <div class="image-preview-actions">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeFeaturedImage()">
                                    <i class="fas fa-trash"></i> Remove Image
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Gallery Images -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <i class="fas fa-images"></i>
                            Project Gallery
                        </h2>

                        <div id="gallery-upload" class="image-upload-area">
                            <i class="fas fa-images fa-3x" style="color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <p style="color: var(--text-color); margin-bottom: 0.5rem;">Click or drag multiple images to upload</p>
                            <p style="color: var(--text-muted); font-size: 0.875rem;">You can upload multiple images at once</p>
                            <input type="file" id="gallery-input" accept="image/*" multiple style="display: none;">
                        </div>

                        <div id="gallery-grid" class="gallery-grid"></div>
                    </div>

                    <!-- Settings -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <i class="fas fa-sliders-h"></i>
                            Settings
                        </h2>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="draft" <?php echo (!isset($project) || $project['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo (isset($project) && $project['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                    <input
                                        type="checkbox"
                                        name="is_featured"
                                        value="1"
                                        <?php echo (isset($project) && $project['is_featured']) ? 'checked' : ''; ?>
                                    >
                                    <span>Featured Project</span>
                                </label>
                                <span class="form-help">Featured projects appear first on the homepage</span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i>
                            <?php echo $isEdit ? 'Update Project' : 'Create Project'; ?>
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" onclick="saveDraft()">
                            <i class="fas fa-file"></i>
                            Save as Draft
                        </button>
                        <a href="<?php echo $baseUrl; ?>/admin/projects" class="btn btn-outline btn-lg">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="<?php echo $baseUrl; ?>/js/admin.js"></script>
    <script>
        const baseUrl = '<?php echo $baseUrl; ?>';
        let galleryImages = [];

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
            placeholder: 'Write your project details here...'
        });

        // Setup slug generation
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        const slugPreview = document.getElementById('slug-preview');

        titleInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                const slug = generateSlug(this.value);
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';
                slugPreview.textContent = slug || 'your-project-slug';
            }
            updateCharCounter('title', 200);
        });

        slugInput.addEventListener('input', function() {
            slugInput.dataset.autoGenerated = 'false';
            const slug = generateSlug(this.value);
            this.value = slug;
            slugPreview.textContent = slug || 'your-project-slug';
        });

        // Character counters
        function updateCharCounter(fieldId, max) {
            const field = document.getElementById(fieldId);
            const counter = document.getElementById(`${fieldId}-counter`);
            counter.textContent = field.value.length;

            if (field.value.length > max) {
                counter.style.color = '#ef4444';
            } else if (field.value.length > max * 0.9) {
                counter.style.color = '#f59e0b';
            } else {
                counter.style.color = 'var(--text-muted)';
            }
        }

        document.getElementById('description').addEventListener('input', () => updateCharCounter('description', 500));

        // Initialize counters
        updateCharCounter('title', 200);
        updateCharCounter('description', 500);
        if (titleInput.value) {
            slugPreview.textContent = slugInput.value || generateSlug(titleInput.value);
        }

        // Featured Image Upload
        const featuredUpload = document.getElementById('featured-image-upload');
        const featuredInput = document.getElementById('featured-image-input');
        const featuredPreview = document.getElementById('featured-image-preview');
        const featuredImg = document.getElementById('featured-image-img');
        const featuredImageInput = document.getElementById('featured_image_input');

        featuredUpload.addEventListener('click', () => featuredInput.click());
        featuredInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                uploadFeaturedImage(this.files[0]);
            }
        });

        // Drag and drop for featured image
        featuredUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            featuredUpload.classList.add('drag-over');
        });

        featuredUpload.addEventListener('dragleave', () => {
            featuredUpload.classList.remove('drag-over');
        });

        featuredUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            featuredUpload.classList.remove('drag-over');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                uploadFeaturedImage(e.dataTransfer.files[0]);
            }
        });

        async function uploadFeaturedImage(file) {
            if (!file.type.startsWith('image/')) {
                alert('Please upload an image file');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                return;
            }

            const formData = new FormData();
            formData.append('image', file);
            formData.append('type', 'featured');

            try {
                featuredUpload.innerHTML = '<i class="fas fa-spinner fa-spin fa-3x"></i><p>Uploading...</p>';

                const response = await fetch(`${baseUrl}/admin/uploadProjectImage`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    featuredImageInput.value = data.url;
                    featuredImg.src = baseUrl + data.url;
                    featuredUpload.style.display = 'none';
                    featuredPreview.style.display = 'block';
                } else {
                    alert(data.error || 'Upload failed');
                    resetFeaturedUpload();
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert('An error occurred during upload');
                resetFeaturedUpload();
            }
        }

        function resetFeaturedUpload() {
            featuredUpload.innerHTML = `
                <i class="fas fa-cloud-upload-alt fa-3x" style="color: var(--text-muted); margin-bottom: 1rem;"></i>
                <p style="color: var(--text-color); margin-bottom: 0.5rem;">Click or drag image to upload</p>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Maximum file size: 5MB</p>
            `;
        }

        function removeFeaturedImage() {
            featuredImageInput.value = '';
            featuredPreview.style.display = 'none';
            featuredUpload.style.display = 'block';
            resetFeaturedUpload();
        }

        // Load existing featured image
        <?php if (isset($project['featured_image']) && $project['featured_image']): ?>
        featuredImageInput.value = '<?php echo $project['featured_image']; ?>';
        featuredImg.src = baseUrl + '<?php echo $project['featured_image']; ?>';
        featuredUpload.style.display = 'none';
        featuredPreview.style.display = 'block';
        <?php endif; ?>

        // Gallery Upload
        const galleryUpload = document.getElementById('gallery-upload');
        const galleryInput = document.getElementById('gallery-input');
        const galleryGrid = document.getElementById('gallery-grid');

        galleryUpload.addEventListener('click', () => galleryInput.click());
        galleryInput.addEventListener('change', function() {
            if (this.files) {
                Array.from(this.files).forEach(file => uploadGalleryImage(file));
            }
        });

        // Drag and drop for gallery
        galleryUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            galleryUpload.classList.add('drag-over');
        });

        galleryUpload.addEventListener('dragleave', () => {
            galleryUpload.classList.remove('drag-over');
        });

        galleryUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            galleryUpload.classList.remove('drag-over');
            if (e.dataTransfer.files) {
                Array.from(e.dataTransfer.files).forEach(file => uploadGalleryImage(file));
            }
        });

        async function uploadGalleryImage(file) {
            if (!file.type.startsWith('image/')) {
                alert('Please upload image files only');
                return;
            }

            const formData = new FormData();
            formData.append('image', file);
            formData.append('type', 'gallery');

            try {
                const response = await fetch(`${baseUrl}/admin/uploadProjectImage`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    galleryImages.push(data.url);
                    addGalleryItem(data.url);
                    updateGalleryInput();
                } else {
                    alert(data.error || 'Upload failed');
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert('An error occurred during upload');
            }
        }

        function addGalleryItem(imageUrl) {
            const item = document.createElement('div');
            item.className = 'gallery-item';
            item.innerHTML = `
                <img src="${baseUrl}${imageUrl}" alt="Gallery image">
                <div class="gallery-item-actions">
                    <button type="button" class="btn btn-sm btn-danger btn-icon" onclick="removeGalleryImage('${imageUrl}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            galleryGrid.appendChild(item);
        }

        function removeGalleryImage(imageUrl) {
            galleryImages = galleryImages.filter(url => url !== imageUrl);
            updateGalleryInput();
            renderGallery();
        }

        function renderGallery() {
            galleryGrid.innerHTML = '';
            galleryImages.forEach(url => addGalleryItem(url));
        }

        function updateGalleryInput() {
            document.getElementById('gallery_images_input').value = JSON.stringify(galleryImages);
        }

        // Load existing gallery images
        <?php if (isset($project['images']) && !empty($project['images'])): ?>
        galleryImages = <?php echo json_encode(array_column($project['images'], 'image_path')); ?>;
        renderGallery();
        updateGalleryInput();
        <?php endif; ?>

        // Form submission
        document.getElementById('projectForm').addEventListener('submit', function(e) {
            // Get Quill content
            const content = quill.root.innerHTML;
            document.getElementById('content_input').value = content;

            // Validate
            if (!content || content === '<p><br></p>') {
                e.preventDefault();
                alert('Please add project content');
                return false;
            }
        });

        function saveDraft() {
            document.getElementById('status').value = 'draft';
            document.getElementById('projectForm').submit();
        }
    </script>
</body>
</html>
