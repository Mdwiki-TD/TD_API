# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

TD_API is the central data access layer for MDwiki (Wikimedia medical content translation project). It provides a REST-like HTTP API that serves as the primary interface between MySQL databases and frontend applications (Translation Dashboard, Wiki Management Tools).

## Technology Stack

- **Language:** PHP 7.x/8.x (no framework, procedural with namespaces)
- **Database:** MySQL with PDO
- **Caching:** APCu (12-hour TTL)
- **External APIs:** Wikimedia/Wikidata APIs via cURL
- **API Documentation:** OpenAPI 3.0 specification at `openapi.json`
- **Deployment:** GitHub Actions SSH deploy to Toolforge (Wikimedia Cloud)

## Architecture

```
Entry Points:
  api.php / index.php → api_cod/request.php (main router)

Core Files:
  api_cod/request.php      - Main switch/case router (40+ endpoints)
  api_cod/sql.php          - Database class + APCu caching functions
  api_cod/helps.php        - Query builder utilities (add_limit, add_order, add_li_params)
  api_cod/select_helps.php - SELECT clause builder from endpoint_params
  api_cod/include.php      - Module loader

Endpoint-specific modules:
  api_cod/subs/            - Endpoint query functions (missing_exists.php, titles_infos.php, top.php)
  api_cod/langs/           - External API wrappers (interwiki.php, site_matrix.php, lang_pairs.php)

Configuration:
  endpoint_params.json     - Per-endpoint parameter/column definitions
  openapi.json            - OpenAPI 3.0 specification
```

### Request Flow

1. HTTP GET request to `api.php?get=<endpoint>`
2. `request.php` loads endpoint config from `endpoint_params.json`
3. Switch/case dispatches to endpoint handler (inline SQL or function call)
4. Query built via `add_li_params()`, `add_order()`, `add_limit()`, `add_offset()`
5. `fetch_query_new()` checks APCu cache, then queries database
6. JSON response: `{time, query, source, length, results, supported_params}`

## API Endpoints

All endpoints are accessed via `api.php?get=<endpoint>` with optional query parameters.

Key endpoint groups (see `openapi.json` for full specification):
- **pages**: pages, pages_users, pages_by_user_or_lang, pages_with_views
- **users**: users, coordinator, full_translators, top_users, user_status
- **statistics**: status, leaderboard_table, graph_data, count_pages
- **views**: views_new, user_views2, lang_views2
- **languages**: lang_names, lang_names_new, site_matrix, translate_type
- **pages_infos**: titles, words, refs_counts, assessments, revids, missing

Common parameters: `limit` (default 50), `offset`, `order`, `order_direction`, `distinct`, `select`

## Development Commands

### Local Development

The project runs on PHP with MySQL. For local development:
- Database config is loaded from `~/confs/db.ini` (or `$HOME/confs/db.ini`)
- Local server detection uses `$_SERVER['SERVER_NAME'] === 'localhost'`
- Add `?test` parameter to enable error reporting: `api.php?get=pages&test`

### Testing Endpoints

Interactive test UI available at `test.html` or `test/index.php`

Example API calls:
```
api.php?get=pages&limit=10
api.php?get=users&userlike=John
api.php?get=status&year=2024
api.php?get=leaderboard_table&cat=RTT
```

### APCu Caching

Add `&apcu` parameter to enable caching: `api.php?get=pages&apcu`
Cache TTL: 12 hours (3600 * 12 seconds)

## Key Implementation Details

### Adding a New Endpoint

1. Add endpoint configuration to `endpoint_params.json`:
```json
{
  "new_endpoint": {
    "columns": ["col1", "col2"],
    "params": [
      {"name": "param1", "column": "db_column", "type": "text", "placeholder": "Description"}
    ]
  }
}
```

2. Add case in `api_cod/request.php` switch statement (or add to `$other_tables` array for simple SELECT queries)

3. Update `openapi.json` with endpoint documentation

### Query Parameter Handling

Use helper functions from `helps.php`:
- `add_li_params($query, $types, $endpoint_params)` - Add WHERE clauses from GET params
- `add_order($query, $endpoint_data)` - Add ORDER BY
- `add_limit($query)` / `add_offset($query)` - Add LIMIT/OFFSET
- `add_group($query, $endpoint_data)` - Add GROUP BY
- `sanitize_input($input, $pattern)` - Validate input against regex

### Database Access

```php
use function API\SQL\fetch_query_new;

list($results, $source) = fetch_query_new($sql_query, $params, $endpoint_name);
// $source is "db" or "apcu" (cache)
```

### Response Format

```php
$out = [
    "time" => $execution_time,
    "query" => $query_string,  // hidden on production
    "source" => "db"|"apcu",
    "length" => count($results),
    "results" => $results,
    "supported_params" => [],
    "supported_values" => [],
    "columns" => $endpoint_columns
];
```

## Deployment

Pushing to `main` branch triggers GitHub Actions workflow (`.github/workflows/update.yaml`) which SSHs to Toolforge and runs `shs/update_td_api.sh`.
