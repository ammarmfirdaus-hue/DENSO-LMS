(() => {
  'use strict';

  const wait = ms => new Promise(resolve => setTimeout(resolve, ms));

  // Retry transient failures for safe reads only. POST/PATCH/DELETE requests
  // are never replayed, because the server may already have completed them.
  window.dlsFetch = async function dlsFetch(input, options = {}) {
    const { timeoutMs = 20000, retries, ...requestOptions } = options;
    const method = String(requestOptions.method || 'GET').toUpperCase();
    const safeRead = method === 'GET' || method === 'HEAD';
    const retryLimit = Number.isInteger(retries) ? Math.max(0, retries) : (safeRead ? 2 : 0);
    let lastError;

    for (let attempt = 0; attempt <= (safeRead ? retryLimit : 0); attempt += 1) {
      const controller = new AbortController();
      const externalSignal = requestOptions.signal;
      let timedOut = false;
      const timeout = window.setTimeout(() => {
        timedOut = true;
        controller.abort();
      }, timeoutMs);
      const forwardAbort = () => controller.abort(externalSignal.reason);
      if (externalSignal?.aborted) forwardAbort();
      else externalSignal?.addEventListener('abort', forwardAbort, { once: true });

      try {
        const response = await fetch(input, { ...requestOptions, signal: controller.signal });
        if (safeRead && attempt < retryLimit && [502, 503, 504].includes(response.status)) {
          response.body?.cancel().catch(() => {});
          await wait(250 * (attempt + 1));
          continue;
        }
        return response;
      } catch (error) {
        if (externalSignal?.aborted) throw error;
        lastError = error;
        const retryable = error instanceof TypeError;
        if (safeRead && retryable && attempt < retryLimit) {
          await wait(250 * (attempt + 1));
          continue;
        }

        if (timedOut || error?.name === 'AbortError') {
          throw new Error('Server LMS tidak merespons. Periksa koneksi lalu coba kembali.', { cause: error });
        }
        if (error instanceof TypeError) {
          throw new Error('Koneksi ke server LMS terputus. Periksa koneksi internet atau muat ulang halaman.', { cause: error });
        }
        throw error;
      } finally {
        window.clearTimeout(timeout);
        externalSignal?.removeEventListener('abort', forwardAbort);
      }
    }

    throw new Error('Koneksi ke server LMS terputus. Periksa koneksi internet atau muat ulang halaman.', { cause: lastError });
  };
})();
