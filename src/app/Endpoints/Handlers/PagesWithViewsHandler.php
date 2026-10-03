<?php
// src/app/Endpoints/Handlers/PagesWithViewsHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};
use function API\Helps\{add_li_params, add_group};

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
        [$tail, $params] = add_li_params(self::FROM_WHERE, [], $ctx->params);

        $sql = self::SELECT . "\n" . $tail;
        $sql = add_group($sql, $ctx->data, $ctx->group);

        return new QuerySpec($sql, $params);
    }
}
