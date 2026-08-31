<?php
/**
 * Blocks that arrive from a template, not from the queried post's content.
 *
 * @package BlueFairy\StoreAccelerator
 */

declare(strict_types=1);

namespace BlueFairy\StoreAccelerator\Tests\Unit;

use BlueFairy\StoreAccelerator\PageContext;
use PHPUnit\Framework\TestCase;

final class PageContextBlockTest extends TestCase
{
    private function render(PageContext $context, string $block_name): void
    {
        $context->note_rendered_block('<div></div>', ['blockName' => $block_name]);
    }

    /**
     * A front page whose product grid lives in the block template has nothing
     * WooCommerce in the queried post's own content. Scanning only that content
     * stripped all three stylesheets from a page full of products.
     */
    public function test_a_product_block_from_a_template_marks_the_page(): void
    {
        $context = new PageContext();
        $this->render($context, 'woocommerce/product-collection');

        $this->assertTrue($context->has('wc_page'));
    }

    public function test_a_product_child_block_also_marks_the_page(): void
    {
        $context = new PageContext();
        $this->render($context, 'woocommerce/product-image');

        $this->assertTrue($context->has('wc_page'));
    }

    /**
     * The mini-cart sits in the site header of a block theme, so it renders on
     * every page. Letting it set wc_page would keep the stylesheets everywhere
     * and turn the whole feature off without saying so.
     */
    public function test_the_mini_cart_marks_only_the_cart_count(): void
    {
        $context = new PageContext();
        $this->render($context, 'woocommerce/mini-cart');

        $this->assertTrue($context->has('mini_cart'));
        $this->assertFalse($context->has('wc_page'));
    }

    public function test_a_mini_cart_child_block_behaves_the_same(): void
    {
        $context = new PageContext();
        $this->render($context, 'woocommerce/mini-cart-items-block');

        $this->assertFalse($context->has('wc_page'));
    }

    public function test_a_non_woocommerce_block_marks_nothing(): void
    {
        $context = new PageContext();
        $this->render($context, 'core/paragraph');

        $this->assertSame([], $context->flags());
    }

    public function test_the_checkout_block_marks_the_checkout(): void
    {
        $context = new PageContext();
        $this->render($context, 'woocommerce/checkout');

        $this->assertTrue($context->has('checkout'));
        $this->assertTrue($context->has('wc_page'));
    }

    public function test_the_same_block_twice_does_not_duplicate_a_flag(): void
    {
        $context = new PageContext();
        $this->render($context, 'woocommerce/product-collection');
        $this->render($context, 'woocommerce/product-collection');

        $this->assertSame(['wc_page'], $context->flags());
    }
}
