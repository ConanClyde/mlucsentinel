/**
 * Global Error Handler
 * Provides centralized error handling, logging, and retry mechanisms
 */

class ErrorHandler {
    constructor() {
        this.retryAttempts = new Map();
        this.maxRetries = 3;
        this.retryDelay = 1000; // 1 second
    }

    /**
     * Handle JavaScript errors
     */
    handleError(error, context = 'application') {
        const errorInfo = {
            message: error.message,
            stack: error.stack,
            context: context,
            url: window.location.href,
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString(),
        };

        // Log to console
        console.error(`[${context}]`, error, errorInfo);

        // Send to server for logging (optional)
        this.logToServer(errorInfo).catch(err => {
            console.warn('Failed to log error to server:', err);
        });

        // Show user-friendly notification
        this.showErrorNotification(error, context);
    }

    /**
     * Handle API errors with retry mechanism
     */
    async handleApiError(error, url, options = {}, retryCount = 0) {
        const shouldRetry = this.shouldRetry(error, retryCount);

        if (shouldRetry) {
            const delay = this.retryDelay * Math.pow(2, retryCount); // Exponential backoff
            console.log(`Retrying request to ${url} (attempt ${retryCount + 1}/${this.maxRetries}) after ${delay}ms`);

            await this.sleep(delay);

            try {
                const response = await fetch(url, options);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response;
            } catch (retryError) {
                return this.handleApiError(retryError, url, options, retryCount + 1);
            }
        }

        // Max retries reached or non-retryable error
        this.handleError(error, 'api');
        throw error;
    }

    /**
     * Determine if error should be retried
     */
    shouldRetry(error, retryCount) {
        if (retryCount >= this.maxRetries) {
            return false;
        }

        // Retry on network errors or 5xx server errors
        if (error instanceof TypeError && error.message.includes('fetch')) {
            return true;
        }

        if (error.status >= 500 && error.status < 600) {
            return true;
        }

        // Don't retry on 4xx client errors
        if (error.status >= 400 && error.status < 500) {
            return false;
        }

        return false;
    }

    /**
     * Log error to server
     */
    async logToServer(errorInfo) {
        try {
            await fetch('/api/log-error', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                },
                body: JSON.stringify(errorInfo),
            });
        } catch (err) {
            // Silently fail - don't log logging errors
        }
    }

    /**
     * Show error notification to user
     */
    showErrorNotification(error, context) {
        // Check if notification functions exist (from app.js or specific pages)
        if (typeof showErrorModal === 'function') {
            let message = 'An unexpected error occurred.';

            if (error.message) {
                message = error.message;
            } else if (context === 'api') {
                message = 'Failed to complete the request. Please try again.';
            }

            showErrorModal(message);
        } else {
            // Fallback to alert if modal function doesn't exist
            console.error('Error:', error);
        }
    }

    /**
     * Sleep utility for delays
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Wrapper for fetch with automatic error handling and retry
     */
    async fetchWithRetry(url, options = {}) {
        try {
            const response = await fetch(url, options);

            if (!response.ok) {
                const error = new Error(`HTTP ${response.status}: ${response.statusText}`);
                error.status = response.status;
                throw error;
            }

            return response;
        } catch (error) {
            return this.handleApiError(error, url, options);
        }
    }

    /**
     * Initialize global error handlers
     */
    init() {
        // Handle unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            this.handleError(event.reason, 'unhandled-rejection');
        });

        // Handle global JavaScript errors
        window.addEventListener('error', (event) => {
            this.handleError(event.error || event.message, 'global-error');
        });

        console.log('Global error handler initialized');
    }
}

// Create singleton instance
const errorHandler = new ErrorHandler();

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => errorHandler.init());
} else {
    errorHandler.init();
}

// Export for use in other modules
window.ErrorHandler = errorHandler;

