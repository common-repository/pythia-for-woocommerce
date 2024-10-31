<?php
/**
 * Template: Projects combobox
 *
 * @package PythiaForWoocommerce/Admin/Templates/Partials
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="form-group">
	<label for="pythia_project"><?php esc_html_e( 'Project', 'wc-pythia' ); ?> * </label>
	<select id="pythia_project" name="pythia_project" class="form-control required">
		<?php if ( ! empty( $projects ) ) : ?>

			<?php if ( count( $projects ) > 1 ) : ?>
				<option value=""><?php esc_html_e( 'Select Project', 'wc-pythia' ); ?></option>
			<?php endif; ?>

			<?php foreach ( $projects as $project ) : ?>
				<option value="<?php echo esc_attr( $project->id ); ?>" <?php selected( ( count( $projects ) === 1 ) ); ?>><?php echo esc_html( $project->name ); ?></option>
			<?php endforeach; ?>
		<?php endif; ?>
	</select>
</div>
<div class="form-group">
	<label for="pythia_source"><?php esc_html_e( 'Source', 'wc-pythia' ); ?> * </label>
	<select id="pythia_source" name="pythia_source" class="form-control required">
		<option value=""><?php esc_html_e( 'Select Source', 'wc-pythia' ); ?></option>
	</select>
</div>
