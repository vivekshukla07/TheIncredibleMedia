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

class Day_Week extends Condition_Base {

	public function get_target_control() {

		return array(
			'label'       => esc_html__( 'Liste des jours', 'eac-components' ),
			'type'        => Controls_Manager::SELECT2,
			'default'     => array( 'sunday' ),
			'label_block' => true,
			'options'     => $this->get_day_of_the_week(),
			'multiple'    => true,
			'render_type' => 'none',
			'condition'   => array(
				'element_condition_key'    => 'day_week',
			),
		);
	}

	public function get_called_classname() {
		return get_called_class();
	}

	public function check( $settings, $value, $operateur = '', $tz = '' ) {
		if ( empty( $value ) ) {
			return false;
		}

		$nom_du_jour = strtolower( gmdate( 'l', strtotime( date_i18n( 'Y-m-d' ) ) ) );

		switch ( $operateur ) {
			case 'in':
				$etat = in_array( $nom_du_jour, $value, true ) ? false : true;
				break;
			case 'not_in':
				$etat = ! in_array( $nom_du_jour, $value, true ) ? false : true;
				break;
		}

		return $etat;
	}

	/**
	 * Get Controls Options.
	 *
	 * @access public
	 * @since 2.1.7
	 *
	 * @return array Les jours de la semaine
	 */
	protected function get_day_of_the_week() {
		return array(
			'monday'    => esc_html__( 'Lundi', 'eac-components' ),
			'tuesday'   => esc_html__( 'Mardi', 'eac-components' ),
			'wednesday' => esc_html__( 'Mercredi', 'eac-components' ),
			'thursday'  => esc_html__( 'Jeudi', 'eac-components' ),
			'friday'    => esc_html__( 'Vendredi', 'eac-components' ),
			'saturday'  => esc_html__( 'Samedi', 'eac-components' ),
			'sunday'    => esc_html__( 'Dimanche', 'eac-components' ),
		);
	}
}
