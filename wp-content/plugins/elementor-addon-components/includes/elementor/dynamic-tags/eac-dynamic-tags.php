<?php
/**
 * Class: Eac_Dynamic_Tags
 *
 * Description: Enregistre les Balises Dynamiques (Dynamic Tags)
 * Met à disposition un ensemble de méthodes pour valoriser les options des listes de Tag
 * Ref: https://gist.github.com/iqbalrony/7ee129379965082fb6c62cf5db372752
 *
 * @since 1.6.0
 */

namespace EACCustomWidgets\Includes\Elementor\DynamicTags;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Eac_Dynamic_Tags {

	const TAG_DIR        = __DIR__ . '/tags/';
	const TAG_DIR_TRAITS = __DIR__ . '/tags/traits/';
	const TAG_NAMESPACE  = __NAMESPACE__ . '\\tags\\';

	/**
	 * $tags_list
	 *
	 * Liste des tags: Nom du fichier PHP => class
	 */
	private $tags_list = array(
		'url-post'                 => 'Eac_Posts_Tag',
		'url-cpt'                  => 'Eac_Cpts_Tag',
		'url-page'                 => 'Eac_Pages_Tag',
		'url-chart'                => 'Eac_Chart_Tag',
		'featured-image-url'       => 'Eac_Featured_Image_Url',
		'author-website-url'       => 'Eac_Author_Website_Url',
		'url-image-widget'         => 'Eac_External_Image_Url',
		'post-by-user'             => 'Eac_Post_User',
		'post-custom-field-keys'   => 'Eac_Post_Custom_Field_Keys',
		'post-custom-field-values' => 'Eac_Post_Custom_Field_Values',
		'post-elementor-tmpl'      => 'Eac_Elementor_Template',
		'post-title'               => 'Eac_Post_Title',
		'post-excerpt'             => 'Eac_Post_Excerpt',
		'post-gallery'             => 'Eac_Post_Gallery',
		'featured-image'           => 'Eac_Featured_Image',
		'user-info'                => 'Eac_User_Info',
		'page-title'               => 'Eac_Page_Title',
		'site-email'               => 'Eac_Site_Email',
		'site-url'                 => 'Eac_Site_URL',
		'site-server'              => 'Eac_Server_Var',
		'site-title'               => 'Eac_Site_Title',
		'site-tagline'             => 'Eac_Site_Tagline',
		'site-logo'                => 'Eac_Site_Logo',
		'site-stats'               => 'Eac_Post_Stats',
		'cookies'                  => 'Eac_Cookies_Var',
		'author-info'              => 'Eac_Author_Info',
		'author-name'              => 'Eac_Author_Name',
		'author-picture'           => 'Eac_Author_Picture',
		'author-social-network'    => 'Eac_Author_Social_Network',
		'featured-image-data'      => 'Eac_Featured_Image_Data',
		'user-picture'             => 'Eac_User_Picture',
		'shortcode'                => 'Eac_Shortcode_Tag',
		'lightbox'                 => 'Eac_Lightbox_Tag',
	);

	/** Constructeur de la class */
	public function __construct() {
		// Charge le trait 'page/post'
		// require_once self::TAG_DIR_TRAITS . 'page-post-trait.php';

		add_action( 'elementor/dynamic_tags/register', array( $this, 'register_tags' ) );
	}

	/** Enregistre les groupes et les balises dynamiques (Dynamic Tags) */
	public function register_tags( $dynamic_tags ) {
		// Enregistre les nouveaux groupes avant d'enregistrer les Tags
		$dynamic_tags->register_group( 'eac-action', array( 'title' => esc_html__( 'EAC Actions', 'eac-components' ) ) );
		$dynamic_tags->register_group( 'eac-author-groupe', array( 'title' => esc_html__( 'EAC Auteur', 'eac-components' ) ) );
		$dynamic_tags->register_group( 'eac-post', array( 'title' => esc_html__( 'EAC Article', 'eac-components' ) ) );
		$dynamic_tags->register_group( 'eac-site-groupe', array( 'title' => esc_html__( 'EAC Site', 'eac-components' ) ) );
		$dynamic_tags->register_group( 'eac-url', array( 'title' => esc_html__( 'EAC URLs', 'eac-components' ) ) );

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

} new Eac_Dynamic_Tags();
