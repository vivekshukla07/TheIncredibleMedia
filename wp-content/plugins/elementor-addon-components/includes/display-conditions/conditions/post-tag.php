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
use EACCustomWidgets\Core\Utils\Eac_Tools_Util;
use Elementor\Controls_Manager;

class Post_Tag extends Condition_Base {

	public function get_target_control() {
		return array(
			'label'       => esc_html__( 'Liste des étiquettes', 'eac-components' ),
			'type'        => 'eac-select2',
			'object_type' => 'post',
			'query_type'  => 'term',
			'query_taxo'  => 'post_tag',
			'multiple'    => true,
			'render_type' => 'none',
			'condition'   => array(
				'element_condition_key' => 'post_tag',
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

		$etat     = true;
		$tags_ids = array();
		$tags     = get_the_tags( get_the_ID() );

		if ( ! is_wp_error( $tags ) && ! empty( $tags ) ) {
			foreach ( $tags as $index => $tag ) {
				array_push( $tags_ids, $tag->term_id );
			}

			$inside = array_intersect( $tags_ids, $value );

			switch ( $operateur ) {
				case 'in':
					$etat = ! empty( $inside ) ? false : true;
					break;
				case 'not_in':
					$etat = empty( $inside ) ? false : true;
					break;
			}
		}
		return $etat;
	}
}
