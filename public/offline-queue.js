/**
 * SISTA Offline Queue & Synchronization Manager
 * Provides IndexedDB storage for offline form submissions (e.g., Attendance & Teaching Journal)
 * and automatically synchronizes them when connection is restored.
 */

const DB_NAME = 'sista-offline-db';
const DB_VERSION = 1;
const STORE_NAME = 'pending-requests';

// Initialize IndexedDB
function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
            }
        };

        request.onsuccess = (event) => {
            resolve(event.target.result);
        };

        request.onerror = (event) => {
            console.error('Failed to open offline database:', event.target.error);
            reject(event.target.error);
        };
    });
}

// Add a request to the offline queue
async function enqueueRequest(url, method, formData, typeName) {
    const db = await openDatabase();
    
    // Convert FormData to a serializable object, handling Blobs/Files natively
    const data = {};
    for (const [key, value] of formData.entries()) {
        if (value instanceof File && value.size > 0) {
            data[key] = {
                isBlob: true,
                name: value.name,
                type: value.type,
                blob: value // Store File/Blob object directly in IndexedDB
            };
        } else if (value instanceof File) {
            data[key] = null; // Ignore empty file inputs
        } else {
            data[key] = value;
        }
    }

    const item = {
        url,
        method,
        data,
        typeName,
        timestamp: Date.now()
    };

    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAME], 'readwrite');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.add(item);

        request.onsuccess = () => {
            console.log(`[Offline Queue] Enqueued ${typeName} request to IDB`);
            resolve(request.result);
        };

        transaction.onerror = (event) => {
            reject(event.target.error);
        };
    });
}

// Get all queued requests
async function getQueuedRequests() {
    const db = await openDatabase();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAME], 'readonly');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.getAll();

        request.onsuccess = () => {
            resolve(request.result);
        };

        request.onerror = (event) => {
            reject(event.target.error);
        };
    });
}

// Delete a request from the queue
async function dequeueRequest(id) {
    const db = await openDatabase();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAME], 'readwrite');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.delete(id);

        request.onsuccess = () => {
            resolve();
        };

        request.onerror = (event) => {
            reject(event.target.error);
        };
    });
}

// Sync all queued requests back to the server
async function syncOfflineQueue() {
    const queue = await getQueuedRequests();
    if (queue.length === 0) return;

    console.log(`[Offline Sync] Found ${queue.length} pending requests to sync.`);
    showSyncingToast(queue.length);

    let successCount = 0;
    const activeCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    for (const item of queue) {
        try {
            // Reconstruct FormData
            const formData = new FormData();
            for (const [key, value] of Object.entries(item.data)) {
                if (value && value.isBlob) {
                    formData.append(key, value.blob, value.name);
                } else if (value !== null) {
                    formData.append(key, value);
                }
            }

            // Fresh CSRF token replacement to prevent 419 errors
            if (activeCsrfToken) {
                formData.set('_token', activeCsrfToken);
            }

            const response = await fetch(item.url, {
                method: item.method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': activeCsrfToken || ''
                },
                body: formData
            });

            if (response.ok || response.redirected || response.status === 200 || response.status === 302) {
                await dequeueRequest(item.id);
                successCount++;
            } else {
                console.warn(`[Offline Sync] Request ${item.id} failed with status:`, response.status);
            }
        } catch (error) {
            console.error(`[Offline Sync] Network error syncing request ${item.id}:`, error);
            break; // Stop attempting if network is lost again
        }
    }

    if (successCount > 0) {
        console.log(`[Offline Sync] Successfully synchronized ${successCount} requests.`);
        showSyncSuccessToast(successCount);
        
        // Refresh after a brief delay to load the fresh server state
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
}

