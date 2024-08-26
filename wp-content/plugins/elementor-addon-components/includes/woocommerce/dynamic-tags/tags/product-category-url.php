<?php
/**
 * Class: Eac_Categories_Url
 *
 * @return affiche la liste des produits par leur URL
 * @since 2.2.0
 */

namespace EACCustomWidgets\Includes\Woocommerce\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\Includes\Woocommerce\DynamicTags\Tags\Traits\Eac_Product_Woo_Traits;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Eac_Categories_Url extends Tag {
	use Eac_Product_Woo_Traits;

	public function get_name() {
		return 'eac-addon-woo-url-cat';
	}

	public function get_title() {
		return esc_html__( 'URL des catégories', 'eac-components' );
	}

	public function get_group() {
		return 'eac-woo-groupe';
	}

	public function get_categories() {
		return array( TagsModule::URL_CATEGORY );
	}

	protected function register_controls() {
		$this->register_product_term_control();
	}

	public function render() {
		$cat_id = $this->get_settings( 'product_category' );

		if ( empty( $cat_id ) ) {
			return '';
		}

		$link = get_term_link( absint( $cat_id ), 'product_cat' );
		if ( ! is_wp_error( $link ) ) {
			echo esc_url( $link );
		} else {
			echo '';
		}
	}
}
