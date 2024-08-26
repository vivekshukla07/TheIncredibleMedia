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
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Date_Compare extends Condition_Base {

	public function get_target_control() {

		return array(
			'label'          => esc_html__( 'Date', 'eac-components' ),
			'type'           => Controls_Manager::DATE_TIME,
			'picker_options' => array(
				'dateFormat' => 'Y-m-d',
				'enableTime' => false,
				// 'allowInput' => true,
			),
			'dynamic'        => array(
				'active'     => true,
				'categories' => array(
					TagsModule::DATETIME_CATEGORY,
				),
			),
			'label_block'    => true,
			'render_type'    => 'none',
			'condition'      => array(
				'element_condition_key' => 'date_compare',
			),
		);
	}

	public function get_called_classname() {
		return get_called_class();
	}

	public function check( $settings, $value, $operateur = '', $tz = '' ) {
		$date_du_jour = ! empty( $tz ) ? $tz : date_i18n( 'Y-m-d' );

		if ( ! strtotime( $value ) ) {
			return true;
		}

		$value        = sanitize_text_field( $value );
		$date_compare = date_i18n( 'Y-m-d', strtotime( $value ) );

		switch ( $operateur ) {
			case 'less_than':
				$result = $date_du_jour < $date_compare;
				break;
			case 'equal':
				$result = $date_du_jour === $date_compare;
				break;
			case 'not_equal':
				$result = $date_du_jour !== $date_compare;
				break;
			case 'more_than':
				$result = $date_du_jour > $date_compare;
				break;
			default:
				$result = false;
		}
		return ! $result;
	}
}
