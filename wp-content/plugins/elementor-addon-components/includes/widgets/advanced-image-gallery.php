<?php
/**
 * Class: Advanced_Image_Gallery_Widget
 * Name: Galerie d'Images Avancée
 * Slug: eac-addon-advanced-gallery
 *
 * Description: Affiche les images des médias et leur contenu dans différents modes
 * grille, mosaïque, metro, justifié et slider
 *
 * @since 2.2.0
 */

namespace EACCustomWidgets\Includes\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\EAC_Plugin;
use EACCustomWidgets\Core\Eac_Config_Elements;
use EACCustomWidgets\Core\Utils\Eac_Helpers_Util;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Icons_Manager;
use Elementor\Core\Breakpoints\Manager as Breakpoints_manager;
use Elementor\Plugin;
use Elementor\Utils;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Advanced_Image_Gallery_Widget extends Widget_Base {
	/** Le slider Trait */
	use \EACCustomWidgets\Includes\Widgets\Traits\Slider_Trait;
	use \EACCustomWidgets\Includes\Widgets\Traits\Button_Read_More_Trait;

	/**
	 * Constructeur de la class Image_Galerie_Widget
	 *
	 * Enregistre les scripts et les styles
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_script( 'swiper', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.3.2/swiper-bundle.min.js', array( 'jquery' ), '8.3.2', true );
		wp_register_script( 'isotope', EAC_ADDON_URL . 'assets/js/isotope/isotope.pkgd.min.js', array( 'jquery' ), '3.0.6', true );
		wp_register_script( 'eac-fit-rows', EAC_Plugin::instance()->get_script_url( 'assets/js/isotope/fit-rows' ), array( 'isotope' ), '2.1.1', true );
		wp_register_script( 'eac-imagesloaded', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.4/imagesloaded.pkgd.min.js', array( 'jquery' ), '4.1.4', true );
		wp_register_script( 'fj-gallery', 'https://cdnjs.cloudflare.com/ajax/libs/flickr-justified-gallery/2.2.0/fjGallery.min.js', array( 'jquery' ), '2.2.0', true );
		wp_register_script( 'eac-advanced-gallery', EAC_Plugin::instance()->get_script_url( 'assets/js/elementor/eac-advanced-gallery' ), array( 'jquery', 'elementor-frontend', 'isotope', 'swiper', 'eac-imagesloaded', 'fj-gallery' ), '2.2.0', true );

		wp_register_style( 'swiper-bundle', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.3.2/swiper-bundle.min.css', array(), '8.3.2' );
		wp_register_style( 'fj-gallery', 'https://cdnjs.cloudflare.com/ajax/libs/flickr-justified-gallery/2.2.0/fjGallery.min.css', array(), '2.2.0' );
		wp_enqueue_style( 'eac-swiper', EAC_Plugin::instance()->get_style_url( 'assets/css/eac-swiper' ), array(), '1.9.8' );
		wp_enqueue_style( 'eac-advanced-gallery', EAC_Plugin::instance()->get_style_url( 'assets/css/advanced-gallery' ), array( 'eac-swiper', 'fj-gallery' ), EAC_PLUGIN_VERSION );
	}

	/**
	 * Le nom de la clé du composant dans le fichier de configuration
	 *
	 * @var $slug
	 *
	 * @access private
	 */
	private $slug = 'advanced-gallery';

	/**
	 * Retrieve widget name.
	 *
	 * @access public
	 *
	 * @return string widget name.
	 */
	public function get_name() {
		return Eac_Config_Elements::get_widget_name( $this->slug );
	}

	/**
	 * Retrieve widget title.
	 *
	 * @access public
	 *
	 * @return string widget title.
	 */
	public function get_title() {
		return Eac_Config_Elements::get_widget_title( $this->slug );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @access public
	 *
	 * @return string widget icon.
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
		return array( 'isotope', 'eac-imagesloaded', 'swiper', 'eac-fit-rows', 'fj-gallery', 'eac-advanced-gallery' );
	}

	/**
	 * Load dependent styles
	 * Les styles sont chargés dans le footer
	 *
	 * @access public
	 *
	 * @return CSS list.
	 */
	public function get_style_depends() {
		return array( 'swiper-bundle', 'fj-gallery' );
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

	protected function get_upsale_data() {
		return array();
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_controls() {

		$responsive_breakpoints = array( 'desktop' );
		$columns_device_args    = array();
		$active_breakpoints     = Plugin::$instance->breakpoints->get_active_breakpoints();

		foreach ( $active_breakpoints as $breakpoint_name => $breakpoint_instance ) {
			$responsive_breakpoints[] = $breakpoint_name;
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

		$this->start_controls_section(
			'ag_galerie_settings',
			array(
				'label' => esc_html__( 'Galerie', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'advanced_gallery',
				array(
					'label'      => esc_html__( 'Ajouter des images', 'eac-components' ),
					'type'       => Controls_Manager::GALLERY,
					'show_label' => true,
					'dynamic'    => array(
						'active'     => true,
						'categories' => array(
							TagsModule::GALLERY_CATEGORY,
						),
					),
					'default'    => array(),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ag_layout_type_settings',
			array(
				'label' => esc_html__( 'Réglages', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'ag_layout_content',
				array(
					'label'     => esc_html__( 'Disposition', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
				)
			);

			$this->add_control(
				'ag_layout_type',
				array(
					'label'   => esc_html__( 'Mode', 'eac-components' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'masonry',
					'options' => array(
						'masonry'     => esc_html__( 'Mosaïque', 'eac-components' ),
						'equalHeight' => esc_html__( 'Grille', 'eac-components' ),
						'metro'       => esc_html__( 'Metro', 'eac-components' ),
						'justify'     => esc_html__( 'Justifier', 'eac-components' ),
						'slider'      => esc_html( 'Slider' ),
					),
				)
			);

			$this->add_responsive_control(
				'ag_columns',
				array(
					'label'        => esc_html__( 'Nombre de colonnes', 'eac-components' ),
					'type'         => Controls_Manager::SELECT,
					'default'      => '3',
					'device_args'  => $columns_device_args,
					'options'      => array(
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'5' => '5',
						'6' => '6',
					),
					'prefix_class' => 'responsive%s-',
					'render_type'  => 'template',
					'condition'    => array( 'ag_layout_type!' => array( 'slider', 'justify' ) ),
				)
			);

			$this->add_control(
				'ag_image_order',
				array(
					'label'     => esc_html__( 'Affichage aléatoire', 'eac-components' ),
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
					'condition' => array( 'ag_layout_type!' => array( 'slider', 'justify' ) ),
				)
			);

			/** inversion droite/gauche */
			$this->add_control(
				'ag_item_scroll',
				array(
					'label'     => esc_html__( "Direction de l'affichage", 'eac-components' ),
					'type'      => Controls_Manager::CHOOSE,
					'options'   => array(
						'left'  => array(
							'title' => esc_html__( 'Droite', 'eac-components' ),
							'icon'  => 'eicon-order-end',
						),
						'right' => array(
							'title' => esc_html__( 'Gauche', 'eac-components' ),
							'icon'  => 'eicon-order-start',
						),
					),
					'default'   => 'left',
					'toggle'    => false,
					'condition' => array( 'ag_layout_type!' => array( 'slider', 'justify' ) ),
				)
			);

			$this->add_control(
				'ag_image_settings',
				array(
					'label'     => esc_html__( 'Image', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				Group_Control_Image_Size::get_type(),
				array(
					'name'      => 'ag_image_size',
					'label'     => esc_html__( 'Dimension des images', 'eac-components' ),
					'default'   => 'medium',
					'exclude'   => array( 'custom' ),
				)
			);

			$this->add_control(
				'ag_image_lazy',
				array(
					'label'     => esc_html( 'Lazy load' ),
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
					'default'   => 'yes',
					'toggle'    => false,
				)
			);

			$this->add_responsive_control(
				'ag_image_height',
				array(
					'label'              => esc_html__( 'Hauteur (px)', 'eac-components' ),
					'type'               => Controls_Manager::SLIDER,
					'size_units'         => array( 'px' ),
					'devices'            => $responsive_breakpoints,
					'default'            => array(
						'unit' => 'px',
						'size' => 250,
					),
					'tablet_default'     => array(
						'unit' => 'px',
						'size' => 200,
					),
					'mobile_default'     => array(
						'unit' => 'px',
						'size' => 150,
					),
					'range'              => array(
						'px' => array(
							'min'  => 100,
							'max'  => 500,
							'step' => 25,
						),
					),
					'frontend_available' => true,
					'condition'          => array(
						'ag_layout_type'   => 'justify',
					),
				)
			);

			$this->add_control(
				'ag_enable_image_ratio',
				array(
					'label'     => esc_html__( 'Activer le ratio image', 'eac-components' ),
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
					'condition' => array(
						'ag_layout_type'   => array( 'equalHeight', 'metro' ),
					),
				)
			);

			$this->add_responsive_control(
				'ag_image_ratio',
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
					'selectors'      => array( '{{WRAPPER}} .advanced-gallery .advanced-gallery__image-instance' => 'aspect-ratio:{{SIZE}};' ),
					'render_type'    => 'template',
					'condition'      => array(
						'ag_layout_type'        => array( 'equalHeight', 'metro' ),
						'ag_enable_image_ratio' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ag_image_ratio_position_y',
				array(
					'label'       => esc_html__( 'Position verticale', 'eac-components' ),
					'type'        => Controls_Manager::SLIDER,
					'size_units'  => array( '%' ),
					'default'     => array(
						'size' => 50,
						'unit' => '%',
					),
					'range'       => array(
						'%' => array(
							'min'  => 0,
							'max'  => 100,
							'step' => 5,
						),
					),
					'selectors'   => array( '{{WRAPPER}} .advanced-gallery .advanced-gallery__image-instance' => 'object-position: 50% {{SIZE}}%;' ),
					'render_type' => 'ui',
					'condition'   => array(
						'ag_layout_type'        => array( 'equalHeight', 'metro' ),
						'ag_enable_image_ratio' => 'yes',
					),
				)
			);

			$this->add_control(
				'ag_overlay_content',
				array(
					'label'     => esc_html__( 'Disposition du contenu', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'ag_overlay_inout',
				array(
					'label'   => esc_html__( 'Affichage du contenu', 'eac-components' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'overlay-out',
					'options' => array(
						'overlay-out' => esc_html__( 'Carte ', 'eac-components' ),
						'overlay-in'  => esc_html__( 'Calque', 'eac-components' ),
						'overlay-fix' => esc_html__( 'Calque partiel', 'eac-components' ),
					),
				)
			);

			$this->add_control(
				'ag_overlay_direction',
				array(
					'label'        => esc_html__( "Direction de l'overlay", 'eac-components' ),
					'type'         => Controls_Manager::CHOOSE,
					'options'      => array(
						'bottom' => array(
							'title' => esc_html__( 'Haut', 'eac-components' ),
							'icon'  => 'eicon-v-align-top',
						),
						'left'   => array(
							'title' => esc_html__( 'Gauche', 'eac-components' ),
							'icon'  => 'eicon-h-align-left',
						),
						'right'  => array(
							'title' => esc_html__( 'Droite', 'eac-components' ),
							'icon'  => 'eicon-h-align-right',
						),
						'top'    => array(
							'title' => esc_html__( 'Bas', 'eac-components' ),
							'icon'  => 'eicon-v-align-bottom',
						),
					),
					'default'      => 'top',
					'toggle'       => false,
					'prefix_class' => 'overlay-',
					'conditions'   => array(
						'terms' => array(
							array(
								'terms' => array(
									array(
										'name'     => 'ag_overlay_inout',
										'operator' => '===',
										'value'    => 'overlay-in',
									),
								),
							),
						),
					),
				)
			);

			$this->add_responsive_control(
				'ag_overlayfix-height',
				array(
					'label'       => esc_html__( 'Hauteur (%)', 'eac-components' ),
					'type'        => Controls_Manager::SLIDER,
					'size_units'  => array( '%' ),
					'default'     => array(
						'size' => 50,
						'unit' => '%',
					),
					'range'       => array(
						'%' => array(
							'min'  => 10,
							'max'  => 100,
							'step' => 5,
						),
					),
					'render_type' => 'ui',
					'selectors'   => array( '{{WRAPPER}} .advanced-gallery__content.overlay-fix' => 'height:{{SIZE}}%;' ),
					'condition'   => array( 'ag_overlay_inout' => 'overlay-fix' ),
				)
			);

			$this->add_responsive_control(
				'ag_overlay_alignment_v',
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
					'toggle'      => false,
					'label_block' => true,
					'selectors'   => array(
						'{{WRAPPER}} .advanced-gallery__overlay' => 'justify-content: {{VALUE}};',
					),
				)
			);

			$this->add_responsive_control(
				'ag_overlay_alignment_h',
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
						'{{WRAPPER}} .advanced-gallery__overlay' => 'align-items: {{VALUE}};',
						'{{WRAPPER}} .advanced-gallery__description-wrapper' => 'text-align: {{VALUE}};',
					),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ag_slider_settings',
			array(
				'label'     => 'Slider',
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'ag_layout_type' => 'slider' ),
			)
		);

			$this->register_slider_content_controls();

		$this->end_controls_section();

		$this->start_controls_section(
			'ag_gallery_content',
			array(
				'label' => esc_html__( 'Contenu', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'ag_filter_heading',
				array(
					'label'     => esc_html__( 'Filtres', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'condition' => array( 'ag_layout_type!' => 'slider' ),
				)
			);

			$this->add_control(
				'ag_content_filter_display',
				array(
					'label'     => esc_html__( 'Afficher les filtres', 'eac-components' ),
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
					'condition' => array( 'ag_layout_type!' => array( 'slider', 'justify' ) ),
				)
			);

			$this->add_control(
				'ag_content_filter_align',
				array(
					'label'     => esc_html__( 'Alignement', 'eac-components' ),
					'type'      => Controls_Manager::CHOOSE,
					'options'   => array(
						'left'   => array(
							'title' => esc_html__( 'Gauche', 'eac-components' ),
							'icon'  => 'eicon-h-align-left',
						),
						'center' => array(
							'title' => esc_html__( 'Centre', 'eac-components' ),
							'icon'  => 'eicon-h-align-center',
						),
						'right'  => array(
							'title' => esc_html__( 'Droite', 'eac-components' ),
							'icon'  => 'eicon-h-align-right',
						),
					),
					'default'   => 'left',
					'toggle'    => false,
					'selectors' => array(
						'{{WRAPPER}} .ag-filters__wrapper, {{WRAPPER}} .ag-filters__wrapper-select' => 'text-align: {{VALUE}};',
					),
					'condition' => array(
						'ag_content_filter_display' => 'yes',
						'ag_layout_type!'           => array( 'slider', 'justify' ),
					),
				)
			);

			$this->add_control(
				'ag_post_heading',
				array(
					'label'     => esc_html__( 'Article', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'ag_content_title',
				array(
					'label'   => esc_html__( 'Afficher le titre', 'eac-components' ),
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
					'default' => 'yes',
					'toggle'  => false,
				)
			);

			$this->add_control(
				'ag_title_tag',
				array(
					'label'     => esc_html__( 'Étiquette du titre', 'eac-components' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'h2',
					'options'   => array(
						'h1'   => 'H1',
						'h2'   => 'H2',
						'h3'   => 'H3',
						'h4'   => 'H4',
						'h5'   => 'H5',
						'h6'   => 'H6',
						'div'  => 'div',
						'span' => 'span',
						'p'    => 'p',
					),
					'condition' => array( 'ag_content_title' => 'yes' ),
				)
			);

			$this->add_control(
				'ag_content_description',
				array(
					'label'   => esc_html__( 'Afficher la description', 'eac-components' ),
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
					'default' => 'yes',
					'toggle'  => false,
				)
			);

			$this->add_control(
				'ag_excerpt_length',
				array(
					'label'     => esc_html__( 'Nombre de mots', 'eac-components' ),
					'type'      => Controls_Manager::NUMBER,
					'min'       => 10,
					'max'       => 100,
					'step'      => 5,
					'default'   => 25,
					'condition' => array( 'ag_content_description' => 'yes' ),
				)
			);

			$this->add_control(
				'ag_content_readmore',
				array(
					'label'   => esc_html__( "Bouton 'En savoir plus'", 'eac-components' ),
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
					'default' => 'yes',
					'toggle'  => false,
				)
			);

			$this->add_control(
				'ag_links_heading',
				array(
					'label'      => esc_html__( 'Liens', 'eac-components' ),
					'type'       => Controls_Manager::HEADING,
					'condition' => array( 'ag_content_title' => 'yes' ),
					'separator'  => 'before',
				)
			);

			$this->add_control(
				'ag_link_nofollow',
				array(
					'label'      => esc_html__( "Ajouter 'nofollow' aux liens", 'eac-components' ),
					'type'       => Controls_Manager::CHOOSE,
					'options'    => array(
						'yes' => array(
							'title' => esc_html__( 'Oui', 'eac-components' ),
							'icon'  => 'fas fa-check',
						),
						'no'  => array(
							'title' => esc_html__( 'Non', 'eac-components' ),
							'icon'  => 'fas fa-ban',
						),
					),
					'default'    => 'no',
					'toggle'     => false,
					'conditions' => array(
						'relation' => 'or',
						'terms'    => array(
							array(
								'name'     => 'ag_content_readmore',
								'operator' => '===',
								'value'    => 'yes',
							),
							array(
								'name'     => 'ag_image_link',
								'operator' => '===',
								'value'    => 'yes',
							),
							array(
								'name'     => 'ag_content_title_link',
								'operator' => '===',
								'value'    => 'yes',
							),
						),
					),
				)
			);

			$this->add_control(
				'ag_image_link',
				array(
					'label'     => esc_html__( "Lien de l'article sur l'image", 'eac-components' ),
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
					'condition' => array(
						'ag_overlay_inout'   => array( 'overlay-out', 'overlay-fix' ),
						'ag_image_lightbox!' => 'yes',
					),
				)
			);

			$this->add_control(
				'ag_image_lightbox',
				array(
					'label'     => esc_html__( "Visionneuse sur l'image", 'eac-components' ),
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
					'condition' => array(
						'ag_layout_type!'  => 'slider',
						'ag_overlay_inout' => array( 'overlay-out', 'overlay-fix' ),
						'ag_image_link!'   => 'yes',
					),
				)
			);

			$this->add_control(
				'ag_content_title_link',
				array(
					'label'     => esc_html__( "Lien de l'article sur le titre", 'eac-components' ),
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
					'condition' => array( 'ag_content_title' => 'yes' ),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ag_readmore_settings',
			array(
				'label'     => esc_html__( "Bouton 'En savoir plus'", 'eac-components' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'ag_content_readmore' => 'yes' ),
			)
		);

			// Trait du contenu du bouton read more
			$this->register_button_more_content_controls();

		$this->end_controls_section();

		/**
		 * Generale Style Section
		 */
		$this->start_controls_section(
			'ag_section_general_style',
			array(
				'label' => esc_html__( 'Général', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'ag_img_style',
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
						'style-8'  => 'Style 5',
						'style-10' => 'Style 6',
						'style-11' => 'Style 7',
						'style-12' => 'Style 8',
					),
					'prefix_class' => 'advanced-gallery__wrapper-',
				)
			);

			$this->add_responsive_control(
				'ag_items_margin',
				array(
					'label'      => esc_html__( 'Marge entre les images', 'eac-components' ),
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
						'{{WRAPPER}} .advanced-gallery .advanced-gallery__inner-wrapper' => 'margin: {{SIZE}}{{UNIT}}; height: calc(100% - 2 * {{SIZE}}{{UNIT}});',
					),
					'condition'  => array( 'ag_layout_type!' => 'justify' ),
				)
			);

			$this->add_responsive_control(
				'ag_items_justify_margin',
				array(
					'label'          => esc_html__( 'Marge entre les images', 'eac-components' ),
					'type'           => Controls_Manager::SLIDER,
					'size_units'     => array( 'px' ),
					'devices'        => $responsive_breakpoints,
					'default'        => array(
						'size' => 15,
						'unit' => 'px',
					),
					'tablet_default' => array(
						'size' => 10,
						'unit' => 'px',
					),
					'mobile_default' => array(
						'size' => 5,
						'unit' => 'px',
					),
					'range'          => array(
						'px' => array(
							'min'  => 0,
							'max'  => 50,
							'step' => 1,
						),
					),
					'render_type'    => 'template',
					'selectors'      => array(
						'{{WRAPPER}} .advanced-gallery.layout-type-justify.fj-gallery' => 'padding: calc({{SIZE}}{{UNIT}} / 2);',
					),
					'condition'      => array( 'ag_layout_type' => 'justify' ),
				)
			);

			$this->add_control(
				'ag_container_style_bgcolor',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'selectors' => array( '{{WRAPPER}} .swiper-container .swiper-slide, {{WRAPPER}} .advanced-gallery' => 'background-color: {{VALUE}};' ),
				)
			);

			/** Articles */
			$this->add_control(
				'ag_items_style',
				array(
					'label'     => esc_html__( 'Articles', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'ag_overlay_inout' => 'overlay-out' ),
				)
			);

			$this->add_control(
				'ag_items_bg_color',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'selectors' => array( '{{WRAPPER}} .advanced-gallery__inner-wrapper, {{WRAPPER}} .advanced-gallery__content.overlay-out' => 'background-color: {{VALUE}};' ),
					'condition' => array( 'ag_overlay_inout' => 'overlay-out' ),
				)
			);

			$this->add_control(
				'ag_filter_style',
				array(
					'label'     => esc_html__( 'Filtre', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array(
						'ag_layout_type!'           => array( 'slider', 'justify' ),
						'ag_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_control(
				'ag_filter_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'selectors' => array(
						'{{WRAPPER}} .ag-filters__wrapper .ag-filters__item, {{WRAPPER}} .ag-filters__wrapper .ag-filters__item a' => 'color: {{VALUE}};',
					),
					'condition' => array(
						'ag_layout_type!'           => array( 'slider', 'justify' ),
						'ag_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'ag_filter_typography',
					'label'     => esc_html__( 'Typographie', 'eac-components' ),
					'global'    => array( 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ),
					'selector'  => '{{WRAPPER}} .ag-filters__wrapper .ag-filters__item, {{WRAPPER}} .ag-filters__wrapper .ag-filters__item a',
					'condition' => array(
						'ag_layout_type!'           => array( 'slider', 'justify' ),
						'ag_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_control(
				'ag_filter_background',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_SECONDARY ),
					'selectors' => array( '{{WRAPPER}} .ag-filters__wrapper .ag-filters__item a' => 'background-color: {{VALUE}};' ),
					'condition' => array(
						'ag_layout_type!'           => array( 'slider', 'justify' ),
						'ag_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_control(
				'ag_filter_outline',
				array(
					'label'     => esc_html__( 'Couleur bordure inférieure', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'selectors' => array( '{{WRAPPER}} .ag-filters__wrapper .ag-filters__item.ag-active::after' => 'border-bottom: 3px solid {{VALUE}};' ),
					'condition' => array(
						'ag_layout_type!'           => array( 'slider', 'justify' ),
						'ag_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ag_filter_padding',
				array(
					'label'     => esc_html__( 'Marges internes', 'eac-components' ),
					'type'      => Controls_Manager::DIMENSIONS,
					'selectors' => array(
						'{{WRAPPER}} .ag-filters__wrapper .ag-filters__item a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition' => array(
						'ag_layout_type!'           => array( 'slider', 'justify' ),
						'ag_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'      => 'ag_filter_border',
					'selector'  => '{{WRAPPER}} .ag-filters__wrapper .ag-filters__item a',
					'condition' => array(
						'ag_layout_type!'           => array( 'slider', 'justify' ),
						'ag_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_control(
				'ag_filter_radius',
				array(
					'label'              => esc_html__( 'Rayon de la bordure', 'eac-components' ),
					'type'               => Controls_Manager::DIMENSIONS,
					'size_units'         => array( 'px', '%' ),
					'allowed_dimensions' => array( 'top', 'right', 'bottom', 'left' ),
					'selectors'          => array(
						'{{WRAPPER}} .ag-filters__wrapper .ag-filters__item a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'          => array(
						'ag_layout_type!'           => array( 'slider', 'justify' ),
						'ag_content_filter_display' => 'yes',
					),
				)
			);

			/** Image */
			$this->add_control(
				'ag_image_section_style',
				array(
					'label'     => esc_html__( 'Image', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'      => 'ag_image_border',
					'selector'  => '{{WRAPPER}} .advanced-gallery__image img',
				)
			);

			$this->add_control(
				'ag_image_border_radius',
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
						'{{WRAPPER}} .advanced-gallery__image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Css_Filter::get_type(),
				array(
					'name'      => 'ag_image_css_filters',
					'selector'  => '{{WRAPPER}} .advanced-gallery__image img',
				)
			);

			/** Titre */
			$this->add_control(
				'ag_titre_section_style',
				array(
					'label'     => esc_html__( 'Titre', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'ag_content_title' => 'yes' ),
				)
			);

			$this->add_control(
				'ag_titre_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'selectors' => array(
						'{{WRAPPER}} .advanced-gallery__item .advanced-gallery__content .advanced-gallery__overlay .advanced-gallery__title' => 'color: {{VALUE}};',
					),
					'condition' => array( 'ag_content_title' => 'yes' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'ag_titre_typography',
					'label'     => esc_html__( 'Typographie', 'eac-components' ),
					'global'    => array( 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ),
					'selector'  => '{{WRAPPER}} .advanced-gallery__item .advanced-gallery__content .advanced-gallery__overlay .advanced-gallery__title',
					'condition' => array( 'ag_content_title' => 'yes' ),
				)
			);

			$this->add_control(
				'ag_texte_section_style',
				array(
					'label'     => esc_html__( 'Description', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'ag_content_description' => 'yes' ),
				)
			);

			$this->add_control(
				'ag_texte_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_TEXT ),
					'selectors' => array( '{{WRAPPER}} .advanced-gallery__item .advanced-gallery__content .advanced-gallery__overlay .advanced-gallery__description-wrapper' => 'color: {{VALUE}};' ),
					'condition' => array( 'ag_content_description' => 'yes' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'ag_texte_typography',
					'label'     => esc_html__( 'Typographie', 'eac-components' ),
					'global'    => array( 'default' => Global_Typography::TYPOGRAPHY_TEXT ),
					'selector'  => '{{WRAPPER}} .advanced-gallery__item .advanced-gallery__content .advanced-gallery__overlay .advanced-gallery__description-wrapper',
					'condition' => array( 'ag_content_description' => 'yes' ),
				)
			);

			/** Overlay */
			$this->add_control(
				'ag_overlay_section_style',
				array(
					'label'     => esc_html__( 'Superposition', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'ag_overlay_inout!' => 'overlay-out' ),
				)
			);

			$this->add_control(
				'ag_overlay_style_bg',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_SECONDARY ),
					'selectors' => array(
						'{{WRAPPER}} .advanced-gallery__content.overlay-fix, {{WRAPPER}} .advanced-gallery__content.overlay-in' => 'background-color: {{VALUE}};',
					),
					'condition' => array( 'ag_overlay_inout!' => 'overlay-out' ),
				)
			);

			$this->add_responsive_control(
				'ag_overlay_style_padding',
				array(
					'label'     => esc_html__( 'Marges internes', 'eac-components' ),
					'type'      => Controls_Manager::DIMENSIONS,
					'selectors' => array(
						'{{WRAPPER}} .advanced-gallery__overlay' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition' => array( 'ag_overlay_inout!' => 'overlay-out' ),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ag_slider_section_style',
			array(
				'label'      => esc_html__( 'Contrôles du slider', 'eac-components' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'     => 'ag_layout_type',
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
									'name'     => 'ag_layout_type',
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
			'ag_readmore_style',
			array(
				'label'     => esc_html__( "Bouton 'En savoir plus'", 'eac-components' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'ag_content_readmore' => 'yes' ),
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
		$settings    = $this->get_settings_for_display();
		$layout_type = $settings['ag_layout_type'];

		if ( ! $settings['advanced_gallery'] || empty( $settings['advanced_gallery'] ) ) {
			return;
		}

		$id             = 'advanced_gallery_' . $this->get_id();
		$slider_id      = 'slider_advanced_gallery_' . $this->get_id();
		$has_swiper     = 'slider' === $layout_type ? true : false;
		$has_filters    = ! $has_swiper && 'justify' !== $layout_type && 'yes' === $settings['ag_content_filter_display'] ? true : false;
		$has_navigation = $has_swiper && 'yes' === $settings['slider_navigation'] ? true : false;
		$has_pagination = $has_swiper && 'yes' === $settings['slider_pagination'] ? true : false;
		$has_scrollbar  = $has_swiper && 'yes' === $settings['slider_scrollbar'] ? true : false;

		if ( 'equalHeight' === $layout_type ) {
			$layout_mode = 'fitRows';
		} elseif ( 'metro' === $layout_type ) {
			$layout_mode = 'masonry';
		} else {
			$layout_mode = $layout_type;
		}

		if ( ! $has_swiper ) {
			if ( 'justify' === $layout_type ) {
				$class = 'advanced-gallery layout-type-justify fj-gallery';
			} else {
				$class = sprintf( 'advanced-gallery layout-type-%s', $layout_mode );
			}
		} else {
			$class = 'advanced-gallery swiper-wrapper';
		}

		$this->add_render_attribute( 'gallery_instance', 'class', esc_attr( $class ) );
		$this->add_render_attribute( 'gallery_instance', 'id', esc_attr( $id ) );
		$this->add_render_attribute( 'gallery_instance', 'role', 'region' );
		$this->add_render_attribute( 'gallery_instance', 'aria-relevant', 'additions' );
		if ( $has_filters ) {
			$this->add_render_attribute( 'gallery_instance', 'aria-live', 'polite' );
			$this->add_render_attribute( 'gallery_instance', 'aria-atomic', 'true' );
		}
		$this->add_render_attribute( 'gallery_instance', 'data-settings', $this->get_settings_json( $id ) );

		if ( $has_swiper ) { ?>
			<div id="<?php echo esc_attr( $slider_id ); ?>" class='eac-advanced-gallery swiper-container'>
		<?php } else { ?>
			<div class='eac-advanced-gallery'>
		<?php }

		if ( $has_filters ) {
			$this->render_advanced_gallery_filter();
		}
		?>
			<div <?php $this->print_render_attribute_string( 'gallery_instance' ); ?>>
				<?php if ( ! $has_swiper ) { ?>
					<div class='advanced-gallery__item-sizer'></div>
				<?php }
				$this->render_gallery(); ?>
			</div>
			<?php if ( $has_navigation ) { ?>
				<div class='swiper-button-next'></div>
				<div class='swiper-button-prev'></div>
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
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_gallery() {
		$settings = $this->get_settings_for_display();

		/** ID de l'article */
		$unique_id = uniqid();

		$layout_type = $settings['ag_layout_type'];

		/** Le swiper est actif */
		$has_swiper = 'slider' === $layout_type ? true : false;

		/** L'image */
		$has_image  = true;
		$image_size = $settings['ag_image_size_size'];

		$lazy_load = 'yes' === $settings['ag_image_lazy'] ? 'lazy' : 'eager';

		/** Visionneuse active mais pas avec le slider */
		$has_image_lightbox = ! $has_swiper && isset( $settings['ag_image_lightbox'] ) && 'yes' === $settings['ag_image_lightbox'] ? true : false;

		/** Lien sur l'image */
		$has_image_link = ! $has_image_lightbox && 'yes' === $settings['ag_image_link'] ? true : false;

		/** Le titre et sa balise */
		$has_title      = 'yes' === $settings['ag_content_title'] ? true : false;
		$has_title_link = $has_title && 'yes' === $settings['ag_content_title_link'] ? true : false;
		$title_tag      = $has_title && ! empty( $settings['ag_title_tag'] ) ? Utils::validate_html_tag( $settings['ag_title_tag'] ) : 'div';

		/** L'attribut nofollow */
		$has_noffolow = 'yes' === $settings['ag_link_nofollow'] ? true : false;

		/** La description */
		$has_description = 'yes' === $settings['ag_content_description'] ? true : false;

		/** Le bouton read more */
		$has_button_readmore = 'yes' === $settings['ag_content_readmore'] ? true : false;
		$has_readmore_picto  = $has_button_readmore && 'yes' === $settings['button_add_more_picto'] ? true : false;

		/** Filtres */
		$has_filters = ! $has_swiper && 'justify' !== $layout_type && 'yes' === $settings['ag_content_filter_display'] ? true : false;

		/** La classe du contenu de l'item */
		$this->add_render_attribute( 'gallery_inner', 'class', 'advanced-gallery__inner-wrapper' );

		$overlay = $settings['ag_overlay_inout'];

		/** La classe du titre/texte/boutons */
		$this->add_render_attribute( 'gallery_content', 'class', esc_attr( 'advanced-gallery__content ' . $overlay ) );
		if ( 'overlay-in' === $overlay ) {
			$this->add_render_attribute( 'gallery_content', 'tabindex', '0' );
		}

		/**
		 * Boucle sur tous les items de la galerie
		*/
		ob_start();
		foreach ( $settings['advanced_gallery'] as $image ) {
			$attachment    = array();
			$attachment_id = isset( $image['id'] ) ? $image['id'] : false;

			if ( $attachment_id ) {
				list($id, $filter, $post_id, $count_item) = array_pad( explode( '::', $attachment_id ), 4, '' );
				$attachment = Eac_Helpers_Util::wp_get_attachment_data( $id, $image_size, $filter, $post_id, $count_item );
			}

			if ( ! $attachment || empty( $attachment ) ) {
				continue;
			}

			if ( $has_filters && ! empty( $attachment['media_cat'] ) ) {
				$sanized = array();
				$filters = explode( ',', $attachment['media_cat'] );
				foreach ( $filters as $filter ) {
					$sanized[] = sanitize_title( mb_strtolower( $filter, 'UTF-8' ) );
				}
				$this->add_render_attribute( 'gallery_item', 'class', 'advanced-gallery__item ' . implode( ' ', $sanized ) );
			} else {
				if ( 'justify' === $layout_type ) {
					$this->add_render_attribute( 'gallery_item', 'class', 'advanced-gallery__item fj-gallery-item' );
				} elseif ( 'slider' === $layout_type ) {
					$this->add_render_attribute( 'gallery_item', 'class', 'advanced-gallery__item swiper-slide' );
				} else {
					$this->add_render_attribute( 'gallery_item', 'class', 'advanced-gallery__item' );
				}
			}

			// L'URL cible des attachments
			$media_url = ! empty( $attachment['media_url'] ) ? $attachment['media_url'] : false;

			if ( $has_button_readmore && $media_url ) {
				$this->add_render_attribute( 'button_readmore', 'class', 'button-readmore' );
				$this->add_render_attribute( 'button_readmore', 'role', 'button' );
				$this->add_render_attribute( 'button_readmore', 'aria-label', sanitize_text_field( $settings['button_more_label'] ) . ' ' . esc_html( $attachment['title'] ) );
				$this->add_render_attribute( 'button_readmore', 'href', esc_url( $media_url ) );
				if ( $has_noffolow ) {
					$this->add_render_attribute( 'button_readmore', 'rel', 'nofollow' );
				}
			}

			if ( $has_image_lightbox ) {
				$this->add_render_attribute( 'image_link', 'href', esc_url( $attachment['src'] ) );
				$this->add_render_attribute( 'image_link', 'class', 'eac-accessible-link' );
				$this->add_render_attribute( 'image_link', 'data-elementor-open-lightbox', 'no' );
				$this->add_render_attribute( 'image_link', 'data-fancybox', esc_attr( $unique_id ) );
				$this->add_render_attribute( 'image_link', 'data-caption', esc_html( ucfirst( $attachment['title'] ) ) );
				$this->add_render_attribute( 'image_link', 'aria-label', esc_html__( "Voir l'image", 'eac-components' ) . ' ' . esc_html( ucfirst( $attachment['title'] ) ) );
			} elseif ( $has_image_link && $media_url ) {
				$this->add_render_attribute( 'image_link', 'class', 'eac-accessible-link' );
				$this->add_render_attribute( 'image_link', 'aria-label', esc_html__( "Voir l'article", 'eac-components' ) . ' ' . esc_html( ucfirst( $attachment['title'] ) ) );
				$this->add_render_attribute( 'image_link', 'href', esc_url( $media_url ) );
				if ( $has_noffolow ) {
					$this->add_render_attribute( 'image_link', 'rel', 'nofollow' );
				}
			}

			if ( $has_title_link && $media_url ) {
				$this->add_render_attribute( 'title_link', 'class', 'eac-accessible-link' );
				$this->add_render_attribute( 'title_link', 'href', esc_url( $media_url ) );
				if ( $has_noffolow ) {
					$this->add_render_attribute( 'title_link', 'rel', 'nofollow' );
				}
			}

			$this->add_render_attribute( 'gallery_image', 'class', 'img-focusable advanced-gallery__image-instance' );
			$this->add_render_attribute( 'gallery_image', 'src', esc_url( $attachment['src'] ) );
			$this->add_render_attribute( 'gallery_image', 'srcset', esc_attr( $attachment['srcset'] ) );
			$this->add_render_attribute( 'gallery_image', 'sizes', esc_attr( $attachment['srcsize'] ) );
			$this->add_render_attribute( 'gallery_image', 'width', esc_attr( $attachment['width'] ) );
			$this->add_render_attribute( 'gallery_image', 'height', esc_attr( $attachment['height'] ) );
			$this->add_render_attribute( 'gallery_image', 'alt', esc_attr( $attachment['alt'] ) );
			if ( 'eager' === $lazy_load ) {
				$this->add_render_attribute( 'gallery_image', 'loading', $lazy_load );
			}
			?>
			<article <?php $this->print_render_attribute_string( 'gallery_item' ); ?>>
				<div <?php $this->print_render_attribute_string( 'gallery_inner' ); ?>>

					<?php if ( $has_image ) : ?>
						<div class='advanced-gallery__image'>
							<?php if ( $has_image_lightbox || $has_image_link ) : ?>
								<a <?php $this->print_render_attribute_string( 'image_link' ); ?>>
							<?php endif; ?>
								<img <?php $this->print_render_attribute_string( 'gallery_image' ); ?>>
							<?php if ( $has_image_lightbox || $has_image_link ) : ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<div <?php $this->print_render_attribute_string( 'gallery_content' ); ?>>
						<div class='advanced-gallery__overlay'>

							<?php if ( $has_title && ! Utils::is_empty( $attachment['title'] ) ) : ?>
								<div class='advanced-gallery__title-wrapper'>
									<?php $title_with_tag = '<' . $title_tag . ' class="advanced-gallery__title">' . esc_html( ucfirst( $attachment['title'] ) ) . '</' . $title_tag . '>';
									if ( $has_title_link && $media_url ) : ?>
										<a <?php $this->print_render_attribute_string( 'title_link' ); ?>>
									<?php endif;
										echo $title_with_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									if ( $has_title_link && $media_url ) : ?>
										</a>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ( $has_description ) :
								$trim_words = wp_trim_words( $attachment['description'], absint( $settings['ag_excerpt_length'] ), '...' );
								$trim_words = preg_replace( '/\|/', '<br>', $trim_words ); ?>
								<div class='advanced-gallery__description-wrapper'>
									<?php echo $trim_words; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							<?php endif; ?>

							<?php if ( $has_button_readmore && $media_url ) : ?>
								<div class='buttons-wrapper'>
									<span class='button__readmore-wrapper'>
										<a <?php $this->print_render_attribute_string( 'button_readmore' ); ?>>
											<?php
											if ( $has_readmore_picto && 'before' === $settings['button_more_position'] ) {
												ob_start();
												Icons_Manager::render_icon( $settings['button_more_picto'], array( 'aria-hidden' => 'true' ) );
												echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											}
												echo esc_html( sanitize_text_field( $settings['button_more_label'] ) );
											if ( $has_readmore_picto && 'after' === $settings['button_more_position'] ) {
												ob_start();
												Icons_Manager::render_icon( $settings['button_more_picto'], array( 'aria-hidden' => 'true' ) );
												echo ob_get_clean();  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											}
											?>
										</a>
									</span>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</article>
			<?php
			$this->remove_render_attribute( 'gallery_image' );
			$this->remove_render_attribute( 'gallery_item' );
			$this->remove_render_attribute( 'button_readmore' );
			$this->remove_render_attribute( 'image_link' );
			$this->remove_render_attribute( 'title_link' );
		}
		// Affiche le rendu
		echo wp_kses_post( ob_get_clean() );
	}

	/**
	 * get_settings_json()
	 *
	 * Retrieve fields values to pass at the widget container
	 * Convert on JSON format
	 *
	 * @uses      wp_json_encode()
	 * @return    JSON oject
	 * @access    protected
	 */
	protected function get_settings_json( $id ) {
		$settings    = $this->get_settings_for_display();
		$layout_type = $settings['ag_layout_type'];

		if ( 'equalHeight' === $layout_type ) {
			$layout_mode = 'fitRows';
		} elseif ( 'metro' === $layout_type ) {
			$layout_mode = 'masonry';
		} else {
			$layout_mode = $layout_type;
		}

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
			'data_id'                  => $id,
			'data_layout'              => $layout_mode,
			'data_equalheight'         => 'equalHeight' === $layout_type ? true : false,
			'data_metro'               => 'metro' === $layout_type ? true : false,
			'data_order'               => 'yes' === $settings['ag_image_order'] ? true : false,
			'data_rtl'                 => 'left' === $settings['ag_item_scroll'] ? true : false,
			'data_gutter'              => 'justify' === $layout_type ? $settings['ag_items_justify_margin']['size'] : 0,
			'data_rowheight'           => 'justify' === $layout_type ? $settings['ag_image_height']['size'] : 0,
			'data_overlay'             => $settings['ag_overlay_inout'],
			'data_fancybox'            => 'yes' === $settings['ag_image_lightbox'] ? true : false,
			'data_filtre'              => 'yes' === $settings['ag_content_filter_display'] ? true : false,
			'data_sw_swiper'           => 'slider' === $layout_type ? true : false,
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

	/**
	 * render_advanced_gallery_filter
	 *
	 * Description: Retourne les filtres formaté en HTML en ligne
	 * ou sous forme de liste pour les media query
	 */
	protected function render_advanced_gallery_filter() {
		$settings     = $this->get_settings_for_display();
		$id           = $this->get_id();
		$filters_name = array();
		$html         = '';

		foreach ( $settings['advanced_gallery'] as $image ) {
			$attachment = array();
			$image_size = $settings['ag_image_size_size'];
			$image_id   = isset( $image['id'] ) ? $image['id'] : false;
			if ( $image_id ) {
				$attachment = Eac_Helpers_Util::wp_get_attachment_data( $image_id, $image_size );
				if ( ! empty( $attachment['src'] ) && ! empty( $attachment['media_cat'] ) ) {
					$current_filters = explode( ',', $attachment['media_cat'] );
					foreach ( $current_filters as $current_filter ) {
						$filters_name[ sanitize_title( mb_strtolower( $current_filter, 'UTF-8' ) ) ] = sanitize_title( mb_strtolower( $current_filter, 'UTF-8' ) );
					}
				}
			}
		}

		// Des filtres
		if ( ! empty( $filters_name ) ) {
			ksort( $filters_name, SORT_FLAG_CASE | SORT_NATURAL );

			$html .= "<div class='ag-filters__wrapper'>";
			$html .= "<div class='ag-filters__item ag-active'><a href='#' class='eac-accessible-link' role='button' data-filter='*' aria-label='" . esc_html__( 'Filtrer les résultats par', 'eac-components' ) . ' ' . esc_html__( 'Tous', 'eac-components' ) . "'>" . esc_html__( 'Tous', 'eac-components' ) . '</a></div>';
			foreach ( $filters_name as $filter_name ) {
				$html .= "<div class='ag-filters__item'><a href='#' class='eac-accessible-link' role='button' data-filter='." . sanitize_title( $filter_name ) . "' aria-label='" . esc_html__( 'Filtrer les résultats par', 'eac-components' ) . ' ' . ucfirst( $filter_name ) . "'>" . ucfirst( $filter_name ) . '</a></div>';
			}
			$html .= '</div>';

			// Filtre dans une liste pour les media query
			$html     .= "<div class='ag-filters__wrapper-select'>";
			$html     .= "<label id='label_" . esc_attr( $id ) . "' class='visually-hidden' for='listbox_" . esc_attr( $id ) . "'>" . esc_html__( 'Filtres personnalisés', 'eac-components' ) . '</label>';
			$html     .= "<select id='listbox_" . esc_attr( $id ) . "' class='ag-filters__select' aria-labelledby='label_" . esc_attr( $id ) . "'>";
				$html .= "<option value='*' selected>" . esc_html__( 'Tous', 'eac-components' ) . '</option>';
			foreach ( $filters_name as $filter_name ) {
				$html .= "<option value='." . sanitize_title( $filter_name ) . "'>" . ucfirst( $filter_name ) . '</option>';
			}
			$html .= '</select>';
			$html .= '</div>';
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	protected function content_template() {}
}
