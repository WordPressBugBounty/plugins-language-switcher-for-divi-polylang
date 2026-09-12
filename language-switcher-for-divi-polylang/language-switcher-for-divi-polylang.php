<?php
/*
Plugin Name: Language Switcher for Polylang – Elementor, Gutenberg, & Divi
Plugin URI:  https://wordpress.org/plugins/language-switcher-for-divi-polylang
Description: Language Switcher for Polylang – Elementor, Gutenberg, & Divi to use added language switcher in your page or divi header menu
Version:     1.1.1
Requires at least: 5.0
Requires PHP: 7.2
Author:      Coolplugins
Author URI:  https://coolplugins.net/?utm_source=lspd_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=plugins_list
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: language-switcher-for-divi-polylang
Requires Plugins: polylang

Language Switcher for Polylang – Elementor, Gutenberg, & Divi is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Language Switcher for Polylang – Elementor, Gutenberg, & Divi is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Language Switcher for Polylang – Elementor, Gutenberg, & Divi. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
* @package LanguageSwitcherForDiviPolylang
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LSDP', '1.1.1' );
define( 'LSDP_DIR', plugin_dir_path( __FILE__ ) );
define( 'LSDP_URL', plugin_dir_url( __FILE__ ) );
define( 'LSDP_MODULE_URL', LSDP_URL . 'includes/modules' );
define( 'LSDP_MODULE_DIR', LSDP_DIR . 'includes/modules' );
define( 'LSDP_FEEDBACK_API', 'https://feedback.coolplugins.net/' );

/**
 * Main plugin bootstrap.
 */
final class LANGUAGE_SWITCHER_FOR_DIVI_POLYLANG {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	private function __construct() {

		add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
		add_action( 'admin_init', array( $this, 'redirect_after_activation' ) );
		add_action( 'activate_language-switcher-for-elementor-polylang/language-switcher-for-elementor-polylang.php', array( $this, 'block_old_plugin_activation' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_action( 'divi_extensions_init', array( $this, 'initialize_divi_4' ) );
		add_filter( 'et_fb_backend_helpers', array( $this, 'localize_divi_data' ) );
	}
	/** Activation defaults. */
	public static function activate() {
		update_option( 'lsdp-v', LSDP );
		update_option( 'lsdp-type', 'FREE' );
		if ( ! get_option( 'lsdp_initial_save_version' ) ) {
			add_option( 'lsdp_initial_save_version', LSDP );
			add_option( 'lsdp_plugin_activation_redirect', true );
		}
		if ( ! get_option( 'lsdp-installDate' ) ) {
			add_option( 'lsdp-installDate', gmdate( 'Y-m-d H:i:s' ) );
		}

		// Elementor CSS/element cache still points at the old LSEP plugin URLs after migration.
		update_option( 'lsdp_need_elementor_cache_clear', '1' );
	}

	/** Load builder-independent features and optional integrations. */
	public function init() {
		require_once LSDP_DIR . 'helpers/class-lsdp-common-helpers.php';
		require_once LSDP_DIR . 'helpers/lsdp-block-helpers.php';

		if ( ! LSDP_Common_Helpers::is_dependencies_active() ) {
			add_action( 'admin_notices', array( $this, 'polylang_notice' ) );
			return;
		}

		// Divi builds its native module dependency tree while the theme loads.
		// Register these hooks now, before after_setup_theme, so Divi 5 can find them.
		$this->initialize_divi_5();
		$this->initialize_theme_builder_conditions();
		require_once LSDP_DIR . 'gutenberg/class-lsdp-language-switcher-block.php';
		LSDP_Language_Switcher_Block::get_instance();

		if ( LSDP_Common_Helpers::is_elementor_available() ) {
			require_once LSDP_DIR . 'elementor/class-lsdp-manager.php';
			require_once LSDP_DIR . 'elementor/class-lsdp-register-widget.php';
			$this->maybe_clear_elementor_cache();
		}

		if ( is_admin() ) {
			require_once LSDP_DIR . 'floating-switcher/class-lsdp-floating-switcher-settings.php';
			LSDP_Floating_Switcher_Settings::get_instance();
			require_once LSDP_DIR . 'admin/feedback/class-lsdp-feedback.php';
			require_once LSDP_DIR . 'admin/dashboard/class-lsdp-admin-dashboard.php';
			lsdp_register_admin_dashboard();
		} else {
			require_once LSDP_DIR . 'floating-switcher/class-lsdp-floating-switcher-frontend.php';
		}
	}

	public function initialize_theme_builder_conditions() {
		$divi_version = self::get_divi_theme_version();
		if ( ! $divi_version || version_compare( (string) $divi_version, '4.0', '<' ) ) {
			return;
		}

		require_once LSDP_DIR . 'theme-builder/class-lsdp-theme-builder-conditions.php';
		new LSDP_Theme_Builder_Conditions();
	}

	/**
	 * Schedule Elementor cache clear after LSEP → LSDP migration / activation.
	 *
	 * Editor re-renders live, but frontend pages/templates that use the switcher
	 * keep stale Elementor CSS / element cache until those posts are regenerated.
	 */
	public function maybe_clear_elementor_cache() {
		$needs_clear = ( '1' === (string) get_option( 'lsdp_need_elementor_cache_clear', '' ) );

		if ( ! $needs_clear && ! get_option( 'lsdp_elementor_cache_cleared' ) ) {
			// Existing sites that already activated after migrating from LSEP.
			$needs_clear = (bool) (
				get_option( 'lsep-v' )
				|| get_option( 'lsep_initial_save_version' )
			);
		}

		if ( ! $needs_clear ) {
			return;
		}

		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'elementor/loaded', array( $this, 'clear_elementor_cache' ), 99 );
			return;
		}

