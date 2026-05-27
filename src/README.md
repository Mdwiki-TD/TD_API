# TD_API Source Directory (`src/`)

## Project Overview

The `src/` directory contains the complete source code for the **TD_API** (Translation Dashboard API) -- a REST-like HTTP API that serves as the primary data access layer for the MDWiki Wikimedia medical content translation project. It provides 40+ endpoints serving translation data, user statistics, language information, and content analytics to frontend applications (Translation Dashboard, Wiki Management Tools).

### Main Features
- 40+ API endpoints for translation data, users, statistics, views, and languages
- PDO-based MySQL database access with prepared statements
- APCu in-memory caching (12-hour TTL) with graceful fallback
- Input validation with regex whitelists and parameterized queries
- OpenAPI 3.0 specification with interactive Swagger UI and ReDoc documentation
- Browser-based API testing interface
- CORS proxy for cross-origin access

### Technologies
- **Language:** PHP 7.4/8.x (procedural with namespaces)
- **Database:** MySQL via PDO
- **Caching:** APCu (optional)
- **External APIs:** Wikimedia/Wikidata APIs via cURL
- **Frontend:** Bootstrap 5, Vanilla JavaScript, jQuery
- **API Documentation:** OpenAPI 3.0 (Swagger UI 4.5.0, ReDoc)
- **No framework, no Composer, no build tools**

### PHP Version
- PHP 7.4+ recommended
- PHP 8.1 compatible (with deprecation warnings for `FILTER_SANITIZE_STRING`)
- PHP 8.2+ will break due to `FILTER_SANITIZE_STRING` removal in `api/proxy.php`

## Project Structure

```
src/
  api.php                   # Primary API entry point (17 lines)
  index.php                 # Duplicate entry point (16 lines)
  include_all.php           # Central dependency loader (20 lines)
  load_env.php              # Development environment variables (9 lines)
  endpoint_params.json      # Per-endpoint parameter/column definitions (33KB)
  openapi.json              # OpenAPI 3.0 specification (61KB)
  openapi.html              # Swagger UI documentation page (53 lines)
  redoc.html                # ReDoc documentation page (19 lines)
  t.php                     # APCu cache inspection/diagnostic tool (16 lines)
  api/
    proxy.php               # CORS reverse proxy (49 lines)
  api_cod/
    request.php             # Main router and switch/case dispatcher (512 lines)
    sql.php                 # Database class + APCu caching (238 lines)
    helps.php               # Query builder utilities (308 lines)
    select_helps.php        # SELECT clause builder (73 lines)
    status.php              # Status endpoint query builder (63 lines)
    leaderboard.php         # Leaderboard data formatting (66 lines)
    qids.php                # Wikidata QID queries (42 lines)
    te.php                  # Empty file (dead code)
    subs/
      missing_exists.php    # Missing/exists endpoint queries (264 lines)
      titles_infos.php      # Titles, revids, pages queries (115 lines)
      top.php               # Top users/languages queries (110 lines)
      missing_exists_backup_.php_x  # Dead backup file (328 lines)
  test/
    index.php               # API test UI entry point (HTML, no PHP)
    script.js               # Test UI application logic (389 lines)
    style.css               # Main stylesheet (244 lines)
    theme.css               # Theme toggle styles (58 lines)
    theme.js                # Theme toggle logic (127 lines)
    endpointGroups.json     # Endpoint group configuration
  test2/
    index.html              # Static API test UI entry point
    script.js               # Test UI v2 logic (550+ lines)
    script.js.backup        # Dead backup file (20KB)
```

### Module Map

```
┌─────────────────────────────────────────────────────────────┐
│                      Entry Points                           │
│  api.php / index.php  →  include_all.php  →  request.php   │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                    Core Engine (api_cod/)                    │
│  request.php  ──→  switch/case dispatcher                   │
│       │                                                      │
│       ├──→ subs/missing_exists.php  (complex queries)       │
│       ├──→ subs/titles_infos.php    (pages/titles)          │
│       ├──→ subs/top.php             (top users/langs)       │
│       ├──→ status.php               (status endpoint)       │
│       ├──→ leaderboard.php          (leaderboard formatting)│
│       ├──→ qids.php                 (Wikidata QIDs)         │
│       │                                                      │
│       ├──→ helps.php       (query building utilities)       │
│       ├──→ select_helps.php (SELECT clause builder)         │
│       └──→ sql.php         (Database + APCu caching)        │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│                    Database (MySQL/PDO)                      │
│  pages, users, langs, categories, views_new_all, words,     │
│  assessments, refs_counts, qids, all_qids_exists, etc.      │
└─────────────────────────────────────────────────────────────┘
```

