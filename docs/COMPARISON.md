# Store Accelerator: free and Pro

The source of truth for every comparison table on the storefront, in the plugins, and in the guides. Change the code, change this file, then rebuild everything downstream.

| | Free | Pro |
|---|---|---|
| Script removal by page context | yes | — inherited from the free plugin |
| Stylesheet removal by page context | yes | — inherited |
| WooCommerce block and shortcode awareness | yes | — inherited |
| Mini-cart detection | yes | — inherited |
| Filterable rules | yes | — inherited |
| Conflict detection | yes | — inherited |
| Settings tab | yes | adds four switches to it |
| Blurred image placeholders | — | yes |
| Backfill for existing images | — | yes |
| Deferred reviews, related products, upsells | — | yes |
| Product listing query cache | — | yes |
| Database index for meta lookups | — | yes |
| Usage counts | — | yes |
| Object cache support | — | yes |

## Things that must never be claimed again

Each of these appeared in 1.0.x collateral and was false:

- **"Related product IDs are cached separately for 10 minutes."** `woocommerce_related_products` is not a WooCommerce filter. The feature never ran, and WooCommerce already caches related products in the `wc_related_{id}` transient.
- **"Cache invalidation fires on product save, status change, term update, and bulk import."** Two of those hook names did not exist and the third was never registered.
- **"Session optimisation — object-cache session handler."** It was an admin notice saying the feature would arrive in a later version.
- **"Six performance modules."** One was a stub, one could not be switched on, and one duplicated the free plugin.
- **"Those are the only places [`wc-cart-fragments`] matters."** It matters anywhere a cart count is displayed, which on most themes is every page.
- **"Keeps the required scripts active when those blocks are present."** True for scripts, false for stylesheets, which is what actually broke.
- **"The store stays live during the ALTER TABLE."** True on the online path; the fallback locked the table and the sentence did not mention it. There is no fallback now.

## Pricing

Free plugin: £0, wordpress.org.
Pro: one-off, lifetime licence, one site. Requires the free plugin.
