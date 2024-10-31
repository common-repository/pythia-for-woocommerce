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

$authenticate_step = (bool) ! isset( $_GET['pythia_auth_success'] ); // phpcs:ignore WordPress.Security.NonceVerification
$project_step      = (bool) $wc_pythia_google_auth_api->google_authorized() && ( ( isset( $_GET['pythia_auth_success'] ) || ! $wc_pythia_google_auth_api->get_analytics_ua_id() ) ); // phpcs:ignore WordPress.Security.NonceVerification
$ua_authenticated  = (bool) $wc_pythia_google_auth_api->google_authorized() && $wc_pythia_google_auth_api->get_analytics_ua_id();
?>
<?php wc_pythia()->get_template( 'partials/header' ); ?>
<?php if ( wc_pythia()->is_wizard_step() ) : ?>
	<?php wc_pythia()->get_template( 'partials/wizard-steps', array( 'current' => 'connect' ) ); ?>
<?php endif; ?>

<section class="content-center-py">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-8 col-12">

				<form id="wc_pythia_google_auth" action="" method="post">
							<h2><?php esc_html_e( 'Connect your Google Analytics', 'wc-pythia' ); ?></h2>

								<?php if ( $authenticate_step ) : ?>
									<p class="mt-4">
										<?php if ( $ua_authenticated ) : ?>
											<?php
											// translators: %s: Display Google UA ID.
											printf( esc_html__( 'You are already authenticated using Google Analytics UA View ID: %s. Click on Re-Authenticate button if you want to login with other Google Account and select a different Project.', 'wc-pythia' ), esc_html( $wc_pythia_google_auth_api->get_analytics_ua_id() ) );
											?>
										<?php else : ?>
											<?php esc_html_e( 'To provide you with better stats, connecting Pythia to Google Analytics would be a good first step. You will be asked to allow Pythia access to your Google Analytics account. Start by authenticating you account, then select your GA project to finish the connection process.', 'wc-pythia' ); ?>
											<br><br>
											<?php if ( ! ( ! is_wp_error( $google_accounts ) && $google_accounts ) && wc_pythia()->is_wizard_step() ) : ?>
												<a class="arrow-right" href="<?php echo esc_url( add_query_arg( 'wizard_step', 1, pythia_is_woocommerce_active() ? wc_pythia()->get_sync_url() : wc_pythia()->get_settings_url() ) ); ?>"><?php esc_html_e( 'Skip this step', 'wc-pythia' ); ?></a>
											<?php endif; ?>
										<?php endif; ?>
									</p>
								<?php elseif ( $project_step ) : ?>
									<p><?php esc_html_e( 'Your account was authorized, please select which Google Analytics Project you want to associate to your Pythia Project.', 'wc-pythia' ); ?></p>
								<?php endif; ?>

								<div class="group-register w-330">

									<button type="submit" name="submit_manual" id="pythia_google_auth_submit" class="btn <?php echo $project_step ? 'btn-dark-py' : 'btn-py'; ?>" <?php echo $project_step ? 'disabled' : ''; ?>><?php $wc_pythia_google_auth_api->google_authorized() ? esc_attr_e( 'Re-Authenticate', 'wc-pythia' ) : esc_attr_e( 'Authenticate', 'wc-pythia' ); ?></button>

									<ol class="py-passed">
										<span class="py-indicator__circle <?php echo $authenticate_step && ! $google_accounts ? 'active' : ''; ?>"></span>
										<span class="py-indicator__circle <?php echo $project_step ? 'active' : ''; ?>"></span>
									</ol>

									<label for="ga_source_web_id"><?php esc_html_e( 'Select your GA project', 'wc-pythia' ); ?></label>
								<?php if ( $project_step ) : ?>
									<?php if ( ! is_wp_error( $google_accounts ) && $google_accounts ) : ?>
										<select name="ga_source_web_id">
										<?php foreach ( $google_accounts as $ua_view_id => $ua_name ) : ?>
											<option value="<?php echo esc_attr( $ua_view_id ); ?>"><?php echo esc_html( $ua_name ); ?></option>
										<?php endforeach; ?>
										</select>
										<button name="pythia_submit_ga_id" id="pythia_submit_ga_id" class="btn btn-py "><?php esc_html_e( 'Connect', 'wc-pythia' ); ?></button>
									<?php else : ?>
										<p><?php esc_html_e( 'There is no Google Analytics account, please create a Google Analytics account first to relate it with your Pythia project.', 'wc-pythia' ); ?></p>
									<?php endif; ?>
								<?php elseif ( $authenticate_step ) : ?>
									<select class="form-control" type="text" disabled></select>
									<button type="submit" class="btn btn-py disabled"><?php esc_html_e( 'Connect', 'wc-pythia' ); ?></button>
								<?php endif; ?>

							</div>
				</form>
			</div>
		</div>
	</div>
</section>

<?php wc_pythia()->get_template( 'partials/footer' ); ?>
