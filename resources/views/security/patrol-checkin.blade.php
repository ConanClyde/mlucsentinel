@extends('layouts.app')

@section('page-title', 'Patrol Check-In')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-4 md:py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Location Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 mb-4 md:mb-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" 
                              style="background-color: {{ $location->color }}15; color: {{ $location->color }}">
                            {{ $location->type->name ?? 'Location' }}
                        </span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $location->short_code }}
                        </span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $location->name }}
                    </h1>
                    @if($location->description)
                        <p class="text-gray-600 dark:text-gray-400">{{ $location->description }}</p>
                    @endif
                </div>
                
                <!-- QR Code Icon -->
                <div class="flex-shrink-0">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Last Check-in Info -->
        @if($lastCheckin)
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4 md:mb-6">
                <div class="flex items-center gap-2 text-blue-800 dark:text-blue-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">Last check-in:</span>
                    <span>{{ $lastCheckin->securityUser->name ?? 'Unknown' }}</span>
                    <span class="text-sm">{{ $lastCheckin->checked_in_at->diffForHumans() }}</span>
                </div>
            </div>
        @endif

        <!-- Check-in Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 mb-4 md:mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Check In</h2>
            
            @if(config('app.debug'))
            <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm">
                <strong>Debug Info:</strong> Form will submit to: <code>{{ route('security.patrol-checkin.store') }}</code>
            </div>
            @endif
            
            <form method="POST" action="{{ route('security.patrol-checkin.store') }}" id="checkinForm">
                @csrf
                <input type="hidden" name="map_location_id" value="{{ $location->id }}">

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Notes (Optional)
                    </label>
                    <textarea 
                        name="notes" 
                        id="notes" 
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                        placeholder="Add any observations or notes about this patrol point..."></textarea>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Check In Now
                </button>
            </form>
        </div>

        <!-- Recent Check-ins -->
        @if($recentCheckins->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Your Recent Check-ins Here</h2>
                <div class="space-y-3">
                    @foreach($recentCheckins as $checkin)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $checkin->checked_in_at->format('M d, Y h:i A') }}
                                    </div>
                                    @if($checkin->notes)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $checkin->notes }}</div>
                                    @endif
                                </div>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $checkin->checked_in_at->diffForHumans() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Back Button -->
        <div class="mt-6">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 dark:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkinForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form submitting...');
            console.log('Action URL:', form.action);
            console.log('Method:', form.method);
            console.log('CSRF Token:', document.querySelector('input[name="_token"]')?.value ? 'Present' : 'Missing');
            console.log('Location ID:', document.querySelector('input[name="map_location_id"]')?.value);
        });
    }
});
</script>
@endpush
