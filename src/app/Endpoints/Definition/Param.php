<?php

declare(strict_types=1);

namespace App\Endpoints\Definition;

/**
 * endpoint param: feeds runtime filtering, and generates OpenAPI documentation.
 */
final class Param
{
    /**
     * @param ?array $options  null = not defined, [] = defined and empty (appears in supported_values)
     * @param string|array|null $doc  null = auto-link by name to a shared component (or inline if none exists),
     *                                string = explicit shared component name, array = full inline definition
     */
    public function __construct(
        public readonly string $name,
        public readonly string $column,
        public readonly string $type = 'text',        // text|number|switch|array|select
        public readonly string $placeholder = '',
        public readonly ?array $options = null,
        public readonly mixed $default = null,
        public readonly mixed $value = null,
        public readonly bool $required = false,
        public readonly bool $noSelect = false,
        public readonly ?bool $noEmptyValue = null,
        public readonly bool $valueCanBeNull = false,
        public readonly string|array|null $doc = null,
    ) {
    }

    /**
     * The same format as endpoint_params.json (absent keys are omitted)
     */
    public function toArray(): array
    {
        return [
            'name'           => $this->name,
            'column'         => $this->column,
            'type'           => $this->type,
            'placeholder'    => $this->placeholder,
            'noSelect'       => $this->noSelect,
            'default'        => $this->default,
            'required'       => $this->required,
            'options'        => $this->options,
            'value'          => $this->value,
            'valueCanBeNull' => $this->valueCanBeNull,
            'noEmptyValue'   => $this->noEmptyValue,
        ];
    }
}
