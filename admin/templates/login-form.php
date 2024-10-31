<?php
/**
 * Template: Login
 *
 * @package PythiaForWoocommerce/Admin/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php wc_pythia()->get_template( 'partials/header', array( 'robot' => true ) ); ?>

<section class="sign-up">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-10 col-12">

				<div class="col-md-6 col-12 form-content">
					<form id="wc_pythia_sign_up" action="" method="post">
						<?php wp_nonce_field( 'pythia-login-nonce' ); ?>
						<div class="form-group">
							<label for="email"><?php esc_html_e( 'Email', 'wc-pythia' ); ?></label>
							<input id="email" type="email" name="email" autocomplete="username email" class="form-control required" minlength="3">
						</div>
						<div class="form-group">
							<label for="password"><?php esc_html_e( 'Password', 'wc-pythia' ); ?></label>
							<input type="password" name="password" id="password" autocomplete="new-password" class="form-control required" minlength="8">
							<p><small><?php esc_html_e( 'If you forgot your password, please install Pythia APP from Apple Store or Google Play Store to reset it.', 'wc-pythia' ); ?></small></p>
						</div>
						<div class="form-group">
							<button type="submit" name="submit-login" id="submit-login" class="btn btn-py width-40"><?php esc_html_e( 'Login', 'wc-pythia' ); ?></button>
							<small class="form-text"><?php esc_html_e( 'Do not have an account? go to register to create one.', 'wc-pythia' ); ?> <a href="<?php echo esc_url( wc_pythia()->get_setup_url() ); ?>"><?php esc_html_e( 'Register', 'wc-pythia' ); ?></a>.</small>
						</div>
					</form>
				</div>

				<div class="col-md-6 col-sm-12 col-xs-12 welcome">
					<h2><?php esc_html_e( 'Welcome Back!', 'wc-pythia' ); ?></h2>
					<p><?php esc_html_e( 'Thank you for choosing Pythia. Please insert your email and password to login and store your settings automatically.', 'wc-pythia' ); ?></p>
				</div>
			</div><!-- py-content 2 -->
		</div>
	</div>
</section>

<?php wc_pythia()->get_template( 'partials/footer' ); ?>
