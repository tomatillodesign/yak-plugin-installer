# Yak Plugin Installer

One-click installer for WordPress plugins from both GitHub and the official .org repo. Easily bulk-install your favorite plugins with a clean, developer-friendly UI.

Built by [Tomatillo Design](https://tomatillodesign.com) for internal projects — but you're welcome to adapt it for your own workflows.

---

## 🔧 Features

- ✅ One-click install and activate
- ✅ Supports GitHub ZIP URLs
- ✅ Installs WordPress.org plugins via the API
- ✅ Admin UI with checkboxes for plugin selection
- ✅ AJAX-based (no reloads)
- ✅ Skips already installed + active plugins
- ✅ Status indicators per plugin
- ✅ Easily extendable

---

## 🧠 Use Case

This is for developers who maintain a reusable set of custom plugins — some hosted on GitHub, others pulled from wp.org — and want a faster way to deploy them on fresh WordPress installs.

---

## 📦 Installation

1. Clone or download this plugin into your `/wp-content/plugins/` directory.
2. Activate **Yak Plugin Installer**.
3. Navigate to **Tools → Yak Plugins**.
4. Check the boxes for the plugins you want to install.
5. Click **Install Selected Plugins** and watch the magic happen.

---

## ✅ Safety

- You can safely delete this plugin once your list is installed.
- Installed plugins remain active and unaffected.
- This plugin doesn’t auto-update or interfere with anything outside its page.

---

## 🗂 Plugin Format

Each plugin entry in the list includes:

```php
[
	'type' => 'github' | 'wporg',
	'name' => 'Readable Plugin Name',
	'slug' => 'unique-plugin-slug',
	'zip'  => 'https://github.com/.../main.zip' // only for GitHub plugins
]
