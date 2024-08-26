/**
 * Description: Cette méthode est déclenchée lorsque le composant 'eac-addon-mega-menu' est chargé dans la page
 *
 * @param $element. Le contenu de la section/container
 * @since 2.1.0
 * @since 2.1.2 Nouvel element handler
 * Elementor 3.1.0 https://developers.elementor.com/a-new-method-for-attaching-a-js-handler-to-an-element/
 */
//if (typeof widgetMegaMenu !== 'function')
class widgetMegaMenu extends elementorModules.frontend.handlers.Base {
	getDefaultSettings() {
		return {
			selectors: {
				targetInstance: '.eac-mega-menu',
				parentElement: '.eac-mega-menu',
				target_nav: '.mega-menu_nav-wrapper',
				target_top_links: '.mega-menu_top-link',
				target_sub_links: '.mega-menu_sub-link',
				target_sub_menus: '.mega-menu_sub-menu',
				target_skip_grid: '.eac-skip-grid',
				button_toggle_open: '.mega-menu_flyout-open',
				button_toggle_close: '.mega-menu_flyout-close',
				button_svg_icon: '.icon-menu-toggle svg',
				cart_quantity: '#menu-item-mini-cart span.badge-cart__quantity',
				target_nav_menu: 'mega-menu_nav-menu',
				mini_cart: '#menu-item-mini-cart',
				mini_cart_links: '.mini-cart-product a',
				mini_cart_title: '#menu-item-mini-cart .widgettitle',
				bodyCheckout: 'woocommerce-account',
				bodyCart: 'woocommerce-cart',
			},
		};
	}

	getDefaultElements() {
		const selectors = this.getSettings('selectors');
		let components = {
			$targetInstance: this.$element.find(selectors.targetInstance),
			$parentElement: this.$element.find(selectors.parentElement).parent(),
			$target_nav: this.$element.find(selectors.target_nav),
			$target_top_links: this.$element.find(selectors.target_top_links),
			$target_sub_links: this.$element.find(selectors.target_sub_links),
			$target_sub_menus: this.$element.find(selectors.target_sub_menus),
			$target_skip_grid: this.$element.find(selectors.target_skip_grid),
			$button_toggle_open: this.$element.find(selectors.button_toggle_open),
			$button_toggle_close: this.$element.find(selectors.button_toggle_close),
			$top_button_svg_icon: this.$element.find(selectors.target_top_links).find(selectors.button_svg_icon),
			$sub_button_svg_icon: this.$element.find(selectors.target_sub_links).find(selectors.button_svg_icon),
			$cart_quantity: this.$element.find(selectors.cart_quantity),
			$target_nav_menu: this.$element.find(selectors.target_nav_menu),
			$mini_cart: this.$element.find(selectors.mini_cart),
			$mini_cart_links: this.$element.find(selectors.mini_cart_links),
			$mini_cart_title: this.$element.find(selectors.mini_cart_title),
			hasBodyClassCheckout: jQuery('body').hasClass(selectors.bodyCheckout),
			hasBodyClassCart: jQuery('body').hasClass(selectors.bodyCart),
			breakpoint: this.$element.find(selectors.target_nav).data('breakpoint'),
			isSticky: 'yes' === this.$element.find(selectors.target_nav).data('enable-fixed') ? true : false,
			mediaQuery: null,
			isBreakpointEnabled: false,
			optionsObserve: {
				root: null,
				rootMargin: "-40px 0px 0px 0px",
				threshold: 0,
			},
			fixedClass: 'menu-fixed',
			isEditMode: elementorFrontend.isEditMode(),
			isPreviewMode: false,
			hasMiniCart: this.$element.find(selectors.target_nav).data('mini-cart'),
			hasLastItemReverted: this.$element.find(selectors.target_nav).data('item-reverted'),
			hasMenuCache: this.$element.find(selectors.target_nav).data('menu-cache'),
			isMenuOrientedVertical: this.$element.hasClass('mega-menu_orientation-vrt'),
			adminBar: document.getElementById('wpadminbar'),
			$target_all_links: null,
		};

		components.mediaQuery = window.matchMedia(components.breakpoint);
		const queryString = window.location.search;
		const urlParams = new URLSearchParams(queryString);
		components.isPreviewMode = urlParams.has('preview') ? true : false;

		return components;
	}

