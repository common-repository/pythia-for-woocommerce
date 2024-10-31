<?php
defined( 'ABSPATH' ) || exit;

class WC_Pythia_Api {


	/**
	 * Singleton instance of this class.
	 *
	 * @since 1.1.3
	 * @var WC_PWC_Pythia_Apiythia
	 */
	protected static $instance;

	/**
	 * Pythia Bot API Url.
	 *
	 * @since 1.1.3
	 * @var string
	 */
	protected $api_url = 'https://api.pythiabot.com/v1/';


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0.0
	 * @return \WC_Pythia_Api
	 */
	public function __construct() {
		if ( defined( 'WC_PYTHIA_API_URL' ) && WC_PYTHIA_API_URL !== $this->api_url ) {
			$this->api_url = WC_PYTHIA_API_URL;
		}
	}

	/**
	 * Main Pythia Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.0.0
	 * @see wc_pythia()
	 * @return \WC_Pythia_Api
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_end_point_url( $end_point = '' ) {
		$url = trailingslashit( $this->api_url . $end_point );
		return $url;
	}

	protected function remote_post( $url, $args ) {
		$response = wp_remote_post( esc_url_raw( $url ), $args );

		if ( wc_pythia()->debug_api() ) {
			$log = array(
				'endpoint' => $url,
				'args'     => $args,
				'response' => $response,
			);
			wc_pythia()->api_log( $log );
		}

		// If there is a wp error when calling the API generate a default error to display.
		if ( is_wp_error( $response ) ) {
			wc_pythia()->debugging_log( $response->get_error_message() );
			$response = new WP_Error( 503, __( 'Service Unavailable', 'wc-pythia' ) );
		}
		return $response;
	}

	protected function remote_request( $url, $args ) {
		$response = wp_remote_request( esc_url_raw( $url ), $args );

		if ( wc_pythia()->debug_api() ) {
			$log = array(
				'endpoint' => $url,
				'args'     => $args,
				'response' => $response,
			);
			wc_pythia()->api_log( $log );
		}

		// If there is a wp error when calling the API generate a default error to display.
		if ( is_wp_error( $response ) ) {
			wc_pythia()->debugging_log( $response->get_error_message() );
			$response = new WP_Error( 503, __( 'Service Unavailable', 'wc-pythia' ) );
		}
		return $response;
	}

	protected function remote_get( $url, $args, $params_string ) {
		$response = wp_remote_get( esc_url_raw( $url ) . '?' . $params_string, $args );

		if ( wc_pythia()->debug_api() ) {
			$log = array(
				'endpoint'     => $url,
				'query_string' => $params_string,
				'args'         => $args,
				'response'     => $response,
			);
			wc_pythia()->api_log( $log );
		}

		// If there is a wp error when calling the API generate a default error to display.
		if ( is_wp_error( $response ) ) {
			wc_pythia()->debugging_log( $response->get_error_message() );
			$response = new WP_Error( 503, __( 'Service Unavailable', 'wc-pythia' ) );
		}
		return $response;
	}

	/**
	 * Initialize statuses
	 *
	 * @since 1.0.0
	 */
	public function sign_up( $body_params = array() ) {
		$url  = $this->get_end_point_url( 'sign-up' );
		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
			'body'    => wp_json_encode( $body_params ),
		);

		// Make API request.
		$response = $this->remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $this->format_response( $response, true );
	}

	/**
	 * Initialize statuses
	 *
	 * @since 1.0.0
	 */
	public function login( $email, $password ) {
		$url  = $this->get_end_point_url( 'sessions' );
		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'email'    => sanitize_email( $email ),
					'password' => sanitize_text_field( $password ),
				)
			),
		);

		// Make API request.
		$response = $this->remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $this->format_response( $response, true );

	}

	/**
	 * Post Single event to the API
	 *
	 * @param array $body_params Request Body.
	 * @return array|WP_Error return API response or WP_Error.
	 */
	public function post_single_event( $name, $data = array() ) {
		$body_params = array(
			'name'      => $name,
			'source_id' => wc_pythia()->settings->get_source_id(),
			'data'      => $data,
		);
		return $this->post( array( 'events' => array( $body_params ) ), 'events' );
	}

	/**
	 * Post events to the API
	 *
	 * @param array $body_params Request Body.
	 * @return array|WP_Error return API response or WP_Error.
	 */
	public function post_events( $body_params = array() ) {
		return $this->post( array( 'events' => $body_params ), 'events' );
	}

	public function post( $body_params = array(), $end_point = 'events' ) {
		$url   = $this->get_end_point_url( $end_point );
		$token = wc_pythia()->settings->get_token();
		$args  = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
				'Authorization' => "Bearer {$token}",
			),
			'body'    => wp_json_encode( $body_params ),
		);

		// Make API request.
		$response = $this->remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Return the response code.
		return $this->format_response( $response );
	}

	public function put( $end_point, $body_params = array() ) {
		$url   = $this->get_end_point_url( $end_point );
		$token = wc_pythia()->settings->get_token();
		$args  = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
				'Authorization' => "Bearer {$token}",
			),
			'method'  => 'PUT',
			'body'    => wp_json_encode( $body_params ),
		);

		// Make API request.
		$response = $this->remote_request( $url, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Return the response code.
		return $this->format_response( $response );
	}

	public function get( $end_point, $body_params = array() ) {
		$params_string = http_build_query( $body_params, '', '&' );
		$url           = $this->get_end_point_url( $end_point );
		$token         = wc_pythia()->settings->get_token();
		$args          = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
				'Authorization' => "Bearer {$token}",
			),
		);

		// Make API request.
		$response = $this->remote_get( $url, $args, $params_string );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Return the response code.
		return $this->format_response( $response );
	}

	public function format_response( $api_response, $return_code = false ) {
		if ( is_wp_error( $api_response ) ) {
			return $api_response;
		}

		if ( empty( $api_response ) ) {
			return new WP_Error( 500, __( 'Unknown error occurred. Empty API response.', 'wc-pythia' ) );
		}

		// Check the response code.
		$response_code    = wp_remote_retrieve_response_code( $api_response );
		$response_message = wp_remote_retrieve_response_message( $api_response );
		$response_body    = wp_remote_retrieve_body( $api_response );
		$response_body    = $this->is_json( $response_body ) ? json_decode( $response_body ) : $response_body;

		switch ( $response_code ) {
			case 201:
			case 200:
				if ( $return_code ) {
					return array(
						'code' => $response_code,
						'body' => $response_body,
					);
				} else {
					return $response_body;
				}
			default:
				return $this->format_error( $response_code, $response_message, $response_body );
		}
	}

	public function format_error( $response_code, $response_message, $response_body = null ) {
		if ( ! empty( $response_body ) ) {
			if ( isset( $response_body->error ) ) {
				$response_message = $response_body->error;
			} elseif ( isset( $response_body->errors ) ) {
				if ( is_array( $response_body->errors ) ) {
					$response_message = $response_body->errors[0];
				} elseif ( ! empty( $response_body->errors->detail ) ) {
					$response_message = $response_body->errors->detail;
				} else {
					$response_message = $response_body->errors;
				}
			}
		}

		return new WP_Error( $response_code, empty( $response_message ) ? __( 'Unknown error occurred.', 'wc-pythia' ) : $response_message );
	}

	public function is_json( $string ) {
		return is_string( $string ) && is_array( json_decode( $string, true ) ) && ( JSON_ERROR_NONE === json_last_error() ) ? true : false;
	}
} // end \WC_Pythia class
