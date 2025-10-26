@extends('layouts.app')

@section('page-title', 'Staff Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    Staff Management
                </h2>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    Manage all staff users
                </p>
            </div>
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                <x-heroicon-o-user-group class="w-8 h-8 text-green-600 dark:text-green-400" />
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">This is Staff Page</h3>
        <p class="text-[#706f6c] dark:text-[#A1A09A]">
            This page will contain staff management functionality including staff lists, departments, and employment information.
        </p>
    </div>
</div>
@endsection
