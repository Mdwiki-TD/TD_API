<?php

namespace API\SQL;

if (!extension_loaded('apcu')) {
    function apcu_exists($key)
    {
        return false;
    }
    function apcu_fetch($key)
    {
        return false;
    }
    function apcu_store($key, $value, $ttl = 0)
    {
        return false;
    }
    function apcu_delete($key)
    {
        return false;
    }
}

/*
Usage:
use function API\SQL\fetch_query;
*/

if (isset($_REQUEST['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};

use PDO;
use PDOException;

class ConnectionManager
{
    private string $host;
    private string $user;
    private string $password;
    private string $dbname;

    public function __construct(array $config)
    {
        $this->host = $config['host'] ?? 'localhost:3306';
        $this->user = $config['user'] ?? 'root';
        $this->password = $config['password'] ?? 'root11';
        $this->dbname = $config['dbname'] ?? 'mdwiki';
    }

    public function getConnection(): PDO
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname}";
            $db = new PDO($dsn, $this->user, $this->password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $db;
        } catch (PDOException $e) {
            throw new DatabaseConnectionException(
                "Failed to connect to database: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}

class DatabaseException extends \Exception {}
class DatabaseConnectionException extends DatabaseException {}

class Database
{

    private $db;
    private $host;
    private $user;
    private $password;
    private $dbname;
    private $db_suffix;

    public function __construct($server_name, $db_suffix = 'mdwiki')
    {
        if (empty($db_suffix)) {
            $db_suffix = 'mdwiki';
        }
        // ---
        $this->db_suffix = $db_suffix;
        $config = $this->getDatabaseConfig($server_name);
        $connectionManager = new ConnectionManager($config);
        $this->db = $connectionManager->getConnection();
    }

    private function getDatabaseConfig(string $server_name): array
    {
        if ($server_name === 'localhost' || !getenv('HOME')) {
            return [
                'host' => 'localhost:3306',
                'dbname' => $this->db_suffix,
                'user' => 'root',
                'password' => 'root11'
            ];
        }

        $ts_pw = posix_getpwuid(posix_getuid());
        $ts_mycnf = parse_ini_file($ts_pw['dir'] . "/confs/db.ini");

        return [
            'host' => 'tools.db.svc.wikimedia.cloud',
            'dbname' => $ts_mycnf['user'] . "__" . $this->db_suffix,
            'user' => $ts_mycnf['user'],
            'password' => $ts_mycnf['password']
        ];
    }
    public function fetch_query(string $sql_query, ?array $params = null): array
    {
        $this->setSqlMode();
        $statement = $this->prepareStatement($sql_query);
        $this->executeStatement($statement, $params);
        return $this->fetchResults($statement);
    }

    private function setSqlMode(): void
    {
        $this->db->exec("SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY',''))");
    }

    private function prepareStatement(string $sql): \PDOStatement
    {
        try {
            return $this->db->prepare($sql);
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to prepare statement: " . $e->getMessage());
        }
    }

    private function executeStatement(\PDOStatement $statement, ?array $params): void
    {
        try {
            $statement->execute($params);
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to execute statement: " . $e->getMessage());
        }
    }

    private function fetchResults(\PDOStatement $statement): array
    {
        try {
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to fetch results: " . $e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->db = null;
    }
}

function create_apcu_key($sql_query, $params)
{
    if (empty($sql_query)) {
        return "!empty_sql_query";
    }
    // Serialize the parameters to create a unique cache key
    $params_string = is_array($params) ? json_encode($params) : '';

    return 'apcu_' . md5($sql_query . $params_string);
}

function get_from_apcu($sql_query, $params)
{
    $cache_key = create_apcu_key($sql_query, $params);
    // ---
    $items = [];
    // ---
    if (!extension_loaded('apcu')) {
        return false;
    }

    if (apcu_exists($cache_key)) {
        $items = apcu_fetch($cache_key);
        // ---
        if (empty($items)) {
            apcu_delete($cache_key);
            $items = false;
        }
    }
    // ---
    return $items;
}

function add_to_apcu($sql_query, $params, $results)
{
    $cache_key = create_apcu_key($sql_query, $params);
    // ---
    $cache_ttl = 3600 * 12;
    // ---
    if (!extension_loaded('apcu')) {
        return false;
    }

    apcu_store($cache_key, $results, $cache_ttl);
}

function fetch_query_new($sql_query, $params, $get)
{
    if ($get != 'settings' && isset($_REQUEST['apcu'])) {
        $in_apcu = get_from_apcu($sql_query, $params);
        // ---
        if ($in_apcu && is_array($in_apcu) && !empty($in_apcu)) {
            return ['results' => $in_apcu, "source" => "apcu"];
        }
    }
    // ---
    $dbname = 'mdwiki';
    // ---
    $gets_new_db = ["missing", "missing_qids", "publish_reports"];
    // ---
    if (in_array($get, $gets_new_db)) {
        $dbname = 'mdwiki_new';
    }
    // ---
    // Create a new database object
    $db = new Database($_SERVER['SERVER_NAME'] ?? '', $dbname);

    // Execute a SQL query
    $results = $db->fetch_query($sql_query, $params);

    // Destroy the database object
    $db = null;

    if ($get != 'settings' && isset($_REQUEST['apcu'])) {
        if ($results && !empty($results)) {
            add_to_apcu($sql_query, $params, $results);
        }
    }

    return ['results' => $results, "source" => "db"];
}
