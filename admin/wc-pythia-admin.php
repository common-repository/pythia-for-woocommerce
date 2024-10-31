<?php
/**
 * Pythia Main file for admin section
 *
 * @package PythiaForWoocommerce/Admin
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once wc_pythia()::plugin_dir() . 'inc/class-wc-pythia-api.php';
require_once wc_pythia()::plugin_dir() . 'inc/class-wc-pythia-google-auth-api.php';
require_once 'inc/class-wc-admin-pythia.php';
require_once 'inc/class-wc-pythia-project.php';
require_once 'class-wc-pythia-admin-assets.php';
require_once 'class-wc-pythia-admin-settings.php';
require_once 'class-wc-pythia-admin-setup.php';
require_once 'class-wc-pythia-admin-google-auth.php';
if ( pythia_is_woocommerce_active() ) {
	require_once 'class-wc-pythia-admin-sync.php';
}
require_once 'class-wc-pythia-admin-login.php';
require_once 'class-wc-pythia-admin-project.php';
