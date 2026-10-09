<?php
// src/app/Endpoints/Handlers/PagesHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, QuerySpec};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\Param;
use App\Endpoints\Definition\EndpointDefinition;

final class PagesHandler implements DefinedEndpoint
{
    private const DEFAULT_SELECT =
        'title, word, translate_type, cat, lang, user, target, date, pupdate, add_date, deleted, mdwiki_revid, campaign';

    /** @param 'pages'|'pages_users' $table يُمرَّر ثابتاً من Registry */
    public function __construct(private string $table)
    {
    }
    public function definition(): EndpointDefinition
    {
        $definitions = [
            'pages'       => new EndpointDefinition(
                endpoint: 'pages',
                summary: 'Retrieve pages list',
                tag: 'pages',
                params: [
                    new Param(
                        name: 'title',
                        column: 'p.title',
                        placeholder: 'Page Title'
                    ),
                    new Param(
                        name: 'lang',
                        column: 'p.lang',
                        placeholder: 'Language code'
                    ),
                    new Param(
                        name: 'user',
                        column: 'p.user',
                        placeholder: 'Username'
                    ),
                    new Param(
                        name: 'target',
                        column: 'p.target',
                        placeholder: 'Target'
                    ),
                    new Param(
                        name: 'cat',
                        column: 'p.cat',
                        placeholder: 'Category'
                    ),
                    new Param(
                        name: 'campaign',
                        column: 'campaign',
                        placeholder: 'Campaign'
                    ),
                    new Param(
                        name: 'group',
                        column: 'group',
                        placeholder: 'Group by field',
                        options: [],
                        noSelect: true
                    ),
                    new Param(
                        name: 'order',
                        column: 'order',
                        placeholder: 'Order by',
                        noSelect: true
                    ),
                    new Param(
                        name: 'pupdate',
                        column: 'p.pupdate',
                        placeholder: 'Date of publication'
                    ),
                    new Param(
                        name: 'add_date',
                        column: 'p.add_date',
                        placeholder: 'Date of addition to DB'
                    ),
                    new Param(
                        name: 'limit',
                        column: 'p.limit',
                        type: 'number',
                        placeholder: 'Limit results',
                        value: '50',
                        noSelect: true
                    ),
                    new Param(
                        name: 'offset',
                        column: 'p.offset',
                        type: 'number',
                        placeholder: 'Offset results',
                        value: '0',
                        noSelect: true
                    ),
                    new Param(
                        name: 'year',
                        column: 'YEAR(p.pupdate)',
                        type: 'number',
                        placeholder: 'Year of publication',
                        doc: 'PublicationYearParam'
                    ),
                    new Param(
                        name: 'date_year',
                        column: 'YEAR(p.date)',
                        type: 'number',
                        placeholder: 'year of date'
                    ),
                    new Param(
                        name: 'select',
                        column: 'select',
                        placeholder: 'Select fields',
                        options: ['count(*)'],
                        noSelect: true
                    ),
                    new Param(
                        name: 'translate_type',
                        column: 'p.translate_type',
                        type: 'select',
                        options: ['all', 'lead']
                    ),
                    new Param(
                        name: 'distinct',
                        column: 'p.distinct',
                        type: 'switch',
                        noSelect: true
                    ),
                    new Param(
                        name: 'Deleted',
                        column: 'p.deleted',
                        type: 'switch',
                        placeholder: '0 or 1'
                    ),
                ],
                columns: ['title', 'word', 'translate_type', 'cat', 'lang', 'user', 'target', 'date', 'pupdate', 'add_date', 'deleted', 'mdwiki_revid'],
                orderValues: [
                    'pupdate_or_add_date' => 'GREATEST(UNIX_TIMESTAMP(pupdate), UNIX_TIMESTAMP(add_date))',
                ],
            ),
            'pages_users' => new EndpointDefinition(
                endpoint: 'pages_users',
                summary: 'Retrieve pages and users data',
                tag: 'pages',
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
                    new Param(
                        name: 'target',
                        column: 'target',
                        placeholder: 'Target'
                    ),
                    new Param(
                        name: 'title',
                        column: 'title',
                        placeholder: 'Page Title'
                    ),
                    new Param(
                        name: 'order',
                        column: 'order',
                        placeholder: 'Order by',
                        noSelect: true
                    ),
                    new Param(
                        name: 'pupdate',
                        column: 'pupdate',
                        placeholder: 'Date of publication'
                    ),
                    new Param(
                        name: 'add_date',
                        column: 'add_date',
                        placeholder: 'Date of addition to DB'
                    ),
                    new Param(
                        name: 'group',
                        column: 'group',
                        placeholder: 'Group by field',
                        options: [],
                        noSelect: true
                    ),
                    new Param(
                        name: 'limit',
                        column: 'limit',
                        type: 'number',
                        placeholder: 'Limit results',
                        value: '50',
                        noSelect: true
                    ),
                    new Param(
                        name: 'offset',
                        column: 'p.offset',
                        type: 'number',
                        placeholder: 'Offset results',
                        value: '0',
                        noSelect: true
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
                columns: ['title', 'word', 'translate_type', 'cat', 'lang', 'user', 'target', 'date', 'pupdate', 'add_date', 'deleted', 'mdwiki_revid'],
            ),
        ];
        return $definitions[$this->table];
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $select = ($ctx->select === '*') ? self::DEFAULT_SELECT : $ctx->select;

        $sql = "SELECT {$ctx->distinct}{$select}
                FROM `{$this->table}` p
                LEFT JOIN categories ca ON p.cat = ca.category
        ";

        // campaign / cat / category are handled manually below
        [$sql, $params] = $ctx->applyFilters($sql, ['campaign', 'cat', 'category']);

        // Apply campaign/category filters
        [$sql, $params] = $ctx->applyCampaignCategory($sql, $params);

        return new QuerySpec($sql, $params);
    }
}
