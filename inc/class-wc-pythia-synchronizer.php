<?php

/**
 * Undocumented class
 *
 * Description.
 *
 * @since 1.0.0
 * @see My_Parent_Class
 */
class WC_Pythia_Synchronizer {

	/**
	 *  Singleton instance for this class.
	 *
	 * @var WC_Pythia_Synchronizer $instance
	 */
	protected static $instance;

	const SYNC_META_KEY                  = '_pythia_order_synchronized';
	const SYNC_OPTION_KEY                = '_pythia_last_order_synchronized';
	const SYNC_OPTION_KEY_LAST_TIME_SYNC = '_pythia_last_time_sync';
	const ORDERS_PER_PAGE                = 50;

	/**
	 * Initialize the plugin.
	 *
	 * @since  1.0.0
	 * @return WC_Pythia
	 */
	public function __construct() {
		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( $this, 'handle_custom_query_var' ), 10, 2 );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Main Pythia Instance, ensures only one instance is/can be loaded
	 *
	 * @since  1.0.0
	 * @see    wc_pythia()
	 * @return \WC_Pythia
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Pythia initialize frontend and backend needs.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// This action is needed in front end when a customer purchase an order.
		add_action( 'woocommerce_order_status_changed', array( $this, 'wc_order_status_changed' ), 10, 4 );

		// This action is needed in front end to have it available when a user visits the site.
		add_action( WC_Pythia_Sync_Scheduler::SCHEDULE_HOOK, array( $this, 'sync_orders' ) );

