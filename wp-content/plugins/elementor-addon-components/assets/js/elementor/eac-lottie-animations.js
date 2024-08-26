
/**
 * Description: Cette méthode est déclenchée lorsque les colonnes et le widget 'eac-addon-lottie-animations' sont chargées dans la page
 *
 * @param {selector} $element. Le contenu de la section/colonne
 * 
 * @since 1.9.3
 */
class widgetLottieAnimations extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetId: '.lottie-anim_wrapper',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        let components = {
            $target: this.$element,
            targetDataId: this.$element.data('id'),
            $targetId: this.$element.find(selectors.targetId),
            targetIdDataId: this.$element.find(selectors.targetId).data('elem-id'),
            elType: this.$element.data('element_type'),
            defaultSettings: {
                container: this.$element.find(selectors.targetId)[0],
                renderer: this.$element.find(selectors.targetId).data('renderer') || 'svg',
                loop: this.$element.find(selectors.targetId).data('loop'),
                autoplay: this.$element.find(selectors.targetId).data('autoplay'),
                path: this.$element.find(selectors.targetId).data('src'),
                name: this.$element.find(selectors.targetId).data('name'),
                rendererSettings: {
                    progressiveLoad: '',
                    preserveAspectRatio: '',
                    imagePreserveAspectRatio: '',
                },
            },
            optionsObserve: {
                root: null,
                rootMargin: "-30px 0px -30px 0px",	// 30px en haut et en bas par défaut
                threshold: 1,						// Ratio de visibilité de la cible
            },
            source: this.$element.find(selectors.targetId).data('src'),
            direction: this.$element.find(selectors.targetId).data('reverse'),
            speed: this.$element.find(selectors.targetId).data('speed'),
            trigger: this.$element.find(selectors.targetId).data('trigger'),
            elTypeList: ['section', 'column', 'container'],
            player: null,
        };
        components.defaultSettings.rendererSettings.progressiveLoad = components.defaultSettings.renderer === 'svg' ? false : true;
        components.defaultSettings.rendererSettings.preserveAspectRatio = components.defaultSettings.renderer === 'svg' ? "xMidYMid meet" : '';
        components.defaultSettings.rendererSettings.imagePreserveAspectRatio = components.defaultSettings.renderer === 'svg' ? "xMidYMid meet" : '';

        return components;
    }

    onInit() {
        super.onInit();
        const that = this;

        /**
         * Ce n'est pas un composant Lottie
         * Check 'this.elements' SI la méthode 'isActive' est ajoutée et qu'elle retourne 'false'
         */
        if (!this.elements || this.elements.$targetId.length === 0) {
            return false;
        }

        /**
         * Lottie background
         * C'est peut être une colonne/container mère
         * On compare l'ID de la colonne et l'ID de l'élément
         */
        if (jQuery.inArray(this.elements.elType, this.elements.elTypeList) !== -1 && this.elements.targetDataId !== this.elements.targetIdDataId) {
            return false;
        }

        //console.log('Feature::' + elementorFrontend.config.experimentalFeatures.e_optimized_assets_loading )
        //console.log('onInit::' + JSON.stringify(this.getSettings()) + "::" + this.elements.targetDataId);

        // Controle de l'URL pour le Lottie background
        if (this.elements.source === '') {
            return false;
        }

        // Charge l'animation
        this.elements.player = bodymovin.loadAnimation(this.elements.defaultSettings);

        // Direction
        this.elements.player.setDirection(this.elements.direction);

        // Vitesse
        this.elements.player.setSpeed(this.elements.speed);

        /**
         * Pas dans le 'bindEvents' le player n'est pas encode initialisé
         */
        if (this.elements.trigger === 'hover') {
            this.elements.$targetId.on('mouseenter', () => { that.elements.player.play(); });
            this.elements.$targetId.on('mouseleave', () => { that.elements.player.pause(); });
        } else if (this.elements.trigger === 'viewport' && window.IntersectionObserver) {
            const intersectObserver = new IntersectionObserver(this.observeElementInViewport.bind(this), this.elements.optionsObserve);
            intersectObserver.observe(this.elements.defaultSettings.container);
        }
    }

    // Il n'y a que les éléments définis dans 'getDefaultElements' qui peuvent être binder
    /**bindEvents() {
        if (this.elements.$targetId.length === 1 && this.elements.player) {
            console.log('bindEvents');
        }
    }*/

    observeElementInViewport(entries, observer) {
        if (entries[0].isIntersecting) {
            this.elements.player.play();
        } else {
            this.elements.player.pause();
        }
    }

    /**isActive(settings) {
        const lesbools = settings.$element.find('.lottie-anim_wrapper').length === 1 ? true : false;
        console.log('isActive::' + lesbools);
        return lesbools;
    }*/
}

jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsLottieAnimations = ($element) => {
        elementorFrontend.elementsHandler.addHandler(widgetLottieAnimations, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/eac-addon-lottie-animations.default', EacAddonsLottieAnimations);
    //elementorFrontend.hooks.addAction('frontend/element_ready/section', EacAddonsLottieAnimations);
    elementorFrontend.hooks.addAction('frontend/element_ready/column', EacAddonsLottieAnimations);
    elementorFrontend.hooks.addAction('frontend/element_ready/container', EacAddonsLottieAnimations);
});
