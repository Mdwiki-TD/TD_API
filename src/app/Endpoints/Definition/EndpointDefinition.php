<?php
// src/app/Endpoints/Definition/EndpointDefinition.php
declare(strict_types=1);

namespace App\Endpoints\Definition;

final class EndpointDefinition
{
    /**
     * @param list<Param> $params
     * @param list<string> $columns            الأعمدة المسموح بها في select/order/group
     * @param array<string,string> $orderValues
     * @param array<string,string> $response   حقول صف النتيجة: اسم => نوع (string|integer|number|boolean|array|object)
     */
    public function __construct(
        public readonly array $params = [],
        public readonly array $columns = [],
        public readonly array $orderValues = [],
        // --- توثيق فقط ---
        public readonly string $summary = '',
        public readonly string $description = '',
        public readonly string $tag = 'general',
        public readonly array $response = [],
        public readonly ?array $responseOverride = null, // لـ leaderboard_table_formated (شكل غير مسطّح)
        public readonly bool $deprecated = false,
    ) {}
}
