/**
 * Description: Cette méthode est déclenchée lorsque les widgets sont chargés dans la page
 *
 * @param {selector} $element Le contenu du widget/container
 * 
 * @since 1.9.6
 */

class elementMotion extends elementorModules.frontend.handlers.Base {

    getDefaultElements() {
        return {
            $target: this.$element,
            elType: this.$element.data('element_type'),
            targetClassId: ".elementor-element-" + this.$element.data('id'),
            parentId: null,
            isEditMode: Boolean(elementorFrontend.isEditMode()),
            currentDevice: elementorFrontend.getCurrentDeviceMode(),
            settings: this.$element.data('eac_settings-motion') || {},
            motionSettings: 'data-eac_settings-motion',
            animationClass: 'eac-element_motion-class',
            animationBase: 'animate__animated',
            animationType: 'animate__',
            entranceSwitcher: 'eac_element_motion_effect',
            entranceId: 'eac_element_motion_id',
            entranceType: 'eac_element_motion_type',
            entranceDuration: 'eac_element_motion_duration',
            entranceTop: 'eac_element_motion_trigger',
            entranceBottom: 'eac_element_motion_trigger',
            entranceDevices: 'eac_element_motion_devices',
            optionsObserve: {
                rootMargin: '',
                threshold: 1,	// Ratio de visibilité du widget
            },
            motionOverflow: [
                'rubberBand',
                'wobble',
                'heartBeat'
            ],
        };
    }

    onInit() {
        super.onInit();

        if (this.elements.isEditMode) {
            this.buildDataSettingsInEditor();
        }

        // La class attendue
        if (!this.elements.$target.hasClass(this.elements.animationClass)) { // || this.elements.$target.hasClass('animated')) {
            return;
        }

        // Erreur settings
        if (Object.keys(this.elements.settings).length === 0) {
            this.cleanElements();
            return;
        }

        // Le device courant n'est dans la liste des devices sélectionnés
        if (jQuery.inArray(this.elements.currentDevice, this.elements.settings.devices) === -1) {
            this.cleanElements();
            return;
        }

        // Teste si le navigateur n'a pas la propriété 'prefers-reduced-motion' désactivée
        const isReduced = window.matchMedia('(prefers-reduced-motion: reduce)') === true || window.matchMedia('(prefers-reduced-motion: reduce)').matches === true;
        if (!!isReduced) {
            this.cleanElements();
            return;
        }

        /** Recherche de l'élément parent et supprime l'overflow pour les animations 'right' et 'left' et d'autres */
        if (this.elements.settings.type.indexOf('Right') !== -1 || this.elements.settings.type.indexOf('Left') !== -1 || jQuery.inArray(this.elements.settings.type, this.elements.motionOverflow)) {
            if ((this.elements.parentId = this.findParentTagId(this.elements.targetClassId)) !== false) {
                jQuery('[data-id="' + this.elements.parentId + '"]').css('overflow', 'hidden');
            }
        }

        // Le type de d'animation
        this.elements.animationType = this.elements.animationType + this.elements.settings.type;

        // Marge supérieur/inférieur de déclenchement
        this.elements.optionsObserve.rootMargin = "-" + this.elements.settings.top + "% 0% " + "-" + this.elements.settings.bottom + "% 0%";
    }

    bindEvents() {
        // L'API IntersectionObserver existe (mac <= 11.1.2)
        if (window.IntersectionObserver) {
            const intersectObserver = new IntersectionObserver(this.observeElementInViewport.bind(this), this.elements.optionsObserve);
            intersectObserver.observe(this.elements.$target[0]);
        }

        // Fin de l'animation, on nettoie tout
        this.elements.$target.on('animationend', () => { this.cleanElements(); });
    }

