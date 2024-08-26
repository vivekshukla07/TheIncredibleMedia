<?php
/**
 * Description: Gère l'interface d'administration des composantrs EAC 'EAC Components'
 * et des options de la BDD.
 * Cette class est instanciée dans 'plugin.php' par le rôle administrateur.
 *
 * Charge le css 'eac-admin' et le script 'eac-admin' d'administration des composants.
 * Ajoute l'item 'EAC Components' dans les menus de la barre latérale
 * Charge le formulaire HTML de la page d'admin.
 *
 * @since 1.0.0
 */

namespace EACCustomWidgets\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

use EACCustomWidgets\EAC_Plugin;
use EACCustomWidgets\Core\Eac_Config_Elements;

class EAC_Admin_Settings {

	private $options_widgets      = '';
	private $options_features     = '';
	private $widgets_nonce        = 'eac_settings_widgets_nonce';         // nonce pour le formulaire des composants
	private $features_nonce       = 'eac_settings_features_nonce';        // nonce pour le formulaire des fonctionnalités
	private $wc_integration_nonce = 'eac_settings_wc_integration_nonce';  // nonce pour le formulaire d'intégration WC

	private $widgets_keys  = array(); // La liste des composants par leur slug
	private $features_keys = array(); // La liste des fonctionnalités par leur slug

	/** L'instance de la class */
	private static $instance = null;

	/**
	 * Constructor
	 *
	 * @param La liste des composants par leur slug
	 */
	private function __construct() {

		/** Le libellé des options de la BDD */
		$this->options_widgets  = Eac_Config_Elements::get_widgets_option_name();
		$this->options_features = Eac_Config_Elements::get_features_option_name();

		/** Affecte les tableaux d'éléments */
		$this->widgets_keys  = Eac_Config_Elements::get_widgets_active();
		$this->features_keys = Eac_Config_Elements::get_features_active();

		/** Enregistre les actions de création du sous-menu et de sauvegarde des formulaires */
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_page_scripts' ) );
		add_action( 'wp_ajax_save_settings', array( $this, 'save_settings' ) );
		add_action( 'wp_ajax_save_features', array( $this, 'save_features' ) );
		add_action( 'wp_ajax_save_wc_integration', array( $this, 'save_wc_integration' ) );
	}

	/** Singleton de la class */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * admin_menu
	 *
	 * Création du nouveau menu dans le dashboard
	 */
	public function admin_menu() {
		$plugin_name = esc_html__( 'EAC composants', 'eac-components' );
		$option      = '';

		$current_user = wp_get_current_user();
		if ( $current_user->has_cap( Eac_Config_Elements::get_manage_options_name() ) ) {
			$option = Eac_Config_Elements::get_manage_options_name();
		} elseif ( $current_user->has_cap( 'manage_options' ) ) {
			$option = 'manage_options';
		}

		if ( ! empty( $option ) ) {
			add_menu_page( $plugin_name, $plugin_name, $option, EAC_DOMAIN_NAME, array( $this, 'admin_page' ), 'dashicons-admin-tools', '58.9' );
		}
	}

	/**
	 * admin_page_scripts
	 *
	 * Charge le css 'eac-admin' et le script 'eac-admin' d'administration des composants
	 * Lance le chargement des options
	 */
	public function admin_page_scripts() {

		/** Le script de la page de configuration du plugin */
		wp_enqueue_script( 'eac-admin', EAC_Plugin::instance()->get_script_url( 'admin/js/eac-admin' ), array( 'jquery', 'jquery-ui-dialog' ), '1.0.0', true );

		/** Le style de la page de configuration du plugin */
		wp_enqueue_style( 'eac-admin', EAC_Plugin::instance()->get_style_url( 'admin/css/eac-admin' ), array(), EAC_PLUGIN_VERSION );

		wp_enqueue_style( 'wp-jquery-ui-dialog' );
	}

