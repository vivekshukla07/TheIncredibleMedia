
"use strict";

// Après le chargement de la page. Lazyload, Fancybox, fitText (Thème Hueman)
jQuery(document).ready(function () {

	// Pu.... de gestion des font-size dans le theme Hueman
	if (jQuery().fitText) {
		//console.log('Events Window =>', jQuery._data(jQuery(window)[0], "events"));
		jQuery(':header').each(function () {
			jQuery(this).removeAttr('style');
			jQuery(window).off('resize.fittext orientationchange.fittext');
			jQuery(window).unbind('resize.fittext orientationchange.fittext');
		});
	}

	// Implémente le proto startsWith pour IE11
	if (!String.prototype.startsWith) {
		String.prototype.startsWith = function (searchString, position) {
			position = position || 0;
			return this.substring(position, searchString.length) === searchString;
		};
	}

	// Initialisation de la Fancybox
	if (jQuery.fancybox) {
		const language = window.navigator.userLanguage || window.navigator.language;
		const lng = language.split("-");
		const langFr = {
			fr: {
				CLOSE: "Fermer",
				NEXT: "Suivant",
				PREV: "Précédent",
				ERROR: "Le contenu ne peut être chargé. <br/> Essayer plus tard.",
				PLAY_START: "Lancer le diaporama",
				PLAY_STOP: "Diaporama sur pause",
				FULL_SCREEN: "Plein écran",
				THUMBS: "Miniatures",
				DOWNLOAD: "Télécharger",
				SHARE: "Partager",
				ZOOM: "Zoom"
			}
		};
		//jQuery.extend(jQuery.fancybox.defaults.i18n, langFr);
		jQuery.fancybox.defaults.lang = lng[0];
		jQuery.fancybox.defaults.idleTime = false;
		/*jQuery.fancybox.defaults.buttons = [
			"zoom",
			"slideShow",
			"thumbs",
			"close"
		];*/
	}

	//Enable/Disable mouse focus
	jQuery(document.body).on('mousedown keydown', function (evt) {
		if (evt.type === 'mousedown') {
			jQuery(document.body).addClass('eac-using-mouse');
		} else {
			jQuery(document.body).removeClass('eac-using-mouse');
		}
	});

	function triggerKeyDownToClickEvent(evt) {
		const id = evt.code || evt.key || 0;
		if ('Space' === id) {
			evt.preventDefault();
			const activeElement = document.activeElement;
			if (jQuery(activeElement).attr('href') !== '#' && !jQuery(activeElement).attr('data-fancybox')) {
				activeElement.dispatchEvent(new MouseEvent('click', { cancelable: true }));
			} else {
				jQuery(activeElement).trigger('click');
			}
		}
	}

	/** Evénement sur les boutons et les liens avec la touche Space pour l'accessibilité */
	jQuery(document.body).on('keydown', '.button__readmore-wrapper a.button-readmore, .button__cart-wrapper a.button-cart, a.eac-accessible-link', triggerKeyDownToClickEvent);
	jQuery(document.body).on('keydown', '.mega-menu_nav-wrapper .mega-menu_top-link, .mega-menu_nav-wrapper .mega-menu_sub-link', triggerKeyDownToClickEvent);
	jQuery(document.body).on('keydown', '.sitemap-posts-list a, .swiper-pagination-bullet, #toctoc-body a, .eac-breadcrumbs-item a', triggerKeyDownToClickEvent);
	jQuery(document.body).on('keydown', '.woocommerce-mini-cart-item.mini_cart_item a', triggerKeyDownToClickEvent);
});

var is_mobile = function () {
	return (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));
};

/**
 * Object removeEmojis
 * Suppression de tous les emojis d'une chaine de caratères
 *
 * @return {string} nettoyée de tous les emojis
 */
var removeEmojis = function (myString) {
	if (!myString) { return ''; }
	return myString.replace(/([#0-9]\u20E3)|[\xA9\xAE\u203C\u2047-\u2049\u2122\u2139\u3030\u303D\u3297\u3299][\uFE00-\uFEFF]?|[\u2190-\u21FF][\uFE00-\uFEFF]?|[\u2300-\u23FF][\uFE00-\uFEFF]?|[\u2460-\u24FF][\uFE00-\uFEFF]?|[\u25A0-\u25FF][\uFE00-\uFEFF]?|[\u2600-\u27BF][\uFE00-\uFEFF]?|[\u2900-\u297F][\uFE00-\uFEFF]?|[\u2B00-\u2BF0][\uFE00-\uFEFF]?|(?:\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDEFF])[\uFE00-\uFEFF]?/g, '');
};
