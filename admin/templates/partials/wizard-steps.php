<?php
/**
 * Template: Wizard Steps
 *
 * @package PythiaForWoocommerce/Admin/Templates/Partials
 * @version 1.1.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section>
	<div class="container">
		<div class="row">
			<div class="col pythia">
				<div class="py-indicator">
					<ul>
						<li class="<?php echo ( 'register' === $current ) ? 'py-active' : ''; ?>">
							<span class="py-indicator__circle"></span> <a class="py-indicator__act" href="#"><?php esc_html_e( 'Register', 'wc-pythia' ); ?></a>
						</li>
						<li class="<?php echo ( 'connect' === $current ) ? 'py-active' : ''; ?>">
							<span class="py-indicator__circle"></span> <a class="py-indicator__act" href="#"><?php esc_html_e( 'Connect', 'wc-pythia' ); ?></a>
						</li>
						<li class="<?php echo ( 'sync' === $current ) ? 'py-active' : ''; ?>">
							<span class="py-indicator__circle"></span> <a class="py-indicator__act" href="#"><?php esc_html_e( 'Sync', 'wc-pythia' ); ?></a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section><!-- /steps section -->
