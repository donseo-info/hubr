<?php

namespace WPShop\WPCommunity\Actions;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WPShop\WPCommunity\Invite;

class InviteAction {

    /**
     * @var Invite
     */
    protected $invite;

    /**
     * @param Invite $invite
     */
    public function __construct( Invite $invite ) {
        $this->invite = $invite;
    }

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_invite_activate';
            add_action( "wp_ajax_{$action}", [ $this, '_invite_activate' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_invite_activate' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _invite_activate() {
        if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], THEME_DEFAULT_NONCE_CONTEXT ) ) {
            wp_send_json_error( new WP_Error( 'forbidden', __( 'Your session has expired. Please refresh the page and try again.', 'wpcommunity' ) ) );
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( new WP_Error( 'login_required', __( 'Login required', 'wpcommunity' ) ) );
        }

        if ( empty( $_REQUEST['invite'] ) ) {
            wp_send_json_error( new WP_Error( 'empty_data', __( 'Empty data', 'wpcommunity' ) ) );
        }

        // get data
        $invite = $_REQUEST['invite'];

        // apply invite
        $apply_invite = $this->invite->apply_invite( get_current_user_id(), $invite );

        // если ошибка
        if ( is_wp_error( $apply_invite ) ) {
            wp_send_json_error( new WP_Error( $apply_invite->get_error_code(), $apply_invite->get_error_message() ) );
        }


        wp_send_json_success( [ 'message' => __( 'Invite successfully applied', 'wpcommunity' ) ] );
    }
}
