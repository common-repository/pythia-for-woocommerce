<?php
/**
 * Analytics Google Client class.
 *
 * Allow Connecting Muse with GA
 *
 * @since 1.0.0
 *
 * @package WC_Pythia\WC_Pythia_Google_Auth_Api
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WC_Pythia_Google_Auth_Api {

	const SOURCE_ID_OPTION_KEY = 'google_auth_source_id';

	const AUTHORIZED_OPTION_KEY = 'google_authorized';

	const UA_ID_OPTION_KEY = 'google_ua_id';

	const UA_NAME_OPTION_KEY = 'google_ua_name';

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0.2
	 */
	public function __construct() {}

	public function get_authenticate_params() {
		$site_id    = $this->get_site_id();
		$project_id = wc_pythia()->settings->get_project_id();

		$callback_url = add_query_arg(
			array(
				'project_id'          => $project_id,
				'site_id'             => $site_id,
				'pythia_version'      => WC_PYTHIA_VERSION,
				'pythia_auth_success' => 'true',
				'wizard_step'         => intval( wc_pythia()->is_wizard_step() ),
			),
			admin_url( 'admin.php?page=wc_pythia_google_auth' )
		);
		$callback_url = apply_filters( 'wc_pythia_callback_url', $callback_url );
		$auth_params  = array(
			'project_id'   => $project_id,
			'callback_url' => $callback_url,
		);
		$auth_params  = apply_filters( 'wc_pythia_auth_params', $auth_params );
		return $auth_params;
	}

	public function maybe_authenticate() {
		$response = wc_pythia()->api->post( $this->get_authenticate_params(), 'providers/google' );
		return $response;
	}

	public function update_source_id( $source_id ) {
		$response = wc_pythia()->settings->update_option( self::SOURCE_ID_OPTION_KEY, $source_id );
		return $response;
	}

	public function get_source_id() {
		$response = wc_pythia()->settings->get_option( self::SOURCE_ID_OPTION_KEY );
		return $response;
	}

	/**
	 * Update google authorization result to yes or now
	 *
	 * @param string $authorized
	 * @return void
	 */
	public function update_google_authorization( $authorized ) {
		$response = wc_pythia()->settings->update_option( self::AUTHORIZED_OPTION_KEY, $authorized );
		return $response;
	}

	public function google_authorized() {
		return wc_pythia()->settings->get_option( self::AUTHORIZED_OPTION_KEY ) === 'yes';
	}

	/**
	 * Update google analytic UA ID
	 *
	 * @param string $ua_id Google UA ID.
	 * @return bool
	 */
	public function update_analytics_ua_id( $ua_id ) {
		$response = wc_pythia()->settings->update_option( self::UA_ID_OPTION_KEY, $ua_id );
		return $response;
	}

	/**
	 * Return Google Analytic UA ID
	 *
	 * @since 1.1.3
	 * @return string
	 */
	public function get_analytics_ua_id() {
		return wc_pythia()->settings->get_option( self::UA_ID_OPTION_KEY );
	}

	/**
	 * Update google analytic UA Name
	 *
	 * @since 1.1.4
	 * @param string $ua_name Google UA Name.
	 * @return bool
	 */
	public function update_analytics_ua_name( $ua_name ) {
		$response = wc_pythia()->settings->update_option( self::UA_NAME_OPTION_KEY, $ua_name );
		return $response;
	}

	/**
	 * Return Google Analytic UA Name
	 *
	 * @since 1.1.4
	 * @return string
	 */
	public function get_analytics_ua_name() {
		return wc_pythia()->settings->get_option( self::UA_NAME_OPTION_KEY );
	}

	/**
	 * Call Pythia API to store source id related with Google Analytic UA ID
	 *
	 * @since 1.1.3
	 *
	 * @param string $ga_ua_profile_id Google Analytic UA project ID.
	 * @return bool
	 */
	public function store_analytics_source_id( $ga_ua_profile_id ) {
		$source_id = $this->get_source_id();
		$response  = wc_pythia()->api->put( "sources/{$source_id}", array( 'profile_id' => $ga_ua_profile_id ) );
		return $response;
	}

	public function get_site_id() {
		$auth_key        = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$secure_auth_key = defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : '';
		$logged_in_key   = defined( 'LOGGED_IN_KEY' ) ? LOGGED_IN_KEY : '';

		$site_id = $auth_key . $secure_auth_key . $logged_in_key;
		$site_id = preg_replace( '/[^a-zA-Z0-9]/', '', $site_id );
		$site_id = sanitize_text_field( $site_id );
		$site_id = trim( $site_id );
		$site_id = ( strlen( $site_id ) > 30 ) ? substr( $site_id, 0, 30 ) : $site_id;
		return $site_id;
	}

	public function get_google_analytics_accounts( $source_id ) {
		$accounts          = array();
		$response_accounts = wc_pythia()->api->get( 'providers/google/accounts', array( 'source_id' => $source_id ) );

		if ( is_wp_error( $response_accounts ) ) {
			return $response_accounts;
		}

		if ( $response_accounts && isset( $response_accounts->accounts ) && is_array( $response_accounts->accounts ) ) {
			$accounts = $response_accounts->accounts;
		} else {
			$accounts = new WP_Error( 400, __( 'No google analytics accounts as part of the response.', 'wc-pythia' ) );
		}

		return $accounts;
	}

}
