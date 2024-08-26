<?php
/**
 * Class: Eac_Product_Categories_Gallery
 *
 * @return créer un tableau d'ID des images de toutes les catégories de produit
 * @since 2.2.2
 */

namespace EACCustomWidgets\Includes\Woocommerce\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Eac_Product_Categories_Gallery extends Data_Tag {

	public function get_name() {
		return 'eac-addon-woo-categories-gallery';
	}

	public function get_title() {
		return esc_html__( 'Galerie des catégories', 'eac-components' );
	}

	public function get_group() {
		return 'eac-woo-groupe';
	}

	public function get_categories() {
		return array( TagsModule::GALLERY_CATEGORY );
	}

	public function get_value( array $options = array() ) {
		$value      = array();

		$args = array(
			'hide_empty' => 1,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
		);
		$terms = get_terms( $args );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
				if ( $thumb_id && ! empty( $thumb_id ) && 0 !== $thumb_id ) {
					$value[] = array( 'id' => $thumb_id . '::categories::' . $term->term_id . '::' . $term->count );
				} else {
					$value[] = array( 'id' => (int) get_option( 'woocommerce_placeholder_image' ) . '::categories::' . $term->term_id . '::' . $term->count );
				}
			}
		}
		return $value;
	}
}
