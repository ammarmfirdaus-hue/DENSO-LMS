<?php
/**
 * DLS Database Connection
 * Menggunakan PDO with SQLSRV driver
 */

require_once __DIR__ . '/config.php';

/**
 * Get PDO database connection
 * @return PDO|null
 */
function getDBConnection(): ?PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "sqlsrv:Server=" . DB_HOST . "," . DB_PORT . ";Database=" . DB_NAME;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::SQLSRV_ATTR_ENCODING    => PDO::SQLSRV_ENCODING_UTF8,
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Catat error, jangan tampilkan di production
            if (defined('E_ALL') && error_reporting() === E_ALL) {
                die("Database connection failed: " . $e->getMessage());
            }
            return null;
        }
    }

    return $pdo;
}

/**
 * Execute a SELECT query
 * @param string $sql
 * @param array $params
 * @return array
 */
function dbQuery(string $sql, array $params = []): array
{
    $pdo = getDBConnection();
    if (!$pdo) return [];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Execute a single row SELECT
 * @param string $sql
 * @param array $params
 * @return array|null
 */
function dbQueryOne(string $sql, array $params = []): ?array
{
    $pdo = getDBConnection();
    if (!$pdo) return null;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Execute INSERT/UPDATE/DELETE
 * @param string $sql
 * @param array $params
 * @return bool
 */
function dbExecute(string $sql, array $params = []): bool
{
    $pdo = getDBConnection();
    if (!$pdo) return false;

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}
