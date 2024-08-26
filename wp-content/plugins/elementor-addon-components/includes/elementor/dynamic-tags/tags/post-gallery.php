<?php
/**
 * Class: Eac_Post_Gallery
 *
 * @return les ID des images de l'article block galerie (Guntenberg) inclus
 * @since 2.2.0
 */

namespace EACCustomWidgets\Includes\Elementor\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Eac_Post_Gallery extends Data_Tag {
	public function get_name() {
		return 'eac-addon-post-gallery';
	}

	public function get_title() {
		return esc_html__( 'Galerie d`images', 'eac-components' );
	}

	public function get_group() {
		return 'eac-post';
	}

	public function get_categories() {
		return array(
			TagsModule::GALLERY_CATEGORY,
		);
	}

	public function get_value( array $options = array() ) {
		$value  = array();
		$images = array();
		$blocks = parse_blocks( get_the_content() );

		/** Gutenberg */
		if ( ! empty( $blocks ) && ! is_null( $blocks[0]['blockName'] ) ) {
			foreach ( $blocks as $block ) {
				if ( 'core/image' === $block['blockName'] ) {
					$images[] = get_post( $block['attrs']['id'] );
				}
				if ( 'core/gallery' === $block['blockName'] ) {
					foreach ( $block['innerBlocks'] as $inner_block ) {
						if ( 'core/image' === $inner_block['blockName'] ) {
							$images[] = get_post( $inner_block['attrs']['id'] );
						}
					}
				}
			}
		}

		/** Récupère tous les médias attachés à l'article. Champ 'Uploaded to' renseigné */
		$images = array_merge( $images, get_attached_media( 'image', get_the_ID() ) );

		foreach ( $images as $image ) {
			/** Les doublons */
			if ( ! in_array( $image->ID, array_column( $value, 'id' ), true ) ) {
				$value[] = array(
					'id' => $image->ID,
				);
			}
		}

		return $value;
	}
}
