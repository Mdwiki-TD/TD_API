<?php

namespace API\Helps;
/*
Usage:
use function API\Helps\sanitize_input;
use function API\Helps\add_order;
use function API\Helps\add_group;
use function API\Helps\add_limit;
use function API\Helps\add_li;
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

function add_li(string $qua, array $types): string
{
    // ---
    $not_empty_keys = ['target_notempty' => 'target'];
    $empty_keys = ['target_empty' => 'target'];

    // ---
    foreach ($types as $type) {
        if (isset($_GET[$type])) {
            // filter input
            $added = filter_input(INPUT_GET, $type, FILTER_SANITIZE_SPECIAL_CHARS);
            // ---
            $where_or_and = (strpos($qua, 'WHERE') !== false) ? ' AND ' : ' WHERE ';
            // ---
            // $add_str = " $where_or_and $type = `$added` ";
            $add_str = " $where_or_and $type = '$added' ";
            // ---
            if ($added == "not_mt" || $added == "not_empty") {
                $add_str = " $where_or_and ($type != '' AND $type IS NOT NULL) ";
            }
            // ---
            if ($added == "mt" || $added == "empty") {
                $add_str = " $where_or_and ($type = '' OR $type IS NULL) ";
            }
            // ---
            if (isset($not_empty_keys[$type])) {
                $key2 = $not_empty_keys[$type];
                $add_str = " $where_or_and $key2 != '' ";
            }
            // ---
            if (isset($empty_keys[$type])) {
                $key2 = $empty_keys[$type];
                $add_str = " $where_or_and $key2 = '' ";
            }
            // ---
            $qua .= $add_str;
            // ---
        }
    }
    return $qua;
}
