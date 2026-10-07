<?php
// src/app/Endpoints/Definition/Param.php
declare(strict_types=1);

namespace App\Endpoints\Definition;

final class Param
{
    public function __construct(
        public readonly string $name,
        public readonly string $column = '',
        public readonly string $type = 'text',       // text|number|switch|array|select
        public readonly string $placeholder = '',
        public readonly ?array $options = null,      // null = غائب، [] = موجود وفارغ
        public readonly mixed $default = null,
        public readonly mixed $value = null,
        public readonly bool $required = false,
        public readonly bool $noSelect = false,
        public readonly ?bool $noEmptyValue = null,  // null = غائب، false = موجود
        public readonly bool $valueCanBeNull = false,
        // --- توثيق فقط ---
        public readonly string $description = '',
        public readonly mixed $example = null,
    ) {}
}
