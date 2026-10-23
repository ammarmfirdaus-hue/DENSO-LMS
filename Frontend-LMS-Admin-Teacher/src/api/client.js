const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:3001/api/v1';
const TOKEN_KEY = 'dls_admin_teacher_access_token';

function readStoredToken() {
  try {
    return window.sessionStorage.getItem(TOKEN_KEY);
  } catch {
    return null;
  }
}

let token = readStoredToken();

export const authToken = {
  get: () => token,
  set: value => {
    token = value;
    try {
      window.sessionStorage.setItem(TOKEN_KEY, value);
    } catch {
      // Keep the in-memory session if browser storage is unavailable.
    }
  },
  clear: () => {
    token = null;
    try {
      window.sessionStorage.removeItem(TOKEN_KEY);
    } catch {
      // The in-memory token is still cleared when browser storage is unavailable.
    }
  }
};

export async function api(path, options = {}) {
  const headers = new Headers(options.headers || {});
  headers.set('Accept', 'application/json');
  if (options.body) headers.set('Content-Type', 'application/json');
  if (token) headers.set('Authorization', `Bearer ${token}`);
  const response = await fetch(`${API_BASE}${path}`, { ...options, headers, credentials: 'omit' });
  const body = await response.json().catch(() => ({}));
  if (!response.ok) {
    if (response.status === 401) authToken.clear();
    throw new Error(body?.error?.message || 'Permintaan gagal. Coba kembali.');
  }
  return body;
}
