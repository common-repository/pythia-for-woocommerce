<?php
/**
 * Template: Sync
 *
 * @package PythiaForWoocommerce/Admin/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$is_step_sync    = wc_pythia()->is_wizard_step();
$orders_not_sync = wc_pythia()->sync->orders_not_sync_count();
?>
<?php wc_pythia()->get_template( 'partials/header' ); ?>
<?php if ( $is_step_sync ) : ?>
	<?php wc_pythia()->get_template( 'partials/wizard-steps', array( 'current' => 'sync' ) ); ?>
<?php endif; ?>

<section class="content-center-py">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-8 col-12">
				<?php if ( wc_pythia()->sync->is_sync_enabled() ) : ?>
					<?php if ( 0 === $orders_not_sync && ! $force_resync ) : ?>
						<?php wc_pythia()->get_template( 'partials/thank-you' ); ?>
					<?php else : ?>

						<form id="wc_pythia_sync" action="" method="post">

							<h2><?php esc_html_e( 'You\'re almost done! Let\'s sync your data.', 'wc-pythia' ); ?></h2>

							<?php if ( $is_step_sync ) : ?>
								<p>
									<?php esc_html_e( 'Your shop tracking tool is ready. Let\'s finish this process with syncing your historical data. Our synchronization tool will help you track all existing orders within your connected project. For accurate results, we recommend to start checking stats in 24hs.', 'wc-pythia' ); ?>
								</p>
							<?php elseif ( $orders_not_sync > 0 ) : ?>
								<p>
									<?php esc_html_e( 'You have pending orders to be synchronized. Please click on Synchronize button and our synchronization tool will help you track all existing orders within your connected project. For accurate results, we recommend to start checking stats in 24hs.', 'wc-pythia' ); ?>
								</p>
							<?php else : ?>
								<p>
									<?php esc_html_e( 'You don\'t have pending orders to be synchronized. Please click on Re-Synchronize button and our synchronization tool will re-synchronize all your existing orders within your connected project. For accurate results, we recommend to start checking stats in 24hs.', 'wc-pythia' ); ?>
								</p>
							<?php endif; ?>

							<?php if ( $last_time ) : ?>
								<p class="mt-4"><?php esc_html_e( 'Last time synchronization process ran', 'wc-pythia' ); ?>:</p>
								<p class="text-white"><strong><?php echo esc_html( $last_time ); ?></strong> </p>
							<?php endif; ?>

							<div id="sync-progress"  class="mt-4 mb-4">
								<p class="pending-orders text-white mb-2"><span class="pending_orders_count"><?php echo esc_html( $orders_not_sync ); ?></span> <?php echo esc_html( _nx( 'pending order remaining', 'pending orders remaining', $orders_not_sync, 'pending orders to be sync', 'wc-pythia' ) ); ?></p>
								<div id="progressbar"></div>
							</div>

							<?php if ( $orders_not_sync > 0 ) : ?>
								<button type="submit" name="submit_manual" id="pythia_manual_sync_submit" class="btn btn-py w-330" <?php echo ! wc_pythia()->sync->is_sync_enabled() ? 'disabled' : ''; ?>><?php esc_attr_e( 'Synchronize', 'wc-pythia' ); ?></button>
								<?php if ( $is_step_sync ) : ?>
									<p>
										<a class="arrow-right" href="<?php echo esc_url( wc_pythia()->get_settings_url() ); ?>"><?php esc_html_e( 'I don\'t have pre-existing data, skip this step', 'wc-pythia' ); ?></a>
									</p>
								<?php endif; ?>
							<?php endif; ?>
							<?php if ( $last_time ) : ?>
								<button type="submit" name="pythia_manual_resync" id="pythia_manual_resync" class="btn btn-py w-330" <?php echo ! wc_pythia()->sync->is_sync_enabled() ? 'disabled' : ''; ?>><?php esc_attr_e( 'Re-Synchronize', 'wc-pythia' ); ?></button>
							<?php endif; ?>

						</form>
					<?php endif; ?>
				<?php else : ?>
						<p><?php esc_html_e( 'Synchronization is disabled. This could be due to different reasons. Possible causes are changes in: Domain, WordPress Site Url or if you created a new environment for development.', 'wc-pythia' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<?php wc_pythia()->get_template( 'partials/footer' ); ?>
