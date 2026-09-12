(function (blocks, element, blockEditor, components, compose, i18n, serverSideRender) {
    'use strict';

    var el = element.createElement;
    var registerBlockType = blocks.registerBlockType;
    var InspectorControls = blockEditor.InspectorControls;
    var BlockControls = blockEditor.BlockControls;
    var AlignmentControl = blockEditor.AlignmentControl;
    var useBlockProps = blockEditor.useBlockProps;
    var useRefEffect = compose.useRefEffect;
    var PanelBody = components.PanelBody;
    var ToggleControl = components.ToggleControl;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var Button = components.Button;
    var TabPanel = components.TabPanel;
    var BoxControl = components.__experimentalBoxControl || components.BoxControl;
    var RangeControl = components.RangeControl;
    var ColorPalette = components.ColorPalette;
    var Notice = components.Notice;
    var ServerSideRender = serverSideRender;
    var __ = i18n.__;
    var useState = element.useState;

    // Get settings from localized script
    var settings = window.lsdpBlockSettings || { options: {}, languages: [], polylangActive: false };

    registerBlockType('lsdp/language-switcher', {
        apiVersion: 3,
        title: __('Language Switcher', 'language-switcher-for-divi-polylang'),
        description: __('Display a language switcher block', 'language-switcher-for-divi-polylang'),
        category: 'widgets',
        icon: el(
            'svg',
            {
                xmlns: 'http://www.w3.org/2000/svg',
                width: 20,
                height: 20,
                viewBox: '0 0 640 512'
            },
            el('path', {
                fill: 'currentColor',
                d: 'M0 128c0-35.3 28.7-64 64-64h512c35.3 0 64 28.7 64 64v256c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64zm320 0v256h256V128zm-141.7 47.9c-3.2-7.2-10.4-11.9-18.3-11.9s-15.1 4.7-18.3 11.9l-64 144c-4.5 10.1.1 21.9 10.2 26.4s21.9-.1 26.4-10.2l8.9-20.1h73.6l8.9 20.1c4.5 10.1 16.3 14.6 26.4 10.2s14.6-16.3 10.2-26.4zM160 233.2l19 42.8h-38zM448 164c11 0 20 9 20 20v4h60c11 0 20 9 20 20s-9 20-20 20h-2l-1.6 4.5c-8.9 24.4-22.4 46.6-39.6 65.4c.9.6 1.8 1.1 2.7 1.6l18.9 11.3c9.5 5.7 12.5 18 6.9 27.4s-18 12.5-27.4 6.9L467 333.8c-4.5-2.7-8.8-5.5-13.1-8.5c-10.6 7.5-21.9 14-34 19.4l-3.6 1.6c-10.1 4.5-21.9-.1-26.4-10.2s.1-21.9 10.2-26.4l3.6-1.6c6.4-2.9 12.6-6.1 18.5-9.8L410 286.1c-7.8-7.8-7.8-20.5 0-28.3s20.5-7.8 28.3 0l14.6 14.6l.5.5c12.4-13.1 22.5-28.3 29.8-45l-35.2.1h-72c-11 0-20-9-20-20s9-20 20-20h52v-4c0-11 9-20 20-20'
            })
        ),
        keywords: [
            __('language', 'language-switcher-for-divi-polylang'),
            __('polylang', 'language-switcher-for-divi-polylang'),
            __('switcher', 'language-switcher-for-divi-polylang')
        ],
        attributes: {
            show_names: {
                type: 'boolean',
                default: true
            },
            show_flags: {
                type: 'boolean',
                default: true
            },
            show_language_codes: {
                type: 'boolean',
                default: false
            },
            hide_current: {
                type: 'boolean',
                default: false
            },
            hide_if_no_translation: {
                type: 'boolean',
                default: false
            },
            dropdown: {
                type: 'string',
                default: 'dropdown'
            },
            marginTop: {
                type: 'number',
                default: 0
            },
            marginRight: {
                type: 'number',
                default: 0
            },
            marginBottom: {
                type: 'number',
                default: 0
            },
            marginLeft: {
                type: 'number',
                default: 0
            },
            paddingTop: {
                type: 'number',
                default: 0
            },
            paddingRight: {
                type: 'number',
                default: 0
            },
            paddingBottom: {
                type: 'number',
                default: 0
            },
            paddingLeft: {
                type: 'number',
                default: 0
            },
            borderColor: {
                type: 'string',
                default: ''
            },
            borderStyle: {
                type: 'string',
                default: 'solid'
            },
            borderWidth: {
                type: 'string',
                default: '0px'
            },
            borderWidthTop: {
                type: 'number',
                default: 0
            },
            borderWidthRight: {
                type: 'number',
                default: 0
            },
            borderWidthBottom: {
                type: 'number',
                default: 0
            },
            borderWidthLeft: {
                type: 'number',
                default: 0
            },
            borderRadiusTopLeft: {
                type: 'number',
                default: 0
            },
            borderRadiusTopRight: {
                type: 'number',
                default: 0
            },
            borderRadiusBottomRight: {
                type: 'number',
                default: 0
            },
            borderRadiusBottomLeft: {
                type: 'number',
                default: 0
            },
            flagRatio: {
                type: 'string',
                default: '4/3'
            },
            flagWidth: {
                type: 'number',
                default: 24
            },
            flagRadius: {
                type: 'number',
                default: 0
            },
            fontSize: {
                type: 'string',
                default: ''
            },
            fontFamily: {
                type: 'string',
                default: ''
            },
            textColor: {
                type: 'string',
                default: ''
            },
            backgroundColor: {
                type: 'string',
                default: ''
            },
            textTransform: {
                type: 'string',
                default: 'none'
            },
            alignment: {
                type: 'string',
                default: 'left'
            },
            customLanguages: {
                type: 'array',
                default: []
            }
        },
        supports: {
            html: false,
            customClassName: true,
            className: true
        },

        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;

            // Notice for invalid custom language selections (e.g. duplicates)
            var duplicateLanguageNoticeState = useState(null);
            var duplicateLanguageNotice = duplicateLanguageNoticeState[0];
            var setDuplicateLanguageNotice = duplicateLanguageNoticeState[1];

            // Auto-populate custom languages when Polylang is not available.
            if (!settings.polylangActive && (!attributes.customLanguages || attributes.customLanguages.length === 0)) {
                setAttributes({
                    customLanguages: [
                        {
                            language: 'en_US'
                        },
                        {
                            language: 'fr_FR'
                        }
                    ]
                });
            }

            // Helper function to create spacing control using BoxControl
            var createSpacingControl = function(label, type) {
                if (!BoxControl) {
                    return null;
                }

                var topAttr = type + 'Top';
                var rightAttr = type + 'Right';
                var bottomAttr = type + 'Bottom';
                var leftAttr = type + 'Left';

                // Create value object from individual attributes
                var values = {
                    top: (attributes[topAttr] || 0) + 'px',
                    right: (attributes[rightAttr] || 0) + 'px',
                    bottom: (attributes[bottomAttr] || 0) + 'px',
                    left: (attributes[leftAttr] || 0) + 'px'
                };

                return el(BoxControl, {
                    key: type,
                    label: label,
                    values: values,
                    onChange: function(newValues) {
                        var newAttrs = {};
                        if (newValues) {
                            newAttrs[topAttr] = parseInt(newValues.top) || 0;
                            newAttrs[rightAttr] = parseInt(newValues.right) || 0;
                            newAttrs[bottomAttr] = parseInt(newValues.bottom) || 0;
                            newAttrs[leftAttr] = parseInt(newValues.left) || 0;
                        }
                        setAttributes(newAttrs);
                    }
                });
            };

            // Helper function to create typography controls
            var createTypographyControls = function() {
                var controls = [];

                // Font Size Control
                controls.push(
                    el(RangeControl, {
                        key: 'fontSize',
                        label: __('Font Size', 'language-switcher-for-divi-polylang'),
                        value: parseInt(attributes.fontSize) || 16,
                        onChange: function(value) {
                            setAttributes({ fontSize: value + 'px' });
                        },
                        min: 10,
                        max: 72,
                        step: 1,
                        __next40pxDefaultSize: true,
                        __nextHasNoMarginBottom: true
                    })
                );

                // Font Family Control
                controls.push(
                    el(SelectControl, {
                        key: 'fontFamily',
                        label: __('Font Family', 'language-switcher-for-divi-polylang'),
                        value: attributes.fontFamily || '',
                        options: [
                            { label: __('Default', 'language-switcher-for-divi-polylang'), value: '' },
                            { label: 'Arial', value: 'Arial, sans-serif' },
                            { label: 'Helvetica', value: 'Helvetica, sans-serif' },
                            { label: 'Times New Roman', value: '"Times New Roman", serif' },
                            { label: 'Georgia', value: 'Georgia, serif' },
                            { label: 'Courier New', value: '"Courier New", monospace' },
                            { label: 'Verdana', value: 'Verdana, sans-serif' },
                            { label: 'Trebuchet MS', value: '"Trebuchet MS", sans-serif' },
                            { label: 'Comic Sans MS', value: '"Comic Sans MS", cursive' },
                            { label: 'Impact', value: 'Impact, sans-serif' }
                        ],
                        onChange: function(value) {
                            setAttributes({ fontFamily: value });
                        },
                        __next40pxDefaultSize: true,
                        __nextHasNoMarginBottom: true
                    })
                );

                // Text Transform Control
                controls.push(
                    el(SelectControl, {
                        key: 'textTransform',
                        label: __('Text Transform', 'language-switcher-for-divi-polylang'),
                        value: attributes.textTransform || 'none',
                        options: [
                            { label: __('None', 'language-switcher-for-divi-polylang'), value: 'none' },
                            { label: __('Uppercase', 'language-switcher-for-divi-polylang'), value: 'uppercase' },
                            { label: __('Lowercase', 'language-switcher-for-divi-polylang'), value: 'lowercase' },
                            { label: __('Capitalize', 'language-switcher-for-divi-polylang'), value: 'capitalize' }
                        ],
                        onChange: function(value) {
                            setAttributes({ textTransform: value });
                        },
                        __next40pxDefaultSize: true,
                        __nextHasNoMarginBottom: true
                    })
                );

                // Alignment Control (Left / Center / Right)
                controls.push(
                    el(SelectControl, {
                        key: 'alignment',
                        label: __('Alignment', 'language-switcher-for-divi-polylang'),
                        value: attributes.alignment || 'left',
                        options: [
                            { label: __('Left', 'language-switcher-for-divi-polylang'), value: 'left' },
                            { label: __('Center', 'language-switcher-for-divi-polylang'), value: 'center' },
                            { label: __('Right', 'language-switcher-for-divi-polylang'), value: 'right' }
                        ],
                        onChange: function(value) {
                            setAttributes({ alignment: value });
                        },
                        __next40pxDefaultSize: true,
                        __nextHasNoMarginBottom: true
                    })
                );

                // Text Color Control
                controls.push(
                    el('div', {
                        key: 'textColorWrapper',
                        style: { marginBottom: '16px' }
                    },
                        el('label', {
                            style: {
                                display: 'block',
                                marginBottom: '8px',
                                fontSize: '11px',
                                fontWeight: '500',
                                textTransform: 'uppercase'
                            }
                        }, __('Text Color', 'language-switcher-for-divi-polylang')),
                        el(ColorPalette, {
                            value: attributes.textColor,
                            onChange: function(color) {
                                setAttributes({ textColor: color });
                            },
                            clearable: true
                        })
                    )
                );

                // Background Color Control
                controls.push(
                    el('div', {
                        key: 'backgroundColorWrapper',
                        style: { marginBottom: '16px' }
                    },
                        el('label', {
                            style: {
                                display: 'block',
                                marginBottom: '8px',
                                fontSize: '11px',
                                fontWeight: '500',
                                textTransform: 'uppercase'
                            }
                        }, __('Background Color', 'language-switcher-for-divi-polylang')),
                        el(ColorPalette, {
                            value: attributes.backgroundColor,
                            onChange: function(color) {
                                setAttributes({ backgroundColor: color });
                            },
                            clearable: true
                        })
                    )
                );

                return controls;
            };

            // Helper function to create border controls
            var createBorderControls = function() {
                var controls = [];

                // Border Color Control
                controls.push(
                    el('div', {
                        key: 'borderColorWrapper',
                        style: { marginBottom: '16px' }
                    },
                        el('label', {
                            style: {
                                display: 'block',
                                marginBottom: '8px',
                                fontSize: '11px',
                                fontWeight: '500',
                                textTransform: 'uppercase'
                            }
                        }, __('Border Color', 'language-switcher-for-divi-polylang')),
                        el(ColorPalette, {
                            value: attributes.borderColor,
                            onChange: function(color) {
                                setAttributes({ borderColor: color });
                            },
                            clearable: true
                        })
                    )
                );

                // Border Style Control
                controls.push(
                    el(SelectControl, {
                        key: 'borderStyle',
                        label: __('Border Style', 'language-switcher-for-divi-polylang'),
                        value: attributes.borderStyle || 'solid',
                        options: [
                            { label: __('Solid', 'language-switcher-for-divi-polylang'), value: 'solid' },
                            { label: __('Dashed', 'language-switcher-for-divi-polylang'), value: 'dashed' },
                            { label: __('Dotted', 'language-switcher-for-divi-polylang'), value: 'dotted' },
                            { label: __('Double', 'language-switcher-for-divi-polylang'), value: 'double' },
                            { label: __('None', 'language-switcher-for-divi-polylang'), value: 'none' }
                        ],
                        onChange: function(value) {
                            setAttributes({ borderStyle: value });
                        },
                        __next40pxDefaultSize: true,
                        __nextHasNoMarginBottom: true
                    })
                );

                // Border Width Control using BoxControl
                if (BoxControl) {
                    var borderWidthValues = {
                        top: (attributes.borderWidthTop || 0) + 'px',
                        right: (attributes.borderWidthRight || 0) + 'px',
                        bottom: (attributes.borderWidthBottom || 0) + 'px',
                        left: (attributes.borderWidthLeft || 0) + 'px'
                    };

                    controls.push(
                        el(BoxControl, {
                            key: 'borderWidth',
                            label: __('Border Width', 'language-switcher-for-divi-polylang'),
                            values: borderWidthValues,
                            onChange: function(newValues) {
                                var newAttrs = {};
                                if (newValues) {
                                    newAttrs.borderWidthTop = parseInt(newValues.top) || 0;
                                    newAttrs.borderWidthRight = parseInt(newValues.right) || 0;
                                    newAttrs.borderWidthBottom = parseInt(newValues.bottom) || 0;
                                    newAttrs.borderWidthLeft = parseInt(newValues.left) || 0;
                                }
                                setAttributes(newAttrs);
                            }
                        })
                    );
                }

                // Border Radius Control using BoxControl
                if (BoxControl) {
                    var borderRadiusValues = {
                        top: (attributes.borderRadiusTopLeft || 0) + 'px',
                        right: (attributes.borderRadiusTopRight || 0) + 'px',
                        bottom: (attributes.borderRadiusBottomRight || 0) + 'px',
                        left: (attributes.borderRadiusBottomLeft || 0) + 'px'
                    };

                    controls.push(
                        el(BoxControl, {
                            key: 'borderRadius',
                            label: __('Border Radius', 'language-switcher-for-divi-polylang'),
                            values: borderRadiusValues,
                            onChange: function(newValues) {
                                var newAttrs = {};
                                if (newValues) {
                                    newAttrs.borderRadiusTopLeft = parseInt(newValues.top) || 0;
                                    newAttrs.borderRadiusTopRight = parseInt(newValues.right) || 0;
                                    newAttrs.borderRadiusBottomRight = parseInt(newValues.bottom) || 0;
                                    newAttrs.borderRadiusBottomLeft = parseInt(newValues.left) || 0;
                                }
                                setAttributes(newAttrs);
                            }
                        })
                    );
                }

                return controls;
            };

            // Helper function to create flag controls
            var createFlagControls = function() {
                return [
                    el(SelectControl, {
                        key: 'flagRatio',
                        label: __('Flag Ratio', 'language-switcher-for-divi-polylang'),
                        value: attributes.flagRatio || '4/3',
                        options: [
                            { label: '4:3', value: '4/3' },
                            { label: '1:1', value: '1/1' }
                        ],
                        onChange: function(value) {
                            setAttributes({ flagRatio: value });
                        },
                        __next40pxDefaultSize: true,
                        __nextHasNoMarginBottom: true
                    }),
                    el(RangeControl, {
                        key: 'flagWidth',
                        label: __('Flag Width', 'language-switcher-for-divi-polylang'),
                        value: attributes.flagWidth || 24,
                        onChange: function(value) {
                            setAttributes({ flagWidth: value });
                        },
                        min: 0,
                        max: 100,
                        step: 1,
                        __next40pxDefaultSize: true,
                        __nextHasNoMarginBottom: true
                    }),
                    el(RangeControl, {
                        key: 'flagRadius',
                        label: __('Flag Radius', 'language-switcher-for-divi-polylang'),
                        value: attributes.flagRadius || 0,
                        onChange: function(value) {
                            setAttributes({ flagRadius: value });
                        },
                        min: 0,
                        max: 100,
                        step: 1,
                        __next40pxDefaultSize: true,
                        __nextHasNoMarginBottom: true
                    })
                ];
            };

            // Create controls for each option
            var controls = [];
            var defaultTabControls = []; // Controls for Default tab (excludes hide_current and hide_if_no_translation)

            for (var option in settings.options) {
                if (settings.options.hasOwnProperty(option)) {
                    (function (opt) {
                        var optionData = settings.options[opt];

                        // Skip these options for default tab controls
                        var excludeFromDefault = opt === 'hide_current' || opt === 'hide_if_no_translation';

                        // Check if this is a select control
                        if (optionData.type === 'select' && optionData.options) {
                            // Convert options object to array for SelectControl
                            var selectOptions = [];
                            for (var key in optionData.options) {
                                if (optionData.options.hasOwnProperty(key)) {
                                    selectOptions.push({
                                        label: optionData.options[key],
                                        value: key
                                    });
                                }
                            }

                            var selectControl = el(SelectControl, {
                                key: opt,
                                label: optionData.label,
                                value: attributes[opt],
                                options: selectOptions,
                                onChange: function (value) {
                                    var newAttrs = {};
                                    newAttrs[opt] = value;
                                    setAttributes(newAttrs);
                                },
                                __next40pxDefaultSize: true,
                                __nextHasNoMarginBottom: true
                            });

                            controls.push(selectControl);
                            if (!excludeFromDefault) {
                                defaultTabControls.push(selectControl);
                            }
                        } else {
                            // Default to ToggleControl for boolean options
                            var toggleControl = el(ToggleControl, {
                                key: opt,
                                label: optionData.label,
                                checked: attributes[opt],
                                onChange: function (value) {
                                    var newAttrs = {};
                                    newAttrs[opt] = value;
                                    setAttributes(newAttrs);
                                },
                                __nextHasNoMarginBottom: true
                            });

                            controls.push(toggleControl);
                            if (!excludeFromDefault) {
                                defaultTabControls.push(toggleControl);
                            }
                        }
                    })(option);
                }
            }

            // Build tabs array
            var tabsArray = [];

            tabsArray.push({
                name: 'default',
                title: __('Default', 'language-switcher-for-divi-polylang'),
                className: 'lsdp-default-tab'
            });

            // Always add Styles tab
            tabsArray.push({
                name: 'styles',
                title: __('Styles', 'language-switcher-for-divi-polylang'),
                className: 'lsdp-styles-tab'
            });

            // Set initial tab
            var initialTab = 'default';
            var attributesKey = JSON.stringify(attributes);
            var dropdownRef = useRefEffect(function (element) {
                if (!element || attributes.dropdown !== 'dropdown') {
                    return undefined;
                }

                function initDropdowns() {
                    if (typeof window.lsdpInitDropdowns === 'function') {
                        window.lsdpInitDropdowns(element.ownerDocument);
                    }
                }

                initDropdowns();

                var observer = new MutationObserver(initDropdowns);
                observer.observe(element, { childList: true, subtree: true });

                return function () {
                    observer.disconnect();
                };
            }, [attributesKey]);
            var blockProps = useBlockProps({ ref: dropdownRef });

            return el(
                'div',
                blockProps,
                el(
                    BlockControls,
                    {},
                    AlignmentControl ? el(AlignmentControl, {
                        value: attributes.alignment || 'left',
                        onChange: function (nextAlign) {
                            var value = nextAlign || 'left';
                            if (value !== 'left' && value !== 'center' && value !== 'right') {
                                value = 'left';
                            }
                            setAttributes({ alignment: value });
                        }
                    }) : null
                ),
                el(
                    InspectorControls,
                    {},
                    el(
                        TabPanel,
                        {
                            className: 'lsdp-inspector-tabs',
                            activeClass: 'active-tab',
                            initialTabName: initialTab,
                            tabs: tabsArray
                        },
                        function (tab) {
                            // Helper function to render default language settings
                            var renderDefaultSettings = function() {
                                var customLanguages = attributes.customLanguages || [];
                                var selectedLanguages = customLanguages
                                    .map(function (l) { return l && l.language ? l.language : ''; })
                                    .filter(function (v) { return !!v; });

                                var repeaterItems = customLanguages.map(function(item, index) {
                                    var currentValue = item.language || '';

                                    var availableLanguageOptions = (settings.languages || []).filter(function (opt) {
                                        // Keep the currently selected option visible, but prevent selecting a language twice.
                                        if (!opt || !opt.value) return true;
                                        return opt.value === currentValue || selectedLanguages.indexOf(opt.value) === -1;
                                    });
                                    return el(
                                        'div',
                                        {
                                            key: index,
                                            style: {
                                                border: '1px solid #ddd',
                                                padding: '12px',
                                                marginBottom: '12px',
                                                borderRadius: '4px',
                                                backgroundColor: '#f9f9f9'
                                            }
                                        },
                                        el(SelectControl, {
                                            label: __('Language', 'language-switcher-for-divi-polylang'),
                                            value: item.language || '',
                                            options: [
                                                { label: __('Select Language', 'language-switcher-for-divi-polylang'), value: '' }
                                            ].concat(availableLanguageOptions),
                                            onChange: function(value) {
                                                // Prevent selecting the same language twice.
                                                var isDuplicate = value && customLanguages.some(function (l, idx) {
                                                    return idx !== index && l && l.language === value;
                                                });
                                                if (isDuplicate) {
                                                    setDuplicateLanguageNotice(__('This language is already added. Please choose another one.', 'language-switcher-for-divi-polylang'));
                                                    return;
                                                }
                                                setDuplicateLanguageNotice(null);
                                                var newLanguages = customLanguages.slice();
                                                newLanguages[index].language = value;
                                                setAttributes({ customLanguages: newLanguages });
                                            },
                                            __next40pxDefaultSize: true,
                                            __nextHasNoMarginBottom: true
                                        }),
                                        el(Button, {
                                            isDestructive: true,
                                            isSmall: true,
                                            onClick: function() {
                                                var newLanguages = customLanguages.slice();
                                                newLanguages.splice(index, 1);
                                                setAttributes({ customLanguages: newLanguages });
                                            },
                                            style: { marginTop: '8px' }
                                        }, __('Remove', 'language-switcher-for-divi-polylang'))
                                    );
                                });

                                return [
                                    el(
                                        PanelBody,
                                        {
                                            key: 'custom-languages',
                                            title: __('Custom Language Links', 'language-switcher-for-divi-polylang'),
                                            initialOpen: true
                                        },
                                        duplicateLanguageNotice && el(Notice, {
                                            status: 'error',
                                            isDismissible: true,
                                            onRemove: function () {
                                                setDuplicateLanguageNotice(null);
                                            },
                                            style: { marginBottom: '12px' }
                                        }, duplicateLanguageNotice),
                                        el('div', {}, repeaterItems),
                                        el(Button, {
                                            isPrimary: true,
                                            onClick: function() {
                                                setDuplicateLanguageNotice(null);
                                                var newLanguages = customLanguages.slice();
                                                newLanguages.push({ language: '' });
                                                setAttributes({ customLanguages: newLanguages });
                                            }
                                        }, __('Add Language', 'language-switcher-for-divi-polylang'))
                                    ),
                                    el(
                                        PanelBody,
                                        {
                                            key: 'display-settings',
                                            title: __('Display Settings', 'language-switcher-for-divi-polylang'),
                                            initialOpen: true
                                        },
                                        defaultTabControls
                                    )
                                ];
                            };

                            // Helper function to render Polylang settings
                            var renderPolylangSettings = function() {
                                return el(
                                    PanelBody,
                                    {
                                        title: __('Language Switcher Settings', 'language-switcher-for-divi-polylang'),
                                        initialOpen: true
                                    },
                                    controls
                                );
                            };

                            if (tab.name === 'default') {
                                return settings.polylangActive ? renderPolylangSettings() : renderDefaultSettings();
                            }

                            if (tab.name === 'styles') {
                                var stylePanels = [
                                    el(
                                        PanelBody,
                                        {
                                            key: 'typography',
                                            title: __('Typography', 'language-switcher-for-divi-polylang'),
                                            initialOpen: true
                                        },
                                        createTypographyControls()
                                    ),
                                    el(
                                        PanelBody,
                                        {
                                            key: 'spacing',
                                            title: __('Spacing', 'language-switcher-for-divi-polylang'),
                                            initialOpen: false
                                        },
                                        createSpacingControl(__('Margin', 'language-switcher-for-divi-polylang'), 'margin'),
                                        createSpacingControl(__('Padding', 'language-switcher-for-divi-polylang'), 'padding')
                                    ),
                                    el(
                                        PanelBody,
                                        {
                                            key: 'border',
                                            title: __('Border', 'language-switcher-for-divi-polylang'),
                                            initialOpen: false
                                        },
                                        createBorderControls()
                                    )
                                ];

                                // Add Flag panel only if show_flags is enabled
                                if (attributes.show_flags) {
                                    stylePanels.push(
                                        el(
                                            PanelBody,
                                            {
                                                key: 'flag',
                                                title: __('Flag', 'language-switcher-for-divi-polylang'),
                                                initialOpen: false
                                            },
                                            createFlagControls()
                                        )
                                    );
                                }

                                return stylePanels;
                            }
                        }
                    )
                ),
                el(ServerSideRender, {
                    block: 'lsdp/language-switcher',
                    attributes: attributes
                })
            );
        },

        save: function () {
            // Server-side rendering, so return null
            return null;
        }
    });

})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.compose,
    window.wp.i18n,
    window.wp.serverSideRender
);

