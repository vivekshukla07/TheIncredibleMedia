/**
 * Description: Cette méthode est déclenchée lorsque la section 'eac-addon-open-streetmap' est chargée dans la page
 *
 * @param $element. Le contenu du composant
 * @since 1.8.8
 */

class widgetOpenStreetMap extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetInstance: '.eac-open-streetmap',
                targetWrapper: '.osm-map_wrapper',
                targetMarkerCenter: '.osm-map_wrapper-markercenter',
                targetMarkers: '.osm-map_wrapper-marker',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        return {
            $targetInstance: this.$element.find(selectors.targetInstance),
            $targetWrapper: this.$element.find(selectors.targetWrapper),
            $targetMarkerCenter: this.$element.find(selectors.targetMarkerCenter),
            $targetMarkers: this.$element.find(selectors.targetMarkers),
            targetNonce: this.$element.find(selectors.targetInstance).find('#osm_nonce').val(),
            settings: this.$element.find(selectors.targetWrapper).data('settings') || {},
            osmIconUrl: eacElementsPath.osmImages + 'osm-icons/',
            osmMarkerShadow: eacElementsPath.osmImages + 'marker-shadow.png',
            proxyPhp: eacElementsPath.proxies + 'proxy-osm.php',
            osmTilesFile: eacElementsPath.osmConfig + 'osmTiles.json',
            osmMap: null,
            mapData: {
                title: this.$element.find(selectors.targetMarkerCenter).find('.osm-map_marker-title')[0].innerText,
                content: this.$element.find(selectors.targetMarkerCenter).find('.osm-map_marker-content')[0].innerHTML,
                lat: this.$element.find(selectors.targetMarkerCenter).data('lat'),
                lng: this.$element.find(selectors.targetMarkerCenter).data('lng'),
            },
            fullscreenOptions: {
                position: this.$element.find(selectors.targetWrapper).data('settings').data_zoompos ? 'bottomleft' : 'topleft', // position du bouton calée sur le bouton de zoom
                title: 'Show me the fullscreen', // change the title of the button, default Full Screen
                titleCancel: 'Exit fullscreen mode', // change the title of the button when fullscreen is on, default Exit Full Screen
                content: null, // change the content of the button, can be HTML, default null
                forceSeparateButton: false, // force separate button to detach from zoom buttons, default false
                forcePseudoFullscreen: true, // force use of pseudo full screen even if full screen API is available, default false
                fullscreenElement: false // Dom element to render in full screen, false by default, fallback to map._container
            },
        };
    }

    onInit() {
        super.onInit();
        const that = this;

        if (Object.keys(this.elements.settings).length === 0) {
            return;
        }

        // Charge le contenu du fichier de configuration des tuiles
        fetch(this.elements.proxyPhp + '/?url=' + encodeURIComponent(this.elements.osmTilesFile) + '&id=' + this.elements.settings.data_id + '&nonce=' + this.elements.targetNonce)
            .then((response) => {
                return response.json();
            }).then((json) => {
                if (Object.keys(json).length > 0) {
                    this.elements.osmMap = this.displayOsmMap(json);
                    window.setTimeout(that.setAccessibilityElements.bind(that), 2000);
                } else {
                    console.log("EAC OSM: File is empty");
                }
            }).catch((error) => {
                console.log("EAC OSM: " + error.message + "::Line=" + error.lineNumber);
            });
    }

    bindEvents() {

        // Touche Escape
        this.elements.$targetInstance.on('keydown', (evt) => {
            const id = evt.code || evt.key || 0;
            if ('Escape' === id) {
                this.elements.$targetInstance.next('.eac-skip-grid').trigger('focus');
            }
        });

        const selector = '.leaflet-control-zoom-in, .leaflet-control-zoom-out, .leaflet-control-zoom-fullscreen, .leaflet-control-layers-toggle';
        this.elements.$targetWrapper.on('keydown', selector, (evt) => {
            const id = evt.code || evt.key || 0;
            if ('Space' === id) {
                evt.preventDefault();
                evt.currentTarget.dispatchEvent(new MouseEvent('click', { cancelable: true }));
            }
        });
    }

    displayOsmMap(osmTilesContent) {
        /** Liste des tuiles et des overlays */
        let baseLayers = {};
        let overlays = {};
        let markerArray = [];
        const geoJsonHeader = 'FeatureCollection';

        jQuery.each(osmTilesContent, (name, valeur) => {
            // Il y a une URL et des options
            if (valeur.url && valeur.url.length > 0 && valeur.options && Object.keys(valeur.options).length > 0) {
                const curLayer = L.tileLayer(valeur.url, valeur.options);
                const title = valeur.options.title ? valeur.options.title : name;
                const type = valeur.options.type ? valeur.options.type : 'tile';
                if (type === 'tile') {
                    baseLayers[title] = curLayer;
                } else {
                    overlays[title] = curLayer;
                }
            }
        });

        /**
         * Création de la carte
         * Safari click marqueurs ne fonctionne pas OSM 1.7.1 donc on passe l'option 'tap' à false
         */
        const map = L.map(this.elements.settings.data_id, {
            center: [this.elements.mapData.lat, this.elements.mapData.lng],
            layers: baseLayers[this.elements.settings.data_layer], // Les tuiles par défaut
            closePopupOnClick: this.elements.settings.data_clickpopup,
            zoom: this.elements.settings.data_zoom,
            zoomControl: !this.elements.settings.data_zoompos,
            tap: false,
        });

        // Ajout du control des tuiles (tiles) et des surcouches (overlays)
        const controlLayers = L.control.layers(baseLayers, overlays, { collapsed: this.elements.settings.data_collapse_menu }).addTo(map);
        /*L.DomEvent.off(controlLayers._container, {
            mouseover: controlLayers._expandSafely,
            mouseenter: controlLayers._expandSafely,
            //mouseleave: controlLayers.collapse,
            //mouseout: controlLayers.collapse
        }, controlLayers);*/

        // Propriétés du marker central
        const defaultIcon = new L.icon({
            iconUrl: this.elements.osmIconUrl + 'default.png',
            iconSize: [45, 45],
            iconAnchor: [22.5, 45],
            popupAnchor: [0, -45],
            shadowUrl: this.elements.osmMarkerShadow,
            shadowSize: [41, 41],
            shadowAnchor: [17, 41],
        });

        // Ajout du marker central à la map et affichage de la popup
        const markerCentralContent = "<div class='osm-map_popup-title'>" + this.elements.mapData.title + "</div><div class='osm-map_popup-content'>" + this.elements.mapData.content + "</div>";
        const markerCentral = L.marker([this.elements.mapData.lat, this.elements.mapData.lng], { icon: defaultIcon })
            .addTo(map)
            .bindPopup(markerCentralContent)
            .on('keydown', (evt) => {
                const id = evt.originalEvent.code || evt.originalEvent.key || 0;
                if ('Space' === id) {
                    evt.originalEvent.preventDefault();
                    jQuery(document.activeElement).trigger('click');
                }
            });

        if (this.elements.settings.data_openpopup) {
            markerCentral.openPopup();
        }

        // Ajout du marqueur à la liste des marqueurs
        markerArray.push(L.marker(new L.LatLng(this.elements.mapData.lat, this.elements.mapData.lng)));

        /**
         * Import du fichier geoJSON et boucle sur les marqueurs
         * ou boucle sur les markers inclus dans le source de la page (repeater Elementor)
         */
        if (this.elements.settings.data_import) {
            if (this.elements.settings.data_import_url !== '') {
                const sizes = this.elements.settings.data_import_sizes.split(',').map(Number);
                const iconAnchor = [sizes[0] / 2, sizes[1]];
                const popupAnchor = [0, -sizes[1]];

                // Change le marker par défaut
                const uniqueIcon = new L.icon({
                    iconUrl: this.elements.osmIconUrl + this.elements.settings.data_import_icon,
                    iconSize: sizes,
                    iconAnchor: iconAnchor,
                    popupAnchor: popupAnchor,
                    shadowUrl: this.elements.osmMarkerShadow,
                    shadowSize: [41, 41],
                    shadowAnchor: [17, 41],
                });

                jQuery.ajax({
                    url: this.elements.proxyPhp,
                    type: 'GET',
                    data: {
                        url: encodeURIComponent(this.elements.settings.data_import_url),
                        id: this.elements.settings.data_id,
                        nonce: this.elements.targetNonce
                    },
                }).done((jsonContent, textStatus, jqXHR) => {
                    //console.log(jqXHR.getResponseHeader('content-type'));
                    // Le contenu du fichier json est bien formé et valide
                    if (jsonContent.type && jsonContent.type === geoJsonHeader) {
                        const popupProperties = this.elements.settings.data_keywords.includes(',') ? this.elements.settings.data_keywords.split(',') : [];

                        // Construction du contenu des popups de chaque marqueur
                        const geoJsonLayer = L.geoJSON(jsonContent, {
                            pointToLayer: (feature, latLng) => {
                                return new L.Marker(latLng, { icon: uniqueIcon });
                            },
                            onEachFeature: (feature, member) => {
                                if (popupProperties.length > 0) {
                                    let popupContent = '';
                                    jQuery.each(popupProperties, (idx, property) => {
                                        property = property.split('|');
                                        const propertyGeo = property[0];
                                        const propertyLabel = property.length === 2 ? property[1] : property[0];
                                        // La propriété est renseignée
                                        if (feature.properties[propertyGeo]) {
                                            try {
                                                const url = new URL(feature.properties[propertyGeo]);
                                                popupContent += "<a href='" + url + "'>" + propertyLabel + "</a><br/>";
                                            } catch {
                                                popupContent += "<div class='osm-map_popup-content'><span class='osm-map_popup-label'>" + propertyLabel + ":</span><span class='osm-map_popup-value'> " + feature.properties[propertyGeo] + '</span></div>';
                                            }
                                        }
                                    });
                                    // Ajout du popup
                                    member.bindPopup(popupContent)
                                        .on('keydown', (evt) => {
                                            const id = evt.originalEvent.code || evt.originalEvent.key || 0;
                                            if ('Space' === id) {
                                                evt.originalEvent.preventDefault();
                                                jQuery(document.activeElement).trigger('click');
                                            }
                                        });
                                }
                            }
                        });

                        // Création du tableau des clusters
                        const markerCluster = L.markerClusterGroup().addLayer(geoJsonLayer);

                        // Ajoute les clusters à la carte et zomm automatique
                        map.addLayer(markerCluster).fitBounds(markerCluster.getBounds());

                    } else {
                        alert(JSON.stringify(jsonContent));
                    }
                }).fail((jqXHR, textStatus, errorThrown) => {
                    alert(errorThrown);
                });
            }
        } else {
            jQuery.each(this.elements.$targetMarkers, (index, marker) => { // Marqueurs non importés (repeater Elementor)
                const lat = jQuery(marker).data('lat');
                const lng = jQuery(marker).data('lng');
                const icon = jQuery(marker).data('icon');
                const title = jQuery(marker).find('.osm-map_marker-title')[0].innerText;
                const content = jQuery(marker).find('.osm-map_marker-content')[0].innerHTML;

                const sizes = jQuery(marker).data('sizes').split(',').map(Number);
                const iconAnchor = [sizes[0] / 2, sizes[1]];
                const popupAnchor = [0, -sizes[1]];

                // Affecte le chemin et les propriétés de la nouvelle icone
                const customIcon = new L.icon({
                    iconUrl: this.elements.osmIconUrl + icon,
                    iconSize: sizes,
                    iconAnchor: iconAnchor,
                    popupAnchor: popupAnchor,
                    shadowUrl: this.elements.osmMarkerShadow,
                    shadowSize: [41, 41],
                    shadowAnchor: [17, 41],
                });

                // Ajout du marker à la map
                L.marker([lat, lng], { icon: customIcon })
                    .addTo(map)
                    .bindPopup("<div class='osm-map_popup-title'>" + title + "</div><div class='osm-map_popup-content'>" + content + "</div>")
                    .on('keydown', (evt) => {
                        const id = evt.originalEvent.code || evt.originalEvent.key || 0;
                        if ('Space' === id) {
                            evt.originalEvent.preventDefault();
                            jQuery(document.activeElement).trigger('click');
                        }
                    });

                // Ajout du marqueur à la liste des marqueurs
                markerArray.push(L.marker(new L.LatLng(lat, lng)));
            });

            // Zoom automatique
            if (markerArray.length > 0 && this.elements.settings.data_zoomauto) {
                const group = L.featureGroup(markerArray);
                map.fitBounds(group.getBounds(), { padding: [50, 50] });
            }

        }

        // Positionne le control zoom
        if (this.elements.settings.data_zoompos) {
            L.control.zoom({ position: 'bottomleft' }).addTo(map);
        }

        // Fullscreen
        if (this.elements.settings.data_fullscreen) {
            L.control.fullscreen(this.elements.fullscreenOptions).addTo(map);
        }

        // Supprime le zoom roulette de la souris
        this.elements.settings.data_wheelzoom === false ? map.scrollWheelZoom.disable() : '';

        // Supprime le zoom double click
        this.elements.settings.data_dblclick === false ? map.doubleClickZoom.disable() : '';

        // Supprime le fond de carte draggable
        this.elements.settings.data_draggable === false ? map.dragging.disable() : '';

        return map;
    }

    setAccessibilityElements() {
        /** Ajout des attributs à la liste des couches et des surcouches */
        jQuery('.leaflet-control-layers-base', this.elements.$targetWrapper).prepend("<div class='osm-map_layers-title'>Tiles Layer</div>");
        jQuery('.leaflet-control-layers-overlays', this.elements.$targetWrapper).prepend("<div class='osm-map_layers-title'>Overlays</div>");

        jQuery('.leaflet-tile-container', this.elements.$targetWrapper).attr({ 'role': 'img', 'aria-label': 'Tiles layer' });
        jQuery('.leaflet-tile-container img', this.elements.$targetWrapper).attr({ 'role': 'presentation', 'alt': '' });
        jQuery('.leaflet-marker-icon.marker-cluster', this.elements.$targetWrapper).attr('aria-label', 'Marker cluster');
        jQuery('.leaflet-control-layers-toggle', this.elements.$targetWrapper).attr('aria-label', 'Open/Close selection of tiles and overlays');

        this.elements.osmMap.eachLayer((layer) => {
            if (layer instanceof L.Marker) {
                const popup = layer.getPopup();
                if (popup) {
                    const content = popup.getContent();
                    if (content && jQuery(content).length === 2) {
                        const contentText = jQuery(content).eq(0).text().length > 0 ? jQuery(content).eq(0).text() : 'Marker';
                        jQuery(layer._icon).attr('aria-label', 'Marker ' + contentText);
                        jQuery(layer._icon).attr('alt', 'Marker ' + contentText);
                    } else {
                        jQuery(layer._icon).attr('aria-label', 'Marker');
                    }
                }
            }
        });
    }
}

/**
 * Description: La class est créer lorsque le composant 'eac-addon-open-streetmap' est chargé dans la page
 *
 * @param elements les éléments du composant 'eac-addon-open-streetmap'
 */
window.addEventListener('DOMContentLoaded', () => {
    window.addEventListener('elementor/frontend/init', () => {
        elementorFrontend.elementsHandler.attachHandler('eac-addon-open-streetmap', widgetOpenStreetMap);
    });
});
