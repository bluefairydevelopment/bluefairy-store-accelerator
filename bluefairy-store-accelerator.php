<?php
/**
 * Plugin Name: BlueFairy Store Accelerator
 * Plugin URI: https://wordpress.bluefairydevelopment.com/bluefairy-store-accelerator/
 * Description: Removes WooCommerce scripts and stylesheets from the pages that do not need them, using real page-context detection.
 * Version: 1.1.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: Blue Fairy Development
 * Author URI: https://wordpress.bluefairydevelopment.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bluefairy-store-accelerator
 * WC requires at least: 8.0
 * WC tested up to: 11.0
 *
 * @package BlueFairy\StoreAccelerator
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('BFSA_VERSION', '1.1.0');
define('BFSA_PLUGIN_FILE', __FILE__);
define('BFSA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BFSA_PLUGIN_URL', plugin_dir_url(__FILE__));

add_action('before_woocommerce_init', static function (): void {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

spl_autoload_register(static function (string $class): void {
    $prefix = 'BlueFairy\\StoreAccelerator\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file     = BFSA_PLUGIN_DIR . 'includes/' . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

/**
 * Marker used by the Pro add-on to confirm the base plugin is present.
 *
 * The add-on cannot rely on class_exists() alone: its own autoloader would not
 * find our classes, and a fatal from a half-loaded base is worse than a notice.
 */
function bfsa_plugin(): string
{
    return BFSA_VERSION;
}

add_action('plugins_loaded', [\BlueFairy\StoreAccelerator\Plugin::class, 'boot'], 20);
