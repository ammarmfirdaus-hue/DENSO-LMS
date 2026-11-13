const { sql, pool } = require('../config/database');
const { publishNotificationEvent } = require('./notification-events');

function registerNotificationJobHandler(register) {
  register('notifications_broadcast', async (item, context) => {
    const title = String(item.title || '').trim();
    const message = String(item.message || '').trim();
    const category = String(item.category || 'general').trim();
    const priority = ['urgent', 'important', 'normal'].includes(item.priority) ? item.priority : 'normal';
    const targetRole = ['student', 'teacher', 'admin'].includes(item.role) ? item.role : 'student';
    const recipientNpk = item.npk ? String(item.npk).trim() : null;
    const recipientEmail = item.email ? String(item.email).trim() : null;
    const recipientName = item.fullname ? String(item.fullname).trim() : (recipientNpk || 'Pengguna');

    if (!title || !message) {
      throw new Error('Judul dan pesan notifikasi wajib diisi.');
    }

    // Insert notification
    const result = await pool.request()
      .input('audience', sql.VarChar(20), targetRole)
      .input('recipientNpk', sql.VarChar(50), recipientNpk)
      .input('title', sql.NVarChar(180), title)
      .input('message', sql.NVarChar(2000), message)
      .input('category', sql.VarChar(30), category)
      .input('priority', sql.VarChar(20), priority)
      .input('senderNpk', sql.VarChar(50), context.ownerNpk)
      .input('senderRole', sql.VarChar(20), context.ownerRole)
      .input('senderName', sql.NVarChar(150), 'Pengirim LMS')
      .input('emailRequested', sql.Bit, Boolean(item.sendEmail && recipientEmail))
      .input('showPopup', sql.Bit, Boolean(item.showPopup))
      .query(`INSERT INTO dbo.lms_notifications(audience_role,recipient_npk,title,message,category,priority,sender_npk,sender_role,sender_name,email_requested,show_popup)
        OUTPUT INSERTED.notification_id
        VALUES(@audience,@recipientNpk,@title,@message,@category,@priority,@senderNpk,@senderRole,@senderName,@emailRequested,@showPopup)`);

    const notificationId = result.recordset[0]?.notification_id;

    // Queue email outbox if requested
    if (item.sendEmail && recipientEmail && notificationId) {
      await pool.request()
        .input('notifId', sql.BigInt, notificationId)
        .input('email', sql.NVarChar(254), recipientEmail)
        .input('name', sql.NVarChar(150), recipientName)
        .query(`INSERT INTO dbo.lms_notification_email_outbox(notification_id,recipient_email,recipient_name)
          VALUES(@notifId,@email,@name)`);
    }

    publishNotificationEvent({
      roles: [targetRole],
      recipientNpk: recipientNpk || undefined,
      type: 'notification_created',
      showPopup: Boolean(item.showPopup)
    });

    return `Notifikasi terkirim ke ${recipientName} (${recipientNpk || targetRole}).`;
  });
}

module.exports = { registerNotificationJobHandler };

