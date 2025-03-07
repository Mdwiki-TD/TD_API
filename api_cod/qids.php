<?php

namespace API\Qids;
/*
Usage:
use function API\Qids\qids_qua;
*/

function qids_qua($get)
{
    // ---
    $valid_tables = ["qids", "qids_others"];
    // ---
    if (!in_array($get, $valid_tables)) {
        $get = "qids";
    }
    // ---
    $quaries = [
        'empty' => "select id, title, qid from xx where qid = ''",
        'all' => "select id, title, qid from xx",
        'duplicate' => <<<SQL
            SELECT
            A.id AS id, A.title AS title, A.qid AS qid,
            B.id AS id2, B.title AS title2, B.qid AS qid2
        FROM
            xx A
        JOIN
            xx B ON A.qid = B.qid
        WHERE
            A.qid != '' AND A.title != B.title AND A.id != B.id
        SQL
    ];
    //---
    $dis = filter_input(INPUT_GET, 'dis', FILTER_SANITIZE_SPECIAL_CHARS);
    //---
    $qua = $quaries[$dis] ?? $quaries['all'];
    //---
    $qua = str_replace("xx", $get, $qua);
    //---
    return $qua;
}
