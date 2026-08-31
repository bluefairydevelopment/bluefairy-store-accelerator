<?php
/**
 * Just enough WordPress for the logic under test.
 */

declare(strict_types=1);

$GLOBALS['bfsa_test_options'] = [];
$GLOBALS['bfsa_test_filters']  = [];
$GLOBALS['bfsa_dequeued']      = ['scripts' => [], 'styles' => []];

if (! function_exists('__')) {
    function __($text, $domain = null)
    {
        return $text;
    }
}

if (! function_exists('get_option')) {
    function get_option($name, $default = false)
    {
        return $GLOBALS['bfsa_test_options'][$name] ?? $default;
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters($tag, $value, ...$args)
    {
        $cb = $GLOBALS['bfsa_test_filters'][$tag] ?? null;

        return $cb ? $cb($value, ...$args) : $value;
    }
}

if (! function_exists('add_action')) {
    function add_action($tag, $callback, $priority = 10, $args = 1): bool
    {
        return true;
    }
}

if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        return false;
    }
}

if (! function_exists('is_woocommerce')) {
    function is_woocommerce(): bool
    {
        return true;
    }
}

if (! function_exists('wp_dequeue_script')) {
    function wp_dequeue_script($handle): void
    {
        $GLOBALS['bfsa_dequeued']['scripts'][] = $handle;
    }
}

if (! function_exists('wp_dequeue_style')) {
    function wp_dequeue_style($handle): void
    {
        $GLOBALS['bfsa_dequeued']['styles'][] = $handle;
    }
}
