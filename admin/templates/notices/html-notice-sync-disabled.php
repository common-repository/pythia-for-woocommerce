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
	<p><strong><?php esc_html_e( 'Pythia for Woocommerce Bot &#8211; Synchronization is DISABLED.', 'wc-pythia' ); ?></strong></p>
	<p><?php esc_html_e( 'Pythia Bot Synchronization could be disabled by different reasons like:', 'wc-pythia' ); ?></p>
	<ul>
		<li>- <?php esc_html_e( 'The site domain was changed from "www" to non "www" or viceversa.', 'wc-pythia' ); ?></li>
		<li>- <?php esc_html_e( 'A development environment was created and the domain was changed.', 'wc-pythia' ); ?></li>
		<li>- <?php esc_html_e( 'A specific port, like 8080, was used and now it is different.', 'wc-pythia' ); ?></li>
		<li>- <?php esc_html_e( 'Custom code applied to disable it.', 'wc-pythia' ); ?></li>
	</ul>
	<p><?php esc_html_e( 'If you didn\'t do any of those changes and you still seeing this notice, please contact support team.', 'wc-pythia' ); ?></p>
