<?php
/**
 * Class: Breadcrumb_Widget
 * Slug: eac-addon-breadcrumbs
 *
 * Description: Ajoute un breadcrumb
 *
 * @since 2.1.1
 */

namespace EACCustomWidgets\Includes\TemplatesLib\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\Includes\TemplatesLib\Widgets\Classes\Breadcrumb_Trail;
use EACCustomWidgets\Core\Eac_Config_Elements;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Icons_Manager;
use Elementor\Utils;

class Breadcrumb_Widget extends Widget_Base {

	/**
	 * Constructeur de la class
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		require_once __DIR__ . '/classes/class-breadcrumb.php';
	}

	/**
	 * Le nom de la clé du composant dans le fichier de configuration
	 *
	 * @var $slug
	 *
	 * @access private
	 */
	private $slug = 'breadcrumbs';

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
		return array( '' );
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
			'bdc_settings',
			array(
				'label' => esc_html__( 'Réglages', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'bdc_home_title',
				array(
					'label'       => esc_html__( "Libellé de la page d'accueil", 'eac-components' ),
					'type'        => Controls_Manager::TEXT,
					'default'     => esc_html__( 'Accueil', 'eac-components' ),
					'label_block' => false,
				)
			);

			$this->add_control(
				'bdc_display_title',
				array(
					'label'   => esc_html__( 'Afficher le titre courant', 'eac-components' ),
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
				'bdc_title_length',
				array(
					'label'       => esc_html__( 'Nombre de mots', 'eac-components' ),
					'description' => esc_html__( 'Nombre de mots dans le titre. 0 = tous les mots', 'eac-components' ),
					'type'        => Controls_Manager::NUMBER,
					'min'         => 0,
					'max'         => 50,
					'step'        => 1,
					'default'     => 0,
					'condition'   => array( 'bdc_display_title' => 'yes' ),
				)
			);

			$this->add_control(
				'bdc_title_tag',
				array(
					'label'       => esc_html__( 'Étiquette des items', 'eac-components' ),
					'type'        => Controls_Manager::CHOOSE,
					'options'     => array(
						'h1'   => array(
							'title' => 'H1',
							'icon'  => 'eicon-editor-h1',
						),
						'h2'   => array(
							'title' => 'H2',
							'icon'  => 'eicon-editor-h2',
						),
						'h3'   => array(
							'title' => 'H3',
							'icon'  => 'eicon-editor-h3',
						),
						'h4'   => array(
							'title' => 'H4',
							'icon'  => 'eicon-editor-h4',
						),
						'h5'   => array(
							'title' => 'H5',
							'icon'  => 'eicon-editor-h5',
						),
						'h6'   => array(
							'title' => 'H6',
							'icon'  => 'eicon-editor-h6',
						),
						'span' => array(
							'title' => esc_html__( 'Paragraphe', 'eac-components' ),
							'icon'  => 'eicon-editor-paragraph',
						),
					),
					'default'     => 'span',
					'toggle'      => false,
					'label_block' => true,
				)
			);

			$this->add_responsive_control(
				'bdc_alignment',
				array(
					'label'     => esc_html__( 'Alignement', 'eac-components' ),
					'type'      => Controls_Manager::CHOOSE,
					'options'   => array(
						'flex-start' => array(
							'title' => esc_html__( 'Gauche', 'eac-components' ),
							'icon'  => 'eicon-h-align-left',
						),
						'center'     => array(
							'title' => esc_html__( 'Centre', 'eac-components' ),
							'icon'  => 'eicon-h-align-center',
						),
						'flex-end'   => array(
							'title' => esc_html__( 'Droit', 'eac-components' ),
							'icon'  => 'eicon-h-align-right',
						),
					),
					'default'   => 'flex-start',
					'separator' => 'before',
					'selectors' => array(
						'{{WRAPPER}} .eac-breadcrumbs' => 'justify-content: {{VALUE}}',
					),
				)
			);

			$this->add_control(
				'dbc_icon_separator',
				array(
					'label'                  => esc_html__( 'Séparateur', 'eac-components' ),
					'type'                   => Controls_Manager::ICONS,
					'default'                => array(
						'value'   => 'fas fa-angle-right',
						'library' => 'solid',
					),
					'label_block'            => 'true',
					'skin'                   => 'inline',
					'exclude_inline_options' => array( 'svg' ),
				)
			);

		$this->end_controls_section();

		/**
		 * Generale Style Section
		 */
		$this->start_controls_section(
			'bdc_style',
			array(
				'label' => esc_html__( "Fil d'ariane", 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'bdc_bg_color',
				array(
					'label'     => esc_html__( 'Couleur du fond', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_PRIMARY ),
					'selectors' => array( '{{WRAPPER}} .eac-breadcrumbs' => 'background-color: {{VALUE}};' ),
				)
			);

			$this->add_responsive_control(
				'bdc_padding',
				array(
					'label'     => esc_html__( 'Marges internes', 'eac-components' ),
					'type'      => Controls_Manager::DIMENSIONS,
					'selectors' => array(
						'{{WRAPPER}} .eac-breadcrumbs' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'bdc_item_style',
			array(
				'label' => esc_html__( 'Items', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'bdc_item_color',
				array(
					'label'     => esc_html__( 'Couleur du texte', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_TEXT ),
					'default'   => '#000000',
					'selectors' => array(
						'{{WRAPPER}} nav .eac-breadcrumbs-item,
						{{WRAPPER}} nav .eac-breadcrumbs-item a' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'bdc_item_color_separator',
				array(
					'label'     => esc_html__( 'Couleur du séparateur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_TEXT ),
					'default'   => '#000000',
					'selectors' => array(
						'{{WRAPPER}} nav .eac-breadcrumbs-separator' => 'color: {{VALUE}};',
					),
					'condition' => array( 'dbc_icon_separator!' => '' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'           => 'bdc_item_typography',
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
					'selector'       => '{{WRAPPER}} nav .eac-breadcrumbs-item, {{WRAPPER}} nav .eac-breadcrumbs-separator',
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
		?>
		<div class='eac-breadcrumbs'>
			<nav aria-label='breadcrumbs'>
				<?php $this->render_breadcrumb(); ?>
			</nav>
		</div>
		<?php
	}

	protected function render_breadcrumb() {
		$settings          = $this->get_settings_for_display();
		$default_separator = ' ';

		if ( ! empty( $settings['dbc_icon_separator'] ) ) {
			ob_start();
			Icons_Manager::render_icon( $settings['dbc_icon_separator'], array( 'aria-hidden' => 'true' ) );
			$icon_separator = ob_get_clean();
		}

		$args = array(
			'separator'     => ! empty( $icon_separator ) ? " $icon_separator " : $default_separator,
			'item_tag'      => ! empty( $settings['bdc_title_tag'] ) ? Utils::validate_html_tag( $settings['bdc_title_tag'] ) : 'p',
			'show_title'    => 'yes' === $settings['bdc_display_title'] ? true : false,
			'trunk_title'   => isset( $settings['bdc_title_length'] ) ? absint( $settings['bdc_title_length'] ) : 0,
			'post_taxonomy' => array(
				'post' => '',
			),
			'labels'        => array(
				'home'       => ! empty( $settings['bdc_home_title'] ) ? sanitize_text_field( $settings['bdc_home_title'] ) : esc_html__( 'Accueil', 'eac-components' ),
				'page_title' => '',
			),
		);

		$breadcrumb = new Breadcrumb_Trail( $args );
		echo wp_kses_post( $breadcrumb->trail() );
	}

	protected function content_template() {}
}
