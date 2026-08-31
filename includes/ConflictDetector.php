<?php
/**
 * Notices a caching plugin that overlaps with what we do.
 *
 * @package BlueFairy\StoreAccelerator
 */

declare(strict_types=1);

namespace BlueFairy\StoreAccelerator;

class ConflictDetector
{
    /**
     * Plugin file => display name.
     */
    private const CANDIDATES = [
        'wp-rocket/wp-rocket.php'             => 'WP Rocket',
        'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
        'w3-total-cache/w3-total-cache.php'   => 'W3 Total Cache',
        'wp-super-cache/wp-cache.php'         => 'WP Super Cache',
        'autoptimize/autoptimize.php'         => 'Autoptimize',
        'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',

        /*
         * Our own. Store Accelerator Pro and Performance Kit Pro both filter
         * woocommerce_product_get_image at priority 10 to install an image
         * placeholder; whichever registers first wins and the other rewrites
         * its output. A customer who owns both should be told, not left to
         * work out why the blur-up looks wrong.
         */
        'bluefairy-performance-kit-pro/bluefairy-performance-kit-pro.php' => 'BlueFairy Performance Kit Pro',
    ];

    /** @var string[]|null */
    private static ?array $found = null;

    /**
     * Scan the active plugins for this request.
     *
     * 1.0.0 computed this once, at activation, and stored the result. Install a
     * caching plugin afterwards and no notice ever appeared; remove one and the
     * notice stayed for ever. Reading the active list is one already-autoloaded
     * option, so there is nothing to store.
     */
    public static function scan(): void
    {
        $active = (array) get_option('active_plugins', []);

        if (is_multisite()) {
            $active = array_merge($active, array_keys((array) get_site_option('active_sitewide_plugins', [])));
        }

        $found = [];

        foreach (self::CANDIDATES as $file => $name) {
            if (in_array($file, $active, true)) {
                $found[] = $name;
            }
        }

        self::$found = $found;
    }

    /**
     * @return string[] Display names, never plugin paths.
     */
    public static function found(): array
    {
        if (null === self::$found) {
            self::scan();
        }

        return self::$found ?? [];
    }
}
