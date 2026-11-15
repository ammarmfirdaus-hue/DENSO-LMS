<?php
/**
 * Floating Student <-> Admin Realtime Support Chat Widget (Rumahweb-inspired solid UI).
 * Included globally via DX_LMS_M_Student_Bottom_Nav.php.
 */
$dlsStudentName = trim((string)($_SESSION['user']['full_name'] ?? $_SESSION['user']['name'] ?? 'Student'));
if ($dlsStudentName === '') {
    $dlsStudentName = 'Student';
}
$dlsChatApiBase = defined('SSO_API_URL') ? rtrim((string)SSO_API_URL, '/') : '';
$dlsAssetPrefix = isset($base_path) ? (string)$base_path : '';
$dlsLogoLight = $dlsAssetPrefix . 'assets/img/icon/denso-vector-logo.png';
$dlsLogoDark = $dlsAssetPrefix . 'assets/img/icon/denso-vector-logo-dark.png';
?>
<div id="dlsSupportChatRoot" class="dls-chat-root" data-api-base="<?= htmlspecialchars($dlsChatApiBase, ENT_QUOTES, 'UTF-8') ?>" data-student-name="<?= htmlspecialchars($dlsStudentName, ENT_QUOTES, 'UTF-8') ?>">
  <!-- Chat Modal -->
  <section
    id="dlsSupportChatBox"
    class="dls-chat-window"
    role="dialog"
    aria-label="Live Chat Admin DENSO LMS"
    aria-hidden="true"
    style="display: none;"
  >
    <!-- Solid Header -->
    <header class="dls-chat-header">
      <div class="dls-chat-header-left">
        <div class="dls-chat-avatar" aria-hidden="true">
          <img
            src="<?= htmlspecialchars($dlsLogoDark, ENT_QUOTES, 'UTF-8') ?>"
            alt="Logo DENSO"
            class="dls-chat-avatar-img"
            onerror="this.onerror=null;this.src='<?= htmlspecialchars($dlsLogoLight, ENT_QUOTES, 'UTF-8') ?>';"
          />
          <span id="dlsSupportAvatarDot" class="dls-chat-avatar-dot" title="Status Admin"></span>
        </div>
        <div class="dls-chat-header-meta">
          <div class="dls-chat-title">Live Chat Admin</div>
          <div class="dls-chat-status-row">
            <span id="dlsSupportPresenceBadge" class="dls-chat-status-pill is-offline">
              <span id="dlsSupportPresenceDot" class="dls-chat-status-dot"></span>
              <span id="dlsSupportPresenceText">Mengecek status...</span>
            </span>
          </div>
        </div>
      </div>

      <div class="dls-chat-header-actions">
        <button
          type="button"
          id="dlsSupportMinimizeBtn"
          class="dls-chat-icon-btn"
          title="Perkecil"
          aria-label="Perkecil jendela chat"
        >
          <span class="material-symbols-outlined">remove</span>
        </button>
        <button
          type="button"
          id="dlsSupportCloseBtn"
          class="dls-chat-icon-btn"
          title="Tutup"
          aria-label="Tutup jendela chat"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
    </header>

    <!-- Connection / Error Banner -->
    <div id="dlsSupportNotice" class="dls-chat-notice" style="display: none;" role="status"></div>

    <!-- Scrollable Body -->
    <div id="dlsSupportMessages" class="dls-chat-body">
      <!-- Rumahweb-style Welcome Card -->
      <div class="dls-chat-welcome">
        <div class="dls-chat-welcome-head">
          <span class="material-symbols-outlined">waving_hand</span>
          <strong>Halo, <?= htmlspecialchars($dlsStudentName, ENT_QUOTES, 'UTF-8') ?>!</strong>
        </div>
        <p class="dls-chat-welcome-desc">
          Selamat datang di Live Support DENSO LMS. Ada kendala belajar atau teknis? Pilih topik cepat di bawah atau tulis pesan langsung ke Admin.
        </p>
        <div id="dlsSupportQuickTopics" class="dls-chat-topics">
          <button type="button" class="dls-chat-topic-btn" data-topic="Halo Admin, saya mengalami kendala akses course.">
            Kendala Akses Course
          </button>
          <button type="button" class="dls-chat-topic-btn" data-topic="Halo Admin, saya ingin bertanya terkait quiz atau nilai.">
            Pertanyaan Quiz / Nilai
          </button>
          <button type="button" class="dls-chat-topic-btn" data-topic="Halo Admin, saya butuh bantuan teknis atau akun.">
            Masalah Akun / Teknis
          </button>
          <button type="button" class="dls-chat-topic-btn" data-topic="Halo Admin, saya ingin berkonsultasi langsung.">
            Hubungi Admin
          </button>
        </div>
      </div>

      <!-- Dynamic Messages Container -->
      <div id="dlsSupportMessageList" class="dls-chat-msg-list"></div>
    </div>

    <!-- Composer Footer -->
    <form id="dlsSupportForm" class="dls-chat-footer" autocomplete="off">
      <div class="dls-chat-input-wrap">
        <input
          id="dlsSupportInput"
          type="text"
          maxlength="2000"
          placeholder="Tulis pesan untuk Admin..."
          class="dls-chat-input"
          aria-label="Tulis pesan untuk Admin"
        />
        <button
          id="dlsSupportSendBtn"
          type="submit"
          class="dls-chat-send-btn"
          title="Kirim pesan"
          aria-label="Kirim pesan"
        >
          <span class="material-symbols-outlined">send</span>
        </button>
      </div>
      <div class="dls-chat-footer-hint">
        <span>Tekan Enter untuk kirim</span>
        <span id="dlsSupportConnMode">Realtime</span>
      </div>
    </form>
  </section>

  <!-- Floating Launcher Bubble -->
  <button
    id="dlsSupportToggleBtn"
    type="button"
    class="dls-chat-launcher"
    aria-label="Buka Live Chat Admin"
    aria-expanded="false"
    aria-controls="dlsSupportChatBox"
    title="Chat Admin"
  >
    <span class="dls-chat-launcher-icon material-symbols-outlined" aria-hidden="true">chat</span>
    <span class="dls-chat-launcher-label">Chat Admin</span>
    <span id="dlsSupportLauncherDot" class="dls-chat-launcher-dot" aria-hidden="true"></span>
    <span id="dlsSupportUnreadBadge" class="dls-chat-unread-badge" style="display: none;">0</span>
  </button>