	/**
	 * admin_page
	 *
	 * Passe les paramètres au script 'eac-admin => eac-admin.js'
	 * Charge les templates de la page d'administration
	 */
	public function admin_page() {
		/** Options elements */
		$settings_components = array(
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'ajax_action' => 'save_settings',
			'ajax_nonce'  => wp_create_nonce( $this->widgets_nonce ),
		);
		wp_add_inline_script( 'eac-admin', 'var components = ' . wp_json_encode( $settings_components ), 'before' );

		/** Options features */
		$settings_features = array(
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'ajax_action' => 'save_features',
			'ajax_nonce'  => wp_create_nonce( $this->features_nonce ),
		);
		wp_add_inline_script( 'eac-admin', 'var features = ' . wp_json_encode( $settings_features ), 'before' );

		/** Options intégration WC */
		$settings_wc_integration = array(
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'ajax_action' => 'save_wc_integration',
			'ajax_nonce'  => wp_create_nonce( $this->wc_integration_nonce ),
		);
		wp_add_inline_script( 'eac-admin', 'var wcintegration = ' . wp_json_encode( $settings_wc_integration ), 'before' );

		/** Charge les templates */
		require_once 'eac-components-header.php';
		require_once 'eac-components-tabs-nav.php';
		?>
		<div class="tabs-stage">
			<?php require_once 'eac-components-tab1.php'; ?>
			<?php require_once 'eac-components-tab4.php'; ?>
			<?php
			if ( Eac_Config_Elements::is_widget_active( 'woo-product-grid' ) ) {
				require_once 'eac-components-tab6.php';
			}
			?>
		</div>
		<?php require_once 'eac-admin-popup-acf.php'; ?>
		<?php require_once 'eac-admin-popup-grant-option.php'; ?>
		<?php require_once 'eac-admin-popup-grant-medias.php'; ?>
		<?php
	}

