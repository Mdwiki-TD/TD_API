<?php

namespace API\SQL;
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

    public function fetch_query($sql_query, $params = null)
    {
        try {
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
            // echo "sql error:" . $e->getMessage() . "<br>" . $sql_query;
            error_log("SQL Error: " . $e->getMessage() . " | Query: " . $sql_query);
            return array();
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
    $gets_new_db = ["missing", "missing_qids"];
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
