<?php

namespace WPShop\WPCommunity;

use WP_Error;

class Auth {

    /**
     * @return void
     */
    public function init() {
        add_filter( 'wpcommunity/auth/illegal_user_logins', [ $this, '_illegal_user_logins' ] );
        add_filter( 'wpcommunity/auth/sanitize_username', [ $this, '_sanitize_username' ] );
    }

    /**
     * @param array $logins
     *
     * @return array
     */
    public function _illegal_user_logins( $logins ) {
        $logins = is_array( $logins ) ? $logins : [ $logins ];

        $illegal = [
            'adm',
            'admin',
            'administrator',
            'moderator',
            'register',
            'login',
            'logout',

            'user',
            'usr',
            'author',
            'bot',

            'premium',
            'vip',

            // mat
            'pizda',
            'pizdec',
            'zalupa',
        ];

        return array_merge( $logins, $illegal );
    }

    /**
     * @param int    $user_id
     * @param string $old_password
     * @param string $new_password
     *
     * @return bool|WP_Error
     */
    public function change_user_password( $user_id, $old_password, $new_password ) {
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            return new WP_Error( 'user_not_found', __( 'User not found', 'wpcommunity' ) );
        }

        if ( ! wp_check_password( $old_password, $user->user_pass, $user->ID ) ) {
            return new WP_Error( 'incorrect_password', __( 'The old password is incorrect.', 'wpcommunity' ) );
        }

        $validate = $this->validate_password( $new_password );
        if ( is_wp_error( $validate ) ) {
            return $validate;
        }

        wp_set_password( $new_password, $user_id );

        return true;
    }

    /**
     * Проверка валидности пароля
     *
     * @param $password
     *
     * @return bool|WP_Error
     */
    public function validate_password( $password ) {
        if ( empty( $password ) ) {
            return new WP_Error( 'check_email_pass', __( 'Please type your password.', 'wpcommunity' ) );
        } else if ( strlen( $password ) < 8 ) {
            return new WP_Error( 'password_short', __( 'Password must be at least 8 characters long.', 'wpcommunity' ) );
        }

        // Check if password is one or all empty spaces.
        $password = trim( $password );
        if ( empty( $password ) ) {
            return new WP_Error( 'password_reset_empty_space', __( 'The password cannot be a space or all spaces.', 'wpcommunity' ) );
        }

        return true;
    }


    public function check_reset_key( $key, $login ) {

        if ( empty( $key ) || empty( $login ) ) {
            return new WP_Error( 'key_login_invalid', __( 'Reset data is invalid. Please request a new link.', 'wpcommunity' ) );
        }

        $user = check_password_reset_key( $key, $login );
        if ( ! $user || is_wp_error( $user ) ) {
            if ( $user && $user->get_error_code() === 'expired_key' ) {
                return new WP_Error( 'expiredkey', __( 'Your password reset link has expired. Please request a new link.', 'wpcommunity' ) );
            } else {
                return new WP_Error( 'invalidkey', __( 'Your password reset link appears to be invalid. Please request a new link.', 'wpcommunity' ) );
            }
        }

        return true;

    }


    /**
     * @param $username
     *
     * @return string
     */
    public function sanitize_username( $username ) {
        /**
         * @since 1.3.0
         */
        $username = apply_filters( 'wpcommunity/auth/sanitize_username', $username );

        return $username;
    }

    /**
     * Remove @ and spaces from username
     *
     * @param string $username
     *
     * @return string
     */
    public function _sanitize_username( $username ) {
        $username = str_replace( [ '@', ' ' ], '', $username );

        return $username;
    }
}
