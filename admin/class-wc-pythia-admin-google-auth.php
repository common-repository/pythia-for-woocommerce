<?php
/**
 *
 */
class WC_Pythia_Admin_Google_Auth extends WC_Pythia_Admin {

	/**
	 * Pythia google auth api
	 *
	 * @var WC_Pythia_Google_Auth_Api
	 */
	private $auth_api;

	/**
	 * Constructor method. Declare init actions
	 *
	 * @since 1.1.3
	 */
	public function __construct() {
		parent::__construct();
		$this->auth_api = new WC_Pythia_Google_Auth_Api();

		/**
		 * Register our wc_pythia_google_auth_init to the admin_init action hook
		*/
		add_action( 'admin_init', array( $this, 'wc_pythia_google_auth_init' ) );
		/**
		 * Register our wc_pythia_google_auth_page to the admin_menu action hook
		 */
		// Don't display setup page if a token exists
		// this means that an account already exists.
		if ( wc_pythia()->is_setup() ) {
			add_action( 'admin_menu', array( $this, 'wc_pythia_google_auth_page' ) );
		}
	}

	/**
	 * Init method
	 *
	 * Declare ajax actions.
	 *
	 * @since 1.1.3
	 * @return void
	 */
	public function wc_pythia_google_auth_init() {
		// Authentication Actions.
		add_action( 'wp_ajax_pythia_maybe_authenticate', array( $this, 'maybe_authenticate' ) );
		add_action( 'wp_ajax_pythia_update_source_id', array( $this, 'update_source_id' ) );
		add_action( 'wp_ajax_pythia_update_analytics_ua_account', array( $this, 'update_analytics_ua_account' ) );
	}

	public function maybe_authenticate() {

		// Check nonce.
		check_ajax_referer( 'pythia-admin-nonce', 'nonce' );

		// current user can authenticate.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( __( "You don't have permission to authenticate.", 'wc-pythia' ) ), 403 );
		}

