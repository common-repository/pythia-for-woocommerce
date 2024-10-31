<?php
/**
 *
 */
class WC_Pythia_Sync_Scheduler {

	const SCHEDULE_HOOK  = 'pythia_synchronization';
	const SCHEDULE_GROUP = 'pythia_schedules';

	public function __construct() {}

	/**
	 * Schedule recurring sync process
	 *
	 * @param integer $timestamp  Timestamp when the process will run the first time.
	 * @param integer $interval_in_seconds Interval in seconds for each repetition.
	 * @param array   $args Arguments sent to hooked method.
	 * @throws RuntimeException Exception fired if the schedule is not added.
	 * @return void
	 */
	public static function schedule_recurring( $timestamp, $interval_in_seconds = 60, $args = array() ) {
		try {
			WC()->queue()->schedule_recurring( $timestamp, $interval_in_seconds, self::SCHEDULE_HOOK, $args, self::SCHEDULE_GROUP );
		} catch ( RuntimeException $e ) {
			throw $e;
		}
	}

	/**
	 * Schedule single sync process
	 *
	 * @param integer $timestamp Timestamp when the process will run.
	 * @param array   $args Araguments sent to hooked method.
	 * @throws RuntimeException Exception fired if the schedule is not added.
	 * @return void
	 */
	public static function schedule_single( $timestamp, $args = array() ) {
		try {
			WC()->queue()->schedule_single( $timestamp, self::SCHEDULE_HOOK, $args, self::SCHEDULE_GROUP );
		} catch ( RuntimeException $e ) {
			throw $e;
		}
	}

	/**
	 * Cancel all Pythia Sync Schedules
	 *
	 * By default it uses an empty array of args, this means that only schedules with empty arguments will be cancelled.
	 * If you want to cancell all schedules at once, you need to pass $args parameter with "null" calue.
	 *
	 * @since 1.1.1
	 *
	 * @param array|null $args Array of arguments to filter schedulers or NULL to avoid filtering by args.
	 * @throws RuntimeException Exception fired if the schedule is not canceled.
	 * @return void
	 */
	public static function cancel_schedule( $args = array() ) {
		try {
			WC()->queue()->cancel_all( self::SCHEDULE_HOOK, $args );
		} catch ( RuntimeException $e ) {
			throw $e;
		}
	}

	/**
	 * Check if the action is scheduled
	 *
	 * By default it uses an empty array as args parameter, this means that only schedules with empty arguments will be checked for next run.
	 * If you want to check next run of recurring or single schedule you need to send args parameter as "null" value.
	 *
	 * @param array|null $args Array of arguments to filter results or NULL to avoid filtering by args.
	 * @return boolean
	 */
	public static function scheduled( $args = array() ) {
		$next_run = self::schedule_next_run( $args );
		return ! empty( $next_run );
	}

	/**
	 * Check if there is a scheduled action running
	 *
	 * @since 1.1.2
	 *
	 * @param array $args Possible arguments, with their default values.
	 *        'hook' => '' - the name of the action that will be triggered.
	 *        'args' => null - the args array that will be passed with the action.
	 *        'date' => null - the scheduled date of the action. Expects a DateTime object, a unix timestamp, or a string that can parsed with strtotime(). Used in UTC timezone.
	 *        'date_compare' => '<=' - operator for testing "date". accepted values are '!=', '>', '>=', '<', '<=', '='.
	 *        'modified' => null - the date the action was last updated. Expects a DateTime object, a unix timestamp, or a string that can parsed with strtotime(). Used in UTC timezone.
	 *        'modified_compare' => '<=' - operator for testing "modified". accepted values are '!=', '>', '>=', '<', '<=', '='.
	 *        'group' => '' - the group the action belongs to.
	 *        'status' => '' - ActionScheduler_Store::STATUS_COMPLETE or ActionScheduler_Store::STATUS_PENDING.
	 *        'claimed' => null - TRUE to find claimed actions, FALSE to find unclaimed actions, a string to find a specific claim ID.
	 *        'per_page' => 5 - Number of results to return.
	 *        'offset' => 0.
	 *        'orderby' => 'date' - accepted values are 'hook', 'group', 'modified', or 'date'.
	 *        'order' => 'ASC'.
	 * @return boolean
	 */
	public static function is_schedule_running( $args = array() ) {
		$status = 'in-progress';

		if ( class_exists( 'ActionScheduler_Store' ) ) {
			$status = ActionScheduler_Store::STATUS_RUNNING;
		}

		$defaults  = array(
			'status' => $status,
		);
		$args      = wp_parse_args( $args, $defaults );
		$schedules = self::search( $args, 'ids' );
		return is_array( $schedules ) && count( $schedules ) > 0;
	}

	/**
	 * Search scheduled actions for sync process
	 *
	 * @param array  $args Possible arguments, with their default values.
	 *        'hook' => '' - the name of the action that will be triggered.
	 *        'args' => null - the args array that will be passed with the action.
	 *        'date' => null - the scheduled date of the action. Expects a DateTime object, a unix timestamp, or a string that can parsed with strtotime(). Used in UTC timezone.
	 *        'date_compare' => '<=' - operator for testing "date". accepted values are '!=', '>', '>=', '<', '<=', '='.
	 *        'modified' => null - the date the action was last updated. Expects a DateTime object, a unix timestamp, or a string that can parsed with strtotime(). Used in UTC timezone.
	 *        'modified_compare' => '<=' - operator for testing "modified". accepted values are '!=', '>', '>=', '<', '<=', '='.
	 *        'group' => '' - the group the action belongs to.
	 *        'status' => '' - ActionScheduler_Store::STATUS_COMPLETE or ActionScheduler_Store::STATUS_PENDING.
	 *        'claimed' => null - TRUE to find claimed actions, FALSE to find unclaimed actions, a string to find a specific claim ID.
	 *        'per_page' => 5 - Number of results to return.
	 *        'offset' => 0.
	 *        'orderby' => 'date' - accepted values are 'hook', 'group', 'modified', or 'date'.
	 *        'order' => 'ASC'.
	 * @param string $return_format OBJECT, ARRAY_A, or ids.
	 * @return array
	 */
	public static function search( $args = array(), $return_format = OBJECT ) {

		$defaults = array(
			'hook'  => self::SCHEDULE_HOOK,
			'group' => self::SCHEDULE_GROUP,
		);
		$args     = wp_parse_args( $args, $defaults );

		try {
			return WC()->queue()->search( $args, $return_format );
		} catch ( RuntimeException $e ) {
			throw $e;
		}

	}

	/**
	 * Get next run for sync process
	 *
	 * By default it uses an empty array as args parameter, this means that only schedules with empty arguments will be checked for next run.
	 * If you want to check next run of recurring or single schedule you need to send args parameter as "null" value.
	 *
	 * @param array|null $args Filter to a hook with matching args that will be passed to the job when it runs. NULL to avoid args filters.
	 * @return WC_DateTime|null The date and time for the next occurrence, or null if there is no pending scheduled action for the given hook
	 */
	public static function schedule_next_run( $args = array() ) {
		// If WooCommerce is not active, return null.
		if ( ! function_exists( 'WC' ) ) {
			return null;
		}

		try {
			return WC()->queue()->get_next( self::SCHEDULE_HOOK, $args );
		} catch ( RuntimeException $e ) {
			throw $e;
		}

	}
}
