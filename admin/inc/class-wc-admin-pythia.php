<?php

defined( 'ABSPATH' ) || exit;

/**
 * WC_Pythia_Admin
 */
class WC_Pythia_Admin {
	protected $prefix;
	protected $plugin_id;
	protected $version;
	protected $plugin_admin_dir;
	protected $plugin_admin_url;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->prefix           = WC_Pythia::plugin_prefix();
		$this->plugin_id        = WC_Pythia::plugin_id();
		$this->version          = WC_Pythia::version();
		$this->plugin_admin_dir = WC_Pythia::plugin_admin_dir();
		$this->plugin_admin_url = WC_Pythia::plugin_admin_url();
	}

	/**
	 * Register Global admin scripts
	 *
	 * Default CSS and JS Resources used by Pythia for Woocommerce plugin.
	 *
	 * @since 1.1.4
	 * @return void
	 */
	public function register_scripts() {

		// Register styles.
		wp_register_style( 'wc-pythia-bootstrap-grid', WC_PYTHIA__PLUGIN_URL . 'admin/assets/css/bootstrap-grid.min.css', array(), WC_Pythia::version() );
		wp_register_style( 'wc-pythia-bootstrap', WC_PYTHIA__PLUGIN_URL . 'admin/assets/css/bootstrap.min.css', array(), WC_Pythia::version() );
		wp_register_style( 'wc-pythia-fontawesome', WC_PYTHIA__PLUGIN_URL . 'admin/assets/css/fontawesome.css', array(), WC_Pythia::version() );
		// wp_register_style( 'wc-pythia-fontawesome-2', WC_PYTHIA__PLUGIN_URL . 'admin/assets/css/font-awesome.css', array(), WC_Pythia::version() );
		wp_register_style( 'wc-pythia', WC_PYTHIA__PLUGIN_URL . 'admin/assets/css/main.min.css', array( 'wc-pythia-bootstrap', 'wc-pythia-bootstrap-grid', 'wc-pythia-fontawesome', 'wc-pythia-fontawesome' ), WC_Pythia::version() );

		wp_enqueue_style( 'wc-pythia' );

		// Register scripts.
		wp_register_script( 'wc-pythia-jquery-validation', WC_PYTHIA__PLUGIN_URL . 'admin/assets/js/jquery-validation/dist/jquery.validate.min.js', array( 'jquery' ), WC_Pythia::version(), false );
		wp_register_script( 'wc-pythia', WC_PYTHIA__PLUGIN_URL . 'admin/assets/js/wc-pythia.js', array( 'jquery' ), WC_Pythia::version(), false );

		// Localize the script with new data.
		$settings = array(
			'plugin_url'       => WC_PYTHIA__PLUGIN_URL,
			'loader_image_url' => WC_PYTHIA__PLUGIN_URL . 'admin/assets/img/loading.svg',
		);
		wp_localize_script( 'wc-pythia', 'pythia_settings', $settings );
		wp_enqueue_script( 'wc-pythia' );
	}

}
