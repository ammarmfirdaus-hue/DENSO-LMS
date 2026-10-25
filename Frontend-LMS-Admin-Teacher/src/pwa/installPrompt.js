const DISMISS_KEY = 'dls_admin_pwa_prompt_dismissed_until_v1';
const INSTALLED_KEY = 'dls_admin_pwa_installed_v1';
const subscribers = new Set();
const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent)
  || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

let deferredInstallPrompt = null;
let iosInstructionsReady = false;

function readStorage(key) {
  try { return localStorage.getItem(key); } catch { return null; }
}

function writeStorage(key, value) {
  try { localStorage.setItem(key, value); } catch { /* Storage can be unavailable in private browsing. */ }
}

function isStandalone() {
  return window.matchMedia?.('(display-mode: standalone)').matches || window.navigator.standalone === true;
}

function isDismissed() {
  return Number(readStorage(DISMISS_KEY) || 0) > Date.now();
}

export function getInstallPromptState() {
  if (isStandalone() || readStorage(INSTALLED_KEY) === '1' || isDismissed()) return { mode: 'hidden' };
  if (deferredInstallPrompt) return { mode: 'install' };
  if (isIOS && iosInstructionsReady) return { mode: 'ios' };
  return { mode: 'hidden' };
}

function publish() {
  const state = getInstallPromptState();
  subscribers.forEach((listener) => listener(state));
}

export function subscribeInstallPrompt(listener) {
  subscribers.add(listener);
  listener(getInstallPromptState());
  return () => subscribers.delete(listener);
}

export function dismissInstallPrompt(days = 14) {
  deferredInstallPrompt = null;
  writeStorage(DISMISS_KEY, String(Date.now() + days * 24 * 60 * 60 * 1000));
  publish();
}

export async function requestPwaInstall() {
  if (!deferredInstallPrompt) return 'unavailable';

  const promptEvent = deferredInstallPrompt;
  deferredInstallPrompt = null;
  try {
    await promptEvent.prompt();
    const choice = await promptEvent.userChoice;
    if (choice?.outcome === 'accepted') {
      writeStorage(INSTALLED_KEY, '1');
    } else {
      writeStorage(DISMISS_KEY, String(Date.now() + 7 * 24 * 60 * 60 * 1000));
    }
    publish();
    return choice?.outcome || 'dismissed';
  } catch {
    writeStorage(DISMISS_KEY, String(Date.now() + 24 * 60 * 60 * 1000));
    publish();
    return 'error';
  }
}

window.addEventListener('beforeinstallprompt', (event) => {
  event.preventDefault();
  deferredInstallPrompt = event;
  publish();
});

window.addEventListener('appinstalled', () => {
  deferredInstallPrompt = null;
  writeStorage(INSTALLED_KEY, '1');
  publish();
});

if (isIOS) {
  window.setTimeout(() => {
    iosInstructionsReady = true;
    publish();
  }, 2200);
}
