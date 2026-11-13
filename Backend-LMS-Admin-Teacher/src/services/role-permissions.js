const { sql, pool } = require('../config/database');

const CACHE_TTL_MS = 30 * 1000;

/**
 * Katalog seluruh izin (permission) modular yang dapat dikelola oleh Admin pada sistem DENSO LMS.
 */
const PERMISSION_CATALOG = [
  {
    key: 'dashboard.view',
    category: 'dashboard',
    categoryLabel: 'Dashboard & Analitik',
    label: 'Lihat Dashboard & Statistik',
    description: 'Membaca ringkasan KPI pelatihan, grafik analitik, dan jumlah course aktif.',
    riskLevel: 'low'
  },
  {
    key: 'activity.view',
    category: 'dashboard',
    categoryLabel: 'Dashboard & Analitik',
    label: 'Lihat Log Aktivitas & Antrean Job',
    description: 'Memantau aktivitas harian pengguna dan progres antrean latar belakang.',
    riskLevel: 'low'
  },
  {
    key: 'courses.view',
    category: 'courses',
    categoryLabel: 'Course & Sesi',
    label: 'Lihat Katalog & Detail Course',
    description: 'Membuka daftar course, detail struktur sesi, dan pratinjau sampul.',
    riskLevel: 'low'
  },
  {
    key: 'courses.create',
    category: 'courses',
    categoryLabel: 'Course & Sesi',
    label: 'Buat Course Baru',
    description: 'Menambahkan entri course baru ke katalog pembelajaran LMS.',
    riskLevel: 'medium'
  },
  {
    key: 'courses.manage_cover',
    category: 'courses',
    categoryLabel: 'Course & Sesi',
    label: 'Kelola Foto Sampul Course',
    description: 'Mengunggah, mengganti, atau menghapus gambar sampul course.',
    riskLevel: 'low'
  },
  {
    key: 'courses.assign_trainer',
    category: 'courses',
    categoryLabel: 'Course & Sesi',
    label: 'Tugaskan Trainer Course',
    description: 'Menunjuk atau mengganti pengajar penanggung jawab course.',
    riskLevel: 'high'
  },
  {
    key: 'courses.manage_sessions',
    category: 'courses',
    categoryLabel: 'Course & Sesi',
    label: 'Kelola Sesi Pertemuan',
    description: 'Menambah dan mengatur urutan sesi pertemuan pada detail course.',
    riskLevel: 'medium'
  },
  {
    key: 'materials.view',
    category: 'materials',
    categoryLabel: 'Materi & Alur Review',
    label: 'Lihat Pengajuan & Materi Terbit',
    description: 'Melihat daftar pengajuan materi dan katalog materi final yang sudah terbit.',
    riskLevel: 'low'
  },
  {
    key: 'materials.submit',
    category: 'materials',
    categoryLabel: 'Materi & Alur Review',
    label: 'Ajukan & Edit Materi',
    description: 'Mengirim materi baru serta memperbarui pengajuan yang masih dapat diedit.',
    riskLevel: 'medium'
  },
  {
    key: 'materials.send_to_reviewer',
    category: 'materials',
    categoryLabel: 'Materi & Alur Review',
    label: 'Kirim ke Reviewer / Tolak Pra-Review',
    description: 'Meneruskan pengajuan ke Reviewer atau mengembalikan materi pada tahap admin.',
    riskLevel: 'high'
  },
  {
    key: 'materials.review',
    category: 'materials',
    categoryLabel: 'Materi & Alur Review',
    label: 'Review & Putuskan Materi',
    description: 'Meninjau berkas pengajuan materi dan memberikan keputusan Disetujui / Perlu Revisi.',
    riskLevel: 'high'
  },
  {
    key: 'materials.publish',
    category: 'materials',
    categoryLabel: 'Materi & Alur Review',
    label: 'Terbitkan Materi ke Student',
    description: 'Mempublikasikan materi yang disetujui ke portal pembelajaran Student.',
    riskLevel: 'high'
  },
  {
    key: 'materials.delete',
    category: 'materials',
    categoryLabel: 'Materi & Alur Review',
    label: 'Hapus Pengajuan / Materi Terbit',
    description: 'Menarik dan menghapus pengajuan atau materi terbit dari sistem.',
    riskLevel: 'high'
  },
  {
    key: 'quizzes.view',
    category: 'quizzes_grades',
    categoryLabel: 'Quiz & Penilaian',
    label: 'Lihat Daftar Quiz & Soal',
    description: 'Membaca daftar quiz dan konfigurasi soal per course.',
    riskLevel: 'low'
  },
  {
    key: 'quizzes.manage',
    category: 'quizzes_grades',
    categoryLabel: 'Quiz & Penilaian',
    label: 'Buat, Edit & Terbitkan Quiz',
    description: 'Mengelola soal pilihan ganda, skor kelulusan, dan status publikasi quiz.',
    riskLevel: 'medium'
  },
  {
    key: 'grades.view',
    category: 'quizzes_grades',
    categoryLabel: 'Quiz & Penilaian',
    label: 'Lihat Rekap Nilai Peserta',
    description: 'Mengakses daftar capaian nilai course dan nilai quiz peserta.',
    riskLevel: 'low'
  },
  {
    key: 'grades.edit',
    category: 'quizzes_grades',
    categoryLabel: 'Quiz & Penilaian',
    label: 'Input & Ubah Nilai Peserta',
    description: 'Memasukkan atau memperbarui nilai course serta umpan balik untuk Student.',
    riskLevel: 'high'
  },
  {
    key: 'users.view',
    category: 'users_enrollments',
    categoryLabel: 'Pengguna & Enrollment',
    label: 'Lihat Daftar Pengguna',
    description: 'Membaca direktori akun pengguna Student maupun staf LMS.',
    riskLevel: 'low'
  },
  {
    key: 'users.manage',
    category: 'users_enrollments',
    categoryLabel: 'Pengguna & Enrollment',
    label: 'Kelola Akun Pengguna',
    description: 'Membuat akun baru, mengubah status/role, mengirim kredensial, atau menghapus pengguna.',
    riskLevel: 'critical'
  },
  {
    key: 'enrollments.view',
    category: 'users_enrollments',
    categoryLabel: 'Pengguna & Enrollment',
    label: 'Lihat Permintaan Akses Course',
    description: 'Melihat antrean pengajuan akses course Student dan riwayat keputusannya.',
    riskLevel: 'low'
  },
  {
    key: 'enrollments.recommend',
    category: 'users_enrollments',
    categoryLabel: 'Pengguna & Enrollment',
    label: 'Rekomendasikan Akses Student',
    description: 'Mengirimkan rekomendasi agar Student tertentu mendapat akses ke suatu course.',
    riskLevel: 'medium'
  },
  {
    key: 'enrollments.decide',
    category: 'users_enrollments',
    categoryLabel: 'Pengguna & Enrollment',
    label: 'Setujui / Tolak Akses Course',
    description: 'Memutuskan persetujuan enrollment Student ke course secara individual maupun massal.',
    riskLevel: 'high'
  },
  {
    key: 'reviewers.view',
    category: 'reviewers',
    categoryLabel: 'Manajemen Reviewer',
    label: 'Lihat Daftar Reviewer',
    description: 'Melihat daftar reviewer beserta beban review aktifnya.',
    riskLevel: 'low'
  },
  {
    key: 'reviewers.manage',
    category: 'reviewers',
    categoryLabel: 'Manajemen Reviewer',
    label: 'Kelola Akun Reviewer',
    description: 'Mendaftarkan reviewer baru, memperbarui profil, atau menonaktifkan reviewer.',
    riskLevel: 'high'
  },
  {
    key: 'forum.view',
    category: 'communication',
    categoryLabel: 'Forum & Notifikasi',
    label: 'Lihat Diskusi Forum',
    description: 'Membaca thread diskusi dan komentar pada forum sesi course.',
    riskLevel: 'low'
  },
  {
    key: 'forum.participate',
    category: 'communication',
    categoryLabel: 'Forum & Notifikasi',
    label: 'Buat Thread, Balas & Suka di Forum',
    description: 'Berpartisipasi aktif membuat thread baru, mengirim balasan, dan menyukai komentar.',
    riskLevel: 'low'
  },
  {
    key: 'forum.moderate',
    category: 'communication',
    categoryLabel: 'Forum & Notifikasi',
    label: 'Moderasi Sesi & Thread Forum',
    description: 'Mengunci, menyembunyikan, menyematkan, atau membuka kunci sesi diskusi forum.',
    riskLevel: 'high'
  },
  {
    key: 'notifications.view',
    category: 'communication',
    categoryLabel: 'Forum & Notifikasi',
    label: 'Lihat & Tandai Notifikasi Dibaca',
    description: 'Melihat pusat notifikasi, notifikasi belum dibaca, serta statistik baca.',
    riskLevel: 'low'
  },
  {
    key: 'notifications.send',
    category: 'communication',
    categoryLabel: 'Forum & Notifikasi',
    label: 'Kirim Notifikasi & Broadcast',
    description: 'Mengirim pengumuman penting, popup, dan notifikasi email kepada peran target.',
    riskLevel: 'medium'
  },
  {
    key: 'settings.api',
    category: 'system',
    categoryLabel: 'Sistem & Hak Akses',
    label: 'Konfigurasi Endpoint API Terpusat',
    description: 'Mengubah URL Backend API yang dibaca oleh Portal React dan Portal Native Student.',
    riskLevel: 'critical'
  },
  {
    key: 'roles.manage',
    category: 'system',
    categoryLabel: 'Sistem & Hak Akses',
    label: 'Kelola Matriks Role & Hak Akses',
    description: 'Mengatur izin granular setiap role serta melihat riwayat audit keamanannya.',
    riskLevel: 'critical',
    systemProtectedForAdmin: true
  }
];

