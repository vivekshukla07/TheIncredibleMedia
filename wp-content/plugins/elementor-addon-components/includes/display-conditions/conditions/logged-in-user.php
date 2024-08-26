<?php
/**
 * Class: Logged_In_User
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

class Logged_In_User extends Condition_Base {

	public function get_target_control() {

		return array(
			'label'       => esc_html__( 'Valeur', 'eac-components' ),
			'type'        => Controls_Manager::SELECT,
			'options'     => array(
				'not_logged_in' => esc_html__( 'Non connecté', 'eac-components' ),
			),
			'default'     => 'not_logged_in',
			'label_block' => true,
			'render_type' => 'none',
			'condition'   => array(
				'element_condition_key'    => 'logged_in_user',
			),
		);
	}

	public function get_called_classname() {
		return get_called_class();
	}

	public function check( $settings, $value, $operateur = '', $tz = '' ) {
		if ( ! is_user_logged_in() || empty( $value ) ) {
			return false;
		}
		return true;
	}
}
