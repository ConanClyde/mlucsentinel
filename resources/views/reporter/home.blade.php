@extends('layouts.app')

@section('page-title', 'Reporter Home')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 sm:p-6">
        <div class="flex items-center justify-between gap-4">
            <div class="flex-1 min-w-0">
                <h2 class="text-xl sm:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-1 sm:mb-2 truncate">
                    Welcome, {{ Auth::user()->name }}!
                </h2>
                <p class="text-sm sm:text-base text-[#706f6c] dark:text-[#A1A09A]">
                    You are logged in as <span class="font-semibold capitalize">{{ str_replace('_', ' ', Auth::user()->user_type) }}</span>
                </p>
            </div>
            <div class="hidden sm:flex w-12 h-12 sm:w-16 sm:h-16 bg-green-100 dark:bg-green-900 rounded-full items-center justify-center flex-shrink-0">
                <x-heroicon-o-newspaper class="w-6 h-6 sm:w-8 sm:h-8 text-green-600 dark:text-green-400" />
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Report User</h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Report parking violations</p>
                </div>
            </div>
            <a href="{{ route('reporter.report-user') }}" class="btn bg-red-600 hover:bg-red-700 text-white border-red-600 w-full text-center">
                Create Report
            </a>
        </div>

        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-document-text class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">My Reports</h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">View your submitted reports</p>
                </div>
            </div>
            <a href="{{ route('reporter.my-reports') }}" class="btn bg-blue-600 hover:bg-blue-700 text-white border-blue-600 w-full text-center">
                View Reports
            </a>
        </div>

        @if(Auth::user()->user_type === 'security')
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-truck class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">My Vehicles</h3>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Manage your vehicles</p>
                    </div>
                </div>
                <a href="{{ route('reporter.my-vehicles') }}" class="btn bg-green-600 hover:bg-green-700 text-white border-green-600 w-full text-center">
                    Manage Vehicles
                </a>
            </div>
        @endif
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Recent Activity</h3>
        <div class="space-y-4">
            <div class="flex items-center space-x-4">
                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                <div class="flex-1">
                    <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Report submitted: Parking violation - ABC-1234</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">2 hours ago</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                <div class="flex-1">
                    <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle registered: XYZ-5678</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">1 day ago</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <div class="flex-1">
                    <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Report resolved: Blocking fire exit</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">3 days ago</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
