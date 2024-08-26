<?php
/**
 * Class: Date_Compare
 *
 * Description:
 *
 * @since 2.1.7
 */

namespace EACCustomWidgets\Includes\DisplayConditions\Conditions;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

class Page_Static extends Condition_Base {

	public function get_target_control() {
		return array(
			'label'       => esc_html__( 'Page statique', 'eac-components' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => array(
				'front' => esc_html__( "Page d'accueil", 'eac-components' ),
				'home'  => esc_html__( 'Home page', 'eac-components' ),
				'blog'  => esc_html__( 'Page des articles', 'eac-components' ),
				'404'   => esc_html__( 'Page 404', 'eac-components' ),
			),
			'render_type' => 'none',
			'condition'   => array(
				'element_condition_key' => 'page_static',
			),
		);
	}

	public function get_called_classname() {
		return get_called_class();
	}

	public function check( $settings, $value, $operateur = '', $tz = '' ) {
		switch ( $value ) {
			case 'home':
				$result = is_front_page() && is_home();
				break;
			case 'front':
				$result = is_front_page() && ! is_home();
				break;
			case 'blog':
				$result = ! is_front_page() && is_home();
				break;
			default:
				$result = is_404();
				break;
		}
		return ! $result;
	}
}
