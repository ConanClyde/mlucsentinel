<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MLUC Sentinel</title>
    
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
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 border-b border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#1b1b18] backdrop-blur-sm bg-white/95 dark:bg-[#1b1b18]/95">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="flex justify-between items-center h-16 relative z-10">
                <div class="flex items-center">
                    <a href="#" id="scroll-to-top" class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] hover:text-[#3b82f6] dark:hover:text-[#60a5fa] transition-colors duration-200 cursor-pointer">MLUC Sentinel</a>
                </div>
                
                {{-- Desktop Menu --}}
                <div class="hidden md:flex items-center gap-4">
                    {{-- Dark/Light Mode Toggle --}}
                    <button id="theme-toggle" class="btn btn-secondary !px-2 !py-2 aspect-square">
                        {{-- Sun Icon (Light Mode) --}}
                        <x-heroicon-s-sun id="sun-icon" class="w-4 h-4 hidden dark:block" />
                        {{-- Moon Icon (Dark Mode) --}}
                        <x-heroicon-s-moon id="moon-icon" class="w-4 h-4 block dark:hidden" />
                    </button>
                    
                    <a href="{{ route('login') }}" class="btn btn-secondary">Sign In</a>
                </div>

                {{-- Mobile Menu Button --}}
                <button id="mobile-menu-toggle" class="btn btn-secondary !px-2 !py-2 aspect-square !inline-flex md:!hidden">
                    {{-- Hamburger Icon --}}
                    <svg id="hamburger-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    {{-- Close Icon (hidden by default) --}}
                    <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Mobile Menu Dropdown - Below navbar, above content --}}
            <div id="mobile-menu" class="absolute top-full left-0 right-0 hidden md:hidden bg-white dark:bg-[#1b1b18] border-b border-[#e3e3e0] dark:border-[#3E3E3A] shadow-lg z-40 overflow-hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-4 space-y-3 pt-2">
                    {{-- Dark/Light Mode Toggle (Mobile) --}}
                    <button id="theme-toggle-mobile" class="w-full btn btn-secondary justify-start">
                        <span id="theme-text-mobile">Dark Mode</span>
                    </button>
                    
                    <a href="{{ route('login') }}" class="block w-full btn btn-secondary text-center">Sign In</a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Mobile Menu Overlay --}}
    <div id="mobile-menu-overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden transition-opacity duration-300"></div>

    <!-- Hero Section -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32">
        <div class="text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 bg-[#1b1b18] dark:bg-white text-white dark:text-[#1b1b18] px-4 py-2 rounded-full text-sm font-medium mb-8">
                <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                Digital Parking & Reporting Management System
            </div>
            
            <!-- Main Heading -->
            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-6 leading-tight" style="word-break: break-word;">
                Campus parking that<span class="sm:hidden"> </span><br class="hidden sm:block">works like a <span class="text-blue-600 dark:text-blue-500">Sentinel</span>
            </h1>
            
            <!-- Subheading -->
            <p class="text-lg md:text-xl text-[#706f6c] dark:text-[#A1A09A] mb-10 max-w-2xl mx-auto">
                Comprehensive digital platform for parking permits, violation reporting with QR technology, evidence-based enforcement, and real-time security monitoring.
            </p>
            
            <!-- CTA Buttons -->
            <div class="flex justify-center gap-4 flex-wrap mb-6">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-[#1b1b18] dark:bg-white text-white dark:text-[#1b1b18] font-semibold text-base px-8 py-3.5 rounded-lg hover:bg-[#2a2a2a] dark:hover:bg-gray-100 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Get Started
                </a>
            </div>
            
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Join MLUC Sentinel as a student, staff member, stakeholder, or security personnel and start managing your campus parking digitally</p>
            
            <!-- Real-Time Statistics Showcase -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16 max-w-4xl mx-auto">
                <div class="text-center p-6 bg-white dark:bg-[#1b1b18] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2" id="stat-users">
                        {{ \App\Models\User::count() }}
                    </div>
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Registered Users</div>
                </div>
                <div class="text-center p-6 bg-white dark:bg-[#1b1b18] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2" id="stat-vehicles">
                        {{ \App\Models\Vehicle::count() }}
                    </div>
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Vehicles Registered</div>
                </div>
                <div class="text-center p-6 bg-white dark:bg-[#1b1b18] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2" id="stat-violations">
                        {{ \App\Models\Report::where('status', 'approved')->count() }}
                    </div>
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Violations Approved</div>
                </div>
            </div>
        </div>

        <!-- Why MLUC Sentinel Section -->
        <div class="mt-32 max-w-5xl mx-auto">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-blue-600 dark:text-blue-500 mb-4 uppercase tracking-wide">Benefits</p>
                <h2 class="text-4xl md:text-5xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">Why MLUC Sentinel?</h2>
                <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] max-w-3xl mx-auto">
                    Traditional campus management relies on manual processes. MLUC Sentinel transforms operations with a centralized digital platform.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-8 mt-12">
                <div class="bg-white dark:bg-[#1b1b18] p-6 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start mb-2">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Digital Parking Permit System</h3>
                    </div>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Eliminate paper-based parking permits with digital vehicle registration, automatic QR-enabled sticker generation, and color-coded identification—streamlining campus parking management from registration to enforcement.</p>
                </div>
                <div class="bg-white dark:bg-[#1b1b18] p-6 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start mb-2">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Smart Violation Reporting System</h3>
                    </div>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Report violations instantly by scanning QR codes on parking stickers. Upload photo evidence, pin exact locations on the interactive campus map, and automatically route reports to the appropriate administrators—transforming violation management from manual to digital.</p>
                </div>
                <div class="bg-white dark:bg-[#1b1b18] p-6 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start mb-2">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Security Patrol Tracking</h3>
                    </div>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Monitor security patrols with QR-based check-ins at strategic locations, track coverage, and ensure comprehensive campus security.</p>
                </div>
                <div class="bg-white dark:bg-[#1b1b18] p-6 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start mb-2">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Transparent Management</h3>
                    </div>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Digital tracking ensures consistent enforcement, automatic admin assignment based on violator type, and complete audit trails.</p>
                </div>
            </div>
        </div>

        <!-- Key Features Section -->
        <div class="mt-32 max-w-5xl mx-auto">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-blue-600 dark:text-blue-500 mb-4 uppercase tracking-wide">Features</p>
                <h2 class="text-4xl md:text-5xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">Complete Campus Management</h2>
                <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] max-w-3xl mx-auto">
                    Everything you need to manage parking, violations, and security in one comprehensive platform.
                </p>
            </div>

            <!-- For Students, Staff & Stakeholders -->
            <div class="bg-white dark:bg-[#1b1b18] p-8 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] mb-8">
                <div class="flex flex-col md:flex-row items-center md:items-center text-center md:text-left mb-6">
                    <div class="w-12 h-12 flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-xl flex items-center justify-center mb-4 md:mb-0 md:mr-4">
                        <x-heroicon-s-user-group class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">For Students, Staff & Stakeholders</h3>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="text-center md:text-left">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Easy Account Creation</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Create your MLUC Sentinel account quickly and easily. Join the digital parking management system and access all campus parking services online.</p>
                    </div>
                    <div class="text-center md:text-left">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Color-Coded Parking Stickers</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Receive color-coded parking stickers based on your user type and plate number for instant identification and authorized parking access across campus.</p>
                    </div>
                    <div class="text-center md:text-left">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">QR-Enabled Verification</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Each parking sticker includes a unique QR code linking to your vehicle registration for quick verification and streamlined violation reporting.</p>
                    </div>
                    <div class="text-center md:text-left">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Digital Parking Management</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Once registered, visit campus administration to register your vehicle, receive your parking permit sticker, and enjoy hassle-free digital campus parking management.</p>
                    </div>
                </div>
            </div>

            <!-- For Security & Reporters -->
            <div class="bg-white dark:bg-[#1b1b18] p-8 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] mb-8">
                <div class="flex flex-col md:flex-row items-center md:items-center text-center md:text-left mb-6">
                    <div class="w-12 h-12 flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-xl flex items-center justify-center mb-4 md:mb-0 md:mr-4">
                        <x-heroicon-s-shield-exclamation class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">For Security Personnel & Reporters</h3>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="text-center md:text-left md:text-center">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">QR-Based Violation Reporting</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Scan parking sticker QR codes with your mobile device to instantly access vehicle information and file violation reports—no manual data entry required.</p>
                    </div>
                    <div class="text-center md:text-left md:text-center">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Evidence Upload System</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Capture and upload photo evidence directly from your device, ensuring every violation report is backed by visual documentation for accountability.</p>
                    </div>
                    <div class="text-center md:text-left md:text-center">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Interactive Campus Map</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Pin exact violation locations on an interactive campus map with precise coordinates, providing administrators with clear location context for enforcement.</p>
                    </div>
                    <div class="text-center md:text-left md:text-center">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Digital Violation Records</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">All reports are automatically stored with complete details, creating a comprehensive digital record system that replaces manual paperwork.</p>
                    </div>
                    <div class="text-center md:text-left md:text-center">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Patrol Check-In System</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Security personnel scan location QR codes at strategic checkpoints to log patrol activities, track coverage areas, and maintain comprehensive patrol records.</p>
                    </div>
                    <div class="text-center md:text-left md:text-center">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Mobile-Optimized Interface</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Access the reporting system from any mobile device with a responsive interface designed for on-the-go enforcement and patrol activities.</p>
                    </div>
                </div>
            </div>

            <!-- For Administration -->
            <div class="bg-white dark:bg-[#1b1b18] p-8 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                <div class="flex flex-col md:flex-row items-center md:items-center text-center md:text-left mb-6">
                    <div class="w-12 h-12 flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-xl flex items-center justify-center mb-4 md:mb-0 md:mr-4">
                        <x-heroicon-s-shield-check class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">For Administration</h3>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="text-center md:text-left">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Parking Operations Dashboard</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Comprehensive dashboard with real-time analytics for parking permit registrations, violation reports, patrol activities, and overall parking facility utilization.</p>
                    </div>
                    <div class="text-center md:text-left">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Violation Report Management</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Review, approve, or reject violation reports with complete evidence trails, manage enforcement actions, and maintain comprehensive violation records.</p>
                    </div>
                    <div class="text-center md:text-left">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Automated Report Assignment</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">System automatically routes violation reports to appropriate administrators—SAS Admin for student violations, Chancellor and Security Admins for others.</p>
                    </div>
                    <div class="text-center md:text-left">
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Analytics & Reporting</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Access detailed analytics, generate comprehensive reports, and export data for institutional records and compliance documentation.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- How It Works Section -->
        <div class="mt-32 max-w-5xl mx-auto">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-blue-600 dark:text-blue-500 mb-4 uppercase tracking-wide">Process</p>
                <h2 class="text-4xl md:text-5xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">How It Works</h2>
                <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] max-w-3xl mx-auto">
                    Simple, streamlined process from registration to enforcement.
                </p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center flex flex-col">
                    <div class="w-16 h-16 bg-[#1b1b18] dark:bg-[#eeeeec] rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white dark:text-[#1b1b18]">1</span>
                    </div>
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Create Your Account</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A] text-sm">Click "Get Started" to create your MLUC Sentinel account. Provide your basic information and join the digital parking management system.</p>
                </div>
                <div class="text-center flex flex-col">
                    <div class="w-16 h-16 bg-[#1b1b18] dark:bg-[#eeeeec] rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white dark:text-[#1b1b18]">2</span>
                    </div>
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Register Your Vehicle</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A] text-sm">Visit campus administration to register your vehicle and receive your color-coded QR-enabled parking sticker with unique identification.</p>
                </div>
                <div class="text-center flex flex-col">
                    <div class="w-16 h-16 bg-[#1b1b18] dark:bg-[#eeeeec] rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white dark:text-[#1b1b18]">3</span>
                    </div>
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Report Violations</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A] text-sm">Scan parking sticker QR codes to instantly file violation reports. Upload photo evidence, select violation types, and pin exact locations on the interactive campus map.</p>
                </div>
                <div class="text-center flex flex-col">
                    <div class="w-16 h-16 bg-[#1b1b18] dark:bg-[#eeeeec] rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white dark:text-[#1b1b18]">4</span>
                    </div>
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Manage & Monitor</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A] text-sm">Administrators review and process violation reports, track parking permit status, monitor security patrol coverage, and access real-time analytics for complete parking and reporting operations management.</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-32 max-w-5xl mx-auto">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-blue-600 dark:text-blue-500 mb-4 uppercase tracking-wide">Support</p>
                <h2 class="text-4xl md:text-5xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">Frequently Asked Questions</h2>
                <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] max-w-3xl mx-auto">
                    Everything you need to know about MLUC Sentinel
                </p>
            </div>
            
            <div class="max-w-3xl mx-auto space-y-4">
                <details class="bg-white dark:bg-[#1b1b18] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow">
                    <summary class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] cursor-pointer">How do I get access to MLUC Sentinel?</summary>
                    <p class="mt-4 text-[#706f6c] dark:text-[#A1A09A]">Click "Get Started" to submit your registration. Select your user type (Student, Staff, Stakeholder, or Security) and optionally provide vehicle information. Your registration will be reviewed by an administrator before your account is activated.</p>
                </details>
                
                <details class="bg-white dark:bg-[#1b1b18] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow">
                    <summary class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] cursor-pointer">How do I register my vehicle?</summary>
                    <p class="mt-4 text-[#706f6c] dark:text-[#A1A09A]">Vehicle registration is done by campus administrators on your behalf. Visit the campus administration office with your vehicle documents and they will register your vehicle and issue your parking permit sticker.</p>
                </details>
                
                <details class="bg-white dark:bg-[#1b1b18] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow">
                    <summary class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] cursor-pointer">What if I lose my parking sticker?</summary>
                    <p class="mt-4 text-[#706f6c] dark:text-[#A1A09A]">Go to marketing administrators to report a lost sticker. They can use the "Request New Sticker" feature in the system to create a replacement request for your vehicle. A sticker fee will apply, and a new sticker with a unique QR code will be generated to prevent unauthorized use of the lost sticker.</p>
                </details>
                
                <details class="bg-white dark:bg-[#1b1b18] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow">
                    <summary class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] cursor-pointer">How do I report a parking violation?</summary>
                    <p class="mt-4 text-[#706f6c] dark:text-[#A1A09A]">Security personnel and authorized reporters can scan the QR code on the vehicle's parking sticker using the mobile app, upload photo evidence, and pin the location on the campus map. The system will automatically route the report to the appropriate administrator.</p>
                </details>
                
                <details class="bg-white dark:bg-[#1b1b18] rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] p-6 hover:shadow-md transition-shadow">
                    <summary class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] cursor-pointer">Can I view my violation history?</summary>
                    <p class="mt-4 text-[#706f6c] dark:text-[#A1A09A]">Students, staff, and stakeholders do not have direct system access. If you receive a violation notification via email, you can contact the administration office to inquire about your violation history and status.</p>
                </details>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="mt-32 text-center">
            <p class="text-lg text-[#706f6c] dark:text-[#A1A09A]">
                For any inquiries, please contact the development team at: <a href="mailto:ademesa.dev@gmail.com" class="text-[#1b1b18] dark:text-[#EDEDEC] font-medium hover:underline">ademesa.dev@gmail.com</a>
            </p>
        </div>

        <!-- University Info -->
        <div class="mt-16 text-center">
            <h3 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">DON MARIANO MARCOS MEMORIAL STATE UNIVERSITY</h3>
            <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] mb-1">Mid La Union Campus</p>
            <p class="text-lg text-[#706f6c] dark:text-[#A1A09A]">College of Information Technology</p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <p class="text-center text-sm text-[#706f6c] dark:text-[#A1A09A] italic mb-2">
                *MLUC Sentinel - A Digital Parking System. A Capstone Project by Dulay, S.A.C.; De Mesa, A.P.; Marzan, J.V.R.; Paz, D.G.F.; Saltivan, G.A.A. (2025).*
            </p>
            <p class="text-center text-[#706f6c] dark:text-[#A1A09A]">
                &copy; {{ date('Y') }} MLUC Sentinel. All rights reserved.
            </p>
        </div>
    </footer>

    <script>
        // Dark/Light Mode Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const html = document.documentElement;
            
            // Check for saved theme preference or default to light mode
            const currentTheme = localStorage.getItem('theme') || 'light';
            html.classList.toggle('dark', currentTheme === 'dark');
            
            // Update theme icons and text based on current theme
            function updateThemeUI() {
                const isDark = html.classList.contains('dark');
                const sunIcons = document.querySelectorAll('#sun-icon');
                const moonIcons = document.querySelectorAll('#moon-icon');
                const themeTextMobile = document.getElementById('theme-text-mobile');
                
                sunIcons.forEach(icon => {
                    icon.classList.toggle('hidden', !isDark);
                    icon.classList.toggle('block', isDark);
                });
                
                moonIcons.forEach(icon => {
                    icon.classList.toggle('hidden', isDark);
                    icon.classList.toggle('block', !isDark);
                });
                
                if (themeTextMobile) {
                    themeTextMobile.textContent = isDark ? 'Light Mode' : 'Dark Mode';
                }
            }
            
            // Initial UI update
            updateThemeUI();
            
            // Desktop theme toggle
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const isDark = html.classList.contains('dark');
                    const newTheme = isDark ? 'light' : 'dark';
                    
                    html.classList.toggle('dark');
                    localStorage.setItem('theme', newTheme);
                    updateThemeUI();
                });
            }
            
            // Mobile theme toggle
            const themeToggleMobile = document.getElementById('theme-toggle-mobile');
            if (themeToggleMobile) {
                themeToggleMobile.addEventListener('click', function() {
                    const isDark = html.classList.contains('dark');
                    const newTheme = isDark ? 'light' : 'dark';
                    
                    html.classList.toggle('dark');
                    localStorage.setItem('theme', newTheme);
                    updateThemeUI();
                });
            }

            // Mobile Menu Toggle
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
            const hamburgerIcon = document.getElementById('hamburger-icon');
            const closeIcon = document.getElementById('close-icon');
            
            // Function to close menu
            function closeMobileMenu() {
                if (!mobileMenu) return;
                mobileMenu.style.maxHeight = '0';
                
                // Hide overlay
                if (mobileMenuOverlay) {
                    mobileMenuOverlay.classList.add('hidden');
                }
                
                // Unlock body scroll
                document.body.style.overflow = 'auto';
                
                setTimeout(() => {
                    mobileMenu.classList.add('hidden');
                }, 300);
                
                if (hamburgerIcon) hamburgerIcon.classList.remove('hidden');
                if (closeIcon) closeIcon.classList.add('hidden');
            }
            
            // Function to open menu
            function openMobileMenu() {
                if (!mobileMenu) return;
                // Remove hidden class first
                mobileMenu.classList.remove('hidden');
                
                // Show overlay
                if (mobileMenuOverlay) {
                    mobileMenuOverlay.classList.remove('hidden');
                }
                
                // Lock body scroll
                document.body.style.overflow = 'hidden';
                
                // Get the actual height
                const menuHeight = mobileMenu.scrollHeight;
                // Animate to full height (slides down from below navbar)
                requestAnimationFrame(() => {
                    setTimeout(() => {
                        mobileMenu.style.maxHeight = menuHeight + 'px';
                    }, 10);
                });
                
                if (hamburgerIcon) hamburgerIcon.classList.add('hidden');
                if (closeIcon) closeIcon.classList.remove('hidden');
            }
            
            if (mobileMenuToggle && mobileMenu) {
                // Set initial hidden state and transition
                mobileMenu.style.maxHeight = '0';
                mobileMenu.style.overflow = 'hidden';
                mobileMenu.style.transition = 'max-height 0.3s ease-in-out';
                
                mobileMenuToggle.addEventListener('click', function() {
                    const isHidden = mobileMenu.classList.contains('hidden');
                    
                    if (isHidden) {
                        openMobileMenu();
                    } else {
                        closeMobileMenu();
                    }
                });
                
                // Close menu when clicking overlay or outside
                if (mobileMenuOverlay) {
                    mobileMenuOverlay.addEventListener('click', function() {
                        closeMobileMenu();
                    });
                }
                
                // Close menu when clicking outside
                document.addEventListener('click', function(event) {
                    const isClickInsideMenu = mobileMenu.contains(event.target);
                    const isClickOnToggle = mobileMenuToggle.contains(event.target);
                    const isClickOnOverlay = mobileMenuOverlay && mobileMenuOverlay.contains(event.target);
                    
                    if (!isClickInsideMenu && !isClickOnToggle && !isClickOnOverlay && !mobileMenu.classList.contains('hidden')) {
                        closeMobileMenu();
                    }
                });
            }

            // Smooth scroll to top functionality
            const scrollToTopButton = document.getElementById('scroll-to-top');
            if (scrollToTopButton) {
                scrollToTopButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                        closeMobileMenu();
                    }
                });
            }
            
            // Real-time statistics updates
            if (window.Echo) {
                // Listen for new users
                window.Echo.channel('users')
                    .listen('.user.created', (event) => {
                        const statElement = document.getElementById('stat-users');
                        if (statElement) {
                            const currentCount = parseInt(statElement.textContent);
                            statElement.textContent = currentCount + 1;
                        }
                    });
                
                // Listen for new vehicles
                window.Echo.channel('vehicles')
                    .listen('.vehicle.created', (event) => {
                        const statElement = document.getElementById('stat-vehicles');
                        if (statElement) {
                            const currentCount = parseInt(statElement.textContent);
                            statElement.textContent = currentCount + 1;
                        }
                    });
                
                // Listen for approved violations
                window.Echo.channel('reports')
                    .listen('.report.status.updated', (event) => {
                        if (event.report && event.report.status === 'approved') {
                            const statElement = document.getElementById('stat-violations');
                            if (statElement) {
                                const currentCount = parseInt(statElement.textContent);
                                statElement.textContent = currentCount + 1;
                            }
                        }
                    });
            }
        });
    </script>
</body>
</html>
