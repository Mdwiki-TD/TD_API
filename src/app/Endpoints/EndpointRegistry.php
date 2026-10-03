<?php
// src/app/Endpoints/EndpointRegistry.php
declare(strict_types=1);

namespace App\Endpoints;

use App\Endpoints\Handlers\{
    CallableHandler,
    DefaultTableHandler,
    StaticSqlHandler,
    FilteredSqlHandler,
    UsersHandler,
    CategoryMembersHandler
};
use function API\Missing\{
    missing_by_lang_and_category,
    exists_statics_by_category,
    exists_by_lang_and_category,
    statics_by_category
};
use function API\TitlesInfos\{mdwiki_revids, titles_query};
use function API\Status\make_status_query;
use function API\Top\{top_langs, top_users, top_lang_of_users};

final class EndpointRegistry
{
    /** جداول بسيطة مسموحة في المسار الافتراضي */
    private const OTHER_TABLES = [
        'in_process_x_placeholder', // in_process له case خاص، انظر LEGACY
        'assessments',
        'refs_counts',
        'enwiki_pageviews',
        'categories',
        'full_translators',
        'users_no_inprocess',
        'projects',
        'settings',
        'translate_type',
    ];

    public const LEGACY_DEPRECATED = [
        'category_members',
        'coordinators',
        'graph_data',
        'langs',
        'user_access',
        'users',
    ];

    /** endpoints لم تُنقل بعد، تبقى في request.php القديم */
    private const LEGACY = [
        'count_pages',
        'in_process',
        'lang_views',
        'lang_views2',
        'language_settings',
        'leaderboard_table',
        'leaderboard_table_formated',
        'pages',
        'pages_by_user_or_lang',
        'pages_langs',
        'pages_users',
        'pages_users_langs',
        'pages_users_to_main',
        'pages_with_views',
        'publish_reports',
        'publish_reports_stats',
        'qids',
        'qids_others',
        'user_lang_status',
        'user_status',
        'user_views',
        'user_views2',
        'users_by_last_pupdate',
        'views',
        'views_new',
        'words',
    ];

    /** @var array<string, EndpointHandler> */
    private array $handlers;

    public function __construct()
    {
        $missing = new CallableHandler(fn($c): array => missing_by_lang_and_category($c->params));

        $this->handlers = [
            'missing'                      => $missing,
            'missing_by_lang_and_category' => $missing,
            'exists_statics_by_category'   => new CallableHandler(fn($c): array => exists_statics_by_category($c->params)),
            'exists_by_lang_and_category'  => new CallableHandler(fn($c): array => exists_by_lang_and_category($c->params)),
            'statics_by_category'          => new CallableHandler(fn($c): array => statics_by_category($c->params)),
            'revids'                       => new CallableHandler(fn($c): array => mdwiki_revids($c->params)),
            'titles'                       => new CallableHandler(fn($c): array => titles_query($c->params)),
            'status'                       => new CallableHandler(fn($c): array => make_status_query($c->params)),
            'top_langs'                    => new CallableHandler(fn($c): array => top_langs($c->params)),
            'top_users'                    => new CallableHandler(fn($c): array => top_users($c->params)),
            'top_lang_of_users'            => new CallableHandler(fn($c): array => top_lang_of_users($c->params)),

            'users'            => new UsersHandler(),
            'category_members' => new CategoryMembersHandler(),

            'coordinators' => new StaticSqlHandler(
                'SELECT id, username, is_active FROM coordinators ORDER BY id',
                applyOrder: false,
            ),

            'langs' => new StaticSqlHandler(
                'SELECT code, autonym, name, redirects FROM langs'
            ),

            'graph_data' => new StaticSqlHandler(
                "SELECT LEFT(pupdate, 7) AS m, COUNT(*) AS c
                FROM pages
                WHERE target != ''
                GROUP BY LEFT(pupdate, 7)
                ORDER BY LEFT(pupdate, 7) ASC",
                applyOrder: false,
            ),

            'user_access' => new FilteredSqlHandler(
                'SELECT id, user_name, created_at FROM access_keys'
            ),
        ];
    }

    public function isLegacy(string $get): bool
    {
        return in_array($get, self::LEGACY, true);
    }

    /** null = غير معروف (خطأ). استدعِ isLegacy() قبلها. */
    public function resolve(EndpointContext $ctx): ?EndpointHandler
    {
        if (isset($this->handlers[$ctx->get])) {
            return $this->handlers[$ctx->get];
        }
        if (in_array($ctx->get, self::OTHER_TABLES, true) || !empty($ctx->data)) {
            return new DefaultTableHandler();
        }
        return null;
    }
}
