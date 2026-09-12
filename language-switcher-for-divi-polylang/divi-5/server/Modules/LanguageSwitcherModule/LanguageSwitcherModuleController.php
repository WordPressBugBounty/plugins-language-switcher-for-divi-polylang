<?php
/**
 * Language Switcher Module Controller.
 *
 * @package LSDP\Modules\LanguageSwitcherModule;
 */

namespace LSDP\Modules\LanguageSwitcherModule;

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Direct access forbidden.' );
}

use LSDP\Modules\Modules;
use ET\Builder\Framework\Controllers\RESTController;
use LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModule;
use LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModuleTraits\ModuleHelper;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class LanguageSwitcherModuleController
 *
 * @package LSDP\Modules\LanguageSwitcherModule
 */
class LanguageSwitcherModuleController extends RESTController {

  /**
   * Return data for the Dynamic Module.
   *
   * @param WP_REST_Request $request REST request object.
   *
   * @return WP_REST_Response|WP_Error
   */
  public static function index( WP_REST_Request $request ): WP_REST_Response {
    $args = [];
    $response = array(
        'language_switcher_data' => ModuleHelper::lsdp_localize_polyglang_data_divi_5($args),
    );
    return rest_ensure_response($response);
  }

  /**
   * Index action arguments.
   *
   * Endpoint arguments as used in `register_rest_route()`.
   *
   * @return array
   */
  public static function index_args(): array {
    return [
      'title' => [
        'type'              => 'string',
        'default'           => '',
        'sanitize_callback' => function( $value, $request, $param ) {
          return sanitize_text_field( $value );
        },
      ],
    ];
  }

  /**
   * Index action permission.
   *
   * Endpoint permission callback as used in `register_rest_route()`.
   *
   * @return bool
   */
  public static function index_permission(): bool {
    return current_user_can('edit_posts');
  }
}