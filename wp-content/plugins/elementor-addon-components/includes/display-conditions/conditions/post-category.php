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

class Post_Category extends Condition_Base {

	public function get_target_control() {
		return array(
			'label'       => esc_html__( 'Liste des catégories', 'eac-components' ),
			'type'        => 'eac-select2',
			'object_type' => 'post',
			'query_type'  => 'term',
			'query_taxo'  => 'category',
			'multiple'    => true,
			'render_type' => 'none',
			'condition'   => array(
				'element_condition_key' => 'post_category',
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

		$categories = get_the_category( get_the_ID() );
		if ( ! empty( $categories ) ) {
			$categories_ids = array();
			foreach ( $categories as $index => $category ) {
				array_push( $categories_ids, $category->cat_ID );
			}

			switch ( $operateur ) {
				case 'in':
					$etat = ! empty( array_intersect( $categories_ids, $value ) ) ? false : true;
					break;
				case 'not_in':
					$etat = empty( array_intersect( $categories_ids, $value ) ) ? false : true;
					break;
			}

			return $etat;
		}
		return true;
	}
}
