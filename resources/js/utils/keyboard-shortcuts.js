/**
 * Keyboard Shortcuts Utility
 * Provides keyboard shortcuts for common actions
 */

class KeyboardShortcuts {
    constructor() {
        this.shortcuts = new Map();
        this.enabled = true;
        this.init();
    }

    /**
     * Initialize keyboard shortcuts
     */
    init() {
        document.addEventListener('keydown', (e) => {
            if (!this.enabled) {
                return;
            }

            // Don't trigger shortcuts when typing in inputs
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                // Allow Ctrl/Cmd combinations even in inputs
                if (!e.ctrlKey && !e.metaKey) {
                    return;
                }
            }

            const key = this.getKeyString(e);
            if (this.shortcuts.has(key)) {
                e.preventDefault();
                const callback = this.shortcuts.get(key);
                callback(e);
            }
        });

        // Register default shortcuts
        this.registerDefaults();
    }

    /**
     * Register a keyboard shortcut
     */
    register(key, callback, description = '') {
        this.shortcuts.set(key, callback);
    }

    /**
     * Unregister a keyboard shortcut
     */
    unregister(key) {
        this.shortcuts.delete(key);
    }

    /**
     * Enable/disable shortcuts
     */
    setEnabled(enabled) {
        this.enabled = enabled;
    }

    /**
     * Get key string representation
     */
    getKeyString(e) {
        const parts = [];

        if (e.ctrlKey) {
            parts.push('ctrl');
        }
        if (e.metaKey) {
            parts.push('meta');
        }
        if (e.altKey) {
            parts.push('alt');
        }
        if (e.shiftKey) {
            parts.push('shift');
        }

        // Normalize key
        let key = e.key.toLowerCase();
        if (key === ' ') {
            key = 'space';
        }

        parts.push(key);
        return parts.join('+');
    }

    /**
     * Register default shortcuts
     */
    registerDefaults() {
        // Save form (Ctrl+S or Cmd+S)
        this.register('ctrl+s', (e) => {
            const form = document.querySelector('form:not([data-no-shortcuts])');
            if (form && !form.dataset.loading) {
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton && !submitButton.disabled) {
                    form.requestSubmit();
                }
            }
        });

        // Close modal (Escape)
        this.register('escape', () => {
            const modals = document.querySelectorAll('.modal-backdrop:not(.hidden)');
            modals.forEach(modal => {
                const closeButton = modal.querySelector('[data-close-modal]');
                if (closeButton) {
                    closeButton.click();
                } else {
                    modal.click(); // Close by clicking backdrop
                }
            });
        });

        // Search focus (Ctrl+K or Cmd+K)
        this.register('ctrl+k', () => {
            const searchInput = document.querySelector('input[type="search"], input[placeholder*="Search" i], input[id*="search" i]');
            if (searchInput && !searchInput.disabled) {
                searchInput.focus();
                searchInput.select();
            }
        });

        // Navigate to dashboard (Ctrl+D or Cmd+D)
        this.register('ctrl+d', () => {
            if (window.location.pathname !== '/dashboard') {
                window.location.href = '/dashboard';
            }
        });

        // Navigate to home (Ctrl+H or Cmd+H)
        this.register('ctrl+h', () => {
            if (window.location.pathname !== '/home') {
                window.location.href = '/home';
            }
        });

        // Open settings (Ctrl+, or Cmd+,)
        this.register('ctrl+,', () => {
            if (window.location.pathname !== '/settings') {
                window.location.href = '/settings';
            }
        });

        // Toggle dark mode (Ctrl+Shift+D or Cmd+Shift+D)
        this.register('ctrl+shift+d', () => {
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.click();
            }
        });
    }

    /**
     * Show help modal with all shortcuts
     */
    showHelp() {
        const shortcuts = Array.from(this.shortcuts.entries()).map(([key, callback]) => ({
            key,
            description: callback.description || '',
        }));

        console.log('Available keyboard shortcuts:', shortcuts);
        // Could show a modal here with all shortcuts
    }
}

// Create singleton instance
const keyboardShortcuts = new KeyboardShortcuts();

// Export
window.KeyboardShortcuts = keyboardShortcuts;

