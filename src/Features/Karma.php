<?php

namespace WPShop\WPCommunity\Features;

use WPShop\WPCommunity\Comments;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

class Karma {

    const USER_META_KARMA               = 'karma';
    const USER_META_KARMA_HISTORY       = 'karma_history';
    const USER_META_KARMA_HISTORY_LIMIT = 1000;

    public $karma_rate = [];

    public function __construct() {

        // name         -- название, которое доступно публично
        // direction    -- направление + или -
        // points       -- кол-во очков
        // object       -- за что дали баллы, post, comment
        // public       -- показывать ли публично информацию об изменении баллов

        $karma_rate_default = [

            // посты
            'post_publish'        => [
                'name'      => __( 'Publish a post', 'wpcommunity' ),
                'direction' => '+',
                'points'    => get_setting( 'karma.post_publish' ),
                'object'    => 'post',
                'public'    => true,
            ],
            'post_unpublish'      => [
                'name'      => __( 'Remove a post from publication', 'wpcommunity' ),
                'direction' => '-',
                'points'    => get_setting( 'karma.post_publish' ),
                'object'    => 'post',
                'public'    => true,
            ],

            // комменты
            'comment_publish'     => [
                'name'      => __( 'Comment on a post', 'wpcommunity' ),
                'direction' => '+',
                'points'    => get_setting( 'karma.comment_publish' ),
                'object'    => 'comment',
                'public'    => true,
            ],
            'comment_delete'      => [
                'name'      => __( 'Delete comment', 'wpcommunity' ),
                'direction' => '-',
                'points'    => get_setting( 'karma.comment_publish' ),
                'object'    => 'comment',
                'public'    => false,
            ],
            'comment_spam'        => [
                'name'      => __( 'Spam comment', 'wpcommunity' ),
                'direction' => '-',
                'points'    => get_setting( 'karma.comment_spam' ),
                'object'    => 'comment',
                'public'    => false,
            ],
            'comment_unspam'      => [
                'name'      => __( 'Unspam comment', 'wpcommunity' ),
                'direction' => '+',
                'points'    => get_setting( 'karma.comment_spam' ),
                'object'    => 'comment',
                'public'    => false,
            ],

            // голосование
            'post_vote_change'    => [
                'name'      => __( 'Vote for a post', 'wpcommunity' ),
                'direction' => '+',
                'points'    => get_setting( 'karma.post_vote_change' ),
                'object'    => 'post',
                'public'    => true,
            ],
            'comment_vote_change' => [
                'name'      => __( 'Vote for a comment', 'wpcommunity' ),
                'direction' => '+',
                'points'    => get_setting( 'karma.comment_vote_change' ),
                'object'    => 'comment',
                'public'    => true,
            ],
        ];

        // todo объединить с настройками сайта
        $this->karma_rate = wp_parse_args( [], $karma_rate_default );

    }

    public function init() {

        // публикация постов
        add_action( 'transition_post_status', [ $this, 'hook_change_post_status' ], 10, 3 );

        // голосование за посты и комментарии
        add_action( 'wpcommunity/vote/change', [ $this, 'hook_vote_change' ], 10, 4 );


        // добавление комментария
        add_action( 'comment_post', [ $this, 'hook_comment_post' ], 10, 2 );
        // одобрение комментария
        add_action( 'comment_unapproved_to_approved', [ $this, 'hook_comment_unapproved_to_approved' ], 10 );
        // удаление комментария (не в корзину, а именно полное удаление из бд)
        add_action( 'delete_comment', [ $this, 'hook_delete_comment' ], 10, 2 );
        // мягкое удаление комментария
        add_action( 'wpcommunity/comments/soft_delete', [ $this, 'hook_delete_comment' ], 10, 2 );
        // мягкое восстановление удаленного комментария
        add_action( 'wpcommunity/comments/soft_delete_undo', [ $this, 'hook_comment_post' ], 10, 2 );
        // комментарий спам
        add_action( 'spam_comment', [ $this, 'hook_spam_comment' ], 10, 2 );
        // комментарий не спам
        add_action( 'unspam_comment', [ $this, 'hook_unspam_comment' ], 10, 2 );

    }

    public function get_karma( $user_id ) {
        return (int) get_user_meta( $user_id, self::USER_META_KARMA, true );
    }


