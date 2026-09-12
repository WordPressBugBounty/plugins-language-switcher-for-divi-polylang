<?php
/**
 * Floating Switcher Frontend Renderer
 *
 * Handles the complete rendering and display of the floating language switcher
 * on the frontend. Manages asset loading, HTML generation, styling, and
 * integration with Polylang for language data.
 *
 * @package    Language_Switcher_For_Elementor_Polylang
 * @subpackage Language_Switcher_For_Elementor_Polylang/floating-switcher
 * @since      1.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LSDP Floating Switcher Frontend Class
 *
 * Renders the floating language switcher on the frontend with full
 * support for responsive design, accessibility, and custom styling.
 *
 * @since 1.2.4
 */
class LSDP_Floating_Switcher_Frontend {

	/**
	 * Mobile layout breakpoint in pixels (matches admin preview).
	 *
	 * @since 1.2.5
	 * @var int
	 */
	const MOBILE_BREAKPOINT = 768;

	/**
	 * Singleton instance.
	 *
	 * @since 1.2.6
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Whether the floater markup was already output in this request.
	 *
	 * @since 1.2.6
	 * @var bool
	 */
	private static $rendered = false;

	/**
	 * Switcher configuration array
	 *
	 * @since 1.2.4
	 * @var array|null
	 */
	private $config;

