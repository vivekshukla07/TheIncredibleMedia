<?php
/**
 * Class: Post_User
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

class Post_Author extends Condition_Base {

	public function get_target_control() {

		return array(
			'label'       => esc_html__( 'Liste des auteurs', 'eac-components' ),
			'type'        => 'eac-select2',
			'query_type'  => 'author',
			'multiple'    => true,
			'label_block' => true,
			'render_type' => 'none',
			'condition'   => array(
				'element_condition_key' => 'post_author',
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

		$authors_id = get_post( get_the_ID() )->post_author;
		$ids        = get_the_author_meta( 'ID', $authors_id );
		$ids        = ! is_array( $ids ) ? array( $ids ) : $ids;

		switch ( $operateur ) {
			case 'in':
				$etat = ! empty( array_intersect( $ids, $value ) ) ? false : true;
				break;
			case 'not_in':
				$etat = empty( array_intersect( $ids, $value ) ) ? false : true;
				break;
		}

		return $etat;
	}
}
