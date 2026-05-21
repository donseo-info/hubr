<?php

namespace WPShop\WPCommunity\Actions;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WPShop\WPCommunity\Auth;
use WPShop\WPCommunity\Social;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

class ProfileAction {

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_profile_update';
            add_action( "wp_ajax_{$action}", [ $this, '_update_profile' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_update_profile' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _update_profile() {
        if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], THEME_DEFAULT_NONCE_CONTEXT ) ) {
            wp_send_json_error( new WP_Error( 'forbidden', __( 'Your session has expired. Please refresh the page and try again.', 'wpcommunity' ) ) );
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( new WP_Error( 'login_required', __( 'Login required', 'wpcommunity' ) ) );
        }

        if ( empty( $_REQUEST['data'] ) ) {
            wp_send_json_error( new WP_Error( 'empty_data', __( 'Empty data', 'wpcommunity' ) ) );
        }

        $user = wp_get_current_user();

        if ( ! $user ) {
            wp_send_json_error( new WP_Error( 'user_not_found', __( 'User not found', 'wpcommunity' ) ) );
        }

        $success_response = [
            'messages' => [ __( 'Profile successfully updated.', 'wpcommunity' ) ],
        ];

        // get data
        $data = wp_parse_args( $_REQUEST['data'], [
            'social_profile'           => [],
            'wpcommunity_display_name' => 'nickname',
            'login'                    => '',
            'nickname'                 => '',

            'old_password'         => '',
            'new_password'         => '',
            'new_password_confirm' => '',
        ] );

        $data = map_deep( $data, 'trim' );

        $data['login'] = sanitize_user( $data['login'], true );

        $data['login'] = theme_container()->get( Auth::class )->sanitize_username( $data['login'] );

        if ( $data['login'] && $data['login'] != $user->user_login ) {
            if ( get_user_by( 'login', $data['login'] ) ) {
                wp_send_json_error( new WP_Error( 'wrong_login', __( 'Unable to set this login', 'wpcommunity' ) ) );
            }

            if ( get_user_by( 'slug', $data['login'] ) ) {
                wp_send_json_error( new WP_Error( 'wrong_login', __( 'Unable to set this nickname', 'wpcommunity' ) ) );
            }

            if ( ! $this->update_user_login( $user->ID, $data['login'] ) ) {
                wp_send_json_error( new WP_Error( 'unable_change_login', __( 'Unable to change login', 'wpcommunity' ) ) );
            }

            $success_response['redirect_url'] = get_permalink( get_setting( 'page.profile' ) );
            $success_response['messages'][]   = __( 'You have changed your login. You need to sign in to your account again.', 'wpcommunity' );
            wp_logout();
        }

        // если пришло имя, даже пустое -- сохраняем
        if ( isset( $data['first_name'] ) ) {
            $first_name = sanitize_text_field( $data['first_name'] );
            update_user_meta( get_current_user_id(), 'first_name', $first_name );
        }

        // если пришла фамилия, даже пустое -- сохраняем
        if ( isset( $data['last_name'] ) ) {
            $last_name = sanitize_text_field( $data['last_name'] );
            update_user_meta( get_current_user_id(), 'last_name', $last_name );
        }

        update_user_meta( get_current_user_id(), 'wpcommunity_display_name', $data['wpcommunity_display_name'] );

        // если пришло описание, даже пустое -- сохраняем
        if ( isset( $data['description'] ) ) {
            update_user_meta( get_current_user_id(), 'description', $data['description'] );
        }

        if ( $data['old_password'] ) {
            if ( empty( $data['new_password'] ) || empty( $data['new_password_confirm'] ) ) {
                wp_send_json_error( new WP_Error( 'empty_new_password', __( 'Please enter a new password and confirm it.', 'wpcommunity' ) ) );
            }
            if ( $data['new_password'] !== $data['new_password_confirm'] ) {
                wp_send_json_error( new WP_Error( 'password_mismatch', __( 'New password and confirmation do not match.', 'wpcommunity' ) ) );
            }

            $auth   = theme_container()->get( Auth::class );
            $result = $auth->change_user_password( get_current_user_id(), $data['old_password'], $data['new_password'] );
            if ( is_wp_error( $result ) ) {
                wp_send_json_error( $result );
            }

            $success_response['redirect_url'] = get_permalink( get_setting( 'page.profile' ) );
            $success_response['messages'][]   = __( 'You have changed password. You need to sign in to your account again.', 'wpcommunity' );
        }

        $service_keys   = array_keys( theme_container()->get( Social::class )->get_services() );
        $service_keys[] = 'url';
        foreach ( $data['social_profile'] as $name => $value ) {
            if ( ! in_array( $name, $service_keys ) ) {
                continue;
            }
            $value = sanitize_url( $value );
            update_user_meta( get_current_user_id(), $name, $value );
        }

        wp_send_json_success( $success_response );
    }


    /**
     * @param int    $user_id
     * @param string $login
     *
     * @return bool
     */
    protected function update_user_login( $user_id, $login ) {
        global $wpdb;
        $wpdb->update(
            $wpdb->users,
            [
                'user_login'    => $login,
                'user_nicename' => $login,
            ],
            [ 'ID' => $user_id ]
        );

        return ! $wpdb->last_error;
    }
}
