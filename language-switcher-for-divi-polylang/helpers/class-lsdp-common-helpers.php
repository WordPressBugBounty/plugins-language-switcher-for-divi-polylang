<?php
/**
 * Language Switcher Polylang Elementor Helpers Class
 *
 * @package Language_Switcher_Polylang_Elementor
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LSDP_Common_Helpers
 *
 * Helper functions for Language Switcher Polylang Elementor plugin.
 *
 * @since 1.0.0
 */
class LSDP_Common_Helpers {
	/**
	 * Maximum number of languages supported in side-by-side switcher mode.
	 *
	 * @since 1.2.5
	 * @var int
	 */
	const SIDE_BY_SIDE_MAX_LANGUAGES = 3;

	/**
	 * Extract flag code from flag URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $flag_url The URL of the flag image.
	 * @return string|false The flag code if found, false otherwise.
	 */
	public static function get_flag_code( $flag_url ) {
		if ( empty( $flag_url ) || ! is_string( $flag_url ) ) {
			return false;
		}

		$flag_code = preg_match( '/polylang\/flags\/([a-z]+)\.(png|svg|jpg|jpeg)$/i', $flag_url, $matches ) ? $matches[1] : false;
		return $flag_code;
	}

	/**
	 * Get country flag HTML for a specific language.
	 *
	 * Accepts a Polylang flag URL or a plain country-code string (e.g. "us").
	 *
	 * @since 1.0.0
	 *
	 * @param string $flag_value Flag URL or country code.
	 * @param string $lang_name  Language name for alt text.
	 * @return string The HTML markup for the flag.
	 */
	public static function get_country_flag( $flag_value, $lang_name ) {

		if ( empty( $flag_value ) || empty( $lang_name ) ) {
			return '';
		}

		if ( false === strpos( $flag_value, '/' ) ) {
			$country_code = sanitize_file_name( $flag_value );
		} else {
			$country_code = self::get_flag_code( $flag_value );
		}

		if ( empty( $country_code ) ) {
			if ( class_exists( 'PLL_Language' ) && method_exists( 'PLL_Language', 'get_flag_html' ) ) {
				$flag = array( 'src' => $flag_value );
				return \PLL_Language::get_flag_html( $flag, '', $lang_name );
			}
			return '';
		}

		$flag_url  = LSDP_URL . 'assets/flags/' . $country_code . '.svg';
		$flag_path = LSDP_DIR . 'assets/flags/' . $country_code . '.svg';

		if ( class_exists( 'PLL_Language' ) && method_exists( 'PLL_Language', 'get_flag_html' ) ) {
			$flag = array(
				'path' => $flag_path,
				'url'  => esc_url( $flag_url ),
				'src'  => esc_url( $flag_url ),
			);
			return \PLL_Language::get_flag_html( $flag, '', $lang_name );
		}

		return sprintf(
			'<img src="%s" alt="%s" loading="lazy" />',
			esc_url( $flag_url ),
			esc_attr( $lang_name )
		);
	}

	/**
	 * Convert Polylang PNG flag URL to plugin's SVG flag URL.
	 * Consolidated method to avoid duplication across frontend and admin.
	 *
	 * @since 1.2.4
	 *
	 * @param string $polylang_flag_url Polylang flag URL.
	 * @return string Plugin's SVG flag URL or original if not found.
	 */
	public static function get_plugin_flag_url( $polylang_flag_url ) {
		if ( empty( $polylang_flag_url ) ) {
			return '';
		}

		if ( false === strpos( $polylang_flag_url, '/' ) ) {
			return LSDP_URL . 'assets/flags/' . sanitize_file_name( $polylang_flag_url ) . '.svg';
		}

		$flag_code = self::get_flag_code( $polylang_flag_url );
		if ( empty( $flag_code ) ) {
			return $polylang_flag_url;
		}

		return LSDP_URL . 'assets/flags/' . $flag_code . '.svg';
	}

