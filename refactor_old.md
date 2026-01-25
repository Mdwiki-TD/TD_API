# TD_API Refactoring Plan

## Executive Summary

This document outlines a comprehensive refactoring plan for the TD_API (Mdwiki Translation Dashboard API) codebase. The project is a REST API serving the MDwiki Translation Dashboard, built with PHP and hosted on Wikimedia Toolforge.

**Current State**: ~1642 lines of PHP code across 14 files, with ~2000+ lines of JSON configuration.

**Key Issues**: Security vulnerabilities, code duplication, poor separation of concerns, minimal testing, and lack of proper configuration management.

---

## Priority Levels

- **P0 (Critical)**: Security vulnerabilities requiring immediate attention
- **P1 (High)**: Issues affecting maintainability and reliability
- **P2 (Medium)**: Code quality improvements
- **P3 (Low)**: Nice-to-have enhancements

---

## 1. Security Improvements (P0 - Critical)

### 1.1 Database Credentials Exposure
**Location**: `api_cod/sql.php:72-73`

**Problem**:
```php
$this->user = 'root';
$this->password = 'root11';
```

**Action Items**:
- [ ] Move credentials to environment variables (`getenv()`)
- [ ] Create `.env.example` file with required variables
- [ ] Add `.env` to `.gitignore`
- [ ] Implement `vlucas/phpdotenv` or custom environment loader
- [ ] Document required environment variables in README

**Alternatives**:
- Use Toolforge's built-in credential management (`/data/project/.my.cnf`)
- Store config in separate file outside webroot

---

### 1.2 Error Information Disclosure
**Location**: `api_cod/sql.php:147-150`, `api.php:3-7`

**Problem**:
```php
// Echoes SQL errors directly to client
echo "sql error:" . $e->getMessage() . "<br>" . $sql_query;

// Enables full error display with ?test=1
ini_set('display_errors', 1);
```

**Action Items**:
- [ ] Remove all `echo` statements for errors
- [ ] Implement proper error logging to file
- [ ] Return generic error messages to clients
- [ ] Remove or protect `?test=` parameter (require authentication)
- [ ] Add environment-aware error display (development vs production)

---

### 1.3 SQL Injection Risk via Manual Interpolation
**Location**: `api_cod/request.php:484`

**Problem**:
```php
$qua = sprintf(str_replace('?', "'%s'", $query), ...$params);
```

**Action Items**:
- [ ] Use PDO prepared statements properly
- [ ] Bind parameters with `bindParam()` or `bindValue()`
- [ ] Remove manual string interpolation
- [ ] Audit all database queries for proper parameterization

---

### 1.4 Input Validation
**Location**: Throughout `api_cod/request.php`

**Problem**:
- `FILTER_SANITIZE_SPECIAL_CHARS` is insufficient
- LIKE queries vulnerable to pattern matching attacks
- No validation for numeric parameters

**Action Items**:
- [ ] Create dedicated input validation class
- [ ] Whitelist allowed values for enum parameters
- [ ] Sanitize LIKE parameters (escape `%` and `_`)
- [ ] Add type validation for integers/floats
- [ ] Implement maximum length checks

---

### 1.5 Rate Limiting
**Problem**: No protection against API abuse

**Action Items**:
- [ ] Implement rate limiting by IP address
- [ ] Add throttling for expensive queries
- [ ] Consider API key system for authenticated users
- [ ] Add request logging for monitoring

---

## 2. Code Deduplication (P1 - High)

### 2.1 Duplicate Entry Points
**Files**: `api.php`, `index.php`

**Problem**: Both files are identical (18 lines)

**Action Items**:
- [ ] Delete `index.php` (keep `api.php` as canonical entry)
- [ ] Update any references to `index.php`
- [ ] Add redirect from `index.php` to `api.php` if needed for backward compatibility

---

### 2.2 Duplicate Language Filtering Functions
**Locations**:
- `api_cod/langs/interwiki.php:100-109`
- `api_cod/langs/site_matrix.php:62-71`

**Problem**: Identical `filter_last()` function in both files

**Action Items**:
- [ ] Create `api_cod/langs/helpers.php` for shared language utilities
- [ ] Move common functions to helpers
- [ ] Update both files to use shared functions
- [ ] Add tests for filter functions

