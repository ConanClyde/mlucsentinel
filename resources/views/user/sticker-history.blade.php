@extends('layouts.app')

@section('page-title', 'Sticker History')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Sticker History</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A] mt-1">
                View payments and sticker issuance history for all of your registered vehicles.
            </p>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900/40 rounded-lg px-4 py-3 text-sm text-blue-900 dark:text-blue-200 flex items-center gap-3">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 dark:text-blue-300" />
            <div>
                <p class="font-medium">Need a receipt?</p>
                <p class="text-xs text-blue-700 dark:text-blue-300">Click the receipt button on any paid transaction.</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <form method="GET" action="{{ route('user.stickers.history') }}" class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-input w-full">
                    <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="failed" {{ $statusFilter === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div>
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ $searchTerm }}" class="form-input w-full" placeholder="Reference, vehicle, or plate no.">
            </div>
            <div>
                <label class="form-label">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input w-full">
            </div>
            <div>
                <label class="form-label">To</label>
                <div class="flex gap-2">
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input w-full">
                    <button type="submit" class="btn btn-primary whitespace-nowrap px-4">
                        Filter
                    </button>
                </div>
                <button type="button" onclick="window.location.href='{{ route('user.stickers.history') }}'" class="text-xs text-blue-600 dark:text-blue-400 mt-2 hover:underline">
                    Reset filters
                </button>
            </div>
        </form>
    </div>

    <!-- History Table -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Reference</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Vehicle</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Amount</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Status</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Paid Date</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Requested</th>
                        <th class="text-center py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]">
                            <td class="py-2 px-3">
                                <p class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $payment->reference ?? 'Pending' }}</p>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">#{{ $payment->id }}</p>
                            </td>
                            <td class="py-2 px-3">
                                @if($payment->vehicle_count > 1)
                                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $payment->vehicle_count }} Vehicles</p>
                                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Batch payment</p>
                                @else
                                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $payment->vehicle->type->name ?? 'Vehicle' }}</p>
                                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $payment->vehicle->plate_no ?? ($payment->vehicle?->color.' '.$payment->vehicle?->number) ?? 'No plate' }}</p>
                                @endif
                            </td>
                            <td class="py-2 px-3">
                                <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">₱{{ number_format($payment->amount ?? 0, 2) }}</span>
                            </td>
                            <td class="py-2 px-3">
                                @php
                                    $statusClass = match($payment->status) {
                                        'paid' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                                        'pending' => 'bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200',
                                        'cancelled' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
                                        'failed' => 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200',
                                        default => 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                {{ $payment->paid_at ? $payment->paid_at->format('M d, Y h:i A') : '-' }}
                            </td>
                            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                {{ $payment->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="py-2 px-3">
                                <div class="flex items-center justify-center gap-2">
                                    @if($payment->status === 'paid')
                                        <button
                                            type="button"
                                            class="btn-view"
                                            data-receipt-url="{{ route('user.stickers.receipt', $payment) }}"
                                            title="View Receipt"
                                        >
                                            <x-heroicon-s-eye class="w-4 h-4" />
                                        </button>
                                    @else
                                        <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">N/A</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10">
                                <div class="text-center">
                                    <img src="/images/empty-state.svg" alt="No history" class="w-32 mx-auto mb-4 opacity-75 hidden dark:block">
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No sticker history found. Once you submit requests and payments are processed, they will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-6">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Showing {{ $payments->firstItem() ?? 0 }}-{{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} transactions
                </p>
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Receipt Modal -->
<div id="userReceiptModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
    <div class="relative bg-white dark:bg-[#0f0f0f] rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-[#e3e3e0] dark:border-[#3E3E3A]">
        <button type="button" id="closeUserReceiptModal" class="absolute top-4 right-4 text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-white">
            <x-heroicon-o-x-mark class="w-6 h-6" />
        </button>
        <div class="p-6 space-y-4" id="userReceiptContent">
            <!-- Content injected via JS -->
        </div>
        <div class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] px-6 py-4 flex justify-end">
            <button type="button" class="btn btn-secondary" id="userReceiptCloseBtn">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/user/sticker-history.js')
@endpush

