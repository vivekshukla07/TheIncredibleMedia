<?php
/**
 * Class: Eac_Acf_Tags
 *
 * Description: Module de base pour construire les balises dynamques ACF
 * aux balises dynamiques ACF
 *
 * @since 1.7.5
 */

namespace EACCustomWidgets\Includes\Acf\DynamicTags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Eac_Acf_Tags {

	const TAG_DIR       = __DIR__ . '/tags/';
	const TAG_NAMESPACE = __NAMESPACE__ . '\\tags\\';

	/**
	 * $tags_list
	 *
	 * Liste des tags: Nom du fichier PHP => class
	 */
	private $tags_list = array(
		'acf-field-keys'         => 'Eac_Post_Acf_Keys',
		'acf-field-values'       => 'Eac_Post_Acf_Values',
		'acf-field-number'       => 'Eac_Acf_Number',
		'acf-field-text'         => 'Eac_Acf_Text',
		'acf-field-color'        => 'Eac_Acf_Color',
		'acf-field-date'         => 'Eac_Acf_Date',
		'acf-field-url'          => 'Eac_Acf_Url',
		'acf-field-image'        => 'Eac_Acf_Image',
		'acf-field-relational'   => 'Eac_Acf_Relational',
		'acf-field-file'         => 'Eac_Acf_File',
		'acf-field-group-text'   => 'Eac_Acf_Group_Text',
		'acf-field-group-url'    => 'Eac_Acf_Group_Url',
		'acf-field-group-image'  => 'Eac_Acf_Group_Image',
		'acf-field-group-color'  => 'Eac_Acf_Group_Color',
		'acf-field-group-date'   => 'Eac_Acf_Group_Date',
		'acf-field-group-number' => 'Eac_Acf_Group_Number',
		'acf-field-group-file'   => 'Eac_Acf_Group_File',
	);

	/**
	 * Constructeur de la class
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'elementor/dynamic_tags/register', array( $this, 'register_tags' ) );
	}

	/**
	 * Enregistre le groupe et les balises dynamiques des champs ACF
	 */
	public function register_tags( $dynamic_tags ) {
		// Enregistre le nouveau groupe avant d'enregistrer les Tags
		$dynamic_tags->register_group( 'eac-acf-groupe', array( 'title' => esc_html__( 'EAC ACF', 'eac-components' ) ) );

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

} new Eac_Acf_Tags();
