<?php
/**
 * Class: Eac_Tools_Util
 *
 * Description: Met à disposition un ensemble de méthodes utiles pour les composants
 *
 * @since 1.0.0
 */

namespace EACCustomWidgets\Core\Utils;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EACCustomWidgets\Core\Eac_Config_Elements;
use Elementor\TemplateLibrary\Source_Local;

class Eac_Tools_Util {

	private static $instance = null;

	/**
	 * @var $user_meta_fields
	 *
	 * Liste des metas acceptés pour les informations Auteur et User
	 */
	private static $user_meta_fields = array(
		'locale',
		'syntax_highlighting',
		'avatar',
		'nickname',
		'first_name',
		'last_name',
		'description',
		'rich_editing',
		'role',
		'twitter',
		'facebook',
		'instagram',
		'linkedin',
		'youtube',
		'pinterest',
		'tumblr',
		'flickr',
		'adrs_address',
		'adrs_city',
		'adrs_zipcode',
		'adrs_country',
		'adrs_occupation',
		'adrs_full',
		'show_admin_bar_front',
	);

	/**
	 * @var $filtered_taxonomies
	 *
	 * Exclusion de catégories
	 */
	private static $filtered_taxonomies = array(
		'nav_menu',
		'link_category',
		'post_format',
		'elementor_library_type',
		'elementor_library_category',
		'elementor_font_type',
		'yst_prominent_words',
		'product_type',
		'product_shipping_class',
		'product_visibility',
		'action-group',
		'translation_priority',
		'flamingo_contact_tag',
		'flamingo_inbound_channel',
		'sdm_categories',
		'sdm_tags',
		'wpforms_log_type',
		'wp_theme',
		'wp_template_part_area',
		'page_options_category',
	);

	/**
	 * @var $filtered_posttypes
	 *
	 * Exclusion de types de post
	 */
	private static $filtered_posttypes = array(
		// WP
		'wp_navigation',
		'wp_template_part',
		'wp_global_styles',
		'wp_template',
		'wp_block',
		'user_request',
		'attachment',
		'revision',
		'oembed_cache',
		'nav_menu_item',
		// GENERIC PLUGINS
		'ae_global_templates',
		'sdm_downloads',
		'mailpoet_page',
		'custom_css',
		'customize_changeset',
		'custom-css-js',
		// ELEMENTOR
		'elementor_library',
		'e-landing-page',
		// FLAMINGO
		'flamingo_contact',
		'flamingo_inbound',
		'flamingo_outbound',
		// WPFORMS
		'wpforms',
		'wpforms_log',
		// WPCF7
		'wpcf7_contact_form',
		// FORMINATOR
		'forminator_forms',
		'forminator_polls',
		'forminator_quizzes',
		// ACF
		'acf-field-group',
		'acf-field',
		'acf-post-type',
		// EAC Options page
		'eac_options_page',
		// WOOCOMMERCE
		'shop_order',
		'shop_coupon',
		'product_variation',
		'shop_order_placehold',
		'shop_order_refund',
	);

	/**
	 * @var $operateurs_comparaison
	 *
	 * Les options des opérateurs de comparaison
	 */
	private static $operateurs_comparaison = array(
		'IN'          => 'IN',
		'NOT IN'      => 'NOT IN',
		'BETWEEN'     => 'BETWEEN',
		'NOT BETWEEN' => 'NOT BETWEEN',
		'LIKE'        => 'LIKE',
		'NOT LIKE'    => 'NOT LIKE',
		'REGEXP'      => 'REGEXP',
		'NOT REGEXP'  => 'NOT REGEXP',
		'='           => '=',
		'!='          => '!=',
		'>'           => '>',
		'>='          => '>=',
		'<'           => '<',
		'<='          => '<=',
	);

