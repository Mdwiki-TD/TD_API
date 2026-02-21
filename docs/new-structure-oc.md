# Proposed Modern Directory Structure for TD_API

## Executive Summary

This document proposes a modern, scalable directory structure for the TD_API PHP application. The current codebase, while functional, has several architectural limitations that hinder long-term maintainability, testing, and deployment efficiency. This proposal adopts industry best practices including layered architecture, separation of concerns, and clear boundaries between public assets, application logic, and configuration.

---

## Current Structure Analysis

### Existing Organization

```text
TD_API/
├── api_cod/           # Core application code (mixed concerns)
│   ├── langs/         # Language-related functionality
│   ├── subs/          # Sub-modules
│   ├── include.php    # Global includes
│   ├── request.php    # Main request handler (536 lines)
│   ├── sql.php        # Database layer (278 lines)
│   └── ...
├── api/               # Proxy endpoint
├── test/              # Frontend test files
├── test2/             # Additional test files
├── x/                 # Utility scripts
├── index.php          # Entry point
├── api.php            # API entry point (duplicate)
├── endpoint_params.json  # Configuration
└── openapi.json       # API documentation
```

### Identified Issues

1. **Mixed Concerns**: Business logic, database queries, and HTTP handling are intermixed in single files
2. **No Clear Boundaries**: No separation between public web root and application code
3. **Configuration Sprawl**: Configuration scattered across JSON files and hardcoded values
4. **Testing Infrastructure**: Test files mixed with source code; no unit test framework
5. **Namespace Inconsistency**: Only `sql.php` uses namespaces; other files use global functions
6. **Documentation**: Limited inline documentation; no clear architecture documentation
7. **No Vendor Isolation**: No clear distinction between application code and third-party dependencies

---

## Proposed Directory Structure

