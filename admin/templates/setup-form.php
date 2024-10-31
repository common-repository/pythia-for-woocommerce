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
?>
<?php wc_pythia()->get_template( 'partials/header', array( 'robot' => true ) ); ?>

<?php wc_pythia()->get_template( 'partials/wizard-steps', array( 'current' => 'register' ) ); ?>

	<section class="sign-up">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-10 col-12">

					<div class="col-md-6 col-12 form-content">
						<form id="wc_pythia_sign_up" action="" method="post">
							<div class="form-group">
								<label for="first_name"><?php esc_html_e( 'First Name', 'wc-pythia' ); ?></label>
								<input id="first_name" name="first_name" title="<?php esc_attr_e( 'Please enter your first name (at least 3 characters)', 'wc-pythia' ); ?>" class="form-control" value="<?php echo esc_attr( $current_user->first_name ); ?>" required minlength="3">
							</div>

							<div class="form-group">
								<label for="last_name"><?php esc_html_e( 'Last Name', 'wc-pythia' ); ?></label>
								<input id="last_name" name="last_name" title="<?php esc_attr_e( 'Please enter your last name (at least 3 characters)', 'wc-pythia' ); ?>" class="form-control" value="<?php echo esc_attr( $current_user->last_name ); ?>" required minlength="3">

							</div>

							<div class="form-group">
								<label for="project_name"><?php esc_html_e( 'Project Name', 'wc-pythia' ); ?></label>
								<input id="project_name" name="project_name" title="<?php esc_attr_e( 'Please enter your project name (at least 3 characters)', 'wc-pythia' ); ?>" class="form-control required" value="<?php echo esc_attr( get_bloginfo('name') ); ?>" minlength="3">

							</div>

							<div class="form-group">
								<label for="email"><?php esc_html_e( 'Email', 'wc-pythia' ); ?></label>
								<input id="email" type="email" name="email" autocomplete="username email" class="form-control required" value="<?php echo esc_attr( $current_user->user_email ); ?>" minlength="3">
							</div>

							<div class="form-group">
								<label for="password"><?php esc_html_e( 'Password', 'wc-pythia' ); ?></label>
								<?php $password = wp_generate_password( 12 ); ?>
								<input type="hidden" name="password1" id="password1" value="" data-pw="<?php echo esc_attr( $password ); ?>" autocomplete="new-password" class="py-form__control">
								<div class="row">
									<div class="col-7">
										<input type="text" name="password" id="password" autocomplete="new-password" class="form-control" value="<?php echo esc_attr( $password ); ?>">
										<div id="password-strength"></div>
									</div>
									<div class="col-5">
										<button class="btn btn-grey pythia-generate-pw"><?php esc_html_e( 'Generate Password', 'wc-pythia' ); ?></button>
									</div>
								</div>
								<small><?php echo esc_html( wp_get_password_hint() ); ?></small>
							</div>

							<div class="form-group">
								<button type="submit" class="btn btn-py width-40"><?php esc_html_e( 'Register', 'wc-pythia' ); ?></button>
								<small class="form-text"><?php esc_html_e( 'Already have an account? go to login to configure your account.', 'wc-pythia' ); ?> <a href="<?php echo esc_attr( wc_pythia()->get_login_url() ); ?>"><?php esc_html_e( 'Login', 'wc-pythia' ); ?></a>.</small>
							</div>
						</form>
					</div>

					<div class="col-md-6 col-sm-12 col-xs-12 welcome">
						<h2><?php esc_html_e( 'Welcome! Let\'s turn on your Pythia account.', 'wc-pythia' ); ?></h2>
						<p><?php esc_html_e( 'Here in Pythia, we help you learn more about your customers & understand how your business is going, so we can suggest actions to make it better. This wizard will help you create your account and start tracking your WooCommerce information.', 'wc-pythia' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</section> <!-- /content section -->

<?php wc_pythia()->get_template( 'partials/footer' ); ?>
