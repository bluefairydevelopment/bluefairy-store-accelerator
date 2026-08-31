# BlueFairy Store Accelerator — features

Every statement here is a claim about the shipped code. If one stops being true, the code is wrong or this file is.

## Script optimisation

Removes three handles at `wp_enqueue_scripts` priority 100, after WooCommerce enqueues at 10.

| Handle | Required by |
|---|---|
| `wc-cart-fragments` | `cart`, `checkout`, `mini_cart` |
| `select2` | `shop`, `product`, `cart`, `checkout`, `account` |
| `selectWoo` | `shop`, `product`, `cart`, `checkout`, `account` |

Change them with `bfsa_script_rules`. A handle beginning `wc-blocks-` is ignored whatever the rules say — those carry the block cart and checkout.

## Stylesheet optimisation

| Handle | Required by |
|---|---|
| `woocommerce-general` | `wc_page` |
| `woocommerce-layout` | `wc_page` |
| `woocommerce-smallscreen` | `wc_page` |

Change them with `bfsa_style_rules`.

## Page context

Resolved once, on the `wp` action. Flags: `cart`, `checkout`, `shop`, `product`, `account`, `wc_page`, `mini_cart`.

`wc_page` is set for any real WooCommerce page **and** for an ordinary page carrying WooCommerce content:

- Blocks: cart, checkout, mini-cart, single-product, all-products, product-collection, handpicked-products, product-best-sellers, product-category, product-new, product-on-sale, product-top-rated, product-tag
- Shortcodes: `products`, `product_page`, `product_category`, `add_to_cart`, `add_to_cart_url`, `woocommerce_cart`, `woocommerce_checkout`, `woocommerce_my_account`, `woocommerce_order_tracking`

WooCommerce blocks are also noticed as they render, which is how a front page whose product grid comes from the block template — not from the page's own content — is recognised. In a block theme the template body renders before `wp_head`, so the flag is set in time for the dequeue.

The mini-cart is the exception: it sets `mini_cart` and nothing else. It is styled by `wc-blocks-style`, not by the three stylesheets this plugin removes, and in a block theme it sits in the site header on every page — letting it set `wc_page` would keep those stylesheets everywhere and switch the feature off without saying so.

**Verify:** publish a page containing only a product-collection block and view source; all three WooCommerce stylesheets must be present. Then view a page with no WooCommerce content at all; all three must be absent.

## Mini-cart detection

`wc-cart-fragments` keeps a displayed cart count fresh without a page load. Detection covers:

1. the `woocommerce/mini-cart` block in the current page's content;
2. the same block in any `header`-area template part of a block theme, cached for a day per stylesheet;
3. the classic `woocommerce_widget_cart` widget in an active sidebar.

It cannot cover a theme that prints the count in its own header PHP. Nothing in WordPress exposes that. Use `bfsa_has_mini_cart` or switch the feature off.

**Verify:** with a mini-cart on the page, `wc-cart-fragments` must still be enqueued.

## Settings

WooCommerce → Settings → Accelerator. One checkbox per feature, stored in the `bfsa_features` option.

A feature with no stored value counts as **on**. This matters when the Pro add-on introduces new switches after the option was last written: defaulting to off would silently disable something a customer had just paid for.

`sanitize()` rebuilds the array from the label list, which is the same list the form renders from — so a key with no checkbox cannot be written off by a save, and a key that is not ours cannot be written at all.

## Conflict detection

Scanned per request from the `active_plugins` option. WP Rocket, LiteSpeed Cache, W3 Total Cache, WP Super Cache, Autoptimize, WP Fastest Cache, and BlueFairy Performance Kit Pro.

Performance Kit Pro is in that list because it also installs image placeholders on `woocommerce_product_get_image` at priority 10, the same hook and priority Store Accelerator Pro uses. Whichever registers first wins. Run one, not both.

The notice appears only on the Accelerator tab.

## What this plugin does not do

- No statistics. Those are in the Pro add-on.
- No database tables, no scheduled work, no HTTP requests.
- One option, `bfsa_features`, removed on uninstall.
- Nothing runs in wp-admin except the settings tab and the notices.

---

## What Store Accelerator Pro adds

You are running the free half. Everything above is yours for good. What follows is what the paid add-on does on top of it — it installs alongside this plugin and adds its switches to the same tab.

| | This plugin | Store Accelerator Pro |
|---|---|---|
| Script removal by page context | yes | yes, inherited |
| Stylesheet removal by page context | yes | yes, inherited |
| WooCommerce block and shortcode awareness | yes | yes, inherited |
| Mini-cart detection | yes | yes, inherited |
| Filterable rules | yes | yes, inherited |
| Conflict detection | yes | yes, inherited |
| **Blurred placeholders for product images** | — | **yes** |
| **Backfill for images uploaded before installation** | — | **yes** |
| **Reviews, related products and upsells deferred until scrolled to** | — | **yes** |
| **Product listing queries cached, pagination intact** | — | **yes** |
| **Database index for product meta lookups** | — | **yes** |
| **Usage counts, hourly and all time** | — | **yes** |
| **Redis / Memcached support** | — | **yes** |

### What you are doing without

Every product image on your shop page is downloaded at full size before a shopper has scrolled to it. Every product page renders its reviews, its related products and its upsells whether or not anyone reads them. Every visit to a category page runs the same product query again from the database.

None of that is visible as a fault. It is visible as a shop that takes a little longer than it should, on every page, for every shopper, for as long as the store runs.

Store Accelerator Pro fixes all three, keeps the pagination the naive version of query caching destroys, and tells you on the settings tab exactly how many times it has done so.

**Get it here: https://wordpress.bluefairydevelopment.com/product/bluefairy-store-accelerator-pro/**

