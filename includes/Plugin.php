<?php
/**
 * Base plugin bootstrap.
 *
 * @package BlueFairy\StoreAccelerator
 */

declare(strict_types=1);

namespace BlueFairy\StoreAccelerator;

use BlueFairy\StoreAccelerator\Admin\ProUpsell;
use BlueFairy\StoreAccelerator\Admin\SettingsPage;
use BlueFairy\StoreAccelerator\Features\ScriptOptimisation;
use BlueFairy\StoreAccelerator\Features\StyleOptimisation;

class Plugin
{
    /**
     * Boot the plugin.
     *
     * The bfsa_loaded action fires BEFORE any of our own subsystems are
     * registered. The Pro add-on hooks it to contribute feature labels and
     * settings fields, and those have to be in place before the settings page
     * or the feature registry read them.
     */
    public static function boot(): void
    {
        if (! class_exists('WooCommerce')) {
            add_action('admin_notices', [self::class, 'render_woocommerce_notice']);

            return;
        }

        ConflictDetector::scan();

        do_action('bfsa_loaded');

        self::register_features();

        (new SettingsPage())->init();
        (new ProUpsell())->init();
    }

    /**
     * Register the front-end features that are switched on.
     */
    private static function register_features(): void
    {
        if (is_admin()) {
            return;
        }

        $context = new PageContext();
        $context->init();

        if (FeatureRegistry::is_enabled('scripts')) {
            (new ScriptOptimisation($context))->init();
        }

        if (FeatureRegistry::is_enabled('styles')) {
            (new StyleOptimisation($context))->init();
        }
    }

    /**
     * Tell an administrator why nothing is happening.
     */
    public static function render_woocommerce_notice(): void
    {
        if (! current_user_can('activate_plugins')) {
            return;
        }

        printf(
            '<div class="notice notice-warning"><p>%s</p></div>',
            esc_html__(
                'BlueFairy Store Accelerator needs WooCommerce to be installed and active. Nothing is being changed until it is.',
                'bluefairy-store-accelerator'
            )
        );
    }
}
