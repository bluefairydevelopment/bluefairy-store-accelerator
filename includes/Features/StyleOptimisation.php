<?php
/**
 * Removes WooCommerce stylesheets from pages with no WooCommerce content.
 *
 * @package BlueFairy\StoreAccelerator\Features
 */

declare(strict_types=1);

namespace BlueFairy\StoreAccelerator\Features;

class StyleOptimisation extends AssetOptimisation
{
    /**
     * All three key off wc_page, which PageContext also sets for a page that
     * merely embeds a WooCommerce block or shortcode. That is the whole point:
     * such a page is not a WooCommerce page by is_woocommerce(), but its
     * products still have to look like products.
     *
     * @return array<string, string[]>
     */
    protected function rules(): array
    {
        return [
            'woocommerce-general'     => ['wc_page'],
            'woocommerce-layout'      => ['wc_page'],
            'woocommerce-smallscreen' => ['wc_page'],
        ];
    }

    /**
     * Filter the stylesheet dequeue rules.
     *
     * Each key is a handle; each value lists the page contexts that require it.
     * Remove a key to stop that handle being touched, or add your own to have
     * it dropped the same way.
     *
     * @return array<string, string[]>
     */
    protected function filtered_rules(): array
    {
        /** This filter is documented above. */
        return (array) apply_filters('bfsa_style_rules', $this->rules(), $this->context);
    }

    protected function dequeue_handle(string $handle): void
    {
        wp_dequeue_style($handle);
    }
}
