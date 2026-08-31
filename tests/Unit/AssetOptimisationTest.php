<?php
/**
 * The dequeue decision, isolated from WordPress.
 *
 * @package BlueFairy\StoreAccelerator
 */

declare(strict_types=1);

namespace BlueFairy\StoreAccelerator\Tests\Unit;

use BlueFairy\StoreAccelerator\Features\ScriptOptimisation;
use BlueFairy\StoreAccelerator\Features\StyleOptimisation;
use BlueFairy\StoreAccelerator\PageContext;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class AssetOptimisationTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['bfsa_test_filters'] = [];
        $GLOBALS['bfsa_dequeued']     = ['scripts' => [], 'styles' => []];
    }

    /**
     * @param string[] $flags
     */
    private function context(array $flags): PageContext
    {
        $context = new PageContext();

        $property = new ReflectionProperty(PageContext::class, 'flags');
        $property->setAccessible(true);
        $property->setValue($context, $flags);

        $resolved = new ReflectionProperty(PageContext::class, 'resolved');
        $resolved->setAccessible(true);
        $resolved->setValue($context, true);

        return $context;
    }

    /**
     * @return string[]
     */
    private function dequeuedScripts(array $flags): array
    {
        (new ScriptOptimisation($this->context($flags)))->run();

        return $GLOBALS['bfsa_dequeued']['scripts'];
    }

    /**
     * @return string[]
     */
    private function dequeuedStyles(array $flags): array
    {
        (new StyleOptimisation($this->context($flags)))->run();

        return $GLOBALS['bfsa_dequeued']['styles'];
    }

    public function test_cart_fragments_goes_on_an_ordinary_page(): void
    {
        $this->assertContains('wc-cart-fragments', $this->dequeuedScripts([]));
    }

    public function test_cart_fragments_stays_on_the_cart(): void
    {
        $this->assertNotContains('wc-cart-fragments', $this->dequeuedScripts(['cart', 'wc_page']));
    }

    /**
     * The handle keeps a displayed cart count fresh. 1.0.0 removed it from
     * every page outside cart and checkout with no check for a mini-cart, so a
     * header count froze site-wide.
     */
    public function test_cart_fragments_stays_where_a_mini_cart_is_rendered(): void
    {
        $this->assertNotContains('wc-cart-fragments', $this->dequeuedScripts(['mini_cart']));
    }

    /**
     * A page carrying a WooCommerce block gets wc_page as well as its specific
     * flag. Without that the product grid on a landing page rendered unstyled.
     */
    public function test_woocommerce_styles_stay_on_a_page_with_embedded_products(): void
    {
        $this->assertSame([], $this->dequeuedStyles(['shop', 'wc_page']));
    }

    public function test_woocommerce_styles_go_from_a_page_with_no_woocommerce_content(): void
    {
        $this->assertSame(
            ['woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen'],
            $this->dequeuedStyles([])
        );
    }

    public function test_a_site_can_keep_a_handle_through_the_filter(): void
    {
        $GLOBALS['bfsa_test_filters']['bfsa_script_rules'] = static function (array $rules): array {
            unset($rules['wc-cart-fragments']);

            return $rules;
        };

        $this->assertNotContains('wc-cart-fragments', $this->dequeuedScripts([]));
    }

    /**
     * Block handles carry the block cart and checkout; removing one breaks the
     * page rather than speeding it.
     */
    public function test_a_wc_blocks_handle_is_never_touched(): void
    {
        $GLOBALS['bfsa_test_filters']['bfsa_script_rules'] = static fn(array $r): array
            => $r + ['wc-blocks-checkout' => ['checkout']];

        $this->assertNotContains('wc-blocks-checkout', $this->dequeuedScripts([]));
    }
}
