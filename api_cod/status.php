<?php

namespace API\Status;
/*
Usage:
use function API\Status\make_status_query;
*/

use function API\Helps\sanitize_input;

function make_status_query()
{
    // https://mdwiki.toolforge.org/api.php?get=status&year=2022&user_group=Wiki&campaign=Main

    $qu_ery = <<<SQL
        SELECT LEFT(p.pupdate, 7) as date, COUNT(*) as count
        FROM pages p
        WHERE p.target != ''
    SQL;

    $pa_rams = [];

    $year       = sanitize_input($_GET['year'] ?? '', '/^\d+$/');
    $user_group = sanitize_input($_GET['user_group'] ?? '', '/^[a-zA-Z ]+$/');
    $campaign   = sanitize_input($_GET['campaign'] ?? '', '/^[a-zA-Z ]+$/');

    if ($year !== null) {
        $added = $year;
        $qu_ery .= " AND YEAR(p.pupdate) = ?";
        $pa_rams[] = $added;
    }

    if ($user_group !== null) {
        $qu_ery .= " AND p.user IN (SELECT username FROM users WHERE user_group = ?)";
        $pa_rams[] = $user_group;
    }

    if ($campaign !== null) {
        $qu_ery .= " AND p.cat IN (SELECT category FROM categories WHERE campaign = ?)";
        $pa_rams[] = $campaign;
    }

    $qu_ery .= <<<SQL
        GROUP BY LEFT(p.pupdate, 7)
        ORDER BY LEFT(p.pupdate, 7) ASC;
    SQL;

    return ["qua" => $qu_ery, "params" => $pa_rams];
}
