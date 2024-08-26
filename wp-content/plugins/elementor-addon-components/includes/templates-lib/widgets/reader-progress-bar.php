<?php
/**
 * Class: Reader_Progress_Bar_Widget
 * Slug: eac-addon-reader-progress
 *
 * Description: Ajoute une barre de progression à la lecture du contenu
 *
 * @since 2.1.1
 */

namespace EACCustomWidgets\Includes\TemplatesLib\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\EAC_Plugin;
use EACCustomWidgets\Core\Eac_Config_Elements;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

class Reader_Progress_Bar_Widget extends Widget_Base {

	/**
	 * Constructeur de la class
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_script( 'eac-reader-progress', EAC_Plugin::instance()->get_script_url( 'includes/templates-lib/assets/js/reader-progress' ), array( 'jquery', 'elementor-frontend' ), '2.1.1', true );
	}

	/**
	 * Le nom de la clé du composant dans le fichier de configuration
	 *
	 * @var $slug
	 *
	 * @access private
	 */
	private $slug = 'reader-progress';

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
		return array( 'eac-reader-progress' );
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
			'rpb_settings',
			array(
				'label' => esc_html__( 'Réglages', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'rpb_content_height',
				array(
					'label'       => esc_html__( 'Hauteur', 'eac-components' ),
					'type'        => Controls_Manager::NUMBER,
					'min'         => 2,
					'max'         => 50,
					'step'        => 1,
					'default'     => 15,
					'render_type' => 'none',
					'selectors'   => array(
						'{{WRAPPER}} .progress' => 'height: {{SIZE}}px;',
					),
				)
			);

			$this->add_control(
				'rpb_content_badge',
				array(
					'label'   => esc_html__( 'Ajouter un badge', 'eac-components' ),
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
				)
			);

			$this->add_control(
				'rpb_content_badge_position',
				array(
					'label'       => esc_html__( 'Position du badge', 'eac-components' ),
					'type'        => Controls_Manager::CHOOSE,
					'options'     => array(
						'flex-start' => array(
							'title' => esc_html__( 'Gauche', 'eac-components' ),
							'icon'  => 'eicon-text-align-left',
						),
						'center'     => array(
							'title' => esc_html__( 'Centre', 'eac-components' ),
							'icon'  => 'eicon-text-align-center',
						),
						'flex-end'   => array(
							'title' => esc_html__( 'Droite', 'eac-components' ),
							'icon'  => 'eicon-text-align-right',
						),
					),
					'default'     => 'center',
					'label_block' => true,
					'selectors'   => array(
						'{{WRAPPER}}.progress-left .eac-reader-progress .progress,
						{{WRAPPER}}.progress-right .eac-reader-progress .progress' => 'justify-content: {{VALUE}};',
					),
					'condition'   => array( 'rpb_content_badge' => 'yes' ),
				)
			);

			$this->add_control(
				'rpb_content_rtl',
				array(
					'label'        => esc_html__( "Direction de l'affichage", 'eac-components' ),
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
					'default'      => 'right',
					'toggle'       => false,
					'prefix_class' => 'progress-',
					'render_type'  => 'template',
				)
			);

		$this->end_controls_section();

		/**
		 * Generale Style Section
		 */
		$this->start_controls_section(
			'rpb_style_barre',
			array(
				'label' => esc_html__( 'Barre de progression', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_group_control(
				Group_Control_Background::get_type(),
				array(
					'name'           => 'rpb_bg',
					'types'          => array( 'classic', 'gradient' ),
					'fields_options' => array(
						'color'          => array(
							'default' => '#84fab0',
						),
						'color_b'        => array(
							'default' => '#8fd3f4',
						),
						'gradient_type'  => array(
							'default' => 'linear',
						),
						'gradient_angle' => array(
							'default' => array(
								'unit' => 'deg',
								'size' => 120,
							),
						),
					),
					'exclude'        => array( 'image' ),
					'selector'       => '{{WRAPPER}} .progress',
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'rpb_style_badge',
			array(
				'label'     => esc_html__( 'Badge', 'eac-components' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'rpb_content_badge' => 'yes' ),
			)
		);

			$this->add_control(
				'rpb_style_badge_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array( 'default' => Global_Colors::COLOR_TEXT ),
					'default'   => '#000',
					'selectors' => array(
						'{{WRAPPER}} .progress-badge' => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'           => 'rpb_style_badge_typography',
					'label'          => esc_html__( 'Typographie', 'eac-components' ),
					'global'         => array( 'default' => Global_Typography::TYPOGRAPHY_PRIMARY ),
					'fields_options' => array(
						'font_size'   => array(
							'default' => array(
								'unit' => 'px',
								'size' => 12,
							),
						),
						'font_weight' => array(
							'default' => 600,
						),
					),
					'selector'       => '{{WRAPPER}} .progress-badge',
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
		$settings = $this->get_settings_for_display();
		?>
		<div class='eac-reader-progress'>
			<div class='progress' role='progressbar' aria-label="<?php esc_html_e( 'Barre de progression de lecture', 'eac-components' ); ?>" aria-valuemin='0' aria-valuemax='100' aria-valuenow='0'>
				<?php if ( isset( $settings['rpb_content_badge'] ) && 'yes' === $settings['rpb_content_badge'] ) { ?>
					<span class='progress-badge'></span>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	protected function content_template() {}
}
