@extends('layouts.app')

@section('page-title', 'Admin Home')

@section('content')
<div class="space-y-6">
    <!-- Clock, Calendar & Weather Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Clock -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-clock class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                </div>
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Current Time</h3>
                <div id="current-time" class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]"></div>
                <div id="current-date" class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1"></div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-calendar-days class="w-8 h-8 text-green-600 dark:text-green-400" />
                </div>
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Today's Date</h3>
                <div id="calendar-widget" class="text-center"></div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-users class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                </div>
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Recent Users</h3>
                <div id="recent-users" class="text-center">
                    <div class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">{{ $recentUsersCount ?? 0 }}</div>
                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Registered Today</div>
                </div>
            </div>
        </div>
    </div>


    <!-- Quick Actions -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.users.students') }}" class="btn bg-blue-600 hover:bg-blue-700 text-white border-blue-600">Manage Users</a>
            <a href="{{ route('admin.vehicles') }}" class="btn bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-600">View Vehicles</a>
            <a href="{{ route('admin.reports') }}" class="btn bg-yellow-100 dark:bg-yellow-900 hover:bg-yellow-200 dark:hover:bg-yellow-800 text-yellow-800 dark:text-yellow-200 border-yellow-300 dark:border-yellow-700">Check Reports</a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Recent Activity</h3>
        <div class="space-y-4" id="recent-activity-container">
            @forelse($recentActivity as $activity)
                <div class="flex items-center space-x-4">
                    <div class="w-2 h-2 bg-{{ $activity['color'] }}-500 rounded-full"></div>
                    <div class="flex-1">
                        <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $activity['message'] }}</p>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $activity['time']->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">No recent activity</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize reports array for real-time updates
    window.reports = @json($reports ?? []);
    
    // Clock functionality
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour12: true, 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
        const dateString = now.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        document.getElementById('current-time').textContent = timeString;
        document.getElementById('current-date').textContent = dateString;
    }
    
    // Calendar functionality
    function updateCalendar() {
        const now = new Date();
        const calendarWidget = document.getElementById('calendar-widget');
        
        const day = now.getDate();
        const month = now.toLocaleDateString('en-US', { month: 'short' });
        const year = now.getFullYear();
        const dayName = now.toLocaleDateString('en-US', { weekday: 'long' });
        
        calendarWidget.innerHTML = `
            <div class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">${day}</div>
            <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${month} ${year}</div>
            <div class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">${dayName}</div>
        `;
    }
    
    // Recent users functionality
    function updateRecentUsers() {
        const recentUsers = document.getElementById('recent-users');
        const count = {{ $recentUsersCount ?? 0 }};
        const message = 'Registered Today';
        
        recentUsers.innerHTML = `
            <div class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">${count}</div>
            <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${message}</div>
        `;
    }
    
    // Update clock every second
    updateClock();
    setInterval(updateClock, 1000);
    
    // Update calendar and recent users
    updateCalendar();
    updateRecentUsers();
    
    // Listen for real-time report updates
    if (window.Echo) {
        window.Echo.channel('reports')
            .listen('.report.created', (event) => {
                console.log('New report received:', event.report);
                
                // Add the new report to the local reports array
                if (window.reports) {
                    window.reports.unshift(event.report);
                }
                
                // Update recent activity
                updateRecentActivity(event.report);
            });
    }
});

// Function to update recent activity
function updateRecentActivity(report) {
    const activityContainer = document.getElementById('recent-activity-container');
    if (activityContainer && report) {
        const activityItem = document.createElement('div');
        activityItem.className = 'flex items-center space-x-4';
        activityItem.innerHTML = `
            <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
            <div class="flex-1">
                <p class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">New violation report submitted: ${report.violation_type ? report.violation_type.name : 'Unknown'}</p>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Just now</p>
            </div>
        `;
        
        // Insert at the top
        activityContainer.insertBefore(activityItem, activityContainer.firstChild);
        
        // Remove excess items (keep only 10)
        const items = activityContainer.querySelectorAll('.flex.items-center.space-x-4');
        if (items.length > 10) {
            items[items.length - 1].remove();
        }
    }
}
</script>
@endsection
