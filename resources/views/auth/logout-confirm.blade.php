<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Logging Out - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF9F6] dark:bg-[#0a0a0a] min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full px-6">
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-8">
            <!-- Logo/Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h2 class="text-2xl font-bold text-center text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                Logging You Out
            </h2>
            
            <!-- Message -->
            <p class="text-center text-[#706f6c] dark:text-[#A1A09A] mb-6">
                Please wait while we securely log you out...
            </p>

            <!-- Loading Spinner -->
            <div class="flex justify-center mb-6">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>

            <!-- Cancel Button (in case auto-submit fails) -->
            <div class="text-center">
                <a href="{{ route('home') }}" class="text-sm text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] transition-colors">
                    Cancel and go back
                </a>
            </div>
        </div>

        <!-- Hidden Auto-Submit Form -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>

    <script>
        // Auto-submit the logout form after a brief delay for better UX
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('logout-form').submit();
            }, 500);
        });
    </script>
</body>
</html>

