<?php
/** @since 1.9.8 Création du trait pour les balises dynamiques Woocommerce */
namespace EACCustomWidgets\Includes\Woocommerce\DynamicTags\Tags\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait Eac_Product_Woo_Traits {
	public function register_product_id_control() {
		$this->add_control(
			'product_id',
			array(
				'label'       => esc_html__( 'Sélectionner un titre', 'eac-components' ),
				'type'        => 'eac-select2',
				'object_type' => 'product',
				'default'     => false,
			)
		);
	}

	/** @since 1.9.9 'query_type' 'taxonomy' */
	public function register_product_taxonomy_control() {
		$this->add_control(
			'product_taxo',
			array(
				'label'       => esc_html__( 'Sélectionner la taxonomie', 'eac-components' ),
				'type'        => 'eac-select2',
				'object_type' => 'product',
				'query_type'  => 'taxonomy',
				'default'     => false,
			)
		);
	}

	/** @since 1.9.9 */
	public function register_product_term_control() {
		$this->add_control(
			'product_category',
			array(
				'label'       => esc_html__( 'Sélectionner la catégorie', 'eac-components' ),
				'type'        => 'eac-select2',
				'object_type' => 'product',
				'query_type'  => 'term',
				'query_taxo'  => 'product_cat',
				'default'     => false,
			)
		);
	}
}
