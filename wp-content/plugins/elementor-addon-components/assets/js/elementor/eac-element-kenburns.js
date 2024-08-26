
/**
 * Description: Cette méthode est déclenchée lorsque la fonctionnalité 'Background Ken Burns slideshow' est chargé dans la page
 *
 * @param {selector} $element. Le contenu de la section/column/container
 * @since 2.0.2
 */

class widgetKenburnsSlideshow extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetInstance: '.eac-kenburns__images-wrapper',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        const components = {
            $targetInstance: this.$element.find(selectors.targetInstance),
            targetID: this.$element.find(selectors.targetInstance).data('elem-id'),
            parentID: this.$element.data('id'),
            settings: {
                randomize: false,
                slideDuration: this.$element.find(selectors.targetInstance).data('duration'),
                fadeDuration: 1000,
                pauseOnTabBlur: elementorFrontend.isEditMode() ? false : true,
                slideElementClass: 'slide',     // Défini dans le CSS
                slideShowWrapperId: 'slideshow' // Défini dans le CSS;
            },
            isEditMode: elementorFrontend.isEditMode() ? true : false,
            currentSlideStartTime: Date.now(),
            paused: false,
            slideTimeDelta: 0,
            resumeStartTime: 0,
            resumeTimer: null,
            cssAnimationDuration: 0,
            $slidesShowWrapper: null,
            parentIDedit: null,
            $parentWrapper: null,
            $slidesWrapper: null,
        };
        components.parentIDedit = components.isEditMode ? 'edit_' + components.parentID : components.parentID;
        components.$parentWrapper = jQuery('.elementor-element-' + components.parentID);
        components.$slidesWrapper = this.$element.find('.eac-kenburns__images-wrapper.' + components.targetID) || {};

        return components;
    }

    onInit() {
        super.onInit();

        /** Pas de slides à animer. les événements globaux sont supprimés */
        if (this.elements.$slidesWrapper.length === 0 && window[this.elements.parentIDedit]) {
            window.clearInterval(window[this.elements.parentIDedit]);
            delete window[this.elements.parentIDedit];
            return false;
        }

        if (this.elements.$slidesWrapper.length > 0 && this.elements.parentID === this.elements.targetID) {
            /** Diapositives aléatoires */
            if (this.elements.settings.randomize === true) {
                const slidesDOM = this.elements.$slidesWrapper[0];
                for (let i = slidesDOM.children.length; i >= 0; i--) {
                    slidesDOM.appendChild(slidesDOM.children[Math.random() * i | 0]);
                }
            }

            const $slideToShowIdBefore = this.elements.$parentWrapper.find('div#' + this.elements.settings.slideShowWrapperId);
            if ($slideToShowIdBefore.length === 0) {
                jQuery('<div id="' + this.elements.settings.slideShowWrapperId + '"></div>').insertBefore(this.elements.$slidesWrapper);
            }

            this.elements.$slidesShowWrapper = this.elements.$parentWrapper.find('#' + this.elements.settings.slideShowWrapperId);
            this.elements.cssAnimationDuration = this.elements.settings.slideDuration + this.elements.settings.fadeDuration;

            /** Ajoute la première diapositive au diaporama */
            this.elements.$slidesWrapper.find('.' + this.elements.settings.slideElementClass + ':first span.animate').addClass('active').css('animation-duration', this.elements.cssAnimationDuration + 'ms')
            this.elements.$slidesWrapper.find('.' + this.elements.settings.slideElementClass + ':first').prependTo(this.elements.$slidesShowWrapper);

            /** Début de la boucle. Commence par supprimer les événements globaux */
            if (window[this.elements.parentIDedit]) {
                window.clearInterval(window[this.elements.parentIDedit]);
                delete window[this.elements.parentIDedit];
            }
            window[this.elements.parentIDedit] = this.elements.parentIDedit;
            window[this.elements.parentIDedit] = window.setInterval(this.slideRefresh.bind(this), this.elements.settings.slideDuration);
        }
    }

    bindEvents() {
        if (this.elements.settings.pauseOnTabBlur === true) {
            jQuery(window).focus(this.setTargetEventFocus.bind(this));
            jQuery(window).blur(this.setTargetEventBlur.bind(this));
        }
    }

    setTargetEventFocus() {
        if (this.elements.$slidesShowWrapper && this.elements.$slidesShowWrapper.length === 1 && this.elements.paused === true) {
            const that = this;
            this.elements.resumeStartTime = Date.now();
            this.elements.paused = false;
            const $slideToShowIdActiveLast = this.elements.$parentWrapper.find('#' + this.elements.settings.slideShowWrapperId + ' span.active:last');
            $slideToShowIdActiveLast.removeClass('paused');

            this.elements.resumeTimer = setTimeout(() => {
                that.elements.slideTimeDelta = 0;
                that.slideRefresh.bind(that);
                window[that.elements.parentIDedit] = that.elements.parentIDedit;
                window[that.elements.parentIDedit] = window.setInterval(that.slideRefresh.bind(that), that.elements.settings.slideDuration);
            }, this.elements.settings.slideDuration - this.elements.slideTimeDelta);
        }
    }

    setTargetEventBlur() {
        this.elements.paused = true;

        if (this.elements.slideTimeDelta !== 0) {
            const timeSinceLastPause = Date.now() - this.elements.resumeStartTime;
            this.elements.slideTimeDelta = this.elements.slideTimeDelta + timeSinceLastPause;
        } else {
            this.elements.slideTimeDelta = Date.now() - this.elements.currentSlideStartTime;
        }

        const $slideToShowIdActiveFirst = this.elements.$parentWrapper.find('#' + this.elements.settings.slideShowWrapperId + ' span.active:first');
        $slideToShowIdActiveFirst.addClass('paused');
        window.clearInterval(window[this.elements.parentIDedit]);
        window.clearTimeout(this.elements.resumeTimer);
    }

    slideRefresh() {
        const that = this;
        const slideshowDOM = this.elements.$slidesShowWrapper[0];
        const $slideElementClassFirst = this.elements.$slidesWrapper.find('.' + this.elements.settings.slideElementClass + ':first');

        /**
         * Si setInterval échoue, le diaporama n'aura parfois aucune diapositive.
         * Cette fonction vérifie et ajoute la diapositive suivante dans le diaporama s'il est vide.
         * Mettre le diaporama en pause évitera les problèmes la plupart du temps,
         * C'est donc une solution de repli en cas d'échec.
         */
        if (slideshowDOM.children.length === 0) {
            if ($slideElementClassFirst.length === 1) {
                $slideElementClassFirst.prependTo(this.elements.$slidesShowWrapper);
            } else {
                window.clearInterval(window[this.elements.parentIDedit]);
                window.clearTimeout(this.elements.resumeTimer);
            }
        } else {
            this.elements.$slidesWrapper.find('.' + this.elements.settings.slideElementClass + ':first').prependTo(this.elements.$slidesShowWrapper);
            const $slideElementFirst = this.elements.$parentWrapper.find('#' + this.elements.settings.slideShowWrapperId + ' .' + this.elements.settings.slideElementClass + ':first span.animate');
            const $slideElementLast = this.elements.$parentWrapper.find('#' + this.elements.settings.slideShowWrapperId + ' .' + this.elements.settings.slideElementClass + ':last');
            const $slideElementLastSpan = this.elements.$parentWrapper.find('#' + this.elements.settings.slideShowWrapperId + ' .' + this.elements.settings.slideElementClass + ':last span.animate');

            $slideElementFirst.addClass('active').css('animation-duration', this.elements.cssAnimationDuration + 'ms');

            $slideElementLast.fadeOut(this.elements.settings.fadeDuration, () => {
                $slideElementLastSpan.removeClass('active').css('animation-duration', '0ms');
                $slideElementLast.appendTo(that.elements.$slidesWrapper);
                that.elements.$slidesWrapper.find('.' + that.elements.settings.slideElementClass).show(0);
            });
        }
    }
}

jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsKenburnsSlideshow = ($element) => {
        elementorFrontend.elementsHandler.addHandler(widgetKenburnsSlideshow, {
            $element
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/section', EacAddonsKenburnsSlideshow);
    elementorFrontend.hooks.addAction('frontend/element_ready/container', EacAddonsKenburnsSlideshow);
    elementorFrontend.hooks.addAction('frontend/element_ready/column', EacAddonsKenburnsSlideshow);
});
