<!-- Student Registration Form -->
<div class="min-h-screen flex items-center justify-center bg-[#FDFDFC] dark:bg-[#161615] py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Student Registration</h2>
            <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">Register your vehicle for campus parking</p>
        </div>

        <!-- Student Registration Form -->
        <form class="mt-8 space-y-6 bg-white dark:bg-[#1b1b18] p-8 rounded-lg shadow-[0_1px_3px_0_rgba(0,0,0,0.1)] dark:shadow-[0_1px_3px_0_rgba(0,0,0,0.3)] border border-[#e3e3e0] dark:border-[#3E3E3A]" id="studentRegistrationForm">
            <!-- Row 1: First Name, Last Name -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- First Name -->
                <div class="form-group">
                    <label for="firstName" class="form-label">First Name</label>
                    <input 
                        id="firstName" 
                        name="firstName" 
                        type="text" 
                        required 
                        class="form-input" 
                        placeholder="John"
                    >
                </div>

                <!-- Last Name -->
                <div class="form-group">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input 
                        id="lastName" 
                        name="lastName" 
                        type="text" 
                        required 
                        class="form-input" 
                        placeholder="Doe"
                    >
                </div>
            </div>

            <!-- Row 2: College, Student ID -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- College Dropdown -->
                <div class="form-group">
                    <label for="college" class="form-label">College</label>
                    <select id="college" name="college" class="form-input" required>
                        <option value="">Select College</option>
                        <option value="1">College of Graduate Studies</option>
                        <option value="2">College of Law</option>
                        <option value="3">College of Engineering</option>
                        <option value="4">College of Information Technology</option>
                        <option value="5">College of Arts and Sciences</option>
                        <option value="6">College of Education</option>
                        <option value="7">College of Management</option>
                        <option value="8">Institute of Criminal Justice Education</option>
                        <option value="9">College of Technology</option>
                    </select>
                </div>

                <!-- Student ID -->
                <div class="form-group">
                    <label for="studentId" class="form-label">Student ID</label>
                    <input 
                        id="studentId" 
                        name="studentId" 
                        type="text" 
                        required 
                        class="form-input" 
                        placeholder="2024-12345"
                    >
                </div>
            </div>

            <!-- Row 3: Email, License Number -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        required 
                        class="form-input" 
                        placeholder="john.doe@student.dmmmsu.edu.ph"
                    >
                </div>

                <!-- License Number -->
                <div class="form-group">
                    <label for="licenseNo" class="form-label">License Number</label>
                    <input 
                        id="licenseNo" 
                        name="licenseNo" 
                        type="text" 
                        required 
                        class="form-input" 
                        placeholder="A12-34-567890"
                    >
                </div>
            </div>

            <!-- Row 4: License Image (Full Width) -->
            <div class="form-group">
                <label class="form-label">License Image</label>
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-4">Upload a clear photo of your driver's license</p>
                
                <!-- Upload Options -->
                <div class="flex gap-4 mb-6">
                    <button type="button" class="btn btn-info" onclick="openLicenseCameraModal()">
                        <x-heroicon-o-camera class="w-4 h-4 inline-block mr-2" />
                        Take Photo
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('licenseFileInput').click()">
                        <x-heroicon-o-photo class="w-4 h-4 inline-block mr-2" />
                        Upload File
                    </button>
                </div>

                <!-- Hidden File Input -->
                <input type="file" id="licenseFileInput" accept="image/*" class="hidden" onchange="handleLicenseFileUpload(event)">

                <!-- Image Preview -->
                <div id="licenseImagePreview" class="hidden mb-4">
                    <div class="relative inline-block">
                        <img id="licensePreviewImage" src="" alt="License Preview" class="w-full max-w-md rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <button type="button" onclick="removeLicensePreview()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                            <x-heroicon-s-x-mark class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Vehicle Section -->
            <div class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Vehicles</h3>
                    <button type="button" id="addVehicleBtn" class="btn btn-secondary ">
                    <x-heroicon-o-plus class="w-4 h-4 inline-block mr-2" />Add Vehicle
                    </button>
                </div>
                
        
                <!-- Vehicle Container -->
                <div id="vehiclesContainer" class="space-y-4">
                    <!-- Default Vehicle -->
                    <div class="vehicle-item bg-gray-50 dark:bg-[#161615] p-4 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Vehicle 1</h4>
                            <button type="button" class="remove-vehicle-btn text-red-600 hover:text-red-700 hidden">
                                <x-heroicon-s-x-mark class="w-4 h-4" />
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Vehicle Type</label>
                                <select name="vehicles[0][type]" class="form-input" required>
                                    <option value="">Select Vehicle Type</option>
                                    <option value="1">Motorcycle</option>
                                    <option value="2">Car</option>
                                    <option value="3">Electric Vehicle</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Plate Number</label>
                                <input 
                                    name="vehicles[0][plate_no]" 
                                    type="text" 
                                    required 
                                    class="form-input" 
                                    placeholder="ABC-1234"
                                >
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-2">
                    Maximum of 3 vehicles allowed per user
                </p>
            </div>

            
            <!-- Submit Button -->
            <div>
                <button type="submit" class="btn btn-primary w-full">
                    Register User
                </button>
            </div>

            
        </form>
    </div>
</div>

<!-- License Camera Modal -->
<div id="licenseCameraModal" class="modal-backdrop hidden">
    <div class="camera-container max-w-4xl">
        <div class="modal-header flex justify-between items-center">
            <h2 class="modal-title">License Camera</h2>
            <button onclick="closeLicenseCameraModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <x-heroicon-s-x-mark class="w-5 h-5" />
            </button>
        </div>
        <div class="modal-body p-0">
            <video id="licenseCameraVideo" autoplay playsinline class="w-full h-auto bg-black max-h-[70vh] sm:max-h-[80vh] object-cover"></video>
            <canvas id="licenseCameraCanvas" class="hidden"></canvas>
        </div>
        <div class="modal-footer">
            <button class="btn-camera" onclick="captureLicensePhoto()">
                <x-heroicon-o-camera class="w-6 h-8" />
            </button>
        </div>
    </div>
</div>


