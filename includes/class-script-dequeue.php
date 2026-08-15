<?php
/**
 * Page-type-aware WooCommerce script/style dequeue.
 *
 * @package BlueFairy\StoreAcceleratorLite
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Removes unused WooCommerce JS and CSS handles based on the current page context.
 *
 * Safety rules enforced here:
 *  - All WC conditionals are guarded with function_exists() checks.
 *  - wc-blocks-* handles are never touched.
 *  - Dequeue runs at wp_enqueue_scripts priority 100 (after WC enqueues at 10).
 *  - Never runs in wp-admin (enforced by caller; double-checked here).
 */
class BFSAL_Script_Dequeue
{
    /**
     * Page-context flags detected for the current request.
     *
     * Possible values: 'cart', 'checkout', 'shop', 'product', 'account', 'wc_page'.
     *
     * @var string[]
     */
    private array $context = [];

    /** Whether context detection has already run. */
    private bool $context_detected = false;

    /**
     * Register hooks.
     *
     * Called once from BFSAL_Plugin::boot() on the frontend.
     */
    public function init(): void
    {
        // Detect page context after WP query is set up.
        add_action('wp', [$this, 'detect_context']);

        // Dequeue at priority 100 — WooCommerce enqueues at 10.
        add_action('wp_enqueue_scripts', [$this, 'dequeue'], 100);
    }

    // -------------------------------------------------------------------------
    // Context detection
    // -------------------------------------------------------------------------

    /**
     * Determine which WooCommerce page types are active for this request.
     *
     * Runs on the 'wp' action so all WC conditionals are reliable.
     * Guards every WC function with function_exists() before calling it.
     */
    public function detect_context(): void
    {
        if ($this->context_detected) {
            return;
        }
        $this->context_detected = true;

        if (!function_exists('is_woocommerce')) {
            return;
        }

        // -----------------------------------------------------------
        // Cart — includes pages with the WooCommerce cart block.
        // -----------------------------------------------------------
        if (function_exists('is_cart') && is_cart()) {
            $this->context[] = 'cart';
        } elseif ($this->current_post_has_block('woocommerce/cart')) {
            $this->context[] = 'cart';
        }

        // -----------------------------------------------------------
        // Checkout — includes pages with the WooCommerce checkout block.
        // -----------------------------------------------------------
        if (function_exists('is_checkout') && is_checkout()) {
            $this->context[] = 'checkout';
        } elseif ($this->current_post_has_block('woocommerce/checkout')) {
            $this->context[] = 'checkout';
        }

        // -----------------------------------------------------------
        // Shop archive / category / tag / product collection block.
        // -----------------------------------------------------------
        if (
            (function_exists('is_shop') && is_shop())
            || (function_exists('is_product_category') && is_product_category())
            || (function_exists('is_product_tag') && is_product_tag())
        ) {
            $this->context[] = 'shop';
        } elseif ($this->current_post_has_block('woocommerce/product-collection')) {
            $this->context[] = 'shop';
        }

        // -----------------------------------------------------------
        // Single product page.
        // -----------------------------------------------------------
        if (function_exists('is_product') && is_product()) {
            $this->context[] = 'product';
        }

        // -----------------------------------------------------------
        // My Account.
        // -----------------------------------------------------------
        if (function_exists('is_account_page') && is_account_page()) {
            $this->context[] = 'account';
        }

        // -----------------------------------------------------------
        // Generic "any WC page" flag — covers any page is_woocommerce()
        // returns true for (shop, product, cart, checkout, etc.).
        // -----------------------------------------------------------
        if (is_woocommerce() || is_cart() || is_checkout() || is_account_page()) {
            $this->context[] = 'wc_page';
        }

        // Deduplicate in case multiple conditions added the same flag.
        $this->context = array_unique($this->context);
    }

    // -------------------------------------------------------------------------
    // Dequeue logic
    // -------------------------------------------------------------------------

