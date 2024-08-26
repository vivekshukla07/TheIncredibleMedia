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

class Date_Range extends Condition_Base {

	public function get_target_control() {
		$date_default = array( wp_date( 'Y-m-d', strtotime( '-1 week' ) ), wp_date( 'Y-m-d', strtotime( '+1 week' ) ) );

		return array(
			'label'          => esc_html__( 'Interval de dates', 'eac-components' ),
			'type'           => Controls_Manager::DATE_TIME,
			'picker_options' => array(
				'dateFormat' => 'Y-m-d',
				'enableTime' => false,
				'mode'       => 'range',
				// 'defaultDate' => $date_default,
			),
			'label_block'    => true,
			'render_type'    => 'none',
			'condition'      => array(
				'element_condition_key' => 'date_range',
			),
		);
	}

	public function get_called_classname() {
		return get_called_class();
	}

	public function check( $settings, $value, $operateur = '', $tz = '' ) {
		$date_du_jour = ! empty( $tz ) ? $tz : wp_date( 'Y-m-d' );
		$date_range   = explode( 'to', sanitize_text_field( $value ) );

		if ( ! is_array( $date_range ) || 2 !== count( $date_range ) ) {
			return true;
		}
		$date_deb = trim( $date_range[0] );
		$date_fin = trim( $date_range[1] );

		if ( empty( $date_deb ) || empty( $date_fin ) || ! strtotime( $date_deb ) || ! strtotime( $date_fin ) ) {
			return true;
		}

		$date_deb = wp_date( 'Y-m-d', strtotime( $date_deb ) );
		$date_fin = wp_date( 'Y-m-d', strtotime( $date_fin ) );

		switch ( $operateur ) {
			case 'in':
				$result = $date_du_jour >= $date_deb && $date_du_jour <= $date_fin;
				break;
			case 'not_in':
				$result = $date_du_jour < $date_deb || $date_du_jour > $date_fin;
				break;
			default:
				$result = false;
		}
		return ! $result;
	}
}
