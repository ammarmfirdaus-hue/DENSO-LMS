function getSubmissionEditDecision({ userRole, isSubmitter, status }) {
  if (userRole === 'teacher') {
    if (!isSubmitter) return { allowed: false, code: 'FORBIDDEN' };
    if (status === 'pending_admin') return { allowed: false, code: 'CANNOT_EDIT_PENDING_APPROVAL' };
    if (status === 'pending_review') return { allowed: false, code: 'CANNOT_EDIT_IN_REVIEW' };
    if (status === 'approved' || status === 'published') return { allowed: false, code: 'CANNOT_EDIT_FINAL' };
    if (status === 'rejected') return { allowed: true };
    return { allowed: false, code: 'INVALID_STATUS' };
  }

  if (userRole === 'admin') {
    if (status === 'pending_review') return { allowed: false, code: 'CANNOT_EDIT_IN_REVIEW' };
    if (status === 'approved' || status === 'published') return { allowed: false, code: 'CANNOT_EDIT_FINAL' };
    if (status === 'pending_admin' || status === 'rejected') return { allowed: true };
    return { allowed: false, code: 'INVALID_STATUS' };
  }

  return { allowed: false, code: 'FORBIDDEN' };
}

module.exports = { getSubmissionEditDecision };
