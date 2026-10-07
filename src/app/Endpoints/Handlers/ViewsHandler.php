<?php
// src/app/Endpoints/Handlers/ViewsHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;
use App\Endpoints\Definition\EndpointDefinition;
use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};

/** استعلامات views_new_all مع pages: تختلف بالأعمدة ونوع الـ JOIN وبارامتر مطلوب اختياري */
final class ViewsHandler implements EndpointHandler
{
    public function __construct(
        private ?string $requiredParam = null,
        private string $defaultOrder = '',
    ) {}

    public function handle(EndpointContext $ctx): QuerySpec
    {
        if ($this->requiredParam !== null && !$ctx->request->enabled($this->requiredParam)) {
            return new QuerySpec(error: "{$this->requiredParam} param required");
        }

        $sql = "SELECT p.title, v.target, v.lang, v.views
                FROM views_new_all v
                LEFT JOIN pages p
                    ON p.target = v.target
                    AND p.lang = v.lang";

        [$sql, $params] = $ctx->applyFilters($sql);

        return new QuerySpec($sql, $params, defaultOrder: $this->defaultOrder);
    }
}
