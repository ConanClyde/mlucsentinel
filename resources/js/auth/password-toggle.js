// Password Toggle Functionality
// Use event delegation to handle dynamically added password toggles
// This works for login, register, reset-password, and other auth pages
document.addEventListener('click', function(e) {
    // Handle clicks on the button or any of its children (including icons)
    const toggleButton = e.target.closest('.toggle-password');
    if (!toggleButton) return;
    
    // Skip if this is in settings page (settings has its own handler in security.js)
    const isInSettings = toggleButton.closest('#disable2FAModal, #viewRecoveryCodesModal');
    if (isInSettings) return;
    
    e.preventDefault();
    e.stopPropagation();
    
    const targetId = toggleButton.getAttribute('data-target');
    if (!targetId) return;
    
    const passwordInput = document.getElementById(targetId);
    if (!passwordInput) return;
    
    const eyeIcon = toggleButton.querySelector('.eye-icon');
    const eyeSlashIcon = toggleButton.querySelector('.eye-slash-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (eyeIcon) eyeIcon.classList.add('hidden');
        if (eyeSlashIcon) eyeSlashIcon.classList.remove('hidden');
    } else {
        passwordInput.type = 'password';
        if (eyeIcon) eyeIcon.classList.remove('hidden');
        if (eyeSlashIcon) eyeSlashIcon.classList.add('hidden');
    }
});
