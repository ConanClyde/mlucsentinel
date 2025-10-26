<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MLUC Sentinel</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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
    <nav class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#1b1b18]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">MLUC Sentinel</h1>
                </div>
                <div class="flex items-center gap-4">
                    {{-- Dark/Light Mode Toggle --}}
                    <button id="theme-toggle" class="btn btn-secondary !px-2 !py-2 aspect-square">
                        {{-- Sun Icon (Light Mode) --}}
                        <x-heroicon-s-sun id="sun-icon" class="w-4 h-4 hidden dark:block" />
                        {{-- Moon Icon (Dark Mode) --}}
                        <x-heroicon-s-moon id="moon-icon" class="w-4 h-4 block dark:hidden" />
                    </button>
                    
                    <a href="{{ route('login') }}" class="btn btn-secondary">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center">
            <h1 class="text-5xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                MLUC Sentinel
            </h1>
            <h2 class="text-3xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">
                A Digital Parking Management System
            </h2>
            <p class="text-2xl text-[#706f6c] dark:text-[#A1A09A] mb-4 max-w-3xl mx-auto font-medium">
                Streamline Campus Parking. Enhance Safety. Simplify Your Journey.
            </p>
            <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] mb-8 max-w-3xl mx-auto">
                Tired of circling for a parking spot? Dealing with congestion and unclear rules? MLUC Sentinel is the smart, digital solution for Don Mariano Marcos Memorial State University - Mid La Union Campus, transforming manual parking chaos into a seamless, efficient, and secure experience for everyone.
            </p>
            <div class="flex justify-center gap-4 flex-wrap">
                <a href="{{ route('login') }}" class="btn btn-primary text-lg px-8 py-3">
                    <x-heroicon-s-rocket-launch class="w-5 h-5 inline-block mr-2" />
                    Experience MLUC Sentinel
                </a>
                <a href="#research" class="btn btn-secondary text-lg px-8 py-3">
                    View Our Research
                </a>
            </div>
        </div>

        <!-- Why MLUC Sentinel Section -->
        <div class="mt-32">
            <h2 class="text-4xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-6 text-center">Why MLUC Sentinel?</h2>
            <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] mb-8 max-w-4xl mx-auto text-center">
                The current manual parking system at DMMMSU-MLUC leads to inefficiency, congestion, and security concerns. MLUC Sentinel addresses these challenges head-on with a centralized, data-driven platform.
            </p>
            
            <div class="grid md:grid-cols-2 gap-6 max-w-5xl mx-auto mt-12">
                <div class="bg-white dark:bg-[#1b1b18] p-6 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <div class="flex items-start mb-2">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-green-600 dark:text-green-400 mr-2 mt-0.5 flex-shrink-0" />
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">End Manual Hassles</h3>
                    </div>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Say goodbye to paper-based permits, fragmented records, and delayed reporting.</p>
                </div>
                <div class="bg-white dark:bg-[#1b1b18] p-6 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <div class="flex items-start mb-2">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-green-600 dark:text-green-400 mr-2 mt-0.5 flex-shrink-0" />
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Reduce Congestion</h3>
                    </div>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Real-time data and smart management minimize traffic buildup and search times.</p>
                </div>
                <div class="bg-white dark:bg-[#1b1b18] p-6 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <div class="flex items-start mb-2">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-green-600 dark:text-green-400 mr-2 mt-0.5 flex-shrink-0" />
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Enhance Campus Safety</h3>
                    </div>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Proactive monitoring and clear regulations create a safer environment for pedestrians and drivers.</p>
                </div>
                <div class="bg-white dark:bg-[#1b1b18] p-6 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <div class="flex items-start mb-2">
                        <x-heroicon-s-check-circle class="w-5 h-5 text-green-600 dark:text-green-400 mr-2 mt-0.5 flex-shrink-0" />
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Ensure Fair Enforcement</h3>
                    </div>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Digital tracking guarantees consistent and transparent application of parking rules.</p>
                </div>
            </div>
        </div>

        <!-- Key Features Section -->
        <div class="mt-32">
            <h2 class="text-4xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-6 text-center">Key Features</h2>
            <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] mb-12 max-w-4xl mx-auto text-center">
                MLUC Sentinel integrates all aspects of campus parking into one intuitive system.
            </p>

            <!-- For Students, Faculty & Staff -->
            <div class="bg-white dark:bg-[#1b1b18] p-8 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] mb-8">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-[#1b1b18] dark:bg-[#eeeeec] rounded-lg flex items-center justify-center mr-3">
                        <x-heroicon-s-user-group class="w-5 h-5 text-white dark:text-[#1b1b18]" />
                    </div>
                    <h3 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">For Students, Faculty & Staff</h3>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Digital Permits</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Apply for and receive your parking permit instantly online—no more long lines.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Real-Time Availability</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Check live parking space availability on your phone before you even arrive on campus.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">User-Friendly Portal</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">A simple interface for managing your vehicle registration and viewing your parking status.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Quick Violation Checks</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Easily view and understand any parking citations.</p>
                    </div>
                </div>
            </div>

            <!-- For Security & Administration -->
            <div class="bg-white dark:bg-[#1b1b18] p-8 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-[#1b1b18] dark:bg-[#eeeeec] rounded-lg flex items-center justify-center mr-3">
                        <x-heroicon-s-shield-check class="w-5 h-5 text-white dark:text-[#1b1b18]" />
                    </div>
                    <h3 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">For Security & Administration</h3>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Centralized Dashboard</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Get a complete, real-time overview of campus parking operations.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Digital Enforcement</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Use mobile devices to verify permits, issue digital citations, and log violations instantly.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Automated Access Control</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Integrate with RFID or license plate recognition for automated gate entry and exit.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Powerful Analytics</h4>
                        <p class="text-[#706f6c] dark:text-[#A1A09A]">Make informed decisions with detailed reports on usage patterns, violation trends, and space utilization.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- How It Works Section -->
        <div class="mt-32">
            <h2 class="text-4xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-6 text-center">How It Works</h2>
            <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] mb-12 max-w-4xl mx-auto text-center">
                MLUC Sentinel is built on a robust and user-centered process.
            </p>
            
            <div class="grid md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-[#1b1b18] dark:bg-[#eeeeec] rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white dark:text-[#1b1b18]">1</span>
                    </div>
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Register & Apply</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Users register their vehicle and apply for a permit through the online portal.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-[#1b1b18] dark:bg-[#eeeeec] rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white dark:text-[#1b1b18]">2</span>
                    </div>
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Get Digital Permit</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Once approved, a digital permit is issued and linked to the vehicle.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-[#1b1b18] dark:bg-[#eeeeec] rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white dark:text-[#1b1b18]">3</span>
                    </div>
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Park with Ease</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Check real-time availability, find a spot quickly, and park.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-[#1b1b18] dark:bg-[#eeeeec] rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white dark:text-[#1b1b18]">4</span>
                    </div>
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Manage & Enforce</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Security monitors compliance digitally, ensuring a fair and orderly system for all.</p>
                </div>
            </div>
        </div>

        <!-- Methodology Section -->
        <div class="mt-32" id="research">
            <h2 class="text-4xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-6 text-center">Our Methodology</h2>
            <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] mb-8 max-w-4xl mx-auto text-center">
                This system was developed as a Capstone Project by BS Information Technology students, following a rigorous and iterative development process:
            </p>
            
            <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <div class="bg-white dark:bg-[#1b1b18] p-6 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">In-Depth Research</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">We conducted interviews and focus group discussions with key campus stakeholders to identify real needs and challenges.</p>
                </div>
                <div class="bg-white dark:bg-[#1b1b18] p-6 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Iterative Development</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">Built using the Spiral Model (SDLC), ensuring risks were managed and user feedback was incorporated at every cycle.</p>
                </div>
                <div class="bg-white dark:bg-[#1b1b18] p-6 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Proven Usability</h3>
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">The system's usability was formally evaluated and validated using the standardized System Usability Scale (SUS).</p>
                </div>
            </div>
        </div>

        <!-- Designed for DMMMSU Section -->
        <div class="mt-32 text-center">
            <h2 class="text-4xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">Designed for DMMMSU-MLUC</h2>
            <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] max-w-4xl mx-auto">
                MLUC Sentinel isn't just another app; it's a custom-built solution designed specifically for the unique needs and growth of our campus. It represents a strategic shift from passive rule enforcement to proactive, smart campus mobility management.
            </p>
        </div>

        <!-- CTA Section -->
        <div class="mt-32 bg-white dark:bg-[#1b1b18] p-12 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] text-center">
            <h2 class="text-4xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-6">Get Started Today!</h2>
            <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] mb-8 max-w-3xl mx-auto">
                Join us in moving DMMMSU-MLUC towards a smarter, more modern campus infrastructure.
            </p>
            <div class="flex justify-center gap-4 flex-wrap mb-8">
                <a href="{{ route('login') }}" class="btn btn-primary text-lg px-8 py-3">
                    Access the System Here
                </a>
                <a href="#research" class="btn btn-secondary text-lg px-8 py-3">
                    Download the Full Research Paper
                </a>
            </div>
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
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
