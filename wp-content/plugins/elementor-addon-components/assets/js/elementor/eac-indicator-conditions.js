/**
 * Description: Implémente l'indicateur de conditions dans le navigator
 *
 * @since 2.1.8
 */
window.addEventListener( 'elementor/init', () => {

	function navigatorAddConditionsIndicator() {
		elementor.navigator.indicators.displayConditions = {
			icon: 'code-bold',
			settingKeys: ['element_condition_active'],
			title: 'Display Conditions',
			section: 'eac_custom_element_condition'
		};
	}

	elementor.on('navigator:init', navigatorAddConditionsIndicator);
});
