export function canAccessAdminManagement(role) {
  return role === 'admin';
}

export function canManageUsers(role) {
  return role === 'admin' || role === 'teacher';
}
