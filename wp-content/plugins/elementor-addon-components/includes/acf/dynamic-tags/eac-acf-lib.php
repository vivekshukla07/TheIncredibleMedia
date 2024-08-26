<?php
/**
 * Class: Eac_Acf_Lib
 *
 * Description: Module ACF pour mettre à disposition les méthodes nécessaires
 * aux balises dynamiques ACF
 *
 * @since 1.7.5
 */

namespace EACCustomWidgets\Includes\Acf\DynamicTags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\Includes\Acf\Eac_Acf_Options_Page;

class Eac_Acf_Lib {

	/**
	 * @var $acf_field_types
	 *
	 * Les champs ACF supportés
	 */
	private static $acf_field_types = array(
		'text',
		'textarea',
		'wysiwyg',
		'select',
		'radio',
		'date_picker',
		'number',
		'true_false',
		'range',
		'checkbox',
	);

	/**
	 * Constructeur de la class
	 *
	 * @access public
	 */
	public function __construct() {
		/** Charge le traits pour corriger la méthode 'print_panel_template' */
		include_once __DIR__ . '/tags/traits/panel-template.php';

		add_filter( 'acf/pre_load_post_id', array( $this, 'fix_post_id_on_preview' ), 10, 2 );
	}

	/**
	 * fix_post_id_on_preview
	 *
	 * Fix des champs ACF en mode preview qui ne s'affichent pas pour Gutenberg ou Elementor
	 */
	public function fix_post_id_on_preview( $null, $post_id ) {
		if ( is_preview() ) {
			return ( null === $post_id ? get_the_ID() : get_the_ID() === $post_id ) ? get_the_ID() : $post_id;
		} else {
			$acf_post_id = isset( $post_id->ID ) ? $post_id->ID : $post_id;

			if ( ! empty( $acf_post_id ) ) {
				return $acf_post_id;
			} else {
				return $null;
			}
		}
	}

	/**
	 * get_acf_fields_options
	 *
	 * @param array  $field_types Les types de champ pour lequel les données seront retournées
	 * @param string $post_id l'ID du post
	 * @param string $add_group Ajouter les groupes ACF 'none', 'group' ou 'relational'
	 *
	 * @return array La liste des champs (Clé/Label) des groupes ACF et du type de champ 'GROUP' dans les groupes ACF
	 */
	public static function get_acf_fields_options( $field_types, $post_id = '', $add_group = 'none' ) {
		$postid         = empty( $post_id ) ? get_the_ID() : $post_id;
		$acf_groups     = array();
		$acf_groups_pt  = array();
		$acf_groups_cpt = array();
		$groups         = array();

		$acf_groups_pt = acf_get_field_groups( array( 'post_id' => $postid ) );

		if ( class_exists( Eac_Acf_Options_Page::class ) ) {
			$acf_groups_cpt = Eac_Acf_Options_Page::get_acf_field_groups();
		}

		$acf_groups = array_merge( $acf_groups_cpt, $acf_groups_pt );

		foreach ( $acf_groups as $group ) {
			$options = array();

			// Le groupe n'est pas désactivé
			if ( ! $group['active'] ) {
				continue;
			}

			if ( isset( $group['ID'] ) && ! empty( $group['ID'] ) ) {
				$fields = acf_get_fields( $group['ID'] );
			} else {
				$fields = acf_get_fields( $group );
			}

			// Pas de champ
			if ( ! is_array( $fields ) ) {
				continue;
			}

			/**
			 * none       = récupère que les champs de premier niveau
			 * group      = récupère que les champs inclus dans un type de champ GROUP
			 * relational = récupère tous les champs directs et dans un type de champ GROUP. Uniquement utilisé par le widget acf-relationship.php
			 */
			foreach ( $fields as $field ) {
				if ( in_array( $field['type'], $field_types, true ) && in_array( $add_group, array( 'none', 'relational' ), true ) ) {
					$key             = $field['key'] . '::' . $field['name'];
					$options[ $key ] = esc_html( $field['label'] );
				} elseif ( in_array( $field['type'], array( 'group' ), true ) && in_array( $add_group, array( 'group', 'relational' ), true ) ) {
					foreach ( $field['sub_fields'] as $sub_field ) {
						if ( in_array( $sub_field['type'], $field_types, true ) ) {
							// Pour maintenir la compatibilité ascendante
							if ( 'relational' === $add_group ) {
								$key = $sub_field['key'] . '::' . $sub_field['name'];
							} else {
								$key = $field['key'] . '::' . $sub_field['key'] . '::' . $sub_field['name'];
							}
							$options[ $key ] = esc_html( $sub_field['label'] );
						} elseif ( in_array( $sub_field['type'], array( 'group' ), true ) ) {
							foreach ( $sub_field['sub_fields'] as $nested_field ) {
								if ( in_array( $nested_field['type'], $field_types, true ) ) {
									$key             = $sub_field['key'] . '::' . $nested_field['key'] . '::' . $nested_field['name'];
									$options[ $key ] = esc_html( $nested_field['label'] );
								}
							}
						}
					}
				}
			}
			if ( empty( $options ) ) {
				continue;
			}

			$groups[] = array(
				'label'   => esc_html( $group['title'] ),
				'options' => $options,
			);
		}

		return $groups;
	}

