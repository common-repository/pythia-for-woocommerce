<?php
/**
 * Template: Header
 *
 * @package PythiaForWoocommerce/Admin/Templates/Partials
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<main class="pythia <?php echo ! empty( $robot ) ? 'pythia-main' : ''; ?>">
	<header class="header-logo">
		<div class="container-fluid">
			<div class="row">
				<div class="col">
					<img src="<?php echo esc_attr( WC_PYTHIA__PLUGIN_URL ); ?>admin/assets/img/logo.svg" alt="Pythia">
				</div>
			</div>
		</div>
	</header>
	<!-- Py Header-->
	<?php wc_pythia()->get_template( 'partials/notices' ); ?>
