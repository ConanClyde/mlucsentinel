@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-4 md:space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Dashboard & Analytics</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Comprehensive insights and system overview</p>
        </div>
        
        <div>
            <a href="{{ route('admin.dashboard.export') }}" class="btn btn-csv">CSV</a>
        </div>
    </div>

    <!-- Dashboard Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <!-- Total Users Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <div class="flex items-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-users class="w-5 h-5 md:w-6 md:h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-3 md:ml-4 min-w-0">
                    <p class="text-xs md:text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] truncate">Total Users</p>
                    <p class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]" data-stat="total_users">{{ number_format($stats['total_users']) }}</p>
                </div>
            </div>
        </div>

        <!-- Active Vehicles Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <div class="flex items-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-truck class="w-5 h-5 md:w-6 md:h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-3 md:ml-4 min-w-0">
                    <p class="text-xs md:text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] truncate">Active Vehicles</p>
                    <p class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]" data-stat="total_vehicles">{{ number_format($stats['total_vehicles']) }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Reports Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <div class="flex items-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 md:w-6 md:h-6 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div class="ml-3 md:ml-4 min-w-0">
                    <p class="text-xs md:text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] truncate">Pending Reports</p>
                    <p class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]" data-stat="pending_reports">{{ number_format($stats['pending_reports']) }}</p>
                </div>
            </div>
        </div>

        <!-- Total Reports Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <div class="flex items-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-document-text class="w-5 h-5 md:w-6 md:h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-3 md:ml-4 min-w-0">
                    <p class="text-xs md:text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] truncate">Total Reports</p>
                    <p class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]" data-stat="total_reports">{{ number_format($stats['total_reports']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row (Revenue, Payments, etc.) - Only for Global Admin & Marketing Admin -->
    @if(Auth::user()->user_type === App\Enums\UserType::GlobalAdministrator || 
        (Auth::user()->user_type === App\Enums\UserType::Administrator && Auth::user()->isMarketingAdmin()))
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <!-- Total Revenue Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <div class="flex items-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-emerald-100 dark:bg-emerald-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-currency-dollar class="w-5 h-5 md:w-6 md:h-6 text-emerald-600 dark:text-emerald-400" />
                </div>
                <div class="ml-3 md:ml-4 min-w-0">
                    <p class="text-xs md:text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] truncate">Total Revenue</p>
                    <p class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]" data-stat="total_revenue">₱{{ number_format($stats['total_revenue'], 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Total Payments Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <div class="flex items-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-credit-card class="w-5 h-5 md:w-6 md:h-6 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div class="ml-3 md:ml-4 min-w-0">
                    <p class="text-xs md:text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] truncate">Total Payments</p>
                    <p class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]" data-stat="total_payments">{{ number_format($stats['total_payments']) }}</p>
                </div>
            </div>
        </div>

        <!-- Paid Payments Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <div class="flex items-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-teal-100 dark:bg-teal-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-check-circle class="w-5 h-5 md:w-6 md:h-6 text-teal-600 dark:text-teal-400" />
                </div>
                <div class="ml-3 md:ml-4 min-w-0">
                    <p class="text-xs md:text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] truncate">Paid Payments</p>
                    <p class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]" data-stat="paid_payments">{{ number_format($stats['paid_payments']) }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Payments Card -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <div class="flex items-center">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-clock class="w-5 h-5 md:w-6 md:h-6 text-orange-600 dark:text-orange-400" />
                </div>
                <div class="ml-3 md:ml-4 min-w-0">
                    <p class="text-xs md:text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] truncate">Pending Payments</p>
                    <p class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]" data-stat="pending_payments">{{ number_format($stats['pending_payments']) }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Patrol Statistics (Global Admin & Security Admin only) -->
    @php
        $canViewPatrolStats = false;
        if (Auth::user()->user_type === App\Enums\UserType::GlobalAdministrator) {
            $canViewPatrolStats = true;
        } elseif (Auth::user()->user_type === App\Enums\UserType::Administrator && Auth::user()->administrator) {
            $adminRole = Auth::user()->administrator->adminRole->name ?? '';
            $canViewPatrolStats = ($adminRole === 'Security');
        }
    @endphp

    @if($canViewPatrolStats)
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <h2 class="text-lg md:text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Patrol Statistics (Last 24 Hours)</h2>
            <a href="{{ route('admin.patrol-history') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                View All
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <!-- Total Check-ins -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3 md:ml-4 min-w-0">
                        <p class="text-xs md:text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] truncate">Total Check-ins</p>
                        <p class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]" data-stat="patrol_total_checkins">{{ number_format($patrolStats['total_checkins']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Guards -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3 md:ml-4 min-w-0">
                        <p class="text-xs md:text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] truncate">Active Guards</p>
                        <p class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]" data-stat="patrol_unique_guards">{{ number_format($patrolStats['unique_guards']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Locations Covered -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3 md:ml-4 min-w-0">
                        <p class="text-xs md:text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] truncate">Locations Covered</p>
                        <p class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]" data-stat="patrol_unique_locations">{{ number_format($patrolStats['unique_locations']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Most Visited Location -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <div class="ml-3 md:ml-4 min-w-0">
                        <p class="text-xs md:text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] truncate">Most Visited</p>
                        <p class="text-base md:text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] truncate" 
                           data-stat="patrol_most_visited_code"
                           title="{{ $patrolStats['most_visited_location']?->mapLocation?->name ?? '' }}">
                            {{ $patrolStats['most_visited_location']?->mapLocation?->short_code ?? 'N/A' }}
                        </p>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]" data-stat="patrol_most_visited_count">{{ number_format($patrolStats['most_visited_location']?->visit_count ?? 0) }} visits</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Patrol Coverage Widget (Global Admin & Security Admin only) -->
    @if($canViewPatrolStats)
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <h2 class="text-lg md:text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Patrol Coverage (Last 7 Days)</h2>
            <a href="{{ route('admin.patrol-history') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                View Details →
            </a>
        </div>
        
        <!-- Coverage Progress Circle -->
        <div class="flex items-center justify-center mb-6">
            <div class="relative w-40 h-40">
                <svg class="w-40 h-40 transform -rotate-90">
                    <circle cx="80" cy="80" r="70" stroke="currentColor" stroke-width="12" fill="transparent" class="text-gray-200 dark:text-gray-700"/>
                    <circle cx="80" cy="80" r="70" stroke="currentColor" stroke-width="12" fill="transparent" 
                            class="{{ $patrolCoverageData['coverage_percentage'] >= 80 ? 'text-green-500' : ($patrolCoverageData['coverage_percentage'] >= 50 ? 'text-yellow-500' : 'text-red-500') }}"
                            stroke-dasharray="{{ 2 * pi() * 70 }}" 
                            stroke-dashoffset="{{ 2 * pi() * 70 * (1 - $patrolCoverageData['coverage_percentage'] / 100) }}"
                            stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $patrolCoverageData['coverage_percentage'] }}%</span>
                    <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Covered</span>
                </div>
            </div>
        </div>

        <!-- Coverage Stats -->
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $patrolCoverageData['covered_locations'] }}/{{ $patrolCoverageData['total_locations'] }}</p>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Locations</p>
            </div>
            <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $patrolCoverageData['total_patrols'] }}</p>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Check-ins</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Violations Per Day Chart -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Violations Per Day (Last 30 Days)</h3>
            <div class="h-48 md:h-64 relative">
                <canvas id="violationsPerDayChart"></canvas>
                <div class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800 animate-pulse" data-skeleton="violations">
                    <div class="w-3/4 h-6 bg-gray-300 dark:bg-gray-700 rounded"></div>
                </div>
            </div>
            <div class="hidden mt-2 text-sm text-red-600 dark:text-red-400" data-error="violations"></div>
        </div>

        <!-- Report Status Distribution -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Report Status</h3>
            <div class="h-48 md:h-64 relative">
                <canvas id="reportStatusChart"></canvas>
                <div class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800 animate-pulse" data-skeleton="reportStatus">
                    <div class="w-3/4 h-6 bg-gray-300 dark:bg-gray-700 rounded"></div>
                </div>
            </div>
            <div class="hidden mt-2 text-sm text-red-600 dark:text-red-400" data-error="reportStatus"></div>
        </div>

        <!-- Vehicle Types Chart -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Vehicle Types</h3>
            <div class="h-48 md:h-64 relative">
                <canvas id="vehicleTypesChart"></canvas>
                <div class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800 animate-pulse" data-skeleton="vehicleTypes">
                    <div class="w-3/4 h-6 bg-gray-300 dark:bg-gray-700 rounded"></div>
                </div>
            </div>
            <div class="hidden mt-2 text-sm text-red-600 dark:text-red-400" data-error="vehicleTypes"></div>
        </div>
    </div>

    <!-- Most Common Violations & Active Users Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Most Common Violations Widget (All Admins) -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Most Common Violations</h2>
            <div class="space-y-3">
                @php
                    $sortedViolations = collect($violationsByType)->sortDesc()->take(8);
                    $maxCount = $sortedViolations->max() ?: 1;
                @endphp
                @forelse($sortedViolations as $type => $count)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $type }}</span>
                            <span class="text-sm font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $count }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full transition-all duration-500" 
                                 style="width: {{ ($count / $maxCount) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-[#706f6c] dark:text-[#A1A09A] py-8">No violations recorded yet</p>
                @endforelse
            </div>
        </div>

        <!-- Active Users by Type Widget (All Admins) -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Active Users by Type</h2>
            <div class="space-y-3">
                @php
                    $userTypeColors = [
                        'global_administrator' => ['bg' => 'bg-purple-500', 'label' => 'Global Admin'],
                        'administrator' => ['bg' => 'bg-blue-500', 'label' => 'Admin'],
                        'student' => ['bg' => 'bg-green-500', 'label' => 'Student'],
                        'staff' => ['bg' => 'bg-indigo-500', 'label' => 'Staff'],
                        'security' => ['bg' => 'bg-red-500', 'label' => 'Security'],
                        'reporter' => ['bg' => 'bg-orange-500', 'label' => 'Reporter'],
                        'stakeholder' => ['bg' => 'bg-gray-500', 'label' => 'Stakeholder'],
                    ];
                    $totalUsers = array_sum($userTypes);
                @endphp
                @foreach($userTypes as $type => $count)
                    @php
                        $typeConfig = $userTypeColors[$type] ?? ['bg' => 'bg-gray-500', 'label' => $type];
                        $percentage = $totalUsers > 0 ? round(($count / $totalUsers) * 100, 1) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full {{ $typeConfig['bg'] }}"></span>
                                <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $typeConfig['label'] }}</span>
                            </div>
                            <span class="text-sm font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $count }} ({{ $percentage }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="{{ $typeConfig['bg'] }} h-2 rounded-full transition-all duration-500" 
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Heatmap and Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Violation Heatmap (2/3 width) - Visible to all administrators -->
        <div class="lg:col-span-2 bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Campus Violation Heatmap</h3>
                    <p class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">Color intensity shows where violations occur most frequently</p>
                </div>
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="flex items-center gap-2 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                        <span class="flex items-center gap-1">
                            <span class="w-12 h-3 rounded" style="background: linear-gradient(to right, #10b981, #fbbf24, #f97316, #ef4444);"></span>
                            <span class="hidden sm:inline">Low → High</span>
                        </span>
                    </div>
                    <div class="flex space-x-2">
                        <button id="heatmap-zoom-in" class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors touch-manipulation">
                            <span class="text-lg font-bold leading-none">+</span>
                        </button>
                        <button id="heatmap-zoom-out" class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors touch-manipulation">
                            <span class="text-lg font-bold leading-none">−</span>
                        </button>
                        <button id="heatmap-reset" class="flex items-center justify-center w-8 h-8 border border-gray-300 dark:border-gray-600 text-[#1b1b18] dark:text-[#EDEDEC] rounded hover:border-gray-400 transition-colors touch-manipulation">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="relative overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-900 w-full" id="heatmap-container">
                
                <!-- Loading Skeleton -->
                <div id="heatmap-skeleton" class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-900 z-50">
                    <div class="text-center">
                        <svg class="animate-spin h-12 w-12 mx-auto text-blue-600 dark:text-blue-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] font-medium">Loading heatmap...</p>
                    </div>
                </div>
                
                <div id="heatmap-wrapper" class="relative w-full origin-center transition-transform duration-300 ease-out">
                    <!-- Campus Map Image -->
                    <img id="heatmap-map" src="{{ asset('images/campus-map.svg') }}" alt="Campus Map" 
                         class="block w-full h-auto select-none" 
                         draggable="false"
                         onload="initializeHeatmapDimensions()">

                    <!-- Heatmap Overlay -->
                    <svg class="absolute inset-0 w-full h-full" id="heatmap-svg" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <defs>
                            <filter id="heatmap-glow">
                                <feGaussianBlur stdDeviation="0.8" result="coloredBlur"/>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Analytics Sidebar (1/3 width) -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Top Reporters -->
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
                <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Top Reporters</h3>
            <div class="space-y-3 max-h-80 overflow-y-auto">
                @forelse($topReporters as $index => $reporter)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $reporter->first_name }} {{ $reporter->last_name }}</p>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $reporter->user_type->label() }}</p>
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
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
                <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Top Violators</h3>
            <div class="space-y-3 max-h-80 overflow-y-auto">
                @forelse($topViolators as $index => $violator)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                <span class="text-sm font-semibold text-red-600 dark:text-red-400">{{ $index + 1 }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $violator->first_name }} {{ $violator->last_name }}</p>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $violator->user_type->label() }}</p>
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
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
                <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Top Violation Locations</h3>
            <div class="space-y-3 max-h-80 overflow-y-auto">
                @forelse($topLocations as $index => $location)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                <span class="text-sm font-semibold text-yellow-600 dark:text-yellow-400">{{ $index + 1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] truncate" title="{{ $location->location }}">{{ $location->location }}</p>
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

    // Campus Violation Heatmap - Individual Dots
    let heatmapScale = 1;
    let heatmapPanX = 0;
    let heatmapPanY = 0;
    let heatmapAspectRatio = 1;
    let activeTooltips = []; // Track all active tooltips for cleanup (like campus-map)
    
    const heatmapReports = @json($reportsForHeatmap ?? []);
    const mapLocations = @json($mapLocations ?? []);
    
    function hideHeatmapSkeleton() {
        const skeleton = document.getElementById('heatmap-skeleton');
        if (skeleton) {
            skeleton.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            setTimeout(() => skeleton.remove(), 300);
        }
    }

    function initializeHeatmapDimensions() {
        const img = document.getElementById('heatmap-map');
        const container = document.getElementById('heatmap-container');
        
        // Set container aspect ratio based on image dimensions for responsive sizing
        if (img && img.complete && img.naturalHeight > 0) {
            heatmapAspectRatio = img.naturalHeight / img.naturalWidth;
            
            // Use CSS aspect-ratio for responsive sizing
            container.style.aspectRatio = `${img.naturalWidth} / ${img.naturalHeight}`;
            
            console.log('🖼️ Heatmap dimensions:', img.naturalWidth, 'x', img.naturalHeight);
            console.log('📐 Heatmap aspect ratio:', heatmapAspectRatio);
            console.log('✅ Heatmap initialized with', heatmapReports.length, 'violation dots');
            
            renderHeatmap(); // This will render both locations and violation dots
        }
    }
    
    // Expose function globally for inline onload handler
    window.initializeHeatmapDimensions = initializeHeatmapDimensions;
    
    function handleHeatmapError(img) {
        img.onerror = null;
        img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjYwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iODAwIiBoZWlnaHQ9IjYwMCIgZmlsbD0iI2Y3ZjlmNyIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjQiIGZpbGw9IiM5Yzk5YzkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5VcGxvYWQgQ2FtcHVzIE1hcDwvdGV4dD48dGV4dCB4PSI1MCUiIHk9IjU1JSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjYWJhYmFiIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+UGxhY2UgY2FtcHVzIG1hcCBpbWFnZSBhdCBwdWJsaWMvaW1hZ2VzL2NhbXB1cy1tYXAuc3ZnPC90ZXh0Pjwvc3ZnPg==';
        img.alt = 'Upload Campus Map';
    }
    
    // Calculate local density for each point
    function calculateDensity(x, y, radius = 5) {
        let count = 0;
        heatmapReports.forEach(report => {
            const distance = Math.sqrt(Math.pow(x - report.x, 2) + Math.pow(y - report.y, 2));
            if (distance <= radius) {
                count++;
            }
        });
        return count;
    }
    
    // Get color based on density
    function getDensityColor(density) {
        if (density === 1) {
            return '#10b981'; // Green - single violation
        } else if (density <= 3) {
            return '#fbbf24'; // Yellow - low density
        } else if (density <= 6) {
            return '#f97316'; // Orange - medium density
        } else {
            return '#ef4444'; // Red - high density
        }
    }
    
    function renderHeatmap() {
        const svg = document.getElementById('heatmap-svg');
        
        // Clear existing (except defs)
        while (svg.children.length > 1) {
            svg.removeChild(svg.lastChild);
        }
        
        // Render locations first (behind violation dots)
        renderHeatmapLocations();
        
        if (heatmapReports.length === 0) {
            return;
        }
        
        // Draw each violation as a dot
        heatmapReports.forEach(report => {
            const density = calculateDensity(report.x, report.y);
            const color = getDensityColor(density);
            const fixedRadius = 0.4 / heatmapScale; // Scale with zoom
            
            // Create circle group
            const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            g.setAttribute('class', 'heatmap-dot');
            g.style.cursor = 'pointer';
            g.style.pointerEvents = 'auto';
            
            // Draw circle
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', report.x);
            circle.setAttribute('cy', report.y);
            circle.setAttribute('r', fixedRadius);
            circle.setAttribute('fill', color);
            circle.setAttribute('fill-opacity', '0.7');
            circle.setAttribute('stroke', color);
            circle.setAttribute('stroke-width', 0.1 / heatmapScale);
            circle.setAttribute('filter', 'url(#heatmap-glow)');
            circle.setAttribute('class', 'transition-all duration-200');
            circle.style.pointerEvents = 'auto';
            
            // Tooltip
            let tooltip = null;
            g.addEventListener('mouseenter', function(e) {
                circle.setAttribute('fill-opacity', '1');
                circle.setAttribute('r', fixedRadius * 1.5);
                
                tooltip = document.createElement('div');
                tooltip.className = 'absolute bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] px-3 py-2 rounded-lg text-sm font-medium shadow-lg z-50 pointer-events-none';
                tooltip.style.left = e.clientX + 10 + 'px';
                tooltip.style.top = e.clientY + 10 + 'px';
                tooltip.innerHTML = `
                    <div class="font-semibold">${report.location}</div>
                    <div class="text-xs mt-1">${report.violation_type}</div>
                    <div class="text-xs text-gray-400 dark:text-gray-600">${report.reported_at}</div>
                    <div class="text-xs mt-1 text-gray-300 dark:text-gray-700">Nearby: ${density} violation${density !== 1 ? 's' : ''}</div>
                `;
                document.body.appendChild(tooltip);
            });
            
            g.addEventListener('mouseleave', function() {
                circle.setAttribute('fill-opacity', '0.7');
                circle.setAttribute('r', fixedRadius);
                if (tooltip) {
                    tooltip.remove();
                    tooltip = null;
                }
            });
            
            g.addEventListener('mousemove', function(e) {
                if (tooltip) {
                    tooltip.style.left = e.clientX + 10 + 'px';
                    tooltip.style.top = e.clientY + 10 + 'px';
                }
            });
            
            g.appendChild(circle);
            svg.appendChild(g);
        });
    }
    
    // Render location polygons on heatmap (view-only)
    function renderHeatmapLocations() {
        const svg = document.getElementById('heatmap-svg');
        
        mapLocations.forEach(location => {
            if (!location.vertices || location.vertices.length < 3) return;
            
            // Check if it's a parking area (hide label like in campus-map)
            const isParking = location.type && location.type.name && location.type.name.toLowerCase().includes('parking');
            
            // Create polygon group
            const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            g.setAttribute('class', 'heatmap-location');
            g.style.cursor = 'pointer';
            g.style.pointerEvents = 'auto';
            
            // Draw polygon (initially transparent, becomes visible on hover)
            const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
            const pointsString = location.vertices.map(v => `${v.x},${v.y}`).join(' ');
            polygon.setAttribute('points', pointsString);
            polygon.setAttribute('fill', 'transparent');
            polygon.setAttribute('stroke', 'transparent');
            polygon.setAttribute('stroke-width', '5'); // Wider invisible stroke to catch hover
            polygon.setAttribute('class', 'transition-all duration-200');
            polygon.style.pointerEvents = 'fill'; // Make sure it catches pointer events
            
            // Create label (always visible, except for parking)
            let label = null;
            if (location.center_x && location.center_y && !isParking) {
                label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                label.setAttribute('x', location.center_x);
                label.setAttribute('y', location.center_y);
                label.setAttribute('text-anchor', 'middle');
                label.setAttribute('dominant-baseline', 'central');
                label.setAttribute('fill', '#000000'); // Black
                label.setAttribute('stroke', '#ffffff'); // White outline
                label.setAttribute('stroke-width', 0.15);
                label.setAttribute('paint-order', 'stroke fill');
                label.setAttribute('font-weight', '700');
                label.setAttribute('font-size', 1);
                label.setAttribute('font-family', 'Satoshi, ui-sans-serif, system-ui, sans-serif');
                label.setAttribute('transform', `scale(1, ${1 / heatmapAspectRatio})`);
                label.setAttribute('transform-origin', `${location.center_x} ${location.center_y}`);
                label.setAttribute('opacity', '1'); // Always visible
                label.style.pointerEvents = 'none';
                label.textContent = location.short_code || location.name.substring(0, 3).toUpperCase();
            }
            
            // Tooltip for location info
            let tooltip = null;
            
            g.addEventListener('mouseenter', function(e) {
                // Show polygon with location color (same as campus-map: 40 hex = 25% opacity)
                polygon.setAttribute('fill', location.color + '40'); // 25% opacity
                polygon.setAttribute('stroke', location.color);
                polygon.setAttribute('stroke-width', 0.3);
                polygon.setAttribute('filter', 'url(#heatmap-glow)');
                
                // Clean up any existing tooltips first (like campus-map)
                activeTooltips.forEach(t => {
                    if (t && t.parentNode) t.remove();
                });
                activeTooltips = [];
                
                // Show tooltip with full location name (like campus-map)
                tooltip = document.createElement('div');
                tooltip.className = 'bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] px-3 py-2 rounded-lg text-sm font-medium shadow-lg pointer-events-none';
                tooltip.style.position = 'fixed'; // Use fixed positioning like campus-map
                tooltip.style.left = e.clientX + 10 + 'px';
                tooltip.style.top = e.clientY + 10 + 'px';
                tooltip.style.zIndex = '9999';
                tooltip.textContent = location.name;
                document.body.appendChild(tooltip);
                activeTooltips.push(tooltip);
            });
            
            g.addEventListener('mouseleave', function() {
                // Hide polygon back to transparent
                polygon.setAttribute('fill', 'transparent');
                polygon.setAttribute('stroke', 'transparent');
                polygon.removeAttribute('filter');
                
                // Remove tooltip
                if (tooltip) {
                    tooltip.remove();
                    activeTooltips = activeTooltips.filter(t => t !== tooltip);
                    tooltip = null;
                }
            });
            
            g.addEventListener('mousemove', function(e) {
                if (tooltip) {
                    tooltip.style.left = e.clientX + 10 + 'px';
                    tooltip.style.top = e.clientY + 10 + 'px';
                }
            });
            
            g.appendChild(polygon);
            if (label) {
                g.appendChild(label);
            }
            svg.appendChild(g);
        });
    }
    
    // Initialize heatmap
    const heatmapImg = document.getElementById('heatmap-map');
    if (heatmapImg.complete) {
        hideHeatmapSkeleton();
        initializeHeatmapDimensions();
    } else {
        heatmapImg.addEventListener('load', function() {
            hideHeatmapSkeleton();
            initializeHeatmapDimensions();
        });
    }
    // Add error handler
    heatmapImg.addEventListener('error', function() {
        hideHeatmapSkeleton();
        handleHeatmapError(this);
    });
    
    // Heatmap zoom and pan controls
    document.getElementById('heatmap-zoom-in').addEventListener('click', () => {
        heatmapScale = Math.min(3, heatmapScale + 0.2);
        applyHeatmapTransform();
    });
    
    document.getElementById('heatmap-zoom-out').addEventListener('click', () => {
        heatmapScale = Math.max(1, heatmapScale - 0.2);
        applyHeatmapTransform();
    });
    
    document.getElementById('heatmap-reset').addEventListener('click', () => {
        heatmapScale = 1;
        heatmapPanX = 0;
        heatmapPanY = 0;
        applyHeatmapTransform();
    });
    
    // Pan functionality
    const heatmapContainer = document.getElementById('heatmap-container');
    const heatmapWrapper = document.getElementById('heatmap-wrapper');
    let isDragging = false;
    let dragStartX, dragStartY;
    let startPanX, startPanY;
    
    heatmapContainer.addEventListener('mousedown', (e) => {
        isDragging = true;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        startPanX = heatmapPanX;
        startPanY = heatmapPanY;
        heatmapContainer.style.cursor = 'grabbing';
        heatmapWrapper.style.transition = 'none';
    });
    
    heatmapContainer.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        
        const deltaX = e.clientX - dragStartX;
        const deltaY = e.clientY - dragStartY;
        
        heatmapPanX = startPanX + deltaX;
        heatmapPanY = startPanY + deltaY;
        
        applyHeatmapTransform();
    });
    
    heatmapContainer.addEventListener('mouseup', () => {
        if (isDragging) {
            isDragging = false;
            heatmapContainer.style.cursor = 'grab';
            heatmapWrapper.style.transition = 'transform 0.3s ease-out';
        }
    });
    
    heatmapContainer.addEventListener('mouseleave', () => {
        if (isDragging) {
            isDragging = false;
            heatmapContainer.style.cursor = 'default';
            heatmapWrapper.style.transition = 'transform 0.3s ease-out';
        }
    });
    
    // Mouse wheel zoom
    heatmapContainer.addEventListener('wheel', (e) => {
        e.preventDefault();
        
        const delta = e.deltaY > 0 ? -0.1 : 0.1;
        const oldScale = heatmapScale;
        const newScale = Math.min(Math.max(1, heatmapScale + delta), 3);
        
        if (newScale !== heatmapScale) {
            const rect = heatmapContainer.getBoundingClientRect();
            const mouseX = e.clientX - rect.left - rect.width / 2;
            const mouseY = e.clientY - rect.top - rect.height / 2;
            
            const scaleRatio = newScale / oldScale;
            heatmapPanX = heatmapPanX - (mouseX - heatmapPanX) * (scaleRatio - 1);
            heatmapPanY = heatmapPanY - (mouseY - heatmapPanY) * (scaleRatio - 1);
            heatmapScale = newScale;
            
            applyHeatmapTransform();
        }
    }, { passive: false });
    
    // Touch events for mobile dragging and pinch-to-zoom
    let heatmapTouchStartDistance = 0;
    let heatmapTouchStartScale = 1;
    let heatmapTouchStartCenterX = 0;
    let heatmapTouchStartCenterY = 0;
    let heatmapTouchStartPanX = 0;
    let heatmapTouchStartPanY = 0;
    let isHeatmapPinching = false;
    let isHeatmapTouchDragging = false;
    
    heatmapContainer.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) {
            // Single touch - dragging
            isHeatmapTouchDragging = true;
            const touch = e.touches[0];
            dragStartX = touch.clientX;
            dragStartY = touch.clientY;
            startPanX = heatmapPanX;
            startPanY = heatmapPanY;
            heatmapContainer.style.cursor = 'grabbing';
            heatmapWrapper.style.transition = 'none';
        } else if (e.touches.length === 2) {
            // Two touches - pinch to zoom
            e.preventDefault();
            isHeatmapPinching = true;
            isHeatmapTouchDragging = false;
            
            const touch1 = e.touches[0];
            const touch2 = e.touches[1];
            
            // Calculate distance between two touches
            const dx = touch2.clientX - touch1.clientX;
            const dy = touch2.clientY - touch1.clientY;
            heatmapTouchStartDistance = Math.sqrt(dx * dx + dy * dy);
            
            // Store initial scale and pan
            heatmapTouchStartScale = heatmapScale;
            heatmapTouchStartPanX = heatmapPanX;
            heatmapTouchStartPanY = heatmapPanY;
            
            // Calculate center point between two touches
            const rect = heatmapContainer.getBoundingClientRect();
            heatmapTouchStartCenterX = ((touch1.clientX + touch2.clientX) / 2) - rect.left - rect.width / 2;
            heatmapTouchStartCenterY = ((touch1.clientY + touch2.clientY) / 2) - rect.top - rect.height / 2;
        }
    }, { passive: false });
    
    heatmapContainer.addEventListener('touchmove', (e) => {
        if (e.touches.length === 1 && isHeatmapTouchDragging && !isHeatmapPinching) {
            // Single touch dragging
            e.preventDefault();
            const touch = e.touches[0];
            const deltaX = touch.clientX - dragStartX;
            const deltaY = touch.clientY - dragStartY;
            
            heatmapPanX = startPanX + deltaX;
            heatmapPanY = startPanY + deltaY;
            
            applyHeatmapTransform();
        } else if (e.touches.length === 2 && isHeatmapPinching) {
            // Pinch to zoom
            e.preventDefault();
            
            const touch1 = e.touches[0];
            const touch2 = e.touches[1];
            
            // Calculate current distance
            const dx = touch2.clientX - touch1.clientX;
            const dy = touch2.clientY - touch1.clientY;
            const currentDistance = Math.sqrt(dx * dx + dy * dy);
            
            // Calculate scale change
            const scaleChange = currentDistance / heatmapTouchStartDistance;
            const newScale = Math.min(Math.max(1, heatmapTouchStartScale * scaleChange), 3);
            
            if (newScale !== heatmapScale) {
                // Calculate the zoom point offset from center
                const scaleRatio = newScale / heatmapTouchStartScale;
                
                // Adjust pan to keep center point stationary
                heatmapPanX = heatmapTouchStartPanX - (heatmapTouchStartCenterX - heatmapTouchStartPanX) * (scaleRatio - 1);
                heatmapPanY = heatmapTouchStartPanY - (heatmapTouchStartCenterY - heatmapTouchStartPanY) * (scaleRatio - 1);
                
                heatmapScale = newScale;
                applyHeatmapTransform();
            }
        }
    }, { passive: false });
    
    heatmapContainer.addEventListener('touchend', (e) => {
        if (e.touches.length === 0) {
            // All touches ended
            isHeatmapTouchDragging = false;
            isHeatmapPinching = false;
            heatmapContainer.style.cursor = 'grab';
            heatmapWrapper.style.transition = 'transform 0.3s ease-out';
        } else if (e.touches.length === 1 && isHeatmapPinching) {
            // One touch ended during pinch - switch to dragging
            isHeatmapPinching = false;
            isHeatmapTouchDragging = true;
            const touch = e.touches[0];
            dragStartX = touch.clientX;
            dragStartY = touch.clientY;
            startPanX = heatmapPanX;
            startPanY = heatmapPanY;
        }
    });
    
    function applyHeatmapTransform() {
        const container = document.getElementById('heatmap-container');
        const wrapper = document.getElementById('heatmap-wrapper');
        
        const containerWidth = container.offsetWidth;
        const containerHeight = container.offsetHeight;
        const scaledWidth = containerWidth * heatmapScale;
        const scaledHeight = containerHeight * heatmapScale;
        const extraWidth = (scaledWidth - containerWidth) / 2;
        const extraHeight = (scaledHeight - containerHeight) / 2;
        
        if (heatmapScale > 1) {
            heatmapPanX = Math.max(-extraWidth, Math.min(extraWidth, heatmapPanX));
            heatmapPanY = Math.max(-extraHeight, Math.min(extraHeight, heatmapPanY));
        } else {
            heatmapPanX = 0;
            heatmapPanY = 0;
        }
        
        wrapper.style.transform = `translate(${heatmapPanX}px, ${heatmapPanY}px) scale(${heatmapScale})`;
        renderHeatmap();
    }
    
    // Handle window resize to maintain aspect ratio
    window.addEventListener('resize', function() {
        // Recalculate aspect ratio on resize to maintain responsiveness
        const img = document.getElementById('heatmap-map');
        const container = document.getElementById('heatmap-container');
        
        if (img && img.complete && img.naturalHeight > 0 && container) {
            container.style.aspectRatio = `${img.naturalWidth} / ${img.naturalHeight}`;
        }
    });

    // Store chart references for theme updates
    window.violationsPerDayChart = violationsPerDayChart;
    window.reportStatusChart = reportStatusChart;
    window.vehicleTypesChart = vehicleTypesChart;
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
                
                // Update dashboard statistics (debounced)
                scheduleStatsUpdate();
                
                // Update recent activity
                updateRecentActivity(event.report);
                
                // Show notification
                showReportNotification(event.report);
            });
    }

    let updateStatsTimer = null;
    function scheduleStatsUpdate() {
        if (updateStatsTimer) clearTimeout(updateStatsTimer);
        updateStatsTimer = setTimeout(updateDashboardStats, 500);
    }

    async function fetchJSON(url) {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    }

    function setStat(name, value, currency = false) {
        const el = document.querySelector(`[data-stat="${name}"]`);
        if (!el) return;
        if (currency) {
            const num = typeof value === 'number' ? value : parseFloat(value || 0);
            el.textContent = '₱' + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else {
            const num = typeof value === 'number' ? value : parseFloat(value || 0);
            el.textContent = isNaN(num) ? (value ?? '') : num.toLocaleString();
        }
    }

    function hideSkeleton(key) {
        const el = document.querySelector(`[data-skeleton="${key}"]`);
        if (el) el.classList.add('hidden');
    }

    function showError(key, message) {
        const err = document.querySelector(`[data-error="${key}"]`);
        if (err) {
            err.textContent = message || 'Failed to load.';
            err.classList.remove('hidden');
        }
        hideSkeleton(key);
    }

    fetchJSON('/api/metrics/overview').then(({ success, stats, reportsByStatus, vehicleTypes }) => {
        if (!success) return;
        if (stats) {
            setStat('total_users', stats.total_users);
            setStat('total_vehicles', stats.total_vehicles);
            setStat('pending_reports', stats.pending_reports);
            setStat('total_reports', stats.total_reports);
            setStat('total_revenue', stats.total_revenue, true);
            setStat('total_payments', stats.total_payments);
            setStat('paid_payments', stats.paid_payments);
            setStat('pending_payments', stats.pending_payments);
        }
        if (reportsByStatus && window.reportStatusChart) {
            const labels = Object.keys(reportsByStatus).map(s => s.charAt(0).toUpperCase() + s.slice(1));
            const values = Object.values(reportsByStatus);
            window.reportStatusChart.data.labels = labels.length ? labels : ['No Data'];
            window.reportStatusChart.data.datasets[0].data = values.length ? values : [1];
            window.reportStatusChart.update();
            hideSkeleton('reportStatus');
        }
        if (vehicleTypes && window.vehicleTypesChart) {
            const labels = Object.keys(vehicleTypes);
            const values = Object.values(vehicleTypes);
            window.vehicleTypesChart.data.labels = labels.length ? labels : ['No Data'];
            window.vehicleTypesChart.data.datasets[0].data = values.length ? values : [1];
            window.vehicleTypesChart.update();
            hideSkeleton('vehicleTypes');
        }
    }).catch(() => {
        showError('reportStatus', 'Failed to load report status.');
        showError('vehicleTypes', 'Failed to load vehicle types.');
    });

    fetchJSON('/api/metrics/violations-per-day').then(({ success, data }) => {
        if (!success || !window.violationsPerDayChart) return;
        const labels = (data || []).map(i => new Date(i.date).toLocaleDateString());
        const values = (data || []).map(i => i.count);
        window.violationsPerDayChart.data.labels = labels.length ? labels : ['No Data'];
        window.violationsPerDayChart.data.datasets[0].data = values.length ? values : [0];
        window.violationsPerDayChart.update();
        hideSkeleton('violations');
    }).catch(() => {
        showError('violations', 'Failed to load violations per day.');
    });

    fetchJSON('/api/metrics/patrol-24h').then(({ success, data }) => {
        if (!success) return;
        setStat('patrol_total_checkins', data?.total_checkins || 0);
        setStat('patrol_unique_guards', data?.unique_guards || 0);
        setStat('patrol_unique_locations', data?.unique_locations || 0);
        const code = data?.most_visited_location?.map_location?.short_code || 'N/A';
        const name = data?.most_visited_location?.map_location?.name || '';
        const codeEl = document.querySelector('[data-stat="patrol_most_visited_code"]');
        if (codeEl) {
            codeEl.textContent = code;
            codeEl.title = name; // Show full name on hover
        }
        const mvCount = data?.most_visited_location?.visit_count || 0;
        const countEl = document.querySelector('[data-stat="patrol_most_visited_count"]');
        if (countEl) countEl.textContent = mvCount.toLocaleString() + ' visits';
    }).catch(() => {});
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
    notification.className = 'fixed top-4 right-4 bg-white dark:bg-[#1a1a1a] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg shadow-lg p-4 z-50 max-w-sm cursor-pointer hover:shadow-xl transition-shadow';
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
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">Click to view report</p>
            </div>
            <button onclick="event.stopPropagation(); this.parentElement.parentElement.remove()" class="text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
        </div>
    `;
    
    // Add click handler to navigate to reports page
    notification.addEventListener('click', () => {
        window.location.href = '{{ route('admin.reports') }}?view=' + report.id;
    });
    
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