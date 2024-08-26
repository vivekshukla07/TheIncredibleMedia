<?php
/**
 * Class: Eac_Post_Excerpt
 *
 * @return le résumé ou tous les paragraphes d'un article créés avec un thème basé sur les blocks
 * @since 1.6.0
 */

namespace EACCustomWidgets\Includes\Elementor\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\Core\Utils\Eac_Tools_Util;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Eac_Post_Excerpt extends Tag {
	public function get_name() {
		return 'eac-addon-post-excerpt';
	}

	public function get_title() {
		return esc_html__( 'Résumé', 'eac-components' );
	}

	public function get_group() {
		return 'eac-post';
	}

	public function get_categories() {
		return array( TagsModule::TEXT_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control(
			'excerpt_length',
			array(
				'label'   => esc_html__( 'Nombre de mots', 'eac-components' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 10,
				'max'     => 200,
				'step'    => 5,
				'default' => 25,
			)
		);
	}

	public function render() {
		$settings = $this->get_settings();
		$post = get_post();

		$longeur = empty( $settings['excerpt_length'] ) ? 25 : $settings['excerpt_length'];
		echo Eac_Tools_Util::get_post_excerpt( $post->ID, absint( $longeur ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
