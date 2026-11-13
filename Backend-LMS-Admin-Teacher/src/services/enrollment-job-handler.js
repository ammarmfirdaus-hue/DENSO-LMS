const { sql, pool } = require('../config/database');
const { decideEnrollmentRequest } = require('./course-enrollments');
const { publishNotificationEvent } = require('./notification-events');

function registerEnrollmentJobHandler(register) {
  register('enrollments_decision', async (item, context) => {
    if (context.ownerRole !== 'admin') {
      throw new Error('Keputusan pendaftaran course hanya dapat diproses oleh Admin.');
    }
    const requestId = Number(item.requestId || item.id);
    if (!requestId || !Number.isInteger(requestId)) {
      throw new Error('ID permintaan pendaftaran tidak valid.');
    }
    const action = item.action === 'rejected' ? 'rejected' : 'approved';
    const note = String(item.note || '').trim();
    if (action === 'rejected' && note.length < 3) {
      throw new Error('Penolakan pendaftaran membutuhkan alasan minimal 3 karakter.');
    }

    const decision = await decideEnrollmentRequest({
      requestId,
      action,
      adminNpk: context.ownerNpk,
      note
    });

    try {
      const notifTitle = action === 'approved' ? 'Akses Course Disetujui' : 'Permintaan Course Ditolak';
      const notifMessage = action === 'approved'
        ? `Akses ke course ${decision.courseTitle} (${decision.courseCode}) telah disetujui Admin. Course sekarang tersedia di My Course.`
        : `Permintaan akses ke course ${decision.courseTitle} (${decision.courseCode}) ditolak Admin. Alasan: ${note || '-'}.`;

      await pool.request()
        .input('npk', sql.VarChar(50), decision.studentNpk)
        .input('title', sql.NVarChar(180), notifTitle)
        .input('message', sql.NVarChar(2000), notifMessage)
        .input('senderNpk', sql.VarChar(50), context.ownerNpk)
        .input('senderName', sql.NVarChar(150), 'Admin LMS')
        .query(`INSERT INTO dbo.lms_notifications(audience_role,recipient_npk,title,message,category,priority,sender_npk,sender_role,sender_name,email_requested)
          VALUES('student',@npk,@title,@message,'course','important',@senderNpk,'admin',@senderName,0)`);

      publishNotificationEvent({
        roles: ['student'],
        recipientNpk: decision.studentNpk,
        type: 'notification_created'
      });
    } catch (notifErr) {
      console.error('Enrollment job notification dispatch skipped:', notifErr.message);
    }

    return action === 'approved'
      ? `Akses untuk ${decision.studentName} (${decision.courseCode}) disetujui.`
      : `Permintaan ${decision.studentName} (${decision.courseCode}) ditolak.`;
  });
}

module.exports = { registerEnrollmentJobHandler };