## Architecture & Code Quality Review

### Request Flow
```
HTTP GET api.php?get=<endpoint>
  → api.php: error reporting (if ?test), redirect to docs (if no ?get)
  → include_all.php: loads load_env.php (if dev), then all modules
  → request.php:
      1. Sanitizes endpoint name
      2. Loads endpoint_params.json
      3. Resolves redirects (e.g., pages_with_views → pages)
      4. Builds SELECT clause
      5. Switch/case dispatches to handler
      6. Builds SQL (parameterized or raw)
      7. Applies ORDER BY, LIMIT, OFFSET
      8. fetch_query_new() → APCu cache → MySQL
      9. JSON response with metadata
```

### Design Patterns
- **Router/Dispatcher:** Switch/case in `request.php`
- **Repository:** Endpoint-specific query builders in `api_cod/subs/`
- **Cache-Aside:** APCu with explicit opt-in (`&apcu` parameter)
- **Proxy:** CORS proxy in `api/proxy.php`

### SOLID Compliance
- **S:** Mostly good -- each module has a focused responsibility
- **O:** Partially -- new endpoints require editing the switch statement
- **L/I/D:** Not applicable (procedural code, no inheritance/interfaces)

## Strengths

1. **Well-organized namespace structure:** `API\SQL`, `API\Helps`, `API\Status`, etc.
2. **Parameterized queries:** Most SQL uses PDO prepared statements
3. **Input validation:** Regex-based sanitization with whitelists
4. **APCu caching:** 12-hour TTL with graceful fallback
5. **Comprehensive OpenAPI spec:** 61KB `openapi.json` with full endpoint documentation
6. **Two documentation UIs:** Swagger UI and ReDoc for different preferences
7. **Interactive test UI:** Browser-based endpoint testing with dynamic form generation
8. **Endpoint redirects:** Clean alias mechanism for endpoint names
9. **Production safety:** Query strings stripped from responses in non-localhost environments

## Weaknesses

1. **Duplicated entry points:** `api.php` and `index.php` are near-identical (maintenance risk)
2. **Dead code:** `te.php`, `missing_exists_backup_.php_x`, `script.js.backup`, commented-out blocks
3. **Inconsistent naming:** `$qua`/`$query`/`$qu_ery` mix; `t.php` is non-descriptive
4. **No autoloading:** Manual `include_once` in `include_all.php`
5. **No unit tests:** Zero automated tests for any module
6. **Mixed JS patterns:** jQuery + vanilla JS in `test/script.js`
7. **Two competing test UIs:** `test/` and `test2/` serve the same purpose

## Critical Issues

| Issue | Severity | Location | Impact |
|-------|----------|----------|--------|
| Hardcoded DB credentials in version control | **Critical** | `load_env.php` | Root user `root:root11` committed to repo |
| `?test` enables `display_errors` in production | **High** | `api.php:5`, `index.php:5` | Any user can trigger verbose error output |
| SQL error leakage in `execute_query()` | **High** | `api_cod/sql.php:138` | Echoes full SQL + error to output |
| `FILTER_SANITIZE_STRING` deprecated/removed | **High** | `api/proxy.php:17` | Breaks on PHP 8.2+ |
| XSS via `innerHTML` in test UIs | **Medium** | `test/script.js:263`, `test2/script.js:157` | Broken escaping in test2 |
| `t.php` exposes cache internals without auth | **Medium** | `t.php` | Anyone can view/clear APCu cache |
| Unsanitized `$_GET['select']` before whitelist | **Medium** | `api_cod/select_helps.php:19` | Depends on whitelist being complete |
| Table name interpolation in raw SQL | **Medium** | `api_cod/request.php:429` | Mitigated by routing guard |

## Areas That Need Attention

