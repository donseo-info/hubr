<?php

namespace WPShop\WPCommunity\Actions;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\Auth;
use WPShop\WPCommunity\Features\GoogleReCaptcha;
use WPShop\WPCommunity\Invite;
use WPShop\WPCommunity\Mail;
use function WPShop\WPCommunity\get_setting;

class AuthAction {

    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @var Invite
     */
    protected $invite;

    /**
     * @var Mail
     */
    protected $mail;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var GoogleReCaptcha
     */
    protected $google_re_captcha;

    /**
     * @param Auth            $auth
     * @param Invite          $invite
     * @param Mail            $mail
     * @param Settings        $settings
     * @param GoogleReCaptcha $google_re_captcha
     */
    public function __construct(
        Auth $auth,
        Invite $invite,
        Mail $mail,
        Settings $settings,
        GoogleReCaptcha $google_re_captcha
    ) {
        $this->auth              = $auth;
        $this->invite            = $invite;
        $this->mail              = $mail;
        $this->settings          = $settings;
        $this->google_re_captcha = $google_re_captcha;
    }

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_auth_login';
            add_action( "wp_ajax_{$action}", [ $this, '_login' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_login' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _login() {

        if ( empty( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], THEME_DEFAULT_NONCE_CONTEXT ) ) {
            wp_send_json_error( new WP_Error( 'forbidden', __( 'Your session has expired. Please refresh the page and try again.', 'wpcommunity' ) ) );
        }

        if ( empty( $_REQUEST['data'] ) ) {
            wp_send_json_error( new WP_Error( 'empty_data', __( 'Empty data', 'wpcommunity' ) ) );
        }


        // get data
        $data = $_REQUEST['data'];

        // todo добавить редирект после авторизации
        $redirect = '';
        if ( ! empty( $data['redirect_to'] ) ) {
            $redirect = $data['redirect_to'];
        }


        if ( is_user_logged_in() ) {
            wp_send_json_success( [ 'redirect' => $redirect ] );
        }

        if ( $data['action'] == 'login' ) {

            if ( empty( $data['email'] ) || empty( $data['pass'] ) || ! is_email( $data['email'] ) ) {
                wp_send_json_error( new WP_Error( 'check_email_pass', __( 'Please make sure you fill correct email and password.', 'wpcommunity' ) ) );
            }

            // Если не найден - выдаем ошибку
            $user = get_user_by( 'email', $data['email'] );
            if ( ! $user ) {
                wp_send_json_error( new WP_Error( 'incorrect_email_pass', __( 'Incorrect email or password', 'wpcommunity' ) ) );
            }

            // если пароль не совпал
            if ( ! wp_check_password( $data['pass'], $user->user_pass, $user->ID ) ) {
                wp_send_json_error( new WP_Error( 'incorrect_email_pass', __( 'Incorrect email or password', 'wpcommunity' ) ) );
            }

            $secure_cookie = apply_filters( 'wpcommunity/auth/secure_cookie', is_ssl(), [
                'user_login'    => $data['email'],
                'user_password' => $data['pass'],
                'remember'      => ! empty( $data['rememberme'] ),
            ] );

            wp_clear_auth_cookie();
            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID, ! empty( $data['rememberme'] ), $secure_cookie );

            do_action( 'wp_login', $user->user_login, $user );

            wp_send_json_success( [ 'redirect' => $redirect ] );

        } else if ( $data['action'] == 'register' ) {

            $errors = new WP_Error();

            $sanitized_username = ( ! empty( $data['username'] ) ) ? sanitize_user( $data['username'] ) : '';

            // удаляем @ и пробелы
            if ( ! empty( $sanitized_username ) ) {
                $sanitized_username = $this->auth->sanitize_username( $sanitized_username );
            }


            // Check the username
            if ( empty( $data['username'] ) || empty( $sanitized_username ) ) {
                $errors->add( 'empty_username', __( 'Please enter a username.', 'wpcommunity' ) );
            } elseif ( ! validate_username( $sanitized_username ) ) {
                $errors->add( 'invalid_username', __( 'This username is invalid because it uses illegal characters.', 'wpcommunity' ) );
            } elseif ( username_exists( $sanitized_username ) ) {
                $errors->add( 'username_exists', __( 'This username is already registered.', 'wpcommunity' ) );
            } else {
                /** This filter is documented in wp-includes/user.php */
                $illegal_user_logins = (array) apply_filters( 'illegal_user_logins', [] );
                if ( in_array( strtolower( $sanitized_username ), array_map( 'strtolower', $illegal_user_logins ) ) ) {
                    $errors->add( 'invalid_username', __( 'Sorry, that username is not allowed.' ) );
                }
            }

            // Check the email address
            if ( empty( $data['email'] ) ) {
                $errors->add( 'empty_email', __( 'Please type your email address.', 'wpcommunity' ) );
            } elseif ( ! is_email( $data['email'] ) ) {
                $errors->add( 'invalid_email', __( 'The email address isn&#8217;t correct.', 'wpcommunity' ) );
            } elseif ( email_exists( $data['email'] ) ) {
                //$errors->add( 'email_exists', __( 'This email is already registered.', 'wpcommunity' ) );
                $errors->add( 'invalid_email', __( 'The email address isn&#8217;t correct.', 'wpcommunity' ) );
            }


            // проверяем пароль
            if ( is_wp_error( $validate_password = $this->auth->validate_password( $data['pass'] ) ) ) {
                $errors->add( $validate_password->get_error_code(), $validate_password->get_error_message() );
            }

            // проверяем есть ли инвайт, не просрочен ли и есть ли у него лимиты
            if ( $this->settings->get_value( 'registration.require_invite' ) ) {
                $check_invite = $this->invite->check_invite( $data['invite'] ?? '' );
                if ( is_wp_error( $check_invite ) ) {
                    $errors->add( $check_invite->get_error_code(), $check_invite->get_error_message() );
                }
            }

            // validate google recaptcha
            if ( $this->google_re_captcha->enabled( GoogleReCaptcha::FORM_SIGN_UP ) ) {
                if ( ! $this->google_re_captcha->verify( $_REQUEST['recaptcha_token'] ?? '' ) ) {
                    $errors->add( 'invalid_recaptcha', __( 'Invalid reCAPTCHA response.', 'wpcommunity' ) );
                }
            }

            if ( $errors->has_errors() ) {
                wp_send_json_error( $errors );
            }

            /**
             * Fires when submitting registration form data, before the user is created.
             *
             * @param string   $sanitized_user_login The submitted username after being sanitized.
             * @param string   $email                The submitted email.
             * @param WP_Error $errors               Contains any errors with submitted username and email,
             *                                       e.g., an empty field, an invalid username or email,
             *                                       or an existing username or email.
             *
             * @since 2.1.0
             *
             */
            do_action( 'register_post', $sanitized_username, $data['email'], $errors );

            /**
             * Filters the errors encountered when a new user is being registered.
             *
             * The filtered WP_Error object may, for example, contain errors for an invalid
             * or existing username or email address. A WP_Error object should always returned,
             * but may or may not contain errors.
             *
             * If any errors are present in $errors, this will abort the user's registration.
             *
             * @param WP_Error $errors               A WP_Error object containing any errors encountered
             *                                       during registration.
             * @param string   $sanitized_user_login User's username after it has been sanitized.
             * @param string   $email                User's email.
             *
             * @since 2.1.0
             *
             */
            $errors = apply_filters( 'registration_errors', $errors, $sanitized_username, $data['email'] );


            // register user
            $user_id = wp_create_user( $sanitized_username, $data['pass'], $data['email'] );
            if ( ! $user_id || is_wp_error( $user_id ) ) {
                $errors->add( 'registerfail', __( 'Couldn&#8217;t register you, please contact the administrator!' ) );

                wp_send_json_error( $errors );
            }

            // send mail
            $this->mail->register_mail( $data['email'], $data['pass'] );


            // invite
            if ( ! empty( $data['invite'] ) ) {
                $apply_invite = $this->invite->apply_invite( $user_id, $data['invite'] );
                if ( is_wp_error( $apply_invite ) ) {
                    wp_send_json_error( $apply_invite );
                }
            }

            wp_send_json_success( [ 'redirect' => $redirect ] );

        } else if ( $data['action'] == 'forget' ) {

            $errors = new WP_Error();

            // Check the email address
            if ( empty( $data['email'] ) ) {
                $errors->add( 'invalid_email', __( 'Please type your email address.', 'wpcommunity' ) );
            } elseif ( ! is_email( $data['email'] ) ) {
                $errors->add( 'invalid_email', __( 'The email address isn&#8217;t correct.', 'wpcommunity' ) );
            } elseif ( ! email_exists( $data['email'] ) ) {
                //$errors->add( 'email_exists', __( 'This email is not registered.', 'wpcommunity' ) );
                $errors->add( 'invalid_email', __( 'The email address isn&#8217;t correct.', 'wpcommunity' ) );
            }


            // если были ошибки -- выходим
            if ( $errors->has_errors() ) {
                wp_send_json_error( $errors );
            }

            // генерируем ссылку с восстановлением
            // todo попробовать отправить в бота
            $user = get_user_by_email( $data['email'] );
            $key  = get_password_reset_key( $user );

            if ( is_wp_error( $key ) ) {
                $errors->add( 'reset_key_fail', __( 'We can&#8217;t generate reset link. Please contact to administrator.', 'wpcommunity' ) );
                wp_send_json_error( $errors );
            }


            // Localize password reset message content for user.
            $locale = get_user_locale( $user );

            $switched_locale = switch_to_locale( $locale );

            if ( is_multisite() ) {
                $site_name = get_network()->site_name;
            } else {
                $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
            }

            $message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
            /* translators: %s: Site name. */
            $message .= sprintf( __( 'Site Name: %s' ), $site_name ) . "\r\n\r\n";
            /* translators: %s: User login. */
            $message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
            $message .= __( 'If this was a mistake, ignore this email and nothing will happen.' ) . "\r\n\r\n";
            $message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";


            // reset url
            $page_profile = get_setting( 'page.profile' );

            $reset_url = __( 'Reset key', 'wpcommunity' ) . ': ' . $key;
            if ( ! empty( $page_profile ) ) {
                $reset_url = get_the_permalink( $page_profile ) . '?action=reset_password&key=' . $key . '&login=' . rawurlencode( $user->user_login ) . '&wp_lang=' . $locale;
            }

            $message .= $reset_url . "\r\n\r\n";


            $requester_ip = $_SERVER['REMOTE_ADDR'];
            if ( $requester_ip ) {
                $message .= sprintf(
                            /* translators: %s: IP address of password reset requester. */
                                __( 'This password reset request originated from the IP address %s.' ),
                                $requester_ip
                            ) . "\r\n";
            }

            /* translators: Password reset notification email subject. %s: Site title. */
            $title = '[' . $site_name . '] ' . __( 'Password Recovery', 'wpcommunity' );

            if ( $switched_locale ) {
                restore_previous_locale();
            }

            $subject = wp_specialchars_decode( $title );

            if ( ! wp_mail( $user->user_email, $subject, $message, '' ) ) {
                $errors->add(
                    'retrieve_password_email_failure', __( 'The email could not be sent. Your site may not be correctly configured to send emails. Get support for resetting your password.', 'wpcommunity' )
                );
                wp_send_json_error( $errors );
            }

            wp_send_json_success( [ 'message' => __( 'A password reset link was emailed.', 'wpcommunity' ) ] );

        } else if ( $data['action'] == 'new_password' ) {

            $errors = new WP_Error();

            // проверяем ключ, если неправильный -- сразу выходим
            $check_reset_key = $this->auth->check_reset_key( $data['key'], $data['login'] );
            if ( is_wp_error( $check_reset_key ) ) {
                wp_send_json_error( new WP_Error( $check_reset_key->get_error_code(), $check_reset_key->get_error_message() ) );
            }


            // проверяем пароль
            if ( is_wp_error( $validate_password = $this->auth->validate_password( $data['pass'] ) ) ) {
                $errors->add( $validate_password->get_error_code(), $validate_password->get_error_message() );
            }


            // если были ошибки -- выходим
            if ( $errors->has_errors() ) {
                wp_send_json_error( $errors );
            }


            // меняем пароль
            $user = get_user_by( 'login', $data['login'] );
            reset_password( $user, $data['pass'] );


            // добавляем GET параметр, чтобы вывести сообщение
            $redirect = add_query_arg( 'reset_password', 'success', $redirect );

            wp_send_json_success( [ 'redirect' => $redirect ] );

        }

        // если не нашли нужное действие
        wp_send_json_error( new WP_Error( 'wrong_action', __( 'Wrong action', 'wpcommunity' ) ) );
    }
}
