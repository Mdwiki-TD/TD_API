<?php

namespace API\SelectHelps;
/*
Usage:
use function API\SelectHelps\get_select;
*/

use function API\Helps\filter_order;

function get_select($endpoint_params, $endpoint_columns)
{
    // ---
    $false_selects = [
        'false',
        '0',
        'select',
    ];
    // ---
    // $SELECT = (isset($_GET['select'])) ? filter_input(INPUT_GET, 'select', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '*';
    $SELECT = (isset($_GET['select']) && !in_array($_GET['select'], $false_selects)) ? $_GET['select'] : '*';
    // ---
    if ($SELECT == '*') {
        return '*';
    }
    // ---
    $select_valids = [
        'count',
        'count(*) as count',
        'count(title) as count',
        'count(p.title) as count',
        'year(date) as year',
        'year(p.date) as year',
        'year(pupdate) as year',
        'year(p.pupdate) as year',
        'lang',
        'p.lang',
        'p.user',
        'user',
        'g_title',
    ];
    // ---
    $select_alias = [
        "count(*)" => "count(*) as count",
        "year" => "year(pupdate) as year",
    ];
    // ---
    $select_lower = strtolower($SELECT);
    // ---
    $SELECT = $select_alias[$select_lower] ?? $SELECT;
    // ---
    $supported_params = array_column($endpoint_params, "name");
    // ---
    $params_key_to_data = array_column($endpoint_params, null, 'name');
    // ---
    $select_options = $params_key_to_data["select"]["options"] ?? [];
    // ---
    if (
        !in_array($select_lower, $select_valids) &&
        !in_array($select_lower, $supported_params) &&
        !in_array($select_lower, $select_options) &&
        !in_array($select_lower, $endpoint_columns)
    ) {
        $SELECT = '*';
    };
    // ---
    $count = isset($_GET['count']) ? filter_input(INPUT_GET, 'count', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : false;
    // ---
    if ($count == '*' || in_array($count, $endpoint_columns)) {
        $SELECT .= ", COUNT($count) as count";
    }
    // ---
    return $SELECT;
}
