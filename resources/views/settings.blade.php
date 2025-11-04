@extends('layouts.app')

@section('page-title', 'Settings')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 md:py-8">
        <!-- Header -->
        <div class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Settings</h1>
            <p class="mt-2 text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">Manage your application settings and preferences</p>
        </div>

        <!-- Settings Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-3 md:p-4">
                    <nav class="space-y-1">
                        <button onclick="showSettingsTab('appearance')" id="tab-appearance" class="settings-tab active w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400">
                            <x-heroicon-o-paint-brush class="w-5 h-5 mr-3" />
                            Appearance
                        </button>
                        <button onclick="showSettingsTab('notifications')" id="tab-notifications" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-bell class="w-5 h-5 mr-3" />
                            Notifications
                        </button>
                        
                        @php
                            $restrictedRoles = ['security', 'reporter'];
                            $isRestrictedUser = in_array(strtolower(auth()->user()->user_type->value), $restrictedRoles);
                        @endphp

                        @unless($isRestrictedUser)
                        <button onclick="showSettingsTab('college')" id="tab-college" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-academic-cap class="w-5 h-5 mr-3" />
                            College
                        </button>
                        <button onclick="showSettingsTab('program')" id="tab-program" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-book-open class="w-5 h-5 mr-3" />
                            Programs
                        </button>
                        <button onclick="showSettingsTab('vehicle-type')" id="tab-vehicle-type" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-truck class="w-5 h-5 mr-3" />
                            Vehicle Type
                        </button>
                        <button onclick="showSettingsTab('location-type')" id="tab-location-type" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-map-pin class="w-5 h-5 mr-3" />
                            Location Type
                        </button>
                        <button onclick="showSettingsTab('fees')" id="tab-fees" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-currency-dollar class="w-5 h-5 mr-3" />
                            Fees
                        </button>
                        @endunless

                        <button onclick="showSettingsTab('security')" id="tab-security" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-shield-check class="w-5 h-5 mr-3" />
                            Security
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div class="lg:col-span-2">
                @include('settings.tabs.appearance')
                @include('settings.tabs.notifications')
                
                @unless($isRestrictedUser)
                @include('settings.tabs.college')
                @include('settings.tabs.program')
                @include('settings.tabs.vehicle-type')
                @include('settings.tabs.location-type')
                @include('settings.tabs.fees')
                @endunless
                
                @include('settings.tabs.security')
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('settings.modals.common-modals')

@unless($isRestrictedUser)
@include('settings.modals.college-modals')
@include('settings.modals.program-modals')
@include('settings.modals.vehicle-type-modals')
@include('settings.modals.location-type-modals')
@include('settings.modals.fees-modals')
@endunless

@include('settings.modals.2fa-modals')

<script>
    // Pass Laravel routes to JavaScript
    window.Laravel = window.Laravel || {};
    window.Laravel.routes = {
        'settings.2fa.enable': '{{ route("settings.2fa.enable") }}',
        'settings.2fa.confirm': '{{ route("settings.2fa.confirm") }}',
        'settings.2fa.disable': '{{ route("settings.2fa.disable") }}',
        'settings.2fa.recovery-codes': '{{ route("settings.2fa.recovery-codes") }}',
        'settings.activity-logs': '{{ route("settings.activity-logs") }}'
    };
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeOut {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.95); }
}
@keyframes highlight {
    0%, 100% { background-color: transparent; }
    50% { background-color: rgba(99, 102, 241, 0.1); }
}

.animate-fade-in { animation: fadeIn 0.5s ease-out; }
.animate-fade-out { animation: fadeOut 0.3s ease-out; }
.animate-highlight { animation: highlight 1s ease-out; }
</style>
@endsection
