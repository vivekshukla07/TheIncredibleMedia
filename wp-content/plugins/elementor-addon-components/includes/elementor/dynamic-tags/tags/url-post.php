<?php
/**
 * Class: Eac_Posts_Tag
 *
 * @return affiche la liste des URL de tous les articles
 * @since 1.6.0
 */

namespace EACCustomWidgets\Includes\Elementor\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;
use Elementor\Controls_Manager;

/**
 * Post Url
 */
class Eac_Posts_Tag extends Data_Tag {

	public function get_name() {
		return 'eac-addon-post-url-tag';
	}

	public function get_title() {
		return esc_html__( 'Articles', 'eac-components' );
	}

	public function get_group() {
		return 'eac-url';
	}

	public function get_categories() {
		return array( TagsModule::URL_CATEGORY );
	}

	public function get_panel_template_setting_key() {
		return 'single_post_url';
	}

	protected function register_controls() {
		$this->add_control(
			'single_post_url',
			array(
				'label'       => esc_html__( 'Articles Url', 'eac-components' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $this->get_all_posts_url(),
				'label_block' => true,
			)
		);
	}

	public function get_value( array $options = array() ) {
		$param_name = $this->get_settings( 'single_post_url' );
		return wp_kses_post( $param_name );
	}

	/**
	 * Retourne la liste des URLs des articles/pages
	 *
	 * @Param {$posttype} Le type d'article 'post' ou 'page'
	 * @Return Un tableau "URL du post" => "Titre du post"
	 */
	private function get_all_posts_url( $posttype = 'post' ) {
		$post_list = array( '' => esc_html__( 'Select...', 'eac-components' ) );

		$data = get_posts(
			array(
				'post_type'      => $posttype,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		if ( ! empty( $data ) && ! is_wp_error( $data ) ) {
			foreach ( $data as $key ) {
				/** if (! function_exists('pll_the_languages')) { */
					$post_list[ esc_url( get_permalink( $key->ID ) ) ] = $key->post_title;
				/**
				} else { // PolyLang
					$post_id_pll = pll_get_post($key->ID);
					if ($post_id_pll) {
						$post_list[get_permalink($post_id_pll)] = $key->post_title;
					} else {
						$post_list[ esc_url( get_permalink( $key->ID ) ) ] = $key->post_title;
					}
				}*/
			}
		}
		return $post_list;
	}
}
