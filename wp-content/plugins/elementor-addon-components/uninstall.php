<?php

/** Si uninstall.php n'est pas appelé par WordPress, crève charogne */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

global $wpdb;

$options        = $wpdb->get_results( $wpdb->prepare( "SELECT option_name FROM {$wpdb->prefix}options WHERE option_name LIKE %s", '%eac_option%' ) );
$transients     = $wpdb->get_results( $wpdb->prepare( "SELECT option_name FROM {$wpdb->prefix}options WHERE option_name LIKE %s", '%transient_eac_%' ) );
$nominatims     = $wpdb->get_results( $wpdb->prepare( "SELECT option_name FROM {$wpdb->prefix}options WHERE option_name LIKE %s", '%eac_nominatim_%' ) );
$menu_item_ids  = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE %s", '_eac_custom_nav_%' ) );
$attachment_ids = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE %s", 'eac_media_%' ) );

/** Nettoie les options */
if ( $options && ! empty( $options ) ) {
	foreach ( $options as $option ) {
		delete_option( $option->option_name );
	}
}

/** Nettoie les options de mise à jour et des caches par les transients */
if ( $transients && ! empty( $transients ) ) {
	foreach ( $transients as $transient ) {
		delete_option( $transient->option_name );
	}
}

/** Nettoie les options instagram nominatim du plugin et des transients */
if ( $nominatims && ! empty( $nominatims ) ) {
	foreach ( $nominatims as $nominatim ) {
		delete_option( $nominatim->option_name );
	}
}

/** Nettoie les metas données des items de menu */
if ( $menu_item_ids && ! empty( $menu_item_ids ) ) {
	foreach ( $menu_item_ids as $menu_item_id ) {
		delete_post_meta( $menu_item_id->post_id, '_eac_custom_nav_menu_item' );
	}
}

/** Nettoie les metas données des deux champs supplémentaires des images du widget advanced gallery */
if ( $attachment_ids && ! empty( $attachment_ids ) ) {
	foreach ( $attachment_ids as $attachment_id ) {
		delete_post_meta( $attachment_id->post_id, 'eac_media_url' );
		delete_post_meta( $attachment_id->post_id, 'eac_media_cat' );
	}
}

/** Suppression des capacités editor et shop_manager */
$role_editor = get_role( 'editor' );
if ( true === $role_editor->has_cap( 'eac_manage_options' ) ) {
	wp_roles()->remove_cap( 'editor', 'eac_manage_options' );
}

$role_shop_manager = get_role( 'shop_manager' );
if ( ! is_null( $role_shop_manager ) && true === $role_shop_manager->has_cap( 'eac_manage_options' ) ) {
	wp_roles()->remove_cap( 'shop_manager', 'eac_manage_options' );
}