	/**
	 * Get singleton instance.
	 *
	 * @since 1.2.6
	 * @return self
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
	 * Registers WordPress hooks for asset enqueuing and switcher rendering.
	 *
	 * @since 1.2.4
	 */
	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_floater' ), 99 );
	}

	/**
	 * Get Switcher Configuration
	 *
	 * @since 1.2.4
	 * @return array Switcher configuration array
	 */
	private function get_config() {
		if ( null === $this->config ) {
			$this->config = get_option( 'lsdp_floating_switcher_config', array() );
		}

		return $this->config;
	}

	/**
	 * Check if Floater is Enabled
	 *
	 * @since 1.2.4
	 * @return bool True if enabled, false otherwise
	 */
	private function is_enabled() {
		$config = $this->get_config();

		return ! empty( $config['enabled'] );
	}

	/**
	 * Whether the floater should load on the current frontend request.
	 *
	 * @since 1.2.6
	 * @return bool
	 */
	private function should_display() {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		if ( class_exists( 'LSDP_Common_Helpers' ) && LSDP_Common_Helpers::is_page_builder_preview() ) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueue Frontend Assets
	 *
	 * @since 1.2.4
	 */
	public function enqueue_assets() {
		if ( ! $this->should_display() ) {
			return;
		}

		$plugin_url = LSDP_URL;
		$version    = defined( 'LSDP' ) ? LSDP : '1.0.0';

		wp_enqueue_style(
			'lsdp-floating-switcher-frontend',
			$plugin_url . 'floating-switcher/css/lsdp-floating-switcher-frontend.css',
			array(),
			$version
		);

		$config = $this->get_config();
		if ( ! empty( $config['enableCustomCss'] ) && ! empty( $config['customCss'] ) ) {
			$custom_css = wp_strip_all_tags( $config['customCss'] );
			$custom_css = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $custom_css );

			wp_add_inline_style( 'lsdp-floating-switcher-frontend', $custom_css );
		}

		wp_enqueue_script(
			'lsdp-floating-switcher-js',
			$plugin_url . 'floating-switcher/js/lsdp-floating-switcher-frontend.js',
			array(),
			$version,
			true
		);

		wp_localize_script(
			'lsdp-floating-switcher-js',
			'lsdpFloaterFrontend',
			array(
				'mobileBreakpoint' => self::MOBILE_BREAKPOINT,
			)
		);
	}

	/**
	 * Render Floating Switcher
	 *
	 * @since 1.2.4
	 */
	public function render_floater() {
		if ( self::$rendered || ! $this->should_display() ) {
			return;
		}

		if ( ! function_exists( 'pll_the_languages' ) || ! function_exists( 'pll_current_language' ) ) {
			return;
		}

		$config         = $this->get_config();
		$desktop_layout = $config['layoutCustomizer']['desktop'] ?? array();
		$mobile_layout  = $config['layoutCustomizer']['mobile'] ?? $desktop_layout;
		$languages      = LSDP_Common_Helpers::get_floater_languages_raw();

		if ( empty( $languages ) ) {
			return;
		}

		$is_dropdown = ( 'dropdown' === $config['type'] );

		if ( ! $is_dropdown && ! LSDP_Common_Helpers::is_side_by_side_allowed() ) {
			$is_dropdown = true;
		}

		if ( ! $is_dropdown ) {
			$languages = LSDP_Common_Helpers::limit_languages_for_side_by_side( $languages );
		}

		$styles = $this->build_responsive_switcher_styles( $config, $desktop_layout, $mobile_layout );

		self::$rendered = true;

		$this->render_switcher_html(
			$languages,
			$desktop_layout,
			$mobile_layout,
			$styles,
			$is_dropdown
		);
	}

	/**
	 * Build responsive CSS custom properties for desktop and mobile layouts.
	 *
	 * @since 1.2.5
	 * @param array $config         Switcher configuration.
	 * @param array $desktop_layout Desktop layout settings.
	 * @param array $mobile_layout  Mobile layout settings.
	 * @return string Inline CSS custom properties.
	 */
	private function build_responsive_switcher_styles( $config, $desktop_layout, $mobile_layout ) {
		$is_large     = ( 'large' === $config['size'] );
		$shared_vars  = array(
			'--bg'                  => $config['bgColor'],
			'--bg-hover'            => $config['bgHoverColor'],
			'--text'                => $config['textColor'],
			'--text-hover'          => $config['textHoverColor'],
			'--border-color'        => $config['borderColor'],
			'--border-width'        => $config['borderWidth'] . 'px',
			'--border-radius'       => $this->build_radius( $config['borderRadius'] ),
			'--flag-radius'         => $config['flagRadius'] . 'px',
			'--flag-size'           => $is_large ? '20px' : '18px',
			'--aspect-ratio'        => ( 'rect' === $config['flagShape'] ) ? '4/3' : '1',
			'--font-size'           => $is_large ? '16px' : '14px',
			'--transition-duration' => '0.2s',
		);
		$desktop_vars = $this->build_layout_css_vars( 'desktop', $desktop_layout );
		$mobile_vars  = $this->build_layout_css_vars( 'mobile', $mobile_layout );
		$vars         = array_merge( $shared_vars, $desktop_vars, $mobile_vars );

		$style_pairs = array();
		foreach ( $vars as $key => $value ) {
			if ( preg_match( '/^--[a-z0-9-]+$/i', $key ) ) {
				$style_pairs[] = $key . ':' . esc_attr( $value );
			}
		}

		return implode( ';', $style_pairs );
	}

	/**
	 * Build viewport-specific layout CSS variables.
	 *
	 * @since 1.2.5
	 * @param string $viewport Desktop or mobile key prefix.
	 * @param array  $layout   Layout settings.
	 * @return array CSS variables.
	 */
	private function build_layout_css_vars( $viewport, $layout ) {
		$position       = $layout['position'] ?? 'bottom-right';
		$position_parts = explode( '-', $position );
		$vertical       = $position_parts[0] ?? 'bottom';
		$horizontal     = $position_parts[1] ?? 'right';

		return array(
			'--lsdp-' . $viewport . '-top'      => ( 'top' === $vertical ) ? '0px' : 'auto',
			'--lsdp-' . $viewport . '-bottom'   => ( 'bottom' === $vertical ) ? '0px' : 'auto',
			'--lsdp-' . $viewport . '-right'    => ( 'right' === $horizontal ) ? '10%' : 'auto',
			'--lsdp-' . $viewport . '-left'     => ( 'left' === $horizontal ) ? '10%' : 'auto',
			'--lsdp-' . $viewport . '-width'    => ( 'custom' === ( $layout['width'] ?? 'default' ) ) ? absint( $layout['customWidth'] ?? 216 ) . 'px' : 'auto',
			'--lsdp-' . $viewport . '-padding'  => ( 'custom' === ( $layout['padding'] ?? 'default' ) ) ? absint( $layout['customPadding'] ?? 0 ) . 'px' : '0px 0px',
			'--lsdp-' . $viewport . '-vertical' => $vertical,
		);
	}

	/**
	 * Get vertical position from a layout config.
	 *
	 * @since 1.2.5
	 * @param array $layout Layout settings.
	 * @return string top|bottom
	 */
	private function get_layout_vertical( $layout ) {
		$position = $layout['position'] ?? 'bottom-right';

		return explode( '-', $position )[0] ?? 'bottom';
	}

	/**
	 * Build Border Radius String
	 *
	 * @since 1.2.4
	 * @param array $radius_array Array of 4 radius values [TL, TR, BR, BL].
	 * @return string CSS border-radius value.
	 */
	private function build_radius( $radius_array ) {
		if ( ! is_array( $radius_array ) ) {
			return '8px 8px 0 0';
		}

		return implode(
			' ',
			array_map(
				function ( $radius ) {
					return absint( $radius ) . 'px';
				},
				$radius_array
			)
		);
	}

	/**
	 * Render Switcher HTML
	 *
	 * @since 1.2.4
	 * @param array  $languages       Language objects.
	 * @param array  $desktop_layout  Desktop layout settings.
	 * @param array  $mobile_layout   Mobile layout settings.
	 * @param string $styles          Inline CSS styles string.
	 * @param bool   $is_dropdown     Whether to render as dropdown.
	 */
	private function render_switcher_html( $languages, $desktop_layout, $mobile_layout, $styles, $is_dropdown ) {
		$current = $languages[0] ?? null;
		$others  = array_slice( $languages, 1 );

		if ( ! $current ) {
			return;
		}

		$layout_class = $is_dropdown ? 'lsdp-ls-dropdown' : 'lsdp-ls-inline';
		?>
		<nav class="lsdp-language-switcher lsdp-floating-switcher <?php echo esc_attr( $layout_class ); ?>"
			style="<?php echo esc_attr( $styles ); ?>"
			role="navigation"
			aria-label="<?php esc_attr_e( 'Website language selector', 'language-switcher-for-divi-polylang' ); ?>"
			data-lsdp-desktop-vertical="<?php echo esc_attr( $this->get_layout_vertical( $desktop_layout ) ); ?>"
			data-lsdp-mobile-vertical="<?php echo esc_attr( $this->get_layout_vertical( $mobile_layout ) ); ?>"
			data-no-translation>

			<?php if ( $is_dropdown ) : ?>
				<div class="lsdp-language-switcher-inner">
					<?php $this->render_language_item( $current, true, $desktop_layout, $mobile_layout ); ?>

					<?php if ( ! empty( $others ) ) : ?>
						<div class="lsdp-switcher-dropdown-list"
							role="group"
							aria-label="<?php esc_attr_e( 'Available languages', 'language-switcher-for-divi-polylang' ); ?>"
							hidden
							inert>
							<?php foreach ( $others as $lang ) : ?>
								<?php $this->render_language_item( $lang, false, $desktop_layout, $mobile_layout ); ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<div class="lsdp-language-switcher-inner">
					<?php
					foreach ( $languages as $lang ) :
						$this->render_language_item( $lang, false, $desktop_layout, $mobile_layout, $lang['is_current'] );
					endforeach;
					?>
				</div>
			<?php endif; ?>
		</nav>
		<?php
	}

	/**
	 * Render Language Item
	 *
	 * @since 1.2.4
	 * @param array  $lang            Language data array.
	 * @param bool   $as_control      Whether to render as button.
	 * @param array  $desktop_layout  Desktop layout settings.
	 * @param array  $mobile_layout   Mobile layout settings.
	 * @param bool   $is_current      Whether this is the current active language.
	 */
	private function render_language_item( $lang, $as_control, $desktop_layout, $mobile_layout, $is_current = false ) {
		$classes = array( 'lsdp-language-item' );

		if ( $as_control ) {
			$classes[] = 'lsdp-language-item__current';
		}

		if ( $is_current ) {
			$classes[] = 'lsdp-language-item__default';
		}

		$tag = $as_control ? 'div' : 'a';
		?>
		<<?php echo esc_attr( $tag ); ?>
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			<?php if ( 'a' === $tag ) : ?>
				href="<?php echo esc_url( $lang['url'] ); ?>"
				title="<?php echo esc_attr( $lang['name'] ); ?>"
			<?php else : ?>
				role="button"
				tabindex="0"
				aria-expanded="false"
				aria-label="<?php esc_attr_e( 'Change language', 'language-switcher-for-divi-polylang' ); ?>"
			<?php endif; ?>
			data-no-translation>
			<?php echo $this->render_responsive_language_labels( $lang, $desktop_layout, $mobile_layout ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $as_control ) : ?>
				<?php echo $this->render_dropdown_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</<?php echo esc_attr( $tag ); ?>>
		<?php
	}

	/**
	 * Render responsive language labels and flag markup.
	 *
	 * @since 1.2.5
	 * @param array $lang            Language data.
	 * @param array $desktop_layout  Desktop layout settings.
	 * @param array $mobile_layout   Mobile layout settings.
	 * @return string HTML output.
	 */
	private function render_responsive_language_labels( $lang, $desktop_layout, $mobile_layout ) {
		$html         = '';
		$desktop_name = LSDP_Common_Helpers::get_language_name( $lang['pll'], $desktop_layout['languageNames'] ?? 'full' );
		$mobile_name  = LSDP_Common_Helpers::get_language_name( $lang['pll'], $mobile_layout['languageNames'] ?? 'full' );
		$show_desktop = $this->should_show_language_names( $desktop_layout );
		$show_mobile  = $this->should_show_language_names( $mobile_layout );
		$flag_html    = $this->get_flag_html( $lang );

		$html .= $this->render_flag_slot(
			$flag_html,
			$desktop_layout['flagIconPosition'] ?? 'before',
			$mobile_layout['flagIconPosition'] ?? 'before',
			'before'
		);

		if ( $show_desktop || $show_mobile ) {
			if ( $show_desktop && $show_mobile && $desktop_name === $mobile_name ) {
				$html .= '<span class="lsdp-language-item-name">' . esc_html( $desktop_name ) . '</span>';
			} else {
				if ( $show_desktop ) {
					$html .= '<span class="lsdp-language-item-name lsdp-language-item-name-desktop">' . esc_html( $desktop_name ) . '</span>';
				}
				if ( $show_mobile ) {
					$html .= '<span class="lsdp-language-item-name lsdp-language-item-name-mobile">' . esc_html( $mobile_name ) . '</span>';
				}
			}
		}

		$html .= $this->render_flag_slot(
			$flag_html,
			$desktop_layout['flagIconPosition'] ?? 'before',
			$mobile_layout['flagIconPosition'] ?? 'before',
			'after'
		);

		return $html;
	}

	/**
	 * Render dropdown chevron for the current language control.
	 *
	 * @since 1.2.5
	 * @return string SVG markup.
	 */
	private function render_dropdown_arrow() {
		return '<svg class="lsdp-dropdown-arrow" width="12" height="8" viewBox="0 0 12 8" fill="none" aria-hidden="true">'
			. '<path d="M1 6.5L6 1.5L11 6.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>'
			. '</svg>';
	}

	/**
	 * Render a viewport-aware flag slot.
	 *
	 * @since 1.2.5
	 * @param string $flag_html    Flag markup.
	 * @param string $desktop_flag Desktop flag position.
	 * @param string $mobile_flag  Mobile flag position.
	 * @param string $position     before|after.
	 * @return string
	 */
	private function render_flag_slot( $flag_html, $desktop_flag, $mobile_flag, $position ) {
		if ( empty( $flag_html ) ) {
			return '';
		}

		$classes = array( 'lsdp-flag-slot' );

		if ( $position === $desktop_flag && 'hide' !== $desktop_flag ) {
			$classes[] = 'lsdp-flag-slot-desktop-' . sanitize_html_class( $position );
		}

		if ( $position === $mobile_flag && 'hide' !== $mobile_flag ) {
			$classes[] = 'lsdp-flag-slot-mobile-' . sanitize_html_class( $position );
		}

		if ( count( $classes ) === 1 ) {
			return '';
		}

		return '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $flag_html . '</span>';
	}

	/**
	 * Determine whether language names should be shown for a layout.
	 *
	 * @since 1.2.5
	 * @param array $layout Layout settings.
	 * @return bool
	 */
	private function should_show_language_names( $layout ) {
		return ! empty( $layout['languageNames'] ) && 'none' !== $layout['languageNames'];
	}

	/**
	 * Get Flag HTML
	 *
	 * @since 1.2.4
	 * @param array $lang Language data array.
	 * @return string Flag image HTML or empty string if no flag.
	 */
	private function get_flag_html( $lang ) {
		if ( empty( $lang['flag'] ) ) {
			return '';
		}

		$config      = $this->get_config();
		$shape_class = '';
		if ( 'square' === ( $config['flagShape'] ?? '' ) ) {
			$shape_class = 'lsdp-flag-square';
		} elseif ( 'rounded' === ( $config['flagShape'] ?? '' ) ) {
			$shape_class = 'lsdp-flag-rounded';
		}

		return sprintf(
			'<img src="%s" class="lsdp-flag-image %s" alt="%s" loading="lazy" decoding="async" />',
			esc_url( $lang['flag'] ),
			esc_attr( $shape_class ),
			esc_attr( $lang['name'] )
		);
	}
}

LSDP_Floating_Switcher_Frontend::get_instance();
