/**
 * Description: Cette méthode est déclenchée lorsque la section 'eac-addon-toc' est chargée dans la page
 *
 * @param {selector} $element. Le contenu de la section
 * @since 1.8.0
 */

class widgetTableOfContent extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetInstance: '.eac-table-of-content',
                toc: '#toctoc',
                tocHead: '#toctoc-head',
                tocBody: '#toctoc-body',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        return {
            $targetInstance: this.$element.find(selectors.targetInstance),
            $toc: this.$element.find(selectors.toc),
            $tocHead: this.$element.find(selectors.tocHead),
            $tocBody: this.$element.find(selectors.tocBody),
            $tocHeadToggler: jQuery("<span></span>").attr('role', 'presentation'),
            settings: {
                fontawesome: this.$element.find(selectors.targetInstance).data('settings').data_fontawesome,
                target: this.$element.find(selectors.targetInstance).data('settings').data_target,
                opened: this.$element.find(selectors.targetInstance).data('settings').data_opened,
                headPicto: ['▼', '▲'],
                titles: this.$element.find(selectors.targetInstance).data('settings').data_title,
                trailer: this.$element.find(selectors.targetInstance).data('settings').data_trailer,
                ancreAuto: this.$element.find(selectors.targetInstance).data('settings').data_anchor,
                topMargin: this.$element.find(selectors.targetInstance).data('settings').data_topmargin,
                ariaLabel: this.$element.find(selectors.targetInstance).data('settings').data_label,
                ancreDefault: 'toc-heading-anchor',
                windowWith: window.innerWidth,
            },
            $linksList: [],
        };
    }

    onInit() {
        super.onInit();

        if(window.location.hash) {
            setTimeout(() => {
                let hash = window.location.hash;
                window.location.hash = '';
                window.location.hash = hash;
            }, 500);
        }

        this.buildAnchorLinks();
    }

    bindEvents() {
        this.elements.$tocHead.on('keydown click', this.onTocHeadClickEvent.bind(this));
        this.elements.$toc.on('keydown', this.onTocKeyboardEvent.bind(this));
        jQuery(window).on('resize', this.onResizeWindow.bind(this));
        jQuery(window).on('orientationchange', this.onOrientationChangeWindow());
    }

    buildAnchorLinks() {
        const target = this.elements.settings.target + " ";
        const titles = this.elements.settings.titles.split(',');
        const titlesTarget = (target + titles.join(',' + target)).split(',').join(',');

        jQuery(titlesTarget).each((index, element) => {
            const $this = jQuery(element);

            /** Saute les items du breadcrumb */
            if ($this.hasClass('eac-breadcrumbs-item')) {
                return true;
            }

            // Check si l'élément est caché 'hidden' TODO 'visible'
            if (!$this.is(":hidden")) {
                const trailerAnchor = this.elements.settings.trailer ? '-' + (index + 1) : ''; // Ajout du trailer
                const tag = $this.prop('tagName').toLowerCase(); // tagName = h1 ... h6
                const content = $this.text().trim(); // Le contenu du titre débarrassé d'éventuel tag link
                let contentAnchor;

                if (this.elements.settings.ancreAuto) {
                    contentAnchor = this.elements.settings.ancreDefault;
                } else {
                    // Format l'ancre 'more SEO friendly'
                    contentAnchor = content.replace(/[\$\*\^’&<>"'`=:\/`\\|_\s+]/g, '-').replace(/[#,;.]/g, '').replace(/-+/g, '-').toLowerCase();
                }

                const anchor = contentAnchor + trailerAnchor;

                // ID dans le titre cible de l'ancre + class
                $this.attr({ 'id': anchor }).addClass('toctoc-jump-link');

                // Ajour du lien dans le body de la TOC
                this.elements.$tocBody.append('<a href="#' + anchor + '" aria-label="' + this.elements.settings.ariaLabel + content + '"><p class="link link-' + tag + '"><i class="' + this.elements.settings.fontawesome + '" aria-hidden="true"></i>' + content + '</p></a>');
            }
        });

        // La liste des liens
        this.elements.$linksList = this.elements.$tocBody.find('a');

        // Ajout du picto de droite
        this.elements.$tocHead.append(this.elements.$tocHeadToggler);

        // Option d'ouverture
        if (!this.elements.settings.opened) {
            this.elements.$tocHeadToggler.text(this.elements.settings.headPicto[0]);
        } else {
            this.elements.$tocBody.css('display', 'block');
            this.elements.$tocHeadToggler.text(this.elements.settings.headPicto[1]);
        }

        // Affiche l'entête et le corps de la table
        this.elements.$toc.append(this.elements.$tocHead).append(this.elements.$tocBody);
    }

    onTocHeadClickEvent(evt) {
        if ('keydown' === evt.type) {
            const id = evt.code || evt.key || 0;
            if ('Enter' !== id && 'Space' !== id) {
                return;
            }
        }
        evt.preventDefault();
        this.elements.settings.opened ? this.elements.settings.opened = false : this.elements.settings.opened = true;

        if (this.elements.settings.opened) {
            this.elements.$tocHeadToggler.text(this.elements.settings.headPicto[1]);
            this.elements.$tocHead.attr('aria-expanded', 'true');
        } else {
            this.elements.$tocHeadToggler.text(this.elements.settings.headPicto[0]);
            this.elements.$tocHead.attr('aria-expanded', 'false');
        }
        this.elements.$tocBody.slideToggle(300);
    }

    onTocKeyboardEvent(evt) {
        const lastElement = document.activeElement;
        const id = evt.code || evt.key || 0;
        if ('Escape' === id) {
            this.elements.$tocHeadToggler.text(this.elements.settings.headPicto[0]);
            this.elements.$tocHead.attr('aria-expanded', 'false');
            this.elements.$tocBody.slideToggle(300);
            this.elements.$tocHead.trigger('focus');
        } else if ('ArrowDown' === id) {
            let currentElement = lastElement;
            evt.preventDefault();
            if (jQuery(currentElement).hasClass('toctoc-head')) {
                currentElement = this.elements.$linksList.get(0);
            } else if (jQuery(currentElement).attr('href')) {
                const currentLinkIndex = this.elements.$linksList.index(jQuery(currentElement));
                if (currentLinkIndex === (this.elements.$linksList.length - 1)) {
                    currentElement = this.elements.$linksList.get(0);
                } else if (currentLinkIndex + 1 < this.elements.$linksList.length) {
                    currentElement = this.elements.$linksList.get(currentLinkIndex + 1);
                } else {
                    currentElement = this.elements.$linksList.get(currentLinkIndex);
                }
            }
            jQuery(currentElement).trigger('focus');
        } else if ('ArrowUp' === id) {
            let currentElement = lastElement;
            evt.preventDefault();
            if (jQuery(currentElement).attr('href')) {
                const currentLinkIndex = this.elements.$linksList.index(jQuery(currentElement));
                if (currentLinkIndex - 1 > 0) {
                    currentElement = this.elements.$linksList.get(currentLinkIndex - 1);
                } else {
                    currentElement = this.elements.$linksList.get(0);
                }
            }
            jQuery(currentElement).trigger('focus');
        }
    }

    onResizeWindow() {
        // Calcule uniquement la largeur pour contourner la barre du navigateur qui s'efface sur les tablettes
        if (this.elements.settings.windowWith !== window.innerWidth) {
            this.elements.settings.windowWith = window.innerWidth;
            this.elements.settings.opened = false;
            this.elements.$tocHeadToggler.text(this.elements.settings.headPicto[0]);
            this.elements.$tocBody.slideUp(300);
        }
    }

    onOrientationChangeWindow() {
        window.dispatchEvent(new Event('resize'));
    }
}

jQuery(window).on('elementor/frontend/init', () => {
    elementorFrontend.elementsHandler.attachHandler('eac-addon-toc', widgetTableOfContent);
});
