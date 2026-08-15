# BlueFairy Store Accelerator Lite for WooCommerce

**Version:** 1.0.0
**Requires WordPress:** 6.5+
**Requires PHP:** 8.1+
**Requires WooCommerce:** 8.0+
**License:** GPL-2.0-or-later

Remove unused WooCommerce scripts and styles from pages that don't need them — zero config, instant speed gains.

---

## What it does

WooCommerce enqueues several JavaScript and CSS files on every page by default. This plugin removes the ones that are not needed on the current page, using page-type detection rather than a simple on/off switch.

| Handle | Dequeued on |
|--------|-------------|
| `wc-cart-fragments` | All pages except cart, checkout, and pages containing a WooCommerce cart block |
| `select2` / `selectWoo` | All pages except shop archives, product pages, cart, checkout, account |
| `woocommerce-general` / `woocommerce-layout` / `woocommerce-smallscreen` | All non-WooCommerce pages |

Block themes are fully supported: if a page contains `woocommerce/cart`, `woocommerce/checkout`, or `woocommerce/product-collection` blocks, the required scripts are kept active.

`wc-blocks-*` handles are **never** touched.

---

## Installation

1. Upload the `bluefairy-store-accelerator-lite` directory to `wp-content/plugins/`.
2. Activate via **Plugins > Installed Plugins**.
3. Done — no configuration needed.

---

## File structure

```
bluefairy-store-accelerator-lite/
├── bluefairy-store-accelerator-lite.php   Plugin header + bootstrap loader
├── includes/
│   ├── class-plugin.php                   WC active check, admin notice, action links
│   └── class-script-dequeue.php          Page-type-aware dequeue logic
├── .gitignore
├── CHANGELOG.md
├── README.md
└── readme.txt                             WordPress.org format
```

---

## Upgrade to Pro

[Store Accelerator Pro](https://wordpress.bluefairydevelopment.com/product/store-accelerator-for-woocommerce/) adds:

- LQIP (Low Quality Image Placeholder) lazy loading
- AJAX lazy-loaded content sections
- WooCommerce query caching
- Database index tuning for large catalogs
- Stats dashboard

---

## Development

No build step required. PHP only.

**Coding standards:** PSR-12, `declare(strict_types=1)` in every file.

---

## License

GPL-2.0-or-later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).
