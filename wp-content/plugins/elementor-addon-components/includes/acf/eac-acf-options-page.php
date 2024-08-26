<?php
/**
 * Class: Eac_Acf_Options_Page
 *
 * Description: Construit et expose un nouveau type d'article
 * utilisé comme support pour les pages d'options ACF
 *
 * @since 1.8.4
 */

namespace EACCustomWidgets\Includes\Acf;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EACCustomWidgets\Core\Eac_Config_Elements;

/**
 * Administrateur ou nouvelle capacité
 * Check capacité 'eac_manage_options'
 */
if ( ! current_user_can( 'manage_options' ) && ! current_user_can( Eac_Config_Elements::get_manage_options_name() ) ) {
	return;
}

class Eac_Acf_Options_Page {

	/**
	 * @var $instance
	 *
	 * Garantir une seule instance de la class
	 */
	private static $instance = null;

	/**
	 * @var $acf_post_type
	 *
	 * Le libellé du type d'article
	 */
	private static $acf_post_type = 'eac_options_page';

	/**
	 * @var $options_page_name
	 *
	 * Le libellé de la page d'options
	 */
	private static $options_page_name = 'eac_options_page-';

	/**
	 * @var $option_page_taxonomy_slug
	 *
	 * Le slug de la taxonomie des pages d'options
	 */
	private static $option_page_taxonomy_slug = 'page_options_category';

	/**
	 * @var $option_page_category_slug
	 *
	 * Le slug de la catégorie des pages d'options
	 */
	private static $option_page_category_slug = 'page-options-category';

	/**
	 * @var $option_page_category_name
	 *
	 * Le nom de la catégorie des pages d'options
	 */
	private static $option_page_category_name = 'Page options category';

	/**
	 * @var $option_page_category_desc
	 *
	 * La description de la catégorie
	 */
	private static $option_page_category_desc = 'Reserved for ACF Options page';

	/**
	 * instance.
	 *
	 * Garantir une seule instance de la class
	 *
	 * @return Eac_Acf_Options_Page une instance de la class
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructeur de la class
	 *
	 * @access private
	 */
	private function __construct() {
		// Construction du post_type
		add_action( 'init', array( $this, 'register_option_page_post_type' ), 0 );

		// Ajout des colonnes et des données dans la vue du post_type
		add_filter( 'manage_' . self::$acf_post_type . '_posts_columns', array( $this, 'add_columns' ) );
		add_action( 'manage_' . self::$acf_post_type . '_posts_custom_column', array( $this, 'data_columns' ), 10, 2 );

		// Ajout du sous-menu
		add_action( 'admin_menu', array( $this, 'add_site_settings_to_menu' ) );

		// L'article page d'options est enregistré
		add_action( 'save_post_' . self::$acf_post_type, array( $this, 'save_options_page' ), 10, 2 );

		// L'article est mis dans la poubelle
		add_action( 'wp_trash_post', array( $this, 'delete_options_page' ), 10 );

		// Champ ACF supprimé. C'est l'action suivante qui fait le job
		// add_action('acf/delete_field', array($this, 'delete_acf_field'));

		// Groupe ACF modifié
		add_filter( 'acf/update_field_group', array( $this, 'update_acf_field_group' ) );

		// Groupe ACF est mis dans la poubelle
		add_action( 'acf/trash_field_group', array( $this, 'delete_acf_group' ) );

		// Ajout du support Elementor au post_type (Bouton Edit with Elementor)
		// add_action('elementor/init', array($this, 'add_elementor_support'));
	}

	/**
	 * update_acf_field_group
	 *
	 * Modifie toutes les options dans la table des options pour ce groupe
	 * Déclenchée par l'action ACF 'update_field_group'
	 *
	 * @var $group Le groupe cible à mettre à jour
	 */
	public function update_acf_field_group( $group_updated ) {
		// Récupère tous les articles du post type
		$articles = get_posts(
			array(
				'post_type'      => self::$acf_post_type,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			)
		);

		// Boucle sur tous les articles pour à nouveau sauvegarder les options
		if ( ! empty( $articles ) && ! is_wp_error( $articles ) ) {
			foreach ( $articles as $article ) {
				// Tous les groupes de champs pour cette article
				$groups = acf_get_field_groups( array( 'post_id' => $article->ID ) );
				foreach ( $groups as $group ) {
					if ( is_array( $group ) && ! empty( $group ) && $group['ID'] === $group_updated['ID'] ) {
						$this->save_options_page( $article->ID, $article );
					}
				}
			}
		}
	}

