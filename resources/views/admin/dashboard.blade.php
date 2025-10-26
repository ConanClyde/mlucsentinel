@extends('layouts.app')

@section('page-title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    Admin Dashboard
                </h2>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    Comprehensive overview of system statistics and management tools
                </p>
            </div>
            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                <x-heroicon-o-squares-2x2 class="w-8 h-8 text-blue-600 dark:text-blue-400" />
            </div>
        </div>
    </div>

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
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">1,234</p>
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
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">567</p>
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
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">89</p>
                </div>
            </div>
        </div>

        <!-- Generated Stickers Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-qr-code class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Generated Stickers</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">2,345</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Registration Chart -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">User Registrations</h3>
            <div class="h-64">
                <canvas id="userRegistrationsChart"></canvas>
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

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.users') }}" class="btn bg-blue-600 hover:bg-blue-700 text-white border-blue-600">Manage Users</a>
            <a href="{{ route('admin.vehicles') }}" class="btn bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-600">View Vehicles</a>
            <a href="{{ route('admin.reports') }}" class="btn bg-yellow-100 dark:bg-yellow-900 hover:bg-yellow-200 dark:hover:bg-yellow-800 text-yellow-800 dark:text-yellow-200 border-yellow-300 dark:border-yellow-700">Check Reports</a>
            <a href="{{ route('admin.stickers') }}" class="btn bg-green-600 hover:bg-green-700 text-white border-green-600">Generate Stickers</a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Recent Activity</h3>
        <div class="space-y-4">
            <div class="flex items-center space-x-4">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <div class="flex-1">
                    <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">New user registration: John Doe</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">2 minutes ago</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                <div class="flex-1">
                    <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">New violation report submitted</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">15 minutes ago</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                <div class="flex-1">
                    <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle sticker generated</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">1 hour ago</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = getLaravelThemeColors();
    const baseOptions = getLaravelChartOptions();

    // User Registrations Chart (Line Chart)
    const userRegistrationsCtx = document.getElementById('userRegistrationsChart').getContext('2d');
    const userRegistrationsChart = new Chart(userRegistrationsCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'New Users',
                data: [12, 19, 3, 5, 2, 3],
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20', // 20% opacity
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.primary,
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

    // Vehicle Types Chart (Doughnut Chart)
    const vehicleTypesCtx = document.getElementById('vehicleTypesChart').getContext('2d');
    const vehicleTypesChart = new Chart(vehicleTypesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Cars', 'Motorcycles', 'Trucks', 'Buses'],
            datasets: [{
                data: [45, 30, 15, 10],
                backgroundColor: [
                    colors.primary,
                    colors.success,
                    colors.warning,
                    colors.info
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            ...baseOptions,
            plugins: {
                ...baseOptions.plugins,
                legend: {
                    ...baseOptions.plugins.legend,
                    position: 'bottom'
                }
            },
            cutout: '60%'
        }
    });

    // Store chart references for theme updates
    window.dashboardCharts = [userRegistrationsChart, vehicleTypesChart];
});
</script>
@endsection