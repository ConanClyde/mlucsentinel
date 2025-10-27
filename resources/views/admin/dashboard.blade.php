@extends('layouts.app')

@section('page-title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Dashboard Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-users class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Total Users</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ number_format($stats['total_users']) }}</p>
                </div>
            </div>
        </div>

        <!-- Active Vehicles Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-truck class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Active Vehicles</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ number_format($stats['total_vehicles']) }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Reports Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Pending Reports</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]" data-stat="pending_reports">{{ number_format($stats['pending_reports']) }}</p>
                </div>
            </div>
        </div>

        <!-- Total Reports Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-document-text class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Total Reports</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ number_format($stats['total_reports']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Violations Per Day Chart -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Violations Per Day (Last 30 Days)</h3>
            <div class="h-64">
                <canvas id="violationsPerDayChart"></canvas>
            </div>
        </div>

        <!-- Report Status Distribution -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Report Status</h3>
            <div class="h-64">
                <canvas id="reportStatusChart"></canvas>
            </div>
        </div>

        <!-- Vehicle Types Chart -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Vehicle Types</h3>
            <div class="h-64">
                <canvas id="vehicleTypesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Reporters -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Top Reporters</h3>
            <div class="space-y-3">
                @forelse($topReporters as $index => $reporter)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $reporter->first_name }} {{ $reporter->last_name }}</p>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ ucfirst(str_replace('_', ' ', $reporter->user_type)) }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $reporter->reports_count }}</span>
                    </div>
                @empty
                    <p class="text-[#706f6c] dark:text-[#A1A09A] text-center py-4">No reports submitted yet</p>
                @endforelse
            </div>
        </div>

        <!-- Top Violators -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Top Violators</h3>
            <div class="space-y-3">
                @forelse($topViolators as $index => $violator)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                <span class="text-sm font-semibold text-red-600 dark:text-red-400">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $violator->first_name }} {{ $violator->last_name }}</p>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ ucfirst(str_replace('_', ' ', $violator->user_type)) }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $violator->vehicles_count }}</span>
                    </div>
                @empty
                    <p class="text-[#706f6c] dark:text-[#A1A09A] text-center py-4">No violations recorded yet</p>
                @endforelse
            </div>
        </div>

        <!-- Top Violation Locations -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Top Violation Locations</h3>
            <div class="space-y-3">
                @forelse($topLocations as $index => $location)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                <span class="text-sm font-semibold text-yellow-600 dark:text-yellow-400">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] truncate">{{ $location->location }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $location->count }}</span>
                    </div>
                @empty
                    <p class="text-[#706f6c] dark:text-[#A1A09A] text-center py-4">No violation locations recorded yet</p>
                @endforelse
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = getLaravelThemeColors();
    const baseOptions = getLaravelChartOptions();
    
    // Initialize reports array for real-time updates
    window.reports = @json($reports ?? []);

    // Violations Per Day Chart (Area Chart)
    const violationsPerDayCtx = document.getElementById('violationsPerDayChart').getContext('2d');
    const violationsPerDayData = @json($violationsPerDay);
    const violationsPerDayLabels = Object.keys(violationsPerDayData).map(date => new Date(date).toLocaleDateString());
    const violationsPerDayValues = Object.values(violationsPerDayData);
    
    const violationsPerDayChart = new Chart(violationsPerDayCtx, {
        type: 'line',
        data: {
            labels: violationsPerDayLabels.length > 0 ? violationsPerDayLabels : ['No Data'],
            datasets: [{
                label: 'Violations',
                data: violationsPerDayValues.length > 0 ? violationsPerDayValues : [0],
                borderColor: colors.danger,
                backgroundColor: colors.danger + '20', // 20% opacity
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.danger,
                pointBorderColor: colors.background,
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            ...baseOptions,
            plugins: {
                ...baseOptions.plugins,
                legend: {
                    ...baseOptions.plugins.legend,
                    position: 'top'
                }
            }
        }
    });

    // Report Status Chart (Doughnut Chart)
    const reportStatusCtx = document.getElementById('reportStatusChart').getContext('2d');
    const reportStatusData = @json($reportsByStatus ?? []);
    const reportStatusLabels = Object.keys(reportStatusData).map(status => 
        status.charAt(0).toUpperCase() + status.slice(1)
    );
    const reportStatusValues = Object.values(reportStatusData);
    
    const reportStatusChart = new Chart(reportStatusCtx, {
        type: 'doughnut',
        data: {
            labels: reportStatusLabels.length > 0 ? reportStatusLabels : ['No Data'],
            datasets: [{
                data: reportStatusValues.length > 0 ? reportStatusValues : [1],
                backgroundColor: [
                    '#FFC107', // Yellow for Pending (from ReportStatus Enum)
                    '#28A745', // Green for Approved
                    '#DC3545'  // Red for Rejected
                ],
                borderWidth: 3,
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--color-background') || '#FFFFFF',
                hoverOffset: 8
            }]
        },
        options: {
            ...baseOptions,
            plugins: {
                ...baseOptions.plugins,
                legend: {
                    ...baseOptions.plugins.legend,
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });

    // Vehicle Types Chart (Doughnut Chart - Same UI as Report Status)
    const vehicleTypesCtx = document.getElementById('vehicleTypesChart').getContext('2d');
    const vehicleTypesData = @json($vehicleTypes);
    const vehicleTypesLabels = Object.keys(vehicleTypesData);
    const vehicleTypesValues = Object.values(vehicleTypesData);
    
    const vehicleTypesChart = new Chart(vehicleTypesCtx, {
        type: 'doughnut',
        data: {
            labels: vehicleTypesLabels.length > 0 ? vehicleTypesLabels : ['No Data'],
            datasets: [{
                data: vehicleTypesValues.length > 0 ? vehicleTypesValues : [1],
                backgroundColor: [
                    '#007BFF', // Blue for first type
                    '#28A745', // Green for second type
                    '#FFC107', // Yellow for third type
                    '#E83E8C', // Pink for fourth type
                    '#6366F1'  // Purple for fifth type
                ],
                borderWidth: 3,
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--color-background') || '#FFFFFF',
                hoverOffset: 8
            }]
        },
        options: {
            ...baseOptions,
            plugins: {
                ...baseOptions.plugins,
                legend: {
                    ...baseOptions.plugins.legend,
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });

    // Store chart references for theme updates
    window.dashboardCharts = [violationsPerDayChart, reportStatusChart, vehicleTypesChart];
    
    // Listen for real-time report updates
    if (window.Echo) {
        window.Echo.channel('reports')
            .listen('.report.created', (event) => {
                console.log('New report received:', event.report);
                
                // Add the new report to the local reports array
                if (window.reports) {
                    window.reports.unshift(event.report);
                }
                
                // Update dashboard statistics
                updateDashboardStats();
                
                // Update recent activity
                updateRecentActivity(event.report);
                
                // Show notification
                showReportNotification(event.report);
            });
    }
});

// Function to update dashboard statistics
function updateDashboardStats() {
    // Update pending reports count
    const pendingCount = window.reports ? window.reports.filter(r => r.status === 'pending').length : 0;
    const pendingElement = document.querySelector('[data-stat="pending_reports"]');
    if (pendingElement) {
        pendingElement.textContent = pendingCount.toLocaleString();
    }
    
    // Update total reports count
    const totalReports = window.reports ? window.reports.length : 0;
    const totalElement = document.querySelector('[data-stat="total_reports"]');
    if (totalElement) {
        totalElement.textContent = totalReports.toLocaleString();
    }
}

// Function to update recent activity
function updateRecentActivity(report) {
    const activityContainer = document.querySelector('.space-y-4');
    if (activityContainer && report) {
        const activityItem = document.createElement('div');
        activityItem.className = 'flex items-center space-x-4';
        activityItem.innerHTML = `
            <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
            <div class="flex-1">
                <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">New violation report submitted: ${report.violation_type ? report.violation_type.name : 'Unknown'}</p>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Just now</p>
            </div>
        `;
        
        // Insert at the top
        activityContainer.insertBefore(activityItem, activityContainer.firstChild);
        
        // Remove excess items (keep only 10)
        const items = activityContainer.querySelectorAll('.flex.items-center.space-x-4');
        if (items.length > 10) {
            items[items.length - 1].remove();
        }
    }
}

// Function to show report notification
function showReportNotification(report) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-white dark:bg-[#1a1a1a] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg shadow-lg p-4 z-50 max-w-sm';
    notification.innerHTML = `
        <div class="flex items-start space-x-3">
            <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center flex-shrink-0">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-yellow-600 dark:text-yellow-400" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">New Report Submitted</p>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">
                    ${report.violation_type ? report.violation_type.name : 'Unknown violation'} at ${report.location || 'Unknown location'}
                </p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endsection