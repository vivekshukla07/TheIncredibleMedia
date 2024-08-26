
/**
 * Description: Cette méthode est déclenchée lorsque la section 'eac-addon-off-canvas' est chargée dans la page
 *
 * @param $element Le contenu de la section
 * @since 1.8.5
 */

class widgetOffCanvas extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetInstance: '.eac-off-canvas',
                targetWrapper: '.oc-offcanvas__wrapper',
                targetVisibleWrapper: '.oc-offcanvas__wrapper-canvas',
                targetOverlay: '.oc-offcanvas__wrapper-overlay',
                targetHeader: '.oc-offcanvas__canvas-header',
                targetCloseId: '.oc-offcanvas__canvas-close',
                targetContent: '.oc-offcanvas__canvas-content',
                targetFirstElement: '.oc-first-element',
                targetLastElement: '.oc-last-element',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        let components = {
            $targetInstance: this.$element.find(selectors.targetInstance),
            $targetWrapper: this.$element.find(selectors.targetWrapper),
            $targetVisibleWrapper: this.$element.find(selectors.targetVisibleWrapper),
            $targetOverlay: this.$element.find(selectors.targetOverlay),
            $targetHeader: this.$element.find(selectors.targetHeader),
            $targetCloseId: this.$element.find(selectors.targetCloseId),
            $targetContent: this.$element.find(selectors.targetContent),
            $targetFirstElement: this.$element.find(selectors.targetVisibleWrapper).find(selectors.targetFirstElement),
            $targetLastElement: this.$element.find(selectors.targetVisibleWrapper).find(selectors.targetLastElement),
            settings: this.$element.find(selectors.targetWrapper).data('settings'),
        };
        components.$triggerId = jQuery('#' + components.settings.data_id + ' .oc-offcanvas__wrapper-trigger');
        components.$triggerLink = components.$triggerId.prop("nodeName") === 'BUTTON' ? components.$triggerId : components.$triggerId.children(':first-child');
        components.$targetId = jQuery('#' + components.settings.data_canvas_id + '.oc-offcanvas__wrapper-canvas');

        return components;
    }

    onInit() {
        super.onInit();

        // Le canvas est à gauche, on inverse la direction du flex de l'entête
        if (this.elements.settings.data_position === 'left') {
            this.elements.$targetHeader.css({ 'flex-direction': 'row-reverse' });
        }
    }

    bindEvents() {
        this.elements.$triggerId.on('click', this.onButtonTriggerClick.bind(this));
        this.elements.$targetCloseId.add(this.elements.$targetOverlay).on('click', this.onCloseButtonOverlayClick.bind(this));
        this.elements.$targetCloseId.on('keydown', this.onCloseButtonWithKeyboard.bind(this));
        jQuery(document.body).on('keydown', this.onCloseBodyWithEscape.bind(this));
        this.elements.$targetVisibleWrapper.on('keydown', this.onNavigateWithKeyboard.bind(this));
    }

    onButtonTriggerClick(evt) {
        evt.preventDefault();
        this.elements.$triggerLink.attr('aria-expanded', 'true');
        jQuery('body').css('overflow-y', 'hidden');

        /* Cache le contenu systématiquement avant l'ouverture/fermeture */
        if (this.elements.$targetContent.css('display') === 'block') {
            this.elements.$targetContent.css({ 'display': 'none' });
        }

        if (this.elements.settings.data_position === 'top' || this.elements.settings.data_position === 'bottom') {
            this.elements.$targetId.animate({ height: 'toggle' }, 300, () => {
                this.elements.$targetContent.css({ 'display': 'block' });
                this.elements.$targetOverlay.css({ 'display': 'block' });
            });
        } else {
            this.elements.$targetId.animate({ width: 'toggle' }, 300, () => {
                this.elements.$targetContent.css({ 'display': 'block' });
                this.elements.$targetOverlay.css({ 'display': 'block' });
            });
        }
    }

    onCloseButtonOverlayClick(evt) {
        evt.preventDefault();
        this.elements.$triggerLink.attr('aria-expanded', 'false');
        jQuery('body').css('overflow-y', '');

        this.elements.$targetContent.css({ 'display': 'none' });
        this.elements.$targetOverlay.css({ 'display': 'none' });

        if (this.elements.settings.data_position === 'top' || this.elements.settings.data_position === 'bottom') {
            this.elements.$targetId.animate({ height: 'toggle' }, 300);
        } else {
            this.elements.$targetId.animate({ width: 'toggle' }, 300);
        }
    }

    onCloseButtonWithKeyboard(evt) {
        const id = evt.code || evt.key || 0;
        if (('Enter' === id || 'Space' === id) && this.elements.$targetId.css('display') === 'block') {
            evt.preventDefault();
            this.elements.$triggerLink.attr('aria-expanded', 'false');
            jQuery('body').css('overflow-y', '');

            this.elements.$targetContent.css({ 'display': 'none' });
            this.elements.$targetOverlay.css({ 'display': 'none' });

            if (this.elements.settings.data_position === 'top' || this.elements.settings.data_position === 'bottom') {
                this.elements.$targetId.animate({ height: 'toggle' }, 300);
            } else {
                this.elements.$targetId.animate({ width: 'toggle' }, 300);
            }
            this.elements.$triggerLink.trigger('focus');
        }
    }

    onCloseBodyWithEscape(evt) {
        const id = evt.code || evt.key || 0;
        if ('Escape' === id && this.elements.$targetId.css('display') === 'block') {
            this.elements.$triggerLink.attr('aria-expanded', 'false');
            jQuery('body').css('overflow-y', '');

            this.elements.$targetContent.css({ 'display': 'none' });
            this.elements.$targetOverlay.css({ 'display': 'none' });

            if (this.elements.settings.data_position === 'top' || this.elements.settings.data_position === 'bottom') {
                this.elements.$targetId.animate({ height: 'toggle' }, 300);
            } else {
                this.elements.$targetId.animate({ width: 'toggle' }, 300);
            }
            this.elements.$triggerLink.trigger('focus');
        } else if (('Enter' === id || 'Space' === id) && this.elements.$targetId.css('display') === 'block') {
            return false;
        }
    }

    onNavigateWithKeyboard(evt) {
        const id = evt.code || evt.key || 0;
        const lastElement = document.activeElement;

        if ('Tab' === id && !evt.shiftKey && this.elements.$targetId.css('display') === 'block' && jQuery(lastElement).hasClass('oc-last-element')) {
            evt.preventDefault();
            this.elements.$targetFirstElement.trigger('focus');
        } else if ('Tab' === id && evt.shiftKey && this.elements.$targetId.css('display') === 'block' && jQuery(lastElement).hasClass('oc-first-element')) {
            evt.preventDefault();
            this.elements.$targetFirstElement.trigger('focus');
        }
    }
}

/*jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsOffCanvas = ($element) => {
        elementorFrontend.elementsHandler.addHandler(widgetOffCanvas, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('eac-addon-off-canvas.default', EacAddonsOffCanvas);
});*/

/**
 * Description: La class est créer lorsque le composant 'eac-addon-off-canvas' est chargé dans la page
 *
 * @param elements (Ex: $scope)
 * @since 2.1.0
 */
jQuery(window).on('elementor/frontend/init', () => {
    elementorFrontend.elementsHandler.attachHandler('eac-addon-off-canvas', widgetOffCanvas);
});
