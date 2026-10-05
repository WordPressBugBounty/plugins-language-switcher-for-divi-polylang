<?php
/**
 * Shared Translation Inspector promo markup and script data.
 *
 * @package Language_Switcher_For_Elementor_Polylang
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build Translation Inspector promo HTML.
 *
 * @param string $context Context slug (get_started|floating_switcher).
 * @return string
 */
function lsdp_get_inspector_promo_html( $context = 'get_started' ) {
	$context      = sanitize_key( $context );
	$status       = class_exists( 'LSDP_Common_Helpers' ) ? LSDP_Common_Helpers::get_inspector_status() : array(
		'installed' => false,
		'active'    => false,
	);
	$is_active    = ! empty( $status['active'] );
	$is_installed = ! empty( $status['installed'] );
	$image_url    = LSDP_URL . 'admin/dashboard/assets/images/toolkit-for-polylang-logo.svg';
	$docs_url     = 'https://wordpress.org/plugins/duplicate-content-addon-for-polylang/';
	$settings_url = admin_url( 'admin.php?page=translation-inspector-polylang' );

	if ( $is_active ) {
		$button_text = __( 'Go to Settings', 'language-switcher-for-divi-polylang' );
	} elseif ( $is_installed ) {
		$button_text = __( 'Activate', 'language-switcher-for-divi-polylang' );
	} else {
		$button_text = __( 'Install', 'language-switcher-for-divi-polylang' );
	}

	ob_start();
	?>
	<div class="lsdp-promo-box lsdp-promo-box-floating-switcher lsdp-inspector-promo">
		<div class="lsdp-promo-main">
			<div class="lsdp-promo-image-section">
				<img
					class="lsdp-promo-image"
					src="<?php echo esc_url( $image_url ); ?>"
					alt="<?php echo esc_attr__( 'Translation Inspector logo', 'language-switcher-for-divi-polylang' ); ?>"
				/>
			</div>
			<div class="lsdp-promo-text-section">
				<div class="lsdp-promo-header-row">
					<strong class="lsdp-promo-title"><?php esc_html_e( 'Toolkit for Polylang - Translation Inspector & Duplicate Content', 'language-switcher-for-divi-polylang' ); ?></strong>
				</div>
				<p class="lsdp-promo-subtitle">
					<?php esc_html_e( 'Duplicate your pages/posts and inspect translations across Polylang languages.', 'language-switcher-for-divi-polylang' ); ?>
				</p>
			</div>
		</div>
		<div class="lsdp-promo-actions">
			<?php if ( $is_active ) : ?>
				<a
					href="<?php echo esc_url( $settings_url ); ?>"
					class="button button-primary lsdp-promo-button"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php echo esc_html( $button_text ); ?>
				</a>
			<?php else : ?>
				<button
					type="button"
					class="button button-primary lsdp-promo-button lsdp-inspector-action-btn"
					data-context="<?php echo esc_attr( $context ); ?>"
				>
					<?php echo esc_html( $button_text ); ?>
				</button>
			<?php endif; ?>
			<a
				href="<?php echo esc_url( $docs_url ); ?>"
				class="button button-secondary lsdp-promo-button-secondary"
				target="_blank"
				rel="noopener noreferrer"
			>
				<?php esc_html_e( 'View Docs', 'language-switcher-for-divi-polylang' ); ?>
				<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
			</a>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Echo Translation Inspector promo HTML.
 *
 * @param string $context Context slug.
 */
function lsdp_render_inspector_promo( $context = 'get_started' ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup is escaped inside lsdp_get_inspector_promo_html().
	echo lsdp_get_inspector_promo_html( $context );
}

/**
 * Localized data for the Inspector install script.
 *
 * @return array
 */
function lsdp_get_inspector_promo_script_data() {
	return array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'installNonce' => wp_create_nonce( 'lsdp_install_inspector' ),
		'settingsUrl'  => admin_url( 'admin.php?page=translation-inspector-polylang' ),
		'i18n'         => array(
			'installing'   => __( 'Installing...', 'language-switcher-for-divi-polylang' ),
			'activating'   => __( 'Activating...', 'language-switcher-for-divi-polylang' ),
			'goToSettings' => __( 'Go to Settings', 'language-switcher-for-divi-polylang' ),
			'installOk'    => __( 'Plugin installed and activated successfully!', 'language-switcher-for-divi-polylang' ),
			'installFail'  => __( 'Failed to install plugin. Please try again.', 'language-switcher-for-divi-polylang' ),
			'networkError' => __( 'Network error. Please check your connection and try again.', 'language-switcher-for-divi-polylang' ),
		),
	);
}

/**
 * Enqueue shared Inspector promo install script.
 *
 * @param array $deps Script dependencies.
 */
function lsdp_enqueue_inspector_promo_script( $deps = array() ) {
	$handle = 'lsdp-inspector-promo';

	wp_enqueue_script(
		$handle,
		LSDP_URL . 'admin/dashboard/assets/js/inspector-promo.js',
		$deps,
		LSDP,
		true
	);

	wp_localize_script( $handle, 'lsdpInspectorPromo', lsdp_get_inspector_promo_script_data() );
}
