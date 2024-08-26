
/**
 * Description: ES6 class Swipper exportée pour 'acf-relationship.js' 'eac-image-gallery.js' 'eac-post-grid.js'
 *
 * @since 2.1.3
 */
export default class EacSwiper {
	constructor(settings, $targetInstance) {
		this.settings = settings;
		this.$targetInstance = $targetInstance;
		this.$target = this.$targetInstance.find('.swiper-wrapper') || {};
		this.$swiperNext = this.$targetInstance.find('.swiper-button-next') || {};
		this.$swiperPrev = this.$targetInstance.find('.swiper-button-prev') || {};
		this.$swiperBullets = this.$targetInstance.find('.swiper-pagination-clickable span.swiper-pagination-bullet') || {};
		this.swiperOptions = {
			touchEventsTarget: 'wrapper',
			watchOverflow: true,
			autoplay: {
				enabled: this.settings.data_sw_autoplay,
				delay: this.settings.data_sw_delay,
				disableOnInteraction: false,
				pauseOnMouseEnter: true,
				reverseDirection: this.settings.data_sw_rtl
			},
			direction: this.settings.data_sw_dir,
			loop: this.settings.data_sw_autoplay === true ? this.settings.data_sw_loop : false,
			speed: 1000,
			grabCursor: true,
			watchSlidesProgress: true,
			slidesPerView: this.settings.data_sw_imgs,
			centeredSlides: this.settings.data_sw_centered,
			loopedSlides: this.settings.data_sw_imgs === 'auto' ? 2 : null,
			effect: this.settings.data_sw_effect,
			//updateOnWindowResize: true,
			//allowTouchMove: false,
			//preventClicks: false,
			//preventClicksPropagation: false,
			//noSwiping: false,
			//noSwipingClass: 'swiper-no-swiping',
			//noSwipingSelector: 'button',
			//a11y: false,
			//simulateTouch: true,
			//touchRatio: 0,
			//centeredSlidesBounds: true,
			freeMode: {
				enabled: this.settings.data_sw_free,
				momentumRatio: 1,
			},
			spaceBetween: this.settings.data_sw_type ? parseInt(jQuery(':root').css('--eac-acf-relationship-grid-margin')) : 0,
			coverflowEffect: {
				rotate: 45,
				slideShadows: true,
			},
			creativeEffect: {
				prev: {
					translate: [0, 0, 0],
				},
				next: {
					translate: ["100%", 0, 0],
				},
			},
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},
			pagination: {
				el: '.swiper-pagination-bullet',
				type: 'bullets',
				clickable: this.settings.data_sw_pagination_click,
				/*renderBullet: function (index, className) {
					className += ' eac-pagination-bullet';
					return '<span class="' + className + '">' + (index + 1) + '</span>';
				},*/
			},
			scrollbar: {
				//enabled: this.settings.data_sw_scroll,
				el: '.swiper-scrollbar',
			},
			breakpoints: {
				// when window width is >= 0px
				0: {
					slidesPerView: 1,
				},
				// when window width is >= 460px
				460: {
					slidesPerView: this.settings.data_sw_imgs === 'auto' ? 'auto' : parseInt(this.settings.data_sw_imgs, 10) > 2 ? this.settings.data_sw_imgs - 2 : this.settings.data_sw_imgs,
				},
				// when window width is >= 767px
				767: {
					slidesPerView: this.settings.data_sw_imgs === 'auto' ? 'auto' : parseInt(this.settings.data_sw_imgs, 10) > 1 ? this.settings.data_sw_imgs - 1 : this.settings.data_sw_imgs,
				},
				// when window width is >= 1024px
				1024: {
					slidesPerView: this.settings.data_sw_imgs === 'auto' ? 'auto' : parseInt(this.settings.data_sw_imgs, 10) > 1 ? this.settings.data_sw_imgs - 1 : this.settings.data_sw_imgs,
				},
				// when window width is >= 1200px
				1200: {
					slidesPerView: this.settings.data_sw_imgs,
				}
			},
		};

		this.swiper = new Swiper(this.$targetInstance[0], this.swiperOptions);
		this.swiperEnabled = this.swiper.enabled;
		if ( this.swiperEnabled ) {
			this.setSwiperEvents();
		}
	}

	isEnabled() {
		return this.swiperEnabled;
	}

	setSwiperEvents() {
		if ( this.$swiperNext ) {
			this.$swiperNext.on('touchend', (evt) => {
				evt.preventDefault();
				this.swiper.slideNext();
			});
		}

		if ( this.$swiperPrev ) {
			this.$swiperPrev.on('touchend', (evt) => {
				evt.preventDefault();
				this.swiper.slidePrev();
			});
		}

		if ( this.$swiperBullets ) {
			this.$swiperBullets.each((index, bullet) => {
				jQuery(this).on('touchend', { slidenum: index }, (evt) => {
					evt.preventDefault();

					if (this.swiper.params.loop === true) {
						this.swiper.slideToLoop(evt.data.slidenum);
					} else {
						this.swiper.slideTo(evt.data.slidenum);
					}

					if (this.swiper.params.autoplay.enabled === true && this.swiper.autoplay.paused === true) {
						this.swiper.autoplay.paused = false;
						this.swiper.autoplay.run();
					}
				});
			});
		}

		/** Accessibilité */
		if ( this.$target ) {
			this.$target.on('focusin', () => {
				if (this.swiper.params.autoplay.enabled === true) {
					this.swiper.autoplay.stop();
				}
			});

			this.$target.on('focusout', () => {
				if (this.swiper.params.autoplay.enabled === true) {
					this.swiper.autoplay.start();
				}
			});
		}
	}
}
