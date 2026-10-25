'use strict';

const CACHE_PREFIX = 'dls-admin-teacher-static-';
const CACHE_NAME = CACHE_PREFIX + 'v1';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll([
        OFFLINE_URL,
        '/manifest.webmanifest',
        '/assets/img/icon/dls-admin-teacher-192.png'
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

  // Never serve a cached offline document over the Vite development portal.
  if (['localhost', '127.0.0.1'].includes(self.location.hostname)) return;
  if (request.method !== 'GET' || url.origin !== self.location.origin) return;
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/uploads/') || url.pathname === '/sw.js') return;

  if (request.mode === 'navigate') {
    // Do not persist authenticated SPA routes or their user-specific HTML.
    event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
    return;
  }

  const isPublicAsset = url.pathname.startsWith('/assets/') || url.pathname === '/manifest.webmanifest';
  if (!isPublicAsset) return;

  const refresh = fetch(request).then((response) => {
    if (response && response.ok && response.type === 'basic') {
      return caches.open(CACHE_NAME)
        .then((cache) => cache.put(request, response.clone()))
        .then(() => response);
    }
    return response;
  });
  event.waitUntil(refresh.catch(() => undefined));
  event.respondWith(caches.match(request).then((cached) => cached || refresh));
});
