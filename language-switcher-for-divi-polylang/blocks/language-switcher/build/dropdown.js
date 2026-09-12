/**
 * Custom Dropdown functionality for Language Switcher Block
 *
 * Editor and frontend share the same width logic: measure the widest
 * language row, then set --switcher-width. Remeasure after fonts/flags
 * so the editor matches the frontend.
 */
(function() {
    'use strict';

    function isInEditor(doc) {
        return doc.body && (
            doc.body.classList.contains('block-editor-iframe__body') ||
            doc.body.classList.contains('block-editor-page') ||
            doc.body.classList.contains('wp-admin')
        );
    }

    function copyFlagBoxSize(source, clone, doc) {
        if (!source || !clone || !source.classList || !source.classList.contains('lsdp-lang-image')) {
            return;
        }

        var view = doc.defaultView;
        if (!view) {
            return;
        }

        var cs = view.getComputedStyle(source);
        var width = cs.width;
        var height = cs.height;

        // Fall back to rendered box when computed size is not usable yet.
        if (!width || width === '0px' || width === 'auto') {
            var rect = source.getBoundingClientRect();
            if (rect.width > 0) {
                width = rect.width + 'px';
                height = rect.height + 'px';
            }
        }

        if (width && width !== '0px') {
            clone.style.width = width;
            clone.style.height = height && height !== '0px' ? height : width;
            clone.style.flexShrink = '0';
            clone.style.overflow = 'hidden';
        }

        var sourceImg = source.querySelector('img');
        var cloneImg = clone.querySelector('img');
        if (sourceImg && cloneImg) {
            cloneImg.style.display = 'block';
            cloneImg.style.width = '100%';
            cloneImg.style.height = '100%';
            cloneImg.style.objectFit = 'cover';
        }
    }

    function measureRowWidth(row, styles, doc, skipSelector) {
        var measurer = doc.createElement('div');
        measurer.style.cssText =
            'position:absolute;visibility:hidden;white-space:nowrap;display:flex;align-items:center;left:-9999px;top:0;';

        if (styles) {
            measurer.style.fontSize = styles.fontSize;
            measurer.style.fontFamily = styles.fontFamily;
            measurer.style.fontWeight = styles.fontWeight;
            measurer.style.gap = styles.gap || '0.5em';
            measurer.style.lineHeight = styles.lineHeight || '1';
            measurer.style.textTransform = styles.textTransform || 'none';
            measurer.style.letterSpacing = styles.letterSpacing || 'normal';
            measurer.style.fontStyle = styles.fontStyle || 'normal';
        }

        doc.body.appendChild(measurer);

        var childNodes = row.childNodes;
        for (var i = 0; i < childNodes.length; i++) {
            if (childNodes[i].nodeType !== 1) {
                continue;
            }
            if (skipSelector && childNodes[i].matches && childNodes[i].matches(skipSelector)) {
                continue;
            }

            var clone = childNodes[i].cloneNode(true);
            copyFlagBoxSize(childNodes[i], clone, doc);
            measurer.appendChild(clone);
        }

        var width = measurer.offsetWidth;
        doc.body.removeChild(measurer);
        return width;
    }

    function setFixedWidth(container) {
        var button = container.querySelector('.lsdp-dropdown-button');
        var menu = container.querySelector('.lsdp-dropdown-menu');
        var arrow = button ? button.querySelector('.lsdp-dropdown-arrow') : null;
        var doc = container.ownerDocument;

        if (!button || !doc || !doc.body) {
            return;
        }

        try {
            // Clear previous width so remeasures are not influenced by a stale value.
            container.style.removeProperty('--switcher-width');

            var buttonStyles = doc.defaultView.getComputedStyle(button);

            // Language content only (no arrow) — longest label wins.
            var maxLangWidth = measureRowWidth(button, buttonStyles, doc, '.lsdp-dropdown-arrow');

            if (menu) {
                var itemLinks = menu.querySelectorAll('.lsdp-dropdown-item a');
                for (var i = 0; i < itemLinks.length; i++) {
                    var itemContentWidth = measureRowWidth(itemLinks[i], buttonStyles, doc);
                    if (itemContentWidth > maxLangWidth) {
                        maxLangWidth = itemContentWidth;
                    }
                }
            }

            // Arrow stays inside that width (margin-left:auto). Only grow if the
            // selected label + arrow is wider than the longest language alone.
            if (arrow) {
                var gap = parseFloat(buttonStyles.gap) || 0;
                // Do not use computed margin-left — `auto` expands to free space
                // on a full-width button and would inflate the measured width.
                var selectedOnly = measureRowWidth(button, buttonStyles, doc, '.lsdp-dropdown-arrow');
                var selectedWithArrow = selectedOnly + gap + arrow.offsetWidth;
                if (selectedWithArrow > maxLangWidth) {
                    maxLangWidth = selectedWithArrow;
                }
            }

            var containerStyles = doc.defaultView.getComputedStyle(container);
            var horizontalExtras =
                (parseFloat(containerStyles.paddingLeft) || 0) +
                (parseFloat(containerStyles.paddingRight) || 0) +
                (parseFloat(containerStyles.borderLeftWidth) || 0) +
                (parseFloat(containerStyles.borderRightWidth) || 0);

            var total = maxLangWidth + horizontalExtras;

            if (total > 0) {
                container.style.setProperty('--switcher-width', Math.ceil(total) + 'px');
            }
        } catch (e) {
            // Keep CSS fallback width when measurement fails.
        }
    }

    function bindRemeasureOnAssets(container, doc) {
        var flags = container.querySelectorAll('img');
        for (var i = 0; i < flags.length; i++) {
            // Re-bind when the editor re-renders new <img> nodes.
            if (!flags[i].complete || flags[i].naturalWidth === 0) {
                flags[i].addEventListener('load', function () {
                    setFixedWidth(container);
                }, { once: true });
            }
        }

        if (container.hasAttribute('data-lsdp-fonts-bound')) {
            return;
        }
        container.setAttribute('data-lsdp-fonts-bound', 'true');

        if (doc.defaultView && doc.defaultView.document && doc.defaultView.document.fonts && doc.defaultView.document.fonts.ready) {
            doc.defaultView.document.fonts.ready.then(function () {
                setFixedWidth(container);
            });
        }
    }

    function scheduleEditorRemeasure(doc) {
        if (!doc.defaultView || !isInEditor(doc)) {
            return;
        }

        var view = doc.defaultView;

        // Debounce — editor MutationObserver can call init repeatedly.
        if (doc._lsdpEditorRemeasureTimer) {
            view.clearTimeout(doc._lsdpEditorRemeasureTimer);
        }

        // Editor injects flags/styles a frame or two after HTML lands.
        view.requestAnimationFrame(function () {
            view.requestAnimationFrame(function () {
                doc.querySelectorAll('.lsdp-dropdown-container').forEach(function (dropdown) {
                    setFixedWidth(dropdown);
                    bindRemeasureOnAssets(dropdown, doc);
                });
            });
        });

        doc._lsdpEditorRemeasureTimer = view.setTimeout(function () {
            doc._lsdpEditorRemeasureTimer = null;
            doc.querySelectorAll('.lsdp-dropdown-container').forEach(function (dropdown) {
                setFixedWidth(dropdown);
            });
        }, 150);
    }

    function initDropdown(container) {
        var button = container.querySelector('.lsdp-dropdown-button');
        var menu = container.querySelector('.lsdp-dropdown-menu');
        var doc = container.ownerDocument;

        if (!button || !menu || !doc) {
            return;
        }

        var inEditor = isInEditor(doc);

        function closeDropdown() {
            button.setAttribute('aria-expanded', 'false');
            menu.style.display = 'none';
        }

        function openDropdown() {
            button.setAttribute('aria-expanded', 'true');
            menu.style.display = 'block';
        }

        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var isExpanded = button.getAttribute('aria-expanded') === 'true';
            isExpanded ? closeDropdown() : openDropdown();
        });

        container.addEventListener('mouseenter', openDropdown);
        container.addEventListener('mouseleave', closeDropdown);

        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                e.preventDefault();
                openDropdown();
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        menu.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeDropdown();
                button.focus();
            } else if (e.key === 'Tab') {
                closeDropdown();
            }
        });

        if (inEditor) {
            menu.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeDropdown();
                });
            });
        }

        doc.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });

        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    function initAllDropdowns(doc) {
        doc = doc || document;

        doc.querySelectorAll('.lsdp-dropdown-container').forEach(function(dropdown) {
            setFixedWidth(dropdown);
            bindRemeasureOnAssets(dropdown, doc);

            if (!dropdown.hasAttribute('data-lsdp-initialized')) {
                dropdown.setAttribute('data-lsdp-initialized', 'true');
                initDropdown(dropdown);
            }
        });

        scheduleEditorRemeasure(doc);
    }

    window.lsdpInitDropdowns = initAllDropdowns;

    function boot(doc) {
        initAllDropdowns(doc);

        if (doc.defaultView && !isInEditor(doc)) {
            doc.defaultView.addEventListener('load', function () {
                initAllDropdowns(doc);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            boot(document);
        });
    } else {
        boot(document);
    }
})();
