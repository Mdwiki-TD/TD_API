<?php
// src/app/Endpoints/Handlers/PagesHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Query\FilterBuilder;
use App\Query\InputSanitizer;

use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

final class PagesHandler implements EndpointHandler
{
    private const DEFAULT_SELECT =
    'title, word, translate_type, cat, lang, user, target, date, pupdate, add_date, deleted, mdwiki_revid, campaign';

    private const PATTERN = '/^[A-Za-z0-9- ]+$/';

    /** @param 'pages'|'pages_users' $table يُمرَّر ثابتاً من Registry */
    public function __construct(private string $table) {}

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $select = ($ctx->select === '*') ? self::DEFAULT_SELECT : $ctx->select;

        $sql = "SELECT {$ctx->distinct}{$select}
                FROM `{$this->table}` p
                LEFT JOIN categories ca ON p.cat = ca.category";

        // campaign / cat / category تُعالج يدوياً أدناه
        [$sql, $params] = $ctx->applyFilters($sql, ['campaign', 'cat', 'category']);

        $campaign = InputSanitizer::match($ctx->request->get('campaign') ?? '', self::PATTERN);
        $category = InputSanitizer::match(
            $ctx->request->get('category') ?? $ctx->request->get('cat') ?? '',
            self::PATTERN
        );

        $glue = FilterBuilder::glue($sql);

        if ($category !== null) {
            $sql .= "$glue p.cat = ?";
            $params[] = $category;
        } elseif ($campaign !== null) {
            $sql .= "$glue ca.campaign = ?";
            $params[] = $campaign;
        }

        return new QuerySpec($sql, $params);
    }
}
