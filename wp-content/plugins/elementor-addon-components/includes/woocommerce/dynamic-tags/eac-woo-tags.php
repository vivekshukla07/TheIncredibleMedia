<?php
/**
 * Class: Eac_Woo_Tags
 *
 * Description: Module de base qui enregistre les objets des balises dynamiques WooCommerce
 *
 * @since 1.9.8
 */

namespace EACCustomWidgets\Includes\Woocommerce\DynamicTags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Eac_Woo_Tags {

	const TAG_DIR        = __DIR__ . '/tags/';
	const TAG_DIR_TRAITS = __DIR__ . '/tags/traits/';
	const TAG_NAMESPACE  = __NAMESPACE__ . '\\tags\\';

	/**
	 * $tags_list
	 *
	 * Liste des tags: Nom du fichier PHP => class
	 */
	private $tags_list = array(
		'product-add-to-cart'          => 'Eac_Product_Add_To_Cart',
		'product-excerpt'              => 'Eac_Product_Excerpt',
		'product-featured-image'       => 'Eac_Product_Image',
		'product-onsale'               => 'Eac_Product_Sale',
		'product-prices'               => 'Eac_Product_Prices',
		'product-rating'               => 'Eac_Product_Rating',
		'product-sku'                  => 'Eac_Product_Sku',
		'product-stock'                => 'Eac_Product_Stock',
		'product-terms'                => 'Eac_Product_Terms',
		'product-title'                => 'Eac_Product_Title',
		'product-url'                  => 'Eac_Products_Url',
		'product-field-keys'           => 'Eac_Product_Field_Keys',
		'product-field-values'         => 'Eac_Product_Field_Values',
		'product-product-gallery'      => 'Eac_Product_Gallery_Images',
		'product-sale'                 => 'Eac_Product_Sale_Total',
		'product-category-image'       => 'Eac_Product_Category_Image',
		'product-category-gallery'     => 'Eac_Product_Category_Gallery',
		'product-category-url'         => 'Eac_Categories_Url',
		'product-categories-gallery'   => 'Eac_Product_Categories_Gallery',
		'product-featured-gallery'     => 'Eac_Product_Featured_Gallery',
		'product-best-selling-gallery' => 'Eac_Product_Best_Selling_Gallery',
		'product-upsell-gallery'       => 'Eac_Product_Upsell_Gallery',
		'product-similar-gallery'      => 'Eac_Product_Gallery_Similar',
	);

	/**
	 * Constructeur de la class
	 *
	 * @access public
	 */
	public function __construct() {
		// Charge le trait 'product id'
		require_once self::TAG_DIR_TRAITS . 'product-trait.php';

		add_action( 'elementor/dynamic_tags/register', array( $this, 'register_tags' ) );

		/** Supprime les zéros à la fin des prix */
		add_filter( 'woocommerce_price_trim_zeros', '__return_true' );
	}

	/**
	 * Enregistre le groupe et les balises dynamiques WooCommerce
	 */
	public function register_tags( $dynamic_tags ) {
		// Enregistre le nouveau groupe avant d'enregistrer les Tags
		$dynamic_tags->register_group( 'eac-woo-groupe', array( 'title' => esc_html__( 'EAC WooCommerce', 'eac-components' ) ) );

		foreach ( $this->tags_list as $file => $class_name ) {
			$full_class_name = self::TAG_NAMESPACE . $class_name;
			$full_file       = self::TAG_DIR . $file . '.php';

			if ( ! file_exists( $full_file ) ) {
				continue;
			}

			// Le fichier est chargé avant de checker le nom de la class
			require_once $full_file;

			if ( class_exists( $full_class_name ) ) {
				$dynamic_tags->register( new $full_class_name() );
			}
		}
	}

} new Eac_Woo_Tags();
