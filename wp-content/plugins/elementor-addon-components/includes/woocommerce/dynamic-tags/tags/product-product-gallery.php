<?php
/**
 * Class: Eac_Product_Gallery_images
 *
 * @return créer un tableau d'ID des images d'un produit
 * @since 1.9.8
 */

namespace EACCustomWidgets\Includes\Woocommerce\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\Includes\Woocommerce\DynamicTags\Tags\Traits\Eac_Product_Woo_Traits;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Eac_Product_Gallery_Images extends Data_Tag {
	use Eac_Product_Woo_Traits;

	public function get_name() {
		return 'eac-addon-woo-gallery-images';
	}

	public function get_title() {
		return esc_html__( 'Galerie d`un produit', 'eac-components' );
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
			'eac_woo_gallery_thumb',
			array(
				'label'        => esc_html__( "Ajouter l'image du produit", 'eac-components' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'oui', 'eac-components' ),
				'label_off'    => esc_html__( 'non', 'eac-components' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);
	}

	public function get_value( array $options = array() ) {
		$product_id     = $this->get_settings( 'product_id' );
		$settings_thumb = $this->get_settings( 'eac_woo_gallery_thumb' ) === 'yes' ? true : false;
		$value          = array();

		if ( empty( $product_id ) ) {
			return '';
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return '';    }

		if ( $settings_thumb ) {
			$thumb_id = $product->get_image_id();
			if ( $thumb_id ) {
				$value[]  = array( 'id' => $thumb_id . '::product::' . $product->get_id() );
			}
		}

		$attachment_ids = $product->get_gallery_image_ids();
		foreach ( $attachment_ids as $attachment_id ) {
			$value[] = array( 'id' => $attachment_id . '::product::' . $product->get_id() );
		}
		return $value;
	}
}
