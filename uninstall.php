<?php
// if uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}
$option_name = 'wc_pythia_options';
delete_option( $option_name );
// for site options in Multisite
// delete_site_option($option_name);.

if ( ! class_exists( 'WC_Pythia_Synchronizer' ) && ! method_exists( 'WC_Pythia_Synchronizer', 'reset_sync_flags' ) ) {
	require_once 'inc/class-wc-pythia-synchronizer.php';
}

WC_Pythia_Synchronizer::reset_sync_flags();

// Clear any cached data that has been removed.
wp_cache_flush();
