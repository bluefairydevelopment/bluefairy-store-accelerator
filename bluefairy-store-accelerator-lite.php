<?php
/**
 * Plugin Name: BlueFairy Store Accelerator Lite for WooCommerce
 * Plugin URI: https://wordpress.bluefairydevelopment.com/product/store-accelerator-for-woocommerce/
 * Description: Zero-config WooCommerce performance: removes unused WooCommerce scripts on pages that don't need them.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: Blue Fairy Development
 * Author URI: https://wordpress.bluefairydevelopment.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bluefairy-store-accelerator-lite
 * WC requires at least: 8.0
 * WC tested up to: 9.9
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('BFSAL_VERSION', '1.0.0');
define('BFSAL_FILE', __FILE__);
define('BFSAL_DIR', plugin_dir_path(__FILE__));
define('BFSAL_URL', plugin_dir_url(__FILE__));

require_once BFSAL_DIR . 'includes/class-script-dequeue.php';
require_once BFSAL_DIR . 'includes/class-plugin.php';

add_action('plugins_loaded', ['BFSAL_Plugin', 'boot'], 20);