	onInit() {
		super.onInit();

		if (!this.elements.isEditMode && !this.elements.isPreviewMode && this.elements.hasMenuCache) {
			this.setMenuClass();
		}

		// Le device est déjà responsive
		if (this.elements.mediaQuery.matches) {
			this.elements.$target_nav.addClass('breakpoint');
			this.elements.isBreakpointEnabled = true;
			this.elements.$button_toggle_open.attr('aria-hidden', 'false');
			this.elements.$button_toggle_close.attr('aria-hidden', 'false');
			this.widgetToggleOn();
		} else {
			this.bindMouseEvents();
		}

		/** Supprime le mini cart lorsque les pages panier et checkout sont affichées */
		if (this.elements.hasMiniCart && (this.elements.hasBodyClassCart || this.elements.hasBodyClassCheckout)) {
			this.elements.$mini_cart.remove();
		}

		this.elements.$mini_cart_title.attr('aria-hidden', 'true');
	}

	/**
	 * Créer la liste des événements et leurs callbacks
	 */
	bindEvents() {
		const that = this.elements;
		const self = this;

		window.setTimeout(() => {
			that.$target_all_links = that.$targetInstance.find('a');
			self.setSubMenuAriaAttributes();
			that.$target_all_links.on('keydown', self.bindKeyboardEvents.bind(self));

			if (that.hasMiniCart) {
				jQuery(document.body).on('removed_from_cart', self.onRemovedFromCart.bind(self));
				jQuery(document.body).trigger('removed_from_cart');
			}
		}, 1000);

		this.elements.mediaQuery.addEventListener('change', this.onMediaQueryChange.bind(this));

		// L'API IntersectionObserver existe (mac <= 11.1.2)
		if (window.IntersectionObserver && this.elements.isSticky && !this.elements.isEditMode) {
			const intersectObserver = new IntersectionObserver(this.observeElementInViewport.bind(this), this.elements.optionsObserve);
			intersectObserver.observe(this.elements.$parentElement[0]);
		}
	}

	setSubMenuAriaAttributes() {
		jQuery.each(jQuery('ul[role="menu"]', this.elements.$targetInstance), (indice, element) => {
			let $title = jQuery(element).parent('li').find('> a .nav-menu_item-title');
			const $id = jQuery(element).parent('li').attr('id');
			if ($title.length === 1) {
				jQuery(element).attr('aria-label', $title.text());
				if ($id) {
					jQuery(element).attr('id', 'sub-menu-' + $id.split('-')[2]);
				}
			} else {
				$title = jQuery(element).parent('li').find('> a .mega-menu_item-title');
				if ($title.length === 1) {
					jQuery(element).attr('aria-label', $title.text());
					if ($id) {
						jQuery(element).attr('id', 'sub-menu-' + $id.split('-')[2]);
					}
				}
			}
		});

		if (this.elements.hasMiniCart) {
			jQuery.each(jQuery('.mini-cart-product.mega-menu_sub-menu li.woocommerce-mini-cart-item.mini_cart_item', this.elements.$targetInstance), (indice, element) => {
				jQuery(element).attr('role', 'none');
			});

			jQuery.each(jQuery('.mini-cart-product.mega-menu_sub-menu a:not(.remove_from_cart_button):not(.button)', this.elements.$targetInstance), (indice, element) => {
				const $title = jQuery(element).text();
				jQuery(element).attr('role', 'menuitem');
				jQuery(element).attr('aria-label', 'View single product page: ' + jQuery.trim($title));
			});

			jQuery('.mini-cart-product.mega-menu_sub-menu ul.woocommerce-mini-cart.cart_list.product_list_widget', this.elements.$targetInstance).attr('role', 'menu');
			jQuery('.mini-cart-product.mega-menu_sub-menu .widget_shopping_cart_content', this.elements.$targetInstance).attr('role', 'menuitem');
			jQuery('.mini-cart-product.mega-menu_sub-menu .widget.woocommerce.widget_shopping_cart', this.elements.$targetInstance).attr('role', 'menuitem');
		}
	}

