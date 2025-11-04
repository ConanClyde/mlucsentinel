// MLUC Sentinel Service Worker
const CACHE_VERSION = 'v1.0.2';
const CACHE_NAME = `mluc-sentinel-${CACHE_VERSION}`;
const OFFLINE_URL = '/offline';

// Assets to cache on install (only essential files, icons will be cached on-demand)
const STATIC_CACHE_URLS = [
    '/offline',
    '/images/campus-map.svg',
    '/manifest.json'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[ServiceWorker] Install');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[ServiceWorker] Caching static assets');
                // Cache files individually to prevent one 404 from breaking everything
                return Promise.allSettled(
                    STATIC_CACHE_URLS.map(url => 
                        fetch(new Request(url, {credentials: 'same-origin'}))
                            .then(response => {
                                if (response.ok) {
                                    return cache.put(url, response);
                                }
                                console.warn('[ServiceWorker] Failed to cache:', url, response.status);
                            })
                            .catch(error => {
                                console.warn('[ServiceWorker] Failed to fetch:', url, error.message);
                            })
                    )
                ).then(() => {
                    console.log('[ServiceWorker] Cache complete (some files may have been skipped)');
                });
            })
            .catch((error) => {
                console.error('[ServiceWorker] Cache open failed:', error);
            })
    );
    
    // Force the waiting service worker to become the active service worker
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[ServiceWorker] Activate');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[ServiceWorker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    
    // Claim all clients
    return self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip cross-origin requests
    if (url.origin !== location.origin) {
        return;
    }
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // API requests - Network first, then cache
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Clone the response before caching
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    return caches.match(request);
                })
        );
        return;
    }
    
    // Static assets - Cache first, then network
    if (
        url.pathname.match(/\.(js|css|png|jpg|jpeg|svg|gif|webp|woff|woff2|ttf|eot|ico)$/) ||
        url.pathname.includes('/images/') ||
        url.pathname.includes('/build/')
    ) {
        event.respondWith(
            caches.match(request)
                .then((cachedResponse) => {
                    // Return cached response immediately, but also fetch fresh copy in background
                    const fetchPromise = fetch(request).then((response) => {
                        if (response.status === 200) {
                            const responseClone = response.clone();
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(request, responseClone);
                            });
                        }
                        return response;
                    }).catch(() => cachedResponse);
                    
                    // Return cached immediately, or wait for network
                    return cachedResponse || fetchPromise;
                })
        );
        return;
    }
    
    // HTML pages - Network first (online-first for fresh content with heroicons), fallback to cache, then offline page
    event.respondWith(
        fetch(request)
            .then((response) => {
                // Cache successful HTML responses (includes heroicons as inline SVG)
                if (response.status === 200 && request.headers.get('accept')?.includes('text/html')) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // If network fails, serve from cache (heroicons still work as inline SVG)
                return caches.match(request)
                    .then((cachedResponse) => {
                        if (cachedResponse) {
                            return cachedResponse;
                        }
                        // Return offline page for navigation requests
                        if (request.mode === 'navigate') {
                            return caches.match(OFFLINE_URL);
                        }
                    });
            })
    );
});

// Background Sync - for offline report submissions
self.addEventListener('sync', (event) => {
    console.log('[ServiceWorker] Background sync', event.tag);
    
    if (event.tag === 'sync-reports') {
        event.waitUntil(syncReports());
    }
});

// Sync queued reports when back online
async function syncReports() {
    try {
        // Get all pending reports from IndexedDB
        const db = await openDatabase();
        const pendingReports = await getPendingReports(db);
        
        console.log('[ServiceWorker] Syncing', pendingReports.length, 'reports');
        
        for (const report of pendingReports) {
            try {
                const response = await fetch('/reporter/report-submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': report.csrfToken
                    },
                    body: JSON.stringify(report.data)
                });
                
                if (response.ok) {
                    // Remove from queue
                    await removeReport(db, report.id);
                    
                    // Notify user
                    self.registration.showNotification('Report Submitted', {
                        body: 'Your queued report has been submitted successfully',
                        icon: '/images/icons/icon-192x192.png',
                        badge: '/images/icons/icon-96x96.png'
                    });
                }
            } catch (error) {
                console.error('[ServiceWorker] Failed to sync report:', error);
            }
        }
    } catch (error) {
        console.error('[ServiceWorker] Sync failed:', error);
    }
}

// Push notification handler
self.addEventListener('push', (event) => {
    console.log('[ServiceWorker] Push received');
    
    let notificationData = {
        title: 'MLUC Sentinel',
        body: 'You have a new notification',
        icon: '/images/icons/icon-192x192.png',
        badge: '/images/icons/icon-96x96.png',
        data: {}
    };
    
    if (event.data) {
        try {
            const data = event.data.json();
            notificationData = {
                title: data.title || notificationData.title,
                body: data.message || data.body || notificationData.body,
                icon: notificationData.icon,
                badge: notificationData.badge,
                data: data.data || {},
                tag: data.tag || 'notification',
                requireInteraction: data.requireInteraction || false
            };
        } catch (e) {
            notificationData.body = event.data.text();
        }
    }
    
    event.waitUntil(
        self.registration.showNotification(notificationData.title, notificationData)
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    console.log('[ServiceWorker] Notification click');
    
    event.notification.close();
    
    const urlToOpen = event.notification.data?.url || '/home';
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Check if there's already a window open
                for (const client of clientList) {
                    if (client.url.includes(urlToOpen) && 'focus' in client) {
                        return client.focus();
                    }
                }
                // Open new window
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

// IndexedDB helpers for offline queue
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

function getPendingReports(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['reports'], 'readonly');
        const store = transaction.objectStore('reports');
        const request = store.getAll();
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function removeReport(db, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['reports'], 'readwrite');
        const store = transaction.objectStore('reports');
        const request = store.delete(id);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve();
    });
}

// Message handler for communication with clients
self.addEventListener('message', (event) => {
    console.log('[ServiceWorker] Message received:', event.data);
    
    if (event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data.type === 'CACHE_URLS') {
        event.waitUntil(
            caches.open(CACHE_NAME).then((cache) => {
                return cache.addAll(event.data.urls);
            })
        );
    }
});

