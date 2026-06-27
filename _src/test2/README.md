# Static API Test UI v2 (`src/test2/`)

## Project Overview

A purely static, client-side API testing interface for the TD_API. This is an experimental rewrite of the `src/test/` directory that eliminates the PHP dependency entirely -- the entry point is `index.html` instead of `index.php`. It dynamically generates Bootstrap 5 tabbed forms from endpoint configuration JSON, allowing developers to test all 39 endpoints.

### Differences from `src/test/`
| Aspect | `src/test/` | `src/test2/` |
|--------|-------------|---------------|
| Entry point | `index.php` (PHP, but no PHP code) | `index.html` (pure static) |
| Theme system | Full dark/light/system toggle | None (dark only, inline CSS) |
| DOM construction | `createElement` approach | Mix of `innerHTML` and `createElement` |
| CSS | External files (`style.css`, `theme.css`) | Inline in `<style>` tag |
| Bootstrap version | 5.3.2 | 5.3.0 |

### Main Features
- Dynamic endpoint form generation from `endpoint_params.json`
- 39 endpoints organized into 8 tabbed groups
- Parameter types: text (with tri-state radio), number, select, switch
- "Try it" button with formatted, syntax-highlighted JSON responses
- Collapsible endpoint cards
- JSON syntax highlighting with color-coded keys, strings, numbers, booleans, nulls

### Technologies
- **Frontend:** Vanilla JavaScript (no jQuery)
- **CSS Framework:** Bootstrap 5.3.0
- **No build tools, no bundler, no npm, no PHP**

## Project Structure

```
src/test2/
  index.html              # Static HTML shell with inline CSS (2553 bytes)
  script.js               # Core application logic (550+ lines)
  script.js.backup        # Previous version of script.js (20482 bytes)
```

### Architecture
```
Browser loads index.html
  |
  +--> script.js (DOMContentLoaded):
         |
         +--> Promise.all([
         |      fetch('../endpoint_params.json'),
         |      fetch('../test/endpointGroups.json')
         |    ])
         |
         +--> generateEndpoints()  ← creates Bootstrap nav-tabs + tab-panes
         |     |
         |     +--> createEndpoint()     ← collapsible card per endpoint
         |     +--> createParamInput()   ← form inputs per parameter type
         |
         +--> Form submission handler:
               testEndpoint() → fetch() → highlightJson() → responseDiv.innerHTML
```

### Key Differences from `script.js.backup`
1. Fixed `endpointGroups.json` path (`../test/endpointGroups.json`)
2. Added null-safety guards for form element access
3. Added explicit `select` param type handler
4. Switched text input rendering from `createElement` to `innerHTML` templates
5. Added `String()` wrapping for form values

## Architecture & Code Quality Review

### Code Organization
- Single HTML file with inline `<style>` block
- Single JS file with all application logic
- Backup file (`script.js.backup`) retained from previous iteration

### Design Patterns
- **Promise.all for parallel data loading:** Fetches endpoint config and groups simultaneously
- **Dynamic UI generation:** Forms built from JSON config at runtime
- **JSON syntax highlighting:** Client-side regex-based colorizer

### Maintainability
- **Bad:** Inline CSS makes styling changes require editing HTML
- **Bad:** `script.js.backup` is dead code that should be removed
- **Mixed:** `innerHTML` is more concise but less safe than `createElement`

### Readability
- Variable naming is mostly consistent (camelCase)
- Function names are descriptive
- Template literals improve readability of HTML generation

## Strengths

1. **Zero server dependencies:** Pure static files, no PHP required
2. **Parallel data loading:** `Promise.all` fetches config files simultaneously
3. **JSON syntax highlighting:** Custom `highlightJson()` provides color-coded response display
4. **Null-safety guards:** Defensive checks added for form element access
5. **No jQuery dependency:** Pure vanilla JavaScript

## Weaknesses

1. **Broken HTML escaping in `highlightJson()`:** The entity encoding is a no-op (see Critical Issues)
2. **Mixed DOM construction:** `innerHTML` for text params, `createElement` for others
3. **Dead code:** `script.js.backup` (20KB), redundant null check, unused `formData` variable
4. **`highlightJson` defined inside loop:** Creates a new closure per endpoint instead of once
5. **No theme system:** Dark-only, unlike `src/test/` which has full theme toggle
6. **No error display to users:** API call errors are caught but only logged to console
7. **Inline CSS:** Makes the HTML file larger and styling harder to maintain
8. **Commented-out code:** Lines 317-322 and 245 contain dead commented code

