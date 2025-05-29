<?php
/**
 * Plugin Name: Yak Plugin Installer
 * Description: One-click GitHub + WordPress.org plugin installer with selectable options.
 * Author: Your Name
 * Version: 1.0
 */

add_action('admin_menu', function () {
	add_menu_page('Yak Plugins', 'Yak Plugins', 'manage_options', 'yak-plugins', 'yak_plugins_page');
});

function yak_plugins_get_list() {
	return [
		// GitHub plugin: zip download
		[
			'type' => 'github',
			'name' => 'Yak Cards',
			'slug' => 'yak-cards',
			'zip'  => 'https://github.com/YOURNAME/yak-cards/archive/refs/heads/main.zip',
		],
		// WordPress.org plugin: install via slug
		[
			'type' => 'wporg',
			'name' => 'Disable Comments',
			'slug' => 'disable-comments',
		],
	];
}

function yak_plugins_page() {
	$plugins      = yak_plugins_get_list();
	$saved        = get_option('yak_plugin_selections', []);
	$current_page = admin_url('admin.php?page=yak-plugins');

	if (isset($_POST['yak_plugins_submit']) && check_admin_referer('yak_plugins_form')) {
		$selections = $_POST['yak_plugins'] ?? [];
		update_option('yak_plugin_selections', $selections);
		echo '<div class="notice notice-success"><p>Selections saved.</p></div>';
	}

	echo '<div class="wrap"><h1>Yak Plugin Installer</h1>';
	echo '<form method="post">';
	wp_nonce_field('yak_plugins_form');
	echo '<table class="widefat"><thead><tr><th>Enable</th><th>Plugin</th><th>Status</th></tr></thead><tbody>';

	foreach ($plugins as $plugin) {
		$key = $plugin['slug'];
		$checked = in_array($key, $saved) ? 'checked' : '';
		$status = '❌ Not installed';

		if ($plugin['type'] === 'wporg') {
			$status = is_plugin_active("{$plugin['slug']}/{$plugin['slug']}.php") ? '✅ Active' : '❌ Inactive';
		} elseif ($plugin['type'] === 'github') {
			$status = is_plugin_active("{$plugin['slug']}/{$plugin['slug']}.php") ? '✅ Active' : '❌ Inactive';
		}

		echo '<tr>';
		echo '<td><input type="checkbox" name="yak_plugins[]" value="' . esc_attr($key) . '" ' . $checked . '></td>';
		echo '<td><strong>' . esc_html($plugin['name']) . '</strong></td>';
		echo '<td>' . $status . '</td>';
		echo '</tr>';
	}
	echo '</tbody></table><p><input type="submit" name="yak_plugins_submit" class="button button-primary" value="Save Plugin Selection"></p></form>';

	// Action buttons for install
	foreach ($plugins as $plugin) {
		if (!in_array($plugin['slug'], $saved)) continue;

		$install_url = wp_nonce_url(
			admin_url('admin-post.php?action=yak_install_plugin&slug=' . $plugin['slug']),
			'yak_install_plugin'
		);

		if ($plugin['type'] === 'github') {
			$install_url = add_query_arg('zip', urlencode($plugin['zip']), $install_url);
		}

		echo '<p><a href="' . esc_url($install_url) . '" class="button">Install + Activate: ' . esc_html($plugin['name']) . '</a></p>';
	}
	echo '</div>';
}

add_action('admin_post_yak_install_plugin', function () {
	check_admin_referer('yak_install_plugin');

	$slug = sanitize_text_field($_GET['slug'] ?? '');
	$zip  = esc_url_raw($_GET['zip'] ?? '');

	if (!current_user_can('install_plugins')) wp_die('Not allowed.');

	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/misc.php';
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
	include_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

	WP_Filesystem();
	$upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());

	if ($zip) {
		$upgrader->install($zip);
	} else {
		$api = plugins_api('plugin_information', ['slug' => $slug, 'fields' => ['sections' => false]]);
		if (is_wp_error($api)) wp_die('Plugin not found.');
		$upgrader->install($api->download_link);
	}

	$plugin_info = $upgrader->plugin_info();
	if ($plugin_info) activate_plugin($plugin_info);

	wp_redirect(admin_url('admin.php?page=yak-plugins'));
	exit;
});
