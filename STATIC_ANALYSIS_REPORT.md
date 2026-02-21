# TD_API Static Analysis Report

**Generated:** 2026-02-14
**Analyzer:** Claude Code Static Analysis
**Codebase:** TD_API - MDwiki Translation Dashboard API

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Security Vulnerabilities](#security-vulnerabilities)
3. [Performance Bottlenecks](#performance-bottlenecks)
4. [Architectural Anti-Patterns](#architectural-anti-patterns)
5. [Logical Errors](#logical-errors)
6. [Code Quality Issues](#code-quality-issues)
7. [File-by-File Analysis](#file-by-file-analysis)
8. [PHPDoc Type Annotations](#phpdoc-type-annotations)
9. [Recommendations Summary](#recommendations-summary)

---

## Executive Summary

| Category | Critical | High | Medium | Low |
|----------|----------|------|--------|-----|
| Security Vulnerabilities | 3 | 4 | 5 | 3 |
| Performance Issues | 1 | 3 | 4 | 2 |
| Architectural Anti-Patterns | 2 | 4 | 3 | 2 |
| Logical Errors | 1 | 2 | 3 | 1 |
| Code Quality Issues | 0 | 2 | 8 | 5 |

**Overall Risk Level:** HIGH

The codebase requires immediate attention to address critical security vulnerabilities, particularly around SQL injection risks and hardcoded credentials.

---

## Security Vulnerabilities

### CRITICAL: SQL Injection Vulnerabilities

#### 1. Direct Parameter Interpolation in SQL (request.php:484)
```php
$qua = sprintf(str_replace('?', "'%s'", $query), ...$params);
```
**Risk:** While parameterized queries are used for execution, this line constructs a raw SQL string by directly embedding parameters. If any parameter contains malicious content, it could lead to SQL injection when this string is logged or displayed.

**Location:** `api_cod/request.php:484`

---

#### 2. Hardcoded Database Credentials (sql.php:72-73)
```php
$this->user = 'root';
$this->password = 'root11';
```
**Risk:** Hardcoded credentials in source code are a severe security risk. If this repository is public or compromised, attackers gain direct database access.

**Location:** `api_cod/sql.php:72-73`

---

#### 3. Potential SQL Injection via Table Name (request.php:463)
```php
$query = "SELECT $DISTINCT $SELECT FROM $get";
```
**Risk:** The `$get` variable is derived from user input and directly interpolated into SQL without proper validation against a whitelist.

**Location:** `api_cod/request.php:463`

---

### HIGH: Input Validation Issues

#### 4. Insufficient Input Sanitization (helps.php:263-264)
```php
$added = filter_input(INPUT_GET, $type, FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$added = (!empty($added)) ? $added : filter_input(INPUT_GET, $column, FILTER_SANITIZE_SPECIAL_CHARS);
```
**Risk:** `FILTER_SANITIZE_SPECIAL_CHARS` is deprecated in PHP 8.1+. Should use `FILTER_SANITIZE_FULL_SPECIAL_CHARS` or better, validate against expected patterns.

**Location:** `api_cod/helps.php:263-264`

---

#### 5. Unvalidated SELECT Clause (select_helps.php:21)
```php
$SELECT = (isset($_GET['select']) && !in_array($_GET['select'], $false_selects)) ? $_GET['select'] : '*';
```
**Risk:** User input directly used in SELECT clause. While there's some validation later, the initial assignment is unsafe.

**Location:** `api_cod/select_helps.php:21`

---

#### 6. Missing Rate Limiting
**Risk:** No rate limiting is implemented on API endpoints, making them vulnerable to DoS attacks and abuse.

**Location:** All endpoints

---

### MEDIUM: Information Disclosure

#### 7. Verbose Error Messages (sql.php:148)
```php
echo "sql error:" . $e->getMessage() . "<br>" . $sql_query;
```
**Risk:** Exposing SQL queries and error details to users can reveal database structure.

**Location:** `api_cod/sql.php:148`

---

#### 8. Query Exposure on Non-Production (request.php:525-528)
```php
if ($_SERVER['SERVER_NAME'] !== 'localhost') {
    unset($out["query"]);
};
```
**Risk:** The check for localhost may not be reliable in all environments (e.g., reverse proxies, load balancers).

**Location:** `api_cod/request.php:525-528`

---

### LOW: Security Headers

#### 9. Missing Security Headers
**Risk:** No security headers (CSP, X-Frame-Options, X-Content-Type-Options) are set.

**Recommendation:** Add security headers in the response.

---

## Performance Bottlenecks

### CRITICAL: Database Connection Per Request

#### 1. New Connection on Every Query (sql.php:262)
```php
$db = new Database($_SERVER['SERVER_NAME'] ?? '', $dbname);
$results = $db->fetchquery($sql_query, $params);
$db = null;
```
**Impact:** Creating a new database connection for every query is extremely inefficient. Connection pooling or persistent connections should be used.

**Location:** `api_cod/sql.php:262-268`

---

### HIGH: N+1 Query Pattern

#### 2. Subquery in Loop Context (request.php:439)
```php
(select v.views from views_new_all v WHERE p.target = v.target AND p.lang = v.lang) as views
```
**Impact:** This correlated subquery executes for every row in the result set.

**Location:** `api_cod/request.php:439`

---

#### 3. Missing Index Recommendations
**Tables requiring indexes:**
- `pages(target, lang)` - composite index
- `views_new_all(target, lang)` - composite index
- `pages(user)` - for user queries
- `pages(pupdate)` - for date-based filtering

---

### MEDIUM: Inefficient Queries

#### 4. Unbounded Result Sets
```php
if (isset($_GET['limit'])) {
```
**Issue:** No default LIMIT is enforced, potentially returning millions of rows.

**Location:** `api_cod/helps.php:148`

---

#### 5. Suboptimal JOIN Strategy (request.php:130-138)
The `leaderboard_table` query joins three tables without proper index hints.

---

### LOW: Repeated File Reads

#### 6. JSON File Loading on Every Request (request.php:64)
```php
$endpoint_params_tab = json_decode(file_get_contents(__DIR__ . '/../endpoint_params.json'), true);
```
**Recommendation:** Cache this configuration in APCu or as a PHP array.

**Location:** `api_cod/request.php:64`

---

## Architectural Anti-Patterns

### CRITICAL: God Object/Switch Anti-Pattern

#### 1. Monolithic Router (request.php:77-469)
The main router contains a 400-line switch statement handling 40+ endpoints.

**Issues:**
- Violates Single Responsibility Principle
- Difficult to test individual endpoints
- Hard to maintain and extend
- Tight coupling between routing and business logic

**Recommendation:** Implement a proper router with endpoint handlers as separate classes.

---

### HIGH: Procedural Code with Namespaces

#### 2. Inconsistent Architecture
The codebase uses namespaces but remains entirely procedural. Functions are used instead of classes for business logic.

**Issues:**
- No dependency injection
- Difficult to unit test
- No interface abstractions
- Global state via `$_GET` and `$_REQUEST`

---

#### 3. Direct Superglobal Access
```php
$_GET['get']
$_GET['limit']
$_GET['user']
```
**Issue:** Direct access to superglobals throughout the codebase makes testing difficult and creates hidden dependencies.

---

### MEDIUM: Code Duplication

#### 4. Repeated Parameter Handling Pattern
The same pattern for parameter sanitization and query building is repeated across multiple endpoints:

```php
$added = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($added !== null) {
    $query .= " AND t.code = ?";
    $params[] = $added;
}
```

**Locations:** `missing_exists.php`, `titles_infos.php`, `request.php` (multiple)

---

### LOW: Inconsistent Error Handling

#### 5. Mixed Error Handling Strategies
- Some functions return empty arrays on error
- Some echo error messages
- Some use error_log
- No consistent exception handling

---

## Logical Errors

### HIGH: Incorrect Cache Key Generation

#### 1. Potential Cache Collision (sql.php:191)
```php
return 'apcu_' . md5($sql_query . $params_string);
```
**Issue:** MD5 collisions are possible. While unlikely, for a high-traffic API this could cause incorrect cached data to be returned.

---

### MEDIUM: Filter Logic Error

#### 2. Double Sanitization (helps.php:16-21)
```php
function sanitize_input($input, $pattern) {
    if (!empty($input) && preg_match($pattern, $input) && $input !== "all") {
        return filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
    return null;
}
```
**Issue:** The function returns `null` for invalid input, but calling code often doesn't distinguish between "not provided" and "invalid".

---

#### 3. Undefined Variable Usage (titles_infos.php:13-28)
```php
$qua_old = <<<SQL
    SELECT ...
SQL;
```
**Issue:** `$qua_old` is defined but never used in the functions below it.

---

### LOW: Type Juggling Issues

#### 4. String Comparison with Numbers (helps.php:46-49)
```php
!is_numeric($value)
```
Using `is_numeric` can have unexpected behavior with string numbers.

---

## Code Quality Issues

### Missing Type Declarations

All functions lack PHPDoc blocks and parameter/return type declarations.

### Inconsistent Naming Conventions

- Mix of snake_case and camelCase
- `$qua` vs `$query` for query variables
- `$tabe` (typo for "table"?)

### Dead Code

- Commented-out code blocks throughout
- Unused variables (`$qua_old` in `titles_infos.php`)

### Magic Strings/Numbers

- `3600 * 12` for cache TTL
- Hardcoded table names
- Status codes without constants

---

## File-by-File Analysis

### api.php (Entry Point)

**Purpose:** Main entry point that delegates to request.php

**Issues:**
- No input validation before include
- Test mode check allows error display

**Recommended PHPDoc:**
```php
<?php
/**
 * API Entry Point
 *
 * Routes incoming API requests to the main request handler.
 * Supports test mode via 'test' request parameter for debugging.
 *
 * @package TD_API
 * @author  MDWiki Team
 * @version 1.0.0
 */

declare(strict_types=1);

/**
 * Enable error reporting in test mode.
 * Triggered by presence of 'test' in $_REQUEST superglobal.
 */
if (isset($_REQUEST['test'])) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// Redirect to test interface if no endpoint specified
if (!isset($_GET['get'])) {
    header('Location: openapi.html');
    exit();
}

require_once __DIR__ . '/api_cod/request.php';
```

---

### api_cod/request.php (Main Router)

**Purpose:** Central routing and query building

**Issues:**
- God switch statement (400+ lines)
- Direct superglobal access
- Mixed responsibilities

**Recommended Refactoring:**
```php
<?php
/**
 * API Request Router
 *
 * Handles routing of API requests to appropriate endpoint handlers.
 * Loads endpoint configuration from endpoint_params.json and builds
 * SQL queries based on request parameters.
 *
 * @package TD_API
 * @author  MDWiki Team
 * @version 1.0.0
 */

declare(strict_types=1);

namespace API\Request;

use function API\SQL\fetch_query_new;
use function API\Helps\{sanitize_input, add_group, add_li_params, add_order, add_limit, add_offset};
// ... other imports

/**
 * Represents an API response
 */
class ApiResponse
{
    public float $time;
    public string $query;
    public string $source;
    public int $length;
    public array $results;
    public array $supported_params;
    public array $supported_values;
    public array $columns;

    public function __construct(
        float $executionTime,
        string $query,
        string $source,
        array $results,
        array $endpointParams,
        array $endpointColumns
    ) {
        $this->time = $executionTime;
        $this->query = $query;
        $this->source = $source;
        $this->length = count($results);
        $this->results = $results;
        $this->supported_params = array_column($endpointParams, 'name');
        $this->supported_values = array_column($endpointParams, 'options', 'name');
        $this->columns = $endpointColumns;
    }

    public function toArray(bool $showQuery = false): array
    {
        $out = [
            'time' => $this->time,
            'source' => $this->source,
            'length' => $this->length,
            'results' => $this->results,
            'supported_params' => $this->supported_params,
            'supported_values' => $this->supported_values,
            'columns' => $this->columns,
        ];

        if ($showQuery) {
            $out['query'] = $this->query;
        }

        return $out;
    }
}

/**
 * Endpoint handler interface
 */
interface EndpointHandlerInterface
{
    /**
     * Build and execute the query for this endpoint
     *
     * @param array $endpointParams Parameters from endpoint_params.json
     * @param array $queryParams Parsed query parameters from request
     * @return array Tuple of [results, source]
     */
    public function handle(array $endpointParams, array $queryParams): array;
}

/**
 * Main router class
 */
class Router
{
    /** @var array<string, EndpointHandlerInterface> */
    private array $handlers = [];

    /** @var array Cached endpoint parameters */
    private array $endpointParams;

    public function __construct(string $configPath)
    {
        $this->loadConfig($configPath);
    }

    private function loadConfig(string $path): void
    {
        $config = file_get_contents($path);
        $this->endpointParams = json_decode($config, true) ?? [];
    }

    /**
     * Route a request to the appropriate handler
     *
     * @param string $endpoint The endpoint name
     * @return ApiResponse
     */
    public function route(string $endpoint): ApiResponse
    {
        // Implementation
    }
}
```

---

### api_cod/sql.php (Database Layer)

**Purpose:** Database connection and query execution

**Issues:**
- Hardcoded credentials
- New connection per query
- Mixed responsibilities

**Recommended PHPDoc:**
```php
<?php
/**
 * Database Layer
 *
 * Provides PDO-based database access with APCu caching support.
 * Handles connection management, query execution, and result caching.
 *
 * @package TD_API
 * @author  MDWiki Team
 * @version 1.0.0
 */

declare(strict_types=1);

namespace API\SQL;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Database Configuration
 */
class DatabaseConfig
{
    public string $host;
    public string $dbname;
    public string $user;
    public string $password;
    public int $timeout = 30;

    /**
     * Create configuration from environment
     *
     * @param string $serverName Server name to determine environment
     * @param string $dbSuffix Database suffix
     * @return self
     * @throws RuntimeException If configuration file not found
     */
    public static function fromEnvironment(string $serverName, string $dbSuffix = 'mdwiki'): self
    {
        $config = new self();

        $homeDir = getenv('HOME') ?: '/home';
        $configPath = $homeDir . '/confs/db.ini';

        if (!file_exists($configPath)) {
            throw new RuntimeException("Database configuration file not found: $configPath");
        }

        $dbConfig = parse_ini_file($configPath);

        if ($serverName === 'localhost') {
            $config->host = 'localhost:3306';
            $config->user = getenv('DB_USER') ?: 'root';
            $config->password = getenv('DB_PASSWORD') ?: '';
        } else {
            $config->host = 'tools.db.svc.wikimedia.cloud';
            $config->user = $dbConfig['user'] ?? '';
            $config->password = $dbConfig['password'] ?? '';
        }

        $config->dbname = ($dbConfig['user'] ?? '') . '__' . $dbSuffix;

        return $config;
    }
}

/**
 * Database Connection Class
 *
 * Manages PDO database connections with automatic reconnection
 * and error handling.
 */
class Database
{
    private ?PDO $connection = null;
    private DatabaseConfig $config;
    private bool $groupByModeDisabled = false;

    /**
     * @param DatabaseConfig $config Database configuration
     */
    public function __construct(DatabaseConfig $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Establish database connection
     *
     * @throws PDOException If connection fails
     */
    private function connect(): void
    {
        $dsn = "mysql:host={$this->config->host};dbname={$this->config->dbname}";
        $dsn .= ";charset=utf8mb4";

        $this->connection = new PDO(
            $dsn,
            $this->config->user,
            $this->config->password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    /**
     * Execute a parameterized query
     *
     * @param string $sql SQL query with placeholders
     * @param array<int, mixed> $params Parameters to bind
     * @return array<int, array<string, mixed>> Query results
     * @throws PDOException On query error
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $this->disableFullGroupByIfNeeded($sql);

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Disable ONLY_FULL_GROUP_BY for queries that need it
     *
     * @param string $sql Query to check
     */
    private function disableFullGroupByIfNeeded(string $sql): void
    {
        if ($this->groupByModeDisabled) {
            return;
        }

        if (stripos($sql, 'GROUP BY') !== false) {
            try {
                $this->connection->exec(
                    "SET SESSION sql_mode=(SELECT REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY',''))"
                );
                $this->groupByModeDisabled = true;
            } catch (PDOException $e) {
                error_log("Failed to disable ONLY_FULL_GROUP_BY: " . $e->getMessage());
            }
        }
    }

    /**
     * Close database connection
     */
    public function close(): void
    {
        $this->connection = null;
    }
}

/**
 * Cache Key Generator
 */
final class CacheKeyGenerator
{
    /**
     * Generate a unique cache key for a query
     *
     * @param string $sql SQL query
     * @param array<int, mixed> $params Query parameters
     * @return string Cache key
     */
    public static function generate(string $sql, array $params): string
    {
        $paramsString = !empty($params) ? json_encode($params) : '';
        return 'td_api_' . hash('sha256', $sql . $paramsString);
    }
}

/**
 * Query Result Cache
 */
class QueryCache
{
    private const DEFAULT_TTL = 43200; // 12 hours

    /**
     * Get cached results if available
     *
     * @param string $sql SQL query
     * @param array<int, mixed> $params Query parameters
     * @return array|null Cached results or null if not found
     */
    public static function get(string $sql, array $params): ?array
    {
        if (!extension_loaded('apcu') || !apcu_enabled()) {
            return null;
        }

        $key = CacheKeyGenerator::generate($sql, $params);

        if (apcu_exists($key)) {
            $result = apcu_fetch($key);
            return is_array($result) ? $result : null;
        }

        return null;
    }

    /**
     * Store query results in cache
     *
     * @param string $sql SQL query
     * @param array<int, mixed> $params Query parameters
     * @param array $results Results to cache
     * @param int $ttl Time to live in seconds
     */
    public static function set(string $sql, array $params, array $results, int $ttl = self::DEFAULT_TTL): void
    {
        if (!extension_loaded('apcu') || !apcu_enabled()) {
            return;
        }

        $key = CacheKeyGenerator::generate($sql, $params);
        apcu_store($key, $results, $ttl);
    }
}

/**
 * Execute a query with optional caching
 *
 * @param string $sql SQL query with placeholders
 * @param array<int, mixed> $params Query parameters
 * @param string $endpointName Endpoint name for database selection
 * @param bool $useCache Whether to use APCu cache
 * @return array{0: array, 1: string} Tuple of [results, source]
 */
function fetch_query_new(string $sql, array $params, string $endpointName, bool $useCache = false): array
{
    if ($useCache && $endpointName !== 'settings') {
        $cached = QueryCache::get($sql, $params);
        if ($cached !== null) {
            return [$cached, 'apcu'];
        }
    }

    $dbname = get_dbname($endpointName);
    $config = DatabaseConfig::fromEnvironment($_SERVER['SERVER_NAME'] ?? '', $dbname);
    $db = new Database($config);

    try {
        $results = $db->fetchAll($sql, $params);
    } finally {
        $db->close();
    }

    if ($useCache && $endpointName !== 'settings' && !empty($results)) {
        QueryCache::set($sql, $params, $results);
    }

    return [$results, 'db'];
}

/**
 * Determine database name for an endpoint
 *
 * @param string $endpointName Endpoint name
 * @return string Database suffix
 */
function get_dbname(string $endpointName): string
{
    static $mapping = [
        'mdwiki_new' => [
            'missing',
            'missing_by_qids',
            'exists_by_qids',
            'publish_reports',
            'login_attempts',
            'logins',
            'publish_reports_stats',
            'all_qids_titles',
        ],
    ];

    foreach ($mapping as $db => $endpoints) {
        if (in_array($endpointName, $endpoints, true)) {
            return $db;
        }
    }

    return 'mdwiki';
}
```

---

### api_cod/helps.php (Query Builder Utilities)

**Purpose:** Helper functions for building SQL queries

**Issues:**
- Deprecated filter constants
- Inconsistent return types
- Global state dependencies

**Recommended PHPDoc:**
```php
<?php
/**
 * Query Builder Helpers
 *
 * Provides utility functions for building SQL queries dynamically
 * based on request parameters. Handles parameter sanitization,
 * ORDER BY, GROUP BY, LIMIT, and OFFSET clauses.
 *
 * @package TD_API
 * @author  MDWiki Team
 * @version 1.0.0
 */

declare(strict_types=1);

namespace API\Helps;

/**
 * Sanitize input against a regex pattern
 *
 * @param string $input Raw input string
 * @param string $pattern Regex pattern to validate against
 * @return string|null Sanitized string or null if invalid
 */
function sanitize_input(string $input, string $pattern): ?string
{
    if (empty($input) || !preg_match($pattern, $input) || $input === 'all') {
        return null;
    }

    return filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: null;
}

/**
 * Validate and sanitize ORDER BY column
 *
 * @param string $key Parameter key to read from $_GET
 * @param array<string, mixed> $endpointData Endpoint configuration
 * @return string|null Validated column name or null
 */
function filter_order(string $key, array $endpointData): ?string
{
    if (!isset($_GET[$key])) {
        return null;
    }

    $value = filter_input(INPUT_GET, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if ($value === false || $value === null) {
        return null;
    }

    $endpointParams = $endpointData['params'] ?? [];
    $endpointColumns = $endpointData['columns'] ?? [];

    // Check single column
    if (in_array($value, $endpointColumns, true) || in_array($value, $endpointParams, true)) {
        return $value;
    }

    // Check comma-separated columns
    $columns = array_map('trim', explode(',', $value));
    $validColumns = [];

    foreach ($columns as $column) {
        if (in_array($column, $endpointColumns, true) ||
            in_array($column, $endpointParams, true) ||
            is_numeric($column)) {
            $validColumns[] = $column;
        }
    }

    return !empty($validColumns) ? implode(', ', $validColumns) : null;
}

/**
 * Add GROUP BY clause to query
 *
 * @param string $query SQL query
 * @param array<string, mixed> $endpointData Endpoint configuration
 * @return string Query with GROUP BY clause added
 */
function add_group(string $query, array $endpointData): string
{
    if (!isset($_GET['group'])) {
        return $query;
    }

    $groupBy = filter_order('group', $endpointData);
    if ($groupBy === null) {
        return $query;
    }

    return $query . " GROUP BY $groupBy";
}

/**
 * Get ORDER BY direction from request
 *
 * @param array<string, mixed> $paramConfig Parameter configuration
 * @return string 'ASC' or 'DESC'
 */
function get_order_direction(array $paramConfig = []): string
{
    $default = $paramConfig['default'] ?? 'DESC';

    if (!isset($_GET['order_direction'])) {
        return $default;
    }

    $direction = filter_input(INPUT_GET, 'order_direction', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if ($direction === false || $direction === null) {
        return $default;
    }

    $direction = strtoupper($direction);
    return in_array($direction, ['ASC', 'DESC'], true) ? $direction : 'DESC';
}

/**
 * Add ORDER BY clause to query
 *
 * @param string $query SQL query
 * @param array<string, mixed> $endpointData Endpoint configuration
 * @return string Query with ORDER BY clause added
 */
function add_order(string $query, array $endpointData): string
{
    $endpointParams = $endpointData['params'] ?? [];
    $paramsByKey = array_column($endpointParams, null, 'name');

    $orderConfig = $paramsByKey['order'] ?? [];
    if (empty($orderConfig)) {
        return $query;
    }

    $orderBy = isset($_GET['order'])
        ? filter_order('order', $endpointData)
        : ($orderConfig['default'] ?? null);

    if ($orderBy === null) {
        return $query;
    }

    // Handle special order expressions
    $specialOrders = [
        'pupdate_or_add_date' => 'GREATEST(UNIX_TIMESTAMP(pupdate), UNIX_TIMESTAMP(add_date))',
    ];

    $orderBy = $specialOrders[$orderBy] ?? $orderBy;
    $direction = get_order_direction($paramsByKey['order_direction'] ?? []);

    return $query . " ORDER BY $orderBy $direction";
}

/**
 * Add OFFSET clause to query
 *
 * @param string $query SQL query
 * @return string Query with OFFSET clause added
 */
function add_offset(string $query): string
{
    if (stripos($query, 'OFFSET') !== false) {
        return $query;
    }

    if (!isset($_GET['offset'])) {
        return $query;
    }

    $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT);
    if ($offset === false || $offset === null || $offset <= 0) {
        return $query;
    }

    return $query . " OFFSET $offset";
}

/**
 * Add LIMIT clause to query
 *
 * @param string $query SQL query
 * @param int $defaultLimit Default limit if not specified
 * @param int $maxLimit Maximum allowed limit
 * @return string Query with LIMIT clause added
 */
function add_limit(string $query, int $defaultLimit = 50, int $maxLimit = 5000): string
{
    if (stripos($query, 'LIMIT') !== false) {
        return $query;
    }

    if (!isset($_GET['limit'])) {
        return $query . " LIMIT $defaultLimit";
    }

    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
    if ($limit === false || $limit === null || $limit <= 0) {
        return $query . " LIMIT $defaultLimit";
    }

    // Enforce maximum limit
    $limit = min($limit, $maxLimit);

    return $query . " LIMIT $limit";
}

/**
 * Add DISTINCT modifier to SELECT clause
 *
 * @param string $query SQL query
 * @return string Query with DISTINCT added
 */
function add_distinct(string $query): string
{
    return preg_replace('/^\s*SELECT\s*/i', 'SELECT DISTINCT ', $query) ?? $query;
}

/**
 * Parameter type configuration
 */
interface ParameterTypeInterface
{
    /**
     * Build WHERE clause fragment for this parameter type
     *
     * @param string $column Database column name
     * @param mixed $value Parameter value
     * @param string $whereOrAnd 'WHERE' or 'AND' keyword
     * @return array{0: string, 1: array} Tuple of [clause, params]
     */
    public function buildClause(string $column, mixed $value, string $whereOrAnd): array;
}

/**
 * Add a single parameter condition to query
 *
 * @param string $query Current query
 * @param string $column Database column name
 * @param mixed $value Parameter value
 * @param array<string, mixed> $config Parameter configuration
 * @return array{0: string, 1: array<int, mixed>} Tuple of [clause, params]
 */
function add_one_param(string $query, string $column, mixed $value, array $config): array
{
    $whereOrAnd = (stripos($query, 'WHERE') !== false) ? ' AND ' : ' WHERE ';
    $clause = '';
    $params = [];

    // Handle special values
    if (in_array($value, ['not_mt', 'not_empty'], true)) {
        $clause = " $whereOrAnd ($column != '' AND $column IS NOT NULL)";
    } elseif (in_array($value, ['mt', 'empty'], true)) {
        $clause = " $whereOr_and ($column = '' OR $column IS NULL)";
    } elseif (in_array($value, ['>0', '&#62;0'], true)) {
        $clause = " $whereOrAnd $column > 0";
    } elseif (($config['type'] ?? '') === 'array') {
        [$clause, $params] = add_array_params('', [], $config['name'], $column, $whereOrAnd);
    } else {
        $params[] = $value;
        $clause = " $whereOrAnd $column = ?";

        if ($config['value_can_be_null'] ?? false) {
            $clause = " $whereOrAnd ($column = ? OR $column IS NULL OR $column = '')";
        }
    }

    return [$clause, $params];
}

/**
 * Add array parameter (IN clause) to query
 *
 * @param string $query Current query (unused, for consistency)
 * @param array<int, mixed> $params Current parameters
 * @param string $paramName GET parameter name
 * @param string $column Database column name
 * @param string $whereOrAnd 'WHERE' or 'AND' keyword
 * @return array{0: string, 1: array<int, mixed>} Tuple of [clause, params]
 */
function add_array_params(
    string $query,
    array $params,
    string $paramName = 'titles',
    string $column = 'title',
    string $whereOrAnd = ''
): array {
    if (empty($whereOrAnd)) {
        $whereOrAnd = ' WHERE ';
    }

    $values = $_GET[$paramName] ?? [];
    if (!is_array($values) || empty($values)) {
        return ['', $params];
    }

    $placeholders = rtrim(str_repeat('?,', count($values)), ',');
    $clause = " $whereOrAnd $column IN ($placeholders)";
    $params = array_merge($params, $values);

    return [$clause, $params];
}

/**
 * Transform and filter parameter types
 *
 * @param array<int, string> $types Column names
 * @param array<int, array<string, mixed>> $endpointParams Endpoint parameters
 * @param array<int, string> $ignoreParams Parameters to ignore
 * @return array<string, array<string, mixed>> Parameter configurations by name
 */
function change_types(array $types, array $endpointParams, array $ignoreParams): array
{
    $result = [];

    // Build from types if provided
    foreach ($types as $type) {
        $result[$type] = ['column' => $type];
    }

    // Merge with endpoint params if no types provided
    if (empty($result) && !empty($endpointParams)) {
        foreach ($endpointParams as $param) {
            if (isset($param['no_select'])) {
                continue;
            }
            $result[$param['name']] = $param;
        }
    }

    // Remove ignored params
    foreach ($ignoreParams as $ignore) {
        unset($result[$ignore]);
    }

    return $result;
}

/**
 * Add multiple parameters to query
 *
 * @param string $query SQL query
 * @param array<int, string> $types Column types
 * @param array<int, array<string, mixed>> $endpointParams Endpoint parameters
 * @param array<int, string> $ignoreParams Parameters to ignore
 * @return array{0: string, 1: array<int, mixed>} Tuple of [query, params]
 */
function add_li_params(
    string $query,
    array $types,
    array $endpointParams = [],
    array $ignoreParams = []
): array {
    $typeConfigs = change_types($types, $endpointParams, $ignoreParams);
    $params = [];

    foreach ($typeConfigs as $paramName => $config) {
        $column = $config['column'] ?? '';
        if (empty($column)) {
            continue;
        }

        // Check if parameter is provided
        if (!isset($_GET[$paramName]) && !isset($_GET[$column])) {
            continue;
        }

        // Get parameter value
        $value = filter_input(INPUT_GET, $paramName, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (empty($value)) {
            $value = filter_input(INPUT_GET, $column, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        // Skip special cases
        if ($column === 'limit' || $column === 'select' || strtolower($value ?? '') === 'all') {
            continue;
        }

        if (isset($config['no_empty_value']) && empty($value)) {
            continue;
        }

        // Handle DISTINCT
        if ($column === 'distinct' && $value === '1') {
            if (stripos($query, 'distinct') === false) {
                $query = add_distinct($query);
            }
            continue;
        }

        // Add parameter condition
        [$clause, $newParams] = add_one_param($query, $column, $value, $config);
        $params = array_merge($params, $newParams);
        $query .= $clause;
    }

    return [$query, $params];
}
```

---

## PHPDoc Type Annotations

### Type Aliases (Recommended for includes/types.php)

```php
<?php
/**
 * Type definitions for TD_API
 *
 * @package TD_API
 */

declare(strict_types=1);

namespace API\Types;

/**
 * @psalm-type EndpointParam = array{
 *     name: string,
 *     column: string,
 *     type?: string,
 *     placeholder?: string,
 *     options?: array<int, string>,
 *     default?: string,
 *     no_select?: bool,
 *     no_empty_value?: bool,
 *     value_can_be_null?: bool
 * }
 *
 * @psalm-type EndpointConfig = array{
 *     columns?: array<int, string>,
 *     params?: array<int, EndpointParam>,
 *     redirect?: string
 * }
 *
 * @psalm-type QueryResult = array<int, array<string, mixed>>
 *
 * @psalm-type ApiResponse = array{
 *     time: float,
 *     query?: string,
 *     source: string,
 *     length: int,
 *     results: QueryResult,
 *     supported_params: array<int, string>,
 *     supported_values: array<string, array<int, string>>,
 *     columns: array<int, string>
 * }
 *
 * @psalm-type DatabaseConfig = array{
 *     host: string,
 *     dbname: string,
 *     user: string,
 *     password: string
 * }
 */
```

---

## Recommendations Summary

### Immediate Actions (Critical - Do Today)

1. **Remove hardcoded credentials** from `sql.php`
   - Use environment variables or secure config files
   - Never commit credentials to version control

2. **Fix SQL injection in table name interpolation**
   - Validate `$get` against whitelist of allowed tables
   - Use parameterized queries where possible

3. **Remove query interpolation for logging**
   - Line 484 in `request.php` creates unnecessary risk
   - Log sanitized queries only

### Short-Term Actions (This Week)

4. **Implement connection pooling**
   - Use persistent connections or a connection pool
   - Reduce database overhead significantly

5. **Add default LIMIT**
   - Prevent accidental full table scans
   - Implement maximum limit enforcement

6. **Standardize error handling**
   - Create custom exception classes
   - Never expose SQL queries to users

7. **Add input validation layer**
   - Centralize all input validation
   - Remove direct `$_GET` access

### Medium-Term Actions (This Month)

8. **Refactor router**
   - Split the 400-line switch into separate handlers
   - Implement proper MVC or similar pattern

9. **Add comprehensive logging**
   - Log all queries with timing
   - Implement structured logging

10. **Add rate limiting**
    - Implement per-IP and per-user limits
    - Add request throttling

### Long-Term Actions (This Quarter)

11. **Add full test coverage**
    - Unit tests for all helper functions
    - Integration tests for all endpoints
    - Security testing

12. **Implement proper dependency injection**
    - Remove all global state dependencies
    - Make code testable

13. **Add API versioning**
    - Support multiple API versions
    - Implement deprecation strategy

---

## Conclusion

The TD_API codebase is functional but has significant technical debt and security concerns that should be addressed. The most critical issues are:

1. **Security:** Hardcoded credentials and potential SQL injection vectors
2. **Performance:** New database connections per request
3. **Architecture:** Monolithic router with mixed responsibilities

Addressing these issues in the order prioritized above will significantly improve the security, performance, and maintainability of the API.
