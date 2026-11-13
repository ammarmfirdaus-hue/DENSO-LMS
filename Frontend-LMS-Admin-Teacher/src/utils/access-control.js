/**
 * Memeriksa apakah pengguna memiliki minimal satu dari izin RBAC yang diminta.
 * Admin selalu mendapat akses penuh jika array permissions belum termuat,
 * namun tetap mengikuti daftar izin aktif dari server bila tersedia.
 *
 * @param {object|null|undefined} user Objek sesi pengguna aktif.
 * @param {string|string[]} requiredPermissions Kunci izin atau array kunci izin (OR logic).
 * @returns {boolean}
 */
export function hasPermission(user, requiredPermissions) {
  if (!user || !user.role) return false;
  if (!requiredPermissions || (Array.isArray(requiredPermissions) && requiredPermissions.length === 0)) {
    return true;
  }
  const list = Array.isArray(requiredPermissions) ? requiredPermissions : [requiredPermissions];
  const granted = Array.isArray(user.permissions) ? user.permissions : null;
  if (!granted) {
    return user.role === 'admin';
  }
  return list.some(perm => granted.includes(perm));
}

/**
 * Memeriksa apakah akun diperbolehkan masuk ke Portal Staff React.
 *
 * @param {object|null|undefined} user Objek sesi pengguna dari API `/auth/login` atau `/auth/me`.
 * @returns {boolean}
 */
export function canAccessStaffPortal(user) {
  if (!user || !user.role) return false;
  if (typeof user.canAccessStaffPortal === 'boolean') {
    return user.canAccessStaffPortal;
  }
  return user.role === 'admin' || user.role === 'teacher';
}

/**
 * Mengembalikan label peran yang mudah dibaca untuk header dan kartu profil.
 *
 * @param {string} role Kunci role ('admin', 'teacher', 'reviewer', 'student').
 * @returns {string}
 */
export function resolveRoleLabel(role) {
  switch (role) {
    case 'admin':
      return 'Administrator';
    case 'teacher':
      return 'Teacher';
    case 'reviewer':
      return 'Reviewer';
    case 'student':
      return 'Student';
    default:
      return role || '-';
  }
}

export function canAccessAdminManagement(userOrRole) {
  if (typeof userOrRole === 'string') return userOrRole === 'admin';
  return hasPermission(userOrRole, ['reviewers.view', 'reviewers.manage', 'roles.manage', 'settings.api']);
}

export function canManageUsers(userOrRole) {
  if (typeof userOrRole === 'string') return userOrRole === 'admin' || userOrRole === 'teacher';
  return hasPermission(userOrRole, ['users.view', 'users.manage']);
}