    public function get_karma_history( $user_id ) {
        $karma_history = get_user_meta( $user_id, self::USER_META_KARMA_HISTORY, true );
        if ( empty( $karma_history ) ) {
            $karma_history = [];
        }

        return $karma_history;
    }


    public function get_karma_history_beauty( $user_id ) {
        $history       = [];
        $karma_history = $this->get_karma_history( $user_id );
        $karma_history = array_reverse( $karma_history );

        if ( ! empty( $karma_history ) ) {
            foreach ( $karma_history as $item ) {

                // действие
                $action_text = $item['action'];

                if ( isset( $this->karma_rate[ $item['action'] ] ) ) {
                    $action_text = '<!-- ' . $item['action'] . ' -->' . $this->karma_rate[ $item['action'] ]['name'];
                }

                // добавляем объект
                if ( isset( $item['object_id'] ) ) {
                    $object = $this->karma_rate[ $item['action'] ]['object'];
                    if ( $object == 'post' ) {
                        if ( $post = get_post( $item['object_id'] ) ) {
                            $action_text .= '<br><a href="' . get_the_permalink( $post->ID ) . '" target="_blank">' . get_the_title( $post->ID ) . '</a>';
                        } else {
                            $action_text .= '<br><em>' . __( 'Post not found', 'wpcommunity' ) . '</em>';
                        }
                    } elseif ( $object == 'comment' ) {
                        $comment = get_comment( $item['object_id'] );
                        if ( ! empty( $comment ) ) {
                            $action_text .= '<br><a href="' . get_the_permalink( $comment->comment_post_ID ) . '#comment-' . $comment->comment_ID . '" target="_blank">' . $this->helper_clear_content( $comment->comment_content ) . '</a>';
                        } else {
                            $action_text .= '<br><em>' . __( 'Comment not found', 'wpcommunity' ) . '</em>';
                        }
                    }
                }

                // кто поменял карму
                if ( ! empty( $item['from_id'] ) ) {
                    $from_text = '<a href="' . esc_url( get_author_posts_url( $item['from_id'] ) ) . '" class="karma-history__avatar">' . get_avatar( $item['from_id'], 24 ) . '</a>';
                } else {
                    $from_text = '';
                }

                // баллы в цвете
                $points_text = $item['direction'] . $item['points'];
                if ( $item['direction'] == '+' ) {
                    $points_text = '<strong style="color: var(--wpsc-text-green-color)">' . $points_text . '</strong>';
                } else {
                    $points_text = '<strong style="color: var(--wpsc-text-red-color)">' . $points_text . '</strong>';
                }

                $item['points_text'] = $points_text;
                $item['action_text'] = $action_text;
                $item['from_text']   = $from_text;

                $history[] = $item;

            }
        }

        return $history;
    }

    public function reset_karma( $user_id ) {
        delete_user_meta( $user_id, self::USER_META_KARMA );
        delete_user_meta( $user_id, self::USER_META_KARMA_HISTORY );
    }


    /**
     * @param $user_id      int     Кому меняем
     * @param $direction    string  Направление, + или -
     * @param $action       string  Действие из рейтов
     * @param $object_id    int     За какой объект начисляем, ID поста, комментария
     * @param $from_id      int     От какого пользователя
     *
     * @return void|\WP_Error
     */
    public function update_karma( $user_id, $direction, $action, $object_id, $from_id = 0 ) {

        if ( ! in_array( $direction, [ '+', '-' ] ) ) {
            return new \WP_Error( 'karma_direction_not_found', __( 'Cant change karma, because direction not found.' ) );
        }

        if ( empty( $action ) || ! isset( $this->karma_rate[ $action ] ) ) {
            return new \WP_Error( 'karma_action_not_found', __( 'Cant change karma, because action not found.' ) );
        }

        $points        = $this->karma_rate[ $action ]['points'];
        $karma         = $this->get_karma( $user_id );
        $karma_history = $this->get_karma_history( $user_id );

        if ( $direction == '+' ) {
            $karma += $points;
        } else {
            $karma -= $points;
        }

        $history = [
            'time'      => current_time( 'timestamp' ),
            'direction' => $direction,
            'points'    => $points,
            'action'    => $action,
            'object_id' => $object_id,
            'from_id'   => $from_id,
        ];

        $karma_history[] = $history;

        /**
         * Allow to increase or decrease karma history limit
         *
         * @since 1.0
         */
        $history_limit = apply_filters( 'wpcommunity/karma/history_limit', 10000 );
        $history_limit = absint( $history_limit ) ?: 10000;

        $karma_history = array_slice( $karma_history, - 1 * $history_limit );

        update_user_meta( $user_id, self::USER_META_KARMA, $karma );
        update_user_meta( $user_id, self::USER_META_KARMA_HISTORY, $karma_history );
    }


