<?php

namespace API\Helps;
/*
Usage:
use function API\Helps\sanitize_input;
use function API\Helps\add_order;
use function API\Helps\add_group;
use function API\Helps\add_limit;
use function API\Helps\add_li;
use function API\Helps\add_li_params;
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
    if (isset($_GET['order'])) {
        $added = filter_input(INPUT_GET, 'order', FILTER_SANITIZE_SPECIAL_CHARS);
        $qua .= " ORDER BY $added DESC";
    }
    return $qua;
}
function add_limit($qua)
{
    if (isset($_GET['limit'])) {
        $added = filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_SPECIAL_CHARS);
        $qua .= " LIMIT $added";
    }
    return $qua;
}

function add_li(string $qua, array $types, array $endpoint_params = []): string
{
    // ---
    // $not_empty_keys = ['target_notempty' => 'target'];
    // $empty_keys = ['target_empty' => 'target'];
    // ---
    $types = array_flip($types);
    // ---
    // $types2 = [];
    // // ---
    // foreach ($types as $type) {
    //     $types2[$type] = $type;
    // }
    // // ---
    // $types = $types2;
    // ---
    if (count($types) == 0 && count($endpoint_params) > 0) {
        foreach ($endpoint_params as $param) {
            // { "name": "title", "column": "w_title", "type": "text", "placeholder": "Page Title" },
            $types[$param['name']] = $param['column'];
        }
    }
    // ---
    foreach ($types as $type => $column) {
        if (isset($_GET[$type]) || isset($_GET[$column])) {
            // filter input
            $added = filter_input(INPUT_GET, $type, FILTER_SANITIZE_SPECIAL_CHARS) ?? filter_input(INPUT_GET, $column, FILTER_SANITIZE_SPECIAL_CHARS);
            // ---
            $where_or_and = (strpos($qua, 'WHERE') !== false) ? ' AND ' : ' WHERE ';
            // ---
            if ($added == "not_mt" || $added == "not_empty") {
                $add_str = " $where_or_and ($column != '' AND $column IS NOT NULL) ";
            } elseif ($added == "mt" || $added == "empty") {
                $add_str = " $where_or_and ($column = '' OR $column IS NULL) ";
            } elseif ($added == ">0" || $added == "&#62;0") {
                $add_str = " $where_or_and $column > 0 ";
            } else {
                // $add_str = " $where_or_and $column = `$added` ";
                $add_str = " $where_or_and $column = '$added' ";
            }
            // ---
            /*
            if (isset($not_empty_keys[$column])) {
                $key2 = $not_empty_keys[$column];
                $add_str = " $where_or_and $key2 != '' ";
            }
            // ---
            if (isset($empty_keys[$column])) {
                $key2 = $empty_keys[$column];
                $add_str = " $where_or_and $key2 = '' ";
            }
                */
            // ---
            $qua .= $add_str;
            // ---
        }
    }
    return $qua;
}

function add_li_params(string $qua, array $types, array $endpoint_params = []): array
{
    // ---
    $types = array_flip($types);
    // ---
    $params = [];
    // ---
    if (count($types) == 0 && count($endpoint_params) > 0) {
        foreach ($endpoint_params as $param) {
            // { "name": "title", "column": "w_title", "type": "text", "placeholder": "Page Title" },
            $types[$param['name']] = $param['column'];
        }
    }
    // ---
    foreach ($types as $type => $column) {
        if (isset($_GET[$type]) || isset($_GET[$column])) {
            // filter input
            $added = filter_input(INPUT_GET, $type, FILTER_SANITIZE_SPECIAL_CHARS) ?? filter_input(INPUT_GET, $column, FILTER_SANITIZE_SPECIAL_CHARS);
            // ---
            $where_or_and = (strpos($qua, 'WHERE') !== false) ? ' AND ' : ' WHERE ';
            // ---
            if ($added == "not_mt" || $added == "not_empty") {
                $add_str = " $where_or_and ($column != '' AND $column IS NOT NULL) ";
            } elseif ($added == ">0" || $added == "&#62;0") {
                $add_str = " $where_or_and $column > 0 ";
            } else {
                // $add_str = " $where_or_and $column = `$added` ";
                $params[] = $added;
                $add_str = " $where_or_and $column = ? ";
            }
            // ---
            $qua .= $add_str;
        }
    }
    return ["qua" => $qua, "params" => $params];
}
