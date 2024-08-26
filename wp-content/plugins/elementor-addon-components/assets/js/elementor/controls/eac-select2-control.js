
/**
 * Description: Creation d'un nouveau control pour étendre le select Elementor avec completion
 * Simple et multiple sélection
 *
 * @since 1.8.9
 */

jQuery(window).on('elementor/init', function () {
	'use strict';

	var ControlSelectChooser = elementor.modules.controls.Select2.extend({

		onReady: function () {
			this.model_cid = 'elementor-control-default-' + this.model.cid;
			this.$control_select = this.$el.find('.eac-select2');
			this.$control_wrapper = this.$el.find('.eac-select2_control-field');
			this.multiple = this.model.get('multiple');
			this.placeholder = this.model.get('placeholder');
			this.object_type = this.model.get('object_type');	// post, page, product, CPT... ou all
			this.query_type = this.model.get('query_type');		// post, taxonomy ou term
			this.query_taxo = this.model.get('query_taxo');		// category, post_tag, product_cat, product_tag, pa_xxxxx (attribute: pa_tissu)
			this.select2Options = this.model.get('select2Options');
			if (this.select2Options && this.select2Options.length !== 0) {
				this.loadSelect2Options();
			} else {
				this.waitForAutocomplete();
			}
			if (this.multiple) {
				this.addbuttonPlus();
				this.$control_select.on('select2:select select2:unselect', () => this.addbuttonPlus());
			}
		},

		/** Ajout de l'icone + dans le field input */
		addbuttonPlus: function () {
			const $wrapper = this.$control_wrapper.find('.select2-selection__rendered .select2-search.select2-search--inline');
			setTimeout(() => {
				jQuery('<li class="select2-selection__choice select2-selection__e-plus-button">+</li>').insertBefore($wrapper);
			}, 50);
		},

		/** L'option 'select2Options' est utilisée en fonctionnement normal sous forme de tableau */
		loadSelect2Options: function () {
			var oldValues = this.getControlValue();
			var dataOptions = [];

			jQuery.each(this.select2Options, function (key, val) {
				dataOptions.push({ id: key, text: val });
			});

			this.$control_select.select2({
				data: dataOptions,
				placeholder: ! this.multiple ? this.placeholder : '',
				dir: elementorCommon.config.isRTL ? 'rtl' : 'ltr',
				delay: 500,
				multiple: this.multiple,
			});

			/** Réaffecte les anciennes valeurs */
			if (oldValues) {
				this.$control_select.select2().val(oldValues).trigger('change');
			}
		},

		waitForAutocomplete: function () {
			var that = this;
			this.$control_select.select2({
				allowClear: true,
				placeholder:  ! this.multiple ? this.placeholder : '',
				dir: elementorCommon.config.isRTL ? 'rtl' : 'ltr',
				delay: 500,
				multiple: this.multiple,
				ajax: {
					type: 'POST',
					dataType: 'json',
					cache: false,
					quietMillis: 500,
					url: eac_autocomplete_search.ajax_url,
					data: function (params) {
						var query = {
							search: params.term || '',
							object_type: that.object_type,
							query_type: that.query_type,
							query_taxo: that.query_taxo,
							action: eac_autocomplete_search.ajax_action,
							nonce: eac_autocomplete_search.ajax_nonce,
						};
						return query
					},
					processResults: function (data) {
						var result = JSON.parse(data['data']);
						return { results: result }
					},
				},
				initSelection: function (element, callback) {
					var elementorSearch = that.getControlValue();
					callback({ id: '', text: '' });

					if (elementorSearch) {
						jQuery.ajax({
							type: 'POST',
							dataType: 'json',
							cache: false,
							quietMillis: 500,
							url: eac_autocomplete_search.ajax_url,
							data: {
								search: elementorSearch,
								object_type: that.object_type,
								query_type: that.query_type,
								query_taxo: that.query_taxo,
								action: eac_autocomplete_search.ajax_action_reload,
								nonce: eac_autocomplete_search.ajax_nonce,
							},
						}).done(function (response) {
							if (response.success) {
								var result = JSON.parse(response['data']);
								var eacSelect2Options = '';
								jQuery.each(result, function (index, data) {
									var key = data.id;
									var value = data.text;
									if (element.find("option[value='" + key + "']").length === 0) {
										eacSelect2Options += '<option selected="selected" value="' + key + '">' + value + '</option>';
									}
								});
								if (eacSelect2Options !== '') {
									element.append(eacSelect2Options).trigger('change');
								}
							}
						});
					}
				},
			});
		},

		onBeforeDestroy() {
			this.$control_select.select2('destroy').off('select2:select select2:unselect');
		},
	});
	elementor.addControlView('eac-select2', ControlSelectChooser);
});