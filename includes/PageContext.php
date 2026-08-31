<?php
/**
 * The single answer to "what kind of page is this?".
 *
 * @package BlueFairy\StoreAccelerator
 */

declare(strict_types=1);

namespace BlueFairy\StoreAccelerator;

use WP_Post;

class PageContext
{
    /**
     * Blocks that put WooCommerce content on a page WooCommerce does not own.
     *
     * A page carrying any of these needs WooCommerce's stylesheets even though
     * is_woocommerce() is false for it. 1.0.0 mapped the product-collection
     * block to the shop context only, while the stylesheet rules keyed off
     * wc_page — so a landing page built round that block rendered its product
     * grid completely unstyled.
     */
    private const WC_BLOCKS = [
        'woocommerce/cart',
        'woocommerce/checkout',
        'woocommerce/mini-cart',
        'woocommerce/single-product',
        'woocommerce/all-products',
        'woocommerce/product-collection',
        'woocommerce/handpicked-products',
        'woocommerce/product-best-sellers',
        'woocommerce/product-category',
        'woocommerce/product-new',
        'woocommerce/product-on-sale',
        'woocommerce/product-top-rated',
        'woocommerce/product-tag',
    ];

    /**
     * Shortcodes with the same implication as the blocks above.
     */
    private const WC_SHORTCODES = [
        'woocommerce_cart',
        'woocommerce_checkout',
        'woocommerce_my_account',
        'woocommerce_order_tracking',
        'products',
        'product_page',
        'product_category',
        'add_to_cart',
        'add_to_cart_url',
    ];

    /**
     * Blocks and shortcodes that also imply a more specific context.
     */
    private const BLOCK_FLAGS = [
        'woocommerce/cart'           => 'cart',
        'woocommerce_cart'           => 'cart',
        'woocommerce/checkout'       => 'checkout',
        'woocommerce_checkout'       => 'checkout',
        'woocommerce/mini-cart'      => 'mini_cart',
        'woocommerce/single-product' => 'product',
        'product_page'               => 'product',
        'woocommerce_my_account'     => 'account',
    ];

    /** @var string[] */
    private array $flags = [];

    private bool $resolved = false;

    /**
     * Resolve on the wp action, where the WooCommerce conditionals are reliable.
     */
    public function init(): void
    {
        add_action('wp', [$this, 'resolve']);

        /*
         * A block theme renders the template body before wp_head, so a
         * WooCommerce block that arrives from a template or a pattern — rather
         * than from the queried post's own content — is still noticed in time
         * for the dequeue at wp_enqueue_scripts priority 100. This is what
         * catches a front page whose product grid lives in the template: the
         * queried post's content is empty of WooCommerce, and scanning it alone
         * stripped the stylesheets from a page full of products.
         */
        add_filter('render_block', [$this, 'note_rendered_block'], 10, 2);
    }

    /**
     * Record a WooCommerce block as it renders.
     *
     * @param  array<string, mixed> $block
     */
    public function note_rendered_block(string $block_content, array $block): string
    {
        $name = (string) ($block['blockName'] ?? '');

        if ('' === $name || ! str_starts_with($name, 'woocommerce/')) {
            return $block_content;
        }

        /*
         * The mini-cart is styled by wc-blocks-style, not by the three
         * stylesheets this plugin removes, and in a block theme it sits in the
         * site header on every page. Letting it set wc_page would keep those
         * stylesheets everywhere and quietly turn the feature off for the whole
         * site. It sets mini_cart — which is what it actually tells us — and
         * nothing else.
         */
        if (str_starts_with($name, 'woocommerce/mini-cart')) {
            $this->add_flag('mini_cart');

            return $block_content;
        }

        if (in_array($name, self::WC_BLOCKS, true) || str_starts_with($name, 'woocommerce/product')) {
            $this->add_flag('wc_page');
        }

        if (isset(self::BLOCK_FLAGS[$name])) {
            $this->add_flag(self::BLOCK_FLAGS[$name]);
        }

        return $block_content;
    }

    private function add_flag(string $flag): void
    {
        if (! in_array($flag, $this->flags, true)) {
            $this->flags[] = $flag;
        }
    }

    /**
     * @return string[]
     */
    public function flags(): array
    {
        return $this->flags;
    }

    /**
     * Does the current page match any of these context flags?
     *
     * @param string[] $flags
     */
    public function is_any(array $flags): bool
    {
        return [] !== array_intersect($this->flags, $flags);
    }

    public function has(string $flag): bool
    {
        return in_array($flag, $this->flags, true);
    }

