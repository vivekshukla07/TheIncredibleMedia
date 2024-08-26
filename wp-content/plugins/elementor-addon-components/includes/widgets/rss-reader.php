<?php
/**
 * Class: Lecteur_Rss_Widget
 * Name: Lecteur RSS
 * Slug: eac-addon-lecteur-rss
 *
 * Description: Lecteur_Rss_Widget affiche une liste de médias
 * qui diffuse du contenu au format RSS
 *
 * @since 1.0.0
 */

namespace EACCustomWidgets\Includes\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\EAC_Plugin;
use EACCustomWidgets\Core\Eac_Config_Elements;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Control_Media;
use Elementor\Utils;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Group_Control_Border;
use Elementor\Core\Schemes\Color;
use Elementor\Repeater;
use Elementor\Core\Breakpoints\Manager as Breakpoints_manager;
use Elementor\Plugin;

class Lecteur_Rss_Widget extends Widget_Base {
	use \EACCustomWidgets\Includes\Widgets\Traits\Button_Read_More_Trait;

	/**
	 * Constructeur de la class Lecteur_Rss_Widget
	 *
	 * Enregistre les scripts et les styles
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_script( 'eac-rss-reader', EAC_Plugin::instance()->get_script_url( 'assets/js/elementor/eac-rss-reader' ), array( 'jquery', 'elementor-frontend' ), '1.0.0', true );

		wp_register_style( 'eac-rss-reader', EAC_Plugin::instance()->get_style_url( 'assets/css/rss-reader' ), array( 'eac' ), '1.0.0' );
	}

	/**
	 * Le nom de la clé du composant dans le fichier de configuration
	 *
	 * @var $slug
	 *
	 * @access private
	 */
	private $slug = 'lecteur-rss';

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
		return array( 'eac-rss-reader' );
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
		return array( 'eac-rss-reader' );
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