		$response = $this->auth_api->maybe_authenticate();
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_messages(), $response->get_error_code() );
		} else {
			// body should have a json object
			// {
			// id: 'UUID',
			// redirect_to: 'URL'
			// }
			// $fake_response = [
			// 'body' => [
			// 'id' => wp_generate_uuid4(  ),
			// 'redirect_to' => '{URL}',
			// ],
			// 'code' => 200,
			// ];
			// $response = wp_json_encode($fake_response);
			// We need to validate response.
			wp_send_json_success( $response, 200 );
		}
		wp_die();
	}

	public function update_source_id() {

		// Check nonce.
		check_ajax_referer( 'pythia-admin-nonce', 'nonce' );

		// current user can authenticate.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( __( "You don't have permission to store source ids.", 'wc-pythia' ) ), 403 );
		}

		$source_id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : null;
		if ( ! empty( $source_id ) ) {
			$response = $this->auth_api->update_source_id( $source_id );
		} else {
			$response = new WP_Error( '400', __( 'Google Auth Source ID is empty. Please try again or contact support.', 'wc-pythia' ) );
		}

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_messages(), $response->get_error_code() );
		} elseif ( false === $response ) {
			wp_send_json_error( array( __( 'There was an error while storing google information. Please try again or contact support.', 'wc-pythia' ) ), 500 );
		} else {
			$return = array(
				'redirect_to' => isset( $_POST['redirect_to'] ) ? add_query_arg( 'wizard_step', intval( wc_pythia()->is_wizard_step() ), esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) ) : '',
				'source_id'   => $source_id,
			);
			wp_send_json_success( $return, 201 );
		}
		wp_die();
	}

	public function update_analytics_ua_account() {

		// Check nonce.
		check_ajax_referer( 'pythia-admin-nonce', 'nonce' );

		// current user can authenticate.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( __( "You don't have permission to store source ids.", 'wc-pythia' ) ), 403 );
		}

		$ga_ua_id   = isset( $_POST['ga_ua_id'] ) ? sanitize_text_field( wp_unslash( $_POST['ga_ua_id'] ) ) : null;
		$ga_ua_name = isset( $_POST['ga_ua_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ga_ua_name'] ) ) : null;
		if ( ! empty( $ga_ua_id ) ) {
			$this->auth_api->update_analytics_ua_id( $ga_ua_id );
			$this->auth_api->update_analytics_ua_name( $ga_ua_name );
			$response = $this->auth_api->store_analytics_source_id( $ga_ua_id );
		} else {
			$response = new WP_Error( '400', __( 'Google Analytics ID is empty. Please try again or contact support.', 'wc-pythia' ) );
		}

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_messages(), $response->get_error_code() );
		} elseif ( false === $response ) {
			wp_send_json_error( array( __( 'There was an error while saving google analytics information. Please try again or contact support.', 'wc-pythia' ) ), 500 );
		} else {
			if ( ! isset( $response->redirect_to ) && wc_pythia()->is_wizard_step() ) {
				$response->redirect_to = add_query_arg( 'wizard_step', intval( wc_pythia()->is_wizard_step() ), pythia_is_woocommerce_active() ? wc_pythia()->get_sync_url() : wc_pythia()->get_settings_url() );
			}
			wp_send_json_success( $response, 201 );
		}
		wp_die();
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function wc_pythia_google_auth_page() {
		$google_auth_page = add_submenu_page(
			$this->plugin_id,
			__( 'Analytics', 'wc-pythia' ),
			__( 'Analytics', 'wc-pythia' ),
			'manage_options',
			'wc_pythia_google_auth',
			array( $this, 'wc_pythia_google_auth_page_html' )
		);

		// load resources related to google auth page.
		add_action( 'load-' . $google_auth_page, array( $this, 'wc_pythia_google_auth_page_load' ) );
	}


	/**
	 * top level menu:
	 * callback functions
	 */
	public function wc_pythia_google_auth_page_load() {
		$screen = get_current_screen();
		/*
		* Check if current screen is My Admin Page
		* Don't add help tab if it's not
		*/
		if ( $screen->id != 'pythia_page_wc_pythia_google_auth' ) {
			return;
		}

		// Load style & scripts.

		$this->register_google_auth_scripts();
		$this->maybe_google_authorized();

	}

	/**
	 * Register Scripts for Google Analytics Auth Page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_google_auth_scripts() {

		// Register parent script.
		parent::register_scripts();

		wp_register_script( 'wc-pythia-google-auth', $this->plugin_admin_url . '/assets/js/wc-pythia-google-auth.js', array( 'jquery' ), WC_Pythia::version(), false );

		// Localize the script with new data.
		$settings = array(
			'pythia_admin_nonce'        => wp_create_nonce( 'pythia-admin-nonce' ),
			'source_id_updated_msg'     => __( 'Source ID updated successfully.', 'wc-pythia' ),
			// translators: %s: Google View ID.
			'ga_ua_id_updated_msg'      => __( 'Google Analytics View ID "%s" updated successfully. Now you can open Pythia Bot mobile application to see your Google Analytics stats.', 'wc-pythia' ),
			'source_id_updated_err_msg' => __( 'Error in Source ID response, please try again or contact support.', 'wc-pythia' ),
			'ga_ua_id_updated_err_msg'  => __( 'Error in Google GA response, please try again or contact support.', 'wc-pythia' ),
			'wizard_step'               => intval( wc_pythia()->is_wizard_step() ),
		);

		wp_localize_script( 'wc-pythia-google-auth', 'pythia_auth_settings', $settings );

		wp_enqueue_script( 'wc-pythia-google-auth' );
	}

	/**
	 * Check if it is a google autorization returned and update it
	 *
	 * @return void
	 */
	public function maybe_google_authorized() {
		if ( isset( $_GET['pythia_auth_success'] ) && 'true' === $_GET['pythia_auth_success'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->auth_api->update_google_authorization( 'yes' );
		}
	}


	/**
	 * Top level menu:
	 * callback functions
	 */
	public function wc_pythia_google_auth_page_html() {
		// check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$google_accounts = array();
		if ( $this->auth_api->google_authorized() && ( isset( $_GET['pythia_auth_success'] ) || ! $this->auth_api->get_analytics_ua_id() ) ) { //phpcs:ignore
			$google_accounts = $this->sort_google_accounts( $this->auth_api->get_google_analytics_accounts( $this->auth_api->get_source_id() ) );
		}
		$wc_pythia_google_auth_api = $this->auth_api;
		wc_pythia()->get_template(
			'google-auth-form',
			array(
				'wc_pythia_google_auth_api' => $wc_pythia_google_auth_api,
				'google_accounts'           => $google_accounts,
			)
		);
	}

	/**
	 * Order google accounts when they are returned from Google.
	 *
	 * @since 1.1.3
	 *
	 * @param Array $google_accounts Unsorted google accounts.
	 * @return Array
	 */
	public function sort_google_accounts( $google_accounts ) {
		$sorted_accounts = array();
		if ( ! is_wp_error( $google_accounts ) && $google_accounts ) {
			foreach ( $google_accounts as $account ) {
				if ( isset( $account->web_properties ) && is_array( $account->web_properties ) ) {
					foreach ( $account->web_properties as $web ) {
						foreach ( $web->profiles as $profile ) {
							$sorted_accounts[ $profile->id ] = sprintf( '%s - %s - %s', $account->name, $web->id, $profile->name );
						}
					}
				}
			}
		}
		asort( $sorted_accounts, SORT_STRING );
		return $sorted_accounts;
	}
}

new WC_Pythia_Admin_Google_Auth();
