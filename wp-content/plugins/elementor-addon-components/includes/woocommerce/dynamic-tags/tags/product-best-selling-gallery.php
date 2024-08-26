<?php
/**
 * Class: Eac_Product_Best_Selling_Gallery
 *
 * @return créer un tableau d'ID des images des meilleures ventes
 * @since 2.2.2
 */

namespace EACCustomWidgets\Includes\Woocommerce\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Eac_Product_Best_Selling_Gallery extends Data_Tag {

	public function get_name() {
		return 'eac-addon-woo-best-selling-gallery';
	}

	public function get_title() {
		return esc_html__( 'Galerie des meilleures ventes', 'eac-components' );
	}

	public function get_group() {
		return 'eac-woo-groupe';
	}

	public function get_categories() {
		return array( TagsModule::GALLERY_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control(
			'woo_selling',
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
		$limit = $this->get_settings( 'woo_selling' );
		$value = array();

		$products = wc_get_products(
			array(
				'meta_key' => 'total_sales',
				'limit'    => absint( $limit ),
				'parent'   => 0,
				'orderby'  => array(
					'meta_value_num' => 'DESC',
					'title'          => 'ASC',
				),
			)
		);

		if ( ! is_wp_error( $products ) && ! empty( $products ) ) {
			foreach ( $products as $product ) {
				$thumb_id = $product->get_image_id();
				if ( $thumb_id ) {
					$value[] = array( 'id' => $thumb_id . '::selling::' . $product->get_id() );
				}
			}
		}
		return $value;
	}
}
