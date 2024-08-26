<?php
/**
 * Class: Eac_Load_Features
 *
 * Description: Charge les fonctionnalités actives
 *
 * @since 1.9.2
 */

namespace EACCustomWidgets\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EACCustomWidgets\Core\Eac_Config_Elements;

class Eac_Load_Features {

	/**
	 * @var $instance
	 *
	 * Garantir une seule instance de la class
	 */
	private static $instance = null;

	/**
	 * Constructeur de la class
	 *
	 * @param $elements La liste des composants et leur état
	 * @param $featuresLes liste des features et leur état
	 */
	private function __construct() {

		// Filtre Lazyload de WP Rocket
		add_filter( 'rocket_lazyload_excluded_attributes', array( $this, 'rocket_lazyload_exclude_class' ) );

		add_filter( 'upload_mimes', array( $this, 'add_json_mime_type' ) );

		// Charge les fonctionnalités
		$this->load_features();

		/** Ajout des filtres pour les champs de la bibliothèque des medias  */
		if ( Eac_Config_Elements::is_feature_active( 'extend-fields-medias' ) ) {
			add_filter( 'attachment_fields_to_edit', array( $this, 'add_custom_attachment_fields' ), 20, 2 );
			add_filter( 'attachment_fields_to_save', array( $this, 'save_custom_attachment_fields' ), 20, 2 );
		}
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * add_json_mime_type
	 *
	 * Ajout du mime_type JSON pour les animations Lottie et l'import de markers OSM
	 *
	 * @var $mimes Array Les types mimes des fichiers supportés
	 */
	public function add_json_mime_type( $mimes ) {
		if ( Eac_Config_Elements::is_feature_active( 'unfiltered-medias' ) && current_user_can( 'administrator' ) ) {
			// Lottie animation ou Lottie background ou Openstreetmap sont activés et le user est un administrateur
			if ( Eac_Config_Elements::is_widget_active( 'lottie-background' ) || Eac_Config_Elements::is_widget_active( 'lottie-animations' ) || Eac_Config_Elements::is_widget_active( 'open-streetmap' ) ) {
				if ( ! array_key_exists( 'json', $mimes ) ) {
					$mimes['json'] = 'application/json';
				}
			}
		}
		return $mimes;
	}

	/**
	 * rocket_lazyload_exclude_class
	 *
	 * Exclusion Lazyload de WP Rocket des images portants la class 'eac-image-loaded'
	 */
	public function rocket_lazyload_exclude_class( $attributes ) {
		$attributes[] = 'class="eac-image-loaded'; // Ne pas fermer les doubles quotes
		// add_filter('wp_lazy_loading_enabled', '__return_false');
		return $attributes;
	}

	/**
	 * load_features
	 *
	 * Charge les fichiers/objets des fonctionnalités actives
	 */
	public function load_features() {

		/**
		 * Ajout des shortcodes Image externe, Templates Elementor et colonne vue Templates Elementor
		 */
		require_once __DIR__ . '/utils/eac-shortcode.php';

		/**
		 * Gestion des widgets globals
		 */
		require_once __DIR__ . '/utils/eac-global-widgets.php';

		/**
		 * Implémente la mise à jour du plugin ainsi que sa fiche détails
		 */
		require_once __DIR__ . '/utils/eac-plugin-updater.php';

		/**
		 * Utils pour tous les composants et les extensions
		 */
		require_once __DIR__ . '/utils/eac-tools.php';

		/**
		 * Helper pour les composants Post Grid et Product Grid
		 */
		require_once __DIR__ . '/utils/eac-helpers.php';

		/**
		 * Chargement de la Lib de gestion des balises ACF
		 */
		if ( Eac_Config_Elements::is_feature_active( 'acf-dynamic-tag' ) && ! class_exists( Eac_Acf_Lib::class ) ) {
			require_once EAC_DYNAMIC_ACF_TAGS_PATH . 'eac-acf-lib.php';
		}

		/**
		 * Charge les fonctionnalités, notamment les balises dynamiques Elementor
		 */
		foreach ( Eac_Config_Elements::get_features_active() as $element => $active ) {
			if ( Eac_Config_Elements::is_feature_active( $element ) ) {
				$path = Eac_Config_Elements::get_feature_path( $element );
				if ( $path ) {
					require_once $path;
				}
			}
		}
	}

	/**
	 * add_custom_attachment_fields
	 *
	 * Ajout des champs URL et catégories pour les images de la librairie des médias
	 */
	public function add_custom_attachment_fields( $form_fields, $post ) {

		if ( ! wp_attachment_is_image( $post->ID ) ) {
			return $form_fields;
		}

		$field_url = get_post_meta( $post->ID, 'eac_media_url', true );
		$field_cat = get_post_meta( $post->ID, 'eac_media_cat', true );

		$form_fields['eac_media_url'] = array(
			'label' => esc_html__( 'EAC URL personnalisée', 'eac-components' ),
			'input' => 'text',
			'value' => $field_url ? $field_url : '',
		);

		$form_fields['eac_media_cat'] = array(
			'label' => esc_html__( 'EAC catégories', 'eac-components' ),
			'input' => 'text',
			'value' => $field_cat ? $field_cat : '',
			'helps' => esc_html( 'Ex: cat1,cat2,cat3' ),
		);
		return $form_fields;
	}

	/**
	 * save_custom_attachment_fields
	 *
	 * Sauvegarde des champs URL et catégories de la librarie des médias
	 */
	public function save_custom_attachment_fields( $post, $attachment ) {
		if ( ! current_user_can( 'edit_post', $post['ID'] ) ) {
			return $post;
		}

		if ( isset( $attachment['eac_media_url'] ) ) {
			$url = wp_strip_all_tags( stripslashes( filter_var( sanitize_text_field( $attachment['eac_media_url'] ), FILTER_VALIDATE_URL ) ) );
			update_post_meta( $post['ID'], 'eac_media_url', $url );
		}

		if ( isset( $attachment['eac_media_cat'] ) ) {
			update_post_meta( $post['ID'], 'eac_media_cat', sanitize_text_field( $attachment['eac_media_cat'] ) );
		}
		return $post;
	}

} Eac_Load_Features::instance();
