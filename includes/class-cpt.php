<?php
/**
 * Custom Post Type Registration
 *
 * @package MP_Directory
 */

namespace MP_Directory;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CPT
 */
class CPT {

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'template_include', array( $this, 'load_templates' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_archive_query' ) );
	}

	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Posłowie', 'Post type general name', 'mp-directory' ),
			'singular_name'         => _x( 'Poseł', 'Post type singular name', 'mp-directory' ),
			'menu_name'             => _x( 'Katalog Posłów', 'Admin Menu text', 'mp-directory' ),
			'name_admin_bar'        => _x( 'Poseł', 'Add New on Toolbar', 'mp-directory' ),
			'add_new'               => __( 'Dodaj nowy', 'mp-directory' ),
			'add_new_item'          => __( 'Dodaj nowego posła', 'mp-directory' ),
			'new_item'              => __( 'Nowy poseł', 'mp-directory' ),
			'edit_item'             => __( 'Edytuj posła', 'mp-directory' ),
			'view_item'             => __( 'Zobacz posła', 'mp-directory' ),
			'all_items'             => __( 'Wszyscy posłowie', 'mp-directory' ),
			'search_items'          => __( 'Szukaj posłów', 'mp-directory' ),
			'parent_item_colon'     => __( 'Nadrzędny poseł:', 'mp-directory' ),
			'not_found'             => __( 'Nie znaleziono posłów.', 'mp-directory' ),
			'not_found_in_trash'    => __( 'Nie znaleziono posłów w koszu.', 'mp-directory' ),
			'featured_image'        => _x( 'Zdjęcie posła', 'Overrides the "Featured Image" phrase', 'mp-directory' ),
			'set_featured_image'    => _x( 'Ustaw zdjęcie posła', 'Overrides the "Set featured image" phrase', 'mp-directory' ),
			'remove_featured_image' => _x( 'Usuń zdjęcie posła', 'Overrides the "Remove featured image" phrase', 'mp-directory' ),
			'use_featured_image'    => _x( 'Użyj jako zdjęcie posła', 'Overrides the "Use as featured image" phrase', 'mp-directory' ),
			'archives'              => _x( 'Archiwum posłów', 'The post type archive label used in nav menus', 'mp-directory' ),
			'insert_into_item'      => _x( 'Wstaw do posła', 'Overrides the "Insert into post" phrase', 'mp-directory' ),
			'uploaded_to_this_item' => _x( 'Przesłano do tego posła', 'Overrides the "Uploaded to this post" phrase', 'mp-directory' ),
			'filter_items_list'     => _x( 'Filtruj listę posłów', 'Screen reader text for the filter links', 'mp-directory' ),
			'items_list_navigation' => _x( 'Nawigacja listy posłów', 'Screen reader text for the pagination', 'mp-directory' ),
			'items_list'            => _x( 'Lista posłów', 'Screen reader text for the items list', 'mp-directory' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'mp' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-groups',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
			'show_in_rest'       => true,
		);

		register_post_type( 'mp', $args );
	}

	public function load_templates( $template ) {
		// Check if we're viewing the MP post type
		if ( is_post_type_archive( 'mp' ) ) {
			$plugin_template = MP_DIRECTORY_PATH . 'templates/archive-mp.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		if ( is_singular( 'mp' ) ) {
			$plugin_template = MP_DIRECTORY_PATH . 'templates/single-mp.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	public function filter_archive_query( $query ) {
		if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'mp' ) ) {
			if ( ! empty( $_GET['mp_party'] ) ) {
				$meta_query = $query->get( 'meta_query' ) ?: array();
				$meta_query[] = array(
					'key'   => 'mp_party',
					'value' => sanitize_text_field( $_GET['mp_party'] ),
				);
				$query->set( 'meta_query', $meta_query );
			}

			if ( ! empty( $_GET['mp_constituency'] ) ) {
				$meta_query = $query->get( 'meta_query' ) ?: array();
				$meta_query[] = array(
					'key'   => 'mp_constituency',
					'value' => sanitize_text_field( $_GET['mp_constituency'] ),
				);
				$query->set( 'meta_query', $meta_query );
			}

			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
		}
	}
}