	/**
	 * Get Polylang languages as a normalized array.
	 *
	 * @since 1.2.5
	 * @return array[] Each item contains slug, name, flag_url, and locale.
	 */
	public static function get_polylang_languages() {
		if ( ! function_exists( 'pll_languages_list' ) ) {
			return array();
		}

		$pll_languages = pll_languages_list( array( 'fields' => false ) );
		if ( empty( $pll_languages ) ) {
			return array();
		}

		$languages = array();
		foreach ( $pll_languages as $lang ) {
			$languages[] = array(
				'slug'     => $lang->slug,
				'name'     => $lang->name,
				'flag_url' => $lang->flag_url ?? '',
				'locale'   => $lang->locale,
			);
		}

		return $languages;
	}

	/**
	 * Get default languages when Polylang is unavailable.
	 *
	 * @since 1.2.5
	 * @return array[] Normalized language items.
	 */
	public static function get_default_languages() {
		return array(
			array(
				'slug'     => 'en',
				'name'     => __( 'English', 'language-switcher-for-divi-polylang' ),
				'flag_url' => 'us',
				'locale'   => 'en_US',
			),
			array(
				'slug'     => 'fr',
				'name'     => __( 'Francais', 'language-switcher-for-divi-polylang' ),
				'flag_url' => 'fr',
				'locale'   => 'fr_FR',
			),
		);
	}

	/**
	 * Get Polylang languages, falling back to defaults when needed.
	 *
	 * @since 1.2.5
	 * @return array[] Normalized language items.
	 */
	public static function get_polylang_languages_with_fallback() {
		$languages = self::get_polylang_languages();

		return ! empty( $languages ) ? $languages : self::get_default_languages();
	}

	/**
	 * Get languages for admin UIs (floating switcher app).
	 *
	 * @since 1.2.5
	 * @return array[] Each item contains code, name, flag, and locale.
	 */
	public static function get_polylang_languages_for_admin() {
		$languages = array();

		foreach ( self::get_polylang_languages_with_fallback() as $language ) {
			$languages[] = array(
				'code'   => $language['slug'],
				'name'   => $language['name'],
				'flag'   => self::get_plugin_flag_url( $language['flag_url'] ),
				'locale' => $language['locale'],
			);
		}

		return $languages;
	}

	/**
	 * Get languages for the floating switcher frontend.
	 *
	 * @since 1.2.5
	 * @param string $name_mode Display mode: full, short, or none.
	 * @return array[] Each item contains code, name, url, flag, and is_current.
	 */
	/**
	 * Get raw floater language data for responsive frontend rendering.
	 *
	 * @since 1.2.5
	 * @return array[] Each item contains code, name, url, flag, is_current, and pll raw data.
	 */
	public static function get_floater_languages_raw() {
		if ( ! function_exists( 'pll_the_languages' ) || ! function_exists( 'pll_current_language' ) ) {
			return array();
		}

		$current_lang  = pll_current_language();
		$raw_languages = pll_the_languages(
			array(
				'raw'           => 1,
				'hide_if_empty' => 0,
			)
		);

		if ( empty( $raw_languages ) ) {
			return array();
		}

		$languages = array();

		foreach ( $raw_languages as $lang ) {
			$lang_data = array(
				'code'       => $lang['slug'],
				'name'       => $lang['name'],
				'url'        => $lang['url'],
				'flag'       => self::get_plugin_flag_url( $lang['flag'] ?? '' ),
				'is_current' => $lang['slug'] === $current_lang,
				'pll'        => $lang,
			);

			if ( $lang_data['is_current'] ) {
				array_unshift( $languages, $lang_data );
			} else {
				$languages[] = $lang_data;
			}
		}

		return $languages;
	}

	/**
	 * Whether the given switcher type renders languages side by side.
	 *
	 * @since 1.2.5
	 * @param string $type Switcher type from saved config.
	 * @return bool
	 */
	public static function is_side_by_side_type( $type ) {
		return in_array( $type, array( 'inline', 'side-by-side' ), true );
	}

	/**
	 * Whether side-by-side mode is allowed for the current Polylang language count.
	 *
	 * @since 1.2.5
	 * @return bool
	 */
	public static function is_side_by_side_allowed() {
		return count( self::get_polylang_languages_with_fallback() ) <= self::SIDE_BY_SIDE_MAX_LANGUAGES;
	}

