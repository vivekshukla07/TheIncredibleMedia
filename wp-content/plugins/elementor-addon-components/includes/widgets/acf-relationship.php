<?php
/**
 * Class: Acf_Relationship_Widget
 * Name: ACF Relationship
 * Slug: eac-addon-acf-relationship
 *
 * Description: Affiche et formate les entrées sélectionnées dans le champ Relationship ou Post object
 * d'un articles. Les articles sont affichées sous forme de grille.
 *
 * @since 1.8.2
 */

namespace EACCustomWidgets\Includes\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\EAC_Plugin;
use EACCustomWidgets\Core\Utils\Eac_Tools_Util;
use EACCustomWidgets\Core\Utils\Eac_Helpers_Util;
use EACCustomWidgets\Core\Eac_Config_Elements;
use EACCustomWidgets\Includes\Acf\DynamicTags\Eac_Acf_Lib;
use EACCustomWidgets\Includes\Acf\Eac_Acf_Options_Page;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Core\Schemes\Color;
use Elementor\Group_Control_Border;
use Elementor\Icons_Manager;
use Elementor\Core\Breakpoints\Manager as Breakpoints_manager;
use Elementor\Plugin;
use Elementor\Utils;

class Acf_Relationship_Widget extends Widget_Base {
	/** Les traits */
	use \EACCustomWidgets\Includes\Widgets\Traits\Slider_Trait;
	use \EACCustomWidgets\Includes\Widgets\Traits\Button_Read_More_Trait;

	/**
	 * $origine_options
	 *
	 * La liste des champs relationnels
	 */
	private $origine_options = array();

	/**
	 * $main_id
	 *
	 * L'ID du template ou de l'article
	 */
	private $main_id;

	/**
	 * Constructeur de la class Acf_Relationship_Widget
	 *
	 * Enregistre les scripts, les styles et initialise l'ID en regard des templates
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		$this->main_id = get_the_ID();
		if ( \Elementor\Plugin::$instance->documents->get_current() !== null ) {
			$this->main_id = \Elementor\Plugin::$instance->documents->get_current()->get_main_id();
		}

		$this->origine_options = Eac_Acf_Lib::get_acf_fields_options( $this->get_acf_supported_fields(), $this->main_id, 'relational' );

		wp_register_script( 'swiper', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.3.2/swiper-bundle.min.js', array( 'jquery' ), '8.3.2', true );
		wp_register_script( 'eac-acf-relation', EAC_Plugin::instance()->get_script_url( 'assets/js/elementor/acf-relationship' ), array( 'jquery', 'elementor-frontend', 'swiper' ), '1.8.2', true );

		wp_register_style( 'swiper-bundle', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.3.2/swiper-bundle.min.css', array(), '8.3.2' );
		wp_enqueue_style( 'eac-swiper', EAC_Plugin::instance()->get_style_url( 'assets/css/eac-swiper' ), array( 'eac' ), '1.8.2' );
		wp_enqueue_style( 'eac-acf-relation', EAC_Plugin::instance()->get_style_url( 'assets/css/acf-relationship' ), array( 'eac-swiper' ), EAC_PLUGIN_VERSION );
	}

	/**
	 * Le nom de la clé du composant dans le fichier de configuration
	 *
	 * @var $slug
	 *
	 * @access private
	 */
	private $slug = 'acf-relationship';

	/**
	 * Retrieve widget name.
	 *
	 * @access public
	 *
	 * @return widget name.
	 */
	public function get_name() {
		return Eac_Config_Elements::get_widget_name( $this->slug );
	}

