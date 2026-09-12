<?php
/**
 * Main plugin class for Language Switcher Polylang Elementor integration.
 *
 * @package LanguageSwitcherManagerPolylangElementor
 * @since 1.0.0
 */

namespace LSDP\LanguageSwitcherManagerPolylangElementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class LSDP_Manager
 *
 * Handles the integration between Polylang and Elementor for template translations.
 *
 * @since 1.0.0
 */
class LSDP_Manager {

	/**
	 * Current template ID being processed.
	 *
	 * @var int|null
	 */
	private $current_template_id;

	/**
	 * Constructor.
	 *
	 * Initializes the plugin by setting up necessary hooks and filters.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Check if required dependencies are active
		if ( ! \LSDP_Common_Helpers::is_dependencies_active() || ! \LSDP_Common_Helpers::is_elementor_available() ) {
			return;
		}
		add_filter( 'pll_get_post_types', array( $this, 'lsdp_register_supported_post_types' ), 10, 1 );
		add_filter( 'elementor/theme/get_location_templates/template_id', array( $this, 'lsdp_translate_template_id' ) );
		add_filter( 'elementor/theme/get_location_templates/condition_sub_id', array( $this, 'lsdp_translate_condition_sub_id' ), 10, 2 );
		add_filter( 'pre_do_shortcode_tag', array( $this, 'lsdp_handle_shortcode_translation' ), 10, 3 );
		add_action( 'elementor/frontend/widget/before_render', array( $this, 'lsdp_translate_widget_template_id' ) );
		add_action( 'elementor/documents/register_controls', array( $this, 'lsdp_add_language_panel_controls' ) );

		if ( \LSDP_Common_Helpers::lsdp_is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
			add_action( 'set_object_terms', array( $this, 'lsdp_update_conditions_on_translation_change' ), 10, 4 );
		}
	}

	/**
	 * Registers supported post types for Polylang translation.
	 *
	 * @since 1.0.0
	 *
	 * @param array $types Array of post types.
	 * @return array Modified array of post types.
	 */
	public function lsdp_register_supported_post_types( $types ) {
		$custom_post_types = array( 'elementor_library' );
		return array_merge( $types, array_combine( $custom_post_types, $custom_post_types ) );
	}

	/**
	 * Translates template ID based on current language.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id The template post ID.
	 * @return int Translated template ID.
	 */
	public function lsdp_translate_template_id( $post_id ) {
		// Get the language of the current page
		$page_lang = pll_get_post_language( get_the_ID() );

		// Get the translated template in current page's language (if exists)
		$translated_post_id = pll_get_post( $post_id, $page_lang );

		// If translated template exists, use it. Otherwise, fallback to default language template
		if ( $translated_post_id ) {
			$post_id = $translated_post_id;
		} else {
			// Fallback: get the template in the default language
			$default_lang        = pll_default_language();
			$default_template_id = pll_get_post( $post_id, $default_lang );

			if ( $default_template_id ) {
				$post_id = $default_template_id;
			}
			// Else fallback is original post_id (in case no default exists either)
		}

		$this->current_template_id = $post_id;

		return $post_id;
	}

	/**
	 * Translates condition sub ID based on current language.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $sub_id     The sub ID to translate.
	 * @param array $condition  The condition data.
	 * @return int Translated sub ID.
	 */
	public function lsdp_translate_condition_sub_id( $sub_id, $condition ) {
		if ( ! $sub_id ) {
			return $sub_id;
		}

		$default_lang = pll_default_language();
		$current_lang = pll_get_post_language( $this->current_template_id );

		if ( $current_lang && $current_lang !== $default_lang && pll_get_post( $this->current_template_id, $default_lang ) ) {
			if ( in_array( $condition['sub_name'], get_post_types(), true ) ) {
				$translated_sub_id = pll_get_post( $sub_id );
				$sub_id            = $translated_sub_id ? $translated_sub_id : $sub_id;
			} else {
				$translated_sub_id = pll_get_term( $sub_id );
				$sub_id            = $translated_sub_id ? $translated_sub_id : $sub_id;
			}
		}

		return $sub_id;
	}

	/**
	 * Handles translation of Elementor template shortcodes.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $short_circuit Whether to skip shortcode processing.
	 * @param string $tag    Shortcode tag.
	 * @param array  $attrs  Shortcode attributes.
	 * @return string|bool Processed shortcode or false.
	 */
	public function lsdp_handle_shortcode_translation( $short_circuit, $tag, $attrs ) {
		if ( 'elementor-template' !== $tag || isset( $attrs['skip'] ) ) {
			return $short_circuit;
		}

		$translated_id = pll_get_post( absint( $attrs['id'] ) );
		$attrs['id']   = $translated_id ? $translated_id : $attrs['id'];
		$attrs['skip'] = 1;

		$output = '';
		foreach ( $attrs as $key => $value ) {
			$output .= " $key=\"" . esc_attr( $value ) . '"';
		}

		return do_shortcode( '[elementor-template' . $output . ']' );
	}

