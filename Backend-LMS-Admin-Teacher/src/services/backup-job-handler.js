const { sql, pool } = require('../config/database');
const { BACKUP_DATASETS, quoteBackupIdentifier } = require('./backup-datasets');

async function getIdentityColumn(transaction, rawTableName) {
  const result = await new sql.Request(transaction)
    .input('fullTable', sql.NVarChar(256), `dbo.${rawTableName}`)
    .query('SELECT TOP 1 name FROM sys.columns WHERE object_id = OBJECT_ID(@fullTable) AND is_identity = 1;');
  return result.recordset[0]?.name || null;
}

async function restoreBatch(item) {
  const dataset = BACKUP_DATASETS[item.dataset];
  if (!dataset || !Array.isArray(item.rows) || !item.rows.length) return 'Tidak ada baris untuk dipulihkan.';
  const table = quoteBackupIdentifier(dataset.table);
  const columns = [...new Set(item.rows.flatMap(row => Object.keys(row)))].filter(name => /^[A-Za-z_][A-Za-z0-9_]*$/.test(name));
  if (!columns.length || dataset.keys.some(key => !columns.includes(key))) throw new Error('Struktur backup tidak cocok dengan dataset.');
  const quotedColumns = columns.map(quoteBackupIdentifier).join(',');
  const transaction = new sql.Transaction(pool);
  await transaction.begin();
  let identityInsertEnabled = false;
  try {
    const identityColumn = await getIdentityColumn(transaction, dataset.table);
    const updates = columns
      .filter(column => !dataset.keys.includes(column) && column !== identityColumn)
      .map(column => `target.${quoteBackupIdentifier(column)}=source.${quoteBackupIdentifier(column)}`)
      .join(',');

    if (identityColumn && columns.includes(identityColumn)) {
      await new sql.Request(transaction).query(`SET IDENTITY_INSERT dbo.${table} ON;`);
      identityInsertEnabled = true;
    }

    for (const row of item.rows) {
      const request = new sql.Request(transaction);
      const values = columns.map((column, index) => {
        const name = `v${index}`;
        request.input(name, row[column] === undefined ? null : row[column]);
        return `@${name}`;
      }).join(',');
      const insertValues = columns.map(column => `source.${quoteBackupIdentifier(column)}`).join(',');
      const updateClause = updates ? `WHEN MATCHED THEN UPDATE SET ${updates}` : '';
      await request.query(`MERGE dbo.${table} AS target USING (VALUES (${values})) AS source (${quotedColumns})
        ON ${dataset.keys.map(key => `target.${quoteBackupIdentifier(key)}=source.${quoteBackupIdentifier(key)}`).join(' AND ')}
        ${updateClause}
        WHEN NOT MATCHED BY TARGET THEN INSERT (${quotedColumns}) VALUES (${insertValues});`);
    }

    if (identityInsertEnabled) {
      await new sql.Request(transaction).query(`SET IDENTITY_INSERT dbo.${table} OFF;`);
      identityInsertEnabled = false;
    }

    await transaction.commit();
    return `${item.rows.length} baris dipulihkan.`;
  } catch (error) {
    if (identityInsertEnabled) {
      await new sql.Request(transaction).query(`SET IDENTITY_INSERT dbo.${table} OFF;`).catch(() => {});
    }
    await transaction.rollback().catch(() => {});
    throw error;
  }
}

function registerBackupJobHandler(register) {
  register('backup_restore', restoreBatch);
}

module.exports = { registerBackupJobHandler };
