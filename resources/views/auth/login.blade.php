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

                <!-- Sign Up Link -->
                <div class="text-center text-sm">
                    <span class="text-[#706f6c] dark:text-[#A1A09A]">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="ml-1 text-[#1b1b18] dark:text-[#EDEDEC] font-medium hover:underline">
                        Sign up
                    </a>
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
        });
    </script>
</body>
</html>