const PERMISSION_KEYS = PERMISSION_CATALOG.map(item => item.key);
const PERMISSION_KEY_SET = new Set(PERMISSION_KEYS);

const ROLE_KEYS = ['admin', 'teacher', 'reviewer', 'student'];

/**
 * Definisi metadata default setiap role sistem.
 */
const DEFAULT_ROLE_DEFINITIONS = {
  admin: {
    role_key: 'admin',
    display_name: 'Administrator',
    description: 'Pengendali penuh platform LMS, persetujuan materi, manajemen akun, pengaturan API, dan hak akses role.',
    portal_target: 'staff',
    can_access_staff_portal: true
  },
  teacher: {
    role_key: 'teacher',
    display_name: 'Teacher / Pengajar',
    description: 'Mengelola course yang ditugaskan, menyusun sesi, mengajukan materi, membuat quiz, menilai peserta, dan moderasi forum.',
    portal_target: 'staff',
    can_access_staff_portal: true
  },
  reviewer: {
    role_key: 'reviewer',
    display_name: 'Reviewer Materi',
    description: 'Peninjau independen yang memeriksa kelayakan materi pembelajaran. Admin dapat membuka akses Portal Staff tambahan sesuai kebutuhan.',
    portal_target: 'reviewer',
    can_access_staff_portal: false
  },
  student: {
    role_key: 'student',
    display_name: 'Student / Peserta',
    description: 'Peserta pelatihan yang mengakses portal belajar native Student, mengerjakan materi/quiz, dan berdiskusi di forum.',
    portal_target: 'student',
    can_access_staff_portal: false
  }
};

