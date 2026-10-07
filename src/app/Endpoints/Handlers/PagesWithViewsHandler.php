<?php
// src/app/Endpoints/Handlers/PagesWithViewsHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

final class PagesWithViewsHandler extends AbstractEndpointHandler
{
    private PagesHandler $pagesHandler;

    public function __construct()
    {
        $this->pagesHandler = new PagesHandler('pages');
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $base = "from pages p WHERE p.target != ''";
        [$sql, $params] = $ctx->applyFilters($base);

        $prefix = <<<SQL
            SELECT DISTINCT
                p.id, p.title, p.word, p.translate_type, p.cat,
                p.lang, p.user, p.target, p.date, p.pupdate,
                p.add_date, p.deleted, p.mdwiki_revid,
                (SELECT v.views FROM views_new_all v WHERE p.target = v.target AND p.lang = v.lang) AS views
        SQL;

        $full = $ctx->applyGroup($prefix . ' ' . $sql);

        return new QuerySpec($full, $params);
    }

    public function getColumns(): array
    {
        return $this->pagesHandler->getColumns();
    }

    public function getParams(): array
    {
        return $this->pagesHandler->getParams();
    }

    public function getOrderValues(): array
    {
        return $this->pagesHandler->getOrderValues();
    }
}