		if ( $this->is_sync_disabled() && null !== WC_Pythia_Sync_Scheduler::schedule_next_run( null ) ) {
			// If Synchronization is disabled, cancel all Synchronization schedules.
			WC_Pythia_Sync_Scheduler::cancel_schedule( null );
		}
	}

	/**
	 * Initialize admin
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {
		add_action( 'wc_pythia_settings_reset', array( $this, 'reset_sync_flags' ) );
		add_action( 'wc_pythia_reset_sync_flags', array( $this, 'reset_sync_flags' ) );
	}

	/**
	 * Handle a custom query var to get orders with the custom var meta.
	 *
	 * @param  array $query      - Args for WP_Query.
	 * @param  array $query_vars - Query vars from WC_Order_Query.
	 * @return array modified $query
	 */
	public function handle_custom_query_var( $query, $query_vars ) {
		if ( ! empty( $query_vars[ self::SYNC_META_KEY ] ) ) {
			$query['meta_query'][] = array(
				'key'     => self::SYNC_META_KEY,
				'compare' => esc_attr( $query_vars[ self::SYNC_META_KEY ] ),
			);
		}
		return $query;
	}

	/**
	 * Determine if Pythia Bot synchronization is allowed.
	 *
	 * Return a comparison result between actual site key and the site_key stored in the database when the user was logged in or when a new account was registered.
	 *
	 * @return boolean
	 */
	public function is_sync_enabled() {
		$is_disabled = wc_pythia()->site_key() === wc_pythia()->settings->get_site_key();
		return boolval( apply_filters( 'wc_pythia_sync_diabled', $is_disabled ) );
	}

	/**
	 * Determine if Pythia Bot synchronization is NOT allowed.
	 *
	 * @since 1.1.2
	 * @return boolean
	 */
	public function is_sync_disabled() {
		return ! $this->is_sync_enabled();
	}

	/**
	 * Track order when order status changed
	 *
	 * @param  string|integer $order_id    Order Id.
	 * @param  string         $status_from Status From.
	 * @param  string         $status_to   Status To.
	 * @param  WC_Order       $order       Order Object.
	 * @return void
	 */
	public function wc_order_status_changed( $order_id, $status_from, $status_to, $order ) {
		// If the status is the same don't do anything.
		if ( $status_from === $status_to ) {
			return;
		}
		// translators: Order ID, status from and status to.
		wc_pythia()->debugging_log( sprintf( __( 'Order with ID "%1$1s" has a status change from "%2$2s" to "%3$3s" ', 'wc-pythia' ), $order_id, $status_from, $status_to ) );
		$micro_time = (int) microtime( true );
		try {
			// The process will run in 1 minute.
			WC_Pythia_Sync_Scheduler::schedule_single( $micro_time + 60, array( 'order_id' => $order_id ) );
			wc_pythia()->api->post_single_event( 'Sync Process Scheduled', array( 'total_number_of_orders' => $this->orders_not_sync_count() ) );
		} catch ( RuntimeException $e ) {
			wc_pythia()->error_log( $e->getMessage() );
		}
	}

	/**
	 * Undocumented function
	 *
	 * Description.
	 *
	 * @since 1.1.2
	 *
	 * @param WC_Order $order Woocommerce order to be synchronized.
	 * @return boolean
	 */
	public function track_order( $order ) {
		if ( $this->is_sync_disabled() ) {
			return new WP_Error( 400, __( 'Pythia Bot API synchronization is disabled. Check your settings, if all is good there please contact Pythia Bot Support Team.', 'wc-pythia' ) );
		}

		if ( empty( $order ) || ! is_a( $order, 'WC_Order' ) || ! wc_is_order_status( sprintf( 'wc-%s', $order->get_status() ) ) ) {
			return new WP_Error( 400, __( 'Synchronization Failed. Empty order or Invalid order status.', 'wc-pythia' ) );
		}

		$response = $this->track( array( $this->get_order_params( $order ) ) );
		if ( ! is_wp_error( $response ) ) {
			// if order track was succesfull.
			$this->set_order_as_tracked( $order );
			return true;
		}
		return $response;
	}

	public function track_orders_batch( $orders ) {
		if ( $this->is_sync_disabled() ) {
			return new WP_Error( 400, __( 'Pythia Bot API synchronization is disabled. Check your settings, if all is good there please contact Pythia Bot Support Team.', 'wc-pythia' ) );
		}

		if ( empty( $orders ) ) {
			return new WP_Error( 400, __( 'Synchronization Failed. Empty orders array.', 'wc-pythia' ) );
		}

		if ( ! is_array( $orders ) && is_a( $orders, 'WC_Order' ) ) {
			$orders = array( $orders );
		}

		$formatted_orders = array();
		foreach ( $orders as $order ) {
			$formatted_orders[] = $this->get_order_params( $order );
		}

		// TODO: return ids tracked succesfully in the database
		$response = $this->track( $formatted_orders );
		if ( ! is_wp_error( $response ) ) {
			foreach ( $formatted_orders as $order_tracked ) {
				$order_id = $order_tracked['data']['external_id'];
				// if order track was succesfull.
				$this->set_order_as_tracked( $order_id );
			}
			return true;
		}
		return $response;
	}

	public function get_order_params( $order ) {
		// var_dump($order->get_taxes());
		// var_dump($order->get_shipping_methods());
		// var_dump($order->get_items( 'coupon' ));
		// TODO: move this under a pythiaOrder class.
		$body_params = array(
			'name'      => 'Order',
			'source_id' => wc_pythia()->settings->get_source_id(),
			'data'      => array(
				'external_id'    => (string) $order->get_id(),
				'id'             => (string) $order->get_order_number(),
				'created_at'     => wc_format_datetime( $order->get_date_created(), 'Y-m-d h:i:s' ),
				'state'          => $order->get_status(),
				'subtotal'       => $order->get_subtotal(),
				'total'          => $order->get_total(),
				'total_shipping' => $order->get_shipping_total(), // check if shipping tax is included
				'total_discount' => $order->get_total_discount(),
				'total_tax'      => $order->get_total_tax(),
				'total_fee'      => $this->get_total_fees( $order ),
				'coupon_codes'   => $order->get_coupon_codes(),
				'customer'       => $this->get_customer_params( $order ),
				'line_items'     => $this->get_line_items_params( $order ),
			),
		);
		return $body_params;
	}

	public function get_customer_params( $order ) {
		return array(
			'id'         => $order->get_customer_id(),
			'created_at' => $order->get_customer_id() && $order->get_user()->get_date_created() ? wc_format_datetime( $order->get_user()->get_date_created(), 'Y-m-d h:i:s' ) : null,
			'first_name' => $order->get_billing_first_name(),
			'last_name'  => $order->get_billing_last_name(),
			'email'      => $order->get_billing_email(),
			'company'    => $order->get_billing_company(),
			'phone'      => $order->get_billing_phone(),
			'address'    => array(
				'address_1' => $order->get_billing_address_1(),
				'address_2' => $order->get_billing_address_2(),
				'city'      => $order->get_billing_city(),
				'state'     => $order->get_billing_state(),
				'country'   => $order->get_billing_country(),
				'postcode'  => $order->get_billing_postcode(),
			),
		);
	}

	public function get_line_items_params( $order ) {
		$items = array();
		foreach ( $order->get_items() as $key => $line_item ) {
			$item_product = $line_item->get_product();
			$items[]      = array(
				'id'           => $line_item->get_id(),
				'product_id'   => $line_item->get_product_id(), // if it is a variable product will return variation id
				'quantity'     => $line_item->get_quantity(),
				'name'         => $line_item->get_name(),
				'sku'          => $item_product ? $line_item->get_product()->get_sku() : 'N/A',
				'price'        => $line_item->get_quantity() > 0 ? $order->get_item_total( $line_item, false, true ) / $line_item->get_quantity() : 1,
				'total_amount' => $order->get_item_total( $line_item, false, true ),
			);
		}
		return $items;
	}

	public function get_total_fees( $order ) {
		$total_fee = 0;
		foreach ( $order->get_fees() as $fee ) {
			$total_fee += $fee->get_total();
		}

		return $total_fee;
	}

	public function get_orders_per_page() {
		return wc_pythia()->settings->get_orders_per_page( self::ORDERS_PER_PAGE );
	}

	/**
	 * Undocumented function
	 *
	 * Description.
	 *
	 * @since 1.1.3
	 *
	 * @param [type] $body_params
	 * @return void
	 */
	public function track( $body_params ) {
		// Do not continue with the sync if it is disabled.
		if ( $this->is_sync_disabled() ) {
			wc_pythia()->log( 'Synchronization was not completed because it is disabled.' );
			return new WP_Error( 400, __( 'Pythia Bot API synchronization is disabled. Check your settings, if all is good there please contact Pythia Bot Support Team.', 'wc-pythia' ) );
		}

		$response = wc_pythia()->api->post_events( $body_params );
		if ( is_wp_error( $response ) ) {
			wc_pythia()->log( 'The following error occurred when contacting API: ' . wp_strip_all_tags( $response->get_error_message() ), 'error' );
			return $response;
		} elseif ( wc_pythia()->settings->debug_enabled() ) {
			wc_pythia()->log( 'Response contacting API: ' . wp_strip_all_tags( $response ) );
		}
		return true;
	}

	public function set_order_as_tracked( $order ) {
		if ( is_a( $order, 'WC_Order' ) ) {
			$order_id = $order->get_id();
		} elseif ( is_numeric( $order ) ) {
			$order_id = $order;
		} else {
			// return if no order or id.
			return;
		}

		update_post_meta( $order_id, self::SYNC_META_KEY, time() );
		update_option( self::SYNC_OPTION_KEY, $order_id );
	}

	/**
	 *
	 */
	public function orders_not_sync( $args = array() ) {
		$default_args = array(
			'limit'             => $this->get_orders_per_page(),
			self::SYNC_META_KEY => 'NOT EXISTS',
			'type'              => 'shop_order',
		);
		$args         = wp_parse_args( $args, $default_args );
		return wc_get_orders( $args );
	}

	/**
	 * Return count of orders that were not sync yet.
	 *
	 * @param  string $status Order status. Function wc_get_order_statuses() returns a list of valid statuses.
	 * @return int
	 */
	public function orders_not_sync_count() {
		global $wpdb;
		$meta_key       = self::SYNC_META_KEY;
		$order_statuses = implode( '\',\'', array_keys( wc_get_order_statuses() ) );
		return absint( $wpdb->get_var( "SELECT COUNT( * ) FROM {$wpdb->posts} AS p LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id AND pm.meta_key = '{$meta_key}' WHERE p.post_type = 'shop_order' AND p.post_status IN ('{$order_statuses}') AND pm.meta_id IS NULL" ) );
	}

	/**
	 * Get last time the sync process ran
	 *
	 * @param  String $date_format Format to display the date.
	 * @return String|Datetime              String with thate to display or datetime
	 */
	public static function last_time_sync( $date_format = null ) {
		$time = get_option( self::SYNC_OPTION_KEY_LAST_TIME_SYNC );

		if ( ! empty( $time ) && ! empty( $date_format ) ) {
			$datetime = new WC_DateTime();
			$datetime->setTimestamp( $time );
			// Set local timezone or offset.
			if ( get_option( 'timezone_string' ) ) {
				$datetime->setTimezone( new DateTimeZone( wc_timezone_string() ) );
			} else {
				$datetime->set_utc_offset( wc_timezone_offset() );
			}
			return $datetime->date_i18n( $date_format );
		}
		return $time;
	}

	/**
	 * Update the value of last time sync
	 *
	 * @param  int $timestamp Time stamp to be used or empty to use current time in UTC
	 * @return bool            True or false if it was updated or no.
	 */
	public static function update_last_time_sync( $timestamp = null ) {
		// If timestamp is omitted it should be current time in UTC
		if ( ! is_numeric( $timestamp ) ) {
			$microtime = (int) microtime( true );
			$timestamp = new WC_DateTime( "@{$microtime}", new DateTimeZone( 'UTC' ) );
		}
		$time = time();
		if ( is_a( $timestamp, 'WC_DateTime' ) ) {
			$time = $timestamp->getTimeStamp();
		}
		return update_option( self::SYNC_OPTION_KEY_LAST_TIME_SYNC, $time );
	}

	public static function reset_sync_flags() {
		// TODO: do this when:
		// - Project is changed.
		// - Settings were reset.
		// - Resync method is executed.
		// - Plugin uninstall.

		// Check if wc_pythia exists when it is used in uninstall file.
		if ( function_exists( 'wc_pythia' ) ) {
			// translators: Place holder is used to display user name.
			wc_pythia()->log( sprintf( __( 'Reset Pythia Synchronization Flags was fired by "%s"', 'wc-pythia' ), get_current_user() ) );
		}
		if ( ! delete_metadata( 'post', 0, self::SYNC_META_KEY, '', true ) ) {
			if ( function_exists( 'wc_pythia' ) ) {
				wc_pythia()->log( __( 'No flags were deleted in post meta table', 'wc-pythia' ) );
			}
		}
		if ( ! delete_option( self::SYNC_OPTION_KEY ) ) {
			if ( function_exists( 'wc_pythia' ) ) {
				wc_pythia()->log( __( 'Sync Option Key deletion was fired but nothing deleted', 'wc-pythia' ) );
			}
		}
		if ( ! delete_option( self::SYNC_OPTION_KEY_LAST_TIME_SYNC ) ) {
			if ( function_exists( 'wc_pythia' ) ) {
				wc_pythia()->log( __( 'Sync Last Time Option deletion was fired but nothing deleted', 'wc-pythia' ) );
			}
		}
	}

	/**
	 * Return WC order number
	 *
	 * @param WC_Order $order Woocommerce order.
	 * @return integer
	 */
	public function get_wc_order_number( $order ) {
		return $order->get_order_number();
	}

	/**
	 * Synchronize method used in the schedule to identify and run a single or batch process depending of the arguments received.
	 */
	public function sync_orders( $order_id = 0 ) {
		if ( is_numeric( $order_id ) && 0 !== absint( $order_id ) ) {
			$this->sync_orders_single( absint( $order_id ) );
		} else {
			$this->sync_orders_batch();
		}
	}

	/**
	 * Synchronize Orders in Batch.
	 *
	 * @return WP_Error|array {
	 * @type integer $total_pending_orders Total pending orders after process ran.
	 * @type array $orders_synchronized Array of Orders IDs tracked.
	 * }
	 */
	public function sync_orders_batch() {
		// Do not continue with the sync if it is disabled.
		if ( $this->is_sync_disabled() ) {
			wc_pythia()->log( 'Batch Orders Synchronization was not completed because Syncrhonization is disabled.' );
			return;
		}
		wc_pythia()->debugging_log( '#####' . __( 'Batch Sync Start!', 'wc-pythia' ) . '#####' );
		$total_pending_orders = $this->orders_not_sync_count();
		$orders_synchronized  = array();

		wc_pythia()->api->post_single_event( 'Sync Started', [ 'total_number_of_orders' => $total_pending_orders ] ); // phpcs:ignore
		// If there are pending orders.
		if ( $total_pending_orders > 0 ) {
			$sync_orders = $this->orders_not_sync();
			$response    = $this->track_orders_batch( $sync_orders );
			if ( ! is_wp_error( $response ) ) {
				$orders_synchronized = array_map(
					array( $this, 'get_wc_order_number' ),
					$sync_orders
				);
			} else {
				wc_pythia()->error_log( sprintf( 'Error in sync_orders_batch: %s', $response->get_error_message() ) );
				wc_pythia()->error_log( __( 'Tracking order failed for orders number: ', 'wc-pythia' ) . implode( ', ', array_map( array( $this, 'get_wc_order_number' ), $sync_orders ) ) );
				// Return WP_Error if there was an error.
				return $response;
			}
		} else {
			// Cancel recurring schedules if there are no pending orders to be processed.
			try {
				WC_Pythia_Sync_Scheduler::cancel_schedule();
			} catch ( \RuntimeException $e ) {
				wc_pythia()->error_log( __( 'Error trying to cancel Pythia Sync Schedule.', 'wc-pythia' ) );
			}
		}

		// UPDATE last tyme the sync process ran.
		$this->update_last_time_sync();

		if ( ! empty( $orders_synchronized ) ) {
			wc_pythia()->debugging_log( __( 'Tracked Orders: ', 'wc-pythia' ) . implode( ', ', $orders_synchronized ) );
		} else {
			wc_pythia()->debugging_log( __( 'There were no orders to be tracked.', 'wc-pythia' ) );
		}

		$batch_result = [  // phpcs:ignore
			'total_pending_orders' => $total_pending_orders,
			'orders_synchronized'  => $orders_synchronized,
		];

		/**
		 * End Syncronization event and debug log will not be sent/stored if there was an error when trying to run the synchronization.
		 */
		wc_pythia()->api->post_single_event(
			'Sync Ended',
			[  // phpcs:ignore
				'total_number_of_orders'  => $total_pending_orders,
				'number_of_synced_orders' => is_array( $batch_result['orders_synchronized'] ) ? count( $orders_synchronized ) : 0,
			]
		);
		wc_pythia()->debugging_log( '#####' . __( 'Batch Sync End!', 'wc-pythia' ) . '#####' );
		return $batch_result;
	}

	/**
	 * Single order synchronization from single order schedule
	 *
	 * When an order status is changed, a single schedule is added to call this method and syncronize the order with Pythia Bot API.
	 *
	 * @since 1.1.2
	 *
	 * @param integer $order_id Order ID.
	 * @return integer|null|WP_Error Order ID if the order was synchronized or null if something failed.
	 */
	public function sync_orders_single( $order_id ) {
		// Do not continue with the sync if it is disabled.
		if ( $this->is_sync_disabled() ) {
			wc_pythia()->log( 'Single Order Synchronization was not completed because Syncrhonization is disabled.' );
			return;
		}
		wc_pythia()->debugging_log( '#####' . __( 'Single Sync Start!', 'wc-pythia' ) . '#####' );
		$tracked_order = null;

		$order = wc_get_order( $order_id );
		if ( $order ) {
			$response = $this->track_order( $order );
			if ( ! is_wp_error( $response ) ) {
				$tracked_order = $order->get_order_number();
				wc_pythia()->debugging_log( __( 'Tracked Order: ', 'wc-pythia' ) . $tracked_order );
			} else {
				wc_pythia()->error_log( __( 'Single Tracking Order failed for order number: ', 'wc-pythia' ) . $order->get_order_number() . __( 'Error Message: ', 'wc-pythia' ) . $response->get_error_message() );
				return $response;
			}
		}

		wc_pythia()->debugging_log( '#####' . __( 'Single Sync End!', 'wc-pythia' ) . '#####' );
		return $tracked_order;
	}
}
