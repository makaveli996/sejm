<?php
/**
 * Cron Scheduler
 *
 * @package MP_Directory
 */

namespace MP_Directory;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cron
 */
class Cron {

	/**
	 * Cron hook name
	 */
	const CRON_HOOK = 'mp_directory_cron_import';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( self::CRON_HOOK, array( $this, 'run_scheduled_import' ) );
		add_action( 'mp_directory_cron_settings_changed', array( $this, 'reschedule' ) );
		
		// Schedule on plugin load if needed
		add_action( 'init', array( $this, 'maybe_schedule' ) );
	}

	/**
	 * Maybe schedule cron if enabled
	 */
	public function maybe_schedule() {
		$enable_cron = Settings::get( 'enable_cron', false );
		
		if ( $enable_cron && ! wp_next_scheduled( self::CRON_HOOK ) ) {
			$this->schedule();
		} elseif ( ! $enable_cron && wp_next_scheduled( self::CRON_HOOK ) ) {
			$this->unschedule();
		}
	}

	/**
	 * Schedule the cron event
	 */
	public function schedule() {
		$interval = Settings::get( 'cron_interval', 'daily' );
		
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), $interval, self::CRON_HOOK );
		}
	}

	/**
	 * Unschedule the cron event
	 */
	public function unschedule() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}

	/**
	 * Reschedule cron when settings change
	 *
	 * @param array $new_settings New settings.
	 */
	public function reschedule( $new_settings ) {
		// Unschedule existing
		$this->unschedule();

		// Schedule if enabled
		if ( isset( $new_settings['enable_cron'] ) && $new_settings['enable_cron'] ) {
			$this->schedule();
		}
	}

	/**
	 * Run scheduled import
	 */
	public function run_scheduled_import() {
		// Log start
		error_log( 'MP Directory: Starting scheduled import at ' . current_time( 'mysql' ) );

		$importer = new Importer();
		$offset   = 0;
		$complete = false;

		// Import in batches until complete
		while ( ! $complete ) {
			$result = $importer->run_import( 0, $offset );

			if ( is_wp_error( $result ) ) {
				error_log( 'MP Directory: Import error - ' . $result->get_error_message() );
				break;
			}

			$offset   = $result['offset'];
			$complete = $result['complete'];

			// Log progress
			error_log(
				sprintf(
					'MP Directory: Batch complete - Imported: %d, Updated: %d, Offset: %d',
					$result['imported'],
					$result['updated'],
					$offset
				)
			);

			// Prevent infinite loops
			if ( $offset > 10000 ) {
				error_log( 'MP Directory: Safety limit reached (10000 MPs). Stopping.' );
				break;
			}
		}

		// Clear preview cache after import
		$importer->clear_preview_cache();

		// Log completion
		error_log( 'MP Directory: Scheduled import completed at ' . current_time( 'mysql' ) );
	}

	/**
	 * Get next scheduled run time
	 *
	 * @return int|false Timestamp or false if not scheduled.
	 */
	public static function get_next_run() {
		return wp_next_scheduled( self::CRON_HOOK );
	}

	/**
	 * Get human-readable next run time
	 *
	 * @return string
	 */
	public static function get_next_run_display() {
		$timestamp = self::get_next_run();
		
		if ( ! $timestamp ) {
			return __( 'Not scheduled', 'mp-directory' );
		}

		return sprintf(
			/* translators: %s: human-readable time difference */
			__( 'In %s', 'mp-directory' ),
			human_time_diff( time(), $timestamp )
		);
	}
}
