<?php

declare(strict_types=1);

namespace App\Endpoints\Definition;

/**
 * Generated from endpoint_params.json + openapi.json (transitional).
 * After verification, each definition will move to its handler and this file will be deleted.
 */
final class EndpointDefinitions
{
    /** @return array<string, EndpointDefinition> */
    public static function all(): array
    {
        return [
            'assessments'           => new EndpointDefinition(
                endpoint: 'assessments',
                summary: 'Retrieve page assessments',
                tag: 'pages_infos',
                params: [
                    new Param(
                        name: 'title',
                        column: 'title',
                        placeholder: 'Page Title'
                    ),
                    new Param(
                        name: 'importance',
                        column: 'importance',
                        placeholder: 'Importance'
                    ),
                    new Param(
                        name: 'distinct',
                        column: 'distinct',
                        type: 'switch',
                        noSelect: true
                    ),
                ],
                columns: ['title', 'importance'],
            ),
            'categories'            => new EndpointDefinition(
                endpoint: 'categories',
                summary: 'Retrieve categories',
                tag: 'other',
                params: [
                    new Param(
                        name: 'Depth',
                        column: 'depth',
                        type: 'number',
                        placeholder: 'Depth Level'
                    ),
                    new Param(
                        name: 'campaign',
                        column: 'campaign',
                        placeholder: 'Campaign'
                    ),
                    new Param(
                        name: 'select',
                        column: 'select',
                        placeholder: 'Select fields',
                        noSelect: true
                    ),
                ],
                columns: ['category', 'category2', 'display', 'campaign', 'depth', 'is_default'],
            ),
            'coordinators'          => new EndpointDefinition(
                endpoint: 'coordinators',
                summary: 'Retrieve coordinators information',
                tag: 'users',
                params: [
                    new Param(
                        name: 'Username',
                        column: 'username',
                        placeholder: 'Coordinator Username'
                    ),
                ],
                columns: ['username', 'is_active'],
            ),
            'count_pages'           => new EndpointDefinition(
                endpoint: 'count_pages',
                summary: 'Count pages',
                tag: 'statistics',
                params: [
                    new Param(
                        name: 'target',
                        column: 'target',
                        placeholder: 'Target'
                    ),
                ],
            ),
            'enwiki_pageviews'      => new EndpointDefinition(
                endpoint: 'enwiki_pageviews',
                summary: 'Retrieve English Wikipedia page views',
                tag: 'pages_infos',
                params: [
                    new Param(
                        name: 'title',
                        column: 'title',
                        placeholder: 'Page Title'
                    ),
                    new Param(
                        name: 'en_views',
                        column: 'en_views',
                        type: 'number',
                        placeholder: 'Views Count'
                    ),
                ],
                columns: ['title', 'en_views'],
            ),
            'full_translators'      => new EndpointDefinition(
                endpoint: 'full_translators',
                summary: 'Retrieve full translators',
                tag: 'users',
                columns: ['user', 'is_active'],
            ),
            'in_process'            => new EndpointDefinition(
                endpoint: 'in_process',
                summary: 'Retrieve in-process pages',
                tag: 'pages',
                params: [
                    new Param(
                        name: 'lang',
                        column: 'lang',
                        placeholder: 'Language code'
                    ),
                    new Param(
                        name: 'cat',
                        column: 'cat',
                        placeholder: 'Category'
                    ),
                    new Param(
                        name: 'user',
                        column: 'user',
                        placeholder: 'Username'
                    ),
                    new Param(
                        name: 'select',
                        column: 'select',
                        placeholder: 'Select fields',
                        noSelect: true
                    ),
                    new Param(
                        name: 'distinct',
                        column: 'distinct',
                        type: 'switch',
                        noSelect: true
                    ),
                    new Param(
                        name: 'group',
                        column: 'group',
                        placeholder: 'Group by field',
                        noSelect: true
                    ),
                    new Param(
                        name: 'order',
                        column: 'order',
                        placeholder: 'Order by',
                        noSelect: true
                    ),
                    new Param(
                        name: 'year',
                        column: 'YEAR(add_date)',
                        type: 'number',
                        placeholder: 'year of date',
                        doc: 'YearParam'
                    ),
                ],
                columns: ['title', 'user', 'lang', 'cat', 'translate_type', 'word', 'add_date'],
            ),
            'langs'                 => new EndpointDefinition(
                endpoint: 'langs',
                summary: 'Retrieve language names',
                tag: 'languages',
                columns: ['code', 'autonym', 'name', 'redirects'],
            ),
            'projects'              => new EndpointDefinition(
                endpoint: 'projects',
                summary: 'Retrieve projects',
                tag: 'other',
                columns: ['g_id', 'g_title'],
            ),
            'refs_counts'           => new EndpointDefinition(
                endpoint: 'refs_counts',
                summary: 'Retrieve reference counts for pages',
                tag: 'pages_infos',
                params: [
                    new Param(
                        name: 'title',
                        column: 'r_title',
                        placeholder: 'Page Title'
                    ),
                    new Param(
                        name: 'lead',
                        column: 'r_lead_refs',
                        type: 'number',
                        placeholder: 'Lead Refs Count',
                        doc: 'LeadRefsParam'
                    ),
                    new Param(
                        name: 'all',
                        column: 'r_all_refs',
                        type: 'number',
                        placeholder: 'All Refs Count'
                    ),
                ],
                columns: ['r_id', 'r_title', 'r_lead_refs', 'r_all_refs'],
            ),
            'settings'              => new EndpointDefinition(
                endpoint: 'settings',
                summary: 'Retrieve settings',
                tag: 'other',
                columns: ['title', 'displayed', 'Type', 'value', 'ignored'],
            ),
            'titles'                => new EndpointDefinition(
                endpoint: 'titles',
                summary: 'Retrieve pages by title or importance',
                tag: 'pages_infos',
                params: [
                    new Param(
                        name: 'title',
                        column: 'ase.title',
                        placeholder: 'Page Title'
                    ),
                    new Param(
                        name: 'importance',
                        column: 'ase.importance',
                        placeholder: 'Importance'
                    ),
                    new Param(
                        name: 'titles',
                        column: 'ase.title',
                        type: 'array'
                    ),
                ],
            ),
            'translate_type'        => new EndpointDefinition(
                endpoint: 'translate_type',
                summary: 'Retrieve translation type data',
                tag: 'languages',
                params: [
                    new Param(
                        name: 'Lead',
                        column: 'tt_lead',
                        type: 'number',
                        placeholder: 'Lead Translation (0 or 1)',
                        doc: 'LeadTranslationParam'
                    ),
                    new Param(
                        name: 'Full',
                        column: 'tt_full',
                        type: 'number',
                        placeholder: 'Full Translation (0 or 1)'
                    ),
                ],
                columns: ['tt_id', 'tt_title', 'tt_lead', 'tt_full'],
            ),
            'users_by_last_pupdate' => new EndpointDefinition(
                endpoint: 'users_by_last_pupdate',
                summary: 'Retrieve users by last pupdate',
                tag: 'users',
            ),
            'users_no_inprocess'    => new EndpointDefinition(
                endpoint: 'users_no_inprocess',
                summary: 'Retrieve users not in process',
                tag: 'users',
                columns: ['user', 'is_active'],
            ),
            'words'                 => new EndpointDefinition(
                endpoint: 'words',
                summary: 'Retrieve word counts for pages',
                tag: 'pages_infos',
                params: [
                    new Param(
                        name: 'title',
                        column: 'w_title',
                        placeholder: 'Page Title'
                    ),
                    new Param(
                        name: 'lead_words',
                        column: 'w_lead_words',
                        type: 'number',
                        placeholder: 'Lead words Count'
                    ),
                    new Param(
                        name: 'all_words',
                        column: 'w_all_words',
                        type: 'number',
                        placeholder: 'Total words Count'
                    ),
                ],
                columns: ['w_id', 'w_title', 'w_lead_words', 'w_all_words'],
            ),
            'revids'                => new EndpointDefinition(
                endpoint: 'revids',
                summary: 'Retrieve revision IDs',
                tag: 'pages_infos',
                description: 'Retrieve revision IDs for specified titles or title arrays',
                params: [
                    new Param(
                        name: 'title',
                        column: 'title',
                        placeholder: 'Page Title'
                    ),
                    new Param(
                        name: 'titles',
                        column: 'title',
                        type: 'array'
                    ),
                ],
                columns: ['title', 'revid'],
            ),
            'publish_reports'       => new EndpointDefinition(
                endpoint: 'publish_reports',
                summary: 'publish reports',
                tag: 'other',
                params: [
                    new Param(
                        name: 'year',
                        column: 'YEAR(date)',
                        type: 'number',
                        placeholder: 'year of date',
                        doc: 'YearParam'
                    ),
                    new Param(
                        name: 'month',
                        column: 'MONTH(date)',
                        type: 'number',
                        placeholder: 'month of date'
                    ),
                    new Param(
                        name: 'title',
                        column: 'title',
                        placeholder: 'Page Title'
                    ),
                    new Param(
                        name: 'user',
                        column: 'user',
                        placeholder: 'user'
                    ),
                    new Param(
                        name: 'lang',
                        column: 'lang',
                        placeholder: 'Language code'
                    ),
                    new Param(
                        name: 'sourcetitle',
                        column: 'sourcetitle',
                        placeholder: 'sourcetitle'
                    ),
                    new Param(
                        name: 'result',
                        column: 'result',
                        placeholder: 'result'
                    ),
                    new Param(
                        name: 'select',
                        column: 'select',
                        placeholder: 'Select fields',
                        noSelect: true
                    ),
                    new Param(
                        name: 'distinct',
                        column: 'distinct',
                        type: 'switch',
                        noSelect: true
                    ),
                ],
                columns: ['date', 'title', 'user', 'lang', 'sourcetitle', 'result', 'data'],
            ),
            'publish_reports_stats' => new EndpointDefinition(
                endpoint: 'publish_reports_stats',
                summary: 'publish reports stats',
                tag: 'other',
                params: [
                    new Param(
                        name: 'lang',
                        column: 'lang',
                        placeholder: 'Language code'
                    ),
                    new Param(
                        name: 'user',
                        column: 'user',
                        placeholder: 'Username'
                    ),
                ],
            ),
            'language_settings'     => new EndpointDefinition(
                endpoint: 'language_settings',
                summary: 'language settings',
                tag: 'other',
                params: [
                    new Param(
                        name: 'lang_code',
                        column: 'lang_code',
                        placeholder: 'Language code'
                    ),
                ],
                columns: ['lang_code', 'move_dots', 'expend', 'add_en_lang'],
            ),
            'get_lang_years'        => new EndpointDefinition(
                endpoint: 'get_lang_years',
                summary: 'Years of language',
                tag: 'languages',
                description: 'list of years of language',
                params: [
                    new Param(
                        name: 'lang',
                        column: 'p.lang',
                        placeholder: 'Language code',
                        noEmptyValue: false
                    ),
                ],
            ),
            'pages_langs'           => new EndpointDefinition(
                endpoint: 'pages_langs',
                summary: 'Retrieve languages that have translated pages',
                tag: 'languages',
            ),
            'pages_users_langs'     => new EndpointDefinition(
                endpoint: 'pages_users_langs',
                summary: 'Retrieve languages that have user pages',
                tag: 'languages',
            ),
        ];
    }

    public static function alltoArray(): array
    {
        return array_map(function (EndpointDefinition $definition) {
            return $definition->toArray();
        }, self::all());
    }

    public static function for(string $name): ?EndpointDefinition
    {
        return self::all()[$name] ?? null;
    }
}