    public function get_karma_rate() {
        return $this->karma_rate;
    }


    public function helper_clear_content( $text, $limit = 100 ) {
        $text = strip_tags( $text );
        $text = mb_substr( $text, 0, $limit );

        return $text;
    }


    public function hook_change_post_status( $new_status, $old_status, $post ) {

        // только для постов, иначе срабатывает на все, даже меню
        if ( $post->post_type != 'post' ) {
            return;
        }

        if ( $new_status == 'publish' && $old_status != 'publish' ) {
            $this->update_karma( $post->post_author, '+', 'post_publish', $post->ID );
        }
        if ( $new_status != 'publish' && $old_status == 'publish' ) {
            $this->update_karma( $post->post_author, '-', 'post_unpublish', $post->ID );
        }
    }


    public function hook_vote_change( $object_type, $object_id, $karma_direction, $karma_iteration = 1 ) {

        if ( $object_type == 'post' ) {
            $post = get_post( $object_id );

            if ( empty( $post ) ) {
                return;
            }

            $action = 'post_vote_change';

            $user_id = $post->post_author;
        } elseif ( $object_type == 'comment' ) {
            $comment = get_comment( $object_id );

            if ( empty( $comment ) ) {
                return;
            }

            $action = 'comment_vote_change';

            $user_id = $comment->user_id;
        } else {
            return;
        }

        // если пользователь не найден -- выходим
        if ( empty( $user_id ) ) {
            return;
        }

        for ( $i = 1 ; $i <= $karma_iteration ; $i ++ ) {
            $this->update_karma( $user_id, $karma_direction, $action, $object_id, get_current_user_id() );
        }

    }


    public function hook_comment_post( $comment_ID, $comment_approved ) {

        // выходим если комментарий не одобрен
        if ( $comment_approved == 0 ) {
            return;
        }

        $comment = get_comment( $comment_ID );

        // если не зарегистрированный пользователь, просто гость -- выходим
        if ( empty( $comment->user_id ) ) {
            return;
        }

        $this->update_karma( $comment->user_id, '+', 'comment_publish', $comment_ID );
    }


    public function hook_comment_unapproved_to_approved( $comment ) {

        // если не зарегистрированный пользователь, просто гость -- выходим
        if ( empty( $comment->user_id ) ) {
            return;
        }

        $this->update_karma( $comment->user_id, '+', 'comment_publish', $comment->comment_ID );
    }


    public function hook_delete_comment( $comment_ID, $comment ) {

        // если не зарегистрированный пользователь, просто гость -- выходим
        if ( empty( $comment->user_id ) ) {
            return;
        }

        // если удаляем спам -- не отнимаем баллы
        if ( $comment->comment_approved == 'spam' ) {
            return;
        }

        // если уже был мягко удален -- ничего не делаем, чтобы повторно не вычитать баллы
        if ( theme_container()->get( Comments::class )->is_trash( $comment_ID ) ) {
            return;
        }

        $this->update_karma( $comment->user_id, '-', 'comment_delete', $comment_ID );
    }


    public function hook_spam_comment( $comment_ID, $comment ) {

        // если не зарегистрированный пользователь, просто гость -- выходим
        if ( empty( $comment->user_id ) ) {
            return;
        }

        $this->update_karma( $comment->user_id, '-', 'comment_spam', $comment_ID );
    }


    public function hook_unspam_comment( $comment_ID, $comment ) {

        // если не зарегистрированный пользователь, просто гость -- выходим
        if ( empty( $comment->user_id ) ) {
            return;
        }

        $this->update_karma( $comment->user_id, '+', 'comment_unspam', $comment_ID );
    }

}