	bindKeyboardEvents(evt) {
		const id = evt.code || evt.key || 0;
		const lastElement = document.activeElement;
		const isMenuTopLink = jQuery(lastElement).hasClass('mega-menu_top-link');
		const $parentMenuTopLink = jQuery(lastElement).parents('li.mega-menu_top-item').find('a.mega-menu_top-link');
		const isLastMenuInverted = this.elements.$target_top_links.index($parentMenuTopLink) === (this.elements.$target_top_links.length - 1) && this.elements.hasLastItemReverted;
		const isMenuTopItem = jQuery(lastElement).parent('li').hasClass('mega-menu_top-item');
		const isMenuSubLink = jQuery(lastElement).hasClass('mega-menu_sub-link');
		const isMenuSubItem = jQuery(lastElement).parent('li').hasClass('mega-menu_sub-item');
		const $parentSubMenu = jQuery(lastElement).parent('li').parent('ul.mega-menu_sub-menu');
		const isMiniCartItem = jQuery(lastElement).parents('div').hasClass('widget_shopping_cart_content');
		const $parentMiniCart = jQuery(lastElement).parents('div.widget_shopping_cart_content');

		if ('Tab' === id && !evt.shiftKey) {
			if (isMenuTopLink) {
				const lastLinkIndex = this.elements.$target_top_links.index(jQuery(lastElement));
				if (lastLinkIndex + 1 < this.elements.$target_top_links.length) {
					const nextLinkElement = jQuery(this.elements.$target_top_links.get(lastLinkIndex + 1));
					evt.preventDefault();
					nextLinkElement.trigger('focus');
				} else {
					this.elements.$target_skip_grid.trigger('focus');
				}
			}
		} else if ('Tab' === id && evt.shiftKey && !jQuery(lastElement).hasClass('mega-menu_top-link')) {
			evt.preventDefault();
		} else if ('ArrowDown' === id) {
			let $childrenLinks;
			let childrenIndex;
			let nextLink;
			evt.preventDefault();

			if (isMenuTopItem && !this.elements.isMenuOrientedVertical) {
				$childrenLinks = jQuery(lastElement).parent('li').find('> ul > li a');

				jQuery($childrenLinks.get(0)).trigger('focus');
			} else if (isMenuSubItem || isMiniCartItem) {
				if (isMenuSubItem) {
					if (this.elements.isBreakpointEnabled && jQuery(lastElement).next('ul').css('display') === 'grid') {
						$childrenLinks = jQuery(lastElement).parent('li').find('> ul > li > a');
					} else {
						$childrenLinks = $parentSubMenu.find('> li > a');
					}
				} else {
					$childrenLinks = $parentMiniCart.find('a');
				}
				childrenIndex = $childrenLinks.index(jQuery(lastElement));

				/** dernier élément, on renvoie le focus au premier élément */
				if (childrenIndex === ($childrenLinks.length - 1)) {
					nextLink = $childrenLinks.get(0);
				} else {
					nextLink = childrenIndex + 1 < $childrenLinks.length ? $childrenLinks.get(childrenIndex + 1) : $childrenLinks.get(childrenIndex);
				}

				jQuery(nextLink).trigger('focus');
			} else if (!this.elements.isMenuOrientedVertical) {
				$childrenLinks = jQuery(lastElement).parents('li.menu-item-has-children').last().find('> ul > li > a');
				childrenIndex = $childrenLinks.index(jQuery(lastElement));
				nextLink = childrenIndex + 1 < $childrenLinks.length ? $childrenLinks.get(childrenIndex + 1) : $childrenLinks.get(childrenIndex);

				jQuery(nextLink).trigger('focus');
			}
		} else if ('ArrowUp' === id) {
			let $childrenLinks;
			let childrenIndex;
			let nextLink;
			const isTopParent = jQuery(lastElement).parent('li').parent('ul').parent('li').hasClass('mega-menu_top-item');
			evt.preventDefault();

			if (isMenuSubItem || isMiniCartItem) {
				if (isMenuSubItem) {
					$childrenLinks = $parentSubMenu.find('> li > a');
				} else {
					$childrenLinks = $parentMiniCart.find('a');
				}
				childrenIndex = $childrenLinks.index(jQuery(lastElement));

				if (isTopParent && childrenIndex === 0 && !this.elements.isMenuOrientedVertical) {
					nextLink = $parentMenuTopLink;
				} else {
					nextLink = childrenIndex - 1 >= 0 ? $childrenLinks.get(childrenIndex - 1) : $childrenLinks.get(childrenIndex);
				}

				jQuery(nextLink).trigger('focus');
			}
		} else if (('ArrowRight' === id && !isLastMenuInverted) || ('ArrowLeft' === id && isLastMenuInverted) && !this.elements.isBreakpointEnabled) {
			evt.preventDefault();

			if (isMenuSubLink) {
				const $childrenLinks = jQuery(lastElement).parent('li').find('> ul > li > a');

				jQuery($childrenLinks.get(0)).trigger('focus');
			} else if (isMenuTopLink && this.elements.isMenuOrientedVertical) {
				const $siblingsLinks = jQuery(lastElement).siblings('ul').first().find('> li > a');

				jQuery($siblingsLinks.get(0)).trigger('focus');
			}
		} else if (('ArrowLeft' === id && !isLastMenuInverted) || ('ArrowRight' === id && isLastMenuInverted) && !this.elements.isBreakpointEnabled) {
			evt.preventDefault();

			if (isMenuSubLink) {
				const isTopParent = jQuery(lastElement).parent('li').parent('ul').parent('li').hasClass('mega-menu_top-item');
				const $childrenLinks = jQuery(lastElement).parent('li').parent('ul').find('a');
				const childrenIndex = $childrenLinks.index(jQuery(lastElement));
				const $nextLink = jQuery(lastElement).parent('li').parent('ul').parent('li').find('a').first();

				if (childrenIndex === 0 && !isTopParent) {
					$nextLink.trigger('focus');
				} else if (childrenIndex === 0 && isTopParent && this.elements.isMenuOrientedVertical) {
					$parentMenuTopLink.trigger('focus');
				}
			}
		} else if ('Escape' === id) {
			if (isMenuTopLink) {
				this.elements.$target_skip_grid.trigger('focus');
			} else {
				const nextLinkIndex = this.elements.$target_top_links.index($parentMenuTopLink);

				jQuery(this.elements.$target_top_links.get(nextLinkIndex)).trigger('focus');
			}
		} else if ('Home' === id) {
			evt.preventDefault();
			jQuery(this.elements.$target_top_links.get(0)).trigger('focus');
		} else if ('End' === id) {
			evt.preventDefault();
			jQuery(this.elements.$target_top_links.get(this.elements.$target_top_links.length - 1)).trigger('focus');
		}
	}

