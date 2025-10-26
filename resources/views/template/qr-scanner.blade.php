<!-- QR Scanner Modal Button -->
<button class="btn btn-csv" onclick="openQRScannerModal()">QR Scanner Modal</button>

<!-- QR Scanner Modal -->
<div id="qrScannerModal" class="modal-backdrop hidden">
    <div class="modal-container max-w-2xl">
        <div class="modal-header flex justify-between items-center">
            <h2 class="modal-title flex items-center gap-2">
                <x-heroicon-o-qr-code class="w-6 h-6" />
                QR Code Scanner
            </h2>
            <button onclick="closeQRScannerModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <x-heroicon-s-x-mark class="w-5 h-5" />
            </button>
        </div>
        <div class="modal-body p-0">
            <div class="relative">
                <video id="qrVideo" autoplay playsinline class="w-full aspect-square bg-black object-cover max-h-[70vh] sm:max-h-[80vh]"></video>
                <div id="qrResult" class="absolute inset-0 p-4 sm:p-6 hidden bg-black bg-opacity-75">
                    <div class="flex items-center justify-center h-full">
                        <div class="bg-white dark:bg-gray-800 p-3 sm:p-4 rounded-lg max-w-xs sm:max-w-sm w-full mx-2">
                            <p class="mb-2 font-semibold text-gray-900 dark:text-white text-sm sm:text-base">Scanned Result:</p>
                            <p id="qrResultText" class="p-2 sm:p-3 bg-[#f3f4f6] dark:bg-gray-700 rounded-md break-all text-xs sm:text-sm"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
