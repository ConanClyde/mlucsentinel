// Message Modal Functions
window.openMessageModal = function() {
    document.getElementById('messageModal').classList.remove('hidden');
}

window.closeMessageModal = function() {
    document.getElementById('messageModal').classList.add('hidden');
}

// Success Modal Functions
window.openSuccessModal = function() {
    document.getElementById('successModal').classList.remove('hidden');
}

window.closeSuccessModal = function() {
    document.getElementById('successModal').classList.add('hidden');
}

// Error Modal Functions
window.openErrorModal = function() {
    document.getElementById('errorModal').classList.remove('hidden');
}

window.closeErrorModal = function() {
    document.getElementById('errorModal').classList.add('hidden');
}

// Warning Modal Functions
window.openWarningModal = function() {
    document.getElementById('warningModal').classList.remove('hidden');
}

window.closeWarningModal = function() {
    document.getElementById('warningModal').classList.add('hidden');
}

// Close modals when clicking outside
const messageModal = document.getElementById('messageModal');
if (messageModal) {
    messageModal.addEventListener('click', function(e) {
        if (e.target === this) closeMessageModal();
    });
}

const successModal = document.getElementById('successModal');
if (successModal) {
    successModal.addEventListener('click', function(e) {
        if (e.target === this) closeSuccessModal();
    });
}

const errorModal = document.getElementById('errorModal');
if (errorModal) {
    errorModal.addEventListener('click', function(e) {
        if (e.target === this) closeErrorModal();
    });
}

const warningModal = document.getElementById('warningModal');
if (warningModal) {
    warningModal.addEventListener('click', function(e) {
        if (e.target === this) closeWarningModal();
    });
}
