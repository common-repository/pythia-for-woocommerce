<?php
/**
 * Pythia for Woocommerce Settings Class
 *
 * Class to manage plugin settings.
 *
 * @package WC_Pythia\WC_Pythia_Settings
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;


/**
 * Pythia for Woocommerce Settings Class
 *
 * Class to manage plugin settings.
 * 'profile_email'
 * 'profile_first_name'
 * 'profile_last_name'
 * 'profile_token'
 * 'project_id'
 * 'source_id'
 * 'site_key'
 * 'debug'
 * 'orders_per_page'
 * 'google_auth_source_id'
 * 'google_authorized'
 * 'google_ua_id'
 * 'google_ua_name'
 * 'source_name'
 * 'project_name'
 * 'projects'
 *
 * @since 1.1.4 Added the $projects, $project_name, $source_name and $google_ua_name
 * @since 1.0.0
 */
class WC_Pythia_Settings {
	protected $options_key;
	protected $options;

	public function __construct() {
		// Get the value of the setting we've registered with register_setting().
		$this->options_key = WC_Pythia::PLUGIN_ID . '_options';
		$this->options     = get_option( $this->options_key );

		// Remove schedules when settings are reset.
		add_action( 'wc_pythia_settings_reset', array( $this, 'reset_settings' ) );
	}

	public function reset_settings() {
		WC_Pythia_Sync_Scheduler::cancel_schedule( null );
	}

	/**
	 * Get Options stored in the database
	 *
	 * @since 1.1.4 Added the $projects, $project_name, $source_name and $google_ua_name
	 * @since 1.0.0
	 * @return array {
	 *     Array of settings.
	 *
	 *     @type String $profile_id Profile ID. Default ''.
	 *     @type String $profile_email Profile Email. Default ''.
	 *     @type String $profile_first_name Profile First Name. Default ''.
	 *     @type String $profile_last_name Profile Last Name. Default ''.
	 *     @type String $profile_token Profile Token without expiration, this is different than login expiration. Default ''.
	 *     @type String $project_id Project ID associated to this website. Default ''.
	 *     @type String $source_id Project Source ID associated to this website, WooCommerce source ID used by default. Default ''.
	 *     @type String $site_key Key generated in the plugin using wc_pythia()->site_key() method. Default ''.
	 *     @type String $debug Flag to enable debug. Default ''. Values 'yes', 'no'.
	 *     @type String $orders_per_page Number of orders to be processed per each sync process. Default '50'.
	 *     @type String $google_auth_source_id Google Analytics Source ID. Default ''.
	 *     @type String $google_authorized Flag to identify if Google account is authorized. Default ''.
	 *     @type String $google_ua_id Google Analytics View ID selected from available views in Google Analytics account. Default ''.
	 *     @type String $google_ua_name Google Analytics View Name. Default ''.
	 *     @type String $source_name Pythia API source Name. Default ''.
	 *     @type String $project_name Pythia Project Name. Default ''.
	 *     @type Array  $projects List of projects from Pythia API. Default array().
	 * }
	 */
	public function get_options() {
		return $this->options ? $this->options : array();
	}

	public function update_options( $options ) {
		// TODO: Escape all options values and remove single option escape, check what happens with arrays as values.
		$updated = update_option( $this->options_key, $options );
		if ( $updated ) {
			$this->options = $options;
			do_action( 'wc_pythia_settings_updated' );
		}
		return $updated;
	}

	public function update_option( $key, $value ) {
		$this->options[ $key ] = sanitize_text_field( $value );
		return $this->update_options( $this->options );
	}

	public function get_option( $option ) {
		return isset( $this->options[ $option ] ) ? $this->options[ $option ] : '';
	}

	public function get_token() {
		return $this->get_option( 'profile_token' );
	}

	public function get_project_id() {
		return $this->get_option( 'project_id' );
	}

	public function get_project_name() {
		return $this->get_option( 'project_name' );
	}

	public function get_environemt() {
		return $this->get_option( 'environment' );
	}

	public function debug_enabled() {
		return $this->get_option( 'debug' ) === 'yes';
	}

	public function get_source_id() {
		return $this->get_option( 'source_id' );
	}

	public function get_source_name() {
		return $this->get_option( 'source_name' );
	}

	public function get_projects() {
		$projects = $this->get_option( 'projects' );
		return $projects ? $projects : array();
	}

	/**
	 * Return Site Key Setting stored in the database
	 *
	 * Site key stored is generated in WC_Pythia class using wp site_url() method.
	 *
	 * @since 1.1.2
	 * @return string
	 */
	public function get_site_key() {
		return $this->get_option( 'site_key' );
	}

	/**
	 * Gets the number of orders that should be processed every sync process run
	 *
	 * @since 1.1.2
	 * @return integer The number of orders to be processed.
	 */
	public function get_orders_per_page( $default = null ) {
		$default        = is_null( $default ) ? WC_Pythia_Synchronizer::ORDERS_PER_PAGE : $default;
		$order_per_page = $this->get_option( 'orders_per_page' );
		return absint( $order_per_page ? $order_per_page : $default );
	}

	private function remove_all_options() {
		return delete_option( $this->options_key );
	}
}
