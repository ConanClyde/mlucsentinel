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
        <div class="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-4 md:gap-6">
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1 max-w-[240px]">
                <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-3 md:p-4">
                    <nav class="space-y-1">
                        @if(Auth::user()->hasPrivilege('view_settings_appearance'))
                        <button onclick="showSettingsTab('appearance')" id="tab-appearance" class="settings-tab active w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400">
                            <x-heroicon-o-paint-brush class="w-5 h-5 mr-3" />
                            Appearance
                        </button>
                        @endif
                        @if(Auth::user()->hasPrivilege('view_settings_notifications'))
                        <button onclick="showSettingsTab('notifications')" id="tab-notifications" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-bell class="w-5 h-5 mr-3" />
                            Notifications
                        </button>
                        @endif
                        
                        @if(Auth::user()->hasPrivilege('view_settings_college'))
                        <button onclick="showSettingsTab('college')" id="tab-college" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-academic-cap class="w-5 h-5 mr-3" />
                            College
                        </button>
                        @endif
                        @if(Auth::user()->hasPrivilege('view_settings_program'))
                        <button onclick="showSettingsTab('program')" id="tab-program" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-book-open class="w-5 h-5 mr-3" />
                            Programs
                        </button>
                        @endif
                        @if(Auth::user()->hasPrivilege('view_settings_vehicle_type'))
                        <button onclick="showSettingsTab('vehicle-type')" id="tab-vehicle-type" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-truck class="w-5 h-5 mr-3" />
                            Vehicle Type
                        </button>
                        @endif
                        @if(Auth::user()->hasPrivilege('view_settings_location_type'))
                        <button onclick="showSettingsTab('location-type')" id="tab-location-type" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-map-pin class="w-5 h-5 mr-3" />
                            Location Type
                        </button>
                        @endif
                        @if(Auth::user()->hasPrivilege('view_settings_fees'))
                        <button onclick="showSettingsTab('fees')" id="tab-fees" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-currency-dollar class="w-5 h-5 mr-3" />
                            Fees
                        </button>
                        @endif

                        @if(auth()->user()->isGlobalAdministrator())
                        <button onclick="showSettingsTab('sticker-colors')" id="tab-sticker-colors" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-swatch class="w-5 h-5 mr-3" />
                            Sticker Colors
                        </button>
                        <button onclick="showSettingsTab('sticker-rules')" id="tab-sticker-rules" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-cog-6-tooth class="w-5 h-5 mr-3" />
                            Sticker Rules
                        </button>
                        <button onclick="showSettingsTab('stakeholders')" id="tab-stakeholders" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-user-circle class="w-5 h-5 mr-3" />
                            Stakeholders
                        </button>
                        <button onclick="showSettingsTab('reporters')" id="tab-reporters" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-megaphone class="w-5 h-5 mr-3" />
                            Reporters
                        </button>
                        @endif

                        @if(auth()->user()->isGlobalAdministrator())
                        <button onclick="showSettingsTab('admin-roles')" id="tab-admin-roles" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-user-group class="w-5 h-5 mr-3" />
                            Admin Roles
                        </button>
                        @endif

                        @if(Auth::user()->hasPrivilege('view_settings_security'))
                        <button onclick="showSettingsTab('security')" id="tab-security" class="settings-tab w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                            <x-heroicon-o-shield-check class="w-5 h-5 mr-3" />
                            Security
                        </button>
                        @endif
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div>
                @if(Auth::user()->hasPrivilege('view_settings_appearance'))
                @include('settings.tabs.appearance')
                @endif
                @if(Auth::user()->hasPrivilege('view_settings_notifications'))
                @include('settings.tabs.notifications')
                @endif
                
                @if(Auth::user()->hasPrivilege('view_settings_college'))
                @include('settings.tabs.college')
                @endif
                @if(Auth::user()->hasPrivilege('view_settings_program'))
                @include('settings.tabs.program')
                @endif
                @if(Auth::user()->hasPrivilege('view_settings_vehicle_type'))
                @include('settings.tabs.vehicle-type')
                @endif
                @if(Auth::user()->hasPrivilege('view_settings_location_type'))
                @include('settings.tabs.location-type')
                @endif
                @if(Auth::user()->hasPrivilege('view_settings_fees'))
                @include('settings.tabs.fees')
                @endif
                
                @if(auth()->user()->isGlobalAdministrator())
                @include('settings.tabs.admin-roles')
                @include('settings.tabs.sticker-colors')
                @include('settings.tabs.sticker-rules')
                @include('settings.tabs.stakeholders')
                @include('settings.tabs.reporters')
                @endif
                
                @if(Auth::user()->hasPrivilege('view_settings_security'))
                @include('settings.tabs.security')
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('settings.modals.common-modals')

@if(Auth::user()->hasPrivilege('view_settings_college'))
@include('settings.modals.college-modals')
@endif
@if(Auth::user()->hasPrivilege('view_settings_program'))
@include('settings.modals.program-modals')
@endif
@if(Auth::user()->hasPrivilege('view_settings_vehicle_type'))
@include('settings.modals.vehicle-type-modals')
@endif
@if(Auth::user()->hasPrivilege('view_settings_location_type'))
@include('settings.modals.location-type-modals')
@endif
@if(Auth::user()->hasPrivilege('view_settings_fees'))
@include('settings.modals.fees-modals')
@endif

@if(auth()->user()->isGlobalAdministrator())
@include('settings.modals.admin-roles-modals')
@include('settings.modals.sticker-color-modals')
@include('settings.modals.stakeholder-modals')
@endif

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
