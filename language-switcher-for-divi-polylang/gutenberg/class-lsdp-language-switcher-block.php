<?php
/**
 * Gutenberg Language Switcher Block
 *
 * @package Language_Switcher_For_Elementor_Polylang
 * @since   1.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class - Singleton pattern
 */
class LSDP_Language_Switcher_Block {

	/**
	 * Single instance of the class
	 *
	 * @var LSDP_Language_Switcher_Block
	 */
	private static $instance = null;

	/**
	 * Block ID counter for unique block IDs
	 *
	 * @var int
	 */
	private $block_id = 0;

	/**
	 * Private constructor to prevent direct instantiation
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Prevent cloning of the instance.
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing of the instance.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Prevent unserializing of the instance (PHP 7.4+).
	 *
	 * @param array $data Serialized data.
	 */
	public function __unserialize( array $data ): void {
		throw new \Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Get the singleton instance
	 *
	 * @return LSDP_Language_Switcher_Block
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'register_language_switcher_block' ) );
	}

	/**
	 * Register language switcher block
	 */
	public function register_language_switcher_block() {
		$this->register_block_assets();

		$switcher_options = $this->get_switcher_options();

		$attributes = $this->get_block_attributes( $switcher_options );

		$block_args = array(
			'attributes'      => $attributes,
			'style'           => 'lsdp-language-switcher-block',
			'render_callback' => array( $this, 'render_language_switcher_block' ),
		);

		if ( $this->is_modern_block_editor_available() ) {
			$block_args['api_version']   = version_compare( $GLOBALS['wp_version'], '6.3', '>=' ) ? 3 : 2;
			$block_args['editor_script'] = 'lsdp-language-switcher-block';
			$block_args['editor_style']  = 'lsdp-language-switcher-block-editor';
		}

		register_block_type( 'lsdp/language-switcher', $block_args );
	}

	/**
	 * Whether the bundled editor application can run on this WordPress version.
	 *
	 * The bundle uses Block API v2 hooks introduced in WordPress 5.6. Older
	 * versions still receive safe server-side rendering for existing blocks.
	 *
	 * @return bool
	 */
	private function is_modern_block_editor_available() {
		global $wp_version;

		return isset( $wp_version ) && version_compare( $wp_version, '5.6', '>=' );
	}

	/**
	 * Register block assets (scripts and styles)
	 */
	private function register_block_assets() {
		$asset_file = LSDP_DIR . 'blocks/language-switcher/build/index.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array(),
				'version'      => LSDP,
			);

		if ( $this->is_modern_block_editor_available() ) {
			wp_register_script(
				'lsdp-language-switcher-block',
				LSDP_URL . 'blocks/language-switcher/build/index.js',
				$asset['dependencies'],
				$asset['version'],
				false
			);
		}

		wp_register_script(
			'lsdp-custom-dropdown',
			LSDP_URL . 'blocks/language-switcher/build/dropdown.js',
			array(),
			LSDP,
			true
		);

		wp_register_style(
			'lsdp-language-switcher-block-editor',
			LSDP_URL . 'blocks/language-switcher/build/editor.css',
			array( 'wp-edit-blocks' ),
			LSDP
		);

		wp_register_style(
			'lsdp-language-switcher-block',
			LSDP_URL . 'blocks/language-switcher/build/style.css',
			array(),
			LSDP
		);

		if ( $this->is_modern_block_editor_available() ) {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		}
	}

	/**
	 * Enqueue block editor-only assets and localized settings.
	 */
	public function enqueue_block_editor_assets() {
		wp_localize_script(
			'lsdp-language-switcher-block',
			'lsdpBlockSettings',
			array(
				'options'        => $this->get_switcher_options(),
				'languages'      => LSDP_Common_Helpers::get_polylang_language_select_options(),
				'polylangActive' => function_exists( 'PLL' ),
			)
		);

		wp_enqueue_script( 'lsdp-custom-dropdown' );
	}

	/**
	 * Get switcher options
	 *
	 * @return array
	 */
	private function get_switcher_options() {
		return array(
			'show_names'             => array(
				'label'   => __( 'Displays language names', 'language-switcher-for-divi-polylang' ),
				'default' => 1,
			),
			'show_flags'             => array(
				'label'   => __( 'Displays flags', 'language-switcher-for-divi-polylang' ),
				'default' => 1,
			),
			'show_language_codes'    => array(
				'label'   => __( 'Show Language Codes', 'language-switcher-for-divi-polylang' ),
				'default' => 0,
			),
			'hide_current'           => array(
				'label'   => __( 'Hides the current language', 'language-switcher-for-divi-polylang' ),
				'default' => 0,
			),
			'hide_if_no_translation' => array(
				'label'   => __( 'Hides languages with no translation', 'language-switcher-for-divi-polylang' ),
				'default' => 0,
			),
			'dropdown'               => array(
				'label'   => __( 'Layout', 'language-switcher-for-divi-polylang' ),
				'type'    => 'select',
				'default' => 'dropdown',
				'options' => array(
					'dropdown'   => __( 'Dropdown', 'language-switcher-for-divi-polylang' ),
					'vertical'   => __( 'Vertical', 'language-switcher-for-divi-polylang' ),
					'horizontal' => __( 'Horizontal', 'language-switcher-for-divi-polylang' ),
				),
			),
		);
	}

	/**
	 * Get block attributes
	 *
	 * @param array $switcher_options Switcher options.
	 * @return array
	 */
	private function get_block_attributes( $switcher_options ) {
		$attributes = array(
			'className'               => array(
				'type'    => 'string',
				'default' => '',
			),
			'marginTop'               => array(
				'type'    => 'number',
				'default' => 0,
			),
			'marginRight'             => array(
				'type'    => 'number',
				'default' => 0,
			),
			'marginBottom'            => array(
				'type'    => 'number',
				'default' => 0,
			),
			'marginLeft'              => array(
				'type'    => 'number',
				'default' => 0,
			),
			'paddingTop'              => array(
				'type'    => 'number',
				'default' => 0,
			),
			'paddingRight'            => array(
				'type'    => 'number',
				'default' => 0,
			),
			'paddingBottom'           => array(
				'type'    => 'number',
				'default' => 0,
			),
			'paddingLeft'             => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderColor'             => array(
				'type'    => 'string',
				'default' => '',
			),
			'borderStyle'             => array(
				'type'    => 'string',
				'default' => 'solid',
			),
			'borderWidth'             => array(
				'type'    => 'string',
				'default' => '0px',
			),
			'borderWidthTop'          => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderWidthRight'        => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderWidthBottom'       => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderWidthLeft'         => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderRadiusTopLeft'     => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderRadiusTopRight'    => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderRadiusBottomRight' => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderRadiusBottomLeft'  => array(
				'type'    => 'number',
				'default' => 0,
			),
			'flagRatio'               => array(
				'type'    => 'string',
				'default' => '4/3',
			),
			'flagWidth'               => array(
				'type'    => 'number',
				'default' => 24,
			),
			'flagRadius'              => array(
				'type'    => 'number',
				'default' => 0,
			),
			'fontSize'                => array(
				'type'    => 'string',
				'default' => '',
			),
			'fontFamily'              => array(
				'type'    => 'string',
				'default' => '',
			),
			'textColor'               => array(
				'type'    => 'string',
				'default' => '',
			),
			'backgroundColor'         => array(
				'type'    => 'string',
				'default' => '',
			),
			'textTransform'           => array(
				'type'    => 'string',
				'default' => 'none',
			),
			'alignment'               => array(
				'type'    => 'string',
				'default' => 'left',
			),
			'customLanguages'         => array(
				'type'    => 'array',
				'default' => array(),
			),
		);

		foreach ( $switcher_options as $option => $data ) {
			if ( isset( $data['type'] ) && 'select' === $data['type'] ) {
				$attributes[ $option ] = array(
					'type'    => 'string',
					'default' => $data['default'],
				);
			} else {
				$attributes[ $option ] = array(
					'type'    => 'boolean',
					'default' => (bool) $data['default'],
				);
			}
		}

		return $attributes;
	}

	/**
	 * Generate a unique block identifier
	 *
	 * @param array $attributes Block attributes.
	 * @return string Unique block identifier.
	 */
	private function get_unique_block_id( $attributes ) {
		++$this->block_id;
		$unique_hash = substr( md5( $this->block_id . wp_json_encode( $attributes ) ), 0, 8 );
		$unique_id   = 'lsdp-block-' . $this->block_id . '-' . $unique_hash;
		return $unique_id;
	}

	/**
	 * Generate per-instance block CSS and enqueue it on the shared block handle.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $block_class Block class name.
	 * @return string
	 */
	private function generate_spacing_css( $attributes, $block_class ) {
		$style_context = $this->get_style_context( $attributes );
		$css           = $this->build_instance_css( $style_context, $block_class );

		if ( '' === $css ) {
			return '';
		}

		wp_enqueue_style( 'lsdp-language-switcher-block' );
		wp_add_inline_style( 'lsdp-language-switcher-block', $css );
		return $css;
	}

	/**
	 * Determine whether the block is being rendered through a REST request.
	 *
	 * @return bool
	 */
	private function is_rest_render() {
		return defined( 'REST_REQUEST' ) && REST_REQUEST;
	}

	/**
	 * Build inline style tag markup for a rendered block instance.
	 *
	 * @param string $css CSS string.
	 * @return string
	 */
	private function get_instance_style_tag( $css ) {
		if ( '' === $css ) {
			return '';
		}

		return '<style>' . $css . '</style>';
	}

	/**
	 * Build normalized style context for an instance.
	 *
	 * @param array $attributes Block attributes.
	 * @return array
	 */
	private function get_style_context( $attributes ) {
		$margin  = lsdp_get_block_spacing_values( $attributes, 'margin' );
		$padding = lsdp_get_block_spacing_values( $attributes, 'padding' );
		$border  = lsdp_get_block_border_values( $attributes );
		$flag    = lsdp_get_block_flag_values( $attributes );

		$alignment = isset( $attributes['alignment'] ) ? $attributes['alignment'] : 'left';
		$alignment = in_array( $alignment, array( 'left', 'center', 'right' ), true ) ? $alignment : 'left';

		return array(
			'margin'               => $margin,
			'padding'              => $padding,
			'border'               => $border,
			'flag'                 => $flag,
			'show_flags'           => ! empty( $attributes['show_flags'] ),
			'font_size'            => $attributes['fontSize'] ?? '',
			'font_family'          => $attributes['fontFamily'] ?? '',
			'text_color'           => $attributes['textColor'] ?? '',
			'background_color'     => $attributes['backgroundColor'] ?? '',
			'text_transform'       => $attributes['textTransform'] ?? '',
			'alignment'            => $alignment,
			'has_margin'           => array_sum( $margin ) > 0,
			'has_padding'          => array_sum( $padding ) > 0,
			'has_border'           => ! empty( $border['color'] ) && ( ! empty( $border['width'] ) || $border['has_individual_widths'] ),
			'has_border_radius'    => ! empty( $border['has_border_radius'] ),
			'has_font_size'        => ! empty( $attributes['fontSize'] ),
			'has_font_family'      => ! empty( $attributes['fontFamily'] ),
			'has_text_color'       => ! empty( $attributes['textColor'] ),
			'has_background_color' => ! empty( $attributes['backgroundColor'] ),
			'has_text_transform'   => ! empty( $attributes['textTransform'] ) && 'none' !== $attributes['textTransform'],
			'has_alignment'        => 'left' !== $alignment,
		);
	}

	/**
	 * Build all per-instance CSS rules.
	 *
	 * @param array  $style_context Normalized style context.
	 * @param string $block_class Block class name.
	 * @return string
	 */
	private function build_instance_css( $style_context, $block_class ) {
		$has_typography = $style_context['has_font_size'] || $style_context['has_font_family'] || $style_context['has_text_color'] || $style_context['has_background_color'] || $style_context['has_text_transform'];

		if ( ! $style_context['has_margin'] && ! $style_context['has_padding'] && ! $style_context['has_border'] && ! $style_context['has_border_radius'] && ! $style_context['show_flags'] && ! $has_typography && ! $style_context['has_alignment'] ) {
			return '';
		}

		$rules = array();

		$list_declarations = $this->build_list_item_declarations( $style_context );
		if ( ! empty( $list_declarations ) ) {
			$rules[] = $this->build_css_rule(
				array(
					'.' . $block_class . '.lsdp-layout-horizontal .lsdp-lang-item',
					'.' . $block_class . '.lsdp-layout-vertical .lsdp-lang-item',
				),
				$list_declarations
			);
		}

		$alignment_rules = $this->build_alignment_rules( $style_context, $block_class );
		if ( ! empty( $alignment_rules ) ) {
			$rules = array_merge( $rules, $alignment_rules );
		}

		$dropdown_rules = $this->build_dropdown_rules( $style_context, $block_class );
		if ( ! empty( $dropdown_rules ) ) {
			$rules = array_merge( $rules, $dropdown_rules );
		}

		$flag_rules = $this->build_flag_rules( $style_context, $block_class );
		if ( ! empty( $flag_rules ) ) {
			$rules = array_merge( $rules, $flag_rules );
		}

		return implode( '', $rules );
	}

	/**
	 * Build declarations for list items in horizontal and vertical layouts.
	 *
	 * @param array $style_context Normalized style context.
	 * @return array
	 */
	private function build_list_item_declarations( $style_context ) {
		$declarations = array();
		$declarations = array_merge( $declarations, $this->get_spacing_declarations( $style_context['margin'], 'margin', $style_context['has_margin'] ) );
		$declarations = array_merge( $declarations, $this->get_spacing_declarations( $style_context['padding'], 'padding', $style_context['has_padding'] ) );
		$declarations = array_merge( $declarations, $this->get_border_declarations( $style_context['border'], false ) );
		$declarations = array_merge( $declarations, $this->get_border_radius_declarations( $style_context['border'], false, $style_context['has_border_radius'] ) );
		$declarations = array_merge( $declarations, $this->get_typography_declarations( $style_context, false, true ) );

		if ( $style_context['has_background_color'] ) {
			$declarations[] = 'background-color: ' . esc_attr( $style_context['background_color'] ) . ';';
		}

		return $declarations;
	}

	/**
	 * Build alignment rules.
	 *
	 * @param array  $style_context Normalized style context.
	 * @param string $block_class Block class name.
	 * @return array
	 */
	private function build_alignment_rules( $style_context, $block_class ) {
		if ( ! $style_context['has_alignment'] ) {
			return array();
		}

		$justify = ( 'center' === $style_context['alignment'] ) ? 'center' : 'flex-end';
		$text    = $style_context['alignment'];

		$rules = array(
			$this->build_css_rule( array( '.' . $block_class . '.lsdp-layout-horizontal' ), array( 'justify-content: ' . $justify . ';' ) ),
			$this->build_css_rule( array( '.' . $block_class . '.lsdp-layout-dropdown' ), array( 'text-align: ' . $text . ';' ) ),
		);

		// Vertical is width:max-content; auto margins position it in the nav.
		if ( 'center' === $style_context['alignment'] ) {
			$rules[] = $this->build_css_rule(
				array( '.' . $block_class . '.lsdp-layout-vertical' ),
				array( 'margin-left: auto;', 'margin-right: auto;' )
			);
		} elseif ( 'right' === $style_context['alignment'] ) {
			$rules[] = $this->build_css_rule(
				array( '.' . $block_class . '.lsdp-layout-vertical' ),
				array( 'margin-left: auto;', 'margin-right: 0;' )
			);
		}

		return $rules;
	}

	/**
	 * Build dropdown-specific rules.
	 *
	 * @param array  $style_context Normalized style context.
	 * @param string $block_class Block class name.
	 * @return array
	 */
	private function build_dropdown_rules( $style_context, $block_class ) {
		$rules = array();

		$container_declarations = array();
		$container_declarations = array_merge( $container_declarations, $this->get_spacing_declarations( $style_context['margin'], 'margin', $style_context['has_margin'], true ) );
		$container_declarations = array_merge( $container_declarations, $this->get_spacing_declarations( $style_context['padding'], 'padding', $style_context['has_padding'], true ) );
		$container_declarations = array_merge( $container_declarations, $this->get_border_declarations( $style_context['border'], true ) );
		$container_declarations = array_merge( $container_declarations, $this->get_border_radius_declarations( $style_context['border'], true, $style_context['has_border_radius'] ) );
		if ( $style_context['has_background_color'] ) {
			$container_declarations[] = 'background-color: ' . esc_attr( $style_context['background_color'] ) . ' !important;';
		}
		if ( ! empty( $container_declarations ) ) {
			$rules[] = $this->build_css_rule( array( '.' . $block_class . '.lsdp-layout-dropdown .lsdp-dropdown-container' ), $container_declarations );
		}

		$button_declarations = $this->get_typography_declarations( $style_context, true, false );
		if ( ! empty( $button_declarations ) ) {
			$rules[] = $this->build_css_rule( array( '.' . $block_class . '.lsdp-layout-dropdown .lsdp-dropdown-button' ), $button_declarations );
		}

		$menu_declarations = array();
		if ( $style_context['has_background_color'] ) {
			$menu_declarations[] = 'background-color: ' . esc_attr( $style_context['background_color'] ) . ' !important;';
		}
		$menu_declarations = array_merge( $menu_declarations, $this->get_border_radius_declarations( $style_context['border'], true, $style_context['has_border_radius'] ) );
		if ( ! empty( $menu_declarations ) ) {
			$rules[] = $this->build_css_rule( array( '.' . $block_class . '.lsdp-layout-dropdown .lsdp-dropdown-menu' ), $menu_declarations );
		}

		if ( $style_context['has_padding'] ) {
			// Apply to the flex link (not the <li>) so the flag/name/code row stays centered.
			$rules[] = $this->build_css_rule(
				array( '.' . $block_class . '.lsdp-layout-dropdown .lsdp-dropdown-item a' ),
				$this->get_spacing_declarations( $style_context['padding'], 'padding', true, true )
			);
		}

		$item_link_declarations = $this->get_typography_declarations( $style_context, false, false );
		if ( ! empty( $item_link_declarations ) ) {
			$rules[] = $this->build_css_rule( array( '.' . $block_class . '.lsdp-layout-dropdown .lsdp-dropdown-item a' ), $item_link_declarations );
		}

		return $rules;
	}

	/**
	 * Build flag rules.
	 *
	 * @param array  $style_context Normalized style context.
	 * @param string $block_class Block class name.
	 * @return array
	 */
	private function build_flag_rules( $style_context, $block_class ) {
		if ( ! $style_context['show_flags'] ) {
			return array();
		}

		$flag        = $style_context['flag'];
		$flag_height = '4/3' === $flag['ratio'] ? round( $flag['width'] * 0.75 ) : $flag['width'];
		$rules       = array();

		$rules[] = $this->build_css_rule(
			array( '.' . $block_class . ' .lsdp-lang-image' ),
			array(
				'width: ' . absint( $flag['width'] ) . 'px;',
				'height: ' . absint( $flag_height ) . 'px;',
				'overflow: hidden;',
			)
		);

		$image_declarations = array(
			'width: 100%;',
			'height: 100%;',
			'object-fit: cover;',
		);
		if ( ! empty( $flag['radius'] ) ) {
			$image_declarations[] = 'border-radius: ' . absint( $flag['radius'] ) . 'px;';
		}

		$rules[] = $this->build_css_rule( array( '.' . $block_class . ' .lsdp-lang-image img' ), $image_declarations );

		return $rules;
	}

	/**
	 * Build a single CSS rule string.
	 *
	 * @param array $selectors CSS selectors.
	 * @param array $declarations CSS declarations.
	 * @return string
	 */
	private function build_css_rule( $selectors, $declarations ) {
		if ( empty( $selectors ) || empty( $declarations ) ) {
			return '';
		}

		return implode( ',', $selectors ) . '{' . implode( '', $declarations ) . '}';
	}

	/**
	 * Build spacing declarations.
	 *
	 * @param array  $values Spacing values.
	 * @param string $property CSS property.
	 * @param bool   $enabled Whether declarations should be emitted.
	 * @param bool   $important Whether to append !important.
	 * @return array
	 */
	private function get_spacing_declarations( $values, $property, $enabled, $important = false ) {
		if ( ! $enabled ) {
			return array();
		}

		return array(
			$property . ': ' . implode( 'px ', array_map( 'absint', $values ) ) . 'px' . ( $important ? ' !important' : '' ) . ';',
		);
	}

	/**
	 * Build border declarations.
	 *
	 * @param array $border Border config.
	 * @param bool  $important Whether to append !important.
	 * @return array
	 */
	private function get_border_declarations( $border, $important = false ) {
		if ( empty( $border['color'] ) || ( empty( $border['width'] ) && empty( $border['has_individual_widths'] ) ) ) {
			return array();
		}

		$suffix       = $important ? ' !important' : '';
		$declarations = array();

		if ( ! empty( $border['has_individual_widths'] ) ) {
			$declarations[] = 'border-color: ' . $border['color'] . $suffix . ';';
			$declarations[] = 'border-style: ' . $border['style'] . $suffix . ';';
			$declarations[] = 'border-width: ' . absint( $border['top'] ) . 'px ' . absint( $border['right'] ) . 'px ' . absint( $border['bottom'] ) . 'px ' . absint( $border['left'] ) . 'px' . $suffix . ';';
			return $declarations;
		}

		$declarations[] = 'border: ' . $border['width'] . ' ' . $border['style'] . ' ' . $border['color'] . $suffix . ';';
		return $declarations;
	}

	/**
	 * Build border-radius declarations.
	 *
	 * @param array $border Border config.
	 * @param bool  $important Whether to append !important.
	 * @param bool  $enabled Whether declarations should be emitted.
	 * @return array
	 */
	private function get_border_radius_declarations( $border, $important = false, $enabled = true ) {
		if ( ! $enabled ) {
			return array();
		}

		return array(
			'border-radius: ' . absint( $border['radius_top_left'] ) . 'px ' . absint( $border['radius_top_right'] ) . 'px ' . absint( $border['radius_bottom_right'] ) . 'px ' . absint( $border['radius_bottom_left'] ) . 'px' . ( $important ? ' !important' : '' ) . ';',
		);
	}

	/**
	 * Build typography declarations.
	 *
	 * @param array $style_context Normalized style context.
	 * @param bool  $important Whether to append !important where previously used.
	 * @param bool  $include_background Whether to include background color.
	 * @return array
	 */
	private function get_typography_declarations( $style_context, $important = false, $include_background = false ) {
		$declarations = array();
		$suffix       = $important ? ' !important' : '';

		if ( $style_context['has_font_size'] ) {
			$declarations[] = 'font-size: ' . esc_attr( $style_context['font_size'] ) . $suffix . ';';
		}

		if ( $style_context['has_font_family'] ) {
			$declarations[] = 'font-family: ' . esc_attr( $style_context['font_family'] ) . $suffix . ';';
		}

		if ( $style_context['has_text_color'] ) {
			$declarations[] = 'color: ' . esc_attr( $style_context['text_color'] ) . ' !important;';
		}

		if ( $include_background && $style_context['has_background_color'] ) {
			$declarations[] = 'background-color: ' . esc_attr( $style_context['background_color'] ) . $suffix . ';';
		}

		if ( $style_context['has_text_transform'] ) {
			$declarations[] = 'text-transform: ' . esc_attr( $style_context['text_transform'] ) . $suffix . ';';
		}

		return $declarations;
	}

	/**
	 * Get current language slug in both frontend and editor contexts
	 *
	 * @return string Current language slug.
	 */
	private function get_current_language_slug() {
		if ( ! function_exists( 'pll_current_language' ) ) {
			return '';
		}

		// Try to get post language in editor/REST context.
		global $post;

		$post_id = null;

		// Check if we're in a REST API request (editor context).
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			// Try to extract post ID from REST route.
			if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
				// Match patterns like /wp/v2/posts/123 or /wp/v2/pages/123.
				if ( preg_match( '#/wp/v2/(?:posts|pages|[^/]+)/(\d+)#', sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $matches ) ) {
					$post_id = intval( $matches[1] );
				}
			}

			// Check $_GET for post_id.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only operation to determine language context in editor, not processing form data.
			if ( ! $post_id && ! empty( $_GET['post_id'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only operation to determine language context in editor, not processing form data.
				$post_id = intval( $_GET['post_id'] );
			}

			// If we found a post ID, get its language.
			if ( $post_id && function_exists( 'pll_get_post_language' ) ) {
				$lang = pll_get_post_language( $post_id );
				if ( $lang ) {
					return $lang;
				}
			}
		}

		// If we have a post object and it has a language, use that.
		if ( isset( $post->ID ) && function_exists( 'pll_get_post_language' ) ) {
			$lang = pll_get_post_language( $post->ID );
			if ( $lang ) {
				return $lang;
			}
		}

		// Fallback to pll_current_language() for frontend.
		return pll_current_language();
	}

	/**
	 * Render language switcher block
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	public function render_language_switcher_block( $attributes ) {
		if ( ! function_exists( 'pll_the_languages' ) ) {
			$custom_languages = isset( $attributes['customLanguages'] ) ? $attributes['customLanguages'] : array();

			if ( ! empty( $custom_languages ) ) {
				foreach ( $custom_languages as $lang ) {
					if ( ! empty( $lang['language'] ) ) {
						return $this->render_custom_languages( $attributes );
					}
				}
			}

			return $this->render_default_language( $attributes );
		}

		$layout              = isset( $attributes['dropdown'] ) ? $attributes['dropdown'] : 'dropdown';
		$show_names          = ! empty( $attributes['show_names'] );
		$show_flags          = ! empty( $attributes['show_flags'] );
		$show_language_codes = ! empty( $attributes['show_language_codes'] );

		if ( ! $show_names && ! $show_flags && ! $show_language_codes ) {
			return '';
		}

		if ( 'dropdown' === $layout ) {
			return $this->render_custom_dropdown( $attributes );
		}

		return $this->render_horizontal_vertical_layout( $attributes );
	}

	/**
	 * Render default language (English) when Polylang is not available
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	private function render_default_language( $attributes ) {
		$show_names          = ! empty( $attributes['show_names'] );
		$show_flags          = ! empty( $attributes['show_flags'] );
		$show_language_codes = ! empty( $attributes['show_language_codes'] );

		// Check if at least one display option is enabled.
		if ( ! $show_names && ! $show_flags && ! $show_language_codes ) {
			return '';
		}

		// Get current page URL.
		$current_url = home_url();
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$current_url = esc_url_raw( home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
		}

		// Create default English language array.
		$default_language = array(
			array(
				'slug'   => 'en',
				'name'   => 'English',
				'url'    => $current_url,
				'flag'   => 'us',
				'locale' => 'en_US',
			),
		);

		$layout = isset( $attributes['dropdown'] ) ? $attributes['dropdown'] : 'dropdown';

		// Use existing rendering methods.
		if ( 'dropdown' === $layout ) {
			return $this->render_dropdown_layout( $attributes, $default_language, 'en' );
		}

		return $this->render_list_layout( $attributes, $default_language, 'en' );
	}

	/**
	 * Convert custom languages to Polylang-compatible format
	 *
	 * @param array $custom_languages Custom languages array.
	 * @return array Languages in Polylang format.
	 */
	private function convert_custom_languages_to_polylang_format( $custom_languages ) {
		$converted = array();
		$seen      = array();

		$current_url = home_url();
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$current_url = esc_url_raw( home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
		}

		foreach ( $custom_languages as $custom_lang ) {
			if ( empty( $custom_lang['language'] ) ) {
				continue;
			}

			$lang_key = $custom_lang['language'];

			if ( isset( $seen[ $lang_key ] ) ) {
				continue;
			}
			$seen[ $lang_key ] = true;

			$lang_data = LSDP_Common_Helpers::get_polylang_language_by_slug( $lang_key );
			if ( ! $lang_data ) {
				continue;
			}

			$url = ! empty( $custom_lang['url'] ) ? $custom_lang['url'] : $current_url;

			$converted[] = array(
				'slug'   => $lang_data['slug'],
				'name'   => $lang_data['name'],
				'url'    => $url,
				'flag'   => ! empty( $lang_data['flag_url'] ) ? $lang_data['flag_url'] : '',
				'locale' => $lang_data['locale'],
			);
		}

		return $converted;
	}

	/**
	 * Render custom languages from repeater field
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	private function render_custom_languages( $attributes ) {
		$custom_languages = isset( $attributes['customLanguages'] ) ? $attributes['customLanguages'] : array();

		if ( empty( $custom_languages ) ) {
			return '';
		}

		$show_names          = ! empty( $attributes['show_names'] );
		$show_flags          = ! empty( $attributes['show_flags'] );
		$show_language_codes = ! empty( $attributes['show_language_codes'] );

		// Check if at least one display option is enabled
		if ( ! $show_names && ! $show_flags && ! $show_language_codes ) {
			return '';
		}

		// Convert custom languages to Polylang format
		$languages = $this->convert_custom_languages_to_polylang_format( $custom_languages );

		if ( empty( $languages ) ) {
			return '';
		}

		$layout = isset( $attributes['dropdown'] ) ? $attributes['dropdown'] : 'dropdown';

		// Use existing rendering methods
		if ( 'dropdown' === $layout ) {
			return $this->render_dropdown_layout( $attributes, $languages );
		}

		return $this->render_list_layout( $attributes, $languages );
	}

	/**
	 * Render horizontal and vertical layouts (for Polylang)
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	private function render_horizontal_vertical_layout( $attributes ) {
		if ( ! function_exists( 'pll_the_languages' ) ) {
			return '';
		}

		$show_names             = ! empty( $attributes['show_names'] );
		$hide_current           = ! empty( $attributes['hide_current'] );
		$hide_if_no_translation = ! empty( $attributes['hide_if_no_translation'] );

		// Get current language slug (works in both frontend and editor contexts)
		$current_lang_slug = $this->get_current_language_slug();

		$args = array(
			'echo'                   => 0,
			'raw'                    => 1,
			'show_flags'             => 0,
			'show_names'             => $show_names,
			'hide_current'           => false,
			'hide_if_no_translation' => false,
		);

		$languages = pll_the_languages( $args );

		if ( empty( $languages ) || ! is_array( $languages ) ) {
			return '';
		}

		// Manually filter languages using our correct language detection (needed for editor/REST context)
		if ( $hide_current || $hide_if_no_translation ) {
			$languages = array_filter(
				$languages,
				function ( $lang ) use ( $hide_current, $hide_if_no_translation, $current_lang_slug ) {
					// Filter out current language if hide_current is enabled
					if ( $hide_current && isset( $lang['slug'] ) && $lang['slug'] === $current_lang_slug ) {
						return false;
					}
					// Filter out languages with no translation if hide_if_no_translation is enabled
					if ( $hide_if_no_translation && ! empty( $lang['no_translation'] ) ) {
						return false;
					}
					return true;
				}
			);
		}

		return $this->render_list_layout( $attributes, $languages, $current_lang_slug );
	}

	/**
	 * Render list layout (horizontal/vertical) - reusable for both Polylang and custom languages
	 *
	 * @param array  $attributes Block attributes.
	 * @param array  $languages Languages array in Polylang format.
	 * @param string $current_lang_slug Optional current language slug.
	 * @return string Block HTML.
	 */
	private function render_list_layout( $attributes, $languages, $current_lang_slug = null ) {
		$layout              = isset( $attributes['dropdown'] ) ? $attributes['dropdown'] : 'dropdown';
		$show_names          = ! empty( $attributes['show_names'] );
		$show_flags          = ! empty( $attributes['show_flags'] );
		$show_language_codes = ! empty( $attributes['show_language_codes'] );

		$unique_class       = $this->get_unique_block_id( $attributes );
		$layout_class       = 'lsdp-layout-' . esc_attr( $layout );
		$custom_class       = isset( $attributes['className'] ) ? $attributes['className'] : '';
		$wrapper_class      = trim( $unique_class . ' ' . $layout_class . ' ' . $custom_class );
		$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $wrapper_class ) );
		$aria_label         = __( 'Choose a language', 'language-switcher-for-divi-polylang' );
		$switcher_output    = '';

		$spacing_css = $this->generate_spacing_css( $attributes, $unique_class );

		foreach ( $languages as $lang ) {
			$is_current = isset( $lang['slug'] ) && $lang['slug'] === $current_lang_slug;

			$switcher_output .= '<li class="lsdp-lang-item' . ( $is_current ? ' current-lang' : '' ) . '">';

			$link_attrs = array(
				'href' => esc_url( $lang['url'] ),
			);

			if ( $is_current ) {
				$link_attrs['aria-current'] = 'true';
			}

			if ( ! empty( $lang['locale'] ) ) {
				$link_attrs['lang']     = esc_attr( $lang['locale'] );
				$link_attrs['hreflang'] = esc_attr( $lang['locale'] );
			}

			$switcher_output .= '<a';
			foreach ( $link_attrs as $attr => $value ) {
				$switcher_output .= ' ' . $attr . '="' . $value . '"';
			}
			$switcher_output .= '>';

			if ( $show_flags ) {
				$custom_flag = LSDP_Common_Helpers::get_country_flag( $lang['flag'], $lang['name'] );
				if ( $custom_flag ) {
					$switcher_output .= '<div class="lsdp-lang-image">' . wp_kses_post( $custom_flag ) . '</div>';
				}
			}

			if ( $show_names && ! empty( $lang['name'] ) ) {
				$switcher_output .= '<div class="lsdp-lang-name">' . esc_html( $lang['name'] ) . '</div>';
			}

			if ( $show_language_codes && ! empty( $lang['slug'] ) ) {
				$switcher_output .= '<div class="lsdp-lang-code">' . esc_html( $lang['slug'] ) . '</div>';
			}

			$switcher_output .= '</a></li>';
		}

		return sprintf(
			'%s<nav role="navigation" aria-label="%s"><ul %s>%s</ul></nav>',
			$this->get_instance_style_tag( $spacing_css ),
			esc_attr( $aria_label ),
			$wrapper_attributes,
			$switcher_output
		);
	}

	/**
	 * Render custom dropdown (for Polylang)
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	private function render_custom_dropdown( $attributes ) {
		if ( ! function_exists( 'pll_the_languages' ) ) {
			return '';
		}

		$show_names             = ! empty( $attributes['show_names'] );
		$hide_current           = ! empty( $attributes['hide_current'] );
		$hide_if_no_translation = ! empty( $attributes['hide_if_no_translation'] );

		// Get current language slug (works in both frontend and editor contexts)
		$current_lang_slug = $this->get_current_language_slug();

		$args = array(
			'echo'                   => 0,
			'raw'                    => 1,
			'show_flags'             => 0,
			'show_names'             => $show_names,
			'hide_current'           => false,
			'hide_if_no_translation' => false,
		);

		$languages = pll_the_languages( $args );

		if ( empty( $languages ) || ! is_array( $languages ) ) {
			return '';
		}

		// Manually filter languages using our correct language detection (needed for editor/REST context)
		if ( $hide_current || $hide_if_no_translation ) {
			$languages = array_filter(
				$languages,
				function ( $lang ) use ( $hide_current, $hide_if_no_translation, $current_lang_slug ) {
					// Filter out current language if hide_current is enabled
					if ( $hide_current && isset( $lang['slug'] ) && $lang['slug'] === $current_lang_slug ) {
						return false;
					}
					// Filter out languages with no translation if hide_if_no_translation is enabled
					if ( $hide_if_no_translation && ! empty( $lang['no_translation'] ) ) {
						return false;
					}
					return true;
				}
			);
		}

		return $this->render_dropdown_layout( $attributes, $languages, $current_lang_slug );
	}

	/**
	 * Render dropdown layout - reusable for both Polylang and custom languages
	 *
	 * @param array  $attributes Block attributes.
	 * @param array  $languages Languages array in Polylang format.
	 * @param string $current_lang_slug Optional current language slug.
	 * @return string Block HTML.
	 */
	private function render_dropdown_layout( $attributes, $languages, $current_lang_slug = null ) {
		$show_names          = ! empty( $attributes['show_names'] );
		$show_flags          = ! empty( $attributes['show_flags'] );
		$show_language_codes = ! empty( $attributes['show_language_codes'] );

		wp_enqueue_script( 'lsdp-custom-dropdown' );

		$unique_class = $this->get_unique_block_id( $attributes );
		$unique_id    = 'lsdp-dropdown-' . substr( $unique_class, -8 );

		// Find current language
		$current_lang = null;
		if ( $current_lang_slug ) {
			foreach ( $languages as $lang ) {
				if ( isset( $lang['slug'] ) && $lang['slug'] === $current_lang_slug ) {
					$current_lang = $lang;
					break;
				}
			}
		}

		if ( ! $current_lang ) {
			$current_lang = reset( $languages );
		}

		// If no valid current language found, return empty
		if ( ! is_array( $current_lang ) || empty( $current_lang ) ) {
			return '';
		}

		$layout_class       = 'lsdp-layout-dropdown lsdp-custom-dropdown';
		$custom_class       = isset( $attributes['className'] ) ? $attributes['className'] : '';
		$wrapper_class      = trim( $unique_class . ' ' . $layout_class . ' ' . $custom_class );
		$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $wrapper_class ) );
		$aria_label         = __( 'Choose a language', 'language-switcher-for-divi-polylang' );

		$spacing_css = $this->generate_spacing_css( $attributes, $unique_class );

		$output  = $this->get_instance_style_tag( $spacing_css );
		$output .= '<div ' . $wrapper_attributes . '>';
		$output .= '<div class="lsdp-dropdown-container" id="' . esc_attr( $unique_id ) . '">';
		$output .= '<button type="button" class="lsdp-dropdown-button lsdp-lang-item" aria-haspopup="listbox" aria-expanded="false" aria-label="' . esc_attr( $aria_label ) . '">';

		if ( $show_flags ) {
			$custom_flag = LSDP_Common_Helpers::get_country_flag( $current_lang['flag'], $current_lang['name'] );
			if ( $custom_flag ) {
				$output .= '<div class="lsdp-lang-image">' . wp_kses_post( $custom_flag ) . '</div>';
			}
		}

		if ( $show_names && ! empty( $current_lang['name'] ) ) {
			$output .= '<div class="lsdp-lang-name">' . esc_html( $current_lang['name'] ) . '</div>';
		}

		if ( $show_language_codes && ! empty( $current_lang['slug'] ) ) {
			$output .= '<div class="lsdp-lang-code">' . esc_html( $current_lang['slug'] ) . '</div>';
		}

		$output .= '<span class="lsdp-dropdown-arrow" aria-hidden="true">&#9660;</span>';
		$output .= '</button>';
		$output .= '<ul class="lsdp-dropdown-menu" role="listbox" style="display: none;">';

		foreach ( $languages as $lang ) {
			// Skip the language shown in the dropdown button.
			if ( isset( $lang['slug'], $current_lang['slug'] ) && $lang['slug'] === $current_lang['slug'] ) {
				continue;
			}

			$classes = array( 'lsdp-dropdown-item' );

			$output .= '<li role="option" class="' . esc_attr( implode( ' ', $classes ) ) . '">';
			$output .= '<a href="' . esc_url( $lang['url'] ) . '">';

			if ( $show_flags ) {
				$custom_flag = LSDP_Common_Helpers::get_country_flag( $lang['flag'], $lang['name'] );
				if ( $custom_flag ) {
					$output .= '<div class="lsdp-lang-image">' . wp_kses_post( $custom_flag ) . '</div>';
				}
			}

			if ( $show_names && ! empty( $lang['name'] ) ) {
				$output .= '<div class="lsdp-lang-name">' . esc_html( $lang['name'] ) . '</div>';
			}

			if ( $show_language_codes && ! empty( $lang['slug'] ) ) {
				$output .= '<div class="lsdp-lang-code">' . esc_html( $lang['slug'] ) . '</div>';
			}

			$output .= '</a></li>';
		}

		$output .= '</ul></div></div>';

		return $output;
	}
}
