<!-- Media Upload Section -->
<div class="bg-white dark:bg-[#1a1a1a] p-6 rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A] mb-6">
    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Media Upload</h3>
    
    <!-- Upload Options -->
    <div class="flex gap-4 mb-6">
        <button class="btn btn-info" onclick="openCameraModal()">
            <x-heroicon-o-camera class="w-4 h-4 inline-block mr-2" />
            Take Photo
        </button>
        <button class="btn btn-secondary" onclick="document.getElementById('fileInput').click()">
            <x-heroicon-o-photo class="w-4 h-4 inline-block mr-2" />
            Upload File
        </button>
    </div>

    <!-- Hidden File Input -->
    <input type="file" id="fileInput" accept="image/*" class="hidden" onchange="handleFileUpload(event)">

    <!-- Image Preview -->
    <div id="imagePreview" class="hidden mb-4">
        <div class="relative inline-block">
            <img id="previewImage" src="" alt="Preview" class="w-full max-w-md rounded-lg border border-[#e3e3e0] dark:border-[#3E3E3A]">
            <button onclick="removePreview()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>
    </div>

</div>

<!-- Camera Modal -->
<div id="cameraModal" class="modal-backdrop hidden">
    <div class="camera-container max-w-4xl">
        <div class="modal-header flex justify-between items-center">
            <h2 class="modal-title">Camera</h2>
            <button onclick="closeCameraModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <x-heroicon-s-x-mark class="w-5 h-5" />
            </button>
        </div>
        <div class="modal-body p-0">
            <video id="cameraVideo" autoplay playsinline class="w-full h-auto bg-black max-h-[70vh] sm:max-h-[80vh] object-cover"></video>
            <canvas id="cameraCanvas" class="hidden"></canvas>
        </div>
        <div class="modal-footer">
            <button class="btn-camera" onclick="capturePhoto()">
                <x-heroicon-o-camera class="w-6 h-8" />
            </button>
        </div>
    </div>
</div>

