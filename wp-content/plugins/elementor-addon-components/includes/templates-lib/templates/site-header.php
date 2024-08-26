<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modèle pour afficher le header des thèmes compatibles
 */
// Auteur du thème peut ajouter quelque chose avant
do_action( 'eac_before_render_site_header', $header_template_id );

// Filtre wpml
$header_template_id = apply_filters( 'wpml_object_id', $header_template_id, 'elementor_library', true );

echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $header_template_id ); // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

// Auteur du thème peut ajouter quelque chose après
do_action( 'eac_after_render_site_header', $header_template_id );
