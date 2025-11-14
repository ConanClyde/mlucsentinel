@extends('layouts.guest')

@section('title', 'Register - MLUC Sentinel')

@section('content')
<div class="min-h-screen py-4 md:py-8 flex items-center justify-center">
    <div class="max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 md:mb-8 text-center">
            <h1 class="text-xl md:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Create Account</h1>
            <p class="text-sm md:text-base text-[#706f6c] dark:text-[#A1A09A]">Choose your role to get started with MLUC Sentinel</p>
        </div>

        <!-- User Type Selection -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 md:p-8">
            <h2 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-6 text-center">Select Your Role</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <!-- Student -->
                <a href="{{ route('register', ['type' => 'student']) }}" class="group block p-6 bg-white dark:bg-[#2a2a2a] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] hover:shadow-md hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-200">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-200 dark:group-hover:bg-blue-800 transition-colors">
                            <x-heroicon-o-academic-cap class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Student</h3>
                    </div>
                </a>

                <!-- Staff -->
                <a href="{{ route('register', ['type' => 'staff']) }}" class="group block p-6 bg-white dark:bg-[#2a2a2a] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] hover:shadow-md hover:border-green-300 dark:hover:border-green-600 transition-all duration-200">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-green-200 dark:group-hover:bg-green-800 transition-colors">
                            <x-heroicon-o-briefcase class="w-8 h-8 text-green-600 dark:text-green-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Staff</h3>
                    </div>
                </a>

                <!-- Stakeholder -->
                <a href="{{ route('register', ['type' => 'stakeholder']) }}" class="group block p-6 bg-white dark:bg-[#2a2a2a] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] hover:shadow-md hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-200">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-purple-200 dark:group-hover:bg-purple-800 transition-colors">
                            <x-heroicon-o-building-office class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Stakeholder</h3>
                    </div>
                </a>

                <!-- Security -->
                <a href="{{ route('register', ['type' => 'security']) }}" class="group block p-6 bg-white dark:bg-[#2a2a2a] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] hover:shadow-md hover:border-red-300 dark:hover:border-red-600 transition-all duration-200">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-red-200 dark:group-hover:bg-red-800 transition-colors">
                            <x-heroicon-o-shield-check class="w-8 h-8 text-red-600 dark:text-red-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Security</h3>
                    </div>
                </a>

                <!-- Reporter -->
                <a href="{{ route('register', ['type' => 'reporter']) }}" class="group block p-6 bg-white dark:bg-[#2a2a2a] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] hover:shadow-md hover:border-orange-300 dark:hover:border-orange-600 transition-all duration-200">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-orange-200 dark:group-hover:bg-orange-800 transition-colors">
                            <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-orange-600 dark:text-orange-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Reporter</h3>
                    </div>
                </a>
            </div>

            <!-- Info Notice -->
            <div class="mt-8 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-md p-4">
                <div class="flex">
                    <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-amber-500 mr-2" />
                    <p class="text-sm text-amber-700 dark:text-amber-400">
                        <strong>Approval Required:</strong> All registrations require administrator approval before account activation.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Login Link -->
        <div class="mt-6 text-center">
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium hover:underline">
                    Sign in here
                </a>
            </p>
        </div>

        <!-- Back to Home -->
        <div class="mt-4 text-center">
            <a href="{{ route('landing') }}" class="text-sm text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to home
            </a>
        </div>
    </div>
</div>
@endsection
