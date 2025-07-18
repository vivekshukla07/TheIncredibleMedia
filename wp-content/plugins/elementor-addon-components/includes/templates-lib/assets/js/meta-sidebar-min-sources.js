(() => {
	"use strict";
	const e = wp.i18n,
		t = wp.plugins,
		l = wp.editPost,
		a = (wp.editor, wp.components),
		n = wp.data;

	var r = (0, n.withSelect)(function (e) {
		return {
			selectedTemplate: e("core/editor").getEditedPostAttribute("meta").eac_theme_builder_template_siteheader
		}
	})(function (t) {
		var l = t.selectedTemplate,
			n = t.onUpdate;

		return React.createElement(a.SelectControl, {
			label: (0, e.__)("Elementor Header Template:", "elemental-theme-builder"),
			value: l,
			onChange: n,
			className: "editor-page-attributes__template",
			options: elementorSiteBuilderData.headerTemplates
		})
	});

	const i = (0, n.withDispatch)(function (e) {
		return {
			onUpdate: function (t) {
				e("core/editor").editPost({
					meta: {
						eac_theme_builder_template_siteheader: t || ""
					}
				})
			}
		}
	})(r);

	var o = (0, n.withSelect)(function (e) {
		return {
			selectedTemplate: e("core/editor").getEditedPostAttribute("meta").eac_theme_builder_template_sitefooter
		}
	})(function (t) {
		var l = t.selectedTemplate,
			n = t.onUpdate;
		return React.createElement(a.SelectControl, {
			label: (0, e.__)("Elementor Footer Template:", "elemental-theme-builder"),
			value: l,
			onChange: n,
			className: "editor-page-attributes__template",
			options: elementorSiteBuilderData.footerTemplates
		})
	});

	const m = (0, n.withDispatch)(function (e) {
		return {
			onUpdate: function (t) {
				e("core/editor").editPost({
					meta: {
						eac_theme_builder_template_sitefooter: t || ""
					}
				})
			}
		}
	})(o);

	(0, t.registerPlugin)("elemental-theme-builder-plugin", {
		icon: "screenoptions",
		render: function () {
			return React.createElement(l.PluginSidebar, {
				name: "elemental-theme-builder-layout-settings",
				title: (0, e.__)("Layout Settings", "elemental-theme-builder")
			}, React.createElement(a.PanelBody, null, React.createElement("p", null, (0, e.__)('"Inherit" will display the template assigned for "Entire Site". "Theme Default" will display the template from the current theme.', "elemental-theme-builder")), React.createElement(i, null), React.createElement(m, null)))
		}
	})

})();