		$this->clear_elementor_cache();
	}

	/**
	 * Clear Elementor CSS/element cache only on posts that use the language switcher widget.
	 */
	public function clear_elementor_cache() {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		$post_ids = $this->get_elementor_posts_using_switcher();
		foreach ( $post_ids as $post_id ) {
			$this->clear_elementor_cache_for_post( (int) $post_id );
		}

		delete_option( 'lsdp_need_elementor_cache_clear' );
		update_option( 'lsdp_elementor_cache_cleared', '1', false );
	}

	/**
	 * Find Elementor posts/templates that contain the language switcher widget.
	 *
	 * Uses Elementor's compact `_elementor_controls_usage` meta (not full `_elementor_data` JSON).
	 *
	 * @return int[]
	 */
	private function get_elementor_posts_using_switcher() {
		global $wpdb;

		// Narrow candidates via the small controls-usage index + Elementor-built posts only.
		$candidate_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT cu.post_id
				FROM {$wpdb->postmeta} cu
				INNER JOIN {$wpdb->postmeta} em
					ON em.post_id = cu.post_id
					AND em.meta_key = '_elementor_edit_mode'
					AND em.meta_value = 'builder'
				WHERE cu.meta_key = '_elementor_controls_usage'
					AND cu.meta_value LIKE %s",
				'%' . $wpdb->esc_like( 'lsep_widget' ) . '%'
			)
		);

		$post_ids = array();

		foreach ( (array) $candidate_ids as $post_id ) {
			$post_id = (int) $post_id;
			if ( $post_id <= 0 ) {
				continue;
			}

			$usage = get_post_meta( $post_id, '_elementor_controls_usage', true );
			if ( is_array( $usage ) && isset( $usage['lsep_widget'] ) ) {
				$post_ids[] = $post_id;
			}
		}

		/**
		 * Filter Elementor post IDs whose switcher-related cache should be cleared.
		 *
		 * @param int[] $post_ids Post IDs containing the language switcher widget.
		 */
		return apply_filters( 'lsdp_elementor_switcher_cache_post_ids', $post_ids );
	}

	/**
	 * Clear Elementor CSS and element cache for a single post/template.
	 *
	 * @param int $post_id Post ID.
	 */
	private function clear_elementor_cache_for_post( $post_id ) {
		if ( $post_id <= 0 ) {
			return;
		}

		delete_post_meta( $post_id, '_elementor_css' );
		delete_post_meta( $post_id, '_elementor_element_cache' );

		try {
			$elementor = \Elementor\Plugin::$instance;

			if ( isset( $elementor->documents ) && method_exists( $elementor->documents, 'get' ) ) {
				$document = $elementor->documents->get( $post_id );
				if ( $document && method_exists( $document, 'delete_cache' ) ) {
					$document->delete_cache();
				}
			}

			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				$css_file = new \Elementor\Core\Files\CSS\Post( $post_id );
				if ( method_exists( $css_file, 'delete' ) ) {
					$css_file->delete();
				}
			}
		} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Safe to ignore; CSS regenerates on the next frontend view.
		}

		wp_cache_delete( $post_id, 'post_meta' );
	}

	/** Load the Divi 4 extension only when Divi fires its integration hook. */
	public function initialize_divi_4() {
		$divi_version = self::get_divi_theme_version();
		if ( $divi_version && version_compare( (string) $divi_version, '5.0', '<' ) ) {
			if ( file_exists( LSDP_DIR . 'includes/LanguageSwitcherForDiviPolylang.php' ) ) {
				require_once LSDP_DIR . 'includes/LanguageSwitcherForDiviPolylang.php';
			}
		}
	}

	/** Register optional Divi 5 support before the theme builds its module tree. */
	public function initialize_divi_5() {
		$divi_version = self::get_divi_theme_version();
		if ( $divi_version && version_compare( (string) $divi_version, '5.0', '>=' ) ) {
			require_once LSDP_DIR . 'divi-5/divi-5.php';
			new LSDP_Divi5();
		}
	}

	/**
	 * Supply language data to the Divi visual builder.
	 *
	 * @param array $data Existing visual builder data.
	 * @return array
	 */
	public function localize_divi_data( $data ) {
		if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() || ! function_exists( 'pll_the_languages' ) ) {
			return $data;
		}

		$languages = pll_the_languages( array( 'raw' => 1 ) );
		if ( empty( $languages ) || ! is_array( $languages ) ) {
			return $data;
		}

		$data['lsdpGlobalObj'] = array(
			'lsdpLanguageData' => array_map(
				static function ( $language ) {
					return array(
						'flagCode'       => LSDP_Common_Helpers::get_flag_code( $language['flag'] ),
						'slug'           => sanitize_key( $language['slug'] ),
						'name'           => sanitize_text_field( $language['name'] ),
						'no_translation' => ! empty( $language['no_translation'] ),
						'url'            => esc_url_raw( $language['url'] ),
					);
				},
				$languages
			),
			'lsdpCurrentLang'  => function_exists( 'pll_current_language' ) ? sanitize_key( pll_current_language() ) : '',
			'lsdpPluginUrl'    => esc_url_raw( LSDP_URL ),
		);

		return $data;
	}

	/** Show the sole dependency notice. */
	public function polylang_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Language Switcher for Polylang requires Polylang to be installed and activated.', 'language-switcher-for-divi-polylang' ) . '</p></div>';
	}

	/** Block activation of the old Elementor plugin if it's version 1.2.5 or lower. */
	public function block_old_plugin_activation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$plugin_path = 'language-switcher-for-elementor-polylang/language-switcher-for-elementor-polylang.php';
		
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
		$version     = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '';
		
		if ( version_compare( $version, '1.2.5', '<=' ) ) {
			deactivate_plugins( $plugin_path );

			$message = sprintf(
				/* translators: %s: successor plugin name */
				__( 'Language Switcher for Elementor & Polylang has been deprecated and replaced by %s. Please use that plugin instead. This plugin cannot be activated while it is installed.', 'language-switcher-for-divi-polylang' ),
				'<strong>Language Switcher for Polylang – Elementor, Gutenberg, & Divi</strong>'
			);

			wp_die(
				wp_kses(
					$message,
					array(
						'strong' => array(),
					)
				),
				esc_html__( 'Plugin activation blocked', 'language-switcher-for-divi-polylang' ),
				array( 'back_link' => true )
			);
		}
	}

	/** Redirect once after activation. */
	public function redirect_after_activation() {
		if ( ! get_option( 'lsdp_plugin_activation_redirect', false ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		delete_option( 'lsdp_plugin_activation_redirect' );
		wp_safe_redirect( admin_url( 'admin.php?page=lsdp-get-started' ) );
		exit;
	}

	/**
	 * Add the dashboard link on the Plugins screen.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=lsdp-get-started' ) ) . '">' . esc_html__( 'Get Started', 'language-switcher-for-divi-polylang' ) . '</a>';
		return $links;
	}

	/** Get singleton. */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get Divi theme version.
	 *
	 * @since 1.2.4
	 * @return string Divi theme version.
	 */
	public static function get_divi_theme_version() {
		if ( ! self::is_divi_theme_active( 'Divi' ) ) {
			return 0;
		}

		$theme = wp_get_theme();

		// When active theme is a child of Divi, use the parent (Divi) theme version.
		if ( $theme->parent() ) {
			return $theme->parent()->get( 'Version' );
		}

		return $theme->get( 'Version' );
	}

	/**
	 * Check if Divi theme is active.
	 *
	 * @since 1.2.4
	 * @return bool True if Divi theme is active.
	 */
	public static function is_divi_theme_active( $target ) {
		$theme = wp_get_theme();

		if (
			$theme->get( 'Name' ) === $target ||
			stripos( $theme->get( 'Template' ), $target ) !== false ||
			( $theme->parent() && stripos( $theme->parent()->get( 'Name' ), $target ) !== false )
		) {
			return true;
		}

		if ( apply_filters( 'divi_ghoster_ghosted_theme', '' ) === $target ) {
			return true;
		}

		return false;
	}
}

register_activation_hook( __FILE__, array( 'LANGUAGE_SWITCHER_FOR_DIVI_POLYLANG', 'activate' ) );
LANGUAGE_SWITCHER_FOR_DIVI_POLYLANG::get_instance();
