@extends('layouts.app')

@section('page-title', 'Students Management')

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Filter Card -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex flex-col lg:flex-row gap-3 md:gap-4">
            <!-- Search -->
            <div class="flex-1">
                <label class="form-label">Search</label>
                <input type="text" id="search-input" class="form-input w-full" placeholder="Search by name, email, or student ID...">
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

            <!-- College Filter -->
            <div class="flex-1">
                <label class="form-label">College</label>
                <select id="college-filter" class="form-input w-full">
                    <option value="">All Colleges</option>
                    @php
                        $colleges = \App\Models\College::orderBy('name')->get();
                    @endphp
                    @foreach($colleges as $college)
                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Button -->
            <div class="flex-shrink-0">
                <label class="form-label opacity-0 hidden sm:block">Reset</label>
                <button id="reset-filters" class="btn btn-secondary !h-[38px] w-full lg:w-auto px-6">Reset</button>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 md:mb-6">
            <h3 class="text-base md:text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Students List</h3>
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
                @if(Auth::user()->isGlobalAdministrator() || Auth::user()->isSecurityAdmin())
                @endif
            </div>
        </div>

        <!-- Bulk Actions Bar (Floating Lower Center) -->
        <div id="bulk-actions-bar" class="hidden fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 shadow-2xl rounded-xl border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#1a1a1a] px-6 py-4">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 pr-4 border-r border-[#e3e3e0] dark:border-[#3E3E3A]">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                            <span id="selected-count">0</span> selected
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="clearSelection()" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button onclick="bulkActivate()" class="btn btn-success !inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Activate</span>
                    </button>
                    <button onclick="bulkDeactivate()" class="btn btn-warning !inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Deactivate</span>
                    </button>
                    <button onclick="confirmBulkDelete()" class="btn btn-danger !inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                        @if(Auth::user()->hasAnyPrivilege(['edit_students', 'delete_students']))
                        <th class="text-center py-2 px-3 w-12">
                            <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        </th>
                        @endif
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Name</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Email</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Student ID</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">College</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Status</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Created</th>
                        <th class="text-center py-2 px-3 text-xs font-medium text-[#706f6c] dark:text-[#A1A09A] uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                    @forelse($students as $student)
                    <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] hover:bg-gray-50 dark:hover:bg-[#161615]" data-id="{{ $student->user_id }}">
                        @if(Auth::user()->hasAnyPrivilege(['edit_students', 'delete_students']))
                        <td class="text-center py-2 px-3">
                            <input type="checkbox" class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" value="{{ $student->user_id }}" onchange="updateBulkActions()">
                        </td>
                        @endif
                        <td class="py-2 px-3">
                            <div class="flex items-center">
                                @php
                                    $colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'];
                                    $firstLetter = strtoupper(substr($student->user->first_name ?? 'U', 0, 1));
                                    $hash = ord($firstLetter);
                                    $avatarColor = $colors[$hash % count($colors)];
                                @endphp
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 text-white font-semibold text-xs flex-shrink-0" style="background-color: {{ $avatarColor }}">
                                    {{ $firstLetter }}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $student->user->first_name ?? '' }} {{ $student->user->last_name ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $student->user->email ?? 'N/A' }}</td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $student->student_id ?? 'N/A' }}</td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $student->college->name ?? 'No College' }}</td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ ($student->user->is_active ?? false) ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                                {{ ($student->user->is_active ?? false) ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-2 px-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $student->created_at->format('M d, Y') }}</td>
                        <td class="py-2 px-3">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="viewStudent({{ $student->id }})" class="btn-view" title="View">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                                @if(Auth::user()->isGlobalAdministrator() || Auth::user()->isSecurityAdmin())
                                    <button onclick="openEditModal({{ $student->id }})" class="btn-edit" title="Edit">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.829-2.828z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteStudent({{ $student->id }})" class="btn-delete" title="Delete">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ Auth::user()->hasAnyPrivilege(['edit_students', 'delete_students']) ? '8' : '7' }}" class="py-8 px-4 text-center text-[#706f6c] dark:text-[#A1A09A]">
                            No students found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div id="pagination-controls" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 md:mt-6">
            <p class="text-xs md:text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Showing <span id="showing-start">1</span>-<span id="showing-end">10</span> of <span id="total-count">0</span> students
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

