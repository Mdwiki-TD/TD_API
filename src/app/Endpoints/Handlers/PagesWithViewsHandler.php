<?php
// src/app/Endpoints/Handlers/PagesWithViewsHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\Definition\EndpointDefinition;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

final class PagesWithViewsHandler implements EndpointHandler
{
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
