<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - MLUC Sentinel</title>
    
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
                <h2 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Welcome Back</h2>
                <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Sign in to your account to continue
                </p>
            </div>

            <!-- Login Form -->
            <form class="mt-8 space-y-6 bg-white dark:bg-[#1b1b18] p-8 rounded-lg shadow-[0_1px_3px_0_rgba(0,0,0,0.1)] dark:shadow-[0_1px_3px_0_rgba(0,0,0,0.3)] border border-[#e3e3e0] dark:border-[#3E3E3A]" 
                  action="{{ route('login.post') }}" 
                  method="POST">
                @csrf

                @if ($errors->any())
                    @php
                        $hasDeactivatedError = false;
                        $deactivatedMessage = '';
                        $otherErrors = [];
                        
                        foreach ($errors->all() as $error) {
                            if (str_contains(strtolower($error), 'deactivated')) {
                                $hasDeactivatedError = true;
                                $deactivatedMessage = $error;
                            } else {
                                $otherErrors[] = $error;
                            }
                        }
                    @endphp
                    
                    @if(count($otherErrors) > 0)
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
                        <div class="flex">
                            <x-heroicon-s-exclamation-circle class="w-5 h-5 text-red-500 mr-2" />
                            <div class="text-sm text-red-700 dark:text-red-400">
                                    @foreach ($otherErrors as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                @endif

                @if (session('status'))
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4">
                        <div class="flex">
                            <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 mr-2" />
                            <p class="text-sm text-green-700 dark:text-green-400">{{ session('status') }}</p>
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
                            autofocus
                            class="form-input" 
                            placeholder="john@example.com"
                            value="{{ old('email') }}"
                        >
                        <div id="email_error" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="relative">
                            <input 
                                id="password" 
                                name="password" 
                                type="password" 
                                required 
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
                        <div id="password_error" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                id="remember" 
                                name="remember" 
                                type="checkbox" 
                                class="w-4 h-4 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded bg-white dark:bg-[#161615] checked:bg-[#1b1b18] dark:checked:bg-[#eeeeec] focus:ring-2 focus:ring-[#1b1b18] dark:focus:ring-[#eeeeec]"
                            >
                            <label for="remember" class="ml-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                Remember me
                            </label>
                        </div>
                        <div class="text-sm">
                            <a href="{{ route('password.request') }}" class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium hover:underline">
                                Forgot password?
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="btn btn-primary w-full">
                        Sign In
                    </button>
                </div>

                <!-- Registration Guidance -->
                <div class="text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Need an account? Please contact your campus administrator for assistance.
                </div>
            </form>

            <!-- Back to Home -->
            <div class="text-center">
                <a href="{{ route('landing') }}" class="text-sm text-[#706f6c] dark:text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                    <x-heroicon-s-arrow-left class="w-4 h-4 inline-block mr-1" />
                    Back to home
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

            // Password Toggle - Direct handler for login page
            document.addEventListener('click', function(e) {
                const toggleButton = e.target.closest('.toggle-password');
                if (!toggleButton) return;
                
                e.preventDefault();
                e.stopPropagation();
                
                const targetId = toggleButton.getAttribute('data-target') || 'password';
                const passwordInput = document.getElementById(targetId);
                if (!passwordInput) return;
                
                const eyeIcon = toggleButton.querySelector('.eye-icon');
                const eyeSlashIcon = toggleButton.querySelector('.eye-slash-icon');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    if (eyeIcon) eyeIcon.classList.add('hidden');
                    if (eyeSlashIcon) eyeSlashIcon.classList.remove('hidden');
                } else {
                    passwordInput.type = 'password';
                    if (eyeIcon) eyeIcon.classList.remove('hidden');
                    if (eyeSlashIcon) eyeSlashIcon.classList.add('hidden');
                }
            });

            // Form validation
            const form = document.querySelector('form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            // Delayed validation (2 seconds after user stops typing)
            let emailTimeout;
            let passwordTimeout;

            emailInput.addEventListener('input', function() {
                clearTimeout(emailTimeout);
                emailTimeout = setTimeout(validateEmail, 1000);
            });

            passwordInput.addEventListener('input', function() {
                clearTimeout(passwordTimeout);
                passwordTimeout = setTimeout(validatePassword, 1000);
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const isEmailValid = validateEmail();
                const isPasswordValid = validatePassword();
                
                if (isEmailValid && isPasswordValid) {
                    form.submit();
                }
            });

            function validateEmail() {
                const email = emailInput.value.trim();
                const emailError = document.getElementById('email_error');
                
                if (!email) {
                    showError(emailInput, emailError, 'Email is required');
                    return false;
                } else if (!isValidEmail(email)) {
                    showError(emailInput, emailError, 'Please enter a valid email address');
                    return false;
                } else {
                    clearError(emailInput, emailError);
                    return true;
                }
            }

            function validatePassword() {
                const password = passwordInput.value;
                const passwordError = document.getElementById('password_error');
                
                if (!password) {
                    showError(passwordInput, passwordError, 'Password is required');
                    return false;
                } else {
                    clearError(passwordInput, passwordError);
                    return true;
                }
            }

            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            function showError(input, errorElement, message) {
                errorElement.textContent = message;
                errorElement.classList.remove('hidden');
                input.classList.remove('border-gray-300', 'dark:border-gray-600');
                input.classList.add('border-red-500');
            }

            function clearError(input, errorElement) {
                errorElement.classList.add('hidden');
                errorElement.textContent = '';
                input.classList.remove('border-red-500');
                input.classList.add('border-gray-300', 'dark:border-gray-600');
            }

            // Show deactivation modal if there's a deactivated error
            @if(isset($hasDeactivatedError) && $hasDeactivatedError)
                showInactiveAccountModal(@json($deactivatedMessage));
            @endif

            function showInactiveAccountModal(message) {
                // Create modal HTML
                const modalHTML = `
                    <div id="inactive-modal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 dark:bg-black/70">
                        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-2xl max-w-md w-full mx-4 border border-[#e3e3e0] dark:border-[#3E3E3A] animate-scale-in">
                            <div class="p-6">
                                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 dark:bg-red-900/30 rounded-full">
                                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-center text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                    Account Deactivated
                                </h3>
                                <p class="text-center text-[#706f6c] dark:text-[#A1A09A] mb-6">
                                    ${message}
                                </p>
                                <div class="flex justify-center">
                                    <button onclick="closeInactiveModal()" class="btn btn-primary px-6">
                                        OK
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Insert modal into body
                document.body.insertAdjacentHTML('beforeend', modalHTML);
            }

            window.closeInactiveModal = function() {
                const modal = document.getElementById('inactive-modal');
                if (modal) {
                    modal.remove();
                }
            };
        });
    </script>
    
    <style>
        @keyframes scale-in {
            0% {
                opacity: 0;
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .animate-scale-in {
            animation: scale-in 0.2s ease-out;
        }
    </style>
</body>
</html>
