# CORS Proxy (`src/api/`)

## Project Overview

A lightweight HTTP reverse proxy script that forwards requests to the main TD_API endpoint at `https://mdwiki.toolforge.org/api.php`. Its primary purpose is to bypass browser CORS (Cross-Origin Resource Sharing) restrictions, allowing frontend applications on different domains to consume the MDWiki API.

### Main Features
- CORS header injection (`Access-Control-Allow-Origin: *`)
- Query string forwarding to the upstream API
- cURL-based HTTP request execution with SSL verification
- JSON error responses on failure

### Technologies
- **Language:** PHP (procedural, no framework)
- **Extension:** cURL
- **Dependencies:** None (standalone script, no Composer, no autoloading)

### PHP Version
- Compatible with PHP 7.x
- Uses `FILTER_SANITIZE_STRING` which is **deprecated in PHP 8.1** and **removed in PHP 8.2+**

## Project Structure

```
src/api/
  proxy.php    # The entire proxy (49 lines, single file)
```

This is a self-contained single-file module with no subdirectories, no configuration files, and no dependencies on other project files.

## Architecture & Code Quality Review

### Request Flow
```
Browser → proxy.php → cURL → https://mdwiki.toolforge.org/api.php → Response → Browser
```

1. CORS headers are set on the response
2. Query string is extracted from `$_SERVER['QUERY_STRING']`
3. Parameters are sanitized via `filter_var()` and appended to the target URL
4. cURL executes the request with SSL verification enabled
5. Response is echoed directly with `Content-Type: application/json`

### Code Organization
- Single file, top-to-bottom procedural script
- No classes, no functions, no separation of concerns
- Acceptable for a 49-line utility script

### Design Patterns
- None. This is a straightforward proxy script.

### SOLID Principles
- Not applicable at this scale. The file has a single responsibility (proxy forwarding).

### Maintainability
- **Good:** Simple and easy to understand
- **Bad:** Hardcoded target URL, no configuration mechanism

### Readability
- Clean, well-structured code with clear variable names

### Scalability
- No rate limiting, no connection pooling, no caching
- Each request creates a new cURL session

## Strengths

- SSL verification is enabled (`CURLOPT_SSL_VERIFYPEER = true`)
- cURL errors are caught and returned as structured JSON responses
- Minimal and focused -- does one thing
- Follows redirects (`CURLOPT_FOLLOWLOCATION = true`)

## Weaknesses

- **Wide-open CORS policy:** `Access-Control-Allow-Origin: *` allows any website to use this as an open relay
- **Hardcoded User-Agent:** Uses an outdated Chrome 58 string (from 2017)
- **POST body not forwarded:** Only `QUERY_STRING` is used; POST requests are silently dropped despite CORS headers advertising POST support
- **No request method validation:** Any HTTP method is accepted without checking

## Critical Issues

| Issue | Severity | Location |
|-------|----------|----------|
| `FILTER_SANITIZE_STRING` deprecated/removed | **High** | `proxy.php:17` |
| No `CURLOPT_TIMEOUT` configured | **Medium** | `proxy.php:22-29` |
| Open relay potential (no rate limiting or auth) | **Medium** | `proxy.php:5` |
| Response always served as `application/json` regardless of upstream content type | **Low** | `proxy.php:45` |

## Areas That Need Attention

- **PHP 8.2+ compatibility:** Replace `FILTER_SANITIZE_STRING` with `htmlspecialchars()` or a custom whitelist
- **Timeout configuration:** Add `CURLOPT_TIMEOUT` (e.g., 30 seconds) to prevent indefinite hangs
- **Input validation:** No whitelist of allowed parameters or query string length limits
- **Logging:** No request logging for debugging or abuse detection
- **Security headers:** No `X-Content-Type-Options`, `X-Frame-Options`, or CSP headers

## Improvement Plan

### Quick Fixes
1. Add `CURLOPT_TIMEOUT` with a reasonable value (e.g., 30 seconds)
2. Replace `FILTER_SANITIZE_STRING` with `htmlspecialchars($params, ENT_QUOTES, 'UTF-8')`
3. Update the User-Agent string to a current browser version

### Medium-Term Improvements
1. Add rate limiting (e.g., via APCu or a simple file-based counter)
2. Implement request method validation (only allow GET)
3. Add a configurable allowlist of origins instead of `*`
4. Forward the `Content-Type` header from the upstream response

### Long-Term Strategy
1. Consider removing this proxy entirely if the main API can be configured with proper CORS headers
2. Add request/response logging for monitoring
3. Implement API key validation if the proxy needs to be restricted

## Comprehensive Review

| Metric | Score | Notes |
|--------|-------|-------|
| **Overall Rating** | 5/10 | Functional but has deprecated code and no security controls |
| **Production Readiness** | Low | Missing timeout, rate limiting, and modern PHP compatibility |
| **Security Score** | 4/10 | Open relay, deprecated sanitization, no auth |
| **Technical Debt** | Low | Minimal code, but the deprecated filter needs fixing |
| **Maintainability** | 7/10 | Simple enough to maintain, but hardcoded values limit flexibility |
| **Risk Assessment** | Medium | The deprecated `FILTER_SANITIZE_STRING` will break on PHP 8.2+ |

## Setup & Usage

### Installation
No installation required. Place `proxy.php` on any PHP server with cURL enabled.

### Usage
```
# Forward a request to the main API
GET /api/proxy.php?get=pages&limit=10

# The proxy appends the query string to:
# https://mdwiki.toolforge.org/api.php?get=pages&limit=10
```

### Requirements
- PHP 7.x (or 8.0 for full compatibility)
- cURL extension enabled
- No database or caching dependencies
