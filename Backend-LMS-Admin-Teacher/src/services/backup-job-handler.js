const { sql, pool } = require('../config/database');
const { BACKUP_DATASETS, quoteBackupIdentifier } = require('./backup-datasets');

async function restoreBatch(item) {
  const dataset = BACKUP_DATASETS[item.dataset];
  if (!dataset || !Array.isArray(item.rows) || !item.rows.length) return 'Tidak ada baris untuk dipulihkan.';
  const table = quoteBackupIdentifier(dataset.table);
  const columns = [...new Set(item.rows.flatMap(row => Object.keys(row)))].filter(name => /^[A-Za-z_][A-Za-z0-9_]*$/.test(name));
  if (!columns.length || dataset.keys.some(key => !columns.includes(key))) throw new Error('Struktur backup tidak cocok dengan dataset.');
  const keys = dataset.keys.map(quoteBackupIdentifier).join(',');
  const quotedColumns = columns.map(quoteBackupIdentifier).join(',');
  const updates = columns.filter(column => !dataset.keys.includes(column)).map(column => `target.${quoteBackupIdentifier(column)}=source.${quoteBackupIdentifier(column)}`).join(',');
  const transaction = new sql.Transaction(pool);
  await transaction.begin();
  try {
    for (const row of item.rows) {
      const request = new sql.Request(transaction);
      const values = columns.map((column, index) => {
        const name = `v${index}`;
        request.input(name, row[column] === undefined ? null : row[column]);
        return `@${name}`;
      }).join(',');
      const insertValues = columns.map(column => `source.${quoteBackupIdentifier(column)}`).join(',');
      const updateClause = updates ? `WHEN MATCHED THEN UPDATE SET ${updates}` : '';
      await request.query(`MERGE ${table} AS target USING (VALUES (${values})) AS source (${quotedColumns})
        ON ${dataset.keys.map(key => `target.${quoteBackupIdentifier(key)}=source.${quoteBackupIdentifier(key)}`).join(' AND ')}
        ${updateClause}
        WHEN NOT MATCHED BY TARGET THEN INSERT (${quotedColumns}) VALUES (${insertValues});`);
    }
    await transaction.commit();
    return `${item.rows.length} baris dipulihkan.`;
  } catch (error) {
    await transaction.rollback().catch(() => {});
    throw error;
  }
}

function registerBackupJobHandler(register) {
  register('backup_restore', restoreBatch);
}

module.exports = { registerBackupJobHandler };
