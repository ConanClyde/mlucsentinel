@extends('layouts.app')

@section('page-title', 'Administrators Management')

@section('content')
<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <!-- Search -->
            <div class="flex-1 md:flex-[2]">
                <label class="form-label">Search</label>
                <input type="text" id="search-input" class="form-input w-full" placeholder="Search by name or email...">
            </div>

            <!-- Status Filter -->
            <div class="flex-1">
                <label class="form-label">Status</label>
                <select id="status-filter" class="form-input w-full">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <!-- Role Filter -->
            <div class="flex-1">
                <label class="form-label">Role</label>
                <select id="role-filter" class="form-input w-full">
                    <option value="">All Roles</option>
                    @foreach($adminRoles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Button -->
            <div class="flex-shrink-0">
                <button id="reset-filters" class="btn btn-secondary !h-[38px] px-6">Reset</button>
            </div>
        </div>
    </div>

    <!-- Administrators Table -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Administrators List</h3>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Show:</span>
                    <select id="pagination-limit" class="form-input !h-[38px] !py-1 !px-3 text-sm">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Live Updates:</span>
                    <div id="connectionStatus" class="w-3 h-3 rounded-full bg-red-500"></div>
                </div>
                <button onclick="exportToCSV()" class="btn btn-csv">CSV</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Name</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Email</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Role</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Status</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Created</th>
                        <th class="text-center py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="administratorsTableBody">
                    @forelse($administrators as $administrator)
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="{{ $administrator->id }}">
                        <td class="py-2 px-3">
                            <div class="flex items-center">
                                @php
                                    $colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'];
                                    $firstLetter = strtoupper(substr($administrator->user->first_name ?? 'U', 0, 1));
                                    $hash = ord($firstLetter);
                                    $avatarColor = $colors[$hash % count($colors)];
                                @endphp
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 text-white font-semibold text-xs flex-shrink-0" style="background-color: {{ $avatarColor }}">
                                    {{ strtoupper(substr($administrator->user->first_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $administrator->user->first_name }} {{ $administrator->user->last_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $administrator->user->email }}</td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $administrator->adminRole->name ?? 'No Role' }}</td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $administrator->user->is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                                {{ $administrator->user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $administrator->created_at->format('M d, Y') }}</td>
                        <td class="py-2 px-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="viewAdministrator({{ $administrator->id }})" class="btn-view" title="View">
                                    <x-heroicon-s-eye class="w-4 h-4" />
                                </button>
                                <button onclick="openEditModal({{ $administrator->id }})" class="btn-edit" title="Edit">
                                    <x-heroicon-s-pencil class="w-4 h-4" />
                                </button>
                                @if($administrator->user_id === auth()->id())
                                    <button class="btn-disable" title="Cannot delete your own account" disabled>
                                        <x-heroicon-s-trash class="w-4 h-4" />
                                    </button>
                                @else
                                    <button onclick="deleteAdministrator({{ $administrator->id }})" class="btn-delete" title="Delete">
                                        <x-heroicon-s-trash class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                            No administrators found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div id="pagination-controls" class="flex items-center justify-between mt-6">
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Showing <span id="showing-start">1</span>-<span id="showing-end">10</span> of <span id="total-count">0</span> administrators
            </p>
            <div class="flex space-x-2">
                <button id="prev-page" class="btn-pagination btn-paginationDisable" onclick="changePage(-1)">
                    <x-heroicon-o-chevron-left class="w-4 h-4" />
                </button>
                <div id="page-numbers" class="flex space-x-2"></div>
                <button id="next-page" class="btn-pagination btn-paginationArrow" onclick="changePage(1)">
                    <x-heroicon-o-chevron-right class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeViewModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Administrator Details</h2>
        </div>
        <div class="modal-body" id="viewModalContent">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title">Edit Administrator</h2>
        </div>
        <form id="editForm">
            <div class="modal-body">
                <input type="hidden" id="edit_admin_id">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" id="edit_first_name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" id="edit_last_name" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="edit_email" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select id="edit_is_active" class="form-input" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <hr class="my-4 border-[#e3e3e0] dark:border-[#3E3E3A]">
                
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div class="relative">
                        <input type="password" id="edit_password" class="form-input pr-10" placeholder="Leave blank to keep current password">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password" data-target="edit_password">
                            <x-heroicon-c-eye class="eye-icon w-5 h-5 text-gray-400" />
                            <x-heroicon-c-eye-slash class="eye-slash-icon w-5 h-5 text-gray-400 hidden" />
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="edit_password_confirmation" class="form-input pr-10" placeholder="Confirm new password">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password" data-target="edit_password_confirmation">
                            <x-heroicon-c-eye class="eye-icon w-5 h-5 text-gray-400" />
                            <x-heroicon-c-eye-slash class="eye-slash-icon w-5 h-5 text-gray-400 hidden" />
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Administrator
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteModalMessage"></p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteAdministrator()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Initialize real-time updates using the AdministratorsRealtime module
let realtimeManager;
let administrators = @json($administrators);
window.currentUserId = {{ auth()->id() }};

// Reverb configuration from environment
const reverbConfig = {
    key: '{{ config('reverb.apps.apps.0.key') }}',
    host: '{{ config('reverb.apps.apps.0.options.host') }}',
    port: '{{ config('reverb.apps.apps.0.options.port') }}',
    scheme: '{{ config('reverb.apps.apps.0.options.scheme') }}'
};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the real-time manager
    if (window.AdministratorsRealtime) {
        realtimeManager = new window.AdministratorsRealtime();
        realtimeManager.init(administrators);
        
        // Update local administrators array when real-time updates occur
        window.Echo.channel('administrators').listen('.administrator.updated', (event) => {
            const index = administrators.findIndex(a => a.id === event.administrator.id);
            if (index !== -1) {
                administrators[index] = event.administrator;
            } else if (event.action === 'created') {
                administrators.unshift(event.administrator);
            }
        });
    } else {
        console.error('AdministratorsRealtime module not loaded');
    }

    // Check if we need to open view modal from notification
    const urlParams = new URLSearchParams(window.location.search);
    const viewId = urlParams.get('view');
    if (viewId) {
        setTimeout(() => {
            viewAdministrator(parseInt(viewId));
            // Clean up URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 500);
    }

    // Filter functionality
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const roleFilter = document.getElementById('role-filter');
    const resetButton = document.getElementById('reset-filters');

    // Add event listeners - directly call applyPagination
    searchInput.addEventListener('input', function() {
        currentPage = 1;
        applyPagination();
    });
    
    statusFilter.addEventListener('change', function() {
        currentPage = 1;
        applyPagination();
    });
    
    roleFilter.addEventListener('change', function() {
        currentPage = 1;
        applyPagination();
    });

    resetButton.addEventListener('click', function() {
        searchInput.value = '';
        statusFilter.value = '';
        roleFilter.value = '';
        currentPage = 1;
        applyPagination();
    });

    // Pagination functionality
    const paginationLimit = document.getElementById('pagination-limit');
    let currentPage = 1;
    let itemsPerPage = 10;

    paginationLimit.addEventListener('change', function() {
        itemsPerPage = parseInt(this.value);
        currentPage = 1;
        applyPagination();
    });

    function applyPagination() {
        const rows = document.querySelectorAll('#administratorsTableBody tr');
        let visibleCount = 0;
        let totalFiltered = 0;
        
        // First pass: count total filtered rows
        rows.forEach((row) => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) {
                return;
            }
            
            const name = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
            const email = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const statusBadge = row.querySelector('td:nth-child(4) span');
            const isActive = statusBadge?.textContent.trim() === 'Active';
            
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const roleValue = roleFilter.value;
            
            const adminId = row.getAttribute('data-id');
            const admin = administrators.find(a => a.id == adminId);
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesStatus = statusValue === '' || 
                                (statusValue === '1' && isActive) || 
                                (statusValue === '0' && !isActive);
            const matchesRole = roleValue === '' || (admin && admin.role_id == roleValue);
            
            if (matchesSearch && matchesStatus && matchesRole) {
                totalFiltered++;
            }
        });
        
        // Second pass: apply pagination
        rows.forEach((row) => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) {
                row.style.display = totalFiltered === 0 ? '' : 'none';
                return;
            }
            
            const name = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
            const email = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const statusBadge = row.querySelector('td:nth-child(4) span');
            const isActive = statusBadge?.textContent.trim() === 'Active';
            
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const roleValue = roleFilter.value;
            
            const adminId = row.getAttribute('data-id');
            const admin = administrators.find(a => a.id == adminId);
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesStatus = statusValue === '' || 
                                (statusValue === '1' && isActive) || 
                                (statusValue === '0' && !isActive);
            const matchesRole = roleValue === '' || (admin && admin.role_id == roleValue);
            
            if (!matchesSearch || !matchesStatus || !matchesRole) {
                row.style.display = 'none';
                return;
            }
            
            visibleCount++;
            const startIndex = (currentPage - 1) * itemsPerPage + 1;
            const endIndex = currentPage * itemsPerPage;
            
            if (visibleCount >= startIndex && visibleCount <= endIndex) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        updatePaginationControls(totalFiltered);
    }
    
    function updatePaginationControls(totalFiltered) {
        const totalPages = Math.ceil(totalFiltered / itemsPerPage);
        const start = totalFiltered === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(currentPage * itemsPerPage, totalFiltered);
        
        // Update showing text
        document.getElementById('showing-start').textContent = start;
        document.getElementById('showing-end').textContent = end;
        document.getElementById('total-count').textContent = totalFiltered;
        
        // Update prev/next buttons
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        
        prevBtn.className = currentPage === 1 ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';
        nextBtn.className = (currentPage === totalPages || totalPages === 0) ? 'btn-pagination btn-paginationDisable' : 'btn-pagination btn-paginationArrow';
        
        // Generate page numbers (show only 3 pages at a time)
        const pageNumbers = document.getElementById('page-numbers');
        pageNumbers.innerHTML = '';
        
        // Calculate which 3 pages to show
        let startPage = Math.max(1, currentPage - 1);
        let endPage = Math.min(totalPages, startPage + 2);
        
        // Adjust if we're near the end
        if (endPage - startPage < 2) {
            startPage = Math.max(1, endPage - 2);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = i === currentPage ? 'btn-pagination btn-paginationActive' : 'btn-pagination btn-paginationNumber';
            btn.onclick = () => goToPage(i);
            pageNumbers.appendChild(btn);
        }
    }

    // Apply initial pagination on page load
    applyPagination();
    
    // Pagination navigation functions (global scope)
    window.changePage = function(direction) {
        const rows = document.querySelectorAll('#administratorsTableBody tr');
        let totalFiltered = 0;
        
        rows.forEach((row) => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) {
                return;
            }
            
            const name = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
            const email = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const statusBadge = row.querySelector('td:nth-child(4) span');
            const isActive = statusBadge?.textContent.trim() === 'Active';
            
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const roleValue = roleFilter.value;
            
            const adminId = row.getAttribute('data-id');
            const admin = administrators.find(a => a.id == adminId);
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesStatus = statusValue === '' || 
                                (statusValue === '1' && isActive) || 
                                (statusValue === '0' && !isActive);
            const matchesRole = roleValue === '' || (admin && admin.role_id == roleValue);
            
            if (matchesSearch && matchesStatus && matchesRole) {
                totalFiltered++;
            }
        });
        
        const totalPages = Math.ceil(totalFiltered / itemsPerPage);
        currentPage = Math.max(1, Math.min(currentPage + direction, totalPages));
        applyPagination();
    };
    
    window.goToPage = function(page) {
        currentPage = page;
        applyPagination();
    };
});

