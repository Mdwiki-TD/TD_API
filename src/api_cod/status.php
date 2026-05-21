<?php

namespace API\Status;
/*
Usage:
use function API\Status\make_status_query;
*/

use function API\Helps\sanitize_input;

function make_status_query($endpoint_params)
{
    // https://mdwiki.toolforge.org/api.php?get=status&year=2022&user_group=Wiki&campaign=Main

    $qu_ery = <<<SQL
        SELECT LEFT(p.pupdate, 7) as date, COUNT(*) as count
        FROM pages p

        LEFT JOIN users u
            ON p.user = u.username

        WHERE p.target != ''

    SQL;

    $pa_rams = [];

    $year = sanitize_input($_GET['year'] ?? '', '/^\d+$/');

    if ($year !== null) {
        $added = $year;
        $qu_ery .= " AND YEAR(p.pupdate) = ?";
        $pa_rams[] = $added;
    }
    $user_group = sanitize_input($_GET['user_group'] ?? '', '/^[a-zA-Z ]+$/');
    if ($user_group !== null) {
        // $qu_ery .= " AND p.user IN (SELECT username FROM users WHERE user_group = ?)";
        $qu_ery .= " AND u.user_group = ?";
        $pa_rams[] = $user_group;
    }

    $campaign   = sanitize_input($_GET['campaign'] ?? '', '/^[a-zA-Z ]+$/');
    $category   = sanitize_input($_GET['cat'] ?? '', '/^[a-zA-Z ]+$/');

    if ($category !== null) {
        $qu_ery .= " AND p.cat = ?";
        $pa_rams[] = $category;
    } elseif ($campaign !== null) {
        $qu_ery .= " AND p.cat IN (SELECT category FROM categories WHERE campaign = ?)";
        $pa_rams[] = $campaign;
    }

    $qu_ery .= <<<SQL
        GROUP BY 1
        ORDER BY 1 ASC;
    SQL;

    return [$qu_ery, $pa_rams];
}
