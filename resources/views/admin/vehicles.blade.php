@extends('layouts.app')

@section('page-title', 'Vehicles Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    Vehicles Management
                </h2>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    Manage all registered vehicles
                </p>
            </div>
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                <x-heroicon-o-truck class="w-8 h-8 text-green-600 dark:text-green-400" />
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">This is Vehicles Page</h3>
        <p class="text-[#706f6c] dark:text-[#A1A09A] mb-6">
            This page will contain vehicle management functionality including vehicle registration, permits, and tracking.
        </p>
        
        <!-- Action Buttons -->
        <div class="flex gap-3">
            <button class="btn bg-green-600 hover:bg-green-700 text-white border-green-600">Add Vehicle</button>
            <button class="btn bg-gray-600 hover:bg-gray-700 text-white border-gray-600">Export Vehicles</button>
            <button class="btn bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-600">Search</button>
        </div>
    </div>
</div>
@endsection
