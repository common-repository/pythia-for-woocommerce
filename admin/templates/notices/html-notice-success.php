<?php
/**
 * Template for successful messages
 *
 * @package WC_Pythia\Templates\Admin\Notices
 * @since 1.1.2
 */

defined( 'ABSPATH' ) || exit;
$messages = empty( $messages ) ? array( __( 'Empty Message.', 'wc-pythia' ) ) : $messages;
?>
<?php foreach ( $messages as $message ) : ?>
<div class="py-alert py-alert-success">
	<span class="py-alert__icon fas fa-exclamation-circle"></span> <span class="py-success-message"><?php echo esc_html( $message ); ?></span>
	<a class="btn-close pythia-notice-close"><i class="fas fa-times-circle"></i></a>
</div>
<?php endforeach; ?>
