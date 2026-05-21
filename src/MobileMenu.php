<?php

namespace WPShop\WPCommunity;

class MobileMenu {

    protected $prefix = 'wpcommunity_';

    /**
     * @return void
     */
    public function init() {
        add_action( 'wp_head', [ $this, '_append_script' ], - 1000 );
    }

    /**
     * @return void
     */
    public function _append_script() {
        $device_edges = get_device_edges();

        /**
         * Allows to change value of device width for hamburger output
         *
         * @since 1.0
         */
        $show_hamburger_from_width = (int) apply_filters( 'wpcommunity/mobile_menu/hamburger_from_width', $device_edges['desktop'] );

        echo '<script id="' . $this->prefix . 'init_mobile_menu_params">var ' . $this->prefix . 'mobile_menu_params = ' . wp_json_encode( [
                'tablet_width'  => (int) $device_edges['tablet'],
                'desktop_width' => (int) $device_edges['desktop'],

                'show_hamburger_from_width' => $show_hamburger_from_width ?: 1024,
            ] ) . '</script>';
        echo '<script id="' . $this->prefix . 'init_mobile_menu">' . $this->js() . '</script>';
    }

    /**
     * @return string
     */
    protected function js() {
        return _ob_get_content( function () {
            foreach ( [ get_stylesheet_directory(), get_template_directory() ] as $base_dir ) {
                $file = $base_dir . '/assets/public/js/inc/hamburger.min.js';
                if ( file_exists( $file ) ) {
                    include $file;

                    return;
                }
            }
        } );
    }
}
