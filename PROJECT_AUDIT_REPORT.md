# TD_API -- Project Audit Report

**Date:** 2026-05-27
**Scope:** Full source directory (`src/`) -- 4 modules, ~3,500 lines of PHP, ~1,200 lines of JavaScript
**Auditor:** Automated code review

---

## Executive Summary

TD_API is a REST-like HTTP API serving as the primary data access layer for the **MDWiki** Wikimedia medical content translation project. It provides **40+ endpoints** for translation pages, user statistics, language data, Wikidata QID lookups, and content analytics to frontend applications (Translation Dashboard, Wiki Management Tools).

**Technologies:** PHP 7.4+ (procedural, no framework), MySQL/PDO, APCu caching, Bootstrap 5 frontend, OpenAPI 3.0 documentation.

**Architecture:** Single-entry-point procedural PHP API. Requests flow through `api.php` → `include_all.php` (dependency loader) → `request.php` (switch/case router) → endpoint-specific query builders → `sql.php` (PDO + APCu) → JSON response. A separate CORS proxy (`api/proxy.php`) and two client-side test UIs (`test/`, `test2/`) complement the core engine.

**At a glance:** The system is functional and actively serving production traffic. It has solid fundamentals (parameterized queries, input validation, APCu caching, OpenAPI spec) but carries significant security debt from debug modes accessible to any user, hardcoded credentials in version control, and inconsistent error handling.

---

## Project Health Assessment

| Dimension | Rating | Summary |
|-----------|--------|---------|
| **Code Quality** | 6/10 | Consistent namespace organization, but dead code, inconsistent naming, and no tests |
| **Maintainability** | 6/10 | Clear module separation, but 512-line switch statement and no autoloading |
| **Scalability** | 7/10 | APCu caching with 12h TTL, connection-per-request model adequate for current load |
| **Security Posture** | 4.5/10 | Good use of prepared statements undermined by debug leakage, hardcoded credentials, and XSS |
| **Production Readiness** | 5.5/10 | Running in production, but with multiple unaddressed security and reliability gaps |

---

## Cross-Project Analysis

### Shared Architectural Patterns

| Pattern | Where Used | Assessment |
|---------|-----------|------------|
| Parameterized SQL (PDO) | `api_cod/sql.php`, `helps.php`, all `subs/` | Correctly implemented for most endpoints |
| APCu cache-aside | `sql.php` (`fetch_query_new()`) | Good: 12h TTL, graceful fallback, explicit opt-in |
| JSON config-driven endpoints | `endpoint_params.json` | Good: centralized, enables dynamic UI generation |
| Whitelist validation | `sanitize_input()`, `filter_order()`, `get_select()` | Good: regex + column/param whitelists |
| Switch/case routing | `request.php` (35+ cases) | Fragile: would benefit from a config-driven routing table |

### Repeated Weaknesses

1. **Debug modes accessible to anyone** -- The `?test` parameter enables `display_errors` and full `error_reporting` in both `api.php` and `index.php` without environment gating. In production, any user can trigger verbose error output.

2. **Dead code accumulation** -- Every module contains dead code:
   - `te.php` (empty file)
   - `missing_exists_backup_.php_x` (328-line backup)
   - `script.js.backup` (20KB backup)
   - `$qua_old` in `titles_infos.php` (unused variable)
   - Commented-out blocks in `request.php`, `t.php`, `script.js`

3. **Duplicated entry points** -- `api.php` and `index.php` are near-identical (17 vs 16 lines). Any behavioral change must be applied to both.

4. **Two competing test UIs** -- `test/` (PHP entry, jQuery, full theme system) and `test2/` (HTML entry, no jQuery, broken escaping) serve the same purpose with different implementations.

5. **No automated tests** -- Zero unit tests, zero integration tests, zero end-to-end tests across the entire project.

### Common Technical Debt

| Debt Item | Impact | Files Affected |
|-----------|--------|---------------|
| No Composer/autoloading | Adding modules requires editing `include_all.php` | `include_all.php` |
| No `.env` loader | `load_env.php` uses `putenv()` with hardcoded values | `load_env.php`, `include_all.php` |
| Inconsistent return types | Some functions return `[$query, $params]`, others `[$query, $params, $error]` | `api_cod/subs/*.php` |
| Mixed variable naming | `$qua`/`$query`/`$qu_ery`/`$query_line` for query strings | `request.php`, `helps.php` |
| No security headers | No CSP, X-Frame-Options, X-Content-Type-Options | All entry points |

