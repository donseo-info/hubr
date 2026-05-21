<?php

namespace Wpshop\Core;

/**
 * Class ThemeOptions
 *
 * 1.0.1    2019-05-25      Add $theme_title
 * 1.0.0    2018-09-06      Init
 */
class ThemeOptions {

    public $text_domain = 'wpshop';

    public $theme_slug = 'wpshop_theme';

    public $settings_name = 'theme_settings';

    public $option_name = 'wpshop_theme_options';

    public $theme_name = 'wpshop-theme';

    public $theme_title = 'WPShop Theme';

    public $updater_url = '';

    public $version = '1.0';

    /**
     * Settings
     */
    public $settings_page_title = 'Theme Settings';
    public $settings_menu_title = 'Theme Settings';
    public $settings_menu_slug = 'theme_settings';

    /**
     * License
     */
    public $license = 'wpshop_theme_license';
    public $license_verify = 'wpshop_theme_license_verify';
    public $license_error = 'wpshop_theme_license_error';

}
