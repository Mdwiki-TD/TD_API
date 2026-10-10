<?php
// src/app/Endpoints/Handlers/PagesWithViewsHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, QuerySpec};

use App\Endpoints\DefinedEndpoint;
use App\Endpoints\Definition\Param;
use App\Endpoints\Definition\EndpointDefinition;

final class PagesWithViewsHandler implements DefinedEndpoint
{
    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            endpoint: 'pages_with_views',
            summary: 'Retrieve pages with view counts (redirects to pages)',
            tag: 'pages',
            description: 'Corresponds to calling `api.php?get=pages_with_views` which redirects internally to `api.php?get=pages`. Parameters are the same as the `api.php?get=pages` endpoint.',
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
        );
    }

    private const SELECT = <<<SQL
        SELECT DISTINCT
            p.id, p.title, p.word, p.translate_type, p.cat,
            p.lang, p.user, p.target, p.date, p.pupdate,
            p.add_date, p.deleted, p.mdwiki_revid,
            (SELECT v.views FROM views_new_all v
              WHERE p.target = v.target AND p.lang = v.lang) AS views
        SQL;

    private const FROM_WHERE = <<<SQL
        FROM pages p
        WHERE p.target != ''
        SQL;

    public function handle(EndpointContext $ctx): QuerySpec
    {
        // الفلاتر تُضاف أولاً على FROM/WHERE (add_one_param تعتمد على وجود WHERE)
        [$tail, $params] = $ctx->applyFilters(self::FROM_WHERE);

        $sql = self::SELECT . "\n" . $tail;
        $sql = $ctx->applyGroup($sql);

        return new QuerySpec($sql, $params);
    }
}
