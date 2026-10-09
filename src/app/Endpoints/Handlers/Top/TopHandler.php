<?php
// src/app/Endpoints/Handlers/Top/TopHandler.php
declare(strict_types=1);

namespace App\Endpoints\Handlers\Top;
use App\Endpoints\{DefinedEndpoint, EndpointContext, QuerySpec};
use App\Endpoints\Definition\{EndpointDefinition, Param};

abstract class TopHandler implements DefinedEndpoint
{
    /** عمود/أعمدة SELECT الأولى */
    abstract protected function selectField(): string;

    /** عمود GROUP BY */
    abstract protected function groupColumn(): string;

    abstract protected function endpointName(): string;
    abstract protected function summary(): string;

    public function definition(): EndpointDefinition
    {
        return new EndpointDefinition(
            endpoint: $this->endpointName(),
            summary: $this->summary(),
            tag: 'users',
            params: [
                new Param(name: 'year', column: 'YEAR(p.pupdate)', type: 'number', placeholder: 'year of date', noEmptyValue: true, doc: 'YearParam'),
                new Param(name: 'month', column: 'MONTH(p.pupdate)', type: 'number', placeholder: 'month of date', noEmptyValue: true),
                new Param(name: 'user_group', column: 'u.user_group', placeholder: 'User Group Name', noEmptyValue: true),
                new Param(name: 'cat', column: 'p.cat', placeholder: 'Category', noEmptyValue: true),
            ],
        );
    }

    public function handle(EndpointContext $ctx): QuerySpec
    {
        $sql = "SELECT
                {$this->selectField()},
                COUNT(p.target) AS targets,
                SUM(CASE
                    WHEN p.word IS NOT NULL AND p.word != 0 AND p.word != '' THEN p.word
                    WHEN translate_type = 'all' THEN w.w_all_words
                    ELSE w.w_lead_words
                END) AS words,
                SUM(CASE
                    WHEN v.views IS NULL OR v.views = '' THEN 0
                    ELSE CAST(v.views AS UNSIGNED)
                END) AS views

            FROM pages p
            LEFT JOIN users u        ON p.user = u.username
            LEFT JOIN words w        ON w.w_title = p.title
            LEFT JOIN views_new_all v ON p.target = v.target AND p.lang = v.lang
            LEFT JOIN langs la       ON p.lang = la.code

            WHERE p.target != '' AND p.target IS NOT NULL
              AND p.user != '' AND p.user IS NOT NULL
              AND p.lang != '' AND p.lang IS NOT NULL";

        // الفلاتر (AND ...) ثم GROUP BY، وبعدها ORDER/LIMIT من QueryExecutor
        [$sql, $params] = $ctx->applyFilters($sql);

        return new QuerySpec(
            $sql . ' GROUP BY ' . $this->groupColumn(),
            $params,
            defaultOrder: 'targets DESC',
        );
    }
}
