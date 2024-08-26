<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * eac_register_shortcode
 *
 * Crée le point d'accès Shortcode pour les images externes 'eac_img_shortcode'
 * Crée le point d'accès pour l'intégration des Templates Elementor
 * Affiche la valeur de la colonne 'Shortcode' dans la vue Elementor Templates
 *
 * @since 1.5.3
 */
add_action( 'init', 'eac_register_shortcode', 0 );
function eac_register_shortcode() {
	add_shortcode( 'eac_img', 'eac_img_shortcode' );
	add_shortcode( 'eac_elementor_tmpl', 'eac_elementor_add_tmpl' );
	if ( class_exists( 'WooCommerce' ) ) {
		add_shortcode( 'eac_product_rating', 'eac_display_product_rating' );
		add_shortcode( 'eac_widget_mini_cart', 'eac_display_widget_mini_cart' );
	}
}

/**
 * Affiche les dix dernières ventes pour les sept derniers jours
 * TODO wc_get_orders ne peux être appelé avant post_type soit créé: woocommerce_after_register_post_type action
 */
/*function eac_product_sold_last_n_days() {
	$all_orders = wc_get_orders(
		array(
			'limit'      => -1,
			'status'     => array_map( 'wc_get_order_status_name', wc_get_is_paid_statuses() ),
			'date_after' => date( 'Y-m-d', strtotime( '-7 days' ) ),
			'return'     => 'ids',
		)
	);
	$trending = array();
	foreach ( $all_orders as $all_order ) {
		$order = wc_get_order( $all_order );
		$items = $order->get_items();
		foreach ( $items as $item ) {
			$product_id = $item->get_product_id();
			if ( ! $product_id ) {
				continue;
			}
			$trending[ $product_id ] = $trending[ $product_id ] ? (int) $trending[ $product_id ] + $item['qty'] : $item['qty'];
		}
	}
	arsort( $trending, SORT_NUMERIC );
	return array_keys( array_slice( $trending, 0, 10, true ) );
}

echo do_shortcode( '[products ids="' . eac_product_sold_last_n_days() . '"]' );
*/

/** Affiche le mini-cart */
if ( ! function_exists( 'eac_display_widget_mini_cart' ) ) {
	function eac_display_widget_mini_cart( $params = array() ) {
		$args = shortcode_atts(
			array(
				'title' => '',
			),
			$params,
			'eac_widget_mini_cart'
		);
		/**$has_cart = ! is_null( WC()->cart && WC()->cart->get_cart_contents_count() !== 0 );*/
		$title = ! empty( $args['title'] ) ? trim( $args['title'] ) : esc_html__( 'Mon panier', 'eac-components' );
		ob_start();
		?>
		<div class="eac_widget_mini_cart">
		<?php the_widget( 'WC_Widget_Cart', array( 'title' => $title ) ); ?>
		</div>
		<?php
		return ob_get_clean();
	}
}

// WooCommerce product rating
if ( ! function_exists( 'eac_display_product_rating' ) ) {
	function eac_display_product_rating( $params = array() ) {
		$args = shortcode_atts(
			array(
				'id' => '',
			),
			$params,
			'eac_product_rating'
		);

		if ( isset( $args['id'] ) && $args['id'] > 0 ) {
			// Get an instance of the WC_Product Object
			$product = wc_get_product( $args['id'] );

			// The product average rating (or how many stars this product has)
			$average = $product->get_average_rating();
		}

		if ( isset( $average ) ) {
			return wc_get_rating_html( $average );
		}
	}
}

/**
 * eac_img_shortcode
 * Shortcode d'intégration d'une image avec lien externe, fancybox et caption
 *
 * Ex:  [eac_img src="https://www.cestpascommode.fr/wp-content/uploads/2019/04/fauteuil-louis-philippe-zebre-01.jpg" fancybox="yes" caption="Fauteuil Zèbre"]
 *      [eac_img src="https://www.cestpascommode.fr/wp-content/uploads/2020/04/chaise-victoria-01.jpg" link="https://www.cestpascommode.fr/realisations/chaise-victoria" caption="Chaise Victoria"]
 *      [eac_img link="https://www.cestpascommode.fr/realisations/bergere-louis-xv-et-sa-chaise" embed="yes"]
 *
 * @since 1.6.0
 */
function eac_img_shortcode( $params = array() ) {
	$args = shortcode_atts(
		array(
			'src'      => '',
			'link'     => '',
			'fancybox' => 'no',
			'caption'  => '',
			'embed'    => 'no',
		),
		$params,
		'eac_img'
	);

	$html_default = '';
	$source       = esc_url( $args['src'] );
	$linked       = esc_url( $args['link'] );
	$fancy_box    = in_array( trim( $args['fancybox'] ), array( 'yes', 'no' ), true ) ? trim( $args['fancybox'] ) : 'no';
	$fig_caption  = esc_html( $args['caption'] );
	$embed_link   = in_array( trim( $args['embed'] ), array( 'yes', 'no' ), true ) ? trim( $args['embed'] ) : 'no';

	if ( empty( $source ) ) {
		return $html_default; }

	if ( 'yes' === $embed_link ) {
		// print_r($linked); // Embed le lien
	} elseif ( ! empty( $linked ) ) { // Lien externe
		$html_default =
			'<figure>
                <a href="' . $linked . '">
                    <img src="' . $source . '" alt="' . $fig_caption . '" />
                    <figcaption>' . $fig_caption . '</figcaption>
                </a>
            </figure>';
		// @since 1.6.2 Fancybox
	} elseif ( 'yes' === $fancy_box ) {
		$html_default =
			'<figure>
                <a href="' . $source . '" data-elementor-open-lightbox="no" data-fancybox="eac-img-shortcode" data-caption="' . $fig_caption . '">
                    <img src="' . $source . '" alt="' . $fig_caption . '"/>
                    <figcaption>' . $fig_caption . '</figcaption>
                </a>
            </figure>';
	} else {
		$html_default =
			'<figure>
                <img src="' . $source . '" alt="' . $fig_caption . '"/>
                <figcaption>' . $fig_caption . '</figcaption>
            </figure>';
	}

	// Return HTML code
	return $html_default;
}

/**
 * eac_elementor_tmpl
 * Shortcode d'intégration d'un modèle Elementor
 *
 * Ex: [eac_elementor_tmpl id="XXXXX"]
 *
 * @since 1.6.0
 */
function eac_elementor_add_tmpl( $params = array() ) {
	$args = shortcode_atts(
		array(
			'id'  => '',
			'css' => 'false',
		),
		$params,
		'eac_elementor_tmpl'
	);

	$id_tmpl  = absint( trim( $args['id'] ) );
	$css_tmpl = 'false' === trim( $args['css'] ) ? false : true;

	if ( empty( $id_tmpl ) || ! get_post( $id_tmpl ) ) {
		return '';
	}

	// Évite la récursivité
	if ( get_the_ID() === $id_tmpl ) {
		return esc_html__( 'ID du modèle ne peut pas être le même que le modèle actuel', 'eac-components' );
	}

	$id_tmpl = apply_filters( 'wpml_object_id', $id_tmpl, 'elementor_library', true );

	return \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id_tmpl );
}

/**
 * console_log
 * Affiche des traces dans la console du navigateur
 *
 * @since 1.6.5
 */
function console_log( $output, $with_script_tags = true ) {
	$js_code = 'console.log(' . wp_json_encode( $output, JSON_HEX_TAG ) . ');';
	if ( $with_script_tags ) {
		$js_code = '<script>' . $js_code . '</script>';
	}
	echo $js_code;
}