### Dependency Issues

| Dependency | Status | Risk |
|------------|--------|------|
| `FILTER_SANITIZE_STRING` | **Removed in PHP 8.2** | `proxy.php` will break |
| Swagger UI 4.5.0 | Pinned but outdated (current: 5.x) | Low |
| ReDoc `@next` tag | **Unpinned** -- could break at any time | Medium |
| jQuery 3.6.0 | Used in only 2 places in `test/script.js` | Unnecessary dependency |
| Bootstrap 5.3.0/5.3.2 | Two different versions across test UIs | Inconsistency |
| CDN resources (all) | No SRI integrity hashes | Supply chain risk |

### Integration Concerns

1. **`test2/` depends on `test/endpointGroups.json`** -- Cross-directory dependency via relative path `../test/endpointGroups.json`. If `test/` is removed, `test2/` breaks.
2. **`test/` depends on `../endpoint_params.json`** -- Same pattern; test UIs are coupled to the parent directory structure.
3. **`api/proxy.php` is independent** -- Not referenced by any other file. Could be removed without impact.
4. **`t.php` is independent** -- Cache diagnostic tool with no dependents.

---

## Critical Findings

### P0 -- Immediate Action Required

| # | Issue | Severity | Location | Impact |
|---|-------|----------|----------|--------|
| 1 | **Hardcoded database credentials in version control** | **Critical** | `load_env.php` | Root user `root:root11` committed to git history. Even if deleted now, it persists in history. |
| 2 | **`?test` enables `display_errors` in production** | **High** | `api.php:5`, `index.php:5` | Any user can append `?test` to trigger verbose PHP error output, potentially leaking file paths, DB details, and stack traces. |
| 3 | **SQL error leakage in `execute_query()`** | **High** | `api_cod/sql.php:138` | Method echoes full SQL query + error message to output: `echo "sql error:" . $e->getMessage() . "<br>" . $sql_query;` |

### P1 -- Fix Within 1 Week

| # | Issue | Severity | Location | Impact |
|---|-------|----------|----------|--------|
| 4 | **`FILTER_SANITIZE_STRING` removed in PHP 8.2+** | **High** | `api/proxy.php:17` | Proxy will fatally error on PHP 8.2+. |
| 5 | **Broken HTML entity escaping (XSS)** | **High** | `test2/script.js:157` | `highlightJson()` replacement is a no-op -- `'&'` replaces `'&'` instead of `'&amp;'`. |
| 6 | **`$_COOKIE['test']` controls debug output** | **Medium** | `api_cod/sql.php:87` | `test_print()` method echoes debug data based on user-controlled cookie. |

### P2 -- Fix Within 1 Month

| # | Issue | Severity | Location | Impact |
|---|-------|----------|----------|--------|
| 7 | **XSS via `innerHTML` in both test UIs** | **Medium** | `test/script.js:263`, `test2/script.js:157` | API response data rendered via `innerHTML` without HTML encoding. |
| 8 | **`t.php` exposes cache internals without auth** | **Medium** | `t.php` | Anyone can view APCu cache keys and clear the entire cache via `?clear`. |
| 9 | **Table name interpolation in raw SQL** | **Medium** | `request.php:429`, `titles_infos.php:91` | `$get` used directly in SQL strings, mitigated only by switch/case routing guard. |
| 10 | **Unsanitized `$_GET['select']` before whitelist** | **Medium** | `select_helps.php:19` | Raw value read first, validated later. If whitelist is incomplete, SQL injection possible. |
| 11 | **No `CURLOPT_TIMEOUT` on proxy** | **Medium** | `api/proxy.php` | Proxy can hang indefinitely if upstream is unresponsive. |
| 12 | **CDN resources without SRI hashes** | **Low** | `openapi.html`, `test/`, `test2/` | CDN compromise would allow arbitrary script injection. |

### Performance Bottlenecks

| Issue | Location | Impact |
|-------|----------|--------|
| `ONLY_FULL_GROUP_BY` auto-disabled | `sql.php:49-57` | Masks query correctness bugs; could produce incorrect aggregation results |
| Connection-per-request | `sql.php` | No connection pooling; acceptable for current load but not scalable |
| `highlightJson()` defined inside loop | `test2/script.js:153` | Creates a new closure per endpoint instead of once |

---

