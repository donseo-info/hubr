<?php

namespace WPShop\WPCommunity\Actions;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WPShop\WPCommunity\Database\Subs;
use WPShop\WPCommunity\User;

class FollowAction {

    /**
     * @var Subs
     */
    protected $subs;

    /**
     * @param Subs $follows
     */
    public function __construct( Subs $follows ) {
        $this->subs = $follows;
    }

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_user_subscribe';
            add_action( "wp_ajax_{$action}", [ $this, '_subscribe' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_subscribe' ] );

            $action = 'wpcommunity_user_unsubscribe';
            add_action( "wp_ajax_{$action}", [ $this, '_unsubscribe' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_unsubscribe' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _subscribe() {
        if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], THEME_DEFAULT_NONCE_CONTEXT ) ) {
            wp_send_json_error( new WP_Error( 'forbidden', __( 'Your session has expired. Please refresh the page and try again.', 'wpcommunity' ) ) );
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( new WP_Error( 'login_required', __( 'Login required', 'wpcommunity' ) ) );
        }

        $data = map_deep( $_REQUEST, 'wp_unslash' );

        $errors = [];
        if ( ! in_array( $data['type'], [
            User::FOLLOW_TYPE_CATEGORY,
            User::FOLLOW_TYPE_TAG,
            User::FOLLOW_TYPE_USER,
        ] ) ) {
            $errors['type'] = __( 'Invalid subscription type', 'wpcommunity' );
        }

        if ( $errors ) {
            $validation_errors = new WP_Error( 'validation', __( 'Validation errors', 'wpcommunity' ) );
            foreach ( $errors as $code => $error ) {
                $validation_errors->add( $code, $error );
            }
            wp_send_json_error( $validation_errors );
        }

        if ( $this->subs->get_row( get_current_user_id(), $data['type'], $data['target'] ) ) {
            wp_send_json_success( [ 'message' => __( 'You are already subscribed', 'wpcommunity' ) ] );
        }

        if ( $this->subs->insert( get_current_user_id(), $data['type'], $data['target'] ) ) {
            wp_send_json_success( [ 'message' => __( 'Successfully subscribed', 'wpcommunity' ) ] );
        }

        wp_send_json_error( new WP_Error( 'error', __( 'Something went wrong while storing subscription', 'wpcommunity' ) ) );
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _unsubscribe() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( new WP_Error( 'login_required', __( 'Login required', 'wpcommunity' ) ) );
        }

        if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], THEME_DEFAULT_NONCE_CONTEXT ) ) {
            wp_send_json_error( new WP_Error( 'forbidden', __( 'Your session has expired. Please refresh the page and try again.', 'wpcommunity' ) ) );
        }

        $data = map_deep( $_REQUEST, 'wp_unslash' );

        $errors = [];
        if ( ! in_array( $data['type'], [
            User::FOLLOW_TYPE_CATEGORY,
            User::FOLLOW_TYPE_TAG,
            User::FOLLOW_TYPE_USER,
        ] ) ) {
            $errors['type'] = __( 'Invalid subscription type', 'wpcommunity' );
        }

        if ( $errors ) {
            $validation_errors = new WP_Error( 'validation', __( 'Validation errors', 'wpcommunity' ) );
            foreach ( $errors as $code => $error ) {
                $validation_errors->add( $code, $error );
            }
            wp_send_json_error( $validation_errors );
        }

        if ( $this->subs->remove( get_current_user_id(), $data['type'], $data['target'] ) ) {
            wp_send_json_success( [ 'message' => __( 'Successfully unsubscribed', 'wpcommunity' ) ] );
        }

        wp_send_json_error( new WP_Error( 'error', __( 'Something went wrong while removing subscription', 'wpcommunity' ) ) );
    }
}
