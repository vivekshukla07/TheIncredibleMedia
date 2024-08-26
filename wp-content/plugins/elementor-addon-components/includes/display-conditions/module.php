<?php
/**
 * Class: Module
 *
 * Description:
 *
 * @since 2.1.7
 */

namespace EACCustomWidgets\Includes\DisplayConditions;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EACCustomWidgets\EAC_Plugin;

use Elementor\Controls_Manager;
use Elementor\Element_Base;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Repeater;
use Elementor\Plugin;

class Module {

	/**
	 * @var $target_elements
	 *
	 * La liste des éléments cibles
	 */
	private $target_elements = array( 'container', 'section', 'column', 'widget' );

	/**
	 * @var $instance
	 *
	 * Garantir une seule instance de la class
	 */
	private static $instance = null;

	/**
	 * @var $controller
	 *
	 * Objet de la liste des controls pour chaque condition
	 * Active les filtres nécessaires pour l'affichage
	 * Appel les méthodes de compaison pour chaque control
	 */
	private $controller;

	/** Constructeur */
	private function __construct() {
		require_once __DIR__ . '/controller.php';
		$this->controller = new Controller();

		add_action( 'elementor/element/after_section_end', array( $this, 'inject_controls' ), 10, 3 );
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ) );
	}

	/**
	 * enqueue_editor_scripts
	 *
	 * Enqueue le script dans l'éditeur
	 */
	public function enqueue_editor_scripts() {
		wp_enqueue_script( 'eac-indicator-conditions', EAC_Plugin::instance()->get_script_url( 'assets/js/elementor/eac-indicator-conditions' ), array(), '2.1.8', true );
	}

	/** Singleton de la class */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * inject_controls
	 *
	 * Inject le control après la section 'section_effects' Advanced tab
	 * pour les colonnes
	 *
	 * @param Element_Base $element    L'élément en cours d'édition
	 * @param String       $section_id L'ID de l'élément
	 * @param array        $args       Arguments de l'élément
	 */
	public function inject_controls( $element, $section_id, $args ) {

		if ( ! $element instanceof Element_Base ) {
			return;
		}

		if ( '_section_responsive' === $section_id && in_array( $element->get_type(), $this->target_elements, true ) ) {

			$element->start_controls_section(
				'eac_custom_element_condition',
				array(
					'label' => esc_html__( "EAC conditions d'affichage", 'eac-components' ),
					'tab'   => Controls_Manager::TAB_ADVANCED,
				)
			);

				$element->add_control(
					'element_condition_active',
					array(
						'label'        => esc_html__( 'Activer les conditions', 'eac-components' ),
						'type'         => Controls_Manager::SWITCHER,
						'label_on'     => esc_html__( 'oui', 'eac-components' ),
						'label_off'    => esc_html__( 'non', 'eac-components' ),
						'return_value' => 'yes',
						'default'      => '',
						'prefix_class' => 'eac-conditions-',
					)
				);

				$element->add_control(
					'element_condition_when',
					array(
						'label'       => esc_html__( "Cacher l'élément quand", 'eac-components' ),
						'type'        => Controls_Manager::SELECT,
						'options'     => array(
							'all' => esc_html__( 'Toutes les conditions sont remplies', 'eac-components' ),
							'any' => esc_html__( 'Une des conditions est remplie', 'eac-components' ),
						),
						'default'     => 'all',
						'label_block' => false,
						'render_type' => 'none',
						'condition'   => array( 'element_condition_active' => 'yes' ),
					)
				);

				$repeater = new Repeater();

				$repeater->add_control(
					'element_condition_key',
					array(
						'label'       => 'Condition',
						'type'        => Controls_Manager::SELECT,
						'groups'      => $this->controller->get_conditions_list(),
						'default'     => 'day_week',
						'label_block' => true,
						'render_type' => 'none',
					)
				);

				$repeater->add_control(
					'element_condition_operateur_date',
					array(
						'label'       => esc_html__( 'Opérateur de comparaison', 'eac-components' ),
						'type'        => Controls_Manager::CHOOSE,
						'options'     => array(
							'less_than' => array(
								'title' => esc_html__( 'Inférieure', 'eac-components' ),
								'icon'  => 'fas fa-chevron-left eac-condition-choose',
							),
							'equal'     => array(
								'title' => esc_html__( 'Égale', 'eac-components' ),
								'icon'  => 'fa-solid fa-equals eac-condition-choose',
							),
							'not_equal' => array(
								'title' => esc_html__( 'Différente', 'eac-components' ),
								'icon'  => 'fa-solid fa-not-equal eac-condition-choose',
							),
							'more_than' => array(
								'title' => esc_html__( 'Supérieure', 'eac-components' ),
								'icon'  => 'fas fa-chevron-right eac-condition-choose',
							),
						),
						'default'     => 'equal',
						'toggle'      => false,
						'label_block' => true,
						'render_type' => 'none',
						'condition'   => array(
							'element_condition_key' => 'date_compare',
						),
					)
				);

				$repeater->add_control(
					'element_condition_operateur_range',
					array(
						'label'       => esc_html__( 'Opérateur de comparaison', 'eac-components' ),
						'type'        => Controls_Manager::CHOOSE,
						'options'     => array(
							'in'     => array(
								'title' => esc_html__( 'Dans la liste', 'eac-components' ),
								'icon'  => 'far fa-sign-in-alt eac-condition-choose',
							),
							'not_in' => array(
								'title' => esc_html__( 'Pas dans la liste', 'eac-components' ),
								'icon'  => 'far fa-sign-out-alt eac-condition-choose',
							),
						),
						'default'     => 'in',
						'toggle'      => false,
						'label_block' => true,
						'render_type' => 'none',
						'condition'   => array(
							'element_condition_key!' => array( 'date_compare', 'logged_in_user', 'page_static' ),
						),
					)
				);

				// Ajout des controls pour les conditions
				$this->controller->add_controls_to_compare( $repeater );

				$condition_flat_list = wp_json_encode( $this->controller->get_conditions_flat_list() );

				$element->add_control(
					'element_condition_list',
					array(
						'type'        => Controls_Manager::REPEATER,
						'fields'      => $repeater->get_controls(),
						'default'     => array(
							array(
								'element_condition_key' => 'day_week',
							),
						),
						'title_field' => "<# let labels = $condition_flat_list; "
							. 'let label = labels[element_condition_key]; #>'
							. '{{{ label }}}',
						'button_text' => esc_html__( 'Ajouter une condition', 'eac-components' ),
						'render_type' => 'none',
						'condition'   => array(
							'element_condition_active' => 'yes',
						),
					)
				);

				$element->add_control(
					'element_condition_fallback_active',
					array(
						'label'        => esc_html__( 'Activer le contenu alternatif', 'eac-components' ),
						'description'  => esc_html__( 'Le contenu alternatif est affiché si le résultat des conditions est vraie', 'eac-components' ),
						'type'         => Controls_Manager::SWITCHER,
						'label_on'     => esc_html__( 'oui', 'eac-components' ),
						'label_off'    => esc_html__( 'non', 'eac-components' ),
						'return_value' => 'yes',
						'default'      => '',
						'render_type'  => 'none',
						'condition'    => array( 'element_condition_active' => 'yes' ),
						'separator'    => 'before',
					)
				);

				$element->add_control(
					'element_condition_fallback_content',
					array(
						'label'       => esc_html__( 'Contenu', 'eac-components' ),
						'type'        => Controls_Manager::TEXTAREA,
						'default'     => sprintf( esc_html__( 'Cette section est réservée pour les utilisateurs connectés.%sVeuillez vous identifier pour accéder à son contenu.', 'eac-components' ), '<br>' ),
						'dynamic'     => array( 'active' => true ),
						'label_block' => true,
						'render_type' => 'none',
						'condition'   => array(
							'element_condition_active' => 'yes',
							'element_condition_fallback_active' => 'yes',
						),
					)
				);

				$element->add_control(
					'element_condition_fallback_style',
					array(
						'label'     => esc_html__( 'Style', 'eac-components' ),
						'type'      => Controls_Manager::HEADING,
						'separator' => 'before',
						'condition' => array(
							'element_condition_active' => 'yes',
							'element_condition_fallback_active' => 'yes',
						),
					)
				);

				$element->add_control(
					'element_condition_fallback_color',
					array(
						'label'     => esc_html__( 'Couleur', 'eac-components' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
						'selectors' => array( '.element-condition_fallback-{{ID}}' => 'color: {{VALUE}};' ),
						'condition' => array(
							'element_condition_active' => 'yes',
							'element_condition_fallback_active' => 'yes',
						),
					)
				);

				$element->add_group_control(
					Group_Control_Typography::get_type(),
					array(
						'name'      => 'element_condition_fallback_typo',
						'label'     => esc_html__( 'Typographie', 'eac-components' ),
						'global'    => array( 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ),
						'selector'  => '.element-condition_fallback-{{ID}}',
						'condition' => array(
							'element_condition_active' => 'yes',
							'element_condition_fallback_active' => 'yes',
						),
					)
				);

				$element->add_control(
					'element_condition_fallback_bgcolor',
					array(
						'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => array( 'default' => Global_Colors::COLOR_SECONDARY ),
						'selectors' => array( '.element-condition_fallback-{{ID}}' => 'background-color: {{VALUE}};' ),
						'condition' => array(
							'element_condition_active' => 'yes',
							'element_condition_fallback_active' => 'yes',
						),
					)
				);

				$element->add_responsive_control(
					'element_condition_fallback_width',
					array(
						'label'          => esc_html__( 'Largeur', 'eac-components' ),
						'type'           => Controls_Manager::SLIDER,
						'size_units'     => array( 'px', '%', 'vw' ),
						'default'        => array(
							'size' => 70,
							'unit' => '%',
						),
						'tablet_default' => array(
							'unit' => '%',
						),
						'mobile_default' => array(
							'unit' => '%',
						),
						'range'          => array(
							'px' => array(
								'min'  => 100,
								'max'  => 1000,
								'step' => 10,
							),
							'%'  => array(
								'min'  => 10,
								'max'  => 100,
								'step' => 10,
							),
							'vw' => array(
								'min'  => 10,
								'max'  => 100,
								'step' => 10,
							),
						),
						'label_block'    => true,
						'separator'      => 'before',
						'selectors'      => array( '.element-condition_fallback-{{ID}}' => 'width: {{SIZE}}{{UNIT}};' ),
						'condition'      => array(
							'element_condition_active' => 'yes',
							'element_condition_fallback_active' => 'yes',
						),
					)
				);

				$element->add_responsive_control(
					'element_condition_fallback_height',
					array(
						'label'       => esc_html__( 'Hauteur (px)', 'eac-components' ),
						'type'        => Controls_Manager::SLIDER,
						'size_units'  => array( 'px' ),
						'default'     => array(
							'size' => 200,
							'unit' => 'px',
						),
						'range'       => array(
							'px' => array(
								'min'  => 150,
								'max'  => 2000,
								'step' => 50,
							),
						),
						'label_block' => true,
						'selectors'   => array( '.element-condition_fallback-{{ID}}' => 'min-height: {{SIZE}}{{UNIT}};' ),
						'condition'   => array(
							'element_condition_active' => 'yes',
							'element_condition_fallback_active' => 'yes',
						),
					)
				);

				$element->add_responsive_control(
					'element_condition_fallback_alignment',
					array(
						'label'     => esc_html__( 'Alignement du texte', 'eac-components' ),
						'type'      => Controls_Manager::CHOOSE,
						'options'   => array(
							'left'   => array(
								'title' => esc_html__( 'Gauche', 'eac-components' ),
								'icon'  => 'eicon-text-align-left',
							),
							'center' => array(
								'title' => esc_html__( 'Centre', 'eac-components' ),
								'icon'  => 'eicon-text-align-center',
							),
							'right'  => array(
								'title' => esc_html__( 'Droite', 'eac-components' ),
								'icon'  => 'eicon-text-align-right',
							),
						),
						'default'   => 'center',
						'selectors' => array(
							'.element-condition_fallback-{{ID}} div' => 'text-align: {{VALUE}};',
						),
						'condition' => array(
							'element_condition_active' => 'yes',
							'element_condition_fallback_active' => 'yes',
						),
					)
				);

				$element->add_group_control(
					Group_Control_Border::get_type(),
					array(
						'name'      => 'element_condition_fallback_border',
						'separator' => 'before',
						'selector'  => '.element-condition_fallback-{{ID}}',
						'condition' => array(
							'element_condition_active' => 'yes',
							'element_condition_fallback_active' => 'yes',
						),
					)
				);

				$element->add_control(
					'element_condition_fallback_radius',
					array(
						'label'              => esc_html__( 'Rayon de la bordure', 'eac-components' ),
						'type'               => Controls_Manager::DIMENSIONS,
						'size_units'         => array( 'px', '%' ),
						'allowed_dimensions' => array( 'top', 'right', 'bottom', 'left' ),
						'selectors'          => array(
							'.element-condition_fallback-{{ID}}' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						),
						'condition'          => array(
							'element_condition_active' => 'yes',
							'element_condition_fallback_active' => 'yes',
						),
					)
				);

			$element->end_controls_section();
		}
	}

} Module::instance();
