const { EventEmitter } = require('node:events');

const notificationEvents = new EventEmitter();
notificationEvents.setMaxListeners(0);

function publishNotificationEvent({ roles, recipientNpk = null, type = 'notification_changed', showPopup = false, payload = null }) {
  notificationEvents.emit('change', {
    roles: [...new Set(roles)],
    recipientNpk: recipientNpk ? String(recipientNpk) : null,
    type,
    showPopup: Boolean(showPopup),
    payload,
    id: Date.now()
  });
}

module.exports = { notificationEvents, publishNotificationEvent };
