@extends('layouts.app')

@section('page-title', 'My Vehicles')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    My Vehicles
                </h2>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    Manage your registered vehicles
                </p>
            </div>
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                <x-heroicon-o-truck class="w-8 h-8 text-green-600 dark:text-green-400" />
            </div>
        </div>
    </div>

    <!-- Add Vehicle Button -->
    <div class="flex justify-end gap-3">
        <button class="btn bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-600">Search</button>
        <button class="btn bg-green-600 hover:bg-green-700 text-white border-green-600">
            <x-heroicon-o-plus class="w-4 h-4 inline-block mr-2" />
            Add Vehicle
        </button>
    </div>

    <!-- Vehicles List -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A]">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Your Vehicles</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Vehicle Card 1 -->
                <div class="bg-gray-50 dark:bg-[#2a2a2a] rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-truck class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full">
                            Active
                        </span>
                    </div>
                    <h4 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-1">ABC-1234</h4>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-2">Toyota Vios</p>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-3">White • Sedan</p>
                    <div class="flex space-x-2">
                        <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm">
                            Edit
                        </button>
                        <button class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm">
                            Remove
                        </button>
                    </div>
                </div>

                <!-- Vehicle Card 2 -->
                <div class="bg-gray-50 dark:bg-[#2a2a2a] rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-truck class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-full">
                            Pending
                        </span>
                    </div>
                    <h4 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-1">XYZ-5678</h4>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-2">Honda Civic</p>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-3">Black • Sedan</p>
                    <div class="flex space-x-2">
                        <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm">
                            Edit
                        </button>
                        <button class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm">
                            Remove
                        </button>
                    </div>
                </div>

                <!-- Vehicle Card 3 -->
                <div class="bg-gray-50 dark:bg-[#2a2a2a] rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-truck class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full">
                            Expired
                        </span>
                    </div>
                    <h4 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-1">DEF-9012</h4>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-2">Nissan Altima</p>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-3">Silver • Sedan</p>
                    <div class="flex space-x-2">
                        <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm">
                            Renew
                        </button>
                        <button class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm">
                            Remove
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div class="text-center py-12 hidden">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-truck class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">No vehicles registered</h3>
                <p class="text-[#706f6c] dark:text-[#A1A09A] mb-4">You haven't registered any vehicles yet.</p>
                <button class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    Add Your First Vehicle
                </button>
            </div>
        </div>
    </div>

    <!-- Vehicle Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Active Vehicles</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">1</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-clock class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Pending Approval</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">1</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Expired</p>
                    <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">1</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
