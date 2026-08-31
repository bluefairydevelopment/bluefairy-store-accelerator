=== BlueFairy Store Accelerator ===
Contributors: bluefairydevelopment
Tags: woocommerce, performance, speed, scripts, optimization
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Removes WooCommerce scripts and stylesheets from the pages that do not need them, and leaves them alone on the pages that do.

== Description ==

WooCommerce loads its JavaScript and stylesheets on pages that have nothing to do with shopping. Every one of those is a request your visitor pays for on your About page.

This plugin removes them — carefully. The difficult part is not the removing; it is knowing when not to.

= What it removes, and when =

* **wc-cart-fragments** — the script that keeps a displayed cart count fresh without a page reload. Removed everywhere except the cart, the checkout, and any page where a cart count is actually shown.
* **select2 / selectWoo** — the dropdown libraries behind variation pickers and country fields. Removed on any page that is not a shop archive, a product, the cart, the checkout or My Account.
* **woocommerce-general, woocommerce-layout, woocommerce-smallscreen** — the WooCommerce stylesheets. Removed only on pages with no WooCommerce content at all.

= Pages that only look like they have no WooCommerce on them =

A landing page built around a product grid is not a WooCommerce page as far as `is_woocommerce()` is concerned, but its products still have to look like products. The context resolver treats a page carrying any of these as a WooCommerce page for stylesheet purposes:

`woocommerce/cart`, `woocommerce/checkout`, `woocommerce/mini-cart`, `woocommerce/single-product`, `woocommerce/all-products`, `woocommerce/product-collection`, `woocommerce/handpicked-products`, `woocommerce/product-best-sellers`, `woocommerce/product-category`, `woocommerce/product-new`, `woocommerce/product-on-sale`, `woocommerce/product-top-rated`, `woocommerce/product-tag`

WooCommerce blocks are also recognised as they render, so a front page whose product grid comes from the block template rather than the page's own content keeps its styling.

The mini-cart is the one block that does not count as WooCommerce content: it is styled separately, and in a block theme it appears in the site header on every page.

The same applies to the `[products]`, `[product_page]`, `[product_category]`, `[add_to_cart]`, `[woocommerce_cart]`, `[woocommerce_checkout]`, `[woocommerce_my_account]` and `[woocommerce_order_tracking]` shortcodes.

= About your cart count =

Removing `wc-cart-fragments` from a page that shows a live cart count freezes that count until the next full page load. This plugin looks for the WooCommerce mini-cart block, the mini-cart in a block theme's header template, and the classic cart widget.

It cannot see a theme that prints the count itself in its own header template — nothing in WordPress exposes that. If your count stops updating, either switch off **Script optimisation**, or tell the plugin the count is there:

`add_filter( 'bfsa_has_mini_cart', '__return_true' );`

= Changing the rules =

Both rule sets are filterable. Each key is a handle; each value lists the page contexts that require it.

`add_filter( 'bfsa_script_rules', function ( $rules ) { unset( $rules['select2'] ); return $rules; } );`

`bfsa_style_rules` works the same way for stylesheets, and `bfsa_page_context` lets you change the resolved context directly. Handles beginning `wc-blocks-` are never removed, whatever the rules say.

= Safety =

* Nothing runs in wp-admin.
* Removal happens at `wp_enqueue_scripts` priority 100, after WooCommerce enqueues at 10.
* No database tables, no scheduled work, one option.
* With WooCommerce inactive the plugin does nothing at all and says so.

== Store Accelerator Pro ==

The paid add-on installs alongside this plugin and adds its switches to the same settings tab:

* Blurred placeholders for product images, generated at upload and backfilled for the images you already have
* Reviews, related products and upsells loaded when the shopper scrolls to them
* Product listing queries cached, with pagination intact and invalidation on every route that changes a product
* One database index for product meta lookups, built in the background

https://wordpress.bluefairydevelopment.com/product/bluefairy-store-accelerator-pro/

== Installation ==

1. Upload the `bluefairy-store-accelerator` folder to `/wp-content/plugins/`, or install the zip through **Plugins → Add New → Upload Plugin**.
2. Activate it.
3. Both switches are on. Settings live under **WooCommerce → Settings → Accelerator**.

== Frequently Asked Questions ==

= Will this break my site? =

The two things most likely to surprise you are a frozen cart count and a theme that relies on `select2` outside WooCommerce pages. Both are one filter or one switch away from being fixed, and both are described above.

= Does it work with a block theme? =

Yes. Block and shortcode detection is how the context is resolved, and the mini-cart scan reads block theme header templates as well as the database.

= Does it work with a caching plugin? =

Yes. This plugin removes assets; a caching plugin stores pages. If one is active you will see a note on the settings tab pointing out where the two might overlap.

= Where are the statistics? =

In the Pro add-on. This plugin deliberately stores nothing beyond its two switches.

== Screenshots ==

1. WooCommerce → Settings → Accelerator — one switch per feature, both on by default.
2. The plugins list row, with the link to the Pro add-on.

== Changelog ==

= 1.1.0 =
* Rebuilt as the base plugin for Store Accelerator Pro, which is now an add-on rather than a separate plugin that repeated this one's work.
* Fixed: a page whose only WooCommerce content was a block or shortcode had all three WooCommerce stylesheets removed, so its products rendered unstyled.
* Fixed: WooCommerce blocks rendered by a block theme's templates were invisible to the context resolver, which read only the queried post's content — so a front page whose product grid came from the template lost all three stylesheets.
* Fixed: `wc-cart-fragments` was removed from every page outside the cart and checkout with no check for a displayed cart count, freezing the count on themes that show one.
* Added: `bfsa_script_rules`, `bfsa_style_rules`, `bfsa_page_context` and `bfsa_has_mini_cart` filters. 1.0.0 had none, so a site whose theme needed a handle could only deactivate the plugin.
* Added: a settings tab with a switch per feature.
* Added: stylesheet removal is now its own switch, separate from scripts.
* Changed: conflicting-plugin detection now runs per request instead of once at activation, so installing or removing a caching plugin afterwards is noticed.

= 1.0.0 =
* First release.
