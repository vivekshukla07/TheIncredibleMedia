<?php
/**
 * Class: Eac_Injection_Widget_Lottie
 *
 * Description: Injecte la section et les controls dans les Colonnes
 * après la section 'Motion effects' sous l'onglet 'Advanced'
 *
 * @since 1.9.3
 */

namespace EACCustomWidgets\Includes\Elementor\Injection;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EACCustomWidgets\EAC_Plugin;
use EACCustomWidgets\Core\Eac_Config_Elements;

use Elementor\Controls_Manager;
use Elementor\Element_Base;

class Eac_Injection_Widget_Lottie {

	/**
	 * @var $target_elements
	 *
	 * La liste des éléments cibles
	 */
	private $target_elements = array( 'column', 'container' );

	/**
	 * Constructeur de la class
	 */
	public function __construct() {
		add_action( 'elementor/element/after_section_end', array( $this, 'inject_section' ), 10, 3 );

		add_filter( 'elementor/column/print_template', array( $this, 'print_template' ), 10, 2 );
		add_filter( 'elementor/container/print_template', array( $this, 'print_template' ), 10, 2 );

		add_action( 'elementor/frontend/column/before_render', array( $this, 'render_lottie' ) );
		add_action( 'elementor/frontend/container/before_render', array( $this, 'render_lottie' ) );

		/** Chargement de jQuery */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_jquery' ) );
		add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * enqueue_scripts_jquery
	 *
	 * Check si Jquery est chargé sinon charge la lib
	 */
	public function enqueue_scripts_jquery() {
		if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery' );
		}
	}

