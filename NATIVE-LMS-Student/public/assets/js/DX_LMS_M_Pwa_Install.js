(function () {
    'use strict';

    var deferredInstallPrompt = null;
    var modal = null;
    var previousFocus = null;
    var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    var storageKey = 'dls_pwa_install_prompt_v1';
    var installedKey = 'dls_pwa_installed_v1';

    function isStandalone() {
        return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches)
            || window.navigator.standalone === true;
    }

    function readStorage(key) {
        try { return localStorage.getItem(key); } catch (error) { return null; }
    }

    function writeStorage(key, value) {
        try { localStorage.setItem(key, value); } catch (error) { /* Private browsing may block storage. */ }
    }

    function isPromptSuppressed() {
        var expiresAt = Number(readStorage(storageKey) || 0);
        return Number.isFinite(expiresAt) && expiresAt > Date.now();
    }

    function suppressPrompt(days) {
        writeStorage(storageKey, String(Date.now() + days * 24 * 60 * 60 * 1000));
    }

    function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) return;
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function (error) {
            console.warn('PWA service worker belum dapat diaktifkan.', error);
        });
    }

    function closeModal(rememberChoice) {
        if (!modal) return;
        modal.dataset.open = 'false';
        window.setTimeout(function () {
            if (modal) modal.hidden = true;
        }, 190);
        if (rememberChoice) suppressPrompt(14);
        if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
    }

    function createModal() {
        if (modal) return modal;

        modal = document.createElement('div');
        modal.className = 'dls-pwa-backdrop';
        modal.hidden = true;
        modal.setAttribute('data-open', 'false');
        modal.innerHTML = [
            '<section class="dls-pwa-dialog" role="dialog" aria-modal="true" aria-labelledby="dls-pwa-title" aria-describedby="dls-pwa-copy" tabindex="-1">',
            '<img class="dls-pwa-icon" src="/assets/img/icon/dls-student-pwa-192.png" alt="">',
            '<h2 class="dls-pwa-title" id="dls-pwa-title">Akses LMS lebih praktis</h2>',
            '<p class="dls-pwa-copy" id="dls-pwa-copy">Pasang DENSO LMS di perangkat Anda agar bisa dibuka seperti aplikasi, langsung dari layar utama.</p>',
            '<div class="dls-pwa-steps" data-ios-steps hidden>Di browser iPhone/iPad, ketuk tombol <strong>Bagikan</strong>, lalu pilih <strong>Tambahkan ke Layar Utama</strong>.</div>',
            '<div class="dls-pwa-actions">',
            '<button class="dls-pwa-button dls-pwa-button-primary" type="button" data-pwa-install>Pasang aplikasi</button>',
            '<button class="dls-pwa-button dls-pwa-button-secondary" type="button" data-pwa-dismiss>Nanti</button>',
            '</div>',
            '</section>'
        ].join('');

        document.body.appendChild(modal);
        modal.addEventListener('click', function (event) {
            if (event.target === modal || event.target.closest('[data-pwa-dismiss]')) closeModal(true);
            if (event.target.closest('[data-pwa-install]')) installApp();
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal && !modal.hidden) closeModal(true);
            if (event.key === 'Tab' && modal && !modal.hidden) {
                var controls = modal.querySelectorAll('button:not([hidden])');
                if (!controls.length) return;
                if (event.shiftKey && document.activeElement === controls[0]) {
                    event.preventDefault(); controls[controls.length - 1].focus();
                } else if (!event.shiftKey && document.activeElement === controls[controls.length - 1]) {
                    event.preventDefault(); controls[0].focus();
                }
            }
        });
        return modal;
    }

    function showModal(forIOS) {
        if (isStandalone() || readStorage(installedKey) === '1' || isPromptSuppressed()) return;
        var element = createModal();
        var installButton = element.querySelector('[data-pwa-install]');
        var steps = element.querySelector('[data-ios-steps]');
        var copy = element.querySelector('#dls-pwa-copy');
        if (forIOS) {
            installButton.hidden = true;
            steps.hidden = false;
            copy.textContent = 'Tambahkan DENSO LMS ke layar utama supaya portal belajar mudah dibuka kapan saja.';
        }
        previousFocus = document.activeElement;
        element.hidden = false;
        requestAnimationFrame(function () { element.dataset.open = 'true'; });
        window.setTimeout(function () { element.querySelector('[role="dialog"]').focus(); }, 30);
    }

    function installApp() {
        if (!deferredInstallPrompt) return;
        var promptEvent = deferredInstallPrompt;
        deferredInstallPrompt = null;
        promptEvent.prompt();
        promptEvent.userChoice.then(function (choice) {
            if (choice && choice.outcome === 'accepted') {
                writeStorage(installedKey, '1');
                closeModal(false);
            } else {
                suppressPrompt(7);
                closeModal(false);
            }
        }).catch(function () {
            suppressPrompt(1);
            closeModal(false);
        });
    }

    function init() {
        registerServiceWorker();
        if (isStandalone() || readStorage(installedKey) === '1' || isPromptSuppressed()) return;

        window.addEventListener('beforeinstallprompt', function (event) {
            event.preventDefault();
            deferredInstallPrompt = event;
            window.setTimeout(function () { showModal(false); }, 900);
        });

        window.addEventListener('appinstalled', function () {
            writeStorage(installedKey, '1');
            closeModal(false);
        });

        if (isIOS) window.setTimeout(function () { showModal(true); }, 2200);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
    else init();
})();
