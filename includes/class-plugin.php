<?php
/**
 * Bootstrap class — registers all plugin hooks.
 *
 * @package BlueFairy\StoreAcceleratorLite
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin bootstrap.
 *
 * Checks WooCommerce is active, wires up the script dequeue module,
 * and adds the "Upgrade to Pro" action link.
 */
class BFSAL_Plugin
{
    /**
     * Boot the plugin.
     *
     * Called at priority 20 on plugins_loaded so WooCommerce has already
     * registered itself.
     */
    public static function boot(): void
    {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [static::class, 'notice_wc_missing']);
            return;
        }

        // Wire up script dequeuing on the frontend only.
        if (!is_admin()) {
            $dequeue = new BFSAL_Script_Dequeue();
            $dequeue->init();
        }

        // Upgrade to Pro action link — safe to add from admin or front.
        add_filter(
            'plugin_action_links_' . plugin_basename(BFSAL_FILE),
            [static::class, 'add_action_links']
        );
    }

    /**
     * Add "Upgrade to Pro →" to the plugin action links row.
     *
     * @param  string[] $links Existing action links.
     * @return string[]
     */
    public static function add_action_links(array $links): array
    {
        $pro_link = sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" style="font-weight:600;">%s</a>',
            esc_url('https://wordpress.bluefairydevelopment.com/product/store-accelerator-for-woocommerce/'),
            esc_html__('Upgrade to Pro &rarr;', 'bluefairy-store-accelerator-lite')
        );

        // Prepend so the link appears first.
        array_unshift($links, $pro_link);

        return $links;
    }

    /**
     * Admin notice shown when WooCommerce is not active.
     *
     * Only shown to users with manage_options capability.
     */
    public static function notice_wc_missing(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        printf(
            '<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
            wp_kses(
                sprintf(
                    /* translators: %s: WooCommerce plugin name */
                    __('<strong>Store Accelerator Lite for WooCommerce</strong> requires %s to be installed and active.', 'bluefairy-store-accelerator-lite'),
                    '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank" rel="noopener noreferrer">WooCommerce</a>'
                ),
                ['strong' => [], 'a' => ['href' => [], 'target' => [], 'rel' => []]]
            )
        );
    }
}
