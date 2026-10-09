<?php
// src/app/Endpoints/EndpointContext.php
declare(strict_types=1);

namespace App\Endpoints;

use App\Http\Request;
use App\Query\FilterBuilder;
use App\Query\InputSanitizer;
use App\Query\Ordering;
use App\Query\SelectBuilder;

final class EndpointContext
{
    private const PATTERN = '/^[A-Za-z0-9- ]+$/';
    public readonly array $params;
    public readonly array $columns;
    public readonly string $select;
    public readonly string $distinct;
    public readonly ?string $group;
    public readonly ?string $order;

    public function __construct(
        public readonly string $get,
        public readonly array $data,
        public readonly Request $request,
    ) {
        $this->params = $data['params'] ?? [];

        $this->columns = $data['columns'] ?? [];
        $this->select = SelectBuilder::build($this->params, $this->columns, $request);

        $this->distinct = $request->enabled('distinct') ? 'DISTINCT ' : '';
        $this->group = $request->get('group');
        $this->order = $request->get('order');
    }

    /** @return array{0: string, 1: array} [sql, params] */
    public function applyFilters(string $sql, array $ignore = []): array
    {
        return FilterBuilder::apply($sql, $this->params, $this->request, $ignore);
    }

    public function applyGroup(string $sql): string
    {
        return Ordering::group($sql, $this->data, $this->group);
    }

    /** @return array{0: string, 1: array} [sql, params] */
    public function applyCampaignCategory(
        string $sql,
        array $params
    ): array {

        // Apply campaign/category filters
        $campaign = InputSanitizer::match($this->request->get('campaign') ?? '', self::PATTERN);
        $category = InputSanitizer::match(
            $this->request->get('category') ?? $this->request->get('cat') ?? '',
            self::PATTERN
        );

        $glue = FilterBuilder::glue($sql);

        if (FilterBuilder::isValid($category)) {
            $sql .= "$glue p.cat = ?";
            $params[] = $category;
        } elseif (FilterBuilder::isValid($campaign)) {
            $sql .= "$glue ca.campaign = ?";
            $params[] = $campaign;
        }
        return [$sql, $params];
    }
    public function isValid(?string $value): bool
    {
        return FilterBuilder::isValid($value);
    }
}
