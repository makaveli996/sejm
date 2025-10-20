<?php
/**
 * Settings Page View
 *
 * @package MP_Directory
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="mp-directory-settings-wrapper">
		<form action="options.php" method="post">
			<?php
			settings_fields( 'mp_directory_settings_group' );
			do_settings_sections( 'mp-directory' );
			submit_button( __( 'Save Settings', 'mp-directory' ) );
			?>
		</form>

		<?php
		// Display cron status if enabled
		$enable_cron = MP_Directory\Settings::get( 'enable_cron', false );
		if ( $enable_cron ) :
			$next_run = MP_Directory\Cron::get_next_run_display();
			?>
			<div class="notice notice-info inline">
				<p>
					<strong><?php esc_html_e( 'Scheduled Import Status:', 'mp-directory' ); ?></strong>
					<?php
					printf(
						/* translators: %s: next run time */
						esc_html__( 'Next automatic import: %s', 'mp-directory' ),
						'<strong>' . esc_html( $next_run ) . '</strong>'
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<hr>

		<h2><?php esc_html_e( 'Quick Actions', 'mp-directory' ); ?></h2>
		
		<div class="mp-directory-actions">
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mp-directory-import' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Import MPs', 'mp-directory' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mp' ) ); ?>" class="button">
					<?php esc_html_e( 'View All MPs', 'mp-directory' ); ?>
				</a>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'mp' ) ); ?>" class="button" target="_blank">
					<?php esc_html_e( 'View MP Archive (Frontend)', 'mp-directory' ); ?>
				</a>
			</p>
		</div>

		<hr>

		<h2><?php esc_html_e( 'API Test', 'mp-directory' ); ?></h2>
		<p><?php esc_html_e( 'Test your API connection to ensure the settings are correct.', 'mp-directory' ); ?></p>
		<p>
			<button type="button" id="mp-directory-test-api" class="button">
				<?php esc_html_e( 'Test Connection', 'mp-directory' ); ?>
			</button>
			<span id="mp-directory-test-result" class="description"></span>
		</p>
	</div>
</div>

<style>
.mp-directory-settings-wrapper {
	max-width: 800px;
}
.mp-directory-actions {
	margin: 15px 0;
}
.mp-directory-actions .button {
	margin-right: 10px;
	margin-bottom: 10px;
}
#mp-directory-test-result {
	display: inline-block;
	margin-left: 10px;
	padding: 5px 10px;
	border-radius: 3px;
}
#mp-directory-test-result.success {
	color: #006505;
	background: #d5f4d6;
}
#mp-directory-test-result.error {
	color: #8b0000;
	background: #ffd5d5;
}
</style>

<script>
jQuery(document).ready(function($) {
	$('#mp-directory-test-api').on('click', function() {
		var $button = $(this);
		var $result = $('#mp-directory-test-result');
		
		$button.prop('disabled', true).text('<?php esc_html_e( 'Testing...', 'mp-directory' ); ?>');
		$result.removeClass('success error').text('');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'mp_directory_test_api',
				nonce: mpDirectoryAdmin.nonce
			},
			success: function(response) {
				$button.prop('disabled', false).text('<?php esc_html_e( 'Test Connection', 'mp-directory' ); ?>');
				
				if (response.success) {
					$result.addClass('success').text('✓ ' + response.data.message);
				} else {
					$result.addClass('error').text('✗ ' + (response.data.message || 'Unknown error'));
				}
			},
			error: function() {
				$button.prop('disabled', false).text('<?php esc_html_e( 'Test Connection', 'mp-directory' ); ?>');
				$result.addClass('error').text('<?php esc_html_e( 'Connection failed', 'mp-directory' ); ?>');
			}
		});
	});
});
</script>
