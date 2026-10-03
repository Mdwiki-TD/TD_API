<?php
// src/app/Endpoints/Handlers/PagesHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};
use function API\Helps\{add_li_params, sanitize_input};

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
        [$sql, $params] = add_li_params($sql, [], $ctx->params, ['campaign', 'cat', 'category']);

        $campaign = sanitize_input($ctx->request->get('campaign') ?? '', self::PATTERN);
        $category = sanitize_input(
            $ctx->request->get('category') ?? $ctx->request->get('cat') ?? '',
            self::PATTERN
        );

        $glue = stripos($sql, 'WHERE') !== false ? ' AND' : ' WHERE';

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
