/**
 * Description: Cette méthode est déclenchée lorsque le composant 'eac-addon-mega-menu' est chargé dans la page
 * Ref: * Elementor 3.1.0 https://developers.elementor.com/a-new-method-for-attaching-a-js-handler-to-an-element/
 * 
 * @param $element. Le contenu du widget
 * @since 1.0.0
 */

import EacSwiper from '../modules/eac-swiper.js';

class widgetImageGalerie extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetInstance: '.eac-image-galerie',
                target: '.image-galerie',
                targetSkipGrid: '.eac-skip-grid',
                imagesInstance: '.image-galerie__image-instance',
                itemsInstance: '.image-galerie__item',
                targetSizer: '.image-galerie__item-sizer',
                targetJustify: '.fj-gallery',
                filterWrapperLink: '.ig-filters__wrapper a',
                filterWrapperSelect: '.ig-filters__select',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        let components = {
            $targetInstance: this.$element.find(selectors.targetInstance),
            $target: this.$element.find(selectors.target),
            $targetSkipGrid: this.$element.find(selectors.targetSkipGrid),
            $imagesInstance: this.$element.find(selectors.imagesInstance),
            $itemsInstance: this.$element.find(selectors.itemsInstance),
            $targetSizer: this.$element.find(selectors.targetSizer),
            $targetJustify: this.$element.find(selectors.targetJustify),
            $filterWrapperLink: this.$element.find(selectors.filterWrapperLink),
            $filterWrapperSelect: this.$element.find(selectors.filterWrapperSelect),
            settings: this.$element.find(selectors.target).data('settings'),
            $targetId: null,
            isotopeOptions: {
                itemSelector: '.image-galerie__item',
                percentPosition: true,
                masonry: {
                    columnWidth: '.image-galerie__item-sizer',
                },
                fitRows: {
                    equalheight: false,
                },
                layoutMode: 'fitRows',
                sortBy: 'original-order',
                visibleStyle: { transform: 'scale(1)', opacity: 1 }, // Transition
            },
            justifyOptions: {
                itemSelector: '.fj-gallery-item',
                rowHeight: 250,
                rowHeightTolerance: 0,
                edgeCaseMinRowHeight: 1,
                gutter: 10,
                calculateItemsHeight: true
            },
            has_swiper: false,
            activeBreakpoints: elementorFrontend.config.responsive.activeBreakpoints,
            windowWidthMob: 0,
            windowWidthMobExtra: 0,
            windowWidthTab: 0,
            windowWidthTabExtra: 0,
            windowWidthLaptop: 0,
            windowWidthWidescreen: 0,
        };
        components.$targetId = jQuery('#' + components.settings.data_id);
        components.isotopeOptions.layoutMode = components.settings.data_layout;
        components.isotopeOptions.fitRows.equalheight = components.settings.data_equalheight;
        components.has_swiper = components.settings.data_sw_swiper || false;
        components.justifyOptions.gutter = components.settings.data_gutter;
        components.justifyOptions.rowHeight = components.settings.data_rowheight;

        return components;
    }

    onInit() {
        super.onInit();

        if (this.elements.has_swiper) {
            this.setSwiperGallery();
        } else {
            if ('justify' === this.elements.settings.data_layout) {
                window.setTimeout(this.setLayoutModeJustify.bind(this), 100);
            } else {
                window.setTimeout(this.setLayoutModeGrid.bind(this), 100);
            }
        }
    }

    bindEvents() {
        if (this.elements.$itemsInstance.length > 0) {
            this.elements.$targetInstance.on('keydown', this.setKeyboardEvents.bind(this));
        }

        if (!this.elements.has_swiper && this.elements.settings.data_filtre) {
            this.elements.$filterWrapperLink.on('click', this.onFilterGridClick.bind(this));
            this.elements.$filterWrapperSelect.on('change', this.onFilterGridChange.bind(this));
        }
    }

    onFilterGridClick(evt) {
        const $this = jQuery(evt.currentTarget);
        const $optionSet = $this.parents('.ig-filters__wrapper');

        // L'item du filtre est déjà sélectionné
        if ($this.parents('.ig-filters__item').hasClass('ig-active')) {
            return false;
        }

        $optionSet.find('.ig-active').removeClass('ig-active');
        $this.parents('.ig-filters__item').addClass('ig-active');
        this.elements.$targetId.isotope({ filter: $this.attr('data-filter') });
        return false;
    }

    onFilterGridChange(evt) {
        // Applique le filtre
        this.elements.$targetId.isotope({ filter: evt.currentTarget.value });
        return false;
    }

    onElementChange(propertyName) {
        if ('ig_image_height' === propertyName && 'justify' === this.elements.settings.data_layout) {
            let heightRow = this.elements.justifyOptions.rowHeight;

            if ('ag_image_height' === propertyName) {
                heightRow = this.getElementSettings('ag_image_height')['size'];
            } else if ('ag_image_height_tablet_extra' === propertyName) {
                heightRow = this.getElementSettings('ag_image_height_tablet_extra')['size'];
            } else if ('ag_image_height_tablet' === propertyName) {
                heightRow = this.getElementSettings('ag_image_height_tablet')['size'];
            } else if ('ag_image_height_mobile_extra' === propertyName) {
                heightRow = this.getElementSettings('ag_image_height_mobile_extra')['size'];
            } else if ('ag_image_height_mobile' === propertyName) {
                heightRow = this.getElementSettings('ag_image_height_mobile')['size'];
            } else if ('ag_image_height_laptop' === propertyName) {
                heightRow = this.getElementSettings('ag_image_height_laptop')['size'];
            }
            this.elements.justifyOptions.rowHeight = heightRow;
            this.setLayoutModeJustify();
        }
    }

    setLayoutModeGrid() {
        /** Applique le mode Metro à la première image */
        if (this.elements.settings.data_metro) {
            this.elements.$itemsInstance.eq(0).addClass('layout-type-metro');
        }

        this.elements.$targetId.isotope(this.elements.isotopeOptions);

        /** Charge les images et initialise Isotope chaque fois */
        this.elements.$targetId.imagesLoaded().progress(() => {
            this.elements.$targetId.isotope(this.elements.isotopeOptions);
        });
    }

    setLayoutModeJustify() {
         // Supprime la div sizer utilisée uniquement pour les modes Grid/Masonry
         this.elements.$targetSizer.remove();

         this.elements.$targetId.imagesLoaded().done(() => {
             this.elements.$targetJustify.fjGallery(this.elements.justifyOptions);
         });
    }

    setSwiperGallery() {
        new EacSwiper(this.elements.settings, this.elements.$targetInstance);
    }

    setKeyboardEvents(evt) {
        const selecteur = 'button, a, [tabindex]:not([tabindex="-1"])';
        const id = evt.code || evt.key || 0;
        let $targetArticleFirst = null;
        let $targetArticleLast = null;
        let $targetArticleContentFirst = null;
        let $targetArticleContentLast = null;

        if (this.elements.settings.data_filtre) {
            const $elementsFiltered = this.elements.$targetId.isotope('getFilteredItemElements');
            $targetArticleFirst = jQuery($elementsFiltered[0]).hasClass('image-galerie__item-sizer') ? jQuery($elementsFiltered[1]) : jQuery($elementsFiltered[0]);
            $targetArticleLast = jQuery($elementsFiltered[$elementsFiltered.length - 1]);
        } else {
            $targetArticleFirst = this.elements.$itemsInstance.first();
            $targetArticleLast = this.elements.$itemsInstance.last();
        }
        $targetArticleContentFirst = $targetArticleFirst.find('.image-galerie__content');
        $targetArticleContentLast = $targetArticleLast.find('.image-galerie__content');

        if ('Home' === id) {
            evt.preventDefault();
            if (this.elements.settings.data_overlay === 'overlay-out') {
                $targetArticleFirst.find(selecteur).first().trigger('focus');
            } else {
                $targetArticleContentFirst.trigger('focus');
                window.setTimeout(() => { $targetArticleContentFirst.find(selecteur).first().focus(); }, 500);
            }
        } else if ('End' === id) {
            evt.preventDefault();
            if (this.elements.settings.data_overlay === 'overlay-out') {
                $targetArticleLast.find(selecteur).last().trigger('focus');
            } else {
                $targetArticleContentLast.trigger('focus');
                window.setTimeout(() => { $targetArticleContentLast.find(selecteur).last().focus(); }, 500);
            }
        } else if ('Escape' === id) {
            this.elements.$targetSkipGrid.trigger('focus');
        }
    }
}

jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsImageGallery = ($element) => {
        elementorFrontend.elementsHandler.addHandler(widgetImageGalerie, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/eac-addon-image-galerie.default', EacAddonsImageGallery);
});
