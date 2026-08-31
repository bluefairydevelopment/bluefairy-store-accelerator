<?php
/**
 * The one link to the paid add-on.
 *
 * @package BlueFairy\StoreAccelerator\Admin
 */

declare(strict_types=1);

namespace BlueFairy\StoreAccelerator\Admin;

class ProUpsell
{
    private const PRODUCT_URL = 'https://wordpress.bluefairydevelopment.com/product/bluefairy-store-accelerator-pro/';

    public function init(): void
    {
        add_filter('plugin_action_links_' . plugin_basename(BFSA_PLUGIN_FILE), [$this, 'add_links']);
        add_action('bfsa_settings_page_footer', [$this, 'render_panel']);
    }

    /**
     * @param  string[] $links
     * @return string[]
     */
    public function add_links(array $links): array
    {
        if ($this->pro_is_active()) {
            return $links;
        }

        $links[] = sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
            esc_url(self::PRODUCT_URL),
            esc_html__('Get Store Accelerator Pro', 'bluefairy-store-accelerator')
        );

        return $links;
    }

    /**
     * A panel under the settings, listing what the add-on adds.
     *
     * Every line here is a feature the add-on ships today. The previous version
     * of this list promised a stats dashboard and session optimisation; one was
     * a counter with no display and the other was an admin notice.
     */
    public function render_panel(): void
    {
        if ($this->pro_is_active()) {
            return;
        }

        $features = [
            __('Blur-up placeholders for product images, generated at upload and backfilled for images you already have', 'bluefairy-store-accelerator'),
            __('Reviews, related products and upsells loaded only when the shopper scrolls to them', 'bluefairy-store-accelerator'),
            __('Product listing queries cached, with pagination intact and invalidation on every route that changes a product', 'bluefairy-store-accelerator'),
        ];

        echo '<hr><h2>' . esc_html__('Store Accelerator Pro', 'bluefairy-store-accelerator') . '</h2>';
        echo '<p>' . esc_html__('The add-on installs alongside this plugin and adds its switches to the list above.', 'bluefairy-store-accelerator') . '</p>';
        echo '<ul style="list-style:disc;margin-left:1.5em;">';

        foreach ($features as $feature) {
            echo '<li>' . esc_html($feature) . '</li>';
        }

        echo '</ul>';
        printf(
            '<p><a class="button button-primary" href="%s" target="_blank" rel="noopener noreferrer">%s</a></p>',
            esc_url(self::PRODUCT_URL),
            esc_html__('Get Store Accelerator Pro', 'bluefairy-store-accelerator')
        );
    }

    private function pro_is_active(): bool
    {
        return function_exists('bfsap_plugin');
    }
}
