<?php
/**
 * Class: Mega_Menu_Widget
 * Name: Simple menu
 * Slug: eac-addon-mega-menu
 *
 * Description: Création d'un menu de navigation basé sur les menus existants
 *
 * @since 2.1.0
 */

namespace EACCustomWidgets\Includes\TemplatesLib\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\EAC_Plugin;
use EACCustomWidgets\Includes\TemplatesLib\Widgets\Classes\EAC_Menu_Walker;
use EACCustomWidgets\Core\Utils\Eac_Tools_Util;
use EACCustomWidgets\Core\Eac_Config_Elements;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;
use Elementor\Core\Breakpoints\Manager as Breakpoints_manager;
use Elementor\Plugin;

class Mega_Menu_Widget extends Widget_Base {

	/**
	 * @var $selected_args_menu
	 */
	private $selected_args_menu = '';

	/**
	 * La liste des breakpoints et de leurs valeurs
	 *
	 * @var $responsive_breakpoints
	 *
	 * @access private
	 */
	private $responsive_breakpoints = array();

	/**
	 * La liste des breakpoints par défaut
	 *
	 * @var $responsive_default
	 *
	 * @access private
	 */
	private $responsive_default = array(
		'(max-width:240px)'  => 'Never',
		'(max-width:4600px)' => 'Always',
		'(max-width:1200px)' => 'Max 1200px',
		'(max-width:1024px)' => 'Max 1024px',
		'(max-width:881px)'  => 'Max 881px',
		'(max-width:768px)'  => 'Max 768px',
		'(max-width:640px)'  => 'Max 640px',
	);

	/**
	 * L'ID réel du menu
	 *
	 * @var $main_id
	 *
	 * @access private
	 */
	private $main_id;

	/**
	 * Constructeur de la class
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		$this->includes();
	}

	/**
	 * Le nom de la clé du composant dans le fichier de configuration
	 *
	 * @var $slug
	 *
	 * @access private
	 */
	private $slug = 'mega-menu';

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
		return array( 'eac-mega-menu' );
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

		/**
		 * if ( Plugin::$instance->breakpoints->has_custom_breakpoints() ) {
		 * Pas besoin de test elementor retourne les anciens breakpoints si le feature n'est pas actif
		 */
		$active_breakpoints = Plugin::$instance->breakpoints->get_active_breakpoints();
		foreach ( $active_breakpoints as $key => $value ) {
			$responsive_value = Plugin::$instance->breakpoints->get_breakpoints( $key )->get_value();
			$this->responsive_breakpoints[ '(max-width:' . absint( $responsive_value ) . 'px)' ] = 'Max ' . absint( $responsive_value ) . 'px';
		}
		$this->responsive_breakpoints['(max-width:240px)']  = esc_html__( 'Jamais', 'eac-components' );
		$this->responsive_breakpoints['(max-width:4600px)'] = esc_html__( 'Toujours', 'eac-components' );

