<?php

namespace WPShop\WPCommunity\Admin;

use WPShop\WPCommunity\Features\Advertisement;
use function WPShop\WPCommunity\theme_container;

class MenuPage {

    /**
     * @return void
     */
    public function init() {
        add_action( 'admin_menu', function () {
            add_options_page(
                __( 'WPCommunity Settings', 'wpcommunity' ),
                __( 'WPCommunity Settings', 'wpcommunity' ),
                'manage_options',
                THEME_SETTINGS_PAGE,
                function () {
                    if ( ! empty( $_REQUEST['setup-assistant'] ) ) {
                        get_template_part( 'template-parts/admin/setup-assistant' );
                    } else {
                        get_template_part( 'template-parts/admin/settings' );
                    }
                }
            );


            add_submenu_page(
                'themes.php',
                __( 'Advertisement', 'wpcommunity' ),
                __( 'Advertisement', 'wpcommunity' ),
                'manage_options',
                'wpcommunity-ad',
                function () {
                    $advertisement = theme_container()->get( Advertisement::class );
                    get_template_part( 'template-parts/admin/advertisement', '', compact( 'advertisement' ) );
                }
            );
        } );
    }
}
