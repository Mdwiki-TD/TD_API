<?php

declare(strict_types=1);
// endpoint => summary, tag, [description مخصص]، params (اسم مكوّن مشترك | مصفوفة inline)
// مرحلي: ينتقل كل مدخل إلى EndpointDefinition الخاص بالـ handler عند نقل التعريفات.
return [
    'assessments' => [
        'summary' => 'Retrieve page assessments',
        'tag' => 'pages_infos',
        'params' => ['LimitParam', 'TitleParam', 'ImportanceParam', 'DistinctParam'],
    ],
    'categories' => [
        'summary' => 'Retrieve categories',
        'tag' => 'other',
        'params' => ['LimitParam', 'DepthParam', 'SelectParam'],
    ],
    'coordinators' => [
        'summary' => 'Retrieve coordinators information',
        'tag' => 'users',
        'params' => ['LimitParam', 'CoordUserParam'],
    ],
    'count_pages' => [
        'summary' => 'Count pages',
        'tag' => 'statistics',
        'params' => ['LimitParam', 'TargetParam'],
    ],
    'enwiki_pageviews' => [
        'summary' => 'Retrieve English Wikipedia page views',
        'tag' => 'pages_infos',
        'params' => ['LimitParam', 'TitleParam', 'EnViewsParam'],
    ],
    'full_translators' => [
        'summary' => 'Retrieve full translators',
        'tag' => 'users',
        'params' => ['LimitParam'],
    ],
    'graph_data' => [
        'summary' => 'Retrieve graph data',
        'tag' => 'statistics',
        'params' => ['LimitParam'],
    ],
    'in_process' => [
        'summary' => 'Retrieve in-process pages',
        'tag' => 'pages',
        'params' => ['DistinctParam', 'LimitParam', 'LangParam', 'UserParam', 'YearParam', 'SelectParam', 'GroupParam', 'OrderParam'],
    ],
    'langs' => [
        'summary' => 'Retrieve language names',
        'tag' => 'languages',
        'params' => ['LimitParam'],
    ],
    'lang_views2' => [
        'summary' => 'Retrieve language view statistics (type 2)',
        'tag' => 'views',
        'params' => ['LimitParam', 'UserParam', 'LangParam', 'YearParam'],
    ],
    'leaderboard_table' => [
        'summary' => 'Retrieve leaderboard table data',
        'tag' => 'statistics',
        'params' => ['LimitParam', 'PublicationYearParam', 'CatParam', 'UserGroupParam'],
    ],
    'leaderboard_table_formated' => [
        'summary' => 'Retrieve formatted leaderboard table data',
        'tag' => 'statistics',
        'params' => ['LimitParam', 'PublicationYearParam', 'CatParam', 'UserGroupParam'],
    ],
    'missing' => [
        'summary' => 'Retrieve missing pages',
        'tag' => 'pages_infos',
        'params' => ['LimitParam', 'LangParam', 'CategoryParam', 'OrderParam'],
    ],
    'exists_statics_by_category' => [
        'summary' => 'Retrieve missing statics',
        'tag' => 'pages_infos',
        'params' => [
            [
                'in' => 'query',
                'name' => 'category',
                'description' => 'Category',
                'required' => false,
                'schema' => [
                    'default' => 'RTT',
                    'type' => 'string',
                ],
            ],
        ],
    ],
    'missing_by_lang_and_category' => [
        'summary' => 'Retrieve missing statics by language and category',
        'tag' => 'pages_infos',
        'params' => [
            'LangParam',
            [
                'in' => 'query',
                'name' => 'category',
                'description' => 'Category',
                'required' => false,
                'schema' => [
                    'default' => 'RTT',
                    'type' => 'string',
                ],
            ],
        ],
    ],
    'exists_by_lang_and_category' => [
        'summary' => 'Retrieve exists statics by language and category',
        'tag' => 'pages_infos',
        'params' => [
            'LangParam',
            [
                'in' => 'query',
                'name' => 'category',
                'description' => 'Category',
                'required' => false,
                'schema' => [
                    'default' => 'RTT',
                    'type' => 'string',
                ],
            ],
        ],
    ],
    'pages' => [
        'summary' => 'Retrieve pages list',
        'tag' => 'pages',
        'params' => [
            'LimitParam',
            'TitleParam',
            'LangParam',
            'UserParam',
            'TargetParam',
            'CatParam',
            'CampaignParam',
            'PupdateParam',
            'AddDateParam',
            'OffsetParam',
            'PublicationYearParam',
            'YearDateParam',
            'SelectParam',
            'TranslateTypeParam',
            'DistinctParam',
            'DeletedParam',
            'GroupParam',
            'OrderParam',
        ],
    ],
    'pages_by_user_or_lang' => [
        'summary' => 'Retrieve pages list by user or language',
        'tag' => 'pages',
        'description' => '',
        'params' => ['LimitParam', 'LangParam', 'UserParam', 'YearParam', 'OrderParam'],
    ],
    'pages_users' => [
        'summary' => 'Retrieve pages and users data',
        'tag' => 'pages',
        'params' => [
            'LimitParam',
            'LangParam',
            'UserParam',
            'TargetParam',
            'TitleParam',
            'OrderParam',
            'PupdateParam',
            'AddDateParam',
            'GroupParam',
            'OffsetParam',
            'SelectParam',
            'DistinctParam',
        ],
    ],
    'pages_users_to_main' => [
        'summary' => 'Retrieve pages users to main data',
        'tag' => 'pages',
        'params' => ['LimitParam'],
    ],
    'pages_with_views' => [
        'summary' => 'Retrieve pages with view counts (redirects to pages)',
        'tag' => 'pages',
        'description' => 'Corresponds to calling `api.php?get=pages_with_views` which redirects internally to `api.php?get=pages`. Parameters are the same as the `api.php?get=pages` endpoint.',
        'params' => [
            'LimitParam',
            'TitleParam',
            'LangParam',
            'UserParam',
            'TargetParam',
            'CatParam',
            'PupdateParam',
            'AddDateParam',
            'OffsetParam',
            'PublicationYearParam',
            'YearDateParam',
            'SelectParam',
            'TranslateTypeParam',
            'DistinctParam',
            'DeletedParam',
            'GroupParam',
            'OrderParam',
        ],
    ],
    'projects' => [
        'summary' => 'Retrieve projects',
        'tag' => 'other',
        'params' => ['LimitParam'],
    ],
    'qids' => [
        'summary' => 'Retrieve QIDs',
        'tag' => 'identifiers',
        'params' => ['LimitParam', 'DisParam'],
    ],
    'qids_others' => [
        'summary' => 'Retrieve other QIDs',
        'tag' => 'identifiers',
        'params' => ['LimitParam', 'DisParam'],
    ],
    'refs_counts' => [
        'summary' => 'Retrieve reference counts for pages',
        'tag' => 'pages_infos',
        'params' => ['LimitParam', 'TitleParam', 'LeadRefsParam', 'AllRefsParam'],
    ],
    'settings' => [
        'summary' => 'Retrieve settings',
        'tag' => 'other',
        'params' => ['LimitParam'],
    ],
    'titles' => [
        'summary' => 'Retrieve pages by title or importance',
        'tag' => 'pages_infos',
        'params' => ['LimitParam', 'TitleParam', 'ImportanceParam', 'TitlesParam'],
    ],
    'translate_type' => [
        'summary' => 'Retrieve translation type data',
        'tag' => 'languages',
        'params' => ['LimitParam', 'LeadTranslationParam', 'FullTranslationParam'],
    ],
    'user_views2' => [
        'summary' => 'Retrieve user view statistics (type 2)',
        'tag' => 'views',
        'params' => ['LimitParam', 'UserParam', 'LangParam', 'YearParam'],
    ],
    'users' => [
        'summary' => 'Retrieve user information',
        'tag' => 'users',
        'params' => ['LimitParam', 'UserLikeParam', 'WikiParam', 'UserGroupParam'],
    ],
    'users_by_last_pupdate' => [
        'summary' => 'Retrieve users by last pupdate',
        'tag' => 'users',
        'params' => ['LimitParam'],
    ],
    'users_no_inprocess' => [
        'summary' => 'Retrieve users not in process',
        'tag' => 'users',
        'params' => ['LimitParam'],
    ],
    'views_new' => [
        'summary' => 'Retrieve new page views',
        'tag' => 'views',
        'params' => ['LimitParam', 'LangParam', 'PublicationYearParam', 'ViewsParam'],
    ],
    'words' => [
        'summary' => 'Retrieve word counts for pages',
        'tag' => 'pages_infos',
        'params' => ['LimitParam', 'TitleParam', 'LeadWordsParam', 'AllWordsParam'],
    ],
    'revids' => [
        'summary' => 'Retrieve revision IDs',
        'tag' => 'pages_infos',
        'description' => 'Retrieve revision IDs for specified titles or title arrays',
        'params' => ['TitleParam', 'TitlesParam'],
    ],
    'publish_reports' => [
        'summary' => 'publish reports',
        'tag' => 'other',
        'description' => '',
        'params' => [
            'LimitParam',
            'TitleParam',
            'UserParam',
            'LangParam',
            'YearParam',
            'SelectParam',
            [
                'in' => 'query',
                'name' => 'month',
                'description' => 'month of date',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                ],
            ],
            [
                'in' => 'query',
                'name' => 'sourcetitle',
                'description' => 'sourcetitle',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                ],
            ],
            [
                'in' => 'query',
                'name' => 'result',
                'description' => 'result',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                ],
            ],
            'DistinctParam',
        ],
    ],
    'publish_reports_stats' => [
        'summary' => 'publish reports stats',
        'tag' => 'other',
        'description' => '',
        'params' => [],
    ],
    'language_settings' => [
        'summary' => 'language settings',
        'tag' => 'other',
        'description' => '',
        'params' => [
            [
                'in' => 'query',
                'name' => 'lang_code',
                'description' => 'Language code',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                ],
            ],
        ],
    ],
    'top_users' => [
        'summary' => 'Retrieve Top users',
        'tag' => 'users',
        'description' => '',
        'params' => ['LimitParam', 'YearParam', 'UserGroupParam', 'CatParam'],
    ],
    'top_langs' => [
        'summary' => 'Retrieve Top langs',
        'tag' => 'users',
        'description' => '',
        'params' => ['LimitParam', 'YearParam', 'UserGroupParam', 'CatParam'],
    ],
    'top_lang_of_users' => [
        'summary' => '',
        'tag' => 'users',
        'description' => '',
        'params' => [
            [
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
            ],
        ],
    ],
    'user_status' => [
        'summary' => '',
        'tag' => 'users',
        'description' => 'list of users (langs, campaigns, categories)',
        'params' => [
            [
                'in' => 'query',
                'name' => 'user',
                'description' => 'Username',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                ],
            ],
            [
                'in' => 'query',
                'name' => 'lang',
                'description' => 'Language code',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                ],
            ],
            [
                'in' => 'query',
                'name' => 'select',
                'description' => 'Select fields',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                    'enum' => ['lang', 'campaign', 'category', 'year'],
                ],
            ],
        ],
    ],
    'get_lang_years' => [
        'summary' => '',
        'tag' => 'languages',
        'description' => 'list of years of language',
        'params' => [
            [
                'in' => 'query',
                'name' => 'lang',
                'description' => 'Language code',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                ],
            ],
        ],
    ],
];
