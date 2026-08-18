=== BlueFairy Store Accelerator Lite for WooCommerce ===
Contributors: bluefairydevelopment
Tags: woocommerce, performance, speed, optimization, scripts
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
WC requires at least: 8.0
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Remove unused WooCommerce scripts and styles from pages that don't need them — zero config, instant speed gains.

== Description ==

WooCommerce loads a number of JavaScript and CSS files on every page of your site by default — even pages that have nothing to do with shopping. Each unnecessary file is a DNS lookup, connection, download, and parse cycle your visitors pay for.

Store Accelerator Lite for WooCommerce removes the worst offenders automatically, using smart page-type detection rather than a simple "is this a WooCommerce page?" check.

**What gets removed and when:**

* **wc-cart-fragments** — The heartbeat script that polls the server to keep your mini-cart count fresh. Removed on every page *except* cart, checkout, and any page containing a WooCommerce cart block. Those are the only places it matters.
* **select2 / selectWoo** — Dropdown enhancement libraries used for variation selectors and country pickers. Removed on any page that is not a shop archive, single product, cart, checkout, or account page.
* **WooCommerce layout stylesheets** (`woocommerce-general`, `woocommerce-layout`, `woocommerce-smallscreen`) — Removed on pages that are not WooCommerce pages at all (blog posts, contact page, etc.).

**Block theme aware:** The plugin detects `woocommerce/cart`, `woocommerce/checkout`, and `woocommerce/product-collection` blocks in page content and keeps the required scripts active when those blocks are present.

**Safe by design:**

* Never touches any `wc-blocks-*` handle — block editor scripts are completely off-limits.
* Dequeues at priority 100, after WooCommerce enqueues at priority 10.
* Guards every WooCommerce conditional with `function_exists()` — the plugin will not fatal if WooCommerce is deactivated.
* No database reads or writes. No settings page. Zero configuration.

**Upgrade to [Store Accelerator Pro](https://wordpress.bluefairydevelopment.com/product/store-accelerator-for-woocommerce/) for:**

* LQIP (Low Quality Image Placeholder) lazy loading for product images
* AJAX lazy-loaded content sections
* WooCommerce query caching
* Database index tuning for large catalogs
* Stats dashboard showing exactly what was optimised on each page load

== Installation ==

1. Upload the `bluefairy-store-accelerator-lite` folder to `/wp-content/plugins/`.
2. Activate the plugin from **Plugins > Installed Plugins**.
3. That's it — no configuration required.

WooCommerce must be installed and active. If it is not, you will see an admin notice as a reminder.

== Frequently Asked Questions ==

= Does this work without WooCommerce? =

No. Store Accelerator Lite for WooCommerce is specifically built to optimise WooCommerce asset loading. If WooCommerce is not active, the plugin will display an admin notice and disable itself until WooCommerce is available.

= Will this conflict with caching plugins? =

No. The plugin dequeues scripts during the normal WordPress enqueue cycle, before any caching layer captures output. Full-page caching plugins (WP Rocket, W3 Total Cache, LiteSpeed Cache, etc.) will cache the already-optimised output. There are no known conflicts.

= What does the Pro version add? =

[Store Accelerator Pro](https://wordpress.bluefairydevelopment.com/product/store-accelerator-for-woocommerce/) includes everything in Lite plus:

* **LQIP images** — Product images load with a blurred low-quality placeholder first, then swap to the full image when it enters the viewport. Reduces perceived load time dramatically.
* **AJAX lazy sections** — Heavy page sections (related products, recently viewed, etc.) are loaded via AJAX after the main content, keeping Time to First Byte and LCP fast.
* **Query caching** — Frequently repeated WooCommerce database queries are cached in the WordPress object cache, reducing database load on busy stores.
* **DB index tuning** — Adds targeted indexes to WooCommerce tables that commonly cause full-table scans on large catalogs.
* **Stats dashboard** — See exactly how many scripts were dequeued, how many queries were served from cache, and what index improvements were applied — on every page load.

= Does it work with block themes (Full Site Editing)? =

Yes. The plugin detects WooCommerce blocks (`woocommerce/cart`, `woocommerce/checkout`, `woocommerce/product-collection`) in post content and keeps the required scripts active whenever those blocks are present.

= Will it break my mini-cart widget? =

No. `wc-cart-fragments` is only dequeued on pages that have no cart-related content. Pages with the WooCommerce Cart block or the classic cart page always keep the script.

== Screenshots ==

1. The plugin row in Plugins > Installed Plugins — zero configuration, no Settings link, active and working immediately.
2. A WooCommerce shop page loading normally — unused scripts and styles silently removed in the background.

== Changelog ==

= 1.0.0 — 2026-08-15 =
* Initial release.
* Page-type-aware dequeuing of `wc-cart-fragments`, `select2`, and `selectWoo`.
* Dequeuing of WooCommerce layout stylesheets on non-WooCommerce pages.
* Block theme awareness for cart, checkout, and product collection blocks.
* Admin notice when WooCommerce is not active.
* "Upgrade to Pro →" action link in the Plugins list table.

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade steps required.
