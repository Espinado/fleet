import './bootstrap'

// ===============================
// Register Service Worker
// ===============================
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/driver/sw.js')
            .then(reg => console.log('Driver SW registered:', reg))
            .catch(err => console.error('SW registration failed', err));
    });
}

