@extends('layouts.app')

@section('page-title', 'Admin Home')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    Welcome, {{ Auth::user()->name }}!
                </h2>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    You are logged in as <span class="font-semibold capitalize">{{ str_replace('_', ' ', Auth::user()->user_type) }}</span>
                </p>
            </div>
            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                <x-heroicon-o-shield-check class="w-8 h-8 text-blue-600 dark:text-blue-400" />
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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

        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-truck class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Registered Vehicles</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">567</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Violations</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">89</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-chart-bar class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Reports</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">45</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.users') }}" class="btn bg-blue-600 hover:bg-blue-700 text-white border-blue-600">Manage Users</a>
            <a href="{{ route('admin.vehicles') }}" class="btn bg-gray-100 hover:bg-gray-200 text-gray-800 border-gray-300">View Vehicles</a>
            <a href="{{ route('admin.reports') }}" class="btn bg-yellow-100 hover:bg-yellow-200 text-yellow-800 border-yellow-300">Check Reports</a>
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
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                <div class="flex-1">
                    <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle registered: ABC-1234</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">15 minutes ago</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                <div class="flex-1">
                    <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">Violation reported: Parking violation</p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">1 hour ago</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
