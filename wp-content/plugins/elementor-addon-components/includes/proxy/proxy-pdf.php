<?php
/**
 * Description: Collecte le contenu d'un fichier PDF distant
 *
 * @param {string} $_REQUEST['url'] l'url du flux à analyser
 * @param {string} $_REQUEST['nonce'] le nonce à tester
 * @return {Object[]} Le contenu du fichier PDF distant
 * @since 1.8.9
 */

namespace EACCustomWidgets\Includes\Proxy;

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once $parse_uri[0] . 'wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $_REQUEST['url'] ) || ! isset( $_REQUEST['id'] ) || ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'eac_file_viewer_nonce_' . sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) ) ) {
	header( 'Content-Type: text/plain' );
	echo esc_html__( 'Jeton invalide. Actualiser la page courante...', 'eac-components' );
	exit;
}

$file = filter_var( urldecode( $_REQUEST['url'] ), FILTER_SANITIZE_URL );

$file_source = wp_safe_remote_get(
	$file,
	array(
		'timeout' => 10,
		'headers' => array( 'Accept' => 'application/pdf' ),
	)
);

if ( is_wp_error( $file_source ) || 200 !== wp_remote_retrieve_response_code( $file_source ) ) {
	header( 'Content-Type: text/plain' );
	$error_message = wp_remote_retrieve_response_code( $file_source );

	if ( 404 === $error_message ) {
		echo '"' . esc_url( $file ) . '" => ' . esc_html__( "La page demandée n'existe pas.", 'eac-components' );
	} elseif ( 403 === $error_message ) {
		echo '"' . esc_url( $file ) . '" => ' . esc_html__( 'Accès refusé.', 'eac-components' );
	} elseif ( 401 === $error_message ) {
		echo '"' . esc_url( $file ) . '" => ' . esc_html__( 'Non autorisé.', 'eac-components' );
	} elseif ( 503 === $error_message ) {
		echo '"' . esc_url( $file ) . '" => ' . esc_html__( 'Service indisponible. Réessayer plus tard.', 'eac-components' );
	} elseif ( 405 === $error_message ) {
		echo '"' . esc_url( $file ) . '" => ' . esc_html__( 'Méthode non autorisée.', 'eac-components' );
	} elseif ( 429 === $error_message ) {
		echo '"' . esc_url( $file ) . '" => ' . esc_html__( 'Trop de requêtes.', 'eac-components' );
	} elseif ( 495 === $error_message ) {
		echo '"' . esc_url( $file ) . '" => ' . esc_html__( 'Certificat SSL invalide.', 'eac-components' );// SSL Certificate Error
	} elseif ( 496 === $error_message ) {
		echo '"' . esc_url( $file ) . '" => ' . esc_html__( 'Certificat SSL requis.', 'eac-components' );// SSL Certificate Required
	} elseif ( 500 === $error_message ) {
		echo '"' . esc_url( $file ) . '" => ' . esc_html__( 'Erreur Interne du Serveur.', 'eac-components' );
	} elseif ( 503 === $error_message ) {
		echo '"' . esc_url( $file ) . '" => ' . esc_html__( 'Service temporairement indisponible.', 'eac-components' );
	} else {
		echo esc_html__( 'HTTP: La requête a échoué.', 'eac-components' );
	}

	return false;
} elseif ( empty( wp_remote_retrieve_body( $file_source ) ) ) {
	header( 'Content-Type: text/plain' );
	echo '"' . esc_url( $file ) . '" => ' . esc_html__( 'Le contenu est vide', 'eac-components' );
	return false;
}

$pdf = wp_remote_retrieve_body( $file_source );

header( 'Content-Type: application/pdf' );
echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
