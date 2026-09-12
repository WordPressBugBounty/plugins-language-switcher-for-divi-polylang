<?php
namespace LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModuleTraits;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

trait ModuleScriptDataTrait {
  public static function module_script_data( $args ) {
    $elements = $args['elements'];

    // Element Script Data Options.
    $elements->script_data(
            [
                'attrName' => 'module',
            ]
        );
  }
}