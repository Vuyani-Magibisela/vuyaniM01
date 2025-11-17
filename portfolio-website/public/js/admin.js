/**
 * Admin Panel JavaScript
 * Handles interactive features for the admin interface
 */

(function() {
    'use strict';

    // ============================================
    // THEME MANAGEMENT
    // ============================================

    const html = document.documentElement;
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', savedTheme);

    // Theme toggle functionality
    window.toggleTheme = function() {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);

        // Update theme toggle icon if it exists
        const themeIcon = document.querySelector('.theme-toggle i');
        if (themeIcon) {
            themeIcon.className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }
    };

    // ============================================
    // SIDEBAR NAVIGATION
    // ============================================

    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.admin-sidebar');
        const mainContent = document.querySelector('.admin-main');
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const sidebarToggle = document.querySelector('.sidebar-toggle');

        // Mobile menu toggle
        if (mobileMenuToggle && sidebar) {
            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }

        // Desktop sidebar toggle
        if (sidebarToggle && sidebar && mainContent) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });

            // Restore sidebar state
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            }
        }

        // Close mobile sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (sidebar && sidebar.classList.contains('active')) {
                if (!sidebar.contains(e.target) && !mobileMenuToggle?.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Set active nav link based on current page
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.split('?')[0])) {
                link.classList.add('active');
            }
        });
    });

    // ============================================
    // MODAL MANAGEMENT
    // ============================================

    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal.active');
            if (activeModal) {
                activeModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
    });

    // Close modal on backdrop click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // ============================================
    // FORM VALIDATION
    // ============================================

    window.validateForm = function(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;

        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('error');

                // Show error message if it doesn't exist
                if (!field.nextElementSibling?.classList.contains('form-error')) {
                    const error = document.createElement('span');
                    error.className = 'form-error';
                    error.textContent = 'This field is required';
                    field.parentNode.appendChild(error);
                }
            } else {
                field.classList.remove('error');
                const errorMsg = field.nextElementSibling;
                if (errorMsg?.classList.contains('form-error')) {
                    errorMsg.remove();
                }
            }
        });

        return isValid;
    };

    // Real-time validation
    document.addEventListener('DOMContentLoaded', function() {
        const requiredFields = document.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                    const errorMsg = this.nextElementSibling;
                    if (errorMsg?.classList.contains('form-error')) {
                        errorMsg.remove();
                    }
                }
            });
        });
    });

    // ============================================
    // SLUG GENERATION
    // ============================================

    window.generateSlug = function(text) {
        return text
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    };

    window.setupSlugGeneration = function(titleInputId, slugInputId, slugPreviewId = null) {
        const titleInput = document.getElementById(titleInputId);
        const slugInput = document.getElementById(slugInputId);
        const slugPreview = slugPreviewId ? document.getElementById(slugPreviewId) : null;

        if (!titleInput || !slugInput) return;

        titleInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                const slug = generateSlug(this.value);
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';

                if (slugPreview) {
                    slugPreview.textContent = slug || 'your-slug-here';
                }
            }
        });

        slugInput.addEventListener('input', function() {
            this.dataset.autoGenerated = 'false';
            const slug = generateSlug(this.value);
            this.value = slug;

            if (slugPreview) {
                slugPreview.textContent = slug || 'your-slug-here';
            }
        });
    };

    // ============================================
    // IMAGE UPLOAD HANDLING
    // ============================================

    window.setupImageUpload = function(config) {
        const {
            dropZoneId,
            fileInputId,
            previewId,
            uploadUrl,
            hiddenInputId,
            maxSize = 5 * 1024 * 1024, // 5MB
            allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']
        } = config;

        const dropZone = document.getElementById(dropZoneId);
        const fileInput = document.getElementById(fileInputId);
        const preview = document.getElementById(previewId);
        const hiddenInput = document.getElementById(hiddenInputId);

        if (!dropZone || !fileInput) return;

        // Click to upload
        dropZone.addEventListener('click', function() {
            fileInput.click();
        });

        // File input change
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                handleFile(this.files[0]);
            }
        });

        // Drag and drop
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        dropZone.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                handleFile(e.dataTransfer.files[0]);
            }
        });

        function handleFile(file) {
            // Validate file type
            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Please upload an image (JPG, PNG, WEBP, or GIF).');
                return;
            }

            // Validate file size
            if (file.size > maxSize) {
                alert(`File is too large. Maximum size is ${maxSize / 1024 / 1024}MB.`);
                return;
            }

            // Show loading state
            if (preview) {
                preview.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Uploading...</p></div>';
            }

            // Upload file
            const formData = new FormData();
            formData.append('image', file);

            fetch(uploadUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Display preview
                    if (preview) {
                        preview.innerHTML = `
                            <img src="${data.url}" alt="Uploaded image" style="max-width: 100%; border-radius: 8px;">
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage('${previewId}', '${hiddenInputId}')">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        `;
                    }

                    // Set hidden input value
                    if (hiddenInput) {
                        hiddenInput.value = data.url;
                    }
                } else {
                    alert(data.error || 'Upload failed');
                    if (preview) {
                        preview.innerHTML = '<p class="text-muted">Click or drag to upload</p>';
                    }
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                alert('An error occurred during upload');
                if (preview) {
                    preview.innerHTML = '<p class="text-muted">Click or drag to upload</p>';
                }
            });
        }
    };

    window.removeImage = function(previewId, hiddenInputId) {
        const preview = document.getElementById(previewId);
        const hiddenInput = document.getElementById(hiddenInputId);

        if (preview) {
            preview.innerHTML = '<p class="text-muted">Click or drag to upload</p>';
        }
        if (hiddenInput) {
            hiddenInput.value = '';
        }
    };

    // ============================================
    // CONFIRMATION DIALOGS
    // ============================================

    window.confirmDelete = function(message = 'Are you sure you want to delete this item?') {
        return confirm(message);
    };

    // ============================================
    // AJAX HELPERS
    // ============================================

    window.ajaxRequest = function(url, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        return fetch(url, options)
            .then(response => response.json())
            .catch(error => {
                console.error('AJAX error:', error);
                throw error;
            });
    };

    // ============================================
    // TOGGLE FEATURED
    // ============================================

    window.setupFeaturedToggle = function(selector, baseUrl) {
        document.querySelectorAll(selector).forEach(toggle => {
            toggle.addEventListener('click', async function() {
                const itemId = this.dataset.itemId;
                const itemType = this.dataset.itemType || 'post';
                const isActive = this.classList.contains('active');

                try {
                    const response = await fetch(`${baseUrl}/admin/toggleFeatured/${itemId}?type=${itemType}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.classList.toggle('active');
                        this.title = isActive ? 'Mark as featured' : 'Remove from featured';
                    } else {
                        alert('Failed to toggle featured status');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred');
                }
            });
        });
    };

    // ============================================
    // CHARACTER COUNTER
    // ============================================

    window.setupCharacterCounter = function(inputId, counterId, maxLength) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(counterId);

        if (!input || !counter) return;

        function updateCounter() {
            const length = input.value.length;
            counter.textContent = `${length}/${maxLength}`;

            if (length > maxLength) {
                counter.style.color = '#ef4444';
            } else if (length > maxLength * 0.9) {
                counter.style.color = '#f59e0b';
            } else {
                counter.style.color = 'var(--text-muted)';
            }
        }

        input.addEventListener('input', updateCounter);
        updateCounter();
    };

    // ============================================
    // AUTO-SAVE DRAFT
    // ============================================

    window.setupAutoSave = function(formId, saveUrl, interval = 60000) {
        const form = document.getElementById(formId);
        if (!form) return;

        let autoSaveTimer;
        let hasChanges = false;

        // Track changes
        form.addEventListener('input', function() {
            hasChanges = true;
        });

        // Auto-save function
        function autoSave() {
            if (!hasChanges) return;

            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hasChanges = false;
                    console.log('Draft saved automatically');

                    // Show toast notification
                    showToast('Draft saved', 'success');
                }
            })
            .catch(error => {
                console.error('Auto-save error:', error);
            });
        }

        // Set up interval
        autoSaveTimer = setInterval(autoSave, interval);

        // Save before leaving page
        window.addEventListener('beforeunload', function(e) {
            if (hasChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    };

    // ============================================
    // TOAST NOTIFICATIONS
    // ============================================

    window.showToast = function(message, type = 'info', duration = 3000) {
        const toastContainer = getOrCreateToastContainer();

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="fas fa-${getToastIcon(type)}"></i>
            <span>${message}</span>
        `;

        toastContainer.appendChild(toast);

        // Animate in
        setTimeout(() => toast.classList.add('show'), 10);

        // Remove after duration
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };

    function getOrCreateToastContainer() {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    function getToastIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    // ============================================
    // TABLE SORTING
    // ============================================

    window.setupTableSorting = function(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const headers = table.querySelectorAll('th[data-sortable]');

        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.innerHTML += ' <i class="fas fa-sort sort-icon"></i>';

            header.addEventListener('click', function() {
                const column = this.dataset.sortable;
                const currentOrder = this.dataset.order || 'asc';
                const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

                // Update icon
                const icon = this.querySelector('.sort-icon');
                icon.className = `fas fa-sort-${newOrder === 'asc' ? 'up' : 'down'} sort-icon`;

                // Reset other headers
                headers.forEach(h => {
                    if (h !== this) {
                        const i = h.querySelector('.sort-icon');
                        if (i) i.className = 'fas fa-sort sort-icon';
                        h.dataset.order = '';
                    }
                });

                this.dataset.order = newOrder;

                // Sort table
                sortTable(table, column, newOrder);
            });
        });
    };

    function sortTable(table, column, order) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const aVal = a.querySelector(`[data-sort-value="${column}"]`)?.textContent || '';
            const bVal = b.querySelector(`[data-sort-value="${column}"]`)?.textContent || '';

            if (order === 'asc') {
                return aVal.localeCompare(bVal);
            } else {
                return bVal.localeCompare(aVal);
            }
        });

        rows.forEach(row => tbody.appendChild(row));
    }

    // ============================================
    // SEARCH FILTER
    // ============================================

    window.setupSearchFilter = function(searchInputId, targetSelector) {
        const searchInput = document.getElementById(searchInputId);
        if (!searchInput) return;

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const items = document.querySelectorAll(targetSelector);

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    };

    // ============================================
    // INITIALIZATION
    // ============================================

    console.log('Admin JS loaded successfully');

})();