## Critical Issues

### 1. Broken HTML Entity Escaping (XSS Risk) -- HIGH

**Location:** `script.js:157`
```js
json = json.replace(/&/g, '&').replace(/</g, '<').replace(/>/g, '>');
```

The replacement strings `'&'`, `'<'`, `'>'` are identical to the match characters -- the escaping is a **no-op**. The correct replacements should be:
```js
json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
```

If the API returns user-controlled content containing `<script>` tags, this would be an XSS vulnerability when rendered via `innerHTML`.

### 2. Unescaped `innerHTML` Interpolation -- MEDIUM

**Location:** `script.js:253-297`
```js
inputGroupDiv.innerHTML = `...placeholder="${placeholderDis}"...value="${param.value || ''}"...`;
```
Parameter values from `endpoint_params.json` are interpolated into HTML without escaping.

### 3. Redundant Null Check (Dead Code) -- LOW

**Location:** `script.js:176-178`
```js
if (endpointData.params) {          // already truthy
    if (!endpointData.params) {     // can never be true
        endpointData.params = [];
    }
```

### 4. Unused `formData` Variable -- LOW

**Location:** `script.js:105`
```js
const formData = new FormData(form);  // created but never used
```

## Areas That Need Attention

- **Fix `highlightJson()` escaping:** Replace no-op entity encoding with correct `&amp;`, `&lt;`, `&gt;`
- **Remove `script.js.backup`:** Dead backup file (20KB)
- **Move `highlightJson()` outside the loop:** Define once, not per-endpoint
- **Add error display UI:** Show fetch errors to users, not just console
- **Clean up dead code:** Remove unused `formData`, redundant null check, commented-out code
- **Extract inline CSS:** Move to an external `.css` file
- **Pin Bootstrap version:** Currently using 5.3.0, consider updating
- **No automated tests**

## Improvement Plan

### Quick Fixes
1. Fix `highlightJson()` escaping: `'&'` → `'&amp;'`, `'<'` → `'&lt;'`, `'>'` → `'&gt;'`
2. Delete `script.js.backup`
3. Remove unused `formData` variable
4. Remove redundant null check in the params loop
5. Remove commented-out code

### Medium-Term Improvements
1. Move `highlightJson()` definition outside the endpoint loop
2. Add error display in the response area for failed requests
3. Extract inline CSS to a separate `style.css` file
4. Add the theme toggle system from `src/test/`
5. HTML-encode parameter values before `innerHTML` insertion

### Long-Term Strategy
1. Decide whether to keep `test/` or `test2/` and consolidate
2. Add automated tests
3. Consider adopting a lightweight framework for maintainability
4. Implement request history/favorites
5. Add response time display

## Comprehensive Review

| Metric | Score | Notes |
|--------|-------|-------|
| **Overall Rating** | 5.5/10 | Functional but has a critical XSS escaping bug and significant dead code |
| **Production Readiness** | Low | The broken `highlightJson()` escaping needs fixing before any exposure |
| **Security Score** | 4/10 | Broken HTML escaping, unescaped innerHTML, no CSP |
| **Technical Debt** | Medium-High | Dead backup file, broken escaping, redundant code |
| **Maintainability** | 5/10 | Inline CSS, mixed DOM approaches, no modularization |
| **Risk Assessment** | Medium | The XSS escaping bug is the primary risk |

## Setup & Usage

### Requirements
- A running TD_API instance (`api.php` accessible at the same host)
- Modern browser with ES6+ support (Promises, template literals, async/await)
- No build step, no npm install, no PHP

### Installation
Place the `test2/` directory alongside `api.php` on the server.

### Usage
```
# Open in browser
https://mdwiki.toolforge.org/test2/
```

### External Dependencies (loaded via CDN)
| Dependency | Version | CDN |
|------------|---------|-----|
| Bootstrap CSS | 5.3.0 | jsdelivr |
| Bootstrap JS | 5.3.0 | jsdelivr |

### Internal Dependencies
- `../endpoint_params.json` -- endpoint parameter definitions
- `../test/endpointGroups.json` -- endpoint group configuration (shared with `src/test/`)
- `../api.php` -- the API being tested (called via fetch)
