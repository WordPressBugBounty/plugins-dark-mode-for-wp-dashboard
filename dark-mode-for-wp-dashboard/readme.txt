=== Dark Mode for WP Dashboard ===
Contributors: naiches
Tags: dark mode, admin theme, dashboard, night mode, accessibility
Tested up to: 7.0
Stable tag: 1.3.5
Requires at least: 6.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A clean, lightweight dark mode for the WordPress admin dashboard.

== Description ==

No settings page, no bloat — just activate and go. Dark mode for every corner of your WordPress admin.

* Instant toggle in the admin bar — no page reload
* Per-user preference: Dark / Light / Auto (follows system)
* Full block editor and Site Editor support
* 12 popular plugins supported out of the box
* Developer-friendly: filters for default preference, custom CSS, and editor canvas control

Supported plugins:

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
* Yoast SEO (including Premium, Local, News, WooCommerce)
* Zamok

The plugin adds a `dark-mode` class to the admin body when active, and provides three filters for customization:

* `add_filter( 'dark_mode_dashboard_css', 'your_custom_stylesheet' )` — load a custom stylesheet
* `add_filter( 'dark_mode_dashboard_default_preference', function() { return 'disabled'; } )` — change the default mode for new users
* `add_filter( 'dark_mode_dashboard_editor_canvas', '__return_false' )` — disable dark mode for the editor content area

== Installation ==

1. Upload the plugin package to the plugins directory.
2. Activate the plugin.
3. Done! Your WordPress dashboard is now in dark mode :)

Use the toggle in the admin bar to switch between dark and light mode instantly. You can also set your preference to Auto in your user profile to follow your system's dark mode setting.

== Screenshots ==
1. Dashboard
2. Plugins
3. Pages

== Changelog ==
= 1.3.5 =
- Fixed: the block editor loaded the post content in light mode; switching to light and back made it dark. The editor renders the content in an iframe and the dark class was applied only once, before the editor finishes mounting its canvas — so the canvas WordPress ends up using never received it, and only a toggle put it back. It is now kept in sync for as long as the editor is mounting, and auto mode is honoured there too
- Fixed: the editor stylesheet was registered on a hook WordPress no longer wants for iframe styles, warning "added to the iframe incorrectly" on every editor load and relying on a compatibility shim; it now uses enqueue_block_assets

= 1.3.4 =
- Fixed: activating the plugin made the Gutenberg Custom HTML block preview render the site's 404 page inside the sandboxed preview iframe (with console sandbox violations). Caused by a leftover reference to a deleted editor stylesheet; editor styles are now only registered when the file exists
- Fixed: ACF Link field — the selected-link summary box rendered as a white island (ACF hardcodes a white background); now themed to the dark surface with a legible URL and visible external-link icon
- Fixed: WooCommerce variable-product Variations panel — variation rows, headers, row separators, and inner fields rendered light; now fully dark
- Fixed: WooCommerce product-data tab column showed a white strip below the last tab (the `ul.wc-tabs` pseudo-element fill); now dark
- Fixed: AIOSEO focus keyphrase pills (metabox + editor sidebar) rendered as bright light "islands" — now themed to the dark surface, with legible keyphrase text, icons, inline rename field, and score colours
- Fixed: AIOSEO Score button in the classic Publish box had a white background — now dark, keeping the green/orange/red score colour on the text and border
- Fixed: Zamok module warning notices rendered as light-on-light — now a readable amber-tinted notice on dark
- Fixed: WooCommerce Select2 multi-select chips (e.g. product tags) rendered as light-grey islands — now themed to the dark surface, including the remove (×) button

= 1.3.3 =
- Fixed: jQuery UI autocomplete dropdowns (tags, classic editor) now readable in dark mode
- Fixed: Command palette (⌘K) icons and "No results found" text were invisible (dark on dark)
- Fixed: Admin bar kbd shortcut badge no longer shows a light border

= 1.3.2 =
- Added: `dark_mode_dashboard_editor_canvas` filter to disable editor canvas dark mode
- Fixed: Editor toggle now properly reverts Classic Editor content area to light mode

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
