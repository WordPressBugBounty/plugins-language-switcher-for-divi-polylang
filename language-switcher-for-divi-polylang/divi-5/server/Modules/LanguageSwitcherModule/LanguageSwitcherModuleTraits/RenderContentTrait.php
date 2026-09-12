<?php

namespace LSDP\Modules\LanguageSwitcherModule\LanguageSwitcherModuleTraits;

require_once LSDP_DIR . 'helpers/class-lsdp-common-helpers.php';

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

trait RenderContentTrait
{
    public static function lsdp_render_content($props)
    {
        global $polylang;
        
        $languages = pll_the_languages(['raw' => 1]);
        $current_lang = strtolower(pll_current_language());
        
        if (!$languages || !isset($languages[$current_lang])) {
            return '';
        }
        
        if ($props['switcher_layouts'] === 'dropdown') {
            return self::get_dropdown_languages_html($languages, $current_lang, $props);
        }
        
        return self::get_languages_list_html($languages, $current_lang, $props);
    }

    private static function get_dropdown_languages_html($languages, $current_lang, $props)
    {
        $active_html = self::get_active_language_html($languages[$current_lang], $props);
        $languages_html = '';
        
        foreach ($languages as $lang) {
            if ($current_lang === $lang['slug'] || ($lang['no_translation'] && $props['hide_untranslated_language'] === 'on')) {
                continue;
            }

            $flag_icon = \LSDP_Common_Helpers::get_country_flag($lang['flag'], $lang['name']);

            $languages_html .= '<li class="lsdp-lang-item">';
            $languages_html .= '<a href="' . esc_url($lang['url']) . '">';
            if (!empty($props['show_language_flag']) && $props['show_language_flag'] === 'on') {
                $languages_html .= '<div class="lsdp-lang-image">' . wp_kses_post($flag_icon) . '</div>';
            }
            if (!empty($props['show_language_name']) && $props['show_language_name'] === 'on') {
                $languages_html .= '<div class="lsdp-lang-name">' . esc_html($lang['name']) . '</div>';
            }
            if (!empty($props['show_language_code']) && $props['show_language_code'] === 'on') {
                $languages_html .= '<div class="lsdp-lang-code">' . esc_html($lang['slug']) . '</div>';
            }
            $languages_html .= '</a></li>';
        }
        
        return $active_html . '<ul class="lsdp-language-list">' . $languages_html . '</ul>';
    }

    private static function get_active_language_html($lang, $props)
    {
        $html = '<span class="lsdp-active-language">';
        $html .= '<a href="' . esc_url($lang['url']) . '">';
        if (!empty($props['show_language_flag']) && $props['show_language_flag'] === 'on') {
            $flag_icon = \LSDP_Common_Helpers::get_country_flag($lang['flag'], $lang['name']);
            $html .= '<div class="lsdp-lang-image">' . wp_kses_post($flag_icon) . '</div>';
        }
        if (!empty($props['show_language_name']) && $props['show_language_name'] === 'on') {
            $html .= '<div class="lsdp-lang-name">' . esc_html($lang['name']) . '</div>';
        }
        if (!empty($props['show_language_code']) && $props['show_language_code'] === 'on') {
            $html .= '<div class="lsdp-lang-code">' . esc_html($lang['slug']) . '</div>';
        }
        $html .= '</a></span>';
        return $html;
    }

    private static function get_languages_list_html($languages, $current_lang, $props)
    {
        $html = '';
        foreach ($languages as $lang) {
            if (($current_lang === $lang['slug'] && $props['hide_current_language'] === 'on') ||
                ($lang['no_translation'] && $props['hide_untranslated_language'] === 'on')) {
                continue;
            }

            $flag_icon = \LSDP_Common_Helpers::get_country_flag($lang['flag'], $lang['name']);
            $anchor_open = '<a href="' . esc_url($lang['url']) . '">';
            $anchor_close = '</a>';

            $html .= '<li class="lsdp-lang-item">';
            $html .= $anchor_open;
            if ($props['show_language_flag'] === 'on') {
                $html .= '<div class="lsdp-lang-image">' . wp_kses_post($flag_icon) . '</div>';
            }
            if ($props['show_language_name'] === 'on') {
                $html .= '<div class="lsdp-lang-name">' . wp_kses_post(esc_html($lang['name'])) . '</div>';
            }
            if ($props['show_language_code'] === 'on') {
                $html .= '<div class="lsdp-lang-code">' . wp_kses_post(esc_html($lang['slug'])) . '</div>';
            }
            $html .= $anchor_close;
            $html .= '</li>';
        }
        return '<ul class="lsdp-language-list">' . $html . '</ul>';
    }

    public static function render_content($props)
    {
        return self::lsdp_render_content($props);
    }
}
