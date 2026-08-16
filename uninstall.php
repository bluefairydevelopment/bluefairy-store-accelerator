<?php
/**
 * Uninstall handler for BlueFairy Store Accelerator Lite.
 *
 * Runs when the plugin is deleted via the WordPress admin (Plugins > Delete).
 * This plugin stores no options, transients, or database tables, so there is
 * nothing to remove. The file exists to satisfy WordPress uninstall conventions.
 *
 * @package BlueFairy_Store_Accelerator_Lite
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// No options, transients, or tables to remove — this plugin is zero-config.
