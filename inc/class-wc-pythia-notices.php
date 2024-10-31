<?php
/**
 * Manage WC_Pythia admin notices
 *
 * @package WC_Pythia\Notices
 * @since 1.1.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Provide the options to add admin notices using the error or success template depending of the notice
 *
 * @since 1.1.2
 */
class WC_Pythia_Notices {

	/**
	 * Print successfull messages
	 *
	 * @since 1.1.2
	 *
	 * @param array $messages Array of messages to be printed.
	 * @return void
	 */
	public static function print_success_notice( $messages ) {
		if ( ! is_array( $messages ) ) {
			$messages = array( $messages );
		}
		ob_start();
		include WC_PYTHIA__PLUGIN_DIR . 'admin/templates/notices/html-notice-success.php';
		$install_notice = ob_get_contents();
		ob_end_clean();
		echo $install_notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Print error messages
	 *
	 * @since 1.1.2
	 *
	 * @param array $messages Array of messages to be printed.
	 * @return void
	 */
	public static function print_error_notice( $messages ) {
		if ( ! is_array( $messages ) ) {
			$messages = array( $messages );
		}
		ob_start();
		include WC_PYTHIA__PLUGIN_DIR . 'admin/templates/notices/html-notice-error.php';
		$install_notice = ob_get_contents();
		ob_end_clean();
		echo $install_notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
