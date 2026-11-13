import { useEffect, useMemo, useState } from 'react';
import { useOutletContext } from 'react-router-dom';
import {
  AlertTriangle,
  Check,
  CheckCircle2,
  Eye,
  GraduationCap,
  History,
  KeyRound,
  Layers,
  Lock,
  RefreshCw,
  RotateCcw,
  Save,
  Search,
  Shield,
  ShieldAlert,
  ShieldCheck,
  Sparkles,
  UserCheck,
  Users
} from 'lucide-react';
import { api } from '../../api/client';
import PageHeader from '../../components/PageHeader';
import StatCard from '../../components/StatCard';
import Card from '../../components/Card';
import Button from '../../components/Button';
import StatusBadge from '../../components/StatusBadge';
import showAlert from '../../utils/alert';

const ROLE_VISUALS = {
  admin: {
    Icon: ShieldAlert,
    accent: 'bg-primary/10 text-primary dark:bg-red-950/60 dark:text-red-300',
    ring: 'border-primary/40 ring-2 ring-primary/15',
    badgeLabel: 'Administrator'
  },
  teacher: {
    Icon: GraduationCap,
    accent: 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300',
    ring: 'border-blue-500/40 ring-2 ring-blue-500/15',
    badgeLabel: 'Teacher'
  },
  reviewer: {
    Icon: Eye,
    accent: 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300',
    ring: 'border-purple-500/40 ring-2 ring-purple-500/15',
    badgeLabel: 'Reviewer'
  },
  student: {
    Icon: Users,
    accent: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    ring: 'border-slate-400/50 ring-2 ring-slate-400/15',
    badgeLabel: 'Student'
  }
};

const RISK_META = {
  low: {
    label: 'Rendah',
    className:
      'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/50 dark:text-emerald-300'
  },
  medium: {
    label: 'Menengah',
    className:
      'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800/60 dark:bg-blue-950/50 dark:text-blue-300'
  },
  high: {
    label: 'Tinggi',
    className:
      'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800/60 dark:bg-amber-950/50 dark:text-amber-300'
  },
  critical: {
    label: 'Kritis',
    className:
      'border-red-200 bg-red-50 text-red-700 dark:border-red-800/60 dark:bg-red-950/50 dark:text-red-300'
  }
};

const INPUT_CLASS =
  'h-10 w-full rounded-lg border border-outline bg-surface-lowest px-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';

/**
 * Menghitung selisih perubahan antara konfigurasi role tersimpan dengan draft editor.
 *
 * @param {object|null} savedRole Data role tersimpan dari server.
 * @param {object|null} draftRole Data role yang sedang disunting.
 * @param {Array} catalog Katalog seluruh izin sistem.
 * @returns {{ added: string[], removed: string[], metaChanged: boolean, portalChanged: boolean, hasChanges: boolean }}
 */
function calculateRoleDiff(savedRole, draftRole, catalog) {
  if (!savedRole || !draftRole) {
    return { added: [], removed: [], metaChanged: false, portalChanged: false, hasChanges: false };
  }
  const prevSet = new Set(savedRole.permissions || []);
  const nextSet = new Set(draftRole.permissions || []);
  const added = [];
  const removed = [];
  for (const item of catalog) {
    if (!prevSet.has(item.key) && nextSet.has(item.key)) added.push(item.key);
    if (prevSet.has(item.key) && !nextSet.has(item.key)) removed.push(item.key);
  }
  const metaChanged =
    String(savedRole.display_name || '') !== String(draftRole.display_name || '') ||
    String(savedRole.description || '') !== String(draftRole.description || '');
  const portalChanged =
    Boolean(savedRole.can_access_staff_portal) !== Boolean(draftRole.can_access_staff_portal);
  return {
    added,
    removed,
    metaChanged,
    portalChanged,
    hasChanges: added.length > 0 || removed.length > 0 || metaChanged || portalChanged
  };
}

/**
 * Komponen Switch interaktif dengan dukungan keyboard, label aria, dan tema Gelap/Terang.
 */
function PermissionSwitch({ checked, disabled, onChange, ariaLabel }) {
  return (
    <button
      type="button"
      role="switch"
      aria-checked={checked}
      aria-label={ariaLabel}
      disabled={disabled}
      onClick={() => !disabled && onChange(!checked)}
      className={`relative inline-flex h-6 w-11 shrink-0 items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 ${
        disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'
      } ${checked ? 'bg-primary' : 'bg-slate-300 dark:bg-slate-700'}`}
    >
      <span
        className={`pointer-events-none inline-block size-5 transform rounded-full bg-white shadow-sm transition-transform ${
          checked ? 'translate-x-5' : 'translate-x-0'
        }`}
      />
    </button>
  );
}

/**
 * Halaman Master Manajemen Role & Hak Akses RBAC (DX_LMS_M_Role_Access).
 * Memungkinkan Admin mengatur izin granular per role (mis. Reviewer, Teacher, Student),
 * membuka atau menutup akses Portal Staff, menerapkan preset, membandingkan matriks antar role,
 * dan memeriksa riwayat audit keamanan.
 */
