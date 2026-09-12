<?php
/**
 * D4 shortcode → D5 on-the-fly conversion helpers.
 *
 * When Divi 5 is active the D4 module class (LSDP_Module) is intentionally
 * NOT loaded, so WordPress has no handler for [language-switcher-for-divi-polylang].
 * Pages created in Divi 4 therefore show the raw shortcode text on the frontend
 * until the editor resaves them (which writes the D5 block format to the DB).
 *
 * This file fixes that by registering:
 *  1. A WordPress shortcode handler that renders the switcher HTML directly,
 *     bridging old D4 content to the D5 render path.
 *  2. The Divi conversion pipeline filter so server-side attribute migration
 *     is properly wired up.
 *
 * @package LanguageSwitcherForDiviPolylang
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auto-incrementing instance counter for scoping inline styles.
 *
 * @var int
 */
$lsdp_d4_shortcode_instance = 0;

/**
 * Register a WordPress shortcode handler for the legacy D4 shortcode tag.
 *
 * This is the exact same approach the Timeline plugin uses: its D4 module
 * class is still registered via divi_extensions_init (which runs even in D5
 * mode), providing a shortcode handler automatically.  Because the language
 * switcher explicitly skips D4 class registration when Divi >= 5.0, we must
 * add the handler ourselves.
 *
 * The handler reads ALL D4 shortcode attributes (layout, visibility AND
 * styling such as colors, fonts, flag size, spacing) and renders full HTML
 * + a scoped <style> block so the page looks identical to the saved D5 version.
 *
 * @since 1.0.0
 */
