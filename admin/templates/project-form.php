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
<?php wc_pythia()->get_template( 'partials/header' ); ?>

<section class="content-center-py">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-8 col-12">
				<div class="group-register w-330">
					<h2><?php esc_html_e( 'Select Your Project', 'wc-pythia' ); ?></h2>
					<p><?php esc_html_e( 'The Pythia Bot project selected will be synchronized with this Website.', 'wc-pythia' ); ?></p>
					<form id="wc_pythia_project_settings" action="" method="post">
						<?php wp_nonce_field( 'pythia-login-nonce' ); ?>

						<?php do_action( 'do_projects_combobox' ); ?>

						<div class="form-group">
							<button type="submit" name="submit-project" id="submit-project" class="btn btn-py width-40"><?php esc_attr_e( 'Save', 'wc-pythia' ); ?></button>
						</div>

					</form>
				</div>
			</div>
		</div>
	</div>
</section>

<?php wc_pythia()->get_template( 'partials/footer' ); ?>
