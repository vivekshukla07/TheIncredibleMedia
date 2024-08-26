
/** https://developers.elementor.com/a-new-method-for-attaching-a-js-handler-to-an-element/ */

class widgetHtmlSitemap extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetInstance: '.eac-html-sitemap',
                targetSitemap: '.site-map-article',
                targetSkipGrid: '.eac-skip-grid',
                targetLink: 'a',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        const components = {
            $targetInstance: this.$element.find(selectors.targetInstance),
            $targetSitemap: this.$element.find(selectors.targetSitemap),
            $targetFirstlink: this.$element.find(selectors.targetInstance).find(selectors.targetLink).first(),
            $targetLastlink: this.$element.find(selectors.targetInstance).find(selectors.targetLink).last(),
            $targetTitleTop: this.$element.find(selectors.targetInstance).find('.sitemap-title-top'),
            $targetSkipGrid: this.$element.find(selectors.targetSkipGrid),
        }
        return components;
    }

    bindEvents() {
        this.elements.$targetSitemap.on('keydown', this.setKeyboardEvents.bind(this));
    }

    setKeyboardEvents(evt) {
        /**
         * sitemap-title-top div frère au-dessus de sitemap-posts-list
         * sitemap-posts-list parent des liens
         *
         * 'keydown' élément de départ qui AVAIT le focus
         */
        const lastElement = document.activeElement;
        let currentTitleTop = lastElement;
        let currentTitleTopIndex = this.elements.$targetTitleTop.index(jQuery(currentTitleTop));
        let $currentTitleTopLinks = null;
        const id = evt.code || evt.key || 0;

        /** La liste des liens sous chaque titre du sitemap */
        if (jQuery(currentTitleTop).hasClass('sitemap-title-top')) {
            $currentTitleTopLinks = jQuery(currentTitleTop).next('div.sitemap-posts-list').find('a');
        } else {
            $currentTitleTopLinks = jQuery(currentTitleTop).parents('div.sitemap-posts-list').find('a');
        }

        if ('Tab' === id && !evt.shiftKey) {
            let currentElement = lastElement;
            evt.preventDefault();

            if (jQuery(currentElement).attr('href')) {
                return;
            } else if (currentTitleTopIndex + 1 < this.elements.$targetTitleTop.length) {
                jQuery(this.elements.$targetTitleTop.get(currentTitleTopIndex + 1)).trigger('focus');
            } else {
                this.elements.$targetSkipGrid.trigger('focus');
            }
        } else if ('Tab' === id && evt.shiftKey) {
            let currentElement = lastElement;

            if (jQuery(currentElement).attr('href')) {
                return false;
            } else if (jQuery(currentElement).hasClass('sitemap-title-top')) {
                if (currentTitleTopIndex - 1 >= 0) {
                    evt.preventDefault();
                    currentElement = this.elements.$targetTitleTop.get(currentTitleTopIndex - 1);
                    jQuery(currentElement).trigger('focus');
                }
            }
        } else if ('ArrowDown' === id) {
            let currentElement = lastElement;
            evt.preventDefault();

            if (jQuery(currentElement).hasClass('sitemap-title-top')) {
                currentElement = $currentTitleTopLinks.get(0);
            } else if (jQuery(currentElement).attr('href')) {
                const currentLinkIndex = $currentTitleTopLinks.index(jQuery(currentElement));
                if (currentLinkIndex === ($currentTitleTopLinks.length - 1)) {
                    currentElement = $currentTitleTopLinks.get(0);
                } else if (currentLinkIndex + 1 < $currentTitleTopLinks.length) {
                    currentElement = $currentTitleTopLinks.get(currentLinkIndex + 1);
                } else {
                    currentElement = $currentTitleTopLinks.get(currentLinkIndex);
                }
            }
            jQuery(currentElement).trigger('focus');
        } else if ('ArrowUp' === id) {
            let currentElement = lastElement;
            evt.preventDefault();

            if (jQuery(currentElement).attr('href')) {
                const currentLinkIndex = $currentTitleTopLinks.index(jQuery(currentElement));
                if (currentLinkIndex - 1 > 0) {
                    currentElement = $currentTitleTopLinks.get(currentLinkIndex - 1);
                } else {
                    currentElement = $currentTitleTopLinks.get(0);
                }
            }
            jQuery(currentElement).trigger('focus');
        } else if ('Escape' === id) {
            let currentElement = lastElement;

            if (jQuery(currentElement).hasClass('sitemap-title-top')) {
                currentElement = this.elements.$targetSkipGrid.get(0);
            } else if (jQuery(currentElement).attr('href')) {
                currentElement = jQuery(currentElement).parents('div.sitemap-posts-list').prev('.sitemap-title-top').get(0);
            }
            jQuery(currentElement).trigger('focus');
        } else if ('Home' === id) {
            const currentIndex = this.elements.$targetTitleTop.index(0);
            evt.preventDefault();
            jQuery(this.elements.$targetTitleTop.get(currentIndex)).trigger('focus');
        } else if ('End' === id) {
            const currentIndex = this.elements.$targetTitleTop.index(this.elements.$targetTitleTop.length - 1);
            evt.preventDefault();
            jQuery(this.elements.$targetTitleTop.get(currentIndex)).trigger('focus');
        }
    }
}

/**
 * Description: La class est créer lorsque le composant 'eac-addon-html-sitemap' est chargé dans la page
 *
 * @param elements (Ex: $scope)
 * @since 2.1.3
 */
jQuery(window).on('elementor/frontend/init', () => {
    elementorFrontend.elementsHandler.attachHandler('eac-addon-html-sitemap', widgetHtmlSitemap);
});