	/**
	 * delete_acf_group
	 *
	 * Supprime toutes les options (eac_options) des champs d'un groupe
	 * Déclenchée par l'action ACF 'trash_field_group'
	 *
	 * @var $group Le groupe de champ ACF mis à la poubelle
	 */
	public function delete_acf_group( $group ) {
		$fields = acf_get_fields( $group['ID'] );
		// Pas de champ
		if ( ! is_array( $fields ) ) {
			return;
		}

		foreach ( $fields as $field ) {
			$this->delete_acf_field( $field );
		}
	}

	/**
	 * delete_acf_field
	 *
	 * Supprime une option d'une page d'options dans la table 'eac_options'
	 *
	 * @var $field L'objet champ à supprimer
	 */
	public function delete_acf_field( $field ) {
		global $wpdb;
		$key = $field['key'];

		/**
		 * C'est un champ de type group
		 * On le supprime ainsi que tous les sous-champs
		 */
		if ( 'group' === $field['type'] ) {
			$option = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT option_name
					FROM {$wpdb->prefix}options
					WHERE option_name LIKE %s",
					'%-' . $wpdb->esc_like( $key )
				)
			);

			if ( $option && ! empty( $option ) && count( $option ) === 1 ) {
				$option_name = reset( $option )->option_name;
				delete_option( $option_name );
			}

