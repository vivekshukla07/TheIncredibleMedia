<?php
/**
 * Class: Eac_Acf_Group_Url
 *
 * Méthode 'get_acf_supported_fields' pour la liste des champs 'URL'
 *
 * @return Affiche les URLs d'un champ ACF de type 'GROUP' pour l'article courant
 *
 * @since 1.8.3
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

class Eac_Acf_Group_Url extends Data_Tag {
	use \EACCustomWidgets\Includes\Acf\DynamicTags\Tags\Traits\Panel_Template_Trait;

	public function get_name() {
		return 'eac-addon-group-url-acf-values';
	}

	public function get_title() {
		return esc_html__( 'ACF Groupe Url', 'eac-components' );
	}

	public function get_group() {
		return 'eac-acf-groupe';
	}

	public function get_categories() {
		return array(
			TagsModule::URL_CATEGORY,
		);
	}

	public function get_panel_template_setting_key() {
		return 'acf_group_url_key';
	}

	protected function register_controls() {

		$this->add_control(
			'acf_group_url_key',
			array(
				'label'       => esc_html__( 'Champ', 'eac-components' ),
				'type'        => Controls_Manager::SELECT,
				'groups'      => Eac_Acf_Lib::get_acf_fields_options( $this->get_acf_supported_fields(), '', 'group' ),
				'label_block' => true,
			)
		);
	}

	/**
	 * get_value
	 *
	 * @param $group_key
	 * @param $sub_field_key
	 * @param $sub_meta_key
	 * @since 1.8.4
	 */
	public function get_value( array $options = array() ) {
		$field_value = '';
		$post_id     = '';
		$field       = array();
		$key         = $this->get_settings( 'acf_group_url_key' );

		if ( ! empty( $key ) ) {
			list($group_key, $sub_field_key, $sub_meta_key) = explode( '::', $key );

			// @since 1.8.4 Récupère l'ID de l'article si c'est une Page d'Options
			if ( class_exists( Eac_Acf_Options_Page::class ) ) {
				$id_page = Eac_Acf_Options_Page::get_options_page_id( $sub_field_key );
				if ( ! empty( $id_page ) ) {
					$post_id = $id_page;
				}
			}

			// Affecte l'ID de l'article courant ou de la page d'options
			$post_id = empty( $post_id ) ? get_the_ID() : (int) $post_id;

			/**
			 * Le nom du champ est = 'field_group_key_field_key'
			 * On calcule la meta_key
			 */
			$meta_key = Eac_Acf_Lib::get_acf_field_name( $sub_field_key, $sub_meta_key, $post_id );

			// Pas de meta_key pour le champ
			if ( empty( $meta_key ) ) {
				return;
			}

			if ( have_rows( $group_key ) ) {
				the_row();
				$field = get_field_object( $meta_key, $post_id );
			}
			reset_rows();

			if ( $field && ! empty( $field['value'] ) ) {
				// La valeur par défaut du champ (url type)
				$field_value = $field['value'];

				// Ne prend que la première URL si mutiples URLs
				if ( is_array( $field_value ) && isset( $field_value[0] ) ) {
					$field_value = $field_value[0];
				}

				switch ( $field['type'] ) {
					case 'email':
							$field_value = 'mailto:' . $field_value;
						break;
					case 'file': // @since 1.8.9
						switch ( $field['return_format'] ) {
							case 'array':
								$field_value = $field_value['url'];
								break;
							case 'id':
								$field_value = wp_get_attachment_url( $field_value );
								break;
						}
						break;
					case 'post_object':
					case 'relationship':
						switch ( $field['return_format'] ) {
							case 'object':
								$field_value = get_permalink( get_post( $field_value->ID )->ID );
								break;
							case 'id':
								$field_value = get_permalink( get_post( $field_value )->ID );
								break;
						}
						break;
					case 'link':
						switch ( $field['return_format'] ) {
							case 'array':
								$field_value = $field_value['url'];
								break;
						}
						break;
				}
			}
		}

		return wp_kses_post( $field_value );
	}

	protected function get_acf_supported_fields() {
		return array(
			'email',
			'link',
			'page_link',
			'url',
			'post_object',
			'relationship',
		);
	}

	public function print_panel_template() {
		$this->fix_print_panel_template();
	}
}
