<?php
/**
 * Undocumented class
 */
class WC_Pythia_Admin_Sync extends WC_Pythia_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		/**
		 * Register our wc_pythia_sync_init to the admin_init action hook
		*/
		add_action( 'admin_init', array( $this, 'wc_pythia_sync_init' ) );

		/**
		 * Register our wc_pythia_sync_page to the admin_menu action hook
		 */
		// Don't display setup page if a token exists
		// this means that an account already exists.
		if ( wc_pythia()->is_setup() ) {
			add_action( 'admin_menu', array( $this, 'wc_pythia_sync_page' ) );
		}
	}

	/**
	 * Initialize method attached to the admin_init
	 *
	 * Define different actions and Ajax action to be used in the synchronization page.
	 *
	 * @since 1.1.2
	 * @return void
	 */
	public function wc_pythia_sync_init() {
		add_action( 'wp_ajax_pythia_schedule_action', array( $this, 'ajax_schedule_action' ) );
		add_action( 'wp_ajax_pythia_sync', array( $this, 'ajax_sync' ) );
		add_action( 'wp_ajax_pythia_resync', array( $this, 'ajax_resync' ) );
	}

	/**
	 * Top level menu
	 */
	public function wc_pythia_sync_page() {
		$sync_page = add_submenu_page(
			$this->plugin_id,
			__( 'Sync', 'wc-pythia' ),
			__( 'Sync', 'wc-pythia' ),
			'manage_options',
			'wc_pythia_sync',
			array( $this, 'wc_pythia_sync_page_html' )
		);

		// Load resources related to sync page.
		add_action( "load-{$sync_page}", array( $this, 'wc_pythia_sync_page_load' ) );
	}


	/**
	 * Top level menu:
	 * callback functions
	 */
	public function wc_pythia_sync_page_load() {
		$screen = get_current_screen();

		/*
		 * Check if current screen is My Admin Page
		 * Don't add help tab if it's not
		 */
		if ( 'pythia_page_wc_pythia_sync' !== $screen->id ) {
			return;
		}

		// Load style & scripts.

		$this->register_sync_scripts();

	}

	/**
	 * Register Sync Admin page Scripts
	 *
	 * @return void
	 */
	public function register_sync_scripts() {

		// Register parent script.
		parent::register_scripts();
		// Register the script.
		wp_register_script( 'wc-pythia-sync', WC_PYTHIA__PLUGIN_URL . 'admin/assets/js/wc-pythia-sync.js', array( 'jquery', 'jquery-ui-progressbar' ), WC_Pythia::version(), false );

		// Localize the script with new data.
		$settings = array(
			'pending_orders_count' => wc_pythia()->sync->orders_not_sync_count(),
			'schedule_next_run'    => WC_Pythia_Sync_Scheduler::schedule_next_run(),
			'sync_enabled'         => wc_pythia()->sync->is_sync_enabled(),
			'sync_nonce'           => wp_create_nonce( 'pythia-sync-nonce' ),
			'resync_nonce'         => wp_create_nonce( 'pythia-resync-nonce' ),
			'schedule_nonce'       => wp_create_nonce( 'pythia-schedule-nonce' ),
			'wizard'               => wc_pythia()->is_wizard_step(),
			'thank_you_page'       => wc_pythia()->get_sync_url(),
		);

		wp_localize_script( 'wc-pythia-sync', 'pythia_sync_settings', $settings );

		// Enqueue the jQuery UI theme css file from google:.
		// wp_enqueue_style( 'wc-pythia-jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', false, '1.12.1', false );

		wp_enqueue_script( 'wc-pythia-sync' );
	}

	/**
	 * Ajax call to schedule sync process.
	 */
	public function ajax_schedule_action() {
		check_ajax_referer( 'pythia-schedule-nonce' );
		$message = '';
		$success = true;
		if ( function_exists( 'as_schedule_recurring_action' ) ) {
			if ( ! WC_Pythia_Sync_Scheduler::scheduled() && ! empty( wc_pythia()->sync->orders_not_sync_count() ) ) {
				$micro_time = (int) microtime( true );
				try {
					// First process will run in 4 minutes and then it will be recursive running every 1 minute.
					WC_Pythia_Sync_Scheduler::schedule_recurring( $micro_time + 240 );
					wc_pythia()->api->post_single_event( 'Sync Process Scheduled', [ 'total_number_of_orders' => wc_pythia()->sync->orders_not_sync_count() ] ); // phpcs:ignore
					$message .= __( 'Sync Process Scheduled.', 'wc-pythia' );
				} catch ( RuntimeException $e ) {
					$message .= $e->getMessage();
				}
			} else {
				$message .= __( 'Sync Process Already Scheduled.', 'wc-pythia' );
			}
		} else {
			wc_pythia()->log( '"as_schedule_recurring_action" does not exists.' );
			$message .= __( '"as_schedule_recurring_action" does not exists. Please contact plugin\'s author.', 'wc-pythia' );
			$success  = false;
		}

		if ( $success ) {
			wp_send_json_success(
				array(
					'message' => $message,
				)
			);
		} else {
			wp_send_json_error( array( $message ? $message : __( 'Unknown Error while trying to generete sync orders schedule.', 'wc-pythia' ) ), 400 );
		}
		wp_die();
	}

	/**
	 * Ajax call to re sync process.
	 */
	public function ajax_resync() {
		check_ajax_referer( 'pythia-resync-nonce' );

		do_action( 'wc_pythia_reset_sync_flags' );

		wp_send_json_success(
			array(
				'message' => __( 'Resync Process Started.', 'wc-pythia' ),
			)
		);
		wp_die();
	}

	/**
	 * Ajax call to Synchronize Orders.
	 */
	public function ajax_sync() {
		check_ajax_referer( 'pythia-sync-nonce' );

		wc_pythia()->debugging_log( '#####' . __( 'Manual Sync Start By Ajax!', 'wc-pythia' ) . '#####' );

		$success              = true;
		$message              = '';
		$batch_result_default = [  // phpcs:ignore
			'total_pending_orders' => null,
			'orders_synchronized'  => 0,
		];

		if ( ! WC_Pythia_Sync_Scheduler::is_schedule_running() ) {
			// TODO: we need to try to sync only if it is connected.
			try {
				$batch_result = wc_pythia()->sync->sync_orders_batch();
				if ( ! is_wp_error( $batch_result ) ) {
					$batch_result = wp_parse_args( $batch_result, $batch_result_default );
				}
			} catch ( Exception $e ) {
				$success = false;
				$message = $e->getMessage();
				$code    = $e->getCode();
				wp_send_json_error( array( $message ? $message : __( 'Unknown Error while synchronizing orders.', 'wc-pythia' ) ), $code ? $code : 400 );
				wp_die();
			}

			if ( is_wp_error( $batch_result ) ) {
				$message = $batch_result->get_error_message();
				$code    = $batch_result->get_error_code();
				$success = false;
				if ( 503 === absint( $code ) ) {
					$message = sprintf( '%s %s: %s', __( 'There was an error trying to synchronize your orders, this error could be caused due to a server or API timeout, please go to the Settings page and set a less value in the field "Number of orders per process", then try synchronization process again. If the error persists please contact Pythia Bot support team.', 'wc-pythia' ), __( 'Error description', 'wc-pythia' ), $message );
				}
				// If this validation is true, it means that no orders were sync.
			} elseif ( 0 === count( $batch_result['orders_synchronized'] ) ) {
				// If this is true all orders sent to be synchronized are failing and we need to stop the process to avoid infinite loop.
				if ( 0 < $batch_result['total_pending_orders'] ) {
					$success = false;
					// translators: string placeholder to display the number of pending orders.
					$message = sprintf( __( 'There are "%s" orders to be processed but none was synchronized, this synchronization process has been stopped, please contact Pythia Support Team.', 'wc-pythia' ), $batch_result['total_pending_orders'] );
				}
			}
		}

		if ( $success ) {
			wp_send_json_success(
				array(
					'pending_orders_count' => ( null === $batch_result['total_pending_orders'] ) ? wc_pythia()->sync->orders_not_sync_count() : $batch_result['total_pending_orders'], // if not null use pending_orders to avoid callind database counter again.
					'orders_synchronized'  => $batch_result['orders_synchronized'],
				)
			);
		} else {
			wp_send_json_error( array( $message ? $message : __( 'Unknown Error while synchronizing orders.', 'wc-pythia' ) ), $code ? $code : 400 );
		}
		wp_die();
	}

	/**
	 * Top level menu:
	 * callback functions
	 */
	public function wc_pythia_sync_page_html() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show error/update messages.
		settings_errors( "{$this->plugin_id}_messages" );

		$args = array(
			'last_time'    => wc_pythia()->sync->last_time_sync( 'F jS, Y \a\t H:i:s' ),
			'force_resync' => ( isset( $_GET['force_resync'] ) && boolval( $_GET['force_resync'] ) ) ? true : false, // phpcs:ignore WordPress.Security.NonceVerification
		);

		wc_pythia()->get_template( 'sync-form', $args );
	}
}

new WC_Pythia_Admin_sync();