	/**
	 * Traite les événements de changement détat du device
	 */
	onMediaQueryChange() {
		if (window.matchMedia(this.elements.breakpoint).matches) {
			this.elements.$target_nav.addClass('breakpoint');
			this.elements.isBreakpointEnabled = true;
			this.elements.$button_toggle_open.attr('aria-hidden', 'false');
			this.elements.$button_toggle_close.attr('aria-hidden', 'false');
			this.widgetToggleOn();
		} else {
			this.elements.$target_nav.removeClass('breakpoint');
			this.elements.isBreakpointEnabled = false;
			this.elements.$button_toggle_open.attr('aria-hidden', 'true');
			this.elements.$button_toggle_close.attr('aria-hidden', 'true');
			this.widgetToggleOff();
		}
		this.elements.$top_button_svg_icon.css('transform', 'rotate(0deg)');
		this.elements.$sub_button_svg_icon.css('transform', 'rotate(0deg)');
	}

	/**
	 * Ajoute les événements sur les boutons 'Menu & Close' ainsi que les top et sub link
	 * Reset l'état des boutons et des icones
	 * Cette méthode est appelée au chargement de la page et lors du changement d'état du device
	 */
	widgetToggleOn() {
		this.bindButtonsToggleEvents();
		this.unbindMouseEvents();

		this.elements.$button_toggle_open.css('display', 'block');
		this.elements.$button_toggle_close.css('display', 'none');
		this.elements.$targetInstance.find('[aria-expanded]').attr('aria-expanded', 'false');

		this.elements.$target_nav.css('display', 'none');

		this.elements.$target_top_links.nextAll('.mega-menu_sub-menu').css('display', 'none');
		this.elements.$target_sub_links.nextAll('.mega-menu_sub-menu').css('display', 'none');
	}

