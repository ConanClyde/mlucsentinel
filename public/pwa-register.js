// PWA Registration and Install Prompt
(function() {
    'use strict';

    let deferredPrompt;
    let installButton;

    // Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then((registration) => {
                    console.log('✓ ServiceWorker registered:', registration.scope);
                    
                    // Check for updates every hour
                    setInterval(() => {
                        registration.update();
                    }, 60 * 60 * 1000);
                })
                .catch((error) => {
                    console.error('✗ ServiceWorker registration failed:', error);
                });
        });

        // Handle service worker updates
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            console.log('ServiceWorker updated, reloading...');
            window.location.reload();
        });
    }

    // Install Prompt Handler
    window.addEventListener('beforeinstallprompt', (e) => {
        console.log('beforeinstallprompt fired');
        
        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();
        
        // Stash the event so it can be triggered later
        deferredPrompt = e;
        
        // Show install button
        showInstallButton();
    });

    // Show install button
    function showInstallButton() {
        installButton = document.getElementById('pwa-install-btn');
        const installedMessage = document.getElementById('pwa-installed-message');
        const notAvailableMessage = document.getElementById('pwa-not-available');
        
        if (installButton) {
            installButton.style.display = 'flex';
            
            // Hide other messages
            if (installedMessage) installedMessage.style.display = 'none';
            if (notAvailableMessage) notAvailableMessage.style.display = 'none';
            
            installButton.addEventListener('click', installApp);
        }
    }

    // Install app function
    function installApp() {
        if (!deferredPrompt) {
            return;
        }

        // Show the install prompt
        deferredPrompt.prompt();

        // Wait for the user to respond to the prompt
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the install prompt');
                
                // Hide install button
                if (installButton) {
                    installButton.style.display = 'none';
                }
            } else {
                console.log('User dismissed the install prompt');
            }
            
            deferredPrompt = null;
        });
    }

    // Detect if app is installed
    window.addEventListener('appinstalled', () => {
        console.log('✓ MLUC Sentinel installed');
        
        const installedMessage = document.getElementById('pwa-installed-message');
        const notAvailableMessage = document.getElementById('pwa-not-available');
        
        // Hide install button
        if (installButton) {
            installButton.style.display = 'none';
        }
        
        // Show installed message
        if (installedMessage) {
            installedMessage.style.display = 'flex';
        }
        if (notAvailableMessage) {
            notAvailableMessage.style.display = 'none';
        }
        
        // Show success message
        if (typeof showNotification === 'function') {
            showNotification('App installed successfully! You can now access MLUC Sentinel from your home screen.');
        }
        
        deferredPrompt = null;
    });

    // Check if already installed (standalone mode)
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
        console.log('✓ Running as installed PWA');
        
        // Show installed message and hide install button
        window.addEventListener('DOMContentLoaded', () => {
            installButton = document.getElementById('pwa-install-btn');
            const installedMessage = document.getElementById('pwa-installed-message');
            const notAvailableMessage = document.getElementById('pwa-not-available');
            
            if (installButton) {
                installButton.style.display = 'none';
            }
            if (installedMessage) {
                installedMessage.style.display = 'flex';
            }
            if (notAvailableMessage) {
                notAvailableMessage.style.display = 'none';
            }
        });
    }
    
    // Check if PWA is supported (show not available message if not)
    window.addEventListener('DOMContentLoaded', () => {
        // Wait a bit to see if beforeinstallprompt fires
        setTimeout(() => {
            const installButton = document.getElementById('pwa-install-btn');
            const installedMessage = document.getElementById('pwa-installed-message');
            const notAvailableMessage = document.getElementById('pwa-not-available');
            
            // If button is still hidden and not installed, show not available message
            if (installButton && installButton.style.display === 'none' && 
                installedMessage && installedMessage.style.display === 'none' &&
                notAvailableMessage) {
                // Check if it's not standalone mode
                if (!window.matchMedia('(display-mode: standalone)').matches && window.navigator.standalone !== true) {
                    notAvailableMessage.style.display = 'block';
                }
            }
        }, 3000); // Wait 3 seconds for beforeinstallprompt
    });

    // Online/Offline detection
    window.addEventListener('online', () => {
        console.log('✓ Back online');
        document.body.classList.remove('offline');
        
        // Show notification
        if (typeof showNotification === 'function') {
            showNotification('You\'re back online!');
        }
        
        // Trigger background sync
        if ('serviceWorker' in navigator && 'sync' in navigator.serviceWorker) {
            navigator.serviceWorker.ready.then((registration) => {
                return registration.sync.register('sync-reports');
            });
        }
    });

    window.addEventListener('offline', () => {
        console.log('✗ Gone offline');
        document.body.classList.add('offline');
        
        // Show offline indicator
        showOfflineIndicator();
    });

    // Show offline indicator
    function showOfflineIndicator() {
        let indicator = document.getElementById('offline-indicator');
        
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'offline-indicator';
            indicator.innerHTML = `
                <div style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    background: #f59e0b;
                    color: white;
                    padding: 10px;
                    text-align: center;
                    z-index: 9999;
                    font-size: 14px;
                    font-weight: 600;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                ">
                    <svg style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-right: 8px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
                    </svg>
                    You're offline. Changes will sync when reconnected.
                </div>
            `;
            document.body.appendChild(indicator);
        }
    }

    // Remove offline indicator when back online
    window.addEventListener('online', () => {
        const indicator = document.getElementById('offline-indicator');
        if (indicator) {
            indicator.remove();
        }
    });

    // Queue report for offline submission
    window.queueOfflineReport = async function(reportData, csrfToken) {
        try {
            const db = await openDatabase();
            const transaction = db.transaction(['reports'], 'readwrite');
            const store = transaction.objectStore('reports');
            
            await store.add({
                data: reportData,
                csrfToken: csrfToken,
                timestamp: Date.now()
            });
            
            console.log('✓ Report queued for offline submission');
            
            // Register for background sync
            if ('serviceWorker' in navigator && 'sync' in navigator.serviceWorker) {
                const registration = await navigator.serviceWorker.ready;
                await registration.sync.register('sync-reports');
            }
            
            return true;
        } catch (error) {
            console.error('Failed to queue report:', error);
            return false;
        }
    };

    // Open IndexedDB
    function openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open('MLUCSentinelDB', 1);
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
            
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains('reports')) {
                    db.createObjectStore('reports', { keyPath: 'id', autoIncrement: true });
                }
            };
        });
    }

    // Check if notification permission is granted
    if ('Notification' in window && navigator.serviceWorker) {
        if (Notification.permission === 'default') {
            // Can request permission when appropriate (e.g., after user action)
            window.requestNotificationPermission = function() {
                Notification.requestPermission().then((permission) => {
                    console.log('Notification permission:', permission);
                });
            };
        }
    }

    console.log('✓ PWA scripts loaded');
})();

