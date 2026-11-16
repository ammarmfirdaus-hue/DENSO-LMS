import React, { useCallback, useEffect, useRef, useState } from 'react';
import { MessageCircle, Send, Trash2, UserCheck } from 'lucide-react';
import { API_BASE, api } from '../../api/client';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import showAlert from '../../utils/alert';

const CHAT_CACHE_KEY = 'dls_admin_support_chat_cache_v1';
const CHAT_CACHE_TTL_MS = 15 * 60 * 1000; // 15 menit tanpa interaksi -> bersihkan cache pesan

function readChatCacheMap() {
  try {
    const raw = localStorage.getItem(CHAT_CACHE_KEY);
    if (!raw) return {};
    const parsed = JSON.parse(raw);
    return parsed && typeof parsed === 'object' ? parsed : {};
  } catch {
    return {};
  }
}

function writeChatCacheMap(map) {
  try {
    localStorage.setItem(CHAT_CACHE_KEY, JSON.stringify(map));
  } catch {}
}

function getStudentCacheEntry(studentId) {
  if (!studentId) return { lastActivityAt: 0, cutoffAt: 0, messages: [] };
  const map = readChatCacheMap();
  const key = String(studentId);
  const entry = map[key];
  if (!entry || typeof entry !== 'object') {
    return { lastActivityAt: 0, cutoffAt: 0, messages: [] };
  }
  const now = Date.now();
  const lastActivityAt = Number(entry.lastActivityAt || 0);
  const cutoffAt = Number(entry.cutoffAt || 0);
  if (lastActivityAt > 0 && now - lastActivityAt >= CHAT_CACHE_TTL_MS) {
    const expired = { lastActivityAt: 0, cutoffAt: now, messages: [] };
    map[key] = expired;
    writeChatCacheMap(map);
    return expired;
  }
  return {
    lastActivityAt,
    cutoffAt,
    messages: Array.isArray(entry.messages) ? entry.messages : [],
  };
}

function saveStudentCacheEntry(studentId, messages, { touchActivity = true, cutoffAt = null } = {}) {
  if (!studentId) return;
  const map = readChatCacheMap();
  const key = String(studentId);
  const prev = map[key] && typeof map[key] === 'object' ? map[key] : {};
  const nextCutoff = cutoffAt !== null ? cutoffAt : Number(prev.cutoffAt || 0);
  const nextActivity = touchActivity ? Date.now() : Number(prev.lastActivityAt || 0);
  map[key] = {
    lastActivityAt: nextActivity,
    cutoffAt: nextCutoff,
    messages: Array.isArray(messages) ? messages.slice(-100) : [],
  };
  writeChatCacheMap(map);
}

function clearStudentCacheEntry(studentId) {
  if (!studentId) return;
  const map = readChatCacheMap();
  delete map[String(studentId)];
  writeChatCacheMap(map);
}

function filterMessagesAfterCutoff(items, cutoffAt) {
  if (!Array.isArray(items)) return [];
  if (!cutoffAt) return items;
  return items.filter(m => {
    const t = m?.created_at ? new Date(m.created_at).getTime() : 0;
    return !t || t >= cutoffAt;
  });
}

