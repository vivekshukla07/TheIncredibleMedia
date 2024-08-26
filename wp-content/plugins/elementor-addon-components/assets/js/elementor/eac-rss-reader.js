
/**
 * Description: Cette méthode est déclenchée lorsque la section 'eac-addon-lecteur-rss' est chargée dans la page
 *
 * @param {selector} $element. Le contenu de la section
 * @since 1.0.0
 */
import EacReadTheFeed from '../modules/eac-ajax-read-feed.js';

class widgetRssReader extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetInstance: '.eac-rss-galerie',
                targetSelect: '.select__options-items',
                targetButton: '#rss__read-button',
                targetHeader: '.rss-item__header',
                targetLoader: '#rss__loader-wheel',
                targetNonce: '#rss_nonce',
                target: '.rss-galerie',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        return {
            $targetInstance: this.$element.find(selectors.targetInstance),
            $targetSelect: this.$element.find(selectors.targetSelect),
            $targetButton: this.$element.find(selectors.targetButton),
            $targetHeader: this.$element.find(selectors.targetHeader),
            $targetLoader: this.$element.find(selectors.targetLoader),
            $target: this.$element.find(selectors.target),
            targetNonce: this.$element.find(selectors.targetNonce).val(),
            settings: this.$element.find(selectors.target).data('settings') || {},
            instanceAjax: null,
            is_ios: /(Macintosh|iPhone|iPod|iPad).*AppleWebKit.*Safari/i.test(navigator.userAgent),
        };
    }

    onInit() {
        super.onInit();

        // Première valeur de la liste par défaut
        this.elements.$targetSelect.find('option:first').attr('selected', 'selected');
    }

    bindEvents() {
        if (Object.keys(this.elements.settings).length > 0) {
            this.elements.$targetSelect.on('change', this.onSelectChange.bind(this));
            this.elements.$targetButton.on('click touch', this.onTriggerButton.bind(this));
            this.elements.$targetInstance.on('keydown', this.onTriggerKeyboard.bind(this));
            jQuery(document).on('ajaxComplete', this.ajaxQueryComplete.bind(this));
        }
    }

    onSelectChange(evt) {
        evt.preventDefault();
        this.elements.$targetLoader.hide();
        this.elements.$target.attr('aria-busy', 'false');
        this.elements.$targetButton.attr('aria-expanded', 'false');
        this.elements.$target.empty();
        this.elements.$targetHeader.html('');
    }

    onTriggerButton(evt) {
        evt.preventDefault();
        this.elements.$targetButton.attr('aria-expanded', 'true');
        this.elements.$target.empty();
        this.elements.$targetHeader.html('');

        /** Initialisation de l'objet Ajax avec l'url du flux, du nonce et de l'ID du composant */
        this.elements.instanceAjax = new EacReadTheFeed(
            jQuery('.select__options-items option:selected', this.elements.$targetInstance).val().replace(/\s+/g, ''),
            this.elements.targetNonce,
            this.elements.settings.data_id
        );
        this.elements.$targetLoader.show();
        this.elements.$target.attr('aria-busy', 'true');
    }

    onTriggerKeyboard(evt) {
        const id = evt.code || evt.key || 0;
        if (!this.elements.$target.is(':empty')) {
            if ('Escape' === id) {
                evt.preventDefault();
                this.elements.$targetButton.attr('aria-expanded', 'false');
                this.elements.$target.empty();
                this.elements.$targetHeader.html('');
                this.elements.$targetSelect.trigger('focus');
            } else if ('End' === id) {
                evt.preventDefault();
                const $endArticle = this.elements.$target.find('article').find('a').last();
                $endArticle.trigger('focus');
            } else if ('Home' === id) {
                evt.preventDefault();
                const $homeArticle = this.elements.$target.find('article').find('a').first();
                $homeArticle.trigger('focus');
            }
        }
    }

    ajaxQueryComplete(event, xhr, ajaxSettings) {
        if (this.elements.instanceAjax !== null && ajaxSettings.ajaxOptions && ajaxSettings.ajaxOptions === this.elements.instanceAjax.getOptions()) { // Le même random number généré lors de la création de l'objet Ajax
            event.stopImmediatePropagation();
            this.elements.$targetLoader.hide();
            this.elements.$target.attr('aria-busy', 'false');

            // Les items à afficher
            const allItems = this.elements.instanceAjax.getItems();

            // Une erreur Ajax ??
            if (allItems.headError) {
                this.elements.$targetHeader.html('<span style="text-align:center; word-break:break-word;"><p>' + allItems.headError + '</p></span>');
                return false;
            }

            // Pas d'item
            if (!allItems.rss) {
                this.elements.$targetHeader.html('<span style="text-align: center">Nothing to display</span>');
                return false;
            }

            const Items = allItems.rss;
            const Profile = allItems.profile;
            const $wrapperHeadContent = jQuery('<div/>', { class: 'rss-item__header-content' });

            if (Profile.headLogo) {
                this.elements.$targetHeader.append('<div class="rss-item__header-img"><a href="' + Profile.headLink + '" aria-label="View RSS feed provider ' + Profile.headTitle + '"><img class="eac-image-loaded" src="' + Profile.headLogo + '" alt="' + Profile.headTitle + '"></a></div>');
            }
            $wrapperHeadContent.append('<span><a href="' + Profile.headLink + '" aria-label="View RSS feed provider ' + Profile.headTitle + '"><h2>' + Profile.headTitle.substring(0, 27) + '...</h2></a></span>');
            $wrapperHeadContent.append('<span>' + Profile.headDescription + '</span>');
            this.elements.$targetHeader.append($wrapperHeadContent);

            // Parcours de tous les items à afficher
            jQuery.each(Items, (index, item) => {
                if (index >= this.elements.settings.data_nombre) { // Nombre d'item à afficher
                    return false;
                }

                const $wrapperItem = jQuery('<article/>', { class: 'rss-galerie__item' });
                const $wrapperContent = jQuery('<div/>', { class: 'rss-galerie__content' });
                const $wrapperContentInner = jQuery('<div/>', { class: 'rss-galerie__content-inner' });

                /** Ajout du support de l'audio, de la vidéo et du PDF */
                if (item.img && this.elements.settings.data_img) {
                    let img = '';
                    let videoattr = '';
                    const titreImg = jQuery.trim(item.title.replace(/"/g, " "));

                    if (item.img.match(/\.mp3|\.m4a/)) { // Flux mp3
                        img = '<div class="rss-galerie__item-image">' +
                            '<audio aria-label="Listen feed ' + titreImg + '" controls preload="none" src="' + item.img + '" type="audio/mp3"></audio>' +
                            '</div>';
                    } else if (item.img.match(/\.mp4|\.m4v/)) { // Flux mp4
                        videoattr = is_ios ? '<video aria-label="View feed video ' + titreImg + '" controls preload="metadata" type="video/mp4">' : '<video controls preload="none" type="video/mp4">';
                        img = '<div class="rss-galerie__item-image">' +
                            videoattr +
                            '<source src="' + item.img + '">' +
                            "Your browser doesn't support embedded videos" +
                            '</video>' +
                            '</div>';
                    } else if (item.img.match(/\.pdf/)) { // Fichier PDF
                        img = '<div class="rss-galerie__item-image" aria-label="View PDF file ' + titreImg + '">' +
                            '<a href="' + item.imgLink + '" data-elementor-open-lightbox="no" data-fancybox="rss-gallery" data-caption="' + titreImg + '">' +
                            '<i class="far fa-file-pdf" aria-hidden="true"></i></a></div>';
                    } else if (this.elements.settings.data_lightbox) { // Fancybox activée
                        img = '<div class="rss-galerie__item-image">' +
                            '<a href="' + item.imgLink + '" class="eac-accessible-link" aria-label="View feed image ' + titreImg + '" data-elementor-open-lightbox="no" data-fancybox="rss-gallery" data-caption="' + titreImg + '">' +
                            '<img class="img-focusable eac-image-loaded" src="' + item.img + '"></a></div>';
                    } else if (this.elements.settings.data_image_link) { // Lien de l'article sur l'image
                        img = '<div class="rss-galerie__item-image">' +
                            '<a href="' + item.lien + '" class="eac-accessible-link" aria-label="View feed ' + titreImg + '">' +
                            '<img class="img-focusable eac-image-loaded" src="' + item.img + '"></a></div>';
                    } else {
                        img = '<div class="rss-galerie__item-image"><img class="eac-image-loaded" src="' + item.img + '"></div>';
                    }
                    $wrapperContent.append(img);
                }

                // Ajout du titre
                if (this.elements.settings.data_title) {
                    item.title = item.title.split(' ', 12).join().replace(/,/g, " ") + '...'; // Afficher 12 mots dans le titre
                    let titre = '';
                    if (this.elements.settings.data_title_link) {
                        titre = '<div class="rss-galerie__item-link-post">' +
                            '<a href="' + item.lien + '" class="eac-accessible-link" aria-label="View feed ' + item.title + '">' +
                            '<h2 class="rss-galerie__item-titre">' + item.title + '</h2></a></div>';
                    } else {
                        titre = '<div class="rss-galerie__item-link-post"><h2 class="rss-galerie__item-titre">' + item.title + '</h2></div>';
                    }
                    $wrapperContentInner.append(titre);
                }

                // Ajout du nombre de mots de la description
                if (this.elements.settings.data_excerpt) {
                    item.description = removeEmojis(item.description);
                    item.description = item.description.split(' ', this.elements.settings.data_excerpt_lenght).join().replace(/,/g, " ") + '[...]';
                    // Ajout de la description
                    const description = '<div class="rss-galerie__item-description"><p>' + item.description + '</p></div>';
                    $wrapperContentInner.append(description);
                }

                // Ajout du bouton readmore
                if (this.elements.settings.data_readmore) {
                    let buttonLabel = this.elements.settings.data_readmore_label;
                    let icon = '';
                    if (this.elements.settings.data_icon) {
                        icon = '<i class="' + this.elements.settings.data_icon + '" aria-hidden="true"></i>';
                        if ('before' === this.elements.settings.data_icon_pos) {
                            buttonLabel = icon + buttonLabel;
                        } else {
                            buttonLabel = buttonLabel + icon;
                        }
                    }
                    const buttonReadmore = '<div class="buttons-wrapper">' +
                        '<span class="button__readmore-wrapper">' +
                        '<a href="' + item.lien + '" class="button-readmore" aria-label="View feed ' + item.title + '">' + buttonLabel + '</a>' +
                        '</span></div>';
                    $wrapperContentInner.append(buttonReadmore);
                }

                // Ajout de la date de publication/Auteur article
                if (this.elements.settings.data_date) {
                    let $wrapperMetas = jQuery('<div/>', { class: 'rss-galerie__item-metas' });
                    const dateUpdate = '<span class="rss-galerie__item-date"><i class="fas fa-calendar" aria-hidden="true"></i>' + new Date(item.update).toLocaleDateString() + '</span>';
                    const Auteur = '<span class="rss-galerie__item-auteur"><i class="fas fa-user" aria-hidden="true"></i>' + item.author + '</span>';
                    $wrapperMetas.append(dateUpdate);
                    if (item.author) {
                        $wrapperMetas.append(Auteur);
                    }
                    $wrapperContentInner.append($wrapperMetas);
                }

                // Ajout dans les wrappers
                $wrapperContent.append($wrapperContentInner);
                $wrapperItem.append($wrapperContent);
                this.elements.$target.append($wrapperItem);
            });
            setTimeout(() => { jQuery('.rss-galerie__item', this.elements.$target).css({ transition: 'all 500ms linear', transform: 'scale(1)' }); }, 200);
        }
    }
}

/**
 * Description: La class est créer lorsque le composant 'eac-addon-lecteur-rss' est chargé dans la page
 *
 * @param elements (Ex: $scope)
 * @since 2.1.0
 */
jQuery(window).on('elementor/frontend/init', () => {
    elementorFrontend.elementsHandler.attachHandler('eac-addon-lecteur-rss', widgetRssReader);
});
