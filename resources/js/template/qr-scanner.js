// QR Scanner Functions with ZXing
import { BrowserMultiFormatReader } from '@zxing/library';

let qrCodeReader = null;
let qrStream = null;

window.openQRScannerModal = async function() {
    document.getElementById('qrScannerModal').classList.remove('hidden');
    document.getElementById('qrResult').classList.add('hidden');
    
    try {
        qrStream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'environment' } 
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
        alert('Error accessing camera: ' + error.message);
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
