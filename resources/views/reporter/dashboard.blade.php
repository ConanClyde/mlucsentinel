@extends('layouts.app')

@section('page-title', 'Reporter Dashboard')

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
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                <x-heroicon-o-newspaper class="w-8 h-8 text-green-600 dark:text-green-400" />
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
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Report Violation</h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Report parking violations</p>
                </div>
            </div>
            <button class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                Create Report
            </button>
        </div>

        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-qr-code class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Scan QR Code</h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Scan vehicle QR codes</p>
                </div>
            </div>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                Open Scanner
            </button>
        </div>

        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-camera class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Take Photo</h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Capture evidence photos</p>
                </div>
            </div>
            <button class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                Open Camera
            </button>
        </div>
    </div>

    <!-- My Reports -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">My Recent Reports</h3>
            <button class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                View All
            </button>
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-[#2a2a2a] rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                    <div>
                        <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Parking Violation - ABC-1234</p>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">2 hours ago</p>
                    </div>
                </div>
                <span class="px-2 py-1 text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full">
                    Pending
                </span>
            </div>
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-[#2a2a2a] rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                    <div>
                        <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">No Parking Zone - XYZ-5678</p>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">1 day ago</p>
                    </div>
                </div>
                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-full">
                    Under Review
                </span>
            </div>
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-[#2a2a2a] rounded-lg">
                <div class="flex items-center space-x-4">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <div>
                        <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Blocking Fire Exit - DEF-9012</p>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">3 days ago</p>
                    </div>
                </div>
                <span class="px-2 py-1 text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full">
                    Resolved
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
