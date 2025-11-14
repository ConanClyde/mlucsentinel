@extends('layouts.app')

@section('page-title', 'My Vehicles')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">My Vehicles</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A] mt-1">Manage your registered vehicles and stickers</p>
        </div>
    </div>

    <!-- Vehicles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($vehicles as $vehicle)
            <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] overflow-hidden">
                <!-- Vehicle Header -->
                <div class="p-6 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-truck class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                    {{ $vehicle->vehicleType->name }}
                                </h3>
                                @if($vehicle->plate_no)
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $vehicle->plate_no }}</p>
                                @else
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No plate number</p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        @if($vehicle->sticker)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Has Sticker
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                No Sticker
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Vehicle Details -->
                <div class="p-6 space-y-4">
                    <!-- Sticker Information -->
                    @if($vehicle->sticker)
                        <div>
                            <h4 class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Sticker Information</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-[#706f6c] dark:text-[#A1A09A]">Sticker Number:</span>
                                    <span class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium">{{ $vehicle->number }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-[#706f6c] dark:text-[#A1A09A]">Sticker Color:</span>
                                    <span class="text-[#1b1b18] dark:text-[#EDEDEC]">{{ ucfirst($vehicle->color) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-[#706f6c] dark:text-[#A1A09A]">Status:</span>
                                    <span class="text-green-600 dark:text-green-400 font-medium">Active</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <x-heroicon-o-identification class="w-8 h-8 text-[#706f6c] dark:text-[#A1A09A] mx-auto mb-2" />
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No sticker assigned</p>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex space-x-2 pt-4 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
                        @if(!$vehicle->sticker)
                            <a href="{{ route('user.requests', ['vehicle_id' => $vehicle->id]) }}" 
                               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-md text-center transition-colors">
                                Request Sticker
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <!-- No Vehicles -->
            <div class="col-span-full">
                <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-12 text-center">
                    <x-heroicon-o-truck class="w-16 h-16 text-[#706f6c] dark:text-[#A1A09A] mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">No Vehicles Registered</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A] mb-6">You haven't registered any vehicles yet. Contact the admin to register your vehicles.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Vehicle Statistics -->
    @if($vehicles->count() > 0)
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <h2 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Vehicle Summary</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $vehicles->count() }}</div>
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Total Vehicles</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ $vehicles->filter(function($v) { return $v->sticker; })->count() }}
                    </div>
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Has Stickers</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                        {{ $vehicles->filter(function($v) { return !$v->sticker; })->count() }}
                    </div>
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Needs Sticker</div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
