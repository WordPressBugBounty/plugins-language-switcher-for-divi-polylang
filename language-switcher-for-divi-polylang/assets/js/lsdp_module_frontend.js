/**
 * Divi language switcher frontend behaviour:
 * - Raise parent row z-index for dropdown overflow.
 * - Click-to-open when data-lsdp-open-on="click".
 */
(function () {
	'use strict';

	function shouldOpenOnClick(wrapper) {
		return (
			wrapper.classList.contains('lsdp-open-on-click') ||
			wrapper.getAttribute('data-lsdp-open-on') === 'click'
		);
	}

	function bindClickOpen(wrapper) {
		if (!shouldOpenOnClick(wrapper) || wrapper.hasAttribute('data-lsdp-click-initialized')) {
			return;
		}

		wrapper.setAttribute('data-lsdp-click-initialized', 'true');

		// Bind on the whole wrapper so flag, name, code, and ::after arrow all toggle.
		wrapper.addEventListener('click', function (e) {
			// Let clicks on other languages navigate; do not toggle.
			if (e.target.closest('.lsdp-language-list')) {
				return;
			}
			e.preventDefault();
			e.stopPropagation();
			wrapper.classList.toggle('active');
		});
	}

	function raiseRowZIndex(wrapper) {
		var parentRow = wrapper.closest('.et_pb_row');
		if (parentRow) {
			parentRow.style.setProperty('z-index', '999');
		}
	}

	function initAll() {
		document.querySelectorAll('.lsdp-wrapper.dropdown').forEach(function (wrapper) {
			raiseRowZIndex(wrapper);
			bindClickOpen(wrapper);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}

	document.addEventListener('click', function (e) {
		document.querySelectorAll('.lsdp-wrapper.dropdown.active').forEach(function (wrapper) {
			if (!shouldOpenOnClick(wrapper)) {
				return;
			}
			if (!wrapper.contains(e.target)) {
				wrapper.classList.remove('active');
			}
		});
	});
})();
