=== Yak Plugin Installer ===
Contributors: tomatillodesign
Tags: github, plugin installer, bulk activation, plugin manager, one-click setup
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One-click installer for WordPress plugins from both GitHub and the official .org repo. Easily select and bulk-install your favorites.

== Description ==

Yak Plugin Installer is a setup utility built for fast, clean WordPress installs.

It’s designed for developers who manage a library of custom or GitHub-hosted plugins, alongside select .org favorites. Check the boxes you want, click once, and install + activate them in a single sweep — no FTP, no uploads, no zip juggling.

Built and maintained by [Tomatillo Design](https://tomatillodesign.com) as an internal utility. You’re welcome to adapt it for your own workflows.

=== Key Features ===

* ✅ Supports GitHub plugins via ZIP URL
* ✅ Installs .org plugins via WP Plugin API
* ✅ Check/uncheck plugin list via simple admin UI
* ✅ One-click install + activate
* ✅ Displays per-plugin install status
* ✅ Gracefully handles already-installed plugins
* ✅ AJAX-based — no page reloads
* ✅ Developer-friendly and extensible

== Installation ==

1. Clone or download this plugin into your `/wp-content/plugins/` directory.
2. Activate "Yak Plugin Installer" from your WordPress admin.
3. Go to **Tools → Yak Plugins** to configure your list and install.
4. Once all plugins are installed and activated, you can safely delete this plugin.

== Usage ==

* On first load, all available plugins are checked by default.
* Any plugins that are already active will be skipped and unchecked.
* After saving selections, click the blue “Install Selected Plugins” button.
* A real-time list of results will appear below the button.
* Works with both public GitHub repos and plugins from WordPress.org.

== FAQ ==

= Can I remove this plugin after installing everything? =

Yes — the plugins it installs stay active. This is just an installer utility, not a dependency manager.

= Does it support private GitHub repos? =

Not yet. You’d need to modify the install logic to include authentication headers or generate signed download links.

= Can I use this to auto-install plugins on client sites? =

Absolutely — this is ideal for that. You can also pre-configure your plugin list as needed.

== Screenshots ==

1. Plugin selection table
2. One-click installer with real-time feedback

== Changelog ==

= 1.0 =
* Initial release
* Supports GitHub + WordPress.org plugin installs
* AJAX-based bulk installer
* Clean admin interface

== Upgrade Notice ==

= 1.0 =
Initial release.

