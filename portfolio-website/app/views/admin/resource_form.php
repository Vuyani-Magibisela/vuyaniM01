<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/core/Session.php';

use App\Core\Session;

// Check if user is authenticated
if (!Session::isAuthenticated()) {
    header('Location: ' . $baseUrl . '/auth/login');
    exit;
}

$isEdit = isset($resource);
$pageTitle = $isEdit ? 'Edit Resource' : 'Create New Resource';
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

        .file-upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--bg-color);
        }

        .file-upload-area:hover {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.05);
        }

        .file-upload-area.drag-over {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.1);
        }

        .file-upload-area.has-file {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
        }

        .file-info {
            display: none;
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: var(--card-bg);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .file-info.active {
            display: block;
        }

        .file-info-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .file-icon-large {
            width: 64px;
            height: 64px;
            background: var(--primary-color);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .file-details {
            flex: 1;
        }

        .file-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.25rem;
        }

        .file-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--text-muted);
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

        .image-preview {
            margin-top: 1rem;
            text-align: center;
        }

        .image-preview img {
            max-width: 200px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
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

        .file-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--primary-color);
            color: white;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .supported-formats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .format-group {
            padding: 1rem;
            background: var(--bg-color);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .format-group-title {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .format-list {
            font-size: 0.75rem;
            color: var(--text-muted);
            line-height: 1.6;
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
                    <a href="<?php echo $baseUrl; ?>/admin/resources" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Resources
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

                <form method="POST" action="<?php echo $baseUrl; ?>/admin/<?php echo $isEdit ? 'updateResource/' . $resource['id'] : 'storeResource'; ?>" id="resourceForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="file_path" id="file_path_input" value="<?php echo $resource['file_path'] ?? ''; ?>">
                    <input type="hidden" name="file_size" id="file_size_input" value="<?php echo $resource['file_size'] ?? ''; ?>">
                    <input type="hidden" name="file_type" id="file_type_input" value="<?php echo $resource['file_type'] ?? ''; ?>">
                    <input type="hidden" name="thumbnail" id="thumbnail_input" value="<?php echo $resource['thumbnail'] ?? ''; ?>">

                    <!-- Basic Information -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <i class="fas fa-info-circle"></i>
                            Basic Information
                        </h2>

                        <div class="form-group">
                            <label for="title" class="required">Resource Title</label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                class="form-control"
                                value="<?php echo htmlspecialchars($resource['title'] ?? ''); ?>"
                                required
                                maxlength="200"
                            >
                            <div class="char-counter">
                                <span id="title-counter">0</span>/200 characters
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="slug">URL Slug</label>
                            <input
                                type="text"
                                id="slug"
                                name="slug"
                                class="form-control"
                                value="<?php echo htmlspecialchars($resource['slug'] ?? ''); ?>"
                                pattern="[a-z0-9-]+"
                            >
                            <span class="form-help">Leave empty to auto-generate from title. Use only lowercase letters, numbers, and hyphens.</span>
                            <div class="slug-preview">
                                <i class="fas fa-link"></i>
                                <span><?php echo $baseUrl; ?>/resources/<span id="slug-preview">your-resource-slug</span></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea
                                id="description"
                                name="description"
                                class="form-control"
                                rows="4"
                                maxlength="1000"
                            ><?php echo htmlspecialchars($resource['description'] ?? ''); ?></textarea>
                            <span class="form-help">Brief description of what this resource contains</span>
                            <div class="char-counter">
                                <span id="description-counter">0</span>/1000 characters
                            </div>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <i class="fas fa-file-upload"></i>
                            Resource File
                        </h2>

                        <div id="file-upload" class="file-upload-area">
                            <i class="fas fa-cloud-upload-alt fa-4x" style="color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <p style="color: var(--text-color); font-size: 1.125rem; margin-bottom: 0.5rem;">Click or drag file to upload</p>
                            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">Maximum file size: 50MB</p>
                            <input type="file" id="file-input" style="display: none;">
                        </div>

                        <div id="file-info" class="file-info">
                            <div class="file-info-header">
                                <div class="file-icon-large">
                                    <i id="file-icon" class="fas fa-file"></i>
                                </div>
                                <div class="file-details">
                                    <div class="file-name" id="file-name">filename.pdf</div>
                                    <div class="file-meta">
                                        <span id="file-type-display" class="file-type-badge">PDF</span>
                                        <span id="file-size-display">2.5 MB</span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-danger" onclick="removeFile()">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>

                        <!-- Supported Formats -->
                        <div class="supported-formats">
                            <div class="format-group">
                                <div class="format-group-title"><i class="fas fa-file-pdf"></i> Documents</div>
                                <div class="format-list">PDF, DOCX, DOC, TXT</div>
                            </div>
                            <div class="format-group">
                                <div class="format-group-title"><i class="fas fa-file-archive"></i> Archives</div>
                                <div class="format-list">ZIP, RAR, TAR, GZIP</div>
                            </div>
                            <div class="format-group">
                                <div class="format-group-title"><i class="fas fa-file-code"></i> Code & Data</div>
                                <div class="format-list">JSON, CSV, SQL, PHP, JS, HTML, CSS</div>
                            </div>
                            <div class="format-group">
                                <div class="format-group-title"><i class="fas fa-file-image"></i> Images</div>
                                <div class="format-list">JPG, PNG, GIF, WEBP</div>
                            </div>
                        </div>
                    </div>

                    <!-- Thumbnail (Optional) -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <i class="fas fa-image"></i>
                            Thumbnail (Optional)
                        </h2>

                        <div id="thumbnail-upload" class="image-upload-area">
                            <i class="fas fa-image fa-3x" style="color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <p style="color: var(--text-color); margin-bottom: 0.5rem;">Click or drag image to upload</p>
                            <p style="color: var(--text-muted); font-size: 0.875rem;">Recommended size: 400x300px, Maximum: 5MB</p>
                            <input type="file" id="thumbnail-input" accept="image/*" style="display: none;">
                        </div>

                        <div id="thumbnail-preview" class="image-preview" style="display: none;">
                            <img id="thumbnail-img" src="" alt="Thumbnail">
                            <div style="margin-top: 1rem;">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeThumbnail()">
                                    <i class="fas fa-trash"></i> Remove Thumbnail
                                </button>
                            </div>
                        </div>
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
                                    <option value="draft" <?php echo (!isset($resource) || $resource['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo (isset($resource) && $resource['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                    <input
                                        type="checkbox"
                                        name="requires_login"
                                        value="1"
                                        <?php echo (isset($resource) && $resource['requires_login']) ? 'checked' : ''; ?>
                                    >
                                    <span>Require Login to Download</span>
                                </label>
                                <span class="form-help">Users must be logged in to download this resource</span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i>
                            <?php echo $isEdit ? 'Update Resource' : 'Create Resource'; ?>
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" onclick="saveDraft()">
                            <i class="fas fa-file"></i>
                            Save as Draft
                        </button>
                        <a href="<?php echo $baseUrl; ?>/admin/resources" class="btn btn-outline btn-lg">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="<?php echo $baseUrl; ?>/js/admin.js"></script>
    <script>
        const baseUrl = '<?php echo $baseUrl; ?>';

        // Setup slug generation
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        const slugPreview = document.getElementById('slug-preview');

        titleInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                const slug = generateSlug(this.value);
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';
                slugPreview.textContent = slug || 'your-resource-slug';
            }
            updateCharCounter('title', 200);
        });

        slugInput.addEventListener('input', function() {
            slugInput.dataset.autoGenerated = 'false';
            const slug = generateSlug(this.value);
            this.value = slug;
            slugPreview.textContent = slug || 'your-resource-slug';
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

        document.getElementById('description').addEventListener('input', () => updateCharCounter('description', 1000));

        // Initialize
        updateCharCounter('title', 200);
        updateCharCounter('description', 1000);
        if (titleInput.value) {
            slugPreview.textContent = slugInput.value || generateSlug(titleInput.value);
        }

        // File Upload
        const fileUpload = document.getElementById('file-upload');
        const fileInput = document.getElementById('file-input');
        const fileInfo = document.getElementById('file-info');

        fileUpload.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                uploadFile(this.files[0]);
            }
        });

        // Drag and drop
        fileUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUpload.classList.add('drag-over');
        });

        fileUpload.addEventListener('dragleave', () => {
            fileUpload.classList.remove('drag-over');
        });

        fileUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUpload.classList.remove('drag-over');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                uploadFile(e.dataTransfer.files[0]);
            }
        });

        async function uploadFile(file) {
            // Check size
            if (file.size > 50 * 1024 * 1024) {
                alert('File size must be less than 50MB');
                return;
            }

            const formData = new FormData();
            formData.append('file', file);

            try {
                fileUpload.innerHTML = '<i class="fas fa-spinner fa-spin fa-4x"></i><p>Uploading...</p>';

                const response = await fetch(`${baseUrl}/admin/uploadResourceFile`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('file_path_input').value = data.url;
                    document.getElementById('file_size_input').value = data.size;
                    document.getElementById('file_type_input').value = data.type;

                    displayFileInfo(file.name, data.type, data.size);
                    fileUpload.classList.add('has-file');
                    fileUpload.innerHTML = '<i class="fas fa-check-circle fa-4x" style="color: #10b981;"></i><p style="color: #10b981; font-weight: 600;">File uploaded successfully!</p>';
                    fileInfo.classList.add('active');
                } else {
                    alert(data.error || 'Upload failed');
                    resetFileUpload();
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert('An error occurred during upload');
                resetFileUpload();
            }
        }

        function displayFileInfo(name, type, size) {
            document.getElementById('file-name').textContent = name;
            document.getElementById('file-type-display').textContent = type.split('/')[1].toUpperCase();
            document.getElementById('file-size-display').textContent = formatFileSize(size);

            // Set icon
            const icon = getFileIcon(type);
            document.getElementById('file-icon').className = `fas ${icon}`;
        }

        function getFileIcon(type) {
            const iconMap = {
                'application/pdf': 'fa-file-pdf',
                'application/msword': 'fa-file-word',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'fa-file-word',
                'application/zip': 'fa-file-archive',
                'application/x-rar-compressed': 'fa-file-archive',
                'text/plain': 'fa-file-alt',
                'application/json': 'fa-file-code',
                'text/csv': 'fa-file-csv',
            };
            return iconMap[type] || 'fa-file';
        }

        function formatFileSize(bytes) {
            if (bytes >= 1073741824) {
                return (bytes / 1073741824).toFixed(2) + ' GB';
            } else if (bytes >= 1048576) {
                return (bytes / 1048576).toFixed(2) + ' MB';
            } else if (bytes >= 1024) {
                return (bytes / 1024).toFixed(2) + ' KB';
            } else {
                return bytes + ' bytes';
            }
        }

        function removeFile() {
            document.getElementById('file_path_input').value = '';
            document.getElementById('file_size_input').value = '';
            document.getElementById('file_type_input').value = '';
            fileInfo.classList.remove('active');
            fileUpload.classList.remove('has-file');
            resetFileUpload();
        }

        function resetFileUpload() {
            fileUpload.innerHTML = `
                <i class="fas fa-cloud-upload-alt fa-4x" style="color: var(--text-muted); margin-bottom: 1rem;"></i>
                <p style="color: var(--text-color); font-size: 1.125rem; margin-bottom: 0.5rem;">Click or drag file to upload</p>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">Maximum file size: 50MB</p>
            `;
        }

        // Load existing file
        <?php if (isset($resource['file_path']) && $resource['file_path']): ?>
        displayFileInfo(
            '<?php echo basename($resource['file_path']); ?>',
            '<?php echo $resource['file_type']; ?>',
            <?php echo $resource['file_size']; ?>
        );
        fileUpload.classList.add('has-file');
        fileUpload.innerHTML = '<i class="fas fa-check-circle fa-4x" style="color: #10b981;"></i><p style="color: #10b981; font-weight: 600;">File uploaded</p>';
        fileInfo.classList.add('active');
        <?php endif; ?>

        // Thumbnail Upload
        const thumbnailUpload = document.getElementById('thumbnail-upload');
        const thumbnailInput = document.getElementById('thumbnail-input');
        const thumbnailPreview = document.getElementById('thumbnail-preview');
        const thumbnailImg = document.getElementById('thumbnail-img');

        thumbnailUpload.addEventListener('click', () => thumbnailInput.click());
        thumbnailInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                uploadThumbnail(this.files[0]);
            }
        });

        async function uploadThumbnail(file) {
            if (!file.type.startsWith('image/')) {
                alert('Please upload an image file');
                return;
            }

            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch(`${baseUrl}/admin/uploadImage`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('thumbnail_input').value = data.url;
                    thumbnailImg.src = baseUrl + data.url;
                    thumbnailUpload.style.display = 'none';
                    thumbnailPreview.style.display = 'block';
                } else {
                    alert(data.error || 'Upload failed');
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert('An error occurred during upload');
            }
        }

        function removeThumbnail() {
            document.getElementById('thumbnail_input').value = '';
            thumbnailPreview.style.display = 'none';
            thumbnailUpload.style.display = 'block';
        }

        // Load existing thumbnail
        <?php if (isset($resource['thumbnail']) && $resource['thumbnail']): ?>
        document.getElementById('thumbnail_input').value = '<?php echo $resource['thumbnail']; ?>';
        thumbnailImg.src = baseUrl + '<?php echo $resource['thumbnail']; ?>';
        thumbnailUpload.style.display = 'none';
        thumbnailPreview.style.display = 'block';
        <?php endif; ?>

        // Form submission
        document.getElementById('resourceForm').addEventListener('submit', function(e) {
            if (!document.getElementById('file_path_input').value) {
                e.preventDefault();
                alert('Please upload a file');
                return false;
            }
        });

        function saveDraft() {
            document.getElementById('status').value = 'draft';
            document.getElementById('resourceForm').submit();
        }
    </script>
</body>
</html>
