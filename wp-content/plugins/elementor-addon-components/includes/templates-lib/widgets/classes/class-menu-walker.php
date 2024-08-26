<?php
/**
 * Class: EAC_Menu_Walker
 *
 * Description: redéfinir le contenu et comportement d'un menu
 *
 * @since 2.1.0
 */

namespace EACCustomWidgets\Includes\TemplatesLib\Widgets\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EAC Nav Menu Walker.
 */
class EAC_Menu_Walker extends \Walker_Nav_Menu {

	/**
	 * Menu Settings du composant 'mega-menu.php'
	 *
	 * @var orientation
	 * @var megamenu
	 */
	private $orientation;
	private $megamenu;

	/**
	 * Class Constructor.
	 */
	public function __construct( $orientation = 'hrz', $mega = false ) {
		$this->orientation = $orientation;
		$this->megamenu    = $mega;
	}

	/**
	 * @var $output Variable retournée en fin de walker
	 * @var $depth  Profondeur du niveau
	 * @var $args   Arguments supplémentaires
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {

		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}

		$indent      = ( $depth ) ? str_repeat( $t, $depth ) : '';
		$classes     = array( 'mega-menu_sub-menu' );
		$class_names = implode( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
		$arias       = ' role="menu"';

		$output .= "{$n}{$indent}<ul$class_names . $arias>{$n}";
	}

	/**
	 * @var $output Variable retournée en fin de walker
	 * @var $item   Information sur l'item en cours
	 * @var $depth  Profondeur du niveau
	 * @var $args   Arguments supplémentaires
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}

		$indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

		$class_names = '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		// C'est un menu ou un sous-menu, on ajoute les class
		$classes[] = 0 === $depth ? 'mega-menu_top-item menu-item-' . esc_attr( $item->ID ) : 'mega-menu_sub-item menu-item-' . esc_attr( $item->ID );

		// Formate la class
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		// Ajout de l'ID au 'li' ainsi que le role
		$id      = apply_filters( 'nav_menu_item_id', $item->ID, $item, $args );
		$id      = $id ? ' id="menu-item-' . esc_attr( $id ) . '"' : '';
		$li_role = ' role="none"';

		$output .= $indent . '<li' . $id . $class_names . $li_role . '>';

		$balise = 'a';

		// Configuration des attributs du lien
		$atts           = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';

		// Ajout du 'href' de l'URL seulent si c'est un lien
		if ( 'a' === $balise ) {
			$atts['href'] = ! empty( $item->url ) ? $item->url : '';
		}

		$atts['aria-current'] = $item->current ? 'page' : '';

		/** Fix quatrième argument oublié */
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		// Ajout de notre propre class aux liens
		if ( empty( $atts['class'] ) ) {
			// Ajout d'une class si c'est un parent
			if ( 0 === $depth ) {
				$atts['class'] = 'mega-menu_top-link';
			} else {
				$atts['class'] = 'mega-menu_sub-link';
			}
		} else {
			if ( 0 === $depth ) {
				$atts['class'] = ' mega-menu_top-link';
			} else {
				$atts['class'] = ' mega-menu_sub-link';
			}
		}

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
				if ( 'href' === $attr && ! $args->walker->has_children ) {
					$value = esc_url( $value ) . ' role="menuitem"';
				} elseif ( 'href' === $attr && $args->walker->has_children ) {
					$value = esc_url( $value ) . ' role="menuitem" aria-haspopup="true" aria-expanded="false" aria-controls="sub-menu-' . esc_attr( $item->ID ) . '"';
				}
				$attributes .= ' ' . $attr . '=' . $value;
			}
		}

		$parent_icon = '';
		if ( $args->walker->has_children ) {
			$aria_label  = esc_html__( 'Ouvrir le sous menu', 'eac-components' );
			$parent_icon = '<span class="icon-menu-toggle" aria-label="' . $aria_label . '">
			    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 80 80" width="80" height="80" aria-hidden="true">
			        <path d="M70.3 13.8L40 66.3 9.7 13.8z"></path>
			    </svg>
	        </span>';
			/**
			$parent_icon = '<button class="icon-menu-toggle" type="button" tabindex="-1" aria-label="' . $aria_label . '">
				<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 80 80" width="80" height="80" aria-hidden="true">
					<path d="M70.3 13.8L40 66.3 9.7 13.8z"></path>
				</svg>
			</button>';
			 */
		}

		/** This filter is documented in wp-includes/post-template.php */
		$title = apply_filters( 'the_title', $item->title, $item->ID );

		/**
		 * Filtre le titre d'un item de menu
		 *
		 * @param string   $title The menu item's title.
		 * @param WP_Post  $item  The current menu item object.
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param int      $depth Depth of menu item. Used for padding.
		 */
		$title = apply_filters( 'nav_menu_item_title', $item->title, $item, $args, $depth );

		// Formatte le lien avec ses attributs
		$item_output  = $args->before;
		$item_output .= '<' . $balise . $attributes . '>';
		$item_output .= '<span class="mega-menu_item-title">' . $title . '</span>';
		$item_output .= $parent_icon . $args->link_after;
		$item_output .= '</' . $balise . '>';

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * @var $output Variable retournée en fin de walker
	 * @var $item   Information sur l'item en cours
	 * @var $depth  Profondeur du niveau
	 * @var $args   Arguments supplémentaires
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}

		if ( 0 === $depth ) {
			$output .= "</li>{$n}";
		} else {
			$output .= "</li>{$n}";
		}
	}

	/**
	 * @var $output Variable retournée en fin de walker
	 * @var $depth  Profondeur du niveau
	 * @var $args   Arguments supplémentaires
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {

		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent  = str_repeat( $t, $depth );
		$output .= "$indent</ul>{$n}";
	}
}
