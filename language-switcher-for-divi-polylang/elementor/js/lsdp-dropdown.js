/**
 * Elementor dropdown width — same idea as the block switcher:
 * measure the widest language row, set --lsep-switcher-width so the
 * absolute menu and trigger share one width without pushing page content.
 */
(function () {
	'use strict';

	function setSwitcherWidth(wrapper) {
		var activeLink = wrapper.querySelector('.lsep-active-language a');
		var list = wrapper.querySelector('.lsep-language-list');

		if (!activeLink || !list) {
			return;
		}

		try {
			var maxWidth = Math.max(activeLink.scrollWidth, activeLink.offsetWidth);
			var itemLinks = list.querySelectorAll('a');

			// Let items size to their content while measuring (list is normally width:100%).
			var prevWidth = list.style.width;
			var prevMinWidth = list.style.minWidth;
			var prevVisibility = list.style.visibility;
			var prevOpacity = list.style.opacity;

			list.style.width = 'max-content';
			list.style.minWidth = 'max-content';
			list.style.visibility = 'hidden';
			list.style.opacity = '1';

			for (var i = 0; i < itemLinks.length; i++) {
				var itemWidth = Math.max(itemLinks[i].scrollWidth, itemLinks[i].offsetWidth);
				if (itemWidth > maxWidth) {
					maxWidth = itemWidth;
				}
			}

			list.style.width = prevWidth;
			list.style.minWidth = prevMinWidth;
			list.style.visibility = prevVisibility;
			list.style.opacity = prevOpacity;

			var wrapperStyles = window.getComputedStyle(wrapper);
			var horizontalExtras =
				(parseFloat(wrapperStyles.paddingLeft) || 0) +
				(parseFloat(wrapperStyles.paddingRight) || 0) +
				(parseFloat(wrapperStyles.borderLeftWidth) || 0) +
				(parseFloat(wrapperStyles.borderRightWidth) || 0);

			if (maxWidth > 0) {
				wrapper.style.setProperty(
					'--lsep-switcher-width',
					Math.ceil(maxWidth + horizontalExtras) + 'px'
				);
			}
		} catch (e) {
			// Keep CSS fallback width when measurement fails.
		}
	}

	function initAll() {
		document.querySelectorAll('.lsep-wrapper.dropdown').forEach(function (wrapper) {
			setSwitcherWidth(wrapper);
		});
	}

	window.lsepInitDropdownWidths = initAll;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}

	// Elementor frontend / preview re-renders widgets without a full reload.
	if (typeof jQuery !== 'undefined') {
		jQuery(window).on('elementor/frontend/init', function () {
			if (window.elementorFrontend && elementorFrontend.hooks) {
				elementorFrontend.hooks.addAction(
					'frontend/element_ready/lsep_widget.default',
					function () {
						initAll();
					}
				);
			}
		});
	}
})();
