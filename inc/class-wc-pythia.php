<?php
/**
 * Pythia for Woocommerce Setup
 *
 * @package WC_Pythia
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Pythia for Woocommerce Class
 *
 * @since 1.0.0
 */
class WC_Pythia {

	/**
	 * WC_Pythia single instance of this plugin
	 *
	 * @var WC_Pythia
	 */
	protected static $instance = null;

	/**
	 * Plugin ID
	 */
	const PLUGIN_ID = 'wc_pythia';

	/**
	 * Plugin Prefix
	 */
	const PLUGIN_PREFIX = 'wc_pythia_';

	/**
	 * Single Instance of WC_Pythia_Settings
	 *
	 * @since 1.0.0
	 * @var WC_Pythia_Settings
	 */
	public $settings;

	/**
	 * Single Instance of WC_Pythia_Api
	 *
	 * @since 1.0.0
	 * @var WC_Pythia_Api
	 */
	public $api;

	/**
	 * Single Instance of WC_Pythia_Synchronizer
	 *
	 * @since 1.0.0
	 * @var WC_Pythia_Synchronizer
	 */
	public $sync;


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0.0
	 * @return \WC_Pythia
	 */
	public function __construct() {
		$this->settings = new WC_Pythia_Settings();
		$this->api      = WC_Pythia_Api::instance();

		if ( pythia_is_woocommerce_active() ) {
			$this->sync = WC_Pythia_Synchronizer::instance();
			add_action( 'woocommerce_init', array( $this, 'init_woocommerce' ) );
		}
	}

	/**
	 * Init action executed when woocommerce_init action run
	 *
	 * @return void
	 */
	public function init_woocommerce() {
		// Admin noticed when sync is disabled.
		if ( $this->sync->is_sync_disabled() ) {
			self::add_notice_when_sync_is_disabled();
		}
		add_action( 'wc_pythia_settings_updated', array( __CLASS__, 'remove_is_sync_disabled_notice' ) );
	}

	/**
	 * Determine if an account is setup.
	 *
	 * @since 1.1.2
	 * @return boolean
	 */
	public function is_setup() {
		return ! empty( wc_pythia()->settings->get_token() );
	}

	/**
	 * Main Pythia Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.0.0
	 * @see wc_pythia()
	 * @return \WC_Pythia
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Return a key generated using site url without protocol
	 *
	 * @since 1.1.2
	 * @return string Key generated using sanitize_key method
	 */
	public function site_key() {
		$site_url = str_replace( array( 'http://', 'https://', '//' ), '', strtolower( site_url() ) );
		return sanitize_key( $site_url );
	}

	public static function version() {
		return WC_PYTHIA_VERSION;
	}

	public static function plugin_id() {
		return self::PLUGIN_ID;
	}

	public static function plugin_prefix() {
		return self::PLUGIN_PREFIX;
	}

	public static function plugin_dir() {
		return WC_PYTHIA__PLUGIN_DIR;
	}

	public static function plugin_admin_dir() {
		return WC_PYTHIA__PLUGIN_ADMIN_DIR;
	}

	public static function plugin_url() {
		return WC_PYTHIA__PLUGIN_URL;
	}

	public static function plugin_admin_url() {
		return WC_PYTHIA__PLUGIN_ADMIN_URL;
	}

	public static function on_deactivate() {
		// Use null as args parameter to cancel recurring and single schedules.
		WC_Pythia_Sync_Scheduler::cancel_schedule( null );
		self::remove_setup_notice();
		self::remove_is_sync_disabled_notice();
	}

	public static function on_activate() {
		if ( ! wc_pythia()->is_setup() ) {
			ob_start();
			include WC_PYTHIA__PLUGIN_DIR . 'admin/templates/notices/html-notice-install.php';
			$install_notice = ob_get_contents();
			ob_end_clean();
			self::add_admin_notice( 'wc_pythia_setup', $install_notice );
		}
	}

	public static function add_notice_when_sync_is_disabled() {
		// TODO: do not run this in ajax call
		if ( ! WC_Pythia_Admin_Notices::has_notice( 'wc_pythia_sync_disabled' ) && false === boolval( get_user_meta( get_current_user_id(), 'dismissed_wc_pythia_sync_disabled_notice', true ) ) ) {
			ob_start();
			include WC_PYTHIA__PLUGIN_DIR . 'admin/templates/notices/html-notice-sync-disabled.php';
			$install_notice = ob_get_contents();
			ob_end_clean();
			self::add_admin_notice( 'wc_pythia_sync_disabled', $install_notice );
		}
	}

	public static function remove_is_sync_disabled_notice() {
		self::remove_admin_notice( 'wc_pythia_sync_disabled' );
	}

	public static function remove_setup_notice() {
		self::remove_admin_notice( 'wc_pythia_setup' );
	}

	public static function remove_admin_notice( $key ) {
		if ( WC_Pythia_Admin_Notices::has_notice( $key ) ) {
			WC_Pythia_Admin_Notices::remove_notice( $key );
		}
	}

	public static function add_admin_notice( $key, $content ) {
		WC_Pythia_Admin_Notices::add_custom_notice( $key, $content );
	}

	public function add_plugin_page_settings_links( $links ) {
		$links[] = '<a href="' . $this->get_settings_url() . '">' . __( 'Settings', 'wc-pythia' ) . '</a>';

		// Do not display setup link if token already exists
		if ( ! $this->settings->get_token() ) {
			$links[] = '<a href="' . $this->get_setup_url() . '">' . __( 'Setup', 'wc-pythia' ) . '</a>';
		}
		return $links;
	}