// Test function to verify Echo connection
function manualTest() {
    console.log('=== Echo Connection Test ===');
    console.log('Echo available:', !!window.Echo);
    console.log('Pusher available:', !!window.Pusher);
    console.log('Real-time manager:', !!realtimeManager);
    
    if (window.Echo) {
        console.log('Echo config:', reverbConfig);
        console.log('Echo connector:', window.Echo.connector);
        
        // Check connection state
        if (window.Echo.connector && window.Echo.connector.pusher) {
            const state = window.Echo.connector.pusher.connection.state;
            console.log('Connection state:', state);
        }
        
        alert('✅ Echo is working! Check console for details.');
    } else {
        alert('❌ Echo is NOT working! Check console for errors.');
    }
}

// View Administrator
function viewAdministrator(id) {
    const administrator = administrators.find(admin => admin.id === id);
    if (!administrator) {
        console.error('Administrator not found:', id);
        return;
    }
    
    const modal = document.getElementById('viewModal');
    const content = document.getElementById('viewModalContent');
    
    // Use the same avatar color logic as the table (no space between names)
    const name = administrator.user.first_name + administrator.user.last_name;
    
    // Generate color using same logic as PHP
    const colors = [
        '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', 
        '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'
    ];
    const firstLetter = administrator.user.first_name.charAt(0).toUpperCase();
    const hash = firstLetter.charCodeAt(0);
    const avatarColor = colors[hash % colors.length];
    const initials = `${administrator.user.first_name.charAt(0)}`.toUpperCase();
    
    content.innerHTML = `
        <div class="flex items-center space-x-4 mb-6">
            <div class="w-16 h-16 rounded-full flex items-center justify-center text-white font-bold text-xl" style="background-color: ${avatarColor}">
                ${initials}
            </div>
            <div>
                <h4 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">${administrator.user.first_name} ${administrator.user.last_name}</h4>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${administrator.admin_role?.name || 'No Role'}</p>
            </div>
        </div>
        <div class="space-y-3">
            <div>
                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Email</label>
                <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${administrator.user.email}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Status</label>
                <p><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${administrator.user.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'}">
                    ${administrator.user.is_active ? 'Active' : 'Inactive'}
                </span></p>
            </div>
            <div>
                <label class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Created</label>
                <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${new Date(administrator.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeViewModal() {
    const modal = document.getElementById('viewModal');
    modal.classList.add('hidden');
}

// Edit Administrator Modal
function openEditModal(id) {
    const administrator = administrators.find(admin => admin.id === id);
    if (!administrator) {
        console.error('Administrator not found:', id);
        return;
    }
    
    document.getElementById('edit_admin_id').value = administrator.id;
    document.getElementById('edit_first_name').value = administrator.user.first_name;
    document.getElementById('edit_last_name').value = administrator.user.last_name;
    document.getElementById('edit_email').value = administrator.user.email;
    document.getElementById('edit_is_active').value = administrator.user.is_active ? '1' : '0';
    
    const modal = document.getElementById('editModal');
    modal.classList.remove('hidden');
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.add('hidden');
}

// Handle edit form submission
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const id = document.getElementById('edit_admin_id').value;
            const isActiveValue = document.getElementById('edit_is_active').value;
            const password = document.getElementById('edit_password').value;
            const passwordConfirmation = document.getElementById('edit_password_confirmation').value;
            
            const data = {
                first_name: document.getElementById('edit_first_name').value,
                last_name: document.getElementById('edit_last_name').value,
                email: document.getElementById('edit_email').value,
                is_active: isActiveValue === '1'
            };
            
            // Add password fields if provided
            if (password) {
                if (password !== passwordConfirmation) {
                    alert('Passwords do not match');
                    return;
                }
                data.password = password;
                data.password_confirmation = passwordConfirmation;
            }
            
            console.log('Sending update data:', data);
            console.log('is_active dropdown value:', isActiveValue);
            console.log('is_active boolean:', data.is_active);
            
            // Mark this action BEFORE sending to prevent notification
            if (realtimeManager) {
                realtimeManager.markUserAction(parseInt(id));
            }
            
            fetch(`/users/administrators/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeEditModal();
                } else {
                    alert(data.message || 'Failed to update administrator');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating');
            });
        });
    }
});

