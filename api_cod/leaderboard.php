<?php

namespace API\Leaderboard;
/*
Usage:
use function API\Leaderboard\leaderboard_table;
use function API\Leaderboard\leaderboard_table_new;
*/

use function API\Helps\sanitize_input;
use function API\Helps\add_limit;

function leaderboard_table()
{
    // ---
    $pa_rams = [];
    // ---
    $qu_ery = "SELECT p.title,
        p.target, p.cat, p.lang, p.word, YEAR(p.pupdate) AS pup_y, LEFT(p.pupdate, 7) as m,
        p.user,
        (SELECT u.user_group FROM users u WHERE p.user = u.username) AS user_group
        FROM pages p
        WHERE p.target != ''
    ";
    // ---
    $user_group = sanitize_input($_GET['user_group'] ?? '', '/^[a-zA-Z ]+$/');
    // ---
    if ($user_group !== null && $user_group !== 'all') {
        // ---
        $qu_ery = "SELECT p.title,
            p.target, p.cat, p.lang, p.word, YEAR(p.pupdate) AS pup_y, p.user, u.user_group, LEFT(p.pupdate, 7) as m
            FROM pages p, users u
            WHERE p.user = u.username
            AND u.user_group = ?
        ";
        // ---
        $pa_rams[] = $user_group;
    };
    // ---
    $year = sanitize_input($_GET['year'] ?? '', '/^\d+$/');
    // ---
    if ($year !== null) {
        $qu_ery .= " AND YEAR(p.pupdate) = ?";
        $pa_rams[] = $year;
    }
    // ---
    $qu_ery = add_limit($qu_ery);
    // ---
    return ["qua" => $qu_ery, "params" => $pa_rams];
}


function leaderboard_table_new()
{
    // ---
    $pa_rams = [];
    // ---
    $qu_ery = "SELECT p.title,
        p.target, p.cat, p.lang, p.word, YEAR(p.pupdate) AS pup_y, LEFT(p.pupdate, 7) as m,
        p.user,
        u.user_group
        FROM pages p
        LEFT JOIN users u ON p.user = u.username
        WHERE p.target != ''
    ";
    // ---
    $user_group = sanitize_input($_GET['user_group'] ?? '', '/^[a-zA-Z ]+$/');
    // ---
    if ($user_group !== null && $user_group !== 'all') {
        // ---
        $qu_ery .= " AND u.user_group = ?";
        // ---
        $pa_rams[] = $user_group;
    };
    // ---
    $year = sanitize_input($_GET['year'] ?? '', '/^\d+$/');
    // ---
    if ($year !== null) {
        $qu_ery .= " AND YEAR(p.pupdate) = ?";
        $pa_rams[] = $year;
    }
    // ---
    $qu_ery = add_limit($qu_ery);
    // ---
    return ["qua" => $qu_ery, "params" => $pa_rams];
}
