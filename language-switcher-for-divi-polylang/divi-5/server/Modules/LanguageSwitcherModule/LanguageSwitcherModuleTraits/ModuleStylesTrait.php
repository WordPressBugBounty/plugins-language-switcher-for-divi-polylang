<?php
namespace LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModuleTraits;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Text\TextStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;

trait ModuleStylesTrait {

  use CustomCssTrait;

  public static function module_styles( $args ) {
    $attrs    = $args['attrs'] ?? [];
    $order_class  = $args['orderClass'];
    $elements = $args['elements'];
    $settings = $args['settings'] ?? [];
    $aspect_ratio = self::sanitize_aspect_ratio(
      $attrs['flag_style']['decoration']['aspect_ratio']['desktop']['value']['aspect_ratio']
        ?? $attrs['flag_style']['innerContent']['desktop']['value']['aspect_ratio']
        ?? ''
    );

    Style::add(
      [
        'id'            => $args['id'],
        'name'          => $args['name'],
        'orderIndex'    => $args['orderIndex'],
        'storeInstance' => $args['storeInstance'],
        'styles'        => [
          // Element: Module.
          $elements->style(
            [
              'attrName'   => 'module',
              'styleProps' => [
                'disabledOn' => [
                  'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                ],
              ],
            ]
          ),

          $elements->style(
            [
                'attrName'   => 'text_style',
            ]
          ),
          $elements->style(
            [
                'attrName'   => 'background_style',
            ]
          ),
          $elements->style(
            [
                'attrName'   => 'container_size',
            ]
          ),
          $elements->style(
            [
                'attrName'   => 'flag_style',
            ]
          ),
          $elements->style(
            [
                'attrName'   => 'color_filters',
            ]
          ),

          CommonStyle::style(
            [
              'selector'            => $order_class . ' .lsdp-wrapper.dropdown ul.lsdp-language-list',
              'attr'                => $attrs['switcher_layouts'],
              'declarationFunction' => function ( $declaration_function_args ) {
                return "--lsdp-dropdown-index: 999;";
              },
            ]
          ),
          CommonStyle::style(
            [
              'selector'            => $order_class . ' .lsdp-wrapper .lsdp-lang-image',   
              'attr'                => $attrs['flag_style']['decoration']['aspect_ratio'] ?? [],
              'declarationFunction' => function ( $declaration_function_args ) {
                $attr_value = self::sanitize_aspect_ratio( $declaration_function_args['attrValue']['aspect_ratio'] ?? '' );
                if ( '' === $attr_value ) {
                  return '';
                }
                if ( '1/1' === $attr_value ) {
                  return "--lsdp-flag-ratio: {$attr_value}; --lsdp-flag-height: var(--lsdp-flag-width);";
                }
                return "--lsdp-flag-ratio: {$attr_value}; --lsdp-flag-height: calc(var(--lsdp-flag-width) * 0.75);";
              },
            ]
          ),
          ( '1/1' === $aspect_ratio ) ? (
            CommonStyle::style(
              [ 
                'selector'            => $order_class . ' .lsdp-wrapper .lsdp-lang-image',   
                'attr'                => $attrs['flag_style']['decoration']['flag_width'] ?? [],
                'declarationFunction' => function ( $declaration_function_args ) {
                  $attr_value = $declaration_function_args['attrValue']['flag_width'] ?? [];
                  if ( ! preg_match( '/^\d+(\.\d+)?(px|em|rem|%|vw|vh)?$/', $attr_value ) ) {
                    return '';
                  }
                  return ("--lsdp-flag-width: {$attr_value}; --lsdp-flag-height: {$attr_value};");
                },
              ]
            ) ): (
             
              CommonStyle::style(
                [
                  'selector'            => $order_class . ' .lsdp-wrapper .lsdp-lang-image',   
                  'attr'                => $attrs['flag_style']['decoration']['flag_width'] ?? [],
                  'declarationFunction' => function ( $declaration_function_args ) {
                    $attr_value = $declaration_function_args['attrValue']['flag_width'] ?? [];
                    if ( ! preg_match( '/^\d+(\.\d+)?(px|em|rem|%|vw|vh)?$/', $attr_value ) ) {
                      return '';
                    }
                    return ("--lsdp-flag-width: {$attr_value}; --lsdp-flag-height: calc(var(--lsdp-flag-width) * 0.75);");
                  },
                ]
              )
            ),
          CommonStyle::style(
            [
              'selector'            => $order_class . ' .lsdp-wrapper .lsdp-lang-image', 
              'attr'                => $attrs['flag_style']['decoration']['flag_border_radius'] ?? [],
              'declarationFunction' => function ( $declaration_function_args ) {
                $attr_value = $declaration_function_args['attrValue']['flag_border_radius'] ?? [];
                if ( ! preg_match( '/^\d+(\.\d+)?(px|em|rem|%|vw|vh)?$/', $attr_value ) ) {
                  return '';
                }
                return "--lsdp-flag-radius: {$attr_value};";
              },
            ]
          ),
          CommonStyle::style(
            [
              'selector'            => $order_class . ' .lsdp-wrapper ul li.lsdp-lang-item a, ' . $order_class . ' .lsdp-wrapper.dropdown',   
              'attr'                => $attrs['background_style']['decoration']['background_color'] ?? [],
              'declarationFunction' => function ( $declaration_function_args ) {
                $attr_value = $declaration_function_args['attrValue']['background_color'] ?? [];
                $hex = sanitize_hex_color( (string) $attr_value );
                if ( ! $hex ) {
                  return '';
                }
                return "background-color: {$hex};";
              },
            ]
          ),
          
        ],
      ]
    );
  }

  /**
   * Allow-list validation for flag aspect ratio values.
   *
   * @param mixed $value Raw aspect ratio from builder attrs.
   * @return string Sanitized ratio, or empty string when invalid.
   */
  private static function sanitize_aspect_ratio( $value ) {
    $allowed = array( 'auto', '1/1', '4/3' );

    if ( is_array( $value ) ) {
      return '';
    }

    $value = (string) $value;

    if ( ! in_array( $value, $allowed, true ) ) {
      return '';
    }

    return $value;
  }
}