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

class User_Lang extends Condition_Base {

	private $code_lang = 'en';

	public function get_target_control() {

		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$langue = substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 );
			if ( 'fr' === $langue ) {
				$this->code_lang = $langue;
			}
		}
		$languages = $this->get_langs( $this->code_lang );

		return array(
			'label'          => esc_html__( 'Liste des langages', 'eac-components' ),
			'type'           => 'eac-select2',
			'select2Options' => $languages,
			'multiple'       => true,
			'render_type'    => 'none',
			'condition'      => array(
				'element_condition_key' => 'user_lang',
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
				$etat = in_array( $this->code_lang, $value, true ) ? false : true;
				break;
			case 'not_in':
				$etat = ! in_array( $this->code_lang, $value, true ) ? false : true;
				break;
		}

		return $etat;
	}

	public function get_langs( $client_lang = 'en' ) {
		$languages = include __DIR__ . '/../languages.php';
		$langs     = array();

		foreach ( $languages as $language => $properties ) {
			$val                              = $properties[ $client_lang ];
			$langs[ strtolower( $language ) ] = $val;
		}
		return $langs;
	}
}
