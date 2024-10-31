<?php
/**
 * Pythia Project Page
 *
 *  If login is succesfull the list of available projects will be displayed to select which one will be associated with the website.
 *
 * @package WC_Pythia\Admin
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;


/**
 * WC_Pythia_Admin_Project
 *
 * Class to manage Pythia Bot Project page.
 *
 * @since 1.1.2
 * @see WC_Pythia_Admin
 */
class WC_Pythia_Admin_Project extends WC_Pythia_Admin {

	/**
	 * Constructor class with admin initializer
	 *
	 * Submenu page added.
	 */
	public function __construct() {
		parent::__construct();
		// Don't display setup page if a token exists
		// This means that an account already exists.
		if ( wc_pythia()->is_setup() && ! wc_pythia()->settings->get_project_id() ) {
			/**
			 * Register our wc_pythia_project_page to the admin_menu action hook
			 */
			add_action( 'admin_menu', array( $this, 'wc_pythia_project_page' ) );
		}
	}

	/**
	 * Pythia project page added as second level menu page
	 *
	 * Submenu page added and the action to load resources for this page only.
	 */
	public function wc_pythia_project_page() {
		$project_page = add_submenu_page(
			$this->plugin_id,
			__( 'Project', 'wc-pythia' ),
			__( 'Project', 'wc-pythia' ),
			'manage_options',
			'wc_pythia_project',
			array( $this, 'wc_pythia_project_page_html' )
		);

		// load resources related to project page.
		add_action( 'load-' . $project_page, array( $this, 'wc_pythia_project_page_load' ) );
	}


	/**
	 * Top level menu:
	 * callback functions
	 */
	public function wc_pythia_project_page_load() {
		$screen = get_current_screen();

		/*
		* Check if current screen is My Admin Page
		* Don't add help tab if it's not
		*/
		if ( 'pythia_page_wc_pythia_project' !== $screen->id ) {
			return;
		}

		// Load style & scripts.

		$this->register_project_scripts();

	}

	/**
	 * Register project page styles, scripts and localize variables
	 *
	 * @return void
	 */
	public function register_project_scripts() {
		// Register parent script.
		parent::register_scripts();
	}

	/**
	 * Top level menu:
	 * callback functions
	 */
	public function wc_pythia_project_page_html() {
		// check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wc_pythia()->get_template( 'project-form' );
	}
}

new WC_Pythia_Project();
new WC_Pythia_Admin_Project();
