<?php
/**
 * Template: Thank You
 *
 * @package PythiaForWoocommerce/Admin/Templates/PArtials
 * @version 1.1.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="su-completed">
	<div class="container">
		<div class="row justify-content-center">
			<div class="bg-androide"></div>
			<div class="col-md-8 col-12 text-center">
				<h1><?php esc_html_e( 'Awesome, you’re all set!', 'wc-pythia' ); ?></h1>
				<p class="mt-4"><?php esc_html_e( 'Thank you for choosing Pythia. From now on we will do the work for you and we will provide all of the information necessary to help your business grow.', 'wc-pythia' ); ?></p>
				<p class="mt-4"><?php esc_html_e( 'Click on the "Re-Synchronize All" button in case you need or want to re-synchronize all your historical data again.', 'wc-pythia' ); ?></p>

				<p  class="mt-4"><a href="<?php echo esc_url( wc_pythia()->get_sync_url() ); ?>&force_resync=1" class="btn btn-py w-330" <?php echo ! wc_pythia()->sync->is_sync_enabled() ? 'disabled' : ''; ?>><?php esc_attr_e( 'Re-Synchronize All', 'wc-pythia' ); ?></a></p>

				<h2  class="mt-5"><?php esc_html_e( 'Download the Pythia Bot mobile app', 'wc-pythia' ); ?></h2>
				<a href="<?php echo esc_url_raw( WC_PYTHIA__APP_STORE_URL ); ?>"><img src="<?php echo esc_attr( WC_PYTHIA__PLUGIN_URL ); ?>admin/assets/img/apple.png" alt="App Store"></a>
				<a href="<?php echo esc_url_raw( WC_PYTHIA__GOOGLE_PLAY_URL ); ?>"><img src="<?php echo esc_attr( WC_PYTHIA__PLUGIN_URL ); ?>admin/assets/img/google.png" alt="Google Play"></a>
			</div>
		</div>
	</div>
</section>
