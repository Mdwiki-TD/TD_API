# API Core Engine (`src/api_cod/`)

## Project Overview

The core backend engine of the TD_API (Translation Dashboard API) for the MDWiki Wikimedia medical content translation project. This module implements the main request router, database access layer, query builders, and APCu caching for 40+ REST-like API endpoints serving translation data, user statistics, language information, and content analytics.

### Main Features
- Centralized request routing via switch/case dispatcher (40+ endpoints)
- PDO-based MySQL database access with prepared statements
- APCu in-memory caching with 12-hour TTL and graceful fallback
- Parameterized query builder with input validation and sanitization
- SELECT clause builder with whitelist-based column validation
- Endpoint-specific query modules for complex joins and aggregations
- JSON response formatting with metadata (timing, source, length)

### Technologies
- **Language:** PHP 7.x/8.x (procedural with namespaces)
- **Database:** MySQL via PDO
- **Caching:** APCu (with graceful degradation if unavailable)
- **No framework, no Composer, no external packages**

### PHP Version
- PHP 7.4+ (uses typed properties, null coalescing, Elvis operator)

## Project Structure

```
src/api_cod/
  request.php               # Main router and switch/case dispatcher (512 lines)
  sql.php                   # Database class + APCu caching functions (238 lines)
  helps.php                 # Query builder utilities (308 lines)
  select_helps.php          # SELECT clause builder from endpoint_params (73 lines)
  status.php                # Status endpoint query builder (63 lines)
  leaderboard.php           # Leaderboard data formatting (66 lines)
  qids.php                  # Wikidata QID endpoint queries (42 lines)
  te.php                    # Empty file (dead code)
  subs/
    missing_exists.php      # Missing/exists endpoint queries (264 lines)
    titles_infos.php        # Titles, revids, pages query builders (115 lines)
    top.php                 # Top users/languages query builders (110 lines)
    missing_exists_backup_.php_x  # Dead backup file (328 lines)
```

### Module Responsibilities

| Module | Namespace | Purpose |
|--------|-----------|---------|
| `request.php` | (root) | Main router: reads endpoint config, dispatches to handlers, formats response |
| `sql.php` | `API\SQL` | `Database` class (PDO), `fetch_query_new()` with APCu integration |
| `helps.php` | `API\Helps` | `add_li_params()`, `add_order()`, `add_limit()`, `add_offset()`, `sanitize_input()` |
| `select_helps.php` | `API\SelectHelps` | `get_select()` builds SELECT clause from `$_GET['select']` |
| `status.php` | `API\Status` | `make_status_query()` for the `/status` endpoint |
| `leaderboard.php` | `API\Leaderboard` | `leaderboard_table_format()`, `langs_format()` |
| `qids.php` | `API\Qids` | `qids_qua()` for Wikidata QID endpoints |
| `subs/missing_exists.php` | `API\Missing` | Complex queries for missing/exists article analysis |
| `subs/titles_infos.php` | `API\TitlesInfos` | Queries for titles, revids, and pages data |
| `subs/top.php` | `API\Top` | Queries for top users, languages, and per-user language stats |

## Architecture & Code Quality Review

### Request Flow
```
HTTP GET api.php?get=<endpoint>
  → api.php includes include_all.php (loads all modules)
  → request.php:
      1. Sanitizes $_GET['get'] with FILTER_SANITIZE_FULL_SPECIAL_CHARS
      2. Loads endpoint_params.json configuration
      3. Resolves redirect endpoints (e.g., pages_with_views → pages)
      4. Builds SELECT clause via get_select()
      5. Switch/case dispatches to endpoint handler
      6. Handler builds SQL query (parameterized or raw)
      7. add_order() → add_limit() → add_offset()
      8. fetch_query_new() → APCu check → Database->fetchquery()
      9. JSON response: {time, query, source, length, results, ...}
```

### Two Query-Building Patterns

1. **Parameterized (`$query` + `$params`):** Uses PDO prepared statements with `?` placeholders. Used by most endpoints. **Secure.**
2. **Raw SQL (`$qua`):** Direct SQL string interpolation. Used for endpoints with no user-filtered WHERE clauses. **Fragile but currently safe due to switch/case routing.**

### Design Patterns
- **Router/Dispatcher:** Central switch/case in `request.php`
- **Repository Pattern:** Endpoint-specific query builders in `subs/`
- **Strategy Pattern:** Dual query paths (`$query` vs `$qua`)
- **Cache-Aside:** APCu caching with explicit opt-in via `&apcu` parameter

### SOLID Principles
- **S (Single Responsibility):** Mostly followed. Each module handles one concern (SQL, helpers, specific endpoints).
- **O (Open/Closed):** Partially followed. New endpoints require editing `request.php` switch statement.
- **L (Liskov Substitution):** N/A (no inheritance hierarchy).
- **I (Interface Segregation):** N/A (no interfaces).
- **D (Dependency Inversion):** Not followed. Direct `new Database()` instantiation in `fetch_query_new()`.

### Maintainability
- **Good:** Clear namespace organization, logical file separation, consistent coding style
- **Bad:** Large switch statement in `request.php` (512 lines), no autoloading, dead code

### Readability
- Generally good. Variable naming is consistent within files (though `$qua` vs `$query` distinction is undocumented).
- Complex SQL queries in `subs/` are well-structured with line breaks.

### Scalability
- APCu caching reduces database load significantly
- Connection-per-request model (no pooling) is fine for this scale
- `ONLY_FULL_GROUP_BY` auto-disable could mask query bugs

## Strengths

