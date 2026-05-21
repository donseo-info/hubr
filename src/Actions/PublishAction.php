<?php

namespace WPShop\WPCommunity\Actions;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WPShop\WPCommunity\FrontendPublish;
use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\Post;
use function WPShop\WPCommunity\get_setting;

class PublishAction {

    /**
     * @var FrontendPublish
     */
    protected $frontend_publish;

    /**
     * @var Membership
     */
    protected $membership;

    /**
     * @param FrontendPublish $frontend_publish
     * @param Membership      $membership
     */
    public function __construct( FrontendPublish $frontend_publish, Membership $membership ) {
        $this->frontend_publish = $frontend_publish;
        $this->membership       = $membership;
    }

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_save_post';
            add_action( "wp_ajax_{$action}", [ $this, '_save_post' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_save_post' ] );

            $action = 'wpcommunity_delete_post';
            add_action( "wp_ajax_{$action}", [ $this, '_delete_post' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_delete_post' ] );

            $action = 'wpcommunity_suggest_tags';
            add_action( "wp_ajax_{$action}", [ $this, '_suggest_tags' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_suggest_tags' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _save_post() {
        if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], THEME_DEFAULT_NONCE_CONTEXT ) ) {
            wp_send_json_error( new WP_Error( 'forbidden', __( 'Your session has expired. Please refresh the page and try again.', 'wpcommunity' ) ) );
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( new WP_Error( 'login_required', __( 'Login required', 'wpcommunity' ) ) );
        }

        if ( empty( $_REQUEST['data'] ) ) {
            wp_send_json_error( new WP_Error( 'empty_data', __( 'Empty data', 'wpcommunity' ) ) );
        }

        // get data
        $data = $_REQUEST['data'];


        // empty title
        if ( empty( $data['title'] ) ) {
            wp_send_json_error( new WP_Error( 'empty_title', __( 'Empty title', 'wpcommunity' ) ) );
        }


        // определяем post_id, чтобы понимать, редактируем или создаем новую
        $post_id = false;
        if ( ! empty( $data['post_id'] ) ) {
            $post_id = (int) $data['post_id'];

            $is_editable = $this->frontend_publish->is_post_editable( $post_id, get_current_user_id() );

            if ( is_wp_error( $is_editable ) ) {
                wp_send_json_error( $is_editable );
            }
        }


        // prepare excerpt
        $excerpt = ( ! empty( $data['excerpt'] ) ) ? $data['excerpt'] : '';
        $excerpt = strip_tags( $excerpt );


        $category_id = 0;
        if ( ! empty( $data['topic'] ) ) {
            $category_id = (int) $data['topic'];
        }
        $excluded_categories = wp_parse_id_list( get_setting( 'publish.exclude_categories' ) );
        if ( in_array( $category_id, $excluded_categories ) ) {
            $category_id = 0;
        }

        $status = 'draft';
        if ( ! empty( $_REQUEST['type'] ) && $_REQUEST['type'] == 'publish' ) {
            $status = get_setting( 'publish.default_status' );
        }


        // Готовим данные для поста
        $post_data = [
            'post_title'     => sanitize_text_field( $data['title'] ),
            'post_content'   => $data['text'],
            'post_status'    => $status,
            'post_author'    => get_current_user_id(),
            'comment_status' => 'open',
            'post_excerpt'   => $excerpt,
        ];

        if ( ! empty( $category_id ) ) {
            $post_data['post_category'] = [ $category_id ];
        }

        if ( $post_id ) {
            $post_data['ID'] = $post_id;
        }


        // Вставляем запись в базу данных
        $post_id = wp_insert_post( $post_data, true );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( $post_id );
        }

        if ( $tags = $data['tags'] ?? '' ) {
            if ( ! get_setting( 'publish.can_create_tags' ) ) {
                $tags = explode( ',', $tags );
                $tags = array_filter( $tags, function ( $tag ) {
                    return tag_exists( $tag );
                } );
            }
            if ( is_wp_error( $result = wp_set_post_tags( $post_id, $tags ) ) ) {
                wp_send_json_error( $result );
            }
        }

        // проверяем и сохраняем формат
        $format = ( isset( $this->frontend_publish->get_formats()[ $data['format'] ] ) ) ? $data['format'] : 'post';
        update_post_meta( $post_id, Post::POST_META_FORMAT, $format );


        // проверяем и сохраняем доступ
        $default_access = $this->membership->get_default_post_type_access( get_post( $post_id ) );
        $access         = ( in_array( $data['access'], [ 'public', 'private' ] ) ) ? $data['access'] : $default_access;
        update_post_meta( $post_id, Post::POST_META_ACCESS, $access );

        if ( ! empty( $_REQUEST['type'] ) && $_REQUEST['type'] == 'preview' ) {
            $post_link = get_preview_post_link( $post_id );
        } else {
            if ( get_setting( 'publish.default_status' ) == 'publish' ) {
                $post_link = get_the_permalink( $post_id );
            } else {
                $post_link = $this->frontend_publish->get_edit_link( $post_id );
            }
        }

        /**
         * @see wp_ajax_set_post_thumbnail()
         */
        $thumbnail_id = $data['_thumbnail_id'];
        if ( '-1' == $thumbnail_id ) {
            delete_post_thumbnail( $post_id );
        } else {
            set_post_thumbnail( $post_id, $thumbnail_id );
        }

        wp_send_json_success( [
            'post_id'   => $post_id,
            'post_link' => $post_link,
            'message'   => __( 'The post are successfully saved.', 'wpcommunity' ),
            'post_data' => $post_data,
        ] );


//		if ( empty( $_REQUEST['post_id'] ) ) {
//			wp_send_json_error( new WP_Error( 'wrong_data', __( 'Wrong data', 'wpcommunity' ) ) );
//		}
//
//		$post = get_post( $_REQUEST['post_id'] );
//		if ( ! $post ) {
//			wp_send_json_error( new WP_Error( 'post_not_found', __( 'Post not found', 'wpcommunity' ) ) );
//		}


    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _delete_post() {
        if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], THEME_DEFAULT_NONCE_CONTEXT ) ) {
            wp_send_json_error( new WP_Error( 'forbidden', __( 'Your session has expired. Please refresh the page and try again.', 'wpcommunity' ) ) );
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( new WP_Error( 'login_required', __( 'Login required', 'wpcommunity' ) ) );
        }

        if ( empty( $_REQUEST['post_id'] ) ) {
            wp_send_json_error( new WP_Error( 'empty_data', __( 'Empty data', 'wpcommunity' ) ) );
        }


        $post_id = (int) $_REQUEST['post_id'];

        $is_deletable = $this->frontend_publish->is_post_deletable( $post_id, get_current_user_id() );

        if ( is_wp_error( $is_deletable ) ) {
            wp_send_json_error( $is_deletable );
        }


        $result = wp_delete_post( $post_id );

        if ( $result ) {
            wp_send_json_success( [
                'post_id' => $post_id,
                'message' => __( 'The post was successfully deleted.', 'wpcommunity' ),
            ] );
        } else {
            wp_send_json_error( new WP_Error( 'could_not_delete', __( 'Couldn\'t delete the post.', 'wpcommunity' ) ) );
        }
    }

    /**
     * @return void
     */
    public function _suggest_tags() {
        $text = $_REQUEST['text'] ?? '';

        $tags = [];
        if ( $text ) {
            $tags = get_terms(
                [
                    'taxonomy'   => 'post_tag',
                    'name__like' => $text,
                    'fields'     => 'names',
                    'hide_empty' => false,
                    'number'     => 20,
                ]
            );
        }

        wp_send_json_success( $tags );
    }
}