	/**
	 * @var $unwanted_char_array
	 *
	 * Remplacement des caractères accentués et diacritiques
	 */
	private static $unwanted_char_array = array(
		'Š' => 'S',
		'š' => 's',
		'Ž' => 'Z',
		'ž' => 'z',
		'À' => 'A',
		'Á' => 'A',
		'Â' => 'A',
		'Ã' => 'A',
		'Ä' => 'AE',
		'Å' => 'A',
		'Æ' => 'A',
		'Ç' => 'C',
		'È' => 'E',
		'É' => 'E',
		'Ê' => 'E',
		'Ë' => 'E',
		'Ì' => 'I',
		'Í' => 'I',
		'Î' => 'I',
		'Ï' => 'I',
		'Ñ' => 'N',
		'Ò' => 'O',
		'Ó' => 'O',
		'Ô' => 'O',
		'Õ' => 'O',
		'Ö' => 'O',
		'Ø' => 'O',
		'Ù' => 'U',
		'Ú' => 'U',
		'Û' => 'U',
		'Ü' => 'U',
		'Ý' => 'Y',
		'Þ' => 'B',
		'ß' => 'ss',
		'à' => 'a',
		'á' => 'a',
		'â' => 'a',
		'ã' => 'a',
		'ä' => 'a',
		'å' => 'a',
		'æ' => 'a',
		'ç' => 'c',
		'è' => 'e',
		'é' => 'e',
		'ê' => 'e',
		'ë' => 'e',
		'ì' => 'i',
		'í' => 'i',
		'î' => 'i',
		'ï' => 'i',
		'ð' => 'o',
		'ñ' => 'n',
		'ò' => 'o',
		'ó' => 'o',
		'ô' => 'o',
		'õ' => 'o',
		'ö' => 'o',
		'ø' => 'o',
		'ù' => 'u',
		'ú' => 'u',
		'û' => 'u',
		'ý' => 'y',
		'þ' => 'b',
		'ÿ' => 'y',
		'Ğ' => 'G',
		'İ' => 'I',
		'Ş' => 'S',
		'ğ' => 'g',
		'ı' => 'i',
		'ş' => 's',
		'ü' => 'u',
		'ă' => 'a',
		'Ă' => 'A',
		'ș' => 's',
		'Ș' => 'S',
		'ț' => 't',
		'Ț' => 'T',
	);

	/**
	 * @var $social_networks
	 *
	 * La liste des réseaux sociaux et de leurs icones
	 */
	private static $social_networks = array(
		'email'     => array(
			'name' => 'Email',
			'icon' => '<i class="fas fa-envelope" aria-hidden="true"></i>',
		),
		'url'       => array(
			'name' => 'Website',
			'icon' => '<i class="fas fa-globe" aria-hidden="true"></i>',
		),
		'twitter'   => array(
			'name' => 'X (Twitter)',
			'icon' => '<i class="fab fa-x-twitter" aria-hidden="true"></i>',
			/** '<i class="fab fa-twitter" aria-hidden="true"></i>', */
		),
		'facebook'  => array(
			'name' => 'Facebook',
			'icon' => '<i class="fab fa-facebook-f" aria-hidden="true"></i>',
		),
		'instagram' => array(
			'name' => 'Instagram',
			'icon' => '<i class="fab fa-instagram" aria-hidden="true"></i>',
		),
		'linkedin'  => array(
			'name' => 'Linkedin',
			'icon' => '<i class="fab fa-linkedin" aria-hidden="true"></i>',
		),
		'youtube'   => array(
			'name' => 'Youtube',
			'icon' => '<i class="fab fa-youtube" aria-hidden="true"></i>',
		),
		'pinterest' => array(
			'name' => 'Pinterest',
			'icon' => '<i class="fab fa-pinterest" aria-hidden="true"></i>',
		),
		'tumblr'    => array(
			'name' => 'Tumblr',
			'icon' => '<i class="fab fa-tumblr" aria-hidden="true"></i>',
		),
		'flickr'    => array(
			'name' => 'Flickr',
			'icon' => '<i class="fab fa-flickr" aria-hidden="true"></i>',
		),
		'reddit'    => array(
			'name' => 'Reddit',
			'icon' => '<i class="fab fa-reddit" aria-hidden="true"></i>',
		),
		'tiktok'    => array(
			'name' => 'TikTok',
			'icon' => '<i class="fab fa-tiktok" aria-hidden="true"></i>',
		),
		'telegram'  => array(
			'name' => 'Telegram',
			'icon' => '<i class="fab fa-telegram" aria-hidden="true"></i>',
		),
		'quora'     => array(
			'name' => 'Quora',
			'icon' => '<i class="fab fa-quora" aria-hidden="true"></i>',
		),
		'twitch'    => array(
			'name' => 'Twitch',
			'icon' => '<i class="fab fa-twitch" aria-hidden="true"></i>',
		),
		'github'    => array(
			'name' => 'Github',
			'icon' => '<i class="fab fa-github" aria-hidden="true"></i>',
		),
		'spotify'   => array(
			'name' => 'Spotify',
			'icon' => '<i class="fab fa-spotify" aria-hidden="true"></i>',
		),
		'mastodon'  => array(
			'name' => 'Mastodon',
			'icon' => '<i class="fab fa-mastodon" aria-hidden="true"></i>',
		),
	);