**Related Duplicates**:
- `filter_data()` - duplicated
- `filter_codes()` - duplicated
- `get_lang_names()` - multiple implementations

---

## 3. Architecture Improvements (P1 - High)

### 3.1 Refactor Monolithic request.php
**Location**: `api_cod/request.php` (535 lines)

**Problem**: Single file handles routing, query building, formatting, caching, and output

**Proposed Structure**:
```
api_cod/
├── Router.php          # Endpoint routing
├── QueryBuilder.php    # SQL query construction
├── Formatter.php       # Response formatting
├── CacheManager.php    # APCu caching logic
└── request.php         # Orchestrator (thin)
```

**Action Items**:
- [ ] Extract routing logic to `Router.php`
- [ ] Extract query building to `QueryBuilder.php`
- [ ] Extract response formatting to `Formatter.php`
- [ ] Extract caching to `CacheManager.php`
- [ ] Keep `request.php` as thin orchestrator

---

### 3.2 Database Class Separation
**Location**: `api_cod/sql.php`

**Problem**: Database class handles both DB operations AND caching

**Action Items**:
- [ ] Create separate `Cache.php` class
- [ ] Move APCu operations to Cache class
- [ ] Remove cache constants from SQL class
- [ ] Implement dependency injection for Cache

---

### 3.3 Configuration Management
**Problem**: Configuration scattered across multiple files

**Current State**:
- `endpoint_params.json` - endpoint definitions
- `langs_table.json` - language data
- Hardcoded values in `sql.php`
- Hardcoded values in `request.php`

**Proposed Structure**:
```
config/
├── database.php       # DB configuration
├── cache.php          # Cache settings
├── endpoints.php      # Loaded from endpoint_params.json
└── languages.php      # Loaded from langs_table.json
```

**Action Items**:
- [ ] Create centralized `Config.php` class
- [ ] Move all magic strings to constants
- [ ] Implement environment-aware config loading
- [ ] Document all configuration options

---

## 4. Code Quality Improvements (P2 - Medium)

### 4.1 Naming Conventions
**Problems**:
- `$qua` vs `$query` (inconsistent)
- `$pa_rams` vs `$params` (typo)
- `$qu_ery` (typo)

**Action Items**:
- [ ] Standardize variable names (`$query`, `$params`)
- [ ] Fix all typos in variable names
- [ ] Create naming convention guide
- [ ] Run linter to enforce conventions

---

### 4.2 Type Safety
**Problem**: No type hints or return types

**Action Items**:
- [ ] Add parameter type hints to all functions
- [ ] Add return type declarations
- [ ] Enable strict types (`declare(strict_types=1);`)
- [ ] Consider using PHPStan for static analysis

---

### 4.3 Constants for Magic Strings
**Problem**: Hardcoded strings throughout code

**Examples**:
```php
$other_tables = ['in_process', 'assessments', 'refs_counts', ...];
```

**Action Items**:
- [ ] Create `api_cod/constants.php`
- [ ] Define table names as constants
- [ ] Define column names as constants
- [ ] Replace magic strings with constants

---

### 4.4 Documentation
**Current State**: Minimal function comments

**Action Items**:
- [ ] Add PHPDoc blocks to all functions
- [ ] Document parameter types
- [ ] Document return types
- [ ] Add examples for complex functions
- [ ] Generate API documentation from PHPDoc

---

## 5. Testing Infrastructure (P1 - High)

### 5.1 Current State
- Interactive test interface in `test/` directory
- No unit tests
- No automated testing
- Manual testing only

### 5.2 Action Items

**Unit Testing**:
- [ ] Install PHPUnit (`composer require phpunit/phpunit`)
- [ ] Create `tests/` directory
- [ ] Write tests for utility functions (filter, validation)
- [ ] Write tests for QueryBuilder
- [ ] Write tests for CacheManager

**Integration Testing**:
- [ ] Create test database fixture
- [ ] Write endpoint integration tests
- [ ] Test SQL query building with real queries
- [ ] Test error handling

**Automated CI**:
- [ ] Add PHPUnit to GitHub Actions workflow
- [ ] Run tests on every pull request
- [ ] Fail build on test failures
- [ ] Add code coverage reporting

