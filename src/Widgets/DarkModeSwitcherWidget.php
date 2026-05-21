<?php

namespace WPShop\WPCommunity\Widgets;

use WP_Widget;

class DarkModeSwitcherWidget extends WP_Widget {

    const WIDGET_ID = 'wpcommunity-dark-mode-switcher';

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            self::WIDGET_ID,
            'WPCommunity: ' .__( 'Dark Mode Switcher', 'wpcommunity' ),
            [
                'description' => __( 'Dark mode switcher widget', 'wpcommunity' ),
            ]
        );
    }

    /**
     * @param array $args
     * @param array $instance
     *
     * @return void
     */
    public function widget( $args, $instance ) {
        get_template_part( 'template-parts/widgets/dark-mode-switcher' );
    }

    /**
     * @param array $instance
     *
     * @return string|void
     */
    public function form( $instance ) {

    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    public function update( $new_instance, $old_instance ) {

        return $new_instance;
    }
}
