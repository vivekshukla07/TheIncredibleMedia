<?php
/**
 * Class: Eac_Acf_Color
 *
 * @return Affiche la valeur d'un champ ACF de type 'COLOR' pour l'article courant
 *
 * @since 1.7.6
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

class Eac_Acf_Color extends Data_Tag {
	use \EACCustomWidgets\Includes\Acf\DynamicTags\Tags\Traits\Panel_Template_Trait;

	public function get_name() {
		return 'eac-addon-color-acf-values';
	}

	public function get_title() {
		return esc_html__( 'ACF Couleur', 'eac-components' );
	}

	public function get_group() {
		return 'eac-acf-groupe';
	}

	public function get_categories() {
		return array(
			TagsModule::COLOR_CATEGORY,
		);
	}

	public function get_panel_template_setting_key() {
		return 'acf_color_key';
	}

	protected function register_controls() {

		$this->add_control(
			'acf_color_key',
			array(
				'label'       => esc_html__( 'Champ', 'eac-components' ),
				'type'        => Controls_Manager::SELECT,
				'groups'      => Eac_Acf_Lib::get_acf_fields_options( $this->get_acf_supported_fields() ),
				'label_block' => true,
			)
		);
	}

	public function get_value( array $options = array() ) {
		$field_value = '';
		$post_id     = '';
		$key         = $this->get_settings( 'acf_color_key' );

		if ( ! empty( $key ) ) {
			list($field_key, $meta_key) = explode( '::', $key );

			// Récupère l'ID de l'article si c'est une  Page d'Options
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

			/** @since 1.8.7 Supporte le format array */
			if ( $field && ! empty( $field['value'] ) ) {
				$field_value = $field['value'];

				switch ( $field['return_format'] ) {
					case 'array':
						$field_value = 'rgba(' . $field_value['red'] . ',' . $field_value['green'] . ',' . $field_value['blue'] . ',' . $field_value['alpha'] . ')';
						break;
				}
			}
		}

		return $field_value;
	}

	protected function get_acf_supported_fields() {
		return array( 'color_picker' );
	}

	public function print_panel_template() {
		$this->fix_print_panel_template();
	}
}
