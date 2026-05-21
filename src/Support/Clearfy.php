<?php

namespace WPShop\WPCommunity\Support;

use function WPShop\WPCommunity\get_setting;

class Clearfy {

    /**
     * @return void
     */
    public function init() {
        add_filter( 'clearfy_prevent_set_last_modified_headers', [ $this, '_prevent_set_last_modified' ] );
    }

    /**
     * @param bool $result
     *
     * @return bool
     */
    public function _prevent_set_last_modified( $result ) {
        if ( is_singular( 'page' ) ) {
            $page_settings = [
                'page.bookmarks',
                'page.join',
                'page.order',
                'page.popular',
                'page.profile',
                'page.publish',
                'page.subs',
                'page.top',
                'page.payment',
            ];
            foreach ( $page_settings as $page_id ) {
                if ( get_setting( $page_id ) && is_page( get_setting( $page_id ) ) ) {
                    return true;
                }
            }
        }

        return $result;
    }
}
