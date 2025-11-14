/**
 * Real-time Student Updates Module
 * 
 * This module handles real-time updates for the students table
 * using Laravel Echo and Reverb WebSocket server.
 */

class StudentsRealtime {
    constructor() {
        this.connectionStatus = null;
        this.tableBody = null;
        this.students = [];
        this.channel = null;
        this.retryCount = 0;
        this.maxRetries = 5;
    }

    /**
     * Initialize the real-time connection
     */
    init(students = []) {
        this.students = students;
        this.connectionStatus = document.getElementById('connectionStatus');
        this.tableBody = document.getElementById('studentsTableBody');

        if (!this.connectionStatus || !this.tableBody) {
            console.error('Required DOM elements not found');
            return;
        }

        this.setupEcho();
    }

    /**
     * Setup Laravel Echo connection
     */
    setupEcho() {
        if (!window.Echo) {
            if (this.retryCount < this.maxRetries) {
                this.retryCount++;
                console.log(`Echo not ready, retrying... (${this.retryCount}/${this.maxRetries})`);
                setTimeout(() => this.setupEcho(), 500);
            } else {
                console.error('Echo failed to initialize after maximum retries');
                this.updateConnectionStatus('error');
            }
            return;
        }

        console.log('Setting up Echo connection...');
        this.updateConnectionStatus('connecting');

        try {
            // Subscribe to the students channel
            this.channel = window.Echo.channel('students');

            // Listen for student.updated events
            this.channel.listen('.student.updated', (event) => {
                console.log('Received broadcast event:', event);
                this.handleStudentUpdate(event);
            });

            // Handle connection success
            this.channel.subscribed(() => {
                console.log('Successfully subscribed to students channel');
                this.updateConnectionStatus('connected');
            });

            // Handle connection errors
            this.channel.error((error) => {
                console.error('Channel subscription error:', error);
                this.updateConnectionStatus('error');
            });

            // Also listen to vehicles channel for real-time vehicle updates
            window.Echo.channel('vehicles').listen('.vehicle.updated', (event) => {
                console.log('Received vehicle update:', event);
                this.handleVehicleUpdate(event);
            });

        } catch (error) {
            console.error('Error setting up Echo:', error);
            this.updateConnectionStatus('error');
        }
    }

    /**
     * Update connection status indicator
     */
    updateConnectionStatus(status) {
        if (!this.connectionStatus) return;

        const statusClasses = {
            connecting: 'bg-yellow-500',
            connected: 'bg-green-500',
            error: 'bg-red-500',
            disconnected: 'bg-gray-500'
        };

        // Remove all status classes
        Object.values(statusClasses).forEach(cls => {
            this.connectionStatus.classList.remove(cls);
        });

        // Add the current status class
        this.connectionStatus.classList.add(statusClasses[status] || statusClasses.disconnected);

        // Update title attribute for tooltip
        const statusTexts = {
            connecting: 'Connecting to live updates...',
            connected: 'Connected - Live updates active',
            error: 'Connection error - Updates may be delayed',
            disconnected: 'Disconnected from live updates'
        };

        this.connectionStatus.title = statusTexts[status] || 'Unknown status';
    }

    /**
     * Handle student update events
     */
    handleStudentUpdate(event) {
        const { student, action, editor } = event;

        switch (action) {
            case 'created':
                this.addStudent(student);
                break;
            case 'updated':
                this.updateStudent(student);
                // Only show notification if this update wasn't made by current user
                if (editor && editor !== 'self' && !this.isCurrentUserAction(student.id)) {
                    this.showBrowserNotification(
                        'Student Updated',
                        `${editor} updated Student ${student.user.first_name} ${student.user.last_name}`,
                        student.id,
                        'updated'
                    );
                }
                break;
            case 'deleted':
                this.removeStudent(student);
                if (editor && !this.isCurrentUserAction(student.id)) {
                    this.showBrowserNotification(
                        'Student Removed',
                        `${editor} removed ${student.user.first_name} ${student.user.last_name}`,
                        null,
                        'deleted'
                    );
                }
                break;
            default:
                console.warn('Unknown action:', action);
        }
    }

