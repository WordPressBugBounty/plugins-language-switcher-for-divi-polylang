/**
 * Shared AutoPoly promo install/activate handler.
 */
(function () {
	'use strict';

	var config = window.lsdpAutopolyPromo;
	if (!config) {
		return;
	}

	function showInstallMessage(promoBox, type, message) {
		if (!promoBox) {
			return;
		}

		var existing = promoBox.querySelector('.lsdp-install-message');
		if (existing) {
			existing.remove();
		}

		var messageDiv = document.createElement('div');
		messageDiv.className = 'lsdp-install-message lsdp-install-message--' + type;
		messageDiv.textContent = message;

		var actions = promoBox.querySelector('.lsdp-promo-actions');
		if (actions) {
			actions.insertAdjacentElement('afterend', messageDiv);
		} else {
			promoBox.appendChild(messageDiv);
		}
	}

	function handleInstallClick(button) {
		var originalText = button.textContent;
		var i18n = config.i18n || {};
		var isActivate = originalText.toLowerCase().indexOf('activate') !== -1;
		var processingText = isActivate
			? (i18n.activating || 'Activating...')
			: (i18n.installing || 'Installing...');
		var promoBox = button.closest('.lsdp-promo-box');

		button.textContent = processingText;
		button.disabled = true;
		button.style.opacity = '0.6';
		button.style.cursor = 'not-allowed';

		function restoreButton() {
			button.textContent = originalText;
			button.disabled = false;
			button.style.opacity = '1';
			button.style.cursor = 'pointer';
		}

		var formData = new FormData();
		formData.append('action', 'lsdp_install_autopoly');
		formData.append('nonce', config.installNonce || '');

		fetch(config.ajaxUrl, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (data) {
				if (data.success) {
					var settingsLink = document.createElement('a');
					settingsLink.href = config.settingsUrl || 'admin.php?page=polylang-atfp-dashboard';
					settingsLink.className = 'button button-primary lsdp-promo-button';
					settingsLink.target = '_blank';
					settingsLink.rel = 'noopener noreferrer';
					settingsLink.textContent = i18n.goToSettings || 'Go to Settings';
					button.parentNode.replaceChild(settingsLink, button);
					showInstallMessage(
						promoBox,
						'success',
						(data.data && data.data.message) || i18n.installOk || ''
					);
				} else {
					restoreButton();
					showInstallMessage(
						promoBox,
						'error',
						(data.data && data.data.message) || i18n.installFail || ''
					);
				}
			})
			.catch(function () {
				restoreButton();
				showInstallMessage(promoBox, 'error', i18n.networkError || '');
			});
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('.lsdp-autopoly-action-btn');
		if (!button) {
			return;
		}
		event.preventDefault();
		handleInstallClick(button);
	});
})();
