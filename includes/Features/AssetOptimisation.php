<?php
/**
 * Shared machinery for the two dequeue features.
 *
 * @package BlueFairy\StoreAccelerator\Features
 */

declare(strict_types=1);

namespace BlueFairy\StoreAccelerator\Features;

use BlueFairy\StoreAccelerator\PageContext;

abstract class AssetOptimisation
{
    /**
     * Handles beginning with any of these are never touched.
     *
     * wc-blocks-* carries the block editor and the block-based cart and
     * checkout; removing one of those breaks the page rather than speeding it.
     */
    private const NEVER_TOUCH = ['wc-blocks-'];

    public function __construct(protected PageContext $context) {}

    /**
     * Rules for this feature: handle => the context flags that require it.
     *
     * @return array<string, string[]>
     */
    abstract protected function rules(): array;

    /**
     * The rules after the site has had its say.
     *
     * Each subclass applies its own filter with the name written out in full:
     * apply_filters() on a computed name is invisible to static analysis and to
     * anyone grepping for the hook.
     *
     * @return array<string, string[]>
     */
    abstract protected function filtered_rules(): array;

    abstract protected function dequeue_handle(string $handle): void;

    public function init(): void
    {
        // WooCommerce enqueues at 10; 100 leaves room for a theme in between.
        add_action('wp_enqueue_scripts', [$this, 'run'], 100);
    }

    public function run(): void
    {
        if (is_admin() || ! function_exists('is_woocommerce')) {
            return;
        }

        foreach ($this->filtered_rules() as $handle => $required) {
            $handle = (string) $handle;

            if ($this->is_protected($handle) || ! is_array($required) || [] === $required) {
                continue;
            }

            if (! $this->context->is_any($required)) {
                $this->dequeue_handle($handle);
            }
        }
    }

    private function is_protected(string $handle): bool
    {
        foreach (self::NEVER_TOUCH as $prefix) {
            if (str_starts_with($handle, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