/**
 * Daftar izin bawaan untuk tiap role sebelum dikustomisasi oleh Admin.
 */
const DEFAULT_ROLE_PERMISSIONS = {
  admin: [...PERMISSION_KEYS],
  teacher: [
    'dashboard.view',
    'activity.view',
    'courses.view',
    'courses.create',
    'courses.manage_cover',
    'courses.manage_sessions',
    'materials.view',
    'materials.submit',
    'materials.delete',
    'quizzes.view',
    'quizzes.manage',
    'grades.view',
    'grades.edit',
    'users.view',
    'users.manage',
    'enrollments.view',
    'enrollments.recommend',
    'forum.view',
    'forum.participate',
    'forum.moderate',
    'notifications.view',
    'notifications.send'
  ],
  reviewer: [
    'materials.review'
  ],
  student: [
    'forum.view',
    'forum.participate',
    'notifications.view'
  ]
};

/**
 * Template preset cepat agar Admin dapat menerapkan profil hak akses siap pakai dengan satu klik.
 */
const ROLE_PRESETS = {
  default: {
    key: 'default',
    label: 'Standar Bawaan Sistem',
    description: 'Mengembalikan konfigurasi role ke setelan standar bawaan DENSO LMS.',
    applicableRoles: ['admin', 'teacher', 'reviewer', 'student'],
    resolve: roleKey => ({
      canAccessStaffPortal: Boolean(DEFAULT_ROLE_DEFINITIONS[roleKey]?.can_access_staff_portal),
      permissions: [...(DEFAULT_ROLE_PERMISSIONS[roleKey] || [])]
    })
  },
  reviewer_executive: {
    key: 'reviewer_executive',
    label: 'Reviewer Portal Penuh (Baca + Review)',
    description: 'Reviewer dapat masuk Portal Staff, memantau Dashboard/Course/Materi/Nilai, berdiskusi di Forum, dan langsung menyetujui atau merevisi materi.',
    applicableRoles: ['reviewer'],
    resolve: () => ({
      canAccessStaffPortal: true,
      permissions: [
        'dashboard.view',
        'courses.view',
        'materials.view',
        'materials.review',
        'quizzes.view',
        'grades.view',
        'forum.view',
        'forum.participate',
        'notifications.view'
      ]
    })
  },
  reviewer_strict: {
    key: 'reviewer_strict',
    label: 'Reviewer Link Email Ketat',
    description: 'Reviewer hanya meninjau materi melalui tautan aman sekali pakai di Portal Review tanpa masuk ke Portal Staff.',
    applicableRoles: ['reviewer'],
    resolve: () => ({
      canAccessStaffPortal: false,
      permissions: ['materials.review']
    })
  },
  teacher_coordinator: {
    key: 'teacher_coordinator',
    label: 'Teacher Koordinator Kurikulum',
    description: 'Teacher memperoleh kewenangan tambahan untuk meneruskan materi ke reviewer, menerbitkan materi, dan memutuskan enrollment course.',
    applicableRoles: ['teacher'],
    resolve: () => ({
      canAccessStaffPortal: true,
      permissions: [
        ...DEFAULT_ROLE_PERMISSIONS.teacher,
        'courses.assign_trainer',
        'materials.send_to_reviewer',
        'materials.publish',
        'enrollments.decide',
        'reviewers.view'
      ]
    })
  },
  read_only_auditor: {
    key: 'read_only_auditor',
    label: 'Mode Auditor (Baca Saja)',
    description: 'Membatasi peran agar hanya dapat membaca data analitik, course, materi, nilai, dan notifikasi tanpa hak ubah/hapus.',
    applicableRoles: ['teacher', 'reviewer'],
    resolve: () => ({
      canAccessStaffPortal: true,
      permissions: [
        'dashboard.view',
        'activity.view',
        'courses.view',
        'materials.view',
        'quizzes.view',
        'grades.view',
        'users.view',
        'enrollments.view',
        'reviewers.view',
        'forum.view',
        'notifications.view'
      ]
    })
  }
};

