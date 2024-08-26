/**
 * Description: Implémente l'indicateur du CSS personnalisé dans le navigator
 *
 * @since 2.1.8
 */
window.addEventListener( 'elementor/init', () => {

	function navigatorAddCustomCssIndicator() {
		elementor.navigator.indicators.customCSS = {
			icon: 'custom-css',
			settingKeys: ['custom_css'],
			title: 'Custom CSS',
			section: 'eac_custom_element_css'
		};
	}

	elementor.on('navigator:init', navigatorAddCustomCssIndicator);
});