    /**
     * Handle vehicle update events
     */
    handleVehicleUpdate(event) {
        const { vehicle, action } = event;
        
        // Update the view modal if it's open and showing this student's vehicles
        const viewModal = document.getElementById('viewModal');
        if (viewModal && !viewModal.classList.contains('hidden')) {
            const existingVehiclesDisplay = document.getElementById('viewVehiclesDisplay');
            if (existingVehiclesDisplay) {
                this.updateViewModalVehicles(vehicle, action);
            }
        }
    }

    /**
     * Update vehicle display in the view modal
     */
    updateViewModalVehicles(vehicle, action) {
        const vehicleList = document.getElementById('viewVehiclesDisplay');
        if (!vehicleList) return;

        const vehicleItem = vehicleList.querySelector(`[data-vehicle-id="${vehicle.id}"]`);
        
        if (action === 'deleted' && vehicleItem) {
            // Remove the vehicle from display
            vehicleItem.remove();
            
            // Check if there are no vehicles left
            if (vehicleList.children.length === 0) {
                vehicleList.innerHTML = '<p class="text-sm text-[#706f6c] dark:text-[#A1A09A] italic">No vehicles registered</p>';
            }
        } else if (action === 'created' || action === 'updated') {
            // Update existing or add new vehicle
            // This would need to fetch the updated student data or reload the modal
            console.log('Vehicle updated, consider refreshing modal data');
        }
    }

    /**
     * Check if the current user just performed this action
     */
    isCurrentUserAction(studentId) {
        if (!window._recentActions) {
            window._recentActions = new Map();
        }
        
        const now = Date.now();
        const recent = window._recentActions.get(studentId);
        
        if (recent && (now - recent) < 2000) {
            window._recentActions.delete(studentId);
            return true;
        }
        
        return false;
    }

    /**
     * Mark an action as performed by current user
     */
    markUserAction(studentId) {
        if (!window._recentActions) {
            window._recentActions = new Map();
        }
        window._recentActions.set(studentId, Date.now());
    }

    /**
     * Add a new student row to the table
     */
    addStudent(student) {
        // Use user_id to find row (consistent with table rows which use data-id="{{ $student->user_id }}")
        const userId = student.user_id || student.user?.id;
        const existingRow = this.tableBody.querySelector(`tr[data-id="${userId}"]`);
        
        if (existingRow) {
            this.updateStudent(student);
            return;
        }

        this.students.unshift(student);
        const newRow = this.createStudentRow(student);
        newRow.classList.add('animate-fade-in');
        
        const emptyRow = this.tableBody.querySelector('tr td[colspan]');
        if (emptyRow) {
            emptyRow.closest('tr').remove();
        }
        
        this.tableBody.insertBefore(newRow, this.tableBody.firstChild);

        setTimeout(() => {
            newRow.classList.remove('animate-fade-in');
        }, 500);
    }

    /**
     * Update an existing student row
     */
    updateStudent(student) {
        // Use user_id to find row (consistent with table rows which use data-id="{{ $student->user_id }}")
        const userId = student.user_id || student.user?.id;
        const existingRow = this.tableBody.querySelector(`tr[data-id="${userId}"]`);
        
        if (!existingRow) {
            console.log('Student not found in table, adding instead');
            this.addStudent(student);
            return;
        }

        // Find student by user_id (consistent with how rows are identified)
        const index = this.students.findIndex(s => (s.user_id === userId) || (s.user?.id === userId));
        if (index !== -1) {
            this.students[index] = student;
        }

        const newRow = this.createStudentRow(student);
        newRow.classList.add('animate-highlight');
        
        existingRow.parentNode.replaceChild(newRow, existingRow);

        setTimeout(() => {
            newRow.classList.remove('animate-highlight');
        }, 1000);
    }

