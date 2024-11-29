<?php

if (!isset($_GET['get'])) {
    header('Location: test/index.html');
    exit();
}

include_once __DIR__ . '/api_cod/request.php';