let cache = {
  loadedAt: 0,
  roles: null
};

function buildDefaultMatrix() {
  const result = {};
  for (const roleKey of ROLE_KEYS) {
    result[roleKey] = {
      ...DEFAULT_ROLE_DEFINITIONS[roleKey],
      permissions: new Set(DEFAULT_ROLE_PERMISSIONS[roleKey])
    };
  }
  return result;
}

/**
 * Mengaplikasikan aturan anti-lockout sehingga Admin tidak pernah kehilangan akses kelola hak akses.
 */
function enforceSystemSafeguards(roleKey, definition) {
  if (roleKey !== 'admin') return definition;
  const perms = new Set(definition.permissions);
  perms.add('roles.manage');
  return {
    ...definition,
    can_access_staff_portal: true,
    permissions: perms
  };
}

/**
 * Menyemaikan (seed) definisi role & izin default ke database jika tabel belum terisi.
 */
async function ensureSeeded() {
  const existing = await pool.request().query(`SELECT COUNT(*) AS total FROM dbo.lms_role_definitions`);
  if (Number(existing.recordset[0]?.total || 0) >= ROLE_KEYS.length) return;

  for (const roleKey of ROLE_KEYS) {
    const def = DEFAULT_ROLE_DEFINITIONS[roleKey];
    await pool.request()
      .input('key', sql.VarChar(30), def.role_key)
      .input('name', sql.NVarChar(100), def.display_name)
      .input('desc', sql.NVarChar(500), def.description)
      .input('portal', sql.VarChar(30), def.portal_target)
      .input('staff', sql.Bit, def.can_access_staff_portal ? 1 : 0)
      .query(`IF NOT EXISTS (SELECT 1 FROM dbo.lms_role_definitions WHERE role_key=@key)
        INSERT INTO dbo.lms_role_definitions(role_key,display_name,description,portal_target,can_access_staff_portal)
        VALUES(@key,@name,@desc,@portal,@staff);`);

    const defaultAllowed = new Set(DEFAULT_ROLE_PERMISSIONS[roleKey] || []);
    for (const permKey of PERMISSION_KEYS) {
      await pool.request()
        .input('role', sql.VarChar(30), roleKey)
        .input('perm', sql.VarChar(80), permKey)
        .input('allowed', sql.Bit, defaultAllowed.has(permKey) ? 1 : 0)
        .query(`IF NOT EXISTS (SELECT 1 FROM dbo.lms_role_permissions WHERE role_key=@role AND permission_key=@perm)
          INSERT INTO dbo.lms_role_permissions(role_key,permission_key,is_allowed)
          VALUES(@role,@perm,@allowed);`);
    }
  }
}

