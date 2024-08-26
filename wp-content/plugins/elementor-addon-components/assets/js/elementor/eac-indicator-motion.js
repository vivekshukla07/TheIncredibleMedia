/**
 * Description: Implémente l'indicateur du motion effects dans le navigator
 *
 * @since 2.1.8
 */
window.addEventListener( 'elementor/init', () => {

	function navigatorAddMotionEffectsIndicator() {
		elementor.navigator.indicators.motionEffect = {
			icon: 'flip-box',
			settingKeys: ['eac_element_motion_effect'],
			title: 'Motion effects',
			section: 'eac_custom_element_effect'
		};
	}

	elementor.on('navigator:init', navigatorAddMotionEffectsIndicator);
});
