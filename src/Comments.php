<?php

namespace WPShop\WPCommunity;

use WP_Post;

class Comments {

    const COMMENT_META_TRASH = 'trash';

    /**
     * @return void
     */
    public function init() {
        add_filter( 'get_comment_text', [ $this, 'filter_get_comment_text' ], 10, 3 );
        add_filter( 'wpcommunity/comments/author_link', [ $this, 'filter_comment_author_link' ], 10, 2 );
        add_filter( 'wpcommunity/comments/comment_avatar', [ $this, 'filter_comment_avatar' ], 10, 2 );

        add_filter( 'preprocess_comment', function ( $comment_data ) {
            if ( isset( $comment_data['user_id'] ) ) {
                if ( $user = get_user_by( 'id', $comment_data['user_id'] ) ) {
                    if ( $name = get_user_name( $user ) ) {
                        $comment_data['comment_author'] = $name;
                    }

                }
            }

            return $comment_data;
        } );

        // todo move to Layout?
        add_action( 'wpcommunity/comments/output', [ $this, '_output_comments' ], 20 );
    }

    /**
     * Если комментарий мягко удален -- прячем текст
     *
     * @param string      $comment_text
     * @param \WP_Comment $comment
     * @param array       $args
     *
     * @return mixed|string
     */
    public function filter_get_comment_text( $comment_text, $comment, $args = [] ) {
        if ( $this->is_trash( $comment->comment_ID ) ) {
            return '<p><em>' . __( 'The comment was deleted.', 'wpcommunity' ) . '</em></p>';
        }

        return $comment_text;
    }


    public function filter_comment_author_link( $author, $comment ) {
        if ( $this->is_trash( $comment->comment_ID ) ) {
            return __( 'Unknown', 'wpcommunity' );
        }

        return $author;
    }


    public function filter_comment_avatar( $author, $comment ) {
        if ( $this->is_trash( $comment->comment_ID ) ) {
            return '<img src="' . get_template_directory_uri() . '/assets/public/images/avatar-unknown.png" alt="">';
        }

        return $author;
    }


    public function is_trash( $comment_id ) {
        return (int) get_comment_meta( $comment_id, self::COMMENT_META_TRASH, true );
    }


    /**
     * Выводит пользователя со ссылкой на профиль на сайте или просто имя пользователя
     *
     * @param $comment
     *
     * @return mixed|void
     */
    public function get_author_link( $comment = null ) {

        $author = '';

        $comment_author = get_comment_author( $comment );

        $attributes = get_the_attributes( 'comment-author-link', [
            'rel' => 'author',
        ] );

        if ( is_object( $comment ) && $comment->user_id > 0 ) {
            $profile_url = get_author_posts_url( $comment->user_id );
            $author      .= '<a href="' . $profile_url . '" ' . $attributes . '>';
            $author      .= $comment_author;
            $author      .= '</a>';
        } else {
            $author .= esc_html( $comment_author );
        }

        $author = apply_filters( 'wpcommunity/comments/author_link', $author, $comment );

        return $author;
    }

    /**
     * @param \WP_Comment $comment
     * @param int         $avatar_size
     *
     * @return mixed|null
     */
    public function get_author_link_with_avatar( $comment, $avatar_size = 96 ) {
        $avatar = $this->get_comment_avatar( $comment, $avatar_size );

        $author = '';

        $comment_author = get_comment_author( $comment );

        $attributes = get_the_attributes( 'comment-author-link', [
            'rel' => 'author',
        ] );

        if ( is_object( $comment ) && $comment->user_id > 0 ) {
            $profile_url = get_author_posts_url( $comment->user_id );
            $author      .= '<a href="' . $profile_url . '" ' . $attributes . '>';
            $author      .= $avatar;
            $author      .= '<span>' . $comment_author . '</span>';
            $author      .= '</a>';
        } else {
            $author .= esc_html( $comment_author );
            $avatar = get_avatar( 0, 24 );
            $author = '<span class="a">' . $avatar . $author . '</span>';
        }

        $author = apply_filters( 'wpcommunity/comments/author_link', $author, $comment );

        return $author;
    }


    public function get_comment_avatar( $comment, $size ) {
        return apply_filters( 'wpcommunity/comments/comment_avatar', get_avatar( $comment, $size ), $comment );
    }

    /**
     * @return void
     */
    public function _output_comments() {
        global $post;

        $user_id = get_current_user_id();

        $can_show = true;
        if ( ! $post || ! $this->can_show_comments( $post, $user_id ) ) {
            $can_show = false;
        }

        /**
         * @since 1.0
         */
        $can_show = apply_filters( 'wpcommunity/comments/can_show', $can_show, $post, $user_id );

        if ( $can_show ) {
            if ( comments_open() || get_comments_number() ) {
                comments_template();
            }
        }
    }

    /**
     * @param WP_Post  $post
     * @param int|null $user_id
     *
     * @return bool
     */
    public function can_show_comments( WP_Post $post, $user_id = null ) {
        $user = null === $user_id ? wp_get_current_user() : get_user_by( 'id', $user_id );

        if ( ( ! $user || ! $user->exists() ) ) {
            if ( get_setting( 'comments.enabled_for_guests' ) && ! get_setting( 'comments.for_members_only' ) ) {
                return true;
            }

            return false;
        }

        if ( get_setting( 'comments.for_members_only' ) ) {
            if ( ! theme_container()->get( Membership::class )->is_user_post_access( $post, $user->ID ) ) {
                return false;
            }
        }

        if ( ! theme_container()->get( Membership::class )->is_post_access( $post, $user->ID ) &&
             ! get_setting( 'comments.enabled_for_guests' )
        ) {
            return false;
        }

        return true;
    }
}
