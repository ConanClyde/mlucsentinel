<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - MLUC Sentinel</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
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
                <h2 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Two-Factor Authentication</h2>
                <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Enter the 6-digit code from your authenticator app
                </p>
            </div>

            <!-- 2FA Form -->
            <form class="mt-8 space-y-6 bg-white dark:bg-[#1b1b18] p-8 rounded-lg shadow-[0_1px_3px_0_rgba(0,0,0,0.1)] dark:shadow-[0_1px_3px_0_rgba(0,0,0,0.3)] border border-[#e3e3e0] dark:border-[#3E3E3A]" 
                  action="{{ route('2fa.verify.post') }}" 
                  method="POST">
            @csrf

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
                    <!-- Code Input -->
                    <div class="form-group">
                        <label for="code" class="form-label">Verification Code</label>
                        <input 
                            id="code" 
                            name="code" 
                            type="text" 
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="21"
                            autocomplete="one-time-code"
                            required 
                            autofocus
                            class="form-input text-center text-2xl tracking-widest"
                            placeholder="000000"
                        >
                    </div>

                    <!-- Recovery Code Link -->
                    <div class="text-center">
                        <button 
                            type="button"
                            id="toggle-mode-btn"
                            onclick="toggleRecoveryMode()" 
                            class="text-sm text-[#1b1b18] dark:text-[#EDEDEC] font-medium hover:underline"
                        >
                            Use a recovery code instead
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="btn btn-primary w-full">
                        Verify & Sign In
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

            // Handle form submission with CSRF token check
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const csrfToken = document.querySelector('input[name="_token"]');
                if (!csrfToken || !csrfToken.value) {
                    e.preventDefault();
                    alert('Session expired. Redirecting to login...');
                    window.location.href = '{{ route("login") }}';
                }
            });
        });

        function toggleRecoveryMode() {
            const input = document.getElementById('code');
            const label = input.previousElementSibling;
            const toggleBtn = document.getElementById('toggle-mode-btn');
            
            if (input.placeholder === '000000') {
                // Switch to recovery mode
                input.placeholder = 'XXXXXXXXXX-XXXXXXXXXX';
                input.maxlength = '21';
                input.type = 'text';
                input.inputmode = 'text';
                input.pattern = '';
                label.textContent = 'Recovery Code';
                toggleBtn.textContent = 'Use a verification code instead';
            } else {
                // Switch back to code mode
                input.placeholder = '000000';
                input.maxlength = '6';
                input.type = 'text';
                input.inputmode = 'numeric';
                input.pattern = '[0-9]*';
                label.textContent = 'Verification Code';
                toggleBtn.textContent = 'Use a recovery code instead';
            }
            input.value = '';
            input.focus();
        }
    </script>
</body>
</html>
