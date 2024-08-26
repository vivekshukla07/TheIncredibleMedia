
/**
 * Description: Cette méthode est déclenchée lorsque la section/colonne/widget/container
 *
 * @param {selector} $element. Le contenu de la section/colonne/widget/container
 * 
 * @since 1.8.1
 */

class widgetClassElementSticky extends elementorModules.frontend.handlers.Base {

    getDefaultElements() {
        return {
            $target: this.$element,
            elType: this.$element.data('element_type'),
            elId: this.$element.data('id'),
            settings: this.$element.data('eac_settings-sticky') || {},
            stickySettings: 'data-eac_settings-sticky',
            $observerTarget: null,
            optionsObserve: {
                root: null,
                rootMargin: '0px 0px 1000px 0px',
                threshold: 1, // Seuil de visibilité de l'élément
            },
            intersectObserver: null,
            resizeObserver: null,
            isSticky: false,
            isEditMode: Boolean(elementorFrontend.isEditMode()),
            positionClass: '',
            adminBar: document.getElementById('wpadminbar'),
            sticky_top: null,
        };
    }

    onInit() {
        super.onInit();

        // Pas dans l'éditeur
        if (this.elements.isEditMode) {
            return;
        }

        if (Object.keys(this.elements.settings).length > 0 && (typeof this.elements.settings.sticky !== 'undefined' && this.elements.settings.sticky === 'yes') && this.isDeviceSelected()) {
            this.initStickyElement();
        }

        if (!this.elements.isSticky) {
            this.cleanTarget();
            return;
        }

        if (window.IntersectionObserver) {
            this.elements.intersectObserver = new IntersectionObserver(this.observeElementInViewport.bind(this), this.elements.optionsObserve);
            this.elements.intersectObserver.observe(this.elements.$observerTarget[0]);

            // Gestion des événements 'resize' et 'orientationchange'
            this.elements.resizeObserver = new ResizeObserver(this.observeResizeViewport.bind(this));
            this.elements.resizeObserver.observe(this.elements.$observerTarget[0]);
        }
    }

    initStickyElement() {
        if (this.elements.adminBar) {
            this.elements.sticky_top = (this.elements.settings.up + this.elements.adminBar.clientHeight) + 'px';
        } else {
            this.elements.sticky_top = this.elements.settings.up + 'px';
        }

        this.elements.isSticky = true;

        // La class sticky ou fixed
        this.elements.positionClass = this.elements.settings.class;

        // Élément global et non basé sur son parent
        if (this.elements.settings.fixed) {
            this.elements.optionsObserve.threshold = 0;
            // L'élément témoin
            this.elements.$observerTarget = jQuery('<div id="eac-element_sticky-observer-' + this.elements.elId + '" style="position:relative;"></div>').insertAfter(this.elements.$target);
        } else {
            this.elements.$observerTarget = this.elements.$target;
            // Marge supérieur/inférieur de déclenchement
            this.elements.optionsObserve.rootMargin = "-" + this.elements.settings.up + "px 0px " + "-" + this.elements.settings.down + "px 0px";
        }
    }

    isDeviceSelected() {
        const currentDevice = elementorFrontend.getCurrentDeviceMode();

        if (jQuery.inArray(currentDevice, this.elements.settings.devices) !== -1) {
            return true;
        }
        return false;
    }

    cleanTarget() {
        this.elements.$target.removeClass(this.elements.positionClass);
        this.elements.$target.removeAttr(this.elements.stickySettings);
        this.elements.$target.css('top', '');
        this.elements.$target.css('bottom', '');
        this.elements.$target.css('z-index', 'auto');
        jQuery("eac-element_sticky-observer-" + this.elements.elId).remove();
    }

    observeElementInViewport(entries, observer) {
        const settings = this.elements.settings;
        //console.log(target.parentElement.className+"::"+target.parentElement.nodeName);

        if (settings.fixed && entries[0].intersectionRatio === 0) {
            this.elements.$target[0].classList.add(this.elements.positionClass);
            this.elements.$target[0].style.top = this.elements.sticky_top;
            this.elements.$target[0].style.zIndex = settings.zindex;
        } else if (!settings.fixed && entries[0].isIntersecting) {
            this.elements.$target[0].classList.add(this.elements.positionClass);
            this.elements.$target[0].style.top = this.elements.sticky_top;
            this.elements.$target[0].style.bottom = settings.down + 'px';
            this.elements.$target[0].style.zIndex = settings.zindex;
            observer.disconnect();
        } else {
            this.elements.$target[0].classList.remove(this.elements.positionClass);
            this.elements.$target[0].style.top = '';
            this.elements.$target[0].style.bottom = '';
            this.elements.$target[0].style.zIndex = 'auto';
        }
    }

    /** callBack de ResizeObserver déclenché par les événements 'resize' et 'orientationchange' */
    observeResizeViewport(entries) {
        if (!this.isDeviceSelected()) {
            this.elements.intersectObserver.disconnect();
        } else {
            if (this.elements.intersectObserver instanceof IntersectionObserver) {
                this.elements.intersectObserver.observe(this.elements.$observerTarget[0]);
            }
        }
    }
}

jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsElementSticky = ($element) => {
        elementorFrontend.elementsHandler.addHandler(widgetClassElementSticky, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/section', EacAddonsElementSticky);
    elementorFrontend.hooks.addAction('frontend/element_ready/column', EacAddonsElementSticky);
    elementorFrontend.hooks.addAction('frontend/element_ready/widget', EacAddonsElementSticky);
    elementorFrontend.hooks.addAction('frontend/element_ready/container', EacAddonsElementSticky);
});
