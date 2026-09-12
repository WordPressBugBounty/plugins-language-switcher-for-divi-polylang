<?php
namespace LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModuleTraits;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModule;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\Module\Module;
use LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModuleTraits\ModuleHelper;

trait RenderCallbackTrait {
  public static function render_callback( $attrs, $content, $block, $elements ) {
    // Attributes with default values
    $props = array(
      'switcher_layouts' => ModuleHelper::get_attr_value($attrs, 'switcher_layouts', 'dropdown'),
      'show_language_flag' => ModuleHelper::get_attr_value($attrs, 'show_language_flag', 'on'),
      'show_language_name' => ModuleHelper::get_attr_value($attrs, 'show_language_name', 'on'),
      'show_language_code' => ModuleHelper::get_attr_value($attrs, 'show_language_code', 'off'),
      'hide_current_language' => ModuleHelper::get_attr_value($attrs, 'hide_current_language', 'off'),
      'hide_untranslated_language' => ModuleHelper::get_attr_value($attrs, 'hide_untranslated_language', 'off'),
    );
    
    $layout = sanitize_html_class( (string) $props['switcher_layouts'] );
    if ( '' === $layout ) {
      $layout = 'dropdown';
    }

    $module_inner_container = HTMLUtility::render(
      [
        'tag'               => 'div',
        'attributes'        => [
          'class' => 'lsdp-wrapper ' . $layout,
          'id' => 'lsdp-wrapper',
        ],
        'childrenSanitizer' => 'et_core_esc_previously',
        'children'          => LanguageSwitcherModule::render_content($props),
      ]
    );
    
    $module_inner = HTMLUtility::render(
      [
        'tag'               => 'div',
        'attributes'        => [
          'class' => 'lsdp-main-wrapper ',
        ],
        'childrenSanitizer' => 'et_core_esc_previously',
        'children'          => $module_inner_container,
      ]
    );
    // This are the module elements that will be rendered in the frontend.
    $module_elements = $elements->style_components(
      [
          'attrName' => 'module'
      ]
    );

    // This are the children of the module container, which are the module elements and the module inner.
    $module_container_children = $module_elements . $module_inner;


    return Module::render(
      [
        // FE only.
        'orderIndex'          => $block->parsed_block['orderIndex'],
        'storeInstance'       => $block->parsed_block['storeInstance'],

        // VB equivalent.
        'attrs'               => $attrs,
        'elements'            => $elements,
        'id'                  => $block->parsed_block['id'],
        'moduleClassName'     => 'LanguageSwitcherModule',
        'name'                => $block->block_type->name,
        'classnamesFunction'  => [LanguageSwitcherModule::class, 'module_classnames'],
        'moduleCategory'      => $block->block_type->category,
        'stylesComponent'     => [LanguageSwitcherModule::class, 'module_styles'],
        'scriptDataComponent' => [LanguageSwitcherModule::class, 'module_script_data'],
        'children'            => $module_container_children,
      ]
    );
  }
}