    /**
     * Work out the flags for this request.
     *
     * Every WooCommerce conditional used here is reached only after the
     * function_exists() guard on the first one — they all live in the same
     * WooCommerce file, so one existing means all of them do. The guard is
     * about WooCommerce being loaded at all, not about individual functions.
     */
    public function resolve(): void
    {
        if ($this->resolved) {
            return;
        }

        $this->resolved = true;

        if (! function_exists('is_woocommerce')) {
            return;
        }

        if (is_cart()) {
            $this->flags[] = 'cart';
        }

        if (is_checkout()) {
            $this->flags[] = 'checkout';
        }

        if (is_shop() || is_product_category() || is_product_tag()) {
            $this->flags[] = 'shop';
        }

        if (is_product()) {
            $this->flags[] = 'product';
        }

        if (is_account_page()) {
            $this->flags[] = 'account';
        }

        $this->resolve_embedded_content();

        if (is_woocommerce() || $this->is_any(['cart', 'checkout', 'account', 'shop', 'product'])) {
            $this->flags[] = 'wc_page';
        }

        if ($this->detect_mini_cart()) {
            $this->flags[] = 'mini_cart';
        }

        $this->flags = array_values(array_unique($this->flags));

        /**
         * Filter the resolved page context.
         *
         * @param string[]    $flags
         * @param PageContext $context
         */
        $this->flags = (array) apply_filters('bfsa_page_context', $this->flags, $this);
    }

    /**
     * Flag WooCommerce content embedded in an ordinary page.
     *
     * Sets both the specific flag and wc_page: the content needs the scripts
     * for its own kind AND the stylesheets that make it look like WooCommerce.
     */
    private function resolve_embedded_content(): void
    {
        $post = $this->queried_post();

        if (! $post instanceof WP_Post) {
            return;
        }

        $content = (string) $post->post_content;

        $found = [];

        foreach (self::WC_BLOCKS as $block) {
            if (has_block($block, $post)) {
                $found[] = $block;
            }
        }

        foreach (self::WC_SHORTCODES as $shortcode) {
            if (has_shortcode($content, $shortcode)) {
                $found[] = $shortcode;
            }
        }

        if ([] === $found) {
            return;
        }

        $this->flags[] = 'wc_page';

        foreach ($found as $item) {
            $this->flags[] = self::BLOCK_FLAGS[$item] ?? 'shop';
        }
    }

    /**
     * Is a live cart count rendered on this page?
     *
     * wc-cart-fragments is what keeps that count fresh without a page load, so
     * dequeuing it where a count is displayed freezes the number until the next
     * full request. We can see the mini-cart block and the classic cart widget.
     * We cannot see a theme that prints WC()->cart->get_cart_contents_count()
     * in its own header template — no hook exposes that. For those themes the
     * bfsa_has_mini_cart filter is the answer, and readme.txt says so.
     */
    private function detect_mini_cart(): bool
    {
        $detected = $this->has('mini_cart')
            || is_active_widget(false, false, 'woocommerce_widget_cart', true) !== false
            || self::block_theme_header_has_mini_cart();

        /**
         * Filter whether this page shows a live cart count.
         *
         * @param bool        $detected
         * @param PageContext $context
         */
        return (bool) apply_filters('bfsa_has_mini_cart', $detected, $this);
    }

    /**
     * Does any header template part of a block theme contain the mini-cart?
     *
     * get_block_templates() reads both the database and the theme's files, so
     * this covers a mini-cart the site owner placed in the site header — the
     * usual arrangement in a block theme, and invisible to has_block() on the
     * queried post. Cached because it walks every header part.
     */
    private static function block_theme_header_has_mini_cart(): bool
    {
        if (! function_exists('wp_is_block_theme') || ! wp_is_block_theme()) {
            return false;
        }

        $key    = 'bfsa_header_mini_cart_' . md5(get_stylesheet() . '|' . BFSA_VERSION);
        $cached = get_transient($key);

        if (false !== $cached) {
            return '1' === $cached;
        }

        $found = false;

        if (function_exists('get_block_templates')) {
            foreach (get_block_templates([], 'wp_template_part') as $part) {
                if (($part->area ?? '') !== 'header') {
                    continue;
                }

                if (str_contains((string) ($part->content ?? ''), 'wp:woocommerce/mini-cart')) {
                    $found = true;
                    break;
                }
            }
        }

        set_transient($key, $found ? '1' : '0', DAY_IN_SECONDS);

        return $found;
    }

    private function queried_post(): ?WP_Post
    {
        $queried = get_queried_object();

        return $queried instanceof WP_Post ? $queried : null;
    }
}
