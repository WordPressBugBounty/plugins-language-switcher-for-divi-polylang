<?php

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Direct access forbidden.' );
}

if ( ! class_exists( 'ET_Builder_Element' ) ) {
	return;
}

require_once LSDP_DIR . 'helpers/class-lsdp-common-helpers.php';
require_once LSDP_DIR . 'helpers/class-lsdp-style-helpers.php';

$lsdp_module_files = glob( __DIR__ . '/modules/*/*.php' );

// Load custom Divi Builder modules
foreach ( (array) $lsdp_module_files as $module_file ) {
	if ( $module_file && preg_match( "/\/modules\/\b([^\/]+)\/\\1\.php$/", $module_file ) ) {
		require_once $module_file;
	}
}
