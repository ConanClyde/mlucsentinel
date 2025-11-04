/**
 * Form Loading States Utility
 * Provides standardized loading states for form submissions
 */

class FormLoader {
    /**
     * Show loading state on form submission
     */
    static showLoading(form, options = {}) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (!submitButton) {
            return;
        }

        const originalText = submitButton.innerHTML;
        const originalDisabled = submitButton.disabled;

        // Store original state
        submitButton.dataset.originalText = originalText;
        submitButton.dataset.originalDisabled = originalDisabled;

        // Disable button and show loading
        submitButton.disabled = true;
        const loadingText = options.loadingText || 'Processing...';
        const spinner = '<svg class="animate-spin h-5 w-5 inline-block mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        submitButton.innerHTML = spinner + loadingText;

        // Add loading class to form
        form.classList.add('form-loading');
        form.dataset.loading = 'true';

        // Disable all form inputs
        const inputs = form.querySelectorAll('input, select, textarea, button');
        inputs.forEach(input => {
            if (input !== submitButton && !input.disabled) {
                input.dataset.originalDisabled = input.disabled;
                input.disabled = true;
            }
        });
    }

    /**
     * Hide loading state and restore form
     */
    static hideLoading(form) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (!submitButton) {
            return;
        }

        // Restore button state
        const originalText = submitButton.dataset.originalText;
        const originalDisabled = submitButton.dataset.originalDisabled === 'true';

        submitButton.innerHTML = originalText || 'Submit';
        submitButton.disabled = originalDisabled;

        // Remove loading class
        form.classList.remove('form-loading');
        delete form.dataset.loading;

        // Re-enable all form inputs
        const inputs = form.querySelectorAll('input, select, textarea, button');
        inputs.forEach(input => {
            if (input.dataset.originalDisabled !== undefined) {
                input.disabled = input.dataset.originalDisabled === 'true';
                delete input.dataset.originalDisabled;
            }
        });
    }

    /**
     * Wrap fetch with automatic loading state management
     */
    static async fetchWithLoading(form, url, options = {}) {
        this.showLoading(form, { loadingText: options.loadingText });

        try {
            const response = await fetch(url, options);
            return response;
        } catch (error) {
            throw error;
        } finally {
            // Don't hide loading here - let the caller handle it after processing response
            // This allows for success/error handling before restoring form
        }
    }

    /**
     * Initialize automatic loading states for all forms
     */
    static init() {
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form.tagName || form.tagName !== 'FORM') {
                return;
            }

            // Skip if form already has loading state or is handled manually
            if (form.dataset.loading === 'true' || form.dataset.manualLoading === 'true') {
                return;
            }

            // Show loading state
            FormLoader.showLoading(form);
        });
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => FormLoader.init());
} else {
    FormLoader.init();
}

// Export for use in other modules
window.FormLoader = FormLoader;

