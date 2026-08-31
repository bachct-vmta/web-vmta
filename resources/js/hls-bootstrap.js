/**
 * HLS video initialiser. Attaches to any <video data-hls-src> on the page.
 *
 * Uses native HLS on Safari; dynamically imports hls.js on Chrome/Firefox
 * so the library is only downloaded when a video element is present on the page.
 * No console.error on missing video — silently no-ops.
 */
async function initHlsVideos() {
    const videos = document.querySelectorAll('video[data-hls-src]');
    if (videos.length === 0) return;

    for (const video of videos) {
        const src = video.dataset.hlsSrc;
        if (!src) continue;

        if (video.canPlayType('application/vnd.apple.mpegurl')) {
            // Safari supports HLS natively
            video.src = src;
        } else {
            const { default: Hls } = await import('hls.js');
            if (Hls.isSupported()) {
                const hls = new Hls();
                hls.loadSource(src);
                hls.attachMedia(video);
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', initHlsVideos);