	/**
	 * get_acf_field_name
	 *
	 * Retourne le field name complet d'un champ de type group 'field_group_key_field_key'
	 * Ou d'un groupe imbriqué dans un groupe
	 *
	 * @param $metavalue    La meta_value recherchée (field_xxxx)
	 * @param $metakey      La meta_key recherchée (field_name)
	 * @param $postid       L'ID de l'article
	 */
	public static function get_acf_field_name( $metavalue, $metakey, $postid ) {
		global $wpdb;
		$name = '';

		$meta_key = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key
				FROM {$wpdb->prefix}postmeta
				WHERE meta_value = %s
				AND post_id = %d
				AND meta_key LIKE %s",
				$metavalue,
				$postid,
				'%' . $metakey
			)
		);

		if ( $meta_key && ! empty( $meta_key ) && count( $meta_key ) === 1 ) { // Il ne doit y avoir qu'une seule meta_key
			$name = substr( current( (array) $meta_key )->meta_key, 1, 999 ); // Supprime l'underscore du début de la donnée
		}
		return $name;
	}

	/**
	 * get_all_acf_fields
	 *
	 * @param $posttype le type d'article à analyser
	 * @return array des champs ACF par leur groupe
	 */
	public static function get_all_acf_fields( $posttype ) {
		$options                   = array();
		$acf_field_groups          = array();
		$acf_supported_field_types = self::$acf_field_types;

		// Les groupes pour le type d'article
		$acf_groups = acf_get_field_groups( array( 'post_type' => $posttype ) );

		if ( ! empty( $acf_groups ) ) {
			foreach ( $acf_groups as $group ) {
				if ( ! $group['active'] ) {
					continue;
				}

				$fields = get_posts(
					array(
						'posts_per_page'         => -1,
						'post_type'              => 'acf-field',
						'orderby'                => 'menu_order',
						'order'                  => 'ASC',
						'suppress_filters'       => true, // DO NOT allow WPML to modify the query
						'post_parent'            => $group['ID'],
						'post_status'            => 'publish',
						'update_post_meta_cache' => false,
					)
				);

				if ( ! empty( $fields ) && ! is_wp_error( $fields ) ) {
					foreach ( $fields as $field ) {
						$pcontent = (array) maybe_unserialize( $field->post_content );
						if ( is_array( $acf_supported_field_types ) && in_array( $pcontent['type'], $acf_supported_field_types, true ) ) {
							$options[] = array(
								'group_title' => $group['title'],
								'excerpt'     => $field->post_excerpt,
								'post_title'  => $field->post_title,
							);
						}
					}
				}
			}
		}

		return $options;
	}

	/**
	 * get_acf_supported_fields
	 *
	 * @return la liste des champs ACF supportés
	 */
	public static function get_acf_supported_fields() {
		$acf_fields = self::$acf_field_types;

		/**
		 * Liste des types de champs supportés
		 *
		 * Filtrer/Ajouter des champ ACF
		 *
		 * @param array $acf_fields Liste des champs par leur slug
		 */
		$acf_fields = apply_filters( 'eac/tools/acf_field_types', $acf_fields );

		return $acf_fields;
	}

} new Eac_Acf_lib();
