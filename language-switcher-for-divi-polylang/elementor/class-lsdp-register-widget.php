<?php
/**
 * Language Switcher Polylang Elementor Widget
 *
 * @package           LanguageSwitcherPolylangElementor
 * @author            Your Name
 * @copyright         2024 Your Company
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSDP_Register_Widget
 *
 * Handles the registration of custom Elementor widget.
 */
class LSDP_Register_Widget {

	/**
	 * Constructor
	 *
	 * Initialize the class and set up hooks.
	 */
	public function __construct() {
		// Check if required dependencies are active
		if ( ! \LSDP_Common_Helpers::is_dependencies_active() || ! \LSDP_Common_Helpers::is_elementor_available() ) {
			return;
		}
		add_action( 'elementor/widgets/register', array( $this, 'lsdp_register_widgets' ) );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'add_editor_js' ) );
		add_action( 'wp_ajax_lsep_elementor_review_notice', array( $this, 'lsdp_elementor_review_notice' ) );
	}

	/**
	 * Register custom Elementor widgets
	 *
	 * @return void
	 */
	public function lsdp_register_widgets() {
		require_once LSDP_DIR . 'elementor/widget/class-lsdp-widget.php';
		\Elementor\Plugin::instance()->widgets_manager->register( new LSDP\LanguageSwitcherPolylangElementorWidget\LSDP_Widget() );
	}

	public function add_editor_js() {
		wp_enqueue_script( 'lsdp-editor-js', LSDP_URL . 'elementor/js/lsdp-editor.js', array( 'jquery' ), LSDP, true );
	}

	// Elementor Review notice ajax request function
	public function lsdp_elementor_review_notice() {
		if ( ! check_ajax_referer( 'lsep_elementor_review', 'nonce', false ) ) {
			wp_send_json_error( __( 'Invalid security token sent.', 'language-switcher-for-divi-polylang' ) );
			wp_die( '0', 400 );
		}

		if ( isset( $_POST['lsep_notice_dismiss'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['lsep_notice_dismiss'] ) ) ) {
			update_option( 'lsep_elementor_review_notice_dismiss', 'yes' );
		}
		exit;
	}
}

// Initialize the widget registration
new LSDP_Register_Widget();
