<?php

$env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'development');

if ($env === 'development' && file_exists(__DIR__ . '/load_env.php')) {
    include_once __DIR__ . '/load_env.php';
}
include_once __DIR__ . '/api_cod/helps.php';
include_once __DIR__ . '/api_cod/sql.php';
include_once __DIR__ . '/api_cod/langs/interwiki.php';
include_once __DIR__ . '/api_cod/langs/lang_pairs.php';
include_once __DIR__ . '/api_cod/langs/site_matrix.php';
include_once __DIR__ . '/api_cod/select_helps.php';
include_once __DIR__ . '/api_cod/qids.php';
include_once __DIR__ . '/api_cod/leaderboard.php';
include_once __DIR__ . '/api_cod/status.php';
include_once __DIR__ . '/api_cod/subs/titles_infos.php';
include_once __DIR__ . '/api_cod/subs/missing_exists.php';
include_once __DIR__ . '/api_cod/subs/top.php';
