<?php
/**
 * Advanced Custom Fields Registration
 *
 * @package MP_Directory
 */

namespace MP_Directory;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ACF
 */
class ACF {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'acf/init', array( $this, 'register_field_groups' ) );
	}

	/**
	 * Register ACF field groups
	 */
	public function register_field_groups() {
		// Check if ACF function exists
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'                   => 'group_mp_details',
				'title'                 => __( 'MP Details', 'mp-directory' ),
				'fields'                => array(
					// Photo URL
					array(
						'key'               => 'field_mp_photo_url',
						'label'             => __( 'Photo URL', 'mp-directory' ),
						'name'              => 'mp_photo_url',
						'type'              => 'url',
						'instructions'      => __( 'Original API photo URL (reference only)', 'mp-directory' ),
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
					),
					// First Name
					array(
						'key'               => 'field_mp_first_name',
						'label'             => __( 'First Name', 'mp-directory' ),
						'name'              => 'mp_first_name',
						'type'              => 'text',
						'instructions'      => '',
						'required'          => 1,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
					),
					// Last Name
					array(
						'key'               => 'field_mp_last_name',
						'label'             => __( 'Last Name', 'mp-directory' ),
						'name'              => 'mp_last_name',
						'type'              => 'text',
						'instructions'      => '',
						'required'          => 1,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
					),
					// Full Name
					array(
						'key'               => 'field_mp_full_name',
						'label'             => __( 'Full Name', 'mp-directory' ),
						'name'              => 'mp_full_name',
						'type'              => 'text',
						'instructions'      => __( 'Derived full name for filtering/sorting', 'mp-directory' ),
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
					),
					// Constituency
					array(
						'key'               => 'field_mp_constituency',
						'label'             => __( 'Constituency', 'mp-directory' ),
						'name'              => 'mp_constituency',
						'type'              => 'text',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
					),
					// Party
					array(
						'key'               => 'field_mp_party',
						'label'             => __( 'Party', 'mp-directory' ),
						'name'              => 'mp_party',
						'type'              => 'text',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
					),
					// Term
					array(
						'key'               => 'field_mp_term',
						'label'             => __( 'Term', 'mp-directory' ),
						'name'              => 'mp_term',
						'type'              => 'text',
						'instructions'      => __( 'Parliamentary term (e.g., 10)', 'mp-directory' ),
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
					),
					// Birth Date
					array(
						'key'               => 'field_mp_birthdate',
						'label'             => __( 'Birth Date', 'mp-directory' ),
						'name'              => 'mp_birthdate',
						'type'              => 'date_picker',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
						'display_format'    => 'd/m/Y',
						'return_format'     => 'Y-m-d',
						'first_day'         => 1,
					),
					// Education
					array(
						'key'               => 'field_mp_education',
						'label'             => __( 'Education', 'mp-directory' ),
						'name'              => 'mp_education',
						'type'              => 'textarea',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'rows'              => 3,
					),
					// Biography
					array(
						'key'               => 'field_mp_biography',
						'label'             => __( 'Biography', 'mp-directory' ),
						'name'              => 'mp_biography',
						'type'              => 'wysiwyg',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'tabs'              => 'all',
						'toolbar'           => 'full',
						'media_upload'      => 0,
					),
					// Contacts Repeater
					array(
						'key'               => 'field_mp_contacts',
						'label'             => __( 'Contact Information', 'mp-directory' ),
						'name'              => 'mp_contacts',
						'type'              => 'repeater',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'layout'            => 'table',
						'button_label'      => __( 'Add Contact', 'mp-directory' ),
						'sub_fields'        => array(
							array(
								'key'   => 'field_mp_contact_label',
								'label' => __( 'Label', 'mp-directory' ),
								'name'  => 'label',
								'type'  => 'text',
								'width' => '30',
							),
							array(
								'key'   => 'field_mp_contact_value',
								'label' => __( 'Value', 'mp-directory' ),
								'name'  => 'value',
								'type'  => 'text',
								'width' => '50',
							),
							array(
								'key'     => 'field_mp_contact_type',
								'label'   => __( 'Type', 'mp-directory' ),
								'name'    => 'type',
								'type'    => 'select',
								'width'   => '20',
								'choices' => array(
									'email' => __( 'Email', 'mp-directory' ),
									'phone' => __( 'Phone', 'mp-directory' ),
									'fax'   => __( 'Fax', 'mp-directory' ),
									'other' => __( 'Other', 'mp-directory' ),
								),
							),
						),
					),
					// Social Media Repeater
					array(
						'key'               => 'field_mp_social',
						'label'             => __( 'Social Media', 'mp-directory' ),
						'name'              => 'mp_social',
						'type'              => 'repeater',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'layout'            => 'table',
						'button_label'      => __( 'Add Social Link', 'mp-directory' ),
						'sub_fields'        => array(
							array(
								'key'     => 'field_mp_social_network',
								'label'   => __( 'Network', 'mp-directory' ),
								'name'    => 'network',
								'type'    => 'select',
								'width'   => '30',
								'choices' => array(
									'twitter'   => __( 'Twitter/X', 'mp-directory' ),
									'facebook'  => __( 'Facebook', 'mp-directory' ),
									'instagram' => __( 'Instagram', 'mp-directory' ),
									'linkedin'  => __( 'LinkedIn', 'mp-directory' ),
									'youtube'   => __( 'YouTube', 'mp-directory' ),
									'website'   => __( 'Website', 'mp-directory' ),
									'other'     => __( 'Other', 'mp-directory' ),
								),
							),
							array(
								'key'   => 'field_mp_social_url',
								'label' => __( 'URL', 'mp-directory' ),
								'name'  => 'url',
								'type'  => 'url',
								'width' => '70',
							),
						),
					),
					// Extra JSON
					array(
						'key'               => 'field_mp_extra_json',
						'label'             => __( 'Extra Data (JSON)', 'mp-directory' ),
						'name'              => 'mp_extra_json',
						'type'              => 'textarea',
						'instructions'      => __( 'Raw JSON dump for unmapped API fields', 'mp-directory' ),
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '',
							'class' => '',
							'id'    => '',
						),
						'rows'              => 5,
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'mp',
						),
					),
				),
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen'        => '',
				'active'                => true,
				'description'           => '',
			)
		);
	}
}
