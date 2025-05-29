<?php
/**
 * Plugin Name: Yak Plugin Installer
 * Description: One-click GitHub + WordPress.org plugin installer with selectable options and bulk AJAX install.
 * Version: 1.0
 * Author: Chris Liu-Beers, Tomatillo Design
 * Author URI: https://tomatillodesign.com
 */

add_action('admin_menu', function () {
	add_menu_page('Yak Plugins', 'Yak Plugins', 'manage_options', 'yak-plugins', 'yak_plugins_page');
});

function yak_plugins_get_list() {
	return [
		[ 'type' => 'github', 'name' => 'AVIF Everywhere (GitHub)', 'slug' => 'tomatillo-design-avif-everywhere', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-avif-everywhere/archive/refs/heads/main.zip' ],
		[ 'type' => 'github', 'name' => 'CTD Tabs on Page (GitHub)', 'slug' => 'ctd-tabs-on-page', 'zip' => 'https://github.com/tomatillodesign/ctd-tabs-on-page/archive/refs/heads/main.zip' ],
		[ 'type' => 'wporg', 'name' => 'Disable Comments (WordPress.org)', 'slug' => 'disable-comments' ],
		[ 'type' => 'wporg', 'name' => 'Limit Login Attempts Reloaded (WordPress.org)', 'slug' => 'limit-login-attempts-reloaded' ],
		[ 'type' => 'wporg', 'name' => 'Safe SVG (WordPress.org)', 'slug' => 'safe-svg' ],
		[ 'type' => 'github', 'name' => 'Simple Collapse (GitHub)', 'slug' => 'tomatillo-design-simple-collapse', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-simple-collapse/archive/refs/heads/main.zip' ],
		[ 'type' => 'github', 'name' => 'Site Manager Role (GitHub)', 'slug' => 'tomatillo-design-site-manager-role', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-site-manager-role/archive/refs/heads/main.zip' ],
		[ 'type' => 'github', 'name' => 'Yak Events Calendar (GitHub)', 'slug' => 'tomatillo-design-yak-events-calendar', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-yak-events-calendar/archive/refs/heads/main.zip' ],
		[ 'type' => 'github', 'name' => 'Yak Info Cards (GitHub)', 'slug' => 'tomatillo-design-yak-info-cards', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-yak-info-cards/archive/refs/heads/main.zip' ],
		[ 'type' => 'github', 'name' => 'YakStretch Cover Block (GitHub)', 'slug' => 'tomatillo-design-yakstretch-cover-block', 'zip' => 'https://github.com/tomatillodesign/tomatillo-design-yakstretch-cover-block/archive/refs/heads/main.zip' ],
	];
}

function yak_plugins_page() {
	$plugins = yak_plugins_get_list();
	$saved = get_option('yak_plugin_selections', false);

	if ($saved === false) {
		$saved = array_map(fn($p) => $p['slug'], $plugins);
		update_option('yak_plugin_selections', $saved);
	} else {
		$valid_slugs = array_map(fn($p) => $p['slug'], $plugins);
		$saved = array_intersect($saved, $valid_slugs);
		$saved = array_unique(array_merge($saved, $valid_slugs));
		update_option('yak_plugin_selections', $saved);
	}

	$all_plugins = get_plugins();

	echo '<div class="wrap"><h1>Yak Plugin Installer</h1>';
	echo '<form id="yak-plugins-form">';
	echo '<table class="widefat"><thead><tr><th>Enable</th><th>Plugin</th><th>Status</th></tr></thead><tbody>';

	foreach ($plugins as $plugin) {
		$slug = $plugin['slug'];
		$matched_path = null;

		foreach (array_keys($all_plugins) as $path) {
			if (str_starts_with($path, $slug . '/')) {
				$matched_path = $path;
				break;
			}
		}

		if (!$matched_path) {
			foreach (array_keys($all_plugins) as $path) {
				if (str_contains($path, $slug)) {
					$matched_path = $path;
					break;
				}
			}
		}

		$is_active = $matched_path && is_plugin_active($matched_path);
		$checked = (!$is_active && in_array($slug, $saved, true)) ? 'checked' : '';

		$status = '❌ Not installed';
		if ($matched_path) {
			$status = $is_active ? '✅ Active' : '❌ Inactive';
		}

		echo '<tr>';
		echo '<td><input type="checkbox" name="yak_plugins[]" value="' . esc_attr($slug) . '" ' . $checked . '></td>';
		echo '<td><strong>' . esc_html($plugin['name']) . '</strong></td>';
		echo '<td>' . $status . '</td>';
		echo '</tr>';
	}

	echo '</tbody></table>';
	echo '<p><button type="button" class="button button-primary" id="yak-install-button">Install Selected Plugins</button></p>';
	echo '</form><div id="yak-install-output" style="margin-top:1em;"></div></div>';
}

add_action('wp_ajax_yak_install_selected_plugins', function () {
	if (!current_user_can('install_plugins')) wp_send_json_error('Unauthorized');
	$selected = $_POST['plugins'] ?? [];
	$plugins = yak_plugins_get_list();

	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';

	WP_Filesystem();
	$results = [];

	foreach ($plugins as $plugin) {
		if (!in_array($plugin['slug'], $selected)) continue;
		$upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
		$zip = $plugin['zip'] ?? '';

		if ($plugin['type'] === 'github' && $zip) {
			$success = $upgrader->install($zip);
		} elseif ($plugin['type'] === 'wporg') {
			$api = plugins_api('plugin_information', ['slug' => $plugin['slug'], 'fields' => ['sections' => false]]);
			if (is_wp_error($api)) {
				$results[] = $plugin['name'] . ' ❌ Plugin not found.';
				continue;
			}
			$success = $upgrader->install($api->download_link);
		} else {
			$results[] = $plugin['name'] . ' ❌ Unknown plugin type.';
			continue;
		}

		$plugin_info = $upgrader->plugin_info();
		if ($plugin_info && !is_wp_error($plugin_info)) {
			activate_plugin($plugin_info);
			$results[] = $plugin['name'] . ' ✅ Installed and activated.';
		} else {
			$results[] = $plugin['name'] . ' ❌ Install failed.';
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
                console.log('[Yak Installer] AJAX response:', json); // <--- add this
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





register_uninstall_hook(__FILE__, function () {
	delete_option('yak_plugin_selections');
});
