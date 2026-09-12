<?php
/**
 * Language Switcher Polylang Elementor Widget
 *
 * @package LanguageSwitcherPolylangElementorWidget
 * @since 1.0.0
 */

namespace LSDP\LanguageSwitcherPolylangElementorWidget;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSDP_Widget
 *
 * Main widget class for the Language Switcher Polylang Elementor widget.
 *
 * @since 1.0.0
 */
class LSDP_Widget extends Widget_Base {

	/**
	 * Constructor for the widget.
	 *
	 * @param array $data Widget data.
	 * @param array $args Widget arguments.
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style(
			'lsep-style',
			LSDP_URL . '/elementor/css/language-switcher-style.css',
			array(),
			LSDP
		);

		wp_register_script(
			'lsep-dropdown',
			LSDP_URL . 'elementor/js/lsdp-dropdown.js',
			array(),
			LSDP,
			true
		);

		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'lsep_language_switcher_icon_css' ) );
	}

	public function lsep_language_switcher_icon_css() {
		wp_enqueue_style( 'lsep-style' );

		$inline_css = "
        .lsep-widget-icon {
            display: inline-block;
            width: 25px;
            height: 25px;
            background-image: url('" . esc_url( LSDP_URL . '/assets/images/lang_switcher.svg' ) . "');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
    ";

		wp_add_inline_style( 'lsep-style', $inline_css );
	}



	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'lsep_widget';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Language Switcher', 'language-switcher-for-divi-polylang' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'lsep-widget-icon';
	}

	/**
	 * Get widget categories.
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'basic' );
	}

	/**
	 * Get widget style dependencies.
	 *
	 * @return array Widget style dependencies.
	 */
	public function get_style_depends() {
		return array( 'lsep-style' );
	}

	/**
	 * Get widget script dependencies.
	 *
	 * @return array Widget script dependencies.
	 */
	public function get_script_depends() {
		return array( 'lsep-dropdown' );
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'Language Switcher', 'language-switcher-for-divi-polylang' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'lsep_language_switcher_type',
			array(
				'label'   => __( 'Language Switcher Type', 'language-switcher-for-divi-polylang' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'dropdown'   => __( 'Dropdown', 'language-switcher-for-divi-polylang' ),
					'vertical'   => __( 'Vertical', 'language-switcher-for-divi-polylang' ),
					'horizontal' => __( 'Horizontal', 'language-switcher-for-divi-polylang' ),
				),
				'default' => 'dropdown',
			)
		);

