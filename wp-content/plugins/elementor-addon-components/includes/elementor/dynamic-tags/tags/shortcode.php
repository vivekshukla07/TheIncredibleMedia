<?php
/**
 * Class: Eac_Shortcode_Tag
 *
 * @return exécute le shortcode et affiche le résultat
 * @since 1.6.0
 */

namespace EACCustomWidgets\Includes\Elementor\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Eac_Shortcode_Tag extends Tag {
	public function get_name() {
		return 'eac-addon-shortcode';
	}

	public function get_title() {
		return esc_html__( 'Shortcode', 'eac-components' );
	}

	public function get_group() {
		return 'eac-site-groupe';
	}

	public function get_categories() {
		return array(
			TagsModule::TEXT_CATEGORY,
			TagsModule::URL_CATEGORY,
			TagsModule::NUMBER_CATEGORY,
			TagsModule::POST_META_CATEGORY,
			TagsModule::DATETIME_CATEGORY,
		);
	}

	protected function register_controls() {
		$this->add_control(
			'shortcode',
			array(
				'label'   => esc_html__( 'Shortcode', 'eac-components' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 8,
			)
		);

		$this->add_control(
			'shortcode_escape',
			array(
				'label'        => esc_html__( 'Échappement du code court', 'eac-components' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'oui', 'eac-components' ),
				'label_off'    => esc_html__( 'non', 'eac-components' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
	}

	public function render() {
		$settings = $this->get_settings();

		if ( empty( $settings['shortcode'] ) ) {
			return;
		}

		$shortcode_value  = $settings['shortcode'];
		$value            = do_shortcode( $shortcode_value );
		$should_escape    = 'yes' === $this->get_settings( 'shortcode_escape' ) ? true : false;

		if ( $should_escape ) {
			$value = wp_kses_post( $value );
		}

		echo $value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