	/**
	 * Supprime les événements sur les boutons 'Menu & Close' ainsi que les top et sub link
	 * Reset l'état des boutons et des icones
	 * Cette méthode est appelée lors du changement d'état du device
	 */
	widgetToggleOff() {
		this.unbindButtonsToggleEvents();
		this.bindMouseEvents();

		this.elements.$button_toggle_open.add(this.elements.$button_toggle_close).css('display', 'none');
		this.elements.$targetInstance.find('[aria-expanded]').attr('aria-expanded', 'false');

		this.elements.$target_nav.css('display', 'block');

		this.elements.$target_sub_menus.css('display', 'none');
	}

	/**
	 * Ajout des événements sur les éléments concernés par le menu responsive
	 */
	bindButtonsToggleEvents() {
		this.elements.$button_toggle_open.on('click', this.onButtonToggleOpen.bind(this));
		this.elements.$button_toggle_close.on('click', this.onButtonToggleClose.bind(this));
		this.elements.$target_top_links.on('click', this.onTargetTopLink.bind(this));
		this.elements.$target_sub_links.on('click', this.onTargetSubLink.bind(this));
	}

	/**
	 * Suppression des événements sur les éléments concernés par le menu responsive
	 */
	unbindButtonsToggleEvents() {
		this.elements.$button_toggle_open.off('click');
		this.elements.$button_toggle_close.off('click');
		this.elements.$target_top_links.off('click');
		this.elements.$target_sub_links.off('click');
	}

	bindMouseEvents() {
		jQuery('li:has(ul[role="menu"])', this.elements.$targetInstance).on('focusin mouseenter', (evt) => {
			jQuery(evt.currentTarget).children('a[aria-haspopup="true"]').first().attr('aria-expanded', 'true');
		});

		jQuery('li:has(ul[role="menu"])', this.elements.$targetInstance).on('focusout mouseleave', (evt) => {
			jQuery(evt.currentTarget).children('a[aria-haspopup="true"]').first().attr('aria-expanded', 'false');
		});
	}

	unbindMouseEvents() {
		jQuery('li:has(ul[role="menu"])', this.elements.$targetInstance).off('focusin mouseenter');
		jQuery('li:has(ul[role="menu"])', this.elements.$targetInstance).off('focusout mouseleave');
	}

