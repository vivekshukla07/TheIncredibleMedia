/**
 * Description: Cette méthode est déclenchée lorsque qu'un élément container/section/column est chargé dans la page
 *
 * @since 2.0.0
 */

class widgetBackgroundImages extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetInstance: '.eac-background__images-wrapper',
				targetImages: '.background-images__wrapper-item',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        return {
            $targetInstance: this.$element.find(selectors.targetInstance),
            $targetImages: this.$element.find(selectors.targetImages),
		    imagesArray: [],
			sizeImagesArray: [],
        };
    }

    onInit() {
        super.onInit();

        jQuery.each(this.elements.$targetImages, (index, img) => {
            const url  = 'url(' + jQuery(img).data('url') + ') ' + jQuery(img).data('position') + ' ' + jQuery(img).data('repeat') + ' ' + jQuery(img).data('attachment');
            const size = jQuery(img).data('size');
            this.elements.imagesArray.push(url);
            this.elements.sizeImagesArray.push(size);
        });

        this.elements.$targetInstance.css({ 'background': this.elements.imagesArray.join(','), 'background-size': this.elements.sizeImagesArray.join(',') });
    }
}

jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsBackgroundImages = ($element) => {
        elementorFrontend.elementsHandler.addHandler(widgetBackgroundImages, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/section', EacAddonsBackgroundImages);
    elementorFrontend.hooks.addAction('frontend/element_ready/container', EacAddonsBackgroundImages);
    elementorFrontend.hooks.addAction('frontend/element_ready/column', EacAddonsBackgroundImages);
});
