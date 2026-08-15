# Changelog

All notable changes to Store Accelerator Lite for WooCommerce are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] — 2026-08-15

### Added
- Initial release.
- Page-type-aware dequeuing of `wc-cart-fragments` on non-cart/checkout pages.
- Page-type-aware dequeuing of `select2` and `selectWoo` on pages that do not need them.
- Dequeuing of WooCommerce layout stylesheets (`woocommerce-general`, `woocommerce-layout`, `woocommerce-smallscreen`) on non-WooCommerce pages.
- Block theme awareness: detects `woocommerce/cart`, `woocommerce/checkout`, and `woocommerce/product-collection` blocks and keeps scripts active when those blocks are present.
- Admin notice when WooCommerce is not active.
- "Upgrade to Pro →" action link in the Plugins list table.
