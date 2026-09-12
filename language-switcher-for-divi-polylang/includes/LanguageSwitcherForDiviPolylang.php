<?php

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Direct access forbidden.' );
}

class LSDP_LanguageSwitcherForDiviPolylang extends DiviExtension {

	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $gettext_domain = 'language-switcher-for-divi-polylang';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name = 'language-switcher-for-divi-polylang';

	/**
	 * The extension's version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = LSDP;

	/**
	 * LSDP_LanguageSwitcherForDiviPolylang constructor.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __construct( $name = 'language-switcher-for-divi-polylang', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		parent::__construct( $name, $args );
	}
}

new LSDP_LanguageSwitcherForDiviPolylang();
