# API Test UI (`src/test/`)

## Project Overview

An interactive, browser-based API testing interface for the TD_API (Translation Dashboard API). Similar in concept to Swagger UI or Postman, this tool dynamically renders endpoint forms from the API's configuration, allowing developers to test all 40+ endpoints with parameter inputs and view formatted JSON responses.

### Main Features
- Dynamic endpoint form generation from `endpoint_params.json`
- 40 endpoints organized into 8 tabbed groups
- Parameter input types: text, number, select dropdown, switch (checkbox)
- Tri-state text filters: manual input, empty, not empty
- Number filters: manual input, >0, =0
- Dark/light/system theme toggle with localStorage persistence
- Deep linking to specific endpoints via URL hash (e.g., `#pages`)
- Collapsible endpoint cards with "Try it" execution
- Formatted JSON response display

### Technologies
- **Frontend:** Vanilla JavaScript + jQuery 3.6.0
- **CSS Framework:** Bootstrap 5.3.2
- **Icons:** Bootstrap Icons 1.11.1
- **Backend:** None (purely client-side, calls `api.php` via fetch)
- **No build tools, no bundler, no npm**

### PHP Version
- The `index.php` file contains no PHP code -- it is served as PHP but is pure HTML. This may be intentional for deployment routing.

## Project Structure

```
src/test/
  index.php             # HTML entry point (no PHP code, 1620 bytes)
  script.js             # Core application logic (389 lines)
  style.css             # Main stylesheet with PS5-inspired dark/light themes (244 lines)
  theme.css             # Theme toggle dropdown styles (58 lines)
  theme.js              # Dark/light/system theme toggle logic (127 lines)
  endpointGroups.json   # Endpoint group → endpoint name mapping (1199 bytes)
```

### Architecture
```
Browser loads index.php (HTML shell)
  |
  +--> theme.js → Initializes dark/light/system theme toggle
  |
  +--> script.js:
         |
         +--> loadEndpointGroups()   ← fetches endpointGroups.json (same dir)
         |
         +--> loadEndpointParams()   ← fetches ../endpoint_params.json (parent dir)
         |
         +--> generateEndpoints()    ← creates Bootstrap tabs + endpoint cards
         |     |
         |     +--> createEndpoint()     ← collapsible card per endpoint
         |     +--> createParamInput()   ← form inputs per parameter type
         |
         +--> add_event()            ← jQuery event handlers for radio toggles

User clicks "Try it":
  testEndpoint() → collect form data → fetch() → render JSON response
```

### Endpoint Groups (40 endpoints across 8 tabs)

| Group | Endpoints |
|-------|-----------|
| **pages_infos** | missing, exists_statics_by_category, missing_by_lang_and_category, exists_by_lang_and_category, exists_by_qids, titles, assessments, enwiki_pageviews, refs_counts, words, revids |
| **pages** | pages, pages_users, pages_with_views, pages_users_to_main, in_process |
| **identifiers** | qids, qids_others |
| **views** | views, views_new, user_views2, lang_views2 |
| **users** | users, user_access, users_by_last_pupdate, full_translators, users_no_inprocess, coordinators |
| **statistics** | status, leaderboard_table, leaderboard_table_formated, count_pages, graph_data |
| **languages** | langs, translate_type |
| **other** | publish_reports, categories, projects, settings |

## Architecture & Code Quality Review

### Code Organization
- `index.php`: Minimal HTML shell with Bootstrap structure
- `script.js`: Monolithic JS file with all application logic
- `theme.js` + `theme.css`: Self-contained theme toggle module
- `style.css`: Main styling with CSS custom properties

### Design Patterns
- **Dynamic UI generation:** Forms are built from JSON configuration at runtime
- **Event delegation:** jQuery change handlers on radio button groups
- **Module separation:** Theme logic is cleanly separated into its own files

### Maintainability
- **Good:** Theme system is well-isolated; endpoint config is externalized to JSON
- **Bad:** `script.js` is monolithic (389 lines); mixed jQuery/vanilla JS usage

### Readability
- Generally good. Function names are descriptive.
- Template literals make HTML generation readable.

### Scalability
- Client-side only, so server load depends on the API backend
- Bootstrap tabs handle the 40 endpoints well

## Strengths

1. **Clean theme system:** Dark/light/system toggle with OS preference detection, localStorage persistence, and keyboard accessibility
2. **Dynamic form generation:** Endpoints are driven by `endpoint_params.json`, so adding endpoints requires no code changes
3. **Tri-state text filters:** Creative radio button pattern for empty/not_empty/manual filtering
4. **Deep linking:** URL hash support allows linking directly to specific endpoints
5. **Responsive design:** CSS grid with `auto-fill` for parameter inputs
6. **Good visual design:** PS5-inspired dark theme with smooth transitions

