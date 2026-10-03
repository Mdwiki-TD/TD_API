# API Core Engine (`src/app/`)

```
src/app/
├── APIController.php               ← Request orchestrator (~40 lines)
├── bootstrap.php                  ← Environment setup, autoloader & error handling
│
├── Http/
│   ├── Request.php                ← Wrapper over $_GET (replaces scattered filter_input calls)
│   ├── JsonResponse.php           ← Output emitter & JSON response headers
│   └── Environment.php            ← Handles APP_ENV, isLocalhost(), and isDebug()
│
├── Config/
│   └── EndpointConfig.php         ← Loads endpoint_params.json with error handling & redirects
│
├── Endpoints/                     ← Core Application Logic
│   ├── EndpointContext.php
│   ├── QuerySpec.php
│   ├── EndpointHandler.php        ← Interface for all handlers
│   ├── EndpointRegistry.php       ← Registry & redirects table
│   ├── Handlers/
│   │   ├── DefaultTableHandler.php
│   │   ├── CallableHandler.php    ← Adapter for legacy procedural functions
│   │   ├── Pages/     (PagesHandler, PagesWithViewsHandler, PagesByUserOrLangHandler, PagesLangsHandler)
│   │   ├── Views/     (ViewsHandler, UserViewsHandler, LangViewsHandler)
│   │   ├── Users/     (UsersHandler, UsersByLastPupdateHandler, CoordinatorsHandler, CountPagesHandler)
│   │   ├── Stats/     (LeaderboardHandler, GraphDataHandler, StatusHandler, Top*Handler)
│   │   ├── Missing/   (MissingHandler, ExistsHandler, StaticsHandler)
│   │   └── Misc/      (LangsHandler, QidsHandler, InProcessHandler, PublishReportsHandler ...)
│   └── Queries/                   ← Heavy SQL queries (migrated from subs/)
│       ├── MissingExistsQueries.php
│       ├── TitlesInfosQueries.php
│       └── TopQueries.php
│
├── Query/                         ← SQL query building utilities (refactored from helps.php)
│   ├── QueryBuilder.php           ← Handles add_li_params / add_group logic
│   ├── Pagination.php             ← Handles add_limit / add_offset logic
│   ├── Ordering.php               ← Handles add_order / filter_order logic
│   ├── SelectBuilder.php          ← Handles get_select logic
│   └── InputSanitizer.php         ← Handles sanitize_input logic
│
├── Database/
│   ├── Database.php               ← Pure PDO instance (refactored from sql.php)
│   ├── QueryExecutor.php          ← Combines order/limit/offset, handles execution & timing
│   └── Cache/
│       ├── CacheInterface.php
│       ├── ApcuCache.php
│       └── NullCache.php          ← Fallback strategy when APCu is unavailable
│
├── Formatting/
│   ├── ResponseBuilder.php        ← Constructs the unified $out array
│   └── Formatters/ (LeaderboardFormatter, LangsFormatter)
│
└── Legacy/                        ← Temporary folder: Legacy files staged for gradual removal

```
