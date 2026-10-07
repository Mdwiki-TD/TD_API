<?php
// src/app/Config/EndpointConfig.php
declare(strict_types=1);

namespace App\Config;

use App\Endpoints\EndpointRegistry;

final class EndpointConfig
{
    private EndpointRegistry $registry;

    public function __construct(?EndpointRegistry $registry = null)
    {
        $this->registry = $registry ?? new EndpointRegistry();
    }

    /** بيانات الـ endpoint مع تتبع redirect واستخراج المعلمات والأعمدة من كلاس Handler الخاص بها */
    public function find(string $get): array
    {
        if ($get === 'pages_with_views') {
            $get = 'pages';
        }

        $handler = $this->registry->getHandler($get);
        if ($handler === null) {
            return [];
        }

        return [
            'params'       => $handler->getParams(),
            'columns'      => $handler->getColumns(),
            'order_values' => $handler->getOrderValues(),
        ];
    }
}
