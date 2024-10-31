<?php
/**
 * WC_Pythia_Admin_Setup class
 */
class WC_Pythia_Admin_Setup  extends WC_Pythia_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		// Don't display setup page if a token exists
		// this means that an account already exists.
		if ( ! wc_pythia()->is_setup() ) {
			/**
			 * Register our wc_pythia_settings_init to the admin_init action hook
			*/
			add_action( 'admin_init', array( $this, 'wc_pythia_settings_init' ) );
			/**
			 * Register our wc_pythia_options_page to the admin_menu action hook
			 */
			add_action( 'admin_menu', array( $this, 'wc_pythia_options_page' ) );
		}
	}

	/**
	 * Init settings
	 *
	 * @since 1.1.3
	 * @return void
	 */
	public function wc_pythia_settings_init() {
		add_action( 'wp_ajax_pythia_sign_up', array( $this, 'sign_up' ) );
		add_action( 'wp_ajax_pythia_store_settings', array( $this, 'store_settings' ) );
	}

	/**
	 * top level menu
	 */
	public function wc_pythia_options_page() {
		$setup_page = add_submenu_page(
			$this->plugin_id,
			__( 'Setup', 'wc-pythia' ),
			__( 'Setup', 'wc-pythia' ),
			'manage_options',
			'wc_pythia_setup',
			array( $this, 'wc_pythia_setup_page_html' )
		);

		// load resources related to setup page.
		add_action( 'load-' . $setup_page, array( $this, 'wc_pythia_setup_page_load' ) );
	}

	/**
	 * Top level menu:
	 * callback public functions
	 */
	public function wc_pythia_setup_page_load() {
		$screen = get_current_screen();

		/*
		* Check if current screen is My Admin Page
		* Don't add help tab if it's not
		*/
		if ( 'pythia_page_wc_pythia_setup' !== $screen->id ) {
			return;
		}

		// Load style & scripts.

		$this->register_setup_scripts();

	}

	/**
	 * Register and enqueue styles and scripts
	 *
	 * @since 1.1.4
	 * @return void
	 */
	public function register_setup_scripts() {

		// Register parent script.
		parent::register_scripts();
		wp_register_script( 'wc-pythia-setup', WC_PYTHIA__PLUGIN_URL . 'admin/assets/js/wc-pythia-setup.js', array( 'wc-pythia', 'wc-pythia-jquery-validation', 'password-strength-meter' ), WC_Pythia::version(), false );

		// Localize the script with new data.
		$settings = array(
			'sign_up_nonce'        => wp_create_nonce( 'pythia-sign-up-nonce' ),
			'store_settings_nonce' => wp_create_nonce( 'pythia-store-settings-nonce' ),
		);
		wp_localize_script( 'wc-pythia-setup', 'pythia_setup_settings', $settings );
		wp_enqueue_script( 'wc-pythia-setup' );
	}

	/**
	 * Ajax method to register a user in Pythia
	 *
	 * Call signup pythia API to register a new user.
	 *
	 * @since 1.1.3
	 * @return void
	 */
	public function sign_up() {
		check_ajax_referer( 'pythia-sign-up-nonce' );

		$params = array(
			'email'        => isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '',
			'first_name'   => isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '',
			'last_name'    => isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '',
			'password'     => isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '',
			'project_name' => isset( $_POST['project_name'] ) ? sanitize_text_field( wp_unslash( $_POST['project_name'] ) ) : '',
			'site_url'     => site_url(),
			'source_type'  => 'google-analytics',
		);

		if ( pythia_is_woocommerce_active() ) {
			$params['source_type'] = 'woocommerce';
		}

		$response = wc_pythia()->api->sign_up( $params );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_messages(), $response->get_error_code() );
		} else {
			wp_send_json_success( $response['body'], $response['code'] );
		}
		wp_die();
	}

	/**
	 * Store settings information
	 *
	 * Store user information after the user is registered in Pythia.
	 *
	 * @since 1.1.4
	 * @return void
	 */
	public function store_settings() {
		check_ajax_referer( 'pythia-store-settings-nonce' );
		// check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$source_default_name = pythia_is_woocommerce_active() ? 'WooCoommerce' : 'WordPress';
		$options             = wc_pythia()->settings->get_options();
		$submitted_options   = array(
			'profile_email'      => isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : null,
			'profile_first_name' => isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : null,
			'profile_last_name'  => isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : null,
			'profile_token'      => isset( $_POST['api_token'] ) ? sanitize_text_field( wp_unslash( $_POST['api_token'] ) ) : null,
			'project_id'         => isset( $_POST['project_id'] ) ? sanitize_text_field( wp_unslash( $_POST['project_id'] ) ) : null,
			'project_name'       => isset( $_POST['project_name'] ) ? sanitize_text_field( wp_unslash( $_POST['project_name'] ) ) : null,
			'source_id'          => isset( $_POST['source_id'] ) ? sanitize_text_field( wp_unslash( $_POST['source_id'] ) ) : null,
			'source_name'        => isset( $_POST['source_name'] ) ? sanitize_text_field( wp_unslash( $_POST['source_name'] ) ) : $source_default_name,
			'site_key'           => wc_pythia()->site_key(),
		);
		$new_options         = wp_parse_args( $submitted_options, $options );

		if ( $new_options && wc_pythia()->settings->update_options( $new_options ) ) {
			WC_Pythia::remove_setup_notice();
			wp_send_json_success(
				array(
					'source_token' => $new_options['profile_token'],
					'redirect_to'  => add_query_arg( 'wizard_step', 1, wc_pythia()->get_ga_url() ),
					'message'      => __(
						'Settings Updated.',
						'wc-pythia'
					),
				),
				200
			);
		} else {
			wp_send_json_error( array( __( 'Settings were not updated.', 'wc-pythia' ) ), 500 );
		}
		wp_die();
	}

	/**
	 * Top level menu:
	 * callback public functions
	 */
	public function wc_pythia_setup_page_html() {
		// check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wc_pythia()->get_template( 'setup-form', array( 'current_user' => wp_get_current_user() ) );
	}
}

new WC_Pythia_Admin_Setup();
