<?php
/**
 * The Accelerator tab under WooCommerce > Settings.
 *
 * @package BlueFairy\StoreAccelerator\Admin
 */

declare(strict_types=1);

namespace BlueFairy\StoreAccelerator\Admin;

use BlueFairy\StoreAccelerator\ConflictDetector;
use BlueFairy\StoreAccelerator\FeatureRegistry;

class SettingsPage
{
    public const TAB = 'bfsa';

    /**
     * The nonce WooCommerce prints in its own settings form.
     *
     * WC_Admin_Settings::save() checks this and manage_woocommerce before it
     * fires woocommerce_update_options_{tab}. We check both again rather than
     * annotating the omission away: a second check costs nothing, and the
     * handler is then correct on its own terms.
     */
    private const NONCE_ACTION = 'woocommerce-settings';

    public function init(): void
    {
        add_filter('woocommerce_settings_tabs_array', [$this, 'add_tab'], 50);
        add_action('woocommerce_settings_' . self::TAB, [$this, 'render']);
        add_action('woocommerce_update_options_' . self::TAB, [$this, 'save']);
        add_action('admin_notices', [$this, 'render_conflict_notice']);
    }

    /**
     * @param  array<string, string> $tabs
     * @return array<string, string>
     */
    public function add_tab(array $tabs): array
    {
        $tabs[self::TAB] = __('Accelerator', 'bluefairy-store-accelerator');

        return $tabs;
    }

    public function render(): void
    {
        $labels       = FeatureRegistry::labels();
        $descriptions = FeatureRegistry::descriptions();

        echo '<h2>' . esc_html__('Store Accelerator', 'bluefairy-store-accelerator') . '</h2>';
        echo '<p>' . esc_html__('Each switch controls one optimisation. All of them are safe to leave on; turn one off if it disagrees with your theme.', 'bluefairy-store-accelerator') . '</p>';
        echo '<table class="form-table" role="presentation"><tbody>';

        foreach ($labels as $key => $label) {
            printf(
                '<tr><th scope="row">%s</th><td><label><input type="checkbox" name="%s[%s]" value="1"%s> %s</label></td></tr>',
                esc_html($label),
                esc_attr(FeatureRegistry::OPTION),
                esc_attr($key),
                checked(FeatureRegistry::is_enabled($key), true, false),
                esc_html($descriptions[$key] ?? '')
            );
        }

        echo '</tbody></table>';

        /**
         * Extra settings fields, contributed by the Pro add-on.
         *
         * Anything echoed here must be inside the WooCommerce settings form and
         * must be saved from a bfsa_save_settings callback.
         */
        do_action('bfsa_register_settings');

        /**
         * Content below the form — the upsell panel, and anything the add-on
         * wants to show that is not a field.
         */
        do_action('bfsa_settings_page_footer');
    }

    public function save(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        check_admin_referer(self::NONCE_ACTION);

        $raw = isset($_POST[FeatureRegistry::OPTION]) && is_array($_POST[FeatureRegistry::OPTION])
            ? array_map('sanitize_text_field', wp_unslash($_POST[FeatureRegistry::OPTION]))
            : [];

        update_option(FeatureRegistry::OPTION, FeatureRegistry::sanitize($raw));

        /**
         * Save any fields the add-on contributed.
         *
         * The nonce and capability have both been checked by the time this
         * fires, so a listener does not have to repeat them.
         */
        do_action('bfsa_save_settings');
    }

    /**
     * Warn about an overlapping caching plugin — on our own screen only.
     *
     * A notice on every admin page for a condition the reader can do nothing
     * about from that page is just noise.
     */
    public function render_conflict_notice(): void
    {
        if (! $this->is_own_screen() || ! current_user_can('manage_woocommerce')) {
            return;
        }

        $found = ConflictDetector::found();

        if ([] === $found) {
            return;
        }

        printf(
            '<div class="notice notice-info"><p>%s</p></div>',
            esc_html(sprintf(
                /* translators: %s: comma-separated list of plugin names */
                __('Store Accelerator: %s is also active. If a switch below duplicates something that plugin already does, turn one of the two off — leaving both on means whichever loads first wins, which is not something either plugin can decide for you.', 'bluefairy-store-accelerator'),
                implode(', ', $found)
            ))
        );
    }

    private function is_own_screen(): bool
    {
        if (! function_exists('get_current_screen')) {
            return false;
        }

        $screen = get_current_screen();

        if (null === $screen || 'woocommerce_page_wc-settings' !== $screen->id) {
            return false;
        }

        // Reading a tab name to decide whether to draw a notice is not a state
        // change; there is nothing here for a nonce to protect.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return isset($_GET['tab']) && self::TAB === sanitize_key(wp_unslash($_GET['tab']));
    }
}
