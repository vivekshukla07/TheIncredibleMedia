/**
 * Description: Cette méthode est déclenchée lorsque la section 'eac-addon-lecteur-audio' est chargée dans la page
 *
 * @since 1.0.0
 */

class widgetAudioPlayer extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                target: '.eac-lecteur-audio',
                targetId: '.la-lecteur-audio',
                targetSelect: '.select__options-items',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        let components = {
            $target: this.$element.find(selectors.target),
            $targetId: this.$element.find(selectors.targetId),
            $targetSelect: this.$element.find(selectors.targetSelect),
            selectedUrl: this.$element.find(selectors.targetSelect).eq(0).val(),
            elId: this.$element.data('id'),
        };
        return components;
    }

    onInit() {
        super.onInit();
        this.elements.$targetId.mediaPlayer({ thisSelector: this.elements.$targetId });
    }

    bindEvents() {
        this.elements.$targetSelect.on('change', (evt) => {
            this.elements.selectedUrl = jQuery(evt.currentTarget).val();
            jQuery('audio', this.elements.$targetId).remove();
			jQuery('svg', this.elements.$targetId).remove();
            const $wrapperAudio = jQuery('<audio/>', { class: 'listen', preload: 'none', 'data-size': '150', src: this.elements.selectedUrl, 'aria-labelledby': 'listbox_' + this.elements.elId });
            this.elements.$targetId.prepend($wrapperAudio);
            this.elements.$targetId.mediaPlayer({ thisSelector: this.elements.$targetId });
        });
    }
}

/**
 * Description: La class est créer lorsque le composant 'eac-addon-lecteur-audio' est chargé dans la page
 *
 * @param elements (Ex: $scope)
 * @since 2.1.0
 */
jQuery(window).on('elementor/frontend/init', () => {
    elementorFrontend.elementsHandler.attachHandler('eac-addon-lecteur-audio', widgetAudioPlayer);
});