export default function RoleAccessPage({ onPermissionsUpdated }) {
  const currentUser = useOutletContext();
  const [snapshot, setSnapshot] = useState({
    catalog: [],
    categories: [],
    roles: [],
    presets: [],
    auditLogs: []
  });
  const [selectedRoleKey, setSelectedRoleKey] = useState('reviewer');
  const [drafts, setDrafts] = useState({});
  const [activeTab, setActiveTab] = useState('editor');
  const [search, setSearch] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('all');
  const [riskFilter, setRiskFilter] = useState('all');
  const [statusFilter, setStatusFilter] = useState('all');
  const [auditSearch, setAuditSearch] = useState('');
  const [saveNote, setSaveNote] = useState('');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');

  async function loadSnapshot({ keepDrafts = false } = {}) {
    setLoading(true);
    try {
      const data = await api('/roles');
      setSnapshot(data);
      if (!keepDrafts) {
        const nextDrafts = {};
        for (const role of data.roles || []) {
          nextDrafts[role.role_key] = {
            ...role,
            permissions: [...(role.permissions || [])]
          };
        }
        setDrafts(nextDrafts);
      }
      setError('');
    } catch (e) {
      const msg = e.message || 'Gagal memuat konfigurasi role.';
      setError(msg);
      showAlert.error('Gagal Memuat Hak Akses', msg);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    loadSnapshot();
  }, []);

  const savedByRole = useMemo(() => {
    const map = {};
    for (const r of snapshot.roles || []) map[r.role_key] = r;
    return map;
  }, [snapshot.roles]);

  const savedSelected = savedByRole[selectedRoleKey] || null;
  const draftSelected = drafts[selectedRoleKey] || savedSelected;

  const catalogByKey = useMemo(() => {
    const map = {};
    for (const item of snapshot.catalog || []) map[item.key] = item;
    return map;
  }, [snapshot.catalog]);

  const selectedDiff = useMemo(
    () => calculateRoleDiff(savedSelected, draftSelected, snapshot.catalog || []),
    [savedSelected, draftSelected, snapshot.catalog]
  );

  const modifiedRoleKeys = useMemo(
    () =>
      (snapshot.roles || [])
        .filter((role) => calculateRoleDiff(role, drafts[role.role_key], snapshot.catalog || []).hasChanges)
        .map((role) => role.role_key),
    [snapshot.roles, drafts, snapshot.catalog]
  );

  const filteredCatalog = useMemo(() => {
    const q = search.trim().toLowerCase();
    const grantedSet = new Set(draftSelected?.permissions || []);
    return (snapshot.catalog || []).filter((item) => {
      if (categoryFilter !== 'all' && item.category !== categoryFilter) return false;
      if (riskFilter !== 'all' && item.riskLevel !== riskFilter) return false;
      if (statusFilter === 'enabled' && !grantedSet.has(item.key)) return false;
      if (statusFilter === 'disabled' && grantedSet.has(item.key)) return false;
      if (
        q &&
        !`${item.key} ${item.label} ${item.description} ${item.categoryLabel}`
          .toLowerCase()
          .includes(q)
      ) {
        return false;
      }
      return true;
    });
  }, [snapshot.catalog, draftSelected, categoryFilter, riskFilter, statusFilter, search]);

  const groupedCatalog = useMemo(() => {
    const groups = new Map();
    for (const item of filteredCatalog) {
      if (!groups.has(item.category)) {
        groups.set(item.category, {
          key: item.category,
          label: item.categoryLabel,
          items: []
        });
      }
      groups.get(item.category).items.push(item);
    }
    return Array.from(groups.values());
  }, [filteredCatalog]);

  const filteredAuditLogs = useMemo(() => {
    const q = auditSearch.trim().toLowerCase();
    if (!q) return snapshot.auditLogs || [];
    return (snapshot.auditLogs || []).filter((log) =>
      `${log.role_key} ${log.changed_by} ${log.changed_by_name} ${log.summary}`
        .toLowerCase()
        .includes(q)
    );
  }, [snapshot.auditLogs, auditSearch]);

  function updateDraftRole(roleKey, updater) {
    setDrafts((prev) => {
      const current = prev[roleKey] || savedByRole[roleKey];
      if (!current) return prev;
      return {
        ...prev,
        [roleKey]: updater(current)
      };
    });
  }

  function toggleRolePermission(roleKey, permKey, enabled) {
    if (roleKey === 'admin' && permKey === 'roles.manage' && !enabled) {
      showAlert.info(
        'Izin Sistem Terproteksi',
        'Izin Kelola Matriks Role & Hak Akses wajib tetap aktif untuk Administrator agar mencegah lockout.'
      );
      return;
    }
    updateDraftRole(roleKey, (current) => {
      const set = new Set(current.permissions || []);
      if (enabled) set.add(permKey);
      else set.delete(permKey);
      return { ...current, permissions: Array.from(set) };
    });
  }

  function toggleModulePermissions(categoryKey, enableAll) {
    const itemsInCategory = (snapshot.catalog || []).filter((item) => item.category === categoryKey);
    updateDraftRole(selectedRoleKey, (current) => {
      const set = new Set(current.permissions || []);
      for (const item of itemsInCategory) {
        if (selectedRoleKey === 'admin' && item.key === 'roles.manage' && !enableAll) {
          continue;
        }
        if (enableAll) set.add(item.key);
        else set.delete(item.key);
      }
      return { ...current, permissions: Array.from(set) };
    });
  }

  function resetDraftRole(roleKey) {
    const original = savedByRole[roleKey];
    if (!original) return;
    setDrafts((prev) => ({
      ...prev,
      [roleKey]: {
        ...original,
        permissions: [...(original.permissions || [])]
      }
    }));
    if (roleKey === selectedRoleKey) setSaveNote('');
  }

  async function saveRole(roleKey) {
    const targetDraft = drafts[roleKey];
    if (!targetDraft) return;
    setSaving(true);
    setError('');
    setNotice('');
    try {
      const res = await api(`/roles/${encodeURIComponent(roleKey)}`, {
        method: 'PUT',
        body: JSON.stringify({
          displayName: targetDraft.display_name,
          description: targetDraft.description,
          canAccessStaffPortal: Boolean(targetDraft.can_access_staff_portal),
          permissions: targetDraft.permissions || [],
          note: roleKey === selectedRoleKey ? saveNote.trim() : 'Pembaruan melalui Matriks Perbandingan'
        })
      });
      await loadSnapshot({ keepDrafts: false });
      await onPermissionsUpdated?.();
      if (roleKey === selectedRoleKey) setSaveNote('');
      const msg =
        res.message ||
        `Hak akses untuk role ${targetDraft.display_name} berhasil diperbarui dan langsung aktif.`;
      setNotice(msg);
      showAlert.success('Hak Akses Tersimpan', msg);
    } catch (e) {
      setError(e.message || 'Gagal menyimpan hak akses role.');
      showAlert.error('Gagal Menyimpan Hak Akses', e.message || 'Terjadi kesalahan.');
    } finally {
      setSaving(false);
    }
  }

  async function applyPreset(preset) {
    const confirmed = await showAlert.confirm({
      title: `Terapkan Preset "${preset.label}"?`,
      text: `Konfigurasi role ${draftSelected?.display_name || selectedRoleKey} akan langsung diperbarui sesuai paket preset ini dan dicatat di log audit.`,
      confirmText: 'Ya, Terapkan Preset',
      cancelText: 'Batal',
      icon: 'question'
    });
    if (!confirmed) return;

    setSaving(true);
    setError('');
    setNotice('');
    try {
      const res = await api(`/roles/${encodeURIComponent(selectedRoleKey)}/preset`, {
        method: 'POST',
        body: JSON.stringify({ presetKey: preset.key })
      });
      await loadSnapshot({ keepDrafts: false });
      await onPermissionsUpdated?.();
      setSaveNote('');
      const msg = res.message || `Preset "${preset.label}" berhasil diterapkan.`;
      setNotice(msg);
      showAlert.success('Preset Diterapkan', msg);
    } catch (e) {
      setError(e.message || 'Gagal menerapkan preset role.');
      showAlert.error('Gagal Menerapkan Preset', e.message || 'Terjadi kesalahan.');
    } finally {
      setSaving(false);
    }
  }

  const staffPortalCount = useMemo(
    () => (snapshot.roles || []).filter((r) => r.can_access_staff_portal).length,
    [snapshot.roles]
  );

  const selectedPermsCount = draftSelected?.permissions?.length || 0;
  const totalCatalogCount = snapshot.catalog?.length || 0;
  const applicablePresets = useMemo(
    () =>
      (snapshot.presets || []).filter((preset) =>
        (preset.applicableRoles || []).includes(selectedRoleKey)
      ),
    [snapshot.presets, selectedRoleKey]
  );

  return (
    <div className="space-y-6 pb-10">
      <PageHeader
        eyebrow="Keamanan & Tata Kelola RBAC"
        title="Role & Hak Akses"
        description="Atur hak akses granular per role (Admin, Teacher, Reviewer, Student), kelola pembukaan akses Portal Staff, bandingkan matriks antar peran, dan audit perubahan secara real-time."
        actions={
          <div className="flex flex-wrap items-center gap-2">
            <StatusBadge status="admin" label={`Anda masuk: ${currentUser?.role || 'admin'}`} />
            <Button
              type="button"
              size="sm"
              variant="outline"
              disabled={loading || saving}
              icon={<RefreshCw className={`size-4 ${loading ? 'animate-spin' : ''}`} />}
              onClick={() => loadSnapshot()}
            >
              Muat Ulang
            </Button>
          </div>
        }
      />

      {/* KPI Ringkasan */}
      <section
        id="roles-overview"
        aria-label="Ringkasan tata kelola hak akses"
        className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
      >
        <StatCard
          label="Role Terkelola"
          value={(snapshot.roles || []).length}
          icon={<Shield className="size-5" />}
          tone="secondary"
          subtext="Admin, Teacher, Reviewer, Student"
          loading={loading}
        />
        <StatCard
          label="Izin Aktif Role Terpilih"
          value={`${selectedPermsCount}/${totalCatalogCount}`}
          icon={<KeyRound className="size-5" />}
          tone="primary"
          subtext={draftSelected?.display_name || selectedRoleKey}
          loading={loading}
        />
        <StatCard
          label="Akses Portal Staff"
          value={`${staffPortalCount} Role`}
          icon={<UserCheck className="size-5" />}
          tone="tertiary"
          subtext="Dapat login ke portal React ini"
          loading={loading}
        />
        <StatCard
          label="Rekam Audit Tersimpan"
          value={(snapshot.auditLogs || []).length}
          icon={<History className="size-5" />}
          tone="info"
          subtext="Riwayat perubahan hak akses"
          loading={loading}
        />
      </section>

      {error && (
        <div
          role="alert"
          className="flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300"
        >
          <AlertTriangle className="mt-0.5 size-4 shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {notice && (
        <div
          role="status"
          className="flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
          <CheckCircle2 className="mt-0.5 size-4 shrink-0" />
          <span>{notice}</span>
        </div>
      )}

      {/* Tab Navigasi Mode */}
      <div
        id="roles-editor-panel"
        className="dls-card-surface flex flex-wrap items-center justify-between gap-3 rounded-xl p-2"
      >
        <div className="flex flex-wrap gap-1.5" role="tablist" aria-label="Mode Pengelolaan Hak Akses">
          {[
            { id: 'editor', label: 'Editor Per-Role', Icon: KeyRound },
            { id: 'matrix', label: 'Matriks Perbandingan', Icon: Layers },
            { id: 'audit', label: `Riwayat Audit (${(snapshot.auditLogs || []).length})`, Icon: History }
          ].map(({ id, label, Icon }) => {
            const active = activeTab === id;
            return (
              <button
                key={id}
                type="button"
                role="tab"
                aria-selected={active}
                onClick={() => setActiveTab(id)}
                className={`inline-flex cursor-pointer items-center gap-2 rounded-lg px-3.5 py-2 text-xs font-semibold transition sm:text-sm ${
                  active
                    ? 'bg-primary text-white shadow-xs'
                    : 'text-on-surface-variant hover:bg-surface-low dark:text-slate-300 dark:hover:bg-slate-800'
                }`}
              >
                <Icon className="size-4" />
                <span>{label}</span>
              </button>
            );
          })}
        </div>

        {modifiedRoleKeys.length > 0 && (
          <span className="inline-flex items-center gap-1.5 rounded-full border border-amber-300 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 dark:border-amber-800/70 dark:bg-amber-950/50 dark:text-amber-300">
            <Sparkles className="size-3.5" />
            {modifiedRoleKeys.length} role memiliki perubahan belum disimpan
          </span>
        )}
      </div>

      {/* TAB 1: EDITOR PER-ROLE */}
      {activeTab === 'editor' && (
        <div className="grid gap-6 lg:grid-cols-12 lg:items-start">
          {/* Kolom Kiri: Pilihan Role (Sticky di bawah header saat scroll) */}
          <aside className="space-y-3 lg:sticky lg:top-20 lg:col-span-4 lg:max-h-[calc(100vh-6rem)] lg:overflow-y-auto lg:pr-1 xl:col-span-3">
            <h2 className="px-1 text-xs font-bold uppercase tracking-[0.12em] text-muted">
              Pilih Role Sistem
            </h2>

            {(snapshot.roles || []).map((role) => {
              const draft = drafts[role.role_key] || role;
              const visual = ROLE_VISUALS[role.role_key] || ROLE_VISUALS.student;
              const RoleIcon = visual.Icon;
              const isSelected = role.role_key === selectedRoleKey;
              const dirty = calculateRoleDiff(role, draft, snapshot.catalog || []).hasChanges;
              const ratio = totalCatalogCount
                ? Math.round(((draft.permissions?.length || 0) / totalCatalogCount) * 100)
                : 0;

              return (
                <button
                  key={role.role_key}
                  type="button"
                  onClick={() => {
                    setSelectedRoleKey(role.role_key);
                    setNotice('');
                  }}
                  className={`dls-card-surface group w-full cursor-pointer rounded-xl p-4 text-left transition ${
                    isSelected
                      ? `${visual.ring} bg-surface-lowest shadow-sm dark:bg-[#171f2c]`
                      : 'hover:-translate-y-0.5 hover:border-outline dark:hover:border-slate-600'
                  }`}
                >
                  <div className="flex items-start justify-between gap-2">
                    <div className="flex min-w-0 items-center gap-3">
                      <span className={`grid size-10 shrink-0 place-items-center rounded-lg ${visual.accent}`}>
                        <RoleIcon className="size-5" />
                      </span>
                      <div className="min-w-0">
                        <p className="truncate text-sm font-bold text-on-surface">
                          {draft.display_name}
                        </p>
                        <p className="font-mono text-[11px] uppercase text-muted">{role.role_key}</p>
                      </div>
                    </div>

                    {dirty && (
                      <span
                        className="size-2.5 shrink-0 rounded-full bg-amber-500 ring-4 ring-amber-500/20"
                        title="Ada perubahan belum disimpan"
                      />
                    )}
                  </div>

                  <p className="mt-2.5 line-clamp-2 text-xs leading-relaxed text-muted">
                    {draft.description}
                  </p>

                  <div className="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-outline/40 pt-2.5 text-[11px] dark:border-slate-800">
                    <span className="font-semibold text-on-surface-variant">
                      {draft.permissions?.length || 0} / {totalCatalogCount} izin ({ratio}%)
                    </span>
                    <span
                      className={`rounded-full px-2 py-0.5 font-semibold ${
                        draft.can_access_staff_portal
                          ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                          : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                      }`}
                    >
                      {draft.can_access_staff_portal ? 'Portal Staff Aktif' : 'Non-Portal Staff'}
                    </span>
                  </div>
                </button>
              );
            })}
          </aside>

          {/* Kolom Kanan: Detail Profil, Preset & Matriks Izin */}
          <div className="min-w-0 space-y-5 lg:col-span-8 xl:col-span-9">
            {draftSelected ? (
              <>
                {/* Kartu Profil Role & Akses Portal Staff */}
                <Card
                  title={`Konfigurasi Role: ${draftSelected.display_name}`}
                  subtitle="Ubah label tampilan, catatan peran, dan kebijakan apakah pengguna dengan role ini boleh masuk ke Portal Staff React."
                  action={<StatusBadge status={selectedRoleKey} label={selectedRoleKey.toUpperCase()} />}
                >
                  <div className="grid gap-4 md:grid-cols-2">
                    <label className="text-xs font-semibold text-on-surface-variant">
                      Nama Tampilan Role
                      <input
                        type="text"
                        maxLength={100}
                        value={draftSelected.display_name || ''}
                        onChange={(e) =>
                          updateDraftRole(selectedRoleKey, (cur) => ({
                            ...cur,
                            display_name: e.target.value
                          }))
                        }
                        className={`${INPUT_CLASS} mt-1.5`}
                      />
                    </label>

                    <div className="flex flex-col justify-between rounded-xl border border-outline/50 bg-surface-low/60 p-3.5 dark:border-slate-700/70 dark:bg-slate-800/50">
                      <div className="flex items-start justify-between gap-3">
                        <div>
                          <p className="text-xs font-bold text-on-surface">
                            Akses Login Portal Staff (React Admin/Teacher)
                          </p>
                          <p className="mt-1 text-[11px] leading-relaxed text-muted">
                            {selectedRoleKey === 'admin'
                              ? 'Terkunci aktif demi mencegah Administrator terkunci dari sistem.'
                              : selectedRoleKey === 'student'
                                ? 'Student diarahkan ke Portal Native PHP; ubah hanya bila diperlukan khusus.'
                                : 'Aktifkan agar akun ber-role ini (mis. Reviewer) dapat masuk ke dashboard & menu yang diizinkan.'}
                          </p>
                        </div>
                        <PermissionSwitch
                          checked={Boolean(draftSelected.can_access_staff_portal)}
                          disabled={selectedRoleKey === 'admin' || selectedRoleKey === 'student' || saving}
                          ariaLabel="Akses Login Portal Staff"
                          onChange={(nextVal) =>
                            updateDraftRole(selectedRoleKey, (cur) => ({
                              ...cur,
                              can_access_staff_portal: nextVal
                            }))
                          }
                        />
                      </div>
                    </div>

                    <label className="text-xs font-semibold text-on-surface-variant md:col-span-2">
                      Deskripsi Peran
                      <textarea
                        rows={2}
                        maxLength={500}
                        value={draftSelected.description || ''}
                        onChange={(e) =>
                          updateDraftRole(selectedRoleKey, (cur) => ({
                            ...cur,
                            description: e.target.value
                          }))
                        }
                        className="mt-1.5 w-full rounded-lg border border-outline bg-surface-lowest p-3 text-sm text-on-surface outline-none transition placeholder:text-muted/70 focus:border-primary focus:ring-2 focus:ring-primary/15 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                      />
                    </label>
                  </div>

                  {/* Preset Cepat */}
                  {applicablePresets.length > 0 && (
                    <div className="mt-4 border-t border-outline/40 pt-4 dark:border-slate-800">
                      <p className="mb-2.5 flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-muted">
                        <Sparkles className="size-3.5 text-primary" />
                        Paket Preset Cepat Untuk {draftSelected.display_name}
                      </p>
                      <div className="grid gap-2.5 sm:grid-cols-2">
                        {applicablePresets.map((preset) => (
                          <div
                            key={preset.key}
                            className="flex items-start justify-between gap-3 rounded-xl border border-outline/50 bg-surface-low/50 p-3 dark:border-slate-700/70 dark:bg-slate-800/40"
                          >
                            <div className="min-w-0">
                              <p className="text-xs font-bold text-on-surface">{preset.label}</p>
                              <p className="mt-0.5 text-[11px] leading-relaxed text-muted">
                                {preset.description}
                              </p>
                            </div>
                            <Button
                              type="button"
                              size="xs"
                              variant="outline"
                              disabled={saving}
                              onClick={() => applyPreset(preset)}
                              className="shrink-0"
                            >
                              Terapkan
                            </Button>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                </Card>

                {/* Filter & Pencarian Matriks Izin */}
                <Card
                  title="Matriks Izin Granular Per Modul"
                  subtitle="Centang fitur dan aksi API yang boleh dilakukan oleh role terpilih."
                >
                  <div className="grid gap-3 border-b border-outline/40 pb-4 sm:grid-cols-2 xl:grid-cols-4 dark:border-slate-800">
                    <div className="relative">
                      <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
                      <input
                        type="search"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Cari izin / kunci API..."
                        className={`${INPUT_CLASS} pl-9`}
                      />
                    </div>

                    <select
                      aria-label="Filter modul izin"
                      value={categoryFilter}
                      onChange={(e) => setCategoryFilter(e.target.value)}
                      className={INPUT_CLASS}
                    >
                      <option value="all">Semua Modul ({(snapshot.categories || []).length})</option>
                      {(snapshot.categories || []).map((cat) => (
                        <option key={cat.key} value={cat.key}>
                          {cat.label}
                        </option>
                      ))}
                    </select>

                    <select
                      aria-label="Filter tingkat risiko"
                      value={riskFilter}
                      onChange={(e) => setRiskFilter(e.target.value)}
                      className={INPUT_CLASS}
                    >
                      <option value="all">Semua Tingkat Risiko</option>
                      <option value="low">Risiko Rendah</option>
                      <option value="medium">Risiko Menengah</option>
                      <option value="high">Risiko Tinggi</option>
                      <option value="critical">Risiko Kritis</option>
                    </select>

                    <select
                      aria-label="Filter status izin"
                      value={statusFilter}
                      onChange={(e) => setStatusFilter(e.target.value)}
                      className={INPUT_CLASS}
                    >
                      <option value="all">Semua Status (Aktif &amp; Nonaktif)</option>
                      <option value="enabled">Hanya Yang Aktif</option>
                      <option value="disabled">Hanya Yang Nonaktif</option>
                    </select>
                  </div>

                  {/* Daftar Modul & Kartu Toggle Izin */}
                  <div className="mt-5 space-y-6">
                    {groupedCatalog.length === 0 ? (
                      <div className="rounded-xl border border-dashed border-outline/60 py-10 text-center text-sm text-muted dark:border-slate-700">
                        Tidak ada izin yang cocok dengan filter pencarian saat ini.
                      </div>
                    ) : (
                      groupedCatalog.map((group) => {
                        const totalInMod = (snapshot.catalog || []).filter(
                          (item) => item.category === group.key
                        ).length;
                        const enabledSet = new Set(draftSelected.permissions || []);
                        const enabledInMod = (snapshot.catalog || []).filter(
                          (item) => item.category === group.key && enabledSet.has(item.key)
                        ).length;

                        return (
                          <div
                            key={group.key}
                            className="overflow-hidden rounded-xl border border-outline/50 bg-surface-low/30 dark:border-slate-700/70 dark:bg-slate-900/40"
                          >
                            <div className="flex flex-wrap items-center justify-between gap-2 border-b border-outline/40 bg-surface-low/80 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/70">
                              <div className="flex items-center gap-2.5">
                                <ShieldCheck className="size-4 text-primary" />
                                <h3 className="text-sm font-bold text-on-surface">{group.label}</h3>
                                <span className="rounded-full bg-surface-lowest px-2 py-0.5 text-[11px] font-semibold text-on-surface-variant dark:bg-slate-900 dark:text-slate-300">
                                  {enabledInMod} / {totalInMod} aktif
                                </span>
                              </div>

                              <div className="flex items-center gap-1.5">
                                <button
                                  type="button"
                                  disabled={saving}
                                  onClick={() => toggleModulePermissions(group.key, true)}
                                  className="cursor-pointer rounded-md border border-outline/60 bg-surface-lowest px-2.5 py-1 text-[11px] font-semibold text-on-surface-variant transition hover:border-primary/40 hover:text-primary dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                                >
                                  Aktifkan Modul
                                </button>
                                <button
                                  type="button"
                                  disabled={saving}
                                  onClick={() => toggleModulePermissions(group.key, false)}
                                  className="cursor-pointer rounded-md border border-outline/60 bg-surface-lowest px-2.5 py-1 text-[11px] font-semibold text-muted transition hover:border-red-300 hover:text-red-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400"
                                >
                                  Nonaktifkan Modul
                                </button>
                              </div>
                            </div>

                            <div className="grid gap-3 p-4 md:grid-cols-2">
                              {group.items.map((perm) => {
                                const isChecked = enabledSet.has(perm.key);
                                const isProtected =
                                  selectedRoleKey === 'admin' && Boolean(perm.systemProtectedForAdmin);
                                const risk = RISK_META[perm.riskLevel] || RISK_META.low;

                                return (
                                  <div
                                    key={perm.key}
                                    className={`flex items-start justify-between gap-3 rounded-xl border p-3.5 transition ${
                                      isChecked
                                        ? 'border-primary/35 bg-surface-lowest shadow-2xs dark:border-primary/40 dark:bg-[#171f2c]'
                                        : 'border-outline/50 bg-surface-lowest/60 opacity-85 dark:border-slate-800 dark:bg-slate-900/60'
                                    }`}
                                  >
                                    <div className="min-w-0 flex-1">
                                      <div className="flex flex-wrap items-center gap-1.5">
                                        <p className="text-xs font-bold text-on-surface sm:text-[13px]">
                                          {perm.label}
                                        </p>
                                        <span
                                          className={`inline-flex items-center rounded-full border px-1.5 py-0.5 text-[10px] font-bold uppercase ${risk.className}`}
                                        >
                                          {risk.label}
                                        </span>
                                        {isProtected && (
                                          <span
                                            className="inline-flex items-center gap-1 rounded-full border border-slate-300 bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                            title="Wajib aktif pada Administrator"
                                          >
                                            <Lock className="size-2.5" />
                                            Terkunci
                                          </span>
                                        )}
                                      </div>

                                      <p className="mt-1 text-xs leading-relaxed text-muted">
                                        {perm.description}
                                      </p>
                                      <p className="mt-1.5 font-mono text-[10.5px] text-muted/85">
                                        {perm.key}
                                      </p>
                                    </div>

                                    <PermissionSwitch
                                      checked={isChecked}
                                      disabled={isProtected || saving}
                                      ariaLabel={`${perm.label} untuk ${draftSelected.display_name}`}
                                      onChange={(nextVal) =>
                                        toggleRolePermission(selectedRoleKey, perm.key, nextVal)
                                      }
                                    />
                                  </div>
                                );
                              })}
                            </div>
                          </div>
                        );
                      })
                    )}
                  </div>

                  {/* Ringkasan Diff & Tombol Simpan */}
                  <div className="mt-6 rounded-xl border border-outline/60 bg-surface-low/80 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                      <div className="min-w-0 flex-1 space-y-2">
                        <div className="flex flex-wrap items-center gap-2">
                          <span className="text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            Ringkasan Perubahan Draft:
                          </span>
                          {!selectedDiff.hasChanges ? (
                            <span className="text-xs text-muted">
                              Belum ada perubahan dari konfigurasi tersimpan.
                            </span>
                          ) : (
                            <div className="flex flex-wrap gap-1.5">
                              {selectedDiff.added.map((k) => (
                                <span
                                  key={`add-${k}`}
                                  className="rounded-md bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300"
                                >
                                  + {catalogByKey[k]?.label || k}
                                </span>
                              ))}
                              {selectedDiff.removed.map((k) => (
                                <span
                                  key={`rem-${k}`}
                                  className="rounded-md bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-800 dark:bg-red-950/70 dark:text-red-300"
                                >
                                  - {catalogByKey[k]?.label || k}
                                </span>
                              ))}
                              {selectedDiff.portalChanged && (
                                <span className="rounded-md bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-800 dark:bg-blue-950/70 dark:text-blue-300">
                                  Portal Staff: {draftSelected.can_access_staff_portal ? 'Dibuka' : 'Ditutup'}
                                </span>
                              )}
                              {selectedDiff.metaChanged && (
                                <span className="rounded-md bg-slate-200 px-2 py-0.5 text-[11px] font-semibold text-slate-800 dark:bg-slate-700 dark:text-slate-200">
                                  Profil Role Diubah
                                </span>
                              )}
                            </div>
                          )}
                        </div>

                        <input
                          type="text"
                          maxLength={250}
                          value={saveNote}
                          onChange={(e) => setSaveNote(e.target.value)}
                          placeholder="Catatan audit opsional (mis: Izinkan Reviewer menyetujui materi di Portal Staff)..."
                          className={INPUT_CLASS}
                        />
                      </div>

                      <div className="flex flex-wrap items-center gap-2">
                        <Button
                          type="button"
                          variant="outline"
                          disabled={!selectedDiff.hasChanges || saving}
                          icon={<RotateCcw className="size-4" />}
                          onClick={() => resetDraftRole(selectedRoleKey)}
                        >
                          Batalkan Draft
                        </Button>
                        <Button
                          type="button"
                          variant="primary"
                          loading={saving}
                          disabled={!selectedDiff.hasChanges || saving}
                          icon={<Save className="size-4" />}
                          onClick={() => saveRole(selectedRoleKey)}
                        >
                          Simpan Hak Akses ({draftSelected.display_name})
                        </Button>
                      </div>
                    </div>
                  </div>
                </Card>
              </>
            ) : null}
          </div>
        </div>
      )}

      {/* TAB 2: MATRIKS PERBANDINGAN LINTAS ROLE */}
      {activeTab === 'matrix' && (
        <Card
          title="Matriks Perbandingan Hak Akses Lintas Role"
          subtitle="Bandingkan dan ubah izin antar role secara berdampingan pada satu tabel terpadu."
          action={
            modifiedRoleKeys.length > 0 && (
              <div className="flex flex-wrap gap-2">
                {modifiedRoleKeys.map((rKey) => (
                  <Button
                    key={`save-${rKey}`}
                    type="button"
                    size="xs"
                    variant="primary"
                    loading={saving}
                    icon={<Save className="size-3.5" />}
                    onClick={() => saveRole(rKey)}
                  >
                    Simpan {drafts[rKey]?.display_name || rKey}
                  </Button>
                ))}
              </div>
            )
          }
        >
          <div className="mb-4 grid gap-3 sm:grid-cols-2">
            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
              <input
                type="search"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Cari nama izin atau kunci modul..."
                className={`${INPUT_CLASS} pl-9`}
              />
            </div>

            <select
              aria-label="Filter kategori matriks"
              value={categoryFilter}
              onChange={(e) => setCategoryFilter(e.target.value)}
              className={INPUT_CLASS}
            >
              <option value="all">Semua Kategori Modul</option>
              {(snapshot.categories || []).map((cat) => (
                <option key={cat.key} value={cat.key}>
                  {cat.label}
                </option>
              ))}
            </select>
          </div>

          <div className="overflow-x-auto rounded-xl border border-outline/50 dark:border-slate-700/70">
            <table className="w-full min-w-[760px] border-collapse text-left text-xs sm:text-sm">
              <thead>
                <tr className="border-b border-outline/50 bg-surface-low/80 text-[11px] font-bold uppercase tracking-wider text-muted dark:border-slate-700 dark:bg-slate-800/80">
                  <th className="px-4 py-3">Modul &amp; Izin</th>
                  {(snapshot.roles || []).map((role) => (
                    <th key={role.role_key} className="px-4 py-3 text-center">
                      <span className="block text-on-surface">{role.display_name}</span>
                      <span className="font-mono text-[10px] font-normal text-muted">
                        ({(drafts[role.role_key]?.permissions || []).length}/{totalCatalogCount})
                      </span>
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody className="divide-y divide-outline/40 dark:divide-slate-800">
                {(snapshot.catalog || [])
                  .filter((item) => {
                    if (categoryFilter !== 'all' && item.category !== categoryFilter) return false;
                    const q = search.trim().toLowerCase();
                    if (
                      q &&
                      !`${item.key} ${item.label} ${item.categoryLabel}`.toLowerCase().includes(q)
                    ) {
                      return false;
                    }
                    return true;
                  })
                  .map((perm) => (
                    <tr
                      key={perm.key}
                      className="transition hover:bg-surface-low/50 dark:hover:bg-slate-800/40"
                    >
                      <td className="px-4 py-3">
                        <p className="font-semibold text-on-surface">{perm.label}</p>
                        <p className="mt-0.5 text-[11px] text-muted">
                          {perm.categoryLabel} · <span className="font-mono">{perm.key}</span>
                        </p>
                      </td>
                      {(snapshot.roles || []).map((role) => {
                        const checked = (drafts[role.role_key]?.permissions || []).includes(perm.key);
                        const locked =
                          role.role_key === 'admin' && Boolean(perm.systemProtectedForAdmin);
                        return (
                          <td key={`${perm.key}-${role.role_key}`} className="px-4 py-3 text-center">
                            <button
                              type="button"
                              disabled={locked || saving}
                              onClick={() => toggleRolePermission(role.role_key, perm.key, !checked)}
                              title={
                                locked
                                  ? 'Terkunci untuk Administrator'
                                  : `Klik untuk ${checked ? 'mencabut' : 'memberikan'} izin ${perm.label} pada ${role.display_name}`
                              }
                              className={`inline-flex size-8 items-center justify-center rounded-lg border transition ${
                                locked
                                  ? 'cursor-not-allowed border-emerald-300 bg-emerald-50 text-emerald-700 opacity-75 dark:border-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300'
                                  : checked
                                    ? 'cursor-pointer border-emerald-400 bg-emerald-500 text-white shadow-2xs hover:bg-emerald-600'
                                    : 'cursor-pointer border-outline/60 bg-surface-lowest text-muted hover:border-primary/40 hover:text-primary dark:border-slate-700 dark:bg-slate-900'
                              }`}
                            >
                              {locked ? (
                                <Lock className="size-3.5" />
                              ) : checked ? (
                                <Check className="size-4 stroke-[2.5]" />
                              ) : (
                                <span className="size-1.5 rounded-full bg-slate-300 dark:bg-slate-600" />
                              )}
                            </button>
                          </td>
                        );
                      })}
                    </tr>
                  ))}
              </tbody>
            </table>
          </div>
        </Card>
      )}

      {/* TAB 3: RIWAYAT AUDIT PERUBAHAN HAK AKSES */}
      {activeTab === 'audit' && (
        <Card
          title="Riwayat Audit Perubahan Hak Akses"
          subtitle="Setiap perubahan izin, status Portal Staff, maupun penerapan preset tercatat lengkap untuk kepatuhan audit."
        >
          <div className="mb-4">
            <div className="relative max-w-md">
              <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
              <input
                type="search"
                value={auditSearch}
                onChange={(e) => setAuditSearch(e.target.value)}
                placeholder="Cari role, NPK/nama Admin, atau ringkasan perubahan..."
                className={`${INPUT_CLASS} pl-9`}
              />
            </div>
          </div>

          {filteredAuditLogs.length === 0 ? (
            <div className="rounded-xl border border-dashed border-outline/60 py-10 text-center text-sm text-muted dark:border-slate-700">
              Belum ada riwayat perubahan hak akses yang tercatat.
            </div>
          ) : (
            <div className="space-y-3">
              {filteredAuditLogs.map((log) => {
                const added = log.changes?.added || [];
                const removed = log.changes?.removed || [];
                return (
                  <article
                    key={log.audit_id}
                    className="rounded-xl border border-outline/50 bg-surface-low/40 p-4 dark:border-slate-700/70 dark:bg-slate-900/50"
                  >
                    <div className="flex flex-wrap items-start justify-between gap-2">
                      <div className="flex flex-wrap items-center gap-2">
                        <StatusBadge status={log.role_key} label={log.role_key.toUpperCase()} />
                        <p className="text-sm font-bold text-on-surface">{log.summary}</p>
                      </div>
                      <time className="text-xs text-muted">
                        {log.created_at
                          ? new Date(log.created_at).toLocaleString('id-ID', {
                              dateStyle: 'medium',
                              timeStyle: 'short'
                            })
                          : '—'}
                      </time>
                    </div>

                    <p className="mt-1.5 text-xs text-muted">
                      Diubah oleh:{' '}
                      <strong className="font-semibold text-on-surface-variant">
                        {log.changed_by_name}
                      </strong>{' '}
                      <span className="font-mono">({log.changed_by})</span>
                    </p>

                    {(added.length > 0 || removed.length > 0) && (
                      <div className="mt-2.5 flex flex-wrap gap-1.5">
                        {added.map((k) => (
                          <span
                            key={`log-add-${log.audit_id}-${k}`}
                            className="rounded-md bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300"
                          >
                            + {catalogByKey[k]?.label || k}
                          </span>
                        ))}
                        {removed.map((k) => (
                          <span
                            key={`log-rem-${log.audit_id}-${k}`}
                            className="rounded-md bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-800 dark:bg-red-950/60 dark:text-red-300"
                          >
                            - {catalogByKey[k]?.label || k}
                          </span>
                        ))}
                      </div>
                    )}
                  </article>
                );
              })}
            </div>
          )}
        </Card>
      )}
    </div>
  );
}

