<?php
/**
 * Class: Eac_Product_Category_Gallery
 *
 * @return créer un tableau d'ID des images d'une catégorie de produit
 * @since 2.2.0
 */

namespace EACCustomWidgets\Includes\Woocommerce\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\Includes\Woocommerce\DynamicTags\Tags\Traits\Eac_Product_Woo_Traits;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Eac_Product_Category_Gallery extends Data_Tag {
	use Eac_Product_Woo_Traits;

	public function get_name() {
		return 'eac-addon-woo-category-gallery';
	}

	public function get_title() {
		return esc_html__( 'Galerie d`une catégorie', 'eac-components' );
	}

	public function get_group() {
		return 'eac-woo-groupe';
	}

	public function get_categories() {
		return array( TagsModule::GALLERY_CATEGORY );
	}

	protected function register_controls() {
		$this->register_product_term_control();
	}

	public function get_value( array $options = array() ) {
		$cat_id = $this->get_settings( 'product_category' );
		$value  = array();

		if ( $cat_id ) {
			$products = wc_get_products(
				array(
					'post_status'         => 'publish',
					'limit'               => -1,
					'orderby'             => 'name',
					'order'               => 'ASC',
					'parent'              => 0,
					'product_category_id' => array( $cat_id ),
				)
			);

			if ( ! is_wp_error( $products ) && ! empty( $products ) ) {
				foreach ( $products as $product ) {
					$thumb_id = $product->get_image_id();
					if ( $thumb_id ) {
						$value[] = array( 'id' => $thumb_id . '::category::' . $product->get_id() );
					}
				}
			}
		}
		return $value;
	}
}