	public function log( $message, $type = 'info', $source = 'wc-pythia' ) {
		$logger = wc_get_logger();
		// $context may hold arbitrary data.
		// If you provide a "source", it will be used to group your logs.
		// More on this later.
		$context = array( 'source' => $source );

		if ( $message && method_exists( $logger, 'log' ) ) {
			// The `log` method accepts any valid level as its first argument.
			$logger->log( $type, $message, $context );
		}
	}

	public function debugging_log( $message, $type = 'debug', $source = 'wc-pythia' ) {
		if ( $this->settings->debug_enabled() ) {
			$this->log( $message, $type, $source );
		}
	}

	/**
	 * Log API if Debug is enabled
	 *
	 * @param string $message Message to be logged.
	 * @param string $type debug|info|error Debug type.
	 * @return void
	 */
	public function api_log( $message, $type = 'debug' ) {
		if ( $this->debug_api() ) {
			if ( is_array( $message ) ) {
				$message = wp_json_encode( $message );
			}
			$this->log( $message, $type, 'wc-pythia-api' );
		}
	}

	/**
	 * Check if Debug API is enabled
	 *
	 * @return bool true|false
	 */
	public function debug_api() {
		return ( defined( 'WC_PYTHIA_API_DEBUG' ) && WC_PYTHIA_API_DEBUG === true );
	}

	public function error_log( $message, $type = 'error', $source = 'wc-pythia' ) {
		$this->log( $message, $type, $source );
	}

	/**
	 * Check if it is a wizard step
	 *
	 * @since 1.1.4
	 * @return string URL to the settings page
	 */
	public function is_wizard_step() {
		return (bool) ( isset( $_REQUEST['wizard_step'] ) && true === boolval( $_REQUEST['wizard_step'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}

	/**
	 * Gets the URL to the settings page
	 *
	 * @since 1.0.0
	 * @return string URL to the settings page
	 */
	public function get_settings_url() {
		return admin_url( 'admin.php?page=wc_pythia' );
	}

	/**
	 * Gets the URL to the setup page
	 *
	 * @since 1.0.0
	 * @return string URL to the setup page
	 */
	public function get_setup_url() {
		return admin_url( 'admin.php?page=wc_pythia_setup' );
	}

	/**
	 * Gets the URL to the login page
	 *
	 * @since 1.0.0
	 * @return string URL to the login page
	 */
	public function get_login_url() {
		return admin_url( 'admin.php?page=wc_pythia_login' );
	}

	/**
	 * Gets the URL to the login page
	 *
	 * @since 1.1.2
	 * @return string URL to the login page
	 */
	public function get_ga_url() {
		return admin_url( 'admin.php?page=wc_pythia_google_auth' );
	}

	/**
	 * Gets the URL to the synchronization page
	 *
	 * @since 1.1.4
	 * @return string URL to the synchronization page
	 */
	public function get_sync_url() {
		return admin_url( 'admin.php?page=wc_pythia_sync' );
	}

	/**
	 * Gets the URL to the select project page
	 *
	 * @since 1.1.4
	 * @return string URL to the select project page
	 */
	public function get_select_project_url() {
		return admin_url( 'admin.php?page=wc_pythia_project' );
	}

	/**
	 * Get all Pythia screen ids.
	 *
	 * @since 1.1.5
	 * @return array
	 */
	public function get_screen_ids() {

		$screen_ids   = array(
			'toplevel_page_wc_pythia',
			'pythia_page_wc_pythia_google_auth',
			'pythia_page_wc_pythia_login',
			'pythia_page_wc_pythia_project',
			'pythia_page_wc_pythia_setup',
			'pythia_page_wc_pythia_sync',
			'profile',
			'user-edit',
		);

		return apply_filters( 'wp_pythia_screen_ids', $screen_ids );
	}

	/**
	 * Get other templates passing attributes and including the file.
	 *
	 * @param string $template_name Template name.
	 * @param array  $args          Arguments. (default: array).
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path  Default path. (default: '').
	 */
	public function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		$template = $this->locate_template( $template_name, $template_path, $default_path );

		// Allow 3rd party plugin filter template file from their plugin.
		$filter_template = apply_filters( 'wc_pythia_get_template', $template, $template_name, $args, $template_path, $default_path );

		if ( $filter_template !== $template ) {
			if ( ! file_exists( $filter_template ) ) {
				/* translators: %s template */
				wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'wc-pythia' ), '<code>' . $template . '</code>' ), '2.1' );
				return;
			}
			$template = $filter_template;
		}

		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args ); // @codingStandardsIgnoreLine
		}

		include $template;
	}

	/**
	 * Like get_template, but returns the HTML instead of outputting.
	 *
	 * @see get_template
	 * @since 1.1.4
	 * @param string $template_name Template name.
	 * @param array  $args          Arguments. (default: array).
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path  Default path. (default: '').
	 *
	 * @return string
	 */
	public function get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		ob_start();
		$this->get_template( $template_name, $args, $template_path, $default_path );
		return ob_get_clean();
	}
	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 * admin/templates/$template_path/$template_name
	 * admin/templates/$template_name
	 *
	 * @param string $template_name Template name.
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path  Default path. (default: '').
	 * @return string
	 */
	public function locate_template( $template_name, $template_path = '', $default_path = '' ) {

		if ( ! $default_path ) {
			$default_path = self::plugin_admin_dir() . '/templates/';
		}

		// Get default template/.
		$template = $default_path . $template_name . '.php';

		// Return what we found.
		return apply_filters( 'wc_pythia_locate_template', $template, $template_name, $template_path );
	}

} // end \WC_Pythia class


/**
 * Returns the One True Instance of WC Pythia
 *
 * @since 1.1.0
 * @return \WC_Pythia
 */
function wc_pythia() {
	return WC_Pythia::instance();
}

// Launch!
wc_pythia();
