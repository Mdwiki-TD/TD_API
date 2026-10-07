<?php
declare(strict_types=1);
namespace App\OpenApi;

/** الأجزاء المشتركة من openapi.json (مستخرجة حرفياً من النسخة اليدوية) */
final class OpenApiCatalog
{
    public static function data(): array
    {
        return [
            'info' => [
                'title' => 'MDwiki API',
                'description' => 'API for accessing MDwiki data including pages, translations, views, and more',
                'version' => '1.0.0',
            ],
            'servers' => [
                [
                    'url' => '/',
                    'description' => 'MDwiki API.',
                ],
            ],
            'tags' => [
                [
                    'name' => 'pages_infos',
                    'description' => 'Endpoints providing information about pages.',
                ],
                [
                    'name' => 'pages',
                    'description' => 'Endpoints related to page lists and details.',
                ],
                [
                    'name' => 'identifiers',
                    'description' => 'Endpoints for identifiers like QIDs.',
                ],
                [
                    'name' => 'views',
                    'description' => 'Endpoints providing page view statistics.',
                ],
                [
                    'name' => 'users',
                    'description' => 'Endpoints related to users and their activity.',
                ],
                [
                    'name' => 'statistics',
                    'description' => 'General statistics and leaderboard data.',
                ],
                [
                    'name' => 'languages',
                    'description' => 'Endpoints providing language information.',
                ],
                [
                    'name' => 'other',
                    'description' => 'Miscellaneous endpoints.',
                ],
            ],
            'parameters' => [
                'AddDateParam' => [
                    'in' => 'query',
                    'name' => 'add_date',
                    'description' => 'Date of addition to DB',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'AllRefsParam' => [
                    'in' => 'query',
                    'name' => 'all',
                    'description' => 'All Refs Count',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                    ],
                ],
                'AllWordsParam' => [
                    'in' => 'query',
                    'name' => 'all_words',
                    'description' => 'Total words Count',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                    ],
                ],
                'CampaignParam' => [
                    'in' => 'query',
                    'name' => 'campaign',
                    'description' => 'Campaign',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'CatParam' => [
                    'in' => 'query',
                    'name' => 'cat',
                    'description' => 'Category',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'CategoryParam' => [
                    'in' => 'query',
                    'name' => 'category',
                    'description' => 'Category',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'CoordUserParam' => [
                    'in' => 'query',
                    'name' => 'username',
                    'description' => 'Coordinator Username',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'DeletedParam' => [
                    'in' => 'query',
                    'name' => 'Deleted',
                    'description' => '0 or 1',
                    'required' => false,
                    'schema' => [
                        'type' => 'boolean',
                    ],
                ],
                'DepthParam' => [
                    'in' => 'query',
                    'name' => 'Depth',
                    'description' => 'Depth Level',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                    ],
                ],
                'DisParam' => [
                    'in' => 'query',
                    'name' => 'dis',
                    'description' => 'dis',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                        'enum' => ['', 'empty', 'all', 'duplicate'],
                    ],
                ],
                'DistinctParam' => [
                    'in' => 'query',
                    'name' => 'distinct',
                    'description' => 'distinct',
                    'required' => false,
                    'schema' => [
                        'type' => 'boolean',
                    ],
                ],
                'EnViewsParam' => [
                    'in' => 'query',
                    'name' => 'en_views',
                    'description' => 'Views Count',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                    ],
                ],
                'FullTranslationParam' => [
                    'in' => 'query',
                    'name' => 'Full',
                    'description' => 'Full Translation (0 or 1)',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                    ],
                ],
                'GroupParam' => [
                    'in' => 'query',
                    'name' => 'group',
                    'description' => 'Group by field',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'ImportanceParam' => [
                    'in' => 'query',
                    'name' => 'importance',
                    'description' => 'Importance',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'LangParam' => [
                    'in' => 'query',
                    'name' => 'lang',
                    'description' => 'Language code',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'LeadRefsParam' => [
                    'in' => 'query',
                    'name' => 'lead',
                    'description' => 'Lead Refs Count',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                    ],
                ],
                'LeadTranslationParam' => [
                    'in' => 'query',
                    'name' => 'Lead',
                    'description' => 'Lead Translation (0 or 1)',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                    ],
                ],
                'LeadWordsParam' => [
                    'in' => 'query',
                    'name' => 'lead_words',
                    'description' => 'Lead words Count',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                    ],
                ],
                'LimitParam' => [
                    'in' => 'query',
                    'name' => 'limit',
                    'description' => 'Maximum number of results to return',
                    'required' => false,
                    'schema' => [
                        'type' => 'integer',
                        'default' => 50,
                    ],
                ],
                'OffsetParam' => [
                    'in' => 'query',
                    'name' => 'offset',
                    'description' => 'Offset results',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                        'default' => 0,
                    ],
                ],
                'OrderParam' => [
                    'in' => 'query',
                    'name' => 'order',
                    'description' => 'Order by',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'PublicationYearParam' => [
                    'in' => 'query',
                    'name' => 'year',
                    'description' => 'Year of publication',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                    ],
                ],
                'PupdateParam' => [
                    'in' => 'query',
                    'name' => 'pupdate',
                    'description' => 'Date of publication',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'SelectParam' => [
                    'in' => 'query',
                    'name' => 'select',
                    'description' => 'Select fields',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'TargetParam' => [
                    'in' => 'query',
                    'name' => 'target',
                    'description' => 'Target',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'TitleParam' => [
                    'in' => 'query',
                    'name' => 'title',
                    'description' => 'Title',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'TitlesParam' => [
                    'in' => 'query',
                    'name' => 'titles',
                    'description' => 'list of titles',
                    'required' => false,
                    'schema' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                        ],
                        'maxItems' => 50,
                    ],
                ],
                'TranslateTypeParam' => [
                    'in' => 'query',
                    'name' => 'translate_type',
                    'description' => 'translate_type',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                        'enum' => ['all', 'lead'],
                    ],
                ],
                'UserGroupParam' => [
                    'in' => 'query',
                    'name' => 'user_group',
                    'description' => 'User Group Name',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'UserLikeParam' => [
                    'in' => 'query',
                    'name' => 'userlike',
                    'description' => 'Username starts with',
                    'required' => true,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'UserNameParam' => [
                    'in' => 'query',
                    'name' => 'user_name',
                    'description' => 'Username',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'UserParam' => [
                    'in' => 'query',
                    'name' => 'user',
                    'description' => 'Username',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'ViewsParam' => [
                    'in' => 'query',
                    'name' => 'views',
                    'description' => 'Views',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                    ],
                ],
                'WikiParam' => [
                    'in' => 'query',
                    'name' => 'wiki',
                    'description' => 'Wiki Name',
                    'required' => false,
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'YearDateParam' => [
                    'in' => 'query',
                    'name' => 'date_year',
                    'description' => 'Year',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                    ],
                ],
                'YearParam' => [
                    'in' => 'query',
                    'name' => 'year',
                    'description' => 'Year',
                    'required' => false,
                    'schema' => [
                        'type' => 'number',
                    ],
                ],
            ],
            'responses' => [
                'Success' => [
                    'description' => 'Successful response',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'time' => [
                                        'type' => 'string',
                                        'example' => '0.26',
                                    ],
                                    'query' => [
                                        'type' => 'string',
                                    ],
                                    'source' => [
                                        'type' => 'string',
                                        'example' => 'db',
                                    ],
                                    'length' => [
                                        'type' => 'integer',
                                        'example' => 50,
                                    ],
                                    'results' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                        ],
                                    ],
                                    'supported_params' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
