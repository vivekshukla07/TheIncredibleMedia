<?php
/**
 * Class: Eac_Injection_Motion_Effects
 *
 * Description:
 *
 * @since 1.9.6
 */

namespace EACCustomWidgets\Includes\Elementor\Injection;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EACCustomWidgets\EAC_Plugin;
use EACCustomWidgets\Core\Utils\Eac_Tools_Util;
use Elementor\Controls_Manager;
use Elementor\Element_Base;
use Elementor\Plugin;

class Eac_Injection_Motion_Effects {

	/**
	 * @var $animation_list
	 *
	 * Liste des animations
	 */
	private $animation_list = array(
		array(
			'label'   => 'Default',
			'options' => array( '' => 'None' ),
		),
		array(
			'label'   => 'Back',
			'options' => array(
				'backInDown'  => 'Back down',
				'backInLeft'  => 'Back left',
				'backInRight' => 'Back right',
				'backInUp'    => 'Back up',
			),
		),
		array(
			'label'   => 'Bounce',
			'options' => array(
				'bounceIn'      => 'Bounce',
				'bounceInDown'  => 'Bounce down',
				'bounceInLeft'  => 'Bounce left',
				'bounceInRight' => 'Bounce right',
				'bounceInUp'    => 'Bounce up',
			),
		),
		array(
			'label'   => 'FadeIn',
			'options' => array(
				'fadeIn'      => 'fadeIn',
				'fadeInDown'  => 'fadeInDown',
				'fadeInLeft'  => 'fadeInLeft',
				'fadeInRight' => 'fadeInRight',
				'fadeInUp'    => 'fadeInUp',
			),
		),
		array(
			'label'   => 'Lightspeed',
			'options' => array(
				'Lightspeed'        => 'Light speed',
				'lightSpeedInRight' => 'Light speed right',
				'lightSpeedInLeft'  => 'Light speed left',
			),
		),
		array(
			'label'   => 'Slide',
			'options' => array(
				'slideInDown'  => 'Slide down',
				'slideInLeft'  => 'Slide left',
				'slideInRight' => 'Slide right',
				'slideInUp'    => 'Slide up',
			),
		),
		array(
			'label'   => 'Zoom',
			'options' => array(
				'zoomIn'      => 'Zoom',
				'zoomInDown'  => 'Zoom down',
				'zoomInLeft'  => 'Zoom left',
				'zoomInRight' => 'Zoom right',
				'zoomInUp'    => 'Zoom up',
			),
		),
		array(
			'label'   => 'Attention seekers',
			'options' => array(
				'bounce'     => 'Bounce',
				'flash'      => 'flash',
				'rubberBand' => 'rubberBand',
				'shakeX'     => 'shakeX',
				'shakeY'     => 'shakeY',
				'swing'      => 'swing',
				'tada'       => 'tada',
				'wobble'     => 'wobble',
				'jello'      => 'jello',
				'heartBeat'  => 'heartBeat',
			),
		),
	);

	/**
	 * @var $active_breakpoints
	 *
	 * La liste des breakpoints actifs
	 */
	private $active_breakpoints = array();

	/**
	 * @var $active_devices
	 *
	 * La liste ordonnée des breakpoints actifs
	 */
	private $active_devices = array();

	/**
	 * @var $target_elements
	 *
	 * La liste des éléments cibles
	 */
	private $target_elements = array( 'widget' );

	/**
	 * @var $device_options
	 *
	 * La liste des breakpoints actifs pour les options du control
	 * $device_options[$device] = $label;
	 */
	private $device_options = array();

