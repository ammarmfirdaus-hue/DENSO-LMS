const { EventEmitter } = require('node:events');

const supportChatEvents = new EventEmitter();
supportChatEvents.setMaxListeners(0);

function publishSupportChatEvent(event) {
  supportChatEvents.emit('change', {
    id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    ...event,
  });
}

module.exports = { supportChatEvents, publishSupportChatEvent };

