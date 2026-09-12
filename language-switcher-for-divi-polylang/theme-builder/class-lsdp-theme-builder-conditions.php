<?php
/**
 * Divi Theme Builder display conditions for Polylang languages.
 *
 * Adds a "Languages (Polylang)" group to Theme Builder template rules
 * ("Use on" / "Exclude from") through `et_theme_builder_template_settings_options`.
 *
 * Compatible with Divi 4 and Divi 5.
 *
 * @package Language_Switcher_For_Divi_Polylang
 * @since   1.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Registers Polylang language conditions in the Divi Theme Builder UI.
 */
final class LSDP_Theme_Builder_Conditions {

	/**
	 * Option group key in Divi's template settings array.
	 */
	const GROUP_ID = 'polylang_languages';

	/**
	 * Condition ID prefix. Divi splits IDs on `:` and passes the parts to validate.
	 */
	const CONDITION_PREFIX = 'language:polylang:';

	/**
	 * Condition priority relative to Divi's built-in rules.
	 *
	 * Higher than "All pages/posts" (70) so language-specific templates win,
	 * lower than specific term (80) and specific post (100).
	 */
	const CONDITION_PRIORITY = 75;

	/**
	 * Validate callback passed to Divi Theme Builder.
	 *
	 * @return array{0: class-string, 1: string}
	 */
	private static function get_validate_callback() {
		return array( __CLASS__, 'validate_polylang_language' );
	}

	/**
	 * Hooks Theme Builder filters.
	 */
	public function __construct() {
		add_filter( 'et_theme_builder_template_settings_options', array( $this, 'add_polylang_options' ) );
	}

	/**
	 * Appends a Polylang language group to Theme Builder condition options.
	 *
	 * @param array $options Existing condition groups keyed by group ID.
	 * @return array
	 */
	public function add_polylang_options( $options ) {
		if ( ! function_exists( 'pll_languages_list' ) ) {
			return $options;
		}

		$languages = pll_languages_list( array( 'fields' => '' ) );
		if ( empty( $languages ) || ! is_array( $languages ) ) {
			return $options;
		}

		$settings = array();

		foreach ( $languages as $language ) {
			if ( ! is_object( $language ) || ! isset( $language->slug, $language->name ) ) {
				continue;
			}

			$settings[] = array(
				'id'       => self::CONDITION_PREFIX . sanitize_key( $language->slug ),
				'label'    => esc_html( $language->name ),
				'priority' => self::CONDITION_PRIORITY,
				'validate' => self::get_validate_callback(),
			);
		}

		if ( empty( $settings ) ) {
			return $options;
		}

		$options[ self::GROUP_ID ] = array(
			'label'    => esc_html__( 'Languages (Polylang)', 'language-switcher-for-divi-polylang' ),
			'settings' => $settings,
		);

		return $options;
	}

	/**
	 * Checks whether the current request matches a Polylang language condition.
	 *
	 * Invoked by Divi via call_user_func() when a template uses a language rule.
	 *
	 * @param string $type    Request type reported by Divi (unused; signature is fixed).
	 * @param string $subtype Post type or taxonomy slug (unused; signature is fixed).
	 * @param int    $id      Post or term ID for the current request, when available.
	 * @param array  $setting Condition ID parts from Divi (exploded on `:`). Index 2 is the language slug.
	 * @return bool
	 */
	public static function validate_polylang_language( $type, $subtype, $id, $setting ) {
		unset( $type, $subtype );

		if ( ! is_array( $setting ) || empty( $setting[2] ) || ! is_string( $setting[2] ) ) {
			return false;
		}

		$expected_slug = sanitize_key( $setting[2] );
		if ( '' === $expected_slug ) {
			return false;
		}

		$current_slug = self::get_request_language( (int) $id );

		return '' !== $current_slug && $current_slug === $expected_slug;
	}

	/**
	 * Resolves the Polylang language for the current Theme Builder request.
	 *
	 * Object metadata is preferred over pll_current_language() so templates stay
	 * correct on subdomain setups and when language cookies are stale.
	 *
	 * @param int $object_id Post or term ID from Divi's condition checker.
	 * @return string Language slug, or empty string when unknown.
	 */
	private static function get_request_language( $object_id ) {
		if ( $object_id > 0 && function_exists( 'pll_get_post_language' ) ) {
			$post_language = pll_get_post_language( $object_id );
			if ( ! empty( $post_language ) ) {
				return $post_language;
			}
		}

		if ( $object_id > 0 && function_exists( 'pll_get_term_language' ) ) {
			$term_language = pll_get_term_language( $object_id );
			if ( ! empty( $term_language ) ) {
				return $term_language;
			}
		}

		if ( ! function_exists( 'pll_current_language' ) ) {
			return '';
		}

		$current_language = pll_current_language();

		return ! empty( $current_language ) ? $current_language : '';
	}
}