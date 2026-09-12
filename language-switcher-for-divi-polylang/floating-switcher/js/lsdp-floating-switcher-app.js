/**
 * LSEP Floating Switcher
 *
 * Main application component for managing the floating language switcher
 * settings in the admin dashboard.
 *
 * @package    Language_Switcher_For_Elementor_Polylang
 * @subpackage Language_Switcher_For_Elementor_Polylang/floating-switcher/js
 * @since     1.2.4
 */

(function () {
    "use strict";

    // Import WordPress element and i18n utilities
    const { createElement: h, render, Component } = wp.element;
    const { __, sprintf } = wp.i18n;

    /**
     * FloaterApp Component
     *
     * Main React component that handles the entire floating switcher configuration interface.
     */
    class FloaterApp extends Component {
      constructor(props) {
        super(props);

        // Load data from global window object passed from PHP
        const data = window.lsdpFloaterData || {};
        const languages = data.languages || [];
        let config = data.config || this.getDefaultConfig();

        if (config.type === "side-by-side" && languages.length > this.getSideBySideMaxLanguages()) {
          config = { ...config, type: "dropdown" };
        }

        config = this.normalizeLayoutCustomizerConfig(config);

        // Initialize component state
        this.state = {
          config: config,
          languages: languages,
          currentDevice: "desktop",
          isSaving: false,
          hasChanges: false,
          originalConfig: JSON.stringify(config),
          showColorPicker: null,
          showPresetConfirm: null,
        };

        this.presets = this.getPresets();
      }

      getSideBySideMaxLanguages() {
        const data = window.lsdpFloaterData || {};
        return data.sideBySideMaxLanguages || 3;
      }

      normalizeLayoutCustomizerConfig(config) {
        if (!config.layoutCustomizer) {
          return config;
        }

        const layoutCustomizer = { ...config.layoutCustomizer };

        ["desktop", "mobile"].forEach((device) => {
          if (!layoutCustomizer[device]) {
            return;
          }

          const layout = { ...layoutCustomizer[device] };

          if (
            layout.flagIconPosition === "hide" &&
            layout.languageNames === "none"
          ) {
            layout.languageNames = "full";
          }

          layoutCustomizer[device] = layout;
        });

        return {
          ...config,
          layoutCustomizer,
        };
      }

      isSideBySideAllowed() {
        return this.state.languages.length <= this.getSideBySideMaxLanguages();
      }

      getDefaultConfig() {
        return {
          enabled: false,
          type: "dropdown",
          bgColor: "#ffffff",
          bgHoverColor: "#0000000d",
          textColor: "#143852",
          textHoverColor: "#1d2327",
          borderColor: "#1438521a",
          borderWidth: 1,
          borderRadius: [8, 8, 0, 0],
          size: "normal",
          flagShape: "rect",
          flagRadius: 2,
          enableCustomCss: true,
          customCss: "",
          layoutCustomizer: {
            desktop: {
              position: "bottom-right",
              width: "default",
              customWidth: 216,
              padding: "default",
              customPadding: 0,
              flagIconPosition: "before",
              languageNames: "full",
            },
            mobile: {
              position: "bottom-right",
              width: "default",
              customWidth: 216,
              padding: "default",
              customPadding: 0,
              flagIconPosition: "before",
              languageNames: "full",
            },
          },
        };
      }

      getPresets() {
        return [
          {
            name: __("Default", "language-switcher-for-divi-polylang"),
            config: {
              bgColor: "#ffffff",
              bgHoverColor: "#0000000d",
              textColor: "#143852",
              textHoverColor: "#1d2327",
              borderColor: "#1438521a",
            },
            background: "rgb(219, 219, 219)",
          },
          {
            name: __("Dark", "language-switcher-for-divi-polylang"),
            config: {
              bgColor: "#000000",
              bgHoverColor: "#444444",
              textColor: "#ffffff",
              textHoverColor: "#eeeeee",
              borderColor: "transparent",
            },
            background: "rgb(219, 219, 219)",
          },
          {
            name: __("Border", "language-switcher-for-divi-polylang"),
            config: {
              bgColor: "#FFFFFF",
              bgHoverColor: "#000000",
              textColor: "#143852",
              textHoverColor: "#ffffff",
              borderColor: "#143852",
            },
            background: "rgb(219, 219, 219)",
          },
          {
            name: __("Transparent", "language-switcher-for-divi-polylang"),
            config: {
              bgColor: "#FFFFFFB2",
              bgHoverColor: "#0000000D",
              textColor: "#000000",
              textHoverColor: "#000000",
              borderColor: "transparent",
            },
            background:
              "linear-gradient(145.41deg, rgb(34, 113, 177) 20.41%, rgb(211, 180, 218) 96.59%)",
          },
        ];
      }

      updateConfig(updates) {
        this.setState((prevState) => {
          const newConfig = { ...prevState.config, ...updates };
          return {
            config: newConfig,
            hasChanges: JSON.stringify(newConfig) !== prevState.originalConfig,
          };
        });
      }

      updateLayoutConfig(device, updates) {
        this.setState((prevState) => {
          const currentLayout = prevState.config.layoutCustomizer[device];
          const nextLayout = {
            ...currentLayout,
            ...updates,
          };

          if (
            nextLayout.flagIconPosition === "hide" &&
            nextLayout.languageNames === "none"
          ) {
            if (updates.flagIconPosition === "hide") {
              nextLayout.languageNames = "full";
            } else {
              nextLayout.flagIconPosition = "before";
            }
          }

          const newConfig = {
            ...prevState.config,
            layoutCustomizer: {
              ...prevState.config.layoutCustomizer,
              [device]: nextLayout,
            },
          };
          return {
            config: newConfig,
            hasChanges: JSON.stringify(newConfig) !== prevState.originalConfig,
          };
        });
      }

      showPresetConfirmation(preset) {
        this.setState({ showPresetConfirm: preset });
      }

      applyPreset(preset) {
        this.updateConfig(preset.config);
        this.setState({ showPresetConfirm: null });
      }

      cancelPresetConfirmation() {
        this.setState({ showPresetConfirm: null });
      }

      isPresetActive(preset) {
        const { config } = this.state;
        const presetConfig = preset.config;
        return Object.keys(presetConfig).every((key) => {
          return config[key] === presetConfig[key];
        });
      }

      revertChanges() {
        if (!this.state.hasChanges) {
          return;
        }

        const original = JSON.parse(this.state.originalConfig);
        this.setState({
          config: original,
          hasChanges: false,
        });
      }

      async saveSettings() {
        this.setState({ isSaving: true });

        const data = new FormData();
        data.append("action", "lsdp_save_floating_switcher");
        data.append("nonce", window.lsdpFloaterData.nonce);
        data.append("config", JSON.stringify(this.state.config));

        try {
          const response = await fetch(window.lsdpFloaterData.ajaxUrl, {
            method: "POST",
            body: data,
            credentials: "same-origin",
          });

          const result = await response.json();

          if (result.success) {
            this.setState({
              originalConfig: JSON.stringify(this.state.config),
              hasChanges: false,
              isSaving: false,
            });
            this.showNotice(
              "success",
              __("Settings saved successfully!", "language-switcher-for-divi-polylang")
            );
          } else {
            throw new Error(
              result.data || __("Failed to save settings", "language-switcher-for-divi-polylang")
            );
          }
        } catch (error) {
          this.showNotice(
            "error",
            error.message || __("Failed to save settings", "language-switcher-for-divi-polylang")
          );
          this.setState({ isSaving: false });
        }
      }

      showNotice(type, message) {
        const notice = document.createElement("div");
        notice.className = `notice notice-${type} is-dismissible`;
        const paragraph = document.createElement("p");
        paragraph.textContent = message;
        notice.appendChild(paragraph);

        const wrap = document.querySelector(".wrap");
        if (wrap) {
          wrap.insertBefore(notice, wrap.firstChild);
          setTimeout(() => notice.remove(), 3000);
        }
      }

      toggleCollapsible(event) {
        const box = event.currentTarget.closest(".lsdp-settings-box");
        if (box && box.classList.contains("lsdp-collapsible")) {
          box.classList.toggle("open");
        }
      }

      render() {
          return h(
          "div",
          { className: "lsdp-floater-app-container" },
          h(
              "main",
              { className: "lsdp-ls-view" },
              h(
              "div",
              { className: "lsdp-floater-settings__wrapper" },
              this.renderLeftColumn(),
              this.renderRightColumn()
              )
          )
          );
      }

      renderRightColumn() {
        return h(
          "div",
          { className: "lsdp-floater-settings__left" },
          h(
            "div",
            { className: "lsdp-sticky-box" },
            this.renderActionButtons(),
            this.renderPreviewBox(),
            this.renderAutoPolyPromo()
          )
        );
      }

      /**
       * Render an icon button with hover effects
       * @param {Object} config - Button configuration
       * @param {string} config.href - Button URL
       * @param {string} config.title - Button tooltip
       * @param {string} config.color - Button icon color
       * @param {Function} config.icon - Function that returns SVG icon element
       * @param {string} config.ariaLabel - Accessibility label
       */
      renderIconButton({ href, title, color, icon, ariaLabel }) {
        return h(
          "a",
          {
            href: href,
            target: "blank",
            rel: "noopener noreferrer",
            className: "lsdp-icon-button",
            title: title,
            "aria-label": ariaLabel || title,
            style: {
              display: "inline-flex",
              alignItems: "center",
              justifyContent: "center",
              width: "32px",
              height: "32px",
              borderRadius: "4px",
              backgroundColor: "#f0f0f1",
              color: color,
              textDecoration: "none",
              transition: "all 0.2s ease",
              cursor: "pointer",
              border: "1px solid transparent"
            },
            onMouseOver: (e) => {
              e.currentTarget.style.backgroundColor = color;
              e.currentTarget.style.color = "#fff";
              e.currentTarget.style.transform = "translateY(-2px)";
              e.currentTarget.style.boxShadow = "0 2px 8px rgba(0,0,0,0.15)";
            },
            onMouseOut: (e) => {
              e.currentTarget.style.backgroundColor = "#f0f0f1";
              e.currentTarget.style.color = color;
              e.currentTarget.style.transform = "translateY(0)";
              e.currentTarget.style.boxShadow = "none";
            }
          },
          icon()
        );
      }

      renderPreviewBox() {
        const { config, currentDevice, hasChanges } = this.state;

        return h(
          "div",
          { className: "lsdp-settings-box" },
          h(
            "header",
            { className: "lsdp-header" },
            h(
              "span",
              { className: "lsdp-title" },
              __("Switcher Preview", "language-switcher-for-divi-polylang")
            ),
            h(
              "div",
              {
                className: "lsdp-header-buttons",
                style: {
                  display: "flex",
                  gap: "8px",
                  marginLeft: "auto",
                  alignItems: "center"
                }
              },
              this.renderIconButton({
                href: "https://docs.coolplugins.net/doc/add-language-switcher-elementor/?ref=creame&utm_source=lsdp_plugin&utm_medium=inside&utm_campaign=docs&utm_content=floating_switcher",
                title: __("View Documentation", "language-switcher-for-divi-polylang"),
                color: "#2271b1",
                ariaLabel: __("Open documentation in new tab", "language-switcher-for-divi-polylang"),
                icon: () => h(
                  "svg",
                  {
                    width: "16",
                    height: "16",
                    viewBox: "0 0 24 24",
                    fill: "none",
                    stroke: "currentColor",
                    strokeWidth: "2",
                    strokeLinecap: "round",
                    strokeLinejoin: "round"
                  },
                  h("path", { d: "M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" }),
                  h("polyline", { points: "14 2 14 8 20 8" }),
                  h("line", { x1: "16", y1: "13", x2: "8", y2: "13" }),
                  h("line", { x1: "16", y1: "17", x2: "8", y2: "17" }),
                  h("polyline", { points: "10 9 9 9 8 9" })
                )
              }),
              this.renderIconButton({
                href: "https://youtu.be/oRp8vmrU8yM?si=wX4_I4E8TfkQpRIT&t=513s",
                title: __("Watch Video Tutorial", "language-switcher-for-divi-polylang"),
                color: "#ff0000",
                ariaLabel: __("Open video tutorial in new tab", "language-switcher-for-divi-polylang"),
                icon: () => h(
                  "svg",
                  {
                    width: "18",
                    height: "18",
                    viewBox: "0 0 24 24",
                    fill: "currentColor"
                  },
                  h("path", {
                    d: "M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"
                  })
                )
              })
            )
          ),
          h(
            "section",
            { className: "lsdp-body" },

            !config.enabled ? h(
              "div",
              {
                className: "lsdp-preview-disabled-message",
                style: {
                  textAlign: "center",
                  padding: "60px 20px",
                  color: "#646970"
                }
              },
              h(
                "svg",
                {
                  width: "48",
                  height: "48",
                  viewBox: "0 0 24 24",
                  fill: "none",
                  style: { margin: "0 auto 16px", display: "block", opacity: "0.5" }
                },
                h("path", {
                  d: "M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z",
                  fill: "#646970"
                })
              ),
              h(
                "p",
                { style: { margin: "0 0 8px", fontSize: "14px", fontWeight: "500" } },
                __("Switcher is Disabled", "language-switcher-for-divi-polylang")
              ),
              h(
                "p",
                { style: { margin: "0", fontSize: "13px", opacity: "0.8" } },
                __("Enable the floating switcher to see the preview", "language-switcher-for-divi-polylang")
              )
            ) :
            [
              config.enableCustomCss &&
                config.customCss &&
                h("style", null, config.customCss),

              h(
                "div",
                {
                  className: "lsdp-language-switcher-preview__container",
                  style: {
                    "--lsdp-preview-bg": `url(${window.lsdpFloaterData.pluginUrl}assets/images/preview-bg.png)`,
                  },
                },
                h(
                  "div",
                  { className: "lsdp-language-switcher-preview-box" },
                  this.renderSwitcherPreview()
                )
              ),

              h(
                "span",
                {
                  className:
                    "lsdp-language-switcher-preview-text lsdp-description-text",
                },
                __(
                  "Hover over the language switcher to see it in action!",
                  "language-switcher-for-divi-polylang"
                )
              )
            ]
          )
        );
      }

  renderAutoPolyPromo() {
    const html = (window.lsdpFloaterData && window.lsdpFloaterData.autoPolyPromoHtml) || "";
    return h("div", {
      className: "lsdp-autopoly-promo-host",
      dangerouslySetInnerHTML: { __html: html },
    });
  }

      renderSwitcherPreview() {
        const { config, languages, currentDevice } = this.state;
        const layoutConfig = config.layoutCustomizer[currentDevice];

        const styles = this.buildPreviewStyles();

        const positionClass = layoutConfig.position.includes("bottom")
          ? "lsdp-switcher-position-bottom"
          : "lsdp-switcher-position-top";

        const isDropdown = config.type === "dropdown";
        const isSideBySide = config.type === "side-by-side";

        let allLangs = languages.length > 0
          ? languages
          : [
              { code: "en", name: "English", flag: "" },
              { code: "fr", name: "French", flag: "" },
            ];

        const maxLanguages = isSideBySide ? this.getSideBySideMaxLanguages() : 5;
        const sampleLangs = allLangs.slice(0, maxLanguages);

        const current = sampleLangs[0];
        const others = sampleLangs.slice(1);

        return h(
          "div",
          {
            className: `lsdp-language-switcher lsdp-floating-switcher lsdp-ls-${
              isDropdown ? "dropdown" : "inline"
            } ${positionClass}`,
            style: styles,
          },
          h(
            "div",
            { className: "lsdp-language-switcher-inner" },
            isSideBySide
              ? sampleLangs.map((lang, index) =>
                  this.renderLanguageItem(lang, index === 0, layoutConfig, isDropdown)
                )
              : [
                  this.renderLanguageItem(current, true, layoutConfig, isDropdown),
                  others.length > 0 &&
                    h(
                      "div",
                      {
                        className:
                          "lsdp-switcher-dropdown-list lsdp-preview-expanded",
                      },
                      others.map((lang) =>
                        this.renderLanguageItem(lang, false, layoutConfig, isDropdown)
                      )
                    ),
                ]
          )
        );
      }

      renderLanguageItem(lang, isDefault, layoutConfig, isDropdown, showDropdownArrow = true) {
        const flagUrl = lang.flag;
        let displayName = "";
        if (layoutConfig.languageNames === "full") {
          displayName = lang.name;
        } else if (layoutConfig.languageNames === "short") {
          displayName = lang.code.toUpperCase();
        }
        return h(
          "a",
          {
            className: `lsdp-language-item ${
              isDefault ? "lsdp-language-item__default" : ""
            }`,
            onClick: (e) => e.preventDefault(),
          },
          layoutConfig.flagIconPosition === "before" &&
            h("img", {
              src: flagUrl,
              className: "lsdp-flag-image",
              loading: "lazy",
              alt: lang.name,
            }),
          layoutConfig.languageNames !== "none" &&
            h(
              "span",
              {
                className: "lsdp-language-item-name",
              },
              displayName
            ),
          layoutConfig.flagIconPosition === "after" &&
            h("img", {
              src: flagUrl,
              className: "lsdp-flag-image",
              loading: "lazy",
              alt: lang.name,
            }),
            isDefault && isDropdown && showDropdownArrow && h(
              "svg",
              {
                className: "lsdp-dropdown-arrow",
                width: "12",
                height: "8",
                viewBox: "0 0 12 8",
                fill: "none",
                "aria-hidden": "true"
              },
              h("path", {
                d: "M1 6.5L6 1.5L11 6.5",
                stroke: "currentColor",
                "stroke-width": "2",
                "stroke-linecap": "round",
                "stroke-linejoin": "round"
              })
            )
          );
        }

      buildPreviewStyles() {
        const { config, currentDevice } = this.state;
        const layoutConfig = config.layoutCustomizer[currentDevice];

        const position = layoutConfig.position || "bottom-right";
        const [vertical, horizontal] = position.split("-");

        return {
          "--bg": config.bgColor,
          "--bg-hover": config.bgHoverColor,
          "--text": config.textColor,
          "--text-hover": config.textHoverColor,
          "--border-color": config.borderColor,
          "--border-radius": config.borderRadius.map((r) => r + "px").join(" "),
          "--font-size": config.size === "large" ? "16px" : "14px",
          "--flag-size": config.size === "large" ? "20px" : "18px",
          "--flag-radius": config.flagRadius + "px",
          "--aspect-ratio": config.flagShape === "rect" ? "4/3" : "1",
          "--transition-duration": "0.2s",
          "--switcher-width":
            layoutConfig.width === "custom"
              ? layoutConfig.customWidth + "px"
              : "auto",
          "--switcher-padding":
            layoutConfig.padding === "custom"
              ? layoutConfig.customPadding + "px"
              : "0px 0px",
          "--border-width": config.borderWidth + "px",
          "--bottom": vertical === "bottom" ? "0px" : "auto",
          "--top": vertical === "top" ? "0px" : "auto",
          "--right": horizontal === "right" ? "14px" : "auto",
          "--left": horizontal === "left" ? "14px" : "auto",
        };
      }

      renderActionButtons() {
        const { hasChanges, isSaving } = this.state;

        return h(
          "div",
          { className: "lsdp-settings-actions" },
          h(
            "button",
            {
              className: "lsdp-submit-btn",
              type: "button",
              onClick: () => this.saveSettings(),
              disabled: !hasChanges || isSaving,
            },
            h(
              "span",
              null,
              isSaving
                ? __("Saving...", "language-switcher-for-divi-polylang")
                : __("Save changes", "language-switcher-for-divi-polylang")
            )
          ),
          h(
            "button",
            {
              className: "lsdp-button-secondary",
              type: "button",
              onClick: () => this.revertChanges(),
              disabled: !hasChanges || isSaving,
              title: __(
                "Revert to last saved values",
                "language-switcher-for-divi-polylang"
              ),
            },
            h(
              "svg",
              {
                width: 14,
                height: 14,
                viewBox: "0 0 14 14",
                fill: "none",
                style: { marginRight: "6px", verticalAlign: "middle" },
              },
              h("path", {
                d: "M7.1752 0.713867C10.7452 0.713867 13.3002 3.54187 13.3002 7.01387C13.3002 10.4859 10.7452 13.3139 7.1752 13.3139C4.9352 13.3139 2.9612 12.2009 1.7992 10.5209L3.6122 9.45687C4.3822 10.5069 5.6142 11.2139 7.0002 11.2139C9.3102 11.2139 11.2002 9.26087 11.2002 7.01387C11.2002 4.76687 9.3102 2.81387 7.0002 2.81387C5.6212 2.81387 4.3962 3.51387 3.6262 4.55687L4.9002 5.61387L0.700195 7.01387V2.11387L2.0232 3.21987C3.2062 1.70087 5.0752 0.713867 7.1752 0.713867Z",
                fill: "#2271B1",
              })
            ),
            __("Revert changes", "language-switcher-for-divi-polylang")
          )
        );
      }

      renderLeftColumn() {
        return h(
          "div",
          { className: "lsdp-floater-settings__right" },
          this.renderEnableAndType(),
          this.renderPresets(),
          this.renderCustomizeLayout(),
          this.renderCustomizeDesign()
        );
      }

      renderEnableAndType() {
        const { config } = this.state;
        const sideBySideAllowed = this.isSideBySideAllowed();
        const sideBySideDisabledTitle = __(
          "Side by Side is available for up to 3 languages.",
          "language-switcher-for-divi-polylang"
        );

        return h(
          "div",
          { className: "lsdp-settings-box" },
          h(
            "header",
            { className: "lsdp-header" },
            h(
              "span",
              { className: "lsdp-title" },
              __("Display a floating language switcher that appears across your website without adding it to your pages.", "language-switcher-for-divi-polylang")
            )
          ),
          h(
            "section",
            { className: "lsdp-body" },

            h(
              "div",
              {
                className: "lsdp-field lsdp-field--row",
                style: { marginBottom: "20px" },
              },
              h(
                "span",
                { className: "lsdp-field__label lsdp-primary-text-bold" },
                __("Enable Floating Language Switcher", "language-switcher-for-divi-polylang")
              ),
              this.renderToggleField(
                "enabled",
                config.enabled,
                config.enabled
                  ? __("Switcher is enabled", "language-switcher-for-divi-polylang")
                  : __("Switcher is disabled", "language-switcher-for-divi-polylang"),
                null
              )
            ),

            h("div", { className: "lsdp-separator" }),

            h(
              "div",
              {
                className: "lsdp-field lsdp-field--row",
                style: { gap: "12px" },
              },
              h(
                "span",
                { className: "lsdp-field__label lsdp-primary-text-bold" },
                __("Switcher Type", "language-switcher-for-divi-polylang")
              ),
              h(
                "div",
                { className: "lsdp-lc-mode-toggle" },
                h(
                  "button",
                  {
                    className: `lsdp-lc-mode-button ${
                      config.type === "dropdown" ? "active" : ""
                    }`,
                    type: "button",
                    onClick: () => this.updateConfig({ type: "dropdown" }),
                  },
                  h("span", null, __("Dropdown", "language-switcher-for-divi-polylang"))
                ),
                h(
                  "button",
                  {
                    className: `lsdp-lc-mode-button ${
                      config.type === "side-by-side" && sideBySideAllowed ? "active" : ""
                    }`,
                    type: "button",
                    disabled: !sideBySideAllowed,
                    title: sideBySideAllowed ? "" : sideBySideDisabledTitle,
                    onClick: () => {
                      if (sideBySideAllowed) {
                        this.updateConfig({ type: "side-by-side" });
                      }
                    },
                  },
                  h("span", null, __("Side by Side", "language-switcher-for-divi-polylang"))
                )
              )
            )
          )
        );
      }

      renderPresets() {
        return h(
          "div",
          { className: "lsdp-settings-box" },
          h(
            "header",
            { className: "lsdp-header" },
            h(
              "span",
              { className: "lsdp-title" },
              __("Apply a Preset", "language-switcher-for-divi-polylang")
            )
          ),
          h(
            "section",
            { className: "lsdp-body" },
            h(
              "div",
              { className: "lsdp-preset-applier" },
              this.presets.map((preset) => this.renderPresetCard(preset))
            )
          )
        );
      }

      renderPresetCard(preset) {
        const { languages, showPresetConfirm, config } = this.state;
        const layoutConfig = config.layoutCustomizer.desktop;
        const isDropdown = config.type === "dropdown";
        const isSideBySide = config.type === "side-by-side";

        let allLangs = languages.length > 0
          ? languages
          : [
              { code: "en", name: "English", flag: "" },
              { code: "fr", name: "French", flag: "" },
            ];

        const maxLanguages = isSideBySide ? this.getSideBySideMaxLanguages() : 5;
        const sampleLangs = allLangs.slice(0, maxLanguages);

        const presetStyles = {
          "--bg": preset.config.bgColor,
          "--bg-hover": preset.config.bgHoverColor,
          "--text": preset.config.textColor,
          "--text-hover": preset.config.textHoverColor,
          "--border-color": preset.config.borderColor,
          "--border-radius": "8px",
          "--font-size": "14px",
          "--flag-size": "18px",
          "--flag-radius": "2px",
          "--aspect-ratio": "4/3",
          "--transition-duration": "0.2s",
        };

        const current = sampleLangs[0];
        const others = sampleLangs.slice(1);

        const isConfirming =
          showPresetConfirm && showPresetConfirm.name === preset.name;

        return h(
          "div",
          {
            className: `lsdp-preset-card${
              this.isPresetActive(preset) ? " lsdp-preset-card-active" : ""
            }`,
            style: { ...presetStyles, position: "relative" },
          },

          isConfirming &&
            h(
              "div",
              {
                className: "lsdp-preset-confirm-overlay",
              },
              h(
                "div",
                { className: "lsdp-preset-confirm-content" },
                h(
                  "p",
                  { className: "lsdp-preset-confirm-title" },
                  __(
                    "Are you sure you want to apply the ",
                    "language-switcher-for-divi-polylang"
                  ),
                  h("strong", null, preset.name),
                  __(" preset?", "language-switcher-for-divi-polylang")
                ),
                h(
                  "p",
                  { className: "lsdp-preset-confirm-warning" },
                  __(
                    "It will override your current settings.",
                    "language-switcher-for-divi-polylang"
                  )
                ),
                h(
                  "div",
                  { className: "lsdp-preset-confirm-actions" },
                  h(
                    "button",
                    {
                      className:
                        "lsdp-preset-confirm-btn lsdp-preset-confirm-btn-primary",
                      onClick: () => this.applyPreset(preset),
                    },
                    __("Apply Preset", "language-switcher-for-divi-polylang")
                  ),
                  h(
                    "button",
                    {
                      className:
                        "lsdp-preset-confirm-btn lsdp-preset-confirm-btn-secondary",
                      onClick: () => this.cancelPresetConfirmation(),
                    },
                    __("Cancel", "language-switcher-for-divi-polylang")
                  )
                )
              )
            ),

          h(
            "div",
            {
              className: "lsdp-preview-rect",
              style: { background: preset.background },
            },
            h(
              "div",
              {
                className: `lsdp-preset-switcher-preview lsdp-language-switcher lsdp-floating-switcher lsdp-ls-${
                  isDropdown ? "dropdown" : "inline"
                } lsdp-switcher-position-bottom`,
              },
              h(
                "div",
                { className: "lsdp-language-switcher-inner" },
                isSideBySide
                  ? sampleLangs.map((lang, index) =>
                      this.renderLanguageItem(lang, index === 0, layoutConfig, isDropdown, false)
                    )
                  : [
                      this.renderLanguageItem(current, true, layoutConfig, isDropdown, false),
                      others.length > 0 &&
                        h(
                          "div",
                          { className: "lsdp-switcher-dropdown-list" },
                          others.map((lang) =>
                            this.renderLanguageItem(lang, false, layoutConfig, isDropdown, false)
                          )
                        ),
                    ]
              )
            )
          ),
          h(
            "button",
            {
              className: `lsdp-apply-btn${
                this.isPresetActive(preset) ? " lsdp-apply-btn-active" : ""
              }`,
              onClick: () => this.showPresetConfirmation(preset),
              disabled: this.isPresetActive(preset),
            },
            this.isPresetActive(preset)
              ? __("Applied", "language-switcher-for-divi-polylang")
              : sprintf(
                  __("Apply %s Preset", "language-switcher-for-divi-polylang"),
                  preset.name
                )
          )
        );
      }

      renderCustomizeDesign() {
        const { config } = this.state;

        return h(
          "div",
          {
            className: "lsdp-settings-box lsdp-collapsible",
            style: { "--lsdp-field-label-width": "190px" },
          },
          h(
            "header",
            {
              className: "lsdp-header",
              onClick: (e) => this.toggleCollapsible(e),
            },
            h(
              "span",
              { className: "lsdp-title" },
              __("Customize Design", "language-switcher-for-divi-polylang")
            ),
            this.renderChevron()
          ),
          h(
            "section",
            { className: "lsdp-body" },

            this.renderColorField(
              "bgColor",
              __("Background color", "language-switcher-for-divi-polylang"),
              config.bgColor
            ),
            this.renderColorField(
              "bgHoverColor",
              __("Background hover color", "language-switcher-for-divi-polylang"),
              config.bgHoverColor
            ),
            this.renderColorField(
              "textColor",
              __("Text color", "language-switcher-for-divi-polylang"),
              config.textColor
            ),
            this.renderColorField(
              "textHoverColor",
              __("Text hover color", "language-switcher-for-divi-polylang"),
              config.textHoverColor
            ),
            this.renderColorField(
              "borderColor",
              __("Switcher border color", "language-switcher-for-divi-polylang"),
              config.borderColor
            ),

            this.renderNumberField(
              "borderWidth",
              __("Switcher border width", "language-switcher-for-divi-polylang"),
              config.borderWidth
            ),

            this.renderBorderRadiusField(),

            h("div", { className: "lsdp-separator" }),

            this.renderRadioGroup(
              "size",
              config.size,
              [
                {
                  value: "normal",
                  label: __("Normal", "language-switcher-for-divi-polylang"),
                },
                {
                  value: "large",
                  label: __("Large", "language-switcher-for-divi-polylang"),
                },
              ],
              __("Flag and text size", "language-switcher-for-divi-polylang"),
              "column"
            ),

            h("div", { className: "lsdp-separator" }),

            this.renderRadioGroup(
              "flagShape",
              config.flagShape,
              [
                {
                  value: "rect",
                  label: __("Rectangle (4:3)", "language-switcher-for-divi-polylang"),
                },
                {
                  value: "square",
                  label: __("Square (1:1)", "language-switcher-for-divi-polylang"),
                },
              ],
              __("Flag icons shape", "language-switcher-for-divi-polylang"),
              "column"
            ),

            this.renderNumberField(
              "flagRadius",
              __("Flag icons border radius", "language-switcher-for-divi-polylang"),
              config.flagRadius
            ),

            h("div", { className: "lsdp-separator" }),

            this.renderToggleField(
              "enableCustomCss",
              config.enableCustomCss,
              __("Enable custom CSS", "language-switcher-for-divi-polylang"),
              null
            ),

            config.enableCustomCss && this.renderCustomCssField()
          )
        );
      }

      renderCustomizeLayout() {
        const { config, currentDevice } = this.state;
        const layoutConfig = config.layoutCustomizer[currentDevice];

        return h(
          "div",
          { className: "lsdp-settings-box lsdp-collapsible" },
          h(
            "header",
            {
              className: "lsdp-header",
              onClick: (e) => this.toggleCollapsible(e),
            },
            h(
              "span",
              { className: "lsdp-title" },
              __("Customize Layout", "language-switcher-for-divi-polylang")
            ),
            this.renderChevron()
          ),
          h(
            "section",
            { className: "lsdp-body" },
            h(
              "div",
              {
                className:
                  "lsdp-layout-customizer-field lsdp-field lsdp-field--column lsdp-field lsdp-field--row",
              },
              h(
                "div",
                { className: "lsdp-lc-mode-toggle" },
                h(
                  "button",
                  {
                    className: `lsdp-lc-mode-button ${
                      currentDevice === "desktop" ? "active" : ""
                    }`,
                    type: "button",
                    onClick: () => this.setState({ currentDevice: "desktop" }),
                  },
                  this.renderDesktopIcon(),
                  h("span", null, __("Desktop", "language-switcher-for-divi-polylang"))
                ),
                h(
                  "button",
                  {
                    className: `lsdp-lc-mode-button ${
                      currentDevice === "mobile" ? "active" : ""
                    }`,
                    type: "button",
                    onClick: () => this.setState({ currentDevice: "mobile" }),
                  },
                  this.renderMobileIcon(),
                  h("span", null, __("Mobile", "language-switcher-for-divi-polylang"))
                )
              ),

              h(
                "div",
                { className: "lsdp-lc-settings-panel" },
                h(
                  "div",
                  { className: "lsdp-lc-section" },
                  h(
                    "div",
                    { className: "lsdp-lc-subfield" },
                    this.renderLayoutRadioGroup(
                      "position",
                      [
                        {
                          value: "bottom-right",
                          label: __("Bottom Right", "language-switcher-for-divi-polylang"),
                        },
                        {
                          value: "bottom-left",
                          label: __("Bottom Left", "language-switcher-for-divi-polylang"),
                        },
                        {
                          value: "top-right",
                          label: __("Top Right", "language-switcher-for-divi-polylang"),
                        },
                        {
                          value: "top-left",
                          label: __("Top Left", "language-switcher-for-divi-polylang"),
                        },
                      ],
                      __("Switcher Position", "language-switcher-for-divi-polylang")
                    )
                  ),

                  h(
                    "div",
                    { className: "lsdp-lc-subfield" },
                    this.renderLayoutRadioGroup(
                      "width",
                      [
                        {
                          value: "default",
                          label: __("Default", "language-switcher-for-divi-polylang"),
                        },
                        {
                          value: "custom",
                          label: __("Custom", "language-switcher-for-divi-polylang"),
                        },
                      ],
                      __("Switcher Width", "language-switcher-for-divi-polylang")
                    )
                  ),

                  layoutConfig.width === "custom" &&
                    h(
                      "div",
                      { className: "lsdp-lc-subfield" },
                      this.renderLayoutNumberField(
                        "customWidth",
                        __("Custom Width", "language-switcher-for-divi-polylang"),
                        layoutConfig.customWidth
                      )
                    ),

                  h(
                    "div",
                    { className: "lsdp-lc-subfield" },
                    this.renderLayoutRadioGroup(
                      "padding",
                      [
                        {
                          value: "default",
                          label: __("Default", "language-switcher-for-divi-polylang"),
                        },
                        {
                          value: "custom",
                          label: __("Custom", "language-switcher-for-divi-polylang"),
                        },
                      ],
                      __("Switcher Padding", "language-switcher-for-divi-polylang")
                    )
                  ),

                  layoutConfig.padding === "custom" &&
                    h(
                      "div",
                      { className: "lsdp-lc-subfield" },
                      this.renderLayoutNumberField(
                        "customPadding",
                        __("Custom Padding", "language-switcher-for-divi-polylang"),
                        layoutConfig.customPadding
                      )
                    ),

                  layoutConfig.languageNames !== "none" &&
                    h(
                      "div",
                      { className: "lsdp-lc-subfield" },
                      this.renderLayoutRadioGroup(
                        "flagIconPosition",
                        [
                          {
                            value: "before",
                            label: __("Before Language", "language-switcher-for-divi-polylang"),
                          },
                          {
                            value: "after",
                            label: __("After Language", "language-switcher-for-divi-polylang"),
                          },
                          {
                            value: "hide",
                            label: __("Hide Icons", "language-switcher-for-divi-polylang"),
                          },
                        ],
                        __("Flag Icons Position", "language-switcher-for-divi-polylang")
                      )
                    ),

                  layoutConfig.flagIconPosition !== "hide" &&
                    h(
                      "div",
                      { className: "lsdp-lc-subfield" },
                      this.renderLayoutRadioGroup(
                        "languageNames",
                        [
                          {
                            value: "full",
                            label: __("Full Names", "language-switcher-for-divi-polylang"),
                          },
                          {
                            value: "short",
                            label: __("Short Names", "language-switcher-for-divi-polylang"),
                          },
                          {
                            value: "none",
                            label: __("No Names", "language-switcher-for-divi-polylang"),
                          },
                        ],
                        __("Language Names", "language-switcher-for-divi-polylang")
                      )
                    )
                )
              )
            )
          )
        );
      }

      renderToggleField(key, value, label, description) {
        return h(
          "div",
          { className: "lsdp-toggle-status-field lsdp-field lsdp-field--row" },
          h("span", { className: "lsdp-primary-text" }, label),
          h(
            "div",
            { className: "lsdp-toggle-wrapper" },
            h(
              "div",
              { className: "lsdp-toggle-inner" },
              h("input", {
                type: "checkbox",
                className: "lsdp-toggle-input",
                checked: value,
                onChange: (e) => this.updateConfig({ [key]: e.target.checked }),
              }),
              h("span", { className: "lsdp-toggle-slider" })
            )
          )
        );
      }

      renderRadioGroup(key, value, options, title = null, layout = "column") {
        return h(
          "div",
          {
            className: `lsdp-radio-group__wrapper lsdp-field lsdp-field--${layout}`,
          },
          title &&
            h(
              "span",
              { className: "lsdp-field__label lsdp-primary-text-bold" },
              title
            ),
          h(
            "div",
            { className: "lsdp-radio-group" },
            options.map((option) =>
              h(
                "div",
                { className: "lsdp-radio-option", key: option.value },
                h(
                  "label",
                  { className: "lsdp-radio-label" },
                  h("input", {
                    type: "radio",
                    name: key,
                    checked: value === option.value,
                    value: option.value,
                    onChange: (e) => this.updateConfig({ [key]: e.target.value }),
                  }),
                  h("span", null, option.label)
                )
              )
            )
          )
        );
      }

      renderLayoutRadioGroup(key, options, title) {
        const { currentDevice, config } = this.state;
        const value = config.layoutCustomizer[currentDevice][key];

        return h(
          "div",
          { className: "lsdp-radio-group__wrapper" },
          h(
            "span",
            { className: "lsdp-field__label lsdp-primary-text-bold" },
            title
          ),
          h(
            "div",
            { className: "lsdp-radio-group" },
            options.map((option) =>
              h(
                "div",
                { className: "lsdp-radio-option", key: option.value },
                h(
                  "label",
                  { className: "lsdp-radio-label" },
                  h("input", {
                    type: "radio",
                    name: `${currentDevice}-${key}`,
                    checked: value === option.value,
                    value: option.value,
                    onChange: (e) =>
                      this.updateLayoutConfig(currentDevice, {
                        [key]: e.target.value,
                      }),
                  }),
                  h("span", null, option.label)
                )
              )
            )
          )
        );
      }

      renderColorField(key, label, value) {
        const displayValue =
          value && value.length > 7 ? value.substring(0, 7) : value;
        const isTransparent = value === "transparent";

        return h(
          "div",
          { className: "lsdp-field lsdp-field--row" },
          h(
            "span",
            { className: "lsdp-field__label lsdp-primary-text-bold" },
            label
          ),
          h(
            "div",
            { className: "lsdp-color__wrapper" },
            h("input", {
              type: "color",
              className: "lsdp-color-input",
              value: isTransparent ? "#ffffff" : displayValue,
              onChange: (e) => this.updateConfig({ [key]: e.target.value }),
              title: __("Pick a color", "language-switcher-for-divi-polylang"),
            }),
            h(
              "span",
              {
                className: "lsdp-color-code lsdp-primary-text",
                style: { cursor: isTransparent ? "pointer" : "default" },
                onClick: isTransparent
                  ? () => this.updateConfig({ [key]: "#000000" })
                  : null,
              },
              value.toUpperCase()
            )
          )
        );
      }

      renderNumberField(key, label, value, min = 0) {
        return h(
          "div",
          { className: "lsdp-field lsdp-field--row" },
          h(
            "span",
            { className: "lsdp-field__label lsdp-primary-text-bold" },
            label
          ),
          h(
            "div",
            { className: "lsdp-number__wrapper" },
            h("input", {
              type: "number",
              className: "lsdp-number-input",
              min: min,
              value: value,
              onChange: (e) =>
                this.updateConfig({ [key]: parseInt(e.target.value) || 0 }),
            }),
            h("span", { className: "lsdp-primary-text" }, "px")
          )
        );
      }

      renderLayoutNumberField(key, label, value) {
        const { currentDevice } = this.state;

        return h(
          "div",
          { className: "lsdp-field lsdp-field--row" },
          h(
            "span",
            { className: "lsdp-field__label lsdp-primary-text-bold" },
            label
          ),
          h(
            "div",
            { className: "lsdp-number__wrapper" },
            h("input", {
              type: "number",
              className: "lsdp-number-input",
              min: 0,
              value: value,
              onChange: (e) =>
                this.updateLayoutConfig(currentDevice, {
                  [key]: parseInt(e.target.value) || 0,
                }),
            }),
            h("span", { className: "lsdp-primary-text" }, "px")
          )
        );
      }

      renderBorderRadiusField() {
        const { config } = this.state;

        const corners = [
          __("Top Left", "language-switcher-for-divi-polylang"),
          __("Top Right", "language-switcher-for-divi-polylang"),
          __("Bottom Right", "language-switcher-for-divi-polylang"),
          __("Bottom Left", "language-switcher-for-divi-polylang"),
        ];

        return h(
          "div",
          { className: "lsdp-field lsdp-field--column" },
          h(
            "span",
            { className: "lsdp-field__label lsdp-primary-text-bold" },
            __("Switcher border radius", "language-switcher-for-divi-polylang")
          ),
          h(
            "div",
            { className: "lsdp-quad-grid" },
            corners.map((corner, index) =>
              h(
                "div",
                { className: "lsdp-quad-radius-corner", key: corner },
                h(
                  "span",
                  { className: "lsdp-primary-text lsdp-corner-label" },
                  corner
                ),
                h(
                  "div",
                  { className: "lsdp-number__wrapper" },
                  h("input", {
                    type: "number",
                    className: "lsdp-number-input",
                    min: 0,
                    value: config.borderRadius[index],
                    onChange: (e) => {
                      const newRadius = [...config.borderRadius];
                      newRadius[index] = parseInt(e.target.value) || 0;
                      this.updateConfig({ borderRadius: newRadius });
                    },
                  }),
                  h("span", { className: "lsdp-primary-text" }, "px")
                )
              )
            )
          )
        );
      }

      renderCustomCssField() {
        const { config } = this.state;

        return h(
          "div",
          {
            className: "lsdp-custom-css-editor lsdp-field lsdp-field--row",
            style: { display: config.enableCustomCss ? "block" : "none" },
          },
          h("textarea", {
            placeholder: __("Write custom CSS here...", "language-switcher-for-divi-polylang"),
            value: config.customCss,
            onChange: (e) => this.updateConfig({ customCss: e.target.value }),
            style: {
              width: "100%",
              minHeight: "200px",
              fontFamily: '"Courier New", monospace',
              fontSize: "13px",
            },
          })
        );
      }

      renderChevron() {
        return h(
          "svg",
          {
            className: "lsdp-chevron open",
            viewBox: "0 0 20 20",
            width: 20,
            height: 20,
          },
          h("path", {
            d: "M5 6L10 11L15 6L17 7L10 14L3 7L5 6Z",
            fill: "#9CA1A8",
          })
        );
      }

      renderDesktopIcon() {
        return h(
          "svg",
          {
            width: 20,
            height: 20,
            viewBox: "0 0 20 20",
            fill: "none",
          },
          h("path", {
            fillRule: "evenodd",
            clipRule: "evenodd",
            d: "M3 2H17C17.55 2 18 2.45 18 3V13C18 13.55 17.55 14 17 14H12V16H14C14.55 16 15 16.45 15 17V18H5V17C5 16.45 5.45 16 6 16H8V14H3C2.45 14 2 13.55 2 13V3C2 2.45 2.45 2 3 2ZM16 11V4H4V11H16Z",
            fill: "#1D2327",
          })
        );
      }

      renderMobileIcon() {
        return h(
          "svg",
          {
            width: 20,
            height: 20,
            viewBox: "0 0 20 20",
            fill: "none",
          },
          h("path", {
            fillRule: "evenodd",
            clipRule: "evenodd",
            d: "M6 2H14C14.55 2 15 2.45 15 3V17C15 17.55 14.55 18 14 18H6C5.45 18 5 17.55 5 17V3C5 2.45 5.45 2 6 2ZM13 14V4H7V14H13Z",
            fill: "#1D2327",
          })
        );
      }
    }

    /**
     * Initialize the App
     */
    document.addEventListener("DOMContentLoaded", function () {
      const root = document.getElementById("lsdp-floater-app-root");

      if (root && typeof wp !== "undefined" && wp.element) {
        const { render, createElement: h } = wp.element;
        render(h(FloaterApp), root);
      }
    });
  })();