// Delete Administrator
let deleteAdminId = null;

function deleteAdministrator(id) {
    const administrator = administrators.find(admin => admin.id === id);
    if (!administrator) {
        console.error('Administrator not found:', id);
        return;
    }
    
    deleteAdminId = id;
    document.getElementById('deleteModalMessage').textContent = 
        `Are you sure you want to delete ${administrator.user.first_name} ${administrator.user.last_name}? This action cannot be undone.`;
    
    // Mark this action BEFORE showing modal
    if (realtimeManager) {
        realtimeManager.markUserAction(id);
    }
    
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('hidden');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    deleteAdminId = null;
}

function confirmDeleteAdministrator() {
    if (!deleteAdminId) return;
    
    fetch(`/users/administrators/${deleteAdminId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeDeleteModal();
        } else {
            alert(data.message || 'Failed to delete administrator');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting');
    });
}

// Export to CSV function
function exportToCSV() {
    // Get current visible administrators (respecting filters)
    const visibleAdmins = administrators.filter(admin => {
        const row = document.querySelector(`tr[data-id="${admin.id}"]`);
        return row && row.style.display !== 'none';
    });

    if (visibleAdmins.length === 0) {
        alert('No administrators to export');
        return;
    }

    // CSV Headers
    const headers = ['Name', 'Email', 'Role', 'Status', 'Created Date'];
    
    // CSV Rows
    const rows = visibleAdmins.map(admin => {
        const name = `${admin.user.first_name} ${admin.user.last_name}`;
        const email = admin.user.email;
        const role = admin.admin_role?.name || 'No Role';
        const status = admin.user.is_active ? 'Active' : 'Inactive';
        const createdDate = new Date(admin.created_at).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        // Escape quotes and wrap in quotes if contains comma
        return [name, email, role, status, createdDate].map(field => {
            const escaped = String(field).replace(/"/g, '""');
            return `"${escaped}"`;
        }).join(',');
    });

    // Combine headers and rows
    const csv = [headers.join(','), ...rows].join('\n');

    // Create blob and download
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `administrators_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Expose functions to global scope for onclick handlers
window.viewAdministrator = viewAdministrator;
window.openEditModal = openEditModal;
window.deleteAdministrator = deleteAdministrator;
window.closeViewModal = closeViewModal;
window.closeDeleteModal = closeDeleteModal;
window.confirmDeleteAdministrator = confirmDeleteAdministrator;
window.exportToCSV = exportToCSV;
window.closeEditModal = closeEditModal;

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (realtimeManager) {
        realtimeManager.disconnect();
    }
});
</script>

