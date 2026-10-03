<?php
// src/app/APIController.php

namespace App;

use App\Database\Database;

/*
index.php
  → bootstrap (env, autoload)
  → APIController::handleRequest()
        1. Request            ← Reads $_GET once
        2. EndpointConfig     ← Parses JSON + resolves redirects
        3. EndpointContext    ← Resolves get, params, columns, select, distinct, group
        4. EndpointRegistry   ← Resolves the appropriate Handler
        5. Handler->handle()  ← Returns a QuerySpec (query, params, error, skipOrder?)
        6. QueryExecutor      ← Applies order/limit/offset + Cache + Database
        7. Formatter          ← Applies custom formatting (leaderboard, langs)
        8. ResponseBuilder    ← Builds metadata (time, source, length, supported_params)
        9. JsonResponse       ← Outputs JSON response
*/

class APIController
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? new Database();
    }
    /**
     * Main entry point: load data, build rows, render the template.
     */
    public function handleRequest(): void
    {
        if ($this->db->isDbNull()) {
            error_log("Database is null");
        }
        // TODO: implement
    }
}
