'use strict';

const CACHE_PREFIX = 'dls-student-static-';
const CACHE_NAME = CACHE_PREFIX + 'v1';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll([
                OFFLINE_URL,
                '/manifest.webmanifest',
                '/assets/img/icon/dls-student-pwa-192.png'
            ]))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys
                .filter((key) => key.startsWith(CACHE_PREFIX) && key !== CACHE_NAME)
                .map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) return;
    if (url.pathname.startsWith('/api/') || url.pathname === '/sw.js') return;

    if (request.mode === 'navigate') {
        // Never persist personalized PHP pages or session-specific responses.
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    const isStaticAsset = url.pathname.startsWith('/assets/') || url.pathname === '/manifest.webmanifest';
    if (!isStaticAsset) return;

    const update = fetch(request).then((response) => {
        if (response && response.ok && response.type === 'basic') {
            return caches.open(CACHE_NAME).then((cache) => cache.put(request, response.clone())).then(() => response);
        }
        return response;
    });
    event.waitUntil(update.catch(() => undefined));
    event.respondWith(caches.match(request).then((cached) => cached || update));
});
