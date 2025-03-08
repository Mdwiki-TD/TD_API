<?php

namespace API\Pages;
/*

Usage:
use function API\Pages\get_pages_qua;

*/

use function API\Helps\add_li;
use function API\Helps\add_group;
use function API\Helps\add_order;
use function API\Helps\add_limit;

$title_not_in_pages = (isset($_GET['title_not_in_pages'])) ? true : false;

function get_pages_qua($get, $DISTINCT, $SELECT)
{
    // ---
    global $title_not_in_pages;
    // ---
    $qua = "SELECT $DISTINCT $SELECT FROM $get";
    // ---
    $qua = add_li($qua, ['lang', 'user', 'translate_type', 'cat', 'title', 'YEAR(date)', 'target', 'pupdate']);
    // ---
    if ($title_not_in_pages) {
        $qua .= " and title not in (select p.title from pages p WHERE p.lang = lang and p.target != '') ";
    }
    // ---
    $qua = add_group($qua);
    $qua = add_order($qua);
    // ---
    return $qua;
}
