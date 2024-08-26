<?php
/**
 * Class: Ajax_Select2_Control
 * Type: eac-select2
 *
 * Description:
 *
 * @since 1.9.8
 */

namespace EACCustomWidgets\Includes\Elementor\Controls;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EACCustomWidgets\EAC_Plugin;

use Elementor\Control_Select2;

class Ajax_Select2_Control extends Control_Select2 {

	/**
	 * Get control type.
	 *
	 * Retrieve the control type, in this case 'eac-select2'.
	 *
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'eac-select2';
	}

	/**
	 * Enqueue control scripts and styles.
	 *
	 * Used to register and enqueue custom scripts and styles
	 * for this control.
	 *
	 * @access public
	 */
	public function enqueue() {
		if ( ! wp_script_is( 'jquery-elementor-select2', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery-elementor-select2' );
			wp_enqueue_style( 'elementor-select2' );
		}

		// Charge le script
		wp_enqueue_script( 'eac-select2-control', EAC_Plugin::instance()->get_script_url( 'assets/js/elementor/controls/eac-select2-control' ), array( 'jquery', 'jquery-elementor-select2' ), EAC_PLUGIN_VERSION, true );

		$args = array(
			'ajax_url'           => admin_url( 'admin-ajax.php' ),
			'ajax_action'        => 'autocomplete_ajax',
			'ajax_action_reload' => 'autocomplete_ajax_reload',
			'ajax_nonce'         => wp_create_nonce( 'eac_autocomplete_search_nonce' ),
		);
		wp_add_inline_script( 'eac-select2-control', 'var eac_autocomplete_search = ' . wp_json_encode( $args ), 'before' );
	}

	/**
	 * Get default settings.
	 *
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return array(
			'placeholder'    => esc_html__( '-- Rechercher --', 'eac-components' ),
			'multiple'       => false,
			'object_type'    => 'post',    // post_type, ex: elementor_library. 'all' pour tous les post_types
			'query_type'     => 'post',    // post, taxonomy ou term
			'query_taxo'     => '',        // category, post_tag, product_cat, product_tag, pa_xxxxx (attribute: pa_tissu)
			'select2Options' => array(),   // Les options dans un tableau
			'label_block'    => true,
		);
	}

	/**
	 * Rendu du contrôle dans l'éditeur.
	 *
	 * @access public
	 */
	public function content_template() {
		?>
		<div class="elementor-control-field">
			<# if (data.label) {#>
				<label for="<?php $this->print_control_uid(); ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<# } #>
			<div class="elementor-control-input-wrapper elementor-control-unit-5 eac-select2_control-field">
				<# var multiple = ( data.multiple ) ? 'multiple' : ''; #>
				<select id="<?php $this->print_control_uid(); ?>" class="elementor-select2 select2-hidden-accessible eac-select2" {{ multiple }} data-setting="{{ data.name }}" type="select2">
				</select>
			</div>
		</div>

		<# if (data.description) { #><div class="elementor-control-field-description">{{{ data.description }}}</div><# } #></div>
		<?php
	}
}
