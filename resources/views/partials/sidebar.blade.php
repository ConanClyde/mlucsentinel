<!-- Sidebar -->
<aside class="fixed left-0 top-0 h-screen w-64 bg-white dark:bg-[#1a1a1a] border-r border-[#e3e3e0] dark:border-[#3E3E3A] z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out" id="sidebar">
    <div class="flex flex-col h-full">
        <!-- Logo and Title -->
        <div class="flex items-center h-16 px-6 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
            <h1 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] leading-tight">MLUC Sentinel</h1>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <!-- Main Navigation -->
            <div class="space-y-1">
                <!-- Home -->
                <a href="{{ route('home') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('home') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                    <x-heroicon-o-home class="w-5 h-5 mr-3" />
                    Home
                </a>

                @if(in_array(Auth::user()->user_type, [App\Enums\UserType::GlobalAdministrator, App\Enums\UserType::Administrator]))
                    @php
                        $canViewDashboard = Auth::user()->hasPrivilege('view_dashboard');

                        $canViewStudents = Auth::user()->hasPrivilege('view_students');
                        $canViewStaff = Auth::user()->hasPrivilege('view_staff');
                        $canViewSecurity = Auth::user()->hasPrivilege('view_security');
                        $canViewStakeholders = Auth::user()->hasPrivilege('view_stakeholders');
                        $canViewReporters = Auth::user()->hasPrivilege('view_reporters');
                        $canViewAdministrators = Auth::user()->hasPrivilege('view_administrators');
                        $hasAnyUsersView = $canViewStudents || $canViewStaff || $canViewSecurity || $canViewStakeholders || $canViewReporters || $canViewAdministrators;

                        $canRegisterStudents = Auth::user()->hasPrivilege('register_students');
                        $canRegisterStaff = Auth::user()->hasPrivilege('register_staff');
                        $canRegisterSecurity = Auth::user()->hasPrivilege('register_security');
                        $canRegisterStakeholders = Auth::user()->hasPrivilege('register_stakeholders');
                        $canRegisterReporters = Auth::user()->hasPrivilege('register_reporters');
                        $canRegisterAdministrators = Auth::user()->hasPrivilege('register_administrators');
                        $hasAnyRegistration = $canRegisterStudents || $canRegisterStaff || $canRegisterSecurity || $canRegisterStakeholders || $canRegisterReporters || $canRegisterAdministrators;

                        $canViewVehicles = Auth::user()->hasPrivilege('view_vehicles');
                        $canViewCampusMap = Auth::user()->hasPrivilege('view_campus_map');
                    @endphp
                    <!-- Admin Navigation -->
                    <!-- Dashboard -->
                    @if($canViewDashboard)
                        <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                            <x-heroicon-o-squares-2x2 class="w-5 h-5 mr-3" />
                            Dashboard
                        </a>
                    @endif

                    <!-- Users Submenu -->
                    @if($hasAnyUsersView)
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
                                @if($canViewStudents)
                                    <a href="{{ route('admin.users.students') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.students') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                        Students
                                    </a>
                                @endif
                                @if($canViewStaff)
                                    <a href="{{ route('admin.users.staff') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.staff') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                        Staff
                                    </a>
                                @endif
                                @if($canViewSecurity)
                                    <a href="{{ route('admin.users.security') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.security') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                        Security
                                    </a>
                                @endif
                                @if($canViewReporters)
                                    <a href="{{ route('admin.users.reporters') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.reporters') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                        Reporters
                                    </a>
                                @endif
                                @if($canViewStakeholders)
                                    <a href="{{ route('admin.users.stakeholders') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.stakeholders') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                        Stakeholders
                                    </a>
                                @endif
                                @if($canViewAdministrators)
                                    <a href="{{ route('admin.users.administrators') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.users.administrators') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                        Administrators
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($hasAnyRegistration)
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
                                @if($canRegisterStudents)
                                    <a href="{{ route('admin.registration.student') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.registration.student') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                        Student
                                    </a>
                                @endif
                                @if($canRegisterStaff)
                                    <a href="{{ route('admin.registration.staff') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.registration.staff') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                        Staff
                                    </a>
                                @endif
                                @if($canRegisterSecurity)
                                    <a href="{{ route('admin.registration.security') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.registration.security') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                        Security
                                    </a>
                                @endif
                                @if($canRegisterStakeholders)
                                    <a href="{{ route('admin.registration.stakeholder') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.registration.stakeholder') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                        Stakeholder
                                    </a>
                                @endif
                                @if($canRegisterReporters)
                                    <a href="{{ route('admin.registration.reporter') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.registration.reporter') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                        Reporter
                                    </a>
                                @endif
                                @if($canRegisterAdministrators)
                                    <a href="{{ route('admin.registration.administrator') }}" class="block px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.registration.administrator') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                                        Administrator
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Pending Registrations (Global Admin only) -->
                    @if(Auth::user()->user_type === App\Enums\UserType::GlobalAdministrator)
                        <a href="{{ route('admin.pending-registrations') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.pending-registrations') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                            <x-heroicon-o-clock class="w-5 h-5 mr-3" />
                            Pending Registrations
                        </a>
                    @endif

                    @if($canViewVehicles)
                        <a href="{{ route('admin.vehicles') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.vehicles') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                            <x-heroicon-o-truck class="w-5 h-5 mr-3" />
                            Vehicles
                        </a>
                    @endif

                    @if(Auth::user()->hasPrivilege('manage_reports'))
                        <a href="{{ route('admin.reports') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.reports') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                            <x-heroicon-o-document-text class="w-5 h-5 mr-3" />
                            Reports
                        </a>
                    @endif

                    @if(Auth::user()->hasPrivilege('view_stickers'))
                        <a href="{{ route('admin.stickers') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.stickers') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                            <x-heroicon-o-tag class="w-5 h-5 mr-3" />
                            Stickers
                        </a>
                    @endif

                    <!-- Map -->
                    @if($canViewCampusMap)
                        <a href="{{ route('admin.campus-map') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.campus-map') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                            <x-heroicon-o-map class="w-5 h-5 mr-3" />
                            Campus Map
                        </a>
                    @endif

                    @if(Auth::user()->hasPrivilege('view_patrol_monitor') || Auth::user()->hasPrivilege('view_patrol_history'))
                        <!-- Patrol Monitoring -->
                        <a href="{{ route('admin.patrol-history') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.patrol-history') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                            <x-heroicon-o-shield-check class="w-5 h-5 mr-3" />
                            Patrol Monitor
                        </a>
                    @endif
                @elseif(Auth::user()->user_type === App\Enums\UserType::Reporter)
                    <!-- Reporter Navigation -->
                    <a href="{{ route('reporter.report-user') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('reporter.report-user') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 mr-3" />
                        Report User
                    </a>

                    <a href="{{ route('reporter.my-reports') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('reporter.my-reports') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-document-text class="w-5 h-5 mr-3" />
                        My Reports
                    </a>

                    <!-- Campus Map -->
                    <a href="{{ route('campus-map') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('campus-map') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-map class="w-5 h-5 mr-3" />
                        Campus Map
                    </a>
                @elseif(Auth::user()->user_type === App\Enums\UserType::Security)
                    <!-- Security Navigation -->
                    
                    <!-- Patrol Scanner -->
                    <a href="{{ route('security.patrol-scanner') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('security.patrol-scanner') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-qr-code class="w-5 h-5 mr-3" />
                        Scan Patrol Point
                    </a>

                    <!-- Patrol History -->
                    <a href="{{ route('security.patrol-history') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('security.patrol-history') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-clock class="w-5 h-5 mr-3" />
                        My Patrol History
                    </a>

                    <!-- Report User -->
                    <a href="{{ route('reporter.report-user') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('reporter.report-user') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 mr-3" />
                        Report User
                    </a>

                    <a href="{{ route('reporter.my-reports') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('reporter.my-reports') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-document-text class="w-5 h-5 mr-3" />
                        My Reports
                    </a>

                    <a href="{{ route('user.vehicles') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('user.vehicles') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-truck class="w-5 h-5 mr-3" />
                        My Vehicles
                    </a>

                    <!-- Campus Map -->
                    <a href="{{ route('campus-map') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('campus-map') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-map class="w-5 h-5 mr-3" />
                        Campus Map
                    </a>
                @elseif(in_array(Auth::user()->user_type, [App\Enums\UserType::Student, App\Enums\UserType::Staff, App\Enums\UserType::Stakeholder]))
                    <!-- User Navigation (Students, Staff, Stakeholders) -->
                    
                    <!-- My Vehicles -->
                    <a href="{{ route('user.vehicles') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('user.vehicles') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-truck class="w-5 h-5 mr-3" />
                        Vehicles
                    </a>

                    <!-- Report History -->
                    <a href="{{ route('user.reports') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('user.reports') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-document-text class="w-5 h-5 mr-3" />
                        Report History
                    </a>

                    <!-- Sticker Requests -->
                    <a href="{{ route('user.requests') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('user.requests') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-plus-circle class="w-5 h-5 mr-3" />
                        Request
                    </a>

                    <!-- Sticker History -->
                    <a href="{{ route('user.stickers.history') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('user.stickers.history') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-credit-card class="w-5 h-5 mr-3" />
                        Sticker History
                    </a>

                    <!-- Campus Map -->
                    <a href="{{ route('campus-map') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('campus-map') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
                        <x-heroicon-o-map class="w-5 h-5 mr-3" />
                        Campus Map
                    </a>
                @endif
            </div>

        </nav>

        <!-- User Info at Bottom -->
        <div class="px-4 py-4 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
            <a href="{{ route('profile') }}" class="flex items-start space-x-3 mb-3 p-3 rounded-lg transition-all duration-200 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 hover:from-blue-100 hover:to-indigo-100 dark:hover:from-blue-900/30 dark:hover:to-indigo-900/30 border border-blue-100 dark:border-blue-800/30 {{ request()->routeIs('profile') ? 'ring-2 ring-blue-500 dark:ring-blue-400' : '' }}">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-base flex-shrink-0 shadow-md" id="user-avatar" style="background-color: {{ $avatarColor }}">
                    {{ strtoupper(substr(Auth::user()->first_name ?? 'User', 0, 1)) }}
                </div>
                <div class="sidebar-user-info flex-1 min-w-0">
                    <p class="sidebar-user-name font-semibold text-sm text-[#1b1b18] dark:text-[#EDEDEC] truncate" title="{{ Auth::user()->first_name ?? 'User' }}">
                        {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                    </p>
                    <p class="sidebar-user-email text-xs text-blue-600 dark:text-blue-400 truncate font-medium">
                        @if(Auth::user()->user_type === App\Enums\UserType::GlobalAdministrator)
                            Global Administrator
                        @elseif(Auth::user()->user_type === App\Enums\UserType::Administrator && Auth::user()->administrator)
                            {{ Auth::user()->administrator->adminRole->name ?? 'Administrator' }}
                        @elseif(Auth::user()->user_type === App\Enums\UserType::Reporter && Auth::user()->reporter)
                            {{ Auth::user()->reporter->reporterRole->name ?? 'Reporter' }}
                        @elseif(Auth::user()->user_type === App\Enums\UserType::Security)
                            Security Personnel
                        @elseif(Auth::user()->user_type === App\Enums\UserType::Student)
                            Student
                        @elseif(Auth::user()->user_type === App\Enums\UserType::Staff)
                            Staff Member
                        @elseif(Auth::user()->user_type === App\Enums\UserType::Stakeholder)
                            Stakeholder
                        @else
                            {{ is_string(Auth::user()->user_type) ? ucfirst(Auth::user()->user_type) : Auth::user()->user_type->label() }}
                        @endif
                    </p>
                </div>
            </a>
            
            <!-- Settings Link -->
            <a href="{{ route('settings') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('settings') ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 'text-[#706f6c] dark:text-[#A1A09A] hover:bg-gray-100 dark:hover:bg-[#2a2a2a] hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]' }}">
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
<div class="fixed inset-0 bg-black/50 z-40 hidden lg:!hidden transition-opacity duration-300" id="sidebar-overlay"></div>
