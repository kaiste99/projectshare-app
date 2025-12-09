/**
 * ProjectShare - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const navbarToggle = document.getElementById('navbarToggle');
    const navbarMenu = document.getElementById('navbarMenu');

    if (navbarToggle && navbarMenu) {
        navbarToggle.addEventListener('click', () => {
            navbarMenu.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navbarToggle.contains(e.target) && !navbarMenu.contains(e.target)) {
                navbarMenu.classList.remove('active');
            }
        });
    }

    // Alert dismissal
    document.querySelectorAll('.alert-close').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.alert').remove();
        });
    });

    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Form validation styling
    document.querySelectorAll('form').forEach(form => {
        form.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('invalid', () => {
                input.classList.add('is-invalid');
            });

            input.addEventListener('input', () => {
                if (input.validity.valid) {
                    input.classList.remove('is-invalid');
                }
            });
        });
    });

    // Copy to clipboard functionality
    document.querySelectorAll('[data-copy]').forEach(btn => {
        btn.addEventListener('click', async function() {
            const text = this.dataset.copy;
            try {
                await navigator.clipboard.writeText(text);
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check"></i> Copied!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            } catch (err) {
                console.error('Failed to copy:', err);
            }
        });
    });

    // Confirm dialogs for delete actions
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // File upload preview
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const preview = document.getElementById(this.dataset.preview);
            if (preview && this.files[0]) {
                if (this.files[0].type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            }
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Dropdown menus
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');

        if (toggle && menu) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('show');
            });
        }
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });

    // Tab functionality
    document.querySelectorAll('[data-tab]').forEach(tabBtn => {
        tabBtn.addEventListener('click', function() {
            const tabGroup = this.closest('.tabs');
            const targetId = this.dataset.tab;
            const targetPanel = document.getElementById(targetId);

            if (tabGroup && targetPanel) {
                // Deactivate all tabs
                tabGroup.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));

                // Hide all panels
                const panelContainer = targetPanel.parentElement;
                panelContainer.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

                // Activate selected tab and panel
                this.classList.add('active');
                targetPanel.classList.add('active');
            }
        });
    });

    // Modal functionality
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        const backdrop = document.querySelector('.modal-backdrop') || createBackdrop();

        if (modal) {
            backdrop.classList.add('active');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        const backdrop = document.querySelector('.modal-backdrop');

        if (modal) {
            modal.classList.remove('active');
        }

        if (backdrop) {
            backdrop.classList.remove('active');
        }

        document.body.style.overflow = '';
    };

    function createBackdrop() {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.addEventListener('click', () => {
            document.querySelectorAll('.modal.active').forEach(modal => {
                closeModal(modal.id);
            });
        });
        document.body.appendChild(backdrop);
        return backdrop;
    }

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.active').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });

    // Close modal buttons
    document.querySelectorAll('.modal-close, [data-dismiss="modal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });

    // AJAX form submission helper
    window.submitFormAjax = async function(form, options = {}) {
        const url = form.action || window.location.href;
        const method = form.method || 'POST';
        const formData = new FormData(form);

        try {
            const response = await fetch(url, {
                method: method,
                body: new URLSearchParams(formData)
            });

            const data = await response.json();

            if (options.onSuccess && data.success) {
                options.onSuccess(data);
            } else if (options.onError && !data.success) {
                options.onError(data);
            }

            return data;
        } catch (error) {
            if (options.onError) {
                options.onError({ error: 'Network error' });
            }
            throw error;
        }
    };

    // Format file size
    window.formatFileSize = function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    };

    // Format date
    window.formatDate = function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    // Debounce function
    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };
});

// Service Worker registration for PWA (optional)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // Uncomment to enable service worker
        // navigator.serviceWorker.register('/sw.js');
    });
}
