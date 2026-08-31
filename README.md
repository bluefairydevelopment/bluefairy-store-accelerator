# BlueFairy Store Accelerator

Removes WooCommerce scripts and stylesheets from pages that do not need them.

This is the free base plugin. [Store Accelerator Pro](https://wordpress.bluefairydevelopment.com/product/bluefairy-store-accelerator-pro/) is an add-on that extends it; it is not a separate plugin and holds no copy of this one's work.

## What it does

| Handle | Kept on |
|---|---|
| `wc-cart-fragments` | cart, checkout, any page showing a cart count |
| `select2`, `selectWoo` | shop archives, products, cart, checkout, My Account |
| `woocommerce-general`, `woocommerce-layout`, `woocommerce-smallscreen` | any page with WooCommerce content, including a page that only embeds a block or shortcode |

Handles beginning `wc-blocks-` are never touched.

## Architecture

```
bluefairy-store-accelerator.php    constants, autoloader, bfsa_plugin() marker
includes/Plugin.php                boot(); fires bfsa_loaded BEFORE registering anything
includes/FeatureRegistry.php       the switches, and the seam the add-on adds its own to
includes/PageContext.php           one resolver: blocks, shortcodes, conditionals, mini-cart
includes/Features/                 AssetOptimisation + the two subclasses
includes/Admin/                    the WooCommerce settings tab, the Pro panel
includes/ConflictDetector.php      overlapping plugins, scanned per request
```

`bfsa_loaded` fires before this plugin registers its own subsystems, so the add-on's feature labels and settings fields exist by the time the registry and the settings page read them.

## Filters

| Filter | Purpose |
|---|---|
| `bfsa_feature_labels` | add a switch to the settings page |
| `bfsa_feature_descriptions` | its one-line description |
| `bfsa_script_rules` | change which script handles are dropped, and when |
| `bfsa_style_rules` | the same for stylesheets |
| `bfsa_page_context` | change the resolved context flags |
| `bfsa_has_mini_cart` | tell the plugin a cart count is displayed |
| `bfsa_register_settings` | render extra fields inside the settings form |
| `bfsa_save_settings` | save them; nonce and capability already checked |
| `bfsa_settings_page_footer` | content below the form |

## Development

```
composer install
vendor/bin/phpunit
```

Fourteen unit tests covering the feature registry and the dequeue decision. They have been checked against deliberate mutations — removing the `wc-blocks-` protection, dropping the mini-cart context, and letting `sanitize()` trust the posted array all turn the suite red.

## Licence

GPL-2.0-or-later.
