<?php
/**
 * Class: Url_Cpts_Tag
 *
 * @return affiche la liste des URL de tous les articles personnalisées (CPT)
 * @since 1.6.0
 */

namespace EACCustomWidgets\Includes\Elementor\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\Core\Utils\Eac_Tools_Util;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;
use Elementor\Controls_Manager;

/**
 * Post Url
 */
class Eac_Cpts_Tag extends Data_Tag {

	public function get_name() {
		return 'eac-addon-cpt-url-tag';
	}

	public function get_title() {
		return esc_html__( 'Articles personnalisés', 'eac-components' );
	}

	public function get_group() {
		return 'eac-url';
	}

	public function get_categories() {
		return array( TagsModule::URL_CATEGORY );
	}

	public function get_panel_template_setting_key() {
		// return 'single_cpt_url';
	}

	protected function register_controls() {
		$this->add_control(
			'single_cpt_url',
			array(
				'label'       => esc_html__( 'Articles personnalisés Url', 'eac-components' ),
				'type'        => Controls_Manager::SELECT,
				'groups'      => $this->get_custom_keys_array(),
				'label_block' => true,
			)
		);
	}

	public function get_value( array $options = array() ) {
		$param_name = $this->get_settings( 'single_cpt_url' );
		if ( empty( $param_name ) ) {
			return ''; }
		return wp_kses_post( $param_name );
	}

	private function get_custom_keys_array() {
		$groups = array();
		// Ajout des pages, posts aux post_types filtrés
		add_filter(
			'eac/tools/post_types',
			function( $posttypes ) {
				return array_merge( $posttypes, array( 'page', 'post' ) );
			}
		);
		$post_types = Eac_Tools_Util::get_filter_post_types();

		foreach ( $post_types as $post_type_name => $post_type ) {
			$cpt_posts           = array();
			$options             = array();
			list($name , $label) = explode( '::', $post_type );

			$cpt_posts = $this->get_all_cpts_data( $name );
			if ( ! empty( $cpt_posts ) && ! is_wp_error( $cpt_posts ) ) {
				foreach ( $cpt_posts as $cpt_post ) {
					$options[ esc_url( get_permalink( $cpt_post->ID ) ) ] = esc_html( $cpt_post->post_title );
				}
				if ( empty( $options ) ) {
					continue;
				}

				$groups[] = array(
					'label'   => esc_html( $label ),
					'options' => $options,
				);
			}
		}

		// Supprime le filtre
		remove_all_filters( 'eac/tools/post_types' );

		return $groups;
	}

	/**
	 * get_all_cpts_data
	 *
	 * Retourne la liste des données des articles personnalisés
	 *
	 * @return ID, post_title, post_name, guid
	 */
	private function get_all_cpts_data( $post_type ) {
		global $wpdb;

		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_name, guid
				FROM {$wpdb->prefix}posts
				WHERE post_type = %s
				AND post_title != ''
				AND post_status = 'publish'",
				$post_type
			)
		);

		return $result;
	}
}
