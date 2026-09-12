<?php
/**
 * All modules.
 *
 * @package LSDP\Modules;
 */

namespace LSDP\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModule;
add_action(
	'init',
	function () {

		if ( ! class_exists( '\\ET\\Builder\\Framework\\Route\\RESTRoute' ) ) {
			return;
		}

		$rest_api = new RESTRegistration();
		$rest_api->register_routes();
	}
);

// Register REST routes.
add_action(
	'divi_module_library_modules_dependency_tree',
	function ( $dependency_tree ) {
		$dependency_tree->add_dependency( new LanguageSwitcherModule() );
	}
);
