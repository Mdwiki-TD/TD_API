<?php

namespace API\SQL;

use App\Database\Database;

if (!extension_loaded('apcu') || !function_exists('apcu_exists')) {
    function apcu_exists($key)
    {
        return false;
    }
    function apcu_fetch($key)
    {
        return false;
    }
    /** @return bool */
    function apcu_store($key, $value, $ttl = 0)
    {
        $_SERVER['_apcu_dummy'] = true;
        return false;
    }
    /** @return bool */
    function apcu_delete($key)
    {
        $_SERVER['_apcu_dummy'] = true;
        return false;
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
