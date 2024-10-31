<?php
/**
 * Template: Setup
 *
 * @package PythiaForWoocommerce/Admin/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wc_pythia_google_auth_api = new WC_Pythia_Google_Auth_Api();
$ga_ua_id                  = $wc_pythia_google_auth_api->get_analytics_ua_id();
$ga_ua_name                = $wc_pythia_google_auth_api->get_analytics_ua_name();
$settings_url              = admin_url( 'admin.php?page=wc_pythia' );
?>
<?php wc_pythia()->get_template( 'partials/header' ); ?>

	<section class="settings">
		<div class="container">
			<div class="row justify-content-md-center">
				<div class="col-md-6">
					<form id="wc_pythia_settings" name="wc_pythia_settings" action="<?php echo esc_url( $settings_url ); ?>" method="post">

						<h2><?php esc_html_e( 'Settings', 'wc-pythia' ); ?></h2>

						<?php do_action( 'wc_pythia_admin_notices' ); ?>

						<?php wp_nonce_field( 'pythia-update-settings' ); ?>

						<?php if ( wc_pythia()->is_setup() ) : ?>
							<div class="group-label">
								<label for=""><?php esc_html_e( 'First Name', 'wc-pythia' ); ?></label>
								<span><?php echo esc_html( wc_pythia()->settings->get_option( 'profile_first_name' ) ); ?></span>
							</div>
							<div class="group-label">
								<label for=""><?php esc_html_e( 'Last Name', 'wc-pythia' ); ?></label>
								<span><?php echo esc_html( wc_pythia()->settings->get_option( 'profile_last_name' ) ); ?></span>
							</div>
							<div class="group-label">
								<label for=""><?php esc_html_e( 'Email', 'wc-pythia' ); ?></label>
								<span><?php echo esc_html( wc_pythia()->settings->get_option( 'profile_email' ) ); ?></span>
							</div>
							<div class="group-label">
								<label for="">
								<?php
									$project_id             = wc_pythia()->settings->get_project_id();
									list( $project_id_key ) = $project_id ? explode( '-', $project_id ) : '-';
								?>
									<?php esc_html_e( 'Project (ID)', 'wc-pythia' ); ?></label>
								<span>	<?php if ( $project_id_key ) : ?>
											<?php echo esc_html( wc_pythia()->settings->get_project_name() ); ?> (<?php echo esc_html( $project_id_key ); ?>)
										<?php else : ?>
											<a href="<?php echo esc_url( wc_pythia()->get_select_project_url() ); ?>"><?php echo esc_html( __( 'Select your Pythia Project.', 'wc-pythia' ) ); ?></a>
										<?php endif; ?>
								</span>
							</div>
							<div class="group-label">
								<label for="">
								<?php
									$source_id             = wc_pythia()->settings->get_source_id();
									if ( $source_id ) {
										list( $source_id_key ) = $source_id ? explode( '-', $source_id ) : '-';
									} else {
										$source_id_key = __( 'N/A', 'wc-pythia' );
									}
								?>
									<?php esc_html_e( 'Source (ID)', 'wc-pythia' ); ?></label>
								<span>
										<?php if ( $project_id_key ) : ?>
											<?php echo esc_html( wc_pythia()->settings->get_source_name() ); ?> (<?php echo esc_html( $source_id_key ); ?>)
										<?php else : ?>
											<a href="<?php echo esc_url( wc_pythia()->get_select_project_url() ); ?>"><?php echo esc_html( __( 'Select your Pythia Project.', 'wc-pythia' ) ); ?></a>
										<?php endif; ?>
								</span>
							</div>
							<div class="group-label">
								<label for=""><?php esc_html_e( 'Google Analytics View', 'wc-pythia' ); ?></label>
								<span><?php if ( $ga_ua_id ) : ?>
										<?php echo esc_html( $ga_ua_name ); ?> (<?php echo esc_html( $ga_ua_id ); ?>)
										<?php elseif ( wc_pythia()->is_setup() ) : ?>
											<a href="<?php echo esc_url( wc_pythia()->get_ga_url() ); ?>"><?php echo esc_html( __( 'Connect with your GA account.', 'wc-pythia' ) ); ?></a>
										<?php else : ?>
											<?php esc_html_e( 'N/A', 'wc-pythia' ); ?>
										<?php endif; ?></span>
							</div>
						<?php else : ?>
							<div class="group-label">
								<p><?php esc_html_e( 'If you don\'t have an account yet, please setup one to continue.', 'wc-pythia' ); ?> <a href="<?php echo esc_url( wc_pythia()->get_setup_url() ); ?>"><?php esc_html_e( 'Click here to setup an account.', 'wc-pythia' ); ?></a></p>
							</div>
							<div class="group-label">
								<p><?php esc_html_e( 'If you already have an account, please login.', 'wc-pythia' ); ?> <a href="<?php echo esc_url( wc_pythia()->get_login_url() ); ?>"><?php esc_html_e( 'Click here to login into your account.', 'wc-pythia' ); ?></a></p>
							</div>
						<?php endif; ?>
						<?php if ( $show_advanced_settings ) : ?>
						<div class="group-label">
							<label for="orders_per_page"><?php esc_html_e( 'Number of orders per process', 'wc-pythia' ); ?> *</label>
							<span><input type="number" id="orders_per_page" class="form-control" name="wc_pythia_options_orders_per_page" value="<?php echo esc_attr( wc_pythia()->settings->get_orders_per_page() ); ?>" required></span>
						</div>
						<div class="checkbox-py">
							<input type="checkbox" id="debug" name="wc_pythia_options_debug" <?php checked( true, wc_pythia()->settings->debug_enabled() ); ?> class="styled-checkbox" value="yes">
							<label for="debug"><?php esc_html_e( 'Debug', 'wc-pythia' ); ?></label>
						</div>
						<?php endif; ?>


						<div class="btn-group-py text-right">
							<button type="submit" name="wc-pythia-submit" id="wc-pythia-submit" class="btn btn-py"><?php esc_attr_e( 'Update', 'wc-pythia' ); ?></button>
							<button name="wc-pythia-disconnect" id="wc-pythia-disconnect" class="btn btn-reset " data-url="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'wc-pythia-disconnect' => 'true' ), $settings_url ), 'pythia-disconnect' ) ); ?>"><?php esc_attr_e( 'Disconnect', 'wc-pythia' ); ?></button>
							<button name="wc-pythia-reset" id="wc-pythia-reset" class="btn btn-reset " data-url="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'wc-pythia-reset' => 'true' ), $settings_url ), 'pythia-reset-settings' ) ); ?>"><?php esc_attr_e( 'Reset & Disconnect', 'wc-pythia' ); ?></button>
						</div>
					</form>
				</div>
			</div>
		</div><!-- main -->
	</section><!-- /py-wrapper -->

	<?php
	wc_pythia()->get_template(
		'partials/dialog',
		array(
			'title'   => __( 'Reset Settings and Disconnect', 'wc-pythia' ),
			'content' => __(
				'This action will delete all settings and disconnect the site from Pythia Bot. Are you sure?',
				'wc-pythia'
			),
		)
	);
	?>

<?php wc_pythia()->get_template( 'partials/footer' ); ?>
