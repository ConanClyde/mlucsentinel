<!-- Vehicle Type Settings -->
<div id="content-vehicle-type" class="settings-content hidden bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle Type Management</h3>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Live Updates:</span>
                <div id="vehicle-types-connection-status" class="w-3 h-3 rounded-full bg-red-500"></div>
            </div>
            <button onclick="openAddVehicleTypeModal()" class="btn btn-primary text-sm">
                Add
            </button>
        </div>
    </div>

    <!-- Vehicle Types Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-[#161615] border-y border-[#e3e3e0] dark:border-[#3E3E3A]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Vehicle Type Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Requires Plate</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Created At</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="vehicle-type-table-body" class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                @forelse($vehicleTypes as $vehicleType)
                <tr class="hover:bg-gray-50 dark:hover:bg-[#2a2a2a] transition-colors" data-vehicle-type-id="{{ $vehicleType->id }}">
                    <td class="px-4 py-3">
                        <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $vehicleType->name }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $vehicleType->requires_plate ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200' }}">
                            {{ $vehicleType->requires_plate ? 'Yes' : 'No' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $vehicleType->created_at->format('M d, Y') }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="editVehicleType({{ $vehicleType->id }})" class="btn-edit" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="deleteVehicleType({{ $vehicleType->id }})" class="btn-delete" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        No vehicle types found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

