<?php
namespace EACCustomWidgets\Includes\Widgets\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

trait Badge_New_Trait {

	protected function register_new_content_controls( $args = array() ) {

		$this->add_control(
			'new_text',
			array(
				'label'       => esc_html__( 'Texte', 'eac-components' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'ai'          => array( 'active' => false ),
				'default'     => esc_html__( 'Nouveau', 'eac-components' ),
				'label_block' => false,
			)
		);

		$this->add_control(
			'new_date',
			array(
				'label'       => esc_html__( 'Nombre de jours', 'eac-components' ),
				'type'        => Controls_Manager::TEXT,
				'ai'          => array( 'active' => false ),
				'description' => esc_html__( "Le badge sera affiché pendant 'Nombre de jours' après la création de la fiche du produit", 'eac-components' ),
				'label_block' => false,
			)
		);

		$this->add_control(
			'new_position',
			array(
				'label'        => esc_html__( 'Position', 'eac-components' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'  => array(
						'title' => esc_html__( 'Gauche', 'eac-components' ),
						'icon'  => 'eicon-order-start',
					),
					'right' => array(
						'title' => esc_html__( 'Droite', 'eac-components' ),
						'icon'  => 'eicon-order-end',
					),
				),
				'default'      => 'left',
				'toggle'       => false,
				'prefix_class' => 'badge-new-pos-',
			)
		);
	}

	protected function register_new_style_controls( $args = array() ) {

		$this->add_control(
			'new_color',
			array(
				'label'     => esc_html__( 'Couleur', 'eac-components' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
				'selectors' => array( '{{WRAPPER}} .badge-new' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'new_bg',
			array(
				'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array( 'default' => Global_Colors::COLOR_SECONDARY ),
				'selectors' => array( '{{WRAPPER}} .badge-new' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'new_typo',
				'label'    => esc_html__( 'Typographie', 'eac-components' ),
				'global'   => array( 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ),
				'selector' => '{{WRAPPER}} .badge-new',
			)
		);

		$this->add_control(
			'new_width',
			array(
				'label'     => esc_html__( 'Largeur', 'eac-components' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 10,
				'max'       => 250,
				'step'      => 5,
				'default'   => 90,
				'selectors' => array( '{{WRAPPER}} .badge-new' => 'width: {{VALUE}}px;' ),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'new_height',
			array(
				'label'     => esc_html__( 'Hauteur', 'eac-components' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 10,
				'max'       => 100,
				'step'      => 5,
				'default'   => 25,
				'selectors' => array( '{{WRAPPER}} .badge-new' => 'height: {{VALUE}}px;' ),
			)
		);

		$this->add_control(
			'new_radius',
			array(
				'label'              => esc_html__( 'Rayon de la bordure', 'eac-components' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => array( 'px', '%' ),
				'allowed_dimensions' => array( 'top', 'right', 'bottom', 'left' ),
				'default'            => array(
					'top'      => 0,
					'right'    => 0,
					'bottom'   => 0,
					'left'     => 0,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'          => array(
					'{{WRAPPER}} .badge-new' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
	}
}
