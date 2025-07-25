<?php

namespace API\SQL;
/*
Usage:
use function API\SQL\fetch_query;
*/

if (isset($_REQUEST['test']) || isset($_COOKIE['test'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
};


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

use PDO;
use PDOException;

class Database
{

    private $db;
    private $host;
    private $user;
    private $password;
    private $dbname;
    private $db_suffix;
    private $groupByModeDisabled = false;

    public function __construct($server_name, $db_suffix = 'mdwiki')
    {
        if (empty($db_suffix)) {
            $db_suffix = 'mdwiki';
        }
        // ---
        $this->db_suffix = $db_suffix;
        $this->set_db($server_name);
    }

    private function set_db($server_name)
    {
        if ($server_name === 'localhost' || !getenv('HOME')) {
            $this->host = 'localhost:3306';
            $this->dbname = $this->db_suffix;
            $this->user = 'root';
            $this->password = 'root11';
        } else {
            $ts_pw = posix_getpwuid(posix_getuid());
            $ts_mycnf = parse_ini_file($ts_pw['dir'] . "/confs/db.ini");
            $this->host = 'tools.db.svc.wikimedia.cloud';
            $this->dbname = $ts_mycnf['user'] . "__" . $this->db_suffix;
            $this->user = $ts_mycnf['user'];
            $this->password = $ts_mycnf['password'];
            unset($ts_mycnf, $ts_pw);
        }

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

    public function disableFullGroupByMode($sql_query)
    {
        // if the query contains "GROUP BY", disable ONLY_FULL_GROUP_BY, strtoupper() is for case insensitive
        if (strpos(strtoupper($sql_query), 'GROUP BY') !== false && !$this->groupByModeDisabled) {
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
    public function execute_query($sql_query, $params = null)
    {
        try {
            $this->disableFullGroupByMode($sql_query);

            $q = $this->db->prepare($sql_query);
            if ($params) {
                $q->execute($params);
            } else {
                $q->execute();
            }

            // Check if the query starts with "SELECT"
            $query_type = strtoupper(substr(trim((string) $sql_query), 0, 6));
            if ($query_type === 'SELECT') {
                // Fetch the results if it's a SELECT query
                $result = $q->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } else {
                // Otherwise, return null
                return [];
            }
        } catch (PDOException $e) {
            echo "sql error:" . $e->getMessage() . "<br>" . $sql_query;
            return false;
        }
    }

    public function fetchquery($sql_query, $params = null)
    {
        try {
            // $this->test_print($sql_query);

            $this->disableFullGroupByMode($sql_query);

            $q = $this->db->prepare($sql_query);
            if ($params) {
                $q->execute($params);
            } else {
                $q->execute();
            }

            // Fetch the results if it's a SELECT query
            $result = $q->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            // echo "SQL Error:" . $e->getMessage() . "<br>" . $sql_query;
            error_log("SQL Error: " . $e->getMessage() . " | Query: " . $sql_query);
            return [];
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
    apcu_store($cache_key, $results, $cache_ttl);
}

function get_dbname($table_name)
{
    // ---
    $dbname = 'mdwiki';
    // ---
    $gets_new_db = ["missing", "missing_qids", "publish_reports", "login_attempts", "logins", "publish_reports_stats"];
    // ---
    if (in_array($table_name, $gets_new_db)) {
        $dbname = 'mdwiki_new';
    }
    // ---
    return $dbname;
}

function fetch_query_new($sql_query, $params, $get)
{
    if ($get != 'settings' && isset($_REQUEST['apcu'])) {
        $in_apcu = get_from_apcu($sql_query, $params);
        // ---
        if ($in_apcu && is_array($in_apcu) && !empty($in_apcu)) {
            return [$in_apcu, "apcu"];
        }
    }
    // ---
    $dbname = get_dbname($get);
    // ---
    // Create a new database object
    $db = new Database($_SERVER['SERVER_NAME'] ?? '', $dbname);

    // Execute a SQL query
    $results = $db->fetchquery($sql_query, $params);

    // Destroy the database object
    $db = null;

    if ($get != 'settings' && isset($_REQUEST['apcu'])) {
        if ($results && !empty($results)) {
            add_to_apcu($sql_query, $params, $results);
        }
    }

    return [$results, "db"];
}