	/**
	 * @var $wp_widgets
	 *
	 * La liste des widgets autorisés pour le composant Off-canvas
	 */
	private static $wp_widgets = array(
		'WP_Widget_Search',
		'WP_Widget_Pages',
		'WP_Widget_Calendar',
		'WP_Widget_Archives',
		'WP_Widget_Meta',
		'WP_Widget_Categories',
		'WP_Widget_Recent_Posts',
		'WP_Widget_Recent_Comments',
		'WP_Widget_RSS',
		'WP_Widget_Tag_Cloud',
	);

	/** Constructeur */
	public function __construct() {
		self::instance();
	}

	/**
	 * 'wp_check_filetype' retourne null si ce n'est pas un type mime de fichier activé (JSON)
	 *
	 * @var $relative_path Le chemin d'accès aux fichiers de configuration JSON
	 * @var $mimes Le type mime des fichiers recherchés
	 * @return la liste des fichiers d'un répertoire sous forme [url] => filename
	 */
	public static function get_directory_files_list( $relative_path = 'includes/config', $mimes = 'application/json' ) {
		$files_list = array( 'none' => esc_html__( 'Aucun', 'eac-components' ) );

		$path = EAC_ADDON_PATH . $relative_path;
		if ( $dir = opendir( $path ) ) {
			while ( $file = readdir( $dir ) ) {
				if ( '.' !== $file && '..' !== $file ) {
					if ( ! is_dir( $path . '/' . $file ) ) {
						$filetype = wp_check_filetype( basename( $file ), null );
						if ( $filetype['type'] === $mimes ) {
							// URL comme key et nom de fichier comme value
							$files_list[ EAC_ADDON_URL . $relative_path . '/' . basename( $file ) ] = basename( $file );
						}
					}
				}
			}
		}

		return $files_list;
	}