			foreach ( $field['sub_fields'] as $sub_field ) {
				$key = $sub_field['key'];

				$option = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT option_name
						FROM {$wpdb->prefix}options
						WHERE option_name LIKE %s",
						'%-' . $wpdb->esc_like( $key )
					)
				);

				if ( $option && ! empty( $option ) && count( $option ) === 1 ) {
					$option_name = reset( $option )->option_name;
					delete_option( $option_name );
				}
			}
		} else {
			$option = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT option_name
					FROM {$wpdb->prefix}options
					WHERE option_name LIKE %s",
					'%-' . $wpdb->esc_like( $key )
				)
			);

			if ( $option && ! empty( $option ) && count( $option ) === 1 ) {
				$option_name = reset( $option )->option_name;
				delete_option( $option_name );
			}
		}
	}

	/**
	 * save_options_page
	 *
	 * Enregistre les champs ACF d'une page d'options dans la table 'eac_options'
	 * Trigger par WordPress 'save_post'
	 *
	 * @var $post_id ID de l'article
	 * @var $post l'objet article
	 */
	public function save_options_page( $post_id, $post ) {
		$post_title  = $post->post_title;
		$post_type   = $post->post_type;
		$post_status = $post->post_status;

		/** if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; } */

		// Ce n'est pas le type d'article attendu
		if ( self::$acf_post_type !== $post_type ) {
			return;
		}

		// L'article n'est pas publié
		if ( 'publish' !== $post_status ) {
			return;
		}

		/**
		 * Supprime toutes les options systématiquement
		 * dû au fait que la règle ACF du groupe a pu être changée
		 * ou que le groupe a été modifié (Méthode: update_acf_field_group)
		 */
		$this->delete_all_options_page( $post_id );

		$args = array(
			'id'           => '',
			'parent_group' => '',
			'title'        => $post_title,
			'name'         => '',
			'meta_key'     => '',
			'field_key'    => '',
			'field_type'   => '',
		);

		// Tous les groupes de champs pour cette article
		$groups = acf_get_field_groups( array( 'post_id' => $post_id ) );

		foreach ( $groups as $group ) {
			$continue_loop = false;

			// Le groupe n'est pas désactivé
			if ( ! $group['active'] ) {
				continue;
			}

			// Les règles
			foreach ( $group['location'] as $locations ) {
				if ( empty( $locations ) ) {
					continue;
				}

				foreach ( $locations as $rule ) {
					if ( 'post_type' === $rule['param'] && '==' === $rule['operator'] && self::$acf_post_type === $rule['value'] ) {
						$continue_loop = true;
					}
				}
			}

			if ( false === $continue_loop ) {
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

			$option_name = self::$options_page_name . $post_id . '-' . $group['key'];

			// La clé n'est pas utilisée dans une autre page d'options
			if ( '' === self::get_options_page_id( $group['key'] ) ) {
				$args['id']         = $group['ID']; // ID du groupe ACF
				$args['title']      = $group['title'];
				$args['field_key']  = $group['key'];
				$args['field_type'] = 'group';

				// Ajoute l'option du group principal
				update_option( $option_name, $args );
			}

			// Pour tous les champs fils
			$args['id'] = $post_id;

			foreach ( $fields as $field ) {
				// Le type du champ est un group
				if ( 'group' === $field['type'] ) {
					$option_name = self::$options_page_name . $post_id . '-' . $field['key'];

					if ( '' === self::get_options_page_id( $field['key'] ) ) {
						$args['title']        = $field['label'];
						$args['parent_group'] = $group['key'];
						$args['name']         = $field['name'];
						$args['meta_key']     = $field['name'];
						$args['field_key']    = $field['key'];
						$args['field_type']   = $field['type'];

						// Ajoute l'option du type de champ group
						update_option( $option_name, $args );
					}

					$sub_fields = have_rows( $field['key'] ) ? $field['sub_fields'] : false;

					// Parcourt les champs du type group
					if ( $sub_fields ) {
						foreach ( $sub_fields as $sub_field ) {
							$option_name = self::$options_page_name . $post_id . '-' . $sub_field['key'];

							if ( '' === self::get_options_page_id( $sub_field['key'] ) ) {
								$args['title']        = $sub_field['label'];
								$args['parent_group'] = $field['key'];
								$args['name']         = $sub_field['name'];
								$args['meta_key']     = $field['name'] . '_' . $sub_field['name']; // Concaténe le nom du type de groupe et le nom du champ voir 'eac_postmeta'
								$args['field_key']    = $sub_field['key'];
								$args['field_type']   = $sub_field['type'];

								// Ajoute l'option du champ
								update_option( $option_name, $args );
							}
						}
					}
				} else {
					$option_name = self::$options_page_name . $post_id . '-' . $field['key'];

					if ( '' === self::get_options_page_id( $field['key'] ) ) {
						$args['title']        = $field['label'];
						$args['parent_group'] = $group['key'];
						$args['name']         = $field['label'];
						$args['meta_key']     = $field['name'];
						$args['field_key']    = $field['key'];
						$args['field_type']   = $field['type'];

						// Ajoute l'option du champ
						update_option( $option_name, $args );
					}
				}
			} // Fields
		} // Group
	}

	/**
	 * delete_options_page
	 *
	 * Supprime une option d'une page d'options dans la table 'eac_options'
	 * Déclenchée par l'action WordPress 'wp_trash_post'
	 *
	 * @var $post_id ID de l'article
	 */
	public function delete_options_page( $post_id ) {
		$post      = get_post( $post_id );
		$post_type = $post->post_type;

		// Ce n'est pas le type d'article attendu
		if ( self::$acf_post_type !== $post_type ) {
			return;
		}

		$fields = get_field_objects( $post_id );

		if ( $fields && ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$option_name = self::$options_page_name . $post_id . '-' . $field['key'];
				delete_option( $option_name );
			}
		}
	}

	/**
	 * delete_all_options_page
	 *
	 * Supprime toutes les options d'une page d'options
	 *
	 * @var $post_id ID de l'article
	 */
	public function delete_all_options_page( $post_id ) {
		global $wpdb;
		$option_name = self::$options_page_name . $post_id;

		$options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name
				FROM {$wpdb->prefix}options
				WHERE option_name LIKE %s",
				$wpdb->esc_like( $option_name ) . '%'
			)
		);

		if ( $options && ! empty( $options ) ) {
			foreach ( $options as $option ) {
				delete_option( $option->option_name );
			}
		}
	}

	/**
	 * get_options_page_id
	 *
	 * Retourne l'ID de la page d'options dans la table 'eac_options'
	 *
	 * @var $key La clé du champ
	 */
	public static function get_options_page_id( $key ) {
		global $wpdb;

		// La clé du groupe
		// reset() and end() are nice because you don't need to know the key, just the position (first or last).
		// $group_key = reset($group)->post_name;

		/** Recherche de l'option par sa clé dans la table eac_options */
		$option = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value
				FROM {$wpdb->prefix}options
				WHERE option_name LIKE %s",
				'%-' . $wpdb->esc_like( $key )
			)
		);

		// Une seule option pour la clé
		if ( $option && ! empty( $option ) && count( $option ) === 1 ) {
			$name                          = reset( $option )->option_name;
			$value                         = maybe_unserialize( reset( $option )->option_value );
			list($prefix, $id, $field_key) = explode( '-', $name ); // eac_options_page-9602-field_63e0fbabf2cea

			// C'est un article et c'est la bonne clé du groupe
			// if (is_string(get_post_status($id)) && $group_key && ! empty($group_key) && $group_key == $value['group']) {
			if ( is_string( get_post_status( $id ) ) ) {
				return $id;
			}
		}
		return '';
	}

	/**
	 * get_post_type_name
	 *
	 * @return Le nom du type d'article
	 */
	public static function get_post_type_name() {
		return self::$acf_post_type;
	}

	/**
	 * register_option_page_post_type
	 *
	 * Enregistre un nouveau type d'article
	 */
	public function register_option_page_post_type() {
		$labels = array(
			'name'               => esc_html_x( 'ACF Options pages', 'Post type general name', 'eac-components' ),
			'singular_name'      => esc_html_x( 'ACF Options page', 'Post type singular name', 'eac-components' ),
			'menu_name'          => esc_html__( 'ACF Options', 'eac-components' ),
			'name_admin_bar'     => esc_html__( 'ACF Options', 'eac-components' ),
			'archives'           => esc_html__( 'Liste Archives', 'eac-components' ),
			'parent_item_colon'  => esc_html__( 'Parent', 'eac-components' ),
			'all_items'          => esc_html__( "Toutes les Pages d'Options", 'eac-components' ),
			'add_new_item'       => esc_html__( "Nouvelle Page d'Options", 'eac-components' ),
			'add_new'            => esc_html__( 'Ajouter', 'eac-components' ),
			'new_item'           => esc_html__( "Nouvelle Page d'Options", 'eac-components' ),
			'edit_item'          => esc_html__( "Ajouter une Page d'Options", 'eac-components' ),
			'update_item'        => esc_html__( "Modifier une Page d'Options", 'eac-components' ),
			'view_item'          => esc_html__( "Voir une Page d'Options", 'eac-components' ),
			'search_items'       => esc_html__( "Chercher dans les Pages d'Options", 'eac-components' ),
			'not_found'          => esc_html__( 'Pas trouvé', 'eac-components' ),
			'not_found_in_trash' => esc_html__( 'Pas trouvé dans la poubelle', 'eac-components' ),
		);

		$args = array(
			'label'               => esc_html__( "Liste des Pages d'Options", 'eac-components' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'author' ),
			'public'              => true,
			'rewrite'             => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			'hierarchical'        => false,
			'publicly_queryable'  => false,
			'query_var'           => false,
			'has_archive'         => false,
			'taxonomies'          => array( self::$option_page_taxonomy_slug ),
		);

		if ( ! post_type_exists( self::$acf_post_type ) ) {
			register_post_type( self::$acf_post_type, $args );
		}

		$taxo_args = array(
			'label'             => __( 'Page options catégories', 'eac-components' ),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_in_rest'      => false,
			'show_admin_column' => true,
			'query_var'         => false,
			'rewrite'           => false,
		);

		/*register_taxonomy( self::$option_page_taxonomy_slug, array( self::$acf_post_type ), $taxo_args );
		register_taxonomy_for_object_type( self::$option_page_taxonomy_slug, self::$acf_post_type );

		$cat_id = term_exists( self::$option_page_category_slug, self::$option_page_taxonomy_slug );

		if ( ! $cat_id ) {
			$cat_id = wp_insert_term(
				self::$option_page_category_name,
				self::$option_page_taxonomy_slug,
				array(
					'description' => self::$option_page_category_desc,
					'slug'        => self::$option_page_category_slug,
				)
			);
		}

		if ( ! is_wp_error( $cat_id ) && isset( $cat_id['term_id'] ) && 0 !== intval( $cat_id['term_id'] ) ) {
			$articles = get_posts(
				array(
					'post_type'      => self::$acf_post_type,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				)
			);
			// Boucle sur tous les articles pour ajouter la catégorie
			if ( ! is_wp_error( $articles ) && ! empty( $articles ) ) {
				foreach ( $articles as $article ) {
					$assign_term = wp_set_object_terms( $article->ID, intval( $cat_id['term_id'] ), self::$option_page_taxonomy_slug );
				}
			}
		}*/
	}

	/**
	 * add_site_settings_to_menu
	 *
	 * Ajout d'un sous-menu pour le type d'article au menu pricipal 'EAC composants'
	 * Check nouvelle capacité pour ajouter le sous-menu
	 */
	public function add_site_settings_to_menu() {
		$callback = 'edit.php?post_type=' . self::$acf_post_type;
		$title    = esc_html__( "ACF Pages d'Options", 'eac-components' );
		$option   = '';

		$current_user = wp_get_current_user();
		if ( $current_user->has_cap( Eac_Config_Elements::get_manage_options_name() ) ) {
			$option = Eac_Config_Elements::get_manage_options_name();
		} elseif ( $current_user->has_cap( 'manage_options' ) ) {
			$option = 'manage_options';
		}

		if ( ! empty( $option ) ) {
			add_submenu_page( EAC_DOMAIN_NAME, $title, $title, $option, $callback );
		}
	}

	/**
	 * add_columns
	 *
	 * Ajout des colonnes à la vue d'admin des pages
	 */
	public function add_columns( $columns ) {
		unset( $columns['date'] );
		return array_merge(
			$columns,
			array(
				'eac_type'        => esc_html__( 'Type', 'eac-components' ),
				'eac_group'       => esc_html__( 'Groupes', 'eac-components' ),
				'eac_field'       => esc_html__( 'Champs', 'eac-components' ),
				'eac_field_saved' => esc_html__( 'Enregistrés', 'eac-components' ),
				'eac_id'          => 'ID',
			)
		);
	}

	/**
	 * data_columns
	 *
	 * Ajoute le contenu des colonnes à la vue d'admin des pages d'options
	 */
	public function data_columns( $column_name, $post_id ) {
		?><style type="text/css">
			th#categories { width: 8%; }
			th#eac_type { width: 7%; }
		</style>
		<?php
		switch ( $column_name ) {
			case 'eac_type':
				echo esc_html( get_post_type_object( get_post_type( $post_id ) )->labels->singular_name );
				break;
			case 'eac_group':
				$title  = array();
				$groups = acf_get_field_groups( array( 'post_id' => $post_id ) );
				foreach ( $groups as $group ) {
					$title[] = $group['title'] . ' (' . $group['key'] . ')';
				}
				if ( ! empty( $title ) ) {
					echo implode( '<br /> ', $title );
				} else {
					echo 'Not found';
				}
				break;
			case 'eac_field':
				$id     = array();
				$groups = acf_get_field_groups( array( 'post_id' => $post_id ) );

				foreach ( $groups as $group ) {
					if ( isset( $group['ID'] ) && ! empty( $group['ID'] ) ) {
						$fields = acf_get_fields( $group['ID'] );
					} else {
						$fields = acf_get_fields( $group );
					}

					foreach ( $fields as $field ) {
						if ( 'group' === $field['type'] ) {
							$id[] = $field['name'] . ' (' . $field['key'] . ')';
							foreach ( $field['sub_fields'] as $sub_field ) {
								$id[] = $field['name'] . '_' . $sub_field['name'] . ' (' . $sub_field['key'] . ')';
							}
						} else {
							$id[] = $field['name'] . ' (' . $field['key'] . ')';
						}
					}
				}

				if ( ! empty( $id ) ) {
					echo implode( '<br />', $id );
				} else {
					echo 'Not found';
				}

				break;
			case 'eac_field_saved':
				$fields_count = 0;
				$saved        = esc_html__( 'Oui', 'eac-components' );
				$groups       = acf_get_field_groups( array( 'post_id' => $post_id ) );

				foreach ( $groups as $group ) {
					$continue_loop = false;

					foreach ( $group['location'] as $locations ) {
						if ( empty( $locations ) ) {
							continue;
						}

						foreach ( $locations as $rule ) {
							if ( 'post_type' === $rule['param'] && '==' === $rule['operator'] && self::$acf_post_type === $rule['value'] ) {
								$continue_loop = true;
							}
						}
					}

					if ( false === $continue_loop ) {
						continue;
					}

					if ( isset( $group['ID'] ) && ! empty( $group['ID'] ) ) {
						$fields = acf_get_fields( $group['ID'] );
					} else {
						$fields = acf_get_fields( $group );
					}

					$fields_count += count( $fields );

					foreach ( $fields as $field ) {
						if ( 'group' === $field['type'] ) {
							foreach ( $field['sub_fields'] as $sub_field ) {
								$option_name = self::$options_page_name . $post_id . '-' . $sub_field['key'];
								if ( get_option( $option_name ) === false ) {
									$saved = esc_html__( 'Non', 'eac-components' );
								}
							}
						} else {
							$option_name = self::$options_page_name . $post_id . '-' . $field['key'];
							if ( get_option( $option_name ) === false ) {
								$saved = esc_html__( 'Non', 'eac-components' );
							}
						}
					}
				}

				if ( 0 === $fields_count ) {
					echo '----';
				} else {
					echo esc_html( $saved );
				}
				break;
			case 'eac_id':
				echo absint( $post_id );
				break;
		}
	}

	/**
	 * add_elementor_support
	 *
	 * Ajoute le support Elementor au nouveau type d'article (Pas utilisé)
	 */
	public function add_elementor_support() {
		add_post_type_support( 'eac_options_page', 'elementor' );
	}

	/**
	 * update_json_field_group
	 *
	 * Update le fichier Group sous theme/acf-json
	 *
	 * @var $field_group L'objet ACF Group
	 */
	public static function update_json_field_group( $field_group ) {
		// vars
		$path = acf_get_setting( 'save_json' );
		$file = $field_group['key'] . '.json';

		// remove trailing slash
		$path = untrailingslashit( $path );

		// bail early if dir does not exist
		if ( ! is_writable( $path ) ) {
			// console_log( 'ACF failed to save field group to .json file. Path does not exist: ' . $path );
			return;
		}

		// load fields
		$fields = acf_get_fields( $field_group );

		// prepare fields
		$fields = acf_prepare_fields_for_export( $fields );

		// add to field group
		$field_group['fields'] = $fields;

		// extract field group ID
		$id = acf_extract_var( $field_group, 'ID' );

		// write file
		$f = fopen( "{$path}/{$file}", 'w' );
		fwrite( $f, acf_json_encode( $field_group ) );
		fclose( $f );
	}

	/**
	 * get_acf_field_groups
	 *
	 * Retourne la liste des groupes pour le post_type à l'exclusion des autres règles
	 *
	 * @var $post_type Post type
	 */
	public static function get_acf_field_groups( $post_type = '' ) {
		if ( '' === $post_type ) {
			$post_type = self::$acf_post_type;
		}

		// Besoin de créer un cache ou un transient pour ces données ?
		$groups           = array();
		$acf_field_groups = acf_get_field_groups();

		foreach ( $acf_field_groups as $acf_field_group ) {
			foreach ( $acf_field_group['location'] as $locations ) {
				if ( empty( $locations ) ) {
					continue;
				}

				foreach ( $locations as $rule ) {
					if ( 'post_type' === $rule['param'] && '==' === $rule['operator'] && $rule['value'] === $post_type ) {
						$groups[] = $acf_field_group;
					}
				}
			}
		}

		return $groups;
	}
}
Eac_Acf_Options_Page::instance();