		$this->start_controls_section(
			'rss_galerie_settings',
			array(
				'label' => esc_html__( 'Liste des flux RSS', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'rss_unique_instance',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'content_classes' => 'eac-editor-panel_info',
					'raw'             => __( "Atlas des flux RSS des journaux de langue Française - <a href='http://atlasflux.saynete.net/' target='_blank' rel='nofolow noopener noreferrer'>Consulter ce site</a>", 'eac-components' ),
				)
			);

			$repeater = new Repeater();

			$repeater->add_control(
				'rss_item_title',
				array(
					'label' => esc_html__( 'Titre', 'eac-components' ),
					'type'  => Controls_Manager::TEXT,
				)
			);

			$repeater->add_control(
				'rss_item_url',
				array(
					'label'       => esc_html__( 'URL', 'eac-components' ),
					'type'        => Controls_Manager::URL,
					'placeholder' => 'https://your-link.com/xml/',
				)
			);

			$this->add_control(
				'rss_image_list',
				array(
					'type'        => Controls_Manager::REPEATER,
					'fields'      => $repeater->get_controls(),
					'default'     => array(
						array(
							'rss_item_title' => 'EAC - Components feed',
							'rss_item_url'   => array( 'url' => 'https://elementor-addon-components.com/feed/' ),
						),
						array(
							'rss_item_title' => 'Reuters - News en-US',
							'rss_item_url'   => array( 'url' => 'https://news.google.com/rss/search?q=when:24h+allinurl:reuters.com&ceid=US:en&hl=en-US&gl=US' ),
						),
						array(
							'rss_item_title' => 'Nasa - Image of the day',
							'rss_item_url'   => array( 'url' => 'https://www.nasa.gov/rss/dyn/lg_image_of_the_day.rss' ),
						),
						array(
							'rss_item_title' => 'Youtube Channel - Arte cinéma',
							'rss_item_url'   => array( 'url' => 'https://www.youtube.com/feeds/videos.xml?channel_id=UClo03hULFynpoX3w1Jv7fhw' ),
						),
						array(
							'rss_item_title' => 'Youtube Channel - Euronews',
							'rss_item_url'   => array( 'url' => 'https://www.youtube.com/feeds/videos.xml?channel_id=UCW2QcKZiU8aUGg4yxCIditg' ),
						),
						array(
							'rss_item_title' => 'Youtube Channel - France Inter',
							'rss_item_url'   => array( 'url' => 'https://www.youtube.com/feeds/videos.xml?channel_id=UCJldRgT_D7Am-ErRHQZ90uw' ),
						),
						array(
							'rss_item_title' => 'Le monde',
							'rss_item_url'   => array( 'url' => 'https://www.lemonde.fr/rss/en_continu.xml' ),
						),
						array(
							'rss_item_title' => 'Le Figaro',
							'rss_item_url'   => array( 'url' => 'https://www.lefigaro.fr/rss/figaro_une.xml' ),
						),
						array(
							'rss_item_title' => "L'Express",
							'rss_item_url'   => array( 'url' => 'https://www.lexpress.fr/rss/alaune.xml' ),
						),
						array(
							'rss_item_title' => 'Courrier International',
							'rss_item_url'   => array( 'url' => 'https://www.courrierinternational.com/feed/all/rss.xml' ),
						),
						array(
							'rss_item_title' => '20 Minutes',
							'rss_item_url'   => array( 'url' => 'https://www.20minutes.fr/feeds/rss-une.xml' ),
						),
						array(
							'rss_item_title' => "L'Équipe",
							'rss_item_url'   => array( 'url' => 'https://dwh.lequipe.fr/api/edito/rss?path=/' ),
						),
						array(
							'rss_item_title' => 'France TV - Info',
							'rss_item_url'   => array( 'url' => 'https://www.francetvinfo.fr/titres.rss' ),
						),
						array(
							'rss_item_title' => 'Huffington Post',
							'rss_item_url'   => array( 'url' => 'https://www.huffingtonpost.fr/feeds/index.xml' ),
						),
						array(
							'rss_item_title' => 'BBC News - World',
							'rss_item_url'   => array( 'url' => 'https://feeds.bbci.co.uk/news/world/rss.xml' ),
						),
						array(
							'rss_item_title' => 'The Gardian - World',
							'rss_item_url'   => array( 'url' => 'https://www.theguardian.com/world/rss' ),
						),
						array(
							'rss_item_title' => 'Corriere della Sera',
							'rss_item_url'   => array( 'url' => 'https://xml2.corriereobjects.it/rss/homepage.xml' ),
						),
						array(
							'rss_item_title' => 'Die Welt',
							'rss_item_url'   => array( 'url' => 'https://www.welt.de/feeds/latest.rss' ),
						),
						array(
							'rss_item_title' => 'CNN World',
							'rss_item_url'   => array( 'url' => 'http://rss.cnn.com/rss/edition_world.rss' ),
						),
						array(
							'rss_item_title' => 'Première - Actu Cinéma',
							'rss_item_url'   => array( 'url' => 'https://www.premiere.fr/rss/actu-cinema' ),
						),
						array(
							'rss_item_title' => 'WP Marmite',
							'rss_item_url'   => array( 'url' => 'https://feedpress.me/WPMarmite' ),
						),
						array(
							'rss_item_title' => 'CSS Tips',
							'rss_item_url'   => array( 'url' => 'https://css-tip.com/feed/feed.xml' ),
						),
						array(
							'rss_item_title' => 'Smashing Magazine',
							'rss_item_url'   => array( 'url' => 'https://www.smashingmagazine.com/feed/' ),
						),
						array(
							'rss_item_title' => 'Podcast France Inter - Le 7/9',
							'rss_item_url'   => array( 'url' => 'https://radiofrance-podcast.net/podcast09/rss_10241.xml' ),
						),
						array(
							'rss_item_title' => 'Podcast France Inter - Le masque et la plume',
							'rss_item_url'   => array( 'url' => 'https://radiofrance-podcast.net/podcast09/rss_14007.xml' ),
						),
						array(
							'rss_item_title' => 'Podcast France Culture - La méthode scientifique',
							'rss_item_url'   => array( 'url' => 'https://radiofrance-podcast.net/podcast09/rss_14312.xml' ),
						),
						array(
							'rss_item_title' => 'Podcast Collège de France - Tous les podcasts',
							'rss_item_url'   => array( 'url' => 'https://podcast.college-de-france.fr/xml/general.xml' ),
						),
						array(
							'rss_item_title' => 'Collège de France - Actualités',
							'rss_item_url'   => array( 'url' => 'https://www.college-de-france.fr/news.xml' ),
						),
						array(
							'rss_item_title' => 'Podcast BBC - BBC World Service',
							'rss_item_url'   => array( 'url' => 'https://podcasts.files.bbci.co.uk/p02nq0gn.rss' ),
						),
						array(
							'rss_item_title' => 'Podcast Spanish - Learn Spanish',
							'rss_item_url'   => array( 'url' => 'https://learnrealspanish.libsyn.com/rss' ),
						),
					),
					'title_field' => '{{{ rss_item_title }}}',
					'button_text' => esc_html__( 'Ajouter un flux', 'eac-components' ),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'rss_layout_type_settings',
			array(
				'label' => esc_html__( 'Disposition', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$active_breakpoints  = Plugin::$instance->breakpoints->get_active_breakpoints();
			$columns_device_args = array();
		foreach ( $active_breakpoints as $breakpoint_name => $breakpoint_instance ) {
			if ( Breakpoints_manager::BREAKPOINT_KEY_WIDESCREEN === $breakpoint_name ) {
				$columns_device_args[ $breakpoint_name ] = array( 'default' => '4' );
			} elseif ( Breakpoints_manager::BREAKPOINT_KEY_LAPTOP === $breakpoint_name ) {
				$columns_device_args[ $breakpoint_name ] = array( 'default' => '3' );
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

			$this->add_responsive_control(
				'rss_columns',
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
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'rss_items_content',
			array(
				'label' => esc_html__( 'Contenu', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			/** Les articles */
			$this->add_control(
				'rss_item_posts',
				array(
					'label' => esc_html__( 'Article', 'eac-components' ),
					'type'  => Controls_Manager::HEADING,
				)
			);

			$this->add_control(
				'rss_item_image',
				array(
					'label'        => esc_html__( 'Image', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'rss_content_item_title',
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
				'rss_item_excerpt',
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
				'rss_item_readmore',
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
				'rss_item_date',
				array(
					'label'        => esc_html__( 'Date de Publication/Auteur', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$this->add_responsive_control(
				'rss_item_content_align_v',
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
					'selectors'   => array( '{{WRAPPER}} .rss-galerie__content-inner' => 'justify-content: {{VALUE}};' ),
				)
			);

			/** Les nombres */
			$this->add_control(
				'rss_item_numbers',
				array(
					'label'     => esc_html__( 'Densité', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'rss_item_nombre',
				array(
					'label'       => esc_html__( "Nombre d'articles", 'eac-components' ),
					'type'        => Controls_Manager::NUMBER,
					'min'         => 5,
					'max'         => 50,
					'step'        => 5,
					'default'     => 20,
				)
			);

			$this->add_control(
				'rss_item_length',
				array(
					'label'       => esc_html__( 'Nombre de mots', 'eac-components' ),
					'description' => esc_html__( 'Résumé', 'eac-components' ),
					'type'        => Controls_Manager::NUMBER,
					'min'         => 10,
					'max'         => 100,
					'step'        => 5,
					'default'     => 25,
					'render_type' => 'none',
					'condition'   => array( 'rss_item_excerpt' => 'yes' ),
				)
			);

			/** Les liens */
			$this->add_control(
				'rss_item_links',
				array(
					'label'     => esc_html__( 'Liens', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'rss_item_image_link',
				array(
					'label'        => esc_html__( "Lien de l'article sur l'image", 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => array(
						'rss_item_image'     => 'yes',
						'rss_item_lightbox!' => 'yes',
					),
				)
			);

			$this->add_control(
				'rss_item_lightbox',
				array(
					'label'        => esc_html__( 'Visionneuse', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => array(
						'rss_item_image'       => 'yes',
						'rss_item_image_link!' => 'yes',
					),
				)
			);

			$this->add_control(
				'rss_item_title_link',
				array(
					'label'        => esc_html__( "Lien de l'article sur le titre", 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'condition'    => array(
						'rss_content_item_title' => 'yes',
					),
				)
			);

			/** Le bouton */
			$this->add_control(
				'rss_item_buttons',
				array(
					'label'     => esc_html__( 'Bouton', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'rss_item_button_label',
				array(
					'label'       => esc_html__( "Label du bouton 'Lire le flux'", 'eac-components' ),
					'type'        => Controls_Manager::TEXT,
					'default'     => esc_html__( 'Lire le flux', 'eac-components' ),
					'dynamic'     => array(
						'active' => true,
					),
					'label_block' => true,
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'rss_items_image',
			array(
				'label'     => esc_html__( 'Réglages image', 'eac-components' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'rss_item_image' => 'yes' ),
			)
		);

			$this->add_control(
				'rss_item_image_height_auto',
				array(
					'label'        => esc_html__( 'Hauteur automatique', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_responsive_control(
				'rss_item_image_height',
				array(
					'label'          => esc_html__( "Hauteur de l'image", 'eac-components' ),
					'type'           => Controls_Manager::SLIDER,
					'size_units'     => array( 'px' ),
					'default'        => array(
						'size' => 200,
						'unit' => 'px',
					),
					'laptop_default' => array(
						'size' => 200,
						'unit' => 'px',
					),
					'tablet_default' => array(
						'size' => 200,
						'unit' => 'px',
					),
					'mobile_default' => array(
						'size' => 150,
						'unit' => 'px',
					),
					'range'          => array(
						'px' => array(
							'min'  => 0,
							'max'  => 500,
							'step' => 50,
						),
					),
					'selectors'      => array( '{{WRAPPER}} .rss-galerie__item-image img' => 'height: {{SIZE}}{{UNIT}};' ),
					'condition'      => array( 'rss_item_image_height_auto!' => 'yes' ),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'rss_more_settings',
			array(
				'label'     => esc_html__( "Bouton 'En savoir plus'", 'eac-components' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'rss_item_readmore' => 'yes' ),
			)
		);

			// Trait du contenu du bouton read more
			$this->register_button_more_content_controls();

		$this->end_controls_section();

		$this->start_controls_section(
			'rss_general_style',
			array(
				'label' => esc_html__( 'Général', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'rss_wrapper_style',
				array(
					'label'        => esc_html__( 'Style', 'eac-components' ),
					'type'         => Controls_Manager::SELECT,
					'default'      => 'style-1',
					'options'      => array(
						'style-0' => esc_html__( 'Défaut', 'eac-components' ),
						'style-1' => 'Style 1',
						'style-2' => 'Style 2',
						'style-3' => 'Style 3',
						'style-4' => 'Style 4',
						'style-5' => 'Style 5',
						'style-6' => 'Style 6',
						'style-7' => 'Style 7',
						'style-8' => 'Style 8',
						'style-9' => 'Style 9',
					),
					'prefix_class' => 'rss-galerie_wrapper-',
				)
			);

			$this->add_control(
				'rss_wrapper_bg_color',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( '{{WRAPPER}} .eac-rss-galerie' => 'background-color: {{VALUE}};' ),
				)
			);

			$this->add_responsive_control(
				'rss_wrapper_gap',
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
						'{{WRAPPER}} .rss-galerie' => 'gap: {{SIZE}}{{UNIT}}; padding: calc({{SIZE}}{{UNIT}} / 2);',
					),
				)
			);

			/** Articles */
			$this->add_control(
				'rss_items_style',
				array(
					'label'     => esc_html__( 'Articles', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'rss_items_bg_color',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( '{{WRAPPER}} .rss-galerie__item' => 'background-color: {{VALUE}};' ),
				)
			);

			$this->add_control(
				'rss_image_style',
				array(
					'label'     => esc_html__( 'Image', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'rss_item_image' => 'yes' ),
				)
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'      => 'rss_image_border',
					'selector'  => '{{WRAPPER}} .rss-galerie__item-image img',
					'condition' => array( 'rss_item_image' => 'yes' ),
				)
			);

			$this->add_control(
				'rss_image_border_radius',
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
						'{{WRAPPER}} .rss-galerie__item-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition' => array( 'rss_item_image' => 'yes' ),
				)
			);

			/** Titre */
			$this->add_control(
				'rss_title_style',
				array(
					'label'     => esc_html__( 'Titre', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'rss_content_item_title' => 'yes' ),
				)
			);

			$this->add_control(
				'rss_titre_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( '{{WRAPPER}} .rss-galerie__item-titre' => 'color: {{VALUE}};' ),
					'condition' => array( 'rss_content_item_title' => 'yes' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'rss_titre_typography',
					'label'    => esc_html__( 'Typographie', 'eac-components' ),
					'scheme'   => Typography::TYPOGRAPHY_4,
					'selector' => '{{WRAPPER}} .rss-galerie__item-titre',
					'condition' => array( 'rss_content_item_title' => 'yes' ),
				)
			);

			/** Résumé */
			$this->add_control(
				'rss_excerpt_style',
				array(
					'label'     => esc_html__( 'Résumé', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'rss_item_excerpt' => 'yes' ),
				)
			);

			$this->add_control(
				'rss_excerpt_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( '{{WRAPPER}} .rss-galerie__item-description p' => 'color: {{VALUE}};' ),
					'condition' => array( 'rss_item_excerpt' => 'yes' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'rss_excerpt_typography',
					'label'    => esc_html__( 'Typographie', 'eac-components' ),
					'scheme'   => Typography::TYPOGRAPHY_4,
					'selector' => '{{WRAPPER}} .rss-galerie__item-description p',
					'condition' => array( 'rss_item_excerpt' => 'yes' ),
				)
			);

			/** Pictogrammes */
			$this->add_control(
				'rss_icone_style',
				array(
					'label'     => esc_html__( 'Pictogrammes', 'eac-components' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( 'rss_item_date' => 'yes' ),
				)
			);

			$this->add_control(
				'rss_icone_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .rss-galerie__item-date i,
						{{WRAPPER}} .rss-galerie__item-auteur i,
						{{WRAPPER}} .rss-galerie__item-date,
						{{WRAPPER}} .rss-galerie__item-auteur' => 'color: {{VALUE}};',
					),
					'condition' => array( 'rss_item_date' => 'yes' ),
				)
			);
		$this->end_controls_section();

		$this->start_controls_section(
			'rss_more_style',
			array(
				'label'     => esc_html__( "Bouton 'En savoir plus'", 'eac-components' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'rss_item_readmore' => 'yes' ),
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
		if ( ! $settings['rss_image_list'] ) {
			return;
		}
		$this->add_render_attribute( 'rss_galerie', 'class', 'rss-galerie' );
		$this->add_render_attribute( 'rss_galerie', 'id', 'rss-galerie' );
		$this->add_render_attribute( 'rss_galerie', 'role', 'feed' );
		$this->add_render_attribute( 'rss_galerie', 'aria-busy', 'false' );
		$this->add_render_attribute( 'rss_galerie', 'data-settings', $this->get_settings_json() );
		?>
		<div class="eac-rss-galerie">
			<input type="hidden" id="rss_nonce" name="rss_nonce" value="<?php echo wp_create_nonce( 'eac_rss_feed_' . $this->get_id() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" />
			<?php $this->render_galerie(); ?>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'rss_galerie' ) ); ?>></div>
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
		$id       = $this->get_id();
		?>
		<div class="rss-select__item-list">
			<div class="rss-options__items-list">
				<label id="label_<?php echo esc_attr( $id ); ?>" class="visually-hidden" for="listbox_<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Liste des flux RSS', 'eac-components' ); ?></label>
				<select id="listbox_<?php echo esc_attr( $id ); ?>" class="select__options-items" aria-labelledby="label_<?php echo esc_attr( $id ); ?>">
					<?php foreach ( $settings['rss_image_list'] as $item ) { ?>
						<?php if ( ! empty( $item['rss_item_url']['url'] ) ) : ?>
							<option value="<?php echo esc_url( $item['rss_item_url']['url'] ); ?>"><?php echo esc_html( sanitize_text_field( $item['rss_item_title'] ) ); ?></option>
						<?php endif; ?>
					<?php } ?>
				</select>
			</div>
			<div class="eac__button">
				<button id="rss__read-button" class="eac__read-button" type="button" aria-expanded="false" aria-controls="rss-galerie" aria-label="<?php echo esc_html( sanitize_text_field( $settings['rss_item_button_label'] ) ) . ' ' . esc_html__( 'Afficher le contenu du flux sélectionné', 'eac-components' ); ?>">
					<?php echo esc_html( sanitize_text_field( $settings['rss_item_button_label'] ) ); ?>
				</button>
			</div>
			<div id="rss__loader-wheel" class="eac__loader-spin"></div>
		</div>
		<div class="rss-item__header"></div>
		<?php
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
	 */

	protected function get_settings_json() {
		$settings = $this->get_settings_for_display();

		$module_settings = array(
			'data_id'             => $this->get_id(),
			'data_nombre'         => ! empty( $settings['rss_item_nombre'] ) ? $settings['rss_item_nombre'] : 20,
			'data_date'           => 'yes' === $settings['rss_item_date'] ? true : false,
			'data_img'            => 'yes' === $settings['rss_item_image'] ? true : false,
			'data_lightbox'       => 'yes' === $settings['rss_item_lightbox'] ? true : false,
			'data_image_link'     => 'yes' === $settings['rss_item_image_link'] ? true : false,
			'data_title'          => 'yes' === $settings['rss_content_item_title'] ? true : false,
			'data_title_link'     => 'yes' === $settings['rss_item_title_link'] ? true : false,
			'data_excerpt'        => 'yes' === $settings['rss_item_excerpt'] ? true : false,
			'data_excerpt_lenght' => 'yes' === $settings['rss_item_excerpt'] && ! empty( $settings['rss_item_length'] ) ? $settings['rss_item_length'] : 20,
			'data_readmore'       => 'yes' === $settings['rss_item_readmore'] ? true : false,
			'data_readmore_label' => 'yes' === $settings['rss_item_readmore'] ? sanitize_text_field( $settings['button_more_label'] ) : '',
			'data_icon'           => isset( $settings['button_more_picto'] ) ? $settings['button_more_picto']['value'] : '',
			'data_icon_pos'       => isset( $settings['button_more_position'] ) ? $settings['button_more_position'] : '',
		);

		return wp_json_encode( $module_settings );
	}

	protected function content_template() {}
}
