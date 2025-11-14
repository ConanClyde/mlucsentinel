@extends('layouts.app')

@section('page-title', 'My Vehicles')

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Vehicles List -->
    <div>
        <div>
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Your Vehicles (<span class="vehicle-count">{{ $vehicles->count() }}</span>)</h3>
            
            @if($vehicles->count() > 0)
                <div class="vehicles-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                    @foreach($vehicles as $vehicle)
                        <!-- Vehicle Card -->
                        <div data-vehicle-id="{{ $vehicle->id }}" class="bg-white dark:bg-[#2a2a2a] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] overflow-hidden hover:shadow-md transition-shadow">
                            <!-- Sticker Image -->
                            <div class="bg-gray-50 dark:bg-[#1a1a1a] flex items-center justify-center border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                @if($vehicle->sticker)
                                    <img src="{{ asset($vehicle->sticker) }}" 
                                         alt="Vehicle Sticker" 
                                         class="w-full h-auto object-contain"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                    <div class="w-full h-32 bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 text-xs" style="display: none;">
                                        No Image
                                    </div>
                                @else
                                    <div class="w-full h-32 bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 text-xs">
                                        No Sticker
                                    </div>
                                @endif
                            </div>

                            <!-- Vehicle Details -->
                            <div class="p-2 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
                                <div class="space-y-1 text-center">
                                    <!-- Vehicle Type -->
                                    <div class="text-[#706f6c] dark:text-[#A1A09A]">
                                        <span class="text-xs font-medium">{{ $vehicle->type->name ?? 'N/A' }}</span>
                                    </div>

                                    <!-- Plate Number -->
                                    <div>
                                        <span class="text-sm font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $vehicle->plate_no }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="empty-state text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-heroicon-o-truck class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">No vehicles registered</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A] mb-4">You haven't registered any vehicles yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes fadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.95); }
    }
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }
    .animate-fade-out {
        animation: fadeOut 0.3s ease-out;
    }
    .animate-slide-in {
        animation: slideIn 0.3s ease-out;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize real-time updates
        const userId = {{ Auth::id() }};
        const realtimeManager = new window.MyVehiclesRealtime(userId);
        realtimeManager.init();
    });
</script>
@endpush
