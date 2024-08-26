/**
 * Description: Cette méthode est déclenchée lorsque les sections 'eac-addon-articles-liste' ou 'eac-addon-product-grid' sont chargées dans la page
 *
 * @param {selector} $scope. Le contenu de la section
 * @since 1.0.0
 */
import EacSwiper from '../modules/eac-swiper.js';

class widgetArticlesListe extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetInstance: '.eac-articles-liste',
                targetWrapper: '.al-posts__wrapper',
                targetSkipGrid: '.eac-skip-grid',
                filterWrapper: '.al-filters__wrapper a',
                filterWrapperSelect: '.al-filters__select',
                buttonPaged: '.al-more-button-paged',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        let components = {
            $targetInstance: this.$element.find(selectors.targetInstance),
            $targetWrapper: this.$element.find(selectors.targetWrapper),
            $targetSkipGrid: this.$element.find(selectors.targetSkipGrid),
            $filterWrapper: this.$element.find(selectors.filterWrapper),
            $filterWrapperSelect: this.$element.find(selectors.filterWrapperSelect),
            $buttonPaged: this.$element.find(selectors.buttonPaged),
            settings: this.$element.find(selectors.targetWrapper).data('settings') || {},
            $targetId: null,
            $paginationId: null,
            isotopeOptions: {
                itemSelector: '.al-post__wrapper',
                percentPosition: true,
                masonry: {
                    columnWidth: '.al-posts__wrapper-sizer',
                },
                fitRows: {
                    equalheight: false,
                },
                layoutMode: 'fitRows',
                sortBy: 'original-order',
                visibleStyle: { transform: 'scale(1)', opacity: 1 },
            },
            infiniteScrollOptions: {
                path: function () { return location.pathname.replace(/\/?$/, '/') + "page/" + parseInt(this.pageIndex + 1); },
                debug: false,
                button: '',
                status: '',
                scrollThreshold: false,
                history: false,
                horizontalOrder: false,
            },
            has_swiper: false,
        };
        components.$targetId = jQuery('#' + components.settings.data_id);
        components.isotopeOptions.layoutMode = components.settings.data_layout;
        components.$paginationId = jQuery('#' + components.settings.data_pagination_id);
        components.infiniteScrollOptions.button = '#' + components.settings.data_pagination_id + ' button';
        components.infiniteScrollOptions.status = '#' + components.settings.data_pagination_id + ' .al-page-load-status';
        components.isotopeOptions.fitRows.equalheight = components.settings.data_equalheight;
        components.has_swiper = components.settings.data_sw_swiper || false;

        return components;
    }

    onInit() {
        super.onInit();

        if (this.elements.has_swiper) {
            new EacSwiper(this.elements.settings, this.elements.$targetInstance);
        } else {
            window.setTimeout(this.setLayoutModeGrid.bind(this), 100);

            if (this.elements.settings.data_filtre) {
                window.setTimeout(this.setFilterWithWindowLocation.bind(this), 500);
            }

            if (this.elements.settings.data_pagination) {
                this.elements.$targetId.infiniteScroll(this.elements.infiniteScrollOptions);
            }
        }
    }

    bindEvents() {
        if (this.elements.settings.data_filtre) {
            this.elements.$filterWrapper.on('click', this.onFilterGridClick.bind(this));
            this.elements.$filterWrapperSelect.on('change', this.onFilterGridChange.bind(this));
        }

        if (this.elements.settings.data_pagination) {
            this.elements.$targetId.on('load.infiniteScroll', this.onLoadInfiniteScroll.bind(this));
        }

        if (this.elements.$targetInstance.length > 0) {
            this.elements.$targetInstance.on('keydown', this.setKeyboardEvents.bind(this));
        }
    }

    /** Init Isotope, charge les images et redessine le layout */
    setLayoutModeGrid() {
        this.elements.$targetId.isotope(this.elements.isotopeOptions);

        /** Charge les images et initialise Isotope chaque fois */
        this.elements.$targetId.imagesLoaded().progress(() => {
            this.elements.$targetId.isotope(this.elements.isotopeOptions);
        });
    }

    setFilterWithWindowLocation() {
        const that = this;
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const filter = urlParams.has('filter') ? decodeURIComponent(urlParams.get('filter')) : false;
        let domInterval = 0;
        if (filter) {
            let domProgress = window.setInterval(function () {
                if (domInterval < 5) {
                    var $data_filter = jQuery(".al-filters__wrapper a[data-filter='." + filter + "']", that.elements.$targetInstance);
                    var $data_select = jQuery('.al-filters__wrapper-select .al-filters__select', that.elements.$targetInstance);
                    if ($data_filter.length === 1 && $data_select.length === 1) {
                        window.clearInterval(domProgress);
                        domProgress = null;
                        $data_filter.trigger('click');
                        $data_filter.trigger('focus');
                        $data_select.val('.' + filter);
                        $data_select.trigger('change');
                    } else {
                        domInterval++;
                    }
                } else {
                    window.clearInterval(domProgress);
                    domProgress = null;
                }
            }, 100);
        }
    }

    onFilterGridClick(evt) {
        const $this = jQuery(evt.currentTarget);
        // L'item du filtre est déjà sélectionné
        if ($this.parents('.al-filters__item').hasClass('al-active')) {
            return false;
        }

        const $optionSet = $this.parents('.al-filters__wrapper');
        $optionSet.find('.al-active').removeClass('al-active');
        $this.parents('.al-filters__item').addClass('al-active');

        // Applique le filtre
        const selector = $this.attr('data-filter');
        this.elements.$targetId.isotope({ filter: selector }); // Applique le filtre
        return false;
    }

    onFilterGridChange(evt) {
        // Récupère la valeur du filtre avec l'option sélectionnée
        const filterValue = evt.currentTarget.value;
        // Applique le filtre
        this.elements.$targetId.isotope({ filter: filterValue });
        return false;
    }

    onLoadInfiniteScroll(event, response, path) {
        // get infiniteScroll instance
        const infScroll = this.elements.$targetId.data('infiniteScroll');
        const selecteur = 'button, a, [tabindex]:not([tabindex="-1"])';
        const selectedItems = '.' + this.elements.settings.data_article + '.al-post__wrapper';
        const $items = jQuery(response).find(selectedItems);     // Recherche les nouveaux items
        const $firstLoadedItem = $items.find(selecteur).first(); // Recherche du premier élément focusable dans les nouveaux items
        //console.log("load.infiniteScroll: " + path + "::Class: " + selectedItems + "::height: " + window.innerHeight / 2);

        this.elements.$targetId.append($items).isotope('appended', $items);
        this.elements.$targetId.imagesLoaded().done(() => {
            this.elements.$targetId.isotope('layout');
            window.setTimeout(() => { $firstLoadedItem.trigger('focus'); }, 200);
        });

        // On teste l'égalité entre le nombre de page totale et celles chargées dans infiniteScroll
        // lorsque le pagging s'applique sur une 'static page' ou 'front page'
        if (parseInt(infScroll.pageIndex) >= parseInt(this.elements.settings.data_max_pages)) {
            this.elements.$targetId.infiniteScroll('destroy'); // Destroy de l'instance
            this.elements.$paginationId.remove(); // Supprime la div status
        } else {
            const nbPosts = parseInt(this.elements.$buttonPaged.text().split('/')[0]) + $items.length;
            const totalPosts = nbPosts + '/' + this.elements.settings.data_found_posts;
            this.elements.$buttonPaged.text(totalPosts); // modifie le nombre d'éléments chargés du bouton 'MORE'
        }
    }

    setKeyboardEvents(evt) {
        const selecteur = 'button, a, [tabindex]:not([tabindex="-1"])';
        const id = evt.code || evt.key || 0;
        let $targetArticleFirst = null;
        let $targetArticleLast = null;

        if (this.elements.settings.data_filtre) {
            const $elementsFiltered = this.elements.$targetId.isotope('getFilteredItemElements');
            $targetArticleFirst = jQuery($elementsFiltered[0]).hasClass('al-posts__wrapper-sizer') ? jQuery($elementsFiltered[1]) : jQuery($elementsFiltered[0]);
            $targetArticleLast = jQuery($elementsFiltered[$elementsFiltered.length - 1]);
        } else {
            $targetArticleFirst = this.elements.$targetInstance.find('article').first();
            $targetArticleLast = this.elements.$targetInstance.find('article').last();
        }

        if ('Home' === id) {
            evt.preventDefault();
            $targetArticleFirst.find(selecteur).first().trigger('focus');
        } else if ('End' === id) {
            evt.preventDefault();
            $targetArticleLast.find(selecteur).last().trigger('focus');
        } else if ('Escape' === id) {
            this.elements.$targetSkipGrid.trigger('focus');
        }
    }
}

/*jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsArticlesListe = ($element) => {
        elementorFrontend.elementsHandler.addHandler(widgetArticlesListe, { $element });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/eac-addon-articles-liste.default', EacAddonsArticlesListe);
    elementorFrontend.hooks.addAction('frontend/element_ready/eac-addon-product-grid.default', EacAddonsArticlesListe);
});*/

/**
 * Description: La class est créer lorsque le composant 'xxxxxxxxx' est chargé dans la page
 *
 * @param elements (Ex: $scope)
 */
jQuery(window).on('elementor/frontend/init', () => {
    elementorFrontend.elementsHandler.attachHandler('eac-addon-articles-liste', widgetArticlesListe);
    elementorFrontend.elementsHandler.attachHandler('eac-addon-product-grid', widgetArticlesListe);
});