- **Security:** Remove hardcoded credentials, restrict `?test` mode, fix `execute_query()` leakage
- **PHP 8.2+ compatibility:** Replace `FILTER_SANITIZE_STRING` in `proxy.php`
- **Dead code cleanup:** Remove `te.php`, backup files, duplicate entry points
- **Testing:** Add unit tests for query builders, input validation, caching
- **Documentation:** Document the `$qua` vs `$query` pattern, add inline comments for complex queries
- **Consolidation:** Merge `test/` and `test2/` into a single test UI
- **Environment config:** Implement proper `.env` file loading (e.g., `vlucas/phpdotenv`)
- **Security headers:** Add CSP, X-Frame-Options, X-Content-Type-Options

## Improvement Plan

### Quick Fixes (Immediate)
1. Remove `load_env.php` from version control; add to `.gitignore`
2. Create `.env.example` with placeholder values
3. Gate `?test` behind `APP_ENV === 'development'` check
4. Fix `execute_query()` to log errors instead of echoing them
5. Delete `te.php`, `missing_exists_backup_.php_x`, `script.js.backup`
6. Fix `highlightJson()` escaping in `test2/script.js`

### Medium-Term (1-3 months)
1. Replace `FILTER_SANITIZE_STRING` with `htmlspecialchars()` in `proxy.php`
2. Consolidate `api.php` and `index.php` into a single entry point
3. Consolidate `test/` and `test2/` into a single test UI
4. Add `CURLOPT_TIMEOUT` to `proxy.php`
5. Restrict `t.php` to localhost or authenticated users
6. Add SRI hashes to CDN resources in `openapi.html` and test UIs
7. Implement proper `.env` file loading with `vlucas/phpdotenv`

### Long-Term (3-6 months)
1. Introduce PSR-4 autoloading with Composer
2. Add unit tests (PHPUnit) for all query builder functions
3. Replace the switch/case router with a configuration-driven routing table
4. Implement API key authentication for sensitive endpoints
5. Add request logging and monitoring
6. Implement rate limiting per IP
7. Add Content-Security-Policy headers
8. Consider migrating to a lightweight PHP framework (Slim, Lumen) for better structure

## Comprehensive Review

| Metric | Score | Notes |
|--------|-------|-------|
| **Overall Rating** | 6/10 | Functional API with good organization, but has security gaps and dead code |
| **Production Readiness** | Medium | Currently running in production, but needs security hardening |
| **Security Score** | 5/10 | Good use of prepared statements, but debug modes, credential leakage, and XSS are risks |
| **Technical Debt** | Medium | Dead code, duplicate files, inconsistent patterns |
| **Maintainability** | 6.5/10 | Clear namespace organization, but no tests and large switch statement |
| **Risk Assessment** | Medium | The hardcoded credentials and unrestricted `?test` parameter are the highest-priority risks |

## Setup & Usage

### Requirements
- PHP 7.4+ with PDO MySQL and cURL extensions
- APCu extension (optional, degrades gracefully)
- MySQL database with the MDWiki schema
- Web server (Apache, Nginx, or PHP built-in server)

### Environment Configuration
Create a `.env` file (or set environment variables):
```env
APP_ENV=development          # or "production"
DB_HOST_TOOLS=localhost:3306
DB_NAME=<database_name>
TOOL_TOOLSDB_USER=<username>
TOOL_TOOLSDB_PASSWORD=<password>
```

### Local Development
```bash
# Start PHP built-in server
php -S localhost:8000

# Test an endpoint
curl "http://localhost:8000/api.php?get=pages&limit=10&test"

# Open API documentation
open http://localhost:8000/openapi.html

# Open test UI
open http://localhost:8000/test/
```

### Example API Calls
```bash
# Pages with pagination
GET api.php?get=pages&limit=10&offset=0

# Users filtered by name
GET api.php?get=users&userlike=John&user_group=translator

# Status for a specific year
GET api.php?get=status&year=2024

# Leaderboard with caching
GET api.php?get=leaderboard_table&cat=RTT&apcu

# Missing articles for a language
GET api.php?get=missing_by_lang_and_category&lang=ar&category=RTT
```

### Testing Endpoints
- **Swagger UI:** `openapi.html` -- interactive API documentation
- **ReDoc:** `redoc.html` -- alternative API documentation
- **Test UI:** `test/` -- Bootstrap-based endpoint testing interface
- **Test UI v2:** `test2/` -- static HTML alternative

### Deployment
Pushing to the `main` branch triggers GitHub Actions which SSHs to Toolforge and runs the deployment script. No manual deployment steps required.
