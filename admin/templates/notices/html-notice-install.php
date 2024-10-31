<?php
/**
 * Template: Install
 *
 * @package PythiaForWoocommerce/Admin/Templates/Notices
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
	<p><?php esc_html_e( 'Welcome to Pythia for Woocommerce &#8211; You&lsquo;re almost ready to start tracking your sales.', 'wc-pythia' ); ?></p>
	<p class="submit">
		<a href="<?php echo esc_url( wc_pythia()->get_setup_url() ); ?>" class="button-primary"><?php esc_html_e( 'Run the Setup', 'wc-pythia' ); ?></a> <a href="<?php echo esc_url( wc_pythia()->get_login_url() ); ?>" class="button-primary"><?php esc_html_e( 'Go to Login', 'wc-pythia' ); ?></a> <a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wc-hide-notice', 'wc_pythia_setup' ), 'woocommerce_hide_notices_nonce', '_wc_notice_nonce' ) ); ?>"><?php esc_html_e( 'Skip setup', 'wc-pythia' ); ?></a>
	</p>
