export function registerPwaServiceWorker() {
  if (!('serviceWorker' in navigator) || !window.isSecureContext) return;

  // Vite's localhost server already provides the application and hot reload.
  // An older production worker can otherwise return the cached offline page
  // when the dev server is stopped or briefly reconnecting.
  if (import.meta.env.DEV) {
    navigator.serviceWorker.getRegistrations()
      .then((registrations) => Promise.all(registrations.map((registration) => registration.unregister())))
      .catch((error) => console.debug('Service worker lama tidak dapat dibersihkan.', error));
    return;
  }

  navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch((error) => {
    console.debug('PWA service worker tidak aktif pada origin ini.', error);
  });
}