// Helper to show a offline-saved toast matching the SISTA UI theme
function showOfflineSavedToast(typeName) {
    let toast = document.getElementById('sista-offline-saved-toast');
    if (toast) toast.remove();

    toast = document.createElement('div');
    toast.id = 'sista-offline-saved-toast';
    toast.className = 'fixed bottom-6 right-6 z-[300] bg-amber-950/85 dark:bg-amber-950/95 text-amber-200 border border-amber-800/50 backdrop-blur-md rounded-2xl p-4 shadow-2xl flex items-center space-x-4 max-w-sm';
    toast.innerHTML = `
        <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 bg-amber-900/50 rounded-xl text-amber-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div class="flex-grow">
            <h4 class="text-sm font-black tracking-tight text-white">Data Disimpan Luring</h4>
            <p class="text-[11px] font-semibold text-amber-300 mt-0.5">Sistem sedang offline. Data ${typeName} disimpan di perangkat dan akan disinkronkan saat online.</p>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Helper to show a syncing toast matching the SISTA UI theme
function showSyncingToast(count) {
    let toast = document.getElementById('sista-syncing-toast');
    if (toast) toast.remove();

    toast = document.createElement('div');
    toast.id = 'sista-syncing-toast';
    toast.className = 'fixed bottom-6 right-6 z-[300] bg-indigo-950/85 dark:bg-indigo-950/95 text-indigo-200 border border-indigo-800/50 backdrop-blur-md rounded-2xl p-4 shadow-2xl flex items-center space-x-4 max-w-sm';
    toast.innerHTML = `
        <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 bg-indigo-900/50 rounded-xl text-indigo-400">
            <svg class="w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <div class="flex-grow">
            <h4 class="text-sm font-black tracking-tight text-white">Sinkronisasi Data</h4>
            <p class="text-[11px] font-semibold text-indigo-300 mt-0.5">Sedang mengunggah ${count} laporan luring...</p>
        </div>
    `;
    document.body.appendChild(toast);
}

// Helper to show sync success toast matching the SISTA UI theme
function showSyncSuccessToast(count) {
    const syncingToast = document.getElementById('sista-syncing-toast');
    if (syncingToast) syncingToast.remove();

    let toast = document.getElementById('sista-sync-success-toast');
    if (toast) toast.remove();

    toast = document.createElement('div');
    toast.id = 'sista-sync-success-toast';
    toast.className = 'fixed bottom-6 right-6 z-[300] bg-emerald-950/85 dark:bg-emerald-950/95 text-emerald-200 border border-emerald-800/50 backdrop-blur-md rounded-2xl p-4 shadow-2xl flex items-center space-x-4 max-w-sm';
    toast.innerHTML = `
        <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 bg-emerald-900/50 rounded-xl text-emerald-400">
            <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div class="flex-grow">
            <h4 class="text-sm font-black tracking-tight text-white">Sinkronisasi Selesai</h4>
            <p class="text-[11px] font-semibold text-emerald-300 mt-0.5">${count} laporan luring berhasil disinkronkan.</p>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Intercept form submissions for offline capability
document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (form.getAttribute('data-offline-sync') === 'true') {
        if (!navigator.onLine) {
            event.preventDefault();
            
            const url = form.getAttribute('action');
            const method = form.getAttribute('method') || 'POST';
            const typeName = form.getAttribute('data-type-name') || 'laporan';
            const formData = new FormData(form);

            try {
                // If it is a livewire or alpine form, stop loading spinner
                if (window.Alpine) {
                    // Search for Alpine component data that has a loading state and set to false
                }
                
                await enqueueRequest(url, method, formData, typeName);
                showOfflineSavedToast(typeName);

                // Try to disable the submit button and show custom offline message on button
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    const origText = submitBtn.innerHTML;
                    submitBtn.innerHTML = `Tersimpan Luring`;
                    setTimeout(() => {
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        submitBtn.innerHTML = origText;
                    }, 3000);
                }
            } catch (err) {
                console.error('[Offline Queue] Error saving offline request:', err);
                alert('Gagal menyimpan data secara offline: ' + err.message);
            }
        }
    }
});

// Automatic sync listener
window.addEventListener('online', () => {
    syncOfflineQueue();
});

// Run sync on load if online
window.addEventListener('load', () => {
    if (navigator.onLine) {
        syncOfflineQueue();
    }
});
