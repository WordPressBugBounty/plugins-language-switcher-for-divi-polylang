/**
 * Elementor dropdown width — same idea as the block switcher:
 * measure the widest language row, set --lsep-switcher-width so the
 * absolute menu and trigger share one width without pushing page content.
 * Also handles click-to-open when data-lsdp-open-on="click".
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

	function shouldOpenOnClick(wrapper) {
		return (
			wrapper.classList.contains('lsep-open-on-click') ||
			wrapper.getAttribute('data-lsdp-open-on') === 'click'
		);
	}

	function bindClickOpen(wrapper) {
		if (!shouldOpenOnClick(wrapper) || wrapper.hasAttribute('data-lsdp-click-initialized')) {
			return;
		}

		var trigger = wrapper.querySelector('.lsep-active-language');
		if (!trigger) {
			return;
		}

		wrapper.setAttribute('data-lsdp-click-initialized', 'true');

		trigger.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			wrapper.classList.toggle('active');
		});
	}

	function initAll() {
		document.querySelectorAll('.lsep-wrapper.dropdown').forEach(function (wrapper) {
			setSwitcherWidth(wrapper);
			bindClickOpen(wrapper);
		});
	}

	window.lsepInitDropdownWidths = initAll;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}

	document.addEventListener('click', function (e) {
		document.querySelectorAll('.lsep-wrapper.dropdown.active').forEach(function (wrapper) {
			if (!shouldOpenOnClick(wrapper)) {
				return;
			}
			if (!wrapper.contains(e.target)) {
				wrapper.classList.remove('active');
			}
		});
	});

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
