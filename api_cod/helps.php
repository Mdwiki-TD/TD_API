<?php

namespace API\Helps;
/*
Usage:
use function API\Helps\sanitize_input;
use function API\Helps\add_order;
use function API\Helps\add_group;
use function API\Helps\add_limit;
use function API\Helps\add_offset;
use function API\Helps\add_li_params;
use function API\Helps\add_li_params_o;
use function API\Helps\add_array_params;
*/

function sanitize_input($input, $pattern)
{
    if (!empty($input) && preg_match($pattern, $input) && $input !== "all") {
        return filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
    return null;
}

function add_group($qua)
{
    if (isset($_GET['group'])) {
        $added = filter_input(INPUT_GET, 'group', FILTER_SANITIZE_SPECIAL_CHARS);
        $qua .= " GROUP BY $added";
    }
    return $qua;
}
function add_order($qua)
{
    $orders = [
        "pupdate_or_add_date" => "GREATEST(UNIX_TIMESTAMP(pupdate), UNIX_TIMESTAMP(add_date))",
    ];
    // ---
    if (isset($_GET['order'])) {
        $added = filter_input(INPUT_GET, 'order', FILTER_SANITIZE_SPECIAL_CHARS);
        // ---
        $added = $orders[$added] ?? $added;
        // ---
        $qua .= " ORDER BY $added DESC";
    }
    return $qua;
}
function add_offset($qua)
{
    // if $qua has OFFSET then return
    if (strpos($qua, 'OFFSET') !== false || strpos($qua, 'offset') !== false) return $qua;
    if (isset($_GET['offset'])) {
        $added = filter_input(INPUT_GET, 'offset', FILTER_SANITIZE_SPECIAL_CHARS);
        $added = (int) $added;
        if ($added > 0) {
            $qua .= " OFFSET $added";
        }
    }
    return $qua;
}
function add_limit($qua)
{
    // if $qua has LIMIT then return
    if (strpos($qua, 'LIMIT') !== false || strpos($qua, 'limit') !== false) return $qua;
    if (isset($_GET['limit'])) {
        $added = filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_SPECIAL_CHARS);
        $added = (int) $added;
        if ($added > 0) {
            $qua .= " LIMIT $added";
        }
    }
    return $qua;
}

function add_distinct($qua)
{
    $qua = preg_replace("/^\s*SELECT\s*/i", "SELECT DISTINCT ", $qua);
    return $qua;
}

function add_one_param($qua, $column, $added, $tabe)
{
    // ---
    $add_str = "";
    $params = [];
    // ---
    $where_or_and = (strpos(strtoupper($qua), 'WHERE') !== false) ? ' AND ' : ' WHERE ';
    // ---
    if ($added == "not_mt" || $added == "not_empty") {
        $add_str = " $where_or_and ($column != '' AND $column IS NOT NULL) ";
        // ---
    } elseif ($added == "mt" || $added == "empty") {
        $add_str = " $where_or_and ($column = '' OR $column IS NULL) ";
        // ---
    } elseif ($added == ">0" || $added == "&#62;0") {
        $add_str = " $where_or_and $column > 0 ";
        // ---
    } elseif ($tabe['type'] ?? '' == 'array') {
        list($add_str, $params) = add_array_params($add_str, $params, $tabe['name'], $column, $where_or_and);
    } else {
        $params[] = $added;
        $add_str = " $where_or_and $column = ? ";
        // ---
        $value_can_be_null = isset($tabe['value_can_be_null']) ? $tabe['value_can_be_null'] : false;
        // ---
        if ($value_can_be_null) {
            $add_str = " $where_or_and ($column = ? OR $column IS NULL OR $column = '') ";
        }
    }
    // ---
    return [$add_str, $params];
}

function change_types($types, $endpoint_params, $ignore_params)
{
    // ---
    // $types = array_flip($types);
    // ---
    $types2 = [];
    // ---
    foreach ($types as $type) {
        $types2[$type] = ["column" => $type];
    }
    // ---value_can_be_null
    $types = $types2;
    // ---
    if (count($types) == 0 && count($endpoint_params) > 0) {
        foreach ($endpoint_params as $param) {
            // { "name": "title", "column": "w_title", "type": "text", "placeholder": "Page Title" },
            // , "no_select": true
            if (isset($param['no_select'])) continue;
            $types[$param['name']] = $param;
        }
    }
    // ---
    foreach ($ignore_params as $param) {
        if (isset($types[$param])) unset($types[$param]);
    }
    // ---
    return $types;
}
function add_array_params($qua, $params, $param = "titles", $column = "title", $where_or_and = "")
{
    // ---
    // list($query, $params) = add_array_params($query, $params, 'titles', 'title', "AND");
    // ---
    if (empty($where_or_and)) {
        $where_or_and = (strpos(strtoupper($qua), 'WHERE') !== false) ? ' AND ' : ' WHERE ';
    }
    // ---
    $titles = $_GET[$param] ?? [];
    // ---
    if (!empty($titles) && is_array($titles)) {
        // ---
        $placeholders = rtrim(str_repeat('?,', count($titles)), ',');
        // ---
        $qua .= " $where_or_and $column IN ($placeholders)";
        // ---
        $params = array_merge($params, $titles);
    }
    // ---
    return [$qua, $params];
}

function add_li_params(string $qua, array $types, array $endpoint_params = [], array $ignore_params = []): array
{
    $types = change_types($types, $endpoint_params, $ignore_params);
    // ---
    $params = [];
    // ---
    foreach ($types as $type => $tabe) {
        // ---
        $column = $tabe['column'];
        // ---
        if (empty($column)) continue;
        // ---
        if (isset($_GET[$type]) || isset($_GET[$column])) {
            // ---
            // filter input
            $added = filter_input(INPUT_GET, $type, FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
            $added = (!empty($added)) ? $added : filter_input(INPUT_GET, $column, FILTER_SANITIZE_SPECIAL_CHARS);
            // ---
            // if "limit" in endpoint_params remove it
            if ($column == "limit" || $column == "select" || strtolower($added) == "all") {
                continue;
            }
            // ---
            if (isset($tabe['no_empty_value']) && empty($added)) {
                continue;
            }
            // ---
            if ($column == "distinct" && $added == "1") {
                if (strpos(strtolower($qua), 'distinct') === false) {
                    $qua = add_distinct($qua);
                }
            } else {
                list($add_str, $new_params) = add_one_param($qua, $column, $added, $tabe);
                // ---
                $params = array_merge($params, $new_params);
                // ---
                $qua .= $add_str;
            }
        }
    }
    // ---
    return [$qua, $params];
}

function add_li_params_o(string $qua, array $types, array $endpoint_params = [], array $ignore_params = []): array
{
    // list($qua, $params) = add_li_params_o(
    // ---
    list($qua, $params) = add_li_params($qua, $types, $endpoint_params, $ignore_params);
    // ---
    return [$qua, $params];
}
