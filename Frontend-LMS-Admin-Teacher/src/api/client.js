export const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:3001/api/v1';
const TOKEN_KEY = 'dls_admin_teacher_access_token';
let token = null;
try { window.sessionStorage.removeItem(TOKEN_KEY); } catch { /* Cookie SSO works without browser storage. */ }

/**
 * Pengelola token otorisasi di memori browser untuk sesi pengguna aktif.
 */
export const authToken = {
  get: () => token,
  set: () => { token = null; },
  clear: () => {
    token = null;
    try {
      window.sessionStorage.removeItem(TOKEN_KEY);
    } catch {
      // The in-memory token is still cleared when browser storage is unavailable.
    }
  }
};

/**
 * Client HTTP universal untuk memanggil REST API Backend LMS.
 * Menangani otomatis penyertaan kredensial cookie SSO HttpOnly,
 * otorisasi Bearer token, header JSON/FormData, dan parsing error terpusat.
 *
 * @param {string} path Jalur endpoint API (contoh: '/courses', '/reviewers').
 * @param {RequestInit & { token?: string|null }} [options] Opsi request fetch.
 * @returns {Promise<any>} Data objek JSON respons dari backend.
 */
export async function api(path, options = {}) {
  const { token: requestToken, ...fetchOptions } = options;
  const headers = new Headers(options.headers || {});
  headers.set('Accept', 'application/json');
  if (options.body && !(options.body instanceof FormData)) headers.set('Content-Type', 'application/json');
  const activeToken = requestToken === null ? null : (requestToken || token);
  if (activeToken) headers.set('Authorization', `Bearer ${activeToken}`);
  const response = await fetch(`${API_BASE}${path}`, { ...fetchOptions, headers, credentials: 'include' });
  if (response.status === 204) return {};
  const body = await response.json().catch(() => ({}));
  if (!response.ok) {
    if (response.status === 401 && requestToken === undefined) authToken.clear();
    throw new Error(body?.error?.message || 'Permintaan gagal. Coba kembali.');
  }
  return body;
}
