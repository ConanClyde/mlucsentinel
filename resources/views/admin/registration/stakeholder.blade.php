@extends('layouts.app')

@section('page-title', 'Stakeholder Registration')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    Stakeholder Registration
                </h2>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    Register new stakeholder users
                </p>
            </div>
            <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                <x-heroicon-o-building-office class="w-8 h-8 text-purple-600 dark:text-purple-400" />
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">This is Stakeholder Registration Page</h3>
        <p class="text-[#706f6c] dark:text-[#A1A09A]">
            This page will contain stakeholder registration functionality including forms, organization details, and approval workflows.
        </p>
    </div>
</div>
@endsection
