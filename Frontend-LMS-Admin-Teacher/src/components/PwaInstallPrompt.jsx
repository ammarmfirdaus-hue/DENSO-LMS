import { useEffect, useRef, useState } from 'react';
import {
  dismissInstallPrompt,
  getInstallPromptState,
  requestPwaInstall,
  subscribeInstallPrompt,
} from '../pwa/installPrompt';
import './PwaInstallPrompt.css';

export default function PwaInstallPrompt() {
  const [promptState, setPromptState] = useState(getInstallPromptState);
  const dialogRef = useRef(null);
  const previousFocus = useRef(null);
  const wasVisible = useRef(false);

  useEffect(() => subscribeInstallPrompt(setPromptState), []);

  useEffect(() => {
    const visible = promptState.mode !== 'hidden';
    if (visible && !wasVisible.current) {
      previousFocus.current = document.activeElement;
      requestAnimationFrame(() => dialogRef.current?.focus());
    } else if (!visible && wasVisible.current) {
      previousFocus.current?.focus?.();
    }
    wasVisible.current = visible;
  }, [promptState.mode]);

  if (promptState.mode === 'hidden') return null;

  function closePrompt() {
    dismissInstallPrompt(14);
  }

  function handleDialogKeyDown(event) {
    if (event.key === 'Escape') {
      event.preventDefault();
      closePrompt();
      return;
    }
    if (event.key !== 'Tab') return;
    const controls = dialogRef.current?.querySelectorAll('button:not([hidden])');
    if (!controls?.length) return;
    if (event.shiftKey && document.activeElement === controls[0]) {
      event.preventDefault();
      controls[controls.length - 1].focus();
    } else if (!event.shiftKey && document.activeElement === controls[controls.length - 1]) {
      event.preventDefault();
      controls[0].focus();
    }
  }

  const isIOSPrompt = promptState.mode === 'ios';

  return (
    <div
      className="dls-pwa-backdrop"
      onMouseDown={(event) => { if (event.target === event.currentTarget) closePrompt(); }}
    >
      <section
        ref={dialogRef}
        className="dls-pwa-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="dls-pwa-title"
        aria-describedby="dls-pwa-description"
        tabIndex={-1}
        onKeyDown={handleDialogKeyDown}
      >
        <img className="dls-pwa-icon" src="/assets/img/icon/dls-admin-teacher-192.png" alt="" />
        <h2 className="dls-pwa-title" id="dls-pwa-title">Akses LMS lebih praktis</h2>
        <p className="dls-pwa-copy" id="dls-pwa-description">
          {isIOSPrompt
            ? 'Tambahkan DENSO LMS ke layar utama agar portal pengelola dan pengajar mudah dibuka.'
            : 'Pasang DENSO LMS di perangkat Anda agar bisa dibuka seperti aplikasi dari layar utama.'}
        </p>
        {isIOSPrompt && (
          <div className="dls-pwa-steps">
            Di browser iPhone/iPad, ketuk <strong>Bagikan</strong>, lalu pilih <strong>Tambahkan ke Layar Utama</strong>.
          </div>
        )}
        <div className="dls-pwa-actions">
          {!isIOSPrompt && (
            <button className="dls-pwa-button dls-pwa-button-primary" type="button" onClick={requestPwaInstall}>
              Pasang aplikasi
            </button>
          )}
          <button className="dls-pwa-button dls-pwa-button-secondary" type="button" onClick={closePrompt}>
            Nanti
          </button>
        </div>
      </section>
    </div>
  );
}
