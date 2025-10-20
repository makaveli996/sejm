<?php
/**
 * Template Part: MP Card
 *
 * @package MP_Directory
 */

$mp_id        = get_the_ID();
$party        = MP_Directory\mp_directory_get_field( 'mp_party', $mp_id );
$constituency = MP_Directory\mp_directory_get_field( 'mp_constituency', $mp_id );
$birthdate    = MP_Directory\mp_directory_get_field( 'mp_birthdate', $mp_id );
?>

<div class="mp-card">
	<a href="<?php the_permalink(); ?>" class="mp-card-link">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="mp-card-photo">
				<?php the_post_thumbnail( 'medium', array( 'alt' => get_the_title() ) ); ?>
			</div>
		<?php else : ?>
			<div class="mp-card-photo mp-card-photo-placeholder">
				<span class="mp-photo-icon">👤</span>
			</div>
		<?php endif; ?>

		<div class="mp-card-body">
			<h3 class="mp-card-title"><?php the_title(); ?></h3>

			<?php if ( $party ) : ?>
				<p class="mp-card-party">
					<span class="mp-party-badge"><?php echo esc_html( $party ); ?></span>
				</p>
			<?php endif; ?>

			<?php if ( $constituency ) : ?>
				<p class="mp-card-constituency">
					📍 <?php echo esc_html( $constituency ); ?>
				</p>
			<?php endif; ?>

			<?php if ( has_excerpt() ) : ?>
				<div class="mp-card-excerpt">
					<?php echo wp_trim_words( get_the_excerpt(), 15 ); ?>
				</div>
			<?php endif; ?>

			<span class="mp-card-more">
				<?php esc_html_e( 'View Profile', 'mp-directory' ); ?> &rarr;
			</span>
		</div>
	</a>
</div>
