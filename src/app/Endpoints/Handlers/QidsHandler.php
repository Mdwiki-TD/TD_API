<?php
// src/app/Endpoints/Handlers/QidsHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{AbstractEndpointHandler, EndpointContext, QuerySpec};
use function API\Qids\qids_qua;

final class QidsHandler extends AbstractEndpointHandler
{
    public function handle(EndpointContext $ctx): QuerySpec
    {
        $sql = qids_qua($ctx->get);
        return new QuerySpec($sql, [], applyOrder: false);
    }

    public function getColumns(): array
    {
        return [
            "title",
            "qid",
        ];
    }

    public function getParams(): array
    {
        return [
            [
                "name" => "dis",
                "column" => "dis",
                "type" => "select",
                "options" => [
                    "",
                    "empty",
                    "all",
                    "duplicate",
                ],
            ],
        ];
    }
}
