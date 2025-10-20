<?php
/**
 * REST API Client
 *
 * @package MP_Directory
 */

namespace MP_Directory;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class REST
 */
class REST {

	/**
	 * Request timeout in seconds
	 */
	const TIMEOUT = 15;

	/**
	 * Maximum retry attempts
	 */
	const MAX_RETRIES = 3;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Hook for any initialization if needed
	}

	/**
	 * Get MPs from API
	 *
	 * @param int $page     Page number.
	 * @param int $per_page Items per page.
	 * @return array|WP_Error
	 */
	public function get_mps( $page = 1, $per_page = 100 ) {
		$base_url = Settings::get( 'api_base_url', '' );
		
		if ( empty( $base_url ) ) {
			return new \WP_Error( 'no_api_url', __( 'API Base URL is not configured.', 'mp-directory' ) );
		}

		return $this->request( $base_url );
	}

	/**
	 * Get all MPs (handles pagination automatically)
	 *
	 * @param int $limit Maximum number of MPs to fetch (0 = no limit).
	 * @return array|WP_Error
	 */
	public function get_all_mps( $limit = 0 ) {
		$base_url = Settings::get( 'api_base_url', '' );
		
		if ( empty( $base_url ) ) {
			return new \WP_Error( 'no_api_url', __( 'API Base URL is not configured.', 'mp-directory' ) );
		}

		$all_mps = array();
		$page    = 1;
		$batch_size = Settings::get( 'import_batch_size', 100 );

		do {
			$response = $this->get_mps( $page, $batch_size );
			
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			// Handle different response structures
			$mps = array();
			if ( isset( $response['data'] ) && is_array( $response['data'] ) ) {
				$mps = $response['data'];
			} elseif ( is_array( $response ) ) {
				$mps = $response;
			}

			if ( empty( $mps ) ) {
				break;
			}

			$all_mps = array_merge( $all_mps, $mps );

			// Check limit
			if ( $limit > 0 && count( $all_mps ) >= $limit ) {
				$all_mps = array_slice( $all_mps, 0, $limit );
				break;
			}

			// Check if there are more pages
			if ( ! isset( $response['pagination']['has_next'] ) || ! $response['pagination']['has_next'] ) {
				// If pagination info not available, break if we got less than requested
				if ( count( $mps ) < $batch_size ) {
					break;
				}
			} else {
				if ( ! $response['pagination']['has_next'] ) {
					break;
				}
			}

			$page++;

		} while ( true );

		return $all_mps;
	}

	/**
	 * Make HTTP request with retry logic
	 *
	 * @param string $url     Request URL.
	 * @param array  $args    Request arguments.
	 * @param int    $attempt Current attempt number.
	 * @return array|WP_Error
	 */
	private function request( $url, $args = array(), $attempt = 1 ) {
		$api_key = Settings::get( 'api_key', '' );

		// Build request arguments
		$default_args = array(
			'timeout' => self::TIMEOUT,
			'headers' => array(
				'Accept' => 'application/json',
			),
		);

		// Add API key if configured
		if ( ! empty( $api_key ) ) {
			$default_args['headers']['Authorization'] = 'Bearer ' . $api_key;
		}

		$args = wp_parse_args( $args, $default_args );

		// Make the request
		$response = wp_remote_get( $url, $args );

		// Check for errors
		if ( is_wp_error( $response ) ) {
			if ( $attempt < self::MAX_RETRIES ) {
				// Exponential backoff
				sleep( pow( 2, $attempt - 1 ) );
				return $this->request( $url, $args, $attempt + 1 );
			}
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		
		// Handle rate limiting and server errors with retry
		if ( in_array( $status_code, array( 429, 500, 502, 503, 504 ), true ) && $attempt < self::MAX_RETRIES ) {
			// Exponential backoff
			sleep( pow( 2, $attempt ) );
			return $this->request( $url, $args, $attempt + 1 );
		}

		// Handle non-200 responses
		if ( $status_code < 200 || $status_code >= 300 ) {
			return new \WP_Error(
				'http_error',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'API request failed with status code %d', 'mp-directory' ),
					$status_code
				)
			);
		}

		// Parse JSON response
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new \WP_Error(
				'json_error',
				sprintf(
					/* translators: %s: JSON error message */
					__( 'Failed to parse JSON response: %s', 'mp-directory' ),
					json_last_error_msg()
				)
			);
		}

		return $data;
	}

	/**
	 * Test API connection
	 *
	 * @return array Array with 'success' (bool) and 'message' (string).
	 */
	public function test_connection() {
		$response = $this->get_mps( 1, 1 );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'API connection successful!', 'mp-directory' ),
		);
	}
}