**Coverage Goal**: 80%+ for critical paths

---

## 6. Dependency Management (P2 - Medium)

### 6.1 Current State
- No `composer.json`
- No package management
- Using only built-in PHP functions

### 6.2 Action Items
- [ ] Initialize Composer project
- [ ] Add `phpstan/phpstan` for static analysis
- [ ] Add `phpunit/phpunit` for testing
- [ ] Add `vlucas/phpdotenv` for environment variables
- [ ] Add `squizlabs/php_codesniffer` for linting
- [ ] Create `composer.json`
- [ ] Add `.gitignore` entry for `/vendor/`

**Proposed composer.json**:
```json
{
  "require": {
    "php": ">=7.4",
    "vlucas/phpdotenv": "^5.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^9.0",
    "phpstan/phpstan": "^1.0",
    "squizlabs/php_codesniffer": "^3.0"
  },
  "autoload": {
    "psr-4": {
      "TD_API\\": "api_cod/"
    }
  }
}
```

---

## 7. Performance Optimizations (P2 - Medium)

### 7.1 Caching Improvements
**Current**: Basic APCu with manual key management

**Action Items**:
- [ ] Implement cache key generation helper
- [ ] Add cache invalidation strategy
- [ ] Consider cache warming for popular endpoints
- [ ] Add cache hit/miss logging

### 7.2 Query Optimization
**Action Items**:
- [ ] Add EXPLAIN analysis for slow queries
- [ ] Add database indexes documentation
- [ ] Implement query result limiting
- [ ] Add pagination for large result sets

---

## 8. Documentation Improvements (P3 - Low)

### 8.1 README Updates
**Action Items**:
- [ ] Add installation instructions
- [ ] Add environment setup guide
- [ ] Add deployment instructions
- [ ] Document all API endpoints
- [ ] Add troubleshooting section

### 8.2 API Documentation
**Action Items**:
- [ ] Keep OpenAPI spec current
- [ ] Add request/response examples
- [ ] Document authentication (if added)
- [ ] Document rate limits (if added)

---

## 9. Implementation Roadmap

### Phase 1: Security Fixes (Week 1)
1. Move database credentials to environment variables
2. Fix error information disclosure
3. Implement proper input validation
4. Add basic rate limiting

### Phase 2: Code Deduplication (Week 2)
1. Create shared helper for language functions
2. Remove duplicate entry points
3. Consolidate filter functions

### Phase 3: Architecture Refactoring (Weeks 3-4)
1. Extract QueryBuilder from request.php
2. Create CacheManager class
3. Implement Config class
4. Refactor request.php to thin orchestrator

### Phase 4: Testing (Week 5)
1. Set up PHPUnit
2. Write unit tests for utilities
3. Write integration tests for endpoints
4. Add CI/CD testing

### Phase 5: Code Quality (Week 6)
1. Add type hints
2. Standardize naming
3. Add constants
4. Improve documentation

---

## 10. Success Metrics

- [ ] Zero hardcoded credentials
- [ ] Zero security vulnerabilities flagged by static analysis
- [ ] 80%+ test coverage
- [ ] All CI/CD tests passing
- [ ] Maximum function complexity < 10
- [ ] Zero code duplication > 5 lines
- [ ] All functions documented with PHPDoc

---

## Appendix: File-by-File Issues Summary

| File | Lines | Issues | Priority |
|------|-------|--------|----------|
| `api_cod/sql.php` | 277 | Hardcoded credentials, error disclosure | P0 |
| `api_cod/request.php` | 535 | Too long, mixed concerns, SQL injection risk | P0/P1 |
| `api_cod/langs/interwiki.php` | ~150 | Duplicate functions | P1 |
| `api_cod/langs/site_matrix.php` | ~120 | Duplicate functions | P1 |
| `api.php` | 18 | Duplicate of index.php | P1 |
| `index.php` | 18 | Duplicate of api.php | P1 |
| `api_cod/helps.php` | 290 | Needs type hints, documentation | P2 |
| `api_cod/select_helps.php` | ~100 | Needs documentation | P2 |

---

*Last Updated: 2026-01-26*
*Version: 1.0*