/**
 * Memuat matriks definisi role dan permission aktif dari SQL Server dengan cache memori singkat.
 */
async function loadRoleMatrix({ forceRefresh = false } = {}) {
  const now = Date.now();
  if (!forceRefresh && cache.roles && (now - cache.loadedAt) < CACHE_TTL_MS) {
    return cache.roles;
  }

  try {
    if (!pool.connected) {
      return buildDefaultMatrix();
    }
    await ensureSeeded();
    const [defsRes, permsRes] = await Promise.all([
      pool.request().query(`SELECT role_key,display_name,description,portal_target,can_access_staff_portal,updated_by,updated_at FROM dbo.lms_role_definitions`),
      pool.request().query(`SELECT role_key,permission_key,is_allowed FROM dbo.lms_role_permissions`)
    ]);

    const matrix = buildDefaultMatrix();
    for (const row of defsRes.recordset) {
      if (!matrix[row.role_key]) continue;
      matrix[row.role_key] = {
        ...matrix[row.role_key],
        role_key: row.role_key,
        display_name: row.display_name,
        description: row.description,
        portal_target: row.portal_target,
        can_access_staff_portal: Boolean(row.can_access_staff_portal),
        updated_by: row.updated_by || null,
        updated_at: row.updated_at || null,
        permissions: new Set()
      };
    }

    const seenByRole = new Map(ROLE_KEYS.map(k => [k, new Set()]));
    for (const row of permsRes.recordset) {
      if (!matrix[row.role_key] || !PERMISSION_KEY_SET.has(row.permission_key)) continue;
      seenByRole.get(row.role_key).add(row.permission_key);
      if (row.is_allowed) {
        matrix[row.role_key].permissions.add(row.permission_key);
      }
    }

    // Jika ada permission baru di katalog yang belum masuk DB, warisi default-nya.
    for (const roleKey of ROLE_KEYS) {
      const seen = seenByRole.get(roleKey);
      const defaults = new Set(DEFAULT_ROLE_PERMISSIONS[roleKey] || []);
      for (const permKey of PERMISSION_KEYS) {
        if (!seen.has(permKey) && defaults.has(permKey)) {
          matrix[roleKey].permissions.add(permKey);
        }
      }
      matrix[roleKey] = enforceSystemSafeguards(roleKey, matrix[roleKey]);
    }

    cache = { loadedAt: now, roles: matrix };
    return matrix;
  } catch (error) {
    if (process.env.NODE_ENV !== 'test') {
      console.error('Role matrix load fallback to defaults:', error.message);
    }
    return buildDefaultMatrix();
  }
}

function invalidateRoleCache() {
  cache = { loadedAt: 0, roles: null };
}

/**
 * Mengambil profil akses efektif (izin + akses portal) untuk satu role.
 */
async function getRoleAccessProfile(roleKey) {
  if (!ROLE_KEYS.includes(roleKey)) {
    return { role_key: roleKey, canAccessStaffPortal: false, permissions: [] };
  }
  const matrix = await loadRoleMatrix();
  const entry = matrix[roleKey] || DEFAULT_ROLE_DEFINITIONS[roleKey];
  const permissions = entry?.permissions instanceof Set
    ? Array.from(entry.permissions)
    : [...(DEFAULT_ROLE_PERMISSIONS[roleKey] || [])];
  return {
    role_key: roleKey,
    display_name: entry.display_name,
    canAccessStaffPortal: Boolean(entry.can_access_staff_portal),
    permissions
  };
}

/**
 * Mengecek apakah role memiliki minimal satu dari daftar izin yang diminta.
 */
