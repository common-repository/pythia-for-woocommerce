<?php
/**
 * Load assets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pythia_Admin_Assets', false ) ) :

	/**
	 * WC_Pythia_Admin_Assets Class.
	 */
	class WC_Pythia_Admin_Assets {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_styles' ) );
		}

		/**
		 * Enqueue styles.
		 */
		public function load_admin_styles() {
			// Register menu CSS style.
			wp_register_style( 'pythia_admin_menu_styles', WC_PYTHIA__PLUGIN_URL . 'admin/assets/css/wc-pythia-menu.css', array(), WC_Pythia::version() );

			// Enqueue menu CSS.
			wp_enqueue_style( 'pythia_admin_menu_styles' );
		}
	}

endif;

return new WC_Pythia_Admin_Assets();
