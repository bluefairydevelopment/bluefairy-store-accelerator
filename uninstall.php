<?php
/**
 * Removes what this plugin stored.
 *
 * @package BlueFairy\StoreAccelerator
 */

declare(strict_types=1);

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Only our own keys, named one by one. A LIKE 'bfsa%' sweep would also take
// the Pro add-on's bfsap_ options with it.
delete_option('bfsa_features');

// The mini-cart scan is cached per theme, so the key is not knowable here;
// it expires on its own within a day and is harmless in the meantime.