	/**
	 * Add Action hook
	 */
	public function __construct() {
		add_action( 'elementor/element/after_section_end', array( $this, 'inject_section' ), 10, 2 );

		add_action( 'elementor/frontend/widget/before_render', array( $this, 'render_animation' ) );

		add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ) );
	}

	/**
	 * enqueue_scripts
	 *
	 * Mets le script dans le file
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'eac-motion-effect', EAC_Plugin::instance()->get_script_url( 'assets/js/elementor/eac-element-effect' ), array( 'jquery', 'elementor-frontend' ), '1.9.6', true );
		wp_enqueue_style( 'animate-motion-effect', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', array(), '4.1.1' );
	}

	/**
	 * enqueue_editor_scripts
	 *
	 * Enqueue le script dans l'éditeur
	 */
	public function enqueue_editor_scripts() {
		wp_enqueue_script( 'eac-indicator-motion', EAC_Plugin::instance()->get_script_url( 'assets/js/elementor/eac-indicator-motion' ), array(), '2.1.8', true );
	}

	/**
	 * inject_section
	 *
	 * @param Element_Base $element        abstract class Element_Base extends Controls_Stack
	 * @param String       $section_id
	 */
	public function inject_section( $element, $section_id ) {

		if ( ! $element instanceof Element_Base ) {
			return;
		}

		if ( 'section_effects' === $section_id && in_array( $element->get_type(), $this->target_elements, true ) ) {

			// Les breakpoints actifs
			$this->active_breakpoints = Plugin::$instance->breakpoints->get_active_breakpoints();

			// Les arguments pour ajouter le device 'desktop'
			$args = array(
				'add_desktop' => true,
				'reverse'     => true,
			);

			// La liste des devices
			$this->active_devices = Plugin::$instance->breakpoints->get_active_devices_list( $args );

			// Les options du control
			foreach ( $this->active_devices as $device ) {
				$label                           = 'desktop' === $device ? esc_html__( 'Ordinateur', 'eac-components' ) : $this->active_breakpoints[ $device ]->get_label();
				$this->device_options[ $device ] = $label;
			}

			// Par défaut supprime les mobiles des devices actifs
			if ( ! empty( $this->active_devices ) ) {
				$this->active_devices = array_diff( $this->active_devices, array( 'mobile_extra', 'mobile' ) );
			}

			/** Début de la section */
			$element->start_controls_section(
				'eac_custom_element_effect',
				array(
					'label' => esc_html__( 'EAC effets de mouvement', 'eac-components' ),
					'tab'   => Controls_Manager::TAB_ADVANCED,
				)
			);

				/** Motion effects */
				$element->add_control(
					'eac_element_motion_effect',
					array(
						'label'        => esc_html__( "Animations d'entrée", 'eac-components' ),
						'type'         => Controls_Manager::SWITCHER,
						'label_on'     => esc_html__( 'oui', 'eac-components' ),
						'label_off'    => esc_html__( 'non', 'eac-components' ),
						'return_value' => 'yes',
						'default'      => '',
					)
				);

				$element->add_control(
					'eac_element_motion_type',
					array(
						'label'       => esc_html__( 'Type', 'eac-components' ),
						'type'        => Controls_Manager::SELECT,
						'label_block' => true,
						'groups'      => $this->animation_list,
						'default'     => '',
						'multiple'    => false,
						'condition'   => array( 'eac_element_motion_effect' => 'yes' ),
					)
				);

				$element->add_control(
					'eac_element_motion_duration',
					array(
						'label'     => esc_html__( 'Durée (s)', 'eac-components' ),
						'type'      => Controls_Manager::NUMBER,
						'default'   => 2,
						'min'       => 1,
						'max'       => 5,
						'step'      => 1,
						'condition' => array( 'eac_element_motion_effect' => 'yes' ),
					)
				);

				$element->add_control(
					'eac_element_motion_trigger',
					array(
						'label'     => esc_html__( 'Seuils de déclenchement', 'eac-components' ),
						'type'      => Controls_Manager::SLIDER,
						'default'   => array(
							'sizes' => array(
								'start' => 10,
								'end'   => 90,
							),
							'unit'  => '%',
						),
						'labels'    => array(
							esc_html__( 'Haut', 'eac-components' ),
							esc_html__( 'Bas', 'eac-components' ),
						),
						'scales'    => 1,
						'handles'   => 'range',
						'condition' => array( 'eac_element_motion_effect' => 'yes' ),
					)
				);

				$element->add_control(
					'eac_element_motion_devices',
					array(
						'label'       => esc_html__( 'Actif avec', 'eac-components' ),
						'type'        => Controls_Manager::SELECT2,
						'multiple'    => true,
						'label_block' => true,
						'default'     => $this->active_devices,
						'options'     => $this->device_options,
						'separator'   => 'before',
						'condition'   => array( 'eac_element_motion_effect' => 'yes' ),
					)
				);

			$element->end_controls_section();
		}
	}

	/**
	 * render_animation
	 *
	 * Modifie l'objet avant le rendu du frontend
	 *
	 * @param $element  Element_Base
	 */
	public function render_animation( $element ) {
		$data     = $element->get_data();
		$type     = $data['elType'];
		$settings = $element->get_settings_for_display();

		if ( ! in_array( $element->get_type(), $this->target_elements, true ) ) {
			return;
		}

		if ( isset( $settings['eac_element_motion_effect'] ) && 'yes' === $settings['eac_element_motion_effect'] && '' !== $settings['eac_element_motion_type'] ) {

			$args_type = array(
				'id'       => $element->get_id(),
				'type'     => esc_html( $settings['eac_element_motion_type'] ),
				'duration' => absint( $settings['eac_element_motion_duration'] ) . 's',
				'top'      => isset( $settings['eac_element_motion_trigger']['sizes']['start'] ) ? absint( $settings['eac_element_motion_trigger']['sizes']['start'] ) : 10,
				'bottom'   => isset( $settings['eac_element_motion_trigger']['sizes']['end'] ) ? 100 - absint( $settings['eac_element_motion_trigger']['sizes']['end'] ) : 10,
				'devices'  => isset( $settings['eac_element_motion_devices'] ) ? array_map( 'esc_attr', $settings['eac_element_motion_devices'] ) : array( 'desktop', 'tablet' ),
			);

			$element->add_render_attribute(
				'_wrapper',
				array(
					'class'                    => 'eac-element_motion-class',
					'style'                    => 'visibility:hidden;',
					'data-eac_settings-motion' => wp_json_encode( $args_type ),
				)
			);
		}
	}
}
new Eac_Injection_Motion_Effects();
