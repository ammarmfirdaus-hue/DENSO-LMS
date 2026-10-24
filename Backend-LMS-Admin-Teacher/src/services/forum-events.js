const { EventEmitter } = require('node:events');

const forumEvents = new EventEmitter();
forumEvents.setMaxListeners(0);

function publishForumEvent(event) {
  forumEvents.emit('change', { id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`, ...event });
}

module.exports = { forumEvents, publishForumEvent };