## Strengths

### Strong Engineering Decisions

1. **Parameterized SQL via PDO** -- The majority of endpoints use prepared statements with `?` placeholders. This is the correct defense against SQL injection and is consistently applied through `helps.php:add_li_params()`.

2. **Whitelist-based input validation** -- `sanitize_input()` validates against regex patterns; `filter_order()` validates ORDER BY against endpoint columns/params; `get_select()` whitelists allowed SELECT values; `qids_qua()` whitelists table names. Defense in depth.

3. **APCu caching with graceful fallback** -- The caching layer checks for APCu availability and provides dummy functions if the extension is missing. 12-hour TTL with explicit opt-in (`&apcu` parameter) gives operators control.

4. **Configuration-driven endpoints** -- `endpoint_params.json` (33KB) defines all endpoint parameters, columns, and types in one place. This enables both the server-side router and client-side test UIs to share a single source of truth.

5. **Comprehensive OpenAPI specification** -- The 61KB `openapi.json` with two documentation UIs (Swagger UI, ReDoc) provides excellent API discoverability.

6. **Clean namespace organization** -- All modules use proper PHP namespaces (`API\SQL`, `API\Helps`, `API\Status`, etc.) with explicit `use function` imports. This is well-organized for a procedural codebase.

7. **Production safety measures** -- Query strings are stripped from API responses on non-localhost servers. The `endpoint_params.json` redirect mechanism keeps the API surface clean.

### Reusable Components

| Component | Reusability |
|-----------|------------|
| `helps.php` query builder functions | High -- generic, endpoint-agnostic |
| `sql.php` Database class + APCu | High -- could serve any PHP/MySQL project |
| `test/theme.js` theme toggle | Medium -- self-contained, portable |
| `endpoint_params.json` config | High -- drives both server and client |

---

## Improvement Roadmap

### Immediate Fixes (This Week)

| # | Action | Effort | Impact |
|---|--------|--------|--------|
| 1 | Remove `load_env.php` from version control; add to `.gitignore` | 5 min | Eliminates credential exposure |
| 2 | Rotate the committed database credentials (`root:root11`) | 15 min | Invalidates leaked credentials |
| 3 | Create `.env.example` with placeholder values | 5 min | Documents required env vars |
| 4 | Gate `?test` behind `APP_ENV === 'development'` in `api.php` and `index.php` | 10 min | Blocks debug mode in production |
| 5 | Fix `execute_query()` to use `error_log()` instead of `echo` | 5 min | Stops SQL error leakage |
| 6 | Fix `highlightJson()` escaping in `test2/script.js` (`'&'` → `'&amp;'`) | 2 min | Fixes XSS vulnerability |
| 7 | Delete `te.php` | 1 min | Removes dead code |

### Short-Term (1-4 Weeks)

| # | Action | Effort | Impact |
|---|--------|--------|--------|
| 8 | Replace `FILTER_SANITIZE_STRING` with `htmlspecialchars()` in `proxy.php` | 15 min | PHP 8.2+ compatibility |
| 9 | Add `CURLOPT_TIMEOUT` (30s) to `proxy.php` | 5 min | Prevents proxy hangs |
| 10 | Delete `missing_exists_backup_.php_x` and `script.js.backup` | 2 min | Removes ~30KB dead code |
| 11 | Restrict `t.php` to localhost or add basic auth | 30 min | Prevents cache abuse |
| 12 | Remove `$_COOKIE['test']` debug check from `sql.php` | 10 min | Removes cookie-based debug vector |
| 13 | Consolidate `api.php` and `index.php` into single entry point | 30 min | Eliminates maintenance risk |
| 14 | Add SRI hashes to all CDN resources | 30 min | Prevents CDN supply chain attacks |
| 15 | Pin ReDoc CDN to a specific version | 5 min | Prevents `@next` breakage |
| 16 | Remove `console.log()` calls from `test/script.js` | 5 min | Cleans debug output |

### Medium-Term (1-3 Months)

