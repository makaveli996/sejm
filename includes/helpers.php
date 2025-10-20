<?php
/**
 * Helper Functions
 *
 * @package MP_Directory
 */

namespace MP_Directory;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sideload an image from URL and attach it to a post
 *
 * @param string $image_url Image URL.
 * @param int    $post_id   Post ID to attach to.
 * @param string $title     Image title/alt.
 * @return int|false Attachment ID or false on failure.
 */
function mp_directory_sideload_image( $image_url, $post_id = 0, $title = '' ) {
	// Validate URL
	if ( empty( $image_url ) || ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
		return false;
	}

	// Check if image already exists for this post
	if ( $post_id > 0 ) {
		$existing_thumbnail = get_post_thumbnail_id( $post_id );
		if ( $existing_thumbnail ) {
			return $existing_thumbnail;
		}
	}

	// Include required files
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// Download file to temp location
	$tmp = download_url( $image_url );

	if ( is_wp_error( $tmp ) ) {
		return false;
	}

	// Get file extension
	$file_array = array(
		'name'     => basename( $image_url ),
		'tmp_name' => $tmp,
	);

	// If no extension, try to determine from URL
	if ( ! pathinfo( $file_array['name'], PATHINFO_EXTENSION ) ) {
		$file_array['name'] .= '.jpg'; // Default to jpg
	}

	// Do the actual upload
	$attachment_id = media_handle_sideload( $file_array, $post_id, $title );

	// Clean up temp file
	if ( file_exists( $tmp ) ) {
		@unlink( $tmp );
	}

	if ( is_wp_error( $attachment_id ) ) {
		return false;
	}

	// Set as featured image if post ID provided
	if ( $post_id > 0 ) {
		set_post_thumbnail( $post_id, $attachment_id );
	}

	return $attachment_id;
}

/**
 * Get MP ACF field value with fallback
 *
 * @param string $field_name Field name.
 * @param int    $post_id    Post ID.
 * @param mixed  $default    Default value.
 * @return mixed
 */
function mp_directory_get_field( $field_name, $post_id = null, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$value = get_field( $field_name, $post_id );
	
	return ! empty( $value ) ? $value : $default;
}

/**
 * Get all unique parties from MPs
 *
 * @return array
 */
function mp_directory_get_parties() {
	global $wpdb;

	$parties = $wpdb->get_col(
		"SELECT DISTINCT meta_value 
		FROM {$wpdb->postmeta} 
		WHERE meta_key = 'mp_party' 
		AND meta_value != '' 
		ORDER BY meta_value ASC"
	);

	return array_filter( $parties );
}

/**
 * Get all unique constituencies from MPs
 *
 * @return array
 */
function mp_directory_get_constituencies() {
	global $wpdb;

	$constituencies = $wpdb->get_col(
		"SELECT DISTINCT meta_value 
		FROM {$wpdb->postmeta} 
		WHERE meta_key = 'mp_constituency' 
		AND meta_value != '' 
		ORDER BY meta_value ASC"
	);

	return array_filter( $constituencies );
}

/**
 * Get all unique terms from MPs
 *
 * @return array
 */
function mp_directory_get_terms() {
	global $wpdb;

	$terms = $wpdb->get_col(
		"SELECT DISTINCT meta_value 
		FROM {$wpdb->postmeta} 
		WHERE meta_key = 'mp_term' 
		AND meta_value != '' 
		ORDER BY meta_value DESC"
	);

	return array_filter( $terms );
}

/**
 * Format contact information
 *
 * @param array $contact Contact data with 'type', 'value', 'label'.
 * @return string HTML output.
 */
function mp_directory_format_contact( $contact ) {
	if ( empty( $contact['value'] ) ) {
		return '';
	}

	$type  = isset( $contact['type'] ) ? $contact['type'] : 'other';
	$value = esc_html( $contact['value'] );
	$label = isset( $contact['label'] ) ? esc_html( $contact['label'] ) : '';

	if ( $type === 'email' ) {
		return sprintf(
			'<a href="mailto:%s">%s</a>',
			esc_attr( $contact['value'] ),
			$value
		);
	} elseif ( $type === 'phone' ) {
		$clean_phone = preg_replace( '/[^0-9+]/', '', $contact['value'] );
		return sprintf(
			'<a href="tel:%s">%s</a>',
			esc_attr( $clean_phone ),
			$value
		);
	}

	return $value;
}

/**
 * Get social media icon class
 *
 * @param string $network Social network name.
 * @return string Icon class or emoji.
 */
function mp_directory_get_social_icon( $network ) {
	$icons = array(
		'twitter'   => '𝕏',
		'facebook'  => '📘',
		'instagram' => '📷',
		'linkedin'  => '💼',
		'youtube'   => '▶️',
		'website'   => '🌐',
		'other'     => '🔗',
	);

	return isset( $icons[ $network ] ) ? $icons[ $network ] : $icons['other'];
}

/**
 * Sanitize API response
 *
 * @param mixed $data Raw data.
 * @return mixed Sanitized data.
 */
function mp_directory_sanitize_api_response( $data ) {
	if ( is_array( $data ) ) {
		return array_map( 'MP_Directory\mp_directory_sanitize_api_response', $data );
	}

	if ( is_string( $data ) ) {
		return sanitize_text_field( $data );
	}

	return $data;
}

/**
 * Debug log helper
 *
 * @param mixed $message Message to log.
 */
function mp_directory_log( $message ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
		if ( is_array( $message ) || is_object( $message ) ) {
			error_log( 'MP Directory: ' . print_r( $message, true ) );
		} else {
			error_log( 'MP Directory: ' . $message );
		}
	}
}
