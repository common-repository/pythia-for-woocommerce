<?php
/**
 * Admin Template: Notice - Updating
 *
 * @package PythiaForWoocommerce/Admin/Templates/Notices
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pending_actions_url = admin_url( 'admin.php?page=wc_pythia_sync' );
$cron_disabled       = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
$cron_cta            = $cron_disabled ? __( 'You can manually run queued updates here.', 'wc-pythia' ) : __( 'View progress &rarr;', 'wc-pythia' );
?>
<div id="message" class="updated woocommerce-message wc-connect">
	<p>
		<strong><?php esc_html_e( 'Pythia synchronization', 'wc-pythia' ); ?></strong><br>
		<?php esc_html_e( 'Pythia is synchronizing your orders in the background. The synchronization process may take a little while, so please be patient.', 'wc-pythia' ); ?>
		<?php
		if ( $cron_disabled ) {
			echo '<br>' . esc_html__( 'Note: WP CRON has been disabled on your install which may prevent this update from completing.', 'wc-pythia' );
		}
		?>
		&nbsp;<a href="<?php echo esc_url( $pending_actions_url ); ?>"><?php echo esc_html( $cron_cta ); ?></a>
	</p>
</div>
