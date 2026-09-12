<?php

namespace LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModuleTraits;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

class ModuleHelper {
    /**
     * Retrieves the attribute value from the provided attributes array.
     *
     * Returns the raw value; escape at the point of output (esc_attr, esc_html, esc_url, etc.).
     *
     * @param array  $attrs   The attributes array.
     * @param string $key     The key to retrieve the value for.
     * @param mixed  $default The default value to return if the key does not exist.
     *
     * @return mixed The attribute value or the default value if not found.
     */
    public static function get_attr_value($attrs, $key, $default = null)
    {
        if ( ! empty( $attrs[ $key ]['desktop']['value'][ $key ] ) ) {
            return $attrs[ $key ]['desktop']['value'][ $key ];
        }

        return $default;
    }

    static function lsdp_localize_polyglang_data_divi_5($data) {
    
        global $polylang;
        $lsdp_polylang = $polylang;
        if ( isset( $lsdp_polylang ) ) {
            try {
                require_once LSDP_DIR . 'helpers/class-lsdp-common-helpers.php';
      
                if ( function_exists( 'pll_the_languages' ) && function_exists( 'pll_current_language' ) ) {
                    $languages = pll_the_languages( array( 'raw' => 1 ) );
                    
                    // Ensure $languages is an array
                    if ( !is_array( $languages ) || empty( $languages ) ) {
                        return; // Exit early if languages are not available
                    }
                    $lang_curr = strtolower( pll_current_language() ? pll_current_language() : pll_default_language() );
                    // Correct array_map function
                    $languages = array_map(
                        function( $language ) {
                            return array(
                                'flagCode'       => esc_html( \LSDP_Common_Helpers::get_flag_code( $language['flag'] ) ),
                                'slug'           => esc_html( $language['slug'] ),
                                'name'           => esc_html( $language['name'] ),
                                'no_translation' => esc_html( $language['no_translation'] ),
                                'url'            => esc_url( $language['url'] ),
                            );
                        },
                        $languages
                    );
                    $custom_data = array(
                        'lsdpLanguageData' => $languages,
                        'lsdpCurrentLang'   => esc_html( $lang_curr ),
                        'lsdpPluginUrl'     => esc_url( LSDP_URL ),
                    );
                    $data['lsdpGlobalObj'] = $custom_data;
                }
            } catch ( Exception $e ) {
                // Handle exception if needed
            }
        }
        return $data;
      }
}


