<!-- Sidebar -->
<aside class="fixed left-0 top-0 h-full w-auto min-w-64 max-w-80 bg-white dark:bg-[#1a1a1a] border-r border-[#e3e3e0] dark:border-[#3E3E3A] z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out" id="sidebar">
    <div class="flex flex-col h-full">
        <!-- Logo and Title -->
        <div class="flex items-center h-16 px-6 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <x-heroicon-o-shield-check class="w-5 h-5 text-white" />
                </div>
                <div class="flex flex-col justify-center">
                    <h1 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] leading-tight">MLUC Sentinel</h1>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 py-6 space-y-2">
            <!-- Main Navigation -->
            <div class="space-y-1">
                <!-- Home -->
                <a href="{{ route('home') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('home') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                    <x-heroicon-o-home class="w-5 h-5 mr-3" />
                    Home
                </a>

                @if(in_array(Auth::user()->user_type, ['global_administrator', 'administrator']))
                    <!-- Admin Navigation -->
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-squares-2x2 class="w-5 h-5 mr-3" />
                        Dashboard
                    </a>

                    <!-- Users Submenu -->
                    <div class="space-y-1">
                        <button onclick="toggleSubmenu('users-submenu')" class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] cursor-pointer">
                            <div class="flex items-center">
                                <x-heroicon-o-users class="w-5 h-5 mr-3" />
                                Users
                            </div>
                            <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('admin.users.*') ? 'rotate-180' : '' }}" id="users-chevron" />
                        </button>
                        
                        <!-- Users Submenu -->
                        <div id="users-submenu" class="ml-6 space-y-1 {{ request()->routeIs('admin.users.*') ? '' : 'hidden' }}">
                            <a href="{{ route('admin.users.students') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.students') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                Students
                            </a>
                            <a href="{{ route('admin.users.staff') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.staff') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                Staff
                            </a>
                            <a href="{{ route('admin.users.security') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.security') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                Security
                            </a>
                            <a href="{{ route('admin.users.reporters') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.reporters') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                Reporters
                            </a>
                            <a href="{{ route('admin.users.stakeholders') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.stakeholders') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                Stakeholders
                            </a>
                            <a href="{{ route('admin.users.administrators') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.administrators') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                Administrators
                            </a>
                        </div>
                    </div>

                    <!-- Registration Submenu -->
                    <div class="space-y-1">
                        <button onclick="toggleSubmenu('registration-submenu')" class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC] cursor-pointer">
                            <div class="flex items-center">
                                <x-heroicon-o-user-plus class="w-5 h-5 mr-3" />
                                Registration
                            </div>
                            <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('admin.registration.*') ? 'rotate-180' : '' }}" id="registration-chevron" />
                        </button>
                        
                        <!-- Registration Submenu -->
                        <div id="registration-submenu" class="ml-6 space-y-1 {{ request()->routeIs('admin.registration.*') ? '' : 'hidden' }}">
                            <a href="{{ route('admin.registration.student') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.registration.student') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                Student
                            </a>
                            <a href="{{ route('admin.registration.staff') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.registration.staff') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                Staff
                            </a>
                            <a href="{{ route('admin.registration.security') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.registration.security') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                Security
                            </a>
                            <a href="{{ route('admin.registration.reporter') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.registration.reporter') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                Reporter
                            </a>
                            <a href="{{ route('admin.registration.stakeholder') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.registration.stakeholder') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                Stakeholder
                            </a>
                            <a href="{{ route('admin.registration.administrator') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.registration.administrator') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                Administrator
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('admin.vehicles') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.vehicles') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-truck class="w-5 h-5 mr-3" />
                        Vehicles
                    </a>

                    <a href="{{ route('admin.reports') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.reports') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-document-text class="w-5 h-5 mr-3" />
                        Reports
                    </a>

                    <a href="{{ route('admin.stickers') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.stickers') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-tag class="w-5 h-5 mr-3" />
                        Stickers
                    </a>
                @elseif(Auth::user()->user_type === 'reporter')
                    <!-- Reporter Navigation -->
                    <a href="{{ route('reporter.report-user') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('reporter.report-user') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 mr-3" />
                        Report User
                    </a>

                    <a href="{{ route('reporter.my-reports') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('reporter.my-reports') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-document-text class="w-5 h-5 mr-3" />
                        My Reports
                    </a>
                @elseif(Auth::user()->user_type === 'security')
                    <!-- Security Navigation -->
                    <a href="{{ route('reporter.report-user') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('reporter.report-user') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 mr-3" />
                        Report User
                    </a>

                    <a href="{{ route('reporter.my-reports') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('reporter.my-reports') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-document-text class="w-5 h-5 mr-3" />
                        My Reports
                    </a>

                    <a href="{{ route('reporter.my-vehicles') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('reporter.my-vehicles') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-truck class="w-5 h-5 mr-3" />
                        My Vehicles
                    </a>
                @endif
            </div>

        </nav>

        <!-- User Info at Bottom -->
        <div class="px-4 py-4 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
            <a href="{{ route('profile') }}" class="flex items-start space-x-3 mb-3 p-2 rounded-lg transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-[#2a2a2a] {{ request()->routeIs('profile') ? 'bg-blue-100 dark:bg-blue-900' : '' }}">
            <div class="w-8 h-8 ml-2 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 mt-0.5" id="user-avatar" style="background-color: {{ $avatarColor }}">
                {{ strtoupper(substr(Auth::user()->first_name ?? 'User', 0, 1)) }}
            </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] break-words" title="{{ Auth::user()->first_name ?? 'User' }}">
                        {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                    </p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] break-words leading-tight" title="{{ Auth::user()->email ?? 'user@example.com' }}">
                        {{ Auth::user()->email ?? 'user@example.com' }}
                    </p>
                </div>
            </a>
            
            <!-- Settings Link -->
            <a href="#" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]">
                <x-heroicon-o-cog-6-tooth class="w-5 h-5 mr-3" />
                Settings
            </a>
            
            <!-- Logout Button -->
            <button onclick="logout()" class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 text-left">
                <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 mr-3" />
                Logout
            </button>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div class="fixed inset-0 bg-black/50 z-30 lg:hidden hidden" id="sidebar-overlay"></div>
