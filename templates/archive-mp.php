<?php
/**
 * Archive Template for MPs
 *
 * @package MP_Directory
 */

use MP_Directory\Settings;

get_header();
?>

<div class="mp-directory-archive">
	<div class="mp-archive-container">
		<header class="mp-archive-header">
			<h1 class="mp-archive-title">
				<?php echo esc_html__( 'Members of Parliament', 'mp-directory' ); ?>
			</h1>
			
			<?php
			global $wp_query;
			$total = $wp_query->found_posts;
			?>
			<p class="mp-archive-count">
				<?php
				printf(
					/* translators: %d: number of MPs */
					esc_html( _n( '%d Member of Parliament', '%d Members of Parliament', $total, 'mp-directory' ) ),
					absint( $total )
				);
				?>
			</p>
		</header>

		<!-- Filters -->
		<div class="mp-filters">
			<form method="get" action="<?php echo esc_url( get_post_type_archive_link( 'mp' ) ); ?>" class="mp-filter-form">
				<div class="mp-filter-row">
					<div class="mp-filter-field">
						<label for="mp-search"><?php esc_html_e( 'Search:', 'mp-directory' ); ?></label>
						<input 
							type="text" 
							id="mp-search" 
							name="s" 
							value="<?php echo esc_attr( get_search_query() ); ?>" 
							placeholder="<?php esc_attr_e( 'Search by name...', 'mp-directory' ); ?>"
						/>
					</div>

					<div class="mp-filter-field">
						<label for="mp-party"><?php esc_html_e( 'Party:', 'mp-directory' ); ?></label>
						<select id="mp-party" name="mp_party">
							<option value=""><?php esc_html_e( 'All Parties', 'mp-directory' ); ?></option>
							<?php
							$parties        = MP_Directory\mp_directory_get_parties();
							$selected_party = isset( $_GET['mp_party'] ) ? sanitize_text_field( $_GET['mp_party'] ) : '';
							foreach ( $parties as $party ) :
								?>
								<option value="<?php echo esc_attr( $party ); ?>" <?php selected( $selected_party, $party ); ?>>
									<?php echo esc_html( $party ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="mp-filter-field">
						<label for="mp-constituency"><?php esc_html_e( 'Constituency:', 'mp-directory' ); ?></label>
						<select id="mp-constituency" name="mp_constituency">
							<option value=""><?php esc_html_e( 'All Constituencies', 'mp-directory' ); ?></option>
							<?php
							$constituencies        = MP_Directory\mp_directory_get_constituencies();
							$selected_constituency = isset( $_GET['mp_constituency'] ) ? sanitize_text_field( $_GET['mp_constituency'] ) : '';
							foreach ( $constituencies as $constituency ) :
								?>
								<option value="<?php echo esc_attr( $constituency ); ?>" <?php selected( $selected_constituency, $constituency ); ?>>
									<?php echo esc_html( $constituency ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="mp-filter-field">
						<button type="submit" class="mp-filter-submit">
							<?php esc_html_e( 'Filter', 'mp-directory' ); ?>
						</button>
						<?php if ( ! empty( $_GET['s'] ) || ! empty( $_GET['mp_party'] ) || ! empty( $_GET['mp_constituency'] ) ) : ?>
							<a href="<?php echo esc_url( get_post_type_archive_link( 'mp' ) ); ?>" class="mp-filter-reset">
								<?php esc_html_e( 'Clear', 'mp-directory' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</form>
		</div>

		<!-- MPs Grid -->
		<?php if ( have_posts() ) : ?>
			<div class="mp-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'mp-directory', 'card' );
					// Fallback to plugin template
					if ( ! locate_template( 'mp-directory-card.php' ) ) {
						include MP_DIRECTORY_PATH . 'templates/parts/mp-card.php';
					}
				endwhile;
				?>
			</div>

			<!-- Pagination -->
			<div class="mp-pagination">
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 2,
						'prev_text' => '&laquo; ' . __( 'Previous', 'mp-directory' ),
						'next_text' => __( 'Next', 'mp-directory' ) . ' &raquo;',
					)
				);
				?>
			</div>

		<?php else : ?>
			<div class="mp-no-results">
				<p><?php esc_html_e( 'No Members of Parliament found.', 'mp-directory' ); ?></p>
				<?php if ( ! empty( $_GET['s'] ) || ! empty( $_GET['mp_party'] ) || ! empty( $_GET['mp_constituency'] ) ) : ?>
					<p>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'mp' ) ); ?>" class="button">
							<?php esc_html_e( 'View All MPs', 'mp-directory' ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php
get_footer();
