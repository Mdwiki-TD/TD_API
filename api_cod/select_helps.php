<?php

namespace API\SelectHelps;
/*
Usage:
use function API\SelectHelps\get_select;
*/

function get_select($endpoint_params)
{
    // ---
    $false_selects = [
        'false',
        '0',
        'select',
    ];
    // ---
    // $SELECT = (isset($_GET['select'])) ? filter_input(INPUT_GET, 'select', FILTER_SANITIZE_SPECIAL_CHARS) : '*';
    $SELECT = (isset($_GET['select']) && !in_array($_GET['select'], $false_selects)) ? $_GET['select'] : '*';
    // ---
    if ($SELECT == '*') {
        return '*';
    }
    // ---
    $select_valids = [
        'count',
        'COUNT(*) as count',
        'count(*) as count',
        'count(title) as count',
        'count(p.title) as count',
        'YEAR(date) AS year',
        'YEAR(p.date) AS year',
        'YEAR(pupdate) AS year',
        'YEAR(p.pupdate) AS year',
        'lang',
        'p.lang',
        'p.user',
        'user',
        'g_title',
        'campaign',
    ];
    // ---
    $select_alias = [
        "count" => "*, count(*) as count",
        "count(*)" => "count(*) as count",
    ];
    // ---
    $SELECT = $select_alias[strtolower($SELECT)] ?? $SELECT;
    // ---
    $supported_params = array_column($endpoint_params, "name");
    // ---
    $select_options = [];
    // ---
    foreach ($endpoint_params as $param) {
        // ---
        if ($param["name"] == "select") {
            $select_options = $param["options"] ?? [];
            break;
        }
    }
    // ---
    if (!in_array($SELECT, $select_valids) && !in_array($SELECT, $supported_params) && !in_array($SELECT, $select_options)) {
        $SELECT = '*';
    };
    // ---
    // if (isset($_GET['select']) && strtolower($_GET['select']) == 'count(*)') $SELECT = 'COUNT(*) as count';
    // ---
    return $SELECT;
}
