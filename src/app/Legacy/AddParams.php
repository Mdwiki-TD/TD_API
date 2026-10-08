<?php
namespace App\Legacy;

class AddParams
{

    public static function change_types($types, $endpoint_params, $ignore_params)
    {

        // $types = array_flip($types);

        $types2 = [];

        foreach ($types as $type) {
            $types2[$type] = ["column" => $type];
        }
        // ---value_can_be_null
        $types = $types2;

        if (count($types) == 0 && count($endpoint_params) > 0) {
            foreach ($endpoint_params as $param) {
                // { "name": "title", "column": "w_title", "type": "text", "placeholder": "Page Title" },
                // , "no_select": true
                if (isset($param['no_select'])) {
                    continue;
                }

                $types[$param['name']] = $param;
            }
        }

        foreach ($ignore_params as $param) {
            if (isset($types[$param])) {
                unset($types[$param]);
            }

        }

        return $types;
    }

    public static function add_distinct($qua)
    {
        $qua = preg_replace("/^\s*SELECT\s*/i", "SELECT DISTINCT ", $qua);
        return $qua;
    }

    public static function add_one_param($qua, $column, $added, $tabe)
    {

        $add_str = "";
        $params  = [];

        $where_or_and = (strpos(strtoupper($qua), 'WHERE') !== false) ? ' AND ' : ' WHERE ';

        if ($added == "not_mt" || $added == "not_empty") {
            $add_str = " $where_or_and ($column != '' AND $column IS NOT NULL) ";

        } elseif ($added == "mt" || $added == "empty") {
            $add_str = " $where_or_and ($column = '' OR $column IS NULL) ";

        } elseif ($added == ">0" || $added == "&#62;0") {
            $add_str = " $where_or_and $column > 0 ";

        } elseif (($tabe['type'] ?? '') == 'array') {
            list($add_str, $params) = self::add_array_params($add_str, $params, $tabe['name'], $column, $where_or_and);
        } else {
            $params[] = $added;
            $add_str  = " $where_or_and $column = ? ";

            $value_can_be_null = isset($tabe['value_can_be_null']) ? $tabe['value_can_be_null'] : false;

            if ($value_can_be_null) {
                $add_str = " $where_or_and ($column = ? OR $column IS NULL OR $column = '') ";
            }
        }

        return [$add_str, $params];
    }

    public static function add_array_params($qua, $params, $param = "titles", $column = "title", $where_or_and = "")
    {

        if (empty($where_or_and)) {
            $where_or_and = (strpos(strtoupper($qua), 'WHERE') !== false) ? ' AND ' : ' WHERE ';
        }

        $titles = $_GET[$param] ?? [];

        if (! empty($titles) && is_array($titles)) {

            $placeholders = rtrim(str_repeat('?,', count($titles)), ',');

            $qua .= " $where_or_and $column IN ($placeholders)";

            $params = array_merge($params, $titles);
        }

        return [$qua, $params];
    }

    public static function read_scalar_param(string $key): ?string
    {
        // $v = filter_input(INPUT_GET, $key) ?? null;
        $v = $_GET[$key] ?? null;
        if (! is_string($v)) {
            return null;
        }
        return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $v) ?? '');
    }

    public static function add_li_params(string $qua, array $types, array $endpoint_params = [], array $ignore_params = []): array
    {
        $types = self::change_types($types, $endpoint_params, $ignore_params);

        $params = [];

        foreach ($types as $type => $tabe) {

            $column = $tabe['column'];

            if (empty($column)) {
                continue;
            }

            if (isset($_GET[$type]) || isset($_GET[$column])) {

                // filter input
                $added = self::read_scalar_param($type) ?? '';
                $added = ($added !== '') ? $added : (self::read_scalar_param($column) ?? '');

                // if "limit" in endpoint_params remove it
                if ($column == "limit" || $column == "select" || ($added && strtolower($added) == "all")) {
                    continue;
                }

                if (isset($tabe['no_empty_value']) && empty($added)) {
                    continue;
                }

                if ($column == "distinct" && $added == "1") {
                    if (strpos(strtolower($qua), 'distinct') === false) {
                        $qua = self::add_distinct($qua);
                    }
                } else {
                    list($add_str, $new_params) = self::add_one_param($qua, $column, $added, $tabe);

                    $params = array_merge($params, $new_params);

                    $qua .= $add_str;
                }
            }
        }

        return [$qua, $params];
    }
}