	/**
	 * enqueue_scripts
	 *
	 * Mets le script dans le file
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'lottie-animation', 'https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.8.1/lottie.min.js', array(), '5.8.1', true );

		wp_enqueue_script( 'eac-lottie-anim', EAC_Plugin::instance()->get_script_url( 'assets/js/elementor/eac-lottie-animations' ), array( 'jquery', 'elementor-frontend', 'lottie-animation' ), '1.9.3', true );
		wp_enqueue_style( 'eac-lottie-anim', EAC_Plugin::instance()->get_style_url( 'assets/css/lottie-animations' ), array( 'eac' ), '1.9.3' );
	}

	/**
	 * inject_section
	 *
	 * Inject le control après la section 'section_effects' Advanced tab
	 * pour les colonnes
	 *
	 * @param Element_Base $element    The edited element.
	 * @param String       $section_id L'ID de la section
	 * @param array        $args       Section arguments.
	 */
	public function inject_section( $element, $section_id, $args ) {

		if ( ! $element instanceof Element_Base ) {
			return;
		}

		if ( 'section_effects' === $section_id && in_array( $element->get_name(), $this->target_elements, true ) ) {

			$element->start_controls_section(
				'eac_custom_element_lottie',
				array(
					'label' => esc_html__( 'EAC Lottie background', 'eac-components' ),
					'tab'   => Controls_Manager::TAB_ADVANCED,
				)
			);

				$element->add_control(
					'eac_element_lottie',
					array(
						'label'        => esc_html__( 'Activer Lottie', 'eac-components' ),
						'type'         => Controls_Manager::SWITCHER,
						'label_on'     => esc_html__( 'oui', 'eac-components' ),
						'label_off'    => esc_html__( 'non', 'eac-components' ),
						'return_value' => 'yes',
						'default'      => '',
					)
				);

				$element->add_control(
					'eac_element_lottie_source',
					array(
						'label'     => esc_html__( 'Origine', 'eac-components' ),
						'type'      => Controls_Manager::CHOOSE,
						'options'   => array(
							'file' => array(
								'title' => esc_html__( 'Fichier média', 'eac-components' ),
								'icon'  => 'eicon-document-file',
							),
							'url'  => array(
								'title' => esc_html__( 'URL', 'eac-components' ),
								'icon'  => 'eicon-editor-link',
							),
						),
						'default'   => 'file',
						'condition' => array( 'eac_element_lottie' => 'yes' ),
					)
				);

				$element->add_control(
					'eac_element_lottie_media_file',
					array(
						'label'        => esc_html__( 'Sélectionner le fichier', 'eac-components' ),
						'type'         => 'FILE_VIEWER',
						'library_type' => array( 'application/json' ), // propriété utilisée par le script 'eac-file-viewer-control.js'
						'description'  => esc_html__( 'Sélectionner le fichier de la librairie des médias', 'eac-components' ),
						'condition'    => array(
							'eac_element_lottie'        => 'yes',
							'eac_element_lottie_source' => 'file',
						),
					)
				);

			if ( Eac_Config_Elements::is_feature_active( 'unfiltered-medias' ) ) {
				$element->add_control(
					'eac_element_lottie_media_url',
					array(
						'label'       => esc_html__( 'URL', 'eac-components' ),
						'type'        => Controls_Manager::URL,
						'description' => __( "Obtenez l'URL de l'animation <a href='https://lottiefiles.com/' target='_blank' rel='nofollow noopener noreferrer'>ici</a>", 'eac-components' ),
						'placeholder' => 'https://lottiefiles.com/anim.json/',
						'dynamic'     => array(
							'active' => true,
						),
						'condition'   => array(
							'eac_element_lottie'        => 'yes',
							'eac_element_lottie_source' => 'url',
						),
					)
				);
			} else {
				$element->add_control(
					'eac_element_lottie_media_url_info',
					array(
						'type'            => Controls_Manager::RAW_HTML,
						'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
						'raw'             => esc_html__( 'Activer la fonctionnalité "Télécharger les fichiers non filtrés" pour lire un flux JSON', 'eac-components' ),
						'condition'       => array( 'eac_element_lottie_source' => 'url' ),
					)
				);
			}

				$element->add_control(
					'eac_element_lottie_loop',
					array(
						'label'       => esc_html__( 'Lire en boucle', 'eac-components' ),
						'type'        => Controls_Manager::CHOOSE,
						'options'     => array(
							'yes' => array(
								'title' => esc_html__( 'Boucle', 'eac-components' ),
								'icon'  => 'fas fa-check',
							),
							'no'  => array(
								'title' => esc_html__( 'Une fois', 'eac-components' ),
								'icon'  => 'fas fa-ban',
							),
						),
						'default'     => 'yes',
						'toggle'      => false,
						'render_type' => 'template',
						'separator'   => 'before',
						'condition'   => array( 'eac_element_lottie' => 'yes' ),
					)
				);

				$element->add_control(
					'eac_element_lottie_speed',
					array(
						'label'     => esc_html__( 'Vitesse', 'eac-components' ),
						'type'      => Controls_Manager::NUMBER,
						'default'   => 1,
						'min'       => 0.1,
						'max'       => 3,
						'step'      => 0.1,
						'condition' => array( 'eac_element_lottie' => 'yes' ),
					)
				);

				/*$element->add_control(
					'eac_element_lottie_rotate',
					array(
						'label'     => esc_html__( 'Rotation', 'eac-components' ),
						'type'      => Controls_Manager::SLIDER,
						'default'   => array(
							'size' => 0,
							'unit' => 'px',
						),
						'range'     => array(
							'px' => array(
								'min'  => -180,
								'max'  => 180,
								'step' => 10,
							),
						),
						'selectors' => array( '{{WRAPPER}} .lottie-anim_wrapper.lottie-anim_wrapper-bg' => 'transform: rotate({{SIZE}}deg);' ),
						'condition' => array( 'eac_element_lottie' => 'yes' ),
					)
				);*/

				$element->add_control(
					'eac_element_lottie_viewport',
					array(
						'label'       => esc_html__( 'Activer dans la fenêtre', 'eac-components' ),
						'description' => esc_html__( 'Active uniquement dans la partie visible de la fenêtre', 'eac-components' ),
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
						'default'     => 'no',
						'separator'   => 'before',
						'condition'   => array( 'eac_element_lottie' => 'yes' ),
					)
				);

				/** Ajout de la class 'lottie-anim_wrapper-bg' puiqu'un widget Lottie peut être dans la colonne */
				$element->add_control(
					'eac_element_lottie_opacity',
					array(
						'label'     => esc_html__( 'Opacité', 'eac-components' ),
						'type'      => Controls_Manager::SLIDER,
						'default'   => array( 'size' => 1 ),
						'range'     => array(
							'px' => array(
								'max'  => 1,
								'min'  => 0.1,
								'step' => 0.1,
							),
						),
						'selectors' => array( '{{WRAPPER}} .lottie-anim_wrapper.lottie-anim_wrapper-bg' => 'opacity: {{SIZE}};' ),
						'condition' => array( 'eac_element_lottie' => 'yes' ),
					)
				);

			$element->end_controls_section();
		}
	}

