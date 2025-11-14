/**
 * Real-time Form Validation Utility
 * Provides client-side validation with real-time feedback
 */

class FormValidator {
    constructor(form) {
        this.form = form;
        this.validationRules = new Map();
        this.errorMessages = new Map();
        this.validators = new Map();
        this.init();
    }

    /**
     * Initialize validation for the form
     */
    init() {
        // Set up real-time validation on input
        const inputs = this.form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });

        // Validate on form submission
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
            }
        });
    }

    /**
     * Add validation rule for a field
     */
    addRule(fieldName, rule, message) {
        if (!this.validationRules.has(fieldName)) {
            this.validationRules.set(fieldName, []);
            this.errorMessages.set(fieldName, []);
        }

        this.validationRules.get(fieldName).push(rule);
        this.errorMessages.get(fieldName).push(message);
    }

    /**
     * Add custom validator
     */
    addValidator(fieldName, validator) {
        this.validators.set(fieldName, validator);
    }

    /**
     * Validate a single field
     */
    validateField(field) {
        const fieldName = field.name || field.id;
        const value = field.value.trim();

        // Clear previous errors
        this.clearFieldError(field);

        // Run custom validators first
        if (this.validators.has(fieldName)) {
            const validator = this.validators.get(fieldName);
            const result = validator(value, field);
            if (result !== true) {
                this.showFieldError(field, result);
                return false;
            }
        }

        // Run standard validation rules
        if (this.validationRules.has(fieldName)) {
            const rules = this.validationRules.get(fieldName);
            const messages = this.errorMessages.get(fieldName);

            for (let i = 0; i < rules.length; i++) {
                const rule = rules[i];
                const message = messages[i];

                if (!rule(value, field)) {
                    this.showFieldError(field, message);
                    return false;
                }
            }
        }

        // Field is valid
        this.showFieldSuccess(field);
        return true;
    }

    /**
     * Validate entire form
     */
    validateForm() {
        const inputs = this.form.querySelectorAll('input, select, textarea');
        let isValid = true;

        inputs.forEach(input => {
            if (input.hasAttribute('required') || input.hasAttribute('data-validate')) {
                if (!this.validateField(input)) {
                    isValid = false;
                }
            }
        });

        return isValid;
    }

    /**
     * Show error for a field
     */
    showFieldError(field, message) {
        field.classList.add('border-red-500', 'dark:border-red-600');
        field.classList.remove('border-green-500', 'dark:border-green-600');

        // Find or create error container
        let errorContainer = field.parentElement.querySelector('.field-error');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'field-error text-red-500 text-sm mt-1';
            field.parentElement.appendChild(errorContainer);
        }

        errorContainer.textContent = message;
        errorContainer.classList.remove('hidden');
    }

    /**
     * Show success state for a field
     */
    showFieldSuccess(field) {
        field.classList.remove('border-red-500', 'dark:border-red-600');
        field.classList.add('border-green-500', 'dark:border-green-600');

        const errorContainer = field.parentElement.querySelector('.field-error');
        if (errorContainer) {
            errorContainer.classList.add('hidden');
        }
    }

    /**
     * Clear error for a field
     */
    clearFieldError(field) {
        const errorContainer = field.parentElement.querySelector('.field-error');
        if (errorContainer) {
            errorContainer.classList.add('hidden');
        }
    }

    /**
     * Standard validation rules
     */
    static rules = {
        required: (value) => value.length > 0,
        email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
        minLength: (min) => (value) => value.length >= min,
        maxLength: (max) => (value) => value.length <= max,
        pattern: (regex) => (value) => regex.test(value),
        match: (otherField) => (value) => {
            const otherValue = document.querySelector(`[name="${otherField}"]`)?.value || '';
            return value === otherValue;
        },
        numeric: (value) => !isNaN(value) && !isNaN(parseFloat(value)),
        integer: (value) => Number.isInteger(Number(value)),
        positive: (value) => parseFloat(value) > 0,
    };
}

// Export
window.FormValidator = FormValidator;

