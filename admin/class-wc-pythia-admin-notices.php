<?php
/**
 *
 */
class WC_Pythia_Admin_Notices {

	/**
	 * Stores notices.
	 *
	 * @var array
	 */
	private static $notices = array();

	/**
	 * Array of notices - name => callback.
	 *
	 * @var array
	 */
	private static $core_notices = array();

	/**
	 * Constructor.
	 */
	public static function init() {
		self::$notices = get_option( 'wp_pythia_admin_notices', array() );

		add_action( 'wp_loaded', array( __CLASS__, 'hide_notices' ) );
		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'admin_print_styles', array( __CLASS__, 'add_notices' ) );
			add_action( 'admin_notices', array( __CLASS__, 'pending_actions_notice' ) );
			add_action( 'admin_print_scripts', array( __CLASS__, 'hide_non_pythia_notices' ) );
			add_action( 'admin_head', array( __CLASS__, 'hide_non_pythia_notices' ), 99 );
		}
	}

	/**
	 * If we need to update, include a message with the update button.
	 */
	public static function pending_actions_notice() {
		if ( ! empty( wc_pythia()->sync ) && wc_pythia()->sync->is_sync_enabled() && WC_Pythia_Sync_Scheduler::scheduled() ) {
			include dirname( __FILE__ ) . '/templates/notices/html-notice-updating.php';
		}
	}

	/**
	 * Remove non-pythia notices in pythia pages.
	 *
	 * @return null If not pythia screen.
	 * @since 1.0.0
	 */
	public static function hide_non_pythia_notices() {
		$current_screen = get_current_screen()->id;
		// return if we're not on a Pythia screens.
		if ( empty( $current_screen ) || strpos( $current_screen, 'pythia' ) === false ) {
			return;
		}

		global $wp_filter;
		if ( ! empty( $wp_filter['user_admin_notices']->callbacks ) && is_array( $wp_filter['user_admin_notices']->callbacks ) ) {
			foreach ( $wp_filter['user_admin_notices']->callbacks as $priority => $hooks ) {
				foreach ( $hooks as $name => $arr ) {
					if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
						unset( $wp_filter['user_admin_notices']->callbacks[ $priority ][ $name ] );
						continue;
					}
					if ( ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) && strpos( strtolower( get_class( $arr['function'][0] ) ), 'pythia' ) !== false ) {
						continue;
					}
					if ( ! empty( $name ) && strpos( $name, 'pythia' ) === false ) {
						unset( $wp_filter['user_admin_notices']->callbacks[ $priority ][ $name ] );
					}
				}
			}
		}

		if ( ! empty( $wp_filter['admin_notices']->callbacks ) && is_array( $wp_filter['admin_notices']->callbacks ) ) {
			foreach ( $wp_filter['admin_notices']->callbacks as $priority => $hooks ) {
				foreach ( $hooks as $name => $arr ) {
					if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
						unset( $wp_filter['admin_notices']->callbacks[ $priority ][ $name ] );
						continue;
					}
					if ( ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) && strpos( strtolower( get_class( $arr['function'][0] ) ), 'pythia' ) !== false ) {
						continue;
					}
					if ( ! empty( $name ) && strpos( $name, 'pythia' ) === false ) {
						unset( $wp_filter['admin_notices']->callbacks[ $priority ][ $name ] );
					}
				}
			}
		}

		if ( ! empty( $wp_filter['all_admin_notices']->callbacks ) && is_array( $wp_filter['all_admin_notices']->callbacks ) ) {
			foreach ( $wp_filter['all_admin_notices']->callbacks as $priority => $hooks ) {
				foreach ( $hooks as $name => $arr ) {
					if ( is_object( $arr['function'] ) && $arr['function'] instanceof Closure ) {
						unset( $wp_filter['all_admin_notices']->callbacks[ $priority ][ $name ] );
						continue;
					}
					if ( ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) && strpos( strtolower( get_class( $arr['function'][0] ) ), 'pythia' ) !== false ) {
						continue;
					}
					if ( ! empty( $name ) && strpos( $name, 'pythia' ) === false ) {
						unset( $wp_filter['all_admin_notices']->callbacks[ $priority ][ $name ] );
					}
				}
			}
		}
	}

	/**
	 * Store notices to DB
	 */
	public static function store_notices() {
		update_option( 'wp_pythia_admin_notices', self::get_notices() );
	}

	/**
	 * Get notices
	 *
	 * @return array
	 */
	public static function get_notices() {
		return self::$notices;
	}

	/**
	 * Remove all notices.
	 */
	public static function remove_all_notices() {
		self::$notices = array();
	}

	/**
	 * Reset notices for themes when switched or a new version of WC is installed.
	 */
	public static function reset_admin_notices() {
		self::add_min_version_notice();
	}

	/**
	 * Show a notice.
	 *
	 * @param string $name Notice name.
	 * @param bool   $force_save Force saving inside this method instead of at the 'shutdown'.
	 */
	public static function add_notice( $name, $force_save = false ) {
		self::$notices = array_unique( array_merge( self::get_notices(), array( $name ) ) );

		if ( $force_save ) {
			// Adding early save to prevent more race conditions with notices.
			self::store_notices();
		}
	}

	/**
	 * Remove a notice from being displayed.
	 *
	 * @param string $name Notice name.
	 * @param bool   $force_save Force saving inside this method instead of at the 'shutdown'.
	 */
	public static function remove_notice( $name, $force_save = false ) {
		self::$notices = array_diff( self::get_notices(), array( $name ) );
		delete_option( 'wp_pythia_admin_notice_' . $name );

		if ( $force_save ) {
			// Adding early save to prevent more race conditions with notices.
			self::store_notices();
		}
	}

	/**
	 * See if a notice is being shown.
	 *
	 * @param string $name Notice name.
	 *
	 * @return boolean
	 */
	public static function has_notice( $name ) {
		return in_array( $name, self::get_notices(), true );
	}

	/**
	 * Hide a notice if the GET variable is set.
	 */
	public static function hide_notices() {
		if ( isset( $_GET['wp-pythia-hide-notice'] ) && isset( $_GET['_wp_pythia_notice_nonce'] ) ) { // WPCS: input var ok, CSRF ok.
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wp_pythia_notice_nonce'] ) ), 'wp_pythia_hide_notices_nonce' ) ) { // WPCS: input var ok, CSRF ok.
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'wc-pythia' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'wc-pythia' ) );
			}

			$hide_notice = sanitize_text_field( wp_unslash( $_GET['wp-pythia-hide-notice'] ) ); // WPCS: input var ok, CSRF ok.

			self::remove_notice( $hide_notice );

			update_user_meta( get_current_user_id(), 'dismissed_' . $hide_notice . '_notice', true );

			do_action( 'wp_pythia_hide_' . $hide_notice . '_notice' );
		}
	}

	/**
	 * Add notices + styles if needed.
	 */
	public static function add_notices() {
		$notices = self::get_notices();

		if ( empty( $notices ) ) {
			return;
		}

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$show_on_screens = array(
			'dashboard',
			'plugins',
		);

		// Notices should only show on Pythia screens, the main dashboard, and on the plugins screen.
		if ( ! in_array( $screen_id, wc_pythia()->get_screen_ids(), true ) && ! in_array( $screen_id, $show_on_screens, true ) ) {
			return;
		}

		foreach ( $notices as $notice ) {
			if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'wp_pythia_show_admin_notice', true, $notice ) ) {
				add_action( 'admin_notices', array( __CLASS__, self::$core_notices[ $notice ] ) );
			} else {
				add_action( 'admin_notices', array( __CLASS__, 'output_custom_notices' ) );
			}
		}
	}

	/**
	 * Add a custom notice.
	 *
	 * @param string $name        Notice name.
	 * @param string $notice_html Notice HTML.
	 */
	public static function add_custom_notice( $name, $notice_html ) {
		self::add_notice( $name );
		update_option( 'wp_pythia_admin_notice_' . $name, wp_kses_post( $notice_html ) );
	}

	/**
	 * Output any stored custom notices.
	 */
	public static function output_custom_notices() {
		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice ) {
				if ( empty( self::$core_notices[ $notice ] ) ) {
					$notice_html = get_option( 'wp_pythia_admin_notice_' . $notice );

					if ( $notice_html ) {
						include dirname( __FILE__ ) . '/views/html-notice-custom.php';
					}
				}
			}
		}
	}

	/**
	 * Wrapper for is_plugin_active.
	 *
	 * @param string $plugin Plugin to check.
	 * @return boolean
	 */
	protected static function is_plugin_active( $plugin ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( $plugin );
	}
}

add_action( 'admin_init', array( 'WC_Pythia_Admin_Notices', 'init' ) );
