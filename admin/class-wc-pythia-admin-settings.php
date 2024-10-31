<?php
/**
 * WC_Pythia_Admin_Settings class
 */
class WC_Pythia_Admin_Settings extends WC_Pythia_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		/**
		 * Register our wc_pythia_settings_init to the admin_init action hook
		*/
		add_action( 'admin_init', array( $this, 'wc_pythia_settings_init' ) );
		add_action( 'admin_menu', array( $this, 'wc_pythia_settings_page' ) );
		add_action( 'wc_pythia_admin_notices', array( $this, 'wc_pythia_settings_notice' ) );
	}

	/**
	 * Custom option and settings
	 */
	public function wc_pythia_settings_init() {
		$screen = get_current_screen();

		/*
		* Check if current screen is My Admin Page
		* Don't add help tab if it's not
		*/
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( ( isset( $_GET['page'] ) && 'wc_pythia' === $_GET['page'] ) && ( ! isset( $_POST['wc-pythia-submit'] ) && ! isset( $_GET['wc-pythia-reset'] ) && ! isset( $_GET['wc-pythia-disconnect'] ) ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_POST['wc-pythia-submit'] ) ) {
			$this->update_settings();
		} elseif ( isset( $_GET['wc-pythia-reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->reset_settings();
		} elseif ( isset( $_GET['wc-pythia-disconnect'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->disconnect();
		}
	}

	/**
	 * Top level menu
	 */
	public function wc_pythia_settings_page() {
		// add top level menu page.
		$main_admin_page = add_menu_page(
			'Pythia for Woocommerce',
			'Pythia',
			'manage_options',
			$this->plugin_id,
			array( $this, 'wc_pythia_settings_page_html' ),
			null
		);
		// load resources related with top level page.
		add_action( 'load-' . $main_admin_page, array( $this, 'wc_pythia_settings_page_load' ) );
	}


	/**
	 * Top level menu:
	 * callback public functions
	 */
	public function wc_pythia_settings_page_load() {
		$screen = get_current_screen();

		/*
		* Check if current screen is My Admin Page
		* Don't add help tab if it's not
		*/
		if ( 'toplevel_page_wc_pythia' !== $screen->id ) {
			return;
		}

		// Redirect to setup if no account is available.
		if ( ! wc_pythia()->is_setup() ) {
			wp_safe_redirect( wc_pythia()->get_setup_url(), 302, 'Pythia for Woocommerce' );
		}
		// Load style & scripts.

		$this->register_settings_scripts();

	}

	/**
	 * Register all the JS and CSS resources for the Settings page
	 *
	 * Register and enqueue the resources adding variables needed by localize script.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings_scripts() {

		// Register parent script.
		parent::register_scripts();

		wp_register_script( 'wc-pythia-settings', WC_PYTHIA__PLUGIN_URL . 'admin/assets/js/wc-pythia-settings.js', array( 'jquery', 'jquery-ui-dialog' ), WC_Pythia::version(), false );

		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_script( 'wc-pythia-settings' );
	}

	/**
	 * Update settings from Settings page
	 *
	 * Action executed after Settings form submission, current settings will be obtained from the database and merged with the new ones to be stored again.
	 * A redirection will be fired after trying to store new settings and a query string variable "pythia-settings-updated" will be added and the value will be true or false depending on the update result.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function update_settings() {
		if ( false === check_admin_referer( 'pythia-update-settings' ) ) {
			return;
		}

		$options           = wc_pythia()->settings->get_options();
		$submitted_options = array(
			'debug'           => isset( $_POST['wc_pythia_options_debug'] ) ? sanitize_text_field( wp_unslash( $_POST['wc_pythia_options_debug'] ) ) : 'no',
			'orders_per_page' => isset( $_POST['wc_pythia_options_orders_per_page'] ) ? sanitize_text_field( wp_unslash( $_POST['wc_pythia_options_orders_per_page'] ) ) : WC_Pythia_Synchronizer::ORDERS_PER_PAGE,
		);
		$new_options       = wp_parse_args( $submitted_options, $options );

		if ( $new_options && wc_pythia()->settings->update_options( $new_options ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wc_pythia&pythia-settings-updated=true' ) );
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=wc_pythia&pythia-settings-updated=false' ) );
		}
	}

	/**
	 * Disconnect Project account
	 *
	 * Project ID, Source ID and Token will be reset.
	 *
	 * @since 1.1.2
	 * @return void
	 */
	public function disconnect() {
		if ( false === check_admin_referer( 'pythia-disconnect' ) ) {
			return;
		}
		$options           = wc_pythia()->settings->get_options();
		$submitted_options = array(
			'profile_email'      => '',
			'profile_first_name' => '',
			'profile_last_name'  => '',
			'profile_token'      => '',
			'project_id'         => '',
			'project_name'       => '',
			'source_id'          => '',
			'source_name'        => '',
			'site_key'           => '',
		);
		$new_options       = wp_parse_args( $submitted_options, $options );

		if ( wc_pythia()->settings->update_options( $new_options ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wc_pythia&pythia-settings-updated=true' ) );
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=wc_pythia&pythia-settings-updated=false' ) );
		}
	}

	/**
	 * Reset settings action
	 *
	 * All settings stored in the database will be reset when this action is fired.
	 * Action wc_pythia_settings_reset is fired if the settings were reset successfully.
	 *
	 * @since 1.1.2
	 * @return void
	 */
	public function reset_settings() {
		if ( false === check_admin_referer( 'pythia-reset-settings' ) ) {
			return;
		}

		$new_options = array();

		if ( wc_pythia()->settings->update_options( $new_options ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wc_pythia&pythia-settings-updated=true' ) );
			do_action( 'wc_pythia_settings_reset' );
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=wc_pythia&pythia-settings-updated=false' ) );
		}
	}

	/**
	 * Print admin notices when settings update is successful or if failed
	 *
	 * @since 1.1.2
	 * @return void
	 */
	public function wc_pythia_settings_notice() {
		if ( array_key_exists( 'pythia-settings-updated', $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			if ( 'true' === $_GET['pythia-settings-updated'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				WC_Pythia_Notices::print_success_notice( __( 'Settings were updated successfully.', 'wc-pythia' ) );
			} elseif ( 'false' === $_GET['pythia-settings-updated'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				WC_Pythia_Notices::print_error_notice( __( 'Settings were not updated or were not changed.', 'wc-pythia' ) );
			}
		}
	}

	/**
	 * Top level menu:
	 * callback public functions
	 */
	public function wc_pythia_settings_page_html() {
		// check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$args = array(
			'show_advanced_settings' => false,
		);
		if ( isset( $_GET['show_advanced_settings'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$args['show_advanced_settings'] = boolval( wp_unslash( $_GET['show_advanced_settings'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}
		wc_pythia()->get_template( 'settings-form', $args );
	}
}

new WC_Pythia_Admin_Settings();
