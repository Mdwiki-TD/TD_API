<?php
// src/app/helps.php

namespace Legacy;

class Helps
{

    public static function add_offset($qua)
    {
        // if $qua has OFFSET then return
        if (strpos($qua, 'OFFSET') !== false || strpos($qua, 'offset') !== false) {
            return $qua;
        }

        if (isset($_GET['offset'])) {
            $added = filter_input(INPUT_GET, 'offset', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $added = (int) $added;
            if ($added > 0) {
                $qua .= " OFFSET $added";
            }
        }
        return $qua;
    }
    public static function add_limit($qua)
    {
        // if $qua has LIMIT then return
        if (strpos($qua, 'LIMIT') !== false || strpos($qua, 'limit') !== false) {
            return $qua;
        }

        if (isset($_GET['limit'])) {
            $added = filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $added = (int) $added;
            if ($added > 0) {
                $qua .= " LIMIT $added";
            }
        }
        return $qua;
    }

    public static function sanitize_input($input, $pattern)
    {
        if (! empty($input) && preg_match($pattern, $input) && $input !== "all") {
            return filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        return null;
    }

    public static function filter_order($key, $endpoint_data, $get_value)
    {

        $endpoint_params  = $endpoint_data['params'] ?? [];
        $endpoint_columns = $endpoint_data['columns'] ?? [];

        if (! isset($_GET[$key])) {
            // error_log("No '$key' parameter defined in endpoint data");
            return null;
        }

        $added = $get_value;

        if (! $added) {
            // error_log("No '$key' parameter provided in the request");
            return null;
        }

        if (in_array($added, $endpoint_columns) || in_array($added, $endpoint_params)) {
            // error_log("Added '$added' is valid for '$key'");
            return $added;
        }

        // split $added or ,
        $added_array = explode(",", $added);

        foreach ($added_array as $k => $value) {
            $value = trim($value);
            // if its number okay
            if (
                ! in_array($value, $endpoint_columns) &&
                ! in_array($value, $endpoint_params) &&
                ! is_numeric($value)
            ) {
                error_log("order value '$value' is not valid for key '$key'");
                unset($added_array[$k]);
            }
        }

        if (count($added_array) > 0) {
            return implode(", ", $added_array);
        }

        return null;
    }

    public static function add_group($qua, $endpoint_data, $get_value)
    {

        $added = self::filter_order('group', $endpoint_data, $get_value);

        if ($added) {
            $qua .= " GROUP BY $added";
        }

        return $qua;
    }

    public static function get_order_direction($param_order_direction)
    {

        $order_direction = isset($_GET['order_direction']) ?
        filter_input(INPUT_GET, 'order_direction', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : ($param_order_direction["default"] ?? "");

        if (! $order_direction) {
            return "DESC";
        }

        $valid_orders = ["ASC", "DESC"];

        // $order_direction upper
        $order_direction = strtoupper($order_direction);

        if (! in_array($order_direction, $valid_orders)) {
            $order_direction = "DESC";
        }

        return $order_direction;
    }

    public static function add_order($qua, $endpoint_data, $get_value)
    {

        $endpoint_params = $endpoint_data['params'] ?? [];
        $order_values    = $endpoint_data['order_values'] ?? [];

        $params_key_to_data = array_column($endpoint_params, null, 'name');

        $param_order = $params_key_to_data["order"] ?? [];

        if (! $param_order) {
            // error_log("No 'order' parameter defined in endpoint data");
            return $qua;
        }

        $default_order = $param_order["default"] ?? "";

        if (empty($get_value) && empty($default_order)) {
            // error_log("No order required");
            return $qua;
        }

        $added = $default_order;

        if (! empty($get_value)) {
            $added_value = $order_values[$get_value] ?? "";
            // error_log("get_value: $get_value, added_value: $added_value");
            if (! empty($added_value)) {
                $added = $added_value;
            } else {
                $added = self::filter_order('order', $endpoint_data, $get_value) ?? $default_order;
            }
        }

        if (! $added) {
            return $qua;
        }

        $param_order_direction = $params_key_to_data["order_direction"] ?? [];
        $order_direction       = self::get_order_direction($param_order_direction);

        $qua .= " ORDER BY $added $order_direction";

        return $qua;
    }

}