    /**
     * Remove a student row from the table
     */
    removeStudent(student) {
        // Use user_id to find row (consistent with table rows which use data-id="{{ $student->user_id }}")
        const userId = student.user_id || student.user?.id;
        const rowToRemove = this.tableBody.querySelector(`tr[data-id="${userId}"]`);
        
        if (!rowToRemove) {
            console.log('Student not found in table');
            return;
        }

        // Filter by user_id (consistent with how rows are identified)
        this.students = this.students.filter(s => (s.user_id !== userId) && (s.user?.id !== userId));
        rowToRemove.classList.add('animate-fade-out');

        setTimeout(() => {
            rowToRemove.remove();

            if (this.tableBody.children.length === 0) {
                this.showEmptyState();
            }
        }, 300);
    }

    /**
     * Create a student table row
     */
    createStudentRow(student) {
        const row = document.createElement('tr');
        row.className = 'border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615] transition-colors';
        // Use user_id for data-id (consistent with table rows which use data-id="{{ $student->user_id }}")
        const userId = student.user_id || student.user?.id;
        row.setAttribute('data-id', userId);

        const statusClass = student.user.is_active
            ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
            : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';

        const statusText = student.user.is_active ? 'Active' : 'Inactive';
        const collegeName = student.college ? student.college.name : 'No College';
        const createdDate = new Date(student.created_at).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });

        const initials = `${student.user.first_name.charAt(0)}`.toUpperCase();
        const avatarColor = this.getAvatarColor(student.user.first_name + student.user.last_name);

        row.innerHTML = `
            <td class="py-2 px-3">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 text-white font-semibold text-xs" style="background-color: ${avatarColor}">
                        ${initials}
                    </div>
                    <div>
                        <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${this.escapeHtml(student.user.first_name)} ${this.escapeHtml(student.user.last_name)}</div>
                    </div>
                </div>
            </td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${this.escapeHtml(student.user.email)}</td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${this.escapeHtml(student.student_id)}</td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${this.escapeHtml(collegeName)}</td>
            <td class="py-2 px-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                    ${statusText}
                </span>
            </td>
            <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">${createdDate}</td>
            <td class="py-2 px-3">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="viewStudent(${student.id})" class="btn-view" title="View">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <button onclick="openEditModal(${student.id})" class="btn-edit" title="Edit">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                    </button>
                    <button onclick="deleteStudent(${student.id})" class="btn-delete" title="Delete">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </td>
        `;

        return row;
    }

    /**
     * Show empty state when no students exist
     */
    showEmptyState() {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="7" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                No students found.
            </td>
        `;
        this.tableBody.appendChild(emptyRow);
    }

    /**
     * Show browser/system notification
     */
    showBrowserNotification(title, message, studentId = null, action = null) {
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.displayNotification(title, message, studentId, action);
                }
            });
        } else if (Notification.permission === 'granted') {
            this.displayNotification(title, message, studentId, action);
        }
    }

    /**
     * Display the browser notification
     */
    displayNotification(title, message, studentId = null, action = null) {
        const notification = new Notification(title, {
            body: message,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: 'student-update',
            requireInteraction: false,
        });

        setTimeout(() => notification.close(), 5000);

        notification.onclick = function() {
            window.focus();
            notification.close();
            
            if (studentId && action === 'updated') {
                window.location.href = `/users/students?view=${studentId}`;
            } else {
                window.location.href = '/users/students';
            }
        };
    }

    /**
     * Generate consistent avatar color based on name
     */
    getAvatarColor(name) {
        const colors = [
            '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', 
            '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'
        ];
        
        const firstLetter = name.charAt(0).toUpperCase();
        const hash = firstLetter.charCodeAt(0);
        
        return colors[hash % colors.length];
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Disconnect from the channel
     */
    disconnect() {
        if (this.channel) {
            window.Echo.leave('students');
            this.channel = null;
            this.updateConnectionStatus('disconnected');
            console.log('Disconnected from students channel');
        }
    }
}

// Export for use in blade templates
window.StudentsRealtime = StudentsRealtime;

