<?php
// src/app/Endpoints/Handlers/PagesHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

use App\Legacy\AddParams;
use App\Legacy\Helps;


function pages_query($endpoint_params, $SELECT, $DISTINCT, $get)
{

    $select = ($SELECT == "*") ? "title, word, translate_type, cat, lang, user, target, date, pupdate, add_date, deleted, mdwiki_revid, campaign" : $SELECT;

    $qua = <<<SQL
        SELECT $DISTINCT $select
        FROM $get p
        LEFT JOIN categories ca ON p.cat = ca.category
    SQL;

    [$query, $params] = AddParams::add_li_params($qua, [], $endpoint_params, ['campaign', 'cat', 'category']);

    $campaign_raw = $_GET['campaign'] ?? null;
    $category_raw = $_GET['category'] ?? $_GET['cat'] ?? null;

    $campaign = Helps::sanitize_input($campaign_raw ?? '', '/^[A-Za-z0-9- ]+$/');
    $category = Helps::sanitize_input($category_raw ?? '', '/^[A-Za-z0-9- ]+$/');

    if ($category !== null) {
        $query    .= " AND p.cat = ?";
        $params[]  = $category;
    } elseif ($campaign !== null) {
        // $query .= " AND p.cat IN (SELECT category FROM categories WHERE campaign = ?)";
        $query    .= " AND ca.campaign = ?";
        $params[]  = $campaign;
    }

    return [$query, $params, ""];
}

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
