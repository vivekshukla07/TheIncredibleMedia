<?php
/**
 * Class: Eac_Product_Upsell_Gallery
 *
 * @return créer un tableau d'ID des produits relatifs (upsell) à un produit
 * @since 2.2.2
 */

namespace EACCustomWidgets\Includes\Woocommerce\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\Includes\Woocommerce\DynamicTags\Tags\Traits\Eac_Product_Woo_Traits;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Eac_Product_Upsell_Gallery extends Data_Tag {
	use Eac_Product_Woo_Traits;

	public function get_name() {
		return 'eac-addon-woo-upsell-gallery';
	}

	public function get_title() {
		return esc_html__( 'Galerie de produits de vente incitative', 'eac-components' );
	}

	public function get_group() {
		return 'eac-woo-groupe';
	}

	public function get_categories() {
		return array( TagsModule::GALLERY_CATEGORY );
	}

	protected function register_controls() {
		$this->register_product_id_control();
	}

	public function get_value( array $options = array() ) {
		$product_id = $this->get_settings( 'product_id' );
		$value      = array();

		if ( empty( $product_id ) ) {
			return $value;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return $value;
		}

		$upsell_ids = $product->get_upsell_ids();
		foreach ( $upsell_ids as $upsell_id ) {
			$product_upsell = wc_get_product( $upsell_id );
			$attachment_id  = $product_upsell->get_image_id();
			$value[]        = array( 'id' => $attachment_id . '::product::' . $product_upsell->get_id() );
		}
		return $value;
	}
}
