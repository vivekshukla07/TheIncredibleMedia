<?php
/**
 * Surcharge de la méthode 'print_panel_template' de 'base-tag.php'
 * @since 2.1.8 Création du trait
 */

namespace EACCustomWidgets\Includes\Acf\DynamicTags\Tags\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait Panel_Template_Trait {

	public function fix_print_panel_template() {
		$panel_template_setting_key = $this->get_panel_template_setting_key();

		if ( ! $panel_template_setting_key ) {
			return;
		}
		?><#
		var key = <?php echo esc_html( $panel_template_setting_key ); ?>;

		if ( key ) {
			var settingsKey = "<?php echo esc_html( $panel_template_setting_key ); ?>";

			/*
			 * If the tag has controls,
			 * and key is an existing control (and not an old one),
			 * and the control has options (select/select2),
			 * and the key is an existing option (and not in a group or an old one).
			 */
			if ( controls && controls[settingsKey] ) {
				var controlSettings = controls[settingsKey];

				if ( controlSettings.options && controlSettings.options[ key ] ) {
					key = controlSettings.options[ key ];
				} else if ( controlSettings.groups ) {
					/*var label = _.filter( _.pluck( _.pluck( controls.key.groups, 'options' ), key ) );*/
					var label = _.filter( _.pluck( _.pluck( controlSettings.groups, 'options' ), key ) );

					if ( label[0] ) {
						key = label[0];
					}
				}
			}

			print( '(' + _.escape( key ) + ')' );
		}
		#>
		<?php
	}
}
