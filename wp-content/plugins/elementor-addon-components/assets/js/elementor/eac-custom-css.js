/**
 * Description: Implémente le filtre et les événements pour gérer le champ CSS personnalisé
 *
 * @since 1.6.0
 */
(function ($) {
	'use strict';

	function addCustomCss(css, context) {
		if (!context) { return; }

		var model = context.model;
		var customCSS = model.get('settings').get('custom_css'); // 'control' ACE Editor
		var selector = '.elementor-element.elementor-element-' + model.get('id');

		if ('document' === model.get('elType')) {
			selector = elementor.config.document.settings.cssWrapperSelector;
		}

		/**
		 * Recherche de la poignée d'édition pour la section/colonne/widget/container
		 * La première si c'est une section/colonne il ne faut pas modifier les poignées internes
		 */
		var $elHandle = $(context.el).find('.elementor-editor-element-settings .elementor-editor-element-edit').first();

		if (customCSS) {
			css += customCSS.replace(/selector/g, selector);

			// Modification de la couleur de la poignée d'édition
			if ($elHandle.length > 0) {
				$elHandle.css('color', 'red');
			}
		} else {
			// Reset de la couleur de la poignée d'édition
			if ($elHandle.length > 0) {
				$elHandle.css('color', 'initial');
			}
		}

		return css;
	}
	elementor.hooks.addFilter('editor/style/styleText', addCustomCss);

	function addPageCustomCss() {
		var customCSS = elementor.settings.page.model.get('custom_css');
		if (customCSS) {
			customCSS = customCSS.replace(/selector/g, elementor.config.document.settings.cssWrapperSelector);
			elementor.settings.page.getControlsCSS().elements.$stylesheetElement.append(customCSS);
		}
	}
	elementor.on('preview:loaded', addPageCustomCss);

})(jQuery);
