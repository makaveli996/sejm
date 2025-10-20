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

	const TIMEOUT = 15;

	const MAX_RETRIES = 3;

	public function __construct() {
	}

	public function get_mps( $page = 1, $per_page = 100 ) {
		$base_url = Settings::get( 'api_base_url', '' );
		
		if ( empty( $base_url ) ) {
			return new \WP_Error( 'no_api_url', __( 'Bazowy URL API nie jest skonfigurowany.', 'mp-directory' ) );
		}

		return $this->request( $base_url );
	}

	public function get_all_mps( $limit = 0 ) {
		$base_url = Settings::get( 'api_base_url', '' );
		
		if ( empty( $base_url ) ) {
			return new \WP_Error( 'no_api_url', __( 'Bazowy URL API nie jest skonfigurowany.', 'mp-directory' ) );
		}

		$all_mps = array();
		$page    = 1;
		$batch_size = Settings::get( 'import_batch_size', 100 );

		do {
			$response = $this->get_mps( $page, $batch_size );
			
			if ( is_wp_error( $response ) ) {
				return $response;
			}

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

	private function request( $url, $args = array(), $attempt = 1 ) {
		$api_key = Settings::get( 'api_key', '' );

		$default_args = array(
			'timeout' => self::TIMEOUT,
			'headers' => array(
				'Accept' => 'application/json',
			),
		);

		if ( ! empty( $api_key ) ) {
			$default_args['headers']['Authorization'] = 'Bearer ' . $api_key;
		}

		$args = wp_parse_args( $args, $default_args );

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			if ( $attempt < self::MAX_RETRIES ) {
				sleep( pow( 2, $attempt - 1 ) );
				return $this->request( $url, $args, $attempt + 1 );
			}
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		
		if ( in_array( $status_code, array( 429, 500, 502, 503, 504 ), true ) && $attempt < self::MAX_RETRIES ) {
			sleep( pow( 2, $attempt ) );
			return $this->request( $url, $args, $attempt + 1 );
		}

		if ( $status_code < 200 || $status_code >= 300 ) {
			return new \WP_Error(
				'http_error',
				sprintf(
					__( 'Żądanie API nie powiodło się z kodem statusu %d', 'mp-directory' ),
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
					__( 'Nie udało się przeanalizować odpowiedzi JSON: %s', 'mp-directory' ),
					json_last_error_msg()
				)
			);
		}

		return $data;
	}

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
			'message' => __( 'Połączenie z API pomyślne!', 'mp-directory' ),
		);
	}
}