	/**
	 * Updates conditions when translations change.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $post_id  Post ID.
	 * @param array  $terms    Terms.
	 * @param array  $tt_ids   Term taxonomy IDs.
	 * @param string $taxonomy Taxonomy name.
	 */
	public function lsdp_update_conditions_on_translation_change( $post_id, $terms, $tt_ids, $taxonomy ) {
		if ( 'post_translations' === $taxonomy && 'elementor_library' === get_post_type( $post_id ) && class_exists( '\\ElementorPro\\Plugin' ) ) {

			$theme_builder = \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'theme-builder' );
			if ( $theme_builder ) {
				$theme_builder->get_conditions_manager()->get_cache()->regenerate();
			}
		}
	}

	/**
	 * Translates widget template ID.
	 *
	 * @since 1.0.0
	 *
	 * @param \Elementor\Element_Base $element Element instance.
	 */
	public function lsdp_translate_widget_template_id( $element ) {
		if ( 'template' !== $element->get_name() ) {
			return;
		}

		$template_id            = $element->get_settings( 'template_id' );
		$translated_template_id = pll_get_post( $template_id );
		$template_id            = $translated_template_id ? $translated_template_id : $template_id;
		$element->set_settings( 'template_id', $template_id );
	}

	/**
	 * Adds language panel controls to Elementor document.
	 *
	 * @since 1.0.0
	 *
	 * @param \Elementor\Core\Base\Document $document Document instance.
	 */
	public function lsdp_add_language_panel_controls( $document ) {
		if ( ! method_exists( $document, 'get_main_id' ) ) {
			return;
		}

		$post_id           = $document->get_main_id();
		$languages         = pll_languages_list( array( 'fields' => '' ) );
		$translations      = pll_get_post_translations( $post_id );
		$current_lang_name = pll_get_post_language( $post_id, 'name' );

		$document->start_controls_section(
			'lsdp_language_panel_controls',
			array(
				'label' => esc_html__( 'Translations', 'language-switcher-for-divi-polylang' ),
				'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
			)
		);

		foreach ( $languages as $lang ) {
			$lang_slug = $lang->slug;
			if ( isset( $translations[ $lang_slug ] ) ) {
				$translated_post_id = (int) $translations[ $lang_slug ];
				$edit_link          = get_edit_post_link( $translated_post_id, 'raw' );

				// get_edit_post_link() can return null; esc_url( null ) triggers PHP 8.1+ ltrim deprecations.
				if ( empty( $edit_link ) ) {
					continue;
				}

				if ( get_post_meta( $translated_post_id, '_elementor_edit_mode', true ) ) {
					$edit_link = add_query_arg( 'action', 'elementor', $edit_link );
				}

				$document->add_control(
					"lsdp_elementor_edit_lang_{$lang_slug}",
					array(
						'type'            => \Elementor\Controls_Manager::RAW_HTML,
						'raw'             => sprintf(
							'<a href="%s" target="_blank"><i class="eicon-pencil"></i> %s - %s</a>',
							esc_url( $edit_link ),
							esc_html( get_the_title( $translated_post_id ) ),
							esc_html( $lang->name )
						),
						'content_classes' => 'elementor-control-field',
					)
				);
			} else {
				$create_link = add_query_arg(
					array(
						'post_type' => get_post_type( $post_id ),
						'from_post' => esc_attr( $post_id ),
						'new_lang'  => esc_attr( $lang_slug ),
						'_wpnonce'  => wp_create_nonce( 'new-post-translation' ),
					),
					admin_url( 'post-new.php' )
				);

				$document->add_control(
					"lsdp_elementor_add_lang_{$lang_slug}",
					array(
						'type'            => \Elementor\Controls_Manager::RAW_HTML,
						'raw'             => sprintf(
							'<a href="%s" target="_blank"><i class="eicon-plus"></i> %s</a>',
							esc_url( $create_link ),
							sprintf(
								/* translators: %s: Language name */
								__( 'Add translation - %s', 'language-switcher-for-divi-polylang' ),
								esc_html( $lang->name )
							)
						),
						'content_classes' => 'elementor-control-field',
					)
				);
			}
		}

		$document->end_controls_section();
	}
}

// Initialize the plugin
new LSDP_Manager();
