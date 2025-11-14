@extends('layouts.app')

@section('page-title', 'Requests')

@section('content')
<div class="space-y-6">
    <!-- Page Header with Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Sticker Requests</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A] mt-1">Request new stickers or view existing requests</p>
        </div>
        <button onclick="openRequestModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
            <x-heroicon-o-plus class="w-4 h-4 inline-block mr-2" />
            New Request
        </button>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <form method="GET" action="{{ route('user.requests') }}" class="flex flex-col lg:flex-row gap-3 md:gap-4">
            <!-- Status Filter -->
            <div class="flex-1">
                <label class="form-label">Status</label>
                <select name="status" id="status" class="form-input w-full">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status', 'pending') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status', 'pending') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <!-- Vehicle Filter -->
            <div class="flex-1">
                <label class="form-label">Vehicle</label>
                <select name="vehicle" id="vehicle" class="form-input w-full">
                    <option value="">All Vehicles</option>
                    @foreach($userVehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ request('vehicle') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->vehicleType->name }} - {{ $vehicle->plate_no ?? 'No Plate' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div class="flex-1">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-input w-full">
            </div>

            <!-- Reset Button -->
            <div class="flex-shrink-0">
                <label class="form-label opacity-0 hidden sm:block">Reset</label>
                <button type="button" onclick="window.location.href='{{ route('user.requests') }}'" class="btn btn-secondary !h-[38px] w-full lg:w-auto px-6">Reset</button>
            </div>
        </form>
    </div>

    <!-- Requests Table -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 md:mb-6">
            <div class="flex items-center gap-3">
                <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Sticker Requests</h3>
            </div>
            <div class="flex flex-wrap items-center gap-3 md:gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">Show:</span>
                    <select id="pagination-limit" class="form-input !h-[38px] !py-1 !px-3 text-xs md:text-sm" onchange="window.location.href='{{ route('user.requests') }}?per_page=' + this.value">
                        <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="20" {{ request('per_page', 15) == 20 ? 'selected' : '' }}>20</option>
                        <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="requests-table-container">
            @include('user.partials.requests-table', ['requests' => $requests])
        </div>
    </div>
</div>
@endsection

<!-- New Request Modal -->
<div id="requestModal" class="modal-backdrop hidden z-[100]" onclick="if(event.target === this) closeRequestModal()">
    <div class="modal-container flex flex-col max-w-2xl" style="max-height: 90vh;">
        <div class="modal-header flex-shrink-0 flex justify-between items-center">
            <h2 class="modal-title">New Sticker Request</h2>
            <button onclick="closeRequestModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="requestForm" method="POST" action="{{ route('user.requests.store') }}" class="flex flex-col flex-1">
            @csrf
            
            <div class="modal-body flex-1 overflow-y-auto space-y-6">
                <!-- Display validation errors -->
                @if ($errors->any())
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <div class="flex">
                            <x-heroicon-o-exclamation-circle class="w-5 h-5 text-red-400 mr-2 flex-shrink-0 mt-0.5" />
                            <div>
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Please fix the following errors:</h3>
                                <ul class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                <!-- Vehicle Selection -->
                <div class="space-y-4">
                    <div>
                        <label for="vehicle_id" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Select Vehicle</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-input" required>
                            <option value="">Choose a vehicle</option>
                            @foreach($userVehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">
                                    {{ $vehicle->vehicleType->name }} - {{ $vehicle->plate_no ?? $vehicle->color . ' ' . $vehicle->number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Hidden Request Type (always 'new') -->
                <input type="hidden" name="request_type" value="new">

                <!-- Reason -->
                <div>
                    <label for="reason" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                        Reason for Request <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" id="reason" rows="4" class="form-input" 
                              placeholder="Please provide a detailed reason for your sticker request..." required></textarea>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">
                        Be specific about why you need this sticker (e.g., new vehicle registration, expired sticker, lost sticker, etc.)
                    </p>
                </div>

                <!-- Additional Information -->
                <div>
                    <label for="additional_info" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                        Additional Information <span class="text-gray-400">(Optional)</span>
                    </label>
                    <textarea name="additional_info" id="additional_info" rows="3" class="form-input" 
                              placeholder="Any additional information that might help process your request..."></textarea>
                </div>

                <!-- Terms and Conditions -->
                <div class="bg-gray-50 dark:bg-[#161615] p-4 rounded-lg">
                    <div class="flex items-start">
                        <input type="checkbox" id="terms_accepted" name="terms_accepted" class="mt-1 mr-3" required>
                        <label for="terms_accepted" class="text-sm text-[#1b1b18] dark:text-[#EDEDEC]">
                            I understand that:
                            <ul class="list-disc list-inside mt-2 text-xs text-[#706f6c] dark:text-[#A1A09A] space-y-1">
                                <li>This request will be reviewed by the administration</li>
                                <li>Processing may take 3-5 business days</li>
                                <li>I will be notified via email about the status</li>
                                <li>False information may result in request rejection</li>
                            </ul>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-shrink-0">
                <button type="button" onclick="closeRequestModal()" class="btn btn-secondary mr-3">Cancel</button>
                <button type="submit" id="submitButton" class="btn btn-primary">
                    <span id="submitButtonText">Submit Request</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Modal -->
<div id="requestDetailsModal" class="modal-backdrop hidden z-[100]" onclick="if(event.target === this) closeRequestDetailsModal()">
    <div class="modal-container flex flex-col" style="max-width: 900px; width: 95%; max-height: 90vh;">
        <div class="modal-header flex-shrink-0 flex justify-between items-center">
            <h2 class="modal-title">Request Details</h2>
            <button onclick="closeRequestDetailsModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body flex-1 overflow-y-auto" id="requestDetailsContent">
            <!-- Content will be loaded here -->
        </div>
        <div class="modal-footer flex-shrink-0">
            <button onclick="closeRequestDetailsModal()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div id="cancelModal" class="modal-backdrop hidden z-[100]" onclick="if(event.target === this) closeCancelModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Cancel Request
            </h2>
        </div>
        <div class="modal-body">
            <p class="text-[#1b1b18] dark:text-[#EDEDEC]">Are you sure you want to cancel this request? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button onclick="closeCancelModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmCancelRequest()" class="btn btn-danger">Cancel Request</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Real-time filtering variables
let filterTimeout = null;
let currentPage = 1;

// Load requests with filters
function loadRequests(page = 1) {
    const status = document.getElementById('status').value;
    const vehicle = document.getElementById('vehicle').value;
    const dateFrom = document.getElementById('date_from').value;
    
    const params = new URLSearchParams({
        status: status,
        vehicle: vehicle,
        date_from: dateFrom,
        page: page
    });
    
    // Show loading state
    const container = document.getElementById('requests-table-container');
    container.style.opacity = '0.6';
    
    fetch(`{{ route('user.requests.data') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                container.innerHTML = data.html;
                container.style.opacity = '1';
                currentPage = page;
            }
        })
        .catch(error => {
            console.error('Error loading requests:', error);
            container.style.opacity = '1';
        });
}

// Load specific page
function loadPage(page) {
    loadRequests(page);
}

// Setup real-time filtering
function setupFilters() {
    const statusFilter = document.getElementById('status');
    const vehicleFilter = document.getElementById('vehicle');
    const dateFilter = document.getElementById('date_from');
    
    [statusFilter, vehicleFilter, dateFilter].forEach(filter => {
        if (filter) {
            filter.addEventListener('change', () => {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    loadRequests(1);
                }, 300);
            });
        }
    });
}

// Make functions globally available
window.openRequestModal = function() {
    console.log('Opening request modal...');
    try {
        const modal = document.getElementById('requestModal');
        if (!modal) {
            console.error('Request modal not found');
            return;
        }
        modal.classList.remove('hidden');
    } catch (error) {
        console.error('Error opening modal:', error);
    }
}

window.closeRequestModal = function() {
    try {
        document.getElementById('requestModal').classList.add('hidden');
        document.getElementById('requestForm').reset();
    } catch (error) {
        console.error('Error closing modal:', error);
    }
}


window.viewRequestDetails = function(requestId) {
    document.getElementById('requestDetailsModal').classList.remove('hidden');
    
    // Load request details via AJAX
    fetch(`/requests/${requestId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const statusBadge = data.status === 'pending' ? 'bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200' :
                               data.status === 'approved' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                               'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
            
            document.getElementById('requestDetailsContent').innerHTML = `
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Side - Sticker Preview -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-50 dark:bg-[#161615] rounded-lg p-6 text-center sticky top-0">
                            <h4 class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Vehicle Sticker</h4>
                            <div class="inline-block bg-white dark:bg-[#1a1a1a] rounded-lg p-4 border-2 border-dashed border-[#e3e3e0] dark:border-[#3E3E3A] w-full max-w-xs">
                                ${data.vehicle.sticker ? 
                                    `<img src="${data.vehicle.sticker}" alt="Vehicle Sticker" class="w-full h-auto rounded">` :
                                    `<div class="w-full h-32 flex items-center justify-center text-[#706f6c] dark:text-[#A1A09A]">
                                        <div class="text-center">
                                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            <p class="text-sm">No Sticker Available</p>
                                        </div>
                                    </div>`
                                }
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side - Request Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Request Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Request ID</label>
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">#${data.id}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Status</label>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusBadge}">
                                        ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Submitted</label>
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${new Date(data.created_at).toLocaleDateString('en-US', { 
                                        year: 'numeric', month: 'long', day: 'numeric', 
                                        hour: '2-digit', minute: '2-digit' 
                                    })}</p>
                                </div>
                            </div>
                            

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Vehicle Type</label>
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.vehicle.vehicle_type.name}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Plate Number</label>
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.vehicle.plate_no || 'Not specified'}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Vehicle Color</label>
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">${data.vehicle.color || 'Not specified'}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Request Details -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Reason for Request</label>
                                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] bg-gray-50 dark:bg-[#161615] p-3 rounded-lg">${data.reason || 'No reason provided'}</p>
                            </div>
                            ${data.additional_info ? `
                            <div>
                                <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Additional Information</label>
                                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] bg-gray-50 dark:bg-[#161615] p-3 rounded-lg">${data.additional_info}</p>
                            </div>
                            ` : ''}
                            ${data.admin_notes ? `
                            <div>
                                <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-1">Admin Notes</label>
                                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-200 dark:border-blue-800">${data.admin_notes}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error loading request details:', error);
            document.getElementById('requestDetailsContent').innerHTML = `
                <div class="text-center py-8">
                    <p class="text-red-600 dark:text-red-400">Error loading request details. Please try again.</p>
                </div>
            `;
        });
}

window.closeRequestDetailsModal = function() {
    document.getElementById('requestDetailsModal').classList.add('hidden');
}

let currentCancelRequestId = null;

window.openCancelModal = function(requestId) {
    currentCancelRequestId = requestId;
    document.getElementById('cancelModal').classList.remove('hidden');
}

window.closeCancelModal = function() {
    document.getElementById('cancelModal').classList.add('hidden');
    currentCancelRequestId = null;
}

window.confirmCancelRequest = function() {
    if (!currentCancelRequestId) return;
    
    fetch(`/requests/${currentCancelRequestId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCancelModal();
            location.reload();
        } else {
            alert('Error canceling request: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error canceling request:', error);
        alert('Error canceling request. Please try again.');
    });
}

// Close modals when clicking outside
document.getElementById('requestModal').addEventListener('click', function(e) {
    if (e.target === this) closeRequestModal();
});

document.getElementById('requestDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) closeRequestDetailsModal();
});

// Test function availability
console.log('Sticker requests JavaScript loaded');
console.log('openRequestModal available:', typeof window.openRequestModal);

// Auto-select vehicle if provided in URL
document.addEventListener('DOMContentLoaded', function() {
    // Setup real-time filtering
    setupFilters();
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('vehicle_id')) {
        openRequestModal();
    }

    // Handle form submission with fresh CSRF token
    const requestForm = document.getElementById('requestForm');
    if (requestForm) {
        requestForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Form submitting...');
            
            // Submit single request following admin pattern
            const formData = new FormData(requestForm);
            
            try {
                const submitResponse = await fetch(requestForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
            
            if (submitResponse.ok) {
                // Success - handle JSON response
                const responseData = await submitResponse.json();
                if (responseData.success) {
                    alert(responseData.message);
                    closeRequestModal();
                    window.location.reload(); // Reload to show success message
                }
            } else if (submitResponse.status === 422) {
                // Validation errors
                const errorData = await submitResponse.json();
                console.error('Validation errors:', errorData);
                
                let errorMessage = 'Please fix the following errors:\n';
                if (errorData.errors) {
                    Object.values(errorData.errors).forEach(errors => {
                        errors.forEach(error => {
                            errorMessage += '• ' + error + '\n';
                        });
                    });
                } else if (errorData.message) {
                    errorMessage = errorData.message;
                }
                
                alert(errorMessage);
            } else if (submitResponse.status === 419) {
                // CSRF error
                alert('Session expired. Please refresh the page and try again.');
                window.location.reload();
            } else {
                console.error('Submission failed:', submitResponse.status);
                alert('Submission failed. Please try again.');
            }
            } catch (error) {
                console.error('Submission error:', error);
                // Fallback to normal form submission
                requestForm.submit();
            }
        });
    }
    
    // Auto-open modal if there are validation errors
    @if ($errors->any())
        openRequestModal();
    @endif
});
</script>
@endpush
