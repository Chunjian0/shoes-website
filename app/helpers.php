<?php

if (!function_exists('format_money')) {
    function format_money($amount)
    {
        return number_format($amount, 2, '.', ',');
    }
}

if (!function_exists('format_date')) {
    function format_date($date)
    {
        return $date ? date('Y-m-d', strtotime($date)) : null;
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime($datetime)
    {
        return $datetime ? date('Y-m-d H:i:s', strtotime($datetime)) : null;
    }
}

if (!function_exists('get_status_badge')) {
    function get_status_badge($status)
    {
        $badges = [
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-red-100 text-red-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
        ];

        $classes = $badges[$status] ?? 'bg-gray-100 text-gray-800';
        return "<span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full {$classes}'>{$status}</span>";
    }
} 