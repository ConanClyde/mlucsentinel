@extends('layouts.app')

@section('page-title', 'Reports Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    Reports Management
                </h2>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    Manage all violation reports
                </p>
            </div>
            <div class="w-16 h-16 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                <x-heroicon-o-document-text class="w-8 h-8 text-yellow-600 dark:text-yellow-400" />
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">This is Reports Page</h3>
        <p class="text-[#706f6c] dark:text-[#A1A09A] mb-6">
            This page will contain report management functionality including violation reports, status updates, and analytics.
        </p>
        
        <!-- Action Buttons -->
        <div class="flex gap-3">
            <button class="btn bg-blue-600 hover:bg-blue-700 text-white border-blue-600">View All Reports</button>
            <button class="btn bg-emerald-600 hover:bg-emerald-700 text-white border-emerald-600">Export CSV</button>
            <button class="btn bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-600">Filter Reports</button>
        </div>
    </div>
</div>
@endsection