    /**
     * Remove WooCommerce handles that are not required on the current page.
     *
     * Never dequeues wc-blocks-* handles.
     * Each rule lists the context flags under which the handle IS needed.
     * If the current page has none of those flags, the handle is dequeued.
     */
    public function dequeue(): void
    {
        // Safety: never run in admin (caller checks this, but guard here too).
        if (is_admin()) {
            return;
        }

        if (!function_exists('is_woocommerce')) {
            return;
        }

        // ------------------------------------------------------------------
        // Script rules
        // Each entry: handle => [contexts where the script IS needed]
        // ------------------------------------------------------------------
        $script_rules = [
            /*
             * wc-cart-fragments: sends a heartbeat to keep cart count live.
             * Only needed on cart, checkout, or pages with a mini-cart / cart block.
             * "cart" context covers both is_cart() and has_block('woocommerce/cart').
             */
            'wc-cart-fragments' => ['cart', 'checkout'],

            /*
             * select2 / selectWoo: used on shop archives (variations filter),
             * product pages, cart, and checkout — but NOT on plain content pages.
             */
            'select2'    => ['shop', 'product', 'cart', 'checkout', 'account'],
            'selectWoo'  => ['shop', 'product', 'cart', 'checkout', 'account'],
        ];

        // ------------------------------------------------------------------
        // Style rules
        // Each entry: handle => [contexts where the style IS needed]
        // ------------------------------------------------------------------
        $style_rules = [
            /*
             * woocommerce (main WC CSS): only needed on actual WC pages.
             */
            'woocommerce-general'     => ['wc_page'],
            'woocommerce-layout'      => ['wc_page'],
            'woocommerce-smallscreen' => ['wc_page'],
        ];

        foreach ($script_rules as $handle => $needed_contexts) {
            $this->maybe_dequeue_script($handle, $needed_contexts);
        }

        foreach ($style_rules as $handle => $needed_contexts) {
            $this->maybe_dequeue_style($handle, $needed_contexts);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Dequeue a script handle if the current page context does not require it.
     *
     * Handles prefixed with 'wc-blocks-' are never dequeued.
     *
     * @param string   $handle          The script handle to evaluate.
     * @param string[] $needed_contexts Context flags under which the script is needed.
     */
    private function maybe_dequeue_script(string $handle, array $needed_contexts): void
    {
        // Hard rule: never touch block editor handles.
        if (str_starts_with($handle, 'wc-blocks-')) {
            return;
        }

        if (!$this->context_needs($needed_contexts)) {
            wp_dequeue_script($handle);
        }
    }

    /**
     * Dequeue a style handle if the current page context does not require it.
     *
     * Handles prefixed with 'wc-blocks-' are never dequeued.
     *
     * @param string   $handle          The style handle to evaluate.
     * @param string[] $needed_contexts Context flags under which the style is needed.
     */
    private function maybe_dequeue_style(string $handle, array $needed_contexts): void
    {
        // Hard rule: never touch block editor handles.
        if (str_starts_with($handle, 'wc-blocks-')) {
            return;
        }

        if (!$this->context_needs($needed_contexts)) {
            wp_dequeue_style($handle);
        }
    }

    /**
     * Return true if the current page context intersects with any of the given flags.
     *
     * @param string[] $needed_contexts Flags that indicate the asset is needed.
     */
    private function context_needs(array $needed_contexts): bool
    {
        return !empty(array_intersect($this->context, $needed_contexts));
    }

    /**
     * Check whether the queried object's post content contains a given block.
     *
     * Uses a static cache per block type to avoid repeated parse passes.
     * Falls back gracefully if the queried object is not a post.
     *
     * @param string $block_name Fully-qualified block name, e.g. 'woocommerce/cart'.
     */
    private function current_post_has_block(string $block_name): bool
    {
        static $cache = [];

        if (array_key_exists($block_name, $cache)) {
            return $cache[$block_name];
        }

        if (!function_exists('has_block')) {
            $cache[$block_name] = false;
            return false;
        }

        $queried = get_queried_object();
        if (!($queried instanceof WP_Post)) {
            $cache[$block_name] = false;
            return false;
        }

        $cache[$block_name] = has_block($block_name, $queried);

        return $cache[$block_name];
    }
}