```
td_api/
├── bin/                          # Executable scripts and CLI tools
│   └── console                   # CLI entry point (migrations, seeds, etc.)
│
├── config/                       # Configuration files
│   ├── app.php                   # Application configuration
│   ├── database.php              # Database configuration
│   ├── cache.php                 # Caching configuration (APCu)
│   ├── routes.php                # Route definitions
│   └── endpoints/                # Endpoint parameter definitions
│       └── params.php            # Converted from endpoint_params.json
│
├── docs/                         # Documentation
│   ├── api/                      # API documentation
│   ├── architecture/             # Architecture decision records (ADRs)
│   └── development/              # Development guides
│
├── public/                       # Web server document root (ONLY this dir exposed)
│   ├── index.php                 # Front controller (replaces index.php & api.php)
│   ├── .htaccess                 # Apache rewrite rules
│   ├── swagger-ui/               # API documentation UI
│   │   └── index.html            # (moved from test.html)
│   └── openapi.json              # API spec (symlink or copy from docs/)
│
├── resources/                    # Non-code resources
│   ├── lang/                     # Language/translation files
│   │   ├── en.json               # English translations
│   │   └── ar.json               # Arabic translations
│   └── schemas/                  # JSON schemas, OpenAPI specs
│       └── openapi.json
│
├── src/                          # Application source code (PSR-4 autoloaded)
│   ├── Domain/                   # Domain layer (business logic)
│   │   ├── Entities/             # Domain entities
│   │   │   ├── Page.php
│   │   │   ├── User.php
│   │   │   └── Translation.php
│   │   ├── ValueObjects/         # Value objects
│   │   │   ├── Language.php
│   │   │   └── Qid.php
│   │   ├── Repositories/         # Repository interfaces (contracts)
│   │   │   ├── PageRepositoryInterface.php
│   │   │   └── UserRepositoryInterface.php
│   │   └── Services/             # Domain services
│   │       ├── TranslationService.php
│   │       └── StatisticsService.php
│   │
│   ├── Application/              # Application layer (use cases)
│   │   ├── DTOs/                 # Data Transfer Objects
│   │   │   ├── PageDTO.php
│   │   │   └── LeaderboardDTO.php
│   │   ├── Actions/              # Use case handlers (CQRS-style)
│   │   │   ├── GetPagesAction.php
│   │   │   ├── GetLeaderboardAction.php
│   │   │   └── GetTranslationStatusAction.php
│   │   ├── Queries/              # Query handlers
│   │   │   ├── GetTopLanguagesQuery.php
│   │   │   └── GetMissingPagesQuery.php
│   │   └── Validators/           # Input validation
│   │       └── RequestValidator.php
│   │
│   ├── Infrastructure/           # Infrastructure layer
│   │   ├── Database/             # Database implementations
│   │   │   ├── DatabaseFactory.php
│   │   │   ├── PDOConnection.php
│   │   │   ├── Repositories/     # Concrete repository implementations
│   │   │   │   ├── SqlPageRepository.php
│   │   │   │   └── SqlUserRepository.php
│   │   │   └── QueryBuilders/    # SQL query building
│   │   │       ├── PageQueryBuilder.php
│   │   │       └── UserQueryBuilder.php
│   │   ├── Cache/                # Caching implementations
│   │   │   ├── CacheInterface.php
│   │   │   └── ApcuCache.php
│   │   ├── Http/                 # HTTP layer
│   │   │   ├── Controllers/      # HTTP controllers
│   │   │   │   ├── ApiController.php
│   │   │   │   └── PagesController.php
│   │   │   ├── Middleware/       # HTTP middleware
│   │   │   │   ├── CorsMiddleware.php
│   │   │   │   ├── RateLimitMiddleware.php
│   │   │   │   └── AuthMiddleware.php
│   │   │   ├── Request.php       # HTTP Request wrapper
│   │   │   └── Response.php      # HTTP Response wrapper
│   │   ├── External/             # External API integrations
│   │   │   └── MediaWikiApi.php
│   │   └── Logging/              # Logging infrastructure
│   │       └── Logger.php
│   │
│   └── Shared/                   # Shared kernel (common utilities)
│       ├── Sanitizers.php        # Input sanitization (refactored from helps.php)
│       ├── ArrayHelpers.php      # Array utilities
│       └── StringHelpers.php     # String utilities
│
├── tests/                        # Test suites
│   ├── Unit/                     # Unit tests (domain layer)
│   │   ├── Domain/
│   │   │   ├── Entities/
│   │   │   └── Services/
│   │   └── Application/
│   ├── Integration/              # Integration tests (infrastructure)
│   │   ├── Database/
│   │   └── Repositories/
│   ├── Functional/               # Functional/API tests
│   │   └── Api/
│   ├── Fixtures/                 # Test data fixtures
│   └── bootstrap.php             # Test bootstrap
│
├── var/                          # Runtime files (git-ignored)
│   ├── cache/                    # Application cache
│   ├── logs/                     # Application logs
│   └── temp/                     # Temporary files
│
├── vendor/                       # Composer dependencies (git-ignored)
│
├── composer.json                 # Composer dependencies and autoloading
├── composer.lock                 # Locked dependencies
├── phpunit.xml                   # PHPUnit configuration
├── .env.example                  # Environment template
├── .env                          # Environment variables (git-ignored)
└── README.md                     # Project documentation
```

---

## Architectural Layers Explained

### 1. Domain Layer (`src/Domain/`)

**Purpose**: Contains the core business logic, independent of any external frameworks or infrastructure.

**Key Components**:
- **Entities**: Objects with identity (Page, User, Translation)
- **Value Objects**: Immutable objects without identity (Language, Qid)
- **Repository Interfaces**: Contracts defining data access requirements
- **Domain Services**: Complex business operations that don't fit in entities

**Benefits**:
- Business logic is isolated and testable without database
- Changes to infrastructure don't affect business rules
- Clear boundaries make the codebase easier to understand

**Migration Example**:
```php
// Current approach (api_cod/request.php)
switch ($get) {
    case 'pages':
        $qua = "SELECT $DISTINCT $SELECT FROM $get p";
        // ... 50 lines of query building
}

// Proposed approach (src/Application/Actions/GetPagesAction.php)
class GetPagesAction {
    public function execute(GetPagesRequest $request): PagesCollection {
        $criteria = new PageCriteria($request->getFilters());
        return $this->pageRepository->findByCriteria($criteria);
    }
}
```

### 2. Application Layer (`src/Application/`)

**Purpose**: Orchestrates use cases by coordinating domain objects. No business rules here, only coordination.

**Key Components**:
- **Actions/Commands**: Handle write operations
- **Queries**: Handle read operations (CQRS pattern)
- **DTOs**: Data structures for crossing layer boundaries
- **Validators**: Input validation and sanitization

**Benefits**:
- Clear entry points for each use case
- Request validation separated from business logic
- Easier to add new features without modifying existing code