1. **Parameterized queries (PDO):** Most endpoints use prepared statements with `?` placeholders -- the correct defense against SQL injection
2. **Input validation with regex:** `sanitize_input()` validates against patterns like `/^[A-Za-z0-9-]+$/`
3. **Whitelist-based routing:** Table names validated against `$other_tables` array and `endpoint_params.json`
4. **ORDER BY validation:** `filter_order()` validates against endpoint columns/params
5. **SELECT clause validation:** Whitelist of allowed values with fallback to `*`
6. **APCu caching with fallback:** Graceful degradation if APCu is unavailable
7. **Query stripping in production:** `query` field removed from non-localhost responses
8. **Consistent namespace usage:** All modules use proper PHP namespaces with explicit `use function` imports
9. **Endpoint redirects:** Clean redirect mechanism for endpoint aliases

## Weaknesses

1. **Dead code:** `te.php` (empty), `$qua_old` in `titles_infos.php`, `missing_exists_backup_.php_x`
2. **Inconsistent naming:** Mix of `$qua`/`$query`/`$qu_ery`/`$query_line`
3. **Inconsistent return types:** Some functions return `[$query, $params]`, others `[$query, $params, $error]`
4. **No autoloading:** Manual `include_once` in `include_all.php`
5. **No error handling on JSON decode:** `json_decode(file_get_contents(...))` in `request.php:61`
6. **Large switch statement:** `request.php` has 35+ cases -- would benefit from a routing table
7. **Commented-out code:** Multiple commented blocks throughout `request.php`

## Critical Issues

| Issue | Severity | Location |
|-------|----------|----------|
| SQL error leakage in `execute_query()` | **High** | `sql.php:138` -- echoes full SQL + error message |
| `$_REQUEST['test']` enables `display_errors` in production | **High** | `request.php:3`, `sql.php:9-13` |
| Table name interpolation via `$get` in raw SQL | **Medium** | `request.php:429`, `titles_infos.php:91` |
| Unsanitized `$_GET['select']` before whitelist check | **Medium** | `select_helps.php:19` |
| `$_COOKIE['test']` controls debug output | **Medium** | `sql.php:87` |
| `sprintf` with user-controlled format params | **Low** | `request.php:454` |
| `ONLY_FULL_GROUP_BY` auto-disable masks query bugs | **Low** | `sql.php:49-57` |

## Areas That Need Attention

- **Security hardening:** Remove or restrict `?test` debug mode, fix `execute_query()` error leakage
- **Dead code cleanup:** Remove `te.php`, `$qua_old`, `missing_exists_backup_.php_x`
- **Error handling:** Add JSON decode error checking, file existence checks
- **Testing:** No unit tests exist for any of the query builder functions
- **Documentation:** The `$qua` vs `$query` pattern needs documentation
- **Consistent return types:** Standardize all query builders to return `[$query, $params, $error]`
- **Logging:** No structured logging; errors go to `error_log()` or are echoed

## Improvement Plan

### Quick Fixes
1. Remove `execute_query()` error echoing -- log to `error_log()` instead
2. Gate `?test` debug mode behind an environment check (e.g., `APP_ENV === 'development'`)
3. Delete `te.php` and `missing_exists_backup_.php_x`
4. Add `json_decode` error checking in `request.php`

### Medium-Term Improvements
1. Replace the switch/case in `request.php` with a routing table array
2. Standardize all query builder return types to `[$query, $params, $error]`
3. Add input validation for `$_GET['select']` before any processing
4. Implement a proper `.env` file loader (e.g., `vlucas/phpdotenv`)
5. Add PHPDoc blocks to all public functions

### Long-Term Refactoring Strategy
1. Introduce PSR-4 autoloading with Composer
2. Extract the `Database` class into a standalone service with connection pooling
3. Create an `Endpoint` interface/abstract class for consistent endpoint implementation
4. Add unit tests for all query builder functions
5. Implement request logging and monitoring

### Security Hardening
1. Remove `?test` parameter from production; use environment-based error reporting
2. Add `execute_query()` to a deprecated list or remove it entirely
3. Validate `$get` against an explicit whitelist before SQL interpolation
4. Add rate limiting per IP for unauthenticated requests
5. Implement API key authentication for sensitive endpoints

## Comprehensive Review

| Metric | Score | Notes |
|--------|-------|-------|
| **Overall Rating** | 6.5/10 | Functional and well-organized, but has security gaps and dead code |
| **Production Readiness** | Medium | Works in production, but debug modes need restricting |
| **Security Score** | 6/10 | Good use of prepared statements, but debug leakage and raw SQL paths are risks |
| **Technical Debt** | Medium | Dead code, inconsistent patterns, no tests |
| **Maintainability** | 7/10 | Clear namespace organization, but large switch statement needs refactoring |
| **Risk Assessment** | Medium | The `?test` parameter and `execute_query()` leakage are the highest risks |

## Setup & Usage

### Requirements
- PHP 7.4+ with PDO MySQL extension
- APCu extension (optional, degrades gracefully)
- MySQL database with the MDWiki schema

### Environment Variables
```env
APP_ENV=development|production
DB_HOST_TOOLS=localhost:3306
DB_NAME=s54732__mdwikiz
TOOL_TOOLSDB_USER=<username>
TOOL_TOOLSDB_PASSWORD=<password>
```

### Example Endpoint Calls
```bash
# Basic pages query
GET api.php?get=pages&limit=10

# Users with filtering
GET api.php?get=users&userlike=John&user_group=translator

# Status with year filter
GET api.php?get=status&year=2024

# Leaderboard with APCu caching
GET api.php?get=leaderboard_table&cat=RTT&apcu
```

### Adding a New Endpoint
1. Add configuration to `endpoint_params.json`
2. Add a `case` in `request.php` switch statement (or add to `$other_tables` for simple queries)
3. If complex, create a query builder function in `subs/`
4. Update `openapi.json` with endpoint documentation
