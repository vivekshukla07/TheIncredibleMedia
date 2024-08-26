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

class Post extends Condition_Base {

	public function get_target_control() {
		return array(
			'label'       => esc_html__( 'Liste des articles', 'eac-components' ),
			'type'        => 'eac-select2',
			'object_type' => 'post',
			'multiple'    => true,
			'render_type' => 'none',
			'condition'   => array(
				'element_condition_key' => 'post',
			),
		);
	}

	public function get_called_classname() {
		return get_called_class();
	}

	public function check( $settings, $value, $operateur = '', $tz = '' ) {
		if ( ! is_array( $value ) ) {
			return true;
		}

		switch ( $operateur ) {
			case 'in':
				$etat = in_array( strval( get_the_ID() ), $value, true ) ? false : true;
				break;
			case 'not_in':
				$etat = ! in_array( strval( get_the_ID() ), $value, true ) ? false : true;
				break;
		}

		return $etat;
	}
}
