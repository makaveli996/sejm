<?php
/**
 * Assets Management
 *
 * @package MP_Directory
 */

namespace MP_Directory;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Assets
 */
class Assets {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue frontend styles and scripts
	 */
	public function enqueue_frontend_assets() {
		// Only load on MP pages
		if ( is_post_type_archive( 'mp' ) || is_singular( 'mp' ) ) {
			wp_enqueue_style(
				'mp-directory-frontend',
				MP_DIRECTORY_URL . 'assets/css/frontend.css',
				array(),
				MP_DIRECTORY_VERSION
			);

			wp_enqueue_script(
				'mp-directory-frontend',
				MP_DIRECTORY_URL . 'assets/js/frontend.js',
				array( 'jquery' ),
				MP_DIRECTORY_VERSION,
				true
			);
		}
	}

	/**
	 * Enqueue admin styles and scripts
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on MP Directory admin pages
		if ( strpos( $hook, 'mp-directory' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'mp-directory-admin',
			MP_DIRECTORY_URL . 'assets/css/admin.css',
			array(),
			MP_DIRECTORY_VERSION
		);

		wp_enqueue_script(
			'mp-directory-admin',
			MP_DIRECTORY_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			MP_DIRECTORY_VERSION,
			true
		);

		// Localize script for AJAX
		wp_localize_script(
			'mp-directory-admin',
			'mpDirectoryAdmin',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'mp_directory_admin' ),
				'i18n'         => array(
					'importing'        => __( 'Importing...', 'mp-directory' ),
					'importComplete'   => __( 'Import completed successfully!', 'mp-directory' ),
					'importError'      => __( 'Import failed. Please try again.', 'mp-directory' ),
					'confirmImport'    => __( 'Are you sure you want to start the import? This may take several minutes.', 'mp-directory' ),
					'previewLoading'   => __( 'Loading preview...', 'mp-directory' ),
					'previewError'     => __( 'Failed to load preview.', 'mp-directory' ),
				),
			)
		);
	}
}
