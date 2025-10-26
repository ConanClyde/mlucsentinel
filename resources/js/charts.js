import Chart from 'chart.js/auto';

// Laravel-style color palette
const laravelColors = {
    primary: {
        50: '#eff6ff',
        100: '#dbeafe', 
        200: '#bfdbfe',
        300: '#93c5fd',
        400: '#60a5fa',
        500: '#3b82f6',
        600: '#2563eb',
        700: '#1d4ed8',
        800: '#1e40af',
        900: '#1e3a8a',
    },
    success: {
        50: '#f0fdf4',
        100: '#dcfce7',
        200: '#bbf7d0',
        300: '#86efac',
        400: '#4ade80',
        500: '#22c55e',
        600: '#16a34a',
        700: '#15803d',
        800: '#166534',
        900: '#14532d',
    },
    warning: {
        50: '#fefce8',
        100: '#fef3c7',
        200: '#fde68a',
        300: '#fcd34d',
        400: '#fbbf24',
        500: '#f59e0b',
        600: '#d97706',
        700: '#b45309',
        800: '#92400e',
        900: '#78350f',
    },
    danger: {
        50: '#fef2f2',
        100: '#fee2e2',
        200: '#fecaca',
        300: '#fca5a5',
        400: '#f87171',
        500: '#ef4444',
        600: '#dc2626',
        700: '#b91c1c',
        800: '#991b1b',
        900: '#7f1d1d',
    },
    info: {
        50: '#f0f9ff',
        100: '#e0f2fe',
        200: '#bae6fd',
        300: '#7dd3fc',
        400: '#38bdf8',
        500: '#0ea5e9',
        600: '#0284c7',
        700: '#0369a1',
        800: '#075985',
        900: '#0c4a6e',
    }
};

// Function to detect dark mode
function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

// Laravel-style theme colors
function getLaravelThemeColors() {
    if (isDarkMode()) {
        return {
            text: '#A1A09A',
            textSecondary: '#706f6c',
            border: '#3E3E3A',
            background: '#1a1a1a',
            surface: '#161615',
            grid: '#2a2a2a',
            primary: laravelColors.primary[400],
            success: laravelColors.success[400],
            warning: laravelColors.warning[400],
            danger: laravelColors.danger[400],
            info: laravelColors.info[400],
        };
    } else {
        return {
            text: '#1b1b18',
            textSecondary: '#706f6c',
            border: '#e3e3e0',
            background: '#ffffff',
            surface: '#f9fafb',
            grid: '#f3f4f6',
            primary: laravelColors.primary[600],
            success: laravelColors.success[600],
            warning: laravelColors.warning[600],
            danger: laravelColors.danger[600],
            info: laravelColors.info[600],
        };
    }
}

// Laravel-style Chart.js defaults
Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif';
Chart.defaults.font.size = 12;

// Function to update Chart.js defaults based on current theme
function updateChartDefaults() {
    const colors = getLaravelThemeColors();
    Chart.defaults.color = colors.textSecondary;
    Chart.defaults.borderColor = colors.border;
    Chart.defaults.backgroundColor = colors.surface;
}

// Initialize with current theme
updateChartDefaults();

// Laravel-style chart options
function getLaravelChartOptions() {
    const colors = getLaravelThemeColors();
    
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: colors.text,
                    font: {
                        family: 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif',
                        size: 12
                    },
                    padding: 20
                }
            },
            tooltip: {
                backgroundColor: colors.background,
                titleColor: colors.text,
                bodyColor: colors.textSecondary,
                borderColor: colors.border,
                borderWidth: 1,
                cornerRadius: 6,
                displayColors: true,
                padding: 12
            }
        },
        scales: {
            x: {
                grid: {
                    color: colors.grid,
                    drawBorder: false
                },
                ticks: {
                    color: colors.textSecondary,
                    font: {
                        family: 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif',
                        size: 11
                    }
                },
                border: {
                    color: colors.border
                }
            },
            y: {
                grid: {
                    color: colors.grid,
                    drawBorder: false
                },
                ticks: {
                    color: colors.textSecondary,
                    font: {
                        family: 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif',
                        size: 11
                    }
                },
                border: {
                    color: colors.border
                }
            }
        }
    };
}

// Function to update existing charts when theme changes
function updateChartsForTheme() {
    updateChartDefaults();
    
    // Update all existing charts
    Object.values(Chart.instances).forEach(function(chart) {
        const colors = getLaravelThemeColors();
        
        // Update chart options
        if (chart.options && chart.options.plugins) {
            if (chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                chart.options.plugins.legend.labels.color = colors.text;
            }
            if (chart.options.plugins.tooltip) {
                chart.options.plugins.tooltip.backgroundColor = colors.background;
                chart.options.plugins.tooltip.titleColor = colors.text;
                chart.options.plugins.tooltip.bodyColor = colors.textSecondary;
                chart.options.plugins.tooltip.borderColor = colors.border;
            }
        }
        
        // Update scales
        if (chart.options && chart.options.scales) {
            if (chart.options.scales.x) {
                if (chart.options.scales.x.grid) {
                    chart.options.scales.x.grid.color = colors.grid;
                }
                if (chart.options.scales.x.ticks) {
                    chart.options.scales.x.ticks.color = colors.textSecondary;
                }
                if (chart.options.scales.x.border) {
                    chart.options.scales.x.border.color = colors.border;
                }
            }
            if (chart.options.scales.y) {
                if (chart.options.scales.y.grid) {
                    chart.options.scales.y.grid.color = colors.grid;
                }
                if (chart.options.scales.y.ticks) {
                    chart.options.scales.y.ticks.color = colors.textSecondary;
                }
                if (chart.options.scales.y.border) {
                    chart.options.scales.y.border.color = colors.border;
                }
            }
        }
        
        chart.update();
    });
}

// Listen for dark mode changes
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            // Small delay to ensure DOM is updated
            setTimeout(updateChartsForTheme, 50);
        }
    });
});

// Start observing the document element for class changes
observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
});

// Also listen for manual theme toggle events
document.addEventListener('DOMContentLoaded', function() {
    // Check if there's a theme toggle button and listen to it
    const themeToggle = document.querySelector('[data-theme-toggle]') || 
                       document.querySelector('.theme-toggle') ||
                       document.querySelector('#theme-toggle');
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            setTimeout(updateChartsForTheme, 100);
        });
    }
});

// Export Chart.js and Laravel utilities
window.Chart = Chart;
window.laravelColors = laravelColors;
window.getLaravelThemeColors = getLaravelThemeColors;
window.getLaravelChartOptions = getLaravelChartOptions;
window.updateChartsForTheme = updateChartsForTheme;
window.isDarkMode = isDarkMode;
