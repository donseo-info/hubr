<?php

namespace WPShop\WPCommunity;

use WP_Error;
use WP_Query;

class Invite {

    const POST_TYPE                = 'invite';
    const USER_META_INVITE_HISTORY = 'invite_history';

    const POST_META_EXPIRED        = 'expired';
    const POST_META_LIMIT          = 'limit';
    const POST_META_FOR_NEWBIE     = 'for_newbie';
    const POST_META_INVITE_HISTORY = 'invite_history';

//	const USER_META_TELEGRAM_USER_ID = 'telegram_user_id';
//	const USER_META_TELEGRAM_USERNAME = 'telegram_username';

    /**
     * @return void
     */
    public function init() {
        new \WPShop\WPCommunity\Metaboxes\MetaboxInvite();
        add_action( 'init', [ $this, '_register_post_type' ] );

        $post_type = self::POST_TYPE;
        add_filter( "manage_{$post_type}_posts_columns", [ $this, '_add_columns' ] );
        add_action( "manage_{$post_type}_posts_custom_column", [ $this, '_add_columns_output' ], 10, 2 );
    }

    public function is_invite_active( $post_id ) {
        $expired = get_post_meta( $post_id, self::POST_META_EXPIRED, true );

        $now     = new \DateTime( 'now' );
        $expired = new \DateTime( $expired );
        $expired->modify( 'tomorrow' );

        if ( $now < $expired ) {
            return $expired->format( 'Y-m-d H:i:s' );
        }

        return false;
    }

    public function is_have_limit( $post_id ) {
        $limit = (int) get_post_meta( $post_id, self::POST_META_LIMIT, true );

        return ( $limit > 0 );
    }

    public function is_user_newbie_check( $post_id ) {
        $for_newbie   = get_post_meta( $post_id, self::POST_META_FOR_NEWBIE, true );
        $user_expired = get_user_meta( get_current_user_id(), User::USER_META_EXPIRED, true );

        if ( $for_newbie == 'checked' && ! empty( $user_expired ) ) {
            return false;
        }

        return true;
    }

    /**
     * @param string $invite
     *
     * @return false|int
     */
    public function is_invite_exists( $invite ) {
        $post = ( new WP_Query( [
            'post_type'      => self::POST_TYPE,
            'title'          => $invite,
            'post_status'    => 'publish',
            'posts_per_page' => 1,

            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
        ] ) )->post;
        if ( $post ) {
            return $post->ID;
        }

        return false;
    }

    public function check_invite( $invite ) {

        $invite = trim( $invite );
        if ( empty( $invite ) ) {
            return new WP_Error( 'invite_empty', __( 'Invite is empty', 'wpcommunity' ) );
        }

        $post_id = $this->is_invite_exists( $invite );

        // существует ли инвайт вообще
        if ( ! $post_id ) {
            return new WP_Error( 'invite_not_found', __( 'Invite not found', 'wpcommunity' ) );
        }

        // проверяем не просрочен ли инвайт
        if ( ! $this->is_invite_active( $post_id ) ) {
            return new WP_Error( 'invite_expired', __( 'Invite expired', 'wpcommunity' ) );
        }

        // не вышли ли лимиты по инвайту
        if ( ! $this->is_have_limit( $post_id ) ) {
            return new WP_Error( 'no_limits', __( 'Sorry, invite is no longer working', 'wpcommunity' ) );
        }

        return $post_id;

    }


