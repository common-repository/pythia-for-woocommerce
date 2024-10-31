<?php
/**
 * Template: Popup Dialog
 *
 * @package PythiaForWoocommerce/Admin/Templates/Partials
 * @version 1.1.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="dialog-confirm" title="<?php echo esc_attr( ( $title ) ? $title : '' ); ?>" style="display: none;">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
		<?php echo esc_html( ( $content ) ? $content : '' ); ?>
	</p>
</div>
