import './bootstrap'
import { compressImageFile } from './compress-image'

window.driverCompressImageFile = compressImageFile

// Register Driver Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        const isLocal =
            location.hostname === "localhost" ||
            location.hostname === "127.0.0.1" ||
            location.hostname.endsWith(".test");

        if (isLocal) {
            console.log("Driver SW disabled on local");
            return;
        }

        try {
            const reg = await navigator.serviceWorker.register('/driver/serviceworker.js', { scope: '/driver/' });
            console.log('Driver SW registered:', reg);
            reg.update?.();
        } catch (err) {
            console.error('SW registration failed', err);
        }
    });
}
