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

	const PREVIEW_CACHE_KEY = 'mp_directory_preview_cache';

	private $rest;

	public function __construct() {
		$this->rest = new REST();
		
		add_action( 'wp_ajax_mp_directory_preview', array( $this, 'ajax_preview' ) );
		add_action( 'wp_ajax_mp_directory_import', array( $this, 'ajax_import' ) );
	}

	public function ajax_preview() {
		check_ajax_referer( 'mp_directory_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Niewystarczające uprawnienia.', 'mp-directory' ) ) );
		}

		$force_refresh = isset( $_POST['force_refresh'] ) && $_POST['force_refresh'] === 'true';

		$preview_data = $this->get_preview_data( $force_refresh );

		if ( is_wp_error( $preview_data ) ) {
			wp_send_json_error( array( 'message' => $preview_data->get_error_message() ) );
		}

		wp_send_json_success( $preview_data );
	}

	public function ajax_import() {
		check_ajax_referer( 'mp_directory_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Niewystarczające uprawnienia.', 'mp-directory' ) ) );
		}

		$batch  = isset( $_POST['batch'] ) ? absint( $_POST['batch'] ) : 0;
		$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;

		$result = $this->run_import( $batch, $offset );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	public function get_preview_data( $force_refresh = false ) {
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
			return new \WP_Error( 'no_data', __( 'Nie znaleziono danych posłów w odpowiedzi API.', 'mp-directory' ) );
		}

		// Format for preview
		$preview = array(
			'items'      => $mps,
			'total'      => count( $mps ),
			'fetched_at' => current_time( 'mysql' ),
		);

		$cache_ttl = Settings::get( 'preview_cache_ttl', 20 ) * MINUTE_IN_SECONDS;
		set_transient( self::PREVIEW_CACHE_KEY, $preview, $cache_ttl );

		return $preview;
	}

	public function run_import( $batch = 0, $offset = 0 ) {
		$batch_size = Settings::get( 'import_batch_size', 100 );
		$page       = floor( $offset / $batch_size ) + 1;

		$response = $this->rest->get_mps( $page, $batch_size );

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
			return array(
				'imported'  => 0,
				'updated'   => 0,
				'offset'    => $offset,
				'complete'  => true,
				'message'   => __( 'Nie ma więcej posłów do zaimportowania.', 'mp-directory' ),
			);
		}

		// Get total count from API
		$total_mps = count( $mps );
		
		// Slice the batch we need based on offset
		$batch_mps = array_slice( $mps, $offset, $batch_size );
		
		// If no records in this batch, we're done
		if ( empty( $batch_mps ) ) {
			return array(
				'imported'  => 0,
				'updated'   => 0,
				'offset'    => $offset,
				'complete'  => true,
				'message'   => __( 'Import zakończony - wszystkie posłowie zostali przetworzeni.', 'mp-directory' ),
			);
		}

		$imported = 0;
		$updated  = 0;

		foreach ( $batch_mps as $mp_data ) {
			$result = $this->import_single_mp( $mp_data );
			
			if ( ! is_wp_error( $result ) ) {
				if ( $result['is_new'] ) {
					$imported++;
				} else {
					$updated++;
				}
			}
		}

		$new_offset = $offset + count( $batch_mps );
		$has_more   = $new_offset < $total_mps;

		return array(
			'imported' => $imported,
			'updated'  => $updated,
			'offset'   => $new_offset,
			'complete' => ! $has_more,
			'message'  => sprintf(
				__( 'Zaimportowano %1$d nowych posłów, zaktualizowano %2$d istniejących posłów.', 'mp-directory' ),
				$imported,
				$updated
			),
		);
	}

	public function import_single_mp( $data ) {
		$api_id = isset( $data['id'] ) ? $data['id'] : null;
		
		if ( empty( $api_id ) ) {
			return new \WP_Error( 'no_api_id', __( 'Dane posła nie zawierają wymaganego pola "id".', 'mp-directory' ) );
		}

		$existing_post = $this->get_mp_by_api_id( $api_id );
		$is_new        = ! $existing_post;

		$mapped = $this->map_api_data( $data );

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

		update_post_meta( $post_id, '_mp_api_id', $api_id );

		if ( function_exists( 'update_field' ) ) {
			foreach ( $mapped['acf_fields'] as $field_key => $field_value ) {
				update_field( $field_key, $field_value, $post_id );
			}
		}

		if ( ! empty( $mapped['photo_url'] ) ) {
			$this->set_featured_image( $post_id, $mapped['photo_url'], $mapped['post_title'] );
		}

		return array(
			'post_id' => $post_id,
			'is_new'  => $is_new,
		);
	}

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

	private function map_api_data( $data ) {
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
		
		$api_id = isset( $data['id'] ) ? absint( $data['id'] ) : 0;
		$api_base_url = Settings::get( 'api_base_url', '' );
		$sejm_photo_url = '';
		$sejm_photo_mini_url = '';
		
		if ( ! empty( $api_id ) && ! empty( $api_base_url ) ) {
			if ( preg_match( '/term(\d+)/i', $api_base_url, $matches ) ) {
				$term = $matches[1];
				$sejm_photo_url = "https://api.sejm.gov.pl/sejm/term{$term}/MP/{$api_id}/photo";
				$sejm_photo_mini_url = "https://api.sejm.gov.pl/sejm/term{$term}/MP/{$api_id}/photo-mini";
			}
		}

		$content_parts = array();
		if ( ! empty( $profession ) ) {
			$content_parts[] = '<p><strong>' . __( 'Zawód:', 'mp-directory' ) . '</strong> ' . esc_html( $profession ) . '</p>';
		}
		if ( ! empty( $education ) ) {
			$content_parts[] = '<p><strong>' . __( 'Wykształcenie:', 'mp-directory' ) . '</strong> ' . esc_html( $education ) . '</p>';
		}

		$post_content = implode( "\n", $content_parts );

		$excerpt = sprintf(
			__( 'Poseł reprezentujący okręg %2$s (%1$s)', 'mp-directory' ),
			$party,
			$constituency
		);

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

	private function set_featured_image( $post_id, $image_url, $title = '' ) {
		if ( has_post_thumbnail( $post_id ) ) {
			return get_post_thumbnail_id( $post_id );
		}

		return mp_directory_sideload_image( $image_url, $post_id, $title );
	}

	public function clear_preview_cache() {
		delete_transient( self::PREVIEW_CACHE_KEY );
	}
}