	/**
	 * L'observateur des événements du viewport
	 * @param {*} entries L'élément observé
	 * @param {*} observer L'observateur
	 */
	observeElementInViewport(entries, observer) {
		const target = entries[0].target;

		// L'objet est complètement visible
		//console.log('intersecting:' + entries[0].isIntersecting + ':' + target.className + ':' + entries[0].intersectionRatio);
		if (entries[0].intersectionRatio > 0) {
			this.elements.$targetInstance[0].classList.remove(this.elements.fixedClass);
			this.elements.$targetInstance[0].style.top = '';
		} else {
			this.elements.$targetInstance[0].classList.add(this.elements.fixedClass);
			if (this.elements.adminBar) {
				this.elements.$targetInstance[0].style.top = this.elements.adminBar.clientHeight + 'px';
			}
		}
	}

	/**
	 * Un élément a été supprimé du panier, on met à jour l'infobulle de l'item panier du menu
	 */
	onRemovedFromCart() {
		const that = this.elements;

		jQuery.ajax({
			url: eacUpdateCounter.ajax_url,
			type: 'post',
			data: {
				action: eacUpdateCounter.ajax_action,
				nonce: eacUpdateCounter.ajax_nonce,
			},
		}).done((response) => {
			if (response.success === true) {
				that.$cart_quantity.text(response.data);
			}
		});
	}

	/**
	 * Événement sur le bouton open du menu responsive
	 * @param {*} evt 
	 */
	onButtonToggleOpen(evt) {
		evt.stopImmediatePropagation();

		this.elements.$button_toggle_open.css('display', 'none').attr('aria-expanded', 'true');
		this.elements.$button_toggle_close.css('display', 'block').attr('aria-expanded', 'true');

		this.elements.$target_nav.toggle();
		this.elements.$button_toggle_close.focus();
	}

	/**
	 * Événement sur le bouton close du menu responsive
	 * @param {*} evt 
	 */
	onButtonToggleClose(evt) {
		evt.stopImmediatePropagation();

		this.elements.$button_toggle_close.css('display', 'none').attr('aria-expanded', 'false');
		this.elements.$button_toggle_open.css('display', 'block').attr('aria-expanded', 'false');

		this.elements.$target_nav.toggle();
		this.elements.$button_toggle_open.focus();

		// Icon SVG
		this.elements.$top_button_svg_icon.css('transform', 'rotate(0deg)');
		this.elements.$sub_button_svg_icon.css('transform', 'rotate(0deg)');

		this.elements.$target_top_links.nextAll('.mega-menu_sub-menu').css('display', 'none');
		this.elements.$target_sub_links.nextAll('.mega-menu_sub-menu').css('display', 'none');
	}

	/**
	 * Événement sur les liens des top menu (niveau 0)
	 * @param {*} evt L'événement click
	 */
	onTargetTopLink(evt) {
		const $thisTopLink = jQuery(evt.currentTarget);
		const $currentIconSvg = $thisTopLink.find('.icon-menu-toggle svg');

		// L'élément n'a pas de sous-menu
		if (!$thisTopLink.attr('aria-haspopup')) { return; }

		evt.stopImmediatePropagation();
		const $expanded = $thisTopLink.attr('aria-expanded') === 'true' ? 'false' : 'true';

		// Modifie l'attribut de tous les top links
		jQuery.each(this.elements.$target_top_links, (indice, element) => {
			if (jQuery(element).attr('aria-haspopup')) {
				jQuery(element).attr('aria-expanded', 'false');
			}
		});

		// Modifie l'attribut du top links courant
		$thisTopLink.attr('aria-expanded', $expanded);

		/**
		 * Les sous-menus sont fermés même si le top link s'ouvre ou se ferme
		 * hormis le sub-menu du top link
		 */
		this.elements.$target_sub_menus.not($thisTopLink.next('.mega-menu_sub-menu')).css('display', 'none').slideUp();

		jQuery.each(this.elements.$target_sub_links.not($thisTopLink.next('.mega-menu_sub-link')), (indice, element) => {
			if (jQuery(element).attr('aria-haspopup')) {
				jQuery(element).attr('aria-expanded', 'false');
			}
		});

		// Icon SVG des sous-menus
		this.elements.$sub_button_svg_icon.css('transform', 'rotate(0deg)');

		// Le mini cart n'a pas d'icone
		if ($currentIconSvg.length !== 0) {
			this.elements.$top_button_svg_icon.not($currentIconSvg).css('transform', 'rotate(0deg)');
			$currentIconSvg.css('transform', 'rotate(' + this.setRotateIconSvg($currentIconSvg[0]) + ')');
		} else {
			this.elements.$top_button_svg_icon.css('transform', 'rotate(0deg)');
		}

		// Le menu courant
		$thisTopLink.next('.mega-menu_sub-menu').slideToggle();
	}

