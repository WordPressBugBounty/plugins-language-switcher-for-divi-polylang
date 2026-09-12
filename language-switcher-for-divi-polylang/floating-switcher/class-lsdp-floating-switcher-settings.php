<?php
/**
 * Floating Language Switcher Settings
 *
 * Handles the admin settings page for the floating language switcher feature.
 * Provides a comprehensive interface for configuring the floating switcher's
 * appearance, behavior, and layout settings.
 *
 * @package    Language_Switcher_For_Elementor_Polylang
 * @subpackage Language_Switcher_For_Elementor_Polylang/floating-switcher
 * @since      1.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * LSDP Floating Switcher Settings Class
 *
 * Manages the admin interface for floating language switcher configuration.
 * Implements singleton pattern to ensure only one instance exists.
 *
 * @since 1.2.4
 */
class LSDP_Floating_Switcher_Settings {

	/**
	 * Singleton instance
	 *
	 * @since 1.2.4
	 * @var LSDP_Floating_Switcher_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * Returns the single instance of this class, creating it if necessary.
	 *
	 * @since 1.2.4
	 * @return LSDP_Floating_Switcher_Settings The singleton instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * Private constructor to enforce singleton pattern.
	 * Registers WordPress hooks for admin menu, assets, and AJAX handlers.
	 *
	 * @since 1.2.4
	 */
	private function __construct() {

		// Enqueue admin assets (CSS/JS)
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Register AJAX handler for saving settings
		add_action( 'wp_ajax_lsdp_save_floating_switcher', array( $this, 'ajax_save_settings' ) );

		// Register AJAX handler for AutoPoly install/activate
		add_action( 'wp_ajax_lsdp_install_autopoly', array( $this, 'ajax_install_autopoly' ) );
	}

	/**
	 * Render Settings Page
	 *
	 * Outputs the HTML for the floating switcher settings page.
	 * Includes the React app root element and page template.
	 *
	 * @since 1.2.4
	 */
	public function render_page() {
		wp_safe_redirect( admin_url( 'admin.php?page=lsdp-get-started&tab=floating-switcher' ) );
		exit;
	}

	/**
	 * Enqueue Admin Assets
	 *
	 * Loads CSS and JavaScript files only on the floating switcher settings page.
	 * Includes WordPress React libraries and localized data for the app.
	 *
	 * @since 1.2.4
	 * @param string $hook The current admin page hook
	 */
	public function enqueue_assets( $hook ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for conditional asset loading.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for conditional asset loading.
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';

		$is_floating_tab = ( 'lsdp-get-started' === $page && 'floating-switcher' === $tab );
		$is_legacy_page  = ( strpos( $hook, 'lsdp-floating-switcher' ) !== false || 'lsdp-floating-switcher' === $page );

		if ( ! $is_floating_tab && ! $is_legacy_page ) {
			return;
		}

		$plugin_url = LSDP_URL;
		$version    = defined( 'LSDP' ) ? LSDP : '1.0.0';

		// Enqueue WordPress React libraries (required for the app)
		wp_enqueue_script( 'wp-element' );
		wp_enqueue_script( 'wp-i18n' );

		// Enqueue admin stylesheet
		wp_enqueue_style(
			'lsdp-floating-switcher-admin',
			$plugin_url . 'floating-switcher/css/lsdp-floating-switcher-admin.css',
			array(),
			$version
		);

		require_once LSDP_DIR . 'admin/dashboard/includes/autopoly-promo.php';
		lsdp_enqueue_autopoly_promo_script();

		// Enqueue React app JavaScript
		wp_enqueue_script(
			'lsdp-floating-switcher-app',
			$plugin_url . 'floating-switcher/js/lsdp-floating-switcher-app.js',
			array( 'wp-element', 'wp-i18n', 'lsdp-autopoly-promo' ),
			$version,
			true
		);

		// Pass configuration data and settings to the JavaScript app
		wp_localize_script(
			'lsdp-floating-switcher-app',
			'lsdpFloaterData',
			$this->get_localized_data()
		);

		// Set up script translations for internationalization
		wp_set_script_translations(
			'lsdp-floating-switcher-app',
			'language-switcher-for-divi-polylang',
			LSDP_DIR . 'languages'
		);
	}

