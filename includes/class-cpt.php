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

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'template_include', array( $this, 'load_templates' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_archive_query' ) );
	}

	/**
	 * Register the MP custom post type
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Members of Parliament', 'Post type general name', 'mp-directory' ),
			'singular_name'         => _x( 'MP', 'Post type singular name', 'mp-directory' ),
			'menu_name'             => _x( 'MP Directory', 'Admin Menu text', 'mp-directory' ),
			'name_admin_bar'        => _x( 'MP', 'Add New on Toolbar', 'mp-directory' ),
			'add_new'               => __( 'Add New', 'mp-directory' ),
			'add_new_item'          => __( 'Add New MP', 'mp-directory' ),
			'new_item'              => __( 'New MP', 'mp-directory' ),
			'edit_item'             => __( 'Edit MP', 'mp-directory' ),
			'view_item'             => __( 'View MP', 'mp-directory' ),
			'all_items'             => __( 'All MPs', 'mp-directory' ),
			'search_items'          => __( 'Search MPs', 'mp-directory' ),
			'parent_item_colon'     => __( 'Parent MPs:', 'mp-directory' ),
			'not_found'             => __( 'No MPs found.', 'mp-directory' ),
			'not_found_in_trash'    => __( 'No MPs found in Trash.', 'mp-directory' ),
			'featured_image'        => _x( 'MP Photo', 'Overrides the "Featured Image" phrase', 'mp-directory' ),
			'set_featured_image'    => _x( 'Set MP photo', 'Overrides the "Set featured image" phrase', 'mp-directory' ),
			'remove_featured_image' => _x( 'Remove MP photo', 'Overrides the "Remove featured image" phrase', 'mp-directory' ),
			'use_featured_image'    => _x( 'Use as MP photo', 'Overrides the "Use as featured image" phrase', 'mp-directory' ),
			'archives'              => _x( 'MP archives', 'The post type archive label used in nav menus', 'mp-directory' ),
			'insert_into_item'      => _x( 'Insert into MP', 'Overrides the "Insert into post" phrase', 'mp-directory' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this MP', 'Overrides the "Uploaded to this post" phrase', 'mp-directory' ),
			'filter_items_list'     => _x( 'Filter MPs list', 'Screen reader text for the filter links', 'mp-directory' ),
			'items_list_navigation' => _x( 'MPs list navigation', 'Screen reader text for the pagination', 'mp-directory' ),
			'items_list'            => _x( 'MPs list', 'Screen reader text for the items list', 'mp-directory' ),
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

	/**
	 * Load custom templates for MP post type
	 *
	 * @param string $template The path to the template.
	 * @return string
	 */
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

	/**
	 * Filter archive query for custom filters
	 *
	 * @param WP_Query $query The query object.
	 */
	public function filter_archive_query( $query ) {
		if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'mp' ) ) {
			// Filter by party
			if ( ! empty( $_GET['mp_party'] ) ) {
				$meta_query = $query->get( 'meta_query' ) ?: array();
				$meta_query[] = array(
					'key'   => 'mp_party',
					'value' => sanitize_text_field( $_GET['mp_party'] ),
				);
				$query->set( 'meta_query', $meta_query );
			}

			// Filter by constituency
			if ( ! empty( $_GET['mp_constituency'] ) ) {
				$meta_query = $query->get( 'meta_query' ) ?: array();
				$meta_query[] = array(
					'key'   => 'mp_constituency',
					'value' => sanitize_text_field( $_GET['mp_constituency'] ),
				);
				$query->set( 'meta_query', $meta_query );
			}

			// Order by title
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
		}
	}
}
