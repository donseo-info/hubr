<?php

namespace WPShop\WPCommunity\Telegram\Commands;

use WPShop\WPCommunity\Admin\Settings;
use function WPShop\WPCommunity\theme_container;

trait BlogInfoTrait {

    /**
     * @return string
     */
    public function get_blogname() {
        return get_option( 'blogname' );
    }

    /**
     * @return string|void
     */
    public function get_site_url() {
        return home_url();
    }

    /**
     * @param string $page
     *
     * @return false|string
     */
    public function get_page_url( $page ) {
        return get_permalink( theme_container()->get( Settings::class )->get_value( $page ) );
    }
}