	/**
	 * @return la liste des ID des articles/pages/Cpt
	 *
	 * @Param {$posttype} Le type d'article 'post' ou 'page' ou custom post type
	 * @Return Un tableau "index::ID du post" => "Titre du post"
	 */
	public static function get_all_posts_by_id( $posttype = 'post' ) {
		$post_list = array();

		$posts = get_posts(
			array(
				'post_type'      => $posttype,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		if ( ! empty( $posts ) && ! is_wp_error( $posts ) ) {
			foreach ( $posts as $index => $value ) {
				// Ajoute l'index du tableau pour conserver le tri sur le titre
				$post_list[ $index . '::' . $value->ID ] = $value->post_title;
			}
		}

		return $post_list;
	}

	/**
	 * @return la liste des templates Elementor
	 *
	 * @param type de la taxonomie ('page' ou 'section')
	 */
	public static function get_elementor_templates( $type = 'page' ) {
		$post_list = array( '' => esc_html__( 'Select...', 'eac-components' ) );

		$data = get_posts(
			array(
				'cache_results'  => false,
				'post_type'      => Source_Local::CPT, //'elementor_library',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'sort_order'     => 'ASC',
				'tax_query'      => array(
					array(
						'taxonomy' => 'elementor_library_type',
						'field'    => 'slug',
						'terms'    => $type,
					),
				),
			)
		);

		if ( ! empty( $data ) && ! is_wp_error( $data ) ) {
			foreach ( $data as $key ) {
				$post_list[ $key->ID ] = $key->post_title;
			}
			ksort( $post_list );
		}

		return $post_list;
	}

	/**
	 * @return la liste des widgets standards
	 *
	 * https://gist.github.com/kingkool68/3418186
	 */
	public static function get_widgets_list() {
		global $wp_widget_factory, $wp_registered_sidebars;
		// global $wp_registered_widgets;
		$widgets = self::$wp_widgets;
		$options = array();

		// console_log($wp_registered_sidebars);
		// console_log($wp_registered_widgets['media_image-2']);

		// Boucle sur les Wigets standards
		foreach ( $wp_widget_factory->widgets as $key => $widget ) {
			if ( in_array( $key, $widgets, true ) ) {
				// $options[$key . "::" . $widget->widget_options['description']] = $widget->name;
				$options[ $key ] = $widget->name;
			}
		}

		// Boucle sur les sidebars
		$sidebars = get_option( 'sidebars_widgets' );

		// Boucle sur les sidebars actives et non vides
		foreach ( $sidebars as $sidebar_id => $sidebar_widgets ) {
			if ( 'wp_inactive_widgets' !== $sidebar_id && is_array( $sidebar_widgets ) && ! empty( $sidebar_widgets ) ) {
				$sidebar_name                                  = isset( $wp_registered_sidebars[ $sidebar_id ]['name'] ) ? $wp_registered_sidebars[ $sidebar_id ]['name'] : 'No name';
				$options[ $sidebar_id . '::' . $sidebar_name ] = 'Sidebar::' . $sidebar_name;

				/**
				foreach ($sidebar_widgets as $widget) {
					$name = $wp_registered_widgets[$widget]['callback'][0]->name;
					$option_name = $wp_registered_widgets[$widget]['callback'][0]->option_name;
					$id_base = $wp_registered_widgets[$widget]['callback'][0]->id_base;
					$key = $wp_registered_widgets[$widget]['params'][0]['number'];

					$widget_data = get_option($option_name);
					$data = $widget_data[$key];
					$title = ! empty($data['title']) ? $data['title'] : 'Empty title';
					//console_log($title."::".$widget."::".$name."::".$option_name."::".$id_base);
					//console_log($wp_registered_widgets[$widget]);
				}
				*/
			}
		}

		// Widget Search premier indice du tableau
		$search  = 'WP_Widget_Search';
		$options = array( $search => $options[ $search ] ) + $options;

		return $options;
	}

	/**
	 * @return la liste des localisations des menus
	 */
	public static function get_menus_location_list() {
		$options = array( '' => esc_html__( 'Select...', 'eac-components' ) );

		$locations = get_registered_nav_menus();
		$menus     = get_nav_menu_locations();

		foreach ( $locations as $key => $location_name ) {
			if ( isset( $menus[ $key ] ) && (int) $menus[ $key ] > 0 ) {
				$options[ $key ] = $locations[ $key ];
			}
		}
		return $options;
	}

	/**
	 * @return la liste des menus
	 */
	public static function get_menus_list() {
		$menus   = wp_get_nav_menus();
		$options = array( '' => esc_html__( 'Select...', 'eac-components' ) );

		foreach ( $menus as $menu ) {
			$options[ $menu->slug ] = $menu->name;
		}
		return $options;
	}

	/**
	 * @access public
	 *
	 * @param $plugin_path string plugin path
	 *
	 * @return boolean
	 */
	public static function is_plugin_installed( $plugin_path ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugins = get_plugins();
		return isset( $plugins[ $plugin_path ] );
	}

	/**
	 * @return la liste des noms des réseaux sociaux
	 */
	public static function get_all_social_medias_name() {
		$networks = self::$social_networks;
		$options  = array();

		foreach ( $networks as $index => $value ) {
			$options[ $index ] = $value['name'];
		}

		return $options;
	}

	/**
	 * @return la liste des icones des réseaux sociaux
	 */
	public static function get_all_social_medias_icon() {
		$networks = self::$social_networks;
		$options  = array();

		foreach ( $networks as $index => $value ) {
			$options[ $index ] = array(
				'name' => $value['name'],
				'icon' => $value['icon'],
			);
		}

		return $options;
	}

	/**
	 * @return l'icone d'un réseaux social
	 */
	public static function get_social_media_icon( $media = null ) {
		$networks = self::$social_networks;
		if ( array_key_exists( $media, $networks ) ) {
			return array(
				'name' => $networks[ $media ]['name'],
				'icon' => $networks[ $media ]['icon'],
			);
		}
		return null;
	}

	/**
	 * @return la liste des metadonnées supportées par les auteurs/users
	 */
	public static function get_unwanted_char() {
		$unwanted_char = self::$unwanted_char_array;

		/**
		 * Liste des caractères de remplacement
		 *
		 * Filtre pour ajouter des caractères de remplacement
		 *
		 * @param array $unwanted_char Liste des caractères
		 */
		$unwanted_char = apply_filters( 'eac/tools/unwanted_char', $unwanted_char );

		return $unwanted_char;
	}

	/**
	 * @return la liste des metadonnées supportées par les auteurs/users
	 */
	public static function get_supported_user_meta_fields() {
		$user_fields = self::$user_meta_fields;

		/**
		 * Liste des métadonnées supportées pour un auteur/user
		 *
		 * Filtrer/Ajouter métadonnées
		 *
		 * @param array $user_fields Liste des métadonnées
		 */
		$user_fields = apply_filters( 'eac/tools/user_meta_fields', $user_fields );

		return $user_fields;
	}

	/**
	 * @return la liste des opérateur de comparaison
	 */
	public static function get_operateurs_comparaison() {
		$operateurs = self::$operateurs_comparaison;

		/**
		 * Liste des opérateurs de comparaison des meta_query
		 *
		 * Filtrer/Ajouter des opérateurs de comparaison
		 *
		 * @param array $operateurs Liste des opérateurs de comparaison.
		 */
		$operateurs = apply_filters( 'eac/tools/operateurs_by_key', $operateurs );

		return $operateurs;
	}

	/**
	 * @return une liste de toutes les couleurs personnalisées et système
	 * Couleur format hexadecimal sans #
	 * Les 10 premières couleurs personnalisées
	 *
	 * @param { $custom} Bool: Ajouter les couleurs personnalisées
	 * @param { $system} Bool: Ajouter les couleurs système
	 */
	public static function get_palette_colors( $custom = true, $system = false ) {
		$palette = array();
		/**
		$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit_for_frontend();
		$system_kit_color = $kit->get_settings_for_display( 'system_colors' );
		$custom_kit_color = $kit->get_settings_for_display( 'custom_colors' );
		*/
		// Récupère l'ID du Kit 'elementor_active_kit'
		$elementor_active_kit = get_option( 'elementor_active_kit' );
		// Post meta qui contient les réglages du Kit avec la clé '_elementor_page_settings'
		$active_kit_settings = get_post_meta( $elementor_active_kit, '_elementor_page_settings', true );

		// Les custom_color existent
		if ( is_array( $active_kit_settings ) && maybe_unserialize( $active_kit_settings ) && isset( $active_kit_settings['custom_colors'] ) && $custom ) {
			$custom_colors = $active_kit_settings['custom_colors'];
			// Boucle sur les couleurs personnalisées
			foreach ( $custom_colors as $key => $custom_color ) {
				if ( $key < 10 ) { // Pas plus de 10
					$palette[] = $custom_color['color'];
				}
			}
		}

		// Les system_colors existent
		if ( is_array( $active_kit_settings ) && maybe_unserialize( $active_kit_settings ) && isset( $active_kit_settings['system_colors'] ) && $system ) {
			$system_colors = $active_kit_settings['system_colors'];
			// Boucle sur les couleurs système
			foreach ( $system_colors as $system_color ) {
				$palette[] = $system_color['color'];
			}
		}

		if ( empty( $palette ) ) {
			return $palette; }

		return implode( ',', $palette );
	}

	/**
	 * @return tous les types d'articles publics filtrés
	 */
	public static function get_filter_post_types() {
		$options   = array();
		$posttypes = self::$filtered_posttypes;

		/**
		 * Liste des opérateurs de comparaison des meta_query
		 *
		 * Ajouter/Supprimer des types d'articles
		 *
		 * @param array $posttypes Liste des types d'articles
		 */
		$posttypes = apply_filters( 'eac/tools/post_types', $posttypes );

		$post_types = get_post_types( array(), 'objects' );

		foreach ( $post_types as $post_type ) {
			if ( is_array( $posttypes ) && ! in_array( $post_type->name, $posttypes, true ) ) {
				$options[ $post_type->name ] = $post_type->name . '::' . $post_type->label;
			}
		}
		ksort( $options );
		return $options;
	}

	/**
	 * @return tous les types d'articles publics non filtrés
	 */
	public static function get_all_post_types() {
		$options    = array();
		$post_types = get_post_types( array(), 'objects' );

		foreach ( $post_types as $post_type ) {
			$options[ $post_type->name ] = $post_type->name . '::' . $post_type->label;
		}
		return $options;
	}

	/**
	 * Lecture du résumé ou du contenu pour un post et réduction au nombre de mots
	 *
	 * @param {$post_id} ID du post
	 * @param {$excerpt_length} Le nombre de mots à extraire
	 */
	public static function get_post_excerpt( $post_id, $excerpt_length ) {
		$the_post      = get_post( $post_id ); // Post/Page/Product ID
		$the_excerpt   = '';
		$the_post_type = $the_post->post_type;

		if ( 'product' === $the_post_type && function_exists( 'wc_get_product' ) ) {
			$product     = wc_get_product( $post_id );
			$the_excerpt = $product->get_description() ? $product->get_description() : $product->get_short_description();
		} elseif ( $the_post ) {
			$the_excerpt = self::get_gutenberg_content( $the_post );
		} else {
			return '[...]';
		}
		if ( empty( $the_excerpt ) ) {
			return '[...]';
		}

		// On supprime tous les tags html ou shortcode du résumé
		$the_excerpt = wp_strip_all_tags( strip_shortcodes( $the_excerpt ) );

		return wp_trim_words( $the_excerpt, $excerpt_length, '...' );
	}

	/**
	 * Extraction du résumé ou du contenu d'un article standard ou Gutenberg
	 *
	 * @param {$post} L'article
	 */
	public static function get_gutenberg_content( $post ) {
		$blocks = parse_blocks( $post->post_content );
		$excerpt = $post->post_excerpt;
		if ( ! empty( $excerpt ) ) {
			return $excerpt;
		}

		// C'est pas une page gutenberg
		if ( 1 === count( $blocks ) && null === $blocks[0]['blockName'] ) {
			return $post->post_content;
		} else {
			$the_excerpt = self::get_block_recursively( $blocks, 'core/paragraph' );
			return is_array( $the_excerpt ) && ! empty( $the_excerpt ) ? implode( ' ', $the_excerpt ) : '';
		}
	}

	/**
	 * Extraction du contenu du block_name avec recherche récursive pour les types de block group
	 *
	 * @param {$blocks} La liste des blocks à analyser
	 * @param {$block_name} Le nom du block recherché
	 */
	public static function get_block_recursively( $blocks, $block_name ) {
		$block_content = array();
		foreach ( $blocks as $block ) {
			if ( isset( $block['blockName'] ) && $block_name === $block['blockName'] ) {
				$block_content[] = $block['innerHTML'];
			} elseif ( is_array( $block['innerBlocks'] && ! empty( $block['innerBlocks'] ) ) ) {
				$block_content = array_merge( $block_content, self::get_block_recursively( $block['innerBlocks'], $block_name ) );
			}
		}
		return $block_content;
	}

	/**
	 * @return Format des images
	 */
	public static function get_thumbnail_sizes() {
		$options = array();
		$sizes   = get_intermediate_image_sizes();
		foreach ( $sizes as $s ) {
			$options[ $s ] = ucfirst( $s );
		}
		return $options;
	}

	/**
	 * Les options de tri des articles
	 */
	public static function get_post_orderby() {
		$options = array(
			'ID'             => esc_html__( 'Id', 'eac-components' ),
			'author'         => esc_html__( 'Auteur', 'eac-components' ),
			'title'          => esc_html__( 'Titre', 'eac-components' ),
			'date'           => esc_html__( 'Date', 'eac-components' ),
			'modified'       => esc_html__( 'Dernière modification', 'eac-components' ),
			'comment_count'  => esc_html__( 'Nombre de commentaires', 'eac-components' ),
			'meta_value_num' => esc_html__( 'Valeur meta numérique', 'eac-components' ),
		);

		/**
		 * Liste des options de tri
		 *
		 * Filtrer les options de tri
		 *
		 * @param array $options Liste des options de tri
		 */
		$options = apply_filters( 'eac/tools/post_orderby', $options );

		return $options;
	}

	/**
	 * @return un tableau filtré de tous les terms de WP
	 */
	public static function get_all_terms() {
		$all_terms     = array();
		$taxos         = array();
		$taxo_singular = array();
		$filtered_taxo = self::$filtered_taxonomies;

		$taxonomies = get_taxonomies( array(), 'objects' ); // Retourne un tableau d'objets

		// Boucle sur les taxonomies
		foreach ( $taxonomies as $taxonomy ) {
			if ( is_array( $filtered_taxo ) && ! in_array( $taxonomy->name, $filtered_taxo, true ) ) {
				$taxos[] = $taxonomy->name;
				$taxo_singular[ $taxonomy->name ] = $taxonomy->label;
			}
		}

		// Boucle sur les terms d'une taxonomie
		if ( ! empty( $taxos ) ) {
			foreach ( $taxos as $taxo ) {
				$terms = get_terms(
					array(
						'taxonomy'   => $taxo,
						'hide_empty' => true,
					)
				);

				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						$all_terms[ $taxo . '::' . $term->slug ] = $taxo_singular[ $taxo ] . '::' . $term->name;
					}
				}
			}
		}
		return $all_terms;
	}

