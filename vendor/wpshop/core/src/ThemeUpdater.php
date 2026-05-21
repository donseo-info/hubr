<?php

namespace Wpshop\Core;

/**
 * Class ThemeUpdater
 */
class ThemeUpdater {

    protected $options;

    private $license_key = '';

    /**
     * Theme_Updater constructor.
     *
     * @param ThemeOptions $options
     */
    public function __construct( ThemeOptions $options ) {

        $this->options = $options;

        //require get_template_directory() . '/inc/core/theme-update-checker.php';

        $this->license_key = get_option( $this->options->license );

        $this->run_updater();

    }

    public function run_updater() {
        if ( ! empty( $this->license_key ) && ! empty( $this->options->updater_url ) ) {

            $update_checker = new \Wpshop\Core\ThemeUpdateChecker(
                $this->options->theme_name,
                $this->options->updater_url . '?action=get_metadata&slug='. $this->options->theme_name . '&license_key=' . $this->license_key
            );

        }
    }

}