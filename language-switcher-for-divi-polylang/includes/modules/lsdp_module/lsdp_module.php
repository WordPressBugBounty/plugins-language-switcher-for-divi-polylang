<?php

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Direct access forbidden.' );
}
class LSDP_Module extends ET_Builder_Module {

	public $slug                   = 'language-switcher-for-divi-polylang';
	public $vb_support             = 'on';
	public $icon_path;
	private static $language_index = 0;
	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'Coolplugins',
		'author_uri' => 'https://coolplugins.net/?utm_source=lspd_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=dashboard',
	);
	
	public function init() {
		if(!et_core_is_fb_enabled()){
			wp_enqueue_script( 'lsdp-module-js', LSDP_URL . 'assets/js/lsdp_module_frontend.js', [], LSDP, true);
		}
		
		$this->name = esc_html__( 'Language Switcher', 'language-switcher-for-divi-polylang' );
		$this->icon_path = LSDP_DIR . 'assets/images/lang_switcher.svg';
		 // Toggle settings
		 $this->settings_modal_toggles = array(
			 'general'  => array(
				 'toggles' => array(
					 'main_content' => array(
						 'title' => esc_html__( 'Language Switcher', 'language-switcher-for-divi-polylang' ),
					 ),
				 ),
			 ),
			 'advanced' => array(
				 'toggles' => array(
					 'lsdp_flag_settings'       => array(
						 'title' => esc_html__( 'Flag', 'language-switcher-for-divi-polylang' ),
					 ),
					 'lsdp_text_settings'       => array(
						 'title' => esc_html__( 'Text', 'language-switcher-for-divi-polylang' ),
					 ),
					 'lsdp_background'          => array(
						 'title' => esc_html__( 'Background', 'language-switcher-for-divi-polylang' ),
					 ),
					 'lsdp_dropdown_background' => array(
						 'title' => esc_html__( 'Dropwdown Background', 'language-switcher-for-divi-polylang' ),
					 ),
				 ),
			 ),
		 );
	}

	public function get_advanced_fields_config() {

		$advanced_fields                   = array();
		$advanced_fields['background']     = false;
		$advanced_fields['animation']      = false;
		$advanced_fields['text']           = false;
		$advanced_fields['margin_padding'] = false;
		$advanced_fields['transform']      = false;
		// $advanced_fields['filters']         = false;
		$advanced_fields['box_shadow']     = false;
		$advanced_fields['border']     	   = false;
		// $advanced_fields['max_width'] 	   = false;
		$advanced_fields['fonts']['lsdp_text_settings'] = array(
			'label_prefix'        => esc_html__( 'Text', 'language-switcher-for-divi-polylang' ),
			'tab_slug'            => 'advanced',
			'toggle_slug'         => 'lsdp_text_settings',
			'css'          => array(
				'main'      => '%%order_class%% .lsdp-wrapper ul li.lsdp_active_lang .lsdp-lang-name, %%order_class%% .lsdp-wrapper ul li.lsdp_active_lang .lsdp-lang-code,   %%order_class%% .lsdp-wrapper ul li .lsdp-lang-name, %%order_class%% .lsdp-wrapper ul li .lsdp-lang-code, %%order_class%% .lsdp-wrapper.dropdown span .lsdp-lang-name, %%order_class%% .lsdp-wrapper.dropdown span .lsdp-lang-code, %%order_class%% .lsdp-wrapper ul li a,',
				'important' => 'all',
			),
			'hide_text_align'     => true,
			'hide_text_shadow'    => true,
		);
		// Configure the margin and padding for the container.
		$advanced_fields['margin_padding'] = array(
			'css'          => array(
				'main'      => '%%order_class%% .lsdp-wrapper ul li a, %%order_class%% .lsdp-wrapper.dropdown, %%order_class%% .lsdp-wrapper.dropdown ul li',
				'important' => true,
				
			),
			'toggle_slug'  => 'lsdp_background',
			'tab_slug'     => 'advanced',
		);

		return $advanced_fields;
	}

	public function get_fields() {
		return array(
			'lsdp_style'                         => array(
				'label'       => esc_html__( 'Layout Options', 'language-switcher-for-divi-polylang' ),
				'type'        => 'select',
				'default'     => 'dropdown',
				'options'     => array(
					'vertical'   => esc_html__( 'Vertical', 'language-switcher-for-divi-polylang' ),
					'horizontal' => esc_html__( 'Horizontal', 'language-switcher-for-divi-polylang' ),
					'dropdown'   => esc_html__( 'Dropdown', 'language-switcher-for-divi-polylang' ),
				),
				'toggle_slug' => 'main_content',
			),
			'lsdp_flag_visibility'               => array(
				'label'       => esc_html__( 'Display Flag', 'language-switcher-for-divi-polylang' ),
				'type'        => 'yes_no_button',
				'default'     => 'on',
				'options'     => array('on','off'),
				'toggle_slug' => 'main_content'
			),
			'lsdp_language_name_visibility'      => array(
				'label'       => esc_html__( 'Show Language Name', 'language-switcher-for-divi-polylang' ),
				'type'        => 'yes_no_button',
				'options'     => array('on','off'),
				'default'     => 'on',
				'toggle_slug' => 'main_content'
			),
			'lsdp_language_code_visibility'      => array(
				'label'       => esc_html__( 'Show Language Code', 'language-switcher-for-divi-polylang' ),
				'type'        => 'yes_no_button',
				'options'     => array('on','off'),
				'toggle_slug' => 'main_content'
			),
			'lsdp_current_lang_visibility'       => array(
				'label'       => esc_html__( 'Hide Current Language', 'language-switcher-for-divi-polylang' ),
				'type'        => 'yes_no_button',
				'options'     => array('on','off'),
				'toggle_slug' => 'main_content',
				'show_if'     => array(
					'lsdp_style' => array( 'horizontal', 'vertical' ),
				),
			),
			'lsdp_unstranslated_lang_visibility' => array(
				'label'       => esc_html__( 'Hide Untranslated Languages', 'language-switcher-for-divi-polylang' ),
				'type'        => 'yes_no_button',
				'options'     => array('on','off'),
				'toggle_slug' => 'main_content'
			),
			'lsdp_flag_ratio'                    => array(
				'label'       => esc_html__( 'Aspect Ratio', 'language-switcher-for-divi-polylang' ),
				'type'        => 'select',
				'default'     => 'auto',
				'options'     => array(
					'auto' => esc_html__( 'auto', 'language-switcher-for-divi-polylang' ),
					'1/1'  => esc_html__( '1:1', 'language-switcher-for-divi-polylang' ),
					'4/3'  => esc_html__( '4:3', 'language-switcher-for-divi-polylang' ),
				),
				'toggle_slug' => 'lsdp_flag_settings',
				'tab_slug'    => 'advanced'
			),
			'lsdp_flag_width'                    => array(
				'label'          => esc_html__( 'Flag Width', 'language-switcher-for-divi-polylang' ),
				'type'           => 'range',
				'default'        => '20px',
				'range_settings' => array(
					'min'  => '10px',
					'max'  => '100px',
					'step' => '1px',
				),
				'toggle_slug'    => 'lsdp_flag_settings',
				'tab_slug'       => 'advanced'
			),
			'lsdp_flag_radius'                   => array(
				'label'          => esc_html__( 'Flag Border Radius', 'language-switcher-for-divi-polylang' ),
				'type'           => 'range',
				'default'        => '0px',
				'range_settings' => array(
					'min'  => '0px',
					'max'  => '100px',
					'step' => '1px',
				),
				'toggle_slug'    => 'lsdp_flag_settings',
				'tab_slug'       => 'advanced'
			),

			'lsdp_bg_normal_color' => array(
				'label'       => esc_html__( 'Background', 'language-switcher-for-divi-polylang' ),
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'lsdp_background',
				'type'        => 'color-alpha',
				'responsive'      => true,
				'mobile_options'  => true,
				'hover'           => 'tabs'
			),
		);
	}

	public function render( $attrs, $content = null, $render_slug = null ) {
			$static_style_loader = new LSDP_STYLE_HELPERS( $attrs, $render_slug, self::$language_index );
			self::$language_index++;
			$style                 = ! isset( $attrs['lsdp_style'] ) ? 'dropdown' : $attrs['lsdp_style'];
			$flag_display          = ! isset( $attrs['lsdp_flag_visibility'] ) ? 'on' : $attrs['lsdp_flag_visibility'];
			$name_display          = ! isset( $attrs['lsdp_language_name_visibility'] ) ? 'on' : $attrs['lsdp_language_name_visibility'];
			$code_display          = ! isset( $attrs['lsdp_language_code_visibility'] ) ? 'off' : $attrs['lsdp_language_code_visibility'];
			$hide_current_lang     = ! isset( $attrs['lsdp_current_lang_visibility'] ) ? 'off' : $attrs['lsdp_current_lang_visibility'];
			$hide_untranslate_lang = ! isset( $attrs['lsdp_unstranslated_lang_visibility'] ) ? 'off' : $attrs['lsdp_unstranslated_lang_visibility'];
			$display_content       = in_array( 'on', array( $flag_display, $name_display, $code_display ) ) || in_array( 'off', array( $hide_current_lang, $hide_untranslate_lang ) );

			if ( $display_content ) {

				$languages = pll_the_languages( array( 'raw' => 1 ) );
				$lang_curr = strtolower( pll_current_language() );

				$html        = '';
				$active_span = '';

				if ( $style === 'dropdown' ) {
					$active_flag_icon = LSDP_Common_Helpers::get_country_flag( $languages[ $lang_curr ]['flag'], $languages[ $lang_curr ]['name'] );
					$active_span       = '<span><a href="' . esc_url( $languages[ $lang_curr ]['url'] ) . '">';
					if ( 'on' === $flag_display ) {
						$active_span .= sprintf(
							'<div class="lsdp-lang-image">%s</div>',
							wp_kses_post( $active_flag_icon )
						);
					}

					if ( 'on' === $name_display ) {
						$active_span .= sprintf(
							'<div class="lsdp-lang-name">%s</div>',
							esc_html( $languages[ $lang_curr ]['name'] )
						);
					}

					if ( 'on' === $code_display ) {
						$active_span .= sprintf(
							'<div class="lsdp-lang-code">%s</div>',
							esc_html( $languages[ $lang_curr ]['slug'] )
						);
					}
					$active_span .= '</a></span>';
				}

				foreach ( $languages as $lang ) {

					if ( $lang_curr === $lang['slug'] && 'dropdown' === $style ) {
						continue;
					}

					if ( $lang_curr === $lang['slug'] && 'on' === $hide_current_lang ) {
						continue;
					}

					if ( $lang['no_translation'] && 'on' === $hide_untranslate_lang ) {
						continue;
					}
					$flag_icon    = LSDP_Common_Helpers::get_country_flag( $lang['flag'], $lang['name'] );
					$active_class = $lang_curr === $lang['slug'] ? 'lsdp_active_lang' : '';
					$html        .= '<li class="' . esc_attr( $active_class ) . '"><a href="' . esc_url( $lang['url'] ) . '">';
					if ( 'on' === $flag_display ) {
						$html .= sprintf(
							'<div class="lsdp-lang-image">%s</div>',
							wp_kses_post( $flag_icon )
						);
					}

					if ( 'on' === $name_display ) {
						$html .= sprintf(
							'<div class="lsdp-lang-name">%s</div>',
							esc_html( $lang['name'] )
						);
					}

					if ( 'on' === $code_display ) {
						$html .= sprintf(
							'<div class="lsdp-lang-code">%s</div>',
							esc_html( $lang['slug'] )
						);
					}
					$html .='</a></li>';
				}

				$output = sprintf(
					' <div id="lsdp-wrapper" class="lsdp-wrapper %1$s">
				%3$s
				<ul class="lsdp-language-list">
				%2$s
				</ul>
				</div>',
					esc_attr( $style ),
					$html,
					$active_span
				);

				return $output;
			}
	}
}

new LSDP_Module();
