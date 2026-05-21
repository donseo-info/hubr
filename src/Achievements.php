<?php

namespace WPShop\WPCommunity;

use WP_Error;

class Achievements {

    const POST_TYPE = 'achievement';


    /**
     * @return void
     */
    public function init() {

//        new \WPShop\WPCommunity\Metaboxes\MetaboxAchievements();
//        add_action( 'init', [ $this, '_register_post_type' ] );

//		$action = 'wpcommunity_vote_process';
//		add_action( "wp_ajax_{$action}", [ $this, 'ajax_vote_process' ] );
//		add_action( "wp_ajax_nopriv_{$action}", [ $this, 'ajax_vote_process' ] );

    }


    public function _register_post_type() {
        $args = [
            "label"               => __( "Achievements", 'wpcommunity' ),
            "description"         => "",
            "public"              => true,
            "publicly_queryable"  => false,
            "show_ui"             => true,
            "show_in_rest"        => false,
            "has_archive"         => false,
            "show_in_menu"        => true,
            "show_in_nav_menus"   => false,
            "delete_with_user"    => false,
            "exclude_from_search" => true,
            "capability_type"     => 'post',
            "map_meta_cap"        => true,
            "hierarchical"        => false,
            "rewrite"             => false,
            "query_var"           => false,
            "menu_position"       => 99,
            "menu_icon"           => "dashicons-awards",
            "supports"            => [ "title", "custom-fields", "author" ],
            //			"taxonomies"            => [ self::TAXONOMY_GROUP, self::TAXONOMY_PRODUCT ],
        ];

        register_post_type( self::POST_TYPE, $args );
    }

}
