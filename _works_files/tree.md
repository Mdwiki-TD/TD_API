```
src/
├── app/
│   ├── Database/
│   │   ├── Cache/
│   │   │   ├── ApcuCache.php
│   │   │   ├── CacheInterface.php
│   │   │   └── NullCache.php
│   │   ├── Database.php
│   │   ├── DatabaseException.php
│   │   └── QueryExecutor.php
│   ├── Endpoints/
│   │   ├── Definition/
│   │   │   ├── EndpointDefinition.php
│   │   │   ├── EndpointDefinitions.php
│   │   │   └── Param.php
│   │   ├── Handlers/
│   │   │   ├── ByCategory/
│   │   │   │   ├── CategoryLangHandler.php
│   │   │   │   ├── ExistsByLangAndCategoryHandler.php
│   │   │   │   ├── ExistsStaticsByCategoryHandler.php
│   │   │   │   ├── MissingByLangAndCategoryHandler.php
│   │   │   │   └── StaticsByCategoryHandler.php
│   │   │   ├── Helpers/
│   │   │   │   ├── DefaultTableHandler.php
│   │   │   │   ├── FilteredSqlHandler.php
│   │   │   │   └── StaticSqlHandler.php
│   │   │   ├── Top/
│   │   │   │   ├── TopHandler.php
│   │   │   │   ├── TopLangOfUsersHandler.php
│   │   │   │   ├── TopLangsHandler.php
│   │   │   │   └── TopUsersHandler.php
│   │   │   ├── CategoryMembersHandler.php
│   │   │   ├── GraphDataHandler.php
│   │   │   ├── LeaderboardHandler.php
│   │   │   ├── MissingPagesHandler.php
│   │   │   ├── PagesByUserOrLangHandler.php
│   │   │   ├── PagesHandler.php
│   │   │   ├── PagesUsersToMainHandler.php
│   │   │   ├── PagesWithViewsHandler.php
│   │   │   ├── QidsHandler.php
│   │   │   ├── UserDataStatusHandler.php
│   │   │   ├── UsersHandler.php
│   │   │   ├── UserStatusHandler.php
│   │   │   └── ViewsHandler.php
│   │   ├── Queries/
│   │   ├── DefinedEndpoint.php
│   │   ├── EndpointContext.php
│   │   ├── EndpointHandler.php
│   │   ├── EndpointRegistry.php
│   │   └── QuerySpec.php
│   ├── Formatting/
│   │   ├── GraphDataFormatter.php
│   │   ├── LangsFormatter.php
│   │   ├── LeaderboardFormatter.php
│   │   ├── ResponseBuilder.php
│   │   └── UserDataStatusFormatter.php
│   ├── Http/
│   │   ├── Environment.php
│   │   └── Request.php
│   ├── OpenApi/
│   │   ├── endpoint_docs.php
│   │   ├── OpenApiBuilder.php
│   │   └── OpenApiCatalog.php
│   ├── Query/
│   │   ├── FilterBuilder.php
│   │   ├── InputSanitizer.php
│   │   ├── Ordering.php
│   │   ├── Pagination.php
│   │   └── SelectBuilder.php
│   ├── APIController.php
│   ├── bootstrap.php
│   ├── Logger.php
│   └── README.md
├── Legacy/
│   ├── subs/
│   │   ├── missing_exists.php
│   │   ├── titles_infos.php
│   │   └── top.php
│   ├── AddParams.php
│   ├── bootstrap.php
│   ├── Helps.php
│   ├── Leaderboard.php
│   ├── LegacyController.php
│   ├── Qids.php
│   ├── SelectHelps.php
│   └── Sql.php
├── api.php
├── bootstrap.php
├── index.php
├── openapi.json
├── README.md
└── request.php

```