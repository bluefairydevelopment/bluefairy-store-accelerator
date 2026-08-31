# Changelog

## 1.1.0

Rebuilt as the base plugin for Store Accelerator Pro. The two plugins previously shared no code, no namespace and no hook seam, and each implemented WooCommerce script removal separately.

### Fixed

- A page whose only WooCommerce content was a block or shortcode had all three WooCommerce stylesheets removed, so its product grid rendered unstyled. The block mapped to the `shop` context while the stylesheet rules keyed off `wc_page`, which such a page never received.
- WooCommerce blocks rendered by a block theme's templates were invisible to the context resolver, which read only the queried post's content. A front page whose product grid came from the template lost all three WooCommerce stylesheets.
- `wc-cart-fragments` was removed from every page outside the cart and checkout with no check for a displayed cart count, freezing the count on any theme that shows one — despite the code's own comment saying the handle was needed "on pages with a mini-cart".
- Three WooCommerce conditionals were called without the `function_exists()` guard the class documentation claimed for all of them.

### Added

- A settings tab under WooCommerce → Settings → Accelerator, with a switch per feature.
- Stylesheet removal as its own switch.
- `bfsa_script_rules`, `bfsa_style_rules`, `bfsa_page_context` and `bfsa_has_mini_cart`. 1.0.0 had no filters at all, so a site whose theme needed a handle could only deactivate the plugin.
- `bfsa_loaded`, `bfsa_feature_labels`, `bfsa_feature_descriptions`, `bfsa_register_settings`, `bfsa_save_settings` and `bfsa_settings_page_footer` — the seam the Pro add-on attaches to.
- Mini-cart detection covering the mini-cart block, a block theme's header template parts, and the classic cart widget.
- A unit suite.

### Changed

- Conflicting-plugin detection runs per request instead of once at activation, so installing or removing a caching plugin afterwards is noticed, and the notice names the plugin rather than printing its file path.
- The conflict notice appears on the Accelerator tab only, not on every admin screen.
- "Upgrade to Pro" is now "Get Store Accelerator Pro", and the link disappears once the add-on is installed.

## 1.0.0

First release.
