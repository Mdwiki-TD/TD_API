<?php

namespace API\Leaderboard;
/*
Usage:
use function API\Leaderboard\leaderboard_table_format;
*/

function leaderboard_table_format($data)
{
    // ---
    $result = [
        "by_lang" => [],
        "by_user" => [],
        "by_month" => [],
        "by_page" => $data
    ];
    // ---
    foreach ($data as $Key => $teb) {
        // $title  = $teb['title'] ?? "";
        // $cat    = $teb['cat'] ?? "";
        //---
        $month  = $teb['m'] ?? ""; // 2021-05
        //---
        if (!isset($result['by_month'][$month])) $result['by_month'][$month] = 0;
        $result['by_month'][$month] += 1;
        //---
        // $target = $teb['target'] ?? "";
        $lang   = $teb['lang'] ?? "";
        $user   = $teb['user'] ?? "";
        //---
        $word   = isset($teb['word']) ? intval($teb['word']) : 0;
        $views  = isset($teb['views']) ? intval($teb['views']) : 0;
        //---
        if (!isset($result['by_lang'][$lang])) {
            $result['by_lang'][$lang] = ["pages" => 0, "words" => 0, "views" => 0];
        }
        $result['by_lang'][$lang]["pages"] += 1;
        $result['by_lang'][$lang]["views"] += $views;
        $result['by_lang'][$lang]["words"] += $word;

        if (!isset($result['by_user'][$user])) {
            $result['by_user'][$user] = ["pages" => 0, "words" => 0, "views" => 0];
        }
        $result['by_user'][$user]["pages"] += 1;
        $result['by_user'][$user]["views"] += $views;
        $result['by_user'][$user]["words"] += $word;
    }
    // ---
    return $result;
}
