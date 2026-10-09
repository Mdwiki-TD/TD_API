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
use App\Endpoints\Handlers\{
    GraphDataHandler,
    DefaultTableHandler,
    StaticSqlHandler,
    FilteredSqlHandler,
    UsersHandler,
    CategoryMembersHandler,
    ViewsHandler,
    LeaderboardHandler,
    QidsHandler,
    PagesUsersToMainHandler,
    PagesWithViewsHandler,
    PagesByUserOrLangHandler,
    UserStatusHandler,
    PagesHandler,
    UserDataStatusHandler,
};
use App\Endpoints\Handlers\{
    MissingByLangAndCategoryHandler,
    MissingPagesHandler,
    ExistsByLangAndCategoryHandler,
    ExistsStaticsByCategoryHandler,
    StaticsByCategoryHandler,
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


    /** @var array<string, EndpointHandler> */
    private array $handlers;

    public function __construct()
    {

        $views = new ViewsHandler(defaultOrder: '1 DESC');
        $userViews = new ViewsHandler(requiredParam: 'user');
        $langViews = new ViewsHandler(requiredParam: 'lang');

        $leader = new LeaderboardHandler();
        $qids = new QidsHandler();

        $this->handlers = [
            'missing' => new MissingPagesHandler(),
            'missing_by_lang_and_category' => new MissingByLangAndCategoryHandler(),
            'exists_by_lang_and_category' => new ExistsByLangAndCategoryHandler(),
            'exists_statics_by_category' => new ExistsStaticsByCategoryHandler(),
            'statics_by_category' => new StaticsByCategoryHandler(),

            'top_langs' => new TopLangsHandler(),
            'top_users' => new TopUsersHandler(),
            'top_lang_of_users' => new TopLangOfUsersHandler(),

            'revids' => new FilteredSqlHandler('SELECT title, revid FROM mdwiki_revids'),
            'titles' => new FilteredSqlHandler(
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
                    left join words w               on w.w_title  = ase.title
                    "
            ),

            'user_status' => new UserStatusHandler(),
            'user_data_status' => new UserDataStatusHandler(),

            'users' => new UsersHandler(),
            'category_members' => new CategoryMembersHandler(),

            'coordinators' => new StaticSqlHandler(
                'SELECT id, username, is_active FROM coordinators ORDER BY id',
                applyOrder: false,
            ),

            'langs' => new StaticSqlHandler('SELECT code, autonym, name, redirects FROM langs'),

            'graph_data' => new GraphDataHandler(),

            'user_access' => new FilteredSqlHandler('SELECT id, user_name, created_at FROM access_keys'),

            'views' => $views,
            'views_new' => $views,
            'user_views' => $userViews,
            'user_views2' => $userViews,
            'lang_views' => $langViews,
            'lang_views2' => $langViews,

            'leaderboard_table' => $leader,
            'leaderboard_table_formated' => $leader,

            'qids' => $qids,
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
            'pages_with_views' => new PagesWithViewsHandler(),

            'users_by_last_pupdate' => new StaticSqlHandler(
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

            'pages_by_user_or_lang' => new PagesByUserOrLangHandler(),

            'pages_langs' => new StaticSqlHandler(
                'SELECT lang, autonym FROM pages p LEFT JOIN langs la ON lang = la.code GROUP BY lang'
            ),
            'pages_users_langs' => new StaticSqlHandler(
                'SELECT lang, autonym FROM pages_users p LEFT JOIN langs la ON lang = la.code GROUP BY lang'
            ),

            'get_lang_years' => new FilteredSqlHandler(
                'SELECT DISTINCT YEAR(p.pupdate) as year
                    FROM pages p
                    LEFT JOIN categories ca ON p.cat = ca.category',
            ),

            'pages' => new PagesHandler('pages'),
            'pages_users' => new PagesHandler('pages_users'),
        ];
        foreach (self::TABLES as $table) {
            $this->handlers[$table] = new DefaultTableHandler($table);
        }
    }

    /** @return array<string, EndpointHandler> الاسم => handler (aliases تشير لنفس الكائن) */
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

        $definition = $handler instanceof DefinedEndpoint
            ? $handler->definition()
            : EndpointDefinitions::for($get);

        return $definition ? [$handler, $definition] : null;
    }

    /** @return array<string, EndpointDefinition> للمولّد ولاختبار round-trip */
    public function definitions(): array
    {
        $out = [];
        foreach ($this->handlers as $name => $h) {
            $d = $h instanceof DefinedEndpoint ? $h->definition() : EndpointDefinitions::for($name);
            if ($d !== null) {
                $out[$name] = $d;
            }

        }
        return $out;
    }
}
