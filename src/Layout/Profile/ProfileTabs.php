<?php

namespace WPShop\WPCommunity\Layout\Profile;

use WPShop\WPCommunity\Data\ProfileTabItem;
use WPShop\WPCommunity\Telegram\TelegramIntegration;
use function WPShop\WPCommunity\theme_container;

class ProfileTabs {

    /**
     * @var int
     */
    protected $sort_order = 0;

    /**
     * @var ProfileTabItem[]
     */
    protected $_tabs = [];

    /**
     * @var ProfileTabItem[]
     */
    protected $_prepared_tabs;

    /**
     * @return $this
     */
    public function register_tabs() {
        $this->register_tab(
            'subscription',
            __( 'Subscription', 'wpcommunity' ),
            function () {
                get_template_part( 'template-parts/profile/tab', 'subscription' );
            },
            true,
            10
        );
        $this->register_tab(
            'about',
            _x( 'About', 'about me', 'wpcommunity' ),
            function () {
                get_template_part( 'template-parts/profile/tab', 'about' );
            },
            true,
            20
        );
        $this->register_tab(
            'karma',
            __( 'Karma', 'wpcommunity' ),
            function () {
                get_template_part( 'template-parts/profile/tab', 'karma' );
            },
            true,
            30
        );
        $this->register_tab(
            'telegram',
            'Telegram',
            function () {
                get_template_part( 'template-parts/profile/tab', 'telegram' );
            },
            function () {
                return theme_container()->get( TelegramIntegration::class )->is_bot_enabled();
            },
            40
        );
        $this->register_tab(
            'posts',
            __( 'Posts', 'wpcommunity' ),
            function () {
                get_template_part( 'template-parts/profile/tab', 'posts' );
            },
            true,
            50
        );

        $this->register_tab(
            'comments',
            __( 'Comments', 'wpcommunity' ),
            function () {
                get_template_part( 'template-parts/profile/tab', 'comments' );
            },
            true,
            50
        );

        do_action( 'wpcommunity/profile/prepare_tabs', $this );

        return $this;
    }

    /**
     * @return string|null
     */
    public function get_default_active_tab() {
        foreach ( $this->get_prepared_tabs() as $key => $tab ) {
            return $key;
        }

        return null;
    }

    /**
     * @param string          $name
     * @param string|callable $nav
     * @param string|callable $content
     * @param bool|callable   $is_visible
     * @param int|null        $sort_order
     *
     * @return $this
     */
    public function register_tab( $name, $nav, $content, $is_visible = true, $sort_order = null ) {
        if ( null === $sort_order ) {
            $sort_order = $this->sort_order += 10;
        } else {
            $this->sort_order = max( $this->sort_order, $sort_order );
        }

        $this->_tabs[ $name ] = new ProfileTabItem( compact( 'nav', 'content', 'is_visible', 'sort_order' ) );

        return $this;
    }

    /**
     * @return ProfileTabItem[]
     */
    public function get_prepared_tabs() {
        if ( null === $this->_prepared_tabs ) {

            /**
             * @since 1.0
             */
            $tabs = apply_filters( 'wpcommunity/profile/initial_tabs', $this->_tabs );

            $tabs = array_filter( $tabs, function ( $tab ) {
                return $tab->is_visible();
            } );

            uasort( $tabs, function ( $item1, $item2 ) {
                return $item2->sort_order - $item2->sort_order;
            } );

            $this->_prepared_tabs = $tabs;
        }

        return $this->_prepared_tabs;
    }
}
