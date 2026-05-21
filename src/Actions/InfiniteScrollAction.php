<?php

namespace WPShop\WPCommunity\Actions;

use JetBrains\PhpStorm\NoReturn;
use WPShop\WPCommunity\Features\Feeds;
use WPShop\WPCommunity\Layout\LoopContext;
use function WPShop\WPCommunity\theme_container;

class InfiniteScrollAction {

    /**
     * @var Feeds
     */
    protected $feeds;

    /**
     * @param Feeds $feeds
     */
    public function __construct( Feeds $feeds ) {
        $this->feeds = $feeds;
    }

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_load_posts';
            add_action( "wp_ajax_{$action}", [ $this, '_load_posts' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_load_posts' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _load_posts() {
        $request = wp_parse_args( $_REQUEST, [
            'page'    => 2,
            'context' => '',
            'qv'      => '',
            'is_page' => false,
            'page_id' => null,
        ] );

        wp_send_json_success( [
            'posts'   => $this->feeds->get_infinite_scroll_post_cards( $request['context'], $request['page'] ),
            'counter' => theme_container()->get( LoopContext::class )->get_counter(),
        ] );
    }
}
