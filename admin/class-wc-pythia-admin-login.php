<?php
/**
 * Pythia Login Page
 *
 * Form login to validate login credentials contacting Pythia Bot API. If login is succesfull redirects to select project page to associate a Pythia Project with the website.
 *
 * @package WC_Pythia\Admin
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;


/**
 * WC_Pythia_Admin_Login
 *
 * Class to manage Pythia Bot Login page.
 *
 * @since 1.1.2
 * @see WC_Pythia_Admin
 */
class WC_Pythia_Admin_Login extends WC_Pythia_Admin {

	/**
	 * Constructor class with admin initializer
	 *
	 * Submenu page added.
	 */
	public function __construct() {
		parent::__construct();
		// Don't display setup page if a token exists
		// This means that an account already exists.
		if ( ! wc_pythia()->is_setup() ) {
			/**
			 * Register our wc_pythia_login_init to the admin_init action hook.
			*/
			add_action( 'admin_init', array( $this, 'wc_pythia_login_init' ) );
			/**
			 * Register our wc_pythia_login_page to the admin_menu action hook
			 */
			add_action( 'admin_menu', array( $this, 'wc_pythia_login_page' ) );
		}
	}

	/**
	 * Login admin page initializer.
	 *
	 * Login ajax action added.
	 */
	public function wc_pythia_login_init() {
		add_action( 'wp_ajax_pythia_login', array( $this, 'login' ) );
	}

	/**
	 * Pythia login page added as second level menu page
	 *
	 * Submenu page added and the action to load resources for this page only.
	 */
	public function wc_pythia_login_page() {
		$login_page = add_submenu_page(
			$this->plugin_id,
			__( 'Login', 'wc-pythia' ),
			__( 'Login', 'wc-pythia' ),
			'manage_options',
			'wc_pythia_login',
			array( $this, 'wc_pythia_login_page_html' )
		);

		// load resources related to login page.
		add_action( 'load-' . $login_page, array( $this, 'wc_pythia_login_page_load' ) );
	}


	/**
	 * Top level menu:
	 * callback functions
	 */
	public function wc_pythia_login_page_load() {
		$screen = get_current_screen();

		/*
		* Check if current screen is My Admin Page
		* Don't add help tab if it's not
		*/
		if ( 'pythia_page_wc_pythia_login' !== $screen->id ) {
			return;
		}

		// Load style & scripts.

		$this->register_login_scripts();

	}

	/**
	 * Register login page styles, scripts and localize variables
	 *
	 * @return void
	 */
	public function register_login_scripts() {
		// Register parent script.
		parent::register_scripts();

		// Register the script.
		wp_register_script( 'wc-pythia-login', WC_PYTHIA__PLUGIN_URL . 'admin/assets/js/wc-pythia-login.js', array( 'wc-pythia-jquery-validation', 'wc-pythia' ), WC_Pythia::version(), false );

		$settings = array(
			'no_projects_err_msg'  => __( 'There are no projects related with your account.', 'woocommmerce-pythia' ),
			'login_successful_msg' => __( 'Login Successful.', 'woocommmerce-pythia' ),
			'projects_page'        => wc_pythia()->get_select_project_url(),
		);
		wp_localize_script( 'wc-pythia-login', 'pythia_auth_settings', $settings );

		wp_enqueue_script( 'wc-pythia-login' );
	}

	/**
	 * Login ajax method
	 *
	 * Sanitize email and password and make the request to the login API endpoint. If the login is successful, settings will be stored.
	 *
	 * @return void
	 */
	public function login() {
		check_ajax_referer( 'pythia-login-nonce' );

		$email    = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : null;
		$password = isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : null;

		$response = wc_pythia()->api->login( $email, $password );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_messages(), $response->get_error_code() );
		} else {
			$profile = $response['body'];
			if ( ! empty( $profile->api_token ) ){
				if ( $this->store_settings( $profile ) ) {
					wp_send_json_success( $profile, $response['code'] );
				} else {
					wp_send_json_error( array( __( 'Settings were not updated, please try again.', 'wc-pythia' ) ), 400 );
				}
			} else {
				wp_send_json_error( array( __( 'Something were wrong with your login, please contact Pythia Support Team.', 'wc-pythia' ) ), 400 );
			}
		}
		wp_die();
	}

	/**
	 * Format and Store settings returned after user login
	 *
	 * Setup notice will be removed after updating settings.
	 *
	 * @param object $profile {
	 *     Profile information to be stored.
	 *
	 *     @type string $profile_id Pythia Profile ID. Default 'null'.
	 *     @type string $email Pythia Profile email. Default 'null'.
	 *     @type string $first_name Pythia Profile first name. Default 'null'.
	 *     @type string $last_name Pythia Profile last name. Default 'null'.
	 *     @type string $api_token Pythia Profile api_token token. Default 'null'.
	 *     @type array  $projects Pythia Projects. Default 'array'.
	 * }
	 * @return boolean
	 */
	public function store_settings( $profile ) {
		// check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options           = wc_pythia()->settings->get_options();
		$submitted_options = array(
			'profile_id'         => isset( $profile->profile_id ) ? sanitize_text_field( wp_unslash( $profile->profile_id ) ) : null,
			'profile_email'      => isset( $profile->email ) ? sanitize_text_field( wp_unslash( $profile->email ) ) : null,
			'profile_first_name' => isset( $profile->first_name ) ? sanitize_text_field( wp_unslash( $profile->first_name ) ) : null,
			'profile_last_name'  => isset( $profile->last_name ) ? sanitize_text_field( wp_unslash( $profile->last_name ) ) : null,
			'profile_token'      => isset( $profile->api_token ) ? sanitize_text_field( wp_unslash( $profile->api_token ) ) : null,
			'projects'           => isset( $profile->projects ) ? $profile->projects : array(),
			'site_key'           => wc_pythia()->site_key(),
		);
		$new_options       = wp_parse_args( $submitted_options, $options );

		if ( $new_options && wc_pythia()->settings->update_options( $new_options ) ) {
			WC_Pythia::remove_setup_notice();
			return true;
		}
		return false;
	}

	/**
	 * Top level menu:
	 * callback functions
	 */
	public function wc_pythia_login_page_html() {
		// check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wc_pythia()->get_template( 'login-form' );
	}
}

new WC_Pythia_Admin_Login();
