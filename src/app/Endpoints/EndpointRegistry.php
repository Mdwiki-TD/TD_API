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
    CategoryMembersHandler,
    ViewsHandler,
    LeaderboardHandler,
    QidsHandler,
    PagesUsersToMainHandler,
};

use function API\Missing\{
    missing_by_lang_and_category,
    exists_statics_by_category,
    exists_by_lang_and_category,
    statics_by_category,
};
use function API\TitlesInfos\{mdwiki_revids, titles_query};
use function API\Status\make_status_query;
use function API\Top\{top_langs, top_users, top_lang_of_users};

final class EndpointRegistry
{
    /** Simple tables allowed in the default path */
    private const OTHER_TABLES = [
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
        'views',
        'views_new',
        'user_views',
        'user_views2',
        'lang_views',
        'lang_views2',

        'leaderboard_table',
        'leaderboard_table_formated',

        'qids',
        'qids_others',
        'pages_users_to_main',
        'publish_reports_stats',
        'count_pages',
        'language_settings',
        'words',
        'in_process',
    ];

    /** endpoints didn't get moved yet, stay in the old request.php */
    private const LEGACY = [
        'pages',
        'pages_by_user_or_lang',
        'pages_langs',
        'pages_users',
        'pages_users_langs',
        'pages_with_views',
        'publish_reports',
        'user_lang_status',
        'user_status',
        'users_by_last_pupdate',
    ];

    /** @var array<string, EndpointHandler> */
    private array $handlers;

    public function __construct()
    {
        $missing = new CallableHandler(fn($c): array => missing_by_lang_and_category($c->params));

        $views     = new ViewsHandler(defaultOrder: '1 DESC');
        $userViews = new ViewsHandler(requiredParam: 'user');
        $langViews = new ViewsHandler(requiredParam: 'lang');

        $leader    = new LeaderboardHandler();
        $qids = new QidsHandler();

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

            'langs' => new StaticSqlHandler( 'SELECT code, autonym, name, redirects FROM langs' ),

            'graph_data' => new StaticSqlHandler(
                "SELECT LEFT(pupdate, 7) AS m, COUNT(*) AS c
                FROM pages
                WHERE target != ''
                GROUP BY LEFT(pupdate, 7)
                ORDER BY LEFT(pupdate, 7) ASC
                ",
                applyOrder: false,
            ),

            'user_access' => new FilteredSqlHandler( 'SELECT id, user_name, created_at FROM access_keys' ),

            'views'       => $views,
            'views_new'   => $views,
            'user_views'  => $userViews,
            'user_views2' => $userViews,
            'lang_views'  => $langViews,
            'lang_views2' => $langViews,

            'leaderboard_table'          => $leader,
            'leaderboard_table_formated' => $leader,

            'qids'        => $qids,
            'qids_others' => $qids,
            'pages_users_to_main' => new PagesUsersToMainHandler(),

            'language_settings' => new FilteredSqlHandler('SELECT DISTINCT * FROM language_settings'),

            'words' => new FilteredSqlHandler(
                'SELECT w_id, w_title, w_lead_words, w_all_words FROM words'
            ),

            'count_pages' => new FilteredSqlHandler(
                'SELECT DISTINCT user, COUNT(target) AS count FROM pages',
                suffix: ' GROUP BY user',
                defaultOrder: 'count DESC',
            ),

            'publish_reports_stats' => new FilteredSqlHandler(
                'SELECT DISTINCT YEAR(date) AS year, MONTH(date) AS month, lang, user, result
                FROM publish_reports',
                // suffix: ' GROUP BY year, month, lang, user, result',
            ),
            'in_process' => new FilteredSqlHandler(
                'SELECT title, user, lang, cat, translate_type, word, add_date,
                        ca.campaign, la.autonym
                FROM in_process
                LEFT JOIN categories ca ON cat = ca.category
                LEFT JOIN langs la ON lang = la.code',
                groupable: true,
            ),
        ];
    }

    public function isLegacy(string $get): bool
    {
        return in_array($get, self::LEGACY, true);
    }

    /** null = Unknown (error). call isLegacy() before. */
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
