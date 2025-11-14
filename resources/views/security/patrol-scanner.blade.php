@extends('layouts.app')

@section('page-title', 'Patrol Scanner')

@section('content')
<div class="space-y-4 md:space-y-6">
    <!-- Page Header - Simple -->
    <div class="mb-4 md:mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-1 sm:mb-2">
            Patrol Scanner
        </h2>
        <p class="text-sm sm:text-base text-[#706f6c] dark:text-[#A1A09A]">
            Scan location QR code to check in
        </p>
    </div>

    <!-- Scanner Option Card -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl shadow-lg border-2 border-[#e3e3e0] dark:border-[#3E3E3A] p-4 md:p-6 lg:p-8">
        <!-- Scanner Container -->
        <div id="scanner-container" class="relative bg-black rounded-lg overflow-hidden aspect-square" style="max-width: 500px; margin: 0 auto;">
            <video id="scanner-video" class="w-full h-full object-cover" style="display: none;"></video>
            <canvas id="scanner-canvas" class="w-full h-full"></canvas>
            
            <!-- Scanner Overlay -->
            <div id="scanner-overlay" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <div class="text-center text-white p-6">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <p class="text-base sm:text-lg font-semibold mb-2">Ready to Scan</p>
                    <p class="text-xs sm:text-sm text-gray-300">Click "Start Scanner" to begin</p>
                </div>
            </div>

            <!-- Flip Camera Button -->
            <button id="flip-camera-btn" onclick="flipCamera()" style="display: none;" class="absolute top-4 right-4 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-full p-3 shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors z-10" title="Flip Camera">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>

            <!-- Scanning Frame -->
            <div id="scanning-frame" class="absolute inset-0 pointer-events-none hidden">
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-48 h-48 sm:w-64 sm:h-64 border-4 border-blue-500 rounded-lg">
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-blue-500"></div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-blue-500"></div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-blue-500"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-blue-500"></div>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="mt-6">
        <div class="flex flex-col gap-3">
            <button id="start-scanner-btn" onclick="startScanner()" 
                    class="btn btn-primary w-full !h-12">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Start Scanner
            </button>
            
            <button id="stop-scanner-btn" onclick="stopScanner()" style="display: none;"
                    class="btn btn-danger w-full !h-12">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                </svg>
                Stop Scanner
            </button>
        </div>
    </div>

    <!-- Instructions Card -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 sm:p-6 mt-6">
        <h3 class="font-semibold text-blue-900 dark:text-blue-200 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            How to Use
        </h3>
        <ol class="text-sm text-blue-800 dark:text-blue-300 space-y-2 list-decimal list-inside ml-2">
            <li>Click "Start Scanner" to activate your camera</li>
            <li>Point your camera at a location QR code</li>
            <li>The scanner will automatically detect and redirect you</li>
            <li>Complete the check-in process</li>
        </ol>
    </div>
</div>

    <!-- Recent Check-ins -->
    @if($recentCheckins->count() > 0)
    <div class="bg-white dark:bg-[#1a1a1a] rounded-xl shadow-lg border-2 border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
            <h3 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Recent Check-ins</h3>
            <div class="space-y-2">
                @foreach($recentCheckins as $checkin)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] truncate">
                                    {{ $checkin->mapLocation->name }} ({{ $checkin->mapLocation->short_code }})
                                </div>
                                <div class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                    {{ $checkin->checked_in_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/@zxing/library@latest"></script>
<script>
let codeReader = null;
let scanning = false;
let qrFacingMode = 'environment'; // 'environment' for back camera, 'user' for front camera
let currentStream = null;

async function startScanner() {
    const video = document.getElementById('scanner-video');
    const overlay = document.getElementById('scanner-overlay');
    const scanningFrame = document.getElementById('scanning-frame');
    const startBtn = document.getElementById('start-scanner-btn');
    const stopBtn = document.getElementById('stop-scanner-btn');
    const flipBtn = document.getElementById('flip-camera-btn');

    try {
        // Request camera with specific facing mode
        currentStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: qrFacingMode,
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            }
        });

        video.srcObject = currentStream;

        codeReader = new ZXing.BrowserQRCodeReader();
        
        codeReader.decodeFromVideoDevice(undefined, video, (result, err) => {
            if (result) {
                console.log('QR Code detected:', result.text);
                
                // Stop the scanner
                stopScanner();
                
                // Redirect to the URL from QR code
                window.location.href = result.text;
            }
            
            if (err && !(err instanceof ZXing.NotFoundException)) {
                console.error(err);
            }
        }).then(() => {
            scanning = true;
            video.style.display = 'block';
            overlay.classList.add('hidden');
            scanningFrame.classList.remove('hidden');
            flipBtn.style.display = 'block';
            startBtn.style.display = 'none';
            stopBtn.style.display = 'block';
        }).catch(err => {
            console.error(err);
            alert('Unable to access camera. Please grant camera permissions and try again.');
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
        });
    } catch (err) {
        console.error(err);
        alert('Unable to access camera. Please grant camera permissions and try again.');
    }
}

function stopScanner() {
    if (codeReader) {
        codeReader.reset();
        codeReader = null;
    }
    
    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
        currentStream = null;
    }
    
    scanning = false;
    const video = document.getElementById('scanner-video');
    const overlay = document.getElementById('scanner-overlay');
    const scanningFrame = document.getElementById('scanning-frame');
    const startBtn = document.getElementById('start-scanner-btn');
    const stopBtn = document.getElementById('stop-scanner-btn');
    const flipBtn = document.getElementById('flip-camera-btn');
    
    video.style.display = 'none';
    overlay.classList.remove('hidden');
    scanningFrame.classList.add('hidden');
    flipBtn.style.display = 'none';
    startBtn.style.display = 'block';
    stopBtn.style.display = 'none';
}

async function flipCamera() {
    // Stop current stream
    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
        currentStream = null;
    }
    if (codeReader) {
        codeReader.reset();
        codeReader = null;
    }
    
    // Toggle facing mode
    qrFacingMode = qrFacingMode === 'environment' ? 'user' : 'environment';
    
    // Restart scanner with new facing mode
    await startScanner();
}

// Stop scanner when leaving page
window.addEventListener('beforeunload', () => {
    if (scanning) {
        stopScanner();
    }
});
</script>
@endpush
