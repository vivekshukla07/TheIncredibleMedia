<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div id="eac-dialog_grant-medias" class="hidden" style="max-width:800px">
	<p><?php esc_html_e( 'En activant cette option vous pourrez ajouter dans la librairie des fichiers de type JSON ou ajouter une URL externe vers une source au format JSON.', 'eac-components' ); ?></p>
	<p><?php esc_html_e( 'Ce type de fichier représente un risque potentiel de sécurité.', 'eac-components' ); ?></p>
	<p><?php esc_html_e( "Le plugin n'effectue aucune analyse de son contenu.", 'eac-components' ); ?></p>
	<p style='color:red;font-weight:600;'><?php esc_html_e( "Vous devez donc vous assurer de leurs provenances avant d'activer cette option.", 'eac-components' ); ?></p>
</div>
