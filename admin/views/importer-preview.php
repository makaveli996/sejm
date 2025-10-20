<?php
/**
 * Importer Preview Page
 *
 * @package MP_Directory
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap">
	<h1><?php esc_html_e( 'Importuj posłów', 'mp-directory' ); ?></h1>

	<div class="mp-directory-import-wrapper">
		<div class="notice notice-info">
			<p>
				<?php esc_html_e( 'To narzędzie pobierze dane posłów ze skonfigurowanego API i zaimportuje je do WordPressa.', 'mp-directory' ); ?>
				<br>
				<?php esc_html_e( 'Najpierw sprawdź podgląd danych, aby upewnić się, że połączenie z API działa poprawnie.', 'mp-directory' ); ?>
			</p>
		</div>

		<!-- Step 1: Preview -->
		<div id="mp-import-step-preview">
			<h2><?php esc_html_e( 'Krok 1: Podgląd danych', 'mp-directory' ); ?></h2>
			<p>
				<button type="button" id="mp-directory-load-preview" class="button button-primary">
					<?php esc_html_e( 'Załaduj podgląd', 'mp-directory' ); ?>
				</button>
				<button type="button" id="mp-directory-refresh-preview" class="button" style="display:none;">
					<?php esc_html_e( 'Odśwież podgląd', 'mp-directory' ); ?>
				</button>
				<span id="mp-preview-status" class="description"></span>
			</p>

			<div id="mp-preview-container" style="display:none;">
				<div id="mp-preview-info"></div>
				<div class="mp-preview-table-wrapper">
					<table class="wp-list-table widefat fixed striped" id="mp-preview-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'ID', 'mp-directory' ); ?></th>
								<th><?php esc_html_e( 'Imię i nazwisko', 'mp-directory' ); ?></th>
								<th><?php esc_html_e( 'Partia', 'mp-directory' ); ?></th>
								<th><?php esc_html_e( 'Okręg wyborczy', 'mp-directory' ); ?></th>
								<th><?php esc_html_e( 'Data urodzenia', 'mp-directory' ); ?></th>
							</tr>
						</thead>
						<tbody id="mp-preview-body">
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- Step 2: Import -->
		<div id="mp-import-step-run" style="display:none;">
			<hr>
			<h2><?php esc_html_e( 'Krok 2: Uruchom import', 'mp-directory' ); ?></h2>
			<p>
				<button type="button" id="mp-directory-start-import" class="button button-primary button-hero">
					<?php esc_html_e( 'Rozpocznij import', 'mp-directory' ); ?>
				</button>
			</p>

			<div id="mp-import-progress" style="display:none;">
				<p id="mp-progress-text"></p>
				<div id="mp-import-results"></div>
			</div>
		</div>
	</div>
</div>

<style>
.mp-directory-import-wrapper {
	max-width: 900px;
}
.mp-preview-table-wrapper {
	margin-top: 20px;
	overflow-x: auto;
}
#mp-preview-table {
	margin-top: 15px;
}
#mp-preview-table th {
	font-weight: 600;
}
#mp-preview-info {
	padding: 10px;
	background: #f0f0f1;
	border-left: 4px solid #2271b1;
	margin-bottom: 15px;
}
#mp-progress-text {
	font-size: 14px;
	color: #2c3338;
}
#mp-import-results {
	margin-top: 20px;
}
#mp-preview-status,
#mp-import-status {
	display: inline-block;
	margin-left: 10px;
}
.status-loading {
	color: #2271b1;
}
.status-success {
	color: #006505;
}
.status-error {
	color: #8b0000;
}
</style>

<script>
jQuery(document).ready(function($) {
	var importing = false;
	var currentOffset = 0;
	var totalImported = 0;
	var totalUpdated = 0;
	var totalProcessed = 0;

	// Load Preview
	$('#mp-directory-load-preview, #mp-directory-refresh-preview').on('click', function() {
		var isRefresh = $(this).attr('id') === 'mp-directory-refresh-preview';
		loadPreview(isRefresh);
	});

	function loadPreview(forceRefresh) {
		var $status = $('#mp-preview-status');
		$status.removeClass('status-success status-error').addClass('status-loading')
			.text(mpDirectoryAdmin.i18n.previewLoading);

		$('#mp-directory-load-preview').prop('disabled', true);

		$.ajax({
			url: mpDirectoryAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'mp_directory_preview',
				nonce: mpDirectoryAdmin.nonce,
				force_refresh: forceRefresh ? 'true' : 'false'
			},
			success: function(response) {
				$('#mp-directory-load-preview').prop('disabled', false);

				if (response.success && response.data.items) {
					displayPreview(response.data);
					$status.removeClass('status-loading status-error').addClass('status-success')
						.text('✓ <?php esc_html_e( 'Podgląd załadowany', 'mp-directory' ); ?>');
					
					$('#mp-directory-refresh-preview').show();
					$('#mp-import-step-run').show();
				} else {
					$status.removeClass('status-loading status-success').addClass('status-error')
						.text('✗ ' + (response.data.message || mpDirectoryAdmin.i18n.previewError));
				}
			},
			error: function() {
				$('#mp-directory-load-preview').prop('disabled', false);
				$status.removeClass('status-loading status-success').addClass('status-error')
					.text('✗ ' + mpDirectoryAdmin.i18n.previewError);
			}
		});
	}

	function displayPreview(data) {
		var $container = $('#mp-preview-container');
		var $body = $('#mp-preview-body');
		var $info = $('#mp-preview-info');

		$body.empty();

		// Display info
		$info.html('<strong><?php esc_html_e( 'Podgląd:', 'mp-directory' ); ?></strong> ' + 
			data.items.length + ' <?php esc_html_e( 'znalezionych posłów', 'mp-directory' ); ?> ' +
			'(<?php esc_html_e( 'Pobrano:', 'mp-directory' ); ?> ' + data.fetched_at + ')');

		// Populate table
		$.each(data.items, function(i, mp) {
			var row = '<tr>' +
				'<td>' + (mp.id || '-') + '</td>' +
				'<td>' + (mp.firstLastName || mp.firstName + ' ' + mp.lastName || '-') + '</td>' +
				'<td>' + (mp.club || '-') + '</td>' +
				'<td>' + (mp.districtName || '-') + '</td>' +
				'<td>' + (mp.birthDate || '-') + '</td>' +
				'</tr>';
			$body.append(row);
		});

		$container.show();
	}

	// Start Import
	$('#mp-directory-start-import').on('click', function() {
		if (importing) {
			return;
		}

		if (!confirm(mpDirectoryAdmin.i18n.confirmImport)) {
			return;
		}

		importing = true;
		currentOffset = 0;
		totalImported = 0;
		totalUpdated = 0;
		totalProcessed = 0;

		$(this).prop('disabled', true);
		$('#mp-import-progress').show();
		$('#mp-progress-text').text(mpDirectoryAdmin.i18n.importing);
		$('#mp-import-results').empty();

		runImportBatch();
	});

	function runImportBatch() {
		$.ajax({
			url: mpDirectoryAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'mp_directory_import',
				nonce: mpDirectoryAdmin.nonce,
				offset: currentOffset,
				batch: 0
			},
			success: function(response) {
				if (response.success) {
					var data = response.data;
					currentOffset = data.offset;
					totalImported += data.imported || 0;
					totalUpdated += data.updated || 0;
					totalProcessed = totalImported + totalUpdated;

					if (!data.complete) {
						// Continue importing
						$('#mp-progress-text').text(
							mpDirectoryAdmin.i18n.importing + ' (' + totalProcessed + ' <?php esc_html_e( 'posłów przetworzonych', 'mp-directory' ); ?>...)'
						);
						
						// Continue importing
						runImportBatch();
					} else {
						// Import complete
						importing = false;
						$('#mp-progress-text').html('<strong>✓ ' + mpDirectoryAdmin.i18n.importComplete + '</strong>');
						$('#mp-directory-start-import').prop('disabled', false);
						
						var summaryMessage = '<?php esc_html_e( 'Zaimportowano', 'mp-directory' ); ?> ' + 
							totalImported + ' <?php esc_html_e( 'nowych posłów, zaktualizowano', 'mp-directory' ); ?> ' + 
							totalUpdated + ' <?php esc_html_e( 'istniejących posłów.', 'mp-directory' ); ?>';
						
						$('#mp-import-results').html(
							'<div class="notice notice-success inline"><p>' + summaryMessage + '</p></div>'
						);
					}
				} else {
					importing = false;
					$('#mp-directory-start-import').prop('disabled', false);
					$('#mp-import-results').html(
						'<div class="notice notice-error inline"><p>✗ ' + 
						(response.data.message || mpDirectoryAdmin.i18n.importError) + 
						'</p></div>'
					);
				}
			},
			error: function() {
				importing = false;
				$('#mp-directory-start-import').prop('disabled', false);
				$('#mp-import-results').html(
					'<div class="notice notice-error inline"><p>✗ ' + 
					mpDirectoryAdmin.i18n.importError + 
					'</p></div>'
				);
			}
		});
	}
});
</script>
