<?php

namespace WPShop\WPCommunity\Actions;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WPShop\WPCommunity\User;

class AvatarAction {

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_remove_avatar';
            add_action( "wp_ajax_{$action}", [ $this, '_remove_avatar' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_remove_avatar' ] );

            $action = 'wpcommunity_upload_avatar';
            add_action( "wp_ajax_{$action}", [ $this, '_upload_avatar' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_upload_avatar' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _remove_avatar() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( new WP_Error( 'error_upload', __( 'You are not allowed for avatar upload', 'wpcommunity' ) ) );
        }

        if ( $current_attachment_id = get_user_meta( get_current_user_id(), User::USER_META_AVATAR_ATTACHMENT, true ) ) {
            wp_delete_attachment( $current_attachment_id, true );
            delete_user_meta( get_current_user_id(), User::USER_META_AVATAR_ATTACHMENT );
        }
        wp_send_json_success( [
            'img_html' => get_avatar( get_current_user_id(), 150 ),
        ] );
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _upload_avatar() {
        $data = map_deep( $_POST, 'wp_unslash' );

        if ( empty( $data['file'] ) ) {
            wp_send_json_error( new WP_Error( 'error_upload', __( 'Unable to upload avatar', 'wpcommunity' ) ) );
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( new WP_Error( 'error_upload', __( 'You are not allowed for avatar upload', 'wpcommunity' ) ) );
        }

        $user  = wp_get_current_user();
        $title = __( 'Avatar for ', 'wpcommunity' ) . preg_replace( '/\s+/', '-', $user->display_name );

        $attachment_id = $this->save_attachment( $data['file'], $title, $user->ID );

        if ( is_wp_error( $attachment_id ) ) {
            wp_send_json_error( $attachment_id );
        }

        if ( $current_attachment_id = get_user_meta( $user->ID, User::USER_META_AVATAR_ATTACHMENT, true ) ) {
            wp_delete_attachment( $current_attachment_id, true );
        }
        update_user_meta( $user->ID, User::USER_META_AVATAR_ATTACHMENT, $attachment_id );
        if ( is_multisite() ) {
            update_user_meta( $user->ID, User::USER_META_AVATAR_ATTACHMENT_BLOG, get_current_blog_id() );
        }

        wp_send_json_success( [
            'url'     => wp_get_attachment_url( $attachment_id ),
            'message' => __( 'Avatar successfully updated', 'wpcommunity' ),
        ] );
    }

    /**
     * @param string $img_base64
     *
     * @return int|WP_Error
     */
    protected function save_attachment( $img_base64, $title, $user_id ) {
        [ $type, $img_base64 ] = explode( ';', $img_base64 );
        [ , $img_base64 ] = explode( ',', $img_base64 );
        $img_base64 = base64_decode( $img_base64 );

        if ( false === $img_base64 ) {
            return new WP_Error( 'save_attachment', __( 'Unable to decode attachment data', 'wpcommunity' ) );
        }

        $wp_upload_dir = wp_upload_dir();

        $hash = md5( $img_base64 );
        $file = $wp_upload_dir['path'] . "/{$user_id}-{$hash}.jpeg";
        if ( false === file_put_contents( $file, $img_base64 ) ) {
            return new WP_Error( 'save_attachment', __( 'Unable to save attachment file', 'wpcommunity' ) );
        }

        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        if ( ! file_is_valid_image( $file ) ) {
            return new WP_Error( 'save_attachment', __( 'Unable to save invalid image file', 'wpcommunity' ) );
        }

        $filetype   = wp_check_filetype( basename( $file ) );
        $attachment = [
            'guid'           => $wp_upload_dir['url'] . '/' . basename( $file ),
            'post_mime_type' => $filetype['type'],
            'post_title'     => $title,
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];

        $attach_id = wp_insert_attachment( $attachment, $file );
        if ( is_wp_error( $attach_id ) ) {
            return $attach_id;
        }

        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        return $attach_id;
    }
}
