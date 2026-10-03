<?php

namespace API\SQL;

use PDO;
use PDOException;

if (!extension_loaded('apcu') || !function_exists('apcu_exists')) {
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

class Database
{

    private $db;
    private $host;
    private $user;
    private $password;
    private $dbname;
    private $appEnv;
    private $groupByModeDisabled = false;

    public function __construct(string $dbnameVar = 'DB_NAME')
    {
        $this->appEnv = $this->envVar('APP_ENV');
        $this->setDb($dbnameVar);
    }

    private function envVar(string $key)
    {
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        return "";
    }
    private function buildDsn(string $dbnameVar): string
    {
        // Load host and database name from environment variables, falling back to a default host
        $this->host   = $this->envVar('DB_HOST_TOOLS') ?: 'tools.db.svc.wikimedia.cloud';
        $this->dbname = $this->envVar($dbnameVar);

        // Build the PDO Data Source Name (DSN) string for MySQL connection
        return "mysql:host={$this->host};dbname={$this->dbname}";
    }

    private function hasValidCredentials(): bool
    {
        // Check whether all required connection credentials are present
        return !empty($this->host) && !empty($this->dbname) && !empty($this->user) && !empty($this->password);
    }

    private function setDb(string $dbnameVar)
    {
        $this->host = $this->envVar('DB_HOST_TOOLS') ?: 'tools.db.svc.wikimedia.cloud';
        $this->dbname = $this->envVar($dbnameVar);
        $this->user     = $this->envVar('TOOL_TOOLSDB_USER');
        $this->password = $this->envVar('TOOL_TOOLSDB_PASSWORD');

        try {
            $this->db = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Log the error message
            error_log($e->getMessage());
            // Display a generic message
            echo "Unable to connect to the database. Please try again later.";
            exit();
        }
    }

    public function test_print($s)
    {
        if (isset($_COOKIE['test']) && $_COOKIE['test'] == 'x') {
            return;
        }

        $print_t = (isset($_REQUEST['test']) || isset($_COOKIE['test'])) ? true : false;

        if ($print_t && gettype($s) == 'string') {
            echo "\n<br>\n$s";
        } elseif ($print_t) {
            echo "\n<br>\n";
            print_r($s);
        }
    }

    public function disableFullGroupByMode($sqlQuery)
    {
        // if the query contains "GROUP BY", disable ONLY_FULL_GROUP_BY, strtoupper() is for case insensitive
        if (strpos(strtoupper($sqlQuery), 'GROUP BY') !== false && !$this->groupByModeDisabled) {
            try {
                // More precise SQL mode modification
                $this->db->exec("SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY',''))");
                $this->groupByModeDisabled = true;
            } catch (PDOException $e) {
                // Log error but don't fail the query
                error_log("Failed to disable ONLY_FULL_GROUP_BY: " . $e->getMessage());
            }
        }
    }

    public function fetchQuery($sqlQuery, $params = null)
    {
        try {
            // $this->test_print($sqlQuery);

            $this->disableFullGroupByMode($sqlQuery);

            $q = $this->db->prepare($sqlQuery);
            if ($params) {
                $q->execute($params);
            } else {
                $q->execute();
            }

            // Fetch the results if it's a SELECT query
            $result = $q->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // echo "SQL Error:" . $e->getMessage() . "<br>" . $sqlQuery;
            error_log("SQL Error: " . $e->getMessage() . " | Query: " . $sqlQuery);
            return [];
        }
    }

    public function execute_query($sqlQuery, $params = null)
    {
        try {
            $this->disableFullGroupByMode($sqlQuery);

            $q = $this->db->prepare($sqlQuery);
            if ($params) {
                $q->execute($params);
            } else {
                $q->execute();
            }

            // Check if the query starts with "SELECT"
            $query_type = strtoupper(substr(trim((string) $sqlQuery), 0, 6));
            if ($query_type === 'SELECT') {
                // Fetch the results if it's a SELECT query
                $result = $q->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } else {
                // Otherwise, return null
                return [];
            }
        } catch (PDOException $e) {
            echo "sql error:" . $e->getMessage() . "<br>" . $sqlQuery;
            return false;
        }
    }

    public function __destruct()
    {
        $this->db = null;
    }
}

function create_apcu_key($sqlQuery, $params)
{
    if (empty($sqlQuery)) {
        return "!empty_sql_query";
    }
    // Serialize the parameters to create a unique cache key
    $params_string = is_array($params) ? json_encode($params) : '';

    return 'apcu_' . md5($sqlQuery . $params_string);
}

function get_from_apcu($sqlQuery, $params)
{
    $cache_key = create_apcu_key($sqlQuery, $params);

    $items = [];

    if (apcu_exists($cache_key)) {
        $items = apcu_fetch($cache_key);

        if (empty($items)) {
            apcu_delete($cache_key);
            $items = false;
        }
    }

    return $items;
}

function add_to_apcu($sqlQuery, $params, $results)
{
    $cache_key = create_apcu_key($sqlQuery, $params);

    $cache_ttl = 3600 * 12;

    apcu_store($cache_key, $results, $cache_ttl);
}

function fetch_query_new($sqlQuery, $params, $get)
{
    if ($get != 'settings' && isset($_REQUEST['apcu'])) {
        $in_apcu = get_from_apcu($sqlQuery, $params);

        if ($in_apcu && is_array($in_apcu)) {
            return [$in_apcu, "apcu"];
        }
    }

    // Create a new database object
    $db = new Database();

    // Execute a SQL query
    $results = $db->fetchQuery($sqlQuery, $params);

    // Destroy the database object
    $db = null;

    if ($get != 'settings' && isset($_REQUEST['apcu'])) {
        if ($results) {
            add_to_apcu($sqlQuery, $params, $results);
        }
    }

    return [$results, "db"];
}
