
/**
 * Description: Cette méthode est déclenchée lorsque le composant 'eac-addon-pdf-viewer' est chargé dans la page
 *
 * @param {selector} $element. Le contenu de la section/container
 * @since 1.8.9
 */

class widgetPdfViewer extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetInstance: '.eac-pdf-viewer',
                targetWrapper: '.fv-viewer__wrapper',
                targetTrigger: 'a.eac-accessible-link',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        let components = {
            $targetInstance: this.$element.find(selectors.targetInstance),
            $targetWrapper: this.$element.find(selectors.targetWrapper),
            $targetTrigger: this.$element.find(selectors.targetTrigger),
            settings: this.$element.find(selectors.targetWrapper).data('settings'),
            fileViewer: eacElementsPath.pdfJs + "viewer.html?file=",
            urlProxy: eacElementsPath.proxies + 'proxy-pdf.php',
            isSafariBrowser: window.safari !== undefined,
            dataURI: '',
        };
        components.$targetIframe = components.$targetWrapper.find('#iframe-' + components.settings.data_id);
        components.$targetFancybox = components.$targetWrapper.find('#fancybox-' + components.settings.data_id);
        components.$targetLoader = components.$targetWrapper.find('#fv-viewer_loader-wheel');
        components.targetNonce = components.$targetInstance.find('#pdf_nonce').val();
        components.options = '#pagemode=none&zoom=' + components.settings.data_zoom;

        return components;
        //const dataURI = "http://www.pdf995.com/samples/pdf.pdf";
        //const dataURI = "https://drive.google.com/uc?export=view&id=1Xkj8K4trKJfQgg0UgZ5UPTeZ1f-S5-68";
        //const dataURI = "https://blog.mozilla.org/press-fr/files/2013/06/FF_Desktop_guide_F_web.pdf";
        //const dataURI = "http://infolab.stanford.edu/pub/papers/google.pdf";
    }

    onInit() {
        super.onInit();

        if (this.elements.settings.data_url === '') { return; } else { this.elements.dataURI = this.elements.settings.data_url; }
        if (this.elements.$targetLoader.length > 0) { this.elements.$targetLoader.show(); }
        this.callAjaxPdf();
    }

    callAjaxPdf() {

        jQuery.ajax({
            url: this.elements.urlProxy,
            type: 'GET',
            data: {
                url: encodeURIComponent(this.elements.dataURI),
                id: this.elements.settings.data_id,
                nonce: this.elements.targetNonce
            },
            xhrFields: { responseType: 'blob' },
        }).done((response) => {
            if (this.elements.$targetLoader.length > 0) { this.elements.$targetLoader.hide(); }
            const contentType = response.type;
            const url = window.URL || window.webkitURL;
            const fileUrl = url.createObjectURL(response);
            const finalUrl = this.elements.fileViewer + fileUrl + this.elements.options;

            // Traitement de l'erreur ou pour SAFARI desktop utilise le lecteur intégré du navigateur
            if (contentType.startsWith("text/plain") || this.elements.isSafariBrowser) {
                this.elements.settings.data_display === 'fancybox' ? this.elements.$targetFancybox.attr("data-src", fileUrl) : this.elements.$targetIframe.attr('src', fileUrl);
            } else {
                // Utilise le lecteur PDF.JS + les options
                this.elements.settings.data_display === 'fancybox' ? this.elements.$targetFancybox.attr("data-src", finalUrl) : this.elements.$targetIframe.attr('src', finalUrl);
            }

            if (this.elements.settings.data_display === 'fancybox') {
                this.displayPdfOnFancybox();
            } else {
                this.displayPdfOnIframe();
            }
        }).fail((jqXHR, textStatus) => {
            alert(textStatus + "::" + jqXHR.statusText);
        });
    }

    displayPdfOnFancybox() {
        this.elements.$targetFancybox.fancybox({
            afterShow: (instance, current) => {
                /** Accessibilité */
                const $content = current.$content;
                $content.attr('aria-modal', 'true');
                $content.attr('role', 'dialog');
                this.elements.$targetTrigger.attr('aria-expanded', 'true');

                if (!this.elements.settings.data_toolleft) {
                    current.$slide.find('iframe').contents().find('#sidebarToggle').css('display', 'none');
                }
                if (!this.elements.settings.data_toolright) {
                    current.$slide.find('iframe').contents().find('#secondaryToolbarToggle').css('display', 'none');
                }
                if (!this.elements.settings.data_download) {
                    current.$slide.find('iframe').contents().find('#download').css('display', 'none');
                    current.$slide.find('iframe').contents().find('#secondaryDownload').css('display', 'none');
                }
                if (!this.elements.settings.data_print) {
                    current.$slide.find('iframe').contents().find('#print').css('display', 'none');
                    current.$slide.find('iframe').contents().find('#secondaryPrint').css('display', 'none');
                }
            },
            afterClose: () => {
                this.elements.$targetTrigger.attr('aria-expanded', 'false');
            }
        });
    }

    displayPdfOnIframe() {
        setTimeout(() => {
            //url.revokeObjectURL(fileUrl);
            if (!this.elements.settings.data_toolleft) {
                this.elements.$targetIframe.contents().find('#sidebarToggle').css('display', 'none');
            }
            if (!this.elements.settings.data_toolright) {
                this.elements.$targetIframe.contents().find('#secondaryToolbarToggle').css('display', 'none');
            }
            if (!this.elements.settings.data_download) {
                this.elements.$targetIframe.contents().find('#download').css('display', 'none');
                this.elements.$targetIframe.contents().find('#secondaryDownload').css('display', 'none');
            }
            if (!this.elements.settings.data_print) {
                this.elements.$targetIframe.contents().find('#print').css('display', 'none');
                this.elements.$targetIframe.contents().find('#secondaryPrint').css('display', 'none');
            }
        }, 2000);
    }
}

/**
 * Description: La class est créer lorsque le composant 'eac-addon-pdf-viewer' est chargé dans la page
 *
 * @param elements (Ex: $scope)
 * @since 2.1.2
 */
jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsPdfViewer = ($element) => {
        elementorFrontend.elementsHandler.addHandler(widgetPdfViewer, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/eac-addon-pdf-viewer.default', EacAddonsPdfViewer);
});
