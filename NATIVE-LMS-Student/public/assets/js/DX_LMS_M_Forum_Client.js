(() => {
  'use strict';
  const root = document.querySelector('[data-forum-view]');
  if (!root) return;

  const apiBase = String(window.DLS_FORUM_API_BASE || '').replace(/\/$/, '');
  const courseCode = root.dataset.courseCode || '';
  const sessionCode = root.dataset.sessionCode || '';
  const threadId = root.dataset.threadId || '';
  const readOnly = root.dataset.readOnly === 'true';
  const locked = root.dataset.locked === 'true';
  const view = root.dataset.forumView;
  const categoryUrl = root.dataset.categoryUrl || '?route=/student/courses';
  const sessionPath = `/forum/courses/${encodeURIComponent(courseCode)}/sessions/${encodeURIComponent(sessionCode)}`;
  let forumOpen = false;
  let eventSource;

  const roleName = role => role === 'teacher' ? 'Teacher' : role === 'admin' ? 'Admin' : 'Student';
  const dateText = value => {
    const date = new Date(value);
    return Number.isNaN(date.valueOf()) ? '' : date.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
  };
  async function request(path, options = {}) {
    const headers = new Headers(options.headers || {});
    headers.set('Accept', 'application/json');
    if (options.body && !(options.body instanceof FormData)) headers.set('Content-Type', 'application/json');
    const response = await fetch(`${apiBase}${path}`, { ...options, headers, credentials: 'include' });
    const result = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(result?.error?.message || 'Permintaan forum gagal.');
    return result;
  }
  function showMessage(text, kind = 'error') {
    const box = root.querySelector('[data-forum-message]');
    if (!box) return;
    box.textContent = text;
    box.hidden = !text;
    box.className = kind === 'error'
      ? 'mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800'
      : 'mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800';
  }
  function make(tag, className, text = '') {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  }
  function renderAttachment(path, alt) {
    if (!path) return null;
    const image = document.createElement('img');
    image.crossOrigin = 'use-credentials';
    image.src = `${apiBase}${path}`;
    image.alt = alt;
    image.loading = 'lazy';
    image.className = 'mt-3 max-h-[420px] w-full max-w-2xl rounded-xl border border-outline-variant/40 object-contain';
    return image;
  }
  function roleBadge(role) {
    const colors = role === 'teacher'
      ? 'bg-primary-container-light text-primary'
      : role === 'admin'
        ? 'bg-surface-container text-on-surface-variant'
        : 'bg-secondary-container-light text-on-secondary-fixed-variant';
    const node = make('span', `shrink-0 rounded-md px-2 py-1 text-[11px] font-semibold ${colors}`, roleName(role));
    return node;
  }
  async function like(targetType, targetId) {
    if (readOnly) return;
    try {
      await request('/forum/likes', { method: 'POST', body: JSON.stringify({ targetType, targetId }) });
      await refresh();
    } catch (error) { showMessage(error.message); }
  }
  function interactionRow(type, id, count, liked, replyCount) {
    const row = make('div', 'mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-outline-variant/30 pt-3');
    const likeButton = make('button', `inline-flex min-h-10 items-center gap-2 rounded-md px-2 text-sm disabled:cursor-not-allowed disabled:opacity-50 ${liked ? 'font-semibold text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary'}`);
    const likeIcon = make('span', 'material-symbols-outlined text-[18px]', liked ? 'thumb_up' : 'thumb_up_off_alt');
    likeIcon.setAttribute('aria-hidden', 'true');
    likeButton.append(likeIcon);
    likeButton.append(make('span', '', `${count || 0} suka`));
    likeButton.type = 'button';
    likeButton.disabled = readOnly;
    likeButton.addEventListener('click', () => like(type, id));
    row.append(likeButton);
    if (replyCount !== undefined) row.append(make('span', 'min-h-10 py-2 text-sm text-on-surface-variant', `${replyCount || 0} balasan`));
    return row;
  }
  function renderPost(post, isReply = false) {
    const card = make('article', isReply ? 'border-t border-outline-variant/30 py-4' : 'rounded-xl border border-outline-variant/40 bg-surface-container-lowest p-4 sm:p-5');
    const author = post.author_name || post.author_npk || 'Pengguna';
    const top = make('div', 'flex min-w-0 items-start gap-3');
    top.append(make('span', 'grid h-9 w-9 shrink-0 place-items-center rounded-full bg-surface-container text-sm font-semibold text-on-surface-variant', Array.from(author.trim())[0]?.toLocaleUpperCase('id') || 'U'));
    const metadata = make('div', 'flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1');
    metadata.append(make('strong', 'min-w-0 break-words text-sm font-semibold text-on-surface', author));
    metadata.append(roleBadge(post.author_role));
    metadata.append(make('time', 'text-xs text-on-surface-variant', dateText(post.created_at)));
    top.append(metadata);
    card.append(top);
    if (!isReply) card.append(make('h3', 'mt-3 break-words text-base font-semibold text-on-surface', post.title));
    card.append(make('p', 'mt-2 whitespace-pre-wrap break-words text-sm leading-6 text-on-surface-variant', post.body));
    const attachment = renderAttachment(post.attachment_path, 'Gambar lampiran forum');
    if (attachment) card.append(attachment);
    if (post.status === 'locked') card.append(make('p', 'mt-3 text-xs font-medium text-on-surface-variant', 'Diskusi ini ditutup untuk balasan.'));
    card.append(interactionRow(isReply ? 'reply' : 'thread', isReply ? post.reply_id : post.thread_id, post.likes_count, post.liked_by_me, isReply ? undefined : post.replies_count));
    return card;
  }
  function makeReplyForm(thread) {
    const form = document.createElement('form');
    form.className = 'mt-4 border-t border-outline-variant/30 pt-4';
    const textarea = document.createElement('textarea');
    textarea.name = 'body'; textarea.required = true; textarea.maxLength = 2000; textarea.rows = 3;
    textarea.placeholder = 'Tulis balasan...';
    textarea.className = 'w-full resize-y rounded-lg border border-outline-variant/50 bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary';
    const fileLabel = make('label', 'block min-w-0 text-xs text-on-surface-variant', 'Lampiran gambar (maks. 5 MB setelah kompresi; GIF animasi tetap asli)');
    const file = document.createElement('input');
    file.type = 'file'; file.name = 'image'; file.accept = 'image/jpeg,image/png,image/gif,image/webp';
    file.className = 'mt-1 block max-w-full text-xs text-on-surface-variant';
    fileLabel.append(file);
    const footer = make('div', 'mt-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between');
    const submit = make('button', 'min-h-11 w-full rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-50 sm:w-auto', 'Kirim balasan');
    submit.type = 'submit';
    footer.append(fileLabel, submit); form.append(textarea, footer);
    form.addEventListener('submit', async event => {
      event.preventDefault(); submit.disabled = true;
      try {
        await request(`/forum/threads/${thread.thread_id}/replies`, { method: 'POST', body: await formBody(form, ['body']) });
        form.reset(); showMessage('Balasan terkirim.', 'success'); await refresh();
      } catch (error) { showMessage(error.message); }
      finally { submit.disabled = false; }
    });
    return form;
  }
  function renderCategory(data) {
    forumOpen = data.isOpen;
    // These page-header elements are siblings of [data-forum-view], not its children.
    const status = document.querySelector('[data-forum-status]');
    if (status) {
      status.textContent = readOnly ? 'Arsip · hanya dapat dibaca' : forumOpen ? 'Forum dibuka' : 'Forum ditutup';
      status.className = `inline-flex rounded-md px-2.5 py-1.5 text-xs font-semibold ${forumOpen && !readOnly ? 'bg-green-50 text-green-800' : 'bg-surface-container text-on-surface-variant'}`;
    }
    const newTopicLink = root.querySelector('[data-new-topic-link]');
    if (newTopicLink) newTopicLink.hidden = !forumOpen || readOnly;
    const list = root.querySelector('[data-forum-list]');
    if (!list) return;
    list.replaceChildren();
    const threads = data.threads || [];
    const threadCount = document.querySelector('[data-thread-count]');
    if (threadCount) threadCount.textContent = `${threads.length} diskusi`;
    if (!threads.length) list.append(make('div', 'rounded-xl border border-dashed border-outline-variant/50 bg-surface-container-lowest p-8 text-center text-sm text-on-surface-variant', 'Belum ada diskusi di sesi ini. Mulai percakapan pertama.'));
    threads.forEach(thread => {
      const post = renderPost(thread);
      const link = document.createElement('a');
      link.href = `?route=/student/forum/thread&id=${encodeURIComponent(thread.thread_id)}&course_id=${encodeURIComponent(courseCode)}&session_id=${encodeURIComponent(sessionCode)}${readOnly ? '&is_riwayat=true' : ''}`;
      link.className = 'mt-3 inline-flex text-sm font-semibold text-primary hover:underline';
      link.textContent = 'Buka diskusi';
      post.append(link); list.append(post);
    });
  }
  async function refresh() {
    if (!apiBase) { showMessage('Alamat layanan forum belum dikonfigurasi.'); return; }
    try {
      if (view === 'category') {
        const data = await request(`/forum/courses/${encodeURIComponent(courseCode)}/sessions/${encodeURIComponent(sessionCode)}`);
        renderCategory(data);
      } else if (view === 'thread') {
        const session = await request(sessionPath);
        forumOpen = session.isOpen;
        const data = await request(`/forum/threads/${encodeURIComponent(threadId)}`);
        const area = root.querySelector('[data-thread-content]');
        if (!area) throw new Error('Area isi diskusi tidak tersedia. Muat ulang halaman.');
        area.replaceChildren();
        if (!data.thread) throw new Error('Diskusi tidak ditemukan.');
        area.append(renderPost(data.thread));
        const replies = make('section', 'mt-5 rounded-xl border border-outline-variant/40 bg-surface-container-lowest p-4 sm:p-5');
        replies.append(make('h2', 'text-base font-semibold text-on-surface', `Balasan (${data.replies.length})`));
        const replyList = make('div', 'mt-2 divide-y divide-outline-variant/30');
        data.replies.forEach(reply => replyList.append(renderPost(reply, true)));
        if (!data.replies.length) replyList.append(make('p', 'py-4 text-sm text-on-surface-variant', 'Belum ada balasan.'));
        replies.append(replyList);
        if (!readOnly && data.thread.status === 'visible' && forumOpen) replies.append(makeReplyForm(data.thread));
        else replies.append(make('p', 'mt-4 border-t border-outline-variant/30 pt-4 text-xs text-on-surface-variant', 'Forum atau diskusi ini sedang ditutup untuk balasan.'));
        area.append(replies);
      } else if (view === 'new') {
        const session = await request(sessionPath);
        forumOpen = session.isOpen;
        const form = root.querySelector('[data-forum-new-form]');
        if (!forumOpen && form) {
          form.querySelectorAll('input,textarea,button').forEach(control => { control.disabled = true; });
          showMessage('Forum sesi ini sedang ditutup. Anda masih dapat membaca diskusi.', 'error');
        }
      }
    } catch (error) { showMessage(error.message); }
  }
  function connectRealtime() {
    if (!apiBase || !courseCode || !sessionCode || readOnly) return;
    eventSource?.close();
    const query = new URLSearchParams({ courseCode, sessionCode });
    eventSource = new EventSource(`${apiBase}/forum/events?${query}`, { withCredentials: true });
    eventSource.addEventListener('ready', refresh);
    eventSource.addEventListener('forum', refresh);
  }
  function wireNewTopic() {
    const form = root.querySelector('[data-forum-new-form]');
    if (!form) return;
    form.addEventListener('submit', async event => {
      event.preventDefault();
      const submit = form.querySelector('button[type="submit"]'); submit.disabled = true;
      try {
        await request(`/forum/courses/${encodeURIComponent(courseCode)}/sessions/${encodeURIComponent(sessionCode)}/threads`, { method: 'POST', body: await formBody(form, ['title', 'body']) });
        window.location.assign(categoryUrl);
      } catch (error) { showMessage(error.message); }
      finally { submit.disabled = false; }
    });
  }

  async function formBody(form, fields) {
    const data = new FormData(form);
    const image = data.get('image');
    if (image instanceof File && image.size > 0) {
      data.set('image', await compressForumImage(image));
      return data;
    }
    return JSON.stringify(Object.fromEntries(fields.map(name => [name, data.get(name)])));
  }

  const MAX_IMAGE_EDGE = 1920;
  const SMALL_IMAGE_LIMIT = 360 * 1024;
  const MAX_IMAGE_SOURCE_SIZE = 25 * 1024 * 1024;
  async function compressForumImage(file) {
    if (!(file instanceof File) || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > MAX_IMAGE_SOURCE_SIZE) return file;
    if (file.type === 'image/gif' || (file.type === 'image/webp' && await isAnimatedWebP(file))) return file;
    let image;
    let imageUrl = '';
    try {
      if (typeof createImageBitmap === 'function') {
        image = await createImageBitmap(file);
      } else {
        imageUrl = URL.createObjectURL(file);
        image = new Image();
        image.decoding = 'async';
        image.src = imageUrl;
        await image.decode();
      }
      const width = image.width || image.naturalWidth;
      const height = image.height || image.naturalHeight;
      if (!width || !height || (file.size <= SMALL_IMAGE_LIMIT && Math.max(width, height) <= MAX_IMAGE_EDGE)) return file;

      const scale = Math.min(1, MAX_IMAGE_EDGE / Math.max(width, height));
      const canvas = document.createElement('canvas');
      canvas.width = Math.max(1, Math.round(width * scale));
      canvas.height = Math.max(1, Math.round(height * scale));
      const context = canvas.getContext('2d', { alpha: true });
      if (!context) return file;
      context.drawImage(image, 0, 0, canvas.width, canvas.height);

      for (const quality of [0.88, 0.84, 0.8]) {
        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/webp', quality));
        if (!blob || blob.type !== 'image/webp' || blob.size >= file.size) return file;
        if (blob.size <= 2 * 1024 * 1024 || quality === 0.8) {
          const baseName = file.name.replace(/\.[^.]+$/, '') || 'forum-image';
          return new File([blob], `${baseName}.webp`, { type: 'image/webp', lastModified: file.lastModified });
        }
      }
    } catch {
      // If browser compression is unavailable, send the original attachment.
    } finally {
      image?.close?.();
      if (imageUrl) URL.revokeObjectURL(imageUrl);
    }
    return file;
  }

  async function isAnimatedWebP(file) {
    try {
      const bytes = new Uint8Array(await file.slice(0, 64 * 1024).arrayBuffer());
      if (forumAscii(bytes, 0, 4) !== 'RIFF' || forumAscii(bytes, 8, 4) !== 'WEBP') return false;
      const view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);
      let offset = 12;
      while (offset + 8 <= bytes.length) {
        const chunk = forumAscii(bytes, offset, 4);
        if (chunk === 'ANIM' || chunk === 'ANMF') return true;
        offset += 8 + view.getUint32(offset + 4, true);
        if (offset % 2 !== 0) offset += 1;
      }
    } catch { /* Let the API validate unreadable image files. */ }
    return false;
  }

  function forumAscii(bytes, offset, length) {
    return String.fromCharCode(...bytes.subarray(offset, offset + length));
  }

  if (view === 'new') wireNewTopic();
  if (view === 'category' && locked) {
    const status = document.querySelector('[data-forum-status]');
    if (status) status.textContent = 'Sesi belum selesai';
    const list = root.querySelector('[data-forum-list]');
    if (list) list.replaceChildren(make('div', 'rounded-xl border border-outline-variant/40 bg-surface-container-lowest p-8 text-center text-sm text-on-surface-variant', 'Selesaikan materi sesi ini terlebih dahulu untuk membuka forum.'));
    const link = root.querySelector('[data-new-topic-link]'); if (link) link.hidden = true;
  } else {
    refresh();
  }
  if (view === 'category' || view === 'thread') connectRealtime();
  window.addEventListener('pagehide', () => eventSource?.close(), { once: true });
})();
