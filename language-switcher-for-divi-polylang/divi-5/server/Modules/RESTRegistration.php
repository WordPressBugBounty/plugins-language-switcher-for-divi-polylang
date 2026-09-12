<?php
/**
 * REST Registration.
 *
 * @package LSDP\Modules;
 */

namespace LSDP\Modules;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\Route\RESTRoute;
use LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModuleController;

/**
 * Class RESTRegistration
 *
 * @package LSDP\Modules
 */
class RESTRegistration {
  /**
   * Register REST routes for modules.
   */
  public function register_routes() {
    $route = new RESTRoute( 'lsdp/v1' ); // Namespace for the extension.

    // Route for Language Switcher Module.
    $route->prefix('/module-data')->get( '/language-switcher-module', LanguageSwitcherModuleController::class );
  }
}