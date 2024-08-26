<?php
/**
 * Class: Eac_Injection_Image_Alt
 *
 * Description: Injecte un champ text pour valoriser l'attribut ALT d'une image
 * lorsque le fonctionnalité 'External image' des Dynamiques Tags est utilisée
 *
 * @since 1.6.3 Image widget
 */

namespace EACCustomWidgets\Includes\Elementor\Injection;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Element_Base;

class Eac_Injection_Image_Alt {

	/**
	 * Constructeur de la class
	 */
	public function __construct() {
		add_action( 'elementor/element/image/section_image/before_section_end', array( $this, 'add_image_custom_injection' ), 10, 2 );
		add_action( 'elementor/element/eac-addon-modal-box/mb_param_trigger/before_section_end', array( $this, 'add_modalbox_custom_injection' ), 10, 2 );
		add_action( 'elementor/element/image-box/section_image/before_section_end', array( $this, 'add_image_custom_injection' ), 10, 2 );

		// Action render content
		add_action( 'elementor/widget/render_content', array( $this, 'add_image_custom_attribute' ), 10, 2 );
		add_action( 'elementor/container/render_content', array( $this, 'add_image_custom_attribute' ), 10, 2 );
	}

	/**
	 * add_modalbox_custom_injection
	 *
	 * Inject le control TEXT pour valoriser l'attribut ALT dans le widget 'eac-addon-modal-box'
	 * notamment lorsque le dynamic tag 'External image' est sélectionné
	 *
	 * @param Element_Base $element The edited element.
	 * @param array        $args Section arguments.
	 */
	public function add_modalbox_custom_injection( $element, $args ) {

		// start injection of control before an other control
		$element->start_injection(
			array(
				'at' => 'after',
				'of' => 'mb_display_image',
			)
		);
			// Ajoute le control Attribut ALT
			$element->add_control(
				'image_link_alt',
				array(
					'label'       => esc_html__( 'Attribut ALT', 'eac-components' ),
					'type'        => Controls_Manager::TEXT,
					'ai'          => array( 'active' => false ),
					'default'     => '',
					'description' => esc_html__( "Valoriser l'attribut 'ALT' pour une image externe (SEO)", 'eac-components' ),
					'label_block' => true,
					'render_type' => 'none',
					'condition'   => array( 'mb_origin_trigger' => 'image' ),
				)
			);

		// end injection just after
		$element->end_injection();
	}

	/**
	 * add_image_custom_injection
	 *
	 * Inject le control TEXT pour valoriser l'attribut ALT dans le widget 'image'
	 * notamment lorsque le dynamic tag 'External image' est sélectionné
	 *
	 * @param Element_Base $element The edited element.
	 * @param array        $args Section arguments.
	 */
	public function add_image_custom_injection( $element, $args ) {

		// start injection of control before an other control
		$element->start_injection(
			array(
				'at' => 'after',
				'of' => 'image',
			)
		);
			// Ajoute le control Attribut ALT
			$element->add_control(
				'image_link_alt',
				array(
					'label'       => esc_html__( 'Attribut ALT', 'eac-components' ),
					'type'        => Controls_Manager::TEXT,
					'default'     => '',
					'description' => esc_html__( "Valoriser l'attribut 'ALT' pour une image externe (SEO)", 'eac-components' ),
					'label_block' => true,
					'render_type' => 'none',
				)
			);

		// end injection just after
		$element->end_injection();
	}

	/**
	 * add_image_custom_attribute
	 *
	 * Modifie l'attribut ALT du widget 'image'
	 *
	 * @param HTML Content
	 * @param Widget settingsy
	 */
	public function add_image_custom_attribute( $content, $widget ) {
		// Wiget image, image-box ou Modalbox
		if ( 'image' === $widget->get_name() || 'image-box' === $widget->get_name() || 'eac-addon-modal-box' === $widget->get_name() ) {

			// get widget image setting
			$settings = $widget->get_settings_for_display();

			// Le champ n'est pas vide et l'attribut ALT est renseigné
			if ( isset( $settings['image_link_alt'] ) && ! empty( $settings['image_link_alt'] ) ) {
				$content_attr_alt = str_replace( 'alt=""', 'alt="' . sanitize_text_field( $settings['image_link_alt'] ) . '"', $content );
				$content          = $content_attr_alt;
			}
		}
		// Retourne le contenu modifié ou original
		return $content;
	}
}
new Eac_Injection_Image_Alt();
