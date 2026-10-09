<?php
// src/app/Endpoints/EndpointRegistry.php
declare(strict_types=1);

namespace App\Endpoints;

use App\Endpoints\Definition\{EndpointDefinition, EndpointDefinitions};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Handlers\Top\{
    TopLangsHandler,
    TopUsersHandler,
    TopLangOfUsersHandler,
};
use App\Endpoints\Handlers\ByCategory\{
    ExistsByLangAndCategoryHandler,
    ExistsStaticsByCategoryHandler,
    MissingByLangAndCategoryHandler,
    StaticsByCategoryHandler,
};
use App\Endpoints\Handlers\Helpers\{
    DefaultTableHandler,
    StaticSqlHandler,
    FilteredSqlHandler,
};
use App\Endpoints\Handlers\{
    CategoryMembersHandler,
    GraphDataHandler,
    LeaderboardHandler,
    MissingPagesHandler,
    PagesByUserOrLangHandler,
    PagesHandler,
    PagesUsersToMainHandler,
    PagesWithViewsHandler,
    QidsHandler,
    UserDataStatusHandler,
    UsersHandler,
    UserStatusHandler,
    ViewsHandler,
};

final class EndpointRegistry
{

    /** جداول بسيطة: whitelist صريحة (تحل محل OTHER_TABLES و !empty($ctx->data)) */
    private const TABLES = [
        'assessments',
        'refs_counts',
        'enwiki_pageviews',
        'categories',
        'full_translators',
        'users_no_inprocess',
        'projects',
        'settings',
        'translate_type',
        'publish_reports',
    ];


    /** @var array<string, EndpointHandler | DefinedEndpoint> */
    private array $handlers;

