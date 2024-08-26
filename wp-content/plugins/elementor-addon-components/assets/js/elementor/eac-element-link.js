/**
 * Description: Cette méthode est déclenchée lorsque le control 'eac_element_link' est chargée dans la page
 * 
 * @since 1.8.4
 */

class elementLink extends elementorModules.frontend.handlers.Base {

    getDefaultElements() {
        return {
            $target: this.$element,
            isEditMode: Boolean(elementorFrontend.isEditMode()),
            settings: this.$element.data('eac_settings-link') || {},
        };
    }

    onInit() {
        super.onInit();

        // Erreur settings et dans l'éditeur
        if (Object.keys(this.elements.settings).length === 0 || this.elements.isEditMode) { return; }
        const url = decodeURIComponent(this.elements.settings.url);
        this.elements.$target.append('<a ' + url + '><span class="eac-element-link"></span></a>');
    }
}

jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsElementLink = ($element) => {
        elementorFrontend.elementsHandler.addHandler(elementLink, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/section', EacAddonsElementLink);
    elementorFrontend.hooks.addAction('frontend/element_ready/column', EacAddonsElementLink);
    elementorFrontend.hooks.addAction('frontend/element_ready/container', EacAddonsElementLink);
});