	/**
	 * render_lottie
	 *
	 * Modifie l'objet avant le rendu en frontend
	 *
	 * @param $element  Element_Base
	 */
	public function render_lottie( $element ) {
		$settings = $element->get_settings_for_display();

		if ( ! in_array( $element->get_name(), $this->target_elements, true ) ) {
			return;
		}

		/**
		 * Le control existe et il est renseigné
		 * La fonctionnalité des médias est active
		 */
		if ( isset( $settings['eac_element_lottie'] ) && 'yes' === $settings['eac_element_lottie'] ) {
			$url = '';

			if ( 'file' === $settings['eac_element_lottie_source'] ) {
				$url = $settings['eac_element_lottie_media_file'];
			} elseif ( Eac_Config_Elements::is_feature_active( 'unfiltered-medias' ) && 'url' === $settings['eac_element_lottie_source'] ) {
				$url = $settings['eac_element_lottie_media_url']['url'];
			}
			$viewp    = 'yes' === $settings['eac_element_lottie_viewport'] ? 'viewport' : 'none';
			$autoplay = 'yes' === $settings['eac_element_lottie_viewport'] ? 'false' : 'true';
			$loop     = 'yes' === $settings['eac_element_lottie_loop'] ? 'true' : 'false';
			$speed    = ! empty( $settings['eac_element_lottie_speed'] ) ? $settings['eac_element_lottie_speed'] : '1';

			if ( empty( $url ) ) {
				return;
			}

			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery(".elementor-element-<?php echo esc_attr( $element->get_id() ); ?>").prepend("<div class='lottie-anim_wrapper lottie-anim_wrapper-bg' role='img' data-src='<?php echo esc_url( $url ); ?>' data-autoplay='<?php echo esc_attr( $autoplay ); ?>' data-loop='<?php echo esc_attr( $loop ); ?>' data-speed='<?php echo esc_attr( $speed ); ?>' data-reverse='1' data-renderer='svg' data-trigger='<?php echo esc_attr( $viewp ); ?>' data-elem-id='<?php echo esc_attr( $element->get_id() ); ?>' data-name='lottie_<?php echo esc_attr( $element->get_id() ); ?>' style='position: absolute; top: 0; left: 0; right: 0; bottom: 0; min-height: 50px;'></div>");
				});
			</script>
			<?php
		}
	}

	/**
	 * print_template
	 *
	 * Modifie l'objet avant le rendu dans l'éditeur
	 *
	 * @param $element  Element_Base
	 */
	public function print_template( $template, $element ) {

		if ( ! in_array( $element->get_name(), $this->target_elements, true ) ) {
			return $template; }

		$old_template = $template;
		ob_start();
		?>

		<#
		if(settings.eac_element_lottie && 'yes' === settings.eac_element_lottie) {
			var url = 'file' === settings.eac_element_lottie_source ? settings.eac_element_lottie_media_file : settings.eac_element_lottie_media_url.url;
			var elemId = view.getID();
			var lottieName = 'lottie_' + view.getID();
			var viewp = 'yes' === settings.eac_element_lottie_viewport ? 'viewport' : 'none';
			var autoplay = 'yes' === settings.eac_element_lottie_viewport ? 'false' : 'true';
			var loop = 'yes' === settings.eac_element_lottie_loop ? 'true' : 'false';
			var speed = settings.eac_element_lottie_speed;
		#>

			<div class="lottie-anim_wrapper lottie-anim_wrapper-bg" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; min-height: 50px;"
			data-src="{{ url }}"
			data-autoplay="{{ autoplay }}"
			data-loop="{{ loop }}"
			data-speed="{{ speed }}"
			data-reverse="1"
			data-renderer="svg"
			data-trigger="{{ viewp }}"
			data-elem-id="{{ elemId }}"
			data-name="{{ lottieName }}"></div>
		<# } #>
		<?php
		$lottie_content = ob_get_clean();
		$template       = $lottie_content . $old_template;
		return $template;
	}

} new Eac_Injection_Widget_Lottie();