    public function apply_invite( $user_id, $invite ) {

        // проверяем есть ли инвайт, не просрочен ли и есть ли у него лимиты
        $post_id = $this->check_invite( $invite );
        if ( is_wp_error( $post_id ) ) {
            return new WP_Error( $post_id->get_error_code(), $post_id->get_error_message() );
        }

        // может он только для новичков?
        if ( ! $this->is_user_newbie_check( $post_id ) ) {
            return new WP_Error( 'no_newbie', __( 'This invite only for newbie', 'wpcommunity' ) );
        }

        // может ты уже активировал его?
        if ( $this->is_user_already_activate_invite( $post_id, $user_id ) ) {
            return new WP_Error( 'invite_already_activated', __( 'Invite already activated', 'wpcommunity' ) );
        }


        // если все хорошо, начисляем инвайт
        $membership = theme_container()->get( Membership::class );
        $days       = (int) get_post_meta( $post_id, 'days', true );


        // обновляем членство количество дней
        $membership->renew_membership( $user_id, $days );


        // добавляем в историю юзера
        $invite_history = get_user_meta( $user_id, self::USER_META_INVITE_HISTORY, true );
        if ( empty( $invite_history ) ) {
            $invite_history = [];
        }
        $invite_history[ current_time( 'timestamp' ) ] = $post_id;
        update_user_meta( $user_id, self::USER_META_INVITE_HISTORY, $invite_history );

        // уменьшаем лимит у инвайта
        $limit = (int) get_post_meta( $post_id, self::POST_META_LIMIT, true );
        $limit --;
        update_post_meta( $post_id, self::POST_META_LIMIT, $limit );

        // добавляем историю самому инвайту
        $invite_history = get_post_meta( $post_id, self::POST_META_INVITE_HISTORY, true );
        if ( empty( $invite_history ) ) {
            $invite_history = [];
        }
        $invite_history[ current_time( 'timestamp' ) ] = $user_id;
        update_post_meta( $post_id, self::POST_META_INVITE_HISTORY, $invite_history );


        // todo удалить эти мета поля
        // todo поменять историю, хранить invite_id
//		update_user_meta( $user_id, self::USER_META_INVITE, $invite );

        return true;

    }


    public function is_user_already_activate_invite( $invite_id, $user_id ) {
        $invite_history = get_user_meta( $user_id, self::USER_META_INVITE_HISTORY, true );
        if ( empty( $invite_history ) ) {
            $invite_history = [];
        }

        if ( in_array( $invite_id, $invite_history ) ) {
            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    public function _register_post_type() {
        $args = [
            "label"               => __( "Invite", 'wpcommunity' ),
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
            "menu_icon"           => "dashicons-email-alt2",
            "supports"            => [ "title", "custom-fields", "author" ],
            //			"taxonomies"            => [ self::TAXONOMY_GROUP, self::TAXONOMY_PRODUCT ],
        ];

        register_post_type( self::POST_TYPE, $args );
    }

    /**
     * @param array $columns
     *
     * @return array
     */
    public function _add_columns( $columns ) {
        $part1 = array_slice( $columns, 0, 2 );
        $part2 = array_slice( $columns, 2 );

        return array_merge( $part1, [
            'days'                     => __( 'Bonus days', 'wpcommunity' ),
            self::POST_META_FOR_NEWBIE => __( 'Only for new users', 'wpcommunity' ),
            self::POST_META_LIMIT      => __( 'Limit', 'wpcommunity' ),
            self::POST_META_EXPIRED    => __( 'Expire', 'wpcommunity' ),
        ], $part2 );
    }

    /**
     * @param string $column_name
     * @param int    $post_id
     *
     * @return void
     */
    public function _add_columns_output( $column_name, $post_id ) {
        $value = get_post_meta( $post_id, $column_name, true );
        if ( self::POST_META_FOR_NEWBIE === $column_name ) {
            echo $value === 'checked'
                ? '<div style="width: 16px;color:#00d300"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M195.82 427.65a31.92 31.92 0 01-22.45-9.2L70.24 316.9a32 32 0 0144.9-45.61l80.67 79.44 201-198.21a32 32 0 1144.94 45.58L218.29 418.44a31.91 31.91 0 01-22.47 9.21z" fill="currentColor"></path></svg></div>'
                : '<div style="width: 16px;color:red"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M438.63 393.37a32 32 0 01-45.26 45.26L256 301.25 118.63 438.63a32 32 0 01-45.26-45.26L210.75 256 73.37 118.63a32 32 0 0145.26-45.26L256 210.75 393.37 73.37a32 32 0 0145.26 45.26L301.25 256z" fill="currentColor"></path></svg></div>';
        } elseif ( self::POST_META_EXPIRED === $column_name ) {
            $expired = new \DateTime( $value );
            echo $expired->format( get_option( 'date_format' ) );
        } else {
            echo $value;

        }
    }
}