	/**
	 * save_features
	 *
	 * Méthode appelée depuis le script 'eac-admin'
	 * Sauvegarde les options dans la table Options de la BDD
	 */
	public function save_features() {
		// Vérification du nonce pour cette action
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), $this->features_nonce ) ) {
			wp_send_json_error( esc_html__( "Les réglages n'ont pu être enregistrés (nonce)", 'eac-components' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Vous ne pouvez pas modifier les réglages', 'eac-components' ) );
		}

		// Les champs 'fields' sélectionnés 'on' sont serialisés dans 'eac-admin.js'
		if ( isset( $_POST['fields'] ) ) {
			parse_str( $_POST['fields'], $settings_on );
		} else {
			wp_send_json_error( esc_html__( "Les réglages n'ont pu être enregistrés (champs)", 'eac-components' ) );
		}

		$settings_features = array();
		$keys              = array_keys( $this->features_keys );

		// La liste des fonctionnalités activés
		foreach ( $keys as $key ) {
			$settings_features[ $key ] = boolval( isset( $settings_on[ $key ] ) ? 1 : 0 );
		}

		// Update de la BDD
		update_option( $this->options_features, $settings_features );

		// Met à jour les options pour le template template 'tab2'
		$this->features_keys = get_option( $this->options_features );

		// retourne 'success' au script JS
		wp_send_json_success( esc_html__( 'Réglages enregistrés', 'eac-components' ) );
	}

	/**
	 * save_settings
	 *
	 * Méthode appelée depuis le script 'eac-admin'
	 * Sauvegarde les options dans la table Options de la BDD
	 */
	public function save_settings() {
		// Vérification du nonce pour cette action
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), $this->widgets_nonce ) ) {
			wp_send_json_error( esc_html__( "Les réglages n'ont pu être enregistrés (nonce)", 'eac-components' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Vous ne pouvez pas modifier les réglages', 'eac-components' ) );
		}

		// Les champs 'fields' sélectionnés 'on' sont serialisés dans 'eac-admin.js'
		if ( isset( $_POST['fields'] ) ) {
			parse_str( $_POST['fields'], $settings_on );
		} else {
			wp_send_json_error( esc_html__( "Les réglages n'ont pu être enregistrés (champs)", 'eac-components' ) );
		}

		$settings_keys = array();
		$keys          = array_keys( $this->widgets_keys );

		// La liste des options de tous les composants activés
		foreach ( $keys as $key ) {
			$settings_keys[ $key ] = boolval( isset( $settings_on[ $key ] ) ? 1 : 0 );
		}

		// Update de la BDD
		update_option( $this->options_widgets, $settings_keys );

		// Met à jour les options pour le template template 'tab1'
		$this->widgets_keys = get_option( $this->options_widgets );

		// retourne 'success' au script JS
		wp_send_json_success( esc_html__( 'Réglages enregistrés', 'eac-components' ) );
	}

	/**
	 * save_wc_integration
	 *
	 * Méthode appelée depuis le script 'eac-admin'
	 * Sauvegarde les options de l'intégration WC dans la table Options de la BDD
	 */
	public function save_wc_integration() {
		$woo_shop_args = Eac_Config_Elements::get_woo_hooks_option_args();

		/** Vérification du nonce pour cette action */
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), $this->wc_integration_nonce ) ) {
			wp_send_json_error( esc_html__( "Les réglages n'ont pu être enregistrés (nonce)", 'eac-components' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Vous ne pouvez pas modifier les réglages', 'eac-components' ) );
		}

		/** Les champs 'fields' sont serialisés dans 'eac-admin.js' */
		if ( isset( $_POST['fields'] ) ) {
			parse_str( $_POST['fields'], $settings_on );
		} else {
			wp_send_json_error( esc_html__( "Les réglages n'ont pu être enregistrés (champs)", 'eac-components' ) );
		}

		/** WooCommerce n'est pas installé */
		if ( ! class_exists( 'WooCommerce' ) ) {
			wp_send_json_error( esc_html__( "WooCommerce n'est pas installé/activé sur votre site", 'eac-components' ) );
		}

		/** ID et URL de la page grille de produit */
		if ( isset( $settings_on['wc_product_select_page'] ) && '' !== $settings_on['wc_product_select_page'] ) {
			$woo_shop_args['product-page']['shop']['url'] = esc_url_raw( get_permalink( absint( $settings_on['wc_product_select_page'] ) ) );
			$woo_shop_args['product-page']['shop']['id']  = absint( $settings_on['wc_product_select_page'] );
		} else {
			$woo_shop_args['product-page']['shop']['url'] = '';
			$woo_shop_args['product-page']['shop']['id']  = (int) 0;
		}

		/**
		 * Les boutons de la page panier
		 * Les URLs du breadcrumb de la page product
		 * Les URLs des métas de la page produit
		 */
		if ( '' !== $woo_shop_args['product-page']['shop']['url'] ) {
			$woo_shop_args['product-page']['redirect_buttons']   = boolval( isset( $settings_on['wc_product_redirect_url'] ) ? 1 : 0 );
			$woo_shop_args['product-page']['breadcrumb']         = boolval( isset( $settings_on['wc_product_breadcrumb'] ) ? 1 : 0 );
			$woo_shop_args['product-page']['metas']              = boolval( isset( $settings_on['wc_product_metas'] ) ? 1 : 0 );
		} else {
			$woo_shop_args['product-page']['redirect_buttons']   = boolval( 0 );
			$woo_shop_args['product-page']['breadcrumb']         = boolval( 0 );
			$woo_shop_args['product-page']['metas']              = boolval( 0 );
		}

		/** Le site en catalogue */
		$woo_shop_args['catalog']['active'] = boolval( isset( $settings_on['wc_product_catalog'] ) ? 1 : 0 );

		/** Message dans la page du produit 'request a quote' et redirection des pages */
		if ( $woo_shop_args['catalog']['active'] ) {
			$woo_shop_args['catalog']['request_quote'] = boolval( isset( $settings_on['wc_product_request'] ) ? 1 : 0 );
			if ( '' !== $woo_shop_args['product-page']['shop']['url'] ) {
				$woo_shop_args['redirect_pages'] = boolval( isset( $settings_on['wc_product_redirect_pages'] ) ? 1 : 0 );
			} else {
				$woo_shop_args['redirect_pages'] = boolval( 0 );
			}
		} else {
			$woo_shop_args['catalog']['request_quote'] = boolval( 0 );
			$woo_shop_args['redirect_pages']           = boolval( 0 );
		}

		/** Update de la BDD */
		update_option( Eac_Config_Elements::get_woo_hooks_option_name(), $woo_shop_args );

		/** retourne 'success' au script JS */
		wp_send_json_success( esc_html__( 'Réglages enregistrés', 'eac-components' ) );
	}
} EAC_Admin_Settings::instance();
