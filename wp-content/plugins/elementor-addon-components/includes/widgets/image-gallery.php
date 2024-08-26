<?php
/**
 * Class: Image_Galerie_Widget
 * Name: Galerie d'Images
 * Slug: eac-addon-image-galerie
 *
 * Description: Image_Galerie_Widget affiche des images dans différents modes
 * grille, mosaïque et justifiées
 *
 * selector .image-galerie__content.overlay-out {
 * position: absolute;
 * left: 0;
 * bottom: 0;
 * width: 100%;
 * height: 150px;
 * }
 *
 * @since 1.0.0
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
use Elementor\Control_Media;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Repeater;
use Elementor\Icons_Manager;
use Elementor\Modules\DynamicTags\Module as TagsModule;
use Elementor\Core\Breakpoints\Manager as Breakpoints_manager;
use Elementor\Plugin;
use Elementor\Utils;

class Image_Galerie_Widget extends Widget_Base {
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
		wp_register_script( 'eac-image-gallery', EAC_Plugin::instance()->get_script_url( 'assets/js/elementor/eac-image-gallery' ), array( 'jquery', 'elementor-frontend', 'isotope', 'fj-gallery', 'swiper', 'eac-imagesloaded' ), '1.0.0', true );

		wp_register_style( 'swiper-bundle', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.3.2/swiper-bundle.min.css', array(), '8.3.2' );
		wp_register_style( 'fj-gallery', 'https://cdnjs.cloudflare.com/ajax/libs/flickr-justified-gallery/2.2.0/fjGallery.min.css', array(), '2.2.0' );
		wp_enqueue_style( 'eac-swiper', EAC_Plugin::instance()->get_style_url( 'assets/css/eac-swiper' ), array(), '1.0.0' );
		wp_enqueue_style( 'eac-image-gallery', EAC_Plugin::instance()->get_style_url( 'assets/css/image-gallery' ), array( 'eac-swiper', 'fj-gallery' ), EAC_PLUGIN_VERSION );
	}

	/**
	 * La taille de l'image par défaut
	 *
	 * @var IMAGE_SIZE
	 */
	const IMAGE_SIZE = '300';

	/**
	 * Le nom de la clé du composant dans le fichier de configuration
	 *
	 * @var $slug
	 *
	 * @access private
	 */
	private $slug = 'image-galerie';

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
		return array( 'isotope', 'eac-imagesloaded', 'swiper', 'eac-image-gallery', 'eac-fit-rows', 'fj-gallery' );
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
			'ig_galerie_settings',
			array(
				'label' => esc_html__( 'Galerie', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$repeater = new Repeater();

			$repeater->add_control(
				'ig_item_title',
				array(
					'label'       => esc_html__( 'Titre', 'eac-components' ),
					'type'        => Controls_Manager::TEXT,
					'dynamic'     => array( 'active' => true ),
					'ai'          => array( 'active' => false ),
					'default'     => esc_html__( 'Image #', 'eac-components' ),
					'label_block' => true,
				)
			);

			$repeater->start_controls_tabs( 'ig_item_tabs_settings' );

				$repeater->start_controls_tab(
					'ig_item_image_settings',
					array(
						'label' => esc_html__( 'Image', 'eac-components' ),
					)
				);

					$repeater->add_control(
						'ig_item_image',
						array(
							'label'   => esc_html__( 'Image', 'eac-components' ),
							'type'    => Controls_Manager::MEDIA,
							'dynamic' => array( 'active' => true ),
							'default' => array(
								'url' => Utils::get_placeholder_image_src(),
							),
						)
					);

					$repeater->add_control(
						'ig_item_alt',
						array(
							'label'       => esc_html__( 'Attribut ALT', 'eac-components' ),
							'type'        => Controls_Manager::TEXT,
							'dynamic'     => array( 'active' => true ),
							'ai'          => array( 'active' => false ),
							'default'     => '',
							'description' => esc_html__( "Valoriser l'attribut 'ALT' pour une image externe (SEO)", 'eac-components' ),
							'label_block' => true,
							'render_type' => 'none',
						)
					);

				$repeater->end_controls_tab();

				$repeater->start_controls_tab(
					'ig_item_content_settings',
					array(
						'label' => esc_html__( 'Contenu', 'eac-components' ),
					)
				);

					$repeater->add_control(
						'ig_item_desc',
						array(
							'label'       => esc_html__( 'Description', 'eac-components' ),
							'type'        => Controls_Manager::TEXTAREA,
							'dynamic'     => array( 'active' => true ),
							'ai'          => array( 'active' => false ),
							'default'     => esc_html__( "Le faux-texte en imprimerie, est un texte sans signification, qui sert à calibrer le contenu d'une page...", 'eac-components' ),
							'label_block' => true,
						)
					);

					$repeater->add_control(
						'ig_item_filter',
						array(
							'label'       => esc_html__( 'Labels du filtre', 'eac-components' ),
							'type'        => Controls_Manager::TEXT,
							'dynamic'     => array(
								'active'     => true,
								'categories' => array(
									TagsModule::POST_META_CATEGORY,
								),
							),
							'ai'          => array( 'active' => false ),
							'default'     => '',
							'description' => esc_html__( 'Labels séparés par une virgule', 'eac-components' ),
							'label_block' => true,
							'render_type' => 'ui',
							'separator'   => 'before',
						)
					);

					$repeater->add_control(
						'ig_item_url',
						array(
							'label'        => esc_html__( 'Lien', 'eac-components' ),
							'type'         => Controls_Manager::URL,
							'description'  => esc_html__( 'Utiliser les balises dynamiques pour les liens internes', 'eac-components' ),
							'placeholder'  => 'http://your-link.com',
							'dynamic'      => array(
								'active' => true,
							),
							'default'      => array(
								'url' => '#',
							),
							'autocomplete' => true,
							'render_type'  => 'ui',
						)
					);

				$repeater->end_controls_tab();

			$repeater->end_controls_tabs();

			$this->add_control(
				'ig_image_list',
				array(
					'label'       => esc_html__( 'Liste des images', 'eac-components' ),
					'type'        => Controls_Manager::REPEATER,
					'fields'      => $repeater->get_controls(),
					'default'     => array(
						array(
							'ig_item_image' => array( 'url' => Utils::get_placeholder_image_src() ),
							'ig_item_title' => esc_html__( 'Image #1', 'eac-components' ),
							'ig_item_desc'  => esc_html__( "Le faux-texte en imprimerie, est un texte sans signification, qui sert à calibrer le contenu d'une page...", 'eac-components' ),
						),
						array(
							'ig_item_image' => array( 'url' => Utils::get_placeholder_image_src() ),
							'ig_item_title' => esc_html__( 'Image #2', 'eac-components' ),
							'ig_item_desc'  => esc_html__( "Le faux-texte en imprimerie, est un texte sans signification, qui sert à calibrer le contenu d'une page...", 'eac-components' ),
						),
						array(
							'ig_item_image' => array( 'url' => Utils::get_placeholder_image_src() ),
							'ig_item_title' => esc_html__( 'Image #3', 'eac-components' ),
							'ig_item_desc'  => esc_html__( "Le faux-texte en imprimerie, est un texte sans signification, qui sert à calibrer le contenu d'une page...", 'eac-components' ),
						),
						array(
							'ig_item_image' => array( 'url' => Utils::get_placeholder_image_src() ),
							'ig_item_title' => esc_html__( 'Image #4', 'eac-components' ),
							'ig_item_desc'  => esc_html__( "Le faux-texte en imprimerie, est un texte sans signification, qui sert à calibrer le contenu d'une page...", 'eac-components' ),
						),
						array(
							'ig_item_image' => array( 'url' => Utils::get_placeholder_image_src() ),
							'ig_item_title' => esc_html__( 'Image #5', 'eac-components' ),
							'ig_item_desc'  => esc_html__( "Le faux-texte en imprimerie, est un texte sans signification, qui sert à calibrer le contenu d'une page...", 'eac-components' ),
						),
						array(
							'ig_item_image' => array( 'url' => Utils::get_placeholder_image_src() ),
							'ig_item_title' => esc_html__( 'Image #6', 'eac-components' ),
							'ig_item_desc'  => esc_html__( "Le faux-texte en imprimerie, est un texte sans signification, qui sert à calibrer le contenu d'une page...", 'eac-components' ),
						),
					),
					'title_field' => '{{{ ig_item_title }}}',
					'button_text' => esc_html__( 'Ajouter une image', 'eac-components' ),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ig_layout_type_settings',
			array(
				'label' => esc_html__( 'Réglages', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'ig_layout_content',
				array(
					'label'     => esc_html__( 'Disposition', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
				)
			);

			$this->add_control(
				'ig_layout_type',
				array(
					'label'   => esc_html__( 'Mode', 'eac-components' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'masonry',
					'options' => array(
						'masonry'     => esc_html__( 'Mosaïque', 'eac-components' ),
						'equalHeight' => esc_html__( 'Grille', 'eac-components' ),
						'justify'     => esc_html__( 'Justifier', 'eac-components' ),
						'slider'      => esc_html( 'Slider' ),
					),
				)
			);

			$this->add_responsive_control(
				'ig_columns',
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
					'condition'    => array( 'ig_layout_type!' => array( 'justify', 'slider' ) ),
				)
			);

			$this->add_control(
				'ig_layout_type_metro',
				array(
					'label'        => esc_html__( 'Activer le mode Metro', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'description'  => esc_html__( 'Est appliqué uniquement à la première image', 'eac-components' ),
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => array( 'ig_layout_type' => 'masonry' ),
				)
			);

			$this->add_control(
				'ig_image_settings',
				array(
					'label'     => esc_html__( 'Image', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'ig_image_size',
				array(
					'label'   => esc_html__( 'Dimension', 'eac-components' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'medium',
					'options' => array(
						'thumbnail'    => esc_html__( 'Miniature', 'eac-components' ),
						'medium'       => esc_html__( 'Moyenne', 'eac-components' ),
						'medium_large' => esc_html__( 'Moyenne-large', 'eac-components' ),
						'large'        => esc_html__( 'Large', 'eac-components' ),
						'full'         => esc_html__( 'Originale', 'eac-components' ),
					),
				)
			);

			$this->add_control(
				'ig_image_lazy',
				array(
					'label'        => esc_html( 'Lazy load' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$this->add_responsive_control(
				'ig_image_height',
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
						'ig_layout_type' => 'justify',
					),
				)
			);

			$this->add_control(
				'ig_enable_image_ratio',
				array(
					'label'        => esc_html__( 'Activer le ratio image', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
					'condition'    => array( 'ig_layout_type' => array( 'equalHeight', 'fitRows' ) ),
				)
			);

			$this->add_responsive_control(
				'ig_image_new_ratio',
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
					'selectors'      => array( '{{WRAPPER}} .image-galerie .image-galerie__image-instance' => 'aspect-ratio:{{SIZE}};' ),
					'render_type'    => 'template',
					'condition'      => array(
						'ig_layout_type'        => array( 'equalHeight', 'fitRows' ),
						'ig_enable_image_ratio' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ig_image_ratio_position_y',
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
					'selectors'  => array( '{{WRAPPER}} .image-galerie .image-galerie__image-instance' => 'object-position: 50% {{SIZE}}%;' ),
					'condition'  => array(
						'ig_layout_type'        => array( 'equalHeight', 'fitRows' ),
						'ig_enable_image_ratio' => 'yes',
					),
				)
			);

			$this->add_control(
				'ig_links_overlay',
				array(
					'label'     => esc_html__( 'Disposition du contenu', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'ig_overlay_inout',
				array(
					'label'     => esc_html__( 'Affichage du contenu', 'eac-components' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'overlay-out',
					'options'   => array(
						'overlay-out' => esc_html__( 'Carte ', 'eac-components' ),
						'overlay-in'  => esc_html__( 'Superposer', 'eac-components' ),
					),
					'condition' => array( 'ig_layout_type!' => 'justify' ),
				)
			);

			$this->add_responsive_control(
				'ig_overlay_inout_align_v',
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
					'default'     => 'center',
					'label_block' => true,
					'selectors'   => array(
						'{{WRAPPER}} .image-galerie__content.overlay-out .image-galerie__overlay' => 'justify-content: {{VALUE}};',
					),
					'condition'   => array(
						'ig_layout_type'   => array( 'equalHeight', 'fitRows', 'slider' ),
						'ig_overlay_inout' => 'overlay-out',
					),
				)
			);

			$this->add_responsive_control(
				'ig_overlay_inout_align_h',
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
						'{{WRAPPER}} .image-galerie__content.overlay-out .image-galerie__overlay,
						{{WRAPPER}} .swiper-container .image-galerie__content.overlay-out .image-galerie__overlay' => 'align-items: {{VALUE}};',
						'{{WRAPPER}} .image-galerie__content.overlay-out .image-galerie__description-wrapper' => 'text-align: {{VALUE}};',
					),
					'condition'   => array(
						'ig_layout_type!'   => 'justify',
						'ig_overlay_inout' => 'overlay-out',
					),
				)
			);

			$this->add_control(
				'ig_overlay_direction',
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
					'prefix_class' => 'overlay-',
					'conditions'   => array(
						'relation' => 'or',
						'terms'    => array(
							array(
								'terms' => array(
									array(
										'name'     => 'ig_layout_type',
										'operator' => '===',
										'value'    => 'justify',
									),
								),
							),
							array(
								'terms' => array(
									array(
										'name'     => 'ig_overlay_inout',
										'operator' => '===',
										'value'    => 'overlay-in',
									),
								),
							),
						),
					),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ig_slider_settings',
			array(
				'label'     => 'Slider',
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'ig_layout_type' => 'slider' ),
			)
		);

			$this->register_slider_content_controls();

		$this->end_controls_section();

		$this->start_controls_section(
			'ig_gallery_content',
			array(
				'label' => esc_html__( 'Contenu', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'ig_filter_heading',
				array(
					'label'     => esc_html__( 'Filtres', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'condition' => array( 'ig_layout_type!' => array( 'justify', 'slider' ) ),
				)
			);

			$this->add_control(
				'ig_content_filter_display',
				array(
					'label'        => esc_html__( 'Afficher les filtres', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => array( 'ig_layout_type!' => array( 'justify', 'slider' ) ),
				)
			);

			$this->add_control(
				'ig_content_filter_align',
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
					'selectors' => array(
						'{{WRAPPER}} .ig-filters__wrapper, {{WRAPPER}} .ig-filters__wrapper-select' => 'text-align: {{VALUE}};',
					),
					'condition' => array(
						'ig_content_filter_display' => 'yes',
						'ig_layout_type!'           => array( 'justify', 'slider' ),
					),
				)
			);

			$this->add_control(
				'ig_post_heading',
				array(
					'label'     => esc_html__( 'Article', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'ig_content_title',
				array(
					'label'        => esc_html__( 'Afficher le titre', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'ig_title_tag',
				array(
					'label'     => esc_html__( 'Étiquette du titre', 'eac-components' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'h2',
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
					'condition' => array( 'ig_content_title' => 'yes' ),
				)
			);

			$this->add_control(
				'ig_content_description',
				array(
					'label'        => esc_html__( 'Afficher la description', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'ig_content_readmore',
				array(
					'label'        => esc_html__( "Bouton 'En savoir plus'", 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'ig_links_heading',
				array(
					'label'     => esc_html__( 'Liens', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'ig_image_link',
				array(
					'label'        => esc_html__( "Lien de l'article sur l'image", 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => array(
						'ig_image_lightbox!' => 'yes',
						'ig_overlay_inout'   => 'overlay-out',
						'ig_layout_type!'    => 'justify',
					),
				)
			);

			$this->add_control(
				'ig_image_lightbox',
				array(
					'label'        => esc_html__( "Visionneuse sur l'image", 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => array(
						'ig_layout_type'   => array( 'masonry', 'equalHeight', 'fitRows' ),
						'ig_overlay_inout' => 'overlay-out',
						'ig_image_link!'   => 'yes',
					),
				)
			);

			$this->add_control(
				'ig_content_title_link',
				array(
					'label'        => esc_html__( "Lien de l'article sur le titre", 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => array( 'ig_content_title' => 'yes' ),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ig_readmore_settings',
			array(
				'label'     => esc_html__( "Bouton 'En savoir plus'", 'eac-components' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'ig_content_readmore' => 'yes' ),
			)
		);

			// Trait du contenu du bouton read more
			$this->register_button_more_content_controls();

		$this->end_controls_section();

		/**
		 * Generale Style Section
		 */
		$this->start_controls_section(
			'ig_section_general_style',
			array(
				'label' => esc_html__( 'Général', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'ig_img_style',
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
					'prefix_class' => 'image-galerie_wrapper-',
				)
			);

			$this->add_responsive_control(
				'ig_items_margin',
				array(
					'label'      => esc_html__( 'Marge entre les images', 'eac-components' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( 'px' ),
					'default'    => array(
						'size' => 5,
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
						'{{WRAPPER}} .image-galerie__inner-wrapper' => 'margin: {{SIZE}}{{UNIT}}; height: calc(100% - 2 * {{SIZE}}{{UNIT}});',
						'{{WRAPPER}} .swiper-container .swiper-slide .image-galerie__inner-wrapper' => 'height: calc(100% - (2 * {{SIZE}}{{UNIT}}));',
					),
					'condition'  => array( 'ig_layout_type!' => 'justify' ),
				)
			);

			$this->add_responsive_control(
				'ig_items_justify_margin',
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
						'{{WRAPPER}} .image-galerie.layout-type-justify.fj-gallery' => 'padding: calc({{SIZE}}{{UNIT}} / 2);',
					),
					'condition'      => array( 'ig_layout_type' => 'justify' ),
				)
			);

			$this->add_control(
				'ig_container_style_bgcolor',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'selectors' => array( '{{WRAPPER}} .swiper-container .swiper-slide, {{WRAPPER}} .image-galerie' => 'background-color: {{VALUE}};' ),
				)
			);

			/** Articles */
			$this->add_control(
				'ig_items_style',
				array(
					'label'     => esc_html__( 'Articles', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'ig_overlay_inout' => 'overlay-out' ),
				)
			);

			$this->add_control(
				'ig_items_bg_color',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'selectors' => array( '{{WRAPPER}} .image-galerie__inner-wrapper, {{WRAPPER}} .image-galerie__content.overlay-out' => 'background-color: {{VALUE}};' ),
					'condition' => array( 'ig_overlay_inout' => 'overlay-out' ),
				)
			);

			$this->add_control(
				'ig_filter_style',
				array(
					'label'     => esc_html__( 'Filtre', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array(
						'ig_layout_type!'           => array( 'justify', 'slider' ),
						'ig_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_control(
				'ig_filter_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'selectors' => array(
						'{{WRAPPER}} .ig-filters__wrapper .ig-filters__item, {{WRAPPER}} .ig-filters__wrapper .ig-filters__item a' => 'color: {{VALUE}};',
					),
					'condition' => array(
						'ig_layout_type!'           => array( 'justify', 'slider' ),
						'ig_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'ig_filter_typography',
					'label'     => esc_html__( 'Typographie', 'eac-components' ),
					'global'    => array( 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ),
					'selector'  => '{{WRAPPER}} .ig-filters__wrapper .ig-filters__item, {{WRAPPER}} .ig-filters__wrapper .ig-filters__item a',
					'condition' => array(
						'ig_layout_type!'           => array( 'justify', 'slider' ),
						'ig_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_control(
				'ig_filter_background',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_SECONDARY ),
					'selectors' => array( '{{WRAPPER}} .ig-filters__wrapper .ig-filters__item a' => 'background-color: {{VALUE}};' ),
					'condition' => array(
						'ig_layout_type!'           => array( 'justify', 'slider' ),
						'ig_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_control(
				'ig_filter_outline',
				array(
					'label'     => esc_html__( 'Couleur de la bordure', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_SECONDARY ),
					'selectors' => array( '{{WRAPPER}} .ig-filters__wrapper .ig-filters__item.ig-active:after' => 'border-bottom: 3px solid {{VALUE}};' ),
					'condition' => array(
						'ig_layout_type!'           => array( 'justify', 'slider' ),
						'ig_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ig_filter_padding',
				array(
					'label'     => esc_html__( 'Marges internes', 'eac-components' ),
					'type'      => Controls_Manager::DIMENSIONS,
					'selectors' => array(
						'{{WRAPPER}} .ig-filters__wrapper .ig-filters__item a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition' => array(
						'ig_layout_type!'           => array( 'justify', 'slider' ),
						'ig_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'      => 'ig_filter_border',
					'selector'  => '{{WRAPPER}} .ig-filters__wrapper .ig-filters__item a',
					'condition' => array(
						'ig_layout_type!'           => array( 'justify', 'slider' ),
						'ig_content_filter_display' => 'yes',
					),
				)
			);

			$this->add_control(
				'ig_filter_radius',
				array(
					'label'              => esc_html__( 'Rayon de la bordure', 'eac-components' ),
					'type'               => Controls_Manager::DIMENSIONS,
					'size_units'         => array( 'px', '%' ),
					'allowed_dimensions' => array( 'top', 'right', 'bottom', 'left' ),
					'selectors'          => array(
						'{{WRAPPER}} .ig-filters__wrapper .ig-filters__item a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'          => array(
						'ig_layout_type!'           => array( 'justify', 'slider' ),
						'ig_content_filter_display' => 'yes',
					),
				)
			);

			/** Image */
			$this->add_control(
				'ig_image_section_style',
				array(
					'label'     => esc_html__( 'Image', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => 'ig_image_border',
					'selector' => '{{WRAPPER}} .image-galerie__image img',
				)
			);

			$this->add_control(
				'ig_image_border_radius',
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
						'{{WRAPPER}} .image-galerie__image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			/** Titre */
			$this->add_control(
				'ig_titre_section_style',
				array(
					'label'     => esc_html__( 'Titre', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'ig_content_title' => 'yes' ),
				)
			);

			$this->add_control(
				'ig_titre_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'default'   => '#919CA7',
					'selectors' => array(
						'{{WRAPPER}} .image-galerie__item .image-galerie__content .image-galerie__overlay .image-galerie__titre-wrapper,
						{{WRAPPER}} .image-galerie__item .image-galerie__content .image-galerie__overlay .image-galerie__titre' => 'color: {{VALUE}};',
					),
					'condition' => array( 'ig_content_title' => 'yes' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'ig_titre_typography',
					'label'     => esc_html__( 'Typographie', 'eac-components' ),
					'global'    => array( 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ),
					'selector'  => '{{WRAPPER}} .image-galerie__item .image-galerie__content .image-galerie__overlay .image-galerie__titre-wrapper,
									{{WRAPPER}} .image-galerie__item .image-galerie__content .image-galerie__overlay .image-galerie__titre',
					'condition' => array( 'ig_content_title' => 'yes' ),
				)
			);

			$this->add_control(
				'ig_texte_section_style',
				array(
					'label'     => esc_html__( 'Description', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'ig_content_description' => 'yes' ),
				)
			);

			$this->add_control(
				'ig_texte_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_TEXT ),
					'selectors' => array( '{{WRAPPER}} .image-galerie__item .image-galerie__content .image-galerie__overlay .image-galerie__description-wrapper' => 'color: {{VALUE}};' ),
					'condition' => array( 'ig_content_description' => 'yes' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'ig_texte_typography',
					'label'     => esc_html__( 'Typographie', 'eac-components' ),
					'global'    => array( 'default' => Global_Typography::TYPOGRAPHY_TEXT ),
					'selector'  => '{{WRAPPER}} .image-galerie__item .image-galerie__content .image-galerie__overlay .image-galerie__description-wrapper',
					'condition' => array( 'ig_content_description' => 'yes' ),
				)
			);

			$this->add_control(
				'ig_texte_align',
				array(
					'label'     => esc_html__( 'Alignement', 'eac-components' ),
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
					'default'   => 'left',
					'selectors' => array(
						'{{WRAPPER}} .image-galerie__item .image-galerie__description-wrapper' => 'text-align: {{VALUE}};',
					),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'ig_slider_section_style',
			array(
				'label'      => esc_html__( 'Contrôles du slider', 'eac-components' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'     => 'ig_layout_type',
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
									'name'     => 'ig_layout_type',
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
			'ig_readmore_style',
			array(
				'label'     => esc_html__( "Bouton 'En savoir plus'", 'eac-components' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'ig_content_readmore' => 'yes' ),
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
		if ( ! $settings['ig_image_list'] || empty( $settings['ig_image_list'] ) ) {
			return;
		}

		$id             = 'image_galerie_' . $this->get_id();
		$slider_id      = 'slider_image_galerie_' . $this->get_id();
		$has_swiper     = 'slider' === $settings['ig_layout_type'] ? true : false;
		$has_filters    = ! $has_swiper && 'yes' === $settings['ig_content_filter_display'] ? true : false;
		$has_navigation = $has_swiper && 'yes' === $settings['slider_navigation'] ? true : false;
		$has_pagination = $has_swiper && 'yes' === $settings['slider_pagination'] ? true : false;
		$has_scrollbar  = $has_swiper && 'yes' === $settings['slider_scrollbar'] ? true : false;
		$layout_type    = 'equalHeight' === $settings['ig_layout_type'] ? 'fitRows' : $settings['ig_layout_type'];

		if ( ! $has_swiper ) {
			if ( 'justify' === $layout_type ) {
				$class = 'image-galerie layout-type-justify fj-gallery';
			} else {
				$class = sprintf( 'image-galerie layout-type-%s', $layout_type );
			}
		} else {
			$class = 'image-galerie swiper-wrapper';
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
			<div id="<?php echo esc_attr( $slider_id ); ?>" class='eac-image-galerie swiper-container'>
		<?php } else { ?>
			<div class='eac-image-galerie'>
		<?php }
		if ( $has_filters ) {
			$this->render_image_gallery_filter();
		}
		?>
			<div <?php $this->print_render_attribute_string( 'gallery_instance' ); ?>>
				<?php if ( ! $has_swiper ) { ?>
					<div class='image-galerie__item-sizer'></div>
				<?php }
				$this->render_galerie(); ?>
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
	protected function render_galerie() {
		$settings = $this->get_settings_for_display();

		/** Variable du rendu final */
		$html = '';

		/** ID de l'article */
		$unique_id = uniqid();

		$layout_type = $settings['ig_layout_type'];

		/** Le swiper est actif */
		$has_swiper = 'slider' === $layout_type ? true : false;

		$title_tag = ! empty( $settings['ig_title_tag'] ) ? Utils::validate_html_tag( $settings['ig_title_tag'] ) : 'div';

		/** L'image */
		$has_image = true;
		$lazy_load = 'yes' === $settings['ig_image_lazy'] ? 'lazy' : 'eager';

		/** Visionneuse active mais pas avec le slider */
		$has_image_lightbox = ! $has_swiper && isset( $settings['ig_image_lightbox'] ) && 'yes' === $settings['ig_image_lightbox'] ? true : false;

		/** Lien sur l'image */
		$has_image_link = ! $has_image_lightbox && 'yes' === $settings['ig_image_link'] ? true : false;

		/** Le titre */
		$has_title      = 'yes' === $settings['ig_content_title'] ? true : false;
		$has_title_link = $has_title && 'yes' === $settings['ig_content_title_link'] ? true : false;

		/** La description */
		$has_description = 'yes' === $settings['ig_content_description'] ? true : false;

		/** Le bouton read more */
		$has_button_readmore = 'yes' === $settings['ig_content_readmore'] ? true : false;
		$has_readmore_picto  = $has_button_readmore && 'yes' === $settings['button_add_more_picto'] ? true : false;

		/** Filtres */
		$has_filters = ! $has_swiper && 'yes' === $settings['ig_content_filter_display'] ? true : false;

		/** Overlay layout == justify, overlay interne par défaut */
		if ( 'justify' === $layout_type ) {
			$overlay = 'overlay-in';
		} elseif ( ! isset( $settings['ig_overlay_inout'] ) ) {
			$overlay = '';
		} else {
			$overlay = $settings['ig_overlay_inout'];
		}

		/** La classe du contenu de l'item, image+titre+texte */
		$this->add_render_attribute( 'gallery_inner', 'class', 'image-galerie__inner-wrapper' );

		/** La classe du titre/texte/boutons */
		$this->add_render_attribute( 'gallery_content', 'class', esc_attr( 'image-galerie__content ' . $overlay ) );
		if ( 'overlay-in' === $overlay ) {
			$this->add_render_attribute( 'gallery_content', 'tabindex', '0' ); // Accessibilité
		}

		/** Boucle sur tous les items */
		ob_start();
		foreach ( $settings['ig_image_list'] as $index => $item ) {
			$attachment = array();
			/** Le titre de l'item */
			$item_title = $settings['ig_image_list'][ $index ]['ig_item_title'];

			/** Il y a une image */
			if ( $has_image && ! empty( $item['ig_item_image']['url'] ) ) {
				if ( ! empty( $item['ig_item_image']['id'] ) ) {
					$attachment = Eac_Helpers_Util::wp_get_attachment_data( $item['ig_item_image']['id'], $settings['ig_image_size'] );
				} else { // Image avec Url externe
					$attachment['src']    = $item['ig_item_image']['url'];
					$attachment['width']  = self::IMAGE_SIZE;
					$attachment['height'] = self::IMAGE_SIZE;
					$attachment['alt']    = ! empty( $item['ig_item_alt'] ) ? 'Image ' . esc_html( $item['ig_item_alt'] ) : 'Image ' . esc_html( $item_title );
				}
			}

			if ( ! $attachment || empty( $attachment ) ) {
				continue;
			}

			/** Le titre, la description sont éditables en ligne PAS sûre */
			$image_list_titre_key = $this->get_repeater_setting_key( 'ig_item_title', 'ig_image_list', $index );
			$image_list_desc_key  = $this->get_repeater_setting_key( 'ig_item_desc', 'ig_image_list', $index );
			$this->add_render_attribute( $image_list_titre_key, 'class', 'image-galerie__titre-wrapper' );
			$this->add_render_attribute( $image_list_desc_key, 'class', 'image-galerie__description-wrapper' );
			$this->add_inline_editing_attributes( $image_list_titre_key, 'none' );
			$this->add_inline_editing_attributes( $image_list_desc_key, 'advanced' );

			/** Les filtres */
			if ( $has_filters && ! empty( $item['ig_item_filter'] ) ) {
				$sanized = array();
				$filters = explode( ',', $item['ig_item_filter'] );
				foreach ( $filters as $filter ) {
					$sanized[] = sanitize_title( mb_strtolower( $filter, 'UTF-8' ) );
				}
				$this->add_render_attribute( 'gallery_item', 'class', 'image-galerie__item ' . implode( ' ', $sanized ) );
			} else {
				if ( 'justify' === $layout_type ) {
					$this->add_render_attribute( 'gallery_item', 'class', 'image-galerie__item fj-gallery-item' );
				} elseif ( 'slider' === $layout_type ) {
					$this->add_render_attribute( 'gallery_item', 'class', 'image-galerie__item swiper-slide' );
				} else {
					$this->add_render_attribute( 'gallery_item', 'class', 'image-galerie__item' );
				}
			}

			/** Un lien avec l'item */
			$item_link = ! empty( $item['ig_item_url']['url'] ) && '#' !== $item['ig_item_url']['url'] ? $item['ig_item_url']['url'] : false;
			if ( $item_link ) {
				$this->add_link_attributes( 'attributes_link', $item['ig_item_url'] );
				if ( $item['ig_item_url']['is_external'] ) {
					$this->add_render_attribute( 'attributes_link', 'rel', 'noopener noreferrer' );
				}
			}
			?>
			<article <?php $this->print_render_attribute_string( 'gallery_item' ); ?>>
			<div <?php $this->print_render_attribute_string( 'gallery_inner' ); ?>>

			<?php
			$this->add_render_attribute( 'gallery_image', 'class', 'img-focusable image-galerie__image-instance' );
			$this->add_render_attribute( 'gallery_image', 'src', esc_url( $attachment['src'] ) );
			$this->add_render_attribute( 'gallery_image', 'width', esc_attr( $attachment['width'] ) );
			$this->add_render_attribute( 'gallery_image', 'height', esc_attr( $attachment['height'] ) );
			$this->add_render_attribute( 'gallery_image', 'alt', esc_attr( $attachment['alt'] ) );
			if ( isset( $attachment['srcset'] ) ) {
				$this->add_render_attribute( 'gallery_image', 'srcset', esc_attr( $attachment['srcset'] ) );
				$this->add_render_attribute( 'gallery_image', 'sizes', esc_attr( $attachment['srcsize'] ) );
			}
			if ( 'eager' === $lazy_load ) {
				$this->add_render_attribute( 'gallery_image', 'loading', $lazy_load );
			}

			if ( 'overlay-out' === $overlay ) {
				$this->add_render_attribute( 'image_link', 'class', 'eac-accessible-link' );
				if ( $has_image_link && $item_link ) {
					$this->add_render_attribute( 'image_link', 'href', esc_url( $item['ig_item_url']['url'] ) );
					$this->add_render_attribute( 'image_link', 'aria-label', esc_html__( "Voir l'article", 'eac-components' ) . ' ' . esc_html( $item_title ) );
				} elseif ( $has_image_lightbox ) {
					$this->add_render_attribute( 'image_link', 'href', esc_url( $attachment['src'] ) );
					$this->add_render_attribute( 'image_link', 'data-elementor-open-lightbox', 'no' );
					$this->add_render_attribute( 'image_link', 'data-fancybox', esc_attr( $unique_id ) );
					$this->add_render_attribute( 'image_link', 'data-caption', esc_html( ucfirst( $item_title ) ) );
					$this->add_render_attribute( 'image_link', 'aria-label', esc_html__( "Voir l'image", 'eac-components' ) . ' ' . esc_html( ucfirst( $item_title ) ) );
				}
			}
			?>
			<!--Affiche l'image-->
			<div class='image-galerie__image'>
				<?php if ( 'overlay-out' === $overlay && ( ( $has_image_link && $item_link ) || $has_image_lightbox ) ) { ?>
					<a <?php $this->print_render_attribute_string( 'image_link' ); ?>>
				<?php } ?>
					<img <?php $this->print_render_attribute_string( 'gallery_image' ); ?>>
				<?php if ( 'overlay-out' === $overlay && ( ( $has_image_link && $item_link ) || $has_image_lightbox ) ) { ?>
					</a>
				<?php } ?>
			</div>

			<?php if ( $has_title || $has_description || ( $item_link && ! $has_image_link ) ) { ?>
				<div <?php $this->print_render_attribute_string( 'gallery_content' ); ?>>
				<div class='image-galerie__overlay'>

				<?php if ( $has_title ) {
					/** Formate le titre */
					$title_with_tag = '<' . $title_tag . ' class="image-galerie__titre">' . esc_html( ucfirst( $item_title ) ) . '</' . $title_tag . '>';
					if ( $item_link && $has_title_link ) { ?>
						<a class='eac-accessible-link' <?php $this->print_render_attribute_string( 'attributes_link' ); ?>>
							<div <?php $this->print_render_attribute_string( $image_list_titre_key ); ?>><?php echo $title_with_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						</a>
					<?php } else { ?>
						<div <?php $this->print_render_attribute_string( $image_list_titre_key ); ?>><?php echo $title_with_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php }
				}

				if ( $has_description ) { ?>
					<div <?php $this->print_render_attribute_string( $image_list_desc_key ); ?>><?php echo wp_kses_post( sanitize_textarea_field( $item['ig_item_desc'] ) ); ?></div>
				<?php }

				if ( $item_link && $has_button_readmore ) {
					$this->add_render_attribute( 'attributes_link', 'class', 'button-readmore' );
					$this->add_render_attribute( 'attributes_link', 'role', 'button' );
					$this->add_render_attribute( 'attributes_link', 'aria-label', sanitize_text_field( $settings['button_more_label'] ) . ' ' . esc_html( $item_title ) );
					?>
					<div class='buttons-wrapper'>
						<span class='button__readmore-wrapper'>
							<a <?php $this->print_render_attribute_string( 'attributes_link' ); ?>>
								<?php if ( $has_readmore_picto && 'before' === $settings['button_more_position'] ) {
									ob_start();
									Icons_Manager::render_icon( $settings['button_more_picto'], array( 'aria-hidden' => 'true' ) );
									echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
									echo esc_html( sanitize_text_field( $settings['button_more_label'] ) );
								if ( $has_readmore_picto && 'after' === $settings['button_more_position'] ) {
									ob_start();
									Icons_Manager::render_icon( $settings['button_more_picto'], array( 'aria-hidden' => 'true' ) );
									echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								} ?>
							</a>
						</span>
					</div>
				<?php } ?>
				</div> <!-- galerie__overlay -->
				</div> <!-- gallery_content -->
			<?php } ?>
			</div> <!-- gallery_inner -->
			</article> <!-- gallery_item -->
			<?php
			$this->remove_render_attribute( 'gallery_image' );
			$this->remove_render_attribute( 'image_link' );
			$this->remove_render_attribute( 'attributes_link' );
			$this->remove_render_attribute( 'gallery_item' );
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
		$layout_type = 'equalHeight' === $settings['ig_layout_type'] ? 'fitRows' : $settings['ig_layout_type'];

		if ( 'justify' === $layout_type ) {
			$overlay = 'overlay-in';
		} elseif ( ! isset( $settings['ig_overlay_inout'] ) ) {
			$overlay = '';
		} else {
			$overlay = $settings['ig_overlay_inout'];
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
			'data_layout'              => $layout_type,
			'data_equalheight'         => in_array( $settings['ig_layout_type'], array( 'equalHeight', 'fitRows' ), true ) ? true : false,
			'data_overlay'             => $overlay,
			'data_gutter'              => 'justify' === $layout_type ? $settings['ig_items_justify_margin']['size'] : 0,
			'data_rowheight'           => 'justify' === $layout_type ? $settings['ig_image_height']['size'] : 0,
			'data_fancybox'            => 'yes' === $settings['ig_image_lightbox'] ? true : false,
			'data_metro'               => 'yes' === $settings['ig_layout_type_metro'] ? true : false,
			'data_filtre'              => 'yes' === $settings['ig_content_filter_display'] ? true : false,
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
	 * render_image_gallery_filter
	 *
	 * Description: Retourne les filtres formaté en HTML en ligne
	 * ou sous forme de liste pour les media query
	 */
	protected function render_image_gallery_filter() {
		$settings     = $this->get_settings_for_display();
		$id           = $this->get_id();
		$filters_name = array();
		$html         = '';

		foreach ( $settings['ig_image_list'] as $item ) {
			if ( ! empty( $item['ig_item_image']['url'] ) && ! empty( $item['ig_item_filter'] ) ) {
				$current_filters = explode( ',', $item['ig_item_filter'] );
				foreach ( $current_filters as $current_filter ) {
					$filters_name[ sanitize_title( mb_strtolower( $current_filter, 'UTF-8' ) ) ] = sanitize_title( mb_strtolower( $current_filter, 'UTF-8' ) );
				}
			}
		}

		// Des filtres
		if ( ! empty( $filters_name ) ) {
			ksort( $filters_name, SORT_FLAG_CASE | SORT_NATURAL );

			$html .= "<div class='ig-filters__wrapper'>";
			$html .= "<div class='ig-filters__item ig-active'><a href='#' class='eac-accessible-link' role='button' data-filter='*' aria-label='" . esc_html__( 'Filtrer les résultats par', 'eac-components' ) . ' ' . esc_html__( 'Tous', 'eac-components' ) . "'>" . esc_html__( 'Tous', 'eac-components' ) . '</a></div>';
			foreach ( $filters_name as $filter_name ) {
				$html .= "<div class='ig-filters__item'><a href='#' class='eac-accessible-link' role='button' data-filter='." . sanitize_title( $filter_name ) . "' aria-label='" . esc_html__( 'Filtrer les résultats par', 'eac-components' ) . ' ' . ucfirst( $filter_name ) . "'>" . ucfirst( $filter_name ) . '</a></div>';
			}
			$html .= '</div>';

			// Filtre dans une liste pour les media query
			$html     .= "<div class='ig-filters__wrapper-select'>";
			$html     .= "<label id='label_" . esc_attr( $id ) . "' class='visually-hidden' for='listbox_" . esc_attr( $id ) . "'>" . esc_html__( 'Filtres personnalisés', 'eac-components' ) . '</label>';
			$html     .= "<select id='listbox_" . esc_attr( $id ) . "' class='ig-filters__select' aria-labelledby='label_" . esc_attr( $id ) . "'>";
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
