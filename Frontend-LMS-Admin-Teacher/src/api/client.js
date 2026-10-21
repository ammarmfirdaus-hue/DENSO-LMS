const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:3001/api/v1';
let token = null;

export const authToken = {
  get: () => token,
  set: value => { token = value; },
  clear: () => { token = null; }
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
