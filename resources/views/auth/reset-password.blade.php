<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - MLUC Sentinel</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/auth/reset-password.js'])
    
    <!-- Dark Mode Script - Run before page renders to prevent flash -->
    <script>
        // Check for saved theme preference or default to light mode
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="bg-[#FDFDFC] dark:bg-[#161615]">
    <!-- Theme Toggle -->
    <div class="fixed top-4 right-4 z-50">
        <button id="theme-toggle" class="btn btn-secondary !px-2 !py-2 aspect-square">
            {{-- Sun Icon (Light Mode) --}}
            <x-heroicon-s-sun id="sun-icon" class="w-4 h-4 hidden dark:block" />
            {{-- Moon Icon (Dark Mode) --}}
            <x-heroicon-s-moon id="moon-icon" class="w-4 h-4 block dark:hidden" />
        </button>
    </div>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <h2 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Reset Password</h2>
                <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Enter the code we sent to your email and your new password
                </p>
            </div>

            <!-- Reset Password Form -->
            <form class="mt-8 space-y-6 bg-white dark:bg-[#1b1b18] p-8 rounded-lg shadow-[0_1px_3px_0_rgba(0,0,0,0.1)] dark:shadow-[0_1px_3px_0_rgba(0,0,0,0.3)] border border-[#e3e3e0] dark:border-[#3E3E3A]" 
                  action="{{ route('password.update') }}" 
                  method="POST">
                @csrf

                @if (session('status'))
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4">
                        <div class="flex">
                            <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 mr-2" />
                            <p class="text-sm text-green-700 dark:text-green-400">{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
                        <div class="flex">
                            <x-heroicon-s-exclamation-circle class="w-5 h-5 text-red-500 mr-2" />
                            <div class="text-sm text-red-700 dark:text-red-400">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-4">
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            required 
                            {{ request()->has('email') ? 'readonly' : 'autofocus' }}
                            class="form-input {{ request()->has('email') ? 'bg-gray-50 dark:bg-gray-800' : '' }}" 
                            placeholder="john@example.com"
                            value="{{ old('email', request()->email) }}"
                        >
                    </div>

                    <!-- Reset Code -->
                    <div class="form-group">
                        <label for="code" class="form-label">Reset Code</label>
                        <input 
                            id="code" 
                            name="code" 
                            type="text" 
                            required 
                            maxlength="6"
                            {{ request()->has('email') ? 'autofocus' : '' }}
                            class="form-input text-center text-2xl tracking-widest font-mono" 
                            placeholder="000000"
                            value="{{ old('code') }}"
                        >
                        <div class="flex justify-between items-center mt-1">
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                Enter the 6-digit code sent to your email
                            </p>
                            @if(request()->has('email'))
                                <button type="button" id="resendCodeBtn" class="text-xs text-[#1b1b18] dark:text-[#EDEDEC] font-medium hover:underline bg-transparent border-none cursor-pointer">
                                    Resend Code
                                </button>
                            @endif
                        </div>
                        <!-- Code validation message -->
                        <div id="codeValidationMessage" class="mt-2 text-sm hidden"></div>
                    </div>

                    <!-- Password Fields Container (initially hidden) -->
                    <div id="passwordFields" class="space-y-4 hidden transition-all duration-300 ease-in-out opacity-0 max-h-0 overflow-hidden m-0 p-0">
                        <!-- New Password -->
                        <div class="form-group">
                            <label for="password" class="form-label">New Password</label>
                            <div class="relative">
                                <input 
                                    id="password" 
                                    name="password" 
                                    type="password" 
                                    class="form-input pr-10" 
                                    placeholder="••••••••"
                                >
                                <button 
                                    type="button" 
                                    class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors"
                                    data-target="password"
                                >
                                    <x-heroicon-c-eye class="w-5 h-5 eye-icon" />
                                    <x-heroicon-c-eye-slash class="w-5 h-5 eye-slash-icon hidden" />
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                Must be at least 8 characters long
                            </p>
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <div class="relative">
                                <input 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    type="password" 
                                    class="form-input pr-10" 
                                    placeholder="••••••••"
                                >
                                <button 
                                    type="button" 
                                    class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors"
                                    data-target="password_confirmation"
                                >
                                    <x-heroicon-c-eye class="w-5 h-5 eye-icon" />
                                    <x-heroicon-c-eye-slash class="w-5 h-5 eye-slash-icon hidden" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button (initially hidden) -->
                <div id="submitButtonContainer" class="hidden">
                    <button type="submit" class="btn btn-primary w-full">
                        Reset Password
                    </button>
                </div>
            </form>
            
            <!-- Back to Login -->
            <div class="text-center">
                <a href="{{ route('login') }}" class="text-sm text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                    <x-heroicon-s-arrow-left class="w-4 h-4 inline-block mr-1" />
                    Back to login
                </a>
            </div>
        </div>
    </div>

    <script>
        // Dark/Light Mode Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const html = document.documentElement;
            
            // Check for saved theme preference or default to light mode
            const currentTheme = localStorage.getItem('theme') || 'light';
            html.classList.toggle('dark', currentTheme === 'dark');
            
            themeToggle.addEventListener('click', function() {
                const isDark = html.classList.contains('dark');
                const newTheme = isDark ? 'light' : 'dark';
                
                html.classList.toggle('dark');
                localStorage.setItem('theme', newTheme);
            });
        });
    </script>
</body>
</html>
