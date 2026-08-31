<?php
/**
 * Removes WooCommerce JavaScript from pages that do not use it.
 *
 * @package BlueFairy\StoreAccelerator\Features
 */

declare(strict_types=1);

namespace BlueFairy\StoreAccelerator\Features;

class ScriptOptimisation extends AssetOptimisation
{
    /**
     * @return array<string, string[]>
     */
    protected function rules(): array
    {
        return [
            /*
             * wc-cart-fragments polls the server to keep a displayed cart count
             * fresh. mini_cart is in this list because dropping it where a count
             * is rendered freezes the number until the next full page load —
             * the most common complaint about WooCommerce script optimisation,
             * and something 1.0.0 did on every page of every site.
             */
            'wc-cart-fragments' => ['cart', 'checkout', 'mini_cart'],

            'select2'           => ['shop', 'product', 'cart', 'checkout', 'account'],
            'selectWoo'         => ['shop', 'product', 'cart', 'checkout', 'account'],
        ];
    }

    /**
     * Filter the script dequeue rules.
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
        return (array) apply_filters('bfsa_script_rules', $this->rules(), $this->context);
    }

    protected function dequeue_handle(string $handle): void
    {
        wp_dequeue_script($handle);
    }
}
