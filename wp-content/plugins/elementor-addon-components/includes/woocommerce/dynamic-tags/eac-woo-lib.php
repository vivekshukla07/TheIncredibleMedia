<?php
/**
 * Class: Eac_Woo_Lib
 *
 * Description: Module WC pour mettre à disposition les méthodes nécessaires
 * aux balises dynamiques WC
 *
 * @since 1.9.8
 */

namespace EACCustomWidgets\Includes\Woocommerce\DynamicTags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Eac_Woo_Lib {

	/**
	 * @var $wc_meta_key_to_props
	 *
	 * Liste des meta_key des produits
	 */
	private static $wc_meta_key_to_props = array(
		'_sku'                   => 'sku',
		'_regular_price'         => 'regular_price',
		'_sale_price'            => 'sale_price',
		'_sale_price_dates_from' => 'date_on_sale_from',
		'_sale_price_dates_to'   => 'date_on_sale_to',
		'total_sales'            => 'total_sales',
		'_tax_status'            => 'tax_status',
		'_tax_class'             => 'tax_class',
		'_manage_stock'          => 'manage_stock',
		'_backorders'            => 'backorders',
		'_low_stock_amount'      => 'low_stock_amount',
		'_sold_individually'     => 'sold_individually',
		'_weight'                => 'weight',
		'_length'                => 'length',
		'_width'                 => 'width',
		'_height'                => 'height',
		'_purchase_note'         => 'purchase_note',
		'_default_attributes'    => 'default_attributes',
		'_virtual'               => 'virtual',
		'_downloadable'          => 'downloadable',
		'_download_limit'        => 'download_limit',
		'_download_expiry'       => 'download_expiry',
		'_stock'                 => 'stock_quantity',
		'_stock_status'          => 'stock_status',
		'_wc_average_rating'     => 'average_rating',
		'_wc_rating_count'       => 'rating_counts',
		'_wc_review_count'       => 'review_count',
	);

	/**
	 * Constructeur de la class
	 *
	 * @access public
	 */
	public function __construct() {}

	/**
	 * @return la propriété d'une meta_key
	 */
	public static function wc_get_meta_key_to_props( $key ) {
		$meta_key = '';

		if ( array_key_exists( $key, self::$wc_meta_key_to_props ) ) {
			$meta_key = self::$wc_meta_key_to_props[ $key ];
		}
		return $meta_key;
	}

} new Eac_Woo_lib();
