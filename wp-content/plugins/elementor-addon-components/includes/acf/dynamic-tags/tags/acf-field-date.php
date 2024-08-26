<?php
/**
 * Class: Eac_Acf_Date
 *
 * Méthode 'get_acf_supported_fields' pour la liste des champs 'URL'
 *
 * @return La valeur d'un champ ACF de type 'Date - Date time' pour l'article courant
 *
 * @since 2.1.7
 */

namespace EACCustomWidgets\Includes\Acf\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\Includes\Acf\DynamicTags\Eac_Acf_Lib;
use EACCustomWidgets\Includes\Acf\Eac_Acf_Options_Page;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Eac_Acf_Date extends Data_Tag {
	use \EACCustomWidgets\Includes\Acf\DynamicTags\Tags\Traits\Panel_Template_Trait;

	public function get_name() {
		return 'eac-addon-date-acf-values';
	}

	public function get_title() {
		return esc_html__( 'ACF Date Time', 'eac-components' );
	}

	public function get_group() {
		return 'eac-acf-groupe';
	}

	public function get_categories() {
		return array(
			TagsModule::DATETIME_CATEGORY,
			TagsModule::TEXT_CATEGORY,
		);
	}

	public function get_panel_template_setting_key() {
		return 'acf_date_key';
	}

	protected function register_controls() {

		$options_time = array(
			'default'      => esc_html__( 'Format de sortie ACF', 'eac-components' ),
			'Y-m-d H:i:s'  => date_i18n( 'Y-m-d H:i:s' ),
			'F j, Y H:i:s' => date_i18n( 'F j, Y H:i:s' ),
			'm/d/Y H:i:s'  => date_i18n( 'm/d/Y H:i:s' ),
			'd/m/Y H:i:s'  => date_i18n( 'd/m/Y H:i:s' ),
			'Ymd H:i:s'    => date_i18n( 'Ymd H:i:s' ),
			'custom'       => esc_html__( 'Personnalité', 'eac-components' ),
		);

		$this->add_control(
			'acf_date_key',
			array(
				'label'       => esc_html__( 'Champ', 'eac-components' ),
				'type'        => Controls_Manager::SELECT,
				'groups'      => Eac_Acf_Lib::get_acf_fields_options( $this->get_acf_supported_fields() ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'acf_date_fallback',
			array(
				'label'          => esc_html__( 'Alternative', 'eac-components' ),
				'type'           => Controls_Manager::DATE_TIME,
				'picker_options' => array(
					'allowInput' => true,
				),
				'label_block'    => true,
				'condition'      => array( 'acf_date_key' => '' ),
			)
		);

		$this->add_control(
			'acf_date_format',
			array(
				'label'       => esc_html__( 'Format de sortie', 'eac-components' ),
				'type'        => Controls_Manager::SELECT,
				'description'    => sprintf(
					/* translators: 1: Date format */
					esc_html__( 'Format de date %1$s', 'eac-components' ),
					'<a href="https://flatpickr.js.org/formatting/#date-formatting-tokens" target="_autre" rel="noopener noreferrer nofollow">Site</a>'
				),
				'options'     => $options_time,
				'default'     => 'default',
				'label_block' => true,
			)
		);

		$this->add_control(
			'acf_date_custom',
			array(
				'label'       => esc_html__( 'Format personnalisé', 'eac-components' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'm/d/Y g:i a',
				'label_block' => true,
				'condition'   => array(
					'acf_date_format' => 'custom',
				),
			)
		);
	}

	public function get_value( array $options = array() ) {
		$field_value = '';
		$post_id     = '';
		$key         = $this->get_settings( 'acf_date_key' );
		$format      = $this->get_settings( 'acf_date_format' );
		$custom      = $this->get_settings( 'acf_date_custom' );

		if ( ! empty( $key ) ) {
			list($field_key, $meta_key) = explode( '::', $key );

			// Récupère l'ID de l'article si c'est une Page d'Options
			if ( class_exists( Eac_Acf_Options_Page::class ) ) {
				$id_page = Eac_Acf_Options_Page::get_options_page_id( $field_key );
				if ( ! empty( $id_page ) ) {
					$post_id = $id_page;
				}
			}

			// Affecte l'ID de l'article courant ou de la page d'options
			$post_id = empty( $post_id ) ? get_the_ID() : $post_id;

			// Récupère l'objet Field
			$field = get_field_object( $field_key, $post_id );

			if ( $field && ! empty( $field['value'] ) ) {
				// La valeur par défaut du champ
				$date_time = \DateTime::createFromFormat( $field['return_format'], $field['value'] );
				if ( 'default' === $format ) {
					$field_value = $date_time instanceof \DateTime ? $date_time->format( $field['return_format'] ) : '';
				} elseif ( 'custom' === $format && ! empty( $custom ) ) {
					$field_value = $date_time instanceof \DateTime ? $date_time->format( $custom ) : '';
				} else {
					$field_value = $date_time instanceof \DateTime ? $date_time->format( $format ) : '';
				}
			}
		}

		if ( empty( $field_value ) && $this->get_settings( 'acf_date_fallback' ) ) {
			$date_time = \DateTime::createFromFormat( 'Y-m-d H:i', $this->get_settings( 'acf_date_fallback' ) );
			if ( 'default' === $format ) {
				$field_value = $date_time instanceof \DateTime ? $date_time->format( 'Y-m-d H:i:s' ) : '';
			} elseif ( 'custom' === $format && ! empty( $custom ) ) {
				$field_value = $date_time instanceof \DateTime ? $date_time->format( $custom ) : '';
			} else {
				$field_value = $date_time instanceof \DateTime ? $date_time->format( $format ) : '';
			}
		}

		return wp_kses_post( $field_value );
	}

	protected function get_acf_supported_fields() {
		return array(
			'date_picker',
			'date_time_picker',
		);
	}

	public function print_panel_template() {
		$this->fix_print_panel_template();
	}
}