		$this->start_controls_section(
			'mn_settings',
			array(
				'label' => esc_html__( 'Réglages', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'mn_content_menu',
				array(
					'label'       => esc_html__( 'Sélectionner un menu', 'eac-components' ),
					'type'        => Controls_Manager::SELECT,
					'options'     => Eac_Tools_Util::get_menus_list(),
					'default'     => array_key_first( Eac_Tools_Util::get_menus_list() ),
					'label_block' => true,
				)
			);

			$this->add_control(
				'mn_content_display',
				array(
					'label'        => esc_html__( 'Affichage', 'eac-components' ),
					'type'         => Controls_Manager::SELECT,
					'options'      => array(
						'default' => esc_html__( 'Défaut', 'eac-components' ),
						'mega'    => esc_html__( 'Multiples colonnes', 'eac-components' ),
					),
					'default'      => 'default',
					'render_type'  => 'template',
					'prefix_class' => 'mega-menu_display-',
				)
			);

			$this->add_responsive_control(
				'mn_mega_number',
				array(
					'label'     => esc_html__( 'Nombre de lignes', 'eac-components' ),
					'type'      => Controls_Manager::NUMBER,
					'min'       => 2,
					'max'       => 30,
					'step'      => 1,
					'default'   => 5,
					'selectors' => array(
						'{{WRAPPER}} .mega-menu_sub-menu:not(.mini-cart-product)' => 'grid-template-rows: repeat({{VALUE}}, auto);',
					),
					'condition' => array( 'mn_content_display' => 'mega' ),
				)
			);

			$this->add_control(
				'mn_content_orientation',
				array(
					'label'        => esc_html__( 'Orientation', 'eac-components' ),
					'type'         => Controls_Manager::SELECT,
					'options'      => array(
						'hrz' => esc_html__( 'Horizontale', 'eac-components' ),
						'vrt' => esc_html__( 'Verticale', 'eac-components' ),
					),
					'default'      => 'hrz',
					'render_type'  => 'template',
					'prefix_class' => 'mega-menu_orientation-',
				)
			);

			$this->add_responsive_control(
				'mn_content_align',
				array(
					'label'     => esc_html__( 'Alignement du menu', 'eac-components' ),
					'type'      => Controls_Manager::CHOOSE,
					'options'   => array(
						'flex-start'    => array(
							'title' => esc_html__( 'Gauche', 'eac-components' ),
							'icon'  => 'eicon-h-align-left',
						),
						'center'        => array(
							'title' => esc_html__( 'Centre', 'eac-components' ),
							'icon'  => 'eicon-h-align-center',
						),
						'flex-end'      => array(
							'title' => esc_html__( 'Droit', 'eac-components' ),
							'icon'  => 'eicon-h-align-right',
						),
						'space-between' => array(
							'title' => esc_html__( 'Justifié', 'eac-components' ),
							'icon'  => 'eicon-h-align-stretch',
						),
					),
					'default'   => 'flex-start',
					'selectors' => array(
						'{{WRAPPER}} .mega-menu_nav-wrapper .mega-menu_nav-menu,
						{{WRAPPER}}.mega-menu_orientation-vrt .mega-menu_nav-wrapper .mega-menu_nav-menu,
						{{WRAPPER}} .mega-menu_nav-toggle' => 'justify-content: {{VALUE}}',
					),
					'condition' => array( 'mn_content_orientation' => 'hrz' ),
				)
			);

			$this->add_control(
				'mn_content_expand',
				array(
					'label'        => esc_html__( 'Menu réduit', 'eac-components' ),
					'type'         => Controls_Manager::CHOOSE,
					'options'      => array(
						'yes' => array(
							'title' => esc_html__( 'Oui', 'eac-components' ),
							'icon'  => 'fas fa-check',
						),
						'no'  => array(
							'title' => esc_html__( 'Non', 'eac-components' ),
							'icon'  => 'fas fa-ban',
						),
					),
					'default'      => 'yes',
					'render_type'  => 'template',
					'prefix_class' => 'mega-menu_collapse-',
					'toggle'       => false,
				)
			);

		$this->add_control(
			'mn_content_add_menu_sticky',
			array(
				'label'     => esc_html__( 'Menu collant', 'eac-components' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'yes' => array(
						'title' => esc_html__( 'Oui', 'eac-components' ),
						'icon'  => 'fas fa-check',
					),
					'no'  => array(
						'title' => esc_html__( 'Non', 'eac-components' ),
						'icon'  => 'fas fa-ban',
					),
				),
				'default'   => 'no',
				'toggle'    => false,
				'separator' => 'before',
				'condition' => array( 'mn_content_orientation' => 'hrz' ),
			)
		);

		$this->add_control(
			'mn_content_item_revert',
			array(
				'label'        => esc_html__( "Inverser l'affichage du dernier élément", 'eac-components' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'yes' => array(
						'title' => esc_html__( 'Oui', 'eac-components' ),
						'icon'  => 'fas fa-check',
					),
					'no'  => array(
						'title' => esc_html__( 'Non', 'eac-components' ),
						'icon'  => 'fas fa-ban',
					),
				),
				'default'      => 'no',
				'toggle'       => false,
				'render_type'  => 'template',
				'prefix_class' => 'mega-menu_reverted-',
				'condition'    => array( 'mn_content_orientation' => 'hrz' ),
			)
		);

		$this->add_control(
			'mn_content_menu_overflow',
			array(
				'label'   => esc_html__( 'Masquer le débordement', 'eac-components' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'yes' => array(
						'title' => esc_html__( 'Oui', 'eac-components' ),
						'icon'  => 'fas fa-check',
					),
					'no'  => array(
						'title' => esc_html__( 'Non', 'eac-components' ),
						'icon'  => 'fas fa-ban',
					),
				),
				'default' => 'no',
				'toggle'  => false,
			)
		);

		if ( class_exists( 'woocommerce' ) ) {
			$this->add_control(
				'mn_content_add_menu_cart',
				array(
					'label'        => esc_html__( 'Mini-panier', 'eac-components' ),
					'description'  => esc_html__( 'Ajouter un mini-panier au menu. Actif sur le frontend.', 'eac-components' ),
					'type'         => Controls_Manager::CHOOSE,
					'options'      => array(
						'yes' => array(
							'title' => esc_html__( 'Oui', 'eac-components' ),
							'icon'  => 'fas fa-check',
						),
						'no'  => array(
							'title' => esc_html__( 'Non', 'eac-components' ),
							'icon'  => 'fas fa-ban',
						),
					),
					'default'      => 'no',
					'toggle'       => false,
					'render_type'  => 'template',
					'prefix_class' => 'mega-menu_cart-',
				)
			);
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'mn_settings_responsive',
			array(
				'label' => esc_html__( 'Mode responsive', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'mn_settings_responsive_breakpoint',
				array(
					'label'   => esc_html__( 'Point de rupture', 'eac-components' ),
					'type'    => Controls_Manager::SELECT,
					'default' => ! empty( $this->responsive_breakpoints ) ? array_key_first( $this->responsive_breakpoints ) : '(max-width:768px)',
					'options' => ! empty( $this->responsive_breakpoints ) ? $this->responsive_breakpoints : $this->responsive_default,
				)
			);

			$this->add_responsive_control(
				'mn_content_wrapper_icon_align',
				array(
					'label'          => esc_html__( 'Alignement des icônes', 'eac-components' ),
					'type'           => Controls_Manager::CHOOSE,
					'default'        => 'center',
					'tablet_default' => 'center',
					'mobile_default' => 'center',
					'options'        => array(
						'flex-start' => array(
							'title' => esc_html__( 'Gauche', 'eac-components' ),
							'icon'  => 'eicon-h-align-left',
						),
						'center'     => array(
							'title' => esc_html__( 'Centre', 'eac-components' ),
							'icon'  => 'eicon-h-align-center',
						),
						'flex-end'   => array(
							'title' => esc_html__( 'Droite', 'eac-components' ),
							'icon'  => 'eicon-h-align-right',
						),
					),
					'toggle'         => false,
					'selectors'      => array(
						'{{WRAPPER}} .mega-menu_flyout-open .mega-menu_menu-icon,
						{{WRAPPER}} .mega-menu_flyout-close .mega-menu_menu-icon' => 'justify-content: {{VALUE}};',
					),
					'condition'      => array( 'mn_settings_responsive_breakpoint!' => '(max-width:240px)' ),
				)
			);

			$this->add_control(
				'mn_content_wrapper_dropdown_icon',
				array(
					'label'                  => esc_html__( "Icône d'ouverture", 'eac-components' ),
					'type'                   => Controls_Manager::ICONS,
					'label_block'            => 'true',
					'default'                => array(
						'value'   => 'fas fa-bars',
						'library' => 'fa-solid',
					),
					'skin'                   => 'inline',
					'exclude_inline_options' => array( 'svg' ),
				)
			);

			$this->add_control(
				'mn_content_wrapper_dropdown_close_icon',
				array(
					'label'                  => esc_html__( 'Icône de fermeture', 'eac-components' ),
					'type'                   => Controls_Manager::ICONS,
					'label_block'            => 'true',
					'default'                => array(
						'value'   => 'fas fa-times',
						'library' => 'fa-solid',
					),
					'skin'                   => 'inline',
					'exclude_inline_options' => array( 'svg' ),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'mn_content_menu_sticky',
			array(
				'label'     => esc_html__( 'Menu collant', 'eac-components' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'mn_content_add_menu_sticky' => 'yes' ),
			)
		);

			$this->add_control(
				'mn_content_menu_sticky_fontsize',
				array(
					'label'          => esc_html__( 'Taille de la fonte (%)', 'eac-components' ),
					'type'           => Controls_Manager::SLIDER,
					'size_units'     => array( '%' ),
					'default'        => array(
						'unit' => '%',
						'size' => 100,
					),
					'tablet_default' => array(
						'unit' => '%',
					),
					'mobile_default' => array(
						'unit' => '%',
					),
					'range'          => array(
						'%' => array(
							'min'  => 50,
							'max'  => 100,
							'step' => 10,
						),
					),
					'selectors'      => array(
						'{{WRAPPER}} .eac-mega-menu.menu-fixed .inside-navigation.inside-container' => 'font-size: {{SIZE}}%;',
						'{{WRAPPER}} .eac-mega-menu.menu-fixed .mega-menu_nav-wrapper .mega-menu_sub-item' => 'line-height: calc(calc({{SIZE}} * var(--eac-hrz-sub-item-line-height)) / 100)',
					),
				)
			);

			$this->add_control(
				'mn_content_menu_sticky_height',
				array(
					'label'      => esc_html__( 'Hauteur (px)', 'eac-components' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'default'    => array(
						'unit' => 'px',
						'size' => 60,
					),
					'range'      => array(
						'px' => array(
							'min'  => 30,
							'max'  => 60,
							'step' => 5,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .eac-mega-menu.menu-fixed .mega-menu_nav-wrapper .mega-menu_top-item' => 'line-height: {{SIZE}}px;',
					),
				)
			);

			$this->add_control(
				'mn_content_menu_sticky_opacity',
				array(
					'label'     => __( 'Opacité', 'eac-components' ),
					'type'      => Controls_Manager::SLIDER,
					'default'   => array( 'size' => 1 ),
					'range'     => array(
						'px' => array(
							'max'  => 1,
							'min'  => 0.1,
							'step' => 0.1,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .eac-mega-menu.menu-fixed .mega-menu_nav-wrapper' => 'opacity: {{SIZE}};',
					),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'mn_content_mini_cart',
			array(
				'label'     => esc_html__( 'Mini-panier', 'eac-components' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'mn_content_add_menu_cart' => 'yes' ),
			)
		);

			$this->add_control(
				'mn_content_mini_cart_badge',
				array(
					'label'       => esc_html__( 'Badge', 'eac-components' ),
					'description' => esc_html__( "Ajouter un badge à l'icône du panier", 'eac-components' ),
					'type'        => Controls_Manager::CHOOSE,
					'options'     => array(
						'yes' => array(
							'title' => esc_html__( 'Oui', 'eac-components' ),
							'icon'  => 'fas fa-check',
						),
						'no'  => array(
							'title' => esc_html__( 'Non', 'eac-components' ),
							'icon'  => 'fas fa-ban',
						),
					),
					'default'     => 'yes',
					'toggle'      => false,
				)
			);

			$this->add_control(
				'mn_content_mini_cart_icon',
				array(
					'label'                  => esc_html__( 'Icône', 'eac-components' ),
					'type'                   => Controls_Manager::ICONS,
					'label_block'            => 'true',
					'default'                => array(
						'value'   => 'fas fa-shopping-cart',
						'library' => 'fa-solid',
					),
					'skin'                   => 'inline',
					'exclude_inline_options' => array( 'svg' ),
				)
			);

			$this->add_responsive_control(
				'mn_content_mini_cart_buttons_with',
				array(
					'label'          => esc_html__( 'Largeur des boutons', 'eac-components' ),
					'type'           => Controls_Manager::SLIDER,
					'size_units'     => array( '%' ),
					'default'        => array(
						'unit' => '%',
						'size' => 50,
					),
					'tablet_default' => array(
						'unit' => '%',
					),
					'mobile_default' => array(
						'unit' => '%',
					),
					'range'          => array(
						'%' => array(
							'min'  => 10,
							'max'  => 100,
							'step' => 10,
						),
					),
					'selectors'      => array(
						'{{WRAPPER}} .woocommerce-mini-cart__buttons .button' => 'width: {{SIZE}}{{UNIT}};',
					),
				)
			);

		$this->end_controls_section();

		/**
		 * Generale Style Section
		 */
		$this->start_controls_section(
			'mn_style_menu',
			array(
				'label' => esc_html__( 'Menu', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'mn_menu_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_TEXT ),
					'default'   => '#FFFFFF',
					'selectors' => array(
						'{{WRAPPER}} .mega-menu_top-item .mega-menu_top-link .mega-menu_item-title,
						{{WRAPPER}} .mega-menu_top-item .mega-menu_top-link .nav-menu_item-icon i,
						{{WRAPPER}} .mega-menu_top-item .mega-menu_top-link .nav-menu_item-title,
						{{WRAPPER}} .mega-menu_top-item .mega-menu_top-link .icon-menu-toggle svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'mn_menu_color_hover',
				array(
					'label'     => esc_html__( 'Couleur au survol', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_TEXT ),
					'default'   => '#FFFFFF',
					'selectors' => array(
						'{{WRAPPER}} .mega-menu_top-item:hover .mega-menu_top-link .mega-menu_item-title,
						{{WRAPPER}} .mega-menu_top-item:hover .mega-menu_top-link .nav-menu_item-icon i,
						{{WRAPPER}} .mega-menu_top-item:hover .mega-menu_top-link .nav-menu_item-title,
						{{WRAPPER}} .mega-menu_top-item:hover .mega-menu_top-link .icon-menu-toggle svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'           => 'mn_menu_typography',
					'label'          => esc_html__( 'Typographie', 'eac-components' ),
					'global'         => array( 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ),
					'fields_options' => array(
						'font_size' => array(
							'default' => array(
								'unit' => 'em',
								'size' => 1,
							),
						),
					),
					'selector'       => '{{WRAPPER}} .mega-menu_nav-menu > li > a > .mega-menu_item-title,
										{{WRAPPER}} .mega-menu_nav-menu > li > span > .mega-menu_item-title,
										{{WRAPPER}} .mega-menu_top-link .icon-menu-toggle svg',
				)
			);

			$this->add_control(
				'mn_menu_bgcolor',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'default'   => '#54595F',
					'selectors' => array(
						'{{WRAPPER}} .mega-menu_nav-wrapper,
						{{WRAPPER}} .eac-mega-menu .mega-menu_nav-toggle,
						{{WRAPPER}} .mega-menu_nav-wrapper.breakpoint .inside-navigation.inside-container' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'mn_menu_bgcolor_hover',
				array(
					'label'     => esc_html__( 'Couleur du fond au survol', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'default'   => '#7A7A7A',
					'selectors' => array(
						'{{WRAPPER}} .mega-menu_top-item:hover,
						{{WRAPPER}} .mega-menu_sub-menu:not(.mini-cart-product) .mega-menu_sub-item:hover,
						{{WRAPPER}} .woocommerce-mini-cart .mini_cart_item:hover,
						{{WRAPPER}} .mega-menu_nav-wrapper .inside-container ul ul li[class*="current-menu-"]>a,
						{{WRAPPER}} .mega-menu_nav-wrapper .inside-container ul li[class*="current-menu-"]' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->add_responsive_control(
				'mn_style_wrapper_width',
				array(
					'label'      => esc_html__( 'Largeur', 'eac-components' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%' ),
					'default'    => array(
						'unit' => 'px',
						'size' => 200,
					),
					'range'      => array(
						'px' => array(
							'min'  => 30,
							'max'  => 1024,
							'step' => 10,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .mega-menu_nav-wrapper,
						{{WRAPPER}} .mega-menu_nav-wrapper .mega-menu_top-item' => 'width: {{SIZE}}{{UNIT}};',
					),
					'condition'  => array( 'mn_content_orientation' => 'vrt' ),
				)
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'      => 'mn_menu_border',
					'selector'  => '{{WRAPPER}} .mega-menu_nav-wrapper',
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => 'mn_menu_shadow',
					'label'    => esc_html__( 'Ombre', 'eac-components' ),
					'selector' => '{{WRAPPER}} .mega-menu_nav-wrapper',
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'mn_style_submenu',
			array(
				'label' => esc_html__( 'Sous-menu', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'mn_submenu_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_TEXT ),
					'default'   => '#FFFFFF',
					'selectors' => array(
						'{{WRAPPER}} .mega-menu_nav-menu > li > ul li a .mega-menu_item-title,
						{{WRAPPER}} .mega-menu_nav-menu > li > ul li span .mega-menu_item-title,
						{{WRAPPER}} .mega-menu_sub-link i,
						{{WRAPPER}} .mega-menu_nav-menu > li > ul li a:not(.remove_from_cart_button),
						{{WRAPPER}} .woocommerce-mini-cart .mini_cart_item .quantity,
						{{WRAPPER}} .woocommerce-mini-cart .mini_cart_item .product-title,
						{{WRAPPER}} .woocommerce-mini-cart .mini_cart_item .quantity .amount,
						{{WRAPPER}} .woocommerce-mini-cart__total.total,
						{{WRAPPER}} .woocommerce-mini-cart__total.total strong,
						{{WRAPPER}} .woocommerce-mini-cart__total.total .amount,
						{{WRAPPER}} .woocommerce-mini-cart__empty-message,
						{{WRAPPER}} .mega-menu_sub-link .icon-menu-toggle svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'mn_submenu_color_hover',
				array(
					'label'     => esc_html__( 'Couleur au survol', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_TEXT ),
					'default'   => '#FFFFFF',
					'selectors' => array(
						'{{WRAPPER}} .mega-menu_sub-item:hover .mega-menu_item-title,
						{{WRAPPER}} .mega-menu_sub-item:hover .nav-menu_item-icon i,
						{{WRAPPER}} .mega-menu_sub-item:hover .nav-menu_item-title,
						{{WRAPPER}} .mega-menu_sub-item:hover .icon-menu-toggle svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'           => 'mn_submenu_typography',
					'label'          => esc_html__( 'Typographie', 'eac-components' ),
					'global'         => array( 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ),
					'fields_options' => array(
						'font_size' => array(
							'default' => array(
								'unit' => 'em',
								'size' => 1,
							),
						),
					),
					'selector'       => '{{WRAPPER}} .mega-menu_nav-menu > li > ul li a .mega-menu_item-title,
						{{WRAPPER}} .mega-menu_nav-menu > li > ul li span .mega-menu_item-title,
						{{WRAPPER}} .woocommerce-mini-cart .mini_cart_item a:not(.remove_from_cart_button),
						{{WRAPPER}} .woocommerce-mini-cart .mini_cart_item .quantity,
						{{WRAPPER}} .woocommerce-mini-cart .mini_cart_item .product-title,
						{{WRAPPER}} .woocommerce-mini-cart .mini_cart_item .quantity .amount,
						{{WRAPPER}} .woocommerce-mini-cart__total.total,
						{{WRAPPER}} .woocommerce-mini-cart__total.total strong,
						{{WRAPPER}} .woocommerce-mini-cart__total.total .amount,
						{{WRAPPER}} .woocommerce-mini-cart__empty-message,
						{{WRAPPER}} .mega-menu_sub-link .icon-menu-toggle svg',
				)
			);

			$this->add_control(
				'mn_submenu_bgcolor',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'default'   => '#54595F',
					'selectors' => array(
						'{{WRAPPER}} .mega-menu_sub-menu,
						{{WRAPPER}} .woocommerce-mini-cart .quantity,
						{{WRAPPER}} .woocommerce-mini-cart__total.total,
						{{WRAPPER}} .mega-menu_nav-wrapper.breakpoint .inside-navigation.inside-container' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->add_responsive_control(
				'mn_submenu_width',
				array(
					'label'      => esc_html__( 'Largeur premier niveau', 'eac-components' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'default'    => array(
						'unit' => 'px',
						'size' => 200,
					),
					'range'      => array(
						'px' => array(
							'min'  => 100,
							'max'  => 500,
							'step' => 10,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .mega-menu_sub-menu' => 'width: {{SIZE}}{{UNIT}};',
					),
					'condition'  => array( 'mn_content_display' => 'default' ),
				)
			);

			$this->add_responsive_control(
				'mn_items_width',
				array(
					'label'      => esc_html__( 'Largeur deuxième niveau', 'eac-components' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'default'    => array(
						'unit' => 'px',
						'size' => 200,
					),
					'range'      => array(
						'px' => array(
							'min'  => 100,
							'max'  => 500,
							'step' => 10,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} .mega-menu_sub-menu .mega-menu_sub-menu' => 'width: {{SIZE}}{{UNIT}};',
					),
					'condition'  => array( 'mn_content_display' => 'default' ),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'mn_style_buttons',
			array(
				'label' => esc_html__( 'Mode responsive', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'mn_buttons_color',
				array(
					'label'     => esc_html__( 'Couleur des boutons', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'default'   => '#FFFFFF',
					'selectors' => array(
						'{{WRAPPER}} .mega-menu_nav-toggle .mega-menu_menu-icon i,
						{{WRAPPER}} .mega-menu_nav-toggle .toggle-menu' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'mn_buttons_typography',
					'label'    => esc_html__( 'Typographie des boutons', 'eac-components' ),
					'global'   => array( 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ),
					'selector' => '{{WRAPPER}} .mega-menu_nav-toggle .mega-menu_menu-icon i, {{WRAPPER}} .mega-menu_nav-toggle .toggle-menu',
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'mn_style_cart_buttons',
			array(
				'label'     => esc_html__( 'Boutons du mini-panier', 'eac-components' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'mn_content_add_menu_cart' => 'yes' ),
			)
		);

			$this->add_control(
				'mn_style_cart_buttons_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_TEXT ),
					'default'   => '#000',
					'selectors' => array(
						'{{WRAPPER}} #menu-item-mini-cart .woocommerce-mini-cart__buttons .button' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'mn_style_cart_buttons_typography',
					'label'    => esc_html__( 'Typographie', 'eac-components' ),
					'global'   => array( 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ),
					'selector' => '{{WRAPPER}} #menu-item-mini-cart .woocommerce-mini-cart__buttons .button',
				)
			);

			$this->add_control(
				'mn_style_cart_buttons_bgcolor',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_SECONDARY ),
					'default'   => '#FFF',
					'selectors' => array(
						'{{WRAPPER}} #menu-item-mini-cart .woocommerce-mini-cart__buttons .button' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => 'mn_style_cart_buttons_border',
					'selector' => '{{WRAPPER}} #menu-item-mini-cart .woocommerce-mini-cart__buttons .button',
				)
			);

			$this->add_control(
				'mn_style_cart_buttons_radius',
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
						'{{WRAPPER}} #menu-item-mini-cart .woocommerce-mini-cart__buttons .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'mn_style_mega_menu',
			array(
				'label'     => esc_html__( 'Colonnes', 'eac-components' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'mn_content_display' => 'mega' ),
			)
		);

		$this->add_responsive_control(
			'mn_style_mega_gap',
			array(
				'label'     => esc_html__( 'Marges entre les colonnes', 'eac-components' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 50,
				'step'      => 1,
				'default'   => 0,
				'selectors' => array(
					'{{WRAPPER}} .mega-menu_sub-menu:not(.mini-cart-product)' => 'column-gap: {{VALUE}}px;',
				),
			)
		);

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
		$settings   = $this->get_settings_for_display();
		$last_class = '';

		if ( empty( $settings['mn_content_menu'] ) ) {
			return;
		}

		if ( isset( $settings['mn_content_item_revert'] ) && 'yes' === $settings['mn_content_item_revert'] ) {
			$last_class = ' item_reverted';
		}

		if ( isset( $settings['mn_content_menu_overflow'] ) && 'yes' === $settings['mn_content_menu_overflow'] ) {
			$last_class = $last_class . ' item_clipped';
		}
		?>
		<div class="eac-mega-menu<?php echo esc_attr( $last_class ); ?>">
		<?php $this->render_megamenu(); ?>
		</div>
		<?php
	}

	protected function render_megamenu() {
		$settings = $this->get_settings_for_display();

		$this->main_id = get_the_ID();
		if ( \Elementor\Plugin::$instance->documents->get_current() !== null ) {
			$this->main_id = \Elementor\Plugin::$instance->documents->get_current()->get_main_id();
		}
		$allowed_content = array(
			'i' => array(
				'class'       => array(),
				'aria-hidden' => array(),
			),
		);

		$current_menu_name = $settings['mn_content_menu'];

		$current_menu_obj = wp_get_nav_menu_object( $current_menu_name );
		if ( ! $current_menu_obj ) {
			return;
		}
		$current_menu_label    = $current_menu_obj->name;
		$current_menu_id       = absint( $current_menu_obj->term_id );
		$current_location_name = array_search( $current_menu_id, get_nav_menu_locations(), true );

		$container_class = 'mega-menu_nav-wrapper';
		$menu_class      = 'mega-menu_nav-menu'; // sf-menu ??
		$menu_icons      = array();
		$menu_icons      = $this->get_menu_icons( $settings );
		$orientation     = 'hrz' === $settings['mn_content_orientation'] ? 'horizontal' : 'vertical';

		$args = array(
			'theme_location'  => $current_location_name,
			'container_class' => "menu-{$current_menu_obj->slug}-container inside-navigation inside-container",
			'menu'            => $current_menu_id,
			'menu_class'      => $menu_class,
			'menu_id'         => $current_menu_obj->slug . '-' . $this->main_id,
			'items_wrap'      => '<ul id="%1$s" class="%2$s" role="menubar" aria-label="' . $current_menu_label . '" aria-orientation="' . $orientation . '">%3$s</ul>',
			'echo'            => false,
			'fallback_cb'     => '__return_empty_string',
			'walker'          => new EAC_Menu_Walker( $settings['mn_content_orientation'], false ),
		);

		/** L'ID du menu */
		$this->selected_args_menu = $current_menu_id;

		$sticky       = Plugin::$instance->editor->is_edit_mode() ? 'no' : $settings['mn_content_add_menu_sticky'];
		$mega_menu_id = 'mega-menu-' . $this->main_id;
		$toggle_open  = 'toggle-open-' . $this->main_id;
		$toggle_close = 'toggle-close-' . $this->main_id;

		/** Les hooks WooCommerce */
		$woo_shop_args = get_option( Eac_Config_Elements::get_woo_hooks_option_name() );

		$this->add_render_attribute( 'nav-menu', 'id', $mega_menu_id );
		$this->add_render_attribute( 'nav-menu', 'class', $container_class );
		$this->add_render_attribute( 'nav-menu', 'aria-label', $current_menu_label );
		$this->add_render_attribute( 'nav-menu', 'itemtype', 'https://schema.org/SiteNavigationElement' );
		$this->add_render_attribute( 'nav-menu', 'itemscope', 'itemscope' );
		$this->add_render_attribute( 'nav-menu', 'data-breakpoint', $settings['mn_settings_responsive_breakpoint'] );
		$this->add_render_attribute( 'nav-menu', 'data-enable-fixed', $sticky );
		$this->add_render_attribute( 'nav-menu', 'data-mini-cart', $woo_shop_args && class_exists( 'WooCommerce' ) && 'yes' === $settings['mn_content_add_menu_cart'] ? true : false );
		$this->add_render_attribute( 'nav-menu', 'data-menu-cache', false );
		$this->add_render_attribute( 'nav-menu', 'data-item-reverted', isset( $settings['mn_content_item_revert'] ) && 'yes' === $settings['mn_content_item_revert'] ? true : false );

		/** Ajout du filtre avant de créer le menu */
		if ( $woo_shop_args && class_exists( 'WooCommerce' ) && 'yes' === $settings['mn_content_add_menu_cart'] ) {
			$woo_shop_args['mini_cart'] = boolval( 1 );
			add_filter( "wp_nav_menu_{$current_menu_obj->slug}_items", array( $this, 'add_custom_cart' ), 10, 2 );
		} elseif ( $woo_shop_args ) {
			$woo_shop_args['mini_cart'] = boolval( 0 );
		}

		/** Update de l'option WooCommerce hooks dans la BDD */
		update_option( Eac_Config_Elements::get_woo_hooks_option_name(), $woo_shop_args );
		?>
		<style>
			@media <?php echo esc_attr( $settings['mn_settings_responsive_breakpoint'] ); ?> {
			#<?php echo esc_attr( $mega_menu_id ); ?>.mega-menu_nav-wrapper { display: none; }
			#<?php echo esc_attr( $toggle_open ); ?>.mega-menu_nav-toggle.elementor-clickable.mega-menu_flyout-open { display: block; }
		}
		</style>
		<?php
		$this->add_render_attribute( 'button-open', 'id', esc_attr( $toggle_open ) );
		$this->add_render_attribute( 'button-open', 'type', 'button' );
		$this->add_render_attribute( 'button-open', 'class', 'mega-menu_nav-toggle elementor-clickable mega-menu_flyout-open' );
		$this->add_render_attribute( 'button-open', 'aria-expanded', 'false' );
		$this->add_render_attribute( 'button-open', 'aria-controls', esc_attr( $mega_menu_id ) );
		$this->add_render_attribute( 'button-open', 'aria-hidden', 'true' );
		$this->add_render_attribute( 'button-open', 'aria-label', esc_html__( 'Ouvrir le menu', 'eac-components' ) . ' ' . esc_html( $current_menu_label ) );

		$this->add_render_attribute( 'button-close', 'id', esc_attr( $toggle_close ) );
		$this->add_render_attribute( 'button-close', 'type', 'button' );
		$this->add_render_attribute( 'button-close', 'class', 'mega-menu_nav-toggle elementor-clickable mega-menu_flyout-close' );
		$this->add_render_attribute( 'button-close', 'aria-expanded', 'false' );
		$this->add_render_attribute( 'button-close', 'aria-controls', esc_attr( $mega_menu_id ) );
		$this->add_render_attribute( 'button-close', 'aria-hidden', 'true' );
		$this->add_render_attribute( 'button-close', 'aria-label', esc_html__( 'Fermer le menu', 'eac-components' ) . ' ' . esc_html( $current_menu_label ) );

		ob_start();
		if ( '(max-width:240px)' !== $settings['mn_settings_responsive_breakpoint'] ) {
			?>
		<button <?php echo wp_kses_post( $this->get_render_attribute_string( 'button-open' ) ); ?>>
			<div class='mega-menu_menu-icon'>
				<?php echo isset( $menu_icons[0] ) ? wp_kses( $menu_icons[0], $allowed_content ) . '<span class="toggle-menu">Menu</span>' : ''; ?>
			</div>
		</button>
		<button <?php echo wp_kses_post( $this->get_render_attribute_string( 'button-close' ) ); ?>>
			<div class='mega-menu_menu-icon'>
				<?php echo isset( $menu_icons[1] ) ? wp_kses( $menu_icons[1], $allowed_content ) . '<span class="toggle-menu">' . esc_html__( 'Fermer', 'eac-components' ) . '</span>' : ''; ?>
			</div>
		</button>
			<?php
		}
		echo '<nav ' . wp_kses_post( $this->get_render_attribute_string( 'nav-menu' ) ) . '>';
			echo wp_nav_menu( $args );
		echo '</nav>';
		echo '<div class="eac-skip-grid" tabindex="0"><span class="visually-hidden">' . esc_html__( 'Sortir de la grille', 'eac-components' ) . '</span></div>';
		$output = ob_get_clean();

		$kses_defaults = wp_kses_allowed_html( 'post' );
		$svg_args      = array(
			'svg'  => array(
				'class'           => true,
				'aria-hidden'     => true,
				'aria-labelledby' => true,
				'role'            => true,
				'xmlns'           => true,
				'width'           => true,
				'height'          => true,
				'viewbox'         => true,
			),
			'path' => array(
				'd'    => true,
				'fill' => true,
			),
		);

		$allowed_tags = array_merge( $kses_defaults, $svg_args );
		echo wp_kses( $output, $allowed_tags );
	}

	/**
	 * Get the menu and close icon HTML.
	 *
	 * @param array $settings Widget settings array.
	 * @access public
	 */
	public function get_menu_icons( $settings ) {
		$menu_icon     = '';
		$close_icon    = '';
		$icons         = array();
		$icon_settings = array(
			$settings['mn_content_wrapper_dropdown_icon'],
			$settings['mn_content_wrapper_dropdown_close_icon'],
		);

		foreach ( $icon_settings as $icon ) {
			ob_start();
			Icons_Manager::render_icon(
				$icon,
				array(
					'aria-hidden' => 'true',
				)
			);
			$menu_icon = ob_get_clean();

			array_push( $icons, $menu_icon );
		}

		return $icons;
	}

	/**
	 * Woocommerce Mini cart in widget
	 */
	private function get_the_widget( $widget ) {
		// error_log(json_encode(get_dynamic_block_names()));
		ob_start();
		the_widget( $widget );
		return ob_get_clean();
	}

	/**
	 * Woocommerce Mini cart in widget
	 */
	public function add_custom_cart( $items, $args ) {
		$settings        = $this->get_settings_for_display();
		$has_cart        = ! is_null( WC()->cart );
		$allowed_content = array(
			'i' => array(
				'class'       => array(),
				'aria-hidden' => array(),
			),
		);

		if ( $has_cart && ( ! is_cart() && ! is_checkout() ) ) {
			ob_start();
			Icons_Manager::render_icon( $settings['mn_content_mini_cart_icon'], array( 'aria-hidden' => 'true' ) );
			$menu_icon = ob_get_clean();

			$old_items = $items;
			ob_start();
			$count_items = WC()->cart->get_cart_contents_count();
			?>
			<li id='menu-item-mini-cart' class='menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children mega-menu_top-item menu-item-mini-cart' role='none'>
				<a href='#' class='mega-menu_top-link' role="menuitem" aria-haspopup='true' aria-expanded='false' aria-controls='sub-menu-mini-cart'>
					<span class='eac-shopping-cart'><?php echo isset( $menu_icon ) ? wp_kses( $menu_icon, $allowed_content ) : ''; ?></span>
					<?php if ( 'yes' === $settings['mn_content_mini_cart_badge'] ) { ?>
						<span class='badge-cart__quantity'><?php echo esc_attr( $count_items ); ?></span>
					<?php } ?>
				</a>
				<ul id='sub-menu-mini-cart' class='mini-cart-product mega-menu_sub-menu' role='menu' aria-label='Mini cart product'>
					<li class='mega-menu_sub-item' role='none'>
						<?php echo wp_kses_post( $this->get_the_widget( 'WC_Widget_Cart' ) ); ?>
					</li>
				</ul>
			</li>
			<?php
			$new_item = ob_get_clean();
			$items    = $old_items . $new_item;
		}

		return $items;
	}

	/**
	 * includes
	 *
	 * les actions 'wp_update_nav_menu' et 'wp_delete_nav_menu' sont dans le fichier 'eac-nav-menu.php'
	 * Le filtre 'wp_nav_menu' et la fonction 'wp_nav_menu' sont court-circuités par 'pre_wp_nav_menu'
	 *
	 * Ajout des filtres pour la sauvegarde/restitution du menu dans une option pour un chargement plus rapide
	 * Force le chargement du cart s'il est null. Le cart est uniquement initialisé sur le frontend
	 */
	public function includes() {
		require_once __DIR__ . '/classes/class-menu-walker.php';

		if ( defined( 'WC_ABSPATH' ) ) {
			if ( is_null( WC()->cart ) ) {
				include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
				include_once WC_ABSPATH . 'includes/class-wc-cart.php';
				wc_load_cart();
			}
		}
	}

	protected function content_template() {}
}
