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

	const CRON_HOOK = 'mp_directory_cron_import';

	public function __construct() {
		add_action( self::CRON_HOOK, array( $this, 'run_scheduled_import' ) );
		add_action( 'mp_directory_cron_settings_changed', array( $this, 'reschedule' ) );
		add_action( 'init', array( $this, 'maybe_schedule' ) );
	}

	public function maybe_schedule() {
		$enable_cron = Settings::get( 'enable_cron', false );
		
		if ( $enable_cron && ! wp_next_scheduled( self::CRON_HOOK ) ) {
			$this->schedule();
		} elseif ( ! $enable_cron && wp_next_scheduled( self::CRON_HOOK ) ) {
			$this->unschedule();
		}
	}

	public function schedule() {
		$interval = Settings::get( 'cron_interval', 'daily' );
		
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), $interval, self::CRON_HOOK );
		}
	}

	public function unschedule() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}

	public function reschedule( $new_settings ) {
		$this->unschedule();

		if ( isset( $new_settings['enable_cron'] ) && $new_settings['enable_cron'] ) {
			$this->schedule();
		}
	}

	public function run_scheduled_import() {
		error_log( 'MP Directory: Starting scheduled import at ' . current_time( 'mysql' ) );

		$importer = new Importer();
		$offset   = 0;
		$complete = false;

		while ( ! $complete ) {
			$result = $importer->run_import( 0, $offset );

			if ( is_wp_error( $result ) ) {
				error_log( 'MP Directory: Import error - ' . $result->get_error_message() );
				break;
			}

			$offset   = $result['offset'];
			$complete = $result['complete'];

			error_log(
				sprintf(
					'MP Directory: Batch complete - Imported: %d, Updated: %d, Offset: %d',
					$result['imported'],
					$result['updated'],
					$offset
				)
			);

			if ( $offset > 10000 ) {
				error_log( 'MP Directory: Safety limit reached (10000 MPs). Stopping.' );
				break;
			}
		}

		$importer->clear_preview_cache();

		error_log( 'MP Directory: Scheduled import completed at ' . current_time( 'mysql' ) );
	}

	public static function get_next_run() {
		return wp_next_scheduled( self::CRON_HOOK );
	}

	public static function get_next_run_display() {
		$timestamp = self::get_next_run();
		
		if ( ! $timestamp ) {
			return __( 'Nie zaplanowano', 'mp-directory' );
		}

		return sprintf(
			__( 'Za %s', 'mp-directory' ),
			human_time_diff( time(), $timestamp )
		);
	}
}