add_action(
	'init',
	function () {
		// Only register when Divi 5 is active and we are NOT inside the
		// visual builder (the VB renders via the JS/React edit component).
		if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
			return;
		}
		if ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
			return;
		}

		add_shortcode(
			'language-switcher-for-divi-polylang',
			function ( $atts ) {
				global $lsdp_d4_shortcode_instance;
				$lsdp_d4_shortcode_instance++;
				$uid = 'lsdp-d4-' . $lsdp_d4_shortcode_instance;

				// ── Layout / visibility attrs ──────────────────────────────────
				$atts = shortcode_atts(
					array(
						// Layout & visibility
						'lsdp_style'                         => 'dropdown',
						'lsdp_flag_visibility'               => 'on',
						'lsdp_language_name_visibility'      => 'on',
						'lsdp_language_code_visibility'      => 'off',
						'lsdp_current_lang_visibility'       => 'off',
						'lsdp_unstranslated_lang_visibility' => 'off',
						// Flag styling
						'lsdp_flag_ratio'                    => '',
						'lsdp_flag_width'                    => '',
						'lsdp_flag_radius'                   => '',
						// Background
						'lsdp_bg_normal_color'               => '',
						// Text / font (Divi generates these from the font group)
						'lsdp_text_settings_text_color'      => '',
						'lsdp_text_settings_font_size'       => '',
						'lsdp_text_settings_letter_spacing'  => '',
						'lsdp_text_settings_line_height'     => '',
						// Spacing
						'custom_margin'                      => '',
						'custom_padding'                     => '',
					),
					$atts,
					'language-switcher-for-divi-polylang'
				);

				$props = array(
					'switcher_layouts'           => sanitize_html_class( $atts['lsdp_style'] ?: 'dropdown' ),
					'show_language_flag'         => $atts['lsdp_flag_visibility'],
					'show_language_name'         => $atts['lsdp_language_name_visibility'],
					'show_language_code'         => $atts['lsdp_language_code_visibility'],
					'hide_current_language'      => $atts['lsdp_current_lang_visibility'],
					'hide_untranslated_language' => $atts['lsdp_unstranslated_lang_visibility'],
				);

				if ( ! function_exists( 'pll_the_languages' ) || ! function_exists( 'pll_current_language' ) ) {
					return '';
				}

				// Use the D5 render layer (already loaded via autoload).
				if ( ! class_exists( '\LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModule' ) ) {
					return '';
				}

				$layout     = sanitize_html_class( $props['switcher_layouts'] ) ?: 'dropdown';
				$inner_html = \LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModule::render_content( $props );

				// ── Build scoped CSS from D4 styling attributes ────────────────
				$css_rules = array();
				$sel       = '#' . $uid; // scope every rule to this instance

				// Background color
				$bg = sanitize_hex_color( $atts['lsdp_bg_normal_color'] );
				if ( $bg ) {
					$css_rules[] = $sel . ' .lsdp-wrapper ul li a, '
								. $sel . ' .lsdp-wrapper.dropdown { background-color: ' . $bg . ' !important; }';
				}

				// Text color
				$text_color = sanitize_hex_color( $atts['lsdp_text_settings_text_color'] );
				if ( $text_color ) {
					$css_rules[] = $sel . ' .lsdp-wrapper ul li .lsdp-lang-name, '
								. $sel . ' .lsdp-wrapper ul li .lsdp-lang-code, '
								. $sel . ' .lsdp-wrapper.dropdown span .lsdp-lang-name, '
								. $sel . ' .lsdp-wrapper.dropdown span .lsdp-lang-code, '
								. $sel . ' .lsdp-wrapper ul li a { color: ' . $text_color . ' !important; }';
				}

				// Font size
				$font_size = sanitize_text_field( $atts['lsdp_text_settings_font_size'] );
				if ( $font_size && preg_match( '/^\d+(\.\d+)?(px|em|rem|%)$/', $font_size ) ) {
					$css_rules[] = $sel . ' .lsdp-wrapper ul li .lsdp-lang-name, '
								. $sel . ' .lsdp-wrapper ul li .lsdp-lang-code, '
								. $sel . ' .lsdp-wrapper ul li a { font-size: ' . $font_size . ' !important; }';
				}

				// Letter spacing
				$letter_spacing = sanitize_text_field( $atts['lsdp_text_settings_letter_spacing'] );
				if ( $letter_spacing && preg_match( '/^\d+(\.\d+)?(px|em|rem)$/', $letter_spacing ) ) {
					$css_rules[] = $sel . ' .lsdp-wrapper ul li .lsdp-lang-name, '
								. $sel . ' .lsdp-wrapper ul li .lsdp-lang-code { letter-spacing: ' . $letter_spacing . ' !important; }';
				}

				// Line height
				$line_height = sanitize_text_field( $atts['lsdp_text_settings_line_height'] );
				if ( $line_height && preg_match( '/^\d+(\.\d+)?(px|em|rem|)$/', $line_height ) ) {
					$css_rules[] = $sel . ' .lsdp-wrapper ul li a { line-height: ' . $line_height . ' !important; }';
				}

				// Flag width & height (via CSS custom properties)
				$flag_width = sanitize_text_field( $atts['lsdp_flag_width'] );
				if ( $flag_width && preg_match( '/^\d+(\.\d+)?(px|em|rem|%)$/', $flag_width ) ) {
					$css_rules[] = $sel . ' { --lsdp-flag-width: ' . $flag_width . '; }';
				}

				// Flag border radius
				$flag_radius = sanitize_text_field( $atts['lsdp_flag_radius'] );
				if ( $flag_radius && preg_match( '/^\d+(\.\d+)?(px|em|rem|%)$/', $flag_radius ) ) {
					$css_rules[] = $sel . ' { --lsdp-flag-radius: ' . $flag_radius . '; }';
				}

				// Flag aspect ratio
				$flag_ratio = sanitize_text_field( $atts['lsdp_flag_ratio'] );
				if ( $flag_ratio && in_array( $flag_ratio, array( 'auto', '1/1', '4/3' ), true ) ) {
					$css_rules[] = $sel . ' { --lsdp-flag-ratio: ' . $flag_ratio . '; }';
					if ( '1/1' === $flag_ratio ) {
						$css_rules[] = $sel . ' .lsdp-lang-image { --lsdp-flag-height: var(--lsdp-flag-width); }';
					} else {
						$css_rules[] = $sel . ' .lsdp-lang-image { --lsdp-flag-height: calc(var(--lsdp-flag-width) * 0.75); }';
					}
				}

				// Custom margin (D4 format: top|right|bottom|left|unit)
				$margin = sanitize_text_field( $atts['custom_margin'] );
				if ( $margin ) {
					$parts = explode( '|', $margin );
					$unit  = isset( $parts[4] ) ? $parts[4] : 'px';
					$t     = isset( $parts[0] ) && '' !== $parts[0] ? $parts[0] . $unit : '0';
					$r     = isset( $parts[1] ) && '' !== $parts[1] ? $parts[1] . $unit : '0';
					$b     = isset( $parts[2] ) && '' !== $parts[2] ? $parts[2] . $unit : '0';
					$l     = isset( $parts[3] ) && '' !== $parts[3] ? $parts[3] . $unit : '0';
					$css_rules[] = $sel . ' .lsdp-wrapper ul li a, '
								. $sel . ' .lsdp-wrapper.dropdown, '
								. $sel . ' .lsdp-wrapper.dropdown ul li { margin: ' . $t . ' ' . $r . ' ' . $b . ' ' . $l . ' !important; }';
				}

				// Custom padding
				$padding = sanitize_text_field( $atts['custom_padding'] );
				if ( $padding ) {
					$parts = explode( '|', $padding );
					$unit  = isset( $parts[4] ) ? $parts[4] : 'px';
					$t     = isset( $parts[0] ) && '' !== $parts[0] ? $parts[0] . $unit : '0';
					$r     = isset( $parts[1] ) && '' !== $parts[1] ? $parts[1] . $unit : '0';
					$b     = isset( $parts[2] ) && '' !== $parts[2] ? $parts[2] . $unit : '0';
					$l     = isset( $parts[3] ) && '' !== $parts[3] ? $parts[3] . $unit : '0';
					$css_rules[] = $sel . ' .lsdp-wrapper ul li a, '
								. $sel . ' .lsdp-wrapper.dropdown, '
								. $sel . ' .lsdp-wrapper.dropdown ul li { padding: ' . $t . ' ' . $r . ' ' . $b . ' ' . $l . ' !important; }';
				}

				// ── Assemble final output ──────────────────────────────────────
				$style_tag = '';
				if ( ! empty( $css_rules ) ) {
					$style_tag = '<style id="lsdp-d4-inline-' . esc_attr( $uid ) . '">'
								. implode( ' ', $css_rules )
								. '</style>';
				}

				return sprintf(
					'%s<div id="%s" class="et_pb_module lsdp_language_switcher_for_divi_polylang"><div class="lsdp-main-wrapper"><div class="lsdp-wrapper %s">%s</div></div></div>',
					$style_tag,
					esc_attr( $uid ),
					esc_attr( $layout ),
					$inner_html
				);
			}
		);
	},
	11 // after modules are fully loaded (priority > 10)
);


/**
 * Register the Divi conversion pipeline filter.
 *
 * Ensures this module is wired into Divi's D4 → D5 attribute migration system
 * so that when an editor does resave a page the conversion outline is applied.
 *
 * @since 1.0.0
 */
add_filter(
	'divi.moduleLibrary.conversion.valueExpansionFunctionMap',
	function ( $map ) {
		// No custom PHP-side expanders needed; the conversionOutline only uses
		// the built-in JS convertInlineFont helper.  Returning $map untouched
		// still registers this module with the conversion pipeline.
		return $map;
	}
);
