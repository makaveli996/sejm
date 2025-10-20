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

	public function __construct() {
		add_action( 'acf/init', array( $this, 'register_field_groups' ) );
	}

	public function register_field_groups() {
		// Check if ACF function exists
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'                   => 'group_mp_details',
				'title'                 => __( 'Szczegóły posła', 'mp-directory' ),
				'fields'                => array(
					array(
						'key'               => 'field_mp_photo_url',
						'label'             => __( 'URL zdjęcia', 'mp-directory' ),
						'name'              => 'mp_photo_url',
						'type'              => 'url',
						'instructions'      => __( 'Oryginalny URL zdjęcia z API (tylko referencja)', 'mp-directory' ),
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
					),
					array(
						'key'               => 'field_mp_sejm_photo_url',
						'label'             => __( 'URL zdjęcia Sejm (pełne)', 'mp-directory' ),
						'name'              => 'mp_sejm_photo_url',
						'type'              => 'url',
						'instructions'      => __( 'Pełnowymiarowe zdjęcie z API Sejmu', 'mp-directory' ),
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
					),
					array(
						'key'               => 'field_mp_sejm_photo_mini',
						'label'             => __( 'URL zdjęcia Sejm (mini)', 'mp-directory' ),
						'name'              => 'mp_sejm_photo_mini',
						'type'              => 'url',
						'instructions'      => __( 'Pomniejszone zdjęcie z API Sejmu (zoptymalizowane dla archiwum)', 'mp-directory' ),
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
					),
					array(
						'key'               => 'field_mp_first_name',
						'label'             => __( 'Imię', 'mp-directory' ),
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
					array(
						'key'               => 'field_mp_last_name',
						'label'             => __( 'Nazwisko', 'mp-directory' ),
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
					array(
						'key'               => 'field_mp_full_name',
						'label'             => __( 'Pełne imię i nazwisko', 'mp-directory' ),
						'name'              => 'mp_full_name',
						'type'              => 'text',
						'instructions'      => __( 'Pełne imię i nazwisko do filtrowania/sortowania', 'mp-directory' ),
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
					),
					array(
						'key'               => 'field_mp_constituency',
						'label'             => __( 'Okręg wyborczy', 'mp-directory' ),
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
					array(
						'key'               => 'field_mp_party',
						'label'             => __( 'Partia', 'mp-directory' ),
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
					array(
						'key'               => 'field_mp_term',
						'label'             => __( 'Kadencja', 'mp-directory' ),
						'name'              => 'mp_term',
						'type'              => 'text',
						'instructions'      => __( 'Kadencja Sejmu (np. 10)', 'mp-directory' ),
						'required'          => 0,
						'conditional_logic' => 0,
						'wrapper'           => array(
							'width' => '50',
							'class' => '',
							'id'    => '',
						),
					),
					array(
						'key'               => 'field_mp_birthdate',
						'label'             => __( 'Data urodzenia', 'mp-directory' ),
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
					array(
						'key'               => 'field_mp_education',
						'label'             => __( 'Wykształcenie', 'mp-directory' ),
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
					array(
						'key'               => 'field_mp_biography',
						'label'             => __( 'Biografia', 'mp-directory' ),
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
					array(
						'key'               => 'field_mp_contacts',
						'label'             => __( 'Informacje kontaktowe', 'mp-directory' ),
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
						'button_label'      => __( 'Dodaj kontakt', 'mp-directory' ),
						'sub_fields'        => array(
							array(
								'key'   => 'field_mp_contact_label',
								'label' => __( 'Etykieta', 'mp-directory' ),
								'name'  => 'label',
								'type'  => 'text',
								'width' => '30',
							),
							array(
								'key'   => 'field_mp_contact_value',
								'label' => __( 'Wartość', 'mp-directory' ),
								'name'  => 'value',
								'type'  => 'text',
								'width' => '50',
							),
							array(
								'key'     => 'field_mp_contact_type',
								'label'   => __( 'Typ', 'mp-directory' ),
								'name'    => 'type',
								'type'    => 'select',
								'width'   => '20',
								'choices' => array(
									'email' => __( 'Email', 'mp-directory' ),
									'phone' => __( 'Telefon', 'mp-directory' ),
									'fax'   => __( 'Faks', 'mp-directory' ),
									'other' => __( 'Inne', 'mp-directory' ),
								),
							),
						),
					),
					array(
						'key'               => 'field_mp_social',
						'label'             => __( 'Media społecznościowe', 'mp-directory' ),
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
						'button_label'      => __( 'Dodaj link społecznościowy', 'mp-directory' ),
						'sub_fields'        => array(
							array(
								'key'     => 'field_mp_social_network',
								'label'   => __( 'Sieć', 'mp-directory' ),
								'name'    => 'network',
								'type'    => 'select',
								'width'   => '30',
								'choices' => array(
									'twitter'   => __( 'Twitter/X', 'mp-directory' ),
									'facebook'  => __( 'Facebook', 'mp-directory' ),
									'instagram' => __( 'Instagram', 'mp-directory' ),
									'linkedin'  => __( 'LinkedIn', 'mp-directory' ),
									'youtube'   => __( 'YouTube', 'mp-directory' ),
									'website'   => __( 'Strona internetowa', 'mp-directory' ),
									'other'     => __( 'Inne', 'mp-directory' ),
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
					array(
						'key'               => 'field_mp_extra_json',
						'label'             => __( 'Dodatkowe dane (JSON)', 'mp-directory' ),
						'name'              => 'mp_extra_json',
						'type'              => 'textarea',
						'instructions'      => __( 'Surowy zrzut JSON dla niezmapowanych pól API', 'mp-directory' ),
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
