// Media Upload Functions
let cameraStream = null;

// File upload handling
window.handleFileUpload = function(event) {
    const file = event.target.files[0];
    if (file) {
        // Check file size (5MB limit)
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if (file.size > maxSize) {
            alert('File size must be less than 5MB. Your file is ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
            event.target.value = ''; // Clear the input
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImage = document.getElementById('previewImage');
            const imagePreview = document.getElementById('imagePreview');
            const uploadOptions = document.querySelector('.flex.gap-4.mb-6');
            
            previewImage.src = e.target.result;
            imagePreview.classList.remove('hidden');
            
            // Hide upload options when image is previewed
            if (uploadOptions) {
                uploadOptions.classList.add('hidden');
            }
        };
        reader.readAsDataURL(file);
    }
}

// Remove preview
window.removePreview = function() {
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('fileInput').value = '';
    
    // Show upload options again
    const uploadOptions = document.querySelector('.flex.gap-4.mb-6');
    if (uploadOptions) {
        uploadOptions.classList.remove('hidden');
    }
}

// Camera Modal Functions
window.openCameraModal = async function() {
    try {
        // Get camera with high resolution first
        cameraStream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'user',
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            } 
        });
        const video = document.getElementById('cameraVideo');
        video.srcObject = cameraStream;
        
        // Wait for video to load and get actual dimensions before showing modal
        video.onloadedmetadata = function() {
            // Set video dimensions to match camera resolution
            video.style.width = '100%';
            video.style.height = 'auto';
            video.style.maxHeight = '80vh'; // Limit max height to prevent overflow
            
            // Only show modal when camera is ready
            document.getElementById('cameraModal').classList.remove('hidden');
        };
    } catch (error) {
        alert('Error accessing camera: ' + error.message);
    }
}

window.closeCameraModal = function() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
    document.getElementById('cameraModal').classList.add('hidden');
}

window.capturePhoto = function() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const context = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);
    
    // Convert canvas to data URL and show in preview
    const dataURL = canvas.toDataURL('image/jpeg', 0.8);
    
    const previewImage = document.getElementById('previewImage');
    const imagePreview = document.getElementById('imagePreview');
    const uploadOptions = document.querySelector('.flex.gap-4.mb-6');
    
    previewImage.src = dataURL;
    imagePreview.classList.remove('hidden');
    
    // Hide upload options when image is previewed
    if (uploadOptions) {
        uploadOptions.classList.add('hidden');
    }
    
    closeCameraModal();
}

document.addEventListener('DOMContentLoaded', function() {
    const cameraModal = document.getElementById('cameraModal');
    if (cameraModal) {
        cameraModal.addEventListener('click', function(e) {
            if (e.target === this) closeCameraModal();
        });
    }
});
