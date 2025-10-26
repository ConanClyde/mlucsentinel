@php
    $avatarColor = '#3B82F6'; // Default color
    if (auth()->check()) {
        $colors = [
            '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', 
            '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'
        ];
        
        // Use only the first letter for consistent color
        $firstLetter = strtoupper(substr(auth()->user()->first_name, 0, 1));
        $hash = ord($firstLetter);
        $avatarColor = $colors[$hash % count($colors)];
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MLUC Sentinel')</title>
    
    <!-- Google Fonts - Inter -->
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
<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Include Sidebar -->
    @include('partials.sidebar')

    <!-- Main Content Area -->
    <div class="lg:ml-64">
        <!-- Top Navigation Bar -->
        <header class="sticky top-0 z-50 bg-white h-16 dark:bg-[#1a1a1a] border-b border-[#e3e3e0] dark:border-[#3E3E3A] px-4 py-3 lg:px-6">
            <div class="flex items-center justify-between h-full">
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="lg:hidden p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#2a2a2a]">
                    <x-heroicon-o-bars-3 class="w-6 h-6" />
                </button>

                <!-- Page Title -->
                <div class="flex-1 lg:flex-none">
                    <h1 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">@yield('page-title', 'Dashboard')</h1>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center gap-3">
                    <!-- Notifications Button -->
                    <div class="relative">
                        <button id="notifications-button" class="btn btn-secondary !px-2 !py-2 aspect-square relative">
                            <x-heroicon-o-bell class="w-4 h-4" />
                            <!-- Notification Badge -->
                            <span id="notification-badge" class="hidden absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">0</span>
                        </button>
                        
                        <!-- Notifications Dropdown -->
                        <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-[#1a1a1a] rounded-lg shadow-lg border border-[#e3e3e0] dark:border-[#3E3E3A] z-50">
                            <!-- Header -->
                            <div class="flex items-center justify-between p-4 border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                                <h3 class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Notifications</h3>
                                <div class="flex items-center gap-2">
                                    <button id="mark-all-read" class="text-xs text-[#706f6c] dark:text-[#A1A09A] hover:underline">Mark all read</button>
                                    <button id="clear-all" class="text-xs text-[#706f6c] dark:text-[#A1A09A] hover:underline">Clear all</button>
                                </div>
                            </div>
                            
                            <!-- Notifications List -->
                            <div id="notifications-list" class="max-h-96 overflow-y-auto">
                                <!-- Empty State -->
                                <div id="empty-notifications" class="p-8 text-center">
                                    <x-heroicon-o-bell-slash class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-2" />
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No notifications</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dark/Light Mode Toggle -->
                    <button id="theme-toggle" class="btn btn-secondary !px-2 !py-2 aspect-square">
                        <!-- Sun Icon (Light Mode) -->
                        <x-heroicon-s-sun id="sun-icon" class="w-4 h-4 hidden dark:block" />
                        <!-- Moon Icon (Dark Mode) -->
                        <x-heroicon-s-moon id="moon-icon" class="w-4 h-4 block dark:hidden" />
                    </button>

                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="p-4 lg:p-6 bg-white dark:bg-[#1a1a1a] min-h-screen">
            @yield('content')
        </main>
    </div>

    <script>
        // Dark/Light Mode Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const html = document.documentElement;
            
            // Theme toggle functionality
            themeToggle.addEventListener('click', function() {
                const isDark = html.classList.contains('dark');
                const newTheme = isDark ? 'light' : 'dark';
                
                html.classList.toggle('dark');
                localStorage.setItem('theme', newTheme);
                
                // Update charts if they exist
                if (typeof updateChartsForTheme === 'function') {
                    setTimeout(updateChartsForTheme, 100);
                }
            });

            // Mobile sidebar toggle
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            mobileMenuButton.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            });

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });

            // Notifications dropdown toggle
            const notificationsButton = document.getElementById('notifications-button');
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            
            notificationsButton.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationsDropdown.classList.toggle('hidden');
                if (!notificationsDropdown.classList.contains('hidden')) {
                    loadNotifications();
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!notificationsDropdown.contains(e.target) && !notificationsButton.contains(e.target)) {
                    notificationsDropdown.classList.add('hidden');
                }
            });

            // Load notifications from API
            function loadNotifications() {
                fetch('/notifications')
                    .then(response => response.json())
                    .then(data => {
                        renderNotifications(data.notifications);
                        updateNotificationBadge(data.unread_count);
                    })
                    .catch(error => console.error('Error loading notifications:', error));
            }

            // Render notifications
            function renderNotifications(notifications) {
                const list = document.getElementById('notifications-list');
                
                if (notifications.length === 0) {
                    list.innerHTML = `
                        <div id="empty-notifications" class="p-8 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No notifications</p>
                        </div>
                    `;
                    return;
                }

                list.innerHTML = notifications.map(notif => `
                    <div class="notification-item ${!notif.is_read ? 'unread bg-blue-50 dark:bg-blue-900/10' : ''} p-4 border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615] cursor-pointer" 
                         data-id="${notif.id}" 
                         data-url="${notif.data?.url || ''}" 
                         data-admin-id="${notif.data?.administrator_id || ''}"
                         data-reporter-id="${notif.data?.reporter_id || ''}"
                         data-action="${notif.data?.action || ''}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${notif.title}</h4>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">${notif.message}</p>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">${formatTime(notif.created_at)}</p>
                            </div>
                            ${!notif.is_read ? '<div class="w-2 h-2 bg-blue-500 rounded-full ml-2 mt-1"></div>' : ''}
                        </div>
                    </div>
                `).join('');

                // Add click handlers
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const id = this.dataset.id;
                        const url = this.dataset.url;
                        const adminId = this.dataset.adminId;
                        const reporterId = this.dataset.reporterId;
                        const action = this.dataset.action;
                        
                        // Mark as read
                        markAsRead(id);
                        
                        // Navigate and open modal if applicable
                        handleNotificationClick(url, adminId, reporterId, action);
                    });
                });
            }

            // Handle notification click - navigate and open modal
            function handleNotificationClick(url, adminId, reporterId, action) {
                if (!url) return;
                
                // Determine which ID to use and which function to call
                const entityId = adminId || reporterId;
                const isReporter = !!reporterId;
                
                // If we're already on the page and it's an update action, open the view modal
                if (window.location.pathname === url && entityId && action === 'updated') {
                    // Close dropdown
                    document.getElementById('notifications-dropdown').classList.add('hidden');
                    
                    // Wait a bit then open modal
                    setTimeout(() => {
                        if (isReporter && typeof viewReporter === 'function') {
                            viewReporter(parseInt(entityId));
                        } else if (!isReporter && typeof viewAdministrator === 'function') {
                            viewAdministrator(parseInt(entityId));
                        }
                    }, 100);
                } else {
                    // Navigate to the page with query parameter
                    if (entityId && action === 'updated') {
                        window.location.href = `${url}?view=${entityId}`;
                    } else {
                        window.location.href = url;
                    }
                }
            }

            // Mark notification as read
            function markAsRead(id) {
                fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                })
                .then(() => loadNotifications())
                .catch(error => console.error('Error marking as read:', error));
            }

            // Mark all as read
            document.getElementById('mark-all-read').addEventListener('click', function() {
                fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                })
                .then(() => loadNotifications())
                .catch(error => console.error('Error marking all as read:', error));
            });

            // Clear all notifications
            document.getElementById('clear-all').addEventListener('click', function() {
                fetch('/notifications/clear-all', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                })
                .then(() => loadNotifications())
                .catch(error => console.error('Error clearing notifications:', error));
            });

            // Update notification badge
            function updateNotificationBadge(count) {
                const badge = document.getElementById('notification-badge');
                if (count > 0) {
                    badge.textContent = count > 9 ? '9+' : count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }

            // Add new notification to the list without full reload
            function addNotificationToList(notification) {
                const list = document.getElementById('notifications-list');
                
                // Remove empty state if it exists
                const emptyState = document.getElementById('empty-notifications');
                if (emptyState) {
                    emptyState.remove();
                }
                
                // Create new notification element
                const notificationElement = document.createElement('div');
                notificationElement.className = 'notification-item unread bg-blue-50 dark:bg-blue-900/10 p-4 border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615] cursor-pointer';
                notificationElement.setAttribute('data-id', notification.id);
                notificationElement.setAttribute('data-url', notification.data?.url || '');
                notificationElement.setAttribute('data-admin-id', notification.data?.administrator_id || '');
                notificationElement.setAttribute('data-reporter-id', notification.data?.reporter_id || '');
                notificationElement.setAttribute('data-action', notification.data?.action || '');
                
                notificationElement.innerHTML = `
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${notification.title}</h4>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">${notification.message}</p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">${formatTime(notification.created_at)}</p>
                        </div>
                        <div class="w-2 h-2 bg-blue-500 rounded-full ml-2 mt-1"></div>
                    </div>
                `;
                
                // Add click handler
                notificationElement.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const url = this.dataset.url;
                    const adminId = this.dataset.adminId;
                    const reporterId = this.dataset.reporterId;
                    const action = this.dataset.action;
                    
                    // Mark as read
                    markAsRead(id);
                    
                    // Navigate and open modal if applicable
                    handleNotificationClick(url, adminId, reporterId, action);
                });
                
                // Add animation class
                notificationElement.classList.add('animate-fade-in');
                
                // Insert at the top of the list
                list.insertBefore(notificationElement, list.firstChild);
                
                // Remove animation class after animation completes
                setTimeout(() => {
                    notificationElement.classList.remove('animate-fade-in');
                }, 500);
            }

            // Format time helper
            function formatTime(timestamp) {
                const date = new Date(timestamp);
                const now = new Date();
                const diff = Math.floor((now - date) / 1000); // seconds

                if (diff < 60) return 'Just now';
                if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
                if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
                if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
                return date.toLocaleDateString();
            }

            // Load notifications on page load
            loadNotifications();

            // Listen for real-time notifications
            if (window.Echo) {
                window.Echo.channel('notifications.user.{{ auth()->id() }}')
                    .listen('.notification.created', (event) => {
                        console.log('New notification received:', event.notification);
                        
                        // Get current notification count
                        const badge = document.getElementById('notification-badge');
                        const currentCount = badge.classList.contains('hidden') ? 0 : parseInt(badge.textContent) || 0;
                        
                        // Update badge immediately
                        updateNotificationBadge(currentCount + 1);
                        
                        // Add new notification to the list without full reload
                        addNotificationToList(event.notification);
                    });
            }
        });

        function logout() {
            // Create a form dynamically to avoid CSRF issues
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("logout") }}';
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Submit the form
            document.body.appendChild(form);
            form.submit();
        }

        // Dynamic Avatar Background Color
        function setAvatarColor() {
            const avatar = document.getElementById('user-avatar');
            if (avatar) {
                const letter = avatar.textContent.trim().toUpperCase();
                const colors = [
                    'bg-red-500', 'bg-pink-500', 'bg-purple-500', 'bg-indigo-500',
                    'bg-blue-500', 'bg-cyan-500', 'bg-teal-500', 'bg-green-500',
                    'bg-lime-500', 'bg-yellow-500', 'bg-orange-500', 'bg-amber-500'
                ];
                
                // Get color index based on letter (A=0, B=1, etc.)
                const colorIndex = (letter.charCodeAt(0) - 65) % colors.length;
                avatar.className = avatar.className.replace(/bg-\w+-\d+/, '') + ' ' + colors[colorIndex];
            }
        }

        // Initialize avatar color when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            setAvatarColor();
        });

        // Toggle submenu functionality
        function toggleSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const chevron = document.getElementById(submenuId.replace('-submenu', '-chevron'));
            
            if (submenu && chevron) {
                // Check if this submenu is currently open
                const isOpen = !submenu.classList.contains('hidden');
                
                if (isOpen) {
                    // If open, close it
                    submenu.classList.add('hidden');
                    chevron.classList.remove('rotate-180');
                } else {
                    // If closed, close all others first, then open this one
                    closeAllSubmenus();
                    submenu.classList.remove('hidden');
                    chevron.classList.add('rotate-180');
                }
            }
        }

        // Close all submenus
        function closeAllSubmenus() {
            const submenus = ['users-submenu', 'registration-submenu'];
            const chevrons = ['users-chevron', 'registration-chevron'];
            
            submenus.forEach(submenuId => {
                const submenu = document.getElementById(submenuId);
                if (submenu) {
                    submenu.classList.add('hidden');
                }
            });
            
            chevrons.forEach(chevronId => {
                const chevron = document.getElementById(chevronId);
                if (chevron) {
                    chevron.classList.remove('rotate-180');
                }
            });
        }

        // Close submenus when clicking other menu items
        document.addEventListener('DOMContentLoaded', function() {
            // Get all main menu links (non-submenu items)
            const mainMenuItems = document.querySelectorAll('nav a[href="#"]:not([onclick])');
            
            mainMenuItems.forEach(item => {
                item.addEventListener('click', function() {
                    closeAllSubmenus();
                });
            });
        });

        // Real-time sidebar updates
        document.addEventListener('DOMContentLoaded', function() {
            if (window.Echo) {
                const currentUserId = {{ auth()->id() }};
                
                // Listen for administrator updates
                window.Echo.channel('administrators')
                    .listen('.administrator.updated', (event) => {
                        if (event.administrator && event.administrator.user_id === currentUserId) {
                            updateSidebarUserInfo(event.administrator.user);
                        }
                    });

                // Listen for reporter updates
                window.Echo.channel('reporters')
                    .listen('.reporter.updated', (event) => {
                        if (event.reporter && event.reporter.user_id === currentUserId) {
                            updateSidebarUserInfo(event.reporter.user);
                        }
                    });
            }
        });

        // Function to update sidebar user info
        function updateSidebarUserInfo(userData) {
            const sidebarName = document.querySelector('#user-avatar').nextElementSibling.querySelector('p');
            if (sidebarName) {
                sidebarName.textContent = `${userData.first_name} ${userData.last_name}`;
            }

            const sidebarEmail = document.querySelector('#user-avatar').nextElementSibling.querySelector('p:last-child');
            if (sidebarEmail) {
                sidebarEmail.textContent = userData.email;
            }

            // Update avatar initial and color
            const avatar = document.querySelector('#user-avatar');
            if (avatar) {
                const newColor = getAvatarColor(userData.first_name + userData.last_name);
                avatar.style.backgroundColor = newColor;
                avatar.textContent = userData.first_name.charAt(0).toUpperCase();
            }
        }

        // Function to get avatar color based on first letter of name
        function getAvatarColor(name) {
            const colors = [
                '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', 
                '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'
            ];
            
            // Use only the first letter for consistent color
            const firstLetter = name.charAt(0).toUpperCase();
            const hash = firstLetter.charCodeAt(0);
            
            return colors[hash % colors.length];
        }
    </script>
    
    @stack('scripts')
</body>
</html>
