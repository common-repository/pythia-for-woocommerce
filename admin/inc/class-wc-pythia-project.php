<?php
/**
 * Load assets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * WC_Pythia_Project Class.
 */
class WC_Pythia_Project {

	/**
	 * Pythia Projects
	 *
	 * @since 1.1.4
	 * @var array
	 */
	public $projects = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_pythia_save_project_settings', array( $this, 'save_project_settings' ) );
		add_action( 'wc_pythia_admin_notices', array( $this, 'wc_pythia_settings_notice' ) );

		add_action( 'do_projects_combobox', array( $this, 'projects_combobox' ) );
		$this->projects = (array) wc_pythia()->settings->get_projects();
	}

	/**
	 * Generate projects combobox.
	 */
	public function projects_combobox() {
		wc_pythia()->get_template( 'partials/projects-combobox', array( 'projects' => $this->projects ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function admin_enqueue_scripts() {
		// Register menu CSS style.
		wp_register_script( 'wc-pythia-projects', WC_PYTHIA__PLUGIN_URL . 'admin/assets/js/wc-pythia-project.js', array( 'jquery', 'wc-pythia', 'wc-pythia-jquery-validation' ), WC_Pythia::version(), false );
		// Localize the script with new data.
		$settings = array(
			'save_project_nonce'   => wp_create_nonce( 'pythia-project-nonce' ),
			'projects'             => $this->projects,
			'source_default'       => __( 'WordPress', 'wc-pythia' ), // Used when no Woocommerce source is found.
			'is_wocommerce_active' => pythia_is_woocommerce_active(),
			'redirect_to'          => pythia_is_woocommerce_active() ? null : wc_pythia()->get_settings_url(),
		);
		wp_localize_script( 'wc-pythia-projects', 'pythia_project_settings', $settings );

		// Enqueue menu CSS.
		wp_enqueue_script( 'wc-pythia-projects' );
	}

	/**
	 * Save Project Settings
	 *
	 * @return void
	 */
	public function save_project_settings() {
		check_ajax_referer( 'pythia-project-nonce' );

		$options      = wc_pythia()->settings->get_options();
		$project_id   = isset( $_POST['project_id'] ) ? sanitize_text_field( wp_unslash( $_POST['project_id'] ) ) : null;
		$project_name = isset( $_POST['project_name'] ) ? sanitize_text_field( wp_unslash( $_POST['project_name'] ) ) : null;
		$source_id    = isset( $_POST['source_id'] ) ? sanitize_text_field( wp_unslash( $_POST['source_id'] ) ) : null;
		$source_name  = isset( $_POST['source_name'] ) ? sanitize_text_field( wp_unslash( $_POST['source_name'] ) ) : null;
		$redirect_to  = ! empty( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : add_query_arg( 'pythia-project-updated', true, wc_pythia()->get_sync_url() );

		$submitted_options = array(
			'project_id'   => $project_id,
			'project_name' => $project_name,
			'source_id'    => $source_id,
			'source_name'  => $source_name,
		);
		$new_options       = wp_parse_args( $submitted_options, $options );
		if ( $new_options && wc_pythia()->settings->update_options( $new_options ) ) {
			if ( $redirect_to ) {
				wp_send_json_success( array( 'redirect_to' => $redirect_to ) );
			} else {
				wp_send_json_success( array( 'message' => __( 'Project saved successfully.', 'wc-pythia' ) ) );
			}
		} else {
			wp_send_json_error( array( __( 'Project was not saved, please try again.', 'wc-pythia' ) ), 400 );
		}
		wp_die();
	}

	/**
	 * Print admin notices when settings update is successful or if failed
	 *
	 * @since 1.1.2
	 * @return void
	 */
	public function wc_pythia_settings_notice() {
		if ( array_key_exists( 'pythia-settings-updated-login', $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			if ( 'true' === $_GET['pythia-settings-updated-login'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				WC_Pythia_Notices::print_success_notice( __( 'You were logged in and your settings were updated succesfully.', 'wc-pythia' ) );
			} elseif ( 'false' === $_GET['pythia-settings-updated-login'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				WC_Pythia_Notices::print_error_notice( __( 'You were logged in but your settings were not updated. Please try resettting Settings and login again, if the proble persist please contact Pythia Bot support.', 'wc-pythia' ) );
			}
		}
	}
}
