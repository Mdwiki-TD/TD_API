<?php

declare(strict_types=1);

namespace App\Endpoints\Definition;

/**
 * تعريفات جميع الـ endpoints ككائنات EndpointDefinition.
 */
final class EndpointDefinitions
{
    /** @return array<string, EndpointDefinition> */
    public static function all(): array
    {
        return [
            'assessments' => new EndpointDefinition(
                summary: 'Retrieve page assessments',
                tag: 'pages_infos',
                params: [
                    new Param(name: 'title', column: 'title', placeholder: 'Page Title'),
                    new Param(name: 'importance', column: 'importance', placeholder: 'Importance'),
                    new Param(name: 'distinct', column: 'distinct', type: 'switch', noSelect: true),
                ],
                columns: ['title', 'importance'],
            ),
            'categories' => new EndpointDefinition(
                summary: 'Retrieve categories',
                tag: 'other',
                params: [
                    new Param(name: 'Depth', column: 'depth', type: 'number', placeholder: 'Depth Level'),
                    new Param(name: 'campaign', column: 'campaign', placeholder: 'Campaign'),
                    new Param(name: 'select', column: 'select', placeholder: 'Select fields', noSelect: true),
                ],
                columns: ['category', 'category2', 'display', 'campaign', 'depth', 'is_default'],
            ),
            'coordinators' => new EndpointDefinition(
                summary: 'Retrieve coordinators information',
                tag: 'users',
                params: [
                    new Param(name: 'Username', column: 'username', placeholder: 'Coordinator Username'),
                ],
                columns: ['username', 'is_active'],
            ),
            'count_pages' => new EndpointDefinition(
                summary: 'Count pages',
                tag: 'statistics',
                params: [
                    new Param(name: 'target', column: 'target', placeholder: 'Target'),
                ],
            ),
            'enwiki_pageviews' => new EndpointDefinition(
                summary: 'Retrieve English Wikipedia page views',
                tag: 'pages_infos',
                params: [
                    new Param(name: 'title', column: 'title', placeholder: 'Page Title'),
                    new Param(name: 'en_views', column: 'en_views', type: 'number', placeholder: 'Views Count'),
                ],
                columns: ['title', 'en_views'],
            ),
            'full_translators' => new EndpointDefinition(
                summary: 'Retrieve full translators',
                tag: 'users',
                columns: ['user', 'is_active'],
            ),
            'graph_data' => new EndpointDefinition(
                summary: 'Retrieve graph data',
                tag: 'statistics',
                params: [
                    new Param(name: 'user_group', column: 'u.user_group', placeholder: 'User Group Name'),
                    new Param(name: 'month', column: 'MONTH(p.pupdate)', type: 'number', placeholder: 'month of date', noEmptyValue: true),
                    new Param(name: 'year', column: 'YEAR(p.pupdate)', type: 'number', placeholder: 'year of date', noEmptyValue: true, doc: 'PublicationYearParam'),
                    new Param(name: 'user', column: 'p.user', placeholder: 'Username', noEmptyValue: false),
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code', noEmptyValue: false),
                    new Param(name: 'category', column: 'p.cat', placeholder: 'Category'),
                    new Param(name: 'campaign', column: 'p.campaign', placeholder: 'Campaign'),
                ],
            ),
            'in_process' => new EndpointDefinition(
                summary: 'Retrieve in-process pages',
                tag: 'pages',
                params: [
                    new Param(name: 'lang', column: 'lang', placeholder: 'Language code'),
                    new Param(name: 'cat', column: 'cat', placeholder: 'Category'),
                    new Param(name: 'user', column: 'user', placeholder: 'Username'),
                    new Param(name: 'select', column: 'select', placeholder: 'Select fields', noSelect: true),
                    new Param(name: 'distinct', column: 'distinct', type: 'switch', noSelect: true),
                    new Param(name: 'group', column: 'group', placeholder: 'Group by field', noSelect: true),
                    new Param(name: 'order', column: 'order', placeholder: 'Order by', noSelect: true),
                    new Param(name: 'year', column: 'YEAR(add_date)', type: 'number', placeholder: 'year of date', doc: 'YearParam'),
                ],
                columns: ['title', 'user', 'lang', 'cat', 'translate_type', 'word', 'add_date'],
            ),
            'langs' => new EndpointDefinition(
                summary: 'Retrieve language names',
                tag: 'languages',
                columns: ['code', 'autonym', 'name', 'redirects'],
            ),
            'lang_views2' => new EndpointDefinition(
                summary: 'Retrieve language view statistics (type 2)',
                tag: 'views',
                params: [
                    new Param(name: 'user', column: 'p.user', placeholder: 'Username'),
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code'),
                    new Param(name: 'year', column: 'YEAR(pupdate)', type: 'number', placeholder: 'Year', doc: 'YearParam'),
                ],
            ),
            'leaderboard_table' => new EndpointDefinition(
                summary: 'Retrieve leaderboard table data',
                tag: 'statistics',
                params: [
                    new Param(name: 'year', column: 'YEAR(p.pupdate)', type: 'number', placeholder: 'Year of publication', doc: 'PublicationYearParam'),
                    new Param(name: 'cat', column: 'cat', placeholder: 'Category', valueCanBeNull: true),
                    new Param(name: 'user_group', column: 'u.user_group', placeholder: 'User Group Name'),
                    new Param(name: 'order', column: 'order', placeholder: 'Order by', noSelect: true),
                ],
                columns: ['u.user_group'],
            ),
            'leaderboard_table_formated' => new EndpointDefinition(
                summary: 'Retrieve formatted leaderboard table data',
                tag: 'statistics',
                params: [
                    new Param(name: 'year', column: 'YEAR(p.pupdate)', type: 'number', placeholder: 'Year of publication', doc: 'PublicationYearParam'),
                    new Param(name: 'cat', column: 'cat', placeholder: 'Category', valueCanBeNull: true),
                    new Param(name: 'user_group', column: 'u.user_group', placeholder: 'User Group Name'),
                    new Param(name: 'order', column: 'order', placeholder: 'Order by', noSelect: true),
                ],
                columns: ['u.user_group'],
            ),
            'missing' => new EndpointDefinition(
                summary: 'Retrieve missing pages',
                tag: 'pages_infos',
                params: [
                    new Param(name: 'lang', column: 't.code', placeholder: 'Language code'),
                    new Param(name: 'category', column: 'a.category', placeholder: 'Category'),
                    new Param(name: 'order', column: 'order', placeholder: 'Order by', noSelect: true),
                ],
            ),
            'exists_statics_by_category' => new EndpointDefinition(
                summary: 'Retrieve missing statics',
                tag: 'pages_infos',
                params: [
                    new Param(name: 'category', column: 'a.category', placeholder: 'Category', default: 'RTT', required: true, doc: [
                        'in' => 'query',
                        'name' => 'category',
                        'description' => 'Category',
                        'required' => false,
                        'schema' => [
                            'default' => 'RTT',
                            'type' => 'string',
                        ],
                    ]),
                ],
            ),
            'missing_by_lang_and_category' => new EndpointDefinition(
                summary: 'Retrieve missing statics by language and category',
                tag: 'pages_infos',
                params: [
                    new Param(name: 'lang', column: 't.code', placeholder: 'Language code', required: true),
                    new Param(name: 'category', column: 'a.category', placeholder: 'Category', default: 'RTT', doc: [
                        'in' => 'query',
                        'name' => 'category',
                        'description' => 'Category',
                        'required' => false,
                        'schema' => [
                            'default' => 'RTT',
                            'type' => 'string',
                        ],
                    ]),
                ],
            ),
            'exists_by_lang_and_category' => new EndpointDefinition(
                summary: 'Retrieve exists statics by language and category',
                tag: 'pages_infos',
                params: [
                    new Param(name: 'lang', column: 't.code', placeholder: 'Language code', required: true),
                    new Param(name: 'category', column: 'a.category', placeholder: 'Category', default: 'RTT', doc: [
                        'in' => 'query',
                        'name' => 'category',
                        'description' => 'Category',
                        'required' => false,
                        'schema' => [
                            'default' => 'RTT',
                            'type' => 'string',
                        ],
                    ]),
                ],
            ),
            'pages' => new EndpointDefinition(
                summary: 'Retrieve pages list',
                tag: 'pages',
                params: [
                    new Param(name: 'title', column: 'p.title', placeholder: 'Page Title'),
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code'),
                    new Param(name: 'user', column: 'p.user', placeholder: 'Username'),
                    new Param(name: 'target', column: 'p.target', placeholder: 'Target'),
                    new Param(name: 'cat', column: 'p.cat', placeholder: 'Category'),
                    new Param(name: 'campaign', column: 'campaign', placeholder: 'Campaign'),
                    new Param(name: 'group', column: 'group', placeholder: 'Group by field', options: [], noSelect: true),
                    new Param(name: 'order', column: 'order', placeholder: 'Order by', noSelect: true),
                    new Param(name: 'pupdate', column: 'p.pupdate', placeholder: 'Date of publication'),
                    new Param(name: 'add_date', column: 'p.add_date', placeholder: 'Date of addition to DB'),
                    new Param(name: 'limit', column: 'p.limit', type: 'number', placeholder: 'Limit results', value: '50', noSelect: true),
                    new Param(name: 'offset', column: 'p.offset', type: 'number', placeholder: 'Offset results', value: '0', noSelect: true),
                    new Param(name: 'year', column: 'YEAR(p.pupdate)', type: 'number', placeholder: 'Year of publication', doc: 'PublicationYearParam'),
                    new Param(name: 'date_year', column: 'YEAR(p.date)', type: 'number', placeholder: 'year of date'),
                    new Param(name: 'select', column: 'select', placeholder: 'Select fields', options: ['count(*)'], noSelect: true),
                    new Param(name: 'translate_type', column: 'p.translate_type', type: 'select', options: ['all', 'lead']),
                    new Param(name: 'distinct', column: 'p.distinct', type: 'switch', noSelect: true),
                    new Param(name: 'Deleted', column: 'p.deleted', type: 'switch', placeholder: '0 or 1'),
                ],
                columns: ['title', 'word', 'translate_type', 'cat', 'lang', 'user', 'target', 'date', 'pupdate', 'add_date', 'deleted', 'mdwiki_revid'],
                orderValues: [
                    'pupdate_or_add_date' => 'GREATEST(UNIX_TIMESTAMP(pupdate), UNIX_TIMESTAMP(add_date))',
                ],
            ),
            'pages_by_user_or_lang' => new EndpointDefinition(
                summary: 'Retrieve pages list by user or language',
                tag: 'pages',
                params: [
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code'),
                    new Param(name: 'user', column: 'p.user', placeholder: 'Username'),
                    new Param(name: 'year', column: 'YEAR(p.date)', type: 'number', placeholder: 'year of date', doc: 'YearParam'),
                    new Param(name: 'order', column: 'order', placeholder: 'Order by', noSelect: true),
                ],
            ),
            'pages_users' => new EndpointDefinition(
                summary: 'Retrieve pages and users data',
                tag: 'pages',
                params: [
                    new Param(name: 'lang', column: 'lang', placeholder: 'Language code'),
                    new Param(name: 'user', column: 'user', placeholder: 'Username'),
                    new Param(name: 'target', column: 'target', placeholder: 'Target'),
                    new Param(name: 'title', column: 'title', placeholder: 'Page Title'),
                    new Param(name: 'order', column: 'order', placeholder: 'Order by', noSelect: true),
                    new Param(name: 'pupdate', column: 'pupdate', placeholder: 'Date of publication'),
                    new Param(name: 'add_date', column: 'add_date', placeholder: 'Date of addition to DB'),
                    new Param(name: 'group', column: 'group', placeholder: 'Group by field', options: [], noSelect: true),
                    new Param(name: 'limit', column: 'limit', type: 'number', placeholder: 'Limit results', value: '50', noSelect: true),
                    new Param(name: 'offset', column: 'p.offset', type: 'number', placeholder: 'Offset results', value: '0', noSelect: true),
                    new Param(name: 'select', column: 'select', placeholder: 'Select fields', noSelect: true),
                    new Param(name: 'distinct', column: 'distinct', type: 'switch', noSelect: true),
                ],
                columns: ['title', 'word', 'translate_type', 'cat', 'lang', 'user', 'target', 'date', 'pupdate', 'add_date', 'deleted', 'mdwiki_revid'],
            ),
            'pages_users_to_main' => new EndpointDefinition(
                summary: 'Retrieve pages users to main data',
                tag: 'pages',
                columns: ['new_target', 'new_user', 'new_qid'],
            ),
            'pages_with_views' => new EndpointDefinition(
                summary: 'Retrieve pages with view counts (redirects to pages)',
                tag: 'pages',
                description: 'Corresponds to calling `api.php?get=pages_with_views` which redirects internally to `api.php?get=pages`. Parameters are the same as the `api.php?get=pages` endpoint.',
                params: [
                    new Param(name: 'title', column: 'p.title', placeholder: 'Page Title'),
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code'),
                    new Param(name: 'user', column: 'p.user', placeholder: 'Username'),
                    new Param(name: 'target', column: 'p.target', placeholder: 'Target'),
                    new Param(name: 'cat', column: 'p.cat', placeholder: 'Category'),
                    new Param(name: 'campaign', column: 'campaign', placeholder: 'Campaign'),
                    new Param(name: 'group', column: 'group', placeholder: 'Group by field', options: [], noSelect: true),
                    new Param(name: 'order', column: 'order', placeholder: 'Order by', noSelect: true),
                    new Param(name: 'pupdate', column: 'p.pupdate', placeholder: 'Date of publication'),
                    new Param(name: 'add_date', column: 'p.add_date', placeholder: 'Date of addition to DB'),
                    new Param(name: 'limit', column: 'p.limit', type: 'number', placeholder: 'Limit results', value: '50', noSelect: true),
                    new Param(name: 'offset', column: 'p.offset', type: 'number', placeholder: 'Offset results', value: '0', noSelect: true),
                    new Param(name: 'year', column: 'YEAR(p.pupdate)', type: 'number', placeholder: 'Year of publication', doc: 'PublicationYearParam'),
                    new Param(name: 'date_year', column: 'YEAR(p.date)', type: 'number', placeholder: 'year of date'),
                    new Param(name: 'select', column: 'select', placeholder: 'Select fields', options: ['count(*)'], noSelect: true),
                    new Param(name: 'translate_type', column: 'p.translate_type', type: 'select', options: ['all', 'lead']),
                    new Param(name: 'distinct', column: 'p.distinct', type: 'switch', noSelect: true),
                    new Param(name: 'Deleted', column: 'p.deleted', type: 'switch', placeholder: '0 or 1'),
                ],
                columns: ['title', 'word', 'translate_type', 'cat', 'lang', 'user', 'target', 'date', 'pupdate', 'add_date', 'deleted', 'mdwiki_revid'],
                orderValues: [
                    'pupdate_or_add_date' => 'GREATEST(UNIX_TIMESTAMP(pupdate), UNIX_TIMESTAMP(add_date))',
                ],
            ),
            'projects' => new EndpointDefinition(
                summary: 'Retrieve projects',
                tag: 'other',
                columns: ['g_id', 'g_title'],
            ),
            'qids' => new EndpointDefinition(
                summary: 'Retrieve QIDs',
                tag: 'identifiers',
                params: [
                    new Param(name: 'dis', column: 'dis', type: 'select', options: ['', 'empty', 'all', 'duplicate']),
                ],
                columns: ['title', 'qid'],
            ),
            'qids_others' => new EndpointDefinition(
                summary: 'Retrieve other QIDs',
                tag: 'identifiers',
                params: [
                    new Param(name: 'dis', column: 'dis', type: 'select', options: ['', 'empty', 'all', 'duplicate']),
                ],
                columns: ['title', 'qid'],
            ),
            'refs_counts' => new EndpointDefinition(
                summary: 'Retrieve reference counts for pages',
                tag: 'pages_infos',
                params: [
                    new Param(name: 'title', column: 'r_title', placeholder: 'Page Title'),
                    new Param(name: 'lead', column: 'r_lead_refs', type: 'number', placeholder: 'Lead Refs Count', doc: 'LeadRefsParam'),
                    new Param(name: 'all', column: 'r_all_refs', type: 'number', placeholder: 'All Refs Count'),
                ],
                columns: ['r_id', 'r_title', 'r_lead_refs', 'r_all_refs'],
            ),
            'settings' => new EndpointDefinition(
                summary: 'Retrieve settings',
                tag: 'other',
                columns: ['title', 'displayed', 'Type', 'value', 'ignored'],
            ),
            'titles' => new EndpointDefinition(
                summary: 'Retrieve pages by title or importance',
                tag: 'pages_infos',
                params: [
                    new Param(name: 'title', column: 'ase.title', placeholder: 'Page Title'),
                    new Param(name: 'importance', column: 'ase.importance', placeholder: 'Importance'),
                    new Param(name: 'titles', column: 'ase.title', type: 'array'),
                ],
            ),
            'translate_type' => new EndpointDefinition(
                summary: 'Retrieve translation type data',
                tag: 'languages',
                params: [
                    new Param(name: 'Lead', column: 'tt_lead', type: 'number', placeholder: 'Lead Translation (0 or 1)', doc: 'LeadTranslationParam'),
                    new Param(name: 'Full', column: 'tt_full', type: 'number', placeholder: 'Full Translation (0 or 1)'),
                ],
                columns: ['tt_id', 'tt_title', 'tt_lead', 'tt_full'],
            ),
            'user_views2' => new EndpointDefinition(
                summary: 'Retrieve user view statistics (type 2)',
                tag: 'views',
                params: [
                    new Param(name: 'user', column: 'p.user', placeholder: 'Username'),
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code'),
                    new Param(name: 'year', column: 'YEAR(pupdate)', type: 'number', placeholder: 'Year', doc: 'YearParam'),
                ],
            ),
            'users' => new EndpointDefinition(
                summary: 'Retrieve user information',
                tag: 'users',
                params: [
                    new Param(name: 'userlike', column: 'userlike', placeholder: 'Username starts with', required: true),
                    new Param(name: 'wiki', column: 'wiki', placeholder: 'Wiki Name'),
                    new Param(name: 'user_group', column: 'user_group', placeholder: 'User Group Name', noSelect: true),
                ],
                columns: ['user_id', 'username', 'email', 'wiki', 'user_group', 'reg_date'],
            ),
            'users_by_last_pupdate' => new EndpointDefinition(
                summary: 'Retrieve users by last pupdate',
                tag: 'users',
            ),
            'users_no_inprocess' => new EndpointDefinition(
                summary: 'Retrieve users not in process',
                tag: 'users',
                columns: ['user', 'is_active'],
            ),
            'views_new' => new EndpointDefinition(
                summary: 'Retrieve new page views',
                tag: 'views',
                params: [
                    new Param(name: 'lang', column: 'v.lang', placeholder: 'Language code'),
                    new Param(name: 'year', column: 'YEAR(p.pupdate)', type: 'number', placeholder: 'Year of publication', doc: 'PublicationYearParam'),
                    new Param(name: 'views', column: 'v.views', type: 'number', placeholder: 'Views'),
                ],
                columns: ['target', 'lang', 'year', 'views'],
            ),
            'words' => new EndpointDefinition(
                summary: 'Retrieve word counts for pages',
                tag: 'pages_infos',
                params: [
                    new Param(name: 'title', column: 'w_title', placeholder: 'Page Title'),
                    new Param(name: 'lead_words', column: 'w_lead_words', type: 'number', placeholder: 'Lead words Count'),
                    new Param(name: 'all_words', column: 'w_all_words', type: 'number', placeholder: 'Total words Count'),
                ],
                columns: ['w_id', 'w_title', 'w_lead_words', 'w_all_words'],
            ),
            'revids' => new EndpointDefinition(
                summary: 'Retrieve revision IDs',
                tag: 'pages_infos',
                description: 'Retrieve revision IDs for specified titles or title arrays',
                params: [
                    new Param(name: 'title', column: 'title', placeholder: 'Page Title'),
                    new Param(name: 'titles', column: 'title', type: 'array'),
                ],
                columns: ['title', 'revid'],
            ),
            'publish_reports' => new EndpointDefinition(
                summary: 'publish reports',
                tag: 'other',
                params: [
                    new Param(name: 'year', column: 'YEAR(date)', type: 'number', placeholder: 'year of date', doc: 'YearParam'),
                    new Param(name: 'month', column: 'MONTH(date)', type: 'number', placeholder: 'month of date'),
                    new Param(name: 'title', column: 'title', placeholder: 'Page Title'),
                    new Param(name: 'user', column: 'user', placeholder: 'user'),
                    new Param(name: 'lang', column: 'lang', placeholder: 'Language code'),
                    new Param(name: 'sourcetitle', column: 'sourcetitle', placeholder: 'sourcetitle'),
                    new Param(name: 'result', column: 'result', placeholder: 'result'),
                    new Param(name: 'select', column: 'select', placeholder: 'Select fields', noSelect: true),
                    new Param(name: 'distinct', column: 'distinct', type: 'switch', noSelect: true),
                ],
                columns: ['date', 'title', 'user', 'lang', 'sourcetitle', 'result', 'data'],
            ),
            'publish_reports_stats' => new EndpointDefinition(
                summary: 'publish reports stats',
                tag: 'other',
                params: [
                    new Param(name: 'lang', column: 'lang', placeholder: 'Language code'),
                    new Param(name: 'user', column: 'user', placeholder: 'Username'),
                ],
            ),
            'language_settings' => new EndpointDefinition(
                summary: 'language settings',
                tag: 'other',
                params: [
                    new Param(name: 'lang_code', column: 'lang_code', placeholder: 'Language code'),
                ],
                columns: ['lang_code', 'move_dots', 'expend', 'add_en_lang'],
            ),
            'top_users' => new EndpointDefinition(
                summary: 'Retrieve Top users',
                tag: 'users',
                params: [
                    new Param(name: 'year', column: 'YEAR(p.pupdate)', type: 'number', placeholder: 'year of date', noEmptyValue: true, doc: 'YearParam'),
                    new Param(name: 'month', column: 'MONTH(p.pupdate)', type: 'number', placeholder: 'month of date', noEmptyValue: true),
                    new Param(name: 'user_group', column: 'u.user_group', placeholder: 'User Group Name', noEmptyValue: true),
                    new Param(name: 'cat', column: 'p.cat', placeholder: 'Category', noEmptyValue: true),
                ],
            ),
            'top_langs' => new EndpointDefinition(
                summary: 'Retrieve Top langs',
                tag: 'users',
                params: [
                    new Param(name: 'year', column: 'YEAR(p.pupdate)', type: 'number', placeholder: 'year of date', noEmptyValue: true, doc: 'YearParam'),
                    new Param(name: 'month', column: 'MONTH(p.pupdate)', type: 'number', placeholder: 'month of date', noEmptyValue: true),
                    new Param(name: 'user_group', column: 'u.user_group', placeholder: 'User Group Name', noEmptyValue: true),
                    new Param(name: 'cat', column: 'p.cat', placeholder: 'Category', noEmptyValue: true),
                ],
            ),
            'top_lang_of_users' => new EndpointDefinition(
                summary: '',
                tag: 'users',
                params: [
                    new Param(name: 'users', column: 'p.user', type: 'array', doc: [
                        'in' => 'query',
                        'name' => 'users',
                        'description' => 'list of users',
                        'required' => false,
                        'schema' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                            ],
                            'maxItems' => 50,
                        ],
                    ]),
                ],
            ),
            'user_status' => new EndpointDefinition(
                summary: '',
                tag: 'users',
                description: 'list of users (langs, campaigns, categories)',
                params: [
                    new Param(name: 'user', column: 'p.user', placeholder: 'Username', noEmptyValue: false),
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code', noEmptyValue: false),
                    new Param(name: 'select', column: 'select', placeholder: 'Select fields', options: ['lang', 'campaign', 'cat', 'year'], doc: [
                        'in' => 'query',
                        'name' => 'select',
                        'description' => 'Select fields',
                        'required' => false,
                        'schema' => [
                            'type' => 'string',
                            'enum' => ['lang', 'campaign', 'category', 'year'],
                        ],
                    ]),
                ],
            ),
            'get_lang_years' => new EndpointDefinition(
                summary: '',
                tag: 'languages',
                description: 'list of years of language',
                params: [
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code', noEmptyValue: false),
                ],
            ),
            'views' => new EndpointDefinition(
                summary: 'Retrieve page views',
                tag: 'views',
                params: [
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code'),
                    new Param(name: 'year', column: 'YEAR(p.pupdate)', type: 'number', placeholder: 'Year of publication', doc: 'PublicationYearParam'),
                ],
            ),
            'user_views' => new EndpointDefinition(
                summary: 'Retrieve page views for a user',
                tag: 'views',
                params: [
                    new Param(name: 'user', column: 'p.user', placeholder: 'Username'),
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code'),
                    new Param(name: 'year', column: 'YEAR(pupdate)', type: 'number', placeholder: 'Year', doc: 'PublicationYearParam'),
                ],
            ),
            'lang_views' => new EndpointDefinition(
                summary: 'Retrieve page views for a language',
                tag: 'views',
                params: [
                    new Param(name: 'user', column: 'p.user', placeholder: 'Username'),
                    new Param(name: 'lang', column: 'p.lang', placeholder: 'Language code'),
                    new Param(name: 'year', column: 'YEAR(pupdate)', type: 'number', placeholder: 'Year', doc: 'PublicationYearParam'),
                ],
            ),
            'category_members' => new EndpointDefinition(
                summary: 'Retrieve article ids of a category',
                tag: 'pages',
            ),
            'pages_langs' => new EndpointDefinition(
                summary: 'Retrieve languages that have translated pages',
                tag: 'languages',
            ),
            'pages_users_langs' => new EndpointDefinition(
                summary: 'Retrieve languages that have user pages',
                tag: 'languages',
            ),
            'status' => new EndpointDefinition(
                summary: 'Retrieve monthly publication counts',
                tag: 'statistics',
                params: [
                    new Param(name: 'year', column: 'YEAR(p.pupdate)', type: 'number', placeholder: 'Year of publication', doc: 'PublicationYearParam'),
                    new Param(name: 'user_group', column: 'u.user_group', placeholder: 'User Group Name'),
                    new Param(name: 'campaign', column: 'campaign', placeholder: 'Campaign'),
                    new Param(name: 'category', column: 'cat', placeholder: 'Category'),
                    new Param(name: 'cat', column: 'cat', placeholder: 'Category'),
                ],
            ),
            'user_access' => new EndpointDefinition(
                summary: 'TODO',
                tag: 'users',
                params: [
                    new Param(name: 'user_name', column: 'user_name', placeholder: 'Username'),
                ],
            ),
            'statics_by_category' => new EndpointDefinition(
                summary: 'TODO',
                tag: 'pages_infos',
            ),
            'user_data_status' => new EndpointDefinition(
                summary: "Retrieve years, languages and campaigns of a user's pages",
                tag: 'users',
                params: [
                    new Param(name: 'user', column: 'p.user', placeholder: 'Username', required: true),
                ],
            ),
        ];
    }

    public static function for(string $name): ?EndpointDefinition
    {
        return self::all()[$name] ?? null;
    }
}
