=== Dark Mode for WP Dashboard ===
Contributors: naiches
Tags: dark mode, night mode, dark ui, dark mode dashboard
Tested up to: 7.0
Stable tag: 1.3.1
Requires at least: 6.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A smooth dark mode for the WordPress admin dashboard.

== Description ==

Adds a smooth dark mode to the default WordPress dashboard interface. Features:

* Instant toggle via admin bar — no page reload
* Per-user preference: Dark (always) / Light (always) / Auto (follows system preference)
* Full block editor support including the content canvas
* Full Site Editor (FSE) support
* Modern CSS custom properties for easy customization
* Dark mode is ON by default — just activate and go

Plugins officially supported:

* Advanced Custom Fields
* AIOSEO
* Better Search Replace
* Jetpack
* Kadence Blocks
* Nested Pages
* SmartCrawl SEO
* Smush
* The SEO Framework
* WooCommerce
* Yoast SEO (and Yoast SEO Premium, Local, News, WooCommerce)

Customization:
Use the `dark_mode_dashboard_css` filter to load a custom stylesheet. The plugin adds a `dark-mode` class to the admin body when active, which you can use as a CSS hook.

== Installation ==

1. Upload the plugin package to the plugins directory.
2. Activate the plugin.
3. Done! Your WordPress dashboard is now in dark mode :)

Use the toggle in the admin bar to switch between dark and light mode instantly. You can also set your preference to Auto in your user profile to follow your system's dark mode setting.

== Screenshots ==
1. Dashboard
2. Plugins
3. Themes

== Changelog ==
= 1.3.1 =
- Added: SmartCrawl SEO support
- Added: Smush support
- Fixed: Form table left/right padding on settings pages

= 1.3.0 =
- Complete CSS rewrite using CSS custom properties (design tokens)
- Full block editor support including editor canvas (iframe)
- Full Site Editor (FSE) support
- Instant toggle — no page reload needed
- Auto mode: follows your OS dark mode preference
- Added `dark_mode_dashboard_default_preference` filter for developers
- Added `dark-mode` body class for third-party CSS hooks
- Fixed: PHP warning on user profile page (Undefined array key)
- Fixed: Missing JS file causing console errors
- Fixed: Dark mode toggle showing on frontend
- Fixed: Unsanitized user meta save
- Fixed: Block editor content area not fully dark
- Fixed: Template parts / Site Editor pages not themed
- Updated: All plugin-specific styles for current versions
- Added: The SEO Framework support
- Added: WP Nested Pages full page listing support
- Fixed: WooCommerce order notes and order heading styling
- Fixed: Plugin Check escaping and sanitization issues
- Removed: MonsterInsights and WP to Twitter support
- Removed jQuery dependency
- Tested up to WordPress 7.0

= 1.2.4 =
- Update: Added nonce to requests

= 1.2.3 =
- Update: Added a toggle in the WP Admin toolbar. Also added support for Gutenberg Blocks by Kadence Blocks and fixed minor Gutenberg issues

= 1.2.2 =
- Fixed: Minor issue with ACF Group field

= 1.2.1 =
- Update: Added support for Google Analytics for WordPress by MonsterInsights & WP to Twitter

= 1.2.0 =
- Update: Added support for Yoast SEO Premium, Yoast SEO Local, Yoast SEO News, Yoast SEO Video, Yoast SEO WooCommerce & AIOSEO. Some minor fixes

= 1.1.3 =
- Update: Added Better Search Replace support

= 1.1.2 =
- Update: Added Advanced Custom Fields support

= 1.1.1 =
- Update: Fixed Gutenberg editor

= 1.1.0 =
- Update: Added the option to disable the dark mode in the user profile.

= 1.0.7 =
- Fixed: Issues with the graphs within the Jetpack plugin

= 1.0.6 =
- Fixed: Some issues with the Jetpack plugin

= 1.0.5 =
- Added: WooCommerce support

= 1.0.4 =
- Added: Nested Pages (plugin) support

= 1.0.3 =
- Update: Ownership change

= 1.0.2 =
- Added: Jetpack support

= 1.0.1 =
- Fixed: Notice: wp_register_style was called incorrectly.

= 1.0.0 =
- First public version
