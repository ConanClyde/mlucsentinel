@extends('layouts.app')

@section('page-title', 'Report History')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Violation Report History</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A] mt-1">View all violation reports associated with your vehicles</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <form method="GET" action="{{ route('user.reports') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Status</label>
                <select name="status" id="status" class="form-input">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                </select>
            </div>

            <!-- Vehicle Filter -->
            <div>
                <label for="vehicle" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Vehicle</label>
                <select name="vehicle" id="vehicle" class="form-input">
                    <option value="">All Vehicles</option>
                    @foreach($userVehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ request('vehicle') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->vehicleType->name }} - {{ $vehicle->plate_no ?? 'No Plate' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">From Date</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-input">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                    Filter Reports
                </button>
            </div>
        </form>
    </div>

    <!-- Reports List -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A]">
        @if($reports->count() > 0)
            <!-- Reports Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                    <thead class="bg-gray-50 dark:bg-[#161615]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">
                                Report Details
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">
                                Vehicle
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">
                                Violation
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#1a1a1a] divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                        @foreach($reports as $report)
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#161615]">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                            Report #{{ $report->id }}
                                        </div>
                                        <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                            by {{ $report->reportedBy->first_name }} {{ $report->reportedBy->last_name }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ $report->violatorVehicle->vehicleType->name }}
                                    </div>
                                    <div class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        {{ $report->violatorVehicle->plate_no ?? 'No Plate' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ $report->violationType->name }}
                                    </div>
                                    @if($report->description)
                                        <div class="text-sm text-[#706f6c] dark:text-[#A1A09A] max-w-xs truncate">
                                            {{ $report->description }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($report->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Pending
                                        </span>
                                    @elseif($report->status === 'resolved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Resolved
                                        </span>
                                    @elseif($report->status === 'dismissed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                            Dismissed
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    {{ $report->created_at->format('M j, Y') }}
                                    <div class="text-xs">{{ $report->created_at->format('g:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewReportDetails({{ $report->id }})" 
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($reports->hasPages())
                <div class="px-6 py-4 border-t border-[#e3e3e0] dark:border-[#3E3E3A]">
                    {{ $reports->links() }}
                </div>
            @endif
        @else
            <!-- No Reports -->
            <div class="p-12 text-center">
                <x-heroicon-o-document-text class="w-16 h-16 text-[#706f6c] dark:text-[#A1A09A] mx-auto mb-4" />
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">No Violation Reports</h3>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    @if(request()->hasAny(['status', 'vehicle', 'date_from']))
                        No reports found matching your filter criteria.
                    @else
                        You have no violation reports. Keep up the good driving!
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Report Details Modal -->
<div id="reportDetailsModal" class="modal-backdrop hidden">
    <div class="modal-container max-w-2xl">
        <div class="modal-header flex justify-between items-center">
            <h2 class="modal-title">Report Details</h2>
            <button onclick="closeReportModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <div class="modal-body" id="reportDetailsContent">
            <!-- Content will be loaded here -->
        </div>
        <div class="modal-footer">
            <button onclick="closeReportModal()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<script>
function viewReportDetails(reportId) {
    // Show modal
    document.getElementById('reportDetailsModal').classList.remove('hidden');
    
    // Load report details via AJAX
    fetch(`/user/reports/${reportId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('reportDetailsContent').innerHTML = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Report ID</label>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">#${data.id}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Status</label>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.status}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle</label>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.vehicle.type} - ${data.vehicle.plate_no || 'No Plate'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Violation Type</label>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.violation_type}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Reporter</label>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.reporter.name}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Date & Time</label>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.date_time}</p>
                        </div>
                    </div>
                    ${data.description ? `
                        <div>
                            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Description</label>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.description}</p>
                        </div>
                    ` : ''}
                    ${data.location ? `
                        <div>
                            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Location</label>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.location}</p>
                        </div>
                    ` : ''}
                    ${data.evidence_photos && data.evidence_photos.length > 0 ? `
                        <div>
                            <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Evidence Photos</label>
                            <div class="grid grid-cols-2 gap-2">
                                ${data.evidence_photos.map(photo => `
                                    <img src="${photo}" alt="Evidence" class="w-full h-32 object-cover rounded-md border border-[#e3e3e0] dark:border-[#3E3E3A]">
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        })
        .catch(error => {
            console.error('Error loading report details:', error);
            document.getElementById('reportDetailsContent').innerHTML = `
                <div class="text-center py-8">
                    <p class="text-red-600 dark:text-red-400">Error loading report details. Please try again.</p>
                </div>
            `;
        });
}

function closeReportModal() {
    document.getElementById('reportDetailsModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('reportDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReportModal();
    }
});
</script>
@endsection
