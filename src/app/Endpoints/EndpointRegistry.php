<?php
// src/app/Endpoints/EndpointRegistry.php
declare(strict_types=1);

namespace App\Endpoints;

use App\Endpoints\Handlers\{
    GraphDataHandler,
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
    PagesWithViewsHandler,
    PagesByUserOrLangHandler,
    UserStatusHandler,
    PagesHandler,
    UserDataStatusHandler,
};

use function API\Missing\{
    missing_by_lang_and_category,
    exists_statics_by_category,
    exists_by_lang_and_category,
    statics_by_category,
};
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
        'pages_with_views',

        'get_lang_years',
        'user_status',
        'pages_by_user_or_lang',
        'users_by_last_pupdate',
        'pages_users_langs',
        'pages_langs',
        'pages',
        'pages_users',
    ];

    /** @var array<string, EndpointHandler> */
    private array $handlers;

    public function __construct()
    {
        $missing = new CallableHandler(
            fn($c): array => missing_by_lang_and_category($c->params),
            params: [
                ["name" => "lang", "column" => "t.code", "type" => "text", "placeholder" => "Language code"],
                ["name" => "category", "column" => "a.category", "type" => "text", "placeholder" => "Category"],
                ["name" => "order", "column" => "order", "type" => "text", "placeholder" => "Order by", "no_select" => true],
            ]
        );

        $views = new ViewsHandler(defaultOrder: '1 DESC');
        $userViews = new ViewsHandler(requiredParam: 'user');
        $langViews = new ViewsHandler(requiredParam: 'lang');

        $leader = new LeaderboardHandler();
        $qids = new QidsHandler();

        $this->handlers = [
            'publish_reports' => new FilteredSqlHandler(
                'SELECT DISTINCT date, title, user, lang, sourcetitle, result, data FROM publish_reports',
                columns: ["date", "title", "user", "lang", "sourcetitle", "result", "data"],
                params: [
                    ["name" => "year", "column" => "YEAR(date)", "type" => "number", "placeholder" => "year of date"],
                    ["name" => "month", "column" => "MONTH(date)", "type" => "number", "placeholder" => "month of date"],
                    ["name" => "title", "column" => "title", "type" => "text", "placeholder" => "Page Title"],
                    ["name" => "user", "column" => "user", "type" => "text", "placeholder" => "user"],
                    ["name" => "lang", "column" => "lang", "type" => "text", "placeholder" => "Language code"],
                    ["name" => "sourcetitle", "column" => "sourcetitle", "type" => "text", "placeholder" => "sourcetitle"],
                    ["name" => "result", "column" => "result", "type" => "text", "placeholder" => "result"],
                    ["name" => "select", "column" => "select", "type" => "text", "placeholder" => "Select fields", "no_select" => true],
                    ["name" => "distinct", "column" => "distinct", "type" => "switch", "no_select" => true],
                ]
            ),
            'language_settings' => new FilteredSqlHandler(
                'SELECT DISTINCT * FROM language_settings',
                columns: ["lang_code", "move_dots", "expend", "add_en_lang"],
                params: [
                    ["name" => "lang_code", "column" => "lang_code", "type" => "text", "placeholder" => "Language code"],
                ]
            ),
            'publish_reports_stats' => new FilteredSqlHandler(
                'SELECT DISTINCT YEAR(date) AS year, MONTH(date) AS month, lang, user, result FROM publish_reports',
                params: [
                    ["name" => "lang", "column" => "lang", "type" => "text", "placeholder" => "Language code"],
                    ["name" => "user", "column" => "user", "type" => "text", "placeholder" => "Username"],
                ]
            ),
            'missing'                      => $missing,
            'missing_by_lang_and_category' => $missing,
            'exists_statics_by_category'   => new CallableHandler(
                fn($c): array => exists_statics_by_category($c->params),
                params: [
                    ["name" => "category", "column" => "a.category", "type" => "text", "placeholder" => "Category", "default" => "RTT", "required" => true],
                ]
            ),
            'exists_by_lang_and_category'  => new CallableHandler(
                fn($c): array => exists_by_lang_and_category($c->params),
                params: [
                    ["name" => "lang", "column" => "t.code", "type" => "text", "placeholder" => "Language code", "required" => true],
                    ["name" => "category", "column" => "a.category", "type" => "text", "placeholder" => "Category", "default" => "RTT"],
                ]
            ),
            'statics_by_category'          => new CallableHandler(
                fn($c): array => statics_by_category($c->params),
                params: [
                    ["name" => "lang", "column" => "t.code", "type" => "text", "placeholder" => "Language code", "required" => true],
                    ["name" => "category", "column" => "a.category", "type" => "text", "placeholder" => "Category", "default" => "RTT"],
                ]
            ),
            'revids' => new FilteredSqlHandler(
                'SELECT title, revid FROM mdwiki_revids',
                columns: ["title", "revid"],
                params: [
                    ["name" => "title", "column" => "title", "type" => "text", "placeholder" => "Page Title"],
                    ["name" => "titles", "column" => "title", "type" => "array"],
                ]
            ),
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
                    left join words w               on w.w_title  = ase.title",
                params: [
                    ["name" => "title", "column" => "ase.title", "type" => "text", "placeholder" => "Page Title"],
                    ["name" => "importance", "column" => "ase.importance", "type" => "text", "placeholder" => "Importance"],
                    ["name" => "titles", "column" => "ase.title", "type" => "array"],
                ]
            ),

            'user_status'                  => new UserStatusHandler(),
            'user_data_status'             => new UserDataStatusHandler(),

            'top_langs'                    => new CallableHandler(
                fn($c): array => top_langs($c->params),
                params: [
                    ["name" => "year", "column" => "YEAR(p.pupdate)", "type" => "number", "placeholder" => "year of date", "no_empty_value" => true],
                    ["name" => "month", "column" => "MONTH(p.pupdate)", "type" => "number", "placeholder" => "month of date", "no_empty_value" => true],
                    ["name" => "user_group", "column" => "u.user_group", "type" => "text", "placeholder" => "User Group Name", "no_empty_value" => true],
                    ["name" => "cat", "column" => "p.cat", "type" => "text", "placeholder" => "Category", "no_empty_value" => true],
                ]
            ),
            'top_users'                    => new CallableHandler(
                fn($c): array => top_users($c->params),
                params: [
                    ["name" => "year", "column" => "YEAR(p.pupdate)", "type" => "number", "placeholder" => "year of date", "no_empty_value" => true],
                    ["name" => "month", "column" => "MONTH(p.pupdate)", "type" => "number", "placeholder" => "month of date", "no_empty_value" => true],
                    ["name" => "user_group", "column" => "u.user_group", "type" => "text", "placeholder" => "User Group Name", "no_empty_value" => true],
                    ["name" => "cat", "column" => "p.cat", "type" => "text", "placeholder" => "Category", "no_empty_value" => true],
                ]
            ),
            'top_lang_of_users'            => new CallableHandler(
                fn($c): array => top_lang_of_users($c->params),
                params: [
                    ["name" => "users", "column" => "p.user", "type" => "array"],
                ]
            ),

            'users'            => new UsersHandler(),
            'category_members' => new CategoryMembersHandler(),

            'coordinators' => new StaticSqlHandler(
                'SELECT id, username, is_active FROM coordinators ORDER BY id',
                applyOrder: false,
                columns: ["username", "is_active"],
                params: [
                    ["name" => "Username", "column" => "username", "type" => "text", "placeholder" => "Coordinator Username"],
                ]
            ),

            'langs' => new StaticSqlHandler(
                'SELECT code, autonym, name, redirects FROM langs',
                columns: ["code", "autonym", "name", "redirects"]
            ),

            'graph_data' => new GraphDataHandler(),

            'user_access' => new FilteredSqlHandler(
                'SELECT id, user_name, created_at FROM access_keys',
                params: [
                    ["name" => "user_name", "column" => "user_name", "type" => "text", "placeholder" => "Username"],
                ]
            ),

            'views'       => $views,
            'views_new'   => new ViewsHandler(
                defaultOrder: '1 DESC',
                columns: ["target", "lang", "year", "views"]
            ),
            'user_views'  => $userViews,
            'user_views2' => $userViews,
            'lang_views'  => $langViews,
            'lang_views2' => $langViews,

            'leaderboard_table'          => $leader,
            'leaderboard_table_formated' => $leader,

            'qids'        => $qids,
            'qids_others' => $qids,
            'pages_users_to_main' => new PagesUsersToMainHandler(),

            'words' => new FilteredSqlHandler(
                'SELECT w_id, w_title, w_lead_words, w_all_words FROM words',
                columns: ["w_id", "w_title", "w_lead_words", "w_all_words"],
                params: [
                    ["name" => "title", "column" => "w_title", "type" => "text", "placeholder" => "Page Title"],
                    ["name" => "lead_words", "column" => "w_lead_words", "type" => "number", "placeholder" => "Lead words Count"],
                    ["name" => "all_words", "column" => "w_all_words", "type" => "number", "placeholder" => "Total words Count"],
                ]
            ),

            'count_pages' => new FilteredSqlHandler(
                'SELECT DISTINCT user, COUNT(target) AS count FROM pages',
                suffix: ' GROUP BY user',
                defaultOrder: 'count DESC',
                params: [
                    ["name" => "target", "column" => "target", "type" => "text", "placeholder" => "Target"],
                ]
            ),

            'in_process' => new FilteredSqlHandler(
                'SELECT title, user, lang, cat, translate_type, word, add_date,
                        ca.campaign, la.autonym
                FROM in_process
                LEFT JOIN categories ca ON cat = ca.category
                LEFT JOIN langs la ON lang = la.code',
                groupable: true,
                columns: ["title", "user", "lang", "cat", "translate_type", "word", "add_date"],
                params: [
                    ["name" => "lang", "column" => "lang", "type" => "text", "placeholder" => "Language code"],
                    ["name" => "cat", "column" => "cat", "type" => "text", "placeholder" => "Category"],
                    ["name" => "user", "column" => "user", "type" => "text", "placeholder" => "Username"],
                    ["name" => "select", "column" => "select", "type" => "text", "placeholder" => "Select fields", "no_select" => true],
                    ["name" => "distinct", "column" => "distinct", "type" => "switch", "no_select" => true],
                    ["name" => "group", "column" => "group", "type" => "text", "placeholder" => "Group by field", "no_select" => true],
                    ["name" => "order", "column" => "order", "type" => "text", "placeholder" => "Order by", "no_select" => true],
                    ["name" => "year", "column" => "YEAR(add_date)", "type" => "number", "placeholder" => "year of date"],
                ]
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
                params: [
                    ["name" => "lang", "column" => "p.lang", "type" => "text", "placeholder" => "Language code", "no_empty_value" => false],
                ]
            ),

            'assessments' => new DefaultTableHandler(
                columns: ["title", "importance"],
                params: [
                    ["name" => "title", "column" => "title", "type" => "text", "placeholder" => "Page Title"],
                    ["name" => "importance", "column" => "importance", "type" => "text", "placeholder" => "Importance"],
                    ["name" => "distinct", "column" => "distinct", "type" => "switch", "no_select" => true],
                ]
            ),
            'refs_counts' => new DefaultTableHandler(
                columns: ["r_id", "r_title", "r_lead_refs", "r_all_refs"],
                params: [
                    ["name" => "title", "column" => "r_title", "type" => "text", "placeholder" => "Page Title"],
                    ["name" => "lead", "column" => "r_lead_refs", "type" => "number", "placeholder" => "Lead Refs Count"],
                    ["name" => "all", "column" => "r_all_refs", "type" => "number", "placeholder" => "All Refs Count"],
                ]
            ),
            'enwiki_pageviews' => new DefaultTableHandler(
                columns: ["title", "en_views"],
                params: [
                    ["name" => "title", "column" => "title", "type" => "text", "placeholder" => "Page Title"],
                    ["name" => "en_views", "column" => "en_views", "type" => "number", "placeholder" => "Views Count"],
                ]
            ),
            'categories' => new DefaultTableHandler(
                columns: ["category", "category2", "display", "campaign", "depth", "is_default"],
                params: [
                    ["name" => "Depth", "column" => "depth", "type" => "number", "placeholder" => "Depth Level"],
                    ["name" => "campaign", "column" => "campaign", "type" => "text", "placeholder" => "Campaign"],
                    ["name" => "select", "column" => "select", "type" => "text", "placeholder" => "Select fields", "no_select" => true],
                ]
            ),
            'translate_type' => new DefaultTableHandler(
                columns: ["tt_id", "tt_title", "tt_lead", "tt_full"],
                params: [
                    ["name" => "Lead", "column" => "tt_lead", "type" => "number", "placeholder" => "Lead Translation (0 or 1)"],
                    ["name" => "Full", "column" => "tt_full", "type" => "number", "placeholder" => "Full Translation (0 or 1)"],
                ]
            ),
            'projects' => new DefaultTableHandler(
                columns: ["g_id", "g_title"]
            ),
            'users_no_inprocess' => new DefaultTableHandler(
                columns: ["user", "is_active"]
            ),
            'full_translators' => new DefaultTableHandler(
                columns: ["user", "is_active"]
            ),
            'settings' => new DefaultTableHandler(
                columns: ["title", "displayed", "Type", "value", "ignored"]
            ),

            'pages'       => new PagesHandler('pages'),
            'pages_users' => new PagesHandler('pages_users'),
        ];
    }

    public function resolve(EndpointContext $ctx): ?EndpointHandler
    {
        return $this->getHandler($ctx->get);
    }

    public function getHandler(string $get): ?EndpointHandler
    {
        if (isset($this->handlers[$get])) {
            return $this->handlers[$get];
        }
        if (in_array($get, self::OTHER_TABLES, true)) {
            return new DefaultTableHandler();
        }
        return null;
    }
}
