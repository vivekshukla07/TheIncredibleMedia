<?php
/**
 * Class: Eac_Custom_Css
 *
 * Link: https://gist.github.com/iqbalrony/a989af18478b5c423530c67a78e1c5bc
 *
 * Description: Implémentation des 'controls' et des méthodes du composant ACE 'Custom CSS'
 *
 * @since 1.6.0
 */

namespace EACCustomWidgets\Includes\Elementor\CustomCss;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EACCustomWidgets\EAC_Plugin;

use Elementor\Controls_Manager;
use Elementor\Element_Base;
use Elementor\Core\Files\CSS\Post;
use Elementor\Core\DynamicTags\Dynamic_CSS;

class Eac_Custom_Css {
	/**
	 * Constructeur de la class
	 */
	public function __construct() {
		add_action( 'elementor/element/after_section_end', array( __CLASS__, 'add_controls_section' ), 10, 2 );
		add_action( 'elementor/element/parse_css', array( $this, 'add_post_css' ), 10, 2 );
		add_action( 'elementor/css-file/post/parse', array( $this, 'add_page_settings_css' ) );

		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ) );
	}

	/**
	 * enqueue_editor_scripts
	 *
	 * Enqueue le script pour l'éditeur de CSS personnalisé
	 */
	public function enqueue_editor_scripts() {
		wp_enqueue_script( 'eac-custom-css', EAC_Plugin::instance()->get_script_url( 'assets/js/elementor/eac-custom-css' ), array( 'jquery' ), '1.6.0', true );
		wp_enqueue_script( 'eac-indicator-css', EAC_Plugin::instance()->get_script_url( 'assets/js/elementor/eac-indicator-css' ), array(), '2.1.8', true );
	}

	/**
	 * add_controls_section
	 *
	 * Remplace le control Custom CSS de la version PRO
	 *
	 * @param $element    Controls_Stack
	 * @param $section_id string id de la section
	 *
	 * Elementor\Core\Kits\Documents\Tabs\settings-custom-css.php. get_id() === 'settings-custom-css'
	 * plugins\pro-elements\modules\custom-css\module.php
	 */
	public static function add_controls_section( $element, $section_id ) {

		if ( 'section_custom_css_pro' === $section_id ) {
			$section_pro = \Elementor\Plugin::$instance->controls_manager->get_control_from_stack( $element->get_unique_name(), $section_id );

			/** Ajouter 'settings-custom-css' dans le array pour le déploiement du global CSS */
			if ( ! is_wp_error( $section_pro ) && in_array( $section_pro['tab'], array( 'advanced', 'settings-custom-css' ), true ) ) {
				\Elementor\Plugin::$instance->controls_manager->remove_control_from_stack( $element->get_unique_name(), array( $section_id, 'custom_css_pro' ) );

				$element->start_controls_section(
					'eac_custom_element_css',
					array(
						'label' => esc_html__( 'EAC CSS personnalisé', 'eac-components' ),
						'tab' => $section_pro['tab'],
					)
				);

				$element->add_control(
					'custom_css',
					array(
						'type'        => Controls_Manager::CODE,
						'label'       => esc_html__( 'Ajoutez votre propre CSS', 'eac-components' ),
						'language'    => 'css',
						'render_type' => 'ui',
						'separator'   => 'none',
						'description' => sprintf(
							/* translators: 1: Link opening tag, 2: Link opening tag, 3: Link closing tag. */
							esc_html__( 'Personnaliser le contenu avec %1$svotre CSS personnalisé%3$s et utiliser %2$sle mot-clé%3$s "selector" pour cibler des éléments particuliers.', 'eac-components' ),
							'<a href="https://elementor-addon-components.com/elementor-custom-css/" target="_blank" rel="noopener noreferrer">',
							'<a href="https://elementor-addon-components.com/elementor-custom-css/#use-the-selector-keyword-to-target-an-element" target="_blank" rel="noopener noreferrer">',
							'</a>'
						),
					)
				);

				$element->end_controls_section();
			}
		}
	}

	/**
	 * add_post_css
	 *
	 * @param $post_css Post
	 * @param $element  Element_Base
	 */
	public function add_post_css( $post_css, $element ) {
		if ( $post_css instanceof Dynamic_CSS ) {
			return;
		}

		$element_settings = $element->get_settings();

		if ( empty( $element_settings['custom_css'] ) ) {
			return;
		}

		$css = trim( $element_settings['custom_css'] );

		if ( empty( $css ) ) {
			return;
		}
		$css = str_replace( 'selector', $post_css->get_element_unique_selector( $element ), $css );

		// Ajoute un commentaire dans le CSS
		$css = sprintf( '/* Start custom CSS for %s, class: %s */', $element->get_name(), $element->get_unique_selector() ) . $css . '/* End custom CSS */';

		$post_css->get_stylesheet()->add_raw_css( $css );
	}

	/**
	 * add_page_settings_css
	 *
	 * @param $post_css Post
	 */
	public function add_page_settings_css( $post_css ) {
		$document   = \Elementor\Plugin::$instance->documents->get( $post_css->get_post_id() );
		$custom_css = $document->get_settings( 'custom_css' );

		/** Fix: PHP 8.1.22 fonction 'trim' paramètre est nul */
		if ( empty( $custom_css ) ) {
			return;
		}

		$custom_css = trim( $custom_css );
		$custom_css = str_replace( 'selector', $document->get_css_wrapper_selector(), $custom_css );

		// Ajout d'un commentaire dans la CSS
		$custom_css = '/* Start custom CSS for page-settings */' . $custom_css . '/* End custom CSS */';

		$post_css->get_stylesheet()->add_raw_css( $custom_css );
	}
} new Eac_Custom_Css();
