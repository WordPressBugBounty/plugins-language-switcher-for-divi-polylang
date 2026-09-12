<?php

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Direct access forbidden.' );
}
class LSDP_STYLE_HELPERS {
	private $redner_slug = '';
	private $props       = '';
	private $order       = 0;

	public function __construct( $props, $render_slug, $order ) {
		$this->redner_slug = $render_slug;
		$this->props       = $props;
		$this->order       = $order;
		$this->generate_divi_styles();
	} 

	private function generate_divi_styles() {
		$selector = '%%order_class%% .lsdp-wrapper';
		$slug                    = $this->redner_slug;
		$attr                    = $this->props;
		$lang_padding            = isset( $attr['lsdp_bg_normal_padding'] ) ? esc_attr($attr['lsdp_bg_normal_padding']) : '';
		$lang_margin             = isset( $attr['lsdp_bg_normal_margin'] ) ? esc_attr($attr['lsdp_bg_normal_margin']) : '';
		$lang_normal_bg_color    = isset( $attr['lsdp_bg_normal_color'] ) ? esc_attr($attr['lsdp_bg_normal_color']) : '';
		$lang_hover_bg_color_hover = isset( $attr['lsdp_bg_normal_color__hover'] ) ? esc_attr($attr['lsdp_bg_normal_color__hover']) : '';
		$flag_width              = isset( $attr['lsdp_flag_width'] ) ? esc_attr($attr['lsdp_flag_width']) : '';
		$flag_radius             = isset( $attr['lsdp_flag_radius'] ) ? esc_attr($attr['lsdp_flag_radius']) : '';
		$flag_ratio              = isset( $attr['lsdp_flag_ratio'] ) ? esc_attr($attr['lsdp_flag_ratio']) : '';
		$normal_text_font        = isset( $attr['lsdp_text_settings_font'] ) ? esc_attr($attr['lsdp_text_settings_font']) : '';
		$hover_text_font         = isset( $attr['lsdp_text_settings_font__hover'] ) ? esc_attr($attr['lsdp_text_settings_font__hover']) : '';
		$normal_text_color       = isset( $attr['lsdp_text_settings_text_color'] ) ? esc_attr($attr['lsdp_text_settings_text_color']) : '';
		$hover_text_color 		 = isset( $attr['lsdp_text_settings_text_color__hover'] ) ? esc_attr($attr['lsdp_text_settings_text_color__hover']) : '';
		$normal_text_size        = isset( $attr['lsdp_text_settings_font_size'] ) ? esc_attr($attr['lsdp_text_settings_font_size']) : '';
		$hover_text_size		 = isset( $attr['lsdp_text_settings_font_size__hover'] ) ? esc_attr($attr['lsdp_text_settings_font_size__hover']) : '';
		$normal_text_spacing     = isset( $attr['lsdp_text_settings_letter_spacing'] ) ? esc_attr($attr['lsdp_text_settings_letter_spacing']) : '';
		$hover_text_spacing      = isset( $attr['lsdp_text_settings_letter_spacing__hover'] ) ? esc_attr($attr['lsdp_text_settings_letter_spacing__hover']) : '';
		$normal_text_line_height = isset( $attr['lsdp_text_settings_line_height'] ) ? esc_attr($attr['lsdp_text_settings_line_height']) : '';
		$hover_text_line_height  = isset( $attr['lsdp_text_settings_line_height__hover'] ) ? esc_attr($attr['lsdp_text_settings_line_height__hover']) : '';
		$hover_bg_margin         = isset( $attr['custom_margin__hover'] ) ? esc_attr($attr['custom_margin__hover']) : '';
		$hover_bg_padding        = isset( $attr['custom_padding__hover'] ) ? esc_attr($attr['custom_padding__hover']) : '';
		ET_Builder_Element::set_style(
			$slug,
			array(
				'selector'    => $selector.'.dropdown',
				'declaration' => sprintf( '--lsdp-dropdown-index: %1$s;', ( 999 + $this->order ) ),
			)
		);

		$this->set_spacing_styles( $slug, $selector, 'lsdp-lang-padding', $lang_padding );
		$this->set_spacing_styles( $slug, $selector, 'lsdp-lang-margin', $lang_margin );

		if ( '' !== $lang_normal_bg_color ) {
			$color = $this->sanitize_css_color( $lang_normal_bg_color );
			if ( false !== $color ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-normal-bg-color: %1$s;', $color ),
					)
				);
			}
		}
		if ( '' !== $lang_hover_bg_color_hover ) {
			$color = $this->sanitize_css_color( $lang_hover_bg_color_hover );
			if ( false !== $color ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-hover-bg-color: %1$s;', $color ),
					)
				);
			}
		}

		$this->set_spacing_styles( $slug, $selector, 'lsdp-hover-bg-mrgn', $hover_bg_margin );
		$this->set_spacing_styles( $slug, $selector, 'lsdp-hover-bg-pading', $hover_bg_padding );

		if ( '' !== $flag_ratio && '1/1' === $flag_ratio ) {
			ET_Builder_Element::set_style(
				$slug,
				array(
					'selector'    => $selector,
					'declaration' => '--lsdp-flag-height: var(--lsdp-flag-width);',
				)
			);
		}
		if ( '' !== $flag_width ) {
			$flag_width = $this->sanitize_css_length( $flag_width );
			if ( false !== $flag_width ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-flag-width: %1$s;', $flag_width ),
					)
				);
			}
		}
		if ( '' !== $flag_radius ) {
			$flag_radius = $this->sanitize_css_length( $flag_radius );
			if ( false !== $flag_radius ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-flag-radius: %1$s;', $flag_radius ),
					)
				);
			}
		}
		if ( '' !== $normal_text_font ) {
			$this->load_google_fonts( $normal_text_font );
			$Font_properties = $this->get_font_properties( $normal_text_font );
			$font_family_css   = $this->format_css_font_family( $Font_properties['fontFamily'] );
			if ( false !== $font_family_css ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-normal-text-font: %1$s;', $font_family_css ),
					)
				);
			}
			$this->set_css_custom_property( $slug, $selector, '--lsdp-normal-text-weight', $this->sanitize_css_font_weight( $Font_properties['fontWeight'] ) );
			$this->set_css_custom_property( $slug, $selector, '--lsdp-normal-text-transform', $this->sanitize_css_text_transform( $Font_properties['textTransform'] ) );
			$this->set_css_custom_property( $slug, $selector, '--lsdp-normal-text-decoration', $this->sanitize_css_text_decoration( $Font_properties['textDecoration'] ) );
			$this->set_css_custom_property( $slug, $selector, '--lsdp-normal-text-style', $this->sanitize_css_font_style( $Font_properties['fontStyle'] ) );
			$this->set_css_custom_property( $slug, $selector, '--lsdp-normal-text-decoration-color', $this->sanitize_css_color( $Font_properties['textDecorationLineColor'] ) );
			$this->set_css_custom_property( $slug, $selector, '--lsdp-normal-text-decoration-style', $this->sanitize_css_text_decoration_style( $Font_properties['textDecorationStyle'] ) );
		}
		if ( '' !== $normal_text_color ) {
			$color = $this->sanitize_css_color( $normal_text_color );
			if ( false !== $color ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-normal-text-color: %1$s;', $color ),
					)
				);
			}
		}
		if ( '' !== $hover_text_color ) {
			$color = $this->sanitize_css_color( $hover_text_color );
			if ( false !== $color ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-hover-text-color: %1$s;', $color ),
					)
				);
			}
		}
		if ( '' !== $normal_text_spacing ) {
			$letter_spacing = $this->sanitize_css_length( $normal_text_spacing );
			if ( false !== $letter_spacing ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-normal-text-letter-spacing: %1$s;', $letter_spacing ),
					)
				);
			}
		}
		if ( '' !== $hover_text_spacing ) {
			$letter_spacing = $this->sanitize_css_length( $hover_text_spacing );
			if ( false !== $letter_spacing ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-hover-text-letter-spacing: %1$s;', $letter_spacing ),
					)
				);
			}
		}
		if ( '' !== $normal_text_size ) {
			$text_size = $this->sanitize_css_length( $normal_text_size );
			if ( false !== $text_size ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-normal-text-size: %1$s;', $text_size ),
					)
				);
			}
		}
		if ( '' !== $hover_text_size ) {
			$text_size = $this->sanitize_css_length( $hover_text_size );
			if ( false !== $text_size ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-hover-text-size: %1$s;', $text_size ),
					)
				);
			}
		}
		if ( '' !== $normal_text_line_height ) {
			$line_height = $this->sanitize_css_length( $normal_text_line_height );
			if ( false !== $line_height ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-normal-text-line-height: %1$s;', $line_height ),
					)
				);
			}
		}
		if ( '' !== $hover_text_line_height ) {
			$line_height = $this->sanitize_css_length( $hover_text_line_height );
			if ( false !== $line_height ) {
				ET_Builder_Element::set_style(
					$slug,
					array(
						'selector'    => $selector,
						'declaration' => sprintf( '--lsdp-hover-text-line-height: %1$s;', $line_height ),
					)
				);
			}
		}
		// Code for generating Divi styles goes here
	}

	private function load_google_fonts( $font_family ) {
		$font_parts       = explode( '|', $font_family );
		$font_family_name = isset( $font_parts[0] ) ? $font_parts[0] : '';
		$font_family_name = $this->sanitize_font_family_name( $font_family_name );

		if ( false === $font_family_name ) {
			return;
		}

		$handle_suffix = sanitize_key( str_replace( ' ', '-', $font_family_name ) );
		if ( '' === $handle_suffix ) {
			return;
		}

		$url = sprintf(
			'https://fonts.googleapis.com/css2?family=%s&display=swap',
			rawurlencode( $font_family_name )
		);

		wp_enqueue_style( 'lsdp-gfonts-' . $handle_suffix, esc_url( $url ), array(), LSDP );
	}

	/**
	 * Allow-list validation for font family names from builder props.
	 *
	 * @param string $font_family_name Raw font family name.
	 * @return string|false Sanitized name, or false when invalid.
	 */
	private function sanitize_font_family_name( $font_family_name ) {
		$font_family_name = trim( (string) $font_family_name );

		if ( '' === $font_family_name || strlen( $font_family_name ) > 100 ) {
			return false;
		}

		if ( ! preg_match( '/^[A-Za-z0-9][A-Za-z0-9 _\-]*$/', $font_family_name ) ) {
			return false;
		}

		return $font_family_name;
	}

	/**
	 * Wrap a validated font family name for safe CSS custom property output.
	 *
	 * @param string $font_family_name Raw font family name.
	 * @return string|false Quoted CSS font-family value, or false when invalid.
	 */
	private function format_css_font_family( $font_family_name ) {
		$font_family_name = $this->sanitize_font_family_name( $font_family_name );

		if ( false === $font_family_name ) {
			return false;
		}

		$escaped = addcslashes( $font_family_name, "\\\"" );

		return '"' . $escaped . '"';
	}

	private function get_font_properties( $fontString ) {
		$fontParts  = explode( '|', $fontString );
		$fontFamily = $fontParts[0];
		$fontWeight = $fontParts[1];
		$fontStyle  = ! empty( $fontParts[2] ) ? 'italic' : 'normal';
		// Determine text transform
		if ( ! empty( $fontParts[3] ) ) {
			$textTransform = 'uppercase';
		} elseif ( ! empty( $fontParts[5] ) ) {
			$textTransform = 'capitalize';
		} else {
			$textTransform = 'none';
		}

		// Determine text decoration
		if ( ! empty( $fontParts[4] ) && ! empty( $fontParts[6] ) ) {
			$textDecoration = 'line-through';
		} elseif ( ! empty( $fontParts[4] ) ) {
			$textDecoration = 'underline';
		} elseif ( ! empty( $fontParts[6] ) ) {
			$textDecoration = 'line-through';
		} else {
			$textDecoration = 'none';
		}

		$textDecorationLineColor = ( ! empty( $fontParts[7] ) ) ? $fontParts[7] : '';
		$textDecorationStyle     = ( ! empty( $fontParts[8] ) ) ? $fontParts[8] : '';

		return array(
			'fontFamily'              => $fontFamily,
			'fontWeight'              => $fontWeight,
			'fontStyle'               => $fontStyle,
			'textTransform'           => $textTransform,
			'textDecoration'          => $textDecoration,
			'textDecorationLineColor' => $textDecorationLineColor,
			'textDecorationStyle'     => $textDecorationStyle,
		);
	}

	/**
	 * Output a validated CSS custom property, skipping invalid values.
	 *
	 * @param string       $slug           Module slug.
	 * @param string       $selector       CSS selector.
	 * @param string       $property_name  Custom property name (e.g. --lsdp-normal-text-weight).
	 * @param string|false $value          Sanitized value, or false when invalid.
	 */
	private function set_css_custom_property( $slug, $selector, $property_name, $value ) {
		if ( false === $value || '' === $value ) {
			return;
		}

		ET_Builder_Element::set_style(
			$slug,
			array(
				'selector'    => $selector,
				'declaration' => sprintf( '%1$s: %2$s;', $property_name, $value ),
			)
		);
	}

	/**
	 * Validate font-weight for safe use in CSS custom properties.
	 *
	 * @param string $weight Raw font weight from Divi font string.
	 * @return string|false Sanitized weight, or false when invalid.
	 */
	private function sanitize_css_font_weight( $weight ) {
		$weight = trim( (string) $weight );

		if ( '' === $weight ) {
			return false;
		}

		$keywords = array( 'normal', 'bold', 'lighter', 'bolder' );
		if ( in_array( $weight, $keywords, true ) ) {
			return $weight;
		}

		if ( preg_match( '/^[1-9]00$/', $weight ) ) {
			return $weight;
		}

		return false;
	}

	/**
	 * Validate text-transform for safe use in CSS custom properties.
	 *
	 * @param string $transform Raw text-transform value.
	 * @return string|false Sanitized value, or false when invalid.
	 */
	private function sanitize_css_text_transform( $transform ) {
		$allowed = array( 'none', 'uppercase', 'lowercase', 'capitalize' );

		return $this->sanitize_css_keyword( $transform, $allowed );
	}

	/**
	 * Validate text-decoration for safe use in CSS custom properties.
	 *
	 * @param string $decoration Raw text-decoration value.
	 * @return string|false Sanitized value, or false when invalid.
	 */
	private function sanitize_css_text_decoration( $decoration ) {
		$allowed = array( 'none', 'underline', 'line-through', 'overline' );

		return $this->sanitize_css_keyword( $decoration, $allowed );
	}

	/**
	 * Validate font-style for safe use in CSS custom properties.
	 *
	 * @param string $style Raw font-style value.
	 * @return string|false Sanitized value, or false when invalid.
	 */
	private function sanitize_css_font_style( $style ) {
		$allowed = array( 'normal', 'italic', 'oblique' );

		return $this->sanitize_css_keyword( $style, $allowed );
	}

	/**
	 * Validate text-decoration-style for safe use in CSS custom properties.
	 *
	 * @param string $style Raw text-decoration-style value.
	 * @return string|false Sanitized value, or false when invalid.
	 */
	private function sanitize_css_text_decoration_style( $style ) {
		$allowed = array( 'solid', 'double', 'dotted', 'dashed', 'wavy' );

		return $this->sanitize_css_keyword( $style, $allowed );
	}

	/**
	 * Allow-list validation for discrete CSS keyword values.
	 *
	 * @param string $value   Raw value.
	 * @param array  $allowed Permitted keyword values.
	 * @return string|false Sanitized keyword, or false when invalid.
	 */
	private function sanitize_css_keyword( $value, $allowed ) {
		$value = strtolower( trim( (string) $value ) );

		if ( '' === $value || ! in_array( $value, $allowed, true ) ) {
			return false;
		}

		return $value;
	}

	/**
	 * Validate a CSS length for safe use in custom properties.
	 *
	 * @param string $length Raw length from builder props.
	 * @return string|false Sanitized length, or false when invalid.
	 */
	private function sanitize_css_length( $length ) {
		$length = trim( (string) $length );

		if ( '' === $length ) {
			return false;
		}

		if ( ! preg_match( '/^-?\d+(\.\d+)?(px|em|rem|%|vw|vh)?$/', $length ) ) {
			return false;
		}

		return $length;
	}

	/**
	 * Validate a color for safe use in CSS custom properties.
	 *
	 * @param string $color Raw color from builder props.
	 * @return string|false Sanitized color, or false when invalid.
	 */
	private function sanitize_css_color( $color ) {
		$color = trim( (string) $color );

		if ( '' === $color ) {
			return false;
		}

		$hex = sanitize_hex_color( $color );
		if ( $hex ) {
			return $hex;
		}

		if ( preg_match( '/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(0|1|0?\.\d+)\s*)?\)$/i', $color, $matches ) ) {
			$red   = (int) $matches[1];
			$green = (int) $matches[2];
			$blue  = (int) $matches[3];

			if ( $red > 255 || $green > 255 || $blue > 255 ) {
				return false;
			}

			if ( isset( $matches[4] ) && '' !== $matches[4] && (float) $matches[4] > 1 ) {
				return false;
			}

			if ( isset( $matches[4] ) && '' !== $matches[4] ) {
				return sprintf( 'rgba(%d,%d,%d,%s)', $red, $green, $blue, $matches[4] );
			}

			return sprintf( 'rgb(%d,%d,%d)', $red, $green, $blue );
		}

		return false;
	}

	/**
	 * Output validated spacing values as CSS custom properties.
	 *
	 * @param string $slug            Module slug.
	 * @param string $selector        CSS selector.
	 * @param string $property_prefix Custom property prefix.
	 * @param string $unit_string     Pipe-delimited Divi spacing string.
	 */
	private function set_spacing_styles( $slug, $selector, $property_prefix, $unit_string ) {
		if ( '' === $unit_string ) {
			return;
		}

		$allowed_sides = array( 'top', 'right', 'bottom', 'left' );
		$spacing       = $this->get_unit_value( $unit_string );

		foreach ( $spacing as $side => $value ) {
			if ( ! in_array( $side, $allowed_sides, true ) || '' === $value ) {
				continue;
			}

			$length = $this->sanitize_css_length( $value );
			if ( false === $length ) {
				continue;
			}

			ET_Builder_Element::set_style(
				$slug,
				array(
					'selector'    => $selector,
					'declaration' => sprintf( '--%1$s-%2$s: %3$s;', $property_prefix, $side, $length ),
				)
			);
		}
	}

	private function get_unit_value( $unitString ) {
		$units = explode( '|', $unitString );

		return array(
			'top'    => $units[0],
			'bottom' => $units[2],
			'left'   => $units[3],
			'right'  => $units[1],
		);
	}
}