		$this->add_control(
			'lsep_language_switcher_show_flags',
			array(
				'label'   => __( 'Show Flags', 'language-switcher-for-divi-polylang' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'lsep_language_switcher_show_names',
			array(
				'label'   => __( 'Show Language Names', 'language-switcher-for-divi-polylang' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'lsep_languages_switcher_show_code',
			array(
				'label'   => __( 'Show Language Codes', 'language-switcher-for-divi-polylang' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'lsep_language_switcher_hide_current_language',
			array(
				'label'   => __( 'Hide Current Language', 'language-switcher-for-divi-polylang' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		$this->add_control(
			'lsep_language_hide_untranslated_languages',
			array(
				'label'   => __( 'Hide Untranslated Languages', 'language-switcher-for-divi-polylang' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'no',
			)
		);

		if ( ! get_option( 'lsep_elementor_review_notice_dismiss' ) ) {
			$review_nonce = wp_create_nonce( 'lsep_elementor_review' );
			$url          = admin_url( 'admin-ajax.php' );
			$review_url   = 'https://wordpress.org/support/plugin/language-switcher-for-divi-polylang/reviews/#new-post';
			$html         = '<div class="lsep_elementor_review_wrapper">';
			$html        .= '<div class="lsep_elementor_review_msg">';
			$html        .= '<p class="lsep_elementor_review_intro">';
			$html        .= sprintf(
				/* translators: %s: plugin name in bold */
				esc_html__( 'Thanks for using %s! If you have a moment, could you kindly leave us a review? We\'d greatly appreciate it and it helps us improve our product.', 'language-switcher-for-divi-polylang' ),
				'<strong>' . esc_html__( 'Language Switcher for Polylang', 'language-switcher-for-divi-polylang' ) . '</strong>'
			);
			$html        .= '</p>';
			$html        .= '<p class="lsep_elementor_review_rating">';
			$html        .= '<a href="' . esc_url( $review_url ) . '" target="_blank" rel="noopener noreferrer">';
			$html        .= esc_html__( 'Share the love with a', 'language-switcher-for-divi-polylang' );
			$html        .= ' <span class="lsep_elementor_review_stars" aria-hidden="true">★★★★★</span> ';
			$html        .= esc_html__( 'rating.', 'language-switcher-for-divi-polylang' );
			$html        .= '</a></p>';
			$html        .= '</div>';
			$html        .= '<div id="lsep_elementor_review_dismiss" data-url="' . esc_url( $url ) . '" data-nonce="' . esc_attr( $review_nonce ) . '">';
			$html        .= esc_html__( 'Close Notice X', 'language-switcher-for-divi-polylang' );
			$html        .= '</div>';
			$html        .= '<div class="lsep_elementor_demo_btn">';
			$html        .= '<a href="' . esc_url( $review_url ) . '" target="_blank" rel="noopener noreferrer">';
			$html        .= esc_html__( 'Submit Review', 'language-switcher-for-divi-polylang' );
			$html        .= '</a></div></div>';

			$this->add_control(
				'lsep_review_notice',
				array(
					'name'            => 'lsep_review_notice',
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => $html,
					'content_classes' => 'lsep_elementor_review_notice',
				)
			);
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => __( 'Language Switcher Style', 'language-switcher-for-divi-polylang' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'lsep_language_switcher_alignment',
			array(
				'label'     => __( 'Switcher Alignment', 'language-switcher-for-divi-polylang' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'language-switcher-for-divi-polylang' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'language-switcher-for-divi-polylang' ),
						'icon'  => 'eicon-h-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'language-switcher-for-divi-polylang' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'   => 'left',
				'condition' => array(
					'lsep_language_switcher_type' => 'dropdown',
				),
				'selectors' => array(
					'{{WRAPPER}} .lsep-main-wrapper' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'lsep_language_swtcher_flag_ratio',
			array(
				'label'        => __( 'Flag Ratio', 'language-switcher-for-divi-polylang' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => array(
					'11' => __( '1/1', 'language-switcher-for-divi-polylang' ),
					'43' => __( '4/3', 'language-switcher-for-divi-polylang' ),
				),
				'prefix_class' => 'lsep-switcher--aspect-ratio-',
				'default'      => '43',
				'selectors'    => array(
					'{{WRAPPER}} .lsep-lang-image' => '--lsep-flag-ratio: {{VALUE}};',
				),
				'condition'    => array(
					'lsep_language_switcher_show_flags' => 'yes',
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_flag_width',
			array(
				'label'      => __( 'Flag Width', 'language-switcher-for-divi-polylang' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 20,
				),
				'selectors'  => array(
					'{{WRAPPER}}.lsep-switcher--aspect-ratio-11 .lsep-lang-image img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.lsep-switcher--aspect-ratio-43 .lsep-lang-image img' => 'width: {{SIZE}}{{UNIT}}; height: calc({{SIZE}}{{UNIT}} * 0.75);',
				),
				'condition'  => array(
					'lsep_language_switcher_show_flags' => 'yes',
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_flag_radius',
			array(
				'label'      => __( 'Flag Radius', 'language-switcher-for-divi-polylang' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
					'%'  => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 0,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lsep-lang-image img' => '--lsep-flag-radius: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'lsep_language_switcher_show_flags' => 'yes',
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_margin',
			array(
				'label'      => esc_html__( 'Margin', 'language-switcher-for-divi-polylang' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'default'    => array(
					'top'    => 0,
					'right'  => 0,
					'bottom' => 0,
					'left'   => 0,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					// '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_padding',
			array(
				'label'      => __( 'Padding', 'language-switcher-for-divi-polylang' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'default'    => array(
					'top'    => 10,
					'right'  => 10,
					'bottom' => 10,
					'left'   => 10,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'     => 'lsep_language_switcher_border',
				'label'    => __( 'Border', 'language-switcher-for-divi-polylang' ),
				'selector' => '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal li a, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical li a',
			)
		);

		$this->add_control(
			'lsep_language_switcher_border_radius',
			array(
				'label'      => __( 'Border Radius', 'language-switcher-for-divi-polylang' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'default'    => array(
					'top'    => 0,
					'right'  => 0,
					'bottom' => 0,
					'left'   => 0,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-language-list' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),

			)
		);
		$this->start_controls_tabs( 'lsep_language_switcher_style_tabs' );
		$this->start_controls_tab(
			'lsep_language_switcher_style_tab_normal',
			array(
				'label' => __( 'Normal', 'language-switcher-for-divi-polylang' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'lsep_language_switcher_typography',
				'label'    => __( 'Typography', 'language-switcher-for-divi-polylang' ),
				'selector' => '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-active-language a div:not(.lsep-lang-image), {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item a, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a',
			)
		);
		$this->add_control(
			'lsep_language_switcher_background_color',
			array(
				'label'     => __( 'Switcher Background Color', 'language-switcher-for-divi-polylang' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown' => '--lsep-normal-bg-color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown ul li' => '--lsep-normal-bg-color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a' => '--lsep-normal-bg-color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a' => '--lsep-normal-bg-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_text_color',
			array(
				'label'     => __( 'Switcher Text Color', 'language-switcher-for-divi-polylang' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown' => '--lsep-normal-text-color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal' => '--lsep-normal-text-color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical' => '--lsep-normal-text-color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-active-language' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-active-language a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-name' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-code' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-name' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-code' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-name' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-code' => 'color: {{VALUE}};',
				),
			)
		);
		$this->end_controls_tab();

		$this->start_controls_tab(
			'lsep_language_switcher_style_tab_hover',
			array(
				'label' => __( 'Hover', 'language-switcher-for-divi-polylang' ),
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'lsep_language_switcher_typography_hover',
				'label'    => __( 'Typography', 'language-switcher-for-divi-polylang' ),
				'selector' => '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-active-language:hover,{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item a:hover, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a:hover, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a:hover',
			)
		);
		$this->add_control(
			'lsep_language_switcher_background_color_hover',
			array(
				'label'     => __( 'Switcher Background Color', 'language-switcher-for-divi-polylang' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown:hover' => '--lsep-normal-bg-color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown ul li:hover' => '--lsep-normal-bg-color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a:hover' => '--lsep-normal-bg-color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a:hover' => '--lsep-normal-bg-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_text_color_hover',
			array(
				'label'     => __( 'Switcher Text Color', 'language-switcher-for-divi-polylang' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown:hover' => '--lsep-normal-text-color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown:hover .lsep-active-language' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown:hover .lsep-active-language a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item:hover a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item:hover .lsep-lang-name' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item:hover .lsep-lang-code' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a:hover' => '--lsep-normal-text-color: {{VALUE}}; color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a:hover .lsep-lang-name' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a:hover .lsep-lang-code' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a:hover' => '--lsep-normal-text-color: {{VALUE}}; color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a:hover .lsep-lang-name' => 'color: {{VALUE}};',
					'{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a:hover .lsep-lang-code' => 'color: {{VALUE}};',
				),
			)
		);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_dropdown_style',
			array(
				'label'     => __( 'Dropdown Style', 'language-switcher-for-divi-polylang' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'lsep_language_switcher_type' => 'dropdown',
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_dropown_direction',
			array(
				'label'        => __( 'Dropdown Direction', 'language-switcher-for-divi-polylang' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => array(
					'up'   => __( 'Up', 'language-switcher-for-divi-polylang' ),
					'down' => __( 'Down', 'language-switcher-for-divi-polylang' ),
				),
				'default'      => 'down',
				'condition'    => array(
					'lsep_language_switcher_type' => 'dropdown',
				),
				'prefix_class' => 'lsep-dropdown-direction-',
			)
		);

		$this->add_control(
			'lsep_language_switcher_icon',
			array(
				'label'                  => __( 'Switcher Icon', 'language-switcher-for-divi-polylang' ),
				'type'                   => Controls_Manager::ICONS,
				'default'                => array(
					'value'   => 'fas fa-caret-down',
					'library' => 'fa-solid',
				),
				'include'                => array( 'fa-solid', 'fa-regular', 'fa-brands' ),
				'exclude_inline_options' => 'svg',
				'label_block'            => false,
				'skin'                   => 'inline',
				'condition'              => array(
					'lsep_language_switcher_type' => 'dropdown',
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_icon_size',
			array(
				'label'      => __( 'Icon Size', 'language-switcher-for-divi-polylang' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
					'%'  => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'condition'  => array(
					'lsep_language_switcher_type' => 'dropdown',
				),
				'selectors'  => array(
					'{{WRAPPER}} .lsep-dropdown-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_icon_color',
			array(
				'label'     => __( 'Icon Color', 'language-switcher-for-divi-polylang' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => array(
					'lsep_language_switcher_type' => 'dropdown',
				),
				'selectors' => array(
					'{{WRAPPER}} .lsep-dropdown-icon' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_icon_spacing',
			array(
				'label'      => __( 'Icon Spacing', 'language-switcher-for-divi-polylang' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'condition'  => array(
					'lsep_language_switcher_type' => 'dropdown',
				),
				'selectors'  => array(
					'{{WRAPPER}} .lsep-dropdown-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_dropdwon_spacing',
			array(
				'label'      => __( 'Dropdown Spacing', 'language-switcher-for-divi-polylang' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 0,
				),
				'selectors'  => array(
					'{{WRAPPER}}.lsep-dropdown-direction-down .lsep-wrapper.dropdown ul' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.lsep-dropdown-direction-up .lsep-wrapper.dropdown ul' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			array(
				'name'           => 'lsep_language_switcher_dropdown_list_border',
				'label'          => __( 'Dropdown List Border', 'language-switcher-for-divi-polylang' ),
				'separator'      => 'before',
				'selector'       => '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown ul',
				'fields_options' => array(
					'border' => array(
						'label' => __( 'Dropdown List Border', 'language-switcher-for-divi-polylang' ),
					),
					'width'  => array(
						'label' => __( 'Border Width', 'language-switcher-for-divi-polylang' ),
					),
					'color'  => array(
						'label' => __( 'Border Color', 'language-switcher-for-divi-polylang' ),
					),
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_dropdown_language_item_separator',
			array(
				'label'      => __( 'Language Item Separator', 'language-switcher-for-divi-polylang' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .lsep-wrapper.dropdown ul.lsep-language-list li.lsep-lang-item:not(:last-child)' => 'border-bottom: {{SIZE}}{{UNIT}} solid;',
				),
			)
		);

		$this->add_control(
			'lsep_language_switcher_dropdown_language_item_separator_color',
			array(
				'label'     => __( 'Separator Color', 'language-switcher-for-divi-polylang' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .lsep-wrapper.dropdown ul.lsep-language-list li.lsep-lang-item:not(:last-child)' => 'border-bottom-color: {{VALUE}};',
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Localize Polylang data for the widget.
	 *
	 * @param array $data Data to be localized.
	 * @return array Localized data.
	 */
	public function lsep_localize_polylang_data( $data ) {
		// Get the global Polylang object
		global $polylang;
		$lsep_polylang = $polylang;
		$data          = array();
		if ( isset( $lsep_polylang ) ) {
			try {
				require_once LSDP_DIR . 'helpers/class-lsdp-common-helpers.php';
				if ( function_exists( 'pll_the_languages' ) && function_exists( 'pll_current_language' ) ) {
					$languages = pll_the_languages( array( 'raw' => 1 ) );
					if ( empty( $languages ) ) {
						return $data; // If no languages, exit early
					}
					$lang_curr = strtolower( pll_current_language() );
					$languages = array_map(
						function ( $language ) {
							$lang_url = isset( $language['url'] ) ? (string) $language['url'] : '';
							return array(
								'flagCode'       => esc_html( \LSDP_Common_Helpers::get_flag_code( $language['flag'] ?? '' ) ),
								'slug'           => esc_html( $language['slug'] ?? '' ),
								'name'           => esc_html( $language['name'] ?? '' ),
								'no_translation' => esc_html( $language['no_translation'] ?? '' ),
								'url'            => esc_url( $lang_url ),
								'flag'           => esc_html( $language['flag'] ?? '' ),
							);
						},
						$languages
					);

					$custom_data      = array(
						'lsepLanguageData' => $languages,
						'lsepCurrentLang'  => esc_html( $lang_curr ),
						'lsepPluginUrl'    => esc_url( LSDP_URL ),
					);
					$custom_data_json = $custom_data;

					$data['lsepGlobalObj'] = $custom_data_json;
				}
			} catch ( \Exception $e ) {
				// Handle exception if needed
			}
		}
		return $data;
	}

	/**
	 * Render the widget output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_active_settings();

		// Get the localized data
		$data      = $this->lsep_localize_polylang_data( array() );
		$lsep_data = isset( $data['lsepGlobalObj'] ) ? $data['lsepGlobalObj'] : array();
		if ( empty( $lsep_data ) ) {
			return;
		}
		if ( 'yes' !== $settings['lsep_language_switcher_show_flags'] && 'yes' !== $settings['lsep_language_switcher_show_names'] && 'yes' !== $settings['lsep_languages_switcher_show_code'] ) {
			return;
		}
		$switcher_html  = '';
		$switcher_html .= '<div class="lsep-main-wrapper">';
		if ( 'dropdown' === $settings['lsep_language_switcher_type'] ) {
			$switcher_html .= '<div class="lsep-wrapper dropdown">';
			$switcher_html .= $this->lsep_render_dropdown_switcher( $settings, $lsep_data );
			$switcher_html .= '</div>';
		} else {
			$switcher_html .= '<div class="lsep-wrapper ' . esc_attr( $settings['lsep_language_switcher_type'] ) . '">';
			$switcher_html .= $this->lsep_render_switcher( $settings, $lsep_data );
			$switcher_html .= '</div>';
		}
		$switcher_html .= '</div>';
		echo wp_kses_post( $switcher_html );
	}

	/**
	 * Render dropdown switcher.
	 *
	 * @param array $settings Widget settings.
	 * @param array $lsep_data Language data.
	 * @return string HTML output.
	 */
	public function lsep_render_dropdown_switcher( $settings, $lsep_data ) {
		$languages    = $lsep_data['lsepLanguageData'];
		$current_lang = $lsep_data['lsepCurrentLang'];

		// If current language should be shown, use it as active language
		if ( 'yes' !== $settings['lsep_language_switcher_hide_current_language'] ) {
			$active_language = isset( $languages[ $current_lang ] ) ? $languages[ $current_lang ] : null;
		} else {
			// Find first available language that's not the current language
			$active_language = null;
			foreach ( $languages as $lang ) {
				if ( $current_lang !== $lang['slug'] &&
					! ( $lang['no_translation'] && 'yes' === $settings['lsep_language_hide_untranslated_languages'] ) ) {
					$active_language = $lang;
					break;
				}
			}
		}

		// If no language found, return empty
		if ( ! $active_language ) {
			return '';
		}

		$active_html    = self::lsep_get_active_language_html( $active_language, $settings );
		$languages_html = '';

		foreach ( $languages as $lang ) {
			// Skip if it's the current language (when hidden), active language, or untranslated language
			if ( ( $current_lang === $lang['slug'] && 'yes' === $settings['lsep_language_switcher_hide_current_language'] ) ||
				$active_language['slug'] === $lang['slug'] ||
				( $lang['no_translation'] && 'yes' === $settings['lsep_language_hide_untranslated_languages'] ) ) {
				continue;
			}

			$flag_icon       = \LSDP_Common_Helpers::get_country_flag( $lang['flag'], $lang['name'] );
			$languages_html .= '<li class="lsep-lang-item">';
			$languages_html .= '<a href="' . esc_url( (string) ( $lang['url'] ?? '' ) ) . '">';
			if ( ! empty( $settings['lsep_language_switcher_show_flags'] ) && 'yes' === $settings['lsep_language_switcher_show_flags'] ) {
				$languages_html .= '<div class="lsep-lang-image">' . wp_kses_post( $flag_icon ) . '</div>';
			}
			if ( ! empty( $settings['lsep_language_switcher_show_names'] ) && 'yes' === $settings['lsep_language_switcher_show_names'] ) {
				$languages_html .= '<div class="lsep-lang-name">' . esc_html( $lang['name'] ) . '</div>';
			}
			if ( ! empty( $settings['lsep_languages_switcher_show_code'] ) && 'yes' === $settings['lsep_languages_switcher_show_code'] ) {
				$languages_html .= '<div class="lsep-lang-code">' . esc_html( $lang['slug'] ) . '</div>';
			}
			$languages_html .= '</a></li>';
		}

		return $active_html . '<ul class="lsep-language-list">' . $languages_html . '</ul>';
	}

	/**
	 * Get active language HTML.
	 *
	 * @param array  $language Language data.
	 * @param array  $settings Widget settings.
	 * @return string HTML output.
	 */
	public static function lsep_get_active_language_html( $language, $settings ) {
		$html      = '<span class="lsep-active-language">';
		$html     .= '<a href="' . esc_url( (string) ( $language['url'] ?? '' ) ) . '">';
		$flag_icon = \LSDP_Common_Helpers::get_country_flag( $language['flag'], $language['name'] );
		if ( ! empty( $settings['lsep_language_switcher_show_flags'] ) && 'yes' === $settings['lsep_language_switcher_show_flags'] ) {
			$html .= '<div class="lsep-lang-image">' . wp_kses_post( $flag_icon ) . '</div>';
		}
		if ( ! empty( $settings['lsep_language_switcher_show_names'] ) && 'yes' === $settings['lsep_language_switcher_show_names'] ) {
			$html .= '<div class="lsep-lang-name">' . esc_html( $language['name'] ) . '</div>';
		}
		if ( ! empty( $settings['lsep_languages_switcher_show_code'] ) && 'yes' === $settings['lsep_languages_switcher_show_code'] ) {
			$html .= '<div class="lsep-lang-code">' . esc_html( $language['slug'] ) . '</div>';
		}
		if ( ! empty( $settings['lsep_language_switcher_icon'] ) ) {
			$html .= '<i class="lsep-dropdown-icon ' . esc_attr( $settings['lsep_language_switcher_icon']['value'] ) . '"></i>';
		}
		$html .= '</a></span>';
		return $html;
	}

	/**
	 * Render Vertcal and Horizontal switcher.
	 *
	 * @param array $settings Widget settings.
	 * @param array $lsep_data Language data.
	 * @return string HTML output.
	 */
	public static function lsep_render_switcher( $settings, $lsep_data ) {
		$html         = '';
		$languages    = $lsep_data['lsepLanguageData'];
		$current_lang = $lsep_data['lsepCurrentLang'];
		foreach ( $languages as $lang ) {
			if ( ( $current_lang === $lang['slug'] && 'yes' === $settings['lsep_language_switcher_hide_current_language'] ) ||
				( $lang['no_translation'] && 'yes' === $settings['lsep_language_hide_untranslated_languages'] ) ) {
				continue;
			}

			$flag_icon    = \LSDP_Common_Helpers::get_country_flag( $lang['flag'], $lang['name'] );
			$anchor_open  = '<a href="' . esc_url( (string) ( $lang['url'] ?? '' ) ) . '">';
			$anchor_close = '</a>';

			$html .= '<li class="lsep-lang-item">';
			$html .= $anchor_open;
			if ( ! empty( $settings['lsep_language_switcher_show_flags'] ) && 'yes' === $settings['lsep_language_switcher_show_flags'] ) {
				$html .= '<div class="lsep-lang-image">' . wp_kses_post( $flag_icon ) . '</div>';
			}
			if ( ! empty( $settings['lsep_language_switcher_show_names'] ) && 'yes' === $settings['lsep_language_switcher_show_names'] ) {
				$html .= '<div class="lsep-lang-name">' . esc_html( $lang['name'] ) . '</div>';
			}
			if ( ! empty( $settings['lsep_languages_switcher_show_code'] ) && 'yes' === $settings['lsep_languages_switcher_show_code'] ) {
				$html .= '<div class="lsep-lang-code">' . esc_html( $lang['slug'] ) . '</div>';
			}
			$html .= $anchor_close;
			$html .= '</li>';
		}

		if ( empty( $html ) ) {
			return '';
		}

		return '<ul class="lsep-language-list">' . $html . '</ul>';
	}
}
