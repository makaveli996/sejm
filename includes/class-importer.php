<?php
/**
 * Importer
 *
 * @package MP_Directory
 */

namespace MP_Directory;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Importer
 */
class Importer {

	/**
	 * Transient key for preview cache
	 */
	const PREVIEW_CACHE_KEY = 'mp_directory_preview_cache';

	/**
	 * REST API client
	 *
	 * @var REST
	 */
	private $rest;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->rest = new REST();
		
		add_action( 'wp_ajax_mp_directory_preview', array( $this, 'ajax_preview' ) );
		add_action( 'wp_ajax_mp_directory_import', array( $this, 'ajax_import' ) );
	}

	/**
	 * AJAX handler for preview
	 */
	public function ajax_preview() {
		check_ajax_referer( 'mp_directory_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'mp-directory' ) ) );
		}

		$force_refresh = isset( $_POST['force_refresh'] ) && $_POST['force_refresh'] === 'true';

		$preview_data = $this->get_preview_data( $force_refresh );

		if ( is_wp_error( $preview_data ) ) {
			wp_send_json_error( array( 'message' => $preview_data->get_error_message() ) );
		}

		wp_send_json_success( $preview_data );
	}

	/**
	 * AJAX handler for import
	 */
	public function ajax_import() {
		check_ajax_referer( 'mp_directory_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'mp-directory' ) ) );
		}

		$batch  = isset( $_POST['batch'] ) ? absint( $_POST['batch'] ) : 0;
		$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;

		$result = $this->run_import( $batch, $offset );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Get preview data with caching
	 *
	 * @param bool $force_refresh Force refresh cache.
	 * @return array|WP_Error
	 */
	public function get_preview_data( $force_refresh = false ) {
		// Try to get cached data
		if ( ! $force_refresh ) {
			$cached = get_transient( self::PREVIEW_CACHE_KEY );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		// Fetch fresh data
		$response = $this->rest->get_mps( 1, 20 ); // Preview first 20 items

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
			return new \WP_Error( 'no_data', __( 'No MP data found in API response.', 'mp-directory' ) );
		}

		// Format for preview
		$preview = array(
			'items'      => $mps,
			'total'      => count( $mps ),
			'fetched_at' => current_time( 'mysql' ),
		);

		// Cache the preview
		$cache_ttl = Settings::get( 'preview_cache_ttl', 20 ) * MINUTE_IN_SECONDS;
		set_transient( self::PREVIEW_CACHE_KEY, $preview, $cache_ttl );

		return $preview;
	}

	/**
	 * Run import (batch processing)
	 *
	 * @param int $batch  Batch number.
	 * @param int $offset Starting offset.
	 * @return array|WP_Error
	 */
	public function run_import( $batch = 0, $offset = 0 ) {
		$batch_size = Settings::get( 'import_batch_size', 100 );
		$page       = floor( $offset / $batch_size ) + 1;

		// Fetch data from API
		$response = $this->rest->get_mps( $page, $batch_size );

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
			return array(
				'imported'  => 0,
				'updated'   => 0,
				'offset'    => $offset,
				'complete'  => true,
				'message'   => __( 'No more MPs to import.', 'mp-directory' ),
			);
		}

		// Import each MP
		$imported = 0;
		$updated  = 0;

		foreach ( $mps as $mp_data ) {
			$result = $this->import_single_mp( $mp_data );
			
			if ( ! is_wp_error( $result ) ) {
				if ( $result['is_new'] ) {
					$imported++;
				} else {
					$updated++;
				}
			}
		}

		$new_offset = $offset + count( $mps );
		$has_more   = count( $mps ) >= $batch_size;

		return array(
			'imported' => $imported,
			'updated'  => $updated,
			'offset'   => $new_offset,
			'complete' => ! $has_more,
			'message'  => sprintf(
				/* translators: 1: imported count, 2: updated count */
				__( 'Imported %1$d new MPs, updated %2$d existing MPs.', 'mp-directory' ),
				$imported,
				$updated
			),
		);
	}

	/**
	 * Import a single MP
	 *
	 * @param array $data MP data from API.
	 * @return array|WP_Error Array with 'post_id' and 'is_new', or WP_Error.
	 */
	public function import_single_mp( $data ) {
		// Extract API ID
		$api_id = isset( $data['id'] ) ? $data['id'] : null;
		
		if ( empty( $api_id ) ) {
			return new \WP_Error( 'no_api_id', __( 'MP data missing required "id" field.', 'mp-directory' ) );
		}

		// Check if MP already exists
		$existing_post = $this->get_mp_by_api_id( $api_id );
		$is_new        = ! $existing_post;

		// Map data to post fields
		$mapped = $this->map_api_data( $data );

		// Prepare post data
		$post_data = array(
			'post_type'    => 'mp',
			'post_status'  => 'publish',
			'post_title'   => $mapped['post_title'],
			'post_content' => $mapped['post_content'],
			'post_excerpt' => $mapped['post_excerpt'],
		);

		if ( $existing_post ) {
			$post_data['ID'] = $existing_post->ID;
			$post_id         = wp_update_post( $post_data );
		} else {
			$post_id = wp_insert_post( $post_data );
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Store API ID
		update_post_meta( $post_id, '_mp_api_id', $api_id );

		// Update ACF fields if available
		if ( function_exists( 'update_field' ) ) {
			foreach ( $mapped['acf_fields'] as $field_key => $field_value ) {
				update_field( $field_key, $field_value, $post_id );
			}
		}

		// Handle featured image
		if ( ! empty( $mapped['photo_url'] ) ) {
			$this->set_featured_image( $post_id, $mapped['photo_url'], $mapped['post_title'] );
		}

		return array(
			'post_id' => $post_id,
			'is_new'  => $is_new,
		);
	}

	/**
	 * Get MP post by API ID
	 *
	 * @param mixed $api_id API ID.
	 * @return WP_Post|null
	 */
	private function get_mp_by_api_id( $api_id ) {
		$query = new \WP_Query(
			array(
				'post_type'      => 'mp',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => '_mp_api_id',
						'value' => $api_id,
					),
				),
				'fields'         => 'ids',
			)
		);

		if ( $query->have_posts() ) {
			return get_post( $query->posts[0] );
		}

		return null;
	}

	/**
	 * Map API data to WordPress post/ACF fields
	 *
	 * @param array $data API data.
	 * @return array Mapped data.
	 */
	private function map_api_data( $data ) {
		// Extract common fields
		$first_name  = isset( $data['firstName'] ) ? sanitize_text_field( $data['firstName'] ) : '';
		$last_name   = isset( $data['lastName'] ) ? sanitize_text_field( $data['lastName'] ) : '';
		$full_name   = isset( $data['firstLastName'] ) ? sanitize_text_field( $data['firstLastName'] ) : "$first_name $last_name";
		$party       = isset( $data['club'] ) ? sanitize_text_field( $data['club'] ) : '';
		$constituency = isset( $data['districtName'] ) ? sanitize_text_field( $data['districtName'] ) : '';
		$birthdate   = isset( $data['birthDate'] ) ? sanitize_text_field( $data['birthDate'] ) : '';
		$email       = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		$education   = isset( $data['educationLevel'] ) ? sanitize_text_field( $data['educationLevel'] ) : '';
		$profession  = isset( $data['profession'] ) ? sanitize_text_field( $data['profession'] ) : '';
		$photo_url   = isset( $data['photo'] ) ? esc_url_raw( $data['photo'] ) : '';
		
		// Build Sejm API photo URLs based on ID
		$api_id = isset( $data['id'] ) ? absint( $data['id'] ) : 0;
		$api_base_url = Settings::get( 'api_base_url', '' );
		$sejm_photo_url = '';
		$sejm_photo_mini_url = '';
		
		if ( ! empty( $api_id ) && ! empty( $api_base_url ) ) {
			// Extract term from base URL (e.g., "term10" from "https://api.sejm.gov.pl/sejm/term10/MP")
			if ( preg_match( '/term(\d+)/i', $api_base_url, $matches ) ) {
				$term = $matches[1];
				$sejm_photo_url = "https://api.sejm.gov.pl/sejm/term{$term}/MP/{$api_id}/photo";
				$sejm_photo_mini_url = "https://api.sejm.gov.pl/sejm/term{$term}/MP/{$api_id}/photo-mini";
			}
		}

		// Build post content
		$content_parts = array();
		if ( ! empty( $profession ) ) {
			$content_parts[] = '<p><strong>' . __( 'Profession:', 'mp-directory' ) . '</strong> ' . esc_html( $profession ) . '</p>';
		}
		if ( ! empty( $education ) ) {
			$content_parts[] = '<p><strong>' . __( 'Education:', 'mp-directory' ) . '</strong> ' . esc_html( $education ) . '</p>';
		}

		$post_content = implode( "\n", $content_parts );

		// Build excerpt
		$excerpt = sprintf(
			/* translators: 1: party name, 2: constituency name */
			__( 'Member of Parliament representing %2$s (%1$s)', 'mp-directory' ),
			$party,
			$constituency
		);

		// Prepare ACF fields
		$acf_fields = array(
			'mp_first_name'        => $first_name,
			'mp_last_name'         => $last_name,
			'mp_full_name'         => $full_name,
			'mp_party'             => $party,
			'mp_constituency'      => $constituency,
			'mp_birthdate'         => $birthdate,
			'mp_education'         => $education,
			'mp_photo_url'         => $photo_url,
			'mp_sejm_photo_url'    => $sejm_photo_url,
			'mp_sejm_photo_mini'   => $sejm_photo_mini_url,
		);

		// Add contacts
		$contacts = array();
		if ( ! empty( $email ) ) {
			$contacts[] = array(
				'label' => __( 'Email', 'mp-directory' ),
				'value' => $email,
				'type'  => 'email',
			);
		}
		if ( ! empty( $contacts ) ) {
			$acf_fields['mp_contacts'] = $contacts;
		}

		// Store unmapped fields in JSON
		$known_keys = array(
			'id', 'firstName', 'lastName', 'firstLastName', 'lastFirstName',
			'secondName', 'accusativeName', 'genitiveName',
			'club', 'districtName', 'districtNum', 'voivodeship',
			'birthDate', 'birthLocation', 'educationLevel', 'profession',
			'email', 'photo', 'numberOfVotes', 'active'
		);

		$extra_data = array_diff_key( $data, array_flip( $known_keys ) );
		if ( ! empty( $extra_data ) ) {
			$acf_fields['mp_extra_json'] = wp_json_encode( $extra_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		}

		return array(
			'post_title'   => $full_name,
			'post_content' => $post_content,
			'post_excerpt' => $excerpt,
			'acf_fields'   => $acf_fields,
			'photo_url'    => $photo_url,
		);
	}

	/**
	 * Set featured image from URL
	 *
	 * @param int    $post_id   Post ID.
	 * @param string $image_url Image URL.
	 * @param string $title     Image title.
	 * @return int|false Attachment ID or false on failure.
	 */
	private function set_featured_image( $post_id, $image_url, $title = '' ) {
		// Check if post already has thumbnail
		if ( has_post_thumbnail( $post_id ) ) {
			return get_post_thumbnail_id( $post_id );
		}

		// Download and attach image
		return mp_directory_sideload_image( $image_url, $post_id, $title );
	}

	/**
	 * Clear preview cache
	 */
	public function clear_preview_cache() {
		delete_transient( self::PREVIEW_CACHE_KEY );
	}
}
