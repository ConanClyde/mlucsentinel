/**
 * Appearance Settings - Theme Management
 */

export function initializeAppearance() {
    const currentTheme = localStorage.getItem('theme') || 'light';
    updateThemeSelection(currentTheme);
    syncNavbarThemeState(currentTheme);
}

export function setThemePreference(theme) {
    const html = document.documentElement;
    
    if (theme === 'dark') {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
    
    updateThemeSelection(theme);
    syncNavbarThemeState(theme);
    
    // Update charts if they exist
    if (typeof updateChartsForTheme === 'function') {
        setTimeout(updateChartsForTheme, 100);
    }
}

export function syncNavbarThemeState(theme) {
    const sunIcon = document.getElementById('sun-icon');
    const moonIcon = document.getElementById('moon-icon');
    
    if (sunIcon && moonIcon) {
        if (theme === 'dark') {
            sunIcon.classList.remove('hidden');
            sunIcon.classList.add('block');
            moonIcon.classList.remove('block');
            moonIcon.classList.add('hidden');
        } else {
            sunIcon.classList.remove('block');
            sunIcon.classList.add('hidden');
            moonIcon.classList.remove('hidden');
            moonIcon.classList.add('block');
        }
    }
}

export function updateThemeSelection(theme) {
    // Remove selection from all theme options
    document.querySelectorAll('.theme-option').forEach(option => {
        option.classList.remove('border-blue-500', 'border-2');
        option.classList.add('border-[#e3e3e0]', 'dark:border-[#3E3E3A]');
    });
    
    // Add selection to current theme
    const selectedTheme = document.getElementById(`theme-${theme}`);
    if (selectedTheme) {
        selectedTheme.classList.add('border-blue-500', 'border-2');
        selectedTheme.classList.remove('border-[#e3e3e0]', 'dark:border-[#3E3E3A]');
    }
    
    // Also sync navbar state
    syncNavbarThemeState(theme);
}

// Listen for theme changes from navbar
export function setupThemeSync() {
    // Listen for storage events (when theme is changed in another tab/window)
    window.addEventListener('storage', function(e) {
        if (e.key === 'theme') {
            const theme = e.newValue || 'light';
            updateThemeSelection(theme);
            syncNavbarThemeState(theme);
        }
    });
    
    // Watch for theme class changes on html element (from navbar toggle)
    const html = document.documentElement;
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const isDark = html.classList.contains('dark');
                const theme = isDark ? 'dark' : 'light';
                updateThemeSelection(theme);
            }
        });
    });
    
    observer.observe(html, {
        attributes: true,
        attributeFilter: ['class']
    });
}

// Make functions globally available
window.setThemePreference = setThemePreference;
window.updateThemeSelection = updateThemeSelection;
window.syncNavbarThemeState = syncNavbarThemeState;

