@extends('layouts.app')

@section('page-title', 'My Patrol History')

@section('content')
<div class="space-y-6">
    <!-- Page Header - Simple -->
    <div class="mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-1 sm:mb-2">
            My Patrol History
        </h2>
        <p class="text-sm sm:text-base text-[#706f6c] dark:text-[#A1A09A]">
            View all your patrol check-ins
        </p>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 sm:p-6">
        <form method="GET" action="{{ route('security.patrol-history') }}" id="filter-form" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" 
                       class="form-input w-full" id="start-date-filter">
            </div>
            <div class="flex-1 w-full">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" 
                       class="form-input w-full" id="end-date-filter">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="button" id="reset-filters" class="btn btn-secondary flex-1 sm:flex-none !h-[38px] px-6">
                    Reset
                </button>
            </div>
        </form>
    </div>

    <!-- Patrol Logs Table -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] overflow-hidden">
        @if($logs->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">
                                Date & Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">
                                Location
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">
                                Code
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">
                                Notes
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">
                                GPS
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#1a1a1a] divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($logs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ $log->checked_in_at->format('M d, Y') }}
                                    </div>
                                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        {{ $log->checked_in_at->format('h:i A') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ $log->mapLocation->name }}
                                    </div>
                                    <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                        {{ $log->mapLocation->type->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ $log->mapLocation->short_code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-[#1b1b18] dark:text-[#EDEDEC] max-w-xs truncate">
                                        {{ $log->notes ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($log->latitude && $log->longitude)
                                        <span class="text-green-600 dark:text-green-400 font-medium">✓ Yes</span>
                                    @else
                                        <span class="text-[#706f6c] dark:text-[#A1A09A]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($logs as $log)
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC] truncate">
                                    {{ $log->mapLocation->name }}
                                </h3>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">
                                    {{ $log->mapLocation->type->name ?? 'N/A' }}
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 ml-2">
                                {{ $log->mapLocation->short_code }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 mt-3 text-xs">
                            <div>
                                <span class="text-[#706f6c] dark:text-[#A1A09A]">Date:</span>
                                <span class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium ml-1">
                                    {{ $log->checked_in_at->format('M d, Y') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[#706f6c] dark:text-[#A1A09A]">Time:</span>
                                <span class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium ml-1">
                                    {{ $log->checked_in_at->format('h:i A') }}
                                </span>
                            </div>
                            @if($log->notes)
                                <div class="col-span-2 mt-1">
                                    <span class="text-[#706f6c] dark:text-[#A1A09A]">Notes:</span>
                                    <p class="text-[#1b1b18] dark:text-[#EDEDEC] mt-1">{{ $log->notes }}</p>
                                </div>
                            @endif
                            <div class="col-span-2 flex items-center gap-2 mt-1">
                                @if($log->latitude && $log->longitude)
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    <span class="text-green-600 dark:text-green-400 text-xs font-medium">GPS Verified</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($logs->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $logs->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-12 px-4">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#706f6c] dark:text-[#A1A09A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    No patrol logs found
                </h3>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Start patrolling by scanning location QR codes
                </p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const startDateFilter = document.getElementById('start-date-filter');
    const endDateFilter = document.getElementById('end-date-filter');
    const resetButton = document.getElementById('reset-filters');

    // Auto-submit form when date filters change
    startDateFilter.addEventListener('change', function() {
        filterForm.submit();
    });

    endDateFilter.addEventListener('change', function() {
        filterForm.submit();
    });

    // Reset button - clear filters and submit
    resetButton.addEventListener('click', function() {
        startDateFilter.value = '';
        endDateFilter.value = '';
        filterForm.submit();
    });
});
</script>
@endpush
