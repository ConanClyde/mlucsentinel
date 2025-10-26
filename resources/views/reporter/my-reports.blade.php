@extends('layouts.app')

@section('page-title', 'My Reports')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    My Reports
                </h2>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    View and manage your submitted reports
                </p>
            </div>
            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                <x-heroicon-o-document-text class="w-8 h-8 text-blue-600 dark:text-blue-400" />
            </div>
        </div>
    </div>

    <!-- Filter and Search -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" placeholder="Search reports..." class="w-full px-3 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#1a1a1a] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <select class="px-3 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#1a1a1a] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="under_review">Under Review</option>
                    <option value="resolved">Resolved</option>
                    <option value="dismissed">Dismissed</option>
                </select>
            </div>
            <div>
                <select class="px-3 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#1a1a1a] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="illegal_parking">Illegal Parking</option>
                    <option value="no_parking_zone">No Parking Zone</option>
                    <option value="blocking_fire_exit">Blocking Fire Exit</option>
                    <option value="handicap_violation">Handicap Violation</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button class="btn bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-800 dark:text-blue-200 border-blue-300 dark:border-blue-700">Filter</button>
                <button class="btn bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-600">Clear</button>
            </div>
        </div>
    </div>

    <!-- Reports List -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A]">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Your Reports</h3>
            
            <div class="space-y-4">
                <!-- Report Item 1 -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-[#2a2a2a] rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                        <div>
                            <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Parking Violation - ABC-1234</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Building A, Parking Lot 1 • 2 hours ago</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-1 text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full">
                            Pending
                        </span>
                        <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm">
                            View Details
                        </button>
                    </div>
                </div>

                <!-- Report Item 2 -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-[#2a2a2a] rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                        <div>
                            <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">No Parking Zone - XYZ-5678</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Main Entrance • 1 day ago</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-1 text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-full">
                            Under Review
                        </span>
                        <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm">
                            View Details
                        </button>
                    </div>
                </div>

                <!-- Report Item 3 -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-[#2a2a2a] rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <div>
                            <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Blocking Fire Exit - DEF-9012</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Building B • 3 days ago</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-1 text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full">
                            Resolved
                        </span>
                        <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm">
                            View Details
                        </button>
                    </div>
                </div>

                <!-- Report Item 4 -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-[#2a2a2a] rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-2 h-2 bg-gray-500 rounded-full"></div>
                        <div>
                            <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Handicap Violation - GHI-3456</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Parking Lot 2 • 1 week ago</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 rounded-full">
                            Dismissed
                        </span>
                        <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm">
                            View Details
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Showing 1-4 of 4 reports
                </p>
                <div class="flex space-x-2">
                    <button class="btn-pagination btn-paginationDisable" disabled>
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </button>
                    <button class="btn-pagination btn-paginationActive">1</button>
                    <button class="btn-pagination btn-paginationArrow" disabled>
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