async function hasRolePermission(roleKey, requiredPermissions = []) {
  if (!ROLE_KEYS.includes(roleKey)) return false;
  const list = Array.isArray(requiredPermissions) ? requiredPermissions : [requiredPermissions];
  if (!list.length) return true;
  const matrix = await loadRoleMatrix();
  const entry = matrix[roleKey];
  if (!entry) return false;
  return list.some(perm => entry.permissions.has(perm));
}

/**
 * Menyusun selisih perubahan izin antara state sebelum dan sesudah penyuntingan.
 */
function computePermissionDiff(previousPerms, nextPerms) {
  const prevSet = previousPerms instanceof Set ? previousPerms : new Set(previousPerms);
  const nextSet = nextPerms instanceof Set ? nextPerms : new Set(nextPerms);
  const added = [];
  const removed = [];
  for (const key of PERMISSION_KEYS) {
    if (!prevSet.has(key) && nextSet.has(key)) added.push(key);
    if (prevSet.has(key) && !nextSet.has(key)) removed.push(key);
  }
  return { added, removed };
}

/**
 * Menyimpan perubahan metadata dan matriks izin suatu role secara transaksional ke SQL Server
 * serta mencatatnya pada tabel audit log khusus maupun activity_logs.
 */
async function updateRolePermissions({
  roleKey,
  displayName,
  description,
  canAccessStaffPortal,
  permissions,
  actorNpk,
  note = ''
}) {
  if (!ROLE_KEYS.includes(roleKey)) {
    const error = new Error('Role tidak dikenal.');
    error.status = 400;
    throw error;
  }

  const currentMatrix = await loadRoleMatrix({ forceRefresh: true });
  const prevEntry = currentMatrix[roleKey];
  const validPerms = (Array.isArray(permissions) ? permissions : []).filter(key => PERMISSION_KEY_SET.has(key));
  const nextSafe = enforceSystemSafeguards(roleKey, {
    ...prevEntry,
    display_name: String(displayName || prevEntry.display_name).trim().slice(0, 100),
    description: String(description ?? prevEntry.description).trim().slice(0, 500),
    can_access_staff_portal: Boolean(canAccessStaffPortal),
    permissions: new Set(validPerms)
  });

  const diff = computePermissionDiff(prevEntry.permissions, nextSafe.permissions);
  const portalChanged = prevEntry.can_access_staff_portal !== nextSafe.can_access_staff_portal;
  const summaryParts = [];
  if (diff.added.length) summaryParts.push(`+${diff.added.length} izin`);
  if (diff.removed.length) summaryParts.push(`-${diff.removed.length} izin`);
  if (portalChanged) {
    summaryParts.push(nextSafe.can_access_staff_portal ? 'Portal Staff dibuka' : 'Portal Staff ditutup');
  }
  if (!summaryParts.length) summaryParts.push('Profil role diperbarui');
  const summary = `${summaryParts.join(', ')}${note ? ` · ${note}` : ''}`.slice(0, 500);

  const tx = new sql.Transaction(pool);
  await tx.begin();
  try {
    await new sql.Request(tx)
      .input('key', sql.VarChar(30), roleKey)
      .input('name', sql.NVarChar(100), nextSafe.display_name)
      .input('desc', sql.NVarChar(500), nextSafe.description)
      .input('staff', sql.Bit, nextSafe.can_access_staff_portal ? 1 : 0)
      .input('actor', sql.VarChar(50), actorNpk)
      .query(`UPDATE dbo.lms_role_definitions
        SET display_name=@name, description=@desc, can_access_staff_portal=@staff, updated_by=@actor, updated_at=SYSUTCDATETIME()
        WHERE role_key=@key;`);

    for (const permKey of PERMISSION_KEYS) {
      const allowed = nextSafe.permissions.has(permKey) ? 1 : 0;
      await new sql.Request(tx)
        .input('role', sql.VarChar(30), roleKey)
        .input('perm', sql.VarChar(80), permKey)
        .input('allowed', sql.Bit, allowed)
        .input('actor', sql.VarChar(50), actorNpk)
        .query(`MERGE dbo.lms_role_permissions AS target
          USING (SELECT @role AS role_key, @perm AS permission_key) AS source
          ON target.role_key = source.role_key AND target.permission_key = source.permission_key
          WHEN MATCHED THEN UPDATE SET is_allowed=@allowed, updated_by=@actor, updated_at=SYSUTCDATETIME()
          WHEN NOT MATCHED THEN INSERT(role_key, permission_key, is_allowed, updated_by) VALUES(@role, @perm, @allowed, @actor);`);
    }

    await new sql.Request(tx)
      .input('role', sql.VarChar(30), roleKey)
      .input('actor', sql.VarChar(50), actorNpk)
      .input('summary', sql.NVarChar(500), summary)
      .input('changes', sql.NVarChar(sql.MAX), JSON.stringify({
        added: diff.added,
        removed: diff.removed,
        portalAccess: portalChanged
          ? { from: prevEntry.can_access_staff_portal, to: nextSafe.can_access_staff_portal }
          : null,
        note: note || null
      }))
      .query(`INSERT INTO dbo.lms_role_audit_logs(role_key, changed_by, summary, changes_json)
        VALUES(@role, @actor, @summary, @changes);
        INSERT INTO dbo.activity_logs(employee_npk, activity_type, description)
        VALUES(@actor, 'role_permissions_updated', CONCAT(N'Mengubah hak akses role ', @role, N': ', @summary));`);

    await tx.commit();
    invalidateRoleCache();
    return { diff, summary };
  } catch (error) {
    await tx.rollback().catch(() => {});
    throw error;
  }
}

