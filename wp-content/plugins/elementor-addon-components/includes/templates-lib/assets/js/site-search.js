/**
 * Description: Cette class est déclenchée lorsque le composant 'eac-addon-site-search' est chargé dans la page
 *
 * https://developers.elementor.com/add-javascript-to-elementor-widgets/
 * 
 * @param $element. Le contenu de la section/container
 * @since 2.1.0
 */

class widgetSiteSearch extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                hiddenButton: '.eac-search_form-wrapper',
                searchIcon: '.search-icon',
                clearIcon: '.clear-icon',
                searchInput: '.eac-search_form-input',
                searchForm: '.eac-search_form',
                searchFormContainer: '.eac-search_form-container',
                searchButton: '.eac-search_button-toggle',
                postTypeSelectWrapper: '.eac-search_select-wrapper',
                postTypeSelect: '.eac-search_select-post-type',
                postType: '.eac-search_form-post-type',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        const components = {
            isHiddenButton: this.$element.find(selectors.hiddenButton).data('hide-button'),
            $searchIcon: this.$element.find(selectors.searchIcon),
            $clearIcon: this.$element.find(selectors.clearIcon),
            $searchInput: this.$element.find(selectors.searchInput),
            $searchForm: this.$element.find(selectors.searchForm),
            $searchFormContainer: this.$element.find(selectors.searchFormContainer),
            $searchButton: this.$element.find(selectors.searchButton),
            $postTypeSelectWrapper: this.$element.find(selectors.postTypeSelectWrapper),
            $postTypeSelect: this.$element.find(selectors.postTypeSelect),
            $postType: this.$element.find(selectors.postType),
        };

        components.$searchIcon.css('display', 'flex');
		components.$clearIcon.css('display', 'none');
		components.$searchInput.val('');

        if (components.isHiddenButton) {
            components.$searchButton.css('display', 'none');
            components.$searchFormContainer.css('display', 'flex');
            components.$postTypeSelectWrapper.css('display', 'inline-block');
        } else {
            components.$searchIcon.css('display', 'none');
        }

        return components;
    }

    bindEvents() {
        this.elements.$searchButton.on('click', this.searchButton.bind(this));
        this.elements.$searchInput.on('keyup', this.searchInput.bind(this));
        this.elements.$clearIcon.on('keydown click', this.clearIcon.bind(this));
        this.elements.$postTypeSelect.on('change', this.postTypeSelect.bind(this));
    }

    searchButton(evt) {
        evt.preventDefault();

        if (this.elements.$searchFormContainer.css('display') === 'none') {
            this.elements.$clearIcon.css('display', 'none');
            this.elements.$searchButton.attr('aria-expanded', 'true');
            this.elements.$searchFormContainer.css('display', 'flex');
            this.elements.$postTypeSelectWrapper.css('display', 'inline-block');
        } else {
            this.elements.$searchInput.val('');
            this.elements.$clearIcon.css('display', 'none');
            this.elements.$searchButton.attr('aria-expanded', 'false');
            this.elements.$searchFormContainer.css('display', 'none');
            this.elements.$postTypeSelectWrapper.css('display', 'none');
        }
        
        //this.elements.$searchFormContainer.stop().toggle({ direction: "right" }, 300);
    }

    searchInput() {
        if (this.elements.$searchInput.val() && this.elements.$clearIcon.css('display') === 'none') {
            this.elements.$searchIcon.css('display', 'none');
            this.elements.$clearIcon.css('display', 'flex');
        } else if (!this.elements.$searchInput.val()) {
            this.elements.isHiddenButton ? this.elements.$searchIcon.css('display', 'flex') : this.elements.$searchIcon.css('display', 'none');
            this.elements.$clearIcon.css('display', 'none');
        }
    }

    clearIcon(evt) {
        const id = evt.code || evt.key || 0;
		if ('keydown' === evt.type && ('Enter' !== id && 'Space' !== id)) {
			return;
		}
        evt.preventDefault();

        this.elements.$searchInput.val('');
		this.elements.isHiddenButton ? this.elements.$searchIcon.css('display', 'flex') : this.elements.$searchIcon.css('display', 'none');
		this.elements.$clearIcon.css('display', 'none');
        this.elements.$searchInput.focus();
    }

    postTypeSelect() {
        this.elements.$postType.val(this.elements.$postTypeSelect.val());
    }
}

/**
 * Description: La class est créer lorsque le composant 'eac-addon-mega-menu' est chargé dans la page
 *
 * @param $element (ex: $scope)
 * @since 2.1.0
 */
jQuery(window).on('elementor/frontend/init', () => {
	elementorFrontend.elementsHandler.attachHandler('eac-addon-site-search', widgetSiteSearch);
});

/**
 * Description: La class est créer lorsque le composant 'eac-addon-site-search' est chargé dans la page
 *
 * @param $element (ex: $scope)
 * @since 2.1.0
 */
/*jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsSiteSearch = ($element) => {
        elementorFrontend.elementsHandler.addHandler(widgetSiteSearch, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/eac-addon-site-search.default', EacAddonsSiteSearch);
});*/
