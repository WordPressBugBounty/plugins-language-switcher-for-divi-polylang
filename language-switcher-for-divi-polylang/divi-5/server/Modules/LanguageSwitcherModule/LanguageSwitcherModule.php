<?php
/**
 * Language Switcher Module class.
 *
 * @package LSDP\Modules\LanguageSwitcherModule;
 */

namespace LSDP\Modules\LanguageSwitcherModule;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModuleTraits;

/**
 * Class LanguageSwitcherModule
 *
 * @package LSDP\Modules\LanguageSwitcherModule
 */
class LanguageSwitcherModule implements DependencyInterface {
  
  use LanguageSwitcherModuleTraits\ModuleClassnamesTrait;
  use LanguageSwitcherModuleTraits\ModuleStylesTrait;
  use LanguageSwitcherModuleTraits\ModuleScriptDataTrait;
  use LanguageSwitcherModuleTraits\RenderCallbackTrait;
  use LanguageSwitcherModuleTraits\RenderContentTrait;
  use LanguageSwitcherModuleTraits\CustomCssTrait;
  
  /**
   * Register module.
   * `DependencyInterface` interface ensures class method name `load()` is executed for initialization.
   */
  public function load() {
    $module_json_folder_path = dirname( __DIR__, 3 ) . '/modules-json/language-switcher';
 
    add_action(
      'init',
      function() use ( $module_json_folder_path ) {
        ModuleRegistration::register_module(
          $module_json_folder_path,
          [
            'render_callback' => [ $this, 'render_callback' ],
          ]
        );
      }
    );
  }
}