<style>
/* Animation styles for real-time updates */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: scale(1);
    }
    to {
        opacity: 0;
        transform: scale(0.95);
    }
}

@keyframes highlight {
    0%, 100% {
        background-color: transparent;
    }
    50% {
        background-color: rgba(99, 102, 241, 0.1);
    }
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}

.animate-fade-out {
    animation: fadeOut 0.3s ease-out;
}

.animate-highlight {
    animation: highlight 1s ease-out;
}

.animate-slide-in {
    animation: slideIn 0.3s ease-out;
}
</style>

<script>
// Listen for profile updates from profile page
document.addEventListener('DOMContentLoaded', function() {
    if (window.Echo) {
        const currentUserId = {{ auth()->id() }};
        
        // Listen for user updates (from profile page)
        window.Echo.channel('administrators')
            .listen('.administrator.updated', (event) => {
                if (event.administrator && event.administrator.user_id === currentUserId) {
                    // Update the current user's row in the table
                    updateCurrentUserRow(event.administrator);
                }
            });
    }
});

// Function to update current user's row in the table
function updateCurrentUserRow(administratorData) {
    const currentUserRow = document.querySelector(`tr[data-id="${administratorData.id}"]`);
    if (currentUserRow) {
        // Update the name in the table
        const nameElement = currentUserRow.querySelector('.text-sm.font-medium');
        if (nameElement) {
            nameElement.textContent = `${administratorData.user.first_name} ${administratorData.user.last_name}`;
        }

        // Update the email in the table
        const emailElement = currentUserRow.querySelector('td:nth-child(2)');
        if (emailElement) {
            emailElement.textContent = administratorData.user.email;
        }

        // Update the status if changed
        const statusElement = currentUserRow.querySelector('span');
        if (statusElement) {
            const isActive = administratorData.user.is_active;
            statusElement.textContent = isActive ? 'Active' : 'Inactive';
            statusElement.className = isActive 
                ? 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                : 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
        }

        // Update the local administrators array
        const index = administrators.findIndex(a => a.id === administratorData.id);
        if (index !== -1) {
            administrators[index] = administratorData;
        }
    }
}
</script>
@endpush
