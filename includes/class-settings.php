<?php
/**
 * Settings Page
 *
 * @package MP_Directory
 */

namespace MP_Directory;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Settings
 */
class Settings {

	const OPTION_NAME = 'mp_directory_settings';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_mp_directory_test_api', array( $this, 'ajax_test_api' ) );
	}

	public function add_admin_menu() {
		add_menu_page(
			__( 'Ustawienia katalogu posłów', 'mp-directory' ),
			__( 'Ustawienia importu posłów', 'mp-directory' ),
			'manage_options',
			'mp-directory',
			array( $this, 'render_settings_page' ),
			'dashicons-groups',
			30
		);

		add_submenu_page(
			'mp-directory',
			__( 'Ustawienia', 'mp-directory' ),
			__( 'Ustawienia', 'mp-directory' ),
			'manage_options',
			'mp-directory',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'mp-directory',
			__( 'Importuj posłów', 'mp-directory' ),
			__( 'Import', 'mp-directory' ),
			'manage_options',
			'mp-directory-import',
			array( $this, 'render_import_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting(
			'mp_directory_settings_group',
			self::OPTION_NAME,
			array( $this, 'sanitize_settings' )
		);

		// API Settings Section
		add_settings_section(
			'mp_directory_api_section',
			__( 'Konfiguracja API', 'mp-directory' ),
			array( $this, 'render_api_section' ),
			'mp-directory'
		);

		add_settings_field(
			'api_base_url',
			__( 'Bazowy URL API', 'mp-directory' ),
			array( $this, 'render_text_field' ),
			'mp-directory',
			'mp_directory_api_section',
			array(
				'label_for'   => 'api_base_url',
				'placeholder' => 'https://api.example.com/mps',
				'description' => __( 'Bazowy URL punktu końcowego API dla posłów.', 'mp-directory' ),
			)
		);

		add_settings_field(
			'api_key',
			__( 'Klucz API (opcjonalny)', 'mp-directory' ),
			array( $this, 'render_text_field' ),
			'mp-directory',
			'mp_directory_api_section',
			array(
				'label_for'   => 'api_key',
				'type'        => 'password',
				'placeholder' => '',
				'description' => __( 'Klucz API jeśli jest wymagany przez zewnętrzne API.', 'mp-directory' ),
			)
		);

		add_settings_section(
			'mp_directory_import_section',
			__( 'Ustawienia importu', 'mp-directory' ),
			array( $this, 'render_import_section' ),
			'mp-directory'
		);

		add_settings_field(
			'preview_cache_ttl',
			__( 'TTL pamięci podręcznej podglądu (minuty)', 'mp-directory' ),
			array( $this, 'render_number_field' ),
			'mp-directory',
			'mp_directory_import_section',
			array(
				'label_for'   => 'preview_cache_ttl',
				'min'         => 5,
				'max'         => 120,
				'default'     => 20,
				'description' => __( 'Jak długo przechowywać dane podglądu API w pamięci podręcznej (5-120 minut).', 'mp-directory' ),
			)
		);

		add_settings_field(
			'import_batch_size',
			__( 'Rozmiar partii importu', 'mp-directory' ),
			array( $this, 'render_number_field' ),
			'mp-directory',
			'mp_directory_import_section',
			array(
				'label_for'   => 'import_batch_size',
				'min'         => 10,
				'max'         => 500,
				'default'     => 100,
				'description' => __( 'Liczba posłów do zaimportowania na partię (10-500).', 'mp-directory' ),
			)
		);

		add_settings_section(
			'mp_directory_cron_section',
			__( 'Zaplanowany import', 'mp-directory' ),
			array( $this, 'render_cron_section' ),
			'mp-directory'
		);

		add_settings_field(
			'enable_cron',
			__( 'Włącz zaplanowany import', 'mp-directory' ),
			array( $this, 'render_checkbox_field' ),
			'mp-directory',
			'mp_directory_cron_section',
			array(
				'label_for'   => 'enable_cron',
				'description' => __( 'Automatycznie importuj posłów w zaplanowanych odstępach czasu.', 'mp-directory' ),
			)
		);

		add_settings_field(
			'cron_interval',
			__( 'Częstotliwość importu', 'mp-directory' ),
			array( $this, 'render_select_field' ),
			'mp-directory',
			'mp_directory_cron_section',
			array(
				'label_for'   => 'cron_interval',
				'options'     => array(
					'hourly'       => __( 'Co godzinę', 'mp-directory' ),
					'twicedaily'   => __( 'Dwa razy dziennie', 'mp-directory' ),
					'daily'        => __( 'Codziennie', 'mp-directory' ),
				),
				'description' => __( 'Jak często uruchamiać automatyczny import.', 'mp-directory' ),
			)
		);
	}

	public function sanitize_settings( $input ) {
		$sanitized = array();

		$sanitized['api_base_url']      = isset( $input['api_base_url'] ) ? esc_url_raw( $input['api_base_url'] ) : '';
		$sanitized['api_key']           = isset( $input['api_key'] ) ? sanitize_text_field( $input['api_key'] ) : '';
		$sanitized['preview_cache_ttl'] = isset( $input['preview_cache_ttl'] ) ? absint( $input['preview_cache_ttl'] ) : 20;
		$sanitized['import_batch_size'] = isset( $input['import_batch_size'] ) ? absint( $input['import_batch_size'] ) : 100;
		$sanitized['enable_cron']       = isset( $input['enable_cron'] ) ? (bool) $input['enable_cron'] : false;
		$sanitized['cron_interval']     = isset( $input['cron_interval'] ) ? sanitize_text_field( $input['cron_interval'] ) : 'daily';

		// Clamp values
		$sanitized['preview_cache_ttl'] = max( 5, min( 120, $sanitized['preview_cache_ttl'] ) );
		$sanitized['import_batch_size'] = max( 10, min( 500, $sanitized['import_batch_size'] ) );

		// Reschedule cron if settings changed
		$old_settings = get_option( self::OPTION_NAME, array() );
		if ( $old_settings['enable_cron'] !== $sanitized['enable_cron'] ||
		     $old_settings['cron_interval'] !== $sanitized['cron_interval'] ) {
			do_action( 'mp_directory_cron_settings_changed', $sanitized );
		}

		return $sanitized;
	}

	/**
	 * Get a setting value
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function get( $key, $default = '' ) {
		$settings = get_option( self::OPTION_NAME, array() );
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	/**
	 * Render API section description
	 */
	public function render_api_section() {
		echo '<p>' . esc_html__( 'Skonfiguruj zewnętrzny punkt końcowy API do pobierania danych posłów.', 'mp-directory' ) . '</p>';
	}

	/**
	 * Render Import section description
	 */
	public function render_import_section() {
		echo '<p>' . esc_html__( 'Kontroluj sposób importowania i buforowania danych.', 'mp-directory' ) . '</p>';
	}

	/**
	 * Render Cron section description
	 */
	public function render_cron_section() {
		echo '<p>' . esc_html__( 'Skonfiguruj automatyczny import w tle.', 'mp-directory' ) . '</p>';
	}

	/**
	 * Render text field
	 *
	 * @param array $args Field arguments.
	 */
	public function render_text_field( $args ) {
		$settings    = get_option( self::OPTION_NAME, array() );
		$value       = isset( $settings[ $args['label_for'] ] ) ? $settings[ $args['label_for'] ] : '';
		$type        = isset( $args['type'] ) ? $args['type'] : 'text';
		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
		?>
		<input 
			type="<?php echo esc_attr( $type ); ?>"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			class="regular-text"
		/>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render number field
	 *
	 * @param array $args Field arguments.
	 */
	public function render_number_field( $args ) {
		$settings = get_option( self::OPTION_NAME, array() );
		$value    = isset( $settings[ $args['label_for'] ] ) ? $settings[ $args['label_for'] ] : $args['default'];
		?>
		<input 
			type="number"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			min="<?php echo esc_attr( $args['min'] ); ?>"
			max="<?php echo esc_attr( $args['max'] ); ?>"
			class="small-text"
		/>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render checkbox field
	 *
	 * @param array $args Field arguments.
	 */
	public function render_checkbox_field( $args ) {
		$settings = get_option( self::OPTION_NAME, array() );
		$value    = isset( $settings[ $args['label_for'] ] ) ? $settings[ $args['label_for'] ] : false;
		?>
		<label>
			<input 
				type="checkbox"
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>"
				value="1"
				<?php checked( $value, true ); ?>
			/>
			<?php if ( ! empty( $args['description'] ) ) : ?>
				<?php echo esc_html( $args['description'] ); ?>
			<?php endif; ?>
		</label>
		<?php
	}

	/**
	 * Render select field
	 *
	 * @param array $args Field arguments.
	 */
	public function render_select_field( $args ) {
		$settings = get_option( self::OPTION_NAME, array() );
		$value    = isset( $settings[ $args['label_for'] ] ) ? $settings[ $args['label_for'] ] : 'daily';
		?>
		<select 
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( self::OPTION_NAME . '[' . $args['label_for'] . ']' ); ?>"
		>
			<?php foreach ( $args['options'] as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show error/update messages
		settings_errors( 'mp_directory_messages' );

		require_once MP_DIRECTORY_PATH . 'admin/views/settings-page.php';
	}

	/**
	 * Render import page
	 */
	public function render_import_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		require_once MP_DIRECTORY_PATH . 'admin/views/importer-preview.php';
	}

	/**
	 * AJAX handler for API connection test
	 */
	public function ajax_test_api() {
		check_ajax_referer( 'mp_directory_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Niewystarczające uprawnienia.', 'mp-directory' ) ) );
		}

		$rest = new REST();
		$result = $rest->test_connection();

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}
}