    public function __construct()
    {

        $this->handlers = [
            'views'                        => new ViewsHandler(endpoint: 'views', defaultOrder: '1 DESC'),
            'user_views'                   => new ViewsHandler(endpoint: 'user_views', requiredParam: 'user'),
            'lang_views'                   => new ViewsHandler(endpoint: 'lang_views', requiredParam: 'lang'),

            'missing'                      => new MissingPagesHandler(),
            'missing_by_lang_and_category' => new MissingByLangAndCategoryHandler(),

            'exists_by_lang_and_category'  => new ExistsByLangAndCategoryHandler(),
            'exists_statics_by_category'   => new ExistsStaticsByCategoryHandler(),

            'statics_by_category'          => new StaticsByCategoryHandler(),

            'top_langs'                    => new TopLangsHandler(),
            'top_users'                    => new TopUsersHandler(),
            'top_lang_of_users'            => new TopLangOfUsersHandler(),

            'user_status'                  => new UserStatusHandler(),
            'user_data_status'             => new UserDataStatusHandler(),

            'users'                        => new UsersHandler(),
            'category_members'             => new CategoryMembersHandler(),

            'graph_data'                   => new GraphDataHandler(),

            'leaderboard_table'            => new LeaderboardHandler(endpoint: 'leaderboard_table'),
            'leaderboard_table_formated'   => new LeaderboardHandler(endpoint: 'leaderboard_table_formated'),

            'qids'                         => new QidsHandler('qids'),
            'qids_others'                  => new QidsHandler('qids_others'),
            'pages_users_to_main'          => new PagesUsersToMainHandler(),

            'pages_with_views'             => new PagesWithViewsHandler(),
            'pages_by_user_or_lang'        => new PagesByUserOrLangHandler(),

            'pages'                        => new PagesHandler('pages'),
            'pages_users'                  => new PagesHandler('pages_users'),


            'coordinators'                 => new StaticSqlHandler(
                'SELECT id, username, is_active FROM coordinators ORDER BY id',
                applyOrder: false,
            ),

            'langs'                        => new StaticSqlHandler('SELECT code, autonym, name, redirects FROM langs'),

            'users_by_last_pupdate'        => new StaticSqlHandler(
                "WITH RankedPages AS (
                    SELECT p1.target, p1.user, p1.pupdate, p1.lang, p1.title,
                            ROW_NUMBER() OVER (PARTITION BY p1.user ORDER BY p1.pupdate DESC) AS rn
                    FROM pages p1
                    WHERE p1.target != ''
                )
                SELECT target, user, pupdate, lang, title
                FROM RankedPages
                WHERE rn = 1
                ORDER BY pupdate DESC",
                applyOrder: false,
            ),

            'pages_langs'                  => new StaticSqlHandler(
                'SELECT lang, autonym FROM pages p LEFT JOIN langs la ON lang = la.code GROUP BY lang'
            ),
            'pages_users_langs'            => new StaticSqlHandler(
                'SELECT lang, autonym FROM pages_users p LEFT JOIN langs la ON lang = la.code GROUP BY lang'
            ),

            'revids'                       => new FilteredSqlHandler('SELECT title, revid FROM mdwiki_revids'),
            'titles'                       => new FilteredSqlHandler(
                "SELECT
                    ase.title AS title,
                    ase.importance AS importance,
                    rc.r_lead_refs AS r_lead_refs,
                    rc.r_all_refs AS r_all_refs,
                    ep.en_views AS en_views,
                    w.w_lead_words AS w_lead_words,
                    w.w_all_words AS w_all_words,
                    q.qid AS qid
                from
                    assessments ase
                    left join enwiki_pageviews ep   on ep.title   = ase.title
                    left join qids q                on q.title    = ase.title
                    left join refs_counts rc        on rc.r_title = ase.title
                    left join words w               on w.w_title  = ase.title"
            ),

            'language_settings'            => new FilteredSqlHandler('SELECT DISTINCT * FROM language_settings'),

            'words'                        => new FilteredSqlHandler(
                'SELECT w_id, w_title, w_lead_words, w_all_words FROM words'
            ),

            'count_pages'                  => new FilteredSqlHandler(
                'SELECT DISTINCT user, COUNT(target) AS count FROM pages',
                suffix: ' GROUP BY user',
                defaultOrder: 'count DESC',
            ),

            'publish_reports_stats'        => new FilteredSqlHandler(
                'SELECT DISTINCT YEAR(date) AS year, MONTH(date) AS month, lang, user, result
                FROM publish_reports',
                // suffix: ' GROUP BY year, month, lang, user, result',
            ),
            'in_process'                   => new FilteredSqlHandler(
                'SELECT title, user, lang, cat, translate_type, word, add_date,
                        ca.campaign, la.autonym
                FROM in_process
                LEFT JOIN categories ca ON cat = ca.category
                LEFT JOIN langs la ON lang = la.code',
                groupable: true,
            ),

            'get_lang_years'               => new FilteredSqlHandler(
                'SELECT DISTINCT YEAR(p.pupdate) as year
                    FROM pages p
                    LEFT JOIN categories ca ON p.cat = ca.category',
            ),

        ];
        foreach (self::TABLES as $table) {
            $this->handlers[$table] = new DefaultTableHandler($table);
        }
    }

    public function all(): array
    {
        return $this->handlers;
    }

    public function resolve(string $get): ?array
    {
        $handler = $this->handlers[$get] ?? null;
        if ($handler === null) {
            return null;
        }

        // $definition = $handler instanceof DefinedEndpoint ? $handler->definition() : EndpointDefinitions::for($get);
        $definition = $handler->definition() ?? EndpointDefinitions::for($get);

        return $definition ? [$handler, $definition] : null;
    }

    /** @return array<string, EndpointDefinition> للمولّد ولاختبار round-trip */
    public function definitions(): array
    {
        $out = [];
        foreach ($this->handlers as $name => $handler) {
            // $definition = $handler instanceof DefinedEndpoint ? $handler->definition() : EndpointDefinitions::for($name);
            $definition = $handler->definition() ?? EndpointDefinitions::for($name);
            if ($definition !== null) {
                $out[$name] = $definition;
            }

        }
        return $out;
    }
}
