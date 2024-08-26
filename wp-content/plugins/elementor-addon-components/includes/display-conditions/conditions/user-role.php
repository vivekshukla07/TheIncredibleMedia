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

class User_Role extends Condition_Base {

	public function get_target_control() {
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}
		$roles = array();

		foreach ( get_editable_roles() as $role_id => $role_data ) {
			$roles[ $role_id ] = translate_user_role( ucfirst( $role_data['name'] ) );
		}

		return array(
			'label'       => esc_html__( 'Liste des rôles', 'eac-components' ),
			'type'        => Controls_Manager::SELECT2,
			'label_block' => true,
			'default'     => array(),
			'options'     => $roles,
			'multiple'    => true,
			'render_type' => 'none',
			'condition'   => array(
				'element_condition_key' => 'user_role',
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

		$user = wp_get_current_user();
		switch ( $operateur ) {
			case 'in':
				$etat = ! empty( array_intersect( $value, $user->roles ) ) ? false : true;
				break;
			case 'not_in':
				$etat = empty( array_intersect( $value, $user->roles ) ) ? false : true;
				break;
		}

		return $etat;
	}
}
