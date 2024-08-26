<?php
/**
 * Class: EAC_Plugin
 *
 * Description:  Active l'administration du plugin avec les droits d'Admin
 * Charge la configuration, les widgets et les fonctionnalités
 *
 * @since 1.0.0
 */

namespace EACCustomWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\Core\Eac_Config_Elements;

/**
 * Main Plugin Class
 */
class EAC_Plugin {

	/**
	 * @var $instance
	 *
	 * Garantir une seule instance de la class
	 */
	private static $instance = null;

	/**
	 * @var suffix_css
	 * Debug des fichiers CSS
	 */
	private $suffix_css = EAC_STYLE_DEBUG ? '.css' : '.min.css';

	/**
	 * @var suffix_js
	 * Debug des fichiers JS
	 */
	private $suffix_js = EAC_SCRIPT_DEBUG ? '.js' : '.min.js';

	/**
	 * Constructeur
	 */
	private function __construct() {
		/** Filtre pour ajouter le type 'module' ES6 et 'defer' à certains scripts */
		add_filter( 'script_loader_tag', array( $this, 'add_script_type_attribute' ), 10, 3 );

		// require_once EAC_ADDON_PATH . 'core/autoload.php';

		/** Charge la configuration du plugin et des composants */
		require_once EAC_ADDON_PATH . 'core/eac-load-config.php';

		/** Ajoute une nouvelle capability 'eac_manage_options' aux rôles "editor' et 'shop_manager' */
		if ( current_user_can( 'manage_options' ) ) {
			$this->set_grant_option_page();
		}

		/** Charge la page d'administration du plugin */
		if ( current_user_can( 'manage_options' ) || current_user_can( Eac_Config_Elements::get_manage_options_name() ) ) {
			require_once EAC_ADDON_PATH . 'admin/settings/eac-load-components.php';
		}

		/** Charge les fonctionnalités */
		require_once EAC_ADDON_PATH . 'core/eac-load-features.php';

		/** Charge les catégories, les controls et les composants Elementor */
		require_once EAC_ADDON_PATH . 'core/eac-load-elements.php';

		/**
		 * Charge les scripts et les styles globaux
		 * Ajoute des colonnes et leurs contenus aux vues Elementor/Templates
		 */
		require_once EAC_ADDON_PATH . 'core/eac-load-scripts.php';
	}

	/**
	 * instance.
	 *
	 * Garantir une seule instance de la class
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Singletons should not be cloneable.
	 */
	protected function __clone() { }

	/**
	 * Singletons should not be restorable from strings.
	 */
	public function __wakeup() { }

	/**
	 * set_grant_option_page
	 *
	 * Ajoute une nouvelle capability 'eac_manage_options' aux rôles "editor' et 'shop_manager'
	 *
	 * 'wp_user_roles' de la table options
	 */
	private function set_grant_option_page() {
		/** Options ACF Options Page && Grant Options Page sont actives */
		$grant_option_page = Eac_Config_Elements::is_feature_active( 'acf-option-page' ) && Eac_Config_Elements::is_feature_active( 'grant-option-page' );
		$role_editor       = get_role( 'editor' );
		$role_shop_manager = get_role( 'shop_manager' );

		if ( $grant_option_page ) {
			if ( false === $role_editor->has_cap( Eac_Config_Elements::get_manage_options_name() ) ) {
				wp_roles()->add_cap( 'editor', Eac_Config_Elements::get_manage_options_name() );
			}

			if ( ! is_null( $role_shop_manager ) && false === $role_shop_manager->has_cap( Eac_Config_Elements::get_manage_options_name() ) ) {
				wp_roles()->add_cap( 'shop_manager', Eac_Config_Elements::get_manage_options_name() );
			}
		} else {
			if ( true === $role_editor->has_cap( Eac_Config_Elements::get_manage_options_name() ) ) {
				wp_roles()->remove_cap( 'editor', Eac_Config_Elements::get_manage_options_name() );
			}

			if ( ! is_null( $role_shop_manager ) && true === $role_shop_manager->has_cap( Eac_Config_Elements::get_manage_options_name() ) ) {
				wp_roles()->remove_cap( 'shop_manager', Eac_Config_Elements::get_manage_options_name() );
			}
		}
	}

	/**
	 * add_script_type_attribute
	 *
	 * Ajout des attributs 'type="module"' OU 'defer'
	 */
	public function add_script_type_attribute( $tag, $handle, $src ) {
		$module_scripts = array( 'instant-page', 'eac-acf-relation', 'eac-image-gallery', 'eac-advanced-gallery', 'eac-post-grid', 'eac-rss-reader', 'eac-news-ticker', 'eac-pinterest-rss' );
		$defer_scripts  = array( 'instant-page', 'eac-table-content', 'eac-site-search' );

		if ( in_array( $handle, $module_scripts, true ) ) {
			$tag = '<script type="module" src="' . esc_url( $src ) . '" id="' . esc_html( $handle ) . '-js"></script>' . chr( 10 );
		}

		if ( in_array( $handle, $defer_scripts, true ) ) {
			$tag = str_replace( ' src', ' defer src', $tag );
		}
		return $tag;
	}

	/**
	 * get_script_url
	 *
	 * Construit le chemin du fichier et ajoute l'extension relative à la constant globale
	 *
	 * @return le chemin absolu du fichier JS passé en paramètre
	 */
	public function get_script_url( $file ) {
		return esc_url( EAC_ADDON_URL . $file . $this->suffix_js );
	}

	/**
	 * get_style_url
	 *
	 * Construit le chemin du fichier et ajoute l'extension relative à la constant globale
	 *
	 * @return le chemin absolu du fichier CSS passé en paramètre
	 */
	public function get_style_url( $file ) {
			return esc_url( EAC_ADDON_URL . $file . $this->suffix_css );
	}

} EAC_Plugin::instance();
