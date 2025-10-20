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

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	public function enqueue_frontend_assets() {
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

	public function enqueue_admin_assets( $hook ) {
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

		wp_localize_script(
			'mp-directory-admin',
			'mpDirectoryAdmin',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'mp_directory_admin' ),
				'i18n'         => array(
					'importing'        => __( 'Importowanie...', 'mp-directory' ),
					'importComplete'   => __( 'Import zakończony pomyślnie!', 'mp-directory' ),
					'importError'      => __( 'Import nie powiódł się. Spróbuj ponownie.', 'mp-directory' ),
					'confirmImport'    => __( 'Czy na pewno chcesz rozpocząć import? Może to potrwać kilka minut.', 'mp-directory' ),
					'previewLoading'   => __( 'Ładowanie podglądu...', 'mp-directory' ),
					'previewError'     => __( 'Nie udało się załadować podglądu.', 'mp-directory' ),
				),
			)
		);
	}
}
