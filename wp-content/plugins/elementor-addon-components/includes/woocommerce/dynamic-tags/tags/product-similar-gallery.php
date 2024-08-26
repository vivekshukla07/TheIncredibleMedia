<?php
/**
 * Class: Eac_Product_GallEac_Product_Gallery_Similarery_images
 *
 * @return créer un tableau d'ID des images similaires (par leur catégorie) à un produit
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

class Eac_Product_Gallery_Similar extends Data_Tag {
	use Eac_Product_Woo_Traits;

	public function get_name() {
		return 'eac-addon-woo-gallery-similar';
	}

	public function get_title() {
		return esc_html__( 'Galerie de produits similaires', 'eac-components' );
	}

	public function get_group() {
		return 'eac-woo-groupe';
	}

	public function get_categories() {
		return array( TagsModule::GALLERY_CATEGORY );
	}

	protected function register_controls() {
		$this->register_product_id_control();

		$this->add_control(
			'woo_similar',
			array(
				'label'   => esc_html__( 'Nombre de produits', 'eac-components' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 50,
				'step'    => 1,
				'default' => 4,
			)
		);
	}

	public function get_value( array $options = array() ) {
		$product_id = $this->get_settings( 'product_id' );
		$limit      = $this->get_settings( 'woo_similar' );
		$value      = array();

		if ( empty( $product_id ) ) {
			return $value;
		}

		$product_cat = wc_get_product( $product_id );
		if ( ! $product_cat ) {
			return $value;    }

		$terms = get_the_terms( $product_cat->get_id(), 'product_cat' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$term_cat = $terms[0]->name;
			$args     = array(
				'category' => array( $term_cat ),
				'limit'    => $limit,
				'orderby'  => 'rand',
				'exclude'  => array( $product_id ),
			);
			$products = wc_get_products( $args );
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
