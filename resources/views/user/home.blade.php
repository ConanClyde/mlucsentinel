@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                    Welcome back, {{ auth()->user()->first_name }}!
                </h1>
                <p class="text-[#706f6c] dark:text-[#A1A09A] mt-1">
                    {{ auth()->user()->user_type->label() }} Dashboard
                </p>
            </div>
            <div class="text-right">
                <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Last login</div>
                <div class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium">
                    {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('M j, Y g:i A') : 'First time' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- My Vehicles -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-truck class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $vehicleCount ?? 0 }}</div>
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">My Vehicles</div>
                </div>
            </div>
        </div>

        <!-- Active Stickers -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-identification class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $activeStickerCount ?? 0 }}</div>
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Active Stickers</div>
                </div>
            </div>
        </div>

        <!-- Violation Reports -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $violationCount ?? 0 }}</div>
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Violations</div>
                </div>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-clock class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $pendingRequestCount ?? 0 }}</div>
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Pending Requests</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Manage Vehicles -->
        <a href="{{ route('user.vehicles') }}" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-800 transition-colors">
                    <x-heroicon-o-truck class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        Manage Vehicles
                    </h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">View and manage your registered vehicles</p>
                </div>
            </div>
        </a>

        <!-- View Report History -->
        <a href="{{ route('user.reports') }}" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center group-hover:bg-red-200 dark:group-hover:bg-red-800 transition-colors">
                    <x-heroicon-o-document-text class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">
                        Report History
                    </h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">View your violation report history</p>
                </div>
            </div>
        </a>

        <!-- Request New Sticker -->
        <a href="{{ route('user.requests') }}" class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center group-hover:bg-green-200 dark:group-hover:bg-green-800 transition-colors">
                    <x-heroicon-o-plus-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">
                        Request Sticker
                    </h3>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Request a new vehicle sticker</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A]">
        <div class="p-6 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
            <h2 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Recent Activity</h2>
        </div>
        <div class="p-6">
            @if(isset($recentActivity) && $recentActivity->count() > 0)
                <div class="space-y-4">
                    @foreach($recentActivity as $activity)
                        <div class="flex items-start space-x-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                            <div class="flex-1">
                                <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $activity->description }}</p>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-clock class="w-12 h-12 text-[#706f6c] dark:text-[#A1A09A] mx-auto mb-4" />
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">No recent activity</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update time every second
    function updateTime() {
        const now = new Date();
        const timeElement = document.getElementById('current-time');
        const dateElement = document.getElementById('current-date');
        
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString();
        }
        if (dateElement) {
            dateElement.textContent = now.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }
    }
    
    updateTime();
    setInterval(updateTime, 1000);
});
</script>
@endsection
