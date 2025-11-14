@extends('layouts.app')

@section('page-title', 'Stickers Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/stickers.css') }}">
@endpush

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Tabs Navigation -->
    <div class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] mb-4 md:mb-6 overflow-x-auto">
        <nav class="flex space-x-4 md:space-x-8 min-w-max">
            <button id="stickersTab" class="sticker-tab active whitespace-nowrap" onclick="switchTab('stickers')">
                <x-heroicon-o-rectangle-stack class="w-4 h-4 md:w-5 md:h-5" />
                <span class="hidden sm:inline">Stickers</span>
            </button>
            <button id="requestTab" class="sticker-tab whitespace-nowrap" onclick="switchTab('request')">
                <x-heroicon-o-document-plus class="w-4 h-4 md:w-5 md:h-5" />
                <span class="hidden sm:inline">Request</span>
            </button>
            <button id="paymentTab" class="sticker-tab whitespace-nowrap" onclick="switchTab('payment')">
                <x-heroicon-o-credit-card class="w-4 h-4 md:w-5 md:h-5" />
                <span class="hidden sm:inline">Payment</span>
                <span id="paymentCount" class="ml-1 md:ml-2 px-1.5 md:px-2 py-0.5 rounded-full text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">{{ $pendingPaymentsCount ?? 0 }}</span>
            </button>
            <button id="transactionsTab" class="sticker-tab whitespace-nowrap" onclick="switchTab('transactions')">
                <x-heroicon-o-clipboard-document-list class="w-4 h-4 md:w-5 md:h-5" />
                <span class="hidden sm:inline">Transactions</span>
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    @include('admin.stickers.tabs.stickers-tab')
    @include('admin.stickers.tabs.request-tab')
    @include('admin.stickers.tabs.payment-tab')
    @include('admin.stickers.tabs.transactions-tab')
</div>

<!-- Modals -->
@include('admin.stickers.modals.all-modals')

@endsection

@push('scripts')
<script>
// Pass sticker fee to JavaScript for real-time calculations
window.stickerFee = {{ \App\Models\Fee::getAmount('sticker_fee', 15.00) }};

// Set current user ID for action tracking
window.currentUserId = {{ auth()->id() }};
window.currentUserName = '{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}';
</script>
@vite('resources/js/admin/stickers-page.js')
@vite('resources/js/admin/sticker-requests-realtime.js')
@endpush
