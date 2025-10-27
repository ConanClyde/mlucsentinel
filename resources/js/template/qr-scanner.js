// QR Scanner Functions with ZXing
import { BrowserMultiFormatReader } from '@zxing/library';

let qrCodeReader = null;
let qrStream = null;

window.openQRScannerModal = async function() {
    document.getElementById('qrScannerModal').classList.remove('hidden');
    document.getElementById('qrResult').classList.add('hidden');
    
    try {
        // Check if camera API is available
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Camera access is not supported in your browser. Please use a modern browser or enable camera permissions.');
            closeQRScannerModal();
            return;
        }

        // Request camera permission with specific constraints
        qrStream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment',
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            } 
        });
        const videoElement = document.getElementById('qrVideo');
        videoElement.srcObject = qrStream;
        
        qrCodeReader = new BrowserMultiFormatReader();
        
        qrCodeReader.decodeFromVideoDevice(null, 'qrVideo', (result, error) => {
            if (result) {
                document.getElementById('qrResultText').textContent = result.text;
                document.getElementById('qrResult').classList.remove('hidden');
                console.log('QR Code detected:', result.text);
            }
        });
    } catch (error) {
        console.error('Error starting QR scanner:', error);
        
        let errorMessage = 'Unable to access camera. ';
        
        if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
            errorMessage += 'Camera permission was denied. Please enable camera access in your browser settings and try again.';
        } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
            errorMessage += 'No camera found on your device.';
        } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
            errorMessage += 'Camera is already in use by another application.';
        } else if (error.name === 'OverconstrainedError' || error.name === 'ConstraintNotSatisfiedError') {
            errorMessage += 'Camera does not support the required settings.';
        } else if (error.name === 'NotSupportedError') {
            errorMessage += 'Camera access requires HTTPS. Please use a secure connection.';
        } else {
            errorMessage += error.message || 'Please check your browser settings and permissions.';
        }
        
        alert(errorMessage);
        closeQRScannerModal();
    }
};

window.closeQRScannerModal = function() {
    if (qrCodeReader) {
        qrCodeReader.reset();
        qrCodeReader = null;
    }
    if (qrStream) {
        qrStream.getTracks().forEach(track => track.stop());
        qrStream = null;
    }
    document.getElementById('qrScannerModal').classList.add('hidden');
};

document.addEventListener('DOMContentLoaded', function() {
    const qrScannerModal = document.getElementById('qrScannerModal');
    if (qrScannerModal) {
        qrScannerModal.addEventListener('click', function(e) {
            if (e.target === this) window.closeQRScannerModal();
        });
    }
});
