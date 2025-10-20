<?php
/**
 * Single MP Template
 *
 * @package MP_Directory
 */

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'mp-directory-single' ); ?>>
		<div class="mp-single-container">
			<!-- Hero Section -->
			<header class="mp-hero">
				<div class="mp-hero-content">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="mp-hero-photo">
							<?php the_post_thumbnail( 'medium', array( 'alt' => get_the_title() ) ); ?>
						</div>
					<?php endif; ?>

					<div class="mp-hero-info">
						<h1 class="mp-title"><?php the_title(); ?></h1>
						
						<?php
						$party        = MP_Directory\mp_directory_get_field( 'mp_party', get_the_ID() );
						$constituency = MP_Directory\mp_directory_get_field( 'mp_constituency', get_the_ID() );
						?>

						<?php if ( $party ) : ?>
							<p class="mp-party">
								<strong><?php esc_html_e( 'Party:', 'mp-directory' ); ?></strong>
								<span class="mp-party-badge"><?php echo esc_html( $party ); ?></span>
							</p>
						<?php endif; ?>

						<?php if ( $constituency ) : ?>
							<p class="mp-constituency">
								<strong><?php esc_html_e( 'Constituency:', 'mp-directory' ); ?></strong>
								<?php echo esc_html( $constituency ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</header>

			<div class="mp-content-wrapper">
				<!-- Meta Information Table -->
				<?php
				get_template_part( 'mp-directory', 'meta-table' );
				// Fallback to plugin template
				if ( ! locate_template( 'mp-directory-meta-table.php' ) ) {
					include MP_DIRECTORY_PATH . 'templates/parts/mp-meta-table.php';
				}
				?>

				<!-- Biography / Content -->
				<?php
				$biography = MP_Directory\mp_directory_get_field( 'mp_biography', get_the_ID() );
				if ( $biography || get_the_content() ) :
					?>
					<section class="mp-section mp-biography">
						<h2><?php esc_html_e( 'Biography', 'mp-directory' ); ?></h2>
						<?php if ( $biography ) : ?>
							<div class="mp-biography-content">
								<?php echo wp_kses_post( $biography ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( get_the_content() ) : ?>
							<div class="entry-content">
								<?php the_content(); ?>
							</div>
						<?php endif; ?>
					</section>
				<?php endif; ?>

				<!-- Contact Information -->
				<?php
				$contacts = MP_Directory\mp_directory_get_field( 'mp_contacts', get_the_ID() );
				if ( $contacts && is_array( $contacts ) ) :
					?>
					<section class="mp-section mp-contacts">
						<h2><?php esc_html_e( 'Contact Information', 'mp-directory' ); ?></h2>
						<ul class="mp-contact-list">
							<?php foreach ( $contacts as $contact ) : ?>
								<?php if ( ! empty( $contact['value'] ) ) : ?>
									<li class="mp-contact-item">
										<strong><?php echo esc_html( $contact['label'] ); ?>:</strong>
										<?php echo wp_kses_post( MP_Directory\mp_directory_format_contact( $contact ) ); ?>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<!-- Social Media -->
				<?php
				$social = MP_Directory\mp_directory_get_field( 'mp_social', get_the_ID() );
				if ( $social && is_array( $social ) ) :
					?>
					<section class="mp-section mp-social">
						<h2><?php esc_html_e( 'Social Media', 'mp-directory' ); ?></h2>
						<ul class="mp-social-list">
							<?php foreach ( $social as $link ) : ?>
								<?php if ( ! empty( $link['url'] ) ) : ?>
									<li class="mp-social-item">
										<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer">
											<span class="mp-social-icon">
												<?php echo esc_html( MP_Directory\mp_directory_get_social_icon( $link['network'] ) ); ?>
											</span>
											<span class="mp-social-label">
												<?php echo esc_html( ucfirst( $link['network'] ) ); ?>
											</span>
										</a>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<!-- Back to Archive -->
				<div class="mp-navigation">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'mp' ) ); ?>" class="mp-back-link">
						&larr; <?php esc_html_e( 'Back to All MPs', 'mp-directory' ); ?>
					</a>
				</div>
			</div>
		</div>
	</article>
<?php endwhile; ?>

<?php
get_footer();