	/**
	 * Événement sur les liens des sous-menus
	 * @param {*} evt L'événement click
	 */
	onTargetSubLink(evt) {
		const $thisSubLink = jQuery(evt.currentTarget);
		const $currentIconSvg = $thisSubLink.parent('li').find('.icon-menu-toggle svg').first();

		// L'élément n'a pas de sous-menu
		if (!$thisSubLink.attr('aria-haspopup')) { return; }

		evt.stopImmediatePropagation();
		const $expanded = $thisSubLink.attr('aria-expanded') === 'true' ? 'false' : 'true';

		// Modifie l'attribut de tous les sub links
		jQuery.each($thisSubLink.nextAll('.mega-menu_sub-menu'), (indice, element) => {
			if (jQuery(element).attr('aria-haspopup')) {
				jQuery(element).attr('aria-expanded', 'false');
			}
		});

		// Modifie l'attribut du top links courant
		$thisSubLink.attr('aria-expanded', $expanded);
		$currentIconSvg.css('transform', 'rotate(' + this.setRotateIconSvg($currentIconSvg[0]) + ')');

		$thisSubLink.nextAll('.mega-menu_sub-menu').slideToggle();
	}

	setRotateIconSvg(element) {
		const st = window.getComputedStyle(element, null);
		const tr = st.getPropertyValue('transform');
		let values = tr.split('(')[1];
		values = values.split(')')[0];
		values = values.split(',');
		const a = values[0];
		const b = values[1];
		const scale = Math.sqrt(a * a + b * b);
		const sin = b / scale;
		const angle = Math.round(Math.atan2(b, a) * (180 / Math.PI));
		const rotate = angle === 0 ? '-180deg' : '0deg';
		return rotate;
	}

	/**
	 * Ajouter les classes aux éléments du menu mis dans le cache
	 */
	setMenuClass() {
		this.elements.$targetInstance.find('.current-menu-ancestor, .current-menu-parent, .current_page_item, .current-menu-item').removeClass('current-menu-ancestor current-menu-parent current_page_item current-menu-item');
		this.elements.$targetInstance.find('a[aria-current="page"]').removeAttr('aria-current');

		const currentLocation = window.location.href.split(/\?|\#/g)[0];

		this.elements.$target_nav.find('li a').each(() => {
			if (currentLocation === jQuery(this).attr('href')) {
				const $nodeParent = jQuery(this).parents().closest('li').not('li#menu-item-mini-cart');
				jQuery(this).attr('aria-current', 'page');
				if ($nodeParent.length) {
					jQuery.fn.reverse = [].reverse;
					$nodeParent.reverse().each((index) => {
						if (index === 0) { jQuery(this).addClass('current-menu-item'); }
						else if (index === 1) { jQuery(this).addClass('current-menu-parent'); }
						else { jQuery(this).addClass('current-menu-ancestor'); }
					});
				}
			}
		});
	}
}

/**
 * Description: La class est créer lorsque le composant 'eac-addon-mega-menu' est chargé dans la page
 * https://github.com/elementor/elementor/issues/9781
 *
 * @param $element
 * @since 2.1.0
 */
window.addEventListener('DOMContentLoaded', () => {
	window.addEventListener('elementor/frontend/init', () => {
		elementorFrontend.elementsHandler.attachHandler('eac-addon-mega-menu', widgetMegaMenu);
	});
});
