<?php

if (!function_exists('format_category_key')) {
    function format_category_key($categoryName)
    {
        // Replace spaces and special characters with underscores, then remove duplicates
        $key = strtolower($categoryName);
        $key = preg_replace('/[^a-z0-9_]/', '_', $key);
        $key = preg_replace('/_+/', '_', $key);
        return trim($key, '_');
    }
}