	/**
	 * Get Localized Data
	 *
	 * Prepares data to be passed to the JavaScript app including
	 * configuration, languages, nonce, and plugin paths.
	 *
	 * @since 1.2.4
	 * @return array Array of data for JavaScript app
	 */
	private function get_localized_data() {
		// Get current configuration and available languages
		$config    = $this->get_switcher_config();
		$languages = LSDP_Common_Helpers::get_polylang_languages_for_admin();

		return array(
			'config'                  => $config,
			'languages'               => $languages,
			'sideBySideMaxLanguages'  => LSDP_Common_Helpers::SIDE_BY_SIDE_MAX_LANGUAGES,
			'nonce'             => wp_create_nonce( 'lsdp_floating_switcher_save' ),
			'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
			'pluginUrl'         => LSDP_URL,
			'autoPolyPromoHtml' => function_exists( 'lsdp_get_autopoly_promo_html' )
				? lsdp_get_autopoly_promo_html( 'floating_switcher' )
				: '',
		);
	}

	/**
	 * Get Switcher Configuration
	 *
	 * Retrieves the saved floating switcher configuration from the database.
	 * If no configuration exists or is invalid, returns and saves default configuration.
	 * Ensures all required fields exist by merging with defaults.
	 *
	 * @since 1.2.4
	 * @return array Complete switcher configuration array
	 */
	public function get_switcher_config() {
		// Get saved configuration from database
		$saved    = get_option( 'lsdp_floating_switcher_config', null );
		$defaults = $this->get_default_config();

		// If no saved config or invalid, return defaults without writing.
		if ( ! is_array( $saved ) || empty( $saved ) ) {
			return $defaults;
		}

		// Merge saved config with defaults to ensure all fields exist.
		$config = $this->deep_merge_defaults( $saved, $defaults );

		return $this->normalize_side_by_side_type( $config );
	}

	/**
	 * Fall back to dropdown when side-by-side is saved but too many languages exist.
	 *
	 * @since 1.2.5
	 * @param array $config Switcher configuration.
	 * @return array
	 */
	private function normalize_side_by_side_type( $config ) {
		if (
			LSDP_Common_Helpers::is_side_by_side_type( $config['type'] ?? '' )
			&& ! LSDP_Common_Helpers::is_side_by_side_allowed()
		) {
			$config['type'] = 'dropdown';
		}

		return $config;
	}

	/**
	 * Get Default Configuration
	 *
	 * Returns the default configuration array for the floating switcher
	 * with all settings set to their default values.
	 *
	 * @since 1.2.4
	 * @return array Default configuration array
	 */
	public function get_default_config() {
		// Device-specific layout defaults (desktop and mobile)
		$layout_defaults = array(
			'desktop' => array(
				'position'         => 'bottom-right',
				'width'            => 'default',
				'customWidth'      => 216,
				'padding'          => 'default',
				'customPadding'    => 0,
				'flagIconPosition' => 'before',
				'languageNames'    => 'full',
			),
			'mobile'  => array(
				'position'         => 'bottom-right',
				'width'            => 'default',
				'customWidth'      => 216,
				'padding'          => 'default',
				'customPadding'    => 0,
				'flagIconPosition' => 'before',
				'languageNames'    => 'full',
			),
		);

		return array(
			'enabled'           => false, // Switcher disabled by default
			'type'              => 'dropdown', // Dropdown mode by default
			'bgColor'           => '#ffffff', // Background color
			'bgHoverColor'      => '#0000000d', // Hover background color
			'textColor'         => '#143852', // Text color
			'textHoverColor'    => '#1d2327', // Hover text color
			'borderColor'       => '#1438521a', // Border color
			'borderWidth'       => 1, // Border width in pixels
			'borderRadius'      => array( 8, 8, 0, 0 ), // Border radius for each corner [TL, TR, BR, BL]
			'size'              => 'normal', // Font and flag size
			'flagShape'         => 'rect', // Flag aspect ratio
			'flagRadius'        => 2, // Flag border radius
			'enableCustomCss'   => true, // Allow custom CSS
			'customCss'         => '', // Custom CSS code
			'layoutCustomizer'  => $layout_defaults, // Device-specific layout settings
		);
	}

	/**
	 * Deep Merge with Defaults
	 *
	 * Recursively merges saved configuration with default values
	 * to ensure all required keys exist even if they were added in updates.
	 *
	 * @since 1.2.4
	 * @param array $saved    Saved configuration array
	 * @param array $defaults Default configuration array
	 * @return array Merged configuration array
	 */
	private function deep_merge_defaults( $saved, $defaults ) {
		// Loop through defaults and add missing keys
		foreach ( $defaults as $key => $default_value ) {
			if ( ! array_key_exists( $key, $saved ) ) {
				// Add missing key with default value
				$saved[ $key ] = $default_value;
			} elseif ( is_array( $default_value ) && is_array( $saved[ $key ] ) ) {
				// Recursively merge nested arrays
				$saved[ $key ] = $this->deep_merge_defaults( $saved[ $key ], $default_value );
			}
		}
		return $saved;
	}