</div>

<script>
(function () {
  function initSupportChatWidget() {
    var root = document.getElementById('dlsSupportChatRoot');
    if (!root || root.dataset.initialized === '1') return;
    root.dataset.initialized = '1';

    var toggleBtn = document.getElementById('dlsSupportToggleBtn');
    var chatBox = document.getElementById('dlsSupportChatBox');
    var minimizeBtn = document.getElementById('dlsSupportMinimizeBtn');
    var closeBtn = document.getElementById('dlsSupportCloseBtn');
    var noticeEl = document.getElementById('dlsSupportNotice');
    var messagesWrap = document.getElementById('dlsSupportMessages');
    var messageListEl = document.getElementById('dlsSupportMessageList');
    var form = document.getElementById('dlsSupportForm');
    var input = document.getElementById('dlsSupportInput');
    var sendBtn = document.getElementById('dlsSupportSendBtn');
    var presenceBadge = document.getElementById('dlsSupportPresenceBadge');
    var presenceText = document.getElementById('dlsSupportPresenceText');
    var avatarDot = document.getElementById('dlsSupportAvatarDot');
    var launcherDot = document.getElementById('dlsSupportLauncherDot');
    var unreadBadge = document.getElementById('dlsSupportUnreadBadge');
    var connModeEl = document.getElementById('dlsSupportConnMode');
    var topicButtons = root.querySelectorAll('.dls-chat-topic-btn');

    if (!toggleBtn || !chatBox) return;

    var metaBase = document.querySelector('meta[name="dls-api-base"]');
    var apiBase = (root.getAttribute('data-api-base') || (metaBase ? metaBase.getAttribute('content') : '') || '').replace(/\/+$/, '');

    var isOpen = false;
    var isSending = false;
    var isFetching = false;
    var lastId = 0;
    var unreadCount = 0;
    var renderedIds = new Set();
    var sseSource = null;
    var pollTimer = null;

    var CACHE_KEY = 'dls_student_support_chat_cache_v1';
    var CACHE_TTL_MS = 15 * 60 * 1000; // 15 menit tanpa interaksi/pesan
    var cachedMessages = [];
    var lastActivityAt = 0;
    var cutoffAt = 0;

    function readCache() {
      try {
        var raw = localStorage.getItem(CACHE_KEY);
        if (!raw) return;
        var parsed = JSON.parse(raw);
        if (!parsed || typeof parsed !== 'object') return;
        var savedActivity = Number(parsed.lastActivityAt || 0);
        var savedCutoff = Number(parsed.cutoffAt || 0);
        if (savedActivity > 0 && Date.now() - savedActivity >= CACHE_TTL_MS) {
          cutoffAt = Date.now();
          lastActivityAt = 0;
          cachedMessages = [];
          writeCache();
          return;
        }
        lastActivityAt = savedActivity;
        cutoffAt = savedCutoff;
        cachedMessages = Array.isArray(parsed.messages) ? parsed.messages : [];
      } catch (_e) {}
    }

    function writeCache() {
      try {
        localStorage.setItem(CACHE_KEY, JSON.stringify({
          lastActivityAt: lastActivityAt,
          cutoffAt: cutoffAt,
          messages: cachedMessages.slice(-100)
        }));
      } catch (_e) {}
    }

    function clearExpiredCacheIfNeeded() {
      if (lastActivityAt > 0 && Date.now() - lastActivityAt >= CACHE_TTL_MS) {
        cutoffAt = Date.now();
        lastActivityAt = 0;
        cachedMessages = [];
        renderedIds.clear();
        if (messageListEl) messageListEl.innerHTML = '';
        writeCache();
        return true;
      }
      return false;
    }

    function touchActivity() {
      lastActivityAt = Date.now();
      writeCache();
    }

    function requestApi(url, options) {
      var opts = Object.assign({ credentials: 'include' }, options || {});
      if (typeof window.dlsFetch === 'function') {
        return window.dlsFetch(url, opts);
      }
      return fetch(url, opts);
    }

    function showNotice(text, isError) {
      if (!noticeEl) return;
      if (!text) {
        noticeEl.style.display = 'none';
        noticeEl.textContent = '';
        noticeEl.classList.remove('is-error');
        return;
      }
      noticeEl.textContent = text;
      noticeEl.style.display = 'block';
      noticeEl.classList.toggle('is-error', Boolean(isError));
    }

    var closeTimer = null;

    function setOpenState(nextOpen) {
      isOpen = Boolean(nextOpen);
      if (closeTimer) {
        clearTimeout(closeTimer);
        closeTimer = null;
      }

      chatBox.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
      toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      toggleBtn.classList.toggle('is-active', isOpen);

      if (isOpen) {
        chatBox.classList.remove('is-closing');
        chatBox.style.display = 'flex';
        void chatBox.offsetWidth;
        chatBox.classList.add('is-opening');

        unreadCount = 0;
        updateUnreadBadge();
        syncMessages(true);
        startRealtime();
        setTimeout(function () {
          scrollToBottom();
          if (input) input.focus();
        }, 40);
      } else {
        if (chatBox.style.display === 'none') return;
        chatBox.classList.remove('is-opening');
        void chatBox.offsetWidth;
        chatBox.classList.add('is-closing');
        closeTimer = setTimeout(function () {
          closeTimer = null;
          if (!isOpen) {
            chatBox.style.display = 'none';
            chatBox.classList.remove('is-closing');
          }
        }, 260);
      }
    }

    window.openStudentSupportChat = function () {
      setOpenState(true);
    };

    function updateUnreadBadge() {
      if (!unreadBadge) return;
      if (unreadCount > 0 && !isOpen) {
        unreadBadge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
        unreadBadge.style.display = 'inline-flex';
      } else {
        unreadBadge.style.display = 'none';
      }
    }

    function updatePresence(presence) {
      var online = Boolean(presence && presence.online);
      var onlineCount = Number(presence && presence.online_count ? presence.online_count : 0);
      if (presenceBadge) {
        presenceBadge.classList.toggle('is-online', online);
        presenceBadge.classList.toggle('is-offline', !online);
      }
      if (presenceText) {
        presenceText.textContent = online
          ? (onlineCount > 1 ? 'Online (' + onlineCount + ' Admin)' : 'Online · Terhubung')
          : 'Admin Offline · Tinggalkan pesan';
      }
      if (avatarDot) avatarDot.classList.toggle('is-online', online);
      if (launcherDot) launcherDot.classList.toggle('is-online', online);
    }

    function formatTime(iso) {
      if (!iso) return '';
      var d = new Date(iso);
      if (Number.isNaN(d.getTime())) return '';
      return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

    function scrollToBottom() {
      if (!messagesWrap) return;
      messagesWrap.scrollTop = messagesWrap.scrollHeight;
    }

    function appendMessage(msg, fromCache) {
      if (!msg || !messageListEl) return;
      var rawId = Number(msg.message_id || msg.id || 0);
      var msgTime = msg.created_at ? new Date(msg.created_at).getTime() : Date.now();
      if (!fromCache && cutoffAt > 0 && msgTime > 0 && msgTime < cutoffAt) {
        if (rawId > lastId) lastId = rawId;
        return;
      }

      if (rawId > 0 && renderedIds.has(rawId)) return;
      if (rawId > 0) {
        renderedIds.add(rawId);
        if (rawId > lastId) lastId = rawId;
      }

      if (!fromCache) {
        cachedMessages.push(msg);
        touchActivity();
      }

      var isStudent = String(msg.sender_role || '').toUpperCase() === 'STUDENT';
      var row = document.createElement('div');
      row.className = 'dls-chat-row ' + (isStudent ? 'is-student' : 'is-admin');

      var bubble = document.createElement('div');
      bubble.className = 'dls-chat-bubble ' + (isStudent ? 'is-student' : 'is-admin');

      if (!isStudent) {
        var senderEl = document.createElement('div');
        senderEl.className = 'dls-chat-sender';
        senderEl.textContent = msg.sender_name || 'Admin Support';
        bubble.appendChild(senderEl);
      }

      var bodyEl = document.createElement('div');
      bodyEl.className = 'dls-chat-text';
      bodyEl.textContent = String(msg.message_text || '');
      bubble.appendChild(bodyEl);

      var timeEl = document.createElement('div');
      timeEl.className = 'dls-chat-time';
      timeEl.textContent = formatTime(msg.created_at) || 'Baru saja';
      bubble.appendChild(timeEl);

      row.appendChild(bubble);
      messageListEl.appendChild(row);
      scrollToBottom();
    }

    async function syncMessages(markRead) {
      if (!apiBase) {
        showNotice('Endpoint chat belum dikonfigurasi.', true);
        return;
      }
      if (isFetching) return;
      isFetching = true;
      try {
        var params = new URLSearchParams();
        if (lastId > 0) params.set('after_id', String(lastId));
        if (markRead) params.set('mark_read', '1');

        var res = await requestApi(apiBase + '/support-chat/messages?' + params.toString(), {
          method: 'GET',
          headers: { Accept: 'application/json' }
        });

        if (!res.ok) {
          if (res.status === 401) {
            showNotice('Sesi login belum aktif di server API. Silakan muat ulang halaman.', true);
          } else {
            showNotice('Gagal memuat riwayat chat (' + res.status + ').', true);
          }
          return;
        }

        var payload = await res.json();
        showNotice('', false);

        var nextPresence = payload && (payload.presence || payload.admin_presence);
        if (nextPresence) {
          updatePresence(nextPresence);
        }

        var items = Array.isArray(payload && payload.data)
          ? payload.data
          : (Array.isArray(payload && payload.messages) ? payload.messages : []);
        for (var i = 0; i < items.length; i += 1) {
          appendMessage(items[i]);
        }
      } catch (_err) {
        showNotice('Tidak dapat terhubung ke server chat. Memeriksa ulang...', true);
      } finally {
        isFetching = false;
      }
    }

    async function sendMessageText(rawText) {
      var text = String(rawText || '').trim();
      if (!text || isSending) return;

      if (!apiBase) {
        showNotice('Endpoint chat belum dikonfigurasi.', true);
        return;
      }

      isSending = true;
      if (sendBtn) sendBtn.disabled = true;
      if (input) input.disabled = true;

      try {
        var res = await requestApi(apiBase + '/support-chat/messages', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json'
          },
          body: JSON.stringify({ message_text: text })
        });

        var payload = await res.json().catch(function () { return {}; });
        if (!res.ok) {
          showNotice(payload.message || 'Gagal mengirim pesan (' + res.status + ').', true);
          return;
        }

        showNotice('', false);
        if (input) input.value = '';
        var savedMsg = payload && (payload.data || payload.message);
        if (savedMsg) {
          appendMessage(savedMsg);
        } else {
          await syncMessages(true);
        }
      } catch (_err) {
        showNotice('Koneksi terputus saat mengirim pesan.', true);
      } finally {
        isSending = false;
        if (sendBtn) sendBtn.disabled = false;
        if (input) {
          input.disabled = false;
          if (isOpen) input.focus();
        }
      }
    }

    function startPollingFallback() {
      if (pollTimer) return;
      if (connModeEl) connModeEl.textContent = 'Sinkronisasi otomatis';
      pollTimer = setInterval(function () {
        if (document.visibilityState === 'hidden') return;
        syncMessages(isOpen);
      }, 6000);
    }

    function startRealtime() {
      if (!apiBase) return;
      if (typeof window.EventSource !== 'function') {
        startPollingFallback();
        return;
      }
      if (sseSource) return;

      try {
        sseSource = new EventSource(apiBase + '/support-chat/stream', { withCredentials: true });
        if (connModeEl) connModeEl.textContent = 'Realtime';

        sseSource.addEventListener('ready', function (evt) {
          try {
            var data = JSON.parse(evt.data || '{}');
            var p = data.presence || data.admin_presence;
            if (p) updatePresence(p);
            showNotice('', false);
          } catch (_e) {}
        });

        sseSource.addEventListener('presence', function (evt) {
          try {
            var data = JSON.parse(evt.data || '{}');
            updatePresence(data.presence || data.data || data);
          } catch (_e) {}
        });

        sseSource.addEventListener('message', function (evt) {
          try {
            var msg = JSON.parse(evt.data || '{}');
            appendMessage(msg);
            var isAdminMsg = String(msg.sender_role || '').toUpperCase() !== 'STUDENT';
            if (isAdminMsg && !isOpen) {
              unreadCount += 1;
              updateUnreadBadge();
            } else if (isAdminMsg && isOpen) {
              syncMessages(true);
            }
          } catch (_e) {}
        });

        sseSource.onerror = function () {
          if (sseSource) {
            sseSource.close();
            sseSource = null;
          }
          startPollingFallback();
        };
      } catch (_err) {
        startPollingFallback();
      }
    }

    // Bind UI handlers unconditionally so clicking the bubble ALWAYS works
    toggleBtn.addEventListener('click', function () {
      setOpenState(!isOpen);
    });

    if (minimizeBtn) {
      minimizeBtn.addEventListener('click', function () {
        setOpenState(false);
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        setOpenState(false);
      });
    }

    document.addEventListener('keydown', function (evt) {
      if (evt.key === 'Escape' && isOpen) {
        setOpenState(false);
      }
    });

    document.addEventListener('click', function (evt) {
      if (!isOpen) return;
      var target = evt.target;
      if (root.contains(target)) return;
      if (target && target.closest && target.closest('[data-open-support-chat]')) return;
      setOpenState(false);
    });

    if (form) {
      form.addEventListener('submit', function (evt) {
        evt.preventDefault();
        touchActivity();
        sendMessageText(input ? input.value : '');
      });
    }

    if (input) {
      input.addEventListener('input', function () {
        if (cachedMessages.length > 0 || isOpen) touchActivity();
      });
    }

    topicButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var topicText = btn.getAttribute('data-topic') || btn.textContent || '';
        touchActivity();
        sendMessageText(topicText);
      });
    });

    // Restore cached messages (or clear if inactive >= 15 minutes)
    readCache();
    for (var cIdx = 0; cIdx < cachedMessages.length; cIdx += 1) {
      appendMessage(cachedMessages[cIdx], true);
    }

    // Periodically check 15-minute inactivity expiry while widget is alive
    setInterval(function () {
      clearExpiredCacheIfNeeded();
    }, 15000);

    // Initial background presence check & realtime listener
    if (apiBase) {
      requestApi(apiBase + '/support-chat/presence', {
        method: 'GET',
        headers: { Accept: 'application/json' }
      })
        .then(function (res) { return res.ok ? res.json() : null; })
        .then(function (payload) {
          var p = payload && (payload.data || payload);
          if (p) updatePresence(p);
        })
        .catch(function () {});

      startRealtime();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSupportChatWidget);
  } else {
    initSupportChatWidget();
  }
})();
</script>