### 3. Infrastructure Layer (`src/Infrastructure/`)

**Purpose**: Contains all technical details and external concerns.

**Key Components**:
- **Database**: Concrete repository implementations, connection management
- **Cache**: APCu and other caching implementations
- **Http**: Controllers, middleware, request/response handling
- **External**: Third-party API integrations

**Benefits**:
- Technical details are isolated
- Easy to swap implementations (e.g., APCu → Redis)
- Database schema changes only affect repository implementations

### 4. Shared Kernel (`src/Shared/`)

**Purpose**: Common utilities used across all layers.

**Migration Path**:
- Move `api_cod/helps.php` functions to `src/Shared/Sanitizers.php`
- Refactor into static utility classes with clear responsibilities
- Maintain backward compatibility with gradual migration

---

## Key Improvements

### 1. Security Enhancement: Public Directory Isolation

**Current Risk**: All files in the web root are accessible via HTTP, including `api_cod/sql.php` which contains database credentials.

**Solution**:
```
# Apache/Nginx configuration
DocumentRoot /var/www/td_api/public

# All other directories (src/, config/, etc.) are outside web root
```

**Benefits**:
- Source code files are not directly accessible
- Configuration files protected from HTTP access
- Prevents accidental exposure of sensitive data

### 2. Configuration Management

**Current Approach**:
```php
// Hardcoded in sql.php
$ts_mycnf = parse_ini_file($this->home_dir . "/confs/db.ini");
```

**Proposed Approach**:
```php
// config/database.php
return [
    'default' => $_ENV['DB_CONNECTION'] ?? 'mysql',
    'connections' => [
        'mysql' => [
            'host' => $_ENV['DB_HOST'],
            'database' => $_ENV['DB_DATABASE'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
        ],
        'mdwiki_new' => [
            // Separate connection for specific tables
        ],
    ],
];
```

**Benefits**:
- Environment-specific configuration via `.env` files
- No hardcoded credentials in source code
- Easy to manage different environments (dev, staging, production)

### 3. Autoloading and Namespaces

**Current**: Manual includes in `api_cod/include.php`
```php
include_once __DIR__ . '/helps.php';
include_once __DIR__ . '/sql.php';
// ... 12 more includes
```

**Proposed**: PSR-4 autoloading via Composer
```json
{
    "autoload": {
        "psr-4": {
            "TDApi\\": "src/"
        }
    }
}
```

**Benefits**:
- No manual include/require statements
- Class loading on-demand
- Namespace-based organization

### 4. Testing Infrastructure

**Current**: Frontend test files (`test/`, `test2/`) mixed with source

**Proposed**: Comprehensive test suite
```
tests/
├── Unit/              # Fast, isolated tests for domain logic
├── Integration/       # Database interaction tests
└── Functional/        # Full HTTP request/response tests
```

**Benefits**:
- Regression prevention
- Confidence when refactoring
- Documentation via tests
- PHPUnit integration for CI/CD

### 5. API Documentation

**Current**: `test.html` at root, `openapi.json` separate

**Proposed**: Organized documentation
```
docs/
├── api/               # API usage guides
├── architecture/      # Architecture Decision Records
└── development/       # Setup and contribution guides

public/swagger-ui/     # Interactive documentation
resources/schemas/     # OpenAPI JSON specs
```

---

## Migration Strategy

### Phase 1: Foundation (Week 1-2)

1. **Create new directory structure**
2. **Set up Composer with PSR-4 autoloading**
3. **Create `public/` directory and move entry points**
4. **Set up environment configuration (.env)**
5. **Create basic configuration files**

### Phase 2: Extract Infrastructure (Week 3-4)

1. **Move database code** to `src/Infrastructure/Database/`
2. **Implement Repository pattern** for existing tables
3. **Create Cache abstraction** (wrap existing APCu usage)
4. **Set up HTTP layer** with Request/Response objects
5. **Move shared utilities** to `src/Shared/`

### Phase 3: Domain Layer (Week 5-6)

1. **Identify core entities** from existing queries
2. **Create entity classes** (Page, User, Translation, etc.)
3. **Define repository interfaces**
4. **Extract business logic** from request.php switch cases

### Phase 4: Application Layer (Week 7-8)

1. **Create Action classes** for each endpoint
2. **Implement input validation** (replace sanitize_input calls)
3. **Create DTOs** for data transformation
4. **Connect Actions to Repositories**

