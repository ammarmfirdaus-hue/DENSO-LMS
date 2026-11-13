const VALID_WINDOWS = new Set([7, 14, 30]);
const SHORT_MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

function normalizeDaysWindow(raw) {
  const numeric = Number(raw);
  return VALID_WINDOWS.has(numeric) ? numeric : 7;
}

function toIsoDateUtc(date) {
  return date.toISOString().slice(0, 10);
}

function formatShortDateLabel(isoDate) {
  const [, month, day] = isoDate.split('-');
  const monthLabel = SHORT_MONTHS[Math.max(0, Number(month) - 1)] || month;
  return `${Number(day)} ${monthLabel}`;
}

function buildDateWindow(days, anchorDate = new Date()) {
  const normalizedDays = normalizeDaysWindow(days);
  const utcMidnight = new Date(Date.UTC(
    anchorDate.getUTCFullYear(),
    anchorDate.getUTCMonth(),
    anchorDate.getUTCDate()
  ));
  const result = [];
  for (let offset = normalizedDays - 1; offset >= 0; offset -= 1) {
    const point = new Date(utcMidnight.getTime() - offset * 86400000);
    const isoDate = toIsoDateUtc(point);
    result.push({ isoDate, label: formatShortDateLabel(isoDate) });
  }
  return result;
}

function parseMaterialTypes(rows) {
  const order = ['text', 'video', 'pdf', 'document', 'image', 'audio', 'link'];
  const counts = new Map(order.map(type => [type, 0]));
  for (const row of rows || []) {
    const key = String(row.material_type || 'text').toLowerCase();
    counts.set(key, (counts.get(key) || 0) + Number(row.total || 0));
  }
  return Array.from(counts.entries())
    .map(([type, count]) => ({ type, count }))
    .filter(item => item.count > 0 || ['text', 'video', 'pdf', 'link'].includes(item.type));
}

module.exports = {
  normalizeDaysWindow,
  toIsoDateUtc,
  formatShortDateLabel,
  buildDateWindow,
  parseMaterialTypes
};

