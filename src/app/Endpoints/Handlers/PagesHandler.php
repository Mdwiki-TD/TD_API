<?php
// src/app/Endpoints/Handlers/PagesHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\Definition\EndpointDefinition;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

final class PagesHandler implements EndpointHandler
{
    private const DEFAULT_SELECT =
    'title, word, translate_type, cat, lang, user, target, date, pupdate, add_date, deleted, mdwiki_revid, campaign';

    /** @param 'pages'|'pages_users' $table يُمرَّر ثابتاً من Registry */
    public function __construct(private string $table) {}

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
