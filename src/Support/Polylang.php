<?php

namespace WPShop\WPCommunity\Support;

use WP_Post;

class Polylang {

    /**
     * @return void
     */
    public function init() {
        add_filter( 'wpcommunity/telegram/send_post', [ $this, '_prevent_send' ], 20, 2 );
    }

    /**
     * @param bool    $result
     * @param WP_Post $post
     *
     * @return bool
     */
    public function _prevent_send( $result, $post ) {
        if ( isset( $GLOBALS["polylang"] ) ) {
            $translations = $GLOBALS["polylang"]->model->post->get_translations( $post->ID );
            if ( $translations ) {
                return false;
            }
        }

        return $result;
    }
}