	/**
	 * @return un tableau de tous les types de produits WooCommerce
	 */
	public static function get_product_post_types() {
		$options  = array();
		$products = array(
			'product' => esc_html__( 'Produits', 'eac-components' ),
			/**
			'product_variation' => esc_html__('Variations', 'eac-components'),
			'shop_coupon' => esc_html__('Codes promo', 'eac-components'),
			'shop_order' => esc_html__('Commandes', 'eac-components'),
			'shop_order_placehold' => esc_html__('Articles', 'eac-components'),
			'shop_order_refund' => esc_html__('Remboursements', 'eac-components'),
			*/
		);

		foreach ( $products as $key => $val ) {
			$options[ $key ] = $key . '::' . $val;
		}
		return $options;
	}

	/**
	 * @return un tableau filtré de toutes les taxonomies d'un produit
	 */
	public static function get_product_taxonomies() {
		$options = array();

		/** Retourne un tableau d'objet */
		$taxonomies = get_taxonomies( array( 'object_type' => array( 'product' ) ), 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy->name ] = $taxonomy->name . '::' . $taxonomy->label;
		}
		return $options;
	}

	/**
	 * @return un tableau filtré de tous les terms d'un produit
	 */
	public static function get_product_terms() {
		$all_terms     = array();
		$taxos         = array();
		$taxo_singular = array();

		/** Retourne un tableau d'objet */
		$taxonomies = get_taxonomies( array( 'object_type' => array( 'product' ) ), 'objects' );

		/** Boucle sur les taxonomies */
		foreach ( $taxonomies as $taxonomy ) {
			$taxos[] = $taxonomy->name;
			$taxo_singular[ $taxonomy->name ] = $taxonomy->label;
		}

		/** Boucle sur les terms d'une taxonomie */
		if ( ! empty( $taxos ) ) {
			foreach ( $taxos as $taxo ) {
				$terms = get_terms(
					array(
						'taxonomy'   => $taxo,
						'hide_empty' => true,
					)
				);

				if ( ! is_wp_error( $terms ) && count( $terms ) > 0 ) {
					foreach ( $terms as $term ) {
						$all_terms[ $taxo . '::' . $term->slug ] = $taxo_singular[ $taxo ] . '::' . $term->name;
					}
				}
			}
		}
		return $all_terms;
	}

	/**
	 * @return un tableau filtré de toutes les taxonomies de WP
	 * Méthode 'get_taxonomies' retourne 'objects' vs 'names' et affiche le couple 'name::label' dans la liste
	 */
	public static function get_all_taxonomies() {
		$options       = array();
		$filtered_taxo = self::$filtered_taxonomies;

		$taxonomies = get_taxonomies( '', 'objects' ); // Retourne un objet

		foreach ( $taxonomies as $taxonomy ) {
			if ( is_array( $filtered_taxo ) && ! in_array( $taxonomy->name, $filtered_taxo, true ) ) {
				$options[ $taxonomy->name ] = $taxonomy->name . '::' . $taxonomy->label;
			}
		}
		return $options;
	}

	/**
	 * @return un tableau filtré de toutes les taxonomies par leur nom
	 */
	public static function get_all_taxonomies_by_name() {
		$options       = array();
		$filtered_taxo = self::$filtered_taxonomies;

		/**
		 * Liste des taxonomies
		 *
		 * Filtre pour ajouter des taxonomies à exclure
		 *
		 * @param array $filtered_taxo Liste des taxonomies
		 */
		$filtered_taxo = apply_filters( 'eac/tools/taxonomies_by_name', $filtered_taxo );

		$taxonomies = get_taxonomies( '', 'objects' ); // Retourne un objet

		foreach ( $taxonomies as $taxonomy ) {
			if ( is_array( $filtered_taxo ) && ! in_array( $taxonomy->name, $filtered_taxo, true ) ) {
				$options[] = $taxonomy->name;
			}
		}
		return $options;
	}

	/**
	 * @return un array de toutes les pages avec le titre pour clé
	 */
	public static function get_pages_by_name() {
		$select_pages = array( '' => esc_html__( 'Select...', 'eac-components' ) );
		$args         = array(
			'sort_order'  => 'ASC',
			'sort_column' => 'post_title',
		);
		$pages        = get_pages( $args );

		foreach ( $pages as $page ) {
			$select_pages[ $page->post_title ] = ucfirst( $page->post_title );
		}
		return $select_pages;
	}

	/**
	 * @return un array de toutes les pages avec l'ID pour clé
	 */
	public static function get_pages_by_id() {
		$select_pages = array( '' => esc_html__( 'Select...', 'eac-components' ) );
		$args         = array(
			'sort_order'  => 'DESC',
			'sort_column' => 'post_title',
		);
		$pages        = get_pages( $args );

		foreach ( $pages as $page ) {
			$select_pages[ $page->ID ] = ucfirst( $page->post_title );
		}
		return $select_pages;
	}

	/**
	 * La date à convertir au format des réglages WP
	 *
	 * @param { $ori_date}   (string) La date à convertir
	 */
	public static function set_wp_format_date( $ori_date ) {
		if ( self::is_timestamp( $ori_date ) ) {
			return date( get_option( 'date_format' ), $ori_date );
		}

		if ( ! strtotime( $ori_date ) ) {
			return $ori_date; }

		return date_i18n( get_option( 'date_format' ), strtotime( $ori_date ) );
	}

	/**
	 * Recherche le format de la date
	 *
	 * @param { $ori_date}   (string) La date dont on recherche le format
	 */
	public static function get_wp_format_date( $ori_date ) {
		if ( preg_match( '/^[0-9]{4}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])$/', $ori_date ) ) {
			return 'Ymd';
		} elseif ( preg_match( '/^[0-9]{4}[\/]{1}(0[1-9]|1[0-2])[\/]{1}(0[1-9]|[1-2][0-9]|3[0-1])$/', $ori_date ) ) {
			return 'Y/m/d';
		} elseif ( preg_match( '/^[0-9]{4}[\-]{1}(0[1-9]|1[0-2])[\-]{1}(0[1-9]|[1-2][0-9]|3[0-1])$/', $ori_date ) ) {
			return 'Y-m-d';
		} elseif ( preg_match( '/^(0[1-9]|[1-2][0-9]|3[0-1])[\/]{1}(0[1-9]|1[0-2])[\/]{1}[0-9]{4}$/', $ori_date ) ) {
			return 'd/m/Y';
		} elseif ( preg_match( '/^(0[1-9]|[1-2][0-9]|3[0-1])[\-]{1}(0[1-9]|1[0-2])[\-]{1}[0-9]{4}$/', $ori_date ) ) {
			return 'd-m-Y';
		} elseif ( preg_match( '/^(0[1-9]|[1-2][0-9]|3[0-1])(0[1-9]|1[0-2])[0-9]{4}$/', $ori_date ) ) {
			return 'dmY';
		} elseif ( preg_match( '/^(0[1-9]|1[0-2])[\/]{1}(0[1-9]|[1-2][0-9]|3[0-1])[\/]{1}[0-9]{4}$/', $ori_date ) ) {
			return 'm/d/Y';
		} elseif ( preg_match( '/^(0[1-9]|1[0-2])[\-]{1}(0[1-9]|[1-2][0-9]|3[0-1])[\-]{1}[0-9]{4}$/', $ori_date ) ) {
			return 'm-d-Y';
		} elseif ( preg_match( '/^(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])[0-9]{4}$/', $ori_date ) ) {
			return 'mdY';
		}

		// Format WP définit dans le paramétrage
		return get_option( 'date_format' );
	}

	/**
	 * La date à convertir au format attendu (YYYY-MM-DD) par la propriété 'value' d'un 'meta_query'
	 *
	 * @param { $ori_date}   (string) La date à convertir
	 */
	public static function set_meta_value_date( $ori_date ) {
		$wp_format_entree = get_option( 'date_format' );  // Settings/General/Date Format (m-d-Y, m/d/Y, d-m-Y, d/m/Y, n/j/y=7/23/21)
		$wp_format_sortie = 'Y-m-d';                    // Format sortie attendu: AAAA-MM-DD

		$$date_maj = date_create_from_format( $wp_format_entree, $ori_date );

		if ( false === $date_maj ) {
			return $ori_date;
		}

		return $$date_maj->format( $wp_format_sortie );
	}

	/**
	 * Formatte la date si c'est une constante strtotime
	 *
	 * @param $la_date  (string) La date à checker
	 */
	public static function get_formated_date_value( $la_date ) {

		// Constante date du jour, -+1 mois, -+1 trimestre, -+1 an !
		if ( 'today' === $la_date ) {
			return date_i18n( 'Y-m-d' );
		} elseif ( 'today-1w' === $la_date ) {
			return date_i18n( 'Y-m-d', strtotime( '-1 week' ) );
		} elseif ( 'today-1m' === $la_date ) {
			return date_i18n( 'Y-m-d', strtotime( '-1 month' ) );
		} elseif ( 'today-1q' === $la_date ) {
			return date_i18n( 'Y-m-d', strtotime( '-3 month' ) );
		} elseif ( 'today-1y' === $la_date ) {
			return date_i18n( 'Y-m-d', strtotime( '-1 year' ) );
		} elseif ( 'today+1w' === $la_date ) {
			return date_i18n( 'Y-m-d', strtotime( '+1 week' ) );
		} elseif ( 'today+1m' === $la_date ) {
			return date_i18n( 'Y-m-d', strtotime( '+1 month' ) );
		} elseif ( 'today+1q' === $la_date ) {
			return date_i18n( 'Y-m-d', strtotime( '+3 month' ) );
		} elseif ( 'today+1y' === $la_date ) {
			return date_i18n( 'Y-m-d', strtotime( '+1 year' ) );
		} elseif ( self::is_timestamp( $la_date ) ) {
			return (string) $la_date;
		} else {
			return self::set_meta_value_date( $la_date );
		}
	}

	/**
	 * Check si la date est un timestamp unix
	 *
	 * @param { $la_date} (string) La date à checker
	 */
	public static function is_timestamp( $la_date ) {
		if ( ! is_numeric( $la_date ) ) {
			return false;
		}

		try {
			new \DateTime( '@' . $la_date );
		} catch ( \Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Garantir une seule instance de la class
	 *
	 * @return Eac_Tools_Util une instance de la class
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
