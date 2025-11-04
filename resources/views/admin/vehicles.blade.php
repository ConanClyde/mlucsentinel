@extends('layouts.app')

@section('page-title', 'Vehicles Management')

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Filter Card -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex flex-col gap-3 md:gap-4">
            <!-- Search and Reset Row -->
            <div class="flex flex-col sm:flex-row gap-3 md:gap-4">
                <div class="flex-1">
                    <label class="form-label">Search</label>
                    <input type="text" id="search-input" class="form-input w-full" placeholder="Search by owner, plate number...">
                </div>
                <div class="flex-shrink-0 sm:w-auto">
                    <label class="form-label opacity-0 hidden sm:block">Reset</label>
                    <button id="reset-filters" class="btn btn-secondary !h-[38px] w-full sm:w-auto px-6">Reset</button>
                </div>
            </div>

            <!-- Filters Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                <!-- Vehicle Type Filter -->
                <div class="w-full">
                    <label class="form-label">Type</label>
                    <select id="type-filter" class="form-input w-full">
                        <option value="">All Types</option>
                        @foreach($vehicleTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Color Filter -->
                <div class="w-full">
                    <label class="form-label">Color</label>
                    <select id="color-filter" class="form-input w-full">
                        <option value="">All Colors</option>
                        <option value="blue">Blue</option>
                        <option value="green">Green</option>
                        <option value="yellow">Yellow</option>
                        <option value="pink">Pink</option>
                        <option value="orange">Orange</option>
                        <option value="maroon">Maroon</option>
                        <option value="white">White</option>
                        <option value="black">Black</option>
                    </select>
                </div>

                <!-- User Type Filter -->
                <div class="w-full">
                    <label class="form-label">User Type</label>
                    <select id="user-type-filter" class="form-input w-full">
                        <option value="">All Users</option>
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>
                        <option value="security">Security</option>
                        <option value="stakeholder">Stakeholder</option>
                    </select>
                </div>

                <!-- College Filter (only for students) -->
                <div class="w-full hidden" id="college-filter-wrapper">
                    <label class="form-label">College</label>
                    <select id="college-filter" class="form-input w-full">
                        <option value="">All Colleges</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}">{{ $college->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicles Table -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 md:mb-6">
            <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Vehicles List</h3>
            <div class="flex flex-wrap items-center gap-3 md:gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">Show:</span>
                    <select id="pagination-limit" class="form-input !h-[38px] !py-1 !px-3 text-xs md:text-sm">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">Live Updates:</span>
                    <div id="connectionStatus" class="w-3 h-3 rounded-full bg-red-500"></div>
                </div>
                <button onclick="exportToCSV()" class="btn btn-csv !text-xs md:!text-sm">CSV</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Owner</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Type</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Plate No.</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Sticker</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Created</th>
                        <th class="text-center py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="vehiclesTableBody">
                    @forelse($vehicles as $vehicle)
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="{{ $vehicle->id }}">
                        <td class="py-2 px-3">
                            <div class="flex items-center">
                                @php
                                    $colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'];
                                    $firstLetter = strtoupper(substr($vehicle->user->first_name ?? 'U', 0, 1));
                                    $hash = ord($firstLetter);
                                    $avatarColor = $colors[$hash % count($colors)];
                                @endphp
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 text-white font-semibold text-xs flex-shrink-0" style="background-color: {{ $avatarColor }}">
                                    {{ $firstLetter }}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $vehicle->user->first_name }} {{ $vehicle->user->last_name }}</div>
                                    <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $vehicle->user->user_type->label() }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $vehicle->type->name ?? 'N/A' }}</td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            {{ $vehicle->plate_no ?? 'No Plate' }}
                        </td>
                        <td class="py-2 px-3">
                            <div class="flex items-center gap-2">
                                @php
                                    $colorMap = [
                                        'blue' => '#007BFF',
                                        'green' => '#28A745',
                                        'yellow' => '#FFC107',
                                        'pink' => '#E83E8C',
                                        'orange' => '#FD7E14',
                                        'maroon' => '#800000',
                                        'white' => '#FFFFFF',
                                        'black' => '#000000',
                                    ];
                                    $bgColor = $colorMap[$vehicle->color] ?? '#000000';
                                @endphp
                                <div class="w-4 h-4 rounded-full border border-gray-300 dark:border-gray-600" style="background-color: {{ $bgColor }}"></div>
                                <span class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">{{ $vehicle->number }}</span>
                            </div>
                        </td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $vehicle->created_at->format('M d, Y') }}</td>
                        <td class="py-2 px-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="viewVehicle({{ $vehicle->id }})" class="btn-view" title="View Sticker">
                                    <x-heroicon-s-eye class="w-4 h-4" />
                                </button>
                                <button onclick="deleteVehicle({{ $vehicle->id }})" class="btn-delete" title="Delete">
                                    <x-heroicon-s-trash class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                            No vehicles found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div id="pagination-controls" class="flex items-center justify-between mt-6">
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Showing <span id="showing-start">1</span>-<span id="showing-end">10</span> of <span id="total-count">0</span> vehicles
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

<!-- View Vehicle Modal -->
<div id="viewModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeViewModal()">
    <div class="modal-container-wide">
        <div class="modal-header">
            <h2 class="modal-title">Vehicle Details</h2>
        </div>
        <div class="modal-body max-h-[80vh] overflow-y-auto" id="view-vehicle-details">
            <!-- Vehicle details will be loaded here -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Vehicle
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteModalMessage"></p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDelete()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Set current user ID for action tracking
window.currentUserId = {{ auth()->id() }};

// Initialize vehicles array and real-time updates
let realtimeManager;
window.vehicles = @json($vehicles);

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the real-time manager
    if (window.VehiclesRealtime) {
        realtimeManager = new window.VehiclesRealtime();
        realtimeManager.init(vehicles);
        
        // Update local vehicles array when real-time updates occur
        window.Echo.channel('vehicles').listen('.vehicle.updated', (event) => {
            const index = vehicles.findIndex(v => v.id === event.vehicle.id);
            if (index !== -1) {
                vehicles[index] = event.vehicle;
            } else if (event.action === 'created') {
                vehicles.unshift(event.vehicle);
            } else if (event.action === 'deleted') {
                vehicles = vehicles.filter(v => v.id !== event.vehicle.id);
            }
        });
    } else {
        console.error('VehiclesRealtime module not loaded');
    }

    // Check if we need to open view modal from notification
    const urlParams = new URLSearchParams(window.location.search);
    const viewId = urlParams.get('view');
    if (viewId) {
        setTimeout(() => {
            viewVehicle(parseInt(viewId));
            // Clean up URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 500);
    }
});
</script>
@endpush
