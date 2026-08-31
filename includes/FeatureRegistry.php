<?php
/**
 * The feature switches, and the seam the Pro add-on adds its own to.
 *
 * @package BlueFairy\StoreAccelerator
 */

declare(strict_types=1);

namespace BlueFairy\StoreAccelerator;

class FeatureRegistry
{
    public const OPTION = 'bfsa_features';

    /**
     * Human labels, keyed by feature slug.
     *
     * The Pro add-on appends its own through bfsa_feature_labels. The label
     * list is the authority on which keys exist: sanitize() rebuilds the stored
     * array from it, so a key with no label cannot be stored and a key with a
     * label cannot be silently dropped.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return (array) apply_filters('bfsa_feature_labels', [
            'scripts' => __('Script optimisation', 'bluefairy-store-accelerator'),
            'styles'  => __('Stylesheet optimisation', 'bluefairy-store-accelerator'),
        ]);
    }

    /**
     * One sentence per feature, for the settings page.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return (array) apply_filters('bfsa_feature_descriptions', [
            'scripts' => __('Remove WooCommerce JavaScript from pages that do not use it.', 'bluefairy-store-accelerator'),
            'styles'  => __('Remove WooCommerce stylesheets from pages with no WooCommerce content.', 'bluefairy-store-accelerator'),
        ]);
    }

    /**
     * Is a feature switched on?
     *
     * An unknown key counts as enabled. A feature added by an update, or by the
     * Pro add-on after the option was last written, has no stored value; the
     * alternative default would silently switch it off.
     */
    public static function is_enabled(string $key): bool
    {
        $stored = (array) get_option(self::OPTION, []);

        if (! array_key_exists($key, $stored)) {
            return true;
        }

        return (bool) $stored[$key];
    }

    /**
     * Rebuild the stored array from the label list.
     *
     * An unchecked checkbox posts nothing, so absence means off — but only for
     * keys we know about. Anything else in the input is discarded.
     *
     * @param  mixed $input
     * @return array<string, bool>
     */
    public static function sanitize(mixed $input): array
    {
        $input  = is_array($input) ? $input : [];
        $result = [];

        foreach (array_keys(self::labels()) as $key) {
            $result[$key] = ! empty($input[$key]);
        }

        return $result;
    }
}
