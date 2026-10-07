<?php
// src/app/Endpoints/Handlers/ViewsHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};

final class ViewsHandler extends AbstractEndpointHandler
{
    public function __construct(
        private ?string $requiredParam = null, // 'user' or 'lang'
        private string $defaultOrder = '',
        private array $columns = [],
    ) {}

    public function handle(EndpointContext $ctx): QuerySpec
    {
        if ($this->requiredParam !== null && !$ctx->request->enabled($this->requiredParam)) {
            return new QuerySpec('', []); // NOP
        }

        $base = <<<SQL
            SELECT p.title, v.target, v.lang, v.views
            FROM views_new_all v
            LEFT JOIN pages p
                ON p.target = v.target
                AND p.lang = v.lang
        SQL;

        [$sql, $params] = $ctx->applyFilters($base);

        return new QuerySpec($sql, $params, defaultOrder: $this->defaultOrder);
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getParams(): array
    {
        return [
            [
                "name" => "lang",
                "column" => "p.lang",
                "type" => "text",
                "placeholder" => "Language code",
            ],
            [
                "name" => "year",
                "column" => "YEAR(p.pupdate)",
                "type" => "number",
                "placeholder" => "Year of publication",
            ],
        ];
    }
}
