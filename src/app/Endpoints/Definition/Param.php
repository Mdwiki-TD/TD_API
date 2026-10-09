<?php

declare(strict_types=1);

namespace App\Endpoints\Definition;

/** بارامتر endpoint: يغذي الفلترة وقت التشغيل، ويولّد وثيقة OpenAPI. */
final class Param
{
    /**
     * @param ?array $options  null = غير معرّف، [] = معرّف وفارغ (يظهر في supported_values)
     * @param string|array|null $doc  null = ربط تلقائي بالاسم بمكوّن مشترك (أو inline إن لم يوجد)،
     *                                string = اسم مكوّن مشترك صريح، array = تعريف inline كامل
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
    ) {}

    /** نفس شكل endpoint_params.json بالضبط (المفاتيح الغائبة تبقى غائبة) */
    public function toArray(): array
    {
        $a = ['name' => $this->name, 'column' => $this->column, 'type' => $this->type];
        if ($this->placeholder !== '')   $a['placeholder'] = $this->placeholder;
        if ($this->noSelect)             $a['no_select'] = true;
        if ($this->default !== null)     $a['default'] = $this->default;
        if ($this->required)             $a['required'] = true;
        if ($this->options !== null)     $a['options'] = $this->options;
        if ($this->value !== null)       $a['value'] = $this->value;
        if ($this->valueCanBeNull)       $a['value_can_be_null'] = true;
        if ($this->noEmptyValue !== null) $a['noEmptyValue'] = $this->noEmptyValue;
        return $a;
    }
}
