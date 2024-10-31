<?php
/**
 * Plugin Name: Pythia for WooCommerce
 * Plugin URI:
 * Description: Pythia Plugin for WooCommerce helps you quickly connect to the Pytyhia platform, so you can easily start understanding your business performance right away on your phone.
 * Author: Pythia Bot
 * Author URI: https://www.pythiabot.com/
 * Text Domain: wc-pythia
 * Domain Path: /languages
 * Version: 1.1.6
 * Requires at least: 5.4.0
 * Requires PHP: 7.1
 *
 * WC requires at least: 4.3.6
 * WC tested up to: 4.4.1
 *
 * Copyright:
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC_Pythia
 */

defined( 'ABSPATH' ) || exit;

define( 'WC_PYTHIA__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_PYTHIA__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WC_PYTHIA__PLUGIN_ADMIN_DIR', WC_PYTHIA__PLUGIN_DIR . 'admin' );
define( 'WC_PYTHIA__PLUGIN_ADMIN_URL', WC_PYTHIA__PLUGIN_URL . 'admin' );
define( 'WC_PYTHIA_VERSION', '1.1.6' );

// App Stores URLs.
define( 'WC_PYTHIA__APP_STORE_URL', 'https://apps.apple.com/us/app/id1504896551' );
define( 'WC_PYTHIA__GOOGLE_PLAY_URL', 'https://play.google.com/store/apps/details?id=com.pythiabot.pythia' );

/**
 * WC Detection
 */
if ( ! function_exists( 'pythia_is_woocommerce_active' ) ) {
	/**
	 * Check if Woocommerce is active
	 *
	 * @return bool
	 */
	function pythia_is_woocommerce_active() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}
}

require_once WC_PYTHIA__PLUGIN_DIR . 'inc/class-wc-pythia-notices.php';
require_once WC_PYTHIA__PLUGIN_DIR . 'admin/class-wc-pythia-admin-notices.php';
require_once WC_PYTHIA__PLUGIN_DIR . 'inc/class-wc-pythia-settings.php';
require_once WC_PYTHIA__PLUGIN_DIR . 'inc/class-wc-pythia-api.php';
require_once WC_PYTHIA__PLUGIN_DIR . 'inc/class-wc-pythia-sync-scheduler.php';

// WC active check.
if ( pythia_is_woocommerce_active() ) {
	require_once WC_PYTHIA__PLUGIN_DIR . 'inc/class-wc-pythia-synchronizer.php';
}

require_once WC_PYTHIA__PLUGIN_DIR . 'inc/class-wc-pythia.php';


// Call activation hook.
register_activation_hook( __FILE__, array( 'WC_Pythia', 'on_activate' ) );
register_deactivation_hook( __FILE__, array( 'WC_Pythia', 'on_deactivate' ) );

// Add settings and setup link.
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( wc_pythia(), 'add_plugin_page_settings_links' ) );

if ( is_admin() ) {
	require_once WC_PYTHIA__PLUGIN_DIR . 'admin/wc-pythia-admin.php';
}
