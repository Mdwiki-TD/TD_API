<?php
// src/app/Endpoints/QuerySpec.php
declare(strict_types=1);

namespace App\Endpoints;

final class QuerySpec
{
    public function __construct(
        public readonly string $sql = '',
        public readonly array $params = [],
        public readonly string $error = '',
        public readonly bool $applyOrder = true, // false للاستعلامات التي تحوي ORDER BY ثابتاً
    ) {}

    public static function fromLegacy(array $r): self
    {
        return new self((string)($r[0] ?? ''), $r[1] ?? [], (string)($r[2] ?? ''));
    }
}