	/**
	 * AJAX Handler: Save Settings
	 *
	 * Handles the AJAX request to save floating switcher configuration.
	 * Validates permissions, nonce, and data before saving to database.
	 *
	 * @since 1.2.4
	 */
	public function ajax_save_settings() {
		// Verify nonce for security
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lsdp_floating_switcher_save' ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'language-switcher-for-divi-polylang' ), 403 );
		}

		// Check user permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'language-switcher-for-divi-polylang' ), 403 );
		}

		// Get and decode configuration JSON
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON is decoded below and every supported field is sanitized by sanitize_config().
		$config_raw = isset( $_POST['config'] ) ? wp_unslash( $_POST['config'] ) : '{}';
		$config     = json_decode( $config_raw, true );

		// Validate JSON decode
		if ( ! is_array( $config ) ) {
			wp_send_json_error( __( 'Invalid data.', 'language-switcher-for-divi-polylang' ), 400 );
		}

		// Sanitize and validate configuration
		$sanitized = $this->sanitize_config( $config );

		// Save to database
		update_option( 'lsdp_floating_switcher_config', $sanitized );

		// Return success response
		wp_send_json_success( __( 'Settings saved successfully.', 'language-switcher-for-divi-polylang' ) );
	}

	/**
	 * AJAX Handler: Install and Activate AutoPoly Plugin
	 *
	 * @since 1.2.4
	 */
	public function ajax_install_autopoly() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'lsdp_install_autopoly' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid security token.', 'language-switcher-for-divi-polylang' ),
				)
			);
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Sorry, you are not allowed to install plugins on this site.', 'language-switcher-for-divi-polylang' ),
				)
			);
		}

		$plugin_slug = 'automatic-translations-for-polylang';

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $plugin_slug,
				'fields' => array( 'sections' => false ),
			)
		);

		if ( is_wp_error( $api ) ) {
			wp_send_json_error(
				array(
					'message' => $api->get_error_message(),
				)
			);
		}

		$install_status = install_plugin_install_status( $api );

		if ( 'install' === $install_status['status'] ) {
			ob_start();
			$skin     = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$result   = $upgrader->install( $api->download_link );
			ob_end_clean();

			if ( is_wp_error( $result ) ) {
				wp_send_json_error(
					array(
						'message' => $result->get_error_message(),
					)
				);
			}

			$install_status = install_plugin_install_status( $api );
		}

		if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {
			$activation_result = activate_plugin( $install_status['file'], '', false, true );

			if ( is_wp_error( $activation_result ) ) {
				wp_send_json_error(
					array(
						'message' => $activation_result->get_error_message(),
					)
				);
			}
		}

		if ( ! get_option( 'lsdp_autopoly_installed' ) ) {
			update_option( 'lsdp_autopoly_installed', 'installed_by_lsdp' );
		}

		wp_send_json_success(
			array(
				'message' => __( 'AutoPoly plugin installed and activated successfully!', 'language-switcher-for-divi-polylang' ),
			)
		);
	}

	/**
	 * Sanitize Configuration
	 *
	 * Sanitizes and validates all configuration fields to ensure
	 * data integrity and security before saving to database.
	 *
	 * @since 1.2.4
	 * @param array $config Raw configuration array from client
	 * @return array Sanitized and validated configuration array
	 */
	private function sanitize_config( $config ) {
		$sanitized = array();

		// Sanitize boolean fields (convert to true/false)
		$sanitized['enabled']           = ! empty( $config['enabled'] );
		$sanitized['enableCustomCss']   = ! empty( $config['enableCustomCss'] );

		// Validate and sanitize type field (dropdown, inline, or side-by-side).
		$sanitized['type'] = in_array( $config['type'] ?? '', array( 'dropdown', 'inline', 'side-by-side' ), true )
		? $config['type']
		: 'dropdown';

		if (
			LSDP_Common_Helpers::is_side_by_side_type( $sanitized['type'] )
			&& ! LSDP_Common_Helpers::is_side_by_side_allowed()
		) {
			$sanitized['type'] = 'dropdown';
		}

		// Sanitize color fields (supports hex with alpha or rgba)
		$color_fields = array( 'bgColor', 'bgHoverColor', 'textColor', 'textHoverColor', 'borderColor' );
		foreach ( $color_fields as $field ) {
			$sanitized[ $field ] = $this->sanitize_color( $config[ $field ] ?? '#ffffff' );
		}

		// Sanitize numeric fields (ensure positive integers)
		$sanitized['borderWidth'] = absint( $config['borderWidth'] ?? 1 );
		$sanitized['flagRadius']  = absint( $config['flagRadius'] ?? 2 );

		// Sanitize border radius array (4 values for each corner: TL, TR, BR, BL)
		if ( isset( $config['borderRadius'] ) && is_array( $config['borderRadius'] ) ) {
			$sanitized['borderRadius'] = array_map( 'absint', array_slice( $config['borderRadius'], 0, 4 ) );
			// Ensure we have exactly 4 values
			$sanitized['borderRadius'] = array_pad( $sanitized['borderRadius'], 4, 0 );
		} else {
			$sanitized['borderRadius'] = array( 8, 8, 0, 0 );
		}

		// Validate size field (normal or large)
		$sanitized['size'] = in_array( $config['size'] ?? '', array( 'normal', 'large' ), true )
		? $config['size']
		: 'normal';

		// Validate flag shape (rect, square, or rounded)
		$sanitized['flagShape'] = in_array( $config['flagShape'] ?? '', array( 'rect', 'square', 'rounded' ), true )
		? $config['flagShape']
		: 'rect';

		// Sanitize custom CSS (remove dangerous code while preserving CSS)
		$sanitized['customCss'] = '';
		if ( ! empty( $config['customCss'] ) ) {
			// Remove script tags and dangerous patterns for security
			$custom_css             = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $config['customCss'] );
			$custom_css             = preg_replace( '/<style\b[^>]*>.*?<\/style>/is', '', $custom_css );
			$custom_css             = wp_kses( $custom_css, array() ); // Strip all HTML tags
			$sanitized['customCss'] = sanitize_textarea_field( $custom_css );
		}

		// Sanitize layout customizer (device-specific settings)
		$default_layouts               = $this->get_default_config()['layoutCustomizer'];
		$sanitized['layoutCustomizer'] = array();

		// Process layout settings for each device (desktop and mobile)
		foreach ( array( 'desktop', 'mobile' ) as $device ) {
			if ( isset( $config['layoutCustomizer'][ $device ] ) && is_array( $config['layoutCustomizer'][ $device ] ) ) {
				$layout = $config['layoutCustomizer'][ $device ];

				// Validate position (e.g., 'bottom-right', 'top-left')
				$valid_positions                                      = array(
					'bottom-right',
					'bottom-left',
					'bottom-center',
					'top-right',
					'top-left',
					'top-center',
				);
				$sanitized['layoutCustomizer'][ $device ]['position'] = in_array( $layout['position'] ?? '', $valid_positions, true )
				? $layout['position']
				: 'bottom-right';

				// Validate width mode (default or custom)
				$sanitized['layoutCustomizer'][ $device ]['width']       = in_array( $layout['width'] ?? '', array( 'default', 'custom' ), true )
				? $layout['width']
				: 'default';
				$sanitized['layoutCustomizer'][ $device ]['customWidth'] = absint( $layout['customWidth'] ?? 216 );

				// Validate padding mode (default or custom)
				$sanitized['layoutCustomizer'][ $device ]['padding']       = in_array( $layout['padding'] ?? '', array( 'default', 'custom' ), true )
				? $layout['padding']
				: 'default';
				$sanitized['layoutCustomizer'][ $device ]['customPadding'] = absint( $layout['customPadding'] ?? 0 );

				// Validate flag icon position (before, after, or hide)
				$sanitized['layoutCustomizer'][ $device ]['flagIconPosition'] = in_array( $layout['flagIconPosition'] ?? '', array( 'before', 'after', 'hide' ), true )
				? $layout['flagIconPosition']
				: 'before';

				// Validate language names display mode (full, short, or none)
				$sanitized['layoutCustomizer'][ $device ]['languageNames'] = in_array( $layout['languageNames'] ?? '', array( 'full', 'short', 'none' ), true )
				? $layout['languageNames']
				: 'full';
			} else {
				// If device config missing or invalid, use defaults
				$sanitized['layoutCustomizer'][ $device ] = $default_layouts[ $device ];
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize Color Value
	 *
	 * Validates and sanitizes color values.
	 * Supports hex colors (with or without alpha) and rgba() format.
	 *
	 * @since 1.2.4
	 * @param string $color Color value to sanitize
	 * @return string Sanitized color value or default fallback
	 */
	private function sanitize_color( $color ) {
		// Remove whitespace from color value
		$color = trim( $color );
		// Allow 'transparent' keyword
		if ( strtolower( $color ) === 'transparent' ) {
			return 'transparent';
		}

		// Validate hex colors (#RGB, #RRGGBB, or #RRGGBBAA with alpha)
		if ( preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $color ) ) {
			return $color;
		}

		// Validate rgba() or rgb() color format
		if ( preg_match( '/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[\d.]+\s*)?\)$/', $color ) ) {
			return $color;
		}

		// Return default white if validation fails
		return '#ffffff';
	}
}