    buildDataSettingsInEditor() {
        const motion = this.getElementSettingInEditor(this.elements.entranceSwitcher, this.elements.$target) || 'no';
        const motionType = this.getElementSettingInEditor(this.elements.entranceType, this.elements.$target) || '';

        // Pas d'aniamtion
        if (motion === 'no' || motionType === '') {
            this.cleanElements();
            return;
        }

        const element_settings = {
            "type": motionType,
            "duration": this.getElementSettingInEditor(this.elements.entranceDuration, this.elements.$target) + 's' || '2s',
            "top": this.getElementSettingInEditor(this.elements.entranceTop, this.elements.$target)['sizes']['start'] || 10,
            "bottom": 100 - this.getElementSettingInEditor(this.elements.entranceBottom, this.elements.$target)['sizes']['end'] || 10,
            "devices": this.getElementSettingInEditor(this.elements.entranceDevices, this.elements.$target) || ['desktop', 'tablet'],
        };

        // Affecte l'attribut à l'élément
        this.elements.$target.attr(this.elements.motionSettings, JSON.stringify(element_settings));

        // Et à l'objet
        this.elements.settings = JSON.parse(this.elements.$target.attr(this.elements.motionSettings));

        // Ajout de la class
        this.elements.$target.addClass(this.elements.animationClass);

        // Cache l'élément
        this.elements.$target.css('visibility', 'hidden');
    }

    /**
     * getElementSettingInEditor
     *
     * Utile en mode édition
     * L'attribut data-eac_settings-motion n'est pas renseigné en mode édition
     * action 'render_animation' dans 'eac-injection-effect.php'
     * On passe par les propriétés Elementor
     *
     */
    getElementSettingInEditor(controlValue, $target) {
        let attributs = {};

        if (!elementorFrontend.hasOwnProperty('config')) { return; }
        if (!elementorFrontend.config.hasOwnProperty('elements')) { return; }
        if (!elementorFrontend.config.elements.hasOwnProperty('data')) { return; }

        const modelCID = this.elements.$target.data('model-cid');
        const editorElementData = elementorFrontend.config.elements.data[modelCID];
        if (!editorElementData) { return; }
        if (!editorElementData.hasOwnProperty('attributes')) { return; }

        attributs = editorElementData.attributes || {};
        if (!attributs[controlValue]) { return; }

        return attributs[controlValue];
    }

    cleanElements() {
        this.elements.settings = {};
        this.elements.$target.removeClass(this.elements.animationClass);
        this.elements.$target.removeClass(this.elements.animationBase);
        this.elements.$target.removeClass(this.elements.animationType);
        this.elements.$target.removeAttr(this.elements.motionSettings);
        this.elements.$target.css({ '--animate-duration': '', 'visibility': '' });
        if (this.elements.parentId !== null) {
            jQuery('[data-id="' + this.elements.parentId + '"]').css('overflow', '');
        }
        this.elements.$target.off('animationend');
    }

    observeElementInViewport(entries, observer) {
        //console.log(entries[0].intersectionRatio + "::" + entries[0].isIntersecting);
        // L'objet est complètement visible
        if (entries[0].isIntersecting) {
            const target = entries[0].target;

            // Affiche l'élément
            target.style.visibility = 'visible';

            // Affecte le durée de l'animation
            //target.style['--animate-duration'] = this.elements.settings.duration;
            target.style.setProperty('--animate-duration', this.elements.settings.duration);
            //target.style.setProperty('--animate-repeat', '3');

            // Ajout des class
            target.classList.add(this.elements.animationBase, this.elements.animationType);

            // Arrêt de l'observation
            observer.unobserve(target);
        }
    }

    /**
     * findParentTagId
     *
     * Recherche récursive montante de l'ID du parent 'section ou container' inner ou non
     * le type Elementor container est une div
     *
     */
    findParentTagId(childClass) {
        let element = document.querySelector(childClass);
        let tag = element.tagName.toLowerCase();

        // Boucle jusqu'au body
        while (tag !== 'body') {
            element = element.parentElement;
            const parentTag = element.tagName.toLowerCase();

            // C'est une 'section' ou une 'div'
            if (parentTag === 'section' || (parentTag === 'div' && element.hasAttribute('data-element_type') && element.getAttribute('data-element_type') === 'container')) {
                return element.dataset['id'];
                //return document.querySelector('[data-id="' + dataset + '"]').className;
            }
            tag = element.parentElement.tagName.toLowerCase();
        }
        return false;
    }
}

jQuery(window).on('elementor/frontend/init', () => {
    const EacAddonsElementMotion = ($element) => {
        elementorFrontend.elementsHandler.addHandler(elementMotion, {
            $element,
        });
    };

    elementorFrontend.hooks.addAction('frontend/element_ready/widget', EacAddonsElementMotion);
});
