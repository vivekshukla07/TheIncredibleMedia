<?php
/**
 * Class: Eac_Load_Scripts
 *
 * Description: Affecte les actions nécessaires et enregistre les scripts/styles
 * Ajout et valorisation des colonnes dans les vues Elementor
 *
 * @since 1.9.2
 */

namespace EACCustomWidgets\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EACCustomWidgets\Core\Eac_Config_Elements;
use EACCustomWidgets\EAC_Plugin;
use EACCustomWidgets\Includes\TemplatesLib\Documents\SiteHeader;
use EACCustomWidgets\Includes\TemplatesLib\Documents\SiteFooter;

/**
 * Eac_Load_Scripts
 */
class Eac_Load_Scripts {

	/**
	 * @var $instance
	 *
	 * Garantir une seule instance de la class
	 */
	private static $instance = null;

	/**
	 * __construct
	 *
	 * @return void
	 */
	private function __construct() {

		/** Action pour charger les styles et scripts globaux */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_global_scripts_styles' ) );

		/** Action pour insérer les scripts et les fonts Awesome dans l'éditeur */
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ) );

		/** Action pour insérer les styles dans le panel Elementor */
		add_action( 'elementor/editor/wp_head', array( $this, 'enqueue_panel_styles' ) );

		/** Filtre sur les attributs des balises HTML autorisées */
		add_filter( 'wp_kses_allowed_html', array( $this, 'add_allowed_attribute_element' ), 10, 2 );

		/** Ajout et valorisation des colonnes des vues Elementor */
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			add_filter( 'manage_elementor_library_posts_columns', array( $this, 'add_columns' ) );
			add_action( 'manage_elementor_library_posts_custom_column', array( $this, 'data_columns' ), 10, 2 );
		}

		/** !!!! */
		/**$this->trigger_group_acf();*/
	}

	/** Singleton de la class */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Imbitable si la fonction n'est pas appelée, la colonne ACF groups ne sera pas valorisée */
	public function trigger_group_acf() {
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$groups = count( acf_get_field_groups() );
		}
	}

	/**
	 * add_allowed_attribute_element
	 *
	 * @param  array $allowed_tags Toutes les balises et leurs attributs
	 * @param  mixed $context Le type de post type
	 * @return array La liste des balises amendée
	 */
	public function add_allowed_attribute_element( $allowed_tags, $context ) {
		if ( 'post' !== $context ) {
			return $allowed_tags;
		}

		if ( isset( $allowed_tags['div'] ) ) {
			$allowed_tags['div']['tabindex'] = true;
		}

		if ( isset( $allowed_tags['ul'] ) ) {
			$allowed_tags['ul']['aria-orientation'] = true;
			$allowed_tags['ul']['aria-haspopup']    = true;
		}

		if ( isset( $allowed_tags['a'] ) ) {
			$allowed_tags['a']['aria-haspopup'] = true;
		}

		if ( isset( $allowed_tags['button'] ) ) {
			$allowed_tags['button']['tabindex'] = true;
		}

		if ( isset( $allowed_tags['img'] ) ) {
			$allowed_tags['img']['srcset'] = true;
			$allowed_tags['img']['sizes']  = true;
		}
		return $allowed_tags;
	}

	/**
	 * enqueue_global_scripts_styles
	 *
	 * Enqueue les styles et scripts globaux
	 */
	public function enqueue_global_scripts_styles() {

		// Le script global
		wp_enqueue_script( 'eac-elements', EAC_Plugin::instance()->get_script_url( 'assets/js/eac-components' ), array( 'jquery' ), '1.0.0', true );

		/** Passe les URLs absolues de certains composants aux objects javascript */
		wp_add_inline_script(
			'eac-elements',
			'var eacElementsPath = ' . wp_json_encode(
				array(
					'proxies'   => EAC_ADDON_URL . 'includes/proxy/',
					'pdfJs'     => EAC_ADDON_URL . 'assets/js/pdfjs/',
					'osmImages' => EAC_ADDON_URL . 'assets/images/',
					'osmConfig' => EAC_ADDON_URL . 'includes/config/osm/',
				)
			),
			'before'
		);

		// Le script de la Fancybox
		wp_enqueue_script( 'eac-fancybox', EAC_Plugin::instance()->get_script_url( 'assets/js/fancybox/jquery.fancybox' ), array( 'jquery' ), '3.5.7', true );

		// https://instant.page/tech
		if ( Eac_Config_Elements::is_feature_active( 'preload-page' ) && ! wp_script_is( 'instant-page', 'enqueued' ) ) {
			add_action( 'wp_footer', function() {
				wp_enqueue_script( 'instant-page', EAC_Plugin::instance()->get_script_url( 'assets/js/instantpage/instantpage' ), array(), '5.2.0', true );
			} );
		}

		// Les styles globaux partagés
		wp_enqueue_style( 'eac', EAC_Plugin::instance()->get_style_url( 'assets/css/eac-components' ), array(), EAC_PLUGIN_VERSION );

		// Les styles de la Fancybox
		wp_enqueue_style( 'eac-fancybox', EAC_Plugin::instance()->get_style_url( 'assets/css/jquery.fancybox' ), array( 'eac' ), '3.5.7' );

		/** Le CSS de X twitter ne s'affiche pas sur toutes les pages TODO */
		if ( ! wp_style_is( 'elementor-icons-fa-brands', 'enqueued' ) ) {
			wp_enqueue_style( 'elementor-icons-fa-brands', plugins_url( '/elementor/assets/lib/font-awesome/css/brands.min.css' ), false, '5.15.3' );
		}
	}

	/**
	 * enqueue_editor_scripts
	 *
	 * Enregistre les styles Font Awesome dans l'éditeur
	 */
	public function enqueue_editor_scripts() {
		/** Semblerait que les fonts Awesome ne soient pas chargées dans l'éditeur Elementor */
		if ( ! wp_style_is( 'font-awesome-5-all', 'enqueued' ) ) {
			wp_enqueue_style( 'font-awesome-5-all', plugins_url( '/elementor/assets/lib/font-awesome/css/all.min.css' ), false, '5.15.3' );
		}
	}

	/**
	 * enqueue_panel_styles
	 *
	 * Enregistre les styles dans le panel de l'éditeur Elementor
	 * Propriété 'content_classes' de control RAW_HTML
	 * Classes de font Awesome pour les control 'start_controls_tab' OpenStreetMap
	 */
	public function enqueue_panel_styles() {
		wp_enqueue_style( 'eac-editor-panel', EAC_Plugin::instance()->get_style_url( 'assets/css/eac-editor-panel' ), false, '1.0.0' );
	}

	/**
	 * add_columns
	 *
	 * Ajout de la colonne 'Shortcode' dans la vue Elementor Templates
	 * Ajout des colonnes 'Show on/ACF groups' dans les vues Header/Footer
	 *
	 * @param  mixed $columns La liste des colonnes de la vue
	 * @return array La liste des colonnes amendées
	 */
	public function add_columns( $columns ) {
		$hft_column = array();

		if ( class_exists( SiteHeader::class ) && ! empty( get_query_var( 'elementor_library_type' ) ) && ( SiteHeader::TYPE === get_query_var( 'elementor_library_type' ) || SiteFooter::TYPE === get_query_var( 'elementor_library_type' ) ) ) {
			$hft_column['eac_hfb_show_on'] = esc_html__( 'Afficher avec', 'eac-components' );

			/**if ( function_exists( 'acf_get_field_groups' ) ) {
				$hft_column['eac_hfb_acf']  = esc_html__( 'ACF groupes', 'eac-components' );
			}*/
		} elseif ( ! empty( get_query_var( 'elementor_library_type' ) ) ) { // Pour sauter l'onglet 'Saved templates'
			$hft_column['eac_shortcode'] = esc_html__( 'Code court', 'eac-components' );
		}

		return array_merge( $columns, $hft_column );
	}

	/**
	 * data_columns
	 *
	 * Affiche la valeur des colonnes dans la vue Elementor Templates
	 *
	 * @param  mixed $column_name
	 * @param  mixed $post_id
	 * @return void
	 */
	public function data_columns( $column_name, $post_id ) {
		?><style type="text/css">
			th#eac_hfb_acf { width: 13%; }
			th#elementor_library_type { width: 10%; }
		</style>
		<?php

		if ( 'eac_shortcode' === $column_name ) {
			echo '<input type="text" class="widefat" onfocus="this.select()" value=\'[eac_elementor_tmpl id="' . esc_attr( $post_id ) . '"]\' readonly>';
		} elseif ( 'eac_hfb_acf' === $column_name ) {
			$have_acf = array();
			$groups   = acf_get_field_groups( array( 'post_id' => $post_id ) );

			foreach ( $groups as $group ) {
				if ( $group['active'] ) {
					$local_groups = acf_have_local_field_groups() ? acf_count_local_field_groups() : 0;
					$local        = acf_have_local_field_groups() && acf_is_local_field_group( $group['key'] ) ? true : false;
					if ( $local ) {
						$have_acf[] = 'Local::' . $group['title'];
					} else {
						$have_acf[] = $group['title'];
					}
				}
			}
			if ( empty( $have_acf ) ) {
				$have_acf[] = 'No';
			}

			echo esc_html( implode( ', ', $have_acf ) );
		} elseif ( 'eac_hfb_show_on' === $column_name ) {
			$meta = get_post_meta( $post_id, '_elementor_page_settings', true );

			if ( isset( $meta['show_on'] ) ) {
				if ( 'singular' === $meta['show_on'] ) {
					if ( ! empty( $meta['singular_pages'] ) ) {
						echo esc_html( implode( ', ', array_map( 'ucfirst', $meta['singular_pages'] ) ) );
					} else {
						echo esc_html( 'singular' );
					}
				} elseif ( 'archive' === $meta['show_on'] ) {
					if ( ! empty( $meta['archive_pages'] ) ) {
						echo esc_html( implode( ', ', array_map( 'ucfirst', $meta['archive_pages'] ) ) );
					} else {
						echo esc_html( 'Archives' );
					}
				} elseif ( 'custom' === $meta['show_on'] ) {
					if ( ! empty( $meta['singular_pages'] ) ) {
						echo esc_html( implode( ', ', array_map( 'ucfirst', $meta['singular_pages'] ) ) );
					}
					if ( ! empty( $meta['archive_pages'] ) ) {
						echo esc_html( ', ' . implode( ', ', array_map( 'ucfirst', $meta['archive_pages'] ) ) );
					}
				} elseif ( 'global' === $meta['show_on'] ) {
					echo esc_html( 'Global' );
				} elseif ( 'blog' === $meta['show_on'] || 'index' === $meta['show_on'] ) {
					echo esc_html( 'Blog' );
				} elseif ( 'front' === $meta['show_on'] ) {
					echo esc_html__( "Page d'accueil", 'eac-components' );
				} elseif ( 'search' === $meta['show_on'] ) {
					echo esc_html__( 'Résultat de la recherche', 'eac-components' );
				} elseif ( 'wc_shop' === $meta['show_on'] ) {
					echo esc_html__( 'Boutique WooCommerce', 'eac-components' );
				} elseif ( 'err404' === $meta['show_on'] ) {
					echo esc_html__( 'Page erreur 404', 'eac-components' );
				} elseif ( 'privacy' === $meta['show_on'] ) {
					echo esc_html__( 'Politique de confidentialité', 'eac-components' );
				} else {
					echo esc_html( '---' );
				}
			} else {
				echo esc_html( '---' );
			}
		}
	}

} Eac_Load_Scripts::instance();
