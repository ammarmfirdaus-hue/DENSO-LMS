const configuredApiBase = (import.meta.env?.VITE_API_BASE_URL || '').replace(/\/$/, '');
const localApiBase = /^http:\/\/(?:127\.0\.0\.1|localhost):3001\/api\/v1$/i;
const API_BASE_KEY = 'dls_admin_api_base_url';
const defaultApiBase = import.meta.env?.DEV && (!configuredApiBase || localApiBase.test(configuredApiBase))
  ? '/api/v1'
  : (configuredApiBase || '/api/v1');
let API_BASE = defaultApiBase;
try { API_BASE = localStorage.getItem(API_BASE_KEY) || defaultApiBase; } catch { /* Browser storage may be disabled. */ }

// Route local UI traffic through Vite so browser requests stay same-origin and
// do not depend on CORS or whether the page was opened via localhost/127.0.0.1.
export { API_BASE };

/**
 * Mengubah path sampul course relatif (/courses/thumbnails/...) menjadi URL lengkap siap pakai untuk tag <img>.
 */
export function resolveCourseThumbnailUrl(thumbnailPath, apiBase = API_BASE) {
  const value = String(thumbnailPath || '').trim();
  if (!value) return null;
  if (/^https?:\/\//i.test(value)) return value;
  const base = String(apiBase || '').replace(/\/+$/, '');
  return `${base}${value.startsWith('/') ? value : `/${value}`}`;
}

export function storeApiBaseUrl(value) {
  const normalized = String(value || '').replace(/\/+$/, '');
  API_BASE = normalized;
  try { localStorage.setItem(API_BASE_KEY, normalized); } catch { /* The backend keeps the canonical value. */ }
}

export async function initializeApiBase() {
  try {
    const response = await fetch(`${API_BASE}/settings/runtime-config`, { credentials: 'include', signal: AbortSignal.timeout(3500) });
    if (!response.ok) return API_BASE;
    const config = await response.json();
    if (typeof config.apiBaseUrl === 'string' && config.apiBaseUrl) storeApiBaseUrl(config.apiBaseUrl);
  } catch { /* Keep Vite/env or the last saved browser fallback when API is offline. */ }
  return API_BASE;
}

const TOKEN_KEY = 'dls_admin_teacher_access_token';
let token = null;
try { window.sessionStorage.removeItem(TOKEN_KEY); } catch { /* Cookie SSO works without browser storage. */ }

const wait = ms => new Promise(resolve => setTimeout(resolve, ms));

/**
 * Fetch API with a bounded timeout and retries for safe reads only.
 * Writes are never retried, so transient network errors cannot duplicate them.
 */
export async function fetchApi(path, options = {}) {
  const { timeoutMs = 20000, retries, ...requestOptions } = options;
  const method = String(requestOptions.method || 'GET').toUpperCase();
  const retryLimit = Number.isInteger(retries) ? Math.max(0, retries) : (method === 'GET' ? 2 : 0);
  const retryableMethod = method === 'GET' || method === 'HEAD';
  let lastError;

  for (let attempt = 0; attempt <= (retryableMethod ? retryLimit : 0); attempt += 1) {
    const controller = new AbortController();
    const externalSignal = requestOptions.signal;
    let timedOut = false;
    const timeout = setTimeout(() => {
      timedOut = true;
      controller.abort();
    }, timeoutMs);
    const forwardAbort = () => controller.abort(externalSignal.reason);
    if (externalSignal?.aborted) forwardAbort();
    else externalSignal?.addEventListener('abort', forwardAbort, { once: true });

    try {
      const response = await fetch(`${API_BASE}${path}`, { ...requestOptions, signal: controller.signal });
      if (retryableMethod && attempt < retryLimit && [502, 503, 504].includes(response.status)) {
        response.body?.cancel().catch(() => {});
        await wait(250 * (attempt + 1));
        continue;
      }
      return response;
    } catch (error) {
      if (externalSignal?.aborted) throw error;
      lastError = error;
      const retryableFailure = error instanceof TypeError;
      if (retryableMethod && retryableFailure && attempt < retryLimit) {
        await wait(250 * (attempt + 1));
        continue;
      }

      if (timedOut || error?.name === 'AbortError') {
        throw new Error('Server LMS tidak merespons dalam batas waktu. Periksa koneksi lalu coba kembali.', { cause: error });
      }
      if (error instanceof TypeError) {
        throw new Error('Tidak dapat terhubung ke server LMS. Pastikan Backend LMS aktif lalu muat ulang halaman.', { cause: error });
      }
      throw error;
    } finally {
      clearTimeout(timeout);
      externalSignal?.removeEventListener('abort', forwardAbort);
    }
  }

  throw new Error('Tidak dapat terhubung ke server LMS. Pastikan Backend LMS aktif lalu muat ulang halaman.', { cause: lastError });
}

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
 * @param {RequestInit & { token?: string|null, timeoutMs?: number, retries?: number }} [options]
 * @returns {Promise<any>} Data objek JSON respons dari backend.
 */
export async function api(path, options = {}) {
  const { token: requestToken, ...fetchOptions } = options;
  const headers = new Headers(options.headers || {});
  headers.set('Accept', 'application/json');
  if (options.body && !(options.body instanceof FormData)) headers.set('Content-Type', 'application/json');
  const activeToken = requestToken === null ? null : (requestToken || token);
  if (activeToken) headers.set('Authorization', `Bearer ${activeToken}`);
  const response = await fetchApi(path, { ...fetchOptions, headers, credentials: 'include' });
  if (response.status === 204) return {};
  const body = await response.json().catch(() => ({}));
  if (!response.ok) {
    if (response.status === 401 && requestToken === undefined) authToken.clear();
    throw new Error(body?.error?.message || `Layanan LMS bermasalah (HTTP ${response.status}). Coba kembali.`);
  }
  return body;
}