| # | Action | Effort | Impact |
|---|--------|--------|--------|
| 17 | Implement proper `.env` loader (e.g., `vlucas/phpdotenv`) | 2h | Eliminates `putenv()` fragility |
| 18 | Replace switch/case router with config-driven routing table | 4h | Enables dynamic endpoint registration |
| 19 | Standardize all query builder return types to `[$query, $params, $error]` | 2h | Eliminates inconsistent error handling |
| 20 | Add `json_decode` error checking in `request.php:61` | 15 min | Prevents silent config failures |
| 21 | Consolidate `test/` and `test2/` into single test UI | 4h | Eliminates duplicate maintenance |
| 22 | Replace jQuery with vanilla JS in `test/script.js` | 1h | Removes unnecessary 87KB dependency |
| 23 | Add `X-Content-Type-Options`, `X-Frame-Options` headers | 15 min | Basic security hardening |
| 24 | Add PHPDoc blocks to all public functions in `api_cod/` | 4h | Improves code documentation |
| 25 | HTML-encode API responses before `innerHTML` insertion in test UIs | 1h | Eliminates XSS vectors |

### Long-Term (3-6 Months)

| # | Action | Effort | Impact |
|---|--------|--------|--------|
| 26 | Introduce PSR-4 autoloading with Composer | 4h | Modernizes dependency management |
| 27 | Add PHPUnit tests for all query builder functions | 2-3 days | Establishes test coverage baseline |
| 28 | Add integration tests for all 40+ endpoints | 2-3 days | Validates end-to-end behavior |
| 29 | Implement API key authentication for sensitive endpoints | 1 day | Protects write/admin operations |
| 30 | Add rate limiting per IP (e.g., via APCu or Redis) | 4h | Prevents abuse |
| 31 | Implement structured request logging | 4h | Enables monitoring and debugging |
| 32 | Add Content-Security-Policy headers | 2h | Prevents XSS and injection attacks |
| 33 | Consider migrating to Slim/Lumen framework | 1-2 weeks | Provides routing, middleware, DI out of the box |

### Security Hardening Priorities (Ordered)

```
1. [P0] Remove hardcoded credentials from version control + rotate
2. [P0] Gate ?test debug mode behind APP_ENV check
3. [P0] Fix execute_query() SQL error echoing
4. [P1] Fix FILTER_SANITIZE_STRING for PHP 8.2+
5. [P1] Fix highlightJson() XSS escaping
6. [P1] Remove $_COOKIE['test'] debug vector
7. [P2] Add SRI hashes to all CDN resources
8. [P2] Restrict t.php access
9. [P2] Add security headers (CSP, X-Frame-Options)
10. [P3] Implement API key auth for sensitive endpoints
11. [P3] Add rate limiting
```

### DevOps and Testing Recommendations

| Area | Current State | Recommendation |
|------|--------------|----------------|
| **CI/CD** | GitHub Actions SSH deploy | Add pre-deploy smoke tests |
| **Testing** | None | PHPUnit for backend, Playwright for frontend |
| **Monitoring** | `error_log()` only | Add structured logging (JSON) with request IDs |
| **Environment** | `putenv()` in `load_env.php` | `vlucas/phpdotenv` with `.env` files |
| **Dependencies** | No Composer | Introduce Composer for autoloading at minimum |
| **Linting** | None | Add PHP_CodeSniffer + ESLint for JS |
| **Secrets** | Hardcoded in git | Use environment variables + `.gitignore` |

---

## Final Evaluation

| Metric | Score | Notes |
|--------|-------|-------|
| **Overall Project Score** | **5.5/10** | Functional and well-organized at the module level, but security gaps and lack of tests are significant |
| **Risk Level** | **Medium-High** | Hardcoded credentials + unrestricted debug modes + no tests = high incident potential |
| **Technical Debt Level** | **Medium** | Dead code, duplicate files, inconsistent patterns, no autoloading |
| **Production Readiness** | **55%** | Running in production, but with known security gaps that should be addressed within 30 days |
| **Security Score** | **4.5/10** | Good foundations (prepared statements, whitelists) undermined by debug leakage and credential exposure |
| **Maintainability Score** | **6/10** | Clear namespace organization, but no tests, large switch statement, and dead code |

### Recommended Next Steps

1. **Today:** Execute all 7 immediate fixes (est. 45 minutes total)
2. **This week:** Rotate the compromised database credentials
3. **This month:** Complete the 9 short-term items
4. **This quarter:** Begin medium-term refactoring, starting with `.env` loader and test consolidation
5. **This half:** Establish PHPUnit test coverage and consider framework migration

The project is a solid procedural PHP API with good bones. The parameterized queries, input validation, and caching architecture are well-implemented. The primary risks are operational (debug modes, credentials) rather than architectural, which means they can be resolved quickly with focused effort.