## Weaknesses

1. **Mixed jQuery/vanilla JS:** jQuery used in only 2 places (`add_event()` and one selector in `testEndpoint()`) while the rest is vanilla JS -- inconsistent and unnecessary dependency
2. **Monolithic `script.js`:** All logic in one 389-line file with no modularization
3. **No error display to users:** API call errors are caught but only logged to console
4. **Debug logging left in:** `console.log()` calls at lines 211 and 224
5. **Dead code:** Commented-out localhost/production URL logic (lines 233-238)
6. **Font reference:** `style.css` references `'SST'` (Sony proprietary font) which will never load from any CDN
7. **Inconsistent variable naming:** Mix of camelCase and snake_case (`endpointGroups` vs `end_params`)

## Critical Issues

| Issue | Severity | Location |
|-------|----------|----------|
| XSS via `innerHTML` with unescaped API response data | **Medium** | `script.js:263` -- `JSON.stringify()` does NOT escape `<`, `>`, `&` |
| XSS via `innerHTML` with unescaped param names/placeholders | **Medium** | `script.js:88,100-113` |
| No Content Security Policy | **Medium** | `index.php` -- loads from 3 external CDN domains |
| CDN resources loaded without SRI integrity hashes | **Low** | `index.php` -- Bootstrap, Icons, jQuery from CDN |

## Areas That Need Attention

- **XSS mitigation:** Use `textContent` instead of `innerHTML` for API response rendering, or HTML-encode `<`, `>`, `&` before insertion
- **Error feedback:** Display API call errors to users in the UI (not just console)
- **Remove jQuery dependency:** Replace the 2 jQuery usages with vanilla JS
- **Clean up debug code:** Remove `console.log()` calls and commented-out code
- **Add CSP headers:** Configure Content-Security-Policy to restrict script sources
- **Add SRI hashes:** Add `integrity` attributes to CDN script/link tags
- **Testing:** No automated tests exist

## Improvement Plan

### Quick Fixes
1. Remove `console.log()` calls from `script.js`
2. Delete commented-out URL logic (lines 233-238)
3. Add error display UI in the response area for failed requests
4. Replace `'SST'` font with a standard web font (e.g., `'Inter', sans-serif`)

### Medium-Term Improvements
1. Replace `innerHTML` with `textContent` for API response rendering
2. HTML-encode parameter names/placeholders before `innerHTML` insertion
3. Remove jQuery dependency -- replace `add_event()` with vanilla `addEventListener`
4. Add SRI hashes to all CDN resources
5. Split `script.js` into modules (endpoint rendering, form handling, API calls)

### Long-Term Strategy
1. Consider migrating to a lightweight framework (e.g., Preact or Alpine.js)
2. Add end-to-end tests with Playwright or Puppeteer
3. Implement request history/favorites
4. Add response time display and comparison
5. Support POST/PUT/DELETE methods for endpoints that need them

## Comprehensive Review

| Metric | Score | Notes |
|--------|-------|-------|
| **Overall Rating** | 7/10 | Well-designed UI with good UX, but has XSS concerns and inconsistent code |
| **Production Readiness** | Medium | Works well as a dev tool, but needs XSS fixes for public exposure |
| **Security Score** | 5/10 | XSS via innerHTML, no CSP, no SRI |
| **Technical Debt** | Low-Medium | Some dead code, mixed jQuery/vanilla, debug logging |
| **Maintainability** | 7/10 | Clean theme system, but monolithic script.js |
| **Risk Assessment** | Low | This is an internal developer tool, not user-facing |

## Setup & Usage

### Requirements
- A running TD_API instance (`api.php` accessible at the same host)
- Modern browser with ES6+ support
- No build step, no npm install

### Installation
Place the `test/` directory alongside `api.php` on the server. No configuration needed.

### Usage
```
# Open in browser
https://mdwiki.toolforge.org/test/

# Deep link to a specific endpoint
https://mdwiki.toolforge.org/test/#pages
```

### External Dependencies (loaded via CDN)
| Dependency | Version | CDN |
|------------|---------|-----|
| Bootstrap CSS | 5.3.2 | jsdelivr |
| Bootstrap JS | 5.3.2 | jsdelivr |
| Bootstrap Icons | 1.11.1 | jsdelivr |
| jQuery | 3.6.0 | code.jquery.com |

### Internal Dependencies
- `../endpoint_params.json` -- endpoint parameter definitions (fetched at runtime)