/**
 * Mengambil riwayat audit perubahan hak akses role terbaru.
 */
async function getRoleAuditLogs(limit = 50) {
  if (!pool.connected) return [];
  const safeLimit = Math.max(1, Math.min(200, Number(limit) || 50));
  const result = await pool.request()
    .input('limit', sql.Int, safeLimit)
    .query(`SELECT TOP (@limit) l.audit_id, l.role_key, l.changed_by,
      COALESCE(ea.fullname, l.changed_by) AS changed_by_name,
      l.summary, l.changes_json, l.created_at
      FROM dbo.lms_role_audit_logs l
      LEFT JOIN dbo.employee_accounts ea ON ea.employee_npk = l.changed_by
      ORDER BY l.audit_id DESC`);
  return result.recordset.map(row => {
    let parsedChanges = {};
    try { parsedChanges = JSON.parse(row.changes_json || '{}'); } catch { /* ignore */ }
    return {
      audit_id: row.audit_id,
      role_key: row.role_key,
      changed_by: row.changed_by,
      changed_by_name: row.changed_by_name,
      summary: row.summary,
      changes: parsedChanges,
      created_at: row.created_at
    };
  });
}

/**
 * Mengembalikan seluruh snapshot katalog, role, preset, dan audit log untuk antarmuka Admin.
 */
async function getRoleManagementSnapshot() {
  const matrix = await loadRoleMatrix({ forceRefresh: true });
  const roles = ROLE_KEYS.map(key => ({
    role_key: matrix[key].role_key,
    display_name: matrix[key].display_name,
    description: matrix[key].description,
    portal_target: matrix[key].portal_target,
    can_access_staff_portal: matrix[key].can_access_staff_portal,
    updated_by: matrix[key].updated_by || null,
    updated_at: matrix[key].updated_at || null,
    permissions: Array.from(matrix[key].permissions)
  }));
  const categories = Array.from(
    PERMISSION_CATALOG.reduce((map, item) => {
      if (!map.has(item.category)) map.set(item.category, item.categoryLabel);
      return map;
    }, new Map())
  ).map(([key, label]) => ({ key, label }));
  const presets = Object.values(ROLE_PRESETS).map(({ key, label, description, applicableRoles }) => ({
    key,
    label,
    description,
    applicableRoles
  }));
  const auditLogs = await getRoleAuditLogs(40);
  return {
    catalog: PERMISSION_CATALOG,
    categories,
    roles,
    presets,
    auditLogs
  };
}

module.exports = {
  PERMISSION_CATALOG,
  PERMISSION_KEYS,
  PERMISSION_KEY_SET,
  ROLE_KEYS,
  DEFAULT_ROLE_DEFINITIONS,
  DEFAULT_ROLE_PERMISSIONS,
  ROLE_PRESETS,
  loadRoleMatrix,
  invalidateRoleCache,
  enforceSystemSafeguards,
  getRoleAccessProfile,
  hasRolePermission,
  computePermissionDiff,
  updateRolePermissions,
  getRoleAuditLogs,
  getRoleManagementSnapshot
};
