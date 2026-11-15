// Allowlist bersama untuk ekspor dan restore. Jangan menerima nama tabel dari client.
const BACKUP_DATASETS = Object.freeze({
  employees: { table: 'employee_accounts', keys: ['employee_npk'] },
  courses: { table: 'courses', keys: ['course_id'] },
  sessions: { table: 'course_sessions', keys: ['session_id'] },
  materials: { table: 'course_materials', keys: ['material_id'] },
  submissions: { table: 'material_submissions', keys: ['submission_id'] },
  reviewLinks: { table: 'material_review_links', keys: ['review_link_id'] },
  enrollments: { table: 'student_progress_snapshots', keys: ['snapshot_id'] },
  forumThreads: { table: 'forum_threads', keys: ['thread_id'] },
  forumReplies: { table: 'forum_replies', keys: ['reply_id'] },
  notifications: { table: 'lms_notifications', keys: ['notification_id'] },
  activity: { table: 'activity_logs', keys: ['activity_id'] },
  settings: { table: 'lms_system_settings', keys: ['setting_key'] },
  roleDefinitions: { table: 'lms_role_definitions', keys: ['role_key'] },
  rolePermissions: { table: 'lms_role_permissions', keys: ['role_key', 'permission_key'] }
});

/**
 * SQL Server does not support parameters for identifiers.  Keep identifier
 * quoting in one place and reject anything outside the allowlisted identifier
 * grammar before it can become part of a query string.
 */
function quoteBackupIdentifier(value) {
  if (typeof value !== 'string' || !/^[A-Za-z_][A-Za-z0-9_]*$/.test(value)) {
    throw new Error('Identifier backup tidak valid.');
  }
  return `[${value}]`;
}

module.exports = { BACKUP_DATASETS, quoteBackupIdentifier };