function formatClock(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '';
  return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

export default function DX_LMS_M_Support_Chat() {
  const [conversations, setConversations] = useState([]);
  const [selectedStudentId, setSelectedStudentId] = useState(null);
  const [messages, setMessages] = useState([]);
  const [draft, setDraft] = useState('');
  const [sending, setSending] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [loadingConvos, setLoadingConvos] = useState(true);
  const [presence, setPresence] = useState({ online: true, online_count: 1 });
  const endRef = useRef(null);
  const selectedRef = useRef(null);

  useEffect(() => {
    selectedRef.current = selectedStudentId;
  }, [selectedStudentId]);

  const loadConversations = useCallback(async () => {
    try {
      const res = await api('/support-chat/conversations');
      const rows = Array.isArray(res?.data) ? res.data : [];
      setConversations(rows);
      if (!selectedRef.current && rows.length > 0) {
        setSelectedStudentId(rows[0].student_id);
      }
    } finally {
      setLoadingConvos(false);
    }
  }, []);

  const loadMessages = useCallback(async (studentId, { touchActivity = false } = {}) => {
    if (!studentId) return;
    const cached = getStudentCacheEntry(studentId);
    if (cached.messages.length > 0) {
      setMessages(cached.messages);
    } else if (cached.cutoffAt > 0) {
      setMessages([]);
    }

    const res = await api(`/support-chat/messages?student_id=${encodeURIComponent(studentId)}&mark_read=1`);
    const fresh = filterMessagesAfterCutoff(Array.isArray(res?.data) ? res.data : [], cached.cutoffAt);
    setMessages(fresh);
    if (fresh.length > 0 || touchActivity) {
      saveStudentCacheEntry(studentId, fresh, { touchActivity: touchActivity || fresh.length > 0 });
    }
    if (res?.presence) setPresence(res.presence);
    setConversations(prev =>
      prev.map(c => (Number(c.student_id) === Number(studentId) ? { ...c, unread_count: 0 } : c))
    );
  }, []);

  useEffect(() => {
    loadConversations();
  }, [loadConversations]);

  useEffect(() => {
    if (selectedStudentId) {
      loadMessages(selectedStudentId);
    }
  }, [selectedStudentId, loadMessages]);

  useEffect(() => {
    const timer = setInterval(() => {
      if (!selectedRef.current) return;
      const entry = getStudentCacheEntry(selectedRef.current);
      if (entry.lastActivityAt === 0 && entry.cutoffAt > 0) {
        setMessages([]);
      }
    }, 15000);
    return () => clearInterval(timer);
  }, []);

  useEffect(() => {
    endRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  useEffect(() => {
    const sse = new EventSource(`${API_BASE}/support-chat/stream`, { withCredentials: true });
    sse.addEventListener('ready', event => {
      try {
        const data = JSON.parse(event.data || '{}');
        if (data.presence) setPresence(data.presence);
      } catch {}
    });
    sse.addEventListener('presence', event => {
      try {
        setPresence(JSON.parse(event.data || '{}'));
      } catch {}
    });
    sse.addEventListener('message', event => {
      try {
        const msg = JSON.parse(event.data || '{}');
        if (!msg?.message_id) return;
        loadConversations();
        if (Number(msg.student_id) === Number(selectedRef.current)) {
          setMessages(prev => {
            const next = prev.some(m => m.message_id === msg.message_id) ? prev : [...prev, msg];
            saveStudentCacheEntry(msg.student_id, next, { touchActivity: true });
            return next;
          });
          if (msg.sender_role === 'student') {
            loadMessages(msg.student_id, { touchActivity: true });
          }
        }
      } catch {}
    });
    sse.addEventListener('cleared', event => {
      try {
        const data = JSON.parse(event.data || '{}');
        if (!data?.student_id) return;
        clearStudentCacheEntry(data.student_id);
        setConversations(prev => prev.filter(c => Number(c.student_id) !== Number(data.student_id)));
        if (Number(data.student_id) === Number(selectedRef.current)) {
          setMessages([]);
          setSelectedStudentId(null);
        }
      } catch {}
    });
    return () => sse.close();
  }, [loadConversations, loadMessages]);

  const handleSend = async event => {
    event.preventDefault();
    const text = draft.trim();
    if (!text || !selectedStudentId || sending) return;
    setSending(true);
    try {
      const res = await api('/support-chat/messages', {
        method: 'POST',
        body: JSON.stringify({ student_id: Number(selectedStudentId), message_text: text }),
      });
      setDraft('');
      if (res?.data) {
        setMessages(prev => {
          const next = prev.some(m => m.message_id === res.data.message_id) ? prev : [...prev, res.data];
          saveStudentCacheEntry(selectedStudentId, next, { touchActivity: true });
          return next;
        });
        loadConversations();
      }
    } finally {
      setSending(false);
    }
  };

  const activeConversation = conversations.find(c => Number(c.student_id) === Number(selectedStudentId)) || null;

  const handleDeleteHistory = async conversation => {
    if (!conversation?.student_id || deleting) return;
    const confirmed = await showAlert.confirm({
      title: 'Hapus Riwayat Percakapan?',
      text: `Seluruh riwayat chat dengan ${conversation.student_name} (NPK ${conversation.student_npk}) akan dihapus permanen.`,
      confirmText: 'Ya, Hapus',
      cancelText: 'Batal',
      danger: true,
      icon: 'warning',
    });
    if (!confirmed) return;

    setDeleting(true);
    try {
      await api(`/support-chat/conversations/${encodeURIComponent(conversation.student_id)}`, {
        method: 'DELETE',
      });
      clearStudentCacheEntry(conversation.student_id);
      setConversations(prev => {
        const next = prev.filter(c => Number(c.student_id) !== Number(conversation.student_id));
        if (Number(selectedRef.current) === Number(conversation.student_id)) {
          setSelectedStudentId(next[0]?.student_id ?? null);
          setMessages([]);
        }
        return next;
      });
      showAlert.success('Riwayat Dihapus', `Riwayat chat ${conversation.student_name} berhasil dihapus.`);
    } catch (err) {
      showAlert.error('Gagal Menghapus', err.message || 'Tidak dapat menghapus riwayat percakapan.');
    } finally {
      setDeleting(false);
    }
  };

  return (
    <section className="space-y-4">
      <div className="dls-card-surface flex flex-wrap items-center justify-between gap-3 rounded-xl p-4">
        <div className="flex items-center gap-3">
          <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400">
            <MessageCircle className="h-5 w-5" />
          </span>
          <div>
            <h1 className="text-lg font-bold text-slate-900 dark:text-white">Pusat Bantuan</h1>
            <p className="text-xs text-slate-500 dark:text-slate-400">Balas pesan siswa secara real-time (riwayat cache otomatis dibersihkan setelah 15 menit tanpa interaksi).</p>
          </div>
        </div>
        <StatusBadge
          status={presence.online ? 'active' : 'pending'}
          label={presence.online ? `Admin Online (${presence.online_count})` : 'Menghubungkan...'}
          dot
          className="px-3 py-1.5"
        />
      </div>

      <div className="grid grid-cols-1 gap-4 lg:grid-cols-[300px_minmax(0,1fr)]">
        {/* Conversation List */}
        <aside className="dls-card-surface flex h-[540px] flex-col overflow-hidden rounded-xl">
          <div className="border-b border-slate-100 px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500 dark:border-slate-800">
            Percakapan Siswa ({conversations.length})
          </div>
          <div className="flex-1 divide-y divide-slate-100 overflow-y-auto dark:divide-slate-800">
            {loadingConvos && <p className="p-4 text-xs text-slate-500">Memuat percakapan...</p>}
            {!loadingConvos && conversations.length === 0 && (
              <p className="p-6 text-center text-xs text-slate-500">Belum ada pesan masuk dari siswa.</p>
            )}
            {conversations.map(c => {
              const isSelected = Number(c.student_id) === Number(selectedStudentId);
              return (
                <button
                  key={c.student_id}
                  type="button"
                  onClick={() => setSelectedStudentId(c.student_id)}
                  className={`flex w-full items-start justify-between gap-2 px-4 py-3 text-left transition ${
                    isSelected
                      ? 'bg-red-50/80 dark:bg-red-950/30'
                      : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'
                  }`}
                >
                  <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-bold text-slate-900 dark:text-white">{c.student_name}</p>
                    <p className="text-[11px] font-mono text-slate-400">NPK {c.student_npk}</p>
                    <p className="mt-1 truncate text-xs text-slate-600 dark:text-slate-300">{c.last_message}</p>
                  </div>
                  <div className="flex flex-col items-end gap-1 shrink-0">
                    <span className="text-[10px] text-slate-400">{formatClock(c.last_message_at)}</span>
                    {Number(c.unread_count) > 0 && (
                      <span className="inline-flex min-w-[18px] items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-bold text-white">
                        {c.unread_count}
                      </span>
                    )}
                  </div>
                </button>
              );
            })}
          </div>
        </aside>

        {/* Active Chat Thread */}
        <div className="dls-card-surface flex h-[540px] flex-col overflow-hidden rounded-xl">
          {activeConversation ? (
            <>
              <header className="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                <div className="flex items-center gap-2.5 min-w-0">
                  <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-950/50 dark:text-red-400">
                    <UserCheck className="h-4 w-4" />
                  </span>
                  <div className="min-w-0">
                    <p className="truncate text-sm font-bold text-slate-900 dark:text-white">{activeConversation.student_name}</p>
                    <p className="text-[11px] text-slate-500">NPK {activeConversation.student_npk}</p>
                  </div>
                </div>
                <Button
                  type="button"
                  variant="danger"
                  size="sm"
                  icon={<Trash2 className="h-3.5 w-3.5" />}
                  loading={deleting}
                  onClick={() => handleDeleteHistory(activeConversation)}
                >
                  Hapus Riwayat
                </Button>
              </header>

              <div className="flex-1 space-y-3 overflow-y-auto bg-slate-50/70 p-4 dark:bg-slate-950/50">
                {messages.map(m => {
                  const isMe = m.sender_role === 'admin';
                  return (
                    <div key={m.message_id} className={`flex flex-col ${isMe ? 'items-end' : 'items-start'}`}>
                      <div
                        className={`max-w-[78%] rounded-2xl px-3.5 py-2 text-xs leading-relaxed break-words shadow-xs ${
                          isMe
                            ? 'rounded-br-sm bg-red-600 text-white'
                            : 'rounded-bl-sm border border-slate-200 bg-white text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100'
                        }`}
                      >
                        {m.message_text}
                      </div>
                      <span className="mt-1 px-1 text-[10px] text-slate-400">
                        {m.sender_name} · {formatClock(m.created_at)}
                      </span>
                    </div>
                  );
                })}
                <div ref={endRef} />
              </div>

              <form onSubmit={handleSend} className="flex items-center gap-2 border-t border-slate-100 p-3 dark:border-slate-800">
                <input
                  type="text"
                  value={draft}
                  onChange={e => setDraft(e.target.value)}
                  maxLength={1000}
                  placeholder="Tulis balasan untuk siswa..."
                  className="h-10 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3.5 text-xs text-slate-900 outline-none focus:border-red-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                />
                <button
                  type="submit"
                  disabled={sending || !draft.trim()}
                  className="inline-flex h-10 items-center gap-1.5 rounded-xl bg-red-600 px-4 text-xs font-bold text-white transition hover:bg-red-700 disabled:opacity-50"
                >
                  <Send className="h-4 w-4" />
                  <span>Kirim</span>
                </button>
              </form>
            </>
          ) : (
            <div className="grid flex-1 place-items-center p-8 text-center text-sm text-slate-500">
              Pilih percakapan siswa di sebelah kiri untuk mulai membalas pesan secara real-time.
            </div>
          )}
        </div>
      </div>
    </section>
  );
}