### Phase 5: Testing & Documentation (Week 9-10)

1. **Write unit tests** for domain layer
2. **Write integration tests** for repositories
3. **Write functional tests** for API endpoints
4. **Update documentation** and OpenAPI specs
5. **Deprecate old files** with backward compatibility layer

### Phase 6: Cleanup (Week 11-12)

1. **Remove old files** (`api_cod/`, duplicate entry points)
2. **Update deployment scripts**
3. **Performance testing**
4. **Production rollout**

---

## File Mapping: Current → Proposed

| Current Location | Proposed Location | Rationale |
|-----------------|-------------------|-----------|
| `api_cod/request.php` | `src/Infrastructure/Http/Controllers/ApiController.php` | HTTP handling belongs in infrastructure |
| `api_cod/sql.php` | `src/Infrastructure/Database/` | Database access is infrastructure concern |
| `api_cod/helps.php` | `src/Shared/Sanitizers.php`, `src/Application/Validators/` | Split into shared utilities and validators |
| `api_cod/QueryBuilder.php` | `src/Infrastructure/Database/QueryBuilders/` | Specific builders for each entity |
| `api_cod/langs/*` | `src/Domain/ValueObjects/Language.php`, `resources/lang/` | Language as value object + translations |
| `api_cod/subs/*` | `src/Infrastructure/Database/QueryBuilders/` | Query building logic |
| `api_cod/leaderboard.php` | `src/Application/Actions/GetLeaderboardAction.php` | Use case in application layer |
| `api_cod/status.php` | `src/Application/Queries/GetTranslationStatusQuery.php` | CQRS query |
| `endpoint_params.json` | `config/endpoints/params.php` | PHP config for better caching |
| `index.php`, `api.php` | `public/index.php` | Single entry point with routing |
| `test.html` | `public/swagger-ui/index.html` | Organized documentation |
| `test/`, `test2/` | `tests/Functional/`, `public/assets/` | Separate test suite from frontend assets |
| `x/` | `bin/`, `var/temp/` | Scripts in bin/, temp files in var/ |

---

## Deployment Configuration

### Apache VirtualHost

```apache
<VirtualHost *:80>
    ServerName mdwiki.toolforge.org
    DocumentRoot /var/www/td_api/public

    <Directory /var/www/td_api/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Protect non-public directories
    <Directory /var/www/td_api>
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteRule ^(src|config|tests|var|vendor)/ - [F,L]
        </IfModule>
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/td_api_error.log
    CustomLog ${APACHE_LOG_DIR}/td_api_access.log combined
</VirtualHost>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name mdwiki.toolforge.org;
    root /var/www/td_api/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive directories
    location ~ ^/(src|config|tests|var|vendor)/ {
        deny all;
        return 404;
    }

    # Static file caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

---

## Benefits Summary

### Maintainability
- **Clear separation of concerns**: Each layer has a single responsibility
- **Easy to navigate**: Developers know exactly where to find code
- **Reduced cognitive load**: Smaller, focused classes instead of large files

### Testability
- **Domain layer**: Unit testable without database
- **Infrastructure layer**: Mockable interfaces for testing
- **Integration tests**: Test database queries in isolation

### Scalability
- **Horizontal scaling**: Stateless design allows multiple servers
- **Caching strategy**: Centralized cache layer easy to optimize
- **Database optimization**: Repository pattern allows query optimization without affecting business logic

### Security
- **Defense in depth**: Web root isolation prevents source code exposure
- **Input validation**: Centralized validation layer
- **Environment isolation**: Configuration separated from code

### Developer Experience
- **IDE support**: PSR-4 autoloading enables better autocomplete
- **Type safety**: Gradual introduction of type hints
- **Documentation**: Clear architecture boundaries serve as documentation

---

## Conclusion

This proposed structure transforms TD_API from a functional but tightly-coupled codebase into a modern, maintainable PHP application. While the migration requires significant upfront effort, the long-term benefits in maintainability, testing, and team productivity justify the investment.

The layered architecture ensures that business logic remains stable while infrastructure details can evolve. The clear separation between public and private code enhances security. The comprehensive test structure prevents regressions and enables confident refactoring.

**Recommended Priority**: High - The current security risk of exposed source files and the maintainability benefits make this migration critical for the project's long-term health.