	/**
	 * Limit language list to the side-by-side maximum (current language stays first).
	 *
	 * @since 1.2.5
	 * @param array $languages Normalized language list.
	 * @return array
	 */
	public static function limit_languages_for_side_by_side( $languages ) {
		return array_slice( $languages, 0, self::SIDE_BY_SIDE_MAX_LANGUAGES );
	}

	/**
	 * Get a Polylang language by slug, with fallback defaults.
	 *
	 * @since 1.2.5
	 * @param string $slug Language slug.
	 * @return array|null Normalized language data or null when not found.
	 */
	public static function get_polylang_language_by_slug( $slug ) {
		foreach ( self::get_polylang_languages_with_fallback() as $language ) {
			if ( $language['slug'] === $slug ) {
				return $language;
			}
		}

		return null;
	}

	/**
	 * Get Polylang languages formatted for block editor select controls.
	 *
	 * @since 1.2.5
	 * @return array[] Select options with value and label keys.
	 */
	public static function get_polylang_language_select_options() {
		$options = array();

		foreach ( self::get_polylang_languages_with_fallback() as $language ) {
			$options[] = array(
				'value' => $language['slug'],
				'label' => $language['name'] . ' (' . $language['slug'] . ')',
			);
		}

		return $options;
	}

	/**
	 * Load plugin API functions when running outside wp-admin.
	 *
	 * @since 1.2.5
	 */
	private static function ensure_plugin_api_loaded() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
	}

	/**
	 * Check whether a plugin is active (safe on frontend requests).
	 *
	 * @since 1.2.5
	 * @param string $plugin_file Plugin basename.
	 * @return bool
	 */
	public static function lsdp_is_plugin_active( $plugin_file ) {
		self::ensure_plugin_api_loaded();

		return is_plugin_active( $plugin_file );
	}

	/**
	 * Check if required plugin dependencies are active.
	 * Consolidated check to avoid repetition.
	 *
	 * @since 1.2.4
	 *
	 * @return bool True if Polylang is active.
	 */
	public static function is_dependencies_active() {
		return function_exists( 'pll_the_languages' );
	}

	/**
		* Check whether Elementor is available before loading its integration.
		*
	 * @return bool
		*/
	public static function is_elementor_available() {

		return did_action( 'elementor/loaded' ) || class_exists( '\\Elementor\\Plugin' );
	}

	/**
	 * Whether the current request is a page-builder edit or preview context.
	 *
	 * @since 1.2.6
	 * @return bool
	 */
	public static function is_page_builder_preview() {
		if ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['et_fb'] ) || ! empty( $_GET['et_bfb'] ) || ! empty( $_GET['et_pb_preview'] ) ) {
			return true;
		}

		if ( class_exists( '\Elementor\Plugin' ) ) {
			$elementor = \Elementor\Plugin::$instance;
			if (
				( isset( $elementor->editor ) && $elementor->editor->is_edit_mode() )
				|| ( isset( $elementor->preview ) && $elementor->preview->is_preview_mode() )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get language name based on display mode.
	 * Consolidated method to format language names consistently.
	 *
	 * @since 1.2.4
	 *
	 * @param array  $lang Language data from Polylang.
	 * @param string $mode Display mode: 'full', 'short', or 'none'.
	 * @return string Formatted language name.
	 */
	public static function get_language_name( $lang, $mode ) {
		switch ( $mode ) {
			case 'full':
				return $lang['name'];
			case 'short':
				return strtoupper( $lang['slug'] );
			case 'none':
			default:
				return '';
		}
	}

	/**
	 * Get AutoPoly plugin file path.
	 *
	 * @since 1.2.4
	 * @return string Plugin file path.
	 */
	public static function get_autopoly_plugin_file() {
		return 'automatic-translations-for-polylang/automatic-translation-for-polylang.php';
	}

	/**
	 * Check AutoPoly plugin status.
	 *
	 * @since 1.2.4
	 * @return array Status with 'installed' and 'active' booleans.
	 */
	public static function get_autopoly_status() {
		self::ensure_plugin_api_loaded();

		$plugin_file = self::get_autopoly_plugin_file();
		$all_plugins = get_plugins();

		return array(
			'installed' => isset( $all_plugins[ $plugin_file ] ),
			'active'    => self::lsdp_is_plugin_active( $plugin_file ),
		);
	}
}
