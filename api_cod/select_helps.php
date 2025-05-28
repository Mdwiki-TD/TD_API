<?php

namespace API\SelectHelps;
/*
Usage:
use function API\SelectHelps\get_select;
*/

function get_select($supported_params)
{
    // ---
    // $SELECT = (isset($_GET['select'])) ? filter_input(INPUT_GET, 'select', FILTER_SANITIZE_SPECIAL_CHARS) : '*';
    $SELECT = (isset($_GET['select']) && $_GET['select'] != 'false' && $_GET['select'] != '0') ? $_GET['select'] : '*';
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
    ];
    // ---
    $select_alias = [
        "count" => "*, count(*) as count",
        "count(*)" => "count(*) as count",
    ];
    // ---
    $SELECT = $select_alias[strtolower($SELECT)] ?? $SELECT;
    // ---
    if (!in_array($SELECT, $select_valids) && !in_array($SELECT, $supported_params)) {
        $SELECT = '*';
    };
    // ---
    // if (isset($_GET['select']) && strtolower($_GET['select']) == 'count(*)') $SELECT = 'COUNT(*) as count';
    // ---
    return $SELECT;
}
