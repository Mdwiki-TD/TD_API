<?php
// src/app/Database/Database.php
declare(strict_types=1);

/**
 * Database Abstraction Layer for MDWiki SQL Operations
 *
 */

namespace App\Database;

use App\Logger;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Database Connection and Query Management Class
 *
 * Encapsulates PDO database operations with automatic connection management,
 * error handling, and environment-specific configuration.
 *
 * @package Database
 */
class Database
{
    private ?PDO $db = null;
    private bool $groupByModeDisabled = false;

    public function __construct(string $dbnameVar = 'DB_NAME')
    {
        $this->connect($dbnameVar);
    }

    private static function env(string $key): string
    {
        $value = getenv($key);
        if ($value !== false) {
            return (string) $value;
        }
        return (string) ($_ENV[$key] ?? '');
    }

    private function connect(string $dbnameVar): void
    {
        $host     = self::env('DB_HOST_TOOLS') ?: 'tools.db.svc.wikimedia.cloud';
        $dbname   = self::env($dbnameVar);
        $user     = self::env('TOOL_TOOLSDB_USER');
        $password = self::env('TOOL_TOOLSDB_PASSWORD');

        // skip DB connection attempt if credentials are missing
        if ($dbname === '' || $user === '' || $password === '') {
            Logger::error('Database credentials are not fully configured; skipping DB connection.');
            return;
        }

        try {
            $this->db = new PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            $this->db = null;
            Logger::error('DB connection failed: ' . $e->getMessage());
        }
    }

    public function isDbNull(): bool
    {
        return $this->db === null;
    }

    /**
     * يعطل ONLY_FULL_GROUP_BY مرة واحدة لكل اتصال عند وجود GROUP BY.
     * (سلوك قديم تعتمد عليه بعض الاستعلامات؛ انظر ملاحظات المراجعة.)
     */
    private function disableFullGroupByMode(string $sqlQuery): void
    {
        if ($this->groupByModeDisabled || $this->db === null) {
            return;
        }
        if (stripos($sqlQuery, 'GROUP BY') === false) {
            return;
        }

        try {
            $this->db->exec("SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY',''))");
            $this->groupByModeDisabled = true;
        } catch (PDOException $e) {
            // لا نُفشل الاستعلام بسبب ذلك
            Logger::error('Failed to disable ONLY_FULL_GROUP_BY: ' . $e->getMessage());
        }
    }

    /**
     * @param  array<int|string, mixed>|null $params
     * @return array<int, array<string, mixed>>
     * @throws DatabaseException عند غياب الاتصال أو فشل الاستعلام
     */
    public function fetchQuery(string $sqlQuery, ?array $params = null): array
    {
        if ($this->db === null) {
            throw new DatabaseException('Database connection is not established');
        }

        Logger::debug('fetchQuery: ' . $sqlQuery);

        try {
            $this->disableFullGroupByMode($sqlQuery);

            $stmt = $this->db->prepare($sqlQuery);
            $stmt->execute($params ?: null);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            Logger::error('SQL Error in fetchQuery: ' . $e->getMessage() . ' | Query: ' . $sqlQuery);
            throw new DatabaseException('Query failed', 0, $e);
        }
    }

    public function __destruct()
    {
        $this->db = null;
    }
}
