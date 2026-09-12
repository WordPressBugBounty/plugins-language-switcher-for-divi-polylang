<?php
/**
 * Divi 5 integration bootstrap.
 *
 * @package LanguageSwitcherForDiviPolylang
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}
// Load the Divi 5 server integration.
require_once LSDP_DIR . 'divi-5/vendor/autoload.php';
require_once LSDP_DIR . 'divi-5/server/Conversion/AttributeConversion.php';
require_once LSDP_DIR . 'divi-5/server/Modules/Modules.php';


/**
 * Registers assets used by the native Divi 5 module.
 */
class LSDP_Divi5 {

	/** Register Divi-specific asset hooks. */
	public function __construct() {

		add_action( 'divi_visual_builder_assets_before_enqueue_scripts', array( $this, 'enqueue_visual_builder_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ), 5 );
	}

	/**
	 * Enqueue Frontend Assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_frontend_assets() {

		if ( ! function_exists( 'et_core_is_fb_enabled' ) ) {
			return;
		}

		// Frontend module assets belong on the public site or inside Divi Visual Builder only.
		if ( is_admin() && ! et_core_is_fb_enabled() ) {
			return;
		}

		wp_enqueue_style( 'lsdp-divi5-frontend-style', LSDP_URL . 'styles/style.min.css', array(), LSDP );
		wp_enqueue_style( 'lsdp-divi5-frontend-helper', LSDP_URL . 'assets/css/lsdphelper.css', array(), LSDP );
		if ( ! et_core_is_fb_enabled() ) {
			wp_enqueue_script( 'lsdp-module-js', LSDP_URL . 'assets/js/lsdp_module_frontend.js', array(), LSDP, true );
		}
	}

	/**
	 * Enqueue Divi 5 Visual Builder Assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_visual_builder_assets() {
		\ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
			array(
				'name'    => 'lsdp-divi5-visual-builder-script',
				'version' => LSDP,
				'script'  => array(
					'src'                => LSDP_URL . 'divi-5/visual-builder/build/language-switcher-for-divi-polylang-build.js',
					'deps'               => array(
						'divi-module-library',
						'divi-vendor-wp-hooks',
						'react',
						'jquery-core',
						'divi-rest',
						'wp-hooks',
					),
					'enqueue_top_window' => false,
					'enqueue_app_window' => true,
				),
			)
		);
	}
}
