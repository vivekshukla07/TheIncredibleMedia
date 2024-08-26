
/**
 * Description: Cette méthode est déclenchée lorsque la section 'eac-addon-chart' est chargée dans la page
 *
 * Notice: Les options par défaut (chartOptions.options) sont modifiées séquentiellement pour chaque type de diagramme
 * 
 * @param {selector} $element. Le contenu de la section
 * @since 1.5.4
 */

class widgetChart extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                targetInstance: '.eac-chart',
                targetClassWrapper: '.chart__wrapper',
                targetChartSwap: '.chart__wrapper-swap',
            },
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        let components = {
            $targetInstance: this.$element.find(selectors.targetInstance),
            $targetClassWrapper: this.$element.find(selectors.targetClassWrapper),
            $targetChartSwap: this.$element.find(selectors.targetChartSwap),
            settings: this.$element.find(selectors.targetClassWrapper).data('settings') || {},
            chartColors: {
                0: 'rgb(230, 25, 75)',      // red
                1: 'rgb(255, 225, 25)',     // yellow
                2: 'rgb(245, 130, 48)',     // orange
                3: 'rgb(60, 180, 75)',      // green
                4: 'rgb(70, 240, 240)',     // cyan
                5: 'rgb(0, 130, 200)',      // blue
                6: 'rgb(145, 30, 180)',     // purple
                7: 'rgb(240, 50, 230)',     // magenta
                8: 'rgb(210, 245, 60)',     // lime
                9: 'rgb(128, 128, 128)',    // grey
            },
            yLeftAxis: 'left-y-axis', // ID Y axe de gauche
            yRightAxis: 'right-y-axis', // ID Y axe de droite
            // Default Chart options
            chartOptions: {
                type: '', // settings.data_type,
                data: {
                    labels: [], // settings.data_labels.split(','),
                    datasets: [{
                        label: '', // settings.x_label,
                        data: [], // settings.y_data.split(/[;,]+/),
                        yAxisID: 'left-y-axis',
                    }],
                },
                options: {
                    layout: { padding: { left: 0, right: 0, top: 5, bottom: 10 } },
                    plugins: { datalabels: { display: false }, style: true },
                    responsive: true,
                    //maintainAspectRatio: true,
                    animation: { duration: 0 },
                    responsiveAnimationDuration: 0,
                    tooltips: { enabled: true, mode: 'index', displayColors: true },
                    title: { display: false },
                    legend: { display: false },
                    scales: {
                        xAxes: [{
                            display: true,
                            scaleLabel: { display: false },
                            gridLines: { display: false },
                            ticks: { display: true, beginAtZero: true }
                        }],
                        yAxes: [{
                            display: true,
                            position: 'left',
                            id: 'left-y-axis',
                            scaleLabel: { display: false },
                            gridLines: { display: false },
                            ticks: { display: true, beginAtZero: true },
                        },
                        {
                            display: false,
                            position: 'right',
                            id: 'right-y-axis',
                            scaleLabel: { display: false },
                            gridLines: { display: false },
                            ticks: { display: false },
                        }]
                    },
                    onResize: this.eacResizeChart.bind(this), // Responsive
                    inverted: false,               // Inversion Légend <-> X labels
                },
            },
            chartInstance: null, // l'instance de la chart
        };

        components.effectColors = {
            highlight: 'rgba(255, 255, 255, 0.75)',
            shadow: 'rgba(0, 0, 0, 0.5)',
            innerglow: 'rgba(255, 255, 0, 0.5)',
            outerglow: 'rgb(255, 255, 0)'
        };
        components.barStyle = {
            borderWidth: 0, bevelWidth: 2,
            bevelHighlightColor: components.effectColors.highlight,
            bevelShadowColor: components.effectColors.shadow
        };
        components.lineRadarStyle = {
            shadowOffsetX: 3,
            shadowOffsetY: 3,
            shadowBlur: 10,
            shadowColor: components.effectColors.shadow,
            pointRadius: 4,
            pointBevelWidth: 2,
            pointBevelHighlightColor: components.effectColors.highlight,
            pointBevelShadowColor: components.effectColors.shadow,
            pointHoverRadius: 6,
            pointHoverBevelWidth: 3,
            pointHoverInnerGlowWidth: 20,
            pointHoverInnerGlowColor: components.effectColors.innerglow,
            pointHoverOuterGlowWidth: 20,
            pointHoverOuterGlowColor: components.effectColors.outerglow
        };
        components.piePolarStyle = {
            shadowOffsetX: 3,
            shadowOffsetY: 3,
            shadowBlur: 10,
            shadowColor: components.effectColors.shadow,
            bevelWidth: 2,
            bevelHighlightColor: components.effectColors.highlight,
            bevelShadowColor: components.effectColors.shadow,
            hoverInnerGlowWidth: 20,
            hoverInnerGlowColor: components.effectColors.glow,
            hoverOuterGlowWidth: 20,
            hoverOuterGlowColor: components.effectColors.glow
        };
        components.$targetDivId = jQuery('#' + components.settings.data_rid);
        components.$targetCanvasId = jQuery('#' + components.settings.data_sid);
        components.$targetDownloadId = jQuery('#' + components.settings.data_did);
        components.data_addline = components.settings.data_boolean.split(',')[0];		// Ajouter une ligne
        components.data_orderline = components.settings.data_boolean.split(',')[1];		// Changer l'ordre de la série line
        components.data_showyaxis2 = components.settings.data_boolean.split(',')[2];	// Ajouter l'axe y de droite
        components.data_y2scale = components.settings.data_boolean.split(',')[3];		// Même échelle que l'axe Y gauche
        components.data_showlegend = components.settings.data_boolean.split(',')[4];	// Afficher la légende
        components.data_showgridxaxis = components.settings.data_boolean.split(',')[5];	// Afficher la grille de l'axe X
        components.data_showgridyaxis = components.settings.data_boolean.split(',')[6];	// Afficher la grille de l'axe Y
        components.data_showgridyaxis2 = components.settings.data_boolean.split(',')[7];// Afficher la grille de l'axe Y de droite
        components.data_showvalues = components.settings.data_boolean.split(',')[8];	// Afficher les valeurs
        components.data_posvalue = components.settings.data_boolean.split(',')[9];		// Afficher les valeurs dedans, dehors
        components.data_percentvalue = components.settings.data_boolean.split(',')[10];	// Afficher les valeurs en pourcentage
        components.data_stacked = components.settings.data_boolean.split(',')[11];		// Les lignes ou les barres sont empilées
        components.data_stepped = components.settings.data_boolean.split(',')[12];		// Les lignes sont empilées
        components.data_yforced = components.settings.data_boolean.split(',')[13];	    // Forcer l'axe X à 100%
        components.data_transparence = components.settings.data_boolean.split(',')[14];	// Transparence de la série
        components.data_randomcolor = components.settings.data_boolean.split(',')[15];	// Couleurs aléatoires
        components.data_palettecolor = components.settings.data_boolean.split(',')[16];	// Palette de couleurs
        components.globalFontSize = parseInt(components.settings.data_boolean.split(',')[17]); // Défaut fontSize
        components.data_ysuffixe = components.settings.data_boolean.split(',')[18];     // Suffixe de l'axe Y
        components.data_y2suffixe = components.settings.data_boolean.split(',')[19];    // Suffixe de l'axe Y2
        components.windowWidthMob = 640;                                                // Responsive
        components.globalColor = [];                                                    // Palette de couleurs
        components.globalTransColor = [];                                               // Palette de couleurs transparentes
        components.xAxeTitle = components.settings.data_type === 'horizontalBar' ? components.settings.y_title : components.settings.x_title;
        components.yAxeTitle = components.settings.data_type === 'horizontalBar' ? components.settings.x_title : components.settings.y_title;
        components.globalTextColor = components.settings.color_legend !== '' ? components.settings.color_legend : '#666666';
        components.globalGridColor = components.settings.color_grid !== '' ? components.settings.color_grid : 'rgba(0, 0, 0, 0.1)';
        components.marginBelowLegends = {
            beforeLayout: function (chart, options) {
                chart.legend.afterFit = function () {
                    let marginLegend = jQuery(window).width() <= components.windowWidthMob || components.data_posvalue === '0' ? 0 : 15;
                    if (jQuery.inArray(chart.config.type, ['polarArea', 'horizontalBar']) !== -1) { marginLegend = 0; }
                    chart.legend.height += marginLegend;
                };
            }
        },
            components.chartOptions.type = components.settings.data_type; // Le type du graph

        return components;
    }

    onInit() {
        super.onInit();

        // Erreur settings
        if (Object.keys(this.elements.settings).length === 0) {
            return;
        }

        // Manque des données 
        if (!this.elements.settings.x_label || !this.elements.settings.y_data || !this.elements.settings.data_labels) {
            this.elements.$targetClassWrapper.append("<h4 style='text-align:center;'>Some data are empty</h4>");
            return;
        }

        const title = jQuery.trim(this.elements.settings.data_title);
        this.elements.$targetDownloadId.attr('download', title + '.png');

        // Appel des functions de construction des datasets et des options
        this.setBasicOptions();

        // Bar Chart
        if (jQuery.inArray(this.elements.settings.data_type, ['bar', 'horizontalBar']) !== -1) { this.setChartBar(); }

        // Line Chart
        if (this.elements.settings.data_type === 'line') { this.setChartLine(); }

        // Pie Doughnut Chart
        if (jQuery.inArray(this.elements.settings.data_type, ['pie', 'doughnut']) !== -1) { this.setChartPie(); }

        // Polar Chart
        if (this.elements.settings.data_type === 'polarArea') { this.setChartpolar(); }

        // Radar Chart
        if (this.elements.settings.data_type === 'radar') { this.setChartRadar(); }

        // Ajout d'un Line dans un Bar Chart
        if (this.elements.data_addline === '1' && this.elements.settings.y2_data.split(',').length > 1) { this.setChartBarLine(); }

        // Plugin datalabels
        if (this.elements.data_showvalues === '1') { this.setDatalabels(); }

        /** -------------------- Création du chart ---------------------- */

        const ctx = this.elements.$targetCanvasId[0].getContext('2d');
        this.elements.chartInstance = new Chart(ctx, {
            plugins: [ChartDataLabels, this.elements.marginBelowLegends],
            type: this.elements.chartOptions.type,
            data: this.elements.chartOptions.data,
            options: this.elements.chartOptions.options
        });

        // Formatter l'échelle de l'axe de droite
        if (this.elements.data_addline === '1' && this.elements.data_showyaxis2 === '1' && this.elements.data_y2scale === '1') { this.setRightAxisTicks(); }

        //if(data_addline === '1' && data_showyaxis2 === '1' && data_y2scale === '1') { scaleDataAxesToUnifyZeroes(chartInstance) }
        // Chargement des charts d'un mobile
        this.eacResizeChart.bind(this);
    }

    bindEvents() {
        this.elements.$targetDownloadId.on('click', this.saveDataChartAsImage.bind(this));
        this.elements.$targetChartSwap.on('click', this.toggleXlabelsWithDatalabels.bind(this));
    }

    /** -------------------- Modifie les options de base ---------------------- */
    setBasicOptions() {

        // Supprime l'enregistrement du plugins 'datalabels' par défaut
        Chart.plugins.unregister(ChartDataLabels);

        // Supprime l'enregistrement du plugin 'style' qui ralentit (slow down) FF sur les mobiles
        const agent = navigator.userAgent.toLowerCase().indexOf('firefox');
        //if((agent.indexOf('firefox') + agent.indexOf('android')) >= 0) { chartOptions.options.plugins.style = false; }

        if (is_mobile() && agent !== -1) {
            this.elements.chartOptions.options.plugins.style = false;
        }

        // Défauts
        Chart.defaults.global.defaultFontSize = this.elements.globalFontSize;

        //if(typeof InstallTrigger !== 'undefined') { chartOptions.options.plugins.style = false; }

        // Le titre de l'axe X  ---------------------
        if (this.elements.xAxeTitle !== '') {
            jQuery.extend(this.elements.chartOptions.options.scales.xAxes[0].scaleLabel, {
                display: true,
                labelString: this.elements.xAxeTitle,
                fontColor: this.elements.globalTextColor
            });
        }

        // Configure le quadrillage X
        if (this.elements.data_showgridxaxis === '1') {
            jQuery.extend(this.elements.chartOptions.options.scales.xAxes[0], { gridLines: { display: true, color: this.elements.globalGridColor } });
        }

        // Couleur des labels X
        jQuery.extend(this.elements.chartOptions.options.scales.xAxes[0].ticks, { fontColor: this.elements.globalTextColor });

        // Le titre de l'axe Y ---------------------
        if (this.elements.yAxeTitle !== '') {
            jQuery.extend(this.elements.chartOptions.options.scales.yAxes[0].scaleLabel, { display: true, labelString: this.elements.yAxeTitle, fontColor: this.elements.globalTextColor });
        }

        // Configure le quadrillage Y
        if (this.elements.data_showgridyaxis === '1') {
            jQuery.extend(this.elements.chartOptions.options.scales.yAxes[0], { gridLines: { display: true, color: this.elements.globalGridColor } });
        }

        // Couleur des labels Y
        jQuery.extend(this.elements.chartOptions.options.scales.yAxes[0].ticks, { fontColor: this.elements.globalTextColor });

        // Affiche le titre du chart ---------------------
        if (this.elements.settings.data_title.length > 0) {
            jQuery.extend(this.elements.chartOptions.options, { title: { display: true, text: this.elements.settings.data_title, padding: 2, fontColor: this.elements.globalTextColor } });
        }

        // Affiche la légende du chart
        if (this.elements.data_showlegend === '1') {
            jQuery.extend(this.elements.chartOptions.options, { legend: { display: true, labels: { boxWidth: 9, padding: 5, usePointStyle: true, fontColor: this.elements.globalTextColor } } });
            //filter: function(item, chart) { return new Number(item.text) < 1.55; } 
        }

        // Affecte la palette des couleurs et les couleurs transparentes
        const nbseries = this.elements.settings.y_data.split(/[;,]+/).length;
        const helpercolor = Chart.helpers.color;

        /** Ajout et traitement de la palette de couleurs globales enregistrées dans Elementor */
        if (this.elements.data_palettecolor === '1') { // Couleurs globales
            const paletteColor = this.elements.settings.data_color.split(',');
            for (let i = 0; i < nbseries; i++) {
                this.elements.globalColor.push(paletteColor[i % Object.keys(paletteColor).length]);
                this.elements.globalTransColor.push(helpercolor(paletteColor[i % Object.keys(paletteColor).length]).alpha(this.elements.data_transparence).rgbString());
            }
        } else if (this.elements.data_randomcolor === '1') { // Couleurs aléatoires
            const that = this;
            this.elements.globalColor = randomColor({ count: nbseries, hue: 'random', luminosity: 'bright', format: 'rgba', alpha: 1 }); // luminosity: 'light' 'bright' 'dark'
            this.elements.globalTransColor = this.elements.globalColor.map(function (x) { return x.replace(/[\d\.]+\)$/g, that.elements.data_transparence + ')'); }); // Mêmes couleurs avec transparence
        } else { // Palette par défaut des couleurs
            for (let j = 0; j < nbseries; j++) {
                this.elements.globalColor.push(this.elements.chartColors[j % Object.keys(this.elements.chartColors).length]);
                this.elements.globalTransColor.push(helpercolor(this.elements.chartColors[j % Object.keys(this.elements.chartColors).length]).alpha(this.elements.data_transparence).rgbString());
            }
        }
    }

    /** -------------------- Chart Bar ----------------------
     * Boucle sur les légendes des séries
     * Index = 0, on écrase le datasets par défaut
     * [val1,val2,val3;val4,val5,val6;...]
     */
    setChartBar() {
        const that = this;

        // Autant de legends que de tableaux de données
        if (this.elements.settings.x_label.split(',').length === this.elements.settings.y_data.split(';').length) {

            // Montre l'icone de swap des données s'il n'y a pas de ligne ajoutée
            if (this.elements.data_addline === '0') {
                this.elements.$targetChartSwap.css('display', 'inline-block');
            }

            jQuery.each(this.elements.settings.x_label.split(','), function (index, valeur) {
                that.elements.chartOptions.data.datasets[index] = {
                    label: valeur,
                    data: that.elements.settings.y_data.split(';')[index].split(','),
                    backgroundColor: that.elements.globalTransColor[index],
                    // Affecte au dataset le même ID que l'axe gauche
                    yAxisID: that.elements.chartOptions.data.datasets.yLeftAxis,
                };
                // Applique le style
                jQuery.extend(that.elements.chartOptions.data.datasets[index], that.elements.barStyle);
            });

            // Le type de chart
            jQuery.extend(this.elements.chartOptions, { type: this.elements.settings.data_type });

            // Les labels de l'axe des abscisses X
            jQuery.extend(this.elements.chartOptions.data, { labels: this.elements.settings.data_labels.split(',') });

            // Étend les propriétés de tooltips
            jQuery.extend(this.elements.chartOptions.options.tooltips, { bevelWidth: 2, bevelHighlightColor: this.elements.effectColors.highlight, bevelShadowColor: this.elements.effectColors.shadow });

            // Ajout du suffixe
            if (this.elements.data_ysuffixe !== '0') {
                jQuery.extend(this.elements.chartOptions.options.scales.yAxes[0].ticks, { callback: function (value, index, values) { return value + that.elements.data_ysuffixe; } });
            }

            // Les barres sont empilées
            if (this.elements.data_stacked === '1') {
                // Inverse l'ordre tooltips
                jQuery.extend(this.elements.chartOptions.options.tooltips, { itemSort: function (a, b) { return b.datasetIndex - a.datasetIndex; } });
                jQuery.extend(this.elements.chartOptions.options.scales.xAxes[0], { stacked: true });
                jQuery.extend(this.elements.chartOptions.options.scales.yAxes[0], { stacked: true });
            }
        }

        // Forcer l'axe des Y à 100%
        if (this.elements.data_yforced === '1') {
            jQuery.extend(this.elements.chartOptions.options.scales.yAxes[0], { ticks: { suggestedMax: 100 } });
        }
    }

    /** -------------------- Chart Line ----------------------
     * Boucle sur les valeurs des séries
     * Index = 0, on écrase le datasets par défaut
     */
    setChartLine() {
        const that = this;
        // Autant de tableaux de données que de legendes
        if (this.elements.settings.y_data.split(';').length === this.elements.settings.x_label.split(',').length) {

            // Montre l'icone de swap des données
            this.elements.$targetChartSwap.css('display', 'inline-block');

            jQuery.each(this.elements.settings.x_label.split(','), function (index, valeur) {
                that.elements.chartOptions.data.datasets[index] = {
                    label: valeur,
                    data: that.elements.settings.y_data.split(';')[index].split(','),
                    order: 0,
                    type: 'line',
                    lineTension: 0.2,
                    fill: index === 0 ? 'origin' : '-1',
                    borderColor: that.elements.globalColor[index],
                    backgroundColor: that.elements.globalTransColor[index],
                    yAxisID: that.elements.yLeftAxis,									// Affecte au dataset le même ID que l'axe gauche
                    steppedLine: that.elements.data_stepped === '1' ? true : false,
                };
                jQuery.extend(that.elements.chartOptions.data.datasets[index], that.elements.lineRadarStyle);
            });

            // Le type de chart
            jQuery.extend(this.elements.chartOptions, { type: this.elements.settings.data_type });

            // Les labels de l'axe des abscisses X
            jQuery.extend(this.elements.chartOptions.data, { labels: this.elements.settings.data_labels.split(',') });

            // Ajout du suffixe
            if (this.elements.data_ysuffixe !== '0') {
                jQuery.extend(this.elements.chartOptions.options.scales.yAxes[0].ticks, { callback: function (value, index, values) { return value + that.elements.data_ysuffixe; } });
            }

            // Les lignes sont empilées
            if (this.elements.data_stacked === '1') {
                // Inverse l'ordre tooltips
                jQuery.extend(this.elements.chartOptions.options.tooltips, { itemSort: function (a, b) { return b.datasetIndex - a.datasetIndex; } });
                jQuery.extend(this.elements.chartOptions.options.scales.yAxes[0], { stacked: true });
            }
        }
    }

    /** -------------------- Chart Pie et Doughnut --------------------
     * Boucle sur les légendes des séries
     * [val1,val2,val3;val4,val5,val6;...]
     */
    setChartPie() {
        const that = this;
        // Autant de legends que de tableaux de données
        if (this.elements.settings.x_label.split(',').length === this.elements.settings.y_data.split(';').length) {

            jQuery.each(this.elements.settings.x_label.split(','), function (index, valeur) {
                const data = that.elements.settings.y_data.split(';')[index].split(',');
                // Somme des datas stockées dans le datasets
                const sumpie = data.reduce(function (a, b) { return parseFloat(a) + parseFloat(b); }, 0);

                // Ajoute le datasets
                that.elements.chartOptions.data.datasets[index] = {
                    label: valeur,
                    data: data,
                    sumvalue: sumpie,
                    backgroundColor: that.elements.globalTransColor,
                };
                // Applique le style
                jQuery.extend(that.elements.chartOptions.data.datasets[index], that.elements.piePolarStyle);
            });

            // Le type de chart
            jQuery.extend(this.elements.chartOptions, { type: this.elements.settings.data_type });

            // Les labels de l'axe des abscisses X
            jQuery.extend(this.elements.chartOptions.data, { labels: this.elements.settings.data_labels.split(',') });

            // Pie vs Doughnut
            jQuery.extend(this.elements.chartOptions.options, { cutoutPercentage: this.elements.settings.data_type === 'pie' ? 0 : 40 });

            // N'affiche que la valeur du data pointer par la souris
            if (this.elements.settings.x_label.split(',').length > 1) {
                jQuery.extend(this.elements.chartOptions.options.tooltips, { mode: 'nearest' });
            }

            // Plus de padding pour les étiquettes de valeur externes
            if (this.elements.data_posvalue === '1') {
                jQuery.extend(this.elements.chartOptions.options.layout, { padding: { left: 0, right: 0, top: 5, bottom: 30 } });
            }

            // Cache les axes X et Y
            jQuery.extend(this.elements.chartOptions.options.scales.xAxes[0], { display: false });
            jQuery.extend(this.elements.chartOptions.options.scales.yAxes[0], { display: false });
        }
    }

    /** -------------------- Chart PolarArea ----------------------
     * Une seule série de données
     */
    setChartpolar() {
        const that = this;
        // Autant de legends que de tableaux de données
        if (this.elements.settings.x_label.split(',').length === this.elements.settings.y_data.split(';').length) {

            jQuery.each(this.elements.settings.x_label.split(','), function (index, valeur) {
                const data = that.elements.settings.y_data.split(';')[index].split(',');
                // Somme des datas stockées dans le datasets
                const sumpie = data.reduce(function (a, b) { return parseFloat(a) + parseFloat(b); }, 0);

                // Ajoute le datasets
                that.elements.chartOptions.data.datasets[index] = {
                    label: valeur,
                    data: data,
                    sumvalue: sumpie,
                    backgroundColor: that.elements.globalTransColor,
                };
                // Applique le style
                jQuery.extend(that.elements.chartOptions.data.datasets[index], that.elements.piePolarStyle);
            });

            // Le type de chart
            jQuery.extend(this.elements.chartOptions, { type: this.elements.settings.data_type });

            // Les labels de l'axe des abscisses X
            jQuery.extend(this.elements.chartOptions.data, { labels: this.elements.settings.data_labels.split(',') });

            // Somme des datas stockées dans le datasets
            const arrpa = this.elements.chartOptions.data.datasets[0].data.reduce(function (a, b) { return parseFloat(a) + parseFloat(b); }, 0);
            jQuery.extend(this.elements.chartOptions.data.datasets[0], { sumvalue: arrpa, });

            // Options
            jQuery.extend(this.elements.chartOptions.options, {
                layout: { padding: { left: 0, right: 0, top: 5, bottom: 5 } },
                scale: {
                    display: true,
                    gridLines: { color: this.elements.globalGridColor },
                    ticks: { beginAtZero: true },
                    pointLabels: { fontColor: this.elements.globalTextColor }		// fontSize = eacResizeChart
                }
            });

            // Cache les axes X et Y
            jQuery.extend(this.elements.chartOptions.options.scales.xAxes[0], { display: false });
            jQuery.extend(this.elements.chartOptions.options.scales.yAxes[0], { display: false });
        }
    }

    /** -------------------- Chart Radar ----------------------
     * Boucle sur les légendes des séries
     * Index = 0, on écrase le datasets par défaut
     * [val1,val2,val3;val4,val5,val6;...]
     */
    setChartRadar() {
        const that = this;
        // Autant de legends que de tableaux de données
        if (this.elements.settings.x_label.split(',').length === this.elements.settings.y_data.split(';').length) {
            jQuery.each(this.elements.settings.x_label.split(','), function (index, valeur) {
                // Ajoute le datasets
                that.elements.chartOptions.data.datasets[index] = {
                    label: valeur,
                    data: that.elements.settings.y_data.split(';')[index].split(','),
                    borderColor: that.elements.globalColor[index],
                    backgroundColor: that.elements.globalTransColor[index],
                    //lineTension: 0.2,
                };
                // Applique le style
                jQuery.extend(that.elements.chartOptions.data.datasets[index], that.elements.lineRadarStyle);
            });

            // Le type de chart
            jQuery.extend(this.elements.chartOptions, { type: this.elements.settings.data_type });

            // Les labels de l'axe des abscisses X
            jQuery.extend(this.elements.chartOptions.data, { labels: this.elements.settings.data_labels.split(',') });

            // Options
            jQuery.extend(this.elements.chartOptions.options, {
                scale: {
                    display: true,
                    gridLines: { color: this.elements.globalGridColor },
                    angleLines: { color: this.elements.globalGridColor },
                    ticks: { beginAtZero: true },
                    pointLabels: { fontColor: this.elements.globalTextColor }
                }	// fontSize = eacResizeChart
            });

            // Cache les axes X et Y
            jQuery.extend(this.elements.chartOptions.options.scales.xAxes[0], { display: false });
            jQuery.extend(this.elements.chartOptions.options.scales.yAxes[0], { display: false });
        }
    }

    /** -------------------- Chart Bar + Line ----------------------
     * Ajout d'une line à un chart Bar
     */
    setChartBarLine() {
        const that = this;
        // Nombre de datasets déjà enregistrés
        const nbbar = this.elements.chartOptions.data.datasets.length;

        // Ajout d'un datasets supplémentaire aux datasets des barres
        this.elements.chartOptions.data.datasets[nbbar] = {
            label: this.elements.settings.y2_label,
            data: this.elements.settings.y2_data.split(','),
            order: this.elements.data_orderline === '1' ? -1 : this.elements.data_orderline,
            type: 'line',
            fill: false,
            lineTension: 0,
            borderColor: this.elements.globalColor[nbbar],
            // Affecte au dataset de la ligne le même ID que l'axe gauche
            yAxisID: this.elements.yLeftAxis,
        };
        // Applique le style
        jQuery.extend(this.elements.chartOptions.data.datasets[nbbar], this.elements.lineRadarStyle);

        // Ajout de l'axe droit
        if (this.elements.data_showyaxis2 === '1') {
            // Affecte les options de l'axe de droite
            jQuery.extend(this.elements.chartOptions.options.scales.yAxes[1], {
                display: true,
                ticks: { display: true, beginAtZero: true, fontColor: this.elements.globalTextColor },
                gridLines: this.elements.data_showgridyaxis2 === '1' ? { display: true, color: this.elements.globalGridColor, } : { display: false },
                scaleLabel: this.elements.settings.y2_title !== '' ? { display: true, labelString: this.elements.settings.y2_title, fontColor: this.elements.globalTextColor } : { display: false, },
            });

            // Ajout du suffixe
            if (this.elements.data_y2suffixe !== '0') {
                jQuery.extend(this.elements.chartOptions.options.scales.yAxes[1].ticks, { callback: function (value, index, values) { return value + that.elements.data_y2suffixe; } });
            }

            // Affecte au dataset de la ligne le même ID que l'axe droit
            jQuery.extend(this.elements.chartOptions.data.datasets[nbbar], { yAxisID: this.elements.yRightAxis });
        }
    }

    /** -------------------- Affiche et formatte les valeurs. Plugin datalabels ---------------------- */
    setDatalabels() {
        const that = this;
        const colordatalabels = Chart.helpers.color;

        jQuery.extend(this.elements.chartOptions.options.plugins.datalabels, {
            // Ce n'est pas un mobile, on affiche les valeurs. display = true ou false
            display: function (context) {
                return context.chart.width > that.elements.windowWidthMob;
            },
            backgroundColor: function (context) {
                // borderColor pour le type 'line'
                const ctxcolor = context.dataset.backgroundColor ? context.dataset.backgroundColor : context.dataset.borderColor;
                // Supprime la transparence
                const bgalpha = Array.isArray(ctxcolor) ? colordatalabels(ctxcolor[context.dataIndex]).alpha('1').rgbString() : colordatalabels(ctxcolor).alpha('1').rgbString();
                return bgalpha;
            },
            borderColor: 'white',
            borderRadius: 8,
            borderWidth: 2,
            color: 'white',
            font: { size: 12, weight: 'bold' },
            padding: 3,
            anchor: function (context) {
                const dataset = context.dataset;
                const value = dataset.data[context.dataIndex];
                return that.elements.data_posvalue === '0' ? 'null' : value < 0 ? 'start' : 'end';
            },
            align: function (context) {
                const dataset = context.dataset;
                const value = dataset.data[context.dataIndex];
                return that.elements.data_posvalue === '0' ? 'null' : value < 0 ? 'start' : 'end';
            },
            formatter: function (value, context) {
                let percentage = value;
                const sum = context.dataset.sumvalue ? context.dataset.sumvalue : value;
                if (that.elements.data_percentvalue === '1' && sum !== value) {
                    percentage = (value * 100 / sum).toFixed(1).replace(/(\.0)$/g, '') + '% ';
                }
                return percentage;
            },
        });
    }

    /** -------------------- Modifie l'échelle des axes y si ajout d'une ligne dans un bar chart ---------------------- 
     * Après création du Chart
     * On calcule et affiche l'échelle des valeurs des deux axes Y
     */
    setRightAxisTicks() {
        const minlefty = Math.ceil(this.elements.chartInstance.scales[this.elements.yLeftAxis]._startValue / 10) * 10;
        const minrighty = Math.ceil(this.elements.chartInstance.scales[this.elements.yRightAxis]._startValue / 10) * 10;
        //if(minrighty > minlefty) { minrighty = minlefty; }
        //if(minrighty < minlefty) { minlefty = minrighty; }

        const maxlefty = Math.ceil(this.elements.chartInstance.scales[this.elements.yLeftAxis]._endValue / 10) * 10;
        const maxrighty = Math.ceil(this.elements.chartInstance.scales[this.elements.yRightAxis]._endValue / 10) * 10;

        const stepLeft = (Math.abs(minlefty) + maxlefty) / 10;
        const stepRight = (Math.abs(minrighty) + maxrighty) / 10;

        this.elements.chartInstance.scales[this.elements.yLeftAxis].options.ticks.min = minlefty;
        this.elements.chartInstance.scales[this.elements.yLeftAxis].options.ticks.max = maxlefty;
        this.elements.chartInstance.scales[this.elements.yLeftAxis].options.ticks.stepSize = stepLeft;

        this.elements.chartInstance.scales[this.elements.yRightAxis].options.ticks.min = minrighty;
        this.elements.chartInstance.scales[this.elements.yRightAxis].options.ticks.max = maxrighty;
        this.elements.chartInstance.scales[this.elements.yRightAxis].options.ticks.stepSize = stepRight;

        // Mise à jour de la chart
        this.elements.chartInstance.update();
    }

    /** -------------------- 2 - Modifie l'échelle des axes y si ajout d'une ligne dans un bar chart ---------------------- */
    scaleDataAxesToUnifyZeroes(chart) {
        const options = chart.options;
        const minlefty = chart.scales[this.elements.yLeftAxis]._startValue;
        const maxlefty = Math.ceil(chart.scales[this.elements.yLeftAxis]._endValue / 10) * 10;
        const minrighty = chart.scales[this.elements.yRightAxis]._startValue;
        const maxrighty = Math.ceil(chart.scales[this.elements.yRightAxis]._endValue / 10) * 10;
        const decimalPoints = 0;
        const m = Math.pow(10, decimalPoints);

        minlefty = parseFloat(minlefty);
        minlefty = (minlefty >= 0 ? Math.ceil(minlefty * m) : Math.floor(minlefty * m)) / m;
        minrighty = parseFloat(minrighty);
        minrighty = (minrighty >= 0 ? Math.ceil(minrighty * m) : Math.floor(minrighty * m)) / m;

        options.scales.yAxes[0].min_value = minlefty;
        options.scales.yAxes[0].max_value = maxlefty;
        options.scales.yAxes[1].min_value = minrighty;
        options.scales.yAxes[1].max_value = maxrighty;

        const axes = options.scales.yAxes;

        // Which gives the overall range of each axis
        axes.forEach(function (axis) {
            axis.range = axis.max_value - axis.min_value;
            // Express the min / max values as a fraction of the overall range
            axis.min_ratio = axis.min_value / axis.range;
            axis.max_ratio = axis.max_value / axis.range;
        });

        // Find the largest of these ratios
        //const sumpie = data.reduce(function(a, b) { return parseFloat(a) + parseFloat(b); }, 0);
        const largest = axes.reduce(function (a, b) {
            const min_ratio = Math.min(a.min_ratio, b.min_ratio);
            const max_ratio = Math.max(a.max_ratio, b.max_ratio);
        });
        /*let largest = axes.reduce(function (a, b) ({
            min_ratio: Math.min(a.min_ratio, b.min_ratio),
            max_ratio: Math.max(a.max_ratio, b.max_ratio)
        }));*/

        // Then scale each axis accordingly
        axes.forEach(function (axis) {
            axis.ticks = axis.ticks || {};
            axis.ticks.min = largest.min_ratio * axis.range;
            axis.ticks.max = largest.max_ratio * axis.range;
        });

        // Mise à jour de la chart
        chart.update();
    }

    /** -------------------- Responsive ---------------------- */
    eacResizeChart() {
        const chart = this.elements.chartInstance;
        if (chart !== null && chart.width <= this.elements.windowWidthMob) {

            Chart.defaults.global.defaultFontSize = 9;

            // Radar & polarArea
            if (jQuery.inArray(this.elements.settings.data_type, ['radar', 'polarArea']) !== -1) {
                jQuery.extend(chart.options.scale.pointLabels, { fontSize: 9 });
            }

            jQuery.extend(chart.options.scales.xAxes[0].scaleLabel, { display: false });
            jQuery.extend(chart.options.scales.yAxes[0].scaleLabel, { display: false });
            if (chart.scales[this.elements.yRightAxis]) {
                jQuery.extend(chart.scales[this.elements.yRightAxis].options.scaleLabel, { display: false });
            }

            // Chart.defaults.global.layout
            jQuery.extend(chart.options.layout, { padding: { left: 0, right: 0, top: 0, bottom: 5 } });

            // Boîte de la légende
            jQuery.extend(chart.options.legend.labels, { boxWidth: 6 });

        } else {

            // Radar & polarArea
            if (jQuery.inArray(this.elements.settings.data_type, ['radar', 'polarArea']) !== -1) {
                jQuery.extend(chart.options.scale.pointLabels, { fontSize: this.elements.globalFontSize });
            }

            if (this.elements.xAxeTitle !== '') {
                jQuery.extend(chart.options.scales.xAxes[0].scaleLabel, { display: true });
            }
            if (this.elements.yAxeTitle !== '') {
                jQuery.extend(chart.options.scales.yAxes[0].scaleLabel, { display: true });
            }
            if (chart.scales[this.elements.yRightAxis] && this.elements.settings.y2_title !== '') {
                jQuery.extend(chart.scales[this.elements.yRightAxis].options.scaleLabel, { display: true });
            }

            // Boîte de la légende
            jQuery.extend(chart.options.legend.labels, { boxWidth: 9 });
        }
        // Mise à jour de la chart
        chart.update();
    }

    saveDataChartAsImage(evt) {
        const canvas = this.elements.$targetCanvasId[0];
        const context = canvas.getContext('2d');
        context.save();
        context.globalCompositeOperation = 'destination-over';
        context.fillStyle = this.elements.$targetDivId.css('background-color');
        context.fillRect(0, 0, canvas.width, canvas.height);

        const savecanvas = canvas.toDataURL('image/png', 1.0);
        evt.currentTarget.href = savecanvas;
        context.restore();
    }

    toggleXlabelsWithDatalabels(evt) {
        const that = this;
        const inverse = this.elements.chartInstance.options.inverted; // Les données ne sont pas inversées ?
        const xdata = inverse === false ? this.elements.settings.data_labels.split(',') : this.elements.settings.x_label.split(',');
        const legends = inverse === false ? this.elements.settings.x_label.split(',') : this.elements.settings.data_labels.split(',');
        const ydata = this.elements.settings.y_data.split(';');

        // Supprime la légende et anciens datasets
        let total = this.elements.chartInstance.data.datasets.length;
        this.elements.chartInstance.data.labels = [];
        while (total >= 0) {
            this.elements.chartInstance.data.datasets.pop();
            total--;
        }

        // Nouvelle légende
        this.elements.chartInstance.data.labels = legends;

        // calcul des nouvelles valeurs datasets
        jQuery.each(xdata, function (index, valeur) {
            let data = [];
            let filline;
            if (that.elements.chartInstance.config.type === 'line') { filline = index === 0 ? 'origin' : '-1'; }
            else { filline = true; }

            if (inverse === false) {
                jQuery.each(ydata, function (yindex, yvaleur) {
                    data.push(yvaleur.split(',')[index]);
                });
            } else {
                data = ydata[index].split(',');
            }

            that.elements.chartInstance.data.datasets[index] = {
                label: valeur,
                data: data,
                backgroundColor: that.elements.globalTransColor[index],
                borderColor: that.elements.globalColor[index],
                yAxisID: that.elements.yLeftAxis,
                steppedLine: that.elements.data_stepped === '1' ? true : false,
                fill: filline,
            };
            // Applique le style propre au type de chart
            if (jQuery.inArray(that.elements.chartInstance.config.type, ['bar', 'horizontalBar']) !== -1) { jQuery.extend(that.elements.chartInstance.data.datasets[index], that.elements.barStyle); }
            else { jQuery.extend(that.elements.chartInstance.data.datasets[index], that.elements.lineRadarStyle); }
        });

        // Inversion legend <--> séries
        jQuery.extend(this.elements.chartInstance.options, { inverted: !inverse });

        //Mise à jour du chart
        this.elements.chartInstance.update();
    }
}

/**
 * Description: La class est créer lorsque le composant 'eac-addon-chart' est chargé dans la page
 *
 * @param elements (Ex: $scope)
 * @since 2.1.0
 */
jQuery(window).on('elementor/frontend/init', () => {
    elementorFrontend.elementsHandler.attachHandler('eac-addon-chart', widgetChart);
});