<!-- View Student Modal -->
<div id="viewModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeViewModal()">
    <div class="modal-container-wide">
        <div class="modal-header">
            <h2 class="modal-title">Student Details</h2>
        </div>
        <div class="modal-body max-h-[70vh] overflow-y-auto">
            <div id="viewModalContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeViewModal()" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeDeleteModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-red-500 flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Student
            </h2>
        </div>
        <div class="modal-body">
            <p id="deleteModalMessage"></p>
        </div>
        <div class="modal-footer">
            <button onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmDeleteStudent()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeEditModal()">
    <div class="modal-container-wide">
        <div class="modal-header">
            <h2 class="modal-title">Edit Student</h2>
        </div>
        <form id="editForm">
            <div class="modal-body max-h-[70vh] overflow-y-auto">
                <input type="hidden" id="edit_student_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-group">
                        <label class="form-label">First Name <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_first_name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_last_name" class="form-input" required>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-group">
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="edit_email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Student ID <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_student_id_input" class="form-input" required>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-group">
                        <label class="form-label">License Number <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_license_no" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Program <span class="text-red-500">*</span></label>
                        <select id="edit_program_id" class="form-input" required>
                            <option value="">Select Program</option>
                            @foreach($colleges as $college)
                                <optgroup label="{{ $college->name }}">
                                    @foreach($college->programs as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label">Status <span class="text-red-500">*</span></label>
                    <select id="edit_is_active" class="form-input" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <!-- License Image Upload -->
                <div class="form-group mb-4">
                    <label class="form-label">License Image</label>
                    <div class="bg-gray-50 dark:bg-[#161615] p-4 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <!-- Upload Options -->
                        <div id="editLicenseUploadOptions" class="flex gap-4 mb-4">
                            <button type="button" class="btn btn-info" onclick="openEditLicenseCameraModal()">
                                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Take Photo
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit_license_image').click()">
                                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                Upload File
                            </button>
                        </div>

                        <!-- Hidden File Input -->
                        <input type="file" id="edit_license_image" name="license_image" accept="image/*" class="hidden" onchange="handleEditLicenseFileUpload(event)">

                        <!-- Current License Image Preview -->
                        <div id="editCurrentLicenseImage" class="hidden mb-4">
                            <h4 class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Current License Image</h4>
                            <div class="relative inline-block">
                                <img id="editCurrentLicenseImageSrc" src="" alt="Current License" class="w-full max-w-md rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                            </div>
                        </div>

                        <!-- New License Image Preview -->
                        <div id="editLicenseImagePreview" class="hidden">
                            <h4 class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">New License Image</h4>
                            <div class="relative inline-block">
                                <img id="editLicensePreviewImage" src="" alt="License Preview" class="w-full max-w-md rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                                <button type="button" onclick="removeEditLicensePreview()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                        
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-2">Upload a clear photo of driver's license (max 2MB). Leave empty to keep current image.</p>
                    </div>
                </div>
                
                <hr class="my-6 border-[#e3e3e0] dark:border-[#3E3E3A]">
                
                <!-- Existing Vehicles (Read-only) -->
                <div id="existingVehiclesSection" class="mb-4">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-3">Existing Vehicles</h3>
                    <div id="existingVehiclesDisplay" class="space-y-3">
                        <!-- Existing vehicles displayed here -->
                    </div>
                    <p id="noVehiclesMessage" class="text-sm text-[#706f6c] dark:text-[#A1A09A] italic"></p>
                </div>
                
                <!-- New Vehicles Section -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Add New Vehicles</h3>
                        <button type="button" id="editAddVehicleBtn" class="btn btn-primary btn-sm" onclick="addEditVehicle()">
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add Vehicle
                        </button>
                    </div>
                    <div id="editVehiclesContainer" class="space-y-4">
                        <!-- New vehicles will be added here -->
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

<!-- Edit License Camera Modal -->
<div id="editLicenseCameraModal" class="modal-backdrop hidden">
    <div class="camera-container max-w-4xl">
        <div class="modal-header flex justify-between items-center">
            <h2 class="modal-title">Camera</h2>
            <button onclick="closeEditLicenseCameraModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="modal-body p-0">
            <video id="editLicenseCameraVideo" autoplay playsinline class="w-full h-auto bg-black max-h-[70vh] sm:max-h-[80vh] object-cover"></video>
            <canvas id="editLicenseCameraCanvas" class="hidden"></canvas>
        </div>
        <div class="modal-footer">
            <button class="btn-camera" onclick="captureEditLicensePhoto()">
                <svg class="w-6 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </button>
        </div>
    </div>
</div>

<!-- Bulk Activate Confirmation Modal -->
<div id="bulkActivateModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeBulkActivateModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-[#1b1b18] dark:text-[#EDEDEC] flex items-center gap-2">
                <x-heroicon-o-check-circle class="modal-icon-success" />
                Activate Students
            </h2>
        </div>
        <div class="modal-body">
            <p id="bulkActivateMessage" class="text-[#1b1b18] dark:text-[#EDEDEC]"></p>
        </div>
        <div class="modal-footer">
            <button onclick="closeBulkActivateModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="executeBulkActivate()" class="btn btn-success">Activate</button>
        </div>
    </div>
</div>

<!-- Bulk Deactivate Confirmation Modal -->
<div id="bulkDeactivateModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeBulkDeactivateModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-[#1b1b18] dark:text-[#EDEDEC] flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-warning" />
                Deactivate Students
            </h2>
        </div>
        <div class="modal-body">
            <p id="bulkDeactivateMessage" class="text-[#1b1b18] dark:text-[#EDEDEC]"></p>
        </div>
        <div class="modal-footer">
            <button onclick="closeBulkDeactivateModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="executeBulkDeactivate()" class="btn btn-warning">Deactivate</button>
        </div>
    </div>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div id="bulkDeleteModal" class="modal-backdrop hidden" onclick="if(event.target === this) closeBulkDeleteModal()">
    <div class="modal-container">
        <div class="modal-header">
            <h2 class="modal-title text-[#1b1b18] dark:text-[#EDEDEC] flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="modal-icon-error" />
                Delete Students
            </h2>
        </div>
        <div class="modal-body">
            <p id="bulkDeleteMessage" class="text-[#1b1b18] dark:text-[#EDEDEC]"></p>
        </div>
        <div class="modal-footer">
            <button onclick="closeBulkDeleteModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="executeBulkDelete()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
// Initialize real-time updates using the StudentsRealtime module
let realtimeManager;
let students = @json($students->items());
let vehicleTypes = @json($vehicleTypes);
let deleteStudentId = null;
window.currentUserId = {{ auth()->id() }};
window.currentUser = {
    id: {{ auth()->id() }},
    first_name: '{{ auth()->user()->first_name }}',
    last_name: '{{ auth()->user()->last_name }}'
};


// Process students data to add proper image paths
students = students.map(function(student) {
    return {
        ...student,
        license_image: student.license_image ? (
            student.license_image.startsWith('/storage/') || student.license_image.startsWith('http') 
                ? student.license_image 
                : '/storage/' + student.license_image
        ) : null,
        vehicles: student.vehicles ? student.vehicles.map(function(vehicle) {
            return {
                ...vehicle,
                sticker_image: vehicle.sticker ? (vehicle.sticker.startsWith('/storage/') || vehicle.sticker.startsWith('http') ? vehicle.sticker : '/storage/' + vehicle.sticker) : null
            };
        }) : []
    };
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the real-time manager
    if (window.StudentsRealtime) {
        realtimeManager = new window.StudentsRealtime();
        realtimeManager.init(students);
        
        // Update local students array when real-time updates occur
        window.Echo.channel('students').listen('.student.updated', (event) => {
            // Use user_id to find the student (consistent with table rows)
            const index = students.findIndex(s => s.user_id === event.student.user_id || s.user?.id === event.student.user_id);
            
            // Process license_image path
            const processedStudent = {
                ...event.student,
                license_image: event.student.license_image && 
                    !event.student.license_image.startsWith('/storage/') && 
                    !event.student.license_image.startsWith('http') 
                    ? '/storage/' + event.student.license_image 
                    : event.student.license_image,
                vehicles: event.student.vehicles ? event.student.vehicles.map(function(vehicle) {
                    // Backend already sends sticker_image, so use it directly
                    // Fallback to processing vehicle.sticker if sticker_image doesn't exist
                    const stickerImage = vehicle.sticker_image || (
                        vehicle.sticker && 
                        !vehicle.sticker.startsWith('/storage/') && 
                        !vehicle.sticker.startsWith('http') 
                            ? '/storage/' + vehicle.sticker 
                            : vehicle.sticker
                    );
                    
                    return {
                        ...vehicle,
                        sticker_image: stickerImage
                    };
                }) : []
            };
            
            // Check if this is from current user's action (avoid duplicate notifications)
            const isCurrentUserAction = realtimeManager && realtimeManager.isCurrentUserAction(event.student.id);
            const editorName = event.editor?.first_name && event.editor?.last_name 
                ? `${event.editor.first_name} ${event.editor.last_name}` 
                : 'System';
            const isCurrentUser = editorName === (window.currentUser?.first_name + ' ' + window.currentUser?.last_name);
            
            if (event.action === 'deleted') {
                // Remove from array
            if (index !== -1) {
                    students.splice(index, 1);
                }
                // Remove from DOM using user_id (consistent with table rows)
                const row = document.querySelector(`tr[data-id="${event.student.user_id}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s ease-out';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                
                // Show browser notification if not current user's action
                if (realtimeManager && !isCurrentUserAction && !isCurrentUser) {
                    realtimeManager.showBrowserNotification(
                        'Student Removed',
                        `${editorName} removed ${processedStudent.user?.first_name} ${processedStudent.user?.last_name}`,
                        null,
                        'deleted'
                    );
                }
            } else if (index !== -1) {
                // Update existing student in array
                students[index] = processedStudent;
                
                // Update the row in the table using user_id (consistent with table rows)
                const row = document.querySelector(`tr[data-id="${event.student.user_id}"]`);
                if (row) {
                    // Determine column index based on whether checkbox column exists
                    const hasCheckbox = !!document.getElementById('select-all');
                    const statusColIndex = hasCheckbox ? 6 : 5;
                    
                    // Update status badge
                    const statusCell = row.querySelector(`td:nth-child(${statusColIndex})`);
                    if (statusCell) {
                        const isActive = processedStudent.user?.is_active ?? false;
                        statusCell.innerHTML = `
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${isActive ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'}">
                                ${isActive ? 'Active' : 'Inactive'}
                            </span>
                        `;
                    }
                    
                    // Highlight the row briefly to show it was updated
                    row.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
                    setTimeout(() => {
                        row.style.backgroundColor = '';
                    }, 1000);
                }
                
                // Show browser notification if not current user's action
                if (realtimeManager && !isCurrentUserAction && !isCurrentUser && event.action === 'updated') {
                    realtimeManager.showBrowserNotification(
                        'Student Updated',
                        `${editorName} updated Student ${processedStudent.user?.first_name} ${processedStudent.user?.last_name}`,
                        processedStudent.id,
                        'updated'
                    );
                }
                
                // If view modal is open for this student, refresh it
                const viewModal = document.getElementById('viewModal');
                if (!viewModal.classList.contains('hidden')) {
                    viewStudent(processedStudent.id);
                }
                
                // If edit modal is open for this student, refresh the existing vehicles display
                const editModal = document.getElementById('editModal');
                if (!editModal.classList.contains('hidden')) {
                    const editStudentId = document.getElementById('edit_student_id').value;
                    if (editStudentId == processedStudent.id) {
                        currentEditStudentVehicles = processedStudent.vehicles || [];
                        displayExistingVehicles();
                    }
                }
            } else if (event.action === 'created') {
                students.unshift(processedStudent);
            }
        });
    } else {
        console.error('StudentsRealtime module not loaded');
    }

    // Check if we need to open view modal from notification
    const urlParams = new URLSearchParams(window.location.search);
    const viewId = urlParams.get('view');
    if (viewId) {
        setTimeout(() => {
            viewStudent(parseInt(viewId));
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 500);
    }

    // Filter functionality
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const collegeFilter = document.getElementById('college-filter');
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
    
    collegeFilter.addEventListener('change', function() {
        currentPage = 1;
        applyPagination();
    });

    resetButton.addEventListener('click', function() {
        searchInput.value = '';
        statusFilter.value = '';
        collegeFilter.value = '';
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
        const rows = document.querySelectorAll('#studentsTableBody tr');
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
            const studentId = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
            const statusBadge = row.querySelector('td:nth-child(5) span');
            const isActive = statusBadge?.textContent.trim() === 'Active';
            
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const collegeValue = collegeFilter.value;
            
            const studentRowId = row.getAttribute('data-id');
            const student = students.find(s => s.user_id == studentRowId || s.user?.id == studentRowId);
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm) || studentId.includes(searchTerm);
            const matchesStatus = statusValue === '' || 
                                (statusValue === '1' && isActive) || 
                                (statusValue === '0' && !isActive);
            const matchesCollege = collegeValue === '' || (student && student.college_id == collegeValue);
            
            if (matchesSearch && matchesStatus && matchesCollege) {
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
            const studentId = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
            const statusBadge = row.querySelector('td:nth-child(5) span');
            const isActive = statusBadge?.textContent.trim() === 'Active';
            
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const collegeValue = collegeFilter.value;
            
            const studentRowId = row.getAttribute('data-id');
            const student = students.find(s => s.user_id == studentRowId || s.user?.id == studentRowId);
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm) || studentId.includes(searchTerm);
            const matchesStatus = statusValue === '' || 
                                (statusValue === '1' && isActive) || 
                                (statusValue === '0' && !isActive);
            const matchesCollege = collegeValue === '' || (student && student.college_id == collegeValue);
            
            if (!matchesSearch || !matchesStatus || !matchesCollege) {
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
        const rows = document.querySelectorAll('#studentsTableBody tr');
        let totalFiltered = 0;
        
        rows.forEach((row) => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) {
                return;
            }
            
            const name = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
            const email = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const studentId = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
            const statusBadge = row.querySelector('td:nth-child(5) span');
            const isActive = statusBadge?.textContent.trim() === 'Active';
            
            const searchTerm = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value;
            const collegeValue = collegeFilter.value;
            
            const studentRowId = row.getAttribute('data-id');
            const student = students.find(s => s.user_id == studentRowId || s.user?.id == studentRowId);
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm) || studentId.includes(searchTerm);
            const matchesStatus = statusValue === '' || 
                                (statusValue === '1' && isActive) || 
                                (statusValue === '0' && !isActive);
            const matchesCollege = collegeValue === '' || (student && student.college_id == collegeValue);
            
            if (matchesSearch && matchesStatus && matchesCollege) {
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

// View Student
function viewStudent(id) {
    const student = students.find(s => s.id === id);
    if (!student) return;
    
    const modal = document.getElementById('viewModal');
    const content = document.getElementById('viewModalContent');
    
    let vehiclesHtml = '';
    if (student.vehicles && student.vehicles.length > 0) {
        vehiclesHtml = '<hr class="my-6 border-[#e3e3e0] dark:border-[#3E3E3A]"><div><h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Vehicles</h3><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';
        student.vehicles.forEach((vehicle, index) => {
            const vehicleType = vehicleTypes.find(vt => vt.id === vehicle.type_id);
            const typeName = vehicleType ? vehicleType.name : 'Unknown';
            
            vehiclesHtml += `
                <div class="vehicle-card-compact">
                    <div class="flex items-start justify-between mb-2">
                        <h4 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC] text-sm">Vehicle ${index + 1}</h4>
                    </div>
                    <div class="grid grid-cols-1 gap-2 mb-3">
                        <div>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Vehicle Type</p>
                            <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${typeName}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Plate Number</p>
                            <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${vehicle.plate_no || 'N/A (Electric)'}</p>
                        </div>
                    </div>
                    ${vehicle.sticker_image ? `
                        <img src="${vehicle.sticker_image}" alt="Sticker" class="vehicle-sticker-image">
                    ` : ''}
                </div>
            `;
        });
        vehiclesHtml += '</div></div>';
    }
    
    content.innerHTML = `
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Full Name</h3>
                    <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${student.user.first_name} ${student.user.last_name}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Email</h3>
                    <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${student.user.email}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Student ID</h3>
                    <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${student.student_id}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">License Number</h3>
                    <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${student.license_no || 'N/A'}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Program</h3>
                    <p class="text-[#1b1b18] dark:text-[#EDEDEC]">${student.program?.name || 'No Program'}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Status</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${student.user.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'}">
                        ${student.user.is_active ? 'Active' : 'Inactive'}
                    </span>
                </div>
            </div>
            ${student.license_image ? `
            <div>
                <h3 class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">License Image</h3>
                <div class="image-container">
                    <img src="${student.license_image}" alt="License" class="license-image">
                </div>
            </div>
            ` : ''}
            ${vehiclesHtml}
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

function deleteStudent(id) {
    const student = students.find(s => s.id === id);
    if (!student) return;
    
    deleteStudentId = id;
    document.getElementById('deleteModalMessage').textContent = 
        `Are you sure you want to delete ${student.user.first_name} ${student.user.last_name}? This action cannot be undone.`;
    
    if (realtimeManager) {
        realtimeManager.markUserAction(id);
    }
    
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteStudentId = null;
}

function confirmDeleteStudent() {
    if (!deleteStudentId) return;
    
    fetch(`/users/students/${deleteStudentId}`, {
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
            alert(data.message || 'Failed to delete student');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting');
    });
}

// Edit Student Modal
let editVehicleCount = 0;
let currentEditStudentVehicles = [];
let vehiclesToDelete = []; // Track vehicles marked for deletion

function displayExistingVehicles() {
    const displayContainer = document.getElementById('existingVehiclesDisplay');
    const noVehiclesMsg = document.getElementById('noVehiclesMessage');
    
    if (!currentEditStudentVehicles || currentEditStudentVehicles.length === 0) {
        displayContainer.innerHTML = '';
        noVehiclesMsg.textContent = 'No vehicles registered yet';
        return;
    }
    
    noVehiclesMsg.textContent = '';
    let vehiclesHtml = '';
    
    currentEditStudentVehicles.forEach((vehicle, index) => {
        const vehicleType = vehicleTypes.find(vt => vt.id === vehicle.type_id);
        const typeName = vehicleType ? vehicleType.name : 'Unknown';
        
        const isMarkedForDeletion = vehiclesToDelete.includes(vehicle.id);
        const opacityClass = isMarkedForDeletion ? 'opacity-50' : '';
        
        vehiclesHtml += `
            <div class="bg-[#FAFAFA] dark:bg-[#1E1E1D] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg p-4 existing-vehicle-card ${opacityClass}" data-vehicle-id="${vehicle.id}">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle ${index + 1}${isMarkedForDeletion ? ' (Will be deleted)' : ''}</h4>
                    <button type="button" onclick="markVehicleForDeletion(${vehicle.id})" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-200" title="${isMarkedForDeletion ? 'Undo Delete' : 'Mark for Deletion'}">
                        ${isMarkedForDeletion ? `
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        ` : `
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        `}
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-1">Vehicle Type</p>
                        <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${typeName}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-1">Plate Number</p>
                        <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${vehicle.plate_no || 'N/A (Electric)'}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-1">Sticker Color</p>
                        <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] capitalize">${vehicle.color || 'N/A'}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-1">Sticker Number</p>
                        <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${vehicle.number || 'N/A'}</p>
                    </div>
                </div>
            </div>
        `;
    });
    
    displayContainer.innerHTML = vehiclesHtml;
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function addEditVehicle() {
    // Count vehicles that are NOT marked for deletion
    const activeVehicles = currentEditStudentVehicles.filter(v => !vehiclesToDelete.includes(v.id)).length;
    const totalVehicles = activeVehicles + editVehicleCount;
    
    if (totalVehicles >= 3) {
        alert('Maximum 3 vehicles allowed per student');
        return;
    }
    
    const vehiclesContainer = document.getElementById('editVehiclesContainer');
    
    const vehicleHtml = `
        <div class="bg-[#FAFAFA] dark:bg-[#1E1E1D] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg p-4 vehicle-card" data-vehicle-index="${editVehicleCount}">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">New Vehicle ${editVehicleCount + 1}</h4>
                <button type="button" onclick="removeEditVehicle(this)" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-200" title="Remove Vehicle">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Vehicle Type <span class="text-red-500">*</span></label>
                    <select name="vehicles[${editVehicleCount}][type_id]" class="form-input" required onchange="togglePlateNumberInput(this)">
                        <option value="">Select Vehicle Type</option>
                        ${vehicleTypes.map(type => `<option value="${type.id}" data-requires-plate="${type.requires_plate ? '1' : '0'}">${type.name}</option>`).join('')}
                    </select>
                </div>
                <div class="form-group" id="plate-number-group-${editVehicleCount}">
                    <label class="form-label">Plate Number</label>
                    <input type="text" name="vehicles[${editVehicleCount}][plate_no]" class="form-input" placeholder="Leave empty for electric vehicles">
                </div>
            </div>
        </div>
    `;
    
    vehiclesContainer.insertAdjacentHTML('beforeend', vehicleHtml);
    editVehicleCount++;
    
    updateEditAddVehicleButton();
}

function removeEditVehicle(button) {
    const vehicleDiv = button.closest('.vehicle-card');
    if (!vehicleDiv) return;
    
    vehicleDiv.remove();
    editVehicleCount--;
    
    // Re-number all new vehicles
    const allVehicleCards = document.querySelectorAll('#editVehiclesContainer .vehicle-card');
    allVehicleCards.forEach((vehicle, index) => {
        const title = vehicle.querySelector('h4');
        if (title) {
            title.textContent = `New Vehicle ${index + 1}`;
        }
        
        // Update form inputs
        const select = vehicle.querySelector('select');
        const input = vehicle.querySelector('input');
        if (select) select.name = `vehicles[${index}][type_id]`;
        if (input) input.name = `vehicles[${index}][plate_no]`;
        
        // Update ID for plate number group
        const plateGroup = vehicle.querySelector('[id^="plate-number-group-"]');
        if (plateGroup) plateGroup.id = `plate-number-group-${index}`;
        
        // Update data attribute
        vehicle.setAttribute('data-vehicle-index', index);
    });
    
    updateEditAddVehicleButton();
}

function updateEditAddVehicleButton() {
    const addBtn = document.getElementById('editAddVehicleBtn');
    // Count vehicles that are NOT marked for deletion
    const activeVehicles = currentEditStudentVehicles.filter(v => !vehiclesToDelete.includes(v.id)).length;
    const totalVehicles = activeVehicles + editVehicleCount;
    
    if (totalVehicles >= 3) {
        addBtn.disabled = true;
        addBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        addBtn.disabled = false;
        addBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

function togglePlateNumberInput(selectElement) {
    const vehicleCard = selectElement.closest('.vehicle-card');
    const plateNumberGroup = vehicleCard.querySelector('[id^="plate-number-group-"]');
    
    if (!plateNumberGroup) {
        return; // Safety check
    }
    
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const requiresPlate = selectedOption && selectedOption.getAttribute('data-requires-plate') === '1';
    const plateNumberInput = plateNumberGroup.querySelector('input');
    
    if (!requiresPlate) {
        plateNumberGroup.style.display = 'none';
        if (plateNumberInput) {
            plateNumberInput.value = '';
            plateNumberInput.removeAttribute('required');
        }
    } else {
        plateNumberGroup.style.display = 'block';
        plateNumberGroup.classList.remove('hidden');
        plateNumberGroup.style.visibility = 'visible';
        if (plateNumberInput) {
            plateNumberInput.setAttribute('required', 'required');
        }
    }
}

// Mark/unmark vehicle for deletion
function markVehicleForDeletion(vehicleId) {
    const index = vehiclesToDelete.indexOf(vehicleId);
    
    if (index > -1) {
        // Unmark for deletion
        vehiclesToDelete.splice(index, 1);
    } else {
        // Mark for deletion
        vehiclesToDelete.push(vehicleId);
    }
    
    // Refresh the display to show updated state
    displayExistingVehicles();
    
    // Update button state
    updateEditAddVehicleButton();
}

// Handle edit form submission
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const id = document.getElementById('edit_student_id').value;
            const isActiveValue = document.getElementById('edit_is_active').value;
            
            const vehicles = [];
            // Collect data from new vehicle inputs
            document.querySelectorAll('#editVehiclesContainer .vehicle-card').forEach((vehicleDiv) => {
                const select = vehicleDiv.querySelector('select[name^="vehicles["]');
                const input = vehicleDiv.querySelector('input[name^="vehicles["]');
                
                if (select && select.value) {
                    const typeId = select.value;
                    const plateNo = input ? input.value : '';
                    
                    vehicles.push({
                        type_id: parseInt(typeId),
                        plate_no: plateNo.trim() || null
                    });
                }
            });
            
            // Check if license image is being uploaded
            const licenseImageInput = document.getElementById('edit_license_image');
            const hasNewLicenseImage = licenseImageInput.files.length > 0;
            
            // Mark this action BEFORE sending to prevent notification
            if (realtimeManager) {
                realtimeManager.markUserAction(parseInt(id));
            }
            
            // Use FormData if there's a license image, otherwise use JSON
            let fetchPromise;
            if (hasNewLicenseImage) {
                const formData = new FormData();
                formData.append('first_name', document.getElementById('edit_first_name').value);
                formData.append('last_name', document.getElementById('edit_last_name').value);
                formData.append('email', document.getElementById('edit_email').value);
                formData.append('student_id', document.getElementById('edit_student_id_input').value);
                formData.append('license_no', document.getElementById('edit_license_no').value);
                formData.append('program_id', document.getElementById('edit_program_id').value);
                formData.append('is_active', isActiveValue === '1' ? '1' : '0');
                formData.append('license_image', licenseImageInput.files[0]);
                
                // Append vehicles array properly for FormData
                // Only send vehicles if there are any (field is nullable in backend)
                if (vehicles.length > 0) {
                    vehicles.forEach((vehicle, index) => {
                        formData.append(`vehicles[${index}][type_id]`, vehicle.type_id);
                        if (vehicle.plate_no) {
                            formData.append(`vehicles[${index}][plate_no]`, vehicle.plate_no);
                        }
                    });
                }
                // If no vehicles, don't send the field at all (nullable)
                
                // Append vehicles to delete
                // Only send if there are vehicles to delete (field is nullable in backend)
                if (vehiclesToDelete.length > 0) {
                    vehiclesToDelete.forEach((vehicleId) => {
                        formData.append('vehicles_to_delete[]', vehicleId);
                    });
                }
                // If no vehicles to delete, don't send the field at all (nullable)
                
                fetchPromise = fetch(`/users/students/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-HTTP-Method-Override': 'PUT',
                        'Accept': 'application/json',
                    },
                    body: formData
                });
            } else {
                const data = {
                    first_name: document.getElementById('edit_first_name').value,
                    last_name: document.getElementById('edit_last_name').value,
                    email: document.getElementById('edit_email').value,
                    student_id: document.getElementById('edit_student_id_input').value,
                    license_no: document.getElementById('edit_license_no').value,
                    program_id: parseInt(document.getElementById('edit_program_id').value),
                    is_active: isActiveValue === '1',
                    vehicles: vehicles,
                    vehicles_to_delete: vehiclesToDelete
                };
                
                console.log('Sending update data:', data);
                
                fetchPromise = fetch(`/users/students/${id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
            }
            
            fetchPromise
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeEditModal();
                    } else {
                        alert(data.message || 'Failed to update student');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating');
                });
        });
    }
    
});

function exportToCSV() {
    const visibleStudents = students.filter(student => {
        const userId = student.user_id || student.user?.id;
        const row = document.querySelector(`tr[data-id="${userId}"]`);
        return row && row.style.display !== 'none';
    });

    if (visibleStudents.length === 0) {
        alert('No students to export');
        return;
    }

    const headers = ['Name', 'Email', 'Student ID', 'College', 'Status', 'Created Date'];
    const rows = visibleStudents.map(student => {
        const name = `${student.user.first_name} ${student.user.last_name}`;
        const email = student.user.email;
        const studentId = student.student_id;
        const college = student.college?.name || 'No College';
        const status = student.user.is_active ? 'Active' : 'Inactive';
        const createdDate = new Date(student.created_at).toLocaleDateString('en-US', { 
            year: 'numeric', month: 'long', day: 'numeric'
        });

        return [name, email, studentId, college, status, createdDate].map(field => {
            const escaped = String(field).replace(/"/g, '""');
            return `"${escaped}"`;
        }).join(',');
    });

    const csv = [headers.join(','), ...rows].join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `students_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// License Image Upload Functions for Edit Modal
let editLicenseCameraStream = null;

function openEditModal(id) {
    const student = students.find(s => s.id === id);
    if (!student) return;
    
    document.getElementById('edit_student_id').value = student.id;
    document.getElementById('edit_first_name').value = student.user.first_name;
    document.getElementById('edit_last_name').value = student.user.last_name;
    document.getElementById('edit_email').value = student.user.email;
    document.getElementById('edit_student_id_input').value = student.student_id;
    document.getElementById('edit_license_no').value = student.license_no;
    document.getElementById('edit_program_id').value = student.program_id;
    document.getElementById('edit_is_active').value = student.user.is_active ? '1' : '0';
    
    // Display current license image if exists
    if (student.license_image) {
        document.getElementById('editCurrentLicenseImage').classList.remove('hidden');
        document.getElementById('editCurrentLicenseImageSrc').src = student.license_image;
    } else {
        document.getElementById('editCurrentLicenseImage').classList.add('hidden');
    }
    
    // Reset new license image preview
    document.getElementById('editLicenseImagePreview').classList.add('hidden');
    document.getElementById('edit_license_image').value = '';
    
    // Store existing vehicles
    currentEditStudentVehicles = student.vehicles || [];
    
    // Clear vehicles to delete
    vehiclesToDelete = [];
    
    // Clear new vehicles container
    document.getElementById('editVehiclesContainer').innerHTML = '';
    editVehicleCount = 0;
    
    // Display existing vehicles
    displayExistingVehicles();
    
    updateEditAddVehicleButton();
    
    const modal = document.getElementById('editModal');
    modal.classList.remove('hidden');
}

// Edit License Camera Modal Functions
window.openEditLicenseCameraModal = async function() {
    try {
        editLicenseCameraStream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'user',
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            } 
        });
        const video = document.getElementById('editLicenseCameraVideo');
        video.srcObject = editLicenseCameraStream;
        
        video.onloadedmetadata = function() {
            video.style.width = '100%';
            video.style.height = 'auto';
            video.style.maxHeight = '80vh';
            
            document.getElementById('editLicenseCameraModal').classList.remove('hidden');
        };
    } catch (error) {
        alert('Error accessing camera: ' + error.message);
    }
}

window.closeEditLicenseCameraModal = function() {
    if (editLicenseCameraStream) {
        editLicenseCameraStream.getTracks().forEach(track => track.stop());
        editLicenseCameraStream = null;
    }
    document.getElementById('editLicenseCameraModal').classList.add('hidden');
}

window.captureEditLicensePhoto = function() {
    const video = document.getElementById('editLicenseCameraVideo');
    const canvas = document.getElementById('editLicenseCameraCanvas');
    const context = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);
    
    canvas.toBlob(function(blob) {
        if (!blob) return;
        
        const file = new File([blob], 'license-photo.jpg', { type: 'image/jpeg' });
        
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        
        const licenseInput = document.getElementById('edit_license_image');
        licenseInput.files = dataTransfer.files;
        
        const dataURL = canvas.toDataURL('image/jpeg', 0.8);
        
        const previewImage = document.getElementById('editLicensePreviewImage');
        const imagePreview = document.getElementById('editLicenseImagePreview');
        const currentLicenseImage = document.getElementById('editCurrentLicenseImage');
        
        previewImage.src = dataURL;
        imagePreview.classList.remove('hidden');
        
        // Hide current license image when new one is captured
        if (currentLicenseImage) {
            currentLicenseImage.classList.add('hidden');
        }
        
        closeEditLicenseCameraModal();
    }, 'image/jpeg', 0.8);
}

window.handleEditLicenseFileUpload = function(event) {
    const file = event.target.files[0];
    if (file) {
        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size must be less than 2MB. Your file is ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImage = document.getElementById('editLicensePreviewImage');
            const imagePreview = document.getElementById('editLicenseImagePreview');
            const currentLicenseImage = document.getElementById('editCurrentLicenseImage');
            
            previewImage.src = e.target.result;
            imagePreview.classList.remove('hidden');
            
            // Hide current license image when new one is uploaded
            if (currentLicenseImage) {
                currentLicenseImage.classList.add('hidden');
            }
        };
        reader.readAsDataURL(file);
    }
};

window.removeEditLicensePreview = function() {
    const imagePreview = document.getElementById('editLicenseImagePreview');
    const currentLicenseImage = document.getElementById('editCurrentLicenseImage');
    
    imagePreview.classList.add('hidden');
    document.getElementById('edit_license_image').value = '';
    
    // Show current license image again when new preview is removed
    if (currentLicenseImage) {
        currentLicenseImage.classList.remove('hidden');
    }
}

// Bulk Operations Functions
function toggleSelectAll(checkbox) {
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    rowCheckboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkActions();
}

function updateBulkActions() {
    const selected = document.querySelectorAll('.row-checkbox:checked');
    const count = selected.length;
    const bulkBar = document.getElementById('bulk-actions-bar');
    const countSpan = document.getElementById('selected-count');
    
    if (count > 0) {
        bulkBar.classList.remove('hidden');
        countSpan.textContent = count;
    } else {
        bulkBar.classList.add('hidden');
    }
    
    // Update select-all checkbox
    const selectAll = document.getElementById('select-all');
    const allCheckboxes = document.querySelectorAll('.row-checkbox');
    selectAll.checked = allCheckboxes.length > 0 && selected.length === allCheckboxes.length;
}

function clearSelection() {
    document.querySelectorAll('.row-checkbox, #select-all').forEach(cb => cb.checked = false);
    updateBulkActions();
}

function getSelectedUserIds() {
    return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => parseInt(cb.value));
}


function bulkActivate() {
    const selected = getSelectedUserIds();
    if (selected.length === 0) {
        alert('Please select at least one student');
        return;
    }
    
    const count = selected.length;
    const message = count === 1 
        ? 'Are you sure you want to activate 1 student?'
        : `Are you sure you want to activate ${count} students?`;
    
    document.getElementById('bulkActivateMessage').textContent = message;
    document.getElementById('bulkActivateModal').classList.remove('hidden');
}

function closeBulkActivateModal() {
    document.getElementById('bulkActivateModal').classList.add('hidden');
}

function executeBulkActivate() {
    const selected = getSelectedUserIds();
    const bulkBar = document.getElementById('bulk-actions-bar');
    bulkBar.style.opacity = '0.6';
    bulkBar.style.pointerEvents = 'none';
    
    // Mark these actions as current user actions to prevent duplicate notifications
    if (realtimeManager) {
        selected.forEach(userId => {
            const student = students.find(s => s.user_id === userId);
            if (student) {
                realtimeManager.markUserAction(student.id);
            }
        });
    }
    
    fetch('/api/bulk/users/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            user_ids: selected,
            is_active: true,
            user_type: 'student'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeBulkActivateModal();
            clearSelection();
            // Real-time updates will handle the UI refresh via broadcasts
            // System notifications are sent from backend
            // Browser notifications will be shown via Echo listeners for other users
        } else {
            alert('Error: ' + (data.message || 'Failed to activate students'));
            bulkBar.style.opacity = '1';
            bulkBar.style.pointerEvents = 'auto';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error activating students');
        bulkBar.style.opacity = '1';
        bulkBar.style.pointerEvents = 'auto';
    });
}

function bulkDeactivate() {
    const selected = getSelectedUserIds();
    if (selected.length === 0) {
        alert('Please select at least one student');
        return;
    }
    
    const count = selected.length;
    const message = count === 1 
        ? 'Are you sure you want to deactivate 1 student?'
        : `Are you sure you want to deactivate ${count} students?`;
    
    document.getElementById('bulkDeactivateMessage').textContent = message;
    document.getElementById('bulkDeactivateModal').classList.remove('hidden');
}

function closeBulkDeactivateModal() {
    document.getElementById('bulkDeactivateModal').classList.add('hidden');
}

function executeBulkDeactivate() {
    const selected = getSelectedUserIds();
    const bulkBar = document.getElementById('bulk-actions-bar');
    bulkBar.style.opacity = '0.6';
    bulkBar.style.pointerEvents = 'none';
    
    // Mark these actions as current user actions to prevent duplicate notifications
    if (realtimeManager) {
        selected.forEach(userId => {
            const student = students.find(s => s.user_id === userId);
            if (student) {
                realtimeManager.markUserAction(student.id);
            }
        });
    }
    
    fetch('/api/bulk/users/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            user_ids: selected,
            is_active: false,
            user_type: 'student'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeBulkDeactivateModal();
            clearSelection();
            // Real-time updates will handle the UI refresh via broadcasts
            // System notifications are sent from backend
            // Browser notifications will be shown via Echo listeners for other users
        } else {
            alert('Error: ' + (data.message || 'Failed to deactivate students'));
            bulkBar.style.opacity = '1';
            bulkBar.style.pointerEvents = 'auto';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error deactivating students');
        bulkBar.style.opacity = '1';
        bulkBar.style.pointerEvents = 'auto';
    });
}

function confirmBulkDelete() {
    const selected = getSelectedUserIds();
    if (selected.length === 0) {
        alert('Please select at least one student');
        return;
    }
    
    const count = selected.length;
    const message = count === 1 
        ? 'Are you sure you want to delete 1 student? This action cannot be undone.'
        : `Are you sure you want to delete ${count} students? This action cannot be undone.`;
    
    document.getElementById('bulkDeleteMessage').textContent = message;
    document.getElementById('bulkDeleteModal').classList.remove('hidden');
}

function closeBulkDeleteModal() {
    document.getElementById('bulkDeleteModal').classList.add('hidden');
}

function executeBulkDelete() {
    const selected = getSelectedUserIds();
    const bulkBar = document.getElementById('bulk-actions-bar');
    bulkBar.style.opacity = '0.6';
    bulkBar.style.pointerEvents = 'none';
    
    // Mark these actions as current user actions to prevent duplicate notifications
    if (realtimeManager) {
        selected.forEach(userId => {
            const student = students.find(s => s.user_id === userId);
            if (student) {
                realtimeManager.markUserAction(student.id);
            }
        });
    }
    
    fetch('/api/bulk/users/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            user_ids: selected,
            user_type: 'student'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeBulkDeleteModal();
            clearSelection();
            // Real-time updates will handle the UI refresh via broadcasts
            // System notifications are sent from backend
            // Browser notifications will be shown via Echo listeners for other users
        } else {
            alert('Error: ' + (data.message || 'Failed to delete students'));
            bulkBar.style.opacity = '1';
            bulkBar.style.pointerEvents = 'auto';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error deleting students');
        bulkBar.style.opacity = '1';
        bulkBar.style.pointerEvents = 'auto';
    });
}

// Expose functions to global scope for onclick handlers
window.viewStudent = viewStudent;
window.openEditModal = openEditModal;
window.deleteStudent = deleteStudent;
window.closeViewModal = closeViewModal;
window.closeDeleteModal = closeDeleteModal;
window.confirmDeleteStudent = confirmDeleteStudent;
window.exportToCSV = exportToCSV;
window.closeEditModal = closeEditModal;
window.addEditVehicle = addEditVehicle;
window.removeEditVehicle = removeEditVehicle;
window.toggleSelectAll = toggleSelectAll;
window.updateBulkActions = updateBulkActions;
window.clearSelection = clearSelection;
window.bulkActivate = bulkActivate;
window.closeBulkActivateModal = closeBulkActivateModal;
window.executeBulkActivate = executeBulkActivate;
window.bulkDeactivate = bulkDeactivate;
window.closeBulkDeactivateModal = closeBulkDeactivateModal;
window.executeBulkDeactivate = executeBulkDeactivate;
window.confirmBulkDelete = confirmBulkDelete;
window.closeBulkDeleteModal = closeBulkDeleteModal;
window.executeBulkDelete = executeBulkDelete;

window.addEventListener('beforeunload', function() {
    if (realtimeManager) {
        realtimeManager.disconnect();
    }
});
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeOut {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.95); }
}
@keyframes highlight {
    0%, 100% { background-color: transparent; }
    50% { background-color: rgba(99, 102, 241, 0.1); }
}

.animate-fade-in { animation: fadeIn 0.5s ease-out; }
.animate-fade-out { animation: fadeOut 0.3s ease-out; }
.animate-highlight { animation: highlight 1s ease-out; }
</style>
@endpush

