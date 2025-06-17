<?php
/**
 * Plugin Name: Yak Plugin & Theme Installer
 * Description: One-click GitHub + WordPress.org plugin and theme installer with selectable options and bulk AJAX install.
 * Version: 1.0.1
 * Author: Chris Liu-Beers, Tomatillo Design
 * Author URI: https://tomatillodesign.com
 */


if (!class_exists('Plugin_Upgrader')) {
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	require_once ABSPATH . 'wp-admin/includes/theme-install.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/class-theme-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
}


add_action('admin_menu', function () {
	add_menu_page('Yak Plugins', 'Yak Plugins', 'manage_options', 'yak-plugins', 'yak_installer_render_page');
});

function yak_installer_get_items() {
	return [
		'plugins' => [
			[ 'type' => 'github', 'name' => 'AVIF Everywhere (GitHub)', 'slug' => 'tomatillo-design-avif-everywhere', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-avif-everywhere/archive/refs/heads/main.zip' ],
			[ 'type' => 'github', 'name' => 'CTD Tabs on Page (GitHub)', 'slug' => 'ctd-tabs-on-page', 'zip' => 'https://github.com/tomatillodesign/ctd-tabs-on-page/archive/refs/heads/main.zip' ],
			[ 'type' => 'wporg', 'name' => 'Disable Comments (WordPress.org)', 'slug' => 'disable-comments' ],
			[ 'type' => 'wporg', 'name' => 'Limit Login Attempts Reloaded (WordPress.org)', 'slug' => 'limit-login-attempts-reloaded' ],
			[ 'type' => 'wporg', 'name' => 'Safe SVG (WordPress.org)', 'slug' => 'safe-svg' ],
			[ 'type' => 'github', 'name' => 'Media Manager (GitHub)', 'slug' => 'tomatillo-design-media-manager', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-media-manager/archive/refs/heads/main.zip' ],
			[ 'type' => 'github', 'name' => 'Simple Collapse (GitHub)', 'slug' => 'tomatillo-design-simple-collapse', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-simple-collapse/archive/refs/heads/main.zip' ],
			[ 'type' => 'github', 'name' => 'Site Manager Role (GitHub)', 'slug' => 'tomatillo-design-site-manager-role', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-site-manager-role/archive/refs/heads/main.zip' ],
			[ 'type' => 'github', 'name' => 'Yak Events Calendar (GitHub)', 'slug' => 'tomatillo-design-yak-events-calendar', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-yak-events-calendar/archive/refs/heads/main.zip' ],
			[ 'type' => 'github', 'name' => 'Yak Info Cards (GitHub)', 'slug' => 'tomatillo-design-yak-info-cards', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-yak-info-cards/archive/refs/heads/main.zip' ],
			[ 'type' => 'github', 'name' => 'YakStretch Cover Block (GitHub)', 'slug' => 'tomatillo-design-yakstretch-cover-block', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-yakstretch-cover-block/archive/refs/heads/main.zip' ],
		],
		'themes' => [
			[ 'type' => 'github', 'name' => 'Yak Theme (GitHub)', 'slug' => 'yak', 'zip' => 'https://github.com/tomatillodesign/yak/archive/refs/heads/main.zip' ],
		],
	];
}

function yak_installer_render_page() {
	$items = yak_installer_get_items();
	$saved = get_option('yak_plugin_selections', false);
	if ($saved === false) {
		$saved = array_merge(
			array_map(fn($p) => $p['slug'], $items['plugins']),
			array_map(fn($t) => $t['slug'], $items['themes'])
		);
		update_option('yak_plugin_selections', $saved);
	}

	echo '<div class="wrap"><h1>Yak Plugin & Theme Installer</h1>';
	echo '<form id="yak-plugins-form">';

	foreach (['plugins' => 'Plugins', 'themes' => 'Yak Theme'] as $group => $label) {
		echo '<h2>' . esc_html($label) . '</h2>';
		echo '<table class="widefat"><thead><tr><th>Enable</th><th>Name</th><th>Status</th></tr></thead><tbody>';

		foreach ($items[$group] as $item) {
			$slug = $item['slug'];
			$status = '❌ Not installed';
			$is_installed = false;
			$is_active = false;

			if ($group === 'plugins') {
				$all_plugins = get_plugins();
				foreach (array_keys($all_plugins) as $path) {
					if (stripos($path, $slug) !== false) {
						$is_installed = true;
						if (is_plugin_active($path)) {
							$is_active = true;
							$status = '✅ Active';
						} else {
							$status = '❌ Inactive';
						}
						break;
					}
				}
			} elseif ($group === 'themes') {
				$theme = wp_get_theme($slug);
				if ($theme->exists()) {
					$is_installed = true;
					$status = '✅ Installed';
				}
			}

			// Only check the box if it's in saved list and NOT active or installed
			if ($group === 'plugins') {
				$checked = (in_array($slug, $saved, true) && !$is_active) ? 'checked' : '';
				} elseif ($group === 'themes') {
					$checked = (in_array($slug, $saved, true) && !$is_installed) ? 'checked' : '';
				}

			echo '<tr>';
			echo '<td><input type="checkbox" name="yak_plugins[]" value="' . esc_attr($slug) . '" ' . $checked . '></td>';
			echo '<td><strong>' . esc_html($item['name']) . '</strong></td>';
			echo '<td>' . $status . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	echo '<p><button type="button" class="button button-primary" id="yak-install-button">Install Selected Items</button></p>';
	echo '</form><div id="yak-install-output" style="margin-top:1em;"></div></div>';
}



add_action('wp_ajax_yak_install_selected_plugins', function () {
	if (!current_user_can('install_plugins')) wp_send_json_error('Unauthorized');
	$selected = $_POST['plugins'] ?? [];
	$items = yak_installer_get_items();

	// require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	// require_once ABSPATH . 'wp-admin/includes/theme-install.php';
	// require_once ABSPATH . 'wp-admin/includes/file.php';
	// require_once ABSPATH . 'wp-admin/includes/misc.php';
	// require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	// require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
	// require_once ABSPATH . 'wp-admin/includes/class-theme-upgrader.php';
	// require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';

	WP_Filesystem();
	$results = [];

	foreach (['plugins', 'themes'] as $group) {
		foreach ($items[$group] as $item) {
			if (!in_array($item['slug'], $selected, true)) continue;

			$zip = $item['zip'] ?? '';
			if (!$zip) {
				$results[] = $item['name'] . ' ❌ No ZIP file URL.';
				continue;
			}

			if ($group === 'plugins') {
				$upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
				$success = $upgrader->install($zip);
				$plugin_info = $upgrader->plugin_info();
				if ($success && $plugin_info && !is_wp_error($plugin_info)) {
					activate_plugin($plugin_info);
					$results[] = $item['name'] . ' ✅ Installed and activated.';
				} else {
					$results[] = $item['name'] . ' ❌ Install failed.';
				}
			} elseif ($group === 'themes') {
				$theme_upgrader = new Theme_Upgrader(new Automatic_Upgrader_Skin());
				$success = $theme_upgrader->install($zip);
				if ($success) {
					$results[] = $item['name'] . ' ✅ Theme installed. Activate via Appearance > Themes.';
				} else {
					$results[] = $item['name'] . ' ❌ Theme install failed.';
				}
			}
		}
	}

	wp_send_json_success($results);
});

add_action('admin_footer', function () {
	$screen = get_current_screen();
	if ($screen && $screen->id === 'toplevel_page_yak-plugins') : ?>
		<script>
			document.getElementById('yak-install-button').addEventListener('click', () => {
				const form = document.getElementById('yak-plugins-form');
				const data = new FormData(form);
				const selected = Array.from(data.getAll('yak_plugins[]'));
				const output = document.getElementById('yak-install-output');
				output.innerHTML = '<em>Installing...</em>';

				const params = new URLSearchParams();
				params.append('action', 'yak_install_selected_plugins');
				selected.forEach(slug => params.append('plugins[]', slug));

				fetch(ajaxurl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'X-Requested-With': 'XMLHttpRequest' },
					body: params
				})
				.then(res => res.json())
				.then(json => {
					if (json.success) {
						output.innerHTML = '<ul>' + json.data.map(line => '<li>' + line + '</li>').join('') + '</ul>';
					} else {
						output.innerHTML = '<p style="color:red;">Install failed: ' + json.data + '</p>';
					}
				})
				.catch(err => {
					output.innerHTML = '<p style="color:red;">Unexpected error.</p>';
					console.error(err);
				});
			});
		</script>
	<?php endif;
});
