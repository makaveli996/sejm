<?php
/**
 * Plugin Name:       MP Directory
 * Plugin URI:        https://example.com/mp-directory
 * Description:       Import and display Members of Parliament from an external API with custom post types, ACF fields, and scheduled imports.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mp-directory
 * Domain Path:       /languages
 */

namespace MP_Directory;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'MP_DIRECTORY_VERSION', '1.0.0' );
define( 'MP_DIRECTORY_FILE', __FILE__ );
define( 'MP_DIRECTORY_PATH', plugin_dir_path( __FILE__ ) );
define( 'MP_DIRECTORY_URL', plugin_dir_url( __FILE__ ) );
define( 'MP_DIRECTORY_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main Plugin Class
 */
class MP_Directory {

	/**
	 * The single instance of the class
	 */
	private static $instance = null;

	/**
	 * Plugin components
	 */
	public $cpt;
	public $acf;
	public $settings;
	public $importer;
	public $rest;
	public $cron;
	public $assets;

	/**
	 * Get the singleton instance
	 *
	 * @return MP_Directory
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->autoload();
		$this->init_components();
		$this->init_hooks();
	}

	/**
	 * Autoload classes
	 */
	private function autoload() {
		require_once MP_DIRECTORY_PATH . 'includes/helpers.php';
		require_once MP_DIRECTORY_PATH . 'includes/class-cpt.php';
		require_once MP_DIRECTORY_PATH . 'includes/class-acf.php';
		require_once MP_DIRECTORY_PATH . 'includes/class-settings.php';
		require_once MP_DIRECTORY_PATH . 'includes/class-rest.php';
		require_once MP_DIRECTORY_PATH . 'includes/class-importer.php';
		require_once MP_DIRECTORY_PATH . 'includes/class-cron.php';
		require_once MP_DIRECTORY_PATH . 'includes/class-assets.php';
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_notices', array( $this, 'check_dependencies' ) );
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'mp-directory',
			false,
			dirname( MP_DIRECTORY_BASENAME ) . '/languages'
		);
	}

	/**
	 * Initialize plugin components
	 */
	public function init_components() {
		// Initialize core components
		$this->cpt      = new CPT();
		$this->acf      = new ACF();
		$this->settings = new Settings();
		$this->rest     = new REST();
		$this->importer = new Importer();
		$this->cron     = new Cron();
		$this->assets   = new Assets();
	}

	/**
	 * Check plugin dependencies
	 */
	public function check_dependencies() {
		// Check if ACF is active
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			echo '<div class="notice notice-warning is-dismissible">';
			echo '<p><strong>' . esc_html__( 'Katalog Posłów:', 'mp-directory' ) . '</strong> ';
			echo esc_html__( 'Wtyczka Advanced Custom Fields jest zalecana dla pełnej funkcjonalności. Niektóre funkcje mogą być ograniczone.', 'mp-directory' );
			echo '</p></div>';
		}
	}
}

/**
 * Activation hook
 */
function mp_directory_activate() {
	// Register post type
	$cpt = new CPT();
	$cpt->register_post_type();

	// Flush rewrite rules
	flush_rewrite_rules();

	// Set default options
	$defaults = array(
		'api_base_url'        => '',
		'api_key'             => '',
		'preview_cache_ttl'   => 20,
		'import_batch_size'   => 100,
		'enable_cron'         => false,
		'cron_interval'       => 'daily',
	);

	if ( ! get_option( 'mp_directory_settings' ) ) {
		add_option( 'mp_directory_settings', $defaults );
	}
}
register_activation_hook( __FILE__, 'MP_Directory\mp_directory_activate' );

/**
 * Deactivation hook
 */
function mp_directory_deactivate() {
	// Clear scheduled events
	$timestamp = wp_next_scheduled( 'mp_directory_cron_import' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'mp_directory_cron_import' );
	}

	// Flush rewrite rules
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'MP_Directory\mp_directory_deactivate' );

/**
 * Initialize the plugin
 */
function mp_directory_init() {
	return MP_Directory::instance();
}

// Kick off the plugin
mp_directory_init();
