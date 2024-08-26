/**
 * Description: Cette class est déclenchée lorsque le composant 'eac-addon-reader-progress' est chargé dans la page
 *
 * https://developers.elementor.com/add-javascript-to-elementor-widgets/
 * 
 * @param $element. Le contenu de la section/container
 * @since 2.1.1
 */

class widgetReaderProgress extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetInstance: '.eac-reader-progress',
                progressContainer: '.progress',
                progressBadge: '.progress-badge',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        const components = {
            $targetInstance: this.$element.find(selectors.targetInstance),
            $progressContainer: this.$element.find(selectors.progressContainer),
            $progressBadge: this.$element.find(selectors.progressBadge),
            adminBar: document.getElementById('wpadminbar'),
        }
        if (components.adminBar) {
            components.$progressContainer.css('top', components.adminBar.clientHeight + 'px');
        }

        return components;
    }

    bindEvents() {
        jQuery(window).on('scroll', () => {
            let docElem = document.documentElement,
                docBody = document.body,
                windowScroll = docElem.scrollTop || docBody.scrollTop,
                windowHeight = (docElem.scrollHeight || docBody.scrollHeight) - window.innerHeight,
                percentValue = (windowScroll / windowHeight * 100).toFixed(4),
                percentDisplayValue = (windowScroll / windowHeight * 100).toFixed(0);

            if (this.elements.$progressBadge && Math.round(percentValue) > 2) {
                this.elements.$progressBadge.text(Math.round(percentValue) + '%');
            } else {
                this.elements.$progressBadge.text('');
            }
            this.elements.$progressContainer.css('width', percentValue + '%').attr('aria-valuenow', percentDisplayValue);
        });
    }
}

/**
 * Description: La class est créer lorsque le composant 'eac-addon-mega-menu' est chargé dans la page
 *
 * @param $element (ex: $scope)
 * @since 2.1.0
 */
jQuery(window).on('elementor/frontend/init', () => {
	elementorFrontend.elementsHandler.attachHandler('eac-addon-reader-progress', widgetReaderProgress);
});

/**
 * Description: La class est créer lorsque le composant 'eac-addon-reader-progress' est chargé dans la page
 *
 * @param $element (ex: $scope)
 * @since 2.1.0
 */
/*jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsReaderProgress = ($element) => {
        elementorFrontend.elementsHandler.addHandler(widgetReaderProgress, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/eac-addon-reader-progress.default', EacAddonsReaderProgress);
});*/
