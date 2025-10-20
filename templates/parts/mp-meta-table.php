<?php
/**
 * Template Part: MP Meta Table
 *
 * @package MP_Directory
 */

$mp_id        = get_the_ID();
$first_name   = MP_Directory\mp_directory_get_field( 'mp_first_name', $mp_id );
$last_name    = MP_Directory\mp_directory_get_field( 'mp_last_name', $mp_id );
$party        = MP_Directory\mp_directory_get_field( 'mp_party', $mp_id );
$constituency = MP_Directory\mp_directory_get_field( 'mp_constituency', $mp_id );
$term         = MP_Directory\mp_directory_get_field( 'mp_term', $mp_id );
$birthdate    = MP_Directory\mp_directory_get_field( 'mp_birthdate', $mp_id );
$education    = MP_Directory\mp_directory_get_field( 'mp_education', $mp_id );

// Build meta items
$meta_items = array();

if ( $first_name ) {
	$meta_items[] = array(
		'label' => __( 'First Name', 'mp-directory' ),
		'value' => $first_name,
	);
}

if ( $last_name ) {
	$meta_items[] = array(
		'label' => __( 'Last Name', 'mp-directory' ),
		'value' => $last_name,
	);
}

if ( $party ) {
	$meta_items[] = array(
		'label' => __( 'Party', 'mp-directory' ),
		'value' => $party,
	);
}

if ( $constituency ) {
	$meta_items[] = array(
		'label' => __( 'Constituency', 'mp-directory' ),
		'value' => $constituency,
	);
}

if ( $term ) {
	$meta_items[] = array(
		'label' => __( 'Term', 'mp-directory' ),
		'value' => $term,
	);
}

if ( $birthdate ) {
	$formatted_date = date_i18n( get_option( 'date_format' ), strtotime( $birthdate ) );
	$meta_items[] = array(
		'label' => __( 'Date of Birth', 'mp-directory' ),
		'value' => $formatted_date,
	);
}

if ( $education ) {
	$meta_items[] = array(
		'label' => __( 'Education', 'mp-directory' ),
		'value' => $education,
	);
}

if ( ! empty( $meta_items ) ) :
	?>
	<section class="mp-section mp-meta">
		<h2><?php esc_html_e( 'Details', 'mp-directory' ); ?></h2>
		<table class="mp-meta-table">
			<tbody>
				<?php foreach ( $meta_items as $item ) : ?>
					<tr>
						<th><?php echo esc_html( $item['label'] ); ?></th>
						<td><?php echo esc_html( $item['value'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>
<?php endif; ?>
