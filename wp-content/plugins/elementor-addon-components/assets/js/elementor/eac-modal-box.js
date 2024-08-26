
/**
 * Description: Cette méthode est déclenchée lorsque la section 'eac-addon-modal-box' est chargée dans la page
 *
 * @param {selector} $element. Le contenu de la section
 * @since 1.6.1
 */

class widgetModalBox extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetWrapper: '.mb-modalbox__wrapper',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        let components = {
            $targetWrapper: this.$element.find(selectors.targetWrapper),
            settings: this.$element.find(selectors.targetWrapper).data('settings'),
        };

        components.$targetId = jQuery('#' + components.settings.data_id);
        components.$targetTrigger = components.$targetId.find('a.eac-accessible-link');
        components.$targetHiddenContent = components.$targetId.find('#modalbox-hidden-' + components.settings.data_id);
        components.$fbButtonClose = components.$targetHiddenContent.find('#my-fb-button');
        // CF7
        components.$targetCF7Div = components.$targetId.find('#modalbox-hidden-' + components.settings.data_id + ' .wpcf7');
        components.$targetCF7Form = components.$targetCF7Div.find('.wpcf7-form');
        components.$targetCF7Response = components.$targetCF7Form.find('.wpcf7-response-output');
        // Forminator
        components.$targetForminatorForm = components.$targetId.find('#modalbox-hidden-' + components.settings.data_id + ' div.mb-modalbox__hidden-content-body form.forminator-custom-form');
        components.$targetForminatorField = components.$targetForminatorForm.find('div.forminator-field');
        components.$targetForminatorError = components.$targetForminatorForm.find('span.forminator-error-message');
        components.$targetForminatorResponse = components.$targetForminatorForm.find('.forminator-response-message');
        // WPForms
        components.$targetWPformsDiv = components.$targetId.find('#modalbox-hidden-' + components.settings.data_id + ' .wpforms-container');
        components.$targetWPFormsForm = components.$targetWPformsDiv.find('.wpforms-form');
        components.$targetWPFormsFieldContainer = components.$targetWPFormsForm.find('.wpforms-field-container');
        // Mailpoet
        components.$targetMailpoetDiv = components.$targetId.find('#modalbox-hidden-' + components.settings.data_id + ' .mailpoet_form');
        components.$targetMailpoetForm = components.$targetMailpoetDiv.find('form.mailpoet_form');
        components.$targetMailpoetPara = components.$targetMailpoetForm.find('.mailpoet_paragraph');

        components.options = {
            baseClass: 'modal-' + components.settings.data_position,
            slideClass: components.settings.data_slideclass,
            smallBtn: true,
            buttons: [''],
            autoFocus: false,
            idleTime: false,
            animationDuration: 600,
            animationEffect: components.settings.data_effet,
            beforeLoad: (instance, current) => {
                // Reset Contact Mailpoet
                if (components.$targetMailpoetDiv.length > 0) {
                    components.$targetMailpoetForm.trigger('reset');
                    components.$targetMailpoetForm.find('p.mailpoet_validate_success').css('display', 'none');
                    components.$targetMailpoetForm.find('p.mailpoet_validate_error').css('display', 'none');
                    components.$targetMailpoetPara.find('ul.parsley-errors-list').remove();
                }
                // Reset Contact Form 7
                if (components.$targetCF7Div.length > 0) {
                    components.$targetCF7Form.trigger('reset');
                    components.$targetCF7Response.hide().empty().removeClass('wpcf7-mail-sent-ok wpcf7-mail-sent-ng wpcf7-validation-errors wpcf7-spam-blocked eac-wpcf7-SUCCESS eac-wpcf7-FAILED');
                    components.$targetCF7Form.find('span.wpcf7-not-valid-tip').remove();
                }
                // Reset WPForms
                if (components.$targetWPformsDiv.length > 0) {
                    components.$targetWPFormsForm.trigger('reset');
                    components.$targetWPFormsFieldContainer.find('div.wpforms-has-error').removeClass('wpforms-has-error');
                    components.$targetWPFormsFieldContainer.find('input.wpforms-error, textarea.wpforms-error').removeClass('wpforms-error');
                    components.$targetWPFormsFieldContainer.find('label.wpforms-error').remove();
                }
                // Reset Forminator
                if (components.$targetForminatorForm.length > 0) {
                    components.$targetForminatorForm.trigger('reset');
                    components.$targetForminatorField.removeClass('forminator-has_error');
                    components.$targetForminatorError.remove();
                }
                //jQuery(':input', $targetForminatorForm).not(':button, :submit, :reset, :hidden').removeAttr('checked').removeAttr('selected').not(':checkbox, :radio, select').val('');
            },
            afterLoad: (instance, current) => {
                components.$targetTrigger.attr('aria-expanded', 'true');
            },
            beforeShow: (instance, current) => {
                // Pour les mobiles force overflow du Body
                jQuery('body.fancybox-active').css({ 'overflow': 'hidden' });

                if (!components.settings.data_modal) {
                    const srcOwith = jQuery(current.src).outerWidth();
                    const slideOwidth = current.$slide.outerWidth();
                    const slidewidth = current.$slide.width();
                    instance.$refs.container.width(srcOwith + (slideOwidth - slidewidth));
                    instance.$refs.container.height(jQuery(current.src).outerHeight() + (current.$slide.outerHeight() - current.$slide.height()));
                }
            },
            afterClose: () => {
                components.$targetTrigger.attr('aria-expanded', 'false');
                // Reset overflow du Body
                jQuery('body.fancybox-active').css({ 'overflow': '' });
            },
            clickContent: (current, event) => {
                //return current.type === 'image' ? 'close' : '';
                //if(current.type === 'image') { return false; }
            },
        },
        components.optionsNoModal = {
            baseClass: 'mb-modalbox_no-modal no-modal_' + components.settings.data_position,
            hideScrollbar: false,
            clickSlide: 'close',
            //clickOutside: 'close',
            touch: false,
            backFocus: false,
        };

        return components;
    }

    onInit() {
        super.onInit();

        // Réservé pour d'éventuelles non modalbox
        if (!this.elements.settings.data_modal) {
            jQuery.extend(this.elements.options, this.elements.optionsNoModal);
        }

        /** Applique les options spécifiques à l'instance de la boîte courante */
        jQuery('[data-fancybox]', this.elements.$targetId).fancybox(this.elements.options);

        /**
         * Affichage automatique différé de la boîte modale après chargement de la page
         * Actif ou non dans l'éditeur
         */
        if ((this.elements.settings.data_trigger === 'pageloaded' && elementorFrontend.isEditMode() && this.elements.settings.data_active) ||
            (this.elements.settings.data_trigger === 'pageloaded' && !elementorFrontend.isEditMode())) {
            setTimeout(() => {
                jQuery.fancybox.open([
                    {
                        src: this.elements.$targetHiddenContent,
                        type: 'inline',
                        opts: this.elements.options
                    }
                ]);
            }, this.elements.settings.data_delay * 1000);
        }
    }

    bindEvents() {
        if (this.elements.$targetCF7Div.length > 0) {
            // Erreur wpcf7
            this.elements.$targetCF7Div.on('wpcf7invalid wpcf7spam wpcf7mailfailed', () => {
                this.elements.$targetCF7Response.addClass('eac-wpcf7-FAILED');
            });

            // Success wpcf7
            this.elements.$targetCF7Div.on('wpcf7mailsent', () => {
                this.elements.$targetCF7Response.addClass('eac-wpcf7-SUCCESS');
                setTimeout(() => { jQuery.fancybox.close(true); }, 3000);
            });
        }

        // Code pour le bouton 'close me' de la page de démonstration
        this.elements.$fbButtonClose.on('click touch', (evt) => {
            evt.preventDefault();
            jQuery.fancybox.close(true);
        });
    }
}

jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsModalBox = ($element) => {
        elementorFrontend.elementsHandler.addHandler(widgetModalBox, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/eac-addon-modal-box.default', EacAddonsModalBox);
});