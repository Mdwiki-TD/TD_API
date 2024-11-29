<?php

if (!isset($_GET['get'])) {
    // header('Location: test/index.php');
    include_once __DIR__ . '/test/index.php';
    exit();
}

include_once __DIR__ . '/api_cod/request.php';