	/**
	 * Retrieve widget title.
	 *
	 * @access public
	 *
	 * @return widget title.
	 */
	public function get_title() {
		return Eac_Config_Elements::get_widget_title( $this->slug );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @access public
	 *
	 * @return widget icon.
	 */
	public function get_icon() {
		return Eac_Config_Elements::get_widget_icon( $this->slug );
	}

	/**
	 * Affecte le composant à la catégorie définie dans plugin.php
	 *
	 * @access public
	 *
	 * @return widget category.
	 */
	public function get_categories() {
		return Eac_Config_Elements::get_widget_categories( $this->slug );
	}

	/**
	 * Load dependent libraries
	 *
	 * @access public
	 *
	 * @return libraries list.
	 */
	public function get_script_depends() {
		return array( 'swiper', 'eac-acf-relation' );
	}

	/**
	 * Load dependent styles
	 *
	 * Les styles sont chargés dans le footer
	 *
	 * @return CSS list.
	 */
	public function get_style_depends() {
		return array( 'swiper-bundle' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return Eac_Config_Elements::get_widget_keywords( $this->slug );
	}

	/**
	 * Get help widget get_custom_help_url.
	 *
	 * @access public
	 *
	 * @return URL help center
	 */
	public function get_custom_help_url() {
		return Eac_Config_Elements::get_widget_help_url( $this->slug );
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_controls() {

		$active_breakpoints = Plugin::$instance->breakpoints->get_active_breakpoints();

		/**
		 * Generale content Section
		 */
		$this->start_controls_section(
			'acf_relation_settings',
			array(
				'label' => esc_html__( 'Réglages', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'acf_relation_settings_origine',
				array(
					'label'       => esc_html__( 'Champ relationnel', 'eac-components' ),
					'type'        => Controls_Manager::SELECT,
					'groups'      => $this->origine_options,
					'label_block' => true,
				)
			);

			$this->add_control(
				'acf_relation_settings_nombre',
				array(
					'label'       => esc_html__( "Nombre d'articles", 'eac-components' ),
					'description' => esc_html__( '-1 = Tous', 'eac-components' ),
					'type'        => Controls_Manager::NUMBER,
					'default'     => 3,
				)
			);

			/**
			$this->add_control('acf_relation_settings_duplicates',
				[
					'label' => esc_html__("Conserver les doublons", 'eac-components'),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => esc_html__('oui', 'eac-components'),
					'label_off' => esc_html__('non', 'eac-components'),
					'return_value' => 'yes',
					'default' => '',
				]
			);*/

		$this->end_controls_section();

		$this->start_controls_section(
			'acf_relation_layout',
			array(
				'label' => esc_html__( 'Disposition', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'acf_relation_layout_type',
				array(
					'label'   => esc_html__( 'Mode', 'eac-components' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'fitRows',
					'options' => array(
						'fitRows' => esc_html__( 'Grille', 'eac-components' ),
						'slider'  => esc_html( 'Slider' ),
					),
				)
			);

			$columns_device_args = array();
		foreach ( $active_breakpoints as $breakpoint_name => $breakpoint_instance ) {
			if ( Breakpoints_manager::BREAKPOINT_KEY_WIDESCREEN === $breakpoint_name ) {
				$columns_device_args[ $breakpoint_name ] = array( 'default' => '4' );
			} elseif ( Breakpoints_manager::BREAKPOINT_KEY_LAPTOP === $breakpoint_name ) {
				$columns_device_args[ $breakpoint_name ] = array( 'default' => '4' );
			} elseif ( Breakpoints_manager::BREAKPOINT_KEY_TABLET_EXTRA === $breakpoint_name ) {
				$columns_device_args[ $breakpoint_name ] = array( 'default' => '3' );
			} elseif ( Breakpoints_manager::BREAKPOINT_KEY_TABLET === $breakpoint_name ) {
				$columns_device_args[ $breakpoint_name ] = array( 'default' => '3' );
			} elseif ( Breakpoints_manager::BREAKPOINT_KEY_MOBILE_EXTRA === $breakpoint_name ) {
				$columns_device_args[ $breakpoint_name ] = array( 'default' => '2' );
			} elseif ( Breakpoints_manager::BREAKPOINT_KEY_MOBILE === $breakpoint_name ) {
				$columns_device_args[ $breakpoint_name ] = array( 'default' => '1' );
			}
		}

			/**
			 * 'prefix_class' ne fonctionnera qu'avec les flexbox
			 */
			$this->add_responsive_control(
				'acf_relation_layout_columns',
				array(
					'label'          => esc_html__( 'Nombre de colonnes', 'eac-components' ),
					'type'           => Controls_Manager::SELECT,
					'default'        => '3',
					'tablet_default' => '2',
					'mobile_default' => '1',
					// 'device_args'  => $columns_device_args,
					'options'        => array(
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'5' => '5',
						'6' => '6',
					),
					'prefix_class'   => 'responsive%s-',
					'condition'      => array( 'acf_relation_layout_type' => 'fitRows' ),
				)
			);

			$this->add_control(
				'acf_relation_layout_image',
				array(
					'label'     => esc_html__( 'Image', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array(
						'acf_relation_content_image' => 'yes',
						'acf_relation_layout_type'   => 'fitRows',
					),
				)
			);

			$this->add_control(
				'acf_relation_content_image_dimension',
				array(
					'label'     => esc_html__( 'Dimension', 'eac-components' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'medium',
					'options'   => array(
						'thumbnail'    => esc_html__( 'Miniature', 'eac-components' ),
						'medium'       => esc_html__( 'Moyenne', 'eac-components' ),
						'medium_large' => esc_html__( 'Moyenne-large', 'eac-components' ),
						'large'        => esc_html__( 'Large', 'eac-components' ),
						'full'         => esc_html__( 'Originale', 'eac-components' ),
					),
					'condition' => array(
						'acf_relation_content_image' => 'yes',
						'acf_relation_layout_type'   => 'fitRows',
					),
				)
			);

			$this->add_control(
				'acf_relation_image_style_ratio_enable',
				array(
					'label'        => esc_html__( 'Activer le ratio image', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
					'condition'    => array(
						'acf_relation_content_image' => 'yes',
						'acf_relation_layout_type'   => 'fitRows',
					),
				)
			);

			$this->add_responsive_control(
				'acf_relation_image_style_ratio',
				array(
					'label'          => esc_html__( 'Ratio', 'eac-components' ),
					'type'           => Controls_Manager::SELECT,
					'default'        => '1 / 1',
					'tablet_default' => '1 / 1',
					'mobile_default' => '9 / 16',
					'options'        => array(
						'1 / 1'  => esc_html__( 'Défaut', 'eac-components' ),
						'9 / 16' => esc_html( '9-16' ),
						'4 / 3'  => esc_html( '4-3' ),
						'3 / 2'  => esc_html( '3-2' ),
						'16 / 9' => esc_html( '16-9' ),
						'21 / 9' => esc_html( '21-9' ),
					),
					'selectors'      => array( '{{WRAPPER}} .acf-relation_container .acf-relation_img img' => 'aspect-ratio:{{SIZE}};' ),
					'condition'      => array(
						'acf_relation_content_image' => 'yes',
						'acf_relation_image_style_ratio_enable' => 'yes',
						'acf_relation_layout_type'   => 'fitRows',
					),
				)
			);

			$this->add_responsive_control(
				'acf_relation_image_ratio_position_y',
				array(
					'label'      => esc_html__( 'Position verticale', 'eac-components' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( '%' ),
					'default'    => array(
						'size' => 50,
						'unit' => '%',
					),
					'range'      => array(
						'%' => array(
							'min'  => 0,
							'max'  => 100,
							'step' => 5,
						),
					),
					'selectors'  => array( '{{WRAPPER}} .acf-relation_container .acf-relation_img img' => 'object-position: 50% {{SIZE}}%;' ),
					'condition'  => array(
						'acf_relation_content_image'            => 'yes',
						'acf_relation_image_style_ratio_enable' => 'yes',
						'acf_relation_layout_type'              => 'fitRows',
					),
				)
			);

			$this->add_control(
				'acf_relation_content_layout',
				array(
					'label'     => esc_html__( 'Disposition du contenu', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_responsive_control(
				'acf_relation_content_align_v',
				array(
					'label'       => esc_html__( 'Alignement vertical', 'eac-components' ),
					'type'        => Controls_Manager::CHOOSE,
					'options'     => array(
						'flex-start'    => array(
							'title' => esc_html__( 'Haut', 'eac-components' ),
							'icon'  => 'eicon-flex eicon-justify-start-v',
						),
						'center'        => array(
							'title' => esc_html__( 'Centre', 'eac-components' ),
							'icon'  => 'eicon-flex eicon-justify-center-v',
						),
						'flex-end'      => array(
							'title' => esc_html__( 'Bas', 'eac-components' ),
							'icon'  => 'eicon-flex eicon-justify-end-v',
						),
						'space-between' => array(
							'title' => esc_html__( 'Espace entre', 'eac-components' ),
							'icon'  => 'eicon-flex eicon-justify-space-between-v',
						),
						'space-around'  => array(
							'title' => esc_html__( 'Espace autour', 'eac-components' ),
							'icon'  => 'eicon-flex eicon-justify-space-around-v',
						),
						'space-evenly'  => array(
							'title' => esc_html__( 'Espace uniforme', 'eac-components' ),
							'icon'  => 'eicon-flex eicon-justify-space-evenly-v',
						),
					),
					'default'     => 'flex-start',
					'label_block' => true,
					'selectors'   => array( '{{WRAPPER}} .acf-relation_content' => 'justify-content: {{VALUE}};' ),
				)
			);

			$this->add_responsive_control(
				'acf_relation_content_align_h',
				array(
					'label'     => esc_html__( 'Alignement horizontal', 'eac-components' ),
					'type'      => Controls_Manager::CHOOSE,
					'options'   => array(
						'start'  => array(
							'title' => esc_html__( 'Gauche', 'eac-components' ),
							'icon'  => 'eicon-h-align-left',
						),
						'center' => array(
							'title' => esc_html__( 'Centre', 'eac-components' ),
							'icon'  => 'eicon-h-align-center',
						),
						'end'    => array(
							'title' => esc_html__( 'Droit', 'eac-components' ),
							'icon'  => 'eicon-h-align-right',
						),
					),
					'default'   => 'center',
					'toggle'    => false,
					'selectors' => array(
						'{{WRAPPER}} .buttons-wrapper' => 'justify-content: {{VALUE}};',
						'{{WRAPPER}} .acf-relation_title, {{WRAPPER}} .acf-relation_title-parent, {{WRAPPER}} .acf-relation_excerpt' => 'text-align: {{VALUE}};',
					),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'acf_relation_slider_settings',
			array(
				'label'     => 'Slider',
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'acf_relation_layout_type' => 'slider' ),
			)
		);

			$this->register_slider_content_controls();

		$this->end_controls_section();

		$this->start_controls_section(
			'acf_relation_content',
			array(
				'label' => esc_html__( 'Contenu', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			/**
			$this->add_control('acf_relation_content_parent',
				[
					'label' => esc_html__("Le titre de l'article parent", 'eac-components'),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => esc_html__('oui', 'eac-components'),
					'label_off' => esc_html__('non', 'eac-components'),
					'return_value' => 'yes',
					'default' => '',
				]
			);
			*/

			$this->add_control(
				'acf_relation_content_post_heading',
				array(
					'label'     => esc_html__( 'Article', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'acf_relation_content_image',
				array(
					'label'        => esc_html__( 'Image en avant', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'acf_relation_content_title',
				array(
					'label'        => esc_html__( 'Titre', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'acf_relation_title_tag',
				array(
					'label'     => esc_html__( 'Étiquette', 'eac-components' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'h3',
					'options'   => array(
						'h1'  => 'H1',
						'h2'  => 'H2',
						'h3'  => 'H3',
						'h4'  => 'H4',
						'h5'  => 'H5',
						'h6'  => 'H6',
						'div' => 'div',
						'p'   => 'p',
					),
					'condition' => array( 'acf_relation_content_title' => 'yes' ),
				)
			);

			$this->add_control(
				'acf_relation_content_date',
				array(
					'label'        => esc_html__( 'Date', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'acf_relation_content_excerpt',
				array(
					'label'        => esc_html__( 'Résumé', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'acf_relation_excerpt_length',
				array(
					'label'     => esc_html__( 'Nombre de mots', 'eac-components' ),
					'type'      => Controls_Manager::NUMBER,
					'min'       => 10,
					'max'       => 100,
					'step'      => 5,
					'default'   => 25,
					'condition' => array( 'acf_relation_content_excerpt' => 'yes' ),
				)
			);

			$this->add_control(
				'acf_relation_content_button',
				array(
					'label'        => esc_html__( "Bouton 'En savoir plus'", 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$this->add_control(
				'acf_relation_content_links_heading',
				array(
					'label'      => esc_html__( 'Liens', 'eac-components' ),
					'type'       => Controls_Manager::HEADING,
					'separator'  => 'before',
					'conditions' => array(
						'relation' => 'or',
						'terms'    => array(
							array(
								'name'     => 'acf_relation_content_image',
								'operator' => '===',
								'value'    => 'yes',
							),
							array(
								'name'     => 'acf_relation_content_title',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				)
			);

			$this->add_control(
				'acf_relation_content_image_link',
				array(
					'label'        => esc_html__( "Lien de l'article sur l'image", 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => array( 'acf_relation_content_image' => 'yes' ),
				)
			);

			$this->add_control(
				'acf_relation_content_title_link',
				array(
					'label'        => esc_html__( "Lien de l'article sur le titre", 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => array( 'acf_relation_content_title' => 'yes' ),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'acf_relation_more_settings',
			array(
				'label'     => esc_html__( "Bouton 'En savoir plus'", 'eac-components' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'acf_relation_content_button' => 'yes' ),
			)
		);

			// Trait du contenu du bouton read more
			$this->register_button_more_content_controls();

		$this->end_controls_section();

		/** Generale Style Section */
		$this->start_controls_section(
			'acf_relation_general_style',
			array(
				'label' => esc_html__( 'Général', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'acf_relation_wrapper_style',
				array(
					'label'        => esc_html__( 'Style', 'eac-components' ),
					'type'         => Controls_Manager::SELECT,
					'default'      => 'style-1',
					'options'      => array(
						'style-0'  => esc_html__( 'Défaut', 'eac-components' ),
						'style-1'  => 'Style 1',
						'style-2'  => 'Style 2',
						'style-3'  => 'Style 3',
						'style-4'  => 'Style 4',
						'style-5'  => 'Style 5',
						'style-6'  => 'Style 6',
						'style-7'  => 'Style 7',
						'style-8'  => 'Style 8',
						'style-9'  => 'Style 9',
						'style-10' => 'Style 10',
						'style-11' => 'Style 11',
						'style-12' => 'Style 12',
					),
					'prefix_class' => 'acf-relation_wrapper-',
				)
			);

			$this->add_responsive_control(
				'acf_relation_wrapper_style_margin',
				array(
					'label'      => esc_html__( 'Marge entre les items', 'eac-components' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'default'    => array(
						'size' => 7,
						'unit' => 'px',
					),
					'range'      => array(
						'px' => array(
							'min'  => 0,
							'max'  => 20,
							'step' => 1,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .swiper-container' => 'padding: {{SIZE}}{{UNIT}};',
					),
					'condition'  => array( 'acf_relation_layout_type' => 'slider' ),
				)
			);

			$this->add_responsive_control(
				'acf_relation_wrapper_style_gap',
				array(
					'label'      => esc_html__( 'Marge entre les items', 'eac-components' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'default'    => array(
						'size' => 10,
						'unit' => 'px',
					),
					'range'      => array(
						'px' => array(
							'min'  => 0,
							'max'  => 50,
							'step' => 1,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .acf-relation_container:not(.swiper-wrapper)' => 'gap: {{SIZE}}{{UNIT}}; padding: calc({{SIZE}}{{UNIT}} / 2);',
					),
					'condition'  => array( 'acf_relation_layout_type!' => 'slider' ),
				)
			);

			$this->add_control(
				'acf_relation_wrapper_style_bgcolor',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Color::get_type(),
						'value' => Color::COLOR_4,
					),
					'selectors' => array( '{{WRAPPER}} .swiper-container, {{WRAPPER}} .acf-relation_container' => 'background-color: {{VALUE}};' ),
				)
			);

			/** Articles */
			$this->add_control(
				'acf_relation_items_style',
				array(
					'label'     => esc_html__( 'Articles', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'acf_relation_items_bg_color',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Color::get_type(),
						'value' => Color::COLOR_4,
					),
					'selectors' => array( '{{WRAPPER}} .acf-relation_container article, {{WRAPPER}} .acf-relation_content' => 'background-color: {{VALUE}};' ),
				)
			);

			/** Images */
			$this->add_control(
				'acf_relation_images_style',
				array(
					'label'     => esc_html__( 'Images', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'acf_relation_content_image' => 'yes' ),
				)
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'      => 'acf_relation_image_border',
					'selector'  => '{{WRAPPER}} .acf-relation_img img',
					'condition' => array( 'acf_relation_content_image' => 'yes' ),
				)
			);

			$this->add_control(
				'acf_relation_image_border_radius',
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
						'{{WRAPPER}} .acf-relation_img img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'          => array( 'acf_relation_content_image' => 'yes' ),
				)
			);

			/**
			 * Titre
			 */
			$this->add_control(
				'acf_relation_title_style',
				array(
					'label'     => esc_html__( 'Titre', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'acf_relation_content_title' => 'yes' ),
				)
			);

			$this->add_control(
				'acf_relation_title_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Color::get_type(),
						'value' => Color::COLOR_4,
					),
					'selectors' => array( '{{WRAPPER}} .acf-relation_title .acf-relation_title-content' => 'color: {{VALUE}};' ),
					'condition' => array( 'acf_relation_content_title' => 'yes' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'acf_relation_title_typography',
					'label'     => esc_html__( 'Typographie', 'eac-components' ),
					'scheme'    => Typography::TYPOGRAPHY_4,
					'selector'  => '{{WRAPPER}} .acf-relation_title .acf-relation_title-content',
					'condition' => array( 'acf_relation_content_title' => 'yes' ),
				)
			);

			/** Date */
			$this->add_control(
				'acf_relation_date_style',
				array(
					'label'     => esc_html__( 'Date', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'acf_relation_content_date' => 'yes' ),
				)
			);

			$this->add_control(
				'acf_relation_date_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Color::get_type(),
						'value' => Color::COLOR_4,
					),
					'selectors' => array( '{{WRAPPER}} .acf-relation_date' => 'color: {{VALUE}};' ),
					'condition' => array( 'acf_relation_content_date' => 'yes' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'acf_relation_date_typography',
					'label'     => esc_html__( 'Typographie', 'eac-components' ),
					'scheme'    => Typography::TYPOGRAPHY_4,
					'selector'  => '{{WRAPPER}} .acf-relation_date',
					'condition' => array( 'acf_relation_content_date' => 'yes' ),
				)
			);

			/** Résumé */
			$this->add_control(
				'acf_relation_excerpt_style',
				array(
					'label'     => esc_html__( 'Résumé', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'acf_relation_content_excerpt' => 'yes' ),
				)
			);

			$this->add_control(
				'acf_relation_excerpt_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Color::get_type(),
						'value' => Color::COLOR_4,
					),
					'selectors' => array( '{{WRAPPER}} .acf-relation_excerpt' => 'color: {{VALUE}};' ),
					'condition' => array( 'acf_relation_content_excerpt' => 'yes' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'acf_relation_excerpt_typography',
					'label'     => esc_html__( 'Typographie', 'eac-components' ),
					'scheme'    => Typography::TYPOGRAPHY_4,
					'selector'  => '{{WRAPPER}} .acf-relation_excerpt',
					'condition' => array( 'acf_relation_content_excerpt' => 'yes' ),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'acf_relation_slider_section_style',
			array(
				'label'      => esc_html__( 'Contrôles du slider', 'eac-components' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'     => 'acf_relation_layout_type',
									'operator' => '===',
									'value'    => 'slider',
								),
								array(
									'name'     => 'slider_navigation',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
						array(
							'terms' => array(
								array(
									'name'     => 'acf_relation_layout_type',
									'operator' => '===',
									'value'    => 'slider',
								),
								array(
									'name'     => 'slider_pagination',
									'operator' => '===',
									'value'    => 'yes',
								),
							),
						),
					),
				),
			)
		);

			/** Slider styles du trait */
			$this->register_slider_style_controls();

		$this->end_controls_section();

		$this->start_controls_section(
			'acf_relation_readmore_style',
			array(
				'label'     => esc_html__( "Bouton 'En savoir plus'", 'eac-components' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'acf_relation_content_button' => 'yes' ),
			)
		);

			// Trait Style du bouton read more
			$this->register_button_more_style_controls();

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		/**
		 * Le champ de sélection des groupes est vide
		 * Les droits (Location rules) ont changés mais Elementor a enregistré la sélection précédente
		 */
		if ( empty( $settings['acf_relation_settings_origine'] ) ) {
			return;
		}

		$slider_id      = 'slider_acf_relationship_' . $this->get_id();
		$has_swiper     = 'slider' === $settings['acf_relation_layout_type'] ? true : false;
		$has_navigation = $has_swiper && 'yes' === $settings['slider_navigation'] ? true : false;
		$has_pagination = $has_swiper && 'yes' === $settings['slider_pagination'] ? true : false;
		$has_scrollbar  = $has_swiper && 'yes' === $settings['slider_scrollbar'] ? true : false;

		if ( $has_swiper ) { ?>
			<div id="<?php echo esc_attr( $slider_id ); ?>" class='eac-acf-relationship swiper-container'>
		<?php } else { ?>
			<div class='eac-acf-relationship'>
			<?php
		}
				$this->get_relation_by_id();
		if ( $has_navigation ) {
			?>
					<div class='swiper-button-prev'></div>
					<div class='swiper-button-next'></div>
				<?php } ?>
				<?php if ( $has_scrollbar ) { ?>
					<div class='swiper-scrollbar'></div>
				<?php } ?>
				<?php if ( $has_pagination ) { ?>
					<div class='swiper-pagination-bullet'></div>
				<?php } ?>
				<div class='eac-skip-grid' tabindex='0'>
					<span class='visually-hidden'><?php esc_html_e( 'Sortir de la grille', 'eac-components' ); ?></span>
				</div>
			</div>
		<?php
	}

	/**
	 * get_relation_by_id
	 *
	 * @access protected
	 */
	protected function get_relation_by_id() {
		$settings = $this->get_settings_for_display();
		$key      = $settings['acf_relation_settings_origine'];
		$items    = array();
		$id       = $this->main_id;

		// Peux avoir 2 ou 3 champs pour les types de champ GROUP
		list($group_key, $field_key, $meta_key) = array_pad( explode( '::', $key ), 3, '' );

		// Que deux champs
		if ( empty( $meta_key ) ) {
			$meta_key  = $field_key;
			$field_key = $group_key;
			$group_key = false;
		}

		/**
		 * Si $field_key est une page d'options, on récupère l'ID de la page d'options
		 *
		 * Attention: les Page d'options n'ont pas encore de catégorie ('non classé' par défaut)
		 * Si pour un groupe on sélectionne une règle sur les catégories et la catégorie 'non classé'
		 * Les champs du groupe seront ajoutés aux pages d'options
		 */
		if ( class_exists( Eac_Acf_Options_Page::class ) ) {
			$id_page = Eac_Acf_Options_Page::get_options_page_id( $field_key );
			if ( ! empty( $id_page ) ) {
				$id = (int) $id_page;
			}
		}

		/**
		 * Si les droits (Location rules) ont changés
		 * il n'y a peut-être plus de groupe associé à l'article
		 */
		$groups = acf_get_field_groups( array( 'post_id' => $id ) );
		if ( ! empty( $groups ) ) {
			$items = $this->get_relations( $field_key, $meta_key, $id );
			if ( ! empty( $items ) ) {
				$this->render_relationship_content( $items );
			}
		}
	}

	/**
	 * get_relations
	 *
	 * Crée la liste des relationship d'un article
	 *
	 * @access protected
	 */
	protected function get_relations( $field_key, $meta_key, $relationship_id ) {
		/**
		 * @var $items Array d'articles en relation avec l'article courant
		 */
		$items = array();

		/**
		 * @var $items_id Array des articles analysés par leur ID
		 */
		$items_id = array();

		/**
		 * @var $loop Variable pour compter le nombre de boucle
		 */
		$loop = 1;

		/**
		* @var $max_loops Variable pour limiter le nombre de boucle
		*
		* Nombre de boucle max pour éviter une boucle sans fin
		*/
		$max_loops = 1;

		$settings       = $this->get_settings_for_display();
		$has_excerpt    = 'yes' === $settings['acf_relation_content_excerpt'] ? true : false;
		$has_duplicate  = false; // 'yes' === $settings['acf_relation_settings_duplicates'] ? true : false;
		$excerpt_length = absint( $settings['acf_relation_excerpt_length'] );
		$field_value    = '';
		$field          = array();

		/** Sous groupe d'un groupe */
		$parent_group = isset( acf_get_field( $field_key )['parent'] ) ? acf_get_field( $field_key )['parent'] : false;
		$parent_key   = $parent_group && isset( acf_get_field( $parent_group )['key'] ) ? acf_get_field( $parent_group )['key'] : false;
		$meta_key     = Eac_Acf_Lib::get_acf_field_name( $field_key, $meta_key, $relationship_id );

		/** Il y a des sub-fields dans le groupe */
		if ( $parent_key && ! empty( $meta_key ) && have_rows( $parent_key ) ) {
			// error_log($parent_key."::".acf_count_local_fields($parent_key)."::".acf_have_local_fields($parent_key));
			// error_log(acf_is_local_enabled()."===".json_encode(acf_get_local_fields($parent_key)));
			the_row();
			$field = get_field_object( $meta_key, $relationship_id );
			reset_rows();
		} else {
			$field = get_field_object( $field_key, $relationship_id );
		}

		if ( $field && ! empty( $field['value'] ) ) {
			$image_size  = $settings['acf_relation_content_image_dimension'];
			$field_value = $field['value'];

			switch ( $field['type'] ) {
				case 'relationship':
				case 'post_object':
					$values   = array();
					$featured = true;
					$img      = '';
					if ( 'relationship' === $field['type'] ) {
						$featured = is_array( $field['elements'] ) && ! empty( $field['elements'][0] ) && 'featured_image' === $field['elements'][0] ? true : false;
					}

					$field_value = is_array( $field_value ) ? $field_value : array( $field_value );

					// Première boucle on ajoute l'ID du post courant
					if ( 1 === $loop ) {
						$items_id[ $relationship_id ] = $relationship_id;
					}

					/**
					 * Boucle sur tous les relationship/post object posts
					 * $Field_value les ID des articles
					 */
					foreach ( $field_value as $value ) {
						$item  = array();
						$id    = 'object' === $field['return_format'] ? (int) $value->ID : (int) $value;
						$title = 'object' === $field['return_format'] ? $value->post_title : get_post( $id )->post_title;

						// Ne conserve pas les doublons et l'ID de l'article est déjà analysé ou c'est l'article courant
						if ( ! $has_duplicate && in_array( $id, $items_id, true ) ) {
							continue;
						}

						// Enregistre les données
						$item[ $id ]['post_id']           = $id;
						$item[ $id ]['post_parent_id']    = $relationship_id;
						$item[ $id ]['post_parent_title'] = get_post( $relationship_id )->post_title;
						$item[ $id ]['post_type']         = get_post_type( $id );
						$item[ $id ]['post_title']        = $title;
						$item[ $id ]['link']              = get_permalink( get_post( $id )->ID );
						$item[ $id ]['img']               = $featured ? Eac_Helpers_Util::wp_get_attachment_data( get_post_thumbnail_id( $id ), $image_size ) : '';
						$item[ $id ]['post_date']         = get_the_modified_date( get_option( 'date_format' ), $id );
						$item[ $id ]['post_excerpt']      = in_array( get_post_type( $id ), array( 'nav_menu_item', 'attachment' ), true ) || ! $has_excerpt ? '[...]' : Eac_Tools_Util::get_post_excerpt( $id, $excerpt_length );
						$item[ $id ]['class']             = implode( ' ', get_post_class( '', $id ) );
						$item[ $id ]['id']                = 'post-' . $id;
						$item[ $id ]['processed']         = false;

						// ID du relationship post + ID du parent pour conserver les doublons
						if ( $has_duplicate ) {
							$items[ $id . '-' . $relationship_id ] = $item[ $id ];
						} else {
							$items[ $id ] = $item[ $id ];
						}

						// Ajout de l'ID de l'article à la liste des ID déjà analysé
						$items_id[] = $id;

						// Ajout d'une boucle récursive. Plus tard
						$loop++;
					}

					if ( $loop > $max_loops ) {
						return $items;
					}

					// Boucle sur tous les items
					foreach ( $items as $post_key => $post_val ) {
						// L'article n'a pas été analysé
						if ( false === $post_val['processed'] ) {
							$items[ $post_key ]['processed'] = true;

							// Champs ACF relationship (Field-key::Field-name) pour cet article
							$key = Eac_Acf_Lib::get_acf_fields_options( $this->get_acf_supported_fields(), $post_val['post_id'], 'relational' );

							// Récursivité on analyse l'ID pour chercher les articles en relationship
							if ( is_array( $key ) && ! empty( $key ) ) {
								$this->get_relations( array_keys( $key )[0], $post_val['post_id'] );
							}
						}
					}
					break;
			}
		}

		return $items;
	}

	/**
	 * render_relationship_content
	 *
	 * Mis en forme des relationship mode grille
	 *
	 * @access protected
	 */
	protected function render_relationship_content( $items = array() ) {
		$settings         = $this->get_settings_for_display();
		$has_image        = 'yes' === $settings['acf_relation_content_image'] ? true : false;
		$has_ratio        = 'yes' === $settings['acf_relation_image_style_ratio_enable'] ? true : false;
		$has_date         = 'yes' === $settings['acf_relation_content_date'] ? true : false;
		$has_excerpt      = 'yes' === $settings['acf_relation_content_excerpt'] ? true : false;
		$has_link         = 'yes' === $settings['acf_relation_content_image_link'] ? true : false;
		$has_button       = 'yes' === $settings['acf_relation_content_button'] ? true : false;
		$has_button_picto = $has_button && 'yes' === $settings['button_add_more_picto'] ? true : false;
		$has_parent_title = false; /** 'yes' === $settings['acf_relation_content_parent'] ? true : false; */
		$nb_posts         = ! empty( $settings['acf_relation_settings_nombre'] ) ? $settings['acf_relation_settings_nombre'] : -1;
		$nb_displayed     = 0;
		$has_swiper       = 'slider' === $settings['acf_relation_layout_type'] ? true : false;

		/**
		 * Formate le titre avec son tag
		 */
		$title_tag      = ! empty( $settings['acf_relation_title_tag'] ) ? Utils::validate_html_tag( $settings['acf_relation_title_tag'] ) : 'div';
		$has_title      = 'yes' === $settings['acf_relation_content_title'] ? true : false;
		$has_title_link = $has_title && 'yes' === $settings['acf_relation_content_title_link'] ? true : false;

		$id = $this->get_id();

		/**
		 * Le wrapper du container et la class pour le ratio d'image
		 */
		if ( ! $has_swiper ) {
			$class = 'acf-relation_container';
		} else {
			$class = 'acf-relation_container swiper-wrapper';
		}

		$this->add_render_attribute( 'container_wrapper', 'class', esc_attr( $class ) );
		$this->add_render_attribute( 'container_wrapper', 'id', esc_attr( $id ) );
		$this->add_render_attribute( 'container_wrapper', 'data-settings', $this->get_settings_json() );
		?>
		<div <?php $this->print_render_attribute_string( 'container_wrapper' ); ?>>
		<?php
		ob_start();
		foreach ( $items as $item ) {
			if ( -1 !== $nb_posts && $nb_displayed >= $nb_posts ) {
				break;
			}

			if ( $has_swiper ) {
				$item['class'] = $item['class'] . ' swiper-slide';
			}
			$aria_label = esc_html__( "Voir l'article", 'eac-components' ) . ' ' . esc_html( $item['post_title'] );
			?>
			<article id="<?php echo esc_attr( $item['id'] ); ?>" class="<?php echo esc_attr( $item['class'] ); ?>">
			<div class='acf-relation_inner-wrapper'>
			<?php
			/** Affichage de l'image */
			if ( $has_image && $item['img'] && is_array( $item['img'] ) ) {
				$this->add_render_attribute( 'item_image', 'class', 'img-focusable' );
				$this->add_render_attribute( 'item_image', 'src', esc_url( $item['img']['src'] ) );
				$this->add_render_attribute( 'item_image', 'srcset', esc_attr( $item['img']['srcset'] ) );
				$this->add_render_attribute( 'item_image', 'sizes', esc_attr( $item['img']['srcsize'] ) );
				$this->add_render_attribute( 'item_image', 'width', esc_attr( $item['img']['width'] ) );
				$this->add_render_attribute( 'item_image', 'height', esc_attr( $item['img']['height'] ) );
				$this->add_render_attribute( 'item_image', 'alt', esc_attr( $item['img']['alt'] ) );
				$this->add_render_attribute( 'item_image', 'loading', 'lazy' );
				?>
				<div class='acf-relation_img'>
					<?php if ( $has_link ) { ?>
						<a class='eac-accessible-link' href="<?php echo esc_url( $item['link'] ); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>">
					<?php } ?>
						<img <?php $this->print_render_attribute_string( 'item_image' ); ?>>
					<?php if ( $has_link ) { ?>
						</a>
					<?php } ?>
				</div>
				<?php
			}
			?>
			<!-- Affichage du contenu -->
			<div class='acf-relation_content'>

			<!-- Affichage du titre -->
			<div class='acf-relation_title'>
				<?php if ( $has_title_link ) { ?>
					<a class='eac-accessible-link' href="<?php echo esc_url( $item['link'] ); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>">
						<?php echo '<' . esc_attr( $title_tag ) . ' class="acf-relation_title-content">' . esc_html( $item['post_title'] ) . '</' . esc_attr( $title_tag ) . '>'; ?>
					</a>
					<?php
				} elseif ( $has_title ) {
					echo '<' . esc_attr( $title_tag ) . ' class="acf-relation_title-content">' . esc_html( $item['post_title'] ) . '</' . esc_attr( $title_tag ) . '>';
				}
				?>
			</div>

			<!-- Affichage du titre du parent -->
			<?php if ( $has_parent_title ) { ?>
				<div class='acf-relation_title-parent'>
				<?php echo '<' . esc_html( $title_tag ) . ' class="acf-relation_title-content">' . esc_html( $item['post_parent_title'] ) . '</' . esc_html( $title_tag ) . '>'; ?>
				</div>
			<?php } ?>

			<!-- Affichage de la date -->
			<?php if ( $has_date ) { ?>
				<div class='acf-relation_date'><?php echo esc_html( $item['post_date'] ); ?></div>
			<?php } ?>

			<!-- Affichage du résumé -->
			<?php if ( $has_excerpt && ( ! is_null( $item['post_excerpt'] ) && ! empty( $item['post_excerpt'] ) ) ) { ?>
				<div class='acf-relation_excerpt'><?php echo wp_kses_post( $item['post_excerpt'] ); ?></div>
			<?php } ?>

			<?php
			if ( $has_button ) {
				$label = ! empty( $settings['button_more_label'] ) ? sanitize_text_field( $settings['button_more_label'] ) : esc_html__( 'En savoir plus', 'eac-components' );
				?>
				<div class='buttons-wrapper'>
					<span class='button__readmore-wrapper'>
					<a href="<?php echo esc_url( $item['link'] ); ?>" class="button-readmore" role="button" aria-label="<?php echo esc_attr( $label ) . ' ' . esc_html( $item['post_title'] ); ?>">
						<?php
						if ( $has_button_picto && 'before' === $settings['button_more_position'] ) {
							Icons_Manager::render_icon( $settings['button_more_picto'], array( 'aria-hidden' => 'true' ) );
						}
						echo esc_html( $label );
						if ( $has_button_picto && 'after' === $settings['button_more_position'] ) {
							Icons_Manager::render_icon( $settings['button_more_picto'], array( 'aria-hidden' => 'true' ) );
						}
						?>
					</a>
					</span>
				</div>
			<?php } ?>

			</div> <!-- Fin div contenu -->
			</div> <!-- Fin div inner wrapper -->
			</article> <!-- Fin article -->

			<?php
			$nb_displayed++;
			$this->remove_render_attribute( 'item_image' );
		}
		?>
		</div> <!-- Fin div container_wrapper -->
		<?php
		echo wp_kses_post( ob_get_clean() );
	}

	/**
	 * get_acf_supported_fields
	 *
	 * La liste des champs supportés
	 *
	 * @access protected
	 */
	protected function get_acf_supported_fields() {
		return array(
			'relationship',
			'post_object',
		);
	}

	/**
	 * get_settings_json()
	 *
	 * Retrieve fields values to pass at the widget container
	 * Convert on JSON format
	 *
	 * @uses      wp_json_encode()
	 *
	 * @return    JSON oject
	 *
	 * @access    protected
	 */
	protected function get_settings_json() {
		$settings = $this->get_settings_for_display();

		$effect = $settings['slider_effect'];
		if ( in_array( $effect, array( 'fade', 'creative' ), true ) ) {
			$nb_images = 1;
		} elseif ( isset( $settings['slider_images_centered'] ) && 'yes' === $settings['slider_images_centered'] ) {
			$nb_images = 2;
		} elseif ( empty( $settings['slider_images_number'] ) ) {
			$nb_images = 3;
		} elseif ( 0 === absint( $settings['slider_images_number'] ) ) {
			$nb_images = 'auto';
			$effect    = 'slide';
		} else {
			$nb_images = absint( $settings['slider_images_number'] );
		}

		$module_settings = array(
			'data_id'                  => $this->get_id(),
			'data_sw_type'             => true,
			'data_sw_swiper'           => 'slider' === $settings['acf_relation_layout_type'] ? true : false,
			'data_sw_autoplay'         => 'yes' === $settings['slider_autoplay'] ? true : false,
			'data_sw_loop'             => 'yes' === $settings['slider_loop'] ? true : false,
			'data_sw_delay'            => absint( $settings['slider_delay'] ),
			'data_sw_imgs'             => $nb_images,
			'data_sw_centered'         => 'yes' === $settings['slider_images_centered'] ? true : false,
			'data_sw_dir'              => 'horizontal',
			'data_sw_rtl'              => 'right' === $settings['slider_rtl'] ? true : false,
			'data_sw_effect'           => $effect,
			'data_sw_free'             => true,
			'data_sw_pagination_click' => 'yes' === $settings['slider_pagination'] && 'yes' === $settings['slider_pagination_click'] ? true : false,
			'data_sw_scroll'           => 'yes' === $settings['slider_scrollbar'] ? true : false,
		);

		return wp_json_encode( $module_settings );
	}

	protected function content_template() {}

}
