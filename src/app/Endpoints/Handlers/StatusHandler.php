<?php
// src/app/Endpoints/Handlers/StatusHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers;

use App\Endpoints\{EndpointContext, EndpointHandler, QuerySpec};
use App\Query\InputSanitizer;

final class StatusHandler implements EndpointHandler
{
    private const WORDS = '/^[A-Za-z0-9- ]+$/';

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $r = $ctx->request;

        $sql = "SELECT LEFT(p.pupdate, 7) AS date, COUNT(*) AS count
                FROM pages p
                LEFT JOIN users u ON p.user = u.username
                WHERE p.target != ''";
        $params = [];

        if (($year = InputSanitizer::match($r->get('year'), '/^\d+$/')) !== null) {
            $sql .= ' AND YEAR(p.pupdate) = ?';
            $params[] = $year;
        }

        if (($group = InputSanitizer::match($r->get('user_group'), self::WORDS)) !== null) {
            $sql .= ' AND u.user_group = ?';
            $params[] = $group;
        }

        $campaign = InputSanitizer::match($r->get('campaign'), self::WORDS);
        $category = InputSanitizer::match($r->get('category') ?? $r->get('cat'), self::WORDS);

        if ($category !== null) {
            $sql .= ' AND p.cat = ?';
            $params[] = $category;
        } elseif ($campaign !== null) {
            $sql .= ' AND p.cat IN (SELECT category FROM categories WHERE campaign = ?)';
            $params[] = $campaign;
        }

        return new QuerySpec($sql . ' GROUP BY 1', $params, defaultOrder: '1 ASC');
    }
}
