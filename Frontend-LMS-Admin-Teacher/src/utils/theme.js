/**
 * Modul Pengelolaan Tema (Dark / Light Mode) untuk DENSO LMS Admin & Teacher.
 * Menyimpan preferensi di localStorage dan mensinkronisasikan class 'dark' pada document.documentElement.
 */

const THEME_STORAGE_KEY = 'dls_theme';

export function getInitialTheme() {
  if (typeof window === 'undefined') return 'light';
  const saved = localStorage.getItem(THEME_STORAGE_KEY);
  if (saved === 'dark' || saved === 'light') return saved;
  // Jika sistem operasi user memilih dark mode
  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    return 'dark';
  }
  return 'light';
}

export function applyTheme(theme) {
  if (typeof document === 'undefined') return;
  const root = document.documentElement;
  if (theme === 'dark') {
    root.classList.add('dark');
    root.setAttribute('data-theme', 'dark');
  } else {
    root.classList.remove('dark');
    root.setAttribute('data-theme', 'light');
  }
  localStorage.setItem(THEME_STORAGE_KEY, theme);
  window.dispatchEvent(new CustomEvent('dls_theme_change', { detail: { theme } }));
}

export function toggleTheme() {
  const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
  const next = current === 'dark' ? 'light' : 'dark';
  applyTheme(next);
  return next;
}

// Inisialisasi tema saat modul diimpor pertama kali
if (typeof window !== 'undefined') {
  applyTheme(getInitialTheme());
}
